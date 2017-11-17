<?php 
global $wpdb;

if(get_option('geodirevents'.'_db_version') != GDEVENTS_VERSION){

	if (GDEVENTS_VERSION <= '1.4.0') {
		add_action('init', 'geodirevents_upgrade_1_4_0', 11);
	}
	
	add_action( 'plugins_loaded', 'geodirevents_upgrade_all' );
	update_option( 'geodirevents'.'_db_version',  GDEVENTS_VERSION );
}

function geodirevents_upgrade_all(){
	geodir_event_tables_install();
	geodirevents_upgrade_1_1_0();
}

function geodirevents_upgrade_1_1_0(){
	global $wpdb,$plugin_prefix;
	
$wpdb->query("ALTER TABLE ".$wpdb->prefix."geodir_gd_event_detail MODIFY `post_title` text NULL");	
	
}

function geodirevents_upgrade_1_4_0(){
	global $wpdb,$plugin_prefix;
	
}


