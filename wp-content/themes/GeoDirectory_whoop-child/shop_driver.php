<?php /* Template Name: Shop driver */ ?>
<?php
global $wpdb, $current_user;

$pid = $_GET['pid'];
$is_current_user_owner = false;
if (isset($pid) && $pid != '') {
  $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
}
if (!is_user_logged_in() || !$is_current_user_owner)
  wp_redirect(home_url());

$group_id = geodir_get_post_meta($pid,'groupID',true);
if($group_id == 0)
    wp_redirect(get_permalink( $pid ));

$group_name = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT group_name FROM serviceGroup where group_id = %d", array($group_id)
    )
);

$drivers = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT driver_id, driver_name FROM driver where groupID like '%".$group_id."%' ", array()
    )
);

$pendding_drivers = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT d.driver_id, d.driver_name FROM driver as d 
        INNER JOIN request_group as rg
        ON d.driver_id = rg.driver_id AND rg.group_id = %d ", array($group_id)
    )
);

function show_driver_list($lists){
    $counter = 0;
    foreach($lists as $driver){
        if($counter == 0){
            echo '<ul class="locations_list">';
        }
        echo '<li class="region">';
        echo $driver->driver_id." ".$driver->driver_name;
        echo '</li>';
        $counter++;
        if($counter > 9){
            echo '</ul>';
            $counter = 0;
        }
    }
    if($counter != 0)
        echo '</ul>';
}

function test_list(){
    $counter = 0;
    for ($x = 0; $x <= 50; $x++) {

        if($counter == 0){
            echo '<ul class="locations_list">';
        }
        echo '<li class="region">';
        echo "The number is: $x <br>";
        echo '</li>';

        $counter++;
        if($counter > 9){
            echo '</ul>';
            $counter = 0;
        }
    }
    echo '</ul>';
}

get_header(); ?>

<script>
function isPositive(s)
{
    return /^\d*$/.test(s);
}
jQuery(document).ready(function($){
    

    jQuery(document).on("click", ".add-driver", function(){
        var pid = $(this).data('pid');
        var nonce = $(this).data('nonce');
        var driver_id = $('#input_driver_id').val();

        if(!isPositive(driver_id))
            return;

        var send_data = 'action=shop_add_driver_to_group&driver_id='+driver_id+'&pid='+pid+'&nonce='+nonce;
        console.log(send_data);

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Updated status callback: " + JSON.stringify(msg) );
                if(msg.success){
                    $( "#msg" ).html('<font color="green"><b>'+msg.data.msg+'</b></font>');
                    $("#pending").append('<li>'+msg.data.result+'</li>');
                }else{
                    $( "#msg" ).html('<font color="red"><b>'+msg.data+'</b></font>' );
                }

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);
            $( "#msg" ).html('<font color="red"><b>'+textStatus+'</b></font>');
            }
        });

    });

});
</script>
<div id="geodir_wrapper" class="geodir-single">
<div class="clearfix geodir-common">
<?php  echo '<h2><li><a href="'.get_permalink( $pid ).'">'.get_the_title( $pid ).'</a></li></h2>'; ?>
<hr>
<div class="order-row">
<div style="float:left;">
    เพิ่มคนส่ง: 
</div>
<div class="order-col-3">
    <input type="text" id="input_driver_id" value="">
</div>
<button class="btn btn-success add-driver"
        data-pid="<?php echo $pid; ?>"
        data-nonce="<?php echo wp_create_nonce( 'shop_add_driver_to_group_'.$pid); ?>" 
    >ตกลง</button>
<div id="msg"></div>
</div><div class="order-clear"></div>
<h1 class="page-title">
    กลุ่ม: <?php echo $group_name; ?>
</h1>
<br>
<div class="order-row">
    <b>รายชื่อสมาชิก:</b>
</div><div class="order-clear"></div>

<div class="order-row">
<?php
//test_list();
show_driver_list($drivers);
?>
</div><div class="order-clear"></div>
<hr>
<div class="order-row">
    <b>รายชื่อที่รอการตอบรับ:</b>
</div><div class="order-clear"></div>

<div class="order-row">
<?php
//test_list();
show_driver_list($pendding_drivers);
?>
<ul class="locations_list" id="pending">
</ul>
</div><div class="order-clear"></div>


</div>
</div>

<?php get_footer(); ?>