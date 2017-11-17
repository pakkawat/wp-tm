<?php
/**
 * This is the main Location Manager plugin file, here we declare and call the important stuff
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global array $geodir_addon_list List of active GeoDirectory extensions.
 */

/*
Plugin Name: GeoDirectory Location Manager
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Location Manager plugin.
Version: 1.5.62
Author: GeoDirectory
Author URI: https://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65853
*/

define("GEODIRLOCATION_VERSION", "1.5.62");
if (!defined('GEODIRLOCATION_TEXTDOMAIN')) define('GEODIRLOCATION_TEXTDOMAIN', 'geodirlocation');

// check advanced search version is new enough
if (defined('GEODIRADVANCESEARCH_VERSION') && version_compare(GEODIRADVANCESEARCH_VERSION,'1.4.91','<')){
	return;
}

global $wpdb, $plugin_prefix, $is_custom_loop,$geodir_addon_list;

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
	if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
		require_once('gd_update.php'); // require update script
	}
}

///GEODIRECTORY CORE ALIVE CHECK START
if (is_admin()) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (!is_plugin_active('geodirectory/geodirectory.php')) {
        return;
    }
}/// GEODIRECTORY CORE ALIVE CHECK END



$geodir_addon_list['geodir_location_manager'] = 'yes' ;

if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';

$path_location_url = plugins_url('',__FILE__);


if (!defined('GEODIR_LOCATION_MANAGER_PATH')) define('GEODIR_LOCATION_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
if (!defined('POST_LOCATION_TABLE')) define('POST_LOCATION_TABLE', $plugin_prefix . 'post_locations' );
if (!defined('POST_NEIGHBOURHOOD_TABLE')) define('POST_NEIGHBOURHOOD_TABLE', $plugin_prefix . 'post_neighbourhood' );
if (!defined('COUNTRIES_TABLE')) define('COUNTRIES_TABLE', $plugin_prefix . 'countries' );
if (!defined('LOCATION_SEO_TABLE')) define('LOCATION_SEO_TABLE', $plugin_prefix . 'location_seo' );
if (!defined('GEODIR_TERM_META')) define('GEODIR_TERM_META', $plugin_prefix . 'term_meta' );


/*
 * Don't cache the location/me/ page
 */
$gd_server_url_parts = explode('/',$_SERVER['REQUEST_URI']);
if (is_array($gd_server_url_parts )) {
    $gd_server_url_parts = array_filter($gd_server_url_parts );
    if(end($gd_server_url_parts)=='me'){
        /**
         * This tries to disable cache on location/me page as it can be very dynamic.
         */
        define('DONOTCACHEPAGE', TRUE);
        $_SERVER['DONOTCACHEPAGE']= TRUE;
    }

}

/**
 * Localisation
 **/
add_action('plugins_loaded','geodir_load_translation_geodirlocation');
function geodir_load_translation_geodirlocation()
{
    $locale = apply_filters('plugin_locale', get_locale(), 'geodirlocation');
    load_textdomain('geodirlocation', WP_LANG_DIR . '/' . 'geodirlocation' . '/' . 'geodirlocation' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodirlocation', false, dirname(plugin_basename(__FILE__)) . '/geodir-location-languages');
    require_once( 'language.php' ); /* Define language constants */
}




/**
 * activation hooks
 **/
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) :
	register_activation_hook( __FILE__, 'geodir_location_activation' );
	register_deactivation_hook( __FILE__, 'geodir_location_deactivation' );
	
endif;

include_once('geodir_location_hooks_actions.php');
include_once('geodir_location_functions.php');
include_once('geodir_count_functions.php');
include_once('geodir_location_template_tags.php');
include_once('geodir_location_widgets.php');
include_once('geodir_location_shortcodes.php');
if ( is_admin() ){
require_once('gd_upgrade.php');	
}


add_action('activated_plugin','geodir_location_plugin_activated') ;
/**
 * This function runs after a plugin has been activated.
 *
 * @param string $plugin Plugin path to main plugin file with plugin data.
 */
function geodir_location_plugin_activated($plugin)
{
	if (!get_option('geodir_installed')) 
	{
		$file = plugin_basename(__FILE__);
		if($file == $plugin) 
		{
			$all_active_plugins = get_option( 'active_plugins', array() );
			if(!empty($all_active_plugins) && is_array($all_active_plugins))
			{
				foreach($all_active_plugins as $key => $plugin)
				{
					if($plugin ==$file)
						unset($all_active_plugins[$key]) ;
				}
			}
			update_option('active_plugins',$all_active_plugins);
			
		}
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodirlocation'));
	}
	
}
?>