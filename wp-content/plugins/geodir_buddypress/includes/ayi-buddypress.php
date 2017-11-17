<?php
// BuddyPress functions
// Member profile Events tab
add_action('bp_setup_nav', 'geodir_ayi_user_events_nav_adder');
function geodir_ayi_user_events_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    $name = __('Events', 'gdbuddypress');

    $screen_function = apply_filters('geodir_ayi_bp_screen_function', 'geodir_ayi_bp_user_events');

    bp_core_new_nav_item(
        array(
            'name' => $name,
            'slug' => 'events',
            'position' => 50,
            'show_for_displayed_user' => true,
            'screen_function' => $screen_function,
            'item_css_id' => 'events',
            'default_subnav_slug' => 'public'
        ));
}

function geodir_ayi_bp_user_events()
{
    add_action('bp_template_content', 'geodir_ayi_bp_user_events_content');
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function geodir_ayi_bp_user_events_content() {
    if (isset($_GET['ayi_interested'])) {
        $event_id = esc_sql(strip_tags($_GET['ayi_interested']));
        if (is_numeric($event_id)) {
            if (isset($_GET['ayi_type']) && $_GET['ayi_type'] == 'maybe') {
                geodir_ayi_bp_events_interested_users($event_id, 'maybe');
            } else {
                geodir_ayi_bp_events_interested_users($event_id, 'yes');
            }
        }
    } else {
        geodir_ayi_bp_user_events_main_content();
    }
}

function geodir_ayi_bp_user_events_main_content()
{
    global $bp;
    $user_id = $bp->displayed_user->id;
    $ayi_paged = ( isset($_GET['ayi_paged']) && $_GET['ayi_paged'] ) ? absint( $_GET['ayi_paged'] ) : 1;
    $paged_url = add_query_arg(
        array(
            'ayi_paged' => '%#%',
        ),
        geodir_ayi_get_user_profile_link($bp->displayed_user->id)."events"
    );
    $number = apply_filters('ayi_events_interested_items_number', 2);
    ?>
    <div class="geodir-ayi-review-header geodir-ayi-event-header-wrap">
        <div class="geodir-ayi-title-and-count">
            <h3 class="geodir-ayi-tab-title"><?php
                $el_type  = isset( $_GET['el_type'] ) ? sanitize_text_field( $_GET['el_type'] ) : 'attending';
                if ($el_type == 'past_events') {
                    echo __('Events Attended', 'gdbuddypress');
                } elseif ($el_type == 'events_submitted') {
                    echo __('Events Submitted', 'gdbuddypress');
                } elseif ($el_type == 'sounds_cool') {
                    echo __('Events Sounds Cool', 'gdbuddypress');
                } elseif ($el_type == 'attending') {
                    echo __('Events Attending', 'gdbuddypress');
                }
                ?></h3>
            <div class="geodir-ayi-tabs" role="navigation">
                <ul>
                    <li class="<?php echo ($el_type == 'past_events') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'past_events'), geodir_curPageURL())); ?>"><?php echo __('Past Events', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($el_type == 'events_submitted') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'events_submitted'), geodir_curPageURL())); ?>"><?php echo __('Events Submitted', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($el_type == 'sounds_cool') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'sounds_cool'), geodir_curPageURL())); ?>"><?php echo __('Sounds Cool', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($el_type == 'attending') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'attending'), geodir_curPageURL())); ?>"><?php echo __('Attending', 'gdbuddypress') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
    $listings = null;
    $query_args = array(
        'posts_per_page' => $number,
        'is_geodir_loop' => true,
        'gd_location' 	 => false,
        'post_type' => 'gd_event',
        'paged' => $ayi_paged,
    );
    if($el_type == 'sounds_cool') {
        $post_ids = get_user_meta($user_id, 'event_rsvp_maybe', true);
        if ($post_ids) {
            $post_ids = array_keys($post_ids);
            $query_args['post__in'] = $post_ids;
            $listings = query_posts($query_args);
        } else {
            $listings = false;
        }
    } elseif($el_type == 'attending') {
        $post_ids = get_user_meta($user_id, 'event_rsvp_yes', true);
        if ($post_ids) {
            $post_ids = array_keys($post_ids);
            $query_args['post__in'] = $post_ids;
            $listings = query_posts($query_args);
        } else {
            $listings = false;
        }
    } elseif($el_type == 'past_events') {
        $maybe_post_ids = get_user_meta($user_id, 'event_rsvp_maybe', true);
        $yes_post_ids = get_user_meta($user_id, 'event_rsvp_yes', true);
        if ($maybe_post_ids && $yes_post_ids) {
            $post_ids = array_merge($maybe_post_ids, $yes_post_ids);
            $post_ids = array_keys($post_ids);
        } elseif ($maybe_post_ids) {
            $post_ids = array_keys($maybe_post_ids);
        } elseif ($yes_post_ids) {
            $post_ids = array_keys($yes_post_ids);
        } else {
            $post_ids = false;
        }
        if ($post_ids) {
            $query_args['post__in'] = $post_ids;
            $query_args['geodir_event_listing_filter'] = 'past';
            global $gdevents_widget;
            $old_gdevents_widget = $gdevents_widget;
            $gdevents_widget = true;
            $listings = query_posts($query_args);
            $gdevents_widget = $old_gdevents_widget;
        } else {
            $listings = false;
        }
    } else {
        $listings = query_posts($query_args);
    }
    global $wp_query;
    $total_items = $wp_query->found_posts;
    $total = ceil($total_items/$number);
    if ($listings) { ?>
        <ul class="geodir-ayi-events-content">
            <?php foreach ($listings as $post) {
                $post = geodir_get_post_info($post->ID);
                geodir_ayi_event_list_content_from_post($post);
            }
            ?>
        </ul>
        <?php
    } else { ?>
        <div class="geodir-ayi-no-events">
            <p><?php echo __('Sorry, no events just yet.', 'gdbuddypress'); ?></p>
            <p><i class="fa fa-calendar"></i></p>
            <p><a href="<?php echo site_url(); ?>/events/" class="geodir-ayi-btn geodir-ayi-btn-primary"><?php echo __('Find Events', 'gdbuddypress'); ?></a></p>
        </div>
    <?php }
    wp_reset_query();
    ?>
    <div class="ayi-pagination">
        <?php
        $translated = __( 'Page', 'gdbuddypress' ); // Supply translatable string
        $base_url = $paged_url;
        echo paginate_links( array(
            'base' => $base_url,
            'current' => max( 1, $ayi_paged ),
            'total' => $total,
            'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
            'type' => 'list'
        ) );
        ?>
    </div>
    <?php
}

function geodir_ayi_bp_events_interested_users($event_id, $type) {

    global $bp;

    $ayi_paged = ( isset($_GET['ayi_paged']) && $_GET['ayi_paged'] ) ? absint( $_GET['ayi_paged'] ) : 1;

    $interested_url = add_query_arg(
        array(
            'ayi_interested' => $event_id,
            'ayi_paged' => '%#%',
        ),
        geodir_ayi_get_user_profile_link($bp->displayed_user->id)."events"
    );

    $number = apply_filters('ayi_events_interested_users_number', 10);
    $offset = ( $ayi_paged - 1 ) * $number;

    $end = $number + $offset;

    $gde = isset( $_GET['gde'] ) ? strip_tags($_GET['gde']) : false;

    if ($type == 'maybe') {
        $yes_users = get_post_meta($event_id, 'event_rsvp_maybe', true);
        if ($gde) {
            $yes_users = isset( $yes_users[$gde] ) ? $yes_users[$gde] : false;
        } else {
            foreach ($yes_users as $key => $value) {
                if (is_string($key)) {
                    unset($yes_users[$key]);
                }
            }
        }
        if (empty($yes_users)) {
            $yes_users = array();
        }
        $users = $yes_users;
        $total_user = count($users);
    } else {
        // yes
        $maybe_users = get_post_meta($event_id, 'event_rsvp_yes', true);
        if ($gde) {
            $maybe_users = isset( $maybe_users[$gde] ) ? $maybe_users[$gde] : false;
        } else {
            foreach ($maybe_users as $key => $value) {
                if (is_string($key)) {
                    unset($maybe_users[$key]);
                }
            }
        }
        if (empty($maybe_users)) {
            $maybe_users = array();
        }
        $users = $maybe_users;
        $total_user = count($users);
    }

    $total = ceil($total_user/$number);
    ?>
    <div class="geodir-ayi-event-header-wrap">
        <div class="geodir-ayi-title-and-count">
            <h3 class="geodir-ayi-tab-title">
                <?php
                if ($type == 'maybe') {
                    echo __('Sounds Cool', 'gdbuddypress');
                } else {
                    echo __('Attending', 'gdbuddypress');
                }
                ?>
            </h3>
            <div class="geodir-ayi-tabs" role="navigation">
                <ul>
                    <li class="<?php echo ($type == 'yes') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('ayi_type' => 'yes'), geodir_curPageURL())); ?>"><?php echo __('Attending', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($type == 'maybe') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('ayi_type' => 'maybe'), geodir_curPageURL())); ?>"><?php echo __('Sounds Cool', 'gdbuddypress') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <ul class="ayi-interested-users">
        <?php
        if ($users) {
            $count = 0;
            foreach ($users as $key => $value) {
                $count++;
                if ($count <= $offset) {
                    continue;
                }

                if ($count > $end) {
                    continue;
                }
                $user = get_user_by('id', $value);
                ?>
                <li class="ayi-interested-users-user">
                    <div class="ayi-interested-users-user-left">
                        <div class="ayi-interested-users-user-avatar">
                            <a href="<?php echo get_author_posts_url($user->ID); ?>">
                                <?php echo get_avatar( $user->user_email, 128 ); ?>
                            </a>
                        </div>
                    </div>
                    <div class="ayi-interested-users-user-right">
                        <div class="ayi-interested-users-user-name">
                            <h3><a href="<?php echo get_author_posts_url($user->ID); ?>"><?php echo $user->display_name; ?></a></h3>
                        </div>
                        <div class="ayi-interested-users-user-type">
                            <?php
                            if ($type == 'maybe') {
                                echo __('Sounds Cool', 'gdbuddypress');
                            } else {
                                echo __('Attending', 'gdbuddypress');
                            }
                            ?>
                        </div>
                    </div>
                </li>
                <?php
            }
        } else {
            // no users found
            echo '<div class="ayi-interested-users-error">';
            echo __('No users found', 'gdbuddypress');
            echo '</div>';
        }
        ?>
    </ul>

    <div class="ayi-pagination">
        <?php
        $translated = __( 'Page', 'gdbuddypress' ); // Supply translatable string

        $base_url = $interested_url ;
        
        echo paginate_links( array(
            'base' => $base_url,
            'current' => max( 1, $ayi_paged ),
            'total' => $total,
            'add_args' => true,
            'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
            'type' => 'list'
        ) );
        ?>
    </div>
    <?php
}

function geodir_ayi_bp_user_events_main_content_dev()
{
    global $bp;
    $user_id = $bp->displayed_user->id;

    $ayi_paged = ( isset($_GET['ayi_paged']) && $_GET['ayi_paged'] ) ? absint( $_GET['ayi_paged'] ) : 1;

    $paged_url = add_query_arg(
        array(
            'ayi_paged' => '%#%',
        ),
        geodir_ayi_get_user_profile_link($bp->displayed_user->id)."events"
    );

    $number = apply_filters('ayi_events_interested_items_number', 2);

    ?>
    <div class="geodir-ayi-review-header geodir-ayi-event-header-wrap">
        <div class="geodir-ayi-title-and-count">
            <h3 class="geodir-ayi-tab-title"><?php
                $el_type  = isset( $_GET['el_type'] ) ? sanitize_text_field( $_GET['el_type'] ) : 'attending';
                if ($el_type == 'past_events') {
                    echo __('Events Attended', 'gdbuddypress');
                } elseif ($el_type == 'events_submitted') {
                    echo __('Events Submitted', 'gdbuddypress');
                } elseif ($el_type == 'sounds_cool') {
                    echo __('Events Sounds Cool', 'gdbuddypress');
                } elseif ($el_type == 'attending') {
                    echo __('Events Attending', 'gdbuddypress');
                }
                ?></h3>
            <div class="geodir-ayi-tabs" role="navigation">
                <ul>
                    <li class="<?php echo ($el_type == 'past_events') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'past_events'), geodir_curPageURL())); ?>"><?php echo __('Past Events', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($el_type == 'events_submitted') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'events_submitted'), geodir_curPageURL())); ?>"><?php echo __('Events Submitted', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($el_type == 'sounds_cool') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'sounds_cool'), geodir_curPageURL())); ?>"><?php echo __('Sounds Cool', 'gdbuddypress') ?></a></li>
                    <li class="<?php echo ($el_type == 'attending') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('el_type' => 'attending'), geodir_curPageURL())); ?>"><?php echo __('Attending', 'gdbuddypress') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
    $listings = null;
    $query_args = array(
        'posts_per_page' => $number,
        'is_geodir_loop' => true,
        'gd_location' 	 => false,
        'post_type' => 'gd_event',
        'paged' => $ayi_paged,
    );

    if($el_type == 'sounds_cool') {
        $post_ids = get_user_meta($user_id, 'event_rsvp_maybe', true);
        if ($post_ids) {
            $post_ids = array_keys($post_ids);
            $query_args['post__in'] = $post_ids;
            $listings = query_posts($query_args);
        } else {
            $listings = false;
        }
    } elseif($el_type == 'attending') {
        $post_ids = get_user_meta($user_id, 'event_rsvp_yes', true);
        if ($post_ids) {
            $post_ids = array_keys($post_ids);
            $query_args['post__in'] = $post_ids;
            $listings = query_posts($query_args);
        } else {
            $listings = false;
        }

    } elseif($el_type == 'past_events') {
        $maybe_post_ids = get_user_meta($user_id, 'event_rsvp_maybe', true);
        $yes_post_ids = get_user_meta($user_id, 'event_rsvp_yes', true);

        if ($maybe_post_ids && $yes_post_ids) {
            $post_ids = array_merge($maybe_post_ids, $yes_post_ids);
            $post_ids = array_keys($post_ids);
        } elseif ($maybe_post_ids) {
            $post_ids = array_keys($maybe_post_ids);
        } elseif ($yes_post_ids) {
            $post_ids = array_keys($yes_post_ids);
        } else {
            $post_ids = false;
        }

        if ($post_ids) {
            $query_args['post__in'] = $post_ids;
            $query_args['geodir_event_listing_filter'] = 'past';
            global $gdevents_widget;
            $old_gdevents_widget = $gdevents_widget;
            $gdevents_widget = true;
            $listings = query_posts($query_args);
            $gdevents_widget = $old_gdevents_widget;
        } else {
            $listings = false;
        }
    } else {
        $listings = query_posts($query_args);
    }

    $global_keys = array();
    $key_count = 0;
    foreach ($listings as $post) {
        $yes_users = get_post_meta($post->ID, 'event_rsvp_maybe', true);
        if (!is_array($yes_users)) {
            $yes_users = array();
        }
        $keys = array();
        foreach ($yes_users as $key => $value) {
            if ($value == $user_id) {
                $keys[] = $key;
            }
        }
        $key_count = $key_count + count($keys);
        $global_keys[$post->ID] = $keys;

//        if (empty($keys)) {
//            continue;
//        }
    }


//    global $wp_query;
//    $total_items = $wp_query->found_posts;
//    $total = ceil($total_items/$number);

    $total = ceil($key_count/$number);
    if ($global_keys) { ?>
        <ul class="geodir-ayi-events-content">
            <?php
            foreach ($global_keys as $event_id => $value) {
                if (is_array($value) && !empty($value)) {
                    $p = geodir_get_post_info($event_id);
                    foreach ($value as $k => $v) {
                        if (is_string($v)) {
                            //gde
                        } else {
                            geodir_ayi_event_list_content_from_post($p);
                        }
                    }
                }
            }
            ?>
        </ul>
        <?php
    } else { ?>
        <div class="geodir-ayi-no-events">
            <p><?php echo __('Sorry, no events just yet.', 'gdbuddypress'); ?></p>
            <p><i class="fa fa-calendar"></i></p>
            <p><a href="<?php echo site_url(); ?>/events/" class="geodir-ayi-btn geodir-ayi-btn-primary"><?php echo __('Find Events', 'gdbuddypress'); ?></a></p>
        </div>
    <?php }
    wp_reset_query();
    ?>
    <div class="ayi-pagination">
        <?php
        $translated = __( 'Page', 'gdbuddypress' ); // Supply translatable string

        $base_url = $paged_url;

        echo paginate_links( array(
            'base' => $base_url,
            'current' => max( 1, $ayi_paged ),
            'total' => $total,
            'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
            'type' => 'list'
        ) );
        ?>
    </div>
    <?php

}