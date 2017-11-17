<?php
/**
 * Uninstall GeoDirectory Location Manager
 *
 * Uninstalling GeoDirectory Location Manager deletes data, tables and options.
 *
 * @package GeoDirectory_Location_Manager
 * @since 1.5.1
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $plugin_prefix;

if (get_option('geodir_un_geodir_location_manager')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIRLOCATION_VERSION')) {
        // Load plugin file.
        include_once('geodir_location_manager.php');
    }
    
    if (empty($plugin_prefix)) {
        $plugin_prefix = $wpdb->prefix . 'geodir_';
    }
    
    geodir_unset_location();
    $default_location = geodir_get_default_location();
    
    $post_types = geodir_get_posttypes();
    // Delete posts.
    if (!empty($post_types)) {
        foreach ($post_types as $post_type) {
            $table = $plugin_prefix . $post_type . '_detail';

            $del_post_sql = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT post_id from " . $table . " where post_location_id != %d",
                    array($default_location->location_id)
                )
            );

            if (!empty($del_post_sql)) {
                foreach ($del_post_sql as $del_post_info) {
                    $postid = $del_post_info->post_id;
                    wp_delete_post($postid);
                }
            }

            $wpdb->query("UPDATE " . $table . " SET post_location_id='0'");

            if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = 'post_neighbourhood'")) {
                $wpdb->query("ALTER TABLE " . $table . " DROP post_neighbourhood");
            }
        }
    }
    
    // Delete default options.
    $location_option = geodir_location_default_options();
    if (!empty($location_option)) {
        foreach ($location_option as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    // Drop tables.
    $wpdb->query("DROP TABLE " . POST_LOCATION_TABLE);
    $wpdb->query("DROP TABLE " . POST_NEIGHBOURHOOD_TABLE);
    $wpdb->query("DROP TABLE " . LOCATION_SEO_TABLE);
    $wpdb->query("DROP TABLE " . GEODIR_TERM_META);
    
    $default_location->location_id = 0;
    update_option('geodir_default_location', $default_location);
}