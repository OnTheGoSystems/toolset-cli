<?php

namespace OTGS\Toolset\CLI\Commands;

use OTGS\Toolset\CLI\WpCli;
use OTGS\Toolset\Common\Auryn\InjectionException;

/**
 * The base class for Toolset commands.
 *
 * @since 1.0
 */
abstract class ToolsetCommand extends \WP_CLI_Command {

	/** @var WpCli */
	private $wp_cli;


	/**
	 * @return WpCli
	 */
	protected function wp_cli() {
		if ( null === $this->wp_cli ) {
			$this->wp_cli = new WpCli();
		}

		return $this->wp_cli;
	}


	protected function get_subcommand( $parameters, $accepted_commands ) {
		foreach ( $accepted_commands as $command ) {
			if ( array_key_exists( $command, $parameters ) ) {
				return $command;
			}
		}

		return null;
	}


	/**
	 * @param $class_name
	 *
	 * @return object|null
	 */
	protected function toolset_dic_make( $class_name ) {
		if ( ! function_exists( '\toolset_dic' ) ) {
			$this->wp_cli()
				->warning( __( 'You\'re dealing with a very old version of Toolset. Things probably won\'t work as expected.', 'toolset-cli' ) );

			return null;
		}

		$dic = toolset_dic();
		try {
			return $dic->make( $class_name );
		} catch ( InjectionException $e ) {
			$this->wp_cli()->warning( sprintf( __( 'Unable to instantiate class "%s"', 'toolset-cli' ), $class_name ) );
			return null;
		}
	}

}
