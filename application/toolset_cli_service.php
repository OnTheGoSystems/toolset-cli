<?php

namespace OTGS\Toolset\CLI;

/**
 * The service that registers Toolset CLI commands.
 *
 * @since 1.0
 */
class Toolset_CLI_Service {

	/**
	 * @var array Command names as keys, handler class as values. Commands are grouped per plugin.
	 */
	private static $commands = array(

		'types' => array(
			'types' => '\OTGS\Toolset\CLI\Types\Types',
			'posttype' => '\OTGS\Toolset\CLI\Types\Post_Type',
			'field group' => '\OTGS\Toolset\CLI\Types\Field_Group',
			'relationship' => '\OTGS\Toolset\CLI\Types\Relationship',
			'association' => '\OTGS\Toolset\CLI\Types\Association',
		),
		'views' => array(
			'archive' => '\OTGS\Toolset\CLI\Views\WPA',
			'view' => '\OTGS\Toolset\CLI\Views\View',
			'template' => '\OTGS\Toolset\CLI\Views\CT',
		),
		'post' => array(
			'post' => '\OTGS\Toolset\CLI\Thirdparty\Post\Extra',
		),
		'wpml' => array(
			'translation' => '\OTGS\Toolset\CLI\Thirdparty\WPML\Translation',
		),
		'csv' => array(
			'csv' => '\OTGS\Toolset\CLI\Thirdparty\CSV\Import',
		),
	);

	/** @var Toolset_CLI_Service */
	private static $instance;

	private static $non_plugin_commands = array( 'post' );


	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public static function initialize() {
		$instance = self::get_instance();

		$instance->register_commands();
	}


	private $commands_registered = false;


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
					\WP_CLI::error( sprintf( __( '%s is not active.', 'toolset-cli' ), ucfirst( $plugin_name ) ) );
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
			default:
				return true;
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
