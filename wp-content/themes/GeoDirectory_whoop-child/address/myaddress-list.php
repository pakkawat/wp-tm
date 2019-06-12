<?php /* Template Name: myaddress-list */ ?>
<script src="https://www.openlayers.org/api/OpenLayers.js"></script>
<script src="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/build/ol.js"></script>
<?php
global $wpdb, $current_user;

$sql = "SELECT * FROM user_address where wp_user_id = ".$current_user->ID." ";
$arrAddress  = $wpdb->get_results( $sql );

?>
<script>
jQuery(document).ready(function($){

  $('#address-form').click(function(){
    $( "#address-content" ).toggleClass('order-status-loading');
    $( "#address-content" ).load( ajaxurl+"?action=load_address_form", function( response, status, xhr ) {
      if ( status == "error" ) {
        var msg = "Sorry but there was an error: ";
        $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
      }
      //console.log( "load_order_status: " + status );
      $( "#address-content" ).toggleClass('order-status-loading');
    });

  });

  jQuery(document).on("click", ".btn-edit", function(){
    $( "#address-content" ).toggleClass('order-status-loading');
    $( "#address-content" ).load( ajaxurl+"?action=load_address_form&address_id="+$(this).data('id'), function( response, status, xhr ) {
      if ( status == "error" ) {
        var msg = "Sorry but there was an error: ";
        $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
      }
      //console.log( "load_order_status: " + status );
      $( "#address-content" ).toggleClass('order-status-loading');
    });
    //console.log($(this).data('id'));
  });

  $('#confirm-delete').on('click', '.btn-ok', function(e) {
    var modalDiv = $(e.delegateTarget);
    var id = $(this).data('recordId');
    var nonce = $(this).data('recordNonce');

    $( "#address-content" ).toggleClass('order-status-loading');
    var send_data = 'action=delete_user_address&id='+id+'&nonce='+nonce;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
            //console.log( "Data deleted: " + JSON.stringify(msg) );
            if(msg.success)
              $('#tr_row_' + id).remove();
            $( "#address-content" ).toggleClass('order-status-loading');
            modalDiv.modal('hide');
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         //console.log(textStatus);
         modalDiv.modal('hide');
      }
    });
    });

    $('#confirm-delete').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.btn-ok', this).data('recordId', data.recordId);
        $('.btn-ok', this).data('recordNonce', data.recordNonce);
        //console.log(data);
    });

    $('#billing-form').click(function(){
      $( "#address-content" ).toggleClass('order-status-loading');
      $( "#address-content" ).load( ajaxurl+"?action=load_billing_address", function( response, status, xhr ) {
        if ( status == "error" ) {
          var msg = "Sorry but there was an error: ";
          $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
        }
        //console.log( "load_order_status: " + status );
        $( "#address-content" ).toggleClass('order-status-loading');
      });

    });

    $('#shipping-form').click(function(){
      $( "#address-content" ).toggleClass('order-status-loading');
      $( "#address-content" ).load( ajaxurl+"?action=load_shipping_address", function( response, status, xhr ) {
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

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ยืนยันการลบข้อมูล</h4>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบข้อมูลนี้หรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<div class="order-row">
  <div class="order-col-3" style="text-align:left;">
    <h1>สมุดที่อยู่</h1>
  </div>
  <div class="order-col-4" style="text-align:center;">
    <button type="button" id="shipping-form" class="btn btn-info">ที่อยู่ในการจัดส่ง ตามที่ได้ระบุไว้</button>
  </div>
  <div class="order-col-4" style="text-align:center;">
    <button type="button" id="billing-form" class="btn btn-info">ที่อยู่ในการออกใบกำกับภาษี ตามที่ได้ระบุไว้</button>
  </div>
</div>
<div class="order-clear"></div>
<br>
<div class="table-responsive">
  <table id="product_table" class="table">
    <thead>
      <th>ชื่อ-สกุล</th>
      <th>ที่อยู่</th>
      <th>รหัสไปรษณีย์</th>
      <th>เบอร์โทรศัพท์</th>
      <th style="width: 15%"></th>
      <th style="width: 10%"></th>
      <th style="width: 10%"></th>
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
        echo '<td><button type="button" class="btn btn-primary btn-edit" href="#" data-id="'.$address->id.'">แก้ไข</button></td>';
        echo '<td><button type="button" class="btn btn-danger btn-xs" href="#" data-record-id="'.$address->id.'" data-record-nonce="'.wp_create_nonce( 'delete_user_address_' . $address->id ).'" data-toggle="modal" data-target="#confirm-delete" style="width:50px;">ลบ</button></td>';
        echo '</tr>';
      }

    ?>
    </tbody>
  </table>
</div>
<button type="button" id="address-form" class="btn btn-success">เพิ่มที่อยู่ใหม่</button>
