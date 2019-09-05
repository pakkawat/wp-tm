<?php /* Template Name: confirmed order */ 

global $wpdb, $current_user;
$user_has_address = false;
if ( is_user_logged_in() ){
  $pid = $_REQUEST['pid'];

  $arrProducts = tamzang_get_all_products_in_cart($current_user->ID);

  if (empty($arrProducts))
    wp_redirect(get_page_link($pid));

  /* 20190627 Bank put deliver fee */
  $default_category_id = geodir_get_post_meta( $pid, 'default_category', true );
  $default_category = $default_category_id ? get_term( $default_category_id, 'gd_placecategory' ) : '';
  $parent = get_term($default_category->parent);

  //Get Delivery_fee and distance
  if(($parent->name == "อาหาร")||($default_category->name == "อาหาร"))
  {
    list($delivery_fee,$distance) = get_delivery_fee($pid);
  }	


  
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

      function update_product_cart($button, newVal){
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
                    $button.closest('.sp-quantity').find("p.quntity-input").text(newVal);
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
      }

      function delete_product_cart($button){
        $('.wrapper-loading').toggleClass('cart-loading');
        var id = $button.data( 'id' );
        var nonce_delete = $button.data( 'delete' );
        var send_data = 'action=delete_product_cart&id='+id+'&nonce='+nonce_delete;
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

      }

      //$(".btn-tamzang-quantity").on("click", function () {
      jQuery(document).on("click", ".btn-tamzang-quantity", function(){

          var $button = $(this);
          var oldValue = $button.closest('.sp-quantity').find("p.quntity-input").text();

          if ($button.data( 'type' ) == "plus") {
              update_product_cart($button, parseFloat(oldValue) + 1);
          } else {
              if (oldValue > 1) {
                  update_product_cart($button, parseFloat(oldValue) - 1);
              } else {
                  delete_product_cart($button);
              }
          }
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
            var payment_type = $("input[name=payment-type]:checked").val();

            if($('#use_point').is(":checked")){
              $('#hidden_up').val("true");
            }else{
              $('#hidden_up').val("");
            }
          }
        });

        $("#place_order").click(function(){
          $("#payment-error").hide();
          var rowCount = $('#tb-cart >tbody >tr').length;
          if (rowCount > 2){
            $("#select-payment-type").modal();
          }
          else {
            $("#no-item").modal();
          }
        });


        jQuery(document).on("click", ".select-shipping", function(){
          var id = $(this).data('id');
          var nonce = $(this).data('nonce');
          var shop_id = $(this).data('shop-id');

          $('.address-wrapper-loading').toggleClass('cart-loading');
          var send_data = 'action=confirm_order_select_shipping&id='+id+'&shop_id='+shop_id+'&nonce='+nonce;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    $('#address-'+msg.data.pre_id).html(msg.data.pre);
                    $('#address-'+id).html(msg.data.select);
                    $('#shipping-address').text(msg.data.select_address);
                  }

                  $('.address-wrapper-loading').toggleClass('cart-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log(textStatus);
              $('.address-wrapper-loading').toggleClass('cart-loading');
            }
          });

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
                  <input type="hidden" name="hidden_up" id="hidden_up" />
                  <button type="submit" class="btn btn-success btn-ok">ตกลง</button>
              </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="select-shipping" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 0 none;padding: 15px 15px 0 15px;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">เปลี่ยนที่อยู่ในการจัดส่ง</h4>
                <a class="btn btn-info" href="<?php echo bp_get_loggedin_user_link().'address/';?>"><span style="color: #ffffff !important;">เพิ่มที่อยู่</span></a>
            </div>
            <div class="modal-body">
              <div class="address-wrapper-loading">
                <?php get_template_part( 'address/select', 'shipping' ); ?>
              </div>
            </div>

        </div>
    </div>
</div>


<div id="geodir_wrapper" class="geodir-single">
<?php 
  echo '<strong>ร้าน:</strong> <a href="'.get_page_link($pid).'">'.get_the_title($pid).'</a><br>';
  echo '<strong>ที่อยู่ในการจัดส่ง:</strong> <div id="shipping-address">'.$user_address->address." ".$user_address->district." ".$user_address->province." ".$user_address->postcode."</div> ";
?>
<a class="btn btn-info" data-toggle="modal" data-target="#select-shipping" ><span style="color: #ffffff !important;" >แก้ไขที่อยู่</span></a>
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
        <div class="wrapper-loading">
          <table class="confirm-table" id="tb-cart">
            <thead>
              <tr>
                <th>สินค้า</th>
                <th style="width:50px">จำนวน</th>
                <th>ทั้งหมด</th>
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
                          if($post->featured_image != "")
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
                      echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  
                            data-delete="'.wp_create_nonce( 'delete_product_cart_' . $post->ID ).'"
                            data-type="minus" data-id="'.$post->ID.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $post->ID ).'">';
                      echo '<span class="glyphicon glyphicon-minus"></span>';
                      echo '</button>';
                      echo '</span>';
                      echo '<div class="sp-input">';
                      echo '<p class="quntity-input" style="width:30px;">'.$post->shopping_cart_qty.'</p>';
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
                      echo '<strong><div id="'.$post->ID.'-total" class ="price" >'.str_replace(".00", "",number_format($total,2)).'</div></strong>';
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
                <td>
                  <?php
                    $user_cash_back = $wpdb->get_row(
                      $wpdb->prepare(
                          "SELECT * FROM cash_back where user_id = %d ", array($current_user->ID)
                      )
                    );
                    if(!empty($user_cash_back)){
                      $point = $user_cash_back->add_on_credit * $user_cash_back->redeem_point_rate;
                      if($point >= $delivery_fee && ($parent->name == "อาหาร" || $default_category->name == "อาหาร") && $delivery_fee != 0){
                        echo 'ขณะนี้คุณมี point มากพอจะใช้แทนค่าส่งกรุณาเลือก หากต้องการใช้: <input type="checkbox" name="use_point" id="use_point" />';
                      }
                    }
                  ?>
                </td>
                <td><div class="popup" onclick="myPopupFunc()">ค่าจัดส่ง*
					<span class="popuptext" id="myPopup">ค่าส่งเบื้องต้น 30 บาท รวมกับระยะทาง 3 กม.แรกคิดกม.ล่ะ 10 บาท กม.ถัดไปคิด กม.ล่ะ 15 บาท</span></div>
				</td>
                <td class="text-right"><strong><div id="delivery"><?php echo $delivery_fee; ?></div></strong></td>
              </tr>
              <tr>
                <td></td>
                <td><h3>รวมทั้งหมด</h3></td>
                <td class="text-right"><h3><strong><div id="sum"><?php echo str_replace(".00", "",number_format($sum,2)); ?></div></strong></h3></td>
              </tr>
            </tbody>
          </table>
            <div style="float:right;">
              <?php if($user_has_address){
			  if(($delivery_fee == 0) and ($distance == 0) and (($parent->name == "อาหาร")||($default_category->name == "อาหาร")))
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
