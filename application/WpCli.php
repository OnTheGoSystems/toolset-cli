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

	/**
	 * Display error message prefixed with "Error: " and exit script.
	 *
	 * @param string $message
	 * @param bool $exit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function error( $message, $exit = false ) {
		/** @noinspection PhpUnhandledExceptionInspection */
		\WP_CLI::error( $message, $exit );
	}


	/**
	 * @param string $message
	 */
	public function success( $message ) {
		\WP_CLI::success( $message );
	}


	/**
	 * @param string $message
	 */
	public function log( $message ) {
		\WP_CLI::log( $message );
	}


	/**
	 * @param string $message
	 */
	public function warning( $message ) {
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
}
