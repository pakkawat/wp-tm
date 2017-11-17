<?php
/**
 * Contains functions related to GeoDirectory Events plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Events
 */
 
// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

function geodir_load_translation_geodirevents() {
	$locale = apply_filters('plugin_locale', get_locale(), 'geodirevents');
	load_textdomain('geodirevents', WP_LANG_DIR . '/' . 'geodirevents' . '/' . 'geodirevents' . '-' . $locale . '.mo');
	load_plugin_textdomain('geodirevents', false, dirname(plugin_basename(GEODIREVENTS_PLUGIN_FILE)) . '/gdevents-languages');
	
	require_once(GEODIREVENTS_PLUGIN_PATH . '/language.php'); // Define language constants
}

function geodir_event_manager_ajaxurl(){
	return admin_url('admin-ajax.php?action=geodir_event_manager_ajax');
}

function geodir_event_plugin_url() { 
	return plugins_url( '', __FILE__ );
	/*
	if (is_ssl()) : 
		return str_replace('http://', 'https://', WP_PLUGIN_URL) . "/" . plugin_basename( dirname(__FILE__)); 
	else :
		return WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)); 
	endif;
	*/
}

function geodir_event_plugin_path() {
	return WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__)); 
}

function geodir_event_add_listing_settings($arr){
	
	$event_array = array();
	foreach($arr as $key => $val){
		
		$event_array[] = $val;
		
		if($val['id'] == 'geodir_tiny_editor_on_add_listing'){
		
			$event_array[] = array(  
											'name' => __( 'Show event \'registration description\' field as editor', 'geodirevents' ),
												'desc' 		=> __( 'Select the option to show advanced editor on add listing page.', 'geodirevents' ),
												'tip' 		=> '',
												'id' 		=> 'geodir_tiny_editor_event_reg_on_add_listing',
												'css' 		=> 'min-width:300px;',
												'std' 		=> '',
												'type' 		=> 'select',
												'class'		=> 'chosen_select',
												'options' => array_unique( array( 
																			'' => __( 'Select', 'geodirevents' ),
																			'yes' => __( 'Yes', 'geodirevents' ),
																			'no' => __( 'No', 'geodirevents' ),
																		))
											);
	
		}
	}
	
	return $event_array;
}

function geodir_event_detail_page_sitebar_content($arr){
	
	$geodir_count = count($arr);
	
	$schedule_array = array();
	
	if(!empty($arr)){
		foreach($arr as $key => $val){
			
			if($geodir_count > 4){
				if( $key == 4)
					$schedule_array[] = 'geodir_event_show_shedule_date';
			}else{
				if( $key == $geodir_count)
					$schedule_array[] = 'geodir_event_show_shedule_date';
			}
			
			$schedule_array[] = $val;
		
		}
	}else{
		$schedule_array[] = 'geodir_event_show_shedule_date';
	}
	
	return $schedule_array;
}

function geodir_event_calender_search_page_title($title){
		
	global $condition_date;	
	
	if(isset($_REQUEST['event_calendar']) && !empty($_REQUEST['event_calendar']) && geodir_is_page('search'))
		$title = apply_filters('geodir_calendar_search_page_title', __(' Browsing Day', 'geodirevents').'" '.date_i18n('F  d, Y',strtotime($condition_date)).'"');
	
	return $title;
}

function geodir_event_schedule_date_fields() {
	global $post;
	
	$recuring_data = array();
	
	if (isset($_REQUEST['backandedit'])) {
		$post = (array)$post;
		$recuring_data['is_recurring'] = !empty( $post['is_recurring'] ) ? true : false;
		$recuring_data['event_start'] = isset($post['event_start']) ? $post['event_start'] : '';
		$recuring_data['event_end'] = isset($post['event_end']) ? $post['event_end'] : '';
		$recuring_data['event_recurring_dates'] = isset($post['event_recurring_dates']) ? $post['event_recurring_dates'] : '';
		$recuring_data['all_day'] = isset($post['all_day']) ? $post['all_day'] : '';
		$recuring_data['starttime'] = isset($post['starttime']) && !$recuring_data['all_day'] ? $post['starttime'] : '';
		$recuring_data['endtime'] = isset($post['endtime']) && !$recuring_data['all_day'] ? $post['endtime'] : '';
		$recuring_data['different_times'] = isset($post['different_times']) ? $post['different_times'] : '';
		$recuring_data['starttimes'] = isset($post['starttimes']) ? $post['starttimes'] : '';
		$recuring_data['endtimes'] = isset($post['endtimes']) ? $post['endtimes'] : '';
		$recuring_data['repeat_type'] = isset($post['repeat_type']) && $recuring_data['is_recurring'] ? $post['repeat_type'] : '';
		$recuring_data['repeat_x'] = isset($post['repeat_x']) && $recuring_data['is_recurring'] ? $post['repeat_x'] : '';
		$recuring_data['duration_x'] = isset($post['duration_x']) && $recuring_data['is_recurring'] && $recuring_data['repeat_type'] != 'custom' ? $post['duration_x'] : '';
		$recuring_data['repeat_end_type'] = isset($post['repeat_end_type']) && $recuring_data['is_recurring'] ? $post['repeat_end_type'] : '';
		$recuring_data['max_repeat'] = isset($post['max_repeat']) && $recuring_data['is_recurring'] ? $post['max_repeat'] : '';
		$recuring_data['repeat_end'] = isset($post['repeat_end']) && $recuring_data['is_recurring'] ? $post['repeat_end'] : '';
		$recuring_data['repeat_days'] = isset($post['repeat_days']) && $recuring_data['is_recurring'] ? $post['repeat_days'] : '';
		$recuring_data['repeat_weeks'] = isset($post['repeat_weeks']) && $recuring_data['is_recurring'] ? $post['repeat_weeks'] : '';
	} else {
		$recuring_data = maybe_unserialize($post->recurring_dates); 
	}

	geodir_event_show_event_fields_html($recuring_data);
}

function geodir_event_save_data( $post_id = '', $request_info ) {
	global $wpdb, $current_user;

	$gd_post_info = array();
	$last_post_id = $post_id;
	$post_type = get_post_type( $post_id );
	
	if ( $post_type != 'gd_event' ) {
		return false;
	}

	// fix any date formatting issues.
	$format = geodir_event_field_date_format();
	$default_start = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );

	if ($format != 'Y-m-d') {
		if (isset($request_info['event_start']) && $request_info['event_start'] ){
			$request_info['event_start'] = geodir_maybe_untranslate_date($request_info['event_start']);
			$temp_event_start = DateTime::createFromFormat($format, $request_info['event_start'] );
			$request_info['event_start']  = !empty($temp_event_start) ? $temp_event_start->format('Y-m-d') : $request_info['event_start'];
		}

		if (isset($request_info['event_end']) && $request_info['event_end'] ){
			$request_info['event_end'] = geodir_maybe_untranslate_date($request_info['event_end']);
			$temp_event_end = DateTime::createFromFormat($format, $request_info['event_end']);
			$request_info['event_end']  = !empty($temp_event_end) ? $temp_event_end->format('Y-m-d') : $request_info['event_end'];
		}
		
		if (isset($request_info['repeat_end']) && $request_info['repeat_end'] ){
			$request_info['repeat_end'] = geodir_maybe_untranslate_date($request_info['repeat_end']);
			$temp_repeat_end = DateTime::createFromFormat($format, $request_info['repeat_end']);
			$request_info['repeat_end']  = !empty($temp_repeat_end) ? $temp_repeat_end->format('Y-m-d') : $request_info['repeat_end'];
		}
	}

	$gd_post_info['event_reg_desc'] = isset( $request_info['event_reg_desc'] ) ? $request_info['event_reg_desc'] : '';
	$gd_post_info['event_reg_fees'] = isset( $request_info['event_reg_fees'] ) ? $request_info['event_reg_fees'] : '';
	
	// Check recurring enabled
	$post_info = geodir_get_post_info( $post_id );
	$recurring_pkg = geodir_event_recurring_pkg( $post_info );
	
	// recurring event
	$is_recurring = isset( $request_info['is_recurring'] ) && empty( $request_info['is_recurring'] ) ? false : true;
	$event_date = isset( $request_info['event_date'] ) ? trim( $request_info['event_date'] ) : ( isset( $request_info['event_recurring_dates'] ) ? $request_info['event_recurring_dates'] : '' );
	$event_start = isset( $request_info['event_start'] ) ? trim( $request_info['event_start'] ) : $event_date;
	$event_end = isset( $request_info['event_end'] ) ? trim( $request_info['event_end'] ) : '';
	$all_day = isset( $request_info['all_day'] ) && !empty( $request_info['all_day'] ) ? true : false;
	$starttime = isset( $request_info['starttime'] ) && !$all_day ? trim( $request_info['starttime'] ) : '';
	$endtime = isset( $request_info['endtime'] ) && !$all_day ? trim( $request_info['endtime'] ) : '';
	$repeat_days = array();
	$repeat_weeks = array();
	
	// recurring event
	if ( $recurring_pkg && $is_recurring ) {
		$repeat_type = isset( $request_info['repeat_type'] ) && in_array( $request_info['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $request_info['repeat_type'] : 'custom'; // day, week, month, year, custom
		$different_times = isset( $request_info['different_times'] ) && !empty( $request_info['different_times'] ) ? true : false;
		$starttimes = $different_times && !$all_day && isset( $request_info['starttimes'] ) ? $request_info['starttimes'] : array();
		$endtimes = $different_times && !$all_day && isset( $request_info['endtimes'] ) && !empty( $request_info['endtimes'] ) ? $request_info['endtimes'] : array();

		// week days
		if ( $repeat_type == 'week' || $repeat_type == 'month' ) {
			$repeat_days = isset( $request_info['repeat_days'] ) ? $request_info['repeat_days'] : $repeat_days;
		}
		
		// by week
		if ( $repeat_type == 'month' ) {
			$repeat_weeks = isset( $request_info['repeat_weeks'] ) ? $request_info['repeat_weeks'] : $repeat_weeks;
		}
			
		if ( $repeat_type == 'custom' ) {
            if ( !geodir_event_is_date( $event_start ) ) {
                $event_start = $default_start;
            }

			$event_recurring_dates = (isset( $request_info['event_recurring_dates'] ) && $request_info['event_recurring_dates'] ) ? trim( $request_info['event_recurring_dates'] ) : $event_start;
			$event_recurring_dates = geodir_event_parse_dates( $event_recurring_dates );
						
			if ( $different_times == 1 ) {
				$starttime = '';
				$endtime = '';
			}
			
			$event_start = !empty( $event_recurring_dates[0] ) ? $event_recurring_dates[0] : $default_start;
			$event_end = $event_start;
			$duration_x = 1;
			
			$repeat_x = 1;
			$repeat_end_type = 0;
			$max_repeat = 1;
			$repeat_end = '';
			
			$event_recurring_dates = !empty( $event_recurring_dates ) ? implode( ',', $event_recurring_dates ) : $event_start;			
		} else {
			$repeat_x = isset( $request_info['repeat_x'] ) ? trim( $request_info['repeat_x'] ) : '';
			$duration_x = isset( $request_info['duration_x'] ) ? trim( $request_info['duration_x'] ) : 1;
			$repeat_end_type = isset( $request_info['repeat_end_type'] ) ? trim( $request_info['repeat_end_type'] ) : 0;
			$event_end = '';
			
			$max_repeat = $repeat_end_type != 1 && isset( $request_info['max_repeat'] ) ? (int)$request_info['max_repeat'] : 1;
			$repeat_end = $repeat_end_type == 1 && isset( $request_info['repeat_end'] ) ? $request_info['repeat_end'] : '';
						
			$repeat_x = $repeat_x > 0 ? (int)$repeat_x : 1;
			$duration_x = $duration_x > 0 ? (int)$duration_x : 1;
			$max_repeat = $max_repeat > 0 ? (int)$max_repeat : 1;
			
			if ( $repeat_end_type == 1 && !geodir_event_is_date( $repeat_end ) ) {
				$repeat_end = '';
			}	
			
			if ( !geodir_event_is_date( $event_start ) ) {
				$event_start = $default_start;
			}
			
			$event_recurring_dates = geodir_event_date_occurrences( $repeat_type, $event_start, $event_end, $repeat_x, $max_repeat, $repeat_end, $repeat_days, $repeat_weeks );
			$event_recurring_dates = !empty( $event_recurring_dates ) ? implode( ",", $event_recurring_dates ) : '';
		}	
	} else {
		if ( !geodir_event_is_date( $event_start ) ) {
			$event_start = $default_start;
		}
				
		if ( strtotime( $event_end ) < strtotime( $event_start ) ) {
			$event_end = $event_start;
		}
		
		$event_recurring_dates = geodir_maybe_untranslate_date($event_start);
        $temp_event_start = DateTime::createFromFormat($format, $event_recurring_dates);
        $event_recurring_dates  = !empty($temp_event_start) ? $temp_event_start->format('Y-m-d') : $event_start;
		
		$starttimes = array();
		$endtimes = array();
		
		$repeat_type = '';
		$repeat_x = '';
		$duration_x = '';
		$repeat_end_type = '';
		$max_repeat = '';
		$repeat_end = '';
		$different_times = false;
	}

	$event_shedule_info = array();
	$event_shedule_info['is_recurring'] = $is_recurring;
	$event_shedule_info['event_start'] = $event_start;
	$event_shedule_info['event_end'] = $event_end;
	$event_shedule_info['event_recurring_dates'] = $event_recurring_dates;
	$event_shedule_info['all_day'] = $all_day;
	$event_shedule_info['starttime'] = $starttime;
	$event_shedule_info['endtime'] = $endtime;
	$event_shedule_info['different_times'] = $different_times;
	$event_shedule_info['starttimes'] = $starttimes;
	$event_shedule_info['endtimes'] = $endtimes;
	$event_shedule_info['repeat_type'] = $repeat_type;
	$event_shedule_info['repeat_x'] = $repeat_x;
	$event_shedule_info['duration_x'] = $duration_x;
	$event_shedule_info['repeat_end_type'] = $repeat_end_type;
	$event_shedule_info['max_repeat'] = $max_repeat;
	$event_shedule_info['repeat_end'] = $repeat_end;
	$event_shedule_info['repeat_days'] = $repeat_days;
	$event_shedule_info['repeat_weeks'] = $repeat_weeks;
		
	// save post info
	$gd_post_info['is_recurring'] = $is_recurring;
	$gd_post_info['recurring_dates'] = maybe_serialize( $event_shedule_info );
	
	/* --- save businesses --- */
	if ( isset( $request_info['geodir_link_business' ]) ) {
		$gd_post_info['geodir_link_business'] = $request_info['geodir_link_business'];
	}

	// save event dates
	geodir_save_event_schedule( $event_shedule_info, $last_post_id ); // to create event-schedule dates	

	// save post info
	geodir_save_post_info($last_post_id, $gd_post_info);
	
	return $last_post_id;
}

function geodir_getDays($year, $startMonth=1, $startDay=1, $dayOfWeek='', $week_e = '', $check_full_start_year='', $check_full_end_year='') {
	
	$start = new DateTime(
			sprintf('%04d-%02d-%02d', $year, $startMonth, $startDay)
	);
	$start->modify($dayOfWeek);
	$end   = new DateTime(
			sprintf('%04d-12-31', $year)
	);
	$end->modify( '+1 day' );
	
	$interval = new DateInterval('P1W');
	$period   = new DatePeriod($start, $interval, $end);
	
	$dates_array = array();
	
	foreach ($period as $dt) {
		
		$date_explode = explode('-', $dt->format("Y-m-d"));
		
		$get_year = $date_explode[0];
		$get_month = $date_explode[1];
		$get_date = $date_explode[2];
		
		$check_get_date = date_i18n('Y-m-d', strtotime($get_year.'-'.$get_month.'-'.$get_date));
		
		if($get_month <= $startMonth)
		{
			if($week_e == '')
			{
				if($check_get_date <= $check_full_end_year && $check_get_date >= $check_full_start_year)
					$dates_array[] = $dt->format("Y-m-d");
			}
			
			
			$monthName = date_i18n("F", mktime(0, 0, 0, $get_month, 10));
			
			if($week_e != '')
			{
				$date_check = date_i18n("Y-m-d", strtotime("$week_e $dayOfWeek of $monthName $get_year"));
				
				if($date_check <= $check_full_end_year && $date_check >= $check_full_start_year)
					$dates_array[] = $date_check;
				
			}
		}
	
	}
	
	return $result = array_unique($dates_array);
}

function geodir_save_event_schedule( $event_shedule_info = array(), $last_post_id = '' ) {
	global $wpdb;
	
	if ( empty( $event_shedule_info ) || $last_post_id == '' ) {
		return false;
	}

	// Check recurring enabled
	$post_info = geodir_get_post_info( $last_post_id );
	$recurring_pkg = geodir_event_recurring_pkg( $post_info );
	
	$format = geodir_event_field_date_format();
	$default_start = date_i18n( $format, current_time( 'timestamp' ) );
	
	if ( !$recurring_pkg ) {
		$event_shedule_info['is_recurring'] = false;
	}

	$wpdb->query( $wpdb->prepare( "DELETE FROM " . EVENT_SCHEDULE . " WHERE event_id=%d", array( $last_post_id ) ) );
	
	$event_recurring_dates = array();
	if ( isset( $event_shedule_info['event_recurring_dates'] ) && !empty( $event_shedule_info['event_recurring_dates'] ) ) {
		if ( is_array( $event_shedule_info['event_recurring_dates'] ) ) {
			$event_recurring_dates = $event_shedule_info['event_recurring_dates'];
		} else {
			$event_recurring_dates = explode( ',', $event_shedule_info['event_recurring_dates'] );
		}
	}
	
	// all day
	$all_day = isset( $event_shedule_info['all_day'] ) && !empty( $event_shedule_info['all_day'] ) ? true : false;
	$different_times = isset( $event_shedule_info['different_times'] ) && !empty( $event_shedule_info['different_times'] ) ? true : false;
	$starttime = !$all_day && isset( $event_shedule_info['starttime'] ) ? $event_shedule_info['starttime'] : '';
	$endtime = !$all_day && isset( $event_shedule_info['endtime'] ) ? $event_shedule_info['endtime'] : '';
	$starttimes = !$all_day && isset( $event_shedule_info['starttimes'] ) ? $event_shedule_info['starttimes'] : array();
	$endtimes = !$all_day && isset( $event_shedule_info['endtimes'] ) ? $event_shedule_info['endtimes'] : array();
	
	if ( $event_shedule_info['is_recurring'] ) {		
		if ( !empty( $event_recurring_dates ) ) {
			$duration = isset( $event_shedule_info['duration_x'] ) && (int)$event_shedule_info['duration_x'] > 0 ? (int)$event_shedule_info['duration_x'] : 1;
			$repeat_type = isset( $event_shedule_info['repeat_type'] ) ? $event_shedule_info['repeat_type'] : 'custom';
									
			$recurring = 1;
			$duration--;
		
			$c = 0;
			foreach( $event_recurring_dates as $key => $date ) {
				if ( $repeat_type == 'custom' && $different_times ) {
					$duration = 0;
					$starttime = isset( $starttimes[$c] ) ? $starttimes[$c] : '';
					$endtime = isset( $endtimes[$c] ) ? $endtimes[$c] : '';
				}
				
				if ( $all_day == 1 ) {
					$starttime = '';
					$endtime = '';
				}
				
				$event_enddate = date_i18n( 'Y-m-d', strtotime( $date . ' + ' . $duration . ' day' ) );
				$sql = $wpdb->prepare( "INSERT INTO  " . EVENT_SCHEDULE . " (event_id, event_date, event_enddate, event_starttime, event_endtime, recurring, all_day) VALUES (%d, %s, %s, %s, %s, %d, %d)", array( $last_post_id, $date, $event_enddate, $starttime, $endtime, $recurring, $all_day ) ) ;
				$wpdb->query( $sql );
				$c++;
			}
		}
	} else {		
		$start_date = isset( $event_shedule_info['event_start'] ) ? $event_shedule_info['event_start'] : '';
		$end_date = isset( $event_shedule_info['event_end'] ) ? $event_shedule_info['event_end'] : $start_date;
				
		if ( !geodir_event_is_date( $start_date ) && !empty( $event_recurring_dates ) ) {
			$start_date = $event_recurring_dates[0];
		}
		
		if ( !geodir_event_is_date( $start_date ) ) {
			$start_date = $default_start;
		}
		
		if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
			$end_date = $start_date;
		}
		
		if ( $starttime == '' && !empty( $starttimes ) ) {
			$starttime = $starttimes[0];
			$endtime = $endtimes[0];
		}
		
		if ( $all_day ) {
			$starttime = '';
			$endtime = '';
		}
		$recurring = 0;
		
		$sql = $wpdb->prepare( "INSERT INTO  " . EVENT_SCHEDULE . " (event_id, event_date, event_enddate, event_starttime, event_endtime, recurring, all_day) VALUES (%d, %s, %s, %s, %s, %d, %d)", array( $last_post_id, $start_date, $end_date, $starttime, $endtime, $recurring, $all_day ) ) ;
		$wpdb->query( $sql );
	}
}

function geodir_event_delete_schedule($post_id){

	global $wpdb, $plugin_prefix;
	
	$post_type = get_post_type( $post_id );
	
	$all_postypes = geodir_get_posttypes();
	
	if(!in_array($post_type, $all_postypes))
		return false;
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".EVENT_SCHEDULE." WHERE event_id=%d", array($post_id)));
	
	$table_name = $plugin_prefix.'gd_event_detail';
	
	$wpdb->query($wpdb->prepare("UPDATE ".$table_name." SET geodir_link_business='' WHERE geodir_link_business=%s",array($post_id)));

}

function geodir_event_remove_illegal_htmltags($tags,$pkey){

	if($pkey == 'event_reg_desc'){
		$tags= '<p><a><b><i><em><h1><h2><h3><h4><h5><ul><ol><li><img><div><del><ins><span><cite><code><strike><strong><blockquote>';
	}
		
	return $tags;
}

function geodir_event_loop_filter_where($where) {
	global $wp_query, $query, $wpdb, $geodir_post_type, $table, $condition_date, $gd_session;
	
	$condition = '';
	$current_date = date_i18n('Y-m-d');
	
	if ( get_query_var( 'event_related_id' ) ) {
		if ( get_query_var( 'geodir_event_type' ) == 'feature' ) {
			$condition = " ( event_date >= '" . $current_date . "' OR ( event_date <= '" . $current_date . "' AND event_enddate >= '" . $current_date . "' ) ) ";
		}
		
		if ( get_query_var( 'geodir_event_type' ) == 'past' ) {
			$condition = " event_date <= '" . $current_date . "' ";
		}
			
		if ( get_query_var( 'geodir_event_type' ) == 'upcoming' ) {
			$condition = " ( event_date >= '" . $current_date . "' OR ( event_date <= '" . $current_date . "' AND event_enddate >= '" . $current_date . "' ) ) ";
		}
		
		$where .= " AND " . $table . ".geodir_link_business = " . get_query_var( 'event_related_id' );
	}

    if (get_query_var('geodir_event_date_calendar')) {
        if (get_query_var('gd_location') && function_exists('geodir_default_location_where')) {
            $where .= geodir_default_location_where('', $table);
        }
        
        $current_year = date_i18n('Y', get_query_var('geodir_event_date_calendar'));
        $current_month = date_i18n('m', get_query_var('geodir_event_date_calendar'));
        
        $month_start = $current_year . '-' . $current_month . '-01'; // First day of month.
        $month_end = date_i18n( 'Y-m-t', strtotime( $month_start ) ); // Get last day of month.
        
        $condition = "( ( ( '" . $month_start . "' BETWEEN event_date AND event_enddate ) OR ( event_date BETWEEN '" . $month_start . "' AND event_enddate ) ) AND ( ( '" . $month_end . "' BETWEEN event_date AND event_enddate ) OR ( event_enddate BETWEEN event_date AND '" . $month_end . "' ) ) )";
        
        $where .= " AND " . $condition;
    } else {
        if ($condition) {
            $find_postids = $wpdb->get_col("SELECT DISTINCT `event_id` FROM `" . EVENT_SCHEDULE . "` WHERE " . $condition);
            $find_postids = !empty($find_postids) && is_array($find_postids) ? implode("','", array_values($find_postids)) : '';
            $event_ids = "'" . $find_postids . "'";
            
            $where .= " AND $wpdb->posts.ID IN ($event_ids)";
        }
    }
    
	// for dashboard listing
	$is_geodir_dashbord = isset($_REQUEST['geodir_dashbord']) && $_REQUEST['geodir_dashbord'] ? true : false;
	if ( ( is_main_query() && $geodir_post_type == 'gd_event' && (geodir_is_page('listing') || is_search() || $is_geodir_dashbord) ) || get_query_var('geodir_event_listing_filter')) {
		$filter = isset($_REQUEST['etype']) ? $_REQUEST['etype'] : '';
		
		if($filter == '')
			$filter = get_option('geodir_event_defalt_filter');
		
		if(get_query_var('geodir_event_listing_filter'))
			$filter = get_query_var('geodir_event_listing_filter');
		
		if ( $filter == 'today' ) {
			$where .= " AND ( " . EVENT_SCHEDULE . ".event_date = '" . $current_date . "' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
		}
			
		if ( $filter == 'upcoming' ) {
			$where .= " AND ( " . EVENT_SCHEDULE . ".event_date >= '" . $current_date . "' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
		}
		
		if ( $filter == 'past' ) {
			$where .= " AND " . EVENT_SCHEDULE . ".event_date < '" . $current_date . "' ";
		}
		
		$where = apply_filters('geodir_event_listing_filter_where', $where, $filter);
		$is_search = is_search();
		$date_format = geodir_event_date_format();
		
		if (!empty($_REQUEST['event_start']) || !empty($_REQUEST['event_end'])) {
			$event_start = !empty($_REQUEST['event_start']) ? sanitize_text_field($_REQUEST['event_start']) : '';
			$event_end = !empty($_REQUEST['event_end']) ? sanitize_text_field($_REQUEST['event_end']) : '';
			
			if ($is_search) {
				$event_start = $event_start ? geodir_date(geodir_maybe_untranslate_date($event_start), 'Y-m-d', $date_format) : '';
				$event_end = $event_end ? geodir_date(geodir_maybe_untranslate_date($event_end), 'Y-m-d', $date_format) : '';
			} else {
				$event_start = $event_start ? date_i18n('Y-m-d', strtotime($event_start)) : '';
				$event_end = $event_end ? date_i18n('Y-m-d', strtotime($event_end)) : '';
			}
			
			if (!empty($event_start) && !empty($event_end)) {
				$where .= " AND ( ( '" . $event_start . "' BETWEEN " . EVENT_SCHEDULE . ".event_date AND " . EVENT_SCHEDULE . ".event_enddate ) OR ( " . EVENT_SCHEDULE . ".event_date BETWEEN '" . $event_start . "' AND " . EVENT_SCHEDULE . ".event_enddate ) ) AND ( ( '" . $event_end . "' BETWEEN " . EVENT_SCHEDULE . ".event_date AND " . EVENT_SCHEDULE . ".event_enddate ) OR ( " . EVENT_SCHEDULE . ".event_enddate BETWEEN " . EVENT_SCHEDULE . ".event_date AND '" . $event_end . "' ) ) ";
			} else if (!empty($event_start)) {
                $where .= " AND ( '" . $event_start . "' BETWEEN " . EVENT_SCHEDULE . ".event_date AND " . EVENT_SCHEDULE . ".event_enddate ) ";
			} else if (!empty($event_end)) {
				$where .= " AND ( '" . $event_end . "' BETWEEN " . EVENT_SCHEDULE . ".event_date AND " . EVENT_SCHEDULE . ".event_enddate ) ";
			}
		}
		
		if ($gd_session->get('all_near_me')) {
			global $plugin_prefix, $wp_query;
			
			if (!$geodir_post_type) {
				$geodir_post_type = $wp_query->query_vars['post_type'];
			}
			$table = $plugin_prefix . $geodir_post_type . '_detail';
			
			$DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
			$mylat = $gd_session->get('user_lat');
			$mylon = $gd_session->get('user_lon');
			
			if ($near_me_range = $gd_session->get('near_me_range')) {
				$dist =  $near_me_range;
			} else if($dist = get_option('geodir_near_me_dist')) {
			} else {
				$dist = 200;
			}
			
			$lon1 = $mylon - $dist / abs(cos(deg2rad($mylat)) * 69); 
			$lon2 = $mylon + $dist / abs(cos(deg2rad($mylat)) * 69);
			$lat1 = $mylat - ($dist / 69);
			$lat2 = $mylat + ($dist / 69);
			
			$rlon1 = is_numeric(min($lon1, $lon2)) ? min($lon1, $lon2) : '';
			$rlon2 = is_numeric(max($lon1, $lon2)) ? max($lon1, $lon2) : '';
			$rlat1 = is_numeric(min($lat1, $lat2)) ? min($lat1, $lat2) : '';
			$rlat2 = is_numeric(max($lat1, $lat2)) ? max($lat1, $lat2) : '';
			
			$where .= " AND ( " . $table . ".post_latitude BETWEEN $rlat1 AND $rlat2 ) AND ( " . $table . ".post_longitude BETWEEN $rlon1 AND $rlon2 ) ";
		}
		
		// filter place for linked events
		$venue = isset( $_REQUEST['venue'] ) ? $_REQUEST['venue'] : '';
		if ( $venue != '' ) {
			$venue = explode( '-', $venue, 2);
			$venue_info = !empty($venue) && isset($venue[0]) ? get_post((int)$venue[0]) : array();
			$link_business_id = !empty( $venue_info ) && isset( $venue_info->ID ) ? (int)$venue_info->ID : '-1';
			
			$where .= " AND " . $table . ".geodir_link_business = " . (int)$link_business_id;
		}		
	}
	
	if ( is_search() && isset( $_REQUEST['geodir_search'] ) && isset( $_REQUEST['event_calendar'] ) && is_main_query() ) {
		$condition_date = substr( $_REQUEST['event_calendar'], 0, 4 ) . '-' . substr( $_REQUEST['event_calendar'] , 4, 2 ) . '-' . substr( $_REQUEST['event_calendar'], 6, 2 );
		$filter_date =  date_i18n( 'Y-m-d', strtotime( $condition_date ) );
		$condition = " ( event_date = '" . $filter_date . "' OR ( event_date <= '" . $filter_date . "' AND event_enddate >= '" . $filter_date . "' ) ) ";
		
		if ( $condition ) {
			$where .= " AND $condition ";
		}
	}
	
	return $where;
}

function geodir_event_date_calendar_fields( $fields ) {
	global $query, $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix, $gd_session;

    $table = EVENT_DETAIL_TABLE;

	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $fields;
	}
	
	$schedule_table = EVENT_SCHEDULE;
		
	if ( get_query_var( 'geodir_event_date_calendar' ) ) {
		$current_year = date_i18n( 'Y', get_query_var( 'geodir_event_date_calendar' ) );
		$current_month = date_i18n( 'm', get_query_var( 'geodir_event_date_calendar' ) );

		$month_start = $current_year . '-' . $current_month . '-01'; // First day of the month.
		$month_end = date_i18n( 'Y-m-t', strtotime( $month_start ) ); // Last day of the month.
		
		$condition = "( ( ( '" . $month_start . "' BETWEEN event_date AND event_enddate ) OR ( event_date BETWEEN '" . $month_start . "' AND event_enddate ) ) AND ( ( '" . $month_end . "' BETWEEN event_date AND event_enddate ) OR ( event_enddate BETWEEN event_date AND '" . $month_end . "' ) ) ) AND " . $schedule_table . ".event_id = " . $wpdb->posts . ".ID";
		
		$fields = " ( SELECT GROUP_CONCAT( DISTINCT CONCAT( DATE_FORMAT( " . $schedule_table . ".event_date, '%d%m%y' ), '', DATE_FORMAT( " . $schedule_table . ".event_enddate, '%d%m%y' ) ) ) FROM " . $schedule_table . " WHERE " . $condition . " ) AS event_dates";
	} else {
		if ( ( is_main_query() && ( geodir_is_page( 'listing' ) || ( is_search() && isset($_REQUEST['geodir_search'])) || isset($_REQUEST['geodir_dashbord'] ) ) ) || get_query_var( 'geodir_event_listing_filter' ) ) {
			$fields .= ", ".$table.".*".", ".EVENT_SCHEDULE.".* ";
		}
	}	
	
	if ($gd_session->get('all_near_me')) {
		$DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
		$mylat = $gd_session->get('user_lat');
		$mylon = $gd_session->get('user_lon');
		
		$fields .= ", (" . $DistanceRadius . " * 2 * ASIN(SQRT(POWER(SIN((ABS($mylat) - ABS(" . $table . ".post_latitude)) * PI() / 180 / 2), 2) + COS(ABS($mylat) * PI() / 180) * COS(ABS(" . $table . ".post_latitude) * PI()/180) * POWER(SIN(($mylon - " . $table . ".post_longitude) * PI() / 180 / 2), 2) ))) AS distance ";
	}

	return $fields;
}

function geodir_event_date_calendar_join($join) {
	global $wpdb, $query, $geodir_post_type, $table, $table_prefix, $plugin_prefix, $gdevents_widget, $gd_session;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $join;
	}
	
	$schedule_table = EVENT_SCHEDULE;
	if (((is_main_query() && (geodir_is_page('listing') || ( is_search() && isset($_REQUEST['geodir_search'])))) || get_query_var('geodir_event_date_calendar') || isset($_REQUEST['geodir_dashbord'])) || get_query_var('geodir_event_listing_filter')) {
		if ( (!geodir_is_geodir_page() && $gdevents_widget) || get_query_var('geodir_event_date_calendar')) {
			$geodir_post_type = 'gd_event';
			$table = $plugin_prefix . $geodir_post_type . '_detail';
			$join .= " INNER JOIN ".$table." ON (".$table.".post_id = $wpdb->posts.ID) ";
		}
		$join .= " INNER JOIN ".$schedule_table." ON (".$schedule_table.".event_id = $wpdb->posts.ID) ";
	}

    if ($gd_session->get('all_near_me')){
        $detail_table = EVENT_DETAIL_TABLE;
        $join .= " INNER JOIN " . $detail_table . " ON (" . $detail_table . ".post_id = $wpdb->posts.ID) ";
    }

	return $join;
}

function geodir_event_posts_order_by_sort($orderby, $sort_by, $table){
	global $query, $geodir_post_type,$wpdb;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $orderby;
	}
	
	if (((is_main_query() && (geodir_is_page('listing') || ( is_search() && isset($_REQUEST['geodir_search']))) || get_query_var('geodir_event_date_calendar') || isset($_REQUEST['geodir_dashbord']))) || get_query_var('geodir_event_listing_filter')){
		$order = 'asc';
		if (is_main_query() && ((!empty($_REQUEST['etype']) && $_REQUEST['etype'] == 'past') || (empty($_REQUEST['etype']) && get_option('geodir_event_defalt_filter') == 'past'))) {
			$order = 'desc';
		}
		$orderby .= " ".$wpdb->prefix."geodir_event_schedule.event_date " . $order . ",  ".$wpdb->prefix."geodir_event_schedule.event_starttime " . $order . " , ";
		
	}
	
	return $orderby;
}

function geodir_event_posts_order_by_sort_distance($orderby){
	global $query, $geodir_post_type, $wpdb, $gd_session;
	
	if ( !empty( $geodir_post_type ) && $geodir_post_type != 'gd_event' ) {
		return $orderby;
	}
	
	if (get_query_var('geodir_event_listing_filter') && get_query_var('order_by')) {
		global $gd_query_args, $query_vars, $wp_query;
		
		$gd_query_args = isset($wp_query->query) ? $wp_query->query : array();
		
		$orderby = geodir_event_widget_events_get_order( array( 'order_by' => get_query_var('order_by') ) );
		$orderby .= " " . EVENT_DETAIL_TABLE . ".is_featured";
	}
	return $orderby;
	
	if (((is_main_query() && (geodir_is_page('listing') || ( is_search() && isset($_REQUEST['geodir_search']))) || get_query_var('geodir_event_date_calendar') || isset($_REQUEST['geodir_dashbord']))) || get_query_var('geodir_event_listing_filter')) {
		if ($gd_session->get('all_near_me')) {
			$orderby =	" distance, " . $orderby;
		}
	}
	
	return $orderby;
}

/**
 * Filter the GROUP BY clause of the event listings query.
 *
 * @since 1.0.0
 * @since 1.2.4 Added global $gdevents_widget.
 *
 * @global object $wp_query WordPress Query object.
 * @global WP_Query $query The WP_Query instance.
 * @global object $wpdb WordPress Database object.
 * @param string $geodir_post_type The post type.
 * @param string $table The table name. Ex: geodir_countries.
 * @param string $condition_date The parameter for date filter.
 * @param bool $gdevents_widget True if event widget, otherwise false.
 *
 * @param string   $groupby The GROUP BY clause of the query.
 * @param WP_Query $q   The WP_Query instance.
 * @return The GROUP BY clause.
*/
function geodir_event_loop_filter_groupby( $groupby, $q ) {
	global $wp_query, $query, $wpdb, $geodir_post_type, $table, $condition_date, $gdevents_widget;
	
	if ($geodir_post_type == 'gd_event' && ((is_main_query() && geodir_is_page('listing') && (isset($q->query['gd_is_geodir_page']) && $q->query['gd_is_geodir_page'])) || $gdevents_widget)) {
		$groupby = " $wpdb->posts.ID," . EVENT_SCHEDULE . ".event_date";
	} else if (get_query_var('geodir_event_date_calendar')){
		$groupby = ' event_id';
	}
	
	return $groupby;
}

function geodir_event_loop_filter($query){

	global $wp_query;

	if ( is_admin() && ( !defined('DOING_AJAX' ) || ( defined('DOING_AJAX') && !DOING_AJAX ) ) ) {
		return $query;
	}

    // function geodir_get_current_posttype wont work right here becasue wp_query is not set yet.
    $geodir_post_type = (isset($query->query_vars['post_type'])) ? $query->query_vars['post_type'] : geodir_get_current_posttype();
    $post_types = geodir_get_posttypes();


	if ( in_array($geodir_post_type, $post_types) && isset($query->query_vars['is_geodir_loop']) && $query->query_vars['is_geodir_loop'] && ($geodir_post_type=='gd_event' || get_query_var('geodir_event_date_calendar') || get_query_var('geodir_event_listing_filter'))) {

			add_filter('posts_fields', 'geodir_event_date_calendar_fields' ,1 );
			add_filter('posts_join', 'geodir_event_date_calendar_join',1);
			add_filter('geodir_posts_order_by_sort', 'geodir_event_posts_order_by_sort', 2, 3);
			add_filter('posts_where', 'geodir_event_loop_filter_where', 2);
			add_filter('posts_groupby', 'geodir_event_loop_filter_groupby',10,2 );
			add_filter('posts_orderby', 'geodir_event_posts_order_by_sort_distance', 10 );
	}else{
        remove_filter('posts_fields', 'geodir_event_date_calendar_fields' ,1 );
        remove_filter('posts_join', 'geodir_event_date_calendar_join',1);
        remove_filter('geodir_posts_order_by_sort', 'geodir_event_posts_order_by_sort', 2, 3);
        remove_filter('posts_where', 'geodir_event_loop_filter_where', 2);
        remove_filter('posts_groupby', 'geodir_event_loop_filter_groupby',10,2 );
        remove_filter('posts_orderby', 'geodir_event_posts_order_by_sort_distance', 10 );
    }
	
	return $query;
}

function geodir_event_cat_post_count_join($join,$post_type){
	global $plugin_prefix;
	if($post_type == 'gd_event')
	{
		$join .= ", ".$plugin_prefix."event_schedule sch ";
	}
	
	return $join;
}

add_filter('geodir_cat_post_count_join', 'geodir_event_cat_post_count_join', 1, 2);

function geodir_event_cat_post_count_where( $where, $post_type ) {
	global $plugin_prefix;
	
	$current_date = date_i18n( 'Y-m-d' );
	
	if ( $post_type == 'gd_event' ) {
		$table_name = $plugin_prefix . $post_type . '_detail';
		$where .= " AND " . $table_name . ".post_id=sch.event_id AND ( sch.event_date >= '" . $current_date . "' OR ( sch.event_date <= '" . $current_date . "' AND sch.event_enddate >= '" . $current_date . "' ) ) ";
	}
	
	return $where;
}

add_filter('geodir_cat_post_count_where', 'geodir_event_cat_post_count_where',1 ,2);

function geodir_event_fill_listings( $term ) {
	//$listings = geodir_event_get_my_listings( 'gd_place', $term );
	$listings = geodir_event_get_my_listings( 'all', $term );
	$options = '<option value="">' . __( 'No Business', 'geodirevents' ) . '</option>';
	if( !empty( $listings ) ) {
		foreach( $listings as $listing ) {
			$options .= '<option value="' . $listing->ID . '">' . $listing->post_title . '</option>';
		}
	}
	return $options;
}

function geodir_event_manager_ajax(){

	$task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : '';
	switch( $task ) {
		case 'geodir_fill_listings' :
			$term = isset( $_REQUEST['term'] ) ? $_REQUEST['term'] : '';
			echo geodir_event_fill_listings( $term );
			exit;
		break;
	}
	
	if(isset($_REQUEST['event_type']) && $_REQUEST['event_type'] == 'calendar'){
		geodir_event_display_calendar(); exit;
	}

	if(isset($_REQUEST['gd_event_general_settings'])){
		geodir_update_options( geodir_event_general_setting_options() );
		
		$msg = 'Your settings have been saved.';
		
		$msg = urlencode($msg);
		
			$location = admin_url()."admin.php?page=geodirectory&tab=gd_event_fields_settings&subtab=gd_event_general_options&event_success=".$msg;
		wp_redirect($location);
		exit;
		
	}
	
	if(isset($_REQUEST['auto_fill']) && $_REQUEST['auto_fill'] == 'geodir_business_autofill'){
		
		if(isset($_REQUEST['place_id']) && $_REQUEST['place_id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_link_business_autofill_nonce' ) )
						exit;
			
			geodir_business_auto_fill($_REQUEST);
			exit;
			
		}else{
		
			wp_redirect(geodir_login_url());
			exit();
		}
		
	}
	
}

function geodir_business_auto_fill($request){

	if(!empty($request)){
		
		$place_id = $request['place_id'];
		$post_type = get_post_type( $place_id );
		$package_id = geodir_get_post_meta($place_id,'package_id',true);
		$custom_fields = geodir_post_custom_fields($package_id,'all',$post_type); 
		
		$json_array = array();
		
		$content_post = get_post($place_id);
		$content = $content_post->post_content;

		$excluded = apply_filters('geodir_business_auto_fill_excluded', array());

		$post_title_value = geodir_get_post_meta($place_id,'post_title',true);
		if (in_array('post_title', $excluded)) {
			$post_title_value = '';
		}

		$post_desc_value = $content;
		if (in_array('post_desc', $excluded)) {
			$post_desc_value = '';
		}

		$json_array['post_title'] = array('key' => 'text', 'value' => $post_title_value);

		$json_array['post_desc'] = array(	'key' => 'textarea', 'value' => $post_desc_value);

		foreach($custom_fields as $key=>$val){
			
			$type = $val['type'];
			
			switch($type){
			
				case 'phone':
				case 'email':
				case 'text':
				case 'url':					
					$value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
					$json_array[$val['htmlvar_name']] = array('key' => 'text', 'value' => $value);
					
				break;
				
				case 'html':
				case 'textarea':
					
					$value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
					$json_array[$val['htmlvar_name']] = array('key' => 'textarea', 'value' => $value);
					
				break;
				
				case 'address':
					
					$json_array['post_address'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_address',true));
					$json_array['post_zip'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_zip',true));
					$json_array['post_latitude'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_latitude',true));
					$json_array['post_longitude'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_longitude',true));
					$extra_fields = unserialize($val['extra_fields']);
					
					$show_city = isset($extra_fields['show_city']) ? $extra_fields['show_city'] : '';
					
					if($show_city){

						$json_array['post_country'] = array('key' => 'text',
																				'value' => geodir_get_post_meta($place_id,'post_country',true));
						$json_array['post_region'] = array('key' => 'text',
																				'value' => geodir_get_post_meta($place_id,'post_region',true));
						$json_array['post_city'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'post_city',true));
						
					}
					
					
				break;
				case 'checkbox':
				case 'radio':
				case 'select':
				case 'datepicker':
				case 'time':
					$value = geodir_get_post_meta( $place_id, $val['htmlvar_name'], true );
					$json_array[$val['htmlvar_name']] = array( 'key' => $type, 'value' => $value );
				break;
				case 'multiselect':
					$value = geodir_get_post_meta( $place_id, $val['htmlvar_name'] );
					$value = $value != '' ? explode( ",", $value ) : array();
					$json_array[$val['htmlvar_name']] = array( 'key' => $type, 'value' => $value );
				break;
				
			}
			
		}

	}
	
	if ( !empty( $json_array ) ) {
		// attach terms
		$post_tags = wp_get_post_terms( $place_id, $post_type . '_tags', array( "fields" => "names" ) );
		$post_tags = !empty( $post_tags ) && is_array( $post_tags ) ? implode( ",", $post_tags ) : '';
		$json_array['post_tags'] = array( 'key' => 'tags', 'value' => $post_tags );
		
		echo json_encode( $json_array );
	}	
}

function geodir_wp_default_date_time_format()
{
	return get_option('date_format'). ' ' .	get_option('time_format');
}

function geodir_get_cal_trans_array()
{

	$cal_trans = array('month_long_1' => __( 'January','geodirevents' ),
'month_long_2' => __( 'February','geodirevents' ),
'month_long_3' => __( 'March','geodirevents' ),
'month_long_4' => __( 'April','geodirevents' ),
'month_long_5' => __( 'May','geodirevents' ),
'month_long_6' => __( 'June','geodirevents' ),
'month_long_7' => __( 'July','geodirevents' ),
'month_long_8' => __( 'August','geodirevents' ),
'month_long_9' => __( 'September','geodirevents' ),
'month_long_10' => __( 'October' ,'geodirevents'),
'month_long_11' => __( 'November' ,'geodirevents'),
'month_long_12' => __( 'December' ,'geodirevents'),
'month_s_1' => __( 'Jan','geodirevents' ),
'month_s_2' => __( 'Feb','geodirevents' ),
'month_s_3' => __( 'Mar','geodirevents' ),
'month_s_4' => __( 'Apr' ,'geodirevents'),
'month_s_5' => __( 'May' ,'geodirevents'),
'month_s_6' => __( 'Jun' ,'geodirevents'),
'month_s_7' => __( 'Jul','geodirevents' ),
'month_s_8' => __( 'Aug' ,'geodirevents'),
'month_s_9' => __( 'Sep' ,'geodirevents'),
'month_s_10' => __( 'Oct' ,'geodirevents'),
'month_s_11' => __( 'Nov','geodirevents' ),
'month_s_12' => __( 'Dec','geodirevents' ),
'day_s1_1' => __( 'S' ,'geodirevents'),
'day_s1_2' => __( 'M' ,'geodirevents'),
'day_s1_3' => __( 'T' ,'geodirevents'),
'day_s1_4' => __( 'W' ,'geodirevents'),
'day_s1_5' => __( 'T' ,'geodirevents'),
'day_s1_6' => __( 'F' ,'geodirevents'),
'day_s1_7' => __( 'S' ,'geodirevents'),
'day_s2_1' => __( 'Su' ,'geodirevents'),
'day_s2_2' => __( 'Mo' ,'geodirevents'),
'day_s2_3' => __( 'Tu','geodirevents' ),
'day_s2_4' => __( 'We','geodirevents' ),
'day_s2_5' => __( 'Th','geodirevents' ),
'day_s2_6' => __( 'Fr' ,'geodirevents'),
'day_s2_7' => __( 'Sa' ,'geodirevents'),
'day_s3_1' => __( 'Sun','geodirevents' ),
'day_s3_2' => __( 'Mon' ,'geodirevents'),
'day_s3_3' => __( 'Tue' ,'geodirevents'),
'day_s3_4' => __( 'Wed','geodirevents' ),
'day_s3_5' => __( 'Thu' ,'geodirevents'),
'day_s3_6' => __( 'Fri' ,'geodirevents'),
'day_s3_7' => __( 'Sat' ,'geodirevents'),
'day_s5_1' => __( 'Sunday','geodirevents' ),
'day_s5_2' => __( 'Monday' ,'geodirevents'),
'day_s5_3' => __( 'Tuesday','geodirevents' ),
'day_s5_4' => __( 'Wednesday','geodirevents' ),
'day_s5_5' => __( 'Thursday','geodirevents' ),
'day_s5_6' => __( 'Friday' ,'geodirevents'),
'day_s5_7' => __( 'Saturday' ,'geodirevents'),

's_previousMonth' => __( 'Previous Month' ),
's_nextMonth' => __( 'Next Month' ),
's_close' => __( 'Close' ));

return $cal_trans;
}

function geodir_event_link_businesses( $post_id, $post_type, $arr = false ) {
	global $wpdb, $plugin_prefix;
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$sql = $wpdb->prepare(
		"SELECT post_id FROM " . $table . " WHERE post_status=%s AND geodir_link_business=%d", array( 'publish', $post_id )
	);
	
	$rows = $wpdb->get_results($sql);
	
	$result = array();
	if ( !empty( $rows ) ) {
		foreach ($rows as $row) {
			$result[] = $row->post_id;
		}
	}
		
	return $result;
}

function geodir_event_link_businesses_data( $post_ids, $event_type = 'all', $list_sort = 'latest', $post_number = 5 ) {
	global $wpdb, $plugin_prefix;
	
	$table = $plugin_prefix . 'gd_event_detail';
	if ( $post_ids == '' || ( is_array( $post_ids ) && empty( $post_ids ) ) ) {
		return NULL;
	}
	$post_ids = is_array( $post_ids ) ? implode( "','", $post_ids ) : '';
	
	$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	$limit = $post_number < 1 || $post_number > 100 ? 5 : $post_number;
	
	$orderby = geodir_event_widget_events_get_order( array( 'order_by' => $list_sort ) );
	
	if ($list_sort == 'upcoming') {
		$orderby = '';
	} 
	
	$where = '';
	switch( $event_type ) {
		case 'today':
			$where .= " AND ( " . EVENT_SCHEDULE . ".event_date LIKE '" . $current_date . "%%' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
		break;
		case 'upcoming':
			$where .= " AND ( " . EVENT_SCHEDULE . ".event_date >= '" . $current_date . "' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
		break;
		case 'past':
			$where .= " AND " . EVENT_SCHEDULE . ".event_date < '" . $current_date . "' ";
		break;
	}

	$sql =  $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS " . $wpdb->posts . ".*, " . $table . ".*, " . EVENT_SCHEDULE . ".*
		FROM " . $wpdb->posts . "
		INNER JOIN " . $table ." ON (" . $table . ".post_id = " . $wpdb->posts . ".ID)
		INNER JOIN " . EVENT_SCHEDULE . " AS " . EVENT_SCHEDULE . " ON (" . EVENT_SCHEDULE . ".event_id = " . $wpdb->posts . ".ID)
		WHERE " . $wpdb->posts . ".ID IN ('" . $post_ids . "')
			AND " . $wpdb->posts . ".post_type = 'gd_event'
			AND " . $wpdb->posts . ".post_status = 'publish'
			" . $where . "
		ORDER BY " . $orderby . " (CASE WHEN DATEDIFF(DATE(" . EVENT_SCHEDULE . ".event_date), '" . $current_date . "') < 0 THEN 1 ELSE 0 END), ABS(DATEDIFF(DATE(" . EVENT_SCHEDULE . ".event_date), '" . $current_date . "')) ASC, " . EVENT_SCHEDULE . ".event_starttime ASC, " . $table . ".is_featured ASC, " . $wpdb->posts . ".post_title ASC
		LIMIT %d", array( $limit) );

	$rows = $wpdb->get_results($sql);
	
	return $rows;
}

function geodir_event_display_link_business() {
	global $post;
	$post_type = geodir_get_current_posttype();
	$all_postypes = geodir_get_posttypes();
		
	if ( !empty( $post ) && $post_type == 'gd_event' && geodir_is_page( 'detail' ) && isset( $post->geodir_link_business ) && !empty( $post->geodir_link_business ) ) {
		$linked_post_id = $post->geodir_link_business;
		$linked_post_info = get_post($linked_post_id);
		if( !empty( $linked_post_info ) ) {
			$linked_post_type_info = in_array( $linked_post_info->post_type, $all_postypes ) ? geodir_get_posttype_info( $linked_post_info->post_type )  : array();
			if( !empty( $linked_post_type_info ) ) {
				$linked_post_title = !empty( $linked_post_info->post_title ) ? $linked_post_info->post_title : __( 'Listing', 'geodirevents' );
				$linked_post_url = get_permalink($linked_post_id);
				
				$html_link_business = '<div class="geodir_more_info geodir_more_info_even geodir_link_business"><span class="geodir-i-website"><i class="fa fa-link"></i> <a title="' . esc_attr( $linked_post_title ) . '" href="'.$linked_post_url.'">' . wp_sprintf( __( 'Go to: %s', 'geodirevents' ), $linked_post_title ) . '</a></span></div>';
				
				echo apply_filters( 'geodir_more_info_link_business', $html_link_business, $linked_post_id, $linked_post_url );
			}
		}
	}
}

function geodir_event_get_my_listings( $post_type = 'all', $search = '', $limit = 5 ) {
	global $wpdb, $current_user;
	
	if( empty( $current_user->ID ) ) {
		return NULL;
	} 
	$geodir_postypes = geodir_get_posttypes();

	$search = trim( $search );
	$post_type = $post_type != '' ? $post_type : 'all';
	
	if( $post_type == 'all' ) {
		$geodir_postypes = implode( ",", $geodir_postypes );
		$condition = $wpdb->prepare( " AND FIND_IN_SET( post_type, %s )" , array( $geodir_postypes ) );
	} else {
		$post_type = in_array( $post_type, $geodir_postypes ) ? $post_type : 'gd_place';
		$condition = $wpdb->prepare( " AND post_type = %s" , array( $post_type ) );
	}


	if(!get_option('geodir_event_link_any')){
		$condition .= !current_user_can( 'manage_options' ) ? $wpdb->prepare( "AND post_author=%d" , array( (int)$current_user->ID ) ) : '';
	}
	$condition .= $search != '' ? $wpdb->prepare( " AND post_title LIKE %s", array( $search . '%%' ) ) : "";
	
	$orderby = " ORDER BY post_title ASC";
	$limit = " LIMIT " . (int)$limit;
	
	$sql = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = %s AND post_type != 'gd_event' " . $condition . $orderby . $limit, array( 'publish' ) );
	$rows = $wpdb->get_results($sql);
	
	return $rows;
}

add_filter('geodir_filter_widget_listings_fields','geodir_filter_event_widget_listings_fields',10,3);
function geodir_filter_event_widget_listings_fields($fields,$table,$post_type){
	global $plugin_prefix;
	if($post_type=='gd_event'){
	$fields .= ", ".EVENT_SCHEDULE.".* ";	
	}
	return $fields;
}

add_filter('geodir_filter_widget_listings_join','geodir_filter_event_widget_listings_join',10,2);
function geodir_filter_event_widget_listings_join($join,$post_type){
	global $plugin_prefix,$wpdb;
	if($post_type=='gd_event'){
	$join .= " INNER JOIN ".EVENT_SCHEDULE." ON (".EVENT_SCHEDULE.".event_id = $wpdb->posts.ID) ";	
	}
	return $join;
}

add_filter( 'geodir_filter_widget_listings_where', 'geodir_filter_event_widget_listings_where', 10, 2 );
function geodir_filter_event_widget_listings_where( $where, $post_type ) {
	global $plugin_prefix, $wpdb, $gd_query_args_widgets;
	if ( $post_type == 'gd_event' ) {
		$current_date = date_i18n('Y-m-d');
		
		if (!empty($gd_query_args_widgets) && isset($gd_query_args_widgets['geodir_event_listing_filter'])) {
			$filter = $gd_query_args_widgets['geodir_event_listing_filter'];
			if ( $filter == 'today' ) {
				$where .= " AND ( " . EVENT_SCHEDULE . ".event_date = '" . $current_date . "' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
			}
				
			if ( $filter == 'upcoming' ) {
				$where .= " AND ( " . EVENT_SCHEDULE . ".event_date >= '" . $current_date . "' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
			}
			
			if ( $filter == 'past' ) {
				$where .= " AND " . EVENT_SCHEDULE . ".event_date < '" . $current_date . "' ";
			}
		} else {
			$where .= " AND ( " . EVENT_SCHEDULE . ".event_date >= '" . $current_date . "' OR ( " . EVENT_SCHEDULE . ".event_date <= '" . $current_date . "' AND " . EVENT_SCHEDULE . ".event_enddate >= '" . $current_date . "' ) ) ";
		}
	}
	return $where;
}

add_filter('geodir_filter_widget_listings_orderby','geodir_filter_event_widget_listings_orderby', 10, 3);
function geodir_filter_event_widget_listings_orderby($orderby,$table,$post_type) {
	global $plugin_prefix, $wpdb, $gd_query_args_widgets;
	
	if ($post_type == 'gd_event') {
		if (!empty($gd_query_args_widgets) && isset($gd_query_args_widgets['order_by'])) {
			if ($gd_query_args_widgets['order_by'] == 'upcoming') {
				$orderby = " ".EVENT_SCHEDULE.".event_date asc,".EVENT_SCHEDULE.".event_starttime asc , ".EVENT_DETAIL_TABLE.".is_featured asc, $wpdb->posts.post_date desc, " . $orderby . " ";
			} else {
				if ($gd_query_args_widgets['order_by'] == 'featured') {
					$orderby = EVENT_DETAIL_TABLE . ".is_featured ASC, ";
				}
				$orderby .= " ".EVENT_SCHEDULE.".event_date asc,".EVENT_SCHEDULE.".event_starttime asc , ".EVENT_DETAIL_TABLE.".is_featured asc, $wpdb->posts.post_date desc, ";
			}
		} else {
			$orderby = " ".EVENT_SCHEDULE.".event_date asc,".EVENT_SCHEDULE.".event_starttime asc , ".EVENT_DETAIL_TABLE.".is_featured asc, $wpdb->posts.post_date desc, ";
		}
	}
	return $orderby;
}

function geodir_event_postview_output($args='', $instance='') {
	global $gd_session;
	// prints the widget
	extract($args, EXTR_SKIP);

	echo $before_widget;

	global $gdevents_widget;
	$gdevents_widget = true;

	$title = empty($instance['title']) ? geodir_ucwords($instance['category_title']) : apply_filters('widget_title', __($instance['title'],'geodirevents'));

	$post_type = 'gd_event';

	$category = empty($instance['category']) ? '0' : apply_filters('widget_category', $instance['category']);

	$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);

	$layout = empty($instance['layout']) ? 'gridview_onehalf' : apply_filters('widget_layout', $instance['layout']);

	$add_location_filter = empty($instance['add_location_filter']) ? '0' : apply_filters('widget_layout', $instance['add_location_filter']);

	$listing_width = empty($instance['listing_width']) ? '' : apply_filters('widget_layout', $instance['listing_width']);

	$list_sort = empty($instance['list_sort']) ? 'latest' : apply_filters('widget_list_sort', $instance['list_sort']);

	$list_filter = empty($instance['list_filter']) ? 'all' : apply_filters('widget_list_filter', $instance['list_filter']);

	if(isset($instance['character_count'])){$character_count = apply_filters('widget_list_character_count', $instance['character_count']);}
	else{$character_count ='';}
	
	$category = is_array($category) ? $category : explode(",", $category);

	if(empty($title) || $title == 'All' ){
		$title .= ' '.get_post_type_plural_label($post_type);
	}

	$location_url = '';

	$location_url = array();
	$city = get_query_var('gd_city');
	if( !empty($city) ){

		if(get_option('geodir_show_location_url') == 'all'){
			$country = get_query_var('gd_country');
			$region = get_query_var('gd_region');
			if(!empty($country))
				$location_url[] = $country;

			if(!empty($region))
				$location_url[] = $region;
		}
		$location_url[] = $city;
	}

	$location_allowed = function_exists( 'geodir_cpt_no_location' ) && geodir_cpt_no_location( $post_type ) ? false : true;
	$location_url = implode("/",$location_url);
	
	$skip_location = false;
	if (!$add_location_filter && $gd_session->get('gd_multi_location')) {
		$skip_location = true;
		$gd_session->un_set('gd_multi_location');
	}

	if ( $location_allowed && $add_location_filter && $gd_session->get( 'all_near_me' ) && geodir_is_page( 'location' ) ) {
		$viewall_url = add_query_arg( array( 
			'geodir_search' => 1, 
			'stype' => $post_type,
			's' => '',
			'snear' => __( 'Near:', 'geodiradvancesearch' ) . ' ' . __( 'Me', 'geodiradvancesearch' ),
			'sgeo_lat' => $gd_session->get( 'user_lat' ),
			'sgeo_lon' => $gd_session->get( 'user_lon' ),
			'etype' => $list_filter,
		), geodir_search_page_base_url() );

		if ( ! empty( $category ) && !in_array( '0', $category ) ) {
			$viewall_url = add_query_arg( array( 's' . $post_type . 'category' => $category ), $viewall_url );
		}
	} else {
		if ( get_option('permalink_structure') )
			$viewall_url = get_post_type_archive_link($post_type);
		else
			$viewall_url = get_post_type_archive_link($post_type);

		if(!empty($category) && $category[0] != '0'){
			global $geodir_add_location_url;
			$geodir_add_location_url = '0';
			if($add_location_filter != '0'){
				$geodir_add_location_url = '1';
			}
			$viewall_url = get_term_link( (int)$category[0], $post_type.'category');
			$geodir_add_location_url = NULL;
		}
	}

	if ($skip_location) {
		$gd_session->set('gd_multi_location', 1);
	}

	if ( is_wp_error( $viewall_url ) ) {
		return;
	}
	?>
	<div class="geodir_locations geodir_location_listing">
		<?php do_action('geodir_before_view_all_link_in_widget') ; ?>
		<div class="geodir_list_heading clearfix">
			<?php echo $before_title.$title.$after_title;?>
			<a href="<?php echo $viewall_url;?>" class="geodir-viewall">
				<?php _e('View all','geodirevents');?>
			</a>
		</div>
		<?php do_action('geodir_after_view_all_link_in_widget') ; ?>
		<?php
		$query_args = array(
			'posts_per_page' => $post_number,
			'is_geodir_loop' => true,
			'gd_location' 	 => ($add_location_filter) ? true : false,
			'post_type' => $post_type,
			'geodir_event_listing_filter' => $list_filter,
			'order_by' =>$list_sort,
			'excerpt_length' => $character_count,
		);
		
		if (!empty($instance['show_featured_only'])) {
			$query_args['show_featured_only'] = 1;
		}

		if (!empty($instance['show_special_only'])) {
			$query_args['show_special_only'] = 1;
		}

		if (!empty($instance['with_pics_only'])) {
			$query_args['with_pics_only'] = 0;
			$query_args['featured_image_only'] = 1;
		}

		if (!empty($instance['with_videos_only'])) {
			$query_args['with_videos_only'] = 1;
		}

		if(!empty($category) && $category[0] != '0'){

			$category_taxonomy = geodir_get_taxonomies($post_type);

			######### WPML #########
			if(geodir_wpml_is_taxonomy_translated($category_taxonomy[0])) {
				$category = gd_lang_object_ids($category, $category_taxonomy[0]);
			}
			######### WPML #########

			$tax_query = array( 'taxonomy' => $category_taxonomy[0],
				'field' => 'id',
				'terms' => $category);

			$query_args['tax_query'] = array( $tax_query );
		}

        global $gridview_columns_widget, $geodir_is_widget_listing, $geodir_event_widget_listview;

        $widget_listings = geodir_get_widget_listings($query_args);

        if (strstr($layout, 'gridview')) {
            $listing_view_exp = explode('_', $layout);
            $gridview_columns_widget = $layout;
            $layout = $listing_view_exp[0];
        } else {
            $gridview_columns_widget = '';
        }

        $template = apply_filters( "geodir_template_part-listing-listview", geodir_plugin_path() . '/geodirectory-templates/widget-listing-listview.php' );
        //$template = apply_filters( "geodir_template_part-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
        //$template = apply_filters( "geodir_event_template_widget_listview", WP_PLUGIN_DIR . '/geodir_event_manager/gdevents_widget_listview.php' );
		global $post, $map_jason, $map_canvas_arr;

		$current_post = $post;
		$current_map_jason = $map_jason;
		$current_map_canvas_arr = $map_canvas_arr;
		$geodir_is_widget_listing = true;
		$geodir_event_widget_listview = true;

		include( $template );
	?>
	</div>
	<?php
	wp_reset_query();
	$geodir_is_widget_listing = false;
	$geodir_event_widget_listview = false;

	$GLOBALS['post'] = $current_post;
	if (!empty($current_post))
		setup_postdata($current_post);
	$map_jason = $current_map_jason;
	$map_canvas_arr = $current_map_canvas_arr;
		
	$gdevents_widget = NULL;
	unset( $gdevents_widget );
	echo $after_widget;
}

function geodir_event_calendar_widget_output($args = '', $instance = '') {
	global $post, $gd_session;
	$id_base = !empty($args['widget_id']) ? $args['widget_id'] : 'geodir_event_listing_calendar';
	
	$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $id_base);
	$day = apply_filters('widget_day', empty($instance['day']) ? '' : $instance['day'], $instance, $id_base);
	$week_day_format = apply_filters('widget_week_day_format', empty($instance['week_day_format']) ? 0 : $instance['week_day_format'], $instance, $id_base);
	$add_location_filter = apply_filters('geodir_event_calendar_widget_add_location_filter', empty($instance['add_location_filter']) ? 0 : 1, $instance, $id_base);
	
	add_action('wp_enqueue_scripts', 'geodir_event_calenders_script');
	$identifier = sanitize_html_class($id_base);
	echo $args['before_widget'];
	$function_name = 'geodir_event_call_calendar_' . rand(100, 999);
	
	// Set location for detail page
	$location_id = 0;
	$location_title = '';
	$backup = array();
	$location_params = '';
	if ($add_location_filter) {
		if (isset($_REQUEST['snear'])) {
			$location_params .= '&snear=' . sanitize_text_field(stripslashes($_REQUEST['snear']));
		}
		if (!empty($_REQUEST['sgeo_lat']) && !empty($_REQUEST['sgeo_lon'])) {
			$location_params .= '&my_lat=' . sanitize_text_field($_REQUEST['sgeo_lat']);
			$location_params .= '&my_lon=' . sanitize_text_field($_REQUEST['sgeo_lon']);
		}
		if (geodir_is_page('detail') && !empty($post) && !empty($post->post_location_id)) {
			$location_id = $post->post_location_id;
		}
		$location_id = apply_filters('geodir_event_calendar_widget_location_id', $location_id, $instance, $id_base);
		if ($location_id && function_exists('geodir_get_location_by_id') && $location = geodir_get_location_by_id('', $location_id)) {
			$backup['all_near_me'] = $gd_session->get('all_near_me');
			$backup['gd_multi_location'] = $gd_session->get('gd_multi_location');
			$backup['gd_country'] = $gd_session->get('gd_country');
			$backup['gd_region'] = $gd_session->get('gd_region');
			$backup['gd_city'] = $gd_session->get('gd_city');
			
			$gd_session->set('all_near_me', false);
			$gd_session->set('gd_multi_location', 1);
			$gd_session->set('gd_country', $location->country_slug);
		 	$gd_session->set('gd_region', $location->region_slug);
			$gd_session->set('gd_city', $location->city_slug);
		}
		if (function_exists('geodir_current_loc_shortcode')) {
			$location_title = geodir_current_loc_shortcode();
		}
	}
	if ($title && strpos($title, '%%location_name%%') !== false) {
		$title = str_replace('%%location_name%%', $location_title, $title);
	}
	?>
	<div class="geodir_event_cal_widget" id="gdwgt_<?php echo $identifier; ?>">
		<?php if (trim($title) != '') { ?>
		<div class="geodir_event_cal_widget_title clearfix"><?php echo $args['before_title'] . __($title, 'geodirevents') . $args['after_title'];?></div>
		<?php } ?>
		<table style="width:100%" class="gd_cal_nav">
			<tr align="center" class="title">
				<td style="width:10%" class="title"><img class="geodir_cal_prev" src="<?php echo plugins_url('gdevents-assets/images/previous2.png', __FILE__); ?>" alt="<?php esc_attr_e('prev', 'geodirevents');?>" /></td>
				<td style="vertical-align:top;text-align:center" class="title"><i class="fa fa-refresh fa-spin gdem-loading"></i></td>
				<td style="width:10%" class="title"><img class="geodir_cal_next" src="<?php echo plugins_url('gdevents-assets/images/next2.png', __FILE__); ?>" alt="<?php esc_attr_e('next', 'geodirevents');?>" /></td>
			</tr>
		</table>
		<div class="geodir_event_calendar"></div>
	</div>
<script type="text/javascript">
if (typeof <?php echo $function_name; ?> !== 'function') {
	window.<?php echo $function_name; ?> = function() {
		var $container = jQuery('#gdwgt_<?php echo $identifier;?>');
		var sday = '<?php echo $day;?>';
		var wday = '<?php echo (int)$week_day_format;?>';
		var gdem_ajaxurl = '<?php echo geodir_event_manager_ajaxurl();?>';
		var gdem_loading = jQuery('.gd_cal_nav .gdem-loading', $container);
		var loc = '&_loc=<?php echo (int)$add_location_filter;?>&_l=<?php echo (int)$location_id;?><?php echo $location_params;?>';
		var myurl = gdem_ajaxurl + "&event_type=calendar" + "&sday=" + sday + "&wday=" + wday + loc;
		jQuery.ajax({
			type: "GET",
			url: myurl,
			success: function(msg) {
				gdem_loading.hide();
				jQuery(".geodir_event_calendar", $container).html(msg);
			}
		});
		
		var mnth = <?php echo date_i18n("n");?>;
		var year = <?php echo date_i18n("Y");?>;
		
		jQuery(".geodir_cal_next", $container).click(function() {
			gdem_loading.show();
			mnth++;
			if (mnth > 12) {
				year++;
				mnth = 1;
			}
			
			var nexturl = gdem_ajaxurl + "&event_type=calendar&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc;
			jQuery.ajax({
				type: "GET",
				url: nexturl,
				success: function(next) {
					gdem_loading.hide();
					jQuery(".geodir_event_calendar", $container).html(next);
				}
			});
		});
		
		jQuery(".geodir_cal_prev", $container).click(function() {
			gdem_loading.show();
			mnth--;
			if (mnth < 1) {
				year--;
				mnth = 12;
			}
			
			var prevurl = gdem_ajaxurl + "&event_type=calendar&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc;
			jQuery.ajax({
				type: "GET",
				url: prevurl,
				success: function(prev) {
					gdem_loading.hide();
					jQuery(".geodir_event_calendar", $container).html(prev);
				}
			});
		});
	};
}

jQuery(document).ready(function() {
	if (typeof <?php echo $function_name; ?> == 'function') {
		<?php echo $function_name; ?>();
	}
});
</script>
	<?php
	echo $args['after_widget'];
	if (!empty($backup)) {
		foreach ($backup as $key => $value) {
			if ($value !== false) {
				$gd_session->set($key, $value);
			} else {
				$gd_session->un_set($key);
			}
		}
	}
}

/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 */
function geodir_event_date_format_php_to_jqueryui( $php_format ) {
	$symbols = array(
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
		// Time
		'a' => 'tt',
		'A' => 'TT',
		'B' => '',
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => '',
		'u' => ''
	);

	$jqueryui_format = "";
	$escaping = false;

	for ( $i = 0; $i < strlen( $php_format ); $i++ ) {
		$char = $php_format[$i];

		// PHP date format escaping character
		if ( $char === '\\' ) {
			$i++;

			if ( $escaping ) {
				$jqueryui_format .= $php_format[$i];
			} else {
				$jqueryui_format .= '\'' . $php_format[$i];
			}

			$escaping = true;
		} else {
			if ( $escaping ) {
				$jqueryui_format .= "'";
				$escaping = false;
			}

			if ( isset( $symbols[$char] ) ) {
				$jqueryui_format .= $symbols[$char];
			} else {
				$jqueryui_format .= $char;
			}
		}
	}

	return $jqueryui_format;
}

function geodir_event_is_date( $date ) {
	$date = trim( $date );
	
	if ( $date == '' || $date == '0000-00-00 00:00:00' || $date == '0000-00-00' ) {
		return false;
	}
	
	$year = (int)date_i18n( 'Y', strtotime( $date ) );
	
	if ( $year > 1970 ) {
		return true;
	}
	
	return false;
}
 
function geodir_event_date_occurrences( $type = 'year', $start_date, $end_date = '', $interval = 1, $limit = '', $repeat_end = '', $repeat_days = array(), $repeat_weeks = array() ) {
	$dates = array();
	$start_time = strtotime( $start_date );
	$end_time = strtotime( $repeat_end );

	switch ( $type ) {
		case 'year': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' year' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( $time_occurrence <= $end_time ) {
						$dates[] = $date_occurrence;
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					for ( $i = 1; $i < $limit ; $i++ ) {
						$every = $interval * $i;
						$time = strtotime( $start_date . '+' . $every . ' year' );
						
						$year = date_i18n( 'Y', $time );
						$month = date_i18n( 'm', $time );
						$day = date_i18n( 'd', $time );
						
						$date_occurrence = $year . '-' . $month . '-' . $day;
						
						$dates[] = $date_occurrence;
					}
				}
			}
		}
		break;
		case 'month': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' month' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( !empty( $repeat_days ) || !empty( $repeat_weeks ) ) {
						$month_days = cal_days_in_month( CAL_GREGORIAN, $month, $year );												
						for ( $d = 1; $d <= $month_days; $d++ ) {
							$recurr_time = strtotime( $year . '-' . $month . '-' . $d );
							$week_day = date_i18n( 'w', $recurr_time );
							$week_diff = ( $recurr_time - strtotime( $year . '-' . $month . '-01' ) );
							$week_num = $week_diff > 0 ? (int)( $week_diff / ( DAY_IN_SECONDS * 7 ) ) : 0;
							$week_num++;														
							
							if ( $recurr_time >= $start_time && $recurr_time <= $end_time ) {
								if ( empty( $repeat_days ) && !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								} else if ( !empty( $repeat_days ) && empty( $repeat_weeks ) && in_array( $week_day, $repeat_days ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								} else if ( !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) && !empty( $repeat_days ) && in_array( $week_day, $repeat_days ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								}
							}
						}
					} else {
						$dates[] = $date_occurrence;
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					if ( !empty( $repeat_days ) || !empty( $repeat_weeks ) ) {
						$dates = array();
						$week_dates = array();
						$days_limit = 0;
						
						$i = 0;
						while ( $days_limit <= $limit ) {
							$time = strtotime( $start_date . '+' . ( $interval * $i ) . ' month' );
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$month_days = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							for ( $d = 1; $d <= $month_days; $d++ ) {
								$recurr_time = strtotime( $year . '-' . $month . '-' . $d );
								$week_day = date_i18n( 'w', $recurr_time );
								$week_diff = ( $recurr_time - strtotime( $year . '-' . $month . '-01' ) );
								$week_num = $week_diff > 0 ? (int)( $week_diff / ( DAY_IN_SECONDS * 7 ) ) : 0;
								$week_num++;
								
								if ( $recurr_time >= $start_time && in_array( $week_day, $repeat_days ) ) {
									$week_date = '';
									
									if ( empty( $repeat_days ) && !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) ) {
										$week_date = date_i18n( 'Y-m-d', $recurr_time );
									} else if ( !empty( $repeat_days ) && empty( $repeat_weeks ) && in_array( $week_day, $repeat_days ) ) {
										$week_date = date_i18n( 'Y-m-d', $recurr_time );
									} else if ( !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) && !empty( $repeat_days ) && in_array( $week_day, $repeat_days ) ) {
										$week_date = date_i18n( 'Y-m-d', $recurr_time );
									}
									if ( $week_date != '' ) {
										$dates[] = $week_date;
										$days_limit++;
									}
									
									if ( count( $dates ) == $limit ) {
										break 2;
									}
								}
							}
							$i++;
							
						}
						
						$dates = !empty( $dates ) ? $dates : date_i18n( 'Y-m-d', $start_time );
					} else {
						for ( $i = 1; $i < $limit ; $i++ ) {
							$every = $interval * $i;
							$time = strtotime( $start_date . '+' . $every . ' month' );
							
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$date_occurrence = $year . '-' . $month . '-' . $day;
							
							$dates[] = $date_occurrence;
						}
					}
				}
			}
		}
		break;
		case 'week': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' week' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( $time_occurrence <= $end_time ) {
						if ( !empty( $repeat_days ) ) {
							for ( $d = 0; $d <= 6; $d++ ) {
								$recurr_time = strtotime( $date_occurrence . '+' . $d . ' day' );
								$week_day = date_i18n( 'w', $recurr_time );
								
								if ( in_array( $week_day, $repeat_days ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								}
							}
						} else {
							$dates[] = $date_occurrence;
						}
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					if ( !empty( $repeat_days ) ) {
						$dates = array();
						$week_dates = array();
						$days_limit = 0;
						
						$i = 0;
						while ( $days_limit <= $limit ) {
							$time = strtotime( $start_date . '+' . ( $interval * $i ) . ' week' );
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$date_occurrence = $year . '-' . $month . '-' . $day;
							
							for ( $d = 0; $d <= 6; $d++ ) {
								$recurr_time = strtotime( $date_occurrence . '+' . $d . ' day' );
								$week_day = date_i18n( 'w', $recurr_time );
								
								if ( in_array( $week_day, $repeat_days ) ) {
									$week_dates[] = date_i18n( 'Y-m-d', $recurr_time );
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
									$days_limit++;
									
									if ( count( $dates ) == $limit ) {
										break 2;
									}
								}
							}
							$i++;
							
						}
						
						$dates = !empty( $dates ) ? $dates : date_i18n( 'Y-m-d', $start_time );
					} else {
						for ( $i = 1; $i < $limit ; $i++ ) {
							$every = $interval * $i;
							$time = strtotime( $start_date . '+' . $every . ' week' );
							
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$date_occurrence = $year . '-' . $month . '-' . $day;
							
							$dates[] = $date_occurrence;
						}
					}
				}
			}
		}
		break;
		case 'day': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' day' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( $time_occurrence <= $end_time ) {
						$dates[] = $date_occurrence;
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					for ( $i = 1; $i < $limit ; $i++ ) {
						$every = $interval * $i;

						$time = strtotime( $start_date . '+' . $every . ' day' );
						
						$year = date_i18n( 'Y', $time );
						$month = date_i18n( 'm', $time );
						$day = date_i18n( 'd', $time );
						
						$date_occurrence = $year . '-' . $month . '-' . $day;
						
						$dates[] = $date_occurrence;
					}
				}
			}
		}
		break;
	}

	$dates = !empty( $dates ) ? array_unique( $dates ) : $dates;
	return $dates;
}

function geodir_event_schedule_exist( $date, $event_id ) {
	global $wpdb;
	
	$date = date_i18n( 'Y-m-d', strtotime( $date ) );
	
	$sql = "SELECT * FROM `" . EVENT_SCHEDULE . "` WHERE event_id=" . (int)$event_id . " AND ( ( event_enddate = '0000-00-00' AND DATE_FORMAT( event_date, '%Y-%m-%d') = '" . $date . "' ) OR ( event_enddate != '0000-00-00' AND DATE_FORMAT( event_date, '%Y-%m-%d') <= '" . $date . "' AND '" . $date . "' <= DATE_FORMAT( event_enddate, '%Y-%m-%d') ) )";
	
	if ( $wpdb->get_var( $sql ) ) {
		return true;
	}
	return false;
}

/**
 * Check package has recurring enabled
 */
function geodir_event_recurring_pkg( $post, $package_info = array() ) {
	$package_info = geodir_post_package_info( $package_info, $post );
	
	$recurring_pkg = true;
	
	if ( is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' ) ) {
		if ( !empty( $package_info ) && isset( $package_info->recurring_pkg ) && (int)$package_info->recurring_pkg == 1 ) {
			$recurring_pkg = false;
		};
	}
	
	if ( get_option( 'geodir_event_disable_recurring' ) ) {
		$recurring_pkg = false;
	}
	 
	return apply_filters( 'geodir_event_recurring_pkg', $recurring_pkg, $post, $package_info );
}

function geodir_event_parse_dates( $dates_input, $array = true ) {
	$dates = array();
	
	if ( !empty( $dates_input ) && $dates_input != '' ) {
		if ( !is_array( $dates_input ) ) {
			$dates_input = explode( ',', $dates_input );
		}
		
		if ( !empty( $dates_input ) ) {
			foreach ( $dates_input as $date ) {
				$date = trim( $date );
				if ( $date != '' && geodir_event_is_date( $date ) ) {
					$dates[] = $date;
				}
			}
		}
	}
	
	if ( !$array ) {
		$dates = implode( ',', $dates );
	}
	
	return $dates;
}

/**
 * Event calendar date format
 *
 */
function geodir_event_field_date_format() {
	$date_format = get_option('geodir_event_date_format_feild');
    
    if ( empty( $date_format ) ) {
        $date_format = 'F j, Y';
    }
    // if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed.
	return apply_filters( 'geodir_event_field_date_format', $date_format);
}

/**
 * Display event dates date format
 *
 */
function geodir_event_date_format() {
	$date_format = get_option('geodir_event_date_format');
    
    if ( get_option('geodir_event_date_use_custom') ) {
        $date_format = get_option('geodir_event_date_format_custom');
    }
    
    if ( empty( $date_format ) ) {
        $date_format = get_option('date_format');
    }
    
    // if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed.
	return apply_filters( 'geodir_event_date_format', $date_format );
}

/**
 * Display event dates date format
 *
 */
function geodir_event_time_format() {
	$time_format = get_option('time_format');
    
	return apply_filters( 'geodir_event_time_format', $time_format );
}

/**
 * Display event dates date time format.
 *
 */
function geodir_event_date_time_format() {
    $date_time_format = geodir_event_date_format() . ' ' . geodir_event_time_format();

    return apply_filters( 'geodir_event_date_time_format', $date_time_format );
}

/**
 * Retrive the page title for the listing page.
 *
 * @since 1.1.8
 *
 * @param  string $page_title Page title.
 * @return string Listing page title.
 */
function geodir_event_listing_page_title($page_title = '') {
	$current_posttype = geodir_get_current_posttype();
	
	if ( geodir_is_page( 'listing' ) && $current_posttype == 'gd_event' && isset( $_REQUEST['venue'] ) && $_REQUEST['venue'] != '' ) {
		$venue = explode( '-', $_REQUEST['venue'], 2);
		$venue_info = !empty($venue) && isset($venue[0]) ? get_post((int)$venue[0]) : array();
		
		if ( !empty( $venue_info ) && isset( $venue_info->post_title ) && $venue_info->post_title != '' )
			$page_title = wp_sprintf( __( '%s at %s', 'geodirevents' ), $page_title, $venue_info->post_title );
	}
		
	return $page_title;
}

/**
 * Filter the past events count in terms array results.
 *
 * @since 1.1.9
 *
 * @param array $terms Array of terms.
 * @param array $taxonomies Array of post taxonomies.
 * @param array $args Terms arguements.
 * @return array Array of terms.
 */
function geodir_event_get_terms( $terms, $taxonomies, $args ) {
	if ( isset( $args['gd_event_no_loop'] ) ) {
		return $terms; // Avoid an infinite loop.
	}
	
	$args['gd_event_no_loop'] = true;
	
	$gd_event_post_type = 'gd_event';
	
	$gd_event_taxonomy = $gd_event_post_type . 'category';
	
	if ( !empty( $terms ) && in_array( $gd_event_taxonomy, $taxonomies ) ) {
		$query_args = array (
			'is_geodir_loop' => true,
			'post_type' => $gd_event_post_type,
			'gd_location' => true,
		);
			
		$new_terms = array();
		
		foreach ( $terms as $key => $term ) {
			$new_term = $term;
			
			if ( isset( $term->taxonomy ) && $term->taxonomy == $gd_event_taxonomy ) {
				$tax_query = array(
					'taxonomy' => $gd_event_taxonomy,
					'field' => 'id',
					'terms' => $term->term_id
				);
				
				$query_args['tax_query'] = array($tax_query);
				
				$new_term->count = geodir_get_widget_listings( $query_args, true );
			}
			
			$new_terms[$key] = $new_term;
		}
		
		$terms = $new_terms;
	}

	return $terms;
}

/**
 * Add the query vars to the term link to retrive today & upcoming events.
 *
 * @since 1.1.9
 *
 * @param string $term_link The term permalink.
 * @param int    $cat->term_id The term id.
 * @param string $post_type Wordpress post type.
 * @return string The category term link.
 */
function geodir_event_category_term_link( $term_link, $term_id, $post_type ) {
	if ( $post_type != 'gd_event' ) {
		return $term_link;
	}
	
	$term_link = add_query_arg( array( 'etype' => 'upcoming' ), $term_link );

	return $term_link;
}

/**
 * Update the terms reviews count for upcoming events.
 *
 * @since 1.2.4
 */
function geodir_event_review_count_force_update() {
	$today_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	$last_update_date = get_option( 'geodir_review_count_force_update' );
	
	if ( !$last_update_date || strtotime( $last_update_date ) < strtotime( $today_date ) ) {
		// update terms reviews count
		geodir_count_reviews_by_terms(true);
		
		// update location reviews count
		if (defined('POST_LOCATION_TABLE')) {
			geodir_event_location_update_count_reviews();
		}
				
		update_option('geodir_review_count_force_update', $today_date );
	}
}

/**
 * Update the reviews count for upcoming events for current location.
 *
 * @since 1.2.4
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @return bool True if update, otherwise false.
 */
function geodir_event_location_update_count_reviews() {
	global $wpdb, $plugin_prefix;
	
	$listing_table = $plugin_prefix . 'gd_event_detail';
	$today_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	
	$sql = "SELECT ed.post_id FROM `" . $listing_table . "` AS ed INNER JOIN " . EVENT_SCHEDULE . " AS es ON (es.event_id = ed.post_id) WHERE ed.post_locations !='' AND es.event_enddate = '" . date_i18n( 'Y-m-d', strtotime($today_date . ' -1 day')  ) . "'";
	$rows = $wpdb->get_results($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			$post_id = $row->post_id;
			geodir_term_review_count_update($post_id);
		}
		
		return true;
	}
	
	return false;;
}

/**
 * Filter reviews sql query fro upcomin events.
 *
 * @since 1.2.4
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $sql Database sql query.
 * @param int $term_id The term ID.
 * @param int $taxonomy The taxonomy Id.
 * @param string $post_type The post type.
 * @return string Database sql query.
 */
function geodir_event_count_reviews_by_term_sql($sql, $term_id, $taxonomy, $post_type) {
	if ($term_id > 0 && $post_type == 'gd_event') {
		global $wpdb, $plugin_prefix;
		
		$listing_table = $plugin_prefix . $post_type . '_detail';
		
		$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
		
		$sql = "SELECT COALESCE(SUM(ed.rating_count),0) FROM `" . $listing_table . "` AS ed INNER JOIN " . EVENT_SCHEDULE . " AS es ON (es.event_id = ed.post_id) WHERE ed.post_status = 'publish' AND ed.rating_count > 0 AND FIND_IN_SET(" . $term_id . ", ed." . $taxonomy . ")";
		$sql .= " AND (es.event_date >= '" . $current_date . "' OR (es.event_date <= '" . $current_date . "' AND es.event_enddate >= '" . $current_date . "'))";
	}
	
	return $sql;
}

/**
 * Filter reviews sql query fro upcoming events for current location.
 *
 * @since 1.2.4
 * @since 1.3.0 Fixed post term count for neighbourhood locations.
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $sql Database sql query.
 * @param int $term_id The term ID.
 * @param int $taxonomy The taxonomy Id.
 * @param string $post_type The post type.
 * @param string $location_type Location type .
 * @param array $loc Current location terms.
 * @param string $count_type The term count type.
 * @return string Database sql query.
 */
function geodir_event_count_reviews_by_location_term_sql($sql, $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type) {
	if ($term_id > 0 && $post_type == 'gd_event') {
		global $wpdb, $plugin_prefix;
		
		if ($count_type == 'review_count') {			
			$listing_table = $plugin_prefix . $post_type . '_detail';
			
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
			
			if (!$loc) {
				$loc = geodir_get_current_location_terms();
			}
			
			$country = isset($loc['gd_country']) && $loc['gd_country'] != '' ? $loc['gd_country'] : '';
			$region = isset($loc['gd_region']) && $loc['gd_region'] != '' ? $loc['gd_region'] : '';
			$city = isset($loc['gd_city']) && $loc['gd_city'] != '' ? $loc['gd_city'] : '';
			$neighbourhood = '';
			if ($city != '' && isset($loc['gd_neighbourhood']) && $loc['gd_neighbourhood'] != '') {
				$location_type = 'gd_neighbourhood';
				$neighbourhood = $loc['gd_neighbourhood'];
			}
			
			$where = '';
			if ( $country!= '') {
				$where .= " AND ed.post_locations LIKE '%,[".$country."]' ";
			}
			
			if ( $region != '' && $location_type!='gd_country' ) {
				$where .= " AND ed.post_locations LIKE '%,[".$region."],%' ";
			}
			
			if ( $city != '' && $location_type!='gd_country' && $location_type!='gd_region' ) {
				$where .= " AND ed.post_locations LIKE '[".$city."],%' ";
			}
			
			if ($location_type == 'gd_neighbourhood' && $neighbourhood != '' && $wpdb->get_var("SHOW COLUMNS FROM " . $listing_table . " WHERE field = 'post_neighbourhood'")) {
				$where .= " AND ed.post_neighbourhood = '" . $neighbourhood . "' ";
			}
			
			$sql = "SELECT COALESCE(SUM(ed.rating_count),0) FROM `" . $listing_table . "` AS ed INNER JOIN " . EVENT_SCHEDULE . " AS es ON (es.event_id = ed.post_id) WHERE ed.post_status = 'publish' " . $where . " AND ed.rating_count > 0 AND FIND_IN_SET(" . $term_id . ", ed." . $taxonomy . ")";
			
			$sql .= " AND (es.event_date >= '" . $current_date . "' OR (es.event_date <= '" . $current_date . "' AND es.event_enddate >= '" . $current_date . "'))";
		}
	}
	
	return $sql;
}

/**
 * Filter the page link to best of widget view all lisitngs.
 *
 * @since 1.2.4
 *
 * @param string $view_all_link View all listings page link.
 * @param string $post_type The Post type.
 * @param object $term The category term object.
 * @return string Link url.
 */
function geodir_event_bestof_widget_view_all_link($view_all_link, $post_type, $term) {
	if ($post_type == 'gd_event') {
		$view_all_link = add_query_arg(array('etype' => 'upcoming'), $view_all_link) ;
	}
	return $view_all_link;
}

/**
 * Displays the event dates in the meta info in the map info window.
 *
 * @since 1.2.7
 * @since 1.4.6 Same day events should just show date and from - to time.
 *
 * @global string $geodir_date_format Date format.
 * @global string $geodir_date_time_format Date time format.
 *
 * @param object $post_id The post id.
 * @param object $post The post info as an object.
 * @param bool|string $preview True if currently in post preview page. Empty string if not.                           *
 */
function geodir_event_infowindow_meta_event_dates($post_id, $post, $preview) {
	global $geodir_date_format, $geodir_date_time_format, $geodir_time_format;
	if (empty($post)) {
		return NULL;
	}
	
	$limit = (int)get_option('geodir_event_infowindow_dates_count', 1); // no of event dates to show in map infowindow.
	if (!$limit > 0) {
		return NULL;
	}
	
	$post_type = isset($post->post_type) ? $post->post_type : NULL;
	if ((int)$post_id > 0) {
		$post_type = get_post_type($post_id);
	}
	
	if (empty($post_type) && $preview) {
		$post_type = !empty($post->listing_type) ? $post->listing_type : (!empty($post->post_type) ? $post->post_type : NULL);
	}
	
	if ($post_type != 'gd_event') {
		return NULL;
	}
	
	$event_type = get_option('geodir_event_infowindow_dates_filter', 'upcoming');
	$schedule_dates = geodir_event_get_schedule_dates($post, $preview, $event_type);

	$dates = array();
	if (!empty($schedule_dates)) {
		$count = 0;
		foreach ($schedule_dates as $date) {
			$event_date = $date['event_date'];
			$event_enddate = $date['event_enddate'];
			$event_starttime = $date['event_starttime'];
			$event_endtime = $date['event_endtime'];
			
			if ($event_enddate == '0000-00-00') {
				$event_enddate = $event_date;
			}
			
			$full_day = false;
			$same_datetime = false;
			$same_day = false;
			
			if ($event_starttime == $event_endtime && ($event_starttime == '00:00:00' || $event_starttime == '00:00' || $event_starttime == '')) {
				$full_day = true;
			}
			
			if ($event_date == $event_enddate && $full_day) {
				$same_datetime = true;
			}
			
			$ievent_date = strtotime($event_date . ' ' . $event_starttime);
			$ievent_enddate = strtotime($event_enddate . ' ' . $event_endtime);
			
			if ($full_day) {
				$start_date = date_i18n($geodir_date_format, $ievent_date);
				$end_date = date_i18n($geodir_date_format, $ievent_enddate);
			} else {
				$start_date = date_i18n($geodir_date_time_format, $ievent_date);
				
				if (!$same_datetime && date_i18n( 'Y-m-d', $ievent_date ) == date_i18n( 'Y-m-d', $ievent_enddate ) ) {
					$same_day = true;
					
					$start_date .= ' - ' . date_i18n( $geodir_time_format, $ievent_enddate );
				} else {
					$end_date = date_i18n($geodir_date_time_format, $ievent_enddate);
				}
			}
			
			$schedule = '<span class="geodir_schedule clearfix"><span class="geodir_schedule_start"><i class="fa fa-caret-right"></i> ' . $start_date . '</span>';
			if (!$same_datetime && !$same_day) {
				$schedule .= '<br /><span class="geodir_schedule_end"><i class="fa fa-caret-left"></i> ' . $end_date . '</span>';
			}
			$schedule .= '</span>';
			
			$dates[] = $schedule;
			
			$count++;
			if ($limit == $count) {
				break;
			}
		}
	}
	
	if (empty($dates)) {
		return NULL;
	}
		
	$content = '<div class="geodir_event_schedule">' . implode('', $dates) . '</div>';
	
	echo $content;
}

/**
 * Get the event schedule dates array.
 *
 * @since 1.2.7
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param object|int $post The post id or the post object.
 * @param bool|string $preview True if currently in post preview page. Empty string if not.
 * @param string $event_type Event type filter. Default 'upcoming'.
 * @return array Array of event schedule dates.
 */
function geodir_event_get_schedule_dates($post, $preview = false, $event_type = 'upcoming') {
	global $wpdb;
	
	$today = date_i18n('Y-m-d', current_time('timestamp'));
	$today_timestamp = strtotime($today);
	
	$results = array();
	if (!$preview) {
		$post_id = NULL;
		
		if (is_int($post)) {
			$post_id = $post;
		} else {
			$post_data = (array)$post;
			$post_id = !empty($post_data['post_id']) ? $post_data['post_id'] : (!empty($post_data['ID']) ? $post_data['ID'] : NULL);
			
		}
		
		if (!$post_id > 0) {
			return NULL;
		}
		
		$where = "";
		
		switch($event_type) {
			case 'past':
				$where = " AND event_date < '" . $today . "'";
			break;
			case 'today':
				$where = " AND (event_date LIKE '" . $today . "%%' OR (event_date <= '" . $today . "' AND event_enddate >= '" . $today . "')) ";
			break;
			case 'upcoming':
				$where = " AND (event_date >= '" . $today . "' OR (event_date <= '" . $today . "' AND event_enddate >= '" . $today . "')) ";
			break;
			case 'all':
			default:
			break;
		}
		
		$sql = "SELECT *, DATE_FORMAT(event_date, '%Y-%m-%d') AS event_date FROM `" . EVENT_SCHEDULE . "` WHERE event_id=" . (int)$post_id . " " . $where . " GROUP BY CONCAT(event_id, '-', event_date) ORDER BY event_date ASC";
		
		$results = $wpdb->get_results($sql, ARRAY_A);
	} else {
		$post_data = (array)$post;
		
		if (empty($post_data)) {
			return NULL;
		}
		
		$post_id = isset($post_data['ID']) ? $post_data['ID'] : NULL;
		
		// Check recurring enabled
		$recurring_pkg = geodir_event_recurring_pkg($post);
			
		if (!$recurring_pkg) {
			$post_data['is_recurring'] = false;
		}
		
		// all day
		$all_day = isset($post_data['all_day']) && !empty($post_data['all_day']) ? true : false;
		$different_times = isset($post_data['different_times']) && !empty($post_data['different_times']) ? true : false;
		$starttime = !$all_day && isset($post_data['starttime']) ? $post_data['starttime'] : '';
		$endtime = !$all_day && isset($post_data['endtime']) ? $post_data['endtime'] : '';
		$starttimes = !$all_day && isset($post_data['starttimes']) ? $post_data['starttimes'] : array();
		$endtimes = !$all_day && isset($post_data['endtimes']) ? $post_data['endtimes'] : array();

		if (!empty($post_data['is_recurring']) && $recurring_pkg) {
			$repeat_type = isset($post_data['repeat_type']) && in_array($post_data['repeat_type'], array('day', 'week', 'month', 'year', 'custom')) ? $post_data['repeat_type'] : 'year'; // day, week, month, year, custom
			
			$start_date = geodir_event_is_date($post_data['event_start']) ? $post_data['event_start'] : date_i18n('Y-m-d', current_time('timestamp'));
			$end_date = isset($post_data['event_end']) ? trim($post_data['event_end']) : '';
			
			$repeat_x = isset($post_data['repeat_x']) ? trim($post_data['repeat_x']) : '';
			$duration_x = isset($post_data['duration_x']) ? trim($post_data['duration_x']) : 1;
			$repeat_end_type = isset($post_data['repeat_end_type']) ? trim($post_data['repeat_end_type']) : 0;
			
			$max_repeat = $repeat_end_type != 1 && isset($post_data['max_repeat']) ? (int)$post_data['max_repeat'] : 0;
			$repeat_end = $repeat_end_type == 1 && isset($post_data['repeat_end']) ? $post_data['repeat_end'] : '';
										 
			if (geodir_event_is_date($end_date) && strtotime($end_date) < strtotime($start_date)) {
				$end_date = $start_date;
			}
				
			$repeat_x = $repeat_x > 0 ? (int)$repeat_x : 1;
			$duration_x = $duration_x > 0 ? (int)$duration_x : 1;
			$max_repeat = $max_repeat > 0 ? (int)$max_repeat : 1;
				
			if ($repeat_type == 'custom') {
				$event_recurring_dates = explode(',', $post_data['event_recurring_dates']);
			} else {
				// week days
				$repeat_days = array();
				if ($repeat_type == 'week' || $repeat_type == 'month') {
					$repeat_days = isset($post_data['repeat_days']) ? $post_data['repeat_days'] : $repeat_days;
				}
				
				// by week
				$repeat_weeks = array();
				if ($repeat_type == 'month') {
					$repeat_weeks = isset($post_data['repeat_weeks']) ? $post_data['repeat_weeks'] : $repeat_weeks;
				}
		
				$event_recurring_dates = geodir_event_date_occurrences($repeat_type, $start_date, $end_date, $repeat_x, $max_repeat, $repeat_end, $repeat_days, $repeat_weeks);
			}
			
			if (empty($event_recurring_dates)) {
				return NULL;
			}
			
			$duration_x--;
		
			$c = 0;
			foreach($event_recurring_dates as $key => $date) {
				$result = array();
				if ($repeat_type == 'custom' && $different_times) {
					$duration_x = 0;
					$starttime = isset($starttimes[$c]) ? $starttimes[$c] : '';
					$endtime = isset($endtimes[$c]) ? $endtimes[$c] : '';
				}
				
				if ($all_day == 1) {
					$starttime = '';
					$endtime = '';
				}
				
				$event_enddate = date_i18n('Y-m-d', strtotime($date . ' + ' . $duration_x . ' day'));
				$event_start_timestamp = strtotime($date);
				$event_end_timestamp = strtotime($event_enddate);
				
				if ($event_type == 'past' && !($event_end_timestamp < $today_timestamp)) {
					continue;
				} else if ($event_type == 'today' && !($event_start_timestamp == $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
					continue;
				} else if ($event_type == 'upcoming' && !($event_start_timestamp >= $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
					continue;
				}
				
				$result['event_id'] = $post_id;
				$result['event_date'] = $date;
				$result['event_enddate'] = $event_enddate;
				$result['event_starttime'] = $starttime;
				$result['event_endtime'] = $endtime;
				$result['recurring'] = true;
				$result['all_day'] = $all_day;
				
				$c++;
				
				$results[] = $result;
			}
		} else {
			$start_date = isset($post_data['event_start']) ? $post_data['event_start'] : '';
			$end_date = isset($post_data['event_end']) ? $post_data['event_end'] : $start_date;
			
			if (!geodir_event_is_date($start_date) && !empty($post_data['event_recurring_dates'])) {
				$event_recurring_dates = explode(',', $post_data['event_recurring_dates']);
				$start_date = $event_recurring_dates[0];
			}
			
			$start_date = geodir_event_is_date($start_date) ? $start_date : $today;
			
			if (strtotime($end_date) < strtotime($start_date)) {
				$end_date = $start_date;
			}
			
			if ($starttime == '' && !empty($starttimes)) {
				$starttime = $starttimes[0];
				$endtime = $endtimes[0];
			}
			
			if ($all_day) {
				$starttime = '';
				$endtime = '';
			}
			
			$event_start_timestamp = strtotime($start_date);
			$event_end_timestamp = strtotime($end_date);
			
			if ($event_type == 'past' && !($event_end_timestamp < $today_timestamp)) {
				return NULL;
			} else if ($event_type == 'today' && !($event_start_timestamp == $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
				return NULL;
			} else if ($event_type == 'upcoming' && !($event_start_timestamp >= $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
				return NULL;
			}
			
			$result['event_id'] = $post_id;
			$result['event_date'] = $start_date;
			$result['event_enddate'] = $end_date;
			$result['event_starttime'] = $starttime;
			$result['event_endtime'] = $endtime;
			$result['recurring'] = false;
			$result['all_day'] = $all_day;
			$results[] = $result;
		}
	}
	
	return $results;
}

add_filter('geodir_filter_widget_listings_groupby','geodir_event_filter_widget_listings_groupby',10,2);
function geodir_event_filter_widget_listings_groupby($groupby, $post_type){

    if($post_type=='gd_event'){
        $groupby .= ' ,'.EVENT_SCHEDULE.'.event_date ';
    }
    return $groupby;
}

function geodir_event_home_map_marker_query_join($join = '') {
	global $plugin_prefix;
	
	$join .= " INNER JOIN " . $plugin_prefix . "event_schedule AS es ON es.event_id = pd.post_id";
	return $join;
}

function geodir_event_home_map_marker_query_where($where = '') {
	$today = date_i18n('Y-m-d');
	
	$where .= " AND (es.event_date >= '" . $today . "' OR (es.event_date <= '" . $today . "' AND es.event_enddate >= '" . $today . "')) ";
	return $where;
}

function geodir_get_detail_page_related_events($request) {
	if (!empty($request)) {
		$post_number = (isset($request['post_number']) && !empty($request['post_number'])) ? $request['post_number'] : '5';
		$relate_to = (isset($request['relate_to']) && !empty($request['relate_to'])) ? $request['relate_to'] : 'category';
		$add_location_filter = (isset($request['add_location_filter']) && !empty($request['add_location_filter'])) ? $request['add_location_filter'] : '0';
		$listing_width = (isset($request['listing_width']) && !empty($request['listing_width'])) ? $request['listing_width'] : '';
		$list_sort = (isset($request['list_sort']) && !empty($request['list_sort'])) ? $request['list_sort'] : 'latest';
		$character_count = (isset($request['character_count']) && !empty($request['character_count'])) ? $request['character_count'] : '';
		$event_type = (isset($request['event_type']) && !empty($request['event_type'])) ? $request['event_type'] : 'upcoming';
		$event_type = apply_filters('geodir_detail_page_related_event_type', $event_type);
        $layout = !empty($request['layout']) ? $request['layout'] : '';

		global $post, $map_jason;
		$current_map_jason = $map_jason;
		$post_type = $post->post_type;
		$post_id = $post->ID;
		$category_taxonomy = '';
		$tax_field = 'id';
		$category = array();

		if ($relate_to == 'category') {

			$category_taxonomy = $post_type . $relate_to;
			if (isset($post->{$category_taxonomy}) && $post->{$category_taxonomy} != '')
				$category = explode(',', trim($post->{$category_taxonomy}, ','));

		} elseif ($relate_to == 'tags') {

			$category_taxonomy = $post_type . '_' . $relate_to;
			if ($post->post_tags != '')
				$category = explode(',', trim($post->post_tags, ','));
			$tax_field = 'name';
		}

		/* --- return false in invalid request --- */
		if (empty($category))
			return false;

		$all_postypes = geodir_get_posttypes();

		if (!in_array($post_type, $all_postypes))
			return false;

		$query_args = array(
				'geodir_event_type' => $event_type,
				'posts_per_page' => $post_number,
				'is_geodir_loop' => true,
				'gd_location' 	 => $add_location_filter ? true : false,
				'post_type' => 'gd_event',
				'post__not_in'     => array( $post_id ),
				'order_by' => $list_sort,
				'excerpt_length' => $character_count,
				'character_count' => $character_count,
				'listing_width' => $listing_width
		);

		$tax_query = array('taxonomy' => $category_taxonomy,
				'field' => $tax_field,
				'terms' => $category
		);

		$query_args['tax_query'] = array( $tax_query );


		add_filter( 'geodir_event_filter_widget_events_where', 'geodir_event_function_related_post_ids_where' );
		$output = geodir_get_post_widget_events($query_args, $layout);
		remove_filter( 'geodir_event_filter_widget_events_where', 'geodir_event_function_related_post_ids_where' );
		
		$map_jason = $current_map_jason;

		$map_jason = $current_map_jason;

		return $output;
	}
	return false;
}

function geodir_event_function_related_post_ids_where( $where ) {
	global $wpdb, $plugin_prefix, $gd_query_args;

	if ( !empty( $gd_query_args ) && isset( $gd_query_args['related_post_ids'] ) ) {
		if ($gd_query_args['related_post_ids']) {
			$where .= " AND ".$wpdb->posts .".ID IN (" . implode(',', $gd_query_args['related_post_ids']) . ")";
		}
	}

	return $where;
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.4.2
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_event_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}

/**
 * Filter the related posts widget query args.
 *
 * @since 1.4.3
 *
 * @param array $query_args The query array.
 * @param array $request Related posts request array.
 * @return array Modified query args.
 */
function geodir_event_related_posts_query_args($query_args, $request) {
    if (!empty($query_args['post_type']) && $query_args['post_type'] == 'gd_event') {
        $query_args['geodir_event_listing_filter'] = 'upcoming';
    }
    
    return $query_args;
}

/**
 * Display notice when site is running with older then PHP 5.3.
 *
 * @since 1.4.5
 *
 */
function geodir_event_PHP_version_notice() {
    echo '<div class="error" style="margin:12px 0"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by <b>GeoDirectory Events</b>. Please contact your host and request that your PHP version be upgraded to <b>5.3 or later</b>.', 'geodirevents' ) . '</p></div>';
}