<?php /* Template Name: driver-bankaccount */ ?>
<?php
global $wpdb, $current_user;


$driver = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM driver where driver_id = %d ", array($current_user->ID)
    )
);
?>

<script>


jQuery(document).ready(function($){


    $('#confirm-bankaccount').on('click', '.btn-ok', function(e) {

        $( ".wrapper-loading" ).toggleClass('order-status-loading');
        var nonce = $(this).data('nonce');
        var name = $( "#bank_name" ).val();
        var account = $( "#bank_account" ).val();

        var send_data = 'action=driver_add_bankaccount&nonce='+nonce+'&name='+name+'&account='+account;
        //console.log(send_data);
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "respone bankaccount: " + JSON.stringify(msg) );
                if(msg.success){
                    $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_money", function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Sorry but there was an error: ";
                    $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                  }
                  else{
                    $( ".wrapper-loading" ).toggleClass('order-status-loading');
                  }
                });
              }else{
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
                console.log(msg);
              }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-bankaccount').modal('toggle');

    });


    $('#confirm-bankaccount').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.bankaccount-text', this).empty();
        $('.bankaccount-text', this).html("<b>ชื่อบัญชี: "+$( "#bank_name" ).val()+"</b><br><b>เลขบัญชี: "+$( "#bank_account" ).val()+"</b>");

        $('.btn-ok', this).data('nonce', data.nonce);
        
        //console.log(data);
    });
});

</script>

<div class="modal fade" id="confirm-bankaccount" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel2">ยืนยันการเพิ่มสมุดบัญชี</h4>
            </div>
            <div class="modal-body">
                <p class="bankaccount-text"></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-success btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<div class="order-row">
    <div class="order-col-3">
        ชื่อบัญชี
    </div>
    <div class="order-col-9" style="text-align:left;">
        <input name="bank_name" id="bank_name" value="" type="text" />
    </div>
</div>

<div class="order-row">
    <div class="order-col-3">
        เลขบัญชี
    </div>
    <div class="order-col-9" style="text-align:left;">
        <input name="bank_account" id="bank_account" value="" type="text" />
    </div>
</div>
<br>
<div class="order-row" style="text-align:center;">
    <button class="btn btn-success" href="#" 
        data-nonce="<?php echo wp_create_nonce( 'driver_bankaccount_'.$current_user->ID); ?>" 
        data-toggle="modal" data-target="#confirm-bankaccount">ตกลง</button>
</div>