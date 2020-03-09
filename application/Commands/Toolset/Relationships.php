<?php

namespace OTGS\Toolset\CLI\Commands\Toolset;

use OTGS\Toolset\CLI\Commands\ToolsetCommand;

class Relationships extends ToolsetCommand {

	const STATUS_SUMMARY = 'summary';

	const STATUS_SET_STATUS = 'set-status';

	const STATUS_INITIALIZE_DATABASE = 'initialize-database';

	const STATUS_SET_DATABASE_LAYER = 'set-database-layer';

	const STATUS_CLEAR = 'clear';

	const STATUS_COMMANDS = [
		self::STATUS_SUMMARY,
		self::STATUS_SET_STATUS,
		self::STATUS_INITIALIZE_DATABASE,
		self::STATUS_SET_DATABASE_LAYER,
		self::STATUS_CLEAR,
	];

	const MIGRATION_STATE = 'state';

	// STATUS
	//
	//
	//
	//


	/**
	 * Retrieves or manipulates the status of the relationship functionality in Toolset. USE WITH GREAT CAUTION.
	 *
	 * Exactly one of the following options must be provided.
	 *
	 * ## OPTIONS
	 *
	 * [--summary]
	 * : Prints the summary of the current status.
	 *
	 * [--set-status=<status>]
	 * : Enable or disable the whole post relationship functionality. Depending on the current version of
	 * Toolset plugins, this might bring back the legacy post relationships in Types.
	 * It only sets the flag in options, the database structure is left untouched.
	 * ---
	 * options:
	 *   - enabled
	 *   - 1
	 *   - true
	 *   - disabled
	 *   - 0
	 *   - false
	 * ---
	 *
	 * [--initialize-database]
	 * : Attempts to create the database tables for relationships as on a clean site.
	 *
	 * [--set-database-layer=<version>]
	 * : Set the currently used database layer to a provided version. The version value must be accepted
	 * by Toolset. It does not touch the database structure in any way.
	 *
	 * [--clear]
	 * : Delete all options regarding the relationship functionality in Toolset. Doesn't directly touch
	 * database structure in any way, but note that Toolset may try to create new tables on a fresh site
	 * if it doesn't have any settings stored.
	 *
	 * @param string[] $args
	 * @param string[] $parameters
	 */
	public function status( $args, $parameters ) {
		if ( count( $parameters ) > 1 || count( $args ) > 0 ) {
			$this->wp_cli()->error( __( 'Invalid subcommand syntax.', 'toolset-cli' ) );

			return;
		}
		$subcommand = $this->get_subcommand( $parameters, self::STATUS_COMMANDS );

		\Toolset_Relationship_Controller::get_instance()->force_autoloader_initialization();

		switch ( $subcommand ) {
			case self::STATUS_SET_STATUS:
				$status = (string) \toolset_getarr( $parameters, self::STATUS_SET_STATUS );
				$enable_m2m = in_array( $status, [ 'true', '1', 'yes', 'enabled' ], true );
				$disable_m2m = in_array( $status, [ 'false', '0', 'no', 'disabled' ], true );
				if ( ! $enable_m2m && ! $disable_m2m ) {
					$this->wp_cli()->error( 'Unrecognized status value.' );
				}

				$this->set_relationship_status( $enable_m2m );
				break;
			case self::STATUS_INITIALIZE_DATABASE:
				$this->initialize_relationships();
				break;
			case self::STATUS_SET_DATABASE_LAYER:
				$this->set_database_layer( \toolset_getarr( $parameters, self::STATUS_SET_DATABASE_LAYER ) );
				break;
			case self::STATUS_CLEAR:
				$this->clear_status();
				break;
		}

		$this->print_status_summary();
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


	/**
	 * @return \OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerMode|null
	 */
	private function get_database_layer_manager() {
		/** @var null|\OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerMode $database_layer_mode_manager */
		$database_layer_mode_manager = $this->toolset_dic_make( '\OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerMode' );

		return $database_layer_mode_manager;
	}


	private function get_database_layer_mode() {
		$database_layer_mode_manager = $this->get_database_layer_manager();
		if ( ! $database_layer_mode_manager ) {
			return null;
		}

		return $database_layer_mode_manager->get();
	}


	/**
	 * @return \OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerFactory|null
	 */
	private function get_database_layer_factory() {
		/** @var \OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerFactory|null $database_layer_factory */
		$database_layer_factory = $this->toolset_dic_make( '\OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerFactory' );

		return $database_layer_factory;
	}


	/**
	 * @return \OTGS\Toolset\Common\Relationships\InitialStateSetup|null
	 */
	private function get_initial_state_setup() {
		/** @var \OTGS\Toolset\Common\Relationships\InitialStateSetup $initial_state_setup */
		$initial_state_setup = $this->toolset_dic_make( '\OTGS\Toolset\Common\Relationships\InitialStateSetup' );

		return $initial_state_setup;
	}


	private function is_m2m_enabled() {
		return apply_filters( 'toolset_is_m2m_enabled', false );
	}


	private function print_status_summary() {
		$is_m2m_enabled = $this->is_m2m_enabled();

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


	private function set_relationship_status( $enable_m2m ) {
		if ( $this->is_m2m_enabled() === $enable_m2m ) {
			$this->wp_cli()->warning( __( 'There\'s nothing to do.', 'toolset-cli' ) );

			return;
		}

		try {
			$this->get_initial_state_setup()->store_state( $enable_m2m );
			\Toolset_Relationship_Controller::get_instance()->reset();

			$this->wp_cli()->success( __( 'Relationship functionality status updated.', 'toolset-cli' ) );
		} catch ( \Throwable $t ) {
			$this->wp_cli()->error( sprintf(
				'%s: "%s"',
				__( 'Unable to set the relationship functionality status', 'toolset-cli' ),
				$t->getMessage()
			) );
		}
	}


	private function initialize_relationships() {
		$this->wp_cli()->log( __( 'Installing relationships database tables...', 'toolset-cli' ) );
		$initial_state_setup = $this->get_initial_state_setup();

		remove_filter( 'toolset_allow_auto_enabling_m2m', '__return_false' );
		$is_success = $initial_state_setup->enable_relationships();
		add_filter( 'toolset_allow_auto_enabling_m2m', '__return_false' );

		if ( $is_success ) {
			$this->wp_cli()->success( __( 'Relationships database tables installed.', 'toolset-cli' ) );
		} else {
			$this->wp_cli()
				->error( __( 'Unable to properly install the relationships database tables', 'toolset-cli' ) );
		}
	}


	private function set_database_layer( $database_layer_value ) {
		$this->wp_cli()->log( __( 'Setting the database layer version...', 'toolset-cli' ) );

		try {
			$this->get_database_layer_manager()->set( $database_layer_value );
		} catch ( \Throwable $t ) {
			$this->wp_cli()->error( $t->getMessage() );

			return;
		}

		$this->wp_cli()->success( __( 'Database layer version updated.', 'toolset-cli' ) );
	}


	private function clear_status() {
		$this->wp_cli()->log( __( 'Deleting options about relationships functionality...', 'toolset-cli' ) );
		try {
			delete_option( \Toolset_Relationship_Controller::IS_M2M_ENABLED_OPTION );
			delete_option( \OTGS\Toolset\Common\Relationships\DatabaseLayer\DatabaseLayerMode::OPTION_NAME );
			\Toolset_Relationship_Controller::get_instance()->reset();
		} catch ( \Throwable $t ) {
			$this->wp_cli()->error( $t->getMessage() );

			return;
		}

		$this->wp_cli()->success( __( 'Options deleted.', 'toolset-cli' ) );
	}


	// MIGRATE
	//
	//
	//
	//

	/**
	 * Perform a single database migration step based on the current migration state and return the next one.
	 *
	 * If no state is provided, the initial one will be generated.
	 *
	 * ## OPTIONS
	 *
	 * [--state=<state-value>]
	 * : Serialized migration state.
	 *
	 * @param string[] $args
	 * @param string[] $parameters
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function migrate( $args, $parameters ) {
		try {
			$database_layer_factory = $this->get_database_layer_factory();
			$migration_controller = $database_layer_factory->migration_controller();

			if ( ! array_key_exists( self::MIGRATION_STATE, $parameters ) ) {
				$this->wp_cli()->log( __( 'No migration state provided, returning the initial one.', 'toolset-cli' ) );
				$next_state = $migration_controller->get_initial_state();
			} else {
				try {
					$current_state = $migration_controller->unserialize_migration_state( $parameters[ self::MIGRATION_STATE ] );
				} catch ( \Exception $e ) {
					$this->wp_cli()->error( sprintf(
						'%s: %s',
						__( 'Unable to read the provided migration state.', 'toolset-cli' ),
						$e->getMessage()
					) );

					return;
				}

				$this->wp_cli()->log( __( 'Performing the migration step as requested...', 'toolset-cli' ) );

				try {
					$next_state = $migration_controller->do_next_step( $current_state );
				} catch ( \Exception $e ) {
					$this->wp_cli()->error( sprintf(
						'%s: "%s"',
						__( 'An error has occurred while performing a migration step', 'toolset-cli' ),
						$e->getMessage()
					) );

					return;
				}

				if ( $next_state->get_result()->is_success() ) {
					$this->wp_cli()->success( sprintf(
						'%s: "%s"',
						__( 'The migration step has been completed successfully', 'toolset-cli' ),
						$next_state->get_result()->get_message()
					) );
				} else {
					$this->wp_cli()->error( sprintf(
						'%s: "%s"',
						__( 'There has been an error while performing the migration step', 'toolset-cli' ),
						$next_state->get_result()->get_message()
					) );
				}
			}

			$this->wp_cli()->log( sprintf(
				"%s:\n\n%s\n",
				__( 'Next migration state', 'toolset-cli' ),
				$next_state->serialize()
			) );
		} catch ( \RuntimeException $e ) {
			$this->wp_cli()->error( $e->getMessage() );

			return;
		}
	}

}
