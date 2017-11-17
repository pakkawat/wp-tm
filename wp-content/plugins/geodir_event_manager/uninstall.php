<?php
/**
 * Uninstall GeoDirectory Events
 *
 * Uninstalling GeoDirectory Events deletes tables and options.
 *
 * @package GeoDirectory_Events
 * @since 1.4.2
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if (get_option('geodir_un_geodir_event_manager')) {    
    $wpdb->hide_errors();
    
    if (!defined('GDEVENTS_VERSION')) {
        // Load plugin file.
        include_once('geodir_event_manager.php');
    }

    // Delete options.
    delete_option('gdevents_installed');
    
    $default_options = geodir_event_general_setting_options();
    if (!empty($default_options)){
        foreach ($default_options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    $posttype = 'gd_event';
    geodir_event_inactive_posttype();
    
    $args = array( 'post_type' => $posttype, 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
    $geodir_all_posts = get_posts( $args );
    
    // Dalete posts.
    if (!empty($geodir_all_posts)) {
        foreach ($geodir_all_posts as $posts) {
            wp_delete_post($posts->ID);
        }
    }
    
    do_action('geodir_after_post_type_deleted', $posttype);
    
    // Dalete field data.
    if (defined('GEODIR_CUSTOM_FIELDS_TABLE')) {
        $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type=%s", array($posttype)));
    }
    if (defined('GEODIR_CUSTOM_SORT_FIELDS_TABLE')) {
        $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type=%s", array($posttype)));
    }
    if (defined('GEODIR_ADVANCE_SEARCH_TABLE')) {
        $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type=%s", array($posttype)));
    }
    
    // Drop tables.
    $wpdb->query("DROP TABLE " . EVENT_DETAIL_TABLE);
    $wpdb->query("DROP TABLE " . EVENT_SCHEDULE);
}