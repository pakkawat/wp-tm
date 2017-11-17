<?php
/*
Plugin Name: GeoDirectory Custom Post Types
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Custom Post Types plugin.
Version: 1.3.6
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65108
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

// Define Constants
define( 'GEODIR_CP_VERSION', '1.3.6' );
define( 'GEODIR_CP_TEXTDOMAIN', 'geodir_custom_posts' );
define( 'GEODIR_CP_PLUGIN_FILE', __FILE__ );
define( 'GEODIR_CP_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIR_CP_PLUGIN_URL', plugins_url( '', __FILE__ ) );

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

$geodir_addon_list['geodir_custom_posts_manager'] = 'yes';

if (!isset($plugin_prefix))
    $plugin_prefix = $wpdb->prefix . 'geodir_';

if (!defined('WP_POST_REVISIONS'))
    define( 'WP_POST_REVISIONS', 0); // To stop post revisions for wordpress

// Load plugin textdomain.
add_action('plugins_loaded', 'geodir_load_translation_custom_posts');

require_once('geodir_cp_functions.php');
require_once('geodir_cpt_link_business.php');
require_once('geodir_cp_template_tags.php'); 
require_once('geodir_cp_hooks_actions.php');
include_once('geodir_cpt_widgets.php');

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once('gd_upgrade.php');

    // Admin init + activation hooks
    register_activation_hook(__FILE__ , 'geodir_custom_post_type_activation');
    register_deactivation_hook(__FILE__ , 'geodir_custom_post_type_deactivation');
    register_uninstall_hook(__FILE__, 'geodir_custom_post_type_uninstall');
}

add_action('activated_plugin', 'geodir_custom_post_type_plugin_activated');
