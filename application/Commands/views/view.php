<?php

namespace OTGS\Toolset\CLI\Views;

use WPV_View;

/**
 * Views View commands.
 */
class View extends Views_Common {

	public static $allowed_purpose = array( 'all', 'parametric', 'slider', 'pagination', 'full' );

	/**
	 * Creates a new View.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<string>]
	 * : The name of the WPA. Default: random string.
	 *
	 * [--view_purpose=<string>]
	 * : The WordPress archive purpose
	 * Can take values: all, parametric. Default: all.
	 *
	 * [--usage_type=<string>]
	 * : The usage group type.
	 * Can take values: post_types, users, taxonomies.
	 *
	 * [--orderby=<string>]
	 * : Field slug for Posts View to be ordered by.
	 *
	 * [--users_orderby=<string>]
	 * : User field slug for Users View to be ordered by.
	 *
	 * [--taxonomy_orderby=<string>]
	 * : Taxonomy Field slug for Taxonomy View to be ordered by.
	 *
	 * [--order=<string>]
	 * : Order direction
	 * Can take values: DESC, ASC.
	 *
	 * [--porcelain]
	 * : prints the created View ID.
	 *
	 * ## EXAMPLES
	 *
	 *    wp views view create --name="Test Thingy" --usage_type="post_types" --usage="cest" --orderby="post_title" --order="ASC" --porcelain
	 *
	 * @subcommand create
	 * @synopsis [--name=<string>] [--view_purpose=<string>] [--usage_type=<string>] [--usage=<string>] [--orderby=<string>] [--order=<string>] [--porcelain]
	 *
	 * @since 1.1
	 */
	public function create( $args, $assoc_args ) {

		$defaults = array(
			'name' => \OTGS\Toolset\CLI\get_random_string(),
			'view_purpose' => 'all',
			'usage_type' => 'post_types',
			'usage' => 'post',
			'orderby' => 'post_date',
			'users_orderby' => 'user_login',
			'taxonomy_orderby' => 'name',
			'order' => 'DESC',
			'output_id' => false,
		);

		$create_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! in_array( $create_args['view_purpose'], self::$allowed_purpose ) ) {
			\WP_CLI::error( __( 'Using unsupported View purpose.', 'toolset-cli' ) );
		}

		try {

			$usages = $this->format_usage( $create_args['usage'], $create_args['usage_type'] );
			$view = WPV_View::create( WPV_View::get_unique_title( $create_args['name'] ), $create_args );

			$updated_meta = $this->cleanup_meta( array_merge( $create_args, $usages ) );
			$updated_meta['query_type'] = $this->query_type( $create_args['usage_type'] );

			$view_meta = $view->get_postmeta( WPV_View::POSTMETA_VIEW_SETTINGS );
			$view_meta = array_replace( $view_meta, $updated_meta );

			$view->update_postmeta( WPV_View::POSTMETA_VIEW_SETTINGS, $view_meta );

			if ( $view->id !== null ) {
				$this->output_result( $view->id, $create_args, 'View created' );
			} else {
				\WP_CLI::error( __( 'Could not create the view.', 'toolset-cli' ) );
			}
		} catch ( \Exception $e ) {
			\WP_CLI::error( __( 'There was an error while creating new Views instance.', 'toolset-cli' ) );
		}

	}

	/**
	 * Formats the View usage by adding grouping usages based on usage_type
	 *
	 * @param string $usage
	 * @param string $usage_type
	 *
	 * @return array
	 */
	protected function format_usage( $usage, $usage_type ) {
		$usages = array();
		switch ( $usage_type ) {
			case 'post_types':
				$usages['post_type'] = array_values( explode( ',', $usage ) );
				break;
			case 'users':
				$usages['roles_type'] = array_values( explode( ',', $usage ) );
				break;
			case 'taxonomies':
				$usages['taxonomy_type'] = array_values( explode( ',', $usage ) );
				break;
			default:
				\WP_CLI::error( sprintf( __( 'Unsupported view usage type %s', 'toolset-cli' ), $usage_type ) );
				break;
		}

		return $usages;
	}

	/**
	 * Returns a compatible query_type to be added to the view meta
	 *
	 * @param string $usage_type
	 *
	 * @return array
	 */
	protected function query_type( $usage_type ) {
		$query_type = array();

		switch ( $usage_type ) {
			case 'post_types':
				$query_type[] = 'posts';
				break;
			case 'users':
				$query_type[] = 'users';
				break;
			case 'taxonomies':
				$query_type[] = 'taxonomy';
				break;
			default:
				\WP_CLI::error( sprintf( __( 'Unsupported view usage type %s', 'toolset-cli' ), $usage_type ) );
				break;
		}

		return $query_type;
	}

	/**
	 * Removes unwanted array keys from View settings meta data array
	 *
	 * @param array $meta
	 *
	 * @return array
	 */
	protected function cleanup_meta( $meta ) {
		$removable_items = array( 'usage_type', 'output_id' );
		foreach ( $removable_items as $item ) {
			unset( $meta[ $item ] );
		}

		return $meta;
	}

}
