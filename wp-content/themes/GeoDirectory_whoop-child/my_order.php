<?php /* Template Name: My order */ ?>
<?php

function get_my_orders($PERPAGE_LIMIT, $filter) {
  global $wpdb, $current_user;

  $sql = "SELECT * FROM orders where wp_user_id = ".$current_user->ID." ".($filter != '' ? 'AND status='.$filter : '');
  $sql2 = "SELECT count(id) FROM orders where wp_user_id = ".$current_user->ID." ".($filter != '' ? 'AND status='.$filter : '');
  //$sql = "SELECT * FROM wp_terms ";
  //$sql2 = "SELECT count(term_id) FROM wp_terms ";

  // getting parameters required for pagination
  $currentPage = 1;
  if(isset($_GET['pageNumber'])){
    $currentPage = $_GET['pageNumber'];
  }
  $startPage = ($currentPage-1)*$PERPAGE_LIMIT;
  if($startPage < 0) $startPage = 0;


  //adding limits to select query
  $sql .= " ORDER BY id DESC limit " . $startPage . "," . $PERPAGE_LIMIT . " ";
  $result  = $wpdb->get_results( $sql );
  $count   = $wpdb->get_var( $sql2 );
  //file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $count, true));
  //echo '<h1>sql: '.$sql.'</h1>';
  //echo '<h1>sql2: '.$sql2.'</h1>';
  return array($result, $count);
}

function pagination($count, $PERPAGE_LIMIT, $filter) {
  $output = '';
  $href = home_url('/my-order/').'?';
  if(!isset($_REQUEST["pageNumber"])) $_REQUEST["pageNumber"] = 1;
  if($PERPAGE_LIMIT != 0)
    $pages  = ceil($count/$PERPAGE_LIMIT);

  $output .= '<ul class="pagination pagination-lg">';
  //if pages exists after loop's lower limit
  if($pages>1) {
    if(($_REQUEST["pageNumber"]-3)>0) {
      $output = $output . '<li class="page-item"><a href="' . $href . 'pageNumber=1'.($filter != '' ? '&status='.$filter : '').'">1</a></li>';
    }
    if(($_REQUEST["pageNumber"]-3)>1) {
      $output = $output . '<li class="page-item"><a href="' . $href . 'pageNumber='.($_REQUEST["pageNumber"]-1).($filter != '' ? '&status='.$filter : '').'"><strong>&lt;</strong></a></li>';
    }

    //Loop for provides links for 2 pages before and after current page
    for($i=($_REQUEST["pageNumber"]-2); $i<=($_REQUEST["pageNumber"]+2); $i++)	{
      if($i<1) continue;
      if($i>$pages) break;
      if($_REQUEST["pageNumber"] == $i)
        $output = $output . '<li class="page-item active"><a href="#">'.$i.'</a></li>';
      else
        $output = $output . '<li class="page-item"><a href="' . $href . 'pageNumber='.$i .($filter != '' ? '&status='.$filter : '').'">'.$i.'</a></li>';
    }

    //if pages exists after loop's upper limit
    if(($pages-($_REQUEST["pageNumber"]+2))>1) {
      $output = $output . '<li class="page-item"><a href="' . $href . 'pageNumber='.($_REQUEST["pageNumber"]+1).($filter != '' ? '&status='.$filter : '').'"><strong>&gt;</strong></a></li>';
    }
    if(($pages-($_REQUEST["pageNumber"]+2))>0) {
      if($_REQUEST["pageNumber"] == $pages)
        $output = $output . '<li class="page-item active"><a href="#">' . ($pages) .'</a></li>';
      else
        $output = $output . '<li class="page-item"><a href="' . $href .'pageNumber='.($pages) .($filter != '' ? '&status='.$filter : '').'">' . ($pages) .'</a></li>';
    }

  }
  $output .= '</ul>';
  return $output;
}

function check_user_point($use_point){
  global $wpdb, $current_user;

  if(empty($use_point) || $use_point != "true")
    return false;

  $user_cash_back = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM cash_back where user_id = %d ", array($current_user->ID)
    )
  );

  if(empty($user_cash_back))
    return false;

  $point = $user_cash_back->add_on_credit * $user_cash_back->redeem_point_rate;
  if($point >= $delivery_fee){
    return true;
  }else{
    return false;
  }

}

  // <table>
  //   <tbody>
  //     <tr id="1402">
  //       <td>
  //         <div class="order-row">
  //           <div class="order-col-9">
  //             <h4 class="product-name">
  //               <strong><a href="x" style="color: #e34f43;">ทดสอบ เพิ่ม</a></strong>
  //             </h4>
  //             <div>op2 : aaab (2222)</div>
  //             <div>optional : ddd (400)</div>
  //             <div>mandatory : ฟฟฟ (222)</div>
  //             <div class="order-clear"></div>
  //           </div>
  //         </div>
  //       </td>
  //       <td>
  //         <strong>100 <span class="text-muted">x</span> 1</strong>
  //       </td>
  //       <td>
  //         <strong><div id="1402-total" class="price">5708.30</div></strong>
  //       </td>
  //     </tr>
  //   </tbody>
  // </table>

function open_tr($product, $total){
  echo '<tr>';
  echo '<td>';
  echo '<div class="order-row">';
  echo '<div class="order-col-9">';
  echo '<h4 class="product-name">';
  echo '<strong>'.$product->product_name.'</strong></h4>';
  if(!empty($product->choice_group_title)){
    echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
    $total += (float)$product->extra_price;
  }

  return $total;
}

function close_tr($product, $total){
  echo '<div>'.$product->special.'</div>';
  echo '</div>';// class="order-col-9"
  echo '</div>';// class="order-row"
  echo '</td>';
  echo '<td>';
  echo '<strong>'.($product->price+$total).' <span class="text-muted">x</span> '.$product->qty.'</strong>';
  echo '</td>';
  echo '<td>';
  echo '<strong><div class="price">'.number_format(($product->price+$total)*$product->qty,2,'.','').'</div></strong>';
  echo '</td>';
  echo '</tr>';
}

// <tr id="1414">
// <td>
// <div class="order-row">
// <div class="order-col-12">
// <h4 class="product-name"><strong>สินค้า222</strong></h4>
// <div>op2 : aaab (2222)</div>
// <div>mandatory : กกกก (555)</div>
// <div>mandatory : ccc (300)</div>
// </div>
// </div>
// <div class="order-clear"></div>
// <div class="order-row">
// <div class="order-col-6" style="padding-top: 5%;"><strong>100 x 1</strong></div>
// <div class="order-col-6"><strong><div id="1414-total" class="price" style="text-align: right;padding-top: 13%;">5299.00</div></strong></div>
// </div><div class="order-clear"></div>
// </td>
// </tr>

function open_tr_mobile($product, $total){
  echo '<tr>';
  echo '<td>';
  echo '<div class="order-row">';
  echo '<div class="order-col-12">';
  echo '<h4 class="product-name">';
  echo '<strong>'.$product->product_name.'</strong></h4>';
  if(!empty($product->choice_group_title)){
    echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
    $total += (float)$product->extra_price;
  }

  return $total;
}

function close_tr_mobile($product, $total){
  echo '<div>'.$product->special.'</div>';
  echo '</div>';// class="order-col-12"
  echo '</div>';// class="order-row"
  echo '<div class="order-row">';
  echo '<div class="order-col-6" style="padding-top: 5%;">';
  echo '<strong>'.($product->price+$total).' <span class="text-muted">x</span> '.$product->qty.'</strong>';
  echo '</div>';
  echo '<div class="order-col-6">';
  echo '<strong><div class="price" style="text-align: right;padding-top: 13%;">'.number_format(($product->price+$total)*$product->qty,2,'.','').'</div></strong>';
  echo '</div>';
  echo '</div>';
  echo '</td>';
  echo '</tr>';
}

global $wpdb, $current_user;
$PERPAGE_LIMIT = 5;
if(isset($_GET['status'])){
  $filter = $_GET['status'];
  $array_status = array(1, 2, 3, 4, 99);
  if(!in_array($filter, $array_status)){
    $filter = '';
  }
}
else{
  $filter = '';
}

if(($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST['pid'])){// สร้าง order
  //$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);
  $arrProducts = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT
        cart.id as cart_id, product.post_id, cart.qty as shopping_cart_qty, cart.product_price, cart.product_title, 
        cart.admin_price, product.featured_image, cart.price_w_ex, cart.special
        FROM shopping_cart as cart
        LEFT OUTER JOIN wp_geodir_gd_product_detail as product
        ON product.post_id = cart.product_id
        WHERE cart.wp_user_id = %d AND product.geodir_shop_id = %d
        ORDER BY cart_id",
        array($current_user->ID, $_POST['pid'])
    )
  );
  $delivery_type_buyer = $_POST['dtype'];
  $payment_type = $_POST['payment-type'];
  $delivery_balancing = $_POST['delivery_balancing_input'];
 
  file_put_contents( dirname(__FILE__).'/debug/debug_payment.log', var_export( "delivery_balancing_input".$delivery_balancing, true));


  if(!empty($arrProducts))
  {
    /* 20190102 Bank put deliver_ticket */
    $tz = 'Asia/Bangkok';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
    //echo $dt->format('d.m.Y, H:i:s');


    $current_date = $dt->format("Y-m-d H:i:s");

    $geodir_delivery_type = geodir_get_post_meta( $_POST['pid'], 'geodir_delivery_type', true );
    $need_delivery = true;
    if (strpos($geodir_delivery_type, '0') !== false) {
      $need_delivery = false;
      echo "Need Delivery Inside IF is :";
    }
    echo "Need Delivery is :".$need_delivery;
    $use_point = false;
      //20190213 Bank Add Delivery Fee if it need delivery
    if($need_delivery)
    {
      //list($delivery_fee,$distance) = get_delivery_fee($_POST['pid']);
      $delivery_fee = $_POST['delivery'];
      $distance = $_POST['distance'];

      if($delivery_fee != 0)
        $use_point = check_user_point($_POST['hidden_up']);
    }

    $promotion_id = '';
    if(!$use_point){
      if(!empty($_POST['pcode'])){
        $return = check_promotion($_POST['pcode']);
  
        if(!$return['is_valid'])
          wp_redirect(home_url('/confirmed-order/?pid='.$_POST['pid'].'&pmsg='.$return['name']));
  
        $promotion_id = $return['promotion_id'];

        $wpdb->query(
          $wpdb->prepare(
              "UPDATE promotion SET uses = (uses + 1) where ID = %d ",
              array($promotion_id)
          )
        );

        $wpdb->query(
          $wpdb->prepare(
              "UPDATE promotion_log set promotion_id = concat(promotion_id, %s) WHERE device_id = %s ",
              array($promotion_id.',', $return['device_id'])
          )
        );
      }
    }

    
    if (($payment_type === '3') || ($delivery_type_buyer === '99')){
      file_put_contents( dirname(__FILE__).'/debug/debug_payment.log', var_export( "Insert INTO Order QR", true),FILE_APPEND);
      // QR payment
      $wpdb->query($wpdb->prepare("INSERT INTO orders SET wp_user_id = %d, post_id = %d, order_date = %s, total_amt = %d, driver_total_amt = %d, tamzang_profit = %d, delivery_balancing = %d, status = %d, payment_type = %d,user_delivery_type =%d, promotion_id =%d ".
    ($need_delivery ? ", deliver_ticket = 'N'" : "").(($use_point) ? ", redeem_point = true" : ""),
      array($current_user->ID, $_POST['pid'], $current_date, 0,0,0,0, 1, $_POST['payment-type'],$delivery_type_buyer, $promotion_id)));
    }    
    else{
      file_put_contents( dirname(__FILE__).'/debug/debug_payment.log', var_export( "Insert INTO Order ELSE", true),FILE_APPEND);
      $wpdb->query($wpdb->prepare("INSERT INTO orders SET wp_user_id = %d, post_id = %d, order_date = %s, total_amt = %d,driver_total_amt = %d,tamzang_profit = %d,delivery_balancing = %d, status = %d, payment_type = %d,user_delivery_type =%d, promotion_id =%d ".
    ($need_delivery ? ", deliver_ticket = 'Y'" : "").(($use_point) ? ", redeem_point = true" : ""),
      array($current_user->ID, $_POST['pid'], $current_date, 0,0,0,0, 1, $_POST['payment-type'],$delivery_type_buyer, $promotion_id)));
    }
    $order_id = $wpdb->insert_id;

    $shipping_id = 0;
    $billing_id = 0;
	
	
    $shipping_address = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array($current_user->ID)
        )
    );
    file_put_contents( dirname(__FILE__).'/debug/debug_payment.log', var_export( "Insert INTO shipping_address", true),FILE_APPEND);

    $wpdb->query($wpdb->prepare("INSERT INTO shipping_address SET order_id = %d, name = %s, address = %s, district = %s, province = %s, postcode = %s, phone = %s, ship_latitude = %s, ship_longitude = %s, price = %f , distance= %s ",
      array($order_id, $shipping_address->name, $shipping_address->address, $shipping_address->district, $shipping_address->province, $shipping_address->postcode, $shipping_address->phone, $shipping_address->latitude, $shipping_address->longitude, $delivery_fee,$distance)));
    $shipping_id = $wpdb->insert_id;

    $billing_address = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM user_address where wp_user_id = %d AND billing_address = 1 ", array($current_user->ID)
        )
    );
    $wpdb->query($wpdb->prepare("INSERT INTO billing_address SET order_id = %d, name = %s, address = %s, district = %s, province = %s, postcode = %s, phone = %s ",
      array($order_id, $billing_address->name, $billing_address->address, $billing_address->district, $billing_address->province, $billing_address->postcode, $billing_address->phone)));
    $billing_id = $wpdb->insert_id;



    $sum = 0;
    $arr_cart_id = array();
    foreach ($arrProducts as $product) {
      $sum += $product->price_w_ex*$product->shopping_cart_qty;
      $driver_sum += $product->admin_price*$product->shopping_cart_qty;
      //geodir_save_post_meta($product->ID, 'geodir_stock', $product->geodir_stock - $product->shopping_cart_qty);// ตัด stock
      $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO order_items SET order_id = %d, cart_id = %d, product_id = %d, product_name = %s, product_img = %s, qty = %d, price = %f , admin_price =%f, special = %s",
          array($order_id, $product->cart_id, $product->post_id, $product->product_title, $product->featured_image, $product->shopping_cart_qty, $product->product_price, $product->admin_price, $product->special)
        )
      );

      $wpdb->query(
        $wpdb->prepare(
            "insert into order_items_detail (order_id,cart_id,choice_group_title,choice_addon_detail,extra_price)
            select %d,scid.shopping_cart_id,scid.choice_group_title,scid.choice_adon_detail,scid.extra_price
            from shopping_cart_item_destials as scid
            where scid.shopping_cart_id = %d",
            array($order_id, $product->cart_id)
        )
      );

      $arr_cart_id[] = $product->cart_id;


    }// end foreach

// insert into order_items_detail (order_id,order_cart_id,choice_group_title,choice_addon_detail,extra_price)
// select 1,scid.shopping_cart_id,scid.choice_group_title,scid.choice_adon_detail,scid.extra_price
// from shopping_cart_item_destials as scid
// where scid.shopping_cart_id in (1402,1403)
    $arr_cart_id_str = implode(",",$arr_cart_id);

    $wpdb->query(
      $wpdb->prepare(
          "DELETE FROM shopping_cart_item_destials WHERE shopping_cart_id = %d in ($arr_cart_id_str)",
          array()
      )
    );

    $wpdb->query(
      $wpdb->prepare(
          "DELETE FROM shopping_cart WHERE id in ($arr_cart_id_str) AND wp_user_id =%d",
          array($current_user->ID)
      )
    );

    // calculate Shop commission

    $shop_commission = get_tamzang_shop_commission($_POST['pid'],$delivery_type_buyer,$driver_sum);
    $tamzang_profit = $shop_commission+($sum - $driver_sum);

    $thread_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT thread_id FROM wp_bp_messages_messages ORDER BY thread_id DESC LIMIT 1 ", array()
      )
    );
    $thread_id++;

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET total_amt = %f,driver_total_amt =%f,tamzang_profit = %f,delivery_balancing =%f, shipping_id = %d, billing_id = %d, thread_id = %d where id =%d",
            array($sum,$driver_sum, $tamzang_profit ,$delivery_balancing, $shipping_id, $billing_id, $thread_id, $order_id)
        )
    );

    // สร้าง message

    $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
        array($thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$order_id , '<strong><p style="font-size:14px;">มีรายการสั่งซื้อสินค้าเข้ามาใหม่</p> <a href="'.home_url('/shop-order/').'?pid='.$_POST['pid'].'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>', $current_date)
      )
    );
    $shop_owner = get_post_field ('post_author', $_POST['pid']);

    $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO wp_bp_messages_recipients SET user_id = %d, thread_id = %d, unread_count = %d, sender_only = %d, is_deleted = %d ",
        array($shop_owner, $thread_id, 1, 0, 0)
      )
    );

    $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO wp_bp_messages_recipients SET user_id = %d, thread_id = %d, unread_count = %d, sender_only = %d, is_deleted = %d ",
        array($current_user->ID, $thread_id, 0, 1, 0)
      )
    );

    // Send Notification to user who Subscribe with OneSignal
    $message = "มี ออร์เดอร์ เข้ามาใหม่จาก #ตามสั่ง#";
		
    $sql = $wpdb->prepare(
      "SELECT device_id FROM onesignal where user_id=%d ", array($shop_owner)
    );
    $player_id_array = $wpdb->get_results($sql);
    foreach ($player_id_array as $list_player_device)
    {
      $player_id = $list_player_device->device_id;
      $response = sendMessage($player_id,$message);
      $return["allresponses"] = $response;
      $return = json_encode( $return);
      //file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "Return :".$return."\n", true));
    }

    file_put_contents( dirname(__FILE__).'/debug/delivery_type.log', var_export( "PlaceOrder Deli type : ".$_POST['dtype'], true));
    
    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, "http://119.59.97.78:8010/o".$order_id."&deli_type:".$delivery_type_buyer);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    //file_put_contents( dirname(__FILE__).'/debug/autoassign.log', var_export( "Start :".$order_id."\n", true));

    // Call web socket to refresh page
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, "https://tamzang.com:3443/?buyer-confirm&order_id:".$order_id."&shop_id:".$_POST['pid']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);

    if(($delivery_type_buyer === '99')||($delivery_type_buyer === '0')){
      // Call NodeJs Wedsocket to create array detail this order
      $ch = curl_init();
      // set URL and other appropriate options
      curl_setopt($ch, CURLOPT_URL, "https://tamzang.com:3443/?new_order&order_id:".$order_id."&shop_id:".$_POST['pid']."&driver_id:0&buyer_id:".$current_user->ID);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      // grab URL and pass it to the browser
      curl_exec($ch);
      // close cURL resource, and free up system resources
      curl_close($ch);
    }

    

  }// end if !empty



}// end สร้าง order


$uploads = wp_upload_dir();

get_header(); ?>

<style>
.order_item td{
   border:none;
}
</style>
<script>
jQuery(document).ready(function($){
 
  //var socket = io.connect('https://tamzang.com:3443',{secure: true});
  function after_upload(element, data)
  {
    if(data.success)
    {
      ui_single_update_status(element, 'อัพโหลดเรียบร้อย', 'success');
      $('#slip_pic_'+data.data.order_id).attr('src', data.data.image+'?dt=' + Math.random());
      $('#slip_pic_'+data.data.order_id).attr('data-src', data.data.image);
      $('#div_slip_pic_'+data.data.order_id).css("display", "inline");
      buyerMessage(data.data.order_id);
    }else
    {
      ui_single_update_status(element, 'อัพโหลดไม่ถูกต้อง', 'danger');
    }
  }

  function ui_single_update_active(element, active)
  {
    element.find('div.progress').toggleClass('d-none', !active);
    element.find('input[type="text"]').toggleClass('d-none', active);

    element.find('input[type="file"]').prop('disabled', active);
    element.find('.btn').toggleClass('disabled', active);

    element.find('.btn i').toggleClass('fa-circle-o-notch fa-spin', active);
    element.find('.btn i').toggleClass('fa-folder-o', !active);
  }

  function ui_single_update_progress(element, percent, active)
  {
    active = (typeof active === 'undefined' ? true : active);

    var bar = element.find('div.progress-bar');

    bar.width(percent + '%').attr('aria-valuenow', percent);
    bar.toggleClass('progress-bar-striped progress-bar-animated', active);

    if (percent === 0){
      bar.html('');
    } else {
      bar.html(percent + '%');
    }
  }

  function ui_single_update_status(element, message, color)
  {
    color = (typeof color === 'undefined' ? 'muted' : color);

    element.find('small.status').prop('class','status text-' + color).html(message);
  }

  $('#drag-and-drop-zone').dmUploader({ //
    url: ajaxurl+'?action=add_transfer_slip_picture',
    maxFileSize: 100000000, // 100 Megs max
    multiple: false,
    allowedTypes: 'image/*',
    extFilter: ['jpg','jpeg','png'],
    dataType: 'json',
    extraData: function() {
     return {
       "order_id": $('#order_id').val(),
       "nonce": $('#nonce').val()
     };
    },
    onDragEnter: function(){
      // Happens when dragging something over the DnD area
      this.addClass('active');
    },
    onDragLeave: function(){
      // Happens when dragging something OUT of the DnD area
      this.removeClass('active');
    },
    onInit: function(){
      // Plugin is ready to use
      //this.find('input[type="text"]').val('');
    },
    onComplete: function(){
      // All files in the queue are processed (success or error)

    },
    onNewFile: function(id, file){
      // When a new file is added using the file selector or the DnD area


      if (typeof FileReader !== "undefined"){
        var reader = new FileReader();
        var img = this.find('img');

        reader.onload = function (e) {
          img.attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
        img.css("display", "inline");
      }
    },
    onBeforeUpload: function(id){
      $('#div_slip_pic_'+$('#order_id').val()).css("display", "none");
      // about tho start uploading a file

      ui_single_update_progress(this, 0, true);
      //ui_single_update_active(this, true);

      ui_single_update_status(this, 'Uploading...');
    },
    onUploadProgress: function(id, percent){
      // Updating file progress
      ui_single_update_progress(this, percent);
    },
    onUploadSuccess: function(id, data){
      //var response = JSON.stringify(data);

      // A file was successfully uploaded

      //ui_single_update_active(this, false);

      // You should probably do something with the response data, we just show it
      //this.find('input[type="text"]').val(response);
      after_upload(this, data);

    },
    onUploadError: function(id, xhr, status, message){
      // Happens when an upload error happens
      //ui_single_update_active(this, false);
      ui_single_update_status(this, 'Error: ' + message, 'danger');
    },
    onFallbackMode: function(){
      // When the browser doesn't support this plugin :(

    },
    onFileSizeError: function(file){
      ui_single_update_status(this, 'ขนาดรูปภาพเกิน 3MB', 'danger');

    },
    onFileTypeError: function(file){
      ui_single_update_status(this, 'ไฟล์ที่อัพโหลดต้องเป็นไฟล์รูปภาพเท่านั้น', 'danger');

    },
    onFileExtError: function(file){
      ui_single_update_status(this, 'File extension not allowed', 'danger');

    }
  });

  $('#add-transfer-slip').on('show.bs.modal', function(e) {
      var data = $(e.relatedTarget).data();
      $('.title', this).text(data.id);
      //$('.btn-default', this).data('orderId', data.orderId);
      $('#nonce', this).val(data.nonce);
      $('#order_id', this).val(data.id);
      //console.log($(this).find('.title').text());
      var bar = $('#drag-and-drop-zone').find('div.progress-bar');
      bar.width(0 + '%').attr('aria-valuenow', 0);
      bar.html(0 + '%');

      $('#drag-and-drop-zone', this).find('small.status').html('');
      $('img', this).css("display", "none");
  });

  jQuery(document).on("click", ".received-product", function(){
    var order_id = $(this).data('id');
    var nonce = $(this).data('nonce');
    var deliUser = $(this).data('deliuser');

    console.log("Deli User :"+deliUser);
    if(deliUser == 1)
    var pickup = 99;

    $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
    var send_data = 'action=user_received_product&id='+order_id+'&nonce='+nonce;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
            //console.log( "Updated status callback: " + JSON.stringify(msg) );
            if(msg.success){           
              $( "#status_"+order_id ).load( ajaxurl+"?action=load_order_status&order_status="+4+"&pickup="+pickup, function( response, status, xhr ) {
                if ( status == "error" ) {
                  var msg = "Sorry but there was an error: ";
                  $( "#status_"+order_id ).html( msg + xhr.status + " " + xhr.statusText );
                }                
              });
              // socket to shopper // driver
              buyerMessage(order_id);
              // delete order out of socket
              completeOrder(order_id);
            }
            $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
            location.reload();
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         console.log(textStatus);
         $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
      }
    });

  });


  $('#confirm-delete').on('click', '.btn-ok', function(e) {

    var order_id = $(this).data('id');
    var nonce = $(this).data('nonce');
    console.log( "ยกเลิก order: " + order_id );

    var send_data = 'action=update_order_status&id='+order_id+'&nonce='+nonce+'&status='+99;
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
        success: function(msg){
              console.log( "Order cancel: " + JSON.stringify(msg) );
              if(msg.success){
                $( "#panel_"+order_id ).removeClass('panel-default').addClass('panel-danger');
                $( "#panel_"+order_id ).find(".panel-footer").remove();
                $( "#status_"+order_id ).html('<div class="order-row" style="text-align:center;"><h1>ยกเลิก</h1></div>');
              }else{
                $( "#panel_"+order_id ).find(".panel-footer .btn-danger").replaceWith( '<font color="FDA50C"><b>'+msg.data+'</b></font>' );
              }
              //tricker websocket make driver refresh
              //socket.emit( 'buyer-message-confirm', { message: "Test sendwebsocket",order: order_id } );
              buyerCancel(order_id);
              

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);

        }
      });

      $('#confirm-delete').modal('toggle');

    });

    $('#confirm-delete').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.title', this).text(data.id);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('nonce', data.nonce);
        //console.log(data);
    });

    /*
    $('[id="flip"]').click(function(){
    	var id = $(this).data('id');
        $("#toggle_pic_"+id).slideToggle("slow");
    });*/
    //20190923 Bank change picture button 
    jQuery(document).on("click", '[id="flip"]', function(){
      var id = $(this).data('id');
      $("#toggle_pic_"+id).slideToggle("slow");
    });


    $('#image-modal').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('#img-content').attr('src', data.src);
    });


    $('#confirm-adjust').on('click', '.btn-ok', function(e) {

      var order_id = $(this).data('id');
      var nonce = $(this).data('nonce');
      var button_type = $(this).data('type');

      var send_data = 'action=customer_response_adjust&id='+order_id+'&nonce='+nonce+'&status='+button_type;
      $.ajax({
          type: "POST",
          url: ajaxurl,
          data: send_data,
          success: function(msg){
                console.log( "customer_response_adjust: " + JSON.stringify(msg) );
                if(msg.success){
                  if(msg.data == "พนักงานตามส่งยืนยันคำสั่งซื้อแล้ว"){
                    $( "#order_adjust_"+order_id ).html('<font color="FDA50C"><b>'+msg.data+'</b></font>');
                  }else{
                    if(button_type){
                      $( "#order_adjust_"+order_id ).html('<font color="green"><b>ยอมรับ</b></font>');
                      $( "#total_amt_"+order_id ).html(msg.data);                      
                    }else{
                      $( "#panel_"+order_id ).removeClass('panel-default').addClass('panel-danger');
                      $( "#panel_"+order_id ).find(".panel-footer").remove();
                      $( "#status_"+order_id ).html('<div class="order-row" style="text-align:center;"><h1>ยกเลิก</h1></div>');
                      $( "#order_adjust_"+order_id ).empty();
                    }
                  }
                  //tricker websocket make driver refresh
                  //socket.emit( 'buyer-message-confirm', { message: "Test sendwebsocket",order: order_id } );
                  if(button_type == 1)
                  {
                    buyerMessage(order_id);
                    console.log("Buyer accept");
                  }
                  
                  else if (button_type == 0){
                    buyerCancel(order_id);
                    console.log("Buyer Cancel");
                  }
                  

                }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

          }
        });

        $('#confirm-adjust').modal('toggle');

      });

    $('#confirm-adjust').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.adjust-text', this).empty();
        $('.adjust-text', this).append(data.text);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('nonce', data.nonce);
        $('.btn-ok', this).data('type', data.type);
        
        console.log(data);
    });


    jQuery(document).on("click", ".cancel-code", function(){
      var order_id = $(this).data('id');
      var nonce = $(this).data('nonce');
      var code = $('#cancel_code_'+order_id).val();

      var send_data = 'action=customer_confirm_code&id='+order_id+'&nonce='+nonce+'&code='+code;

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
        success: function(msg){
              //console.log( "Updated status callback: " + JSON.stringify(msg) );
              if(msg.success){
                $( "#panel_"+order_id ).find('.panel-body .cancel_code').replaceWith('<font color="green"><b>'+msg.data+'</b></font>');
              }else{
                $( "#panel_"+order_id ).find('.panel-body .cancel_code .cancel_txt').html('<h4>'+msg.data+'</h4>' );
              }
              //tricker websocket make driver refresh
              //socket.emit( 'buyer-message-confirm', { message: "Test sendwebsocket",order: order_id } );
              //console.log("Buyer input code");
              buyerMessage(order_id);

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
          $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
        }
      });

    });

    // Pay QR click

    jQuery(document).on("click", '[id="QrPay"]', function(){
      var id = $(this).data('id');
      var nonce = $(this).data('nonce');
      console.log(nonce);
      var send_data = 'action=buyerQRpayment&nonce='+nonce+'&orderId='+id;
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
        success: function(msg){
          console.log(msg);
          if(msg.success){
            if(msg.data == "Already Pay"){
              location.reload();
            }else{
              window.open('https://test02.tamzang.com/driver_qr/?Ref1='+msg.data.ref1+'&Ref2='+msg.data.ref2+'&Ref3='+msg.data.ref3, '_blank');
            }
            
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);          
        }
      });

    });
    

});
</script>
<script src="http://test02.tamzang.com/JS/node_modules/socket.io-client/dist/socket.io.js"></script>
<script src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/nodeClient.js" defer></script>

<input type="hidden" id="usr_id" value="<?php echo get_current_user_id() ;?>">
<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">

      <article role="article">
        <header class="article-header">
          <div class="order-row">
            <div class="order-col-6">
              <h1 class="page-title entry-title" itemprop="headline">
                <?php the_title(); ?>
              </h1>
            </div>
            <div class="order-col-6">
              <div class="order-col-3" style="text-align:righ;">
                สถานะ:
              </div>
              <div class="order-col-9">
                <form name="status">
                  <select name="menu" onChange="window.document.location.href=this.options[this.selectedIndex].value;" value="GO">
                      <option value="<?php echo home_url('/my-order/');?>" selected="selected">ทั้งหมด</option>
                      <option value="<?php echo home_url('/my-order/').'?status=1';?>" <?php if ($filter == 1) echo ' selected="selected" '; ?>>รอการจ่ายเงิน</option>
                      <option value="<?php echo home_url('/my-order/').'?status=2';?>" <?php if ($filter == 2) echo ' selected="selected" '; ?>>ยืนยันการจ่ายเงิน</option>
                      <option value="<?php echo home_url('/my-order/').'?status=3';?>" <?php if ($filter == 3) echo ' selected="selected" '; ?>>ทำการจัดส่งแล้ว</option>
                      <option value="<?php echo home_url('/my-order/').'?status=4';?>" <?php if ($filter == 4) echo ' selected="selected" '; ?>>ได้รับสินค้าแล้ว</option>
                      <option value="<?php echo home_url('/my-order/').'?status=99';?>" <?php if ($filter == 99) echo ' selected="selected" '; ?>>ยกเลิก</option>
                  </select>
                </form>
              </div>
              <div class="order-clear"></div>
            </div>
          </div>
          <div class="order-clear"></div>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">

          <div class="modal fade" id="add-transfer-slip" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="myModalLabel">ยืนยันการสั่งซื้อสินค้า</h4>
                      </div>
                      <div class="modal-body">
                          <p>กรุณาอัพโหลดรูปภาพหลักฐานการโอนเงินของใบสั่งซื้อสินค้า #<b><i class="title"></i></b></p>

                          <form class="mb-3 dm-uploader" id="drag-and-drop-zone">
                            <div class="form-row">
                              <div class="col-md-10 col-sm-12">
                                <div class="from-group mb-2">
                                  <div class="progress mb-2 d-none">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                      role="progressbar"
                                      style="width: 0%;"
                                      aria-valuenow="0" aria-valuemin="0" aria-valuemax="0">
                                      0%
                                    </div>
                                  </div>

                                </div>
                                <div class="form-group">
                                  <label for="file-upload" class="btn btn-primary">
                                      <i class="fa fa-cloud-upload"></i> กรุณาเลือกไฟล์
                                  </label>
                                  <input id="file-upload" type="file" style="display:none;"/>
                                  <small class="status text-muted">Select a file or drag it over this area..</small>
                                </div>
                              </div>
                              <div class="col-md-2  d-md-block  d-sm-none">
                                <img src="" >
                              </div>
                            </div>
                            <input type="hidden" id="order_id" value="" />
                            <input type="hidden" id="nonce" value="" />
                          </form>

                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                      </div>
                  </div>
              </div>
          </div>

          <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="myModalLabel">ยืนยันยกเลิกคำสั่งซื้อ</h4>
                      </div>
                      <div class="modal-body">
                          <p>คุณกำลังจะยกเลิกคำสั่งซื้อรหัส <b><i class="title"></i></b></p>
                          <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                          <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
                      </div>
                  </div>
              </div>
          </div>

          <div id="image-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-body">
                      <img id="img-content" src="">
                  </div>
              </div>
            </div>
          </div>

          <div class="modal fade" id="confirm-adjust" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="myModalLabel2">ยืนยันการปรับราคา</h4>
                    </div>
                    <div class="modal-body">
                        <p class="adjust-text"></p>
                        <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-success btn-ok">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>

          <?php

          list($arrOrders, $count) = get_my_orders($PERPAGE_LIMIT, $filter);

          foreach ($arrOrders as $order) {
            set_query_var( 'order_status', $order->status );
            set_query_var( 'my_order', true );
            set_query_var( 'id', $order->id );
            set_query_var( 'deliver_ticket', $order->deliver_ticket );
            if($order->user_delivery_type == 99)
            {
              set_query_var( 'pickup', true );
            }              
            else{
              set_query_var( 'pickup', false );
            }              
            set_query_var( 'payment_type', $order->payment_type );

		      ?>
          <div style="padding:0; margin:0; width:auto; height:auto;">
		  <!-- bank change div panel into 100% from 900px -->
          <div class="panel <?php echo ($order->status == 99 ? 'panel-danger' : 'panel-default'); ?>" id="panel_<?php echo $order->id; ?>" style="width:100%;">
            <div class="panel-heading">
              <div class="order-col-12">
                <?php echo tamzang_thai_datetime($order->order_date); ?>
              </div>              
              <div class="order-col-12">
                Order id: #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php echo get_the_title($order->post_id); ?></a>
              </div>
              <div class="order-col-12" style="text-align:left;">
                <div class="order-col-5">
                  ที่อยู่ในการจัดส่ง: 
                </div>
                <div class="order-col-9">                
                  <?php

                  $shipping_address = $wpdb->get_row(
                      $wpdb->prepare(
                          "SELECT * FROM shipping_address where order_id = %d ", array($order->id)
                      )
                  );
                  $shipping_price = $shipping_address->price - $order->delivery_balancing;
                  $old_shipping_price = $shipping_address->price ;
                  if($wpdb->num_rows > 0)
                  {
                    echo "".$shipping_address->address." ".$shipping_address->district." ".$shipping_address->province." ".$shipping_address->postcode;
                  }

                  if(!empty($order->promotion_id)){
                    $shipping_price = cal_shipping_price_with_promotion($order->promotion_id, $shipping_price);
                  }

                  ?>
                </div>
                <div class="<?php echo (wp_is_mobile() ? 'order-col-12' : 'order-col-3') ?>">
                <?php if(($order->payment_type == '3') && ($order->deliver_ticket == 'N') &&($order->status <= 5) ){
                  $qrnonce  = wp_create_nonce('buyerQRpayment_'.$order->id);
                  echo "<button class='btn btn-primary' href='#' data-id='$order->id' data-nonce= '$qrnonce' id='QrPay'>คลิกที่นี่เพื่อจ่ายเงินผ่าน QR</button>";
                }                  
                ?>                            
              </div>
              </div>
              <div class="order-clear"></div>
            </div>
            <div class="panel-body">

              <?php
              $OrderItems  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT oi.*, oid.choice_group_title, oid.choice_addon_detail,oid.extra_price
                    FROM order_items as oi
                    LEFT OUTER JOIN order_items_detail as oid
                    on oi.cart_id = oid.cart_id
                    WHERE oi.order_id = %d ORDER BY oid.id ",
                    array($order->id)
                )
              );
              $is_mobile = wp_is_mobile();
              $sum = 0;// ราคารวมทั้ง order
              $total = 0;// ราคาสินค้า + extra
              $temp_cart_id = 0;
              $pre_product_price = 0;
              $pre_product;
              $pre_qty = 0;
              foreach ($OrderItems as $product) {
                if($temp_cart_id == 0){//start first loop
                  $temp_cart_id = $product->cart_id;
                  $pre_product_price = (float)$product->product_price;
                  $pre_qty = (int)$product->qty;
                  $pre_product = $product;

                  echo '<table class="order_item">';
                  echo '<tbody>';
                  if($is_mobile)
                    $total = open_tr_mobile($product, $total);
                  else
                    $total = open_tr($product, $total);
                }else if($product->cart_id != $temp_cart_id){
                  if($is_mobile)
                    close_tr_mobile($pre_product, $total);// $total ตอนนี้คือผลรวมของ extra_price เท่านั้น
                  else
                    close_tr($pre_product, $total);// $total ตอนนี้คือผลรวมของ extra_price เท่านั้น
                  $total = ($pre_product_price + $total)* $pre_qty;
                  $sum += $total;

                  $temp_cart_id = $product->cart_id;
                  $pre_product_price = (float)$product->product_price;
                  $pre_qty = (int)$product->qty;
                  $pre_product = $product;
                  $total = 0;
                  if($is_mobile)
                    $total = open_tr_mobile($product, $total);
                  else
                    $total = open_tr($product, $total);
                }else{// product options
                  if(!empty($product->choice_group_title)){
                    echo '<div>'.$product->choice_group_title.' : '.$product->choice_adon_detail.' ('.$product->extra_price.')</div>';
                    $total += (float)$product->extra_price;
                  }
                }
              }
              if(count($OrderItems) > 0){
                $product = end($OrderItems);
                if($is_mobile)
                  close_tr_mobile($product, $total);
                else
                  close_tr($product, $total);
                echo '</tbody>';
                echo '</table>';
                $total = ($product->price + $total)* $product->qty;
                $sum += $total;
              }
              ?>

              <?php if(!empty($order->driver_adjust)){ 
                        if($order->adjust_accept){
                          ?>
                              <div class="order-row">
                                <div class="order-col-6" style="float:right;">
                                  
                                  <div class="order-col-6" style="text-align:right;">
                                      <strong>ราคาเพิ่มเติม</strong>
                                  </div>
                                  <div class="order-col-2">
                                      <strong>รวม</strong>
                                  </div>
                                  <div class="order-col-4">
                                      <strong><?php echo str_replace(".00", "",number_format($order->driver_adjust,2)); ?> บาท</strong>
                                  </div>

                                </div>
                              </div>
                              <div class="order-clear"></div>
                              <hr>
                          <?php
                        } else{
                          if(($order->status != 99) && $order->status < 3){ 
                            $adjust_total = $order->total_amt+$order->driver_adjust;
                            if(!$order->redeem_point)
                              $adjust_total += $shipping_price;
                            $text = ' ราคาเพิ่มเติมอีก <strong>'.str_replace(".00", "",number_format($order->driver_adjust,2)).' บาท</strong> <h4><strong>รวมทั้งหมด '.str_replace(".00", "",number_format($adjust_total,2)).'</strong> บาท</h4>';
                            $customer_nonce = wp_create_nonce( 'customer_response_adjust_'.$order->id);
                            ?>
                                <div class="order-row" id="order_adjust_<?php echo $order->id; ?>">
                                  <div class="order-col-4">
                                    <?php echo $text; ?>
                                  </div>
                                  <div class="order-col-4">
                                    <button class="btn btn-success" href="#" 
                                    data-id="<?php echo $order->id; ?>" data-nonce="<?php echo $customer_nonce; ?>" 
                                    data-type="1" data-text='<font color="green"><b>ยอมรับ</b></font><?php echo $text; ?>'
                                    data-toggle="modal" data-target="#confirm-adjust">ยอมรับ</button>
                                    <button class="btn btn-danger" href="#" 
                                    data-id="<?php echo $order->id; ?>" data-nonce="<?php echo $customer_nonce; ?>" 
                                    data-type="0" data-text='<font color="red"><b>ไม่ยอมรับ (ยกเลิก #Order:<?php echo $order->id; ?>)</b></font><?php echo $text; ?>'
                                    data-toggle="modal" data-target="#confirm-adjust">ไม่ยอมรับ</button>
                                  </div>
                                </div>
                                <div class="order-clear"></div>
                                <hr>
                            <?php
                          }
                        } 
              ?>
              <?php } ?>

              <?php if($shipping_price != 0 && !$order->redeem_point){ ?>        
                <div class="order-row">
                  <div class="order-col-9" style="text-align:right;">
                    ราคาค่าจัดส่ง
                  </div>
                  <div class="order-col-3">
                    <strong><?php echo str_replace(".00", "",number_format($shipping_price,2)); ?></strong> บาท
                  </div>
                </div>
                <div class="order-clear"></div>
                <hr>

                <?php if(!empty($order->promotion_id)){ 
                  $promotion = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM promotion where ID = %d ", array($order->promotion_id)
                    )
                  );
                ?>
                  <div class="order-row" style="color:red;">
                    <div class="order-col-6" style="text-align:right;">
                      <?php echo $promotion->name; ?>
                    </div>
                    <div class="order-col-3" style="text-align:right;">
                      <?php echo 'ลดค่าจัดส่ง '.str_replace(".00", "",number_format($old_shipping_price - $shipping_price,2)); ?>
                    </div>
                    <div class="order-col-3">
                      <strong><?php echo 'เหลือค่าจัดส่ง '.str_replace(".00", "",number_format($shipping_price,2)); ?> บาท</strong>
                    </div>
                  </div>
                  <div class="order-clear"></div>
                  <hr>
                <?php } ?>
              <?php } ?>


              <div class="order-row">
                <div class="order-col-9" style="text-align:right;">
                  <h4>ทั้งหมด</h4>
              	</div>
                <div class="order-col-3">
                  <h4><strong id="total_amt_<?php echo $order->id; ?>">
                    <?php //echo ($order->adjust_accept ? $order->total_amt+$order->driver_adjust+$shipping_price : $order->total_amt+$shipping_price);
                      $sum = $order->total_amt;                     

                      if($order->adjust_accept)
                          $sum += $order->driver_adjust;                       
                      if(!$order->redeem_point)
                          $sum += $shipping_price;                          
                      echo str_replace(".00", "",number_format($sum,2));
                    ?>
                  </strong> บาท</h4>
              	</div>
              </div>
              <div class="order-clear"></div>
              <hr>


              <div class="order-row">
                <div class="wrapper-loading" id="status_<?php echo $order->id; ?>">
                  <?php get_template_part( 'ajax-order-status' ); ?>
                </div>
              </div>

              <?php if($order->status < 3 && $order->cancel_code != "" && $order->cancel_code != "ok"){ ?>
                <div class="order-clear"></div>
                <hr>
                <div class="order-row cancel_code">
                  <div class="order-col-4">
                    <input type="text" id="cancel_code_<?php echo $order->id; ?>" value=""  placeholder="กรุณาใส่รหัสเพื่อยืนยัน">
                  </div>
                  <div class="order-col-4">
                    <button class="btn btn-success cancel-code"
                      data-id="<?php echo $order->id; ?>" data-nonce="<?php echo wp_create_nonce( 'customer_confirm_code_'.$order->id); ?>" 
                      >ตกลง</button>
                  </div>

                  <p class="cancel_txt"></p>
                </div>
              <?}?>

            </div>

            <?php if($order->status != 99){ ?>
              <div class="panel-footer">

                  <?php if($order->deliver_ticket == 'Y' && $order->status > 1){ 
                          $driver = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT driver_name,phone,profile_pic
                                FROM driver
                                INNER JOIN driver_order_log_assign
                                ON driver.Driver_id = driver_order_log_assign.driver_id
                                WHERE driver_order_log_assign.driver_order_id = %d AND driver_order_log_assign.status = 2 ", array($order->id)
                            )
                          );

                          if(!empty($driver)){
                        ?>
                          <div class="order-row" style="text-align:center;">
                              <a data-toggle="modal" data-target="#image-modal" data-src="<?php echo $uploads['baseurl'].$driver->profile_pic; ?>"
                              style="cursor: pointer;" >
                                รูปภาพ
                              </a>
                              <strong>พนักงานตามส่ง:</strong><?php echo $driver->driver_name; ?> <i class='fa fa-phone' aria-hidden='true' style = 'color:#007bff'> <a href='tel:<?php echo $driver->phone; ?>'><?php echo $driver->phone; ?></a></i>  
                          </div>
                          <div class="order-clear"></div>
                      <?php }
                      }else if ($order->deliver_ticket != 'Y' && $order->payment_type == 2){?>
                          <div class="order-row" style="text-align:center;">
                            <h2>เก็บเงินปลายทาง</h2>
                          </div>
                          <div class="order-clear"></div>
                      <?php } ?>
                
                <div class="order-row">
                  <?php if ( wp_is_mobile() ){ ?>
                    <div class="order-col-12" style="text-align:center;min-height:1px;">
                      <?php if($order->status == 1 || $order->status == 2){ ?>
                        <button class="btn btn-danger" href="#" data-id="<?php echo $order->id; ?>"
                          data-nonce="<?php echo wp_create_nonce( 'update_order_status_'.$order->id); ?>"
                          data-toggle="modal" data-target="#confirm-delete" >ยกเลิก</button>
                      <?php } ?>
                    </div>
                    <br>
                    <div class="order-col-12" style="text-align:center;min-height:1px;">
                        <button class="btn btn-primary" href="#" data-id="<?php echo $order->id; ?>"
                          id="flip"
                        >รูปภาพ</button>
                      </div>
                    <br>
                    <?php //if($order->payment_type == 1) { ?>

                      <div class="order-col-12" style="text-align:center;min-height:1px;">
                        <?php if($order->deliver_ticket == 'Y' && $order->status < 5){ ?>
                          <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                            data-nonce="<?php echo wp_create_nonce( 'add_transfer_slip_picture_'.$order->id); ?>"
                            data-toggle="modal" data-target="#add-transfer-slip"
                          >อัพโหลด</button>
                        <?php }else if($order->status < 4){ ?>
                          <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                            data-nonce="<?php echo wp_create_nonce( 'add_transfer_slip_picture_'.$order->id); ?>"
                            data-toggle="modal" data-target="#add-transfer-slip"
                          >อัพโหลด</button>
                        <?php } ?>
                      </div>
                    <?php //} ?>
                  <?php }else{ ?>
                    <div class="order-col-4" style="text-align:left;min-height:1px;">
                      <?php if($order->status == 1 || $order->status == 2){ ?>
                        <button class="btn btn-danger" href="#" data-id="<?php echo $order->id; ?>"
                          data-nonce="<?php echo wp_create_nonce( 'update_order_status_'.$order->id); ?>"
                          data-toggle="modal" data-target="#confirm-delete" >ยกเลิก</button>
                      <?php } ?>
                    </div>

                    <div class="order-col-4" style="text-align:center;min-height:1px;">
                        <button class="btn btn-primary" href="#" data-id="<?php echo $order->id; ?>"
                          id="flip"
                        >รูปภาพ</button>
                      </div>

                    <?php //if($order->payment_type == 1) { ?>

                      <div class="order-col-4" style="text-align:right;min-height:1px;">
                        <?php if($order->deliver_ticket == 'Y' && $order->status < 5){ ?>
                          <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                            data-nonce="<?php echo wp_create_nonce( 'add_transfer_slip_picture_'.$order->id); ?>"
                            data-toggle="modal" data-target="#add-transfer-slip"
                          >อัพโหลด</button>
                        <?php }else if($order->status < 4){ ?>
                          <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                            data-nonce="<?php echo wp_create_nonce( 'add_transfer_slip_picture_'.$order->id); ?>"
                            data-toggle="modal" data-target="#add-transfer-slip"
                          >อัพโหลด</button>
                        <?php } ?>
                      </div>
                    <?php //} ?>
                  <?php } ?>
                </div>
                <div class="order-clear"></div>

                <div class="order-row" id="toggle_pic_<?php echo $order->id; ?>" style="display:none;text-align:center;">
                  <div class="<?php echo (wp_is_mobile() ? 'order-col-12' : 'order-col-6') ?>">
                    <?php if($order->image_slip != ''){ ?>
                      <?php echo (wp_is_mobile() ? '<h4>รูปประกอบจากผู้ซื้อ</h4>' : '<h2>รูปประกอบจากผู้ซื้อ</h2>') ?>
                      <img class="img2" id="slip_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->image_slip; ?>" 
                      data-src="<?php echo $uploads['baseurl'].$order->image_slip; ?>"  data-toggle="modal" data-target="#image-modal" />
                    <?php }else{ ?>
                      <div id="div_slip_pic_<?php echo $order->id; ?>" style="display:none;">
                        <?php echo (wp_is_mobile() ? '<h4>รูปประกอบจากผู้ซื้อ</h4>' : '<h2>รูปประกอบจากผู้ซื้อ</h2>') ?>
                        <img class="img2" id="slip_pic_<?php echo $order->id; ?>" src="" data-toggle="modal" data-target="#image-modal"  data-src="" />
                      </div>
                    <?php } ?>
                  </div>
                  <div class="<?php echo (wp_is_mobile() ? 'order-col-12' : 'order-col-6') ?>">
                    <?php if($order->tracking_image != ''){ ?>
                      <?php echo (wp_is_mobile() ? '<h4>รูปประกอบจากผู้ขาย</h4>' : '<h2>รูปประกอบจากผู้ขาย</h2>') ?>
                      <img class="img2" data-toggle="modal" data-target="#image-modal" data-src="<?php echo $uploads['baseurl'].$order->tracking_image; ?>"
                      id="tracking_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->tracking_image; ?>" />
                    <?php } ?>
                  </div>
                  <div class="<?php echo (wp_is_mobile() ? 'order-col-12' : 'order-col-6') ?>">
                    <?php if($order->driver_image != ''){ ?>
                      <?php echo (wp_is_mobile() ? '<h4>รูปประกอบจากพนักงานตามส่ง</h4>' : '<h2>รูปประกอบจากพนักงานตามส่ง</h2>') ?>
                      <img class="img2" data-toggle="modal" data-target="#image-modal" data-src="<?php echo $uploads['baseurl'].$order->driver_image; ?>"
                       src="<?php echo $uploads['baseurl'].$order->driver_image; ?>" />
                    <?php } ?>
                  </div>
                </div>
                <div class="order-clear"></div>
             </div>
          <?php } ?>

          </div>
          </div>



        <?php }//end foreach ($arrOrders as $order) ?>
        </section>
        <?php // end article section ?>
        <footer class="article-footer cf"><?php echo pagination($count, $PERPAGE_LIMIT, $filter); ?></footer>
      </article>

    </div>

  </div>
</div>
<?php get_footer(); ?>
