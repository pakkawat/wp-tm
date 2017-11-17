<?php
/**
 * Contains functions related to Claim Manager plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Claim_Manager
 */
 
// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * Plugin activation hook.
 *
 * @package GeoDirectory_Claim_Manager
 * @since 1.0.0
 * @since 1.2.9 Don't loose previously saved settings when plugin is reactivated.
 */
function geodir_claim_listing_activation() {
    if (get_option('geodir_installed')) {
        geodir_claim_activation_script();

        $default_options = geodir_claim_resave_settings(geodir_claim_default_options());
        geodir_update_options($default_options, true);
        
        $notifications = geodir_claim_resave_settings(geodir_claim_notifications());
        geodir_update_options($notifications, true);

        add_option('geodir_claim_listing_activation_redirect', 1);
    }
}

/**
 * Plugin deactivation hook.
 *
 * @package GeoDirectory_Claim_Manager
 * @since 1.0.0
 */
function geodir_claim_listing_deactivation() {
    // Plugin deactivation stuff here
}

/**
 * Check GeoDirectory plugin installed.
 *
 * @package GeoDirectory_Claim_Manager
 * @since 1.0.0
 */
function geodir_claim_listing_plugin_activated( $plugin ) {
    if ( !get_option( 'geodir_installed' ) )  {
        $file = plugin_basename( GEODIRCLAIM_PLUGIN_FILE );
        
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
        
        wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodirclaim' ) );
    }
}

/**
 * Plugin activation redirect.
 *
 * @package GeoDirectory_Claim_Manager
 * @since 1.0.0
 */
function geodirclaimlisting_activation_redirect() {
    if ( get_option( 'geodir_claim_listing_activation_redirect', false ) ) {
        delete_option( 'geodir_claim_listing_activation_redirect' );
        
        wp_redirect( admin_url( 'admin.php?page=geodirectory&tab=claimlisting_fields&subtab=geodir_claim_options' ) ); 
    }
}

/**
 * Handle the plugin settings for plugin deactivate to activate.
 *
 * It manages the the settings without loosing previous settings saved when plugin
 * status changed from deactivate to activate.
 *
 * @since 1.2.9
 * @package GeoDirectory_Claim_Manager
 *
 * @param array $settings The option settings array.
 * @return array The settings array.
 */
function geodir_claim_resave_settings($settings = array()) {
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
 * Load geodirectory claim manager plugin textdomain.
 *
 * @package GeoDirectory_Claim_Manager
 * @since 1.0.0
 */
function geodir_load_translation_geodirclaim() {
    $locale = apply_filters( 'plugin_locale', get_locale(), 'geodirclaim' );
    load_textdomain( 'geodirclaim', WP_LANG_DIR . '/geodirclaim/geodirclaim-' . $locale . '.mo' );
    load_plugin_textdomain( 'geodirclaim', false, dirname( plugin_basename( GEODIRCLAIM_PLUGIN_FILE ) ) . '/geodir-claim-languages' );

    /**
     * Define language constants.
     */
    require_once( GEODIRCLAIM_PLUGIN_PATH . '/language.php' );
}

function geodir_claim_activation_script() {
	global $wpdb,$plugin_prefix;

	/**
	 * Include any functions needed for upgrades.
	 *
	 * @since 1.1.4
	 */
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$wpdb->hide_errors();

	
	$collate = '';
	if($wpdb->has_cap( 'collation' ) ) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_CLAIM_TABLE."'") != GEODIR_CLAIM_TABLE){
	
		$claim_table = "CREATE TABLE IF NOT EXISTS `".GEODIR_CLAIM_TABLE."` (
			`pid` int(11) NOT NULL AUTO_INCREMENT,
			`list_id` varchar(255) NOT NULL,
			`list_title` varchar(255) NOT NULL,
			`user_id` varchar(255) NOT NULL,
			`user_name` varchar(255) NOT NULL,
			`user_email` varchar(255) NOT NULL,
			`user_fullname` varchar(255) NOT NULL,
			`user_number` varchar(255) NOT NULL,
			`user_position` varchar(255) NOT NULL,
			`user_comments` longtext NOT NULL,
			`admin_comments` longtext NOT NULL,
			`claim_date` varchar(255) NOT NULL,
			`org_author` varchar(255) NOT NULL,
			`org_authorid` varchar(255) NOT NULL,
			`rand_string` varchar(255) NOT NULL,
			`status` varchar(255) NOT NULL,
			`user_ip` varchar(255) NOT NULL,
			`upgrade_pkg_id` INT( 11 ) NOT NULL,
			`upgrade_pkg_data` TINYTEXT NOT NULL,
			PRIMARY KEY (`pid`)) $collate";
		
		$claim_table = apply_filters('geodir_claim_listing_table' , $claim_table);	
		
		// rename tables if we need to
		if ($wpdb->query("SHOW TABLES LIKE 'geodir_claim'") > 0) {
			$wpdb->query("RENAME TABLE geodir_claim TO ".$wpdb->prefix."geodir_claim");
		} else {
			dbDelta($claim_table);
		}
		
		do_action('geodir_claim_listing_table_created' ,$claim_table ) ;
		
		update_option( 'geodir_claim_fields_upgrade', '1' );		
	}	
}

function geodir_delete_claim_listing_info($deleted_postid, $force = false){
	
	global $wpdb;
	
	$post_type = get_post_type( $deleted_postid );
	
	$all_postypes = geodir_get_posttypes();
	
	if(!in_array($post_type, $all_postypes))
		return false;
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CLAIM_TABLE." WHERE list_id=%s", array($deleted_postid)));
	
}


function geodir_unactioned_claims(){
	
	global $wpdb, $plugin_prefix;
	
	$geodir_unaction_claim = $wpdb->get_var("SELECT COUNT(pid) 
						FROM ".GEODIR_CLAIM_TABLE."
						WHERE status = ''");
	return $geodir_unaction_claim;
}


function geodir_claims_change_unread_to_read(){
	
	global $wpdb, $plugin_prefix;
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'manage_geodir_claim_listing'):
	
		$wpdb->query("update ".GEODIR_CLAIM_TABLE." set status='0' where status = ''");
		
	endif;
}


function geodir_claim_post_type_setting(){

	$post_type_arr = array();
	
	$post_types = geodir_get_posttypes('object');
	
	foreach($post_types as $key => $post_types_obj)
	{
		$post_type_arr[$key] = $post_types_obj->labels->singular_name;
	}
	return 	$post_type_arr;
}


function geodir_get_claim_default_options_form($current_tab){
	
	$current_tab = $_REQUEST['subtab'];
	geodir_claim_default_option_form($current_tab);
	
}


function geodir_claim_default_options($arr=array()){

	$arr[] = array( 'name' => __( 'Options', 'geodirclaim' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'claim_options' );
	
	$arr[] = array( 'name' => __('Geo Directory Claim Listing Options', 'geodirclaim'), 'type' => 'sectionstart', 'id' => 'claim_default_options');
	
	$arr[] = array(  
		'name' => __('Enable claim listing?', 'geodirclaim'),
		'desc' 		=> __( 'Select \'yes\' to enable claim listing.', 'geodirclaim' ),
		'tip' 		=> '',
		'id' 			=> 'geodir_claim_enable',
		'css' 		=> 'min-width:300px;',
		'std' 		=> '',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique( array( 
			'' => __( 'Select', 'geodirclaim' ),
			'yes' => __( 'Yes', 'geodirclaim' ),
			'no' => __( 'No', 'geodirclaim' ),
			))
	);
	
	$arr[] = array(  
		'name' => __('Auto approve claim listing? (email verification)', 'geodirclaim'),
		'desc' 		=> __( 'Select \'yes\' to auto approve claim listing.', 'geodirclaim' ),
		'tip' 		=> '',
		'id' 			=> 'geodir_claim_auto_approve',
		'css' 		=> 'min-width:300px;',
		'std' 		=> '',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique( array( 
			'' => __( 'Select', 'geodirclaim' ),
			'yes' => __( 'Yes', 'geodirclaim' ),
			'no' => __( 'No', 'geodirclaim' ),
			))
	);
	
	$arr[] = array(  
		'name' => __('Show owner verified text on listings?', 'geodirclaim'),
		'desc' 		=> __( 'Select \'yes\' to show owner verified text on listings.', 'geodirclaim' ),
		'tip' 		=> '',
		'id' 			=> 'geodir_claim_show_owner_varified',
		'css' 		=> 'min-width:300px;',
		'std' 		=> '',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique( array( 
			'' => __( 'Select', 'geodirclaim' ),
			'yes' => __( 'Yes', 'geodirclaim' ),
			'no' => __( 'No', 'geodirclaim' ),
			))
	);
	
	$arr[] = array(  
		'name' => __('Show link to author page on listings?', 'geodirclaim'),
		'desc' 		=> __( 'Select \'yes\' to show link to author page on listings.', 'geodirclaim' ),
		'tip' 		=> '',
		'id' 			=> 'geodir_claim_show_author_link',
		'css' 		=> 'min-width:300px;',
		'std' 		=> '',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique( array( 
			'' => __( 'Select', 'geodirclaim' ),
			'yes' => __( 'Yes', 'geodirclaim' ),
			'no' => __( 'No', 'geodirclaim' ),
			))
	);
	
	$arr[] = array(  
		'name' => __('Choose post types for show claim listing link', 'geodirclaim'),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'geodir_post_types_claim_listing',
		'css' 		=> 'min-width:300px;',
		'std' 		=> array(),
		'type' 		=> 'multiselect',
		'placeholder_text' => __( 'Select post types', 'geodirclaim' ) ,
		'class'		=> 'chosen_select',
		'options' => array_unique( geodir_claim_post_type_setting())
	);
	
	if ( is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' ) ) {
		// Force to payment settings
		$arr[] = array(  
			'name' => __( 'Force an upgrade to complete the claim listing procedure', 'geodirclaim' ),
			'desc' 		=> __( 'Select \'yes\' to force an upgrade to complete the claim listing procedure.', 'geodirclaim' ),
			'tip' 		=> '',
			'id' 			=> 'geodir_claim_force_upgrade',
			'css' 		=> 'min-width:300px;',
			'std' 		=> '',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( array( 
				'' => __( 'Select', 'geodirclaim' ),
				'yes' => __( 'Yes', 'geodirclaim' ),
				'no' => __( 'No', 'geodirclaim' ),
				))
		);
	}
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'claim_default_options');
	
	$arr = apply_filters('geodir_claim_default_options' ,$arr );
	
	return $arr;
}


function geodir_claim_notifications($arr=array()){

	$arr[] = array( 'name' => __( 'Notifications', 'geodirclaim' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'claim_notifications' );
	
	$arr[] = array( 'name' => __('Manage Geo Directory Claim Notifications', 'geodirclaim'), 'type' => 'sectionstart', 'id' => 'claim_notifications');
	
	$arr[] = array(  
		'name' => __('Admin claim listing request notice', 'geodirclaim'),
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_email_subject_admin',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Claim Listing Requested', 'geodirclaim')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_email_content_admin',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear Admin,<p><p>A user has requested to become the owner of the below lisitng.</p><p>[#listing_link#]</p><p>You may wish to login and check the claim details.</p><p>Thank you,<br /><br />[#site_name#].</p>", 'geodirclaim')
		);
		
		$arr[] = array(  
		'name' => __('Claim listing request', 'geodirclaim'),
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Claim Listing Requested', 'geodirclaim')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],<p><p>You have requested to become the owner of the below listing.</p><p>[#listing_link#]</p><p>We may contact you to confirm your request is genuine.</p><p>You will receive a email once your request has been verified</p><p>Thank you,<br /><br />[#site_name#].</p>", 'geodirclaim')
		);
		
		$arr[] = array(  
		'name' => __('Claim listing approval', 'geodirclaim'),
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_approved_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Claim Listing Approved', 'geodirclaim')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_approved_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],<p><p>Your request to become the owner of the below listing has been APPROVED.</p><p>[#listing_link#]</p><p>You may now login and edit your listing.</p><p>Thank you,<br /><br />[#site_name_url#].</p>", 'geodirclaim')
		);
		
		$arr[] = array(  
		'name' => __('Claim listing rejected', 'geodirclaim'),
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_rejected_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Claim Listing Rejected', 'geodirclaim')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_rejected_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],<p><p>Your request to become the owner of the below listing has been REJECTED.</p><p>[#listing_link#]</p><p>If you feel this is a wrong decision please reply to this email with your reasons.</p><p>Thank you,<br /><br />[#site_name#].</p>", 'geodirclaim')
		);
		
		$arr[] = array(  
		'name' => __('Claim listing verification required', 'geodirclaim'),
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_auto_approve_email_subject',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> __('Claim Listing Verification Required', 'geodirclaim')
		);
	$arr[] = array(  
		'name' => '',
		'desc' 		=> '',
		'id' 		=> 'geodir_claim_auto_approve_email_content',
		'css' 		=> 'width:500px; height: 150px;',
		'type' 		=> 'textarea',
		'std' 		=>  __("<p>Dear [#client_name#],<p><p>Your request to become the owner of the below listing needs to be verified.</p><p>[#listing_link#]</p><p><b>By clicking the VERIFY link below you are stating you are legally associated with this business and have the owners consent to edit the listing.</b></p><p><b>If you are not associated with this business and edit the listing with malicious intent you will be solely liable for any legal action or claims for damages.</b></p><p>[#approve_listing_link#]</p><p>Thank you,<br /><br />[#site_name_url#].</p>", 'geodirclaim')
		);
	
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'claim_notifications');
	
	$arr = apply_filters('geodir_claim_notifications' ,$arr );
	
	return $arr;
}



function geodir_enable_editor_on_claim_notifications($notification){
	
	if(!empty($notification) && get_option('geodir_tiny_editor')=='1'){
		
		foreach($notification as $key => $value){
			if($value['type'] == 'textarea')
				$notification[$key]['type'] = 'editor';
		}
		
	}
	
	return $notification;
}


function geodir_get_admin_claim_listing_option_form(){
	
	global $wpdb;
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_claim_options' )
	{
		add_action('geodir_admin_option_form', 'geodir_get_claim_default_options_form');
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'manage_geodir_claim_listing')
	{
			if(isset($_REQUEST['pagetype']) && $_REQUEST['pagetype']=='addedit')
			{
				geodir_admin_claim_frm();
					
			}else
			{
				geodir_manage_claim_listing();
			}
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_claim_notification')
	{
		add_action('geodir_admin_option_form', 'geodir_get_claim_default_options_form');
	}
	
}


function geodir_claim_manager_ajax()
{

	if(isset($_POST['geodir_sendact']) && $_POST['geodir_sendact'] == 'add_claim')
	{	
		geodir_user_add_claim();
	}
	
	if(isset($_REQUEST['claimact']) && $_REQUEST['claimact'] == 'addclaim')
	{
		geodir_claim_add_comment();
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_claim_options')
	{
		
		geodir_update_options(geodir_claim_default_options());
		
		$msg = CLAIM_LISTING_OPTIONS_SAVE;
		
		$msg = urlencode($msg);
		
		$location = admin_url()."admin.php?page=geodirectory&tab=claimlisting_fields&subtab=geodir_claim_options&claim_success=".$msg;
		
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['manage_action']) && $_REQUEST['manage_action']=='true')
	{
		geodir_manage_claim_listing_actions();
	}
	
	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_claim_notification')
	{
		
		geodir_update_options(geodir_claim_notifications());
		
		$msg = CLAIM_NOTIFY_SAVE_SUCCESS;
		
		$msg = urlencode($msg);
		
		$location = admin_url()."admin.php?page=geodirectory&tab=claimlisting_fields&subtab=geodir_claim_notification&claim_success=".$msg;
	
		wp_redirect($location);exit;
		
	}
	
	if(isset($_REQUEST['popuptype']) && $_REQUEST['popuptype'] != '' && isset($_REQUEST['post_id']) && $_REQUEST['post_id'] != ''){
		
		if($_REQUEST['popuptype'] == 'geodir_claim_enable')
			geodir_claim_popup_form($_REQUEST['post_id']);
		
		exit;
	}
	
}


function geodir_claim_add_comment()
{
	global $wpdb, $plugin_prefix;
	
	if(isset($_REQUEST['claim_addcomment_nonce']) && isset($_REQUEST['id']) && current_user_can('manage_options')){
	
		if ( !wp_verify_nonce( $_REQUEST['claim_addcomment_nonce'], 'claim_addcomment_nonce' ) )
		return;
		
		if(isset($_REQUEST['claimact']) && $_REQUEST['claimact'] == 'addclaim')
		{
			$id = $_REQUEST['id'];
			$admin_com = $_REQUEST['admin_com'];
			
			if($id)
			{
				$wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set admin_comments=%s", array($admin_com)));
			}
			
			$msg = CLAIM_COMMENT_ADD_SUCCESS;
			$msg = urlencode($msg);
			
			$location = admin_url('admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing&claim_success='.$msg);
			wp_redirect($location);exit;
	
		}
	
	}else{		
		wp_redirect(geodir_login_url());
		exit();
	}
}

function geodir_display_post_claim_link() {
    global $post, $preview;

    $geodir_post_type = array();
    if (get_option('geodir_post_types_claim_listing')) {
        $geodir_post_type = get_option('geodir_post_types_claim_listing');
    }

    $post_id = $post->ID;
    $posttype = (isset($post->post_type)) ? $post->post_type : '';

    if (in_array($posttype, $geodir_post_type) && !$preview) {
        $post_info = get_post($post_id);
        $author_id = $post_info->post_author;
        $post_type = $post_info->post_type;
        
        // WPML
        $duplicate_of = geodir_wpml_is_post_type_translated($post_type) ? get_post_meta((int)$post_id, '_icl_lang_duplicate_of', true) : NULL;
        // WPML

        $is_owned = !$duplicate_of ? (int)geodir_get_post_meta($post_id, 'claimed', true) : (int)geodir_get_post_meta($duplicate_of, 'claimed', true);

        if (get_option('geodir_claim_show_owner_varified') == 'yes') { 
            if ( $is_owned ) {
                echo '<p class="sucess_msg"><i class="fa fa-check-circle"></i> '. CLAIM_OWNER_VERIFIED_PLACE . '</p>';
            }
        }
        
        if (get_option('geodir_claim_enable') == 'yes' && !$is_owned ) {
            if ($duplicate_of) {
                $current_url = get_permalink($duplicate_of);
                $current_url = add_query_arg(array('gd_go' => 'claim'), $current_url);
                
                if (!is_user_logged_in()) {
                    $current_url = geodir_login_url(array('redirect_to' => urlencode_deep($current_url)));
                    $current_url = apply_filters('geodir_claim_login_to_claim_url', $current_url, $duplicate_of);
                }
                    
                echo '<p class="edit-link gd-claim-link"><a href="' . $current_url . '"><i class="fa fa-question-circle"></i> ' . CLAIM_BUSINESS_OWNER . '</a></p>';
            } else {
                if (is_user_logged_in()) {
                    echo '<input type="hidden" name="geodir_claim_popup_post_id" value="' . $post_id . '" /><div class="geodir_display_claim_popup_forms"></div><p class="edit-link gd-claim-link"><i class="fa fa-question-circle"></i> <a href="javascript:void(0);" class="geodir_claim_enable" id="gd-claim-button">' . CLAIM_BUSINESS_OWNER . '</a></p>';
                    if (!empty($_REQUEST['gd_go']) && $_REQUEST['gd_go'] == 'claim' && !isset($_REQUEST['geodir_claim_request'])) {
                        echo '<script type="text/javascript">jQuery(function(){jQuery("#gd-claim-button").trigger("click");});</script>';
                    }
                } else {
                    $current_url = remove_query_arg(array('gd_go'), geodir_curPageURL());
                    $current_url = add_query_arg(array('gd_go' => 'claim'), $current_url);
                    $login_to_claim_url = geodir_login_url(array('redirect_to' => urlencode_deep($current_url)));
                    $login_to_claim_url = apply_filters('geodir_claim_login_to_claim_url', $login_to_claim_url, $post_id);
                    echo '<p class="edit-link gd-claim-link"><a href="' . $login_to_claim_url . '"><i class="fa fa-question-circle"></i> ' . CLAIM_BUSINESS_OWNER . '</a></p>';
                }
            }
            
            if (isset($_REQUEST['geodir_claim_request']) && $_REQUEST['geodir_claim_request'] == 'success') {
                if (get_option('geodir_claim_auto_approve') == 'yes') {
                    echo '<p class="sucess_msg">' . __('Verification link was sent to your email, verify to claim the listing.','geodirclaim') . '</p>';
                } else {
                    echo '<p class="sucess_msg">' . CLAIM_LISTING_SUCCESS . '</p>';
                }
            }
        }
        
        if ($is_owned && get_option('geodir_claim_show_author_link') == 'yes' && !$preview) {
            $author_link = get_author_posts_url($author_id);
            $author_link = geodir_getlink($author_link, array('geodir_dashbord' => 'true', 'stype' => $post_type), false);
            
            // hook for author link
            $author_link = apply_filters('geodir_dashboard_author_link', $author_link, $author_id, $post_type);
            
            echo '<p class="geodir-author-link"><i class="fa fa-user"></i> ';
            echo CLAIM_AUTHOR_TEXT;
            echo '<a href="' . $author_link . '">';
            the_author_meta( 'display_name', $author_id );
            echo '</a></p>';
        }
    }
}

function geodir_user_add_claim() {
    global $wp_query, $post, $General, $wpdb, $plugin_prefix, $current_user;

    if (isset($_REQUEST['add_claim_nonce_field']) && isset($_REQUEST['geodir_pid']) && is_user_logged_in()) {
        if ( !wp_verify_nonce( sanitize_text_field( $_REQUEST['add_claim_nonce_field'] ), 'add_claim_nonce' . (int)$_REQUEST['geodir_pid'] ) ) {
            echo "add_claim_nonce_field:fail";
            return;
        }
        
        // WPML
        if (geodir_wpml_is_post_type_translated(get_post_type((int)$_POST['geodir_pid'])) && $duplicate_of = get_post_meta((int)$_POST['geodir_pid'], '_icl_lang_duplicate_of', true)) {
            $_POST['geodir_pid'] = $duplicate_of;
        }
        // WPML

        $list_id = $pid = (int)$_POST['geodir_pid'];
        $claim_post = get_post($pid);
        
        if (isset($_POST['geodir_sendact']) && $_POST['geodir_sendact'] == 'add_claim') {
            $uid = $claim_post->post_author;
            $list_title = $claim_post->post_title;
            $user_id = $current_user->ID;
            $user_name = $current_user->user_login;
            $user_email = $current_user->user_email;
            $user_fullname = sanitize_text_field($_POST['geodir_full_name']);
            $user_number = sanitize_text_field($_POST['geodir_user_number']);
            $user_position = sanitize_text_field($_POST['geodir_user_position']);
            $user_comments = $_POST['geodir_user_comments'];
            $claim_date = date("F j, Y, g:i a");
            $org_author = get_the_author_meta( 'login', $uid );
            $org_authorid = $claim_post->post_author;
            $rand_string = createRandomString();
            $user_ip = getenv("REMOTE_ADDR");
            
            // Force to upgrade to complete claim listing
            $force_upgrade = geodir_claim_force_upgrade();
            $package_list = geodir_claim_payment_package_list( $claim_post->post_type );
            
            $force_payment = $force_upgrade && $package_list ? true : false;
            $geodir_upgrade_pkg = '';
            $package_info = array();
            if ( $force_payment ) {
                $geodir_upgrade_pkg = isset($_POST['geodir_claim_pkg']) ? $_POST['geodir_claim_pkg'] : '';
                $package_info = geodir_get_package_info_by_id( $geodir_upgrade_pkg );
                
                if ( empty( $package_info ) || !$list_id ) {
                    return;
                }
            }
        
            if ( $list_id ) {
                $claimsql = $wpdb->prepare("INSERT INTO ".GEODIR_CLAIM_TABLE." (list_id, list_title, user_id, user_name, user_email, user_fullname, user_number, user_position, user_comments, claim_date, org_author, org_authorid, rand_string, user_ip ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,%s, %s )",
                array($list_id,$list_title,$user_id,$user_name,$user_email,$user_fullname,$user_number,$user_position,$user_comments,$claim_date,$org_author,$org_authorid,$rand_string,$user_ip)
                );
            
                $wpdb->query($claimsql);
                $claim_id = $wpdb->insert_id;
                
                geodir_adminEmail( $list_id, $user_id, 'claim_requested' ); /* email to admin*/
                
                if ( get_option( 'geodir_claim_auto_approve' ) == 'yes' ) {
                    if (!$force_payment) {
                        geodir_clientEmail( $list_id, $user_id, 'auto_claim', $rand_string );/* email to client*/
                    }
                } else {
                    geodir_clientEmail( $list_id, $user_id, 'claim_requested' ); /* email to client*/
                }
                
                // Force to upgrade to complete claim listing
                $upgrade_pkg_data = array();
                $upgrade_pkg_data['post_id'] = $list_id;
                $upgrade_pkg_data['package_id'] = $geodir_upgrade_pkg;
                $upgrade_pkg_data['date'] = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
                $upgrade_pkg_data['amount'] = (isset($package_info->amount)) ? $package_info->amount : '';
                $upgrade_pkg_data['user_id'] = $user_id;
                $upgrade_pkg_data['author_id'] = $org_authorid;
                $upgrade_pkg_data['claim_id'] = $claim_id;
                
                $post_status = get_post_status( $list_id );
                
                // invoice data
                $invoice_id = NULL;
                if ($force_payment && !empty($package_info) && (float)$package_info->amount > 0 ) {
                    $post_id = $list_id;
                    $package_id = $geodir_upgrade_pkg;
                
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
                
                    $expire_date = $alive_days > 0 ? date_i18n( 'Y-m-d', strtotime( date_i18n( 'Y-m-d' ) . ' + ' . (int)$alive_days . ' days' ) ) : '';
                
                    $amount = $package_info->amount;
                
                    $tax_amount = geodir_payment_get_tax_amount( $amount, $package_id, $post_id );
                
                    $amount = geodir_payment_price( $amount, false );
                
                    $paid_amount = ( $amount + $tax_amount );
                    $paid_amount = $paid_amount > 0 ? $paid_amount : 0;
                
                    $payment_status = $paid_amount > 0 ? 'pending' : 'confirmed';
                
                    $invoice_type = 'claim_listing';
                    $invoice_callback = 'claim_listing';
                    $invoice_title = wp_sprintf(  __( 'Claim Listing: %s', 'geodirclaim' ), get_the_title( $post_id ) );
                
                    $data = array();
                    $data['type'] = $amount > 0 ? 'paid' : 'free';
                    $data['post_id'] = $post_id;
                    $data['post_title'] = $invoice_title;
                    $data['post_action'] = 'claim';
                    $data['invoice_type'] = $invoice_type;
                    $data['invoice_callback'] = $invoice_callback;
                    $data['invoice_data'] = maybe_serialize( $upgrade_pkg_data );
                    $data['package_id'] = $package_id;
                    $data['package_title'] = $package_info->title;
                    $data['amount'] = $amount;
                    $data['alive_days'] = $alive_days;
                    $data['expire_date'] = $expire_date;
                    $data['tax_amount'] = $tax_amount;
                    $data['paied_amount'] = $paid_amount;
                    $data['status'] = $payment_status;

                    $invoice_id = geodir_create_invoice( $data );
                    
                    $upgrade_pkg_data['invoice_id'] = $invoice_id;
                }
                
                // invoice data
                $upgrade_pkg_data = maybe_serialize( $upgrade_pkg_data );
                
                $sql = $wpdb->prepare( "UPDATE " . GEODIR_CLAIM_TABLE . " SET `upgrade_pkg_id`=%d, `upgrade_pkg_data`=%s WHERE `pid`=%d", array( $geodir_upgrade_pkg, $upgrade_pkg_data, $wpdb->insert_id ) );
                $wpdb->query( $sql );
                
                if ( $invoice_id ) {
                    $new_post_status = get_post_status( $list_id );
                    
                    if ( $new_post_status != $post_status ) {
                        $post_update = array();
                        $post_update['ID'] = $list_id;
                        $post_update['post_status'] = $post_status;
                        
                        wp_update_post( $post_update );
                    }
                    
                    if ( $payment_status == 'confirmed' ) {
                        geodir_update_invoice_status( $invoice_id, $payment_status );
                    }
                    
                    do_action( 'geodir_payment_checkout_redirect', $invoice_id );
                }
            }
            
            $postlink = get_permalink( $claim_post->ID );
            $url = geodir_getlink( $postlink, array( 'geodir_claim_request' => 'success' ), false );
            wp_redirect($url);
            gd_die();
        }
    } else {
        wp_redirect(geodir_login_url());
        gd_die();
    }
}

function geodir_manage_claim_listing_actions()
{
	global $wpdb, $plugin_prefix;
	
	if(isset($_REQUEST['_wpnonce']) && isset($_REQUEST['id']) && $_REQUEST['id'] != '' && current_user_can( 'manage_options' )){
		
		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'claim_action_'.$_REQUEST['id'] ) )
				return;
		
		if(isset($_REQUEST['pagetype']) && $_REQUEST['pagetype'] == 'delete')
		{
			$pid = $_REQUEST['id'];
			
			$approvesql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where pid=%d", array($pid));
			
			$approveinfo = $wpdb->get_results($approvesql);

			$author_id = $approveinfo[0]->user_id;
			
			$post_id = $approveinfo[0]->list_id;
			
			$wpdb->query($wpdb->prepare("delete from ".GEODIR_CLAIM_TABLE." where pid=%d", array($pid)));
			
			$change_clamed = $wpdb->get_row($wpdb->prepare("select pid from ".GEODIR_CLAIM_TABLE." where list_id=%s and status='1'", array($post_id)));
			
			if(!$change_clamed)
			{
				geodir_save_post_meta($post_id, 'claimed','0');
				
				/**
				 * Called on claim request deleted.
				 *
				 * @since 1.2.2
				 *
				 * @param int $pid The claim id.
                 * @param int $post_id The post id.
				 */
				do_action('geodir_claim_request_delete', $pid, $post_id);
			}
			
			$msg = CLAIM_DELETED_SUCCESS;
			
			$msg = urlencode($msg);
			
			$location = admin_url('admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing&claim_success='.$msg);
			wp_redirect($location);
			gd_die();
		}
		
		if(isset($_REQUEST['pagetype']) && $_REQUEST['pagetype'] == 'approve')
		{
			$pid = $_REQUEST['id'];
			
			$approvesql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where pid=%d", array($pid));
			
			$approveinfo = $wpdb->get_results($approvesql);
			
			$post_id = $approveinfo[0]->list_id;
			
			$author_id = $approveinfo[0]->user_id;
			
			$claim_id = $pid;
			$old_status = $approveinfo[0]->status;
			$new_status = 1;
			
			$wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set status='2' where list_id=%s and user_id!=%s and status='1'", array($post_id,$author_id)));
			
			geodir_save_post_meta($post_id, 'claimed','1');
			
			$wpdb->query($wpdb->prepare("update $wpdb->posts set post_author=%d where ID=%d", array($author_id,$post_id))); 		
			$wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set status='1' where pid=%d", array($pid)));
			
			// Force to upgrade to complete claim listing
			$force_upgrade = geodir_claim_force_upgrade();
			$package_list = geodir_claim_payment_package_list( get_post_type( $post_id ) );
			
			if ( $force_upgrade && !empty( $package_list ) && !empty( $approveinfo ) && isset( $approveinfo[0]->upgrade_pkg_id ) ) {
				$geodir_upgrade_pkg = $approveinfo[0]->upgrade_pkg_id;
				$package_info = geodir_get_package_info_by_id( $geodir_upgrade_pkg );
				
				if ( !empty( $package_info ) ) {
					$claim_post_info = array();
					$claim_post_info['package_id'] = $geodir_upgrade_pkg;
				
					geodir_save_listing_payment( $post_id, $claim_post_info );
					
					$wpdb->query( $wpdb->prepare( "UPDATE `" . GEODIR_CLAIM_TABLE . "` SET `upgrade_pkg_id`='' WHERE `pid`=%d", array( $pid ) ) );
				}
			}
			
			/**
			 * Called on claim request status change.
			 *
			 * @since 1.2.2
			 *
			 * @param int $claim_id The claim id.
			 * @param int $new_status New claim status. Ex: 0 for pending, 1 for approved and 2 for rejected etc.
			 * @param int $old_status Old claim status. Ex: 0 for pending, 1 for approved and 2 for rejected etc.
			 */
			do_action('geodir_claim_request_status_change', $claim_id, $new_status, $old_status);
				 
			geodir_clientEmail($post_id,$author_id,'claim_approved'); /* email to client*/
			
			$msg = CLAIM_APPROVE_SUCCESS;
			
			$msg = urlencode($msg);
			
			$location = admin_url('admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing&claim_success='.$msg);
			
			wp_redirect($location);

			gd_die();
			
		}
		
		if(isset($_REQUEST['pagetype']) && $_REQUEST['pagetype'] == 'reject')
		{
			$pid = $_REQUEST['id'];
			
			$wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set status='2' where pid=%d", array($pid)));
			
			$approvesql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where pid=%d", array($pid));
			
			$approveinfo = $wpdb->get_results($approvesql);
			
			$post_id = $approveinfo[0]->list_id;
			
			$author_id = $approveinfo[0]->user_id;
			
			$claim_id = $pid;
			$old_status = $approveinfo[0]->status;
			$new_status = 2;
			
			/** This action is documented in geodir_claim_functions.php */
			do_action('geodir_claim_request_status_change', $claim_id, $new_status, $old_status);
			
			geodir_clientEmail($post_id,$author_id,'claim_rejected'); /* email to client*/
			
			$msg = CLAIM_REJECT_SUCCESS;
			
			$msg = urlencode($msg);
			
			$location = admin_url('admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing&claim_success='.$msg);
			
			wp_redirect($location);

			gd_die();
			
		}
		
		if(isset($_REQUEST['pagetype']) && $_REQUEST['pagetype'] == 'undo')
		{
			$pid = $_REQUEST['id'];
			
			$approvesql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where pid=%d", array($pid));
			
			$approveinfo = $wpdb->get_results($approvesql);
			
			$post_id = $approveinfo[0]->list_id;
			
			$author_id = $approveinfo[0]->org_authorid;
			
			$wpdb->query($wpdb->prepare("update $wpdb->posts set post_author=%d where ID=%d", array($author_id,$post_id)));
			
			$wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set status='2' where pid=%d", array($pid)));
			
			$change_clamed = $wpdb->get_row($wpdb->prepare("select pid from ".GEODIR_CLAIM_TABLE." where list_id=%s and status='1'", array($post_id)));
			
			if(!$change_clamed)
			{
				geodir_save_post_meta($post_id, 'claimed','0'); /*update claimed post data.*/
				
				/** This action is documented in geodir_claim_functions.php */
				do_action('geodir_claim_request_status_change', $pid, 0, $approveinfo[0]->status);
			}
			
			$location = admin_url('admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing&msg=reject');
			
			wp_redirect($location);
			gd_die();
		
		}
	
	}else{		
		wp_redirect(geodir_login_url());
		gd_die();
	}

}


function geodir_display_claim_messages(){

	if(isset($_REQUEST['claim_success']) && $_REQUEST['claim_success'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' .  esc_html($_REQUEST['claim_success']) . '</strong></p></div>';
					
	}
	
	if(isset($_REQUEST['claim_error']) && $_REQUEST['claim_error'] != '')
	{
			echo '<div id="claim_message_error" class="updated fade"><p><strong>' . esc_html($_REQUEST['claim_error']) . '</strong></p></div>';
				
	}
}

function geodir_adminEmail($page_id, $user_id, $message_type, $custom_1 = '') {
    $subject = '';
    $client_message = '';
    
    if ($message_type == 'claim_approved') {
        $subject = get_option('geodir_claim_approved_email_subject');
        $client_message = get_option('geodir_claim_approved_email_content');
    } else if ($message_type == 'claim_rejected') {
        $subject = get_option('geodir_claim_rejected_email_subject');
        $client_message = get_option('geodir_claim_rejected_email_content');
    } else if ($message_type == 'claim_requested') {
        $subject = get_option('geodir_claim_email_subject_admin'); 
        $client_message = get_option('geodir_claim_email_content_admin');
    } else if ($message_type == 'auto_claim') {
        $subject = get_option('geodir_claim_auto_approve_email_subject');
        $client_message = get_option('geodir_claim_auto_approve_email_content');
    }

	if (!empty($subject)) {
		$subject = __(stripslashes_deep($subject),'geodirclaim');
	}

	if (!empty($client_message)) {
		$client_message = __(stripslashes_deep($client_message),'geodirclaim');
	}

    $transaction_details = $custom_1;


    $approve_listing_link = '<a href="'.geodir_info_url(array("geodir_ptype"=>"verify","rs"=>$custom_1)).'">'.CLAIM_VERIFY_TEXT.'</a>';
    $fromEmail = get_bloginfo( 'admin_email' );
    $fromEmailName = get_site_emailName();

    if (function_exists('get_property_price_info_listing')) {
        $pkg_limit = get_property_price_info_listing($page_id);
        $alivedays = $pkg_limit['days'];
    } else {
        $alivedays = 'unlimited';
    }

    $productlink = get_permalink($page_id);

    $post_info = get_post($page_id);

    $post_date =  date('dS F,Y',strtotime($post_info->post_date));

    $listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';

    $site_login_url = geodir_login_url();

    $loginurl_link = '<a href="'.$site_login_url.'">login</a>';

	if (defined('ICL_LANGUAGE_CODE')){
		$siteurl = icl_get_home_url();
	}else{
		$siteurl = home_url();
	}

    $siteurl_link = '<a href="'.$siteurl.'">'.$fromEmailName.'</a>';

    $user_info = get_userdata($user_id);

    $user_email = $user_info->user_email;

    $display_name = $user_info->first_name;

    $user_login = $user_info->user_login;

    $number_of_grace_days = get_option('ptthemes_listing_preexpiry_notice_days');

    if ($number_of_grace_days == '' ) {
        $number_of_grace_days = 1;
    }

    $post_type = $post_info->post_type == 'event' ? 'event' : 'listing';

    $renew_link = '<a href="'.$siteurl.'?ptype=post_'.$post_type.'&renew=1&pid='.$page_id.'">'.CLAIM_RENEW_LINK.'</a>';

    $search_array = array('[#client_name#]','[#listing_link#]','[#posted_date#]','[#number_of_days#]','[#number_of_grace_days#]','[#login_url#]','[#username#]','[#user_email#]','[#site_name_url#]','[#renew_link#]','[#post_id#]','[#site_name#]','[#approve_listing_link#]','[#transaction_details#]');

    $replace_array = array($display_name,$listingLink,$post_date,$alivedays,$number_of_grace_days,$loginurl_link,$user_login,$user_email,$siteurl_link,$renew_link,$page_id,$fromEmailName,$approve_listing_link,$transaction_details);

    $client_message = str_replace($search_array,$replace_array,$client_message);

    $subject = str_replace($search_array,$replace_array,$subject);

    $headers  = array();
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: '.$fromEmailName.' <'.$fromEmail.'>';

    // strip slashes from subject & message text
    $subject = stripslashes_deep( $subject );
    $client_message = stripslashes_deep( $client_message );

    $sent = wp_mail($fromEmail, $subject, $client_message, $headers); // To site admin email
    if (!$sent && function_exists('geodir_error_log')) {
        if (is_array($fromEmail)) {
            $fromEmail = implode(',', $fromEmail);
        }
        $log_message = sprintf(
            __("Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirclaim'),
            $message_type,
            date_i18n('F j Y H:i:s', current_time('timestamp')),
            $fromEmail,
            $subject
        );
        geodir_error_log($log_message);
    }
}

function geodir_clientEmail($page_id, $user_id, $message_type, $custom_1 = '') {
    $subject = '';
    $client_message = '';
    if ($message_type == 'claim_approved') {
        $subject = get_option('geodir_claim_approved_email_subject');
        $client_message = get_option('geodir_claim_approved_email_content');
    } else if($message_type == 'claim_rejected') {
        $subject = get_option('geodir_claim_rejected_email_subject');
        $client_message = get_option('geodir_claim_rejected_email_content');
    } else if($message_type == 'claim_requested') {
        $subject = get_option('geodir_claim_email_subject');
        $client_message = get_option('geodir_claim_email_content');
    } else if($message_type == 'auto_claim') {
        $subject = get_option('geodir_claim_auto_approve_email_subject');
        $client_message = get_option('geodir_claim_auto_approve_email_content');
    }

	if (!empty($subject)) {
		$subject = __(stripslashes_deep($subject),'geodirclaim');
	}

	if (!empty($client_message)) {
		$client_message = __(stripslashes_deep($client_message),'geodirclaim');
	}

    $transaction_details = $custom_1;

    $approve_listing_link = '<a href="'.geodir_info_url(array("geodir_ptype"=>"verify","rs"=>$custom_1)).'">'.CLAIM_VERIFY_TEXT.'</a>';

    $fromEmail = get_option('site_email');
    $fromEmailName = get_site_emailName();

    if (function_exists('get_property_price_info_listing')) {
        $pkg_limit = get_property_price_info_listing($page_id);
        $alivedays = $pkg_limit['days'];
    } else {
        $alivedays = 'unlimited';
    }

    $productlink = get_permalink($page_id);

    $post_info = get_post($page_id);

    $post_date =  date('dS F,Y',strtotime($post_info->post_date));

    $listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';

    $site_login_url = geodir_login_url();

    $loginurl_link = '<a href="'.$site_login_url.'">login</a>';

	if (defined('ICL_LANGUAGE_CODE')){
		$siteurl = icl_get_home_url();
	}else{
		$siteurl = home_url();
	}

    $siteurl_link = '<a href="'.$siteurl.'">'.$fromEmailName.'</a>';

    $user_info = get_userdata($user_id);

    $user_email = $user_info->user_email;

    $display_name = $user_info->first_name;

    if (!$display_name)
        $display_name = get_the_author_meta( 'display_name', $user_id );

    $user_login = $user_info->user_login;

    $number_of_grace_days = get_option('ptthemes_listing_preexpiry_notice_days');

    if ($number_of_grace_days == '') {
        $number_of_grace_days = 1;
    }

    $post_type = $post_info->post_type == 'event' ? 'event' : 'listing';

    $renew_link = '<a href="'.$siteurl.'?ptype=post_'.$post_type.'&renew=1&pid='.$page_id.'">'.CLAIM_RENEW_LINK.'</a>';

    $search_array = array('[#client_name#]','[#listing_link#]','[#posted_date#]','[#number_of_days#]','[#number_of_grace_days#]','[#login_url#]','[#username#]','[#user_email#]','[#site_name_url#]','[#renew_link#]','[#post_id#]','[#site_name#]','[#approve_listing_link#]','[#transaction_details#]');

    $replace_array = array($display_name,$listingLink,$post_date,$alivedays,$number_of_grace_days,$loginurl_link,$user_login,$user_email,$siteurl_link,$renew_link,$page_id,$fromEmailName,$approve_listing_link,$transaction_details);

    $client_message = str_replace($search_array,$replace_array,$client_message);

    $subject = str_replace($search_array,$replace_array,$subject);

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: '.$fromEmailName.' <'.$fromEmail.'>' . "\r\n";

    // strip slashes from subject & message text
    $subject = stripslashes_deep( $subject );
    $client_message = stripslashes_deep( $client_message );  
    
    $sent = wp_mail($user_email, $subject, $client_message, $headers); // To client email
    if (!$sent && function_exists('geodir_error_log')) {
        $log_message = sprintf(
            __("Email from GeoDirectory failed to send.\nMessage type: %s\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'geodirclaim'),
            $message_type,
            date_i18n('F j Y H:i:s', current_time('timestamp')),
            $user_email,
            $subject
        );
        geodir_error_log($log_message);
    }
}

function geodir_claim_add_field_in_table(){
	if ( !get_option( 'geodir_claim_fields_upgrade' ) ) {
		geodir_add_column_if_not_exist( GEODIR_CLAIM_TABLE, 'upgrade_pkg_id', 'INT( 11 ) NOT NULL' );
		geodir_add_column_if_not_exist( GEODIR_CLAIM_TABLE, 'upgrade_pkg_data', 'TINYTEXT NOT NULL' );
		
		update_option( 'geodir_claim_fields_upgrade', '1' );
	}
}

/**
 * Check to see enabled force an upgrade to complete the claim listing procedure.
 *
 * This will check for payment addon active & option "Force an upgrade to complete 
 *      the claim listing procedure." enabled in claim listing settings. 
 *
 * @since 1.1.4
 *
 * @return bool True if enabled, else false.
 */
function geodir_claim_force_upgrade() {
	if ( get_option( 'geodir_claim_force_upgrade' ) == 'yes' && is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' ) ) {
		return true;
	}
	
	return false;
}

/**
 * Display price package list in claim lisitng form.
 *
 * @since 1.1.4
 *
 * @param  string $field Claim listing field name.
 * @return string Display price package list.
 */
function geodir_claim_after_claim_form_field( $field = '' ) {
	if ( $field == 'geodir_user_comments' && geodir_claim_force_upgrade() ) {
		$gd_post_types = geodir_get_posttypes();
		
		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '';
		$post_type = get_post_type( $post_id );
		
		if ( $post_type != '' && in_array( $post_type, $gd_post_types ) ) {
			$post_terms = wp_get_post_terms( $post_id, $post_type . 'category', array( 'fields' => 'ids' ) );
			$package_list = geodir_claim_payment_package_list( $post_type, $post_terms );
			
			if ( empty( $package_list ) ) {
				return;
			}
			
			?>
			<div id="gd_claim_pkgs" class="row clearfix gd-claim-pkgs gd-chosen-outer">
				<label><?php _e( 'Select Package', 'geodirclaim' );?> : <span>*</span></label>
				<select name="geodir_claim_pkg" id="geodir_claim_pkg" field_type="select" class="is_required chosen_select">
				<?php foreach ( $package_list as $package ) { ?>
				<option value="<?php echo $package->pid; ?>"><?php echo stripslashes_deep( $package->title_desc ); ?></option>
				<?php } ?>
				</select>
				<span class="message_error2" id="geodir_claim_pkgInfo"></span>
			</div>
			<script type="text/javascript">jQuery("#geodir_claim_form").addClass('gd-claimfrm-upgrade');jQuery(".chosen_select", "#geodir_claim_form").chosen({"disable_search":true});</script>
			<?php
		}
	}
}

function geodir_claim_payment_package_list( $post_type, $post_cats = NULL, $exclude_free = true ) {
	if ( !function_exists( 'geodir_package_list_info' ) ) {
		return NULL;
	}
	
	$package_list = array();
	
	$packages = geodir_package_list_info( $post_type );
	
	if ( !empty( $packages ) ) {
		$post_cats = !empty( $post_cats ) && is_array( $post_cats ) && !is_wp_error( $post_cats ) ? $post_cats : array();
		
		foreach ( $packages as $package ) {
			if ( !(float)$package->amount > 0 && $exclude_free ) {
				continue;
			}
			
			if ( !empty( $post_cats ) && !empty( $package->cat ) && ( $package_cats = explode( ',', $package->cat ) ) ) {
				$package_cats = array_map( 'trim', $package_cats );
				
				if ( !empty( $package_cats ) && is_array( $package_cats ) ) {
					$exclude = false;
					
					foreach ( $post_cats as $post_cat ) {
						if ( in_array( $post_cat, $package_cats ) ) {
							$exclude = true;
						} else {
							$exclude = false;
							break;
						}
					}
					
					if ( $exclude ) {
						continue;
					}
				}
			}
			
			$package_list[] = $package;
		}
	}
	
	return $package_list;
}

function geodir_claim_allow_pay_for_invoice( $allow, $invoice_info ) {
	if ( !empty( $invoice_info->invoice_type ) && $invoice_info->invoice_type == 'claim_listing' ) {
		if ( in_array( $invoice_info->status, array( 'failed', 'pending' ) ) ) {
			$allow = true;	
		}
	}
	return $allow;
}

function geodir_claim_get_info( $id ) {
	global $wpdb;
	
	$sql = $wpdb->prepare("SELECT * FROM " . GEODIR_CLAIM_TABLE . " WHERE pid=%d", array( (int)$id ) );
	$result = $wpdb->get_row( $sql );
	
	return $result;
}

function geodir_claim_invoice_callback_claim_listing( $invoice_id, $new_status, $old_status = 'pending', $subscription = false ) {
	global $wpdb;
	
	$invoice_info = geodir_get_invoice( $invoice_id );
	
	if ( !( !empty( $invoice_info ) && $new_status != $old_status ) ) {
		return false;
	}
	
	$invoice_data = maybe_unserialize( $invoice_info->invoice_data );
	
	if ( empty( $invoice_data ) ) {
		return false;
	}
	
	$claim_id 	= isset( $invoice_data['claim_id'] ) ? $invoice_data['claim_id'] : NULL;
	$package_id = isset( $invoice_data['package_id'] ) ? $invoice_data['package_id'] : $invoice_data->package_id;
	
	$claim_info = geodir_claim_get_info( $claim_id );
	if ( empty( $claim_info ) ) {
		return false;
	}
	$post_id 		= (int)$claim_info->list_id;
	$author_id 		= (int)$claim_info->user_id;
	
	$gd_post_info 	= geodir_get_post_info( $post_id );
	
	if ( empty( $gd_post_info ) ) {
		return false;
	}
			
	if ( $new_status == 'confirmed' ) {
		$sql = $wpdb->prepare( "UPDATE " . GEODIR_CLAIM_TABLE . " SET status='2' WHERE list_id=%s AND user_id!=%s AND status='1'", array( $post_id, $author_id ) );
		$wpdb->query($sql);
		
		geodir_save_post_meta( $post_id, 'claimed', '1' );
		geodir_save_post_meta( $post_id, 'post_status', 'publish' );
		
		$sql = $wpdb->prepare( "UPDATE $wpdb->posts SET post_author=%d WHERE ID=%d", array( (int)$author_id,(int)$post_id ) );
		$wpdb->query( $sql );
		
		$sql = $wpdb->prepare( "UPDATE " . GEODIR_CLAIM_TABLE . " SET status='1', `upgrade_pkg_id`='' WHERE pid=%d", array( $claim_id ) );
		$wpdb->query( $sql );
		
		$claim_post_info = array();
		$claim_post_info['package_id'] = $package_id;
	
		geodir_save_listing_payment( $post_id, $claim_post_info );
		
		$post_status = get_post_status( $post_id );
		if ( $post_status != 'publish' ) {
			$post_update = array();
			$post_update['ID'] = $post_id;
			$post_update['post_status'] = 'publish';
						
			wp_update_post( $post_update );
		}
				
		/** This action is documented in geodir_claim_functions.php */
		do_action('geodir_claim_request_status_change', $claim_id, 1, $claim_info->status);
		
		// Notify to client.
		geodir_clientEmail( $post_id, $author_id, 'claim_approved' );
	} else if ( $new_status == 'pending' ) {
	} else if ( $new_status == 'canceled' ) {
	} else if ( $new_status == 'failed' ) {
	} else if ( $new_status == 'onhold' ) {
	}
	
	return true;
}

function geodir_claim_messsage_on_login_form() {
    if (!is_user_logged_in() && !empty($_REQUEST['redirect_to']) ) {
        if (strpos($_REQUEST['redirect_to'], '?gd_go=claim') !== false || strpos($_REQUEST['redirect_to'], '&gd_go=claim') !== false) {
            echo '<script type="text/javascript">jQuery(function(){if(typeof gd_signin_to_claim === \'undefined\'){jQuery(\'.login_form_l\').before(\'<p class="error_msg_fix">' . esc_attr(__('Please login/signup to claim your listing.','geodirclaim')) . '</p>\');gd_signin_to_claim=1;}});</script>';
        }
    }
}

/**
 * @since 1.2.9
 */
function geodir_claim_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}
