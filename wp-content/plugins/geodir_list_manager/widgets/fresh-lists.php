<?php
class Geodir_Fresh_Lists_Widget extends WP_Widget
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $widget_ops = array(
            'description' => __('Displays "Fresh Lists" widget', 'geodirlists'),
            'classname' => 'widget_fresh_lists',
        );
        parent::__construct(false, $name = _x('GD List > Fresh Lists', 'widget name', 'geodirlists'), $widget_ops);

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    public function widget($args, $instance)
    {
        extract($args);

        $title = empty($instance['title']) ? 'Fresh Lists' : apply_filters('fresh_lists_widget_title', __($instance['title'], 'geodirlists'));
        $post_limit = empty($instance['post_limit']) ? '3' : apply_filters('lists_widget_post_limit', $instance['post_limit']);

        $query_args = array(
            'posts_per_page' => $post_limit,
            'post_type' => 'gd_list',
            'orderby' => 'modified'
        );
        $lists = new WP_Query($query_args);

        echo $before_widget;

        if ($title) {
            echo $before_title . $title . $after_title;
        } ?>

        <?php if ($lists->have_posts()) { ?>
        <ul class="widget_today_in_talk">
            <?php while ($lists->have_posts()) :
                $lists->the_post();
                global $post;
                $author_link = get_avatar($post->post_author, 60);
            if (class_exists('BuddyPress')) {
                $permalink = esc_url(add_query_arg(array('list_id' => $post->ID), bp_core_get_user_domain($post->post_author).'lists/'));
            } else {
                $permalink = get_the_permalink();
            }
           ?>
            <li>
                <div class="event-content-box">
                    <div class="event-content-avatar">
                        <div class="event-content-avatar-inner">
                            <a href="<?php echo $permalink; ?>">
                                <?php echo $author_link; ?>
                            </a>
                        </div>
                    </div>
                    <div class="event-content-body">
                        <div class="event-content-body-top">
                            <div class="event-title">
                                <a class="bbp-forum-title" href="<?php echo $permalink; ?>">
                                    <?php echo get_the_title(); ?>
                                </a>
                                <div class="event-date">
                                    <?php echo wp_trim_words(stripcslashes(strip_tags(get_the_content())), 10); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <?php endwhile; ?>
        </ul>
        <a class="whoop-wid-link-more" href="<?php echo home_url('/lists/'); ?>">
            <?php echo __('More Lists', 'geodirlists'); ?>
        </a>
        <?php } else { ?>
            <div class="widget-error">
                <?php _e( 'We don\'t have any lists right now', 'geodirlists' );?>
            </div>
        <?php 
        }
        echo $after_widget;

        // Reset the $post global
        wp_reset_postdata();
    }

    public function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['post_limit'] = strip_tags($new_instance['post_limit']);
        return $instance;
    }

    public function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => 'Fresh Lists', 'post_limit' => '3'));
        $title = strip_tags($instance['title']);
        $post_limit = strip_tags($instance['post_limit']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat"
                                                                                         id="<?php echo $this->get_field_id('title'); ?>"
                                                                                         name="<?php echo $this->get_field_name('title'); ?>"
                                                                                         type="text"
                                                                                         value="<?php echo esc_attr($title); ?>"/></label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('post_limit'); ?>"><?php _e('Number of lists to display:', 'geodirlists');?>
                <input class="widefat" id="<?php echo $this->get_field_id('post_limit'); ?>"
                       name="<?php echo $this->get_field_name('post_limit'); ?>" type="text"
                       value="<?php echo esc_attr($post_limit); ?>"/>
            </label>
        </p>
    <?php
    }
}

add_action('widgets_init', 'geodir_list_register_widgets');
function geodir_list_register_widgets() {
    register_widget('Geodir_Fresh_Lists_Widget');
}
