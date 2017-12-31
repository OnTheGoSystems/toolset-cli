<?php

namespace Toolset_CLI;

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
			'types' => '\Toolset_CLI\Types\Types',
			'posttype' => '\Toolset_CLI\Types\Post_Type',
			'field group' => '\Toolset_CLI\Types\Field_Group',
			'relationship' => '\Toolset_CLI\Types\Relationship',
			'association' => '\Toolset_CLI\Types\Association',
		),
		'views' => array(
			'archive'  => '\Toolset_CLI\Views\WPA',
			'view'     => '\Toolset_CLI\Views\View',
			'template' => '\Toolset_CLI\Views\CT'
		),
		'post'  => array(
			'post' => '\Toolset_CLI\Post\Extra_Post_Commands'
		)
	);

	private static $instance;
	private static $non_plugin_commands = array( 'post' );

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __clone() {
	}

	private function __construct() {
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
				if ( $command_name != $plugin_name ) {
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
	 */
	private function add_command( $command_name, $handler_class_name, $plugin_name ) {
		\WP_CLI::add_command( $command_name, $handler_class_name, array(
			'before_invoke' => function () use ( $plugin_name ) {
				if ( ! in_array( $plugin_name, self::$non_plugin_commands ) && ! $this->is_plugin_active( $plugin_name ) ) {
					\WP_CLI::error( sprintf( __( '%s is not active.', 'toolset-cli' ), ucfirst( $plugin_name ) ) );
				}
			},
		) );
	}

	/**
	 * Checks if the plugin of the corresponding command is active.
	 *
	 * @return bool Whether the plugin is active.
	 */
	private function is_plugin_active( $plugin ) {
		switch ( $plugin ) {
			case 'types':
				return ( apply_filters( 'types_is_active', false ) && $this->is_m2m_active() );
				break;
			case 'views':
				return defined( 'WPV_VERSION' );
				break;
		}

		return false;
	}

	/**
	 * Checks if m2m is active.
	 *
	 * @return bool Whether m2m is active.
	 */
	private function is_m2m_active() {
		if ( ! apply_filters( 'toolset_is_m2m_enabled', false ) ) {
			\WP_CLI::error( __( 'm2m is not active.', 'toolset-cli' ) );

			return false;
		}
		if ( ! defined( 'TOOLSET_VERSION' ) || version_compare( TOOLSET_VERSION, "2.5.3" ) < 0 ) {
			\WP_CLI::error( __( 'Toolset Common version is old, please update to the latest one.', 'toolset-cli' ) );

			return false;
		}

		return true;
	}

}
