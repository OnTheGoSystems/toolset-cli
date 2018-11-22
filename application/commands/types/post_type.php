<?php

namespace Toolset_CLI\Types;

/**
 * Post Type commands.
 *
 * @package Toolset_CLI\Types
 */
class Post_Type extends Types_Command {

	/**
	 * Displays a list of post types.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : The format of the output. Can take values: table, csv, json, count, yaml. Default: table.
	 *
	 * [--domain=<domain>]
	 * : The domain of the post types. Can take values: all, types, builtin.
	 *
	 * [--intermediary=<bool>]
	 * : Whether to return intermediary post types. Default: false.
	 *
	 * [--repeating_field_group=<bool>]
	 * : Whether to return repeating field group post types. Default: false.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype list --format=json --domain=all --intermediary=true --repeating_field_group=true
	 *
	 * @subcommand list
	 * @synopsis [--format=<format>] [--domain=<domain>] [--intermediary=<bool>] [--repeating_field_group=<bool>]
	 *
	 * @since 1.0
	 */
	public function list_items( $args, $assoc_args ) {
		$defaults = array(
			'format' => 'table',
			'domain' => 'all',
			'intermediary' => false,
			'repeating_field_group' => false,
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$items = $this->get_items( $assoc_args['domain'], $assoc_args['intermediary'], $assoc_args['repeating_field_group'] );

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, $this->get_columns() );
	}

	/**
	 * Get all registered post types.
	 *
	 * @param string $domain The domain of the post types. Can take values: all, types, builtin.
	 * @param bool $is_intermediary Whether to return intermediary post types. Default: false.
	 * @param bool $is_repeating_field_group Whether to return repeating field group post types. Default: false.
	 *
	 * @return array
	 */
	private function get_items( $domain = 'all', $is_intermediary = false, $is_repeating_field_group = false ) {
		$args = array(
			'is_intermediary' => ( $is_intermediary === 'true' ? true : false ),
			'is_repeating_field_group' => ( $is_repeating_field_group === 'true' ? true : false ),
		);

		if ( $domain != 'all' ) {
			$args['from_types'] = ( $domain == 'types' ) ? true : false;
		}

		$query = new \Toolset_Post_Type_Query( $args );
		$post_types = $query->get_results();

		$return_items = array();
		foreach ( $post_types as $post_type ) {
			$return_items[] = array(
				'slug' => $post_type->get_slug(),
				'singular' => $post_type->get_label( \Toolset_Post_Type_Labels::SINGULAR_NAME ),
				'plural' => $post_type->get_label( \Toolset_Post_Type_Labels::NAME ),
				'active' => $post_type->is_registered() ? __( 'Yes', 'toolset-cli' ) : __( 'No', 'toolset-cli' ),
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
			'active',
		);

		return $columns;
	}

	/**
	 * Creates a new post type.
	 *
	 * ## OPTIONS
	 *
	 * [--slug=<string>]
	 * : The name of the post type. Default: random string.
	 *
	 * [--singular=<string>]
	 * : The singular name of the post type. Default: random string.
	 *
	 * [--plural=<string>]
	 * : The plural name of the post type. Default: random string.
	 *
	 * [--editor=<string>]
	 * : Which editor to use. Can take values: classic, block. Default: classic.
	 *
	 * [--show_in_rest=<bool>]
	 * : Whethere show_in_rest option is enabled. Default: false.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype create --slug='book' --singular='Book' --plural='Books' --editor=block --show_in_rest=true
	 *
	 * @subcommand create
	 * @synopsis [--slug=<string>] [--singular=<string>] [--plural=<string>] [--editor=<string>] [--show_in_rest=<bool>]
	 *
	 * @since 1.0
	 */
	public function create( $args, $assoc_args ) {
		$defaults = array(
			'slug' => \Toolset_CLI\get_random_string(),
			'plural' => '',
			'singular' => '',
			'editor' => 'classic',
			'show_in_rest' => 'false',
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$post_type_options = array(
			'editor' => $assoc_args['editor'],
			'show_in_rest' => $assoc_args['show_in_rest'],
		);

		$post_type = $this->create_item( $assoc_args['slug'], $assoc_args['plural'], $assoc_args['singular'], $post_type_options );

		if ( ! empty ( $post_type ) ) {
			\WP_CLI::success( __( 'Created post type.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'Could not create post type.', 'toolset-cli' ) );
		}
	}

	/**
	 * Bulk generates post types.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many items to generate. Default: 10
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype generate --count=100
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

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Generating post types', 'toolset-cli' ), $assoc_args['count'] );
		for ( $i = 0; $i < $assoc_args['count']; $i ++ ) {
			$this->create_item();
			$progress->tick();
		}
		$progress->finish();
	}

	/**
	 * Creates a post type.
	 *
	 * @param string $slug The slug of the post type.
	 * @param string $plural The plural name of the post type.
	 * @param string $singular The singular name of the post type.
	 * @param array $options The options of the post type.
	 *
	 * @return IToolset_Post_Type_From_Types
	 */
	protected function create_item( $slug = '', $plural = '', $singular = '', $post_type_options = array() ) {
		if ( empty ( $slug ) ) {
			$slug = \Toolset_CLI\get_random_string();
		}

		if ( empty ( $plural ) ) {
			$plural = $slug;
		}

		if ( empty ( $singular ) ) {
			$singular = $slug;
		}

		try {
			$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
			$post_type = $post_type_repository->create( $slug, $plural, $singular );

			if ( isset( $post_type_options['show_in_rest'] ) && $post_type_options['show_in_rest'] == 'true' ) {
				$post_type->set_show_in_rest( true );
			}

			if ( isset( $post_type_options['editor'] ) && $post_type_options['editor'] == 'block' ) {
				$post_type->use_block_editor();
			}

			$post_type_repository->save( $post_type );

			// @todo This is a workaround to flush rewrite rules, until toolsetcommon-329 is fixed
			register_post_type( $slug );
			flush_rewrite_rules( false );

			return $post_type;
		} catch ( \RuntimeException $e ) {
			\WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Deletes all existing post types.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype empty
	 *
	 * @subcommand empty
	 *
	 * @since 1.0
	 */
	public function empty_items( $args, $assoc_args ) {
		$items = $this->get_items( 'types' );
		if ( ! empty( $items ) ) {
			$progress = \WP_CLI\Utils\make_progress_bar( __( 'Deleting post types', 'toolset-cli' ), count( $items ) );
			foreach ( $items as $item ) {
				$this->delete_item( $item['slug'] );
				$progress->tick();
			}
			$progress->finish();
		} else {
			\WP_CLI::warning( __( 'There are no post types to delete.', 'toolset-cli' ) );
		}
	}

	/**
	 * Deletes a post type.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the post type.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype delete book
	 *
	 * @subcommand delete
	 * @synopsis <slug>
	 *
	 * @since 1.0
	 */
	public function delete( $args, $assoc_args ) {
		list( $slug ) = $args;
		$this->delete_item( $slug );
		\WP_CLI::success( __( 'Deleted post type.', 'toolset-cli' ) );
	}

	/**
	 * Deletes a post type.
	 *
	 * @param string $slug The slug of the post type.
	 */
	protected function delete_item( $slug ) {
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$post_type = $this->get_post_type( $slug );
		$post_type_repository->delete( $post_type );
	}

	/**
	 * Updates a post type.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the post type.
	 *
	 * [--slug=<string>]
	 * : The name of the post type.
	 *
	 * [--singular=<string>]
	 * : The singular name of the post type.
	 *
	 * [--plural=<string>]
	 * : The plural name of the post type.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype update book --slug='ebook' --singular='Ebook' --plural='Ebooks'
	 *
	 * @subcommand update
	 * @synopsis <slug> [--slug=<string>] [--singular=<string>] [--plural=<string>]
	 *
	 * @since 1.0
	 */
	public function update_item( $args, $assoc_args ) {
		list( $slug ) = $args;

		if ( empty( $slug ) ) {
			\WP_CLI::error( __( 'You must specify a post type slug.', 'toolset-cli' ) );
		}

		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$post_type = $post_type_repository->get_from_types( $slug );

		if ( empty( $post_type ) ) {
			\WP_CLI::error( __( 'Post type does not exist.', 'toolset-cli' ) );
		}

		if ( isset( $assoc_args['slug'] ) && ! empty ( $assoc_args['slug'] ) ) {
			$post_type_repository->change_slug( $post_type, $assoc_args['slug'] );
		}
		if ( isset( $assoc_args['singular'] ) && ! empty ( $assoc_args['singular'] ) ) {
			$post_type->set_label( \Toolset_Post_Type_Labels::SINGULAR_NAME, $assoc_args['singular'] );
		}
		if ( isset( $assoc_args['plural'] ) && ! empty ( $assoc_args['plural'] ) ) {
			$post_type->set_label( \Toolset_Post_Type_Labels::NAME, $assoc_args['plural'] );
		}

		$post_type_repository->save( $post_type );

		\WP_CLI::success( __( 'Updated post type.', 'toolset-cli' ) );
	}

	/**
	 * Activates a post type.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the post type.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype activate book
	 *
	 * @subcommand activate
	 * @synopsis <slug>
	 *
	 * @since 1.0
	 */
	public function activate( $args, $assoc_args ) {
		list( $slug ) = $args;
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$post_type = $this->get_post_type( $slug, true );
		$post_type->set_is_disabled( false );
		$post_type_repository->save( $post_type );
		\WP_CLI::success( __( 'Activated post type.', 'toolset-cli' ) );
	}

	/**
	 * Deactivates a post type.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the post type.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types posttype deactivate book
	 *
	 * @subcommand deactivate
	 * @synopsis <slug>
	 *
	 * @since 1.0
	 */
	public function deactivate( $args, $assoc_args ) {
		list( $slug ) = $args;
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$post_type = $this->get_post_type( $slug, true );
		$post_type->set_is_disabled( true );
		$post_type_repository->save( $post_type );
		\WP_CLI::success( __( 'Deactivated post type.', 'toolset-cli' ) );
	}
}