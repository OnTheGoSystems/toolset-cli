<?php

namespace Toolset_CLI\Views;

use Toolset_CLI\Toolset_Command;

use WPV_WordPress_Archive;
use WPV_View;
use WPV_Settings;

abstract class Views_Commands extends Toolset_Command {

	/**
	 * Creates an empty Draft post to be used in creating Views
	 *
	 * @return int|\WP_Error
	 */
	protected function touch_post() {
		return wp_insert_post( array(
			'post_title' => 'Draft CLI Post'
		) );
	}

	/**
	 * Verifies that a given Views is a WPA
	 *
	 * @param WPV_WordPress_Archive $wpa
	 */
	protected function verify_is_wpa( $wpa ) {
		if ( ! $wpa->is_a_wordpress_archive() ) {
			\WP_CLI::error( __( 'Provided id doesn\'t belong to a WPA', 'toolset-cli' ) );
		}
	}


	/**
	 * Outputs --porcelain value (ids) or the provided success fallback messasge
	 *
	 * @param mixed $result
	 * @param array $args
	 * @param string $fallback
	 */
	protected function output_result( $result, $args, $fallback ) {
		if ( in_array( 'porcelain', $args ) ) {
			\WP_CLI::print_value( $result );
		} else {
			\WP_CLI::success( __( $fallback, 'toolset-cli' ) );
		}
	}
}