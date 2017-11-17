<?php
add_action( 'widgets_init', create_function( '', 'return register_widget("Geodir_Next_Review_Widget");' ) );
class Geodir_Next_Review_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'Displays "Your Next Review Awaits" widget', GEODIRECTORY_FRAMEWORK ),
            'classname' => 'widget_next_review',
        );
        parent::__construct( false, $name = _x( 'Whoop > Your Next Review Awaits', 'widget name', GEODIRECTORY_FRAMEWORK ), $widget_ops );

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

        $title = empty($instance['title']) ? __('Your Next Review Awaits', GEODIRECTORY_FRAMEWORK) : apply_filters('ynra_widget_title', __($instance['title'], GEODIRECTORY_FRAMEWORK));
        $post_type = empty($instance['post_type']) ? 'gd_place' : apply_filters('ynra_widget_post_type', $instance['post_type']);

        $post_ids = your_next_review_postids($post_type);
        if (!$post_ids) {
            return;
        }
        echo $before_widget;
        ?>
        <?php if ($title) {
            echo $before_title . $title . $after_title;
        } ?>
        <?php
        $current_id = $post_ids[0];
        $current_index = array_search($current_id, $post_ids);

        $next = $current_index + 1;
        $prev = $current_index - 1;
        ?>
        <div class="whoop-ynr-wrap">
        <div class="whoop-next-slides">
            <div class="whoop-rev-next-nav">
                <ul class="flex-direction-nav" style="display: block">
            <?php if ($prev > 0) { ?>
                    <li>
                        <a class="whoop-btn-prev-next" data-id="<?php echo $post_ids[$prev]; ?>" data-ptype="<?php echo $post_type; ?>" href="#"><i class="fa fa-caret-left"></i></a>
                    </li>
            <?php } ?>
        <?php if ($next < count($post_ids)) { ?>
                    <li>
                        <a class="whoop-btn-prev-next" data-id="<?php echo $post_ids[$next]; ?>" data-ptype="<?php echo $post_type; ?>" href="#"><i class="fa fa-caret-right"></i></a>
                    </li>
        <?php } ?>
                </ul>
            </div>
            <ul class="whoop-next-reviews slides">
                <?php
                your_next_review_item($current_id);
            ?>
            </ul>
        </div>
        </div>
        <?php echo $after_widget; ?>
    <?php
    }

    function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['post_type'] = strip_tags($new_instance['post_type']);
        return $instance;
    }

    function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array(
            'title' => __('Your Next Review Awaits', GEODIRECTORY_FRAMEWORK),
            'post_type' => ''
            ));
        $title = strip_tags($instance['title']);
        $post_type = strip_tags($instance['post_type']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __("Widget Title:", GEODIRECTORY_FRAMEWORK); ?> <input class="widefat"
                                                                                         id="<?php echo $this->get_field_id('title'); ?>"
                                                                                         name="<?php echo $this->get_field_name('title'); ?>"
                                                                                         type="text"
                                                                                         value="<?php echo esc_attr($title); ?>"/></label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:', GEODIRECTORY_TEXTDOMAIN);?>

                <?php $postypes = geodir_get_posttypes();
                /**
                 * Filter the post types to display in widget.
                 *
                 * @since 1.3.9
                 *
                 * @param array $postypes Post types array.
                 */
                $postypes = apply_filters('geodir_post_type_list_in_p_widget', $postypes); ?>

                <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>"
                        name="<?php echo $this->get_field_name('post_type'); ?>"
                        onchange="geodir_change_category_list(this)">

                    <?php foreach ($postypes as $postypes_obj) { ?>

                        <option <?php if ($post_type == $postypes_obj) {
                            echo 'selected="selected"';
                        } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_', $postypes_obj);
                            echo ucfirst($extvalue[1]); ?></option>

                    <?php } ?>

                </select>
            </label>
        </p>
    <?php
    }

}

function your_next_review_postids($post_type) {
    $current_user = wp_get_current_user();

    $p_ids = array();
    $reviewed_or_invalid_ids = array();
    $user_favorites = get_user_meta($current_user->ID, 'gd_user_favourite_post', true);
    //latest bookmarks first
    if ($user_favorites) {
        $user_favorites = array_reverse($user_favorites, true);
        foreach ($user_favorites as $fav) {
            if ((get_post_status($fav) == 'publish') && (get_post_type($fav) == $post_type) && (whoop_user_review_limit($fav) == 0)) {
                $p_ids[] = $fav;
            } else {
                $reviewed_or_invalid_ids[] = (int) $fav;
            }
        }
    }

    if (count($p_ids) < 10) {
        $query_args = array(
            'posts_per_page' => 100,
            'is_geodir_loop' => true,
            'gd_location' => true,
            'post_type' => $post_type,
            'order_by' => 'high_review',
        );

        $widget_listings = geodir_get_widget_listings($query_args);

        $listing_ids = wp_list_pluck( $widget_listings, 'ID' );

        foreach ($listing_ids as $id) {
            if ((!in_array($id, $reviewed_or_invalid_ids)) && whoop_user_review_limit($id) == 0) {
                $p_ids[] = $id;
                if (count($p_ids) >= 10) {
                    break;
                }
            }
        }
    }
    $post_ids = array_unique($p_ids);
    $post_ids = array_values($post_ids);
    return $post_ids;
}

function your_next_review_item($post_id) {
    $post_object = get_post($post_id);
    global $post, $geodir_post_type;
    $post = $post_object;
    setup_postdata( $post );
    $geodir_post_type = $post->post_type;

    $args = array(
        'title_reply'       => '',
        'logged_in_as' => '',
        'label_submit'      => __( 'Post Review', GEODIRECTORY_FRAMEWORK ),
        'comment_field' => '<p class="comment-form-comment"><textarea placeholder="'.__( "Start your review...", GEODIRECTORY_FRAMEWORK ).'" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>'
    );
    ?>
    <li class="whoop-next-review" id="comments">
        <div class="whoop-next-avatar">
            <?php
            if ($fimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' )) {
                ?>
                <a href="<?php echo get_the_permalink($post->ID); ?>">
                    <div class="geodir_thumbnail" style="background-image:url(<?php echo $fimage[0]; ?>);"></div>
                </a>
            <?php } ?>
        </div>
        <div class="whoop-next-content">
            <div class="whoop-next-title">
                <a class="whoop-next-title-link" href="<?php echo get_the_permalink($post->ID); ?>"><?php echo get_the_title($post->ID); ?></a>
            </div>
            <?php
            comment_form( $args, $post_id );
            ?>
            <div style="display: none" class="whoop-hovercard">
                <div class="whoop-hovercard-inner">
                    <div class="whoop-member-post-metas">
                        <a class="hovercard-title" href="<?php echo get_the_permalink($post->ID); ?>"><?php echo get_the_title($post->ID); ?></a><br>

                        <div class="whoop-boomark-post-rating geodir-big-header-ratings">
                            <?php
                            $post_avgratings = geodir_get_post_rating($post->ID);
                            echo geodir_get_rating_stars($post_avgratings, $post->ID);
                            ?>
                            <a href="<?php echo get_comments_link($post->ID); ?>"
                               class="geodir-big-header-rc"><?php geodir_comments_number($post->rating_count); ?></a>
                        </div>
                        <div class="whoop-bm-address">
                            <?php
                            $html = whoop_get_address_html($post);
                            echo $html;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
<?php
    whoop_next_review_js();
}


function your_next_review_ajax()
{
    check_ajax_referer('whoop-ynr-nonce', 'whoop_ynr_nonce');
    //set variables
    $post_id = strip_tags(esc_sql($_POST['postid']));
    $post_type = strip_tags(esc_sql($_POST['ptype']));

    $post_ids = your_next_review_postids($post_type);
    $current_id = $post_id;
    $current_index = array_search($current_id, $post_ids);

    $next = $current_index + 1;
    $prev = $current_index - 1;
    ?>
    <div class="whoop-rev-next-nav">
        <ul class="flex-direction-nav" style="display: block">
            <?php if ($prev >= 0) { ?>
                <li>
                    <a class="whoop-btn-prev-next" data-id="<?php echo $post_ids[$prev]; ?>" data-ptype="<?php echo $post_type; ?>" href="#"><i
                            class="fa fa-caret-left"></i></a>
                </li>
            <?php } ?>
            <?php if ($next < count($post_ids)) { ?>
                <li>
                    <a class="whoop-btn-prev-next" data-id="<?php echo $post_ids[$next]; ?>" data-ptype="<?php echo $post_type; ?>" href="#"><i
                            class="fa fa-caret-right"></i></a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <ul class="whoop-next-reviews slides">
        <?php
        your_next_review_item($current_id);
        ?>
    </ul>
    <script src="<?php echo geodir_plugin_url() . '/geodirectory-assets/js/jRating.jquery.min.js'; ?>"></script>
    <script src="<?php echo geodir_plugin_url() . '/geodirectory-assets/js/on_document_load.js#asyncload'; ?>"></script>
    <?php
    wp_die();
}

//Ajax functions
add_action('wp_ajax_your_next_review', 'your_next_review_ajax');

//Javascript
function whoop_next_review_js() {
    $ajax_nonce = wp_create_nonce("whoop-ynr-nonce");
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('a.whoop-btn-prev-next').click(function (e) {
                e.preventDefault();
                var container = jQuery('.whoop-next-slides');
                var postid = jQuery(this).attr('data-id');
                var ptype = jQuery(this).attr('data-ptype');
                var data = {
                    'action': 'your_next_review',
                    'whoop_ynr_nonce': '<?php echo $ajax_nonce; ?>',
                    'postid': postid,
                    'ptype': ptype
                };

                jQuery('.whoop-rev-next-nav').html('<i class="fa fa-cog fa-spin"></i>');

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                    container.html(response);
                    if (typeof geodir_init_rating === 'function') { geodir_init_rating(); }
                    if (typeof geodir_review_upload_init === 'function') { geodir_review_upload_init(); }
                });
            })
            jQuery('.whoop-next-content textarea').focus(function () {
                jQuery(this).animate({ height: "182px" }, 500);
                jQuery('.whoop-next-content .form-submit').css('display', 'block');
            });
            jQuery('.whoop-next-content textarea').focusout(function () {
                jQuery(this).animate({ height: "36px" }, 500);
            });
        });
    </script>
<?php
}

function whoop_next_review_enqueue() {
    wp_register_style('geodir-rating-style', geodir_plugin_url() . '/geodirectory-assets/css/jRating.jquery.css', array(), GEODIRECTORY_VERSION);
    wp_enqueue_style('geodir-rating-style');
    if (defined('GEODIRREVIEWRATING_VERSION')) {
        wp_register_style( 'geodir-reviewratingrating-style', GEODIR_REVIEWRATING_PLUGINDIR_URL .'/css/style.css' );
        wp_enqueue_style( 'geodir-reviewratingrating-style' );
    }
}
add_action('wp_enqueue_scripts', 'whoop_next_review_enqueue');