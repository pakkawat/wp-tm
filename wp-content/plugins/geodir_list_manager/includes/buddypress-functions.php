<?php
/*
 * Buddypress functions
 */
function gdlists_bp_user_lists_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    $screen_function = apply_filters('gdlists_bp_screen_function', 'gdlists_bp_user_lists');

    bp_core_new_nav_item(
        array(
            'name' => __('Lists', 'geodirlists'),
            'slug' => 'lists',
            'position' => 21,
            'show_for_displayed_user' => true,
            'screen_function' => $screen_function,
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'public'
        ));
}

add_action('bp_setup_nav', 'gdlists_bp_user_lists_nav_adder');

function gdlists_bp_user_lists()
{
    add_action('bp_template_content', 'gdlists_bp_user_lists_content');
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}


function geodir_get_lists_by_user_id($user_id = 0) {

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

function gdlists_view_url_buddypress($view_url, $pid) {
    $view_url = esc_url(add_query_arg(array('list_id' => $pid), bp_get_loggedin_user_link().'lists/'));
    return $view_url;
}
add_filter('gdlists_list_view_url', 'gdlists_view_url_buddypress', 10, 2);

function gdlists_bp_user_lists_content()
{
    global $bp;
    $user_id = $bp->displayed_user->id;

    if (!$user_id) {
        return;
    }

    if(isset($_GET['list_id']) && $_GET['list_id'] != '') {
        $pid = (int)sanitize_text_field(esc_sql($_GET['list_id']));
        gdlists_bp_lists_tab_content_single($pid);
    } else {
        gdlists_bp_lists_tab_content_loop($user_id);
    }

}

function gdlists_bp_lists_tab_content_single($pid) {
    ?>
    <h3 class="gdlists-tab-title"><?php echo get_the_title($pid); ?></h3>
    <div class="gdlists-tab-list-content">
        <?php echo get_post_field('post_content', $pid); ?>
    </div>
    <?php gdlist_get_listings($pid); ?>

    <?php
}

function gdlists_bp_lists_tab_content_loop($user_id) {
    global $wp_query;
    // Define custom query parameters
    $custom_query_args = array(
        'posts_per_page' => 100,
        'post_type' => 'gd_list',
        'author' => $user_id,
    );

    // Get current page and append to custom query parameters array
    $custom_query_args['paged'] = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

    // Instantiate custom query
    $custom_query = new WP_Query( $custom_query_args );

    // Pagination fix
    $temp_query = $wp_query;
    $wp_query   = NULL;
    $wp_query   = $custom_query;

    // Output custom query loop
    echo '<h2 class="gdlists-tab-title">'.sprintf( __( '%s\'s Lists', 'geodirlists' ), bp_core_get_user_displayname( $user_id ) ).'</h2>';
    echo '<div class="gd-list-content-wrap">';
    echo '<ul class="gd-list-item-wrap-ul">';
    if ( $custom_query->have_posts() ) :
        while ( $custom_query->have_posts() ) :
            $custom_query->the_post();
            global $post;
            ?>
            <li class="gd-list-item-wrap">
                <h3>
                    <a href="<?php echo esc_url(add_query_arg(array('list_id' => $post->ID), geodir_curPageURL())); ?>">
                        <?php echo get_the_title($post); ?>
                    </a>
                </h3>
                <p class="gd-list-item-desc">
                    <?php echo wp_trim_words(stripslashes(strip_tags(get_the_content($post))), 20); ?>
                </p>
            </li>
            <?php
        endwhile;
    else:
        ?>
        <li class="gd-list-item-wrap">
            <p>
                <?php _e( 'Oops, No listings Found!', 'geodirlists' ); ?>
            </p>
        </li>
        <?php
    endif;
    echo '</ul">';
    echo '</div">';
    // Reset postdata
    wp_reset_postdata();


    // Reset main query object
    $wp_query = NULL;
    $wp_query = $temp_query;
}

// When buddypress available redirect page to buddypess lists page
function gdlists_redirect_to_bp_page() {
    if ((class_exists('BuddyPress') && is_singular('gd_list')) && !isset($_GET['edit-list'])) {
        global $post;
        $post_id = $post->ID;
        $author_id = $post->post_author;

        $view_url = esc_url(add_query_arg(array('list_id' => $post_id), bp_core_get_user_domain( $author_id ).'lists/'));
        wp_redirect($view_url);
    }
}
add_action('template_redirect', 'gdlists_redirect_to_bp_page');