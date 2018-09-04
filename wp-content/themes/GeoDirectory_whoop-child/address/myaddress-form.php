<?php /* Template Name: myaddress-form */ ?>

<?php
global $wpdb, $current_user;

$address_id = get_query_var('address_id');
$province = '';
if (isset($address_id) && $address_id != ''){
  $address = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT * FROM user_address WHERE id = %d ",
          array($address_id)
      )
  );

}

?>
<script>
jQuery(document).ready(function($){

  let dropdown = $('#dd_province');

  dropdown.empty();

  dropdown.append('<option selected="true" value="" >กรุณาเลือกจังหวัด</option>');
  dropdown.prop('selectedIndex', 0);

  // Populate dropdown with list of provinces
  $.getJSON(ajaxurl+'?action=get_province', function (data) {
    $.each(data.data, function (key, entry) {
      dropdown.append($('<option></option>').attr('value', entry.province).text(entry.province));
    })
    var province = "<?php echo $address->province; ?>";
    if (province != "")
      dropdown.val(province).change();
  });



  $("#dd_province").change(function () {
      var region = this.value;

      let dropdown = $('#dd_district');

      dropdown.empty();

      dropdown.append('<option selected="true" value="" >กรุณาเลือกเขต/อำเภอ</option>');
      dropdown.prop('selectedIndex', 0);

      $.getJSON(ajaxurl+'?action=get_district&region='+region, function (data) {
        $.each(data.data, function (key, entry) {
          dropdown.append($('<option></option>').attr('value', entry.district).text(entry.district));
        })
        var district = "<?php echo $address->district; ?>";
        if (district != "")
          dropdown.val(district).change();
      });

      dropdown.prop("disabled", false);

  });

  $("#dd_province").change(function () {
      var district = this.value;
      $("#tb_postcode").prop("disabled", false);
  });


  $("form[name='user_address_form']").validate({
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
      },
      address: "required",
      dd_province:{
        required: true
      },
      dd_district:{
        required: true
      },
      tb_postcode:{
        required: true,
        maxlength: 5,
        digits: true
      }
    },
    // Specify validation error messages
    messages: {
      name: "กรุณากรอกชื่อ-สกุล",
      phone: "กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง",
      address: "กรุณากรอกที่อยู่",
      dd_province: "กรุณาเลือกจังหวัด",
      dd_district: "กรุณาเลือกอำเภอ",
      tb_postcode: "กรุณากรอกรหัสไปรษณีย์ให้ถูกต้อง"
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      $( "#address-content" ).toggleClass('order-status-loading');

      var clikedForm = $(form);
      console.log(clikedForm.serialize());
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: clikedForm.serialize(),
        success: function(msg){

              $( "#address-content").load( ajaxurl+"?action=load_address_list", function( response, status, xhr ) {
                if ( status == "error" ) {
                  var msg = "Sorry but there was an error: ";
                  $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
                }
                $( "#address-content" ).toggleClass('order-status-loading');
                console.log("Response: "+status);
              });


              //$('.wrapper-loading').toggleClass('cart-loading');

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
           $( "#address-content" ).toggleClass('order-status-loading');
        }
      });

    }
  });

  $('#back-address-list').click(function(){
    $( "#address-content" ).toggleClass('order-status-loading');
    $( "#address-content" ).load( ajaxurl+"?action=load_address_list", function( response, status, xhr ) {
      if ( status == "error" ) {
        var msg = "Sorry but there was an error: ";
        $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
      }
      //console.log( "load_order_status: " + status );
      $( "#address-content" ).toggleClass('order-status-loading');
    });

  });

});
</script>
<h1>เพิ่มที่อยู่ใหม่</h1>
<section class="entry-content cf" itemprop="articleBody" style="width:67%;">

  <form name="user_address_form">

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>ชื่อ-สกุล<span>*</span> </label>
       <input type="text" id = "name" name="name" value="<?php echo esc_attr(stripslashes($address->name)); ?>">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>หมายเลขโทรศัพท์<span>*</span> </label>
       <input type="text" id = "phone" name="phone" value="<?php echo esc_attr(stripslashes($address->phone)); ?>">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>ที่อยู่<span>*</span> </label>
       <input type="text" id = "address" name="address" value="<?php echo esc_attr(stripslashes($address->address)); ?>">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>จังหวัด<span>*</span> </label>
       <select id="dd_province" name="dd_province" ></select>
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>อำเภอ<span>*</span> </label>
       <select id="dd_district" name="dd_district" disabled>
         <option selected="true">กรุณาเลือกเขต/อำเภอ</option>
       </select>
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>รหัสไปรษณีย์<span>*</span> </label>
       <input type="text" id = "tb_postcode" name="tb_postcode" value="<?php echo esc_attr(stripslashes($address->postcode)); ?>" disabled>
    </div>

    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'add_user_address_' . $current_user->ID ); ?>"  />
    <input type="hidden" name="address_id" value="<?php echo esc_attr(stripslashes($address_id)); ?>"  />
    <input type="hidden" name="action" value="add_user_address"  />
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
