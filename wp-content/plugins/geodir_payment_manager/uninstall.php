<?php
/**
 * Uninstall GeoDirectory Payment Manager
 *
 * Uninstalling GeoDirectory Payment Manager deletes data, tables and options.
 *
 * @package GeoDirectory_Payment_Manager
 * @since 1.4.4
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $plugin_prefix;

if (get_option('geodir_un_geodir_payment_manager')) {    
    $wpdb->hide_errors();
    
    if (!defined('GEODIRPAYMENT_VERSION')) {
        // Load plugin file.
        include_once('geodir_payment_manager.php');
    }
    
    if (empty($plugin_prefix)) {
        $plugin_prefix = $wpdb->prefix . 'geodir_';
    }
    
    // Delete data.
    $post_types = geodir_get_posttypes();
    if (!empty($post_types)) {
        foreach($post_types as $post_type){
            $table = $plugin_prefix . $post_type.'_detail';
            $wpdb->query("UPDATE " . $table . " SET package_id='0', alive_days='0', paymentmethod='0', expire_date='Never', is_featured='0', paid_amount='0'");
            
            if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = 'expire_notification'"))
                $wpdb->query("ALTER TABLE " . $table . " DROP expire_notification");
        }
    }
        
    $paymentinfo = $wpdb->get_results($wpdb->prepare("SELECT option_id FROM " . $wpdb->prefix . "options WHERE option_name LIKE %s", array('payment_method_%')));
    if (!empty($paymentinfo)) {
        foreach ($paymentinfo as $payment) {
            $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "options WHERE option_id=%d ", array($payment->option_id)));
        }
    }
    
    // Update data.
    $wpdb->query("UPDATE " . GEODIR_CUSTOM_FIELDS_TABLE . " SET packages='0'");
    
    // Delete data.
    $wpdb->query("DELETE FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE htmlvar_name='is_featured'");
    
    // Delete options.
    delete_option('geodir_allow_coupon_code');
    
    $default_options = geodir_payment_general_options();
    if (!empty($default_options)) {
        foreach ($default_options as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    $notifications = geodir_payment_notifications();
    if (!empty($notifications)) {
        foreach ($notifications as $value) {
            if (isset($value['id']) && $value['id'] != '') {
                delete_option($value['id']);
            }
        }
    }
    
    // Drop tables.
    $wpdb->query("DROP TABLE " . GEODIR_PRICE_TABLE);
    $wpdb->query("DROP TABLE " . INVOICE_TABLE);
    $wpdb->query("DROP TABLE " . COUPON_TABLE);
}