<?php

namespace Toolset_CLI\Thirdparty\Post;

/**
 * Extra post commands.
 *
 * @package Toolset_CLI\Thirdparty\Post
 */
class Extra extends \Post_Command {

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

	/**
	 * Assigns a parent post.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the child post
	 *
	 * * <parent_id>
	 * : The ID for the parent post
	 *
	 * ## EXAMPLES
	 *
	 *    wp post set_parent 1 2
	 *
	 * @subcommand set_parent
	 * @synopsis <id> <parent_id>
	 *
	 * @since 1.0
	 */
	public function set_parent( $args, $assoc_args ) {
		list( $id, $parent_id ) = $args;

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a child post id', 'toolset-cli' ) );
		}

		if ( ! is_numeric( $parent_id ) ) {
			\WP_CLI::error( __( 'Please provide a parent post id', 'toolset-cli' ) );
		}

		$update_post = wp_update_post(
			array(
				'ID'          => $id,
				'post_parent' => $parent_id
			)
		);

		if ( ! ( $update_post instanceof \WP_Error ) ) {
			\WP_CLI::success( __( 'Post updated successfully', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'There was an error while assigning a parent to the post.', 'toolset-cli' ) );
		}
	}

}