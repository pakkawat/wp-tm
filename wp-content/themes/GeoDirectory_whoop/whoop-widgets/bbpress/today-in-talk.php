<?php
add_action( 'bbp_widgets_init', array( 'BBP_TodayInTalk_Widget',  'register_widget' ), 10 );
/**
 * bbPress Topic Widget
 *
 * Adds a widget which displays the topic list
 *
 * @since bbPress (r2653)
 *
 * @uses WP_Widget
 */
class BBP_TodayInTalk_Widget extends WP_Widget {

    /**
     * bbPress Topic Widget
     *
     * Registers the topic widget
     *
     * @since bbPress (r2653)
     *
     * @uses apply_filters() Calls 'BBP_TodayInTalk_Widget_options' with the
     *                        widget options
     */
    public function __construct() {
        $widget_ops = apply_filters( 'bbp_todayintalk_widget_options', array(
            'classname'   => 'widget_today_in_talk',
            'description' => __( 'A list of recent topics, sorted by popularity or freshness.', GEODIRECTORY_FRAMEWORK )
        ) );

        parent::__construct( false, __( 'Whoop > Today in Talk', GEODIRECTORY_FRAMEWORK ), $widget_ops );
    }

    /**
     * Register the widget
     *
     * @since bbPress (r3389)
     *
     * @uses register_widget()
     */
    public static function register_widget() {
        register_widget( 'BBP_TodayInTalk_Widget' );
    }

    /**
     * Displays the output, the topic list
     *
     * @since bbPress (r2653)
     *
     * @param mixed $args
     * @param array $instance
     * @uses apply_filters() Calls 'bbp_topic_widget_title' with the title
     * @uses bbp_topic_permalink() To display the topic permalink
     * @uses bbp_topic_title() To display the topic title
     * @uses bbp_get_topic_last_active_time() To get the topic last active
     *                                         time
     * @uses bbp_get_topic_id() To get the topic id
     */
    public function widget( $args = array(), $instance = array() ) {

        // Get widget settings
        $settings = $this->parse_settings( $instance );

        // Typical WordPress filter
        $settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

        // bbPress filter
        $settings['title'] = apply_filters( 'bbp_topic_widget_title', $settings['title'], $instance, $this->id_base );

        // How do we want to order our results?
        switch ( $settings['order_by'] ) {

            // Order by most recent replies
            case 'freshness' :
                $topics_query = array(
                    'post_type'           => bbp_get_topic_post_type(),
                    'post_parent'         => $settings['parent_forum'],
                    'posts_per_page'      => (int) $settings['max_shown'],
                    'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
                    'ignore_sticky_posts' => true,
                    'no_found_rows'       => true,
                    'meta_key'            => '_bbp_last_active_time',
                    'orderby'             => 'meta_value',
                    'order'               => 'DESC',
                );
                break;

            // Order by total number of replies
            case 'popular' :
                $topics_query = array(
                    'post_type'           => bbp_get_topic_post_type(),
                    'post_parent'         => $settings['parent_forum'],
                    'posts_per_page'      => (int) $settings['max_shown'],
                    'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
                    'ignore_sticky_posts' => true,
                    'no_found_rows'       => true,
                    'meta_key'            => '_bbp_reply_count',
                    'orderby'             => 'meta_value',
                    'order'               => 'DESC'
                );
                break;

            // Order by which topic was created most recently
            case 'newness' :
            default :
                $topics_query = array(
                    'post_type'           => bbp_get_topic_post_type(),
                    'post_parent'         => $settings['parent_forum'],
                    'posts_per_page'      => (int) $settings['max_shown'],
                    'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
                    'ignore_sticky_posts' => true,
                    'no_found_rows'       => true,
                    'order'               => 'DESC'
                );
                break;
        }

        // Note: private and hidden forums will be excluded via the
        // bbp_pre_get_posts_normalize_forum_visibility action and function.
        $widget_query = new WP_Query( $topics_query );

        // Bail if no topics are found
        if ( ! $widget_query->have_posts() ) {
            return;
        }

        echo $args['before_widget'];

        if ( !empty( $settings['title'] ) ) {
            echo $args['before_title'] . $settings['title'] . $args['after_title'];
        } ?>

        <ul>

            <?php while ( $widget_query->have_posts() ) :

                $widget_query->the_post();
                $topic_id    = bbp_get_topic_id( $widget_query->post->ID );
                $forum_id = bbp_get_topic_forum_id( $topic_id );
                $author_link =  bbp_get_topic_author_avatar( $topic_id, 60 );
                ?>

                <li>
                    <div class="event-content-box">
                        <div class="event-content-avatar">
                            <div class="event-content-avatar-inner">
                                <a href="<?php bbp_topic_permalink( $topic_id ); ?>">
                                    <?php echo $author_link; ?>
                                </a>
                            </div>
                        </div>
                        <div class="event-content-body">
                            <div class="event-content-body-top">
                                <div class="event-title">
                                    <a class="bbp-forum-title" href="<?php bbp_topic_permalink( $topic_id ); ?>"><?php bbp_topic_title( $topic_id ); ?></a>
                                    <div class="event-date">
                                        <?php bbp_forum_title($forum_id); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="event-content-body-bottom">

                                <div class="event-interested">
                                    <?php bbp_topic_last_active_time( $topic_id ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

            <?php endwhile; ?>

        </ul>

        <a class="whoop-wid-link-more" href="<?php echo home_url('/forums/'); ?>"><?php echo __( 'More Talk', GEODIRECTORY_FRAMEWORK );?></a>

        <?php echo $args['after_widget'];

        // Reset the $post global
        wp_reset_postdata();
    }

    /**
     * Update the topic widget options
     *
     * @since bbPress (r2653)
     *
     * @param array $new_instance The new instance options
     * @param array $old_instance The old instance options
     */
    public function update( $new_instance = array(), $old_instance = array() ) {
        $instance                 = $old_instance;
        $instance['title']        = strip_tags( $new_instance['title'] );
        $instance['order_by']     = strip_tags( $new_instance['order_by'] );
        $instance['parent_forum'] = sanitize_text_field( $new_instance['parent_forum'] );
        $instance['max_shown']    = (int) $new_instance['max_shown'];

        // Force to any
        if ( !empty( $instance['parent_forum'] ) && !is_numeric( $instance['parent_forum'] ) ) {
            $instance['parent_forum'] = 'any';
        }

        return $instance;
    }

    /**
     * Output the topic widget options form
     *
     * @since bbPress (r2653)
     *
     * @param $instance Instance
     * @uses BBP_TodayInTalk_Widget::get_field_id() To output the field id
     * @uses BBP_TodayInTalk_Widget::get_field_name() To output the field name
     */
    public function form( $instance = array() ) {

        // Get widget settings
        $settings = $this->parse_settings( $instance ); ?>

        <p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Title:',                  GEODIRECTORY_FRAMEWORK ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximum topics to show:', GEODIRECTORY_FRAMEWORK ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>

        <p>
            <label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'Parent Forum ID:', GEODIRECTORY_FRAMEWORK ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
            </label>

            <br />

            <small><?php _e( '"0" to show only root - "any" to show all', GEODIRECTORY_FRAMEWORK ); ?></small>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order By:',        GEODIRECTORY_FRAMEWORK ); ?></label>
            <select name="<?php echo $this->get_field_name( 'order_by' ); ?>" id="<?php echo $this->get_field_name( 'order_by' ); ?>">
                <option <?php selected( $settings['order_by'], 'newness' );   ?> value="newness"><?php _e( 'Newest Topics',                GEODIRECTORY_FRAMEWORK ); ?></option>
                <option <?php selected( $settings['order_by'], 'popular' );   ?> value="popular"><?php _e( 'Popular Topics',               GEODIRECTORY_FRAMEWORK ); ?></option>
                <option <?php selected( $settings['order_by'], 'freshness' ); ?> value="freshness"><?php _e( 'Topics With Recent Replies', GEODIRECTORY_FRAMEWORK ); ?></option>
            </select>
        </p>

    <?php
    }

    /**
     * Merge the widget settings into defaults array.
     *
     * @since bbPress (r4802)
     *
     * @param $instance Instance
     * @uses bbp_parse_args() To merge widget options into defaults
     */
    public function parse_settings( $instance = array() ) {
        return bbp_parse_args( $instance, array(
            'title'        => __( 'Today in Talk', GEODIRECTORY_FRAMEWORK ),
            'max_shown'    => 5,
            'parent_forum' => 'any',
            'order_by'     => false
        ), 'todayintalk_widget_settings' );
    }
}