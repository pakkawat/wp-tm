<?php do_action('geodir_before_listing_listview');
global $gridview_columns;
$grid_view_class = apply_filters('geodir_grid_view_widget_columns', $gridview_columns);
if (isset($_SESSION['gd_listing_view']) && $_SESSION['gd_listing_view'] != '' && !isset($before_widget) && !isset($related_posts)) {
    if ($_SESSION['gd_listing_view'] == '1') {
        $grid_view_class = '';
    }
    if ($_SESSION['gd_listing_view'] == '2') {
        $grid_view_class = 'gridview_onehalf';
    }
    if ($_SESSION['gd_listing_view'] == '3') {
        $grid_view_class = 'gridview_onethird ';
    }
    if ($_SESSION['gd_listing_view'] == '4') {
        $grid_view_class = 'gridview_onefourth';
    }
    if ($_SESSION['gd_listing_view'] == '5') {
        $grid_view_class = 'gridview_onefifth';
    }
}
?>

    <ul class="geodir_category_list_view whoop-view clearfix">

        <?php if (have_posts()) :

            do_action('geodir_before_listing_post_listview');

            while (have_posts()) : the_post();
                global $post, $wpdb, $listing_width, $preview;
                $post_view_class = apply_filters('geodir_post_view_extra_class', '');
                $post_view_article_class = apply_filters('geodir_post_view_article_extra_class', '');
                ?>

                <li class="clearfix <?php if ($grid_view_class) {
                    echo 'geodir-gridview ' . $grid_view_class;
                } else {
                    echo ' geodir-listview ';
                } ?> <?php if ($post_view_class) {
                    echo $post_view_class;
                } ?>" <?php if ($listing_width) echo "style='width:{$listing_width}%;'"; // Width for widget listing ?> >
                    <article class="geodir-category-listing <?php if ($post_view_article_class) {
                        echo $post_view_article_class;
                    } ?>">
                        <div class="geodir-post-img">
                            <?php if ($fimage = geodir_show_featured_image($post->ID, 'list-thumb', true, false, $post->featured_image)) { ?>

                                <a href="<?php the_permalink(); ?>">
                                    <?php echo $fimage; ?>
                                </a>
                                <?php
                                do_action('geodir_before_badge_on_image', $post);
                                if ($post->is_featured) {
                                    echo geodir_show_badges_on_image('featured', $post, get_permalink());
                                }

                                $geodir_days_new = (int)get_option('geodir_listing_new_days');

                                if (round(abs(strtotime($post->post_date) - strtotime(date('Y-m-d'))) / 86400) < $geodir_days_new) {
                                    echo geodir_show_badges_on_image('new', $post, get_permalink());
                                }
                                do_action('geodir_after_badge_on_image', $post);
                                ?>


                            <?php } ?>

                        </div>

                        <div class="geodir-content">

                            <div class="geodir-entry-content">
                                <?php do_action('geodir_before_listing_post_title', 'listview', $post); ?>

                                <header class="geodir-entry-header"><h3 class="geodir-entry-title">
                                        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">

                                            <?php the_title(); ?>

                                        </a>
                                    </h3></header>
                                <!-- .entry-header -->

                                <?php do_action('geodir_after_listing_post_title', 'listview', $post); ?>

<!-- 20171109 Bank Add Order Now -->
								<?php /*echo "Tab index is $post_view_class";*/ ?>
									<div class="Order-online" id="List-order-online"> 
 									<?php
        							$check_button= $post->geodir_Button_enable;
       			    				if($check_button){
        			echo "<span class='glf-button' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false' style='padding:5px;width: 130px;height: 30px'>สั่งเลย</span><script 
       							src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";}?>
									</div>
<!-- Bank  -->

                                <?php /// Print Distance
                                if (isset($_REQUEST['sgeo_lat']) && $_REQUEST['sgeo_lat'] != '') {

                                    $startPoint = array('latitude' => $_REQUEST['sgeo_lat'], 'longitude' => $_REQUEST['sgeo_lon']);

                                    $endLat = $post->post_latitude;
                                    $endLon = $post->post_longitude;
                                    $endPoint = array('latitude' => $endLat, 'longitude' => $endLon);
                                    $uom = get_option('geodir_search_dist_1');
                                    $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom);?>
                                    <h3>
                                        <?php

                                        if (round((int)$distance, 2) == 0) {
                                            $uom = get_option('geodir_search_dist_2');

                                            $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom);
                                            if ($uom == 'feet') {
                                                $uom = __('feet', GEODIRECTORY_FRAMEWORK);
                                            } else {
                                                $uom = __('meters', GEODIRECTORY_FRAMEWORK);
                                            }
                                            echo round($distance) . ' ' . $uom . '<br />';
                                        } else {
                                            if ($uom == 'miles') {
                                                $uom = __('miles', GEODIRECTORY_FRAMEWORK);
                                            } else {
                                                $uom = __('km', GEODIRECTORY_FRAMEWORK);
                                            }
                                            echo round($distance, 2) . ' ' . $uom . '<br />';
                                        }
                                        ?>
                                    </h3>
                                <?php } ?>


                                <?php do_action('geodir_before_listing_post_excerpt', $post); ?>
                                <?php echo geodir_show_listing_info('listing'); ?>

                                <?php
//                                if (isset($character_count) && ($character_count || $character_count == '0')) {
//                                    $content_out = geodir_max_excerpt($character_count);
//                                } else {
//                                    $content_out = get_the_excerpt();
//                                }
//                                if (!empty($content_out)) {
//                                    echo "<p>" . $content_out . "</p>";
//                                }
                                ?>
	                            <footer class="geodir-entry-meta">
		                            <div class="geodir-addinfo clearfix">

			                            <?php

			                            $review_show = geodir_is_reviews_show('listview');

			                            ?>
			                            <div class="geodir-big-header-ratings">
				                            <?php
				                            global $post;
				                            $post_avgratings = geodir_get_post_rating($post->ID);
				                            echo geodir_get_rating_stars($post_avgratings, $post->ID);
				                            ?>
				                            <a href="<?php comments_link(); ?>" class="geodir-big-header-rc"><?php geodir_comments_number($post->rating_count); ?></a>
				                            <?php
				                            $current_post_type = geodir_get_current_posttype();
				                            $category_taxonomy = geodir_get_taxonomies( $current_post_type );

				                            $terms = get_the_terms($post->ID, $category_taxonomy[0]);
				                            if(!empty($terms)){
					                            echo "<span class='geodir-category clearfix geodir-big-header-cats'>";
//                                                if($post->geodir_price_range){
//                                                    echo $post->geodir_price_range." - ";
//                                                } else {
                                                    echo '<i class="fa fa-tags whoop-cat-i"></i>';
//                                                }
					                            foreach($terms as $term){
						                            $term = get_term_by( 'id', $term->term_id, $category_taxonomy[0]);
						                            echo "<a href='".esc_attr( get_term_link($term) ) . "'>$term->name</a>";
					                            }
					                            echo "</span>";
				                            }
				                            ?>
                                            <?php
                                            /**
                                             * Called after printing favorite html.
                                             *
                                             * @since 1.0.0
                                             */
                                            do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );


                                            /**
                                             * Called after printing map pin point.
                                             *
                                             * @since 1.0.0
                                             * @since 1.5.9 Added $post as second param.
                                             * @param int $post->ID The post id.
                                             * @param object $post The post object.
                                             */
                                            do_action( 'geodir_listing_after_pinpoint', $post->ID ,$post);


                                            if ($post->post_author == get_current_user_id()) {

				                            $addplacelink = get_permalink(get_option('geodir_add_listing_page'));
				                            $editlink = geodir_getlink($addplacelink, array('pid' => $post->ID), false);
				                            $upgradelink = geodir_getlink($editlink, array('upgrade' => '1'), false);

				                            $ajaxlink = geodir_get_ajax_url();
				                            $deletelink = geodir_getlink($ajaxlink, array('geodir_ajax' => 'add_listing', 'ajax_action' => 'delete', 'pid' => $post->ID), false);

				                            ?>

				                            <span class="geodir-authorlink clearfix">

											<?php if (isset($_REQUEST['geodir_dashbord']) && $_REQUEST['geodir_dashbord']) {

												do_action('geodir_before_edit_post_link_on_listing');
												?>
                                                <a href="<?php echo $editlink; ?>" class="geodir-edit"
                                                   title="<?php _e('Edit Listing', GEODIRECTORY_FRAMEWORK); ?>">
                                                    <?php
                                                    $geodir_listing_edit_icon = apply_filters('geodir_listing_edit_icon', 'fa fa-edit');
                                                    echo '<i class="'. $geodir_listing_edit_icon .'"></i>';
                                                    ?>
                                                    <?php _e('Edit', GEODIRECTORY_FRAMEWORK); ?>
                                                </a>
                                                <a href="<?php echo $deletelink; ?>" class="geodir-delete"
                                                   title="<?php _e('Delete Listing', GEODIRECTORY_FRAMEWORK); ?>">
                                                    <?php
                                                    $geodir_listing_delete_icon = apply_filters('geodir_listing_delete_icon', 'fa fa-close');
                                                    echo '<i class="'. $geodir_listing_delete_icon .'"></i>';
                                                    ?>
                                                    <?php _e('Delete', GEODIRECTORY_FRAMEWORK); ?>
                                                </a>
												<?php
												do_action('geodir_after_edit_post_link_on_listing');
											} ?>
											</span>

			                            <?php } ?>

		                            </div>
		                            <!-- geodir-addinfo ends here-->
	                            </footer>
                            </div>
	                        <div class="geodir-whoop-address">
		                        <?php
		                        $html ="";
                                $cf_address = geodir_get_field_infoby('htmlvar_name','post',$post->post_type);
                                $cf_contact = geodir_get_field_infoby('htmlvar_name','geodir_contact',$post->post_type);
                                $cf_website = geodir_get_field_infoby('htmlvar_name','geodir_website',$post->post_type);

                                if (strpos($cf_address->show_in, '[listing]') !== false) {
                                    if($post->post_address){ $html .= '<span>'.$post->post_address.'</span><br>';}
                                    if($post->post_city){ $html .= '<span>'.$post->post_city.'</span>, ';}
                                    if($post->post_region){ $html .= '<span>'.$post->post_region.'</span> ';}
                                    if($post->post_zip){ $html .= '<span>'.$post->post_zip.'</span><br>';}
                                    if($post->post_country){ $html .= '<span>'.__( $post->post_country, GEODIRECTORY_FRAMEWORK ).'</span><br>';}
                                }
                                if (strpos($cf_contact->show_in, '[listing]') !== false) {
                                    if ( $post->geodir_contact ) {
                                        $html .= '<span><i class="fa fa-phone"></i><a href="tel:' . $post->geodir_contact . '" target="_blank" rel="nofollow"> ' . $post->geodir_contact . '</a></span><br>';
                                    }
                                }
                                if (strpos($cf_website->show_in, '[listing]') !== false) {
                                    if ( $post->geodir_website ) {
                                        $html .= '<span><i class="fa fa-link"></i><a href="' . $post->geodir_website . '" target="_blank" rel="nofollow">' . __( ' Website', GEODIRECTORY_FRAMEWORK ) . '</a></span><br>';
                                    }
                                }
                                echo $html;
		                        ?>
	                        </div>
	                        <!-- .entry-meta -->
                            <?php do_action('geodir_after_listing_post_excerpt', $post); ?>
                        </div>
                        <!-- gd-content ends here-->

                    </article>
	                <?php
	                $args = array(
		                'status' => 'approve',
		                'number' => 1,
		                'parent' => 0,
		                'post_id' => $post->ID, // use post_id, not post_ID
	                );
	                $comments = get_comments($args);
                    if ($comments) {
                        ?>
                    <div class="geodir-whoop-listing-review">
                        <?php
	                foreach($comments as $comment) {
                        if ($comment->user_id) {
                            $user_profile_url = get_author_posts_url($comment->user_id);
                        } else {
                            $user_profile_url = '';
                        }
                        if ($user_profile_url) {
                            echo '<a href="' . $user_profile_url . '">';
                        }
		                echo get_avatar( $comment, 33 );
                        if ($user_profile_url) {
                            echo '</a>';
                        }
		                ?>
		                <div>
		                    <?php echo strip_tags($comment->comment_content); ?>
		                </div>
		                <?php
	                }
	                ?>
                </div>
                        <?php } ?>
                </li>

            <?php
            endwhile;

            do_action('geodir_after_listing_post_listview');

        else:

            $favorite = isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite' ? true : false;

            			/**
                         * Called inside the `ul` of the listings template, when no listing found.
                         *
                         * @since 1.0.4
            			 * @param string 'listing-listview' Listing listview template.
            			 * @param bool $favorite Are favorite listings results?
                         */
                        do_action('geodir_message_not_found_on_listing', 'listing-listview', $favorite);

        endif;

        ?>
    </ul>  <!-- geodir_category_list_view ends here-->

    <div class="clear"></div>
<?php do_action('geodir_after_listing_listview');   
