<?php

add_action('admin_init', 'geodir_admin_claim_listing_init');
function geodir_admin_claim_listing_init() 
{
	if(is_admin()):
		add_filter('geodir_settings_tabs_array','geodir_admin_claim_listing_tabs' , 4); 
		add_action('geodir_admin_option_form', 'geodir_get_admin_claim_listing_option_form',4);
		add_action( 'add_meta_boxes', 'geodir_add_claim_option_metabox', 12 );
		add_action('geodir_before_admin_panel' , 'geodir_display_claim_messages');
		add_filter('geodir_claim_notifications', 'geodir_enable_editor_on_claim_notifications', 1);
	endif;	
}

add_action('before_delete_post','geodir_delete_claim_listing_info', 11);

add_action( 'admin_enqueue_scripts', 'geodir_admincss_claim_manager', 10 );

add_action( 'admin_enqueue_scripts', 'geodir_claim_admin_scripts' );

add_action('wp_footer','geodir_claim_localize_all_js_msg');

add_action('admin_footer','geodir_claim_localize_all_js_msg');

add_action('wp_ajax_geodir_claim_ajax_action', "geodir_claim_manager_ajax");

add_action( 'wp_ajax_nopriv_geodir_claim_ajax_action', 'geodir_claim_manager_ajax' );

add_action('admin_init', 'geodirclaimlisting_activation_redirect');

add_action('admin_init', 'geodir_claims_change_unread_to_read');

add_action('wp_enqueue_scripts', 'geodir_add_claim_listing_stylesheet');

add_action('wp_enqueue_scripts', 'geodir_add_claim_listing_scripts');

add_action('geodir_after_edit_post_link', 'geodir_display_post_claim_link', 2);

add_action('geodir_before_main_form_fields' , 'geodir_add_claim_fields_before_main_form', 1); 

add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_claim_manager', 10,1); 

function geodir_diagnose_multisite_conversion_claim_manager($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_claim'] = __('Claim listing','geodirclaim');
	return $table_arr;
}

function geodir_add_claim_fields_before_main_form(){
	
	global $post;

	if (isset($_REQUEST['listing_type']) && $_REQUEST['listing_type'] != '') {
		$post_type = sanitize_text_field($_REQUEST['listing_type']);
	} else {
		$post_type = $post->post_type;
	}

	$geodir_post_type = get_option('geodir_post_types_claim_listing', array());

	if (!in_array($post_type, $geodir_post_type)) {
		return;
	}

	$is_claimed = isset($post->claimed) ? $post->claimed : ''; ?>
	
	<div id="geodir_claimed_row" class="required_field geodir_form_row clearfix">
			<label><?php _e(CLAIM_BUSINESS_OWNER_ASSOCIATE,'geodirclaim');?><span>*</span> </label>
			<input class="gd-radio" <?php if($is_claimed == '1'){echo 'checked="checked"';} ?> type="radio" name="claimed" value="1" field_type="radio">
			<?php _e(CLAIM_YES_TEXT,'geodirclaim');?>
			<input class="gd-radio" <?php if($is_claimed == '0'){echo 'checked="checked"';} ?> type="radio" name="claimed" value="0" field_type="radio">
			<?php _e(CLAIM_NO_TEXT,'geodirclaim');?>
		 <span class="geodir_message_error"><?php _e(CLAIM_DECLARE_OWNER_ASSOCIATE,'geodirclaim');?></span>
	</div><?php
	
}

function geodir_claim_localize_all_js_msg(){

	global $path_location_url;

	if (defined('ICL_LANGUAGE_CODE')){
		$lang = '&lang='.ICL_LANGUAGE_CODE;
	}else{
		$lang = '';
	}
	
	$arr_alert_msg = array(
							'geodir_claim_admin_url' => admin_url('admin.php'),
        'geodir_claim_admin_ajax_url' => admin_url('admin-ajax.php'),
        'geodir_claim_admin_ajax_url_form' => admin_url('admin-ajax.php?action=geodir_claim_ajax_action'.$lang),
							'geodir_want_to_delete_claim' => CLAIM_WANT_TO_DELETE,
							'geodir_want_to_approve_claim' => CLAIM_WANT_TO_APPROVE,
							'geodir_want_to_reject_claim' => CLAIM_WANT_TO_REJECT,
							'geodir_want_to_undo_claim' => CLAIM_WANT_TO_UNDO,
							'geodir_what_is_claim_process' => WHAT_IS_CLAIM_PROCESS,
							'geodir_claim_process_hide' => CLAIM_LISTING_PROCESS_HIDE,
							'geodir_claim_field_id_required' =>  __('This field is required.','geodirclaim'),
							'geodir_claim_valid_email_address_msg' =>  __('Please enter valid email address.','geodirclaim'),
						);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_claim_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}

// Add  fields for force upgrade
add_action( 'wp', 'geodir_claim_add_field_in_table');
add_action( 'wp_admin', 'geodir_claim_add_field_in_table');

add_action( 'geodir_after_claim_form_field', 'geodir_claim_after_claim_form_field', 0, 1 );
add_filter( 'geodir_payment_allow_pay_for_invoice', 'geodir_claim_allow_pay_for_invoice', 10, 2 );
add_action( 'geodir_payment_invoice_callback_claim_listing', 'geodir_claim_invoice_callback_claim_listing', 10, 4 );
add_action( 'login_form', 'geodir_claim_messsage_on_login_form', 10);
if (is_admin()) {
    add_filter('geodir_plugins_uninstall_settings', 'geodir_claim_uninstall_settings', 10, 1);
}

/*
 * Handle the claim request status change.
 *
 * @since 1.3.1
 *
 * @global object $wpdb WordPress Database object.
 * @global object $sitepress Sitepress WPML object.
 *
 * @param int $claim_id The claim id.
 * @param int $new_status New claim status. Ex: 0 for pending, 1 for approved and 2 for rejected etc.
 * @param int $old_status Old claim status. Ex: 0 for pending, 1 for approved and 2 for rejected etc.
 */
function geodir_claim_onchange_claim_value( $claim_id, $new_status, $old_status ) {
    global $wpdb, $sitepress;

    if ( !empty( $claim_id ) && geodir_is_wpml() ) {
        $data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_CLAIM_TABLE . " WHERE pid = %d", array( (int)$claim_id ) ) );

        if ( !empty( $data ) && !empty( $data->list_id ) && geodir_wpml_is_post_type_translated( get_post_type( $data->list_id ) ) && $duplicates = $sitepress->get_duplicates( $data->list_id ) ) {
            $post_data = $wpdb->get_row( $wpdb->prepare( "SELECT post_author, post_status FROM " . $wpdb->posts . " WHERE ID = %d", array( $data->list_id ) ) );
            
            if ( empty( $post_data ) ) {
                return;
            }
            
            $claimed = (int)geodir_get_post_meta( $data->list_id, 'claimed', true );
            
            foreach ( $duplicates as $duplicate ) {
                geodir_save_post_meta( $duplicate, 'claimed', $claimed );
                
                $wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->posts . " SET post_author = %d, post_status = %s WHERE ID = %d", array( $post_data->post_author, $post_data->post_status, $duplicate ) ) );
            }
        }
    }
}
add_action( 'geodir_claim_request_status_change', 'geodir_claim_onchange_claim_value', 10, 3 );

/*
 * Handle the claim delete request.
 *
 * @since 1.3.1
 *
 * @global object $wpdb WordPress Database object.
 * @global object $sitepress Sitepress WPML object.
 *
 * @param int $claim_id The claim id.
 * @param int $post_id The post id.
 */
function geodir_claim_ondelete_claim( $claim_id, $post_id ) {
    global $wpdb, $sitepress;

    if ( !empty( $post_id ) && geodir_wpml_is_post_type_translated( get_post_type( $post_id ) ) && $duplicates = $sitepress->get_duplicates( $post_id ) ) {
        $post_data = $wpdb->get_row( $wpdb->prepare( "SELECT post_author, post_status FROM " . $wpdb->posts . " WHERE ID = %d", array( $post_id ) ) );
        
        if ( empty( $post_data ) ) {
            return;
        }

        $claimed = (int)geodir_get_post_meta( $post_id, 'claimed', true );
        
        foreach ( $duplicates as $duplicate ) {
            geodir_save_post_meta( $duplicate, 'claimed', $claimed );
            
            $wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->posts . " SET post_author = %d, post_status = %s WHERE ID = %d", array( $post_data->post_author, $post_data->post_status, $duplicate ) ) );
        }
    }
}
add_action( 'geodir_claim_request_delete', 'geodir_claim_ondelete_claim', 10, 2 );