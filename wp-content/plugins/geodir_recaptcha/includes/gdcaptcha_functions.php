<?php
/**
 * Contains functions related to GD captcha plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
	
/**
 * plugin activation hook.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_activation() {
	if ( get_option( 'geodir_installed' ) ) {
		$options = geodir_resave_settings( geodir_recaptcha_settings() );
		geodir_update_options( $options, true );
		add_option( 'geodir_recaptcha_activation_redirect', 1 );
	}
}

/**
 * plugin deactivation hook.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_deactivation() {
}

/**
 * plugin activation redirect.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_activation_redirect() {
	if ( get_option( 'geodir_recaptcha_activation_redirect', false ) ) {
		delete_option( 'geodir_recaptcha_activation_redirect' );
		
		wp_redirect( admin_url( 'admin.php?page=geodirectory&tab=geodir_recaptcha&subtab=gdcaptcha_settings' ) ); 
	}
}

/**
 * check GeoDirectory plugin installed.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $plugin Path to a plugin file or directory, relative to the plugins directory (without the leading and trailing slashes).
 */
function geodir_recaptcha_plugin_activated( $plugin ) {
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
		
		wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir-recaptcha' ) );
	}
}

/**
 * Add admin js and css.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @global string $pagenow Current page name.
 */
function geodir_recaptcha_admin_scripts() {
	global $pagenow;
	
	if ( $pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'geodir_recaptcha' ) {
		// Style
		wp_register_style( 'geodir_recaptcha_style', GEODIR_RECAPTCHA_PLUGIN_URL . '/css/geodir-recaptcha.css' );
		wp_enqueue_style( 'geodir_recaptcha_style' );
	}
}

/**
 * This function is used to create GeoDirectory re-captcha navigation.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param array $tabs GeoDirectory Settings Tab list.
 * @return array Modified GeoDirectory Settings Tab list.
 */
function geodir_recaptcha_tabs_array( $tabs ) {
	$gd_captcha_tabs = array();
	$gd_captcha_tabs['label'] = __('Re-Captcha Settings', 'geodir-recaptcha');
	$gd_captcha_tabs['subtabs'] = array(
										array(
											'subtab' => 'gdcaptcha_settings',
											'label' => __( 'Re-Captcha Settings', 'geodir-recaptcha' ),
											'form_action' => admin_url( 'admin-ajax.php?action=geodir_recaptcha_ajax' )
										)
									);
	
	// hook for geodirectory re-captcha tabs

    /**
     * Filters the recaptcha tabs.
     *
     * @since 1.0.0
     * @package GeoDirectory_ReCaptcha
     */
	$gd_captcha_tabs = apply_filters( 'geodir_recaptcha_tabs', $gd_captcha_tabs );
	
	$tabs['geodir_recaptcha'] = $gd_captcha_tabs;
	
	return $tabs;
}//https://www.google.com/recaptcha/admin#list

/**
 * ReCaptcha setting options.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param array $options GeoDirectory settings options array.
 * @return array Modified GeoDirectory settings options array.
 */
function geodir_recaptcha_settings( $options = array() ) {
	$options[] = array('name' => __( 'Re-Captcha Settings', 'geodir-recaptcha' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'gdcaptcha_subtab_settings' );
	
	// general settings
	$options[] = array('name' => __( 'Google reCAPTCHA Keys', 'geodir-recaptcha' ), 'type' => 'sectionstart', 'id' => 'gdcaptcha_settings_keys' );
	
	$options[] = array(  
					'name' => __( 'Site key', 'geodir-recaptcha' ),
					'desc' => '',
					'id' => 'geodir_recaptcha_site_key',
					'type' => 'text',
					'css' => 'min-width:350px;',
					'std' => '',
					'desc' 	=> __( '*Required - Enter Re-Captcha site key that you get after site registration at <a target="_blank" href="https://www.google.com/recaptcha/admin#list">here</a>.', 'geodir-recaptcha' ),
				);
	
	$options[] = array(  
					'name' => __( 'Secret key', 'geodir-recaptcha' ),
					'desc' => '',
					'id' => 'geodir_recaptcha_secret_key',
					'type' => 'text',
					'css' => 'min-width:350px;',
					'std' => '',
					'desc' 	=> __( '*Required - Enter Re-Captcha secret key that you get after site registration at <a target="_blank" href="https://www.google.com/recaptcha/admin#list">here</a>.', 'geodir-recaptcha' ),
				);
	
	$options[] = array( 'type' => 'sectionend', 'id' => 'gdcaptcha_settings_general' );
	
	// activate options
	$options[] = array('name' => __( 'Activate Options', 'geodir-recaptcha' ), 'type' => 'sectionstart', 'id' => 'gdcaptcha_settings_options' );
	
	$options[] = array(
		'name' => __( 'Enable Google reCAPTCHA for', 'geodir-recaptcha' ),
		'desc' => __( 'WordPress Registration', 'geodir-recaptcha' ),
		'id' => 'geodir_recaptcha_wp_registration',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array(
		'name' => '',
		'desc' => __( 'GeoDirectory Registration', 'geodir-recaptcha' ),
		'id' => 'geodir_recaptcha_registration',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> ''
	);
	$options[] = array(  
		'name' => '',
		'desc' => __( 'GeoDirectory Add Listing', 'geodir-recaptcha' ),
		'id' => 'geodir_recaptcha_add_listing',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> ''
	);
	if ( is_plugin_active( 'geodir_claim_listing/geodir_claim_listing.php' ) ) { // check to see claim listing addon active
		$options[] = array(  
			'name' => '',
			'desc' => __( 'GeoDirectory Claim Listing', 'geodir-recaptcha' ),
			'id' => 'geodir_recaptcha_claim_listing',
			'std' => '0',
			'type' => 'checkbox',
			'checkboxgroup'	=> ''
		);
	}
	$options[] = array(  
		'name' => '',
		'desc' => __( 'GeoDirectory Comments', 'geodir-recaptcha' ),
		'id' => 'geodir_recaptcha_comments',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> ''
	);
	$options[] = array(  
		'name' => '',
		'desc' => __( 'GeoDirectory Send To Friend', 'geodir-recaptcha' ),
		'id' => 'geodir_recaptcha_send_to_friend',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> ''
	);
	$options[] = array(  
		'name' => '',
		'desc' => __( 'GeoDirectory Send Enquiry', 'geodir-recaptcha' ),
		'id' => 'geodir_recaptcha_send_enquery',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> ( !class_exists( 'BuddyPress' ) ? 'end' : '' )
	);
	if ( class_exists( 'BuddyPress' ) ) { // check to see buddypress addon active
		$options[] = array(  
			'name' => '',
			'desc' => __( 'BuddyPress Registration', 'geodir-recaptcha' ),
			'id' => 'geodir_recaptcha_buddypress',
			'std' => '0',
			'type' => 'checkbox',
			'checkboxgroup'	=> 'end'
		);
	}
	// user roles
	$count = 0;
	$roles = get_editable_roles();
	foreach ( $roles as $role => $data ) {
		$count++;
		$checkboxgroup = ( $count == 1 ? 'start' : ( $count == count( $roles ) ? 'end' : '' ) );
		$options[] = array(  
			'name' => ( $count == 1 ? __( 'Disable Google reCAPTCHA for', 'geodir-recaptcha' ) : '' ),
			'desc' => __( $data['name'], 'geodir-recaptcha' ),
			'id' => 'geodir_recaptcha_role_' . $role,
			'std' => '0',
			'type' => 'checkbox',
			'checkboxgroup'	=> $checkboxgroup
		);
	}
	$options[] = array(  
					'name' => __( 'Captcha Title', 'geodir-recaptcha' ),
					'desc' => '',
					'id' => 'geodir_recaptcha_title',
					'type' => 'text',
					'css' => 'min-width:350px;',
					'std' => '',
					'desc' 	=> __( 'Captcha title to be displayed above captcha code, leave blank to hide.', 'geodir-recaptcha' ),
				);
	$options[] = array(  
		'name' => __( 'Captcha Theme', 'geodir-recaptcha' ),
		'desc' 		=> __( 'Select color theme of captcha widget. <a target="_blank" href="https://developers.google.com/recaptcha/docs/display#render_param">Learn more</a>', 'geodir-recaptcha' ),
		'tip' 		=> '',
		'id' 		=> 'geodir_recaptcha_theme',
		'css' 		=> 'min-width:120px;',
		'std' 		=> 'light',
		'type' 		=> 'select',
		'options' => array( 
			'light' => __( 'Light', 'geodir-recaptcha' ),
			'dark' => __( 'Dark', 'geodir-recaptcha' ),
		)
	);

    $options[] = array(
        'name' => __( 'Captcha Version', 'geodir-recaptcha' ),
        'desc' 		=> __( 'Select version for captcha widget. <a target="_blank" href="https://developers.google.com/recaptcha/docs/versions#checkbox">Learn more</a><br /><span style="color:red;">V2 keys will not work with invisible recaptcha, you will have to create new ones.</span>', 'geodir-recaptcha' ),
        'tip' 		=> '',
        'id' 		=> 'geodir_recaptcha_client_version',
        'css' 		=> 'min-width:120px;',
        'std' 		=> 'light',
        'type' 		=> 'select',
        'options' => array(
            'v2' => __( 'reCAPTCHA v2', 'geodir-recaptcha' ),
            'invisible' => __( 'Invisble reCAPTCHA', 'geodir-recaptcha' ),
        )
    );
	
	$options[] = array( 'type' => 'sectionend', 'id' => 'gdcaptcha_settings_options' );
	
	// hook for custom map general options
    /**
     * Filters the recaptcha setting options.
     *
     * @since 1.0.0
     * @package GeoDirectory_ReCaptcha
     */
	$options = apply_filters( 'geodir_recaptcha_settings', $options );
	
	return $options;
}

/**
 * Get current subtab name.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $default The default value to return when subtab empty.
 * @return string Sub tab name.
 */
function geodir_recaptcha_current_subtab( $default = '' ) {
	$subtab = isset( $_REQUEST['subtab'] ) ? $_REQUEST['subtab'] : '';
	
	if ($subtab=='' && $default!='') {
		$subtab = $default;
	}
	
	return $subtab;
}

/**
 * Adds ReCaptcha settings tab content.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_recaptcha_tab_content() {
	global $wpdb;
	
	$subtab = geodir_recaptcha_current_subtab();
	
	if ( $subtab == 'gdcaptcha_settings' ) {	
		add_action( 'geodir_admin_option_form', 'geodir_recaptcha_option_form' );
	}
}

/**
 * ReCaptcha settings form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $current_tab Current Tab name.
 */
function geodir_recaptcha_option_form ( $current_tab ) {
	$current_tab = geodir_recaptcha_current_subtab();
	
	geodir_recaptcha_get_option_form( $current_tab );
}

/**
 * main ajax function.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_ajax() {
	$subtab = geodir_recaptcha_current_subtab();
	
	if ( $subtab == 'gdcaptcha_settings' ) {
		geodir_update_options( geodir_recaptcha_settings() );
		
		$msg = urlencode_deep( __( 'Settings saved.', 'geodir-recaptcha' ) );
		
		wp_redirect( admin_url() . 'admin.php?page=geodirectory&tab=geodir_recaptcha&subtab=gdcaptcha_settings&success_msg=' . $msg );
		gd_die();
	}
}

/**
 * Init GD ReCaptcha plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @global string $pagenow Current page name.
 * @param bool $admin Is this admin page?.
 * @param bool $admin_ajax Is this a admin ajax request?
 */
function geodir_recaptcha_init( $admin=false, $admin_ajax = false ) {
    global $pagenow;
	$admin = is_admin();
	$admin_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

	if ( geodir_recaptcha_check_role() ) { // disable captcha as per user role settings
		return;
	}

	$load_css = false;
	// WP registration form
	if ( !$admin && get_option( 'geodir_recaptcha_wp_registration' ) ) {
        // Make sure jQuery is available
        if ( $pagenow == 'wp-login.php' || $pagenow == 'wp-signup.php') {
            wp_enqueue_script('jquery');
        }
		if ( !is_multisite() ) {
			// general wordpress signup
			add_action( 'register_form', 'geodir_recaptcha_registration_form' );
		} else {
			add_action( 'signup_extra_fields', 'geodir_recaptcha_registration_form' );
		}

        add_action( 'register_post', 'geodir_recaptcha_registration_check', 0, 3 );

		$load_css = true;
	}

	// GD registration form
	if ( !$admin && get_option( 'geodir_recaptcha_registration' ) ) {
		// general geodirectory signup
		add_action( 'social_connect_form', 'geodir_recaptcha_registration_form' );
		add_action( 'register_post', 'geodir_recaptcha_registration_check', 0, 3 );
		
		$load_css = true;
	}
	
	// add listing form
	if ( get_option( 'geodir_recaptcha_add_listing' ) ) {


		if(!is_admin() && isset($_REQUEST['post_title']) && isset($_REQUEST['listing_type']) && !isset( $_REQUEST['pid'])){
			geodir_recaptcha_check( 'add_listing' );
		}
		
		$post_info = NULL;
		if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
			$post_id = $_REQUEST['pid'];
			$post_info = get_post( $post_id );
		}
		
		if ( empty( $post_info ) ) {
			add_action( 'geodir_after_main_form_fields', 'geodir_recaptcha_add_listing_form', 0 );
			$load_css = true;
		}
	}
	
	// comments form
	if ( !$admin && get_option( 'geodir_recaptcha_comments' ) ) {
		//add_action( 'comment_form_after_fields', 'geodir_recaptcha_comments_form', 100 );
		//add_action( 'comment_form_logged_in_after', 'geodir_recaptcha_comments_form', 100 );
		add_action( 'comment_form', 'geodir_recaptcha_comments_form', 100 );

		add_action( 'pre_comment_on_post', 'geodir_recaptcha_comments_check', 0, 1 );
		add_action( 'comment_on_trash', 'geodir_recaptcha_comments_check', 0, 1 );
		add_action( 'comment_on_draft', 'geodir_recaptcha_comments_check', 0, 1 );
		add_action( 'comment_on_password_protected', 'geodir_recaptcha_comments_check', 0, 1 );
		$load_css = true;
	}
	
	// send to form
	if ( ( !$admin || $admin && $admin_ajax ) && get_option( 'geodir_recaptcha_send_to_friend' ) ) {
		if(isset($_REQUEST['sendact']) && $_REQUEST['sendact']=='email_frnd'){
			geodir_recaptcha_check( 'send_to_frnd' );
		}
		add_action( 'geodir_after_stf_form_field', 'geodir_recaptcha_send_to_friend_form' );
		$load_css = true;
	}
	
	// send enquiry form
	if ( ( !$admin || $admin && $admin_ajax ) && get_option( 'geodir_recaptcha_send_enquery' ) ) {
		if(isset($_REQUEST['sendact']) && $_REQUEST['sendact']=='send_inqury'){
			geodir_recaptcha_check( 'send_inqury' );
		}
		add_action( 'geodir_after_inquiry_form_field', 'geodir_recaptcha_send_enquery_form' );
		$load_css = true;
	}
		
	// claim listing form
	if ( ( !$admin || $admin && $admin_ajax ) && get_option( 'geodir_recaptcha_claim_listing' ) ) {
		if(isset($_REQUEST['geodir_sendact']) && $_REQUEST['geodir_sendact']=='add_claim'){
			geodir_recaptcha_check( 'add_claim' );
		}
		add_action( 'geodir_after_claim_form_field', 'geodir_recaptcha_claim_listing_form' );
		$load_css = true;
	}
	
	// buddypress registration form
	if ( !$admin && get_option( 'geodir_recaptcha_buddypress' ) ) {
		add_action( 'bp_before_registration_submit_buttons', 'geodir_recaptcha_bp_registration_form' );
		add_action( 'bp_signup_validate', 'geodir_recaptcha_bp_registration_check' );
		
		$load_css = true;
	}
	
	if ( $load_css ) {
		wp_register_style( 'gd-captcha-style', GEODIR_RECAPTCHA_PLUGIN_URL . '/css/gd-captcha-style.css', array(), GEODIR_RECAPTCHA_VERSION);
		wp_enqueue_style( 'gd-captcha-style' );
	}
	

    /**
     * Functions added to this hook will be executed after init.
     *
     * @since 1.0.0
     * @package GeoDirectory_ReCaptcha
     */
	do_action( 'geodir_recaptcha_init' );
}

/**
 * registration form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_registration_form() {
	$content = geodir_recaptcha_display( 'registration' );
	
	if ( $content ) {
		echo $content;
	}
}

/**
 * check captcha for registration.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $user_login Username.
 * @param string $user_email User email.
 * @param string $errors Registration errors.
 */
function geodir_recaptcha_registration_check( $user_login='', $user_email='', $errors='' ) {
	geodir_recaptcha_check( 'registration', $errors );
}

/**
 * add listing form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_add_listing_form() {
    $captcha_version = get_option( 'geodir_recaptcha_client_version' );
	?>
	<span class="gdcaptcha-err geodir_form_row clearfix"></span>
	<?php
	echo geodir_recaptcha_display( 'add_listing', 'geodir_form_row clearfix' );
	?>
<script type="text/javascript">
    function add_listing_form_check_delegate(ele) {

        var isValidate = true;
        var $form = this;

        jQuery(this).find(".required_field:visible").each(function(){
            jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload").each(function(){

                if(jQuery(this).is('.chosen_select, .geodir_location_add_listing_chosen')){
                    var chosen_ele = jQuery(this);
                    jQuery('#'+jQuery(this).attr('id')+'_chzn').mouseleave(function(){
                        validate_field( chosen_ele );
                    });

                }
                if(!validate_field( this )) {
                    isValidate = validate_field(this);
                }
            });
        });

        return false;
    }
<?php if ( $captcha_version != 'invisible' ) : ?>
jQuery(document).ready(function() {
	jQuery(document).delegate("#propertyform", "submit", function(ele){
        jQuery(this).find('.gdcaptcha-err').hide();
        var isValidate = true;
        var $form = this;

        jQuery(this).find(".required_field:visible").each(function(){
            jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload").each(function(){

                if(jQuery(this).is('.chosen_select, .geodir_location_add_listing_chosen')){
                    var chosen_ele = jQuery(this);
                    jQuery('#'+jQuery(this).attr('id')+'_chzn').mouseleave(function(){
                        validate_field( chosen_ele );
                    });

                }
                if(!validate_field( this )) {
                    isValidate = validate_field(this);
                }
            });
        });

        if(isValidate) {
            var ret = jQuery(this).find('.g-recaptcha-response').val() ? true : '';
            if(ret == true) {
                return true;
            } else {
                var divId = jQuery($form).find('.gd-captcha-render').attr('id');
                if ( divId ) {
                    try {
                        grecaptcha.reset(jQuery('#'+divId.toString()));
                    } catch(err) {
                        jQuery(this).find('.gdcaptcha-err').html(err).show();
                        console.log(err);
                    }
                }
            }
        } else {
            jQuery(window).scrollTop(jQuery(".geodir_message_error:visible:first").closest('.required_field').offset().top);
        }
        return false;
	});
});
<?php endif ?>

</script>
	<?php
}

/**
 * comments form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_comments_form() {
	echo geodir_recaptcha_display( 'comments' );

    if ( get_option( 'geodir_recaptcha_client_version' ) != 'invisible' ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                var parentForm = jQuery('#gdcaptcha_comments').closest('form');
                var findEle = jQuery(parentForm).find('.gd-captcha-comments');
                jQuery(parentForm).find('.gd-captcha-comments').remove();
                jQuery(parentForm).find('#submit').parent().before(findEle);
            });
        </script>
        <?php
    }
}

/**
 * check captcha for comments.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_comments_check() {
	if ( isset( $_POST['comment'] ) && trim( $_POST['comment'] ) != '' ) {
		geodir_recaptcha_check( 'comments' );
	}
}

/**
 * send to friend form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $field GeoDirectory field name.
 */
function geodir_recaptcha_send_to_friend_form( $field = 'frnd_comments' ) {
    $captcha_version = get_option( 'geodir_recaptcha_client_version' );

	if ( $field == 'frnd_comments' ) {
		?>
		<span class="gdcaptcha-err"></span>
		<?php
		echo geodir_recaptcha_display( 'send_to_friend', 'row  clearfix' );
		?>
<script type="text/javascript">
<?php if ( $captcha_version != 'invisible' ) : ?>
jQuery(document).ready(function() {
	jQuery("form#send_to_frnd").submit(function(ele) {
        jQuery(this).find('.gdcaptcha-err').hide();
        var frmValid = true;
        jQuery(this).find(".is_required:visible").each(function() {
            if(!geodir_popup_validate_field(this)) {
                frmValid = geodir_popup_validate_field(this);
            }
        });

        if(frmValid) {
            var ret = jQuery(this).find('.g-recaptcha-response').val() ? true : '';
            if(ret == true) {
                return true;
            } else {
                var divId = jQuery(this).find('.gd-captcha-render').attr('id');
                if ( divId ) {
                    try {
                        grecaptcha.reset(jQuery('#'+divId.toString()));
                    } catch(err) {
                        jQuery(this).find('.gdcaptcha-err').html(err).show();
                        console.log(err);
                    }
                }
            }
        }
        return false;
	});
});
<?php endif ?>
</script>
		<?php
	}
}

/**
 * send enquiry form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $field GeoDirectory field name.
 */
function geodir_recaptcha_send_enquery_form( $field = 'inq_msg' ) {
    $captcha_version = get_option( 'geodir_recaptcha_client_version' );
	if ( $field == 'inq_msg' ) {
		?>
		<span class="gdcaptcha-err"></span>
		<?php
		echo geodir_recaptcha_display( 'send_enquery', 'row  clearfix' );
		?>
<?php if ( $captcha_version != 'invisible' ) : ?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#agt_mail_agent").submit(function(ele) { 
		jQuery(this).find('.gdcaptcha-err').hide();
		var frmValid = true;
		jQuery(this).find(".is_required:visible").each(function() {
			if(!geodir_popup_validate_field(this)) {
				frmValid = geodir_popup_validate_field(this);
			}
		});
		if(frmValid) {
			var ret = jQuery(this).find('.g-recaptcha-response').val() ? true : '';
			if(ret == true) {
				return true;
			} else {
				var divId = jQuery(this).find('.gd-captcha-render').attr('id');
				if ( divId ) {
					try {
						grecaptcha.reset(jQuery('#'+divId.toString()));
					} catch(err) {
						jQuery(this).find('.gdcaptcha-err').html(err).show();
						console.log(err);
					}
				}
			}
		}
		return false;
	});
});
</script>
<?php endif ?>
		<?php
	}
}

/**
 * claim listing form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @global object $bp BuddyPress object.
 *
 * @param string $field GeoDirectory field name.
 */
function geodir_recaptcha_claim_listing_form( $field = 'geodir_user_comments' ) {
	$captcha_version = get_option( 'geodir_recaptcha_client_version' );
	if ( $field == 'geodir_user_comments' ) {
		?>
		<span class="gdcaptcha-err"></span>
		<?php
		echo geodir_recaptcha_display( 'claim_listing', 'row  clearfix' );
		?>
		<?php if ( $captcha_version != 'invisible' ) : ?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#geodir_claim_form").submit(function(ele) { 
		jQuery(this).find('.gdcaptcha-err').hide();
		var wpdm_validate = true;
		jQuery(this).find(".is_required:visible").each(function() {
			if(!geodir_claim_popup_validate_field(this)) {
				wpdm_validate = geodir_claim_popup_validate_field(this);
			}
		});
		if(wpdm_validate) {
			var ret = jQuery(this).find('.g-recaptcha-response').val() ? true : '';
			if(ret == true) {
				return true;
			} else {
				var divId = jQuery(this).find('.gd-captcha-render').attr('id');
				if ( divId ) {
					try {
						grecaptcha.reset(jQuery('#'+divId.toString()));
					} catch(err) {
						jQuery(this).find('.gdcaptcha-err').html(err).show();
						console.log(err);
					}
				}
			}
		}
		return false;
	});
});

</script>
		<?php endif ?>
		<?php
	}
}

/**
 * verify captcha.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @global string $pagenow
 * @param string $form The form name.
 * @param string $errors Form errors.
 */
function geodir_recaptcha_check( $form = '', $errors='' ) {
    global $pagenow;
	$site_key = get_option( 'geodir_recaptcha_site_key' );
	$secret_key = get_option( 'geodir_recaptcha_secret_key' );

    // Don't check captcha on WP registration if option is unchecked
	if ( $pagenow == 'wp-login.php' && !get_option( 'geodir_recaptcha_wp_registration' ) ) {
	    return;
    }

    // Don't check captcha on GD registration if option is unchecked
    if ( $pagenow != 'wp-login.php' && !get_option( 'geodir_recaptcha_registration' ) ) {
        return;
    }
		
	if ( !( strlen( $site_key ) > 10 && strlen( $secret_key ) > 10 ) ) {
		return;
	}
	
	if ( !class_exists( 'ReCaptcha' ) ) {
		require_once( GEODIR_RECAPTCHA_PLUGIN_PATH . '/lib/recaptchalib.php' );
	}
	
	$reCaptcha = new ReCaptcha( $secret_key );
	
	$recaptcha_value = isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';
	$response = $reCaptcha->verifyResponse( $_SERVER['REMOTE_ADDR'], $recaptcha_value );

	$invalid_captcha = !empty( $response ) && isset( $response->success ) && $response->success ? false : true;
	
	if ( !$invalid_captcha ) {
		return;
	} else {
		if ( $form == 'bp_registration' ) {
			global $bp;
			$bp->signup->errors['gd_recaptcha_field'] = __( 'You have entered an incorrect CAPTCHA value.', 'geodir-recaptcha' );
			return;
		} else {
			if ( !empty( $errors ) && is_object( $errors ) ) {
				$errors->add( 'invalid_captcha', __( '<strong>ERROR</strong>: You have entered an incorrect CAPTCHA value.', 'geodir-recaptcha' ) );
			} else {
				wp_die( __( '<strong>ERROR</strong>: You have entered an incorrect CAPTCHA value. Click the BACK button on your browser, and try again.', 'geodir-recaptcha' ) );
			}
		}
	}
}



/**
 * buddypress registration form.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */
function geodir_recaptcha_bp_registration_form() {
	$content = geodir_recaptcha_display( 'bp_registration' );
	
	if ( $content ) {
		echo $content;
	}
}

/**
 * check captcha for buddypress registration.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $user_login Username.
 * @param string $user_email User email.
 * @param string $errors Registration errors.
 */
function geodir_recaptcha_bp_registration_check( $user_login='', $user_email='', $errors='' ) {
	geodir_recaptcha_check( 'bp_registration', $errors );
}

/**
 * captcha theme, see https://developers.google.com/recaptcha/docs/display#render_param
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @return mixed|void
 */
function geodir_recaptcha_theme() {
	$theme = get_option( 'geodir_recaptcha_theme', 'light' );

    /**
     * Filters the recaptcha theme.
     *
     * @since 1.0.0
     * @package GeoDirectory_ReCaptcha
     */
	$theme = apply_filters( 'geodir_recaptcha_captcha_theme', $theme );
	
	return $theme;
}

/**
 * check role of user and disable captcha.
 *
 * @since 1.0.0
 * @since 1.1.4 Fix: Network admin should be treated as an administrator role.
 * @package GeoDirectory_ReCaptcha
 *
 * @global object $current_user Current user object.
 *
 * @return bool
 */
function geodir_recaptcha_check_role() {
	if ( !is_user_logged_in() ) { // visitors
		return false;
	}
	
	global $current_user;
	$role = !empty( $current_user ) && isset( $current_user->roles[0] ) ? $current_user->roles[0] : '';
	
	if ( is_multisite() && is_super_admin( $current_user->ID ) ) {
		$role = 'administrator';
	}
	
	if ( $role != '' && (int)get_option( 'geodir_recaptcha_role_' . $role ) == 1 ) { // disable captcha
		return true;
	}
	else { // enable captcha
		return false;
	}
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.1.1
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_recaptcha_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(dirname(__FILE__)));
    
    return $settings;
}