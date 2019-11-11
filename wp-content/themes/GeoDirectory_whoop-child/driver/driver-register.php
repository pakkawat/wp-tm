<?php /* Template Name: driver-register */ ?>
<?php 
global $wpdb, $current_user;
//if ( isset( $_POST['name'] ) && trim( $_POST['name'] ) != '' ) {
  // $recaptcha_error = new WP_Error();
  // geodir_recaptcha_check( 'driver_registration', $recaptcha_error );

//}

?>
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
      },
      image:{
        required:true,
        extension: true
        //filesize: true
      },
      image_id_card:{
        required:true,
        extension: true
        //filesize: true
      },
      image_licence:{
        required:true,
        extension: true
        //filesize: true
      },
      image_car_licence:{
        required:true,
        extension: true
        //filesize: true
      },
      term_condition:{
        required:true
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
      $( "#driver-content" ).toggleClass('order-status-loading');

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
                $( "#driver-content").load( ajaxurl+"?action=load_driver_pending", function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Sorry but there was an error: ";
                    $( "#driver-content" ).html( msg + xhr.status + " " + xhr.statusText );
                  }

                  console.log("Response: "+status);
                });
              }else{
                console.log(msg);
              }

              $( "#driver-content" ).toggleClass('order-status-loading');
              //$('.wrapper-loading').toggleClass('cart-loading');

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
           $( "#driver-content" ).toggleClass('order-status-loading');
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

});
</script>

<h1>ลงทะเบียน driver</h1>
<section class="entry-content cf" itemprop="articleBody" style="width:67%;">

  <form id="driver_form" name="driver_form" enctype="multipart/form-data">

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>รูปภาพ<span>*</span> </label>
       <input id="image" name="image" type="file" />
       <div id="preview"></div>
    </div>
    <hr>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>สำเนาบัตรประชาชน<span>*</span> </label>
       <input id="image_id_card" name="image_id_card" type="file" />
       <div id="preview_id_card"></div>
    </div>
    <hr>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>สำเนาใบอนุญาตขับขี่<span>*</span> </label>
       <input id="image_licence" name="image_licence" type="file" />
       <div id="preview_licence"></div>
    </div>
    <hr>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>สำเนาทะเบียนรถ<span>*</span> </label>
       <input id="image_car_licence" name="image_car_licence" type="file" />
       <div id="preview_car_licence"></div>
    </div>
    <hr>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>หนังสือยินยอมการใช้รถ(กรณีชื่อเจ้าของรถและผู้สมัครไม่ตรงกัน) </label>
       <input id="image_car_licence2" name="image_car_licence2" type="file" />
       <div id="preview_car_licence2"></div>
    </div>
    <hr>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>ชื่อ-สกุล<span>*</span> </label>
       <input type="text" id="name" name="name" value="">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>หมายเลขโทรศัพท์<span>*</span> </label>
       <input type="text" id="phone" name="phone" value="">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>โน้ต</label>
       <textarea cols="20" id="note" name="note" placeholder="โน้ต" rows="2"></textarea>
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <input type="checkbox" name="term_condition" value="1" id="term_condition">
       <a href="https://www.tamzang.com/driver-agreement/" target="_blank">โปรดยอมรับข้อกำหนดในการให้บริการของเรา</a>
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
    <?php 
      // $content = geodir_recaptcha_display( 'driver_registration' );

      // if ( $content ) {
      //   echo $content;
      // }


      // if( is_wp_error( $recaptcha_error ) ) {
      //   echo '<h3>'.$recaptcha_error->get_error_message().'</h3>';
      // }

    ?>
    </div>

    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'register_driver_' . $current_user->ID ); ?>"  />
    <input type="hidden" name="action" value="register_driver"  />
    <br><br>
    <div class="order-row">
      <div class="order-col-6" style="text-align:left;">
        <button type="submit" class="btn btn-success">บันทึก</button>
      </div>
      <div class="order-col-6" style="text-align:right;">
        <button type="button" id="back-address-list" class="btn btn-danger">ย้อนกลับ</button>
      </div>
    </div>
  </form>
</section>