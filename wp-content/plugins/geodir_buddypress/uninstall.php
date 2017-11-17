<?php
/**
 * Uninstall GeoDirectory BuddyPress Integration
 *
 * Uninstalling GeoDirectory BuddyPress Integration deletes the plugin options.
 *
 * @package GeoDirectory_BuddyPress_Integration
 * @since 1.1.4
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( get_option( 'geodir_un_geodir_buddypress' ) ) {
	$wpdb->hide_errors();
	
	// Delete options.
	$options = $wpdb->get_results( "SELECT `option_name` FROM `" . $wpdb->options . "` WHERE `option_name` LIKE 'geodir_buddypress_%' OR `option_name` LIKE 'gdbuddypress_%'" );
	
	if ( !empty( $options ) ) {
		foreach ( $options as $option ) {
			delete_option( $option->option_name );
		}
	}
	
	delete_option( 'geodir_un_geodir_buddypress' );
}