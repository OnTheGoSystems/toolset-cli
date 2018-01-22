<?php

namespace Toolset_CLI\Types;

use \Toolset_CLI\Toolset_Command;

/**
 * The base class for Types commands.
 *
 * @package Toolset_CLI\Types
 */
abstract class Types_Command extends Toolset_Command {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		do_action( 'toolset_do_m2m_full_init' );
	}

	/**
	 * Get a post type model.
	 *
	 * @param string $slug The post type slug.
	 * @param bool $from_types Whether it is a Types post type. Default: false.
	 *
	 * @return \IToolset_Post_Type|\IToolset_Post_Type_From_Types|null Post type model or null if it doesn't exist.
	 */
	protected function get_post_type( $slug, $from_types = false ) {
		if ( empty( $slug ) ) {
			\WP_CLI::error( __( 'You must specify a post type slug.', 'toolset-cli' ) );
		}
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$post_type = null;

		if ( $from_types ) {
			$post_type = $post_type_repository->get_from_types( $slug );
		} else {
			$post_type = $post_type_repository->get( $slug );
		}

		if ( empty( $post_type ) ) {
			\WP_CLI::error( __( 'Post type does not exist.', 'toolset-cli' ) );
		}

		return $post_type;
	}
}
