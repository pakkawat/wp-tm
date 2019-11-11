<?php /* Template Name: driver-order */ 
global $wpdb, $current_user;
if ( !is_user_logged_in() )
    wp_redirect(home_url());

$driver = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT is_ready, pin_enable, tamzangEnable FROM driver where Driver_id = %d ", array($current_user->ID)
    )
);

if(empty($driver))
    wp_redirect(home_url());

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>ตามสั่ง</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php echo '<script type="text/javascript">var ajaxurl = "' .admin_url('admin-ajax.php'). '";</script>';?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="<?php echo get_stylesheet_directory_uri() . '/js/uploader/jquery.dm-uploader.min.js'; ?>"></script>
  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
  <script type="text/javascript" src="https://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/mobile.js"></script>
</head>

<style>
.order-status-loading:before {
display: flex;
flex-direction: column;
justify-content: center;
content: 'รอสักครู่...';
text-align: center;
font-size: 20px;
background: rgba(0, 0, 0, .8);
position: absolute;
top: 0px;
bottom: 0;
left: 0;
right: 0;
color: #EEE;
z-index: 1000;
width: 100%;
height: 100%;
}

/* Bootstrap Toggle v2.2.2 corrections for Bootsrtap 4*/
.toggle-off {
    box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
}
.toggle.off {
    border-color: rgba(0, 0, 0, .25);
}

.toggle-handle {
    background-color: white;
    border: thin rgba(0, 0, 0, .25) solid;
}
</style>

<script src="http://test02.tamzang.com/JS/node_modules/socket.io-client/dist/socket.io.js"></script>
<script src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/nodeClient_driver.js" defer></script>


<script>

function showPosition(position) {
	var user_id = <?php echo get_current_user_id() ;?>;
	console.log( "update address process" );
	var send_data = 'action=update_driver_location&Lat='+position.coords.latitude+'&Lng='+position.coords.longitude+"&user_id="+user_id;
	console.log(send_data);
	
    jQuery.ajax({
        type: 'POST',
		dataType: 'json',
        url: ajaxurl,
        data: send_data,
		success: function() 
	{
        // alert("ยืนยันตำแหน่งเรียบร้อย");
		// location.reload();
    },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
        }
      });
}

jQuery(document).ready(function($){
    $('#main_order').on("click", '.page-item a', function(event) { 
        console.log($(this).data("page"));
        if($(this).data("page") != null)
        {
            $( "#main_order").load( ajaxurl+"?action=load_order_list&page="+$(this).data("page"), function( response, status, xhr ) {
                    if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( "#main_order" ).html( msg + xhr.status + " " + xhr.statusText );
                    }

                    console.log("Response: "+status);
            });
        }

    });

    $('#confirm-order').on('click', '.btn-ok', function(e) {

        var order_id = $(this).data('id');
        var log_id = $(this).data('log_id');
        var nonce = $(this).data('nonce');
        console.log( "ยืนยันรับ order: " + order_id + " log_id: " + log_id );
        

        var send_data = 'action=driver_confirm_order&id='+order_id+'&nonce='+nonce+'&log_id='+log_id;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order confirmed: " + JSON.stringify(msg) );
                if(msg.success){
                    //$( "#confirm_button_"+order_id ).html('<img src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/pass.png" />ยืนยันรับคำสั่งซื้อ');
                    $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                        if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                        }
                    });
                    //tricker to websocket make buyer/seller refresh
                    driveraccept(order_id);
                    //socket.emit( 'driver-message-confirm', { message: "Test sendwebsocket",order: order_id } );
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-order').modal('toggle');

    });

    $('#confirm-order').on('click', '.btn-reject', function(e) {

        var order_id = $(this).data('id');
        var log_id = $(this).data('log_id');
        var nonce = $(this).data('nonce');
        console.log( "ปฏิเสธ order: " + order_id + " log_id: " + log_id );
        

        var send_data = 'action=driver_reject_order&id='+order_id+'&nonce='+nonce+'&log_id='+log_id;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order confirmed: " + JSON.stringify(msg) );
                if(msg.success){
                    $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                        if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                        }
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-order').modal('toggle');

        });


    $('#confirm-order').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.order-text', this).text(data.text);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('log_id', data.log_id);
        $('.btn-ok', this).data('nonce', data.nonce);
        $('.btn-reject', this).data('id', data.id);
        $('.btn-reject', this).data('log_id', data.log_id);
        $('.btn-reject', this).data('nonce', data.nonce); 
    });

    $('#cancel-order').on('click', '.btn-ok', function(e) {

        var order_id = $(this).data('id');
        var log_id = $(this).data('log_id');
        var nonce = $(this).data('nonce');

        var send_data = 'action=driver_cancel_order&id='+order_id+'&nonce='+nonce+'&log_id='+log_id;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                $( "#panel_"+order_id ).find(".card-body .btn-danger").replaceWith( msg.data );
                //tricker websocket buyer to refresh
                driverMessage(order_id);
                //socket.emit( 'driver-message-confirm', { message: "Test sendwebsocket",order: order_id } );
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#cancel-order').modal('toggle');

    });

    $('#cancel-order').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.order-text', this).text(data.text);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('log_id', data.log_id);
        $('.btn-ok', this).data('nonce', data.nonce);
    });

    function isPositiveFloat(s)
    {
        return /^(?![0.]+$)\d+(\.\d{1,2})?$/gm.test(s);
    }

    $('body').on('click', '#confirm-adjust .btn-ok', function(e) {

        var order_id = $(this).data('id');
        var log_id = $(this).data('log_id');
        var nonce = $(this).data('nonce');
        var adjust = $(this).data('adjust');
        console.log( "adjust order: " + order_id );

        var send_data = 'action=driver_adjust_price&id='+order_id+'&nonce='+nonce+'&adjust='+adjust+'&log_id='+log_id;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order adjusted: " + JSON.stringify(msg) );
                if(msg.success){
                    $( "#order_adjust_"+order_id ).html('<font color="#eb9316"><b>รอลูกค้ายอมรับ</b></font>');
                    driverMessage(order_id);
                    //socket.emit( 'driver-message-confirm', { message: "Test sendwebsocket",order: order_id } );
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-adjust').modal('toggle');

        

    });

    function display_currency(money){
        money = (money).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        parts = money.split(".");
        if(parts[1] > 0)
          money = parts[0]+"."+parts[1];
        else
          money = parts[0];
        return money;
    }

    $('body').on('show.bs.modal','#confirm-adjust', function(e) {
        var data = $(e.relatedTarget).data();
        var check = isPositiveFloat($('#adjust_'+data.id).val());
        if(check){
            $('.btn-ok', this).show();
            $('.btn-ok', this).data('id', data.id);
            $('.btn-ok', this).data('log_id', data.log_id);
            $('.btn-ok', this).data('nonce', data.nonce);
            $('.btn-ok', this).data('adjust', $('#adjust_'+data.id).val());
            $('.adjust-text', this).html("คุณต้องการเพิ่มราคาอีก<b><i>"+display_currency(parseFloat($('#adjust_'+data.id).val()))+"</i></b> บาท<br>คุณต้องการดำเนินการต่อหรือไม่?");
        }else{
            $('.btn-ok', this).hide();
            $('.adjust-text', this).text('กรุณาใส่ราคาให้ถูกต้อง');
        }
        
        //console.log(data);
    });

    jQuery(document).on("click", ".assign-order", function(){
        var order_id = $(this).data('id');
        var log_id = $(this).data('log_id');
        var nonce = $(this).data('nonce');
        var driver_id = $('#assign-employee option:selected').val();
        console.log("ส่งต่อ Order:"+order_id + " log_id: " + log_id );
        var send_data = 'action=supervisor_assign_order&id='+order_id+'&nonce='+nonce+'&driver_id='+driver_id+'&log_id='+log_id;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "ส่งต่อ Order เรียบร้อย: " + JSON.stringify(msg) );
                if(msg.success){
                    $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                        if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                        }
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });

    });
	// paring driver button
	$('#chooseHeadDriver').click(function(){
	console.log("ChooseHead Driver Activate!!");
		$( "#driver-content").load( ajaxurl+"?action=chooseHeadDriver&driver_id="+<?php echo get_current_user_id() ;?>, function( response, status, xhr ) 
		{
		console.log("Second AJAX START and Tamzang ID is "+<?php echo get_current_user_id() ;?> );
			if ( status == "error" ) {
				var msg = "Sorry but there was an error: ";
				$( "#driver-content" ).html( msg + xhr.status + " " + xhr.statusText );
			}
		
		});

	});

    jQuery(document).on("click", ".driver-step", function(){
        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        $( ".wrapper-loading" ).toggleClass('order-status-loading');
        console.log("driver step Order:"+order_id);
        var send_data = 'action=driver_next_step&id='+order_id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "driver step Order เรียบร้อย: " + JSON.stringify(msg) );
                if(msg.success){
                    // tricker socket order status to buyer and seller
                    driverMessage(order_id);
                    //socket.emit( 'driver-message-confirm', { message: "Test sendwebsocket",order: order_id } );
                    if(msg.data == "close" || msg.data == "รับสินค้า"){                        
                        $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                            if ( status == "error" ) {
                                var msg = "Sorry but there was an error: ";
                                $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                            }
                        });
                        if(msg.data == "close"){
                            driverComplete(order_id);
                        }
                    }
                    else
                        $('.driver-step').html(msg.data);
                }
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).html( textStatus );
            }
        });

    });

    function after_upload(element, data)
    {
        if(data.success)
        {
        ui_single_update_status(element, 'อัพโหลดเรียบร้อย', 'success');
        $('#tracking_pic_'+data.data.order_id).attr('src', data.data.image+'?dt=' + Math.random());
        $('#tracking_pic_'+data.data.order_id).attr('data-src', data.data.image);
        $('#div_tracking_pic_'+data.data.order_id).css("display", "inline");
        driverMessage(data.data.order_id);
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
        url: ajaxurl+'?action=driver_add_image',
        maxFileSize: 12000000, // 12 Megs max
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

    $('#driver-add-pic').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.title', this).text(data.id);
        $('#nonce', this).val(data.nonce);
        $('#order_id', this).val(data.id);
        var bar = $('#drag-and-drop-zone').find('div.progress-bar');
        bar.width(0 + '%').attr('aria-valuenow', 0);
        bar.html(0 + '%');

        $('#drag-and-drop-zone', this).find('small.status').html('');
        $('img', this).css("display", "none");
    });

    $('#toggle-is_ready').change(function() {
        var id = $(this).data('d-id');
        var nonce = $(this).data('nonce');
        var is_ready = $(this);

        $( ".wrapper-loading" ).toggleClass('order-status-loading');

        var send_data = 'action=driver_ready&id='+id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                if(msg.success){
                    if(is_ready.prop('checked')){
                        $('#status_text').html('<font color="green">สถานะ: ขณะนี้คุณกำลังรอรับคำสั่งซื้อ</font>');
                    }else{
                        $('#status_text').html('<font color="red">สถานะ: ขณะนี้คุณจะไม่ได้รับคำสั่งซื้อเพราะไม่พร้อมทำงาน</font>');
                    }
                }
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).html( textStatus );
            }
        });
    });

    $('#confirm-location').on('click', '.btn-ok', function(e) {

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } 

        $('#confirm-location').modal('toggle');

    });

    $('#toggle-pin_enable').change(function() {
        var id = $(this).data('d-id');
        var nonce = $(this).data('nonce');
        var check = $(this).prop('checked');
        $( ".wrapper-loading" ).toggleClass('order-status-loading');

        var send_data = 'action=driver_pin_enable&id='+id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){

                if(check){
                    $('#confirm-location').modal('toggle');
                }
                

                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).html( textStatus );
            }
        });
    });

    $('#toggle-tamzangEnable').change(function() {
        var id = $(this).data('d-id');
        var nonce = $(this).data('nonce');

        $( ".wrapper-loading" ).toggleClass('order-status-loading');

        var send_data = 'action=driver_tamzangEnable&id='+id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){

                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).html( textStatus );
            }
        });
    });
	
});

function getUserID() {
  var current_user_id = <?php echo get_current_user_id() ;?>;
  return current_user_id;
}

</script>
<body>

<div class="modal fade" id="confirm-order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">ยืนยันรับคำสั่งซื้อ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>คุณกำลังจะยืนยันรับคำสั่งซื้อรหัส <b><i class="order-text"></i></b></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <div class="col-4">
                    <button type="button" class="btn btn-danger btn-reject" data-dismiss="modal" style="float:left;">ปฏิเสธ</button>
                </div>
                <div class="col-8 text-right">
                    <button type="button" class="btn btn-success btn-ok">ยืนยันรับคำสั่งซื้อ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancel-order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">ยกเลิกคำสั่งซื้อ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>คุณกำลังจะยกเลิกคำสั่งซื้อรหัส <b><i class="order-text"></i></b></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <div class="col-6">
                    <button type="button" class="btn btn-success btn-reject" data-dismiss="modal" style="float:left;">ไม่ยกเลิก</button>
                </div>
                <div class="col-6 text-right">
                    <button type="button" class="btn btn-danger btn-ok">ยืนยันยกเลิก</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="driver-add-pic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">รูปภาพประกอบ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>รูปภาพประกอบคำสั่งซื้อรหัส #<b><i class="title"></i></b></p>

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
                    <div class="col-sm-12">
                        <img class="img-fluid" src="" >
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

<div class="modal fade" id="confirm-location" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">ยืนยันตำแหน่ง</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการเปลี่ยนตำแหน่งยืนยันเป็นตำแหน่งปัจจุบันหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <div class="col-4">
                    <button type="button" class="btn btn-danger btn-reject" data-dismiss="modal" style="float:left;">ปฏิเสธ</button>
                </div>
                <div class="col-8 text-right">
                    <button type="button" class="btn btn-success btn-ok">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
<div id="main_order">
    <div class="wrapper-loading">
        <?php get_template_part( 'driver/driver', 'order_template' ); ?>
    </div>

    <br>
    <div>
        <p class="text-center" style="color: green;">หมายเลขไอดีของคุณคือ :  <?php echo $current_user->ID ; ?></p>
    </div>

    <div class="row">
        <div class="col-6">
            <input type="checkbox" <?php echo $driver->tamzangEnable ? 'checked' : '';?> data-toggle="toggle" id="toggle-tamzangEnable"
                data-d-id="<?php echo $current_user->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'driver_tamzangEnable_'.$current_user->ID); ?>"
                data-on="ตามสั่ง" data-off="ประจำร้าน" 
                data-onstyle="success" data-offstyle="primary"
                data-width="137" data-height="42">
        </div>
        <div class="col-6" style="text-align:right;">
            <input type="checkbox" <?php echo $driver->is_ready ? 'checked' : '';?> data-toggle="toggle" id="toggle-is_ready"
                data-d-id="<?php echo $current_user->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'driver_ready_'.$current_user->ID); ?>"
                data-on="พร้อมรับงาน" data-off="ไม่พร้อมรับงาน" 
                data-onstyle="success" data-offstyle="danger"
                data-width="137" data-height="42">
        </div>
    </div>
<br>
    <div class="row">
        <div class="col-6">
            <input type="checkbox" <?php echo $driver->pin_enable ? 'checked' : '';?> data-toggle="toggle" id="toggle-pin_enable"
                    data-d-id="<?php echo $current_user->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'driver_pin_enable_'.$current_user->ID); ?>"
                    data-on="เปิดใช้ Pin" data-off="ปิดใช้ Pin" 
                    data-onstyle="success" data-offstyle="danger" >
        </div>
        <div class="col-6" style="text-align:right;">
        </div>
    </div>

    <div class="row text-center">
        <div class="col-12">
        <a href="<?php echo home_url(); ?>">
            <img src="<?php echo get_stylesheet_directory_uri() . '/images/tamzang.png'; ?>" alt="ตามสั่ง">
        </a>
        </div>
    </div>

</div>
</div>
</body>
</html>