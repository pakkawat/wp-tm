<?php
function geodir_whoop_details_slider()
{
    global $post, $preview;

    $nav_slides = '';
    $slides = 1;

    if ($preview) {
        $p_images = explode(',', $post->post_images);
        $post_images = array();
        $p_count = 1;
        foreach($p_images as $p_image) {
            if ($p_count > 3) {
                break;
            }
            $post_images[] = (object)array(
                'id' => null,
                'title' => null,
                'src' => $p_image
            );
            $p_count++;
        }
        $post_images = (object)$post_images;
    } else {
        $post_images = geodir_get_images($post->ID, 'medium', get_option('geodir_listing_no_img'));
    }

    if (empty($post_images) && get_option('geodir_listing_no_img')) {
        $post_images = (object)array((object)array('src' => get_option('geodir_listing_no_img')));
    }

    $start_image_id = null;
    if (!empty($post_images)) {
        $total_image = count((array)$post_images);
        foreach ($post_images as $image) {
            $nav_slides .= '<div class="whoop-photo whoop-photo-' . $slides . '">'.geodir_show_image(array('src' => $image->src), 'whoop-carousel-thumb', false, false).'';
            if (!$preview && $slides == 3 && $total_image > 3) {
                $nav_slides .= '<a class="whoop-show-all-overlay" href="' . esc_url(add_query_arg(array("biz_photos" => $image->id), get_permalink())) . '"><i class="fa fa-th"></i>' . sprintf( __( 'See all %d photos', GEODIRECTORY_FRAMEWORK ), $total_image ) . '</a>';
            } else {
                $nav_slides .= '<a class="whoop-show-all-overlay" style="background:none !important;" href="' . esc_url(add_query_arg(array("biz_photos" => $image->id), get_permalink())) . '"></a>';
            }
            $nav_slides .= '</div>';
            $slides++;
            if ($slides > 3) {
                break;
            }
        }
    }

    if (!empty($post_images)) {
        ?>
        <?php if ($slides > 1) { ?>
            <div id="geodir_carousel_whoop" class="geodir_flexslider_whoop">
                <div class="whoop-img-track">
                    <?php echo $nav_slides; ?>
                </div>
            </div>
        <?php } ?>
    <?php
    }
}

function gedir_whoop_js()
{
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('a.whoop-account-dd-link').click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                jQuery('.whoop-account-details').toggle();
            });
            jQuery(document).click(function (e) {
                if (e.target.class != 'whoop-account-details' && !jQuery('.whoop-account-details').find(e.target).length) {
                    jQuery('.whoop-account-details').hide();
                }
            });

            jQuery('a.whoop-share-button').click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                jQuery('.whoop-sharing-buttons').toggle();
            });
            jQuery(document).click(function (e) {
                if (e.target.class != 'whoop-sharing-buttons' && !jQuery('.whoop-sharing-buttons').find(e.target).length) {
                    jQuery('.whoop-sharing-buttons').hide();
                }
            });
        });
    </script>
<?php
}

add_action('wp_footer', 'gedir_whoop_js');

function whoop_biz_photos_template()
{
    $templates[] = 'geodirectory/geodir-bizphotos.php';
    return locate_template($templates);
}

if (isset($_GET['biz_photos']) || isset($_GET['add_biz_photo'])) {
    remove_action('geodir_detail_before_main_content', 'geodir_whoop_big_header', 30);
    add_filter('geodir_template_detail', 'whoop_biz_photos_template');
}

function geodir_biz_photos_gallery()
{
    global $post;
    $post_images = geodir_get_images($post->ID, 'thumbnail', get_option('geodir_listing_no_img'));

    $p_images = array();
    if (!empty($post_images)) {
        foreach ($post_images as $image) {
            $img_url = $image->src;
            if (!$img_url) {
                $img_url = get_option('geodir_listing_no_img');
            }
            $p_images[$image->id] = array(
                'src' => $img_url,
                'post_author' => $image->user_id ? $image->user_id : $post->post_author,
                'post_title' => $image->caption,
                'post_date' => $post->post_date
            );
        }
    }

    $slides = 1;
    $nav_slides = "";

    $biz_photo_id = null;
    if (isset($_GET['biz_photos']) && $_GET['biz_photos'] != '') {
        $biz_photo_id = strip_tags($_GET['biz_photos']);
    }

    $selected_img = null;
    $total_image = null;

    if (!empty($p_images)) {
        $total_image = count((array)$p_images);
        foreach ($p_images as $key => $image) {
            if ($biz_photo_id && $biz_photo_id == $key) {
                $selected_img = $image;
                $nav_slides .= '<div class="whoop-biz-photo selected">';
            } else {
                $nav_slides .= '<div class="whoop-biz-photo">';
            }
            $author_id = (int) $image['post_author'];
            $user = get_user_by('id', $author_id);
            $name = whoop_bp_member_name(whoop_get_current_user_name($user));
            if (class_exists('BuddyPress')) {
                $user_link = bp_core_get_user_domain($author_id);
            } else {
                $user_link = get_author_posts_url( $author_id );
            }
            $nav_slides .= '<div class="whoop-biz-photo-wrap">';
            $nav_slides .= '<div class="whoop-biz-photo-box">';
            $nav_slides .= '<a href="' . esc_url(add_query_arg(array("biz_photos" => $key), get_permalink())) . '"><div style="width:100px;height:100px;display:block;background-image:url(' . $image['src'] . ');background-position:center;border-radius: 4px;background-size:cover;"></div></a>';
            $nav_slides .= '</div>';
            $nav_slides .= '<div class="whoop-biz-p-caption">';
            $nav_slides .= '<p class="smaller">';
            $nav_slides .= sprintf( __( 'From <a href="%s">%s</a>', GEODIRECTORY_FRAMEWORK ), $user_link, $name );
            $nav_slides .= '</p>';
            $nav_slides .= '<p class="smaller">';
            $nav_slides .= $image['post_title'];
            $nav_slides .= '</p>';
            $nav_slides .= '</div>';
            $nav_slides .= '</div>';
            $nav_slides .= '</div>';
            $slides++;
        }
    }// endfore

    ?>
    <div class="geodir-biz-header-wrap">
        <h2 class="geodir-biz-header-title"><a href="<?php echo get_the_permalink($post->id); ?>"><?php echo get_the_title($post->id); ?></a>  > <?php echo __('Photos', GEODIRECTORY_FRAMEWORK); ?></h2>
        <?php
        if (is_user_logged_in()) {
        $package_info = array();
        $package_info = geodir_post_package_info($package_info, $post);

        $image_limit = $package_info->image_limit;

        $thumb_img_arr = array();
        $thumb_img_arr = geodir_get_images($post->ID);

        $totImg = count((array)$thumb_img_arr);

        if ($image_limit == '' || $totImg < (int) $image_limit) {
         ?>
    <div class="whoop-add-button-wrap">
        <a href="<?php echo esc_url(add_query_arg(array("add_biz_photo" => '1'), get_permalink())); ?>" class="whoop-btn whoop-btn-primary">
            <i class="fa fa-star"></i> <?php echo __('Add Photos', GEODIRECTORY_FRAMEWORK); ?></a>
    </div>
<?php }
} ?>
    </div>
    <?php
    if (!empty($selected_img)) {
        $post_ids = array();
        foreach($p_images as $key => $value) {
            $post_ids[] = $key;
        }
        $current_id = (int) $biz_photo_id;
        $current_index = array_search($current_id, $post_ids);

        $next = $current_index + 1;
        $prev = $current_index - 1;
        ?>
        <div class="whoop-biz-nav-wrap">
            <div class="whoop-biz-nav-links">
        <?php if ($prev >= 0) { ?>
            <a href="<?php echo esc_url(add_query_arg(array("biz_photos" => $post_ids[$prev]), get_permalink())); ?>" class="whoop-btn whoop-btn-primary whoop-btn-small"><?php echo __('Prev', GEODIRECTORY_FRAMEWORK); ?></a>
        <?php } else { ?>
            <a href="#" class="whoop-btn whoop-btn-primary whoop-btn-small whoop-btn-disabled"><?php echo __('Prev', GEODIRECTORY_FRAMEWORK); ?></a>
        <?php } ?>
            <span class="whoop-media-nav-count"><strong><?php echo $next; ?></strong> of <strong><?php echo $total_image; ?></strong></span>
        <?php if ($next < count($post_ids)) { ?>
            <a href="<?php echo esc_url(add_query_arg(array("biz_photos" => $post_ids[$next]), get_permalink())); ?>" class="whoop-btn whoop-btn-primary whoop-btn-small"><?php echo __('Next', GEODIRECTORY_FRAMEWORK); ?></a>
        <?php } else { ?>
            <a href="#" class="whoop-btn whoop-btn-primary whoop-btn-small whoop-btn-disabled"><?php echo __('Next', GEODIRECTORY_FRAMEWORK); ?></a>
        <?php } ?>
            </div>
        </div>
        </div>
        <div class="geodir-whoop-media-container">
            <div class="geodir_whoop_biz_photo_selected">
                <div class="geodir_whoop_biz_photo_selected-info">
                    <div class="gd-list-item-author">
                        <div class="comment-meta comment-author vcard">
                            <?php
        $author_id = (int) $selected_img['post_author'];
        $user = get_user_by('id', $author_id);
        $name = whoop_bp_member_name(whoop_get_current_user_name($user));
        if (class_exists('BuddyPress')) {
            $user_link = bp_core_get_user_domain($author_id);
        } else {
            $user_link = get_author_posts_url( $author_id );
        }
        ?>
        <?php echo get_avatar($author_id, 60); ?>
                            <cite><b class="reviewer">
                                    <a href="<?php echo $user_link; ?>" class="url"><?php echo $name; ?></a>
                                </b>
                            </cite>
                            <?php whoop_get_user_stats($author_id); ?>
                        </div>
                    </div>
                    <p class="whoop_photo_caption">
                        <?php echo $selected_img['post_title']; ?>
                    </p>
                    <p class="whoop_photo_timestamp">
                        <?php echo date_i18n('F j, Y', strtotime( $selected_img['post_date'] )); ?>
                    </p>
                </div>
                <div class="geodir_whoop_biz_photo_selected-frame">
                    <div class="biz-photo-inner">
                        <div style="display: block;">
                            <img src="<?php echo $selected_img['src']; ?>" alt="selected image"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    if (!empty($post_images)) {
        ?>
        <div class="geodir_whoop_biz_photos_wrap">
            <div class="geodir_whoop_biz_photos">
                <?php echo $nav_slides; ?>
            </div>
        </div>
    <?php
    }
}

if (isset($_GET['biz_photos'])) {
    add_action('geodir_biz_photos_main_content', 'geodir_biz_photos_gallery');
}

function geodir_add_biz_photo_form() {
    global $post;
    $package_info = array();
    $package_info = geodir_post_package_info($package_info, $post);

    $image_limit = $package_info->image_limit;

    $post_id = $post->ID;

    $thumb_img_arr = array();
    $thumb_img_arr = geodir_get_images($post_id);

    $totImg = count((array)$thumb_img_arr);

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }

    $error = null;
    $success = null;
    ?>
    <div class="geodir-biz-header-wrap">
        <h2 class="geodir-biz-header-title"><?php echo __('Add Photo', GEODIRECTORY_FRAMEWORK); ?></h2>
    </div>

    <?php if ($image_limit == '' || $totImg < (int) $image_limit) { ?>
    <?php
    if (isset( $_POST['add_biz_image_nonce'], $post_id ) && wp_verify_nonce( $_POST['add_biz_image_nonce'], 'add_biz_image' )) {
        $caption = sanitize_text_field(esc_sql($_POST['image_caption']));
        if ($_FILES) {
            foreach ($_FILES as $file => $array) {
                if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                    $error = __('Image is required', GEODIRECTORY_FRAMEWORK);
                }

                //require the needed files
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                require_once(ABSPATH . "wp-admin" . '/includes/media.php');

                $post_data = array(
                    'post_title' => $caption,
                    'caption' => $caption,
                    'post_author' => $user_id
                );

                $attachment_id = media_handle_upload( 'biz_image', $post_id, $post_data );

                if ( is_wp_error( $attachment_id ) ) {
                    $error = __('Image upload error.', GEODIRECTORY_FRAMEWORK);
                } else {
                    $row_id = geodir_insert_user_image_data($attachment_id, $post_id, $user_id);
                    $permalink = esc_url(add_query_arg(array("biz_photos" => $row_id), get_permalink($post_id)));
                    wp_redirect( $permalink );
                    exit;
                    //$success = __('Image upload successful', GEODIRECTORY_FRAMEWORK);
                }
            }
        } else {
            $error = __('Image is required', GEODIRECTORY_FRAMEWORK);
        }
    }
    if($error) {
        echo '<p class="add-list-error">'.$error.'</p>';
    }
    if($success) {
        echo '<p class="add-list-success">'.$success.'</p>';
    }
    ?>
    <form name="addlistform" id="propertyform" action="#"
          method="post" enctype="multipart/form-data">
        <input type="hidden" name="pid" value="<?php echo $post->ID; ?>"/>
        <?php wp_nonce_field( 'add_biz_image', 'add_biz_image_nonce' ); ?>
        <div id="geodir_post_image_row" class="required_field geodir_form_row clearfix">
            <label><?php _e('Image', GEODIRECTORY_FRAMEWORK);?><span>*</span> </label>
            <input type="file" name="biz_image" id="biz_image" class="geodir_textfield" style="padding-left: 0;" multiple="false"/>
        </div>

        <div id="geodir_image_caption_row" class="geodir_form_row clearfix">
            <label><?php _e('Photo Caption', GEODIRECTORY_FRAMEWORK);?></label>
            <input type="text" name="image_caption" id="image_caption" class="geodir_textfield"/>
        </div>

        <div id="geodir-add-listing-submit" class="geodir_form_row clear_both" align="center" style="padding:2px;">
            <input name="add_image_submit" type="submit" value="<?php _e('Add Image', GEODIRECTORY_FRAMEWORK);?>"
                   class="geodir_button"/>
        </div>
    </form>
    <?php
    wp_reset_query();
    } else {
        echo '<p class="add-list-error">'.__('You cannot upload more images in this package.', GEODIRECTORY_FRAMEWORK).'</p>';
    }
}

function whoop_output_buffer() {
    ob_start();
}

if (isset($_GET['add_biz_photo'])) {
    add_action('init', 'whoop_output_buffer');
    add_action('geodir_biz_photos_main_content', 'geodir_add_biz_photo_form');
}

function geodir_insert_user_image_data($image_id, $post_id, $user_id) {
    global $wpdb;

    if (!$image_id OR !$post_id OR !$user_id) {
        return;
    }

    $image_data = wp_get_attachment_metadata( $image_id, true );

    $post_images = geodir_get_images($post_id);

    if ($post_images) {
        $menu_order = count($post_images) + 1;
    } else {
        $menu_order = 1;
    }

    $image_name_arr = explode('/', $image_data['file']);
    $filename = end($image_name_arr);
    if (strpos($filename, '?') !== false) {
        list($filename) = explode('?', $filename);
    }

    $img_name_arr = explode('.', $filename);

    $file_title = isset($img_name_arr[0]) ? $img_name_arr[0] : $filename;
    if (!empty($img_name_arr) && count($img_name_arr) > 2) {
        $new_img_name_arr = $img_name_arr;
        if (isset($new_img_name_arr[count($img_name_arr) - 1])) {
            unset($new_img_name_arr[count($img_name_arr) - 1]);
            $file_title = implode('.', $new_img_name_arr);
        }
    }
    $file_title = sanitize_file_name($file_title);

    $attachment = array();
    $attachment['post_id'] = $post_id;
    $attachment['user_id'] = $user_id;
    $attachment['title'] = $file_title;
    $attachment['caption'] = get_the_title($image_id);
    $attachment['content'] = '';
    $attachment['file'] = '/'.$image_data['file'];
    $attachment['mime_type'] = get_post_mime_type($image_id);
    $attachment['menu_order'] = $menu_order;
    $attachment['is_featured'] = 0;
    $attachment['is_approved'] = 0;

    $attachment_set = '';

    foreach ($attachment as $key => $val) {
        if ($val != '')
            $attachment_set .= $key . " = '" . $val . "', ";
    }

    $attachment_set = trim($attachment_set, ", ");

    $wpdb->query("INSERT INTO " . GEODIR_ATTACHMENT_TABLE . " SET " . $attachment_set);

    return $wpdb->insert_id;
}

function whoop_geodir_imagesizes($imagesizes) {
    $imagesizes['whoop-carousel-thumb'] = array('w' => 250, 'h' => 250);
    return $imagesizes;
}
add_filter('geodir_imagesizes', 'whoop_geodir_imagesizes');
