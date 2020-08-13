
<?php /* Template Name: confirmed order */ 

global $wpdb, $current_user;
$user_has_address = false;

if ( !is_user_logged_in() )
wp_redirect(home_url());

$pid = $_REQUEST['pid'];

if(get_post_type($pid) != "gd_place")
wp_redirect(home_url());

$array_shop_time = check_shop_open($pid);
$is_shop_open = $array_shop_time['is_shop_open'];
$shop_time = $array_shop_time['shop_time'];

if(!$is_shop_open)
wp_redirect(get_page_link($pid));

//$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);
$arrProducts = $wpdb->get_results(
  $wpdb->prepare(
      "SELECT 
      cart.id as cart_id, product.post_id, cart.qty as shopping_cart_qty, cart.product_price, cart.product_title, 
      item_detail.*, cart.admin_price, product.featured_image, cart.special
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
$product_id_choose= $arrProducts[0]->post_id;


if (empty($arrProducts))
  wp_redirect(get_page_link($pid));



  // Get Delivery Type from User
$user_choose_delitype = $wpdb->get_var(
  $wpdb->prepare(
      "SELECT user_delivery_type FROM shopping_cart where wp_user_id = %d and product_id =%d", array($current_user->ID,$product_id_choose)
  )
);



if(!empty($user_choose_delitype)){
  $dtype = $user_choose_delitype;    
}else{
  // Get Delivery Type from restaurant
  $delivery_type = geodir_get_post_meta( $pid, 'geodir_delivery_type', true );
  // Set Dtype for tamzang assign driver
  if($delivery_type == 3){
    $dtype = 1;
  }
  // Set Dtype for tamzang assign Exclusive driver
  elseif($delivery_type == 4){
    $dtype = 2;
  }
  else{
    $dtype = $delivery_type;
  }    
} 

// Get Payment Type from restaurant
$payment_type = $wpdb->get_row(
  $wpdb->prepare(
      "SELECT geodir_Cash,geodir_QR_Code,geodir_transfer FROM wp_geodir_gd_place_detail where post_id = %d ", array($pid)
  )
);

if($dtype != 0)
{
  list($delivery_fee,$distance) = get_delivery_fee($pid,$dtype);
}

$restaurant_delivery_balancing = $wpdb->get_row(
  $wpdb->prepare(
      "SELECT * from delivery_variable where post_id = %d and geodir_delivery_type = %d", array($pid,$dtype)
  )
);
  
  

$user_address = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array($current_user->ID)
    )
);  
if($wpdb->num_rows > 0)
{
  $user_has_address = true;
}


?>
<?php get_header(); ?>


<script>

    jQuery(document).ready(function($){ 
      $( ".header" ).hide();      
      console.log("confirm page Start");
    

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
        var dtype = document.getElementById("dtype").value;
        var deli_cost = $('#default_delivery').text();
        var cart_id = $button.data( 'cart_id' );
        var shop_id = $button.data( 'shop_id' );
        //console.log($("#"+id+"-price").text());
        console.log("Dtype"+dtype);
        var nonce = $button.data( 'nonce' );
        var send_data = 'action=update_product_cart&cart_id='+cart_id+'&nonce='+nonce+'&qty='+newVal+'&deli_type='+dtype+"&default_delivery="+deli_cost+"&shop_id="+shop_id;
        
        $.ajax({
          type: "POST",
          url: geodir_var.geodir_ajax_url,
          data: send_data,
          success: function(msg){
                if(msg.success){                  
                  console.log( "update confirm: " + JSON.stringify(msg) );
                  $button.closest('.sp-quantity').find("p.quntity-input").text(newVal);
                  var total = display_currency(msg.data.total);
                  var deli_balancing = msg.data.delivery_balancing;
                  var show_deli = msg.data.show_delivery_fee;
                  $("#"+cart_id+"-total").text(total);
                  var sum = 0;
                  $(".price").each(function() {
                    var value = $(this).text().replace(/,/g, '');
                    // add only if the value is number
                    if(!isNaN(value) && value.length != 0) {
                        sum += parseFloat(value);
                    }
                  });
                  //sum = display_currency(sum+parseFloat($("#delivery").html()));
                  $("#cart-total").data("cart-total",sum);
                  sum = display_currency(sum+parseFloat(show_deli));
                  $("#sum").text(sum);
                  $("#delivery").text(show_deli);
                  document.getElementById("delivery_balancing").value = deli_balancing;
                  document.getElementById("delivery_balancing_input").value = deli_balancing;
                  
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
        var cart_id = $button.data( 'cart_id' );
        var shop_id = $button.data( 'shop_id' );
        var nonce_delete = $button.data( 'delete' );
        var dtype = document.getElementById("dtype").value;
        var deli_cost = $('#default_delivery').text();
        var send_data = 'action=delete_product_cart&cart_id='+cart_id+'&nonce='+nonce_delete+'&deli_type='+dtype+"&default_delivery="+deli_cost+"&shop_id="+shop_id;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    console.log( "Data deleted: " + JSON.stringify(msg) );
                    $('#' + cart_id).remove();
                    var sum = 0;
                    var deli_balancing = msg.data.delivery_balancing;
                    var show_deli = msg.data.show_delivery_fee;
                    $(".price").each(function() {
                      var value = $(this).text().replace(/,/g, '');
                      // add only if the value is number
                      if(!isNaN(value) && value.length != 0) {
                          sum += parseFloat(value);
                      }
                    });
                    $("#cart-total").data("cart-total",sum);
                    sum = display_currency(sum+parseFloat(show_deli));
                    $("#sum").text(sum);
                    $("#delivery").text(show_deli);
                    document.getElementById("delivery_balancing").value = deli_balancing;
                    document.getElementById("delivery_balancing_input").value = deli_balancing;
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
          var delivery_balancing = document.getElementById("delivery_balancing").value;
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
                    $('#default_delivery').html(msg.data.new_delivery_default);
                    $('#delivery_balancing_input').val(msg.data.delivery_balancing);
                    //$(".address-wrapper-loading").load(window.location.href +  " .address-wrapper-loading");
                    
                         
                    console.log(msg.data);  
                    if(dtype != 0){
                      $('#delivery_input').val(msg.data.new_delivery_default);
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

        $("input[name='dtype']").change(function(){
          var pid = $(this).data('pid');
          var dtype = $(this).val();
          var nonce = $(this).data('nonce');
          var total = $("#cart-total").data('cart-total');
          var delivery_balancing = document.getElementById("delivery_balancing").value;          
          
          $('.delivery_type-loading-loading').toggleClass('cart-loading');
          var send_data = 'action=select_delivery_type&pid='+pid+'&dtype='+dtype+'&nonce='+nonce+'&total='+total;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){  
                    console.log(msg.data.select_type);                                     
                   
                    $('#dtype').val(dtype);

                    // Calculate delivery balancing
                    if(parseFloat(delivery_balancing) > parseFloat(msg.data.new_delivery_fee)){
                      console.log("balance more than new deli");
                      delivery_balancing = msg.data.new_delivery_fee;
                    }
                    var delivery_show = parseFloat(msg.data.new_delivery_fee) - parseFloat(delivery_balancing);
                    $('#delivery').html(delivery_show);
                    var show_sum = parseFloat(total) + parseFloat(delivery_show);                    
                    //$('#sum').html(msg.data.new_sum.toFixed(2));
                    $('#sum').html(show_sum.toFixed(2));
                    $('#delivery_input').val(msg.data.new_delivery_fee);
                    $('#delivery_balancing_input').val(delivery_balancing);
                    $('#default_delivery').html(msg.data.new_delivery_fee);
                    if(msg.data.new_order_button == 0)
                    {             
                      console.log("No  deli ");         
                      $("#place_order_button").html("<h3>ขณะนี้ระบบไม่สามารถคำนวนค่าจัดส่งได้ กรุณาลองใหม่ภายหลัง ขออภัยในความไม่สะดวก</h3>");
                    }
                    else{ 
                      console.log("Have  deli "); 
                      $("#place_order_button").html("<button type='button' class='btn btn-success' id='place_order'>ดำเนินการสั่งสินค้า <span class='glyphicon glyphicon-play'></span></button>");
                    }

                    if(dtype == 99){
                      document.getElementById("pickup_text").style.display = "block";
                      document.getElementById("delivery_text").style.display = "none";
                      
                    }
                    else{
                      document.getElementById("pickup_text").style.display = "none";
                      document.getElementById("delivery_text").style.display = "block";
                      
                    }

                    if(dtype == 1 )
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
          var issub = false;
          var pid = $(this).data('pid');
          var nonce = $(this).data('nonce');
          var promotion_input = $.trim( $('#promotion_input').val() );
          console.log("Check mobile "+isMobileDevice());
          if(isMobileDevice() === true){
            //console.log("return from android "+app.makeToast());
            if (app.makeToast() === true){
              if(promotion_input == "")
                return;
              console.log(promotion_input+'---'+pid);
              promotion_process(pid,promotion_input,nonce);
            }
            else{
              OneSignal.showSlidedownPrompt();
              $('#promotion-msg').html('<font color="red">กรูณากดลิงค์ด้านล่าง</font>');
              //window.location.href = "/";
            } 
          }
          else{
            OneSignal.push(function() {
              console.log("One signal start");
              OneSignal.isPushNotificationsEnabled(function(isEnabled) {
                console.log("One signal isPushNotificationsEnabled");
                if (isEnabled){
                  if(promotion_input == "")
                    return;
                  console.log(promotion_input+'---'+pid);   
                  promotion_process(pid,promotion_input,nonce);
                }
                else{
                  OneSignal.showSlidedownPrompt();
                  $('#promotion-msg').html('<font color="red">กรูณากดลิงค์ด้านล่าง</font>');
                  //window.location.href = "/";
                }          
              }); 
            });// end OneSignal.push
          }
        });

      function promotion_process(pid,promotion_input,nonce){
        $('.wrapper-loading').toggleClass('cart-loading');
        var send_data = 'action=user_use_promotion&pid='+pid+'&promotion_input='+promotion_input+'&nonce='+nonce;
        console.log("Post ID "+pid);
        $.ajax({
          type: "POST",
          url: geodir_var.geodir_ajax_url,
          data: send_data,
          success: function(msg){
                console.log(msg);
                if(msg.success){
                  $('#pcode').val(promotion_input);
                  var deli_cost = $('#default_delivery').text();
                  var discount = Math.round(((deli_cost*(1-(msg.data.percent/100))) - msg.data.constant)*100) /100;
                  //var discount = ((deli_cost*(1-(msg.data.percent/100))) - msg.data.constant);
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
                  $('#sum').html( Math.round((sum - discount)*100) / 100 );
                  $('#promotion-msg').html('<font color="green">'+msg.data.name+' ลดค่าส่งไป:'+Math.round(discount*100)/100+' บาท</font>');
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
    }); // End Jquery ready

	// When the user clicks on div, open the popup
function myPopupFunc() {
  var popup = document.getElementById("myPopup");
  popup.classList.toggle("show");
}

function isMobileDevice() {
    //return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
    var ua = navigator.userAgent;
    if(/Chrome/i.test(ua))
      return false;
    else if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua))
       return true;
    else
      return false;
};

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

    //var OneSignal = OneSignal || [];
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

<?php if($dtype != 0){ ?>
<div class="modal fade" id="select-delivery_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">เลือกวิธีการรับสินค้า</h4>
            </div>
            <div class="modal-body">
            <div class="delivery_type-loading">
                <?php                   
                  set_query_var( 'pid', $pid );
                  set_query_var('product',$arrProducts);
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
  echo '<h1 style="text-align: center;height: 50px;padding-top: 15px;background-color: #dee2e6;';
  if(is_english(get_the_title($pid))){
    echo strlen(get_the_title($pid)) < 30 ? 'font-size: 30px;' : 'font-size: 15px;';
  }
  else{
    echo strlen(get_the_title($pid)) < 90 ? 'font-size: 30px;' : 'font-size: 15px;';

  }
  echo '"><strong style="color: #3b65a7;"> < </strong> <a href="'.get_page_link($pid).'">'.get_the_title($pid).'</a></h1>';
  //echo '<h1 style="text-align: center;background-color: #dee2e6;"><strong style="color: #3b65a7;"> < </strong> <a href="'.get_page_link($pid).'">'.get_the_title($pid).'</a></h1>';
  echo '<strong>ที่อยู่ในการจัดส่ง:</strong><div id"shipping-label">'.$user_address->name.'</div><div id="shipping-address">'.$user_address->address." ".$user_address->district." ".$user_address->province." ".$user_address->postcode."</div> ";
?>
<a class="btn btn-info" data-toggle="modal" data-target="#select-shipping" ><span style="color: #ffffff !important;" >แก้ไขที่อยู่</span></a>
  <?php //geodir_breadcrumb();?>
  <?php 
  if(wp_is_mobile()) // Show display for Mobile
  {
  ?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
      <div class="wrapper-loading">
          <table class="confirm-table" id="tb-cart">
            <thead>
              <tr>
                <th>รายการสินค้า/บริการ</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sum = 0;
              $sum_admin = 0;
              $total = 0;
              $total_admin = 0;
              $temp_cart_id = 0;
              $pre_product_price = 0;
              $pre_product_admin_price = 0;
              $pre_qty = 0;
              $pre_product;


              function open_mobile_tr($product, $total){
                $uploads = wp_upload_dir();
                echo '<tr id="'.$product->cart_id.'">';
                echo "<td>";
                echo '<div class="order-row">';                        
                echo '<div class="order-col-12">';
                echo '<h4 class="product-name"><strong><a href="'.get_permalink($product->post_id).'" style="color: #e34f43;">'.$product->product_title.'</a></strong></h4>';
                
                if(!empty($product->choice_group_title)){
                  echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
                  $total += (float)$product->extra_price;
                }

                return $total;
              }

              function close_mobile_tr($product, $total){
                echo '<div>'.$product->special.'</div>';
                echo '</div>';// close class="order-col-12"
                echo "</div>";// close class="order-row"
                echo '<div class="order-clear"></div>';

                // second column
                echo '<div class="order-row">'; 
                echo '<div class="order-col-6">';
                echo '<div class="sp-quantity">';
                echo '<div class="input-group">';
                echo '<span class="input-group-btn">';
                echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  
                data-delete="'.wp_create_nonce( 'delete_product_cart_' . $product->cart_id ).'" data-shop_id="'.$pid.'"
                data-type="minus" data-cart_id="'.$product->cart_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->cart_id ).'">';
                echo '<span class="glyphicon glyphicon-minus"></span>';
                echo '</button>';
                echo '</span>';
                echo '<div class="sp-input">';
                echo '<p class="quntity-input" style="width:30px;text-align: center;">'.$product->shopping_cart_qty.'</p>';
                echo '</div>';
                echo '<span class="input-group-btn">';
                echo '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-shop_id="'.$pid.'"
                data-type="plus" data-cart_id="'.$product->cart_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->cart_id ).'"
                style="margin-left:0px">';
                echo '<span class="glyphicon glyphicon-plus"></span>';
                echo '</button>';
                echo '</span>';
                echo '</div>';
                echo '</div>';                        
                echo "</div>";
                echo '<div class="order-col-6">';
                echo '<strong><div id="'.$product->cart_id.'-total" class ="price" style= "text-align: right;padding-top: 13%;" >'.number_format($total,2,'.','').'</div></strong>';
                echo "</div>";
                echo "</div>";
                echo '<div class="order-clear"></div>';
                echo "</td>";
                echo "</tr>";
              }

              foreach ($arrProducts as $product) {
                if($temp_cart_id == 0){//start first loop
                  $temp_cart_id = $product->cart_id;
                  $pre_product_price = (float)$product->product_price;
                  $pre_product_admin_price = (float)$product->admin_price;
                  $pre_qty = (int)$product->shopping_cart_qty;
                  $pre_product = $product;

                  $total = open_mobile_tr($product, $total);
                
                }else if($product->cart_id != $temp_cart_id){
                  $sum_admin += ($pre_product_admin_price + $total)* $pre_qty;// $total ตอนนี้คือผลรวมของ extra_price เท่านั้น
                  $total = ($pre_product_price + $total)* $pre_qty;
                  $sum += $total;
                  
                  close_mobile_tr($pre_product, $total);

                  $temp_cart_id = $product->cart_id;
                  $pre_product_price = (float)$product->product_price;
                  $pre_product_admin_price = (float)$product->admin_price;
                  $pre_qty = (int)$product->shopping_cart_qty;
                  $pre_product = $product;
                  $total = 0;
                  $total = open_mobile_tr($product, $total);
                  

                }else{// product options
                  if(!empty($product->choice_group_title)){
                    echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
                    $total += (float)$product->extra_price;
                  }
                }
              }

              if(count($arrProducts) > 0){
                $product = end($arrProducts);
                $sum_admin += ($pre_product_admin_price + $total)* $pre_qty;// $total ตอนนี้คือผลรวมของ extra_price เท่านั้น
                $total = ($pre_product_price + $total)* $pre_qty;
                $sum += $total;
                
                close_mobile_tr($product, $total);
              }

              // 20191108 bank make number have .00
              echo '<div id="cart-total" data-cart-total ="'.number_format($sum,2).'" ></div>';

              // Calculate delivery balanceing
              $tamzang_profit_pre = $sum - $sum_admin;
              if($tamzang_profit_pre < 0){
                $tamzang_profit_pre = 0;
              }
              $delivery_balancing = (($tamzang_profit_pre * $restaurant_delivery_balancing->delivery_balancing_percent)/100) + $restaurant_delivery_balancing->delivery_balancing_constant;
              if($delivery_balancing > $delivery_fee){
                $delivery_balancing = $delivery_fee;
              }
              $show_delivery_fee = $delivery_fee - $delivery_balancing; 
              $sum += $show_delivery_fee;

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
                      if($point >= $delivery_fee && ($dtype == 1) && $delivery_fee != 0){
                        echo '<p id="use_point_text" style = "display: block;">ขณะนี้คุณมี point มากพอจะใช้แทนค่าส่งกรุณาเลือก หากต้องการใช้:  <input type="checkbox" name="use_point" id="use_point" /> </p>';
                      }
                    }
                  ?>
                </td>               
              </tr>
              <tr>
                <td>
                  <div class="order-row">
                    <div class="order-col-6"> 
                      <?php if(($dtype > 0) && ($dtype < 99)){ ?>
                        <h4 id="pickup_text" style = "display: none; float: left; color: #1e62b7;"> *ไปรับเองหน้าร้าน (Pick up)* </h4>
                        <h4 id="delivery_text" style = "display: block; float: left; color: #1e62b7;"> *เดลิเวอร์ลี่* </h4>
                      <?php }elseif($dtype == 99){ ?>                    
                        <h4 id="pickup_text" style = "display: block; float: left; color: #1e62b7;"> *ไปรับเองหน้าร้าน (Pick up)* </h4>
                        <h4 id="delivery_text" style = "display: none; float: left; color: #1e62b7;"> *เดลิเวอร์ลี่* </h4>
                      <?php }elseif($dtype == 0){ ?>                  
                        <h4 id="delivery_text" style = "display: block; float: left; color: #1e62b7;"> *ทางร้านใช้บริการจัดส่งผ่านบริษัทจัดส่งสินค้า (เช่น ไปรษณีไทย / Kerry)* </h4>
                      <?php } ?>                   
                      <?php if($dtype != 0 ){ ?>
                        <a class="btn btn-info" data-toggle="modal" data-target="#select-delivery_type" >
                        <span style="color: #ffffff !important;">เลือกวิธีการรับสินค้า</span></a>
                      <?php } ?>
                      <p> <p>
                    </div>
                    <div class="order-col-6">
                      <div class="popup" onclick="myPopupFunc()" style = "padding-left: 52%;">ค่าจัดส่ง*
                        <span class="popuptext" id="myPopup" style="z-index: 10;">ค่าส่งเบื้องต้น 30 บาท รวมกับระยะทาง 3 กม.แรกคิดกม.ล่ะ 10 บาท กม.ถัดไปคิด กม.ล่ะ 15 บาท</span>
                      </div>
                      <strong><div id="delivery" style ="text-align: right;"><?php echo $show_delivery_fee; ?></div></strong>
                      <div id="default_delivery" style="display:none;"><?php echo $delivery_fee; ?></div>
                      <input type="hidden" id="delivery_balancing" value="<?php echo $delivery_balancing; ?>"/>
                    </div>
                  </div>
                  <div class="order-clear"></div>
                </td>            
              </tr>
              <tr>
              <td>
                <div class="order-row">
                  <div class="order-col-6">
                        <button class="btn btn-info promotion-input" href="#"
                          data-pid="<?php echo $_REQUEST['pid']; ?>"
                          data-nonce="<?php echo wp_create_nonce( 'user_use_promotion_'.$current_user->ID); ?>"
                          >ยืนยัน</button>                     
                  </div>
                  <div class="order-col-6">
                    <input type="text" id="promotion_input" placeholder="กรุณาระบุโค้ดส่วนลด" value="" style="width:150px;float:right;">
                  </div>
                </div>
                <div class="order-clear"></div>
                <div id="promotion-msg" >
                          <?php if(!empty($_REQUEST['pmsg'])){ ?>
                            <p><font color="red">ไม่สามารถใช้โปรโมชั่น <?php echo $_REQUEST['pmsg']; ?></font></p>
                          <?php } ?>
                </div>
                <a href="#" id="my-notification-button" style="display: none;" >กดที่นี่เพื่อรับสิทธิ์การใช้โปรโมชั่น</a>
              </td>
              </tr>
              <tr>
                <td>         
                  <div class="order-row">
                    <div class="order-col-6">
                      <h3>รวมทั้งหมด</h3>
                    </div>
                    <div class="order-col-6">
                      <h3><strong><div id="sum" style ="text-align: right;"><?php //echo str_replace(".00", "",number_format($sum,2));
                     //// 20191108 bank make number have .00
                      echo number_format($sum,2,'.','');
                      ?></div></strong></h3>
                      <div id="default_sum" style="display:none;text-align: right;"><?php echo number_format($sum,2,'.',''); ?></div>
                    </div>
                  </div>
                  <div class="order-clear"></div>
                </td>
              </tr>
            </tbody>
          </table>
            <div style="float:right;" id="place_order_button">
              <?php if($user_has_address){
			  if(($delivery_fee == 0) and ($distance == 0) and (($dtype > 0)&&($dtype < 99)))
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
  <?php  
  }  // End If mobile 
  else{ // Show Display for PC
  ?>
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
                $sum = 0;
                $sum_admin = 0;
                $total = 0;
                $total_admin = 0;
                $temp_cart_id = 0;
                $pre_product_price = 0;
                $pre_product_admin_price = 0;
                $pre_product;
                $pre_qty = 0;
                

                function open_tr($product, $total){
                  $uploads = wp_upload_dir();
                  echo '<tr id="'.$product->cart_id.'">';
                  echo "<td>";
                  echo '<div class="order-row">';
                  echo '<div class="order-col-3" style="height: 1px;">';
                  if($product->featured_image != "")
                  echo '<img style="width:72px;height:72px;" src="'.$uploads['baseurl'].$product->featured_image.'">';
                  echo "</div>";
                  echo '<div class="order-col-9">';
                  echo '<h4 class="product-name"><strong><a href="'.get_permalink($product->post_id).'" style="color: #e34f43;">'.$product->product_title.'</a></strong></h4>';
                  if(!empty($product->choice_group_title)){
                    echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
                    $total += (float)$product->extra_price;
                  }

                  return $total;
                }

                function close_tr($product, $total){
                  // end tr
                  echo '<div>'.$product->special.'</div>';
                  echo '</div>';// close class="order-col-9"
                  echo '<div class="order-clear"></div>';
                  echo "</td>";
                  // second column
                  echo '<td>';
                  echo '<div class="sp-quantity">';
                  echo '<div class="input-group">';
                  echo '<span class="input-group-btn">';
                  echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  
                  data-delete="'.wp_create_nonce( 'delete_product_cart_' . $product->cart_id ).'" data-shop_id="'.$pid.'"
                  data-type="minus" data-cart_id="'.$product->cart_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->cart_id ).'">';
                  echo '<span class="glyphicon glyphicon-minus"></span>';
                  echo '</button>';
                  echo '</span>';
                  echo '<div class="sp-input">';
                  echo '<p class="quntity-input" style="width:30px;">'.$product->shopping_cart_qty.'</p>';
                  echo '</div>';
                  echo '<span class="input-group-btn">';
                  echo '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-shop_id="'.$pid.'"
                  data-type="plus" data-cart_id="'.$product->cart_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->cart_id ).'"
                  style="margin-left:0px">';
                  echo '<span class="glyphicon glyphicon-plus"></span>';
                  echo '</button>';
                  echo '</span>';
                  echo '</div>';
                  echo '</div>';
                  echo "</td>";
                  echo '<td>';
                  // 20191108 bank make number have .00
                  echo '<strong><div id="'.$product->cart_id.'-total" class ="price" >'.number_format($total,2,'.','').'</div></strong>';
                  echo "</td>";
                  echo "</tr>";
                }

                foreach ($arrProducts as $product) {
                  if($temp_cart_id == 0){//start first loop
                    $temp_cart_id = $product->cart_id;
                    $pre_product_price = (float)$product->product_price;
                    $pre_product_admin_price = (float)$product->admin_price;
                    $pre_qty = (int)$product->shopping_cart_qty;
                    $pre_product = $product;

                    $total = open_tr($product, $total);
                  
                  }else if($product->cart_id != $temp_cart_id){
                    $sum_admin += ($pre_product_admin_price + $total)* $pre_qty;// $total ตอนนี้คือผลรวมของ extra_price เท่านั้น
                    $total = ($pre_product_price + $total)* $pre_qty;
                    $sum += $total;
                    
                    close_tr($pre_product, $total);

                    $temp_cart_id = $product->cart_id;
                    $pre_product_price = (float)$product->product_price;
                    $pre_product_admin_price = (float)$product->admin_price;
                    $pre_qty = (int)$product->shopping_cart_qty;
                    $pre_product = $product;
                    $total = 0;
                    $total = open_tr($product, $total);
                    

                  }else{// product options
                    if(!empty($product->choice_group_title)){
                      echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
                      $total += (float)$product->extra_price;
                    }
                  }
                }

                if(count($arrProducts) > 0){
                  $product = end($arrProducts);
                  $sum_admin += ($pre_product_admin_price + $total)* $pre_qty;// $total ตอนนี้คือผลรวมของ extra_price เท่านั้น
                  $total = ($pre_product_price + $total)* $pre_qty;
                  $sum += $total;
                  
                  close_tr($product, $total);
                }


                // 20191108 bank make number have .00
                echo '<div id="cart-total" data-cart-total ="'.number_format($sum,2).'" ></div>';

                // Calculate delivery balanceing
                $tamzang_profit_pre = $sum - $sum_admin;
                if($tamzang_profit_pre < 0){
                  $tamzang_profit_pre = 0;
                }
                $delivery_balancing = (($tamzang_profit_pre * $restaurant_delivery_balancing->delivery_balancing_percent)/100) + $restaurant_delivery_balancing->delivery_balancing_constant;
                if($delivery_balancing > $delivery_fee){
                  $delivery_balancing = $delivery_fee;
                }
                $show_delivery_fee = $delivery_fee - $delivery_balancing; 
                $sum += $show_delivery_fee;

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
                      if($point >= $delivery_fee && ($dtype == 1) && $delivery_fee != 0){
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
                  <?php if(($dtype > 0) && ($dtype < 99)){ ?>
                  <h4 id="pickup_text" style = "display: none; float: left; color: #1e62b7;"> *ไปรับเองหน้าร้าน (Pick up)* </h4>
                  <h4 id="delivery_text" style = "display: block; float: left; color: #1e62b7;"> *เดลิเวอร์ลี่* </h4>
                  <?php }elseif($dtype == 99){ ?>                  
                      <h4 id="pickup_text" style = "display: block; float: left; color: #1e62b7;"> *ไปรับเองหน้าร้าน (Pick up)* </h4>
                      <h4 id="delivery_text" style = "display: none; float: left; color: #1e62b7;"> *เดลิเวอร์ลี่* </h4>
                  <?php }elseif($dtype == 0){ ?>                  
                    <h4 id="delivery_text" style = "display: block; float: left; color: #1e62b7;"> *ทางร้านใช้บริการจัดส่งผ่านบริษัทจัดส่งสินค้า (เช่น ไปรษณีไทย / Kerry)* </h4>
                  <?php } ?>
                  <?php if($dtype != 0 ){ ?>
                    <a class="btn btn-info" data-toggle="modal" data-target="#select-delivery_type" style="float: right;">
                    <span style="color: #ffffff !important;">เลือกวิธีการรับสินค้า</span></a>
                  <?php } ?>
                </td>
                <td>
                  <div class="popup" onclick="myPopupFunc()">ค่าจัดส่ง*
					          <span class="popuptext" id="myPopup">ค่าส่งเบื้องต้น 30 บาท รวมกับระยะทาง 3 กม.แรกคิดกม.ล่ะ 10 บาท กม.ถัดไปคิด กม.ล่ะ 15 บาท</span>
                  </div>
				        </td>
                <td class="text-right">
                  <strong><div id="delivery"><?php echo $show_delivery_fee; ?></div></strong>
                  <div id="default_delivery" style="display:none;"><?php echo $delivery_fee; ?></div>
                  <input type="hidden" id="delivery_balancing" value="<?php echo $delivery_balancing; ?>"/>
                </td>
              </tr>
              <tr>
              <td>
                <div class="order-row">
                  <div class="order-col-12">
                    <input type="text" id="promotion_input" placeholder="กรุณาระบุโค้ดส่วนลด" value="" style="width:150px;float:right;">
                  </div>                    
                </div>
              </td>
              <td>
                <div class="order-row">
                  <div class="order-col-12">
                        <button class="btn btn-info promotion-input" href="#" 
                          data-pid="<?php echo $_REQUEST['pid']; ?>"
                          data-nonce="<?php echo wp_create_nonce( 'user_use_promotion_'.$current_user->ID); ?>" 
                          >ยืนยัน</button>                      
                  </div>
                </div>
                <div id="promotion-msg" >
                          <?php if(!empty($_REQUEST['pmsg'])){ ?>
                            <p><font color="red">ไม่สามารถใช้โปรโมชั่น <?php echo $_REQUEST['pmsg']; ?></font></p>
                          <?php } ?>
                </div>
                <a href="#" id="my-notification-button" style="display: none;" >กดที่นี่เพื่อรับสิทธิ์การใช้โปรโมชั่น</a>
              </td>
              <td>
              </td>
              </tr>
              <tr>
              <td>
              </td>
              <td>
                <h3 style ="float: right;">รวมทั้งหมด</h3>
              </td>
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
			  if(($delivery_fee == 0) and ($distance == 0) and (($dtype > 0)&&($dtype < 99)))
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
  <?php 
  } // End If PC 
  ?>
  
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
                      if(($dtype == 0)&&($payment_type->geodir_transfer)){
                    ?>
                        <div>
                          <input class="form-check-input" type="radio" name="payment-type" value="1" id="pt-radio1">
                          <label class="form-check-label" for="pt-radio1">
                            โอนเงิน
                          </label>
                        </div>                        
                    <?php }
                    if($payment_type->geodir_Cash) 
                    { ?>
                      <div>
                        <input class="form-check-input" type="radio" name="payment-type" value="2" id="pt-radio2">
                        <label class="form-check-label" for="pt-radio2">
                          เงินสด
                        </label>
                      </div>
              <?php } 
                    if($payment_type->geodir_QR_Code) 
                    { ?>
                      <div>
                        <input class="form-check-input" type="radio" name="payment-type" value="3" id="pt-radio3">
                        <label class="form-check-label" for="pt-radio3">
                          ชำระผ่าน QR พร้อมเพย
                        </label>
                      </div>
              <?php } ?>                    
                  </p>
                  <p><label id="payment-error" style="color:red;display:none;">กรุณาเลือกวิธีชำระเงิน</label></p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                  <input type="hidden" name="pid" value="<?php echo $pid; ?>"/>
                  <input type="hidden" name="delivery" id="delivery_input" value="<?php echo $delivery_fee; ?>"/>
                  <input type="hidden" name="distance" id="distance_input" value="<?php echo $distance; ?>"/>
                  <input type="hidden" name="delivery_balancing_input" id="delivery_balancing_input" value="<?php echo $delivery_balancing; ?>"/>
                  <input type="hidden" name="hidden_up" id="hidden_up" />
                  <input type="hidden" name="dtype" id="dtype" value="<?php echo $dtype; ?>" />
                  <input type="hidden" name="pcode" id="pcode" />
                  <button type="submit" class="btn btn-success btn-ok">ตกลง</button>
              </div>
            </form>
        </div>
    </div>
</div>

<?php get_footer(); ?>
