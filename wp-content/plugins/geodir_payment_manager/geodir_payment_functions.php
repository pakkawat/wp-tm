<?php

add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_payment_manager', 10,1); 
function geodir_diagnose_multisite_conversion_payment_manager($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_invoice'] = __('Invoice','geodir_payments');
	$table_arr['geodir_price'] = __('Price','geodir_payments');
	$table_arr['geodir_coupons'] = __('Coupons','geodir_payments');
	return $table_arr;
}

//==========GEODIR PAYMENT MODULE ACTIVATION CODE===============
function geodir_payment_activation() {
	// First check if geodir main plugin is active or not.
	if (get_option('geodir_installed')) {
		geodir_payment_activation_script();
		
		$notifications_settings = geodir_payment_resave_settings(geodir_payment_notifications());
		geodir_update_options($notifications_settings, true);
		
		$general_settings = geodir_payment_resave_settings(geodir_payment_general_options());
		geodir_update_options($general_settings, true);
		
		add_option('geodir_payment_manager_activation_redirect', 1);
	}
}


// This function is used to create geodirectory payment manager navigation 
function geodir_payment_manager_tabs($tabs){
	$tabs['paymentmanager_fields'] = array( 'label' =>__( 'Prices and Payments', 'geodir_payments' ),
									'subtabs' => array(
													array('subtab' => 'geodir_payment_general_options',
																'label' =>__( 'General', 'geodir_payments'),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_payment_manager',
																'label' =>__( 'Prices', 'geodir_payments'),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													/*array('subtab' => 'geodir_payment_options',
																'label' =>__( 'Payments', 'geodir_payments'),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_invoice_list',
																'label' =>__( 'Invoices', 'geodir_payments'),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),
													array('subtab' => 'geodir_coupon_manager',
																'label' =>__( 'Coupons', 'geodir_payments'),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax')),*/
													array('subtab' => 'payment_notifications',
																'label' =>__( 'Notifications', 'geodir_payments'),
																'form_action' => admin_url('admin-ajax.php?action=geodir_payment_manager_ajax'))
													
													)
				);
	return $tabs; 
}


function geodir_payment_general_options_form($current_tab)
{
	$current_tab = $_REQUEST['subtab'];
	geodir_payment_option_form($current_tab); // function in geodir_payment_template_functions.php
}


function geodir_get_payment_notifications_form($current_tab)
{
	$current_tab = $_REQUEST['subtab'];
	geodir_payment_option_form($current_tab); // function in geodir_payment_template_functions.php
}


function geodir_payment_manager_tab_content()
{
	global $wpdb;
?>
	
	<?php
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_general_options')
	{	
		add_action('geodir_admin_option_form', 'geodir_payment_general_options_form');
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_manager')
	{
		if(isset($_REQUEST['gd_pagetype']) && $_REQUEST['gd_pagetype']=='addeditprice')
		{
			geodir_package_price_form();
		}
		else
		{
			geodir_package_price_list();
		}
	}	
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_options')
	{	
		if(isset($_REQUEST['gd_payact']) && $_REQUEST['gd_payact']=='gd_setting')
		{
			geodir_payment_gateway_setting_form();
		}
		else
		{
			geodir_payment_gateways_list();
		}
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_invoice_list')
	{
		geodir_payment_invoice_list();
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_coupon_manager')
	{
		if(isset($_REQUEST['gd_pagetype']) && $_REQUEST['gd_pagetype']=='addeditcoupon')
		{
			geodir_payment_coupon_form();
		}
		else
		{
			geodir_payment_coupon_list();
		}
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'payment_notifications')
	{
		add_action('geodir_admin_option_form', 'geodir_get_payment_notifications_form');	
	}	
		
}

//=========ADD BACKEND FORM CSS FUNCTION===========
function geodir_admincss_payment_manager(){
	global $pagenow;
	
	if ( ( $pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'paymentmanager_fields' ) || ( function_exists( 'geodir_wpi_allow_pm_scripts' ) && geodir_wpi_allow_pm_scripts() ) ) {
	//Style
	wp_register_style('payment-plugin-style', plugins_url('',__FILE__).'/css/geodir-payment-manager.css');
	wp_enqueue_style('payment-plugin-style');
	
	}
}

function geodir_payment_admin_scripts() {
	global $pagenow;
	if ( ( $pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'paymentmanager_fields' ) || ( function_exists( 'geodir_wpi_allow_pm_scripts' ) && geodir_wpi_allow_pm_scripts() ) ) {
		$deps = array();
		if ( defined( 'WPINV_VERSION' ) ) {
			$deps[] = 'wpinv-admin-script';
		}
		wp_register_script( 'geodirectory-payment-admin-script', plugins_url( '/js/payment-script.js', __FILE__ ), $deps );
		wp_enqueue_script( 'geodirectory-payment-admin-script');
	}
}

function geodir_payment_create_new_post_type($post_type = ''){
	
	global $wpdb, $plugin_prefix;
	
	if($post_type != ''){
	
		$all_postypes = geodir_get_posttypes();
	
		if(!in_array($post_type, $all_postypes))
			return false;
		
		$package_info = geodir_get_post_package_info_on_listing('', '', $post_type);
		
		$package_id = $package_info->pid;
		
		$table = $plugin_prefix.$post_type.'_detail';
		
		$wpdb->query($wpdb->prepare("UPDATE ".$table." SET package_id=%d WHERE package_id=0",array($package_id)));
		
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages=%d WHERE post_type=%s AND (packages='0' || packages='')",
				array($package_id,$post_type)
			)
		);
		
		$meta_field_add = " ENUM( 'false', 'true' ) NOT NULL DEFAULT 'false'";
		geodir_add_column_if_not_exist( $table, "expire_notification", $meta_field_add );
		geodir_add_column_if_not_exist( $table, "exp2", $meta_field_add );
		geodir_add_column_if_not_exist( $table, "exp3", $meta_field_add );
		
	}
}


function geodir_payment_delete_post_type($post_type = ''){

	global $wpdb, $plugin_prefix;
	
	if($post_type != ''){
		
		$all_postypes = geodir_get_posttypes();
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_PRICE_TABLE." WHERE post_type=%s", array($post_type)));
		
		$coupon_data = $wpdb->get_results($wpdb->prepare("SELECT cid, post_types FROM ".COUPON_TABLE." WHERE FIND_IN_SET(%s, post_types)", array($post_type)));
		
		if(!empty($coupon_data)){
			
			foreach($coupon_data as $key => $coupon){
			
				$coupons = explode(",",$coupon->post_types);
				
				if(($del_key = array_search($post_type, $coupons)) !== false)
					unset($coupons[$del_key]);
				
				if(!empty($coupons)){
					
					$coupons = implode(',',$coupons);
					
					$wpdb->query($wpdb->prepare("UPDATE ".COUPON_TABLE." SET post_types=%s WHERE cid=%d",array($coupons,$coupon->cid)));
					
				}else{
					
					$wpdb->query($wpdb->prepare("DELETE FROM ".COUPON_TABLE." WHERE cid=%d", array($coupon->cid)));
					
				}
					
			}
			
		}
	
	}
}

function geodir_payment_activation_redirect(){
	if (get_option('geodir_payment_manager_activation_redirect', false))
	{
		delete_option('geodir_payment_manager_activation_redirect');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_general_options')); 
	}
}

/**
 * Runs on plugin deactivation.
 *
 * @since 1.0.0
 * @package GeoDirectory_Payment_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_payment_deactivation() {
	global $wpdb;
	
	/* --- delete custom sort options --- */
	if (defined('GEODIR_CUSTOM_SORT_FIELDS_TABLE')) {
		$wpdb->query($wpdb->prepare("UPDATE " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " SET is_active='0' WHERE htmlvar_name=%s", array('is_featured')));
	}	
}

//==========GEODIR PAYMENT MODULE UNINSTALLATION CODE===============
function geodir_get_additional_pay_options($pay_method = '') {
	$paymenthodinfo = array();
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Merchant Id", 'geodir_payments'),
	"fieldname"		=>	"merchantid",
	"value"			=>	"myaccount@paypal.com",
	"description"	=>	__("Example : myaccount@paypal.com", 'geodir_payments'),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Cancel Url", 'geodir_payments'),
	"fieldname"		=>	"cancel_return",
	"value"			=>	geodir_info_url(array('pay_action'=>'cancel','pmethod'=>'paypal')),
	"description"	=>	__("Example : ", 'geodir_payments').geodir_info_url(array('pay_action'=>'cancel','pmethod'=>'paypal')),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Return Url", 'geodir_payments'),
	"fieldname"		=>	"returnUrl",
	"value"			=>	geodir_info_url(array('pay_action'=>'return','pmethod'=>'paypal')),
	"description"	=>	__("Example : ", 'geodir_payments').geodir_info_url(array('pay_action'=>'return','pmethod'=>'paypal')),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Notify Url", 'geodir_payments'),
	"fieldname"		=>	"notify_url",
	"value"			=>	geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'paypal')),
	"description"	=>	__("Example : ", 'geodir_payments').geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'paypal')),
	);
	
	$paymenthodinfo['paypal'] = array(
	"name" 		=> __('Paypal', 'geodir_payments'),
	"key" 		=> 'paypal',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'1',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_paypal_options' ,$payOpts),
	);
	
	//////////authorize.net start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Login ID", 'geodir_payments'),
	"fieldname"		=>	"loginid",
	"value"			=>	"yourname@domain.com",
	"description"	=>	__("Example : yourname@domain.com", 'geodir_payments')
	);
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Transaction Key", 'geodir_payments'),
	"fieldname"		=>	"transkey",
	"value"			=>	"1234567890",
	"description"	=>	__("Example : 1234567890", 'geodir_payments'),
	);
	
	$paymenthodinfo['authorizenet'] = array(
	"name" 		=> __('Authorize.net', 'geodir_payments'),
	"key" 		=> 'authorizenet',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'3',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_authorizenet_options' ,$payOpts),
	);
	
	//////////worldpay start////////
	
	$payOpts = array();	

	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Instant Id", 'geodir_payments'),
	"fieldname"		=>	"instId",
	"value"			=>	"211616",
	"description"	=>	__("Example : 211616", 'geodir_payments')
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account Id", 'geodir_payments'),
	"fieldname"		=>	"accId1",
	"value"			=>	"12345",
	"description"	=>	__("Example : 12345", 'geodir_payments')
	);
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Notify Url", 'geodir_payments'),
	"fieldname"		=>	"ipnfilepath",
	"value"			=>	geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'worldpay')),
	"description"	=>	wp_sprintf( __( 'Login to your Worldpay Merchant Interface then enable Payment Response & Shopper Response. Next, go to the Payment Response URL field and type "<b>%s</b>" or "<b>&lt;wpdisplay item=MC_callback&gt;</b>" for a dynamic payment response.', 'geodir_payments' ),geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'worldpay'))),
	);
	
	$paymenthodinfo['worldpay'] = array(
	"name" 		=> __('Worldpay', 'geodir_payments'),
	"key" 		=> 'worldpay',
	"isactive"	=>	'1', // 1->display,0->hide\
	"display_order"=>'4',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_worldpay_options' ,$payOpts),
	);
	
	//////////2co start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Vendor ID", 'geodir_payments'),
	"fieldname"		=>	"vendorid",
	"value"			=>	"1303908",
	"description"	=>	__("Enter Vendor ID Example : 1303908", 'geodir_payments')
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Notify Url", 'geodir_payments'),
	"fieldname"		=>	"ipnfilepath",
	"value"			=>	geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'2co')),
	"description"	=>	__("Example : ", 'geodir_payments').geodir_info_url(array('pay_action'=>'ipn','pmethod'=>'2co')),
	);
	
	$paymenthodinfo['2co'] = array(
	"name" 		=> __('2CO (2Checkout)', 'geodir_payments'),
	"key" 		=> '2co',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'5',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_2co_options' ,$payOpts),
	);
	
	//////////pre bank transfer start////////
	
	$payOpts = array();
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account Name", 'geodir_payments'),
	"fieldname"		=>	"bankinfo",
	"value"			=>	"ICICI Bank",
	"description"	=>	__("Enter the bank name to which you want to transfer payment", 'geodir_payments')
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account SC", 'geodir_payments'),
	"fieldname"		=>	"bank_accountsc",
	"value"			=>	"11-22-33",
	"description"	=>	__("Enter your bank Account Sort Code", 'geodir_payments'),
	);
	
	$payOpts[] = array(
	"field_type" => "text",
	"title"			=>	__("Account No", 'geodir_payments'),
	"fieldname"		=>	"bank_accountid",
	"value"			=>	"AB1234567890",
	"description"	=>	__("Enter your bank Account Number", 'geodir_payments'),
	);
	
	$paymenthodinfo['prebanktransfer'] = array(
	"name" 		=> __('Pre Bank Transfer', 'geodir_payments'),
	"key" 		=> 'prebanktransfer',
	"isactive"	=>	'1', // 1->display,0->hide
	"display_order"=>'6',
	"payment_mode"=>'live',
	"payOpts"	=>	apply_filters('geodir_payment_prebanktransfer_options' ,$payOpts),
	);

	
	if($pay_method != ''){
	
		return isset($paymenthodinfo[$pay_method]) ? $paymenthodinfo[$pay_method] : '';
	
	}
	return $paymenthodinfo;

}


function geodir_payment_activation_script()
{
	global $wpdb,$plugin_prefix;
	
	$wpdb->hide_errors();
	
	/**
	 * Include any functions needed for upgrades.
	 *
	 * @since 1.0.0
	 */
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	if($wpdb->query("SHOW TABLES LIKE 'geodir_price'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_price'")==0){$wpdb->query("RENAME TABLE geodir_price TO ".$wpdb->prefix."geodir_price");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_invoice'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_invoice'")==0){$wpdb->query("RENAME TABLE geodir_invoice TO ".$wpdb->prefix."geodir_invoice");}
	if($wpdb->query("SHOW TABLES LIKE 'geodir_coupons'")>0 && $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_coupons'")==0){$wpdb->query("RENAME TABLE geodir_coupons TO ".$wpdb->prefix."geodir_coupons");}	
	
	$collate = '';
	if($wpdb->has_cap( 'collation' ) ) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}
		
// Table for storing place packages  - these are user defined

		$price_table = "CREATE TABLE ".GEODIR_PRICE_TABLE." (
					pid int(11) NOT NULL AUTO_INCREMENT,
					title varchar(255) NOT NULL,
					amount float(12,2) NOT NULL,
					cat text NOT NULL,
					status tinyint(2) NOT NULL DEFAULT '1',
					days int(10) NOT NULL,
					is_default tinyint(4) NOT NULL DEFAULT '0',
					is_featured tinyint(4) NOT NULL DEFAULT '0',
					title_desc text NOT NULL,
					image_limit varchar(255) NOT NULL,
					cat_limit varchar(255) NOT NULL,
					post_type varchar(255) NOT NULL,
					link_business_pkg varchar(255) NOT NULL,
					link_business_cpt varchar(255) NOT NULL,
					recurring_pkg varchar(255) NOT NULL,
					reg_desc_pkg varchar(255) NOT NULL,
					reg_fees_pkg varchar(255) NOT NULL,
					downgrade_pkg varchar(255) NOT NULL,
					sub_active varchar(255) NOT NULL,
					display_order INT( 11 ) NOT NULL DEFAULT '0',
					sub_units varchar(255) NOT NULL,
					sub_units_num varchar(255) NOT NULL,
					sub_num_trial_days varchar(255) NOT NULL,
					sub_num_trial_units varchar(1) NOT NULL DEFAULT 'D',
					sub_units_num_times varchar(255) NOT NULL,
					google_analytics TINYINT( 4 ) NOT NULL DEFAULT '0',
					sendtofriend TINYINT( 4 ) NOT NULL DEFAULT '0',
					use_desc_limit TINYINT( 1 ) NOT NULL DEFAULT '0',
					desc_limit INT( 11 ) NOT NULL DEFAULT '0',
					use_tag_limit TINYINT( 1 ) NOT NULL DEFAULT '0',
					tag_limit INT( 11 ) NOT NULL DEFAULT '0',
					hide_related_tab TINYINT( 1 ) NOT NULL DEFAULT '0',
					has_upgrades TINYINT( 1 ) NOT NULL DEFAULT '1',
					disable_coupon enum('0', '1') NOT NULL DEFAULT '0',
					disable_editor ENUM('0', '1') NOT NULL DEFAULT '0',
					PRIMARY KEY  (pid)) $collate";
					
		$price_table = apply_filters('geodir_payment_package_table' , $price_table);	
		
		dbDelta($price_table);
		
		do_action('geodir_payment_package_table_created' ,$price_table );
	
	/* ------- update post detail table start --- */
	$post_types = geodir_get_posttypes();
	
	if(!empty($post_types)){
	
		foreach($post_types as $post_type){
			
			$package_info = geodir_get_post_package_info_on_listing('', '', $post_type);
			
			$package_id = $package_info->pid;
			
			$table = $plugin_prefix.$post_type.'_detail';
			
			$wpdb->query($wpdb->prepare("UPDATE ".$table." SET package_id=%d WHERE package_id=0",array($package_id)));
			
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages=%d WHERE post_type=%s AND (packages='0' || packages='')",
					array($package_id,$post_type)
				)
			);
			
			$meta_field_add = " ENUM( 'false', 'true' ) NOT NULL DEFAULT 'false'";
			geodir_add_column_if_not_exist( $table, "expire_notification", $meta_field_add );
			geodir_add_column_if_not_exist( $table, "exp2", $meta_field_add );
			geodir_add_column_if_not_exist( $table, "exp3", $meta_field_add );
		}
	}	
		
	// Table for storing place packages  - these are user defined

		$invoice_table = "CREATE TABLE ".INVOICE_TABLE." (  
						 id int( 11  )  NOT  NULL  auto_increment ,
						 type varchar( 100  )  default NULL ,
						 post_id int( 11  )  NOT  NULL ,
						 post_title varchar( 255  )  default NULL ,
						 post_action varchar( 100  )  default NULL ,
						 invoice_type varchar(50) NOT NULL,
						 invoice_callback varchar(255) NOT NULL,
						 invoice_data text NOT NULL,
						 package_id int( 11  )  NOT  NULL ,
						 package_title varchar( 254  )  default NULL ,
						 amount float  default NULL ,
						 alive_days int( 11  )  default NULL ,
						 expire_date datetime  default NULL ,
						 user_id int( 11  )  default NULL ,
						 coupon_code varchar( 100  )  default NULL ,
						 coupon_usage enum('increase', 'decrease') DEFAULT NULL,
						 discount float  default NULL ,
						 tax_amount float(10,2) NOT NULL,
						 paied_amount float  default NULL ,
						 paymentmethod varchar( 100  )  default NULL ,
						 status varchar( 100  )  default NULL ,
						 subscription tinyint(1) NOT NULL DEFAULT '0',
						 HTML text,
						 is_current enum(  '1',  '0'  ) default  '0',
						 date datetime  default NULL ,
						 date_updated datetime NOT NULL,
						 PRIMARY KEY  (id)) $collate";
		$invoice_table = apply_filters('geodir_payment_invoice_table' , $invoice_table);
		
		dbDelta($invoice_table);
		
		do_action('geodir_payment_invoice_table_created' ,$invoice_table ) ;

	
	// Table for storing coupon  - these are user defined

		$coupon_table = "CREATE TABLE ".COUPON_TABLE." (
		cid int(11) NOT NULL AUTO_INCREMENT,
		coupon_code varchar(100) NOT NULL,
		post_types varchar(255) NOT NULL,
		recurring varchar(255) NOT NULL DEFAULT '0',
		discount_type varchar(5) NOT NULL,
		discount_amount float NOT NULL,
		status enum('0','1') NOT NULL DEFAULT '0',
		usage_limit varchar(11) DEFAULT NULL,
		usage_count int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY  (cid)) $collate";
					
		$coupon_table = apply_filters('geodir_payment_coupon_table' , $coupon_table);
		
		dbDelta($coupon_table);
		
		do_action('geodir_payment_coupon_table_created' ,$coupon_table ) ;

	
	//////////paypal settings start////////
	$paymenthodinfo = geodir_get_additional_pay_options();
	
	if(!empty($paymenthodinfo)){
	
		for($i=0;$i<count($paymenthodinfo);$i++)
		foreach($paymenthodinfo as $key => $value)
		{
			
			$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s order by option_id asc", array('payment_method_'.$key));
			
			$paymentinfo = $wpdb->get_results($paymentsql);
			
			if(empty($paymentinfo))
			{
				$value = apply_filters('geodir_payment_'.$key.'_payment_method' ,$value);
				$paymenthodArray = array("option_name"	=>	'payment_method_'.$key, "option_value"	=>	serialize($value),);
				
				$wpdb->insert( $wpdb->options, $paymenthodArray );
			}
		}
		
	}
		
}

function geodir_payment_after_custom_detail_table_create($post_type, $detail_table){
	
	$post_types = geodir_get_posttypes();
	
	if(in_array($post_type, $post_types)){
		$meta_field_add = " ENUM( 'false', 'true' ) NOT NULL DEFAULT 'false'";
		geodir_add_column_if_not_exist( $detail_table, "expire_notification", $meta_field_add );
		geodir_add_column_if_not_exist( $detail_table, "exp2", $meta_field_add );
		geodir_add_column_if_not_exist( $detail_table, "exp3", $meta_field_add );
	}
	
}


function get_payment_options($method)
{
	global $wpdb;
	
	$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s",array('payment_method_'.$method));
	$paymentinfo = $wpdb->get_results($paymentsql);
	
	if($paymentinfo)
	{
		foreach($paymentinfo as $paymentinfoObj)
		{
			$optReturnarr = array();
			$option_value = unserialize($paymentinfoObj->option_value);
			foreach($option_value as $key => $value)
			{
				if($key != 'payOpts')
				{
					$optReturnarr[$key] = $value ;
				}
			}
			
			$paymentOpts = $option_value['payOpts'];
			
			for($i=0;$i<count($paymentOpts);$i++)
			{
				$optReturnarr[$paymentOpts[$i]['fieldname']] = $paymentOpts[$i]['value'];
			}
			return $optReturnarr;
		}
	}
}


function geodir_payment_pre_expiry_notification_days(){

	$expire = array();
	$i = 0;
	while ($i < 61){
		$expire[] = $i;
		$i++;
	}

	return apply_filters('geodir_pre_expiry_notification_days', $expire);
}


function geodir_payment_general_options($arr=array())
{

	$arr[] = array( 'name' => __( 'General Options', 'geodir_payments' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'payment_emails' );
	
	
	$arr[] = array( 'name' => __( 'Listing expiration settings', 'geodir_payments' ), 'type' => 'sectionstart', 'id' => 'expiration_settings_options');
	
	$arr[] = array(  
		'name' => __( 'Enable expiry process?', 'geodir_payments' ),
		'desc' 		=> sprintf(__( ' Enable expiry process? (untick to disable) If you disable this option, none of the place listings will expire in future.', 'geodir_payments' )),
		'id' 		=> 'geodir_listing_expiry',
		'std' 		=> '1',
		'type' 		=> 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	
	$arr[] = array(  
		'name' => '',
		'desc' 		=> __( 'Select the listing status after the place listing expires.', 'geodir_payments' ),
		'tip' 		=> '',
		'id' 		=> 'geodir_listing_ex_status',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'draft',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique( array( 
			'draft' => __( 'draft', 'geodir_payments' ),
			'publish' => __( 'publish', 'geodir_payments' ),
			'trash' => __( 'trash', 'geodir_payments' ),
			))
	);

	$arr[] = array(  
		'name' => __( 'Paid Listing Status', 'geodirectory' ),
		'desc' 		=> __( 'Select the listing status to apply to the listing on payment received for the invoice. Default: Publish.', 'geodir_payments' ),
		'tip' 		=> '',
		'id' 		=> 'geodir_paid_listing_status',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'publish',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options'	=> array( 
			'publish' => __( 'Publish', 'geodir_payments' ),
			'pending' => __( 'Pending Review', 'geodir_payments' ),
			'draft' => __( 'Draft', 'geodir_payments' ),
		)
	);

	$arr[] = array(  
			'name'  => __( 'Enable pre expiry notification to author?', 'geodir_payments' ),
			'desc' 	=> __('Enable pre expiry notification to author? (untick to disable) If you disable the option, pre expiry email notification will stop.', 'geodir_payments' ),
			'id' 	=> 'geodir_listing_preexpiry_notice_disable',
			'type' 	=> 'checkbox',
			'std' 	=> '1' ,// Default value to show home top section
			
		);
		
	$arr[] = array(  
			'name'  => __( 'Display expire date to author in dashboard listings?', 'geodir_payments' ),
			'desc' 	=> __( 'Display expire date to author in dashboard listings? If you tick the option, listing expire date will be displayed to listing author in dashboard listings.', 'geodir_payments' ),
			'id' 	=> 'geodir_payment_expire_date_on_listing',
			'type' 	=> 'checkbox',
			'std' 	=> '0' ,// Default value to show home top section
			
		);
	$arr[] = array(  
			'name'  => __( 'Display expire date to author in listing detail sidebar?', 'geodir_payments' ),
			'desc' 	=> __( 'Display expire date to author in listing detail sidebar? If you tick the option, listing expire date will be displayed to listing author in listing detail sidebar.', 'geodir_payments' ),
			'id' 	=> 'geodir_payment_expire_date_on_detail',
			'type' 	=> 'checkbox',
			'std' 	=> '0' ,// Default value to show home top section
			
		);
		

	$arr[] = array( 'type' => 'sectionend', 'id' => 'expiration_settings_options');
	
	/*
	$arr[] = array( 'name' => __( 'Geo Directory Manage Currency', 'geodir_payments' ), 'type' => 'sectionstart', 'id' => 'payment_general_options');
	
	$arr[] = array(  
		'name' => __( 'Default Currency (Ex.: USD)', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_currency',
		'type' 		=> 'text',
		'css' 		=> 'min-width:200px;',
		'std' 		=> __('USD', 'geodir_payments')
		);
		
	$arr[] = array(  
		'name' => __( 'Default Currency Symbol (Ex.: $)', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_currencysym',
		'type' 		=> 'text',
		'css' 		=> 'min-width:200px;',
		'std' 		=> __('$', 'geodir_payments')
		);
	
	$currency_symbol = geodir_get_currency_sym();
	
	$arr[] = array(
				'name' => __( 'Currency Position', 'geodir_payments' ),
				'desc' => __( 'This controls the position of the currency symbol.', 'geodir_payments' ),
				'id' => 'geodir_payment_currency_position',
				'css' => 'min-width:200px;',
				'std' => 'left',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
								'left' => wp_sprintf(__('Left (%s99.99)', 'geodir_payments'), $currency_symbol),
								'right' => wp_sprintf(__('Right (99.99%s)', 'geodir_payments'), $currency_symbol),
								'left_space' => wp_sprintf(__('Left with space (%s 99.99)', 'geodir_payments'), $currency_symbol),
								'right_space' => wp_sprintf(__('Right with space (99.99 %s)', 'geodir_payments'), $currency_symbol)
							)
			);
	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_general_options');
	$arr[] = array( 'name' => __( 'Payment Invoice Settings', 'geodir_payments' ), 'type' => 'sectionstart', 'id' => 'payment_invoice_options');
	$arr[] = array(  
		'name' => __( 'Minimum digits for the custom invoice id', 'geodir_payments' ),
		'desc' 		=> __( 'Number between 0 to 20. If the invoice id has less digits than this number, it is left padded with 0s.Ex: invoice id 108 will padded to 00108 if digits set to 5. The default 0 means no padding.', 'geodir_payments' ),
		'id' 		=> 'geodir_payment_invoice_threshold',
		'type' 		=> 'text',
		'css' 		=> 'min-width:200px;',
		'std' 		=> '',
		'placeholder' => __( 'Ex: 5', 'geodir_payments' )
		);
	$arr[] = array(  
		'name' => __( 'Display Invoice ID Prefix', 'geodir_payments' ),
		'desc' 		=> __( 'Prefix will be added before invoice id to customize. Ex: GD-00108 or GD-INV-108 (mexlength upto 10 chars)', 'geodir_payments' ),
		'id' 		=> 'geodir_payment_invoice_prefix',
		'type' 		=> 'text',
		'css' 		=> 'min-width:200px;',
		'std' 		=> '',
		'placeholder' => __( 'Ex: GD-', 'geodir_payments' )
		);
	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_general_options');
	*/
	$arr = apply_filters('payment_invoice_options' ,$arr );
	
	
	return $arr;
}


function geodir_payment_notifications($arr=array())
{

	$arr[] = array( 'name' => __( 'Payment Emails', 'geodir_payments' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'payment_emails' );
	
	$arr[] = array( 'name' => __( 'Client Emails', 'geodir_payments' ), 'type' => 'sectionstart', 'id' => 'payment_client_emails');
	
	$arr[] = array(  
		'name' => __( 'Payment success to client email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_client_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Acknowledgment for your Payment', 'geodir_payments')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_client_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],</p><p>Payment has been successfully received. Your details are below</p><p>[#transaction_details#]</p><br><p>We hope you enjoy. Thanks!</p><p>[#site_name#]</p>", 'geodir_payments')
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post renew success to client email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Renewal of listing ID:#[#post_id#]', 'geodir_payments') // Default value for the page title - changed in settings
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __('<p>Dear [#client_name#],</p><p>Your listing [#listing_link#] has been renewed.</p><p>NOTE: If your listing is not active yet your payment may be being checked by an admin and it will be activated shortly.</p><br><p>[#site_name#]</p>', 'geodir_payments')
		);
	
	$arr[] = array(  
		'name' => __( 'Post Upgrade Success to Client Email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Upgrade of listing ID:#[#post_id#]', 'geodir_payments')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],</p><p>Your listing [#listing_link#] has been upgraded.</p><p>NOTE: If your listing is not active yet your payment may be being checked by an admin and it will be activated shortly.</p><br><p>[#site_name#]</p>", 'geodir_payments')
		);
	$arr[] = array(  
		'name' => __( 'Send invoice to client email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_payment_invoice_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('[#site_name#] - Invoice Details #[#invoice_id#]', 'geodir_payments')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_payment_invoice_email_body',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __('<p>Dear [#client_name#],</p><p>Here is details for your invoice <a href="[#invoice_link#]">#[#invoice_id#] - [#invoice_title#]</a> at <a href="[#site_name_url#]">[#site_name#]</a>.</p><p><b>Invoice Details:</b></p><p>Type: [#invoice_type#]</p><p>Date: [#invoice_date#]</p><p>Status: [#invoice_status#]</p><p>Payment Method: [#payment_method#]</p><p>Payable Amount: [#invoice_amount#]</p>[#invoice_discount_details#][#invoice_listing_details#][#invoice_package_details#][#invoice_transaction_details#]<p>---</p><p>Thank you for your contribution.</p><p><a href="[#site_name_url#]">[#site_name#]</a></p>', 'geodir_payments')
		);
	
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_client_emails');
    
    $arr[] = array( 'name' => __( 'Pre Expiry Notifications To Clients', 'geodir_payments' ), 'type' => 'sectionstart', 'id' => 'payment_preexpire_client_emails');
    $arr[] = array(  
        'name' => __( 'Enable pre expiry notice days (first)', 'geodir_payments' ),
        'desc' => __( 'Select number of days before first pre expiry notification email will be sent.', 'geodir_payments' ),
        'id' => 'geodir_listing_preexpiry_notice_days',
        'css' => 'min-width:200px;',
        'std' => '5',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => geodir_payment_pre_expiry_notification_days()
    );
    $arr[] = array(  
        'name' => __( 'Listing expiration email (first)', 'geodir_payments' ),
        'desc' => '',
        'id' => 'geodir_renew_email_subject',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'Place listing expiration Notification' // Default value for the page title - changed in settings
    );
    $arr[] = array(  
        'name' => '',
        'desc' => '',
        'id' => 'geodir_renew_email_content',
        'css' => 'width:500px; height: 150px;',
        'type' => 'textarea',
        'std' => "<p>Dear [#client_name#],<p><p>Your listing - [#listing_link#] posted on  <u>[#posted_date#]</u> for [#number_of_days#] days.</p><p>It's going to expiry after [#number_of_grace_days#] day(s). If the listing expire, it will no longer appear on the site.</p><p> If you want to renew, Please login to your member area of our site and renew it as soon as it expire.</p><p>You may like to login the site from [#login_url#].</p><p>Your login ID is <b>[#username#]</b> and Email ID is <b>[#user_email#]</b>.</p><p>Thank you,<br />[#site_name_url#].</p>"
    );
    $arr[] = array(  
        'name' => __( 'Enable pre expiry notice days (second)', 'geodir_payments' ),
        'desc' => __( 'Select number of days before second pre expiry notification email will be sent. Select 0 to disable notification.', 'geodir_payments' ),
        'id' => 'geodir_listing_preexpiry_notice_days2',
        'css' => 'min-width:200px;',
        'std' => '0',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => geodir_payment_pre_expiry_notification_days()
    );
    $arr[] = array(  
        'name' => __( 'Listing expiration email (second)', 'geodir_payments' ),
        'desc' => '',
        'id' => 'geodir_renew_email_subject2',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'Place listing expiration Notification'
    );
    $arr[] = array(  
        'name' => '',
        'desc' => '',
        'id' => 'geodir_renew_email_content2',
        'css' => 'width:500px; height: 150px;',
        'type' => 'textarea',
        'std' => "<p>Dear [#client_name#],<p><p>Your listing - [#listing_link#] posted on  <u>[#posted_date#]</u> for [#number_of_days#] days.</p><p>It's going to expiry after [#number_of_grace_days#] day(s). If the listing expire, it will no longer appear on the site.</p><p> If you want to renew, Please login to your member area of our site and renew it as soon as it expire.</p><p>You may like to login the site from [#login_url#].</p><p>Your login ID is <b>[#username#]</b> and Email ID is <b>[#user_email#]</b>.</p><p>Thank you,<br />[#site_name_url#].</p>"
    );
    
    $arr[] = array(  
        'name' => __( 'Enable pre expiry notice days (third)', 'geodir_payments' ),
        'desc' => __( 'Select number of days before third pre expiry notification email will be sent. Select 0 to disable notification.', 'geodir_payments' ),
        'id' => 'geodir_listing_preexpiry_notice_days3',
        'css' => 'min-width:200px;',
        'std' => '0',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => geodir_payment_pre_expiry_notification_days()
    );
    $arr[] = array(  
        'name' => __( 'Listing expiration email (third)', 'geodir_payments' ),
        'desc' => '',
        'id' => 'geodir_renew_email_subject3',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'Place listing expiration Notification'
    );
    $arr[] = array(  
        'name' => '',
        'desc' => '',
        'id' => 'geodir_renew_email_content3',
        'css' => 'width:500px; height: 150px;',
        'type' => 'textarea',
        'std' => "<p>Dear [#client_name#],<p><p>Your listing - [#listing_link#] posted on  <u>[#posted_date#]</u> for [#number_of_days#] days.</p><p>It's going to expiry after [#number_of_grace_days#] day(s). If the listing expire, it will no longer appear on the site.</p><p> If you want to renew, Please login to your member area of our site and renew it as soon as it expire.</p><p>You may like to login the site from [#login_url#].</p><p>Your login ID is <b>[#username#]</b> and Email ID is <b>[#user_email#]</b>.</p><p>Thank you,<br />[#site_name_url#].</p>"
    );
    $arr[] = array( 'type' => 'sectionend', 'id' => 'payment_preexpire_client_emails');

	$arr[] = array( 'name' => __( 'Admin Emails', 'geodir_payments' ), 'type' => 'sectionstart', 'id' => 'payment_admin_emails');
	
	
	$arr[] = array(  
		'name' => __( 'Payment success to admin email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_admin_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Payment received successfully', 'geodir_payments')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_success_admin_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,</p><p>Payment has been received. Below are the transaction details.</p><p>[#transaction_details#]</p><br><p>[#site_name#]</p>", 'geodir_payments')
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post renewal success to admin email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_subject_admin',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Renewal of listing ID:#[#post_id#]', 'geodir_payments') // Default value for the page title - changed in settings
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_renew_success_email_content_admin',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __('<p>Dear Admin,</p><p>Listing [#listing_link#] has been renewed. Please confirm payment and then update the listings published date to todays date. </p><p>NOTE: If payment was made by paypal the "published date" should be updated automatically. </p><br><p>[#site_name#]</p>', 'geodir_payments')
		);
	
	
	$arr[] = array(  
		'name' => __( 'Post upgrade success to admin email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_subject_admin',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Upgrade of listing ID:#[#post_id#]', 'geodir_payments')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_upgrade_success_email_content_admin',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,</p><p>Listing [#listing_link#] has been upgraded. Please confirm payment and then update the listings published date to todays date. </p><p>NOTE: If payment was made by paypal the \"published date\" should be updated automatically. </p><br><p>[#site_name#]</p>", 'geodir_payments')
		);
	
	
	$arr[] = array(  
		'name' => __( 'Payment fail to admin email', 'geodir_payments' ),
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_fail_admin_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('IPN INVALID - Place Listing Submitted', 'geodir_payments')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_post_payment_fail_admin_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,</p><p>Paypal IPN Invalid for listing ID: #[#post_id#]</p><p>Please manually check your paypal logs, and if payment was received manually publish the listing.</p><p>[#listing_link#]</p><br><p>[#site_name#]</p>", 'geodir_payments')
		);
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'payment_admin_emails');
	
	$arr = apply_filters('geodir_payment_notifications' ,$arr );
	
	return $arr;
}


function geodir_enable_editor_on_payment_notifications($notification){
	
	if(!empty($notification) && get_option('geodir_tiny_editor')=='1'){
		
		foreach($notification as $key => $value){
			if($value['type'] == 'textarea')
				$notification[$key]['type'] = 'editor';
		}
		
	}
	
	return $notification;
}


function geodir_get_currency_sym() {
	$currencysym = get_option('geodir_currencysym');
	
	$currencysym = $currencysym ? stripslashes_deep($currencysym) : '$';
	
	return $currencysym;
}

function geodir_get_currency_type() {
	$currency = get_option('geodir_currency');
	
	$currency = $currency ? stripslashes_deep($currency) : 'USD';
	
	return $currency;
}

/**
 * Get the currency symbol position.
 *
 * @since 1.3.6
 *
 * @return string The currency position.
 */
function geodir_payment_get_currency_position() {
	$currency_position = get_option('geodir_payment_currency_position');
	
	$currency_position = $currency_position != '' ? $currency_position : 'left';
	
	return $currency_position;
}

function geodir_package_list_info($post_type = '', $pkgid = '')
{

	global $wpdb;
	
	$subsql = '';
	
	if($pkgid)
		$subsql .= " and pid = '$pkgid' ";	
		
	if($post_type)
		$subsql .= " and post_type = '$post_type'";		

	$pricesql = "select * from ".GEODIR_PRICE_TABLE." where status=1 $subsql ORDER BY `display_order` ASC, `amount` ASC";
	
	$pricesql = apply_filters('geodir_package_list_query' ,$pricesql ) ;
	return $priceinfo = $wpdb->get_results($pricesql);

}

function geodir_get_post_package_info( $pkg_id = '', $pid = '' ) {	
	global $wpdb;
	
	$post_type = '';
	if( $pkg_id == '' && $pid != '' ) {
		$gd_post_info = geodir_get_post_info( $pid );
		if ( !empty( $gd_post_info ) && isset( $gd_post_info->package_id ) && $gd_post_info->package_id > 0 ) {
			$pkg_id = $gd_post_info->package_id;
			$post_type = $gd_post_info->post_type;
		}
	}
	
	// get price package info
	$priceinfo = geodir_get_package_info( $pkg_id );
	
	if ( !empty($priceinfo) && is_array( $priceinfo ) ) {
		$priceinfo = (object)$priceinfo;
	}
		
	$info = array();
	if( !empty( $priceinfo ) ) {
		$priceinfoObj = $priceinfo;
		$info['pid'] = $priceinfoObj->pid;
		$info['title'] = __(stripslashes_deep($priceinfoObj->title), 'geodirectory');
		$info['amount'] = $priceinfoObj->amount;
		$info['cat'] =$priceinfoObj->cat;
		$info['status'] = $priceinfoObj->status;
		$info['days'] = $priceinfoObj->days;
		$info['is_default'] = $priceinfoObj->is_default;
		$info['is_featured'] = $priceinfoObj->is_featured;
		$info['title_desc'] = __(stripslashes_deep($priceinfoObj->title_desc), 'geodirectory');
		$info['image_limit'] = $priceinfoObj->image_limit;
		$info['cat_limit'] = $priceinfoObj->cat_limit;
		$info['post_type'] = $priceinfoObj->post_type;
		$info['link_business_pkg'] = $priceinfoObj->link_business_pkg;
		$info['link_business_cpt'] = $priceinfoObj->link_business_cpt;
		$info['recurring_pkg'] = $priceinfoObj->recurring_pkg;
		$info['reg_desc_pkg'] = $priceinfoObj->reg_desc_pkg;
		$info['reg_fees_pkg'] = $priceinfoObj->reg_fees_pkg;
		$info['downgrade_pkg'] = $priceinfoObj->downgrade_pkg;
		$info['sub_active'] = $priceinfoObj->sub_active;
		$info['sub_units'] = $priceinfoObj->sub_units;
		$info['sub_units_num'] = $priceinfoObj->sub_units_num;
		$info['sub_num_trial_days'] = $priceinfoObj->sub_num_trial_days;
		$info['sub_num_trial_units'] = isset( $priceinfoObj->sub_num_trial_units ) && !empty( $priceinfoObj->sub_num_trial_units ) ? $priceinfoObj->sub_num_trial_units : 'D';
		$info['sub_units_num_times'] = $priceinfoObj->sub_units_num_times;
		$info['google_analytics'] = $priceinfoObj->google_analytics;
		$info['sendtofriend'] = $priceinfoObj->sendtofriend;
		$info['use_desc_limit'] = $priceinfoObj->use_desc_limit;
		$info['desc_limit'] = $priceinfoObj->desc_limit;
		$info['use_tag_limit'] = $priceinfoObj->use_tag_limit;
		$info['tag_limit'] = $priceinfoObj->tag_limit;
		$info['hide_related_tab'] = isset( $priceinfoObj->hide_related_tab ) ? (int)$priceinfoObj->hide_related_tab : 0;
		$info['has_upgrades'] = isset($priceinfoObj->has_upgrades) ? (int)$priceinfoObj->has_upgrades : 1;
		$info['disable_coupon'] = isset($priceinfoObj->disable_coupon) ? (bool)$priceinfoObj->disable_coupon : 0;
		$info['disable_editor'] = !empty($priceinfoObj->disable_editor) ? true : false;
	}
	return $info;
}

function geodir_get_package_info_by_id( $pid, $status = '1' ) {
	global $wpdb;
	
	if ( !$pid > 0 ) {
		return NULL;
	}
	
	$where = '';
	if ( $status == '1' ) {
		$where = "AND status = '1'";
	} else if ( $status == '0' ) {
		$where = "AND status != '1'";
	}
	
	$query = $wpdb->prepare( "SELECT * FROM " . GEODIR_PRICE_TABLE . " WHERE pid = %d " . $where, array( $pid ) );
	$row = $wpdb->get_row( $query );
	
	return $row;	
}

function geodir_get_package_info( $package_id ) {
	global $wpdb, $geodir_get_package_info_cache;
	if(!$package_id){return;}
	if ( is_numeric( $package_id ) && is_array( $geodir_get_package_info_cache ) && !empty( $geodir_get_package_info_cache ) && isset( $geodir_get_package_info_cache[$package_id] ) ) {
		return $geodir_get_package_info_cache[$package_id];
	}
	
	// get price package info
	$priceinfo = geodir_get_package_info_by_id( $package_id );
	
	if ( $priceinfo && !is_wp_error( $priceinfo ) ) {
		$info = apply_filters( 'geodir_package_info', $priceinfo, $package_id );
		$geodir_get_package_info_cache[$package_id] = $info;
		return $info;
	} else {
		return false;
	}
}

function geodir_get_default_package($post_type){
	global $wpdb;
	
	$post_types = geodir_get_posttypes();
	
	if(!$wpdb->get_var($wpdb->prepare("SELECT pid FROM ".GEODIR_PRICE_TABLE." WHERE post_type=%s", array($post_type))) && in_array($post_type, $post_types))
	{			
					
		$price_insert = "INSERT INTO ".GEODIR_PRICE_TABLE." (`title`, `amount`, `days`, `status`, `is_default`, `cat`, `is_featured`, `title_desc`, `image_limit`, `cat_limit`, `google_analytics`, `sendtofriend`, `post_type`, `link_business_pkg`, `link_business_cpt`, `recurring_pkg`, `reg_desc_pkg`, `reg_fees_pkg`, `downgrade_pkg`) VALUES ('".__('Free', 'geodir_payments')."', 0.00, 0, 1, 1, '', 0, '".__('Free: number of publish days are unlimited (0.00 '.geodir_get_currency_type().')', 'geodir_payments')."', '', '', 0, 1, '".$post_type."', 0, 1, 0, 0, 0, '')";
		
		$wpdb->query($price_insert);
		
	}
	
	$pricesql = $wpdb->prepare("SELECT * FROM ".GEODIR_PRICE_TABLE." WHERE status = '1' AND is_default = '1' AND post_type = %s", array($post_type));
	
	$priceinfo = $wpdb->get_row($pricesql);
	
	if($priceinfo && !is_wp_error($priceinfo) )
		return apply_filters('geodir_default_package_info' , $priceinfo);
	else
		return false;	
}


function geodir_get_post_package_info_on_listing($info, $post, $post_type = ''){
	// if post is array convert to object
	if(!is_object($post) && !empty($post)){  $post = json_decode(json_encode($post), FALSE);}

	$listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';
	$package_id = '';

	if(!is_object($post) && isset($post['post_type']) && $post['post_type'] != '')
		$listing_type = $post['post_type'];
	
	if(is_object($post) && isset($post->ID) && isset($post->package_id)){
		$package_id = $post->package_id;
	}
		
	if(isset($_REQUEST['package_id'])){
		
		$package_id = $_REQUEST['package_id'];
		
	}elseif(isset($post->package_id) || ((isset($_REQUEST['post_type']) || isset($post->post_type)) && $package_id ) || (isset($post->ID) && $package_id = geodir_get_post_meta($post->ID,'package_id')) ){
		
		$listing_type = isset($post->post_type) ? $post->post_type : $listing_type;
		$package_id = isset($post->package_id) ? $post->package_id : $package_id;
		
	}elseif(($listing_type != '' && isset($post->pid) && $post->pid != '') || (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '' && !isset($_REQUEST['post_type']))){
		
		$post_id = isset($post->pid) ? $post->pid : $_REQUEST['pid'];
		
		$package_id = geodir_get_post_meta($post_id,'package_id');
		
	}
	
	if(empty($package_id)){
		
		if(empty($listing_type))
			$listing_type = isset($post->post_type) ? $post->post_type : '';
		
		$all_postypes = geodir_get_posttypes();
		
		if($post_type != '' && in_array($post_type, $all_postypes))
			$listing_type = $post_type;
		
		$default_package = geodir_get_default_package($listing_type);
		
		if(!empty($default_package))
			$package_id = $default_package->pid;
		
	}
	
	return $info = geodir_get_package_info($package_id);

}

function geodir_create_invoice( $data = array() ) {
	global $wpdb, $current_user;
	
	if ( empty( $data ) || !is_array( $data ) ) {
		return NULL;
	}
	
	$data = apply_filters( 'geodir_payment_invoice_params', $data, false ); // false => create
	
	if ( isset( $data['id'] ) ) {
		unset( $data['id'] );
	}
	
	$date = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
	$data['date'] = $date;
	
	$data = wp_unslash( $data );
	
	if ( empty( $data ) ) {
		return NULL;
	}
	
	if ( !isset( $data['user_id'] ) ) {
		$data['user_id'] = $current_user->data->ID;
	}
	
	if ( false === $wpdb->insert( INVOICE_TABLE, $data ) ) {
		return NULL;
	}
	$invoice_id = (int)$wpdb->insert_id;
	
	do_action( 'geodir_payment_invoice_created', $invoice_id );
	
	return $invoice_id;			
} 

function geodir_update_invoice( $data = array() ) {
	global $wpdb, $current_user;
	
	if ( empty( $data ) || !is_array( $data ) ) {
		return NULL;
	}

	$data = apply_filters( 'geodir_payment_invoice_params', $data );
	
	$invoice_id = isset( $data['id'] ) ? $data['id'] : NULL;
	if ( isset( $data['id'] ) ) {
		unset( $data['id'] );
	}
	
	$date = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
	$data['date_updated'] = $date;
	
	$data = wp_unslash( $data );
	
	if ( empty( $data ) ) {
		return NULL;
	}

	if ( false === $wpdb->update( INVOICE_TABLE, $data, array( 'id' => $invoice_id ) ) ) {
		return NULL;
	}
	
	do_action( 'geodir_payment_invoice_updated', $invoice_id );
	
	return $invoice_id;			
}

function geodir_get_invoice($id = ''){
	global $wpdb;
	
	
	$invoice = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".INVOICE_TABLE." WHERE id = %d ", array($id)));

	if(!empty($invoice))
		return $invoice;
	else
		return false;	
}

function geodir_update_invoice_status( $invoice_id, $new_status = '', $subscription = '' ) {
	global $wpdb;
	
	$new_status = $new_status != '' ? geodir_strtolower( $new_status ) : '';
	
	if ( !$invoice_id > 0 || $new_status == '' ) {
		return false;
	}
	
	$all_status = geodir_payment_all_payment_status();
	
	$invoice_info = geodir_get_invoice( $invoice_id );
	
	if ( in_array( $new_status, $all_status ) && !empty( $invoice_info ) ) {	
		$old_status = $invoice_info->status;
		
		if ( $new_status != $old_status || $subscription) {
			$sql_subscription = $subscription !== '' ? ", subscription = " . (int)$subscription : '';
			
			$query = $wpdb->prepare( "UPDATE `" . INVOICE_TABLE . "` SET `status` = %s " . $sql_subscription . " WHERE id = %d ", array( $new_status, $invoice_id ) );
			$wpdb->query( $query );
			
			geodir_payment_invoice_coupon_usage_count($invoice_id);
			
			do_action( 'geodir_payment_invoice_status_changed', $invoice_id, $new_status, $old_status, (bool)$subscription );
			
			return $invoice_id;
		}
	}
	
	return false;
}

function geodir_payment_invoice_status_changed( $invoice_id, $new_status, $old_status = 'pending', $subscription = false ) {
	$invoice_info = geodir_get_invoice( $invoice_id );
	
	if ( !empty( $invoice_info ) && ($new_status != $old_status || $subscription) ) {
		do_action( 'geodir_payment_invoice_callback_' . $invoice_info->invoice_callback, $invoice_id, $new_status, $old_status, $subscription );
	}
}
add_action( 'geodir_payment_invoice_status_changed', 'geodir_payment_invoice_status_changed', 10, 4 );

function geodir_payment_invoice_callback_add_listing( $invoice_id, $new_status, $old_status = 'pending', $subscription = false ) {
	global $wpi_has_free_trial;

	$invoice_info = geodir_get_invoice( $invoice_id );

	if ( empty( $invoice_info ) ) {
		return false;
	}

    if (  $new_status == $old_status && !$subscription) {
        return false;
    }

	$invoice_package_id = $invoice_info->package_id;
	$invoice_alive_days = $invoice_info->alive_days;
	$invoice_type		= $invoice_info->invoice_type;
	
	$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	
	$post_id = $invoice_info->post_id;
	$gd_post_info = geodir_get_post_info( $post_id );
	
	if ( empty( $gd_post_info ) ) {
		return false;
	}

	$package_info = geodir_get_package_info( $gd_post_info->package_id );
	
	$post_status = get_post_status( $post_id );
	$post_package_id = $gd_post_info->package_id;
	$post_expire_date = $gd_post_info->expire_date;
	
	$alive_days = 1;
	$sub_num_trial_days = 0;
	
	if ( !empty( $package_info ) ) {
		$sub_num_trial_days = (int)geodir_payment_get_units_to_days( (int)$package_info->sub_num_trial_days, $package_info->sub_num_trial_units );
		$alive_days = (int)geodir_payment_get_units_to_days( (int)$package_info->sub_units_num, $package_info->sub_units );
	}
	
	if ( $new_status == 'confirmed' ) {
		$post_default_status = geodir_payment_paid_listing_status();
		
		$update_post = array();
		
		if ( $subscription ) {
			$payment_date = date_i18n( 'Y-m-d', strtotime( $invoice_info->date ) );
			
			$date_diff = round( abs( strtotime( $payment_date ) - strtotime( $current_date ) ) / 86400 );
			
			// Update post status
			if ( $sub_num_trial_days > 0 || $alive_days > 0 ) {
				geodir_set_post_status( $post_id, $post_default_status );
				
				if ( $post_status != 'publish' ) {
					$update_post['ID'] = $post_id;
					$update_post['post_date'] = current_time( 'mysql' );
					$update_post['post_date_gmt'] = current_time( 'mysql', 1 );
				}
			}
			if ( $sub_num_trial_days > 0 && $wpi_has_free_trial && $invoice_type != 'renew_listing' ) {
				$alive_days = $sub_num_trial_days;
			}

			$expire_date = date_i18n( 'Y-m-d', strtotime( $current_date . '+' . $alive_days . ' days' ) );

			geodir_save_post_meta( $post_id, 'expire_date', $expire_date );
		} else {
			// Update post status
			geodir_set_post_status( $post_id, $post_default_status );
		
			if ( !empty( $invoice_package_id ) && $invoice_alive_days > 0 && $invoice_package_id == $post_package_id && geodir_strtolower( $post_expire_date ) != 'never' && strtotime( $post_expire_date ) >= strtotime( $current_date ) && $post_status == 'publish' ) {
				$expire_date = date_i18n( 'Y-m-d', strtotime( $post_expire_date . ' + ' . $invoice_alive_days . ' days' ) );
				if ($gd_post_info->alive_days > 0) {
					$alive_days = (int)($gd_post_info->alive_days + $invoice_alive_days);
				} else {
					$alive_days = round((int)abs(strtotime($expire_date) - strtotime(date_i18n('Y-m-d', time()))) / DAY_IN_SECONDS);
					$alive_days = $alive_days > 0 ? $alive_days : 1;
				}
			} else {
				if ( $post_status != 'publish' ) {
					$update_post['ID'] = $post_id;
					$update_post['post_date'] = current_time( 'mysql' );
					$update_post['post_date_gmt'] = current_time( 'mysql', 1 );
				}
				
				$alive_days = (int)$gd_post_info->alive_days;
				
				if ( geodir_strtolower( $post_expire_date ) != 'never' && strtotime( $post_expire_date ) < strtotime( $current_date ) ) {
					$alive_days = $invoice_alive_days;
				}
				
				$expire_date = $alive_days > 0 ? date_i18n( 'Y-m-d', strtotime( $current_date . ' + ' . $alive_days . ' days' ) ) : 'Never';
			}
		}
		
		geodir_save_post_meta( $post_id, 'alive_days', $alive_days);
		geodir_save_post_meta( $post_id, 'expire_date', $expire_date);
				
		// Update the post into the database
		if ( !empty( $update_post ) ) {
			wp_update_post( $update_post );
		}
		
		$auther_id = !empty($gd_post_info->post_author) ? $gd_post_info->post_author : $invoice_info->user_id;
		$author_data = get_userdata($auther_id);
		
		$post_status = get_post_status( $post_id );
		
		if ($post_status == 'publish' && !empty($author_data)) {
			if ($invoice_type == 'upgrade_listing') {
				geodir_payment_clientEmail($post_id, $auther_id, 'payment_upgrade');
				geodir_payment_adminEmail($post_id, $auther_id, 'payment_upgrade');
			} else if ($invoice_type == 'renew_listing') {
				geodir_payment_clientEmail($post_id, $auther_id, 'payment_renew');
				geodir_payment_adminEmail($post_id, $auther_id, 'payment_renew');
			}
		}
	} else if ( $new_status == 'cancelled' ) {
		if ( !( get_post_meta( $post_id, '_gdpm_recurring', true ) || $subscription || !empty( $package_info->sub_active ) ) || !get_option('geodir_listing_expiry') ) {
			geodir_set_post_status( $post_id, 'draft' );
		}
	} else if ( $new_status == 'refunded' ) {
		if ( get_option('geodir_listing_expiry') ) {
			global $gd_skip_preexpiry, $gd_posts_to_expire;

			$gd_skip_preexpiry = true;
			$gd_posts_to_expire = array( $post_id );
			update_post_meta( $post_id, '_gdpm_recurring', false );

			geodir_expire_check();

			$gd_skip_preexpiry = false;
			$gd_posts_to_expire = NULL;
		} else {
			geodir_set_post_status( $post_id, 'draft' );
		}
	} else if ( $new_status == 'failed' ) {
		geodir_set_post_status( $post_id, 'draft' );
	} else if ( $new_status == 'onhold' ) {
		geodir_set_post_status( $post_id, 'draft' );
	}
	
	return true;
}
add_action( 'geodir_payment_invoice_callback_add_listing', 'geodir_payment_invoice_callback_add_listing', 10, 4 );
add_action( 'geodir_payment_invoice_callback_upgrade_listing', 'geodir_payment_invoice_callback_add_listing', 10, 4 );
add_action( 'geodir_payment_invoice_callback_renew_listing', 'geodir_payment_invoice_callback_add_listing', 10, 4 );

function geodir_update_invoice_transaction_details($id = '', $html = ''){
    global $wpdb;

    if ($id != '' && $html != '') {
        if ( $wpdb->query($wpdb->prepare("UPDATE ".INVOICE_TABLE." SET `html` = %s WHERE id = %d ",array($html,$id))) )
            do_action('geodir_payment_invoice_transaction_details_changed', $id, $html);

        return $id;
    }

    return false;
}

function geodir_downgrade_packages_list($post_type='')
{
	global $wpdb;
	$subsql = '';
	if(isset($pro_type) && !empty($pro_type))
	{
		$subsql = " and post_type='$post_type'";	
	}
	$pricesql = "select * from ".GEODIR_PRICE_TABLE." where status=1 $subsql";
	$priceinfo = $wpdb->get_results($pricesql);
	return $priceinfo;
}

// TEMP CHANGE ADMINEMAIL FUNCTION NAME --------
function geodir_payment_adminEmail($post_id, $user_id, $message_type, $extra = '') {
	$login_details = '';
	$to_message = '';
	$to_subject = '';
					
	if ($message_type == 'payment_success') {
		$subject = get_option('geodir_post_payment_success_admin_email_subject'); 
		$message = get_option('geodir_post_payment_success_admin_email_content'); 
	} elseif ($message_type == 'payment_fail') {
		$subject = get_option('geodir_post_payment_fail_admin_email_subject'); 
		$message = get_option('geodir_post_payment_fail_admin_email_content');
	} elseif ($message_type == 'payment_upgrade') {
		$subject = get_option('geodir_post_upgrade_success_email_subject_admin'); 
		$message = get_option('geodir_post_upgrade_success_email_content_admin');
	} elseif ($message_type == 'payment_renew') {
		$subject = get_option('geodir_post_renew_success_email_subject_admin'); 
		$message = get_option('geodir_post_renew_success_email_content_admin');
	} else {
		return false;
	}
	
	if (!empty($subject)) {
		$subject = __(stripslashes_deep($subject), 'geodirectory');
	}

	if (!empty($message)) {
		$message = __(stripslashes_deep($message), 'geodirectory');
	}
	
	$user_info = get_userdata($user_id);
	
	$toEmail =  get_option('site_email');
	$toEmailName = get_site_emailName();
	
	$sitefromEmail = get_option('site_email');
	$sitefromEmailName = get_site_emailName();
	
	$productlink = get_permalink($post_id);
	
	$post_info = get_post($post_id);
	
	$posted_date = $post_info->post_date;
	$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
	$siteurl = home_url();
	$siteurl_link = '<a href="'.$siteurl.'">'.$siteurl.'</a>';
	$loginurl = geodir_login_url();
	$loginurl_link = '<a href="'.$loginurl.'">login</a>';
	
	$fromEmail = $sitefromEmail;
	$fromEmailName = $sitefromEmailName;
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#login_url#]','[#login_details#]','[#client_name#]', '[#posted_date#]', '[#transaction_details#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$loginurl_link,$login_details,$toEmailName, $posted_date, $extra);
	$message = str_replace($search_array,$replace_array,$message);
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]', '[#posted_date#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName, $posted_date);
	$subject = str_replace($search_array,$replace_array,$subject);
	
	$headers  = array();
	$headers[] = 'Content-type: text/html; charset=UTF-8';
	$headers[] = 'From: '.$fromEmailName.' <'.$fromEmail.'>';

	/**
	 * Filter the admin email to address.
	 *
	 * @since 1.4.0
	 * @package GeoDirectory_Payment_Manager
	 * @param string $toEmail The email address the email is being sent to.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$toEmail = apply_filters('geodir_payment_adminEmail_to',$toEmail,$post_id, $user_id, $message_type, $extra);
	/**
	 * Filter the admin email subject.
	 *
	 * @since 1.4.0
	 * @package GeoDirectory_Payment_Manager
	 * @param string $subject The email subject.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$subject = apply_filters('geodir_payment_adminEmail_subject',$subject,$post_id, $user_id, $message_type, $extra);
	/**
	 * Filter the admin email message.
	 *
	 * @since 1.4.0
	 * @package GeoDirectory_Payment_Manager
	 * @param string $message The email message text.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$message = apply_filters('geodir_payment_adminEmail_message',$message,$post_id, $user_id, $message_type, $extra);
	/**
	 * Filter the admin email headers.
	 *
	 * @since 1.4.0
	 * @since 2.0.0 $headers changed from string to array.       
	 * @package GeoDirectory_Payment_Manager
	 * @param array $headers The email headers.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$headers = apply_filters('geodir_payment_adminEmail_headers',$headers,$post_id, $user_id, $message_type, $extra);
	
	$sent = wp_mail($toEmail, $subject, $message, $headers);
	if( !$sent && function_exists( 'geodir_error_log' ) ) {
		if ( is_array( $toEmail ) ) {
			$toEmail = implode( ',', $toEmail );
		}
		$log_message = sprintf(
			__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
			$message_type,
			date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
			$toEmail,
			$subject
		);
		geodir_error_log( $log_message );
	}
}

if (!function_exists('geodir_get_post_meta')) {
	function geodir_get_post_meta( $post_id, $meta_key, $single = false ) {
		if (!$post_id) {
			return false;
		}
		global $wpdb,$plugin_prefix;
		
		$all_postypes = geodir_get_posttypes();
		
		$post_type = get_post_type( $post_id );
		
		if (!in_array($post_type, $all_postypes)) {
			return false;
		}
		
		$table = $plugin_prefix . $post_type . '_detail';
		
		if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = '" . $meta_key . "'") != '') {
            $meta_value = $wpdb->get_var($wpdb->prepare("SELECT " . $meta_key . " from " . $table . " where post_id = %d", array($post_id)));
            
            if ($meta_value && $meta_value !== '') {
                return maybe_serialize($meta_value);
            } else
                return $meta_value;
        } else {
            return false;
        }
	}
}

function geodir_payment_clientEmail($post_id, $user_id, $message_type, $extra = '') {
	$login_details = '';
	$to_message = '';
	$to_subject = '';
	
	$number_of_grace_days = 0;
	
	if ($message_type == 'payment_success') {
		$subject = get_option('geodir_post_payment_success_client_email_subject'); 
		$message = get_option('geodir_post_payment_success_client_email_content'); 
	} elseif ($message_type == 'expiration') {
		$subject = get_option('geodir_renew_email_subject'); 
		$message = get_option('geodir_renew_email_content');
		$number_of_grace_days = (int)get_option('geodir_listing_preexpiry_notice_days');
	} elseif ($message_type == 'expiration2') {
		$subject = get_option('geodir_renew_email_subject2'); 
		$message = get_option('geodir_renew_email_content2');
		$number_of_grace_days = (int)get_option('geodir_listing_preexpiry_notice_days2');
	} elseif ($message_type == 'expiration3') {
		$subject = get_option('geodir_renew_email_subject3'); 
		$message = get_option('geodir_renew_email_content3');
		$number_of_grace_days = (int)get_option('geodir_listing_preexpiry_notice_days3');
	} elseif ($message_type == 'payment_upgrade') {
		$subject = get_option('geodir_post_upgrade_success_email_subject'); 
		$message = get_option('geodir_post_upgrade_success_email_content');
	} elseif ($message_type == 'payment_renew') {
		$subject = get_option('geodir_post_renew_success_email_subject'); 
		$message = get_option('geodir_post_renew_success_email_content');
	} else {
		return false;
	}
	
	if (!empty($subject)) {
		$subject = __(stripslashes_deep($subject), 'geodirectory');
	}

	if (!empty($message)) {
		$message = __(stripslashes_deep($message), 'geodirectory');
	}
	
	$alivedays = geodir_get_post_meta($post_id, 'alive_days', true);
	$number_of_grace_days = !empty($number_of_grace_days) ? $number_of_grace_days : geodir_payment_preexpiry_notice_days();
	
	$user_info = get_userdata($user_id);
	$toEmail = $user_info->user_email;
	$toEmailName = $user_info->display_name;
	$user_login = $user_info->user_login;
	$user_email = $user_info->user_email;
	
	$to_message = nl2br($to_message);
	$sitefromEmail = get_option('site_email');
	$sitefromEmailName = get_site_emailName();
	$productlink = get_permalink($post_id);
	
	$post_info = get_post($post_id);
	
	$posted_date = $post_info->post_date;
	$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
	$siteurl = home_url();
	$siteurl_link = '<a href="'.$siteurl.'">'.$siteurl.'</a>';
	$loginurl = geodir_login_url();
	$loginurl_link = '<a href="'.$loginurl.'">login</a>';
	
	$fromEmail = $sitefromEmail;
	$fromEmailName = $sitefromEmailName;
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#comments#]','[#login_url#]','[#login_details#]','[#client_name#]', '[#posted_date#]', '[#transaction_details#]', '[#number_of_grace_days#]', '[#number_of_days#]', '[#username#]', '[#user_email#]' );
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$to_message,$loginurl_link,$login_details,$toEmailName, $posted_date, $extra, $number_of_grace_days, $alivedays, $user_login, $user_email);
	$message = str_replace($search_array,$replace_array,$message);
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]', '[#posted_date#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName, $posted_date);
	$subject = str_replace($search_array,$replace_array,$subject);
	$headers  = array();
	$headers[] = 'Content-type: text/html; charset=UTF-8';
	$headers[] = "Reply-To: ".$fromEmail;
	$headers[] = 'From: '.$sitefromEmailName.' <'.$sitefromEmail.'>';

	/**
	 * Filter the client email to address.
	 *
	 * @since 1.4.0
	 * @package GeoDirectory_Payment_Manager
	 * @param string $toEmail The email address the email is being sent to.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$toEmail = apply_filters('geodir_payment_clientEmail_to',$toEmail,$post_id, $user_id, $message_type, $extra);
	/**
	 * Filter the client email subject.
	 *
	 * @since 1.4.0
	 * @package GeoDirectory_Payment_Manager
	 * @param string $subject The email subject.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$subject = apply_filters('geodir_payment_clientEmail_subject',$subject,$post_id, $user_id, $message_type, $extra);
	/**
	 * Filter the client email message.
	 *
	 * @since 1.4.0
	 * @package GeoDirectory_Payment_Manager
	 * @param string $message The email message text.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$message = apply_filters('geodir_payment_clientEmail_message',$message,$post_id, $user_id, $message_type, $extra);
	/**
	 * Filter the client email headers.
	 *
	 * @since 1.4.0
	 * @since 2.0.0 $headers changed from string to array.       
	 * @package GeoDirectory_Payment_Manager
	 * @param array $headers The email headers.
	 * @param int $post_id The post id of the post the email is regarding.
	 * @param string $message_type The type of email being sent.
	 * @param string $extra Transaction details if available.
	 */
	$headers = apply_filters('geodir_payment_clientEmail_headers',$headers,$post_id, $user_id, $message_type, $extra);

	$sent = wp_mail($toEmail, $subject, $message, $headers);
	if( !$sent && function_exists( 'geodir_error_log' ) ) {
		if ( is_array( $toEmail ) ) {
			$toEmail = implode( ',', $toEmail );
		}
		$log_message = sprintf(
			__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
			$message_type,
			date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
			$toEmail,
			$subject
		);
		geodir_error_log( $log_message );
	}
	
	// send bcc to admin for expired listing
	if (($message_type == 'expiration' || $message_type == 'expiration2' || $message_type == 'expiration3') && get_option('geodir_bcc_expire')) {
		$adminEmail = get_bloginfo('admin_email');
		$subject .= ' ' . __('- ADMIN BCC COPY', 'geodir_payments');
		$sent = wp_mail($adminEmail, $subject, $message, $headers);
		if( !$sent && function_exists( 'geodir_error_log' ) ) {
			if ( is_array( $adminEmail ) ) {
				$adminEmail = implode( ',', $adminEmail );
			}
			$log_message = sprintf(
				__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
				$message_type,
				date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
				$adminEmail,
				$subject
			);
			geodir_error_log( $log_message );
		}
	}
}

// Payment module related Post Metabox function 
function geodir_payment_metabox_add()
{
	
	global $post;
	
	$geodir_post_types = geodir_get_posttypes('array');
	$geodir_posttypes = array_keys($geodir_post_types);

	if( isset($post->post_type) && in_array($post->post_type,$geodir_posttypes) ):
	
		$geodir_posttype = $post->post_type;
		$post_typename = geodir_ucwords($geodir_post_types[$geodir_posttype]['labels']['singular_name']);
		
		add_meta_box( 'geodir_listing_transaction', 'Listing Transactions', 'geodir_listing_transaction', $geodir_posttype,'normal', 'high' );
	
	endif;
	
	
	
}


function geodir_listing_transaction(){ 
	global $post, $wpdb;
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_listing_transaction_noncename' ); 
	
	$pid_sql = $wpdb->prepare("SELECT * FROM ".INVOICE_TABLE." WHERE post_id = %d ORDER BY date desc", array($post->ID));
	
	$transactions = $wpdb->get_results($pid_sql);
	
	$payment_statuses = geodir_payment_all_payment_status( false );
	?>
    <table cellpadding="3" cellspacing="3" class="widefat post fixed" >
        <thead>
			  <th>#</th>
              <th><?php _e('Type', 'geodir_payments');?></th>
              <th><?php _e('Package Information', 'geodir_payments');?></th>
              <th><?php _e('Coupon', 'geodir_payments');?></th>
              <th><?php _e('Discount', 'geodir_payments');?></th>
              <th><?php _e('Payable Amount', 'geodir_payments');?></th>
              <th><?php _e('Payment Method', 'geodir_payments');?></th>
              <th><?php _e('Date', 'geodir_payments');?></th>
              <th><?php _e('Status', 'geodir_payments');?></th>
            
        </thead>
        <tbody>
		<?php
        $total = 0;
        foreach($transactions as $invoice){
            $type = ucfirst($invoice->type);
			$status = $invoice->status;
            $paid_amt ='';
            $paid_amt = $invoice->paied_amount;
            
			if ( in_array( geodir_strtolower( $status ), array( 'paid', 'active', 'subscription-payment', 'free' ) ) ) {
				$status = 'confirmed';
			} else if ( in_array( geodir_strtolower( $status ), array( 'unpaid' ) ) ) {
				$status = 'pending';
			}
			$incomplete = $status == 'pending' && empty($invoice->paymentmethod) ? true : false;
						
			if ( (isset($type) && ($type=='Paid' || $type=='Subscription-Payment')) && $status == 'paid' ) {
				$total = $total + $paid_amt;
			}
        ?>
       		<tr>
              <td><?php echo $invoice->id;?></td>
			  <td><?php _e($type, 'geodir_payments');?></td>
              <td>	
              		<label><?php _e('ID:', 'geodir_payments');?>&nbsp;</label><?php echo $invoice->package_id;?>
                    <?php //_e('('.$invoice->package_title.')');?><br/>
                    <label><?php _e('Amount:', 'geodir_payments');?>&nbsp;</label><?php echo geodir_payment_price($invoice->amount);?><br/>
                    <label><?php _e('Alive Days:', 'geodir_payments');?>&nbsp;</label><?php echo $invoice->alive_days;?>
                    
              </td>
              <td><?php echo ($invoice->coupon_code) ? $invoice->coupon_code : __('No', 'geodir_payments');?></td>
          	  <td><?php echo geodir_payment_price($invoice->discount);?></td>
              <td><?php echo geodir_payment_price($paid_amt); ?></td>
              <td><?php echo ($invoice->paymentmethod) ? $invoice->paymentmethod : __('No', 'geodir_payments');?></td>
              <td><?php echo $invoice->date;?></td>
              <td>
			  		<select id="status" name="invoice_listing_status[]">
                    	<?php 
						foreach ( $payment_statuses as $status_key => $status_name ) { 
							if ($incomplete && $status_key == 'pending') {
								$status_name = __('Incomplete', 'geodir_payments');
							}
						?>
							<option value="<?php echo $invoice->id;?>,<?php echo $status_key;?>" <?php selected( $status, $status_key );?>><?php echo $status_name;?></option>
						<?php } ?>
                    </select>
					<br /><label> <?php _e('Ref.:', 'geodir_payments');?> <?php echo geodir_payment_invoice_id_formatted($invoice->id);?></label>
			  </td>
            </tr>
		<?php } ?>
        </tbody>
	</table><br />
    
	<?php  echo __('<b>Total Received: </b>', 'geodir_payments').geodir_payment_price($total);
}

function geodir_post_transaction_save( $post_id )  
{
	global $wpdb,$current_user;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	return;
	
	if ( !isset($_POST['geodir_listing_transaction_noncename']) || !wp_verify_nonce( $_POST['geodir_listing_transaction_noncename'], plugin_basename( __FILE__ ) ) )
	return;
	
	if(isset($_REQUEST['invoice_listing_status']) && !empty($_REQUEST['invoice_listing_status'])){
	
		foreach($_REQUEST['invoice_listing_status'] as $invoice){
			
			$invoice_info = explode(',', $invoice);
			
			$invoice_id = $invoice_info[0]; 
			$invoice_status = $invoice_info[1];
			
			geodir_update_invoice_status($invoice_id,$invoice_status);
			
		}
		
	}
		
}

//// Ajax related function 
/*-----------------------------------------*/
// MAIN AJAX FUNCTION  ////////////////

function geodir_payment_manager_ajax() {
	global $gd_session;
	
	if(isset($_REQUEST['gd_add_price']) && $_REQUEST['gd_add_price'] == 'addprice')
	{
		geodir_add_edit_price();	
	}
	
	if(isset($_REQUEST['action_del']) && $_REQUEST['action_del'] == 'true')
	{
		geodir_del_price();
	}
	
	if(isset($_REQUEST['paymentsetting']) && $_REQUEST['paymentsetting'] == 'update_setting')
	{
		geodir_change_payment_method_setting();
	}
	
	if(isset($_REQUEST['gdaction']) && $_REQUEST['gdaction'] == 'change_status')
	{
		geodir_change_payment_method_status();
	}
	
	if(isset($_REQUEST['invoice_action']) && $_REQUEST['invoice_action'] == 'invoice')
	{
		geodir_change_invoice_status();
	}
	
	if(isset($_REQUEST['gd_add_coupon']) && $_REQUEST['gd_add_coupon'] == 'addprice')
	{
		geodir_add_edit_coupon();	
	}
	
	if(isset($_REQUEST['coupon_del']) && $_REQUEST['coupon_del'] == 'true')
	{
		geodir_del_coupon();
	}
	
	if(isset($_REQUEST['allow_coupon']) && $_REQUEST['allow_coupon'] == 'true')
	{
		geodir_allow_coupon_code();
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_payment_general_options')
	{
		
		geodir_update_options(geodir_payment_general_options());
		
		$msg = 'Your settings have been saved.';
		
		$msg = urlencode($msg);
		
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_general_options&success_msg=".$msg;
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'payment_notifications')
	{
		
		geodir_update_options(geodir_payment_notifications());
		
		$msg = 'Notifications updated successfully.';
		
		$msg = urlencode($msg);
		
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=payment_notifications&success_msg=".$msg;
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['payment_ajax_data']) && $_REQUEST['payment_ajax_data'] != '')
	{
		geodir_fields_list_by_posttype($_REQUEST['post_type'], $_REQUEST['pkg_id'], $_REQUEST['cats'], $_REQUEST['payment_ajax_data']);
		exit;
	}
	
	
	if(isset($_REQUEST['payaction']) && $_REQUEST['payaction'] == 'trouble_shoot'){
		
		geodir_payment_method_update();
	
	}
	
	$task = isset( $_POST['task'] ) ? $_POST['task'] : '';
	if ( $task == 'apply_coupon' ) {
		$_wpnonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$coupon_code = isset( $_POST['coupon'] ) ? $_POST['coupon'] : '';
		
		$return = array();
		$return['reload'] = true;
		$return['success'] = false;
		
		$cart = geodir_payment_get_cart();
		
		if ( wp_verify_nonce( $_wpnonce, 'gd_cart_nonce' ) && !empty( $cart ) ) {
			$status = geodir_payment_set_coupon_code( $cart->id, $coupon_code );
						
			switch ( $status ) {
				case 1: // successfully applied
					$return['success'] = true;
					$return['msg'] = __( 'Coupon discount applied successfully.', 'geodir_payments' );
				break;
				case 2: // already applied
					$return['reload'] = false;
					$return['success'] = true;
					$return['msg'] = __( 'Coupon discount already applied.', 'geodir_payments' );
				break;
				default: // invalid
					$return['msg'] = __( 'Coupon does not exist or maximum coupon redemption limit reached!', 'geodir_payments' );
				break;
			}
		} else {
			$return['msg'] = __( 'Invalid request found. Please try again later!', 'geodir_payments' );
		}
				
		echo json_encode( $return );
		exit;
	} else if ( $task == 'invoices' ) {
		$_wpnonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		
		if ( wp_verify_nonce( $_wpnonce, 'ajax_invoices_nonce' ) ) {
			echo geodir_payment_invoices_page_content( true );
			exit;
		}
		echo 0;
		exit;
	} else if ( $task == 'invoice_pay' ) {
		$return = array();
		$return['reload'] = false;
		$return['success'] = false;
		$return['msg'] = __( 'Invalid request found. Please try again later!', 'geodir_payments' );;
		
		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$invoice_id = isset( $_POST['invoice_id'] ) ? (int)$_POST['invoice_id'] : '';
		
		if ( wp_verify_nonce( $nonce, 'gd_invoice_nonce_' . $invoice_id ) ) {
			$pay_for_invoice = geodir_payment_allow_pay_for_invoice( $invoice_id );
			
			if ( $pay_for_invoice && $cart_id = geodir_payment_cart_id( $invoice_id ) ) {
				do_action( 'geodir_payment_pay_for_invoice', $invoice_id );
				
				$return['reload'] = true;
				$return['success'] = true;
				$return['msg'] = NULL;
			}
		}
		echo json_encode( $return );
		exit;
	} else if ( $task == 'invoice_send' ) {
		$return = array();
		$return['success'] = false;
		$return['msg'] = __( 'Oops something wrong, invoice sending fail!', 'geodir_payments' );
		
		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$invoice_id = isset( $_POST['invoice_id'] ) ? (int)$_POST['invoice_id'] : '';
		
		if ( wp_verify_nonce( $nonce, 'gd_nonce_send_invoice_' . $invoice_id ) ) {
			$sent = geodir_payment_send_invoice( $invoice_id );
			
			if ( !empty( $sent ) ) {
				$return['success'] = true;
				$return['msg'] = wp_sprintf( __( 'Invoice was successfully sent to %s', 'geodir_payments' ), $sent );
			}
		}
		echo json_encode( $return );
		exit;
	} else if ( $task == 'save_temp_listing' ) {
		$return = array();
		$return['success'] = true;
		
		$_wpnonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
		
		if ( !empty($_POST) && wp_verify_nonce( $_wpnonce, 'gd_save_temp_listing' ) ) {
			$gd_session->set('listing', $_POST);
		}
		echo json_encode($return);
		exit;
	}
}

function geodir_payment_set_coupon_code( $invoice_id, $coupon_code ) {
	$invoice = geodir_get_invoice( $invoice_id );
	
	if ( empty($invoice) ) {		
		return false;
	}
	
	$post_type = geodir_payment_cart_post_type( $invoice_id );
	
	$data = array();
	
	$status = false;
	$invalid = false;
	
	if ($coupon_code != '' && geodir_payment_allow_coupon_usage(array('cart_id' => $invoice_id)) && geodir_is_valid_coupon($post_type, $coupon_code)) {
		$usage_count_left = geodir_payment_coupon_usage_count_left($coupon_code);
		
		if ( $invoice->coupon_code == $coupon_code ) {
			if (!$usage_count_left && $invoice->coupon_usage != 'increase') {
				$status = 0; // limit reached.
				$invalid = true;
			} else {
				$status = 2; // already applied.
			}
		} else {
			if ($usage_count_left) {
				$status = 1; // successfully applied.
				
				$amount = $invoice->amount;
				$tax_amount = $invoice->tax_amount;
				$discount = $coupon_code != '' ? geodir_get_discount_amount( $coupon_code, $amount ) : 0;
				
				$amount = geodir_payment_price( $amount, false );
		
				$paied_amount = ( $amount + $tax_amount ) - $discount;
				
				$data['coupon_code'] = $coupon_code;
				$data['amount'] = $amount;
				$data['tax_amount'] = $tax_amount;
				$data['discount'] = $discount;
				$data['paied_amount'] = max(0, $paied_amount);
			} else {
				$status = 0; // limit reached.
				$invalid = true;
			}
		}
	} else {
		$invalid = true;
	}
		
	if ($invalid) {
		$status = 0; // invalid.
		
		$amount = $invoice->amount;
		$tax_amount = $invoice->tax_amount;
		$discount = 0;
		
		$amount = geodir_payment_price( $amount, false );

		$paied_amount = ( $amount + $tax_amount );
		
		$data['coupon_usage'] = NULL;
		$data['coupon_code'] = '';
		$data['amount'] = $amount;
		$data['tax_amount'] = $tax_amount;
		$data['discount'] = $discount;
		$data['paied_amount'] = max(0, $paied_amount);
	}
	if (!empty($data)) {
		$data['id'] = $invoice_id;
		
		if (!empty($invoice->coupon_code) && $invoice->coupon_code != $coupon_code && $invoice->coupon_usage == 'increase') { // decrease old coupon usage count if already counted.
			geodir_payment_decrease_coupon_usage_count($invoice->coupon_code);
			$data['coupon_usage'] = NULL;
		}

		geodir_update_invoice( $data );
	}
	
	$status = apply_filters( 'geodir_payment_set_coupon_code', $status, $invoice_id, $coupon_code );
	
	return $status;
}

function geodir_payment_manager_ajaxurl(){
	return admin_url('admin-ajax.php?action=geodir_payment_manager_ajax');
}

function geodir_fields_list_by_posttype($post_type = '', $pkg_id = '', $cats='', $ajax_data='')
{
	global $wpdb,$cat_display,$post_cat;
	if($post_type)
	{
		$custom_fields = geodir_post_custom_fields('','all',$post_type);
		$html = '<select style="min-width:200px;" multiple="multiple" name="pay_custom_fields[]">';
		if(!empty($custom_fields)){
			foreach($custom_fields as $key=>$val)
			{
				$id =  $val['id'];
				$label = stripslashes($val['admin_title']);
				$is_default =  $val['is_default'];
				$is_admin =  $val['is_admin'];
				$field_type =  $val['field_type'];
				
				if(!($field_type == 'address' && $is_admin == '1') && !($field_type == 'taxonomy' && $is_admin == '1'))
				{
					$selected = '';
					if($pkg_id != '' && $wpdb->get_var($wpdb->prepare("SELECT id FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages) AND id='".$id."'", array($pkg_id))))
					{
						$selected = 'selected="selected"';
					}
					$html .= '<option '.$selected.' value="'.$id.'">'.$label.'</option>';
				}
				
			}
		}
			
			$cat_display = 'multiselect';
			$post_cat = $cats;
			$html_cat = '<select style="min-width:200px;" name="gd_cat[]" multiple="multiple" style="height: 100px;" >';	
			$html_cat .= geodir_custom_taxonomy_walker($post_type.'category');
			
			$html_cat .= '</select>';
			
			
			$downgrade = '<select style="min-width:200px;" name="gd_downgrade_pkg" style="height: 100px;" >';	
			$downgrade .= '<option value="0">'.__("Expire", 'geodir_payments').'</option>';
				$priceinfo = geodir_package_list_info($post_type);
				$pricearr = array();
				foreach($priceinfo as $priceinfoObj){ 
					$selected = ''; 
					if ($priceinfoObj->pid == $cats)
						$selected = 'selected="selected"';
						
					$downgrade .= '<option value="'.$priceinfoObj->pid.'" '.$selected.'>' . __(stripslashes_deep($priceinfoObj->title), 'geodirectory') . ' - '.$priceinfoObj->post_type.'</option>';
				} 
			
			$downgrade .= '</select>';
			
		
		if($ajax_data == ''){
	
			$htmls['html_cat'] = isset($html_cat) ? $html_cat : '';
			$htmls['posttype'] = isset($html) ? $html : '';
			$htmls['downgrade'] = isset($downgrade) ? $downgrade : '';
			
			return $htmls;
			
			
		
		}else{
			$htmls['html_cat'] = $html_cat;
			$htmls['posttype'] = $html;
			$htmls['downgrade'] = $downgrade;
			 echo json_encode($htmls);
			//echo $htmls = '{"posttypes":"$html","html_cat":"$html_cat"}';
		}
	}
}

// Ajax function to add edit price package
function geodir_add_edit_price()
{
	global $wpdb,$plugin_prefix;
	
	if(current_user_can( 'manage_options' )){
	
		if($_POST['gd_add_price'] == 'addprice' && isset($_REQUEST['package_add_update_nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['package_add_update_nonce'], 'package_add_update' ) )
				return;

			$id = $_POST['gd_id'];
			
			$title = $_POST['gd_title'];
			
			$amount = $_POST['gd_amount'];
			
			$days = $_POST['gd_days'];
			
			$status = $_POST['gd_status'];
			
			$is_default = $_POST['gd_is_default']; //COMP5
			
			$cat = isset($_POST['gd_cat']) ? $_POST['gd_cat'] : '';
			
			$is_featured = $_POST['gd_is_featured'];
			
			$title_desc = $_POST['gd_title_desc'];
			
			$image_limit = $_POST['gd_image_limit'];
			
			$cat_limit = $_POST['gd_cat_limit'];
			
			$google_analytics = $_POST['google_analytics'];
			
			$sendtofriend = $_POST['geodir_sendtofriend'];
			
			$post_type = $_POST['gd_posting_type'];
			
			$link_business_pkg = isset($_POST['gd_link_business_pkg']) ? $_POST['gd_link_business_pkg'] : '';

			$link_business_cpt = isset($_POST['gd_link_business_cpt']) ? $_POST['gd_link_business_cpt'] : '';
			
			$recurring_pkg = isset($_POST['gd_recurring_pkg']) ? $_POST['gd_recurring_pkg'] : '';
			
			$reg_desc_pkg = isset($_POST['gd_reg_desc_pkg']) ? $_POST['gd_reg_desc_pkg'] : '';
			
			$reg_fees_pkg = isset($_POST['gd_reg_fees_pkg']) ? $_POST['gd_reg_fees_pkg'] : '';
			
			$downgrade_pkg = $_POST['gd_downgrade_pkg'];
			
			$sub_active = isset($_POST['gd_sub_active']) ? $_POST['gd_sub_active'] : '';
			
			$display_order = isset($_POST['gd_display_order']) ? (int)$_POST['gd_display_order'] : '';
			
			$sub_units = $_POST['gd_sub_units'];
			
			$sub_units_num = $_POST['gd_sub_units_num'];
			$sub_num_trial_days = $_POST['sub_num_trial_days'];
			$sub_num_trial_units = !empty( $_POST['gd_sub_num_trial_units'] ) && in_array( $_POST['gd_sub_num_trial_units'], array( 'D', 'W', 'M', 'Y' ) ) ? $_POST['gd_sub_num_trial_units'] : 'D';
			$sub_units_num_times = $_POST['sub_units_num_times'];
			
			$use_desc_limit = $_POST['gd_use_desc_limit'];
			$desc_limit = $_POST['gd_desc_limit'];
			$use_tag_limit = $_POST['gd_use_tag_limit'];
			$tag_limit = $_POST['gd_tag_limit'];
			
			if($sub_active){
				if($sub_units=='D'){$mult = 1;}
				if($sub_units=='W'){$mult = 7;}
				if($sub_units=='M'){$mult = 30;}
				if($sub_units=='Y'){$mult = 365;}
				$days = $mult;
			}
			
			$hide_related_tab = isset($_POST['geodir_hide_related_tab']) ? (int)$_POST['geodir_hide_related_tab'] : 0;
			$has_upgrades = isset($_POST['geodir_has_upgrades']) ? (int)$_POST['geodir_has_upgrades'] : 1;
			$disable_coupon = isset($_POST['geodir_disable_coupon']) ? absint($_POST['geodir_disable_coupon']) : 0;
			$disable_editor = !empty($_POST['gd_disable_editor']) ? 1 : 0;
			
			if ($id != '') {
				$get_oldposttype = $wpdb->get_row($wpdb->prepare("SELECT post_type, is_default FROM ".$plugin_prefix."price WHERE pid=%d", array($id)));
							
				$get_oldpricedata = $wpdb->get_results($wpdb->prepare("SELECT post_type FROM ".$plugin_prefix."price WHERE post_type=%s",array($get_oldposttype->post_type)));
	
				
				if(count($get_oldpricedata) > 1)
				{
					
					if($is_default && $get_oldposttype->post_type == $post_type){
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='0' WHERE pid!=%d AND post_type=%s",array($id,$post_type)));
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='1' WHERE pid=%d",array($id)));
						
					}elseif(!$get_oldposttype->is_default && $get_oldposttype->post_type != $post_type ){
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET post_type=%s WHERE pid=%d",array($post_type,$id)));
						
						if($is_default)
						{
							$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='0' WHERE pid!=%d AND post_type=%s",array($id,$post_type)));
							
							$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='1' WHERE pid=%d",array($id)));
							
						}
						
					}elseif($get_oldposttype->is_default){
						
						$error = __("You have not change this package because its default package for ".$get_oldposttype->post_type.".", 'geodir_payments');
							
					}
					
				}
				else
				{
					
					if($get_oldposttype->is_default != $is_default || $get_oldposttype->post_type != $post_type)
					{
						$error = __("You have not change this package because its default package for ".$get_oldposttype->post_type.".", 'geodir_payments');
					}
					
				}
				
			}
			
			
			
			if(empty($error))
			{
			
				if($cat)
				{
					$cat = implode(',',$cat);
				}
				
				if(!$title_desc)
				{
					$title_desc = $title.' : number of publish days are '.$days.' (<span id="'.str_replace(' ','_',$title).'">'.$amount.' '.geodir_get_currency_type().'</span>)';
				}
				
				//$title_desc = addslashes($title_desc);
				
				do_action('geodir_before_save_package');
				
				if ($id) {
					$wpdb->query(
						$wpdb->prepare(
							"update ".GEODIR_PRICE_TABLE." set 
							title=%s, 
							amount=%f,
							days=%d,
							status=%d,
							cat=%s,
							is_featured=%d,
							title_desc=%s, 
							image_limit=%s, 
							cat_limit=%s, 
							google_analytics = %d, 
							sendtofriend = %d, 
							post_type=%s, 
							link_business_pkg=%s,
							link_business_cpt=%s,
							recurring_pkg=%s, 
							reg_desc_pkg=%s, 
							reg_fees_pkg=%s, 
							downgrade_pkg=%s, 
							sub_active=%s,
							display_order=%d,
							sub_units=%s,
							sub_units_num=%s,
							sub_num_trial_days=%s, 
							sub_num_trial_units=%s, 
							sub_units_num_times=%s,
							use_desc_limit=%d,
							desc_limit=%d,
							use_tag_limit=%d,
							tag_limit=%d,
							hide_related_tab=%d,
							has_upgrades=%d,
							disable_coupon=%s,
							disable_editor=%s WHERE pid=%d",
							array($title,$amount,$days,$status,$cat,$is_featured,$title_desc,$image_limit,$cat_limit,$google_analytics,$sendtofriend,$post_type,$link_business_pkg,$link_business_cpt,$recurring_pkg,$reg_desc_pkg,$reg_fees_pkg,$downgrade_pkg,$sub_active,$display_order,$sub_units,$sub_units_num,$sub_num_trial_days,$sub_num_trial_units,$sub_units_num_times, $use_desc_limit, $desc_limit, $use_tag_limit, $tag_limit, $hide_related_tab, $has_upgrades, $disable_coupon, $disable_editor,$id)
						)
					);
					
					$msg = __('Price updated successfully.', 'geodir_payments');
				} else {
					$wpdb->query(
						$wpdb->prepare(
							"insert into ".GEODIR_PRICE_TABLE." set
							title=%s, 
							amount=%f, 
							days=%d, 
							status=%d,
							is_default=%d,
							cat=%s,
							is_featured=%d,
							title_desc=%s, 
							image_limit=%s, 
							cat_limit=%s, 
							google_analytics = %d,
							sendtofriend = %d,
							post_type=%s, 
							link_business_pkg=%s,
							link_business_cpt=%s,
							recurring_pkg=%s, 
							reg_desc_pkg=%s, 
							reg_fees_pkg=%s, 
							downgrade_pkg=%s, 
							sub_active=%s,
							display_order=%d,
							sub_units=%s,
							sub_units_num=%s,
							sub_num_trial_days=%s,
							sub_num_trial_units=%s,  
							sub_units_num_times= %s,
							use_desc_limit=%d,
							desc_limit=%d,
							use_tag_limit=%d,
							tag_limit=%d,
							hide_related_tab=%d,
							has_upgrades=%d,
							disable_coupon=%s,
							disable_editor=%s",
							array($title, $amount, $days, $status,$is_default,$cat,$is_featured,$title_desc,$image_limit,$cat_limit,$google_analytics,$sendtofriend,$post_type,$link_business_pkg,$link_business_cpt,$recurring_pkg,$reg_desc_pkg,$reg_fees_pkg,$downgrade_pkg,$sub_active,$display_order,$sub_units,$sub_units_num,$sub_num_trial_days,$sub_num_trial_units,$sub_units_num_times, $use_desc_limit, $desc_limit, $use_tag_limit, $tag_limit, $hide_related_tab, $has_upgrades, $disable_coupon, $disable_editor)
						)
					);
					
					$id = $wpdb->insert_id;
					
					$msg = __('Price created successfully.', 'geodir_payments');
					
					if($is_default)
					{
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='0' WHERE pid!=%d AND post_type=%s",array($id,$post_type)));
						
						$wpdb->query($wpdb->prepare("UPDATE ".$plugin_prefix."price SET is_default='1' WHERE pid=%d",array($id)));
					}
				}
				
				do_action('geodir_after_save_package', $id);

				$post_fields = !empty($_REQUEST['pay_custom_fields']) ? $_REQUEST['pay_custom_fields'] : array();
				
				$all_packages = $wpdb->get_results($wpdb->prepare("SELECT pid FROM ".$plugin_prefix."price WHERE post_type=%s",array($post_type)));
				
				$packages_default_field = '';
				if($all_packages){
						
						foreach($all_packages as $pkg){
							$packages_default_field .= ','.$pkg->pid;
						}
						
				}
				
				/* --- start posts default fields --- */
				$default_address_field = $wpdb->get_row($wpdb->prepare("select id from ".GEODIR_CUSTOM_FIELDS_TABLE." where is_admin='1' and field_type='address' and post_type=%s", array($post_type)));
				
				$post_fields[] = $default_address_field->id;
				$post_default_fields[] = $default_address_field->id;
				
				
				$default_taxonomy_field =	$wpdb->get_row($wpdb->prepare("select id from ".GEODIR_CUSTOM_FIELDS_TABLE." where is_admin='1' and field_type='taxonomy' and post_type=%s",array($post_type)));
				
				$post_fields[] = $default_taxonomy_field->id;
				$post_default_fields[] = $default_taxonomy_field->id;
				
				/* --- end posts default fields --- */
				
				if(!empty($post_fields))
				{
					$post_fields_main_array = array($id,$post_type);
					
					$post_fields_length = count($post_fields);
					$post_fields_format = array_fill(0, $post_fields_length, '%d');
					$post_fields_format = implode(',', $post_fields_format);
					
					$post_fields_main_array = array_merge($post_fields_main_array,$post_fields);
					
					$post_default_main_array = array($post_type);
					$post_default_fields_length = count($post_default_fields);
					$post_default_format = array_fill(0, $post_default_fields_length, '%d');
					$post_default_format = implode(',', $post_default_format);	
					$post_default_main_array = array_merge($post_default_main_array,$post_default_fields);
					
					$old_package_change = $wpdb->get_results($wpdb->prepare("SELECT id, packages from ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages)",array($id)));
					
					if(!empty($old_package_change))
					{
						
						foreach($old_package_change as $key){
							
							$pck_array = explode(',', $key->packages);
							
							$packages = '';
							$comma = '';
							foreach($pck_array as $pck_key)	
							{
								if($pck_key != $id && $pck_key != '')
								{
									$packages .= $comma.$pck_key;
									$comma = ',';
								}
							}
							
							$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = %s WHERE id=%d",array($packages,$key->id)));
						}
						
					}
					
					
					$wpdb->query(
						$wpdb->prepare(
						"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = CONCAT('',TRIM(BOTH ',' FROM packages),',%d,') WHERE post_type = %s AND id IN ($post_fields_format)",
						$post_fields_main_array	
						)
					);
	
	
	
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = '".$packages_default_field."' WHERE post_type = %s AND id IN ($post_default_format)",
						$post_default_main_array
					)
				);
			
			}
					
				$msg = urlencode($msg);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&success_msg=".$msg;
				wp_redirect($location);
				gd_die();
				
			}
			else
			{
				$error = urlencode($error);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&error_msg=".$error;
				wp_redirect($location);
				gd_die();
			}
		}
	
	}else{
		
		wp_redirect(geodir_login_url());
		gd_die();
	
	}
	
	
}


//============ AJAX FUNCTION FOR ADD/EDIT COUPON ============
function geodir_add_edit_coupon() {
	global $wpdb;
	
	if (current_user_can('manage_options')) {
		if ($_POST['gd_add_coupon'] == 'addprice' && isset($_REQUEST['coupon_add_update_nonce'])) {
			if (!wp_verify_nonce( $_REQUEST['coupon_add_update_nonce'], 'coupon_add_update'))
				return;
				
			$id = $_POST['gd_id'];
			$coupon_code  = $_POST['coupon_code'];
			$post_type = !empty($_POST['gd_coupon_post_type']) ? implode(',', $_POST['gd_coupon_post_type']) : '';
			$discount_type = $_POST['discount_type'];
			$discount_amount = $_POST['discount_amount'];
			$gd_status = $_POST['gd_status'];
			$recurring = $_POST['gd_recurring'];
			$usage_limit = isset($_POST['usage_limit']) && trim($_POST['usage_limit']) != '' ? trim($_POST['usage_limit']) : NULL;		
			
			$error = '';
			$extra_query = $id > 0 ? " AND cid != '".$id."'" : '';	
			
			$duplicate = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".COUPON_TABLE." WHERE coupon_code = %s".$extra_query,array($coupon_code)));
			
			if ($duplicate) {
				$error = __("Coupon code already exists.", 'geodir_payments');
			}
			
			if (empty($error)) {
				if ($id != '') {
					$wpdb->query($wpdb->prepare(
					"UPDATE ".COUPON_TABLE." SET coupon_code=%s, post_types=%s, discount_type=%s, discount_amount=%f, status=%s, recurring=%s, usage_limit=%s WHERE cid=%d",array($coupon_code,$post_type,$discount_type,$discount_amount,$gd_status,$recurring,$usage_limit,$id)));
					
					$msg = __('Coupon code updated successfully.', 'geodir_payments');
				} else {
					$wpdb->query(
					$wpdb->prepare(
					"INSERT INTO ".COUPON_TABLE." SET coupon_code=%s, post_types=%s, discount_type=%s, discount_amount=%f, status=%s, recurring=%s, usage_limit=%s",
					array($coupon_code,$post_type,$discount_type,$discount_amount,$gd_status,$recurring,$usage_limit)
					)
					);
					
					$msg = __('Coupon code submitted successfully.', 'geodir_payments');
				}
				
				$msg = urlencode($msg);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&success_msg=".$msg;
				wp_redirect($location);
				exit;
			} else {
				$error = urlencode($error);
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&error_msg=".$error;
				wp_redirect($location);
				exit;
			}
		}
	} else {
		wp_redirect(geodir_login_url());
		exit();
	}
}

//============ AJAX FUNCTION FOR DELETE COUPON ============
function geodir_del_coupon()
{
	global $wpdb, $price_db_table_name;
	
	if(current_user_can( 'manage_options' )){

		if($_REQUEST['pagetype'] == 'delete' && $_REQUEST['id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'coupon_code_delete_'.$_REQUEST['id'] ) )
						return;
			
						
			$cid = $_REQUEST['id'];
			
			$wpdb->query($wpdb->prepare("delete from ".COUPON_TABLE." where cid=%d",array($cid)));
			
			$msg = __('Coupon deleted successfully.', 'geodir_payments');
			
			$msg = urlencode($msg);
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager&success_msg=success&success_msg=".$msg;
			wp_redirect($location);
			exit;
		}
	
	}else{
		
		wp_redirect(geodir_login_url());
		exit();
	}

}

//============ AJAX FUNCTION FOR DELETE COUPON ============
function geodir_allow_coupon_code()
{
		
		if(current_user_can( 'manage_options' )){
		
			if(isset($_REQUEST['_wpnonce'])){
			
				if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'allow_coupon_code_nonce' ) )
					return;
					
				update_option('geodir_allow_coupon_code', $_REQUEST['value']);
				
				$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_coupon_manager";
				wp_redirect($location);
				exit;
			
			}
		
		}else{
		
		wp_redirect(geodir_login_url());
		exit();
	
	}
		
}

//============AJAX FUNCTION FOR UPDATE PAYMENT METHOD ============
function geodir_payment_method_update()
{
	global $wpdb;
	
	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['payaction'] == 'trouble_shoot' && $_REQUEST['pay_method'] != '' && isset($_REQUEST['nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['nonce'], 'payment_trouble_shoot'.$_REQUEST['pay_method'] ) )
				return;
			
			
			$pay_method = str_replace('payment_method_', '', $_REQUEST['pay_method']);
			
			$paymenthodinfo = array();
			
			if($pay_method != '')
				$paymenthodinfo = geodir_get_additional_pay_options($pay_method);
			
			
			if(is_array($paymenthodinfo) && !empty($paymenthodinfo)){
			
				$paymentsql = $wpdb->prepare("select * from $wpdb->options where option_name like %s order by option_id asc", array('payment_method_'.$pay_method));
			
				$paymentinfo = $wpdb->get_row($paymentsql);
				
				if($paymentinfo->option_id){
				
					$wpdb->query($wpdb->prepare("update $wpdb->options set option_value=%s where option_id=%d",array(serialize($paymenthodinfo),$paymentinfo->option_id)));
					
				}
			
			}
			
			$msg = __('Price method updated successfully.', 'geodir_payments');
			$msg = urlencode($msg);
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&success_msg=".$msg;
			wp_redirect($location);
			exit;
			
		}
	}else{
		
		wp_redirect(geodir_login_url());
		exit();
	
	}

}


//============AJAX FUNCTION FOR DELETE PRICE============
function geodir_del_price()
{
	global $wpdb, $price_db_table_name,$plugin_prefix;
	
	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['pagetype'] == 'delete' && $_REQUEST['id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'package_action_'.$_REQUEST['id'] ) )
			return;
			
			$pid = $_REQUEST['id'];
			
			/* --- delete package detail default package set --- */
			$post_type = $wpdb->get_var($wpdb->prepare("select post_type from ".GEODIR_PRICE_TABLE." where pid=%d",array($pid)));
			
			$table = $plugin_prefix.$post_type.'_detail';
			
			$default_package = geodir_get_default_package($post_type);
			$package_id = $default_package->pid;
			
			$wpdb->query($wpdb->prepare("update ".$table." set package_id=%d where package_id=%d",array($package_id,$pid)));
			/**
			 * @since 2.0.0
			 */
			do_action('geodir_payment_pre_delete_package', $pid);
			$wpdb->query($wpdb->prepare("delete from ".GEODIR_PRICE_TABLE." where pid=%d",array($pid)));
			/**
			 * @since 2.0.0
			 */
			do_action('geodir_payment_post_delete_package', $pid);
			
			$old_package_change =	$wpdb->get_results($wpdb->prepare("SELECT id, packages from ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages)",array($pid)));
			
					
					if(!empty($old_package_change))
					{				
						foreach($old_package_change as $key){
						 $pck_array = explode(',', $key->packages);
							$packages = '';
							$comma = '';
							foreach($pck_array as $pck_key)	
							{
								if($pck_key != $pid && $pck_key != '')
								{
									$packages .= $comma.$pck_key;
									$comma = ',';
								}
							}
							$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages = %s WHERE id=%d",array($packages,$key->id)));
						}
						
					}
			
			$msg = __('Price deleted successfully.', 'geodir_payments');
			$msg = urlencode($msg);
			$location = admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&success_msg=".$msg;
			wp_redirect($location);
			exit;
		}
	}else{
		
		wp_redirect(geodir_login_url());
		exit();
	
	}

}

//============AJAX FUNCTION FOR CHANGE PAYMENT METHOD SETTING============
function geodir_change_payment_method_setting()
{
	global $wpdb;
	
	if(current_user_can( 'manage_options' )){
	
		if($_REQUEST['paymentsetting'] && isset($_REQUEST['update_payment_settings_nonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['update_payment_settings_nonce'], 'payment_options_status_update_'.$_REQUEST['id'] ) )
				return;
				
			
			$paymentupdsql = $wpdb->prepare("select option_value from $wpdb->options where option_id=%d",array($_REQUEST['id']));
			
			$paymentupdinfo = $wpdb->get_results($paymentupdsql);
			if($paymentupdinfo)
			{
				foreach($paymentupdinfo as $paymentupdinfoObj)
				{
					$option_value = unserialize($paymentupdinfoObj->option_value);
					$payment_method = trim($_POST['payment_method']);
					$display_order = trim($_POST['display_order']);
					$payment_isactive = $_POST['payment_isactive'];
					$payment_mode = $_POST['payment_mode'];
					if($payment_method)
					{
						$option_value['name'] = $payment_method;
					}
					$option_value['display_order'] = $display_order;
					$option_value['isactive'] = $payment_isactive;
					$option_value['payment_mode'] = $payment_mode;
					$paymentOpts = $option_value['payOpts'];
					for($o=0;$o<count($paymentOpts);$o++)
					{
						$paymentOpts[$o]['value'] = $_POST[$paymentOpts[$o]['fieldname']];
					}
					$option_value['payOpts'] = $paymentOpts;
					$option_value_str = serialize($option_value);	
				}
			}
			
			$updatestatus = $wpdb->prepare("update $wpdb->options set option_value= %s where option_id=%d",array($option_value_str,$_REQUEST['id']));
			
			$wpdb->query($updatestatus);
			
			$msg = __('Payment Method Updated Successfully.', 'geodir_payments');
			$msg = urlencode($msg);
			
			wp_redirect(admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&success_msg=".$msg);
			
			exit;
		}
	
	}else{
		
		wp_redirect(geodir_login_url());
		exit();
	
	}

} 


//============AJAX FUNCTION FOR CHANGE PAYMENT METHOD STATUS============
function geodir_change_payment_method_status()
{
		global $wpdb;
		
		if(current_user_can( 'manage_options' )){
		
		if($_GET['status']!='' && $_GET['id']!='' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'payment_options_status_update_'.$_GET['id'] ) )
			return;
			
			$paymentupdsql = $wpdb->prepare("select option_value from $wpdb->options where option_id=%d",array($_GET['id']));
			
			$paymentupdinfo = $wpdb->get_results($paymentupdsql);
			
			if($paymentupdinfo)
			{
				foreach($paymentupdinfo as $paymentupdinfoObj)
				{
					$option_value = unserialize($paymentupdinfoObj->option_value);
					
					$option_value['isactive'] = $_GET['status'];
					
					$option_value_str = serialize($option_value);
					
				}
			}
			
			$updatestatus = $wpdb->prepare("update $wpdb->options set option_value= %s where option_id=%d",array($option_value_str,$_GET['id']));
			
			$wpdb->query($updatestatus);
		}
		
		$msg = 'Payment Method Status Updated Successfully.';
		$msg = urlencode($msg);
		wp_redirect(admin_url()."admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options&success_msg=".$msg);
		
		exit;
		
		}else{
		
		wp_redirect(geodir_login_url());
		exit();
	
	}
}

//============AJAX FUNCTION FOR CHANGE INVOICE STATUS============
function geodir_change_invoice_status() {
	global $wpdb;
	
	$all_status = geodir_payment_all_payment_status();

	if ( current_user_can( 'manage_options' ) ) {
		if ( isset( $_REQUEST['invoice_action'] ) && $_REQUEST['invoice_action'] == 'invoice' ) {
			$wpnonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
			
			if ( !wp_verify_nonce( $wpnonce, 'invoice_status_update_nonce' ) ) {
				return;
			}
			
			$invoice_id = isset( $_REQUEST['invoiceid'] ) ? (int)$_REQUEST['invoiceid'] : '';
			$invoice_info = geodir_get_invoice( $invoice_id );
			
			$status = isset( $_REQUEST['inv_status'] ) ? sanitize_text_field($_REQUEST['inv_status']) : '';
			
			if ( in_array( $status, $all_status ) && !empty( $invoice_info ) && $status != $invoice_info->status ) {
				// Update invoice status
				geodir_update_invoice_status( $invoice_id, $status );
				
				$msg = urlencode( GD_INVOICE_MSG );
		
				wp_redirect( admin_url() . 'admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_invoice_list&success_msg=' . $msg );
				exit;
			}
		}
		
		return;
	} else {
		wp_redirect( geodir_login_url() );
		exit;
	}	
}


//============ CHECK COUPON CODE BY LISTING TYPE ============


function geodir_build_payment_list() {
	global $gd_session, $post, $package_id;
	
	$listing_type = isset($_REQUEST['listing_type']) ? sanitize_text_field($_REQUEST['listing_type']) : '';
	
	if (empty($listing_type)) {
		$listing_type = $post->post_type;
	}
	
	if (isset($_REQUEST['package_id'])) {
		$package_id = $_REQUEST['package_id'];
	} elseif (isset($post->package_id) && $post->package_id != '') {
		$listing_type = $post->post_type;
		$package_id = $post->package_id;
	} else {
		$default_package = geodir_get_default_package($listing_type);
		$package_id = !empty($default_package->pid) ? $default_package->pid : '';
		
	}
	
	$gd_ses_listing = $gd_session->get('listing');
	$package_info = geodir_get_package_info($package_id);
	$package_list_info = geodir_package_list_info($listing_type);
	
	if (is_page() && isset($post->post_content) && has_shortcode( $post->post_content, 'gd_add_listing' ) ) {
		$page_id = $post->ID;
	} else { 
		$page_id = geodir_add_listing_page_id();
	}
	
	$postlink = get_permalink( $page_id );
	$postlink = geodir_getlink($postlink,array('listing_type'=>$listing_type),false);
	
	if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
		$postlink = geodir_getlink($postlink,array('pid'=>$_REQUEST['pid']),false);
	}
	
	echo '<div class="geodir_price_package_row geodir_form_row clearfix ">';
	
	if (isset($_REQUEST['package_id']) || (!isset($_REQUEST['pid']) || $_REQUEST['pid'] == '')) {
		echo '<h5>'.SELECT_PACKAGE_TEXT.'</h5>';
		        
		foreach($package_list_info as $pkg) {
			$post_pkg_link = '';
			$alive_days = ($pkg->days) ? $pkg->days : 'unlimited';

			$pkg_desc = str_replace('number of publish days are', __('number of publish days are', 'geodir_payments'), $pkg->title_desc);
		
			$post_pkg_link = geodir_getlink($postlink,array('package_id'=>$pkg->pid),false);
			
			if (empty($_REQUEST['pid'])) {
				$post_pkg_link = remove_query_arg(array('backandedit'), $post_pkg_link);
				$post_pkg_link = add_query_arg(array('backandedit' => 1), $post_pkg_link);
			}
			
			if (empty($_REQUEST['pid'])) {
				$onclick = "javascript:gdPackageOnChange(this, '" . esc_attr($post_pkg_link) . "');";
			} else {
				$onclick = "window.location.href='" . esc_attr($post_pkg_link) . "'";
            }
			?>
			<div id="geodir_price_package_<?php echo $pkg->pid; ?>" class="geodir_package">
			<input name="package_id" type="radio" value="<?php echo $pkg->pid;?>" <?php if($package_id == $pkg->pid) echo 'checked="checked"';?> onclick="<?php echo $onclick;?>">&nbsp;<?php _e(stripslashes_deep($pkg_desc), 'geodirectory'); ?>
			</div>
			<?php 
		}
        
        if (empty($_REQUEST['pid'])) {
?><script type="text/javascript">function gdPackageOnChange(el,url){var $form=jQuery(el).closest('form[name=propertyform]');jQuery.ajax({url:'<?php echo esc_url(geodir_payment_manager_ajaxurl());?>',type:'POST',dataType:'json',data:'task=save_temp_listing&_wpnonce=<?php echo wp_create_nonce( 'gd_save_temp_listing' );?>&'+jQuery($form).serialize(),beforeSend:function(){jQuery(el).parent().append('&nbsp;&nbsp;<i class="fa fa-spin fa-refresh"></i>');},complete:function(xhr,textStatus){window.location.href=url;return}});}</script><?php
        }
	}
	
	echo '</div>';
}


function geodir_build_coupon() {
	global $post;
	
	if ( defined( 'WPINV_VERSION' ) ) {
		return;
	}
	
	$listing_type = !empty($_REQUEST['listing_type']) ? sanitize_text_field($_REQUEST['listing_type']) : '';
	$pid = !empty($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
	if (empty($listing_type) && $pid) {
		$listing_type = get_post_type($pid);
	}
	
	if (isset($_REQUEST['package_id'])) {
		$package_id = (int)$_REQUEST['package_id'];
	} else if (isset($post->package_id) && $post->package_id != '') {
		$package_id = $post->package_id;
	} else {
		$default_package = geodir_get_default_package($listing_type);
		$package_id = !empty($default_package->pid) ? $default_package->pid : 0;
	}
	
	$params = array();
	$params['post_type'] = $listing_type;
	if ($pid) {
		$params['post_id'] = $pid;
	}
	if ($package_id) {
		$params['package_id'] = $package_id;
	}
	
	$allow_coupon = geodir_payment_allow_coupon_usage($params);
	
	if ($allow_coupon) {
		$coupon_code = isset($post->coupon_code) ? $post->coupon_code : '';
	?>
		<h5><?php echo COUPON_CODE_TITLE_TEXT;?></h5>
		<div id="geodir_coupon_code_row" class="geodir_form_row clearfix" >
			<label><?php echo PRO_ADD_COUPON_TEXT;?></label>
			<input name="coupon_code" id="coupon_code" value="<?php echo esc_attr(stripslashes($coupon_code)); ?>" type="text" class="geodir_textfield" maxlength="100"  />
			<span class="geodir_message_note"><?php echo COUPON_NOTE_TEXT; ?></span>
		</div>
	<?php
	}
}

function geodir_payment_allow_coupon_usage( $params = array() ) {
	$allow_coupon = get_option('geodir_allow_coupon_code');

	if ($allow_coupon && !empty($params) && is_array($params)) {		
		if (isset($params['post_type']) && !($params['post_type'] != '' && geodir_is_valid_coupon($params['post_type']))) {
			$allow_coupon = false;
		}
		
		if ($allow_coupon && isset($params['package_id']) && !(geodir_payment_package_check_allow_coupon($params['package_id']))) {
			$allow_coupon = false;
		}
		
		if ($allow_coupon && isset($params['cart_id'])) { // cart id
			$cart_coupon = false;
			
			$cart = geodir_payment_get_cart($params['cart_id'], false);
			if (!empty($cart)) {
				$cart_coupon = true;

				$post_type = geodir_payment_cart_post_type($cart->id);
				if (!geodir_is_valid_coupon($post_type)) {
					$cart_coupon = false;
				}
				 
				if ($cart_coupon && !geodir_payment_package_check_allow_coupon($cart->package_id)) {
					$cart_coupon = false;
				}
			}
			$allow_coupon = $cart_coupon;
		}
	}
	
	return apply_filters('geodir_payment_allow_coupon_usage', $allow_coupon, $params);
}

function geodir_payment_package_check_allow_coupon($package_id) {
	$package_info = geodir_get_package_info_by_id($package_id);
	
	$allow_coupon = false;
	if (!empty($package_info)) {
		$allow_coupon = isset($package_info->disable_coupon) && $package_info->disable_coupon ? false : true;
	}
	
	return $allow_coupon;
}

function geodir_get_payable_amount_with_coupon( $total_amt, $coupon_code ) {
	$discount_amt = geodir_get_discount_amount( $coupon_code, $total_amt );
	
	$discount_amt = apply_filters( 'geodir_payment_filter_payable_amount_with_coupon', $discount_amt, $coupon_code, $total_amt );

	if ( $discount_amt > 0 ) {
		return $total_amt - $discount_amt;
	} else {
		return $total_amt;
	}
}

function geodir_get_discount_amount($coupon,$amount)
{

	global $wpdb;

	if($coupon!='' && $amount>0)
	{
		
		$couponinfo =	$wpdb->get_row($wpdb->prepare("SELECT * FROM ".COUPON_TABLE." WHERE coupon_code=%s",array($coupon)));
		
		if($couponinfo)
		{
			
			if($couponinfo->discount_type=='per')
			{
			
				$discount_amt = ($amount*$couponinfo->discount_amount)/100;
				
			}elseif($couponinfo->discount_type=='amt')
			{
			
				$discount_amt = $couponinfo->discount_amount;
			
			}
			
			return number_format($discount_amt, 2, '.', '');
			
		}

	}

	return '0';			

}

function geodir_is_valid_coupon($post_type, $coupon='')
{

	global $wpdb;
	
	$query = '';
	if($coupon)
		$query = " AND coupon_code = '".$coupon."'";
	
	
	$couponinfo =	$wpdb->get_var($wpdb->prepare("SELECT cid FROM ".COUPON_TABLE." WHERE FIND_IN_SET(%s, post_types) AND status='1' ".$query,array($post_type)));
	
	
	if($couponinfo)
	{
		return true;
	}

	return false;

}

function geodir_display_payment_messages() {
	if (isset($_REQUEST['success_msg']) && $_REQUEST['success_msg'] != '') {
		echo '<div id="message" class="updated fade"><p><strong>' . sanitize_text_field( $_REQUEST['success_msg'] ) . '</strong></p></div>';			
	}
	
	if (isset($_REQUEST['error_msg']) && $_REQUEST['error_msg'] != '') {
		echo '<div id="payment_message_error" class="updated fade"><p><strong>' . sanitize_text_field( $_REQUEST['error_msg'] ) . '</strong></p></div>';
	}
}

function geodir_display_post_upgrade_link() {
	global $post, $preview,$wpdb;
	
	if (!$preview) {
		if (is_user_logged_in() && $post->post_author == get_current_user_id()) {
			$post_id = $post->ID;
			$post_package_id = isset($post->package_id) ? $post->package_id : '';
			
			if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
				$post_id = (int)$_REQUEST['pid'];
				
				if (empty($post_package_id))
					$post_package_id = geodir_get_post_meta($post_id, 'package_id', true);
			}
			
			$postlink = get_permalink(geodir_add_listing_page_id());
			$editlink = geodir_getlink($postlink, array('pid' => $post_id), false);

			// we set post to published on the fly for users own posts so we need to get the real status here
			//$post_status = get_post_status( $post->ID );
			$post_status = $wpdb->get_var($wpdb->prepare("Select post_status FROM $wpdb->posts WHERE ID=%d",$post->ID));
			
			// show renew link before pre expiry days * 2
			$post_expire_date = isset($post->expire_date) ? $post->expire_date : '';
			$preexpiry_notice = false;
			
			if (geodir_payment_preexpiry_notice_is_active() && geodir_payment_preexpiry_notice_days_max() > 0 && $post_expire_date != '0000-00-00' && $post_expire_date != '' && geodir_strtolower($post_expire_date) != 'never' && strtotime($post_expire_date) > strtotime(date('01-01-1970'))) {
				$preexpiry_date = strtotime($post_expire_date) - (DAY_IN_SECONDS * geodir_payment_preexpiry_notice_days_max());
				$preexpiry_notice = $preexpiry_date <= strtotime(date('Y-m-d')) ? true : false;
			}
			
			$action_link = __('Upgrade Listing', 'geodir_payments');
			if ($post_status == 'draft' || $preexpiry_notice) {
				$action_link = __('Renew Listing', 'geodir_payments');
			} else {
				$package_info = geodir_get_package_info( $post_package_id );
				
				if ( !empty( $package_info ) && isset( $package_info->has_upgrades ) && !$package_info->has_upgrades ) {
					return;
				}
			}
	
			$upgradelink = geodir_getlink($editlink, array('package_id' => $post_package_id), false);  
			
			echo '<p class="geodir_upgrade_link"><i class="fa fa-chevron-circle-up"></i> <a href="'.$upgradelink.'">'.$action_link.'</a></p>';
		}
	}
}

function geodir_display_post_upgrade_link_on_listing() {
	global $post,$wpdb;
	
	$addplacelink = get_permalink( geodir_add_listing_page_id() );
	$editlink = geodir_getlink($addplacelink, array('pid' => $post->ID), false);
	$upgradelink = geodir_getlink($editlink, array('package_id' => $post->package_id), false);

	// we set post to published on the fly for users own posts so we need to get the real status here
	//$post_status = get_post_status( $post->ID );
	$post_status = $wpdb->get_var($wpdb->prepare("Select post_status FROM $wpdb->posts WHERE ID=%d",$post->ID));

	// show renew link before pre expiry days * 2
	$post_expire_date = isset($post->expire_date) ? $post->expire_date : '';
	$preexpiry_notice = false;
	
	if (geodir_payment_preexpiry_notice_is_active() && geodir_payment_preexpiry_notice_days_max() > 0 && $post_expire_date != '0000-00-00' && $post_expire_date != '' && geodir_strtolower($post_expire_date) != 'never' && strtotime($post_expire_date) > strtotime(date('01-01-1970'))) {
		$preexpiry_date = strtotime($post_expire_date) - (DAY_IN_SECONDS * geodir_payment_preexpiry_notice_days_max());
		$preexpiry_notice = $preexpiry_date <= strtotime(date('Y-m-d')) ? true : false;
	}
				
	$action_link = __('Upgrade Listing', 'geodir_payments');
    $action_type = 'upgrade';
	if ($post_status == 'draft' || $preexpiry_notice) {
		$action_link = __('Renew Listing', 'geodir_payments');
        $action_type = 'renew';
	}

    if ($action_type == 'upgrade') {
        $package_info = geodir_get_package_info( $post->package_id );
				
		if ( !empty( $package_info ) && isset( $package_info->has_upgrades ) && $package_info->has_upgrades ) {
		?>
        <a href="<?php echo $upgradelink; ?>" class="geodir-upgrade"
           title="<?php echo $action_link; ?>">
            <?php
            $geodir_listing_upgrade_icon = apply_filters('geodir_listing_upgrade_icon', 'fa fa-chevron-circle-up');
            echo '<i class="' . $geodir_listing_upgrade_icon . '"></i>';
            ?>
            <?php echo $action_link; ?>
        </a>
    <?php
		}
    } else {
        ?>
        <a href="<?php echo $upgradelink; ?>" class="geodir-upgrade"
           title="<?php echo $action_link; ?>">
            <?php
            $geodir_listing_renew_icon = apply_filters('geodir_listing_renew_icon', 'fa fa-chevron-circle-up');
            echo '<i class="' . $geodir_listing_renew_icon . '"></i>';
            ?>
            <?php echo $action_link; ?>
        </a>
        <?php
    }
}

function geodir_expire_check() {
    global $wpdb, $plugin_prefix, $gd_skip_preexpiry, $gd_posts_to_expire, $gd_force_to_expire;
    $today              = date_i18n('Y-m-d', current_time('timestamp'));
    $today_time         = strtotime($today);
    $yesterday          = date_i18n('Y-m-d', strtotime($today . " - 1 day"));
    $yesterday_time     = strtotime($yesterday);
    $geodir_postypes    = geodir_get_posttypes();
    $upload_dir         = wp_upload_dir();
    $upload_basedir     = $upload_dir['basedir'];

    if (get_option('geodir_listing_expiry')) {
        foreach ($geodir_postypes as $post_type) {
            $table = $plugin_prefix . $post_type . '_detail';
            
            // Send pre-expire notifications to client            
            if (geodir_payment_preexpiry_notice_is_active() && !$gd_skip_preexpiry) {
                $grace_data     = geodir_payment_preexpiry_grace_data();
                $grace_fields   = '';
                $grace_dates    = array();
                
                foreach($grace_data as $field => $data) {
                    $grace_fields   .= ", detail." . $field . ", DATE_SUB(detail.expire_date, INTERVAL " . (int)$data['days'] . " DAY) AS grace" . $data['suffix'];
                    $grace_dates[]  = "( detail." . $field . " = 'false' AND DATE_SUB(detail.expire_date, INTERVAL " . (int)$data['days'] . " DAY) <= '" . $today . "' AND DATE_SUB(detail.expire_date, INTERVAL " . (int)$data['days'] . " DAY) >= '" . $yesterday . "')";
                }
                $grace_dates = "AND ( " . implode(" OR ", $grace_dates) . " )";
                
                $query  = "SELECT p.ID, p.post_title, p.post_author, detail.expire_date, detail.package_id " . $grace_fields . " FROM " . $table . " detail, " . $wpdb->posts . " p WHERE p.post_status != 'trash' AND p.ID = detail.post_id AND detail.expire_date != 'Never' AND detail.expire_date != '' AND detail.expire_date >= '" . $today . "' " . $grace_dates . " ORDER BY detail.expire_date ASC";
                $rows   = $wpdb->get_results($query);
                
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $package_info = geodir_get_package_info($row->package_id);
                        
                        if (empty($package_info->sub_active)) {
                            update_post_meta($row->ID, '_gdpm_recurring', false);
                        }
                        
                        if (!empty($package_info->sub_active) || get_post_meta($row->ID, '_gdpm_recurring', true)) { // Don't send expiration for recurring listing
                            continue;
                        }
                        
                        $update = array();
                        
                        foreach($grace_data as $field => $data) {
                            $grace_field = 'grace' . $data['suffix'];
                            
                            if (isset($row->$field) && $row->$field == 'false' && strtotime($row->$grace_field) <= $today_time && strtotime($row->$grace_field) >= $yesterday_time) {
                                geodir_payment_clientEmail($row->ID, $row->post_author, 'expiration' . $data['suffix']);
                                $update[] = $field . "='true'";
                            }
                        }
                        
                        if (!empty($update)) {
                            $wpdb->query($wpdb->prepare("UPDATE `" . $table . "` SET " . implode(', ', $update) . " WHERE post_id=%d", array($row->ID)));
                        }
                    }
                }
            }
            
            $strcurrent = $wpdb->get_var(("SELECT UNIX_TIMESTAMP( STR_TO_DATE( '".$today."', '%Y-%m-%d' ) )"));

            if (!empty($gd_posts_to_expire)) {
                $sql = "SELECT p.ID, p.post_author, p.post_title, detail.package_id FROM " . $table . " detail, " . $wpdb->posts . " p WHERE p.post_status != 'trash' AND p.ID = detail.post_id AND detail.post_id IN('" . implode("','", $gd_posts_to_expire) . "')";
            } else {
                $sql = $wpdb->prepare("SELECT p.ID, p.post_author, p.post_title, detail.package_id FROM " . $table . " detail, " . $wpdb->posts . " p WHERE p.post_status != 'trash' AND p.ID = detail.post_id AND detail.expire_date != 'Never' AND detail.expire_date != '' AND UNIX_TIMESTAMP(detail.expire_date) <= %s", array($strcurrent) );
            }
            
            $postid_str = $wpdb->get_results($sql);

            if (!empty($postid_str)) {
                foreach ($postid_str as $postid_str_obj) {
                    $post_id = $postid_str_obj->ID;
                    $package_id = $postid_str_obj->package_id;
                    $old_package_info = geodir_get_package_info($package_id);
                    if (empty($old_package_info->sub_active)) {
                        update_post_meta($post_id, '_gdpm_recurring', false);
                    }
                    
                    if ( !empty( $gd_force_to_expire ) && is_array( $gd_force_to_expire ) && in_array( $post_id, $gd_force_to_expire ) ) {
                        // Force to expire
                    } else {
                        if (!empty($old_package_info->sub_active) || get_post_meta($post_id, '_gdpm_recurring', true)) { // Don't expire recurring listing
                            continue;
                        }
                    }
                    
                    $old_image_limit = empty($old_package_info->image_limit) ? 0 : $old_package_info->image_limit;
                    $old_cat_limit = empty($old_package_info->cat_limit) ? 0 : $old_package_info->cat_limit;
                    $downgrade_pkg = $old_package_info->downgrade_pkg;
                    $package_info = (int)$downgrade_pkg>0 ? geodir_get_package_info($downgrade_pkg) : array();

                    if ((int)$downgrade_pkg>0 && $downgrade_pkg != '' && !empty($package_info)) {
                        $featured = $package_info->is_featured;
                        $image_limit = empty($package_info->image_limit) ? 0 : $package_info->image_limit;
                        $cat_limit = empty($package_info->cat_limit) ? 0 : $package_info->cat_limit;
                        $days = $package_info->days;
                        $exclude_cats = $package_info->cat;
                        
                        if ($cat_limit != 0 && $cat_limit < $old_cat_limit) {
                            
                            $terms = wp_get_post_terms($post_id, $post_type . 'category', array("fields" => "all"));
                            
                            $term_ids = array();
                            foreach ($terms as $termsObj) {
                                if ($termsObj->parent==0) {
                                    $term_ids[] = $termsObj->term_id;
                                }
                            }
                            
                            $cat_arr = array_slice($term_ids, 0, $cat_limit);
                            $term_ids = implode(",", $cat_arr);
                            
                            wp_set_object_terms($post_id, $cat_arr, $post_type.'category'  );
                            
                            $post_default_category = geodir_get_post_meta($post_id,'default_category');
                            
                            if ($post_default_category != '' && !in_array($post_default_category, $cat_arr)) {
                                $post_default_category = $cat_arr[0];
                                geodir_save_post_meta($post_id, 'default_category', $post_default_category);
                            }
                            
                            geodir_set_postcat_structure($post_id,$post.'category',$post_default_category,'');
                        }
                        
                        $post_images  = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT * FROM ".GEODIR_ATTACHMENT_TABLE." WHERE `post_id`=%d order by menu_order asc",
                                array($post_id)
                            )
                        );
                        
                        $count_post_images = count($post_images);
                        
                        if ($image_limit != 0 && $image_limit < $old_image_limit && $count_post_images > $image_limit) {
                            
                            $post_images_arr = array_slice($post_images, $image_limit, $image_limit);
                            
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
                            }
                        }
                        
                        $expire_date = 'Never';
                        if((int)$days > 0 && $days != '') {
                            $expire_date = date('Y-m-d', strtotime(date('Y-m-d')."+".(int)$days." days"));
                        }
                        geodir_save_post_meta($post_id, 'is_featured', $featured);
                        geodir_save_post_meta($post_id, 'package_id', $downgrade_pkg); 
                        geodir_save_post_meta($post_id, 'paid_amount', ''); 
                        geodir_save_post_meta($post_id, 'paymentmethod', ''); 
                        geodir_save_post_meta($post_id, 'alive_days', $days); 
                        geodir_save_post_meta($post_id, 'expire_date', $expire_date); 
                        geodir_save_post_meta($post_id, 'expire_notification', 'false');
                        geodir_save_post_meta($post_id, 'exp2', 'false');
                        geodir_save_post_meta($post_id, 'exp3', 'false');

                        /**
                         * Fires when a post is downgraded by the expire process.
                         *
                         * @param int $post_id The post id that is being downgraded.
                         * @param int $package_id The price package the post was on.
                         * @param int $downgrade_pkg The price package the post is being downgraded to.
                         */
                        do_action('geodir_downgrade_price',$post_id,$package_id,$downgrade_pkg);
                        
                        $post_info = get_post($post_id);
                        if (!empty($post_info) && isset($post_info->post_status) && $post_info->post_status!='publish') {
                            $update_post = array();
                            $update_post['post_status'] = 'publish'; 
                            $update_post['ID'] = $post_id;
                            wp_update_post($update_post);
                        }
                    } else {
                        $post_info = get_post($post_id);
                        $post_ex_status = get_option('geodir_listing_ex_status');
                        if (!empty($post_info) && isset($post_info->post_status) && $post_info->post_status!=$post_ex_status) {
                            $expire_post = array();
                            $expire_post['post_status'] = $post_ex_status; 
                            $expire_post['ID'] = $post_id;
                            
                            wp_update_post($expire_post); // update post expiry status
                        }
                    }
                }
            }
        }
    }
}

// run the expiry check twice a day
if ( !wp_next_scheduled('geodir_task_hook') ) {
	wp_schedule_event( time(), 'twicedaily', 'geodir_task_hook' ); // hourly, daily and twicedaily
}

if (!function_exists('geodir_expire_check_cron')) {
	function geodir_expire_check_cron() {
		geodir_expire_check();
		//checkForUpdates();
	}
}

add_action('geodir_task_hook', 'geodir_expire_check_cron');


/* Payment Method Options diagnostic function */

function geodir_diagnose_payment_method_options()
{
	
	echo "Coming Soon..." ;

}

function geodir_pm_substr($string, $limit) {
	$max_length = (int)$limit;
	$text = trim($string);
	$tags   = array();
    $result = "";

    $is_open   = false;
    $grab_open = false;
    $is_close  = false;
    $in_double_quotes = false;
    $in_single_quotes = false;
    $tag = "";

    $i = 0;
    $stripped = 0;

    $stripped_text = strip_tags($text);

    while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
    {
        $symbol  = $text{$i};
        $result .= $symbol;

        switch ($symbol)
        {
           case '<':
                $is_open   = true;
                $grab_open = true;
                break;

           case '"':
               if ($in_double_quotes)
                   $in_double_quotes = false;
               else
                   $in_double_quotes = true;

            break;

            case "'":
              if ($in_single_quotes)
                  $in_single_quotes = false;
              else
                  $in_single_quotes = true;

            break;

            case '/':
                if ($is_open && !$in_double_quotes && !$in_single_quotes)
                {
                    $is_close  = true;
                    $is_open   = false;
                    $grab_open = false;
                }

                break;

            case ' ':
                if ($is_open)
                    $grab_open = false;
                else
                    $stripped++;

                break;

            case '>':
                if ($is_open)
                {
                    $is_open   = false;
                    $grab_open = false;
                    array_push($tags, $tag);
                    $tag = "";
                }
                else if ($is_close)
                {
                    $is_close = false;
                    array_pop($tags);
                    $tag = "";
                }

                break;

            default:
                if ($grab_open || $is_close)
                    $tag .= $symbol;

                if (!$is_open && !$is_close)
                    $stripped++;
        }

        $i++;
    }

    while ($tags)
        $result .= "</".array_pop($tags).">";

    return $result;
}

function geodir_payment_notification_add_bcc_option($settings) {
	if (!empty($settings)) {
		$new_settings = array();
		foreach ($settings as $setting) {
			if (isset($setting['id']) && $setting['id']=='site_bcc_options' && isset($setting['type']) && $setting['type']=='sectionend') {
				
				$geodir_bcc_expire_yes = array(  
											'name' => __( 'Expire listings', 'geodir_payments' ),
											'desc' => __( 'Yes', 'geodir_payments' ),
											'id' => 'geodir_bcc_expire',
											'std' => 'yes',
											'type' => 'radio',
											'value' => '1',
											'radiogroup' => 'start'
										);
				
				$geodir_bcc_expire_no = array(  
											'name' => __( 'Expire listings', 'geodir_payments' ),
											'desc' => __( 'No', 'geodir_payments' ),
											'id' => 'geodir_bcc_expire',
											'std' => 'yes',
											'type' => 'radio',
											'value' => '0',
											'radiogroup' => 'end'
										);
				
				$new_settings[] = $geodir_bcc_expire_yes;
				$new_settings[] = $geodir_bcc_expire_no;
				
				$geodir_bcc_invoice_yes = array(  
											'name' => __( 'Payment Invoice', 'geodir_payments' ),
											'desc' => __( 'Yes', 'geodir_payments' ),
											'id' => 'geodir_bcc_invoice',
											'std' => 'yes',
											'type' => 'radio',
											'value' => '1',
											'radiogroup' => 'start'
										);
				
				$geodir_bcc_invoice_no = array(  
											'name' => __( 'Payment Invoice', 'geodir_payments' ),
											'desc' => __( 'No', 'geodir_payments' ),
											'id' => 'geodir_bcc_invoice',
											'std' => 'yes',
											'type' => 'radio',
											'value' => '0',
											'radiogroup' => 'end'
										);
				
				$new_settings[] = $geodir_bcc_invoice_yes;
				$new_settings[] = $geodir_bcc_invoice_no;
			}
			$new_settings[] = $setting;
		}
		$settings = $new_settings;
	}
		
	return $settings;
}

function geodir_payment_post_view_extra_class( $class , $all_postypes='') {
	global $post;
	
	if(!$all_postypes){$all_postypes = geodir_get_posttypes();}
	
	$gdp_post_id = !empty( $post ) && isset( $post->ID ) ? $post->ID : NULL;
	$gdp_post_type = $gdp_post_id > 0 && isset( $post->post_type ) ? $post->post_type : NULL;
	$gdp_post_type = $gdp_post_type != '' && !empty( $all_postypes ) && in_array( $gdp_post_type, $all_postypes ) ? $gdp_post_type : NULL;
		
	if ( $gdp_post_id && $gdp_post_type ) {
		//$default_package = geodir_get_default_package( $gdp_post_type );
		
		$gdp_package_id = isset( $post->package_id ) ? $post->package_id : 0;
		//$gdp_package_id = !$gdp_package_id > 0 && !empty( $default_package ) && !empty( $default_package->pid ) ? $default_package->pid : $gdp_package_id;
		
		//$gdp_package_info = $gdp_package_id > 0 ? geodir_get_package_info( $gdp_package_id ) : NULL;
		
		$append_class = $gdp_package_id > 0 ? 'gd-post-pkg-' . $gdp_package_id : '';
		
		/*if ( !empty( $gdp_package_info ) ) {
			$append_class .= isset( $gdp_package_info->amount ) && (float)$gdp_package_info->amount > 0 ? ' gd-post-pkg-paid' : ' gd-post-pkg-free';			
		}*/
		
		if ( isset($post->paid_amountt) ) {
			$append_class .= isset( $gdp_package_info->amount ) && (float)$gdp_package_info->amount > 0 ? ' gd-post-pkg-paid' : ' gd-post-pkg-free';			
		}
		
		if ( $append_class != '' ) {	
			$class = $class != '' ? $class . ' ' . trim( $append_class ) : trim( $append_class );
		}
	}
	
	return $class;
}

function geodir_payment_get_units_to_days( $value, $unit ) {
	switch ( $unit ) {
		case 'W':
			$value = $value * 7;
		break;
		case 'M':
			$value = $value * 30;
		break;
		case 'Y':
			$value = $value * 365;
		break;
	}
	
	return $value;
}

/* display listing expire date on author dashboard listing*/
/* display listing expire date on author dashboard listing*/
function geodir_payment_display_expire_date_on_listing() {
	global $post;
	
	$show_expire_date = get_option( 'geodir_listing_expiry' ) && get_option( 'geodir_payment_expire_date_on_listing' );
	
	$html = '';
	if ( $show_expire_date && get_current_user_id() ) {
		if ( geodir_is_page('author') && !empty( $post ) && isset( $post->post_author ) && $post->post_author == get_current_user_id() ) {
			$post_expire_date = geodir_get_post_meta( $post->ID, 'expire_date', true);
			$post_expire_time = strtotime( $post_expire_date );
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
			
			$expire_date_text = __( 'Unknown', 'geodir_payments' );
			$expire_date_class = 'geodir-expire-unknown';
			if ( $post_expire_date != '0000-00-00' && $post_expire_date != '' && ( geodir_strtolower( $post_expire_date ) == 'never' || $post_expire_time > strtotime( date( '01-01-1970' ) ) ) )  {
				if ( geodir_strtolower( $post_expire_date ) == 'never' ) {
					$expire_date_text = __( 'Never', 'geodir_payments' );
					$expire_date_class = 'geodir-expire-never';
				} else {
					$expire_date_text = geodir_payment_time_diff( $post_expire_time );
					$expire_date_text = date_i18n( geodir_default_date_format(), $post_expire_time ) . ' (<font class="geodir-expire-diff">'.$expire_date_text.'</font>)';
					
					$expire_date_class = $post_expire_time >= strtotime( $current_date ) ? 'geodir-expire-left' : 'geodir-expire-overdue';
				}
			}
			
			$html = '<span class="geodir-expire-date ' . $expire_date_class . '"><i class="fa fa-calendar"></i> <font class="geodir-expire-label">' . __( 'Expire date: ', 'geodir_payments' ) . '</font><font class="geodir-expire-text">' . $expire_date_text . '</font></span>';
		}
	}
	
	if ( $html != '' ) {
		echo apply_filters( 'geodir_payment_filter_expire_date_on_listing', $html );
	}
}

/* display listing expire date on author listing detail*/
function geodir_payment_display_expire_date_on_detail() {
	global $post;
	
	$show_expire_date = get_option( 'geodir_listing_expiry' ) && get_option( 'geodir_payment_expire_date_on_detail' );
	
	$html = '';
	if ( $show_expire_date && get_current_user_id() ) {
		if ( geodir_is_page('detail') && !empty( $post ) && isset( $post->post_author ) && $post->post_author == get_current_user_id() ) {
			$post_expire_date = geodir_get_post_meta( $post->ID, 'expire_date', true);
			$post_expire_time = strtotime( $post_expire_date );
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
			
			$expire_date_text = __( 'Unknown', 'geodir_payments' );
			$expire_date_class = 'geodir-expire-unknown';
			if ( $post_expire_date != '0000-00-00' && $post_expire_date != '' && ( geodir_strtolower( $post_expire_date ) == 'never' || $post_expire_time > strtotime( date( '01-01-1970' ) ) ) )  {
				if ( geodir_strtolower( $post_expire_date ) == 'never' ) {
					$expire_date_text = __( 'Never', 'geodir_payments' );
					$expire_date_class = 'geodir-expire-never';
				} else {
					$expire_date_text = geodir_payment_time_diff( $post_expire_time );
					$expire_date_text = date_i18n( geodir_default_date_format(), $post_expire_time ) . ' (<font class="geodir-expire-diff">'.$expire_date_text.'</font>)';
					
					$expire_date_class = $post_expire_time >= strtotime( $current_date ) ? 'geodir-expire-left' : 'geodir-expire-overdue';
				}
			}
			
			$html = '<span class="geodir-expire-date ' . $expire_date_class . '"><font class="geodir-expire-label">' . __( 'Expire date: ', 'geodir_payments' ) . '</font><font class="geodir-expire-text">' . $expire_date_text . '</font></span>';
			$html = '<p class="geodir_expire_date"><i class="fa fa-calendar"></i> ' . $html . '</p>';
		}
	}
	
	if ( $html != '' ) {
		echo apply_filters( 'geodir_payment_filter_expire_date_on_detail', $html );
	}
}

function geodir_payment_time_diff( $from, $to = '' ) {
	if ( empty( $to ) ) {
		$to = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );
	}

	$diff = (int) abs( $to - $from );

	if ( $diff >= YEAR_IN_SECONDS ) {
		$years = round( $diff / YEAR_IN_SECONDS );
		
		if ( $years <= 1 ) {
			$years = 1;
		}
		
		if ( $to <= $from ) {
			$since = sprintf( _n( '%s year left', '%s years left', $years, 'geodir_payments' ), $years );
		} else {
			$since = sprintf( _n( '%s year overdue', '%s years overdue', $years, 'geodir_payments' ), $years );
		}
	} else {
		$days = round( $diff / DAY_IN_SECONDS );
		
		if ( $days <= 1 ) {
			$days = 1;
		}
		
		if ( $to == $from ) {
			$since = __( 'today', 'geodir_payments' );
		} elseif ( $to < $from ) {
			$since = sprintf( _n( '%s day left', '%s days left', $days, 'geodir_payments' ), $days );
		} else {
			$since = sprintf( _n( '%s day overdue', '%s days overdue', $days, 'geodir_payments' ), $days );
		}
	}

	return $since;
}

// add fields on update listing form
add_action('geodir_after_main_form_fields', 'geodir_payment_after_main_form_fields', 1);
function geodir_payment_after_main_form_fields() {
	global $gd_session;
	
	if (!empty($_REQUEST['pid']) && !empty($_REQUEST['package_id'])) {
		$post_type = get_post_type($_REQUEST['pid']);
		$prev_package_id = geodir_get_post_meta($_REQUEST['pid'], 'package_id', true);
		$prev_expire_date = geodir_get_post_meta($_REQUEST['pid'], 'expire_date', true);

		$gd_session->set('geodir_prev_package_id', $prev_package_id);
		$gd_session->set('geodir_prev_expire_date', $prev_expire_date);

		if ($post_type != '' && in_array($post_type, geodir_get_posttypes()) && $prev_package_id ==$_REQUEST['package_id'] && $prev_expire_date != '' && geodir_strtolower($prev_expire_date) != 'never' && strtotime($prev_expire_date) >= strtotime(date('Y-m-d'))) {
			echo '<input type="hidden" id="geodir_prev_package_id" name="geodir_prev_package_id" value="'.$prev_package_id.'" /><input type="hidden" id="geodir_prev_expire_date" name="geodir_prev_expire_date" value="'.$prev_expire_date.'" />';
		}
	}
}

// delete invoice
if ( is_admin() ) {
add_action( 'wp_ajax_geodir_del_invoice', 'geodir_del_invoice' );
}
function geodir_del_invoice($id='') {
	global $wpdb; 
if($id){$invoice_id = $id;}
	else{$invoice_id = intval( $_POST['invoice_id'] );}
if(!$invoice_id ){return;}
	
      $del = $wpdb->query($wpdb->prepare("DELETE FROM ".INVOICE_TABLE." WHERE id=%d", array($invoice_id)));
	if($del){echo 1;}else{echo 0;}
	die(); 
}

// delete invoice when post is deleted
add_action( 'admin_init', 'geodir_del_invoice_from_post_init' );
function geodir_del_invoice_from_post_init() {
    if ( current_user_can( 'delete_posts' ) )
        add_action( 'delete_post', 'geodir_del_invoice_from_post', 10 );
}

function geodir_del_invoice_from_post( $pid ) {
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM ".INVOICE_TABLE." WHERE post_id=%d", array($pid)));
    return true;
}

function geodir_diagnose_run_expire()
{	global $wpdb,$plugin_prefix;
	
	$is_error_during_diagnose = false;
	$output_str = '';
	
	geodir_expire_check();
	$output_str .= "<li>".__('Done' , 'geodir_payments' )."</li>" ;



if($is_error_during_diagnose)
	{
		$info_div_class =  "geodir_problem_info" ;
		$fix_button_txt = "<input type='button' value='".__('Fix' , 'geodir_payments' )."' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='ratings' />";
	}
	else
	{
		$info_div_class =  "geodir_noproblem_info" ;
		$fix_button_txt = '';
	}
	echo "<ul class='$info_div_class'>" ;
	echo $output_str ;
	echo  $fix_button_txt;
	echo "</ul>" ;
	
}

/**
 * Checks to show/hide related listing tab on detail page.
 *
 * @since 1.2.3
 *
 * @param array|object $package_info Price package info.
 * @return bool Returns true on success & false on fail.
 */
function geodir_payments_hide_related_tab( $post ) {
	if ( empty( $post ) ) {
		return false;
	}
	
	$package_info = geodir_post_package_info( array(), $post );
	
	if ( !empty( $package_info ) && is_object( $package_info ) && isset( $package_info->hide_related_tab ) && (int)$package_info->hide_related_tab == 1 ) {
		return true;
	} else if ( !empty( $package_info ) && is_array( $package_info ) && isset( $package_info['hide_related_tab'] ) && (int)$package_info['hide_related_tab'] == 1 ) {
		return true;
	}
	
	return false;
}

/**
 * Filter the related listing tab should be displayed on detail page or not.
 *
 * @since 1.2.3
 *
 * @global WP_Post $post WP Post object. Default current post.
 *
 * @param bool $is_display True if related listing should be displayed, otherwise false.
 * @param string $tab The listing detail page tab.
 * @return True if related listing should be displayed, otherwise false.
 */
function geodir_payment_related_listing_is_display( $is_display, $tab ) {
	global $post;

    if ( $tab == 'related_listing' && ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) && !empty( $post ) ) {		
		if ( geodir_payments_hide_related_tab( $post ) ) {
			$is_display = false;
		}
	}

    return $is_display;
}

function geodir_payment_get_tax_amount( $amount, $package_id, $post_id = 0 ) {
	$tax_amount = 0;
	
	$tax_amount = apply_filters( 'geodir_payment_get_tax_amount', $tax_amount, $amount, $package_id, $post_id );
	
	$tax_amount = geodir_payment_price( $tax_amount, false );

    return $tax_amount;
}

function geodir_payment_all_payment_status( $names = true ) {
	$payment_status = array();
	$payment_status['confirmed'] = __( 'Confirmed', 'geodir_payments' );
	$payment_status['pending'] = __( 'Pending', 'geodir_payments' );
	$payment_status['cancelled'] = __( 'Cancelled', 'geodir_payments' );
	$payment_status['failed'] = __( 'Failed', 'geodir_payments' );
	$payment_status['onhold'] = __( 'On Hold', 'geodir_payments' );
	$payment_status['refunded'] = __( 'Refunded', 'geodir_payments' );
	
	$payment_status = apply_filters( 'geodir_payment_all_payment_status', $payment_status );
	
	if ( $names ) {
		$payment_status = array_keys( $payment_status );
	}
	
	return $payment_status;
}

function geodir_payment_status_name( $name ) {
	$payment_status = geodir_payment_all_payment_status( false );
	
	if ( $name != '' ) {
		$name = isset( $payment_status[$name] ) ? $payment_status[$name] : __( $name, 'geodir_payments' );
	}
	
	return $name;
}

function geodir_payment_invoice_types( $keys = true ) {
	$invoice_types = array();
	$invoice_types['add_listing'] = __( 'Add Listing', 'geodir_payments' );
	$invoice_types['upgrade_listing'] = __( 'Upgrade Listing', 'geodir_payments' );
	$invoice_types['renew_listing'] = __( 'Renew Listing', 'geodir_payments' );
	$invoice_types['add_franchises'] = __( 'Add Franchises', 'geodir_payments' );
	$invoice_types['claim_listing'] = __( 'Claim Listing', 'geodir_payments' );
	
	$invoice_types = apply_filters( 'geodir_payment_invoice_types', $invoice_types );
	
	if ( $keys ) {
		$invoice_types = array_keys( $invoice_types );
	}
	
	return $invoice_types;
}

function geodir_payment_invoice_type_name( $type ) {
	$invoice_types = geodir_payment_invoice_types( false );
	
	$type = $type != '' ? $type : 'add_listing';
	
	if ( $type != '' ) {
		$type = isset( $invoice_types[$type] ) ? $invoice_types[$type] : __( $type, 'geodir_payments' );
	}
	
	return $type;
}

function geodir_payment_checkout_page_id(){
    $gd_page_id = get_option('geodir_checkout_page');

    if (geodir_is_wpml()) {
        $gd_page_id =  geodir_wpml_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

function geodir_payment_invoices_page_id(){
    $gd_page_id = get_option('geodir_invoices_page');

    if (geodir_is_wpml()) {
        $gd_page_id =  geodir_wpml_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

function geodir_payment_locate_template( $template = ' ') {
    switch ( $template ) {
        case 'checkout':
			$template = locate_template( array( 'geodir_payment_manager/checkout.php' ) );
		break;
		case 'invoices':
			$template = locate_template( array( 'geodir_payment_manager/invoices.php' ) );
		break;
		case 'invoice':
			$template = locate_template( array( 'geodir_payment_manager/invoice.php' ) );
		break;
		default:
			$template = NULL;
		break;
	}

    return $template;
}

function geodir_payment_template_loader( $template ) {
	if ( geodir_payment_is_page( 'checkout' ) ) {
		$template = geodir_payment_locate_template( 'checkout' );

		if ( !$template ) {
			$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/checkout.php';
		}
		
		return $template = apply_filters( 'geodir_template_checkout', $template );
	} else if ( geodir_payment_is_page( 'invoices' ) ) {
		if ( geodir_payment_is_page( 'invoice' ) ) {
			$template = geodir_payment_locate_template( 'invoice' );
	
			if ( !$template ) {
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/invoice.php';
			}
			
			$template = apply_filters( 'geodir_template_invoice_detail', $template );
		} else {
			$template = geodir_payment_locate_template( 'invoices' );
	
			if ( !$template ) {
				$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/invoices.php';
			}
			
			$template = apply_filters( 'geodir_template_invoices', $template );
		}
	} else if ( geodir_payment_is_page( 'expired' ) ) {
		$template = geodir_payment_locate_template( 'expired' );

		if ( !$template ) {
			$template = GEODIR_PAYMENT_MANAGER_PATH . '/geodir-payment-templates/expired.php';
		}
		
		return $template = apply_filters( 'geodir_template_expired', $template );
	}
	
	return $template;
}
add_filter( 'template_include', 'geodir_payment_template_loader', 10 );

function geodir_payment_is_page( $gdpage = '' ) {
    global $wp_query, $post;

    switch ( $gdpage ) {
        case 'checkout':
            if ( is_page() && get_query_var( 'page_id' ) == geodir_payment_checkout_page_id() ) {
                return true;
            } else if ( is_page() && isset( $post->post_content ) && has_shortcode( $post->post_content, 'gd_checkout' ) ) {
                return true;
            }
            break;
		case 'invoices':
            if ( is_page() && get_query_var( 'page_id' ) == geodir_payment_invoices_page_id() ) {
                return true;
            } else if ( is_page() && isset( $post->post_content ) && has_shortcode( $post->post_content, 'gd_invoices' ) ) {
                return true;
            }
            break;
		case 'invoice':
            if ( geodir_payment_is_page( 'invoices' ) && !empty( $_GET['invoice_id']) ) {
                return true;
            }
            break;
        case 'expired':
            if ( is_single() && !empty( $post->ID ) && !empty( $wp_query->is_expired ) && $post->ID == $wp_query->is_expired ) {
                return true;
            }
            break;
        default:
            return false;
            break;
	}
	
	return false;
}

function geodir_payment_checkout_redirect( $invoice_id ) {
	$page_id = geodir_payment_checkout_page_id();
	
	$redirect_url = geodir_getlink( get_permalink( $page_id ) );
	
	$cart_id = geodir_payment_cart_id( $invoice_id );
	
	do_action( 'geodir_payment_pre_checkout', $invoice_id );
	
	$redirect_url = apply_filters( 'geodir_payment_checkout_redirect_url', $redirect_url );
	
	wp_redirect( $redirect_url );
	gd_die();
}
add_action( 'geodir_payment_checkout_redirect', 'geodir_payment_checkout_redirect' );

function geodir_payment_cart_id( $invoice_id = '' ) {	
	global $gd_session;
	
	$user_ID = get_current_user_id();
	
	if ( !$user_ID ) {
		geodir_payment_clear_cart();
		
		return NULL;
	}
	
	if ( $invoice_id > 0  ) {
		$gd_session->set('gd_cart_id', $invoice_id);
	}
	
	$cart_id = (int)$gd_session->get('gd_cart_id');
	
	$cart_info = $cart_id > 0 ? geodir_get_invoice( $cart_id ) : NULL;
	if ( empty( $cart_info ) ) {
		geodir_payment_clear_cart();
		
		return NULL;
	}
	
	if ( $user_ID != $cart_info->user_id ) {
		geodir_payment_clear_cart();
		
		return NULL;
	}
	
	$cart_id = apply_filters( 'geodir_payment_cart_id', $cart_id, $invoice_id );
	
	return $cart_id;
}

function geodir_payment_cart_post_type( $cart_id ) {
	$cart = geodir_get_invoice( $cart_id );
	
	if ( empty( $cart ) ) {
		return NULL;
	}
	
	$post_type = get_post_type( $cart->post_id );
	
	return $post_type;
}

function geodir_payment_get_cart( $main_cart_id = '', $validate = true ) {	
	$cart_id = $main_cart_id > 0 ? $main_cart_id : geodir_payment_cart_id();
	
	if ( !$cart_id ) {		
		return NULL;
	}
	
	$cart = geodir_get_invoice( $cart_id );
	$post_type = geodir_payment_cart_post_type($cart_id);
	
	if ( !$cart ) {		
		return NULL;
	}
	
	if ( $validate ) {
		$coupon_code = $cart->coupon_code;
		if (!($coupon_code != '' && $cart_id && geodir_payment_allow_coupon_usage(array('cart_id' => $cart_id)) && geodir_is_valid_coupon($post_type, $coupon_code))) {
			$coupon_code = '';
		}
		
		$amount = $cart->amount;
		$tax_amount = $cart->tax_amount;
		$discount = $coupon_code != '' ? geodir_get_discount_amount( $coupon_code, $amount ) : 0;
		
		$amount = geodir_payment_price( $amount, false );

		$paied_amount = ( $amount + $tax_amount ) - $discount;
		$paied_amount = $paied_amount > 0 ? $paied_amount : 0;
		
		$cart->coupon_code = $coupon_code;
		$cart->amount = $amount;
		$cart->tax_amount = $tax_amount;
		$cart->discount = $discount;
		$cart->paied_amount = $paied_amount;
	}
	
	$cart->amount_display = geodir_payment_price( $cart->amount );
	$cart->tax_amount_display = geodir_payment_price( $cart->tax_amount );
	$cart->discount_display = geodir_payment_price( $cart->discount );
	$cart->paied_amount_display = geodir_payment_price( $cart->paied_amount );
	
	$cart = apply_filters('geodir_payment_get_cart', $cart);
	
	return $cart;
}

function geodir_payment_clear_cart() {
	global $gd_session;
	
	do_action( 'geodir_payment_clear_cart_before' );
	
	$gd_session->un_set('gd_cart_id');
	
	do_action( 'geodir_payment_clear_cart_after' );
	
	return true;
}

function geodir_payment_set_custom_page( $custom_pages = array() ) {
	if ( geodir_payment_is_page( 'checkout' ) ) {
		$custom_pages['geodir_set_custom_checkout_page'] = true;
	}
	
	if ( geodir_payment_is_page( 'invoices' ) ) {
		$custom_pages['geodir_set_custom_invoices_page'] = true;
	}
	
	return $custom_pages;
}
add_filter( 'geodir_set_custom_pages', 'geodir_payment_set_custom_page' );

function geodir_payment_action_checkout_page_title() {
    global $post;

	$class = apply_filters('geodir_page_title_class', 'entry-title fn');
	
	$class_header = apply_filters('geodir_page_title_header_class', 'entry-header');
	
	echo '<header class="' . $class_header . '"><h1 class="' . $class . '">';

    $title = get_the_title($post->ID);
	
	echo apply_filters('geodir_checkout_page_title_text', $title);
	
	echo '</h1></header>';
}
add_action( 'geodir_checkout_before_page_content', 'geodir_payment_action_checkout_page_title', 10 );

function geodir_payment_action_invoices_page_title() {
	$class = apply_filters('geodir_page_title_class', 'entry-title fn');
	
	$class_header = apply_filters('geodir_page_title_header_class', 'entry-header');
	
	echo '<header class="' . $class_header . '"><h1 class="' . $class . '">';
	
	$title = __( 'Manage Invoices' , 'geodir_payments' );
	
	echo apply_filters('geodir_invoices_page_title_text', $title);
	
	echo '</h1></header>';
}
add_action( 'geodir_invoices_before_page_content', 'geodir_payment_action_invoices_page_title', 10 );

function geodir_payment_invoice_detail_page_title( $invoice_id = NULL ) {
	$class = apply_filters('geodir_page_title_class', 'entry-title fn');
	
	$class_header = apply_filters('geodir_page_title_header_class', 'entry-header');
	
	echo '<header class="' . $class_header . '"><h1 class="' . $class . '">';
	
	$title = wp_sprintf( __( 'Invoice #%s' , 'geodir_payments' ), geodir_payment_invoice_id_formatted($invoice_id) );
	
	echo apply_filters('geodir_invoice_detail_page_title_text', $title);
	
	echo '</h1></header>';
}

function geodir_payment_subscription_methods() {
	$methods = array( 'payment_method_paypal', 'payment_method_2co' );
	$methods = apply_filters( 'geodir_subscription_methods', $methods );
	
	return $methods;
}

function geodir_payment_get_methods( $recurring = false ) {
	global $wpdb;
	
	if ( $recurring && $subscription_methods = geodir_payment_subscription_methods() ) {
		$where = "IN (" . implode( ',', array_fill( 0, count( $subscription_methods ), '%s' ) ) . ")";
		$params = $subscription_methods;		
	} else {
		$where = "LIKE %s";
		$params = array( 'payment_method_%' );
	}
	
	$query = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name " . $where, $params );
	$results = $wpdb->get_results( $query );
	
	$return = array();
	
	if ( !empty( $results ) ) {
		foreach ( $results as $row ) {
			$option_name = $row->option_name;			
			$option_info = maybe_unserialize( $row->option_value );
			
			if ( !empty( $option_info ) && isset( $option_info['isactive'] ) && $option_info['isactive'] ) {
				$return[$option_info['display_order']][] = $option_info;
			}
		}
		
		if ( !empty( $return ) ) {
			ksort( $return );
			
			$rows = $return;
			
			$return = array();
			foreach ( $rows as $row ) {
				$return = array_merge( $return, $row );
			}
		}
	}
	
	$return = apply_filters( 'geodir_payment_get_methods', $return );
	
	return $return;
}

function geodir_payment_method_title( $payment_method ) {
	global $wpdb;
	
	$query = $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", array( 'payment_method_' . $payment_method ) );
	$row = $wpdb->get_row( $query );
	
	$value = __( $payment_method , 'geodir_payments' );
	
	if ( !empty( $row ) ) {
		$option_name = $row->option_name;			
		$option_info = maybe_unserialize( $row->option_value );
			
		if ( !empty( $option_info ) && !empty( $option_info['name'] ) ) {
			$value = __( $option_info['name'] , 'geodir_payments' );
		}
	}
	
	$value = apply_filters( 'geodir_payment_method_title', $value, $payment_method );
	
	return $value;
}

function geodir_payment_price( $amount, $display = true, $decimal_sep = '.', $thousand_sep = "," ) {
	if ( !$display ) {
		$decimal_sep = '.';
		$thousand_sep = '';
	}
	
	$price = number_format( (float)$amount, 2, $decimal_sep, $thousand_sep );
	
	if ( $display ) {
		$symbol = geodir_get_currency_sym();
		$position = geodir_payment_get_currency_position();
		
		$format = '%1$s%2$s';
		switch($position) {
			case 'left':
				$format = '%1$s%2$s';
			break;
			case 'right':
				$format = '%2$s%1$s';
			break;
			case 'left_space':
				$format = '%1$s&nbsp;%2$s';
			break;
			case 'right_space':
				$format = '%2$s&nbsp;%1$s';
			break;
		}
		
		$price = wp_sprintf($format, $symbol, $price);
	}
	
	$price = apply_filters( 'geodir_payment_price', $price, $amount, $display, $decimal_sep, $thousand_sep );
	
	return $price;
}

function geodir_payment_method_fields( $payment_method ) {
	if ( file_exists( GEODIR_PAYMENT_MANAGER_PATH . $payment_method . '/' . $payment_method . '.php' ) ) {
		include_once( GEODIR_PAYMENT_MANAGER_PATH . $payment_method . '/' . $payment_method . '.php' );
	}
}
add_action( 'geodir_payment_method_fields', 'geodir_payment_method_fields' );

function geodir_payment_cart_button_text( $text, $payment_method = '' ) {
	if ( $payment_method == 'paypal' ) {
		$text = __( 'Proceed to PayPal', 'geodir_payments' );
	}
	
	return $text;
}
add_filter( 'geodir_payment_cart_button_text', 'geodir_payment_cart_button_text', 0, 2 );

function geodir_payment_coupon_info_by_code( $coupon_code ) {
	global $wpdb;
	
	if ( $coupon_code == '' ) {
		return false;
	}
	
	$query = $wpdb->prepare( "SELECT * FROM `" . COUPON_TABLE . "` WHERE coupon_code = %s", array( $coupon_code ) );
	$row = $wpdb->get_row( $query );
	
	return $row;
}

function geodir_payment_invoice_is_recurring_pkg( $invoice ) {
	$invoice_info = !empty( $invoice ) && is_object( $invoice ) ? $invoice : geodir_get_invoice( $invoice );
	
	if ( !geodir_payment_invoice_is_valid( $invoice_info ) ) {
		return false;
	}
	
	$recurring = false;
	
	if ( $invoice_info->invoice_type == 'add_listing' || $invoice_info->invoice_type == 'upgrade_listing' || $invoice_info->invoice_type == 'renew_listing' ) {
		$package_info = (array)geodir_get_post_package_info( $invoice_info->package_id, $invoice_info->post_id );
		
		if ( !empty( $package_info ) && !empty( $package_info['sub_active'] ) ) {
			$recurring = true;
		}
	}
	
	$recurring = apply_filters( 'geodir_payment_invoice_is_recurring_pkg', $recurring, $invoice_info );
	
	return $recurring;
}

function geodir_payment_coupon_is_recurring( $coupon_code ) {
	$coupon_info = geodir_payment_coupon_info_by_code( $coupon_code );
	
	if ( !empty( $coupon_info ) && !empty( $coupon_info->recurring ) ) {
		return true;
	}
	
	return false;
}

function geodir_payment_invoice_is_valid( $invoice, $owner = true ) {
	$invoice_info = !empty( $invoice ) && is_object( $invoice ) ? $invoice : geodir_get_invoice( $invoice );
		
	$user_id 		= get_current_user_id();
	
	if ( !$user_id || empty( $invoice_info ) ) {
		return false;
	}
	
	$valid 			= true;
	$invoice_id 	= $invoice_info->id;
	$invoice_type 	= $invoice_info->invoice_type;
	$post_id 		= $invoice_info->post_id;
	$owner_id 		= $invoice_info->user_id;
	$post_type 		= get_post_type( $post_id );
	
	$gd_post_types 	= geodir_get_posttypes();
	
	if ( $invoice_type == 'add_lisitng' ) {
		$post_type = get_post_type( $post_id );
		
		if ( !in_array( $post_type, $gd_post_types ) ) {
			$valid = false;	
		}
	}
	
	if ( $user_id != $owner_id ) {
		$valid = false;
	}
	
	$valid = apply_filters( 'geodir_payment_invoice_is_valid', $valid, $invoice_info, $owner );
	
	return $valid;
}

function geodir_payment_allow_pay_for_invoice( $invoice ) {
	$allow = false;
	
	$invoice_info = !empty( $invoice ) && is_object( $invoice ) ? $invoice : geodir_get_invoice( $invoice );
	
	if ( !geodir_payment_invoice_is_valid( $invoice_info ) ) {
		return $allow;
	}
	
	$invoice_type 	= $invoice_info->invoice_type;
	$status 		= $invoice_info->status;
	
	if ( $invoice_type == 'add_listing' || $invoice_type == 'upgrade_listing' || $invoice_type == 'renew_listing' ) {
		if ( in_array( $status, array( 'failed', 'pending' ) ) ) {
			$allow = true;	
		}
	}
	
	$allow = apply_filters( 'geodir_payment_allow_pay_for_invoice', $allow, $invoice_info );
	
	return $allow;
}

function geodir_payment_invoice_info_title_meta( $invoice ) {
	$invoice_info = !empty( $invoice ) && is_object( $invoice ) ? $invoice : geodir_get_invoice( $invoice );
	
	if ( !geodir_payment_invoice_is_valid( $invoice_info ) ) {
		return NULL;
	}
	
	$invoice_id = $invoice_info->id;
	
	$invoice_link = geodir_payment_invoice_page_link( $invoice_id );
	
	$info = '<a href="' . esc_url( $invoice_link ) . '" title="' . esc_attr( __( 'View invoice details', 'geodir_payments' ) ) . '">' . __( 'View Invoice', 'geodir_payments' ) . '</a>';
	$info = apply_filters( 'geodir_payment_invoice_info_title_meta', $info, $invoice_info );
	
	return $info;
}

function geodir_payment_invoice_info_status_meta( $invoice ) {
	$invoice_info = !empty( $invoice ) && is_object( $invoice ) ? $invoice : geodir_get_invoice( $invoice );
	
	if ( !geodir_payment_invoice_is_valid( $invoice_info ) ) {
		return NULL;
	}
	
	$invoice_id = $invoice_info->id;
	
	$info = '';
	
	$pay_for_invoice = geodir_payment_allow_pay_for_invoice( $invoice_info );
	if ( $pay_for_invoice ) {
		 $info .= '<a href="javascript:void(0)" onclick="gd_invoice_paynow(' . (int)$invoice_id . ', jQuery(\'tr[data-id='.$invoice_id.']\',\'#gd_payment_invoices\'));">' . __( 'Pay For Invoice', 'geodir_payments' ) . '</a>';
	}
	
	$info = apply_filters( 'geodir_payment_invoice_info_status_meta', $info, $invoice_info );
	
	return $info;
}

function geodir_payment_send_invoice( $invoice_id ) {
	$invoice_info = geodir_get_invoice( $invoice_id );
	
	if ( empty( $invoice_info ) ) {
		return false;
	}
	
	$dat_format = geodir_default_date_format() . ' ' . get_option( 'time_format' );
	
	$site_name 	= get_site_emailName();
	$site_url 	= home_url( '/' );
	$site_email = geodir_get_site_email_id();
	$admin_email = get_option( 'admin_email' );
		
	$user_id = $invoice_info->user_id;
	$user_data = get_userdata( $user_id );
	
	if ( empty( $user_data ) ) {
		return false;
	}
	
	$user_email = $user_data->user_email;
	$user_name 	= geodir_get_client_name( $user_id );
	$to_email  	= $user_email;
	$user_login = $user_data->user_login;

	$loginurl = geodir_login_url();
	$loginurl_link = '<a href="'.$loginurl.'">'.__('login', 'geodir_payments').'</a>';
	
	$params = array();
	$params['site_name'] 		= $site_name;
	$params['site_name_url'] 	= $site_url;
	$params['site_url'] 		= $site_url;
	$params['site_email'] 		= $site_email;
	
	$params['user_id'] 			= $user_id;
	$params['user_email'] 		= $user_email;
	$params['user_name'] 		= $user_name;
	$params['username'] 		= $user_login;
	
	$params['client_id'] 		= $user_id;
	$params['client_email'] 	= $user_email;
	$params['client_name'] 		= $user_name;

	$params['login_url'] 		= $loginurl_link;
	
	$date = $invoice_info->date_updated != '0000-00-00 00:00:00' ? $invoice_info->date_updated : $invoice_info->date;
	$date = $date != '0000-00-00 00:00:00' ? $date : '';
	$invoice_date = $date != '' ? date_i18n( $dat_format, strtotime( $date ) ) : '';
							
	$invoice_amount = geodir_payment_price( $invoice_info->paied_amount );
		
	$post_id		= $invoice_info->post_id;
	$package_id 	= $invoice_info->package_id;
	$invoice_title 	= $invoice_info->post_title;
	$transaction_details = $invoice_info->HTML;
	$tax_amount 	= $invoice_info->tax_amount;
	$discount 		= $invoice_info->discount;
	$coupon_code 	= $invoice_info->coupon_code;
	$invoice_type 	= geodir_payment_invoice_type_name( $invoice_info->invoice_type );
	$payment_method = geodir_payment_method_title( $invoice_info->paymentmethod );
	$invoice_status = geodir_payment_status_name( $invoice_info->status );
	
	$tax_amount 		= $tax_amount > 0 ? geodir_payment_price( $tax_amount ) : '';
	$discount_amount 	= $discount > 0 ? geodir_payment_price( $discount ) : '';
	$discount_coupon 	= $discount > 0 && $coupon_code != '' ? $coupon_code : '';
		
	$params['invoice_id'] 		= geodir_payment_invoice_id_formatted($invoice_id);
	$params['invoice_title'] 	= $invoice_title;
	$params['invoice_type'] 	= $invoice_type;
	$params['invoice_amount'] 	= $invoice_amount;
	$params['payment_method'] 	= $payment_method;
	$params['invoice_status'] 	= $invoice_status;
	$params['invoice_date'] 	= $invoice_date;
	$params['tax_amount'] 		= $tax_amount;
	$params['discount_amount'] 	= $discount_amount;
	$params['discount_coupon'] 	= $discount_coupon;
	$params['transaction_details'] 	= $transaction_details;
	$params['invoice_link'] 	= geodir_payment_invoice_page_link($invoice_id);
	
	$listing_title 		= '';
	$listing_link 		= '';
	$package_name 		= $invoice_info->package_title;
	if ( ( $invoice_info->invoice_type == 'add_listing' || $invoice_info->invoice_type == 'upgrade_listing' || $invoice_info->invoice_type == 'renew_listing' || $invoice_type == 'claim_listing' ) && $post_id > 0 ) {
		$listing_title 	= get_the_title( $post_id );
		$listing_link 	= get_permalink( $post_id );
	}
	$params['listing_id'] 		= $post_id;
	$params['listing_title'] 	= $listing_title;
	$params['listing_link'] 	= $listing_link;
	$params['listing_link'] 	= $listing_link;
	$params['package_id'] 		= $package_id;
	$params['package_name'] 	= $package_name;
	
	$invoice_tax_details			= '';
	$invoice_discount_details		= '';
	$invoice_listing_details		= '';
	$invoice_package_details		= '';
	$invoice_custom_details			= '';
	$invoice_transaction_details	= '';
		
	if ( $tax_amount > 0 ) {
		$invoice_tax_details = __( '<p>Tax: [#tax_amount#]</p>', 'geodir_payments' );
	}
	
	if ( $discount > 0 ) {
		$invoice_discount_details = __( '<p>Discount: [#discount_amount#]</p><p>Discount Coupon: [#discount_coupon#]</p>', 'geodir_payments' );
	}
	
	if ( ( $invoice_info->invoice_type == 'add_listing' || $invoice_info->invoice_type == 'upgrade_listing' || $invoice_info->invoice_type == 'renew_listing' || $invoice_info->invoice_type == '' || $invoice_type == 'claim_listing' ) && $post_id > 0 ) {
		$invoice_listing_details = __( '<p>Listing ID: [#listing_id#]</p><p>Listing: <a href="[#listing_link#]">[#listing_title#]</a></p>', 'geodir_payments' );
		
		$invoice_package_details = __( '<p>Package ID: [#package_id#]</p><p>Package: [#package_name#]</p>', 'geodir_payments' );
	}
	
	if ( $transaction_details ) {
		$invoice_transaction_details = __( '<p><b>Transaction Details:</b></p><p>[#transaction_details#]</p>', 'geodir_payments' );
	}
	
	$invoice_tax_details 			= apply_filters( 'geodir_payment_send_invoice_tax_details', $invoice_tax_details, $invoice_info );
	$invoice_discount_details 		= apply_filters( 'geodir_payment_send_invoice_discount_details', $invoice_discount_details, $invoice_info );
	$invoice_listing_details 		= apply_filters( 'geodir_payment_send_invoice_listing_details', $invoice_listing_details, $invoice_info );
	$invoice_package_details 		= apply_filters( 'geodir_payment_send_invoice_package_details', $invoice_package_details, $invoice_info );
	$invoice_custom_details 		= apply_filters( 'geodir_payment_send_invoice_custom_details', $invoice_custom_details, $invoice_info );
	$invoice_transaction_details 	= apply_filters( 'geodir_payment_send_invoice_transaction_details', $invoice_transaction_details, $invoice_info );
	
	$params['invoice_tax_details']			= $invoice_tax_details;
	$params['invoice_discount_details']		= $invoice_discount_details;
	$params['invoice_listing_details']		= $invoice_listing_details;
	$params['invoice_package_details']		= $invoice_package_details;
	$params['invoice_custom_details']		= $invoice_custom_details;
	$params['invoice_transaction_details']	= $invoice_transaction_details;
	
	$subject = __(stripslashes_deep(get_option('geodir_payment_invoice_email_subject')), 'geodirectory');
	$message = __(stripslashes_deep(get_option('geodir_payment_invoice_email_body')), 'geodirectory');
	
	$headers  = array();
	$headers[] = 'Content-type: text/html; charset=UTF-8';
	$headers[] = 'Reply-To: '.$site_email;
	$headers[] = 'From: '.$site_name.' <'.$site_email.'>';
	
	foreach ( $params as $search => $replace ) {
		$message = str_replace( '[#' . $search . '#]', $replace, $message );
		$subject = str_replace( '[#' . $search . '#]', $replace, $subject );
	}
	
	if ( strpos($subject, '[#' ) !== false || strpos($message, '[#' ) !== false ) {
		foreach ( $params as $search => $replace ) {
			$message = str_replace( '[#' . $search . '#]', $replace, $message );
			$subject = str_replace( '[#' . $search . '#]', $replace, $subject );
		}
	}
	
	$sent = wp_mail( $to_email, $subject, $message, $headers );
	if( !$sent && function_exists( 'geodir_error_log' ) ) {
		if ( is_array( $to_email ) ) {
			$to_email = implode( ',', $to_email );
		}
		$log_message = sprintf(
			__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
			'send_invoice',
			date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
			$to_email,
			$subject
		);
		geodir_error_log( $log_message );
	}
	
	if ( get_option( 'geodir_bcc_invoice' ) ) {
		$subject .= ' ' . __('- ADMIN BCC COPY', 'geodir_payments');
		$sent = wp_mail( $admin_email, $subject, $message, $headers );
		if( !$sent && function_exists( 'geodir_error_log' ) ) {
			if ( is_array( $admin_email ) ) {
				$admin_email = implode( ',', $admin_email );
			}
			$log_message = sprintf(
				__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
				'send_invoice',
				date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
				$to_email,
				$subject
			);
			geodir_error_log( $log_message );
		}
	}
	
	return $to_email;
}

function geodir_payment_get_client_email( $user_id ) {
	$client_email = '';
	
	$user_data = get_userdata($user_id);
	
	$client_email = !empty( $user_data ) ? $user_data->user_email : NULL;
		
	return $client_email;
}

function geodir_payment_invoices_page_link() {
	$page_link = get_permalink( geodir_payment_invoices_page_id() );
	
	$page_link = apply_filters( 'geodir_payment_invoices_page_link', $page_link );
	
	return $page_link;
}

function geodir_payment_invoice_page_link( $invoice_id ) {
	$page_link = geodir_payment_invoices_page_link();
	
	$page_link = add_query_arg( array( 'invoice_id' => $invoice_id ), $page_link );
	
	$page_link = apply_filters( 'geodir_payment_invoice_page_link', $page_link, $invoice_id );
	
	return $page_link;
}

function geodir_payment_check_invoice_owner( $invoice, $user_id ) {
	$invoice_info = !empty( $invoice ) && is_object( $invoice ) ? $invoice : geodir_get_invoice( $invoice );
	
	if ( !geodir_payment_invoice_is_valid( $invoice_info ) ) {
		return false;
	}
	
	$owner = false;
	
	if ( $user_id > 0 && $user_id == $invoice_info->user_id ) {
		$owner = true;
	}
	
	$owner = apply_filters( 'geodir_payment_check_invoice_owner', $owner, $invoice_info );
	
	return $owner;
}

function geodir_payment_invoice_coupon_usage_count($invoice_id) {
	global $wpdb;
	
	$invoice_info = geodir_get_invoice($invoice_id);

	if (empty($invoice_info)) {
		return false;
	}
	
	if (empty($invoice_info->coupon_code) || !geodir_payment_allow_coupon_usage(array('cart_id' => $invoice_id))) {
		return false;
	}

	$status = $invoice_info->status;
	$code = $invoice_info->coupon_code;
	$usage_status = $invoice_info->coupon_usage;
		
	$increase = false;
	$decrease = false;

	switch($usage_status) {
		case 'increase':
			if (in_array($status, array('cancelled', 'refunded'))) {
				$decrease = true;
			}
		break;
		case 'decrease':
			if (in_array($status, array('confirmed', 'pending', 'onhold', 'failed'))) {
				$increase = true;
			}
		break;
		default:
			if (in_array($status, array('confirmed', 'pending', 'onhold', 'failed'))) {
				$increase = true;
			}
			
			if (in_array($status, array('cancelled', 'refunded'))) {
				$decrease = true;
			}
		break;
	}

	if ($increase || $decrease) {
		$data = array();
		if ($increase) {
			geodir_payment_increase_coupon_usage_count($code);
			$data['coupon_usage'] = 'increase';
		}
		
		if ($decrease) {
			geodir_payment_decrease_coupon_usage_count($code);
			$data['coupon_usage'] = 'decrease';
		}
		
		return $wpdb->update(INVOICE_TABLE, $data, array('id' => $invoice_id));
	}
}

function geodir_payment_increase_coupon_usage_count($code) {
	global $wpdb;
	
	$coupon = geodir_payment_coupon_info_by_code($code);
	
	if (empty($coupon)) {
		return false;
	}
	
	$usage_count = $coupon->usage_count > 0 ? $coupon->usage_count : 0;
	$usage_count++;
	
	if ( false === $wpdb->update(COUPON_TABLE, array('usage_count' => $usage_count), array('cid' => $coupon->cid))) {
		return false;
	}
	
	return true;
}

function geodir_payment_decrease_coupon_usage_count($code) {
	global $wpdb;
	
	$coupon = geodir_payment_coupon_info_by_code($code);
	
	if (empty($coupon)) {
		return false;
	}
	
	$usage_count = $coupon->usage_count > 0 ? $coupon->usage_count : 0;
	
	if ($usage_count == 0) {
		return true;
	}
	
	$usage_count--;
	
	if ( false === $wpdb->update(COUPON_TABLE, array('usage_count' => $usage_count), array('cid' => $coupon->cid))) {
		return false;
	}
	
	return true;
}

function geodir_payment_coupon_usage_count_left($code, $bool = true) {
	$left = 0;
	$coupon = geodir_payment_coupon_info_by_code($code);
	
	if (!empty($coupon)) {
		$usage_limit = $coupon->usage_limit;
		$usage_count = $coupon->usage_count;
		
		if ($usage_limit > 0) {
			$left = $usage_limit;
			
			if ($usage_count > 0) {
				$left = $usage_limit > $usage_count ? $usage_limit - $usage_count : 0;
			}
		} else if ($usage_limit === '' && !strlen($usage_limit) > 0) {
			$left = 'n'; // unlimited
		} else {
			$left = 0;
		}
	}
	if ($bool) {
		$left = ($left === 'n') || ($left !== 'n' && $left > 0) ? true : false;
	}
	return $left;
}

/**
 * Handle the plugin settings for plugin deactivate to activate.
 *
 * It manages the the settings without loosing previous settings saved when plugin
 * status changed from deactivate to activate.
 *
 * @since 1.3.6
 * @package GeoDirectory_Payment_Manager
 *
 * @param array $settings The option settings array.
 * @return array The settings array.
 */
function geodir_payment_resave_settings($settings = array()) {
	if (!empty($settings) && is_array($settings)) {
		$c = 0;
		
		foreach ($settings as $setting) {
			if (!empty($setting['id']) && false !== ($value = get_option($setting['id']))) {
				$settings[$c]['std'] = $value;
			}
			$c++;
		}
	}
	
	return $settings;
}

/**
 * Get the customized invoice id to display.
 *
 * @since 1.3.6
 *
 * @param int $invoice_id The invoice id.
 * @return int|string Custom invoice id.
 */
function geodir_payment_invoice_id_formatted($invoice_id) {
	$threshold = absint(get_option('geodir_payment_invoice_threshold'));
	$threshold = $threshold > 20 ? 20 : $threshold;
	
	$prefix = get_option('geodir_payment_invoice_prefix');
	
	$custom_invoice_id = substr($prefix, 0, 10) . zeroise($invoice_id, $threshold);
	/**
	 * Filter the invoice id to display customized invoice id.
	 *
	 * @since 1.3.6
	 *
	 * @param string $custom_invoice_id The custom invoice id.
	 * @param int $invoice_id The invoice id.
	 */
	 $custom_invoice_id = apply_filters('geodir_payment_invoice_id_formatted', $custom_invoice_id, $invoice_id);
	
	return $custom_invoice_id;
}

/**
 * Adds payment settings options that requires to add for translation.
 *
 * @package GeoDirectory_Payment_Manager
 * @since 1.3.6
 *
 * @param array $gd_options GeoDirectory setting option names.
 * @param array Modified option names.
 */
function geodir_payment_settings_for_translation($gd_options = array()) {
	// Notifications settings
	$notifications = geodir_payment_notifications();
	
	if (!empty($notifications)) {
		foreach ($notifications as $setting) {
			if (isset($setting['type']) && in_array($setting['type'], array('text', 'textarea')) && isset($setting['id']) && $setting['id'] != '') {
				$gd_options[] = $setting['id'];
			}
		}
	}
	
	return $gd_options;
}

/**
 * Get price description text for translation.
 *
 * @since 1.3.6
 *
 * @global object $wpdb WordPress database abstraction object.
 *
 * @param  array $translation_texts Array of text strings.
 * @return array
 */
function geodir_payment_db_text_translation( $translation_texts = array() ) {
	global $wpdb;
	
	$translation_texts = !empty( $translation_texts ) && is_array( $translation_texts ) ? $translation_texts : array();
	$payment_texts = array();
	
	// geodir_price
	$query = "SELECT title, title_desc FROM `" . GEODIR_PRICE_TABLE . "`";
	$rows = $wpdb->get_results($query);
	
	if ( !empty( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( $row->title != '' ) {
				$payment_texts[] = __(stripslashes_deep($row->title), 'geodirectory');
			}
			
			if ( $row->title_desc != '' ) {
				$payment_texts[] = __(stripslashes_deep($row->title_desc), 'geodirectory');
			}
		}
	}
	
	if ( !empty( $payment_texts ) ) {
		$payment_texts = !empty( $payment_texts ) ? array_unique( $payment_texts ) : $payment_texts;
		$translation_texts = array_merge( $translation_texts, $payment_texts );
	}
		
	return $translation_texts;
}

function geodir_payment_has_renew_period($post_id) {
	$return = false;
	
	$expire_date = geodir_get_post_meta($post_id, 'expire_date', true);
	if ($expire_date == '' || $expire_date == '0000-00-00' || geodir_strtolower($expire_date) == 'never') {
		return $return;
	}
	
	$expire_time = strtotime($expire_date);
	$enable_preexpiry_notice = geodir_payment_preexpiry_notice_is_active();
	$notice_days = geodir_payment_preexpiry_notice_days();
	
	if ($enable_preexpiry_notice && $notice_days > 0 && $expire_time > strtotime('01-01-1970')) {
		$preexpiry_date = $expire_time - (DAY_IN_SECONDS * $notice_days * 2); // show renew link before pre expiry days * 2
		
		if($preexpiry_date <= strtotime(date_i18n('Y-m-d'))) {
			$return = true;
		}
	}
	
	return $return;
}

function geodir_payment_recurring_pay_desc($unit, $value, $period = '', $suffix = '') {
    $value = (int)$value > 0 ? (int)$value : 1;
    $period = (int)$period > 0 ? (int)$period : '';
    
    $pay_desc = '';
    if ($unit == 'D') {
        if ((int)$period > 0) {
            if ($value > 1) {
                if ($period > 1) {
                    $pay_desc = wp_sprintf(__('for each %d days, for %d installments', 'geodir_payments'), $value, $period);
                } else {
                    $pay_desc = wp_sprintf(__('for %d days', 'geodir_payments'), $value);
                }
            } else {
                $pay_desc = wp_sprintf(_n('for one day', 'for each day, for %d installments', $period, 'geodir_payments'), $period);
            }
        } else {
            $pay_desc = wp_sprintf(_n('for each day', 'for each %d days', $value, 'geodir_payments'), $value);
        }
    } else if ($unit == 'W') {
        if ((int)$period > 0) {
            if ($value > 1) {
                if ($period > 1) {
                    $pay_desc = wp_sprintf(__('for each %d weeks, for %d installments', 'geodir_payments'), $value, $period);
                } else {
                    $pay_desc = wp_sprintf(__('for %d weeks', 'geodir_payments'), $value);
                }
            } else {
                $pay_desc = wp_sprintf(_n('for one week', 'for each week, for %d installments', $period, 'geodir_payments'), $period);
            }
        } else {
            $pay_desc = wp_sprintf(_n('for each week', 'for each %d weeks', $value, 'geodir_payments'), $value);
        }
    } else if ($unit == 'M') {
        if ((int)$period > 0) {
            if ($value > 1) {
                if ($period > 1) {
                    $pay_desc = wp_sprintf(__('for each %d months, for %d installments', 'geodir_payments'), $value, $period);
                } else {
                    $pay_desc = wp_sprintf(__('for %d months', 'geodir_payments'), $value);
                }
            } else {
                $pay_desc = wp_sprintf(_n('for one month', 'for each month, for %d installments', $period, 'geodir_payments'), $period);
            }
        } else {
            $pay_desc = wp_sprintf(_n('for each month', 'for each %d months', $value, 'geodir_payments'), $value);
        }
    } else if ($unit == 'Y') {
        if ((int)$period > 0) {
            if ($value > 1) {
                if ($period > 1) {
                    $pay_desc = wp_sprintf(__('for each %d years, for %d installments', 'geodir_payments'), $value, $period);
                } else {
                    $pay_desc = wp_sprintf(__('for %d years', 'geodir_payments'), $value);
                }
            } else {
                $pay_desc = wp_sprintf(_n('for one year', 'for each year, for %d installments', $period, 'geodir_payments'), $period);
            }
        } else {
            $pay_desc = wp_sprintf(_n('for each year', 'for each %d years', $value, 'geodir_payments'), $value);
        }
    }
    
    if ($suffix != '' && $pay_desc != '') {
        $pay_desc = $suffix . ' ' . $pay_desc;
    }
    
    $pay_desc = apply_filters('geodir_payment_recurring_pay_desc', $pay_desc, $unit, $value, $period, $suffix);

    return $pay_desc;
}

function geodir_payment_checkout_free_trial_desc($value, $unit) {
    $trial_desc = '';
    
    if (!absint($value) > 0) {
        return $trial_desc;
    }
    $value = absint($value);
    
    if ($unit == 'D') {
        $trial_desc = wp_sprintf(_n('Free trial for one day', 'Free trial for the first %d days', $value, 'geodir_payments'), $value);
    } else if ($unit == 'W') {
        $trial_desc = wp_sprintf(_n('Free trial for the first one week', 'Free trial for the first %d weeks', $value, 'geodir_payments'), $value);
    } else if ($unit == 'M') {
        $trial_desc = wp_sprintf(_n('Free trial for the first one month', 'Free trial for the first %d months', $value, 'geodir_payments'), $value);
    } else if ($unit == 'Y') {
        $trial_desc = wp_sprintf(_n('Free trial for the first one year', 'Free trial for the first %d years', $value, 'geodir_payments'), $value);
    }
    
    $trial_desc = apply_filters('geodir_payment_checkout_free_trial_desc', $trial_desc, $value, $unit);

    return $trial_desc;
}

function geodir_payment_preexpiry_notice_is_active() {    
    global $preexpiry_notice_is_active;
    
    if ( !$preexpiry_notice_is_active ) {
        $preexpiry_notice_is_active = (bool)get_option('geodir_listing_preexpiry_notice_disable');
    }
    
    return apply_filters('geodir_payment_preexpiry_notice_is_active', $preexpiry_notice_is_active);
}

function geodir_payment_preexpiry_notice_days_max() {
	global $preexpiry_notice_days_max;

	if ( !$preexpiry_notice_days_max ) {
		$notice_days_1 = (int)get_option('geodir_listing_preexpiry_notice_days');
		$notice_days_2 = (int)get_option('geodir_listing_preexpiry_notice_days2');
		$notice_days_3 = (int)get_option('geodir_listing_preexpiry_notice_days3');

		$notice_days_1 = $notice_days_1 > 0 ? $notice_days_1 : 0;
		$notice_days_2 = $notice_days_2 > 0 ? $notice_days_2 : 0;
		$notice_days_3 = $notice_days_3 > 0 ? $notice_days_3 : 0;

		$preexpiry_notice_days_max = max($notice_days_1, $notice_days_2, $notice_days_3);


		if ($preexpiry_notice_days_max == 0) {
			$preexpiry_notice_days_max = 1;
		}
	}

	return apply_filters('geodir_payment_preexpiry_notice_days_max', $preexpiry_notice_days_max);
}

function geodir_payment_preexpiry_notice_days() {
    global $preexpiry_notice_days;
    
    if ( !$preexpiry_notice_days ) {
        $notice_days_1 = (int)get_option('geodir_listing_preexpiry_notice_days');
        $notice_days_2 = (int)get_option('geodir_listing_preexpiry_notice_days2');
        $notice_days_3 = (int)get_option('geodir_listing_preexpiry_notice_days3');
        
        $notice_days_1 = $notice_days_1 > 0 ? $notice_days_1 : 0;
        $notice_days_2 = $notice_days_2 > 0 ? $notice_days_2 : 0;
        $notice_days_3 = $notice_days_3 > 0 ? $notice_days_3 : 0;

	    $preexpiry_notice_days = min($notice_days_1, $notice_days_2, $notice_days_3);

        if ($preexpiry_notice_days == 0) {
            $preexpiry_notice_days = 1;
        }
    }
    
    return apply_filters('geodir_payment_preexpiry_notice_days', $preexpiry_notice_days);
}

function geodir_payment_preexpiry_grace_data() {
    $periods = array();
    if ($notice_days_1 = (int)get_option('geodir_listing_preexpiry_notice_days')) {
        $periods['expire_notification'] = array('days' => $notice_days_1, 'suffix' => '');
    }
    if ($notice_days_2 = (int)get_option('geodir_listing_preexpiry_notice_days2')) {
        $periods['exp2'] = array('days' => $notice_days_2, 'suffix' => '2');
    }
    if ($notice_days_3 = (int)get_option('geodir_listing_preexpiry_notice_days3')) {
        $periods['exp3'] = array('days' => $notice_days_3, 'suffix' => '3');
    }
    if (empty($periods)) {
        $periods['expire_notification'] = array('days' => 1, 'suffix' => '');
    }
    
    return $periods;
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.4.4
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_payment_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}

/**
 * Display notice if Invoicing plugin is not active.
 *
 * @since 2.0.0
 */
function geodir_payment_invoicing_notice() {
    if ( !is_plugin_active( 'invoicing/invoicing.php' ) ) {
        echo '<div class="error is-dismissible" style="margin:12px 0"><p><strong>' . sprintf( __( 'Payment Manager v2+, requires you install %sInvoicing%s plugin for the Payment Manager plugin to work.', 'geodir_payments' ), '<a href="https://wpinvoicing.com/" target="_blank" title="Invoicing">', '</a>' ) . '</strong></p></div>';
    }
}

/**
 * Get the custom fields name for input package id.
 *
 * @since 2.0.0
 * 
 * @global object $wpdb WordPress Database object.
 * @global array $geodir_post_custom_fields_cache Cached listing custom fields.
 *
 * @param int $package_id The listing package ID.
 * @param string $post_type The listing type.
 * @return array Custom fields name.
 */
function geodir_payment_package_fields( $package_id, $post_type ) {
    global $wpdb, $geodir_post_custom_fields_cache;

    $cache_stored = 'package_fields_' . $package_id . '_' . $post_type;

    if ( !empty( $geodir_post_custom_fields_cache ) && array_key_exists( $cache_stored, $geodir_post_custom_fields_cache ) ) {
        return $geodir_post_custom_fields_cache[$cache_stored];
    }
     
    $sql = $wpdb->prepare("SELECT htmlvar_name FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type=%s AND FIND_IN_SET( %s, packages ) AND field_type != 'fieldset' AND htmlvar_name != '' ORDER BY id ASC", array( $post_type, (int)$package_id ) );
    $rows = $wpdb->get_col( $sql );

    if ( empty( $geodir_post_custom_fields_cache ) ) {
        $geodir_post_custom_fields_cache = array();
    }

    $geodir_post_custom_fields_cache[$cache_stored] = $rows;
     
    return $rows;
}

/**
 * Set the listing status on payment received for the invoice.
 *
 * @since 2.0.3
 * 
 * @return string The listing status.
 */
function geodir_payment_paid_listing_status() {
    $status = get_option( 'geodir_paid_listing_status' );

    if ( empty( $status ) ) {
        $status = 'publish';
    }

    return apply_filters( 'geodir_payment_paid_listing_status', $status );
}

/**
 * Get the package alive days.
 *
 * @since 2.0.32
 * 
 * @param int $package_id The package ID.
 * @param bool $skip_trial True to do not count trial days.
 * @return int Package alive days.
 */
function geodir_payment_package_alive_days( $package_id, $skip_trial = false ) {
    $package_info = geodir_get_package_info( $package_id );
    
    if ( empty( $package_info ) ) {
        return false;
    }

    $alive_days = $package_info->days;

    if ( !empty( $package_info->sub_active ) ) {
        if ( !$skip_trial && !empty( $package_info->sub_num_trial_days ) ) {
            $alive_days = geodir_payment_get_units_to_days( (int)$package_info->sub_num_trial_days, $package_info->sub_num_trial_units );
        } else {
            $alive_days = geodir_payment_get_units_to_days( (int)$package_info->sub_units_num, $package_info->sub_units );
        }
    }

    return $alive_days;
}