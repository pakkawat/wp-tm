<?php
// MUST have WordPress.
if ( !defined( 'WPINC' ) ) {
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
}

if ( is_admin() ) {
    add_filter( 'wpinv_admin_js_localize', 'geodir_wpi_admin_js_localize', 10, 1 );
    add_filter( 'wpinv_item_non_editable_message', 'geodir_wpi_notice_edit_package', 10, 2 );
    add_filter( 'post_class', 'geodir_wpi_package_item_classes', 11, 3 );

    add_action( 'admin_init', 'geodir_wpi_geodir_integration' );
    add_action( 'wpinv_item_info_metabox_after', 'geodir_wpi_gdp_package_type_info', 10, 1 ) ;
    add_action( 'wpinv_prices_metabox_price', 'wpinv_wpi_prices_price_note', 10, 1 ) ;

    // Tool Actions
    add_action( 'wpinv_tools_row', 'geodir_wpi_add_tools', 10 );
    add_action( 'wpinv_tool_merge_packages', 'geodir_wpi_tool_merge_packages' );
    add_action( 'wpinv_tool_merge_invoices', 'geodir_wpi_tool_merge_invoices' );
    add_action( 'wpinv_tool_merge_coupons', 'geodir_wpi_tool_merge_coupons' );
    add_action( 'wpinv_tool_merge_fix_taxes', 'geodir_wpi_tool_merge_fix_taxes' );
    
    add_action( 'add_meta_boxes', 'geodir_wpi_register_meta_box_create_invoice', 10, 2 );
    add_action( 'wp_ajax_gd_wpi_create_invoice', 'geodir_wpi_create_invoice' );
    add_action( 'wp_ajax_nopriv_gd_wpi_create_invoice', 'geodir_wpi_create_invoice' );
}

// Filters
add_filter( 'pre_option_geodir_currency', 'geodir_wpi_gdp_to_wpi_currency', 10, 2 );
add_filter( 'pre_option_geodir_currencysym', 'geodir_wpi_gdp_to_wpi_currency_sign', 10, 2 );
add_filter( 'wpinv_get_item_types', 'geodir_wpi_get_package_type', 10, 1 );
add_filter( 'wpinv_can_delete_item', 'geodir_wpi_can_delete_package_item', 10, 2 );
add_filter( 'wpinv_email_invoice_line_item_summary', 'geodir_wpi_email_line_item_summary', 10, 4 );
add_filter( 'wpinv_admin_invoice_line_item_summary', 'geodir_wpi_admin_line_item_summary', 10, 4 );
add_filter( 'wpinv_print_invoice_line_item_summary', 'geodir_wpi_print_line_item_summary', 10, 4 );
add_filter( 'wpinv_item_allowed_save_meta_value', 'geodir_wpi_skip_save_package_price', 10, 3 );
add_filter( 'wpinv_item_types_for_quick_add_item', 'geodir_wpi_remove_package_for_quick_add_item', 10, 2 );
add_filter( 'wpinv_item_dropdown_query_args', 'geodir_wpi_item_dropdown_hide_packages', 10, 3 );
add_filter( 'wpinv_get_option_address_autofill_api', 'geodir_wpi_set_google_map_api_key', 10, 3 );
add_filter( 'geodir_payment_price' , 'geodir_wpi_gdp_to_wpi_display_price', 10000, 5 );
add_filter( 'geodir_payment_checkout_redirect_url', 'geodir_wpi_gdp_to_inv_checkout_redirect', 100, 1 );
add_filter( 'geodir_payment_set_coupon_code', 'geodir_wpi_payment_set_coupon_code', 10, 3 );
add_filter( 'geodir_googlemap_script_extra', 'geodir_wpi_google_map_places_params', 101, 1 );
add_action( 'geodir_after_save_package', 'geodir_wpi_update_package_item', 10, 1 );
add_filter( 'geodir_dashboard_links', 'geodir_wpi_gdp_dashboard_invoice_history_link', 10, 1 );

// Actions
add_action( 'wpinv_update_status', 'geodir_wpi_to_gdp_update_status', 999, 3 );
add_action( 'wpinv_item_is_taxable', 'geodir_wpi_gdp_to_gdi_set_zero_tax', 10, 4 ) ;
add_action( 'wpinv_subscription_cancelled', 'geodir_wpi_to_gdp_handle_subscription_cancel', 10, 2 );
add_action( 'wpinv_checkout_cart_line_item_summary', 'geodir_wpi_cart_line_item_summary', 10, 4 );
add_action( 'geodir_payment_invoice_created', 'geodir_wpi_save_invoice', 11, 3 );
add_action( 'geodir_payment_invoice_updated', 'geodir_wpi_update_invoice', 11, 1 );
add_action( 'geodir_checkout_page_content', 'geodir_wpi_print_checkout_errors', -10 );
add_action( 'geodir_payment_invoice_status_changed', 'geodir_wpi_payment_status_changed', 11, 4 );
add_action( 'geodir_payment_invoice_transaction_details_changed', 'geodir_wpi_transaction_details_note', 11, 2 );
add_action( 'geodir_payment_post_delete_package', 'geodir_wpi_gdp_to_wpi_delete_package', 10, 1 ) ;
add_action( 'template_redirect', 'geodir_wpi_check_redirects' );