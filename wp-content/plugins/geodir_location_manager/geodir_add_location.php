<?php
/**
 * Contains add location page template.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */

$location_id = !empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : '';
$hood_id = !empty($_REQUEST['hood_id']) ? (int)$_REQUEST['hood_id'] : '';

$location_result = geodir_get_location_by_id('', $location_id);
$hood_result = geodir_location_get_neighbourhood_by_id($hood_id);

$prefix = 'gd_';

$lat = !empty($location_result->city_latitude) ? stripslashes($location_result->city_latitude) : '';
$lng = !empty($location_result->city_longitude) ? stripslashes($location_result->city_longitude) : '';
$city = !empty($location_result->city) ? esc_attr(stripslashes($location_result->city)) : '';
$region = !empty($location_result->region) ? esc_attr(stripslashes($location_result->region)) : '';
$country = !empty($location_result->country) ? stripslashes($location_result->country) : '';
$city_meta = !empty($location_result->city_meta) ? stripslashes($location_result->city_meta) : '';
$city_desc = !empty($location_result->city_desc) ? stripslashes($location_result->city_desc) : '';

$hood_name = !empty($hood_result->hood_name) ? esc_attr(stripslashes($hood_result->hood_name)) : '';
$hood_meta_title = !empty($hood_result->hood_meta_title) ? esc_attr(stripslashes($hood_result->hood_meta_title)) : $hood_name;
$hood_slug = !empty($hood_result->hood_slug) ? $hood_result->hood_slug : '';

$map_title = GD_LOCATION_SET_MAP;
if (isset($_REQUEST['add_hood'])) {
    $map_title = GD_LOCATION_NEIGHBOURHOOD_SET_ON_MAP;
    
    if (!empty($hood_result)) {
        $lat = stripslashes($hood_result->hood_latitude);
        $lng = stripslashes($hood_result->hood_longitude);
        $city_meta = isset($hood_result->hood_meta) ? stripslashes($hood_result->hood_meta) : '';
        $city_desc = isset($hood_result->hood_description) ? stripslashes($hood_result->hood_description) : '';
    }
}

$nonce = wp_create_nonce( 'location_add_edit_nonce' );
?>
<div class="gd-content-heading">
    <h3><?php echo GD_LOCATION_ADD_LOCATION; ?></h3>
    <?php if (isset($_REQUEST['add_hood'])) { ?>
    <input type="hidden" name="location_ajax_action" value="add_hood">
    <input type="hidden" name="update_hood" value="<?php echo $hood_id; ?>">
    <input type="hidden" name="hood_slug" value="<?php echo $hood_slug; ?>">
    <?php } else { ?>
    <input type="hidden" name="location_ajax_action" value="location">
    <?php } ?>
    <input type="hidden" name="location_addedit_nonce" value="<?php echo $nonce;?>" />
    <input type="hidden" name="update_city" value="<?php echo $location_id; ?>">
    <table class="form-table geodir_add_location_form" id="gd_option_form">
        <tbody>
            <?php if (isset($_REQUEST['add_hood'])) { ?>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php echo GD_HOOD_NAME;?><span style="display:inline; color:#FF0000;">*</span></th>
                <td class="forminp">
                    <div class="gtd-formfeild required">
                        <input type="text" size="80" style="width:440px" id="hood_name" name="hood_name" value="<?php echo $hood_name; ?>" />
                        <div class="gd-location_message_error"> <?php echo GD_LOCATION_FIELD_REQ;?></div>
                    </div>
                    <span class="description"></span>
                </td>
            </tr>
            <?php } ?>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php echo GD_LOCATION_CITY;?><span style="display:inline; color:#FF0000;">*</span></th>
                <td class="forminp">
                    <div class="gtd-formfeild required">
                        <input type="text" size="80" style="width:440px" id="<?php echo $prefix;?>city" name="<?php echo $prefix;?>city" value="<?php echo $city; ?>" />
                        <div class="gd-location_message_error"> <?php echo GD_LOCATION_FIELD_REQ;?></div>
                    </div>
                    <span class="description"></span>        
                </td>
            </tr>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php echo GD_LOCATION_REGION;?><span style="display:inline; color:#FF0000;">*</span></th>
                <td class="forminp">
                    <div class="gtd-formfeild required">
                        <input type="text" id="<?php echo $prefix;?>region" size="80" style="width:440px" name="<?php echo $prefix;?>region" value="<?php echo $region; ?>" />
                        <div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
                    </div>
                    <span class="description"></span>        
                </td>
            </tr>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php echo GD_LOCATION_COUNTRY;?><span style="display:inline; color:#FF0000;">*</span></th>
                <td class="forminp">
                    <div class="gtd-formfeild required">
                        <select id="<?php echo $prefix ?>country" class="chosen_select"data-location_type="country" name="<?php echo $prefix ?>country"  data-placeholder="<?php esc_attr_e('Choose a country.', 'geodirlocation') ;?>" data-addsearchtermonnorecord="1" data-ajaxchosen="0" data-autoredirect="0" data-showeverywhere="0" >
                        <?php geodir_get_country_dl($country, $prefix); ?> 
                        <div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
                    </div>
                    <span class="description"></span>        
                </td>
            </tr>
            <tr valign="top" class="single_select_page  gd-add-location-map">
                <th class="titledesc" scope="row">&nbsp;</th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <?php include(geodir_plugin_path() . "/geodirectory-functions/map-functions/map_on_add_listing_page.php"); ?>
                    </div>
                </td>
            </tr>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row">
                <?php 
                    if (isset($_REQUEST['add_hood'])) {
                        _e('Neighbourhood Latitude', 'geodirlocation');
                    } else {
                        echo GD_LOCATION_LATITUDE;
                    }
                    ?>
                    <span style="display:inline; color:#FF0000;">*</span>
                </th>
                <td class="forminp">
                    <div class="gtd-formfeild required">
                        <input type="text" size="80" style="width:440px" id="<?php echo $prefix;?>latitude" name="<?php echo $prefix;?>latitude" value="<?php echo $lat; ?>" />
                    </div>
                    <div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
                </td>
            </tr>
        <tr valign="top" class="single_select_page">
            <th class="titledesc" scope="row">
                <?php 
                if (isset($_REQUEST['add_hood'])) {
                    _e('Neighbourhood Longitude', 'geodirlocation');
                } else {
                    echo GD_LOCATION_LONGITUDE;
                }
                ?>
                <span style="display:inline; color:#FF0000;">*</span>
            </th>
            <td class="forminp">
                <div class="gtd-formfeild required">
                    <input type="text"  size="80" style="width:440px" id="<?php echo $prefix;?>longitude" name="<?php echo $prefix;?>longitude" value="<?php echo $lng; ?>" />
                </div>
                <div class="gd-location_message_error"><?php echo GD_LOCATION_FIELD_REQ;?></div>
            </td>
            </tr>
            <?php if (isset($_REQUEST['add_hood'])) { ?>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php _e('Neighbourhood Meta Title', 'geodirlocation');?><span style="display:inline; color:#FF0000;"></span></th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <input type="text" size="80" style="width:440px" id="hood_meta_title" name="hood_meta_title" value="<?php echo $hood_meta_title; ?>" />
                        <div class="gd-location_message_error"></div>
                    </div>
                    <span class="description"></span>
                </td>
            </tr>
            <?php } ?>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php echo (isset($_REQUEST['add_hood']) ? _e('Neighbourhood Meta', 'geodirlocation') : GD_LOCATION_CITY_META);?></th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <textarea style="width:440px;" name="city_meta"><?php echo $city_meta; ?></textarea>
                    </div>
                </td>
            </tr>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php echo (isset($_REQUEST['add_hood']) ? _e('Neighbourhood Description', 'geodirlocation') : GD_LOCATION_CITY_DESC);?></th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <textarea style="width:440px;" name="city_desc"><?php echo $city_desc; ?></textarea>
                    </div>
                </td>
            </tr>
            <?php if (isset($_REQUEST['id']) && $_REQUEST['id'] != '' && !isset($_REQUEST['add_hood'])) { ?>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php _e('Action For Listing', 'geodirlocation');?></th>
                <td class="forminp">
                    <div class="gtd-formfeild" style="padding-top:10px;">
                        <input style="display:none;" type="radio" name="listing_action" checked="checked" value="delete" /> 
                        <label><?php _e('Post will be updated if both city and map marker position has been changed.','geodirlocation');?></label>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <p class="submit" style="margin-top:10px;padding-left:12px;">
        <input id="geodir_location_save" class="button-primary" type="submit" name="submit" value="<?php echo esc_attr(GD_LOCATION_SAVE);?>">
    </p>
</div>