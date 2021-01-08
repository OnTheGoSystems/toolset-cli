<?php

namespace OTGS\Toolset\CLI\Views;

use WPV_View;

class Export extends Views_Commands {

	/**
	 * Exports Views to a zip file.
	 *
	 * ## Options
	 *
	 * <file>
	 * : The filename of the exported file.
	 *
	 * ## Examples
	 *
	 *     wp views export file
	 *
	 * @synopsis <file>
	 *
	 * @param array $args The arguments of the post type query.
	 * @param array $assoc_args The associative arguments of the post type query.
	 */
	public function __invoke ( $args, $assoc_args ) {
		list( $export_filename ) = $args;

		if ( empty ( $export_filename ) ) {
			\WP_CLI::warning( __( 'You must specify a valid file.', 'toolset-cli' ) );
			return;
		}

		// Warn if the file already exists.
		if ( file_exists ( $export_filename ) ) {
			\WP_CLI::warning( __( sprintf ('"%s" already exists. Exiting.', $export_filename), 'toolset-cli' ) );
			return; // NOTE: quit now, or continue anyway? maybe add an option to continue?

		}

		require_once WPV_PATH. '/inc/wpv-import-export.php' ;

		$exported_data = wpv_admin_export_data ( false ) ;

		if ( file_put_contents($export_filename, $exported_data) ) {
			\WP_CLI::success( __( sprintf ( 'The views were exported successfully to "%s."', $export_filename ) , 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'There was an error while exporting the views.', 'toolset-cli' ) );
		}

	}

}