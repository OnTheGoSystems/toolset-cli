<?php

namespace OTGS\Toolset\CLI\Commands\Toolset;

use OTGS\Toolset\CLI\Commands\ToolsetCommand;

class Relationships extends ToolsetCommand {

	const STATUS_SUMMARY = 'summary';

	const STATUS_COMMANDS = [ self::STATUS_SUMMARY ];


	/**
	 * @param string[] $args
	 * @param string[] $parameters
	 */
	public function status( $args, $parameters ) {
		$subcommand = $this->get_subcommand( $parameters, self::STATUS_COMMANDS );

		if ( null === $subcommand ) {
			$subcommand = self::STATUS_SUMMARY;
		}

		switch ( $subcommand ) {
			case self::STATUS_SUMMARY:
				$this->print_status_summary();

				return;
		}
	}


	private function print_boolean_list( $condition, $title, $yes = null, $no = null ) {
		$this->wp_cli()->log( sprintf(
			' - %s: %s',
			$title,
			$condition
				? $this->wp_cli()->green( $yes ? : __( 'yes', 'toolset-cli' ) )
				: $this->wp_cli()->red( $no ? : __( 'no', 'toolset-cli' ) )
		) );
	}


	private function has_legacy_relationships() {
		if ( ! class_exists( '\Toolset_Condition_Plugin_Types_Has_Legacy_Relationships' ) ) {
			$this->wp_cli()
				->warning( __( 'Unable to determine if Types contains legacy relationships.', 'toolset-cli' ) );
			return false;
		}

		$condition = new \Toolset_Condition_Plugin_Types_Has_Legacy_Relationships();
		return $condition->is_met();
	}


	private function get_database_layer_mode() {
		/** @var null|\OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerMode $database_layer_mode_manager */
		$database_layer_mode_manager = $this->toolset_dic_make( '\OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerMode' );
		if ( ! $database_layer_mode_manager ) {
			return null;
		}

		return $database_layer_mode_manager->get();
	}


	private function print_status_summary() {
		$is_m2m_enabled = apply_filters( 'toolset_is_m2m_enabled', false );

		$this->wp_cli()->log(
			$this->wp_cli()->white( __( 'Status of Toolset Relationships:', 'toolset-cli' ) . PHP_EOL )
		);
		$this->print_boolean_list(
			$is_m2m_enabled,
			__( 'Relationship functionality', 'toolset-cli' ),
			__( 'enabled', 'toolset-cli' ),
			__( 'disabled', 'toolset-cli' )
		);

		if ( $is_m2m_enabled ) {
			$db_layer_mode = $this->get_database_layer_mode();
			$this->wp_cli()->log( sprintf(
				' - %s: %s',
				__( 'Database layer mode', 'toolset-cli' ),
				$db_layer_mode ? $this->wp_cli()->white( $db_layer_mode ) : $this->wp_cli()->red( 'N/A' )
			) );
		} else {
			$is_m2m_ready = apply_filters( 'toolset_is_m2m_ready', false );
			$this->print_boolean_list(
				$is_m2m_ready,
				__( 'Ready for relationships', 'toolset-cli' )
			);

			$this->print_boolean_list(
				$this->has_legacy_relationships(),
				__( 'Has legacy Type spost relationships', 'toolset-cli' )
			);
		}
	}

}
