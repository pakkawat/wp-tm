<?php
/**
 * Contains neighbourhood list page template.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 */
global $wpdb, $table_prefix;

$table_prefix = $wpdb->prefix;

$id = (int)$_REQUEST['id'];
?>
<div><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit&id='.$id."&add_hood=true";?>"><?php echo GD_CITY_ADD_HOOD; ?></a></div>

<h3><?php echo GD_MANAGE_NEIGHBOURHOOD; ?></h3>

		
		<table style=" width:100%" cellpadding="5" class="widefat post fixed" >
				<thead>
						<tr>
								<th width="140" align="left"><strong><?php echo GD_LOCATION_CITY; ?></strong></th>
								<th width="140" align="left"><strong><?php echo GD_NEIGHBOURHOOD; ?></strong></th>
								<th width="140" align="left"><strong><?php echo GD_HOOD_LAT; ?></strong></th>
								<th width="145" align="left"><strong><?php echo GD_HOOD_LONG; ?></strong></th>
								<th width="145" align="left"><strong><?php echo GD_LOCATION_ACTION; ?></strong></th>
						</tr>
				<?php
				$neighbourhood_sql = 
				$wpdb->prepare(
				"select * from ".POST_NEIGHBOURHOOD_TABLE." WHERE hood_location_id=%d ORDER BY hood_id DESC",
				array($id)
				);
				$hood_info = $wpdb->get_results($neighbourhood_sql);
				
				if($hood_info)
				{
				foreach($hood_info as $hood_infoObj)
				{
					$cityid = $hood_infoObj->hood_location_id;
					
					$nonce = wp_create_nonce( 'neighbourhood_delete_'.$hood_infoObj->hood_id );
					
					$cityname = $wpdb->get_var(
						$wpdb->prepare(
						"select city from ".POST_LOCATION_TABLE." WHERE location_id=%d",
						array($cityid)
						)
					);
				?>
				<tr>
						<td><?php echo stripslashes($cityname);?></td>
						<td><?php echo stripslashes($hood_infoObj->hood_name);?></td>
						<td><?php echo stripslashes($hood_infoObj->hood_latitude);?></td>
						<td><?php echo stripslashes($hood_infoObj->hood_longitude);?></td>
                        <td>
                        
                            <a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit&add_hood=true&id='.$hood_infoObj->hood_location_id.'&hood_id='.$hood_infoObj->hood_id;?>"><img src="<?php echo plugins_url('',__FILE__); ?>/images/edit.png" alt="<?php echo GD_LOCATION_EDIT; ?>" title="<?php echo GD_LOCATION_EDIT; ?>"/></a>
                        		
                                &nbsp;&nbsp;
                                
                            <a href="<?php echo admin_url().'admin-ajax.php?action=geodir_locationajax_action&location_ajax_action=delete_hood&id='.$hood_infoObj->hood_id.'&city_id='.$hood_infoObj->hood_location_id.'&_wpnonce='.$nonce; ?>" onClick="return confirm('Are you sure want to delete this Neighbourhood?');"><img src="<?php echo plugins_url('',__FILE__); ?>/images/delete.png" alt="<?php echo GD_LOCATION_DELETE; ?>" title="<?php echo GD_LOCATION_DELETE; ?>"/></a>
                            
                        </td>
						
				</tr>
				<?php
				}
				}
				else
				{
				?>
                    <tr>
                        <td><?php echo MSG_NO_RESULT; ?></td>
                    </tr>
                <?php } ?>
				</thead>
		</table>
