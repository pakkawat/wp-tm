<?php
do_action('geodir_before_listing_listview');

global $gridview_columns_widget, $gd_session;

/** This action is documented in geodirectory-templates/listing-listview.php */
$grid_view_class = apply_filters('geodir_grid_view_widget_columns', $gridview_columns_widget);

if ($gd_ses_listing_view = $gd_session->get('gd_listing_view') && empty($geodir_event_widget_listview)) {
    $grid_view_class = geodir_convert_listing_view_class($gd_ses_listing_view);
}
?>
<ul class="geodir_category_list_view clearfix">
  <?php 
if ( !empty( $widget_events ) ) {
	do_action('geodir_before_listing_post_listview');
	foreach ( $widget_events as $widget_event ) {
		$post = $widget_event;
		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		
		$post_view_class = apply_filters( 'geodir_post_view_extra_class', '' );
		$post_view_article_class = apply_filters( 'geodir_post_view_article_extra_class', '' );
	?>
  <li id="post-<?php echo $post->ID;?>" class="clearfix <?php echo ($grid_view_class ? 'geodir-gridview ' . $grid_view_class : 'geodir-listview'); ?> <?php if($post_view_class){echo $post_view_class;}?>" <?php if(isset($listing_width) && $listing_width > 0) echo "style='width:{$listing_width}%;'"; // Width for widget listing ?>>
    <article class="geodir-category-listing <?php if($post_view_article_class){echo $post_view_article_class;}?>">
      <div class="geodir-post-img">
        <?php if($fimage = geodir_show_featured_image($post->ID, 'list-thumb', true, false, $post->featured_image)){ ?>
        <a  href="<?php the_permalink(); ?>">
        <?php  echo $fimage;?>
        </a>
        <?php do_action('geodir_before_badge_on_image', $post) ;
				if( $post->is_featured ) {
					echo geodir_show_badges_on_image( 'featured', $post, get_permalink() );
				}
				
				$geodir_days_new = (int)get_option('geodir_listing_new_days');
				
				if(round(abs(strtotime($post->post_date)-strtotime(date('Y-m-d')))/86400)<$geodir_days_new){
					echo geodir_show_badges_on_image('new' , $post,get_permalink());
				}
				
				do_action('geodir_after_badge_on_image', $post);
			} ?>
      </div>
      <div class="geodir-content">
        <?php do_action('geodir_before_listing_post_title', 'listview', $post ); ?>
        <header class="geodir-entry-header">
          <h3 class="geodir-entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php the_title(); ?>
            </a></h3>
        </header>
        <!-- .entry-header -->
        <?php do_action('geodir_after_listing_post_title', 'listview', $post );?>
        <?php /// Print Distance
			if ( isset( $_REQUEST['sgeo_lat'] ) && $_REQUEST['sgeo_lat'] != '' ) {
				$startPoint = array( 'latitude'	=> $_REQUEST['sgeo_lat'], 'longitude' => $_REQUEST['sgeo_lon']);	
				$endLat = $post->post_latitude; 
				$endLon = $post->post_longitude;
				$endPoint = array( 'latitude'	=> $endLat, 'longitude'	=> $endLon);
				$uom = get_option( 'geodir_search_dist_1' );
				$distance = geodir_calculateDistanceFromLatLong( $startPoint, $endPoint, $uom );
				?>
        <h3>
          <?php
				if (round((int)$distance,2) == 0){
					$uom = get_option('geodir_search_dist_2');
					$distance = geodir_calculateDistanceFromLatLong ($startPoint,$endPoint,$uom);
					echo round($distance).' '.__( $uom, 'geodirectory' ).'<br />';
				} else {
					echo round($distance,2).' '.__( $uom, 'geodirectory' ).'<br />';
				}
			?>
        </h3>
        <?php } ?>
        <?php do_action('geodir_before_listing_post_excerpt', $post); ?>
        <?php echo geodir_show_listing_info( 'listing' );?>
        <?php if(isset( $character_count ) && $character_count == '0' ) { } else { ?>
			<div class="geodir-entry-content">
			  <p>
				<?php
				if(isset( $character_count ) && $character_count != '' ) {
					echo geodir_max_excerpt( $character_count ); 
				} else { 
					the_excerpt(); 
				}
				?>
			  </p>
			</div>
			<?php } ?>
        <?php do_action('geodir_after_listing_post_excerpt', $post ); ?>
      </div>
      <!-- gd-content ends here-->
      <footer class="geodir-entry-meta">
        <div class="geodir-addinfo clearfix">
          <?php 
				$review_show = geodir_is_reviews_show('listview');
				if ($review_show) {
					
					global $preview;
					if (!$preview) {
						$post_avgratings = geodir_get_commentoverall_number($post->ID);
						
						do_action('geodir_before_review_rating_stars_on_listview' , $post_avgratings , $post->ID) ;
						echo geodir_get_rating_stars($post_avgratings,$post->ID);
						do_action('geodir_after_review_rating_stars_on_listview' , $post_avgratings , $post->ID);
					}
					?>
          <a href="<?php comments_link(); ?>" class="geodir-pcomments"><i class="fa fa-comments"></i>
          <?php geodir_comments_number( $post->rating_count ); ?>
          </a>
          <?php 
				}
				geodir_favourite_html($post->post_author,$post->ID);
				?>
        </div>
        <!-- geodir-addinfo ends here-->
      </footer>
      <!-- .entry-meta -->
    </article>
  </li>
  <?php }
		do_action('geodir_after_listing_post_listview');
		
	} else {
		/** This action is documented in geodirectory-templates/listing-listview.php */
		do_action('geodir_message_not_found_on_listing', 'gdevents_widget_listview', false);
	}
	?>
</ul>
<!-- geodir_category_list_view ends here-->
<div class="clear"></div>
<?php do_action('geodir_after_listing_listview'); ?>