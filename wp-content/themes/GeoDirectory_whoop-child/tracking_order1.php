<?php /* Template Name: tracking_order1 */ 
global $wpdb, $current_user;

is_tamzang_admin();

$order_log = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM driver_order_log ORDER by driver_order_id desc  ", array()
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
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
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

jQuery(document).ready(function() {
    $('#tran_list tfoot th').each( function () {
        var title = $(this).text();
        if(title == "id_คนส่ง" || title == "Order_id")
            $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
    });

    var table = jQuery('#tran_list').DataTable({
        "ordering": true,
        columnDefs: [{
            orderable: false,
            targets: "no-sort"
        }]
    });

    // Apply the search
    table.columns().every( function () {
    var that = this;

        $( 'input', this.footer() ).on( 'keyup change clear', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );

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

    // $('#tran_list').on('click', 'input[type="checkbox"]', function() {
    //     var id = $(this).val();
    //     var nonce = $(this).data('nonce');
    //     $('.wrapper-loading').toggleClass('order-status-loading');

    //     var send_data = 'action=approve_driver2&id='+id+'&nonce='+nonce;
    //     console.log(send_data);
    //     $.ajax({
    //     type: "POST",
    //     url: ajaxurl,
    //     data: send_data,
    //     success: function(msg){
    //             //console.log( "Updated status callback: " + JSON.stringify(msg) );
    //             console.log(JSON.stringify(msg));
    //             $('.wrapper-loading').toggleClass('order-status-loading');
    //     },
    //     error: function(XMLHttpRequest, textStatus, errorThrown) {
    //         console.log(textStatus);
    //         $('.wrapper-loading').toggleClass('order-status-loading');
    //     }
    //     });

    // });
});

</script>
<body>

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

<h2>สถานะ Order ของคนส่ง</h2>

<div class="wrapper-loading">

<table id="tran_list" style="width:70%" class="hover">
    <thead>
        <tr>
            <th class="no-sort">ร้านค้า</th>
            <th class="no-sort">คนส่ง</th>
            <th class="no-sort">id_คนส่ง</th>
            <th>Order_id</th>
            <th class="no-sort">สถานะ</th>
            <th>วันที่ได้งาน</th>
            <th>วันที่ส่งต่องาน</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($order_log as $log) {
            echo "<tr>";
            echo "<td><a href='".get_permalink( $log->tamzang_id )."' class='btn btn-info' target='_blank'>".get_the_title( $log->tamzang_id)."</a></td>";
            echo "<td><a href='".home_url('/driver-edit/')."?d_id=".$log->driver_id."' class='btn btn-info' target='_blank'>คนส่ง</a></td>";
            echo "<td>".$log->driver_id."</td>";
            echo "<td>".$log->driver_order_id."</td>";
            if($log->status == 2)
                echo "<td>คนส่งรับงาน</td>";
            else if($log->status == 3)
                echo "<td>ส่งงานเรียบร้อย</td>";
            else if($log->status == 4)
                echo "<td>คนส่งปฏิเสธงาน</td>";
            else
                echo "<td>".$log->status."</td>";
            echo "<td>".$log->assign_date."</td>";
            if(!empty($log->transfer_date))
                echo "<td>".$log->transfer_date."</td>";
            else
                echo "<td></td>";
            echo "</tr>";
        }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <th>ร้านค้า</th>
            <th>คนส่ง</th>
            <th>id_คนส่ง</th>
            <th>Order_id</th>
            <th>สถานะ</th>
            <th>วันที่ได้งาน</th>
            <th>วันที่ส่งต่องาน</th>
        </tr>
    </tfoot>
</table>

</div>


Operator กด Assign รอ Driver ตอบรับ = 1<br>
Driver  Accept = 2<br>
Driver  complete deliverly = 3<br>
driver reject assign = 4<br>
operator  abort order = 5<br>

</body>
</html>