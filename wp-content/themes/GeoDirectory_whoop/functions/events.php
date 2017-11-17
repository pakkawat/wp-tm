<?php
function whoop_geodir_event_template_loader($template) {

    if(geodir_get_current_posttype() == 'gd_event'){
        remove_filter('geodir_detail_page_sidebar_content', 'geodir_event_detail_page_sitebar_content', 2);
    }

    return $template;
}


function whoop_event_listing_template($template)
{
    global $preview;
    $listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';
    if (($preview && $listing_type == 'gd_event') || (get_query_var('post_type') == 'gd_event')) {
        remove_action('geodir_detail_before_main_content', 'geodir_whoop_big_header', 30);
        $template = locate_template(array("geodirectory/listing-eventdetail.php"));
    }
    return $template;
}

add_filter('geodir_template_detail', 'whoop_event_listing_template');
add_filter('geodir_template_preview', 'whoop_event_listing_template');

function get_event_date_from_post($post)
{
    global $preview;

    $return = false;

    if ($preview) {
        $event_start_date = $post->event_start ? date('l, F j, Y', strtotime($post->event_start)) : '';
        $event_start_time = $post->starttime ? date('g:i a', strtotime($post->starttime)) : '';

        $event_end_date = $post->event_end ? date('l, F j, Y', strtotime($post->event_end)) : '';
        $event_end_time = $post->endtime ? date('g:i a', strtotime($post->endtime)) : '';
    } else {
        $event_details = maybe_unserialize($post->recurring_dates);

        $event_start_date = isset($event_details['event_start']) ? date('l, F j, Y', strtotime($event_details['event_start'])) : '';
        $event_start_time = isset($event_details['starttime']) ? date('g:i a', strtotime($event_details['starttime'])) : '';

        $event_end_date = isset($event_details['event_end']) ? date('l, F j, Y', strtotime($event_details['event_end'])) : '';
        $event_end_time = isset($event_details['endtime']) ? date('g:i a', strtotime($event_details['endtime'])) : '';

        if (isset($event_details['is_recurring']) && $event_details['repeat_type'] == 'week' && isset($event_details['repeat_days']) && $event_details['repeat_days']) {
            $days = array( __('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'));
            $event_start_date = $days[(int) $event_details['repeat_days'][0]];
            $event_end_date = $days[(int) end( $event_details['repeat_days'] )];

            $return = '<span class="eve-start-date">' . $event_start_date . ' to </span><span class="eve-end-date">' . $event_end_date . ', ' . $event_start_time . ' - ' . $event_end_time . '</span>';

        } elseif (isset($event_details['is_recurring'])) {
            $gde = isset( $_GET['gde'] ) ? strip_tags($_GET['gde']) : false;

            if ($gde) {
                $event_start_date = $event_details['event_start'] ? date('l, F j, Y', strtotime($gde)) : '';
            }
        }
    }

    if ($return) {
        return $return;
    }
    $output = '<span class="eve-start-date">' . $event_start_date . ' ' . $event_start_time . '</span>';
    if (!empty($post->event_end)) {
        $output .= ' - <span class="eve-end-date">' . $event_end_date . ' ' . $event_end_time . '</span>';
    }
    return $output;
}

function geodir_whoop_event_show_shedule_date( $post ) {
    global $geodir_date_time_format, $geodir_date_format, $geodir_time_format;
    
    if ( geodir_is_page( 'preview' ) ) {
        $recuring_data = (array)$post;
        $input_format = geodir_event_field_date_format();
            
        if (isset($recuring_data['event_start']) && $recuring_data['event_start']) {
            $recuring_data['event_start'] = geodir_date($recuring_data['event_start'], 'Y-m-d', $input_format);
        }

        if (isset($recuring_data['event_end']) && $recuring_data['event_end']) {
            $recuring_data['event_end'] = geodir_date($recuring_data['event_end'], 'Y-m-d', $input_format);
        }
        
        if (isset($recuring_data['repeat_end']) && $recuring_data['repeat_end']) {
            $recuring_data['repeat_end'] = geodir_date($recuring_data['repeat_end'], 'Y-m-d', $input_format);
        }
    } else {
        $recuring_data = !empty( $post->recurring_dates ) ? maybe_unserialize( $post->recurring_dates ) : NULL;
    }
    
    $schedules = '';

    if ( !empty( $recuring_data ) && ( isset( $recuring_data['event_recurring_dates'] ) && $recuring_data['event_recurring_dates'] != '' ) || ( isset( $post->is_recurring ) && !empty( $post->is_recurring ) ) ) {
        $geodir_num_dates = 0;
        $starttimes = '';
        $endtimes = '';
        $astarttimes = array();
        $aendtimes = array();
        
        // Check recurring enabled
        $recurring_pkg = geodir_event_recurring_pkg( $post );
        
        $hide_past_dates = get_option( 'geodir_event_hide_past_dates' );
        
        if ( $post->is_recurring && $recurring_pkg ) {
            if ( !isset( $recuring_data['repeat_type'] ) ) {
                $recuring_data['repeat_type'] = 'custom';
            }
            
            $repeat_type = isset( $recuring_data['repeat_type'] ) && in_array( $recuring_data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $recuring_data['repeat_type'] : 'year'; // day, week, month, year, custom
            
            $different_times = isset( $recuring_data['different_times'] ) && !empty( $recuring_data['different_times'] ) ? true : false;
            
            if ( geodir_is_page( 'preview' ) ) {
                $start_date = geodir_event_is_date( $recuring_data['event_start'] ) ? $recuring_data['event_start'] : date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
                $end_date = isset( $recuring_data['event_end'] ) ? trim( $recuring_data['event_end'] ) : '';
                $all_day = isset( $recuring_data['all_day'] ) && !empty( $recuring_data['all_day'] ) ? true : false;
                $starttime = isset( $recuring_data['starttime'] ) && !$all_day ? trim( $recuring_data['starttime'] ) : '';
                $endtime = isset( $recuring_data['endtime'] ) && !$all_day ? trim( $recuring_data['endtime'] ) : '';

                $starttimes = isset( $recuring_data['starttimes'] ) && !$all_day ? $recuring_data['starttimes'] : '';
                $endtimes = isset( $recuring_data['endtimes'] ) && !$all_day ? $recuring_data['endtimes'] : '';
            
                $repeat_x = isset( $recuring_data['repeat_x'] ) ? trim( $recuring_data['repeat_x'] ) : '';
                $duration_x = isset( $recuring_data['duration_x'] ) ? trim( $recuring_data['duration_x'] ) : 1;
                $repeat_end_type = isset( $recuring_data['repeat_end_type'] ) ? trim( $recuring_data['repeat_end_type'] ) : 0;
            
                $max_repeat = $repeat_end_type != 1 && isset( $recuring_data['max_repeat'] ) ? (int)$recuring_data['max_repeat'] : 0;
                $repeat_end = $repeat_end_type == 1 && isset( $recuring_data['repeat_end'] ) ? $recuring_data['repeat_end'] : '';
                                         
                if ( geodir_event_is_date( $end_date ) && strtotime( $end_date ) < strtotime( $start_date ) ) {
                    $end_date = $start_date;
                }
                
                $repeat_x = $repeat_x > 0 ? (int)$repeat_x : 1;
                $duration_x = $duration_x > 0 ? (int)$duration_x : 1;
                $max_repeat = $max_repeat > 0 ? (int)$max_repeat : 1;
                
                if ( $repeat_end_type == 1 && !geodir_event_is_date( $repeat_end ) ) {
                    $repeat_end = '';
                }
                
                if ( $repeat_type == 'custom' ) {
                    $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                } else {
                    // week days
                    $repeat_days = array();
                    if ( $repeat_type == 'week' || $repeat_type == 'month' ) {
                        $repeat_days = isset( $recuring_data['repeat_days'] ) ? $recuring_data['repeat_days'] : $repeat_days;
                    }
                    
                    // by week
                    $repeat_weeks = array();
                    if ( $repeat_type == 'month' ) {
                        $repeat_weeks = isset( $recuring_data['repeat_weeks'] ) ? $recuring_data['repeat_weeks'] : $repeat_weeks;
                    }
            
                    $event_recurring_dates = geodir_event_date_occurrences( $repeat_type, $start_date, $end_date, $repeat_x, $max_repeat, $repeat_end, $repeat_days, $repeat_weeks );
                }
            } else {
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
            }

            if ( empty( $recuring_data['all_day'] ) ) {
                if ( $repeat_type == 'custom' && $different_times ) {
                    $astarttimes = isset( $recuring_data['starttimes'] ) ? $recuring_data['starttimes'] : array();
                    $aendtimes = isset( $recuring_data['endtimes'] ) ? $recuring_data['endtimes'] : array();
                } else {
                    $starttimes = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                    $endtimes = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                }
            }
            
            $schedules = '';

            foreach( $event_recurring_dates as $key => $date ) {
                $geodir_num_dates++;
                
                if ( $repeat_type == 'custom' && $different_times ) {
                    if ( !empty( $astarttimes ) && isset( $astarttimes[$key] ) ) {
                        $starttimes = $astarttimes[$key];
                        $endtimes = $aendtimes[$key];
                    } else {
                        $starttimes = '';
                        $endtimes = '';
                    }
                }
                
                $duration = isset( $recuring_data['duration_x'] ) && (int)$recuring_data['duration_x'] > 0 ? (int)$recuring_data['duration_x'] : 1;
                $duration--;
                $enddate = date_i18n( 'Y-m-d', strtotime( $date . ' + ' . $duration . ' day' ) );
                
                // Hide past dates
                if ( $hide_past_dates && strtotime( $enddate ) < strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ) ) {
                    $geodir_num_dates--;
                    continue;
                }
                        
                $sdate = strtotime( $date . ' ' . $starttimes );
                $edate = strtotime( $enddate . ' ' . $endtimes );
                            
                $start_date = date_i18n( $geodir_date_time_format, $sdate );
                $end_date = date_i18n( $geodir_date_time_format, $edate );
                
                $full_day = false;
                $same_datetime = false;
                
                if ( $starttimes == $endtimes && ( $starttimes == '' || $starttimes == '00:00:00' || $starttimes == '00:00' ) ) {
                    $full_day = true;
                }
                
                if ( $start_date == $end_date && $full_day ) {
                    $same_datetime = true;
                }

                $link_date = date_i18n( 'Y-m-d', $sdate );
                $title_date = date_i18n( $geodir_date_format, $sdate );
                if ( $full_day ) {
                    $start_date = $title_date;
                    $end_date = date_i18n( $geodir_date_format, $edate );
                }
                
                // recuring event title
                $recurring_event_title = $post->post_title . ' - ' . $title_date;
                $recurring_event_title = apply_filters( 'geodir_event_recurring_event_link', $recurring_event_title, $post->ID );
                
                // recuring event link
                $recurring_event_link = geodir_getlink( get_permalink( $post->ID ), array( 'gde' => $link_date ) );
                $recurring_event_link = esc_url( apply_filters( 'geodir_event_recurring_event_link', $recurring_event_link, $post->ID ) );
                
                $recurring_class = 'gde-recurr-link';
                $recurring_class_cont = 'gde-recurring-cont';
                if ( isset( $_REQUEST['gde'] ) && $_REQUEST['gde'] == $link_date ) {
                    $recurring_event_link = 'javascript:void(0);';
                    $recurring_class .= ' gde-recurr-act';
                    $recurring_class_cont .= ' gde-recurr-cont-act';
                }
                                
                $schedules .= '<p class="' . $recurring_class_cont . '">';
                $schedules .= '<i class="fa fa-caret-right"></i> <a class="' . $recurring_class . '" href="' . $recurring_event_link . '" title="' . esc_attr( $recurring_event_title ) . '">';
                $schedules .= '<span class="eve-start-date">' . $start_date . '</span>';
                if ( !$same_datetime ) {
                    $schedules .= ' - ';
                    if ( date_i18n( 'Y-m-d', $sdate ) == date_i18n( 'Y-m-d', $edate ) ) {
                        $end_date = date_i18n( $geodir_time_format, $edate );
                    }
                    $schedules .= '<span class="eve-end-date">' . $end_date . '</span>';
                }
                $schedules .= '</a>';
                $schedules .= '</p>';
            }
            
            if ( !$geodir_num_dates > 0 ) {
                return;
            }
        } else {
            $geodir_num_dates = 0;
            
            if ( isset( $recuring_data['is_recurring'] ) ) {
                $start_date = isset( $recuring_data['event_start'] ) ? $recuring_data['event_start'] : '';
                $end_date = isset( $recuring_data['event_end'] ) ? $recuring_data['event_end'] : $start_date;
                $all_day = isset( $recuring_data['all_day'] ) && !empty( $recuring_data['all_day'] ) ? true : false;
                $starttime = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                $endtime = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                $starttimes = isset( $recuring_data['starttimes'] ) && !empty( $recuring_data['starttimes'] ) ? $recuring_data['starttimes'] : array();
                $endtimes = isset( $recuring_data['endtimes'] ) && !empty( $recuring_data['endtimes'] ) ? $recuring_data['endtimes'] : array();
                
                if ( !geodir_event_is_date( $start_date ) && !empty( $event_recurring_dates ) ) {
                    $start_date = $event_recurring_dates[0];
                }
                            
                if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
                    $end_date = $start_date;
                }
                
                if ( $starttime == '' && !empty( $starttimes ) ) {
                    $starttime = $starttimes[0];
                    $endtime = $endtimes[0];
                }
                                        
                $one_day = false;
                if ( $start_date == $end_date && $all_day ) {
                    $one_day = true;
                }

                if ( $all_day ) {
                    $start_datetime = strtotime( $start_date );
                    $end_datetime = strtotime( $end_date );
                    
                    $start_date = date_i18n( $geodir_date_format, $start_datetime );
                    $end_date = date_i18n( $geodir_date_format, $end_datetime );
                    if ( $start_date == $end_date ) {
                        $one_day = true;
                    }
                } else {
                    if ( $start_date == $end_date && $starttime == $endtime ) {
                        $end_date = date_i18n( 'Y-m-d', strtotime( $start_date . ' ' . $starttime . ' +1 day' ) );
                        $one_day = false;
                    }
                    $start_datetime = strtotime( $start_date . ' ' . $starttime );
                    $end_datetime = strtotime( $end_date . ' ' . $endtime );
                    
                    $start_date = date_i18n( $geodir_date_time_format, $start_datetime );
                    $end_date = date_i18n( $geodir_date_time_format, $end_datetime );
                }
                
                $title_start_date = date_i18n( 'Y-m-d H:i:s', $start_datetime ) . ' ' . date_i18n( 'T+H:i', get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
                if ( !$one_day ) {
                    $title_start_date .=  ' - ' . date_i18n( 'Y-m-d H:i:s', $end_datetime ) . ' ' . date_i18n( 'T+H:i', get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
                }
                
                $schedules .= '<p title="' . esc_attr( $title_start_date ) . '">';
                $schedules .= '<span class="eve-start-date">' . $start_date. '</span>';
                if ( !$one_day ) {
                    $schedules .= ' - ';
                    if ( date_i18n( 'Y-m-d', $start_datetime ) == date_i18n( 'Y-m-d', $end_datetime ) ) {
                        $end_date = date_i18n( $geodir_time_format, $end_datetime );
                    }
                    $schedules .= '<span class="eve-end-date">' . $end_date. '</span>';
                }
                $schedules .= '</p>';
            } else { // older event dates
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                $starttimes = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                $endtimes = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                
                $schedules = '';
                
                foreach( $event_recurring_dates as $key => $date ) {
                    $geodir_num_dates++;
                
                    if ( isset( $recuring_data['different_times'] ) && $recuring_data['different_times'] == '1' ) {
                        $starttimes = isset( $recuring_data['starttimes'][$key] ) ? $recuring_data['starttimes'][$key] : '';
                        $endtimes = isset( $recuring_data['endtimes'][$key] ) ? $recuring_data['endtimes'][$key] : '';
                    }
                    
                    $sdate = strtotime( $date . ' ' . $starttimes );
                    $edate = strtotime( $date . ' ' . $endtimes );
                    
                    if ( $starttimes > $endtimes ) {
                        $edate = strtotime( $date . ' ' . $endtimes . " +1 day" );
                    }
                    
                    // Hide past dates
                    if ( $hide_past_dates && strtotime( date_i18n( 'Y-m-d', $edate ) ) < strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ) ) {
                        $geodir_num_dates--;
                        continue;
                    }
                
                    $schedules .= '<p>';
                    $schedules .= '<span class="eve-start-date">' . date_i18n( $geodir_date_time_format, $sdate ). '</span>';
                    if ( $sdate != $edate ) {
                        $output .= ' - ';
                        $schedules .= '<span class="eve-end-date">' . date_i18n( $geodir_date_time_format, $edate ). '</span>';
                    }
                    $schedules .= '</p>';
                }
                
                if ( !$geodir_num_dates > 0 ) {
                    return;
                }
            }
        } 
        
        echo $schedules;
    }
}

function whoop_event_singe_main_content($post)
{
    global $preview;
    ?>
    <div class="whoop-event-detail">
        <div class="whoop-event-photos">
            <div class="whoop-event-photo-inner">
                <?php
                if ($preview) {
                    $image_url_string = $post->post_images;
                    if (!empty($image_url_string)) {
                        $image_urls = explode(',', $image_url_string);
                        $image_url = $image_urls[0];
                        ?>
                        <img width="200" src="<?php echo $image_url; ?>" class="attachment-250x200 wp-post-image" alt="">
                        <?php
                    }
                } else {
//                    if (has_post_thumbnail()) {
//                        echo get_the_post_thumbnail( $post->ID, array( 250, 200) );
//                    }
                    if ($fimage = geodir_get_featured_image($post->ID, '', true, $post->featured_image)) {
                        ?>
                        <img width="200" src="<?php echo $fimage->src; ?>" class="attachment-250x200 wp-post-image" alt="">
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <h1 class="entry-title geodir-big-header-title fn whoop-title">
            <?php
            echo esc_attr(stripslashes($post->post_title));
            ?>
        </h1>
        <div class="whoop_event_details">
            <dl>
                <dt><?php echo __('Category:', GEODIRECTORY_FRAMEWORK); ?></dt>
                <dd>
                    <?php
                    $post_type = 'gd_event';
                    $post_taxonomy = $post_type . 'category';
                    if($preview) {
                        $cats = $post->post_category[$post_taxonomy];
                    } else {
                        $cats = $post->$post_taxonomy;
                    }

                    if (!empty($cats)) {
                        $links = array();
                        $terms = array();
                        $termsOrdered = array();
                        if (!is_array($cats)) {
                            $post_term = explode(",", trim($cats, ","));
                        } else {
                            $post_term = $cats;
                        }

                        $post_term = array_unique($post_term);
                        if (!empty($post_term)) {
                            foreach ($post_term as $pt) {
                                $pt = trim($pt);

                                if ($pt != ''):
                                    $term = get_term_by('id', $pt, $post_taxonomy);
                                    if (is_object($term)) {
                                        $links[] = "<a href='" . esc_attr(get_term_link($term, $post_taxonomy)) . "'>$term->name</a>";
                                        $terms[] = $term;
                                    }
                                endif;
                            }
                            // order alphabetically
                            asort($links);
                            foreach (array_keys($links) as $key) {
                                $termsOrdered[$key] = $terms[$key];
                            }
                            $terms = $termsOrdered;

                        }

                        if (!isset($listing_label)) {
                            $listing_label = '';
                        }
                        $taxonomies[$post_taxonomy] = wp_sprintf('%l', $links, (object)$terms);

                    }


                    if (isset($taxonomies[$post_taxonomy])) {
                        echo '<span class="geodir-category">' . $taxonomies[$post_taxonomy] . '</span>';
                    }
                    ?>
                </dd>
            </dl>
            <?php do_action('whoop_event_detail_after_category'); ?>
            <dl>
                <dt>
                    <?php echo __('When:', GEODIRECTORY_FRAMEWORK); ?>
                </dt>
                <dd class="eve-dates">
                    <?php
                    echo geodir_whoop_event_show_shedule_date($post);
                    ?>
                </dd>
            </dl>
            <dl>
                <dt>
                    <?php echo __('Where:', GEODIRECTORY_FRAMEWORK); ?>
                </dt>
                <dd>
                    <?php
                    echo whoop_get_address_html($post);
                    ?>
                </dd>
            </dl>
            <dl>
                <dt>
                    <?php echo __('Submitted By:', GEODIRECTORY_FRAMEWORK); ?>
                </dt>
                <dd class="eve-submitted-by">
                    <?php
                    if($preview) {
                        $author_id = get_current_user_id();
                    } else {
                        $author_id = $post->post_author;
                    }
                    $user = get_user_by('id', $author_id);
                    ?>
                    <div class="eve-submitted-by-avatar">
                        <a href="<?php echo whoop_get_user_profile_link($user->ID); ?>"><?php echo get_avatar($user->ID, 20); ?></a>
                    </div>
                    <a class="eve-user-name" href="<?php echo whoop_get_user_profile_link($user->ID); ?>">
                        <?php echo whoop_bp_member_name(whoop_get_current_user_name($user)); ?>
                    </a>

                    <a href="<?php echo whoop_get_user_profile_link($user->ID); ?>events/" class="smaller">
                        <?php echo __('See all of', GEODIRECTORY_FRAMEWORK); ?> <?php echo whoop_bp_member_name(whoop_get_current_user_name($user)); ?>'s <?php echo __('events', GEODIRECTORY_FRAMEWORK); ?> &raquo;
                    </a>
                </dd>
            </dl>
            <?php
            $enable_what_why = apply_filters('whoop_events_enable_what_why', false);
            if ($enable_what_why) {
            ?>
            <dl>
                <dt class="eve-desc">
                    <?php echo __('What/Why:', GEODIRECTORY_FRAMEWORK); ?>
                </dt>
                <dd class="eve-desc">
                    <?php
                    if ($preview) {
                        $desc = $post->post_desc;
                    } else {
                        $desc = $post->post_content;
                    }
                    $show_editor = get_option('geodir_tiny_editor_on_add_listing');
                    $desc = $show_editor ? stripslashes($desc) : stripslashes($desc);
                    echo $desc;
                    ?>
                </dd>
            </dl>
            <?php } ?>
        </div>
    </div>
<?php
}

add_action('geodir_event_details_main_content', 'whoop_event_singe_main_content',50);
add_action('geodir_event_details_main_content', 'geodir_show_detail_page_tabs', 60);


function event_list_content_from_post($post) {
    $author_id = $post->post_author;
    $user = get_user_by('id', $author_id);
    ?>
    <li>
        <div class="event-content-box">
            <div class="event-content-avatar">
                <div class="event-content-avatar-inner">
                    <?php
                    if ($fimage = geodir_get_featured_image($post->ID, '', true, $post->featured_image)) {
                        ?>
                        <a href="<?php the_permalink(); ?>">
                            <div class="geodir_thumbnail" style="background-image:url(<?php echo $fimage->src; ?>);"></div>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <div class="event-content-body">
                <div class="event-content-body-top">
                    <div class="event-title">
                        <a href="<?php the_permalink(); ?>"><?php echo get_the_title($post->ID) ?></a>

                        <div class="event-date whoop-event-date-display">
                            <?php //echo get_event_date_from_post($post); ?>
                            <?php geodir_calender_event_details_after_post_title(); ?>
                        </div>
                    </div>
                    <div class="event-author">
                        <div class="event-submitted-by">
                            <?php echo __('Submitted by', GEODIRECTORY_FRAMEWORK); ?><br/>
                            <a href="<?php echo whoop_get_user_profile_link($user->ID); ?>">
                                <?php echo whoop_bp_member_name(whoop_get_current_user_name($user)); ?>
                            </a>
                        </div>
                        <div class="event-submitted-by-avatar">
                            <a href="<?php echo whoop_get_user_profile_link($user->ID); ?>"><?php echo get_avatar($user->ID, 30); ?></a>
                        </div>

                    </div>

                </div>
                <div class="event-content-body-bottom">
                    <div class="event-address">
                        <?php
                        echo whoop_get_address_html($post);
                        ?>
                    </div>
                    <div class="event-interested">
                        <?php echo $post->rsvp_count; ?> <?php echo whoop_pluralize($post->rsvp_count, __('is interested', GEODIRECTORY_FRAMEWORK), __('are interested', GEODIRECTORY_FRAMEWORK)); ?>
                    </div>
                </div>
            </div>
        </div>
    </li>
<?php
}

function whoop_option_geodir_disable_rating($value)
{
    global $post, $gdf;
    if ($gdf['whoop-event-review'] == '1') {
        if(isset($post->post_type) && $post->post_type=='gd_event'){
            remove_action('comment_form_logged_in_after', 'geodir_comment_rating_fields');
            remove_action('comment_form_before_fields', 'geodir_comment_rating_fields');
            if (defined('GEODIRREVIEWRATING_VERSION')) {
                remove_action("comments_template",'geodir_reviewrating_show_post_ratings',10);
                remove_action( 'comment_form_logged_in_after', 'geodir_reviewrating_comment_rating_fields' );
                remove_action( 'comment_form_before_fields', 'geodir_reviewrating_comment_rating_fields' );
            }
            return 1;
        }
    }
    return $value;
}
add_filter('pre_option_geodir_disable_rating', 'whoop_option_geodir_disable_rating');

//function whoop_option_geodir_disable_rating_action() {
//    global $post, $gdf;
//    if ($gdf['whoop-event-review'] == '1') {
//        if(isset($post->post_type) && $post->post_type=='gd_event'){
//            add_filter('pre_option_geodir_disable_rating', '__return_true');
//        }
//    }
//}
//add_action('wp', 'whoop_option_geodir_disable_rating_action', 100);

function geodir_event_index_right_section()
{
    if (get_option('geodir_show_listing_right_section')) { ?>
        <div class="geodir-content-right geodir-sidebar-wrap">
            <?php dynamic_sidebar('event-index'); ?>
        </div>
    <?php }
}

function geodir_event_index_content_section()
{
    ?>
    <?php dynamic_sidebar('event-index-content'); ?>
<?php
}

function whoop_geodir_listings_remove_content()
{
    if (get_query_var('post_type') == 'gd_event' && isset($_GET['e_index'])) {
        remove_action('geodir_listings_content', 'geodir_action_listings_content', 10);
        remove_action('geodir_listings_sidebar_right_inside', 'geodir_listing_right_section', 10);
        remove_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);
        add_action('geodir_listings_sidebar_right_inside', 'geodir_event_index_right_section', 10);
        add_action('geodir_listings_content', 'geodir_event_index_content_section', 10);
    }
}
add_action('wp', 'whoop_geodir_listings_remove_content');

include_once(get_template_directory() .'/whoop-widgets/popular-events-widget.php');
add_filter( 'template_include', 'whoop_geodir_event_template_loader',0);

function whoop_geodir_event_loop_filter_where($where, $filter) {

    $day = date_i18n('w');

    $week_start = date_i18n('Y-m-d', strtotime('-'.$day.' days'));
    $week_end = date_i18n('Y-m-d', strtotime('+'.(6-$day).' days'));

    $next_week_start = date_i18n('Y-m-d', strtotime('+'.(7-$day).' days'));
    $next_week_end = date_i18n('Y-m-d', strtotime('+'.(7+(6-$day)).' days'));

    $week_after_next_start = date_i18n('Y-m-d', strtotime('+'.(14-$day).' days'));
    $week_after_next_end = date_i18n('Y-m-d', strtotime('+'.(14+(6-$day)).' days'));

    if ( $filter == 'this_week' ) {
        $where .= "AND (" . EVENT_SCHEDULE . ".event_date <= '".$week_end."' AND " . EVENT_SCHEDULE . ".event_enddate >='" . $week_start."') ";
    }

    if ( $filter == 'next_week' ) {
        $where .= "AND (" . EVENT_SCHEDULE . ".event_date <= '".$next_week_end."' AND " . EVENT_SCHEDULE . ".event_enddate >='" . $next_week_start."') ";
    }

    if ( $filter == 'week_after_next' ) {
        $where .= "AND (" . EVENT_SCHEDULE . ".event_date <= '".$week_after_next_end."' AND " . EVENT_SCHEDULE . ".event_enddate >='" . $week_after_next_start."') ";
    }
    return $where;
}
add_filter('geodir_event_listing_filter_where', 'whoop_geodir_event_loop_filter_where', 10, 2);