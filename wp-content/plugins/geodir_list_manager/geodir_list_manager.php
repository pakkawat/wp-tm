<?php
/*
Plugin Name: GeoDirectory Lists
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Lists manager.
Version: 1.0.1
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 69994
*/

define("GEODIRLISTS_VERSION", "1.0.1");
if (!defined('GEODIRLISTS_TEXTDOMAIN')) define('GEODIRLISTS_TEXTDOMAIN', 'geodirlists');

define( 'GEODIR_LISTS_PLUGIN_URL', plugins_url('',__FILE__) );

global $wpdb, $plugin_prefix, $is_custom_loop,$geodir_addon_list;

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
    if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
        require_once('gd_update.php'); // require update script
    }
}

///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if(!is_plugin_active('geodirectory/geodirectory.php')){
        return;
    }
}
/// GEODIRECTORY CORE ALIVE CHECK END

$geodir_addon_list['geodir_list_manager'] = 'yes' ;

if(!isset($plugin_prefix))
    $plugin_prefix = $wpdb->prefix.'geodir_';

function geodir_list_output_buffer() {
    if(isset($_POST['add_list_submit'])) {
        ob_start();
    }
}
add_action('init', 'geodir_list_output_buffer');

function geodir_enqueue_list_scripts() {
    if (class_exists('BuddyPress')) {
        if (is_buddypress()) {
            $is_bp_page = true;
        } else {
            $is_bp_page = false;
        }
    } else {
        $is_bp_page = false;
    }
    if( get_post_type() == 'gd_list' || $is_bp_page) {
        if (is_single()) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script( 'jquery-ui-touch-punch', GEODIR_LISTS_PLUGIN_URL . '/assets/js/jquery.ui.touch-punch.min.js', array('jquery-ui-sortable') );
        }
        wp_register_style( 'gd-list-css', GEODIR_LISTS_PLUGIN_URL . '/assets/css/style.css' );
        wp_enqueue_style( 'gd-list-css' );
    }
}
add_action( 'wp_enqueue_scripts', 'geodir_enqueue_list_scripts', 99 );


//Functions
include_once('includes/gdlist-functions.php');
include_once('includes/template-functions.php');
//BuddyPress
if (class_exists('BuddyPress')) {
    include_once('includes/buddypress-functions.php');
}
//widgets
include_once('widgets/fresh-lists.php');


// include upgrade script
if ( is_admin() ){
    require_once('gd_upgrade.php');
}


function gdlists_init() {
    // defines the post type so the rules can be flushed.
    gd_list_post_type();
    gd_list_create_pages();
    // and flush the rules.
    flush_rewrite_rules();
}

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    register_activation_hook( __FILE__, 'gdlists_init' );
    register_deactivation_hook( __FILE__, 'gd_list_deactivate' );
    add_filter( 'geodir_plugins_uninstall_settings', 'gd_list_uninstall_settings', 10, 1 );
}

if ( ! empty ( $GLOBALS['pagenow'] ) && 'plugins.php' === $GLOBALS['pagenow'] ) {
    add_action( 'admin_notices', 'gd_list_check_admin_notices', 0 );
}

/**
 * Plugin deactivation hook.
 *
 * @since 0.0.5
 */
function gd_list_deactivate() {
    // Plugin deactivation stuff.
}

/**
 * List manager requirements check
 */
function gd_list_check_plugin_requirements()
{
    $errors = array ();

    if ( !is_plugin_active('posts-to-posts/posts-to-posts.php') ) {
        $errors[] =  __( 'Addon requires <a href="https://wordpress.org/plugins/posts-to-posts/" target="_blank">Posts 2 Posts</a> plugin.', 'geodirlists' );
    }
    return $errors;

}

/**
 * List manager admin notices
 */
function gd_list_check_admin_notices()
{
    $errors = gd_list_check_plugin_requirements();

    if ( empty ( $errors ) )
        return;

    // Suppress "Plugin activated" notice.
    unset( $_GET['activate'] );

    // This plugin's name
    $name = get_file_data( __FILE__, array ( 'Plugin Name' ) );

    $message = __( '<i>'.$name[0].'</i> has been deactivated.', 'geodirlists' );

    printf(
        '<div class="error"><p>%1$s</p>
        <p>%2$s</p></div>',
        join( '</p><p>', $errors ),
        $message
    );

    deactivate_plugins( plugin_basename( __FILE__ ) );
}

/**
 * Localisation
 **/
add_action('plugins_loaded','geodir_load_translation_list_manager');
function geodir_load_translation_list_manager()
{
    $locale = apply_filters('plugin_locale', get_locale(), 'geodirlists');
    load_textdomain('geodirlists', WP_LANG_DIR . '/' . 'geodirlists' . '/' . 'geodirlists' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodirlists', false, dirname(plugin_basename(__FILE__)) . '/languages');
    require_once( 'language.php' ); /* Define language constants */
}