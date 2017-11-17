<?php 
/*
Plugin Name: GeoDirectory Events
Plugin URI: https://wpgeodirectory.com
Description: GeoDirectory Events plugin .
Version: 1.4.8
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65116
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

// Define Constants
define( 'GDEVENTS_VERSION', '1.4.8' );
define( 'GEODIREVENTS_TEXTDOMAIN', 'geodirevents' );
define( 'GEODIREVENTS_PLUGIN_FILE', __FILE__ );
define( 'GEODIREVENTS_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIREVENTS_PLUGIN_URL', plugins_url( '', __FILE__ ) );

/*
 * Globals
 */ 
global $wpdb, $plugin_prefix, $geodir_addon_list, $geodir_date_time_format, $geodir_date_format, $geodir_time_format;

if ( is_admin() ) {
    if (!function_exists( 'is_plugin_active')) {
        /**
         * Include WordPress plugin core file to use core functions to check for active plugins.
         */
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    //GEODIRECTORY UPDATE CHECKS
    if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
        require_once('gd_update.php'); // require update script
    }


    // check core GD is active and if not bail.
    if (!is_plugin_active('geodirectory/geodirectory.php')){
        return;
    }
    
    if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
        if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            add_action( 'admin_notices', 'geodir_event_PHP_version_notice' );
        }
    }
}

$geodir_addon_list['geodir_event_manager'] = 'yes' ;

if (!isset($plugin_prefix))
    $plugin_prefix = $wpdb->prefix . 'geodir_';

/* Table Names */
if (!defined('EVENT_DETAIL_TABLE')) define('EVENT_DETAIL_TABLE', $plugin_prefix . 'gd_event_detail' );
if (!defined('EVENT_SCHEDULE')) define('EVENT_SCHEDULE', $plugin_prefix . 'event_schedule' );

add_action('plugins_loaded', 'geodir_load_translation_geodirevents');

include_once( 'gdevents_template_functions.php' );
include_once( 'gdevents_functions.php' ); 
include_once( 'gdevents_hooks_actions.php' );
include_once( 'gdevents_widget.php' );
include_once( 'gdevents_shortcodes.php' );

// Are You Interested widget
include_once('ayi-functions.php');
include_once('ayi-widgets.php');
include_once('ayi-shortcodes.php');

$geodir_date_format = geodir_event_date_format();
$geodir_time_format = geodir_event_time_format();
$geodir_date_time_format = geodir_event_date_time_format();

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( 'gdevents-admin/admin_functions.php' );
    require_once( 'gdevents-admin/admin_hooks_actions.php' );
    require_once( 'gdevents-admin/admin_install.php' );
    
    // Admin init + activation hooks
    register_activation_hook(__FILE__ , 'geodir_events_activation');
    register_deactivation_hook(__FILE__ , 'geodir_event_deactivation');
    
    require_once( 'gd_upgrade.php' );
}

add_action('activated_plugin', 'geodir_event_plugin_activated');