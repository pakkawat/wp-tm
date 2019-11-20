<?php /* Template Name: driver-money */ ?>
<?php
global $wpdb, $current_user;


$driver = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM driver where driver_id = %d ", array($current_user->ID)
    )
);

$debit_batch_check = false;
$driver_cash = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT driver_cash FROM driver_debit_batch where driver_id = %d AND batch_select = 0 AND batch_number is NULL ", array($current_user->ID)
    )
);

if(empty($driver_cash))
    $debit_batch_check = true;

?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script>

jQuery(function () {
    jQuery(".transaction_date").datepicker({changeMonth: true, changeYear: true, maxDate:'0' });

    jQuery(".transaction_date").datepicker("option", "dateFormat", 'dd-mm-yy');
});

jQuery(document).ready(function($){


    jQuery(document).on("click", ".show_detail", function(){
            
        $( ".wrapper-loading" ).toggleClass('order-status-loading');

        $( ".list" ).load( ajaxurl+"?action=load_driver_transaction_list", { start_date: $( "#start_date" ).val(), end_date: $( "#end_date" ).val() }, function( response, status, xhr ) {
            if ( status == "error" ) {
                var msg = "Sorry but there was an error: ";
                $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
            }else{
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            }
        });

    });

    // $("#dd_withdraw").change(function () {
    //     //console.log();
    //     //$('#withdraw_result').html(this.value * $('#driver_cash').text());
    // });

    $('#confirm-withdraw').on('click', '.btn-ok', function(e) {

        $( ".wrapper-loading" ).toggleClass('order-status-loading');
        var nonce = $(this).data('nonce');
        var dd_value = $(this).data('dd_value');

        var send_data = 'action=driver_withdraw_money&nonce='+nonce+'&dd_value='+dd_value;
        //console.log(send_data);
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "respone withdraw: " + JSON.stringify(msg) );
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

        $('#confirm-withdraw').modal('toggle');

    });


    $('#confirm-withdraw').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.withdraw-text', this).empty();
        $('.withdraw-text', this).html("<b>"+$( "#dd_withdraw option:selected" ).text()+"</b>");

        $('.btn-ok', this).data('nonce', data.nonce);
        $('.btn-ok', this).data('dd_value', $( "#dd_withdraw" ).val());
        
        //console.log(data);
    });
});

</script>

<div class="modal fade" id="confirm-withdraw" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel2">ยืนยันการถอนเงิน</h4>
            </div>
            <div class="modal-body">
                <p class="withdraw-text"></p>
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
    <h1 class="page-title" style="float: left;">
        หมายเลขไอดี: <?php echo $current_user->ID; ?>
    </h1>
    <div style="float: right;"><b>driver_cash:</b> <div id="driver_cash" style="display: inline-block;"><?php echo $driver->driver_cash;?></div> บาท</div>
</div>
<div class="order-clear"></div>
<br>
<div class="order-row">
<?php if($debit_batch_check){ ?>
    <div class="order-col-6">
        <select id="dd_withdraw">
            <option value="0.5">ถอน <?php echo str_replace(".00", "",number_format($driver->driver_cash*0.5,2)); ?> บาท (50%)</option>
            <option value="0.6">ถอน <?php echo str_replace(".00", "",number_format($driver->driver_cash*0.6,2)); ?> บาท (60%)</option>
            <option value="0.7">ถอน <?php echo str_replace(".00", "",number_format($driver->driver_cash*0.7,2)); ?> บาท (70%)</option>
            <option value="0.8">ถอน <?php echo str_replace(".00", "",number_format($driver->driver_cash*0.8,2)); ?> บาท (80%)</option>
            <option value="0.9">ถอน <?php echo str_replace(".00", "",number_format($driver->driver_cash*0.9,2)); ?> บาท (90%)</option>
            <option value="1">ถอน <?php echo str_replace(".00", "",number_format($driver->driver_cash*1,2)); ?> บาท (100%)</option>
        </select>
    </div>
    <button class="btn btn-success" href="#" 
        data-nonce="<?php echo wp_create_nonce( 'driver_withdraw_money_'.$current_user->ID); ?>" 
        data-toggle="modal" data-target="#confirm-withdraw">ตกลง</button>
<?php }else{ ?>
<h2>ถอน: <?php echo str_replace(".00", "",number_format($driver_cash, 2)); ?> บาท</h2>
<h2>ท่านสามารถถอนเงินอีกครั้งได้ในวันถัดไป</h2>
<?php } ?>
</div>
<div class="order-clear"></div>


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