<?php

namespace OTGS\Toolset\CLI\Views;

use WPV_View;

/**
 * Class Import
 * @package OTGS\Toolset\CLI\Views
 */
class Import extends Views_Commands {

/**
 * Imports Views from an XML file.
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
 *     wp --user=<admin> views import <file>
 *     wp --user=<admin> views import --views-overwrite <file>
 *
 * @synopsis [--views-overwrite] [--views-delete] [--view-templates-overwrite] [--view-templates-delete] [--view-settings-overwrite] <file>
 *
 * @param array $args The array of command-line arguments.
 * @param array $assoc_args The associative array of command-line options.
 */

	public function __invoke ( $args, $assoc_args ) {

		// Get the filename to import.
		list( $import_filename ) = $args;

		// Is the file empty?
		if ( empty ( $import_filename ) ) {
			\WP_CLI::error( __( 'You must specify a valid file to import.', 'toolset-cli' ) );
		}

		// Does the import file exist?
		if ( ! file_exists ( $import_filename ) ) {
			\WP_CLI::error( sprintf ( __( '"%s" does not exist. Exiting.' ), $import_filename), 'toolset-cli' );
		}

		// Returns filename extension without a period prefixed to it.
		$extension = pathinfo( $import_filename, PATHINFO_EXTENSION );

		// Does the file have a ".xml" extension?
		if ( ! $extension || strtolower ( $extension ) != 'xml' ) {
			\WP_CLI::error( sprintf ( __( '"%s" is not in XML format.'), $import_filename), 'toolset-cli' );
		}

		// Load the import code from the Views plugin.
		require_once WPV_PATH. '/embedded/inc/wpv-import-export-embedded.php' ;

		// Flag to track the import status.
		$import_status = false ;

		// Array of arguments to pass to wpv_api_import_from_file().
		$import_args = array() ;
		$import_args['import-file'] = $import_filename ;

		// Parse command-line options and add to $import_args[].
		if ( count ( $assoc_args ) > 0 ) {
			foreach ( $assoc_args as $option => $value ) {
				// We ignore the $value. The presence of the element is enough.
				$import_args[$option] = true ;
			}
		}

		$import_status = wpv_api_import_from_file ( $import_args ) ;
		// IMPORTANT: If the command is run without specifying a current_user_can( EDIT_VIEWS ), the import will fail.
		// wp --user=<wpadmin> views import <filename>

		if ( $import_status === true ) {
			\WP_CLI::success( sprintf ( __( 'The views were imported successfully from "%s."'), $import_filename ) , 'toolset-cli' );
		} else {
			\WP_CLI::error( __( 'There was an error importing the views. Did you run the command as a WordPress user with the appropriate capabilities?', 'toolset-cli' ) );
		}

	}

}