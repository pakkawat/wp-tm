<?php /* Template Name: ajax-cart */ ?>

<?php
global $current_user;

// $post_id = isset($post_id) ? $post_id : get_the_ID();
// if($post->post_type == 'gd_product')
//   $post_id = $post->geodir_shop_id;
// else
// $post_id = $post->ID;

$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);

?>




<table id="tb-cart" class="table">
  <thead>
    <tr>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php

      $sum = 0;
      $uploads = wp_upload_dir();

      foreach ($arrProducts as $product) {
        $total = (float)$product->geodir_price*(int)$product->shopping_cart_qty;
        $sum += $total;
        echo '<tr id="'.$product->ID.'">';
          echo "<td>";
            echo "<div>";
              if($product->featured_image != "")
                echo '<a class="thumbnail pull-left" href="#"> <img class="media-object" src="'.$uploads['baseurl'].$product->featured_image.'" style="width: 72px; height: 72px;"> </a>';
              // echo '<div class="media-body">';
              //   echo '<h4 class="media-heading" style="word-break:break-word;">'.$product->post_title.'@'.str_replace(".00", "",number_format($product->geodir_price,2)).'</h4>';
              // echo "</div>";
            echo "</div>";
          echo "</td>";
          echo '<td>';

            echo '<h4>'.$product->post_title.' @'.str_replace(".00", "",number_format($product->geodir_price,2)).'</h4>';

            echo '<div class="order-row">';

              echo '<div class="order-col-6">';
                echo '<strong><div id="'.$product->ID.'-total" class ="price" >'.str_replace(".00", "",number_format($total,2)).' บาท</div></strong>';
              echo '</div>';

              echo '<div class="order-col-6">';
                echo '<div class="sp-quantity">';
                echo '<div class="input-group">';
                echo '<span class="input-group-btn">';
                echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-id="'.$product->ID.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->ID ).'">';
                echo '<span class="glyphicon glyphicon-minus"></span>';
                echo '</button>';
                echo '</span>';
                echo '<div class="sp-input">';
                echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->shopping_cart_qty.'">';
                echo '</div>';
                echo '<span class="input-group-btn">';
                echo '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-type="plus" data-id="'.$product->ID.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->ID ).'">';
                echo '<span class="glyphicon glyphicon-plus"></span>';
                echo '</button>';
                echo '</span>';
                echo '</div>';
                echo '</div>';
              echo '</div>';

            echo '</div>';


          //   //echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->qty.'">';
          // echo "</td>";
          // // echo '<td style="text-align: center">';
          // //   echo '<strong><div id="'.$product->ID.'-price" >'.str_replace(".00", "",number_format($product->geodir_price,2)).'</div></strong>';
          // // echo "</td>";
          // echo '<td style="text-align: center">';
          //   echo '<strong><div id="'.$product->ID.'-total" class ="price" >'.str_replace(".00", "",number_format($total,2)).'</div></strong>';
          // echo "</td>";
          // echo "<td>";
          //   echo '<a class="btn btn-danger btn-xs" href="#"
          //   data-record-id="'.$product->ID.'"
          //   data-record-title="'.$product->post_title.'"
          //   data-record-nonce="'.wp_create_nonce( 'delete_product_cart_' . $product->ID ).'"
          //   data-toggle="modal" data-target="#confirm-delete" style="color:white;" ><span class="glyphicon glyphicon-trash"></span> ลบ</a>';
          // echo "</td>";
        echo "</tr>";
      }
    ?>
    <tr>
      <td style="text-align:left;white-space:nowrap;"><h3>รวมทั้งหมด</h3></td>
      <td style="text-align:right;"><h3><strong><div id="sum"><?php echo str_replace(".00", "",number_format($sum,2)); ?> บาท</div></strong></h3></td>
    </tr>
  </tbody>
</table>

