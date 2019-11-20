<?php /* Template Name: confirmed order */ 

global $wpdb, $current_user;
$user_has_address = false;
if ( is_user_logged_in() ){
  $pid = $_REQUEST['pid'];

  $arrProducts = tamzang_get_all_products_in_cart($current_user->ID);

  if (empty($arrProducts))
    wp_redirect(get_page_link($pid));


  $delivery_type = geodir_get_post_meta( $pid, 'geodir_delivery_type', true );
  $groupID = geodir_get_post_meta( $pid, 'groupID', true );
  $shop_has_driver = false;
  if($groupID != 0){
    $check_driver = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT driver_id FROM driver where groupID like '%".$groupID."%' ", array()
      )
    );
  
    if(!empty($check_driver))
      $shop_has_driver = true;
  }

    
  if($delivery_type != 0)
  {
    list($delivery_fee,$distance) = get_delivery_fee($pid,$delivery_type);
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

        jQuery(document).on("click", "#place_order", function(){
          if($('#use_point').is(":checked")){
              console.log(" use point");
            }else{
              console.log(" Not use point");
            }
          $("#payment-error").hide();
          if ($('.sp-quantity').length > 0){
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
          var total = $("#cart-total").data('cart-total');
          console.log("Nonce: "+total);


          $('.address-wrapper-loading').toggleClass('cart-loading');
          var dtype = document.getElementById("dtype").value;
          var send_data = 'action=confirm_order_select_shipping&id='+id+'&shop_id='+shop_id+'&nonce='+nonce+'&total='+total+'&deli_type='+dtype;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    $('#address-'+msg.data.pre_id).html(msg.data.pre);
                    $('#address-'+id).html(msg.data.select);
                    $('#shipping-address').text(msg.data.select_address);
                    //$(".address-wrapper-loading").load(window.location.href +  " .address-wrapper-loading");
                    
                         
                    console.log(msg.data);  
                    if(dtype != 0){
                      $('#delivery_input').val(msg.data.new_delivery_fee);
                      $('#distance_input').val(msg.data.new_distance);
                      $('#delivery').html(msg.data.new_delivery_fee);
                      $('#sum').html(msg.data.new_sum.toFixed(2));
                    } 
                    else{
                      $('#delivery_input').val(0);
                      $('#distance_input').val(0);
                      $('#delivery').html(0);
                      $('#sum').html(total.toFixed(2));
                    }                   
                    if((msg.data.new_order_button == 0) && (dtype != 0))
                    {                      
                      $("#place_order_button").html("<h3>ขณะนี้ระบบไม่สามารถคำนวนค่าจัดส่งได้ กรุณาลองใหม่ภายหลัง ขออภัยในความไม่สะดวก</h3>");

                    }
                    else{ 
                      $("#place_order_button").html("<button type='button' class='btn btn-success' id='place_order'>ดำเนินการสั่งสินค้า <span class='glyphicon glyphicon-play'></span></button>");
                    } 
                  }
                  $('.address-wrapper-loading').toggleClass('cart-loading');                  
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log(textStatus);
              $('.address-wrapper-loading').toggleClass('cart-loading');
            }
          });

        });

        jQuery(document).on("click", ".select-delivery_type", function(){
          var pid = $(this).data('pid');
          var dtype = $(this).data('dtype');
          var nonce = $(this).data('nonce');
          var total = $("#cart-total").data('cart-total');

          console.log("Total"+total);

          $('.delivery_type-loading-loading').toggleClass('cart-loading');
          var send_data = 'action=select_delivery_type&pid='+pid+'&dtype='+dtype+'&nonce='+nonce+'&total='+total;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    $('#delivery_type_'+msg.data.pre_type).html(msg.data.pre);
                    $('#delivery_type_'+msg.data.select_type).html(msg.data.select);
                    $('#dtype').val(msg.data.select_type);
                    $('#delivery').html(msg.data.new_delivery_fee);
                    $('#sum').html(msg.data.new_sum.toFixed(2));
                    $('#delivery_input').val(msg.data.new_delivery_fee);


                    if(msg.data.new_order_button == 0)
                    {             
                      console.log("No  deli ");         
                      $("#place_order_button").html("<h3>ขณะนี้ระบบไม่สามารถคำนวนค่าจัดส่งได้ กรุณาลองใหม่ภายหลัง ขออภัยในความไม่สะดวก</h3>");
                    }
                    else{ 
                      console.log("Have  deli "); 
                      $("#place_order_button").html("<button type='button' class='btn btn-success' id='place_order'>ดำเนินการสั่งสินค้า <span class='glyphicon glyphicon-play'></span></button>");
                    }
                    if(msg.data.select_type == 1)
                    {
                      document.getElementById("use_point_text").style.display = "block";
                      document.getElementById("use_point").checked = false;
                    }
                    else
                    {
                      document.getElementById("use_point_text").style.display = "none";
                      document.getElementById("use_point").checked = false;
                    }
                  }

                  $('.delivery_type-loading-loading').toggleClass('cart-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log(textStatus);
              $('.delivery_type-loading-loading').toggleClass('cart-loading');
            }
          });

        });

        jQuery(document).on("click", ".promotion-input", function(){
          var pid = $(this).data('pid');
          var nonce = $(this).data('nonce');
          var promotion_input = $.trim( $('#promotion_input').val() );
          OneSignal.push(function() {
            OneSignal.isPushNotificationsEnabled(function(isEnabled) {
              if (isEnabled){

                if(promotion_input == "")
                  return;
                console.log(promotion_input+'---'+pid);

                $('.wrapper-loading').toggleClass('cart-loading');
                var send_data = 'action=user_use_promotion&pid='+pid+'&promotion_input='+promotion_input+'&nonce='+nonce;
                $.ajax({
                  type: "POST",
                  url: geodir_var.geodir_ajax_url,
                  data: send_data,
                  success: function(msg){
                        console.log(msg);
                        if(msg.success){
                          $('#pcode').val(promotion_input);
                          var deli_cost = $('#default_delivery').text();
                          var discount = ( deli_cost*(1-(msg.data.percent/100))) - msg.data.constant;
                          var result = 0;
                          console.log(result+" = "+deli_cost+" - "+discount);
                          if(discount <= 0){
                            discount = deli_cost;
                          }else{
                            result = discount;
                            discount = deli_cost - discount;
                          }
                          $('#delivery').html(result);
                          var sum = $('#default_sum').text();
                          $('#sum').html(sum - discount);
                          $('#promotion-msg').html('<font color="green">'+msg.data.name+' ลดค่าส่งไป:'+discount+' บาท</font>');
                        }else{
                          $('#pcode').val("");
                          $('#promotion-msg').html('<font color="red">'+msg.data+'</font>');
                        }
                        $('.wrapper-loading').toggleClass('cart-loading');
                  },
                  error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log(textStatus);
                    $('.wrapper-loading').toggleClass('cart-loading');
                  }
                });
              }
              else{
                OneSignal.showSlidedownPrompt();
                $('#promotion-msg').html('<font color="red">กรูณากดลิงค์ด้านล่าง</font>');
                $('#pcode').val("");
                //window.location.href = "/"; 
              }
                
            });
          });
        });

    });

	// When the user clicks on div, open the popup
function myPopupFunc() {
  var popup = document.getElementById("myPopup");
  popup.classList.toggle("show");
}

function onManageWebPushSubscriptionButtonClicked(event) {
        getSubscriptionState().then(function(state) {
          if (state.isOptedOut) {
              /* Opted out, opt them back in */
              OneSignal.setSubscription(true);
          } else {
              /* Unsubscribed, subscribe them */
              OneSignal.registerForPushNotifications();
          }
          OneSignal.getUserId().then(function(userId) {
            console.log("OneSignal User ID:", userId);   
            jQuery.post( geodir_var.geodir_ajax_url, { action: "updateOnesignal", doing: "INSERT", device_id: userId } );
          });
        });
        event.preventDefault();
    }

    function updateMangeWebPushSubscriptionButton(buttonSelector) {
        var hideWhenSubscribed = false;
        var subscribeText = "กดที่นี่เพื่อยืนยันการใช้โปรโมชั่น";
        var unsubscribeText = "Unsubscribe from Notifications";

        getSubscriptionState().then(function(state) {
            var buttonText = !state.isPushEnabled || state.isOptedOut ? subscribeText : unsubscribeText;

            var element = document.querySelector(buttonSelector);
            if (element === null) {
                return;
            }

            element.removeEventListener('click', onManageWebPushSubscriptionButtonClicked);
            element.addEventListener('click', onManageWebPushSubscriptionButtonClicked);
            element.textContent = buttonText;

            if (state.isPushEnabled) {
                element.style.display = "none";
            } else {
                element.style.display = "";
            }
        });
    }

    function getSubscriptionState() {
        return Promise.all([
          OneSignal.isPushNotificationsEnabled(),
          OneSignal.isOptedOut()
        ]).then(function(result) {
            var isPushEnabled = result[0];
            var isOptedOut = result[1];

            return {
                isPushEnabled: isPushEnabled,
                isOptedOut: isOptedOut
            };
        });
    }

    var OneSignal = OneSignal || [];
    var buttonSelector = "#my-notification-button";

    /* This example assumes you've already initialized OneSignal */
    OneSignal.push(function() {
        // If we're on an unsupported browser, do nothing
        if (!OneSignal.isPushNotificationsSupported()) {
            return;
        }
        updateMangeWebPushSubscriptionButton(buttonSelector);
        OneSignal.on("subscriptionChange", function(isSubscribed) {
            /* If the user's subscription state changes during the page's session, update the button text */
            updateMangeWebPushSubscriptionButton(buttonSelector);
        });
    });
	
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
                      if($delivery_type == 0){
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
                  <input type="hidden" name="delivery" id="delivery_input" value="<?php echo $delivery_fee; ?>"/>
                  <input type="hidden" name="distance" id="distance_input" value="<?php echo $distance; ?>"/>
                  <input type="hidden" name="hidden_up" id="hidden_up" />
                  <input type="hidden" name="dtype" id="dtype" value="<?php echo $delivery_type; ?>" />
                  <input type="hidden" name="pcode" id="pcode" />
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

<?php if($delivery_type == 1 && $shop_has_driver){ ?>
<div class="modal fade" id="select-delivery_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">เลือกพนักงานจัดส่ง</h4>
            </div>
            <div class="modal-body">
            <div class="delivery_type-loading">
                <?php 
                  get_template_part( 'select', 'delivery_type' ); 
                ?>
              </div>
            </div>

        </div>
    </div>
</div>
<?php } ?>

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
                    // second column
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
                      //echo '<strong><div id="'.$post->ID.'-total" class ="price" >'.str_replace(".00", "",number_format($total,2)).'</div></strong>';
                      // 20191108 bank make number have .00
                      echo '<strong><div id="'.$post->ID.'-total" class ="price" >'.number_format($total,2,'.','').'</div></strong>';
                    echo "</td>";
                  echo "</tr>";
                }
                //echo '<div id="cart-total" data-cart-total ="'.str_replace(".00", "",number_format($sum,2)).'" ></div>';
                // 20191108 bank make number have .00
                echo '<div id="cart-total" data-cart-total ="'.number_format($sum,2).'" ></div>';

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
                      if($point >= $delivery_fee && ($delivery_type == 1) && $delivery_fee != 0){
                        echo '<p id="use_point_text" style = "display: block;">ขณะนี้คุณมี point มากพอจะใช้แทนค่าส่งกรุณาเลือก หากต้องการใช้:  <input type="checkbox" name="use_point" id="use_point" /> </p>';
                      }
                    }
                  ?>
                </td>
                <td>
                </td>
                <td>
                </td>
              </tr>
              <tr>
                <td>
                   <?php if($delivery_type == 1 && $shop_has_driver){ ?>
                    <a class="btn btn-info" data-toggle="modal" data-target="#select-delivery_type" style="float: right;">
                    <span style="color: #ffffff !important;">เลือกพนักงานส่ง</span></a>
                  <?php } ?>
                </td>
                <td>
                  <div class="popup" onclick="myPopupFunc()">ค่าจัดส่ง*
					          <span class="popuptext" id="myPopup">ค่าส่งเบื้องต้น 30 บาท รวมกับระยะทาง 3 กม.แรกคิดกม.ล่ะ 10 บาท กม.ถัดไปคิด กม.ล่ะ 15 บาท</span>
                  </div>
				        </td>
                <td class="text-right">
                  <strong><div id="delivery"><?php echo $delivery_fee; ?></div></strong>
                  <div id="default_delivery" style="display:none;"><?php echo $delivery_fee; ?></div>
                </td>
              </tr>
              <tr>
              <td>
                  <div class="order-row">
                    <div class="order-col-6">
                      <input type="text" id="promotion_input" placeholder="กรุณาระบุโค้ดส่วนลด" value="" style="width:150px;float:right;">
                    </div>
                    <div class="order-col-6">
                      <button class="btn btn-info promotion-input" href="#" 
                        data-pid="<?php echo $_REQUEST['pid']; ?>"
                        data-nonce="<?php echo wp_create_nonce( 'user_use_promotion_'.$current_user->ID); ?>" 
                        >ยืนยัน</button>
                      <div id="promotion-msg" >
                        <?php if(!empty($_REQUEST['pmsg'])){ ?>
                          <p><font color="red">ไม่สามารถใช้โปรโมชั่น <?php echo $_REQUEST['pmsg']; ?></font></p>
                        <?php } ?>
                      </div>
                      <a href="#" id="my-notification-button" style="display: none;" >กดที่นี่เพื่อรับสิทธิ์การใช้โปรโมชั่น</a>
                    </div>
                  </div>
                </td>
                <td>
                </td>
                <td>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>
                  <h3 style ="float: right;">รวมทั้งหมด</h3></td>
                  <td class="text-right"><h3><strong><div id="sum"><?php //echo str_replace(".00", "",number_format($sum,2)); 
                  //// 20191108 bank make number have .00
                  echo number_format($sum,2,'.','');
                  ?></div></strong></h3>
                  <div id="default_sum" style="display:none;"><?php echo number_format($sum,2,'.',''); ?></div>
                </td>
              </tr>
            </tbody>
          </table>
            <div style="float:right;" id="place_order_button">
              <?php if($user_has_address){
			  if(($delivery_fee == 0) and ($distance == 0) and ($delivery_type > 0))
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
