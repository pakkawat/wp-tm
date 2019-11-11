<?php /* Template Name: driver-approve */ 
global $wpdb, $current_user;

is_tamzang_admin();

$register_list = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM register_driver ORDER by regis_date desc  ", array()
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
    jQuery('#tran_list').DataTable({
        "ordering": true,
        columnDefs: [{
            orderable: false,
            targets: "no-sort"
        }]
    });

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

    $('#tran_list').on('click', 'input[type="checkbox"]', function() {
        var id = $(this).val();
        var nonce = $(this).data('nonce');
        $('.wrapper-loading').toggleClass('order-status-loading');

        var send_data = 'action=approve_driver2&id='+id+'&nonce='+nonce;
        console.log(send_data);
        $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
        success: function(msg){
                //console.log( "Updated status callback: " + JSON.stringify(msg) );
                console.log(JSON.stringify(msg));
                $('.wrapper-loading').toggleClass('order-status-loading');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);
            $('.wrapper-loading').toggleClass('order-status-loading');
        }
        });

    });
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

<div class="wrapper-loading">

<table id="tran_list" style="width:70%" class="hover">
    <thead>
        <tr>
            <th class="no-sort">Approve</th>
            <th>วันที่</th>
            <th>ชื่อ</th>
            <th class="no-sort">เบอร์โทรศัพท์</th>
            <th class="no-sort">Note</th>
            <!--<th class="no-sort">รูปภาพ</th>-->
            <!--<th class="no-sort">บัตรประชาชน</th>-->
            <!--<th class="no-sort">ใบอนุญาตขับขี่</th>-->
            <!--<th class="no-sort">สำเนาทะเบียนรถ</th>-->
            <!--<th class="no-sort">หนังสือยินยอมการใช้รถ</th>-->
            <th class="no-sort">แก้ไข</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($register_list as $register) {
            echo "<tr>";
            echo "<td><input type='checkbox' class='checkbox_driver' 
                    data-nonce=".wp_create_nonce( 'approve_driver_'.$register->wp_user_id)."  
                    value=".$register->wp_user_id."
                    ".($register->approve ? 'checked' : '')."></td>";
            echo "<td>".date('d/m/Y', strtotime($register->regis_date))."</td>";
            echo "<td>".$register->wp_user_id."-".$register->name."</td>";
            echo "<td>".$register->phone."</td>";
            echo "<td>".$register->note."</td>";
            // echo "<td><img width='200' height='200' src=".$uploads['baseurl'].$register->profile_pic." 
            //     data-toggle='modal' data-target='#image-modal' 
            //     data-src='".$uploads['baseurl'].$register->profile_pic."'/></td>";
            // echo "<td><img width='200' height='200' src=".$uploads['baseurl'].$register->id_card." 
            //     data-toggle='modal' data-target='#image-modal' 
            //     data-src='".$uploads['baseurl'].$register->id_card."'/></td>";
            // echo "<td><img width='200' height='200' src=".$uploads['baseurl'].$register->licence." 
            //     data-toggle='modal' data-target='#image-modal' 
            //     data-src='".$uploads['baseurl'].$register->licence."'/></td>";
            // echo "<td><img width='200' height='200' src=".$uploads['baseurl'].$register->car_licence." 
            //     data-toggle='modal' data-target='#image-modal' 
            //     data-src='".$uploads['baseurl'].$register->car_licence."'/></td>";
            // echo "<td><img width='200' height='200' src=".$uploads['baseurl'].$register->car_licence2." 
            //     data-toggle='modal' data-target='#image-modal' 
            //     data-src='".$uploads['baseurl'].$register->car_licence2."'/></td>";
            echo "<td><a href='".home_url('/driver-edit/')."?d_id=".$register->wp_user_id."' class='btn btn-info' target='_blank'>แก้ไข</a></td>";
            echo "</tr>";
        }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Approve</th>
            <th>วันที่</th>
            <th>ชื่อ</th>
            <th>เบอร์โทรศัพท์</th>
            <th>Note</th>
            <!--<th>รูปภาพ</th>-->
            <!--<th>บัตรประชาชน</th>-->
            <!--<th>ใบอนุญาตขับขี่</th>-->
            <!--<th>สำเนาทะเบียนรถ</th>-->
            <!--<th>หนังสือยินยอมการใช้รถ</th>-->
            <th>แก้ไข</th>
        </tr>
    </tfoot>
</table>

</div>

</body>
</html>