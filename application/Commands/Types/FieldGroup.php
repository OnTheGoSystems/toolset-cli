<?php

namespace OTGS\Toolset\CLI\Types;

use RuntimeException;
use Toolset_Element_Domain;
use Toolset_Field_Definition_Factory;
use Toolset_Field_Group_Factory;
use Toolset_Field_Group_Post;
use WP_CLI;
use function OTGS\Toolset\CLI\get_domain_from_post_type;
use function OTGS\Toolset\CLI\get_random_string;
use function WP_CLI\Utils\format_items;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Field group commands.
 */
class FieldGroup extends TypesCommand {

	/**
	 * Displays a list of field groups.
	 *
	 * ## OPTIONS
	 *
	 * [--domain=<domain>]
	 * : The domain of the group. Can take values: posts, terms, users. Default: posts.
	 *
	 * [--status=<status>]
	 * : Whether to return public or private field groups. Can take values: public, private. Default: public.
	 *
	 * [--format=<format>]
	 * : The format of the output. Can take values: – table, csv, ids, json, count, yaml. Default: table.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types field group list --domain=posts --status=public --format=json
	 *
	 * @subcommand list
	 * @synopsis [--domain=<domain>] [--status=<status>] [--format=<format>]
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @since 1.0
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function list_items( $args, $assoc_args ) {
		$defaults = [
			'format' => 'table',
			'domain' => 'all',
			'status' => 'public',
		];
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$return_ids = $assoc_args['format'] === 'ids';
		$items = $this->get_items( $assoc_args['domain'], $assoc_args['status'], $return_ids );

		format_items( $assoc_args['format'], $items, $this->get_columns() );
	}


	/**
	 * Returns all field groups.
	 *
	 * @param string $domain The domain that the field groups belong to.
	 * @param string $status Whether to return public, private or all post types. Can take values: public, private,
	 *     all. Default: public.
	 * @param bool $return_ids Whether it should return in IDs format.
	 *
	 * @return array The field groups.
	 */
	protected function get_items( $domain = 'all', $status = 'public', $return_ids = false ) {

		$args = [
			'domain' => $domain,
		];

		if ( $status !== 'all' ) {
			$args['is_active'] = $status === 'public';
		}

		$item_groups = apply_filters( 'types_query_groups', [], $args );

		if ( is_numeric( array_keys( $item_groups )[0] ) ) {
			$item_groups[] = $item_groups;
		}

		$return_items = [];
		foreach ( $item_groups as $items ) {
			foreach ( $items as $item ) {
				if ( $return_ids ) {
					$return_items[] = $item->get_id();
				} else {
					$return_items[] = [
						'id' => $item->get_id(),
						'post_title' => $item->get_name(),
						'slug' => $item->get_slug(),
						'post_status' => $item->is_active() ? __( 'active', 'toolset-cli' )
							: __( 'inactive', 'toolset-cli' ),
						'domain' => get_domain_from_post_type( $item->get_post_type() ),
					];
				}
			}
		}

		return $return_items;
	}


	/**
	 * Returns the columns of the list command.
	 *
	 * @return string[] The columns of the list command.
	 */
	protected function get_columns() {
		return [
			'id',
			'post_title',
			'slug',
			'domain',
			'post_status',
		];
	}


	/**
	 * Creates a new field group.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<string>]
	 * : The name of the group. Default: random string.
	 *
	 * [--title=<string>]
	 * : The title of the group. Default: random string.
	 *
	 * [--domain=<domain>]
	 * : The domain of the group. Can take values: posts, terms, users. Default: posts.
	 *
	 * [--status=<bool>]
	 * : The status of the group.
	 *
	 * [--description=<string>]
	 * : Field group description.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types field group create --name=my-group --title='My Group' --domain=terms
	 *
	 * @subcommand create
	 * @synopsis [--name=<string>] [--title=<string>] [--domain=<domain>] [--status=<bool>] [--description=<string>]
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws WP_CLI\ExitException
	 * @since 1.0
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function create( $args, $assoc_args ) {
		$defaults = [
			'name' => get_random_string(),
			'title' => '',
			'domain' => 'posts',
			'status' => true,
		];
		$field_group_args = wp_parse_args( $assoc_args, $defaults );

		$field_group = $this->create_item(
			$field_group_args['domain'], $field_group_args['name'], $field_group_args['title'], $field_group_args['status'],
			array_key_exists( 'description', $field_group_args ) ? $field_group_args['description'] : ''
		);

		if ( $field_group ) {
			WP_CLI::success( __( 'Created field group.', 'toolset-cli' ) );
		} else {
			WP_CLI::error( __( 'Could not create field group.', 'toolset-cli' ) );
		}
	}


	/**
	 * Bulk generates field groups.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many items to generate. Default: 10
	 *
	 * [--domain=<domain>]
	 * : The domain of the group. Can take values: posts, terms, users. Default: posts.
	 *
	 * ## EXAMPLES
	 *
	 *   wp types field group generate --count=100 --domain=terms
	 *
	 * @subcommand generate
	 * @synopsis [--count=<number>] [--domain=<domain>]
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @since 1.0
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function generate( $args, $assoc_args ) {
		$defaults = [
			'count' => 10,
			'domain' => 'posts',
		];
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$progress = make_progress_bar( __( 'Generating field groups', 'toolset-cli' ), $assoc_args['count'] );
		for ( $i = 0; $i < $assoc_args['count']; $i ++ ) {
			$this->create_item( $assoc_args['domain'] );
			$progress->tick();
		}
		$progress->finish();
	}


	/**
	 * Creates a field group.
	 *
	 * @param string $domain Valid field domain
	 * @param string $name Sanitized field group name. Note that the final name may change when new post is inserted.
	 * @param string $title Field group title.
	 * @param bool $is_active Whether the group is active.
	 * @param string $description Field group description.
	 *
	 * @return null|int The ID of the group or null on error.
	 */
	protected function create_item( $domain = 'posts', $name = '', $title = '', $is_active = true, $description = '' ) {
		if ( empty ( $name ) ) {
			$name = get_random_string();
		}

		if ( empty ( $title ) ) {
			$title = $name;
		}

		$group_id = apply_filters( 'types_create_field_group', null, $domain, $name, $title, $is_active );

		if ( $group_id ) {
			wp_update_post( [
				'ID' => $group_id,
				'post_content' => $description,
			] );
		}

		return $group_id;
	}


	/**
	 * Deletes all existing field groups.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types field group empty
	 *
	 * @subcommand empty
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @since 1.0
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function empty_items( $args, $assoc_args ) {
		$items = $this->get_items();
		if ( ! empty( $items ) ) {
			$progress = make_progress_bar( __( 'Deleting field groups', 'toolset-cli' ), count( $items ) );
			foreach ( $items as $item ) {
				$this->delete_item( $item['id'], true );
				$progress->tick();
			}
			$progress->finish();
		} else {
			WP_CLI::warning( __( 'There are no field groups to delete.', 'toolset-cli' ) );
		}
	}


	/**
	 * Deletes a field group.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID of the group.
	 *
	 * [--force=<bool>]
	 * : Whether to bypass trash and force deletion. Default: false.
	 *
	 * ## EXAMPLES
	 *
	 *    wp types field group delete 42 --force=true
	 *
	 * @subcommand delete
	 * @synopsis <id> [--force=<bool>]
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @throws WP_CLI\ExitException
	 * @since 1.0
	 */
	public function delete( $args, $assoc_args ) {

		list( $id ) = $args;

		if ( empty( $id ) ) {
			WP_CLI::warning( __( 'You must specify a field group ID.', 'toolset-cli' ) );

			return;
		}

		$defaults = [
			'force' => false,
		];
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$delete_result = $this->delete_item( $id, $assoc_args['force'] );

		if ( $delete_result ) {
			WP_CLI::success( __( 'Deleted field group.', 'toolset-cli' ) );
		} else {
			WP_CLI::error( __( 'Could not delete field group.', 'toolset-cli' ) );
		}

	}


	/**
	 * Deletes a field group.
	 *
	 * @param integer $id The ID of the field group.
	 * @param bool $force Whether to bypass trash and force deletion. Default: false.
	 *
	 * @return mixed The post object if deleted successfully, or false on failure.
	 */
	protected function delete_item( $id, $force = false ) {
		return wp_delete_post( $id, $force );
	}


	/**
	 * Adds an existing field to a field group.
	 *
	 * ## OPTIONS
	 *
	 * --domain=<string>
	 * : Element domain of the field group (posts|terms|users).
	 *
	 * --group_slug=<string>
	 * : Slug of the custom field group.
	 *
	 * --field_slug=<string>
	 * : Slug of the custom field that should be added to the group.
	 *
	 * @param $args
	 * @param $parameters
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_field( $args, $parameters ) {
		if ( ! in_array( $parameters['domain'], Toolset_Element_Domain::all(), true ) ) {
			$this->wp_cli()->error( __( 'Invalid or missing element domain.', 'toolset-cli' ) );
			return;
		}

		$group_factory = Toolset_Field_Group_Factory::get_factory_by_domain( $parameters['domain'] );
		$field_group = $group_factory->load_field_group( $parameters['group_slug'] );
		if ( ! $field_group ) {
			$this->wp_cli()->error( __( 'Invalid custom field group slug.', 'toolset-cli' ) );
			return;
		}

		$field_definition_factory = Toolset_Field_Definition_Factory::get_factory_by_domain( $parameters['domain'] );
		$field_definition = $field_definition_factory->load_field_definition( $parameters['field_slug'] );
		if ( ! $field_definition ) {
			$this->wp_cli()->error( __( 'Invalid custom field definition.', 'toolset-cli' ) );
			return;
		}

		$is_success = $field_group->add_field_definition( $field_definition );
		if ( $is_success ) {
			$this->wp_cli()->success( __( 'Custom field added to the field group.', 'toolset-cli' ) );
		} else {
			$this->wp_cli()->error( __( 'Toolset reports an error when trying to add the custom field to the field group.', 'toolset-cli' ) );
		}
	}


	/**
	 * Set filters (display conditions) for a field group.
	 *
	 * Setting a particular type of condition overwrites its previous setting completely.
	 * This command needs to be extended to achieve full functionality.
	 *
	 * ## OPTIONS
	 *
	 * --domain=<string>
	 * : Element domain of the field group (posts|terms|users). Only post field groups are supported at the moment.
	 *
	 * --group_slug=<string>
	 * : Slug of the custom field group.
	 *
	 * [--post_types=<string>]
	 * : Set a display condition by post type. Comma-separated list of post type slugs.
	 *
	 * [--terms=<string>]
	 * : Set a display condition by taxonomy terms. Comma-separated list of term slugs. Must be coupled with
	 *   the --taxonomy parameter.
	 *
	 * [--taxonomy=<string>]
	 * : Taxonomy slug when using the --terms parameter.
	 *
	 * [--template=<string>]
	 * : Set a display condition by page template. The value should be name of the template file.
	 *
	 * [--operator=<string>]
	 * : Comparison operator for display conditions. Accepted values: 'AND'|'OR'.
	 *
	 * @param $args
	 * @param $parameters
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function set_display_conditions( $args, $parameters ) {
		if ( Toolset_Element_Domain::POSTS !== $parameters['domain'] ) {
			$this->wp_cli()->error( __( 'Only post field groups are supported at the moment.', 'toolset-cli' ) );
			return;
		}

		$group_factory = Toolset_Field_Group_Factory::get_factory_by_domain( $parameters['domain'] );
		$field_group = $group_factory->load_field_group( $parameters['group_slug'] );
		if ( ! $field_group ) {
			$this->wp_cli()->error( __( 'Invalid custom field group slug.', 'toolset-cli' ) );
			return;
		}

		if ( array_key_exists( 'operator', $parameters ) ) {
			$operator = $parameters['operator'] === 'AND' ? 'all' : 'any';
			update_post_meta( $field_group->get_id(), Toolset_Field_Group_Post::POSTMETA_FILTER_OPERATOR, $operator );
			$this->wp_cli()->log( __( 'Comparison operator updated.', 'toolset-cli' ) );
		}

		if ( array_key_exists( 'post_types', $parameters ) ) {
			$post_types = implode( ',', array_map( 'trim', explode( ',', $parameters['post_types'] ) ) );
			update_post_meta( $field_group->get_id(), Toolset_Field_Group_Post::POSTMETA_POST_TYPE_LIST, $post_types );
			$this->wp_cli()->log( __( 'Display condition by post types set.', 'toolset-cli' ) );
		}

		if ( array_key_exists( 'terms', $parameters ) ) {
			if ( ! array_key_exists( 'taxonomy', $parameters ) ) {
				$this->wp_cli()->error( __( 'The --taxonomy parameter is mandatory when using --terms.', 'toolset-cli' ) );
				return;
			}
			$term_ids = [];
			foreach ( explode( ',', $parameters['terms'] ) as $term_slug ) {
				$term = get_term_by( 'slug', $term_slug, $parameters['taxonomy'] );
				if ( ! $term ) {
					$this->wp_cli()->error( sprintf(
						__( 'Term %s not found, cannot use it as a display condition.', 'toolset-cli' ), $term_slug
					) );
					return;
				}
				$term_ids[] = $term->term_taxonomy_id;
			}
			update_post_meta(
				$field_group->get_id(),
				Toolset_Field_Group_Post::POSTMETA_TERM_LIST,
				implode( ',', $term_ids )
			);
			$this->wp_cli()->log( __( 'Display condition by post types set.', 'toolset-cli' ) );
		}

		if ( array_key_exists( 'template', $parameters ) ) {
			update_post_meta( $field_group->get_id(), Toolset_Field_Group_Post::POSTMETA_TEMPLATE_LIST, $parameters['template'] );
			$this->wp_cli()->log( __( 'Display condition by page template.', 'toolset-cli' ) );
		}

		$this->wp_cli()->success( __( 'Display condition setup completed.', 'toolset-cli' ) );
	}
}
