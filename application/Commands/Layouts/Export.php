<?php

namespace OTGS\Toolset\CLI\Layouts;

use WPDD_Layouts_Theme ;
use ZIP ;

/**
 * Layouts export command.
 */
class Export extends LayoutsCommand {

	/**
	 * Exports Layouts to a ZIP file.
	 *
	 * ## Options
	 *
	 * [--overwrite]
	 * : Overwrite existing ZIP file.
	 *
	 * <file>
	 * : The path of the exported ZIP file.
	 *
	 * ## Examples
	 *
	 *     wp layouts export <file>
	 *
	 * @param array $args The array of command-line arguments.
	 * @param array $assoc_args The associative array of command-line options.
	 */
	public function __invoke( $args, $assoc_args ) {

		list( $export_filename ) = $args;

		if ( empty ( $export_filename ) ) {
			$this->wp_cli()->error( __( 'You must specify a valid file.', 'toolset-cli' ) );
		}

		// Does the specified filename have a ".zip" extension? If not, add it and notify the user.
		// Returns filename extension without a period prefixed to it.
		$export_filename_extension = pathinfo( $export_filename, PATHINFO_EXTENSION );

		if ( ! $export_filename_extension || strtolower ( $export_filename_extension ) != 'zip' ) {
			// Returns filename without the path to the parent directory.
			$export_filename_basename = pathinfo( $export_filename, PATHINFO_BASENAME );

			$this->wp_cli()->warning ( __( sprintf ('"%1$s" lacks a ".zip" extension. Adding it. The new filename will be "%1$s.zip."', $export_filename_basename ), 'toolset-cli' ) ) ;

			// Append '.zip' to export filename.
			$export_filename .= '.zip' ;
		}

		$overwrite_export_filename = false ;
		// Parse command-line options.
		if ( count ( $assoc_args ) > 0 ) {
			if ( isset ( $assoc_args['overwrite'] ) ) {
				$overwrite_export_filename = true ;
			}
		}

		// Abort if the file already exists and we are not deliberately overwriting it.
		if ( file_exists ( $export_filename ) && ! $overwrite_export_filename ) {
			$this->wp_cli()->error( __( sprintf ('"%1$s" already exists. Aborting. (Use "--overwrite" to overwrite %1$s.)', $export_filename), 'toolset-cli' ) );
		}

		// Load the export code from the Layouts plugin.
		require_once WPDDL_ABSPATH . '/inc/theme/wpddl.theme-support.class.php' ;

		$layouts = new WPDD_Layouts_Theme () ;
		$layouts_data = $layouts->export_for_download() ;
		// The "parent" folder holding the layouts files, when unzipped.
		$directory_name = pathinfo( $export_filename, PATHINFO_FILENAME );

		$export_status = false ;
		if (class_exists('Zip')) {
			$zip = new Zip();
			$zip->addDirectory( $directory_name );

			foreach ( $layouts_data as $file_data ) {
				$zip->addFile( $file_data['file_data'], $directory_name . '/' . $file_data['file_name'] );
			}

			if ( file_put_contents ( $export_filename, $zip->getZipData() ) ) {
				$export_status = true ;
			}

		}
		else {
			$this->wp_cli()->error ( __( 'The ZIP library is not present. Aborting.', 'toolset-cli' ) ) ;
		}

		if ( $export_status === true ) {
			$this->wp_cli()->success( sprintf ( __( 'The layouts were exported successfully to "%s."'), $export_filename ), 'toolset-cli' );
		} else {
			$this->wp_cli()->error( __( 'There was an error exporting the layouts.', 'toolset-cli' ) );
		}

	}

}