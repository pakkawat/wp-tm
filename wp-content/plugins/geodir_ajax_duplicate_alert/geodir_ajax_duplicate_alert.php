<?php
/*
Plugin Name: GeoDirectory Ajax Duplicate Alert
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Ajax Duplicate Alert plugin.
Version: 1.1.9
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65088
*/

global $wpdb,$plugin_prefix,$geodir_addon_list;
if (!defined('GEODIRDUPLICATEALERT_VERSION')) define('GEODIRDUPLICATEALERT_VERSION','1.1.9');
if (!defined('GEODIRDUPLICATEALERT_TEXTDOMAIN')) define('GEODIRDUPLICATEALERT_TEXTDOMAIN','geodir_duplicatealert');

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
	if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
		require_once('gd_update.php'); // require update script
	}
}

// GEODIRECTORY CORE ALIVE CHECK START
if (is_admin()) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');

	if (!is_plugin_active('geodirectory/geodirectory.php')) {
		return;
	}
}
// GEODIRECTORY CORE ALIVE CHECK END

$geodir_addon_list['geodir_ajax_duplicate_alert_manager'] = 'yes' ;

if (!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix . 'geodir_';

add_action('plugins_loaded','geodir_load_translation_duplicatealert');
function geodir_load_translation_duplicatealert() {
    $locale = apply_filters('plugin_locale', get_locale(), 'geodir_duplicatealert');
    load_textdomain('geodir_duplicatealert', WP_LANG_DIR . '/' . 'geodir_duplicatealert' . '/' . 'geodir_duplicatealert' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodir_duplicatealert', false, dirname(plugin_basename(__FILE__)) . '/geodir-duplicate-alert-languages');
    include_once('language.php');
}

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	register_activation_hook( __FILE__, 'geodir_duplicate_alert_activation' );
	register_deactivation_hook( __FILE__ , 'geodir_duplicate_alert_deactivation' );
	
	add_filter('geodir_plugins_uninstall_settings', 'geodir_duplicate_alert_uninstall_settings', 10, 1);
	add_action('admin_init', 'geodir_duplicate_alert_activation_redirect');
	add_action('admin_init', 'geodir_duplicate_alert_from_submit_handler');
	add_filter('geodir_settings_tabs_array','geodir_adminpage_duplicate_alert',5); 
	add_action('geodir_admin_option_form' , 'geodir_duplicate_alert_tab_content', 5);
}

add_action('activated_plugin','geodir_duplicate_alert_plugin_activated');
function geodir_duplicate_alert_plugin_activated($plugin) {
	if (!get_option('geodir_installed')) {
		$file = plugin_basename(__FILE__);
		
		if ($file == $plugin) {
			$all_active_plugins = get_option('active_plugins', array());
			
			if (!empty($all_active_plugins) && is_array($all_active_plugins)) {
				foreach ($all_active_plugins as $key => $plugin) {
					if ($plugin == $file) {
						unset($all_active_plugins[$key]);
					}
				}
			}
			update_option('active_plugins',$all_active_plugins);
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir_payments'));
	}
}

function geodir_duplicate_alert_activation() {
	// First check if geodir main plugin is active or not.
	if (get_option('geodir_installed')) {
		add_option('geodir_duplicate_alert_manager_activation_redirect', 1);
	}
}

/**
 * Plugin deactivation hook.
 *
 * @since 1.1.6
 */
function geodir_duplicate_alert_deactivation() {
	// Plugin deactivation stuff here.
}

function geodir_duplicate_alert_activation_redirect() {
	if (get_option('geodir_duplicate_alert_manager_activation_redirect', false)) {
		delete_option('geodir_duplicate_alert_manager_activation_redirect');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=duplicatealert_fields')); 
	}
}

function geodir_duplicate_alert_ajax_url(){
	return admin_url('admin-ajax.php?action=geodir_duplicatealert_ajax_action');
}

add_action('wp_ajax_geodir_duplicatealert_ajax_action', 'geodir_duplicate_alert_ajax_action');
add_action('wp_ajax_nopriv_geodir_duplicatealert_ajax_action', 'geodir_duplicate_alert_ajax_action');
function geodir_duplicate_alert_ajax_action() {
	if (isset($_REQUEST['search_val']) && $_REQUEST['search_val'] != '')
		geodir_search_duplicate_field();
	exit;
}

function geodir_search_duplicate_field() {
	global $wpdb, $plugin_prefix;
	
	$field = $_REQUEST['field'];
	$post_type = $_REQUEST['post_type'];
	
	$search = isset($_REQUEST['search_val']) ? htmlentities($_REQUEST['search_val']) : '';
	$search = stripslashes($search);

	if ($search) {
		$table = $plugin_prefix . $post_type . '_detail';
		
		if ($wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM ".$table." WHERE field = %s", array($field)))) {
			$query = $wpdb->prepare("SELECT d." . $field . " FROM $wpdb->posts p, " . $table . " d WHERE p.ID=d.post_id AND d." . $field . "=%s AND p.post_type=%s AND p.post_status='publish'", array($search, $post_type));
			
			$results = $wpdb->get_results( $query );
			
			if (count($results) > 0) {
				$post_types = geodir_get_posttypes('object');
			
				$name = __($post_types->$post_type->labels->singular_name, 'geodirectory');
				
				echo wp_sprintf(__('A %s with this field is already listed!<br/> Please make sure you are not adding a duplicate entry', 'geodir_duplicatealert'), $name);
			}
		}
	}
}

function geodir_adminpage_duplicate_alert($tabs) {
	$tabs['duplicatealert_fields'] = array( 
		'label' =>__( 'Duplicate Alert', 'geodir_duplicatealert' ),
		'label' =>__( 'Duplicate Alert', 'geodir_duplicatealert' )
	);
	
	return $tabs;
}

function geodir_duplicate_alert_from_submit_handler() {
	if (isset($_REQUEST['geodir_duplicatealert_general_options_save'])) {
			$post_types = '';
			if (isset($_REQUEST['geodir_post_types_duplicate']) && is_array($_REQUEST['geodir_post_types_duplicate'])) {
				$post_types = implode(',', $_REQUEST['geodir_post_types_duplicate']);
			}
			update_option('geodir_post_types_duplicate', $post_types);
			
			$post_types = geodir_get_posttypes('object');
					
			foreach ($post_types as $key => $post_types_obj) {
				if (isset($_REQUEST['geodir_duplicate_field_' . $key]))
					update_option('geodir_duplicate_field_' . $key, $_REQUEST['geodir_duplicate_field_' . $key]);
			}
			
			$msg = urlencode(__('Your settings have been saved.', 'geodir_duplicatealert'));
			$location = admin_url() . "admin.php?page=geodirectory&tab=duplicatealert_fields&adl_success=" . $msg;
			
			wp_redirect($location);
			gd_die();
		}
}

function geodir_duplicate_alert_tab_content($tab) {
	switch ($tab) {
		case 'duplicatealert_fields':
			geodir_duplicatealert_setting_fields();
		break;
	}
}

function geodir_duplicatealert_setting_fields(){
	global $wpdb;
	?>
	<div class="inner_content_tab_main">
		<div class="gd-content-heading active">
			<h3><?php _e('Show alert when duplicate value is entered in selected field per selected post type(s) on new Listing page.','geodir_duplicatealert'); ?></h3>
			<table cellpadding="5" class="widefat post fixed">
				<thead>
					<tr>
						<th width="100" align="left"><strong><?php _e('S.No.','geodir_duplicatealert'); ?></strong></th>
						<th width="250" align="left"><strong><?php _e('Listing Type','geodir_duplicatealert'); ?></strong></th>
						<th width="250" align="left"><strong><?php _e('Field Name','geodir_duplicatealert'); ?></strong></th>
					</tr>
					<?php
					$selected_posttypes = array();
						
					if ($post_types_duplicate = get_option('geodir_post_types_duplicate'))
						$selected_posttypes = explode(',', $post_types_duplicate);
						
						$post_types = geodir_get_posttypes('object');
						
						$i = 0;
						foreach ($post_types as $key => $post_types_obj) {								
							$i++;
							
							$query = $wpdb->prepare("SELECT	htmlvar_name, site_title, extra_fields FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s AND is_active = %s AND field_type IN('email', 'phone', 'text', 'address') ORDER BY sort_order ASC", array($key, '1'));
							$field_records = $wpdb->get_results($query);
							
							$duplicate_alert_fields = array('' => 'Select Field', 'post_title' => 'Listing Title');
							
							foreach ($field_records as $fields) {
								if ($fields->htmlvar_name == 'post') {
									$duplicate_alert_fields['post_address'] = $fields->site_title;
									
									if ($fields->extra_fields != '') {
										$extra_fields = maybe_unserialize($fields->extra_fields);
										
										if (isset($extra_fields['show_zip']) && $extra_fields['show_zip'] == '1')
											$duplicate_alert_fields['post_zip'] = $extra_fields['zip_lable'];
									}
								} else {
									$duplicate_alert_fields[$fields->htmlvar_name] = $fields->site_title;
								}
							}
							
							$duplicate_alert_fields = apply_filters('geodir_ajax_duplicate_alert_fields_' . $key, $duplicate_alert_fields);
						?>
					<tr>
						<td align="left"><?php echo $i; ?>.</td>
						<td align="left"><input type="checkbox" value="<?php echo $key; ?>" name="geodir_post_types_duplicate[]" <?php checked(true, (!empty($selected_posttypes) && in_array($key, $selected_posttypes))) ;?> /> <?php _e($post_types_obj->labels->singular_name, 'geodirectory');?></td>
						<td align="left">
							<select id="geodir_duplicate_field_<?php echo $key;?>" style="min-width:300px;" name="geodir_duplicate_field_<?php echo $key;?>">
								<?php if (!empty($duplicate_alert_fields)) { foreach($duplicate_alert_fields as $field_key => $value) { ?>
								<option value="<?php echo esc_attr($field_key); ?>" <?php selected($field_key, get_option('geodir_duplicate_field_' . $key)) ;?>><?php _e($value, 'geodirectory');?></option>
								<?php } } ?>
							</select>
						</td>
					</tr>
					<?php } ?>
				</thead>
			</table>
			<p class="submit" style="margin-top:10px;"><input name="geodir_duplicatealert_general_options_save" class="button-primary" type="submit" value="<?php _e( 'Save changes','geodir_duplicatealert' ); ?>" /><input type="hidden" name="subtab" id="last_tab" /></p>
		</div>
	</div>
	<?php
}

add_action('wp_footer','geodir_duplicate_alert_localize_vars', 10);
function geodir_duplicate_alert_localize_vars() {
	if (geodir_is_page('add-listing')) {
		global $post, $gd_session;
		
		$geodir_current_posttype = 	isset($post->post_type) ? $post->post_type : '';
		
		if (isset($_REQUEST['listing_type']) && $_REQUEST['listing_type'] != '') {
			$geodir_current_posttype = 	$_REQUEST['listing_type'];
		} else if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
			 $geodir_current_posttype = get_post_type($_REQUEST['pid']);
		} else if (isset($_REQUEST['backandedit'])) {
			$post = (object)$gd_session->get('listing');
			$geodir_current_posttype = $post->listing_type;	
		}	
		
		$geodir_field_name = get_option('geodir_duplicate_field_' . $geodir_current_posttype);
		
		$arr_alert_msg = array(
							'geodir_duplicate_alert_ajax_url' => geodir_duplicate_alert_ajax_url(),
							'geodir_post_types_duplicate' => get_option('geodir_post_types_duplicate'),
							'geodir_duplicate_field_name' => $geodir_field_name,
							'geodir_duplicate_current_posttype' => $geodir_current_posttype,
						);
		
		foreach ( $arr_alert_msg as $key => $value ) {
			if ( !is_scalar($value) )
				continue;
			$arr_alert_msg[$key] = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
		}
	
		$script = "var geodir_duplicate_alert_js_var = " . json_encode($arr_alert_msg) . ';';
		echo '<script>' . $script . '</script>';
	}
}

add_action('geodir_before_admin_panel' , 'geodir_display_duplicate_alert_messages'); 
function geodir_display_duplicate_alert_messages() {
	if (isset($_REQUEST['adl_success']) && $_REQUEST['adl_success'] != '') {
		echo '<div id="message" class="updated fade"><p><strong>' . __( $_REQUEST['adl_success'], 'geodir_duplicatealert' ) . '</strong></p></div>';
	}
}

add_action( 'wp_enqueue_scripts', 'geodir_ajax_duplicate_alert_templates_styles' );
function geodir_ajax_duplicate_alert_templates_styles() {
	if (geodir_is_page('add-listing')) {
		wp_register_style('geodir-duplicate-frontend-style', plugins_url('',__FILE__) . '/css/custom_duplicate_alert.css');
		wp_enqueue_style('geodir-duplicate-frontend-style');
	}
}

add_action( 'wp_enqueue_scripts', 'geodir_ajax_duplicate_alert_templates_script' );
function geodir_ajax_duplicate_alert_templates_script() {
	if (geodir_is_page('add-listing')) {
		wp_enqueue_script('jquery');
		wp_register_script('geodir-duplicate-custom-js', plugins_url('',__FILE__) . '/js/custom_duplicate_alert.js');
		wp_enqueue_script('geodir-duplicate-custom-js');
	}
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.1.6
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_duplicate_alert_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}