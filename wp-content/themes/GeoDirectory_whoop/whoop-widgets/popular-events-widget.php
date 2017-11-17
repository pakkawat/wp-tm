<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("Geodir_Popular_Events_Widget");' ) );
class Geodir_Popular_Events_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'Displays "Popular Events" widget in event pages', GEODIRECTORY_FRAMEWORK ),
            'classname' => 'widget_popular_events',
        );
        parent::__construct( false, $name = _x( 'Whoop > Popular & Latest Events', 'widget name', GEODIRECTORY_FRAMEWORK ), $widget_ops );

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget( $args, $instance ) {
        extract( $args );

        $title = empty($instance['title']) ? __('Popular Events', GEODIRECTORY_FRAMEWORK) : apply_filters('pop_events_widget_title', __($instance['title'], GEODIRECTORY_FRAMEWORK));
        $post_limit = empty($instance['post_limit']) ? '5' : apply_filters('events_widget_post_limit', $instance['post_limit']);
        $order_by = empty($instance['order_by']) ? 'rsvp_count' : apply_filters('events_order_by', $instance['order_by']);
        $etype  = isset( $_GET['etype'] ) ? sanitize_text_field( $_GET['etype'] ) : 'all';

        echo $before_widget;
        ?>
        <div class="whoop_pe_widget_header">
        <?php if ($title) {
            echo $before_title . $title . $after_title;
        } ?>
            <a class="whoop-btn whoop-btn-small whoop-btn-more whoop_pe_browse_btn" href="<?php echo get_post_type_archive_link('gd_event'); ?>"><?php echo __('Browse All Events', GEODIRECTORY_FRAMEWORK) ?> &raquo;</a>

            <?php if ($order_by == 'rsvp_count' && $args['id'] == 'event-index-content') { ?>
            <div class="efilter whoop-tabs">
                <ul>
                    <li><?php echo __('See Events For:', GEODIRECTORY_FRAMEWORK) ?></li>
                    <li class="<?php echo ($etype == 'all') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'all'), geodir_curPageURL())); ?>"><?php echo __('All Events', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($etype == 'today') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'today'), geodir_curPageURL())); ?>"><?php echo __('Today', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($etype == 'upcoming') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'upcoming'), geodir_curPageURL())); ?>"><?php echo __('Upcoming', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($etype == 'past') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'past'), geodir_curPageURL())); ?>"><?php echo __('Past', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($etype == 'this_week') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'this_week'), geodir_curPageURL())); ?>"><?php echo __('This Week', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($etype == 'next_week') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'next_week'), geodir_curPageURL())); ?>"><?php echo __('Next Week', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($etype == 'week_after_next') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('etype' => 'week_after_next'), geodir_curPageURL())); ?>"><?php echo __('Week After Next', GEODIRECTORY_FRAMEWORK) ?></a></li>
                </ul>
            </div>
            <?php } ?>
        </div>
        <?php
        $query_args = array(
            'posts_per_page' => $post_limit,
            'is_geodir_loop' => true,
            'order_by' => $order_by,
            'post_type' => 'gd_event'
        );
        if ($args['id'] != 'event-index-content') {
            $etype = 'all';
            $etype = apply_filters('whoop_pop_event_widget_type', $etype);
        }
        if ($order_by == 'rsvp_count') {
            $query_args['geodir_event_listing_filter'] = $etype;
        } else {
            $query_args['geodir_event_listing_filter'] = 'all';
            $query_args['order_by'] = 'latest';
        }
        $events = query_posts( $query_args );
        if ($events) {
            ?>
            <ul class="whoop-events-content">
                <?php
                foreach ($events as $postt) {
                    global $post;
                    $old_post = $post;
                    $post = $postt;
                    event_list_content_from_post($post);
                    $post = $old_post;
                }
                ?>
            </ul>
            <?php if (get_post_type() != 'gd_event') { ?>
            <a class="whoop-wid-link-more" href="<?php echo esc_url(add_query_arg(array('e_index' => 'true'), get_post_type_archive_link('gd_event'))); ?>"><?php echo __( 'More Events', GEODIRECTORY_FRAMEWORK );?></a>
            <?php } ?>
            <?php
        } else {
            ?>
            <div class="widget-error">
                <?php
                    $error = __( "We don't have any events right now", GEODIRECTORY_FRAMEWORK );
                ?>
                <?php echo $error; ?>
            </div>
        <?php
        }
        wp_reset_query();
        ?>
        <?php echo $after_widget; ?>
    <?php
    }

    function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['post_limit'] = strip_tags($new_instance['post_limit']);
        $instance['order_by'] = strip_tags($new_instance['order_by']);
        return $instance;
    }

    function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => __('Popular Events', GEODIRECTORY_FRAMEWORK), 'post_limit' => '5', 'order_by' => 'rsvp_count'));
        $title = strip_tags($instance['title']);
        $post_limit = strip_tags($instance['post_limit']);
        $order_by = strip_tags($instance['order_by']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __("Widget Title:", GEODIRECTORY_FRAMEWORK); ?> <input class="widefat"
                                                                                         id="<?php echo $this->get_field_id('title'); ?>"
                                                                                         name="<?php echo $this->get_field_name('title'); ?>"
                                                                                         type="text"
                                                                                         value="<?php echo esc_attr($title); ?>"/></label>
        </p>
        <p>

            <label
                for="<?php echo $this->get_field_id('post_limit'); ?>"><?php _e('Number of events to display:', GEODIRECTORY_FRAMEWORK);?>

                <input class="widefat" id="<?php echo $this->get_field_id('post_limit'); ?>"
                       name="<?php echo $this->get_field_name('post_limit'); ?>" type="text"
                       value="<?php echo esc_attr($post_limit); ?>"/>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Widget Type:', GEODIRECTORY_FRAMEWORK ); ?></label>
            <select name="<?php echo $this->get_field_name( 'order_by' ); ?>" id="<?php echo $this->get_field_name( 'order_by' ); ?>">
                <option <?php selected( $order_by, 'date' );   ?> value="date"><?php _e( 'Latest Events', GEODIRECTORY_FRAMEWORK ); ?></option>
                <option <?php selected( $order_by, 'rsvp_count' );   ?> value="rsvp_count"><?php _e( 'Popular Events', GEODIRECTORY_FRAMEWORK ); ?></option>
            </select>
        </p>
    <?php
    }

}