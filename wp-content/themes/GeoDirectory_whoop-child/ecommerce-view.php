<?php /* Template Name: ecommerce-view */ ?>

<?php

$post_id = get_query_var('post_id');
$cat_id = get_query_var('cat_id');
//$arrProducts = tamzang_get_all_products($post->ID,$cat_id);

$query_args = array(
  'is_geodir_loop' => true,
  'post_type' => 'gd_product',
  'posts_per_page' => -1,
  'order_by' => 'post_title'
);

add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);
$arrProducts = geodir_get_widget_listings($query_args);

$uploads = wp_upload_dir();
// $geodir_uploadpath = $uploads['path'];
// $geodir_uploadurl = $uploads['url'];

?>

<div class="tamzang-flex">
<?php
global $post;
$current_post = $post;
foreach ( $arrProducts as $product )
{
  $post = $product;
  $GLOBALS['post'] = $post;
  setup_postdata($post);
  create_product_modal($post, $current_post->ID);
  echo'<section>';
  if($post->featured_image != '')
    echo '<img src="'.$uploads['baseurl'].$post->featured_image.'" style="width250px;height:250px;" />';
    echo '<h3><strong><a href="'.get_the_permalink().'" style="color: #e34f43;">'.$post->post_title.'</a></strong></h3>';
  echo '<p style="overflow-wrap:break-word;">'.get_the_excerpt().'</p>';
  echo '<aside>';
  echo '<ul>';
  echo '<li>ราคา: '.str_replace(".00", "",number_format($post->geodir_price,2)).' บาท</li>';
  echo '<li>มีสินค้า</li>';
  echo '</ul>';
  echo '<button type="button" style="color:white;" data-toggle="modal" data-target="#product_'.$product->ID.'">';
  echo 'เพิ่มลงตะกร้า';
  echo '</button>';
  echo '</aside>';
  echo '</section> ';
}
$GLOBALS['post'] = $current_post;
if (!empty($current_post)) {
    setup_postdata($current_post);
}
?>
</div>
