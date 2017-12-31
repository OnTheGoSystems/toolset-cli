<?php

namespace Toolset_CLI\Post;

/**
 * Views View commands.
 *
 * @package Toolset_CLI\Views
 */
class Extra_Post_Commands extends \Post_Command {

	/**
	 * Sticks a post.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the post that will become sticky
	 *
	 * ## EXAMPLES
	 *
	 *    wp post stick 1
	 *
	 * @subcommand stick
	 * @synopsis <id>
	 *
	 * @since 1.0
	 */
	public function stick( $args, $assoc_args ) {
		list( $id ) = $args;

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a Post id', 'toolset-cli' ) );
		}

		try {

			stick_post( (int) $id );

			\WP_CLI::success( __( 'Stuck post successfully', 'toolset-cli' ) );


		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while sticking the post.', 'toolset-cli' ) );
		}
	}

	/**
	 * Unsticks a post.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the post the will become unstick
	 *
	 * ## EXAMPLES
	 *
	 *    wp post unstick 1
	 *
	 * @subcommand unstick
	 * @synopsis <id>
	 *
	 * @since 1.0
	 */
	public function unstick( $args, $assoc_args ) {
		list( $id ) = $args;

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a Post id', 'toolset-cli' ) );
		}

		try {

			unstick_post( (int) $id );
			\WP_CLI::success( __( 'Unstuck post successfully', 'toolset-cli' ) );

		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while sticking the post.', 'toolset-cli' ) );
		}
	}

}