<?php
/**
 * Uninstall GeoDirectory Review Rating Manager
 *
 * Uninstalling GeoDirectory Review Rating Manager deletes data, tables and options.
 *
 * @package GeoDirectory_Review_Rating_Manager
 * @since 1.3.4
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $plugin_prefix;

if (get_option('geodir_un_geodir_review_rating_manager')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIRREVIEWRATING_VERSION')) {
        // Load plugin file.
        include_once('geodir_review_rating_manager.php');
    }
    
    if (empty($plugin_prefix)) {
        $plugin_prefix = $wpdb->prefix . 'geodir_';
    }
    
    // Update data.
    $post_types = geodir_get_posttypes();
    foreach ($post_types as $post_type) {
        $detail_table = $plugin_prefix . $post_type . '_detail';
        
        if ($wpdb->get_var("SHOW COLUMNS FROM " . $detail_table . " WHERE field = 'ratings'")) {
            $wpdb->query("ALTER TABLE " . $detail_table . " DROP ratings");
        }
    }
    
    if ($wpdb->get_var("SHOW COLUMNS FROM " . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . " WHERE field = 'read_unread'")) {
        $wpdb->query("ALTER TABLE " . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . " DROP read_unread");
    }

    // Delete options.
    $default_options = geodir_reviewrating_default_options();
    if (!empty($default_options)) {
        foreach ($default_options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    // Drop tables.
    $wpdb->query("DROP TABLE " . GEODIR_REVIEWRATING_STYLE_TABLE);
    $wpdb->query("DROP TABLE " . GEODIR_REVIEWRATING_CATEGORY_TABLE);
    $wpdb->query("DROP TABLE " . GEODIR_UNASSIGN_COMMENT_IMG_TABLE);
    $wpdb->query("DROP TABLE " . GEODIR_COMMENTS_REVIEWS_TABLE);
}