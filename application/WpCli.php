<?php

namespace OTGS\Toolset\CLI;

/**
 * WP-CLI API wrapper.
 *
 * Prevents depending on static methods in our code, improves testability.
 *
 * @since 1.1
 */
class WpCli {


	private $is_porcelain = false;


	public function set_porcelain( $is_porcelain = true ) {
		$this->is_porcelain = (bool) $is_porcelain;
	}


	/**
	 * Display error message prefixed with "Error: " and exit script.
	 *
	 * @param string $message
	 * @param bool $exit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function error( $message, $exit = false ) {
		if ( $this->is_porcelain ) {
			if ( $exit ) {
				exit( 1 );
			}

			return;
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		\WP_CLI::error( $message, $exit );
	}


	/**
	 * @param string $message
	 */
	public function success( $message ) {
		if ( $this->is_porcelain ) {
			return;
		}
		\WP_CLI::success( $message );
	}


	/**
	 * @param string $message
	 * @param bool $is_porcelain_output
	 */
	public function log( $message, $is_porcelain_output = false ) {
		if ( $this->is_porcelain && ! $is_porcelain_output ) {
			return;
		}
		\WP_CLI::log( $message );
	}


	/**
	 * @param string $message
	 */
	public function warning( $message ) {
		if ( $this->is_porcelain ) {
			return;
		}
		\WP_CLI::warning( $message );
	}


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function green( $string ) {
		return \WP_CLI::colorize( '%G' . $string . '%n' );
	}


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function red( $string ) {
		return \WP_CLI::colorize( '%R' . $string . '%n' );
	}


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function white( $string ) {
		return \WP_CLI::colorize( '%W' . $string . '%n' );
	}


	/**
	 * @param $command
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function runcommand( $command, $options = [] ) {
		return \WP_CLI::runcommand( $command, $options );
	}


	public function confirm( $question, $parameters = [] ) {
		return \WP_CLI::confirm( $question, $parameters );
	}
}
