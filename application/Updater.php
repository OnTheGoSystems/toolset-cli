<?php

namespace OTGS\Toolset\CLI;

/**
 * Allow to update this plugin directly from GitHub releases via the WordPress core update mechanism.
 *
 * @link https://www.smashingmagazine.com/2015/08/deploy-wordpress-plugins-with-github-using-transients/
 */
class Updater {

	const GITHUB_REPOSITORY = 'toolset-cli';
	const GITHUB_ORGANIZATION = 'OnTheGoSystems';

	const PLUGIN_PROP_NAME = 'Name';
	const PLUGIN_PROP_URI = 'PluginURI';
	const PLUGIN_PROP_AUTHOR_NAME = 'AuthorName';
	const PLUGIN_PROP_AUTHOR_URI = 'AuthorURI';
	const PLUGIN_PROP_DESCRIPTION = 'Description';

	const GITHUB_RELEASE_VERSION = 'tag_name';
	const GITHUB_RELEASE_DATE = 'published_at';
	//const GITHUB_RELEASE_DOWNLOAD_URL = 'zipball_url';
	const GITHUB_RELEASE_ASSETS = 'assets';
	const GITHUB_ASSET_DOWNLOAD_URL = 'browser_download_url';


	/** @var string */
	private $plugin_filename_path;

	/** @var string Cache for get_plugin_basename(). */
	private $plugin_basename;

	/** @var array|null Cache for get_plugin_property(). */
	private $plugin_data;

	/** @var array|null Cache for get_latest_release_info(). */
	private $github_response;


	/**
	 * Updater constructor.
	 *
	 * @param $plugin_filename_path
	 */
	public function __construct( $plugin_filename_path ) {
		$this->plugin_filename_path = $plugin_filename_path;
	}


	/**
	 * Initialize the updater.
	 */
	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'modify_transient' ], 10, 1 );
		add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 10, 3 );
		add_filter( 'upgrader_post_install', [ $this, 'after_install' ], 10, 3 );
		add_filter( 'plugin_row_meta', [ $this, 'filter_plugin_links' ], 10, 2 );
	}


	/**
	 * Retrieve a property of the currently installed version of the plugin.
	 *
	 * @param string $key One of the PLUGIN_PROP_* keys.
	 *
	 * @return string
	 */
	private function get_plugin_property( string $key ): string {
		if ( null === $this->plugin_data ) {
			$this->plugin_data = get_plugin_data( $this->plugin_filename_path );
		}

		if ( ! array_key_exists( $key, $this->plugin_data ) ) {
			return '';
		}

		return $this->plugin_data[ $key ];
	}


	private function get_plugin_basename(): string {
		if ( null === $this->plugin_basename ) {
			$this->plugin_basename = plugin_basename( $this->plugin_filename_path );
		}

		return $this->plugin_basename;
	}


	/**
	 * Get a property from the latest release on GitHub.
	 *
	 * @param string $key One of the GITHUB_RELEASE_* keys.
	 *
	 * @return string|array
	 */
	private function get_latest_release_property( string $key ) {
		$release_info = $this->get_latest_release_info();
		if ( ! array_key_exists( $key, $release_info ) ) {
			return '';
		}

		return $release_info[ $key ];
	}


	/**
	 * @return string|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	private function get_latest_release_download_url() {
		$assets = $this->get_latest_release_property( self::GITHUB_RELEASE_ASSETS );

		if ( ! is_array( $assets ) || 1 !== count( $assets ) ) {
			return null;
		}

		$asset = reset( $assets );

		if ( ! array_key_exists( self::GITHUB_ASSET_DOWNLOAD_URL, $asset ) ) {
			return null;
		}

		$url = $asset[ self::GITHUB_ASSET_DOWNLOAD_URL ];

		if ( ! is_string( $url ) || empty( $url ) ) {
			return null;
		}

		return $url;
	}


	/**
	 * Get details about the latest release from GitHub's API.
	 */
	private function get_latest_release_info(): array {
		if ( null === $this->github_response ) {
			$api_uri = sprintf(
				'https://api.github.com/repos/%1$s/%2$s/releases/latest',
				self::GITHUB_ORGANIZATION,
				self::GITHUB_REPOSITORY
			);
			$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $api_uri ) ), true );

			$this->github_response = $response;
		}

		return $this->github_response;
	}


	/**
	 * Modify the transient tracking the plugins' versions.
	 *
	 * @param $transient
	 *
	 * @return mixed
	 * @noinspection PhpUnused
	 */
	public function modify_transient( $transient ) {
		// Did WordPress check for updates?
		if ( property_exists( $transient, 'checked' ) && $transient->checked ) {
			$is_out_of_date = version_compare(
				$this->get_latest_release_property( self::GITHUB_RELEASE_VERSION ),
				$transient->checked[ $this->get_plugin_basename() ], 'gt'
			);

			if ( $is_out_of_date ) {
				$download_url = $this->get_latest_release_download_url();

				if ( $download_url ) {
					// If the release doesn't have a downloadable asset, skip the update.
					$transient->response[ $this->get_plugin_basename() ] = (object) [
						'url' => $this->get_plugin_property( self::PLUGIN_PROP_URI ),
						'slug' => $this->get_plugin_slug(),
						'package' => $download_url,
						'new_version' => $this->get_latest_release_property( self::GITHUB_RELEASE_VERSION ),
					];
				}
			}
		}

		return $transient;
	}


	private function get_plugin_slug(): string {
		return current( explode( '/', $this->get_plugin_basename(), 2 ) );
	}


	/**
	 * Add links to plugin table.
	 *
	 * @param $links
	 * @param $file
	 *
	 * @return mixed
	 */
	public function filter_plugin_links( $links, $file ) {
		// Are we looking at the plugin row of our plugin?
		if ( $file === $this->get_plugin_basename() ) {
			$links[] = sprintf(
				'<a href="%s" rel="noopener" target="_blank">%s</a>',
				$this->get_plugin_property( self::PLUGIN_PROP_URI ),
				__( 'Visit plugin site', 'toolset-cli' )
			);
		}

		return $links;
	}


	/**
	 * Add modal popup for "View Details" link in plugin table.
	 *
	 * @param $result
	 * @param $action
	 * @param $args
	 *
	 * @return object
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( isset( $args->slug ) && $args->slug === $this->get_plugin_slug() ) {
			$this->get_latest_release_info(); // Get our repo info

			return (object) [
				'name' => $this->get_plugin_property( self::PLUGIN_PROP_NAME ),
				'slug' => $this->get_plugin_basename(),
				'version' => $this->get_latest_release_property( self::GITHUB_RELEASE_VERSION ),
				'author' => $this->get_plugin_property( self::PLUGIN_PROP_AUTHOR_NAME ),
				'author_profile' => $this->get_plugin_property( self::PLUGIN_PROP_AUTHOR_URI ),
				'last_updated' => $this->get_latest_release_property( self::GITHUB_RELEASE_DATE ),
				'homepage' => $this->get_plugin_property( self::PLUGIN_PROP_URI ),
				'short_description' => wp_kses_allowed_html( $this->get_plugin_property( self::PLUGIN_PROP_DESCRIPTION ) ),
				'sections' => [
					'Description' => $this->get_plugin_description_for_popup(),
				],
				'download_link' => $this->get_latest_release_download_url(),
			];
		}

		return $result;
	}


	private function get_plugin_description_for_popup(): string {
		return sprintf(
			'<p>%s</p><p><strong><a href="%s" target="_blank" rel="nofollow">%s</a></strong></p>',
			wp_kses( $this->get_plugin_property( self::PLUGIN_PROP_DESCRIPTION ), 'post' ),
			$this->get_plugin_property( self::PLUGIN_PROP_URI ),
			__( 'Visit the plugin homepage for changelog and further details', 'toolset-cli' )
		);
	}

	/**
	 * Execute actions right after the plugin has been installed.
	 *
	 * @param $response
	 * @param $hook_extra
	 * @param $result
	 *
	 * @return mixed
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUnused
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		// Move files to the plugin directory and set the destination for the rest of the stack.
		$install_directory = plugin_dir_path( $this->plugin_filename_path );
		$wp_filesystem->move( $result['destination'], $install_directory );
		$result['destination'] = $install_directory;

		if ( is_plugin_active( $this->get_plugin_basename() ) ) {
			activate_plugin( $this->get_plugin_basename() );
		}

		return $result;
	}

}
