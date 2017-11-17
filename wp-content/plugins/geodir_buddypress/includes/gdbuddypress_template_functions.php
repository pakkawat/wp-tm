<?php
/**
 * Contains functions related to GeoDirectory BuddyPress Integration plugin templates.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 */

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * GeoDirectory BuddyPress Integration settings form content.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @param string $tab_name Tab name.
 */
function geodir_buddypress_get_option_form( $tab_name ) {
	switch ( $tab_name ) {
		case 'gdbuddypress_settings': {
			geodir_admin_fields( geodir_buddypress_settings() );
			?>
<p class="submit">
  <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'gdbuddypress' ); ?>" />
  <input type="hidden" name="subtab" value="gdbuddypress_settings" id="last_tab" />
</p>
</div>
<?php
		}
		break;		
	}// end of switch
}

/**
 * BuddyPress Listings Tab content.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @param array $args Query arguments.
 */
function geodir_buddypress_listings_html( $args = array() ) {
	wp_register_style( 'gdbuddypress-style.css', GEODIR_BUDDYPRESS_PLUGIN_URL . '/css/gdbuddypress-style.css', array(), GEODIR_BUDDYPRESS_VERSION );
	wp_enqueue_style( 'gdbuddypress-style.css');
	
	global $posts_per_page, $found_posts, $paged;
	$current_posts_per_page = $posts_per_page;
	$current_found_posts = $found_posts;
	$current_paged = $paged;
	
	$posts_per_page = $posts_per_page > 0 ? $posts_per_page : 5;
	
	$layout = get_option( 'geodir_buddypress_listings_layout', 'listview' );
	$posts_per_page = get_option( 'geodir_buddypress_listings_count', $posts_per_page );
	$excerpt = get_option( 'geodir_buddypress_listings_excerpt', 200 );
    
	$post_type = $args['post_type'];
	$layout = apply_filters( 'gdbp_listing_layout', $layout );
	$list_sort = apply_filters( 'gdbp_listing_list_sort', 'newest' );
	$posts_per_page = apply_filters( 'gdbp_listing_post_limit', $posts_per_page );
	$character_count = apply_filters( 'gdbp_list_character_count', $excerpt );
	$post_type_name = !empty( $args['post_type_name'] ) ? geodir_strtolower( $args['post_type_name'] ) : __( 'listings', 'gdbuddypress' );
	// sorting options
	add_action( 'geodir_before_listing', 'geodir_buddypress_display_sort_options' );
	add_filter( 'geodir_buddypress_posts_orderby', 'geodir_posts_orderby', 100, 1 );
	
	// pagination
	add_action( 'geodir_after_listing', 'geodir_buddypress_pagination', 20 );

	$query_args = array(
					'posts_per_page' => $posts_per_page,
					'is_geodir_loop' => true,
					'gd_location' 	 => false,
					'post_type' => $post_type,
					'order_by' => $list_sort
				);
	if ($post_type == 'gd_event') {
		if (is_user_logged_in() && get_current_user_id() == bp_displayed_user_id()) {
			
		} else {
			$query_args['geodir_event_type'] = 'upcoming';
			add_filter( 'geodir_filter_bp_listings_where', 'geodir_filter_event_widget_listings_where', 10, 2 );
		}
	} 
	
	if ( $character_count ) {
		$query_args['excerpt_length'] = $character_count;
	}
	
	if ( (bool)bp_is_current_component( 'favorites' ) ) {
		$query_args['filter_favorite'] = true;
	}
	
	global $gridview_columns_widget, $geodir_is_widget_listing;
	
	$query_args['count_only'] = true;
	$found_posts = geodir_buddypress_get_bp_listings( $query_args );
	
	$query_args['count_only'] = false;
	$widget_listings = geodir_buddypress_get_bp_listings( $query_args );
	$listing_view = $layout;
	if  ( strstr( $listing_view, 'gridview' ) ) {
		$gridview_columns_widget = $listing_view;
		$listing_view_exp = explode( '_', $listing_view );
		$listing_view = $listing_view_exp[0];
	}
	
	$template = apply_filters( "geodir_template_part-widget-listing-listview", geodir_plugin_path() . '/geodirectory-templates/widget-listing-listview.php' );
	###### MAIN CONTENT WRAPPERS OPEN ######
	// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
	do_action( 'geodir_wrapper_content_open', 'listings-page', 'geodir-wrapper-content', 'gdbp-wrapper-content gdbp-listings-page' );
	
	// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='',$itemtype='')
	do_action( 'geodir_main_content_open', 'listings-page', 'geodir-main-content', 'gdbp-main-content  gdbp-listings-page listings-page');
	
	if ( empty( $widget_listings ) ) {
		?>
		<div class="info" id="message"><p><?php echo wp_sprintf( __( 'There were no %s found.', 'gdbuddypress' ), $post_type_name ); ?></p></div>
		<?php
	} else {
		// currently set values
		global $post, $geodir_event_widget_listview, $map_jason, $map_canvas_arr, $posts_per_page, $found_posts, $paged;
		
		$current_post = $post;
		$current_map_jason = $map_jason;
		$current_map_canvas_arr = $map_canvas_arr;
		$geodir_is_widget_listing = true;
		$my_lisitngs = false;
		$old_event_widget_listview = $geodir_event_widget_listview;
		if ( bp_loggedin_user_id() && bp_displayed_user_id() == bp_loggedin_user_id() ) {
			$my_lisitngs = true;
			$_REQUEST['geodir_dashbord'] = true;
		}
		
		if ( $post_type == 'gd_event' ) {
			$geodir_event_widget_listview = true;
		}
		
		echo '<div class="clearfix">'; do_action( 'geodir_before_listing' ); echo '</div>';
		
		// all listings html
		include( $template );
				
		echo '<div class="clearfix">'; do_action( 'geodir_after_listing' ); echo '</div>';
		
		// release original values
		global $post, $geodir_event_widget_listview, $map_jason, $map_canvas_arr;
		
		$GLOBALS['post'] = $current_post;
		setup_postdata( $current_post );
		$geodir_event_widget_listview = $old_event_widget_listview;
		$map_jason = $current_map_jason;
		$map_canvas_arr = $current_map_canvas_arr;
		if ( $my_lisitngs ) {
			unset( $_REQUEST['geodir_dashbord'] );
		}
	}
	
	global $posts_per_page, $found_posts, $paged;
	$posts_per_page = $current_posts_per_page;
	$found_posts = $current_found_posts;
	$paged = $current_paged;
		
	do_action( 'geodir_main_content_close', 'listings-page' );
	
	###### MAIN CONTENT WRAPPERS CLOSE ######
	// this adds the closing html tags to the wrapper_content div :: ($type='')
	do_action( 'geodir_wrapper_content_close', 'listings-page' );
}

/**
 * BuddyPress reviews Tab template.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @param string $comment_template Comment template path.
 * @return string Modified Comment template path.
 */
function geodir_buddypress_comment_template( $comment_template ) {
     return GEODIR_BUDDYPRESS_PLUGIN_PATH . '/templates/reviews.php';
}

/**
 * BuddyPress reviews Tab comment class.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @param array       $classes    An array of comment classes.
 * @param string      $class      A comma-separated list of additional classes added to the list.
 * @param int         $comment_id The comment id.
 * @param object   	  $comment    The comment
 * @param int|WP_Post $post_id    The post ID or WP_Post object.
 * @return array Modified HTML class array.
 */
function geodir_buddypress_comment_class( $classes, $class, $comment_id, $comment, $post_id ) {
    $classes[] = 'bypostauthor';
    return $classes;
}

/**
 * BuddyPress reviews Tab content.
 *
 * @since 1.0.0
 * @since 1.1.0 Fix conflict with woocommerce comments.
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @global bool $gd_buddypress_reviews True if buddypress reviews tab.
 *
 * @param array $args Query arguments.
 */
function geodir_buddypress_reviews_html( $args = array() ) {
	global $gd_buddypress_reviews;
	$gd_buddypress_reviews = true;
	wp_register_style( 'gdbuddypress-style.css', GEODIR_BUDDYPRESS_PLUGIN_URL . '/css/gdbuddypress-style.css', array(), GEODIR_BUDDYPRESS_VERSION );
	wp_enqueue_style( 'gdbuddypress-style.css');
	
	$post_type = $args['post_type'];
	
	add_filter( 'comments_template', 'geodir_buddypress_comment_template', 100, 1 );
	add_filter( 'comment_class', 'geodir_buddypress_comment_class', 100, 5 );
	
	if (class_exists('WC_Comments')) {
		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );
		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_webhook_comments' ), 10, 1 );
	}
    
    if ( defined( 'GEODIR_REVIEWRATING_PLUGINDIR_URL' ) ) {
        remove_filter( 'comments_array', 'geodir_reviewrating_filter_comments' );
        add_filter( 'comments_clauses', 'geodir_reviewrating_comments_shorting', 11, 1 );
    }
    add_filter( 'comments_clauses', 'geodir_buddypress_comments_clauses', 1000, 1 );
		
	/* Show Comment Rating */
	if ( defined( 'GEODIR_REVIEWRATING_PLUGINDIR_URL' ) && ( get_option( 'geodir_reviewrating_enable_rating' ) || get_option( 'geodir_reviewrating_enable_images' ) || get_option( 'geodir_reviewrating_enable_review' ) || get_option( 'geodir_reviewrating_enable_sorting' ) || get_option( 'geodir_reviewrating_enable_sharing' ) ) ) {    
		global $geodir_post_type;
		$geodir_post_type = $post_type;
		add_filter( 'comment_text', 'geodir_reviewrating_wrap_comment_text', 42, 2 );
		
		wp_register_script( 'geodir-reviewrating-review-script', GEODIR_REVIEWRATING_PLUGINDIR_URL.'/js/comments-script.js' );
		wp_enqueue_script( 'geodir-reviewrating-review-script' );
		
		wp_register_style( 'geodir-reviewratingrating-style', GEODIR_REVIEWRATING_PLUGINDIR_URL .'/css/style.css' );
		wp_enqueue_style( 'geodir-reviewratingrating-style' );
	}
			
	$review_limit = apply_filters( 'gdbp_review_limit', 5 );
	$list_sort = apply_filters( 'gdbp_review_list_sort', 'latest' );
	$character_count = apply_filters( 'gdbp_review_character_count', 200 );
	
	$post_type_name = !empty( $args['post_type_name'] ) ? geodir_strtolower( $args['post_type_name'] ) : __( 'listings', 'gdbuddypress' );
	
	$query_args = array(
					'posts_per_page' => $review_limit,
					'is_geodir_loop' => true,
					'gd_location' 	 => false,
					'post_type' => $post_type,
					'order_by' => $list_sort
				);
	$author_id = bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id();
	$logged_id = bp_loggedin_user_id();
				
	$defaults = array();
	$args = array(
		'post_type' => $post_type,
		'order'   => 'DESC',
		'orderby' => 'comment_date_gmt',
		'status'  => 'approve',
		'user_id'  => $author_id,
		'number' => 20
	);
	
	if ( $logged_id && $logged_id == $author_id ) {
		$args['include_unapproved'] = array( $author_id );
	}
	$args = wp_parse_args( $args, $defaults );

	global $wp_query;
	$query = new WP_Comment_Query;
	$comments = $query->query( $args );
	
	$wp_query->comments = apply_filters( 'comments_array', $comments, '' );
	$comments = $wp_query->comments;
	$wp_query->comment_count = count($wp_query->comments);
				
	###### MAIN CONTENT WRAPPERS OPEN ######
	// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
	do_action( 'geodir_wrapper_content_open', 'gdbp-reviews-page', 'geodir-wrapper-content', 'gdbp-wrapper-content gdbp-wrapper-reviews' );
	
	// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='',$itemtype='')
	do_action( 'geodir_main_content_open', 'gdbp-reviews-page', 'reviewsTab', 'gdbp-main-content gdbp-reviews-content');
	
	$overridden_cpage = false;
	if ( '' == get_query_var('cpage') && get_option('page_comments') ) {
		set_query_var( 'cpage', 'newest' == get_option('default_comments_page') ? get_comment_pages_count() : 1 );
		$overridden_cpage = true;
	}

	if ( !defined('COMMENTS_TEMPLATE') )
		define('COMMENTS_TEMPLATE', true);

	$file = '/comments.php';
	$theme_template = STYLESHEETPATH . $file;
	/**
	 * Filter the path to the theme template file used for the comments template.
	 *
	 * @since 1.5.1
	 *
	 * @param string $theme_template The path to the theme template file.
	 */
	$include = apply_filters( 'comments_template', $theme_template );
	
	if ( file_exists( $include ) )
		require( $include );
	elseif ( file_exists( TEMPLATEPATH . $file ) )
		require( TEMPLATEPATH . $file );
	else // Backward compat code will be removed in a future release
		require( ABSPATH . WPINC . '/theme-compat/comments.php');
				
	do_action( 'geodir_main_content_close', 'gdbp-reviews-page' );
	
	###### MAIN CONTENT WRAPPERS CLOSE ######
	// this adds the closing html tags to the wrapper_content div :: ($type='')
	do_action( 'geodir_wrapper_content_close', 'gdbp-reviews-page' );
	
	$gd_buddypress_reviews = false;
}

/**
 * BuddyPress reviews Tab - comment HTML.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @param object $comment Comment object.
 * @param array $args Comment arguments.
 * @param int $depth Comment depth.
 */
function geodir_buddypress_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class('geodir-comment'); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'gdbuddypress' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'gdbuddypress' ), '<span class="edit-link">', '</span>' ); ?></p>
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
				    do_action('gdbp_comment_meta_before', $comment);
					/**
					 * Filter to modify comment avatar size
					 *
					 * You can use this filter to change comment avatar size.
					 *
					 * @since 1.1.0
					 * @package GeoDirectory
					 */
					$avatar_size = apply_filters('gdbuddypress_comment_avatar_size', 44);
					echo get_avatar($comment, $avatar_size);
					printf( '<cite><b class="reviewer">%1$s</b> %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $comment->post_author ) ? '<span>' . __( 'Post author', 'gdbuddypress' ) . '</span>' : ''
					);
					echo "<span class='item'><small><span class='fn'>$comment->post_title</span></small></span>";
					printf( '<a href="%1$s"><time datetime="%2$s" class="dtreviewed">%3$s<span class="value-title" title="%2$s"></span></time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						sprintf( __( '%1$s at %2$s', 'gdbuddypress' ), get_comment_date(), get_comment_time() )
					);
					do_action('gdbp_comment_meta_after', $comment);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'gdbuddypress' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'gdbuddypress' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'gdbuddypress' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}

/**
 * BuddyPress listings tab sort options.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 */
function geodir_buddypress_display_sort_options()
{
    global $wp_query;

    $sort_by = isset($_REQUEST['sort_by']) ? sanitize_text_field( $_REQUEST['sort_by'] ) : '';
    $post_type = geodir_buddypress_action_post_type();
    $sort_options = geodir_get_sort_options($post_type);

    if ($post_type && !empty($sort_options)) {
        $sort_field_options = '';

        foreach ($sort_options as $sort) {
            $label = $sort->site_title;

            if ($sort->field_type == 'random') {
                $key = $sort->field_type;

                $selected = ($sort_by == $key || ($sort->is_default == '1' && !isset($_REQUEST['sort_by']))) ? 'selected="selected"' : '';

                $sort_field_options .= '<option ' . $selected . ' value="' . esc_url(add_query_arg('sort_by', $key)) . '">' . __( $label, 'geodirectory' ) . '</option>';
            }

            if ($sort->htmlvar_name == 'comment_count') {
                $sort->htmlvar_name = 'rating_count';
            }

            if ($sort->sort_asc) {
                $key = $sort->htmlvar_name . '_asc';
                $label = $sort->asc_title ? $sort->asc_title : $sort->site_title;

                $selected = ($sort_by == $key || ($sort->is_default == '1' && !isset($_REQUEST['sort_by']))) ? 'selected="selected"' : '';

                $sort_field_options .= '<option ' . $selected . ' value="' . esc_url(add_query_arg('sort_by', $key)) . '">' . __( $label, 'geodirectory' ) . '</option>';
            }

            if ($sort->sort_desc) {
                $key = $sort->htmlvar_name . '_desc';
                $label = $sort->desc_title ? $sort->desc_title : $sort->site_title;

                $selected = ($sort_by == $key || ($sort->is_default == '1' && !isset($_REQUEST['sort_by']))) ? 'selected="selected"' : '';

                $sort_field_options .= '<option ' . $selected . ' value="' . esc_url(add_query_arg('sort_by', $key)) . '">' . __( $label, 'geodirectory' ) . '</option>';
            }

        }

        if ($sort_field_options != '') {
            ?>
            <div class="geodir-tax-sort">
                <select name="sort_by" id="sort_by" onchange="javascript:window.location=this.value;">
                    <option
                        value="<?php echo esc_url(add_query_arg('sort_by', '')); ?>" <?php if ($sort_by == '') echo 'selected="selected"'; ?>><?php _e('Sort By', 'gdbuddypress'); ?></option>
                    <?php echo $sort_field_options; ?>
                </select>
            </div>
        <?php
        }
    }
}

/**
 * BuddyPress listings tab pagination.
 *
 * @since 1.0.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @param string $before Pagination before HTML.
 * @param string $after Pagination after HTML.
 * @param string $prelabel Pagination previous label text.
 * @param string $nxtlabel Pagination next label text.
 * @param int $pages_to_show Number of pages to show.
 * @param bool $always_show Always display the pagination? Default: false.
 */
function geodir_buddypress_pagination($before = '', $after = '', $prelabel = '', $nxtlabel = '', $pages_to_show = 5, $always_show = false)
{
    global $posts_per_page, $found_posts, $paged;
    if (empty ($prelabel)) {
        $prelabel = '<strong>&laquo;</strong>';
    }
    global $bp;
    $user_domain = geodir_buddypress_get_user_domain();

    if (empty($nxtlabel)) {
        $nxtlabel = '<strong>&raquo;</strong>';
    }

    $half_pages_to_show = round($pages_to_show / 2);

    if (!is_single() && $found_posts > 0 && $posts_per_page > 0) {
        $numposts = $found_posts;
        $max_page = ceil($numposts / $posts_per_page);

        if (empty($paged)) {
            $paged = 1;
        }

        $current_domain = '';
        $component_domain = '';
        if ($bp->current_component && $bp->current_action) {
            $component_domain = trailingslashit($user_domain . $bp->current_component . '/');
            $current_domain = trailingslashit($user_domain . $bp->current_component . '/' . $bp->current_action);
        }

        if ($max_page > 1 || $always_show) {
            echo "$before <div class='Navi'>";
            if ($paged >= ($pages_to_show - 1)) {
                $url = get_pagenum_link();
                if ($current_domain) {
                    $url = strpos($url, $current_domain) !== false ? $url : str_replace($component_domain, $current_domain, $url);
                }
                echo '<a href="' . str_replace('&paged', '&amp;paged', $url) . '">&laquo;</a>';
            }
            ob_start();
            previous_posts_link($prelabel);
            $url = ob_get_clean();
            if ($current_domain) {
                $url = strpos($url, $current_domain) !== false ? $url : str_replace($component_domain, $current_domain, $url);
            }
            echo $url;
            for ($i = $paged - $half_pages_to_show; $i <= $paged + $half_pages_to_show; $i++) {
                if ($i >= 1 && $i <= $max_page) {
                    if ($i == $paged) {
                        echo "<strong class='on'>$i</strong>";
                    } else {
                        $url = get_pagenum_link($i);
                        if ($current_domain) {
                            $url = strpos($url, $current_domain) !== false ? $url : str_replace($component_domain, $current_domain, $url);
                        }
                        echo ' <a href="' . str_replace('&paged', '&amp;paged', $url) . '">' . $i . '</a> ';
                    }
                }
            }
            ob_start();
            next_posts_link($nxtlabel, $max_page);
            $url = ob_get_clean();
            if ($current_domain) {
                $url = strpos($url, $current_domain) !== false ? $url : str_replace($component_domain, $current_domain, $url);
            }
            echo $url;
            if (($paged + $half_pages_to_show) < ($max_page)) {
                $url = get_pagenum_link($max_page);
                if ($current_domain) {
                    $url = strpos($url, $current_domain) !== false ? $url : str_replace($component_domain, $current_domain, $url);
                }
                echo '<a href="' . str_replace('&paged', '&amp;paged', $url) . '">&raquo;</a>';
            }
            echo "</div> $after";
        }
    }
}

/**
 * Filter the array of comment query clauses.
 *
 * Fix the user_id ambiguous column error in comments and reviews table.
 *
 * @since 1.1.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @global object $wpdb WordPress Database object.
 * @global bool $gd_buddypress_reviews True if buddypress reviews tab.
 *
 * @param array $clauses A compacted array of comment query clauses.
 * @return array Modified comment query clauses.
 */
function geodir_buddypress_comments_clauses( $clauses ) {
	global $wpdb, $gd_buddypress_reviews;
	
	if ( $gd_buddypress_reviews ) {
		$where = $clauses['where'];
		
		$where = str_replace( ' user_id', ' ' . $wpdb->comments . '.user_id', $where );
		$where = str_replace( ' comment_approved', ' ' . $wpdb->comments . '.comment_approved', $where );
		$clauses['where'] = $where;
	}
	return $clauses;
}

/**
 * Multirating addon strips author link from reviews. This function fixes that.
 *
 * @since 1.1.0
 * @package GeoDirectory_BuddyPress_Integration
 *
 * @return array Modified comment query clauses.
 */
function multirating_get_comment_author_link($return, $author, $comment_id) {
    $comment = get_comment( $comment_id );
    if (isset($comment->user_id) && $comment->user_id != '0') {
        $url = trailingslashit( bp_core_get_user_domain( $comment->user_id ) );
        $return = "<a href='$url' rel='external nofollow' class='url'>$author</a>";
    }
    return $return;
}

add_action('init', 'init_multirating_get_comment_author_link');
function init_multirating_get_comment_author_link() {
     if ( get_option( 'geodir_buddypress_link_author' ) && defined('GEODIRREVIEWRATING_VERSION')) {
        add_filter('get_comment_author_link', 'multirating_get_comment_author_link', 10, 3);
    }
}