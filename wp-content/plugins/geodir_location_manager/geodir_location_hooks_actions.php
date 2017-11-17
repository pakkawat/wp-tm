<?php
/**
 * Contains hook related to Location Manager plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */

add_filter('geodir_diagnose_multisite_conversion', 'geodir_diagnose_multisite_conversion_location_manager', 10, 1);
/**
 * Diagnose Location Manager tables.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param array $table_arr Diagnose table array.
 * @return array Modified diagnose table array.
 */
function geodir_diagnose_multisite_conversion_location_manager($table_arr)
{

    $table_arr['geodir_post_neighbourhood'] = __('Neighbourhood', 'geodirlocation');
    $table_arr['geodir_post_locations'] = __('Locations', 'geodirlocation');
    $table_arr['geodir_location_seo'] = __('Location SEO', 'geodirlocation');
    return $table_arr;
}

/**************************
 * /* ACTIVATION/DEACTIVATION
 ***************************/

/**
 * Plugin Activation Function
 *
 * @since 1.0.0
 * @since 1.5.1 Should not loose previously saved settings when plugin is reactivated.
 * @package GeoDirectory_Location_Manager
 */
function geodir_location_activation() {
    if (get_option('geodir_installed')) {
        geodir_location_activation_script();

        $options = geodir_location_resave_settings(geodir_location_default_options());
        geodir_update_options($options, true);

        add_option('geodir_location_manager_activation_redirect', 1);
    }
}

/**
 * Function to install all location manager related data and options.
 *
 * @since 1.0.0
 * @since 1.4.1 Updated to fix dbDelta sql for upgrades.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_location_activation_script()
{
    global $wpdb, $plugin_prefix;

    /**
     * Include any functions needed for upgrades.
     *
     * @since 1.3.6
     */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $is_set_default_location = geodir_get_default_location();
    $wpdb->hide_errors();

    $collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }


    // post locations table
    $location_table = "CREATE TABLE " . POST_LOCATION_TABLE . " (
					location_id int(11) NOT NULL AUTO_INCREMENT,
					country varchar(254) NOT NULL,
					region varchar(254) NOT NULL,
					city varchar(254) NOT NULL,
					country_slug varchar(254) NOT NULL,
					region_slug varchar(254) NOT NULL,
					city_slug varchar(254) NOT NULL,
					city_latitude varchar(254) NOT NULL,
					city_longitude varchar(254) NOT NULL,
					is_default ENUM( '0', '1' ) NOT NULL DEFAULT '0',
					city_meta VARCHAR( 254 ) NOT NULL,
					city_desc TEXT NOT NULL,
					PRIMARY KEY  (location_id)
					) $collate ";

    /**
     * Filter the SQL query that creates/updates the post locations DB table structure.
     *
     * @since 1.4.1
     * @param string $sql The SQL insert query string.
     */
    $location_table = apply_filters('geodir_location_post_locations_table_create', $location_table);
    dbDelta($location_table);

    $location_result = (array)geodir_get_default_location(); // this function is there in core plugin location_functions.php file.
    $post_types = geodir_get_posttypes(); // Function in core geodirectory plugin
    $location_info = geodir_add_new_location_via_adon($location_result);
    geodir_location_set_default($location_info->location_id);

    if (!empty($post_types)) {
        foreach ($post_types as $post_type) {
            $table = $plugin_prefix . $post_type . '_detail';
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE " . $table . " SET post_location_id=%d WHERE post_location_id=0",
                    array($location_info->location_id)
                )
            );
        }
    }

    // location neighbourhood table
    $neighbourhood_table = "CREATE TABLE " . POST_NEIGHBOURHOOD_TABLE . " (
				hood_id int(11) NOT NULL AUTO_INCREMENT,
				hood_location_id int(11) NOT NULL,
				hood_name varchar(254) NOT NULL,
				hood_latitude varchar(254) NOT NULL,
				hood_longitude varchar(254) NOT NULL,
				hood_slug varchar(254) NOT NULL,
				hood_meta_title varchar(254) NOT NULL,
                hood_meta varchar(254) NOT NULL,
                hood_description text NOT NULL,
				PRIMARY KEY  (hood_id)
				) $collate ";

    /**
     * Filter the SQL query that creates/updates the location neighbourhood DB table structure.
     *
     * @since 1.4.1
     * @param string $sql The SQL insert query string.
     */
    $neighbourhood_table = apply_filters('geodir_location_post_neighbourhood_table_create', $neighbourhood_table);
    dbDelta($neighbourhood_table);

    $address_extra_info = $wpdb->get_results("select id, extra_fields from " . GEODIR_CUSTOM_FIELDS_TABLE . " where field_type = 'address'");

    if (!empty($address_extra_info)) {
        foreach ($address_extra_info as $extra) {
            $fields = array();
            if ($extra->extra_fields != '') {
                $fields = unserialize($extra->extra_fields);
                if (!isset($fields['show_city'])) {
                    $fields['show_city'] = 1;
                }
                if (!isset($fields['city_lable'])) {
                    $fields['city_lable'] = __('City', 'geodirlocation');
                }
                if (!isset($fields['show_region'])) {
                    $fields['show_region'] = 1;
                }
                if (!isset($fields['region_lable'])) {
                    $fields['region_lable'] = __('Region', 'geodirlocation');
                }
                if (!isset($fields['show_country'])) {
                    $fields['show_country'] = 1;
                }
                if (!isset($fields['country_lable'])) {
                    $fields['country_lable'] = __('Country', 'geodirlocation');
                }

                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE " . GEODIR_CUSTOM_FIELDS_TABLE . " SET extra_fields=%s WHERE id=%d",
                        array(serialize($fields), $extra->id)
                    )
                );
            }
        }
    }

    //$post_types = geodir_get_posttypes();
    if (!empty($post_types)) {
        foreach ($post_types as $post_type) {
            $detail_table = $plugin_prefix . $post_type . '_detail';
            $meta_field_add = "VARCHAR( 100 ) NULL";
            geodir_add_column_if_not_exist($detail_table, "post_neighbourhood", $meta_field_add);
        }

        if (GEODIRLOCATION_VERSION <= '1.5.0') {
            geodir_location_fix_neighbourhood_field_limit_150();
        }
    }

    // location seo table
    $location_seo_table = "CREATE TABLE " . LOCATION_SEO_TABLE . " (
			  seo_id int(11) NOT NULL AUTO_INCREMENT,
			  location_type varchar(255) NOT NULL,
			  country_slug varchar(254) NOT NULL,
			  region_slug varchar(254) NOT NULL,
			  city_slug varchar(254) NOT NULL,
			  seo_meta_title text NOT NULL,
			  seo_meta_desc text NOT NULL,
			  seo_title text NOT NULL,
			  seo_desc text NOT NULL,
			  seo_image varchar(254) NOT NULL,
			  seo_image_tagline varchar(254) NOT NULL,
			  date_created datetime NOT NULL,
			  date_updated datetime NOT NULL,
			  PRIMARY KEY  (seo_id)
			) $collate ";

    /**
     * Filter the SQL query that creates/updates the location seo DB table structure.
     *
     * @since 1.4.1
     * @param string $sql The SQL insert query string.
     */
    $location_seo_table = apply_filters('geodir_location_seo_table_create', $location_seo_table);
    dbDelta($location_seo_table);

    // location term meta count table
    $term_meta_table = "CREATE TABLE " . GEODIR_TERM_META . " (
					id int NOT NULL AUTO_INCREMENT,
					location_type varchar( 100 ) NULL DEFAULT NULL,
					location_name varchar( 100 ) NULL DEFAULT NULL,
					region_slug varchar( 100 ) NOT NULL,
					country_slug varchar( 100 ) NOT NULL,
					term_count text NOT NULL,
					review_count text NOT NULL,
					PRIMARY KEY  (id)
					) $collate ";

    /**
     * Filter the SQL query that creates/updates the term meta DB table structure.
     *
     * @since 1.4.1
     * @param string $sql The SQL insert query string.
     */
    $term_meta_table = apply_filters('geodir_location_term_meta_table_create', $term_meta_table);
    dbDelta($term_meta_table);
}

/**
 * Plugin deactivation Function.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_location_deactivation()
{
    global $wpdb, $plugin_prefix;
    $default_location = geodir_get_default_location();
    $post_types = geodir_get_posttypes();
    if (!empty($post_types)) {
        foreach ($post_types as $post_type) {
            $table = $plugin_prefix . $post_type . '_detail';
            $wpdb->query($wpdb->prepare("UPDATE " . $table . " SET post_location_id='0' WHERE post_location_id=%d", array($default_location->location_id)));
        }

    }
    $default_location->location_id = 0;
    update_option('geodir_default_location', $default_location);
}

/**
 * Handle the plugin settings for plugin deactivate to activate.
 *
 * It manages the the settings without loosing previous settings saved when plugin
 * status changed from deactivate to activate.
 *
 * @since 1.5.1
 * @package GeoDirectory_Location_Manager
 *
 * @param array $settings The option settings array.
 * @return array The settings array.
 */
function geodir_location_resave_settings($settings = array()) {
    if (!empty($settings) && is_array($settings)) {
        $c = 0;

        foreach ($settings as $setting) {
            if (!empty($setting['id']) && false !== ($value = get_option($setting['id']))) {
                $settings[$c]['std'] = $value;
            }
            $c++;
        }
    }

    return $settings;
}

/**************************
 * /* INIT HOOKS
 ***************************/
add_action('admin_init', 'geodir_admin_location_init');
/**
 * Initialize admin functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_admin_location_init()
{
    if (is_admin()):
        geodir_location_form_submit_handler();
        add_filter('geodir_settings_tabs_array', 'geodir_admin_location_tabs', 4);
        add_action('geodir_admin_option_form', 'geodir_get_admin_location_option_form', 2);
    endif;
}

/**
 * Function to add tabs and subtabs in GeoDirectory backend.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param array $tabs Geodirectory settings page tab list.
 * @return array Modified Tabs list
 */
function geodir_admin_location_tabs($tabs)
{
    $tabs['managelocation_fields'] = array(
        'label' => __('MultiLocations', 'geodirlocation'),
        'subtabs' => array(
            array('subtab' => 'geodir_location_setting',
                'label' => __('Location Settings', 'geodirlocation'),
                'form_action' => admin_url('admin-ajax.php?action=geodir_locationajax_action')
            ),
            array('subtab' => 'geodir_location_manager',
                'label' => __('Manage Location', 'geodirlocation'),
                'form_action' => ''
            ),
            array('subtab' => 'geodir_location_seo',
                'label' => __('SEO Settings', 'geodirlocation'),
                'form_action' => ''
            ),
            array('subtab' => 'geodir_location_addedit',
                'label' => __('Add/Edit Location', 'geodirlocation'),
                'form_action' => admin_url('admin-ajax.php?action=geodir_locationajax_action')
            ),
            array(
                'subtab' => 'geodir_location_translate',
                'label' => __('Translate Countries', 'geodirlocation'),
                'form_action' => ''
            )
        )// end of sub tab array
    );// end of main array

    return $tabs;
}

/**
 * Function to show backend form based on selected sub tab.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param string $current_tab The current settings tab name.
 */
function geodir_get_admin_location_option_form($current_tab)
{
    global $wpdb;

    $subtab = isset($_REQUEST['subtab']) ? $_REQUEST['subtab'] : '';

    switch ($subtab) {
        case 'geodir_location_setting': {
            add_action('geodir_admin_option_form', 'geodir_get_location_default_options_form');// function is in geodir_location_template_tags.php file
        }
            break;
        case 'geodir_location_manager': {
            include_once('geodir_location_list.php');
        }
            break;
        case 'geodir_location_seo': {
            include_once('geodir_location_seo.php');
        }
            break;
        case 'geodir_location_addedit': {
            include_once('geodir_add_location.php');
        }
            break;
        case 'geodir_location_translate': {
            include_once('geodir_location_translate.php');
        }
            break;
    }
}

/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $current_tab The current settings tab name.
 */
function geodir_get_location_default_options_form($current_tab)
{

    $current_tab = $_REQUEST['subtab'];
    geodir_location_default_option_form($current_tab); // this function is in template tags
}

add_action('admin_init', 'geodir_location_activation_redirect');
/**
 * Hook to redirect user to Location related setting page in backend on plugin installation.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_location_activation_redirect()
{
    if (get_option('geodir_location_manager_activation_redirect', false)) {
        delete_option('geodir_location_manager_activation_redirect');
        wp_redirect(admin_url('admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_setting'));
    }
}

add_action('geodir_before_admin_panel', 'geodir_display_location_messages');
/**
 * Function for display GeoDirectory location error and success messages.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_display_location_messages()
{
    if (isset($_REQUEST['location_success']) && $_REQUEST['location_success'] != '') {
        echo '<div id="message" class="updated fade"><p><strong>' . sanitize_text_field($_REQUEST['location_success']) . '</strong></p></div>';

    }

    if (isset($_REQUEST['location_error']) && $_REQUEST['location_error'] != '') {
        echo '<div id="payment_message_error" class="updated fade"><p><strong>' . sanitize_text_field($_REQUEST['location_error']) . '</strong></p></div>';

    }
}


/**************************
 * /* SCRIPT AND STYLE RELATED
 ***************************/

add_action('wp_enqueue_scripts', 'geodir_add_location_style_sheet');
/**
 * Adds location manager plugin css.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_add_location_style_sheet()
{
    wp_enqueue_style('location_manager_css', plugins_url('/css/geodir-location.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'geodir_add_location_admin_style_sheet');
/**
 * Adds location manager plugin admin css.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_add_location_admin_style_sheet()
{
    wp_enqueue_style('location_manager_admin_css', plugins_url('/css/location-admin.css', __FILE__));
}


add_action('wp_enqueue_scripts', 'geodir_add_location_scripts');
add_action('admin_enqueue_scripts', 'geodir_add_location_scripts');
/**
 * Adds location manager plugin js.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_add_location_scripts()
{
    if (is_admin() && (isset($_REQUEST['page']) && $_REQUEST['page'] == 'geodirectory') && (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'managelocation_fields'))
        wp_enqueue_script('geodirectory-location-admin', plugins_url('/js/location-admin.js', __FILE__));

    // Include script only on front end.

    wp_enqueue_script('geodirectory-location-front', plugins_url('/js/location-front.min.js#asyncload', __FILE__), '', '', true);


}

add_action('wp_footer', 'geodir_location_localize_all_js_msg');
add_action('admin_footer', 'geodir_location_localize_all_js_msg');
/**
 * Outputs translated JS text strings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_location_localize_all_js_msg()
{
    global $path_location_url;

    $arr_alert_msg = array(
        'geodir_location_admin_url' => admin_url('admin.php'),
        'geodir_location_plugin_url' => $path_location_url,
        'geodir_location_admin_ajax_url' => admin_url('admin-ajax.php'),
        'select_merge_city_msg' => MSG_LOCATION_JS_SELECT_CITY,
        'set_location_default_city_confirmation' => MSG_LOCATION_SET_DEFAULT_CITY,
        'LISTING_URL_PREFIX' => __('Please enter listing url prefix', 'geodirlocation'),
        'LISTING_URL_PREFIX_INVALID_CHAR' => __('Invalid character in listing url prefix', 'geodirlocation'),
        'LOCATION_URL_PREFIX' => __('Please enter location url prefix', 'geodirlocation'),
        'LOCATOIN_PREFIX_INVALID_CHAR' => __('Invalid character in location url prefix', 'geodirlocation'),
        'LOCATION_CAT_URL_SEP' => __('Please enter location and category url separator', 'geodirlocation'),
        'LOCATION_CAT_URL_SEP_INVALID_CHAR' => __('Invalid character in location and category url separator', 'geodirlocation'),
        'LISTING_DETAIL_URL_SEP' => __('Please enter listing detail url separator', 'geodirlocation'),
        'LISTING_DETAIL_URL_SEP_INVALID_CHAR' => __('Invalid character in listing detail url separator', 'geodirlocation'),

        'LOCATION_PLEASE_WAIT' => __('Please wait...', 'geodirlocation'),
        'LOCATION_CHOSEN_NO_RESULT_TEXT' => __('Sorry, nothing found!', 'geodirlocation'),
        'LOCATION_CHOSEN_KEEP_TYPE_TEXT' => __('Please wait...', 'geodirlocation'),
        'LOCATION_CHOSEN_LOOKING_FOR_TEXT' => __('We are searching for', 'geodirlocation'),
        'select_location_translate_msg' => MSG_LOCATION_JS_SELECT_COUNTRY,
        'select_location_translate_confirm_msg' => MSG_LOCATION_JS_SELECT_COUNTRY_CONFIRM,
        'gd_text_search_city' => __('Search City', 'geodirlocation'),
        'gd_text_search_region' => __('Search Region', 'geodirlocation'),
        'gd_text_search_country' => __('Search Country', 'geodirlocation'),
        'gd_text_search_location' => __('Search location', 'geodirlocation'),
        'gd_base_location' => geodir_get_location_link('base'),
        'UNKNOWN_ERROR' => __('Unable to find your location.', 'geodirlocation'),
        'PERMISSION_DENINED' => __('Permission denied in finding your location.', 'geodirlocation'),
        'POSITION_UNAVAILABLE' => __('Your location is currently unknown.', 'geodirlocation'),
        'BREAK' => __('Attempt to find location took too long.', 'geodirlocation'),
        'DEFAUTL_ERROR' => __('Browser unable to find your location.', 'geodirlocation'),
        'msg_Near' => __("Near:", 'geodirlocation'),
        'msg_Me' => __("Me", 'geodirlocation'),
        'msg_User_defined' => __("User defined", 'geodirlocation'),
        'delete_location_msg' => __('Deleting location will also DELETE any LISTINGS in this location. Are you sure want to DELETE this location?', 'geodirlocation'),
        'delete_bulk_location_select_msg' => __('Please select at least one location.', 'geodirlocation'),

    );


    foreach ($arr_alert_msg as $key => $value) {
        if (!is_scalar($value))
            continue;
        $arr_alert_msg[$key] = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
    }

    $script = "var geodir_location_all_js_msg = " . json_encode($arr_alert_msg) . ';';
    echo '<script>';
    echo $script;
    echo '</script>';
}

/**************************
 * /* WIDGETS RELATED
 ***************************/
// All these functions are in geodir_location_widgets.php file
add_action('widgets_init', 'register_geodir_location_widgets');

add_action('widgets_init', 'register_geodir_neighbourhood_widgets');

add_action('widgets_init', 'register_geodir_neighbourhood_posts_widgets');

add_action('widgets_init', 'register_geodir_location_description_widgets');


/**************************
 * /* LOCATION ADDONS ADMIN PANEL RELATED
 ***************************/
add_filter('geodir_settings_tabs_array', 'geodir_hide_set_location_default', 3);
/**
 * Function to hide geodirectory core manage default location tab.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param array $tabs Geodirectory settings page tab list.
 * @return array Modified Tabs list
 */
function geodir_hide_set_location_default($tabs)
{
    if (!empty($tabs)):
        unset($tabs['default_location_settings']);
    endif;
    return $tabs;
}

add_filter('geodir_search_near_addition', 'geodir_search_near_additions', 3);
/**
 * Adds any extra info to the near search box query when trying to geolocate it via google api.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param string $additions Extra info string.
 * @return string
 */
function geodir_search_near_additions($additions)
{
    global $wpdb;
    $loc = '';
    if ($default = get_option('geodir_default_location')) {
        if (get_option('geodir_enable_region') == 'default' && $default->region) {
            $loc .= '+", ' . $default->region . '"';
        }
        if (get_option('geodir_enable_country') == 'default' && $default->country) {
            $loc .= '+", ' . $default->country . '"';
        }
    }
    return $loc;
}

add_filter('geodir_design_settings', 'geodir_detail_page_related_post_add_location_filter_checkbox', 1);
/**
 * This add a new filed in Geodirectory > Design > Detail > Related Post Settings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param array $arr GD design settings array.
 * @return array Filtered GD design settings array.
 */
function geodir_detail_page_related_post_add_location_filter_checkbox($arr)
{
    $location_design_array = array();
    foreach ($arr as $key => $val) {
        $location_design_array[] = $val;
        if ($val['id'] == 'geodir_related_post_excerpt') {
            $location_design_array[] = array(
                'name' => __('Enable Location Filter:', 'geodirlocation'),
                'desc' => __('Enable location filter on related post.', 'geodirlocation'),
                'id' => 'geodir_related_post_location_filter',
                'type' => 'checkbox',
                'std' => '1' // Default value to show home top section
            );
        }
    }
    return $location_design_array;
}


/**************************
 * /* LOCATION ADDONS QUERY FILTERS
 **************************    */

/**
 * Sets user location.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $gd_session GeoDirectory Session object.
 */
function geodir_set_user_location_near_me()
{
    global $gd_session;
    ?>
    <script type="text/javascript">
        (function () {
            // Try HTML5 geolocation
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    lat = position.coords.latitude;
                    lon = position.coords.longitude;
                    my_location = 1;
                    if (typeof gdSetupUserLoc === 'function') {
                        gdSetupUserLoc();
                    } else {
                        gdLocationSetupUserLoc();
                    }
                    jQuery.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            action: 'gd_location_manager_set_user_location',
                            lat: lat,
                            lon: lon,
                            myloc: 1
                        },
                        beforeSend: function () {
                        },
                        success: function (data, textStatus, xhr) {
                            <?php if (!$gd_session->get('my_location')) { ?>window.location.href = "<?php echo geodir_get_location_link('base') . 'me/';?>";<?php } ?>
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            alert(textStatus);
                        }
                    });
                });
            } else {
                // Browser doesn't support Geolocation
                alert(geodir_location_all_js_msg.DEFAUTL_ERROR);
            }
        }());
    </script>
    <?php
}

add_filter('query_vars', 'add_location_var');
/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param array $public_query_vars The array of white listed query variables.
 * @return array Filtered query variables.
 */
function add_location_var($public_query_vars)
{

    $public_query_vars[] = 'gd_neighbourhood';

    return $public_query_vars;
}

if (get_option('permalink_structure') != '')
    add_filter('parse_request', 'geodir_set_location_var_in_session', 100);
/**
 * Set location data in session.
 *
 * @since 1.0.0
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 * @since 1.5.4 Set location code restructured to fix broken location links - FIXED
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param object $wp WordPress object.
 */
function geodir_set_location_var_in_session($wp) {
    global $gd_session;

    // Don't reset location if 404
    if (isset($wp->query_vars['error']) && $wp->query_vars['error'] == '404') {
        return;
    }
    /*
    $point1 = array('latitude'=>'-22.5260060699' ,'longitude'=> '-43.7334400235' ) ;
    $point1 = array('latitude'=>'28.635308' ,'longitude'=> '77.22496' ) ;
    $point2 = array('latitude'=>'-22.7024218' ,'longitude'=> '-43.33662349999997' ) ;
    $point2 = array('latitude'=>'-22.7356363' ,'longitude'=> '-43.44001100000003' ) ;

    echo geodir_calculateDistanceFromLatLong($point1, $point2);
    */
    // Avoid all the changes made by core, restore original query vars ;
    // $wp->query_vars=$wp->geodir_query_vars ;

    // this code will determine when a user wants to switch location
    // A location can be switched using 3 ways
    // 1. using location switcher, in this case the url will always have location prefix
    // Query Vars will have page_id parameter
    // check if query var has page_id and that page id is location page
    $hide_country = get_option('geodir_location_hide_country_part');
    $hide_region = get_option('geodir_location_hide_region_part');
    $hide_city = false;
    $add_categories_url = get_option('geodir_add_categories_url');
    $add_location_url = get_option('geodir_add_location_url');
    $show_location_url = get_option('geodir_show_location_url');
    $neighbourhood_active = get_option('location_neighbourhoods');

    $is_listing_location = false;
    $is_listing_term = false;
    $is_listing_detail = false;

    $geodir_location_part = 'all';
    $base_location_var = 'gd_country'; // If [gd_country] => me
    if ($hide_region && $hide_country) {
        $geodir_location_part = 'city';
        $base_location_var = 'gd_city'; // If [gd_city] => me
    } else if ($hide_region && !$hide_country) {
        $geodir_location_part = 'country_city';
    } else if (!$hide_region && $hide_country) {
        $geodir_location_part = 'region_city';
        $base_location_var = 'gd_region'; // If [gd_region] => me
    }
    $gd_neighbourhood = '';

    // my location set start
    // Fix for WPML removing page_id query var:
    if (isset($wp->query_vars['page']) && !isset($wp->query_vars['page_id']) && isset($wp->query_vars['pagename']) && !is_home()) {
        global $wpdb;

        $page_for_posts = get_option('page_for_posts');
        $real_page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_name=%s", $wp->query_vars['pagename']));

        if (geodir_is_wpml()) {
            $real_page_id = geodir_wpml_object_id($real_page_id, 'page', true, ICL_LANGUAGE_CODE);
        }
        if ($real_page_id && $real_page_id != $page_for_posts && $real_page_id != get_option('woocommerce_shop_page_id')) {// added check fro woocommerce shop page to prevent no products.
            $wp->query_vars['page_id'] = $real_page_id;
        }
    }

    if ((isset($wp->query_vars[$base_location_var]) && $wp->query_vars[$base_location_var] == 'me' && $gd_session->get('user_lat') && $gd_session->get('user_lon')) || ($gd_session->get('near_me_range') && is_admin())) {
        if (isset($_REQUEST['user_lat']) && $_REQUEST['user_lat']) {
            $gd_session->set('user_lat', $_REQUEST['user_lat']);
        }
        if (isset($_REQUEST['user_lon']) && $_REQUEST['user_lon']) {
            $gd_session->set('user_lon', $_REQUEST['user_lon']);
        }
        if ($near_me_range = $gd_session->get('near_me_range')) {
            $_REQUEST['sdist'] = $near_me_range;
        }

        $gd_session->set('all_near_me', 1);
        $_REQUEST['sgeo_lat'] = $gd_session->get('user_lat');
        $_REQUEST['sgeo_lon'] = $gd_session->get('user_lon');
        $_REQUEST['snear'] = 1;

        // unset any locations
        geodir_unset_location();

        //set page as GD page
        geodir_set_is_geodir_page($wp);
        return;
    } elseif (isset($wp->query_vars[$base_location_var]) && $wp->query_vars[$base_location_var] == 'me') {
        // at the near me page but with no location
        add_action('wp_head', 'geodir_set_user_location_near_me');
        return;
    } else {
        $gd_session->un_set('all_near_me');
    }
    // my location set end
    geodir_set_is_geodir_page($wp);

    // if is GD homepage set the page ID
    if (geodir_is_page('home')) {
        $wp->query_vars['page_id'] = get_option('page_on_front');
    }

    if (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == geodir_location_page_id() || (isset($_REQUEST['set_location_type']) && $_REQUEST['set_location_type'] && isset($_REQUEST['set_location_val']) && $_REQUEST['set_location_val'])) {
        $gd_country = '';
        $gd_region = '';
        $gd_city = '';
        $gd_neighbourhood = '';

        if (isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country'] != '')
            $gd_country = urldecode($wp->query_vars['gd_country']);

        if (isset($wp->query_vars['gd_region']) && $wp->query_vars['gd_region'] != '')
            $gd_region = urldecode($wp->query_vars['gd_region']);

        if (isset($wp->query_vars['gd_city']) && $wp->query_vars['gd_city'] != '')
            $gd_city = urldecode($wp->query_vars['gd_city']);

        if (isset($wp->query_vars['gd_neighbourhood']) && $wp->query_vars['gd_neighbourhood'] != '')
            $gd_neighbourhood = urldecode($wp->query_vars['gd_neighbourhood']);

        if (!($gd_country == '' && $gd_region == '' && $gd_city == '')) {
            $default_location = geodir_get_default_location();

            if ($add_location_url) {
                if ($show_location_url != 'all') {
                    /*
                     * @todo i don't see the point in this code so i am removing it. (stiofan)
                     */
                    /*
					if (!$gd_region || $gd_region == '') {
						if ($gd_ses_region = $gd_session->get('gd_region'))
							$gd_region = $gd_ses_region;
						else
							$gd_region = $default_location->region_slug;
					}

					if (!$gd_city || $gd_city == '') {
						if ($gd_ses_city = $gd_session->get('gd_city'))
							$gd_city = $gd_ses_city;
						else
							$gd_city = $default_location->city_slug;

						$base_location_link = geodir_get_location_link('base') ;
						wp_redirect($base_location_link . '/' .$gd_country . '/' . $gd_region . '/' . $gd_city )	;
						exit();
					}
					*/
                }
            }

            // Check location exists or not.
            $check_args = array(
                'what' => 'city',
                'city_val' => $gd_city,
                'region_val' => $gd_region,
                'country_val' => $gd_country,
                'country_column_name' => 'country_slug',
                'region_column_name' => 'region_slug',
                'city_column_name' => 'city_slug',
                'location_link_part' => false,
                'compare_operator' => '',
                'format' => array('type' => 'array')
            );
            $location_exists = geodir_get_location_array($check_args);

            if (empty($location_exists)) {
                geodir_unset_location();

                foreach ($wp->query_vars as $key => $vars) {
                    unset($wp->query_vars[$key]);
                }
                $wp->query_vars['error'] = '404';
                return;
            }

            $args = array(
                'what' => 'city',
                'city_val' => $gd_city,
                'region_val' => $gd_region,
                'country_val' => $gd_country,
                'country_column_name' => 'country_slug',
                'region_column_name' => 'region_slug',
                'city_column_name' => 'city_slug',
                'location_link_part' => false,
                'compare_operator' => ''
            );
            $location_array = geodir_get_location_array($args);
            if (!empty($location_array)) {
                $gd_session->set('gd_multi_location', 1);
                $gd_session->set('gd_country', $gd_country);
                $gd_session->set('gd_region', $gd_region);
                $gd_session->set('gd_city', $gd_city);
                $gd_session->set('gd_neighbourhood', $gd_neighbourhood);

                $wp->query_vars['gd_country'] = $gd_country;
                $wp->query_vars['gd_region'] = $gd_region;
                $wp->query_vars['gd_city'] = $gd_city;
                $wp->query_vars['gd_neighbourhood'] = $gd_neighbourhood;
            } else {
                geodir_unset_location();

                foreach ($wp->query_vars as $key => $vars) {
                    unset($wp->query_vars[$key]);
                }
                $wp->query_vars['error'] = '404';
            }
        } else {
            geodir_unset_location();
        }
    } else if (isset($wp->query_vars['post_type']) && $wp->query_vars['post_type'] != '') {
        if (!is_admin()) {
            $requested_post_type = $wp->query_vars['post_type'];

            // check if this post type is geodirectory post types
            $post_type_array = geodir_get_posttypes();

            if (in_array($requested_post_type, $post_type_array)) {
                // now u can apply geodirectory related manipulation.
                // echo "good: it is geodirectory post type<br />" ;
                // print_r($wp->query_vars) ;
            }
        }
    } else {
        $is_geodir_taxonomy = false;
        $geodir_taxonomy = '';
        $geodir_post_type = '';
        $geodir_term = '';
        $taxonomy_query_var = '';
        $is_geodir_location_found = false;
        $geodir_set_location_session = false;
        
        $gd_country = '';
        $gd_region = '';
        $gd_city = '';
        $gd_neighbourhood = '';
    
        $geodir_taxonomis = geodir_get_taxonomies('', true);
        if (!empty($geodir_taxonomis)) {
            foreach ($geodir_taxonomis as $taxonomy) {
                if ($taxonomy && array_key_exists($taxonomy, $wp->query_vars)) {
                    $is_geodir_taxonomy = true;
                    $geodir_taxonomy = $taxonomy;
                    $geodir_post_type = str_replace('category', '', $taxonomy);
                    $geodir_post_type = str_replace('_tags', '', $geodir_post_type);
                    $geodir_term = $wp->query_vars[$geodir_taxonomy];
                    break;
                }
            }
        }

        $is_location_less = !empty( $geodir_post_type ) && function_exists( 'geodir_cpt_no_location' ) && geodir_cpt_no_location( $geodir_post_type ) ? true : false;
        if ( $is_location_less ) {
            $add_location_url = false;
            $hide_country = true;
            $hide_region = true;
            $hide_city = true;
            $neighbourhood_active = false;
        }
        
        if ($is_geodir_taxonomy) {
            $taxonomy_query_var = $geodir_term;
            $geodir_terms = explode('/', $geodir_term);
            $geodir_count_terms = count($geodir_terms);
            $geodir_reverse_terms = array_reverse($geodir_terms);
            $geodir_last_term = end($geodir_terms);
            
            $wp->query_vars['post_type'] = $geodir_post_type;
            
            $location_args = array(
                'country_column_name' => 'country_slug',
                'region_column_name' => 'region_slug',
                'city_column_name' => 'city_slug',
                'neighbourhood_column_name' => 'hood_slug',
                'location_link_part' => false,
                'compare_operator' => '',
                'format' => array(
                    'type' => 'array'
                )
            );
            
            $location_structure = array();
            if (!$hide_country) {
                $location_structure[] = 'country';
            }
            if (!$hide_region) {
                $location_structure[] = 'region';
            }
            if (!$hide_city) {
                $location_structure[] = 'city';
            }
            if ($neighbourhood_active) {
                $location_structure[] = 'neighbourhood';
            }
            
            // Check cpt/location url structure.
            $maybe_location = !empty($location_structure) && $geodir_count_terms <= count($location_structure) ? true : false;
            
            if ($maybe_location) {
                $what = $location_structure[$geodir_count_terms - 1];
                $find_location_args = array('what' => $what);
                
                foreach ($geodir_terms as $key => $term) {
                    $find_location_args[ $location_structure[ $key ] . '_val' ] = urldecode($term);
                }
                
                $location = geodir_get_location_array(array_merge($location_args, $find_location_args));

                if (!empty($location)) {
                    $is_listing_location = true;
                    $taxonomy_query_var = '';
                    $is_geodir_location_found = true;
                    $geodir_set_location_session = true;
                    
                    $gd_country = isset($find_location_args['country_val']) ? $find_location_args['country_val'] : '';
                    $gd_region = isset($find_location_args['region_val']) ? $find_location_args['region_val'] : '';
                    $gd_city = isset($find_location_args['city_val']) ? $find_location_args['city_val'] : '';
                    $gd_neighbourhood = isset($find_location_args['neighbourhood_val']) ? $find_location_args['neighbourhood_val'] : '';
                } else {
                    $maybe_location = false;
                }
            }
            
            // Check cpt/location/term & cpt/term url structure.
            if (!$is_listing_location) {
                $maybe_term = geodir_term_exists($geodir_last_term, $geodir_taxonomy) ? true : false;
                
                if ($maybe_term) {
                    $location_vars = $geodir_terms;
                    array_pop($location_vars);

                    $excempt_terms = array();
                    if (!empty($location_vars)) {
                        if (!empty($location_structure)) {
                            $location_count_terms = count($location_vars);
                            
                            for ($i = 0; $i <= $location_count_terms; $i++) {
                                if (empty($location_vars)) {
                                    break;
                                }

                                if (count($location_vars) > count($location_structure)) {
                                    $excempt_terms[] = end($location_vars);
                                    array_pop($location_vars);
                                    continue;
                                }
                                
                                $what = $location_structure[count($location_vars) - 1];
                                $find_location_args = array('what' => $what);
                                
                                foreach ($location_vars as $key => $term) {
                                    $find_location_args[ $location_structure[ $key ] . '_val' ] = urldecode($term);
                                }
                                
                                $location = geodir_get_location_array(array_merge($location_args, $find_location_args));

                                if (!empty($location)) {
                                    $is_geodir_location_found = true;
                                    $geodir_set_location_session = true;
                                    
                                    $gd_country = isset($find_location_args['country_val']) ? $find_location_args['country_val'] : '';
                                    $gd_region = isset($find_location_args['region_val']) ? $find_location_args['region_val'] : '';
                                    $gd_city = isset($find_location_args['city_val']) ? $find_location_args['city_val'] : '';
                                    $gd_neighbourhood = isset($find_location_args['neighbourhood_val']) ? $find_location_args['neighbourhood_val'] : '';
                                    break;
                                } else {
                                    $excempt_terms[] = end($location_vars); // parent-cat/sub-cat
                                    array_pop($location_vars);
                                }
                            }
                            
                            $excempt_terms = !empty($excempt_terms) ? array_reverse($excempt_terms) : $excempt_terms;
                        } else {
                            $excempt_terms = $location_vars;
                        }
                        
                        $taxonomy_query_var = '';
                        if (!empty($excempt_terms)) {
                            foreach ($excempt_terms as $excempt_term) {
                                if (!geodir_term_exists($excempt_term, $geodir_taxonomy)) {
                                    $maybe_term = false;
                                }
                            }
                            
                            $taxonomy_query_var = implode('/', $excempt_terms) . '/';
                        }
                        $taxonomy_query_var .= $geodir_last_term;
                    } else {
                        $taxonomy_query_var = $geodir_last_term; // Term url. (cpt/term)
                    }
                }
                
                $is_listing_term = $maybe_term;
            }
            
            // Check cpt/location/term/detail & cpt/location/detail & cpt/term/detail & cpt/detail url structure.
            if (!$is_listing_location && !$is_listing_term) {
                $total_detail_var = 1;
                if ($add_location_url) {
                    if ($show_location_url == 'all') {
                        $total_detail_var += 3;
                    } else if ($show_location_url == 'country_city' || $show_location_url == 'region_city') {
                        $total_detail_var += 2;
                    } else {
                        $total_detail_var += 1;
                    }
                }
                if ($add_categories_url) {
                    $total_detail_var += 1;
                }
                
                $maybe_detail = $total_detail_var == $geodir_count_terms ? true : false;
                if ($maybe_detail) {
                    $taxonomy_query_var = '';
                    
                    $geodir_post = get_posts(array(
                        'name' => $geodir_last_term,
                        'posts_per_page' => 1,
                        'post_type' => $geodir_post_type,
                    ));

                    if (empty($geodir_post)) {
                        $geodir_post = get_posts(array(
                            'name' => $geodir_last_term,
                            'posts_per_page' => 1,
                            'post_type' => $geodir_post_type,
                            'post_status' => array('draft', 'pending'),
                            'suppress_filters' => false,
                        ));
                    }
                    
                    $maybe_detail = !empty($geodir_post) && !empty($geodir_post[0]->post_status) && $geodir_post[0]->post_status == 'publish' ? $maybe_detail : false;
                    
                    if ($maybe_detail && $add_location_url) {
                        $find_location_args = array('what' => 'city');
                        
                        if ($show_location_url == 'all') {
                            $find_location_args['country_val'] = urldecode($geodir_terms[0]);
                            $find_location_args['region_val'] = urldecode($geodir_terms[1]);
                            $find_location_args['city_val'] = urldecode($geodir_terms[2]);
                        } else if ($show_location_url == 'country_city') {
                            $find_location_args['country_val'] = urldecode($geodir_terms[0]);
                            $find_location_args['city_val'] = urldecode($geodir_terms[1]);
                        } else if ($show_location_url == 'region_city') {
                            $find_location_args['region_val'] = urldecode($geodir_terms[0]);
                            $find_location_args['city_val'] = urldecode($geodir_terms[1]);
                        } else {
                            $find_location_args['city_val'] = urldecode($geodir_terms[0]);
                        }
                        
                        $gd_country = isset($find_location_args['country_val']) ? $find_location_args['country_val'] : '';
                        $gd_region = isset($find_location_args['region_val']) ? $find_location_args['region_val'] : '';
                        $gd_city = isset($find_location_args['city_val']) ? $find_location_args['city_val'] : '';
                        
                        $location = geodir_get_location_array(array_merge($location_args, $find_location_args));

                        if (!empty($location)) {
                            $is_geodir_location_found = true;
                        } else {
                            $maybe_detail = false;
                        }
                    }
                    
                    if ($maybe_detail && $add_categories_url) {
                        $taxonomy_query_var = $geodir_reverse_terms[1];
                        
                        if (geodir_term_exists($geodir_reverse_terms[1], $geodir_taxonomy)) {
                            $maybe_detail = true;
                        } else {
                            $maybe_detail = false;
                        }
                    }
                }
                
                $is_listing_detail = $maybe_detail;
                
                if ($is_listing_detail) {
                    $wp->query_vars[$geodir_post_type] = $geodir_last_term;
                    $wp->query_vars['name'] = $geodir_last_term;
                    
                    if ($gd_ses_neighbourhood = $gd_session->get('gd_neighbourhood')) {
                        $gd_neighbourhood = $gd_ses_neighbourhood;
                    }
                }
            }
            
            // now check if there is location parts in the url or not
            if ($add_location_url) {
                if ($is_geodir_location_found) {
                    // if location still not found then clear location related session variables
                    if ($geodir_set_location_session) {
                        $gd_session->set('gd_multi_location', 1);
                        $gd_session->set('gd_country', $gd_country);
                        $gd_session->set('gd_region', $gd_region);
                        $gd_session->set('gd_city', $gd_city);
                        $gd_session->set('gd_neighbourhood', $gd_neighbourhood); // EXTRA
                    }
                    
                    $wp->query_vars['gd_country'] = $gd_country;
                    $wp->query_vars['gd_region'] = $gd_region;
                    $wp->query_vars['gd_city'] = $gd_city;
                    $wp->query_vars['gd_neighbourhood'] = $gd_neighbourhood;
                } else {
                    $gd_country = '';
                    $gd_region = '';
                    $gd_city = '';
                    $gd_neighbourhood = '';
                }
            }
            
            $wp->query_vars[$geodir_taxonomy] = $taxonomy_query_var;

            if ($wp->query_vars[$geodir_taxonomy] == '') {
                unset($wp->query_vars[$geodir_taxonomy]);
            }
            
            if (!$is_listing_location && !$is_listing_term && !$is_listing_detail) {
                foreach ($wp->query_vars as $key => $vars) {
                    unset($wp->query_vars[$key]);
                }

                $wp->query_vars['error'] = '404';
            }
        }
    }

    /* Integrated to first time load redirect setting in advance search addon.
    if (isset($wp->query_vars['gd_is_geodir_page']) && is_array($wp->query_vars) && (count($wp->query_vars) == 1 || count($wp->query_vars) == 2)) {
        if (!$gd_session->get('gd_location_filter_on_site_load')) {
            $gd_session->set('gd_location_filter_on_site_load', 1);

            if (get_option('geodir_result_by_location') == 'default') {
                $gd_session->set('gd_location_default_loaded', 1);//set that the default location was added on first load

                $location_default = geodir_get_default_location();
                $default_country_slug = isset($location_default->country_slug) ? $location_default->country_slug : '';
                $default_region_slug = isset($location_default->region_slug) ? $location_default->region_slug : '';
                $default_city_slug = isset($location_default->city_slug) ? $location_default->city_slug : '';

                $gd_session->set('gd_multi_location', 1);
                $gd_session->set('gd_country', $default_country_slug);
                $gd_session->set('gd_region', $default_region_slug);
                $gd_session->set('gd_city', $default_city_slug);

                $wp->query_vars['gd_country'] = $default_country_slug;
                $wp->query_vars['gd_region'] = $default_region_slug;
                $wp->query_vars['gd_city'] = $default_city_slug;
            }
        }
    } else {
        $gd_session->set('gd_location_filter_on_site_load', 1);
        $gd_session->un_set('gd_location_default_loaded');
    }
    */

    // Unset location session if gd page and location not set.
    if (isset($wp->query_vars['gd_is_geodir_page']) && !isset($wp->query_vars['gd_country'])) {
        geodir_unset_location();
    }

    // Unset location session if on homepage and it's not a GD page
    if (!isset($wp->query_vars['gd_is_geodir_page']) && strpos(str_replace(array("https://", "http://", "www."), array('', '', ''), geodir_curPageURL()), str_replace(array("https://", "http://", "www."), array('', '', ''), home_url('', 'http'))) !== false) {
        geodir_unset_location();
    }

    // Set location vars from current url.
    if (!$is_listing_detail) {
        if (isset($wp->query_vars['gd_is_geodir_page']) && isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country']) {
            $country_slug = $wp->query_vars['gd_country'];
            $region_slug = $wp->query_vars['gd_region'];
            $city_slug = $wp->query_vars['gd_city'];

            $default_country = $country_slug == '' && get_option('geodir_enable_country') == 'default' ? true : false;
            $default_region = $region_slug == '' && get_option('geodir_enable_region') == 'default' ? true : false;

            if ($default_country || $default_region) {
                $default_location = geodir_get_default_location();

                $country_slug = $default_country ? $default_location->country_slug : $country_slug;
                $region_slug = $default_region ? $default_location->region_slug : $region_slug;
            }

            if ($city_slug != '') {
                $location_info = geodir_city_info_by_slug($city_slug, $country_slug, $region_slug);

                if (!empty($location_info)) {
                    $country_slug = $location_info->country_slug;
                    $region_slug = $location_info->region_slug;
                    $city_slug = $location_info->city_slug;
                }
            }

            $gd_session->set('gd_multi_location', 1);
            $gd_session->set('gd_country', $country_slug);
            $gd_session->set('gd_region', $region_slug);
            $gd_session->set('gd_city', $city_slug);
        }

        if ($gd_session->get('gd_multi_location') == 1) {
            $wp->query_vars['gd_country'] = $gd_session->get('gd_country');
            $wp->query_vars['gd_region'] = $gd_session->get('gd_region');
            $wp->query_vars['gd_city'] = $gd_session->get('gd_city');
        }
    }

    // now check if there is location parts in the url or not
    if ($add_location_url || (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == geodir_location_page_id())) {
        if ($geodir_location_part == 'all') {
        } else if ($geodir_location_part == 'country_city') {
            if (isset($wp->query_vars['gd_region']))
                $wp->query_vars['gd_region'] = '';
        } else if ($geodir_location_part == 'region_city') {
            if (isset($wp->query_vars['gd_country']))
                $wp->query_vars['gd_country'] = '';
        } else if ($geodir_location_part == 'city') {
            if (isset($wp->query_vars['gd_country']))
                $wp->query_vars['gd_country'] = '';

            if (isset($wp->query_vars['gd_region']))
                $wp->query_vars['gd_region'] = '';
        }
    } else {
        if (isset($wp->query_vars['gd_country']))
            $wp->query_vars['gd_country'] = '';
        if (isset($wp->query_vars['gd_region']))
            $wp->query_vars['gd_region'] = '';
        if (isset($wp->query_vars['gd_city']))
            $wp->query_vars['gd_city'] = '';
        if ($neighbourhood_active && isset($wp->query_vars['gd_neighbourhood'])) {
            $wp->query_vars['gd_neighbourhood'] = '';
            $gd_neighbourhood = '';
        }
    }
    
    $neighbourhood = $neighbourhood_active && $gd_neighbourhood && !empty($wp->query_vars['gd_city']) ? geodir_location_check_is_neighbourhood($gd_neighbourhood, $wp->query_vars['gd_city'], $wp->query_vars['gd_region'], $wp->query_vars['gd_country']) : '';

    if ($neighbourhood_active) {
        if (!empty($neighbourhood) && isset($neighbourhood->hood_slug)) {
            $wp->query_vars['gd_neighbourhood'] = $neighbourhood->hood_slug;
            $gd_session->set('gd_neighbourhood', $neighbourhood->hood_slug);
        } else if (isset($wp->query_vars['gd_neighbourhood']) && $wp->query_vars['gd_neighbourhood'] && empty($neighbourhood)){
            foreach ($wp->query_vars as $key => $vars) {
                unset($wp->query_vars[$key]);
            }
            $wp->query_vars['error'] = '404';
            return;
        }
    } else {
        if (isset($wp->query_vars['gd_neighbourhood'])) {
            unset($wp->query_vars['gd_neighbourhood']);
        }
        $gd_session->un_set('gd_neighbourhood');
    }
}

add_action('pre_get_posts', 'geodir_listing_loop_location_filter', 2);
/**
 * Adds location filter to the query.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wp_query WordPress Query object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param object $query The WP_Query instance.
 */
function geodir_listing_loop_location_filter($query)
{
    global $wp_query, $geodir_post_type, $table, $plugin_prefix, $table, $term;

    // fix wp_reset_query for popular post view widget
    if (!geodir_is_geodir_page()) {
        return;
    }

    $apply_location_filter = true;
    if (isset($query->query_vars['gd_location'])) {
        $apply_location_filter = $query->query_vars['gd_location'] ? true : false;
    }

    if (isset($query->query_vars['is_geodir_loop']) && $query->query_vars['is_geodir_loop'] && !is_admin() && !geodir_is_page('add-listing') && !isset($_REQUEST['geodir_dashbord']) && $apply_location_filter) {
        geodir_post_location_where(); // this function is in geodir_location_functions.php
    }
}

/**
 * Filters the where clause.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_post_location_where()
{
    global $snear;
    if ((is_search() && $_REQUEST['geodir_search'])) {
        add_filter('posts_where', 'searching_filter_location_where', 2);

        if ($snear != '')
            add_filter('posts_where', 'searching_filter_location_where', 2);
    }

    if (!geodir_is_page('detail'))
        add_filter('posts_where', 'geodir_default_location_where', 2);/**/

}

/**
 * Adds the location filter to the where clause.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $where The WHERE clause of the query.
 * @return string Filtered WHERE clause.
 */
function searching_filter_location_where($where)
{
    global $table;
    $city_where = '';
    // Filter-Location-Manager // City search ..
    if (isset($_REQUEST['scity'])) {
        if (is_array($_REQUEST['scity']) && !empty($_REQUEST['scity'])) {
            $awhere = array();
            foreach ($_REQUEST['scity'] as $city) {
                //$city_where .= "'".$city."',";
                //$where .= " FIND_IN_SET(".$_REQUEST['scity'].", post_locations), ";
                $awhere[] = " post_locations LIKE '[" . $_REQUEST['scity'] . "],%' ";
            }
            $where .= " ( " . implode(" OR ", $awhere) . " ) ";
        } elseif ($_REQUEST['scity'] != '') {
            //$city_where = "'".$_REQUEST['scity']."'";
            //$where .= " FIND_IN_SET(".$_REQUEST['scity'].", post_locations) ";
            $where .= " post_locations LIKE '[" . $_REQUEST['scity'] . "],%' ";
        }

        /*if(!empty($city_where))
            $where .= " AND ".POST_LOCATION_TABLE.".city IN ( ". trim($city_where,',') ." ) ";*/

    }
    return $where;
}


add_action('geodir_filter_widget_listings_fields', 'geodir_filter_widget_listings_fields_set', 10, 2);
/**
 * Filters the Field clause of the query.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $fields Fields string.
 * @param string $table Table name.
 * @return string Filtered field clause.
 */
function geodir_filter_widget_listings_fields_set($fields, $table)
{
    global $gd_session;
    // my location set start
    if ($gd_session->get('all_near_me')) {
        global $wpdb;
        $mylat = $gd_session->get('user_lat');
        $mylon = $gd_session->get('user_lon');
        $DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));

        $fields .= $wpdb->prepare(", (" . $DistanceRadius . " * 2 * ASIN(SQRT(POWER(SIN((ABS(%s) - ABS(" . $table . ".post_latitude)) * PI() / 180 / 2), 2) + COS(ABS(%s) * PI() / 180) * COS(ABS(" . $table . ".post_latitude) * PI() / 180) * POWER(SIN((%s - " . $table . ".post_longitude) * PI() / 180 / 2), 2)))) AS distance ", $mylat, $mylat, $mylon);
    }
    return $fields;
}

add_action('geodir_filter_widget_listings_orderby', 'geodir_filter_widget_listings_orderby_set', 10, 2);
/**
 * Adds the location filter to the orderby clause.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $orderby Order by clause string.
 * @param string $table Table name.
 * @return string Filtered Orderby Clause.
 */
function geodir_filter_widget_listings_orderby_set($orderby, $table)
{
    global $gd_session;
    // my location set start
    if ($gd_session->get('all_near_me')) {
        $orderby = " distance, " . $orderby;
    }
    return $orderby;
}

/**
 * Adds the default location filter to the where clause.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wp_query WordPress Query object.
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global object $wp WordPress object.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $where The WHERE clause of the query.
 * @param string $p_table Post table.
 * @return mixed|string Filtered where clause.
 */
function geodir_default_location_where($where, $p_table = '')
{
    global $wp_query, $wpdb, $table, $wp, $plugin_prefix, $gd_session;

    $allowed_location = apply_filters('geodir_location_allowed_location_where', true, $wp->query_vars, $table, $wp_query, $p_table);
    if (!$allowed_location) {
        return $where;
    }

    // my location set start
    if ($gd_session->get('all_near_me') && !isset($_REQUEST['lat_ne'])) {
        $my_lat = 0;
        $my_lon = 0;

        if (isset($_REQUEST['my_lat']) && isset($_REQUEST['my_lon'])) {
            $my_lat = filter_var($_REQUEST['my_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $my_lon = filter_var($_REQUEST['my_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        $mylat = $my_lat ? $my_lat : $gd_session->get('user_lat');
        $mylon = $my_lon ? $my_lon : $gd_session->get('user_lon');

        if (is_numeric($gd_session->get('near_me_range'))) {
            $dist = $gd_session->get('near_me_range');
        } else if (get_option('geodir_near_me_dist') != '') {
            $dist = get_option('geodir_near_me_dist');
        } else {
            $dist = 200;
        }

        $lon1 = $mylon - $dist / abs(cos(deg2rad($mylat)) * 69);
        $lon2 = $mylon + $dist / abs(cos(deg2rad($mylat)) * 69);
        $lat1 = $mylat - ($dist / 69);
        $lat2 = $mylat + ($dist / 69);

        $rlon1 = is_numeric(min($lon1, $lon2)) ? min($lon1, $lon2) : '';
        $rlon2 = is_numeric(max($lon1, $lon2)) ? max($lon1, $lon2) : '';
        $rlat1 = is_numeric(min($lat1, $lat2)) ? min($lat1, $lat2) : '';
        $rlat2 = is_numeric(max($lat1, $lat2)) ? max($lat1, $lat2) : '';

        $where .= " AND post_latitude between $rlat1 and $rlat2 AND post_longitude between $rlon1 and $rlon2 ";
        return $where;
    }

    $where = str_replace("0 = 1", "1=1", $where);
    
    $session_loc = geodir_get_current_location_terms('session');


    $country = '';
    $region = '';
    $city = '';
    $neighbourhood = '';

    if (isset($session_loc['gd_country']) && $session_loc['gd_country']) {
        $country = $session_loc['gd_country'];
    }

    if (!$country || $country == '') {
        // check if we have country  in query vars
        if (isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country'] != '') {
            $country = $wp->query_vars['gd_country'];
        }
    }

    if (isset($session_loc['gd_region']) && $session_loc['gd_region']) {
        $region = $session_loc['gd_region'];
    }

    if (!$region || $region == '') {
        // check if we have region in query vars
        if (isset($wp->query_vars['gd_region']) && $wp->query_vars['gd_region'] != '') {
            $region = $wp->query_vars['gd_region'];
        }
    }

    if (isset($session_loc['gd_city']) && $session_loc['gd_city']) {
        $city = $session_loc['gd_city'];
    }

    if (!$city || $city == '') {
        // check if we have city in query vars
        if (isset($wp->query_vars['gd_city']) && $wp->query_vars['gd_city'] != '') {
            $city = $wp->query_vars['gd_city'];
        }
    }

    if ($country && $country != '') {
        $where .= " AND post_locations LIKE '%,[" . $country . "]' ";
    }

    if ($region && $region != '') {
        $where .= " AND post_locations LIKE '%,[" . $region . "],%' ";
    }

    if ($city && $city != '') {
        $where .= " AND post_locations LIKE '[" . $city . "],%' ";
    }

    if (get_option('location_neighbourhoods')) {
        if ($gd_ses_neighbourhood = $gd_session->get('gd_neighbourhood')) {
            $neighbourhood = $gd_ses_neighbourhood;
        }

        if (!$neighbourhood || $neighbourhood == '') {
            // check if we have neighbourhood in query vars
            if (isset($wp->query_vars['gd_neighbourhood']) && $wp->query_vars['gd_neighbourhood'] != '') {
                $neighbourhood = $wp->query_vars['gd_neighbourhood'];
            }
        }

        // added for map calls
        if (empty($neighbourhood) && !empty($_REQUEST['gd_neighbourhood'])) {
            $neighbourhood = $_REQUEST['gd_neighbourhood'];

            if (isset($_REQUEST['gd_posttype']) && $_REQUEST['gd_posttype'] != '') {
                $p_table = "pd";
            }
        }

        $post_table = $table != '' ? $table . '.' : ''; /* fixed db error when $table is not set */

        if (!empty($p_table)) {
            $post_table = $p_table . '.';
        }

        if (is_array($neighbourhood) && !empty($neighbourhood)) {
            $neighbourhood_length = count($neighbourhood);
            $format = array_fill(0, $neighbourhood_length, '%s');
            $format = implode(',', $format);

            $where .= $wpdb->prepare(" AND " . $post_table . "post_neighbourhood IN ($format) ", $neighbourhood);
        } else if (!is_array($neighbourhood) && !empty($neighbourhood)) {
            $where .= $wpdb->prepare(" AND " . $post_table . "post_neighbourhood LIKE %s ", $neighbourhood);
        }
    }

    return $where;
}

/**************************
 * /* LOCATION AJAX Handler
 ***************************/
add_action('wp_ajax_geodir_location_ajax', 'geodir_location_ajax_handler'); // it in geodir_location_functions.php
add_action('wp_ajax_nopriv_geodir_location_ajax', 'geodir_location_ajax_handler');
// AJAX Handler //
/**
 * Handles ajax request.
 *
 * @since 1.0.0
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 *              Location filter added in back-end post type listing pages.
 * @package GeoDirectory_Location_Manager
 */
function geodir_location_ajax_handler()
{
    if (isset($_REQUEST['gd_loc_ajax_action']) && $_REQUEST['gd_loc_ajax_action'] != '') {
        switch ($_REQUEST['gd_loc_ajax_action']) {
            case 'get_location' :
                $neighbourhood_active = get_option('location_neighbourhoods');
                $which_location = isset($_REQUEST['gd_which_location']) ? trim($_REQUEST['gd_which_location']) : '';
                $formated_for = isset($_REQUEST['gd_formated_for']) ? trim($_REQUEST['gd_formated_for']) : '';

                if ($which_location != '') {
                    $city_val = isset($_REQUEST['gd_city_val']) && $_REQUEST['gd_city_val'] != '' ? $_REQUEST['gd_city_val'] : '';
                    $region_val = isset($_REQUEST['gd_region_val']) && $_REQUEST['gd_region_val'] != '' ? $_REQUEST['gd_region_val'] : '';
                    $country_val = isset($_REQUEST['gd_country_val']) && $_REQUEST['gd_country_val'] != '' ? $_REQUEST['gd_country_val'] : '';
                    $country_val = isset($_REQUEST['gd_country_val']) && $_REQUEST['gd_country_val'] != '' ? $_REQUEST['gd_country_val'] : '';
                    $task = !empty($_REQUEST['_task']) ? $_REQUEST['_task'] : '';
                    $max_num_pages = !empty($_REQUEST['_pages']) ? absint($_REQUEST['_pages']) : 1;

                    $spage = !empty($_REQUEST['spage']) && $_REQUEST['spage'] > 0 ? absint($_REQUEST['spage']) : 0;
                    $no_of_records = !empty($_REQUEST['lscroll']) ? absint($_REQUEST['lscroll']) : 0;
                    if (!$no_of_records > 0) {
                        $default_limit = (int)get_option('geodir_location_no_of_records');
                        $no_of_records = absint($default_limit) > 0 ? $default_limit : 50;
                    }

                    $location_args = array(
                        'what' => $which_location,
                        'city_val' => $city_val,
                        'region_val' => $region_val,
                        'country_val' => $country_val,
                        'compare_operator' => 'like',
                        'country_column_name' => 'country',
                        'region_column_name' => 'region',
                        'city_column_name' => 'city',
                        'location_link_part' => true,
                        'order_by' => ' asc ',
                        'no_of_records' => $no_of_records,
                        'format' => array('type' => 'array'),
                        'spage' => $spage
                    );

                    if ($neighbourhood_active) {
                        $neighbourhood_val = isset($_REQUEST['gd_neighbourhood_val']) && $_REQUEST['gd_neighbourhood_val'] != '' ? $_REQUEST['gd_neighbourhood_val'] : '';
                        $location_args['neighbourhood_val'] = $neighbourhood_val;
                        $location_args['neighbourhood_column_name'] = 'hood_name';
                    }
                    
                    if ($task != 'loadmore') {
                        $total = geodir_get_location_array(array_merge($location_args, array('counts_only' => true)), false);
                        $max_num_pages = ceil($total / $no_of_records);
                    }

                    $location_array = geodir_get_location_array($location_args);

                    if ($formated_for == 'location_switcher') {
                        $base_location_link = geodir_get_location_link('base');

                        if (!empty($location_array)) {
                            if ($task == 'loadmore') {
                                $load_more = $max_num_pages > ($spage + 1) ? true : false;
                            } else {
                                $load_more = $max_num_pages > 1 ? true : false;
                            }
                        
                            $has_arrow = true;
                            if (($neighbourhood_active && $which_location == 'neighbourhood') || (!$neighbourhood_active && $which_location == 'city')) {
                                $has_arrow = false;
                            }

                            $arrow_html = $has_arrow ? '<span class="geodir_loc_arrow"><a href="javascript:void(0);">&nbsp;</a></span>' : '';

                            foreach ($location_array as $location_item) {
                                if (empty($location_item->{$which_location})) {
                                    continue;
                                }
                                $location_name = $which_location == 'country' ? __($location_item->{$which_location}, 'geodirectory') : $location_item->{$which_location};

                                echo '<li class="geodir_loc_clearfix"><a href="' . geodir_location_permalink_url($base_location_link . $location_item->location_link) . '">' . stripslashes($location_name) . '</a>' . $arrow_html . '</li>';
                            }
                            if ($load_more) {
                                echo '<li class="geodir_loc_clearfix gd-loc-loadmore"><button class="geodir_button" data-pages="' . $max_num_pages . '" data-next="' . ( $spage + 1 ) . '" data-title="' . esc_attr__( 'Loading...', 'geodirlocation' ) . '"><i class="fa fa-refresh fa-fw" aria-hidden="true"></i> <font>' . __( 'Load more', 'geodirlocation' ) . '<font></button></li>';
                            }
                        } else {
                            if ($task == 'loadmore') {
                                exit;
                            }
                            echo '<li class="geodir_loc_clearfix gdlm-no-results">' . __("No Results", 'geodirlocation') . '</li>';
                        }
                    } else {
                        print_r($location_array);
                    }
                    exit();
                }
                break;
            case 'fill_location': {
                $neighbourhood_active = get_option('location_neighbourhoods');

                $gd_which_location = isset($_REQUEST['gd_which_location']) ? strtolower(trim($_REQUEST['gd_which_location'])) : '';
                $term = isset($_REQUEST['term']) ? trim($_REQUEST['term']) : '';

                $base_location = geodir_get_location_link('base');
                $current_location_array;
                $selected = '';
                $country_val = '';
                $region_val = '';
                $city_val = '';
                $country_val = geodir_get_current_location(array('what' => 'country', 'echo' => false));
                $region_val = geodir_get_current_location(array('what' => 'region', 'echo' => false));
                $city_val = geodir_get_current_location(array('what' => 'city', 'echo' => false));
                $neighbourhood_val = $neighbourhood_active ? geodir_get_current_location(array('what' => 'neighbourhood', 'echo' => false)) : '';

                $item_set_selected = false;

                if (isset($_REQUEST['spage']) && $_REQUEST['spage'] != '') {
                    $spage = $_REQUEST['spage'];
                } else {
                    $spage = '';
                }

                if (isset($_REQUEST['lscroll']) && $_REQUEST['lscroll'] != '') {
                    $no_of_records = '5';
                } else {
                    $no_of_records = '5';
                } // this was loading all locations when set to 0 on tab switch so we change to 5 to limit it.

                $location_switcher_list_mode = get_option('geodir_location_switcher_list_mode');
                if (empty($location_switcher_list_mode)) {
                    $location_switcher_list_mode = 'drill';
                }

                if ($location_switcher_list_mode == 'drill') {
                    $args = array(
                        'what' => $gd_which_location,
                        'country_val' => in_array($gd_which_location, array('region', 'city', 'neighbourhood')) ? $country_val : '',
                        'region_val' => in_array($gd_which_location, array('city', 'neighbourhood')) ? $region_val : '',
                        'echo' => false,
                        'no_of_records' => $no_of_records,
                        'format' => array('type' => 'array'),
                        'spage' => $spage
                    );

                    if ($gd_which_location == 'neighbourhood' && $city_val != '') {
                        $args['city_val'] = $city_val;
                    }
                } else {
                    $args = array(
                        'what' => $gd_which_location,
                        'echo' => false,
                        'no_of_records' => $no_of_records,
                        'format' => array('type' => 'array'),
                        'spage' => $spage
                    );
                }

                if ($term != '') {
                    if ($gd_which_location == 'neighbourhood') {
                        $args['neighbourhood_val'] = $term;
                    }

                    if ($gd_which_location == 'city') {
                        $args['city_val'] = $term;
                    }

                    if ($gd_which_location == 'region') {
                        $args['region_val'] = $term;
                    }

                    if ($gd_which_location == 'country') {
                        $args['country_val'] = $term;
                    }
                } else {
                    if ($gd_which_location == 'country' && $country_val != '') {
                        $args_current_location = array(
                            'what' => $gd_which_location,
                            'country_val' => $country_val,
                            'compare_operator' => '=',
                            'no_of_records' => '1',
                            'echo' => false,
                            'format' => array('type' => 'array')
                        );
                        $current_location_array = geodir_get_location_array($args_current_location, true);
                    }

                    if ($gd_which_location == 'region' && $region_val != '') {
                        $args_current_location = array(
                            'what' => $gd_which_location,
                            'country_val' => $country_val,
                            'region_val' => $region_val,
                            'compare_operator' => '=',
                            'no_of_records' => '1',
                            'echo' => false,
                            'format' => array('type' => 'array')
                        );
                        $current_location_array = geodir_get_location_array($args_current_location, true);
                    }

                    if ($gd_which_location == 'city' && $city_val != '') {
                        $args_current_location = array(
                            'what' => $gd_which_location,
                            'country_val' => $country_val,
                            'region_val' => $region_val,
                            'city_val' => $city_val,
                            'compare_operator' => '=',
                            'no_of_records' => '1',
                            'echo' => false,
                            'format' => array('type' => 'array')
                        );
                        $current_location_array = geodir_get_location_array($args_current_location, true);
                    }

                    if ($gd_which_location == 'neighbourhood' && $neighbourhood_val != '') {
                        $args_current_location = array(
                            'what' => $gd_which_location,
                            'country_val' => $country_val,
                            'region_val' => $region_val,
                            'city_val' => $city_val,
                            'neighbourhood_val' => $neighbourhood_val,
                            'compare_operator' => '=',
                            'no_of_records' => '1',
                            'echo' => false,
                            'format' => array('type' => 'array')
                        );

                        $current_location_array = geodir_get_location_array($args_current_location, true);
                    }
                    // if not searching then set to get exact matches
                    $args['compare_operator'] = 'in';
                }

                $location_array = geodir_get_location_array($args, true);
                // get country val in case of country search to get selected option

                if (!isset($_REQUEST['lscroll']))
                    echo '<option value="" disabled="disabled" style="display:none;" selected="selected">' . __('Select', 'geodirlocation') . '</option>';

                if (get_option('geodir_everywhere_in_' . $gd_which_location . '_dropdown') && !isset($_REQUEST['lscroll'])) {
                    echo '<option value="' . $base_location . '">' . __('Everywhere', 'geodirlocation') . '</option>';
                }

                $selected = '';
                $loc_echo = '';

                if (!empty($location_array)) {
                    foreach ($location_array as $locations) {
                        $selected = '';
                        $with_parent = isset($locations->label) ? true : false;

                        switch ($gd_which_location) {
                            case 'country':
                                if (strtolower($country_val) == strtolower(stripslashes($locations->country))) {
                                    $selected = ' selected="selected"';
                                }
                                $locations->country = __(stripslashes($locations->country), 'geodirectory');
                                break;
                            case 'region':
                                $country_iso2 = geodir_location_get_iso2($country_val);
                                $country_iso2 = $country_iso2 != '' ? $country_iso2 : $country_val;
                                $with_parent = $with_parent && strtolower($region_val . ', ' . $country_iso2) == strtolower(stripslashes($locations->label)) ? true : false;
                                if (strtolower($region_val) == strtolower(stripslashes($locations->region)) || $with_parent) {
                                    $selected = ' selected="selected"';
                                }
                                break;
                            case 'city':
                                $with_parent = $with_parent && strtolower($city_val . ', ' . $region_val) == strtolower(stripslashes($locations->label)) ? true : false;
                                if (strtolower($city_val) == strtolower(stripslashes($locations->city)) || $with_parent) {
                                    $selected = ' selected="selected"';
                                }
                                break;
                            case 'neighbourhood':
                                $with_parent = $with_parent && strtolower($neighbourhood_val . ', ' . $city_val) == strtolower(stripslashes($locations->label)) ? true : false;
                                if (strtolower($neighbourhood_val) == strtolower(stripslashes($locations->neighbourhood)) || $with_parent) {
                                    $selected = ' selected="selected"';
                                }
                                break;
                        }

                        echo '<option value="' . geodir_location_permalink_url($base_location . $locations->location_link) . '"' . $selected . '>' . ucwords(stripslashes($locations->{$gd_which_location})) . '</option>';

                        if (!$item_set_selected && $selected != '') {
                            $item_set_selected = true;
                        }
                    }
                }

                if (!empty($current_location_array) && !$item_set_selected && !isset($_REQUEST['lscroll'])) {
                    foreach ($current_location_array as $current_location) {
                        $selected = '';
                        $with_parent = isset($current_location->label) ? true : false;

                        switch ($gd_which_location) {
                            case 'country':
                                if (strtolower($country_val) == strtolower(stripslashes($current_location->country))) {
                                    $selected = ' selected="selected"';
                                }
                                $current_location->country = __(stripslashes($current_location->country), 'geodirectory');
                                break;
                            case 'region':
                                $country_iso2 = geodir_location_get_iso2($country_val);
                                $country_iso2 = $country_iso2 != '' ? $country_iso2 : $country_val;
                                $with_parent = $with_parent && strtolower($region_val . ', ' . $country_iso2) == strtolower(stripslashes($current_location->label)) ? true : false;
                                if (strtolower($region_val) == strtolower(stripslashes($current_location->region)) || $with_parent) {
                                    $selected = ' selected="selected"';
                                }
                                break;
                            case 'city':
                                $with_parent = $with_parent && strtolower($city_val . ', ' . $region_val) == strtolower(stripslashes($current_location->label)) ? true : false;
                                if (strtolower($city_val) == strtolower(stripslashes($current_location->city)) || $with_parent) {
                                    $selected = ' selected="selected"';
                                }
                                break;
                            case 'neighbourhood':
                                $with_parent = $with_parent && strtolower($neighbourhood_val . ', ' . $city_val) == strtolower(stripslashes($locations->label)) ? true : false;
                                if (strtolower($neighbourhood_val) == strtolower(stripslashes($locations->neighbourhood)) || $with_parent) {
                                    $selected = ' selected="selected"';
                                }
                                break;
                        }

                        echo '<option value="' . geodir_location_permalink_url($base_location . $current_location->location_link) . '"' . $selected . '>' . ucwords(stripslashes($current_location->{$gd_which_location})) . '</option>';
                    }
                }
                exit;
            }
                break;
            case 'fill_location_on_add_listing' :
                $selected = '';
                $country_val = (isset($_REQUEST['country_val'])) ? $_REQUEST['country_val'] : '';
                $region_val = (isset($_REQUEST['region_val'])) ? $_REQUEST['region_val'] : '';
                $city_val = (isset($_REQUEST['city_val'])) ? $_REQUEST['city_val'] : '';
                $compare_operator = (isset($_REQUEST['compare_operator'])) ? $_REQUEST['compare_operator'] : '=';

                if (isset($_REQUEST['term']) && $_REQUEST['term'] != '') {
                    if ($_REQUEST['gd_which_location'] == 'region') {
                        $region_val = $_REQUEST['term'];
                        $city_val = '';
                    } else if ($_REQUEST['gd_which_location'] == 'city') {
                        $city_val = $_REQUEST['term'];
                    }
                }

                if ($_REQUEST['gd_which_location'] != 'neighbourhood') {
                    $args = array(
                        'what' => $_REQUEST['gd_which_location'],
                        'country_val' => (strtolower($_REQUEST['gd_which_location']) == 'region' || strtolower($_REQUEST['gd_which_location']) == 'city') ? $country_val : '',
                        'region_val' => $region_val,
                        'city_val' => $city_val,
                        'echo' => false,
                        'compare_operator' => $compare_operator,
                        'format' => array('type' => 'array')
                    );
                    $location_array = geodir_get_location_array($args);
                } else {
                    $neighbourhood_val = !empty($_REQUEST['neighbourhood_val']) ? sanitize_text_field($_REQUEST['neighbourhood_val']) : '';
                    geodir_get_neighbourhoods_dl($city_val, $neighbourhood_val);
                    exit();
                }

                // get country val in case of country search to get selected option

                if ($_REQUEST['gd_which_location'] == 'region')
                    echo '<option  value="" >' . __('Select Region', 'geodirlocation') . '</option>';
                else
                    echo '<option  value="" >' . __('Select City', 'geodirlocation') . '</option>';

                if (!empty($location_array)) {
                    foreach ($location_array as $locations) {
                        $selected = '';
                        switch ($_REQUEST['gd_which_location']) {
                            case 'country':
                                if (strtolower($country_val) == strtolower(stripslashes($locations->country)))
                                    $selected = " selected='selected' ";
                                break;
                            case 'region':
                                if (strtolower($region_val) == strtolower(stripslashes($locations->region)))
                                    $selected = " selected='selected' ";
                                break;
                            case 'city':
                                if (strtolower($city_val) == strtolower(stripslashes($locations->city)))
                                    $selected = " selected='selected' ";
                                break;

                        }
                        echo '<option ' . $selected . ' value="' . ucwords(stripslashes($locations->{$_REQUEST['gd_which_location']})) . '" >' . ucwords(stripslashes($locations->{$_REQUEST['gd_which_location']})) . '</option>';
                    }

                } else {
                    if (isset($_REQUEST['term']) && $_REQUEST['term'] != '')
                        echo '<option value="' . sanitize_text_field($_REQUEST['term']) . '" >' . sanitize_text_field($_REQUEST['term']) . '</option>';
                }
                exit();
                break;
            case 'get_location_options':
                $return = array();
                if (!isset($_REQUEST['cn'])) {
                    echo json_encode($return);
                    exit;
                }
                $args = array();
                $args['filter_by_non_restricted'] = false;
                $args['location_link_part'] = false;
                $args['compare_operator'] = '=';
                $args['country_column_name'] = 'country_slug';
                $args['region_column_name'] = 'region_slug';
                $args['country_val'] = $_REQUEST['cn'];

                $args['fields'] = 'region AS title, region_slug AS slug';
                $args['order'] = 'region';
                $args['group_by'] = 'region_slug';

                if (isset($_REQUEST['rg'])) {
                    $args['region_val'] = $_REQUEST['rg'];

                    $args['fields'] = 'city AS title, city_slug AS slug';
                    $args['order'] = 'city';
                    $args['group_by'] = 'city_slug';
                }

                $gd_locations = geodir_sitemap_get_locations_array($args);

                $options = '';
                if (!empty($gd_locations)) {
                    foreach ($gd_locations as $location) {
                        $options .= '<option value="' . esc_attr($location->slug) . '">' . __(stripslashes($location->title), 'geodirectory') . '</option>';
                    }
                }
                $return['options'] = $options;
                echo json_encode($return);
                exit;
                break;
            case 'wpml_register_strings':
                $return = array();
                
                if (current_user_can('manage_options') && geodir_is_wpml()) {
                    global $sitepress, $wpdb;
                    
                    $switch_lang = false;
                    
                    $default_lang = $sitepress->get_default_language();
                    $current_lang = $sitepress->get_current_language();
                    
                    if ($current_lang != 'all' && $current_lang != $default_lang) {
                        $switch_lang = $current_lang;
                        $sitepress->switch_lang('all', true);
                    }
                    
                    // Location SEO
                    $sql = "SELECT `seo_desc`, `seo_meta_title`, `seo_meta_desc`, `seo_image_tagline` FROM `" . LOCATION_SEO_TABLE . "` WHERE seo_desc != '' OR seo_meta_title != '' OR seo_meta_desc != '' OR seo_image_tagline != '' ORDER BY seo_id DESC";
                    $results = $wpdb->get_results($sql);
                    
                    $count = 0;
                    if (!empty($results)) {
                        foreach ($results as $result) {
                            if ( !empty( $result->seo_meta_title ) ) {
                                geodir_wpml_register_string( $result->seo_meta_title, 'geodirlocation' );
                                $count++;
                            }
                            if ( !empty( $result->seo_meta_desc ) ) {
                                geodir_wpml_register_string( $result->seo_meta_desc, 'geodirlocation' );
                                $count++;
                            }
                            if ( !empty( $result->seo_desc ) ) {
                                geodir_wpml_register_string( $result->seo_desc, 'geodirlocation' );
                                $count++;
                            }
                            if ( !empty( $result->seo_image_tagline ) ) {
                                geodir_wpml_register_string( $result->seo_image_tagline, 'geodirlocation' );
                                $count++;
                            }
                        }
                    }
                    
                    if (get_option('location_neighbourhoods')) {
                        $sql = "SELECT `hood_meta_title`, `hood_meta`, `hood_description` FROM `" . POST_NEIGHBOURHOOD_TABLE . "` WHERE hood_meta_title != '' OR hood_meta != '' OR hood_description != '' ORDER BY hood_id DESC";
                        $results = $wpdb->get_results($sql);
                        
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ( !empty( $result->hood_meta_title ) ) {
                                    geodir_wpml_register_string( $result->hood_meta_title, 'geodirlocation' );
                                    $count++;
                                }
                                if ( !empty( $result->hood_meta ) ) {
                                    geodir_wpml_register_string( $result->hood_meta, 'geodirlocation' );
                                    $count++;
                                }
                                if ( !empty( $result->hood_description ) ) {
                                    geodir_wpml_register_string( $result->hood_description, 'geodirlocation' );
                                    $count++;
                                }
                            }
                        }
                    }
                    
                    if ($switch_lang) {
                        $sitepress->switch_lang($switch_lang, true);
                    }

                    if ($count > 0) {
                        $return['message'] = wp_sprintf( __( '%d strings merged for WPML string translation.', 'geodirlocation' ), $count );
                    } else {
                        $return['message'] = __( 'No string found to merge for WPML string translation.', 'geodirlocation' );
                    }
                }
                
                wp_send_json($return);
                break;
        }
    }
}

// AJAX Handler ends//

/**************************
 * /* LOCATION SWITCHER IN NAV
 ***************************/
add_filter('wp_page_menu', 'geodir_location_pagemenu_items', 110, 2);
add_filter('wp_nav_menu_items', 'geodir_location_menu_items', 110, 2);
/**
 * Filters the HTML output of a page-based menu.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $menu The HTML output.
 * @param array $args An array of arguments.
 * @return string Filtered HTML.
 */
function geodir_location_pagemenu_items($menu, $args)
{
    $locations = get_nav_menu_locations();
    $geodir_theme_location = get_option('geodir_theme_location_nav');
    $geodir_theme_location_nav = array();

    if (empty($locations) && empty($geodir_theme_location)) {
        $menu = str_replace("</ul></div>", add_nav_location_menu_items() . "</ul></div>", $menu);
        $geodir_theme_location_nav[] = $args['theme_location'];
        update_option('geodir_theme_location_nav', $geodir_theme_location_nav);
    } else if (is_array($geodir_theme_location) && in_array($args['theme_location'], $geodir_theme_location)) {
        $menu = str_replace("</ul></div>", add_nav_location_menu_items() . "</ul></div>", $menu);
    }

    return $menu;
}

/**
 * Filter the HTML output of navigation menus.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $items The HTML list content for the menu items.
 * @param object $args An object containing wp_nav_menu() arguments.
 * @return string Filtered HTML.
 */
function geodir_location_menu_items($items, $args)
{

    $location = $args->theme_location;

    $geodir_theme_location = get_option('geodir_theme_location_nav');

    if (has_nav_menu($location) == '1' && is_array($geodir_theme_location) && in_array($location, $geodir_theme_location)) {

        $items = $items . add_nav_location_menu_items();

    }

    return $items;
}

/**
 * Adds location items to the menu.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @return string HTML.
 */
function add_nav_location_menu_items()
{
    $items = '';

    if (get_option('geodir_show_changelocation_nave')) {
        $current_location = geodir_get_current_location(array('echo' => false), true);
        if (empty($current_location)) {
            $current_location = CHANGE_LOCATION;
        }
        $current_location_link = geodir_get_location_link();

        $li_class = apply_filters('geodir_location_switcher_menu_li_class', 'menu-item menu-item-type-social menu-item-type-social gd-location-switcher');
        $a_class = apply_filters('geodir_location_switcher_menu_a_class', '');
        $sub_ul_class = apply_filters('geodir_location_switcher_menu_sub_ul_class', 'sub-menu');
        $sub_li_class = apply_filters('geodir_location_switcher_menu_sub_li_class', 'menu-item gd-location-switcher-menu-item');

        $items .= '<li id="menu-item-gd-location-switcher" class="' . $li_class . '">';
        $items .= '<a href="#" class="' . $a_class . '"><i class="fa fa-map-marker"></i> ' . __($current_location, 'geodirectory') . '</a>'; // link replaced with # for better mobile support

        $items .= '<ul class="' . $sub_ul_class . '">';
        $items .= '<li class="' . $sub_li_class . '">';
        $args = array('echo' => false, 'addSearchTermOnNorecord' => 0, 'autoredirect' => true);
        $items .= geodir_location_tab_switcher($args);

        $items .= '</li>';
        $items .= '	</ul> ';
        /**
         * Filter called after the closing ul tag for the location switcher menu item.
         *
         * @since 1.4.5
         */
        $items .= apply_filters('geodir_location_switcher_menu_after_sub_ul', '');
        $items .= '</li>';
    }

    return $items;
}


/**************************
 * /* Filters and Actions for other addons
 ***************************/
add_filter('geodir_breadcrumb', 'geodir_location_breadcrumb', 1, 2);


add_filter('geodir_add_listing_map_restrict', 'geodir_remove_listing_map_restrict', 1, 1);
/**
 * Allow marker to be dragged beyond the range of default city when Multilocation is enabled.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param bool $restrict Whether to restrict the map?
 * @return bool
 */
function geodir_remove_listing_map_restrict($restrict)
{
    return false;
}

add_filter('geodir_home_map_enable_location_filters', 'geodir_home_map_enable_location_filters', 1);
/**
 * Enable location filter on home page.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param bool $enable True if location filters should be used, false if not.
 * @return bool
 */
function geodir_home_map_enable_location_filters($enable)
{
    return $enable = true;
}

add_filter('geodir_home_map_listing_where', 'geodir_default_location_where', 1);
add_filter('geodir_cat_post_count_where', 'geodir_default_location_where', 1, 2);

add_action('geodir_create_new_post_type', 'geodir_after_custom_detail_table_create', 1, 2);
/**
 * Add neighbourhood column in custom post detail table.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $post_type The post type.
 * @param string $detail_table The detail table name.
 */
function geodir_after_custom_detail_table_create($post_type, $detail_table = '')
{
    global $wpdb, $plugin_prefix;
    $post_types = geodir_get_posttypes();
    if ($detail_table == '')
        $detail_table = $plugin_prefix . $post_type . '_detail';

    if (in_array($post_type, $post_types)) {
        $meta_field_add = "VARCHAR( 100 ) NULL";
        geodir_add_column_if_not_exist($detail_table, "post_neighbourhood", $meta_field_add);
    }

    if (GEODIRLOCATION_VERSION <= '1.5.0') {
        geodir_location_fix_neighbourhood_field_limit_150();
    }
}


add_action('geodir_address_extra_listing_fields', 'geodir_location_address_extra_listing_fields', 1, 1);
/**
 * This is used to put country , region , city and neighbour dropdown on add/edit listing page.
 *
 * @since 1.0.0
 * @since 1.4.1 Updated to get city and state selected for go back & edit listing from preview page.
 * @since 1.5.3 Fix country code for translated country.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param array $val The array of setting for the custom field.
 */
function geodir_location_address_extra_listing_fields($val) {
    global $gd_session;

    $enable_country = get_option('geodir_enable_country');
    $enable_region = get_option('geodir_enable_region');
    $enable_city = get_option('geodir_enable_city');

    $name = $val['name'];
    $is_required = $val['is_required'];
    $is_default = $val['is_default'];
    $is_admin = $val['is_admin'];
    $required_msg = $val['required_msg'];
    $extra_fields = unserialize($val['extra_fields']);

    $prefix = $name . '_';

    ($extra_fields['city_lable'] != '') ? $city_title = $extra_fields['city_lable'] : $city_title = ucwords($prefix . ' city');
    ($extra_fields['region_lable'] != '') ? $region_title = $extra_fields['region_lable'] : $region_title = ucwords($prefix . ' region');
    ($extra_fields['country_lable'] != '') ? $country_title = $extra_fields['country_lable'] : $country_title = ucwords($prefix . ' country');

    $city = '';
    $region = '';
    $country = '';
    $neighbourhood = '';

    if (isset($_REQUEST['backandedit']) && $_REQUEST['backandedit'] && $gd_ses_listing = $gd_session->get('listing')) {
        $post = $gd_ses_listing;
        $city = $post[$prefix . 'city'];
        $region = $post[$prefix . 'region'];
        $country = $post[$prefix . 'country'];
        $neighbourhood = isset($post[$prefix . 'neighbourhood']) ? $post[$prefix . 'neighbourhood'] : '';
    } else if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '' && $post_info = geodir_get_post_info($_REQUEST['pid'])) {
        $post_info = (array)$post_info;
        $city = $post_info[$prefix . 'city'];
        $region = $post_info[$prefix . 'region'];
        $country = $post_info[$prefix . 'country'];
        $neighbourhood = isset($post_info[$prefix . 'neighbourhood']) ? $post_info[$prefix . 'neighbourhood'] : '';
    } else if ($gd_session->get('gd_multi_location')) {
        if ($gd_ses_city = $gd_session->get('gd_city'))
            $location = geodir_get_locations('city', $gd_ses_city);
        else if ($gd_ses_region = $gd_session->get('gd_region'))
            $location = geodir_get_locations('region', $gd_ses_region);
        else if ($gd_ses_country = $gd_session->get('gd_country'))
            $location = geodir_get_locations('country', $gd_ses_country);

        if (isset($location) && $location)
            $location = end($location);

        $city = isset($location->city) ? $location->city : '';
        $region = isset($location->region) ? $location->region : '';
        $country = isset($location->country) ? $location->country : '';
    }

    $location = geodir_get_default_location();
    if (empty($city)) $city = isset($location->city) ? $location->city : '';
    if (empty($region)) $region = isset($location->region) ? $location->region : '';
    if (empty($country)) $country = isset($location->country) ? $location->country : '';
    $country = geodir_get_normal_country($country);
    ?>
    <div id="geodir_add_listing_all_chosen_container_row" class="geodir_location_add_listing_all_chosen_container">
        <?php
        if ($enable_country == 'default') {
            global $wpdb;
            $countries_ISO2 = geodir_get_country_iso2($country);
            ?><input type="hidden" name="geodir_location_add_listing_country_val" value="<?php echo $country; ?>" />
            <input type="hidden" id="<?php echo $prefix ?>country" data-country_code="<?php echo $countries_ISO2; ?>"
                   name="<?php echo $prefix ?>country" value="<?php echo $country; ?>"/>
            <?php
        } else {
                ?>
                <div id="geodir_<?php echo $prefix . 'country'; ?>_row"
                     class="<?php if ($is_required) echo 'required_field'; ?> geodir_form_row  geodir_location_add_listing_country_chosen_container clearfix">
                    <label><?php _e($country_title, 'geodirlocation'); ?><?php if ($is_required) echo '<span>*</span>'; ?>
                    </label>
                    <div class="geodir_location_add_listing_country_chosen_div" style="width:57%; float:left;">
                        <input type="hidden" name="geodir_location_add_listing_country_val"
                               value="<?php echo $country; ?>"/>

                        <select id="<?php echo $prefix ?>country" class="geodir_location_add_listing_chosen"
                                data-location_type="country" name="<?php echo $prefix ?>country"
                                data-placeholder="<?php _e('Choose a country.', 'geodirlocation'); ?>"
                                data-addsearchtermonnorecord="1" data-ajaxchosen="0" data-autoredirect="0"
                                data-showeverywhere="0">
                            <?php
                            if (get_option('geodir_enable_country') == 'multi') {
                                geodir_get_country_dl($country, $prefix);
                            } else if (get_option('geodir_enable_country') == 'selected') {
                                geodir_get_limited_country_dl($country, $prefix);
                            }
                            ?>
                        </select>
                    </div>
                    <span
                        class="geodir_message_note"><?php _e('Click on above field and type to filter list', 'geodirlocation') ?></span>
                    <?php if ($is_required) { ?>
                        <span class="geodir_message_error"><?php echo $required_msg ?></span>
                    <?php } ?>
                </div>
                <?php

        }  // end of show country if

        if ($enable_region == 'default') {
            ?>
            <input type="hidden" name="geodir_location_add_listing_region_val" value="<?php echo $region; ?>"/>
            <input type="hidden" id="<?php echo $prefix ?>region" name="<?php echo $prefix ?>region"
                   value="<?php echo $region; ?>"/>
            <?php
        } else {
                ?>
                <div id="geodir_<?php echo $prefix . 'region'; ?>_row"
                     class="<?php if ($is_required) echo 'required_field'; ?> geodir_form_row  geodir_location_add_listing_region_chosen_container clearfix">
                    <label><?php _e($region_title, 'geodirlocation'); ?><?php if ($is_required) echo '<span>*</span>'; ?>
                    </label>
                    <div class="geodir_location_add_listing_region_chosen_div" style="width:57%; float:left;">
                        <input type="hidden" name="geodir_location_add_listing_region_val"
                               value="<?php echo $region; ?>"/>
                        <select id="<?php echo $prefix ?>region" class="geodir_location_add_listing_chosen"
                                data-location_type="region" name="<?php echo $prefix ?>region"
                                data-searchplaceholder=" <?php if (get_option('geodir_enable_region') != 'selected') { _e('Search or enter new', 'geodirlocation'); }?>"
                                data-placeholder="<?php _e('Please wait..&hellip;', 'geodirlocation'); ?>" <?php if (get_option('geodir_enable_region') == 'selected') { ?> data-ajaxchosen="0" <?php } else { ?> data-ajaxchosen="1" <?php } ?>
                                data-addsearchtermonnorecord="1" data-autoredirect="0">
                            <?php
                            $selected = '';
                            $args = array(
                                'what' => 'region',
                                'country_val' => $country,
                                'region_val' => '',
                                'echo' => false,
                                'format' => array('type' => 'array')
                            );

                            if (get_option('location_dropdown_all')) {
                                $args['no_of_records'] = '10000'; // set limit to 10 thousands as this is most browsers limit
                            }
                            $location_array = geodir_get_location_array($args);
                            // get country val in case of country search to get selected option
                            ?>
                            <option value=''><?php _e('Select State', 'geodirlocation'); ?></option>
                            <?php
                            $fill_region = true;
                            if (!empty($location_array)) {
                                foreach ($location_array as $locations) {
                                    $selected = strtolower($region) == strtolower($locations->region) ? " selected='selected'" : '';
                                    if ($selected) {
                                        $fill_region = false;
                                    }
                                    ?>
                                    <option <?php echo $selected; ?>
                                        value="<?php echo $locations->region; ?>"><?php echo ucwords($locations->region); ?></option>
                                    <?php
                                }
                            }
                            if ($fill_region && $region != '') {
                                ?>
                                <option selected="selected"
                                        value="<?php echo $region; ?>"><?php echo ucwords($region); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <span
                        class="geodir_message_note"><?php _e('Click on above field and type to filter list or add a new region', 'geodirlocation') ?></span>
                    <?php if ($is_required) { ?>
                        <span class="geodir_message_error"><?php echo $required_msg ?></span>
                    <?php } ?>
                </div>
                <?php
        } //end of show region

        if ($enable_city == 'default') {
            ?>
            <input type="hidden" name="geodir_location_add_listing_city_val" value="<?php echo $city; ?>"/>
            <input type="hidden" id="<?php echo $prefix ?>city" name="<?php echo $prefix ?>city"
                   value="<?php echo $city; ?>"/>
            <?php
        } else {
                ?>
                <div id="geodir_<?php echo $prefix . 'city'; ?>_row"
                     class="<?php if ($is_required) echo 'required_field'; ?> geodir_form_row  geodir_location_add_listing_city_chosen_container clearfix">
                    <label><?php _e($city_title, 'geodirlocation'); ?><?php if ($is_required) echo '<span>*</span>'; ?>
                    </label>
                    <div class="geodir_location_add_listing_city_chosen_div" style="width:57%; float:left;">
                        <input type="hidden" name="geodir_location_add_listing_city_val" value="<?php echo $city; ?>"/>
                        <select id="<?php echo $prefix ?>city" class="geodir_location_add_listing_chosen"
                                data-location_type="city" name="<?php echo $prefix ?>city"
                                data-searchplaceholder=" <?php if (get_option('geodir_enable_city') != 'selected') { _e('Search or enter new', 'geodirlocation'); }?>"
                                data-placeholder="<?php _e('Please wait..&hellip;', 'geodirlocation'); ?>" <?php if (get_option('geodir_enable_city') == 'selected') { ?> data-ajaxchosen="0" <?php } else { ?> data-ajaxchosen="1" <?php } ?>
                                data-addsearchtermonnorecord="1" data-autoredirect="0">
                            <?php
                            $selected = '';
                            $args = array(
                                'what' => 'city',
                                'country_val' => $country,
                                'region_val' => $region,
                                'echo' => false,
                                'format' => array('type' => 'array')
                            );

                            if (get_option('location_dropdown_all')) {
                                $args['no_of_records'] = '10000'; // set limit to 10 thousands as this is most browsers limit
                            }
                            $location_array = geodir_get_location_array($args);
                            // get country val in case of country search to get selected option
                            ?>
                            <option value=''><?php _e('Select City', 'geodirlocation'); ?></option>
                            <?php
                            $fill_city = true;
                            if (!empty($location_array)) {
                                foreach ($location_array as $locations) {
                                    $selected = strtolower($city) == strtolower($locations->city) ? " selected='selected'" : '';
                                    if ($selected) {
                                        $fill_city = false;
                                    }
                                    ?>
                                    <option <?php echo $selected; ?>
                                        value="<?php echo $locations->city; ?>"><?php echo ucwords($locations->city); ?></option>
                                    <?php
                                }
                            }
                            if ($fill_city && $city != '') {
                                ?>
                                <option selected="selected"
                                        value="<?php echo $city; ?>"><?php echo ucwords($city); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <span
                        class="geodir_message_note"><?php _e('Click on above field and type to filter list or add a new city', 'geodirlocation') ?></span>
                    <?php if ($is_required) { ?>
                        <span class="geodir_message_error"><?php echo $required_msg; ?></span>
                    <?php } ?>
                </div>
                <?php

        } // end of show city if

        if (get_option('location_neighbourhoods') && $is_admin == '1') {
            global $plugin_prefix;

            if ($enable_city == 'default') {
                $city = $location->city;
            }

            $neighbourhood_options = geodir_get_neighbourhoods_dl(esc_attr(stripslashes($city)), $neighbourhood, false);

            $neighbourhood_display = '';
            if (trim($neighbourhood_options) == '')
                $neighbourhood_display = 'style="display:none;"';

            ?>
            <div id="geodir_<?php echo $prefix . 'neighbourhood'; ?>_row"
                 class="geodir_form_row  geodir_location_add_listing_neighbourhood_chosen_container clearfix" <?php echo $neighbourhood_display; ?> >
                <label><?php _e('Neighbourhood', 'geodirlocation'); ?></label>

                <div class="geodir_location_add_listing_neighbourhood_chosen_div" style="width:57%; float:left;">
                    <input type="hidden" name="geodir_location_add_listing_neighbourhood_val" value="<?php echo esc_attr(stripslashes($neighbourhood)); ?>" />
                    <select name="<?php echo $prefix . 'neighbourhood'; ?>" class="chosen_select" option-ajaxChosen="false">
                        <?php echo $neighbourhood_options; ?>
                    </select>
                </div>
                <span
                    class="geodir_message_note"><?php _e('Click on above field and type to filter list', 'geodirlocation') ?></span>
            </div>
            <?php

        }
        ?>
    </div><!-- end of geodir_location_add_listing_all_chosen_container -->
    <?php

}


/**************************
 * /* DATABASE OPERATION RELATED FILTERS AND ACTIONS
 ***************************/
add_filter('geodir_get_location_by_id', 'geodir_get_location_by_id', 1, 2); // this function is in geodir_location_functions.php
add_filter('geodir_default_latitude', 'geodir_location_default_latitude', 1, 2);
add_filter('geodir_default_longitude', 'geodir_location_default_longitude', 1, 2);

add_action('geodir_after_save_listing', 'geodir_save_listing_location', 2, 3);
/**
 * Action to save location related information in post type detail table on add/edit new listing action.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $last_post_id The saved post ID.
 * @param array $request_info The post details in an array.
 */
function geodir_save_listing_location($last_post_id, $request_info) {
    global $wpdb;
    
    $neighbourhood_active = (bool)get_option('location_neighbourhoods');
    
    $location_info = array();
    if (isset($request_info['post_neighbourhood'])) {
        $location_info['post_neighbourhood'] = $request_info['post_neighbourhood'];
    }
    
    if (isset($request_info['post_city']) && isset($request_info['post_region'])) {
        $post_location_id = geodir_get_post_meta($last_post_id, 'post_location_id', true);

        $post_location = geodir_get_location_by_id('', $post_location_id);

        $location_info['post_locations'] = '[' . $post_location->city_slug . '],[' . $post_location->region_slug . '],[' . $post_location->country_slug . ']'; // set all overall post location
        //$location_info['post_country'] = __($post_location->country, 'geodirectory'); // set translated country. @todo kiran why is this translated?
        $location_info['post_country'] = $post_location->country;
        
        if ($neighbourhood_active && !empty($location_info['post_neighbourhood']) && !empty($request_info['geodir_location_add_listing_neighbourhood_val'])) {
            $neighbourhood = geodir_location_get_neighbourhood_by_id($location_info['post_neighbourhood'], true, $post_location_id);
            
            if (empty($neighbourhood)) {
                $neighbourhood = geodir_location_neighbourhood_by_name_loc_id($location_info['post_neighbourhood'], $post_location_id);
                
                if (empty($neighbourhood)) {
                    $save_hood = array();
                    $save_hood['hood_location_id'] = $post_location_id;
                    $save_hood['hood_name'] = stripslashes(sanitize_text_field($location_info['post_neighbourhood']));
                    $save_hood['hood_latitude'] = !empty($post_location->city_latitude) ? $post_location->city_latitude : (!empty($request_info['post_latitude']) ? $request_info['post_latitude'] : '');
                    $save_hood['hood_longitude'] = !empty($post_location->city_longitude) ? $post_location->city_longitude : (!empty($request_info['post_longitude']) ? $request_info['post_longitude'] : '');
                    
                    $neighbourhood = geodir_location_insert_update_neighbourhood($save_hood);
                }
                
                if (!empty($neighbourhood)) {
                    $request_info['post_neighbourhood'] = $neighbourhood->hood_slug;
                    $location_info['post_neighbourhood'] = $neighbourhood->hood_slug;
                }
            }
        }
    }

    if (!empty($location_info))
        geodir_save_post_info($last_post_id, $location_info);
}


add_action('geodir_add_new_location', 'geodir_add_new_location_via_adon', 1, 1);
// this action is defined in geodirectory core plugin and geodir_add_new_location_via_adon geodir_location_functions.php

add_action('geodir_get_new_location_link', 'geodir_get_new_location_link', 1, 3);


add_action('geodir_address_extra_admin_fields', 'geodir_location_address_extra_admin_fields', 1, 2);

add_filter('geodir_auto_change_map_fields', 'geodir_location_auto_change_map_fields', 1, 1);

add_action('geodir_update_marker_address', 'geodir_location_update_marker_address', 1, 1);

add_action('geodir_add_listing_js_start', 'geodir_location_autofill_address', 1, 1);

add_filter('geodir_codeaddress', 'geodir_location_codeaddress', 1, 1);


/**
 * Change the address code when add neighbourhood request.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $codeAddress Row of address to use in google map.
 * @return string Filtered codeAddress.
 */
function geodir_location_codeaddress($codeAddress)
{

    if (isset($_REQUEST['add_hood']) && $_REQUEST['add_hood'] != '') {

        ob_start(); ?>
        address = jQuery("#hood_name").val();
        <?php $codeAddress = ob_get_clean();

    }
    return $codeAddress;
}

/**
 * Set auto change map fields to false when add neighbourhood request.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param bool $change Whether to auto fill country, state, city values in fields.
 * @return bool
 */
function geodir_location_auto_change_map_fields($change)
{

    if (isset($_REQUEST['add_hood']) && $_REQUEST['add_hood'] != '') {
        $change = false;
    }
    return $change;
}

add_filter('geodir_googlemap_script_extra', 'geodir_location_map_extra', 1, 1);
/**
 * Add map extras.
 *
 * @since 1.0.0
 * @since 1.5.1 Function geodir_is_page() used to identify add listing page.
 * @package GeoDirectory_Location_Manager
 *
 * @param string $prefix The string to filter, default is empty string.
 * @return string
 */
function geodir_location_map_extra($prefix = '') {
    global $pagenow;

    if (geodir_is_page('add-listing') || (is_admin() && ($pagenow == 'post.php' || isset($_REQUEST['post_type'])))) {
        return "&libraries=places";
    }
}

/**
 * Adds js to autofill the address.
 *
 * @since 1.0.0
 * @since 1.5.1 Function geodir_is_page() used to identify add listing page.
 * @package GeoDirectory_Location_Manager
 *
 * @global string $pagenow The current screen.
 *
 * @param string $prefix The prefix for all elements.
 */
function geodir_location_autofill_address($prefix = '') {
    global $pagenow;

    if (geodir_is_page('add-listing') || (is_admin() && ($pagenow == 'post.php' || isset($_REQUEST['post_type'])))) {
        if (get_option('location_address_fill')) {
        } else {
    ?>

    jQuery(function() {
        initialize_autofill_address();
    });
    <?php } ?>

    var placeSearch, autocomplete;
    var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
    };

    function initialize_autofill_address() {

        var options = {
        types: ['geocode'],
        <?php
        $geodir_enable_country = get_option( 'geodir_enable_country' );
        if($geodir_enable_country == 'default'){
            $location_default = geodir_get_default_location();
            if( isset( $location_default->country) && $location_default->country ){
                $countries_ISO2 = geodir_get_country_iso2($location_default->country);
                echo 'componentRestrictions: {country: "'.$countries_ISO2.'"}';
            }
        }elseif($geodir_enable_country == 'selected'){
            if (get_option('geodir_selected_countries')){
                $selected_countries = get_option('geodir_selected_countries');

                if(!empty($selected_countries) && count($selected_countries) < 6){// we can only filter by 5 countries
                    //print_r($selected_countries);echo '###';
                    $iso2_codes = array();
                    foreach($selected_countries as $country_name ){
                        $iso2_code = geodir_get_country_iso2($country_name);
                        if($iso2_code){
                            $iso2_codes[] = $iso2_code;
                        }
                    }

                    if(!empty($iso2_codes)){
                        echo 'componentRestrictions: {country: ['."'" . implode("','", $iso2_codes) . "'".']}';
                    }
                }
            }

        }

        ?>

        };

        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            // Create the autocomplete object, restricting the search
            // to geographical location types.
            autocomplete = new google.maps.places.Autocomplete(
                /** @type {HTMLInputElement} */
                (document.getElementById('<?php echo $prefix . 'address'; ?>')), options);
            // When the user selects an address from the dropdown,
            // populate the address fields in the form.
            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                fillInAddress();
            });
        }
    }

    // [START region_fillform]
    function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();

        //blank fields
        jQuery('#<?php echo $prefix . 'country'; ?> option[value=""]').attr("selected",true);
        jQuery("#<?php echo $prefix . 'country'; ?>").trigger("chosen:updated");

        if (!jQuery('#<?php echo $prefix . 'region'; ?> option[value=""]').length) {
            jQuery("#<?php echo $prefix . 'region'; ?>").append('<option value=""><?php _e('Select Region', 'geodirlocation'); ?></option>');
        }
        jQuery('#<?php echo $prefix . 'region'; ?> option[value=""]').attr("selected",true);
        jQuery("#<?php echo $prefix . 'region'; ?>").trigger("chosen:updated");

        if (!jQuery('#<?php echo $prefix . 'city'; ?> option[value=""]').length) {
            jQuery("#<?php echo $prefix . 'city'; ?>").append('<option value=""><?php _e('Select City', 'geodirlocation'); ?></option>');
        }
        jQuery('#<?php echo $prefix . 'city'; ?> option[value=""]').attr("selected",true);
        jQuery("#<?php echo $prefix . 'city'; ?>").trigger("chosen:updated");

        jQuery('#<?php echo $prefix . 'zip'; ?>').val('');

        var newArr = new Array();
        newArr[0] = place;
        geocodeResponse(newArr);

        if (place.name) {
        jQuery('#<?php echo $prefix . 'address'; ?>').val(place.name);
        }

        geodir_codeAddress(true);

    }
    // [END region_fillform]
    <?php
    }
}

/**
 * Updates marker address.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $prefix Identifier used as a prefix for field name
 */
function geodir_location_update_marker_address($prefix = '')
{
    global $pagenow, $wpdb, $gd_session;

    if (((is_page() && geodir_is_page('add-listing'))) || (is_admin() && ($pagenow == 'post.php' || isset($_REQUEST['post_type'])))) {
        $country_option = get_option('geodir_enable_country');
        $region_option = get_option('geodir_enable_region');
        $city_option = get_option('geodir_enable_city');
        $neighbourhood_active = get_option('location_neighbourhoods');

        $default_country = '';
        $default_region = '';
        $default_city = '';

        if ($gd_session->get('gd_multi_location')) {
            if ($gd_ses_city = $gd_session->get('gd_country'))
                $location = geodir_get_locations('city', $gd_ses_city);
            else if ($gd_ses_region = $gd_session->get('gd_country'))
                $location = geodir_get_locations('region', $gd_ses_region);
            else if ($gd_ses_country = $gd_session->get('gd_country'))
                $location = geodir_get_locations('country', $gd_ses_country);

            if (isset($location) && $location)
                $location = end($location);

            $default_city = isset($location->city) ? $location->city : '';
            $default_region = isset($location->region) ? $location->region : '';
            $default_country = isset($location->country) ? $location->country : '';
        }

        $location = geodir_get_default_location();

        if (empty($default_city)) $default_city = isset($location->city) ? $location->city : '';
        if (empty($default_region)) $default_region = isset($location->region) ? $location->region : '';
        if (empty($default_country)) $default_country = isset($location->country) ? $location->country : '';

        $default_lat = apply_filters('geodir_default_latitude', $location->city_latitude, true);
        $default_lng = apply_filters('geodir_default_longitude', $location->city_longitude, true);

        $selected_countries = array();
        if (get_option('geodir_selected_countries'))
            $selected_countries = get_option('geodir_selected_countries');

        $selected_regions = array();
        if (get_option('geodir_selected_regions'))
            $selected_regions = get_option('geodir_selected_regions');

        $selected_cities = array();
        if (get_option('geodir_selected_cities'))
            $selected_cities = get_option('geodir_selected_cities');

            
        ?>

        var error = false;
        var loc_error_checking_start_count = 0;
        var loc_error_checking_end_count = 0;
        <?php
        if ($country_option == 'default' || $country_option == 'selected') {
            echo "loc_error_checking_start_count++;";
        }
        if ($region_option == 'default' || $region_option == 'selected') {
            echo "loc_error_checking_start_count++;";
        }
        if ($city_option == 'default' || $city_option == 'selected') {
            echo "loc_error_checking_start_count++;";
        }

        if ($country_option == 'default') {
            $countries_ISO2 = geodir_get_country_iso2($default_country);

            ?>
            if ('<?php echo $countries_ISO2; ?>' != getCountryISO && error == false) {
            error = true;
            alert('<?php printf(__('Please choose any address of the (%s) country only.', 'geodirectory'), $default_country); ?>');
            loc_error_checking_end_count=loc_error_checking_start_count;
            } else {
            loc_error_checking_end_count++;
            }
            <?php
        } elseif ($country_option == 'selected') {
            if (is_array($selected_countries) && !empty($selected_countries)) {
                $selected_countries_string = implode(',', $selected_countries);

                if (count($selected_countries) > 1) {
                    $selected_countries_string = sprintf(__('Please choose any address of the (%s) countries only.', 'geodirectory'), implode(',', $selected_countries));
                } else {
                    $selected_countries_string = sprintf(__('Please choose any address of the (%s) country only.', 'geodirectory'), implode(',', $selected_countries));
                }

            } else {
                $selected_countries_string = __('No countries available.', 'geodirectory');
            }

            $countriesP = implode(',', array_fill(0, count($selected_countries), '%s'));
            $countries_ISO2 = $wpdb->get_results($wpdb->prepare("SELECT ISO2 FROM " . GEODIR_COUNTRIES_TABLE . " WHERE Country IN ($countriesP)", $selected_countries));
            $cISO_arr = array();
            foreach ($countries_ISO2 as $cIOS2) {
                $cISO_arr[] = $cIOS2->ISO2;
            }
            ?>
            var country_array = <?php echo json_encode($cISO_arr); ?>;
            //country_array = jQuery.map(country_array, String.toLowerCase);

            if (error == false && getCountryISO && jQuery.inArray( getCountryISO, country_array ) == -1  ) {
            error = true;
            alert('<?php echo $selected_countries_string; ?>');
            loc_error_checking_end_count++;
            } else {
            loc_error_checking_end_count++;
            }
        <?php } ?>
        if (getCountry && getCity && error == false) {
        jQuery.post("<?php echo admin_url() . 'admin-ajax.php?action=geodir_locationajax_action&location_ajax_action=set_region_on_map'; ?>", {
        country: getCountry,
        state: getState,
        city: getCity
        }).done(function(data) {
        if (jQuery.trim(data) != '') {
        getState = data;
        }

        <?php if ($region_option == 'default') { ?>
            if ('<?php echo geodir_strtolower(esc_attr($default_region)); ?>' != getState.toLowerCase() && error == false) {
            error = true;
            alert('<?php printf(__('Please choose any address of the (%s) region only.', 'geodirectory'), $default_region); ?>');

            loc_error_checking_end_count++;
            } else {
            loc_error_checking_end_count++;
            }
            <?php
        } elseif ($region_option == 'selected') {
            $selected_regions_string = '';

            if (is_array($selected_regions) && !empty($selected_regions)) {
                $selected_regions_string = implode(',', $selected_regions);

                if (count($selected_regions) > 1) {
                    $selected_regions_string = sprintf(__('Please choose any address of the (%s) regions only.', 'geodirectory'), implode(',', $selected_regions));
                } else {
                    $selected_regions_string = sprintf(__('Please choose any address of the (%s) region only.', 'geodirectory'), implode(',', $selected_regions));
                }
            }
            ?>

            var region_array = <?php echo json_encode(stripslashes_deep($selected_regions)); ?>;
            region_array = jQuery.map(region_array, function(n){return n.toLowerCase();});

            if (jQuery.inArray( getState.toLowerCase(), region_array ) == -1 && error == false && region_array.length > 0) {
            error = true;
            alert('<?php echo $selected_regions_string; ?>');
            loc_error_checking_end_count++;
            } else {
            loc_error_checking_end_count++;
            }
        <?php }
        if ($city_option == 'default') { ?>
            if ('<?php echo geodir_strtolower(esc_attr($default_city)); ?>' != getCity.toLowerCase() && error == false) {
            error = true;
            alert('<?php printf(__('Please choose any address of the (%s) city only.', 'geodirectory'), $default_city); ?>');
            loc_error_checking_end_count++;
            } else {
            loc_error_checking_end_count++;
            }
            <?php
        } elseif ($city_option == 'selected') {
            $selected_cities_string = '';
            if (is_array($selected_cities) && !empty($selected_cities)) {
                if (count($selected_cities) > 1) {
                    $selected_cities_string = sprintf(__('Please choose any address of the (%s) cities only.', 'geodirectory'), implode(',', $selected_cities));
                } else {
                    $selected_cities_string = sprintf(__('Please choose any address of the (%s) city only.', 'geodirectory'), implode(',', $selected_cities));
                }
            }
            ?>

            var city_array = <?php echo json_encode(stripslashes_deep($selected_cities)); ?>;
            city_array = jQuery.map(city_array, function(n){return n.toLowerCase();});

            if (jQuery.inArray( getCity.toLowerCase(), city_array ) == -1 && error == false && city_array.length > 0) {
            error = true;
            alert('<?php echo $selected_cities_string; ?>');
            loc_error_checking_end_count++;
            } else {
            loc_error_checking_end_count++;
            }
        <?php } ?>
        });
        }

        function gd_location_error_done() {
            if (loc_error_checking_start_count != loc_error_checking_end_count) {
                setTimeout(function() {
                    gd_location_error_done();
                }, 100);
            } else {
                if (error == false) {
                    var mapLang = '<?php echo geodir_get_map_default_language();?>';
                    var countryChanged = jQuery.trim(old_country) != jQuery.trim(getCountry) ? true : false;
                    old_country = jQuery.trim(old_country);
                    if (mapLang != 'en' && old_country) {
                        <?php if ($country_option == 'default') { ?>
                        var oldISO2 = jQuery('input#<?php echo $prefix . 'country'; ?>').attr('data-country_code');
                        <?php } else { ?>
                        var oldISO2 = jQuery('#<?php echo $prefix . 'country'; ?> option[value="' + old_country + '"]').attr('data-country_code');
                        <?php } ?>
                        if (oldISO2 && oldISO2 == getCountryISO) {
                            countryChanged = false;
                        }
                    }

                    if (countryChanged) {
                        jQuery('#<?php echo $prefix . 'region'; ?> option').remove();

                        if (jQuery("#<?php echo $prefix . 'region'; ?> option:contains('" + getState + "')").length == 0) {
                            jQuery("#<?php echo $prefix . 'region'; ?>").append('<option value="' + getState + '">' + getState + '</option>');
                        }

                        jQuery('#<?php echo $prefix . 'region'; ?> option[value="' + getState + '"]').attr("selected", true);
                        jQuery("#<?php echo $prefix . 'region'; ?>").trigger("chosen:updated");

                        jQuery('#<?php echo $prefix . 'city'; ?> option').remove();

                        if (jQuery("#<?php echo $prefix . 'city'; ?> option:contains('" + getCity + "')").length == 0) {
                            jQuery("#<?php echo $prefix . 'city'; ?>").append('<option value="' + getCity + '">' + getCity + '</option>');
                        }

                        jQuery('#<?php echo $prefix . 'city'; ?> option[value="' + getCity + '"]').attr("selected", true);
                        jQuery("#<?php echo $prefix . 'city'; ?>").trigger("chosen:updated");
                    }

                    if (jQuery.trim(old_region) != jQuery.trim(getState)) {
                        jQuery('#<?php echo $prefix . 'city'; ?> option').remove();

                        if (jQuery("#<?php echo $prefix . 'city'; ?> option:contains('" + getCity + "')").length == 0) {
                            jQuery("#<?php echo $prefix . 'city'; ?>").append('<option value="' + getCity + '">' + getCity + '</option>');
                        }

                        jQuery('#<?php echo $prefix . 'city'; ?> option[value="' + getCity + '"]').attr("selected", true);
                        jQuery("#<?php echo $prefix . 'city'; ?>").trigger("chosen:updated");
                    }

                    if (getCountry) {
                        jQuery('#<?php echo $prefix . 'country'; ?> option[value="' + getCountry + '"]').attr("selected", true);
                        jQuery("#<?php echo $prefix . 'country'; ?>").trigger("chosen:updated");
                    }

                    if (getZip) {
                        if (getCountryISO == 'SK' || getCountryISO == 'TR' || getCountryISO == 'DK' || getCountryISO == 'ES' || getCountryISO == 'CZ') {
                            geodir_region_fix(getCountryISO, getZip, '<?php echo $prefix; ?>');
                        }
                    }
                    
                    var $chosen_supported = geodir_lm_chosen_supported();
                    <?php if ($region_option != 'default') { ?>
                    if (!$chosen_supported) {
                        var $new_loc = jQuery("#<?php echo $prefix . 'region_new'; ?>");
                        if (!$new_loc.attr('id')) {
                            var $new_loc_html = '<div style="display:none" class="gd-new-loc-div gd-new-region-div"><input field_type="address" placeholder="<?php esc_attr_e('Add new region (if not in the list)', 'geodirlocation'); ?>" id="<?php echo $prefix . 'region_new'; ?>" class="geodir_textfield" value="" type="text" /><input  onclick="geodir_lm_type_new_location(this, \'region\');" value="<?php esc_attr_e('Add', 'geodirlocation'); ?>" type="button" /></div>';
                            jQuery("#<?php echo $prefix . 'region'; ?>").after($new_loc_html);
                        }
                    }
                    <?php } ?>
                    <?php if ($city_option != 'default') { ?>
                    if (!$chosen_supported) {
                        var $new_loc = jQuery("#<?php echo $prefix . 'city_new'; ?>");
                        if (!$new_loc.attr('id')) {
                            var $new_loc_html = '<div style="display:none" class="gd-new-loc-div gd-new-city-div"><input field_type="address" placeholder="<?php esc_attr_e('Add new city (if not in the list)', 'geodirlocation'); ?>" id="<?php echo $prefix . 'city_new'; ?>" class="geodir_textfield" value="" type="text" /><input  onclick="geodir_lm_type_new_location(this, \'city\');" value="<?php esc_attr_e('Add', 'geodirlocation'); ?>" type="button" /></div>';
                            jQuery("#<?php echo $prefix . 'city'; ?>").after($new_loc_html);
                        }
                    }
                    <?php } ?>

                    if (getState) {
                        jQuery('.gd-new-region-div').hide();
                        
                        if (jQuery("#<?php echo $prefix . 'region'; ?> option:contains('" + getState + "')").length == 0) {
                            jQuery("#<?php echo $prefix . 'region'; ?>").append('<option value="' + getState + '">' + getState + '</option>');
                        }

                        jQuery('#<?php echo $prefix . 'region'; ?> option[value="' + getState + '"]').attr("selected", true);
                        jQuery("#<?php echo $prefix . 'region'; ?>").trigger("chosen:updated");
                    } else {
                        <?php if ($region_option != 'default') { ?>
                        if (!$chosen_supported) {
                            jQuery('.gd-new-region-div').show();
                            jQuery('.gd-new-region-div [field_type="address"]').val("");
                        }
                        <?php } ?>
                    }

                    if (getCity) {
                        jQuery('.gd-new-city-div').hide();
                        
                        if (jQuery("#<?php echo $prefix . 'city'; ?> option:contains('" + getCity + "')").length == 0) {
                            jQuery("#<?php echo $prefix . 'city'; ?>").append('<option value="' + getCity + '">' + getCity + '</option>');
                        }

                        jQuery('#<?php echo $prefix . 'city'; ?> option[value="' + getCity + '"]').attr("selected", true);
                        jQuery("#<?php echo $prefix . 'city'; ?>").trigger("chosen:updated");

                        jQuery('select.geodir_location_add_listing_chosen').each(function () {
                            if (jQuery(this).attr('id') == '<?php echo $prefix . 'city'; ?>') {
                                jQuery(this).change();
                            }
                        });
                    } else {
                        <?php if ($city_option != 'default') { ?>
                        if (!$chosen_supported) {
                            jQuery('.gd-new-city-div').show();
                            jQuery('.gd-new-city-div [field_type="address"]').val("");
                        }
                        <?php } ?>
                    }
                    <?php if ($neighbourhood_active) { ?>
                    if (window.neighbourhood) {
                        var $neighbourhood = jQuery('#<?php echo $prefix . 'neighbourhood'; ?>');
                        if ($neighbourhood.find('option:contains("' + window.neighbourhood + '")').length == 0) {
                            $neighbourhood.append('<option value="' + window.neighbourhood + '">' + window.neighbourhood + '</option>');
                        }
                        $neighbourhood.find('option[value="' + window.neighbourhood + '"]').attr("selected", true);
                        $neighbourhood.trigger("chosen:updated");
                    }
                    <?php } ?>
                } else {
                    geodir_set_map_default_location('<?php echo $prefix . 'map'; ?>', '<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>');
                    return false;
                }

                if (error) {
                    geodir_set_map_default_location('<?php echo $prefix . 'map'; ?>', '<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>');
                    return false;
                }
            }
        }

        gd_location_error_done();
    <?php }
    if (isset($_REQUEST['add_hood']) && $_REQUEST['add_hood'] != '') { ?>
        if (getCity) {
        if (jQuery('input[id="hood_name"]').attr('id')) {
        //jQuery("#hood_name").val(getCity);
        }
        }
    <?php } elseif (get_option('location_neighbourhoods') && (geodir_is_page('add-listing') || (is_admin() && ($pagenow == 'post.php' || isset($_REQUEST['post_type']))))) { ?>
        //geodir_get_neighbourhood_dl(getCity);
        <?php
    }
}

add_filter('geodir_add_listing_js_start', 'geodir_add_fix_region_code');
/**
 *
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_add_fix_region_code()
{
?>
function geodir_region_fix(ISO2, ZIP, prefix) {
    var _wpnonce = jQuery('#gd_location').closest('form').find('#_wpnonce').val();

    jQuery.post("<?php echo plugins_url('', __FILE__); ?>/zip_arrays/"+ISO2+".php", {
        ISO2: ISO2, ZIP:ZIP
    }).done(function(data) {
        if (data) {
            getState =  data;

            if (getState) {
                if (jQuery("#"+prefix+"<?php echo 'region'; ?> option:contains('"+getState+"')").length == 0) {
                    jQuery("#"+prefix+"<?php echo 'region'; ?>").append('<option value="'+getState+'">'+getState+'</option>');
                }

                jQuery('#'+prefix+'<?php echo 'region'; ?> option[value="'+getState+'"]').attr("selected",true);
                jQuery("#"+prefix+"<?php echo 'region'; ?>").trigger("chosen:updated");
            }
        }
    });
}
<?php
}




/*========================*/
/* ENABLE SHARE LOCATION */
/*========================*/
add_filter('geodir_ask_for_share_location', 'geodir_ask_for_share_location');
/**
 * Ask user confirmation to share location.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param bool $mode Ask the user? Default: false.
 * @return bool Filtered value.
 */
function geodir_ask_for_share_location($mode)
{
    global $gd_session;
    if ($gd_session->get('gd_location_shared') == 1 || ($gd_session->get('gd_multi_location') && !$gd_session->get('gd_location_default_loaded'))) {
        $gd_session->set('gd_location_shared', 1);
        return $mode;
    } else if (!geodir_is_geodir_page()) {
        return $mode;
    } else if (geodir_is_page('home')) {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', TRUE);// do not cache if we are asking for location
        }
        return true;
    }

    return $mode;
}

add_filter('geodir_share_location', 'geodir_location_manager_share_location');

/**
 * Redirect url after sharing location.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wp_query WordPress Query object.
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $redirect_url Old redirect url
 * @return bool|null|string Filtered redirect url.
 */
function geodir_location_manager_share_location($redirect_url) {
    global $wpdb, $wp_query, $plugin_prefix, $gd_session;

    if (isset($_REQUEST['geodir_ajax']) && $_REQUEST['geodir_ajax'] == 'share_location') {
        $gd_session->set('gd_onload_redirect_done', 1);

        if (isset($_REQUEST['error']) && $_REQUEST['error']) {
            $gd_session->set('gd_location_shared', 1);
            return;
        }

        // ask user to share his location only one time.
        $gd_session->set('gd_location_shared', 1);

        $DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));

        if (get_option('geodir_search_dist') != '') {
            $dist = get_option('geodir_search_dist');
        } else {
            $dist = '25000';
        }
        if (get_option('geodir_near_me_dist') != '') {
            $dist2 = get_option('geodir_near_me_dist');
        } else {
            $dist2 = '200';
        }

        if (isset($_REQUEST['lat']) && isset($_REQUEST['long'])) {
            $mylat = (float)stripslashes(ucfirst($_REQUEST['lat']));
            $mylon = (float)stripslashes(ucfirst($_REQUEST['long']));
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
            $addr_details = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
            $mylat = stripslashes(ucfirst($addr_details['geoplugin_latitude']));
            $mylon = stripslashes(ucfirst($addr_details['geoplugin_longitude']));
        }

        $gd_session->set('user_lat', $mylat);
        $gd_session->set('user_lon', $mylon);
        $lon1 = $mylon - $dist2 / abs(cos(deg2rad($mylat)) * 69);
        $lon2 = $mylon + $dist2 / abs(cos(deg2rad($mylat)) * 69);
        $lat1 = $mylat - ($dist2 / 69);
        $lat2 = $mylat + ($dist2 / 69);

        $rlon1 = is_numeric(min($lon1, $lon2)) ? min($lon1, $lon2) : '';
        $rlon2 = is_numeric(max($lon1, $lon2)) ? max($lon1, $lon2) : '';
        $rlat1 = is_numeric(min($lat1, $lat2)) ? min($lat1, $lat2) : '';
        $rlat2 = is_numeric(max($lat1, $lat2)) ? max($lat1, $lat2) : '';

        $near_location_info = $wpdb->get_results($wpdb->prepare("SELECT *,CONVERT((%s * 2 * ASIN(SQRT( POWER(SIN((%s - (" . $plugin_prefix . "gd_place_detail.post_latitude)) * pi()/180 / 2), 2) +COS(%s * pi()/180) * COS( (" . $plugin_prefix . "gd_place_detail.post_latitude) * pi()/180) *POWER(SIN((%s - " . $plugin_prefix . "gd_place_detail.post_longitude) * pi()/180 / 2), 2) ))),UNSIGNED INTEGER) as distance FROM " . $plugin_prefix . "gd_place_detail WHERE (" . $plugin_prefix . "gd_place_detail.post_latitude IS NOT NULL AND " . $plugin_prefix . "gd_place_detail.post_latitude!='') AND " . $plugin_prefix . "gd_place_detail.post_latitude between $rlat1 and $rlat2  AND " . $plugin_prefix . "gd_place_detail.post_longitude between $rlon1 and $rlon2 ORDER BY distance ASC LIMIT 1", $DistanceRadius, $mylat, $mylat, $mylon));

        if (!empty($near_location_info)) {
            $redirect_url = geodir_get_location_link('base') . 'me';
            return ($redirect_url);
            die();
        }


        $location_info = $wpdb->get_results($wpdb->prepare("SELECT *,CONVERT((%s * 2 * ASIN(SQRT( POWER(SIN((%s - (" . POST_LOCATION_TABLE . ".city_latitude)) * pi()/180 / 2), 2) +COS(%s * pi()/180) * COS( (" . POST_LOCATION_TABLE . ".city_latitude) * pi()/180) *POWER(SIN((%s - " . POST_LOCATION_TABLE . ".city_longitude) * pi()/180 / 2), 2) ))),UNSIGNED INTEGER) as distance FROM " . POST_LOCATION_TABLE . " ORDER BY distance ASC LIMIT 1", $DistanceRadius, $mylat, $mylat, $mylon));

        if (!empty($location_info)) {
            $location_info = end($location_info);
            $location_array = array();
            $location_array['gd_country'] = $location_info->country_slug;
            $location_array['gd_region'] = $location_info->region_slug;
            $location_array['gd_city'] = $location_info->city_slug;
            $base = rtrim(geodir_get_location_link('base'), '/');
            $redirect_url = $base . '/' . $location_info->country_slug . '/' . $location_info->region_slug . '/' . $location_info->city_slug;

                $args_current_location = array(
                    'what' => 'city',
                    'country_val' => $location_info->country,
                    'region_val' => $location_info->region,
                    'city_val' => $location_info->city,
                    'compare_operator' => '=',
                    'no_of_records' => '1',
                    'echo' => false,
                    'format' => array('type' => 'array')
                );
                $current_location_array = geodir_get_location_array($args_current_location);
            if(isset($current_location_array[0])){
                $redirect_url =  $base.'/'.$current_location_array[0]->location_link;
            }
            $redirect_url = geodir_location_permalink_url($redirect_url);
        } else {
            $redirect_url = geodir_get_location_link('base');
        }

        return ($redirect_url);
    }
}


add_filter('geodir_term_slug_is_exists', 'geodir_location_term_slug_is_exists', 1, 3);
/**
 * Check term slug exists or not.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param bool $slug_exists Default: false.
 * @param string $slug The term slug.
 * @param int|string $term_id The term ID.
 * @return bool Filtered $slug_exists value.
 */
function geodir_location_term_slug_is_exists($slug_exists, $slug, $term_id)
{

    global $plugin_prefix, $wpdb, $table_prefix;
    $slug = urldecode($slug);
    $get_slug = $wpdb->get_var($wpdb->prepare("SELECT location_id FROM " . $plugin_prefix . "post_locations WHERE country_slug=%s ||	region_slug=%s ||	city_slug=%s ", array($slug, $slug, $slug)));

    if ($get_slug)
        return true;

    if ($wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s AND term_id != %d", array($slug, $term_id))))
        return true;


    return $slug_exists;
}


add_action('init', 'geodir_update_locations_default_options');
/**
 * Update the default settings of location manager.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_update_locations_default_options()
{

    if (!get_option('geodir_update_locations_default_options')) {

        if (!get_option('geodir_enable_country'))
            update_option('geodir_enable_country', 'multi');

        if (!get_option('geodir_enable_region'))
            update_option('geodir_enable_region', 'multi');

        if (!get_option('geodir_enable_city'))
            update_option('geodir_enable_city', 'multi');

        //if (!get_option('geodir_result_by_location'))
            //update_option('geodir_result_by_location', 'everywhere');

        if (!get_option('geodir_everywhere_in_country_dropdown'))
            update_option('geodir_everywhere_in_country_dropdown', '1');

        if (!get_option('geodir_everywhere_in_region_dropdown'))
            update_option('geodir_everywhere_in_region_dropdown', '1');

        if (!get_option('geodir_everywhere_in_city_dropdown'))
            update_option('geodir_everywhere_in_city_dropdown', '1');

        update_option('geodir_update_locations_default_options', '1');

    }

    if (get_option('location_neighbourhoods')) {
        add_action( 'geodir_add_listing_geocode_js_vars', 'geodir_location_grab_neighbourhood' );
    }
}

add_action('wp', 'geodir_location_temple_redirect');
/**
 * Manage canonical link on location pages.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wp WordPress object.
 */
function geodir_location_temple_redirect()
{
    global $wp;

    if (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == geodir_location_page_id()) {
        add_action('template_redirect', 'geodir_set_location_canonical_urls', 1);
    }
}

/**
 * Modify canonical links.
 *
 * @since 1.0.0
 * @since 1.4.0 Filter added to fix conflict canonical url with WordPress SEO by Yoast.
 * @package GeoDirectory_Location_Manager
 */
function geodir_set_location_canonical_urls()
{
    remove_action('wp_head', 'rel_canonical');
    add_action('wp_head', 'geodir_location_rel_canonical', 9);
    add_filter('wpseo_canonical', 'geodir_location_remove_wpseo_canonical');
}

/**
 * Adds rel='canonical' tag to links.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wp WordPress object.
 */
function geodir_location_rel_canonical()
{
    global $wp;

    if (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == geodir_location_page_id()) {
        $link = geodir_get_location_link();

        if (get_option('geodir_set_as_home') && $link == geodir_get_location_link('base')) {
            $link = get_bloginfo('url');
        } else {
            if (get_option('permalink_structure') != '') {
                $link = trim($link);
                $link = rtrim($link, "/") . "/";
            }
        }

        echo "<link rel='canonical' href='$link' />\n";
    }
}


add_action('init', 'geodir_remove_parse_request_core');
/**
 * Removes {@see geodir_set_location_var_in_session_in_core} function from parse_request.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_remove_parse_request_core()
{
    remove_filter('parse_request', 'geodir_set_location_var_in_session_in_core');
}

/* category + location description */
if (is_admin()) {
    add_action('edit_tag_form_fields', 'geodir_location_cat_loc_desc');
    add_filter('geodir_plugins_uninstall_settings', 'geodir_location_uninstall_settings', 10, 1);
}
/**
 * Adds additional description form fields to the listing category.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param $row
 */
function geodir_location_cat_loc_desc($row) {
    global $wpdb, $wp_version;

    if (!is_admin()) {
        return;
    }

    $taxonomy = !empty($row) && !empty($row->taxonomy) ? $row->taxonomy : '';
    if (!$taxonomy) {
        return;
    }
    $taxObject = get_taxonomy($taxonomy);

    $post_type = isset($taxObject->object_type[0]) ? $taxObject->object_type[0] : '';

    if (!$post_type) {
        return;
    }

    if ($taxonomy != $post_type . 'category') {
        return;
    }
    $sql = "SELECT loc.location_id, loc.country, loc.region, loc.city, loc.country_slug, loc.region_slug, loc.city_meta FROM " . POST_LOCATION_TABLE . " AS loc ORDER BY loc.city ASC";
    $locations = $wpdb->get_results($sql);
    if (empty($locations)) {
        return;
    }

    $term_id = $row->term_id;

    $gd_cat_loc_default = 1;

    $option_name = 'geodir_cat_loc_' . $post_type . '_' . $term_id;
    $cat_loc_option = get_option($option_name);

    $gd_cat_loc_default = !empty($cat_loc_option) && isset($cat_loc_option['gd_cat_loc_default']) ? (int)$cat_loc_option['gd_cat_loc_default'] : $gd_cat_loc_default;
    if (isset($_REQUEST['topdesc_type'])) {
        $gd_cat_loc_default = true;
    }

    $is_default = true;
    if ($gd_cat_loc_default == 0) {
        $is_default = false;
    }

    $show_countries = get_option('geodir_enable_country') == 'default' && get_option('geodir_location_hide_country_part') ? false : true;
    $show_regions = get_option('geodir_enable_region') == 'default' && get_option('geodir_location_hide_region_part') ? false : true;

    $all_countries = array();
    $all_regions = array();
    $location_options_arr = array();
    $location_options = '';
    $count = 0;
    if (isset($_REQUEST['gd_location'])) {
        $gd_location = (int)$_REQUEST['gd_location'];
    }
    foreach ($locations as $location) {
        $count++;
        $location_id = (int)$location->location_id;
        if ($count == 1 && !isset($gd_location)) {
            $gd_location = $location_id;
        }
        $country = $location->country;
        $region = $location->region;
        $city = $location->city;
        $location_name = $city . ', ' . $region . ', ' . __($country, 'geodirectory');
        $location_options_arr[] = array('value' => $location_id, 'label' => $location_name);
        $selected = $gd_location == $location_id ? 'selected="selected"' : '';
        if ($gd_location == $location_id) {
            $gd_location_name = $location_name;
        }
        $location_options .= '<option value="' . $location_id . '" ' . $selected . '>' . $location_name . '</option>';

        if (empty($all_countries[$location->country_slug])) {
            $all_countries[$location->country_slug] = __($country, 'geodirectory');
        }

        if ($show_regions && empty($all_regions[$location->country_slug][$location->region_slug])) {
            $all_regions[$location->country_slug][$location->region_slug] = $region;
        }
    }

    $option_name = 'geodir_cat_loc_' . $post_type . '_' . $term_id . '_' . $gd_location;
    $option = get_option($option_name);
    $gd_cat_loc_desc = !empty($option) && isset($option['gd_cat_loc_desc']) ? $option['gd_cat_loc_desc'] : '';
    if (isset($_REQUEST['gd_cat_loc'])) {
        $gd_cat_loc_desc = $_REQUEST['gd_cat_loc'];
    }
    $description = esc_attr($gd_cat_loc_desc);

    $country_description = '';
    $region_description = '';
    $country_options = '';
    $region_options = '';

    $gd_cat_country = isset($_REQUEST['gd_cat_country']) ? sanitize_text_field($_REQUEST['gd_cat_country']) : '';
    $gd_cat_region = isset($_REQUEST['gd_cat_region']) ? sanitize_text_field($_REQUEST['gd_cat_region']) : '';
    $gd_cat_region_country = isset($_REQUEST['gd_cat_region_country']) ? sanitize_text_field($_REQUEST['gd_cat_region_country']) : '';

    if (($show_countries || $show_regions) && !empty($all_countries)) {
        asort($all_countries);

        $i = 0;

        foreach ($all_countries as $country_slug => $country_name) {
            if ($show_countries) {
                if ($i == 0 && empty($gd_cat_country)) {
                    $gd_cat_country = $country_slug;
                }

                $selected = $gd_cat_country == $country_slug ? 'selected="selected"' : '';
                $country_options .= '<option value="' . $country_slug . '" ' . $selected . '>' . $country_name . '</option>';
            }

            if ($i == 0 && empty($gd_cat_region_country) && empty($gd_cat_region)) {
                $gd_cat_region_country = $country_slug;
            }

            if ($show_regions && !empty($all_regions[$country_slug])) {
                $country_regions = $all_regions[$country_slug];
                asort($country_regions);

                $region_options .= '<optgroup data-country="' . esc_attr($country_slug) . '" label="' . esc_attr($country_name) . '">';

                $j = 0;

                foreach ($country_regions as $region_slug => $region_name) {
                    if ($i == 0 && $j == 0 && empty($gd_cat_region)) {
                        $gd_cat_region = $region_slug;
                    }

                    $selected = $country_slug == $gd_cat_region_country && $gd_cat_region == $region_slug ? 'selected="selected"' : '';
                    $region_options .= '<option value="' . $region_slug . '" ' . $selected . '>' . $region_name . '</option>';

                    $j++;
                }

                $region_options .= '</optgroup>';
            }

            $i++;
        }

        if ($show_countries) {
            $country_description = geodir_location_get_term_top_desc($term_id, $gd_cat_country, $post_type, 'country');

            if (isset($_REQUEST['gd_cat_loc_country'])) {
                $country_description = esc_attr($_REQUEST['gd_cat_loc_country']);
            }
        }

        if ($show_regions) {
            $region_description = geodir_location_get_term_top_desc($term_id, $gd_cat_region, $post_type, 'region', $gd_cat_region_country);

            if (isset($_REQUEST['gd_cat_loc_region'])) {
                $region_description = esc_attr($_REQUEST['gd_cat_loc_region']);
            }
        }
    }

    $gd_cat_country_name = !empty($all_countries[$gd_cat_country]) ? $all_countries[$gd_cat_country] : '';
    $gd_cat_region_name = !empty($all_regions[$gd_cat_region_country][$gd_cat_region]) ? $all_regions[$gd_cat_region_country][$gd_cat_region] . ', ' : '';
    $gd_cat_region_name .= !empty($all_countries[$gd_cat_region_country]) ? $all_countries[$gd_cat_region_country] : '';
    ?>
    <tr class="form-field topdesc_type">
        <th scope="row"><label for="topdesc_type"><?php echo __('Category Top Description', 'geodirlocation'); ?></label>
        </th>
        <td><input type="checkbox" id="topdesc_type" name="topdesc_type" class="rw-checkbox" value="1" <?php echo checked($is_default, true, false); ?> /> <?php echo _e('Use description of default for all locations', 'geodirlocation'); ?><br/><span class="description"><?php echo __('%location% tag available here', 'geodirlocation'); ?></span>
        </td>
    </tr>
    <?php
    if ($show_countries && !empty($country_options)) {
        $count = 1;
        $field_id = 'gd_cat_loc_country';
        $field_name = 'gd_cat_loc_country';
    ?>
    <tr class="form-field location-top-desc" <?php echo ($is_default ? 'style="display:none"' : ''); ?>>
        <th scope="row"><label for=""><?php echo __('Category + Country Top Description', 'geodirlocation'); ?></label><br/><span class="description"><?php echo __('(Leave blank to display default description of category for location)', 'geodirlocation'); ?></span>
        </th>
        <td class="all-locations">
            <select name="gd_location_country" id="gd_location_country" class="gd-location-list" onchange="javascript:changeCatLocation('<?php echo $term_id; ?>', '<?php echo $post_type; ?>', 'country', this);"><?php echo $country_options; ?></select><span class="gd-loc-progress gd-catloc-status"><i class="fa fa-refresh fa-spin"></i> <?php _e('Saving...', 'geodirlocation'); ?></span><span class="gd-loc-done gd-catloc-status"><i class="fa fa-check-circle"></i> <?php _e('Saved', 'geodirlocation'); ?></span><span class="gd-loc-fail gd-catloc-status"><i class="fa fa-exclamation-circle"></i> <?php _e('Not saved!', 'geodirlocation'); ?></span>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td class="cat-loc-editor cat-loc-row-<?php echo $count; ?>">
                            <label for="<?php echo $field_id; ?>">&raquo; <?php echo wp_sprintf(__('Category description for location %s', 'geodirlocation'), '<b id="lbl-location-name">' . $gd_cat_country_name . '</b>'); ?></label>
                            <?php if (version_compare($wp_version, '3.2.1') < 1) { ?>
                                <textarea class="at-wysiwyg theEditor large-text cat-loc-desc" name="<?php echo  $field_name; ?>" id="<?php echo $field_id; ?>" cols="40" rows="10"><?php echo $country_description; ?></textarea>
                            <?php } else {
                                $settings = array('textarea_name' => $field_name, 'media_buttons' => true, 'editor_class' => 'at-wysiwyg cat-loc-desc', 'textarea_rows' => 10);
                                // Use new wp_editor() since WP 3.3
                                wp_editor(stripslashes(html_entity_decode($country_description)), $field_id, $settings);
                            } ?>
                            <div id="<?php echo $field_id; ?>-values" style="display:none!important">
                                <input type="hidden" name="gd_loc_country" value="<?php echo $gd_cat_country; ?>"/>
                            </div>
                           <script type="text/javascript">jQuery('textarea#<?php echo $field_id;?>').attr('onchange', "javascript:saveCatLocation(this, 'country');");</script>
                       </td>
                    </tr>
                </tbody>
            </table>
            <span class="description"><?php _e('Description auto saved on change value.', 'geodirlocation'); ?></span>
        </td>
    </tr>
    <?php } ?>
    <?php if ($show_regions && !empty($region_options)) { ?>
    <tr class="form-field location-top-desc" <?php echo ($is_default ? 'style="display:none"' : ''); ?>>
        <th scope="row"><label for=""><?php echo __('Category + Region Top Description', 'geodirlocation'); ?></label><br/><span class="description"><?php echo __('(Leave blank to display default description of category for location)', 'geodirlocation'); ?></span>
        </th>
        <td class="all-locations"><select name="gd_location_region" id="gd_location_region" class="gd-location-list" onchange="javascript:changeCatLocation('<?php echo $term_id; ?>', '<?php echo $post_type; ?>', 'region', this);"><?php echo $region_options; ?></select><span class="gd-loc-progress gd-catloc-status"><i class="fa fa-refresh fa-spin"></i> <?php _e('Saving...', 'geodirlocation'); ?></span><span class="gd-loc-done gd-catloc-status"><i class="fa fa-check-circle"></i> <?php _e('Saved', 'geodirlocation'); ?></span><span class="gd-loc-fail gd-catloc-status"><i class="fa fa-exclamation-circle"></i> <?php _e('Not saved!', 'geodirlocation'); ?></span>
            <table class="form-table">
                <tbody>
                <?php
                $count = 1;
                $field_id = 'gd_cat_loc_region';
                $field_name = 'gd_cat_loc_region';

                echo '<tr><td class="cat-loc-editor cat-loc-row-' . $count . '">';
                echo '<label for="' . $field_id . '">&raquo; ' . sprintf(__('Category description for location %s', 'geodirlocation'), '<b id="lbl-location-name">' . $gd_cat_region_name . '</b>') . '</label>';
                if (version_compare($wp_version, '3.2.1') < 1) {
                    echo '<textarea class="at-wysiwyg theEditor large-text cat-loc-desc" name="' . $field_name . '" id="' . $field_id . '" cols="40" rows="10">' . $region_description . '</textarea>';
                } else {
                    $settings = array('textarea_name' => $field_name, 'media_buttons' => true, 'editor_class' => 'at-wysiwyg cat-loc-desc', 'textarea_rows' => 10);
                    // Use new wp_editor() since WP 3.3
                    wp_editor(stripslashes(html_entity_decode($region_description)), $field_id, $settings);
                }
                ?>
                <div id="<?php echo $field_id; ?>-values" style="display:none!important">
                    <input type="hidden" name="gd_loc_region" value="<?php echo $gd_cat_region; ?>"/>
                    <input type="hidden" name="gd_loc_region_country" value="<?php echo $gd_cat_region_country; ?>"/>
                </div>
                <script type="text/javascript">jQuery('textarea#<?php echo $field_id;?>').attr('onchange', "javascript:saveCatLocation(this, 'region');");</script>
                <?php
                echo '</td></tr>';
                ?></tbody>
            </table>
            <span class="description"><?php _e('Description auto saved on change value.', 'geodirlocation'); ?></span>
        </td>
    </tr>
    <?php } ?>
    <tr class="form-field location-top-desc" <?php echo ($is_default ? 'style="display:none"' : ''); ?>>
        <th scope="row"><label for=""><?php echo __('Category + City Top Description', 'geodirlocation'); ?></label><br/><span class="description"><?php echo __('(Leave blank to display default description of category for location)', 'geodirlocation'); ?></span>
        </th>
        <td class="all-locations"><select name="gd_location" id="gd_location" class="gd-location-list" onchange="javascript:changeCatLocation('<?php echo $term_id; ?>', '<?php echo $post_type; ?>', 'city', this);"><?php echo $location_options; ?></select><span class="gd-loc-progress gd-catloc-status"><i class="fa fa-refresh fa-spin"></i> <?php _e('Saving...', 'geodirlocation'); ?></span><span class="gd-loc-done gd-catloc-status"><i class="fa fa-check-circle"></i> <?php _e('Saved', 'geodirlocation'); ?></span><span class="gd-loc-fail gd-catloc-status"><i class="fa fa-exclamation-circle"></i> <?php _e('Not saved!', 'geodirlocation'); ?></span>
            <table class="form-table">
                <tbody>
                <?php
                $count = 1;
                $field_id = 'gd_cat_loc';
                $field_name = 'gd_cat_loc';

                echo '<tr><td class="cat-loc-editor cat-loc-row-' . $count . '">';
                echo '<label for="' . $field_id . '">&raquo; ' . sprintf(__('Category description for location %s', 'geodirlocation'), '<b id="lbl-location-name">' . $gd_location_name . '</b>') . '</label>';
                if (version_compare($wp_version, '3.2.1') < 1) {
                    echo '<textarea class="at-wysiwyg theEditor large-text cat-loc-desc" name="' . $field_name . '" id="' . $field_id . '" cols="40" rows="10">' . $description . '</textarea>';
                } else {
                    $settings = array('textarea_name' => $field_name, 'media_buttons' => true, 'editor_class' => 'at-wysiwyg cat-loc-desc', 'textarea_rows' => 10);
                    // Use new wp_editor() since WP 3.3
                    wp_editor(stripslashes(html_entity_decode($description)), $field_id, $settings);
                }
                ?>
                <div id="<?php echo $field_id; ?>-values" style="display:none!important"><input type="hidden" id="gd_locid" name="gd_locid" value="<?php echo $gd_location; ?>"/><input type="hidden" id="gd_posttype" name="gd_posttype" value="<?php echo $post_type; ?>"/><input type="hidden" id="gd_catid" name="gd_catid" value="<?php echo $term_id; ?>"/></div>
                <script type="text/javascript">jQuery('textarea#<?php echo $field_id;?>').attr('onchange', "javascript:saveCatLocation(this, 'city');");</script>
                <?php
                echo '</td></tr>';
                ?></tbody>
            </table>
            <span class="description"><?php _e('Description auto saved on change value.', 'geodirlocation'); ?></span>
        </td>
    </tr>
    <?php
}

add_action('admin_head', 'geodir_location_cat_loc_add_css');
/**
 * Adds category location styles to head.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global string $pagenow The current screen.
 */
function geodir_location_cat_loc_add_css()
{
    global $pagenow;

    $taxonomy = isset($_REQUEST['taxonomy']) ? $_REQUEST['taxonomy'] : '';
    $action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' || $pagenow == 'term.php' ? true : false;
    if (is_admin() && $taxonomy && $action && strpos($taxonomy, 'category') !== false) {
        ?>
        <style>td.cat-loc-editor {
                padding-top: 10px;
                padding-bottom: 12px;
                border: 1px solid #dedede
            }

            .all-locations > table {
                margin-top: 0
            }

            .cat-loc-editor > label {
                padding-bottom: 10px;
                display: block
            }

            textarea.cat-loc-desc {
                width: 100% !important
            }

            .default-top-desc iframe, .default-top-desc textarea {
                min-height: 400px !important
            }

            .cat-loc-editor iframe {
                min-height: 234px !important
            }

            .cat-loc-editor textarea {
                min-height: 256px !important
            }

            .location-top-desc .description {
                font-weight: normal
            }

            #ct_cat_top_desc {
                width: 100% !important
            }

            select.gd-location-list {
                margin-bottom: 5px;
                margin-left: 0;
            }</style>
        <?php
    }
}

add_filter('tiny_mce_before_init', 'add_idle_function_to_tinymce');
/**
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param $initArray
 * @return mixed
 */
function add_idle_function_to_tinymce($initArray) {
    if (isset($initArray['selector'])) {
        if ($initArray['selector'] == '#gd_cat_loc_country') {
            $initArray['setup'] = 'function(ed) { ed.onChange.add(function(ob, e) { var content = ob.getContent(); if (ob.id=="gd_cat_loc_country") { saveCatLocation(ob, "country", content); } }); }';
        } else if ($initArray['selector'] == '#gd_cat_loc_region') {
            $initArray['setup'] = 'function(ed) { ed.onChange.add(function(ob, e) { var content = ob.getContent(); if (ob.id=="gd_cat_loc_region") { saveCatLocation(ob, "region", content); } }); }';
        } else if ($initArray['selector'] == '#gd_cat_loc') {
            $initArray['setup'] = 'function(ed) { ed.onChange.add(function(ob, e) { var content = ob.getContent(); if (ob.id=="gd_cat_loc") { saveCatLocation(ob, "city", content); } }); }';
        }
    }
    return $initArray;
}

add_action('admin_footer', 'geodir_location_cat_loc_add_script', 99);
/**
 * Adds category location javascript to footer.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global string $pagenow The current screen.
 */
function geodir_location_cat_loc_add_script() {
    global $pagenow;

    $taxonomy = isset($_REQUEST['taxonomy']) ? $_REQUEST['taxonomy'] : '';
    $action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' || $pagenow == 'term.php' ? true : false;
    if (is_admin() && $taxonomy && $action && strpos($taxonomy, 'category') !== false) {
        $show_countries = get_option('geodir_enable_country') == 'default' && get_option('geodir_location_hide_country_part') ? false : true;
        $show_regions = get_option('geodir_enable_region') == 'default' && get_option('geodir_location_hide_region_part') ? false : true;
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#wp-ct_cat_top_desc-wrap').closest('tr').addClass('default-top-desc');
                jQuery('.default-top-desc > th > label').hide();
                jQuery("#topdesc_type").change(function (e) {
                    e.preventDefault();
                    var $input = jQuery(this);
                    if ($input.is(":checked")) {
                        jQuery('.default-top-desc').show();
                        jQuery('.location-top-desc').hide();
                    } else {
                        jQuery('.default-top-desc').hide();
                        jQuery('.location-top-desc').show();
                    }
                });
                jQuery("#topdesc_type").trigger('change');
            });

            function saveCatLocation(obj, type, content) {
                var locid, country, region, $tr;
                var catid = $('#gd_catid').val();
                var posttype = $('#gd_posttype').val();
                if (!catid || !posttype) {
                    return;
                }

                if (type == 'country') {
                    var country = $('[name="gd_loc_country"]').val();
                    if (!country) {
                        return;
                    }
                    $tr = $('[name="gd_loc_country"]').closest('.location-top-desc');
                } else if (type == 'region') {
                    country = $('[name="gd_loc_region_country"]').val();
                    region = $('[name="gd_loc_region"]').val();
                    if (!country || !region) {
                        return;
                    }
                    $tr = $('[name="gd_loc_region"]').closest('.location-top-desc');
                } else {
                    locid = $('#gd_locid').val();
                    if (!locid) {
                        return;
                    }
                    $tr = $('#gd_locid').closest('.location-top-desc');
                }

                if (typeof content == 'undefined') {
                    content = $(obj).val();
                }
                $('.gd-catloc-status', $tr).hide();
                $('.gd-loc-progress', $tr).show();

                var _wpnonce = $('#gd_location').closest('form').find('#_wpnonce').val();
                var loc_default = $('#topdesc_type').is(':checked') == true ? 1 : 0;

                var postData = {
                    action: 'geodir_locationajax_action',
                    location_ajax_action: 'geodir_save_cat_location',
                    posttype: posttype,
                    wpnonce: _wpnonce,
                    catid: catid,
                    loc_default: loc_default,
                    content: content,
                };

                if (type == 'country') {
                    postData._type = 'country';
                    postData.country = country;
                } else if (type == 'region') {
                    postData._type = 'region';
                    postData.country = country;
                    postData.region = region;
                } else {
                    postData._type = 'city';
                    postData.locid = locid;
                }

                jQuery.post(geodir_location_all_js_msg.geodir_location_admin_ajax_url, postData).done(function (data) {
                    $('.gd-catloc-status', $tr).hide();
                    if (data == 'FAIL') {
                        $('.gd-loc-fail', $tr).show();
                    } else {
                        $('.gd-loc-done', $tr).show();
                    }
                });
            }

            function changeCatLocation(catid, posttype, type, obj) {
                var locid, loc_name, country, region, field;

                $('.gd-catloc-status').hide();

                if (!catid || !posttype) {
                    return;
                }

                if (type == 'country') {
                    var country = $(obj).val();
                    if (!country) {
                        return;
                    }

                    field = 'gd_cat_loc_country';
                    loc_name = $("#gd_location_country option:selected").text();
                    jQuery('[name="gd_loc_country"]').val(country);
                } else if (type == 'region') {
                    country = $(obj).find('option:selected').closest('optgroup').data('country');
                    region = $(obj).val();
                    if (!country || !region) {
                        return;
                    }

                    loc_name = $("#gd_location_region option:selected").text() + ', ' + $(obj).find('option:selected').closest('optgroup').attr('label');
                    field = 'gd_cat_loc_region';
                    jQuery('[name="gd_loc_region"]').val(region);
                    jQuery('[name="gd_loc_region_country"]').val(country);
                } else {
                    locid = $(obj).val();
                    if (!locid) {
                        return;
                    }

                    field = 'gd_cat_loc';
                    loc_name = $("#gd_location option:selected").text();
                    jQuery("#gd_locid").val(locid);
                }

                $(obj).closest('.location-top-desc').find('#lbl-location-name').text(loc_name);

                var _wpnonce = $('#gd_location').closest('form').find('#_wpnonce').val();
                var is_tinymce = typeof tinymce != 'undefined' && typeof tinymce.editors != 'undefined' && typeof tinymce.editors[field] != 'undefined' ? true : false;
                if (is_tinymce) {
                    tinymce.editors[field].setProgressState(true);
                }

                var postData = {
                    action: 'geodir_locationajax_action',
                    location_ajax_action: 'geodir_change_cat_location',
                    posttype: posttype,
                    wpnonce: _wpnonce,
                    catid: catid,
                };

                if (type == 'country') {
                    postData._type = 'country';
                    postData.country = country;
                } else if (type == 'region') {
                    postData._type = 'region';
                    postData.country = country;
                    postData.region = region;
                } else {
                    postData._type = 'city';
                    postData.locid = locid;
                }

                jQuery.post(geodir_location_all_js_msg.geodir_location_admin_ajax_url, postData).done(function (data) {
                    if (data != 'FAIL') {
                        $('#' + field).val(data);

                        if (is_tinymce) {
                            tinymce.editors[field].setContent(data);
                        }
                    }

                    if (is_tinymce) {
                        tinymce.editors[field].setProgressState(false);
                    }
                });
            }
        </script>
        <?php
    }
}

if (is_admin()) {
    add_action('edited_term', 'geodir_location_save_cat_loc_desc', 10, 2);
    add_action('geodir_import_export', 'geodir_location_import_export', 10, 3);
}
/**
 * Save category and location description.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param int|string $term_id The term ID.
 * @param int $tt_id The term taxonomy ID.
 */
function geodir_location_save_cat_loc_desc($term_id, $tt_id)
{
    if (!is_admin()) {
        return;
    }
    $taxonomy = isset($_REQUEST['taxonomy']) ? $_REQUEST['taxonomy'] : '';
    $topdesc_type = isset($_REQUEST['topdesc_type']) ? $_REQUEST['topdesc_type'] : '';
    $gd_locid = isset($_REQUEST['gd_locid']) ? $_REQUEST['gd_locid'] : '';
    if (!$gd_locid || !$taxonomy) {
        return;
    }
    $taxObject = get_taxonomy($taxonomy);
    $post_type = $taxObject->object_type[0];
    if (!$post_type) {
        return;
    }
    if ($taxonomy != $post_type . 'category') {
        return;
    }
    $option = array();
    $option['gd_cat_loc_default'] = (int)$topdesc_type;
    $option['gd_cat_loc_cat_id'] = (int)$term_id;
    $option['gd_cat_loc_post_type'] = $post_type;
    $option['gd_cat_loc_taxonomy'] = $taxonomy;
    $option_name = 'geodir_cat_loc_' . $post_type . '_' . $term_id;

    update_option($option_name, $option);
}

if (!is_admin()) {
    add_action('wp_print_scripts', 'geodir_location_remove_action_listings_description', 100);
}
/**
 * Remove listing description and add the new description.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geodir_location_remove_action_listings_description()
{
    // remove default description
    remove_action('geodir_listings_page_description', 'geodir_action_listings_description');

    // add action to display description
    add_action('geodir_listings_page_description', 'geodir_location_action_listings_description', 100);
}

/**
 * Adds listing description to the page.
 *
 * @since 1.0.0
 * @since 1.4.9 Modified to filter neighbourhood in category top description.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global object $wp_query WordPress Query object.
 */
function geodir_location_action_listings_description() {
    global $wpdb, $wp_query;
    $current_term = $wp_query->get_queried_object();
    $post_type = geodir_get_current_posttype();

    if (isset($current_term->term_id) && $current_term->term_id != '' && $term_id = $current_term->term_id) {
        $term_desc = term_description($term_id, $post_type . '_tags');
        $saved_data = stripslashes(geodir_get_tax_meta($term_id, 'ct_cat_top_desc', false, $post_type));

        if ($term_desc && !$saved_data) {
            $saved_data = $term_desc;
        }

        $default_location = geodir_get_default_location();
        /**
         * Filter the Everywhere text in location description.
         *
         * @since 1.5.6
         * 
         * @param string $replace_location Everywhere text.
         */
        $replace_location = apply_filters('geodir_location_description_everywhere_text', __('Everywhere', 'geodirlocation'));

        $gd_country = get_query_var('gd_country');
        $gd_region = get_query_var('gd_region');
        $gd_city = get_query_var('gd_city');
        
        $location_type = '';
        if (!empty($gd_country)) {
            $location_type = 'country';
        }
        if (!empty($gd_region)) {
            $location_type = 'region';
        }
        if (!empty($gd_city)) {
            $location_type = 'city';
        }

        if ($location_type == 'country' || $location_type == 'region' || $location_type == 'city') {
            if (get_option('geodir_enable_country') == 'default' && !empty($default_location->country_slug)) {
                $gd_country = $default_location->country_slug;
            }

            if ($location_type != 'country') {
                if (get_option('geodir_enable_region') == 'default' && !empty($default_location->region_slug)) {
                    $gd_region = $default_location->region_slug;
                }

                if ($location_type != 'region') {
                    if (get_option('geodir_enable_city') == 'default' && !empty($default_location->city_slug)) {
                        $gd_city = $default_location->city_slug;
                    }
                }
            }
        }

        $current_location = '';
        if ($gd_country != '') {
            $location_type = 'country';
            $current_location = get_actual_location_name('country', $gd_country, true);
        }
        if ($gd_region != '') {
            $location_type = 'region';
            $current_location = get_actual_location_name('region', $gd_region);
        }
        if ($gd_city != '') {
            $location_type = 'city';
            $current_location = get_actual_location_name('city', $gd_city);
        }

        $default_option = get_option('geodir_cat_loc_' . $post_type . '_' . $term_id);
        $show_default = !empty($default_option) && !empty($default_option['gd_cat_loc_default']) ? true : false;
        if (!$show_default) {
            $saved_data = $term_desc;
        }

        $location_description = '';
        if ($location_type == 'city') {
            $location_info = geodir_city_info_by_slug($gd_city, $gd_country, $gd_region);

            $replace_location = !empty($location_info) ? $location_info->city : $replace_location;
            $location_id = !empty($location_info) ? $location_info->location_id : '';

            if (!$show_default && $location_id) {
                $location_description = geodir_location_get_term_top_desc($term_id, $location_id, $post_type, 'city');
            }
        } else if ($location_type == 'region') {
            $replace_location = geodir_get_current_location(array('what' => 'region', 'echo' => false));

            if (!$show_default && $gd_region) {
                $location_description = geodir_location_get_term_top_desc($term_id, $gd_region, $post_type, 'region', $gd_country);
            }
        } else if ($location_type == 'country') {
            $replace_location = geodir_get_current_location(array('what' => 'country', 'echo' => false));
            $replace_location = __($replace_location, 'geodirectory');

            if (!$show_default && $gd_country) {
                $location_description = geodir_location_get_term_top_desc($term_id, $gd_country, $post_type, 'country');
            }
        }
        if (get_option( 'location_neighbourhoods' ) && ($gd_neighbourhood = get_query_var('gd_neighbourhood')) != '') {
            $current_location = get_actual_location_name('neighbourhood', $gd_neighbourhood, true);
        }
        $replace_location = $current_location != '' ? $current_location : $replace_location;

        if (trim($location_description) != '') {
            $saved_data = stripslashes($location_description);
        }
        if (!empty($saved_data)) {
            $saved_data = geodir_replace_location_variables($saved_data);
        }
        $saved_data = str_replace('%location%', $replace_location, $saved_data);

        // stop payment manager filtering content length
        $filter_priority = has_filter( 'the_content', 'geodir_payments_the_content' );
        if ( false !== $filter_priority ) {
            remove_filter( 'the_content', 'geodir_payments_the_content', $filter_priority );
        }

        $cat_description = apply_filters('the_content', $saved_data);

        if ( false !== $filter_priority ) {
            add_filter( 'the_content', 'geodir_payments_the_content', $filter_priority );
        }

        if ($cat_description) {
            echo '<div class="term_description">' . $cat_description . '</div>';
        }
    }
}

add_action('geodir_add_listing_codeaddress_before_geocode', 'geodir_add_listing_codeaddress_before_geocode_lm', 11);

/**
 * Disable geodir_codeAddress from location manager. Adds return to js.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_add_listing_codeaddress_before_geocode_lm()
{
    global $wpdb;
    if (get_option('location_set_address_disable')) {
        ?>
        return;// disable geodir_codeAddress from location manager
    <?php }
}

add_filter('geodir_auto_change_address_fields_pin_move', 'geodir_location_set_pin_disable', 10, 1);
/**
 * Filters the auto change address fields values when moving the map pin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param bool $val Whether to change the country, state, city values in fields.
 * @return string|bool
 */
function geodir_location_set_pin_disable($val)
{
    if (get_option('location_set_pin_disable')) {
        return '0';//return false
    }
    return $val;
}

/**
 * Set search near text.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $near The current near value.
 * @param string $default_near_text The default near value.
 * @return string Filtered near value.
 */
function geodir_location_set_search_near_text($near, $default_near_text = '')
{
    if (trim($near) == '1') {
        $near = trim($default_near_text) == '1' ? '' : $default_near_text;
    }

    return stripslashes($near);
}

add_filter('geodir_search_near_text', 'geodir_location_set_search_near_text', 1000, 2);

/**
 * Removes the canonical url on location page that added by Yoast WordPress SEO.
 *
 * @since 1.4.0
 * @package GeoDirectory_Location_Manager
 *
 * @param string $canonical The canonical URL
 * @return bool Empty value.
 */
function geodir_location_remove_wpseo_canonical($canonical)
{
    $canonical = false;

    return $canonical;
}

/**
 * Unset's the current location sessions.
 *
 * @since 1.4.0
 * @package GeoDirectory_Location_Manager
 * @global object $gd_session GeoDirectory Session object.
 */
function geodir_unset_location()
{
    global $gd_session;
    $gd_session->un_set(array('gd_multi_location', 'gd_city', 'gd_region', 'gd_country', 'gd_neighbourhood'));
}

/*
 * Unset the location if a user does a near search.
 */
if (isset($_REQUEST['snear']) && $_REQUEST['snear'] != '') {
    add_action('parse_request', 'geodir_unset_location', 500);
}

/**
 * Filters the map query for server side clustering.
 *
 * Alters the query to limit the search area to the bounds of the map view.
 *
 * @since 1.1.1
 * @param string $search The where query string for marker search.
 * @package GeoDirectory_Marker_Cluster
 */
function geodir_location_manager_location_me($search)
{
    $my_lat = filter_var($_REQUEST['my_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $my_lon = filter_var($_REQUEST['my_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $distance_in_miles = (get_option('geodir_search_dist')) ? get_option('geodir_search_dist') : 40;
    $data = geodir_lm_bounding_box($my_lat, $my_lon, sqrt($distance_in_miles));

    $lat_sw = filter_var($data[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $lat_ne = filter_var($data[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $lon_sw = filter_var($data[2], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $lon_ne = filter_var($data[3], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $lon_not = '';
    //if the corners span more than half the world

    if ($lon_ne > 0 && $lon_sw > 0 && $lon_ne < $lon_sw) {
        $lon_not = 'not';
    } elseif ($lon_ne < 0 && $lon_sw < 0 && $lon_ne < $lon_sw) {
        $lon_not = 'not';
    } elseif ($lon_ne < 0 && $lon_sw > 0 && ($lon_ne + 360 - $lon_sw) > 180) {
        $lon_not = 'not';
    } elseif ($lon_ne < 0 && $lon_sw > 0 && abs($lon_ne) + abs($lon_sw) > 180) {
        $lon_not = 'not';
    }

    if ($lon_ne == 180 && $lon_sw == -180) {
        return $search;
    }

    $search .= " AND pd.post_latitude between least($lat_sw,$lat_ne) and greatest($lat_sw,$lat_ne)  AND pd.post_longitude $lon_not between least($lon_sw,$lon_ne) and greatest($lon_sw,$lon_ne)";

    return $search;
}


function geodir_lm_bounding_box($lat_degrees, $lon_degrees, $distance_in_miles)
{

    $radius = 3963.1; // of earth in miles

    // bearings - FIX
    $due_north = deg2rad(0);
    $due_south = deg2rad(180);
    $due_east = deg2rad(90);
    $due_west = deg2rad(270);

    // convert latitude and longitude into radians
    $lat_r = deg2rad($lat_degrees);
    $lon_r = deg2rad($lon_degrees);

    // find the northmost, southmost, eastmost and westmost corners $distance_in_miles away
    // original formula from
    // http://www.movable-type.co.uk/scripts/latlong.html

    $northmost = asin(sin($lat_r) * cos($distance_in_miles / $radius) + cos($lat_r) * sin($distance_in_miles / $radius) * cos($due_north));
    $southmost = asin(sin($lat_r) * cos($distance_in_miles / $radius) + cos($lat_r) * sin($distance_in_miles / $radius) * cos($due_south));

    $eastmost = $lon_r + atan2(sin($due_east) * sin($distance_in_miles / $radius) * cos($lat_r), cos($distance_in_miles / $radius) - sin($lat_r) * sin($lat_r));
    $westmost = $lon_r + atan2(sin($due_west) * sin($distance_in_miles / $radius) * cos($lat_r), cos($distance_in_miles / $radius) - sin($lat_r) * sin($lat_r));


    $northmost = rad2deg($northmost);
    $southmost = rad2deg($southmost);
    $eastmost = rad2deg($eastmost);
    $westmost = rad2deg($westmost);

    // sort the lat and long so that we can use them for a between query
    if ($northmost > $southmost) {
        $lat1 = $southmost;
        $lat2 = $northmost;

    } else {
        $lat1 = $northmost;
        $lat2 = $southmost;
    }


    if ($eastmost > $westmost) {
        $lon1 = $westmost;
        $lon2 = $eastmost;

    } else {
        $lon1 = $eastmost;
        $lon2 = $westmost;
    }

    return array($lat1, $lat2, $lon1, $lon2);
}


add_filter('get_pagenum_link', 'geodir_lm_strip_location_from_blog_link', 10, 1);

/**
 * Removes the location from blog page links if added.
 *
 * @since 1.5.6
 * @package GeoDirectory_Location_Manager
 *
 * @param string $link The link maybe with location info in it.
 * @return string The link with no location info in it.
 */
function geodir_lm_strip_location_from_blog_link($link)
{

    if (strpos($link, '/category/') !== false) {

        $loc_home = home_url();

        if (function_exists('geodir_location_geo_home_link')) {
            remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
        }
        $real_home = home_url();
        if (function_exists('geodir_location_geo_home_link')) {
            add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
        }

        $link = str_replace($loc_home, trailingslashit($real_home), $link);

    }
    return $link;
}

add_action('init', 'geodir_sitemap_init', 10);
add_filter('wpseo_sitemap_index', 'geodir_sitemap_index', 10, 1);

add_action('admin_panel_init', 'geodir_location_admin_location_filter_init', 10);


add_action('geodir_diagnostic_tool', 'geodir_refresh_location_cat_counts_tool', 1);
/**
 * Adds location category count tool to GD Tools page.
 *
 * @since 1.4.8
 * @package GeoDirectory_Location_Manager
 */
function geodir_refresh_location_cat_counts_tool()
{
    ?>
    <tr>
        <td><?php _e('Location category counts', 'geodirlocation'); ?></td>
        <td>
            <small><?php _e('Refresh the category counts for each location (can take time on large sites)', 'geodirlocation'); ?></small>
        </td>
        <td><input type="button" value="<?php _e('Run', 'geodirlocation'); ?>"
                   class="button-primary geodir_diagnosis_button" data-diagnose="run_refresh_cat_count"/>
        </td>
    </tr>
    <?php
}

/**
 * Returns the html/js used to ajax refresh the location category count tool.
 *
 * @since 1.4.8
 * @package GeoDirectory_Location_Manager
 */
function geodir_diagnose_run_refresh_cat_count()
{
    global $wpdb, $plugin_prefix;

    $output_str = '';
    $city_count = geodir_cat_count_location('city');
    if (!$city_count) {
        _e('No cities found.', 'geodirlocation');
        exit;
    }

    $region_count = geodir_cat_count_location('region');
    $country_count = geodir_cat_count_location('country');

    $first_city = $wpdb->get_var("SELECT city_slug FROM " . POST_LOCATION_TABLE . " ORDER BY  `city_slug` ASC LIMIT 1");


    // city
    $output_str .= "<li class='gd-cat-count-progress-city'><h2 class='gd-cat-count-loc-title'>" . __('Cities', 'geodirlocation') . ": <span class='gd-cat-count-progress-city-name'></span></h2>";

    $output_str .= '<div id=\'gd_progressbar_box_city\'>
					  <div id="gd_progressbar" class="gd_progressbar">
						<div class="gd-progress-label"></div>
					  </div>
					</div>';

    $output_str .= '</li>';

    //region
    $output_str .= "<li class='gd-cat-count-progress-region'><h2 class='gd-cat-count-loc-title'>" . __('Regions', 'geodirlocation') . ": <span class='gd-cat-count-progress-region-name'></span></h2>";

    $output_str .= '<div id=\'gd_progressbar_box_region\'>
					  <div id="gd_progressbar" class="gd_progressbar">
						<div class="gd-progress-label"></div>
					  </div>
					</div>';

    $output_str .= '</li>';

    // country
    $output_str .= "<li class='gd-cat-count-progress-country'><h2 class='gd-cat-count-loc-title'>" . __('Countries', 'geodirlocation') . ": <span class='gd-cat-count-progress-country-name'></span></h2>";

    $output_str .= '<div id=\'gd_progressbar_box_country\'>
					  <div id="gd_progressbar" class="gd_progressbar">
						<div class="gd-progress-label"></div>
					  </div>
					</div>';

    $output_str .= '</li>';


    $info_div_class = "geodir_running_info";
    $fix_button_txt = '';

    echo "<ul class='gd-loc-cat-count-container $info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";
    ?>
    <script>
        jQuery('.gd_progressbar').each(function () {
            jQuery(this).progressbar({value: 0});
        });

        $gdIntCityCount = '<?php echo $city_count;?>';
        $gdIntRegionCount = '<?php echo $region_count;?>';
        $gdIntCountryCount = '<?php echo $country_count;?>';
        $gdCityCount = 0;
        $gdRegionCount = 0;
        $gdCountryCount = 0;

        setTimeout(function () {
            gd_loc_count_loc_terms('city', '<?php echo $first_city;?>');
            gd_progressbar('.gd-cat-count-progress-city', 0, '0% (0 / ' + $gdIntCityCount + ') <i class="fa fa-refresh fa-spin"></i><?php echo esc_attr(__('Calculating...', 'geodirlocation'));?>');
            gd_progressbar('.gd-cat-count-progress-region', 0, '0% (0 / ' + $gdIntRegionCount + ') <i class="fa fa-hourglass-start"></i><?php echo esc_attr(__('Waiting...', 'geodirlocation'));?>');
            gd_progressbar('.gd-cat-count-progress-country', 0, '0% (0 / ' + $gdIntCountryCount + ') <i class="fa fa-hourglass-start"></i><?php echo esc_attr(__('Waiting...', 'geodirlocation'));?>');

        }, 1000);

        function gd_loc_count_loc_terms($type, $loc) {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'html',
                data: {
                    action: 'geodir_location_cat_count_ajax',
                    gd_loc_type: $type,
                    gd_loc: $loc
                },
                beforeSend: function () {
                    jQuery('.gd-cat-count-progress-' + $type + '-name').html($loc);
                },
                success: function (data, textStatus, xhr) {
                    data = JSON.parse(data);

                    if ($type == 'city') {
                        $gdCityCount++;
                        var percentage = Math.round(($gdCityCount / $gdIntCityCount ) * 100);
                        percentage = percentage > 100 ? 100 : percentage;

                        if (data.loc_type && data.loc_name && data.loc_type == $type && data.loc_name != $loc) {
                            gd_loc_count_loc_terms(data.loc_type, data.loc_name);
                            gd_progressbar('.gd-cat-count-progress-city', percentage, '' + percentage + '% (' + ( $gdCityCount ) + ' / ' + $gdIntCityCount + ') <i class="fa fa-refresh fa-spin"></i><?php echo esc_attr(__('Calculating...', 'geodirlocation'));?>');
                        } else if (data.loc_type && data.loc_name && data.loc_type != $type && data.loc_name != $loc) {
                            gd_loc_count_loc_terms(data.loc_type, data.loc_name);
                            jQuery('.gd-cat-count-progress-city-name').html('');
                            gd_progressbar('.gd-cat-count-progress-city', percentage, '' + percentage + '% (' + ( $gdCityCount ) + ' / ' + $gdIntCityCount + ') <i class="fa fa-check"></i><?php echo esc_attr(__('Complete!', 'geodirlocation'));?>');
                        }

                    } else if ($type == 'region') {

                        $gdRegionCount++;
                        var percentage = Math.round(($gdRegionCount / $gdIntRegionCount ) * 100);
                        percentage = percentage > 100 ? 100 : percentage;

                        if (data.loc_type && data.loc_name && data.loc_type == $type && data.loc_name != $loc) {
                            gd_loc_count_loc_terms(data.loc_type, data.loc_name);
                            gd_progressbar('.gd-cat-count-progress-region', percentage, '' + percentage + '% (' + ( $gdRegionCount ) + ' / ' + $gdIntRegionCount + ') <i class="fa fa-refresh fa-spin"></i><?php echo esc_attr(__('Calculating...', 'geodirlocation'));?>');
                        } else if (data.loc_type && data.loc_name && data.loc_type != $type && data.loc_name != $loc) {
                            gd_loc_count_loc_terms(data.loc_type, data.loc_name);
                            jQuery('.gd-cat-count-progress-region-name').html('');
                            gd_progressbar('.gd-cat-count-progress-region', percentage, '' + percentage + '% (' + ( $gdRegionCount ) + ' / ' + $gdIntRegionCount + ') <i class="fa fa-check"></i><?php echo esc_attr(__('Complete!', 'geodirlocation'));?>');
                        }

                    } else if ($type == 'country') {

                        $gdCountryCount++;
                        var percentage = Math.round(($gdCountryCount / $gdIntCountryCount ) * 100);
                        percentage = percentage > 100 ? 100 : percentage;

                        if (data.loc_type && data.loc_name && data.loc_type == $type && data.loc_name != $loc) {
                            gd_loc_count_loc_terms(data.loc_type, data.loc_name);
                            gd_progressbar('.gd-cat-count-progress-country', percentage, '' + percentage + '% (' + ( $gdCountryCount ) + ' / ' + $gdIntCountryCount + ') <i class="fa fa-refresh fa-spin"></i><?php echo esc_attr(__('Calculating...', 'geodirlocation'));?>');
                        } else if (data.loc_type && data.loc_name && data.loc_type != $type && data.loc_name != $loc) {
                            gd_loc_count_loc_terms(data.loc_type, data.loc_name);
                            gd_progressbar('.gd-cat-count-progress-country', percentage, '' + percentage + '% (' + ( $gdCountryCount ) + ' / ' + $gdIntCountryCount + ') <i class="fa fa-check"></i><?php echo esc_attr(__('Complete!', 'geodirlocation'));?>');
                        } else if (data.loc_type && data.loc_type == 'end') {
                            gd_progressbar('.gd-cat-count-progress-country', percentage, '' + percentage + '% (' + ( $gdCountryCount ) + ' / ' + $gdIntCountryCount + ') <i class="fa fa-check"></i><?php echo esc_attr(__('Complete!', 'geodirlocation'));?>');

                            jQuery('.gd-cat-count-progress-country-name').html('');
                            jQuery(".gd-loc-cat-count-container").addClass("geodir_noproblem_info").removeClass("geodir_running_info");
                            alert('<?php _e('Complete!', 'geodirlocation');?>');
                        }

                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    alert(textStatus);
                }
            });
        }
    </script>
    <?php

}

/**
 * Gets the total count for city/region/country.
 *
 * @since 1.4.8
 * @package GeoDirectory_Location_Manager
 * @param string $type The location type, city/region/country.
 */
function geodir_cat_count_location($type)
{
    global $wpdb;
    $type = esc_sql($type);
    $count = $wpdb->get_var("SELECT COUNT(DISTINCT `" . $type . "_slug`) FROM " . POST_LOCATION_TABLE);

    return $count;
}

add_action('wp_ajax_geodir_location_cat_count_ajax', 'geodir_location_cat_count');

/**
 * Ajax function used to count the location category counts and return the next location to check.
 *
 * @since 1.4.8
 * @package GeoDirectory_Location_Manager
 */
function geodir_location_cat_count()
{
    global $wpdb;
    $gd_loc_type = '';
    if (sanitize_text_field($_POST['gd_loc_type']) == 'city') {
        $gd_loc_type = 'city';
        $gd_loc_type_next = 'region';
    } elseif (sanitize_text_field($_POST['gd_loc_type']) == 'region') {
        $gd_loc_type = 'region';
        $gd_loc_type_next = 'country';
    } elseif (sanitize_text_field($_POST['gd_loc_type']) == 'country') {
        $gd_loc_type = 'country';
        $gd_loc_type_next = 'end';
    }
    if (!$gd_loc_type) {
        exit;
    }
    $gd_loc = sanitize_text_field($_POST['gd_loc']);
    $loc = $wpdb->get_row($wpdb->prepare("SELECT location_id,country_slug as gd_country,region_slug as gd_region,city_slug as gd_city FROM " . POST_LOCATION_TABLE . " WHERE `" . $gd_loc_type . "_slug`=%s  GROUP BY `" . $gd_loc_type . "_slug` ORDER BY `" . $gd_loc_type . "_slug` ASC LIMIT 1", $gd_loc), ARRAY_A);
    $loc_id = $loc['location_id'];


    geodir_get_loc_term_count('term_count', $gd_loc, 'gd_' . $gd_loc_type, $loc, true);

    $sql = $wpdb->prepare("SELECT " . $gd_loc_type . "_slug FROM " . POST_LOCATION_TABLE . " WHERE " . $gd_loc_type . "_slug!=%s AND `" . $gd_loc_type . "_slug` > %s GROUP BY `" . $gd_loc_type . "_slug`  ORDER BY  `" . $gd_loc_type . "_slug` ASC LIMIT 1", $loc["gd_{$gd_loc_type}"], $loc["gd_{$gd_loc_type}"]);
    $loc_name = $wpdb->get_var($sql);

    if (!$loc_name && $gd_loc_type_next != 'end') {
        $gd_loc_type = $gd_loc_type_next;
        $sql = "SELECT " . $gd_loc_type . "_slug FROM " . POST_LOCATION_TABLE . "  GROUP BY `" . $gd_loc_type . "_slug`  ORDER BY  `" . $gd_loc_type . "_slug` ASC LIMIT 1";
        $loc_name = $wpdb->get_var($sql);
    } elseif (!$loc_name && $gd_loc_type_next == 'end') {
        $gd_loc_type = $gd_loc_type_next;
    }

    $result = array(
        'loc_type' => $gd_loc_type,
        'loc_name' => $loc_name,
        'loc' => $loc,
    );

    echo json_encode($result);
    gd_die();
}

/**
 * Filter location description text..
 *
 * @since 1.4.9
 *
 * @global object $wp WordPress object.
 *
 * @param string $description The location description text.
 * @param string $gd_country The current country slug.
 * @param string $gd_region The current region slug.
 * @param string $gd_city The current city slug.
 */
function geodir_location_neighbourhood_description( $description, $gd_country, $gd_region, $gd_city ) {
    global $wp;

    if ( !empty( $wp->query_vars['gd_neighbourhood'] ) && get_option( 'location_neighbourhoods' ) ) {
        $description = '';

        $location = geodir_city_info_by_slug( $gd_city, $gd_country, $gd_region );
        $location_id = !empty( $location ) && !empty( $location->location_id ) ? $location->location_id : 0;
        $hood = geodir_location_get_neighbourhood_by_id( $wp->query_vars['gd_neighbourhood'], true, $location_id );

        if ( !empty( $hood ) && !empty( $hood->hood_description ) ) {
            $description = stripslashes( __( $hood->hood_description, 'geodirlocation' ) );
        }
    }

    return $description;
}
add_filter('geodir_location_description', 'geodir_location_neighbourhood_description', 10, 4);

function geodir_fix_wpml_duplicate_slug($wp) {
    if (geodir_is_wpml() && empty($wp->query_vars['error']) && !empty($wp->query_vars['page_id'])) {
        $wp->query_vars['page_id'] =  geodir_wpml_object_id($wp->query_vars['page_id'], 'page', true);
    }

    return $wp;
}
add_action('parse_request', 'geodir_fix_wpml_duplicate_slug', 10, 1);
remove_filter('geodir_location_slug_check', 'geodir_location_slug_check');

/**
 * Filter location variables used in description text.
 *
 * @since 1.5.3
 *
 * @param string $description The location description text.
 * @param string $gd_country The current country slug.
 * @param string $gd_region The current region slug.
 * @param string $gd_city The current city slug.
 * @return string Filtered description.
 */
function geodir_location_description_filter_variables( $description, $gd_country, $gd_region, $gd_city ) {
    if ( !empty( $description ) ) {
        $description = geodir_replace_location_variables( $description );
    }

    return $description;
}
add_filter('geodir_location_description', 'geodir_location_description_filter_variables', 999, 4);

/**
 * Add neighbourhood location title variables.
 *
 * @since 1.5.3
 *
 * @param array $settings The settings array.
 * @return array Filtered array.
 */
function geodir_location_filter_title_meta_vars($settings) {
    foreach($settings as $index => $setting) {
        if (!empty($setting['id']) && $setting['id'] == 'geodir_meta_vars' && !empty($setting['type']) && $setting['type']== 'sectionstart' && get_option('location_neighbourhoods')) {
            $settings[$index]['desc'] = $setting['desc'] . ', %%location_neighbourhood%%, %%in_location_neighbourhood%%';
        }
    }
    return $settings;
}
add_filter('geodir_title_meta_settings', 'geodir_location_filter_title_meta_vars', 11, 1);

/**
 * Set neighbourhood as a default tab in location switcher.
 *
 * @since 1.5.3
 *
 * @param string $tab The location switcher default tab.
 * @return string Filtered default tab.
 */
function geodir_location_switcher_set_neighbourhood_tab($tab) {
    if (get_option('location_neighbourhoods') && get_option('geodir_enable_city') == 'default') {
        $tab = 'neighbourhood';
    }

    return $tab;
}
add_filter('geodir_location_switcher_default_tab', 'geodir_location_switcher_set_neighbourhood_tab', 10, 1);

/**
 * Filter everywhere_in_neighbourhood_dropdown option.
 *
 * @since 1.5.3
 *
 * @param bool Whether to show everywhere option in location switcher or not.
 * @param string $option Option name.
 * @return bool True if active false if disabled.
 */
function geodir_location_switcher_everywhere_in_neighbourhood($value, $option) {
    return true;
}
add_filter('pre_option_geodir_everywhere_in_neighbourhood_dropdown', 'geodir_location_switcher_everywhere_in_neighbourhood', 10, 2);

/**
 * Filters the WPML language switcher urls for location pages.
 *
 * @since 1.5.3
 *
 * @param array  $languages WPML active languages.
 * @return array Filtered languages.
 */
function geodir_location_wpml_filter_ls_languages($languages) {
    global $sitepress;
    
    if (geodir_is_geodir_page() && geodir_is_page('location')) {
        $location_terms = geodir_get_current_location_terms();
        $location_terms = geodir_remove_location_terms($location_terms);
        
        if (empty($location_terms)) {
            return $languages;
        }
        
        $default_language = $sitepress->get_default_language();
        $current_language = $sitepress->get_current_language();
        
        $permalink_structure = get_option('permalink_structure');
        
        foreach ( $languages as $code => $url) {            
            $sitepress->switch_lang( $code );
            
            $location_url = trailingslashit(geodir_get_location_link('base'));
            
            if ($permalink_structure) {
                $location_url .= implode('/', array_values($location_terms)) . '/';
            } else {
                $location_url = add_query_arg($location_terms, $location_url);
            }
            
            if ($location_url != $url['url']) {
                $languages[$code]['url'] = $location_url;
            }
        }
        
        $sitepress->switch_lang( $current_language );
    }

    return $languages;
}
add_filter( 'icl_ls_languages', 'geodir_location_wpml_filter_ls_languages', 11, 1 );

/**
 * Handle import/export for location manager.
 *
 * @since 1.5.4
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global object $current_user Current user object.
 * @global null|object $wp_filesystem WP_Filesystem object.
 * @return string Json data.
 */
function geodir_location_ajax_import_export() {
    global $wpdb, $plugin_prefix, $current_user, $wp_filesystem;
    
    error_reporting(0);

    $xstart = microtime(true);

    // try to set higher limits for import
    $max_input_time = ini_get('max_input_time');
    $max_execution_time = ini_get('max_execution_time');
    $memory_limit= ini_get('memory_limit');

    if(!$max_input_time || $max_input_time<3000){
        ini_set('max_input_time', 3000);
    }

    if(!$max_execution_time || $max_execution_time<3000){
        ini_set('max_execution_time', 3000);
    }

    if($memory_limit && str_replace('M','',$memory_limit)){
        if(str_replace('M','',$memory_limit)<256){
            ini_set('memory_limit', '256M');
        }
    }

    $json = array();

    if ( !current_user_can( 'manage_options' ) ) {
        wp_send_json( $json );
    }

    $task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : NULL;
    $nonce = isset( $_REQUEST['_nonce'] ) ? $_REQUEST['_nonce'] : NULL;
    $stat = isset( $_REQUEST['_st'] ) ? $_REQUEST['_st'] : false;

    if ( !wp_verify_nonce( $nonce, 'geodir_import_export_nonce' ) ) {
        wp_send_json( $json );
    }

    $post_type = isset( $_REQUEST['_pt'] ) ? $_REQUEST['_pt'] : NULL;
    $location_type = isset( $_REQUEST['_lt'] ) ? $_REQUEST['_lt'] : NULL;
    
    $chunk_per_page = isset( $_REQUEST['_n'] ) ? absint($_REQUEST['_n']) : NULL;
    $chunk_per_page = $chunk_per_page < 50 || $chunk_per_page > 100000 ? 5000 : $chunk_per_page;
    $chunk_page_no = isset( $_REQUEST['_p'] ) ? absint($_REQUEST['_p']) : 1;

    $wp_filesystem = geodir_init_filesystem();
    if (!$wp_filesystem) {
        $json['error'] = __( 'Filesystem ERROR: Could not access filesystem.', 'geodirectory' );
        wp_send_json( $json );
    }

    if (!empty($wp_filesystem) && isset($wp_filesystem->errors) && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
        $json['error'] = __( 'Filesystem ERROR: ' . $wp_filesystem->errors->get_error_message(), 'geodirectory' );
        wp_send_json( $json );
    }

    $csv_file_dir = geodir_path_import_export( false );
    if ( !$wp_filesystem->is_dir( $csv_file_dir ) ) {
        if ( !$wp_filesystem->mkdir( $csv_file_dir, FS_CHMOD_DIR ) ) {
            $json['error'] = __( 'ERROR: Could not create cache directory. This is usually due to inconsistent file permissions.', 'geodirectory' );
            wp_send_json( $json );
        }
    }

    switch ( $task ) {
        case 'export_cat_locations': {
            $file_url_base = geodir_path_import_export() . '/';
            $file_name = 'gd_cat_locations_' . date( 'dmyHi' );
            $file_url = $file_url_base . $file_name . '.csv';
            $file_path = $csv_file_dir . '/' . $file_name . '.csv';
            $file_path_temp = $csv_file_dir . '/gd_cat_locations_' . $nonce . '.csv';
            
            $items_count = isset( $_REQUEST['_t'] ) ? absint( $_REQUEST['_t'] ) : 0;
            
            if ( isset( $_REQUEST['_st'] ) ) {
                $line_count = (int)geodir_import_export_line_count( $file_path_temp );
                $percentage = count( $items_count ) > 0 && $line_count > 0 ? ceil( $line_count / $items_count ) * 100 : 0;
                $percentage = min( $percentage, 100 );
                
                $json['percentage'] = $percentage;
                wp_send_json( $json );
            } else {
                $chunk_file_paths = array();
                
                if ( !$items_count > 0 ) {
                    $json['error'] = __( 'No records to export.', 'geodirectory' );
                } else {
                    $chunk_per_page = min( $chunk_per_page, $items_count );
                    $chunk_total_pages = ceil( $items_count / $chunk_per_page );
                    
                    $j = $chunk_page_no;
                    $chunk_save_items = geodir_location_imex_cat_locations_data( $chunk_per_page, $j, $post_type, $location_type );
                    
                    $per_page = 500;
                    $per_page = min( $per_page, $chunk_per_page );
                    $total_pages = ceil( $chunk_per_page / $per_page );
                    
                    for ( $i = 0; $i <= $total_pages; $i++ ) {
                        $save_items = array_slice( $chunk_save_items , ( $i * $per_page ), $per_page );
                        
                        $clear = $i == 0 ? true : false;
                        geodir_save_csv_data( $file_path_temp, $save_items, $clear );
                    }
                    
                    if ( $wp_filesystem->exists( $file_path_temp ) ) {
                        $chunk_page_no = $chunk_total_pages > 1 ? '-' . $j : '';
                        $chunk_file_name = $file_name . $chunk_page_no . '.csv';
                        $file_path = $csv_file_dir . '/' . $chunk_file_name;
                        $wp_filesystem->move( $file_path_temp, $file_path, true );
                        
                        $file_url = $file_url_base . $chunk_file_name;
                        $chunk_file_paths[] = array('i' => $j . '.', 'u' => $file_url, 's' => size_format(filesize($file_path), 2));
                    }
                    
                    if ( !empty($chunk_file_paths) ) {
                        $json['total'] = $items_count;
                        $json['files'] = $chunk_file_paths;
                    } else {
                        $json['error'] = __( 'Fail, something wrong to create csv file.', 'geodirectory' );
                    }
                }
                wp_send_json( $json );
            }
        }
        break;
        case 'prepare_import':
        case 'import_catloc': {
            ini_set( 'auto_detect_line_endings', true );
            
            $uploads = wp_upload_dir();
            $uploads_dir = $uploads['path'];
            $uploads_subdir = $uploads['subdir'];
            
            $csv_file = isset( $_POST['_file'] ) ? $_POST['_file'] : NULL;
            $import_choice = isset( $_REQUEST['_ch'] ) ? $_REQUEST['_ch'] : 'skip';
            
            $csv_file_arr = explode( '/', $csv_file );
            $csv_filename = end( $csv_file_arr );
            $target_path = $uploads_dir . '/temp_' . $current_user->data->ID . '/' . $csv_filename;
            
            $json['file'] = $csv_file;
            $json['error'] = __( 'The uploaded file is not a valid csv file. Please try again.', 'geodirectory' );
            $file = array();

            if ( $csv_file && $wp_filesystem->is_file( $target_path ) && $wp_filesystem->exists( $target_path ) ) {
                $wp_filetype = wp_check_filetype_and_ext( $target_path, $csv_filename );
                
                if (!empty($wp_filetype) && isset($wp_filetype['ext']) && geodir_strtolower($wp_filetype['ext']) == 'csv') {
                    $json['error'] = NULL;
                    $json['rows'] = 0;
                    
                    $lc_all = setlocale(LC_ALL, 0); // Fix issue of fgetcsv ignores special characters when they are at the beginning of line
                    setlocale(LC_ALL, 'en_US.UTF-8');
                    if ( ( $handle = fopen($target_path, "r" ) ) !== FALSE ) {
                        while ( ( $data = fgetcsv( $handle, 100000, "," ) ) !== FALSE ) {
                            if ( !empty( $data ) ) {
                                $file[] = $data;
                            }
                        }
                        fclose($handle);
                    }
                    setlocale(LC_ALL, $lc_all);

                    $json['rows'] = (!empty($file) && count($file) > 1) ? count($file) - 1 : 0;
                    
                    if (!$json['rows'] > 0) {
                        $json['error'] = __('No data found in csv file.', 'geodirectory');
                    }
                } else {
                    wp_send_json( $json );
                }
            } else {
                wp_send_json( $json );
            }
            
            if ( $task == 'prepare_import' || !empty( $json['error'] ) ) {
                wp_send_json( $json );
            }
            
            $total = $json['rows'];
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 1;
            $processed = isset($_POST['processed']) ? (int)$_POST['processed'] : 0;
            
            $count = $limit;
            
            if ($count < $total) {
                $count = $processed + $count;
                if ($count > $total) {
                    $count = $total;
                }
            } else {
                $count = $total;
            }
            
            $updated = 0;
            $skipped = 0;
            $invalid = 0;
            
            if ( $task == 'import_catloc' ) {
                $import_choice = 'update';
                
                if (!empty($file)) {
                    $columns = isset($file[0]) ? $file[0] : NULL;
                    
                    if (empty($columns) || (!empty($columns) && $columns[0] == '')) {
                        $json['error'] = __('File you are uploading is not valid. Columns does not matching.', 'geodirectory');
                        wp_send_json( $json );
                    }
                    
                    $gd_error_log = __('GD IMPORT CATEGORY + LOCATION DESCRIPTIONS [ROW %d]:', 'geodirlocation');
                    $gd_error_hood = __( 'Could not be saved due to invalid data', 'geodirectory' );
                    
                    $gd_location_ids = array();
                    
                    for ($i = 1; $i <= $limit; $i++) {
                        $index = $processed + $i;
                        
                        if (isset($file[$index])) {
                            $row = $file[$index];
                            $row = array_map( 'trim', $row );
                            $data = array();
                            
                            foreach ($columns as $c => $column) {
                                $data[$column] = sanitize_text_field($row[$c]);
                            }
                            
                            if (empty($data['term_id'])) {
                                $invalid++;
                                geodir_error_log( wp_sprintf( $gd_error_log, ($index + 1) ) . ' ' . __( 'term_id is empty!', 'geodirlocation' ) );
                                continue;
                            }
                            
                            if (empty($data['post_type'])) {
                                $invalid++;
                                geodir_error_log( wp_sprintf( $gd_error_log, ($index + 1) ) . ' ' . __( 'post_type is empty!', 'geodirlocation' ) );
                                continue;
                            }
                            
                            if (!isset($data['top_description'])) {
                                $invalid++;
                                geodir_error_log( wp_sprintf( $gd_error_log, ($index + 1) ) . ' ' . __( 'top_description column not found!', 'geodirlocation' ) );
                                continue;
                            }
                            
                            if (!empty($data['country_slug']) || !empty($data['region_slug']) || !empty($data['city_slug'])) {
                                if (empty($data['country_slug'])) {
                                    $invalid++;
                                    geodir_error_log( wp_sprintf( $gd_error_log, ($index + 1) ) . ' ' . __( 'country_slug is empty!', 'geodirlocation' ) );
                                    continue;
                                }
                                
                                if (!empty($data['city_slug'])) {
                                    if (empty($data['region_slug'])) {
                                        $invalid++;
                                        geodir_error_log( wp_sprintf( $gd_error_log, ($index + 1) ) . ' ' . __( 'region_slug is empty!', 'geodirlocation' ) );
                                    } else {
                                        $location_id = 0;
                                        
                                        if (!empty($gd_location_ids[$data['country_slug']][$data['region_slug']][$data['city_slug']])) {
                                            $location_id = $gd_location_ids[$data['country_slug']][$data['region_slug']][$data['city_slug']];
                                        } else {
                                            $location = geodir_city_info_by_slug( $data['city_slug'], $data['country_slug'], $data['region_slug'] );
                                            
                                            if ( !empty( $location->location_id ) ) {
                                                $location_id = $location->location_id;
                                                $gd_location_ids[$data['country_slug']][$data['region_slug']][$data['city_slug']] = $location_id;
                                            }
                                        }
                                        
                                        if (!empty($location_id)) {
                                            geodir_location_save_term_top_desc( $data['post_type'], $data['term_id'], $data['top_description'], $location_id, 'city' );
                                            $updated++;
                                        } else {
                                            $invalid++;
                                            geodir_error_log( wp_sprintf( $gd_error_log, ($index + 1) ) . ' ' . __( 'city not found!', 'geodirlocation' ) );
                                        }
                                    }
                                } else {
                                    if (!empty($data['region_slug'])) {
                                        geodir_location_save_term_top_desc( $data['post_type'], $data['term_id'], $data['top_description'], $data['region_slug'], 'region', $data['country_slug']);
                                        $updated++;
                                    } else {
                                        geodir_location_save_term_top_desc( $data['post_type'], $data['term_id'], $data['top_description'], $data['country_slug'], 'country' );
                                        $updated++;
                                    }
                                }
                            } else { // default top location
                                $save_option = array();
                                $save_option['gd_cat_loc_cat_id'] = $data['term_id'];
                                $save_option['gd_cat_loc_post_type'] = $data['post_type'];
                                $save_option['gd_cat_loc_taxonomy'] = $data['post_type'] . 'category';
                                $save_option['gd_cat_loc_default'] = !empty( $data['enable_default_for_all_locations'] ) && (int)$data['enable_default_for_all_locations'] == 1 ? 1 : 0;
                                update_option( 'geodir_cat_loc_' . $data['post_type'] . '_' . $data['term_id'], $save_option );
            
                                geodir_update_tax_meta( $data['term_id'], 'ct_cat_top_desc', $data['top_description'], $data['post_type'] );
                                $updated++;
                            }
                            
                            continue;
                        }
                    }
                }
                
                $json = array();
                $json['processed'] = $limit;
                $json['updated'] = $updated;
                $json['skipped'] = $skipped;
                $json['invalid'] = $invalid;
                
                wp_send_json( $json );
            }
        }
        break;
        case 'import_finish':{
            /**
             * Run an action when an import finishes.
             *
             * This action can be used to fire functions after an import ends.
             *
             * @since 1.5.4
             */
            do_action('geodir_import_finished');
        }
        break;
    }
    echo '0';
    gd_die();
}
// Handle ajax request for import/export.
add_action( 'wp_ajax_geodir_location_imex', 'geodir_location_ajax_import_export' );
add_action( 'wp_ajax_nopriv_geodir_location_imex', 'geodir_location_ajax_import_export' );

/**
 * Grab neighbourhood and add it to neighbourhood field during add listing location search.
 *
 * @since 1.5.4
 *
 */
function geodir_location_grab_neighbourhood() {
?> 
window.neighbourhood = '';
window.gdGeo = true;
if (window.gdMaps == 'google' && typeof responses != 'undefined' && responses && responses.length > 0) {    
    for (var j=0; j < responses[0].address_components.length; j++) {
        var component = responses[0].address_components[j];
        if (component.types[0] == 'neighbourhood' || component.types[0] == 'neighborhood') {
            window.neighbourhood = component.long_name;
        }
    }
} 
<?php
}

/**
 * Filter the address fields array being displayed.
 *
 * @since 1.5.5
 *
 * @param array $address_fields The array of address fields.
 * @param object $post The current post object.
 * @param array $cf The custom field array details.
 * @param string $location The location to output the html.
 * @return array Filtered address fields.
 */
function geodir_custom_field_output_show_address_neighbourhood($address_fields, $post, $cf, $location) {
    if (!empty($address_fields) && !empty($cf['extra_fields']) && !empty($post->post_neighbourhood) && get_option('location_neighbourhoods')) {
        $extra_fields = stripslashes_deep(maybe_unserialize($cf['extra_fields']));
        $show_neighbourhood = !empty($extra_fields['show_neighbourhood']) ? true : false;
        
        /**
         * Filter "show neighbourhood in address" value.
         *
         * @param bool $show_neighbourhood True if neighbourhood should be displayed else False.
         * @param string $location The location to output the html.
         * @param object $post The current post object.
         * @param array $cf The custom field array details.
         *
         * @since 1.5.5
         */
        $show_neighbourhood = apply_filters('geodir_show_neighbourhood_in_address', $show_neighbourhood, $location, $post, $cf);
        
        $post_neighbourhood = get_actual_location_name('neighbourhood', $post->post_neighbourhood, true);
        if (empty($post_neighbourhood)) {
            $post_neighbourhood = preg_replace('/-(\d+)$/', '', $post->post_neighbourhood);
            $post_neighbourhood = preg_replace('/[_-]/', ' ', $post_neighbourhood);
            $post_neighbourhood = __(geodir_ucwords($post_neighbourhood), 'geodirectory');
        }
        
        if ($show_neighbourhood && $post_neighbourhood) {
            $neighbourhood_field = array('post_neighbourhood' => '<span itemprop="addressNeighbourhood">' . $post_neighbourhood . '</span>');
            
            $offset_field = '';
            if (isset($address_fields['post_city'])) {
                $offset_field = 'post_city';
            } else if (isset($address_fields['post_address'])) {
                $offset_field = 'post_address';
            }
            
            if ($offset_field) {
                $offset = array_search($offset_field, array_keys($address_fields)) + 1;
                $address_fields = array_merge(array_slice($address_fields, 0, $offset), $neighbourhood_field, array_slice($address_fields, $offset));
            } else {
                $address_fields = array_merge($neighbourhood_field, $address_fields);
            }
        }
    }
    return $address_fields;
}
add_filter('geodir_custom_field_output_address_fields', 'geodir_custom_field_output_show_address_neighbourhood', 10, 4);


/**
 * Add location type in the near field value.
 *
 * @since 1.0.0
 * @since 1.4.7 Changed language domain to "geodirectory" for "In:" text
 *              because if translation don't match in both plugins then
 *              it will breaks autocomplete search.
 *
 * @global object $wpdb        WordPress Database object.
 * @global object $gd_session  GeoDirectory Session object.
 *
 * @param string $near The near field value.
 * @return string Filtered near value.
 */
function geodir_set_search_near_text($near, $default_near_text='') {
    global $wpdb, $gd_session;

    if (!defined('POST_LOCATION_TABLE')) {
        return $near;
    }

    // If near me then set to default as its set vai JS on page
    if($near== __("Near:", 'geodiradvancesearch').' '.__("Me", 'geodiradvancesearch')){
        return $default_near_text;
    }


    $gd_ses_country = $gd_session->get('gd_country');
    $gd_ses_region = $gd_session->get('gd_region');
    $gd_ses_city = $gd_session->get('gd_city');

    if ($gd_ses_country || $gd_ses_region || $gd_ses_city) {
        if (($gd_ses_neighbourhood = $gd_session->get('gd_neighbourhood')) && get_option('location_neighbourhoods')) {
            $neighbourhood = geodir_location_get_neighbourhood_by_id($gd_ses_neighbourhood, true);

            if (!empty($neighbourhood)) {
                $near = __('In:', 'geodirectory') . ' ' . $neighbourhood->neighbourhood . ' ' . __('(Neighbourhood)', 'geodiradvancesearch');
                return $near;
            }
        }

        if ($gd_ses_city) {
            $type = 'city';
            $location_slug = 'city_slug';
            $value = $gd_ses_city;
        } else if ($gd_ses_region) {
            $type = 'region';
            $location_slug = 'region_slug';
            $value = $gd_ses_region;
        } else if ($gd_ses_country) {
            $type = 'country';
            $location_slug = 'country_slug';
            $value = $gd_ses_country;
        } else {
            return $near;
        }

        $location_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . POST_LOCATION_TABLE . " WHERE " . $location_slug . "=%s", $value));

        if (!empty($location_data)) {
            if ($type == 'city') {
                $near = __('In:', 'geodirectory') . ' ' . $location_data->{$type} . ' ' . __('(City)', 'geodiradvancesearch');
            } else if ($type == 'region') {
                $near = __('In:', 'geodirectory') . ' ' . $location_data->{$type} . ' ' . __('(Region)', 'geodiradvancesearch');
            } else if ($type == 'country') {
                $near = __('In:', 'geodirectory') . ' ' . __($location_data->{$type}, 'geodirectory') . ' ' . __('(Country)', 'geodiradvancesearch');
            }
        }
    }
    return $near;
}
add_action('geodir_search_near_text', 'geodir_set_search_near_text', 10, 2);



function geodir_as_add_search_location() {
    global $wpdb, $gd_session;

    if (!defined('POST_LOCATION_TABLE')) {
        return;
    }

    $gd_ses_country = $gd_session->get('gd_country');
    $gd_ses_region = $gd_session->get('gd_region');
    $gd_ses_city = $gd_session->get('gd_city');

    if ($gd_ses_country || $gd_ses_region || $gd_ses_city) {
        if (($gd_ses_neighbourhood = $gd_session->get('gd_neighbourhood')) && get_option('location_neighbourhoods')) {
            $neighbourhood = geodir_location_get_neighbourhood_by_id($gd_ses_neighbourhood, true);

            if (!empty($neighbourhood)) {
                echo '<input name="set_location_type" type="hidden" value="4">';
                echo '<input name="set_location_val" type="hidden" value="' . $neighbourhood->location_id . '">';
                echo '<input name="gd_hood_s" type="hidden" value="' . $neighbourhood->hood_id . '">';
                return;
            }
        }

        if ($gd_ses_city) {
            $type = '3';
            $location_slug = 'city_slug';
            $value = $gd_ses_city;
        } else if ($gd_ses_region) {
            $type = '2';
            $location_slug = 'region_slug';
            $value = $gd_ses_region;
        } else if ($gd_ses_country) {
            $type = '1';
            $location_slug = 'country_slug';
            $value = $gd_ses_country;
        } else {
            return;
        }

        $location_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . POST_LOCATION_TABLE . " WHERE " . $location_slug . "=%s", $value));

        if (!empty($location_data) && isset($location_data->location_id) && $type) {
            echo '<input name="set_location_type" type="hidden" value="' . $type . '">';
            echo '<input name="set_location_val" type="hidden" value="' . $location_data->location_id . '">';
        }
    }
}
add_action('geodir_before_search_button', 'geodir_as_add_search_location', 10);

function geodir_set_search_near_class( $class ) {
    global $gd_session;

    if (!$gd_session->get('user_lat')) {
        if ($gd_session->get('gd_country')) {
            $class = $class . ' near-country';
        }

        if ($gd_session->get('gd_neighbourhood')) {
            $class = $class . ' near-neighbourhood';
        } else if ($gd_session->get('gd_city')) {
            $class = $class . ' near-city';
        } else if ($gd_session->get('gd_region')) {
            $class = $class . ' near-region';
        }
    }

    return $class;
}
add_action('geodir_search_near_class', 'geodir_set_search_near_class', 10, 1);



function geodir_set_session_from_url()
{
    if (isset($_REQUEST['set_location_type']) && isset($_REQUEST['set_location_val']) && isset($_REQUEST['snear']) && $_REQUEST['snear'] == '') {
        global $gd_session;
        //clear user location
        $gd_session->set('user_lat', '');
        $gd_session->set('user_lon', '');
        $gd_session->set('my_location', 0);

        add_filter('parse_request', 'geodir_set_location_var_in_session_autocompleter', 99);
    }
}

add_action('init', 'geodir_set_session_from_url', 0);

function geodir_set_location_var_in_session_autocompleter($wp)
{
    if (!function_exists('geodir_get_location_by_id')) {
        return $wp;
    }

    $set_location_id = isset($_REQUEST['set_location_val']) ? (int)$_REQUEST['set_location_val'] : NULL;
    $set_location_type = isset($_REQUEST['set_location_type']) ? (int)$_REQUEST['set_location_type'] : NULL;

    if (!$set_location_id > 0 || !$set_location_type > 0) {
        return $wp;
    }

    $nLoc = geodir_get_location_by_id('', (int)$set_location_id);
    if (empty($nLoc)) {
        return $wp;
    }

    switch ($set_location_type) {
        case 1:
            $wp->query_vars['gd_country'] = $nLoc->country_slug;
            $wp->query_vars['gd_region'] = '';
            $wp->query_vars['gd_city'] = '';
            break;
        case 2:
            $wp->query_vars['gd_country'] = $nLoc->country_slug;
            $wp->query_vars['gd_region'] = $nLoc->region_slug;
            $wp->query_vars['gd_city'] = '';
            break;
        case 3:
        case 4:
            $wp->query_vars['gd_country'] = $nLoc->country_slug;
            $wp->query_vars['gd_region'] = $nLoc->region_slug;
            $wp->query_vars['gd_city'] = $nLoc->city_slug;

            if ($set_location_type == 4 && !empty($_REQUEST['gd_hood_s']) && get_option('location_neighbourhoods') && $neighbourhood = geodir_location_get_neighbourhood_by_id((int)$_REQUEST['gd_hood_s'])) {
                $wp->query_vars['gd_neighbourhood'] = $neighbourhood->neighbourhood_slug;
            }
            break;
    }
    return $wp;
}