<?php
/*
Plugin Name: Toolset CLI
Description: WP-CLI commands for Toolset plugins
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com
Version: 1.0.3
License: GPLv2 or later
*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/vendor/autoload.php';

	$bootstrap = new \OTGS\Toolset\CLI\Bootstrap();
	$bootstrap->initialize();
}
