<?php
/**
 * Uninstall GeoDirectory Custom Post Types
 *
 * Uninstalling GeoDirectory Custom Post Types deletes data, tables and options.
 *
 * @package GeoDirectory_Custom_Post_Types
 * @since 1.3.1
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if (get_option('geodir_un_geodir_custom_posts')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIR_CP_VERSION')) {
        // Load plugin file.
        include_once('geodir_custom_posts.php');
    }
    
    $geodir_custom_post_types = get_option('geodir_custom_post_types');
    $geodir_post_types = get_option( 'geodir_post_types' );
    $geodir_taxonomies = get_option('geodir_taxonomies');
    
    if (!empty($geodir_custom_post_types)) {
        foreach ($geodir_custom_post_types as $key) {
            if (array_key_exists($key . 'category', $geodir_taxonomies)) {
                unset($geodir_taxonomies[$key . 'category']);
                update_option('geodir_taxonomies', $geodir_taxonomies);
            }
            
            if (array_key_exists($key . '_tags', $geodir_taxonomies)) {
                unset($geodir_taxonomies[$key . '_tags']);
                update_option('geodir_taxonomies', $geodir_taxonomies);
            }
            
            if (array_key_exists($key, $geodir_post_types)) {
                unset($geodir_post_types[$key]);
                update_option('geodir_post_types', $geodir_post_types);
                geodir_custom_post_type_ajax($key);
            }
            
            if (array_key_exists($key, $geodir_custom_post_types)) {
                unset($geodir_custom_post_types[$key]);
                update_option('geodir_custom_post_types', $geodir_custom_post_types);
            }
        }
    }
}