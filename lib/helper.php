<?php

namespace OTGS\Toolset\CLI;

/**
 * Generates a random string.
 *
 * @param int $length The length of the string.
 *
 * @return string The generated string.
 */
function get_random_string( $length = 10 ) {
	$characters = 'abcdefghijklmnopqrstuvwxyz';
	$characters_length = strlen( $characters );
	$random_string = '';
	for ( $i = 0; $i < $length; $i ++ ) {
		$random_string .= $characters[ rand( 0, $characters_length - 1 ) ];
	}

	return $random_string;
}

/**
 * Gets the domain (post, user, term) from the post type.
 *
 * @param string $post_type The post type.
 *
 * @return string The domain name.
 */
function get_domain_from_post_type( $post_type ) {
	$domains = array(
		'wp-types-group' => __( 'Posts', 'toolset-cli' ),
		'wp-types-user-group' => __( 'Users', 'toolset-cli' ),
		'wp-types-term-group' => __( 'Terms', 'toolset-cli' ),
	);

	return $domains[ $post_type ];
}
