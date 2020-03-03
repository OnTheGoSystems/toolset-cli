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
	public function error( $message, $exit = true ) {
		/** @noinspection PhpUnhandledExceptionInspection */
		\WP_CLI::error( $message, $exit );
	}


	/**
	 * @param string $message
	 */
	public function success( $message ) {
		\WP_CLI::success( $message );
	}
}
