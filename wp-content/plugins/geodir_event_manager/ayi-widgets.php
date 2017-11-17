<?php
// register AYI widget
add_action( 'widgets_init', 'geodir_register_ayi_widget' );
function geodir_register_ayi_widget() {
    register_widget( 'Geodir_AYI_Widget' );
}

class Geodir_AYI_Widget extends WP_Widget
{
    /**
     * Class constructor.
     */
    function __construct()
    {
        $widget_ops = array(
            'description' => __('Displays "Are you interested?" widget in event pages', 'geodir-ayi'),
            'classname' => 'widget_are_you_interested',
        );
        parent::__construct(false, $name = _x('GD > Are you interested?', 'widget name', 'geodir-ayi'), $widget_ops);

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget($args, $instance)
    {
        echo geodir_ayi_widget_display($args, $instance);
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
        $instance = wp_parse_args((array)$instance, array('title' => __('Are You Interested?', 'geodir-ayi')));
        $title = strip_tags($instance['title']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __("Widget Title:", 'geodir-ayi'); ?>
                <input class="widefat"
                      id="<?php echo $this->get_field_id('title'); ?>"
                      name="<?php echo $this->get_field_name('title'); ?>"
                      type="text"
                      value="<?php echo esc_attr($title); ?>"/>
            </label>
        </p>
        <?php
    }

}
