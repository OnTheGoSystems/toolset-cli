<?php

namespace OTGS\Toolset\CLI\Views;

use ZipArchive;

class Export extends Views_Commands {


	const EXPORT_FORMAT_ZIP = 'zip';

	const EXPORT_FORMAT_XML = 'xml';


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
	public function __invoke( $args, $assoc_args ) {
		list( $export_filename ) = $args;

		if ( empty ( $export_filename ) ) {
			$this->wp_cli()->warning( __( 'You must specify a valid file.', 'toolset-cli' ) );

			return;
		}

		// The default exported format is XML.
		$export_file_format = self::EXPORT_FORMAT_XML;

		// Was the --format option specified on the command line?
		if ( array_key_exists( 'format', $assoc_args ) ) {

			switch ( strtolower( $assoc_args['format'] ) ) {
				case self::EXPORT_FORMAT_ZIP:
					$export_file_format = self::EXPORT_FORMAT_ZIP;
					break;
				case self::EXPORT_FORMAT_XML:
					// Do nothing, this is the default format.
					break;
				default:
					// For any other format, quit.
					$this->wp_cli()
						->error( sprintf( __( '"%s" is not a valid export format. Aborting.', 'toolset-cli' ), strtolower( $assoc_args['format'] ) ) );

			}
		}

		// Returns filename extension without a period prefixed to it.
		$export_filename_extension = pathinfo( $export_filename, PATHINFO_EXTENSION );

		// Does the specified filename have an extension? If not, add it and notify the user.
		if ( ! $export_filename_extension || strtolower( $export_filename_extension ) !== $export_file_format ) {

			// Returns filename without the path to the parent directory.
			$export_filename_basename = pathinfo( $export_filename, PATHINFO_BASENAME );

			$this->wp_cli()
				->warning( __( sprintf( '"%1$s" lacks a "%2$s" extension. Adding it. The new filename will be "%1$s.%2$s."', $export_filename_basename, $export_file_format ), 'toolset-cli' ) );

			// Append appropriate extension to export filename.
			$export_filename .= sprintf( '.%s', $export_file_format );
		}

		// Warn if the file already exists.
		if ( ! array_key_exists( 'overwrite', $assoc_args ) && is_file( $export_filename ) ) {
			$this->wp_cli()
				->error( sprintf( __( '"%s" already exists. Aborting.', 'toolset-cli' ), $export_filename ) );
		}

		require_once WPV_PATH . '/inc/wpv-import-export.php';

		$exported_data = wpv_admin_export_data( false );

		if ( $export_file_format === self::EXPORT_FORMAT_ZIP ) {
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
