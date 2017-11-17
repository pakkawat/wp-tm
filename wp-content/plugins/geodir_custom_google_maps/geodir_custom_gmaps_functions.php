<?php

/**
 * Loads the CSS and JS in the backend needed to run the plugin.
 */
function geodir_custom_gmaps_admin_css(){
	global $pagenow;
	
	if ($pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'custom_gmaps_manager') {
		// Style
		wp_register_style('geodir-custom-gmaps-plugin-style', plugins_url('',__FILE__).'/css/geodir-custom-gmaps-manager.css');
		wp_enqueue_style('geodir-custom-gmaps-plugin-style');

        //add color picker scripts
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker');
	}
}

function geodir_custom_google_maps_plugin_activated($plugin) {
	if (!get_option('geodir_installed'))  {
		$file = plugin_basename(__FILE__);
		
		if ($file == $plugin) {
			$all_active_plugins = get_option( 'active_plugins', array() );
			
			if (!empty($all_active_plugins) && is_array($all_active_plugins)) {
				foreach ($all_active_plugins as $key => $plugin) {
					if ($plugin ==$file) {
						unset($all_active_plugins[$key]);
					}
				}
			}
			update_option('active_plugins', $all_active_plugins);
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir_customgmaps'));
	}
}

function geodir_custom_gmaps_activation(){
	if (get_option('geodir_installed')) {
		$options = geodir_resave_settings(geodir_custom_gmaps_general_options());
		geodir_update_options($options, true);
		
		add_option('geodir_custom_gmaps_activation_redirect_opt', 1);
	}
}

function geodir_custom_gmaps_deactivation() {
}

function geodir_custom_gmaps_activation_redirect(){
	if (get_option('geodir_custom_gmaps_activation_redirect_opt', false)) {
		delete_option('geodir_custom_gmaps_activation_redirect_opt');
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_general_options')); 
	}
}

function geodir_custom_gmaps_current_subtab($default='') {
	$subtab = isset($_REQUEST['subtab']) ? $_REQUEST['subtab'] : '';
	
	if ($subtab=='' && $default!='') {
		$subtab = $default;
	}
	
	return $subtab;
}

// This function is used to create geodirteory custom maps manager navigation
function geodir_custom_gmaps_tabs_array($tabs) {
	$custom_gmaps_tabs = array();
	$custom_gmaps_tabs['label'] = __('Custom Google Maps', 'geodir_customgmaps');
	$custom_gmaps_tabs['subtabs'] = array(
										array(
											'subtab' => 'geodir_custom_gmaps_general_options',
											'label' => __( 'General', 'geodir_customgmaps'),
											'form_action' => admin_url('admin-ajax.php?action=geodir_custom_gmaps_manager_ajax')
										),
										array(
											'subtab' => 'geodir_custom_gmaps_manage_styles',
											'label' => __( 'Manage Styles', 'geodir_customgmaps'),
											'form_action' => admin_url('admin-ajax.php?action=geodir_custom_gmaps_manager_ajax')
										)
									);
	// hook for custom map tabs
	$custom_gmaps_tabs = apply_filters('geodir_custom_gmaps_tabs', $custom_gmaps_tabs);
	
	$tabs['custom_gmaps_manager'] = $custom_gmaps_tabs;
	return $tabs;
}

function geodir_custom_gmaps_general_options( $options = array() ) {
	$options[] = array('name' => __('General Options', 'geodir_customgmaps'), 'type' => 'no_tabs', 'desc' => '', 'id' => 'gmaps_general_options');
	$options[] = array('name' => __('General Settings', 'geodir_customgmaps'), 'type' => 'sectionstart', 'id' => 'custom_gmaps_settings');
	
	$options[] = array(  
		'name' => __('Enable custom style on home page map?', 'geodir_customgmaps'),
		'desc' => __('Enable custom style on home page map.', 'geodir_customgmaps'),
		'id' => 'geodir_custom_gmaps_home',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array(  
		'name' => __('Enable custom style on listing page map?', 'geodir_customgmaps'),
		'desc' => __('Enable custom style on listing page map.', 'geodir_customgmaps'),
		'id' => 'geodir_custom_gmaps_listing',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array(  
		'name' => __('Enable custom style on detail page map?', 'geodir_customgmaps'),
		'desc' => __('Enable custom style on detail page map.', 'geodir_customgmaps'),
		'id' => 'geodir_custom_gmaps_detail',
		'std' => '0',
		'type' => 'checkbox',
		'checkboxgroup'	=> 'start'
	);
	$options[] = array( 'type' => 'sectionend', 'id' => 'custom_gmaps_settings');
	
	// hook for custom map general options
	$options = apply_filters('geodir_custom_gmaps_general_options', $options);
	
	return $options;
}

function geodir_custom_gmaps_option_form($current_tab) {
	$current_tab = geodir_custom_gmaps_current_subtab();
	geodir_custom_gmaps_get_option_form($current_tab);
}

function geodir_custom_gmaps_manager_tab_content() {
	global $wpdb;
	
	$subtab = geodir_custom_gmaps_current_subtab();
	
	if ($subtab == 'geodir_custom_gmaps_general_options') {
		add_action('geodir_admin_option_form', 'geodir_custom_gmaps_option_form');
	} else if ($subtab == 'geodir_custom_gmaps_manage_styles') {
		$gd_map = isset($_REQUEST['gd_map']) ? sanitize_text_field($_REQUEST['gd_map']) : '';
		$map = isset($_REQUEST['map']) ? sanitize_text_field($_REQUEST['map']) : '';
		
		if ($gd_map=='home' || $gd_map=='listing' || $gd_map=='detail') {
			if ($map == 'osm') {
				geodir_custom_gmaps_osm_style_form($gd_map);
			} else {
				geodir_custom_gmaps_add_style_form($gd_map);
			}
		} else {
			geodir_custom_gmaps_show_styles_list();
		}
	}
}

// main ajax function
function geodir_custom_gmaps_manager_ajax() {
	$subtab = geodir_custom_gmaps_current_subtab();
	
	if (isset($_POST['custom_gmaps_update_nonce']) && isset($_POST['gd_map'])) {
		if (!empty($_POST['map']) && $_POST['map'] == 'osm') {
			$msg = geodir_custom_gmaps_update_osm_style();
		} else {
			$msg = geodir_custom_gmaps_update_style();
		}

		$redirect = 'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles';
		$redirect .= '&gd_map=' . sanitize_text_field($_POST['gd_map']);

		if (!empty($_POST['gd_map']) && !empty($_POST['gd_style'])) {
			$redirect .= '&gd_style=' . sanitize_text_field($_POST['gd_style']);
		}
        
		if (!empty($_POST['map']) && !empty($_POST['map'])) {
			$redirect .= '&map=' . sanitize_text_field($_POST['map']);
		}
        
		$redirect .= '&success_msg=' . urlencode_deep($msg);

		wp_redirect(admin_url($redirect));
		exit;
	}
	
	if ($subtab == 'geodir_custom_gmaps_general_options') {
		geodir_update_options(geodir_custom_gmaps_general_options());
		
		$msg = __('Settings saved.', 'geodir_customgmaps');
		$msg = urlencode_deep($msg);
		
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_general_options&success_msg=' . $msg));
		exit;
	}
}

function geodir_custom_gmaps_update_style() {
	$msg = __('Map style not saved, please try again!', 'geodir_customgmaps');
	if (current_user_can('manage_options') && isset($_POST['custom_gmaps_update_nonce'])) {
		$gd_map = isset($_POST['gd_map']) ? trim($_POST['gd_map']) : '';
		$gd_style = isset($_POST['gd_style']) && $_POST['gd_style'] == 'i' ? 'i' : 'c';
		$gd_gmap_style = isset($_POST['gd_gmap_style']) ? $_POST['gd_gmap_style'] : '';
		
		if ($gd_style == 'i') {
			$gd_gmap_style = isset($_POST['gd_gmap_import']) ? $_POST['gd_gmap_import'] : '';
			$gd_gmap_style = wp_strip_all_tags($gd_gmap_style, true);
			$gd_gmap_style = stripslashes_deep($gd_gmap_style);
			$gd_gmap_style = str_replace(" ", "", $gd_gmap_style);
			$gd_gmap_style = json_decode($gd_gmap_style, true);
		}
		
		if (empty($gd_gmap_style)) {
			$msg = __('Map style not saved, please add atleast one style!', 'geodir_customgmaps');
			//wp_redirect(admin_url().'admin.php?page=geodirectory&tab=custom_gmaps_manager&subtab=geodir_custom_gmaps_manage_styles&gd_map='.$gd_map);
			//exit;
		}
		
		if (wp_verify_nonce($_POST['custom_gmaps_update_nonce'], 'custom_gmaps_update')) {
			$save_params = array();
			
			if ($gd_style == 'i') {
				$save_params = $gd_gmap_style;
			} else {
				foreach ($gd_gmap_style as $index => $row) {
					$featureType = isset($row['featureType']) && $row['featureType'] != '' ? $row['featureType'] : '';
					$elementType = isset($row['elementType']) && $row['elementType'] != '' ? $row['elementType'] : '';
					$stylers = isset($row['stylers']) && !empty($row['stylers']) != '' ? $row['stylers'] : '';
					
					$parse_stylers = array();
					foreach ($stylers as $styler => $value) {
						if ($value!='' && strlen($value) > 0) {
							$parse_stylers[][$styler] = $value;
						}
					}
					if ($featureType != '' && !empty($parse_stylers)) {
						$save_param = array();
						$save_param['featureType'] = $featureType;
						if ($elementType!='') {
							$save_param['elementType'] = $elementType;
						}
						$save_param['stylers'] = $parse_stylers;
						$save_params[] = $save_param;
					}
				}
			}

			if (empty($save_params)) {
				$msg = __('Map style not saved, please choose atleast one styler!', 'geodir_customgmaps');
				//return $msg;
			}
			
			$return = false;
			switch($gd_map) {
				case 'home': {
					$option_value = get_option('geodir_custom_gmaps_style_home');
					// hook
					$save_params = apply_filters('geodir_custom_gmaps_save_style_home', $save_params);
					update_option('geodir_custom_gmaps_style_home', $save_params);
					$return = true;
				}
				break;
				case 'listing': {
					$option_value = get_option('geodir_custom_gmaps_style_listing');
					// hook
					$save_params = apply_filters('geodir_custom_gmaps_save_style_listing', $save_params);
					update_option('geodir_custom_gmaps_style_listing', $save_params);
					$return = true;
				}
				break;
				case 'detail': {
					$option_value = get_option('geodir_custom_gmaps_style_detail');
					// hook
					$save_params = apply_filters('geodir_custom_gmaps_save_style_detail', $save_params);
					update_option('geodir_custom_gmaps_style_detail', $save_params);
					$return = true;
				}
				break;
			}

			if ($return) {
				$msg = __('Map style saved.', 'geodir_customgmaps');
			}
		}
	}
	return $msg;
}

function geodir_custom_gmaps_update_osm_style() {
	$msg = __('Map style not saved, please try again!', 'geodir_customgmaps');
	
	if (current_user_can('manage_options') && isset($_POST['custom_gmaps_update_nonce'])) {
		$gd_map = isset($_POST['gd_map']) ? sanitize_text_field($_POST['gd_map']) : '';
		$baseLayer = !empty($_POST['gd_osm_base']) ? sanitize_text_field($_POST['gd_osm_base']) : '';
		$overlays = !empty($_POST['gd_osm_overlays']) ? $_POST['gd_osm_overlays'] : array();
		
		if (wp_verify_nonce($_POST['custom_gmaps_update_nonce'], 'custom_gmaps_update')) {
			$defaults = geodir_custom_gmaps_get_osm_style();
			
			if (!empty($baseLayer)) {
				$defaults[$gd_map]['baseLayer'] = $baseLayer;
			}
			
			$defaults[$gd_map]['overlays'] = $overlays;
			
			update_option('geodir_custom_gmaps_osm_style', $defaults);
			
			$msg = __('Map style saved.', 'geodir_customgmaps');
		}
	}
	
	return $msg;
}

function geodir_custom_gmaps_StyleFeatureType() {
	// more info - https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyleFeatureType
	$options = array('all', 'administrative', 'administrative.country', 'administrative.land_parcel', 'administrative.locality', 'administrative.neighborhood', 'administrative.province', 'landscape', 'landscape.man_made', 'landscape.natural', 'landscape.natural.landcover', 'landscape.natural.terrain', 'poi', 'poi.attraction', 'poi.business', 'poi.government', 'poi.medical', 'poi.park', 'poi.place_of_worship', 'poi.school', 'poi.sports_complex', 'road', 'road.arterial', 'road.highway', 'road.highway.controlled_access', 'road.local', 'transit', 'transit.line', 'transit.station', 'transit.station.airport', 'transit.station.bus', 'transit.station.rail', 'water', 'administrative');
	
	// hook for feature type
	$options = apply_filters('geodir_custom_gmaps_StyleFeatureType', $options);
	
	$options = is_array($options) && !empty($options) ? array_unique($options) : $options;
	
	return $options;
}

function geodir_custom_gmaps_StyleElementType() {
	// more info - https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyleFeatureType
	$options = array('all', 'geometry', 'geometry.fill', 'geometry.stroke', 'labels', 'labels.icon', 'labels.text', 'labels.text.fill', 'labels.text.stroke');
	
	// hook for element type
	$options = apply_filters('geodir_custom_gmaps_StyleElementType', $options);
	
	$options = is_array($options) && !empty($options) ? array_unique($options) : $options;
	
	return $options;
}

function geodir_custom_gmaps_Styler() {
	// more info - https://developers.google.com/maps/documentation/javascript/reference?csw=1#MapTypeStyler
	$options = array('color', 'gamma', 'hue', 'invert_lightness', 'lightness', 'saturation', 'visibility', 'weight');
	
	// hook for styler
	$options = apply_filters('geodir_custom_gmaps_Styler', $options);
	
	$options = is_array($options) && !empty($options) ? array_unique($options) : $options;
	
	return $options;
}

function geodir_custom_gmaps_feature_type_options($value='', $select=false) {
	$return = $select ? '<option value="">'.__('Select', 'geodir_customgmaps').'</option>' : '';
	
	$feature_types = geodir_custom_gmaps_StyleFeatureType();
	
	if (!empty($feature_types)) {
		foreach ($feature_types as $feature_type) {
			$selected = $feature_type == $value ? 'selected="selected"' : '';
			$return .= '<option value="'.$feature_type.'" '.$selected.'>'.$feature_type.'</option>';
		}
	}
	
	return $return;
}

function geodir_custom_gmaps_element_type_options($value='', $select=true) {
	$return = $select ? '<option value="">'.__('Select', 'geodir_customgmaps').'</option>' : '';
	
	$element_types = geodir_custom_gmaps_StyleElementType();
	
	if (!empty($element_types)) {
		foreach ($element_types as $element_type) {
			$selected = $element_type == $value ? 'selected="selected"' : '';
			$return .= '<option value="'.$element_type.'" '.$selected.'>'.$element_type.'</option>';
		}
	}
	
	return $return;
}

function geodir_custom_gmaps_init_map_style() {
	// filter for home map options
	if (get_option('geodir_custom_gmaps_home')) {
		$map_widgets = get_option('widget_geodir_map_v3_home_map');
		
		if (!empty($map_widgets)) {
			add_filter('geodir_map_options_gd_home_map', 'geodir_custom_gmaps_home_map_options', 10, 1); // Custom stype for shortcoded home page map.
			
			foreach ($map_widgets as $key => $value) {
				add_filter('geodir_map_options_geodir_map_v3_home_map_'.(int)$key, 'geodir_custom_gmaps_home_map_options', 10, 1);
			}
		}
	}
	
	// filter for listing map options
	if (get_option('geodir_custom_gmaps_listing')) {
		$map_widgets = get_option('widget_geodir_map_v3_listing_map');
		
		if (!empty($map_widgets)) {
			add_filter('geodir_map_options_gd_listing_map', 'geodir_custom_gmaps_listing_map_options', 10, 1); // Custom stype for shortcoded listing page map.
			
			foreach ($map_widgets as $key => $value) {
				add_filter('geodir_map_options_geodir_map_v3_listing_map_'.(int)$key, 'geodir_custom_gmaps_listing_map_options', 10, 1);
			}
		}
	}
	
	// filter for detail map options
	if (get_option('geodir_custom_gmaps_detail')) {
		add_filter('geodir_map_options_detail_page_map_canvas', 'geodir_custom_gmaps_detail_map_options', 10, 1);
	}
}

function geodir_custom_gmaps_home_map_options($map_options) {
	$style_option = get_option('geodir_custom_gmaps_style_home');
	
	if (!empty($style_option) && (is_array($style_option) || is_object($style_option))) {
		$map_options['mapStyles'] = json_encode($style_option);
	}
    
	// OpenStreetMAp style
	if (in_array(geodir_map_name(), array('auto', 'osm'))) {
		$osm_style = geodir_custom_gmaps_get_osm_style();
        
		$map_options['osmBaseLayer'] = !empty($osm_style['home']['baseLayer']) ? $osm_style['home']['baseLayer'] : 'OpenStreetMap.Mapnik';
		$map_options['osmOverlays'] = !empty($osm_style['home']['overlays']) ? $osm_style['home']['overlays'] : '[]';
	}
    
	return $map_options;
}

function geodir_custom_gmaps_listing_map_options($map_options) {
	$style_option = get_option('geodir_custom_gmaps_style_listing');
	
	if (!empty($style_option) && (is_array($style_option) || is_object($style_option))) {
		$map_options['mapStyles'] = json_encode($style_option);
	}
    
	// OpenStreetMAp style
	if (in_array(geodir_map_name(), array('auto', 'osm'))) {
		$osm_style = geodir_custom_gmaps_get_osm_style();
        
		$map_options['osmBaseLayer'] = !empty($osm_style['listing']['baseLayer']) ? $osm_style['listing']['baseLayer'] : 'OpenStreetMap.Mapnik';
		$map_options['osmOverlays'] = !empty($osm_style['listing']['overlays']) ? $osm_style['listing']['overlays'] : '[]';
	}
    
	return $map_options;
}

function geodir_custom_gmaps_detail_map_options($map_options) {
	$style_option = get_option('geodir_custom_gmaps_style_detail');
	
	if (!empty($style_option) && (is_array($style_option) || is_object($style_option))) {
		$map_options['mapStyles'] = json_encode($style_option);
	}
    
	// OpenStreetMAp style
	if (in_array(geodir_map_name(), array('auto', 'osm'))) {
		$osm_style = geodir_custom_gmaps_get_osm_style();
        
		$map_options['osmBaseLayer'] = !empty($osm_style['detail']['baseLayer']) ? $osm_style['detail']['baseLayer'] : 'OpenStreetMap.Mapnik';
		$map_options['osmOverlays'] = !empty($osm_style['detail']['overlays']) ? $osm_style['detail']['overlays'] : '[]';
	}
    
	return $map_options;
}

function geodir_custom_gmaps_object_to_array($data) {
    if (is_array($data) || is_object($data)) {
        $result = array();
        
		if (!empty($data)) {
			foreach ($data as $key => $value) {
				$result[$key] = geodir_custom_gmaps_object_to_array($value);
			}
		}
        return $result;
    }
    return $data;
}

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_map_name($map_name) {
    if (geodir_custom_gmaps_current_subtab() == 'geodir_custom_gmaps_manage_styles' && !empty($_REQUEST['gd_map'])) {
        if (!empty($_REQUEST['map']) && $_REQUEST['map'] == 'osm') {
            $map_name = 'osm';
        } else {
            $map_name = 'google';
        }
    }
    return $map_name;
}

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_osm_providers() {
    $providers = array(
        'OpenStreetMap' => array(
            'url' => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'options' => array(
                'maxZoom' => 19,
                'attribution' => '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            ),
            'variants' => array(
                'Mapnik' => array(),
                'BlackAndWhite' => array(
                    'url' => '//{s}.tiles.wmflabs.org/bw-mapnik/{z}/{x}/{y}.png',
                    'options' => array(
                        'maxZoom' => 18
                    )
                ),
                'DE' => array(
                    'url' => '//{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png',
                    'options' => array(
                        'maxZoom' => 18
                    )
                ),
                'France' => array(
                    'url' => '//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
                    'options' => array(
                        'maxZoom' => 20,
                        'attribution' => '&copy; Openstreetmap France | {attribution.OpenStreetMap}'
                    )
                ),
                'HOT' => array(
                    'url' => '//{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
                    'options' => array(
                        'attribution' => '{attribution.OpenStreetMap}, Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
                    )
                )
            )
        ),
        'OpenSeaMap' => array(
            'url' => '//tiles.openseamap.org/seamark/{z}/{x}/{y}.png',
            'options' => array(
                'attribution' => 'Map data: &copy; <a href="http://www.openseamap.org">OpenSeaMap</a> contributors'
            )
        ),
        'OpenTopoMap' => array(
            'url' => '//{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            'options' => array(
                'maxZoom' => 17,
                'attribution' => 'Map data: {attribution.OpenStreetMap}, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
            )
        ),
        'Thunderforest' => array(
            'url' => '//{s}.tile.thunderforest.com/{variant}/{z}/{x}/{y}.png',
            'options' => array(
                'attribution' => '&copy; <a href="http://www.thunderforest.com/">Thunderforest</a>, {attribution.OpenStreetMap}',
                'variant' => 'cycle'
            ),
            'variants' => array(
                'OpenCycleMap' => 'cycle',
                'Transport' => array(
                    'options' => array(
                        'variant' => 'transport',
                        'maxZoom' => 19
                    )
                ),
                'TransportDark' => array(
                    'options' => array(
                        'variant' => 'transport-dark',
                        'maxZoom' => 19
                    )
                ),
                'SpinalMap' => array(
                    'options' => array(
                        'variant' => 'spinal-map',
                        'maxZoom' => 11
                    )
                ),
                'Landscape' => 'landscape',
                'Outdoors' => 'outdoors',
                'Pioneer' => 'pioneer'
            )
        ),
        'OpenMapSurfer' => array(
            'url' => '//korona.geog.uni-heidelberg.de/tiles/{variant}/x={x}&y={y}&z={z}',
            'options' => array(
                'maxZoom' => 20,
                'variant' => 'roads',
                'attribution' => 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data {attribution.OpenStreetMap}'
            ),
            'variants' => array(
                'Roads' => 'roads',
                'AdminBounds' => array(
                    'options' => array(
                        'variant' => 'adminb',
                        'maxZoom' => 19
                    )
                ),
                'Grayscale' => array(
                    'options' => array(
                        'variant' => 'roadsg',
                        'maxZoom' => 19
                    )
                )
            )
        ),
        'Hydda' => array(
            'url' => '//{s}.tile.openstreetmap.se/hydda/{variant}/{z}/{x}/{y}.png',
            'options' => array(
                'variant' => 'full',
                'attribution' => 'Tiles courtesy of <a href="http://openstreetmap.se/" target="_blank">OpenStreetMap Sweden</a> &mdash; Map data {attribution.OpenStreetMap}'
            ),
            'variants' => array(
                'Full' => 'full',
                'Base' => 'base',
                'RoadsAndLabels' => 'roads_and_labels'
            )
        ),
        'Stamen' => array(
            'url' => '//stamen-tiles-{s}.a.ssl.fastly.net/{variant}/{z}/{x}/{y}.{ext}',
            'options' => array(
                'attribution' => 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data {attribution.OpenStreetMap}',
                'subdomains' => 'abcd',
                'minZoom' => 0,
                'maxZoom' => 20,
                'variant' => 'toner',
                'ext' => 'png'
            ),
            'variants' => array(
                'Toner' => 'toner',
                'TonerBackground' => 'toner-background',
                'TonerHybrid' => 'toner-hybrid',
                'TonerLines' => 'toner-lines',
                'TonerLabels' => 'toner-labels',
                'TonerLite' => 'toner-lite',
                'Watercolor' => array(
                    'options' => array(
                        'variant' => 'watercolor',
                        'minZoom' => 1,
                        'maxZoom' => 16
                    )
                ),
                'Terrain' => array(
                    'options' => array(
                        'variant' => 'terrain',
                        'minZoom' => 0,
                        'maxZoom' => 18
                    )
                ),
                'TerrainBackground' => array(
                    'options' => array(
                        'variant' => 'terrain-background',
                        'minZoom' => 0,
                        'maxZoom' => 18
                    )
                ),
                'TopOSMRelief' => array(
                    'options' => array(
                        'variant' => 'toposm-color-relief',
                        'ext' => 'jpg',
                        'bounds' => array(
                            array(22, -132),
                            array(51, -56)
                        )
                    )
                ),
                'TopOSMFeatures' => array(
                    'options' => array(
                        'variant' => 'toposm-features',
                        'bounds' => array(
                            array(22, -132),
                            array(51, -56)
                        ),
                        'opacity' => 0.9
                    )
                )
            )
        ),
        'Esri' => array(
            'url' => '//server.arcgisonline.com/ArcGIS/rest/services/{variant}/MapServer/tile/{z}/{y}/{x}',
            'options' => array(
                'variant' => 'World_Street_Map',
                'attribution' => 'Tiles &copy; Esri'
            ),
            'variants' => array(
                'WorldStreetMap' => array(
                    'options' => array(
                        'attribution' => '{attribution.Esri} &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012'
                    )
                ),
                'DeLorme' => array(
                    'options' => array(
                        'variant' => 'Specialty/DeLorme_World_Base_Map',
                        'minZoom' => 1,
                        'maxZoom' => 11,
                        'attribution' => '{attribution.Esri} &mdash; Copyright: &copy;2012 DeLorme'
                    )
                ),
                'WorldTopoMap' => array(
                    'options' => array(
                        'variant' => 'World_Topo_Map',
                        'attribution' => '{attribution.Esri} &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'
                    )
                ),
                'WorldImagery' => array(
                    'options' => array(
                        'variant' => 'World_Imagery',
                        'attribution' => '{attribution.Esri} &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                    )
                ),
                'WorldTerrain' => array(
                    'options' => array(
                        'variant' => 'World_Terrain_Base',
                        'maxZoom' => 13,
                        'attribution' => '{attribution.Esri} &mdash; Source: USGS, Esri, TANA, DeLorme, and NPS'
                    )
                ),
                'WorldShadedRelief' => array(
                    'options' => array(
                        'variant' => 'World_Shaded_Relief',
                        'maxZoom' => 13,
                        'attribution' => '{attribution.Esri} &mdash; Source: Esri'
                    )
                ),
                'WorldPhysical' => array(
                    'options' => array(
                        'variant' => 'World_Physical_Map',
                        'maxZoom' => 8,
                        'attribution' => '{attribution.Esri} &mdash; Source: US National Park Service'
                    )
                ),
                'OceanBasemap' => array(
                    'options' => array(
                        'variant' => 'Ocean_Basemap',
                        'maxZoom' => 13,
                        'attribution' => '{attribution.Esri} &mdash; Sources: GEBCO, NOAA, CHS, OSU, UNH, CSUMB, National Geographic, DeLorme, NAVTEQ, and Esri'
                    )
                ),
                'NatGeoWorldMap' => array(
                    'options' => array(
                        'variant' => 'NatGeo_World_Map',
                        'maxZoom' => 16,
                        'attribution' => '{attribution.Esri} &mdash; National Geographic, Esri, DeLorme, NAVTEQ, UNEP-WCMC, USGS, NASA, ESA, METI, NRCAN, GEBCO, NOAA, iPC'
                    )
                ),
                'WorldGrayCanvas' => array(
                    'options' => array(
                        'variant' => 'Canvas/World_Light_Gray_Base',
                        'maxZoom' => 16,
                        'attribution' => '{attribution.Esri} &mdash; Esri, DeLorme, NAVTEQ'
                    )
                )
            )
        ),
        'OpenWeatherMap' => array(
            'url' => '//{s}.tile.openweathermap.org/map/{variant}/{z}/{x}/{y}.png',
            'options' => array(
                'maxZoom' => 19,
                'attribution' => 'Map data &copy; <a href="http://openweathermap.org">OpenWeatherMap</a>',
                'opacity' => 0.5
            ),
            'variants' => array(
                'Clouds' => 'clouds',
                'CloudsClassic' => 'clouds_cls',
                'Precipitation' => 'precipitation',
                'PrecipitationClassic' => 'precipitation_cls',
                'Rain' => 'rain',
                'RainClassic' => 'rain_cls',
                'Pressure' => 'pressure',
                'PressureContour' => 'pressure_cntr',
                'Wind' => 'wind',
                'Temperature' => 'temp',
                'Snow' => 'snow'
            )
        ),
        'FreeMapSK' => array(
            'url' => '//t{s}.freemap.sk/T/{z}/{x}/{y}.jpeg',
            'options' => array(
                'minZoom' => 8,
                'maxZoom' => 16,
                'subdomains' => '1234',
                'bounds' => array(
                    array(47.204642, 15.996093),
                    array(49.830896, 22.576904)
                ),
                'attribution' => '{attribution.OpenStreetMap}, vizualization CC-By-SA 2.0 <a href="http://freemap.sk">Freemap.sk</a>'
            )
        ),
        'MtbMap' => array(
            'url' => '//tile.mtbmap.cz/mtbmap_tiles/{z}/{x}/{y}.png',
            'options' => array(
                'attribution' => '{attribution.OpenStreetMap} &amp; USGS'
            )
        ),
        'CartoDB' => array(
            'url' => '//{s}.basemaps.cartocdn.com/{variant}/{z}/{x}/{y}.png',
            'options' => array(
                'attribution' => '{attribution.OpenStreetMap} &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
                'subdomains' => 'abcd',
                'maxZoom' => 19,
                'variant' => 'light_all'
            ),
            'variants' => array(
                'Positron' => 'light_all',
                'PositronNoLabels' => 'light_nolabels',
                'PositronOnlyLabels' => 'light_only_labels',
                'DarkMatter' => 'dark_all',
                'DarkMatterNoLabels' => 'dark_nolabels',
                'DarkMatterOnlyLabels' => 'dark_only_labels'
            )
        ),
        'HikeBike' => array(
            'url' => '//{s}.tiles.wmflabs.org/{variant}/{z}/{x}/{y}.png',
            'options' => array(
                'maxZoom' => 19,
                'attribution' => '{attribution.OpenStreetMap}',
                'variant' => 'hikebike'
            ),
            'variants' => array(
                'HikeBike' => array(),
                'HillShading' => array(
                    'options' => array(
                        'maxZoom' => 15,
                        'variant' => 'hillshading'
                    )
                )
            )
        ),
        'BasemapAT' => array(
            'url' => '//maps{s}.wien.gv.at/basemap/{variant}/normal/google3857/{z}/{y}/{x}.{format}',
            'options' => array(
                'maxZoom' => 19,
                'attribution' => 'Datenquelle: <a href="www.basemap.at">basemap.at</a>',
                'subdomains' => array('', '1', '2', '3', '4'),
                'format' => 'png',
                'bounds' => array(
                    array(46.358770, 8.782379),
                    array(49.037872, 17.189532)
                ),
                'variant' => 'geolandbasemap'
            ),
            'variants' => array(
                'basemap' => 'geolandbasemap',
                'grau' => 'bmapgrau',
                'overlay' => 'bmapoverlay',
                'highdpi' => array(
                    'options' => array(
                        'variant' => 'bmaphidpi',
                        'format' => 'jpeg'
                    )
                ),
                'orthofoto' => array(
                    'options' => array(
                        'variant' => 'bmaporthofoto30cm',
                        'format' => 'jpeg'
                    )
                )
            )
        ),
        'NASAGIBS' => array(
            'url' => '//map1.vis.earthdata.nasa.gov/wmts-webmerc/{variant}/default/{time}/{tilematrixset}{maxZoom}/{z}/{y}/{x}.{format}',
            'options' => array(
                'attribution' => 'Imagery provided by services from the Global Imagery Browse Services (GIBS), operated by the NASA/GSFC/Earth Science Data and Information System (<a href="https://earthdata.nasa.gov">ESDIS</a>) with funding provided by NASA/HQ.',
                'bounds' => array(
                    array(-85.0511287776, -179.999999975),
                    array(85.0511287776, 179.999999975)
                ),
                'minZoom' => 1,
                'maxZoom' => 9,
                'format' => 'jpg',
                'time' => '',
                'tilematrixset' => 'GoogleMapsCompatible_Level'
            ),
            'variants' => array(
                'ModisTerraTrueColorCR' => 'MODIS_Terra_CorrectedReflectance_TrueColor',
                'ModisTerraBands367CR' => 'MODIS_Terra_CorrectedReflectance_Bands367',
                'ViirsEarthAtNight2012' => array(
                    'options' => array(
                        'variant' => 'VIIRS_CityLights_2012',
                        'maxZoom' => 8
                    )
                ),
                'ModisTerraLSTDay' => array(
                    'options' => array(
                        'variant' => 'MODIS_Terra_Land_Surface_Temp_Day',
                        'format' => 'png',
                        'maxZoom' => 7,
                        'opacity' => 0.75
                    )
                ),
                'ModisTerraSnowCover' => array(
                    'options' => array(
                        'variant' => 'MODIS_Terra_Snow_Cover',
                        'format' => 'png',
                        'maxZoom' => 8,
                        'opacity' => 0.75
                    )
                ),
                'ModisTerraAOD' => array(
                    'options' => array(
                        'variant' => 'MODIS_Terra_Aerosol',
                        'format' => 'png',
                        'maxZoom' => 6,
                        'opacity' => 0.75
                    )
                ),
                'ModisTerraChlorophyll' => array(
                    'options' => array(
                        'variant' => 'MODIS_Terra_Chlorophyll_A',
                        'format' => 'png',
                        'maxZoom' => 7,
                        'opacity' => 0.75
                    )
                )
            )
        ),
        'NLS' => array(
            // NLS maps are copyright National library of Scotland.
            // http://maps.nls.uk/projects/api/index.html
            // Please contact NLS for anything other than non-commercial low volume usage
            //
            // Map sources: Ordnance Survey 1:1m to 1:63K, 1920s-1940s
            //   z0-9  - 1:1m
            //  z10-11 - quarter inch (1:253440)
            //  z12-18 - one inch (1:63360)
            'url' => '//nls-{s}.tileserver.com/nls/{z}/{x}/{y}.jpg',
            'options' => array(
                'attribution' => '<a href="http://geo.nls.uk/maps/">National Library of Scotland Historic Maps</a>',
                'bounds' => array(
                    array(49.6, -12),
                    array(61.7, 3)
                ),
                'minZoom' => 1,
                'maxZoom' => 18,
                'subdomains' => '0123',
            )
        )
    );
    
    return apply_filters('geodir_custom_gmaps_osm_providers', $providers );
}

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_osm_get_layers($type = 'all') {
    $all_providers = $providers = geodir_custom_gmaps_osm_providers();
    if ( $type != 'base' && $type != 'overlay' && $type != 'active' ) {
        return $all_providers;
    }
    
    if ($type == 'active') {
        $type = 'overlay';
        $overlays = geodir_custom_gmaps_active_provider_names();
    } else {
        $overlays = geodir_custom_gmaps_osm_define_overlays();
    }
    
    foreach ($all_providers as $name => $layer) {
        if (!empty($layer['variants']) && is_array($layer['variants'])) {
            $parent = !empty($name) ? $name . '.' : '';
            $variants = $layer['variants'];
            
            foreach ($variants as $variant_name => $variant) {
                $full_variant = $parent . $variant_name;
                
                if ($type == 'overlay' && !in_array($full_variant, $overlays) && isset($providers[$name]['variants'][$variant_name])) {
                    unset($providers[$name]['variants'][$variant_name]);
                } else if ($type == 'base' && in_array($full_variant, $overlays) && isset($providers[$name]['variants'][$variant_name])) {
                    unset($providers[$name]['variants'][$variant_name]);
                }
            }
            
            if (empty($providers[$name]['variants'])) {
                unset($providers[$name]);
            }
        } else {
            if (!empty($name)) {
                if ($type == 'overlay' && !in_array($name, $overlays) && isset($providers[$name])) {
                    unset($providers[$name]);
                } else if ($type == 'base' && in_array($name, $overlays) && isset($providers[$name])) {
                    unset($providers[$name]);
                }
            }
        }
    }
    
    return $providers;
 }

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_osm_layer_names($type = 'base') {
    $providers = geodir_custom_gmaps_osm_get_layers($type);
    
    if ( $type == 'all' ) {
        return $providers;
    }
    
    $layer_names = array();
            
    foreach ($providers as $name => $layer) {
        if (!empty($layer['variants']) && is_array($layer['variants'])) {
            $parent = !empty($name) ? $name . '.' : '';
            $variants = $layer['variants'];
            
            foreach ($variants as $variant_name => $variant) {
                if (!empty($variant_name)) {
                    $layer_names[] = $parent . $variant_name;
                }
            }
        } else {
            if (!empty($name)) {
                $layer_names[] = $name;
            }
        }
    }
    
    return $layer_names;
 }
 
/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_osm_define_overlays() {
    $overlays = array('OpenSeaMap', 'OpenMapSurfer.AdminBounds', 'Hydda.RoadsAndLabels', 'Stamen.TonerHybrid', 'Stamen.TonerLines', 'Stamen.TonerLabels', 'Stamen.TopOSMFeatures', 'OpenWeatherMap.Clouds', 'OpenWeatherMap.Pressure', 'OpenWeatherMap.Wind', 'NASAGIBS.ModisTerraLSTDay', 'NASAGIBS.ModisTerraSnowCover', 'NASAGIBS.ModisTerraAOD', 'NASAGIBS.ModisTerraChlorophyll' );

    return apply_filters('geodir_custom_gmaps_osm_define_overlays', $overlays);
}

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_active_provider_names() {
    $home = get_option('geodir_custom_gmaps_home');
    $listing = get_option('geodir_custom_gmaps_listing');
    $detail = get_option('geodir_custom_gmaps_detail');
    
    $providers = array();
    if ($home || $listing || $detail) {
        $style = geodir_custom_gmaps_get_osm_style();
        
        if ($home) {
            if (!empty($style['home']['baseLayer'])) {
                $providers = array_merge($providers, array($style['home']['baseLayer']));
            }
            
            if (!empty($style['home']['overlays']) && is_array($style['home']['overlays'])) {
                $providers = array_merge($providers, $style['home']['overlays']);
            }
        }
        
        if ($listing) {
            if (!empty($style['listing']['baseLayer'])) {
                $providers = array_merge($providers, array($style['listing']['baseLayer']));
            }
            
            if (!empty($style['listing']['overlays']) && is_array($style['listing']['overlays'])) {
                $providers = array_merge($providers, $style['listing']['overlays']);
            }
        }
        
        if ($detail) {
            if (!empty($style['detail']['baseLayer'])) {
                $providers = array_merge($providers, array($style['detail']['baseLayer']));
            }
            
            if (!empty($style['detail']['overlays']) && is_array($style['detail']['overlays'])) {
                $providers = array_merge($providers, $style['detail']['overlays']);
            }
        }
    }
    if (!empty($providers)) {
        $providers = array_merge(array('OpenStreetMap.Mapnik'), $providers);
        $providers = array_unique($providers);
    }
    
    return $providers;
}
 
/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_localize_providers($vars = array()) {
    if (($home = get_option('geodir_custom_gmaps_home')) || ($listing = get_option('geodir_custom_gmaps_listing')) || ($detail = get_option('geodir_custom_gmaps_detail'))) {        
        $providers = geodir_custom_gmaps_osm_get_layers('active');
        
        $vars['osmProviders'] = !empty($providers) ? $providers : '[]';
    }
    
    return $vars;
}

/**
 * @since 1.0.8
 *
 */
function geodir_custom_gmaps_get_osm_style() {
    $default_layer = 'OpenStreetMap.Mapnik';
    
    $defaults = array(
            'home' => array(
                'baseLayer' => $default_layer,
                'overlays' => array()
            ),
            'listing' => array(
                'baseLayer' => $default_layer,
                'overlays' => array()
            ),
            'detail' => array(
                'baseLayer' => $default_layer,
                'overlays' => array()
            ),
        );
    
    $style = get_option('geodir_custom_gmaps_osm_style');
    
    $style = wp_parse_args($style, $defaults);
    
    foreach ($defaults as $page => $value) {
        if (empty($style[$page]['baseLayer'])) {
            $style[$page]['baseLayer'] = $value['baseLayer'];
        }
        
        if (empty($style[$page]['overlays'])) {
            $style[$page]['overlays'] = $value['overlays'];
        }
    }
    
    return apply_filters('geodir_custom_gmaps_get_osm_style', $style);
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.0.8
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_custom_gmaps_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}