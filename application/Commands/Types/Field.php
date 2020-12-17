<?php

namespace OTGS\Toolset\CLI\Types;

use Toolset_Element_Domain;
use Toolset_Field_Definition;
use Toolset_Field_Definition_Abstract;
use Toolset_Field_Definition_Factory;
use Toolset_Field_Definition_Post;
use Toolset_Field_Group;
use Toolset_Field_Type_Definition_Factory;
use Toolset_Field_Utils;
use function WP_CLI\Utils\format_items;

/**
 * Field definition commands.
 */
class Field extends TypesCommand {


	/**
	 * Create a new field definition.
	 *
	 * Note: The field definition needs to be assigned to a custom field group by using
	 * the 'types field group add_field' command.
	 *
	 * Note: This may not work properly for field types that require specific configuration and it may need to be
	 * extended.
	 *
	 * ## OPTIONS
	 *
	 * --domain=<string>
	 * : Element domain of the field group (posts|terms|users). Only the posts domain is supported at the moment.
	 *
	 * --type=<string>
	 * : Field type slug as defined in Toolset_Field_Type_Definition_Factory.
	 *
	 * --slug=<string>
	 * : Custom field slug. Must be unique within the given domain.
	 *
	 * [--name=<string>]
	 * : Display name of the custom field. If not provided, the slug will be used instead.
	 *
	 * @param $args
	 * @param $parameters
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function create( $args, $parameters ) {
		$field_type = Toolset_Field_Type_Definition_Factory::get_instance()
			->load_field_type_definition( $parameters['type'] );
		if ( ! $field_type ) {
			$this->wp_cli()->error( __( 'Unrecognized field type.', 'toolset-cli' ) );
		}
		$definition_factory = Toolset_Field_Definition_Factory::get_factory_by_domain( $parameters['domain'] );

		switch ( $parameters['domain'] ) {
			case Toolset_Element_Domain::TERMS:
			case Toolset_Element_Domain::USERS:
				$this->wp_cli()->error( __( 'Only the posts domain is supported at the moment.', 'toolset-cli' ) );
				return;
			case Toolset_Element_Domain::POSTS:
				$definition_class = Toolset_Field_Definition_Post::class;
				break;
			default:
				$this->wp_cli()->error( __( 'Invalid or missing element domain.', 'toolset-cli' ) );
				return;
		}

		$field_slug = $parameters['slug'];
		// Workaround, as creating new fields is currently possible only from the legacy Types codebase.
		$new_field_slug = $definition_factory->create_field_definition_for_existing_fields( 'wpcf-' . $field_slug );
		if ( false === $new_field_slug ) {
			$this->wp_cli()->error( __( 'Toolset reports an error when creating a new field definition.', 'toolset-cli' ) );
			return;
		}
		$this->wp_cli()->log( __( 'New field slug created: ', 'toolset-cli' ) . $new_field_slug );

		$field_definition = new $definition_class(
			$field_type,
			[
				'slug' => $parameters['slug'],
				'id' => $parameters['slug'],
				'type' => $field_type->get_slug(),
				'name' => array_key_exists( 'name', $parameters ) ? $parameters['name'] : $parameters['slug'],
				'meta_type' => Toolset_Field_Utils::domain_to_legacy_meta_type( $parameters['domain'] ),
			],
			$definition_factory
		);

		$definition_factory->update_definition( $field_definition );
	}


	/**
	 * List custom field definitions.
	 *
	 * ## OPTIONS
	 *
	 * --domain=<string>
	 * : Element domain of the field group (posts|terms|users).
	 *
	 * @subcommand list
	 *
	 * @param $args
	 * @param $parameters
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function list_items( $args, $parameters ) {
		$definition_factory = Toolset_Field_Definition_Factory::get_factory_by_domain( $parameters['domain'] );

		$field_definitions = array_map(
			static function ( Toolset_Field_Definition_Abstract $definition ) {
				return [
					'slug' => $definition->get_slug(),
					'type' => $definition instanceof Toolset_Field_Definition ? $definition->get_type_slug() : 'n/a',
					'groups' => implode( ', ', array_map(
						static function ( Toolset_Field_Group $group ) {
							return $group->get_slug();
						},
						$definition->get_associated_groups()
					) ),
				];
			},
			$definition_factory->load_all_definitions()
		);

		$this->wp_cli()->log( format_items( 'table', $field_definitions, [ 'slug', 'type', 'groups' ] ) );
	}

}
