jQuery(document).ready(function() {


 	jQuery("#gt-form-builder-tab ul li a").click(function() {
		if(!jQuery(this).attr('id')){return;}
		var htmlvar_name = jQuery(this).attr('id').replace('gd-','');
		
		var post_type = jQuery(this).closest('#gt-form-builder-tab').find('#new_post_type').val();
		
		var id = 'new'+jQuery(".field_row_main ul.advance li:last").index();
		
		var manage_field_type = jQuery(this).closest('#geodir-available-fields').find(".manage_field_type").val();
		
		if(manage_field_type == 'advance_search'){
			
			jQuery.get(geodir_admin_ajax.url+'?action=geodir_ajax_advance_search_action&create_field=true',{ htmlvar_name: htmlvar_name,listing_type:post_type, field_id: id, field_ins_upd: 'new' },
			function(data)
			{
				console.log(id);
				jQuery('.field_row_main ul.advance').append(data);
				
				jQuery('#licontainer_'+htmlvar_name).find('#sort_order').val( parseInt(jQuery('#licontainer_'+htmlvar_name).index()) + 1 );

				show_hide('field_frm'+htmlvar_name);
				jQuery('html, body').animate({
					scrollTop: jQuery("#licontainer_"+htmlvar_name).offset().top
				}, 1000);
				
			});

			if(htmlvar_name!='fieldset'){
				jQuery(this).closest('li').hide();
			}

		}
		
	});
	
	
	jQuery(".field_row_main ul.advance").sortable({ opacity: 0.8, placeholder: "ui-state-highlight",
		cancel: "input,label,select",cursor: 'move', update: function() {
			
			var order = jQuery(this).sortable("serialize") + '&update=update';
		
			jQuery.get(geodir_admin_ajax.url+'?action=geodir_ajax_advance_search_action&create_field=true', order, function(theResponse){
				
			});
		}
	});
		
		
		
});



function save_advance_search_field(id)
{	
	if(jQuery('#licontainer_'+id+' #field_title').length > 0){
		
		var htmlvar_name = jQuery('#licontainer_'+id+' #field_title').val();
		
		if(htmlvar_name == '')
		{
			alert(geodir_all_js_msg.custom_field_not_blank_var);
			
			return false;
		}
	}
	
	var field_data_type_range = jQuery('#licontainer_'+id).find('#field_data_type').val();
	
	var data_type_range = jQuery('#licontainer_'+id).find('#search_condition').val();
	var	data_type_change = jQuery('#licontainer_'+id).find('#data_type_change').val();
	
	if((field_data_type_range=='INT' || field_data_type_range =='FLOAT') && data_type_change!='TEXT'){
		
		var search_min_value='';
		var search_max_value='';
		var search_diff_value='';
		search_min_value = jQuery('#licontainer_'+id).find('input[name="search_min_value"]').val();
		search_max_value = jQuery('#licontainer_'+id).find('input[name="search_max_value"]').val();
		search_diff_value = jQuery('#licontainer_'+id).find('input[name="search_diff_value"]').val();
		var flag_cond ='';
		if(search_min_value==''){
			jQuery('#licontainer_'+id).find('input[name="search_min_value"]').css("border", "1px solid #FF0000");
			flag_cond= 'error';
		}else{
			jQuery('#licontainer_'+id).find('input[name="search_min_value"]').css("border", "1px solid #DDDDDD");
			flag_cond= '';
		}
		if(search_max_value==''){
			jQuery('#licontainer_'+id).find('input[name="search_max_value"]').css("border", "1px solid #FF0000");
			flag_cond= 'error';
		}else{
			flag_cond= '';
			jQuery('#licontainer_'+id).find('input[name="search_max_value"]').css("border", "1px solid #DDDDDD");
		}
		
		if(search_diff_value==''){
		jQuery('#licontainer_'+id).find('input[name="search_diff_value"]').css("border", "1px solid #FF0000");
			flag_cond= 'error';
			
		}else{
			flag_cond= '';
			jQuery('#licontainer_'+id).find('input[name="search_diff_value"]').css("border", "1px solid #DDDDDD");
		}
		if(flag_cond == 'error'){
			
			return false;
		}
	}
	
	var fieldrequest = jQuery('#licontainer_'+id).find("select, textarea, input").serialize();

	var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest ;
	
	jQuery.ajax({
		'url': geodir_admin_ajax.url+'?action=geodir_ajax_advance_search_action',
		'type': 'POST',
		'data':  request_data ,
		'success': function(result){
					
				
			 	if(jQuery.trim( result ) == 'HTML Variable Name should be a unique name')
				{
					
					alert(geodir_all_js_msg.custom_field_unique_name);
					
				}
				else
				{
					jQuery('#licontainer_'+id).replaceWith(jQuery.trim( result ));
					
					var order = jQuery(".field_row_main ul.advance").sortable("serialize") + '&update=update'; 
				
					jQuery.get(geodir_admin_ajax.url+'?action=geodir_ajax_advance_search_action&create_field=true', order, 
						function(theResponse){
							//alert(theResponse);
					}); 
					
					jQuery('.field_frm').hide();
				}															 
			
			 
		}
	});
	
	
}
function show_hide_advance_search(id)
{
	jQuery('#'+id).toggle();
}

function delete_advance_search_field(id, nonce,deleteid)
{
	
	var restore_id = id.replace('new','');
	
	var confarmation = confirm(geodir_all_js_msg.custom_field_delete);
	
	if(confarmation == true)
	{
		jQuery('#create_advance_search_li_'+deleteid).show();
		jQuery.get(geodir_admin_ajax.url+'?action=geodir_ajax_advance_search_action&create_field=true', { field_id: id, field_ins_upd: 'delete', _wpnonce:nonce },
		function(data)
		{
			jQuery('#licontainer_'+id).remove();
			
		});
		
		jQuery('#gd-'+deleteid).closest('li').show();
		
	}
	
}

function select_search_type(value,id){
	jQuery('#licontainer_'+id).find('#data_type').val('RANGE');
	jQuery('#licontainer_'+id).find('.expand_custom_area').css("display",'none');
	if(value=='TEXT'){
		
		jQuery('.search_type_drop').css("display",'none');
		jQuery('.search_type_text').css("display",'table-row');
		jQuery('#licontainer_'+id).find('#search_condition').val('SINGLE');
		jQuery('#licontainer_'+id).find('#search_condition_select').prop('selectedIndex',0);
		
	}else{
		if(value=='LINK' || value=='CHECK')
			jQuery('#licontainer_'+id).find('.expand_custom_area').css("display",'table-row');
		
		jQuery('#licontainer_'+id).find('#search_condition').val(value);
		jQuery('.search_type_text').css("display",'none');
		jQuery('.search_type_drop').css("display",'table-row');
	}
}

function select_range_option(value,id){
	jQuery('#licontainer_'+id).find('#search_condition').val(value);
}
function select_search_custom(value,id){
	jQuery('#licontainer_'+id).find('.expand_custom_area').css("display",'none');
	if(value=='LINK' || value=='CHECK'){
		jQuery('#licontainer_'+id).find('.expand_custom_area').css("display",'table-row');
	}

	// if its a check then we make it a multiple
	if(value=='CHECK'){
		jQuery('#licontainer_'+id).find('.gd-search-operator').css("display",'block');
	}else{
		jQuery('#licontainer_'+id).find('.gd-search-operator').css("display",'none');
	}

			
		
}
function search_difference_value(value){
	
	if(value==1)
	jQuery('.search_diff_value').show();
	else
	jQuery('.search_diff_value').hide();
}


/* ========== distance sorting options actions ======= */

jQuery(document).ready(function(){
	
	jQuery( document ).delegate( 'input[name="geodir_distance_sorting"]', "click", function() {
		var main = jQuery(this);
		
		jQuery('.geodir_distance_sort_options').each(function(){
			
			if(main.is(":checked") == true ){
				jQuery(this).show();
			}else{
				jQuery(this).hide();
			}
			
		});
		
	});
	
});




