<?php

namespace OTGS\Toolset\CLI\Thirdparty\WPML;

use \OTGS\Toolset\CLI\Toolset_Command;

/**
 * WPML commands.
 */
class Translation extends Toolset_Command {

	/**
	 * Creates a translation of a post.
	 *
	 * ## OPTIONS
	 *
	 * [--post=<string>]
	 * : The post to create the translation of. Required.
	 *
	 * [--language=<string>]
	 * : The language of the translation. Required.
	 *
	 * [--title=<string>]
	 * : The title of the translation.
	 *
	 * [--content=<string>]
	 * : The content of the translation.
	 *
	 * ## EXAMPLES
	 *
	 *    wp wpml translation add --post=12 --post_type=book --language="el" --title="translated post" --content="this
	 * is the translated post"
	 *
	 * @subcommand add
	 * @synopsis [--post=<string>] [--language=<string>] [--title=<string>] [--content=<string>]
	 *
	 * @since 1.0
	 */
	public function add( $args, $assoc_args ) {
		$defaults = array(
			'post' => null,
			'language' => null,
			'title' => \OTGS\Toolset\CLI\get_random_string(),
			'content' => \OTGS\Toolset\CLI\get_random_string(),
		);
		$list_args = wp_parse_args( $assoc_args, $defaults );

		if ( empty( $list_args['post'] ) ) {
			\WP_CLI::error( __( 'You must specify a post.', 'toolset-cli' ) );
		}

		if ( empty( $list_args['language'] ) ) {
			\WP_CLI::error( __( 'You must specify a language.', 'toolset-cli' ) );
		}

		if ( false === get_post_status( $list_args['post'] ) ) {
			\WP_CLI::error( __( 'You must specify a valid post ID.', 'toolset-cli' ) );
		}

		$post_type = get_post_type( $list_args['post'] );
		$translated_post_data = array(
			'post_title'    => $list_args['title'],
			'post_content'  => $list_args['content'],
			'post_status'   => 'publish',
			'post_type'   => $post_type,
		);
		$translated_post_id = wp_insert_post( $translated_post_data );

		global $sitepress;
		$translation_id = $sitepress->set_element_language_details( $translated_post_id, 'post_' . $post_type, $list_args['post'], $list_args['language'] );

		if ( ! empty ( $translation_id ) ) {
			\WP_CLI::success( __( 'Created translation.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'Could not create translation.', 'toolset-cli' ) );
		}
	}

}
