<?php

namespace Toolset_CLI\Views;

use WPV_WordPress_Archive;
use WPV_Settings;

/**
 * Views WordPress Archives commands.
 *
 * @package Toolset_CLI\Views
 */
class WPA extends Views_Common {

	private static $allowed_purpose = array( 'all', 'parametric' );

	/**
	 * Creates a new WPA.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<string>]
	 * : The name of the WPA. Default: random string.
	 *
	 * [--view_purpose=<string>]
	 * : The WordPress archive purpose
	 * Can take values: all, parametric. Default: all.
	 *
	 * [--usage_type=<string>]
	 * : The usage group type.
	 * Can take values: standard, taxonomies, post_types.
	 *
	 * [--orderby=<string>]
	 * : Field slug for WPA to be ordered by
	 *
	 * [--order=<string>]
	 * : Order direction
	 * Can take values: DESC, ASC
	 *
	 * [--porcelain]
	 * : prints the created WPA id
	 *
	 * ## EXAMPLES
	 *
	 *    wp views archive create --name="Test Thingy" --usage_type="standard" --usage="home-blog-page" --orderby="post_title" --order="ASC" --porcelain
	 *
	 * @subcommand create
	 * @synopsis [--name=<string>] [--usage_type=<string>] [--usage=<string>] [--orderby=<string>] [--view_purpose=<string>] [--order=<string>] [--porcelain]
	 *
	 * @since 1.1
	 */
	public function create( $args, $assoc_args ) {

		$defaults = array(
			'name'         => \Toolset_CLI\get_random_string(),
			'view_purpose' => 'all',
			'usage_type'   => 'standard',
			'usage'        => 'home-blog-page',
			'orderby'      => 'post_date',
			'order'        => 'DESC',
			'output_id'    => false
		);

		$create_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! in_array( $create_args['view_purpose'], self::$allowed_purpose ) ) {
			\WP_CLI::warning( __( 'Using unsupported WPA purpose', 'toolset-cli' ) );
			$create_args['view_purpose'] = 'all';
		}

		try {
			$wpa = WPV_WordPress_Archive::create( WPV_WordPress_Archive::get_unique_title( $create_args['name'] ), $create_args );
			$this->update_assignment_option( $wpa->id, $this->format_usage( $create_args['usage'], $create_args['usage_type'] ) );

			if ( $wpa->id !== null ) {
				$this->output_result( $wpa->id, $create_args, 'WordPress Archive created' );
			} else {
				\WP_CLI::error( __( 'Could not create WordPress Archive.', 'toolset-cli' ) );
			}

		} catch ( Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new Views instance.', 'toolset-cli' ) );
		}

	}

	/**
	 * Updates the View assignment by modifying the global wpv_options option, and appending the usage to it
	 *
	 * @param int $view
	 * @param string|array $usage
	 *
	 * @return bool
	 */
	protected function update_assignment_option( $view, $usage ) {
		$formatted_usage = array();

		if ( ! is_array( $usage ) ) {
			$usage = array( $usage );
		}

		foreach ( $usage as $usage_string ) {
			$formatted_usage[ $usage_string ] = $view;
		}

		$wpv_option = get_option( WPV_Settings::OPTION_NAME, array() );

		return update_option( WPV_Settings::OPTION_NAME, array_merge( $wpv_option, $formatted_usage ) );
	}

	/**
	 * Formats the WPA usage by adding correct prefixes based on usage_type
	 *
	 * @param string $usage
	 * @param string $usage_type
	 *
	 * @return string
	 */
	protected function format_usage( $usage, $usage_type ) {
		switch ( $usage_type ) {
			case"post_types":
				return sprintf( 'view_cpt_%s', $usage );
			case"standard":
				return sprintf( 'view_%s', $usage );
			case"taxonomies":
				return sprintf( 'view_taxonomy_loop_%s', $usage );
			default:
				\WP_CLI::error( sprintf( __( 'Unsupported view usage type %s', 'toolset-cli' ), ucfirst( $usage_type ) ) );
				break;
		}
	}
}