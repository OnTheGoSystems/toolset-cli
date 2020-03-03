<?php

namespace OTGS\Toolset\CLI\Views;

use WPV_View;

/**
 * Common methods shared by Views and WPA.
 */
abstract class Views_Common extends Views_Commands {

	/**
	 * Updates the pagination settings for a given View
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The post ID for View
	 *
	 * [--count=<int>]
	 * : Posts per page count. Default: default
	 *
	 * [--type=<string>]
	 * : Pagination type
	 * Can take values: paged, ajaxed, disabled.
	 *
	 *
	 * ## EXAMPLES
	 *
	 *    wp views view update_pagination 37 --type="ajaxed" --count=20
	 *    wp views wpa update_pagination 37 --type="ajaxed" --count=20
	 *
	 * @subcommand update_pagination
	 * @synopsis <id> [--type=<string>] [--count=<int>]
	 *
	 * @since 1.1
	 */
	public function update_pagination( $args, $assoc_args ) {
		list( $id ) = $args;

		$defaults = array(
			"count" => 'default',
			"type"  => 'paged'
		);

		$pagination_args = wp_parse_args( $assoc_args, $defaults );

		if ( !is_numeric($id) ) {
			\WP_CLI::error( __( 'Please provide a View id', 'toolset-cli' ) );
		}

		try {

			$view = WPV_View::get_instance( (int) $id );

			if ( $view !== null ) {
				$view_settings = $view->get_postmeta( WPV_View::POSTMETA_VIEW_SETTINGS );

				$view_settings['pagination']['posts_per_page'] = $pagination_args['count'];
				$view_settings['pagination']['type']           = $pagination_args['type'];

				$result = $view->update_postmeta( WPV_View::POSTMETA_VIEW_SETTINGS, $view_settings );

				if ( !empty($result) ) {
					\WP_CLI::success( __( 'Updated pagination settings', 'toolset-cli' ) );
				} else {
					\WP_CLI::error( __( 'Could not update the pagination settings', 'toolset-cli' ) );
				}

			} else {
				\WP_CLI::error( __( 'There was an error while creating new Views instance.', 'toolset-cli' ) );
			}

		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new Views instance.', 'toolset-cli' ) );
		}
	}

	/**
	 * Adds a field to the end of View loop html
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The post ID for View
	 *
	 * [--field=<string>]
	 * : Field shortcode
	 *
	 *
	 * ## EXAMPLES
	 *
	 *    wp views view add_loop_field 42 --field="[post-excerpt]"
	 *    wp views wpa add_loop_field 42 --field="[post-excerpt]"
	 *
	 * @subcommand add_loop_field
	 * @synopsis <id> [--field=<string>]
	 *
	 * @since 1.1
	 */
	public function add_loop_field( $args, $assoc_args ) {

		list( $id ) = $args;

		$defaults = array(
			"field" => ''
		);

		$loop_args = wp_parse_args( $assoc_args, $defaults );

		try {

			$view = WPV_View::get_instance( (int) $id );

			$view->begin_modifying_view_settings();
			$view->begin_modifying_loop_settings();

			$view->loop_meta_html = str_replace( "</wpv-loop>", sprintf( " %s </wpv-loop>", $loop_args['field'] ), $view->loop_meta_html );

			$view->finish_modifying_loop_settings();
			$view->finish_modifying_view_settings();

			\WP_CLI::success( __( 'Added a new field to the loop', 'toolset-cli' ) );

		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new Views instance.', 'toolset-cli' ) );
		}
	}

}
