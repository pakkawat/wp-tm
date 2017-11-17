<?php
/**
 * Uninstall GeoDirectory Custom Google Maps
 *
 * Uninstalling GeoDirectory Custom Google Maps deletes options.
 *
 * @package GeoDirectory_Custom_Google_Maps
 * @since 1.0.8
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if (get_option('geodir_un_geodir_custom_google_maps')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIR_CUSTOMGMAPS_VERSION')) {
        // Load plugin file.
        include_once('geodir_custom_google_maps.php');
    }
    
    // Delete options.
    delete_option('geodir_custom_gmaps_style_home');
    delete_option('geodir_custom_gmaps_style_listing');
    delete_option('geodir_custom_gmaps_style_detail');
    delete_option('geodir_custom_gmaps_osm_style');
    
    $options = geodir_custom_gmaps_general_options();
    if (!empty($options)) {
        foreach ($options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
}