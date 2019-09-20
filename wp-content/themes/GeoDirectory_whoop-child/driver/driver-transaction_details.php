<?php /* Template Name: driver-transaction_details */ ?>
<?php 
global $wpdb, $current_user;


$uploads = wp_upload_dir();

$profile_pic = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT profile_pic FROM driver where driver_id = %d ", array($current_user->ID)
    )
);
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="<?php echo get_stylesheet_directory_uri() . '/js/uploader/jquery.dm-uploader.min.js'; ?>"></script>
<script>
jQuery(function () {
    jQuery(".transaction_date").datepicker({changeMonth: true, changeYear: true, maxDate:'0' });

    jQuery(".transaction_date").datepicker("option", "dateFormat", 'dd-mm-yy');
});

jQuery(document).ready(function($){

    function after_upload(element, data)
    {
        console.log(data);
        if(data.success)
        {
            ui_single_update_status(element, 'อัพโหลดเรียบร้อย', 'success');
            $('#driver_pic').attr('src', data.data+'?dt=' + Math.random());
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
        url: ajaxurl+'?action=driver_update_picture',
        maxFileSize: 10000000, // 10 Megs max
        multiple: false,
        allowedTypes: 'image/*',
        extFilter: ['jpg','jpeg','png'],
        dataType: 'json',
        extraData: function() {
        return {
        "driver_id": $('#driver_id').val(),
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

    $('#update-profile-pic').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('#nonce', this).val(data.nonce);
        $('#driver_id', this).val(data.id);
        var bar = $('#drag-and-drop-zone').find('div.progress-bar');
        bar.width(0 + '%').attr('aria-valuenow', 0);
        bar.html(0 + '%');

        $('#drag-and-drop-zone', this).find('small.status').html('');
        $('img', this).css("display", "none");
    });


    jQuery(document).on("click", ".show_detail", function(){
            
        $( ".wrapper-loading" ).toggleClass('order-status-loading');

        $( ".list" ).load( ajaxurl+"?action=load_driver_transaction_list", { start_date: $( "#start_date" ).val(), end_date: $( "#end_date" ).val() }, function( response, status, xhr ) {
            if ( status == "error" ) {
                var msg = "Sorry but there was an error: ";
                $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
            }

            $( ".wrapper-loading" ).toggleClass('order-status-loading');
        });

        // console.log( $.datepicker.parseDate('dd/mm/yy', $( "#start_date" ).val()));

    });
});

</script>



<div class="modal fade" id="update-profile-pic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">เปลี่ยนรูปโปรไฟล์</h4>
            </div>
            <div class="modal-body">
                <p>กรุณาอัพโหลดรูปโปรไฟล์</p>

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
                <input type="hidden" id="driver_id" value="" />
                <input type="hidden" id="nonce" value="" />
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<h1 class="page-title">
    รูปประจำตัวผู้ส่ง
</h1>

<img width="200" height="200" id="driver_pic"  src="<?php echo $uploads['baseurl'].$profile_pic; ?>" />

<button class="btn btn-info" href="#" data-id="<?php echo $current_user->ID; ?>"
    data-nonce="<?php echo wp_create_nonce( 'driver_update_picture_'.$current_user->ID); ?>"
    data-toggle="modal" data-target="#update-profile-pic"
>อัพโหลด</button>

<hr>

<h1 class="page-title">
    รายงานบัญชีเงินประกัน
</h1>

<div class="order-row">
    <div class="order-col-6">
        <div class="order-col-3">
            ตั้งแต่:
        </div>
        <div class="order-col-9">
            <input name="start_date" id="start_date" value="" type="text" class="transaction_date"/>
        </div>
    </div>
    <div class="order-col-6">
        <div class="order-col-3">
            ถึง:
        </div>
        <div class="order-col-9">
            <input name="end_date" id="end_date" value="" type="text" class="transaction_date"/>
        </div>
    </div>
</div>
<div class="order-clear"></div>
<div class="order-row" style="text-align:center;">
    <button class="btn btn-success show_detail" >แสดง</button>
</div>
<div class="list"></div>