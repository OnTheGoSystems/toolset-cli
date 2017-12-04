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
	 * @param string $post_type_slug
	 *
	 * @return IToolset_Post_Type|null Post type model or null if it doesn't exist.
	 */
	protected function get_post_type( $post_type_slug ) {
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();

		return $post_type_repository->get( $post_type_slug );
	}
}
