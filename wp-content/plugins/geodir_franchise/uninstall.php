<?php
/**
 * Uninstall GeoDirectory Franchise Manager
 *
 * Uninstalling GeoDirectory Franchise Manager deletes options.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.2
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $plugin_prefix;

if (get_option('geodir_un_geodir_franchise')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIR_FRANCHISE_VERSION')) {
        // Load plugin file.
        include_once('geodir_franchise.php');
    }
    
    if (empty($plugin_prefix)) {
        $plugin_prefix = $wpdb->prefix . 'geodir_';
    }
    
    // Update listing tables.
    $post_types = geodir_get_posttypes();
    if (!empty($post_types)) {
        foreach($post_types as $post_type) {
            $table = $plugin_prefix . $post_type . '_detail';
            
            if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = 'franchise'")) {
                $wpdb->query("ALTER TABLE " . $table . " DROP franchise");
            }
        }
    }
    
    // Update price tables.
    if (defined('GEODIR_PRICE_TABLE')) {
        if ($wpdb->get_var("SHOW COLUMNS FROM " . GEODIR_PRICE_TABLE . " WHERE field = 'enable_franchise'")) {
            $wpdb->query("ALTER TABLE " . GEODIR_PRICE_TABLE . " DROP enable_franchise");
            $wpdb->query("ALTER TABLE " . GEODIR_PRICE_TABLE . " DROP franchise_cost");
            $wpdb->query("ALTER TABLE " . GEODIR_PRICE_TABLE . " DROP franchise_limit");
        }
    }
    
    // Delete general options.    
    $options = geodir_franchise_general_settings();
    if (!empty($options)) {
        foreach ($options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    // Delete notification settings.    
    $options = geodir_franchise_notifications();
    if (!empty($options)) {
        foreach ($options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
}