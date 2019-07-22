<?php /* Template Name: waiting-cart */ ?>

<?php
global $current_user;

function tamzang_orderby_shop_id($orderby, $table, $post_type){
  $orderby = " ".$table.".geodir_shop_id ASC, ";
  return $orderby;
}

//$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);


add_filter('geodir_filter_widget_listings_join', 'inner_join_user_id', 10, 2);
add_filter('geodir_filter_widget_listings_fields', 'select_shopping_cart_field', 10, 3);
add_filter('geodir_filter_widget_listings_orderby', 'tamzang_orderby_shop_id', 10, 3);
$query_args = array(
  'is_geodir_loop' => true,
  'post_type' => 'gd_product',
  'posts_per_page' => -1,
  'order_by' => 'post_title'
);

$arrProducts = geodir_get_widget_listings($query_args);

get_header();

?>
<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 67%">



      <ul class="geodir_category_list_view whoop-view clearfix">

      <?php
        $shop_id = "";
        foreach ($arrProducts as $product){
          if($shop_id != $product->geodir_shop_id){
            if($shop_id != "")
              echo "</div></div></article></li>";
      ?>
            <li class="clearfix  geodir-listview  gd-post-gd_place">
              <article class="geodir-category-listing ">
                <div class="geodir-post-img">

                <?php 
                $featured_image = geodir_get_post_meta( $product->geodir_shop_id, 'featured_image', true );
                $permalink = get_permalink( $product->geodir_shop_id );
                $post_date = get_the_date('',$product->geodir_shop_id);
                if ($fimage = geodir_show_featured_image($product->geodir_shop_id, 'list-thumb', true, false, $featured_image)) 
                { ?>
                  <a href="<?php echo $permalink; ?>">
                      <?php echo $fimage; ?>
                  </a>
                  <?php
                  if ($post->is_featured) {
                      echo geodir_show_badges_on_image('featured', $product->geodir_shop_id, $permalink);
                  }
                  $geodir_days_new = (int)get_option('geodir_listing_new_days');
                  if (round(abs(strtotime($post_date) - strtotime(date('Y-m-d'))) / 86400) < $geodir_days_new) {
                      echo geodir_show_badges_on_image('new', $product->geodir_shop_id, $permalink);
                  }
                } ?>


                </div>
                <div class="geodir-content">
                  <div class="geodir-entry-content">
                    <header class="geodir-entry-header">
                      <h3 class="geodir-entry-title">
                        <a href="<?php echo get_permalink( $product->geodir_shop_id ) ?>"><?php echo get_the_title( $product->geodir_shop_id )?></a>
                      </h3>
                    </header>
                    <footer class="geodir-entry-meta">
                      <div class="geodir-addinfo clearfix">
                        <div class="geodir-big-header-ratings">
                          <?php
                            $post_avgratings = geodir_get_post_rating($product->geodir_shop_id);
                            echo geodir_get_rating_stars($post_avgratings, $product->geodir_shop_id);
                          ?>
                            <a href="<?php comments_link(); ?>" 
                            class="geodir-big-header-rc">
                            <?php echo geodir_comments_number(geodir_get_post_meta( $product->geodir_shop_id, 'rating_count', true )); ?></a>

                            <?php
                              $current_post_type = get_post_type((int)$product->geodir_shop_id);
                              $category_taxonomy = geodir_get_taxonomies( $current_post_type );
                              $terms = get_the_terms($product->geodir_shop_id, $category_taxonomy[0]);

                              if(!empty($terms)){
                                echo "<span class='geodir-category clearfix geodir-big-header-cats'>";
                                echo '<i class="fa fa-tags whoop-cat-i"></i>';

                                foreach($terms as $term){
                                  $term = get_term_by( 'id', $term->term_id, $category_taxonomy[0]);
                                  echo "<a href='".esc_attr( get_term_link($term) ) . "'>$term->name</a>";
                                }
                                echo "</span>";
                              }

                              $open = date(get_option('time_format'), strtotime(geodir_get_post_meta( $product->geodir_shop_id, 'geodir_open_time', true )));
                              $close = date(get_option('time_format'), strtotime(geodir_get_post_meta( $product->geodir_shop_id, 'geodir_close_time', true )));
                              echo '<div class="geodir_more_info" style="clear:both;">
                                  <span class="geodir-i-time">
                                  <i class="fa fa-clock-o"></i>: '.$open.' - '.$close.'</span></div>';
                            ?>
                        </div>
                      </div>
                    </footer>
                  </div>
                  <div class="geodir-whoop-address">
                    <strong style="color: #e34f43;">ตะกร้าสินค้า</strong>
                    <?php echo "<p><a href=".get_permalink( $product->post_id ).">".get_the_title( $product->post_id )."</a></p>"; ?>
            <?php 
          
              $shop_id  = $product->geodir_shop_id;
            }else{
              echo "<p><a href=".get_permalink( $product->post_id ).">".get_the_title( $product->post_id )."</a></p>";
            }
          }
      ?>
      </ul>


    </div>

  </div>
</div>
<?php get_footer(); ?>
