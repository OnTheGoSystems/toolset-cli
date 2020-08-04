<?php

namespace OTGS\Toolset\CLI;

/**
 * The service that registers Toolset CLI commands.
 *
 * @since 1.0
 */
class Bootstrap {

	/**
	 * @var array Command names as keys, handler class as values. Commands are grouped per plugin.
	 */
	private static $commands = [
		'toolset' => [
			'relationships' => '\OTGS\Toolset\CLI\Commands\Toolset\Relationships',
		],
		'types' => [
			'types' => '\OTGS\Toolset\CLI\Types\Types',
			'posttype' => '\OTGS\Toolset\CLI\Types\Post_Type',
			'field group' => '\OTGS\Toolset\CLI\Types\Field_Group',
			'relationship' => '\OTGS\Toolset\CLI\Types\Relationship',
			'association' => '\OTGS\Toolset\CLI\Types\Association',
		],
		'views' => [
			'archive' => '\OTGS\Toolset\CLI\Views\WPA',
			'view' => '\OTGS\Toolset\CLI\Views\View',
			'template' => '\OTGS\Toolset\CLI\Views\CT',
		],
		'post' => [
			'post' => '\OTGS\Toolset\CLI\Thirdparty\Post\Extra',
		],
		'wpml' => [
			'translation' => '\OTGS\Toolset\CLI\Thirdparty\WPML\Translation',
		],
		'csv' => [
			'csv' => '\OTGS\Toolset\CLI\Thirdparty\CSV\Import',
		],
	];


	/** @var string[] */
	private static $non_plugin_commands = array( 'post' );


	/** @var bool */
	private $commands_registered = false;


	/**
	 * Setup the WP-CLI to work with this plugin.
	 */
	public function initialize() {
		$this->register_commands();
	}


	/**
	 * Registers all available commands.
	 */
	private function register_commands() {

		if ( $this->commands_registered ) {
			return;
		}

		foreach ( self::$commands as $plugin_name => $commands ) {
			foreach ( $commands as $command_name => $handler_class_name ) {
				if ( $command_name !== $plugin_name ) {
					$command_name = $plugin_name . ' ' . $command_name;
				}
				$this->add_command( $command_name, $handler_class_name, $plugin_name );
			}
		}

		$this->commands_registered = true;
	}


	/**
	 * Registers a specific command.
	 *
	 * @param string $command_name The name of the command.
	 * @param string $handler_class_name The name of the handler class.
	 * @param string $plugin_name The name of the plugin.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	private function add_command( $command_name, $handler_class_name, $plugin_name ) {
		\WP_CLI::add_command( $command_name, $handler_class_name, [
			'before_invoke' => function () use ( $plugin_name ) {
				if (
					! in_array( $plugin_name, self::$non_plugin_commands, true )
					&& ! $this->is_plugin_active( $plugin_name )
				) {
					\WP_CLI::error( sprintf(
						/* translators: Plugin name. */
						__( '%s is not active.', 'toolset-cli' ),
						ucfirst( $plugin_name )
					) );
				}
			},
		] );
	}


	/**
	 * Checks if the plugin of the corresponding command is active.
	 *
	 * @param string $plugin Plugin slug.
	 *
	 * @return bool Whether the plugin is active.
	 */
	private function is_plugin_active( $plugin ) {
		switch ( $plugin ) {
			case 'types':
				return ( apply_filters( 'types_is_active', false ) && $this->is_m2m_active() );
			case 'views':
				return defined( 'WPV_VERSION' );
			case 'wpml':
				return ( apply_filters( 'toolset_is_wpml_active_and_configured', false ) );
			case 'toolset':
				return apply_filters( 'types_is_active', false ) || $this->is_plugin_active( 'views' );
			case 'csv':
				return true;
			default:
				return false;
		}
	}


	/**
	 * Checks if m2m is active.
	 *
	 * @return bool Whether m2m is active.
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	private function is_m2m_active() {
		if ( ! apply_filters( 'toolset_is_m2m_enabled', false ) ) {
			/** @noinspection PhpUnhandledExceptionInspection */
			\WP_CLI::error( __( 'm2m is not active.', 'toolset-cli' ) );

			return false;
		}
		if ( ! defined( 'TOOLSET_VERSION' ) || version_compare( TOOLSET_VERSION, '2.5.3' ) < 0 ) {
			/** @noinspection PhpUnhandledExceptionInspection */
			\WP_CLI::error( __( 'Toolset Common version is old, please update to the latest one.', 'toolset-cli' ) );

			return false;
		}

		return true;
	}

}
