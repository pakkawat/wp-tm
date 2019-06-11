<?php /* Template Name: confirmed order */ 

global $wpdb, $current_user;
$user_has_address = false;
if ( is_user_logged_in() ){
  $pid = $_REQUEST['pid'];

  $arrProducts = tamzang_get_all_products_in_cart($current_user->ID);

  if (empty($arrProducts))
    wp_redirect(get_page_link($pid));

  //Get Delivery_fee and distance
  list($delivery_fee,$distance) = get_delivery_fee($pid);
  
  $user_address = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT * FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array($current_user->ID)
      )
  );  
  if($wpdb->num_rows > 0)
  {
    $user_has_address = true;
  }

}else{
  wp_redirect(home_url());
}
?>
<?php get_header(); ?>

<script>
    jQuery(document).ready(function($){

      function display_currency(money){
        money = (money).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        parts = money.split(".");
        if(parts[1] > 0)
          money = parts[0]+"."+parts[1];
        else
          money = parts[0];
        return money;
      }

      //$(".btn-tamzang-quantity").on("click", function () {
      jQuery(document).on("click", ".btn-tamzang-quantity", function(){

          var $button = $(this);
          var oldValue = $button.closest('.sp-quantity').find("input.quntity-input").val();

          if ($button.data( 'type' ) == "plus") {
              var newVal = parseFloat(oldValue) + 1;
          } else {
              // Don't allow decrementing below zero
              if (oldValue > 1) {
                  var newVal = parseFloat(oldValue) - 1;
              } else {
                  newVal = 1;
              }
          }
          $('.wrapper-loading').toggleClass('cart-loading');
          var id = $button.data( 'id' );
          //console.log($("#"+id+"-price").text());
          var nonce = $button.data( 'nonce' );
          var send_data = 'action=update_product_cart&id='+id+'&nonce='+nonce+'&qty='+newVal;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    
                    console.log( "update confirm: " + JSON.stringify(msg) );
                    $button.closest('.sp-quantity').find("input.quntity-input").val(newVal);
                    var total = display_currency(msg.data);
                    $("#"+id+"-total").text(total);
                    var sum = 0;
                    $(".price").each(function() {
                      var value = $(this).text().replace(/,/g, '');
                      // add only if the value is number
                      if(!isNaN(value) && value.length != 0) {
                          sum += parseFloat(value);
                      }
                    });
                    sum = display_currency(sum+parseFloat($("#delivery").html()));
                    $("#sum").text(sum);
                  }

                  $('.wrapper-loading').toggleClass('cart-loading');
                  //console.log( "Data Saved: " + msg );
                  //console.log(tamzang_ajax_settings.ajaxurl);
                  // ถ้า msg = 0 แสดงว่าไม่ได้ login
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
               console.log(textStatus);
               $('.wrapper-loading').toggleClass('cart-loading');
            }
          });

      });




      $('#confirm-delete').on('click', '.btn-ok', function(e) {
          var $modalDiv = $(e.delegateTarget);
          var id = $(this).data('recordId');
          var nonce = $(this).data('recordNonce');
          //console.log(id);
          // $.ajax({url: '/api/record/' + id, type: 'DELETE'})
          // $.post('/api/record/' + id).then()
          $modalDiv.addClass('loading');
          var send_data = 'action=delete_product_cart&id='+id+'&nonce='+nonce;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    console.log( "Data deleted: " + JSON.stringify(msg) );
                    $('#' + id).remove();
                    var sum = 0;
                    $(".price").each(function() {
                      var value = $(this).text().replace(/,/g, '');
                      // add only if the value is number
                      if(!isNaN(value) && value.length != 0) {
                          sum += parseFloat(value);
                      }
                    });
                    sum = display_currency(sum+parseFloat($("#delivery").html()));
                    $("#sum").text(sum);
                  }

                  $modalDiv.modal('hide').removeClass('loading');
                  //console.log( "Data Saved: " + msg );
                  //console.log(tamzang_ajax_settings.ajaxurl);
                  // ถ้า msg = 0 แสดงว่าไม่ได้ login
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log(textStatus);
              $modalDiv.modal('hide').removeClass('loading');
            }
          });

        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
            $('.btn-ok', this).data('recordNonce', data.recordNonce);
        });

        $('#select-payment-type').on('click', '.btn-ok', function(e) {
          if($('input[name=payment-type]:checked').length<=0)
          {
            $("#payment-error").show();
            e.preventDefault();
          }
          else
          {
            var $modalDiv = $(e.delegateTarget);
            var id = $(this).data('recordId');
            console.log(id);
            var payment_type = $("input[name=payment-type]:checked").val();
            console.log("payment_type: "+payment_type);
          }
        });

        $("#place_order").click(function(){
          $("#payment-error").hide();
          var rowCount = $('#tb-cart >tbody >tr').length;
          console.log(rowCount);
          if (rowCount > 1){
            console.log("nonce:"+$(this).data('nonce'));
            $("#select-payment-type").modal();
          }
          else {
            $("#no-item").modal();
          }
        });

    });

	// When the user clicks on div, open the popup
function myPopupFunc() {
  var popup = document.getElementById("myPopup");
  popup.classList.toggle("show");
}
	
</script>

<?php if(!$user_has_address){ ?>
<div class="modal fade" id="no-address" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ข้อความ</h4>
            </div>
            <div class="modal-body">
                <p>คุณยังไม่ได้เพิ่มที่อยู่ในการจัดส่งกรุณา <a href="<?php echo bp_get_loggedin_user_link().'address/';?>">คลิก</a> เพื่อเพิ่มที่อยู่ในการจัดส่ง</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">ตกลง</button>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="modal fade" id="no-item" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ข้อความ</h4>
            </div>
            <div class="modal-body">
                <p>กรุณาเลือกสินค้า</p>
                <p>กลับไปที่ร้านค้า <a href="<?php echo get_page_link($pid);?>">คลิก</a></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ยืนยันการลบสินค้า</h4>
            </div>
            <div class="modal-body">
                <p>คุณกำลังจะลบสินค้า <b><i class="title"></i></b></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="select-payment-type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">กรุณาเลือกวิธีชำระเงิน</h4>
            </div>
            <form action="<?php echo home_url('/my-order/'); ?>" method="post" name="place_order_form">
              <div class="modal-body">
                  <p>
                    <?php 
                      $default_category_id = geodir_get_post_meta( $pid, 'default_category', true );
                      $default_category = $default_category_id ? get_term( $default_category_id, 'gd_placecategory' ) : '';
					  //echo print_r($default_category);
                      $parent = get_term($default_category->parent);
                      if(($parent->name != "อาหาร")&&($default_category->name != "อาหาร")){
                    ?>
                        <div>
                          <input class="form-check-input" type="radio" name="payment-type" value="1" id="pt-radio1">
                          <label class="form-check-label" for="pt-radio1">
                            โอนเงิน
                          </label>
                        </div>
                    <?php } ?>
                    <div>
                      <input class="form-check-input" type="radio" name="payment-type" value="2" id="pt-radio2">
                      <label class="form-check-label" for="pt-radio2">
                        เก็บเงินปลายทาง
                      </label>
                    </div>
                  </p>
                  <p><label id="payment-error" style="color:red;display:none;">กรุณาเลือกวิธีชำระเงิน</label></p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                  <input type="hidden" name="pid" value="<?php echo $pid; ?>"/>
                  <button type="submit" class="btn btn-success btn-ok">ตกลง</button>
              </div>
            </form>
        </div>
    </div>
</div>


<div id="geodir_wrapper" class="geodir-single">
<?php 
  echo '<strong>ร้าน:</strong> <a href="'.get_page_link($pid).'">'.get_the_title($pid).'</a><br>';
  echo "<strong>ที่อยู่ในการจัดส่ง:</strong> ".$user_address->address." ".$user_address->district." ".$user_address->province." ".$user_address->postcode." ";
?>
<a class="btn btn-info" href="<?php echo bp_get_loggedin_user_link().'address/';?>"><span style="color: #ffffff !important;" >แก้ไขที่อยู่</span></a>
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
        <div class="wrapper-loading">
          <table class="confirm-table" id="tb-cart">
            <thead>
              <tr>
                <th>สินค้า</th>
                <th style="width:50px">จำนวน</th>
                <th>ราคา</th>
                <th>ทั้งหมด</th>
                <th> </th>
              </tr>
            </thead>
            <tbody>
              <?php
                global $post;
                $current_post = $post;
                $sum = 0;
                $uploads = wp_upload_dir();

                foreach ($arrProducts as $product) {
                  $post = $product;
                  $GLOBALS['post'] = $post;
                  setup_postdata($post);
                  $total = (float)$post->geodir_price*(int)$post->shopping_cart_qty;
                  $sum += $total;
                  echo '<tr id="'.$post->ID.'">';
                    echo "<td>";
                      echo '<div class="order-row">';
                        echo '<div class="order-col-3">';
                          echo '<img style="width:72px;height:72px;" src="'.$uploads['baseurl'].$post->featured_image.'">';
                        echo "</div>";
                        echo '<div class="order-col-9">';
                          echo '<h4 class="product-name"><strong><a href="'.get_the_permalink().'" style="color: #e34f43;">'.$post->post_title.'</a></strong></h4>';
                          echo '<p style="overflow-wrap:break-word;">'.get_the_excerpt().'</p>';
                        echo "</div>";
                      echo "</div>";
                      echo '<div class="order-clear"></div>';
                      //   echo '<a class="thumbnail pull-left" href="#"> <img class="media-object" src="'.$uploads['baseurl'].$product->featured_image.'" style="width: 72px; height: 72px;"> </a>';
                      //   echo '<div class="media-body">';
                      //     echo '<h4 class="media-heading">'.$product->name.'</h4>';
                      //     //echo '<h5 class="media-heading"> ร้าน <a href="'.get_page_link($product->post_id).'">'.get_the_title($product->post_id).'</a></h5>';
                      //     echo '<span><strong>'.$product->short_desc.'</strong></span>';
                      //   echo "</div>";
                      // echo "</div>";
                    echo "</td>";
                    echo '<td>';


                      echo '<div class="sp-quantity">';
                      echo '<div class="input-group">';
                      echo '<span class="input-group-btn">';
                      echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-id="'.$post->ID.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $post->ID ).'">';
                      echo '<span class="glyphicon glyphicon-minus"></span>';
                      echo '</button>';
                      echo '</span>';
                      echo '<div class="sp-input">';
                      echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$post->shopping_cart_qty.'">';
                      echo '</div>';
                      echo '<span class="input-group-btn">';
                      echo '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" 
                            data-type="plus" data-id="'.$post->ID.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $post->ID ).'"
                            style="margin-left:0px">';
                      echo '<span class="glyphicon glyphicon-plus"></span>';
                      echo '</button>';
                      echo '</span>';
                      echo '</div>';
                      echo '</div>';

                      //echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->qty.'">';
                    echo "</td>";
                    echo '<td>';
                      echo '<strong><div id="'.$post->ID.'-price" >'.str_replace(".00", "",number_format($post->geodir_price,2)).'</div></strong>';
                    echo "</td>";
                    echo '<td>';
                      echo '<strong><div id="'.$post->ID.'-total" class ="price" >'.str_replace(".00", "",number_format($total,2)).'</div></strong>';
                    echo "</td>";
                    echo "<td>";
                      echo '<a class="btn btn-danger btn-xs" href="#"
                      data-record-id="'.$post->ID.'"
                      data-record-title="'.$post->post_title.'"
                      data-record-nonce="'.wp_create_nonce( 'delete_product_cart_' . $post->ID ).'"
                      data-toggle="modal" data-target="#confirm-delete" style="color:white;" ><span class="glyphicon glyphicon-trash"></span> ลบ</a>';
                    echo "</td>";
                  echo "</tr>";
                }

                $GLOBALS['post'] = $current_post;
                if (!empty($current_post)) {
                    setup_postdata($current_post);
                }
				        $sum += $delivery_fee;
    		      ?>
			<tr>
                <td></td>
                <td></td>
                <td></td>
                <td><div class="popup" onclick="myPopupFunc()">ค่าจัดส่ง*
					<span class="popuptext" id="myPopup">ค่าส่งเบื้องต้น 30 บาท รวมกับระยะทาง 3 กม.แรกคิดกม.ล่ะ 10 บาท กม.ถัดไปคิด กม.ล่ะ 15 บาท</span></div>
				</td>
                <td class="text-right"><strong><div id="delivery"><?php echo $delivery_fee; ?></div></strong></td>
              </tr>
              <tr>
                <td>
                </td>
                <td></td>
                <td></td>
                <td><h3>รวมทั้งหมด</h3></td>
                <td class="text-right"><h3><strong><div id="sum"><?php echo str_replace(".00", "",number_format($sum,2)); ?></div></strong></h3></td>
              </tr>
            </tbody>
          </table>
            <div style="float:right;">
              <?php if($user_has_address){
			  if(($delivery_fee == 0) and ($distance == 0))
			  {
				?>
				<h3>ขณะนี้ระบบไม่สามารถคำนวนค่าจัดส่งได้ ขออภัยในความไม่สะดวก</h3>
			  <?php  
			  }
			  else{
			  ?>
                <button type="button" class="btn btn-success" id="place_order">
                    ดำเนินการสั่งสินค้า <span class="glyphicon glyphicon-play"></span>
                </button>
              <?php }
			  }else{ ?>
                <button class="btn btn-success" data-toggle="modal" data-target="#no-address"
                  ><span class="glyphicon glyphicon-play"></span> สั่งเลย</button>
              <?php } ?>
            </div>
        </div>
    </div>

  </div>
</div>
<?php get_footer(); ?>
