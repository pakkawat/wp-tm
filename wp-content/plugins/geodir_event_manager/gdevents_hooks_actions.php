<?php  

add_filter( 'template_include', 'geodir_event_template_loader',0);
 
function geodir_event_template_loader($template) {
	
	if(geodir_get_current_posttype() == 'gd_event'){
	
		add_action('geodir_before_detail_fields','geodir_event_schedule_date_fields', 3);
		add_action('geodir_before_detail_fields','geodir_event_show_business_fields_html',1);
		add_filter('geodir_detail_page_sidebar_content', 'geodir_event_detail_page_sitebar_content', 2);
		add_action('geodir_before_listing', 'geodir_event_display_filter_options', 100);
		
	}
	
	return $template;
}

add_action('wp_ajax_geodir_event_manager_ajax', "geodir_event_manager_ajax");

add_action( 'wp_ajax_nopriv_geodir_event_manager_ajax', 'geodir_event_manager_ajax' );

add_filter('geodir_show_filters','geodir_add_search_fields',10,2);

add_action('geodir_after_save_listing','geodir_event_save_data',12,2);

add_action('widgets_init', 'geodir_event_register_widgets');

add_action('pre_get_posts','geodir_event_loop_filter' ,2 );

add_action('delete_post', 'geodir_event_delete_schedule' );

add_action( 'wp_enqueue_scripts', 'geodir_event_templates_styles' );

add_filter('geodir_hide_save_button', 'geodir_event_hide_save_button');

add_action('geodir_after_description_field', 'geodir_event_add_event_features', 1);

add_action('geodir_before_default_field_in_meta_box', 'geodir_event_add_event_features', 1);

add_action('geodir_after_description_on_listing_detail', 'geodir_event_before_description', 1);

add_action('geodir_after_description_on_listing_preview', 'geodir_event_before_description', 1);

add_filter('geodir_save_post_key', 'geodir_event_remove_illegal_htmltags', 1, 2);

add_filter('geodir_design_settings', 'geodir_event_add_listing_settings', 1);

add_filter('geodir_search_page_title',"geodir_event_calender_search_page_title", 1);

add_action('geodir_after_listing_post_title',"geodir_calender_event_details_after_post_title", 1);


add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_events', 10,1); 
function geodir_diagnose_multisite_conversion_events($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_gd_event_detail'] = __('Events','geodirevents');
	$table_arr['geodir_event_schedule'] = __('Event schedule','geodirevents');
	return $table_arr;
}

function geodir_event_templates_styles(){
	
	wp_register_style( 'geodir-event-frontend-style', geodir_event_plugin_url().'/gdevents-assets/css/style.css' );
	wp_enqueue_style( 'geodir-event-frontend-style' );

	
}


add_action( 'wp_enqueue_scripts', 'geodir_event_templates_script' );
function geodir_event_templates_script(){
	
	wp_enqueue_script( 'jquery' );


    if (is_page() && geodir_is_page('add-listing')) {
        wp_register_script('geodir-event-custom-js', geodir_event_plugin_url() . '/gdevents-assets/js/event-custom.min.js', array(), GDEVENTS_VERSION);
        wp_enqueue_script('geodir-event-custom-js');
        wp_localize_script( 'geodir-event-custom-js', 'cal_trans', geodir_get_cal_trans_array() );
    }

}

function geodir_event_calenders_script(){
	
	wp_register_script( 'geodir-event-calender', geodir_event_plugin_url().'/gdevents-assets/js/event_custom.min.js');
	wp_enqueue_script( 'geodir-event-calender');
	
}


add_action('wp_footer','geodir_event_localize_vars',10);
add_action('admin_footer','geodir_event_localize_vars',10);
function geodir_event_localize_vars()
{
	global $pagenow;
	
	if(geodir_is_page('add-listing') || $pagenow == 'post.php' || $pagenow == 'post-new.php'){
	
		$arr_alert_msg = array(
								'geodir_event_ajax_url' => geodir_event_manager_ajaxurl(),
								'EVENT_PLEASE_WAIT' =>__( 'Please wait...', 'geodirevents' ),
								'EVENT_CHOSEN_NO_RESULT_TEXT' =>__( 'No Business', 'geodirevents' ),
								'EVENT_CHOSEN_KEEP_TYPE_TEXT' =>__( 'Please wait...', 'geodirevents' ),
								'EVENT_CHOSEN_LOOKING_FOR_TEXT' =>__( 'We are searching for', 'geodirevents' ),
								'EVENT_CHOSEN_NO_RESULTS_MATCH_TEXT' =>__( 'No results match', 'geodirevents' ),
							);
		
		foreach ( $arr_alert_msg as $key => $value ) 
		{
			if ( !is_scalar($value) )
				continue;
			$arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
		}
	
		$script = "var geodir_event_alert_js_var = " . json_encode($arr_alert_msg) . ';';
		echo '<script>';
		echo $script ;	
		echo '</script>';
		
	}
	
}
// display linked business under detail page tabs
add_filter( 'geodir_detail_page_tab_list_extend', 'geodir_detail_page_link_business_tab' );
function geodir_detail_page_link_business_tab( $tabs_arr ) {
	global $post, $wpdb, $plugin_prefix, $character_count, $gridview_columns, $gd_query_args;
	$post_type = geodir_get_current_posttype();
	$all_postypes = geodir_get_posttypes();
		
	if ( !empty($post) && !empty($post->ID) && !empty( $tabs_arr ) && $post_type != 'gd_event' && in_array( $post_type, $all_postypes ) && ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) ) {			
		$old_character_count = $character_count;
		$old_gridview_columns = $gridview_columns;
		$old_gd_query_args = $gd_query_args;
		
		$event_type = get_option( 'geodir_event_linked_event_type', 'all' );
		$list_sort = get_option( 'geodir_event_linked_sortby', 'latest' );
		$post_number = get_option( 'geodir_event_linked_count', '5' );
		$gridview_columns = get_option( 'geodir_event_linked_listing_view', 'gridview_onehalf' );
		$character_count = get_option( 'geodir_event_linked_post_excerpt', '20' );
		
		if ( empty( $gd_query_args ) ) {
			$gd_query_args = array();
		}
		$gd_query_args['is_geodir_loop'] = true;
				
		$listing_ids = geodir_event_link_businesses( $post->ID, $post_type );
		if ( !empty( $listing_ids ) ) {
			$listings_data = geodir_event_link_businesses_data( $listing_ids, $event_type, $list_sort, $post_number );
			if ( !empty( $listings_data ) ) {
				$html = geodir_event_get_link_business( $listings_data );
				if ( $html ) {
					$post->link_business = '';
					$tabs_arr['link_business'] = array( 
														'heading_text' => __( 'Events', 'geodirevents' ),
														'is_active_tab' => false,
														'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, 'link_business'),
														'tab_content' => $html
													);
				}
			}
		}
		
		global $character_count, $gridview_columns;
		$old_gd_query_args = $gd_query_args;
		$character_count = $old_character_count;
		$gridview_columns = $old_gridview_columns;
	}
	return $tabs_arr;
}

// display link business on event detail page to go back to the linked listing
add_action( 'geodir_after_detail_page_more_info', 'geodir_event_display_link_business' );

// update for recurring event
add_action( 'wp', 'geodir_event_add_field_in_table');
add_action( 'wp_admin', 'geodir_event_add_field_in_table');

function geodir_event_add_field_in_table(){
	global $wpdb, $plugin_prefix;
	
	if ( !get_option( 'geodir_event_recurring_feature' ) ) {
		if ( !$wpdb->get_var( "SHOW COLUMNS FROM " . EVENT_DETAIL_TABLE . " WHERE field = 'is_recurring'" ) ) {
			$wpdb->query( "ALTER TABLE " . EVENT_DETAIL_TABLE . " ADD `is_recurring` TINYINT( 1 ) NOT NULL DEFAULT '0'" );
			
			$wpdb->query( "UPDATE " . EVENT_DETAIL_TABLE . " SET `is_recurring` = 1" );
		}
		
		if ( !$wpdb->get_var( "SHOW COLUMNS FROM " . EVENT_SCHEDULE . " WHERE field = 'event_enddate'" ) ) {
			$wpdb->query( "ALTER TABLE " . EVENT_SCHEDULE . " ADD `event_enddate` DATE NOT NULL" );
			$wpdb->query( "ALTER TABLE " . EVENT_SCHEDULE . " ADD `recurring` TINYINT( 1 ) NOT NULL DEFAULT '0'" );
		}
		if ( !$wpdb->get_var( "SHOW COLUMNS FROM " . EVENT_SCHEDULE . " WHERE field = 'all_day'" ) ) {
			$wpdb->query( "ALTER TABLE " . EVENT_SCHEDULE . " ADD `all_day` TINYINT( 1 ) NOT NULL DEFAULT '0'" );
		}
		
		update_option( 'geodir_event_recurring_feature', '1' );
	}
}

// add date to title for resurring event
function geodir_event_title_recurring_event( $title, $post_id = null ) {
	global $post, $geodir_date_format;

    $gd_post_type = !empty( $post ) && isset( $post->post_type ) ? $post->post_type : '';
    if ( $gd_post_type != 'gd_event' ) {
		return $title;
	}
	
	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $post );
	
	if ( !$recurring_pkg ) {
		return $title;
	}
	
	if ( !empty( $post ) && !empty( $post_id ) && isset( $post->ID ) && $post->ID == $post_id && isset( $post->post_type ) && $post->post_type == 'gd_event' && !empty( $post->is_recurring ) ) {
		
		$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ));
		$current_time = strtotime($current_date);
		
		if ( !empty( $post->event_date ) && geodir_event_is_date( $post->event_date ) ) {
			$event_start_time = strtotime(date_i18n( 'Y-m-d', strtotime($post->event_date)));
			$event_end_time = isset($post->event_enddate) && geodir_event_is_date($post->event_enddate) ? strtotime($post->event_enddate) : 0;
			
			if ($event_end_time > $event_start_time && $event_start_time <= $current_time && $event_end_time >= $current_time) {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, $current_time ) ) . "</span>";
			} else {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, strtotime( $post->event_date ) ) ) . "</span>";
			}
		} else {
			if ( is_single() && isset( $_REQUEST['gde'] ) && geodir_event_is_date( $_REQUEST['gde'] ) && geodir_event_schedule_exist( $_REQUEST['gde'], $post_id ) ) {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, strtotime( $_REQUEST['gde'] ) ) ) . "</span>";
			}
		}
	}
	return $title;
}
add_filter( 'the_title', 'geodir_event_title_recurring_event', 100, 2 );

// get link for resurring event
function geodir_event_link_recurring_event( $link ) {
	global $post;

    if($post->post_type!='gd_event'){return $link;}
	
	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $post );
	
	if ( !$recurring_pkg ) {
		return $link;
	}
	
	if ( !empty( $post ) && isset( $post->ID ) && !empty( $post->is_recurring ) && !empty( $post->event_date ) ) {
		if ( geodir_event_is_date( $post->event_date ) && get_permalink() == get_permalink( $post->ID ) ) {
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ));
			$current_time = strtotime($current_date);
			
			$event_start_time = strtotime(date_i18n( 'Y-m-d', strtotime($post->event_date)));
			$event_end_time = isset($post->event_enddate) && geodir_event_is_date($post->event_enddate) ? strtotime($post->event_enddate) : 0;
			
			if ($event_end_time > $event_start_time && $event_start_time <= $current_time && $event_end_time >= $current_time) {
				$link_date = date_i18n( 'Y-m-d', strtotime( $current_time ) );
			} else {
				$link_date = date_i18n( 'Y-m-d', strtotime( $post->event_date ) );
			}
		
			// recuring event link
			$link = geodir_getlink( get_permalink( $post->ID ), array( 'gde' => $link_date ) );
		}
	}
	return $link;
}
add_filter( 'the_permalink', 'geodir_event_link_recurring_event', 100 );

// Filter the page title for event listing.
add_filter( 'geodir_listing_page_title', 'geodir_event_listing_page_title', 2, 10);

// Remove past event count from popular category count.
if ( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	add_filter( 'get_terms', 'geodir_event_get_terms', 20, 3 );
	
	add_filter( 'geodir_category_term_link', 'geodir_event_category_term_link', 20, 3 );
}

//gd core bestof widget
add_action('geodir_bestof_get_widget_listings_before', 'geodir_bestof_remove_event_filters');
function geodir_bestof_remove_event_filters() {
    remove_filter('geodir_filter_widget_listings_orderby','geodir_filter_event_widget_listings_orderby',10,3);
}

add_action('geodir_bestof_get_widget_listings_after', 'geodir_bestof_after_event_filters');
function geodir_bestof_after_event_filters() {
    add_filter('geodir_filter_widget_listings_orderby','geodir_filter_event_widget_listings_orderby',10,3);
}

add_action('wp_loaded', 'geodir_event_review_count_force_update');
add_filter('geodir_count_reviews_by_term_sql', 'geodir_event_count_reviews_by_term_sql', 10, 4);
add_filter('geodir_location_count_reviews_by_term_sql', 'geodir_event_count_reviews_by_location_term_sql', 10, 7);
add_filter('geodir_bestof_widget_view_all_link', 'geodir_event_bestof_widget_view_all_link', 10, 3);
add_filter('geodir_event_filter_widget_events_join', 'geodir_function_widget_listings_join', 10, 1);
add_filter('geodir_event_filter_widget_events_where', 'geodir_function_widget_listings_where', 10, 1);
add_action('geodir_infowindow_meta_before', 'geodir_event_infowindow_meta_event_dates', 10, 3);

// Remove past event count from popular category count.
if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'cat' && isset($_REQUEST['gd_posttype']) && $_REQUEST['gd_posttype'] == 'gd_event') {
	add_filter('geodir_home_map_listing_join', 'geodir_event_home_map_marker_query_join', 99, 1);
	add_filter('geodir_home_map_listing_where', 'geodir_event_home_map_marker_query_where', 100, 1);
}



add_filter('geodir_details_schema','geodir_event_schema_filter',10,2);
/*
 * Filter the schema data for events.
 *
 * Used to filter the schema data to remove non event fields and add location and event start date/time.
 *
 * @since 1.3.1
 * @param array $schema The schema array of info.
 * @param object $post The post object.
 * @return array The filtered schema array.
 */
function geodir_event_schema_filter($schema, $post) {
    if ($schema['@type'] == 'Event') {
        if (!empty($post->geodir_link_business)) {
            $place = array();
            $linked_post = geodir_get_post_info($post->geodir_link_business);
            $place["@type"] = "Place";
            $place["name"] =  $linked_post->post_title;
            $place["address"] = array(
                "@type" => "PostalAddress",
                "streetAddress" => $linked_post->post_address,
                "addressLocality" => $linked_post->post_city,
                "addressRegion" => $linked_post->post_region,
                "addressCountry" => $linked_post->post_country,
                "postalCode" => $linked_post->post_zip
            );
            $place["telephone"] = $linked_post->geodir_contact;
            
            if($linked_post->post_latitude && $linked_post->post_longitude) {
                $schema['geo'] = array(
                    "@type" => "GeoCoordinates",
                    "latitude" => $linked_post->post_latitude,
                    "longitude" => $linked_post->post_longitude
                );
            }
        } else {
            $place = array();
            $place["@type"] = "Place";
            $place["name"] = $schema['name'];
            $place["address"] = $schema['address'];
            $place["telephone"] = $schema['telephone'];
            $place["geo"] = $schema['geo'];
            $place["geo"] = $schema['geo'];
        }

        if (!empty($post->recurring_dates)) {
            $dates = maybe_unserialize($post->recurring_dates);

            $start_date = isset($dates['event_start']) ? $dates['event_start'] : '';
            $end_date = isset($dates['event_end']) ? $dates['event_end'] : $start_date;
            $all_day = isset($dates['all_day']) && !empty( $dates['all_day'] ) ? true : false;
            $start_time = isset($dates['starttime']) ? $dates['starttime'] : '';
            $end_time = isset($dates['endtime']) ? $dates['endtime'] : '';
            
            $startDate = $start_date;
            $endDate = $end_date;
            $startTime = $start_time;
            $endTime = $end_time;
            
            if (isset($dates['is_recurring'])) {
                if ($dates['is_recurring']) {
                    $rdates = explode(',', $dates['event_recurring_dates']);
                                    
                    $repeat_type = isset($dates['repeat_type']) && in_array($dates['repeat_type'], array('day', 'week', 'month', 'year', 'custom')) ? $dates['repeat_type'] : 'custom';
                    $duration = isset($dates['duration_x']) && $repeat_type != 'custom' && (int)$dates['duration_x'] > 0 ? (int)$dates['duration_x'] : 1;
                    $duration--;
                    
                    $different_times = isset($dates['different_times']) && !empty($dates['different_times']) ? true : false;
                    $astarttimes = isset($dates['starttimes']) && !empty($dates['starttimes']) ? $dates['starttimes'] : array();
                    $aendtimes = isset($dates['endtimes']) && !empty($dates['endtimes']) ? $dates['endtimes'] : array();
                    
                    if (isset($_REQUEST['gde']) && in_array($_REQUEST['gde'], $rdates)) {
                        $key = array_search($_REQUEST['gde'], $rdates);
                        
                        $startDate =  sanitize_text_field($_REQUEST['gde']);
                        
                        if ($repeat_type == 'custom' && $different_times) {
                            if (!empty($astarttimes) && isset($astarttimes[$key])) {
                                $startTime = $astarttimes[$key];
                                $endTime = $aendtimes[$key];
                            } else {
                                $startTime = '';
                                $endTime = '';
                            }
                        }
                    } else {
                        $day_today = date_i18n('Y-m-d');
                        
                        foreach ($rdates as $key => $rdate) {
                            if (strtotime($rdate) >= strtotime($day_today)) {
                                $startDate = date_i18n('Y-m-d', strtotime($rdate));
                                
                                if ($repeat_type == 'custom' && $different_times) {
                                    if (!empty($astarttimes) && isset($astarttimes[$key])) {
                                        $startTime = $astarttimes[$key];
                                        $endTime = $aendtimes[$key];
                                    } else {
                                        $startTime = '';
                                        $endTime = '';
                                    }
                                }
                                break;
                            }
                        }
                    }
                    
                    $endDate = date_i18n('Y-m-d', strtotime($startDate . ' + ' . $duration . ' day'));
                }
            } else {
                if (!empty($dates['event_recurring_dates']) && $event_recurring_dates = explode(',', $dates['event_recurring_dates'])) {
                    $day_today = date_i18n('Y-m-d');
                    
                    foreach ($event_recurring_dates as $rdate) {
                        if (strtotime($rdate) >= strtotime($day_today)) {
                            $startDate = date_i18n('Y-m-d', strtotime($rdate));
                            break;
                        }
                    }
                    
                    if ($startDate === '' && !empty($event_recurring_dates)) {
                        $startDate = $event_recurring_dates[0];
                    }
                }
            }
            
            if ($endDate === '') {
                $endDate = $startDate;
            }
            
            $startTime = $startTime !== '' ? $startTime : '00:00';
            $endTime = $endTime !== '' ? $endTime : '00:00';
            
            if ($startDate == $endDate && $startTime == $endTime && $startTime == '00:00') {
                $endTime = '23:59';
            }
            
            $schema['startDate'] = $startDate . 'T' . $startTime;
            $schema['endDate'] = $endDate . 'T' . $endTime;
        }
        $schema['location'] = $place;

        unset($schema['telephone']);
        unset($schema['address']);
        unset($schema['geo']);
    }

    return $schema;
}

add_filter('geodir_advance_search_filter_titles', 'geodir_event_search_calendar_day_filter_title', 10, 1);
add_filter('geodir_title_meta_settings', 'geodir_event_filter_title_meta_vars', 10, 1);
add_filter('geodir_filter_title_variables_vars', 'geodir_event_filter_title_variables_vars', 10, 4);
add_filter('geodir_related_posts_widget_query_args', 'geodir_event_related_posts_query_args', 10, 2);

if (is_admin()) {
    add_filter('geodir_plugins_uninstall_settings', 'geodir_event_uninstall_settings', 10, 1);
}


/**
 * Replace schema types for even categories.
 *
 * @since 1.4.5
 * @param $schemas
 * @return array
 */
function geodir_add_event_schemas($schemas){

	if(isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy']=='gd_eventcategory') {
		$schemas = array();
		//add event schemas
		$schemas['Event'] = 'Event';
		$schemas['EventVenue'] = 'EventVenue';
		$schemas['BusinessEvent'] = 'BusinessEvent';
		$schemas['ChildrensEvent'] = 'ChildrensEvent';
		$schemas['ComedyEvent'] = 'ComedyEvent';
		$schemas['CourseInstance'] = 'CourseInstance';
		$schemas['DanceEvent'] = 'DanceEvent';
		$schemas['DeliveryEvent'] = 'DeliveryEvent';
		$schemas['EducationEvent'] = 'EducationEvent';
		$schemas['ExhibitionEvent'] = 'ExhibitionEvent';
		$schemas['Festival'] = 'Festival';
		$schemas['FoodEvent'] = 'FoodEvent';
		$schemas['LiteraryEvent'] = 'LiteraryEvent';
		$schemas['MusicEvent'] = 'MusicEvent';
		$schemas['PublicationEvent'] = 'PublicationEvent';
		$schemas['SaleEvent'] = 'SaleEvent';
		$schemas['ScreeningEvent'] = 'ScreeningEvent';
		$schemas['SocialEvent'] = 'SocialEvent';
		$schemas['SportsEvent'] = 'SportsEvent';
		$schemas['TheaterEvent'] = 'TheaterEvent';
		$schemas['VisualArtsEvent'] = 'VisualArtsEvent';

	}
	return $schemas;
}
add_filter('geodir_cat_schemas', 'geodir_add_event_schemas',10,1);


add_filter('geodir_popular_post_view_list_sort','geodir_event_add_sort_option',10,2);
/**
 * Add upcoming sort option to popular post view widget options.
 *
 * @since 1.4.7
 * @param array $list_sort_arr The array of key value pairs of settings.
 * @param array $instance The array of widget settings.
 *
 * @return array The array of filtered sort options.
 */
function geodir_event_add_sort_option($list_sort_arr,$instance){

	$list_sort_arr['upcoming'] = __('Upcoming (Events Only)','geodirevents');
	return $list_sort_arr;
}


/**
 * Group the recurring events in search results.
 *
 * @since 1.4.7
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $groupby The GROUP BY clause of the query.
 * @param WP_Query $wp_query The WP_Query instance.
 * @return string Filtered GROUP BY clause of the query.
 */
function geodir_event_group_recurring_events( $groupby, $wp_query ) {
    global $wpdb;
    
    // No proximity parameter set.
    if ( !( isset( $_REQUEST['sgeo_lat'] ) && $_REQUEST['sgeo_lat'] != '' && isset( $_REQUEST['sgeo_lon'] ) && $_REQUEST['sgeo_lon'] != '' ) ) {
        return $groupby;
    }

    if ( !empty( $_REQUEST['stype'] ) && $_REQUEST['stype'] == 'gd_event' && $wp_query->is_main_query() && geodir_is_page( 'search' ) ) {
        $groupby = $wpdb->posts . ".ID";
    }

    return $groupby;
}
add_filter( 'posts_groupby', 'geodir_event_group_recurring_events', 100, 2 );