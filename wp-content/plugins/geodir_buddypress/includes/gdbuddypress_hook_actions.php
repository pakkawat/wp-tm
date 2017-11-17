<?php
/**
 * Hook and filter actions used by the plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 */

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * admin hooks
 */
if ( is_admin() ) {
	add_action( 'admin_init', 'geodir_buddypress_activation_redirect' );
	add_action( 'admin_enqueue_scripts', 'geodir_buddypress_admin_scripts', 10 );
	add_action( 'geodir_admin_option_form', 'geodir_buddypress_tab_content', 2 );
	add_action( 'wp_ajax_geodir_buddypress_ajax', 'geodir_buddypress_ajax' );
	add_action( 'wp_ajax_nopriv_geodir_buddypress_ajax', 'geodir_buddypress_ajax' );
	
	add_filter( 'geodir_settings_tabs_array', 'geodir_buddypress_tabs_array', 10 );
	add_filter( 'geodir_plugins_uninstall_settings', 'geodir_buddypress_uninstall_settings', 10, 1 );
} else { // non admin hooks
	// buddypress author link
	add_filter( 'geodir_dashboard_author_link', 'geodir_buddypress_author_link', 10, 4 );
	
	// buddypress my my link
	add_filter( 'geodir_dashboard_link_my_listing', 'geodir_buddypress_link_my_listing', 10, 3 );
	// buddypress my favorite link
	add_filter( 'geodir_dashboard_link_favorite_listing', 'geodir_buddypress_link_favorite_listing', 10, 3 );
	
	// gd to buddypress regirter form redirect
	add_action( 'wp', 'geodir_buddypress_gdsignup_redirect' );
	
	// gd to buddypress author page redirect
	add_action( 'wp', 'geodir_buddypress_author_redirect' );
	
	// gd to buddypress regirter form redirect
	add_filter( 'geodir_signup_reg_form_link', 'geodir_buddypress_signup_reg_form_link', 10, 1 );
	
	add_action( 'wp_enqueue_scripts', 'geodir_buddypress_enqueue_scripts' );
	if ( get_option( 'geodir_buddypress_link_author' ) ) {
		add_filter( 'author_link', 'geodir_buddypress_bp_author_link', 11, 2 );
	}
}

add_action( 'init', 'geodir_buddypress_init' );
// Setup navigation
add_action( 'bp_loaded', 'geodir_buddypress_on_bp_loaded', 20 );
add_action( 'bp_setup_nav', 'geodir_buddypress_setup_nav', 11 );
add_filter( 'bp_blogs_record_post_post_types', 'geodir_buddypress_record_geodir_post_types' );
add_filter( 'bp_blogs_record_comment_post_types', 'geodir_buddypress_record_comment_post_types' );

add_filter( 'bp_blogs_format_activity_action_new_blog_post', 'geodir_buddypress_new_listing_activity', 100, 2 );
add_filter( 'bp_blogs_format_activity_action_new_blog_comment', 'geodir_buddypress_new_listing_comment_activity', 100, 2 );

add_filter( 'bp_activity_get_activity_id', 'geodir_buddypress_get_activity_id', 100 );

add_filter( 'login_redirect', 'geodir_buddypress_login_redirect', 100, 3 );

add_filter( 'bp_get_activity_content_body', 'geodir_buddypress_bp_activity_featured_image', 1, 1 );
add_filter( 'bp_disable_blogforum_comments', 'geodir_buddypress_disable_comment_as_review', 10, 1 );
