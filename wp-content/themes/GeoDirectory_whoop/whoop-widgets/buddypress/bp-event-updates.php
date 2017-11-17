<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("BP_Whoop_Event_Updates_Widget");' ) );
class BP_Whoop_Event_Updates_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'Display event updates in event page like Yelp', GEODIRECTORY_FRAMEWORK ),
            'classname' => 'widget_bp_whoop_eu_widget',
        );
        parent::__construct( false, $name = _x( 'Whoop > Event Updates', 'widget name', GEODIRECTORY_FRAMEWORK ), $widget_ops );

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget( $args, $instance ) {
        extract( $args );

        $wid_title = __( 'Event Updates', GEODIRECTORY_FRAMEWORK );
        $instance['title'] = $wid_title;
        $title = apply_filters( 'whoop_eu_widget_title', $instance['title'] );


        echo $before_widget;
        $title = esc_html( $title );
        ?>
        <div class="whoop_ra_widget_header">
            <?php
            echo $before_title . $title . $after_title;
            ?>
        </div>
        <ul class="geodir_recent_reviews">
            <?php
            $act_args = array();
            whoop_bp_event_update_activity($act_args);
            ?>
        </ul>
        <?php echo $after_widget; ?>
    <?php
    }

    function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => 'Event Updates'));
        $title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat"
                                                                                         id="<?php echo $this->get_field_id('title'); ?>"
                                                                                         name="<?php echo $this->get_field_name('title'); ?>"
                                                                                         type="text"
                                                                                         value="<?php echo esc_attr($title); ?>"/></label>
        </p>
    <?php
    }
}