<?php
/*
 * Plugin Name: Popup anything on click
 * Plugin URI: https://www.wponlinesupport.com/plugins
 * Text Domain: popup-anything-on-click
 * Description: Display a modal popup by clicking on a link, image or button 
 * Domain Path: /languages/
 * Version: 1.2
 * Author: WP Online Support
 * Author URI: https://www.wponlinesupport.com
 * Contributors: WP Online Support
*/

if( !defined( 'POPUPAOC_VERSION' ) ) {
	define( 'POPUPAOC_VERSION', '1.2' ); // Version of plugin
}
if( !defined( 'POPUPAOC_DIR' ) ) {
    define( 'POPUPAOC_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'POPUPAOC_URL' ) ) {
    define( 'POPUPAOC_URL', plugin_dir_url( __FILE__ )); // Plugin url
}
if( !defined( 'POPUPAOC_PLUGIN_BASENAME' ) ) {
	define( 'POPUPAOC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // plugin base name
}
if(!defined( 'POPUPAOC_POST_TYPE' ) ) {
	define('POPUPAOC_POST_TYPE', 'aoc_popup'); // Plugin post type
}
if(!defined( 'POPUPAOC_META_PREFIX' ) ) {
	define('POPUPAOC_META_PREFIX','_aoc_'); // Plugin metabox prefix
}

/**
 * Load Text Domain
 * This gets the plugin ready for translation
 * 
 * @package Popup anything on click
 * @since 1.0.0
 */
add_action('plugins_loaded', 'popupaoc_load_textdomain');
function popupaoc_load_textdomain() {
	load_plugin_textdomain( 'popup-anything-on-click', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

// Taking some global variable
global $paoc_popup_data;

// Funcions File
require_once( POPUPAOC_DIR .'/includes/popupaoc-functions.php' );

// Post Type File
require_once( POPUPAOC_DIR . '/includes/popupaoc-post-types.php' );

// Script Class File
require_once( POPUPAOC_DIR . '/includes/class-popupaoc-script.php' );

// Admin Class File
require_once( POPUPAOC_DIR . '/includes/admin/class-popupaoc-admin.php' );

// Shortcode file
require_once( POPUPAOC_DIR . '/includes/shortcode/popupaoc-popup-shortcode.php' );

// Public File
require_once( POPUPAOC_DIR . '/includes/class-paoc-public.php' );