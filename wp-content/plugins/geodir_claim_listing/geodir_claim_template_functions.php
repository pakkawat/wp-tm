<?php

function geodir_admin_claim_frm(){
	
	global $wpdb, $plugin_prefix;
    $claiminfo = '';
	if($_REQUEST['id']!='')
	{
		$pid = $_REQUEST['id'];
		$claimsql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where pid=%d",array($pid));
		$claiminfo = $wpdb->get_results($claimsql);
	}
	?>
	
	<div class="gd-content-heading">
	<h3><?php echo CLAIM_LISTING_DETAIL; ?></h3>
		
		<?php
			$nonce = wp_create_nonce( 'claim_addcomment_nonce' );
		?>
		<input type="hidden" name="claim_addcomment_nonce" value="<?php echo $nonce;?>" />							
		<input type="hidden" name="claimact" value="addclaim">
		<input type="hidden" name="id" value="<?php echo esc_html($_REQUEST['id']);?>">
		
	<table class="form-table">
		<tbody>
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_LISTING_TITLE;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						 <a href="<?php echo trailingslashit(get_site_url()).'?p='.$claiminfo[0]->list_id; ?>" target="_blank"><?php echo stripslashes($claiminfo[0]->list_title);?></a>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_USERNAME;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo $claiminfo[0]->user_name;?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_FULLNAME;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo stripslashes($claiminfo[0]->user_fullname);?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_USEREMAIL;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo stripslashes($claiminfo[0]->user_email);?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_NUMBER;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo stripslashes($claiminfo[0]->user_number);?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_POSITION;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo stripslashes($claiminfo[0]->user_position);?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_USERCOMMENTS;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<textarea name="user_com" cols="40" rows="5" id="user_com" disabled="disabled"><?php echo stripslashes($claiminfo[0]->user_comments);?></textarea>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_STATUS;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php if($claiminfo[0]->status==1) echo CLAIM_APPROVED_TEXT; elseif($claiminfo[0]->status==2) echo CLAIM_REJECTED_TEXT; else echo CLAIM_NO_DECISION;?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_DATE;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo $claiminfo[0]->claim_date;?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_USERIP;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo $claiminfo[0]->user_ip;?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_ORIGINAL_AUTHOR;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<?php echo $claiminfo[0]->org_author;?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
			<tr valign="top" class="single_select_page">
					<th class="titledesc" scope="row"><?php echo CLAIM_AUTHOR_COMMENTS;?></th>
					<td class="forminp">
					 <div class="gtd-formfeild">
						<textarea name="admin_com" cols="40" rows="5" id="admin_com"><?php echo stripslashes($claiminfo[0]->admin_comments);?></textarea> <br /><?php echo CLAIM_COMMENTS_APPROVE_REJECT;?>
					</div>
					<span class="description"></span>        
					</td>
			</tr>
			
		</tbody>
	</table>
	
	<p class="submit" style="margin-top:10px;">
		<input type="submit" class="button-primary" name="submit" value="<?php echo CLAIM_ADDCOMMENT;?>" onclick="return check_frm();">
		 <input type="button" class="button-primary" name="cancel" value="<?php echo CLAIM_CANCEL;?>" onClick="window.location.href='<?php echo trailingslashit(get_site_url());?>wp-admin/admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing'">
	</p>
										
	</form>
	</div><?php
}


function geodir_claim_popup_form($post_id){
	global $post;

	if (defined('ICL_LANGUAGE_CODE') && geodir_wpml_is_post_type_translated(get_post_type($post_id))){
		$lang = '&lang='.ICL_LANGUAGE_CODE;
	}else{
		$lang = '';
	}
	
	?>
	<div id="gd-basic-modal-content4" class="clearfix">
	
		<?php do_action( 'geodir_before_claim_form' ); ?>
		<form name="geodir_claim_form" id="geodir_claim_form" action="<?php echo admin_url();?>admin-ajax.php?action=geodir_claim_ajax_action<?php echo $lang;?>" method="post" >
		<?php
			$nonce = wp_create_nonce( 'add_claim_nonce'.$post_id );
		?>
			<input type="hidden" name="add_claim_nonce_field" value="<?php echo $nonce;?>" />	
			<input type="hidden" id="claim_form_pid" name="geodir_pid" value="<?php echo esc_html($post_id);?>" />
			<input type="hidden" name="geodir_sendact" value="add_claim" />
			
			<h3><?php echo CLAIM_LISTING_TEXT;?></h3>
			
			<h4><a id="gd-claimdisplayText" href="javascript:void(0);" onclick="geodir_claimtoggle();"><?php echo WHAT_IS_CLAIM_PROCESS; ?></a></h4>
			
			<div id="gd-claimtoggleText"  style="display: none"><p><?php echo CLAIM_LISTING_PROCESS; ?></p><hr /></div>
			
			<p id="reply_send_success2" class="sucess_msg" style="display:none;"></p>
			
			<?php do_action('geodir_before_claim_form_field', 'geodir_full_name') ;?>
			<div class="row clearfix" >
					<input required field_type="text" name="geodir_full_name" id="geodir_full_name" type="text" placeholder="<?php echo CLAIM_FULLNAME;?>"  />
			</div>
			<?php do_action('geodir_after_claim_form_field', 'geodir_full_name') ;?>
			
			<?php do_action('geodir_before_claim_form_field', 'geodir_user_comments') ;?>
			<div class="row  clearfix" >
					<input required field_type="text" name="geodir_user_number" id="geodir_user_number" type="text" placeholder="<?php echo CLAIM_CONTACT_NUMBER;?>" />
			</div>
			<?php do_action('geodir_after_claim_form_field', 'geodir_user_number') ;?>
			
			<?php do_action('geodir_before_claim_form_field', 'geodir_user_comments') ;?>
			<div class="row  clearfix" >
					<input required field_type="text" name="geodir_user_position" id="geodir_user_position" type="text" placeholder="<?php echo CLAIM_POS_IN_BUSINESS;?>" />
			</div>
			<?php do_action('geodir_after_claim_form_field', 'geodir_user_number') ;?>
			
			<?php do_action('geodir_before_claim_form_field', 'geodir_user_comments') ;?>
			<div class="row  clearfix" >
					<textarea required field_type="textarea" name="geodir_user_comments" id="geodir_user_comments" cols="" rows="" placeholder="<?php echo CLAIM_LISTING_SAMPLE_CONTENT;?>"></textarea>
			</div>
			
			<?php do_action( 'geodir_after_claim_form_field', 'geodir_user_comments' ) ;?>
			<input name="geodir_Send" type="submit" value="<?php echo CLAIM_SEND_TEXT; ?> " class="button " />	
		</form>
		<?php do_action('geodir_after_claim_form'); ?>
	</div>
	<?php

}


function geodir_manage_claim_listing(){
	
	global $wpdb, $plugin_prefix, $path_url;

	?>
	<div class="gd-content-heading">      
	<h3><?php echo CLAIM_GEODIR_CLAIMS_LISTING; ?></h3>   
                     
	<table style="width:100%" cellpadding="5" class="widefat post fixed" >
			<thead>
					<tr>
							<th width="150" align="left"><strong><?php echo CLAIM_LISTING_TITLE; ?></strong></th>
							<th width="330" align="left"><strong><?php echo CLAIM_USER_INFO; ?></strong></th>
							<th width="100" align="left"><strong><?php echo CLAIM_STATUS; ?></strong></th>
							<th width="100" align="left"><strong><?php echo CLAIM_DETAILS; ?></strong></th>
							<th width="70" align="left"><strong><?php echo CLAIM_ACTION; ?></strong></th>
							<th align="left">&nbsp;</th>
					</tr>
			<?php
			$claimsql = "select * from ".GEODIR_CLAIM_TABLE."  ORDER BY status ASC";
			$claiminfo = $wpdb->get_results($claimsql);
			if($claiminfo)
			{
			foreach($claiminfo as $claiminfoObj)
			{
				$nonce = wp_create_nonce( 'claim_action_'.$claiminfoObj->pid );
			?>
			<tr <?php if($claiminfoObj->status==1)echo 'style="background-color:#99FFCC"'; elseif ($claiminfoObj->status==2) echo 'style="background-color:#FFAEAE"';  ?>>
					<td><?php echo $claiminfoObj->list_title;?></td>
					
					<td>
						<strong><?php echo CLAIM_USER; ?>:&nbsp;</strong><?php echo stripslashes($claiminfoObj->user_name);?><br />
						<strong><?php echo CLAIM_FULLNAME; ?>:&nbsp;</strong><?php echo stripslashes($claiminfoObj->user_fullname);?><br />
						<strong><?php echo CLAIM_POSITION; ?>:&nbsp;</strong><?php echo stripslashes($claiminfoObj->user_position);?><br />
						<strong><?php echo CLAIM_PHONE; ?>:&nbsp;</strong><?php echo stripslashes($claiminfoObj->user_number);?>
					</td>
					
					<td><?php if($claiminfoObj->status==1) echo CLAIM_APPROVED_TEXT; elseif($claiminfoObj->status==2) echo CLAIM_REJECTED_TEXT; else echo CLAIM_NO_DECISION;?></td>
					<td><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing&pagetype=addedit&id='.$claiminfoObj->pid;?>"><?php echo CLAIM_FULL_DETAILS; ?></a> </td>
					<td>
					<?php if ($claiminfoObj->status=='' || $claiminfoObj->status=='0'){ ?>
					<a href="javascript:void(0);" onClick="return approve_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo $path_url; ?>/images/tick.png" alt="Approve" title="Approve"/></a>
					<a href="javascript:void(0);" onClick="return reject_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo $path_url; ?>/images/reject.png" alt="Reject" title="Reject"/></a>
					<a href="javascript:void(0);" onClick="return delete_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo geodir_plugin_url(); ?>/geodirectory-assets/images/delete.png" alt="Delete" title="Delete"/></a>
					<?php } ?>
					<?php if ($claiminfoObj->status=='1'){ ?>
					<a href="javascript:void(0);" onClick="return undo_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo $path_url; ?>/images/undo.png" alt="Undo" title="Undo" /></a>
					<a href="javascript:void(0);" onClick="return delete_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo geodir_plugin_url(); ?>/geodirectory-assets/images/delete.png" alt="Delete" title="Delete"/></a>
					<?php } ?>
					<?php if ($claiminfoObj->status=='2'){ ?>
					<a href="javascript:void(0);" onClick="return approve_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo $path_url; ?>/images/tick.png" alt="Approve" title="Approve" /></a>
					<a href="javascript:void(0);" onClick="return delete_rec('<?php echo $claiminfoObj->pid;?>', '<?php echo $nonce;?>');"><img src="<?php echo geodir_plugin_url(); ?>/geodirectory-assets/images/delete.png" alt="Delete" title="Delete" /></a>
					<?php } ?>
					</td>
					<td>&nbsp;</td>
			</tr>
			<?php
			}
			}
			?>
			</thead>
	</table></div><?php

}


function geodir_claim_template_loader( $template ){

	global $wp_query, $current_user, $plugin_prefix, $wpdb;
	
	if(isset($_REQUEST['geodir_ptype'])){
		
		global $information;
		
		if($current_user->ID){
			
			if(get_option('geodir_claim_auto_approve')=='yes'){
				
				if(!isset($_REQUEST['rs']) || (isset($_REQUEST['rs']) && $_REQUEST['rs']=='')){
					
					$information .= CLAIM_VARIFY_CODE_NOT_EXIST;
					
				}else{
						
					$rand_string = isset($_REQUEST['rs']) ? $_REQUEST['rs'] : '';	
					
					$approvesql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where rand_string=%s",array($rand_string));
					
					$approveinfo = $wpdb->get_results($approvesql);
					
					if($approveinfo)
					{
						$pid = $approveinfo[0]->pid;
						
						$post_id = $approveinfo[0]->list_id;
						
						$author_id = $approveinfo[0]->user_id;
						
						$user_id = $current_user->ID;
						
						$status = $approveinfo[0]->status;
						
						if($author_id==$user_id)
						{
							
							if($status==1)
							{
								$information .= CLAIM_LISTING_ALREADY_VARIFIED;
							}
							elseif($status==2)
							{
								$information .= CLAIM_LISTING_VERIFICATION_REJECTED;
							}
							else
							{
								
								$wpdb->query($wpdb->prepare("update $wpdb->posts set post_author=%d where ID=%d", array($author_id,$post_id))); 
								
								$wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set status='1' where pid=%d", array($pid)));
								
								geodir_save_post_meta($post_id, 'claimed','1');
																
								/** This action is documented in geodir_claim_functions.php */
								do_action('geodir_claim_request_status_change', $pid, 1, $status);
								
								geodir_clientEmail($post_id,$author_id,'claim_approved'); /* email to client */
								
								$information .= CLAIM_LISTING_SUCCESS_VERIFIED.' <a href="'.esc_attr(get_permalink($post_id)).'">'.$approveinfo[0]->list_title.'</a>';		
								
							}	
						}
						else
						{
							$information .= CLAIM_VARIFY_CODE_NOT_MATCH;
						}
					}
					else
					{
						$information .= CLAIM_VARIFY_CODE_NOT_EXIST;
					}	
				}
			
			}else{
				$information .= CLAIM_AUTO_VARIFY_DISABLE;
			}
			
		}else{
            $site_login_url = geodir_login_url();

            $information .= sprintf( CLAIM_LOGIN_REQUIRED, '<a href="'.$site_login_url.'" >');
		}
		
		$template = geodir_locate_template('information');
		
		if ( ! $template ) $template = geodir_plugin_path() . '/geodirectory-templates/geodir-information.php';
		
		$template = apply_filters('geodir_template_information',$template);
		
	}
	
	return $template;	
}

if(isset($_REQUEST['geodir_ptype']) && $_REQUEST['geodir_ptype']=='verify') {
    add_filter('the_content', 'geodir_claim_content_loader',10,1);
}

function geodir_claim_content_loader( $content ){

    global $wp_query, $current_user, $plugin_prefix, $wpdb;

    if(isset($_REQUEST['geodir_ptype']) && $_REQUEST['geodir_ptype']=='verify'){

        $content = '';

        if($current_user->ID){

            if(get_option('geodir_claim_auto_approve')=='yes'){

                if(!isset($_REQUEST['rs']) || (isset($_REQUEST['rs']) && $_REQUEST['rs']=='')){

                    $content .= CLAIM_VARIFY_CODE_NOT_EXIST;

                }else{

                    $rand_string = isset($_REQUEST['rs']) ? $_REQUEST['rs'] : '';

                    $approvesql = $wpdb->prepare("select * from ".GEODIR_CLAIM_TABLE." where rand_string=%s",array($rand_string));

                    $approveinfo = $wpdb->get_results($approvesql);

                    if($approveinfo)
                    {
                        $pid = $approveinfo[0]->pid;

                        $post_id = $approveinfo[0]->list_id;

                        $author_id = $approveinfo[0]->user_id;

                        $user_id = $current_user->ID;

                        $status = $approveinfo[0]->status;

                        if($author_id==$user_id)
                        {

                            if($status==1)
                            {
                                $content .= CLAIM_LISTING_ALREADY_VARIFIED;
                            }
                            elseif($status==2)
                            {
                                $content .= CLAIM_LISTING_VERIFICATION_REJECTED;
                            }
                            else
                            {

                                $wpdb->query($wpdb->prepare("update $wpdb->posts set post_author=%d where ID=%d", array($author_id,$post_id)));

                                $wpdb->query($wpdb->prepare("update ".GEODIR_CLAIM_TABLE." set status='1' where pid=%d", array($pid)));

                                geodir_save_post_meta($post_id, 'claimed','1');
								
								/** This action is documented in geodir_claim_functions.php */
								do_action('geodir_claim_request_status_change', $pid, 1, $status);

                                geodir_clientEmail($post_id,$author_id,'claim_approved'); /* email to client */

                                $content .= CLAIM_LISTING_SUCCESS_VERIFIED.' <a href="'.esc_attr(get_permalink($post_id)).'">'.$approveinfo[0]->list_title.'</a>';

                            }
                        }
                        else
                        {
                            $content .= CLAIM_VARIFY_CODE_NOT_MATCH;
                        }
                    }
                    else
                    {
                        $content .= CLAIM_VARIFY_CODE_NOT_EXIST;
                    }
                }

            }else{
                $content .= CLAIM_AUTO_VARIFY_DISABLE;
            }

        }else{
            $site_login_url = geodir_login_url();

            $content .= sprintf( CLAIM_LOGIN_REQUIRED, '<a href="'.$site_login_url.'" >');
        }


    }

    return $content;
}


function geodir_claim_default_option_form($tab_name){
	
	switch ($tab_name)
	{
		
		case 'geodir_claim_options' :
			
			geodir_admin_fields( geodir_claim_default_options() );?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodirclaim' ); ?>" />
			<input type="hidden" name="subtab" value="geodir_claim_options" id="last_tab" />
			</p>
			</div>
			
			<?php
			
		break;
		
		case 'geodir_claim_notification' :
			
			geodir_admin_fields( geodir_claim_notifications() ); ?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodirclaim' ); ?>" />
			<input type="hidden" name="subtab" value="geodir_claim_notification" id="last_tab" />
			</p>
			</div>
			 
		<?php break;
		
	}// end of switch
}
