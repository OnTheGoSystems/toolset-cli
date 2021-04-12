<?php
/*
Plugin Name: Toolset CLI
Plugin URI: https://github.com/OnTheGoSystems/toolset-cli
Description: WP-CLI commands for Toolset plugins.
Author: OnTheGoSystems
Author URI: https://onthegosystems.com
Version: 1.1
License: GPLv2 or later
*/

require_once __DIR__ . '/vendor/autoload.php';
$bootstrap = new \OTGS\Toolset\CLI\Bootstrap();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$bootstrap->initialize();
}

$bootstrap->initialize_updater(__FILE__);
