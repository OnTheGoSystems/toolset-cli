<?php

namespace OTGS\Toolset\CLI\Thirdparty\CSV;

use OTGS\Toolset\CLI\Commands\ToolsetCommand;

/**
 * CSV Import commands.
 */
class Import extends ToolsetCommand {

	/**
	 * @var string
	 */
	private $delimiter = ';';

	/**
	 * @var string
	 */
	private $header_delimiter = '#';

	/**
	 * @var array
	 */
	private $supported_types = array(
		'post',
		'meta',
		'taxonomy',
	);

	/**
	 * Imports a CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : The CSV file to import.
	 *
	 * [--post_type=<post_type>]
	 * : The post type of the imported posts. Default: post.
	 *
	 * ## EXAMPLES
	 *
	 *    wp csv import file.csv --post_type=book
	 *
	 * @subcommand import
	 * @synopsis <file> [--post_type=<post_type>]
	 *
	 * @since 1.0
	 */
	public function import( $args, $assoc_args ) {
		list( $file_name ) = $args;

		if ( empty ( $file_name ) || ! file_exists( $file_name ) ) {
			\WP_CLI::warning( __( 'You must specify a valid file.', 'toolset-cli' ) );

			return;
		}

		$defaults = array(
			'post_type' => 'post',
		);
		$assoc_args = wp_parse_args( $assoc_args, $defaults );

		if ( ! post_type_exists( $assoc_args['post_type'] ) ) {
			\WP_CLI::error( sprintf( __( '%s post type does not exist.', 'toolset-cli' ), $assoc_args['post_type'] ) );
		}

		$file_handle = $this->read_file( $file_name );
		$headers_array = $this->parse_headers( $file_handle );
		$import_data = $this->parse_data( $file_handle, $headers_array );
		$data_is_stored = $this->store_data( $headers_array, $import_data, $assoc_args['post_type'] );

		if ( $data_is_stored ) {
			\WP_CLI::success( __( 'The file was imported successfully.', 'toolset-cli' ) );
		} else {
			\WP_CLI::error( __( 'An error occured while importing the file.', 'toolset-cli' ) );
		}
	}

	/**
	 * Reads the file content.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return resource The file pointer resource.
	 */
	private function read_file( $file_name ) {
		$file_handle = null;

		ini_set( "auto_detect_line_endings", "1" );

		if ( ! file_exists( $file_name ) ) {
			\WP_CLI::error( sprintf( __( '%s file does not exist.', 'toolset-cli' ), $this->file ) );
		}

		if ( ! $file_handle = fopen( $file_name, 'r' ) ) {
			\WP_CLI::error( sprintf( __( '%s file cannot be accessed, please check the permissions.', 'toolset-cli' ), $this->file ) );
		}

		return $file_handle;
	}

	/**
	 * Parses the header line of the file
	 *
	 * @param resource $file_handle The file pointer resource.
	 *
	 * @return array The headers array.
	 *
	 * @example
	 *
	 * The file header should follow this pattern:
	 * supported_type#type_value;supported_type#type_value;supported_type#type_value;...
	 *
	 * so for example a header line like:
	 *        post#id;post#title;meta#_toolset_associations_writer-book
	 *
	 * would instruct to store the following data as:
	 *        - post: id
	 *        - post: title
	 *        - meta: _toolset_associations_writer-book
	 *
	 * The returned array of this example should be:
	 *
	 * Array
	 *    (
	 *        [0] => Array
	 *        (
	 *            [type] => post
	 *            [value] => id
	 *        )
	 *        [1] => Array
	 *        (
	 *            [type] => post
	 *            [value] => title
	 *        )
	 *        [2] => Array
	 *        (
	 *            [type] => meta
	 *            [value] => _toolset_associations_writer-book
	 *        )
	 *    )
	 *
	 *
	 */
	private function parse_headers( $file_handle ) {
		$headers_array = array();

		if ( ! $header_line = fgetcsv( $file_handle ) ) {
			\WP_CLI::error( __( 'The file has invalid format.', 'toolset-cli' ) );
		}

		if ( ! is_array( $header_line ) || empty( $header_line ) || ! isset( $header_line[0] ) ) {
			\WP_CLI::error( __( 'The file has invalid format.', 'toolset-cli' ) );
		}

		$headers = explode( $this->delimiter, $header_line[0] );

		foreach ( $headers as $header ) {
			$header_parts = explode( $this->header_delimiter, $header );

			if ( ! isset( $header_parts[0] ) || ! in_array( $header_parts[0], $this->supported_types ) ) {
				\WP_CLI::error( sprintf( __( '%s is not supported in file header.', 'toolset-cli' ), $header_parts[0] ) );
			}

			if ( $header_parts[0] == 'taxonomy' && ! taxonomy_exists( $header_parts[1] ) ) {
				\WP_CLI::error( sprintf( __( '%s is not a registered taxonomy.', 'toolset-cli' ), $header_parts[1] ) );
			}

			$headers_array[] = array(
				'type' => $header_parts[0],
				'value' => $header_parts[1],
			);
		}

		return $headers_array;
	}

	/**
	 * Parses the file data to be imported.
	 *
	 * @param resource $file_handle The file pointer resource.
	 * @param array $headers_array The headers array.
	 *
	 * @return array The file data to import.
	 */
	private function parse_data( $file_handle, $headers_array ) {
		$import_data = array();
		$rows_counter = 0;

		while ( ( $row = fgetcsv( $file_handle ) ) !== false ) {
			$data = explode( $this->delimiter, $row[0] );
			foreach ( $headers_array as $header_key => $header_value ) {
				$import_data[ $rows_counter ][ $header_key ] = $data[ $header_key ];
			}
			$rows_counter ++;
		}

		fclose( $file_handle );

		if ( $rows_counter == 0 ) {
			\WP_CLI::error( sprintf( 'File has no entries.', 'toolset-cli' ) );
		}

		return $import_data;
	}

	/**
	 * Stores tha data in the DB.
	 *
	 * @param array $headers_array The headers array.
	 * @param array $import_data The file data to import.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 */
	private function store_data( $headers_array, $import_data, $post_type ) {
		foreach ( $import_data as $entry ) {

			foreach ( $entry as $entry_key => $entry_value ) {
				if ( $headers_array[ $entry_key ]['type'] == 'post' && $headers_array[ $entry_key ]['value'] == 'title' ) {

					if ( ! $post_id = wp_insert_post( array(
						'post_title' => $entry_value,
						'post_type' => $post_type,
						'post_status' => 'publish',
					) ) ) {
						\WP_CLI::warning( sprintf( __( 'Could not create post %s.', 'toolset-cli' ), $entry_value ) );
						continue;
					}
				}

				if ( isset( $post_id ) && $headers_array[ $entry_key ]['type'] == 'meta' ) {
					update_post_meta( $post_id, $headers_array[ $entry_key ]['value'], $entry_value );
				}

				if ( isset( $post_id ) && $headers_array[ $entry_key ]['type'] == 'taxonomy' ) {
					wp_set_object_terms( $post_id, $entry_value, $headers_array[ $entry_key ]['value'] );
				}
			}
		}

		return true;
	}
}
