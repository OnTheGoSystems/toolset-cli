<?php

namespace OTGS\Toolset\CLI\Views;

use WPV_Content_Template;
use WPV_Settings;

class CT extends Views_Commands {

	/**
	 * Creates a new Content Template.
	 *
	 * ## OPTIONS
	 *
	 * [--title=<string>]
	 * : The title of the CT. Default: random string.
	 *
	 * [--content=<string>]
	 * : The Template content.
	 *
	 * [--assignment_group=<string>]
	 * : The usage group type.
	 * Can take values: singles, archives, taxonomy.
	 *
	 * [--assignment_slug=<string>]
	 * : Post type or taxonomy slug.
	 *
	 * [--porcelain]
	 * : prints the created Content Template ID when passed
	 *
	 * ## EXAMPLES
	 *
	 *    wp views template create --title="Test Thingy" --content="some html and shortcodes"
	 * --assignment_group="singles" --assignment_slug="cest" --porcelain
	 *
	 * @subcommand create
	 * @synopsis [--title=<string>] [--content=<string>] [--assignment_group=<string>] [--assignment_slug=<string>]
	 *     [--porcelain]
	 *
	 * @since 1.1
	 */
	public function create( $args, $assoc_args ) {
		$defaults = array(
			'title' => \OTGS\Toolset\CLI\get_random_string(),
			'content' => 'Put your template here',
			'assignment_group' => 'singles',
			'assignment_slug' => 'post',
		);

		$create_args = wp_parse_args( $assoc_args, $defaults );

		try {

			$ct = WPV_Content_Template::create( $create_args['title'] );

			if ( ! empty( $create_args['content'] ) ) {
				$this->set_content( $ct->id, $create_args['content'] );
			}

			if ( ! empty( $create_args['assignment_group'] ) && ! empty( $create_args['assignment_slug'] ) ) {
				$this->set_assignment( $ct->id, $create_args['assignment_group'], $create_args['assignment_slug'] );
			}

			if ( $ct->id !== null ) {
				$this->output_result( $ct->id, $create_args, 'Content Template created' );
			} else {
				\WP_CLI::error( __( 'Could not create content template.', 'toolset-cli' ) );
			}
		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new Views instance.', 'toolset-cli' ) );
		}
	}


	/**
	 * Duplicates an existing Content Template.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the Content Template that will be duplicated.
	 *
	 * [--title=<string>]
	 * : The title for the new Content Template.
	 *
	 * [--output_id]
	 * : prints the duplicated Content Template ID.
	 *
	 * ## EXAMPLES
	 *
	 *    wp views template duplicate 1 --title="Test Thingy" --porcelain
	 *
	 * @subcommand duplicate
	 * @synopsis <id> [--title=<string>] [--porcelain]
	 *
	 * @since 1.1
	 */
	public function duplicate( $args, $assoc_args ) {
		list( $id ) = $args;

		$defaults = array(
			'title' => \OTGS\Toolset\CLI\get_random_string(),
			'output_id' => false,
		);

		$duplicate_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a CT id', 'toolset-cli' ) );
		}

		try {
			$ct = WPV_Content_Template::get_instance( $id );
			$duplicate_ct = $ct->duplicate( $duplicate_args['title'], true );

			if ( $duplicate_ct !== null && $duplicate_ct->id !== null ) {
				$this->output_result( $duplicate_ct->id, $duplicate_args, 'Content Template duplicated' );
			} else {
				\WP_CLI::error( __( 'Could not duplicate content template.', 'toolset-cli' ) );
			}
		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new CT instance.', 'toolset-cli' ) );
		}
	}


	/**
	 * Changes the Template content for an existing CT.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the content template that will be duplicated.
	 *
	 * [--content=<string>]
	 * : The new template content.
	 *
	 * ## EXAMPLES
	 *
	 *    wp views template change_content 1 --content="Test Thingy"
	 *
	 * @subcommand change_content
	 * @synopsis <id> [--content=<string>]
	 *
	 * @since 1.1
	 */
	public function change_content( $args, $assoc_args ) {

		list( $id ) = $args;

		$defaults = array(
			'content' => \OTGS\Toolset\CLI\get_random_string(),
		);

		$content_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a CT id', 'toolset-cli' ) );
		}

		$result = $this->set_content( $id, $content_args['content'] );

		if ( $result !== 0 ) {
			\WP_CLI::success( __( 'Change CT content.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'Could not change CT content.', 'toolset-cli' ) );
		}
	}


	/**
	 * Binds posts to the specified CT.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the content template that will be duplicated.
	 *
	 * [--posts_ids=<string>]
	 * : Comma seperated posts ids to be bound to the CT.
	 *
	 * ## EXAMPLES
	 *
	 *    wp views template bind 1 --posts_ids="1,2,3,4"
	 *
	 * @subcommand bind
	 * @synopsis <id> [--posts_ids=<string>]
	 *
	 * @since 1.1
	 */
	public function bind( $args, $assoc_args ) {

		list( $id ) = $args;

		$defaults = array(
			'posts_ids' => - 1,
		);

		$bind_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a CT id', 'toolset-cli' ) );
		}

		if ( $bind_args['posts_ids'] === - 1 ) {
			\WP_CLI::error( __( 'Please provide a post id using --posts_ids arg', 'toolset-cli' ) );
		}

		try {
			$ct = WPV_Content_Template::get_instance( $id );
			$result = $ct->bind_posts( explode( ',', $bind_args['posts_ids'] ) );

			if ( $result !== false ) {
				\WP_CLI::success( __( 'bind-ed posts to content template.', 'toolset-cli' ) );
			} else {
				\WP_CLI::error( __( 'could not bind posts to content template.', 'toolset-cli' ) );
			}
		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new CT instance.', 'toolset-cli' ) );
		}
	}


	/**
	 * Assign the CT to resource, CPT single, CPT archive, or taxonomies.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID for the content template that will be duplicated.
	 *
	 * [--assignment_group=<string>]
	 * : The usage group type.
	 * Can take values: singles, archives, taxonomy.
	 *
	 * [--assignment_slug=<string>]
	 * : CPT or taxonomy slug.
	 *
	 * ## EXAMPLES
	 *
	 *    wp views template assign 1 --assignment_slug="cest" --assignment_group="archives"
	 *
	 * @subcommand assign
	 * @synopsis <id> [--assignment_slug=<string>] [--assignment_group=<string>]
	 *
	 * @since 1.1
	 */
	public function assign( $args, $assoc_args ) {
		list( $id ) = $args;

		$defaults = array(
			'assignment_slug' => null,
			'assignment_group' => null,
		);

		$assign_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! is_numeric( $id ) ) {
			\WP_CLI::error( __( 'Please provide a CT id', 'toolset-cli' ) );
		}

		$result = $this->set_assignment( $id, $assign_args['assignment_group'], $assign_args['assignment_slug'] );

		if ( $result !== false ) {
			\WP_CLI::success( __( 'assigned content template to resource.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'could not assign content template to the resource.', 'toolset-cli' ) );
		}

	}


	/**
	 * Sets the assignment for a given CT
	 *
	 * @param int $ct_id
	 * @param string $assignment_group
	 * @param string $assignment_slug
	 *
	 * @return bool
	 */
	protected function set_assignment( $ct_id, $assignment_group, $assignment_slug ) {

		$wpv_option = get_option( WPV_Settings::OPTION_NAME, array() );

		return update_option( WPV_Settings::OPTION_NAME, array_merge( $wpv_option, array( $this->format_assignment( $assignment_group, $assignment_slug ) => $ct_id ) ) );
	}


	/**
	 * Adds necessary prefixes for all assignment types
	 *
	 * @param string $assignment_group
	 * @param string $assignment_slug
	 *
	 * @return string
	 */
	protected function format_assignment( $assignment_group, $assignment_slug ) {
		$prefix = null;
		switch ( $assignment_group ) {
			case 'singles':
				$prefix = WPV_Settings::SINGLE_POST_TYPES_CT_ASSIGNMENT_PREFIX;
				break;
			case 'archives':
				$prefix = WPV_Settings::CPT_ARCHIVES_CT_ASSIGNMENT_PREFIX;
				break;
			case 'taxonomy':
				$prefix = WPV_Settings::TAXONOMY_ARCHIVES_CT_ASSIGNMENT_PREFIX;
				break;
			default:
				\WP_CLI::error( sprintf( __( 'Unsupported CT assignment type %s', 'toolset-cli' ), ucfirst( $assignment_group ) ) );
				break;
		}

		return sprintf( '%s%s', $prefix, $assignment_slug );
	}


	/**
	 * Sets the content for a given CT
	 *
	 * @param int $ct_id
	 * @param string $content
	 *
	 * @return int|\WP_Error
	 */
	protected function set_content( $ct_id, $content ) {
		return wp_update_post( array(
			'ID' => $ct_id,
			'post_content' => $content,
		) );
	}
}
