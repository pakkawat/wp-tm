<?php /* Template Name: my-shop */ ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
jQuery(document).ready(function($){

  $(document).on('change', '.open_shop', function(e) {
    var id = $(this).data('id');
    var nonce = $(this).data('nonce');
    var btn = $(this);
    console.log(id);

    $( ".table" ).toggleClass('order-status-loading');

    var send_data = 'action=change_shop_status&id='+id+'&nonce='+nonce;
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
        success: function(msg){
          console.log(msg);
          // if(msg.success){

          // }
          $( ".table" ).toggleClass('order-status-loading');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);
            $( ".table" ).html( textStatus );
        }
    });
  });

});
</script>
<?php 

global $wpdb, $current_user;

//$is_admin = current_user_can('administrator');

$my_query = new WP_Query( array(
    'post_type' => 'gd_place',
    'order'             => 'ASC',
    'orderby'           => 'title',
    'author' => $current_user->ID,
    'post_per_page' => -1,
    'nopaging' => true
) );

if ( $my_query->have_posts() ) {

  if ( wp_is_mobile() ){

      ?>

      <table class="table" style="position: relative;";>
          <thead>
          <th>ร้านค้า</th>

          </thead>
          <tbody>

          <?php

          while ( $my_query->have_posts() ) {

              $my_query->the_post();
              $array_shop_time = check_shop_open(get_the_ID());
              $shop_time = $array_shop_time['shop_time'];
              $is_shop_open = $array_shop_time['is_shop_open'];
              $tamzang_id = geodir_get_post_meta( get_the_ID(), 'geodir_tamzang_id', true );
              echo '<tr>';
              echo '<td>';
              echo '<a href="' .get_permalink(). ' ">';
              the_title();
              echo '</a>';
              if(!empty($shop_time) && !empty($tamzang_id)){
                echo '<div style="float: right;"><input type="checkbox" '.($is_shop_open ? 'checked' : '').' data-toggle="toggle" class="open_shop"
                data-id="'.get_the_ID().'" data-nonce="'.wp_create_nonce( 'change_shop_status_'.$current_user->ID.get_the_ID()).'"
                data-on="เปิดร้าน" data-off="ปิดร้าน"
                data-onstyle="success" data-offstyle="danger"></div>';
              }
              echo '<br><br><a class="btn btn-info btn-block" href="'. home_url('/shop-order/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >รายการสั่งซื้อของร้าน</span></a>';
              echo '<br>';
              // if($is_admin){
              //     echo '<div class="order-row">';
              //     echo '<div class="order-col-6"><a class="btn btn-success btn-block" href="'. home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.get_the_ID() .'"><span style="color: #ffffff !important;" >เพิ่มสินค้า</span></a></div>';
              //     echo '<div class="order-col-6"><a class="btn btn-primary btn-block" href="'. home_url('/product-list/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >แก้ไขสินค้า</span></a></div>';
              //     echo '</div>';
              // }
              echo '</td>';
              echo '</tr>';

          }


          ?>

          </tbody>
      </table>


      <?php

  }else{
    ?>
    <div class="table-responsive">
    <table class="table" style="position: relative;";>
    <thead>
    <th>ชื่อร้านค้า</th>
    <th></th>
    <th></th>
    </thead>
    <tbody>
    <?php

    while ( $my_query->have_posts() ) {

      $my_query->the_post();
      $array_shop_time = check_shop_open(get_the_ID());
      $shop_time = $array_shop_time['shop_time'];
      $is_shop_open = $array_shop_time['is_shop_open'];
      $tamzang_id = geodir_get_post_meta( get_the_ID(), 'geodir_tamzang_id', true );
      // echo '<div class="table-responsive">';
      // echo '<table class="table" style="position: relative;";>';
      // echo '<thead>';
      // echo '<th>ชื่อร้านค้า</th>';
      // if(!empty($shop_time) && !empty($tamzang_id))
      //   echo '<th></th>';
      // echo '<th></th>';
      // if($is_admin){
      //   echo '<th></th>';
      //   echo '<th></th>';
      // }
      // echo '</thead>';
      // echo '<tbody>';
      echo '<tr>';
      echo '<td>';
      echo '<a href="' .get_permalink(). ' ">';
      the_title();
      echo '</a>';
      echo '</td>';
      if(!empty($shop_time) && !empty($tamzang_id)){
        echo '<td><input type="checkbox" '.($is_shop_open ? 'checked' : '').' data-toggle="toggle" class="open_shop"
        data-id="'.get_the_ID().'" data-nonce="'.wp_create_nonce( 'change_shop_status_'.$current_user->ID.get_the_ID()).'"
        data-on="เปิดร้าน" data-off="ปิดร้าน" 
        data-onstyle="success" data-offstyle="danger"></td>';
      }else{
        echo '<td></td>';
      }
      echo '<td style="text-align:center;"><a class="btn btn-info btn-block" href="'. home_url('/shop-order/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >รายการสั่งซื้อของร้าน</span></a></td>';
      // if($is_admin){
      //     echo '<td style="text-align:center;"><a class="btn btn-success btn-block" href="'. home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.get_the_ID() .'"><span style="color: #ffffff !important;" >เพิ่มสินค้า</span></a></td>';
      //     echo '<td style="text-align:center;"><a class="btn btn-primary btn-block" href="'. home_url('/product-list/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >แก้ไขสินค้า</span></a></td>';
      // }
      echo '</tr>';

    }


      ?>

      </tbody>
    </table>
  </div>

  <?php
  }


}

?>