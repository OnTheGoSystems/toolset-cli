<?php

namespace OTGS\Toolset\CLI\Views;

class Export extends Views_Commands {

	/**
	 * Exports Views to a zip file.
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
	 * @synopsis <file> [--overwrite]
	 *
	 * @param array $args The arguments of the post type query.
	 * @param array $assoc_args The associative arguments of the post type query.
	 */
	public function __invoke( $args, $assoc_args ) {
		list( $export_filename ) = $args;

		if ( empty ( $export_filename ) ) {
			\WP_CLI::warning( __( 'You must specify a valid file.', 'toolset-cli' ) );

			return;
		}

		// Warn if the file already exists.
		if ( ! array_key_exists( 'overwrite', $assoc_args ) && is_file( $export_filename ) ) {
			\WP_CLI::warning( sprintf( __( '"%s" already exists. Exiting.', 'toolset-cli' ), $export_filename ) );

			return;
		}

		require_once WPV_PATH . '/inc/wpv-import-export.php';

		$exported_data = wpv_admin_export_data( false );

		$written_bytes = file_put_contents( $export_filename, $exported_data );

		if ( ! $written_bytes ) {
			\WP_CLI::error( __( 'There was an error while exporting the views.', 'toolset-cli' ) );

			return;
		}

		\WP_CLI::success( sprintf( __( 'The views were exported successfully to "%s".', 'toolset-cli' ), $export_filename ) );
	}

}
