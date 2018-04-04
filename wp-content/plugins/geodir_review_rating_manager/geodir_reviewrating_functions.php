<?php
/**
 * Contains functions related to Review Rating Manager plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * Plugin Activation Function
 *
 * @since 1.0.0
 * @since 1.3.4 Should not loose previously saved settings when plugin is reactivated.
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_activation() {
    if (get_option('geodir_installed')) {
        geodir_reviewrating_db_install();
        update_option( "geodir_reviewrating_db_version", GEODIRREVIEWRATING_VERSION );

        $default_options = geodir_reviewrating_resave_settings(geodir_reviewrating_default_options());
        geodir_update_options($default_options, true);

        add_option('geodir_reviewrating_activation_redirect_opt', 1);
    }
}

/**
 * Plugin deactivation hook.
 *
 * @package GeoDirectory_Review_Rating_Manager
 * @since 1.3.4
 */
function geodir_reviewrating_deactivation() {
    // Plugin deactivation stuff here
}

/**
 * Adds review rating manager to active plugin list.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $plugin Plugin basename.
 */
function geodir_reviewrating_plugin_activated( $plugin ) {
    if ( !get_option( 'geodir_installed' ) )  {
        $file = plugin_basename( GEODIR_REVIEWRATING_PLUGIN_FILE );

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

        wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir_reviewratings' ) );
    }
}

/**
 * Handle the plugin settings for plugin deactivate to activate.
 *
 * It manages the the settings without loosing previous settings saved when plugin
 * status changed from deactivate to activate.
 *
 * @since 1.3.4
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $settings The option settings array.
 * @return array The settings array.
 */
function geodir_reviewrating_resave_settings($settings = array()) {
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

function geodir_load_translation_reviewratings() {
    $locale = apply_filters('plugin_locale', get_locale(), 'geodir_reviewratings');
    load_textdomain('geodir_reviewratings', WP_LANG_DIR . '/' . 'geodir_reviewratings' . '/' . 'geodir_reviewratings' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodir_reviewratings', false, dirname(plugin_basename(GEODIR_REVIEWRATING_PLUGIN_FILE)) . '/geodir-reviewrating-languages');

    include_once(GEODIR_REVIEWRATING_PLUGINDIR_PATH . '/language.php');
}

add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_review_manager', 10,1);
/**
 * Diagnose review rating manager tables.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $table_arr Diagnose table array.
 * @return array Modified diagnose table array.
 */
function geodir_diagnose_multisite_conversion_review_manager($table_arr){
	// Diagnose Claim listing details table
	$table_arr['geodir_rating_style'] = __('Rating style', 'geodir_reviewratings');
	$table_arr['geodir_rating_category'] = __('Rating category', 'geodir_reviewratings');
	$table_arr['geodir_unassign_comment_images'] = __('Comment image', 'geodir_reviewratings');
	$table_arr['geodir_comments_reviews'] = __('Comment reviews', 'geodir_reviewratings');
	return $table_arr;
}

/**
 * Plugin Database Instalation Function
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_db_install() {
	global $wpdb,$plugin_prefix;

	/**
	 * Include any functions needed for upgrades.
	 *
	 * @since 1.1.9
     * @package GeoDirectory_Review_Rating_Manager
	 */
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$wpdb->hide_errors();


	$collate = '';
	if($wpdb->has_cap( 'collation' )) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
	}


		$rating_style_table = "CREATE TABLE ".GEODIR_REVIEWRATING_STYLE_TABLE." (
								id int(11) NOT NULL AUTO_INCREMENT,
								name varchar(200) NOT NULL,
								s_img_off text NOT NULL,
								s_img_width text NOT NULL,
								s_img_height text NOT NULL,
								star_color text NOT NULL,
								star_lables text NOT NULL,
								star_number varchar(200) NOT NULL,
								is_default enum( '0', '1' ) NOT NULL DEFAULT '0',
								PRIMARY KEY  (id)
								) $collate";

		dbDelta( $rating_style_table );

		$default_star_lables = array();
		$default_star_lables[] = 'Terrible';
		$default_star_lables[] = 'Poor';
		$default_star_lables[] = 'Average';
		$default_star_lables[] = 'Very Good';
		$default_star_lables[] = 'Excellent';
		$default_star_lables = maybe_serialize( $default_star_lables );

    if($wpdb->get_var("SHOW TABLES LIKE '".GEODIR_REVIEWRATING_STYLE_TABLE."'") != GEODIR_REVIEWRATING_STYLE_TABLE){
        $default_img = GEODIR_REVIEWRATING_PLUGINDIR_URL."/icons/stars.png";

		$wpdb->query("INSERT INTO  ".GEODIR_REVIEWRATING_STYLE_TABLE." (name,s_img_off,s_img_width,s_img_height,star_color,star_lables,star_number,is_default) VALUES ('overall','$default_img','23','20','#ff9900','$default_star_lables','5','1') ");

		update_option( 'geodir_reviewrating_change_star_lables_field', '1' );
	}


		$rating_category_table = "CREATE TABLE ".GEODIR_REVIEWRATING_CATEGORY_TABLE." (
									id int( 11 ) NOT NULL AUTO_INCREMENT ,
									title varchar( 500 ) NOT NULL ,
									post_type varchar( 500 ) NOT NULL ,
									category text NOT NULL ,
									category_id text NOT NULL ,
									check_text_rating_cond enum( '0', '1' ) NOT NULL DEFAULT '1',
									PRIMARY KEY  (id)
									) $collate ";

		dbDelta( $rating_category_table );

		$comment_images_table = "CREATE TABLE ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." (
								id int(11) NOT NULL AUTO_INCREMENT,
								directory varchar(240) NOT NULL,
								date datetime NOT NULL,
								PRIMARY KEY  (id)
								) $collate ";

		dbDelta( $comment_images_table );



		$comments_reviews_table = "CREATE TABLE ".GEODIR_COMMENTS_REVIEWS_TABLE." (
									like_id int(11) NOT NULL AUTO_INCREMENT,
									comment_id int(11) NOT NULL,
									ip varchar(100) NOT NULL,
									user_id int(11) NOT NULL DEFAULT '0',
									like_unlike int(11) NOT NULL,
									user_agent text NOT NULL,
									like_date datetime NOT NULL,
									PRIMARY KEY  (like_id)
									) $collate";

		dbDelta( $comments_reviews_table );


	if(!$wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE field = 'read_unread'"))
	{
		$wpdb->query("ALTER TABLE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." ADD `read_unread` VARCHAR( 50 )  NOT NULL");
	}

	if(!$wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE field = 'total_images'"))
	{
		$wpdb->query("ALTER TABLE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." ADD `total_images` int( 11 )  NOT NULL");
	}

	$post_types = geodir_get_posttypes();
	foreach($post_types as $post_type){

		global $plugin_prefix;

		$detail_table = $plugin_prefix . $post_type . '_detail';
		geodir_add_column_if_not_exist($detail_table, 'ratings',  'TEXT NULL DEFAULT NULL');

	}

	$link_image = GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/up-img.png';
	$unlink_image = GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/down.png';
	$default_img = GEODIR_REVIEWRATING_PLUGINDIR_URL."/icons/stars.png";

	//if(!get_option('geodir_reviewrating_review_like_img'))
		//update_option('geodir_reviewrating_review_like_img',$link_image);

	//if(!get_option('geodir_reviewrating_review_unlike_img'))
		//update_option('geodir_reviewrating_review_unlike_img',$unlink_image);

	if(!get_option('geodir_reviewrating_overall_off_img'))
		update_option('geodir_reviewrating_overall_off_img',$default_img);

	if(!get_option('geodir_reviewrating_overall_color'))
		update_option('geodir_reviewrating_overall_color','#ff9900');

	if(!get_option('geodir_reviewrating_overall_off_img_width'))
		update_option('geodir_reviewrating_overall_off_img_width','23');

	if(!get_option('geodir_reviewrating_overall_off_img_height'))
		update_option('geodir_reviewrating_overall_off_img_height','20');

	if(!get_option('geodir_reviewrating_overall_rating_texts'))
		update_option('geodir_reviewrating_overall_rating_texts',array(0 => 'Terrible',1 => 'Poor',2 => 'Average',3 => 'Very Good',4 => 'Excellent'));

	if(!get_option('geodir_reviewrating_overall_count'))
		update_option('geodir_reviewrating_overall_count','5');
}

/**
 * review rating manager delete post type.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $post_type The post type.
 */
function geodir_reviewrating_delete_post_type($post_type = ''){

	global $wpdb, $plugin_prefix;

	if($post_type != ''){

		$all_postypes = geodir_get_posttypes();

		$rating_data = $wpdb->get_results($wpdb->prepare("SELECT id, post_type FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE FIND_IN_SET(%s, post_type)", array($post_type)));

		if(!empty($rating_data)){

			foreach($rating_data as $key => $rating){

				$ratings = explode(",",$rating->post_type);

				if(($del_key = array_search($post_type, $ratings)) !== false)
					unset($ratings[$del_key]);

				if(!empty($ratings)){

					$ratings = implode(',',$ratings);

					$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_REVIEWRATING_CATEGORY_TABLE." SET post_type=%s WHERE id=%d",array($ratings,$rating->id)));

				}else{

					$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE id=%d", array($rating->id)));

				}

			}

		}

	}

}

/**
 * Adds custom columns in detail table for review rating manager.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $post_type The post type.
 * @param string $detail_table The detail table name.
 */
function geodir_reviewrating_after_custom_detail_table_create($post_type, $detail_table){

	$post_types = geodir_get_posttypes();

	if(in_array($post_type, $post_types)){
		geodir_add_column_if_not_exist($detail_table, 'ratings',  'TEXT NULL DEFAULT NULL');
		geodir_add_column_if_not_exist($detail_table, 'overall_rating',  'float(11) DEFAULT NULL');
	}

}

/**
 * Redirects user to review rating manager settings page after plugin activation.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_activation_redirect() {
	if (get_option('geodir_reviewrating_activation_redirect_opt', false)) {
		delete_option('geodir_reviewrating_activation_redirect_opt');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=multirating_fields&subtab=geodir_multirating_options'));
	}
}

/**
 * Admin ajax url for review rating manager.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @return string|void
 */
function geodir_reviewrating_ajax_url(){
		return admin_url('admin-ajax.php?action=geodir_reviewrating_ajax');
}

/**
 * Adds settings form to each sub tab of review rating manager.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $tab GeoDirectory setting tab name.
 */
function geodir_reviewrating_option_forms($tab){

	if($tab == 'multirating_fields'){

		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_multirating_options')
			add_action('geodir_admin_option_form', 'geodir_reviewrating_general_options_form');

		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_rating_settings')
			geodir_reviewrating_overall_settings_form();

		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_rating_style')
			geodir_reviewrating_manage_rating_style_form();

		if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_create_rating')
			geodir_reviewrating_create_rating_form();

		//if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_manage_review')
			//geodir_reviewrating_manage_review_form();

	} else if ($tab == 'reviews_fields') {
		geodir_reviewrating_manage_comments();
	}
}


/**
 * Adds multi rating and image upload fields to comments.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @return bool
 */
function geodir_reviewrating_comment_rating_fields() {
	global $geodir_post_type;

	if (!$geodir_post_type) {
		$geodir_post_type = geodir_get_current_posttype();
	}

	$all_postypes = geodir_get_posttypes();

	if (!in_array($geodir_post_type, $all_postypes))
		return false;

	if (geodir_cpt_has_rating_disabled($geodir_post_type)) {
		return;
	}

	if (get_option('geodir_reviewrating_enable_rating')) {
		geodir_reviewrating_rating_frm_html();
	}

	if(get_option('geodir_reviewrating_enable_images')):
		geodir_reviewrating_rating_img_html();
	endif;
}

/**
 * Adds MultiRating settings General Tab options.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $current_tab
 */
function geodir_reviewrating_general_options_form($current_tab){

	$current_tab = sanitize_text_field($_REQUEST['subtab']);
	geodir_review_rating_general_options($current_tab);

}

/**
 * Adds overall rating to the map marker info window.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $rating_star
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 * @return string
 */
function geodir_reviewrating_advance_stars_on_infowindow($rating_star, $avg_rating, $post_id) {
	if (!empty($post_id) && geodir_cpt_has_rating_disabled((int)$post_id)) {
		return $rating_star;
	}

	$rating_star  = geodir_reviewrating_draw_overall_rating($avg_rating);

	return $rating_star;
}


/**
 * This function runs before displaying overall rating on detail page sidebar.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 */
function geodir_before_reviewrating_advance_stars_on_detail($avg_rating, $post_id)
{
    ob_start();
}

/**
 * This function adds overall rating and summary to the detail page sidebar.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 */
function geodir_after_reviewrating_advance_stars_on_detail($avg_rating, $post_id)
{
	$star_html = ob_get_clean();
	if (!(!empty($post_id) && geodir_cpt_has_rating_disabled((int)$post_id))) {
		$star_html = '';
		$star_html  .= geodir_reviewrating_draw_overall_rating($avg_rating);

		if ( $star_html != '' ) {
			$star_html .= geodir_reviewrating_detail_page_rating_summary( $avg_rating, $post_id );
		}
	}

	echo $star_html;
}

/**
 * This function runs before displaying overall rating on gridview.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 */
function geodir_before_reviewrating_advance_stars_on_gridview($avg_rating, $post_id)
{
	ob_start();
}

/**
 * This function adds overall rating to the gridview.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 */
function geodir_after_reviewrating_advance_stars_on_gridview($avg_rating, $post_id)
{
	$star_html = ob_get_clean();
	if (!(!empty($post_id) && geodir_cpt_has_rating_disabled((int)$post_id))) {
		$star_html = '';
		$star_html  .= geodir_reviewrating_draw_overall_rating($avg_rating);
	}
	echo $star_html;
}


/**
 * This function runs before displaying overall rating on listview.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 */
function geodir_before_reviewrating_advance_stars_on_listview($avg_rating, $post_id)
{
	ob_start();
}

/**
 * This function adds overall rating to the listview.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 */
function geodir_after_reviewrating_advance_stars_on_listview($avg_rating, $post_id)
{
	$star_html = ob_get_clean();
	if (!(!empty($post_id) && geodir_cpt_has_rating_disabled((int)$post_id))) {
		$star_html = '';
		$star_html  .= geodir_reviewrating_draw_overall_rating($avg_rating);
	}
	echo $star_html;
}


/**
 * Adds review manager default settings to the GeoDirectory settings page.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $arr
 * @return array
 */
function geodir_reviewrating_default_options($arr=array()){

	$arr[] = array( 'name' => __( 'Options', 'geodir_reviewratings' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'review_rating' );

	$arr[] = array( 'name' => __( 'General Settings', 'geodir_reviewratings' ), 'type' => 'sectionstart', 'id' => 'review_rating_default_options');

	$arr[] = array(
		'name'  => __( 'Enable multirating:', 'geodir_reviewratings' ),
		'desc' 	=> __('Enable multirating for comment on post.', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_enable_rating',
		'type' 	=> 'checkbox',
		'std' 	=> '1' // Default value to show home top section
	);

	$arr[] = array(
		'name'  => __( 'Enable comment images upload:', 'geodir_reviewratings' ),
		'desc' 	=> __('Enable upload images in comments for a post.', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_enable_images',
		'type' 	=> 'checkbox',
		'std' 	=> '0'
	);

	$arr[] = array(
		'name'  => __( 'Enable review on comments:', 'geodir_reviewratings' ),
		'desc' 	=> __('Let\'s users rate comments useful or not.', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_enable_review',
		'type' 	=> 'checkbox',
		'std' 	=> '0'
	);

	$arr[] = array(
		'name'  => __( 'Enable comment list sorting:', 'geodir_reviewratings' ),
		'desc' 	=> __('Enable comment list sorting.', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_enable_sorting',
		'type' 	=> 'checkbox',
		'std' 	=> '0'
	);

	$arr[] = array(
		'name'  => __( 'Hide rating stars summary on detail page:', 'geodir_reviewratings' ),
		'desc' 	=> __( 'Hide rating stars summary on the detail page sidebar. (this will break Google rich snippets for reviews)', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_hide_rating_summary',
		'type' 	=> 'checkbox',
		'std' 	=> '0'
	);

	$arr[] = array(
		'name'  => __( 'Disable mandatory rating star:', 'geodir_reviewratings' ),
		'desc' 	=> __( 'Disable mandatory rating stars for multiratings. (this will allow to post review without select rating stars for multiratings.)', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_optional_multirating',
		'type' 	=> 'checkbox',
		'std' 	=> '0'
	);

	/*$arr[] = array(
		'name'  => __( 'Enable comment social sharing:', 'geodir_reviewratings' ),
		'desc' 	=> __('Enable share a comment on social wall.', 'geodir_reviewratings' ),
		'id' 	=> 'geodir_reviewrating_enable_sharing',
		'type' 	=> 'checkbox',
		'std' 	=> '0'
	);*/

	$arr[] = array( 'type' => 'sectionend', 'id' => 'review_rating_default_options');

	$arr = apply_filters('geodir_reviewrating_default_options' ,$arr );

	return $arr;
}

/**
 * Get review manager, rating categories.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $cat_id
 * @return bool|mixed
 */
function geodir_reviewrating_rating_categories($cat_id = ''){
	global $wpdb;
	$where = '';
	if($cat_id != '')
		$where = $wpdb->prepare(" AND rt.id = %d ", array($cat_id));

	$results = $wpdb->get_results("SELECT rt.id as id,
									rt.title as title,
									rt.post_type as post_type,
									rt.category as category,
									rt.category_id as category_id,
									rt.check_text_rating_cond as check_text_rating_cond,
                  rt.show_rating as show_rating,
									rs.s_img_off  as s_img_off,
									rs.s_img_width as s_img_width,
									rs.s_img_height as s_img_height,
									rs.star_color as star_color,
									rs.star_lables as star_lables,
									rs.star_number as star_number
									FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." rt,".GEODIR_REVIEWRATING_STYLE_TABLE." rs
									WHERE rt.category_id = rs.id $where order by rt.id");

	if(!empty($results) && $cat_id != '')
		return $results[0];
	elseif(!empty($results))
		return $results;
	else
		return false;
}


/**
 * Rating manager delete comment by comment ID.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $comment_id The comment ID.
 */
function geodir_reviewrating_delete_comments( $comment_id ){

	global $wpdb;

	geodir_reviewrating_delete_comment_images($comment_id);

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM ".GEODIR_COMMENTS_REVIEWS_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);
}


/**
 * Rating manager set comment status.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $comment_id The comment ID.
 * @param int|string $status The comment status.
 */
function geodir_reviewrating_set_comment_status($comment_id,$status){
	global $wpdb;

	$comment_info = get_comment($comment_id);

	$post_id = isset($comment_info->comment_post_ID) ? $comment_info->comment_post_ID : '';

	if(!empty($comment_info))
		$status = $comment_info->comment_approved;

	if($status=='approve' || $status==1){$status=1;}else{$status=0;}

	$comment_info_ID = isset($comment_info->comment_ID) ? $comment_info->comment_ID : '';
	$old_rating = geodir_get_commentoverall($comment_info_ID);

	$post_type = get_post_type($post_id);



	if($comment_id){

		$overall_rating = $old_rating;

		if(isset($old_rating)){
			$comment_content = isset($comment_info->comment_content) ? $comment_info->comment_content : '';
			$sqlqry = $wpdb->prepare("UPDATE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." SET
						overall_rating = %f,
						status		= %s,
						comment_content	= %s
						WHERE comment_id = %d",
						array($overall_rating,$status,$comment_content,$comment_id)
						);

			$wpdb->query($sqlqry);

			$post_newrating = geodir_get_review_total($post_id);

			//update rating
			geodir_update_postrating($post_id,$post_type);

		}

        //update post average ratings
        geodir_reviewrating_update_post_ratings($post_id);


	}

}

/**
 * Rating manager update ratings for a Post.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $post_id The post ID.
 */
function geodir_reviewrating_update_post_ratings($post_id) {
    global $wpdb,$plugin_prefix;
    $post_type = get_post_type($post_id);
    $detail_table =  $plugin_prefix . $post_type . '_detail';
    $post_ratings = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ratings FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE post_id = %d AND status=1 AND ratings != '' AND overall_rating>0 ",
            array($post_id)
        )
    );

    $post_comments_rating = array();

    if(!empty($post_ratings)){
        $r_count=count($post_ratings);
        $optional_multirating = get_option('geodir_reviewrating_optional_multirating'); // Allow review withour rating star for multirating

		foreach($post_ratings as $rating){

            $ratings = unserialize($rating->ratings);

            foreach($ratings as $rating_id=>$rating_value){
                $rating_count = 0;

				if ( !empty($post_comments_rating) && array_key_exists($rating_id,$post_comments_rating) ) {
                    $rating_count = (int)$post_comments_rating[$rating_id]['c'];
					if ( !$optional_multirating || (float)$rating_value > 0 ) {
						$rating_count++;
					}

					$new_rating_value = (float)$post_comments_rating[$rating_id]['r']  + (float)$rating_value;

					$post_comments_rating[$rating_id]['c'] = $rating_count;
                    $post_comments_rating[$rating_id]['r'] = $new_rating_value;
                } else {
                    $rating_count = 0;
					if ( !$optional_multirating || (float)$rating_value > 0 ) {
						$rating_count++;
					}

					$post_comments_rating[$rating_id]['c'] = (int)$rating_count;
                    $post_comments_rating[$rating_id]['r'] = (float)$rating_value;
                }
            }
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table){
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE ".$detail_table." SET ratings  = %s where post_id =%d",
                array(maybe_serialize($post_comments_rating),$post_id)
            )
        );
    } else {
        update_post_meta( $post_id, 'ratings ', maybe_serialize($post_comments_rating) );
    }

}

/**
 * Rating manager filter current post comments.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $comments
 * @return array|int
 */
function geodir_reviewrating_filter_comments($comments) {
	global $post, $wpdb, $gd_comment_args, $gd_filter_comments;

	if (empty($post) || (!is_single() && !is_page()) || (isset($post->comment_count) && $post->comment_count <= 0) ) {
		return $comments;
	}

	$gd_filter_comments = true;
	$all_postypes = geodir_get_posttypes();

	if (!(!empty($post->post_type) && in_array($post->post_type, $all_postypes))) {
		return $comments;
	}

	add_filter( 'comments_clauses', 'geodir_reviewrating_comments_shorting', 10, 2 );

	$comment_args = array();
	$comment_args['post_id'] = $post->ID;
	$comment_args['status'] = 'approve';

	if ( get_option('thread_comments') ) {
		$comment_args['hierarchical'] = 'threaded';
	} else {
		$comment_args['hierarchical'] = false;
	}

	if ( is_user_logged_in() ) {
		$comment_args['include_unapproved'] = get_current_user_id();
	} else {
		$commenter = wp_get_current_commenter();
		if ( $commenter['comment_author_email'] ) {
			$comment_args['include_unapproved'] = $commenter['comment_author_email'];
		}
	}

	if ( !empty( $gd_comment_args['post_id'] ) && $gd_comment_args['post_id'] == $post->ID && !empty( $gd_comment_args['number'] ) ) {
		$comment_args['number'] = $gd_comment_args['number'];
		$comment_args['offset'] = $gd_comment_args['offset'];
	}

	$comments = get_comments( $comment_args  ); // 'status' => 'approve' means it will not show users their own unaproved reviews

	// Trees must be flattened before they're passed to the walker.
	if ( $comment_args['hierarchical'] && !empty( $comments ) ) {
		$comments_flat = array();
		foreach ( $comments as $_comment ) {
			$comments_flat[]  = $_comment;
			$comment_children = $_comment->get_children( array(
				'format' => 'flat',
				'status' => $comment_args['status'],
				'orderby' => !empty( $gd_comment_args['orderby'] ) ? : 'comment_date_gmt'
			) );

			foreach ( $comment_children as $comment_child ) {
				$comments_flat[] = $comment_child;
			}
		}

		$comments = $comments_flat;
	}

	$gd_filter_comments = false;

	return $comments;
}

/* ----------- GET UNACTION CLAIMED --------- */
/**
 * Get unread reviews count.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @return null|string
 */
function geodir_reviewrating_unread_reviews(){

	global $wpdb, $plugin_prefix;

	$geodir_review_count = $wpdb->get_var(
		"SELECT COUNT(wpc.comment_ID) FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id AND	read_unread='' ");

	return $geodir_review_count;
}


/**
 * Rating manager update Overall Rating Tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_update_overall_settings() {
    if (current_user_can('manage_options') && isset($_REQUEST['geodir_overall_rating_nonce']) && wp_verify_nonce($_REQUEST['geodir_overall_rating_nonce'], 'geodir_overall_rating')) {
        global $vailed_file_type, $wpdb;

        if (isset($_REQUEST['overall_rating_text']) && $_REQUEST['overall_rating_text'] != '') {
            $overall_rating_text = $_REQUEST['overall_rating_text'];
            update_option('geodir_reviewrating_overall_rating_texts', $overall_rating_text);
        } else {
            $overall_rating_text = get_option('geodir_reviewrating_overall_rating_texts');
        }

        $star_rating_text_value = geodir_reviewrating_serialize_star_lables($overall_rating_text);

        if (isset($_REQUEST['overall_count']) && $_REQUEST['overall_count'] != '') {
            $style_count = (int)$_REQUEST['overall_count'];
            update_option('geodir_reviewrating_overall_count', $style_count);
        } else {
            $style_count = (int)get_option('geodir_reviewrating_overall_count');
        }

        if (isset($_REQUEST['overall_color']) && $_REQUEST['overall_color'] != '') {
            $style_color = sanitize_text_field($_REQUEST['overall_color']);
            update_option('geodir_reviewrating_overall_color', $style_color);
        } else {
            $style_color = get_option('geodir_reviewrating_overall_color');
        }

        if (isset($_REQUEST['geodir_reviewrating_show_rating_cond']) && $_REQUEST['geodir_reviewrating_show_rating_cond'] != '')
            update_option('geodir_reviewrating_show_rating_cond', sanitize_text_field($_REQUEST['geodir_reviewrating_show_rating_cond']));

        $set_query = $wpdb->prepare("SET name = 'overall', star_lables = %s, star_number = %s, star_color = %s, is_default = '1' ", array($star_rating_text_value, $style_count, $style_color));

        if (!empty($_FILES) && !function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        if (isset($_FILES['file_off']) && in_array($_FILES['file_off']['type'], $vailed_file_type)) {
            $uploadedfile = $_FILES['file_off'];
            $upload_overrides = array('test_form' => false);

            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
            if (empty($movefile['error']) && !empty($movefile['file']) && file_is_valid_image($movefile['file'])) {
                $imagesize = getimagesize($movefile['file']);

                if (isset($imagesize[0])) {
                    update_option('geodir_reviewrating_overall_off_img_width', $imagesize[0]);
                }

                if ($imagesize[1]) {
                    update_option('geodir_reviewrating_overall_off_img_height', $imagesize[1]);
                }

                update_option('geodir_reviewrating_overall_off_img', $movefile['url']);
            }
        }

        // Upload reating star image for featured listing
        if (isset($_FILES['file_off_featured']) && in_array($_FILES['file_off_featured']['type'], $vailed_file_type)) {
            $uploadedfile = $_FILES['file_off_featured'];
            $upload_overrides = array('test_form' => false);

            $movefile_f = wp_handle_upload($uploadedfile, $upload_overrides);
            if (empty($movefile_f['error']) && !empty($movefile_f['file']) && file_is_valid_image($movefile_f['file'])) {
                $imagesize = getimagesize($movefile_f['file']);

                if (!empty($imagesize[0])) {
                    update_option('geodir_reviewrating_overall_off_img_width_featured', $imagesize[0]);
                }

                if (!empty($imagesize[1])) {
                    update_option('geodir_reviewrating_overall_off_img_height_featured', $imagesize[1]);
                }

                update_option('geodir_reviewrating_overall_off_img_featured', $movefile_f['url']);
            }
        }

        // Reating star style color for featured listing
        if (isset($_REQUEST['overall_color_featured']) && $_REQUEST['overall_color_featured'] != '') {
            update_option('geodir_reviewrating_overall_color_featured', sanitize_text_field($_REQUEST['overall_color_featured']));
        }

        if ($s_file_off_path = get_option('geodir_reviewrating_overall_off_img')) {
            $set_query .= $wpdb->prepare(", s_img_off = %s ", array(addslashes($s_file_off_path)));
        } else {
            $default_img = GEODIR_REVIEWRATING_PLUGINDIR_URL . "/icons/stars.png";
            update_option('geodir_reviewrating_overall_off_img', $default_img);
            $set_query .= $wpdb->prepare(", s_img_off = %s ", array(addslashes($default_img)));
        }

        if ($s_file_on_path = get_option('geodir_reviewrating_overall_on_img_width')) {
            $set_query .= $wpdb->prepare(", s_img_width = %s ", array(addslashes($s_file_on_path)));
        }

        if ($s_file_half_path = get_option('geodir_reviewrating_overall_half_img_height')) {
            $set_query .= $wpdb->prepare(", s_img_height = %s ",array(addslashes($s_file_half_path)));
        }

        geodir_add_column_if_not_exist(GEODIR_REVIEWRATING_STYLE_TABLE, 'is_default',  "ENUM( '0', '1' ) NOT NULL DEFAULT '0'");

        if (!$wpdb->get_var("SELECT id FROM " . GEODIR_REVIEWRATING_STYLE_TABLE . " WHERE is_default='1'")) {
            $wpdb->query("INSERT INTO " . GEODIR_REVIEWRATING_STYLE_TABLE . " {$set_query} ");
        }
    } else {
        wp_redirect(geodir_login_url());
        exit();
    }
}

/**
 * Rating manager update Rating Styles Tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_update_rating_styles() {
    if (current_user_can('manage_options')) {
        global $vailed_file_type, $wpdb;

        if (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'update_styles' && isset($_REQUEST['geodir_update_rating_styles_nonce'])) {
            if (!wp_verify_nonce($_REQUEST['geodir_update_rating_styles_nonce'], 'geodir_update_rating_styles')) {
                wp_redirect(home_url());
                exit();
            }

            $plugin_dir_path = GEODIR_REVIEWRATING_PLUGINDIR_PATH;

            $multi_rating_category = sanitize_text_field($_REQUEST['multi_rating_category']);
            $style_count = (int)$_REQUEST['style_count'];
            $style_color = sanitize_text_field($_REQUEST['style_color']);

            $star_rating_text = $_REQUEST['star_rating_text'];

            if (count($star_rating_text) > 0) {
                $star_rating_text_value = geodir_reviewrating_serialize_star_lables($star_rating_text);
            }

            $set_query = $wpdb->prepare("SET name = %s, star_lables = %s, star_number = %s , star_color = %s ", array($multi_rating_category, $star_rating_text_value, $style_count, $style_color));

            if (isset($_FILES['s_file_off']) && in_array($_FILES['s_file_off']['type'], $vailed_file_type)) {
                $move_off_file = $plugin_dir_path . '/images/' . $_FILES['s_file_off']['name'];

                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }

                $uploadedfile = $_FILES['s_file_off'];
                $upload_overrides = array('test_form' => false);

                $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
                if (empty($movefile['error']) && !empty($movefile['file']) && file_is_valid_image($movefile['file'])) {
                    $s_file_off_path = $movefile['url'];

                    $imagesize = getimagesize($movefile['file']);
                    if (!empty($imagesize[0])) {
                        $set_query .= $wpdb->prepare(", s_img_width = %s ", array($imagesize[0]));
                    }
                    if (!empty($imagesize[1])) {
                        $set_query .= $wpdb->prepare(", s_img_height = %s ", array($imagesize[1]));
                    }
                    $set_query .= $wpdb->prepare(", s_img_off = %s ", array($s_file_off_path));
                }
            }

            if (isset($_REQUEST['update_category']) && $_REQUEST['update_category'] != '') {
                $wpdb->query($wpdb->prepare("UPDATE " . GEODIR_REVIEWRATING_STYLE_TABLE . " {$set_query} WHERE id = %d ", array($_REQUEST['update_category'])) );
            } else {
                $wpdb->query("INSERT INTO " . GEODIR_REVIEWRATING_STYLE_TABLE . " {$set_query} ");
            }
        }

        if (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'delete_style' && isset($_REQUEST['_wpnonce'])) {
            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'geodir_delete_rating_styles_' . (int)$_REQUEST['cat_id'])) {
                wp_redirect(home_url());
				gd_die();
            }

            $wpdb->query($wpdb->prepare("DELETE FROM  " . GEODIR_REVIEWRATING_STYLE_TABLE . " WHERE id = %d", array((int)$_REQUEST['cat_id'])));
        }
    } else {
        wp_redirect(geodir_login_url());
		gd_die();
    }
}

/**
 * Rating manager delete comment images by url.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @return bool
 */
function geodir_reviewrating_delete_comment_images_by_url(){

	if(current_user_can( 'manage_options' )){

		if( isset($_REQUEST['ajax_action'])  && $_REQUEST['ajax_action']=='remove_images_by_url' && isset($_REQUEST['_wpnonce'])) {

				if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'del_img_'.$_REQUEST['remove_image_id'] ) )
					return false;

				global $wpdb;

				$remove_image_id = $_REQUEST['remove_image_id'];

				$img_url = $_REQUEST['img_url'];

				$comment_imges = $wpdb->get_var($wpdb->prepare("SELECT comment_images FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",array($remove_image_id)));

				$del_images = explode(',',$comment_imges);
				$total_images = count($del_images);

				if(($key = array_search($img_url, $del_images)) !== false) {
					unset($del_images[$key]);
					$total_images = $total_images-1;

					$wp_upload_dir = wp_upload_dir();

					$comment_img_path = $wp_upload_dir['basedir'].'/comment_images/';

					$file_name = basename($img_url);

					$new_file_name =  $comment_img_path . $file_name;

					if(file_exists($new_file_name))
					{
						unlink($new_file_name);
					}

				}

				$del_images = implode(',', $del_images);

				$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE ."
							SET comment_images = %s,
							total_images = %d
							WHERE comment_id = %d",
							array($del_images, $total_images, $remove_image_id)
						 ));

				echo $total_images;exit;
			}

	}else{

		wp_redirect(geodir_login_url());
		exit();

	}

}


/**
 * Review manager Create Ratings Tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_add_update_ratings(){

	if(current_user_can( 'manage_options' )){

		global $wpdb;
		if(isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'update_rating_category' && isset($_REQUEST['geodir_create_rating_nonce_field'])){

			if( !wp_verify_nonce( $_REQUEST['geodir_create_rating_nonce_field'], 'geodir_create_rating_nonce' )){
				wp_redirect(home_url());exit();}

			$title = $_REQUEST['rating_title'];

			if(isset($_REQUEST['number_of_post']) && $_REQUEST['number_of_post']!='')
			{
				for($j=1;$j<=$_REQUEST['number_of_post'];$j++){

					if(isset($_REQUEST['post_type'.$j]) && $_REQUEST['post_type'.$j]!=''){

						$post_type[] = $_REQUEST['post_type'.$j];

						if(isset($_REQUEST['categories_type_'.$j]) && $_REQUEST['categories_type_'.$j]!=''){

							foreach($_REQUEST['categories_type_'.$j] as $value){
								$cat_arr[] = $value;
							}
						}
					}
				}
			}

			if(count($cat_arr)>0)
				$categories = implode(',',$cat_arr);

			if(count($post_type)>0)
				$post_type = implode(',',$post_type);

			$geodir_rating_style_dl = $_REQUEST['geodir_rating_style_dl'];
			$show_text_star_count = $_REQUEST['show_star'];
      $show_rating = isset($_REQUEST['show_rating']) ? 1 :0;

			if(isset($_REQUEST['rating_cat_id']) && $_REQUEST['rating_cat_id'] != '')
			{
				$category_insert_id = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '';

				$sqlqry = $wpdb->prepare(
								"UPDATE ".GEODIR_REVIEWRATING_CATEGORY_TABLE." SET
								title		= %s,
								post_type 	= %s,
								category	= %s,
								category_id = %s,
								check_text_rating_cond = %s,
                show_rating = %s
								WHERE id = %d",
								array($title,$post_type,$categories,$geodir_rating_style_dl,$show_text_star_count,$show_rating,$_REQUEST['rating_cat_id'])
							);

				$wpdb->query($sqlqry);

			}else if($title !='' && $post_type !='' && $categories !=''){

				$geodir_rating_style_dl = $_REQUEST['geodir_rating_style_dl'];

				$sqlqry = $wpdb->prepare(
								"INSERT INTO ".GEODIR_REVIEWRATING_CATEGORY_TABLE." SET
								title		= %s,
								post_type 	= %s,
								category	= %s,
								category_id = %s,
								check_text_rating_cond = %s,
                show_rating = %s",
								array($title,$post_type,$categories,$geodir_rating_style_dl,$show_text_star_count,$show_rating)
							);

				$wpdb->query($sqlqry);
			}
		}

		if(isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'delete_rating_category' && isset($_REQUEST['_wpnonce'])){

		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_delete_rating_'.$_REQUEST['rating_cat_id'] ) ){
			wp_redirect(home_url());
			gd_die();
		}

		$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE id = %d", array($_REQUEST['rating_cat_id'])));

	}

	}else{

		wp_redirect(geodir_login_url());
		gd_die();

	}
}


/**
 * Review manager Like / Unlike icons Tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_update_review_setting(){

	global $vailed_file_type;

	if(current_user_can( 'manage_options' ) && isset($_REQUEST['geodir_update_review_nonce_field'])){

		if( !wp_verify_nonce( $_REQUEST['geodir_update_review_nonce_field'], 'geodir_update_review_nonce' )){
				wp_redirect(home_url());exit();}

		if(isset($_FILES['file_like']) && in_array($_FILES['file_like']['type'],$vailed_file_type))
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile = $_FILES['file_like'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile ) {
					update_option('geodir_reviewrating_review_like_img',$movefile['url']);
				}
		}

		if(isset($_FILES['file_unlike']) && in_array($_FILES['file_unlike']['type'],$vailed_file_type))
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile = $_FILES['file_unlike'];
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile ) {
					update_option('geodir_reviewrating_review_unlike_img',$movefile['url']);
				}
		}

	}else{

		wp_redirect(geodir_login_url());
		exit();

	}
}


/**
 * Review Rating ajax submit function.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_ajax_actions(){

	global $wpdb;
	$url = admin_url( 'admin.php');

	if(isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_multirating_options'){

		geodir_update_options(geodir_reviewrating_default_options());

		$msg = __('Your settings have been saved.', 'geodir_reviewratings');

		$msg = urlencode($msg);

		$url = add_query_arg( array('page'=> 'geodirectory','tab'=>'multirating_fields','subtab'=>'geodir_multirating_options','gdrr_success'=> $msg), esc_url( $url ) );
		wp_redirect( $url );exit;
	}

	if($_REQUEST['ajax_action'] == 'update_overall_setting'){

		geodir_reviewrating_update_overall_settings();

		$msg = __('Your settings have been saved.', 'geodir_reviewratings');

		$msg = urlencode($msg);

		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_rating_settings&gdrr_success='.$msg), esc_url( $url ) );
		wp_redirect( $url );exit;
	}

	if($_REQUEST['ajax_action'] == 'update_review_setting'){

		geodir_reviewrating_update_review_setting();

		$msg = __('Your settings have been saved.', 'geodir_reviewratings');

		$msg = urlencode($msg);

		$url = add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_manage_review&gdrr_success='.$msg), esc_url( $url ));
		wp_redirect( $url );exit;
	}

	if($_REQUEST['ajax_action'] == 'update_styles' || $_REQUEST['ajax_action'] == 'delete_style' ){

		geodir_reviewrating_update_rating_styles();

		$msg = __('Your settings have been saved.', 'geodir_reviewratings');

		if($_REQUEST['ajax_action'] == 'delete_style')
			$msg = __('Rating Style Delete successfully.', 'geodir_reviewratings');

		$msg = urlencode($msg);
		$url =  add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_rating_style&gdrr_success='.$msg), esc_url($url ) );

		wp_redirect( $url );exit;
	}

	if($_REQUEST['ajax_action'] == 'update_rating_category' || $_REQUEST['ajax_action'] == 'delete_rating_category' ){

		geodir_reviewrating_add_update_ratings();

		$msg = __('Your settings have been saved.', 'geodir_reviewratings');
		if($_REQUEST['ajax_action'] == 'delete_rating_category')
			$msg = __('Rating Delete successfully.', 'geodir_reviewratings');

		$msg = urlencode($msg);

		$url =  add_query_arg( array('page'=> 'geodirectory&tab=multirating_fields&subtab=geodir_create_rating&gdrr_success='.$msg), esc_url($url ));

		wp_redirect( $url );exit;
	}

	if($_REQUEST['ajax_action'] == 'ajax_tax_cat'){

		if(isset($_REQUEST['post_type'])){
			global $cat_display;
			$cat_display = 'select';
			// WPML
			$is_wpml = geodir_is_wpml();
			if ($is_wpml) {
				global $sitepress;
				$active_lang = ICL_LANGUAGE_CODE;

				$sitepress->switch_lang('all', true);
			}
			// WPML

			echo geodir_custom_taxonomy_walker($_REQUEST['post_type'].'category');

			// WPML
			if ($is_wpml) {
				$sitepress->switch_lang($active_lang, true);
			}
			// WPML
		}
		exit;
	}

	if ($_REQUEST['ajax_action'] == 'review_update_frontend') {
		$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
		$comment_id = isset($_REQUEST['comment_id']) ? (int)$_REQUEST['comment_id'] : '';
		$wpnonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';

		if( !wp_verify_nonce($wpnonce, 'gd-like-' . $comment_id)) {
			echo '0';
			exit;
		}
		geodir_reviewrating_save_like_unlike($comment_id, $task);
		exit;
	}

	if($_REQUEST['ajax_action'] == 'comment_actions' || $_REQUEST['ajax_action'] == 'show_tab_head'){

		geodir_reviewrating_comment_action($_REQUEST);

	}

	if(isset( $_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'remove_images_by_url'){

		geodir_reviewrating_delete_comment_images_by_url();

	}
}

/**
 * Review Rating module related Post Metabox function.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_comment_metabox(){

	add_meta_box('geodir_comment_xtra_box', __('Extra Arguments', 'geodir_reviewratings'), 'geodir_reviewrating_comment_rating_box', 'comment', 'normal','high');
}


/**
 * new comments change unread to read.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_reviews_change_unread_to_read(){

	global $wpdb, $plugin_prefix;

	if(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'reviews_fields'):

		$wpdb->query("update ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." set read_unread='1' where read_unread = ''");

	endif;
}

/**
 * Review Rating module delete images function
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $comment_id The comment ID.
 */
function geodir_reviewrating_delete_comment_images($comment_id){

	global $wpdb;

	$del_comment_imges = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT comment_images FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);

	if($del_comment_imges != '')
	{
		$del_images = explode(',', $del_comment_imges);

		$wp_upload_dir = wp_upload_dir();

		$comment_img_path = $wp_upload_dir['basedir'].'/comment_images/';

		foreach($del_images as $img)
		{
			$file_name = basename($img);

			$new_file_name =  $comment_img_path . $file_name;

			if(file_exists($new_file_name))
			{
				unlink($new_file_name);
			}
		}
	}
}

if (!function_exists('geodir_reviewrating_plupload_action')) {
    /**
     *
     *
     * @since 1.0.0
     * @package GeoDirectory_Review_Rating_Manager
     *
     * @param $upload
     * @return mixed
     */
    function geodir_reviewrating_upload_dir($upload) {
		global $wpdb, $current_user;

		$temp_folder_name = 'temp_'.$current_user->data->ID;

		if($current_user->data->ID == '')
		{
			$temp_folder_name = 'temp_'.session_id();

			$geodir_unassing_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT count(id) FROM ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." WHERE directory=%s",
					array($temp_folder_name)
				)
			);




            if ($geodir_unassing_count == 0) {
                $date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." (directory, date) VALUES (%s, %s)",
                        array($temp_folder_name, $date)
                    )
                );

            }

		}

		$upload['subdir']	= $upload['subdir'].'/'.$temp_folder_name;
		$upload['path']		= $upload['basedir'] . $upload['subdir'];
		$upload['url']		= $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}

    /**
     * Review Rating module plupload_action.
     *
     * @since 1.0.0
     * @package GeoDirectory_Review_Rating_Manager
     */
    function geodir_reviewrating_plupload_action() {

		// check ajax noonce
		$imgid = $_POST["imgid"];

		check_ajax_referer($imgid . 'pluploadan');

		// handle custom file uploaddir
		add_filter('upload_dir', 'geodir_reviewrating_upload_dir');

		// handle file upload
		$status = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'geodir_reviewrating_plupload'));
	 	// remove handle custom file uploaddir
	 	remove_filter('upload_dir', 'geodir_reviewrating_upload_dir');
		// send the uploaded file url in response
		if ( isset( $status['url'] ) && $status['url'] != '' ) {
			echo $status['url'];
		} else {
			echo '';
		}
		exit;
	}
}

/**
 * Review Rating replay link.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $link
 * @return string
 */
function geodir_reviewrating_comment_replylink($link){

	$link = '<div class="gdrr-comment-replaylink">'.$link.'</div>';

	return $link;
}

/**
 * Review Rating cancel reply link.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $link
 * @return string
 */
function geodir_reviewrating_cancle_replylink($link){

	$link = '<span class="gdrr-cancel-replaylink">'.$link.'</span>';

	return $link;
}

/**
 * Review Rating update Comment Rating.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $comment_content
 * @return mixed
 */
function geodir_reviewrating_update_comments($comment_content){
	global $wpdb, $post, $user_ID;

	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'editedcomment'):

	$comment_ID = $_REQUEST['comment_ID'];
	$comment_post_ID = $_REQUEST['comment_post_ID'];

	$ratings = '';
	if(isset($_REQUEST['geodir_rating']) && is_array($_REQUEST['geodir_rating'])){

		$rating = array();

		foreach($_REQUEST['geodir_rating'] as $key => $value ){

			if($key != 'overall'){
				$rating[$key] = $value;
			}else{
				$overall_rating = $value;
			}
		}

		if(!empty($rating))
			$ratings = serialize($rating);
	}

	$comment_images = '';
	if(isset($_POST['comment_images']) && $file_info = $_POST['comment_images']){

		if($file_info != '')
		{
			$newArr = explode(',', $file_info);
			$comment_images = geodir_reviewrating_add_remove_images($comment,$newArr);

			if(!empty($comment_images))
			{
					$comment_images = implode(',', $comment_images);
			}
		}
	}

	$status = isset($_POST['comment_status']) ? $_POST['comment_status'] : '';
	if(!empty($rating) || $overall_rating || $comment_images != ''){
		$rating_ip = geodir_get_ip();

		$sqlqry = $wpdb->prepare(
								"UPDATE ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." SET
								ratings		= %s,
								overall_rating = %f,
								comment_images = %s,
								status		= %s,
								comment_content = %s
								WHERE comment_id = %d",
								array($ratings,$overall_rating,$comment_images,$status,$comment_content,$comment_ID)
							);

		$wpdb->query($sqlqry);
		if(!empty($rating) || $overall_rating)
			geodir_reviewrating_update_postrating($comment_post_ID,$rating,$overall_rating);
	}

	endif;

	return $comment_content;
}



/**
 * Review Rating insert Comment Rating.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $comment
 * @return bool
 */
function geodir_reviewrating_save_rating($comment = 0){
    global $wpdb,$user_ID;
    $comment_obj = get_comment($comment);
    $post_id = isset($comment_obj->comment_post_ID) ? $comment_obj->comment_post_ID : '';

    if(!$post_id ){
        return;
    }

    $post = get_post( $post_id );
    $post_type = (isset($post->post_type)) ? $post->post_type : get_post_type($post_id);
    $all_postypes = geodir_get_posttypes();

    if (!in_array($post_type, $all_postypes))
        return false;

    if (!empty($post_type) && geodir_cpt_has_rating_disabled($post_type)) {
        return false;
    }

    $comment_info = get_comment($comment);
    $status = $comment_info->comment_approved;
    $rating = array();
    if (isset($_REQUEST['geodir_rating']) && is_array($_REQUEST['geodir_rating']) && get_option('geodir_reviewrating_enable_rating')) {
        foreach ($_REQUEST['geodir_rating'] as $key => $value) {
            if ($key != 'overall') {
                $rating[$key] = sanitize_text_field($value);
            } else {
                $overall_rating = sanitize_text_field($value);
            }
        }

        if (!empty($rating))
            $ratings = serialize($rating);
    } else {
        $overall_rating = sanitize_text_field($_REQUEST['geodir_overallrating']);
    }

    if (isset($comment_info->comment_parent) && (int)$comment_info->comment_parent == 0) {
        $overall_rating = $overall_rating > 0 ? $overall_rating : '0';
    } else {
        $overall_rating = '';
    }

    $comment_images = '';
    $total_images = '';

    if (isset($_POST['comment_images']) && $_POST['comment_images'] != '') {
        $file_info = $_POST['comment_images'];

        if ($file_info != '') {
            $newArr = explode(',', $file_info);
            $total_images = count($newArr);
            $comment_images = geodir_reviewrating_add_remove_images($comment, $newArr);

            if (!empty($comment_images)) {
                $comment_images = implode(',', $comment_images);
            }
        }
    }

    if (!empty($rating) || $overall_rating || $comment_images != '') {
        $ratings = isset($ratings) ? $ratings : '';
        $rating_ip = geodir_get_ip();
        global $plugin_prefix;
        $post_details = $wpdb->get_row("SELECT * FROM " . $plugin_prefix . $post_type . "_detail WHERE post_id =" . (int)$post->ID);
        $post_status = $post->post_status == 'publish' ? '1' : '0';

        $sqlqry = $wpdb->prepare(
                                "INSERT INTO ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." SET
                                post_id = %d,
                                post_title = %s,
                                post_type = %s,
                                user_id = %d,
                                comment_id = %d,
                                total_images = %d,
                                rating_ip = %s,
                                ratings = %s,
                                overall_rating = %f,
                                comment_images = %s,
                                status = %s,
                                post_status = %s,
                                post_date = %s,
                                post_city = %s,
                                post_region = %s,
                                post_country = %s,
                                post_longitude = %s,
                                post_latitude = %s,
                                comment_content = %s
                                ",
                                array($post->ID, $post->post_title, $post->post_type, $user_ID, $comment,$total_images, $rating_ip, $ratings, $overall_rating, $comment_images, $status, $post_status, date_i18n('Y-m-d H:i:s', current_time('timestamp')), $post_details->post_city, $post_details->post_region, $post_details->post_country, $post_details->post_latitude, $post_details->post_longitude, $comment_info->comment_content)
                            );

        $wpdb->query($sqlqry);


        $strpieces = explode(",", $comment_images);
        $strlength = sizeof($strpieces);
        for($i = 0; $i < $strlength; $i++)
        {
            $commment_image_adj = str_replace("https://tamzang.com/wp-content/uploads/","/",$strpieces[$i]);
            $attach_sqlqry = $wpdb->prepare(
                                           "INSERT INTO wp_geodir_attachments SET
                                           post_id = %d,user_id = %d,file =%s,mime_type = 'image/jpeg',menu_order = '0'",
                                           array($post->ID,$user_ID,$commment_image_adj)
                                         );

            $wpdb->query($attach_sqlqry);
        }


        if (!empty($rating) || $overall_rating)
            geodir_reviewrating_update_postrating($post->ID, $rating, $overall_rating);
    }

    //update post average ratings
    geodir_reviewrating_update_post_ratings($post->ID);
}

/**
 * Review Rating remove unncesssary images.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_remove_unncesssary_directories() {
    global $wpdb;

    $date = date_i18n('Y-m-d H:i:s', strtotime("-1 day"));

    $unassing_imges = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, directory FROM ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." WHERE date<=%s",
            array($date)
        )
    );

    if (!empty($unassing_imges)) {
        foreach ($unassing_imges as $unimg) {
            $wp_upload_dir = wp_upload_dir();
            $temp_folder = $wp_upload_dir['path'] . '/' . $unimg->directory;

            geodir_delete_directory($temp_folder);

            $wpdb->get_results(
                $wpdb->prepare(
                    "DELETE FROM ".GEODIR_UNASSIGN_COMMENT_IMG_TABLE." WHERE id=%d",
                    array($unimg->id)
                )
            );
        }
    }
}

/**
 * Review Rating comments shorting.
 *
 * @since 1.0.0
 * @since 1.2.4 Modified to fix review sorting.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $clauses
 * @return mixed
 */
function geodir_reviewrating_comments_shorting( $clauses ) {
	global $post, $wpdb, $comments_shorting;

	$comment_order = get_option('page_comments') ? get_option('comment_order') : NULL;
	$comments_shorting = $comment_order == 'asc' ? 'oldest' : 'latest';
	/**
	 * Filter the default comments sorting.
	 *
	 * @since 1.1.7
     * @package GeoDirectory_Review_Rating_Manager
	 *
	 * @param string $comment_sorting Sorting name to sort comments.
	 */
	$comment_sorting = apply_filters( 'geodir_reviewrating_comments_shorting_default', $comments_shorting );
	$comment_sorting = isset( $_REQUEST['comment_sorting'] ) && $_REQUEST['comment_sorting'] != '' ? $_REQUEST['comment_sorting'] : $comment_sorting;

	switch( $comment_sorting ) {
		case 'low_rating':
				$sorting_orderby = GEODIR_REVIEWRATING_POSTREVIEW_TABLE.'.overall_rating';
				$sorting_order = 'ASC';
			break;

		case 'high_rating':
				$sorting_orderby = GEODIR_REVIEWRATING_POSTREVIEW_TABLE.'.overall_rating';
				$sorting_order = 'DESC';
			break;

		case 'low_review':
				$sorting_orderby = 'wasthis_review';
				$sorting_order = 'ASC';
			break;

		case 'high_review':
				$sorting_orderby = 'wasthis_review';
				$sorting_order = 'DESC';
			break;

		case 'oldest':
				$sorting_orderby = 'comment_date_gmt';
				$sorting_order = 'ASC';
			break;

		case 'least_images':
				$sorting_orderby = 'total_images';
				$sorting_order = 'ASC';
			break;

		case 'highest_images':
				$sorting_orderby = 'total_images';
				$sorting_order = 'DESC';
			break;

		default:
				$sorting_orderby = 'comment_date_gmt';
				$sorting_order = 'DESC';
	}

    if(!isset($clauses['groupby'])){$clauses['groupby'] = '';}

	$clauses['fields']	= "*, $wpdb->comments.comment_content AS comment_content";
	$clauses['orderby']	= $sorting_orderby . ' ' . $sorting_order;
	$clauses['order'] 	= $sorting_order;
	$clauses['join'] 	.= " LEFT JOIN ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." ON ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE.".comment_id=$wpdb->comments.comment_ID";
	//$clauses['where'] 	.= " AND ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE.".comment_id = $wpdb->comments.comment_ID ";
	$clauses['groupby'] .= " $wpdb->comments.comment_ID ";


	// fix comment awaiting moderation
	if(isset($clauses['where']) && $clauses['where']){
		$clauses['where'] = str_replace(' user_id ',' '.$wpdb->comments.'.user_id',$clauses['where']);
	}

	return $clauses;
}

/**
 * Review Rating comment ajax actions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $request
 * @return bool
 */
function geodir_reviewrating_comment_action($request){
		global $wpdb;

		$comment_ids = array();
		if(isset($request['comment_ids']) && $request['comment_ids'] != '')
		$comment_ids = explode(',', $request['comment_ids']);

		if(!empty($comment_ids) && $request['comment_ids'] != ''){

			if( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_review_action_nonce' ))
				return false;

			foreach($comment_ids as $comment_id){

				if($comment_id != ''){

					switch ( $request['comment_action'] ){
						case 'deletecomment' :
							wp_delete_comment( $comment_id );
							break;
						case 'trashcomment' :
							wp_trash_comment($comment_id);
							break;
						case 'untrashcomment' :
							wp_untrash_comment($comment_id);
							break;
						case 'spamcomment' :
							wp_spam_comment($comment_id);
							break;
						case 'unspamcomment' :
							wp_unspam_comment($comment_id);
							break;
						case 'approvecomment' :
							wp_set_comment_status( $comment_id, 'approve' );
							break;
						case 'unapprovecomment' :
							wp_set_comment_status( $comment_id, 'hold' );
							break;
					}

				}

			}

				if(isset($request['geodir_comment_search']))
					$geodir_commentsearch = $request['geodir_comment_search'];

				if(isset($request['geodir_comment_posttype']))
					$post_type = $request['geodir_comment_posttype'];

				$status = $request['subtab'];

				$orderby = 'comment_date_gmt';
					$order = 'DESC';
					if(isset($request['geodir_comment_sort']) )
					{
						if($request['geodir_comment_sort'] == 'oldest'){
							$orderby = 'comment_date_gmt';
							$order = 'ASC';
					}
				}

				if(isset($request['paged']) && $request['paged'] != '')
				{
					$paged = $request['paged'];
				}
				else
				{
					$paged = 1;
				}

				$show_post = $request['show_post'];

				$defaults = array(
				'paged' => $paged,
				'show_post' => $show_post,
				'orderby' => $orderby,
				'order' => $order,
				'post_type' => $post_type,
				'comment_approved' => $status,
				'user_id' => '',
				'search' => $geodir_commentsearch,
				);

				$comments = geodir_reviewrating_get_comments($defaults);

				geodir_reviewrating_show_comments($comments['comments']);

		}

		if(isset($request['gd_tab_head'])){

			geodir_reviewrating_show_tab_head($request['gd_tab_head']);

		}

		exit;
}


/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $style_id
 * @return bool|mixed
 */
function geodir_reviewrating_get_style_by_id($style_id = '')
{
	global $wpdb;

	$select_style = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM ".GEODIR_REVIEWRATING_STYLE_TABLE." WHERE id = %d",
			array($style_id)
		)
	);

	if(!empty($select_style))
		return $select_style;
	else
		return false;
}


/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @return array|bool|mixed
 */
function geodir_reviewrating_get_styles(){

	global $wpdb;
	$styles = array();

	$styles = $wpdb->get_results("SELECT * FROM ".GEODIR_REVIEWRATING_STYLE_TABLE);

	if(!empty($styles))
		return $styles;
	else
		return false;
}


/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $comment_id The comment ID.
 * @return array|bool|mixed
 */
function geodir_reviewrating_get_comment_rating_by_id($comment_id = 0){

	global $wpdb;
	$reatings = array();

	$reatings = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT ratings,overall_rating,status FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE comment_id = %d",
			array($comment_id)
		)
	);

	if(!empty($reatings))
		return $reatings;
	else
		return false;
}


/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $post_id The post ID.
 */
function geodir_reviewrating_update_postrating_all($post_id = 0){


	global $wpdb,$plugin_prefix;

	$post = get_post( $post_id );

	$post_ratings = array();

#########################################################
$post_ratings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ratings FROM ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." WHERE post_id = %d AND status=1 AND ratings != ''",
				array($post_id)
			)
		);

		$post_comments_rating = array();
		$new_ratings = array();

		if(!empty($post_ratings)){
		$r_count=count($post_ratings);
			foreach($post_ratings as $rating){

				$ratings = unserialize($rating->ratings);

				foreach($ratings as $rating_id=>$rating_value){

					if( !empty($post_comments_rating) && array_key_exists($rating_id,$post_comments_rating) ){

						$new_rating_value = (float)$post_comments_rating[$rating_id]['r'] + (float)$rating_value;
						$post_comments_rating[$rating_id]['c'] = $r_count;
						$post_comments_rating[$rating_id]['r'] = $new_rating_value;

					}else{
						$post_comments_rating[$rating_id]['c'] = (float)$r_count;
						$post_comments_rating[$rating_id]['r'] = (float)$rating_value;
					}

				}

			}

		}
		if($post_comments_rating){$new_ratings = $post_comments_rating;}
##########################################################


	//update rating
	geodir_update_postrating($post_id,$post->post_type);



}

/**
 * Review manager update rating for a post.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $post_id The post ID.
 * @param array $ratings The rating information.
 * @param float $overall overall rating.
 */
function geodir_reviewrating_update_postrating($post_id = 0, $ratings, $overall){
	geodir_reviewrating_update_postrating_all($post_id); return; // DISABLED FOR NOW, WE WILL JUST CALL AN OVERAL UPDATE FUNCTION ON COMMENT SAVE. geodir_reviewrating_update_postrating_all

	global $wpdb,$plugin_prefix;

	$post = get_post( $post_id );

	$post_ratings = array();
	$post_ratings = geodir_reviewrating_get_post_rating($post_id);
	//print_r($post_ratings);exit;
	$old_ratings = $post_ratings;
	$new_ratings = array();
	//print_r($ratings);exit;
	if(!empty($ratings)){
		$r_count = count($ratings);
		foreach($ratings as $rating_id=>$rating_value){

			$rating_info = geodir_reviewrating_rating_categories($rating_id);

			if( !empty($post_ratings) && array_key_exists($rating_id,$old_ratings) ){

				$new_rating_value = (float)$old_ratings[$rating_id]['r'] + (float)$rating_value;
				$new_ratings[$rating_id]['c'] = $new_rating_value;
				$new_ratings[$rating_id]['r'] = (float)$old_ratings[$rating_id]['c']+1;

			}/*elseif($post->comment_count > 1){
				$new_ratings[$rating_id] = ($post->comment_count * $rating_info->star_number) + (float)$rating_value;
			}*/else{
				$new_ratings[$rating_id]['c'] = (float)$r_count;
				$new_ratings[$rating_id]['r'] = (float)$rating_value;
			}
		}
	}


	//update rating
	geodir_update_postrating($post_id,$post->post_type);

}


/**
 * Review manager get rating by post ID.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $post_id The post ID.
 * @return array|bool
 */
function geodir_reviewrating_get_post_rating($post_id) {
	global $wpdb, $plugin_prefix;

	$post_type = get_post_type( $post_id );
	$detail_table = $plugin_prefix . $post_type . '_detail';

	$ratings = array();

	if ($wpdb->get_var("SHOW TABLES LIKE '".$detail_table."'") == $detail_table) {
		$sql = $wpdb->prepare(
				"SELECT ratings, rating_count, overall_rating FROM ".$detail_table." WHERE post_id = %d",
				array($post_id)
			);
		$post_ratings = $wpdb->get_row($sql);

		if (!empty($post_ratings) && $post_ratings->rating_count>0 ) {
			$old_rating = @unserialize($post_ratings->ratings);

			if (function_exists('geodir_get_commentoverall_number')) {
				$overall = 	geodir_get_commentoverall_number($post_id);
			} else {
				$overall = 	$post_ratings->overall_rating/$post_ratings->rating_count;
			}
		}

	} else {
		$old_rating_val = get_post_meta( $post_id, 'ratings');
		if(isset($post_id) && is_array($old_rating_val)){$old_rating = end($old_rating_val);}else{$old_rating =array();}
		$overall_val = get_post_meta( $post_id, 'overall_rating');
		if(isset($post_id) && is_array($overall_val)){$overall= end($overall_val);}else{$overall =array();}
	}

	if (!empty($old_rating)) {
		foreach ($old_rating as $key=>$value) {
			$ratings[$key] = $value;
		}
	}

	if (isset($overall) && $overall != '') {
		$ratings['overall'] = $overall;
	}

	if(!empty($ratings))
		return $ratings;
	else
		return false;
}


/**
 * Review manager Get comments count.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $status The comment status.
 * @return null|string
 */
function geodir_reviewrating_get_comments_count($status = ''){

	global $wpdb;

	switch($status):

		case 'approved':
			$status = " AND wpc.comment_approved = '1' ";
		break;

		case 'pending':
			$status = " AND wpc.comment_approved = '0' ";
		break;

		case 'trash':
		case 'spam':
			$status = $wpdb->prepare(" AND wpc.comment_approved = %s ", array($status));
		break;

		default:
			$status = " AND wpc.comment_approved != 'spam' AND wpc.comment_approved != 'trash' ";

	endswitch;

	$geodir_review_count = $wpdb->get_var("SELECT COUNT(wpc.comment_ID)
						FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc
						WHERE wpc.comment_ID = gdc.comment_id ".$status);

	return $geodir_review_count;
	exit;
}


/**
 * Review manager Get comments.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array|string $default Comment filter array.
 * @return mixed
 */
function geodir_reviewrating_get_comments($default=''){
	global $wpdb,$paged, $show_post;

	$condition = '';
	$orderby = '';
	$value_order = '';
	$value_field = '';
	$limit = '';

	$search_array = array();
	foreach($default as $key=>$value)
	{
		if($key == 'comment_approved')
		{
			switch($value):
				case 'approved':
					$condition .= " AND wpc.comment_approved = '1' ";
				break;
				case 'pending':
					$condition .= " AND wpc.comment_approved = '0' ";
				break;
				case 'trash':
				case 'spam':
					$condition .= " AND wpc.comment_approved = '{$value}' ";
				break;
				default:
					$condition .= " AND wpc.comment_approved != 'spam' AND wpc.comment_approved != 'trash' ";
			endswitch;
		}
		elseif($key == 'orderby' || $key == 'order' )
		{
			if($key == 'orderby')
			{
				$value_field = $value;
			}
			else
			{
				$value_order = $value;
			}

			$orderby = " ORDER BY wpc.{$value_field} {$value_order} ";

			if($value_field == 'overall_rating')
			{
				$orderby = " ORDER BY gdc.{$value_field} {$value_order} ";
			}

		}
		elseif($value != '' && $key == 'post_type')
		{
			$condition .= " AND gdc.{$key} = '{$value}' ";
		}
		elseif($value != '' && $key == 'search' )
		{
			$condition .= " AND (gdc.post_title LIKE %s || wpc.comment_author LIKE %s || wpc.comment_content LIKE %s ) ";

			$search_array = array('%'.$value.'%','%'.$value.'%','%'.$value.'%');

		}
		elseif($value != '' && $key == 'paged')
		{
			$paged = $value;
			$start = $value;
		}
		elseif($value != '' && $key == 'show_post')
		{
			$show_post = $value;
		}
	}

	if($condition == '')
	{
		$condition .= " AND wpc.comment_approved != 'spam' AND wpc.comment_approved != 'trash' ";
	}

	if($start != '' && $show_post != '')
	{
		if($start > 0)
		{

			$start = ($start-1)*$show_post;

			$limit = "LIMIT $start, $show_post";
		}
		else
		{
			$limit = "LIMIT $start, $show_post";
		}
	}

	if(!empty($search_array)) {
		$array['comment_count'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(wpc.comment_ID) FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby, $search_array));

		$array['comments'] = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby.$limit, $search_array));
	}else{

		$array['comment_count'] = $wpdb->get_var("SELECT COUNT(wpc.comment_ID) FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby);

		$array['comments'] = $wpdb->get_results("SELECT * FROM $wpdb->comments wpc, ".GEODIR_REVIEWRATING_POSTREVIEW_TABLE." gdc WHERE wpc.comment_ID = gdc.comment_id ".$condition.$orderby.$limit);

	}

	return $array;

}


/**
 * Rating manager save comment like and dislike.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param int $comment_id The Comment ID.
 * @param string $like_action Like or dislike action.
 */
function geodir_reviewrating_save_like_unlike($comment_id, $like_action) {
	if ($comment_id > 0 && ($like_action == 'like' || $like_action == 'unlike')) {
		$return = geodir_reviewrating_handle_like_unlike($comment_id, $like_action);
	}
	geodir_reviewrating_comments_like_unlike($comment_id, true, true);
	exit;
}

/**
 * Add or remove images for a comment.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param object $comment The comment object.
 * @param array $newArr Images array.
 * @return array Comment Images array.
 */
function geodir_reviewrating_add_remove_images( $comment, $newArr ) {
	global $wpdb, $current_user;

	$temp_folder_name = 'temp_' . $current_user->data->ID;

	if ( $current_user->data->ID == '' ) {
		$temp_folder_name = 'temp_' . session_id();
	}

	$wp_upload_dir = wp_upload_dir();
	$temp_folder = $wp_upload_dir['path'] . '/' . $temp_folder_name;
	$comment_img_path = $wp_upload_dir['basedir'] . '/comment_images';

	if ( !is_dir( $comment_img_path ) ) {
		wp_mkdir_p( $comment_img_path );
	}

	$comment_images = array();
	foreach( $newArr as $img ) {
		$file_ext = pathinfo( $img, PATHINFO_EXTENSION );
		$file_name = basename( $img, "." . $file_ext );
		$filename =  $temp_folder . '/' . basename( $img );
		$new_file_name =  $comment_img_path . '/' . $file_name . '_' . time() . '.' . $file_ext;
		copy( $filename, $new_file_name );
		$comment_images[] = $wp_upload_dir['baseurl'] . '/comment_images/' . $file_name . '_' . time() . '.' . $file_ext;
	}

	geodir_delete_directory( $temp_folder );

	/*if ( is_dir( $temp_folder ) ) {
		$dirPath = $temp_folder;
		if ( substr( $dirPath, strlen( $dirPath ) - 1, 1) != '/' ) {
			$dirPath .= '/';
		}
		$files = glob( $dirPath . '*', GLOB_MARK );
		foreach ( $files as $file ) {
			if ( is_dir( $file ) ) {
				self::deleteDir( $file );
			} else {
				unlink( $file );
			}
		}
		rmdir($dirPath);
	}*/

	return $comment_images;
}

/**
 * Add ratings column to the detail table when a new post type get created.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $post_type The post type.
 * @return bool
 */
function geodir_reviewrating_create_new_post_type($post_type = ''){

	global $wpdb, $plugin_prefix;

	if($post_type != ''){

		$all_postypes = geodir_get_posttypes();

		if(!in_array($post_type, $all_postypes))
			return false;

		$detail_table = $plugin_prefix . $post_type . '_detail';
		geodir_add_column_if_not_exist($detail_table, 'ratings',  'TEXT NULL DEFAULT NULL');

	}
}

/**
 * function for display geodirectory review rating error and success messages.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_display_messages() {
    if (isset($_REQUEST['gdrr_success']) && $_REQUEST['gdrr_success'] != '') {
        echo '<div id="message" class="updated fade"><p><strong>' . sanitize_text_field($_REQUEST['gdrr_success']) . '</strong></p></div>';
    }

    if (isset($_REQUEST['gdrr_error']) && $_REQUEST['gdrr_error'] != '') {
        echo '<div id="payment_message_error" class="updated fade"><p><strong>' . sanitize_text_field($_REQUEST['gdrr_error']) . '</strong></p></div>';
    }
}

/**
 * Check whether to display ratings or not.
 *
 * @since 1.0.0
 * @since 1.2.4 Should not hide default rating if multirating for comment on post is disabled.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param bool $is_display Whether to display or not?
 * @param string $pageview The view template. Ex: listview, gridview etc.
 * @return bool
 */
function geodir_review_rating_is_reviews_show($is_display, $pageview){

	if(get_option('geodir_reviewrating_enable_rating') || $is_display) {
		$is_display = true;
	}else{
		$is_display = false;
	}

	return $is_display;
}


/**
 * Adds overall rating to the admin comments.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $a Comment actions.
 * @return mixed
 */
function geodir_reviewrating_comment_meta_row_action( $a ) {
	global $comment;

	if ( !empty( $comment->comment_post_ID ) && geodir_cpt_has_rating_disabled( (int)$comment->comment_post_ID ) ) {
		return $a;
	}

	$rating = geodir_get_commentoverall($comment->comment_ID);
	if($rating != 0){

		$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
		echo geodir_reviewrating_draw_overall_rating($comment_ratings->overall_rating);

	}
	return $a;
}

/**
 * Rating manager serialize star labels.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param mixed $star_lables Rating star labels.
 * @return mixed
 */
function geodir_reviewrating_serialize_star_lables( $star_lables ) {
	if ( empty( $star_lables ) ) {
		return $star_lables;
	}

	$star_lables = maybe_serialize( $star_lables );

	return $star_lables;
}

/**
 * Converts rating manager star labels to array.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param mixed $star_lables Rating star labels.
 * @param bool $translate Do you want to translate this label? Default: false.
 * @return array|mixed
 */
function geodir_reviewrating_star_lables_to_arr( $star_lables, $translate = false ) {
	if ( $star_lables == '' ) {
		return array();
	}

	if ( is_serialized( $star_lables ) ) {
		$star_lables = maybe_unserialize( $star_lables );
	} else {
		$star_lables = explode( ',', $star_lables );
	}

	if ( $translate && !empty( $star_lables ) ) {
		$translated = array();
		foreach ( $star_lables as $lable ) {
			$translated[] = __( $lable, 'geodirectory' );
		}

		$star_lables = $translated;
	}

	return $star_lables;
}

/**
 * Converts rating manager star labels to string.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param mixed $star_lables Rating star labels.
 * @param bool $translate Do you want to translate this label? Default: false.
 * @param string $separator Label separator.
 * @return array|mixed|null|string|void
 */
function geodir_reviewrating_star_lables_to_str( $star_lables, $translate = false , $separator = ',' ) {
	if ( empty( $star_lables ) ) {
		return NULL;
	}

	if ( is_serialized( $star_lables ) ) {
		$star_lables = maybe_unserialize( $star_lables );
	}

	if ( is_array( $star_lables ) ) {
		if ( $translate && !empty( $star_lables ) ) {
			$translated = array();
			foreach ( $star_lables as $lable ) {
				$translated[] = __( $lable, 'geodirectory' );
			}

			$star_lables = $translated;
		}

		$star_lables = implode( $separator, $star_lables );
	} else {
		$star_lables = __( $star_lables, 'geodirectory' );
	}
	$star_lables = $star_lables != '' ? stripslashes_deep( $star_lables ) : $star_lables;

	return $star_lables;
}

/**
 * Change star label field of rating manager.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_action_on_init() {
	global $wpdb;

	/* change field type varchar to text*/
	if ( !get_option( 'geodir_reviewrating_change_star_lables_field' ) ) {
		$wpdb->query( "ALTER TABLE `" . GEODIR_REVIEWRATING_STYLE_TABLE . "` CHANGE `star_lables` `star_lables` TEXT NOT NULL" );

		update_option( 'geodir_reviewrating_change_star_lables_field', '1' );
	}
}

/**
 * display rating summary on detail page sidebar.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $avg_rating Average post rating.
 * @param int $post_id The post ID.
 * @return string Rating summary HTML.
 */
function geodir_reviewrating_detail_page_rating_summary( $avg_rating, $post_id ) {
	$html = '';
	if ( get_option( 'geodir_reviewrating_hide_rating_summary' ) ) {
		return $html;
	}

	if ( !empty( $post_id ) && $avg_rating != '' ) {
		$geodir_post_info = geodir_get_post_info( $post_id );

		if ( !empty( $geodir_post_info ) ) {
			$post_title = $geodir_post_info->post_title;
			$post_thumbnail = '';
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );

			$overall_max_rating = (int)get_option( 'geodir_reviewrating_overall_count' );
			$total_reviews = geodir_get_review_count_total( $post_id );

			if ( $total_reviews > 0 ) {
				$html .= '<div class="average-review">';

				$avg_ratings = ( is_float( $avg_rating ) || ( strpos( $avg_rating, ".", 1 ) == 1 && strlen( $avg_rating ) > 3) ) ? number_format( $avg_rating, 1, '.', '') : $avg_rating;

				$html .= '<span>';
				$html .= '<span class="rating">' . $avg_ratings . '</span>&nbsp;/&nbsp;<span>' . $overall_max_rating . '</span> '.__( 'based on', 'geodir_reviewratings' ) . '&nbsp;<span class="count">' . $total_reviews . '</span>&nbsp;';
				$html .= $total_reviews > 1 ? __( 'reviews', 'geodir_reviewratings' ) : __( 'review', 'geodir_reviewratings' );
				$html .= '</span><br />';
				$html .= '<span class="item">';
				$html .= '<span class="fn">' . $post_title . '</span>';

				if ( $post_thumbnail_id > 0 ) {
					$attachment_image = wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );
					$post_thumbnail = !empty( $attachment_image ) && isset( $attachment_image[0] ) && $attachment_image[0] != '' ? $attachment_image[0] : '';
					if ( $post_thumbnail != '' ) {
						$html .= '<br /><img src="' . $post_thumbnail . '" class="photo"  alt="' . esc_attr( $post_title ) . '" />';
					}
				}

				$html .= '</span>';
				$html .= '</div>';
			}
		}
	}

	$html = apply_filters( 'geodir_reviewrating_filter_detail_page_rating_summary', $html, $avg_rating, $post_id ) ;

	return $html;
}


/**
 * Get the rating star labels for translation
 *
 * @since 1.1.6
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress database abstraction object.
 *
 * @param  array $translation_texts Array of text strings.
 * @return array
 */
function geodir_reviewrating_db_translation($translation_texts = array()) {
	global $wpdb;

	$translation_texts = !empty( $translation_texts ) && is_array( $translation_texts ) ? $translation_texts : array();

	// Overall star labels
	$overall_labels = get_option('geodir_reviewrating_overall_rating_texts');
	if (!empty($overall_labels)) {
		foreach ( $overall_labels as $label ) {
			if ( $label != '' )
				$translation_texts[] = stripslashes_deep($label);
		}
	}

	// Rating style table
	$sql = "SELECT name, star_lables FROM `" . GEODIR_REVIEWRATING_STYLE_TABLE . "`";
	$rows = $wpdb->get_results($sql);

	if (!empty($rows)) {
		foreach($rows as $row) {
			if (!empty($row->name))
				$translation_texts[] = stripslashes_deep($row->name);

			if (!empty($row->star_lables)) {
				$labels = geodir_reviewrating_star_lables_to_arr($row->star_lables);

				if (!empty($labels)) {
					foreach ( $labels as $label ) {
						if ( $label != '' )
							$translation_texts[] = stripslashes_deep($label);
					}
				}
			}
		}
	}

	// Rating category table
	$sql = "SELECT title FROM `" . GEODIR_REVIEWRATING_CATEGORY_TABLE . "`";
	$rows = $wpdb->get_results($sql);

	if (!empty($rows)) {
		foreach($rows as $row) {
			if (!empty($row->title))
				$translation_texts[] = stripslashes_deep($row->title);
		}
	}
	$translation_texts = !empty($translation_texts) ? array_unique($translation_texts) : $translation_texts;

	return $translation_texts;
}

/**
 * Check the comment is liked or not.
 *
 * @since 1.2.8
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $comment_id The comment ID.
 * @return bool True if liked. Otherwise false.
 */
function geodir_reviewrating_has_comment_liked($comment_id) {
	global $wpdb;

	if (!(int)$comment_id > 0) {
		return false;
	}

	$user_id = get_current_user_id();
	if (!$user_id > 0) {
		return false;
	}

	$query = $wpdb->prepare("SELECT COUNT(like_id) FROM `" . GEODIR_COMMENTS_REVIEWS_TABLE . "` WHERE comment_id = %d AND user_id = %d", array($comment_id, $user_id));
	$liked = $wpdb->get_var($query);

	if ($liked) {
		return true;
	}

	return false;
}

/**
 * Get the total number of likes for comment.
 *
 * @since 1.2.8
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $comment_id The comment ID.
 * @return int Total likes for comment.
 */
function geodir_reviewrating_get_total_liked($comment_id) {
	global $wpdb;

	$liked = 0;

	if (!(int)$comment_id > 0) {
		return $liked;
	}

	$query = $wpdb->prepare("SELECT wasthis_review FROM `" . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . "` WHERE comment_id = %d", array($comment_id));
	$liked = (int)$wpdb->get_var($query);

	return $liked;
}

/**
 * Handle and save like/unlike value.
 *
 * @since 1.2.8
 * @since 1.3.6 Hooks added for like/unlike comment.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $comment_id The comment ID.
 * @param string $action The comment usefull action (like or unlike). Default 'like'.
 * @return bool True on success. False on fail.
 */
function geodir_reviewrating_handle_like_unlike($comment_id, $action = 'like') {
	global $wpdb;

	if (!(int)$comment_id > 0) {
		return false;
	}

	$user_id = get_current_user_id();
	if (!$user_id > 0) {
		return false;
	}
	$has_liked = geodir_reviewrating_has_comment_liked($comment_id);

	if ($action == 'like') {
		if (!$has_liked) {
			$ip = geodir_get_ip();
			$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$like_date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

			$query = $wpdb->prepare("INSERT INTO `" . GEODIR_COMMENTS_REVIEWS_TABLE . "` SET comment_id = %d, ip = %s, user_id = %d, like_unlike = %d, user_agent = %s, like_date = %s", array($comment_id, $ip, $user_id, 1, $user_agent, $like_date));

			if ($wpdb->query($query)) {
				$like_id = $wpdb->insert_id;

				$query = $wpdb->prepare("UPDATE `" . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . "` SET wasthis_review = wasthis_review + 1 WHERE comment_id = %d", array($comment_id));
				$wpdb->query($query);

				do_action('geodir_reviewrating_comment_liked', $like_id);

				return true;
			}
		}
	} else {
		if ($has_liked) {
			$query = $wpdb->prepare("DELETE FROM `" . GEODIR_COMMENTS_REVIEWS_TABLE . "` WHERE comment_id = %d AND user_id = %d", array($comment_id, $user_id));

			if ($wpdb->query($query)) {
				$query = $wpdb->prepare("UPDATE `" . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . "` SET wasthis_review = wasthis_review - 1 WHERE comment_id = %d AND wasthis_review > 0", array($comment_id));
				$wpdb->query($query);

				do_action('geodir_reviewrating_comment_unliked', $comment_id, $user_id);

				return true;
			}
		}
	}

	return false;
}

if (!function_exists('geodir_get_ip')) {
/**
 * Get the visitor's IP address.
 *
 * @since 1.2.8
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @return string The IP address.
 */
function geodir_get_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Filter the the visitor's IP address.
	 *
	 * @since 1.2.8
	 * @package GeoDirectory_Review_Rating_Manager
	 *
	 * @param string $ip The IP address.
	 */
	return apply_filters('geodir_get_ip', $ip);
}
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.3.4
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_reviewrating_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));

    return $settings;
}

function geodir_reviewrating_wpml_sync_like($like_id) {
    global $wpdb;

    $like = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . GEODIR_COMMENTS_REVIEWS_TABLE . " WHERE like_id=%d", $like_id), ARRAY_A);
    if (!empty($like)) {
        $comment_id = $like['comment_id'];

        $original_comment = get_comment_meta( $comment_id, '_icl_duplicate_of', true );
        if ( $original_comment ) {
            $duplicates = $wpdb->get_col( $wpdb->prepare( "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key='_icl_duplicate_of' AND meta_value=%d", $original_comment ) );
            $duplicates = array( $original_comment ) + array_diff( $duplicates, array( $comment_id ) );
        } else {
            $duplicates = $wpdb->get_col( $wpdb->prepare( "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key='_icl_duplicate_of' AND meta_value=%d", $comment_id ) );
        }

        if (!empty($duplicates)) {
            unset($like['like_id']);

            foreach ($duplicates as $dup) {
                $like['comment_id'] = $dup;
                $wpdb->insert(GEODIR_COMMENTS_REVIEWS_TABLE, $like);

                $wpdb->query($wpdb->prepare("UPDATE `" . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . "` SET wasthis_review = wasthis_review + 1 WHERE comment_id = %d", array($dup)));
            }
        }
    }
}

function geodir_reviewrating_wpml_sync_unlike($comment_id, $user_id) {
    global $wpdb;

    $original_comment = get_comment_meta( $comment_id, '_icl_duplicate_of', true );

    if ( $original_comment ) {
        $duplicates = $wpdb->get_col( $wpdb->prepare( "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key='_icl_duplicate_of' AND meta_value=%d", $original_comment ) );
        $duplicates = array( $original_comment ) + array_diff( $duplicates, array( $comment_id ) );
    } else {
        $duplicates = $wpdb->get_col( $wpdb->prepare( "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key='_icl_duplicate_of' AND meta_value=%d", $comment_id ) );
    }

    if (!empty($duplicates)) {
        foreach ($duplicates as $dup) {
            $wpdb->query($wpdb->prepare("DELETE FROM `" . GEODIR_COMMENTS_REVIEWS_TABLE . "` WHERE comment_id = %d AND user_id = %d", array($dup, $user_id)));

            $wpdb->query($wpdb->prepare("UPDATE `" . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . "` SET wasthis_review = wasthis_review - 1 WHERE comment_id = %d AND wasthis_review > 0", array($dup)));
        }
    }
}

function geodir_reviewrating_list_comments_args( $args ) {

    if ( !empty( $args['callback'] ) && $args['callback'] == 'geodir_comment' ) {
        $page_comments = get_option('page_comments');
        $comment_order = get_option('comment_order');
        $default_comments_page = get_option('default_comments_page');
        $default_shorting = 'latest';

        $reverse_order = false;
        if ( $page_comments ) {
            if ( $default_comments_page == 'newest' ) {
                $default_shorting = 'latest';
                $reverse_order = true;
            } else if ( $default_comments_page == 'oldest' ) {
                $default_shorting = 'oldest';
            }
        }

        $comment_sorting = apply_filters( 'geodir_reviewrating_comments_shorting_default', $default_shorting );
        $comment_sorting = isset( $_REQUEST['comment_sorting'] ) && $_REQUEST['comment_sorting'] != '' ? $_REQUEST['comment_sorting'] : $comment_sorting;

        if ( $comment_sorting == 'latest' || $comment_sorting == 'oldest' || empty( $comment_sorting ) ) {
            if ( $comment_sorting == 'latest' && ( $default_comments_page == 'oldest' || ( !$page_comments && $default_comments_page == 'newest' ) ) ) {
                 $args['reverse_top_level'] = $comment_order == 'asc' ? true : false;

                 if ( !$page_comments && $comment_order == 'asc' && ( $default_comments_page == 'newest' || $default_comments_page == 'oldest' ) ) {
                     $args['reverse_top_level'] = false;
                 }
            } else if ( $comment_sorting == 'oldest' && !$page_comments && ( $default_comments_page == 'newest' || $default_comments_page == 'oldest' ) ) {
                 $args['reverse_top_level'] = false;
            } else {
                $args['reverse_top_level'] = null;
            }
        } else {
            $args['reverse_top_level'] = $reverse_order;
        }
    }
    return $args;
}

function geodir_reviewrating_reviews_query_args( $comment_args ) {
    global $gd_comment_args;

    $gd_comment_args = $comment_args;

    return $comment_args;
}

function geodir_reviewrating_reviews_clauses( $clauses, $wp_comment_query = array() ) {
    global $post, $wpdb, $gd_comment_args, $gd_filter_reviews;

    if ( !empty( $wp_comment_query->query_vars['parent__in'][0] ) ) {
        $clauses['fields'] = $wpdb->comments . ".*, " . $wpdb->comments . ".comment_content AS comment_content";
        return $clauses;
    }

    if (empty($post) || (!is_single() && !is_page()) || (isset($post->comment_count) && $post->comment_count <= 0) || !empty($wp_comment_query->query_vars['count'])) {
        return $clauses;
    }

    $gd_filter_reviews = true;
    $all_postypes = geodir_get_posttypes();

    if (!(!empty($post->post_type) && in_array($post->post_type, $all_postypes))) {
        return $clauses;
    }

    $comments_shorting = 'latest';

    $page_comments = get_option('page_comments');
    $comment_order = get_option('comment_order');
    $default_comments_page = get_option('default_comments_page');
    $reverse_order = false;

    if ( $page_comments ) {
        if ( $default_comments_page == 'newest' ) {
            $comments_shorting = 'latest';
            $reverse_order = true;
        } else if ( $default_comments_page == 'oldest' ) {
            $comments_shorting = 'oldest';
            //$reverse_order = true;
        }
    }

    /**
     * Filter the default comments sorting.
     *
     * @since 1.1.7
     * @package GeoDirectory_Review_Rating_Manager
     *
     * @param string $comment_sorting Sorting name to sort comments.
     */
    $comment_sorting = apply_filters( 'geodir_reviewrating_comments_shorting_default', $comments_shorting );
    $comment_sorting = isset( $_REQUEST['comment_sorting'] ) && $_REQUEST['comment_sorting'] != '' ? $_REQUEST['comment_sorting'] : $comment_sorting;

    switch( $comment_sorting ) {
        case 'low_rating':
                $sorting_orderby = GEODIR_REVIEWRATING_POSTREVIEW_TABLE . '.overall_rating';
                $sorting_order = 'ASC';
            break;
        case 'high_rating':
                $sorting_orderby = GEODIR_REVIEWRATING_POSTREVIEW_TABLE . '.overall_rating';
                $sorting_order = 'DESC';
            break;
        case 'low_review':
                $sorting_orderby = 'wasthis_review';
                $sorting_order = 'ASC';
            break;
        case 'high_review':
                $sorting_orderby = 'wasthis_review';
                $sorting_order = 'DESC';
            break;
        case 'oldest':
                $sorting_orderby = 'comment_date_gmt';
                $sorting_order = 'ASC';
            break;
        case 'least_images':
                $sorting_orderby = 'total_images';
                $sorting_order = 'ASC';
            break;
        case 'highest_images':
                $sorting_orderby = 'total_images';
                $sorting_order = 'DESC';
            break;
        default:
                $sorting_orderby = 'comment_date_gmt';
                $sorting_order = 'DESC';
    }

    if ( $reverse_order ) {
        if ( $sorting_order == 'DESC' ) {
            $sorting_order = 'ASC';
        } else {
            $sorting_order = 'DESC';
        }
    }

    if ( !isset( $clauses['groupby'] ) ) {
        $clauses['groupby'] = "";
    }

    $clauses['fields'] = $wpdb->comments . ".*, " . $wpdb->comments . ".comment_content AS comment_content";
    $clauses['join'] .= " LEFT JOIN " . GEODIR_REVIEWRATING_POSTREVIEW_TABLE." ON " . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . ".comment_id = " . $wpdb->comments . ".comment_ID";
    $clauses['groupby'] .= " $wpdb->comments.comment_ID ";
    $clauses['orderby'] = $sorting_orderby . ' ' . $sorting_order;
    $clauses['order'] = $sorting_order;

    if ( $sorting_orderby != 'comment_date_gmt' ) {
        $clauses['orderby'] .= ", comment_date_gmt ";

        if ( $reverse_order ) {
            $clauses['orderby'] .= 'ASC';
        } else {
            $clauses['orderby'] .= 'DESC';
        }
    }

    // fix comment awaiting moderation
    if ( isset( $clauses['where'] ) && $clauses['where'] ) {
        $clauses['where'] = str_replace( ' user_id ', ' ' . $wpdb->comments . '.user_id', $clauses['where'] );
    }

    return $clauses;
}

function geodir_reviewrating_comments_pagenum_link( $result ) {
    global $gd_filter_reviews;

    if ( $gd_filter_reviews && !empty( $_REQUEST['comment_sorting'] ) ) {
        $result = str_replace( '#comments', '#reviews', $result );
        $result = add_query_arg( 'comment_sorting', $_REQUEST['comment_sorting'], $result );
    }

    return $result;
}
add_filter( 'get_comments_pagenum_link', 'geodir_reviewrating_comments_pagenum_link', 10, 1 );
