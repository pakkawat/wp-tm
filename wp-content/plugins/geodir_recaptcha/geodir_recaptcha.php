<?php
/**
 * This is the main GeoDirectory Re-Captcha plugin file, here we declare and call the important stuff.
 *
 * @global array $geodir_addon_list GeoDirectory addon list array.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */

/*
Plugin Name: GeoDirectory Re-Captcha
Plugin URI: http://wpgeodirectory.com/
Description: Integrates Google reCAPTCHA anti-spam methods with wordpress GeoDirectory addon including registration, comments, send to friend, send enquiry and claim listing forms, modded to be 100% GeoDirectory compatible.
Version: 1.1.6
Author: GeoDirectory
Author URI: https://wpgeodirectory.com/
License: GPLv3
Update URL: https://wpgeodirectory.com
Update ID: 65872
*/

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
	
// Define Constants
define( 'GEODIR_RECAPTCHA_VERSION', '1.1.6' );
define( 'GEODIR_RECAPTCHA_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'GEODIR_RECAPTCHA_PLUGIN_URL', plugins_url('',__FILE__) );
define( 'GDCAPTCHA_TEXTDOMAIN', 'geodir-recaptcha' );

global $geodir_addon_list;

$geodir_addon_list['geodir_recaptcha'] = 'yes' ;

if ( is_admin() ) {
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

add_action('plugins_loaded','geodir_load_translation_gdcaptcha');
function geodir_load_translation_gdcaptcha()
{
    /**
     * Filters the plugin locale.
     *
     * @since 1.0.0
     * @package GeoDirectory_ReCaptcha
     */
    $locale = apply_filters('plugin_locale', get_locale(), 'geodir-recaptcha');
    load_textdomain('geodir-recaptcha', WP_LANG_DIR . '/' . 'geodir-recaptcha' . '/' . 'geodir-recaptcha' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodir-recaptcha', false, dirname(plugin_basename(__FILE__)) . '/gdcaptcha-languages');
}

/**
 * Include core files
 */
// Contains functions related to GD captcha plugin.
require_once( 'includes/gdcaptcha_functions.php' );
// Contains functions related to GD captcha templates.
require_once( 'includes/gdcaptcha_template_functions.php' );
// Contains hook and filter actions used by the plugin.
require_once( 'includes/gdcaptcha_hook_actions.php' );

/**
 * Admin init + activation hooks
 */
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	register_activation_hook( __FILE__ , 'geodir_recaptcha_activation' );
	register_deactivation_hook( __FILE__ , 'geodir_recaptcha_deactivation' );
}

add_action( 'activated_plugin', 'geodir_recaptcha_plugin_activated' ) ;