<?php

namespace OTGS\Toolset\CLI\Views;

use WP_Error;

class Import extends Views_Commands {


	const IMPORT_FORMAT_ZIP = 'zip';

	const IMPORT_FORMAT_XML = 'xml';

	const VALID_EXTENSIONS = [
		self::IMPORT_FORMAT_ZIP,
		self::IMPORT_FORMAT_XML,
	];


	/**
	 * Import Views from an XML or ZIP file.
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
	 * : The path to the XML or ZIP file to import.
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
	public function __invoke( array $args, array $assoc_args ) {
		// Get the filename to import.
		list( $import_filename ) = $args;

		if ( empty ( $import_filename ) ) {
			$this->wp_cli()->error(
				__( 'You must specify a valid file to import. Aborting.', 'toolset-cli' ), true
			);
			return;
		}

		if ( ! is_file( $import_filename ) ) {
			$this->wp_cli()->error( sprintf(
				__( '"%s" does not exist. Aborting.', 'toolset-cli' ), $import_filename
			), true );
			return;
		}

		// Returns filename extension without a period prefixed to it.
		$import_filename_extension = strtolower( pathinfo( $import_filename, PATHINFO_EXTENSION ) );
		$import_filename_basename = pathinfo( $import_filename, PATHINFO_BASENAME );

		if (
			! $import_filename_extension
			|| ! in_array( $import_filename_extension, self::VALID_EXTENSIONS )
		) {
			$this->wp_cli()->error( sprintf(
				__( '"%1$s" is in an invalid format%3$s. The valid formats are: %2$s. Aborting.', 'toolset-cli' ),
				$import_filename_basename,
				implode( ', ', self::VALID_EXTENSIONS ),
				$import_filename_extension !== '' ? ' ("' . $import_filename_extension . '")' : ''
			), true );
			return;
		}

		// Load the import code from the Views plugin.
		require_once WPV_PATH . '/embedded/inc/wpv-import-export-embedded.php';

		// Array of arguments to pass to wpv_api_import_from_file().
		$import_args = [ 'import-file' => $import_filename ];

		// Parse command-line options and add to $import_args[].
		if ( count( $assoc_args ) > 0 ) {
			foreach ( $assoc_args as $option => $value ) {
				// We ignore the $value. The presence of the element is enough.
				$import_args[ $option ] = true;
			}
		}

		// Run the import.
		$import_status = wpv_api_import_from_file( $import_args );

		if ( $import_status instanceof WP_Error || ! $import_status ) {
			$this->wp_cli()->error( sprintf(
				__( 'There was an error importing the views: "%s"', 'toolset-cli' ),
				$import_status instanceof WP_Error ? $import_status->get_error_message() : 'unknown error'
			), true );

			return;
		}

		$this->wp_cli()->success( sprintf(
			__( 'The views were imported successfully from "%s."', 'toolset-cli' ), $import_filename
		) );
	}

}
