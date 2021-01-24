<?php

namespace OTGS\Toolset\CLI\Views;

class Export extends Views_Commands {

	/**
	 * Export Views to an XML file.
	 *
	 * ## Options
	 *
	 * <file>
	 * : The filename of the exported file.
	 *
	 * [--overwrite]
	 * : Allow for overwriting an existing file.
	 *
	 * ## Examples
	 *
	 *     wp views export file
	 *
	 * @synopsis [--overwrite] <file>
	 *
	 * @param array $args The array of command-line arguments.
	 * @param array $assoc_args The associative array of command-line options.
	 */
	public function __invoke( $args, $assoc_args ) {
		list( $export_filename ) = $args;

		if ( empty ( $export_filename ) ) {
			\WP_CLI::warning( __( 'You must specify a valid file.', 'toolset-cli' ) );
			return;
		}

		// Does the specified filename have a ".xml" extension? If not, add it and notify the user.
		// Returns filename extension without a period prefixed to it.
		$export_filename_extension = pathinfo( $export_filename, PATHINFO_EXTENSION );

		if ( ! $export_filename_extension || strtolower ( $export_filename_extension ) != 'xml' ) {
			// Returns filename without the path to the parent directory.
			$export_filename_basename = pathinfo( $export_filename, PATHINFO_BASENAME );

			\WP_CLI::warning ( __( sprintf ('"%1$s" lacks a ".xml" extension. Adding it. The new filename will be "%1$s.xml."', $export_filename_basename ), 'toolset-cli' ) ) ;

			// Append '.xml' to export filename.
			$export_filename .= '.xml' ;
		}

		// Warn if the file already exists.
		if ( ! array_key_exists( 'overwrite', $assoc_args ) && is_file( $export_filename ) ) {
			\WP_CLI::error( sprintf( __( '"%s" already exists. Aborting.', 'toolset-cli' ), $export_filename ) );
		}

		require_once WPV_PATH . '/inc/wpv-import-export.php';

		$exported_data = wpv_admin_export_data( false );

		$written_bytes = file_put_contents( $export_filename, $exported_data );

		if ( ! $written_bytes ) {
			\WP_CLI::error( __( 'There was an error exporting the views.', 'toolset-cli' ) );
			return;
		}

		\WP_CLI::success( sprintf( __( 'The views were exported successfully to "%s".', 'toolset-cli' ), $export_filename ) );
	}

}
