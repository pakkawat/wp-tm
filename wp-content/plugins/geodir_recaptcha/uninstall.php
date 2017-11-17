<?php
/**
 * Uninstall GeoDirectory Re-Captcha
 *
 * Uninstalling GeoDirectory Re-Captcha deletes the plugin options.
 *
 * @package GeoDirectory_ReCaptcha
 * @since 1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

if (get_option('geodir_un_geodir_recaptcha')) {
    if (!defined('GEODIR_RECAPTCHA_VERSION')) {
        // Load plugin file.
        include_once('geodir_recaptcha.php');
    }
        
    // Delete options.
    $options = geodir_recaptcha_settings();
    if ( !empty( $options ) ) {
        foreach ( $options as $option) {
            if ( isset( $option['id'] ) && $option['id'] != '' ) {
                delete_option( $option['id'] );
            }
        }
    }
    
    // Delete roles captcha hide option.
    foreach ( get_editable_roles() as $role => $data ) {
        delete_option( 'geodir_recaptcha_role_' . $role );
    }
}