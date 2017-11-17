<?php
/**
 * Upgrade related functions.
 */

global $wpdb;

if (get_option('gdw_db_version') != GDF_VERSION) {

	if (GDF_VERSION <= '1.0.0') {
		add_action('init', 'whoop_upgrade_one_zero_zero', 11);
	}

	$default_star = get_option('geodir_default_rating_star_icon');
	if ($default_star == get_theme_root_uri().'/whoop/library/images/whoop-star.png') {
		update_option('geodir_default_rating_star_icon', get_template_directory_uri() . '/library/images/whoop-star.png');
	}

	$default_star = get_option('geodir_reviewrating_overall_off_img');
	if ($default_star == get_theme_root_uri().'/whoop/library/images/whoop-star.png') {
		update_option('geodir_reviewrating_overall_off_img', get_template_directory_uri() . '/library/images/whoop-star.png');
	}

	update_option('gdw_db_version', GDF_VERSION);
}

function whoop_upgrade_one_zero_zero(){

	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"update " . GEODIR_CUSTOM_FIELDS_TABLE . " set show_on_listing=%s where htmlvar_name = %s OR htmlvar_name = %s OR htmlvar_name = %s", array('1', 'post', 'geodir_contact', 'geodir_website')
		)
	);

}