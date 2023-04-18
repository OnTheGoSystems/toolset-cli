<?php

namespace OTGS\Toolset\CLI\Types;

/**
 * Association commands.
 */
class Association extends TypesCommand {

	/**
	 * Creates an association between two items.
	 *
	 * ## OPTIONS
	 *
	 * [--relationship=<string>]
	 * : The relationship slug.
	 *
	 * [--first=<number>]
	 * : ID of the first item.
	 *
	 * [--second=<number>]
	 * : ID of the second item.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types association create --relationship=relationship-slug --first=12 --second=25
	 *
	 * @subcommand create
	 * @synopsis [--first=<string>] [--second=<string>] [--relationship=<string>]
	 *
	 * @since 1.0
	 */
	public function create( $args, $assoc_args ) {
		$defaults = array(
			'first' => null,
			'second' => null,
			'relationship' => null,
		);

		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		if ( ( $assoc_args['first'] == null ) || ( $assoc_args['second'] == null ) ) {
			\WP_CLI::error( __( 'Please insert valid item IDs.', 'toolset-cli' ) );
		}

		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$definition = $definition_repository->get_definition( $assoc_args['relationship'] );

		if ( ! isset( $assoc_args['relationship'] ) || ( $definition == null ) ) {
			\WP_CLI::error( __( 'Please insert a valid relationship.', 'toolset-cli' ) );
		}

		try {
			$assocation = $definition->create_association( $assoc_args['first'], $assoc_args['second'] );

			if ( is_a( $assocation, 'Toolset_Result' ) && $assocation->is_error() ) {
				\WP_CLI::error( __( 'Could not create association. ' . $assocation->get_message(), 'toolset-cli' ) );
			}
		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'Could not create association. ' . $e->getMessage(), 'toolset-cli' ) );
		}

		\WP_CLI::success( __( 'Created association.', 'toolset-cli' ) );
	}


	/**
	 * Bulk generates associations. Posts involved in associations are created automatically.
	 *
	 * ## OPTIONS
	 *
	 * [--count-first=<number>]
	 * : How many items of the first part involved in the relationship to generate. Default: 1
	 *
	 * [--count-second=<number>]
	 * : How many items of the second part involved in the relationship to generate for each one of the first part.
	 * Default: 10
	 *
	 * [--post=<number>]
	 * : The ID of the first post of the association. If used, count-first parameter should be ommitted. If ommitted, a
	 * new post will be created.
	 *
	 * [--relationship=<string>]
	 * : The relationship slug.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types association generate --count-first=2 --count-second=20 --relationship=relationship-slug
	 *    wp types association generate --post=12 --count-second=40 --relationship=relationship-slug
	 *
	 * @subcommand generate
	 * @synopsis [--count-first=<number>] [--count-second=<number>] [--post=<number>] [--relationship=<string>]
	 *
	 * @since 1.0
	 */
	public function generate( $args, $assoc_args ) {
		$defaults = array(
			'count-first' => 1,
			'count-second' => 10,
			'post' => null,
			'relationship' => null,
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! is_null( $assoc_args['post'] ) && $assoc_args['count-first'] != 1 ) {
			\WP_CLI::error( __( 'First count parameter cannot be set when post parameter is set.', 'toolset-cli' ) );
		}

		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$definition = $definition_repository->get_definition( $assoc_args['relationship'] );

		if ( ! isset( $assoc_args['relationship'] ) || ( $definition == null ) ) {
			\WP_CLI::error( __( 'Please insert a valid relationship.', 'toolset-cli' ) );
		}

		$first_post_type = $definition->get_parent_type()->get_types()[0];
		$second_post_type = $definition->get_child_type()->get_types()[0];

		$wpcli_command_options = array(
			'return' => true,
			'exit_error' => true,
		);

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Generating associations', 'toolset-cli' ), $assoc_args['count-first']
			* $assoc_args['count-second'] );
		for ( $i = 0; $i < $assoc_args['count-first']; $i ++ ) {

			$first_item = $assoc_args['post'];

			if ( is_null( $first_item ) ) {
				$first_item = \WP_CLI::runcommand( 'post create --porcelain --post_status=publish --post_type='
					. $first_post_type
					. ' --post_title='
					. \OTGS\Toolset\CLI\get_random_string(), $wpcli_command_options );
			}

			for ( $j = 0; $j < $assoc_args['count-second']; $j ++ ) {
				$second_item = \WP_CLI::runcommand( 'post create --porcelain --post_status=publish --post_type='
					. $second_post_type
					. ' --post_title='
					. \OTGS\Toolset\CLI\get_random_string(), $wpcli_command_options );

				try {
					$assocation = $definition->create_association( $first_item, $second_item );

					if ( is_a( $assocation, 'Toolset_Result' ) && $assocation->is_error() ) {
						\WP_CLI::warning( __( 'Could not create association. '
							. $assocation->get_message(), 'toolset-cli' ) );
					}
				} catch ( \Exception $e ) {
					\WP_CLI::error( __( 'Could not create association. ' . $e->getMessage(), 'toolset-cli' ) );
				}

				$progress->tick();
			}
		}
		$progress->finish();
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
	 *    wp types association query --post=123 --relationship=slug
	 *    wp types association query --post=123 --relationship=slug --role-to-return=child --query-by-role=parent
	 *    wp types association query --post=123 --relationship=slug --role-to-return=child --query-by-role=parent --format=json
	 *
	 * @subcommand query
	 * @synopsis --post=<number> --relationship=<string> [--role-to-return=<string>] [--query-by-role=<string>] [--format=<format>]
	 * @link https://toolset.com/documentation/customizing-sites-using-php/post-relationships-api/#toolset_get_related_posts
	 */
	public function query( $args, $assoc_args ) {
		$defaults = array (
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
			'limit' => -1,
		] ;
		$query_args['role_to_return'] = $assoc_args['role-to-return'];
		$query_args['query_by_role'] = $assoc_args['query-by-role'];

		$related_posts = toolset_get_related_posts ( $post, $relationship_slug, $query_args ) ;

		$columns = [ 'ID', 'post_title', ] ;
		$items = array_map ( function ( $related_post ) {
			return [
				'ID' => $related_post,
				'post_title' => get_the_title ($related_post)
			] ;
		}, $related_posts) ;

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, $columns );
	}
}
