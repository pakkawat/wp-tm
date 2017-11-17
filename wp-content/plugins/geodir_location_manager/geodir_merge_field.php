<?php
/**
 * Contains functions related to Location Manager plugin update.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
global $wpdb;

$gd_select_city = $_REQUEST['merge_id'];

$gd_other_id = $_REQUEST['mergeall'];

$gd_selectid = $wpdb->prepare("select * from ".POST_LOCATION_TABLE." WHERE location_id = %d",array($gd_select_city));

$gd_selectid_info = $wpdb->get_row($gd_selectid);

$gd_other_sql = $wpdb->prepare("select * from ".POST_LOCATION_TABLE." WHERE location_id IN (%d) AND location_id!=%d  ORDER BY city ASC", array($gd_other_id,$gd_select_city));

$gd_other_info = $wpdb->get_results($gd_other_sql);

// some servers not interpreting this correctly as html so we manaully set it here
try {
    header('Content-Type: text/html');
} catch(Exception $e) {
    // Error
}

?>

<div class="merge-locationto">
    <div>
        <div class="gtd-formfeild" style="padding-top:10px;">
        	<input type='text' style="width:226px;" id="gd_city" name="gd_city" value="<?php echo $gd_selectid_info->city; ?>"/>
        </div>
        <div class="gtd-formfeild" style="padding-top:10px;">
        	<input type='text' style="width:226px;" id="gd_region" name="gd_region" value="<?php echo $gd_selectid_info->region; ?>"/>
        </div>
        <div class="gtd-formfeild" style="padding-top:10px;">
        	<input type='text' style="width:226px;" id="gd_country" name="gd_country" readonly value="<?php echo geodir_get_normal_country( $gd_selectid_info->country ); ?>"/>
        </div>
        <div class="gtd-formfeild" style="padding-top:10px;">
            <input type='text' style="width:226px;" id="gd_lat" name="gd_lat" value="<?php echo $gd_selectid_info->city_latitude; ?>"/>
        </div>
        <div class="gtd-formfeild" style="padding-top:10px;">
        	<input type='text' style="width:226px;" id="gd_log" name="gd_log" value="<?php echo $gd_selectid_info->city_longitude; ?>"/>
        </div>
        <div class="gtd-formfeild" style="padding-top:10px; margin-bottom:15px;">
        	<input type='submit' class="button-primary" value="<?php echo GD_LOCATION_SET_PRIMARY; ?>"/>
        </div>
    </div>
</div>

<div class="merge-locationfrom">
<?php if ( !empty( $gd_other_info ) ) : foreach( $gd_other_info as $gd_other_info_obj ) : ?>
    <div>
        <ul style="margin:0; margin-left:5px;">
            <li style="padding:10px 5px;margin:0"><a href="javascript:void(0)" id="gd_set_city" style="text-decoration:none;" onClick="geodir_set_primary_location(this.id, '<?php echo $gd_other_info_obj->city; ?>')">&laquo; <?php echo $gd_other_info_obj->city; ?></a></li>
            <li style="padding:12px 5px;margin:0"><a href="javascript:void(0)" id="gd_set_region" style="text-decoration:none;" onClick="geodir_set_primary_location(this.id, '<?php echo $gd_other_info_obj->region; ?>')">&laquo; <?php echo $gd_other_info_obj->region; ?></a></li>
            <li style="padding:10px 5px;margin:0"><a href="javascript:void(0)" id="gd_set_country" style="text-decoration:none;" onClick="geodir_set_primary_location(this.id, '<?php echo $gd_other_info_obj->country; ?>')">&laquo; <?php echo geodir_get_normal_country( $gd_other_info_obj->country ); ?></a></</li>
            <li style="padding:10px 5px;margin:0"><a href="javascript:void(0)" id="gd_set_lat" style="text-decoration:none;" onClick="geodir_set_primary_location(this.id, '<?php echo $gd_other_info_obj->city_latitude; ?>')">&laquo; <?php echo $gd_other_info_obj->city_latitude; ?></a></li>
            <li style="padding:10px 5px;margin:0"><a href="javascript:void(0)" id="gd_set_log" style="text-decoration:none;" onClick="geodir_set_primary_location(this.id, '<?php echo $gd_other_info_obj->city_longitude; ?>')">&laquo; <?php echo $gd_other_info_obj->city_longitude; ?></a></li>
        </ul>
    </div>
<?php endforeach; endif; ?>
<div style="clear:both;"></div>
</div>
<div style="clear:both;"></div>
