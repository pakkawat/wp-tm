<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("BP_Whoop_Friends_Widget");' ) );
class BP_Whoop_Friends_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'A list of recently active, popular, and newest Friends of the displayed member.  Widget is only shown when viewing a member profile.', GEODIRECTORY_FRAMEWORK ),
            'classname' => 'widget_bp_whoop_friends_widget',
        );
        parent::__construct( false, $name = _x( 'Whoop > Friends', 'widget name', GEODIRECTORY_FRAMEWORK ), $widget_ops );

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget( $args, $instance ) {
        extract( $args );

        if ( ! bp_displayed_user_id() ) {
            return;
        }

        $user_id = bp_displayed_user_id();
        //$friend_count = friends_get_friend_count_for_user($user_id);
        $friend_count  = whoop_get_friend_count_for_user($user_id);
        if($friend_count > 1) {
            $wid_title = sprintf( __( '%s Friends', GEODIRECTORY_FRAMEWORK ), $friend_count );
        } else {
            $wid_title = sprintf( __( '%s Friend', GEODIRECTORY_FRAMEWORK ), $friend_count );
        }
        $link = trailingslashit( bp_displayed_user_domain() . bp_get_friends_slug() );
        $instance['title'] = $wid_title;

        if ( empty( $instance['friend_default'] ) ) {
            $instance['friend_default'] = 'active';
        }

        /**
         * Filters the Friends widget title.
         *
         * @since BuddyPress (1.8.0)
         *
         * @param string $title The widget title.
         */
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;

        $title = $instance['link_title'] ? '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a>' : esc_html( $title );

        if($friend_count > 0) {
            echo $before_title . $title . $after_title;
        }

        $members_args = array(
            'user_id'         => absint( $user_id ),
            'type'            => sanitize_text_field( $instance['friend_default'] ),
            'max'             => absint( $instance['max_friends'] ),
            'populate_extras' => 1,
        );

        ?>

        <?php if ( bp_has_members( $members_args ) ) : ?>
            <ul id="members-list" class="item-list">
                <?php while ( bp_members() ) : bp_the_member(); ?>
                    <li>
                        <div class="item-avatar">
                            <a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
                            <div class="whoop-friend-review-count">
                                <span class="whoop-friend-count"><i class="fa fa-users"></i> <?php echo whoop_get_friend_count_for_user(bp_get_member_user_id()); //echo friends_get_friend_count_for_user( bp_get_member_user_id()); ?></span>
                                <span class="whoop-review-count"><i class="fa fa-star"></i> <?php $count = geodir_get_review_count_by_user_id(bp_get_member_user_id()); if($count) { echo $count; } else { echo "0";}?></span>
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-title">
                                <a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
                            </div>
                        </div>
                    </li>

                <?php endwhile; ?>
            </ul>
            <?php
            if ($friend_count > absint( $instance['max_friends'] )) { ?>
                <a href="<?php echo $link ?>" class="whoop-btn whoop-more-btn">More &#187;</a>
            <?php }
            ?>
        <?php else: ?>

            <div class="whoop-no-friends">
                <p><i class="fa fa-users"></i></p>
                <p><?php echo bp_get_displayed_user_displayname().' '.__( 'has no friends.', GEODIRECTORY_FRAMEWORK ); ?></p>
            </div>

        <?php endif; ?>

        <?php echo $after_widget; ?>
    <?php
    }

    /**
     * Process a widget save.
     *
     * @param array $new_instance The parameters saved by the user.
     * @param array $old_instance The parameters as previously saved to the database.
     * @return array $instance The processed settings to save.
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['max_friends']    = absint( $new_instance['max_friends'] );
        $instance['friend_default'] = sanitize_text_field( $new_instance['friend_default'] );
        $instance['link_title']	    = (bool) $new_instance['link_title'];

        return $instance;
    }

    /**
     * Render the widget edit form.
     *
     * @param array $instance The saved widget settings.
     * @return string|void
     */
    function form( $instance ) {
        $defaults = array(
            'max_friends' 	 => 4,
            'friend_default' => 'active',
            'link_title' 	 => false
        );
        $instance = wp_parse_args( (array) $instance, $defaults );

        $max_friends 	= $instance['max_friends'];
        $friend_default = $instance['friend_default'];
        $link_title	= (bool) $instance['link_title'];
        ?>

        <p><label for="<?php echo $this->get_field_name( 'link_title' ) ?>"><input type="checkbox" name="<?php echo $this->get_field_name('link_title') ?>" value="1" <?php checked( $link_title ) ?> /> <?php _e( 'Link widget title to Members directory', GEODIRECTORY_FRAMEWORK ) ?></label></p>

        <p><label for="bp-core-widget-friends-max"><?php _e( 'Max friends to show:', GEODIRECTORY_FRAMEWORK ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_friends' ); ?>" name="<?php echo $this->get_field_name( 'max_friends' ); ?>" type="text" value="<?php echo absint( $max_friends ); ?>" style="width: 30%" /></label></p>

        <p>
            <label for="bp-core-widget-friends-default"><?php _e( 'Default friends to show:', GEODIRECTORY_FRAMEWORK ); ?>
                <select name="<?php echo $this->get_field_name( 'friend_default' ) ?>">
                    <option value="newest" <?php selected( $friend_default, 'newest' ); ?>><?php _e( 'Newest', GEODIRECTORY_FRAMEWORK ) ?></option>
                    <option value="active" <?php selected( $friend_default, 'active' );?>><?php _e( 'Active', GEODIRECTORY_FRAMEWORK ) ?></option>
                    <option value="popular"  <?php selected( $friend_default, 'popular' ); ?>><?php _e( 'Popular', GEODIRECTORY_FRAMEWORK ) ?></option>
                </select>
            </label>
        </p>

    <?php
    }
}