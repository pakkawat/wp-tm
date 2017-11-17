<?php
/**
 * Contains hook related to Payment Manager plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Payment_Manager
 */
 
/**
 * activation hooks
 **/
if ( is_admin() ) {
	add_action('admin_enqueue_scripts', 'geodir_admincss_payment_manager', 10);
	add_filter('geodir_settings_tabs_array','geodir_payment_manager_tabs',2);
	add_action('geodir_admin_option_form' , 'geodir_payment_manager_tab_content', 2);
	add_action('wp_ajax_geodir_payment_manager_ajax', "geodir_payment_manager_ajax");
	add_action('wp_ajax_nopriv_geodir_payment_manager_ajax', 'geodir_payment_manager_ajax'); 
	add_action('save_post', 'geodir_post_transaction_save');
	add_action('admin_init', 'geodir_payment_activation_redirect');
	add_action('admin_enqueue_scripts', 'geodir_payment_admin_scripts');
	add_action('admin_footer','geodir_payment_localize_all_js_msg');
	add_filter('geodir_payment_notifications', 'geodir_enable_editor_on_payment_notifications', 1);
	add_action('geodir_create_new_post_type', 'geodir_payment_create_new_post_type', 1, 1);
	add_action('geodir_after_post_type_deleted', 'geodir_payment_delete_post_type', 1, 1);
	add_filter('geodir_after_custom_detail_table_create', 'geodir_payment_after_custom_detail_table_create', 2, 2);
	add_filter('geodir_notifications_settings', 'geodir_payment_notification_add_bcc_option', 1);
	add_filter('geodir_plugins_uninstall_settings', 'geodir_payment_uninstall_settings', 10, 1);
	add_action('admin_notices', 'geodir_payment_invoicing_notice');
}

/**
 * Outputs translated JS text strings.
 *
 * @since 1.0.0
 *
 * @global $path_location_url Path of current file location.
 */
function geodir_payment_localize_all_js_msg() {
	global $path_location_url;
	
	$checkout_page_link = (geodir_payment_checkout_page_id()) ? get_page_link( geodir_payment_checkout_page_id() ) : '';
	
	$arr_alert_msg = array(
		'geodir_payment_admin_url' => admin_url('admin.php'),
		'geodir_payment_admin_ajax_url' => admin_url('admin-ajax.php'),
		'geodir_want_to_delete_price' =>__('Are you sure want to delete price?','geodir_payments'),
		'geodir_payment_enter_title' =>__('Please enter Title','geodir_payments'),
		'geodir_payment_coupon_code' =>__('Please enter coupon code.','geodir_payments'),
		'geodir_payment_select_post_type' =>__('Please select post type.','geodir_payments'),
		'geodir_payment_enter_discount' =>__('Please enter discount amount.','geodir_payments'),
		'geodir_payment_delete_coupon' =>__('Are you sure want to delete coupon?','geodir_payments'),
		'geodir_payment_recur_times_msg' =>__('Recurring times must be blank or greater than 1','geodir_payments'),
		'authorizenet_cardholder_name_empty' =>__('Please enter Cardholder name', 'geodir_payments'),
		'authorizenet_cc_number_empty' =>__('Please enter card number', 'geodir_payments'),
		'authorizenet_cc_date_empty' =>__('Please enter expire date', 'geodir_payments'),
		'ajax_invoices_nonce' => wp_create_nonce( 'ajax_invoices_nonce' ),
		'geodir_pay_invoice_confirm' => __( 'Are you sure want to pay for this invoice?', 'geodir_payments' ),
		'geodir_send_invoice_confirm' => __( 'Are you sure want to send the invoice via email?', 'geodir_payments' ),
		'geodir_checkout_link' => $checkout_page_link,
	);
	
	foreach ( $arr_alert_msg as $key => $value ) {
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_payment_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}

/**
 * Add action for payment gateway ipn handler.
 *
 * @since 1.0.0
 *
 * @global object $wp_query WordPress Query object.
 */
function geodir_payment_ipn() {
	if (isset($_REQUEST['pay_action']) ) {
		global $wp_query;
		
		if ($_REQUEST['pay_action'] == 'ipn' && isset($_REQUEST['pmethod']))	{
			/**
			 * Handle the payment ipn request for the payment method.
			 *
			 * @since 1.0.0
			 *
			 * @param int $invoice_id Current payment invoice/cart id.
			 */
			do_action('geodir_ipn_handler_' . $_REQUEST['pmethod'] ); /* ADD IPN handler action */
			exit;
		}
	}
}
add_action( 'init', 'geodir_payment_ipn' );

/**
 * Filter the template to display after payment process completed.
 *
 * @since 1.0.0
 *
 * @global object $wp_query WordPress Query object.
 *
 * @param string $template Full path of the template file.
 * @return Template file path.
 */
function geodir_payment_response($template){
	if(isset($_REQUEST['pay_action']) ) {
		global $wp_query;
		
		if ($_REQUEST['pay_action'] == 'cancel') {	
			$template = locate_template( array( 'geodirectory/cancel.php' ) );
			if ( ! $template ) 
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/cancel.php';
		}
		
		if ($_REQUEST['pay_action'] == 'return') {	
			$template = locate_template( array( 'geodirectory/return.php' ) );
			if ( ! $template )
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/return.php';
		}	
		if ($_REQUEST['pay_action'] == 'success') {	
			$template = locate_template( array( 'geodirectory/success.php' ) );
			if ( ! $template ) 
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/success.php';
		}
	}
	return $template;
}
add_filter('template_include', 'geodir_payment_response', 200);
add_action('geodir_before_detail_fields' , 'geodir_build_payment_list', 1); 
add_action('geodir_before_detail_fields' , 'geodir_build_coupon', 2); 
add_filter('geodir_post_package_info', 'geodir_get_post_package_info_on_listing' , 2, 3) ;
add_action('geodir_before_admin_panel' , 'geodir_display_payment_messages'); 
add_action('geodir_after_edit_post_link', 'geodir_display_post_upgrade_link', 1); 
add_action('geodir_before_edit_post_link_on_listing', 'geodir_display_post_upgrade_link_on_listing', 1);
add_action('geodir_after_edit_post_link', 'geodir_payment_display_expire_date_on_detail', 10);
add_action('geodir_after_edit_post_link_on_listing', 'geodir_payment_display_expire_date_on_listing', 10);
add_filter('geodir_publish_listing_form_message', 'geodir_payment_publish_listing_form_message', 1, 2);

/**
 * Filter the publish listing message on the preview page.
 *
 * @since 1.0.0
 *
 * @param string $form_message The message to be filtered.
 */
function geodir_payment_publish_listing_form_message( $form_message ) {
	return $form_message = '';
}

add_filter('geodir_publish_listing_form_go_back', 'geodir_payment_publish_listing_form_go_back', 1, 2);
/**
 * Get the go back and edit HTML on the preview page.
 *
 * @since 1.0.0
 *
 * @param string $listing_form_go_back The HTML for the cancel and go back and edit button/link.
 * @return Go back and edit buttons/links.
 */
function geodir_payment_publish_listing_form_go_back($listing_form_go_back) {
	return $listing_form_go_back = '';
}

add_filter('geodir_publish_listing_form_button', 'geodir_payment_publish_listing_form_button', 1, 2);
/**
 * Get the HTML button for publishing the listing on the preview page.
 *
 * @since 1.0.0
 *
 * @param string $listing_form_button The HTML for the submit button.
 * @return The submit button.
 */
function geodir_payment_publish_listing_form_button($listing_form_button) {
	return $listing_form_button = '';
}

if (isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != '') {
	add_filter('geodir_publish_listing_form_action', 'geodir_payment_publish_listing_form_action', 1, 2);
}

/**
 * Get the URL for the publish listing form on the preview page.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $form_action_url The URL for the form.
 * @return Submit listing form url.
 */
function geodir_payment_publish_listing_form_action($form_action_url) {
	global $post;
	
	$post_type = $post->listing_type;
	
	$package_price_info = geodir_get_post_package_info($_REQUEST['package_id']);
	
	$payable_amount = $package_price_info['amount'];
	
	if ($payable_amount > 0) {
		$form_action_url = geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=pre-checkout&listing_type=' . $post_type;	
	}

	return $form_action_url;
}

add_action( 'geodir_publish_listing_form_before_msg', 'geodir_publish_payment_listing_form_before_msg', 1 );
/**
 * Add the content on the add listing preview page inside the publish 
 * listings form, before the publish message.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 * @global object $wpdb WordPress Database object.
 */
function geodir_publish_payment_listing_form_before_msg() {
    global $post, $wpdb;

    $post_type = $post->listing_type;

    $req_package_id = isset($_REQUEST['package_id']) ? (int)$_REQUEST['package_id'] : '';
    $pid = isset($_REQUEST['pid']) ? (int)$_REQUEST['pid'] : '';
    $coupon_code = isset($_REQUEST['coupon_code']) ? sanitize_text_field($_REQUEST['coupon_code']) : '';

    if ($req_package_id != '') {
        $package_price_info = geodir_get_post_package_info($req_package_id);
    } else {
        if (!empty($post) && isset($post->package_id)) {
            $package_price_info = geodir_get_post_package_info($post->package_id);
        }
    }

    $package_id = isset($package_price_info['pid']) ? $package_price_info['pid'] : '';
    $payable_amount = isset($package_price_info['amount']) ? $package_price_info['amount'] : 0;
    $alive_days = isset($package_price_info['days']) ? $package_price_info['days'] : 0;
    $type_title = isset($package_price_info['title']) ? $package_price_info['title'] : '';
    $sub_active = isset($package_price_info['sub_active']) ? $package_price_info['sub_active'] : '';

    $recurring_desc = '';
    $free_trial_desc = '';
    if ( $sub_active ) {
        $sub_units_num_var = $package_price_info['sub_units_num'];
        $sub_units_var = $package_price_info['sub_units'];
        $sub_units_num_times = $package_price_info['sub_units_num_times'];
        $alive_days = geodir_payment_get_units_to_days( $sub_units_num_var, $sub_units_var );
        
        // paypal free trial
        $sub_num_trial_days_var = $package_price_info['sub_num_trial_days'];
        $sub_num_trial_units_var = $package_price_info['sub_num_trial_units'];
        
        $desc_suffix = '';
        if ( $sub_num_trial_days_var > 0 ) {
            $desc_suffix = __( 'Then charged' , 'geodir_payments' );
            $alive_days = geodir_payment_get_units_to_days( $sub_num_trial_days_var, $sub_num_trial_units_var );
            $free_trial_desc = geodir_payment_checkout_free_trial_desc( $sub_num_trial_days_var, $sub_num_trial_units_var );
        }
        
        $recurring_desc = geodir_payment_recurring_pay_desc($sub_units_var, $sub_units_num_var, $sub_units_num_times, $desc_suffix);
    }

    $org_payable_amount = $payable_amount;

    $alive_days = $alive_days == 0 ? UNLIMITED : $alive_days;

    $preview_message = __('This is a preview of your listing, it has not been published yet. <br />If there is something wrong then "Go back and edit" or if you want to add the listing then click on "Publish".', 'geodir_payments');
    $preview_button_label = __('Publish', 'geodir_payments');

    $coupon_code_msg = '';
    if ($coupon_code != '') {
        if (geodir_payment_allow_coupon_usage(array('package_id' => $package_id)) && geodir_is_valid_coupon($post_type, $coupon_code) && geodir_payment_coupon_usage_count_left($coupon_code)) {
            $payable_amount = geodir_get_payable_amount_with_coupon($payable_amount, $coupon_code);
        } else {
            $coupon_code = '';
            $coupon_code_msg = '<p class="error_msg_fix">' . WRONG_COUPON_MSG . '</p>';
        }
    }
    $payable_amount_display = geodir_payment_price($payable_amount);

    if ($recurring_desc !== '' || $free_trial_desc !== '') {
        $free_trial_desc = '<font class="gd-free-trial-desc">' . $free_trial_desc . '</font>';
        $recurring_desc = '<font class="gd-recurring-desc">' . $recurring_desc . '</font>';
        $payable_amount_display .= ' ( ' . trim($free_trial_desc . ' ' . $recurring_desc) . ' )';
    }

    $gd_pay_type = 'new';
    $post_package_id = geodir_get_post_meta($pid, 'package_id', true);
    
    if (!$pid > 0 && !((float)$payable_amount > 0)) { // new
    } elseif ($pid > 0 && !((float)$payable_amount > 0)) { // update
        if ($post_package_id == $package_id && get_post_status($pid) == 'draft') { // free renew
            $gd_pay_type = 'renew';
            $preview_message = __('This is a preview of your listing, it has not been updated yet. <br />If there is something wrong then "Go Back and Edit" or if you want to renew the listing then click on "Publish & Renew Now".', 'geodir_payments');
            $preview_button_label = __('Publish & Renew Now', 'geodir_payments');
        } else {
            $gd_pay_type = 'update';
            $preview_message = __('This is a preview of your listing, it has not been updated yet. <br />If there is something wrong then "Go Back and Edit" or if you want to update the listing then click on "Update Now".', 'geodir_payments');
            $preview_button_label = __('Update Now', 'geodir_payments');
        }
    } elseif ($req_package_id > 0 && (float)$payable_amount > 0 && !$pid > 0) { // paid new
        $preview_message = wp_sprintf(__('This is a preview of your listing, it has not been published yet. <br />If there is something wrong then "Go Back and Edit" or if you want to add the listing then click on "Confirm Preview & Go to Checkout".<br> You are going to pay <b>%s</b> & alive days are <b>%s</b> as %s listing.', 'geodir_payments'), $payable_amount_display , $alive_days , $type_title);
        $preview_button_label = __('Confirm Preview & Go to Checkout', 'geodir_payments');
    } elseif ($req_package_id > 0 && (float)$org_payable_amount > 0 && $pid > 0) { // paid update
        if ($post_package_id == $package_id) { // paid renew
            $gd_pay_type = 'renew';
            $preview_message = wp_sprintf(__('This is a preview of your listing, it has not been updated yet. <br />If there is something wrong then "Go Back and Edit" or if you want to renew the listing then click on "Checkout to Renew Now".<br> You are going to pay <b>%s</b> & alive days are <b>%s</b> as %s listing.', 'geodir_payments'), $payable_amount_display , $alive_days , $type_title);
            $preview_button_label = __('Checkout to Renew Now', 'geodir_payments');
        } else { // paid upgrade
            $gd_pay_type = 'upgrade';
            $preview_message = wp_sprintf(__('This is a preview of your listing, it has not been updated yet. <br />If there is something wrong then "Go Back and Edit" or if you want to upgrade the listing then click on "Checkout to Upgrade Now".<br> You are going to pay <b>%s</b> & alive days are <b>%s</b> as %s listing.', 'geodir_payments'), $payable_amount_display , $alive_days , $type_title);
            $preview_button_label = __('Checkout to Upgrade Now', 'geodir_payments');
        }
    }

    /* -------- START LISTING FORM MESSAGE*/
    ob_start();
    echo $coupon_code_msg;	
    echo '<h5 class="geodir_information">' . $preview_message . '</h5>';
    /* -------- END LISTING FORM MESSAGE*/

    /* -------- START LISTING FORM BUTTON*/
    ?>
    <input type="hidden" name="price_select" value="<?php echo $package_id;?>" />
    <input type="hidden" name="coupon_code" value="<?php echo $coupon_code;?>" />
    <input type="hidden" name="gd_pay_type" value="<?php echo $gd_pay_type;?>" />
    <?php 
    echo '<input type="submit" name="Submit and Pay" value="' . esc_attr($preview_button_label) . '" class=" geodir_button geodir_publish_button" />';
    /* -------- END LISTING FORM BUTTON*/

    /* -------- START LISTING GO BACK LINK*/
    $post_id = '';
    if (isset($post->pid)) {
        $post_id = (int)$post->pid;
    } else if ($pid) {
        $post_id = (int)$pid;
    }

    $postlink = get_permalink(geodir_add_listing_page_id());
    $postlink = geodir_getlink($postlink, array('pid' => $post_id, 'backandedit' => '1', 'listing_type' => $post_type ), false);

    if ($req_package_id != '') {
        $postlink = geodir_getlink($postlink, array('package_id' => $req_package_id), false);
    }
    ?>
    <input type="button" name="Go Back" value="<?php echo esc_attr(PRO_BACK_AND_EDIT_TEXT);?>" class="geodir_button goback_button" onclick="window.location.href='<?php echo $postlink;?>'" />&nbsp;&nbsp;
    <input type="button" name="Cancel" value="<?php echo esc_attr(PRO_CANCEL_BUTTON);?>" class="geodir_button cancle_button" onclick="window.location.href='<?php echo geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=cancel&pid='.$post_id.'&listing_type='.$post_type;?>'" />
    <?php
    $content = ob_get_clean();

    echo $content;
}

add_action('init', 'payment_handler');
/**
 * Perform handler for payment after submit listing.
 *
 * @since 1.0.0
 *
 * @global object $gd_session GeoDirectory Session object.
 */
function payment_handler() {
	global $gd_session;
	
	$geodir_ajax = isset($_REQUEST['geodir_ajax']) ? $_REQUEST['geodir_ajax'] : '';
	$ajax_action = isset($_REQUEST['ajax_action']) ? $_REQUEST['ajax_action'] : '';

	if ($geodir_ajax == 'add_listing') {
		switch($ajax_action) {
			case 'pre-checkout': {
				$request = $gd_session->get('listing');

				if (!empty($request) && isset($request['geodir_spamblocker']) && $request['geodir_spamblocker'] == '64' && isset($request['geodir_filled_by_spam_bot']) && $request['geodir_filled_by_spam_bot'] == '') {
					$post_id = geodir_save_listing();
					if ( $post_id ) {
						$package_id = $request['package_id'];
						$package_info = geodir_get_package_info( $package_id );
						$package_id = $package_info->pid;
						$post_type = get_post_type( $post_id );
						
						$coupon_code = isset( $_REQUEST['coupon_code'] ) ? $_REQUEST['coupon_code'] : '';
						
						$alive_days = $package_info->days;
						$sub_units = $package_info->sub_units;
						$sub_units_num = $package_info->sub_units_num;
						
						if ( $package_info->sub_active ) {
							$sub_units_num_var = $package_info->sub_units_num;
							$sub_units_var = $package_info->sub_units;
							$alive_days = geodir_payment_get_units_to_days( $sub_units_num_var, $sub_units_var );
							$sub_num_trial_days_var = $package_info->sub_num_trial_days;
							$sub_num_trial_units = isset( $package_info->sub_num_trial_units ) ? $package_info->sub_num_trial_units : 'D';
							$sub_num_trial_days_var = geodir_payment_get_units_to_days( $sub_num_trial_days_var, $sub_num_trial_units );
							$sub_units_num_times_var = $package_info->sub_units_num_times;
							
							if ( $package_info->sub_num_trial_days > 0 ) {
								$alive_days = $sub_num_trial_days_var;
							}
						}
						
						$expire_date = $alive_days > 0 ? date_i18n( 'Y-m-d', strtotime( date_i18n( 'Y-m-d' ) . ' + ' . (int)$alive_days . ' days' ) ) : '';
						
						$amount = $package_info->amount;
						
						$discount = $coupon_code != '' ? geodir_get_discount_amount( $coupon_code, $amount ) : 0;
						$tax_amount = geodir_payment_get_tax_amount( $amount, $package_id, $post_id );
						
						
						if (!($coupon_code != '' && geodir_payment_allow_coupon_usage(array('package_id' => $package_id)) && geodir_is_valid_coupon($post_type, $coupon_code) && geodir_payment_coupon_usage_count_left($coupon_code))) {
							$coupon_code = '';
							$discount = 0;
						}
						
						$amount = geodir_payment_price( $amount, false );
						
						$paid_amount = ( $amount + $tax_amount ) - $discount;
						$paid_amount = $paid_amount > 0 ? $paid_amount : 0;
						
						$payment_status = $paid_amount > 0 ? 'pending' : 'confirmed';
						
						$invoice_type = 'add_listing';
						$invoice_callback = 'add_listing';
						$invoice_title = wp_sprintf(  __( 'Add Listing: %s', 'geodir_payments' ), get_the_title( $post_id ) );
						if ($package_id && $amount > 0 && !empty($_REQUEST['pid'])) {
							$post_status = get_post_status($_REQUEST['pid']);
							$gd_pay_type = !empty($_POST['gd_pay_type']) ? $_POST['gd_pay_type'] : 'upgrade';
							if ($gd_pay_type == 'renew' || ($gd_pay_type != 'upgrade' && $post_status == 'draft')) {
								$invoice_type = 'renew_listing';
								$invoice_callback = 'renew_listing';
								$invoice_title = wp_sprintf(  __( 'Renew Listing: %s', 'geodir_payments' ), get_the_title( $post_id ) );
							} else {
								$invoice_type = 'upgrade_listing';
								$invoice_callback = 'upgrade_listing';
								$invoice_title = wp_sprintf(  __( 'Upgrade Listing: %s', 'geodir_payments' ), get_the_title( $post_id ) );
							}
						}
						
						$data = array();
						$data['type'] = $amount > 0 ? 'paid' : 'free';
						$data['post_id'] = $post_id;
						$data['post_title'] = $invoice_title;
						$data['post_action'] = 'add';
						$data['invoice_type'] = $invoice_type;
						$data['invoice_callback'] = $invoice_callback;
						$data['invoice_data'] = maybe_serialize( array() );
						$data['package_id'] = $package_id;
						$data['package_title'] = __(stripslashes_deep($package_info->title), 'geodirectory');
						$data['amount'] = $amount;
						$data['alive_days'] = $alive_days;
						$data['expire_date'] = $expire_date;
						$data['coupon_code'] = $coupon_code;
						$data['discount'] = $discount;
						$data['tax_amount'] = $tax_amount;
						$data['paied_amount'] = $paid_amount;
						$data['status'] = $payment_status;
						$data['is_current'] = 1;
						$data['subscription'] = !empty( $package_info->sub_active ) ? 1 : 0;

						$invoice_id = geodir_create_invoice( $data );
						
						if ( $invoice_id ) {
							geodir_update_invoice_status( $invoice_id, $payment_status );
							
							/**
							 * Called before redirect to the payment checkout page.
							 *
							 * @since 1.2.6
							 *
							 * @param int $invoice_id Current payment invoice/cart id.
							 */
							do_action( 'geodir_payment_checkout_redirect', $invoice_id );
							
							wp_redirect( home_url() );
							gd_die();
						}
					}
				} else {
					$gd_session->un_set('listing');
					wp_redirect( home_url() );
				}
			}
		}
	}
	
	if ($geodir_ajax == 'checkout' && $cart_id = geodir_payment_cart_id()) {
		$cart = geodir_payment_get_cart($cart_id);
		$_wpnonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

		if ( wp_verify_nonce( $_wpnonce, 'gd_cart_nonce' ) && !empty( $cart ) && ( !empty( $_POST['gd_payment_method'] ) || !empty( $_POST['gd_checkout_publish'] ) ) ) {
			$payment_method = !empty( $_POST['gd_payment_method'] ) ? $_POST['gd_payment_method'] : '';
			$checkout_publish = !empty( $_POST['gd_checkout_publish'] ) ? $_POST['gd_checkout_publish'] : '';
			
			$data['id'] = $cart_id;
			$data['paymentmethod'] = $payment_method;

			$free_publish = !$cart->paied_amount > 0 && wp_verify_nonce( $checkout_publish, 'gd_checkout_publish' . $cart_id ) ? true : false;
			
			if ( $free_publish ) {
				/**
				 * Called before publishing invoice when amount is not payable.
				 *
				 * @since 1.3.2
				 *
				 * @param int $cart_id Current payment invoice/cart id.
				 */
				do_action( 'geodir_payment_checkout_free_publish_before', $cart_id );
				
				$data['paymentmethod'] = 'free';
				$data['type'] = 'free';
				geodir_update_invoice( $data );
				
				geodir_payment_invoice_coupon_usage_count($cart_id);
				
				$user_id = $cart->user_id;
				$post_id = $cart->post_id;
				$item_name = $cart->post_title;
				if (!empty($cart->coupon_code) && $cart->discount > 0) {
					$cart->paied_amount = $cart->amount;
				}
				$payable_amount = geodir_payment_price( $cart->paied_amount );
					
				$transaction_details = '';
				$transaction_details .= '--------------------------------------------------<br />';
				$transaction_details .= sprintf( __( 'Payment Details for Invoice ID #%s', 'geodir_payments' ), geodir_payment_invoice_id_formatted($cart_id) ) . '<br />';
				$transaction_details .= '--------------------------------------------------<br />';
				$transaction_details .= sprintf( __( 'Item Name: %s', 'geodir_payments' ), $item_name ) . '<br />';
				$transaction_details .= '--------------------------------------------------<br />';
				$transaction_details .= sprintf( __( 'Status: %s', 'geodir_payments' ), __( 'Confirmed', 'geodir_payments' ) ) . '<br />';
				$transaction_details .= sprintf( __( 'Amount: %s', 'geodir_payments' ), $payable_amount ) . '<br />';
				if (!empty($cart->coupon_code) && $cart->discount > 0) {
					$transaction_details .= sprintf( __( 'Discount: %s', 'geodir_payments' ), geodir_payment_price( $cart->discount ) ) . '<br />';
				}
				$transaction_details .= sprintf( __( 'Type: %s', 'geodir_payments' ), __( 'Free', 'geodir_payments' ) ) . '<br />';
				$transaction_details .= sprintf( __( 'Date: %s', 'geodir_payments' ), date_i18n( 'F j, Y, g:i a', current_time( 'timestamp' ) ) ) . '<br />';
				$transaction_details .= sprintf( __( 'Method: %s', 'geodir_payments' ), __( 'Instant Publish', 'geodir_payments' ) ) . '<br />';
				$transaction_details .= '--------------------------------------------------<br />';
				
				// update invoice status and transaction details
				geodir_update_invoice_status( $cart_id, 'confirmed' );
				geodir_update_invoice_transaction_details( $cart_id, $transaction_details );				
				
				// Send notification to admin.
				geodir_payment_adminEmail( $post_id, $user_id, 'payment_success', $transaction_details );
				
				// Send notification to client.
				geodir_payment_clientEmail( $post_id, $user_id, 'payment_success', $transaction_details );		
								
				/**
				 * Called after publishing invoice when amount is not payable.
				 *
				 * @since 1.3.2
				 *
				 * @param int $cart_id Current payment invoice/cart id.
				 */
				do_action( 'geodir_payment_checkout_free_publish_after', $cart_id );
				
				// Clear cart
				geodir_payment_clear_cart();
				
				$redirect_url = geodir_info_url(  array( 'pay_action' => 'success', 'inv' => $cart_id, 'pid' => $post_id ) );
				wp_redirect( $redirect_url );
				gd_die();
			} else if ( !$free_publish && $payment_method ) {
				geodir_update_invoice( $data );
				
				geodir_payment_invoice_coupon_usage_count($cart_id);
				
				/**
				 * Called before redirect to the payment gateway form for all payment methods.
				 *
				 * @since 1.2.6
				 *
				 * @param int $cart_id Current payment invoice/cart id.
				 */
				do_action( 'geodir_payment_form_handler_global', $cart_id );
				
				/**
				 * Called before redirect to the payment gateway form for selected payment method.
				 *
				 * @since 1.2.6
				 *
				 * @param int $cart_id Current payment invoice/cart id.
				 */
				do_action( 'geodir_payment_form_handler_' . $payment_method, $cart_id );
			} else {
				// Clear cart
				geodir_payment_clear_cart();
				
				wp_redirect( home_url() );
				gd_die();
			}
		}
	}
}

add_action('geodir_after_save_listing', 'geodir_save_listing_payment', 2, 2);
/**
 * Process the listing price package values after listing saved.
 *
 * @since 1.0.0
 *
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param int $last_post_id The listing id.
 * @param array $request_info The listing request data.
 */
function geodir_save_listing_payment( $last_post_id, $request_info ) {
	global $gd_session;
	
	$payment_info = array();
	$package_info = array();

	if ( isset( $request_info['alive_days'] ) && isset( $request_info['expire_date'] ) ) {
		if( !empty( $request_info['expire_date'] ) && $request_info['expire_date'] != '0000-00-00' && geodir_strtolower( $request_info['expire_date'] ) != 'never' ) {
			$payment_info['expire_date'] = $request_info['expire_date'];
		} else if( $request_info['alive_days'] > 0 ) {
			$old_alive_days = geodir_get_post_meta( $last_post_id, 'alive_days', true );
			$old_expire_date = geodir_get_post_meta( $last_post_id, 'expire_date', true );
			$old_expire_date = $request_info['expire_date'];
			
			$actual_date = date( 'Y-m-d' );
			
			if( $old_alive_days > 0 && $old_expire_date != '' && $old_expire_date != '0000-00-00' && $old_expire_date != 'Never' ) {
				$actual_date = date( 'Y-m-d', strtotime( $old_expire_date . "-" . $old_alive_days . " days" ) );
			}
			
			$payment_info['expire_date'] = date( 'Y-m-d', strtotime( $actual_date . "+" . $request_info['alive_days'] . " days" ) );
		} else {
			$payment_info['expire_date'] = 'Never';
			
			if ( $request_info['expire_date'] != '' && $request_info['expire_date'] != '0000-00-00' ) {
				$payment_info['expire_date'] = $request_info['expire_date'];
			}
		}
		
		$payment_info['alive_days'] = $request_info['alive_days'];
		$payment_info['package_id'] = $request_info['package_id'];
		$payment_info['is_featured'] = $request_info['is_featured'];
	}	
	
	if (isset($request_info['package_id']) && $request_info['package_id'] != '' && empty($payment_info)) {
		$package_info = (array)geodir_get_package_info($request_info['package_id']);

		if (!empty($package_info)) {
			if (isset($package_info['sub_active']) && $package_info['sub_active']=='1' && isset($package_info['sub_units_num']) && $package_info['sub_units_num']>0) {
				if($package_info['sub_units']=='D'){$mult = 1;}
				if($package_info['sub_units']=='W'){$mult = 7;}
				if($package_info['sub_units']=='M'){$mult = 30;}
				if($package_info['sub_units']=='Y'){$mult = 365;}
				$pay_days = ($package_info['sub_units_num']*$mult);
				if (!empty($_POST['gd_pay_type']) && $_POST['gd_pay_type'] == 'new' && !empty($package_info['sub_num_trial_days'])) {
					$trial_unit = !empty($package_info['sub_num_trial_units']) ? $package_info['sub_num_trial_units'] : 'D';
					$pay_days = geodir_payment_get_units_to_days($package_info['sub_num_trial_days'], $trial_unit);
				}
				$payment_info['expire_date'] = $expire_date = date_i18n('Y-m-d', strtotime("+" . $pay_days . " days"));
				$payment_info['alive_days'] = $pay_days;
			} elseif (isset($package_info['days']) && $package_info['days'] != 0) {
				$old_alive_days = geodir_get_post_meta($last_post_id, 'alive_days', true);
				$old_expire_date = geodir_get_post_meta($last_post_id, 'expire_date', true);
				$old_package_id = geodir_get_post_meta($last_post_id, 'package_id', true);
				
				$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
				
				if (!empty($old_package_id) && $old_alive_days > 0 && $old_package_id == $request_info['package_id'] && geodir_strtolower($old_expire_date) != 'never' && strtotime($old_expire_date) >= strtotime($current_date) && get_post_status($last_post_id) == 'publish') {
					$alive_days = (int)($old_alive_days + $package_info['days']);
					$expire_date = date_i18n( 'Y-m-d', strtotime($old_expire_date . ' + ' . $package_info['days'] . ' days'));
					
					if (!empty($_POST['gd_pay_type']) && $_POST['gd_pay_type'] == 'renew') {
						$alive_days = (int)$old_alive_days;
						$expire_date = $old_expire_date;
					}elseif(!empty($_POST['gd_pay_type']) && $_POST['gd_pay_type'] == 'new'){
						$alive_days = $package_info['days'];
						$expire_date = date_i18n('Y-m-d', strtotime("+" . $alive_days . " days"));
					}
				} else {
					if (!empty($_POST['gd_pay_type']) && $_POST['gd_pay_type'] == 'renew' && get_post_status($last_post_id) == 'publish') {
						$alive_days = (int)$old_alive_days;
						$expire_date = strtotime($old_expire_date) >= strtotime($current_date) ? $old_expire_date : $current_date;
					} else {
						$alive_days = $package_info['days'];
						$expire_date = date_i18n('Y-m-d', strtotime("+" . $alive_days . " days"));
  					}
				}
				$payment_info['expire_date'] = $expire_date;
				$payment_info['alive_days'] = $alive_days;
			} else {
				$payment_info['expire_date'] = 'Never';
				$payment_info['alive_days'] = $package_info['days'];
			}
			
			$payment_info['package_id'] = $package_info['pid'];
			$payment_info['is_featured'] = $package_info['is_featured'];
		}
	}
	
	$session_listing['geodir_prev_package_id'] = $gd_session->get('geodir_prev_package_id');
	$session_listing['geodir_prev_expire_date'] = $gd_session->get('geodir_prev_expire_date');
	$payment_info['expire_notification'] = 'false';
	$payment_info['exp2'] = 'false';
	$payment_info['exp3'] = 'false';
		
	// if listing not expired and goes to upgrade listing with same package
	if (!empty($payment_info) && !empty($_REQUEST['price_select']) && !empty($_REQUEST['paymentmethod']) && !empty($_REQUEST['pid']) && $_REQUEST['ajax_action']=='paynow') {
		$session_listing = $gd_session->get('listing');
        $prev_package_id = $gd_session->get('geodir_prev_package_id') ? $gd_session->get('geodir_prev_package_id') : geodir_get_post_meta($_REQUEST['pid'], 'package_id', true);
        $prev_expire_date = $gd_session->get('geodir_prev_expire_date') ? $gd_session->get('geodir_prev_expire_date') : geodir_get_post_meta($_REQUEST['pid'], 'expire_date', true);

		if ($prev_package_id==$_REQUEST['price_select'] && $prev_expire_date && strtotime($prev_expire_date) >= strtotime(date('Y-m-d'))) {
			$payment_info = array();
		}
	}


	if (!empty($payment_info)) {

        $prev_package_id = (isset($prev_package_id)) ? $prev_package_id : geodir_get_post_meta($last_post_id, 'package_id', true);
        $package_id = (isset($payment_info['package_id'])) ? $payment_info['package_id'] : '';

		geodir_save_post_info($last_post_id, $payment_info);

        /**
         * Fires when a post payment info is saved.
         *
         * The `$prev_package_id` may not be set all the time.
         *
         * @param int $last_post_id The post id that is being downgraded.
         * @param int|null $prev_package_id The price package the post was on.
         * @param int|null $package_id The price package the post is being downgraded to.
         * @param array $payment_info An array containing the payment info details.
         */
        do_action('geodir_save_listing_payment',$last_post_id,$prev_package_id,$package_id,$payment_info);
	}
}

add_action('geodir_after_save_listing', 'geodir_save_listing_package_fields', 20, 2);
/**
 * Save the listing price package fields values after listing saved.
 *
 * This function will checks & validates the limitations of category count, tags, 
 * post images, category exclusions, description characters length etc.
 *
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $post_id The post id.
 * @param array $request_info The listing request data.
 */
function geodir_save_listing_package_fields($post_id='', $request_info) {
	global $wpdb;
	$package_id = (isset($request_info['package_id'])) ? $request_info['package_id'] : '';
	
	if (!$post_id || !$package_id) {
		return;
	}
	
	$package_info = (array)geodir_get_package_info($package_id);
	$post_info = geodir_get_post_info($post_id);
	$post_type = $post_info->post_type;
	$post_category = $post_type.'category';
	
	// check for excluded cats
	if ($package_info['cat']) {// only run if there are excluded cats
		$cur_cats = array_unique(array_filter(explode(",", $post_info->{$post_category})));
		$ex_cats = array_filter(explode(",", $package_info['cat']));

		foreach($cur_cats as $key => $value) {
		  if(in_array($value, $ex_cats)) {  
			unset($cur_cats[$key]);
		  }
		}

		$cur_cats = array_map('intval',$cur_cats);// this was being treated as a string so we convert to int.
		$cur_cats_str = (!empty($cur_cats)) ? implode(',',$cur_cats) : '';
		$term_taxonomy_ids = wp_set_object_terms($post_id, $cur_cats,$post_category);
		geodir_save_post_meta($post_id, $post_category,$cur_cats_str );
		
		// check if defualt cat is excluded and if so chane it
		$default_cat = $post_info->default_category;
		if($default_cat && in_array($default_cat, $ex_cats)){
		geodir_save_post_meta($post_id, 'default_category', $cur_cats[0]);	
		}
	}

	// check if featured only if not in admin
	if (!is_admin()) {
		if($package_info['is_featured']!=$post_info->is_featured){
			geodir_save_post_meta($post_id, 'is_featured', $package_info['is_featured']);
		}
	}

	// check image limit
	if ($package_info['image_limit']!='') {
		$image_limit = $package_info['image_limit'];
		$post_images  = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM ".GEODIR_ATTACHMENT_TABLE." WHERE `post_id`=%d order by menu_order asc",
					array($post_id)
				)
			);

		$count_post_images = count($post_images);
		
		if ($count_post_images > $image_limit) {
			if($image_limit>='1'){
				foreach ($post_images as $key=>$perimage) {// move featured image to the end of the arr so it's not removed
					if($post_info->featured_image==$perimage->file){unset($post_images[$key]);$post_images[]=$perimage;}
				}
			}

			$post_images_arr = array_slice($post_images, 0, $count_post_images-$image_limit);

			$upload_dir = wp_upload_dir();
			$upload_basedir = $upload_dir['basedir'];
			
			foreach ($post_images_arr as $perimage) {
				
				if (file_exists($upload_basedir.$perimage->file)) {
					unlink($upload_basedir.$perimage->file);
				}
				
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM ".GEODIR_ATTACHMENT_TABLE." WHERE ID=%d",
						array($perimage->ID)
					)
				);
				if($post_info->featured_image==$perimage->file){geodir_save_post_meta($post_id, 'featured_image', '');}
			}
		}
	}

	// check if there is a category limit
	if ( $package_info['cat_limit'] != '') {
		$cur_cats = array_unique(array_filter(explode(",", $post_info->{$post_category})));
		$cat_limit = (int)$package_info['cat_limit'];
		
		if (count($cur_cats) > $cat_limit) {
			$default_category = (int)$post_info->default_category > 0 ? (int)$post_info->default_category : $cur_cats[0];

			$count = 0;
			$new_cur_cats = array();
			foreach ($cur_cats as $cat_id) {
				$new_cur_cats[] = (int)$cat_id;
				
				$count++;
				if ($count >= $cat_limit) {
					break;
				}
			}

			if ($default_category && !in_array($default_category, $new_cur_cats)) {
				$new_cur_cats[$cat_limit-1] = $default_category;
			}
			
			$cur_cats_str = (!empty($new_cur_cats)) ? implode(',',$new_cur_cats) : '';
			$term_taxonomy_ids = wp_set_object_terms($post_id, $new_cur_cats, $post_category);
			
			geodir_save_post_meta($post_id, $post_category, $cur_cats_str);
			
			$post_cat_str = '';
			if (!empty($new_cur_cats)) {
				$post_cat_str = '#'.implode(",y:#", $new_cur_cats) . ',y:';
				$post_cat_str = str_replace('#' . $default_category . ',y', '#' . $default_category . ',y,d', $post_cat_str);
				$post_cat_str = ltrim($post_cat_str, '#');
				
				$post_cat_str = array($post_category => $post_cat_str);
			}
			
			geodir_set_postcat_structure($post_id, $post_category, $default_category, $post_cat_str);
		}
	}
	
	// check custom fields
	$custom_fields = geodir_post_custom_fields('','all',$post_type);
	
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $key=>$val) {
			$is_default =  $val['is_default'];
			$is_admin =  $val['is_admin'];
			$field_type =  $val['field_type'];
			$packages = array();
			$packages = array_unique(array_filter(explode(",",$val['packages'])));
			
			if (!($field_type == 'address' && $is_admin == '1') && !($field_type == 'taxonomy' && $is_admin == '1') && $val['for_admin_use']!='1') {
				if (in_array($package_id,$packages)) { // if active for this package then dont change
				} else { // if not active in this package then blank
					geodir_save_post_meta($post_id, $val['name'],'');
				}
			}
		}
	}
}

add_action('geodir_payment_invoice_created', 'geodir_payment_detail_fields_update', 1, 1);
/**
 * Updates the invoice and post details after invoice created.
 *
 * @since 1.0.0
 *
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param int $invoice_id The payment invoice id.
 */
function geodir_payment_detail_fields_update($invoice_id) {
	global $gd_session;
	
	$invoice_info = geodir_get_invoice($invoice_id);
	
	if (!empty($invoice_info)) {
		$payment_info = array();
		$payment_info['paymentmethod'] = $invoice_info->paymentmethod;
		$payment_info['paid_amount'] = $invoice_info->paied_amount;
		
		geodir_save_post_info($invoice_info->post_id, $payment_info);
		
		$post_status = get_post_status($invoice_info->post_id);
		
		// if listing not expired and goes to upgrade listing with same package
		$update = true;
		if (!empty($_REQUEST['price_select']) && !empty($_REQUEST['paymentmethod']) && !empty($_REQUEST['pid']) && $_REQUEST['ajax_action']=='paynow') {
			$session_listing = $gd_session->get('listing');
			$prev_package_id = $gd_session->get('geodir_prev_package_id') ? $gd_session->get('geodir_prev_package_id') : geodir_get_post_meta($_REQUEST['pid'], 'package_id', true);
			$prev_expire_date = $gd_session->get('geodir_prev_expire_date') ? $gd_session->get('geodir_prev_expire_date') : geodir_get_post_meta($_REQUEST['pid'], 'expire_date', true);

			if ($prev_package_id == $_REQUEST['price_select'] && $prev_expire_date && strtotime($prev_expire_date) >= strtotime(date('Y-m-d'))) {
				$update = false;
			}
		}
		
		if (!empty($_POST['gd_pay_type']) && $_POST['gd_pay_type'] == 'renew' && $post_status != 'draft') {
			$update = false;
		}

		if ($update && $payment_info['paid_amount'] > 0) {
			$post['ID'] = $invoice_info->post_id;
			$post['post_status'] = $post_status == 'pending' ? 'pending' : 'draft';
			$last_post_id = wp_update_post( $post );
		}
	}	
}

add_action('before_delete_post','geodir_payment_delete_listing_info', 1, 2);

/**
 * Delete the invoice details for post after post deleted. 
 *
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $deleted_postid The post id requested to delete.
 * @param bool $force Force to delete post data if True, else false.
 * @return False for invalid post type.
 */
function geodir_payment_delete_listing_info($deleted_postid, $force = false) {
	global $wpdb;
	
	$post_type = get_post_type( $deleted_postid );
	
	$all_postypes = geodir_get_posttypes();

	if(!in_array($post_type, $all_postypes))
		return false;
			
	$wpdb->query($wpdb->prepare("DELETE FROM ".INVOICE_TABLE." WHERE status = 'pending' AND `post_id` = %d", array($deleted_postid)));
} 

add_action( 'add_meta_boxes', 'geodir_package_meta_box_add', 0, 2 );
/**
 * Add the price package meta box in backend add lisitng form.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_package_meta_box_add() {	
	global $post;
	
	$geodir_post_types = geodir_get_posttypes('array');
	$geodir_posttypes = array_keys($geodir_post_types);
	
	if ( isset($post->post_type) &&  in_array($post->post_type,$geodir_posttypes) ):
		$geodir_posttype = $post->post_type;
		$post_typename = geodir_ucwords($geodir_post_types[$geodir_posttype]['labels']['singular_name']);
		
		add_meta_box( 'geodir_post_package_setting', $post_typename.' Package Settings', 'geodir_post_package_setting', $geodir_posttype,'side', 'high' );
	endif;
}

/**
 * Display lisitng price package info on backend add lisitng form.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 * @global int $post_id The current post id.
 * @global int $package_id Price package of the current post.
 */
function geodir_post_package_setting(){
	global $post, $post_id, $package_id;
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_package_setting_noncename' );
	
	$package_price_info = geodir_package_list_info($post->post_type);
	
	if (isset($_REQUEST['package_id'])) {
		$package_id = $_REQUEST['package_id'];
	} elseif($post_package_id = geodir_get_post_meta($post_id,'package_id') ) {
		$package_id = $post_package_id;	
	} else {
		foreach($package_price_info as $pck_val ) {
			if ($pck_val->is_default) {
				$package_id = $pck_val->pid;
			}	
		}
	}	
	?>
	<div class="misc-pub-section" >
		<h4 style="display:inline;"><?php echo SELECT_PACKAGE_TEXT;?></h4>
		<?php 
			foreach ($package_price_info as $pkg) {

				// set package, alive days and expire and featured
				if($package_id == $pkg->pid){
					$alive_days = isset($pkg->days) ? $pkg->days : '';
					$is_featured = isset($pkg->is_featured) ? $pkg->is_featured : '0';
					$expire_date = $pkg->days > 0 ? date_i18n( 'Y-m-d', strtotime(' + ' . (int)$pkg->days . ' days')) : 'Never';
				}
				
				$post_pkg_link = get_edit_post_link( $post_id ).'&package_id='.$pkg->pid;
				?>
				<div class="gd-package" style="width:100%; margin:5px 0px;">
				<input class="gd-checkbox"  name="package_id" type="radio" value="<?php echo $pkg->pid;?>"  <?php if($package_id == $pkg->pid) echo 'checked="checked"';?> onclick="window.location.href='<?php echo $post_pkg_link;?>'">
				<?php 
				_e(stripslashes_deep($pkg->title_desc), 'geodirectory');
				?>
				</div>
			<?php } ?>	
	</div>
	<?php
	
	if (geodir_get_post_meta($post_id, 'alive_days',true) != '')
		$alive_days = geodir_get_post_meta($post_id, 'alive_days',true);
	
	if (geodir_get_post_meta($post_id, 'is_featured',true) != '')
		$is_featured = geodir_get_post_meta($post_id, 'is_featured',true);
	
	if (geodir_get_post_meta($post_id, 'expire_date',true) != '')		
		$expire_date = geodir_get_post_meta($post_id,'expire_date',true);
	?>
     <div class="misc-pub-section">
        <h4 style="display:inline;"><?php _e('Alive Days:', 'geodir_payments'); ?></h4>
        <input type="text" name="alive_days" value="<?php if(isset($alive_days)){ echo $alive_days;} else{echo '0';};?>"  />
		<br />
        <h4 style="display:inline;"><?php _e('Expire Date:', 'geodir_payments'); ?>(ie: YYYY-MM-DD)</h4>
		<input type="text" name="expire_date" value="<?php if(isset($expire_date)){ echo $expire_date;} else{echo 'Never';};?>" />
    </div>
    <div class="misc-pub-section">
        <h4 style="display:inline;"><?php _e('Is Featured:', 'geodir_payments'); ?></h4>
                <input type="radio" class="gd-checkbox" name="is_featured" id="is_featured_yes" <?php if(isset($is_featured) && $is_featured=='1' ){echo 'checked="checked"';}?>  value="1"   /> <?php _e('Yes', 'geodir_payments');?>
                <input type="radio" class="gd-checkbox" name="is_featured" id="is_featured_no" <?php if((isset($is_featured) && $is_featured=='0') || !isset($is_featured)){echo 'checked="checked"';}?> value="0"   /> <?php _e('No', 'geodir_payments');?>
    </div>
	<?php	 
}

add_filter('geodir_packages_list_on_custom_fields','geodir_pay_packages_list_on_custom_fields', 1, 2);
/**
 * Get the content for price packages list.
 *
 * Filter the price packages list in custom field form in admin
 * custom fields settings.
 *
 * @since 1.0.0
 *
 * @param string $html The price packages content.
 * @param object $field_info Custom field object.
 */
function geodir_pay_packages_list_on_custom_fields( $html, $field_info ) {
	$field_display = '';
	
	if (isset($field_info->is_admin) && $field_info->is_admin == '1' && ($field_info->field_type == 'taxonomy' || $field_info->field_type == 'address') ) {
		$field_display = 'style="display:none;"';
	}
	?>
	<li <?php echo $field_display;?> >
		<label for="admin_title" class="gd-cf-tooltip-wrap">
			<i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show only on these price packages ? :', 'geodir_payments');?>
			<div class="gdcf-tooltip">
				<?php _e('Want to show only on these price packages ? (Select multiple price packages by holding down "Ctrl" key.)', 'geodir_payments');?>
			</div>
		</label>
		<div class="gd-cf-input-wrap">
			<select name="show_on_pkg[]" id="show_on_pkg" multiple="multiple" style="height: 100px; width:90%;">
				<?php
				$priceinfo = geodir_package_list_info($_REQUEST['listing_type']);
				$pricearr = array();
				if (isset($field_info->packages) && $field_info->packages) {
					$pricearr = explode(',',$field_info->packages);
				}
				foreach ($priceinfo as $priceinfoObj) {
					?>
					<option value="<?php echo $priceinfoObj->pid; ?>" <?php if (in_array($priceinfoObj->pid, $pricearr)){ echo 'selected="selected"';}?>><?php echo '#'.$priceinfoObj->pid.': ' . __(stripslashes_deep($priceinfoObj->title), 'geodirectory');?></option>
				<?php }  ?>
			</select>
		</div>
	</li>
	<?php
}

function geodir_maybe_hide_special_offers_tab($tabs) {
	global $post, $wpdb;

	$post_type = isset($post->post_type) ? $post->post_type : '';
	if (!$post_type) { 
		$post_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';
	}
	
	if (isset($post->package_id) && (isset($tabs['special_offers']) || isset($tabs['post_video']))) {
		$results = $wpdb->get_results($wpdb->prepare("SELECT htmlvar_name, packages FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE ( htmlvar_name = %s OR htmlvar_name = %s ) AND post_type = %s", array('geodir_special_offers', 'geodir_video', $post_type)));

		$fields = array();
		if ( !empty( $results ) ) {
			foreach ( $results as $row ) {
				$packages = explode( ',', trim( $row->packages, ',' ) );
				if ( !empty( $packages ) && in_array( $post->package_id, $packages ) ) {
					$fields[$row->htmlvar_name] = 1;
				}
			}
		}

		if ( isset($tabs['special_offers']) ) {
			$tabs['special_offers']['is_display'] = $tabs['special_offers']['is_display'] && !empty($fields['geodir_special_offers']) ? 1 : 0;
		}
		if ( isset($tabs['post_video']) ) {
			$tabs['post_video']['is_display'] = $tabs['post_video']['is_display'] && !empty($fields['geodir_video']) ? 1 : 0;
		}
	}

	return $tabs;
}
add_action('geodir_detail_page_tab_list_extend','geodir_maybe_hide_special_offers_tab',10,1);


add_filter('geodir_add_custom_sort_options', 'geodir_package_add_custom_sort_options', 2, 2);
/**
 * Add the featured option in the sorting fields options.
 *
 * @since 1.0.0
 *
 * @param array  $fields Custom field sorting fields.
 * @param string $post_type The post type.
 * @return Fields array.
 */
function geodir_package_add_custom_sort_options($fields, $post_type) {
    $fields[] = array(
        'post_type' => $post_type,
        'data_type' => '',
        'field_type' => 'enum',
        'site_title' => 'Featured',
        'htmlvar_name' => 'is_featured'
    );

    return $fields;
}

/* ----------- Updated package table(new field add sendtofriend in package table) */

add_action('plugins_loaded', 'geodir_changes_in_package_table');
/**
 * Its checks and adds the new fields in price package table during plugin load.
 *
 * @since 1.0.0
 * @since 1.3.2 Modified to check & add field has_upgrades in price package table.
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_changes_in_package_table() {
    if (is_admin()) {
        global $wpdb, $plugin_prefix;

        // add fields for new payment system
        if (!get_option('geodir_changes_in_invoice_table_1_2_6')) {
            // remove google wallet payment method
            if (get_option('payment_method_googlechkout')) {
                delete_option('payment_method_googlechkout');
            }
            
            update_option('geodir_changes_in_invoice_table_1_2_6', '1');
        }
    }
}

// add a row for diagnostic too 
//add_action('geodir_diagnostic_tool' , 'geodir_add_payment_diagnostic_tool' , 1);
/**
 * Checks and fix the problem if any have in the payment methods settings.
 *
 * @since 1.0.0
 */
function geodir_add_payment_diagnostic_tool() {
?>
<tr>
    <td><?php _e('Payment methods check','geodir_payments');?></td>
    <td><small><?php _e('Checks the payment methods settings for problems.','geodir_payments');?></small></td>
    <td><input type="button" value="<?php _e('Run','geodir_payments');?>" class="button-primary geodir_diagnosis_button" data-diagnose="payment_method_options" /></td>
</tr>
<?php
}

add_action('geodir_diagnostic_tool' , 'geodir_add_expire_diagnostic_tool' , 1);
/**
 * Checks the expire date for all listings.
 *
 * @since 1.0.0
 */
function geodir_add_expire_diagnostic_tool() {
?>
<tr>
  <td><?php _e('Run expire check','geodir_payments');?></td>
  <td><small><?php _e('Manually run the expire check function.','geodir_payments');?></small></td>
  <td><input type="button" value="<?php _e('Run','geodir_payments');?>" class="button-primary geodir_diagnosis_button" data-diagnose="run_expire" />
  </td>
</tr>
<?php
}

add_action('geodir_before_description_field' , 'geodir_payments_before_description_field', 1);
/**
 * Add the content before description field in the add listing form.
 *
 * @since 1.0.0
 */
function geodir_payments_before_description_field() {
}

add_action('geodir_after_description_field' , 'geodir_payments_after_description_field', 1);
/**
 * Add the content after description field in the add listing form.
 *
 * @since 1.0.0
 */
function geodir_payments_after_description_field() {
}

add_filter('geodir_description_field_desc', 'geodir_payments_description_field_desc', 1, 2);
/**
 * Filter the add listing description field text.
 *
 * @since 1.0.0
 *
 * @param string $desc The text for the description field.
 * @param int $desc_count The character limit number if any.
 * @return string The description text.
 */
function geodir_payments_description_field_desc($desc, $desc_count) {
	$desc = trim($desc);
	if (is_int($desc_count)) {
		$desc = geodir_excerpt($desc, (int)$desc_count);
	}
	return $desc;
}

/**
 * Checks the description text limit enabled or not for package.
 *
 * @since 1.0.0
 *
 * @param object $package_info Price package info.
 * @return True if limit enabled, otherwise False.
 */
function geodir_payments_desc_limit_enabled($package_info) {
	if (!empty($package_info) && is_object($package_info) && isset($package_info->use_desc_limit) && $package_info->use_desc_limit==1) {
		return true;
	}
	return false;
}

/**
 * Get the description text limit for the package.
 *
 * @since 1.0.0
 *
 * @param object $package_info Price package info.
 * @return Length limit for description.
 */
function geodir_payments_get_desc_limit($package_info) {
	$desc_limit_enabled = geodir_payments_desc_limit_enabled($package_info);
	$desc_limit = '';
	if ($desc_limit_enabled) {
		$desc_limit = (int)$package_info->desc_limit;
	}
	return $desc_limit;
}

add_filter('geodir_description_field_desc_limit', 'geodir_payments_description_field_desc_limit', 1, 2);
/**
 * Filter the add listing description field character limit.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param int $desc_limit The amount of characters to limit the description to.
 * @return Description characters limit.
 */
function geodir_payments_description_field_desc_limit($desc_count) {
	global $post;
	$package_info = geodir_post_package_info(array(), $post);
	$desc_limit_enabled = geodir_payments_desc_limit_enabled($package_info);
	$desc_limit = geodir_payments_get_desc_limit($package_info);
	if ($desc_limit_enabled) {
		$desc_count = $desc_limit;
	}
	return $desc_count;
}

add_filter('geodir_description_field_desc_limit_msg', 'geodir_payments_description_field_desc_limit_msg', 1, 2);
/**
 * Filter the listing description limit message.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $desc_msg The limit message string if any.
 * @param int $desc_count The character limit number if any.
 * @return Description limit message if any.
 */
function geodir_payments_description_field_desc_limit_msg($desc_msg, $desc_count) {
	global $post;
	$package_info = geodir_post_package_info(array(), $post);
	$desc_limit_enabled = geodir_payments_desc_limit_enabled($package_info);
	if ($desc_limit_enabled) {
		if ((int)$desc_count>0) {
			$desc_msg = __('For description you can use up to %d characters only for this package.', 'geodir_payments');
			if (strpos($desc_msg, '%d')!==false) {
				$desc_msg = sprintf($desc_msg, $desc_count);
			}
		} else {
			$desc_msg = __('You can not add description for this package.', 'geodir_payments');
		}
		add_filter( 'tiny_mce_before_init', 'geodir_payments_add_idle_function_to_tinymce' );
	}
	return $desc_msg;
}

/**
 * Filter the tinymce editor settings array.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param array $initArray Tinymce editor settings array.
 * @return Tinymce settings array.
 */
function geodir_payments_add_idle_function_to_tinymce( $initArray ) {
	global $post;
	$package_info = geodir_post_package_info(array(), $post);
	$desc_limit_enabled = geodir_payments_desc_limit_enabled($package_info);
	if ($desc_limit_enabled) {
		$desc_limit = (int)geodir_payments_get_desc_limit($package_info);
		$desc_msg = geodir_payments_description_field_desc_limit_msg('', $desc_limit);
		if (isset($initArray['selector']) && $initArray['selector']=='#post_desc') {
            $initArray['setup'] = 'function(ed) {  ed.on("KeyUp", function(e) {ob= this;var content = ob.getContent(); if (ob.id=="post_desc") { var re = /(<([^>]+)>)/ig; plaintext = content.replace(re, ""); cnt=plaintext.length; if (cnt>parseInt('.(int)$desc_limit.')) { alert("'.addslashes($desc_msg).'"); plaintext=plaintext.substring(0, '.(int)$desc_limit.'); ob.setContent(plaintext); } } }) }';
		}
	}
	return $initArray;
}

add_action('geodir_before_listing_tags_field' , 'geodir_payments_before_listing_tags_field', 1);
/**
 * Add the content before tags field in add lisitng form.
 *
 * @since 1.0.0
 */
function geodir_payments_before_listing_tags_field() {
}

add_action('geodir_after_listing_tags_field' , 'geodir_payments_after_listing_tags_field', 1);
/**
 * Add the content after tags field in add lisitng form.
 *
 * @since 1.0.0
 */
function geodir_payments_after_listing_tags_field() {
}

add_filter('geodir_listing_tags_field_tags', 'geodir_payments_listing_tags_field_tags', 1, 2);
/**
 * Filter the add listing tags field text.
 *
 * @since 1.0.0
 *
 * @param string $tags The text for the description field.
 * @param int $tags_count The character limit number if any.
 * @return string Listing tags.
 */
function geodir_payments_listing_tags_field_tags($tags, $tags_count) {
	$tags = trim($tags);
	if (is_int($tags_count)) {
		$tags = geodir_excerpt($tags, (int)$tags_count);
	}
	return $tags;
}

/**
 * Checks the tags text limit enabled or not for package.
 *
 * @since 1.0.0
 *
 * @param object $package_info Price package info.
 * @return True if tag limit enabled, otherwise False.
 */
function geodir_payments_tag_limit_enabled($package_info) {
	if (!empty($package_info) && is_object($package_info) && isset($package_info->use_tag_limit) && $package_info->use_tag_limit==1) {
		return true;
	}
	return false;
}

/**
 * Get the tags text limit for the package.
 *
 * @since 1.0.0
 *
 * @param object $package_info Price package info.
 * @return Character limit for tags.
 */
function geodir_payments_get_tag_limit($package_info) {
	$tag_limit_enabled = geodir_payments_tag_limit_enabled($package_info);
	$tag_limit = '';
	if ($tag_limit_enabled) {
		$tag_limit = (int)$package_info->tag_limit;
	}
	return $tag_limit;
}

add_filter('geodir_listing_tags_field_tags_count', 'geodir_payments_listing_tags_field_tags_count', 1, 2);
/**
 * Filter the add listing tags field character limit.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param int $tags_count The amount of characters to limit the tags to.
 * @return Tags characters limit.
 */
function geodir_payments_listing_tags_field_tags_count($tags_count) {
	global $post;
	$package_info = geodir_post_package_info(array(), $post);
	$tag_limit_enabled = geodir_payments_tag_limit_enabled($package_info);
	$tag_limit = geodir_payments_get_tag_limit($package_info);
	if ($tag_limit_enabled) {
		$tags_count = $tag_limit;
	}
	return $tags_count;
}

add_filter('geodir_listing_tags_field_tags_msg', 'geodir_payments_listing_tags_field_tags_msg', 1, 2);
/**
 * Filter the tags description limit message.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $desc_msg The limit message string if any.
 * @param int $desc_count The character limit number if any.
 * @return Tags limit message if any.
 */
function geodir_payments_listing_tags_field_tags_msg($tags_msg, $tags_count) {
	if (!is_int($tags_count)) {
		$tags_msg = __('Tags are short keywords, with no space within.(eg: tag1, tag2, tag3).', 'geodir_payments');
	} else {
		if ($tags_count>0) {
			$tags_msg = __('Tags are short keywords, with no space within.(eg: tag1, tag2, tag3) Up to %d characters only for this package.', 'geodir_payments');
			if (strpos($tags_msg, '%d')!==false) {
				$tags_msg = sprintf($tags_msg, $tags_count);
			}
		} else {
			$tags_msg = __('Tags are short keywords, currently tags not allowed for this package.', 'geodir_payments');
		}
	}
	return $tags_msg;
}

add_filter('geodir_action_details_post_tags', 'geodir_payments_action_details_post_tags', 1, 2);
/**
 * Filter the post tags.
 *
 * Allows you to filter the post tags output on the details page of a post.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $post_tags A comma seperated list of tags.
 * @param int $post_id The current post id.
 */
function geodir_payments_action_details_post_tags($post_tags, $post_id) {
	global $post;
	$package_info = geodir_post_package_info(array(), $post);
	$tag_limit_enabled = geodir_payments_tag_limit_enabled($package_info);
	$tag_limit = geodir_payments_get_tag_limit($package_info);
	
	if (!empty($post) && is_object($post) && ((!empty($post_id) && isset($post->ID) && $post->ID==$post_id) || isset($post->preview))&& isset($post->post_tags) && $tag_limit_enabled) {
		$post_tags = $post->post_tags;
		$post_tags = geodir_excerpt($post_tags, (int)$tag_limit);
		$post->post_tags = $post_tags;
	}
	return $post_tags;
}

add_filter('geodir_listinginfo_request', 'geodir_payments_listinginfo_request', 1, 2);
/**
 * Filter to change listing info.
 *
 * @since 1.0.0
 *
 * @param array $postinfo_array See {@see geodir_save_post_info()} for accepted args.
 * @param int $post_id The post ID.
 * @return Lisitng info array.
 */
function geodir_payments_listinginfo_request($postinfo_array, $post_id) {
	if (is_admin()) {
		return $postinfo_array;
	}
	$package_info = geodir_post_package_info(array(), $postinfo_array);
	$tag_limit_enabled = geodir_payments_tag_limit_enabled($package_info);
	
	if ($tag_limit_enabled && isset($postinfo_array['post_tags'])) {
		$tag_limit = geodir_payments_get_tag_limit($package_info);
		if ($tag_limit>0) {
			$post_tags = $postinfo_array['post_tags'];
			$post_tags = geodir_excerpt($post_tags, (int)$tag_limit);
		} else {
			$post_tags = '';
		}
		$postinfo_array['post_tags'] = $post_tags;
	}
	return $postinfo_array;
}

add_filter('geodir_action_get_request_info', 'geodir_payments_action_get_request_info', 1, 2);
/**
 * Filter the listing request info array.
 *
 * @since 1.0.0
 *
 * @param array $request_info See {@see geodir_save_listing()} for accepted args.
 * @return Lisitng request info array.
 */
function geodir_payments_action_get_request_info($request_info) {
	$geodir_ajax = isset($request_info['geodir_ajax']) && $request_info['geodir_ajax']=='add_listing' ? true : false;
	if (!$geodir_ajax) {
		return $request_info;
	}
	$package_info = geodir_post_package_info(array(), $request_info);
	$desc_limit_enabled = geodir_payments_desc_limit_enabled($package_info);
	$tag_limit_enabled = geodir_payments_tag_limit_enabled($package_info);
	
	if ($desc_limit_enabled && isset($request_info['post_desc'])) {
		$desc_limit = geodir_payments_get_desc_limit($package_info);
		$post_desc = $request_info['post_desc'];
		$post_desc = geodir_excerpt($post_desc, (int)$desc_limit);
		$request_info['post_desc'] = $post_desc;
	}

	if ($tag_limit_enabled && isset($request_info['post_tags'])) {
		$tag_limit = geodir_payments_get_tag_limit($package_info);
		if ($tag_limit>0) {
			$post_tags = $request_info['post_tags'];
			$post_tags = geodir_excerpt($post_tags, (int)$tag_limit);
		} else {
			$post_tags = '';
		}
		$request_info['post_tags'] = $post_tags;
	}
	return $request_info;
}

add_filter( 'the_content', 'geodir_payments_the_content', 99);
/**
 * Filter the listing content.
 *
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $post_desc Post content text.
 * @retrun Post content.
 */
function geodir_payments_the_content($post_desc) {
	global $post;
	if (is_admin() || empty($post)) {
		return $post_desc;
	}
	
	$post_type = '';
	if (!empty($post->ID)) {
		$post_type = get_post_type($post->ID);
	} else if (!empty($post->pid)) {
		$post_type = get_post_type($post->pid);
	} else if (!empty($post->post_type)) {
		$post_type = $post->post_type;
	} else if (!empty($post->listing_type)) {
		$post_type = $post->listing_type;
	} else if (!empty($_REQUEST['listing_type'])) {
		$post_type = sanitize_text_field($_REQUEST['listing_type']);
	}
	
	if (!($post_type && in_array($post_type, geodir_get_posttypes()))) {
		return $post_desc;
	}
	
	if(is_object($post) && isset($post->ID) && !empty($post->geodir_video) ){
		if (strpos($post_desc,$post->geodir_video) !== false) {
			   return $post_desc;
		}
	}
	
	$package_info = geodir_post_package_info(array(), $post);
	$desc_limit_enabled = geodir_payments_desc_limit_enabled($package_info);
	
	if (is_object($post) && (isset($post->ID) || (!isset($post->ID) && isset($post->preview))) && $post_desc!='' && $desc_limit_enabled) {
		$desc_limit = geodir_payments_get_desc_limit($package_info);
		$post_desc = geodir_excerpt($post_desc, (int)$desc_limit);
		return $post_desc;
	}
	return $post_desc;
}

/* add class for listing row */
add_filter( 'geodir_post_view_extra_class', 'geodir_payment_post_view_extra_class' );

add_filter( 'geodir_detail_page_tab_is_display', 'geodir_payment_related_listing_is_display', 9999, 2 );


/**
 * Check and add the payment checkout page if not exists.
 *
 * @since 1.2.6
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_diagnose_checkout_page($page_chk_arr) {
    global $wpdb;

    $fix = isset($_POST['fix']) ? true : false;
    $output_str = $page_chk_arr['output_str'];
    $is_error_during_diagnose = $page_chk_arr['is_error_during_diagnose'];

    //////////////////////////////////
    /* Diagnose GD Checkout Starts */
    //////////////////////////////////
    $option_value = get_option('geodir_checkout_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('GD Checkout page exists with proper setting.', 'geodir_payments') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('GD Checkout page is missing.', 'geodir_payments') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-checkout', __('GD Checkout', 'geodir_payments'), $page_found, 'geodir_checkout_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: GD Checkout page fixed', 'geodir_payments') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: GD Checkout page fix failed', 'geodir_payments') . "</strong></li>";
            }
        }
    }

    return array('output_str'=>$output_str,'is_error_during_diagnose'=>$is_error_during_diagnose );

}
add_filter('geodir_diagnose_default_pages','geodir_diagnose_checkout_page',10,1);


/**
 * Check and add the payment invoices page if not exists.
 *
 * @since 1.2.6
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_diagnose_invoices_page($page_chk_arr) {
    global $wpdb;
    $fix = isset($_POST['fix']) ? true : false;
    $output_str = $page_chk_arr['output_str'];
    $is_error_during_diagnose = $page_chk_arr['is_error_during_diagnose'];

    //////////////////////////////////
    /* Diagnose GD Invoices Starts */
    //////////////////////////////////
    $option_value = get_option('geodir_invoices_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Manage Invoices page exists with proper setting.', 'geodir_payments') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Manage Invoices page is missing.', 'geodir_payments') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-invoices', __('Manage Invoices', 'geodir_payments'), $page_found, 'geodir_invoices_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Manage Invoices page fixed', 'geodir_payments') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Manage Invoices page fix failed', 'geodir_payments') . "</strong></li>";
            }
        }
    }

    return array('output_str'=>$output_str,'is_error_during_diagnose'=>$is_error_during_diagnose );
}
add_filter('geodir_diagnose_default_pages','geodir_diagnose_invoices_page',10,1);


add_action( 'geodir_payment_form_fields_paypal', 'geodir_payment_form_fields_paypal' );
/**
 * Add the paypal form fields in the paypal gateway form.
 *
 * @since 1.2.6
 * @since 1.3.6 Fixed coupon applyied for recurring price package.  
 *
 * @param int $invoice_id Payment invoice id.
 */
function geodir_payment_form_fields_paypal( $invoice_id ) {
	$invoice_info = geodir_get_invoice( $invoice_id );
	
	$subscription = '';
	
	if ( !empty( $invoice_info ) ) {
		$invoice_type = $invoice_info->invoice_type;
		$post_id = $invoice_info->post_id;
		$package_id = $invoice_info->package_id;
		$payable_amount = $invoice_info->paied_amount;
		$amount_ex_discount = ( $invoice_info->amount + $invoice_info->tax_amount ); // Amount + Tax only
		
		if ( $invoice_type == 'add_listing' || $invoice_type == 'upgrade_listing' || $invoice_type == 'renew_listing' ) {
			$package_info = geodir_get_post_package_info( $package_id, $post_id );
			
			/* PAYPAL RECURRING CODE */
			$is_subscription = !empty( $package_info['sub_active'] ) ? true : false;
			if ( $is_subscription ) {
				$subscription = '-subscriptions';
				$sub_units = $package_info['sub_units'];
				$sub_units_num = $package_info['sub_units_num'];
				$sub_units_num_times = $package_info['sub_units_num_times'];			
				$sub_num_trial_days = (int)$package_info['sub_num_trial_days'];
				$sub_num_trial_units = !empty( $package_info['sub_num_trial_units'] ) ? $package_info['sub_num_trial_units'] : 'D';
				
				$post_type = geodir_payment_cart_post_type( $invoice_id );
				$coupon_code = $invoice_info->coupon_code;
				
				if ( $sub_num_trial_days > 0 ) {
				?>
					<input type="hidden" value="0" name="a1" />
					<input type="hidden" value="<?php echo $sub_num_trial_days;?>" name="p1" />
					<input type="hidden" value="<?php echo $sub_num_trial_units;?>" name="t1" />
				<?php 
				}
				
				if ( $coupon_code != '' && geodir_is_valid_coupon( $post_type, $coupon_code ) && geodir_payment_coupon_is_recurring( $coupon_code ) ) {
				?>
					<input type="hidden" value="<?php echo $payable_amount;?>" name="a1" />
					<input type="hidden" value="<?php echo $sub_units_num;?>" name="p1" />
					<input type="hidden" value="<?php echo $sub_units;?>" name="t1" />
				<?php
					$payable_amount = $amount_ex_discount;
				}
				?>
				<input type="hidden" value="<?php echo $payable_amount;?>" name="a3" />
				<input type="hidden" value="<?php echo $sub_units_num;?>" name="p3" />
				<input type="hidden" value="<?php echo $sub_units;?>" name="t3" />
				<input type="hidden" value="1" name="src">
				<input type="hidden" value="2" name="rm">
				<?php if ( $sub_units_num_times > 0 ) { ?>
					<input type="hidden" name="srt" value="<?php echo $sub_units_num_times;?>" />
				<?php
				}
			}
		}
	}
	?>
	<input type="hidden" name="cmd" value="_xclick<?php echo $subscription;?>" />
	<?php
}

add_action( 'geodir_invoices_page_content', 'geodir_payment_invoices_page_content' );
add_action( 'geodir_invoice_detail_page_content', 'geodir_payment_invoice_detail_page_content' );
add_action( 'geodir_dashboard_links', 'geodir_payment_invoices_list_page_link' );
add_filter( 'geodir_payment_invoice_pay_links', 'geodir_payment_invoice_pay_links', 10, 2 );
add_action( 'geodir_invoice_detail_before_page_content', 'geodir_payment_invoice_detail_page_title', 10 );

add_action('admin_init','geodir_create_payment_pages');
function geodir_create_payment_pages() {
    if (!get_option('geodir_payment_pages_installed')) {
        geodir_create_page(esc_sql(_x('gd-checkout', 'page_slug', 'geodir_payments')), 'geodir_checkout_page', __('GD Checkout', 'geodir_payments'), '');
        geodir_create_page(esc_sql(_x('gd-invoices', 'page_slug', 'geodir_payments')), 'geodir_invoices_page', __('Manage Invoices', 'geodir_payments'), '');
        
        update_option('geodir_payment_pages_installed', true);
    }
}
add_filter('geodir_load_db_language', 'geodir_payment_db_text_translation', 10, 1);
add_filter('geodir_gd_options_for_translation', 'geodir_payment_settings_for_translation', 10, 1);
add_action('geodir_after_detail_page_more_info', 'geodir_payment_sidebar_show_send_to_friend', 11);

/**
 * Renew the free package listing.
 *
 * @since 1.4.3
 *
 * @param int $post_id The post id.
 * @param int|null $prev_package_id The price package the post was on.
 * @param int|null $package_id The price package of the post.
 * @param array $payment_info An array containing the payment info details.
 */
function geodir_payment_renew_free_listing($post_id, $prev_package_id, $package_id, $payment_info) {   
    if (!(!empty($_REQUEST['geodir_ajax']) && $_REQUEST['geodir_ajax'] == 'add_listing' && !empty($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'update')) {
        return;
    }
    
    if ($package_id > 0 && $prev_package_id == $package_id) { // Renew / Upgrade
        $expire_date = geodir_get_post_meta($post_id, 'expire_date', true);
        $package_info = geodir_get_package_info($package_id);
        
        if (!empty($package_info) && !((float)$package_info->amount > 0)) { // Free
            $post_status = get_post_status($post_id);
            $today = date_i18n('Y-m-d', current_time('timestamp'));
            $preexpiry_notice = false;
            
            if (geodir_payment_preexpiry_notice_is_active() && geodir_payment_preexpiry_notice_days() > 0 && $expire_date != '0000-00-00' && $expire_date != '' && geodir_strtolower($expire_date) != 'never' && strtotime($expire_date) >= strtotime($today)) {
                $preexpiry_date = strtotime($expire_date) - (DAY_IN_SECONDS * geodir_payment_preexpiry_notice_days());
                $preexpiry_notice = $preexpiry_date <= strtotime($today) ? true : false;
            }
            
            if ($post_status != 'publish' || $preexpiry_notice) {
                if ($post_status != 'publish') {  // Renew draft listing
                    geodir_set_post_status($post_id, 'publish');
                } else if ($preexpiry_notice && !empty($_POST['gd_pay_type']) && $_POST['gd_pay_type'] == 'renew') { // Renew active listing
                    $expire_date = $package_info->days > 0 ? date_i18n( 'Y-m-d', strtotime($expire_date . ' + ' . (int)$package_info->days . ' days')) : 'Never';
                    
                    geodir_save_post_meta( $post_id, 'alive_days', (int)$package_info->days);
                    geodir_save_post_meta( $post_id, 'expire_date', $expire_date);
                }
            }
        }
    }
}
add_action( 'geodir_save_listing_payment', 'geodir_payment_renew_free_listing', 100, 4 );

/**
 * Check the editor for listing description allowed or not package.
 *
 * @since 2.0.0
 *
 * @param bool $show_editor If true the editor will be available for description field.
 * @param object $package_info The listing package.
 * @param string $listing_type The current post type.
 * @param object $post The current post object.
 * @return bool True if editor allowed else False.
 */
function geodir_payment_description_show_editor($show_editor, $package_info, $listing_type, $post) {
    if (!empty($package_info->disable_editor)) {
        $show_editor = false;
    }
    return $show_editor;
}
add_filter('geodir_description_field_show_editor', 'geodir_payment_description_show_editor', 10, 4);

function geodir_payment_load_invoicing_functions() {
    if ( defined( 'WPINV_VERSION' ) && version_compare( WPINV_VERSION, '1.0.0', '>=' ) ) {
        /**
         * Includes the invoicing related hooks.
         *
         * @since 2.0.32
         */
        include_once( 'includes/geodir-invoicing-hooks.php' );
        /**
         * Includes the invoicing related functions.
         *
         * @since 2.0.32
         */
        include_once( 'includes/geodir-invoicing-functions.php' );
    }
}
add_action( 'wpinv_loaded', 'geodir_payment_load_invoicing_functions' );