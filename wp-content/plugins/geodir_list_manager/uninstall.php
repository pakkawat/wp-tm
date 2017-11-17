<?php
/**
 * Uninstall GeoDirectory Lists
 *
 * Uninstalling BuddyPress Compliments deletes the plugin data & settings.
 *
 * @package GeoDirectory_Lists
 * @since 0.0.5
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

if ( get_option( 'geodir_un_geodir_list_manager' ) ) {
    if ( !defined( 'GEODIRLISTS_VERSION' ) ) {
        // Load plugin file.
        include_once( 'geodir_list_manager.php' );
    }
    
    /* Delete post types & posts. */
    $posttypes = array( 'gd_list' );
    
    foreach ( array_unique( array_filter( $posttypes ) ) as $posttype ) {
        $args = array( 'post_type' => $posttype, 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
        $posts = get_posts( $args );
        
        // Dalete posts.
        if ( !empty( $posts ) ) {
            foreach ( $posts as $post ) {
                wp_delete_post( $post->ID );
            }
        }
    }
    
    // Delete options.
    delete_option( 'geodirlists_db_version' );
}