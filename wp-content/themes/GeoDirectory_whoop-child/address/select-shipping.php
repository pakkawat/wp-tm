<?php /* Template Name: select-shipping */ ?>

<?php
global $wpdb, $current_user;

$sql = "SELECT * FROM user_address where wp_user_id = ".$current_user->ID." ";
$arrAddress  = $wpdb->get_results( $sql );

?>

<?php if ( wp_is_mobile() ){ ?>

<div class="table-responsive">
<table id="shipping_table" class="table table-bordred table-striped">
  <tbody>
  <?php

    foreach ($arrAddress as $address) {
      echo '<tr>';
        echo "<td>";
          echo $address->name.'<br>';
          echo $address->address.'<br>';
          echo $address->province.'-'.$address->district.'-'.$address->postcode.'<br>';
          echo 'โทร :'.$address->phone.'<br>';
          echo '<div id="address-'.$address->id.'">';
            if(!$address->shipping_address){
              echo '<a class="btn btn-success select-shipping" href="#"
              data-id="'.$address->id.'"
              data-shop-id="'.$_REQUEST['pid'].'"
              data-nonce="'.wp_create_nonce( 'select_shipping_address' . $address->id ).'"
              style="color:white;" >เลือก</a>';
            }else{
              echo '<img src="'.get_stylesheet_directory_uri().'/js/pass.png" />';
            }
          echo "</div>";
        echo "</td>";
      echo '</tr>';
    }

  ?>
  </tbody>
</table>
</div>



<?php }else{ ?>

<div class="table-responsive">
<table id="shipping_table" class="table table-bordred table-striped">
  <thead>
    <th>ชื่อ-สกุล</th>
    <th>ที่อยู่</th>
    <th>รหัสไปรษณีย์</th>
    <th>เบอร์โทรศัพท์</th>
    <th></th>
  </thead>
  <tbody>
  <?php

    foreach ($arrAddress as $address) {
      echo '<tr id="tr_row_'.$address->id.'">';
      echo '<td>'.$address->name.'</td>';
      echo '<td>'.$address->address.'</td>';
      echo '<td>'.$address->province.'-'.$address->district.'-'.$address->postcode.'</td>';
      echo '<td>'.$address->phone.'</td>';
      echo '<td id="address-'.$address->id.'">';
        if(!$address->shipping_address){
          echo '<a class="btn btn-success select-shipping" href="#"
          data-id="'.$address->id.'"
          data-shop-id="'.$_REQUEST['pid'].'"
          data-nonce="'.wp_create_nonce( 'select_shipping_address' . $address->id ).'"
          style="color:white;" >เลือก</a>';
        }else{
          echo '<img src="'.get_stylesheet_directory_uri().'/js/pass.png" />';
        }
      echo "</td>";
      echo '</tr>';
    }

  ?>
  </tbody>
</table>
</div>

<?php } ?>