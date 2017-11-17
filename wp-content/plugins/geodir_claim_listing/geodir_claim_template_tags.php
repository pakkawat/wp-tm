<?php



function geodir_admin_claim_listing_tabs($tabs){
	
	$total_unaction_claims = '';
	if(geodir_unactioned_claims())
	{
	
		$total_unaction_claims = '<span id="unaproved_reviews">'.geodir_unactioned_claims().'</span>';
	}
	
		$tabs['claimlisting_fields'] = array( 'label' =>__( 'Listing Claims <span id="">'.$total_unaction_claims.'</span>', 'geodirclaim' ),
														'subtabs' => array(
																								array('subtab' => 'geodir_claim_options',
																									'label' =>__( 'Options', 'geodirclaim'),
																									'form_action' => admin_url('admin-ajax.php?action=geodir_claim_ajax_action')),
																								array('subtab' => 'manage_geodir_claim_listing',
																									'label' =>__( 'Listing Claims', 'geodirclaim'),
																									'form_action' => admin_url('admin-ajax.php?action=geodir_claim_ajax_action')),
																								array('subtab' => 'geodir_claim_notification',
																									'label' =>__( 'Notifications', 'geodirclaim'),
																									'form_action' => admin_url('admin-ajax.php?action=geodir_claim_ajax_action'))
																								)
													);
	
	return $tabs; 
}


function geodir_admincss_claim_manager(){
	global $pagenow;
	
	if($pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'claimlisting_fields'){
	
		wp_register_style('cliam-plugin-style', plugins_url('',__FILE__).'/css/geodir-cliam-manager.css');
		wp_enqueue_style('cliam-plugin-style');
	
	}
}

function geodir_claim_admin_scripts(){	
	global $pagenow;
	if($pagenow == 'admin.php' && $_REQUEST['page'] == 'geodirectory' && ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'claimlisting_fields'))
	
	wp_register_script( 'geodirectory-claim-admin', plugins_url('/js/claim-script.js',__FILE__));
	wp_enqueue_script( 'geodirectory-claim-admin');
	
}

function geodir_add_claim_listing_stylesheet(){

	wp_enqueue_style( 'geodir-claim-popup-style',plugins_url('/css/geodir-claim-popup-frm.css',__FILE__)  );	
	
}


function geodir_add_claim_listing_scripts(){
	
	global $path_url;
	wp_enqueue_script( 'jquery' );
	wp_register_script( 'geodirectory-claim-admin', plugins_url('/js/claim-script.js',__FILE__));
	wp_enqueue_script( 'geodirectory-claim-admin');
	
}

function geodir_add_claim_option_metabox(){
	
	global $post;
	
	$geodir_post_types = geodir_get_posttypes('array');
	$geodir_posttypes = array_keys($geodir_post_types);
	if( isset($post->post_type) && in_array($post->post_type,$geodir_posttypes) ):
	
		$geodir_posttype = $post->post_type;
		$post_typename = geodir_ucwords($geodir_post_types[$geodir_posttype]['labels']['singular_name']);
		
		add_meta_box( 'geodir_claim_listing_information', $post_typename.' Claim Settings', 'geodir_claim_listing_information', $geodir_posttype, 'side', 'high' );
	
	endif;
	
}

function geodir_claim_listing_information() {
	global $post, $post_id;
	
	wp_nonce_field(plugin_basename(__FILE__), 'geodir_post_claim_setting_noncename');
	
	$is_claimed = (int)geodir_get_post_meta($post_id, 'claimed', true); ?>
	<div class="geodir-claim-section">
		<h4 style="display:inline;"><?php echo CLAIM_IS_CLAIMED; ?></h4>
		<input type="radio" class="gd-checkbox" name="claimed" id="is_claimed_yes" <?php checked($is_claimed, 1); ?> value="1" /> <?php echo CLAIM_YES_TEXT; ?>
		<input type="radio" class="gd-checkbox" name="claimed" id="is_claimed_no" <?php checked($is_claimed, 0); ?> value="0" /> <?php echo CLAIM_NO_TEXT; ?>
	</div><?php
}