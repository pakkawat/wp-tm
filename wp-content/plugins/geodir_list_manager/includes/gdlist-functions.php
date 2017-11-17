<?php
function gd_list_post_type() {
    if ( ! post_type_exists('gd_list') ) {
        $labels = array (
            'name'          => __('Lists', 'geodirlists'),
            'singular_name' => __('List', 'geodirlists'),
            'add_new'       => __('Add New', 'geodirlists'),
            'add_new_item'  => __('Add New List', 'geodirlists'),
            'edit_item'     => __('Edit List', 'geodirlists'),
            'new_item'      => __('New List', 'geodirlists'),
            'view_item'     => __('View List', 'geodirlists'),
            'search_items'  => __('Search Lists', 'geodirlists'),
            'not_found'     => __('No List Found', 'geodirlists'),
            'not_found_in_trash' => __('No List Found In Trash', 'geodirlists') );

        $args = array (
            'labels' => $labels,
            'can_export' => true,
            'capability_type' => 'post',
            'description' => __('List post type.', 'geodirlists'),
            'has_archive' => 'lists',
            'hierarchical' => false,
            'map_meta_cap' => true,
            'public' => true,
            'query_var' => true,
            'rewrite' => array ('slug' => 'lists', 'with_front' => false, 'hierarchical' => true),
            'supports' => array( 'title', 'editor', 'author')
        );
        register_post_type(__('gd_list', 'geodirlists'), $args);

    }
}
add_action( 'init', 'gd_list_post_type' );

function gd_list_p2p_connection() {

    $all_postypes = geodir_get_posttypes();

    if (!$all_postypes) {
        $all_postypes = array('gd_place');
    }
    foreach ($all_postypes as $pt) {
        p2p_register_connection_type(
            array(
                'name'  => $pt.'_to_gd_list',
                'from'  => $pt,
                'to'    => 'gd_list',
                'admin_box' => array(
                    'show' => 'to',
                    'context' => 'side'
                )
            )
        );
    }

}
add_action( 'p2p_init', 'gd_list_p2p_connection');


function gd_list_create_pages()
{
    $list_page_id = get_option('geodir_add_list_page');
    if(!$list_page_id || ( FALSE === get_post_status( $list_page_id ))) {
        include_once(geodir_plugin_path() . '/geodirectory-admin/admin_install.php');
        geodir_create_page(esc_sql(_x('add-list', 'page_slug', 'geodirlists')), 'geodir_add_list_page', __('Add List', 'geodirlists'), '');
    }
}
//register_activation_hook( __FILE__, 'gd_list_create_pages', 99 );

function gdlist_create_connection_for_each_post($cur_post_id, $post_ids) {
    $listed_posts = gdlist_get_all_listed_posts($cur_post_id);

    $listed_post_ids = array();
    foreach($listed_posts as $key => $title) {
        $listed_post_ids[] = (string) $key;
    }

    $removed_ids = array_diff($listed_post_ids, $post_ids);
    $added_ids = array_diff($post_ids, $listed_post_ids);

    $list_post = get_post($cur_post_id);
    $list_author = $list_post->post_author;

    if ( get_current_user_id() == $list_author || current_user_can('edit_post', $cur_post_id) ) {
        foreach($added_ids as $pid) {
            $con_type = get_post_type( $pid ).'_to_gd_list';
            $args = array(
                'from' => $pid,
                'to' => $cur_post_id
            );

            p2p_create_connection($con_type, $args);
        }

        foreach($removed_ids as $pid) {
            $con_type = get_post_type( $pid ).'_to_gd_list';
            $args = array(
                'from' => $pid,
                'to' => $cur_post_id
            );

            p2p_delete_connections($con_type, $args);
        }
    }
}

function gdlist_create_connection() {
    check_ajax_referer('gdlist-connection-nonce', 'gdlist_connection_nonce');
    //set variables
    $ids = strip_tags(esc_sql($_POST['ids']));
    $cur_post_id = strip_tags(esc_sql($_POST['cur_post_id']));
    $post = array();
    if(empty($ids)) {
        $post_ids = array();
    } else {
        parse_str($ids);
        $post_ids = $post;
    }

    gdlist_create_connection_for_each_post($cur_post_id, $post_ids);
    wp_die();
}
add_action('wp_ajax_gdlist_create_connection', 'gdlist_create_connection');


function gdlist_all_listed_posts($post_id = null) {
    if ($post_id) {
        $post = get_post($post_id);
    } else {
        global $post;
    }
    global $bp;
    $user_id = $bp->displayed_user->id;
    $listed_posts = array();
    $all_postypes = geodir_get_posttypes();
    foreach ($all_postypes as $pt) {
        $args = array(
            'connected_type' => $pt.'_to_gd_list',
            'connected_items' => $post,
        );
        $args['posts_per_page'] = 3;
        $connected = new WP_Query($args);
        while ( $connected->have_posts() ) : $connected->the_post();
            ?>
            <li>
                <div class="gd-list-post-thumb">
                    <?php echo get_the_post_thumbnail( $post->ID, array( 20, 20) ); ?>
                </div>
                <a href="<?php echo get_the_permalink(); ?>">
                    <?php echo get_the_title(); ?>
                </a>
                <br/>
                <em>
                    <?php
                    $args = array(
                        'status' => 'approve',
                        'number' => 1,
                        'parent' => 0,
                        'post_id' => $post->ID, // use post_id, not post_ID
                        'user_id' => $user_id
                    );
                    $comments = get_comments($args);
                    if ($comments) {
                        ?>
                        <?php
                        foreach($comments as $comment) {
                            ?>
                            <?php echo wp_trim_words(stripslashes(strip_tags($comment->comment_content)), 20); ?>
                            <?php
                        }
                        ?>
                    <?php } ?>
                </em>
            </li>
            <?php
        endwhile;
        wp_reset_postdata(); // set $post back to original post
    }
    return $listed_posts;
}

function gdlist_get_all_listed_posts($post_id = null) {
    if ($post_id) {
        $post = get_post($post_id);
    } else {
        global $post;
    }
    $listed_posts = array();
    $all_postypes = geodir_get_posttypes();
    foreach ($all_postypes as $pt) {
        $connected = new WP_Query( array(
            'connected_type' => $pt.'_to_gd_list',
            'connected_items' => $post,
            'nopaging' => true
        ) );
        while ( $connected->have_posts() ) : $connected->the_post();
            $listed_posts[get_the_ID()] = get_the_title();
        endwhile;
        wp_reset_postdata(); // set $post back to original post
    }
    return $listed_posts;
}

function gdlist_get_all_reviewed_posts() {
    $p_ids = gdlist_get_user_reviewed_posts();
    $post_types = geodir_get_posttypes();
    $all_posts = array();
    if ($p_ids) {
        $query_args = array(
            'post_type' => $post_types,
            'posts_per_page' => 100
        );
        $query_args['post__in'] = $p_ids;
        $listings = new WP_Query($query_args);

        if ($listings) {
            while ( $listings->have_posts() ) : $listings->the_post();
                $all_posts[get_the_ID()] = get_the_title();
            endwhile;
        }
        wp_reset_postdata();
    }
    return $all_posts;
}

function gdlist_get_user_reviewed_posts() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return false;
    }
    global $wpdb, $tablecomments, $tableposts;
    $tablecomments = $wpdb->comments;
    $tableposts = $wpdb->posts;
    $review_table = GEODIR_REVIEW_TABLE;

    $where = $wpdb->prepare("WHERE post_status=%d AND status=%d AND overall_rating>%d AND user_id=%d ", array(1, 1, 0, $user_id));

    $query = "SELECT post_id FROM $review_table $where";

    $items_per_page = 100;

    $page = isset($_GET['rpage']) ? abs((int)strip_tags(esc_sql($_GET['rpage']))) : 1;
    $offset = ($page * $items_per_page) - $items_per_page;
    $results = $wpdb->get_results($query . " GROUP BY post_id LIMIT ${offset}, ${items_per_page}");

    $p_ids = array();
    foreach($results as $result) {
        $p_ids[] = $result->post_id;
    }

    return $p_ids;
}

function gdlist_single_loop_item() {
    global $post;
    ?>
    <li class="gd-list-item-wrap">
        <h3 class="whoop-tab-title">
            <a href="<?php echo esc_url(add_query_arg(array('list_id' => $post->ID), geodir_curPageURL())); ?>">
                <?php echo get_the_title($post); ?>
            </a>
                <span class="gd-list-item-count">

                </span>
        </h3>
        <p class="gd-list-item-desc">
            <?php echo wp_trim_words(stripslashes(strip_tags(get_the_content($post))), 20); ?>
        </p>
        <ul class="gd-list-item-comments">
            <?php gdlist_all_listed_posts(); ?>
        </ul>
    </li>
    <?php
}

add_action( 'pre_get_posts', 'gd_lists_by_author' );
function gd_lists_by_author($query){
    if ( isset( $_REQUEST['gd_lists'] ) && is_author() && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'gd_list' ) );
    }
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 0.0.5
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function gd_list_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(dirname(__FILE__)));
    
    return $settings;
}