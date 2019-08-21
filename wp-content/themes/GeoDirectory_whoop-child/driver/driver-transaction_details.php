<?php /* Template Name: driver-transaction_details */ ?>
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
            }

            $( ".wrapper-loading" ).toggleClass('order-status-loading');
        });

        // console.log( $.datepicker.parseDate('dd/mm/yy', $( "#start_date" ).val()));

    });
});

</script>

<h1 class="page-title">

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