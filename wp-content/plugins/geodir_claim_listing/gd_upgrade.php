<?php 
global $wpdb;

if(get_option('geodirclaim'.'_db_version') != GEODIRCLAIM_VERSION){
	//ini_set("display_errors", "1");error_reporting(E_ALL); // for error checking
	
	add_action( 'plugins_loaded', 'geodirclaim_upgrade_all' );
	update_option( 'geodirclaim'.'_db_version',  GEODIRCLAIM_VERSION );
}

function geodirclaim_upgrade_all(){
	geodir_claim_activation_script();
	geodirclaim_upgrade_1_0_7();
}

function geodirclaim_upgrade_1_0_7(){
	global $wpdb,$plugin_prefix;
	
}


