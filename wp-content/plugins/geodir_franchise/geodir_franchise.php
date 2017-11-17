<?php
/*
Plugin Name: GeoDirectory Franchise Manager
Plugin URI: http://wpgeodirectory.com/
Description: Integrates franchise service feature with GeoDirectory listings.
Version: 1.0.7
Author: GeoDirectory
Author URI: http://wpgeodirectory.com/
License: GPLv3
Update URL: https://wpgeodirectory.com
Update ID: 65845
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
	
// Define Constants
define( 'GEODIR_FRANCHISE_VERSION', '1.0.7' );
define( 'GEODIR_FRANCHISE_TEXTDOMAIN', 'geodir-franchise' );
define( 'GEODIR_FRANCHISE_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIR_FRANCHISE_PLUGIN_URL', plugins_url('',__FILE__) );

if ( is_admin() ) {
	if ( !function_exists( 'is_plugin_active' ) ) {
		/**
		 * Include WordPress plugin core file to use core functions to check for active plugins.
		 */
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
    // check core GD is active and if not bail.
    if(!is_plugin_active('geodirectory/geodirectory.php')){
        return;
    }

	//GEODIRECTORY UPDATE CHECKS
	if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
		require_once('gd_update.php'); // require update script
	}

}

// Load plugin textdomain.
add_action( 'plugins_loaded', 'geodir_franchise_load_textdomain' );

/**
 * Include the main functions related to the plugin.
 */
require_once( GEODIR_FRANCHISE_PLUGIN_PATH . '/includes/gdfranchise_functions.php' );

/**
 * Include the hook actions used for the plugin.
 */
require_once( GEODIR_FRANCHISE_PLUGIN_PATH . '/includes/gdfranchise_hook_actions.php' );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	register_activation_hook( __FILE__ , 'geodir_franchise_activation' );
	register_deactivation_hook( __FILE__ , 'geodir_franchise_deactivation' );
}

add_action( 'activated_plugin', 'geodir_franchise_plugin_activated' );