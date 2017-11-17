<?php
/**
 * Contains location settings page template.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
$location_multicity = get_option( 'location_multicity' );
$location_everywhere = get_option( 'location_everywhere' );
$location_neighbourhoods = get_option( 'location_neighbourhoods' );
?>
	
<h3><?php echo __('Geo Directory Location Settings', 'geodirlocation'); ?></h3>

<input type="hidden" name="location_ajax_action" value="settings">

<table class="form-table" id="gd_option_form">
	<tbody>
		<tr valign="top" class="single_select_page">
			<td class="forminp">
			 <span class="description"><?php echo GD_LOCATION_MULTICITY_DESC;?></span>
               
             <div class="gtd-formfeild">
					<input type="checkbox" <?php if($location_multicity){ echo 'checked="checked"'; } ?>  name="location_multicity" value="yes" />&nbsp;&nbsp;&nbsp; <?php echo GD_LOCATION_MULTICITY;?>
			 </div>
			      
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<td class="forminp">
			 <span class="description"><?php echo GD_LOCATION_EVERYWHERE_DESC;?></span>
               
             <div class="gtd-formfeild">
					<input type="checkbox" name="location_everywhere" <?php if($location_everywhere){ echo 'checked="checked"'; } ?> value="yes" />&nbsp;&nbsp;&nbsp; <?php echo GD_LOCATION_EVERYWHERE;?>
			 </div>
			      
			</td>
		</tr>
		
		<tr valign="top" class="single_select_page">
			<td class="forminp">
			 <span class="description"><?php echo __("Select the option if you wish to enable neighbourhood options.", 'geodirlocation');?></span>
               
             <div class="gtd-formfeild">
					<input type="checkbox" name="location_neighbourhoods" <?php if($location_neighbourhoods){ echo 'checked="checked"'; } ?> value="yes" />&nbsp;&nbsp;&nbsp; <?php echo GD_LOCATION_NEIGHBOURHOODS;?>
			 </div>
			      
			</td>
		</tr>
		
	</tbody>
</table>



<p class="submit" style="margin-top:10px; padding-left:12px;">
<input class="button-primary" type="submit" name="submit" value="<?php echo GD_LOCATION_SAVE;?>">
</p>