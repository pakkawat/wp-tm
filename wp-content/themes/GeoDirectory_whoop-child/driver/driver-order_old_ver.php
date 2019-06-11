<?php /* Template Name: driver-order */ ?>
<?php

?>

<script>
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
        var nonce = $(this).data('nonce');
        console.log( "ยืนยันรับ order: " + order_id );
        $( "#confirm_button_"+order_id ).html('<img src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/pass.png" />ยืนยันเรียบร้อย');

        // var send_data = 'action=driver_confirm_order&id='+order_id+'&nonce='+nonce;
        // $.ajax({
        //     type: "POST",
        //     url: ajaxurl,
        //     data: send_data,
        //     success: function(msg){
        //         console.log( "Order confirmed: " + JSON.stringify(msg) );
        //         if(msg.success){
        //             $( "#panel_"+order_id ).removeClass('panel-default').addClass('panel-success');
        //             $( "#panel_"+order_id ).find(".panel-footer").remove();
        //             $( "#status_"+order_id ).html('<div class="order-row" style="text-align:center;"><h1>ยกเลิก</h1></div>');
        //         }
        //     },
        //     error: function(XMLHttpRequest, textStatus, errorThrown) {
        //     console.log(textStatus);

        //     }
        // });

        $('#confirm-order').modal('toggle');

    });

    $('#confirm-order').on('click', '.btn-reject', function(e) {

        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        console.log( "ปฏิเสธ order: " + order_id );
        $( "#confirm_button_"+order_id ).html('<img src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/error.png" />ปฏิเสธการสั่งซื้อ');

        // var send_data = 'action=driver_reject_order&id='+order_id+'&nonce='+nonce;
        // $.ajax({
        //     type: "POST",
        //     url: ajaxurl,
        //     data: send_data,
        //     success: function(msg){
        //         console.log( "Order confirmed: " + JSON.stringify(msg) );
        //         if(msg.success){
        //             $( "#panel_"+order_id ).removeClass('panel-default').addClass('panel-success');
        //             $( "#panel_"+order_id ).find(".panel-footer").remove();
        //             $( "#status_"+order_id ).html('<div class="order-row" style="text-align:center;"><h1>ยกเลิก</h1></div>');
        //         }
        //     },
        //     error: function(XMLHttpRequest, textStatus, errorThrown) {
        //     console.log(textStatus);

        //     }
        // });

        $('#confirm-order').modal('toggle');

        });


    $('#confirm-order').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.order-text', this).text(data.text);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('nonce', data.nonce);
        $('.btn-reject', this).data('id', data.id);
        $('.btn-reject', this).data('nonce', data.nonce);
        //console.log(data);
    });

    function isPositiveInteger(s)
    {
        return /^\d+$/.test(s);
    }

    $('#confirm-adjust').on('click', '.btn-ok', function(e) {

        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        var adjust = $(this).data('adjust');
        console.log( "adjust order: " + order_id );

        var send_data = 'action=driver_adjust_price&id='+order_id+'&nonce='+nonce+'&adjust='+adjust;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order adjusted: " + JSON.stringify(msg) );
                if(msg.success){
                    $( "#order_adjust_"+order_id ).html('<font color="#eb9316"><b>รอลูกค้ายอมรับ</b></font>');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-adjust').modal('toggle');

        

    });


    $('#confirm-adjust').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        var check = isPositiveInteger($('#adjust_'+data.id).val());
        if(check){
            $('.btn-ok', this).show();
            $('.btn-ok', this).data('id', data.id);
            $('.btn-ok', this).data('nonce', data.nonce);
            $('.btn-ok', this).data('adjust', $('#adjust_'+data.id).val());
            $('.adjust-text', this).append("คุณต้องการเพิ่มราคาอีก<b><i>"+$('#adjust_'+data.id).val()+"</i></b> บาท");
        }else{
            $('.btn-ok', this).hide();
            $('.adjust-text', this).text('กรุณาใส่ราคาให้ถูกต้อง');
        }
        
        //console.log(data);
    });


});
</script>

<div class="modal fade" id="confirm-order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ยืนยันรับคำสั่งซื้อ</h4>
            </div>
            <div class="modal-body">
                <p>คุณกำลังจะยืนยันรับคำสั่งซื้อรหัส <b><i class="order-text"></i></b></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-reject" data-dismiss="modal" style="float:left;">ปฏิเสธ</button>
                <button type="button" class="btn btn-success btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-adjust" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel2">ยืนยันการปรับราคา</h4>
            </div>
            <div class="modal-body">
                <p class="adjust-text"></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-success btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>


<div id="main_order">
    <div class="wrapper-loading">
        <?php get_template_part( 'driver/driver', 'order_list' ); ?>
    </div>
</div>
