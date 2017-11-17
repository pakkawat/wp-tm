<?php
/** http://wpgeodirectory.com/support/topic/how-to-change-default-tab-from-profile-to-review/#post-24021 **/
add_filter('geodir_detail_page_tab_list_extend', 'geodir_detail_page_tab_list_extend');
function geodir_detail_page_tab_list_extend($tab_array)
{
    global $preview;
    if ($preview) {
        return $tab_array;
    }
    $new_tab_array = array();
    // here u can modify this array, u can create a completely new one too.
    if (isset($tab_array['reviews'])) {
        $new_tab_array['reviews'] = $tab_array['reviews'];// set in new array
        $new_tab_array['reviews']['is_active_tab'] = '1';
        unset($tab_array['reviews']);//unset in old one
    }
    if (isset($tab_array['post_profile'])) {
        $new_tab_array['post_profile'] = $tab_array['post_profile']; // set in new array
        $new_tab_array['post_profile']['is_active_tab'] = '';
        unset($tab_array['post_profile']);//unset in old one
    }

    if (isset($tab_array['post_map'])) {
        $new_tab_array['post_map'] = $tab_array['post_map'];// set in new array
        unset($tab_array['post_map']);//unset in old one
    }

    if (isset($tab_array['special_offers'])) {
        $new_tab_array['special_offers'] = $tab_array['special_offers'];// set in new array
        unset($tab_array['special_offers']);//unset in old one
    }

    if (isset($tab_array['post_info'])) {
        $new_tab_array['post_info'] = $tab_array['post_info'];// set in new array
        unset($tab_array['post_info']);//unset in old one
    }

    if (isset($tab_array['post_images'])) {
        $new_tab_array['post_images'] = $tab_array['post_images'];// set in new array
        unset($tab_array['post_images']);//unset in old one
    }

    if (isset($tab_array['post_video'])) {
        $new_tab_array['post_video'] = $tab_array['post_video'];// set in new array
        unset($tab_array['post_video']);//unset in old one
    }

    if (isset($tab_array['special_offers'])) {
        $new_tab_array['special_offers'] = $tab_array['special_offers'];// set in new array
        unset($tab_array['special_offers']);//unset in old one
    }


    // now we set any remaining tabs that have not been assigned an order
    foreach ($tab_array as $key => $tab) {
        $new_tab_array[$key] = $tab;
    }

    return $new_tab_array;
}

function whoop_get_address_html($post, $review_page = false) {
    $html = "";
    $post_address = isset($post->post_address) ? $post->post_address : geodir_get_post_meta($post->ID, 'post_address', true);
    $post_city = isset($post->post_city) ? $post->post_city : geodir_get_post_meta($post->ID, 'post_city', true);
    $post_region = isset($post->post_region) ? $post->post_region : geodir_get_post_meta($post->ID, 'post_region', true);
    $post_zip = isset($post->post_zip) ? $post->post_zip : geodir_get_post_meta($post->ID, 'post_zip', true);
    $post_country = isset($post->post_country) ? $post->post_country : geodir_get_post_meta($post->ID, 'post_country', true);
    $class = $review_page ? 'fsize12' : '';
    if ($post_address) {
        $html .= '<span class="'.$class.'">' . stripslashes($post_address) . '</span><br>';
    }
    if ($post_city) {
        $html .= '<span class="'.$class.'">' . $post_city . '</span>, ';
    }
    if ($post_region) {
        $html .= '<span class="'.$class.'">' . $post_region . '</span> ';
    }
    if ($post_zip) {
        $html .= '<span class="'.$class.'">' . $post_zip . '</span><br>';
    }
    if ($post_country && !$review_page) {
        $html .= '<span class="'.$class.'">' . $post_country . '</span><br>';
    }
    return apply_filters('whoop_get_address_html_filter', $html, $post, $class);
}

function whoop_get_user_profile_link($user_id)
{
    if (class_exists('BuddyPress')) {
        $user_link = bp_core_get_user_domain($user_id);
    } else {
        $user_link = get_author_posts_url($user_id);
    }
    return $user_link;
}

function whoop_pluralize($count, $singular, $plural = false)
{
    return ($count == 1 ? $singular : $plural);
}

function whoop_return_blank() {
    return '';
}

function whoop_bp_member_name($name)
{
    $text = explode(' ', $name);
    if (count($text) > 1) {
        $first_char = strtoupper(substr($text[1], 0, 1));
        return $text[0] . ' ' . $first_char . '.';
    } else {
        return $name;
    }
}

function whoop_get_current_user_name($current_user)
{
    $uname = "";
    $name_stack = array(
        'display_name',
        'user_nicename',
        'user_login'
    );
    foreach ($name_stack as $source) {
        if (!empty($current_user->{$source})) {
            $uname = $current_user->{$source};
            break;
        }
    }
    return $uname;
}

function whoop_get_user_stats($user_id) {
    ?>
    <ul class="user-account-stats-c">
        <?php if ( class_exists( 'BuddyPress' ) && bp_is_active( 'friends' ) ) { ?>
            <li class="whoop-friend-count">
                <i class="fa fa-users"></i>
                <strong><?php //echo friends_get_friend_count_for_user( $user_id );
                    echo whoop_get_friend_count_for_user($user_id);?></strong>
                <?php _e('friends', GEODIRECTORY_FRAMEWORK); ?>
            </li>
        <?php } ?>
        <li class="whoop-review-count">
            <i class="fa fa-star"></i>
            <strong>
                <?php $count = geodir_get_review_count_by_user_id($user_id );
                if($count) {
                    echo $count;
                } else {
                    echo "0";
                }
                ?>
            </strong>
            <?php _e('reviews', GEODIRECTORY_FRAMEWORK); ?>
        </li>
    </ul>
<?php
}


function geodir_detail_page_review_rating_html_wrap($content_html)
{
    $html = '<section class="widget geodir-widget">';
    $html .= '<h3 class="widget-title">' . __('Rating Information', GEODIRECTORY_FRAMEWORK) . '</h3>';
    $html .= $content_html;
    $html .= '</section>';
    return $html;
}

add_filter('geodir_detail_page_review_rating_html', 'geodir_detail_page_review_rating_html_wrap');

function geodir_edit_post_link_html_wrap($content_html)
{
    $html = '<section class="widget geodir-widget">';
    $html .= '<h3 class="widget-title">' . __('User Links', GEODIRECTORY_FRAMEWORK) . '</h3>';
    $html .= $content_html;
    $html .= '</section>';
    return $html;
}

add_filter('geodir_edit_post_link_html', 'geodir_edit_post_link_html_wrap');

function geodir_detail_page_more_info_html_wrap($content_html)
{
    $html = '<section class="widget geodir-widget">';
    $html .= '<h3 class="widget-title">' . __('Listing Information', GEODIRECTORY_FRAMEWORK) . '</h3>';
    $html .= $content_html;
    $html .= '</section>';
    return $html;
}

add_filter('geodir_detail_page_more_info_html', 'geodir_detail_page_more_info_html_wrap');

function geodir_social_sharing_buttons_html_wrap($content_html)
{
    $html = '<section class="widget geodir-widget">';
    $html .= '<h3 class="widget-title">' . __('Like this page', GEODIRECTORY_FRAMEWORK) . '</h3>';
    $html .= $content_html;
    $html .= '</section>';
    return $html;
}

add_filter('geodir_social_sharing_buttons_html', 'geodir_social_sharing_buttons_html_wrap');

function geodir_google_analytic_html_wrap($content_html)
{
    if (trim($content_html) == '') {
        return $content_html;
    }
    $html = '<section class="widget geodir-widget">';
    $html .= '<h3 class="widget-title">' . __('Google Analytics', GEODIRECTORY_FRAMEWORK) . '</h3>';
    $html .= $content_html;
    $html .= '</section>';
    return $html;
}

add_filter('geodir_google_analytic_html', 'geodir_google_analytic_html_wrap');

function geodir_share_this_button_html_wrap($content_html)
{
    $html = '<section class="widget geodir-widget">';
    $html .= '<h3 class="widget-title">' . __('Share this page', GEODIRECTORY_FRAMEWORK) . '</h3>';
    $html .= $content_html;
    $html .= '</section>';
    return $html;
}

add_filter('geodir_share_this_button_html', 'geodir_share_this_button_html_wrap');

function geodir_whoop_sharing_buttons_popup()
{
    ?>
    <div class="whoop-dd-menu whoop-sharing-buttons">
        <div class="likethis">
            <?php geodir_twitter_tweet_button(); ?>
            <?php geodir_fb_like_button(); ?>
            <?php geodir_google_plus_button(); ?>
        </div>
    </div>
<?php
}

add_action('geodir-whoop-sharing-buttons-popup', 'geodir_whoop_sharing_buttons_popup');

add_action('geodir_detail_before_main_content', 'geodir_whoop_big_header', 30);
function geodir_whoop_big_header()
{
    global $post, $preview;
    ?>
    <div class="geodir-big-header geodir-common clearfix">
        <h1 class="entry-title geodir-big-header-title fn whoop-title">
            <?php
            echo esc_attr(stripslashes($post->post_title));
            ?>
        </h1>

<!--  Bank   -->
<div class="order-online-big-button">
<?php 
$check_button= $post->geodir_Button_enable;

if($check_button){

echo "<span class='glf-button' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false'>ORDER NOW</span><script src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";

}?>
</div>

<!-- Bank Add ORDER Big BUTTON   -->

<!-- Bank  -->
<div class="order-online-small-button">
<?php
if($check_button){

echo "<span class='glf-button glyphicon' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false'></span><script src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";

}?>
</div>
<!-- Bank Add Shop Cart BUTTON on top  -->

        <div class="geodir-big-header-wrap">
            <div class="geodir-big-header-ratings">
                <?php
                $post_avgratings = geodir_get_post_rating($post->ID);
                echo geodir_get_rating_stars($post_avgratings, $post->ID);
                ?>
                <a href="<?php comments_link(); ?>"
                   class="geodir-big-header-rc">
                    <?php
                    if (!$preview) {
                        geodir_comments_number($post->rating_count);
                    } else {
                        echo __('No Reviews', GEODIRECTORY_FRAMEWORK);
                    }
                    ?></a>
                <?php
                do_action('geodir-whoop-listing-taxnomies');
                ?>
            </div>
            <div class="geodir-big-header-buttons">
                <?php
                if (!is_user_logged_in()) {
                    $login_url = geodir_login_url(array('redirect_to'=>get_the_permalink()));
                } else {
                    $login_url = false;
                }
                if (!$preview) {
                    $show_review_btn = apply_filters('whoop_big_header_show_review_btn', true);
                    $show_add_photo_btn = apply_filters('whoop_big_header_show_add_photo_btn', true);
                    $show_edit_btn = apply_filters('whoop_big_header_show_edit_btn', true);
                    $show_share_btn = apply_filters('whoop_big_header_show_share_btn', true);
                    $show_bookmark_btn = apply_filters('whoop_big_header_show_bookmark_btn', true);
                    $show_business_owner_btn = apply_filters('whoop_big_header_show_business_owner_btn', true);
                    $user_id = get_current_user_id();

                    $package_info = array();
                    $package_info = geodir_post_package_info($package_info, $post);

                    $image_limit = $package_info->image_limit;

                    $thumb_img_arr = array();
                    $thumb_img_arr = geodir_get_images($post->ID);

                    $totImg = count((array)$thumb_img_arr);

                    global $gdf;
                    if (($gdf['whoop-limit-review'] == '1') || ($gdf['whoop-limit-review'] == '0') && (whoop_user_review_limit() == 0)) {
                        if ($show_review_btn) {
                        ?>
                            <a href="<?php if ($login_url) { echo $login_url; } else { the_permalink(); } ?>#respond" class="whoop-btn whoop-btn-primary whoop-write-rev-btn geodir_button">
                                <i class="fa fa-star"></i> <?php echo __('Write a Review', GEODIRECTORY_FRAMEWORK); ?>
                            </a>
                        <?php }
                    } ?>
                    <span class="whoop-btn-group">
                        <?php do_action('whoop_big_header_buttons_before'); ?>
                        <?php if ($show_add_photo_btn) { ?>
                            <?php
                            if ($image_limit == '' || $totImg < (int) $image_limit) { ?>
	                            <a href="<?php if ($login_url) { echo $login_url.'?add_biz_photo=1'; } else { echo esc_url(add_query_arg(array("add_biz_photo" => '1'), get_permalink())); } ?>" class="whoop-btn whoop-btn-small"><i class="fa fa-camera"></i> <?php echo __('Add Photo', GEODIRECTORY_FRAMEWORK); ?></a>
                            <?php } ?>
                        <?php } ?>
                        <?php
                        if (!$login_url && $user_id == $post->post_author) {
                            $postlink = get_permalink(geodir_add_listing_page_id());
                            $editlink = geodir_getlink($postlink, array('pid' => $post->ID), false);
                            ?>
                            <?php if ($show_edit_btn) { ?>
                                <a href="<?php echo $editlink; ?>" class="whoop-btn whoop-btn-small"><i class="fa fa-edit"></i> <?php echo __('Edit', GEODIRECTORY_FRAMEWORK); ?></a>
                            <?php } ?>
                            <?php
                        }
                        ?>
                    <?php if ($show_share_btn) { ?>
                        <a href="#" class="whoop-btn whoop-btn-small whoop-share-button"><i class="fa fa-share"></i> <?php echo __('Share', GEODIRECTORY_FRAMEWORK); ?></a>
                    <?php } ?>
                    <?php if ($show_bookmark_btn) { ?>
                        <?php geodir_favourite_html($user_id, $post->ID); ?>
                    <?php } ?>
                        <?php
                        if (function_exists('geodir_load_translation_geodirclaim') && $show_business_owner_btn) {
                            $geodir_post_type = array();
                            if(get_option('geodir_post_types_claim_listing'))
                                $geodir_post_type =	get_option('geodir_post_types_claim_listing');
                            $posttype = (isset($post->post_type)) ? $post->post_type : '';
                            if(in_array($posttype, $geodir_post_type) && !$preview) {
                                $is_owned = geodir_get_post_meta( $post->ID, 'claimed', true );
                                if ( get_option( 'geodir_claim_enable' ) == 'yes' && $is_owned == '0' ) {

                                    if ( is_user_logged_in() ) {
                                        $user_link_display = get_option('geodir_disable_user_links_section');
                                        if ($user_link_display == '1') {
                                            echo '<div class="geodir-company_info">';
                                            echo '<input type="hidden" name="geodir_claim_popup_post_id" value="' . $post->ID . '" />';
                                            echo '<div class="geodir_display_claim_popup_forms"></div>';
                                            echo '<a href="javascript:void(0);" class="whoop-btn whoop-btn-small geodir_claim_enable"><i class="fa fa-question-circle"></i> ' . CLAIM_BUSINESS_OWNER . '</a>';
                                            echo '</div>';
                                        } else {
                                            echo '<a href="javascript:void(0);" class="whoop-btn whoop-btn-small geodir_claim_enable"><i class="fa fa-question-circle"></i> ' . CLAIM_BUSINESS_OWNER . '</a>';
                                        }

                                    } else {

                                        $site_login_url = geodir_login_url();
                                        echo '<a href="' . $site_login_url . '" class="whoop-btn whoop-btn-small"><i class="fa fa-question-circle"></i> ' . CLAIM_BUSINESS_OWNER . '</a>';

                                    }
                                }
                            }
                        }
                        ?>
                        <?php do_action('whoop_big_header_buttons_after'); ?>
	                </span>
                <?php } ?>
                <?php do_action('geodir-whoop-sharing-buttons-popup'); ?>
            </div>
        </div>
    </div>
    <?php
    if(function_exists('geodir_cpt_no_location')) {
        $hide_map = geodir_cpt_no_location( $post->post_type );
    } else {
        $hide_map = false;
    }
    $hide_map = apply_filters('whoop_detail_page_hide_map', $hide_map);
    ?>
    <div class="geodir-listing-map-div geodir-common clearfix" style="<?php if ($hide_map) { echo 'max-width:660px;';}?>">
        <?php if (!$hide_map) { ?>
        <div class="geodir-listing-map-inner-div">
            <div class="geodir-listing-map-box-div">
                <?php
                $img_map_args = array();
                $address_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
                $address_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
                $mapview = isset($post->post_mapview) ? $post->post_mapview : 'ROADMAP';
                $mapzoom = isset($post->post_mapzoom) ? $post->post_mapzoom : '';
                if (!$mapzoom) {
                    $mapzoom = 12;
                }
                $data = isset($post->marker_json) ? $post->marker_json : '';
                $map_json = json_decode($data);
                $img_map_args['center'] = $address_latitude . ',' . $address_longitude;
                $img_map_args['zoom'] = $mapzoom;
                $img_map_args['maptype'] = $mapview;
                $img_map_args['size'] = '300x150';
                $img_map_args['format'] = 'JPEG';
                $img_map_args['sensor'] = false;
                $img_map_args['markers'] = $address_latitude . ',' . $address_longitude;
                $google_key = geodir_get_map_api_key();
                if ($google_key) {
                    $img_map_args['key'] = $google_key;
                }

                $img_old_url = 'http://maps.googleapis.com/maps/api/staticmap';
                $img_url = esc_url(add_query_arg($img_map_args, $img_old_url));
                ?>
                <img src="<?php echo $img_url; ?>" alt=""/>

                <?php
                $display_info = apply_filters('whoop_display_info_below_map_image', true);
                if ($display_info) {
                    $html = whoop_get_address_html( $post );

                    if ( $post->geodir_contact ) {
                        $html .= '<span><i class="fa fa-phone"></i><a href="tel:' . $post->geodir_contact . '" target="_blank" rel="nofollow"> ' . $post->geodir_contact . '</a></span><br>';
                    }
                    if ( $post->geodir_website ) {
                        $html .= '<span><i class="fa fa-link"></i><a href="' . $post->geodir_website . '" target="_blank" rel="nofollow">' . __( ' Website', GEODIRECTORY_FRAMEWORK ) . '</a></span><br>';
                    }
                    echo $html;
                }
                ?>
            </div>
        </div>
        <?php } ?>
        <div class="geodir-listing-slider-div">
            <?php do_action('geodir-whoop-listing-slider-div'); ?>
        </div>
    </div>
<?php
}


remove_action('geodir_details_main_content', 'geodir_action_page_title', 20);
remove_action('geodir_details_main_content', 'geodir_action_details_taxonomies', 40);
add_action('geodir-whoop-listing-taxnomies', 'geodir_action_details_taxonomies', 40);
remove_action('geodir_details_main_content', 'geodir_action_details_slider', 30);
add_action('geodir-whoop-listing-slider-div', 'geodir_whoop_details_slider', 30);

//BuddyPress

function bp_get_displayed_user_displayname()
{
    global $bp;
    $uname = $bp->displayed_user->fullname;

    if (empty($name)) {
        $name_stack = array(
            'display_name',
            'user_nicename',
            'user_login'
        );
        foreach ($name_stack as $source) {
            if (!empty($bp->displayed_user->userdata->{$source})) {
                $uname = $bp->displayed_user->userdata->{$source};
                break;
            }
        }
    }
    return $uname;
}

add_filter('bp_member_name', 'whoop_bp_member_name', 10, 1);
add_filter('get_comment_author', 'whoop_bp_member_name');

//To display user location on home page userinfo widget
function whoop_get_user_location($user_id)
{
    if (class_exists('BuddyPress') && function_exists('xprofile_get_field_data')) {
        $location = xprofile_get_field_data(xprofile_get_field_id_from_name('My Hometown'), $user_id);
    } else {
        $default_location = geodir_get_default_location();
        $location = $default_location->city;
    }
    return $location;
}

//enqueue follow js for review hover links
function whoop_bp_follow_js() {
    if (is_single()) {
        wp_enqueue_script('bp-follow-js');
    }
}
add_action( 'wp_enqueue_scripts', 'whoop_bp_follow_js' );

// Change some GD settings
add_action( 'after_switch_theme', 'whoop_default_geodirectory_settings' );
function whoop_default_geodirectory_settings() {
    update_option('geodir_set_as_home', '1');
    update_option('geodir_width_home_contant_section', '67');
    update_option('geodir_width_listing_contant_section', '67');
    update_option('geodir_width_search_contant_section', '67');
    update_option('geodir_width_author_contant_section', '67');
    update_option('geodir_listing_view', 'listview');
    update_option('geodir_search_view', 'listview');
    update_option('geodir_author_view', 'listview');
}

// add listing-preview body class
function listing_preview_body_class( $classes ) {
    global $preview;
    if (is_page() && $preview) {
        $classes[] = 'listing-preview';
    }
    return $classes;
}
add_filter( 'body_class', 'listing_preview_body_class' );

function whoop_get_friend_count_for_user($user_id=''){
    if(!$user_id){$user_id = get_current_user_id();}

    if(!$user_id){return '0';}

    /* This is stored in 'total_friend_count' usermeta.
     This function will recalculate, update and return. */
    global $wpdb;
    $count = get_user_meta($user_id, 'total_friend_count', true);

    if(!$count){return '0';}
    else{return $count;}
}

function whoop_add_custom_styles() {
    global $gdf;
    if(empty($gdf)) {
        return;
    }
    ?>
    <style>
        <?php
        if (!$gdf['header-gdf-fixed']) {

            if ( !is_active_sidebar( 'header-right' ) ) {
                $geodir_wrapper_top = 76;
            } else {
                $geodir_wrapper_top = 89;
            }

            $header_top = 0;
            if (!$gdf['head-gdf-adminbar'] && !$gdf['head-gdf-adminbar-fixed']) {
                $header_top = 31;
            }
            if (!$gdf['head-wp-adminbar']) {
                $header_top = 32;
            }
        ?>
        @media only screen and (min-width: 768px) {
            .header {
                position: fixed;
                width: 100%;
                top: <?php echo $header_top; ?>px;
                left: 0;
            }
            #geodir_wrapper {
                margin-top: <?php echo $geodir_wrapper_top; ?>px;
            }
        }
        <?php
        } ?>

        <?php
        if ($gdf['header-button-color'] OR $gdf['header-button-color-hover']) {
        ?>
        .header-right-area .geodir_submit_search,
        a.whoop-account-dd-link {
            background-image:none;
        }
        <?php
        } ?>

        <?php
        if ($header_button_border_color = $gdf['header-button-border-color']) {
        ?>
        #mobile-navigation-left ul li a:hover,
        a.whoop-login-btn:hover {
            -webkit-box-shadow: inset 0 1px 0 <?php echo $header_button_border_color; ?>, 0 1px 0 <?php echo $header_button_border_color; ?>;
            box-shadow: inset 0 1px 0 <?php echo $header_button_border_color; ?>, 0 1px 0 <?php echo $header_button_border_color; ?>;
        }
        <?php
        } ?>
    </style>
<?php
}
add_action( 'wp_head', 'whoop_add_custom_styles' );


function whoop_bp_compliments_item_user_name($name, $user) {
    $name = whoop_bp_member_name(whoop_get_current_user_name($user));
    return $name;
}
add_filter('bp_compliments_item_user_name', 'whoop_bp_compliments_item_user_name', 10, 2);

function whoop_bp_compliments_after_user_name($author_id) {
    whoop_get_user_stats($author_id);
}
add_action('bp_compliments_after_user_name', 'whoop_bp_compliments_after_user_name', 10, 1);

function whoop_bp_before_member_compliments_content() {
   ?>
    <div class="whoop-review-header whoop-event-header-wrap">
        <div class="whoop-title-and-count">
            <h3 class="whoop-tab-title">
                <?php echo bp_get_displayed_user_displayname() . '\'s '.BP_COMP_PLURAL_NAME; ?>
            </h3>
        </div>
    </div>
<?php
}
add_action('bp_before_member_compliments_content', 'whoop_bp_before_member_compliments_content');

function whoop_geodir_show_post_address($html, $variables_array) {
    if (geodir_is_page('listing') || geodir_is_page('search')) {
        $html = '';
    }
    return $html;
}
add_filter('geodir_show_post_address', 'whoop_geodir_show_post_address', 10, 2);

function whoop_geodir_show_geodir_contact($html, $variables_array) {
    if (geodir_is_page('listing') || geodir_is_page('search')) {
        $html = '';
    }
    return $html;
}
add_filter('geodir_show_geodir_contact', 'whoop_geodir_show_geodir_contact', 10, 2);

function whoop_geodir_show_geodir_website($html, $variables_array) {
    if (geodir_is_page('listing') || geodir_is_page('search')) {
        $html = '';
    }
    return $html;
}
add_filter('geodir_show_geodir_website', 'whoop_geodir_show_geodir_website', 10, 2);
