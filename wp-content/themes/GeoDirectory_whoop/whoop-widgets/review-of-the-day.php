<?php
class geodir_rotd_widget extends WP_Widget
{

    function __construct() {
        $widget_ops = array('classname' => 'geodir_recent_reviews', 'description' => __('Whoop > Review of the Day', GEODIRECTORY_FRAMEWORK));
        parent::__construct(
            'geodir_rotd', // Base ID
            __('Whoop > Review of the Day', GEODIRECTORY_FRAMEWORK), // Name
            $widget_ops// Args
        );
    }

    public function widget($args, $instance)
    {
        // prints the widget
        extract($args, EXTR_SKIP);

        $title = empty($instance['title']) ? '' : apply_filters('widget_title', __($instance['title'], GEODIRECTORY_FRAMEWORK));

        $cur_time = current_time( 'timestamp' );
        $reviews = get_option('rotd_data');
        $r_date = date_i18n( 'Ymd', $cur_time);
        $c_id = 0;
        if (isset($reviews[$r_date])) {
            $c_id = $reviews[$r_date];
        }
        if ($c_id) {
            echo $before_widget;
            ?>
            <div class="widget geodir_rotd_section">
                <?php if ($title) {
                    echo $before_title . $title . $after_title;
                }
                global $wpdb;
                $comment = get_comment($c_id);
                $comment_id = $comment->comment_ID;
                $review_table = GEODIR_REVIEW_TABLE;
                $request = "SELECT overall_rating FROM $review_table WHERE comment_id =$comment_id AND overall_rating>1";
                $result = $wpdb->get_row($request);
                $comment_lenth = apply_filters('whoop_rotd_excerpt_length', 60);
                $comment_post_ID = $comment->comment_post_ID;
                $comment_content = strip_tags($comment->comment_content);
                $comment_content = preg_replace('#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_content);
                $post_title = get_the_title($comment_post_ID);
                $permalink = get_permalink($comment_post_ID);
                $comment_permalink = $permalink . "#comment-" . $comment->comment_ID;
                $read_more = '<a class="comment_excerpt" href="' . $comment_permalink . '">' . __('Read more', GEODIRECTORY_FRAMEWORK) . '</a>';

                $comment_content_length = geodir_utf8_strlen($comment_content);
                if ($comment_content_length > $comment_lenth) {
                    $comment_excerpt = geodir_utf8_substr($comment_content, 0, $comment_lenth) . '... ' . $read_more;
                } else {
                    $comment_excerpt = $comment_content;
                }

                if ($comment->user_id) {
                    $user_profile_url = get_author_posts_url($comment->user_id);
                } else {
                    $user_profile_url = '';
                }

                $comments_echo = '';
                $comments_echo .= '<li class="clearfix">';
                $comments_echo .= "<span class=\"li" . $comment_id . " geodir_reviewer_image\">";
                if ($user_profile_url) {
                    $comments_echo .= '<a href="' . $user_profile_url . '">';
                }
                $comments_echo .= get_avatar($comment->comment_author_email, 60);
                if ($user_profile_url) {
                    $comments_echo .= '</a>';
                }
                $comments_echo .= "</span>\n";
                $comments_echo .= '<span class="geodir_reviewer_content">';
                if ($user_profile_url) {
                    $comments_echo .= '<a href="' . $user_profile_url . '">';
                }
                $comments_echo .= '<span class="geodir_reviewer_author">' . $comment->comment_author . '</span> ';
                if ($user_profile_url) {
                    $comments_echo .= '</a>';
                }
                $comments_echo .= '<span class="geodir_reviewer_reviewed">' . __('reviewed', GEODIRECTORY_FRAMEWORK) . '</span> ';
                //if($comment->user_id){'</a> ';}
                $comments_echo .= '<a href="' . $permalink . '" class="geodir_reviewer_title">' . $post_title . '</a>';
                $comments_echo .= geodir_get_rating_stars($result->overall_rating, $comment_post_ID);
                $comments_echo .= '<p class="geodir_reviewer_text">' . $comment_excerpt . '';
                $comments_echo .= '</p>';

                $comments_echo .= "</span>\n";

                ?>
                <ul class="geodir_recent_reviews">
                    <?php echo $comments_echo; ?>
                </ul>
            </div>
            <?php
            echo $after_widget;
        }
    }

    public function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    public function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => __('Review of the Day', GEODIRECTORY_FRAMEWORK)));
        $title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __("Widget Title:", GEODIRECTORY_FRAMEWORK); ?> <input class="widefat"
                                                                                         id="<?php echo $this->get_field_id('title'); ?>"
                                                                                         name="<?php echo $this->get_field_name('title'); ?>"
                                                                                         type="text"
                                                                                         value="<?php echo esc_attr($title); ?>"/></label>
        </p>
    <?php
    }
}

register_widget('geodir_rotd_widget');



