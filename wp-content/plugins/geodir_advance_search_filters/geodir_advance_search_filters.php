<?php
/*
Plugin Name: GeoDirectory Advance Search Filters
Plugin URI: http://wpgeodirectory.com/
Description: GeoDirectory Advance Search Filters.
Version: 1.4.92
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
Update URL: https://wpgeodirectory.com
Update ID: 65056
*/

define("GEODIRADVANCESEARCH_VERSION", "1.4.92");
if (!defined('GEODIRADVANCESEARCH_TEXTDOMAIN')) define('GEODIRADVANCESEARCH_TEXTDOMAIN', 'geodiradvancesearch');
global $wpdb, $plugin_prefix;

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
	if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
		require_once('gd_update.php'); // require update script
	}
}

///GEODIRECTORY CORE ALIVE CHECK START
if (is_admin()) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    if (is_plugin_active('geodir_autocompleter/geodir_autocompleter.php')) {
        deactivate_plugins('geodir_autocompleter/geodir_autocompleter.php');
    }

    if (is_plugin_active('geodir_share_location/geodir_share_location.php')) {
        deactivate_plugins('geodir_share_location/geodir_share_location.php'); 
    }

    if (!is_plugin_active('geodirectory/geodirectory.php')) {
        return;
    }
    
    if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
        if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            add_action( 'admin_notices', 'geodir_search_PHP_version_notice' );
        }
    }
}
/// GEODIRECTORY CORE ALIVE CHECK END

if(!isset($plugin_prefix))
	$plugin_prefix = $wpdb->prefix.'geodir_';

$path_location_url = plugins_url('',__FILE__);

if (!defined('GEODIR_ADVANCE_SEARCH_TABLE')) define('GEODIR_ADVANCE_SEARCH_TABLE', $plugin_prefix . 'custom_advance_search_fields' );


add_action('plugins_loaded','geodir_load_translation_geodiradvancesearch');
function geodir_load_translation_geodiradvancesearch()
{
    $locale = apply_filters('plugin_locale', get_locale(), 'geodiradvancesearch');
    load_textdomain('geodiradvancesearch', WP_LANG_DIR . '/' . 'geodiradvancesearch' . '/' . 'geodiradvancesearch' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodiradvancesearch', false, dirname(plugin_basename(__FILE__)) . '/geodir-advance-search-languages');
    require_once('language.php'); // Define language constants
}

define('GEODIRADVANCESEARCH_PLUGIN_URL', plugins_url('',__FILE__));
if ( !defined( 'GEODIRADVANCESEARCH_PLUGIN_PATH' ) ) {
	define( 'GEODIRADVANCESEARCH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
 
/**
 * Admin init + activation hooks
 **/


include_once('geodirectory_advance_search_filters_output.php');
include_once('geodirectory_advance_search_function.php');
include_once('geodirectory_advance_search_hooks_actions.php');

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    register_activation_hook(__FILE__ , 'geodir_advance_search_filters_activation');
    register_deactivation_hook(__FILE__ , 'geodir_advance_search_filters_deactivation');
}
if ( is_admin() ){
    require_once('gd_upgrade.php');
}



add_action('activated_plugin','geodir_advance_search_filters_plugin_activated') ;
function geodir_advance_search_filters_plugin_activated($plugin)
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
		
		wp_die(__('<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodiradvancesearch'));
	}
}