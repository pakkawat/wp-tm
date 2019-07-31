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
  $arrProducts = tamzang_get_all_products_in_cart($current_user->ID);
  if(!empty($arrProducts))
  {
    /* 20190102 Bank put deliver_ticket */
    $default_category_id = geodir_get_post_meta( $_POST['pid'], 'default_category', true );
    $default_category = $default_category_id ? get_term( $default_category_id, 'gd_placecategory' ) : '';
    $parent = get_term($default_category->parent);

    $tz = 'Asia/Bangkok';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
    //echo $dt->format('d.m.Y, H:i:s');

    $current_date = $dt->format("Y-m-d H:i:s");

    // $stock_results = $wpdb->get_results(
    //     $wpdb->prepare(
    //     "SELECT p.id,p.stock - s.qty as result
    //     FROM products as p
    //     INNER JOIN shopping_cart as s on s.product_id = p.id
    //     WHERE s.wp_user_id = %d AND p.post_id = %d AND p.unlimited = false", array($current_user->ID,$_POST['post_id'])
    // ));

    // if (!empty($stock_results)){
    //   foreach ( $stock_results as $result ){
    //     $wpdb->query(
    //         $wpdb->prepare(
    //             "UPDATE products SET stock = %d where id = %d ",
    //             array($result->result, $result->id)
    //         )
    //     );
    //   }
    // }

    $use_point = false;
      //20190213 Bank Add Delivery Fee if it need delivery
    if(($parent->name == "อาหาร")||($default_category->name == "อาหาร"))
    {
      list($delivery_fee,$distance) = get_delivery_fee($_POST['pid']);
      if($delivery_fee != 0)
        $use_point = check_user_point($_POST['hidden_up']);
    }	

    $wpdb->query($wpdb->prepare("INSERT INTO orders SET wp_user_id = %d, post_id = %d, order_date = %s, total_amt = %d, status = %d, payment_type = %d ".
    (($parent->name == "อาหาร")||($default_category->name == "อาหาร") ? ", deliver_ticket = 'Y'" : "").(($use_point) ? ", redeem_point = true" : ""),
      array($current_user->ID, $_POST['pid'], $current_date, 0, 1, $_POST['payment-type'])));
    $order_id = $wpdb->insert_id;

    $shipping_id = 0;
    $billing_id = 0;
	
	
    $shipping_address = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array($current_user->ID)
        )
    );
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

    foreach ($arrProducts as $product) {
      $sum += $product->geodir_price*$product->shopping_cart_qty;
      //geodir_save_post_meta($product->ID, 'geodir_stock', $product->geodir_stock - $product->shopping_cart_qty);// ตัด stock
      $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO order_items SET order_id = %d, product_id = %d, product_name = %s, product_img = %s, qty = %d, price = %f ",
          array($order_id, $product->ID, $product->post_title, $product->featured_image, $product->shopping_cart_qty, $product->geodir_price)
        )
      );

      $wpdb->query(
          $wpdb->prepare(
              "DELETE FROM shopping_cart WHERE product_id = %d AND wp_user_id =%d",
              array($product->ID, $current_user->ID)
          )
      );

    }// end foreach

    $thread_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT thread_id FROM wp_bp_messages_messages ORDER BY thread_id DESC LIMIT 1 ", array()
      )
    );
    $thread_id++;

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET total_amt = %f, shipping_id = %d, billing_id = %d, thread_id = %d where id =%d",
            array($sum, $shipping_id, $billing_id, $thread_id, $order_id)
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
    $message = "มี Order อาหารมาใหม่จาก Tamzang";
		
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
      file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "Return :".$return."\n", true),FILE_APPEND);
    }

    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, "http://119.59.97.78:8010/".$order_id);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    file_put_contents( dirname(__FILE__).'/debug/autoassign.log', var_export( "Start :".$order_id."\n", true));
  }// end if !empty



}// end สร้าง order


$uploads = wp_upload_dir();

get_header(); ?>

<script>
jQuery(document).ready(function($){

  function after_upload(element, data)
  {
    if(data.success)
    {
      ui_single_update_status(element, 'อัพโหลดเรียบร้อย', 'success');
      $('#slip_pic_'+data.data.order_id).attr('src', data.data.image);
      $('#slip_pic_'+data.data.order_id).attr('data-src', data.data.image);
      $('#div_slip_pic_'+data.data.order_id).css("display", "inline");
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
    maxFileSize: 3000000, // 3 Megs max
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

    $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
    var send_data = 'action=user_received_product&id='+order_id+'&nonce='+nonce;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
            //console.log( "Updated status callback: " + JSON.stringify(msg) );
            if(msg.success){
              $( "#status_"+order_id ).load( ajaxurl+"?action=load_order_status&order_status="+4, function( response, status, xhr ) {
                if ( status == "error" ) {
                  var msg = "Sorry but there was an error: ";
                  $( "#status_"+order_id ).html( msg + xhr.status + " " + xhr.statusText );
                }

              });
            }
            $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
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


    $('[id="flip"]').click(function(){
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
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
          $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
        }
      });

    });

});
</script>
<?php
if(wp_is_mobile()){ // if mobile browser
?>
<style>
@media only screen and (orientation: portrait){
.page-id-225149 #container {

    height: 100vw;


    -webkit-transform: rotate(90deg);

    -moz-transform: rotate(90deg);

    -o-transform: rotate(90deg);

    -ms-transform: rotate(90deg);

    transform: rotate(90deg);

  }
}
</style>
<?php
}
else { // desktop browser
?>
<style>
.img2 {
    border: 1px solid #ddd; /* Gray border */
    border-radius: 4px;  /* Rounded border */
    padding: 5px; /* Some padding */
    width: 150px; /* Set a small width */
}

/* Add a hover effect (blue shadow) */
.img2:hover {
    box-shadow: 0 0 2px 1px rgba(0, 140, 186, 0.5);
}
</style>
<?php
}
?>
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
                          <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
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
		      ?>
          <div style="padding:0; margin:0; width:auto; height:auto;">
		  <!-- bank change div panel into 100% from 900px -->
          <div class="panel <?php echo ($order->status == 99 ? 'panel-danger' : 'panel-default'); ?>" id="panel_<?php echo $order->id; ?>" style="width:100%;">
            <div class="panel-heading">
              <div class="order-col-3">
                Order id: #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php echo get_the_title($order->post_id); ?></a>
              </div>
              <div class="order-col-9" style="text-align:right;">
                <?php

                $shipping_address = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM shipping_address where order_id = %d ", array($order->id)
                    )
                );
                $shipping_price = $shipping_address->price;
                if($wpdb->num_rows > 0)
                {
                  echo "ที่อยู่ในการจัดส่ง: ".$shipping_address->address." ".$shipping_address->district." ".$shipping_address->province." ".$shipping_address->postcode;
                }

                ?>
              </div>
              <div class="order-clear"></div>
            </div>
            <div class="panel-body">

              <?php
              $OrderItems  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM order_items where order_id =%d ",
                    array($order->id)
                )
              );
              foreach ($OrderItems as $product) {

              ?>
                <div class="order-row">
                  <div class="order-col-2">
                    <img style="width:72px;height:72px;" src="<?php echo $uploads['baseurl'].$product->product_img; ?>">
                  </div>
                  <div class="order-col-4">
                    <h4 class="product-name"><strong><?php echo $product->product_name; ?></strong></h4><h4><small><?php //echo $product->short_desc; ?></small></h4>
                  </div>
                  <div class="order-col-6">
                    <div class="order-col-6" style="text-align:right;">
                      <strong><?php echo str_replace(".00", "",number_format($product->price,2)); ?> <span class="text-muted">x</span> <?php echo $product->qty; ?></strong>
                    </div>
                    <div class="order-col-2">
                      <strong>รวม</strong>
                    </div>
                    <div class="order-col-4">
                      <strong><?php echo str_replace(".00", "",number_format($product->price*$product->qty,2)); ?> บาท</strong>
                    </div>
                  </div>
                </div>
                <div class="order-clear"></div>
                <hr>

              <?php

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
                            $text = ' ราคาเพิ่มเติมอีก <strong>'.str_replace(".00", "",number_format($order->driver_adjust,2)).' บาท</strong> <h4><strong>รวมทั้งหมด '.str_replace(".00", "",number_format($order->total_amt+$order->driver_adjust+$shipping_price,2)).'</strong> บาท</h4>';
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
                              <strong>พนักงานตามส่ง:</strong><?php echo $driver->driver_name; ?> <strong>เบอร์โทรศัพท์:</strong><?php echo $driver->phone; ?> 
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
                  <div class="order-col-4" style="text-align:left;min-height:1px;">
                    <?php if($order->status == 1 || $order->status == 2){ ?>
                      <button class="btn btn-danger" href="#" data-id="<?php echo $order->id; ?>"
                        data-nonce="<?php echo wp_create_nonce( 'update_order_status_'.$order->id); ?>"
                        data-toggle="modal" data-target="#confirm-delete" >ยกเลิกคำสั่งซื้อ</button>
                    <?php } ?>
                  </div>

                  <div class="order-col-4" style="text-align:center;min-height:1px;">
                      <button class="btn btn-primary" href="#" data-id="<?php echo $order->id; ?>"
                        id="flip"
                      >แสดงรูปภาพ</button>
                    </div>

                  <?php if($order->payment_type == 1) { ?>

                    <div class="order-col-4" style="text-align:right;min-height:1px;">
                      <?php if($order->status == 1){ ?>
                        <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                          data-nonce="<?php echo wp_create_nonce( 'add_transfer_slip_picture_'.$order->id); ?>"
                          data-toggle="modal" data-target="#add-transfer-slip"
                        >อัพโหลดรูปภาพ</button>
                      <?php } ?>
                    </div>
                  <?php } ?>
                </div>
                <div class="order-clear"></div>

                <div class="order-row" id="toggle_pic_<?php echo $order->id; ?>" style="display:none;text-align:center;">
                  <div class="order-col-6">
                    <?php if($order->image_slip != ''){ ?>
                      <h2>หลักฐานการโอนเงิน</h2>
                      <img class="img2" id="slip_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->image_slip; ?>" />
                    <?php }else{ ?>
                      <div id="div_slip_pic_<?php echo $order->id; ?>" style="display:none;">
                        <h2>หลักฐานการโอนเงิน</h2>
                        <img class="img2" id="slip_pic_<?php echo $order->id; ?>" src="" data-toggle="modal" data-target="#image-modal"  data-src="" />
                      </div>
                    <?php } ?>
                  </div>
                  <div class="order-col-6">
                    <?php if($order->tracking_image != ''){ ?>
                      <h2>หลักฐานการจัดส่ง</h2>
                      <img class="img2" data-toggle="modal" data-target="#image-modal" data-src="<?php echo $uploads['baseurl'].$order->tracking_image; ?>"
                      id="tracking_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->tracking_image; ?>" />
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
