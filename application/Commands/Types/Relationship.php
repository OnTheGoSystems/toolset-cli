<?php

namespace OTGS\Toolset\CLI\Types;

/**
 * Relationship commands.
 */
class Relationship extends TypesCommand {

	/**
	 * Displays a list of relationships.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : The format of the output. Can take values: table, csv, json, count, yaml. Default: table.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship list --format=json
	 *
	 * @subcommand list
	 * @synopsis [--format=<format>]
	 *
	 * @since 1.0
	 */
	public function list_items( $args, $assoc_args ) {
		$defaults = array(
			'format' => 'table',
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$items = $this->get_items();

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, $this->get_columns() );
	}

	/**
	 * Get all relationships.
	 *
	 * @return array
	 */
	private function get_items() {
		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$definitions = $definition_repository->get_definitions();

		$return_items = array();
		foreach ( $definitions as $definition ) {
			$first_limit = ( $definition->get_cardinality()->get_parent( 'max' ) == - 1 ) ? "*" : $definition->get_cardinality()->get_parent( 'max' );
			$second_limit = ( $definition->get_cardinality()->get_child( 'max' ) == - 1 ) ? "*" : $definition->get_cardinality()->get_child( 'max' );

			$return_items[] = array(
				'slug' => $definition->get_slug(),
				'singular' => $definition->get_display_name_singular(),
				'plural' => $definition->get_display_name_plural(),
				'first_post_type' => $definition->get_child_type()->get_types()[0],
				'second_post_type' => $definition->get_parent_type()->get_types()[0],
				'limits' => $first_limit . '..' . $second_limit,
				'origin' => $definition->get_origin()->get_origin_keyword(),
				'active' => $definition->is_active() ? __( 'Yes', 'toolset-cli' ) : __( 'No', 'toolset-cli' ),
			);
		}

		return $return_items;
	}

	/**
	 * Returns the columns of the list command.
	 *
	 * @return string[] The columns of the list command.
	 */
	protected function get_columns() {
		$columns = array(
			'slug',
			'singular',
			'plural',
			'first_post_type',
			'second_post_type',
			'limits',
			'origin',
			'active',
		);

		return $columns;
	}

	/**
	 * Creates a relationship between two post types.
	 *
	 * ## OPTIONS
	 *
	 * [--first=<cpt>]
	 * : The first post type slug. Required.
	 *
	 * [--second=<cpt>]
	 * : The second post type slug. Required.
	 *
	 * [--slug=<string>]
	 * : The relationship slug. Required.
	 *
	 * [--cardinality=<string>]
	 * : Relationship type: Many to many, one to many or one to one. Can take values: *..*, <number>..*,
	 * <number>..<number>. Defaults to *..*
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship create --first=post --second=attachment --slug=featured-video --cardinality=1..*
	 *
	 * @subcommand create
	 * @synopsis [--first=<string>] [--second=<string>] [--slug=<string>] [--cardinality=<string>]
	 *
	 * @since 1.0
	 */
	public function create( $args, $assoc_args ) {
		$defaults = array(
			'first' => 'post',
			'second' => 'page',
			'slug' => 'post-page',
			'cardinality' => '*..*',
		);
		$relationship_args = wp_parse_args( $assoc_args, $defaults );

		$definition_extra = array(
			'name' => $relationship_args['slug'],
			'singular_name' => $relationship_args['slug'],
			'cardinality' => $relationship_args['cardinality'],
		);

		$definition = $this->create_item( $relationship_args['slug'], $relationship_args['first'], $relationship_args['second'], $definition_extra );
		\WP_CLI::success( __( 'Created relationship.', 'toolset-cli' ) );
	}

	/**
	 * Bulk generates relationships.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many items to generate. Default: 10
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship generate --count=100
	 *
	 * @subcommand generate
	 * @synopsis [--count=<number>]
	 *
	 * @since 1.0
	 */
	public function generate( $args, $assoc_args ) {
		$defaults = array(
			'count' => 10,
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Generating relationships', 'toolset-cli' ), $assoc_args['count'] );
		for ( $i = 0; $i < $assoc_args['count']; $i ++ ) {
			$this->create_item();
			$progress->tick();
		}
		$progress->finish();
	}

	/**
	 * Creates a new relationship definition.
	 *
	 * @param string $slug Relationship slug.
	 * @param string $first_post_type_slug The first post type slug.
	 * @param string $second_post_type_slug The second post type slug.
	 * @param Mixed[] $extra Extra data: [
	 *                                                name: Definition name
	 *                                                singular_name: Definition singular name
	 *                                                cardinality: example: *..*
	 *                                              ]
	 *
	 * @return \Toolset_Relationship_Definition
	 */
	private function create_item( $slug = 'post-page', $first_post_type_slug = 'post', $second_post_type_slug = 'page', $extra = array() ) {
		if ( empty ( $slug ) ) {
			$slug = \OTGS\Toolset\CLI\get_random_string();
		}
		if ( $this->get_post_type( $first_post_type_slug ) == null ) {
			\WP_CLI::error( __( 'First post type does not exist.', 'toolset-cli' ) );
		}
		if ( $this->get_post_type( $second_post_type_slug ) == null ) {
			\WP_CLI::error( __( 'Second post type does not exist.', 'toolset-cli' ) );
		}

		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$parent_post_type = \Toolset_Relationship_Element_Type::build_for_post_type( $first_post_type_slug );
		$child_post_type = \Toolset_Relationship_Element_Type::build_for_post_type( $second_post_type_slug );
		$definition = $definition_repository->create_definition( $slug, $parent_post_type, $child_post_type );

		if ( isset( $extra['name'] ) ) {
			$definition->set_display_name( $extra['name'] );
		}

		if ( isset( $extra['singular_name'] ) ) {
			$definition->set_display_name_singular( $extra['singular_name'] );
		}

		if ( isset( $extra['cardinality'] ) ) {

			$cardinality_parts = explode( '..', $extra['cardinality'] );

			if ( 2 === count( $cardinality_parts ) ) {
				$extra['cardinality'] = '0..' . $cardinality_parts[0] . ':' . '0..' . $cardinality_parts[1];
			} else {
				\WP_CLI::error( __( 'Please enter a valid cardinality: *..*, <number>..*, <number>..<number>.', 'toolset-cli' ) );
			}

			try {
				$cardinality = \Toolset_Relationship_Cardinality::from_string( $extra['cardinality'] );
				$definition->set_cardinality( new \Toolset_Relationship_Cardinality( $cardinality->get_parent(), $cardinality->get_child() ) );
			} catch ( \InvalidArgumentException $e ) {
				\WP_CLI::error( __( 'Please enter a valid cardinality: *..*, <number>..*, <number>..<number>.', 'toolset-cli' ) );
			}
		}

		//We always set as distinct for now, this will parameterized when it is supported by the GUI.
		$definition->is_distinct( true );

		$definition_repository->persist_definition( $definition );

		return $definition;
	}

	/**
	 * Deletes all existing relationships.
	 *
	 * ## OPTIONS
	 *
	 * [--cleanup=<bool>]
	 * : Whether to delete related associations, intermediary post type and the intermediary post field group, if they
	 * exist. Defaults to true.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship empty --cleanup=false
	 *
	 * @subcommand empty [--cleanup=<bool>]
	 *
	 * @since 1.0
	 */
	public function empty_items( $args, $assoc_args ) {
		$defaults = array(
			'cleanup' => true,
		);
		$delete_args = wp_parse_args( $assoc_args, $defaults );

		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$definitions = $definition_repository->get_definitions();

		if ( ! empty( $definitions ) ) {
			$progress = \WP_CLI\Utils\make_progress_bar( __( 'Deleting relationships', 'toolset-cli' ), count( $definitions ) );
			foreach ( $definitions as $definition ) {
				$this->delete_item( $definition->get_slug(), $delete_args['cleanup'] );
				$progress->tick();
			}
			$progress->finish();
		} else {
			\WP_CLI::warning( __( 'There are no relationships to delete.', 'toolset-cli' ) );
		}
	}

	/**
	 * Deletes a relationship.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the relationship.
	 *
	 * [--cleanup=<bool>]
	 * : Whether to delete related associations, intermediary post type and the intermediary post field group, if they
	 * exist. Defaults to true.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship delete relationship-slug --cleanup=false
	 *
	 * @subcommand delete
	 * @synopsis <slug> [--cleanup=<bool>]
	 *
	 * @since 1.0
	 */
	public function delete( $args, $assoc_args ) {
		$defaults = array(
			'cleanup' => true,
		);
		$delete_args = wp_parse_args( $assoc_args, $defaults );

		list( $slug ) = $args;

		if ( empty( $slug ) ) {
			\WP_CLI::error( __( 'You must specify a relationship slug.', 'toolset-cli' ) );
		}

		$delete_result = $this->delete_item( $slug, $delete_args['cleanup'] );

		if ( $delete_result ) {
			\WP_CLI::success( __( 'Deleted relationship.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'Relationship does not exist.', 'toolset-cli' ) );
		}
	}

	/**
	 * Deletes a relationship.
	 *
	 * @param string $slug The slug of the relationship.
	 * @param bool $do_cleanup true to delete related associations, intermediary post type and the intermediary post
	 *     field group, if they exist.
	 */
	protected function delete_item( $slug, $do_cleanup = true ) {
		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$definition = $definition_repository->get_definition( $slug );

		if ( is_null( $definition ) ) {
			return false;
		}

		if ( ! is_bool( $do_cleanup ) ) {
			$do_cleanup = $do_cleanup === 'false' ? false : true;
		}

		$definition_repository->remove_definition( $definition, $do_cleanup );

		return true;
	}

	/**
	 * Displays posts related to a given post.
	 *
	 * ## OPTIONS
	 *
	 * --post=<number>
	 * : The ID of the post.
	 *
	 *	--relationship=<string>
	 * : The relationship slug.
	 *
	 * [--role-to-return=<string>]
	 * : The role to return.
	 *
	 * [--query-by-role=<string>]
	 * : The role to query by.
	 *
	 * [--format=<format>]
	 * : The format of the output. Can take values: table, csv, json, count, yaml. Default: table.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship get-related-posts --post=123 --relationship=slug
	 *    wp types relationship get-related-posts --post=123 --relationship=slug --role-to-return=child --query-by-role=parent
	 *    wp types relationship get-related-posts --post=123 --relationship=slug --role-to-return=child --query-by-role=parent --format=json
	 *
	 * @subcommand get-related-posts
	 * @synopsis --post=<number> --relationship=<string> [--role-to-return=<string>] [--query-by-role=<string>] [--format=<format>]
	 * @link https://toolset.com/documentation/customizing-sites-using-php/post-relationships-api/#toolset_get_related_posts
	 */
	public function get_related_posts( $args, $assoc_args ) {
		$defaults    = array (
			'role-to-return' => 'child',
			'query-by-role' => 'parent',
			'format' => 'table',
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );
		$relationship_slug = $assoc_args['relationship'];
		$post = $assoc_args['post'] ;

		$query_args = [
			'orderby' => 'title',
			'order' => 'ASC',
			'return' => 'post_id',
			'limit' => 999,
			] ;
		$query_args['role_to_return'] = $assoc_args['role-to-return'];
		$query_args['query_by_role'] = $assoc_args['query-by-role'];

		$related_posts = toolset_get_related_posts ( $post, $relationship_slug, $query_args ) ;

		if ( count($related_posts) > 0 ) {
			$columns = [ 'ID', 'post_title',] ;
			$items = array_map ( function ( $related_post ) {
				return [
					'ID' => $related_post,
					'post_title' => get_the_title ($related_post)
				] ;
			}, $related_posts) ;

			\WP_CLI\Utils\format_items( $assoc_args['format'], $items, $columns );
		}
	}
}