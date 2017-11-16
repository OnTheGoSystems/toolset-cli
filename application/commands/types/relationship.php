<?php

namespace Toolset_CLI\Types;

use Toolset_CLI\Types\Types_Command;

/**
 * Relationship commands.
 *
 * @package Toolset_CLI\Types
 */
class Relationship extends Types_Command {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		// I don't know why it is not defined.
		if ( !defined('TOOLSET_EDIT_LAST' )){
			define( 'TOOLSET_EDIT_LAST', '_toolset_edit_last');
		}
		do_action( 'toolset_do_m2m_full_init' );
	}


	/**
	 * Creates a relationship between two CPT, if they doesn't exist they will be created.
	 *
	 * ## OPTIONS
	 *
	 * [--parent=<cpt>]
	 * : The parent post type. Required.
	 * Can take values: post, page, media, <string>.
	 * If the post type doesn't exist it will be created.
	 * Singular and plural names can be set using comas:
	 * <slug>,<plural>,<singular> => book,Books,Book
	 *
	 * [--child=<cpt>]
	 * : The child post type. Required.
	 * Can take values: post, page, media, <string>.
	 * If the post type doesn't exist it will be created.
	 * Singular and plural names can be set using comas:
	 * <slug>,<plural>,<singular> => book-author,Authors,Author
	 *
	 * [--definition=<relationship>]
	 * : The relationship. Required.
	 * Can take values: <string>.
	 * Singular and plural names can be set using comas:
	 * <slug>,<plural>,<singular> => authorship,Authorships,Authorship
	 *
	 * [--cardinality=<string>]
	 * : Relationship type: Many to many, one to many or one to one. Default: *..*
	 * Can take values: *..*, <number>..*, <number>..<number>,  .
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship create --parent=posts --child=media --definition=featured-video
	 *    wp types relationship create --parent=book,Books,Book --child=book-author,Authors,Author --definition=authorship,Authorships,Authorship
	 *    wp types relationship create --parent=book,Books,Book --child=book-author,Authors,Author --definition=authorship,Authorships,Authorship --cardinality=*..*
	 *
	 * @subcommand create
	 * @synopsis [--parent=<string>] [--child=<string>] [--definition=<string>] [--cardinality=<string>]
	 *
	 * @since 1.0
	 */
	public function create( $args, $assoc_args ) {
		if ( ! apply_filters( 'toolset_is_m2m_enabled', false ) ) {
			\WP_CLI::error( 'Toolset m2m is not activated' );
		}
		if ( ! defined( 'TOOLSET_VERSION' ) || version_compare(TOOLSET_VERSION,  "2.5.3") < 0 ) {
			\WP_CLI::error( 'Please, update your Toolset Common version' );
		}

		$defaults = array(
			'cardinality' => '*..*',
		);
		$relationship_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! isset( $relationship_args['parent'] ) ) {
			\WP_CLI::runcommand( 'help types relationship generate' );
			\WP_CLI::error( 'Parent post type is required' );
		}
		if ( ! isset( $relationship_args['child'] ) ) {
			\WP_CLI::error( 'Child post type is required' );
			\WP_CLI::runcommand( 'help types relationship generate' );
		}
		if ( ! isset( $relationship_args['definition'] ) ) {
			\WP_CLI::error( 'Relationship definition is required' );
			\WP_CLI::runcommand( 'help types relationship generate' );
		}

		// <slug>,<plural>,<singular>.
		$parent_post_type = explode( ',', $relationship_args['parent'] );
		$parent_post_type_object = $this->create_post_type( $parent_post_type[0], toolset_getarr( $parent_post_type, 1, null), toolset_getarr( $parent_post_type, 2, null) );
		// <slug>,<plural>,<singular>.
		$child_post_type = explode( ',', $relationship_args['child'] );
		$child_post_type_object = $this->create_post_type( $child_post_type[0], toolset_getarr( $child_post_type, 1, null), toolset_getarr( $child_post_type, 2, null) );
		$definition_data = explode( ',', $relationship_args['definition'] );

		$definition_extra = array(
			'name' => toolset_getarr( $definition_data, 1, '' ),
			'singular_name' => toolset_getarr( $definition_data, 1, '' ),
			'cardinality' => toolset_getarr( $relationship_args, 'cardinality', false ),
		);

		$definition = $this->create_relationship( $definition_data[0], $parent_post_type_object, $child_post_type_object, $definition_extra );
		\WP_CLI::success( 'Command executed correctly' );
	}


	/**
	 * Generates a relationship between two CPT, if they doesn't exist they will be created.
	 *
	 * ## OPTIONS
	 *
	 * [--definition=<relationship>]
	 * : The relationship slug. Required.
	 * Can take values: <string>.
	 *
	 * [--parent-items=<number>]
	 * : Number of parent items created for testing. If it is not set and `--child-items` is, it will take the existing posts.
	 * It depends on the cardinality:
	 *    - if value is 10 and cardinality is 1..*, it will create 10 items and will relate them to some new N `--child-items` items
	 *    - if value is 10 and cardinality is *..*, it will create 10 items and will related them with some of the existing N `--child-items` items
	 *
	 * [--child-items=<number>]
	 * : Number of child items created for testing. Default: 0
	 *
	 * ## EXAMPLES
	 *
	 *    wp types relationship generate --definition=authorship --parent-items=10000 --child-items=10000
	 *    wp types relationship generate --definition=authorship --child-items=10000
	 *
	 * @subcommand generate
	 * @synopsis [--parent=<string>] [--child=<string>] [--definition=<string>] [--cardinality=<string>] [--parent-items=<string>] [--child-items=<string>]
	 *
	 * @since 1.0
	 */
	public function generate( $args, $assoc_args ) {
		if ( ! apply_filters( 'toolset_is_m2m_enabled', false ) ) {
			\WP_CLI::error( 'Toolset m2m is not activated' );
		}
		if ( ! defined( 'TOOLSET_VERSION' ) || version_compare(TOOLSET_VERSION,  "2.5.3") < 0 ) {
			\WP_CLI::error( 'Please, update your Toolset Common version' );
		}

		$defaults = array(
			'parent-items' => 0,
			'child-items' => 0,
		);
		$relationship_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! isset( $relationship_args['definition'] ) ) {
			\WP_CLI::error( 'Relationship definition is required' );
			\WP_CLI::runcommand( 'help types relationship generate' );
		}

		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$definition = $definition_repository->get_definition( $relationship_args['definition'] );
		if ( ! isset( $relationship_args['definition'] ) ) {
			\WP_CLI::error( 'Relationship definition doesn\'t exist, please create it.' );
		}
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$parent_post_type_object = $post_type_repository->get( $definition->get_parent_type()->get_types()[0] );
		$child_post_type_object = $post_type_repository->get( $definition->get_child_type()->get_types()[0] );

		$parent_cardinality = $definition->get_cardinality()->get_parent();
		if ( $parent_cardinality < 0 ) {
			$parent_cardinality = PHP_INT_MAX;
		}
		$parent_items_number = $definition->get_cardinality()->is_one_to_many()
			? $relationship_args['parent-items']
			: min( $relationship_args['parent-items'], $parent_cardinality );
		$child_cardinality = $definition->get_cardinality()->get_child();
		if ( $child_cardinality < 0 ) {
			$child_cardinality = PHP_INT_MAX;
		}
		$child_items_number = min( $relationship_args['child-items'], $child_cardinality );

		$parent_items_ids = array();
		if ( $parent_items_number ) {
			$parent_items_ids = $this->generate_post_items( $parent_post_type_object, $parent_items_number );
		}
		if ( $child_items_number ) {
			if ( empty( $parent_items_ids ) ) {
				$parent_items_ids = get_posts( array(
					'fields' => 'ids',
					'posts_per_page' => -1,
					'post_type' => $parent_post_type_object->get_slug(),
				) );
			}
			// The behaviour differs depending on the relationship type.
			if ( ! empty( $parent_items_ids ) && $definition->get_cardinality()->is_one_to_many() ) {
				$this->generate_child_items_and_relate_one_to_many( $child_post_type_object, $child_items_number, $parent_items_ids, $definition );
			} elseif ( ! empty( $parent_items_ids ) && ! $definition->get_cardinality()->is_one_to_many() ) {
				$this->generate_child_items_and_relate_many_to_many( $child_post_type_object, $child_items_number, $parent_items_ids, $definition );
			}
		}
		\WP_CLI::success( 'Command executed correctly' );
	}


	/**
	 * Creates a new Post type object using m2m API
	 *
	 * @param String $slug Post type slug.
	 * @param String $name Post type plural name.
	 * @param String $singular_name Post type singular name.
	 *
	 * @return IToolset_Post_Type
	 */
	private function create_post_type( $slug, $name = null, $singular_name = null ) {
		$post_type_repository = \Toolset_Post_Type_Repository::get_instance();
		$parent_post_type_obj = $post_type_repository->get( $slug );
		if ( ! $parent_post_type_obj ) {
			$name = $name? $name : $slug;
			$singular_name = $singular_name? $singular_name : $slug;
			$parent_post_type_obj = $post_type_repository->create( $slug, $name, $singular_name );
			\WP_CLI::log( "Post type {$slug} created" );
			$post_type_repository->save( $parent_post_type_obj );
		} else {
			\WP_CLI::log( "Post type {$slug} exists" );
		}
		return $parent_post_type_obj;
	}


	/**
	 * Creates a new post type object
	 *
	 * @param String             $slug Relationship slug.
	 * @param IToolset_Post_Type $parent_post_type Parent post type object.
	 * @param IToolset_Post_Type $child_post_type Child post type object.
	 * @param Mixed[]            $extra Extra data: [
	 *                                                name: Definition name
	 *                                                singular_name: Definition singular name
	 *                                                cardinality: example: *..*
	 *                                              ]
	 *
	 * @return Toolset_Relationship_Definition
	 */
	private function create_relationship( $slug, \IToolset_Post_Type $parent_post_type, \IToolset_Post_Type $child_post_type, $extra = null ) {
		$definition_repository = \Toolset_Relationship_Definition_Repository::get_instance();
		$parent_element = \Toolset_Relationship_Element_Type::build_for_post_type( $parent_post_type->get_slug() );
		$child_element = \Toolset_Relationship_Element_Type::build_for_post_type( $child_post_type->get_slug() );
		$definition = $definition_repository->create_definition( $slug, $parent_element, $child_element );
		if ( isset( $extra['name'] ) ) {
			$definition->set_display_name( $extra['name'] );
		}
		if ( isset( $extra['singular_name'] ) ) {
			$definition->set_display_name_singular( $extra['singular_name'] );
		}
		if ( isset( $extra['cardinality'] ) ) {
			$cardinality = explode( '..', $extra['cardinality'] );
			if ( count( $cardinality ) === 2 ) {
				switch ( $cardinality[0] ) {
					case '*':
						$parent_cardinality = \Toolset_Relationship_Cardinality::INFINITY;
						break;
					default:
						$parent_cardinality = (int) $cardinality[0];
						break;
				}
				switch ( $cardinality[1] ) {
					case '*':
						$child_cardinality = \Toolset_Relationship_Cardinality::INFINITY;
						break;
					default:
						$child_cardinality = (int) $cardinality[1];
						break;
				}
				$definition->set_cardinality( new \Toolset_Relationship_Cardinality( $parent_cardinality, $child_cardinality ) );
			}
		}
		$definition_repository->save_definitions();
		\WP_CLI::log( "Relationship definition {$slug} created" );
		return $definition;
	}


	/**
	 * Generates a list of post types
	 *
	 * @param IToolset_Post_Type $post_type Post type element.
	 * @param Integer            $count Number of post type to create.
	 *
	 * @return Integer[] List of IDs of posts created.
	 */
	private function generate_post_items( \IToolset_Post_Type $post_type, $count ) {
		$ids = array();
		$post_type_name = $post_type->get_label();
		$post_type_slug = $post_type->get_slug();
		$progress = \WP_CLI\Utils\make_progress_bar( "Generating {$post_type_name} items", $count );
		for( $i = 0; $i < $count; $i++ ) {
			$post = array(
				'post_title' => $post_type_name . ' #' . ( $i + 1 ),
				'post_type' => $post_type_slug,
				'post_status' => 'publish',
			);
			$ids[] = wp_insert_post( $post );
			$progress->tick();
		}
		$progress->finish();
		\WP_CLI::log( $count . ' items created' );
		return $ids;
	}


	/**
	 * Generates a list of child post types and related them to the existing parents using a one-to-many relationship
	 *
	 * @param IToolset_Post_Type              $post_type Post type element.
	 * @param Integer                         $max_count Number of post type to create.
	 * @param Integer[]                       $parent_ids List of parents post ids.
	 * @param Toolset_Relationship_Definition $definition Relationship definition.
	 *
	 * @return Integer[] List of IDs of posts created.
	 */
	private function generate_child_items_and_relate_one_to_many( \IToolset_Post_Type $post_type, $max_count, $parent_ids, \Toolset_Relationship_Definition $definition ) {
		$ids = array();
		$post_type_name = $post_type->get_label();
		$post_type_slug = $post_type->get_slug();
		$total_count = 0;

		foreach( $parent_ids as $parent_id ) {
			$count = rand( 1, $max_count );
			\WP_CLI::log( "Creating relationships for parent ID:{$parent_id}" );
			$progress = \WP_CLI\Utils\make_progress_bar( "Generating {$post_type_name} items for parent ID:{$parent_id}", $count );
			for( $i = 0; $i < $count; $i++ ) {
				$post = array(
					'post_title' => $post_type_name . ' #' . ( $total_count + 1 ),
					'post_type' => $post_type_slug,
					'post_status' => 'publish',
				);
				$child_id = wp_insert_post( $post );
				$definition->create_association( $parent_id, $child_id );
				$ids[] = $child_id;
				$progress->tick();
				$total_count++;
			}
			$progress->finish();
		}
		\WP_CLI::log( $total_count . ' items created and associated to they parent' );
		return $ids;
	}


	/**
	 * Generates a list of child post types and related them to the existing parents using a many-to-many relationship
	 *
	 * @param IToolset_Post_Type              $post_type Post type element.
	 * @param Integer                         $child_count Number of post type to create.
	 * @param Integer[]                       $parent_ids List of parents post ids.
	 * @param Toolset_Relationship_Definition $definition Relationship definition.
	 *
	 * @return Integer[] List of IDs of posts created.
	 */
	private function generate_child_items_and_relate_many_to_many( \IToolset_Post_Type $post_type, $child_count, $parent_ids, \Toolset_Relationship_Definition $definition ) {
		$ids = array();
		$total_count = 0;
		$parent_count = count ( $parent_ids );

		$child_ids = $this->generate_post_items( $post_type, $child_count );

		$elements_ids = array( $parent_ids, $child_ids );
		foreach( $elements_ids as $type => $element_ids ) {
			$other_ids = $elements_ids[ ( $type + 1 ) % 2 ];
			foreach( $element_ids as $element_id ) {
				$related_id_keys = array_rand( $other_ids, rand( 1, count( $other_ids ) ) );
				$progress = \WP_CLI\Utils\make_progress_bar( "Creating relationships for element ID:{$element_id}", count( $related_id_keys ) );
				foreach( $related_id_keys as $related_id_key ) {
					$definition->create_association( $element_id, $other_ids[ $related_id_key ] );
					$progress->tick();
					$total_count++;
				}
				$progress->finish();
			}
		}
		\WP_CLI::log( $total_count . ' associations created' );
		return $child_ids;
	}
}
