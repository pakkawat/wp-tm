<?php /* Template Name: driver-group */ ?>
<?php
global $wpdb, $current_user;

$requests = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT sg.group_id, sg.group_name FROM request_group as rg 
                        INNER JOIN serviceGroup as sg
                        ON sg.group_id = rg.group_id AND rg.driver_id = %d ",array($current_user->ID)
                    )
            );

$my_groups = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT groupID FROM driver WHERE driver_id = %d ", array($current_user->ID)
                )
            );
$my_groups = substr($my_groups, 0, -1);// delete last comma

if(!empty($my_groups)){
    $groups = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM serviceGroup WHERE group_id IN (".$my_groups.") ", array()
                )
            );
}
       
?>
<script>
jQuery(document).ready(function($){
    jQuery(document).on("click", ".join-group", function(){
            
        $( ".wrapper-loading" ).toggleClass('order-status-loading');
        var gid = $(this).data('gid');
        var nonce = $(this).data('nonce');
        var send_data = 'action=driver_join_group&gid='+gid+'&nonce='+nonce;

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){

                if(msg.success){
                    $('#gid-' + gid).remove();
                    $('#driver_group > tbody:last-child').append(msg.data);
                    //$('#driver_group > tbody:last-child').append('<tr id="gid-'+gid+'"><td>'+msg.data.gname+'</td><td></td><td style="text-align: center;">'+msg.data.gbutton+'</td></tr>');
                }
                $( ".wrapper-loading" ).toggleClass('order-status-loading');

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            }
        });

    });

    $('#confirm-group').on('click', '.btn-ok', function(e) {

        var gid = $(this).data('gid');
        var gtype = $(this).data('gtype');
        var nonce = $(this).data('nonce');

        $( ".wrapper-loading" ).toggleClass('order-status-loading');
        if(gtype == "1"){
            var send_data = 'action=driver_decline_group&gid='+gid+'&nonce='+nonce;
        }else{
            var send_data = 'action=driver_quit_group&gid='+gid+'&nonce='+nonce;
        }
        
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){

                if(msg.success){
                    $('#gid-' + gid).remove();
                }
                $( ".wrapper-loading" ).toggleClass('order-status-loading');

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            }
        });

        $('#confirm-group').modal('toggle');

    });

    $('#confirm-group').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        if(data.type == "1"){
            $('.group-text', this).html("คุณกำลังปฏิเสธเข้าร่วมกลุ่ม <b><i>"+data.gname+"</i></b>");
        }else{
            $('.group-text', this).html("คุณกำลังออกจากกลุ่ม <b><i>"+data.gname+"</i></b>");
        }
        
        $('.btn-ok', this).data('gid', data.gid);
        $('.btn-ok', this).data('gtype', data.type);
        $('.btn-ok', this).data('nonce', data.nonce);
    });

    $('#list-group').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.group-title', this).html(data.gname);
        var send_data = 'action=driver_list_group&gid='+data.gid+'&nonce='+data.nonce;
        console.log(send_data);
        $( ".modal-show-list" ).toggleClass('order-status-loading');

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){

                if(msg.success){
                    $('#show-list').html(msg.data);
                }
                $( ".modal-show-list" ).toggleClass('order-status-loading');

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".modal-show-list" ).toggleClass('order-status-loading');
            }
        });

    });
});
</script>

<div class="modal fade" id="confirm-group" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <p class="group-text"></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <div class="col-6">
                    <button type="button" class="btn btn-default btn-reject" data-dismiss="modal" style="float:left;">ปิด</button>
                </div>
                <div class="col-6 text-right">
                    <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="list-group" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="group-title"></h4>
            </div>
            <div class="modal-body modal-show-list">
                <ul id="show-list">
                </ul>
            </div>
            <div class="modal-footer">
                <div class="col-6">
                    <button type="button" class="btn btn-default btn-reject" data-dismiss="modal" style="float:left;">ปิด</button>
                </div>
                <div class="col-6 text-right">

                </div>
            </div>
        </div>
    </div>
</div>

<div class="wrapper-loading">

<?php if(!empty($requests)){?>

<table class="table">
<thead>
<th>คำเชิญเข้าร่วมกลุ่ม</th>
<th></th>
<th></th>
</thead>
<tbody>

<?php

foreach ($requests as $group){
    echo '<tr id="gid-'.$group->group_id.'">';
        echo '<td>';
        echo $group->group_name;
        echo '</td>';
        echo '<td style="text-align: center;">';
        echo '<button class="btn btn-success join-group" href="#"
                data-gid="'.$group->group_id.'" 
                data-nonce="'.wp_create_nonce( 'driver_join_group_'.$current_user->ID).'">เข้าร่วม</button>';
        echo '</td>';
        echo '<td style="text-align: center;">';
        echo '<button class="btn btn-danger" href="#"  data-toggle="modal" data-target="#confirm-group"
                data-gid="'.$group->group_id.'" data-gname="'.$group->group_name.'" data-type="1"
                data-nonce="'.wp_create_nonce( 'driver_decline_group_'.$current_user->ID).'">ปฏิเสธ</button>';
        echo '</td>';
    echo '</tr>';
}

?>

</tbody>
</table>


<hr>

<?php }?>


<table class="table" id="driver_group">
<thead>
<th>กลุ่ม</th>
<th></th>
<th></th>
</thead>
<tbody>

<?php
if(!empty($groups)){
    foreach ($groups as $group){
        echo '<tr id="gid-'.$group->group_id.'">';
            echo '<td>';
            echo $group->group_name;
            echo '</td>';
            echo '<td style="text-align: center;">';
            echo '<button class="btn btn-info" href="#"  data-toggle="modal" data-target="#list-group"
                    data-gid="'.$group->group_id.'" data-gname="'.$group->group_name.'"
                    data-nonce="'.wp_create_nonce( 'driver_list_group_'.$current_user->ID).'">รายชื่อร้าน</button>';
            echo '</td>';
            echo '<td style="text-align: center;">';
            echo '<button class="btn btn-danger" href="#"  data-toggle="modal" data-target="#confirm-group"
                    data-gid="'.$group->group_id.'" data-gname="'.$group->group_name.'" data-type="2"
                    data-nonce="'.wp_create_nonce( 'driver_quit_group_'.$current_user->ID).'">ออกจากกลุ่ม</button>';
            echo '</td>';
        echo '</tr>';
    }
}
?>

</tbody>
</table>


<hr>


</div>