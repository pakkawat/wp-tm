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

    $("form[name='driver_form']").validate({
    // Specify validation rules
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
      phone: "กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง"
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      $( "#driver-content" ).toggleClass('order-status-loading');

      var clikedForm = $(form);
      console.log(clikedForm.serialize());
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: clikedForm.serialize(),
        success: function(msg){
              if(msg.success){
                $( "#driver-content").load( ajaxurl+"?action=load_driver_pending", function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Sorry but there was an error: ";
                    $( "#driver-content" ).html( msg + xhr.status + " " + xhr.statusText );
                  }

                  console.log("Response: "+status);
                });
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

});
</script>

<h1>ลงทะเบียน driver</h1>
<section class="entry-content cf" itemprop="articleBody" style="width:67%;">

  <form name="driver_form">

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
    <div class="order-row">
      <div class="order-col-6" style="text-align:left;">
        <button type="submit" class="btn btn-warning">บันทึก</button>
      </div>
      <div class="order-col-6" style="text-align:right;">
        <button type="button" id="back-address-list" class="btn btn-info">ย้อนกลับ</button>
      </div>
    </div>
  </form>
</section>