<?php
/**
 * GeoDirectory Franchise hooks and filter actions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Franchise_Manager
 */
 
// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

// Admin hooks
if ( is_admin() ) {
	add_action( 'admin_init', 'geodir_franchise_activation_redirect' );
	add_action( 'add_option_geodir_franchise_posttypes', 'geodir_franchise_check_franchise_column', 10, 2 );
	add_action( 'update_option_geodir_franchise_posttypes', 'geodir_franchise_check_franchise_column', 10, 2 );
	add_action( 'geodir_after_save_package', 'geodir_franchise_after_save_package', 1, 1 );
	add_action( 'geodir_payment_package_table_created', 'geodir_franchise_payment_package_table_created', 10, 1 );
	add_action( 'add_meta_boxes', 'geodir_franchise_set_admin_package_id', -1, 2 );
	add_action( 'current_screen', 'geodir_franchise_set_identify_franchise', 10, 1 );
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { // Ajax mode
		add_action( 'wp_ajax_geodir_franchise_ajax', 'geodir_franchise_ajax' );
		add_action( 'wp_ajax_nopriv_geodir_franchise_ajax', 'geodir_franchise_ajax' );
		add_action( 'post_updated', 'geodir_franchise_post_updated', 16, 3 );
	} else {
		add_action( 'admin_enqueue_scripts', 'geodir_franchise_enqueue_scripts', 10 );
		add_filter( 'post_row_actions', 'geodir_franchise_post_row_actions', 10, 2 );
		add_action( 'add_meta_boxes', 'geodir_franchise_meta_box_add', 1, 2 );
		add_action( 'geodir_payment_package_extra_fields', 'geodir_franchise_payment_package_extra_fields', 1, 1 );
		add_action( 'admin_footer', 'geodir_franchise_localize_all_js_msg' );
		
		// Add the tab in left sidebar menu for Franchise Settings page.
		add_filter( 'geodir_settings_tabs_array', 'geodir_franchise_settings_tab', 11 );
		add_action( 'geodir_admin_option_form', 'geodir_franchise_admin_tab_content', 2 );
		add_filter('geodir_plugins_uninstall_settings', 'geodir_franchise_uninstall_settings', 10, 1);
	}
} else { // Front end hooks
	add_action( 'geodir_detail_before_main_content', 'geodir_franchise_set_locked_fields_values', 7 );
	add_action( 'geodir_after_edit_post_link', 'geodir_franchise_add_franchise_link', 1 );
	add_action( 'wp_footer', 'geodir_franchise_localize_all_js_msg' );
	
	// display linked franchises under detail page tabs
	add_filter( 'geodir_detail_page_tab_list_extend', 'geodir_detail_page_my_franchises_tab' );
	add_action( 'geodir_after_detail_page_more_info', 'geodir_franchise_detail_page_sidebar_links' );
	
	if ( function_exists( 'geodir_display_post_claim_link' ) ) {
		add_action( 'geodir_after_edit_post_link', 'geodir_franchise_remove_franchise_claim_link', 1 );
	}
}

add_action( 'init', 'geodir_franchise_init');
add_action( 'geodir_before_detail_fields', 'geodir_franchise_add_listing_field' );
add_action( 'geodir_before_detail_fields' , 'geodir_franchise_set_package_id', 0 );
add_action( 'wp_enqueue_scripts', 'geodir_franchise_enqueue_scripts' );
add_action( 'geodir_after_save_listing', 'geodir_franchise_after_save_listing', 10, 2 );
add_action( 'pre_get_posts', 'geodir_franchise_pre_get_posts', 10 );
add_filter( 'geodir_filter_widget_listings_where', 'geodir_franchise_widget_listings_where', 10, 2 );
if (geodir_franchise_is_payment_active()) {
	add_filter( 'geodir_payment_allow_pay_for_invoice', 'geodir_franchise_allow_pay_for_invoice', 10, 2 );
	add_action( 'geodir_payment_invoice_callback_add_franchise', 'geodir_franchise_invoice_callback_add_franchise', 10, 4 );
	add_filter( 'geodir_payment_invoice_types', 'geodir_franchise_payment_invoice_types' );
	add_filter( 'geodir_payment_invoice_before_listing_details', 'geodir_franchise_invoice_before_listing_details', 10, 1 );
	add_filter( 'geodir_payment_invoice_after_listing_details', 'geodir_franchise_invoice_after_listing_details', 10, 1 );
	add_filter( 'geodir_payment_admin_list_invoice_links', 'geodir_franchise_admin_list_invoice_links', 10, 2 );
}
add_action( 'geodir_claim_request_status_change', 'geodir_franchise_claim_status_change', 10, 3 );
add_filter( 'geodir_post_view_extra_class', 'geodir_franchise_post_view_class', 10, 2 );
add_filter( 'geodir_gd_options_for_translation', 'geodir_franchise_add_options_for_translation', 10, 1 );
add_filter( 'geodir_seo_page_title', 'geodir_franchise_seo_page_title', 9, 2 );
