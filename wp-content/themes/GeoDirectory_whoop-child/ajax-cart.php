<?php /* Template Name: ajax-cart */ ?>

<?php
global $current_user;

$post_id = isset($post_id) ? $post_id : get_the_ID();
$arrProducts = tamzang_get_all_products_in_cart($current_user->ID, $post_id);

?>

<table id="tb-cart" class="table table-hover">
  <thead>
    <tr>
      <th>สินค้า</th>
      <th>จำนวน</th>
      <th>ราคา</th>
      <th>ทั้งหมด</th>
      <th> </th>
    </tr>
  </thead>
  <tbody>
    <?php

      $sum = 0;
      $uploads = wp_upload_dir();

      foreach ($arrProducts as $product) {
        $total = (int)$product->price*(int)$product->qty;
        $sum += $total;
        echo '<tr id="'.$product->product_id.'">';
          echo "<td>";
            echo "<div>";
              echo '<a class="thumbnail pull-left" href="#"> <img class="media-object" src="'.$uploads['baseurl'].$product->featured_image.'" style="width: 72px; height: 72px;"> </a>';
              echo '<div class="media-body">';
                echo '<h4 class="media-heading">'.$product->name.'</h4>';
              echo "</div>";
            echo "</div>";
          echo "</td>";
          echo '<td style="text-align: center">';


            echo '<div class="sp-quantity">';
            echo '<div class="input-group">';
            echo '<span class="input-group-btn">';
            echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-id="'.$product->product_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->product_id ).'">';
            echo '<span class="glyphicon glyphicon-minus"></span>';
            echo '</button>';
            echo '</span>';
            echo '<div class="sp-input">';
            echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->qty.'">';
            echo '</div>';
            echo '<span class="input-group-btn">';
            echo '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-type="plus" data-id="'.$product->product_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->product_id ).'">';
            echo '<span class="glyphicon glyphicon-plus"></span>';
            echo '</button>';
            echo '</span>';
            echo '</div>';
            echo '</div>';


            //echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->qty.'">';
          echo "</td>";
          echo '<td style="text-align: center">';
            echo '<strong><div id="'.$product->product_id.'-price" >'.$product->price.'</div></strong>';
          echo "</td>";
          echo '<td style="text-align: center">';
            echo '<strong><div id="'.$product->product_id.'-total" >'.$total.'</div></strong>';
          echo "</td>";
          echo "<td>";
            echo '<a class="btn btn-danger btn-xs" href="#"
            data-record-id="'.$product->product_id.'"
            data-record-title="'.$product->name.'"
            data-record-nonce="'.wp_create_nonce( 'delete_product_cart_' . $product->product_id ).'"
            data-toggle="modal" data-target="#confirm-delete" style="color:white;" ><span class="glyphicon glyphicon-trash"></span> ลบ</a>';
          echo "</td>";
        echo "</tr>";
      }
    ?>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td><h3>รวมทั้งหมด</h3></td>
      <td class="text-right"><h3><strong><div id="sum"><?php echo $sum; ?></div></strong></h3></td>
    </tr>
  </tbody>
</table>
