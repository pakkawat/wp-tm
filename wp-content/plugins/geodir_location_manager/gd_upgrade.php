<?php
/**
 * Contains functions related to Location Manager plugin upgrade.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
global $wpdb;

if(get_option('geodirlocation'.'_db_version') != GEODIRLOCATION_VERSION){
	//ini_set("display_errors", "1");error_reporting(E_ALL); // for error checking
	add_action( 'plugins_loaded', 'geolocation_upgrade_all' );
	update_option( 'geodirlocation'.'_db_version',  GEODIRLOCATION_VERSION );
}

/**
 * Handles upgrade for all location manager versions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */
function geolocation_upgrade_all(){
	geodir_location_activation_script();
	geolocation_upgrade_1_3_2();
	geodir_location_fix_db_seo_names();
}

/**
 * Handles upgrade for location manager versions <= 1.3.2.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
function geolocation_upgrade_1_3_2(){
    global $wpdb;

    /*
     * We clear the term meta so it's rebuilt as there was a bug.
     */
    $wpdb->query('TRUNCATE TABLE '.GEODIR_TERM_META);
}

/**
 * Fixes the neighbourhood field limit of 30 to 100 in details table.
 *
 * @since 1.5.0
 * @global object $wpdb WordPress Database object.
 */
function geodir_location_fix_neighbourhood_field_limit_150() {
    global $wpdb;

    $all_postypes = geodir_get_posttypes();

    if (!empty($all_postypes)) {
        foreach ($all_postypes as $key) {
            // update each GD CTP
            try {
                $wpdb->query("ALTER TABLE " . $wpdb->prefix . "geodir_" . $key . "_detail MODIFY post_neighbourhood VARCHAR( 100 ) NULL");
            } catch(Exception $e) {
                error_log( 'Error: ' . $e->getMessage() );
            }
        }
    }
}


/**
 * Fixes the db column names for seo info.
 *
 * @since 1.5.2
 * @global object $wpdb WordPress Database object.
 */
function geodir_location_fix_db_seo_names(){
	global $wpdb;

	if ($wpdb->query("UPDATE " . LOCATION_SEO_TABLE . " seo SET seo.seo_meta_desc = seo.seo_title WHERE seo.seo_meta_desc ='' ")) {
		return true;
	} else {
		return false;
	}
}
