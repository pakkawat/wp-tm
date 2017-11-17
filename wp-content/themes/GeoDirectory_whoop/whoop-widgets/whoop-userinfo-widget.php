<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("BP_Whoop_UserInfo_Widget");' ) );
class BP_Whoop_UserInfo_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'Displays user information when logged in.', GEODIRECTORY_FRAMEWORK ),
            'classname' => 'widget_bp_whoop_userinfo_widget',
        );
        parent::__construct( false, $name = _x( 'Whoop > User Info', 'widget name', GEODIRECTORY_FRAMEWORK ), $widget_ops );

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget( $args, $instance ) {
        extract( $args );
        if ( ! get_current_user_id() ) {
            return;
        }
        $current_user = wp_get_current_user();
        if ( class_exists( 'BuddyPress' ) ) {
            $user_link = bp_get_loggedin_user_link();
        } else {
            $user_link = get_author_posts_url( $current_user->ID );
        }

        echo $before_widget;
        ?>
        <div class="whoop-userinfo-widget-inner">
            <div class="whoop-userinfo-widget-top">
                <div class="whoop-dd-menu-group">
                    <div class="whoop-account-info">
                        <div class="whoop-media-avatar">
                            <div class="whoop-photo-box">
                                <a href="<?php echo $user_link ?>">
                                    <?php echo get_avatar( $current_user->ID, 60 ); ?>
                                </a>
                            </div>
                        </div>
                        <div class="whoop-media-info">
                            <ul class="whoop-user-info">
                                <li class="whoop-user-name">
                                    <a href="<?php echo $user_link ?>">
                                        <?php echo whoop_bp_member_name(whoop_get_current_user_name($current_user)); ?>
                                    </a>

                                </li>
                                <li class="user-location">
                                    <b><?php echo whoop_get_user_location($current_user->ID); ?></b>
                                </li>
                            </ul>
                            <ul class="user-account-stats">
                                <?php if ( class_exists( 'BuddyPress' ) && bp_is_active( 'friends' ) ) { ?>
                                <li class="whoop-friend-count">
                                    <i class="fa fa-users"></i>
                                    <?php //echo friends_get_friend_count_for_user( $current_user->ID );
                                    echo whoop_get_friend_count_for_user($current_user->ID);?>
                                </li>
                                <?php } ?>
                                <li class="whoop-review-count">
                                    <i class="fa fa-star"></i>
                                    <?php //$count = geodir_get_review_count_by_user_id($current_user->ID );
                                    $count = geodir_get_review_count_by_user_id($current_user->ID);
                                    if($count) {
                                        echo $count;
                                    } else {
                                        echo "0";
                                    }?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="whoop-userinfo-widget-bottom">

            </div>
        </div>
        <?php echo $after_widget; ?>
    <?php
    }

}