<?php

namespace OTGS\Toolset\CLI\Views;

use WP_Error;

class Import extends Views_Commands {

	/**
	 * Import Views from an XML file.
	 *
	 * Omitted options default to 'false'.
	 *
	 * ## Options
	 *
	 * [--views-overwrite]
	 * : Bulk overwrite if View or WordPress Archive exists.
	 *
	 * [--views-delete]
	 * : Delete any existing Views or WordPress Archives that are not in the import.
	 *
	 * [--view-templates-overwrite]
	 * : Bulk overwrite if Content Template exists.
	 *
	 * [--view-templates-delete]
	 * : Delete any existing Content Templates that are not in the import.
	 *
	 * [--view-settings-overwrite]
	 * : Overwrite Views settings.
	 *
	 * <file>
	 * : The path to the XML file to import.
	 *
	 * ## Examples
	 *
	 *     wp views import <file>
	 *     wp views import --views-overwrite <file>
	 *
	 * @synopsis [--views-overwrite] [--views-delete] [--view-templates-overwrite] [--view-templates-delete] [--view-settings-overwrite] <file>
	 *
	 * @param array $args The array of command-line arguments.
	 * @param array $assoc_args The associative array of command-line options.
	 */
	public function __invoke( $args, $assoc_args ) {
		// Get the filename to import.
		list( $import_filename ) = $args;

		if ( empty ( $import_filename ) ) {
			\WP_CLI::error( __( 'You must specify a valid file to import.', 'toolset-cli' ) );
		}

		if ( ! is_file( $import_filename ) ) {
			\WP_CLI::error( sprintf( __( '"%s" does not exist. Exiting.', 'toolset-cli' ), $import_filename ) );
		}

		// Returns filename extension without a period prefixed to it.
		$extension = pathinfo( $import_filename, PATHINFO_EXTENSION );

		// Does the file have a ".xml" extension?
		if ( ! $extension || strtolower( $extension ) !== 'xml' ) {
			\WP_CLI::error( sprintf( __( '"%s" is not in XML format.', 'toolset-cli' ), $import_filename ) );
			return;
		}

		// Load the import code from the Views plugin.
		require_once WPV_PATH . '/embedded/inc/wpv-import-export-embedded.php';

		// Array of arguments to pass to wpv_api_import_from_file().
		$import_args = [];
		$import_args['import-file'] = $import_filename;

		// Parse command-line options and add to $import_args[].
		if ( count( $assoc_args ) > 0 ) {
			foreach ( $assoc_args as $option => $value ) {
				// We ignore the $value. The presence of the element is enough.
				$import_args[ $option ] = true;
			}
		}

		$import_status = wpv_api_import_from_file( $import_args );

		if ( $import_status instanceof WP_Error || ! $import_status ) {
			\WP_CLI::error( sprintf(
				__( 'There was an error importing the views: "%s"', 'toolset-cli' ),
				$import_status instanceof WP_Error ? $import_status->get_error_message() : 'unknown error'
			) );
			return;
		}

		\WP_CLI::success( sprintf( __( 'The views were imported successfully from "%s."', 'toolset-cli' ), $import_filename ) );
	}

}
