<?php
/**
 * Uninstall GeoDirectory Social Importer
 *
 * Uninstalling GeoDirectory Social Importer deletes the plugin options.
 *
 * @package GeoDirectory_Social_Importer
 * @since 1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

if ( get_option( 'geodir_un_geodir_social_importer' ) ) {
    // Delete options.
    delete_option( 'gdfi_config' );
    delete_option( 'gdfi_post_to_facebook' );
    delete_option( 'gdfi_config_yelp' );
    delete_option( 'geodir_social_disable_post_to_fb' );
    delete_option( 'geodir_social_cpt_to_fb' );
    delete_option( 'geodir_social_disable_auto_post' );
}