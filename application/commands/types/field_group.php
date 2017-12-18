<?php

namespace Toolset_CLI\Types;

/**
 * Field group commands.
 *
 * @package Toolset_CLI\Types
 */
class Field_Group extends Types_Command {

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
	 * @since 1.0
	 */
	public function list_items( $args, $assoc_args ) {
		$defaults = array(
			'format' => 'table',
			'domain' => 'all',
			'status' => 'public',
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$ids = $assoc_args['format'] == 'ids' ? true : false;
		$items = $this->get_items( $assoc_args['domain'], $assoc_args['status'], $ids );

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, $this->get_columns() );
	}

	/**
	 * Returns all field groups.
	 *
	 * @param string $domain The domain that the field groups belong to.
	 * @param string $status Whether to return public, private or all post types. Can take values: public, private,
	 *     all. Default: public.
	 * @param bool $ids Whether it should return in IDs format.
	 *
	 * @return array The field groups.
	 */
	protected function get_items( $domain = 'all', $status = 'public', $ids = false ) {

		$args = array(
			'domain' => $domain,
		);

		if ( $status != 'all' ) {
			$args['is_active'] = ( $status == 'public' ) ? true : false;
		}

		$item_groups = apply_filters( 'types_query_groups', array(), $args );

		if ( is_numeric( array_keys( $item_groups )[0] ) ) {
			$item_groups[] = $item_groups;
		}

		$return_items = array();
		foreach ( $item_groups as $items ) {
			foreach ( $items as $item ) {
				if ( $ids ) {
					$return_items[] = $item->get_id();
				} else {
					$return_items[] = array(
						'id' => $item->get_id(),
						'post_title' => $item->get_name(),
						'post_status' => $item->is_active() ? __( 'active', 'toolset-cli' ) : __( 'inactive', 'toolset-cli' ),
						'domain' => \Toolset_CLI\get_domain_from_post_type( $item->get_post_type() ),
					);
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
		$columns = array(
			'id',
			'post_title',
			'domain',
			'post_status',
		);

		return $columns;
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
	 * ## EXAMPLES
	 *
	 *    wp types field group create --name=my-group --title='My Group' --domain=terms
	 *
	 * @subcommand create
	 * @synopsis [--name=<string>] [--title=<string>] [--domain=<domain>] [--status=<bool>]
	 *
	 * @since 1.0
	 */
	public function create( $args, $assoc_args ) {
		$defaults = array(
			'name' => \Toolset_CLI\get_random_string(),
			'title' => '',
			'domain' => 'posts',
			'status' => true,
		);
		$field_group_args = wp_parse_args( $assoc_args, $defaults );

		$field_group = $this->create_item( $field_group_args['domain'], $field_group_args['name'], $field_group_args['title'], $field_group_args['status'] );

		if ( ! empty ( $field_group ) ) {
			\WP_CLI::success( __( 'Created field group.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'Could not create field group.', 'toolset-cli' ) );
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
	 * @since 1.0
	 */
	public function generate( $args, $assoc_args ) {
		$defaults = array(
			'count' => 10,
			'domain' => 'posts',
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Generating field groups', 'toolset-cli' ), $assoc_args['count'] );
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
	 *
	 * @return null|int The ID of the group or null on error.
	 */
	protected function create_item( $domain = 'posts', $name = '', $title = '', $status = true ) {
		if ( empty ( $name ) ) {
			$name = \Toolset_CLI\get_random_string();
		}

		if ( empty ( $title ) ) {
			$title = $name;
		}

		return apply_filters( 'types_create_field_group', null, $domain, $name, $title, $status );
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
	 * @since 1.0
	 */
	public function empty_items( $args, $assoc_args ) {
		$items = $this->get_items();
		if ( ! empty( $items ) ) {
			$progress = \WP_CLI\Utils\make_progress_bar( __( 'Deleting field groups', 'toolset-cli' ), count( $items ) );
			foreach ( $items as $item ) {
				$this->delete_item( $item['id'], true );
				$progress->tick();
			}
			$progress->finish();
		} else {
			\WP_CLI::warning( __( 'There are no field groups to delete.', 'toolset-cli' ) );
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
	 * @since 1.0
	 */
	public function delete( $args, $assoc_args ) {

		list( $id ) = $args;

		if ( empty( $id ) ) {
			\WP_CLI::warning( __( 'You must specify a field group ID.', 'toolset-cli' ) );

			return;
		}

		$defaults = array(
			'force' => false,
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		$delete_result = $this->delete_item( $id, $assoc_args['force'] );

		if ( $delete_result ) {
			\WP_CLI::success( __( 'Deleted field group.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'Could not delete field group.', 'toolset-cli' ) );
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
}
