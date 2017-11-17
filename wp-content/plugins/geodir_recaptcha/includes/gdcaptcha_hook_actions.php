<?php
/**
 * Hook and filter actions used by the plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * admin hooks
 */
if ( is_admin() ) {
	add_action( 'admin_init', 'geodir_recaptcha_activation_redirect' );
	add_action( 'admin_enqueue_scripts', 'geodir_recaptcha_admin_scripts', 10 );
	add_action( 'geodir_admin_option_form', 'geodir_recaptcha_tab_content', 2 );
	add_action( 'wp_ajax_geodir_recaptcha_ajax', 'geodir_recaptcha_ajax' );
	add_action( 'wp_ajax_nopriv_geodir_recaptcha_ajax', 'geodir_recaptcha_ajax' );
	
	add_filter( 'geodir_settings_tabs_array', 'geodir_recaptcha_tabs_array', 10 );
	add_filter('geodir_plugins_uninstall_settings', 'geodir_recaptcha_uninstall_settings', 10, 1);
} else { // non admin hooks
}

add_action( 'init', 'geodir_recaptcha_init', 0 );
