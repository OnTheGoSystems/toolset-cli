<?php

namespace OTGS\Toolset\CLI\Commands;

use OTGS\Toolset\CLI\WpCli;

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

}
