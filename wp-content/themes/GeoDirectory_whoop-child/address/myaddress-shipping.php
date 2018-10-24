<?php /* Template Name: myaddress-shipping */ ?>

<?php
global $wpdb, $current_user;

$sql = "SELECT * FROM user_address where wp_user_id = ".$current_user->ID." ";
$arrAddress  = $wpdb->get_results( $sql );

?>
<script>
jQuery(document).ready(function($){

  $("form[name='shipping_form']").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      shipping_address:{
        required: true
      }
    },
    // Specify validation error messages
    messages: {
      shipping_address: "กรุณาเลือกที่อยู่ในการจัดส่ง"
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
              //console.log( "user address Added: " + JSON.stringify(msg) );
              if(msg.success){
                $( "#address-content").load( ajaxurl+"?action=load_address_list", function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Sorry but there was an error: ";
                    $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
                  }
                  //console.log( "load_address_list: " + status );

                });
              }
              $( "#address-content" ).toggleClass('order-status-loading');

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           //console.log(textStatus);
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

      $( "#address-content" ).toggleClass('order-status-loading');
    });

  });

});
</script>

<h1>ที่อยู่ในการจัดส่ง ตามที่ได้ระบุไว</h1>

<form name="shipping_form">
  <table id="product_table" class="table table-bordred table-striped">
    <thead>
      <th>ชื่อ-สกุล</th>
      <th>ที่อยู่</th>
      <th>รหัสไปรษณีย์</th>
      <th>เบอร์โทรศัพท์</th>
      <th></th>
      <th></th>
    </thead>
    <tbody>
    <?php

      foreach ($arrAddress as $address) {
        echo '<tr id="tr_row_'.$address->id.'">';
        echo '<td>'.$address->name.'</td>';
        echo '<td>'.$address->address.'</td>';
        echo '<td>'.$address->province.'-'.$address->district.'-'.$address->postcode.'</td>';
        echo '<td>'.$address->phone.'</td>';
        echo '<td>';
        if($address->shipping_address && $address->billing_address)
          echo 'ที่อยู่ในการจัดส่ง - ที่อยู่ในการออกใบเสร็จ ตามที่ได้ระบุไว้';
        else if($address->shipping_address)
          echo 'ที่อยู่ในการจัดส่ง';
        else if($address->billing_address)
          echo 'ที่อยู่ในการออกใบเสร็จ ตามที่ได้ระบุไว้';
        echo '</td>';
        echo '<td><input type="radio" name="shipping_address" value="'.$address->id.'" ';
        if($address->shipping_address)
          echo 'checked';
        echo '></td>';
        echo '</tr>';
      }

    ?>
    </tbody>
  </table>
  <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'update_shipping_address_' . $current_user->ID ); ?>"  />
  <input type="hidden" name="action" value="update_shipping_address"  />
  <div class="order-row">
    <div class="order-col-6" style="text-align:left;">
      <button type="submit" class="btn btn-warning">บันทึก</button>
    </div>
    <div class="order-col-6" style="text-align:right;">
      <button type="button" id="back-address-list" class="btn btn-info">ย้อนกลับ</button>
    </div>
  </div>

</form>
