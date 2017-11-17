<?php
function geodir_custom_gmaps_get_option_form($tab_name) {
    switch ($tab_name) {
        case 'geodir_custom_gmaps_general_options': {
            geodir_admin_fields( geodir_custom_gmaps_general_options() );
            ?>
<p class="submit">
  <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodir_customgmaps' ); ?>" />
  <input type="hidden" name="subtab" value="geodir_custom_gmaps_general_options" id="last_tab" />
</p>
</div>
        <?php
        }
        break;
    }// end of switch
}

function geodir_custom_gmaps_show_styles_list() {
    $gmap_title = __('Update Google Map Style', 'geodir_customgmaps');
    $osm_title = __('Update OpenStreetMap Style', 'geodir_customgmaps');
    $style_url = 'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles';
?>
<div class="gd-content-heading active">
  <h3>
    <?php _e('Custom Maps Manage Styles', 'geodir_customgmaps');?>
  </h3>
  <table cellpadding="5" class="widefat post fixed" id="gd_cgm_table" >
    <thead>
      <tr>
        <th class="lft" id="gd_cgm_style_name" style="cursor:pointer;"><strong>
          <?php _e('Map Style', 'geodir_customgmaps'); ?>
          </strong></th>
        <th width="120" class="cntr"><strong>
          <?php _e('Google Map', 'geodir_customgmaps'); ?>
          </strong></th>
        <th width="120" class="cntr"><strong>
          <?php _e('OpenStreetMap', 'geodir_customgmaps'); ?>
          </strong></th>
      </tr>
    </thead>
    <tbody>
      <tr class="gd-cgm-odd">
        <th class="lft"><strong><?php _e('Home Page Map Style', 'geodir_customgmaps'); ?></strong></th>
        <th class="cntr">
          <a href="<?php echo admin_url($style_url . '&gd_map=home');?>" title="<?php echo esc_attr($gmap_title); ?>"><i class="fa fa-pencil-square-o"></i></a></th>
        <th class="cntr">
          <a href="<?php echo admin_url($style_url . '&gd_map=home&map=osm');?>" title="<?php echo esc_attr($gmap_title); ?>"><i class="fa fa-pencil-square-o"></i></a></th>
      </tr>
    <tr class="gd-cgm-even">
        <th class="lft"><strong><?php _e('Listing Page Map Style', 'geodir_customgmaps'); ?></strong></th>
        <th class="cntr">
          <a href="<?php echo admin_url($style_url . '&gd_map=listing');?>" title="<?php echo esc_attr($gmap_title); ?>"><i class="fa fa-pencil-square-o"></i></a></th>
        <th class="cntr">
          <a href="<?php echo admin_url($style_url . '&gd_map=listing&map=osm');?>" title="<?php echo esc_attr($gmap_title); ?>"><i class="fa fa-pencil-square-o"></i></a></th>
      </tr>
    <tr class="gd-cgm-odd">
        <th class="lft"><strong><?php _e('Detail Page Map Style', 'geodir_customgmaps'); ?></strong></th>
        <th class="cntr">
          <a href="<?php echo admin_url($style_url . '&gd_map=detail');?>" title="<?php echo esc_attr($gmap_title); ?>"><i class="fa fa-pencil-square-o"></i></a></th>
        <th class="cntr">
          <a href="<?php echo admin_url($style_url . '&gd_map=detail&map=osm');?>" title="<?php echo esc_attr($gmap_title); ?>"><i class="fa fa-pencil-square-o"></i></a></th>
      </tr>
    </tbody>
  </table>
</div>
<?php
}

function geodir_custom_gmaps_add_style_form($gd_map = '') {
    if (!($gd_map == 'home' || $gd_map == 'listing' || $gd_map == 'detail')) {
        wp_redirect(admin_url('admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles'));
        exit;
    }

    $feature_type_options = geodir_custom_gmaps_feature_type_options();
    $element_type_options = geodir_custom_gmaps_element_type_options();
    $styler_options = geodir_custom_gmaps_Styler();

    $saved_option = get_option('geodir_custom_gmaps_style_'.$gd_map);
    if (!empty($saved_option)) {
        $saved_option = geodir_custom_gmaps_object_to_array($saved_option);
    }

    $styler_width = count($styler_options) > 0 ? 100 / count($styler_options) : 100;

    $title = '';
    if ($gd_map=='home') {
        $title = __('Home Page Map', 'geodir_customgmaps');
    } else if ($gd_map=='listing') {
        $title = __('Listing Page Map', 'geodir_customgmaps');
    } else if ($gd_map=='detail') {
        $title = __('Detail Page Map', 'geodir_customgmaps');
    }

    $gd_style = isset($_REQUEST['gd_style']) && $_REQUEST['gd_style'] == 'i' ? 'i' : 'c';
    $textarea_value = !empty($saved_option) && is_array($saved_option) ? json_encode($saved_option) : '';
    ?>
<div class="gd-content-heading active">
  <h3>
    <?php _e('Customize Google Maps Style:', 'geodir_customgmaps');?> <?php echo $title;?>
  </h3>
  <label class="gd-about-styler"><a href="<?php _e('https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyler', 'geodir_customgmaps');?>" target="_blank"><?php _e('Read more about MapTypeStyler Properties.', 'geodir_customgmaps');?></a></label>
  <h3><?php _e('Map Preview:', 'geodir_customgmaps');?></h3>
  <div id="gd_cgm_preview_map" class="geodir_map"></div>
  <div class="clear"></div><input id="gd_custom_style" type="button" class="button-secondary gd-custom-style" data-style="c" value="<?php _e('Custom Styles', 'geodir_customgmaps');?>" />&nbsp;&nbsp;<input id="gd_import_style" type="button" class="button-secondary gd-import-style" data-style="i" value="<?php _e('Import Styles', 'geodir_customgmaps');?>" />
  <input id="gd_preview_style" type="button" class="button-primary gd_preview_style" value="<?php _e('Preview', 'geodir_customgmaps');?>" />
  <div class="clear"></div>
  <input type="hidden" name="custom_gmaps_update_nonce" value="<?php echo wp_create_nonce('custom_gmaps_update'); ?>" />
  <input type="hidden" name="gd_map" value="<?php echo $gd_map;?>" id="gd_map" />
  <input type="hidden" name="gd_style" value="<?php echo $gd_style;?>" id="gd_style" />
  <div id="gd-custom-gmaps-custom" style="<?php echo ($gd_style != 'c' ? 'display:none' : '');?>">
  <h4><?php _e('Custom Styles:', 'geodir_customgmaps');?></h4>
  <table class="form-table gd-custom-gmaps-style-table">
    <tbody>
      <tr valign="top" class="gd-custom-gmaps-attrs">
        <td valign="top" class="gd-custom-gmaps-td">
            <table class="form-table gd-custom-gmaps-table">
                <tr>
                  <td colspan="4" style="width:50%"><?php _e('featureType: ', 'geodir_customgmaps');?><select class="gd-select-medium" id="gd_custom_gmaps_ftype"><?php echo $feature_type_options; ?></select></td>
                  <td colspan="4" style="width:50%"><?php _e('elementType: ', 'geodir_customgmaps');?><select class="gd-select-medium" id="gd_custom_gmaps_etype"><?php echo $element_type_options; ?></select></td>
                </tr>
                <tr class="stylers-label">
                  <td colspan="8"><label><?php _e('stylers:', 'geodir_customgmaps');?></label></td>
                </tr>
                <tr>
  <?php 
    foreach ($styler_options as $styler_option) { 
        $placeholder = $styler_option;
        $class = 'gd-cgm-style gd-styler';
        $extra_attr = '';
        switch ($styler_option) {
            case 'color':
                $placeholder = '#ff0000';
                $extra_attr .= ' type="text" maxlength="7"';
                $class .= ' gd-color-picker';
            break;
            case 'gamma':
                $placeholder = '1.0';
                $extra_attr .= ' type="text" min="0.01" max="10" maxlength="4"';
                $class .= ' rgt';
            break;
            case 'hue':
                $placeholder = '#ff0000';
                $extra_attr .= ' type="text" maxlength="7"';
                $class .= ' gd-color-picker';
            break;
            case 'lightness':
                $placeholder = '-25';
                $extra_attr .= ' type="text" min="-100" max="100" maxlength="4"';
                $class .= ' rgt';
            break;
            case 'saturation':
                $placeholder = '-100';
                $extra_attr .= ' type="text" min="-100" max="100" maxlength="4"';
                $class .= ' rgt';
            break;
            case 'weight':
                $placeholder = '1';
                $extra_attr .= ' type="text" min="0" max="1000" maxlength="3"';
                $class .= ' rgt';
            break;
            case 'visibility':
            break;
            case 'invert_lightness':
            break;
        }
        $extra_attr .= ' class="'.$class.'" placeholder="'.$placeholder.'"';
    ?>
                <td style="width:<?php echo $styler_width;?>%"><label><?php echo $styler_option;?>:</label><div class="clear"></div><?php if ($styler_option=='visibility') { ?><select data-name="<?php echo $styler_option;?>" <?php echo $extra_attr;?>><option value=""><?php _e('default', 'geodir_customgmaps');?></option><option value="on">on</option><option value="off">off</option><option value="simplifed">simplifed</option></select><?php } else if ($styler_option=='invert_lightness') { ?><select data-name="<?php echo $styler_option;?>" <?php echo $extra_attr;?>><option value=""><?php _e('default', 'geodir_customgmaps');?></option><option value="true">true</option></select><?php } else { ?><input value="" data-name="<?php echo $styler_option;?>" <?php echo $extra_attr;?> /><?php } ?></td>
  <?php } ?>
                </tr>
            </table>
        </td>
        <td style="width:30px" class="cntr" valign="middle"><input id="gd_add_style" type="button" class="button-primary" name="gd_add_style" value="<?php _e('Add', 'geodir_customgmaps');?>" /></td>
      </tr>
      <?php $i = 0; if (!empty($saved_option) && is_array($saved_option)) { ?>
      <?php 
      foreach ($saved_option as $option_row) {
        $stylers = isset($option_row['stylers']) ? $option_row['stylers'] : array();
        $saved_stylers = array();
        if (!empty($stylers)) {
            foreach ($stylers as $styler) {
                if (!empty($styler) && is_array($styler)) {
                    foreach ($styler as $stylerF => $stylerV) {
                        $saved_stylers[$stylerF] = $stylerV;
                    }
                }
            }
        }
      ?>
        <tr valign="top" class="gd-style-row">
          <td valign="top" class="gd-custom-gmaps-td"><table class="form-table gd-custom-gmaps-table">
              <tbody>
                <tr>
                  <td style="width:50%" colspan="4"><?php _e('featureType: ', 'geodir_customgmaps');?><font class="cgm-val"><?php echo (isset($option_row['featureType']) ? $option_row['featureType'] : '');?></font>
                    <input type="hidden" value="<?php echo (isset($option_row['featureType']) ? $option_row['featureType'] : '');?>" name="gd_gmap_style[<?php echo $i;?>][featureType]" class="stl-featureType"></td>
                  <td style="width:50%" colspan="4"><?php _e('elementType: ', 'geodir_customgmaps');?><font class="cgm-val"><?php echo (isset($option_row['elementType']) ? $option_row['elementType'] : '');?></font>
                    <input type="hidden" value="<?php echo (isset($option_row['elementType']) ? $option_row['elementType'] : '');?>" name="gd_gmap_style[<?php echo $i;?>][elementType]" class="stl-elementType"></td>
                </tr>
                <tr>
                  <?php foreach ($styler_options as $styler_option) { ?>
                    <td style="<?php echo $styler_width;?>%"><label><?php echo $styler_option;?>:</label><div class="clear"></div><font class="cgm-val"><?php echo (isset($saved_stylers[$styler_option]) ? $saved_stylers[$styler_option] : '');?></font><input type="hidden" value="<?php echo (isset($saved_stylers[$styler_option]) ? $saved_stylers[$styler_option] : '');?>" name="gd_gmap_style[<?php echo $i;?>][stylers][<?php echo $styler_option;?>]" data-name="<?php echo $styler_option;?>" class="stl-styler"></td>
                    <?php } ?>
                </tr>
              </tbody>
            </table></td>
          <td valign="middle" class="cntr"><input type="button" onclick="jQuery(this).closest('.gd-style-row').remove();" id="gd_remove_style" class="button-primary" value="<?php _e('Remove', 'geodir_customgmaps');?>"></td>
        </tr>
      <?php  $i++; } ?>
      <?php } ?>
    </tbody>
    </table>
    </div>
    <div id="gd-custom-gmaps-import" style="<?php echo ($gd_style != 'i' ? 'display:none' : '');?>">
    <h4><?php _e('Import Styles:', 'geodir_customgmaps');?></h4>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <td class="forminp">
                    <label><?php _e('Use the predefined map styles ( JavaScript Style Array ) available at <a href="https://snazzymaps.com/explore" target="_blank">snazzymaps.com/explore</a>. It must be an well defined javascript array format.', 'geodir_customgmaps');?></label>
                </td>
            </tr>
            <tr valign="top">
            <td class="forminp">
                <textarea id="gd_gmap_import" name="gd_gmap_import" placeholder="<?php echo esc_attr('[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}]');?>"><?php echo $textarea_value;?></textarea>
                <span class="description"><?php _e('Paste the predefined map styles ( JavaScript Style Array ) here.', 'geodir_customgmaps');?></span>
            </td>
        </tr></tbody>
    </table>
  </div>
  <p class="submit gd-cgm-actions">
    <input type="hidden" id="gd-cgm-index" value="<?php echo $i;?>" />
    <input type="submit" class="button-primary" onclick="if(!confirm('<?php esc_attr_e('Are you sure?', 'geodir_customgmaps');?>')){return false;}" name="submit" value="<?php $gd_style == 'i' ? _e('Import & Save Styles', 'geodir_customgmaps') : _e('Save Styles', 'geodir_customgmaps');?>" /><input id="gd_preview_style" type="button" class="button-primary gd_preview_style" value="<?php _e('Preview', 'geodir_customgmaps');?>" /><input type="button" class="button-primary" name="gd_cancel" value="<?php _e('Cancel', 'geodir_customgmaps');?>" onclick="window.location.href='<?php echo admin_url()?>admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles'" />
  </p>
  </form>
</div>
<?php
$default_location = geodir_get_default_location();
$default_lng = isset($default_location->city_longitude) ? $default_location->city_longitude : '';
$default_lat = isset($default_location->city_latitude) ? $default_location->city_latitude : '';
$default_lng = $default_lng ? $default_lng : '39.952484';
$default_lat = $default_lat ? $default_lat : '-75.163786';
?>
<script type="text/javascript">
function gd_cgm_activate_color_picker(){
    jQuery('.gd-color-picker').wpColorPicker();
}
    jQuery(document).ready(function($){
        gd_cgm_activate_color_picker();
    });

jQuery(function(){
    jQuery('#gd_add_style').click(function(){
        var ftypeN = 'featureType';
        var etypeN = 'elementType';
        var ftypeV = jQuery('#gd_custom_gmaps_ftype').val();
        ftypeV = ftypeV!='undefined' ? ftypeV : '';
        var etypeV = jQuery('#gd_custom_gmaps_etype').val();
        etypeV = etypeV!='undefined' ? etypeV : '';
        
        var id = parseInt(jQuery('#gd-cgm-index').val());
        var content = '';
        content += '<tr valign="top" class="gd-style-row">';
            content += '<td valign="top" class="gd-custom-gmaps-td">';
                content += '<table class="form-table gd-custom-gmaps-table"><tbody>';
                    content += '<tr>';
                        content += '<td colspan="4" style="width:50%"><?php _e('featureType: ', 'geodir_customgmaps');?><font class="cgm-val">'+ftypeV+'</font><input type="hidden" name="gd_gmap_style['+id+'][featureType]" value="'+ftypeV+'" class="stl-featureType" /></td>';
                        content += '<td colspan="4" style="width:50%"><?php _e('elementType: ', 'geodir_customgmaps');?><font class="cgm-val">'+etypeV+'</font><input type="hidden" name="gd_gmap_style['+id+'][elementType]" value="'+etypeV+'" class="stl-elementType" /></td>';
                    content += '</tr>';
                    content += '<tr>';
                        
        jQuery('.gd-custom-gmaps-table .gd-styler').each(function(){
            var $this = this;
            var styName = jQuery($this).attr('data-name');
            var styVal = jQuery($this).val();
            styVal = styVal!='undefined' ? styVal : '';
                        content += '<td style="<?php echo $styler_width;?>%"><label>'+styName+':</label><div class="clear"></div><font class="cgm-val">'+styVal+'</font><input type="hidden" name="gd_gmap_style['+id+'][stylers]['+styName+']" value="'+styVal+'" data-name="'+styName+'" class="stl-styler" />';
            jQuery($this).val('');
        });
                    content += '</tr>';
                content += '</tbody></table>';
            content += '</td>';
            content += '<td valign="middle" class="cntr"><input type="button" value="<?php _e('Remove', 'geodir_customgmaps');?>" class="button-primary" id="gd_remove_style" onclick="jQuery(this).closest(\'.gd-style-row\').remove();"></td>';
        content += '</tr>';
        
        jQuery('#gd_custom_gmaps_ftype').val('all');
        jQuery('#gd_custom_gmaps_etype').val('');
        jQuery('#gd-cgm-index').val(id+1);
        jQuery('.gd-custom-gmaps-attrs').after(content);
    });
    var myOptions = {
        zoom: 8,
        center: new google.maps.LatLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    jQuery('#gd_cgm_preview_map').goMap(myOptions);
    <?php if (!empty($saved_option) && (is_array($saved_option) || is_object($saved_option))) { ?>
    try {
        var mapStyles = JSON.parse('<?php echo json_encode($saved_option);?>');
        if (typeof mapStyles == 'object' && mapStyles ) {
            jQuery.goMap.map.setOptions({styles: mapStyles});
        }
    }
    catch(err) {
        console.log(err.message);
    }
    <?php } ?>

    jQuery('.gd_preview_style', '.geodir_custom_gmaps_manage_styles').click(function(){
        var myStyles = [];
        var gd_style = jQuery('#gd_style', '.geodir_custom_gmaps_manage_styles').val();
        if (gd_style == 'i') {
            var iStyles = jQuery('#gd_gmap_import', '.geodir_custom_gmaps_manage_styles').val();
            try {
                iStyles = iStyles != '' ? iStyles.trim() : '';
                if (iStyles) {
                    myStyles = JSON.parse(iStyles);
                }
            } catch(err) {
                alert("<?php esc_attr_e('It seems styles has not valid javascript array format! Please use valid javascript array and try again.', 'geodir_customgmaps');?>");
            }
        } else {
            jQuery('.gd-custom-gmaps-style-table .gd-style-row').each(function(){
                var $this = this;
                var fType = jQuery($this).find('.stl-featureType').val();
                var eType = jQuery($this).find('.stl-elementType').val();
                var stylers = [];
                var style = {}; // my object
                var j = 0;
                jQuery($this).find('.stl-styler').each(function(){
                    var $sty = this;
                    var styV = jQuery($sty).val();
                    var style = {};
                    if (typeof styV!='undefined' && styV != '') {
                        var styN = jQuery($sty).attr('data-name');
                        if (style) {
                            style[styN] = styV;
                            stylers[j] = style;
                            j++;
                        }
                    }
                });
                if (typeof fType!='undefined' && fType != '' && stylers && stylers.length) {
                    var myStyle;
                    if (typeof eType!='undefined' && eType != '') {
                        myStyle = {featureType:fType,elementType:eType,stylers:stylers};
                    } else {
                        myStyle = {featureType:fType,stylers:stylers};
                    }
                    myStyles.push(myStyle);
                }
            });
        }
        if (typeof myStyles != 'undefined' && myStyles) {
            try {
                jQuery.goMap.map.setOptions({styles: myStyles});
            }
            catch(err) {
                console.log(err.message);
            }
        }
    });

    jQuery('#gd_custom_style, #gd_import_style').click(function(){
        var gd_style = jQuery(this).attr('data-style');
        gd_style = gd_style && gd_style == 'i' ? 'i' : 'c';
        if (gd_style == 'i') {
            jQuery('#gd_style').val('i');
            jQuery('input[type="submit"]', 'form.geodir_custom_gmaps_manage_styles').val("<?php _e('Import & Save Styles', 'geodir_customgmaps');?>");
            jQuery('#gd-custom-gmaps-custom').hide();
            jQuery('#gd-custom-gmaps-import').show();
        } else {
            jQuery('#gd_style').val('c');
            jQuery('input[type="submit"]', 'form.geodir_custom_gmaps_manage_styles').val("<?php _e('Save Styles', 'geodir_customgmaps');?>");
            jQuery('#gd-custom-gmaps-import').hide();
            jQuery('#gd-custom-gmaps-custom').show();
        }
    });
})
</script>
<?php
}

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_osm_style_form($gd_map = '') {
    if (!($gd_map == 'home' || $gd_map == 'listing' || $gd_map == 'detail')) {
        wp_redirect(admin_url('admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles'));
        exit;
    }
    
    $default_location = geodir_get_default_location();
    $default_lng = !empty($default_location->city_longitude) ? $default_location->city_longitude : '39.952484';
    $default_lat = !empty($default_location->city_latitude) ? $default_location->city_latitude : '-75.163786';

    $providers = geodir_custom_gmaps_osm_providers();
    $base_layers = geodir_custom_gmaps_osm_layer_names('base');
    $overlay_layers = geodir_custom_gmaps_osm_layer_names('overlay');
    
    $title = '';
    if ($gd_map == 'home') {
        $title = __('Home Page Map', 'geodir_customgmaps');
    } else if ($gd_map == 'listing') {
        $title = __('Listing Page Map', 'geodir_customgmaps');
    } else if ($gd_map == 'detail') {
        $title = __('Detail Page Map', 'geodir_customgmaps');
    }
    
    $style = geodir_custom_gmaps_get_osm_style();
    
    $defaultLayer = $style[$gd_map]['baseLayer'];
    $defaultOverlays = $style[$gd_map]['overlays'];
    
    $defaultLayer = !empty($defaultLayer) && in_array($defaultLayer, $base_layers) ? $defaultLayer : $base_layers[0];
    $defaultOverlays = !empty($defaultOverlays) ? json_encode($defaultOverlays) : '[]';
    ?>
    <div class="gd-content-heading active">
        <h3><?php _e('Customize OpenStreetMap Style:', 'geodir_customgmaps');?> <?php echo $title;?></h3>
        <h4><?php _e('Map Preview:', 'geodir_customgmaps');?></h4>
        <div id="gd_osm_map" class="geodir_map"></div>
        <div class="clear"></div>
        <input type="hidden" name="custom_gmaps_update_nonce" value="<?php echo wp_create_nonce('custom_gmaps_update'); ?>" />
        <input type="hidden" name="gd_map" value="<?php echo $gd_map;?>" id="gd_map" />
        <input type="hidden" name="map" value="osm" />
        <p class="submit gd-cgm-actions">
            <input type="hidden" id="gd-osm-base" name="gd_osm_base" value="<?php echo $defaultLayer;?>" />
            <input type="submit" class="button-primary" name="submit" value="<?php _e('Save Styles', 'geodir_customgmaps');?>" />
            <input type="button" class="button-primary" name="gd_cancel" value="<?php _e('Cancel', 'geodir_customgmaps');?>" onclick="window.location.href='<?php echo admin_url('admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles');?>" />
        </p>
<script type="text/javascript">
var defaultLayer = '<?php echo $defaultLayer;?>';
var defaultOverlays = <?php echo $defaultOverlays; ?>;
(function () {
    'use strict';
    
    L.TileLayer.Provider.providers = <?php echo json_encode($providers); ?>
    
    var map = new L.Map('gd_osm_map', {
        center: new L.LatLng('<?php echo $default_lat;?>', '<?php echo $default_lng;?>'), 
        zoom: 5
    });
    
    function escapeHtml(string) {
        return string
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderValue(value) {
        if (typeof value === 'string') {
            return "'" + escapeHtml(value) + "'";
        } else {
            return JSON.stringify(value).replace(/,/g, ', ');
        }
    }

    L.TileLayer.include({
        getExampleJS: function() {
            var layerName = this._providerName.replace('.', '_');

            var url = this._exampleUrl || this._url;
            var options = L.extend({}, this._options, this._exampleAPIcodes || {});

            // replace {variant} in urls with the selected variant, since
            // keeping it in the options map doesn't make sense for one layer
            if (options.variant) {
                url = url.replace('{variant}', options.variant);
                delete options.variant;
            }

            var code = '';
            if (url.indexOf('//') === 0) {
                code += '// https: also suppported.\n';
                url = 'http:' + url;
            }
            code += 'var ' + layerName + ' = L.tileLayer(\'' + url + '\', {\n';

            var first = true;
            for (var option in options) {
                if (first) {
                    first = false;
                } else {
                    code += ',\n';
                }
                code += '\t' + option + ': ' + renderValue(options[option]);
            }
            code += '\n});\n';

            return code;
        }
    });

    var isOverlay = function(providerName, layer) {
        if (layer.options.opacity && layer.options.opacity < 1) {
            return true;
        }
        var overlayPatterns = [
            '^(OpenWeatherMap|OpenSeaMap)',
            'OpenMapSurfer.AdminBounds',
            'Stamen.Toner(Hybrid|Lines|Labels)',
            'Acetate.(foreground|labels|roads)',
            'Hydda.RoadsAndLabels'
        ];

        return providerName.match('(' + overlayPatterns.join('|') + ')') !== null;
    };

    // Ignore some providers in the preview
    var isIgnored = function(providerName) {
        if (providerName === 'ignored') {
            return true;
        }
        // reduce the number of layers previewed for some providers
        if (providerName.startsWith('HERE') || providerName.startsWith('OpenWeatherMap')) {
            var whitelist = [
                'HERE.normalDay',
                'HERE.basicMap',
                'HERE.hybridDay',
                'OpenWeatherMap.Clouds',
                'OpenWeatherMap.Pressure',
                'OpenWeatherMap.Wind'
            ];
            return whitelist.indexOf(providerName) === -1;
        }
        return false;
    };

    // collect all layers available in the provider definition
    var baseLayers = {};
    var overlays = {};
    var currentOvs = {};

    var addLayer = function(name) {
        if (isIgnored(name)) {
            return;
        }
        var layer = L.tileLayer.provider(name);
        
        if (isOverlay(name, layer)) {
            var label = '<font class="_gdmc" data-value="' + layer._providerName + '" />' + name + '</font>';
            if (defaultOverlays.length > 0 && jQuery.inArray(name, defaultOverlays) !== -1) {
                currentOvs[label] = layer;
            }
            overlays[label] = layer;
        } else {
            baseLayers[name] = layer;
        }
    };
    L.tileLayer.provider.eachLayer(addLayer);

    // add minimap control to the map
    var layersControl = L.control.layers(baseLayers, overlays, {collapsed: false}).addTo(map);

    // add OpenStreetMap.Mapnik, or the first if it does not exist
    if (baseLayers[defaultLayer]) {
        baseLayers[defaultLayer].addTo(map);
        jQuery('#gd-osm-base').val(defaultLayer);
    } else {
        baseLayers[Object.keys(baseLayers)[0]].addTo(map);
        jQuery('#gd-osm-base').val(Object.keys(baseLayers)[0]);
    }
    
    for (var currentOv in currentOvs) {
        gdAddOverlayValue(currentOvs[currentOv]._providerName);
        currentOvs[currentOv].addTo(map);
    }

    // if a layer is selected and if it has bounds an the bounds are not in the
    // current view, move the map view to contain the bounds
    map.on('baselayerchange', function(e) {
        var layer = e.layer;
        if (!map.hasLayer(layer)) {
            return;
        }

        jQuery('#gd-osm-base').val(layer._providerName);
        
        if (layer.options.minZoom > 1 && map.getZoom() > layer.options.minZoom) {
            map.setZoom(layer.options.minZoom);
        }
        if (!layer.options.bounds) {
            return;
        }
        var bounds = L.latLngBounds(layer.options.bounds);
        map.fitBounds(bounds, {
            paddingTopLeft: [0, 200],
            paddingBottomRight: [200, 0]
        });
    });
    
    map.on("overlayadd", function(e) {
        gdAddOverlayValue(e.layer._providerName);
    });
    
    map.on("overlayremove", function(e) {
        gdRemoveOverlayValue(e.layer._providerName);
    });
    
    function gdAddOverlayValue(name) {
        gdRemoveOverlayValue(name);
        
        jQuery('.geodir_custom_gmaps_manage_styles .gd-cgm-actions').append('<input type="hidden" value="' + name + '" name="gd_osm_overlays[]" />');
    }
    
    function gdRemoveOverlayValue(name) {
        jQuery('.geodir_custom_gmaps_manage_styles .gd-cgm-actions').find('[value="' + name + '"]').remove();
    }
    
    // resize layers control to fit into view.
    function resizeLayerControl () {
        var layerControlHeight = parseInt(jQuery('#gd_osm_map').height()) - (10 + 50);
        var layerControl = document.getElementsByClassName('leaflet-control-layers-expanded')[0];

        layerControl.style.overflowY = 'auto';
        layerControl.style.maxHeight = layerControlHeight + 'px';
    }
    map.on('resize', resizeLayerControl);
    resizeLayerControl();
})();
</script>
    </div>
</form>
<?php
}