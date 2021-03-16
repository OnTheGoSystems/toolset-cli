<?php

namespace OTGS\Toolset\CLI\Views;

use ZipArchive;

class Export extends Views_Commands {


	const EXPORT_FORMAT_ZIP = 'zip';

	const EXPORT_FORMAT_XML = 'xml';


	const SUPPORTED_EXPORT_FORMATS = [
		self::EXPORT_FORMAT_XML,
		self::EXPORT_FORMAT_ZIP,
	];


	/**
	 * Export Views to an XML or ZIP file.
	 *
	 * ## Options
	 *
	 * <file>
	 * : The filename of the exported file.
	 *
	 * [--format=<zip|xml>]
	 * : The format can be either "zip" or "xml". If omitted, the default is xml.
	 *
	 * [--overwrite]
	 * : Allow for overwriting an existing file.
	 *
	 * ## Examples
	 *
	 *     wp views export file
	 *     wp views export --overwrite --format=zip file
	 *
	 * @synopsis [--overwrite] [--format=<zip|xml>] <file>
	 *
	 * @param array $args The array of command-line arguments.
	 * @param array $assoc_args The associative array of command-line options.
	 */
	public function __invoke( array $args, array $assoc_args ) {
		list( $export_filename ) = $args;

		if ( empty ( $export_filename ) ) {
			$this->wp_cli()->warning( __( 'You must specify a valid file.', 'toolset-cli' ) );

			return;
		}

		// The default exported format is XML.

		// Returns filename extension without a period prefixed to it.
		$export_filename_extension = strtolower( pathinfo( $export_filename, PATHINFO_EXTENSION ) );

		$format = array_key_exists( 'format', $assoc_args )
			? strtolower( $assoc_args['format'] )
			: $export_filename_extension;

		if ( $format !== $export_filename_extension ) {
			$this->wp_cli()->error(
				__( 'The --format argument doesn\'t match the file extension.', 'toolset-cli' ),
				true
			);
			return;
		}

		if ( ! in_array( $format, self::SUPPORTED_EXPORT_FORMATS, true ) ) {
			$this->wp_cli()->error( sprintf(
				__( '"%s" is not a valid export format. Aborting.', 'toolset-cli' ),
				$format
			), true );
			return;
		}

		// Warn if the file already exists.
		if ( ! array_key_exists( 'overwrite', $assoc_args ) && is_file( $export_filename ) ) {
			$this->wp_cli()->error( sprintf(
				__( '"%s" already exists. Aborting.', 'toolset-cli' ), $export_filename
			), true );
			return;
		}

		require_once WPV_PATH . '/inc/wpv-import-export.php';

		$exported_data = wpv_admin_export_data( false );

		if ( $format === self::EXPORT_FORMAT_ZIP ) {
			// Create Zip archive.
			$export_file_zip_data = new ZipArchive;
			$result = $export_file_zip_data->open( $export_filename, ZipArchive::CREATE );
			if ( $result === true ) {

				$export_file_zip_data->addFromString( 'settings.xml', $exported_data );
				// A file named "settings.php" is exported via the GUI. It just contains a timestamp.
				$settings = sprintf( '<?php $timestamp = %s; ?>', time() );
				$export_file_zip_data->addFromString( 'settings.php', $settings );
				if ( ! $export_file_zip_data->close() ) {
					$this->wp_cli()->error( __( 'There was an error saving the views to a ZIP file.', 'toolset-cli' ) );
				}
			} else {
				$this->wp_cli()->error( __( 'There was an error exporting the views to a ZIP file.', 'toolset-cli' ) );
			}

		} else {
			// We're only writing an XML file.
			$written_bytes = file_put_contents( $export_filename, $exported_data );

			if ( ! $written_bytes ) {
				$this->wp_cli()->error( __( 'There was an error exporting the views to an XML file.', 'toolset-cli' ) );

				return;
			}
		}

		$this->wp_cli()
			->success( sprintf( __( 'The views were exported successfully to "%s".', 'toolset-cli' ), $export_filename ) );
	}

}
