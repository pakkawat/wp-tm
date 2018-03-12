<?php
/**
 * Template for the list of places
 *
 * This is used mostly by widgets that produce a list of listings and outputs the actual grid or list of listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global string $gridview_columns_widget The girdview style of the listings for widget.
 * @global object $gd_session GeoDirectory Session object.
 */

/** This action is documented in geodirectory-templates/listing-listview.php */
do_action('geodir_before_listing_listview');

global $gridview_columns_widget, $gd_session;

/** This action is documented in geodirectory-templates/listing-listview.php */
// $grid_view_class = apply_filters('geodir_grid_view_widget_columns', $gridview_columns_widget);
// if ($gd_session->get('gd_listing_view') && !isset($before_widget)) {
//     $grid_view_class = geodir_convert_listing_view_class($gd_session->get('gd_listing_view'));
// }
$grid_view_class = 'gridview_onethird ';
?>
    <ul class="geodir_category_list_view whoop-view clearfix tamzang-cat-list-wrapper">
        <?php
        if (!empty($widget_listings)) {
            /** This action is documented in geodirectory-templates/listing-listview.php */
            do_action('geodir_before_listing_post_listview');
            $all_postypes = geodir_get_posttypes();
            $geodir_days_new = (int)get_option('geodir_listing_new_days');
            foreach ($widget_listings as $widget_listing) {
                global $gd_widget_listing_type;
                $post = $widget_listing;

                $GLOBALS['post'] = $post;
                setup_postdata($post);

                $gd_widget_listing_type = $post->post_type;

                /** This action is documented in geodirectory-templates/listing-listview.php */
                $post_view_class = apply_filters('geodir_post_view_extra_class', '', $all_postypes);

                /** This action is documented in geodirectory-templates/listing-listview.php */
                $post_view_article_class = apply_filters('geodir_post_view_article_extra_class', '');
                ?>
                <li class="clearfix <?php echo 'geodir-gridview ' . $grid_view_class; ?>
                  <?php if ($post_view_class) {
                    echo $post_view_class;
                } ?> tamzang-place-arrange-30" style="border:none;"
                <?php
                echo " data-post-id='$post->ID' ";
                /** This action is documented in geodirectory-templates/listing-listview.php */
                do_action('geodir_listview_inside_li', $post, 'widget');
                ?>>
                    <article class="geodir-category-listing tamzang-place-border">
                        <div class="geodir-post-img">
                            <?php if ($fimage = geodir_show_featured_image($post->ID, 'list-thumb', true, false, $post->featured_image)) { ?>
                                <a href="<?php the_permalink(); ?>"><?php echo $fimage; ?></a>
                                <?php
                                /** This action is documented in geodirectory-templates/listing-listview.php */
                                do_action('geodir_before_badge_on_image', $post);
                                if ($post->is_featured) {
                                    echo geodir_show_badges_on_image('featured', $post, get_permalink());
                                }


                                if (round(abs(strtotime($post->post_date) - strtotime(date('Y-m-d'))) / 86400) < $geodir_days_new) {
                                    echo geodir_show_badges_on_image('new', $post, get_permalink());
                                }

                                /** This action is documented in geodirectory-templates/listing-listview.php */
                                do_action('geodir_after_badge_on_image', $post);
                            }
                            ?>
                        </div>
                        <div class="geodir-content">

                          <div class="geodir-entry-content">
                            <?php
                            /** This action is documented in geodirectory-templates/listing-listview.php */
                            do_action('geodir_before_listing_post_title', 'listview', $post); ?>
                            <header class="geodir-entry-header">
                                <h3 class="geodir-entry-title">
                                    <a href="<?php the_permalink(); ?>"
                                       title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                </h3>
                            </header>
                            <!-- .entry-header -->
                            <?php
                            /** This action is documented in geodirectory-templates/listing-listview.php */
                            do_action('geodir_after_listing_post_title', 'listview', $post); ?>
                            <?php /// Print Distance
                            if (isset($_REQUEST['sgeo_lat']) && $_REQUEST['sgeo_lat'] != '') {
                                $startPoint = array('latitude' => $_REQUEST['sgeo_lat'], 'longitude' => $_REQUEST['sgeo_lon']);
                                $endLat = $post->post_latitude;
                                $endLon = $post->post_longitude;
                                $endPoint = array('latitude' => $endLat, 'longitude' => $endLon);
                                $uom = get_option('geodir_search_dist_1');
                                $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom);
                                ?>
                                <h3>
                                    <?php
                                    if (round($distance, 2) == 0) {
                                        $uom = get_option('geodir_search_dist_2');
                                        $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom);
                                        if ($uom == 'feet') {
                                            $uom = __('feet', 'geodirectory');
                                        } else {
                                            $uom = __('meters', 'geodirectory');
                                        }
                                        echo round($distance) . ' ' . __($uom, 'geodirectory') . '<br />';
                                    } else {
                                        if ($uom == 'miles') {
                                            $uom = __('miles', 'geodirectory');
                                        } else {
                                            $uom = __('km', 'geodirectory');
                                        }
                                        echo round($distance, 2) . ' ' . __($uom, 'geodirectory') . '<br />';
                                    }
                                    ?>
                                </h3>
                            <?php } ?>
                            <footer class="geodir-entry-meta">
                              <div class="geodir-addinfo clearfix">
                                <div class="geodir-big-header-ratings">
                                  <?php
                                  do_action( 'geodir_before_review_html', $post, 'widget' );
                                  $review_show = geodir_is_reviews_show('listview');
                                  if ($review_show) {
                                      $post_avgratings = geodir_get_post_rating($post->ID);
                                      /** This action is documented in geodirectory-templates/listing-listview.php */
                                      do_action('geodir_before_review_rating_stars_on_listview', $post_avgratings, $post->ID);
                                      echo geodir_get_rating_stars($post_avgratings, $post->ID);
                                      do_action('geodir_after_review_rating_stars_on_listview', $post_avgratings, $post->ID);
                                      ?>
                                      <a href="<?php comments_link(); ?>" class="geodir-big-header-rc"><?php geodir_comments_number($post->rating_count); ?></a>
                                      <?php
                                  }
                                      $current_post_type = geodir_get_current_posttype();
  				                            $category_taxonomy = geodir_get_taxonomies( $current_post_type );
  				                            $terms = get_the_terms($post->ID, $category_taxonomy[0]);
  				                            if(!empty($terms)){
  					                            echo "<span class='geodir-category clearfix geodir-big-header-cats'>";
                                        echo '<i class="fa fa-tags whoop-cat-i"></i>';
  					                            foreach($terms as $term){
  						                            $term = get_term_by( 'id', $term->term_id, $category_taxonomy[0]);
  						                            echo "<a href='".esc_attr( get_term_link($term) ) . "'>$term->name</a>";
  					                            }
  					                            echo "</span>";
  				                            }
                                      do_action( 'geodir_after_favorite_html', $post->ID, 'listing');
                                      //do_action( 'geodir_listing_after_pinpoint', $post->ID ,$post);

                                      ?>
                                </div>
                              </div>
                            </footer>
                            <div class="geodir-whoop-address">
                              <?php
                                $html ="";
                                $cf_address = geodir_get_field_infoby('htmlvar_name','post',$post->post_type);
                                $cf_contact = geodir_get_field_infoby('htmlvar_name','geodir_contact',$post->post_type);
                                $cf_website = geodir_get_field_infoby('htmlvar_name','geodir_website',$post->post_type);
                                if (strpos($cf_address->show_in, '[listing]') !== false)
                                {
                                  if($post->post_address){ $html .= '<span>'.$post->post_address.'</span><br>';}
                                  if($post->post_city){ $html .= '<span>'.$post->post_city.'</span>, ';}
                                  if($post->post_region){ $html .= '<span>'.$post->post_region.'</span> ';}
                                  if($post->post_zip){ $html .= '<span>'.$post->post_zip.'</span><br>';}
                                  if($post->post_country){ $html .= '<span>'.__( $post->post_country, GEODIRECTORY_FRAMEWORK ).'</span><br>';}
                                }
                                if (strpos($cf_contact->show_in, '[listing]') !== false)
                                {
                                  if ( $post->geodir_contact )
                                  {
                                    $html .= '<span><i class="fa fa-phone"></i><a href="tel:' . $post->geodir_contact . '" target="_blank" rel="nofollow"> ' . $post->geodir_contact . '</a></span><br>';
                                  }
                                  if (strpos($cf_website->show_in, '[listing]') !== false)
                                  {
                                    if ( $post->geodir_website )
                                    {
                                      $html .= '<span><i class="fa fa-link"></i><a href="' . $post->geodir_website . '" target="_blank" rel="nofollow">' . __( ' Website', GEODIRECTORY_FRAMEWORK ) . '</a></span><br>';
                                    }
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
                  } // end foreach $widget_listings
                } // end if (!empty($widget_listings))
                    ?>
                    </ul>  <!-- geodir_category_list_view ends here-->
                    <div class="clear"></div>

<?php
/** This action is documented in geodirectory-templates/listing-listview.php */
do_action('geodir_after_listing_listview');
