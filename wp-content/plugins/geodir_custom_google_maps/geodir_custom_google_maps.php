<?php
/*
Plugin Name: GeoDirectory Custom Google Maps
Plugin URI: http://wpgeodirectory.com
Description: This plugin gives an advanced style system for Google Maps.
Version: 1.1.0
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65102
*/

/* Define Constants */
define('GEODIR_CUSTOMGMAPS_VERSION', '1.1.0');
if (!defined('GEODIRCUSTOMGMAPS_TEXTDOMAIN'))define('GEODIRCUSTOMGMAPS_TEXTDOMAIN', 'geodir_customgmaps');

define('GEODIR_CUSTOMGMAPS_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)));
define('GEODIR_CUSTOMGMAPS_PLUGIN_URL', plugins_url('',__FILE__));

global $wpdb, $plugin_prefix, $geodir_addon_list;

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
	if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
		require_once('gd_update.php'); // require update script
	}
}

// GEODIRECTORY CORE ALIVE CHECK START
if (is_admin()) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if (!is_plugin_active('geodirectory/geodirectory.php')) {
		return;
	}
}
// GEODIRECTORY CORE ALIVE CHECK END

$geodir_addon_list['geodir_custom_google_maps_manager'] = 'yes' ;

if (!isset($plugin_prefix)) {
	$plugin_prefix = $wpdb->prefix.'geodir_';
}

add_action('plugins_loaded','geodir_load_translation_customgmaps');
function geodir_load_translation_customgmaps()
{
    $locale = apply_filters('plugin_locale', get_locale(), 'geodir_customgmaps');
    load_textdomain('geodir_customgmaps', WP_LANG_DIR . '/' . 'geodir_customgmaps' . '/' . 'geodir_customgmaps' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodir_customgmaps', false, dirname(plugin_basename(__FILE__)) . '/geodir-customgmaps-languages');

    include_once('language.php'); // Define language constants
}

/**
 * Include core files
 **/
require_once('geodir_custom_gmaps_functions.php'); 
require_once('geodir_custom_gmaps_template_functions.php'); 
require_once('geodir_custom_gmaps_hooks_actions.php');

/**
 * Admin init + activation hooks
 **/
if (is_admin() || ( defined( 'WP_CLI' ) && WP_CLI )) {
	register_activation_hook(__FILE__ , 'geodir_custom_gmaps_activation');
	register_deactivation_hook(__FILE__ , 'geodir_custom_gmaps_deactivation');
}

add_action('activated_plugin','geodir_custom_google_maps_plugin_activated') ;