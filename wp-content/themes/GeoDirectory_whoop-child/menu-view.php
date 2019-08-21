<?php /* Template Name: menu-view */ ?>

<?php

$post_id = get_query_var('pid');
$geodir_tamzang_id = geodir_get_post_meta( $post_id, 'geodir_tamzang_id', true );
$cat_id = get_query_var('cat_id');
// $arrProducts = tamzang_get_all_products($post_id,$cat_id);

$query_args = array(
  'is_geodir_loop' => true,
  'post_type' => 'gd_product',
  'posts_per_page' => -1,
  'order_by' => 'post_title'
);
//add_filter('geodir_search_output_to_main_taxonomy', 'test_echo', 10, 3);
add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);
if(!empty($cat_id))
  add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_category_id', 10, 2);
$arrProducts = geodir_get_widget_listings($query_args);

$uploads = wp_upload_dir();
// $geodir_uploadpath = $uploads['path'];
// $geodir_uploadurl = $uploads['url'];

?>

<?php
global $post;
$current_post = $post;

foreach ( $arrProducts as $product )
{
  $post = $product;
  $GLOBALS['post'] = $post;
  setup_postdata($post);
  create_product_modal($post, $current_post->ID);
  echo '<div class="order-row">';
  echo '<div class="order-col-2">';
  if($post->featured_image != '')
      echo '<a href="'.get_the_permalink().'"><img src="'.$uploads['baseurl'].$post->featured_image.'" class="food-img" /></a>';
  else
      echo '<p></p>';
  echo '</div>';
  echo '<div class="order-col-6">';
  echo '<h3><strong><a href="'.get_the_permalink().'" style="color: #e34f43;">'.$post->post_title.'</a></strong></h3>';
  echo '<p style="overflow-wrap:break-word;">'.get_the_excerpt().'</p>';
  echo '</div>';
  echo '<div class="order-col-4" style="text-align:right;">';
  echo '<b>'.str_replace(".00", "",number_format($post->geodir_price,2)).' <sup>บาท</sup></b> ';
    if(!empty($current_post->geodir_tamzang_id)){
        if($post->geodir_show_addcart){
          echo '<button type="button" style="color:white;" 
            data-toggle="modal" data-target="#product_'.$post->ID.'">+</button>';
        }
    }
  echo '</div>';
  echo '</div>';
  echo '<div class="order-clear"></div>';
  echo '<hr>';
}

$GLOBALS['post'] = $current_post;
if (!empty($current_post)) {
    setup_postdata($current_post);
}
?>
