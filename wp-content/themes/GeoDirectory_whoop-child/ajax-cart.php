<?php /* Template Name: ajax-cart */ ?>

<?php
global $current_user;

// $post_id = isset($post_id) ? $post_id : get_the_ID();
// if($post->post_type == 'gd_product')
//   $post_id = $post->geodir_shop_id;
// else
// $post_id = $post->ID;

//$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);

// SELECT product.post_id as product_id, product.post_title, cart.id as cart_id, cart.qty, cart.product_price, item_detail.*
// FROM wp_geodir_gd_product_detail as product
// INNER JOIN shopping_cart as cart
// ON product.post_id = cart.product_id
// LEFT OUTER JOIN shopping_cart_item_destials as item_detail
// on item_detail.shopping_cart_id = cart.id
// WHERE cart.wp_user_id = 6 AND product.geodir_shop_id = 2337
$pid = $_GET['pid'];
$product_in_cart = $wpdb->get_results(
  $wpdb->prepare(
      "SELECT 
      cart.id as cart_id, cart.qty, cart.product_price, cart.product_title, cart.special,
      item_detail.*
      FROM shopping_cart as cart
      LEFT OUTER JOIN wp_geodir_gd_product_detail as product
      ON product.post_id = cart.product_id
      LEFT OUTER JOIN shopping_cart_item_destials as item_detail
      on item_detail.shopping_cart_id = cart.id
      WHERE cart.wp_user_id = %d AND product.geodir_shop_id = %d
      ORDER BY cart_id",
      array($current_user->ID, $pid)
  )
);

?>

<style>
.p-options{
  color:grey;
  font-size:12px;
  line-height:18px;
}
.remove_cart_item{
  cursor: pointer;
}
</style>


<table id="tb_cart" class="table" style="margin-top: 10px;">
  <thead>
    <tr>
      <th>Qty</th>
      <th>Item</th>
      <th>Price(บาท)</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php
      $sum_all = 0;
      $sum_product_price = 0;
      $uploads = wp_upload_dir();
      $temp_cart_id = 0;
      $pre_product_price = 0;
      $pre_qty = 0;
      $str_tr = "";

      foreach ($product_in_cart as $product) {
        if($temp_cart_id == 0){//start first loop
          $temp_cart_id = $product->cart_id;
          $pre_product_price = (float)$product->product_price;
          $pre_qty = (int)$product->qty;
          $str_tr = "";
          $pre_product_spacial = $product->special;

          echo '<tr id="tb_cart_'.$product->cart_id.'">';
          echo'<td>'.$product->qty.'</td>';
          echo '<td>';
          echo '<div class="row">';
          echo '<h4>'.$product->product_title.'</h4>';
          echo'</div>';
          echo '<div class="row">';// start product options
          if(!empty($product->choice_group_title)){
            echo '<div class="col-12 p-options">';
            echo $product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')';
            echo '</div>';
            $sum_product_price += (float)$product->extra_price;
          }

        }else if($product->cart_id != $temp_cart_id){
          // end tr
          $sum_product_price = ($sum_product_price+$pre_product_price)*$pre_qty;
          echo '<div class="col-12 p-options">'.$pre_product_spacial.'</div>';
          echo '</div>';// close product options
          echo '</td>';
          echo '<td>';
          echo '<strong><div id="'.$temp_cart_id.'-total" class="price" data-total="'.round($sum_product_price, 2).'">'.str_replace(".00", "",number_format($sum_product_price,2)).'</div></strong>';
          echo '</td>';
          echo '<td><i class="fa fa-remove remove_cart_item" data-nonce="'.wp_create_nonce( 'remove_cart_item_'.$current_user->ID.$temp_cart_id).'" data-cart_id="'.$temp_cart_id.'"></i></td>';
          echo '</tr>';
          $sum_all += $sum_product_price;

          //echo $str_tr;

          // start new product
          $temp_cart_id = $product->cart_id;
          $pre_product_price = (float)$product->product_price;
          $pre_qty = (int)$product->qty;
          $sum_product_price = 0;
          $pre_product_spacial = $product->special;
          
          echo '<tr id="tb_cart_'.$product->cart_id.'">';
          echo '<td>'.$product->qty.'</td>';
          echo '<td>';
          echo '<div class="row">';
          echo '<h4>'.$product->product_title.'</h4>';
          echo '</div>';
          echo '<div class="row">';// start product options
          if(!empty($product->choice_group_title)){
            echo '<div class="col-12 p-options">';
            echo $product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')';
            echo '</div>';
            $sum_product_price += (float)$product->extra_price;
          }
          
        }else{// product options
          if(!empty($product->choice_group_title)){
            echo '<div class="col-12 p-options">';
            echo $product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')';
            echo '</div>';
            $sum_product_price += (float)$product->extra_price;
          }

        }
        
      }

      if(count($product_in_cart) > 0){
        // end tr
        $sum_product_price = ($sum_product_price+$pre_product_price)*$pre_qty;
        $product = end($product_in_cart);
        echo '<div class="col-12 p-options">'.$product->special.'</div>';
        echo '</div>';// close product options
        echo '</td>';
        echo '<td>';
        echo '<strong><div id="'.$temp_cart_id.'-total" class="price" data-total="'.round($sum_product_price, 2).'">'.str_replace(".00", "",number_format($sum_product_price,2)).'</div></strong>';
        echo '</td>';
        echo '<td><i class="fa fa-remove remove_cart_item" data-nonce="'.wp_create_nonce( 'remove_cart_item_'.$current_user->ID.$temp_cart_id).'" data-cart_id="'.$temp_cart_id.'"></i></td>';
        echo '</tr>';
        $sum_all += $sum_product_price;
      }
    ?>
    <tr>
      <td colspan="2" style="text-align:left;white-space:nowrap;"><h3>รวมทั้งหมด</h3></td>
      <td colspan="2" ><h3><strong><div id="sum" data-sum="<?php echo round($sum_all, 2);?>">
      <h3><strong><?php echo str_replace(".00", "",number_format($sum_all,2)); ?> บาท</strong></h3></td>
    </tr>
    <tr>
      <td colspan="2">==</td>
      <td colspan="2">==</td>
    </tr>
  </tbody>
</table>

