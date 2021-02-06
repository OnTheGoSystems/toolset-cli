<?php

namespace OTGS\Toolset\CLI\Layouts;

use WPDD_Layouts_Theme ;

/**
 * Layouts import command.
 */
class Import extends LayoutsCommand {

	/**
	 * Imports Layouts from a ZIP file.
	 *
	 * Omitted options default to 'false'.
	 *
	 * ## Options
	 *
	 * [--layouts-overwrite]
	 * : Overwrite any layout if it already exists.
	 *
	 * [--layouts-delete]
	 * : Delete any existing layouts that are not in the import.
	 *
	 * [--overwrite-layouts-assignment]
	 * : Overwrite layout assignments.
	 *
	 * <file>
	 * : The path to the ZIP file to import.
	 *
	 * ## Examples
	 *
	 *     wp layouts import <file>
	 *     wp layouts import --layouts-overwrite --layouts-delete --overwrite-layouts-assignment <file>
	 *
	 * @param array $args The array of command-line arguments.
	 * @param array $assoc_args The associative array of command-line options.
	 */
	public function __invoke( $args, $assoc_args ) {
		// Get the filename to import.
		list( $import_filename ) = $args;

		// Is the file empty?
		if ( empty ( $import_filename ) ) {
			$this->wp_cli()->error( __( 'You must specify a valid file to import.', 'toolset-cli' ) );
		}

		// Does the import file exist?
		if ( ! file_exists ( $import_filename ) ) {
			$this->wp_cli()->error( sprintf ( __( '"%s" does not exist. Aborting.' ), $import_filename), 'toolset-cli' );
		}

		// Returns filename extension without a period prefixed to it.
		$import_filename_extension = pathinfo( $import_filename, PATHINFO_EXTENSION );

		// Does the file have a ".zip" extension?
		if ( ! $import_filename_extension || strtolower ( $import_filename_extension ) != 'zip' ) {
			$this->wp_cli()->error( sprintf ( __( '"%s" is not in ZIP format.'), $import_filename), 'toolset-cli' );
		}

		// Load the import code from the Layouts plugin.
		require_once WPDDL_ABSPATH . '/inc/theme/wpddl.theme-support.class.php' ;

		$layouts = new WPDD_Layouts_Theme () ;

		// Array of arguments to pass to import_layouts() method.
		$import_args = array() ;

		// Parse command-line options and add to $import_args[].
		if ( count ( $assoc_args ) > 0 ) {
			foreach ( $assoc_args as $option => $value ) {
				// We ignore the $value. The presence of the element is enough.
				$import_args[$option] = true ;
			}
		}

		// Returns filename without the path to the parent directory.
		$import_filename_basename = pathinfo( $import_filename, PATHINFO_BASENAME );

		// Create an array that holds the properties of the $_FILES array for compatibility with manage_manual_import() method.
		$files = [] ;
		$files['import-file']['name'] = $import_filename_basename ; // basename
		$files['import-file']['tmp_name'] = $import_filename ; // full path

		$import_succeeded = $layouts->import_layouts( $files, $import_args );

		if ( $import_succeeded ) {
			$this->wp_cli()->success( sprintf ( __( 'The layouts were imported successfully from "%s."'), $import_filename ) , 'toolset-cli' );
		} else {
			$this->wp_cli()->error( __( 'There was an error importing the layouts.', 'toolset-cli' ) );
		}

	}

}