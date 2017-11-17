<?php
/**
 * Contains functions related to Location Manager plugin update.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
global $wpdb, $table_prefix;

$table_prefix = $wpdb->prefix;

$locatonid = implode(',', $_REQUEST['mergeid']);

?>
<h3><?php echo GD_MANAGE_LOCATION; ?></h3>
	 <?php
	 	$nonce = wp_create_nonce( 'location_merge_wpnonce' );
	 ?>
	 <input type="hidden" name="location_merge_nonce" value="<?php echo $nonce;?>" />
	 <input type="hidden" name="geodir_location_merge_ids" id="geodir_location_merge_ids" value="<?php echo sanitize_text_field($locatonid); ?>" />
		<input type="hidden" name="location_ajax_action" value="merge" />
		<table style=" width:100%" cellpadding="5" class="widefat post fixed" >
				<thead>
						<tr>
							<th width="15" align="left">&nbsp;</th>
								<th width="140" align="left"><strong><?php echo GD_LOCATION_CITY; ?></strong></th>
								<th width="140" align="left"><strong><?php echo GD_LOCATION_REGION; ?></strong></th>
								<th width="140" align="left"><strong><?php echo GD_LOCATION_COUNTRY; ?></strong></th>
								<th width="145" align="left"><strong><?php echo GD_LOCATION_LATITUDE; ?></strong></th>
								<th width="145" align="left"><strong><?php echo GD_LOCATION_LONGITUDE; ?></strong></th>
						</tr>
				<?php
				
				$locatonid_merge_ids_length = count($_REQUEST['mergeid']);
				$format = array_fill(0, $locatonid_merge_ids_length, '%d');
				$format = implode(',', $format);
		
				$locationsql = $wpdb->prepare("select * from ".POST_LOCATION_TABLE." WHERE location_id IN ($format) ORDER BY city ASC",$_REQUEST['mergeid']);
				$locationinfo = $wpdb->get_results($locationsql);
				if($locationinfo)
				{
				foreach($locationinfo as $locationinfoObj)
				{
				?>
				<tr>
					<td><input type="radio" onclick="geodir_location_select_primary_city(<?php echo $locationinfoObj->location_id; ?>)" value="<?php echo $locationinfoObj->location_id; ?>" id="merge" name="gd_merge" /></td>
						<td><?php echo $locationinfoObj->city;?></td>
						<td><?php echo $locationinfoObj->region;?></td>
						<td><?php echo $locationinfoObj->country;?></td>
						<td><?php echo $locationinfoObj->city_latitude;?></td>
						<td><?php echo $locationinfoObj->city_longitude;?></td>
						
				</tr>
				<?php
				}
				}
				?>
				</thead>
		</table>
		<label style="padding:15px; display:block;"><b><?php echo GD_LOCATION_NOTE; ?></b> <?php echo GD_LOCATION_PRIMARY_CITY; ?></label>
		<div id="geodir_location_merge_div"></div>

