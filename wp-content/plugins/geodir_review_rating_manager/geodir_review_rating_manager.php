<?php 
/*
Plugin Name: GeoDirectory Review Rating Manager
Plugin URI: http://wpgeodirectory.com
Description: This plugin gives a advanced comment system with multi rating system on post comments.
Version: 1.4.0
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65876
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

// Define Constants
define( 'GEODIRREVIEWRATING_VERSION', '1.4.0' );
define( 'GEODIRREVIEWRATING_TEXTDOMAIN', 'geodir_reviewratings' );
define( 'GEODIR_REVIEWRATING_PLUGIN_FILE', __FILE__ );
define( 'GEODIR_REVIEWRATING_PLUGINDIR_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIR_REVIEWRATING_PLUGINDIR_URL', plugins_url( '', __FILE__ ) );

global $wpdb, $plugin, $plugin_prefix, $vailed_file_type, $geodir_addon_list;
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
}

$geodir_addon_list['geodir_review_rating_manager'] = 'yes' ;

if (!isset($plugin_prefix))
    $plugin_prefix = $wpdb->prefix . 'geodir_';

$plugin = plugin_basename( __FILE__ );

$vailed_file_type = array('image/png', 'image/gif', 'image/jpg', 'image/jpeg');

/* Tables Cinstants */
if (!defined('GEODIR_REVIEWRATING_STYLE_TABLE')) define('GEODIR_REVIEWRATING_STYLE_TABLE', $plugin_prefix . 'rating_style' );
if (!defined('GEODIR_REVIEWRATING_CATEGORY_TABLE')) define('GEODIR_REVIEWRATING_CATEGORY_TABLE', $plugin_prefix.'rating_category');
if (!defined('GEODIR_REVIEWRATING_POSTREVIEW_TABLE')) define('GEODIR_REVIEWRATING_POSTREVIEW_TABLE', $plugin_prefix . 'post_review' );
if (!defined('GEODIR_UNASSIGN_COMMENT_IMG_TABLE')) define('GEODIR_UNASSIGN_COMMENT_IMG_TABLE', $plugin_prefix . 'unassign_comment_images' );
if (!defined('GEODIR_COMMENTS_REVIEWS_TABLE')) define('GEODIR_COMMENTS_REVIEWS_TABLE', $plugin_prefix . 'comments_reviews' );

add_action('plugins_loaded', 'geodir_load_translation_reviewratings');

include_once('geodir_reviewrating_template_tags.php');
include_once('geodir_reviewrating_functions.php');
include_once('geodir_reviewrating_template_functions.php');
include_once('geodir_reviewrating_actions.php'); 

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once('gd_upgrade.php');

    // Admin init + activation hooks
    register_activation_hook(__FILE__ , 'geodir_reviewrating_activation');
    register_deactivation_hook(__FILE__ , 'geodir_reviewrating_deactivation');
}

add_action('activated_plugin', 'geodir_reviewrating_plugin_activated'); 
