<?php
/**
 * GeoDirectory Event Admin
 * 
 * Main admin file which loads all settings panels and sets up admin menus.
 *
 * @author 		Vikas Sharma
 * @category 	Admin
 * @package 	GeoDirectory Events
 */
 
/* Admin init loader */
 
add_action('admin_init', 'geodir_event_activation_redirect');

add_action( 'add_meta_boxes', 'geodir_event_meta_box_add' );

add_action('menu_order', 'geodir_event_admin_menu_order',12);

add_action('custom_menu_order', 'geodir_event_admin_custom_menu_order');

add_action('geodir_event_add_fields_on_metabox', 'geodir_event_show_event_fields_html');

add_action('geodir_event_business_fields_on_metabox', 'geodir_event_show_business_fields_html');

add_action('geodir_payment_package_extra_fields','geodir_event_package_add_extra_fields', 2, 1);

add_action('geodir_before_admin_panel' , 'geodir_display_event_messages'); 

add_action('geodir_admin_option_form' , 'geodir_event_tab_content', 110);

add_filter('geodir_settings_tabs_array','geodir_event_manager_tabs',110);

add_action( 'admin_enqueue_scripts', 'geodir_event_admin_templates_styles' );

add_action( 'admin_enqueue_scripts', 'geodir_event_admin_templates_script' );

add_action('admin_init', 'geodir_event_delete_unnecessary_fields');

function geodir_event_admin_templates_script(){



	wp_register_script('geodir-event-custom-js', geodir_event_plugin_url().'/gdevents-assets/js/event-custom.js');
	wp_enqueue_script( 'geodir-event-custom-js' );
    wp_localize_script( 'geodir-event-custom-js', 'cal_trans', geodir_get_cal_trans_array() );

}

function geodir_event_admin_templates_styles(){

	wp_register_style( 'geodir-event-backend-style', geodir_event_plugin_url().'/gdevents-assets/css/admin-style.css' );
	wp_enqueue_style( 'geodir-event-backend-style' );

}

function geodir_event_meta_box_add()
{
	global $post;
  
	add_meta_box( 'geodir_event_schedule', __( 'Event Schedule', 'geodirevents' ), 'geodir_event_event_schedule_setting', 'gd_event','normal', 'high' );
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if(!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
	
	if(isset($package_info->link_business_pkg) && $package_info->link_business_pkg  == '1'){	
		
		add_meta_box('geodir_event_business',__( 'Businesses', 'geodirevents' ),'geodir_event_business_setting','gd_event','side','high');
		
	}
	
}

function geodir_event_insert_dummy_data_loop($post_type,$data_type,$item_index){

	if($post_type=='gd_event' && $data_type=='standard_events'){
		/**
		 * Contains dummy property for sale post content.
		 *
		 * @since 1.6.11
		 * @package GeoDirectory
		 */
		include_once('gdevents_dummy_post.php');
	}

}
add_action('geodir_insert_dummy_data_loop','geodir_event_insert_dummy_data_loop',10,3);

function geodir_event_date_types_for($data_types,$post_type){
	if($post_type=='gd_event'){
		$data_types = array(
			'standard_events' => array(
			'name'=>__('Events','geodirectory'),
			'count'=> 13
		)
		);
	}
	return $data_types;

}
add_filter('geodir_dummy_date_types_for','geodir_event_date_types_for',10,2);

add_action('geodir_sample_csv_download_link', 'geodir_sample_csv_for_events_download_link', 1);

function geodir_sample_csv_for_events_download_link(){
	?>
	<div class="geodir_event_csv_download">
	<a href="<?php echo geodir_event_plugin_url() . '/gdevents-assets/event_listing.csv'?>" ><?php _e("Download sample csv for Events", 'geodirevents')?></a>
	</div>
	<?php
}

/**
 * Place detail page linked events settings
 */
function geodir_event_design_settings( $settings = array() ) {
	$return = array();
	foreach ( $settings as $key => $setting ) {
		$return[] = $setting;
		
		if ( isset( $setting['type'] ) && $setting['type'] == 'sectionend' && $setting['id'] == 'detail_page_related_post_settings' ) {
			$return[] = array(
							'name' => __( 'Linked Events Settings', 'geodirevents' ),
							'type' => 'sectionstart',
							'desc' => '',
							'id' => 'geodir_event_linked_event_settings'
						);
			$return[] = array(
							'name' => __( 'Display events filter:', 'geodirevents' ),
							'desc' => '',
							'id' => 'geodir_event_linked_event_type',
							'css' => 'min-width:300px;',
							'std' => 'all',
							'type' => 'select',
							'class' => 'chosen_select',
							'options' => array_unique( array( 
											'all' => __( 'All Events', 'geodirevents' ),
											'today' => __( 'Today', 'geodirevents' ),
											'upcoming' => __( 'Upcoming', 'geodirevents' ),
											'past' => __( 'Past', 'geodirevents' ),
										) )
						);
			$return[] = array(
							'name' => __( 'Sort by:', 'geodirevents' ),
							'desc' => __( 'Set the linked event listing sort by view', 'geodirevents' ),
							'id' => 'geodir_event_linked_sortby',
							'css' => 'min-width:300px;',
							'std' => 'latest',
							'type' => 'select',
							'class' => 'chosen_select',
							'options' => array_unique( array( 
											'az' => __( 'A-Z', 'geodirevents' ),
											'latest' => __( 'Latest', 'geodirevents' ),
											'featured' => __( 'Featured', 'geodirevents' ),
											'high_review' => __( 'Review', 'geodirevents' ),
											'high_rating' => __( 'Rating', 'geodirevents' ),
											'random' => __( 'Random', 'geodirevents' ),
											'upcoming' => __( 'Upcoming', 'geodirevents' ),
										) )
						);
			$return[] = array(  
							'name' => __( 'Number of events:', 'geodirevents' ),
							'desc' => __( 'Enter number of events to display on linked events listing', 'geodirevents' ),
							'id' => 'geodir_event_linked_count',
							'type' => 'text',
							'css' => 'min-width:300px;',
							'std' => '5'
						);
			$return[] = array(
							'name' => __( 'Layout:', 'geodirevents' ),
							'desc' => __( 'Set the listing view of linked event on place detail page', 'geodirevents' ),
							'id' => 'geodir_event_linked_listing_view',
							'css' => 'min-width:300px;',
							'std' => 'gridview_onehalf',
							'type' => 'select',
							'class' => 'chosen_select',
							'options' => array_unique( array( 
											'gridview_onehalf' => __( 'Grid View (Two Columns)', 'geodirevents' ),
											'gridview_onethird' => __( 'Grid View (Three Columns)', 'geodirevents' ),
											'gridview_onefourth' => __( 'Grid View (Four Columns)', 'geodirevents' ),
											'gridview_onefifth' => __( 'Grid View (Five Columns)', 'geodirevents' ),
											'listview' => __( 'List view', 'geodirevents' ),
										) )
						);
			$return[] = array(  
							'name' => __( 'Event content excerpt:', 'geodirevents' ),
							'desc' => __( 'Enter event content excerpt character count.', 'geodirevents' ),
							'id' => 'geodir_event_linked_post_excerpt',
							'type' => 'text',
							'css' => 'min-width:300px;',
							'std' => '20'
						);
			$return[] = array( 
							'type' => 'sectionend',
							'id' => 'geodir_event_linked_event_settings'
						);
		}
	}
	return $return;
}
// This add a new fields in Geodirectory > Design > Detail > Linked Event Settings 
add_filter( 'geodir_design_settings', 'geodir_event_design_settings', 1 );
