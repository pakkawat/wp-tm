<?php /* Template Name: Shop order */ ?>
<?php

function get_shop_orders($pid, $PERPAGE_LIMIT) {
  global $wpdb, $current_user;

  $sql = "SELECT * FROM orders where post_id = ".$pid." ";
  $sql2 = "SELECT count(id) FROM orders where post_id = ".$pid." ";
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
  //echo '<h1>'.$sql.'</h1>';
  return array($result, $count);
}

function pagination($pid, $count, $PERPAGE_LIMIT) {
  $output = '';
  $href = home_url('/shop-order/') . '?pid='.$pid;
  if(!isset($_REQUEST["pageNumber"])) $_REQUEST["pageNumber"] = 1;
  if($PERPAGE_LIMIT != 0)
    $pages  = ceil($count/$PERPAGE_LIMIT);

  $output .= '<ul class="pagination pagination-lg">';
  //if pages exists after loop's lower limit
  if($pages>1) {
    if(($_REQUEST["pageNumber"]-3)>0) {
      $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber=1">1</a></li>';
    }
    if(($_REQUEST["pageNumber"]-3)>1) {
      $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber='.($_REQUEST["pageNumber"]-1).'"><strong>&lt;</strong></a></li>';
    }

    //Loop for provides links for 2 pages before and after current page
    for($i=($_REQUEST["pageNumber"]-2); $i<=($_REQUEST["pageNumber"]+2); $i++)	{
      if($i<1) continue;
      if($i>$pages) break;
      if($_REQUEST["pageNumber"] == $i)
        $output = $output . '<li class="page-item active"><a href="#">'.$i.'</a></li>';
      else
        $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber='.$i .'">'.$i.'</a></li>';
    }

    //if pages exists after loop's upper limit
    if(($pages-($_REQUEST["pageNumber"]+2))>1) {
      $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber='.($_REQUEST["pageNumber"]+1).'"><strong>&gt;</strong></a></li>';
    }
    if(($pages-($_REQUEST["pageNumber"]+2))>0) {
      if($_REQUEST["pageNumber"] == $pages)
        $output = $output . '<li class="page-item active"><a href="#">' . ($pages) .'</a></li>';
      else
        $output = $output . '<li class="page-item"><a href="' . $href .'&pageNumber='.($pages) .'">' . ($pages) .'</a></li>';
    }

  }
  $output .= '</ul>';
  return $output;
}

global $wpdb, $current_user;
$PERPAGE_LIMIT = 5;

$pid = $_GET['pid'];
$is_current_user_owner = false;
if (isset($pid) && $pid != '') {
  $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
}
if (!is_user_logged_in() || !$is_current_user_owner)
  wp_redirect(home_url());

$uploads = wp_upload_dir();

get_header(); ?>

<script>
jQuery(document).ready(function($){



  function after_upload(element, data)
  {
    if(data.success)
    {
      ui_single_update_status(element, 'อัพโหลดเรียบร้อย', 'success');
      $('#tracking_pic_'+data.data.order_id).attr('src', data.data.image);
      $('#tracking_pic_'+data.data.order_id).attr('data-src', data.data.image);
      $('#div_tracking_pic_'+data.data.order_id).css("display", "inline");
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
    url: ajaxurl+'?action=add_tracking_image',
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

  $('#add-tracking-pic').on('show.bs.modal', function(e) {
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




  jQuery(document).on("change", ".order-status", function(){
    var order_status = $(this).val();
    var order_id = $(this).data('id');
    var nonce = $(this).data('nonce');
    console.log(order_status+"--"+order_id+"--"+nonce);


    $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
    var send_data = 'action=update_order_status&id='+order_id+'&nonce='+nonce+'&status='+order_status;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
            console.log( "Updated status callback: " + JSON.stringify(msg) );
            if(msg.success){
              $( "#status_"+order_id ).load( ajaxurl+"?action=load_order_status&order_status="+order_status, function( response, status, xhr ) {
                if ( status == "error" ) {
                  var msg = "Sorry but there was an error: ";
                  $( "#status_"+order_id ).html( msg + xhr.status + " " + xhr.statusText );
                }
                console.log( "load_order_status: " + status );

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

  $('[id="flip"]').click(function(){
    var id = $(this).data('id');
      $("#toggle_pic_"+id).slideToggle("slow");
  });


  $('#image-modal').on('show.bs.modal', function(e) {
      var data = $(e.relatedTarget).data();
      $('#img-content').attr('src', data.src);
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
          <h1 class="page-title entry-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">



          <div class="modal fade" id="add-tracking-pic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="myModalLabel">ยืนยันการจัดส่งสินค้า</h4>
                      </div>
                      <div class="modal-body">
                          <p>กรุณาอัพโหลดรูปภาพหลักฐานการจัดส่งสินค้า #<b><i class="title"></i></b></p>

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



          <div id="image-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-body">
                      <img id="img-content" src="">
                  </div>
              </div>
            </div>
          </div>



          <?php

          list($arrOrders, $count) = get_shop_orders($pid, $PERPAGE_LIMIT);

          foreach ($arrOrders as $order) {
            set_query_var( 'order_status', $order->status );
		      ?>

          <div style="overflow-x:auto;">
          <div class="panel <?php echo ($order->status == 99 ? 'panel-danger' : 'panel-default'); ?>" id="panel_<?php echo $order->id; ?>" style="width:900px;">
            <div class="panel-heading">
              <div class="order-col-3">
                Order id: #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php echo get_the_title($order->post_id); ?></a>
              </div>
              <div class="order-col-9">

                <?php

                $shipping_address = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM shipping_address where order_id = %d ", array($order->id)
                    )
                );

                if($wpdb->num_rows > 0)
                {
                  echo "ชื่อ:".$shipping_address->name." เบอร์โทรศัพท์: ".$shipping_address->phone." ที่อยู่ในการจัดส่ง: ".$shipping_address->address." ".$shipping_address->district." ".$shipping_address->province." ".$shipping_address->postcode;
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
                        <strong><?php echo $product->price; ?> <span class="text-muted">x</span> <?php echo $product->qty; ?></strong>
                      </div>
                      <div class="order-col-2">
                        <strong>รวม</strong>
                      </div>
                      <div class="order-col-4">
                        <strong><?php echo $product->price*$product->qty; ?> บาท</strong>
                      </div>
                    </div>
                  </div>
                  <div class="order-clear"></div>
                  <hr>

                <?php

              }
              ?>

              <div class="order-row">
                <div class="order-col-9" style="text-align:right;">
                  <h4>ทั้งหมด</h4>
              	</div>
                <div class="order-col-3">
                  <h4><strong><?php echo $order->total_amt; ?></strong> บาท</h4>
              	</div>
              </div>
              <div class="order-clear"></div>
              <hr>


              <div class="order-row">
                <div class="wrapper-loading" id="status_<?php echo $order->id; ?>">
                  <?php get_template_part( 'ajax-order-status' ); ?>
                </div>
              </div>

            </div>

            <?php if($order->status != 99){ ?>
              <div class="panel-footer">
                <div class="order-row">
                  <div class="order-col-4" style="text-align:left;margin-top:10px;margin-bottom:10px;">
                    <select name="status" class="order-status" data-id="<?php echo $order->id; ?>" data-nonce="<?php echo wp_create_nonce( 'update_order_status_'.$order->id); ?>">
                        <option <?php if ($order->status == '1') echo ' selected="selected" '; ?> value="1">รอการจ่ายเงิน</option>
                        <option <?php if ($order->status == '2') echo ' selected="selected" '; ?> value="2">ยืนยันการจ่ายเงิน</option>
                        <option <?php if ($order->status == '3') echo ' selected="selected" '; ?> value="3">ทำการจัดส่งแล้ว</option>
                    </select>
                  </div>
                  <?php if($order->payment_type == 2){ ?>
                    <div class="order-col-6" style="text-align:center;min-height:1px;">
                      <h2>เก็บเงินปลายทาง</h2>
                    </div>
                  <?php } else { ?>
                    <div class="order-col-4" style="text-align:center;min-height:1px;">
                      <button class="btn btn-primary" href="#" data-id="<?php echo $order->id; ?>"
                        id="flip"
                      >แสดงรูปภาพ</button>
                    </div>
                    <div class="order-col-4" style="text-align:right;min-height:1px;">
                      <?php if($order->image_slip != ''){ ?>
                        <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                          data-nonce="<?php echo wp_create_nonce( 'add_tracking_image_'.$order->id); ?>"
                          data-toggle="modal" data-target="#add-tracking-pic"
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
                      <img class="img2" data-toggle="modal" data-target="#image-modal" data-src="<?php echo $uploads['baseurl'].$order->image_slip; ?>"
                      id="slip_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->image_slip; ?>" />
                    <?php }?>
                  </div>
                  <div class="order-col-6">
                    <?php if($order->tracking_image != ''){ ?>
                      <h2>หลักฐานการจัดส่ง</h2>
                      <img class="img2" data-toggle="modal" data-target="#image-modal" data-src="<?php echo $uploads['baseurl'].$order->tracking_image; ?>"
                      id="tracking_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->tracking_image; ?>" />
                    <?php }else{ ?>
                      <div id="div_tracking_pic_<?php echo $order->id; ?>" style="display:none;">
                        <h2>หลักฐานการจัดส่ง</h2>
                        <img class="img2" id="tracking_pic_<?php echo $order->id; ?>"  src="" data-toggle="modal" data-target="#image-modal"  data-src="" />
                      </div>
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
        <footer class="article-footer cf"><?php echo pagination($pid, $count, $PERPAGE_LIMIT); ?></footer>
      </article>

    </div>

  </div>
</div>
<?php get_footer(); ?>
