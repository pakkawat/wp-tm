<?php /* Template Name: driver-edit */ 
global $wpdb, $current_user;

is_tamzang_admin();

if ( !isset( $_GET['d_id'] ) ) {
  return;
}

$driver = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM register_driver where wp_user_id = %d", array($_GET['d_id'])
    )
);

$uploads = wp_upload_dir();

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
  <script src="<?php echo get_stylesheet_directory_uri() . '/js/jquery.validate.min.js'; ?>"></script>

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
.north {
transform:rotate(0deg);
-ms-transform:rotate(0deg); /* IE 9 */
-webkit-transform:rotate(0deg); /* Safari and Chrome */
}
.west {
transform:rotate(90deg);
-ms-transform:rotate(90deg); /* IE 9 */
-webkit-transform:rotate(90deg); /* Safari and Chrome */
}
.south {
transform:rotate(180deg);
-ms-transform:rotate(180deg); /* IE 9 */
-webkit-transform:rotate(180deg); /* Safari and Chrome */
    
}
.east {
transform:rotate(270deg);
-ms-transform:rotate(270deg); /* IE 9 */
-webkit-transform:rotate(270deg); /* Safari and Chrome */
}
</style>



<script>
jQuery(document).ready(function($){

  jQuery.validator.addMethod(
    "extension",
    function (value, element) {
        var fileType = element.files[0].type;
        var isImage = /(jpg|jpeg|png)$/i.test(fileType);
        return isImage;
    },
    'รูปภาพต้องนามสกุล jpg, jpeg, png'
  );

  // jQuery.validator.addMethod(
  //   "filesize",
  //   function (value, element) {
  //       console.log(element.files[0].size);
  //       return (element.files[0].size <= 10000000);
  //   },
  //   'ขนาดรูปต้องน้อยกว่า 10MB'
  // );

    $("form[name='driver_form']").validate({
    // Specify validation rules
    errorElement: 'div',
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      name: "required",
      phone:{
        required: true,
        maxlength: 10,
        digits: true
      }
    },
    // Specify validation error messages
    messages: {
      name: "กรุณากรอกชื่อ-สกุล",
      phone: "กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง",
      image: {
        required: "กรุณาใส่รูปภาพ"
      },
      image_id_card: {
        required: "กรุณาใส่รูปภาพสำเนาบัตรประชาชน"
      },
      image_licence: {
        required: "กรุณาใส่รูปภาพสำเนาใบอนุญาตขับขี่"
      },
      image_car_licence: {
        required: "กรุณาใส่รูปภาพสำเนาทะเบียนรถ"
      },
      term_condition:{
        required: "กรุณายอมรับเงื่อนไขการใช้บริการ"
      }
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form, event) {
      event.preventDefault();
      $( "#edit-content" ).toggleClass('order-status-loading');

      var clikedForm = $(form);
      var formData = new FormData(form);
      // console.log(clikedForm.serialize());
      // console.log($(form)[0]);
      console.log(formData);

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: formData,
        processData: false,
        contentType: false,
        success: function(msg){
              if(msg.success){
                location.reload();
              }else{
                console.log(msg);
              }

              $( "#edit-content" ).toggleClass('order-status-loading');
              //$('.wrapper-loading').toggleClass('cart-loading');

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
           $( "#edit-content" ).toggleClass('order-status-loading');
        }
      });

    }
  });

  $('#image').change(function() {
    var file = $(this).get(0).files[0];
    //var preview = $('#preview');
    var img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    $('#preview').html(img);
    var reader = new FileReader();
    reader.onload = function(e) {
        window.URL.revokeObjectURL(this.src);
    }
    reader.readAsDataURL(file);
    $('#preview img').css({'width':'200px'});
});

$('#image_id_card').change(function() {
    var file = $(this).get(0).files[0];
    //var preview = $('#preview');
    var img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    $('#preview_id_card').html(img);
    var reader = new FileReader();
    reader.onload = function(e) {
        window.URL.revokeObjectURL(this.src);
    }
    reader.readAsDataURL(file);
    $('#preview_id_card img').css({'width':'200px'});
});

$('#image_licence').change(function() {
    var file = $(this).get(0).files[0];
    //var preview = $('#preview');
    var img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    $('#preview_licence').html(img);
    var reader = new FileReader();
    reader.onload = function(e) {
        window.URL.revokeObjectURL(this.src);
    }
    reader.readAsDataURL(file);
    $('#preview_licence img').css({'width':'200px'});
});

$('#image_car_licence').change(function() {
    var file = $(this).get(0).files[0];
    //var preview = $('#preview');
    var img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    $('#preview_car_licence').html(img);
    var reader = new FileReader();
    reader.onload = function(e) {
        window.URL.revokeObjectURL(this.src);
    }
    reader.readAsDataURL(file);
    $('#preview_car_licence img').css({'width':'200px'});
});

$('#image_car_licence2').change(function() {
    var file = $(this).get(0).files[0];
    //var preview = $('#preview');
    var img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    $('#preview_car_licence2').html(img);
    var reader = new FileReader();
    reader.onload = function(e) {
        window.URL.revokeObjectURL(this.src);
    }
    reader.readAsDataURL(file);
    $('#preview_car_licence2 img').css({'width':'200px'});
});

$('#image-modal').on('show.bs.modal', function(e) {
    var data = $(e.relatedTarget).data();
    $('#img-content').attr('src', data.src);
});

$( ".rotate_pic" ).click(function() {
    var img = $(this).next( "img" );
    if(img.hasClass('north')){
        img.attr('class','west');
    }else if(img.hasClass('west')){
        img.attr('class','south');
    }else if(img.hasClass('south')){
        img.attr('class','east');
    }else if(img.hasClass('east')){
        img.attr('class','north');
    }
});


});
</script>
<body id="edit-content">

<div id="image-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-body">
            <input type="button" class="rotate_pic" value="หมุนรูป">
            <img id="img-content" class="north" src="" style="width:100%;">
        </div>
    </div>
</div>
</div>

<h1>แก้ไข driver</h1>
<div class="container">

  <form id="driver_form" name="driver_form" enctype="multipart/form-data">

    <div class="row justify-content-md-center">
        <div class="col-4">
            <label>รูปภาพ<span>*</span> </label>
            <input id="image" name="image" type="file" />
            <div id="preview"></div>
        </div>
        <div class="col-4">
            <img width='200' height='200' src="<?php echo $uploads['baseurl'].$driver->profile_pic; ?>" 
                data-toggle='modal' data-target='#image-modal' 
                data-src='<?php echo $uploads['baseurl'].$driver->profile_pic; ?>'/>
        </div>
    </div>
    <hr>

    <div class="row justify-content-md-center">
        <div class="col-4">
            <label>สำเนาบัตรประชาชน<span>*</span> </label>
            <input id="image_id_card" name="image_id_card" type="file" />
            <div id="preview_id_card"></div>
        </div>
        <div class="col-4">
            <img width='200' height='200' src="<?php echo $uploads['baseurl'].$driver->id_card; ?>" 
                data-toggle='modal' data-target='#image-modal' 
                data-src='<?php echo $uploads['baseurl'].$driver->id_card; ?>'/>
        </div>
    </div>
    <hr>

    <div class="row justify-content-md-center">
        <div class="col-4">
            <label>สำเนาใบอนุญาตขับขี่<span>*</span> </label>
            <input id="image_licence" name="image_licence" type="file" />
            <div id="preview_licence"></div>
        </div>
        <div class="col-4">
            <img width='200' height='200' src="<?php echo $uploads['baseurl'].$driver->licence; ?>" 
                data-toggle='modal' data-target='#image-modal' 
                data-src='<?php echo $uploads['baseurl'].$driver->licence; ?>'/>
        </div>
    </div>
    <hr>

    <div class="row justify-content-md-center">
        <div class="col-4">
            <label>สำเนาทะเบียนรถ<span>*</span> </label>
            <input id="image_car_licence" name="image_car_licence" type="file" />
            <div id="preview_car_licence"></div>
        </div>
        <div class="col-4">
            <img width='200' height='200' src="<?php echo $uploads['baseurl'].$driver->car_licence; ?>" 
                data-toggle='modal' data-target='#image-modal' 
                data-src='<?php echo $uploads['baseurl'].$driver->car_licence; ?>'/>
        </div>
    </div>
    <hr>

    <div class="row justify-content-md-center">
        <div class="col-4">
            <label>หนังสือยินยอมการใช้รถ(กรณีชื่อเจ้าของรถและผู้สมัครไม่ตรงกัน) </label>
            <input id="image_car_licence2" name="image_car_licence2" type="file" />
            <div id="preview_car_licence2"></div>
        </div>
        <div class="col-4">
            <img width='200' height='200' src="<?php echo $uploads['baseurl'].$driver->car_licence2; ?>" 
                data-toggle='modal' data-target='#image-modal' 
                data-src='<?php echo $uploads['baseurl'].$driver->car_licence2; ?>'/>
        </div>
    </div>
    <hr>

    <div class="row justify-content-md-center">
        <div class="col-4"><label>ชื่อ-สกุล<span>*</span> </label></div>
        <div class="col-4"><input type="text" id="name" name="name" value="<?php echo $driver->name; ?>" style="width: 100%;"></div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-4"><label>หมายเลขโทรศัพท์<span>*</span> </label></div>
        <div class="col-4"><input type="text" id="phone" name="phone" value="<?php echo $driver->phone; ?>" style="width: 100%;"></div>
    </div>

    <div class="row justify-content-md-center">
        <div class="col-4"><label>โน้ต</label></div>
        <div class="col-4"><textarea cols="20" id="note" name="note" placeholder="โน้ต" rows="2" style="width: 100%;"><?php echo $driver->note; ?></textarea></div>
    </div>

    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'update_driver_profile_' . $driver->wp_user_id ); ?>"  />
    <input type="hidden" name="action" value="update_driver_profile"  />
    <input type="hidden" name="driver_id" value="<?php echo $driver->wp_user_id; ?>"  />
    <br><br>
    <div class="row justify-content-md-center">
        <button type="submit" class="btn btn-success">บันทึก</button>
    </div>
  </form>
</div>

</body>
</html>