<?php
/*
Plugin Name: GeoDirectory Claim Manager
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Claim Manager plugin.
Version: 1.3.22
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65098
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

// Define Constants
define( 'GEODIRCLAIM_VERSION', '1.3.22' );
define( 'GEODIRCLAIM_TEXTDOMAIN', 'geodirclaim' );
define( 'GEODIRCLAIM_PLUGIN_FILE', __FILE__ );
define( 'GEODIRCLAIM_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIRCLAIM_PLUGIN_URL', plugins_url( '', __FILE__ ) );

global $wpdb, $plugin_prefix, $site_login_url, $geodir_addon_list;
if ( is_admin() ) {
    if (!function_exists( 'is_plugin_active')) {
        /**
         * Include WordPress plugin core file to use core functions to check for active plugins.
         */
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    // check core GD is active and if not bail.
    if (!is_plugin_active('geodirectory/geodirectory.php')){
        return;
    }

    //GEODIRECTORY UPDATE CHECKS
    if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
        require_once('gd_update.php'); // require update script
    }

}

$geodir_addon_list['geodir_claim_manager'] = 'yes' ;

if (!isset($plugin_prefix))
    $plugin_prefix = $wpdb->prefix . 'geodir_';

$path_url = plugins_url('', __FILE__);

if (!defined('GEODIR_CLAIM_TABLE')) 
    define('GEODIR_CLAIM_TABLE', $plugin_prefix . 'claim');

// Load plugin textdomain.
add_action('plugins_loaded', 'geodir_load_translation_geodirclaim');

include_once('geodir_claim_hooks_actions.php');
include_once('geodir_claim_template_tags.php');
include_once('geodir_claim_template_functions.php');
include_once('geodir_claim_functions.php');

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once('gd_upgrade.php');

    // Admin init + activation hooks
    register_activation_hook(__FILE__ , 'geodir_claim_listing_activation');
    register_deactivation_hook(__FILE__ , 'geodir_claim_listing_deactivation');
}

add_action('activated_plugin', 'geodir_claim_listing_plugin_activated');
