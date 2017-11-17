<?php
/**
 * activation hooks
 **/
if ( is_admin() ) :

    if ( ! wp_next_scheduled( 'geodir_review_rating_clean' ) ) {
        wp_schedule_event( time(), 'daily', 'geodir_review_rating_clean' );
    }

    add_action( 'geodir_review_rating_clean', 'geodir_reviewrating_remove_unncesssary_directories' );
	 
	add_action('admin_init', 'geodir_reviewrating_activation_redirect');
	 
	add_action( 'admin_enqueue_scripts', 'geodir_reviewrating_admin_scripts', 11);
	
	add_action( 'admin_enqueue_scripts', 'geodir_reviewrating_admin_styles', 11);
	 
	add_filter('geodir_settings_tabs_array','geodir_reviewrating_navigations',5);
	
	add_action('geodir_admin_option_form' , 'geodir_reviewrating_option_forms',5);
	
	add_action('wp_ajax_geodir_reviewrating_ajax', "geodir_reviewrating_ajax_actions");
	
	add_action( 'wp_ajax_nopriv_geodir_reviewrating_ajax', 'geodir_reviewrating_ajax_actions' );
	
	add_action( 'add_meta_boxes', 'geodir_reviewrating_comment_metabox', 13 );
	 
	add_action('admin_init', 'geodir_reviewrating_reviews_change_unread_to_read');
	
	// Rating star labels translation
	add_filter('geodir_load_db_language', 'geodir_reviewrating_db_translation');
	add_filter('geodir_plugins_uninstall_settings', 'geodir_reviewrating_uninstall_settings', 10, 1);
endif;

 
add_action('init','geodir_reviewrating_remove_all_filters',100);
add_action( 'init', 'geodir_reviewrating_action_on_init' );

/**
 * Removes all review rating filters.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_remove_all_filters(){
	
	if(has_action( 'comment_form_logged_in_after', 'geodir_comment_rating_fields' )){
		
		if(get_option('geodir_reviewrating_enable_rating')):
			remove_action('wp_set_comment_status','geodir_update_rating_status_change');
			remove_action( 'comment_form_logged_in_after', 'geodir_comment_rating_fields' );
			remove_action( 'comment_form_before_fields', 'geodir_comment_rating_fields' );
			remove_action( 'edit_comment','geodir_update_rating' );
			remove_action( 'delete_comment', 'geodir_comment_delete_comment' );
			remove_filter( 'comment_text', 'geodir_wrap_comment_text',40);
			remove_action( 'add_meta_boxes_comment', 'geodir_comment_add_meta_box' );
			remove_filter( 'comment_row_actions', 'geodir_comment_meta_row_action', 11, 1 );
		endif;
	
		remove_action( 'comment_post','geodir_save_rating' );
	}	
}


add_action( 'wp_enqueue_scripts', 'geodir_reviewrating_comments_script');

add_action('wp_ajax_geodir_reviewrating_plupload', "geodir_reviewrating_plupload_action");
 
add_action( 'wp_ajax_nopriv_geodir_reviewrating_plupload', 'geodir_reviewrating_plupload_action' );

add_filter('geodir_after_custom_detail_table_create','geodir_reviewrating_after_custom_detail_table_create',1,2);

add_action( 'delete_comment', 'geodir_reviewrating_delete_comments' );

add_action('wp_set_comment_status','geodir_reviewrating_set_comment_status',100,2);
//add_action('edit_comment','geodir_reviewrating_set_comment_status',100,2);

//add_filter('comments_array', 'geodir_reviewrating_filter_comments'); 
add_filter( 'wp_list_comments_args', 'geodir_reviewrating_list_comments_args', 10, 1 );
add_filter( 'comments_template_query_args', 'geodir_reviewrating_reviews_query_args', 10, 1 );
add_filter( 'comments_clauses', 'geodir_reviewrating_reviews_clauses', 10, 2 );

add_action( 'geodir_create_new_post_type', 'geodir_reviewrating_create_new_post_type', 1, 1 );

add_action( 'geodir_after_post_type_deleted', 'geodir_reviewrating_delete_post_type', 1, 1 );


/* Show overall comments on comments listing (backend) */
if(get_option('geodir_reviewrating_enable_rating')){
	add_filter( 'comment_row_actions', 'geodir_reviewrating_comment_meta_row_action', 12, 1 );
}


/* Show Comment Rating */
if(get_option('geodir_reviewrating_enable_rating') || get_option('geodir_reviewrating_enable_images') || get_option('geodir_reviewrating_enable_review') || get_option('geodir_reviewrating_enable_sorting') || get_option('geodir_reviewrating_enable_sharing')){
	add_filter('comment_text', 'geodir_reviewrating_wrap_comment_text',42,2);
}
 
/* Show Post Rating */
if(get_option('geodir_reviewrating_enable_rating') && get_option('geodir_reviewrating_enable_sorting')){
	add_action("comments_template",'geodir_reviewrating_show_post_ratings',10);
}

/* Modify Comment Form Fields Taxt */
if(get_option('geodir_reviewrating_enable_rating')):
	//add_filter('comment_form_defaults', 'gdreviewratings_set_comment_defaults'); 
endif;


add_filter('geodir_reviews_rating_comment_shorting', 'geodir_reviews_rating_update_comment_shorting_options');
/**
 * Adds review rating sorting options to the available sorting list.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $arr Sorting array.
 * @return array Modified sorting array.
 */
function geodir_reviews_rating_update_comment_shorting_options($arr){

	if(get_option('geodir_reviewrating_enable_images')){
		$arr['least_images'] = __( 'Least Images', 'geodir_reviewratings' );
		$arr['highest_images'] = __( 'Highest Images', 'geodir_reviewratings' );
	}
												 
	if(get_option('geodir_reviewrating_enable_review')){
		$arr['low_review'] = __( 'Lowest Reviews', 'geodir_reviewratings' );
		$arr['high_review'] = __( 'Highest Reviews', 'geodir_reviewratings' );
	}
	
	return $arr;
}
 
/* Show Rating Fields In Comment Form */
add_action( 'comment_form_logged_in_after', 'geodir_reviewrating_comment_rating_fields' );
 
add_action( 'comment_form_before_fields', 'geodir_reviewrating_comment_rating_fields' );
 
add_filter('comment_reply_link', 'geodir_reviewrating_comment_replylink');/* Wrap Comment reply link */

add_filter('cancel_comment_reply_link', 'geodir_reviewrating_cancle_replylink');/* Wrap Cancel rply link */
 
add_filter('comment_save_pre','geodir_reviewrating_update_comments');/* update Comment Rating */
  
add_action('comment_post','geodir_reviewrating_save_rating');/* Save Comment Rating */

add_action('geodir_before_admin_panel' , 'geodir_reviewrating_display_messages'); 

add_action('wp_footer','geodir_reviewrating_localize_all_js_msg');

add_action('admin_footer','geodir_reviewrating_localize_all_js_msg');

add_action('admin_head-media-upload-popup','geodir_reviewrating_localize_all_js_msg');
 

add_action('geodir_before_review_rating_stars_on_listview', 'geodir_before_reviewrating_advance_stars_on_listview', 2, 2 ) ;
add_action('geodir_after_review_rating_stars_on_listview', 'geodir_after_reviewrating_advance_stars_on_listview', 2, 2 ) ;

add_action('geodir_before_review_rating_stars_on_gridview', 'geodir_before_reviewrating_advance_stars_on_gridview', 2, 2 ) ;
add_action('geodir_after_review_rating_stars_on_gridview', 'geodir_after_reviewrating_advance_stars_on_gridview', 2, 2 ) ;

add_action('geodir_before_review_rating_stars_on_detail', 'geodir_before_reviewrating_advance_stars_on_detail', 2, 2 ) ;
add_action('geodir_after_review_rating_stars_on_detail', 'geodir_after_reviewrating_advance_stars_on_detail', 2, 2 ) ;


add_filter('geodir_review_rating_stars_on_infowindow', 'geodir_reviewrating_advance_stars_on_infowindow', 2, 3 ) ;


/**
 * Localize all javascript message strings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_localize_all_js_msg(){

	global $path_location_url;
	
	$arr_alert_msg = array(
							'geodir_reviewrating_admin_url' => admin_url('admin.php'),
							'geodir_reviewrating_admin_ajax_url' => geodir_reviewrating_ajax_url(),
							'geodir_reviewrating_select_overall_rating_off_img' => __('Please select overall rating Off image.', 'geodir_reviewratings'),
							'geodir_reviewrating_select_overall_rating_on_img' => __('Please select overall rating on image.', 'geodir_reviewratings'),
							'geodir_reviewrating_select_overall_rating_half_img' => __('Please select Overall rating half image.', 'geodir_reviewratings'),
							'geodir_reviewrating_please_enter' => __('Please enter', 'geodir_reviewratings'),
							'geodir_reviewrating_score_text' => __('Score text', 'geodir_reviewratings'),
							'geodir_reviewrating_star_text' => __('Star Text', 'geodir_reviewratings'),
							'geodir_reviewrating_enter_title' => __('Please enter Title.', 'geodir_reviewratings'),
							'geodir_reviewrating_rating_delete_confirmation' => __('Do you want to delete this rating?', 'geodir_reviewratings'),
							'geodir_reviewrating_please_select' => __('Please select', 'geodir_reviewratings'),
							'geodir_reviewrating_categories_text' => __('Categories.', 'geodir_reviewratings'),
							'geodir_reviewrating_select_post_type' => __('Please select Post Type.', 'geodir_reviewratings'),
							'geodir_reviewrating_enter_rating_title' => __('Please enter rating title.', 'geodir_reviewratings'),
							'geodir_reviewrating_select_multirating_style' => __('Please Select multirating style.', 'geodir_reviewratings'),
							'geodir_reviewrating_select_review_like_img' => __('Please select review like image.', 'geodir_reviewratings'),
							'geodir_reviewrating_select_review_unlike_img' => __('Please select review unlike image.', 'geodir_reviewratings'),
							
							'geodir_reviewrating_hide_images' => __('Hide Images', 'geodir_reviewratings'),
							'geodir_reviewrating_show_images' => __('Show Images', 'geodir_reviewratings'),
							
							'geodir_reviewrating_hide_ratings' => __('Hide Multi Ratings', 'geodir_reviewratings'),
							'geodir_reviewrating_show_ratings' => __('Show Multi Ratings', 'geodir_reviewratings'),
							'geodir_reviewrating_delete_image_confirmation' => __('Are you sure want to delete this image?', 'geodir_reviewratings'),
							'geodir_reviewrating_please_enter_below' => __('Please enter below', 'geodir_reviewratings'),
							'geodir_reviewrating_please_enter_above' => __('Please enter above', 'geodir_reviewratings'),
							'geodir_reviewrating_numeric_validation' => __('Please enter only numeric value', 'geodir_reviewratings'),
							'geodir_reviewrating_maximum_star_rating_validation' => __('You are create maximum seven star rating', 'geodir_reviewratings'),
							'geodir_reviewrating_star_and_input_box_validation' => __('Your input box number and number of star is not same', 'geodir_reviewratings'),
							'geodir_reviewrating_star_and_score_text_validation' => __('Your input box number and number of Score text is not same', 'geodir_reviewratings'),
							'geodir_reviewrating_select_rating_off_img' => __('Please select rating off image.', 'geodir_reviewratings'),
							'geodir_reviewrating_rating_img_featured' => get_option( 'geodir_reviewrating_overall_off_img_featured' ),
							'geodir_reviewrating_rating_color_featured' => get_option( 'geodir_reviewrating_overall_color_featured' ),
							'geodir_reviewrating_rating_width_featured' => get_option( 'geodir_reviewrating_overall_off_img_width_featured' ),
							'geodir_reviewrating_rating_height_featured' => get_option( 'geodir_reviewrating_overall_off_img_height_featured' ),
							'geodir_reviewrating_optional_multirating' => (bool)get_option( 'geodir_reviewrating_optional_multirating' ),
							'err_empty_review' => __('Please type a review.', 'geodir_reviewratings'),
							'err_empty_reply' => __('Please type a reply.', 'geodir_reviewratings'),
						);
	
	foreach ( $arr_alert_msg as $key => $value ) 
	{
		if ( !is_scalar($value) )
			continue;
		$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
	}
	
	$script = "var geodir_reviewrating_all_js_msg = " . json_encode($arr_alert_msg) . ';';
	echo '<script>';
	echo $script ;	
	echo '</script>';
}
add_filter('geodir_is_reviews_show', 'geodir_review_rating_is_reviews_show', 2, 2);

/* google rich snippets for reviews */
//add_action( 'wp_footer', 'geodir_review_rating_reviews_rich_snippets' );

add_filter('geodir_reviewratings_overall', 'geodir_font_awesome_rating_form_html', 10, 2);
add_filter('geodir_reviewratings_individual', 'geodir_font_awesome_rating_form_html', 10, 2);
add_filter('geodir_reviewrating_draw_ratings_html', 'geodir_font_awesome_rating_stars_html', 10, 3);
add_filter('geodir_reviewrating_draw_overall_rating_html', 'geodir_font_awesome_rating_stars_html', 10, 3);

/**
 * Set WordPress locale filter.
 *
 * @since 1.6.16
 * @package GeoDirectory
 */
function geodir_reviewrating_wpml_set_filter() {
    if (geodir_is_wpml()) {
        global $sitepress;
        
        if (get_option('geodir_reviewrating_enable_review') && $sitepress->get_setting('sync_comments_on_duplicates')) {
            add_action('geodir_reviewrating_comment_liked', 'geodir_reviewrating_wpml_sync_like', 10, 1);
            add_action('geodir_reviewrating_comment_unliked', 'geodir_reviewrating_wpml_sync_unlike', 10, 2);
        }
    }
}
add_filter('plugins_loaded', 'geodir_reviewrating_wpml_set_filter', 10);
