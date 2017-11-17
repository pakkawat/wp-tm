<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("BP_Whoop_Recent_Activity_Widget");' ) );
class BP_Whoop_Recent_Activity_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'Display recent activities in home page like Yelp', GEODIRECTORY_FRAMEWORK ),
            'classname' => 'widget_bp_whoop_ra_widget',
        );
        parent::__construct( false, $name = _x( 'Whoop > Recent Activity', 'widget name', GEODIRECTORY_FRAMEWORK ), $widget_ops );

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget( $args, $instance ) {
        extract( $args );

        $wid_title = __( 'Recent Activity', GEODIRECTORY_FRAMEWORK );
        $instance['title'] = $wid_title;
        $title = apply_filters( 'whoop_ra_widget_title', $instance['title'] );

        $ra_type  = isset( $_GET['ra_type'] ) ? sanitize_text_field( $_GET['ra_type'] ) : 'near_you';

        echo $before_widget;
        $title = esc_html( $title );
        ?>
        <div class="whoop_ra_widget_header">
        <?php
        echo $before_title . $title . $after_title;
        ?>
        <?php if(is_user_logged_in()) { ?>
        <div id="buddypress">
            <div class="item-list-tabs no-ajax" id="subnav" role="navigation">
                <ul>
                    <li class="<?php echo ($ra_type == 'near_you') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('ra_type' => 'near_you'), geodir_curPageURL())); ?>"><?php echo __('Near You', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($ra_type == 'friends') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('ra_type' => 'friends'), geodir_curPageURL())); ?>"><?php echo __('Friends', GEODIRECTORY_FRAMEWORK) ?></a></li>
                    <li class="<?php echo ($ra_type == 'self') ? 'current selected' : ''; ?>"><a href="<?php echo esc_url(add_query_arg(array('ra_type' => 'self'), geodir_curPageURL())); ?>"><?php echo __('Your Activity', GEODIRECTORY_FRAMEWORK) ?></a></li>
                </ul>
            </div>
        </div>
        <?php } ?>
        </div>
        <?php
        $act_args = array();
        if ($ra_type == 'self') {
            $act_args['scope'] = 'just-me';
        } elseif ($ra_type == 'friends') {
            $act_args['scope'] = 'friends';
        }
        ?>
        <ul class="geodir_recent_reviews">
        <?php
        whoop_bp_recent_activity($act_args);
        ?>
        </ul>
        <?php echo $after_widget; ?>
    <?php
    }
}

function whoop_fetch_recent_activity()
{
    check_ajax_referer('whoop-recent-activity-nonce', 'whoop_recent_activity_nonce');
    //set variables
    $page_no = strip_tags(esc_sql($_POST['page_no']));
    $scope = strip_tags(esc_sql($_POST['scope']));

    $act_args = array();
    $act_args['scope'] = $scope;
    $act_args['page'] = (int) $page_no + 1;
    whoop_bp_recent_activity($act_args);
    wp_die();
}

//Ajax functions
add_action('wp_ajax_whoop_fetch_recent_activity', 'whoop_fetch_recent_activity');
add_action('wp_ajax_nopriv_whoop_fetch_recent_activity', 'whoop_fetch_recent_activity');

//Javascript
function whoop_recent_activity_js()
{ ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            var loading = jQuery("#whoop_recent_activity_loading");
            var link = jQuery('.whoop-load-more');
            var container = jQuery('#whoop_ra_load_more');
            <?php $ajax_nonce = wp_create_nonce( "whoop-recent-activity-nonce" ); ?>
            jQuery('a.whoop-load-more').click(function (e) {
                e.preventDefault();
                jQuery(document).ajaxStart(function () {
                    link.hide();
                    loading.show();
                }).ajaxStop(function () {
                    loading.hide();
                    container.fadeIn('slow');
                });
                var page_no = jQuery(this).attr('data-page');
                var scope = jQuery(this).attr('data-scope');
                var data = {
                    'action': 'whoop_fetch_recent_activity',
                    'whoop_recent_activity_nonce': '<?php echo $ajax_nonce; ?>',
                    'page_no': page_no,
                    'scope': scope
                };

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                    container.replaceWith(response);
                });
            })
        });
    </script>
<?php
}