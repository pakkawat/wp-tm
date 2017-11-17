<?php
// GLOBAL
function geodir_payment_form_handler_global( $invoice_id ) {
    // Clear cart
    geodir_payment_clear_cart();

    $invoice_info = geodir_get_invoice( $invoice_id );
            
    if ( !empty( $invoice_info ) && !empty( $invoice_info->paymentmethod ) && !empty( $invoice_info->post_id ) ) {
        geodir_save_post_meta( (int)$invoice_info->post_id, 'paymentmethod', $invoice_info->paymentmethod );
    }
}
add_action( 'geodir_payment_form_handler_global' , 'geodir_payment_form_handler_global' );

// PAYPAL
function geodir_payment_form_paypal( $invoice_id ) {
	$invoice_info = geodir_get_invoice( $invoice_id );
	$paymentmethod = get_payment_options( $invoice_info->paymentmethod );
	
	$currency_code = geodir_get_currency_type();
	
	$user_id = $invoice_info->user_id;
	$post_id = $invoice_info->post_id;
	$item_name = $invoice_info->post_title;
	$merchantid = $paymentmethod['merchantid'];
	$paymode = $paymentmethod['payment_mode'];


    $return_url = geodir_info_url(array('pay_action'=>'return','pmethod'=>'paypal','pid'=>$post_id,'inv'=>$invoice_id));
    $cancel_return = geodir_info_url(array('pay_action'=>'cancel','pmethod'=>'paypal','pid'=>$post_id,'inv'=>$invoice_id));
    $notify_url = geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'paypal'));


	
	$item_name = apply_filters( 'geodir_paypal_item_name', home_url( '/' ) . ' - ' . $item_name, $invoice_id );
	
	if ( $paymode =='sandbox' ) {
		$action = 'https://www.sandbox.paypal.com/us/cgi-bin/webscr';
	} else {
		$action = 'https://www.paypal.com/cgi-bin/webscr';
	}
	?>
	<form name="frm_payment_method" action="<?php echo $action;?>" method="post">
		<input type="hidden" name="business" value="<?php echo $merchantid;?>" />
		<input type="hidden" name="item_name" value="<?php echo esc_attr( $item_name );?>" />
		<input type="hidden" name="amount" value="<?php echo $invoice_info->paied_amount;?>" />
		<input type="hidden" name="currency_code" value="<?php echo $currency_code;?>" />
		<input type="hidden" name="no_note" value="1" />
		<input type="hidden" name="no_shipping" value="1" />
		<input type="hidden" name="custom" value="<?php echo $invoice_id;?>" />
		<input type="hidden" name="notify_url" value="<?php echo $notify_url;?>" />
		<input type="hidden" name="return" value="<?php echo $return_url;?>" />
		<input type="hidden" name="cancel_return" value="<?php echo $cancel_return;?>" />
        <input type="hidden" name="charset" value="<?php echo get_bloginfo( 'charset' );?>" />
		<?php do_action( 'geodir_payment_form_fields_paypal', $invoice_id ); ?>
	</form>
	<div class="wrapper">
		<div class="clearfix container_message">
			<center><h1 class="head2"><?php echo PAYPAL_MSG; ?></h1></center>
		</div>
	</div>
	<script type="text/javascript">setTimeout("document.frm_payment_method.submit()",50);</script>
	<?php
	exit;
}
add_action( 'geodir_payment_form_handler_paypal' , 'geodir_payment_form_paypal' );



// AUTHORIZENET
function geodir_payment_form_authorizenet( $invoice_id ) {
	global $current_user, $gd_session;
	
	$invoice_info = geodir_get_invoice( $invoice_id );
	$paymentmethod = get_payment_options( $invoice_info->paymentmethod );
	
	$currency_code = geodir_get_currency_type();
	
	$user_id = $invoice_info->user_id;
	$post_id = $invoice_info->post_id;
	$item_name = $invoice_info->post_title;
	$item_name = apply_filters( 'geodir_authorizenet_item_name', $item_name, $invoice_id );
	
	$payable_amount = $invoice_info->paied_amount;

	$sandbox = $paymentmethod['payment_mode'] == 'sandbox' ? true : false;
	$loginid = $paymentmethod['loginid'];
	$transkey = $paymentmethod['transkey'];
	
	$display_name = geodir_get_client_name($user_id);
	$user_email = $current_user->data->user_email;
	$user_phone = isset($current_user->data->user_phone) ? $current_user->data->user_phone : '';
	
	$cc_number = isset($_REQUEST['cc_number']) ? sanitize_text_field($_REQUEST['cc_number']) : '';
	$cc_month = isset($_REQUEST['cc_month']) ? sanitize_text_field($_REQUEST['cc_month']) : '';
	$cc_year = isset($_REQUEST['cc_year']) ? sanitize_text_field($_REQUEST['cc_year']) : '';
	$cv2 = isset($_REQUEST['cv2']) ? sanitize_text_field($_REQUEST['cv2']) : '';
	
	$x_card_num = $cc_number;
	$x_exp_date = $cc_month . substr( $cc_year, 2, strlen( $cc_year ) );
	$x_card_code = $cv2;
		
	require_once('authorizenet/authorizenet.class.php');
	
	$a = new authorizenet_class;
	if ($sandbox) {
		$a->is_sandbox(); // put api in sandbox mode
	}
	
	/*You login using your login, login and tran_key, or login and password.  It
	varies depending on how your account is setup.
	I believe the currently recommended method is to use a tran_key and not
	your account password.  See the AIM documentation for additional information.*/	
	$a->add_field('x_login', $loginid);
	$a->add_field('x_tran_key', $transkey);
	//$a->add_field('x_password', 'CHANGE THIS TO YOUR PASSWORD');
	
	$a->add_field('x_version', '3.1');
	$a->add_field('x_type', 'AUTH_CAPTURE');
	//$a->add_field('x_test_request', 'TRUE');     Just a test transaction
	$a->add_field('x_relay_response', 'FALSE');
	
	/*
	You *MUST* specify '|' as the delim char due to the way I wrote the class.
	I will change this in future versions should I have time.  But for now, just
	 make sure you include the following 3 lines of code when using this class.
	*/	
	$a->add_field('x_delim_data', 'TRUE');
	$a->add_field('x_delim_char', '|');     
	$a->add_field('x_encap_char', '');
	
	/*
	Setup fields for customer information.  This would typically come from an
	array of POST values from a secure HTTPS form.
	*/	
	$a->add_field('x_first_name', $display_name);
	$a->add_field('x_last_name', '');
	/*
	$a->add_field('x_address', $address);
	$a->add_field('x_city', $userInfo['user_city']);
	$a->add_field('x_state', $userInfo['user_state']);
	$a->add_field('x_zip', $userInfo['user_postalcode']);
	$a->add_field('x_country', 'US');
	$a->add_field('x_country',  $userInfo['user_country']);
	*/
	$a->add_field('x_email', $user_email);
	$a->add_field('x_phone', $user_phone);
	
	/* Using credit card number '4007000000027' performs a successful test.  This
	 allows you to test the behavior of your script should the transaction be
	 successful.  If you want to test various failures, use '4222222222222' as
	 the credit card number and set the x_amount field to the value of the
	 Response Reason Code you want to test. 
	
	 For example, if you are checking for an invalid expiration date on the
	 card, you would have a condition such as:
	 if ($a->response['Response Reason Code'] == 7) ... (do something)
	
	 Now, in order to cause the gateway to induce that error, you would have to
	 set x_card_num = '4222222222222' and x_amount = '7.00'
	
	  Setup fields for payment information*/
	//$a->add_field('x_method', $_REQUEST['cc_type']);
	$a->add_field('x_method', 'CC');
	$a->add_field('x_card_num', $x_card_num);
	/*
	$a->add_field('x_card_num', '4007000000027');   // test successful visa
	$a->add_field('x_card_num', '370000000000002');   // test successful american express
	$a->add_field('x_card_num', '6011000000000012');  // test successful discover
	$a->add_field('x_card_num', '5424000000000015');  // test successful mastercard
	$a->add_field('x_card_num', '4222222222222');    // test failure card number
	*/
	$a->add_field('x_amount', $payable_amount);
	$a->add_field('x_exp_date', $x_exp_date);    /* march of 2008*/
	$a->add_field('x_card_code', $x_card_code);    // Card CAVV Security code
	
	/* Process the payment and output the results */
	$success = false;
	$message = '';
	$response_code = $a->process();

	switch ($response_code) {
		case 1:  /* Success */
			$success = true;

			$transaction_details = '';
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= sprintf(__("Payment Details for Invoice ID #%s", 'geodir_payments'), geodir_payment_invoice_id_formatted($invoice_id) ) ."<br />";
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= sprintf(__("Item Name: %s", 'geodir_payments'), $item_name)."<br />";
			$transaction_details .= "--------------------------------------------------<br />";
			$transaction_details .= sprintf(__("Trans ID: %s", 'geodir_payments'), $a->response['Transaction ID'])."<br />";
			$transaction_details .= sprintf(__("Status: %s", 'geodir_payments'), $a->response['Response Code'])."<br />";
			$transaction_details .= sprintf(__("Amount: %s", 'geodir_payments'),$a->response['Amount'])."<br />";
			$transaction_details .= sprintf(__("Type: %s", 'geodir_payments'),$a->response['Transaction Type'])."<br />";
			$transaction_details .= sprintf(__("Date: %s", 'geodir_payments'), date_i18n("F j, Y, g:i a", current_time( 'timestamp' )))."<br />";
			$transaction_details .= sprintf(__("Method: %s", 'geodir_payments'), 'Authorize.net')."<br />";
			$transaction_details .= "--------------------------------------------------<br />";	
			
			/*############ SET THE INVOICE STATUS START ############*/
			// update invoice status and transaction details
			geodir_update_invoice_status( $invoice_id, 'confirmed' );
			geodir_update_invoice_transaction_details( $invoice_id, $transaction_details );
			/*############ SET THE INVOICE STATUS END ############*/
			
			// send notification to admin
			geodir_payment_adminEmail( $post_id, $user_id, 'payment_success', $transaction_details );
			
			// send notification to client
			geodir_payment_clientEmail( $post_id, $user_id, 'payment_success', $transaction_details );


            $redirect_url =geodir_info_url(  array( 'pay_action' => 'success', 'inv' => $invoice_id, 'pid' => $post_id ) );
			wp_redirect( $redirect_url );
			exit;
		break;
		case 2:  /* Declined */
			$message = $a->get_response_reason_text();
			
			// update invoice status
			geodir_update_invoice_status( $invoice_id, 'cancelled' );
		break;
		case 3:  /* Error */
			$message = $a->get_response_reason_text();
			
			// update invoice status
			geodir_update_invoice_status( $invoice_id, 'failed' );
		break;
	}

	if ( !$success ) {
		$gd_session->set('display_message', $message);

        $redirect_url = geodir_info_url(  array( 'pay_action' => 'cancel', 'inv' => $invoice_id, 'pmethod' => 'authorizenet', 'err_msg' => urlencode( $message ) ));

        wp_redirect( $redirect_url );
	}
	exit;
}
add_action( 'geodir_payment_form_handler_authorizenet' , 'geodir_payment_form_authorizenet' );

// WORLDPAY
function geodir_payment_form_worldpay( $invoice_id ) {
	$invoice_info = geodir_get_invoice( $invoice_id ); 
	$paymentmethod = get_payment_options( $invoice_info->paymentmethod );
	$sandbox = $paymentmethod['payment_mode'] == 'sandbox' ? true : false;
    $ipn_url = geodir_info_url(  array( 'pay_action' => 'ipn', 'pmethod' => 'worldpay'));


    $user_id = $invoice_info->user_id;
	$post_id = $invoice_info->post_id;
	$item_name = $invoice_info->post_title;
	$item_name = apply_filters( 'geodir_worldpay_item_name', $item_name, $invoice_id );
	$payable_amount = $invoice_info->paied_amount;
	
	$client_name = geodir_get_client_name( $user_id );
	$client_email = geodir_payment_get_client_email( $user_id );
	
	$currency_code = geodir_get_currency_type();
							
	$instId = $paymentmethod['instId'];
	$accId1 = $paymentmethod['accId1'];
	$cartId = $invoice_id;
	$desc = $item_name;
	$currency = $currency_code;
	$amount = $payable_amount;
	
	$action_url = $sandbox ? 'https://secure-test.worldpay.com/wcc/purchase' : 'https://secure.worldpay.com/wcc/purchase';
	$testMode = $sandbox ? 100 : 0;
	?>
	<form action="<?php echo $action_url;?>" name="frm_payment_method" method="POST">
	  <input type="hidden" name="instId"  value="<?php echo $instId;?>">
	  <input type="hidden" name="cartId" value="<?php echo $cartId;?>" />
	  <input type="hidden" name="currency" value="<?php echo $currency;?>" />
	  <input type="hidden" name="amount"  value="<?php echo $amount;?>" />
	  <input type="hidden" name="desc" value="<?php echo esc_attr( $desc );?>" />
	  <input type="hidden" name="name" value="<?php echo esc_attr( $client_name );?>" />
	  <input type="hidden" name="email" value="<?php echo esc_attr( $client_email );?>" />
	  <input type="hidden" name="MC_callback" value="<?php echo $ipn_url;?>"> 
	  <input type="hidden" name="testMode" value="<?php echo $testMode;?>" />
	</form>
	<div class="wrapper">
		<div class="clearfix container_message">
			<h1 class="head2"><?php echo WORLD_PAY_MSG; ?></h1>
		</div>
	</div>
	<script type="text/javascript">setTimeout("document.frm_payment_method.submit()",50);</script>
	<?php
	exit;
}
add_action( 'geodir_payment_form_handler_worldpay' , 'geodir_payment_form_worldpay' );

// 2CO
function geodir_payment_form_2co( $invoice_id ) {
	$invoice_info = geodir_get_invoice( $invoice_id ); 
	$payment_method = get_payment_options( $invoice_info->paymentmethod );
	$sandbox = $payment_method['payment_mode'] == 'sandbox' ? true : false;
	
	$user_id = $invoice_info->user_id;
	$post_id = $invoice_info->post_id;
	$package_id = $invoice_info->package_id;
	$item_name = $invoice_info->post_title;
	$item_name = apply_filters( 'geodir_worldpay_item_name', $item_name, $invoice_id );
	$payable_amount = $invoice_info->paied_amount;
	$invoice_type = $invoice_info->invoice_type;
	
	$currency_code = geodir_get_currency_type();
	
	$user_info = get_userdata( $user_id ); 
							
	$payable_amount = $invoice_info->paied_amount;
	$post_title = $invoice_info->post_title;
	
	$client_name = geodir_get_client_name( $user_id );
	$client_email = $user_info->user_email;
	
	$package_info = geodir_get_post_package_info($package_id, $post_id);
	$gd_post_info = geodir_get_post_info($post_id);
	
	$is_subscription = false;
	if ($invoice_type == 'add_listing' || $invoice_type == 'upgrade_listing' || $invoice_type == 'renew_listing') {
		if (!empty($package_info['sub_active'])) {
			$is_subscription = true;
			
			$sub_inerval_x = !empty($package_info['sub_units_num']) ? absint($package_info['sub_units_num']) : '1';
			$sub_inerval_u = !empty($package_info['sub_units']) ? $package_info['sub_units'] : 'D';
			$sub_duration_x = !empty($package_info['sub_units_num_times']) ? absint($package_info['sub_units_num_times']) : 0;
			$sub_trial_x = !empty($package_info['sub_units_num']) ? absint($package_info['sub_units_num']) : 0;
			$sub_trial_u = !empty($package_info['sub_num_trial_units']) ? $package_info['sub_num_trial_units'] : 'D';
		
			$recurrence_u = 'Week';
			if ($sub_inerval_u == 'W') {
				$recurrence_u = 'Week';
			} else if ( $sub_inerval_u == 'M' ) {
				$recurrence_u = 'Month';
			} else if ( $sub_inerval_u == 'Y' ) {
				$recurrence_u = 'Year';
			} else {
				if ($sub_inerval_x%365 == 0) {
					$sub_inerval_u = 'Year';
					$recurrence_u = $sub_inerval_x / 365;
				} else if ($sub_inerval_x%30 == 0) {
					$sub_inerval_xn = $sub_inerval_x / 30;
					$recurrence_u = 'Month';
				} else {
					if ($sub_inerval_x > 56) {
						$sub_inerval_xn = round($sub_inerval_x / 30);
						$recurrence_u = 'Month';
					} else {
						$sub_inerval_xn = round($sub_inerval_x / 7);
						$recurrence_u = 'Week';
					}
				}
				
				$sub_inerval_x = max(1, $sub_inerval_xn);
			}
			
			$recurrence = $sub_inerval_x . ' ' . $recurrence_u;
			$duration = $sub_duration_x > 0 ? ($sub_duration_x * $sub_inerval_x) . ' ' . $recurrence_u : 'Forever';
		}
	}
	
	$merchantid = $payment_method['vendorid'];
	if ( $merchantid == '' ) {
		$merchantid = '1303908';
	}
	
	$submit_url = $sandbox ? 'https://sandbox.2checkout.com/checkout/purchase' : 'https://www.2checkout.com/checkout/purchase';
	$sid = $merchantid;
	$name = $item_name;
	$price = $payable_amount;
	$x_receipt_link_url = $payment_method['ipnfilepath'];
	
	$add_params = array();
	if (!empty($gd_post_info->post_country))
		$add_params['country'] = $gd_post_info->post_country;
	
	if (!empty($gd_post_info->post_region))
		$add_params['state'] = $gd_post_info->post_region;
	
	if (!empty($gd_post_info->post_city))
		$add_params['city'] = $gd_post_info->post_city;
	
	if (!empty($gd_post_info->post_zip))
		$add_params['zip'] = $gd_post_info->post_zip;
	
	if (!empty($gd_post_info->geodir_contact))
		$add_params['phone'] = $gd_post_info->geodir_contact;
	
	$discount = '';
	if ($is_subscription && !empty($invoice_info->coupon_code) && $invoice_info->discount > 0) {
		$recurring_coupon = geodir_payment_coupon_is_recurring($invoice_info->coupon_code) ? true : false;
		
		if ($recurring_coupon) {
			$price = $invoice_info->amount;
			$add_params['li_1_startup_fee'] = $invoice_info->discount * (-1);
		}
	}
	?>
	<form action="<?php echo $submit_url;?>" method="post" name="frm_payment_method">
	  <input type="hidden" name="sid" value="<?php echo $sid;?>" />
	  <input type="hidden" name="mode" value="2CO" />
	  <input type="hidden" name="li_1_type" value="product" />
	  <input type="hidden" name="li_1_name" value="<?php echo esc_attr( $name );?>" />
	  <input type="hidden" name="li_1_price" value="<?php echo $price;?>" />
	  <input type="hidden" name="li_1_tangible" value="N" />
	  <input type="hidden" name="li_1_product_id" value="<?php echo $invoice_id;?>" />
	  <?php if ($is_subscription) { ?>
	  <input type="hidden" name="li_1_recurrence" value="<?php echo $recurrence;?>" />
	  <input type="hidden" name="li_1_duration" value="<?php echo $duration;?>" />
	  <?php } ?>
	  <input type="hidden" name="currency_code" value="<?php echo $currency_code;?>" />
	  <input type="hidden" name="merchant_order_id" value="<?php echo $invoice_id;?>" />
	  <input type="hidden" name="card_holder_name" value="<?php echo esc_attr( $client_name );?>" />
	  <input type="hidden" name="email" value="<?php echo esc_attr( $client_email );?>" />
	  <?php if (!empty($add_params)) { foreach ($add_params as $param_name => $param_value) { ?>
	  <input type="hidden" name="<?php echo $param_name;?>" value="<?php echo esc_attr( $param_value );?>" />
	  <?php } } ?>
	  <input type="hidden" name="x_receipt_link_url" value="<?php echo $x_receipt_link_url;?>" />
	</form>
	<div class="wrapper" style="color:#666;font-family:Open Sans,sans-serif;font-size:13px;">
		<div class="clearfix container_message">
			<center><h1 class="head2"><?php echo TWOCO_MSG; ?></h1></center>
		</div>
	</div>
	<script type="text/javascript">setTimeout("document.frm_payment_method.submit()",50);</script>
	<?php
	exit;
}
add_action( 'geodir_payment_form_handler_2co' , 'geodir_payment_form_2co' );

// PRE BANK TRANSFER
function geodir_payment_form_prebanktransfer( $invoice_id ) {
	$invoice_info = geodir_get_invoice( $invoice_id );
	
	$user_id = $invoice_info->user_id;
	$post_id = $invoice_info->post_id;
	$item_name = $invoice_info->post_title;
	$item_name = apply_filters( 'geodir_prebanktransfer_item_name', $item_name, $invoice_id );
	$payable_amount = geodir_payment_price( $invoice_info->paied_amount );
		
	$transaction_details = '';
	$transaction_details .= '--------------------------------------------------<br />';
	$transaction_details .= sprintf( __( 'Payment Details for Invoice ID #%s', 'geodir_payments' ), geodir_payment_invoice_id_formatted($invoice_id) ) . '<br />';
	$transaction_details .= '--------------------------------------------------<br />';
	$transaction_details .= sprintf( __( 'Item Name: %s', 'geodir_payments' ), $item_name ) . '<br />';
	$transaction_details .= '--------------------------------------------------<br />';
	$transaction_details .= sprintf( __( 'Status: %s', 'geodir_payments' ), __( 'Pending', 'geodir_payments' ) ) . '<br />';
	$transaction_details .= sprintf( __( 'Amount: %s', 'geodir_payments' ), $payable_amount ) . '<br />';
	$transaction_details .= sprintf( __( 'Type: %s', 'geodir_payments' ), __( 'Pre Bank Transfer', 'geodir_payments' ) ) . '<br />';
	$transaction_details .= sprintf( __( 'Date: %s', 'geodir_payments' ), date_i18n( 'F j, Y, g:i a', current_time( 'timestamp' ) ) ) . '<br />';
	$transaction_details .= sprintf( __( 'Method: %s', 'geodir_payments' ), __( 'Pre Bank Transfer', 'geodir_payments' ) ) . '<br />';
	$transaction_details .= '--------------------------------------------------<br />';	
	
	/*############ SET THE INVOICE STATUS START ############*/
	// update invoice status and transaction details
	geodir_update_invoice_status( $invoice_id, 'pending' );
	geodir_update_invoice_transaction_details( $invoice_id, $transaction_details );
	/*############ SET THE INVOICE STATUS END ############*/
	
	// send notification to admin
	geodir_payment_adminEmail( $post_id, $user_id, 'payment_success', $transaction_details );
	
	// send notification to client
	geodir_payment_clientEmail( $post_id, $user_id, 'payment_success', $transaction_details );



    $redirect_url = geodir_info_url(  array( 'pay_action' => 'success', 'inv' => $invoice_id, 'pid' => $post_id ) );

    wp_redirect($redirect_url);
	gd_die();
}
add_action( 'geodir_payment_form_handler_prebanktransfer', 'geodir_payment_form_prebanktransfer' );

// PAYMENT ON DELIVERY
/**
 * Perform payment on delivery request for current invoice.
 *
 * @since 1.0.0
 * @package GeoDirectory_Payment_Manager
 *
 * @param int $invoice_id Payment invoice id.
 */
function geodir_payment_form_payondelevary($invoice_id) {
}
add_action( 'geodir_payment_form_handler_payondelevary' , 'geodir_payment_form_payondelevary' );

// PAYMENT IPN HANDLERS
// PAYPAL IPN
function geodir_ipn_handler_paypal() {
	$paymentOpts = get_payment_options('paypal');
	$paymode = $paymentOpts['payment_mode'];
	$sandbox = $paymode == 'sandbox' ? true : false;
	
	$currency_code 	= geodir_get_currency_type(); // Actual curency code
	$merchantid 	= $paymentOpts['merchantid']; // Actual paypal business email
	
	/* read the post from PayPal system and add 'cmd' */
	$post_data = 'cmd=_notify-validate';
	
	$post = $_POST;

	foreach ($post as $key => $value) {
		$value = urlencode(stripslashes_deep($value));
		$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);/* this fixes paypal invalid IPN , STIOFAN */
		$post_data .= "&$key=$value";
	}
	
	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($post_data) . "\r\n\r\n";
	
	$paypal_url = $paymode == 'sandbox' ? 'ssl://www.sandbox.paypal.com' : 'ssl://www.paypal.com';
		
	$fp = fsockopen ($paypal_url, 443, $errno, $errstr, 30);
	
	if (!$fp) { 
		// HTTP ERROR
	} else {
		fputs ($fp, $header . $post_data);
	
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			
			// Inspect IPN validation result and act accordingly
			$valid_ipn = strstr($res, "VERIFIED");
			$invalid_ipn = strstr($res, "INVALID");
			
			$invoice_id		= isset($post['custom']) ? $post['custom'] : NULL; // invoice id
			$invoice_info 	= geodir_get_invoice( $invoice_id );
            if ( empty( $invoice_info ) ) {
                return;
            }
            
            /// PM TO INVOICING IPN
            if ( defined( 'WPINV_VERSION' ) ) {
                $wpi_invoice        = !empty( $invoice_info->invoice_id ) ? wpinv_get_invoice( $invoice_info->invoice_id ) : NULL;
                $wpi_invoice_id     = !empty( $wpi_invoice ) && !empty( $wpi_invoice->ID ) ? $wpi_invoice->ID : geodir_wpi_save_invoice( $invoice_id );
                $txn_type           = !empty( $post['txn_type'] ) ? $post['txn_type'] : '';
                if ( $wpi_invoice_id && $txn_type ) {
                    $post['custom'] = $wpi_invoice_id;

                    if ( $txn_type == 'recurring_payment' || $txn_type == 'subscr_payment' ) {
                        wpinv_process_paypal_subscr_payment( $post );
                        die( '1' );
                    } else if ( $txn_type == 'subscr_cancel' || $txn_type == 'subscr_failed' ) {
                        wpinv_process_paypal_subscr_cancel( $post );
                        die( '1' );
                    }
                }
                die( '2' );
            }
            ///

			// if no invoice info it might have wrong custom field in IPN, as the post id.
			if(!$invoice_info){
				global $wpdb;
				$invoice = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".INVOICE_TABLE." WHERE post_id = %d ", array($invoice_id)));
				if($invoice){
					$invoice_info = $invoice;
					$invoice_id = $invoice_id->id;
				}
			}

			$user_id		= !empty( $invoice_info ) ? $invoice_info->user_id : '1';
			
			if ( $valid_ipn || $sandbox) { // it will enter in condition in test mode. 
				$item_name		= $post['item_name'];
				$txn_id			= $post['txn_id'];
				$payment_status	= $post['payment_status'];
				$payment_type	= $post['payment_type'];
				$payment_date	= $post['payment_date'];
				$txn_type		= $post['txn_type'];
				$subscription 	= $txn_type == 'recurring_payment' || $txn_type == 'subscr_payment' ? true : false;
				
				$mc_currency	= $post['mc_currency'];
				$mc_gross		= $post['mc_gross'];
				$payment_gross	= $post['payment_gross'];
				$receiver_email	= $post['receiver_email'];
				$receiver_id	= $post['receiver_id']; // Paypal Merchant Account ID
				$paid_amount	= $mc_gross ? $mc_gross : $payment_gross;
				
				$cart_amount	= $invoice_info->paied_amount;
				$post_id		= $invoice_info->post_id;
				
				/*####################################
				######## FRAUD CHECKS ################
				####################################*/
				$fraud					= false;
				$fraud_msg				= '';
				$transaction_details	= '';
				
				// Paypal business field allows both paypal id and paypal email. @see https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/#html-variables-for-shopping-carts
				if ( !( $receiver_email == $merchantid || $receiver_id == $merchantid ) ) {
					$fraud = true;
					$fraud_msg .= __('### The Paypal receiver email address does not match the paypal address for this site ###<br />', 'geodir_payments');
				}
				
				if ( floatval($paid_amount) != floatval($cart_amount) ) {
					$fraud = true;
					$fraud_msg .= __('### The paid amount does not match the price package selected ###<br />', 'geodir_payments');
				}
				
				if ( $mc_currency != $currency_code ) {
					$fraud = true;
					$fraud_msg .= __('### The currency code returned does not match the code on this site. ###<br />', 'geodir_payments');
				}
				
				if ($txn_type == 'subscr_payment' || $txn_type == 'recurring_payment' || $txn_type == 'subscr_signup') {
					update_post_meta($post_id, '_gdpm_recurring', true);
				} else {
					update_post_meta($post_id, '_gdpm_recurring', false);
				}
				
				/*#####################################
				######## PAYMENT SUCCESSFUL ###########
				######################################*/
				if ($txn_type == 'web_accept' || $txn_type == 'subscr_payment' || $txn_type == 'recurring_payment' || $txn_type == 'express_checkout' ) {
					$paid_amount_with_currency = geodir_payment_price($paid_amount);
					
					if ( $fraud ) {
						$transaction_details .= __('WARNING FRAUD DETECTED PLEASE CHECK THE DETAILS - (IF CORRECT, THEN PUBLISH THE POST)', 'geodir_payments')."<br />";
					}
					
					$transaction_details .= $fraud_msg;
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Payment Details for Invoice ID #%s", 'geodir_payments'), geodir_payment_invoice_id_formatted($invoice_id)) ."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Item Name: %s", 'geodir_payments'),$item_name)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
					$transaction_details .= sprintf(__("Trans ID: %s", 'geodir_payments'), $txn_id)."<br />";
					$transaction_details .= sprintf(__("Status: %s", 'geodir_payments'), $payment_status)."<br />";
					$transaction_details .= sprintf(__("Amount: %s", 'geodir_payments'), $paid_amount_with_currency)."<br />";
					$transaction_details .= sprintf(__("Type: %s", 'geodir_payments'),$payment_type)."<br />";
					$transaction_details .= sprintf(__("Date: %s", 'geodir_payments'), $payment_date)."<br />";
					$transaction_details .= sprintf(__("Method: %s", 'geodir_payments'), $txn_type)."<br />";
					$transaction_details .= "--------------------------------------------------<br />";
										
					/*############ SET THE INVOICE STATUS START ############*/
					// update invoice status and transaction details
					geodir_update_invoice_status( $invoice_id, 'confirmed', $subscription );
					geodir_update_invoice_transaction_details( $invoice_id, $transaction_details );
					/*############ SET THE INVOICE STATUS END ############*/
					
					// send notification to admin
					geodir_payment_adminEmail( $post_id, $user_id, 'payment_success', $transaction_details );
					
					// send notification to client
					geodir_payment_clientEmail( $post_id, $user_id, 'payment_success', $transaction_details );
					
				} else if ( $txn_type == 'subscr_cancel' || $txn_type == 'subscr_failed' ) {
					// Set the subscription ac cancelled
					$post_content = str_replace("&", "<br />", urldecode($post_data));
					$post_content .= '<br />############## '.__('ORIGINAL SUBSCRIPTION INFO BELOW', 'geodir_payments').' ####################<br />';
					$post_content .= $invoice_info->html;
					
					// update invoice status and transaction details
					$status = $txn_type == 'subscr_cancel' ? 'cancelled' : 'failed';
					
					geodir_update_invoice_status( $invoice_id, $status, $subscription );
					geodir_update_invoice_transaction_details( $invoice_id, $post_content );
					
				} else if( $txn_type == 'subscr_signup' ) {
					$post_content = '####### '.__('THIS IS A SUBSCRIPTION SIGNUP AND IF A FREE TRIAL WAS OFFERED NO PAYMENT WILL BE RECEIVED', 'geodir_payments').' ######<br />';
					$post_content .= str_replace("&", "<br />", urldecode($post_data));
					
					// update invoice status and transaction details
					geodir_update_invoice_status( $invoice_id, 'confirmed', $subscription );
					geodir_update_invoice_transaction_details( $invoice_id, $post_content );
				}
				/*#####################################
				######## PAYMENT SUCCESSFUL ###########
				######################################*/				
			} else if ( $invalid_ipn ) {
				// update invoice status
				geodir_update_invoice_status( $invoice_id, 'failed' );
					
				// send notification to admin
				geodir_payment_adminEmail( $invoice_id, $user_id, 'payment_fail' );
			}	
		}
	}
}
add_action( 'geodir_ipn_handler_paypal' , 'geodir_ipn_handler_paypal' );

// 2CO IPN
function geodir_ipn_handler_2co() {
	$post = $_POST;
	
    $ins_vendor_id = isset($post['vendor_id']) ? $post['vendor_id'] : '';
	$ins_vendor_order_id = isset($post['vendor_order_id']) && $post['vendor_order_id'] > 0 ? absint($post['vendor_order_id']) : '';
	$ins_message_type = isset($post['message_type']) ? $post['message_type'] : '';
	$ins_sale_id = isset($post['sale_id']) ? absint($post['sale_id']) : '';
	$ins_invoice_id = isset($post['invoice_id']) ? absint($post['invoice_id']) : '';
	$ins_recurring = isset($post['recurring']) && absint($post['recurring']) == 1 ? true : false;
	$ins_invoice_status = isset($post['invoice_status']) ? $post['invoice_status'] : '';
	$ins_recurrence_status = isset($post['item_rec_status_1']) ? $post['item_rec_status_1'] : '';
	$ins_next_recurrence = isset($post['item_rec_date_next_1']) ? $post['item_rec_date_next_1'] : '';
	$ins_payment_type = isset($post['payment_type']) ? $post['payment_type'] : '';
	$ins_md5_hash = isset($post['md5_hash']) ? $post['md5_hash'] : '';
	$ins_cust_amount = isset($post['invoice_cust_amount']) ? $post['invoice_cust_amount'] : '';
	
	$invoice_id = $ins_vendor_order_id;
	$is_subscription = $ins_recurring;
	$invoice_info = $invoice_id ? geodir_get_invoice($invoice_id) : NULL;
	
	if (empty($invoice_info) || !($ins_message_type && $ins_sale_id && $ins_invoice_id)) {
		wp_redirect(home_url());
		exit;
	}
	
	$message_type = isset( $post['message_type'] ) ? geodir_strtoupper($post['message_type']) : '';

    /// PM TO INVOICING IPN
    if ( defined( 'WPINV_VERSION' ) ) {
        $wpi_invoice        = !empty( $invoice_info->invoice_id ) ? wpinv_get_invoice( $invoice_info->invoice_id ) : NULL;
        $wpi_invoice_id     = !empty( $wpi_invoice ) && !empty( $wpi_invoice->ID ) ? $wpi_invoice->ID : geodir_wpi_save_invoice( $invoice_id );
        $wpi_invoice        = empty( $wpi_invoice->ID ) && $wpi_invoice_id ? wpinv_get_invoice( $wpi_invoice_id ) : $wpi_invoice;
        
        if ( !empty( $wpi_invoice ) && $wpi_invoice_id && $message_type ) {
            $post['vendor_order_id'] = $wpi_invoice_id;
            
            if ( $message_type == 'RECURRING_INSTALLMENT_SUCCESS' ) {
                wpinv_2co_record_subscription_payment( $post, $wpi_invoice );
                die( '1' );
            } else if ( $message_type == 'RECURRING_STOPPED' ) {
                $wpi_invoice->stop_subscription();
                die( '1' );
            } else if ( $message_type == 'RECURRING_RESTARTED' ) {
                $wpi_invoice->restart_subscription();
                die( '1' );
            } else if ( $message_type == 'RECURRING_COMPLETE' ) {
                $wpi_invoice->complete_subscription();
                die( '1' );
            }
        }
        die( '2' );
    }
    ///
	
	$status = 'pending';
	$is_recurring = false;
	if ($message_type == 'ORDER_CREATED' || $message_type == 'INVOICE_STATUS_CHANGED') {
		if ($ins_invoice_status == 'approved' || $ins_invoice_status == 'deposited') {
			$status = 'confirmed';
		} else if ($ins_invoice_status == 'declined') {
			$status = 'failed';
		}
	} else if ($message_type == 'REFUND_ISSUED') {
		$status = 'failed';
	} else if (in_array($message_type, array('RECURRING_INSTALLMENT_SUCCESS', 'RECURRING_RESTART', 'RECURRING_RESTARTED'))) {
		$status = 'confirmed';
		$is_recurring = true;
	} else if (in_array($message_type, array('RECURRING_INSTALLMENT_FAILED', 'RECURRING_STOPPED', 'RECURRING_COMPLETE'))) {
		$status = 'custom';
		$is_recurring = true;
	}
	
	$invoice_data = (array)maybe_unserialize($invoice_info->invoice_data);
		
	$post_id = $invoice_info->post_id;
	$user_id = $invoice_info->user_id;
	
	if ($is_recurring) {
		if ($status == 'confirmed') {
			update_post_meta($post_id, '_gdpm_recurring', true);
		} else {
			update_post_meta($post_id, '_gdpm_recurring', false);
		}
	}
	
	$redirect_url = home_url();
	$success_url = geodir_info_url(array('pay_action' => 'success', 'inv' => $invoice_id, 'pid' => $post_id));
	$cancel_url = geodir_info_url(array('pay_action' => 'cancel', 'inv' => $invoice_id, 'pid' => $post_id));
	
	$notify_status = '';
	$update_status = false;
	$update_transaction = false;
	switch ($status) {
		case 'confirmed':
			$redirect_url = $success_url;
			$notify_status 	= 'payment_success';
			$update_status = true;
			$update_transaction = true;
		break;
		case 'pending':
			$redirect_url = $success_url;
		break;
		case 'cancelled':
		case 'failed':
			$redirect_url = $cancel_url;
			$notify_status 	= 'payment_fail';
			$update_status = true;
			$update_transaction = true;
		break;
		case 'custom':
			$notify_status 	= 'payment_success';
			$redirect_url = $success_url;
			$update_transaction = true;
		break;
	}
	
	$item_name		= $post['item_name_1'];
	$txn_id			= $ins_invoice_id;
	$payment_status = geodir_payment_status_name( $status );
	$amount			= geodir_payment_price($ins_cust_amount);
	$payment_type 	= $ins_payment_type;
	$payment_date 	= date_i18n( "F j, Y, g:i a", current_time( 'timestamp' ) );
	$payment_method = geodir_payment_method_title( '2co' );
	$discount = '';
	if (!empty($invoice_info->coupon_code) && $invoice_info->discount > 0) {
		$recurring_coupon = geodir_payment_coupon_is_recurring($invoice_info->coupon_code) ? true : false;
		
		$discount = ($recurring_coupon && $is_recurring && $status == 'confirmed') ? '' : geodir_payment_price($invoice_info->discount);
	}
	
	$invoice_data['timestamp'] = $post['timestamp'];
	$invoice_data['vendor_id'] = $ins_invoice_id;
	$invoice_data['invoice_id'] = $ins_invoice_id;
	$invoice_data['sale_id'] = $ins_sale_id;
	
	$transaction_details = '';
	if ($is_subscription) {
		if ($is_recurring) {
			$transaction_details .= '##### ' . __(geodir_strtoupper($post['message_description']), 'geodir_payments' ) . ' #####<br />';
		} else {
			$transaction_details .= '##### ' . __( 'THIS IS A SUBSCRIPTION PAYMENT', 'geodir_payments' ) . ' #####<br />';
		}
	}
	$transaction_details .= "--------------------------------------------------<br />";
	$transaction_details .= wp_sprintf( __( "Payment Details for Invoice ID #%s", 'geodir_payments' ), geodir_payment_invoice_id_formatted($invoice_id) ) . "<br />";
	$transaction_details .= "--------------------------------------------------<br />";
	$transaction_details .= wp_sprintf( __( "Item Name: %s", 'geodir_payments' ), $item_name ) . "<br />";
	$transaction_details .= "--------------------------------------------------<br />";
	$transaction_details .= wp_sprintf( __( "Trans ID: %s", 'geodir_payments' ), $txn_id ) . "<br />";
	$transaction_details .= wp_sprintf( __( "Status: %s", 'geodir_payments' ), $payment_status ) . "<br />";
	$transaction_details .= wp_sprintf( __( "Payable Amount: %s", 'geodir_payments' ), $amount ) . "<br />";
	if ($discount) {
		$transaction_details .= sprintf( __( 'Discount: %s', 'geodir_payments' ), $discount ) . '<br />';
	}
	$transaction_details .= wp_sprintf( __( "Type: %s", 'geodir_payments' ), $payment_type ) . "<br />";
	$transaction_details .= wp_sprintf( __( "Date: %s", 'geodir_payments' ), $payment_date ) . "<br />";
	$transaction_details .= wp_sprintf( __( "Method: %s", 'geodir_payments' ), $payment_method ) . "<br />";
	$transaction_details .= wp_sprintf( __( "Sale ID: %s", 'geodir_payments' ), $ins_sale_id ) . "<br />";

	if ($is_subscription && $ins_recurrence_status) {
		$transaction_details .= wp_sprintf( __( "Subscription status: %s", 'geodir_payments' ), __(ucfirst($ins_recurrence_status), 'geodir_payments') ) . "<br />";
	}
	if ($is_subscription && $status == 'confirmed' && $ins_next_recurrence && $ins_recurrence_status == 'live') {
		$transaction_details .= wp_sprintf( __( "Date of next recurring installment: %s", 'geodir_payments' ), date_i18n("F j, Y", strtotime($ins_next_recurrence))) . "<br />";
	}
	$transaction_details .= "--------------------------------------------------<br />";

	if ($update_status) {
		global $wpdb;
		$wpdb->update(INVOICE_TABLE, array('subscription' => ($is_subscription ? 1 : 0), 'invoice_data' => maybe_serialize($invoice_data)), array('id' => $invoice_id));
		
		geodir_update_invoice_status($invoice_id, $status, $is_subscription);
	}
	if ($update_transaction) {
		geodir_update_invoice_transaction_details($invoice_id, $transaction_details);
	}
	
	if ($notify_status != '') {
		geodir_payment_adminEmail($post_id, $user_id, $notify_status, $transaction_details); // send notification to admin
		geodir_payment_clientEmail($post_id, $user_id, $notify_status, $transaction_details); // send notification to client
	}

	wp_redirect($redirect_url);
	exit;
}
add_action( 'geodir_ipn_handler_2co' , 'geodir_ipn_handler_2co' );

// 2CO IPN
function geodir_ipn_handler_worldpay() {
	$post = $_POST;
	
	$cardType 		= isset( $post['cardType'] ) ? geodir_strtoupper($post['cardType']) : '';
	$invoice_id 	= isset( $post['cartId'] ) ? (int)$post['cartId'] : '';
	$invoice_status = isset( $post['transStatus'] ) ? $post['transStatus'] : '';
	$txn_id			= isset( $post['transId'] ) ? $post['transId'] : '';
	
	if ( $invoice_id > 0 && $invoice_status != '' && $txn_id != '' ) {
		$invoice_info = geodir_get_invoice( $invoice_id );
		if ( empty( $invoice_info ) ) {
			exit;
		}
        
        /// PM TO INVOICING IPN
        if ( defined( 'WPINV_VERSION' ) ) {
            $wpi_invoice        = !empty( $invoice_info->invoice_id ) ? wpinv_get_invoice( $invoice_info->invoice_id ) : NULL;
            $wpi_invoice_id     = !empty( $wpi_invoice ) && !empty( $wpi_invoice->ID ) ? $wpi_invoice->ID : geodir_wpi_save_invoice( $invoice_id );
            
            if ( $wpi_invoice_id ) {
                $_POST['MC_key'] = $wpi_invoice->get_key();
                $_POST['MC_invoice_id'] = $wpi_invoice_id;
                
                wpinv_process_worldpay_ipn();
            }
            return;
        }
        ///
		
		$post_id		= $invoice_info->post_id;
		$user_id		= $invoice_info->user_id;
		
		$notify_status 	= 'payment_fail';
		
		if ( $invoice_status == 'Y' ) { // payment status approved
			$status 		= 'confirmed';
			$notify_status 	= 'payment_success';
		} else if ( $invoice_status == 'C' ) { // payment status pending
			$status 		= 'cancelled';
		} else { // payment status fail
			$status 		= 'fail';
		}
		
		$item_name		= $invoice_info->post_title;
		$payment_status = geodir_payment_status_name( $status );
		$amount			= geodir_payment_price( $post['amount'] );
		$payment_type 	= $cardType;
		$payment_date 	= date_i18n( "F j, Y, g:i a", current_time( 'timestamp' ) );
		$payment_method = geodir_payment_method_title( 'worldpay' );
		
		$transaction_details = "--------------------------------------------------<br />";
		$transaction_details .= wp_sprintf( __( "Payment Details for Invoice ID #%s", 'geodir_payments' ), geodir_payment_invoice_id_formatted($invoice_id) ) . "<br />";
		$transaction_details .= "--------------------------------------------------<br />";
		$transaction_details .= wp_sprintf( __( "Item Name: %s", 'geodir_payments' ), $item_name ) . "<br />";
		$transaction_details .= "--------------------------------------------------<br />";
		$transaction_details .= wp_sprintf( __( "Trans ID: %s", 'geodir_payments' ), $txn_id ) . "<br />";
		$transaction_details .= wp_sprintf( __( "Status: %s", 'geodir_payments' ), $payment_status ) . "<br />";
		$transaction_details .= wp_sprintf( __( "Amount: %s", 'geodir_payments' ), $amount ) . "<br />";
		$transaction_details .= wp_sprintf( __( "Type: %s", 'geodir_payments' ), $payment_type ) . "<br />";
		$transaction_details .= wp_sprintf( __( "Date: %s", 'geodir_payments' ), $payment_date ) . "<br />";
		$transaction_details .= wp_sprintf( __( "Method: %s", 'geodir_payments' ), $payment_method ) . "<br />";
		$transaction_details .= "--------------------------------------------------<br />";
		
		geodir_update_invoice_status( $invoice_id, $status );
		geodir_update_invoice_transaction_details( $invoice_id, $transaction_details );
		
		geodir_payment_adminEmail( $post_id, $user_id, $notify_status, $transaction_details ); // send notification to admin
		geodir_payment_clientEmail( $post_id, $user_id, $notify_status, $transaction_details ); // send notification to client
	}
	exit;
}
add_action( 'geodir_ipn_handler_worldpay' , 'geodir_ipn_handler_worldpay' );

