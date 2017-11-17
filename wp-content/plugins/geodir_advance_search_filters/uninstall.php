<?php
/**
 * Uninstall Advance Search Filters
 *
 * Uninstalling Advance Search Filters deletes tables and options.
 *
 * @package GeoDirectory_Advance_Search_Filters
 * @since 1.4.4
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if (get_option('geodir_un_geodir_advance_search_filters')) {
    $wpdb->hide_errors();
    
    if (!defined('GEODIRADVANCESEARCH_VERSION')) {
        // Load plugin file.
        include_once('geodir_advance_search_filters.php');
    }

    // Delete options.
    $default_options = geodir_autocompleter_options();
    if (!empty($default_options)) {
        foreach ($default_options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    delete_option('geodir_autocompleter_matches_label');
    
    // Drop tables.
    $wpdb->query("DROP TABLE " . GEODIR_ADVANCE_SEARCH_TABLE);
}