
function geodir_set_primary_location(id, gdvalue)
{
	if(id == 'gd_set_city')
	{
		document.getElementById('gd_city').value = gdvalue;	
	}
	if(id == 'gd_set_region')
	{
		document.getElementById('gd_region').value = gdvalue;	
	}
	if(id == 'gd_set_country')
	{
		document.getElementById('gd_country').value = gdvalue;	
	}
	if(id == 'gd_set_lat')
	{
		document.getElementById('gd_lat').value = gdvalue;	
	}
	if(id == 'gd_set_log')
	{
		document.getElementById('gd_log').value = gdvalue;	
	}
}


function geodir_location_merge_ids()
{
	var error = false;
	
	 jQuery("#geodir_location-form-merge").find("#mergevalue:checked").each(function () {
		error = true;
	});
	
	if(error ==  true)
	{
		return true;
	}
	else
	{
		alert(geodir_location_all_js_msg.select_merge_city_msg);
		return false;
	}
	
}

function geodir_set_location_default(id, nonce)
{
	
	if(confirm(geodir_location_all_js_msg.set_location_default_city_confirmation))
	{
		window.location.href=geodir_location_all_js_msg.geodir_location_admin_ajax_url+"?action=geodir_locationajax_action&location_ajax_action=set_default&id="+id+"&_wpnonce="+nonce;
		return true;		
	}
	else
	{
		return false;
	}
	
}

function geodir_location_select_primary_city(id)
{
var mergealltext = document.getElementById('geodir_location_merge_ids').value;

var url =  geodir_location_all_js_msg.geodir_location_admin_url+'?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_manager&geodir_location_merge=merge&merge_id='+id+'&mergeall='+mergealltext;
	
		jQuery.post(url, {  },
				function(mergefrmhtml) {
						jQuery('#geodir_location_merge_div').html(mergefrmhtml);
					}
				);

}

function geodir_add_location_validation(fields){
		
		var error = false;
		
		if(fields.val() == ''){
				
				jQuery(fields).closest('.gtd-formfeild').find('.gd-location_message_error').show();
				error = true;
				
			}else{
				
				jQuery(fields).closest('.gtd-formfeild').find('.gd-location_message_error').hide();
				
			}
		
		if(error){
			return false;
		}else{
			return true;	
		}
		
	}

jQuery(document).ready(function(){
	
	jQuery('#geodir_location_save').click(function(){
	
		var is_validate = true;
		
		jQuery(this).closest('form').find('.required:visible').each(function(){
			
			var fields = jQuery(this).find('input, select');
			
			if(!geodir_add_location_validation(fields))
				is_validate = false;
				
		});
		
		if(!is_validate){return false;}
		
	});
	
	jQuery('.geodir_add_location_form').find(".required:visible").find('input').blur(function(){
		geodir_add_location_validation(jQuery(this));
	});
	
	jQuery('.geodir_add_location_form').find(".required:visible").find('select').change(function(){
		geodir_add_location_validation(jQuery(this));
	});
	
	
	// add multiple select / deselect functionality
	jQuery("#location_selectall").click(function () {
		jQuery('.merge_case').attr('checked', this.checked);
	});
	
	jQuery(".merge_case").click(function(){
		if(jQuery(".merge_case").length == jQuery(".merge_case:checked").length) {
			 jQuery("#location_selectall").attr("checked", "checked");
		} else {
				jQuery("#location_selectall").removeAttr("checked");
		}
	});
	
	
	jQuery('.button-primary').click(function(){
		var error = false;
		var characterReg = /^\s*[a-zA-Z0-9,\s]+\s*$/;
		var listing_prefix = jQuery('#geodir_listing_prefix').val();
		var location_prefix = jQuery('#geodir_location_prefix').val();
		var listingurl_separator = jQuery('#geodir_listingurl_separator').val();
		var detailurl_separator = jQuery('#geodir_detailurl_separator').val();
					
		if(listing_prefix==''){
			alert(geodir_location_all_js_msg.LISTING_URL_PREFIX);
			jQuery('#geodir_listing_prefix').focus();
			error = true; }
		
		if (!characterReg.test(listing_prefix) && listing_prefix!=''){
			jQuery('#geodir_listing_prefix').focus();
			alert(geodir_location_all_js_msg.LISTING_URL_PREFIX_INVALID_CHAR);
			error = true; }
			
		if(location_prefix==''){
			alert(geodir_location_all_js_msg.LOCATION_URL_PREFIX);
			jQuery('#geodir_location_prefix').focus();
			error = true; }
			
		if (!characterReg.test(location_prefix) && location_prefix!=''){
			alert(geodir_location_all_js_msg.LISTING_URL_PREFIX_INVALID_CHAR);
			jQuery('#geodir_location_prefix').focus();
			error = true; }
		
		if(listingurl_separator==''){
			alert(geodir_location_all_js_msg.LOCATION_CAT_URL_SEP);
			jQuery('#geodir_listingurl_separator').focus();
			error = true; }
		
		if (!characterReg.test(listingurl_separator) && listingurl_separator!=''){
			alert(geodir_location_all_js_msg.LOCATION_CAT_URL_SEP_INVALID_CHAR);
			jQuery('#geodir_listingurl_separator').focus();
			error = true; }
		
		if(detailurl_separator==''){
			alert(geodir_location_all_js_msg.LISTING_DETAIL_URL_SEP);
			jQuery('#geodir_detailurl_separator').focus();
			error = true; }
			
		if (!characterReg.test(detailurl_separator) && detailurl_separator!=''){
			alert(geodir_location_all_js_msg.LISTING_DETAIL_URL_SEP_INVALID_CHAR);
			jQuery('#geodir_detailurl_separator').focus();
			error = true; }
			
		if(error==true){
			return false;
		}else{
			return true;
		}
	});
	
});

function geodir_show_selected_countries(ele) {
	if (jQuery(ele).val() != 'selected') {
		jQuery('select#geodir_selected_countries').closest('tr').hide();
	} else {
		jQuery('select#geodir_selected_countries').closest('tr').show();
	}
	
	var countDrop = jQuery('input#geodir_everywhere_in_country_dropdown');
	var countPart = jQuery('input#geodir_location_hide_country_part');
	
	if (jQuery(ele).val() == 'default') {
		jQuery(countDrop).closest('tr').hide();
		jQuery(countDrop).prop('checked', false);
		jQuery(countPart).closest('tr').show();
	} else {
		jQuery(countDrop).closest('tr').show();
		jQuery(countPart).closest('tr').hide();
		jQuery(countPart).prop('checked', false);
	}
}

function geodir_show_selected_regions(ele) {
	if (jQuery(ele).val() != 'selected') {
		jQuery('select#geodir_selected_regions').closest('tr').hide();
	} else {
		jQuery('select#geodir_selected_regions').closest('tr').show();
	}
	
	var regDrop = jQuery('input#geodir_everywhere_in_region_dropdown');
	var regPart = jQuery('input#geodir_location_hide_region_part');
	
	if (jQuery(ele).val() == 'default') {
		jQuery(regDrop).closest('tr').hide();
		jQuery(regDrop).prop('checked', false);
		jQuery(regPart).closest('tr').show();
	} else {
		jQuery(regDrop).closest('tr').show();
		jQuery(regPart).closest('tr').hide();
		jQuery(regPart).prop('checked', false);
	}
}

function geodir_show_selected_cities(ele){
	
	if(jQuery(ele).val() != 'selected'){
		jQuery('select#geodir_selected_cities').closest('tr').hide();
	}else{
		jQuery('select#geodir_selected_cities').closest('tr').show();
	}
	
	if(jQuery(ele).val() == 'default'){
		jQuery('input#geodir_everywhere_in_city_dropdown').closest('tr').hide();
		jQuery('input#geodir_everywhere_in_city_dropdown').attr('checked', false);
	}else{
		jQuery('input#geodir_everywhere_in_city_dropdown').closest('tr').show();
	}
}


jQuery(document).ready(function(){
	
	var countryChecked = jQuery("input[name=geodir_enable_country]:checked");
	
	geodir_show_selected_countries(countryChecked);
	
	jQuery("input[name=geodir_enable_country]").click(function(){
		
		if(jQuery(this).is(':checked'))
			geodir_show_selected_countries(jQuery(this));
		
	});
	
	
	var regionChecked = jQuery("input[name=geodir_enable_region]:checked");
	
	geodir_show_selected_regions(regionChecked);
	
	jQuery("input[name=geodir_enable_region]").click(function(){
		
		if(jQuery(this).is(':checked'))
			geodir_show_selected_regions(jQuery(this));
		
	});
	
	var cityChecked = jQuery("input[name=geodir_enable_city]:checked");
	
	geodir_show_selected_cities(cityChecked);
	
	jQuery("input[name=geodir_enable_city]").click(function(){
		
		if(jQuery(this).is(':checked'))
			geodir_show_selected_cities(jQuery(this));
		
	});
	
	
	
	/* -------- */
	
});


/* --- location switcher list mode settings --- */

function geodir_show_changelocation_nave(ele){
	
	if(ele.is(':checked')){
			
			jQuery("input[name=geodir_location_switcher_list_mode]").closest('tr').show();
			
			if(jQuery("input[name=geodir_location_switcher_list_mode]:radio:checked").length == 0)
				jQuery("input[name=geodir_location_switcher_list_mode]:first").attr('checked', true);
			
		}else{
			
			jQuery("input[name=geodir_location_switcher_list_mode]").each(function(){
				
				jQuery(this).attr('checked', false);
				
			});
			
			jQuery("input[name=geodir_location_switcher_list_mode]").closest('tr').hide();
			
		}
}

jQuery(document).ready(function(){
	
	jQuery("input[name=geodir_show_changelocation_nave]").click(function(){
		
		geodir_show_changelocation_nave(jQuery(this));
		
	});
	
	var locationswitcher = jQuery("input[name=geodir_show_changelocation_nave]");
	
	geodir_show_changelocation_nave(locationswitcher);
	
});


/* ------- SET SET SETTINGS ------- */
jQuery(document).ready(function(){
	
	jQuery('.geodir_set_location_seo a.show_div, .geodir_set_location_seo_region a.show_div').click(function(){
		
		var slug = jQuery(this).closest('tr').find('input[class="location_slug"]').val();
		
		if(jQuery('.geodir_location_seo'+slug).css('display') != 'none') {
			jQuery('.geodir_location_city'+slug).hide();
			jQuery('.geodir_location_seo'+slug).hide();
			
			jQuery('.geodir_location_seo'+slug+' a.show_div').find('img').attr('src', geodir_location_all_js_msg.geodir_location_plugin_url+'/images/plus-white-icon.png');
			
			jQuery(this).find('img').attr('src', geodir_location_all_js_msg.geodir_location_plugin_url+'/images/plus-white-icon.png');
			
		}else{
			
			jQuery('.geodir_location_seo'+slug).show();
			jQuery(this).find('img').attr('src', geodir_location_all_js_msg.geodir_location_plugin_url+'/images/minus-white-icon.png');
			
		}
		
	});
	
	
	/* ------- AJAX REQUEST FOR SAVE SEO ------- */
	
	var timer;
	
	jQuery('#geodir_location_seo_settings .geodir_meta_keyword, #geodir_location_seo_settings .geodir_meta_description').on('change', function() {
		
		var field = jQuery(this).attr("class");
		var location_slug = jQuery(this).closest('tr').find('.location_slug').val();
		var field_val = jQuery(this).val();
		var nonce =  jQuery(this).closest('tr').find('input[name="wpnonce"]').val();
		var location_type = jQuery(this).closest('tr').find('.location_type').val();
		var country_slug = jQuery(this).closest('tr').find('.country_slug').val();
		var region_slug = jQuery(this).closest('tr').find('.region_slug').val();
		
		jQuery.post( geodir_location_all_js_msg.geodir_location_admin_ajax_url+"?action=geodir_locationajax_action", { field: field, location_slug: location_slug, field_val: field_val, wpnonce: nonce, location_ajax_action: 'geodir_set_location_seo', location_type: location_type, country_slug: country_slug, region_slug: region_slug})
		.done(function( data ) {
			//alert( "Data Loaded: " + data );
		});		
	});	
});

jQuery(function(){
	// add multiple select / deselect functionality
	jQuery("#country_slug_all").click(function() {
		jQuery('.country-slug').attr('checked', this.checked);
	});
	jQuery(".country-slug").click(function() {
		if(jQuery(".country-slug").length == jQuery(".country-slug:checked").length) {
			jQuery("#country_slug_all").attr("checked", "checked");
		} else {
			jQuery("#country_slug_all").removeAttr("checked");
		}
	});
});

function geodir_location_translate() {
	var error = true;
	jQuery("#geodir_location-form-translate").find("#country-slug:checked").each(function() {
		error = false;
	});
	if(error == false) {
		if (confirm(geodir_location_all_js_msg.select_location_translate_confirm_msg)) {
			return true;
		}
	} else {
		alert(geodir_location_all_js_msg.select_location_translate_msg);
	}
	return false;
}

function geodir_location_bulk_delete() {
	var error = false;
	var ids = '';
	jQuery("#geodir_location-form-merge").find("#mergevalue:checked").each(function() {
		ids += '&id[]=' + jQuery(this).val();
		error = true;
	});
	if (error == true) {
		if ( confirm(geodir_location_all_js_msg.delete_location_msg) ) {
			var submit_url = jQuery("#gd_location_bulk_url").val();
			var return_url = jQuery("#gd_location_page_url").val();
			window.location.href = submit_url + ids + '&return=' + encodeURIComponent(return_url);
		} else {
			return false;
		}
	} else {
		alert(geodir_location_all_js_msg.delete_bulk_location_select_msg);
		return false;
	}
}