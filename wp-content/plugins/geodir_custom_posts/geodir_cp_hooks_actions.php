<?php

add_action('admin_init', 'geodir_cp_activation_redirect');


add_action('geodir_admin_option_form', 'geodir_get_admin_cp_form', 3);

add_action('admin_init', 'geodir_cp_from_submit_handler');

add_action( 'admin_enqueue_scripts', 'geodir_custom_post_type_script' );

add_filter('geodir_settings_tabs_array','geodir_cp_fields_tab',3);

add_action('geodir_before_admin_panel' , 'geodir_display_cp_messages');

add_action('wp_ajax_geodir_cp_ajax_action', 'geodir_custom_post_type_ajax');

add_action( 'wp_ajax_nopriv_geodir_cp_ajax_action', 'geodir_custom_post_type_ajax' ); 

add_action('admin_init', 'geodir_payment_remove_unnecessary_fields');
add_action('widgets_init', 'register_geodir_cpt_widgets'); 


add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_CPT', 10,1); 
function geodir_diagnose_multisite_conversion_CPT($table_arr){
	global $wpdb;
	$post_types = geodir_get_posttypes();
	
	if(!empty($post_types))
	{
		foreach($post_types as $p_type)
		{	if($p_type=='gd_place' || $p_type=='gd_event'){continue;}
			$table_arr["geodir_".$p_type."_detail"] = "CPT: geodir_".$p_type."_detail";
		}
		
	}
	return $table_arr;
}

// This is used to change the post type args before saving into the post type options array in database.
// This helps to rewrite the post type url on wordpress default rule.
//add_filter('geodir_post_type_args', 'geodir_custom_post_type_args_modify',1, 2) ;

/*
 * @deprecated 1.1.8 no need for this anymore
 */
function geodir_custom_post_type_args_modify( $args ,$post_type)
{
	
	if(isset($_REQUEST['geodir_save_post_type']))
	{

			$custom_post_type	= htmlentities(trim($_REQUEST['geodir_custom_post_type']));
			$listing_slug 		= urldecode(sanitize_title($_REQUEST['geodir_listing_slug']));
			
			if($custom_post_type == 'place')
				update_option('geodir_listing_prefix', $listing_slug);
			
			if(geodir_strtolower($post_type) == geodir_strtolower($custom_post_type))
			{
				if(array_key_exists('has_archive' ,$args ))
					$args['has_archive']  = $listing_slug ;
					
				if(array_key_exists('rewrite' ,$args ))
				{
					if(array_key_exists('slug' ,$args['rewrite']))
						$args['rewrite']['slug'] = $listing_slug.'/%gd_taxonomy%' ;		
				}
			}
					
	}
	
	return $args ;
}

function geodir_cp_fields_tab($tabs)
{
	$tabs['geodir_manage_custom_posts'] = array( 'label' =>__( 'Custom Post Types','geodir_custom_posts' ));
	$geodir_post_types = get_option( 'geodir_post_types' );
	$post_types = geodir_get_posttypes();
	
	if(!empty($post_types))
	{
		foreach($post_types as $p_type)
		{
			if (!array_key_exists($p_type.'_fields_settings', $tabs))
			{
				
				$post_type_array = $geodir_post_types[$p_type];
				$listing_slug = $post_type_array['labels']['singular_name'];
				
				$tabs[$p_type.'_fields_settings'] = array( 	
						'label' => sprintf(__('%s Settings', 'geodir_custom_posts' ), ucfirst($listing_slug) ),
						'subtabs' => array(
													array('subtab' => 'custom_fields',
																'label' =>__( 'Custom Fields', 'geodir_custom_posts'),
																'request' => array('listing_type'=>$p_type)),
													array('subtab' => 'sorting_options',
																'label' =>__( 'Sorting Options', 'geodir_custom_posts'),
																'request' => array('listing_type'=>$p_type)),
												),
						'request' => array('listing_type'=>$p_type) 
						);
			}
		}
	}
	
	return $tabs; 
	
}

function geodir_get_admin_cp_form($tab_name)
{
	switch ($tab_name)
	{
		case 'geodir_manage_custom_posts':
			if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'cp_addedit')
				geodir_cp_add_edit_form();
			else
				geodir_cp_listing();
			
			break;
	}
	
}

add_filter('geodir_custom_post_type_default_menu_icon','geodir_custom_post_type_default_menu_icon');

function geodir_custom_post_type_default_menu_icon($menu_icon){
	
	if($menu_icon ==''){
		//replace menu icon with available icon in plugin image folder or from core.
	}
	
	return $menu_icon;
	
}

add_action('wp_footer','geodir_custom_post_type_localize_all_js_msg');

add_action('admin_footer','geodir_custom_post_type_localize_all_js_msg');


function geodir_custom_post_type_localize_all_js_msg(){

	global $path_location_url;
	
	$arr_alert_msg = array(
		
		'geodir_cp_post_type_char_validation' => __('Post Type can not be more than 17 characters.', 'geodir_custom_posts'),
		'geodir_cp_post_type_illegal_characters_validation' => __('Post Type contains illegal characters.', 'geodir_custom_posts'),
		'geodir_cp_post_type_blank_validation' => __('Post Type must not be blank.', 'geodir_custom_posts'),
		'geodir_cp_listing_slug_illegal_characters_validation' => __('Listing Slug contains illegal characters.', 'geodir_custom_posts'),
		'geodir_cp_listing_slug_blank_validation' => __('Listing Slug must not be blank.', 'geodir_custom_posts'),
		'geodir_cp_listing_order_value_validation' => __('Enter valid order value.', 'geodir_custom_posts'),
		
	);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_custom_post_type_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;
	echo '</script>';
}

// change tab heading text on post detrail page
add_filter('geodir_detail_page_tab_list_extend', 'geodir_detail_page_tab_headings_change_ctp');
function geodir_detail_page_tab_headings_change_ctp( $tabs_arr ) {
	global $wpdb;
	
	$post_type = geodir_get_current_posttype();
	$all_postypes = geodir_get_posttypes();
		
	if (!empty($tabs_arr) && $post_type != '' && in_array($post_type, $all_postypes)) {
		$geodir_post_types = get_option('geodir_post_types');
		if (empty($geodir_post_types[$post_type])) {
			return $tabs_arr;
		}
		$post_type_array = $geodir_post_types[$post_type];
		if (array_key_exists('post_profile', $tabs_arr) && !empty($post_type_array['labels']['label_post_profile'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_profile']);
			
			if (isset($tabs_arr['post_profile']['heading_text']) && $field_title != '') {
				$tabs_arr['post_profile']['heading_text'] = __($field_title, 'geodir_custom_posts');
			}
		}
		
		if (array_key_exists('post_info', $tabs_arr) && !empty($post_type_array['labels']['label_post_info'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_info']);
			
			if (isset($tabs_arr['post_info']['heading_text']) && $field_title != '') {
				$tabs_arr['post_info']['heading_text'] = __($field_title, 'geodir_custom_posts');
			}
		}
		
		if (array_key_exists('post_images', $tabs_arr) && !empty($post_type_array['labels']['label_post_images'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_images']);
			
			if (isset($tabs_arr['post_images']['heading_text']) && $field_title != '') {
				$tabs_arr['post_images']['heading_text'] = __($field_title, 'geodir_custom_posts');
			}
		}
		
		if (array_key_exists('post_map', $tabs_arr) && !empty($post_type_array['labels']['label_post_map'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_post_map']);
			
			if (isset($tabs_arr['post_map']['heading_text']) && $field_title != '') {
				$tabs_arr['post_map']['heading_text'] = __($field_title, 'geodir_custom_posts');
			}
		}
		
		if (array_key_exists('reviews', $tabs_arr) && !empty($post_type_array['labels']['label_reviews'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_reviews']);
			
			if (isset($tabs_arr['reviews']['heading_text']) && $field_title != '') {
				$tabs_arr['reviews']['heading_text'] = __($field_title, 'geodir_custom_posts');
			}
		}
		
		if (array_key_exists('related_listing', $tabs_arr) && !empty($post_type_array['labels']['label_related_listing'])) {
			$field_title = stripslashes_deep($post_type_array['labels']['label_related_listing']);
			
			if (isset($tabs_arr['related_listing']['heading_text']) && $field_title != '') {
				$tabs_arr['related_listing']['heading_text'] = __($field_title, 'geodir_custom_posts');
			}
		}
	
	}
	
	return $tabs_arr;
}

// Disable location for CPT
if ( is_admin() ) {
	add_filter( 'geodir_general_settings', 'geodir_cpt_tab_general_settings', 10 );
	add_action( 'geodir_update_options_general_settings', 'geodir_cpt_submit_general_settings', 10 );
	add_action( 'geodir_manage_available_fields', 'geodir_cpt_manage_available_fields', 10 );
	add_filter( 'admin_footer', 'geodir_cpt_admin_footer' );
    add_filter('geodir_plugins_uninstall_settings', 'geodir_cpt_uninstall_settings', 10, 1);
}

add_filter( 'term_link', 'geodir_cpt_term_link', 9999, 3 );
add_filter( 'post_type_archive_link', 'geodir_cpt_post_type_archive_link', 9999, 2 );
add_filter( 'geodir_add_location_url_to_url', 'geodir_cpt_post_type_link', 10, 3 );
add_filter( 'geodir_location_allowed_location_where', 'geodir_cpt_allowed_location_where', 9999, 5 );
add_filter( 'geodir_post_view_extra_class', 'geodir_cpt_post_view_class' );
add_action( 'admin_panel_init', 'geodir_cpt_admin_list_columns', 2 );
add_filter( 'geodir_current_location_terms', 'geodir_cpt_current_location_terms', 9999, 3 );
add_filter( 'geodir_detail_page_tab_is_display', 'geodir_cpt_detail_page_map_is_display', 9999, 2 );
if ( isset( $_REQUEST['geodir_search'] ) && $_REQUEST['geodir_search'] ) {
	add_filter( 'init', 'geodir_cpt_remove_loc_on_search', 0 );
	add_action( 'pre_get_posts', 'geodir_cpt_remove_location_search', 1 );
}
add_filter( 'geodir_show_map_listing', 'geodir_cpt_remove_map_listing', 10 );
add_filter( 'geodir_loc_term_count', 'geodir_cpt_loc_term_count', 10, 2 );


add_filter('geodir_seo_meta_description_pre','geodir_pt_meta_desc',8,2);
/**
 * Replace the CPT meta description if set in the CPT settings.
 *
 * @since 1.2.9
 * @param string $meta_desc The original meta description.
 * @param string $gd_page The GD page type, we look for `pt`
 *
 * @return string The CPT meta description.
 */
function geodir_pt_meta_desc($meta_desc,$gd_page){

	if($gd_page=='pt'){
		$post_type = get_query_var('post_type');
		if(is_array($post_type)){$post_type = reset($post_type);}

		$cpt_info = geodir_get_posttype_info($post_type);
		if(isset($cpt_info['seo']['meta_description']) && $cpt_info['seo']['meta_description']){
			$meta_desc = $cpt_info['seo']['meta_description'];
		}
	}
	return $meta_desc;
}

add_action('geodir_listings_page_description', 'geodir_cpt_pt_desc', 10);
function geodir_cpt_pt_desc(){
	if(geodir_is_page('pt')){
		$post_type = get_query_var('post_type');
		if(is_array($post_type)){$post_type = reset($post_type);}

		$cpt_info = geodir_get_posttype_info($post_type);

		if(isset($cpt_info['description']) && $cpt_info['description'] && $cpt_info['description']!=$cpt_info['labels']['singular_name'].' post type.'){
			?><div class="term_description"><?php echo $cpt_info['description'];?></div><?php
		}
	}
}

/**
 * Hide pinpoint for location less CPTs.
 *
 * @since 1.3.6
 *
 * @global object $wp_query WP_Query query object.
 */
function geodir_check_listing_pinpoint() {
    global $wp_query;
    
    if ( !empty( $wp_query ) && $wp_query->is_main_query() ) {
        if ( ! empty( $_REQUEST['geodir_search'] ) && ! empty( $_REQUEST['stype'] ) ) {
            $post_type = sanitize_text_field( $_REQUEST['stype'] );
        } else {
            $post_type = get_query_var( 'post_type' );
        }

        if ( $post_type && geodir_cpt_no_location( $post_type ) ) {
            remove_action( 'geodir_listing_after_pinpoint', 'geodir_output_pinpoint_html_listings', 1, 2 );
        }
    }
}
add_action( 'template_redirect', 'geodir_check_listing_pinpoint', 10 );