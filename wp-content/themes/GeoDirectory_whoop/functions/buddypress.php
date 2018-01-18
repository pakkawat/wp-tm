<?php
/**
 * -----------------------------------------------------------------------------
 * whoop theme functions
 * -----------------------------------------------------------------------------
 */
function whoop_bp_get_members_pagination_count() {
    global $members_template;

    if ( empty( $members_template->type ) )
        $members_template->type = '';

    $start_num = intval( ( $members_template->pag_page - 1 ) * $members_template->pag_num ) + 1;
    $from_num  = bp_core_number_format( $start_num );
    $to_num    = bp_core_number_format( ( $start_num + ( $members_template->pag_num - 1 ) > $members_template->total_member_count ) ? $members_template->total_member_count : $start_num + ( $members_template->pag_num - 1 ) );
    $total     = bp_core_number_format( $members_template->total_member_count );

    $pag = sprintf( _n( '1 of 1', '%1$s to %2$s of %3$s', $members_template->total_member_count, 'buddypress' ), $from_num, $to_num, $total );

    return $pag;
}
add_filter( 'bp_members_pagination_count', 'whoop_bp_get_members_pagination_count');

function whoop_bp_get_members_pagination_links() {
    global $members_template;
    if($members_template->pag_links) {
        $html = '<span class="whoop-pagination-text">'.__('Go to Page', GEODIRECTORY_FRAMEWORK).'</span>';
        $html .= $members_template->pag_links;
    } else {
        $html = $members_template->pag_links;
    }
    return $html;
}
add_filter( 'bp_get_members_pagination_links', 'whoop_bp_get_members_pagination_links');

if (!defined('BP_DEFAULT_COMPONENT')) {
    define( 'BP_DEFAULT_COMPONENT', 'profile' );
}
function whoop_remove_activity_from_profile(){
    global $gdf;
    if(!empty($gdf)) {
        if ($gdf['whoop-bp-activiy-tab']) {
            bp_core_remove_nav_item('activity');
        }
        if ($gdf['whoop-bp-review-tab']) {
            bp_core_remove_nav_item('reviews');
        }
        if ($gdf['whoop-bp-bookmarks-tab']) {
            bp_core_remove_nav_item('favorites');
        }
        //bp_core_remove_subnav_item( 'profile', 'edit' );
    }
}
add_action('bp_setup_nav', 'whoop_remove_activity_from_profile', 15);

function whoop_bp_move_submenu() {
    global $bp;
    if ( ! bp_is_active( 'settings' ) ) {
        return;
    }

    $user_domain = whoop_bp_get_user_domain();

    // Get the settings slug
    $settings_slug = bp_get_settings_slug();

    bp_core_new_subnav_item( array(
        'name'            => _x( 'Profile Info', 'Profile Info sub nav', GEODIRECTORY_FRAMEWORK ),
        'slug'            => 'edit',
        'parent_url'      => trailingslashit( $user_domain . $settings_slug ),
        'parent_slug'     => $settings_slug,
        'screen_function' => 'whoop_xprofile_screen_edit_profile',
        'position'        => 20,
        'user_has_access' => bp_core_can_edit_settings()
    ) );

    bp_core_new_subnav_item( array(
        'name'            => _x( 'Change Avatar', 'Profile header sub menu', GEODIRECTORY_FRAMEWORK ),
        'slug'            => 'change-avatar',
        'parent_url'      => trailingslashit( $user_domain . $settings_slug ),
        'parent_slug'     => $settings_slug,
        'screen_function' => 'whoop_xprofile_screen_change_avatar',
        'position'        => 30,
        'user_has_access' => bp_core_can_edit_settings()
    ) );
}
add_action('bp_setup_nav', 'whoop_bp_move_submenu', 16);

function whoop_xprofile_screen_change_avatar() {
    add_action( 'bp_template_content', 'whoop_change_profile_picture_screen_content' );
    bp_core_load_template( apply_filters( 'xprofile_template_change_avatar', 'members/single/plugins' ) );
    xprofile_screen_change_avatar();

}

function whoop_change_profile_picture_screen_content() {
    bp_get_template_part( 'members/single/profile/change-avatar' );
}
function whoop_xprofile_screen_edit_profile() {
    add_action( 'bp_template_content', 'whoop_change_xprofile_screen_edit_profile' );
    bp_core_load_template( apply_filters( 'xprofile_template_display_profile', 'members/single/plugins' ) );
    xprofile_screen_edit_profile();

}

function whoop_change_xprofile_screen_edit_profile() {
    bp_get_template_part( 'members/single/profile/edit' );
}
//This filter disables buddypress auto links in profile text
function whoop_remove_xprofile_links() {
    remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );
}
add_action( 'bp_init', 'whoop_remove_xprofile_links' );

function whoop_check_friendship() {
    global $bp;
    if (!is_user_logged_in())
        return;
    if ($bp->loggedin_user->id == $bp->displayed_user->id)
        return;
    $is_friend = friends_check_friendship($bp->loggedin_user->id, $bp->displayed_user->id);

    $loggedin_user_url = '<a href="'.bp_core_get_userlink($bp->loggedin_user->id, false, true).'">'.__( 'You', GEODIRECTORY_FRAMEWORK ).'</a>';
    $displayed_user_url = '<a href="'.bp_core_get_userlink($bp->displayed_user->id, false, true).'">'.whoop_bp_member_name(bp_get_displayed_user_displayname()).'</a>';

    echo '<p class="whoop-check-friendship">';
    echo "<span>";
    if (!$is_friend) {
        echo '<i class="fa fa-user"></i> ';
        echo $loggedin_user_url.' '.__('are not closely connected to', GEODIRECTORY_FRAMEWORK).' '.$displayed_user_url;
    } else {
        echo '<i class="fa fa-user"></i> ';
        echo $loggedin_user_url.' &rarr; <i class="fa fa-users"></i> '.$displayed_user_url;
    }
    echo "</span>";
    echo "</p>";
}

function whoop_modify_bp_buttons($contents, $args, $button) {
    if ($args['component'] == 'friends' && $args['id'] == 'pending') {
        $icon = '<i class="fa fa-minus-square"></i> ';
    } elseif ($args['component'] == 'friends' && $args['id'] == 'is_friend') {
        $icon = '<i class="fa fa-minus-square"></i> ';
    } elseif ($args['component'] == 'friends' && $args['id'] == 'not_friends') {
        $icon = '<i class="fa fa-plus-square"></i> ';
    } elseif ($args['component'] == 'messages' && $args['id'] == 'private_message') {
        $icon = '<i class="fa fa-comment"></i> ';
    } elseif ($args['component'] == 'activity' && $args['id'] == 'public_message') {
        $icon = '<i class="fa fa-comment"></i> ';
    } elseif ($args['component'] == 'follow' && $args['id'] == 'following') {
        $icon = '<i class="fa fa-heart"></i> ';
    } elseif ($args['component'] == 'follow' && $args['id'] == 'not-following') {
        $icon = '<i class="fa fa-heart"></i> ';
    } elseif ($args['component'] == 'compliments' && $args['id'] == 'compliments') {
        $icon = '<i class="fa fa-trophy"></i> ';
    } else {
        $icon = '';
    }
    $args['link_text'] = $icon.$args['link_text'];
    $button = new BP_Button( $args );
    return $button->contents;
}
add_filter('bp_get_button', 'whoop_modify_bp_buttons', 10, 3);

function whoop_remove_public_message_button() {
    remove_filter( 'bp_member_header_actions','bp_send_public_message_button', 20);
}
add_action( 'bp_member_header_actions', 'whoop_remove_public_message_button' );

function whoop_bp_sidebar() {
    if (bp_is_user_profile() && bp_current_action() == "public") {
        get_sidebar('bp-details');
    }
}
add_action('bp_whoop_sidebar', 'whoop_bp_sidebar');

function whoop_bp_title() {
    global $bp;
    if (bp_is_user_friends()) {
        ?>
        <div class="whoop-review-header">
            <div class="whoop-title-and-count">
                <h3 class="whoop-tab-title">
                    <?php
                    if ($bp->loggedin_user->id == $bp->displayed_user->id) {
                        echo __('My Friends', GEODIRECTORY_FRAMEWORK);
                    } else {
                        echo whoop_bp_member_name(bp_get_displayed_user_displayname()) . __('\'s Friends', GEODIRECTORY_FRAMEWORK);
                    }
                    ?>
                </h3>
                <?php if( bp_is_active( 'friends' ) ) { ?>
                    <p><?php //echo friends_get_friend_count_for_user( bp_displayed_user_id());
                        echo whoop_get_friend_count_for_user(bp_displayed_user_id());?> <?php echo __('Friends', GEODIRECTORY_FRAMEWORK); ?></p>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}
add_action('bp_before_member_body', 'whoop_bp_title');

function whoop_bp_recent_activity($args) {
    $defaults = array(
        'object'                    => apply_filters('whoop_bp_recent_activity_objects', array('friends','whoopreviews','whoopbookmarks','compliments')),
        'per_page'                  => 5,
        'page'                      => 1,
        'scope'                     => '',
        'max'                       => 20,
        'show_avatar'               => 'yes',
        'show_filters'              => 'yes',
        'included'                  => false,
        'excluded'                  => false,
        'is_personal'               => 'no',
        'is_blog_admin_activity'    => 'no',
        'show_post_form'            => 'no'
    );

    $args = wp_parse_args( $args, $defaults );
    extract( $args );
    global $bp;
    $primary_id = null;
    ?>
    <?php
    $activity_args = array(
        'object' => $object,
        'max' => $max,
        'page' => $page,
        'per_page' => $per_page,
        'primary_id' => $primary_id,
        'scope' => $scope,
        'show_avatar' => $show_avatar
    );
    if ( bp_has_activities($activity_args) ) : ?>
        <?php while ( bp_activities() ) : bp_the_activity(); ?>
            <?php whoop_activity_entry($args);?>
        <?php endwhile; ?>
        <div id="whoop_ra_load_more">
            <p id="whoop_recent_activity_loading"><i class="fa fa-cog fa-spin"></i></p>
            <a href="javascript:;" class="whoop-load-more" data-page="<?php echo $page; ?>" data-scope="<?php echo $scope; ?>">
                <span><?php echo __('See more recent activity', GEODIRECTORY_FRAMEWORK); ?></span>
            </a>
            <?php whoop_recent_activity_js(); ?>
        </div>
    <?php else: ?>
        <div class="widget-error">
            <?php if( $scope == 'just-me' )
                $error = __( 'We don\'t have any recent activity of you right now.', GEODIRECTORY_FRAMEWORK );
            elseif( $scope == 'friends' )
                $error = __( 'We don\'t have any recent activity of your friends right now', GEODIRECTORY_FRAMEWORK );
            else
                $error = __( 'We don\'t have any recent activity right now', GEODIRECTORY_FRAMEWORK );
            ?>
            <?php echo $error; ?>
        </div>
    <?php endif;?>
    <?php
}

function whoop_activity_entry($args) {
    $args = wp_parse_args( $args );
    extract( $args );
    $allow_comment = false;
    ?>
    <li class="clearfix">
        <span class="geodir_reviewer_image">
            <a href="<?php bp_activity_user_link() ?>">
                <?php bp_activity_avatar( 'type=thumb&width=60&height=60' ) ?>
            </a>
        </span>
        <span class="geodir_reviewer_content whoop-r-activity-inner">
                <div class="whoop-r-activity-title">
                    <?php bp_activity_action() ?>
                </div>
                <div class="whoop-r-activity-body">
                    <?php bp_activity_content_body() ?>
                </div>
        </span>
    </li>
<?php }

function whoop_bp_event_update_activity($args) {
    $defaults = array(
        'object'                    => array('whoopevents'),
        'per_page'                  => 5,
        'page'                      => 1,
        'scope'                     => '',
        'max'                       => 20,
        'show_avatar'               => 'yes',
        'show_filters'              => 'yes',
        'included'                  => false,
        'excluded'                  => false,
        'is_personal'               => 'no',
        'is_blog_admin_activity'    => 'no',
        'show_post_form'            => 'no'
    );

    $args = wp_parse_args( $args, $defaults );
    extract( $args );
    global $bp;
    $primary_id = null;
    ?>
    <?php
    $activity_args = array(
        'object' => $object,
        'max' => $max,
        'page' => $page,
        'per_page' => $per_page,
        'primary_id' => $primary_id,
        'scope' => $scope,
        'show_avatar' => $show_avatar
    );
    if ( bp_has_activities($activity_args) ) : ?>
        <?php while ( bp_activities() ) : bp_the_activity(); ?>
            <?php whoop_event_update_activity_entry($args);?>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="widget-error">
            <?php
            $error = __( 'We don\'t have any event updates right now', GEODIRECTORY_FRAMEWORK );
            ?>
            <?php echo $error; ?>
        </div>
    <?php endif;?>
    <?php
}

function whoop_event_update_activity_entry($args) {
    $args = wp_parse_args( $args );
    extract( $args );
    ?>
    <li class="clearfix">
        <span class="geodir_reviewer_image">
            <a href="<?php bp_activity_user_link() ?>">
                <?php bp_activity_avatar( 'type=thumb&width=40&height=40' ) ?>
            </a>
        </span>
        <span class="geodir_reviewer_content whoop-r-activity-inner">
                <div class="whoop-r-activity-title">
                    <?php bp_activity_action() ?>
                </div>
        </span>
    </li>
<?php }

function whoop_event_updates($post_id, $user_id, $type, $add_or_remove) {
    if ($user_id && $post_id) {
        if ($add_or_remove == 'add') {
            $user = get_user_by('id', $user_id);
            $user_link = get_author_posts_url( $user_id );
            $name = whoop_bp_member_name(whoop_get_current_user_name($user));
            if ($type == 'event_rsvp_yes') {
                $action = '<a href="'.$user_link.'">'.$name.'</a> '.__('is attending', GEODIRECTORY_FRAMEWORK).' <a href="'.get_the_permalink($post_id).'">'.get_the_title($post_id).'</a>';
            } else {
                $action = '<a href="'.$user_link.'">'.$name.'</a> '.__('says "Sounds Cool" for', GEODIRECTORY_FRAMEWORK).' <a href="'.get_the_permalink($post_id).'">'.get_the_title($post_id).'</a>';
            }
            $args = array(
                'action' => $action,
                'component' => 'whoopevents',
                'type'  => 'event_interested',
                'user_id' => $user_id,
                'secondary_item_id' => $post_id
            );
            bp_activity_add( $args );
        } elseif ($add_or_remove == 'remove') {
            $args = array(
                'component' => 'whoopevents',
                'type'  => 'event_interested',
                'user_id' => $user_id,
                'secondary_item_id' => $post_id
            );
            bp_activity_delete( $args );
        }
    }
}
if ( bp_is_active( 'activity' ) ) {
    add_action('ayi_interested_update', 'whoop_event_updates', 10, 4);
}

function whoop_bp_activity_content_filter($content, $activity = null) {
    if (isset($activity->component) && isset($activity->type) && $activity->component == 'whoopreviews' && $activity->type == 'wrote_a_review') {
        $comment_id = $activity->item_id;
        $status = wp_get_comment_status( $comment_id );
        if ($status == 'approved') {
            $content = '<section class="comment-content comment">';
            $content .= get_comment_text($comment_id);
            $content .= '</section>';
        }
        return $content;
    } else {
        return $content;
    }
}
add_filter('bp_get_activity_content_body', 'whoop_bp_activity_content_filter', 10, 2);

/*
 * Reviews
 */
function whoop_bp_user_recent_reviews_content()
{
    global $bp;
    $user_id = $bp->displayed_user->id;
    geodir_get_reviews_by_user_id($user_id);
}

add_action('whoop_bp_before_content', 'whoop_bp_user_recent_reviews_content');

function whoop_modify_author_url($link, $author_id) {
    return bp_core_get_user_domain( $author_id );
}
//This breaks gd my listing links.
//add_filter('author_link', 'whoop_modify_author_url', 10, 2);

function whoop_bp_recent_activity_objects_filter($objects) {
    $objects[] = 'blogs';
    return $objects;
}
//add_filter('whoop_bp_recent_activity_objects', 'whoop_bp_recent_activity_objects_filter', 10);
//widgets
if( bp_is_active( 'friends' )) {
    include_once(get_template_directory() .'/whoop-widgets/buddypress/bp-friends-widget.php');
}
if ( bp_is_active( 'activity' ) ) {
    include_once(get_template_directory() .'/whoop-widgets/buddypress/bp-recent-activity.php');
    include_once(get_template_directory() .'/whoop-widgets/buddypress/bp-event-updates.php');
}

function whoop_configure_buddypress() {
    whoop_bp_custom_fields(true);
}
add_action( 'after_switch_theme', 'whoop_configure_buddypress' );

// Hook for add custom fields
add_action( 'admin_init', 'whoop_bp_custom_fields' );
function whoop_bp_custom_fields($switch=false) {
    //allow only for admin
    if ( !current_user_can( 'manage_options' ) ) {
        return;
    }

    $action = false;
    if ($switch == true) {
        $action = 'switch';
    } elseif (isset($_GET['xaction'])) {
        if ($_GET['xaction'] == 'xprofile-fields-delete') {
            delete_option( 'whoop_xfields_created');
            $action = 'delete';
        } elseif ($_GET['xaction'] == 'xprofile-fields-create') {
            delete_option( 'whoop_xfields_created');
            $action = 'create';
        }
    }

    if(!$action) {
        return;
    }

    $xfields_created = get_option( 'whoop_xfields_created');

    if (!$xfields_created) {

        $your_headline_id = xprofile_get_field_id_from_name('Your Headline');
        $i_love_id = xprofile_get_field_id_from_name('I Love');
        $find_me_in_id = xprofile_get_field_id_from_name('Find Me In');
        $my_hometown_id = xprofile_get_field_id_from_name('My Hometown');
        $my_blog_or_website_id = xprofile_get_field_id_from_name('My Blog Or Website');
        $why_you_should_id = xprofile_get_field_id_from_name('Why You Should Read My Reviews');
        $my_second_fav_id = xprofile_get_field_id_from_name('My Second Favorite Website');
        $great_book_id = xprofile_get_field_id_from_name('The Last Great Book I Read');
        $my_first_concert_id = xprofile_get_field_id_from_name('My First Concert');
        $my_fav_movie_id = xprofile_get_field_id_from_name('My Favorite Movie');
        $my_last_meal_id = xprofile_get_field_id_from_name('My Last Meal On Earth');
        $dont_tell_id = xprofile_get_field_id_from_name('Dont Tell Anyone Else But');
        $most_recent_id = xprofile_get_field_id_from_name('Most Recent Discovery');
        $current_crush_id = xprofile_get_field_id_from_name('Current Crush');

        if ($action == 'delete') {

            if ($your_headline_id) {
                xprofile_delete_field($your_headline_id);
            }

            if ($i_love_id) {
                xprofile_delete_field($i_love_id);
            }

            if ($find_me_in_id) {
                xprofile_delete_field($find_me_in_id);
            }

            if ($my_hometown_id) {
                xprofile_delete_field($my_hometown_id);
            }

            if ($my_blog_or_website_id) {
                xprofile_delete_field($my_blog_or_website_id);
            }

            if ($why_you_should_id) {
                xprofile_delete_field($why_you_should_id);
            }

            if ($my_second_fav_id) {
                xprofile_delete_field($my_second_fav_id);
            }

            if ($great_book_id) {
                xprofile_delete_field($great_book_id);
            }

            if ($my_first_concert_id) {
                xprofile_delete_field($my_first_concert_id);
            }

            if ($my_fav_movie_id) {
                xprofile_delete_field($my_fav_movie_id);
            }

            if ($my_last_meal_id) {
                xprofile_delete_field($my_last_meal_id);
            }

            if ($dont_tell_id) {
                xprofile_delete_field($dont_tell_id);
            }

            if ($most_recent_id) {
                xprofile_delete_field($most_recent_id);
            }

            if ($current_crush_id) {
                xprofile_delete_field($current_crush_id);
            }

            update_option('whoop_xfields_created', 'yes');

            $query_args = array('page' => 'bp-profile-setup');
            $link = add_query_arg($query_args, admin_url('/users.php'));
            wp_redirect($link);

        }

        if ($action == 'create' OR $action == 'switch') {
            if (!$your_headline_id) {
                $name_field_args = array(
                    'field_id' => $your_headline_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'Your Headline',
                    'description' => __('America\'s Next Top Singer, Don\'t you wish your girlfriend was Elite like me?', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$i_love_id) {
                $name_field_args = array(
                    'field_id' => $i_love_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textarea',
                    'name' => 'I Love',
                    'description' => __('Comma separated phrases (e.g. sushi, Radiohead, puppies)', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$find_me_in_id) {
                $name_field_args = array(
                    'field_id' => $find_me_in_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'Find Me In',
                    'description' => __('Greenwich Village, Nob Hill, or short pants', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$my_hometown_id) {
                $name_field_args = array(
                    'field_id' => $my_hometown_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'My Hometown',
                    'description' => __('Schenectady, NY', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$my_blog_or_website_id) {
                $name_field_args = array(
                    'field_id' => $my_blog_or_website_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'My Blog Or Website',
                    'description' => __('www.someblog.wordpress.com', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$why_you_should_id) {
                $name_field_args = array(
                    'field_id' => $why_you_should_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'Why You Should Read My Reviews',
                    'description' => __('I go out 7 times a week, sometimes 8; I\'m the king of the world!', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$my_second_fav_id) {
                $name_field_args = array(
                    'field_id' => $my_second_fav_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'My Second Favorite Website',
                    'description' => __('www.flickr.com, www.pets.com', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$great_book_id) {
                $name_field_args = array(
                    'field_id' => $great_book_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'The Last Great Book I Read',
                    'description' => __('Stephen Colbert\'s I Am America, Whatever Oprah tells me', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$my_first_concert_id) {
                $name_field_args = array(
                    'field_id' => $my_first_concert_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'My First Concert',
                    'description' => __('Duran Duran, Vanilla Ice, Wang Chung', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$my_fav_movie_id) {
                $name_field_args = array(
                    'field_id' => $my_fav_movie_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'My Favorite Movie',
                    'description' => __('Eat Drink Man Woman, Harold & Kumar Go to White Castle', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$my_last_meal_id) {
                $name_field_args = array(
                    'field_id' => $my_last_meal_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'My Last Meal On Earth',
                    'description' => __('French Laundry, My mom\'s meatloaf, A tub of fried chicken', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$dont_tell_id) {
                $name_field_args = array(
                    'field_id' => $dont_tell_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'Dont Tell Anyone Else But',
                    'description' => __('I love Starbucks; My mom nominated me for the Elite Squad', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$most_recent_id) {
                $name_field_args = array(
                    'field_id' => $most_recent_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'Most Recent Discovery',
                    'description' => __('Shoegazer Music, In-N-Out Animal Style', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }

            if (!$current_crush_id) {
                $name_field_args = array(
                    'field_id' => $current_crush_id,
                    'field_group_id' => 1,
                    'can_delete' => 1,
                    'type' => 'textbox',
                    'name' => 'Current Crush',
                    'description' => __('Hayden Panettiere, Jessie\'s Girl', GEODIRECTORY_FRAMEWORK),
                );
                xprofile_insert_field($name_field_args);
            }
        }

        update_option('whoop_xfields_created', 'yes');

        if ($action == 'create') {
            $query_args = array('page' => 'bp-profile-setup');
            $link = add_query_arg($query_args, admin_url('/users.php'));
            wp_redirect($link);
        }
    }
}

add_filter('gdlists_bp_screen_function', 'modify_gdlists_bp_screen_function');
function modify_gdlists_bp_screen_function($fname) {
    return "whoop_bp_user_lists";
}

function whoop_bp_user_lists()
{
    add_action('bp_template_content', 'whoop_bp_user_lists_content');
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function whoop_bp_user_lists_content()
{
    global $bp;
    $user_id = $bp->displayed_user->id;
    ?>
    <div class="whoop-bookmark-header">
        <?php
        if(isset($_GET['list_id'])) {
            $pid = (int)sanitize_text_field(esc_sql($_GET['list_id']));
            $title = get_the_title($pid);
            $content = strip_tags(get_post_field('post_content', $pid));
            $user_link = bp_core_get_user_domain($user_id);
            ?>
            <h3 class="whoop-tab-title" style="float: none;">
                <?php echo $title; ?>
                <?php
                $list_post = get_post($pid);
                $list_author = $list_post->post_author;
                if ((get_current_user_id() == $list_author) || (current_user_can('edit_post', $pid))) { ?>
                    <a style="font-size: 10px;" href="<?php echo add_query_arg('edit-list', '1', get_permalink($pid)); ?>">
                        <?php echo __('Edit Items', GEODIRECTORY_FRAMEWORK); ?>
                    </a>
                <?php } ?>
                <a href="<?php echo $user_link; ?>lists/" class="see-all-lists"><?php echo __('See All Lists', GEODIRECTORY_FRAMEWORK); ?></a>
            </h3>
            <?php if ($content) { ?>
                <p class="whoop-list-desc" style="border-bottom: 1px solid #e5e5e1;padding-bottom: 10px;margin-bottom: 10px;">
                    <?php echo $content; ?>
                </p>
            <?php } ?>
            <?php gdlist_get_listings($pid); ?>
            <?php
        } else {
            ?>
            <h3 class="whoop-tab-title" style="float: none;line-height: 30px;">
                <?php echo __('Lists', 'geodirlists'); ?>
                <?php if ($user_id == get_current_user_id()) { ?>
                    <a href="<?php echo home_url('/add-list/'); ?>" class="whoop-btn whoop-btn-small whoop-btn-primary gd-list-view-btn"><?php echo __('Create List', 'geodirlists'); ?></a>
                <?php } ?>
            </h3>
            <?php
        }
        ?>
    </div>
    <?php
    whoop_geodir_get_lists_by_user_id($user_id);
}

function whoop_geodir_get_lists_by_user_id($user_id = 0) {
    if(isset($_GET['list_id'])) {
        $pid = (int) sanitize_text_field(esc_sql($_GET['list_id']));
        $listed_posts = gdlist_get_all_listed_posts($pid);
        $post_ids = array();
        foreach($listed_posts as $key => $lp) {
            $post_ids[] = $key;
        }
        if ($post_ids) {
            //gdlists_geodir_get_reviews_by_user_id($user_id, false, $post_ids);
        } else { ?>
            <div class="whoop-no-events whoop-no-lists">
                <p>
                    <i class="fa fa-list"></i>
                    <?php echo __('Sorry, no list items just yet.', 'geodirlists'); ?>
                </p>

            </div>
        <?php }
    } else {
        $query_args = array(
            'posts_per_page' => 100,
            'post_type' => 'gd_list',
            'author' => $user_id
        );
        $lists = new WP_Query($query_args);
        if ($lists) {
            ?>
            <ul class="whoop-gd-list-content">
                <?php
                while ( $lists->have_posts() ) : $lists->the_post();
                    gdlist_single_loop_item();
                endwhile;
                wp_reset_postdata();
                ?>
            </ul>
            <?php
        } else { ?>
            <div class="whoop-no-events whoop-no-lists">
                <p>
                    <i class="fa fa-list"></i>
                    <?php echo __('Sorry, no lists just yet.', 'geodirlists'); ?>
                </p>

            </div>
        <?php }
    }
}

//add_action('init', 'remove_gd_lists_title_filter');
//function remove_gd_lists_title_filter() {
//    var_dump(is_singular('gd_list'));
//    if (!is_singular('gd_list')) {
//        remove_filter( 'the_title', 'add_edit_links_in_gdlists_title', 10, 2);
//    }
//}
add_filter('enable_list_title_filter_in_bp_page', '__return_false');
