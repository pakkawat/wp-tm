<?php
function geodir_get_reviews_by_user_id($user_id = 0, $recent_only = true, $post_ids = array())
{
    global $wpdb, $tablecomments, $tableposts;
    $tablecomments = $wpdb->comments;
    $tableposts = $wpdb->posts;
    $review_table = GEODIR_REVIEW_TABLE;

    // Determine user to use
    $user_domain = whoop_bp_get_user_domain();
    $keyword = isset($_GET['sk']) ? sanitize_text_field($_GET['sk']) : null;

    if ($keyword) {
        $where = $wpdb->prepare("WHERE r.post_title LIKE %s AND r.post_status=%d AND r.status=%d AND r.overall_rating>%d AND r.user_id=%d ", array('%' . $keyword . '%', 1, 1, 0, $user_id));
    } else {
        $where = $wpdb->prepare("WHERE r.post_status=%d AND r.status=%d AND r.overall_rating>%d AND r.user_id=%d ", array(1, 1, 0, $user_id));
    }
    if (!empty($post_ids)) {
        $postids_str = esc_sql(implode(',', $post_ids));
        $where .= "AND r.post_id in ({$postids_str}) ";
    }
    $total_query = "SELECT COUNT(*) FROM $review_table as r $where";
    $query = "SELECT r.id as ID, r.post_type, r.comment_id as comment_ID, r.post_date as comment_date,r.overall_rating, r.user_id, r.post_id FROM $review_table as r $where";
    $total = $wpdb->get_var($total_query);
    $items_per_page = 10;
    $page = isset($_GET['rpage']) ? abs((int)strip_tags(esc_sql($_GET['rpage']))) : 1;
    $offset = ($page * $items_per_page) - $items_per_page;

    $order_by = " ORDER BY r.post_date LIMIT ${offset}, ${items_per_page}";
    $comments = $wpdb->get_results($query . $order_by);

    if ($comments OR $keyword) {
        ?>
        <?php if (!$post_ids) { ?>
            <div class="whoop-review-header">
                <div class="whoop-title-and-count">
                    <?php if ($keyword) { ?>
                        <h3 class="whoop-tab-title"><?php echo __('Search Reviews', GEODIRECTORY_FRAMEWORK); ?></h3>
                    <?php } elseif ($recent_only) { ?>
                        <h3 class="whoop-tab-title"><?php echo __('Recent Reviews', GEODIRECTORY_FRAMEWORK); ?></h3>
                    <?php } else { ?>
                        <h3 class="whoop-tab-title"><?php echo __('All Reviews', GEODIRECTORY_FRAMEWORK); ?></h3>
                    <?php } ?>
                    <?php if (!$keyword) { ?>
                        <p>
                            <?php echo geodir_get_review_count_by_user_id($user_id) . ' ' . __('Reviews', GEODIRECTORY_FRAMEWORK); ?>
                        </p>
                    <?php } ?>
                    <?php if ($keyword && !$comments) { ?>
                        <p>
                            <?php echo __('There were no results for', GEODIRECTORY_FRAMEWORK) . ' <b>' . $keyword . '.</b>'; ?>
                        </p>
                    <?php } ?>
                </div>
                <div class="review-search">
                    <form action="<?php echo $user_domain; ?>reviews/" method="get" id="search-message-form">
                        <label for="reviews_search" class="bp-screen-reader-text"><?php echo __('Search Reviews', GEODIRECTORY_FRAMEWORK) ?></label>
                        <input type="text" name="sk" id="reviews_search"
                               placeholder="<?php echo $keyword ? $keyword : __('Search Reviews...', GEODIRECTORY_FRAMEWORK); ?>"
                               style="background-image: none; background-position: 0% 0%; background-repeat: repeat;">
                        <input type="submit" class="button" id="reviews_search_submit" value="<?php echo __('Search', GEODIRECTORY_FRAMEWORK); ?>">
                    </form>

                </div>
            </div>
        <?php } ?>
        <?php $start = $offset ? $offset : 1;
        $end = $offset + $items_per_page;
        $end = ($end > $total) ? $total : $end;
        ?>
        <?php if ($total > $items_per_page) { ?>
            <?php if ($recent_only) { ?>
            <?php } else { ?>
                <div id="pag-top" class="pagination">
                    <div class="pag-count" id="member-dir-count-top">
                        <?php echo sprintf(_n('1 of 1', '%1$s to %2$s of %3$s', $total, GEODIRECTORY_FRAMEWORK), $start, $end, $total); ?>
                    </div>
                    <div class="pagination-links">
                        <span class="whoop-pagination-text">Go to Page</span>
                        <?php
                        echo paginate_links(array(
                            'base' => esc_url(add_query_arg('rpage', '%#%')),
                            'format' => '',
                            'prev_next' => false,
                            'total' => ceil($total / $items_per_page),
                            'current' => $page
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
        <ul class="whoop-member-profile-reviews">
            <?php
            foreach ($comments as $comment) {
                ?>
                <li <?php @comment_class('geodir-comment', $comment->comment_ID); ?>
                    id="li-comment-<?php echo $comment->comment_ID; ?>">
                    <article id="comment-<?php echo $comment->comment_ID; ?>" class="comment hreview">
                        <header class="comment-meta comment-author vcard">
                            <?php
                            $post = get_post($comment->post_id);
                            if ($fimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' )) {
                                ?>
                                <a href="<?php echo get_the_permalink($post->ID); ?>">
                                    <div class="geodir_thumbnail" style="background-image:url(<?php echo $fimage[0]; ?>);"></div>
                                </a>
                            <?php } ?>
                            <div class="whoop-member-post-metas">
                                <a href="<?php echo get_the_permalink($post->ID); ?>"><?php echo get_the_title($post->ID); ?></a><br>
                                <?php
                                $html = whoop_get_address_html($post, true);
                                echo $html;
                                ?>
                            </div>
                        </header>
                        <!-- .comment-meta -->

                        <section class="comment-content comment">
                            <?php @comment_text($comment->comment_ID); ?>
                        </section>
                        <!-- .comment-content -->

                    </article>
                    <!-- #comment-## -->
                </li>
            <?php
            }
            ?>
        </ul>
        <?php if ($total > $items_per_page) { ?>
            <?php if ($recent_only) { ?>
                <a href="<?php echo $user_domain; ?>reviews/?rpage=2" class="whoop-btn whoop-more-btn"><?php echo __('More', GEODIRECTORY_FRAMEWORK) ?> &#187;</a>
            <?php } else { ?>
                <div id="pag-top" class="pagination">
                    <div class="pag-count" id="member-dir-count-top">
                        <?php echo sprintf(_n('1 of 1', '%1$s to %2$s of %3$s', $total, GEODIRECTORY_FRAMEWORK), $start, $end, $total); ?>
                    </div>
                    <div class="pagination-links">
                        <span class="whoop-pagination-text"><?php echo __('Go to Page', GEODIRECTORY_FRAMEWORK) ?></span>
                        <?php
                        echo paginate_links(array(
                            'base' => esc_url(add_query_arg('rpage', '%#%')),
                            'format' => '',
                            'prev_next' => false,
                            'total' => ceil($total / $items_per_page),
                            'current' => $page
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    <?php
    } else {
        ?>
        <div id="whoop_no_reviews">
            <p>
                <i class="fa fa-pencil-square-o"></i>
                <?php echo whoop_bp_member_name(bp_get_displayed_user_displayname()) . ' ' . __('hasn\'t written any reviews just yet.', GEODIRECTORY_FRAMEWORK); ?>
            </p>
        </div>
    <?php
    }
    ?>
<?php
}
//Allow only one review on a post for a user
$user_review_arr = '';
function whoop_user_review_limit($post_id = null) {
    global $current_user, $wpdb,$user_review_arr;
    $user_id = $current_user->ID;
    if (!$user_id) {
        return 0;
    }

    if($user_review_arr){
        if(isset($user_review_arr[$post_id])){
            return $user_review_arr[$post_id];
        }
    }else{
        $review_table = GEODIR_REVIEW_TABLE;
        $results = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $review_table WHERE overall_rating>%d AND user_id=%d ", array( 0, $user_id)));

        if(!empty($results)) {
            $user_review_arr = array();
            foreach ($results as $val) {
                if(isset($user_review_arr[$val->post_id])){
                    $user_review_arr[$val->post_id]=$user_review_arr[$val->post_id]+1;
                }else{
                    $user_review_arr[$val->post_id] = 1;
                }
            }

            if(isset($user_review_arr[$post_id])){
                return $user_review_arr[$post_id];
            }

        }

    }

    return 0;
}

//review of the day
function whoop_mark_review_of_the_day($actions, $comment) {
    $rating = 0;
    if (!empty($comment))
        $rating = geodir_get_commentoverall($comment->comment_ID);
    if ($rating != 0 ) {
        $cur_time = current_time( 'timestamp' );
        $date = date_i18n( 'l, F j, Y', $cur_time);
        $reviews = get_option('rotd_data');
        $r_date = date_i18n( 'Ymd', $cur_time);
        if (isset($reviews[$r_date]) && $reviews[$r_date] == $comment->comment_ID) {
            $action_url = admin_url("index.php?rotd_action=no&comment_id=$comment->comment_ID");
            $actions['mark_rotd'] = "<a href='{$action_url}' title='" . __( 'Remove from Review of The Day', GEODIRECTORY_FRAMEWORK ) . "'>". __( 'Remove from ROTD - ', GEODIRECTORY_FRAMEWORK ) . $date . '</a>';
        } else {
            $action_url = admin_url("index.php?rotd_action=yes&comment_id=$comment->comment_ID");
            $actions['mark_rotd'] = "<a href='{$action_url}' title='" . __( 'Mark as Review of The Day', GEODIRECTORY_FRAMEWORK ) . "'>". __( 'Mark as ROTD - ', GEODIRECTORY_FRAMEWORK ) . $date . '</a>';
        }
    }
    return $actions;
}
add_filter('comment_row_actions', 'whoop_mark_review_of_the_day', 10, 2);

function handle_rotd_action() {
    if(isset($_GET['rotd_action']) && isset($_GET['comment_id'])) {
        $cur_time = current_time( 'timestamp' );
        $date = date_i18n( 'Ymd', $cur_time);
        $reviews = get_option('rotd_data');
        if ($_GET['rotd_action'] == 'yes') {
            if(is_array($reviews))
                $reviews[$date] = $_GET['comment_id'];
            else
                $reviews = array($date => $_GET['comment_id']);

        } else {

            if(is_array($reviews))
                unset($reviews[$date]);

        }
        update_option('rotd_data', $reviews);
        add_action( 'admin_notices', 'whoop_rotd_notice' );
    }
}
add_action( 'admin_init', 'handle_rotd_action' );

function whoop_rotd_notice() {
    ?>
    <div class="updated">
        <p><?php echo sprintf( __( 'Review of the Day Updated <a href="%s">Go back to All Reviews</a>', GEODIRECTORY_FRAMEWORK ), admin_url("edit-comments.php")) ?></p>
    </div>
<?php
}

function geodir_whoop_reviews_g_size()
{
    return 60;
}

add_filter('geodir_recent_reviews_g_size', 'geodir_whoop_reviews_g_size');

function geodir_whoop_comment_template($comment_template)
{
    global $post;

    $post_types = geodir_get_posttypes();

    if (!(is_singular() && (have_comments() || (isset($post->comment_status) && 'open' == $post->comment_status)))) {
        return;
    }
    if (in_array($post->post_type, $post_types)) { // assuming there is a post type called business
        return locate_template('reviews.php');
    }
}

remove_filter("comments_template", "geodir_comment_template");
add_filter("comments_template", "geodir_whoop_comment_template");

//for geodir_buddypress
add_filter('geodir_buddypress_comment_callback', 'geodir_buddypress_comment_callback_whoop');
function geodir_buddypress_comment_callback_whoop() {
    return 'geodir_bp_comment_whoop';
}
function geodir_bp_comment_whoop($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    switch ($comment->comment_type) :
        case 'pingback' :
        case 'trackback' :
            // Display trackbacks differently than normal comments.
            ?>
            <li <?php comment_class('geodir-comment'); ?> id="comment-<?php comment_ID(); ?>">
            <p><?php _e('Pingback:', GEODIRECTORY_FRAMEWORK); ?> <?php comment_author_link(); ?> <?php edit_comment_link(__('(Edit)', GEODIRECTORY_FRAMEWORK), '<span class="edit-link">', '</span>'); ?></p>
            <?php
            break;
        default :
            // Proceed with normal comments.
            //global $post;
            ?>
            <li <?php @comment_class('geodir-comment', $comment->comment_ID); ?>
                id="li-comment-<?php echo $comment->comment_ID; ?>">
                <article id="comment-<?php echo $comment->comment_ID; ?>" class="comment hreview">
                    <header class="comment-meta comment-author vcard">
                        <?php
                        $post = get_post($comment->comment_post_ID);
                        if ($fimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' )) {
                            ?>
                            <a href="<?php echo get_the_permalink($post->ID); ?>">
                                <div class="geodir_thumbnail" style="background-image:url(<?php echo $fimage[0]; ?>);"></div>
                            </a>
                        <?php } ?>
                        <div class="whoop-member-post-metas">
                            <a href="<?php echo get_the_permalink($post->ID); ?>"><?php echo get_the_title($post->ID); ?></a><br>
                            <?php
                            $html = whoop_get_address_html($post, true);
                            echo $html;
                            ?>
                        </div>
                    </header>
                    <!-- .comment-meta -->

                    <section class="comment-content comment">
                        <?php @comment_text($comment->comment_ID); ?>
                    </section>
                    <!-- .comment-content -->

                </article>
                <!-- #comment-## -->
            </li>
            <?php
            break;
    endswitch; // end comment_type check
}

function geodir_comment_whoop($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    switch ($comment->comment_type) :
        case 'pingback' :
        case 'trackback' :
            // Display trackbacks differently than normal comments.
            ?>
            <li <?php comment_class('geodir-comment'); ?> id="comment-<?php comment_ID(); ?>">
            <p><?php _e('Pingback:', GEODIRECTORY_FRAMEWORK); ?> <?php comment_author_link(); ?> <?php edit_comment_link(__('(Edit)', GEODIRECTORY_FRAMEWORK), '<span class="edit-link">', '</span>'); ?></p>
            <?php
            break;
        default :
            // Proceed with normal comments.
            global $post;
            ?>
        <li <?php comment_class('geodir-comment'); ?> id="li-comment-<?php comment_ID(); ?>">
            <article id="comment-<?php comment_ID(); ?>" class="comment hreview">
                <header class="comment-meta comment-author vcard">
                    <?php
                    if ($comment->user_id) {
                        $user_profile_url = get_author_posts_url($comment->user_id);
                    } else {
                        $user_profile_url = '';
                    }

                    if ($user_profile_url) {
                        echo '<a href="' . $user_profile_url . '">';
                    }
                    echo get_avatar($comment->comment_author_email, 60);
                    if ($user_profile_url) {
                        echo '</a>';
                    }
                    ?>
                    <cite>
                        <b class="reviewer">
                            <?php
                            if ($user_profile_url) {
                                echo '<a href="' . $user_profile_url . '">';
                            }
                            echo get_comment_author($comment->comment_ID);
                            if ($user_profile_url) {
                                echo '</a>';
                            }
                            ?>
                        </b>
                    </cite>
                    <?php whoop_get_user_stats($comment->user_id); ?>
                    <?php do_action('whoop_review_hover_content', $comment->user_id, get_comment_link( $comment )); ?>
                </header>
                <!-- .comment-meta -->

                <?php if ('0' == $comment->comment_approved) : ?>
                    <p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', GEODIRECTORY_FRAMEWORK); ?></p>
                <?php endif; ?>

                <section class="comment-content comment">
                    <?php comment_text(); ?>
                </section>
                <?php
                global $gdf;
                if (
                    is_user_logged_in() &&
                    ($comment->comment_parent == '0') &&
                    ($gdf['whoop-author-reply'] != '1') &&
                    ($gdf['whoop-limit-review'] != '0') &&
                    (get_current_user_id() == $post->post_author || $gdf['whoop-user-reply'] != '1')
                ) { ?>
                <div class="comment-links whoop-com-links">
                    <div class="reply">
                        <?php comment_reply_link(array_merge($args, array('reply_text' => __('Reply', 'geodirectory'), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
                    </div>
                </div>
                <?php } ?>
            </article><!-- #comment-## -->
            <?php
            break;
    endswitch; // end comment_type check
}


remove_filter('comment_text', 'geodir_wrap_comment_text', 40);
add_filter('get_comment_text', 'geodir_whoop_wrap_comment_text', 10, 2);
function geodir_whoop_wrap_comment_text($content, $comment = '')
{   global $post;
    $rating = 0;
    if (!empty($comment))
        $rating = geodir_get_commentoverall($comment->comment_ID);
    if ($rating != 0 && !is_admin()) {
        $item = '<div class="whoop-review-rating">' . geodir_get_rating_stars($rating, $comment->comment_ID);
        $item .= "<span style=\"display: none;\" class='item'><small><span class='fn'>$post->post_title</span></small></span>";
        $item .= '<span style="display: none;"><a href="'.esc_url(get_comment_link($comment->comment_ID)).'"><time datetime="'.get_comment_time('c').'" class="dtreviewed">'.sprintf(__('%1$s at %2$s', 'geodirectory'), get_comment_date(), get_comment_time()).'<span class="value-title" title="'.get_comment_time('c').'"></span></time></a></span></div>';
        $item .= '<div class="description">' . $content . '</div>';
        return $item;
    } else
        return $content;
}

//buddypress
function whoop_add_review_activity($comment_id) {
    $comment = get_comment($comment_id);
    $post_id = $comment->comment_post_ID;
    $all_postypes = geodir_get_posttypes();
    $post_type = get_post_type( $post_id );
    if ($post_type == 'gd_event' || !in_array($post_type, $all_postypes))
        return;

    $user_id = $comment->user_id;
    if ($user_id && $post_id) {
        $user = get_user_by('id', $user_id);
        $user_link = whoop_get_user_profile_link( $user_id );
        $name = whoop_bp_member_name(whoop_get_current_user_name($user));
        $action = sprintf( __( '<a href="%s">%s</a> wrote a review for <a href="%s">%s</a>', GEODIRECTORY_FRAMEWORK ), $user_link, $name, get_the_permalink($post_id), get_the_title($post_id) );
        $args = array(
            'action' => $action,
            'component' => 'whoopreviews',
            'type'  => 'wrote_a_review',
            'user_id' => $user_id,
            'item_id' => $comment_id,
            'secondary_item_id' => $post_id
        );
        bp_activity_add( $args );
    }
}
if (class_exists('BuddyPress') && bp_is_active( 'activity' )) {
    add_action('comment_post', 'whoop_add_review_activity');
}

//whoop review hover content
function whoop_review_hover_content($user_id, $comment_link) {
    $comment_link = preg_replace("/#comment-([0-9]+)/", "?commentid=$1",  $comment_link);
    global $post;
    if (!is_user_logged_in()) {
        return;
    }
    $current_user_id = get_current_user_id();

    $author_share_link_display = apply_filters('whoop_author_share_link_display', false);
    if ($current_user_id == $user_id && $author_share_link_display == false) {
        return;
    }
    ?>
    <div class="whoop-hover-content">
        <ul class="whoop-hover-list">
            <li class="whoop-hover-list-item">
                <a class="whoop-hover-ajax whoop-send-to-friend" data-pid="<?php echo $post->ID; ?>" data-receiver="<?php echo $user_id; ?>" data-type="share" data-clink="<?php echo $comment_link; ?>" href="">
                    <i class="fa fa-share"></i>
                    <?php _e('Share review', GEODIRECTORY_FRAMEWORK); ?>
                </a>
            </li>
            <?php if ($current_user_id != $user_id) { ?>
                <?php if (class_exists('BuddyPress') && class_exists('BP_Compliments')) { ?>
                    <li class="whoop-hover-list-item">
                        <a class="whoop-hover-ajax whoop-send-compliment" data-pid="<?php echo $post->ID; ?>" data-receiver="<?php echo $user_id; ?>" data-type="compliment" data-clink="<?php echo $comment_link; ?>" href="">
                            <i class="fa fa-trophy"></i>
                            <?php _e('Send Compliment', GEODIRECTORY_FRAMEWORK); ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if (class_exists('BuddyPress')) { ?>
                    <?php if ( bp_is_active( 'messages' ) ) { ?>
                        <li class="whoop-hover-list-item">
                            <a class="whoop-send-pm" href="<?php echo wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $user_id ) ); ?>">
                                <i class="fa fa-comment"></i>
                                <?php _e('Send message', GEODIRECTORY_FRAMEWORK); ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ( bp_is_active( 'friends' ) && class_exists('BP_Follow_Component') ) { ?>
                        <li class="whoop-hover-list-item manage-follow-container">
                            <div id="item-buttons">
                                <a class="whoop-hover-follow" href="">
                                    <?php
                                    $args = array(
                                        'leader_id'     => $user_id,
                                        'follower_id'   => get_current_user_id(),
                                    );
                                    echo bp_follow_get_add_follow_button($args);
                                    ?>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </ul>
    </div>
    <?php
}
add_action('whoop_review_hover_content', 'whoop_review_hover_content', 10, 2);

add_action('init', 'whoop_set_default_rating_icon');
function whoop_set_default_rating_icon() {
    $default_star = get_option('geodir_default_rating_star_icon');
    if (!$default_star || ($default_star == geodir_plugin_url() . '/geodirectory-assets/images/stars.png')) {
        update_option('geodir_default_rating_star_icon', get_template_directory_uri() . '/library/images/whoop-star.png');
    }
}

if (defined('GEODIRREVIEWRATING_VERSION') && is_admin()) {
    add_action('init', 'whoop_set_default_multi_rating_icon');
}
function whoop_set_default_multi_rating_icon() {
    $default_star = get_option('geodir_reviewrating_overall_off_img');
    if (!$default_star || ($default_star == GEODIR_REVIEWRATING_PLUGINDIR_URL . '/icons/stars.png')) {
        update_option('geodir_reviewrating_overall_off_img', get_template_directory_uri() . '/library/images/whoop-star.png');
    }

    $default_color = get_option('geodir_reviewrating_overall_color');
    if (!$default_color || ($default_color == '#ff9900')) {
        update_option('geodir_reviewrating_overall_color', '#ed695d');
    }
}

function add_commentid_scroll_js() {
    if (!is_single()) {
        return;
    }
    if (isset($_GET['commentid'])) {
        $comment_id = strip_tags(esc_sql($_GET['commentid']));
        if ($comment_id) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery(document).scrollTo('#comment-<?php echo $comment_id; ?>');
                });
            </script>
            <?php
        }
    }
}
add_action('wp_footer', 'add_commentid_scroll_js');

//function whoop_author_share_link_display() {
//    return true;
//}
//add_filter('whoop_author_share_link_display', 'whoop_author_share_link_display');