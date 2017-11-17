<?php
/**
 * Uninstall GeoDirectory Claim Manager
 *
 * Uninstalling GeoDirectory Claim Manager deletes tables and options.
 *
 * @package GeoDirectory_Claim_Manager
 * @since 1.2.9
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $plugin_prefix;

if (get_option('geodir_un_geodir_claim_listing')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIRCLAIM_VERSION')) {
        // Load plugin file.
        include_once('geodir_claim_listing.php');
    }
    
    if (empty($plugin_prefix)) {
        $plugin_prefix = $wpdb->prefix . 'geodir_';
    }

    // Update listing tables.
    $post_types = geodir_get_posttypes();
    if (!empty($post_types)) {
        foreach($post_types as $post_type) {
            $table = $plugin_prefix . $post_type . '_detail';
            
            $wpdb->query("UPDATE " . $table . " SET claimed = ''");
        }
    }
    
    // Delete default options.
    $default_options = geodir_claim_default_options();
    if (!empty($default_options)) {
        foreach ($default_options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }

    // Delete notification options.
    $notifications = geodir_claim_notifications();
    if (!empty($notifications)) {
        foreach ($notifications as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    // Drop tables.
    $wpdb->query("DROP TABLE " . GEODIR_CLAIM_TABLE);
}