<?php
/**
 * Contains functions related to Franchise Manager plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Franchise_Manager
 */
 
// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
	
/**
 * Plugin activation hook.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 * @since 1.0.2 Don't loose previously saved settings when plugin is reactivated.
 */
function geodir_franchise_activation() {
	if ( get_option( 'geodir_installed' ) ) {
		$options = geodir_resave_settings(geodir_franchise_general_settings());
		geodir_update_options($options, true);
		
		$options = geodir_resave_settings(geodir_franchise_notifications());
		geodir_update_options($options, true);
		
		add_option( 'geodir_franchise_activation_redirect', 1 );
	}
}

/**
 * Plugin deactivation hook.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 */
function geodir_franchise_deactivation() {
}

/**
 * Plugin activation redirect.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 */
function geodir_franchise_activation_redirect() {
	if ( get_option( 'geodir_franchise_activation_redirect', false ) ) {
		delete_option( 'geodir_franchise_activation_redirect' );
		
		wp_redirect( admin_url( 'admin.php?page=geodirectory&tab=franchise&subtab=general' ) ); 
	}
}

/**
 * Check GeoDirectory plugin installed.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 */
function geodir_franchise_plugin_activated( $plugin ) {
	if ( !get_option( 'geodir_installed' ) )  {
		$file = plugin_basename( __FILE__ );
		
		if ( $file == $plugin ) {
			$all_active_plugins = get_option( 'active_plugins', array() );
			
			if ( !empty( $all_active_plugins ) && is_array( $all_active_plugins ) ) {
				foreach ( $all_active_plugins as $key => $plugin ) {
					if ( $plugin == $file ) {
						unset( $all_active_plugins[$key] );
					}
				}
			}
			update_option( 'active_plugins', $all_active_plugins );
		}
		
		wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir-franchise' ) );
	}
}

/**
 * Load geodirectory franchise plugin textdomain.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 */
function geodir_franchise_load_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'geodir-franchise' );
	
	load_textdomain( 'geodir-franchise', WP_LANG_DIR . '/geodir-franchise/geodir-franchise-' . $locale . '.mo' );
	load_plugin_textdomain( 'geodir-franchise', false, dirname( plugin_basename( __FILE__ ) ) . '/gdfranchise-languages' );
	
	/**
	 * Define language constants.
	 */
	require_once( GEODIR_FRANCHISE_PLUGIN_PATH . '/language.php' );
}

/**
 * Instantiate & handles the franchise request actions.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 */
function geodir_franchise_init() {
	$request = isset($_REQUEST) ? $_REQUEST : array();
	
	$geodir_ajax = isset($request['geodir_ajax']) ? $request['geodir_ajax'] : '';
	$ajax_action = isset($request['ajax_action']) ? $request['ajax_action'] : '';
	
	if ($geodir_ajax == 'add_franchise') {
		switch ($ajax_action) {
			case 'checkout_now': {
				$franchise_id = isset($request['franchise_id']) ? $request['franchise_id'] : '';
				$wpnonce = isset($request['_wpnonce']) ? $request['_wpnonce'] : '';
			
				if ( wp_verify_nonce( $wpnonce, 'geodir_franchise_nonce_' . $franchise_id ) && !empty( $franchise_id ) && geodir_franchise_check( $franchise_id ) ) {
					$franchise_cost_data = geodir_franchise_get_franchises_cost( $franchise_id );
					
					if (!empty($franchise_cost_data) && $franchise_cost_data['amount'] > 0) {
						$post_id = $franchise_id;
						if ($franchise_cost_data['count'] > 1) {
							$invoice_title = wp_sprintf(  __( 'Add %d franchises of "%s"', 'geodir-franchise' ), $franchise_cost_data['count'] , get_the_title( $post_id ) );
						} else {
							$invoice_title = wp_sprintf(  __( 'Add franchise of "%s"', 'geodir-franchise' ), get_the_title( $post_id ) );
						}
						$invoice_type = 'add_franchise';
						$invoice_callback = 'add_franchise';
						
						$package_id = geodir_get_post_meta($post_id, 'package_id', true);
						$package_info = geodir_get_package_info($package_id);
						$package_title = !empty($package_info) ? $package_info->title : '';
						$alive_days = geodir_franchise_package_alive_days($package_id);						
						$expire_date = $alive_days > 0 ? date_i18n('Y-m-d', strtotime(date_i18n('Y-m-d') . ' + ' . (int)$alive_days . ' days')) : '';
						
						$amount = $franchise_cost_data['amount'];
						$tax_amount = geodir_payment_get_tax_amount( $amount, $package_id, $post_id );
						$amount = geodir_payment_price( $amount, false );
						$payable_amount = ( $amount + $tax_amount );
						$payable_amount = $payable_amount > 0 ? $payable_amount : 0;
						$payment_status = $payable_amount > 0 ? 'pending' : 'confirmed';
						
						$invoice_data = array();
						$invoice_data['franchise_id'] = $franchise_id;
						$invoice_data['franchises'] = $franchise_cost_data['franchises'];
						
						$data = array();
						$data['type'] = $amount > 0 ? 'paid' : 'free';
						$data['post_id'] = $post_id;
						$data['post_title'] = $invoice_title;
						$data['post_action'] = 'add_franchise';
						$data['invoice_type'] = $invoice_type;
						$data['invoice_callback'] = $invoice_callback;
						$data['invoice_data'] = maybe_serialize( $invoice_data );
						$data['package_id'] = $package_id;
						$data['package_title'] = $package_title;
						$data['amount'] = $payable_amount;
						$data['alive_days'] = $alive_days;
						$data['expire_date'] = $expire_date;
						$data['tax_amount'] = $tax_amount;
						$data['paied_amount'] = $payable_amount;
						$data['status'] = $payment_status;
						
						$current_post_status = get_post_status($post_id);

						$invoice_id = geodir_create_invoice( $data );
						
						if ( $invoice_id ) {
							if ($current_post_status != '' && get_post_status($post_id) != $current_post_status) {
								geodir_set_post_status($post_id, $current_post_status);
							}
							
							if ( $payment_status == 'confirmed' ) {
								geodir_update_invoice_status( $invoice_id, $payment_status );
							}
							
							/**
							 * Called before redirect to the payment checkout page.
							 *
							 * @since 1.0.0
							 *
							 * @param int $invoice_id Current payment invoice/cart id.
							 */
							do_action( 'geodir_payment_checkout_redirect', $invoice_id );
						}
					}
				}
			}
			break;
		}
		
		wp_redirect( trailingslashit(home_url()) );
	}
}

/**
 * Add the invoice type for franchise payments to payment invoice types.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 * 
 * @param array $invoice_types The payment invoice types array.
 * @return array Payment invoice types.
 */
function geodir_franchise_payment_invoice_types($invoice_types = array()) {
	$invoice_types['add_franchise'] = __( 'Add Franchise', 'geodir-franchise' );
	
	return $invoice_types;
}

/**
 * Get the package alive days for current package id.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $package_id The price package id.
 * @return int Package alive days.
 */
function geodir_franchise_package_alive_days($package_id) {
	$alive_days = 0;
	
	if ( !geodir_franchise_is_payment_active() ) {
		return $alive_days;
	}
	
	$package_info = geodir_get_package_info($package_id);
	
	if (!empty($package_info)) {
		$alive_days = $package_info->days;
		
		if ( $package_info->sub_active ) {
			$sub_units_num_var = $package_info->sub_units_num;
			$sub_units_var = $package_info->sub_units;
			$alive_days = geodir_payment_get_units_to_days( $sub_units_num_var, $sub_units_var );
			
			$sub_num_trial_days_var = $package_info->sub_num_trial_days;
			$sub_num_trial_units = isset( $package_info->sub_num_trial_units ) ? $package_info->sub_num_trial_units : 'D';
			$sub_num_trial_days_var = geodir_payment_get_units_to_days( $sub_num_trial_days_var, $sub_num_trial_units );
			
			if ( $package_info->sub_num_trial_days > 0 ) {
				$alive_days = $sub_num_trial_days_var;
			}
		}
	}
	
	return $alive_days;
}

/**
 * Outputs translated JS text strings.
 *
 * This function outputs text strings used in JS files as a json array of strings so they can be translated 
 * and still be used in JS files.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 */
function geodir_franchise_localize_all_js_msg() {
	$js_msg = array();
	$js_msg['admin_url'] = admin_url( 'admin.php' );
	$js_msg['ajax_url'] = admin_url( 'admin-ajax.php?action=geodir_franchise_ajax' );
	$js_msg['btn_text_save'] = __( 'Save', 'geodir-franchise' );
	$js_msg['btn_text_saving'] = __( 'Saving...', 'geodir-franchise' );
	$js_msg['btn_pay_for_franchises'] = esc_attr( __( 'Pay For Franchises', 'geodir-franchise' ) );
	$js_msg['btn_pay_are_you_sure'] = esc_attr( __( 'Are you sure?', 'geodir-franchise' ) );
	$js_msg['btn_card_holder_empty'] = esc_attr( __( 'Please enter card holder name!', 'geodir-franchise' ) );
	$js_msg['btn_card_number_empty'] = esc_attr( __( 'Please enter card number!', 'geodir-franchise' ) );
	$js_msg['btn_card_date_empty'] = esc_attr( __( 'Please enter valid card expire date!', 'geodir-franchise' ) );
	$js_msg['txt_processing'] = __( 'Processing...', 'geodir-franchise' );
	$js_msg['txt_redirect_checkout'] = __( 'Redirecting to checkout...', 'geodir-franchise' );
	$js_msg['txt_save_error'] = __( 'Error: fail to save data, please check your data and try again!', 'geodir-franchise' );
	$js_msg['btn_duplicate_image'] = esc_attr( __( 'Duplicate images from Main Listing', 'geodir-franchise' ) );
	$js_msg['btn_duplicating_image'] = esc_attr( __( 'Duplicating images from Main Listing...', 'geodir-franchise' ) );
	$js_msg['err_duplicate_image'] = esc_attr( __( 'No images duplicated from main listing!', 'geodir-franchise' ) );
	
	if (geodir_franchise_is_payment_active()) {
		$page_id = geodir_payment_checkout_page_id();
		$redirect_url = geodir_getlink(get_permalink($page_id));
		$js_msg['checkout_link'] = $redirect_url;
	}
	
	foreach ( $js_msg as $key => $value ) {
		if ( !is_scalar( $value ) ) {
			continue;
		}
		
		$js_msg[$key] = html_entity_decode( (string)$value, ENT_QUOTES, 'UTF-8' );
	}
	
	echo '<script type="text/javascript">var gdFranchise = ' . json_encode( $js_msg ) . ';</script>';
}

/**
 * Get current subtab name.
 *
 * @since 1.0.0
 * @package GeoDirectory_Franchise_Manager
 *
 * @param string $default The default value to return when subtab empty.
 * @return string Sub tab name.
 */
function geodir_franchise_current_subtab( $default = 'general' ) {
	$subtab = isset( $_REQUEST['subtab'] ) ? sanitize_text_field($_REQUEST['subtab']) : '';
	
	if ( $subtab=='' && $default != '' ) {
		$subtab = $default;
	}
	
	return $subtab;
}

/**
 * Add the tab in left sidebar menu for franchise settings page.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * $param array $tabs GeoDirectory settings tabs array.
 * @return array Array of tab data.
 */
function geodir_franchise_settings_tab( $tabs ) {
	$franchise_tabs = array();
	$franchise_tabs['label'] = __('Franchise Settings', 'geodir-franchise');
	$franchise_tabs['subtabs'] = array(
										array(
											'subtab' => 'general',
											'label' => __( 'General Settings', 'geodir-franchise' ),
											'form_action' => admin_url( 'admin-ajax.php?action=geodir_franchise_ajax&tab=franchise' )
										),
										array(
											'subtab' => 'notifications',
											'label' => __( 'Notifications', 'geodir-franchise' ),
											'form_action' => admin_url( 'admin-ajax.php?action=geodir_franchise_ajax&tab=franchise' )
										)
									);
	/**
	 * Filter the franchise settings tabs in backend settings.
	 *
	 * @since 1.0.0
	 * @param array $franchise_tabs The array of tabs to display.
	 */
	$franchise_tabs = apply_filters( 'geodir_franchise_settings_tabs', $franchise_tabs );
	
	$tabs['franchise'] = $franchise_tabs;
    return $tabs;
}

/**
 * Adds franchise settings tab content.
 *
 * @since 1.0.0
 * @package GeoDirectory_Franchise_Manager
 */
function geodir_franchise_admin_tab_content() {
	if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'franchise' ) {
		add_action( 'geodir_admin_option_form', 'geodir_franchise_admin_option_form' );
	}
}

/**
 * The franchise settings form.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param string $current_tab Current Tab name.
 */
function geodir_franchise_admin_option_form ( $current_tab ) {
	$current_tab = geodir_franchise_current_subtab();
	
	geodir_franchise_get_admin_option_form( $current_tab );
}

/**
 * Outputs franchise settings forms.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
*
 * @param string $tab_name Tab name.
 */
function geodir_franchise_get_admin_option_form( $tab_name ) {
	switch ( $tab_name ) {
		case 'general': {
			geodir_admin_fields( geodir_franchise_general_settings() );
			?>
<p class="submit">
  <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodir-franchise' ); ?>" />
  <input type="hidden" name="subtab" value="general" id="last_tab" />
</p>
</div>
		<?php
		}
		break;
		case 'notifications': {
			geodir_admin_fields( geodir_franchise_notifications() );
			?>
<p class="submit">
  <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodir-franchise' ); ?>" />
  <input type="hidden" name="subtab" value="notifications" id="last_tab" />
</p>
</div>
		<?php
		}
		break;
	}// end of switch
}

/**
 * Get the franchise general settings options.
 *
 * @since 1.0.0
 * @since 1.0.3 Settings added to hide main/current viewing listing from tab.
 * @package GeoDirectory_Franchise_Manager
 *
 * @param array $options Franchise general settings options.
 * @return array Modified general settings options.
 */
function geodir_franchise_general_settings( $options = array() ) {
	$post_types = geodir_get_posttypes( 'object' );
	
	$post_type_options = array();
	
	foreach ( $post_types as $key => $post_types_obj ) {
		$post_type_options[$key] = $post_types_obj->labels->singular_name;
	}
	
	$options[] = array('name' => __( 'General Settings', 'geodir-franchise' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'gd_franchise_general' );
	
	$options[] = array('name' => __( 'Enable Franchise', 'geodir-franchise' ), 'type' => 'sectionstart', 'id' => 'gd_franchise_general_enable' );
	
	$options[] = array(
						'name' => __( 'Select post type to enable franchise feature', 'geodir-franchise' ),
						'desc' => __( 'Select the post types that requires franchise feature.', 'geodir-franchise' ),
						'tip' => '',
						'id' => 'geodir_franchise_posttypes',
						'css' => 'min-width:300px;',
						'std' => array(),
						'type' => 'multiselect',
						'placeholder_text' => __( 'Select post types', 'geodir-franchise' ),
						'class' => 'chosen_select',
						'options' => $post_type_options
					);
	$options[] = array( 'type' => 'sectionend', 'id' => 'gd_franchise_general_enable' );
	$options[] = array('name' => __( 'Detail Page Franchises Tab Settings', 'geodir-franchise' ), 'type' => 'sectionstart', 'id' => 'gd_franchise_general_tab' );
	
	$options[] = array(  
						'name' => __( 'Display Franchises Tab:', 'geodir-franchise' ),
						'desc' => __( 'Enable franchises tab on listing detail page.', 'geodir-franchise' ),
						'id' => 'geodir_franchise_post_enable_tab',
						'type' => 'checkbox',
						'std' => '1',
					);
	$options[] = array(  
						'name' => __( 'Display Link To All Franchises:', 'geodir-franchise' ),
						'desc' => __( 'Enable link to view all franchises under franchises tab on listing detail page.', 'geodir-franchise' ),
						'id' => 'geodir_franchise_post_enable_link',
						'type' => 'checkbox',
						'std' => '1'
					);
					
	$options[] = array(
					'name' => __('Layout', 'geodirectory'),
					'desc' => __('Set the listing view of franchise post on detail page', 'geodir-franchise'),
					'id' => 'geodir_franchise_post_listing_view',
					'css' => 'min-width:300px;',
					'std' => 'gridview_onehalf',
					'type' => 'select',
					'class' => 'chosen_select',
					'options' => array_unique(array(
						'gridview_onehalf' => __('Grid View (Two Columns)', 'geodirectory'),
						'gridview_onethird' => __('Grid View (Three Columns)', 'geodirectory'),
						'gridview_onefourth' => __('Grid View (Four Columns)', 'geodirectory'),
						'gridview_onefifth' => __('Grid View (Five Columns)', 'geodirectory'),
						'listview' => __('List view', 'geodirectory'),
					))
				);

    $options[] = array(
					'name' => __('Sort by', 'geodirectory'),
					'desc' => __('Set the franchise post listing sort by view', 'geodir-franchise'),
					'id' => 'geodir_franchise_post_sortby',
					'css' => 'min-width:300px;',
					'std' => 'latest',
					'type' => 'select',
					'class' => 'chosen_select',
					'options' => array_unique(array( 
						'az' => __( 'A-Z', 'geodirectory' ),
						'latest' => __( 'Latest', 'geodirectory' ),
						'featured' => __( 'Featured', 'geodirectory' ),
						'high_review' => __( 'Review', 'geodirectory' ),
						'high_rating' => __( 'Rating', 'geodirectory' ),
						'random' => __( 'Random', 'geodirectory' ),
					))
				);

    $options[] = array(
					'name' => __('Number of posts:', 'geodirectory'),
					'desc' => __('Enter number of posts to display on franchise posts listing', 'geodir-franchise'),
					'id' => 'geodir_franchise_post_count',
					'type' => 'text',
					'css' => 'min-width:300px;',
					'std' => '5'
				);

    $options[] = array(
					'name' => __('Post excerpt', 'geodirectory'),
					'desc' => __('Post content excerpt character count', 'geodirectory'),
					'id' => 'geodir_franchise_post_excerpt',
					'type' => 'text',
					'css' => 'min-width:300px;',
					'std' => '20'
				);
	
	// Add location filter if location manager available.		
	if ( geodir_franchise_is_location_active() ) {
		$options[] = array(  
						'name' => __( 'Enable Location Filter:', 'geodir-franchise' ),
						'desc' => __( 'Enable location filter on franchise post.', 'geodir-franchise' ),
						'id' => 'geodir_franchise_post_location_filter',
						'type' => 'checkbox',
						'std' => '1'
					);
	}
	$options[] = array(  
		'name' => __( 'Hide viewing franchise:', 'geodir-franchise' ),
		'desc' => __( 'Do not show current viewing franchise in franchises tab.', 'geodir-franchise' ),
		'id' => 'geodir_franchise_hide_viewing',
		'type' => 'checkbox',
		'std' => ''
	);
	$options[] = array(  
		'name' => __( 'Show main listing in MAIN listing franchises tab:', 'geodir-franchise' ),
		'desc' => __( 'Show main listing in franchises tab on MAIN listing detail page.', 'geodir-franchise' ),
		'id' => 'geodir_franchise_show_main',
		'type' => 'checkbox',
		'std' => ''
	);
	$options[] = array(  
		'name' => __( 'Hide main listing from FRANCHISE listing franchises tab:', 'geodir-franchise' ),
		'desc' => __( 'Do not show main listing in franchises tab on FRANCHISES listing detail page.', 'geodir-franchise' ),
		'id' => 'geodir_franchise_hide_main',
		'type' => 'checkbox',
		'std' => '1'
	);
	$options[] = array( 'type' => 'sectionend', 'id' => 'gd_franchise_general_tab' );
	
	$options[] = array('name' => __( 'Detail Page Sidebar Settings', 'geodir-franchise' ), 'type' => 'sectionstart', 'id' => 'gd_franchise_detail_sidebar' );	
	$options[] = array(  
						'name' => __( 'Display All Franchises Link:', 'geodir-franchise' ),
						'desc' => __( 'Display "All Franchises" link on sidebar of listing detail page.', 'geodir-franchise' ),
						'id' => 'geodir_franchise_show_franchises_link',
						'type' => 'checkbox',
						'std' => '',
					);
	$options[] = array(  
						'name' => __( 'Display Main Listing Link:', 'geodir-franchise' ),
						'desc' => __( 'Display "Main Listing For Franchise" link on sidebar of franchise listing detail page.', 'geodir-franchise' ),
						'id' => 'geodir_franchise_show_parent_link',
						'type' => 'checkbox',
						'std' => ''
					);
	$options[] = array(  
		'name' => __( 'Hide main listing from All Franchises view page:', 'geodir-franchise' ),
		'desc' => __( 'Do not show main listing on All Franchises view page.', 'geodir-franchise' ),
		'id' => 'geodir_franchise_hide_main_all',
		'type' => 'checkbox',
		'std' => ''
	);
	$options[] = array( 'type' => 'sectionend', 'id' => 'gd_franchise_detail_sidebar' );
	
	/**
	 * Filters the franchise general settings options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options Franchise general settings options array.
	 */
	$options = apply_filters( 'geodir_franchise_general_settings', $options );
	
	return $options;
}

/**
 * Get the franchise notification settings options.
 *
 * @since 1.0.0
 * @package GeoDirectory_Franchise_Manager
 *
 * @param array $options Franchise notification settings options.
 * @return array Modified notification settings options.
 */
function geodir_franchise_notifications( $options = array() ) {		
	$options[] = array('name' => __( 'Notifications', 'geodir-franchise' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'gd_franchise_notifications' );
	
	$options[] = array('name' => __( 'Notifications Settings', 'geodir-franchise' ), 'type' => 'sectionstart', 'id' => 'gd_franchise_notifications_settings' );
					
	$options[] = array(  
				'name' => __( 'BCC option for franchise listings approved:', 'geodir-franchise' ),
				'desc' => __( 'Enable bcc option for notifications on franchise listings approved.', 'geodir-franchise' ),
				'id' => 'geodir_franchise_bcc_admin_payment_franchises',
				'css' => 'min-width:300px;',
				'std' => '0',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array_unique( array( 
					'1' => __( 'Yes', 'geodir-franchise' ),
					'0' => __( 'No', 'geodir-franchise' ),
					))
			);
	$options[] = array( 'type' => 'sectionend', 'id' => 'gd_franchise_notifications_settings' );
	$options[] = array('name' => __( 'Franchise Notifications', 'geodir-franchise' ), 'type' => 'sectionstart', 'id' => 'gd_franchise_notifications_emails' );
	$options[] = array(  
		'name' => __( 'Notify to client for franchise listings approved:', 'geodir-franchise' ),
		'desc' => '',
		'id' => 'geodir_franchise_client_email_subject_payment_franchises',
		'type' => 'text',
		'css' => 'min-width:300px;',
		'std' => __('Your franchise listings of "[#listing_title#]" approved', 'geodir-franchise')
		);
	$options[] = array(  
		'name' => '',
		'desc' => '',
		'id' => 'geodir_franchise_client_email_message_payment_franchises',
		'css' => 'width:500px; height: 150px;',
		'type' => 'textarea',
		'std' => __('<p>Dear [#client_name#],<p><p>Your request to add franchises of <b>[#listing_title#]</b> has been APPROVED at site [#site_link#].</p><p>Your details are below:</p><p>Your main listing: [#main_listing_link#]</p><p>Franchise listings: [#franchise_listings_links#]</p><br><p>We hope you enjoy. Thanks!</p><p>[#site_link#]</p>', 'geodir-franchise')
		);
	$options[] = array( 'type' => 'sectionend', 'id' => 'gd_franchise_notifications_emails' );
	
	/**
	 * Filters the franchise notification settings options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options Franchise notification settings options array.
	 */
	$options = apply_filters( 'geodir_franchise_notifications_settings', $options );
	
	return $options;
}

/**
 * Handle ajax request within franchise plugin and outputs json formatted content.
 *
 * All ajax requests like get the franchise data to fill up date in add franchise form, save franchise, 
 * show the payments detail in add franchise form are handled within this function.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global object $gd_session GeoDirectory Session object.
 * @global array $gd_permalink_cache Array of cached listing permalinks.
 */
function geodir_franchise_ajax() {
    global $wpdb, $plugin_prefix, $gd_session, $gd_permalink_cache;
    $subtab = geodir_franchise_current_subtab();

    if ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'franchise' ) {
        switch ($subtab) {
            case 'notifications':
                geodir_update_options( geodir_franchise_notifications() );
            break;
            default:
                geodir_update_options( geodir_franchise_general_settings() );
            break;
        }
        
        $msg = urlencode_deep( __( 'Settings saved.', 'geodir-franchise' ) );
        
        wp_redirect( admin_url() . 'admin.php?page=geodirectory&tab=franchise&subtab=' . $subtab . '&success_msg=' . $msg );
        gd_die();
    }

    $request = isset( $_REQUEST ) ? $_REQUEST : '';
    $task = isset( $request['task'] ) ? $request['task'] : '';
    $wpnonce = isset( $request['_wpnonce'] ) ? $request['_wpnonce'] : '';

    $return = array();
    switch ( $task ) {
        case 'franchise_data': {
            $franchise_id = isset( $request['franchise'] ) ? $request['franchise'] : '';
            
            if ( wp_verify_nonce( $request['_wpnonce'], 'geodir_franchise_nonce_' . $franchise_id ) && !empty( $franchise_id ) ) {
                $post_id = isset( $request['post_id'] ) ? $request['post_id'] : '';
                $franchise = $post_id ? geodir_get_post_meta( $post_id, 'franchise', true ) : $franchise_id;
                
                if ( $franchise == $franchise_id && geodir_franchise_check( $franchise_id ) ) {
                    $post_info = geodir_get_post_info( $post_id );
                    if ( $post_id && empty( $post_info ) ) {
                        echo json_encode( $return );
                        exit;
                    }
                                    
                    if ( empty( $post_info ) ) {
                        $post_info = array();
                        
                        $post_info['ID'] = 0;
                        $post_info['post_title'] = '';
                        $post_info['post_content'] = '';
                        
                        $post_info = (object)$post_info;
                    }
                    
                    $franchise_info = geodir_get_post_info( $franchise_id );
                    
                    $post_type = get_post_type( $franchise_id );
                    $package_id = geodir_get_post_meta( $franchise_id, 'package_id', true );
                    $claimed = isset( $post_info->claimed ) ? (int)$post_info->claimed : 0;
                    $claimed = isset( $franchise_info->claimed ) ? (int)$franchise_info->claimed : $claimed;

                    if ( !empty( $locked_fields ) ) {
                        if ( in_array( 'post_title', $locked_fields ) ) {
                            $post_info->post_title = $franchise_info->post_title;
                        }
                        
                        if ( in_array( 'post_desc', $locked_fields ) ) {
                            $post_info->post_desc = $franchise_info->post_content;
                        }
                    }
                                        
                    $data = array();
                    $data['post_title'] = array( 'type' => 'text', 'value' => $post_info->post_title );
                    $data['post_desc'] = array( 'type' => 'textarea', 'value' => $post_info->post_content );
                    
                    $custom_fields = geodir_post_custom_fields( $package_id, 'all', $post_type );
                    $locked_fields = geodir_franchise_get_locked_fields( $franchise_id );
                    
                    foreach( $custom_fields as $key => $field ) {
                        $field_name = $field['htmlvar_name'];
                        $field_type = $field['type'];
                        
                        if (empty($field_name) || $field_type == 'fieldset') {
                            continue;
                        }
                        $default_value = empty( $post_info ) ? stripslashes_deep($field['default_value']) : '';
                        
                        $extra_fields = maybe_unserialize( $field['extra_fields'] );
                        $field_value = isset( $post_info->$field_name ) ? $post_info->$field_name : $default_value;
                        
                        if ( !empty( $locked_fields ) && in_array( $field_name, $locked_fields ) ) {
                            $field_value = isset( $franchise_info->$field_name ) ? $franchise_info->$field_name : $default_value;
                        }

                        switch( $field_type ) {
                            case 'text':
                            case 'email':
                            case 'url':
                            case 'phone':
                                $data[$field_name] = array( 'type' => 'text', 'value' => $field_value );
                            break;
                            case 'textarea':
                            case 'html':
                                $data[$field_name] = array( 'type' => 'textarea', 'value' => $field_value );
                            break;
                            case 'address':
                                $default_location = geodir_get_default_location();
                                
                                if ( !empty( $locked_fields ) ) {
                                    if ( in_array( 'post', $locked_fields ) ) {
                                        $post_info->post_address = $franchise_info->post_address;
                                        $post_info->post_zip = $franchise_info->post_zip;
                                        $post_info->post_latitude = $franchise_info->post_latitude;
                                        $post_info->post_longitude = $franchise_info->post_longitude;
                                        $post_info->post_country = $franchise_info->post_country;
                                        $post_info->post_region = $franchise_info->post_region;
                                        $post_info->post_city = $franchise_info->post_city;
                                    }
                                }
                                $post_latitude = !empty($post_info->post_latitude) ? $post_info->post_latitude : ( isset($default_location->city_latitude) ? $default_location->city_latitude : '' );
                                $post_longitude = !empty($post_info->post_longitude) ? $post_info->post_longitude : ( isset($default_location->city_longitude) ? $default_location->city_longitude : '' );
                                
                                $data['post_address'] = array( 'type' => 'text', 'value' => ( isset($post_info->post_address) ? $post_info->post_address : '' ) );
                                $data['post_zip'] = array( 'type' => 'text', 'value' => ( isset($post_info->post_zip) ? $post_info->post_zip : '' ) );
                                $data['post_latitude'] = array( 'type' => 'text', 'value' => $post_latitude );
                                $data['post_longitude'] = array( 'type' => 'text', 'value' => $post_longitude );
                                
                                if ( isset( $extra_fields['show_city'] ) && !empty( $extra_fields['show_city'] ) ) {
                                    $post_country = !empty($post_info->post_country) ? $post_info->post_country : ( isset($default_location->country) ? $default_location->country : '' );
                                    $post_region = !empty($post_info->post_region) ? $post_info->post_region : ( isset($default_location->region) ? $default_location->region : '' );
                                    $post_city = !empty($post_info->post_city) ? $post_info->post_city : ( isset($default_location->city) ? $default_location->city : '' );
                                    
                                    $data['post_country'] = array( 'type' => 'text', 'value' => $post_country );
                                    $data['post_region'] = array( 'type' => 'text', 'value' => $post_region );
                                    $data['post_city'] = array( 'type' => 'text', 'value' => $post_city );
                                }
                            break;
                            case 'checkbox':
                            case 'radio':
                            case 'select':
                            case 'datepicker':
                            case 'time':
                            case 'file':
                                $data[$field_name] = array( 'type' => $field_type, 'value' => $field_value );
                            break;
                            case 'multiselect':
                                $field_value = $field_value != '' ? explode( ",", $field_value ) : array();
                                $data[$field_name] = array( 'type' => $field_type, 'value' => $field_value );
                            break;
                            case 'taxonomy':
                                global $post, $cat_display, $exclude_cats;
                                
                                $post = $post_info;
                                if ( !empty( $locked_fields ) && in_array( $field_name, $locked_fields ) ) {
                                    $post = $franchise_info;
                                }

                                $package_info = (array)geodir_post_package_info( array(), $franchise_info );
                                
                                $exclude_cats = array();
                                $cat_limit = '';

                                if ( !empty( $package_info ) ) {
                                    $exclude_cats = isset( $package_info['cat'] ) && $package_info['cat'] != '' ? explode( ',', $package_info['cat'] ) : $exclude_cats;
                                    $cat_limit = isset( $package_info['cat_limit'] ) ? (int)$package_info['cat_limit'] : $cat_limit;
                                }
                                
                                $html = '';
                                $cat_display = $extra_fields;
                                
                                if ( $cat_display != '' && $cat_display != 'ajax_chained' ) {
                                    if ( $cat_limit > 0 && $cat_display != 'select' && $cat_display != 'radio' ) {
                                        $cat_limit_msg = __( 'Only select', 'geodirectory' ) . ' ' . $cat_limit . __( ' categories for this package.', 'geodirectory' );
                                    } else {
                                        $cat_limit_msg = $field['required_msg'] != '' ? __( $field['required_msg'], 'geodirectory' ) : '';
                                    }
                                    
                                    $html .= '<input type="hidden" cat_limit="' . $cat_limit . '" id="cat_limit" value="' . esc_attr( $cat_limit_msg ) . '" name="cat_limit[' . $field_name . ']"  />';
                                    
                                    if ( $cat_display == 'select' || $cat_display == 'multiselect' ) {
                                        $multiple = $cat_display == 'multiselect' ? 'multiple="multiple"' : '';
            
                                        $html .= '<select id="' . $field_name . '" ' . $multiple . ' type="' . $field_name . '" name="post_category[' . $field_name . '][]" alt="' . $field_name . '" field_type="' . $cat_display . '" class="geodir_textfield textfield_x chosen_select" data-placeholder="' . __( 'Select Category', 'geodirectory' ) . '">';
                                        
                                        if ( $cat_display == 'select' ) {
                                            $html .= '<option value="">' . __( 'Select Category', 'geodirectory' ) . '</option>';
                                        }
                                    }
                                    
                                    $html .= geodir_custom_taxonomy_walker( $field_name, $cat_limit );

                                    if ( $cat_display == 'select' || $cat_display == 'multiselect' ) {
                                        $html .= '</select>';
                                    }
                                } else {
                                    ob_start();
                                    geodir_custom_taxonomy_walker2( $field_name, $cat_limit );
                                    $html .= ob_get_clean();
                                }
                                
                                $data[$field_name] = array( 'type' => $field_type, 'value' => $field_value, 'html' => $html );
                            break;
                        }
                    }
                    $post_tags = '';
                    $post_images = '';
                    
                    if ( $post_id ) {
                        $post_tags = wp_get_post_terms( $post_id, $post_type . '_tags', array( 'fields' => 'names' ) );
                        $post_tags = !empty( $post_tags ) && is_array( $post_tags ) ? implode( ",", $post_tags ) : '';
                        
                        $images = geodir_get_images( $post_id );
                        
                        $post_images = array();
                        if ( !empty( $images ) ) {
                            foreach ( $images as $image ) {
                                if ( isset( $image->src ) && trim( $image->src ) != '' ) {
                                    $post_images[] = trim( $image->src );
                                }
                            }
                        }
                        
                        $post_images = !empty( $post_images ) ? implode( ",", $post_images ) : '';
                    }
                                        
                    $data['claimed'] = array( 'type' => 'radio', 'value' => $claimed );
                    $data['post_tags'] = array( 'type' => 'tags', 'value' => $post_tags );
                    $data['post_images'] = array( 'type' => 'images', 'value' => $post_images );
                    
                    if ($post_type == 'gd_event') {
                        $recurring_data = !empty($post_info->recurring_dates) ? maybe_unserialize($post_info->recurring_dates) : array();
                        
                        $event_start = isset($recurring_data['event_start']) ? $recurring_data['event_start'] : date_i18n('Y-m-d');
                        $event_end = isset($recurring_data['event_end']) ? $recurring_data['event_end'] : '';
                        $all_day = isset($recurring_data['all_day']) ? (int)$recurring_data['all_day'] : 0;
                        $starttime = isset($recurring_data['event_end']) && !$all_day ? $recurring_data['starttime'] : '00:00';
                        $endtime = isset($recurring_data['event_end']) && !$all_day ? $recurring_data['endtime'] : '00:00';

                        $data['event_start'] = array( 'type' => 'text', 'value' => $event_start );
                        $data['event_end'] = array( 'type' => 'text', 'value' => $event_end );
                        $data['all_day'] = array( 'type' => 'checkbox', 'value' => $all_day );
                        $data['starttime'] = array( 'type' => 'select', 'value' => $starttime );
                        $data['endtime'] = array( 'type' => 'select', 'value' => $endtime );
                    }
                    
                    /**
                     * Filters the franchise fields data.
                     *
                     * @since 1.0.0
                     *
                     * @param array $data Franchise fields data.
                     * @param int $post_id Franchise listing id.
                     * @param int $franchise_id Main listing id.
                     */
                    $data = apply_filters( 'geodir_franchise_listing_fields_data', $data, $post_id, $franchise_id );
                    
                    $return = $data;
                }
            }
        }
        break;
        case 'save_franchise': {
            $franchise_id = isset( $request['franchise'] ) ? $request['franchise'] : '';
                        
            if ( wp_verify_nonce( $request['_wpnonce'], 'geodir_franchise_nonce_' . $franchise_id ) && !empty( $franchise_id ) ) {
                $post_id = isset( $request['post_id'] ) ? $request['post_id'] : '';
                $franchise = $post_id ? geodir_get_post_meta( $post_id, 'franchise', true ) : $franchise_id;
                
                $post_info = $post_id ? geodir_get_post_info( $post_id ) : NULL;
                if ( $franchise == $franchise_id && geodir_franchise_check( $franchise_id ) ) {
                    if ( $post_id && empty( $post_info ) ) {
                        $return['success'] = false;
                        $return['error'] = __( 'The requested listing not found!', 'geodir-franchise' );
                        echo json_encode( $return );
                        exit;
                    }
                }
                
                if ($post_id && !geodir_listing_belong_to_current_user($post_id)) {
                    $return['success'] = false;
                    $return['error'] = __( 'You are not allowed to access this listing!', 'geodir-franchise' );
                    echo json_encode( $return );
                    exit;
                }
                
                $default_status = geodir_new_post_default_status();
                $franchise_info = get_post( $franchise_id );
                $franchise_cost = geodir_franchise_get_franchise_cost($franchise_id);
                $post_type = get_post_type( $franchise_id );
                $current_post_status = !empty($post_info) ? $post_info->post_status : '';
                
                $save_post_status = 'draft';
                if (!$franchise_cost > 0) {
                    $save_post_status = $default_status;
                    $current_post_status = $save_post_status;
                }
                
                $parent_link_label = __( 'View main listing', 'geodir-franchise' );
                /**
                 * Filter the view main listing link label.
                 *
                 * @since 1.0.0
                 *
                 * @param string $parent_link_label View main listing link label.
                 * @param string $post_type The post type.
                 */
                $parent_link_label = apply_filters( 'geodir_franchise_view_main_listing_link_label', $parent_link_label, $post_type );
                
                $main_listing_link = '<a class="gd-franchise-link-m" title="' . esc_attr( $parent_link_label ) . '" href="' . esc_url( get_permalink( $franchise_id ) ) . '">' . $parent_link_label . '</a>';
                
                $allowed_to_add_more = geodir_franchise_allowed_to_add_more( $franchise_id );
                if ( !$post_id > 0 && $allowed_to_add_more !== 'unlimited' && $allowed_to_add_more === 0 ) {
                    $franchise_limit = geodir_franchise_get_franchise_limit( $franchise_id );
                    
                    $return['success'] = false;
                    $return['error'] = '<font>' . wp_sprintf( __( 'Oops! franchise listing not saved because you have reached the limit of <b>%d</b> franchise(s) with this package.', 'geodir-franchise' ), $franchise_limit ) . '</font> ' . $main_listing_link;
                    echo json_encode( $return );
                    exit;
                }
                
                /*
                 * Unset the session so we don't loop.
                 */
                $gd_session->un_set('listing');
                
                if ( empty( $post_info ) ) {
                    $post_info = array();
                    $post_info['post_type'] = $post_type;
                    $post_info['post_status'] = $save_post_status;
                    $post_info = (object)$post_info;
                }
                $request['post_type'] = $post_type;
                $request['content'] = $request['post_desc'];
                
                global $post;
                
                $post = $post_info;
                
                if ($default_status != $save_post_status) {
                    update_option('geodir_new_post_default_status', $save_post_status);
                }
                
                if (empty($request['post_title'])) {
                    $request['post_title'] = $franchise_info->post_title;
                    
                    if ( !$post_id > 0 ) {
                        $request['post_name'] = geodir_franchise_unique_post_slug( $franchise_info->post_name, $post_type, $franchise_id );
                    }
                }

                $save_id = geodir_save_listing( $request, false, true );
                
                if ($default_status != $save_post_status) {
                    update_option('geodir_new_post_default_status', $default_status);
                }
                
                if ( is_wp_error( $save_id ) ) {
                    $return['success'] = false;
                    $return['error'] = $save_id->get_error_message();
                } else {
                    $return['success'] = (int)$save_id;
                    $return['post_id'] = $save_id;
                    $return['post_text'] = wp_sprintf( __( '%s ( ID: %d )', 'geodir-franchise' ), $request['post_title'], $save_id );
                        
                    if ($current_post_status != '' && get_post_status($save_id) != $current_post_status) {
                        geodir_set_post_status($save_id, $current_post_status);
                    }
                    
                    $post_status = get_post_status($save_id);
                    
                    $success_msg = '';
                    $add_franchise_link_label = __( 'Add another franchise', 'geodir-franchise' );
                    
                    /**
                     * Filter the add franchise link label.
                     *
                     * @since 1.0.0
                     *
                     * @param string $add_franchise_link_label Add franchise link label.
                     * @param string $post_type The post type.
                     */
                    $add_franchise_link_label = apply_filters( 'geodir_franchise_add_franchise_link_label', $add_franchise_link_label, $post_type );
                    $add_franchise_link = '<a title="' . esc_attr( $add_franchise_link_label ) . '" href="javascript:void(0);" onclick="gd_franchise_add_another();">' . $add_franchise_link_label . '</a> ';
                    
                    $allowed_to_add_more = geodir_franchise_allowed_to_add_more( $franchise_id );
                    if ( $allowed_to_add_more !== 'unlimited' && $allowed_to_add_more === 0 ) {
                        $add_franchise_link = '';
                    }
                    
                    if ($post_status == 'publish' || $post_status == 'private') {
                        if ( $post_id > 0 ) {
                            $success_msg .= '<font>' . __( 'Franchise listing updated successfully.', 'geodir-franchise' ) . '</font> ';
                        } else {
                            $success_msg .= '<font>' . __( 'Franchise listing submitted successfully.', 'geodir-franchise' ) . '</font> ';
                            
                            if ( !empty( $gd_permalink_cache[ $save_id ] ) ) {
                                unset( $gd_permalink_cache[ $save_id ] );
                            }
                        }
                        $success_msg .= $add_franchise_link;
                        
                        $franchise_link_label = __( 'View franchise', 'geodir-franchise' );

                        /**
                         * Filter the view franchise link label.
                         *
                         * @since 1.0.0
                         *
                         * @param string $franchise_link_label View franchise link label.
                         * @param string $post_type The post type.
                         */
                        $franchise_link_label = apply_filters( 'geodir_franchise_view_franchise_link_label', $franchise_link_label, $post_type );
                    
                        $success_msg .= '<a class="gd-franchise-link-s" title="' . esc_attr( $franchise_link_label ) . '" href="' . esc_url( get_permalink( $save_id ) ) . '">' . $franchise_link_label . '</a> ';
                    } else {
                        if ($franchise_cost > 0) {
                            $success_msg .= '<font>' . __( 'Franchise listing has been submitted. In order to publish your listing, kindly complete the payment of Pay For Franchises.', 'geodir-franchise' ) . '</font> ';
                        } else {
                            $success_msg .= '<font>' . __( 'Franchise listing has been submitted. An admin will review your listing shortly.', 'geodir-franchise' ) . '</font> ';
                        }
                        
                        $success_msg .= $add_franchise_link;
                    }
                    $success_msg .= $main_listing_link;
                    
                    $return['success_msg'] = $success_msg;
                }
            }
        }
        break;
        case 'check_payments': {
            $franchise_id = isset( $request['franchise_id'] ) ? $request['franchise_id'] : '';
            
            if ( wp_verify_nonce( $request['_wpnonce'], 'geodir_franchise_nonce_' . $franchise_id ) && !empty( $franchise_id ) && geodir_franchise_check( $franchise_id ) ) {
                $return = geodir_franchise_get_franchises_cost( $franchise_id );
            }
        }
        break;
        case 'duplicate_images': {
            $return = array( 'success' => false );
            
            $franchise_id = isset( $request['franchise_id'] ) ? $request['franchise_id'] : '';
            
            if ( wp_verify_nonce( $request['_wpnonce'], 'geodir_franchise_nonce_' . $franchise_id ) && !empty( $franchise_id ) && geodir_franchise_check( $franchise_id ) ) {
                $post_images = geodir_get_images( $franchise_id );
                
                $images = array();
                if ( !empty( $post_images ) ) {
                    $wp_filesystem = geodir_init_filesystem();
                    if ( !$wp_filesystem ) {
                        $return['error'] = __( 'Filesystem ERROR: Could not access filesystem.', 'geodirectory' );
                        wp_send_json( $return );
                    }
                    
                    if ( !empty( $wp_filesystem ) && isset( $wp_filesystem->errors ) && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
                        $return['error'] = __( 'Filesystem ERROR: ' . $wp_filesystem->errors->get_error_message(), 'geodirectory' );
                        wp_send_json( $return );
                    }
                    
                    $uploads = wp_upload_dir();
                    
                    $temp_subdir = 'temp_' . (int)get_current_user_id();
                    $temp_dir = trailingslashit( $uploads['path'] ) . $temp_subdir;
                    
                    if ( !$wp_filesystem->is_dir( $temp_dir ) ) {
                        if ( !$wp_filesystem->mkdir( $temp_dir, FS_CHMOD_DIR ) ) {
                            $return['error'] = __( 'ERROR: Could not create cache directory. This is usually due to inconsistent file permissions.', 'geodirectory' );
                            wp_send_json( $return );
                        }
                    }
                    
                    foreach ( $post_images as $post_image ) {
                        if ( !empty( $post_image->src ) && !empty( $post_image->path ) && is_file( $post_image->path ) && file_exists( $post_image->path ) ) {
                            $filename = basename( $post_image->path );							
                            if ( strpos( $filename, $franchise_id . '_' ) === 0 ) {
                                $filename = ltrim( $filename, $franchise_id . '_' );
                            }
                            
                            $source_file = $post_image->path;
                            $destination_file = trailingslashit( $temp_dir ) . $filename;
                            
                            if ( $wp_filesystem->copy( $source_file, $destination_file, true, FS_CHMOD_FILE ) ) {
                                $temp_url = trailingslashit( $uploads['url'] ) . $temp_subdir;
                                
                                $images[] = trailingslashit( $temp_url ) . $filename;
                            }
                        }
                    }
                }
                
                $return['success'] = true;
                $return['data'] = array( 'images' => $images );
                if ( empty( $images ) ) {
                    $return['error'] = __( 'No images found to duplicate from main listing.', 'geodir-franchise' );
                }
            } else {
                $return['error'] = __( 'You are not allowed to do this action!', 'geodir-franchise' );
            }
        }
        break;
    }

    if (!empty($return)) {
        $return = stripslashes_deep($return);
    }

    echo json_encode( $return );
    gd_die();
}

/**
 * Get the franchise cost data for main listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $franchise_id Main parent listing id.
 * @param string $key Franchise cost data key. Default empty.
 * @return mixed The franchise cost data.
 */
function geodir_franchise_get_franchises_cost( $franchise_id, $key = '' ) {
	$return = '';
	
	if (geodir_franchise_check( $franchise_id ) && geodir_franchise_is_payment_active() ) {
		$return = array();
		
		$franchise_cost = geodir_franchise_get_franchise_cost( $franchise_id );
		$franchise_limit = geodir_franchise_get_franchise_limit( $franchise_id );
		$my_franchises = geodir_franchise_post_franchises( $franchise_id, false, true, array('draft', 'pending') );
		$total_franchises = count( $my_franchises );
		
		$costf = geodir_payment_price($franchise_cost);
		$amount = number_format( (float)($franchise_cost * $total_franchises), 2 );
		$amountf = geodir_payment_price($amount);
		
		$info = wp_sprintf( __( 'Pay amount of <b>%s</b> for %d franchise(s). Cost per franchise is %s', 'geodir-franchise' ), $amountf, $total_franchises, $costf );
		
		$return['count'] = $total_franchises;
		$return['cost'] = $franchise_cost;
		$return['costf'] = $costf;
		$return['amount'] = $amount;
		$return['amountf'] = $amountf;
		$return['franchises'] = $my_franchises;
		$return['limit'] = (int)$franchise_limit;
		$return['info'] = $info;
		
		if ( isset( $return[$key] ) ) {
			$return = $return[$key];
		}
	}
	return $return;
}

/**
 * Get the franchise cost amount for main listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $franchise_id Main parent listing id.
 * @return mixed The franchise cost amount.
 */
function geodir_franchise_get_franchise_cost( $franchise_id ) {
	$return = NULL;
	
	if (geodir_franchise_check( $franchise_id ) && geodir_franchise_is_payment_active() ) {
		$franchise_info = geodir_get_post_info( $franchise_id );
		$package_info = (array)geodir_post_package_info( array(), $franchise_info );
		
		if ( !empty( $package_info ) && isset( $package_info['franchise_cost'] ) && (float)$package_info['franchise_cost'] > 0 ) {
			$return = number_format( (float)$package_info['franchise_cost'], 2 );
		}
	}
	return $return;
}

/**
 * Add the franchise column in listing tables if not exists.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $option Name of the option to add.
 * @param mixed $value Value of the option. Default empty.
 */
function geodir_franchise_check_franchise_column( $option, $value ) {
	global $plugin_prefix;
	
	$post_types = get_option( 'geodir_franchise_posttypes' );
	
	if ( !empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$listing_table = $plugin_prefix . $post_type . '_detail';
			
			geodir_add_column_if_not_exist( $listing_table, 'franchise', "INT( 11 ) NOT NULL DEFAULT 0" );
		}
	}
	
	if (geodir_franchise_is_payment_active()) {
		geodir_franchise_payment_package_table_created();
	}
}

/**
 * Checks and add franchise columns in db on payment package table created.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param string $price_table DB query of price package table. Default empty.
 */
function geodir_franchise_payment_package_table_created( $price_table = '' ) {
	if (geodir_franchise_is_payment_active()) {
		geodir_add_column_if_not_exist( GEODIR_PRICE_TABLE, 'enable_franchise', "INT( 11 ) NOT NULL DEFAULT 0" );
		geodir_add_column_if_not_exist( GEODIR_PRICE_TABLE, 'franchise_cost', "FLOAT NULL" );
		geodir_add_column_if_not_exist( GEODIR_PRICE_TABLE, 'franchise_limit', "INT( 11 ) NOT NULL DEFAULT 0" );
	}
}

/**
 * Display franchise fields in front-end listing form.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_add_listing_field() {
	global $post;
	
	$post_id = isset( $_REQUEST['pid'] ) ? (int)$_REQUEST['pid'] : '';
	$post_type = isset( $_REQUEST['listing_type'] ) ? sanitize_text_field($_REQUEST['listing_type']) : '';
	$franchise_id = isset( $_REQUEST['franchise'] ) ? (int)$_REQUEST['franchise'] : 0;
	
	$is_franchise = false;
	if ( isset( $_REQUEST['backandedit'] ) ) {
		$post_type = isset( $post->listing_type ) ? $post->listing_type : $post_type;
		$franchise_id = isset( $post->franchise ) ? $post->franchise : $franchise_id;
	} else if ( $post_id ) {
		$post_type = isset( $post->post_type ) ? $post->post_type : $post_type;
		$franchise_id = isset( $post->franchise ) ? $post->franchise : $franchise_id;
	}
	
	if (!$post_id && $franchise_id) {
		$post = get_post($franchise_id);
	}

	if ( !geodir_franchise_enabled( $post_type ) || !geodir_franchise_pkg_is_active( $post ) ) {
		return;
	}
	
	$payment_active = geodir_franchise_is_payment_active();
	$package_info = geodir_post_package_info( array(), $post );
	$package_id = isset( $package_info->pid ) ? $package_info->pid : 0;
	$post_fields = geodir_franchise_fields_array( $package_id, $post_type );
	?>
	<h5><?php _e( 'Manage Franchise', 'geodir-franchise' );?></h5>
	<?php
	if ( geodir_franchise_check( $franchise_id ) ) {
		$locked_fields = geodir_franchise_get_locked_fields( $franchise_id );
		$my_franchises = geodir_franchise_post_franchises( $franchise_id, true, true, array('draft', 'pending', 'publish') );
		$franchise_limit = $payment_active ? geodir_franchise_get_franchise_limit( $franchise_id ) : 0;
		$allowed_to_add = $payment_active ? geodir_franchise_allowed_to_add_more( $franchise_id ) : 'unlimited';
		?>
		<input type="hidden" id="franchise" name="franchise" value="<?php echo $franchise_id; ?>" />
		<input type="hidden" name="package_id" value="<?php echo (int)geodir_get_post_meta( $franchise_id, 'package_id', true ); ?>" />
		<input type="hidden" id="gd_franchise_nonce" value="<?php echo wp_create_nonce( 'geodir_franchise_nonce_' . $franchise_id ); ?>" />
		<input type="hidden" id="gd_page_id" value="<?php echo get_the_ID(); ?>" />
		<div id="gd_my_franchises" class="geodir_form_row clearfix">
			<label for="gd_my_franchise"><?php _e( 'Select Franchise', 'geodir-franchise' );?></label>
			<div class="geodir_multiselect_list">
				<select id="gd_my_franchise" class="gd_my_franchise_chosen chosen_select" option-ajaxchosen="false" data-placeholder="<?php _e( 'Select Franchise', 'geodir-franchise' );?>" field_type="select">
				<option value="0" <?php selected( 0, (int)$post_id, true ); ?> <?php echo ( ( $allowed_to_add > 0 || $allowed_to_add === 'unlimited' ) ? '' : 'disabled' );?>><?php _e( 'Add New Franchise', 'geodir-franchise' );?></option>
				<?php 
				if( !empty( $my_franchises ) ) { 
					asort( $my_franchises );
					
					foreach ( $my_franchises as $value => $label ) {
						$label = wp_sprintf( __( '%s ( ID: %d )', 'geodir-franchise' ), $label, $value );
				?>
				<option value="<?php echo $value; ?>" <?php selected( $value, (int)$post_id, true ); ?>><?php echo $label; ?></option>
				<?php } } ?>
			</select>
			</div>
			<div id="gd_franchise_pay_row" class="geodir_form_row clearfix" style="display:none">
				<h5><?php _e( 'Franchises Cost', 'geodir-franchise' );?></h5>
				<div id="gd_franchise_cost_row" class="geodir_form_row clearfix"></div>
				<div id="gd_franchise_btn_row" class="geodir_form_row clearfix"></div>
			</div>
			<script type="text/javascript">
			jQuery(function(){
				gd_franchise_form_init('#propertyform', <?php echo (int)$franchise_id;?>);
			<?php if ( !empty( $locked_fields ) && is_array( $locked_fields ) ) { $locked_fields = "['" . implode( "','", $locked_fields ) . "']";?>
				gd_franchise_lock_fields(<?php echo $locked_fields; ?>, '#propertyform');
			<?php } ?>
			<?php if ( isset( $_REQUEST['franchise'] ) && $_REQUEST['franchise'] == $franchise_id ) { ?>
			jQuery('.geodir_price_package_row [name="package_id"]').each(function(){
				var onclick = jQuery(this).attr('onclick');
				var quote = onclick[onclick.length - 1];
				onclick = onclick.substring(0, onclick.length - 1) + "&franchise=<?php echo $franchise_id;?>" + quote;
				jQuery(this).attr('onclick', onclick);
			});
			<?php } ?>
			});
			</script>
			<?php
			if ( $payment_active ) {
				$limit_msg = '';
				if ( $allowed_to_add !== 'unlimited' && (int)$franchise_limit > 0 ) {
					if ( $allowed_to_add > 0 ) {
						$limit_notice = wp_sprintf( __( 'You can add <b>%d</b> more new franchise(s) with this package.', 'geodir-franchise' ), $allowed_to_add );
					} else {
						$limit_notice = wp_sprintf( __( 'You can add total only <b>%d</b> franchise(s) with this package.', 'geodir-franchise' ), $franchise_limit );
						$limit_msg = '<div class="gd-franchise-msg gd-franchise-msg-success gdfm-limit-msg">' . wp_sprintf( __( 'You have reached the limit of <b>%d</b> franchise(s) with this package!', 'geodir-franchise' ), $franchise_limit ) . '</div>';
					}
				} else {
					$limit_notice = __( 'You can add <b>unlimited</b> franchises with this package.', 'geodir-franchise' );
				}
				echo '<span class="geodir_message_note">' . $limit_notice . '</span>' . $limit_msg;
			}
			?>
		</div>
		<?php
		return;
	}

	$franchise_lock = array();
	if ( isset( $_REQUEST['backandedit'] ) ) {
		$is_franchise = isset( $post->gd_is_franchise ) ? $post->gd_is_franchise : $is_franchise;
		$franchise_lock = $is_franchise && isset( $post->gd_franchise_lock )  && is_array( $post->gd_franchise_lock ) ? $post->gd_franchise_lock : array();
	} else if ( $post_id > 0 ) {
		$is_franchise = get_post_meta( $post_id, 'gd_is_franchise', true );
		$franchise_lock = $is_franchise ? geodir_franchise_get_locked_fields( $post_id ) : array();
		$franchise_lock = is_array( $franchise_lock ) ? $franchise_lock : array();
	}

	$style_fields = !$is_franchise ? 'style="display:none"' : '';
	?>
	<div id="gd_is_franchise_row" class="geodir_form_row clearfix">
		<label for="gd_franchise_chk"><?php _e( 'Enable franchise', 'geodir-franchise' );?></label>
		<input type="hidden" value="<?php echo (int)$is_franchise; ?>" id="gd_is_franchise" name="gd_is_franchise" />
		<input id="gd_franchise_chk" type="checkbox" <?php checked( $is_franchise, true ); ?> field_type="checkbox" class="gd-checkbox gd-franchise-chk" value="1" />
		<span class="geodir_message_note"><?php _e( 'Enable franchise feature for your listing.', 'geodir-franchise' );?></span>
	</div>
	<div id="gd_franchise_lock_row" class="geodir_form_row clearfix" <?php echo $style_fields;?>>
		<label for="gd_franchise_lock"><?php _e( 'Lock franchise fields', 'geodir-franchise' );?></label>
		<div class="geodir_multiselect_list">
			<select id="gd_franchise_lock" name="gd_franchise_lock[]" class="gd_franchise_lock_chosen chosen_select" option-ajaxchosen="false" data-placeholder="<?php _e( 'Select Fields', 'geodir-franchise' );?>" multiple="multiple" field_type="multiselect">
			<?php if( !empty( $post_fields ) ) { foreach ( $post_fields as $name => $label ) { if ( empty( $name ) ) { continue; } ?>
			<option value="<?php echo $name; ?>" <?php selected( in_array( $name, $franchise_lock ) , true ); ?>><?php echo $label; ?></option>
			<?php } } ?>
		</select>
		</div>
	</div>
	<?php
}

/**
 * Enqueue franchise plugin scripts.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global string $pagenow The current screen.
 */
function geodir_franchise_enqueue_scripts() {
	global $pagenow;
	
	$is_settings_page = ( $pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'franchise' ) ? true : false;
	
	if ( ( is_page() && geodir_is_page( 'add-listing' ) ) || ( is_admin() && ( ( $pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' ) || $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) || $is_settings_page ) {
		wp_register_style( 'geodir_franchise_style', GEODIR_FRANCHISE_PLUGIN_URL . '/css/gd-franchise.css' );
		wp_enqueue_style( 'geodir_franchise_style' );
		
		if ( is_admin() ) {
			wp_register_script( 'geodir-franchise', GEODIR_FRANCHISE_PLUGIN_URL . '/js/geodir-franchise.js', array(), GEODIR_FRANCHISE_VERSION );
		} else {
			wp_register_script( 'geodir-franchise', GEODIR_FRANCHISE_PLUGIN_URL . '/js/geodir-franchise.js', array( 'geodirectory-script' ), GEODIR_FRANCHISE_VERSION );
		}
		wp_enqueue_script( 'geodir-franchise' );
	}
}

/**
 * Check franchise feature enabled for current post type.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param string $post_type WP post type or WP texonomy. Ex: gd_place.
 * @param bool $taxonomy Whether $post_type is taxonomy or not.
 * @return bool True if franchise feature enabled, otherwise false.
 */ 
function geodir_franchise_enabled( $post_type = '', $taxonomy = false ) {
	$post_types = get_option( 'geodir_franchise_posttypes' );
	
	if ( $taxonomy && !empty( $post_types ) ) {
		$posttypes = array();
		
		foreach ( $post_types as $posttype ) {
			$posttypes[] = $posttype . 'category';
			$posttypes[] = $posttype . '_tags';
		}
		
		$post_types = $posttypes;
	}

	$return = false;
	if ( $post_type != '' && !empty( $post_types ) && in_array( $post_type, $post_types ) ) {
		$return = true;
	}

	return $return;
}

/**
 * Get the fields array for price package id.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $package_id The price package id.
 * @param string $post_type The post type.
 * @return array Fields array.
 */
function geodir_franchise_fields_array( $package_id, $post_type ) {
	$custom_fields = geodir_post_custom_fields( $package_id, 'all', $post_type );
	
	$fields = array();	
	if ( !empty( $custom_fields ) ) {
		foreach ( $custom_fields as $field_id => $field ) {
			if (empty($field['name']) || $field['type'] == 'fieldset') {
				continue;
			}
			
			if ( !empty( $field['site_title'] ) ) {
				$field_title = __( $field['site_title'], 'geodirectory' );
			} else if ( !empty( $field['label'] ) ) {
				$field_title = __( $field['label'], 'geodirectory' );
			} else if ( !empty( $field['admin_title'] ) ) {
				$field_title = __( $field['admin_title'], 'geodirectory' );
			} else {
				$field_title = __( $field['name'], 'geodirectory' );
			}
			
			$fields[$field['name']] = stripslashes_deep( $field_title );
		}
	}
	
	$fields['post_title'] = PLACE_TITLE_TEXT;
	$fields['post_desc'] = PLACE_DESC_TEXT;
	$fields['post_tags'] = TAGKW_TEXT;
	if ( defined( 'CLAIM_BUSINESS_OWNER_ASSOCIATE' ) ) {
		$fields['claimed'] = CLAIM_BUSINESS_OWNER_ASSOCIATE;
	}
	
	/**
	 * Filter the fields array for price package.
	 *
	 * @since 1.0.0
	 * @param array $fields The fields array.
	 * @param int $package_id The price package id.
	 * @param string $post_type The post type.
	 */
	$fields = apply_filters( 'geodir_franchise_fields_array', $fields, $package_id, $post_type );
	
	if ( !empty( $fields ) ) {
		asort( $fields );
	}

	return $fields;
}

/**
 * Manage and save the franchise data after a listing is saved to the database.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $post_id The post ID.
 * @param array $post_info The post info array.
 * @return mixed.
 */
function geodir_franchise_after_save_listing( $post_id, $post_info ) {
	if ( !$post_id > 0 || empty( $post_info ) ) {
		return;
	}

	if ( ! ( isset( $post_info['gd_is_franchise'] ) || isset( $post_info['franchise'] ) ) ) {
		return;
	}
	
	$listing_type = isset( $post_info['listing_type'] ) && $post_info['listing_type'] != '' ? $post_info['listing_type'] : (isset( $post_info['post_type'] ) && $post_info['post_type'] != '' ? $post_info['post_type'] : '');
	if ( !geodir_franchise_enabled( $listing_type ) ) {
		return;
	}
	
	if ( isset( $post_info['gd_is_franchise'] ) && empty( $post_info['franchise'] ) ) {
		if ( absint( $post_info['gd_is_franchise'] ) != 0 ) {
			$gd_franchise_lock = array();
			
			if ( (int)$post_info['gd_is_franchise'] && isset( $post_info['gd_franchise_lock'] ) ) {
				if ( is_array( $post_info['gd_franchise_lock'] ) ) {
					$gd_franchise_lock = $post_info['gd_franchise_lock'];
				} else {
					$gd_franchise_lock = str_replace(" ", "", $post_info['gd_franchise_lock'] );
					$gd_franchise_lock = trim( $gd_franchise_lock );
					$gd_franchise_lock = explode( ",", $gd_franchise_lock );
				}
			}
			
			update_post_meta( $post_id, 'gd_is_franchise', (int)$post_info['gd_is_franchise'] );
			update_post_meta( $post_id, 'gd_franchise_lock', $gd_franchise_lock );

			geodir_franchise_merge_locked_fields( $post_id );
		} else {
			geodir_franchise_remove_franchise( $post_id );
		}
	} else {
		if ( isset( $post_info['franchise'] ) && (int)$post_info['franchise'] > 0 && geodir_franchise_check( $post_info['franchise'] ) ) {
			geodir_save_post_meta( $post_id, 'franchise', (int)$post_info['franchise'] );
			
			geodir_franchise_merge_locked_fields( (int)$post_info['franchise'], array( $post_id ) );
		}
	}
	
	return true;
}

/**
 * Add the franchise settings mext box in back-end listing form.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_meta_box_add() {
	global $post;
	
	$geodir_post_types = geodir_get_posttypes('array');
	$geodir_posttypes = array_keys( $geodir_post_types );
	
	if ( isset( $post->post_type ) &&  in_array( $post->post_type, $geodir_posttypes ) ) {
		$geodir_posttype = $post->post_type;
		
		if ( geodir_franchise_enabled( $geodir_posttype ) && geodir_franchise_pkg_is_active( $post ) ) {
			$post_typename = ucwords( $geodir_post_types[$geodir_posttype]['labels']['singular_name'] );
			add_meta_box( 'geodir_franchise_admin_field', wp_sprintf( __( '%s Franchise Settings', 'geodir-franchise' ), __( $post_typename, 'geodir-franchise' ) ), 'geodir_franchise_admin_field', $geodir_posttype, 'side', 'high' );
		}
	}	
}

/**
 * Display franchise fields in back-end listing form.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_admin_field() {
	global $post;
	
	$post_type = $post->post_type;
	$post_id = isset( $post->ID ) && $post->ID > 0 ? $post->ID : NULL;
	$franchise_id = isset( $_REQUEST['franchise'] ) ? (int)$_REQUEST['franchise'] : 0;
	
	$is_franchise = false;
	$franchise_id = isset( $post->franchise ) ? $post->franchise : $franchise_id;
	
	$package_info = geodir_post_package_info( array(), $post );
	$package_id = isset( $package_info->pid ) ? $package_info->pid : 0;
	$post_fields = geodir_franchise_fields_array( $package_id, $post_type );
	
	if ( geodir_franchise_check( $franchise_id ) ) {
		$allowed_to_add = geodir_franchise_allowed_to_add_more( $franchise_id );
		if ( empty($_GET['post']) && $allowed_to_add !== 'unlimited' && ( $franchise_limit = (int)geodir_franchise_get_franchise_limit( $franchise_id ) ) > 0 ) {
			$return = false;
			if ( (int)$allowed_to_add > 0 ) {
				$limit_notice = wp_sprintf( __( 'You can add <b>%d</b> more new franchise(s) with this package.', 'geodir-franchise' ), $allowed_to_add );
			} else {
				echo '<script type="text/javascript">jQuery(function(){jQuery("form#post #geodir_franchise_admin_field").addClass("gd-hidden");});</script>';
				$limit_notice = wp_sprintf( __( 'You have reached the limit of <b>%d</b> franchise(s) with this package, the listing will <b>NOT</b> be saved as a franchise listing.', 'geodir-franchise' ), $franchise_limit );
				$return = true;
			}
			
			echo '<div class="updated notice notice-success" id="message"><p>' . $limit_notice . '</p></div>';
			if ($return) {
				return;
			}
		}
		
		$locked_fields = geodir_franchise_get_locked_fields( $franchise_id );
		$set_franchise_title = false;
		if ( in_array( 'post_title', $locked_fields ) ) {
			$set_franchise_title = get_post_field( 'post_title', $franchise_id );
			$set_franchise_title = $set_franchise_title != '' ? $set_franchise_title : __( 'Listing Title', 'geodir-franchise' );
		}
		?>
		<input type="hidden" name="franchise" value="<?php echo $franchise_id; ?>" />
		<input type="hidden" id="gd_franchise_nonce" value="<?php echo wp_create_nonce( 'geodir_franchise_nonce_' . $franchise_id ); ?>" />
		<script type="text/javascript">
		jQuery(function(){
			jQuery('form#post').addClass('gd-franchise-form gd-frm-franchise-s');
			gd_franchise_duplicate_image_init('form#post', <?php echo (int)$franchise_id; ?>, true);
		<?php if ( !empty( $locked_fields ) && is_array( $locked_fields ) ) { $locked_fields = "['" . implode( "','", $locked_fields ) . "']";?>
			gd_franchise_lock_fields(<?php echo $locked_fields; ?>, 'form#post', true);
			<?php if ( $set_franchise_title != '' ) { ?>
			jQuery('[name="post_title"]', 'form#post').val("<?php echo esc_attr( $set_franchise_title ) ;?>");
			<?php } ?>
		<?php } ?>
		<?php if ( isset( $_REQUEST['franchise'] ) && (int)$_REQUEST['franchise'] == $franchise_id ) { ?>
		jQuery('#geodir_post_package_setting [name="package_id"]').each(function(){
			var onclick = jQuery(this).attr('onclick');
			var quote = onclick[onclick.length - 1];
			onclick = onclick.substring(0, onclick.length - 1) + "&franchise=<?php echo $franchise_id;?>" + quote;
			jQuery(this).attr('onclick', onclick);
		});
		<?php } ?>
		});
		</script>
		<?php
		return;
	}
		
	$franchise_lock = array();
	if ( $post_id > 0 ) {
		$is_franchise = get_post_meta( $post_id, 'gd_is_franchise', true );
		$franchise_lock = $is_franchise ? geodir_franchise_get_locked_fields( $post_id ) : array();
		$franchise_lock = is_array( $franchise_lock ) ? $franchise_lock : array();
	}
	
	$add_franchise_link_text = __( 'Add Franchise', 'geodir-franchise' );
	/**
	 * Filter the add franchise link label.
	 *
	 * @since 1.0.0
	 *
	 * @param string $add_franchise_link_text Add franchise link label.
	 * @param string $post_type The post type.
	 */
	$add_franchise_link_text = apply_filters( 'geodir_franchise_add_franchise_link_text', $add_franchise_link_text, $post_type );
	
	$style_fields = !$is_franchise ? 'style="display:none"' : '';
	?>
	<div class="misc-franchise-section">
		<?php if ( $is_franchise ) { ?>
		<script type="text/javascript">
		jQuery(function(){
			jQuery('form#post').addClass('gd-frm-franchise-m');
			jQuery('#wpbody-content > .wrap > h1:first a.page-title-action').after('<?php echo ' <a href="' . esc_url( admin_url( 'post-new.php?post_type=' . $post_type . '&franchise=' . $post_id ) ) . '" class="page-title-action">' . esc_html( $add_franchise_link_text ) . '</a>';?>');
		});
		</script>
		<?php } ?>
		<h4 style="display:inline;"><?php _e( 'Is Franchise:', 'geodir-franchise' );?></h4>
		<input type="radio" value="1" id="gd_is_franchise_yes" name="gd_is_franchise" class="gd-checkbox" <?php checked( (int)$is_franchise, 1 ); ?> /><?php _e( 'Yes', 'geodir-franchise' );?>&nbsp;&nbsp;<input type="radio" value="0" id="gd_is_franchise_no" name="gd_is_franchise" class="gd-checkbox" <?php checked( (int)$is_franchise, 0 ); ?> /><?php _e( 'No', 'geodir-franchise' );?>           
	</div>
	<div class="misc-franchise-section franchise-fields-row" <?php echo $style_fields;?>>
		<h4 style="display:inline;"><?php _e( 'Lock franchise fields:', 'geodir-franchise' );?></h4>
		<div class="geodir_multiselect_list">
			<select id="gd_franchise_lock" name="gd_franchise_lock[]" class="gd_franchise_lock_chosen chosen_select" option-ajaxchosen="false"data-placeholder="<?php _e( 'Select Fields', 'geodir-franchise' );?>" multiple="multiple" field_type="multiselect">
				<?php if( !empty( $post_fields ) ) { foreach ( $post_fields as $name => $label ) { if ( empty( $name ) ) { continue; } ?>
				<option value="<?php echo $name; ?>" <?php selected( in_array( $name, $franchise_lock ) , true ); ?>><?php echo $label; ?></option>
				<?php } } ?>
			</select>
		</div>           
	</div>
	<?php
}

/**
 * Display Add Franchise link in user dashboard links section.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_add_franchise_link() {
	global $post;
	if ( !( !empty( $post ) &&  geodir_is_page( 'detail' ) ) ) {
		return;
	}
	
	$is_current_user_owner = geodir_listing_belong_to_current_user();
	if ( !( $is_current_user_owner && isset( $post->post_type ) && geodir_franchise_enabled( $post->post_type ) ) ) {
		return;
	}
	
	$post_id = $post->ID;
	
	if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
		$post_id = (int)$_REQUEST['pid'];
	}
		
	if ( (int)get_option('geodir_disable_user_links_section') != 1 && $franchise_id = geodir_franchise_main_franchise_id( $post_id ) ) {
        if ( get_post_status( $franchise_id ) == 'publish' ) {
            $franchise_link = get_permalink( geodir_add_listing_page_id() );
            $franchise_link = geodir_getlink( $franchise_link, array( 'listing_type' => $post->post_type, 'franchise' => $franchise_id ), false );
            
            $add_franchise_link_text = __( 'Add Franchise', 'geodir-franchise' );

            /**
             * Filter the add franchise link label.
             *
             * @since 1.0.0
             *
             * @param string $add_franchise_link_text Add franchise link label.
             * @param string $post->post_type The post type.
             */
            $add_franchise_link_text = apply_filters( 'geodir_franchise_add_franchise_link_text', $add_franchise_link_text, $post->post_type );
        
            $content_html = ' <p class="franchise_link"><i class="fa fa-plus"></i> <a href="' . $franchise_link . '">' . $add_franchise_link_text . '</a></p>';
            
            /**
             * Filter the add franchise link content.
             *
             * @since 1.0.0
             *
             * @param array $content_html Add franchise link content.
             * @param int $post_id The post ID.
             * @param int $franchise_id The parent listing id.
             */
            echo $franchise_link_html = apply_filters( 'geodir_franchise_franchise_link_html', $content_html, $post_id, $franchise_id );
        }
	}
}

/**
 * Remove the claim listing link for franchise listings form user dashboard links section.
 * 
 * No need to show claim listing link for franchise listings. Otherwise it conflicts between the owner of parent and franchise listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_remove_franchise_claim_link() {
	global $post;
	if ( !( !empty( $post ) &&  geodir_is_page( 'detail' ) && isset( $post->post_type ) && geodir_franchise_enabled( $post->post_type ) ) ) {
		return;
	}
	
	$post_ID = isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ? (int)$_REQUEST['pid'] : $post->ID;
	if ( !$post_ID ) {
		return;
	}
	
	$franchise_ID = geodir_franchise_main_franchise_id( $post_ID );
	if ( $franchise_ID && $franchise_ID != $post_ID ) {
		if ( !geodir_get_post_meta( $franchise_ID, 'claimed', true ) || !geodir_get_post_meta( $post_ID, 'claimed', true ) ) {
			remove_action( 'geodir_after_edit_post_link', 'geodir_display_post_claim_link', 2 );
		}
	}
	
	return;
}

/**
 * Check whether franchise enabled for the post and is a main parent listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $pist_id the post id. Default 0.
 * @return bool True if current post is main listing. False if not.
 */
function geodir_franchise_check( $pist_id = 0 ) {
	$pist_id = $pist_id > 0 ? $pist_id : ( isset( $_REQUEST['franchise'] ) ? (int)$_REQUEST['franchise'] : '' );
	
	if ( !$pist_id > 0 ) {
		return false;
	}
	
	$post_type = get_post_type( $pist_id );
	$gd_post = geodir_get_post_info( $pist_id );

	if ( geodir_franchise_enabled( $post_type ) && (int)get_post_meta( $pist_id, 'gd_is_franchise', true ) && geodir_franchise_pkg_is_active( $gd_post ) ) {
		return true;
	}
	
	return false;
}

/**
 * Set the locked field values for the franchise listings.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 * @global bool $preview True if the current page is a preview page. False if not.
 */
function geodir_franchise_set_locked_fields_values() {
	global $post, $preview;
    
	$is_backend_preview = (is_single() && !empty($_REQUEST['post_type']) && !empty($_REQUEST['preview']) && !empty($_REQUEST['p'])) && is_super_admin() ? true : false; // skip if preview from backend
    if (!$preview || $is_backend_preview) {
        return;
    }// bail if not previewing
		
	$franchise_id = isset( $_REQUEST['franchise'] ) ? (int)$_REQUEST['franchise'] : 0;
	if ( geodir_franchise_check( $franchise_id ) ) {
		$_REQUEST = geodir_franchise_grab_franchise_values( $_REQUEST, $franchise_id );
	}
}

/**
 * Grab the locked field values between parent listing and franchise listings.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param array $request The post request info.
 * @param int $franchise_id The parent listing id.
 * @return array The post request info.
 */
function geodir_franchise_grab_franchise_values( $request, $franchise_id ) {
	$franchise_info = (array)geodir_get_post_info( $franchise_id );
	
	if ( empty( $franchise_info ) ) {
		return $request;
	}
	
	$locked_fields = geodir_franchise_get_locked_fields( $franchise_id );
	if ( empty( $locked_fields ) ) {
		return $request;
	}
	
	$franchise_type = $franchise_info['post_type'];
	$taxonomy_category = $franchise_type . 'category';
	
	$package_id = !empty( $franchise_info['package_id'] ) ? $franchise_info['package_id'] : ( !empty( $request['package_id'] ) ? $request['package_id'] : 0 );
	$custom_fields = geodir_post_custom_fields( $package_id, 'all', $franchise_type );
	if ( !empty( $custom_fields ) ) {
		$parse_custom_fields = array();
		foreach ( $custom_fields as $custom_field ) {
			if (empty($custom_field['name']) || $custom_field['type'] == 'fieldset') {
				continue;
			}
			$parse_custom_fields[$custom_field['name']] = $custom_field;
		}
		
		$custom_fields = $parse_custom_fields;
	}
	
	foreach ( $locked_fields as $field ) {
		if (empty($field)) {
			continue;
		}
		$field_value = '';
		switch( $field ) {
			case $taxonomy_category :
				if ( isset( $franchise_info['default_category'] ) ) {
					$request['default_category'] = $franchise_info['default_category'];
				}
				
				$franchise_category = '';
				if ( !empty( $franchise_info[$taxonomy_category] ) ) {
					$categories = explode( ",", $franchise_info[$taxonomy_category] );
					
					if ( !empty( $categories ) ) {
						$franchise_category = array();
						
						foreach ( $categories as $category ) {
							if ( trim( $category ) > 0 ) {
								$franchise_category[] = trim( $category );
							}
						}
					}					
				}
								
				$field_value = array( $taxonomy_category => $franchise_category );
				$field = 'post_category';
			break;
			/*case 'post_images':
				$post_images = '';
				
				$franchise_images = geodir_get_images( $franchise_id );
				
				if ( !empty( $franchise_images ) ) {
					$images = array();
					foreach ( $franchise_images as $image ) {
						$image = (array)$image;
						if ( !empty( $image ) && isset( $image['src'] ) && $image['src'] != '' ) {
							$post_images[] = $image['src'];
						}
					}
					
					$post_images = !empty( $post_images ) ? implode( ",", $post_images ) : '';
				}
				$field_value = $post_images;
			break;*/
			case 'post': {
				$address_fields = array( 'post_address', 'post_country', 'post_region', 'post_city', 'post_zip', 'post_latitude', 'post_longitude', 'post_mapview', 'post_mapzoom' );
				foreach ( $address_fields as $address_field ) {
					$request[$address_field] = isset( $franchise_info[$address_field] ) ? $franchise_info[$address_field] : '';
				}
				
				$field_value = $request['post_address'];
				$field = 'post_address';
			}
			break;
			case 'post_desc':
				$field_value = $franchise_info['post_content'];
			break;
			default:
				if ( isset( $custom_fields[$field] ) ) {
					$field_type = $custom_fields[$field]['type'];
					if ( $field_type == 'multiselect' ) {
						$field_values = '';
						
						if ( isset( $franchise_info[$field] ) ) {
							$field_values = explode( ",", $franchise_info[$field] );
							
							if ( !empty( $field_values ) ) {
								$values = array();
								
								foreach ( $field_values as $value ) {
									if ( trim( $value ) != "" ) {
										$values[] = trim( $value );
									}
								}
								
								$field_values = $values;
							}					
						}
						$field_value = $field_values;
					} else {
						$field_value = $franchise_info[$field];
					}
				} else {
					$field_value = isset( $franchise_info[$field] ) ? $franchise_info[$field] : '';
				}
			break;
		}
		
		$request[$field] = $field_value;
	}
	return $request;
}

/**
 * Get the locked fields for the listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $post_id The post ID.
 * @param bool $parsed Parse the locked fields array.
 * @return array Array of locked fields.
 */
function geodir_franchise_get_locked_fields( $post_id, $parsed = false ) {
	$locked_fields = '';
	
	if ( geodir_franchise_check( $post_id ) ) {
		$locked_fields = get_post_meta( $post_id, 'gd_franchise_lock', true );
		
		if ( !empty( $locked_fields ) && $parsed ) {
			$post_type = get_post_type( $post_id );
			$taxonomy_category = $post_type . 'category';
			
			if ( in_array( $taxonomy_category, $locked_fields ) ) {
				$locked_fields = array_merge( $locked_fields, array( 'default_category' ) );
			}
			
			if ( in_array( 'post_desc', $locked_fields ) ) {
				$locked_fields = array_merge( $locked_fields, array( 'post_content' ) );
				
				if ( ( $key = array_search( 'post_desc', $locked_fields ) ) !== false ) {
					unset( $locked_fields[$key] );
				}
			}
			
			if ( in_array( 'post', $locked_fields ) ) {
				if ( ( $key = array_search( 'post', $locked_fields ) ) !== false ) {
					unset( $locked_fields[$key] );
				}
				$address_fields = array( 'post_address', 'post_country', 'post_region', 'post_city', 'post_zip', 'post_latitude', 'post_longitude', 'post_mapview', 'post_mapzoom', 'post_locations', 'post_location_id', 'post_neighbourhood', 'post_latlng' );
				
				$locked_fields = array_merge( $locked_fields, $address_fields );
			}
		} else {
			if ( in_array( 'post_content', $locked_fields ) ) {
				$locked_fields = array_merge( $locked_fields, array( 'post_desc' ) );
				
				if ( ( $key = array_search( 'post_content', $locked_fields ) ) !== false ) {
					unset( $locked_fields[$key] );
				}
			}
		}
		/**
		 * Filter the locked fields array.
		 *
		 * @since 1.0.0
		 * @param array $franchise_tabs The array of tabs to display.
		 */
		$locked_fields = apply_filters( 'geodir_franchise_get_locked_fields', $locked_fields, $post_id );
	}
	
	return $locked_fields;
}

/**
 * Get the list of franchise listings of the post.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $post_id The post ID.
 * @param bool $select_options True to get list in post_id => post_title format for select menu options. False if not. Default false.
 * @param bool $owner True if parent listing included in list. False if not. Default false.
 * @param array $post_status Post statuses array to filter listings result. Default empty.
 * @return array The franchise listings.
 */
function geodir_franchise_post_franchises( $post_id, $select_options = false, $owner = false, $post_status = '' ) {
    global $wpdb, $plugin_prefix;

    $franchises = array();

    if ( geodir_franchise_check( $post_id ) ) {
        $listing_type = get_post_type( $post_id );
        $listing_table = $plugin_prefix . $listing_type . '_detail';
        
        $post_status_where = " AND post_status NOT IN('draft', 'auto-draft', 'trash')";
        if ( $post_status !== '' && !empty($post_status) ) {
            if ( is_array( $post_status ) ) {
                $post_status_where = " AND post_status IN ('" . implode( "','", $post_status ) . "')";
            } else {
                $post_status_where = " AND post_status = '" . $post_status . "'";
            }
        }
        
        $query = $wpdb->prepare( "SELECT post_id, post_title FROM " . $listing_table . " WHERE franchise = %d " . $post_status_where, array( $post_id ) );
        $rows = $wpdb->get_results( $query );
        
        if ( !empty( $rows ) ) {
            foreach ( $rows as $row ) {
                if ($owner && !geodir_listing_belong_to_current_user($row->post_id)) { // Not allowed to manage franchise
                    continue;
                }
                
                if ( $select_options ) {
                    $franchises[$row->post_id] = stripslashes_deep( $row->post_title );
                } else {
                    $franchises[] = $row->post_id;
                }
            }
        }

        /**
         * Filter the list of franchise listings of the post.
         *
         * @since 1.0.0
         * @param array $franchises The franchise listings.
         * @param int $post_id The post ID.
         * @param bool $select_options True to get list in post_id => post_title format for select menu options. False if not. Default false.
         */
        $franchises = apply_filters( 'geodir_franchise_post_franchises', $franchises, $post_id, $select_options );
    }

    return $franchises;
}

/**
 * Merge the locked fields value between parent listing and franchise listings.
 *
 * If parent listing updated then locked fields values also updated for franchise listings.
 * If franchise listing updated then locked fields values updated with parent listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $post_id The parent listing id.
 * @param array $sub_franchises Array of franchise listing ids.
 */
function geodir_franchise_merge_locked_fields( $post_id, $sub_franchises = array() ) {
	global $wpdb;

	if ( geodir_franchise_check( $post_id ) ) {
		$post_type = get_post_type( $post_id );
		$locked_fields = geodir_franchise_get_locked_fields( $post_id );
		$sub_franchises = !empty( $sub_franchises ) ? $sub_franchises : geodir_franchise_post_franchises( $post_id );

		$is_claim_active = geodir_franchise_is_claim_active( $post_type );
		if ( $is_claim_active && empty( $locked_fields ) ) {
			$locked_fields = (array)$locked_fields;
			$locked_fields[] = 'claimed';
		}
		
		if ( !empty( $sub_franchises ) ) {
			$franchise_info = (array)geodir_get_post_info( $post_id );

			if ( empty( $franchise_info ) ) {
				return false;
			}

			$locked_fields = (array)geodir_franchise_get_locked_fields( $post_id, true );
			if ( $is_claim_active && ( empty( $locked_fields ) || !in_array( 'claimed', $locked_fields ) ) ) {
				$locked_fields[] = 'claimed';
			}
			
			
			$franchise_alive_days = geodir_get_post_meta( $post_id, 'alive_days', true );
			$franchise_expire_date = geodir_get_post_meta( $post_id, 'expire_date', true );
			
			$franchise_type = $franchise_info['post_type'];
			$taxonomy_category = $franchise_type . 'category';
			$taxonomy_tags = $franchise_type . '_tags';
			
			$franchise_terms = (array)wp_get_post_terms( $post_id, $taxonomy_category, array( 'fields' => 'ids' ) );
			$franchise_tags = (array)wp_get_post_terms( $post_id, $taxonomy_tags, array( 'fields' => 'ids' ) );
					
			foreach ( $sub_franchises as $sub_franchise_id ) {
				$sub_franchise_info = geodir_get_post_info( $sub_franchise_id );
				// Check price package plan.
				if ( !geodir_franchise_pkg_is_active( $sub_franchise_info ) ) {
					continue;
				}

				$sub_franchise_info = (array)$sub_franchise_info;

				if(!empty( $locked_fields )){

					$merge_data = array();
					foreach ( $locked_fields as $locked_field ) {
						if ( !empty( $locked_field ) && isset( $franchise_info[$locked_field] ) ) {
							if ( $locked_field == $taxonomy_category ) {
								$sub_franchise_category = trim( $sub_franchise_info[$locked_field] );
								$sub_franchise_category = rtrim( $sub_franchise_category, "," );
								$sub_franchise_category = ltrim( $sub_franchise_category, "," );
								$sub_franchise_info[$locked_field] = trim( $sub_franchise_category );

								$franchise_category = trim( $franchise_info[$locked_field] );
								$franchise_category = rtrim( $franchise_category, "," );
								$franchise_category = ltrim( $franchise_category, "," );
								$franchise_info[$locked_field] = trim( $franchise_category );

								$sub_franchise_terms = (array)wp_get_post_terms( $sub_franchise_id, $taxonomy_category, array( 'fields' => 'ids' ) );
								$terms_diff = array_diff($franchise_terms, $sub_franchise_terms);

								if (!empty($terms_diff)) {
									$merge_data[$locked_field] = $franchise_info[$locked_field];
								}
							}

							if ($locked_field == $taxonomy_tags) {
								$sub_franchise_tags = (array)wp_get_post_terms( $sub_franchise_id, $taxonomy_tags, array( 'fields' => 'ids' ) );
								$tags_diff = array_diff($franchise_tags, $sub_franchise_tags);

								if (!empty($tags_diff)) {
									$merge_data[$locked_field] = $franchise_info[$locked_field];
								}
							}

							if ( !isset( $sub_franchise_info[$locked_field] ) || ( isset( $sub_franchise_info[$locked_field] ) && $sub_franchise_info[$locked_field] != $franchise_info[$locked_field] ) ) {
								$merge_data[$locked_field] = $franchise_info[$locked_field];
							}
						}
					}

					if ( !empty( $merge_data ) ) {
						$merge_data = array_map( 'addslashes_gpc', $merge_data );

						/**
						 * Filter data to be merged between parent listing and franchise listing.
						 *
						 * @since 1.0.0
						 *
						 * @param array $merge_data Merged data array.
						 */
						$merge_data = apply_filters( 'geodir_franchise_merge_data', $merge_data );

						// Save post info
						geodir_save_post_info( $sub_franchise_id, $merge_data );

						if ( !empty( $merge_data[$taxonomy_category] ) || !empty( $merge_data['default_category'] ) ) {
							$post_category = (array)wp_get_object_terms( $post_id, $taxonomy_category, array('fields' => 'ids') );

							if ( !empty( $post_category ) ) {
								/*
								 * Hierarchical taxonomies must always pass IDs rather than names so that
								 * children with the same names but different parents aren't confused.
								 */
								if ( is_taxonomy_hierarchical( $taxonomy_category ) ) {
									$post_category = array_unique( array_map( 'intval', $post_category ) );
								}
								// Set post terms
								wp_set_object_terms( $sub_franchise_id, $post_category, $taxonomy_category );

								// Set post category structure
								geodir_set_postcat_structure( $sub_franchise_id, $taxonomy_category, $franchise_info['default_category'], '' );
							}
						}

						if ( !empty( $merge_data['post_tags'] ) ) {
							wp_set_object_terms( $sub_franchise_id, $merge_data['post_tags'], $taxonomy_tags );
						}

						// Update post title & post content in wp_posts
						if ( !empty( $merge_data['post_title'] ) || !empty( $merge_data['post_content'] ) ) {
							$set_fields = array();
							$set_values = array();
							if ( !empty( $merge_data['post_title'] ) ) {
								$set_fields[] = 'post_title = %s';
								$set_values[] = $merge_data['post_title'];
							}

							if ( !empty( $merge_data['post_content'] ) ) {
								$set_fields[] = 'post_content = %s';
								$set_values[] = $merge_data['post_content'];
							}

							$set_values[] = $sub_franchise_id;

							$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET " . implode( ", ", $set_fields ) . " WHERE ID = %d", $set_values ) );
						}
					}
				}
				geodir_save_post_meta( $sub_franchise_id, 'alive_days', $franchise_alive_days );
				geodir_save_post_meta( $sub_franchise_id, 'expire_date', $franchise_expire_date );
			}
		}
	}
}

/**
 * Add the link in post row actions array to display Add Franchise link in back-end listing page.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param array $actions An array of row action links.
 * @param WP_Post $post The post object.
 * @return array An array of row action links.
 */
function geodir_franchise_post_row_actions( $actions, $post ) {
	if ( geodir_franchise_check( $post->ID )  ) {
		$actions['franchise'] = '<a href="' . esc_url( admin_url( 'post-new.php?post_type=' . $post->post_type . '&franchise=' . $post->ID ) ) . '" title="' . esc_attr__( 'Add new franchise', 'geodir-franchise' ) . '">' . __( 'Add Franchise', 'geodir-franchise' ) . '</a>';
	}
	return $actions;
}

/**
 * Updates custom table when post inline updated.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $post_ID The post ID.
 * @param object $post_after Post object after the update.
 * @param object $post_before Post object before the update.
 */
function geodir_franchise_post_updated( $post_ID, $post_after, $post_before ) {
	global $wpdb;
	
	$post_type = get_post_type( $post_ID );
	
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'inline-save' ) {
		if ( $post_type != '' && geodir_franchise_enabled( $post_type ) && !wp_is_post_revision( $post_ID ) ) {		
			if ( geodir_franchise_check( $post_ID ) ) {
				geodir_franchise_merge_locked_fields( $post_ID );
			} else {
				$franchise = (int)geodir_get_post_meta( $post_ID, 'franchise', true );
				
				if ( geodir_franchise_check( $franchise ) ) {
					geodir_franchise_merge_locked_fields( (int)$franchise, array( $post_ID ) );
				}
			}
		}
	}
}

/**
 * Add the enable franchise field in back-end price package form.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param array $priceinfo The current price package info.
 */
function geodir_franchise_payment_package_extra_fields( $priceinfo = array() ) {
	$post_type = isset( $priceinfo->post_type ) ? $priceinfo->post_type : '';
	
	$enable_franchise = isset( $priceinfo->enable_franchise ) && (int)$priceinfo->enable_franchise == 1 ? 1 : 0;
	$franchise_cost = $enable_franchise && isset( $priceinfo->franchise_cost ) && $priceinfo->franchise_cost != '' ? number_format( (float)$priceinfo->franchise_cost, 2 ) : '';
	$franchise_limit = $enable_franchise && isset( $priceinfo->franchise_limit ) && (int)$priceinfo->franchise_limit > 0 ? (int)$priceinfo->franchise_limit : '';
	
	$currency = stripslashes_deep( get_option( 'geodir_currencysym', '$' ) );
	$currency = !$currency ? $currency : '$';
	?>
	<tr valign="top" class="single_select_page">
		<th scope="row" class="titledesc"><?php _e( 'Enable Franchise', 'geodir-franchise' ); ?></th>
		<td class="forminp">
			<?php if ( $post_type != '' && geodir_franchise_enabled( $post_type ) ) { ?>
			<div class="gtd-formfield">
				<select name="geodir_enable_franchise" style="min-width:100px;">
					<option value="0" <?php selected( $enable_franchise, 0 ); ?>><?php _e( 'No', 'geodir-franchise' ); ?></option>
					<option value="1" <?php selected( $enable_franchise, 1 ); ?>><?php _e( 'Yes', 'geodir-franchise' ); ?></option>
				</select>
			</div>
			<span class="description">&nbsp;<?php _e( 'Enable franchise feature.', 'geodir-franchise' ); ?></span>
			<?php } else { ?>
			<div class="gtd-formfield"><?php echo ( $post_type != '' ? __( 'You must have franchise feature enabled for post type in general settings.', 'geodir-franchise' ) : __( 'You can only manage franchise once price saved.', 'geodir-franchise' ) ); ?></div>
			<?php } ?>
		</td>
	</tr>
	<?php if ( $post_type != '' && geodir_franchise_enabled( $post_type ) ) { ?>
	<tr valign="top" class="single_select_page">
		<th scope="row" class="titledesc"><?php echo wp_sprintf( __( 'Franchise Cost ( %s )', 'geodir-franchise' ), $currency ); ?></th>
		<td class="forminp">
			<div class="gtd-formfield">
				<input type="text" value="<?php echo $franchise_cost; ?>" name="gd_franchise_cost" style="width:100px;" placeholder="<?php esc_attr_e('5.00', 'geodir-franchise' ); ?>">
			</div>
			<span class="description">&nbsp;<?php _e( 'Franchise price will be charged to each franchise. Ex: 5.00', 'geodir-franchise' ); ?></span>
		</td>
	</tr>
	<tr valign="top" class="single_select_page">
		<th scope="row" class="titledesc"><?php echo __( 'Franchises Limit', 'geodir-franchise' ); ?></th>
		<td class="forminp">
			<div class="gtd-formfield">
				<input type="number" name="gd_franchise_limit" id="gd_franchise_limit" placeholder="<?php esc_attr_e('Unlimited', 'geodir-franchise');?>" step="1" min="0" style="min-width:200px;" value="<?php echo $franchise_limit;?>" />
			</div>
			<span class="description">&nbsp;<?php _e( 'Limit the number of franchises that can be added under main listing for this price package. Leave blank or add 0 (zero) for unlimited.', 'geodir-franchise' ); ?></span>
		</td>
	</tr>
	<?php } ?>
	<?php
}

/**
 * Check whether payment manager addon installed and active.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @return bool True if payment manager installed and active. False if not.
 */
function geodir_franchise_is_payment_active() {
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	
	if ( is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php') ) {
		return true;
	}
	
	return false;
}

/**
 * Check whether location manager addon installed and active.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @return bool True if location manager installed and active. False if not.
 */
function geodir_franchise_is_location_active() {
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	
	if ( is_plugin_active( 'geodir_location_manager/geodir_location_manager.php' ) ) {
		return true;
	}
	
	return false;
}

/**
 * Check whether claim manager addon installed and active.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @return bool True if claim manager installed and active. False if not.
 */
function geodir_franchise_is_claim_active( $post_type = '' ) {
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	
	$return = false;
	if ( defined( 'GEODIR_CLAIM_TABLE' ) && is_plugin_active( 'geodir_claim_listing/geodir_claim_listing.php') ) {
		$return = true;
		
		if ( $post_type != '' && !in_array( $post_type, (array)get_option( 'geodir_post_types_claim_listing' ) ) ) {
			$return = false;
		}
	}
	
	return $return;
}

/**
 * Check whether price package has franchise enabled.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param object $post The current post object.
 * @param array $package_info Package info. Default empty.
 * @return bool True if price package has franchise enabled. False if not.
 */
function geodir_franchise_pkg_is_active( $post, $package_info = array() ) {
	$package_info = geodir_post_package_info( $package_info, $post );
	
	$is_activate = true;
	
	if ( geodir_franchise_is_payment_active() ) {
		$is_activate = false;
		
		if ( !empty( $package_info ) && isset( $package_info->enable_franchise ) && (int)$package_info->enable_franchise == 1 ) {
			$is_activate = true;
		};
	}

	/**
	 * Filter the value of whether price package has franchise enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $is_activate True if price package has franchise enabled. False if not.
	 * @param object $post The current post object.
	 * @param array $package_info Package info.
	 */
	return apply_filters( 'geodir_franchise_package_is_activate', $is_activate, $post, $package_info );
}

/**
 * Save the package enable franchise value on package saved. 
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $package_id The package id.
 * @return bool True if enable franchise value updated. False if not.
 */
function geodir_franchise_after_save_package( $package_id ) {
	global $wpdb;

	if ( $package_id > 0 && defined( 'GEODIR_PRICE_TABLE' ) ) {
		$request = $_POST;
		
		if ( isset( $request['gd_add_price'] ) && $request['gd_add_price'] == 'addprice' && isset( $request['gd_posting_type'] ) && isset( $request['geodir_enable_franchise'] ) ) {
			$enable_franchise = geodir_franchise_enabled( $request['gd_posting_type'] ) ? (int)$request['geodir_enable_franchise'] : 0;
			$franchise_cost = $enable_franchise && isset( $request['gd_franchise_cost'] ) && (float)$request['gd_franchise_cost'] > 0 ? number_format( (float)$request['gd_franchise_cost'], 2 ) : '';
			$franchise_limit = $enable_franchise && isset( $request['gd_franchise_limit'] ) ? absint($request['gd_franchise_limit']) : 0;
			
			$wpdb->query( $wpdb->prepare( "UPDATE `" . GEODIR_PRICE_TABLE . "` SET `enable_franchise` = %d, `franchise_cost` = %s, `franchise_limit` = %s WHERE `pid` = %d",array( $enable_franchise, $franchise_cost, $franchise_limit, (int)$package_id ) ) );
			
			return true;
		}
	}
	return false;
}

/**
 * Sets the franchise filter parameters.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param WP_Query $query The WP_Query instance.
 * @return WP_Query instance.
 */
function geodir_franchise_pre_get_posts( $query ) {
	if ( is_admin() && ( !defined('DOING_AJAX' ) || ( defined('DOING_AJAX') && !DOING_AJAX ) ) ) {
		return $query;
	}

	$post_types = geodir_get_posttypes();
    // function geodir_get_current_posttype wont work right here because wp_query is not set yet.
    $gd_post_type = (isset($query->query_vars['post_type'])) ? $query->query_vars['post_type'] :'';

	if ( !empty( $_REQUEST['franchise'] ) && in_array( $gd_post_type, $post_types ) && geodir_is_geodir_page() && geodir_is_page( 'listing' ) && $query->is_main_query() ) {
		add_filter( 'posts_where', 'geodir_franchise_search_where', 10 );
	} else {
        remove_filter( 'posts_where', 'geodir_franchise_search_where', 10 );
    }
	
	return $query;
}

/**
 * Filter the where query clause for franchise listings.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 * @since 1.0.3 Option added to hide main listing.
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $where The where query string.
 * @return string Modified where query string.
 */
function geodir_franchise_search_where( $where ) { 
	global $plugin_prefix;
	
	if ( is_admin() && ( !defined('DOING_AJAX' ) || ( defined('DOING_AJAX') && !DOING_AJAX ) ) ) {
		return $where;
	}

	$post_types = geodir_get_posttypes();
	$gd_post_type = geodir_get_current_posttype();
	
	if ( !empty( $_REQUEST['franchise'] ) && in_array( $gd_post_type, $post_types ) && geodir_is_geodir_page() && geodir_is_page( 'listing' ) && geodir_franchise_enabled( $gd_post_type ) ) {
		$table = $plugin_prefix . $gd_post_type . '_detail';
		
		$franchise = explode( '-', $_REQUEST['franchise'], 2 );
		$franchise_info = !empty( $franchise ) && isset( $franchise[0] ) ? get_post( (int)$franchise[0] ) : array();
		$franchise_id = !empty( $franchise_info ) && isset( $franchise_info->ID ) ? (int)$franchise_info->ID : '-1';
		
		if ( geodir_franchise_check( $franchise_id ) ) {
			$where .= " AND (`" . $table . "`.`franchise` = " . (int)$franchise_id . "";
			if ( !get_option( 'geodir_franchise_hide_main_all' ) ) {
				$where .= " OR `" . $table . "`.`post_id` = " . (int)$franchise_id . "";
			}
			$where .= ")";
		} else {
			$where .= " AND `" . $table . "`.`franchise` = '-1'";
		}
	}
	
	return $where;
}

/**
 * Franchise listings tab on listings detail page.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global int $character_count The excerpt length.
 * @global string $gridview_columns The girdview style of the listings.
 * @global object $gd_query_args The GeoDirectory query object.
 *
 * @param array $tabs_arr Tabs array.
 * @return array Modified tabs array.
 */
function geodir_detail_page_my_franchises_tab( $tabs_arr ) {
	global $post, $wpdb, $plugin_prefix, $character_count, $gridview_columns, $gd_query_args;
	
	$post_type = geodir_get_current_posttype();
	$gd_postypes = geodir_get_posttypes();
		
	if ( !empty( $post ) && !empty( $post->ID ) && !empty( $tabs_arr ) && in_array( $post_type, $gd_postypes ) && geodir_is_page( 'detail' ) && get_option( 'geodir_franchise_post_enable_tab' ) ) {			
		$franchise_id = geodir_franchise_main_franchise_id( $post->ID );
		
		if ( $franchise_id ) {
			$old_character_count = $character_count;
			$old_gridview_columns = $gridview_columns;
			$old_gd_query_args = $gd_query_args;
			
			$post_number = get_option( 'geodir_franchise_post_count', '5' );
			$list_sort = get_option( 'geodir_franchise_post_sortby', 'latest' );
			$gridview_columns = get_option( 'geodir_franchise_post_listing_view', 'gridview_onehalf' );
			$gridview_columns = $gridview_columns == 'listview' ? '' : $gridview_columns;
			$character_count = get_option( 'geodir_franchise_post_excerpt', '20' );
			$location_filter = get_option( 'geodir_franchise_post_location_filter', 0 ) ? true : false;
							
			$gd_query_args = array(
				'franchise_id' => $franchise_id,
				'franchise_post_id' => $post->ID,
				'posts_per_page' => $post_number,
				'is_geodir_loop' => true,
				'post_type' => $post_type,
				'order_by' => $list_sort,
				'gd_location' => $location_filter
			);
			
			$franchise_listings = geodir_get_widget_listings( $gd_query_args );
			
			if ( !empty( $franchise_listings ) ) {
				$html = geodir_franchise_tab_content( $franchise_listings );
				if ( $html ) {
					wp_enqueue_style( 'gd_franchise_style', GEODIR_FRANCHISE_PLUGIN_URL . '/css/gd-franchise.css', array(), GEODIR_FRANCHISE_VERSION );
					
					$franchises_tab_text = __( 'Franchises', 'geodir-franchise' );
					/**
					 * Filter franchises tab label on detail page.
					 *
					 * @since 1.0.0
					 *
					 * @param string $franchises_tab_text Franchises tab label.
					 * @param string $post_type The post type.
					 */
					$franchises_tab_text = apply_filters( 'geodir_franchise_franchises_tab_text', $franchises_tab_text, $post_type );
					
					$post->gd_tab_franchises = '';

					/**
					 * Filter for whether franchise tab display or not on detail page tabs.
					 *
					 * @since 1.0.0
					 *
					 * @param string true Display tab if True. Hide tab if False.
					 * @param string 'gd_tab_franchises' detail page tab id.
					 */
					$tabs_arr['gd_tab_franchises'] = array( 
														'heading_text' => $franchises_tab_text,
														'is_active_tab' => false,
														'is_display' => apply_filters( 'geodir_detail_page_tab_is_display', true, 'gd_tab_franchises' ),
														'tab_content' => $html
													);
				}
			}
			
			global $gd_query_args, $character_count, $gridview_columns;
			$gd_query_args = $old_gd_query_args;
			$character_count = $old_character_count;
			$gridview_columns = $old_gridview_columns;
		}
	}
	return $tabs_arr;
}

/**
 * Get the content of detail page franchises tab content.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 * @global string $gridview_columns The girdview style of the listings.
 * @global int $character_count The excerpt length.
 * @global object $widget_listings The widget listings object.
 * @global array $map_jason Map data in json format.
 * @global array $map_canvas_arr Array of map canvas data.
 * @global string $gridview_columns_widget The girdview style of the listings for widget.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param array $rows The listings object.
 * @return string Franchises tab content.
 */
function geodir_franchise_tab_content( $rows = array() ) {
	global $post, $gridview_columns, $character_count, $widget_listings, $gd_session;
	
	if( empty ( $rows ) ) {
		return NULL;
	}
	
	$widget_listings = $rows;
	
	if ( !$character_count ) {
		$character_count = 20;
	}
	
	/** This filter is documented in geodirectory-functions/general_functions.php */
	$template = apply_filters("geodir_template_part-widget-listing-listview", geodir_locate_template('widget-listing-listview'));
	
	global $post, $map_jason, $map_canvas_arr, $gridview_columns_widget;
    $current_post = $post;
    $current_map_jason = $map_jason;
    $current_map_canvas_arr = $map_canvas_arr;
    $current_grid_view = $gridview_columns_widget;
    
	$gridview_columns_widget = $gridview_columns;
    
	$gd_listing_view_set = $gd_session->get( 'gd_listing_view' ) ? true : false;
	$gd_listing_view_old = '';
    if ( $gd_listing_view_set ) {
		$gd_listing_view_old = $gd_session->get( 'gd_listing_view' );
		$gd_session->un_set( 'gd_listing_view' );
	}
    $geodir_is_widget_listing = true;
	
	$franchise_id = geodir_franchise_main_franchise_id( $post->ID );
	$franchise_title = $post->post_title;
	
	if ($franchise_id != $post->ID ) {
		$franchise_title = get_the_title( $franchise_id );
	}
	
	$params = array();
	$params['franchise'] = $franchise_id . '-' . $franchise_title;
	$view_all_url = geodir_getlink( get_post_type_archive_link( $post->post_type ), $params );

	/**
	 * Filter view all franchise listings link.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_all_url View all franchises link.
	 * @param int $franchise_id The parent listing ID.
	 */
	$view_all_url = apply_filters( 'geodir_franchise_all_franchises_link', $view_all_url, $franchise_id );
	
	$view_all_link_text = __( 'View all franchises', 'geodir-franchise' );

	/**
	 * Filter view all franchise listings link label.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_all_link_text View all franchises link label.
	 * @param string $post->post_type The post type.
	 */
	$view_all_link_text = apply_filters( 'geodir_franchise_all_franchises_link_text', $view_all_link_text, $post->post_type );
	
	ob_start();
	?>
	<div class="geodir_locations geodir_location_listing">
		<?php if ( get_option( 'geodir_franchise_post_enable_link' ) ) { ?>
		<a class="geodir-viewall geodir-viewall-link clearfix" href="<?php echo esc_url( $view_all_url );?>" title="<?php echo esc_attr($view_all_link_text);?>"><?php echo esc_attr($view_all_link_text);?></a>
		<?php }	
	/**
	 * Includes the template for the listing listview.
	 */
	include($template);
	?>
	</div>
	<?php
	
	$content = ob_get_clean();

    $geodir_is_widget_listing = false;

    $GLOBALS['post'] = $current_post;
	if (!empty($current_post)) {
    	setup_postdata($current_post);
	}
	if ($gd_listing_view_set) { // Set back previous value
		$gd_session->set( 'gd_listing_view', $gd_listing_view_old );
	} else {
		$gd_session->un_set( 'gd_listing_view' );
	}
    $map_jason = $current_map_jason;
    $map_canvas_arr = $current_map_canvas_arr;
    $gridview_columns_widget = $current_grid_view;
	
	return $content;
}

/**
 * Filter the where query clause for franchise widget listings.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 * @since 1.0.3 Option added to hide main/current viewing listing from tab.
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global object $gd_query_args_widgets The GeoDirectory query object.
 *
 * @param string $where The where query string.
 * @param string $post_type Thepost type.
 * @return string Modified where query string.
 */
function geodir_franchise_widget_listings_where( $where, $post_type ) {
	global $plugin_prefix, $gd_query_args_widgets;
	
	$franchise_id = !empty( $gd_query_args_widgets ) && isset( $gd_query_args_widgets['franchise_id'] ) ? $gd_query_args_widgets['franchise_id'] : '';
	if ( (int)$franchise_id && geodir_franchise_check( $franchise_id ) ) {
		$table = $plugin_prefix . $post_type . '_detail';
		
		$franchise_post_id = isset( $gd_query_args_widgets['franchise_post_id'] ) ? $gd_query_args_widgets['franchise_post_id'] : '';
		
		$where .= " AND (`" . $table . "`.`franchise` = " . (int)$franchise_id;
		$franchise_where = '';
		if ( $franchise_post_id > 0 ) {
            if ($franchise_post_id != $franchise_id ) {
                if ( !get_option( 'geodir_franchise_hide_main' ) ) {
                    $where .= " OR `" . $table . "`.`post_id` = " . (int)$franchise_id; // Show main listing in FRANCHISES listing detail page franchises tab.
                }
                
                if ( get_option( 'geodir_franchise_hide_viewing' ) ) {
                    $franchise_where .= " AND `" . $table . "`.`post_id` != " . (int)$franchise_post_id;
                }
            } else {
                if ( get_option( 'geodir_franchise_show_main' ) ) {
                    $where .= " OR `" . $table . "`.`post_id` = " . (int)$franchise_id; // Show main listing in MAIN listing detail page franchises tab.
                }
            }
		}
		
		$where .= ")";
        $where .= $franchise_where;
	}
	
	return $where;
}

/**
 * Checks whether payment allowed for invoice again.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param bool $allow True if payment allowed for invoice again. False if not.
 * @param object $invoice_info The invoice object data.
 * @return bool True if payment allowed for invoice again. False if not.
 */
function geodir_franchise_allow_pay_for_invoice( $allow, $invoice_info ) {
	if ( !empty( $invoice_info->invoice_type ) && $invoice_info->invoice_type == 'add_franchise' ) {
		if ( in_array( $invoice_info->status, array( 'failed', 'pending' ) ) ) {
			$allow = true;	
		}
	}
	return $allow;
}

/**
 * Handle the callback for add franchise invoices when payment invoice status has changed. 
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $invoice_id The payment invoice id.
 * @param string $new_status New status of invoice.
 * @param string $old_status Old status of invoice. Default 'pending'.
 * @param bool $subscription True if invoice has subscription payment. False if not.
 * @return bool True if valid callback. False if not.
 */
function geodir_franchise_invoice_callback_add_franchise( $invoice_id, $new_status, $old_status = 'pending', $subscription = false ) {
	global $wpdb;
	
	$invoice_info = geodir_get_invoice( $invoice_id );
	if ( !( !empty( $invoice_info ) && $new_status != $old_status ) && !empty($invoice_info->invoice_data) ) {
		return false;
	}
	
	$invoice_data = maybe_unserialize( $invoice_info->invoice_data );	
	$franchise_id = isset( $invoice_data['franchise_id'] ) ? $invoice_data['franchise_id'] : NULL;
	$franchises = isset( $invoice_data['franchises'] ) ? $invoice_data['franchises'] : NULL;
	
	if (!(!empty($franchise_id) && geodir_franchise_check($franchise_id)) || empty($franchises)) {
		return false;
	}
	
	$franchise_post_info 	= geodir_get_post_info( $franchise_id );
	if ( empty( $franchise_post_info ) ) {
		return false;
	}
	
	$my_franchises = geodir_franchise_post_franchises( $franchise_id, false, false, array('draft', 'pending') );
	$package_id = $invoice_info->package_id;
	$author_id	= !empty($invoice_info->user_id) ? $invoice_info->user_id : $franchise_post_info->author_id;

	if ( $new_status == 'confirmed' ) {
		if (!empty($franchises)) {
			foreach ($franchises as $child_post_id) {
				if (in_array($child_post_id, $my_franchises)) {
					$child_post_info = array();
					$child_post_info['package_id'] = $package_id;
					geodir_save_listing_payment( $child_post_id, $child_post_info );
					
					$post_update = array();
					$post_update['ID'] = $child_post_id;
					$post_update['post_status'] = 'publish';
									
					wp_update_post( $post_update );
				}
				
				geodir_franchise_clientEmail($franchise_id, $author_id, 'payment_franchises', $invoice_info);
			}
		}
	} else if ( $new_status == 'pending' ) {
	} else if ( $new_status == 'canceled' ) {
	} else if ( $new_status == 'failed' ) {
	} else if ( $new_status == 'onhold' ) {
	}
	
	return true;
}

/**
 * Send mail notification to listing client when franchises status updated on invoice status has changed.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $post_id The post ID.
 * @param int $user_id The user ID.
 * @param string $message_type The notification message type. Ex: 'payment_franchises'.
 * @param object $invoice_info Payment invoice object. Default empty.
 * @return bool True if notification sent successfully. False if not.
 */
function geodir_franchise_clientEmail($post_id, $user_id, $message_type = '', $invoice_info = array()) {
	$post_info = get_post($post_id);
	if (empty($post_info)) {
		return false;
	}

	/**
	 * Filter the franchise notification message type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message_type The notification message type.
	 * @param int $post_id The post ID.
	 * @param int $user_id The user ID.
	 */
	$message_type = apply_filters('geodir_franchise_client_email_message_type', $message_type, $post_id, $user_id);
	
	$subject = get_option('geodir_franchise_client_email_subject_' . $message_type);
	$message = get_option('geodir_franchise_client_email_message_' . $message_type);
		
	if ($subject == '' || $message == '') {
		return false;
	}
	
	$subject = __( stripslashes_deep($subject), 'geodirectory' );
	$message = __( stripslashes_deep($message), 'geodirectory' );
	
	$author_id = $post_info->post_author;
	
	$user_id = $user_id ? $user_id : $author_id;
	$user_info = get_userdata($user_id);
	if (empty($user_info)) {
		return false;
	}
	
	$user_email = $user_info->user_email;
	$user_name = geodir_get_client_name($user_id);
	
	$site_email = geodir_get_site_email_id();
	$admin_email = get_option( 'admin_email' );
	$site_emailName = get_site_emailName();
	$site_url = trailingslashit(home_url());
	$siteurl_link = '<a href="' . $site_url . '">' . $site_emailName . '</a>';
	
	$listing_title = get_the_title($post_id);
	$listing_url = get_permalink($post_id);
	$listing_link = '<a href="' . $listing_url . '">' . $listing_title . '</a>';
	
	$franchise_listings_links = '';
	if ($message_type == 'payment_franchises' && !empty($invoice_info->invoice_data)) {
		$invoice_data = maybe_unserialize( $invoice_info->invoice_data );
		$franchises = isset( $invoice_data['franchises'] ) ? $invoice_data['franchises'] : NULL;
		
		if (!empty($franchises)) {
			$franchise_listings_links = array();
			
			foreach ($franchises as $child_post_id) {
				$child_post_title = get_the_title($child_post_id);
				$child_post_url = get_permalink($child_post_id);
				
				$franchise_listings_links[] = '<a href="' . $child_post_url . '">' . $child_post_title . '</a>';
			}
			
			$franchise_listings_links = implode(", ", $franchise_listings_links);
		}
	}
		
	$params = array();
	$params['site_name'] = $site_emailName;
	$params['site_url'] = $site_url;
	$params['site_link'] = $siteurl_link;
	$params['site_email'] = $site_email;
	
	$params['user_id'] = $user_id;
	$params['user_email'] = $user_email;
	$params['user_name'] = $user_name;
	
	$params['client_id'] = $user_id;
	$params['client_email'] = $user_email;
	$params['client_name'] = $user_name;
	$params['listing_id'] = $post_id;
	$params['listing_title'] = $listing_title;
	$params['listing_url'] = $listing_url;
	$params['listing_link'] = $listing_link;
	$params['main_listing_id'] = $post_id;
	$params['main_listing_title'] = $listing_title;
	$params['main_listing_url'] = $listing_url;
	$params['main_listing_link'] = $listing_link;
	$params['franchise_listings_links'] = $franchise_listings_links;
	
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
	
	$headers  = array();
	$headers[] = 'Content-type: text/html; charset=UTF-8';
	$headers[] = "Reply-To: " . $site_email . "\r\n";
	$headers[] = 'To: ' . $user_name . ' <' . $user_email . '>';
	$headers[] = 'From: ' . $site_emailName . ' <' . $site_email . '>';
	
	$sent = wp_mail($user_email, $subject, $message, $headers);
	if( !$sent && function_exists( 'geodir_error_log' ) ) {
		if ( is_array( $user_email ) ) {
			$user_email = implode( ',', $user_email );
		}
		$log_message = sprintf(
			__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
			$message_type,
			date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
			$user_email,
			$subject
		);
		geodir_error_log( $log_message );
	}
	
	// send bcc to admin.
	if (get_option('geodir_franchise_bcc_admin_' . $message_type)) {
		$subject .= ' - ADMIN BCC COPY';
		$sent = wp_mail($admin_email, $subject, $message, $headers);
		if( !$sent && function_exists( 'geodir_error_log' ) ) {
			if ( is_array( $admin_email ) ) {
				$admin_email = implode( ',', $admin_email );
			}
			$log_message = sprintf(
				__( "Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirectory' ),
				$message_type,
				date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
				$admin_email,
				$subject
			);
			geodir_error_log( $log_message );
		}
	}
	
	return true;
}

/**
 * Display package info before listing details in invoice.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param object $invoice_info The invoice info object.
 */
function geodir_franchise_invoice_before_listing_details($invoice_info) {
	if (!empty($invoice_info) && isset($invoice_info->invoice_type) && $invoice_info->invoice_type == 'add_franchise') {
		$package_display = '';
        $package_id = 0;
		if (!empty($invoice_info->package_id)) {
			$package_id = $invoice_info->package_id;
			$package_display = $invoice_info->package_title;
		}
		?>
		<?php if ( $package_display ) { ?>
		<div class="gd-inv-detail">
			<label class="gd-inv-lbl"><?php _e( 'Package ID:', 'geodir_payments' );?> </label>
			<span class="gd-inv-val"><?php echo $package_id;?></span>
		</div>
		<div class="gd-inv-detail">
			<label class="gd-inv-lbl"><?php _e( 'Package:', 'geodir_payments' );?> </label>
			<span class="gd-inv-val"><?php echo $package_display;?></span>
		</div>
		<?php } ?>
		<?php
	}
}

/**
 * Display franchise details after listing details in invoice.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param object $invoice_info The invoice info object.
 */
function geodir_franchise_invoice_after_listing_details($invoice_info) {
	if (!empty($invoice_info) && isset($invoice_info->invoice_type) && $invoice_info->invoice_type == 'add_franchise') {
		$post_id = $invoice_info->post_id;
		$main_listing_link = '<a href="' . get_permalink($post_id) . '" target="_blank">' . get_the_title($post_id) . '</a>';
		$main_listing = wp_sprintf(__('%s <small> ( ID: %d )</small>', 'geodir-franchise'), $main_listing_link, $post_id);
		$franchise_listings = '';
		
		if (!empty($invoice_info->invoice_data)) {
			$invoice_data = maybe_unserialize($invoice_info->invoice_data);
			
			$franchises = isset( $invoice_data['franchises'] ) ? $invoice_data['franchises'] : NULL;
			
			if (!empty($franchises)) {
				$franchise_listings = array();				
				
				foreach ($franchises as $child_post_id) {
					$child_post_title = get_the_title($child_post_id);
					
					if ($child_post_title) {
						$child_post_url = get_permalink($child_post_id);
						$child_listing_link = '<a href="' . $child_post_url . '" target="_blank">' . $child_post_title . '</a>';
						$franchise_listings[] = wp_sprintf(__('- %s <small> ( ID: %d )</small>', 'geodir-franchise'), $child_listing_link, $child_post_id);
					}
				}
				
				$franchise_listings = !empty($franchise_listings) ? '<br>' . implode("<br>", $franchise_listings) : '';
			}
		}
		?>
		<div class="gd-pmt-franchise-detail clearfix">
			<h4 style="margin-bottom:0.35rem"><?php _e('Franchise Details:', 'geodir-franchise');?></h4>
			<div class="gd-inv-detail">
				<label class="gd-inv-lbl"><?php _e('Main Listing:', 'geodir-franchise');?></label>
				<span class="gd-inv-val"><?php echo $main_listing;?></span>
			</div>
			<div class="gd-inv-detail gd-inv-detail-frs">
				<label class="gd-inv-lbl"><?php _e('Franchise Listings:', 'geodir-franchise');?></label>
				<span class="gd-inv-val"><?php echo $franchise_listings;?></span>
			</div>
		</div>
		<?php
	}
}

/**
 * Display parent listing and franchise listings links in back-end invoice list.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param string $invoice_links The listings links content.
 * @param object $invoice_info The invoice info object.
 * @return string The listings links content.
 */
function geodir_franchise_admin_list_invoice_links($invoice_links, $invoice_info) {
	if (!empty($invoice_info) && isset($invoice_info->invoice_type) && $invoice_info->invoice_type == 'add_franchise' && !empty($invoice_info->invoice_data)) {
		$invoice_data = maybe_unserialize($invoice_info->invoice_data);
		$franchises = isset( $invoice_data['franchises'] ) ? $invoice_data['franchises'] : NULL;
		
		if (!empty($franchises)) {
			$franchise_listings = array();				
			
			foreach ($franchises as $child_post_id) {
				$child_post_title = get_the_title($child_post_id);
				
				if ($child_post_title) {
					$child_post_url = get_permalink($child_post_id);
					$franchise_listings[] = '<a href="' . $child_post_url . '" target="_blank">' . $child_post_title . '</a>';
				}
			}
			
			if (!empty($franchise_listings)) {
				$invoice_links .= '<br><small class="gd-adminv-frs" style="line-height:normal">' . implode(', ', $franchise_listings) . '</small>';
			}
		}
	}
	
	return $invoice_links;
}

/**
 * Get the parent listing id of the post.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $post_id The post ID.
 * @return int The parent listing ID.
 */
function geodir_franchise_main_franchise_id( $post_id ) {
	$franchise_id = NULL;
	
	if ( geodir_franchise_check( $post_id ) ) {
		$franchise_id = $post_id;
	} else {
		$post_id = (int)geodir_get_post_meta( $post_id, 'franchise', true );
		
		if ( geodir_franchise_check( $post_id ) ) {
			$franchise_id = $post_id;
		}
	}
	
	return $franchise_id;
}

/**
 * Computes a unique slug for the listing, when given the desired slug and some listing details.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $slug The desired slug (post_name).
 * @param string $post_type Post type.
 * @param int $post_ID Post ID.
 * @return string Unique slug for the listing, based on $post_name (with a -1, -2, etc. suffix).
 */
function geodir_franchise_unique_post_slug( $slug, $post_type, $franchise_ID, $post_ID = 0 ) {
	global $wpdb;

	$original_slug = $slug;
	
	$slug = sanitize_title( $slug );
	
	$post_ID_sql = $post_ID > 0 ? " AND ID != '" . (int)$post_ID . "'" : '';
	
	// Post slugs must be unique across all posts.
	$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s $post_ID_sql LIMIT 1";
	$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type ) );

	if ( $post_name_check ) {
		$suffix = 1;
		do {
			$alt_post_name = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
			$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type ) );
			$suffix++;
		} while ( $post_name_check );
		$slug = $alt_post_name;
	}

	/**
	 * Filter the unique listing slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The post slug.
	 * @param string $post_type Post type.
	 * @param int $franchise_ID Post franchise ID.
	 * @param int $post_ID Post ID.
	 * @param string $original_slug The original post slug.
	 */
	return apply_filters( 'geodir_franchise_unique_post_slug', $slug, $post_type, $franchise_ID, $post_ID, $original_slug );
}

/**
 * Handles claim request status change.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $claim_id The claim id.
 * @param int $new_status New claim status. Ex: 0 for pending, 1 for approved and 2 for rejected etc.
 * @param int $old_status Old claim status. Ex: 0 for pending, 1 for approved and 2 for rejected etc.
 */
function geodir_franchise_claim_status_change( $claim_id, $new_status, $old_status = '' ) {
	global $wpdb;
	
	if ( !function_exists( 'geodir_claim_get_info' ) ) {
		return;
	}
	
	$row = geodir_claim_get_info( $claim_id );
	if ( !empty( $row ) ) {
		$post_ID = $row->list_id;
		$author_ID = $row->user_id;
		
		if ( $post_ID && $author_ID && geodir_franchise_check( $post_ID ) ) {
			if ( (int)$new_status == 1 ) {
				$franchises = geodir_franchise_post_franchises( $post_ID );
				
				if ( !empty( $franchises ) ) {
					foreach ( $franchises as $franchise_ID ) {
						geodir_save_post_meta( $franchise_ID, 'claimed', '1' );
						$wpdb->update( $wpdb->posts, array( 'post_author' => $author_ID ), array( 'ID' => $franchise_ID ) );
					}
				}
			}
		}
	}
}

/**
 * Appends extra HTML classes to the post class.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $class The old class string.
 * @param string|array $all_postypes The GD post types.
 * @return string The modified post class.
 */
function geodir_franchise_post_view_class( $class, $all_postypes = '' ) {
	global $post;

    if ( empty( $all_postypes ) ) {
        $all_postypes = geodir_get_posttypes();
    }

	if ( !empty( $post ) && !empty( $all_postypes ) ) {
		$post_ID = !empty( $post->ID ) ? $post->ID : NULL;
		$post_type = !empty( $post->post_type ) && in_array( $post->post_type, $all_postypes ) ? $post->post_type : NULL;
		
		if ( $post_ID && $post_type && geodir_franchise_enabled( $post_type ) && $franchise_ID = geodir_franchise_main_franchise_id( $post_ID ) ) {
			$franchise_class = $franchise_ID == $post_ID ? 'gdp-franchise-m' : 'gdp-franchise-s';
			$class = $class != '' ? trim($class) . ' ' . $franchise_class : $franchise_class;
		}
	}

    return $class;
}

/**
 * Set the main listing's package id for the franchise listing in back end.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 *
 * @param string $post_type The post type.
 * @param object $post The current post object.
 */
function geodir_franchise_set_admin_package_id($post_type, $post) {
	if ( !geodir_franchise_is_payment_active() ) {
		return;
	}
	global $post;
	
	$post_id = !empty( $post->ID ) ? $post->ID : '';
	$franchise_id = isset( $_REQUEST['franchise'] ) ? (int)$_REQUEST['franchise'] : 0;
	
	if ( $post_id && $post_franchise_id = geodir_get_post_meta( $post_id, 'franchise' ) ) {
		$franchise_id = $post_franchise_id;
	}
	
	if ( $franchise_id && geodir_franchise_check( $franchise_id ) ) {
		$package_id = geodir_get_post_meta( $franchise_id, 'package_id' );
		
		if ( $package_id ) {
			$_REQUEST['package_id'] = $package_id;
			
			if ( $post_id && isset( $post->package_id ) ) {
				$post->package_id = $package_id;
			}
		}
	}
}

/**
 * Set the main listing's package id for the franchise listing in front end.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_set_package_id() {
	if ( !geodir_franchise_is_payment_active() ) {
		return;
	}
	global $post;
	
	$post_id = isset( $_REQUEST['pid'] ) ? (int)$_REQUEST['pid'] : '';
	$franchise_id = isset( $_REQUEST['franchise'] ) ? (int)$_REQUEST['franchise'] : 0;
	
	if ( isset( $_REQUEST['backandedit'] ) ) {
		$franchise_id = isset( $post->franchise ) ? $post->franchise : $franchise_id;
	} else if ( $post_id ) {
		$franchise_id = isset( $post->franchise ) ? $post->franchise : $franchise_id;
	}
	
	if ( $franchise_id && geodir_franchise_check( $franchise_id ) ) {
		$package_id = geodir_get_post_meta( $franchise_id, 'package_id' );
		
		if ( $package_id ) {
			$_REQUEST['package_id'] = $package_id;
			
			if ( $post_id && isset( $post->package_id ) ) {
				$post->package_id = $package_id;
			}
		}
	}
}

/**
 * Adds the main listing & all franchises links on detail pade sidebar.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @global object $post The current post object.
 */
function geodir_franchise_detail_page_sidebar_links() {
	global $post;
	
	if ( !empty( $post ) && !empty( $post->ID ) && geodir_is_page( 'detail' ) && $parent_id = geodir_franchise_main_franchise_id( $post->ID ) ) {			
		$post_id = $post->ID;
		$post_type = $post->post_type;
		$post_title = get_the_title( $parent_id );
		
		if ( $parent_id != $post_id ) {
						
			if ( get_option( 'geodir_franchise_show_parent_link' ) ) {
				$parent_link_label = __( 'Main Listing For Franchise', 'geodir-franchise' );

				/**
				 * Filter the parent listing link label.
				 *
				 * @since 1.0.0
				 *
				 * @param string $parent_link_label The parent listing link label.
				 * @param string $post_type The post type.
				 */
				$parent_link_label = apply_filters( 'geodir_franchise_parent_link_label', $parent_link_label, $post_type );
				$parent_link_url = get_permalink( $parent_id );
				
				$parent_link = '<div class="geodir_more_info gd-franchise-link"><span class="geodir-i-website"><i class="fa fa-link"></i><a title="' . esc_attr( $parent_link_label ) . '" href="' . esc_url( $parent_link_url ) . '">' . $parent_link_label . '</a></span></div>';
				
				/**
				 * Filter the parent listing link content.
				 *
				 * @since 1.0.0
				 *
				 * @param string $parent_link The parent listing link content.
				 * @param int $post_id The post ID.
				 * @param int $parent_id The parent listing ID.
				 */
				echo apply_filters( 'geodir_franchise_detail_page_show_parent_link', $parent_link, $post_id, $parent_id );
			}
		}
		
		if ( get_option( 'geodir_franchise_show_franchises_link' ) ) {
			$franchises_link_label = __( 'All Franchises', 'geodir-franchise' );

			/**
			 * Filter the all franchise listings link label.
			 *
			 * @since 1.0.0
			 *
			 * @param string $franchises_link_label All franchises link label.
			 * @param string $post_type The post type.
			 */
			$franchises_link_label = apply_filters( 'geodir_franchise_franchises_link_label', $franchises_link_label, $post_type );
			
			$params = array();
			$params['franchise'] = $parent_id . '-' . $post_title;
			
			$franchises_link_url = geodir_getlink( get_post_type_archive_link( $post_type ), $params );
			
			/**
			 * Filter the all franchise listings page link.
			 *
			 * @since 1.0.0
			 *
			 * @param string $franchises_link_url All franchises page link.
			 * @param int $parent_id The parent listing ID.
			 */
			$franchises_link_url = apply_filters( 'geodir_franchise_all_franchises_link', $franchises_link_url, $parent_id );
			
			$franchises_link = '<div class="geodir_more_info gd-franchise-link"><span class="geodir-i-website"><i class="fa fa-sitemap"></i><a title="' . esc_attr( $franchises_link_label ) . '" href="' . esc_url( $franchises_link_url ) . '">' . $franchises_link_label . '</a></span></div>';
			
			/**
			 * Filter the all franchise listings link content.
			 *
			 * @since 1.0.0
			 *
			 * @param string $franchises_link All franchises link content.
			 * @param int $parent_id The parent listing ID.
			 */
			echo apply_filters( 'geodir_franchise_detail_page_show_franchises_link', $franchises_link, $parent_id );
		}
	}
}

/**
 * Add the franchise option names that requires to add for translation.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param  array $options Array of option names.
 * @return array Array of option names.
 */
function geodir_franchise_add_options_for_translation( $options = array() ) {
	$options[] = 'geodir_franchise_client_email_subject_payment_franchises';
	$options[] = 'geodir_franchise_client_email_message_payment_franchises';
	
	return $options;
}

/**
 * Get view franchises listing page title.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param string $title The page title including variables.
 * @param string $gd_page The GeoDirectory page type if any.
 */
function geodir_franchise_seo_page_title($title, $gd_page = '') {
	if ( !empty( $_REQUEST['franchise'] ) && geodir_is_geodir_page() && ( $gd_page == 'pt' || $gd_page == 'listing' ) ) {
		$franchise = explode( '-', sanitize_text_field($_REQUEST['franchise']), 2 );
		
		if ( !empty( $franchise[0] ) && geodir_franchise_check( $franchise[0] ) ) {
			$listing_title = get_the_title( $franchise[0] );
			$title = wp_sprintf( __('All %s Franchises', 'geodir-franchise'), $listing_title );
			
			/**
			 * Filter page title for view franchises listing page title.
			 *
			 * @since 1.0.0
			 *
			 * @param string $title The page title.
			 * @param int $franchise[0] The main listing id.
			 * @param string $gd_page The GeoDirectory page type if any.
			 */
			 $title =  apply_filters( 'geodir_franchise_view_franchises_page_title', $title, $franchise[0], $gd_page );
		}
	}
	
	return $title;
}

/**
 * Adds filters to add franchises columns after the current screen has been set.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param WP_Screen $screen Current WP_Screen object.
 */
function geodir_franchise_set_identify_franchise( $screen ) {
	if ( isset($screen->base) && $screen->base == 'edit' && isset( $screen->post_type ) && geodir_franchise_enabled( $screen->post_type ) ) {
		$post_type = $screen->post_type;
		
		add_filter( 'manage_' . $post_type . '_posts_columns' , 'geodir_franchise_manage_cpt_posts_columns', 101, 1 );
		add_action( 'manage_' . $post_type . '_posts_custom_column', 'geodir_franchise_manage_cpt_posts_custom_column', 101, 2 );
    }
}

/**
 * Add the franchises custom columns to the columns displayed in the cpt list table for a specific post type.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param array $post_columns An array of column names.
 * @return array An array of modified column names.
 */
function geodir_franchise_manage_cpt_posts_columns( $post_columns ) {
	if ( !isset( $post_columns['gd_franchise_type'] ) ) {
		if ( ( $offset = array_search( 'title', array_keys( $post_columns ) ) ) !== false ) {
			$offset = $offset + 1;
		} else {
			$offset = 1;
		}
		
		$new_columns = array( 'gd_franchise_type' => '<span class="dashicons dashicons-networking" title="' . esc_attr( __( 'Franchise Listing Type', 'geodir-franchise' ) ) . '"> </span>', 'gdfr_main_listing' => __( 'Main Listing', 'geodir-franchise' )  );
		$post_columns = array_merge( array_slice( $post_columns, 0, $offset, true ), $new_columns, array_slice( $post_columns, $offset, null, true ) );
	}
	
	return $post_columns;
}

/**
 * Display custom columns values for each custom column of a specific post type in the cpt list table.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id The current post ID.
 */
function geodir_franchise_manage_cpt_posts_custom_column( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'gd_franchise_type':
			$color = '#ccc';
			if ( $main_listing_id = geodir_franchise_main_franchise_id( $post_id ) ) {
				if ( $main_listing_id == $post_id ) {
					$class = 'gdfr-franchise-m';
					$title = __( 'Main Listing', 'geodir-franchise' );
					$color = 'orange';
				} else {
					$class = 'gdfr-franchise-s';
					$title = __( 'Franchise Listing', 'geodir-franchise' );
					$color = '#00a0d2';
				}
			} else {
				$class = 'gdfr-franchise-n';
				$title = __( 'Normal Listing', 'geodir-franchise' );
			}
			echo '<span class="' . $class . ' dashicons dashicons-networking" title="' . esc_attr( $title ) . '" style="color:' . $color . '"> </span>';
		break;
		case 'gdfr_main_listing':
			$main_listing = '<span aria-hidden="true">&mdash;</span>';
			if ( $main_listing_id = geodir_franchise_main_franchise_id( $post_id ) ) {
				if ( $main_listing_id != $post_id ) {
					$main_listing_title = get_the_title( $main_listing_id );
					$edit_link = get_edit_post_link( $main_listing_id );
					$edit_listing = '<br><small>' . __( 'ID:', 'geodir-franchise' ) . ' <a href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'geodir-franchise' ), $main_listing_title ) ) . '">' . $main_listing_id . '</a></small>';
					$main_listing = '<a target="_blank" href="' . esc_url( get_permalink( $main_listing_id ) ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'geodir-franchise' ), $main_listing_title ) ) . '">' . $main_listing_title . '</a>' . $edit_listing;
				}
			}
			
			echo $main_listing;
		break;
	}
}

/**
 * Get the franchise limit for main listing.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $franchise_id Main parent listing id.
 * @return int The franchise limit.
 */
function geodir_franchise_get_franchise_limit( $franchise_id ) {
	$value = 0;
	
	if (geodir_franchise_check( $franchise_id ) && geodir_franchise_is_payment_active() ) {
		$franchise_info = geodir_get_post_info( $franchise_id );
		$package_info = (array)geodir_post_package_info( array(), $franchise_info );
		
		$value = !empty( $package_info ) && isset( $package_info['franchise_limit'] ) ? absint($package_info['franchise_limit']) : 0;
	}
	return $value;
}

/**
 * Check if allowed to add more franchises.
 *
 * @package GeoDirectory_Franchise_Manager
 * @since 1.0.0
 *
 * @param int $franchise_id Main parent listing id.
 * @return int|string Allowed number of new franchises.
 */
function geodir_franchise_allowed_to_add_more( $franchise_id ) {
	if (geodir_franchise_check( $franchise_id ) ) {
		$return = 'unlimited';
		
		if ( geodir_franchise_is_payment_active() && ( $franchise_limit = geodir_franchise_get_franchise_limit( $franchise_id ) ) > 0 ) {
			$my_franchises = geodir_franchise_post_franchises( $franchise_id );
			$my_franchises = !empty( $my_franchises ) ? count( $my_franchises ) : 0;
			
			$return = max( ( $franchise_limit - $my_franchises ), 0 );
		}
	} else {
		$return = 0;
	}
	return $return;
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.0.2
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_franchise_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(dirname(__FILE__)));
    
    return $settings;
}

/**
 * Delete franchise and its data.
 *
 * @since 1.0.6
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @return int $post_id The listing id.
 * @return mixed.
 */
function geodir_franchise_remove_franchise( $post_id ) {
    global $wpdb, $plugin_prefix;
    
    if ( empty( $post_id ) ) {
        return false;
    }
    
    $post_type = get_post_type( $post_id );
    
    do_action( 'geodir_franchise_pre_remove_franchise', $post_id );
    
    delete_post_meta( $post_id, 'gd_is_franchise' );
    delete_post_meta( $post_id, 'gd_franchise_lock' );
    
    $wpdb->query( $wpdb->prepare( "UPDATE " . $plugin_prefix . $post_type . '_detail' . " SET `franchise` = '0' WHERE `franchise` = %d", array( $post_id ) ) );
    
    do_action( 'geodir_franchise_post_remove_franchise', $post_id );
    
    return true;
}