<?php

namespace OTGS\Toolset\CLI;

use OTGS\Toolset\CLI\Types\Field;
use OTGS\Toolset\CLI\Thirdparty\CSV\Import;
use OTGS\Toolset\CLI\Thirdparty\WPML\Translation;
use OTGS\Toolset\CLI\Thirdparty\Post\Extra;
use OTGS\Toolset\CLI\Views\CT;
use OTGS\Toolset\CLI\Views\View;
use OTGS\Toolset\CLI\Views\WPA;
use OTGS\Toolset\CLI\Types\Association;
use OTGS\Toolset\CLI\Types\Relationship;
use OTGS\Toolset\CLI\Types\FieldGroup;
use OTGS\Toolset\CLI\Types\PostType;
use OTGS\Toolset\CLI\Types\Types;
use OTGS\Toolset\CLI\Commands\Toolset\Relationships;
use WP_CLI;
use OTGS\Toolset\CLI\Views\Export;

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
			'relationships' => Relationships::class,
		],
		'types' => [
			'types' => Types::class,
			'posttype' => PostType::class,
			'field' => Field::class,
			'field group' => FieldGroup::class,
			'relationship' => Relationship::class,
			'association' => Association::class,
		],
		'views' => [
			'archive' => WPA::class,
			'view' => View::class,
			'template' => CT::class,
			'export' => Export::class,
			'import' => Views\Import::class,
		],
		'post' => [
			'post' => Extra::class,
		],
		'wpml' => [
			'translation' => Translation::class,
		],
		'csv' => [
			'csv' => Import::class,
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
		WP_CLI::add_command( $command_name, $handler_class_name, [
			'before_invoke' => function () use ( $plugin_name ) {
				if (
					! in_array( $plugin_name, self::$non_plugin_commands, true )
					&& ! $this->is_plugin_active( $plugin_name )
				) {
					WP_CLI::error( sprintf(
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
			WP_CLI::error( __( 'm2m is not active.', 'toolset-cli' ) );

			return false;
		}
		if ( ! defined( 'TOOLSET_VERSION' ) || version_compare( TOOLSET_VERSION, '2.5.3' ) < 0 ) {
			/** @noinspection PhpUnhandledExceptionInspection */
			WP_CLI::error( __( 'Toolset Common version is old, please update to the latest one.', 'toolset-cli' ) );

			return false;
		}

		return true;
	}

}
