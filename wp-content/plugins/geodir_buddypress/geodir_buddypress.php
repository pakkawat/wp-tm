<?php
/**
 * This is the main GeoDirectory BuddyPress Integration plugin file, here we declare and call the important stuff.
 *
 * @global array $geodir_addon_list GeoDirectory addon list array.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 */

/*
Plugin Name: GeoDirectory BuddyPress Integration
Plugin URI: http://wpgeodirectory.com/
Description: Integrates GeoDirectory listing activity with the BuddyPress.
Version: 1.2.1
Author: GeoDirectory
Author URI: https://wpgeodirectory.com/
Update URL: https://wpgeodirectory.com
Update ID: 65093
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
	
// Define Constants
define( 'GEODIR_BUDDYPRESS_VERSION', '1.2.1' );
define( 'GEODIR_BUDDYPRESS_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIR_BUDDYPRESS_PLUGIN_URL', plugins_url('',__FILE__) );
define( 'GDBUDDYPRESS_TEXTDOMAIN', 'gdbuddypress' );

global $geodir_addon_list;

$geodir_addon_list['geodir_buddypress'] = 'yes' ;

if ( is_admin() ) {
	// Check if BuddyPress is active, if not bail.
	if ( !class_exists( 'BuddyPress' ) ){
		return;
	}

	// GEODIRECTORY CORE ALIVE CHECK START
	if ( !function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	if ( !is_plugin_active( 'geodirectory/geodirectory.php' ) ) {
		return;
	}
	// GEODIRECTORY CORE ALIVE CHECK END

	//GEODIRECTORY UPDATE CHECKS
	if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
		require_once('gd_update.php'); // require update script
	}

}


/**
 * Localisation
 */
add_action('plugins_loaded','geodir_load_translation_gdbuddypress');
function geodir_load_translation_gdbuddypress()
{
    $locale = apply_filters('plugin_locale', get_locale(), 'gdbuddypress');
    load_textdomain('gdbuddypress', WP_LANG_DIR . '/' . 'gdbuddypress' . '/' . 'gdbuddypress' . '-' . $locale . '.mo');
    load_plugin_textdomain('gdbuddypress', false, dirname(plugin_basename(__FILE__)) . '/gdbuddypress-languages');
}

/**
 * Include core files
 */
if (class_exists('BuddyPress')) {
	require_once( 'includes/gdbuddypress_functions.php' );
	require_once( 'includes/gdbuddypress_template_functions.php' );
	require_once( 'includes/gdbuddypress_hook_actions.php' );
}
add_action('plugins_loaded','geodir_load_ayi_buddypress');
function geodir_load_ayi_buddypress() {
	if (defined('GDEVENTS_VERSION')) {
		require_once( 'includes/ayi-buddypress.php' );
	}
}

/**
 * Admin init + activation hooks
 */
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	register_activation_hook( __FILE__ , 'geodir_buddypress_activation' );
	register_deactivation_hook( __FILE__ , 'geodir_buddypress_deactivation' );
}

add_action( 'activated_plugin', 'geodir_buddypress_plugin_activated' ) ;