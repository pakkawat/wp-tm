<?php
/**
 * Contains location SEO page template.
 *
 * @since 1.0.0
 * @since 1.4.2 Customized by using pagination and search filter.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 */
global $wpdb, $table_prefix;

$table_prefix = $wpdb->prefix;

$get_countries = $wpdb->get_results("select * from ".POST_LOCATION_TABLE." GROUP BY country ORDER BY country ASC");

$location_type = !empty($_REQUEST['gd_loc']) && in_array($_REQUEST['gd_loc'], array('country', 'region', 'city')) ? sanitize_text_field($_REQUEST['gd_loc']) : 'country';

$args = array();
$args['location_type'] = $location_type;

$data = geodir_location_seo_settings_list($args);

$items = !empty($data) && !empty($data['items']) ? $data['items'] : NULL;

if ($location_type == 'city') {
	$location_type_label = __('Cities', 'geodirlocation');
	$location_label = __('City', 'geodirlocation');
} else if ($location_type == 'region') {
	$location_type_label = __('Regions', 'geodirlocation');
	$location_label = __('Region', 'geodirlocation');
} else {
	$location_type_label = __('Countries', 'geodirlocation');
	$location_label = __('Country', 'geodirlocation');
}

$imageID = null;
$tagline = null;
?>
<div class="gd-content-heading gd-location-seo-box">
	<h3><?php echo __('Geo Directory Manage SEO', 'geodirlocation') . '&nbsp;&nbsp;&raquo;&nbsp;&nbsp;' . $location_type_label; ?></h3>
	<?php if ( !empty( $data['pagination_top'] ) || !empty( $data['filter_box'] ) ) { ?>
	<br class="clear" />
	<div class="tablenav top">
		<div class="alignleft actions bulkactions gd-seo-bulkactions"><?php if ( !empty( $data['filter_box'] ) ) { echo $data['filter_box']; } ?></div>
		<?php if ( !empty( $data['pagination_top'] ) ) { echo $data['pagination_top']; } ?>
	</div><br class="clear" />
	<?php } ?>
	<table id="geodir_location_seo_settings" style="width:100%" cellpadding="5" class="widefat post fixed gd-seo-tbltype-<?php echo $location_type;?>">
		<thead>
			<tr>
				<th align="left"><strong><?php echo __('Location', 'geodirlocation') . ' <small>(' . $location_label . ')</small>';?></strong></th>
				<?php if ($location_type == 'region' || $location_type == 'city') { ?>
				<?php if ($location_type == 'city') { ?>
				<th align="left"><strong><?php _e('Region', 'geodirlocation');?></strong></th>
				<?php } ?>
				<th align="left"><strong><?php _e('Country', 'geodirlocation');?></strong></th>
				<?php } ?>
				<th align="left" class="gd-seo-metath"><strong><?php _e('Meta Title/Description', 'geodirlocation');?></strong></th>
				<th align="left" class="gd-seo-metath"><strong><?php _e('Location Description', 'geodirlocation');?></strong></th>
				<th align="left" class="gd-seo-metath"><strong><?php _e('Image/Tagline', 'geodirlocation');?></strong></th>
			</tr>
		<?php if ($items) { ?>
		<?php
		$c = 0;
		foreach ($items as $item) {
			$meta_title = '';
			$meta_desc = '';
			$location_desc = '';
			$country_slug = '';
			$region_slug = '';
			$city_slug = '';
			$city_meta = '';
			$location_image_tagline = '';
            $imageID = '';
			
			if ($location_type == 'city') {
				$slug = $item->city_slug;
				$region_slug = $item->region_slug;
				$country_slug = $item->country_slug;
				$location_title = $item->city;
				
				$city_meta = geodir_city_info_by_slug($slug, $country_slug, $region_slug);
			} else if ($location_type == 'region') {
				$slug = $item->region_slug;
				$country_slug = $item->country_slug;
				$location_title = $item->region;
			} else {
				$slug = $item->country_slug;
				$location_title = __($item->country, 'geodirectory');
			}

			$meta_desc_default = $location_title;
			$location_desc_default = $location_title;
			$loc_image_default = $location_title;
			$loc_tagline_default = __('Enter Image Tagline', 'geodirlocation');
			
			$seo = geodir_location_seo_by_slug($slug, $location_type, $country_slug, $region_slug);
			if (!empty($seo)) {
				$meta_title = $seo->seo_meta_title != '' ? $seo->seo_meta_title : $meta_title;
				$meta_desc = $seo->seo_meta_desc != '' ? $seo->seo_meta_desc : $meta_desc;
				$location_desc = $seo->seo_desc != '' ? $seo->seo_desc : $location_desc;
				$location_image_tagline = $seo->seo_image_tagline != '' ? $seo->seo_image_tagline : '';
				$imageID = $seo->seo_image;
			}

			
			if (!empty($city_meta)) {
				if(!$meta_desc){$meta_desc = $city_meta->city_meta;}
				if(!$location_desc ){$location_desc = $city_meta->city_desc;}
			}
		?>
		<tr class="geodir_set_location_seo">
			<td><?php echo $location_title;?><input type="hidden" name="gd_seo[<?php echo $c;?>][location_slug]" value="<?php echo esc_attr($slug);?>" /></td>
			<?php if ($location_type == 'region' || $location_type == 'city') { ?>
			<?php if ($location_type == 'city') { ?>
			<td><?php echo $item->region;?><input type="hidden" name="gd_seo[<?php echo $c;?>][region_slug]" value="<?php echo esc_attr($item->region_slug);?>" /></td>
			<?php } ?>
			<td><?php _e($item->country, 'geodirlocation');?><input type="hidden" name="gd_seo[<?php echo $c;?>][country_slug]" value="<?php echo esc_attr($item->country_slug);?>" /></td>
			<?php } ?>
			<td>
				<input type="text" name="gd_seo[<?php echo $c;?>][meta_title]" class="geodir_meta_title gd-admin-input" placeholder="<?php esc_attr_e($meta_desc_default); echo ' '; _e('Meta Title','geodirlocation');?>" value="<?php echo esc_attr(stripslashes($meta_title)); ?>" />
				<textarea name="gd_seo[<?php echo $c;?>][meta_desc]" class="geodir_meta_keyword" placeholder="<?php esc_attr_e($meta_desc_default);echo ' '; _e('Meta Description','geodirlocation');?>"><?php echo stripslashes($meta_desc); ?></textarea>
			</td>
			<td><textarea name="gd_seo[<?php echo $c;?>][loc_desc]" class="geodir_meta_description" placeholder="<?php esc_attr_e($location_desc_default);?>"><?php echo stripslashes($location_desc); ?></textarea></td>
			<td><?php
				if ($imageID) {
					$imageURL = wp_get_attachment_image_src( $imageID );
					echo '<img src="'.$imageURL[0].'" width="50" />';
//					echo '<br/>';
					$remove_url = wp_nonce_url(admin_url('admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_seo&seo_id='.$seo->seo_id), 'gd_seo_image_remove', 'gd_loc_nonce');
					echo '<a onclick="return confirm(\'Are you sure you want to remove this image?\');" style="display: inline-block; margin-left: 10px;" href="'.$remove_url.'">Remove</a>';
				}
				?>
				<input type="file" name="gd_seo[<?php echo $c;?>]" class="geodir_loc_image" placeholder="<?php esc_attr_e($loc_image_default);?>" />
				<br/>
				<textarea name="gd_seo[<?php echo $c;?>][loc_tagline]" class="geodir_meta_description" placeholder="<?php esc_attr_e($loc_tagline_default);?>"><?php echo stripslashes($location_image_tagline); ?></textarea>
			</td>
		</tr>
		<?php $c++; } } ?>
		</thead>
	</table>
	<?php if ( !empty( $data['pagination'] ) ) { ?>
	<div class="tablenav bottom">
		<div class="alignleft actions bulkactions"></div>
		<?php echo $data['pagination']; ?>
		<br class="clear" />
	</div>
	<?php } ?>
</div>
<p class="submit"><input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodirlocation' ); ?>" /><input type="hidden" name="location_ajax_action" value="geodir_set_location_seo"><?php if (geodir_is_wpml()) { ?>&nbsp;&nbsp;&nbsp;<button id="gd_wpml_register_strings" type="button" class="button-primary" /><span><i style="display:none;margin-right:5px;" class="fa fa-spin fa-refresh"></i><?php _e( 'Merge all strings for WPML translation', 'geodirlocation' ); ?></span></button><?php } ?></p>
<?php if (geodir_is_wpml()) { ?>
<script type="text/javascript">
jQuery(function($) {
    $('#gd_wpml_register_strings').click(function(e) {
        e.preventDefault();
        var $action = $(this);
        var data = {
            action: 'geodir_location_ajax',
            gd_loc_ajax_action: 'wpml_register_strings',
        };
        $.ajax({
            url: geodir_location_all_js_msg.geodir_location_admin_ajax_url,
            data: data,
            type: 'POST',
            cache: false,
            dataType: 'json',
            beforeSend: function(xhr) {
                $('.fa-refresh', $action).show();
                $action.attr('disabled', 'disabled');
            },
            success: function(res, status, xhr) {
                if (typeof res == 'object' && res) {
                    if (res.message) {
                        alert(res.message);
                    }
                    if (res.error) {
                        alert(res.error);
                    }
                }
            }
        }).complete(function(xhr, status) {
            $('.fa-refresh', $action).hide();
            $action.removeAttr('disabled');
        })
    });
});
</script>
<?php } ?>