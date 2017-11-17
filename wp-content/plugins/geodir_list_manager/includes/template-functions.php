<?php
add_filter( 'template_include', 'add_list_page_template' );
function add_list_page_template( $template ) {

    $list_page_id = get_option('geodir_add_list_page');
    if ( is_page( $list_page_id )  ) {
        $template = locate_template( array( 'geodirectory/add-list.php' ) );
        if (!$template)
            $template = WP_PLUGIN_DIR . "/" . plugin_basename(dirname(dirname(__FILE__))) . '/geodirectory-templates/add-list.php';
        $template = apply_filters('geodir_template_add_list', $template);
    }
    return $template;
}

function get_single_gd_list_template($single_template) {
    global $post;

    if ($post->post_type == 'gd_list') {
        $single_template = WP_PLUGIN_DIR . "/" . plugin_basename(dirname(dirname(__FILE__))) . '/geodirectory-templates/single-gd_list.php';
    }
    return $single_template;
}
//add_filter( 'single_template', 'get_single_gd_list_template' );

function geodir_action_add_list_page_title()
{
    echo '<header class=""><h3>';

    if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
        echo apply_filters('geodir_add_listing_page_title_text', (geodir_ucwords(__('Edit List', 'geodirlists'))));
    } elseif (isset($listing_type)) {
        echo apply_filters('geodir_add_listing_page_title_text', (geodir_ucwords(__('Add List', 'geodirlists'))));
    } else {
        apply_filters('geodir_add_listing_page_title_text', the_title());
    }
    echo '</h3></header>';
}
add_action('geodir_add_list_page_title', 'geodir_action_add_list_page_title', 10);

add_action('geodir_add_list_form', 'geodir_action_add_list_form', 10);
function geodir_action_add_list_form()
{
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }
    $error = null;
    $pid = 0;
    if(isset($_GET['pid'])) {
        $pid = (int) sanitize_text_field(esc_sql($_GET['pid']));
        if($pid) {
            $list_post = get_post($pid);
            $list_author = $list_post->post_author;
            if (((get_current_user_id() == $list_author) || (current_user_can('edit_post', $pid))) && (get_post_type($pid) == 'gd_list')) {

            } else { ?>
                <p class="add-list-error">
                    <?php echo __('You don\'t have permission to edit this list.', 'geodirlists'); ?>
                </p>

                <?php
                return;
            }
        }
    }
    if(isset($_POST['add_list_submit'])) {
        if(!$_POST['post_title']) {
            $error = __('List title required', 'geodirlists');
        } else {
            $title = sanitize_text_field(esc_sql($_POST['post_title']));
            $desc = sanitize_text_field(esc_sql($_POST['post_desc']));
            $list_id = (int) sanitize_text_field(esc_sql($_POST['gdlist_id']));

            if ($list_id) {
                $list_post = get_post($list_id);
                $list_author = $list_post->post_author;
                if (((get_current_user_id() == $list_author) || (current_user_can('edit_post', $list_id))) && (get_post_type($list_id) == 'gd_list')) {
                    $pid = $list_id;
                } else {
                    $pid = 0;
                }
            }
            // Create post object
            $post_data = array(
                'ID'            => $pid,
                'post_title'    => $title,
                'post_content'  => $desc,
                'post_status'   => 'publish',
                'post_author'   => $user_id,
                'post_type' => 'gd_list'
            );

            $post_id = wp_insert_post( $post_data );
            $permalink = get_permalink( $post_id );
            $permalink = add_query_arg('edit-list', '1', $permalink);
            wp_redirect( $permalink );
            exit;
        }
    }

    if($error) {
        echo '<p class="add-list-error">'.$error.'</p>';
    }

    $title = '';
    $desc = '';
    ?>
    <form name="addlistform" id="propertyform" action="<?php echo get_page_link(get_option('geodir_add_list_page'));?>"
          method="post" enctype="multipart/form-data">
        <h5><?php
            if ($pid) {
                _e('Edit List Details', 'geodirlists');
                $title = get_the_title($pid);
                $desc = strip_tags(get_post_field('post_content', $pid));
                $submit_btn_text = __('Update', 'geodirlists');
            } else {
                _e('Enter List Details', 'geodirlists');
                $submit_btn_text = __('Create', 'geodirlists');
            }
            ?></h5>
        <div id="geodir_post_title_row" class="required_field geodir_form_row clearfix">
            <label><?php _e('List Title', 'geodirlists');?><span>*</span> </label>
            <input type="text" name="post_title" id="post_title" class="geodir_textfield"
                   value="<?php echo esc_attr(stripslashes($title)); ?>"/>
        </div>

        <?php
        $desc = esc_attr(stripslashes($desc));
        ?>

        <div id="geodir_post_desc_row" class="geodir_form_row clearfix">
            <label><?php _e('List Description', 'geodirlists');?></label>

            <textarea name="post_desc" id="post_desc" class="geodir_textarea"><?php echo $desc; ?></textarea>

        </div>

        <div id="geodir-add-listing-submit" class="geodir_form_row clear_both" align="center" style="padding:2px;">
            <input name="add_list_submit" type="submit" value="<?php echo $submit_btn_text; ?>"
                   class="geodir_button"/>
            <input type="hidden" name="gdlist_id" value="<?php echo $pid; ?>">
            <span class="geodir_message_note"
                  style="padding-left:0px;"> <?php _e('Note: You will be able to add items in the next page', 'geodirlists');?></span>
        </div>

    </form>
    <?php
}

function gdlist_enqueue_scripts(){
    if( is_single() && get_post_type()=='gd_list' ){
        wp_enqueue_script( 'jquery-ui-sortable' );
    }
}
add_action('wp_enqueue_scripts', 'gdlist_enqueue_scripts');

add_action('wp_footer', 'gdlist_create_connection_js');
function gdlist_create_connection_js() {
    if( is_single() && get_post_type() == 'gd_list' ) {
        $ajax_nonce = wp_create_nonce("gdlist-connection-nonce");
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("#gdlists-listSortable-left, #gdlists-listSortable-right").sortable({
                    connectWith: ".gdlists-listSortable"
                }).disableSelection();

                jQuery('form.gd_list_form').submit(function () {
                    jQuery("#gd_list_submit_btn").html('Saving...').prop('disabled', true);
                    var ids = jQuery("#gdlists-listSortable-right").sortable("serialize");
                    var cur_post_id = jQuery('#cur_post_id').val();
                    var data = {
                        'action': 'gdlist_create_connection',
                        'gdlist_connection_nonce': '<?php echo $ajax_nonce; ?>',
                        'ids': ids,
                        'cur_post_id': cur_post_id
                    };
                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                        jQuery("#gd_list_submit_btn").html('Saved').prop('disabled', false);
                    });
                    return false;
                });
            });
        </script>
        <?php
    }
}


// edit links in GD lists title
function add_edit_links_in_gdlists_title( $title, $id = null ) {

    if (class_exists('BuddyPress')) {
        if (is_buddypress()) {
            $is_bp_page = true;
        } else {
            $is_bp_page = false;
        }
    } else {
        $is_bp_page = false;
    }

    if (is_single()) {
        global $post;
        if ($post->ID == $id) {
            $is_main = true;
        } else {
            $is_main = false;
        }
    } else {
        $is_main = true;
    }
    
    if ($is_bp_page) {
        $enable_filter_in_bp_page = apply_filters('enable_list_title_filter_in_bp_page', true);
        if (!$enable_filter_in_bp_page) {
            return $title;
        }
    }
    if ((is_single() || $is_bp_page) && $id && get_post_type($id) == 'gd_list' && $is_main) {
        $list_post = get_post($id);
        $list_author = $list_post->post_author;
        $edit_list = isset($_GET['edit-list']) ? true : false;
        $list_url = esc_url(get_permalink($id));
        $view_url = apply_filters('gdlists_list_view_url', $list_url, $id);
        ob_start();
        if (((get_current_user_id() == $list_author) || (current_user_can('edit_post', $id)))) {

            ?>
            <?php if (isset($_GET['edit-list']) && !$is_bp_page) { ?>
            <a style="font-size: 10px;" href="<?php echo esc_url(add_query_arg(array('pid' => $id), home_url('/add-list/'))); ?>">
                <?php echo __('Edit Title and Desc', 'geodirlists'); ?>
            </a>
                <?php } ?>
            <ul class="gd-list-view-links">
            <?php if ($edit_list) { ?>
                <li><a href="<?php echo $view_url; ?>" class="gd-list-view-btn"><?php echo __('View List', 'geodirlists'); ?></a></li>
            <?php } else { ?>
                <li><a href="<?php echo add_query_arg('edit-list', '1', $list_url); ?>"><?php echo __('Edit List', 'geodirlists'); ?></a></li>
                <li><a href="<?php echo get_post_type_archive_link( 'gd_list' ); ?>"><?php echo __('All Lists', 'geodirlists'); ?></a></li>
            <?php } ?>
            </ul>
        <?php } else {
            ?>
            <ul class="gd-list-view-links">
                <?php
                if ($is_bp_page && isset($_GET['list_id'])) {
                    $bp_displayed_user_id = bp_displayed_user_id();
                    ?>
                    <li><a href="<?php echo bp_core_get_user_domain($bp_displayed_user_id).'lists/'; ?>"><?php echo bp_core_get_user_displayname($bp_displayed_user_id).__(' Lists', 'geodirlists'); ?></a></li>
                    <li><a href="<?php echo get_post_type_archive_link( 'gd_list' ); ?>"><?php echo __('All Lists', 'geodirlists'); ?></a></li>
                    <?php
                } elseif (is_single()) {
                    ?>
                    <li><a href="<?php echo add_query_arg('gd_lists', '1', get_author_posts_url($list_author)); ?>"><?php echo get_the_author_meta('display_name', $list_author).__(' Lists', 'geodirlists'); ?></a></li>
                    <li><a href="<?php echo get_post_type_archive_link( 'gd_list' ); ?>"><?php echo __('All Lists', 'geodirlists'); ?></a></li>
                    <?php
                }
                ?>
            </ul>
            <?php
        }
        $output = ob_get_contents();
        ob_end_clean();
        $title = $title.$output;
    }

    return $title;
}
add_filter( 'the_title', 'add_edit_links_in_gdlists_title', 10, 2);

// add gd lists content
function add_gdlists_content( $desc ) {
    $list_desc = $desc;
    global $post;
    if (!$post) {
        return $desc;
    }
    $p_id = $post->ID;
    $post_type = $post->post_type;

    if ($post_type != 'gd_list') {
        return $desc;
    }

    if ((is_single() && $post_type == 'gd_list') || isset($_GET['list_id'])) {
        $edit_list = isset($_GET['edit-list']) ? true : false;
        $cur_post_id = $p_id;
        $list_post = get_post($cur_post_id);
        $list_author = $list_post->post_author;
        ob_start();
        if ($edit_list && ((get_current_user_id() == $list_author) || (current_user_can('edit_post', $cur_post_id)))) {
            $all_posts = gdlist_get_all_reviewed_posts();
            $listed_posts = gdlist_get_all_listed_posts();
            $unlisted_posts = array_diff($all_posts, $listed_posts);
            ?>
            <h4><?php echo __('Drag and drop items to create or re-arrange your list.', 'geodirlists') ?></h4>

            <div class="add-items-to-list">
                <h4><?php echo __('All Your Reviews:', 'geodirlists') ?></h4>
                <ul id="gdlists-listSortable-left" class="gdlists-listSortable">
                    <?php
                    foreach ($unlisted_posts as $id => $title) {
                        ?>
                        <li id="post_<?php echo $id; ?>" class="whoop-li-title">
                            <?php echo $title; ?>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <div class="add-items-to-list">
                <form name="addlistform" class="gd_list_form" id="propertyform" action="#" method="post">
                    <input type='hidden' id="cur_post_id" name='cur_post_id' value='<?php echo $cur_post_id; ?>'>
                    <h4><?php echo __('Your List:', 'geodirlists') ?></h4>
                    <button type="submit" id="gd_list_submit_btn" class="whoop-btn whoop-btn-small">
                        <?php echo __('Done', 'geodirlists') ?>
                    </button>
                    <ul id="gdlists-listSortable-right" class="gdlists-listSortable">
                        <?php
                        foreach ($listed_posts as $id => $title) {
                            ?>
                            <li id="post_<?php echo $id; ?>" class="whoop-li-title">
                                <?php echo $title; ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </form>
            </div>
        <?php } else {
            gdlist_get_listings($cur_post_id);
        }
        $output = ob_get_contents();
        ob_end_clean();
        if ($list_desc) {
            $list_desc .= '<div class="gdlists-line"></div>';
        }
        $list_desc .= $output;
    }

    return $list_desc;
}
add_filter( 'the_content', 'add_gdlists_content');

function gdlists_modify_related_listings_query($query_args) {
    unset($query_args['tax_query']);
    unset($query_args['post_type']);
    unset($query_args['gd_location']);
    return $query_args;
}

function gdlist_get_listings($cur_post_id) {
    global $gridview_columns, $post;
    $origi_post = $post;

    $listed_posts = gdlist_get_all_listed_posts($cur_post_id);
    $post_ids = array_keys($listed_posts);

    if (empty($post_ids)) {
        _e( 'Oops, No listings Found!', 'geodirlists' );
    } else {
        $query_args = array(
            'posts_per_page' => 100,
            'is_geodir_loop' => true,
            'gd_location' => false,
            'post_type' => geodir_get_posttypes(),
            'post__not_in' => array($cur_post_id),
            'post__in' => $post_ids,
        );
        $layout = 'geodir-listview';

        $layout = apply_filters('gdlists_grid_view_layout', $layout);


        query_posts($query_args);

        if (strstr($layout, 'gridview')) {
            $listing_view_exp = explode('_', $layout);
            $gridview_columns = $layout;
            $layout = $listing_view_exp[0];
        } else if ($layout == 'list') {
            $gridview_columns = '';
        }


        $template = apply_filters("geodir_template_part-gdlists-listing-listview", geodir_locate_template('listing-listview'));


        include($template);

        wp_reset_query();
        $post = $origi_post;
    }
}

function gd_lists_author_title($title) {
    if ( isset( $_REQUEST['gd_lists'] ) && is_author()) {
        $title = sprintf( __( 'Lists By: %s', 'geodirlists' ), '<span class="vcard">' . get_the_author() . '</span>' );
    }
    return $title;
}
add_filter( 'get_the_archive_title', 'gd_lists_author_title');