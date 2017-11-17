<?php
/**
 * Uninstall GeoDirectory Ajax Duplicate Alert
 *
 * Uninstalling GeoDirectory Ajax Duplicate Alert deletes the plugin options.
 *
 * @package GeoDirectory_Ajax_Duplicate_Alert
 * @since 1.1.6
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

if ( get_option( 'geodir_un_geodir_ajax_duplicate_alert' ) ) {
    if ( !defined( 'GEODIRDUPLICATEALERT_VERSION' ) ) {
        // Load plugin file.
        include_once( 'geodir_ajax_duplicate_alert.php' );
    }
    
    $post_types = geodir_get_posttypes();

    // Delete each post type duplicate fields settings.
    foreach ( $post_types as $post_type ) {
        delete_option( 'geodir_duplicate_field_' . $post_type );
    }
    
    // Delete options.
    delete_option( 'geodir_post_types_duplicate' );
}