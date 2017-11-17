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

$rows = get_post_location_countries();
$nonce = wp_create_nonce( 'update_location_translate' );
$post_action = isset( $_POST['action'] ) ? $_POST['action'] : '';
$post_wpnonce = isset( $_POST['update_location_translate'] ) ? $_POST['update_location_translate'] : '';
$post_country_slug = isset( $_POST['country_slug'] ) && !empty( $_POST['country_slug'] ) ? $_POST['country_slug'] : '';
$success = 0;
$submit = false;
if( $post_action == 'update_location_translate' && $post_wpnonce && $post_country_slug && is_array( $post_country_slug ) ) {
	if ( wp_verify_nonce( $post_wpnonce, 'update_location_translate' ) ) {
		foreach( $post_country_slug as $post_slug ) {
			$post_country = get_post_country_by_slug( $post_slug );
			$return = geodir_update_location_translate($post_slug);
			
			if( $return ) {
				$success++;
			}
		}
		$submit = true;
	}
}

$msg = '';
if ( $submit ) {
	if( $success > 0 ) {
		flush_rewrite_rules( false );
		
		$msg = MSG_MANAGE_LOCATION_TRANSLATE_SUCCESS;
	} else {
		$msg = MSG_MANAGE_LOCATION_TRANSLATE_FAIL;
	}
	$rows = get_post_location_countries();
}
?><div class="gd-content-heading">
  <?php if( $msg != '' ) { ?><div class="updated below-h2" id="message"><p><?php echo $msg; ?></p></div><?php } ?>
  <h3><?php echo GD_MANAGE_LOCATION_TRANSLATE; ?></h3>
  <p><?php _e( 'INSTRUCTIONS: Translate the countries you want to via .po file and then upload the .mo file to your server, then tick the countries you translated and click the Update button.', 'geodirectory');?></p>
  <input type="hidden" name="action" value="update_location_translate" />
  <input type="hidden" name="update_location_translate" value="<?php echo $nonce;?>" />
  <table style=" width:100%" cellpadding="5" class="widefat post fixed" id="geodir_location-form-translate" >
    <thead>
      <tr>
        <th width="10"><input type="checkbox" id="country_slug_all" style="margin-left:0;"></th>
        <th width="102" align="left"><strong><?php echo GD_LOCATION_COUNTRY;?></strong></th>
        <th width="102" align="left"><strong><?php echo GD_LOCATION_COUNTRY_URL;?></strong></th>
        <th width="130" align="left"><strong><?php echo GD_LOCATION_COUNTRY_AFTER_TRANSLATION; ?></strong></th>
        <th width="130" align="left"><strong><?php echo GD_LOCATION_COUNTRY_URL_AFTER_TRANSLATION;?></strong></th>
        <th width="55" style="text-align:center"><strong><?php echo GD_LOCATION_TOTAL_LOCATIONS;?></strong></th>
        <th width="55" style="text-align:center"><strong><?php echo GD_LOCATION_TOTAL_LISTINGS;?></strong></th>
      </tr>
    </thead>
    <tbody>
      <?php
if( !empty( $rows ) ) {
	foreach( $rows as $row ) {
		$country = $row->country;
		$country_slug = $row->country_slug;
		
		$translated_country = __( $country, 'geodirectory');
		$translated_country = trim( wp_unslash( $translated_country ) );
		$translated_country_slug = create_location_slug( $translated_country );
		
		$total_locations = (int)$row->total;
		$total_listings = (int)count_listings_by_country( $country, $country_slug, true );
		?>
      <tr>
        <td><input type="checkbox" class="country-slug" name="country_slug[]" id="country-slug" value="<?php echo $country_slug;?>"></td>
        <td><?php echo $country;?></td>
        <td><?php echo $country_slug;?></td>
        <td><?php echo $translated_country;?></td>
        <td><?php echo $translated_country_slug;?></td>
        <td align="center"><?php echo $total_locations;?></td>
        <td align="center"><?php echo $total_listings;?></td>
      </tr>
      <?php
	}
}
?>
    </tbody>
  </table>
  <?php if( !empty( $rows ) ) { ?>
  <span style="padding:10px;display:block;"><b><?php echo GD_LOCATION_NOTE; ?></b> <?php echo MSG_LOCATION_SELECT_LOCATION_TRANSLATE; ?></span>
  <input type="submit" value="<?php echo GD_LOCATION_BTN_UPDATE;?>" class="button-primary" onclick="return geodir_location_translate()" style="margin:0 10px 10px;" />
  <?php } ?>
</div>