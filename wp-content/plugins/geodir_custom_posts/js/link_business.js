// CPT Link Business

jQuery(document).ready(function() {
    jQuery('#geodir_link_cpt_business_autofill').bind("click", function() {
        var place_id = jQuery('select[name="geodir_link_cpt_business"]').val();
        var nonce = jQuery('input[name="geodir_link_cpt_business_nonce"]').val();
        if(place_id != '') {
            var ajax_url = geodir_cpt_link_alert_js_var.geodir_cpt_link_ajax_url;
            jQuery.post(ajax_url, {
                _wpnonce: nonce,
                auto_fill: "geodir_cpt_business_autofill",
                place_id: place_id
            }).done(function(data) {
                if(jQuery.trim(data) != '') {
                    var address = false;
                    var json = jQuery.parseJSON(data);
                    jQuery.each(json, function(i, item) {
                        if(item.key == 'text') {
                            if(item.value == false) item.value = '';
                            jQuery('input[name="' + i + '"]').val(item.value);
                        }
                        if(item.key == 'textarea') {
                            if(item.value == false) item.value = '';
                            jQuery('#' + i).val(item.value);
                            if(typeof tinymce != 'undefined') {
                                if(tinyMCE.get('content') && i == 'post_desc') {
                                    i = 'content';
                                    jQuery('#title').focus();
                                }
                                if(tinymce.editors.length > 0 && tinyMCE.get(i)) tinyMCE.get(i).setContent(item.value);
                            }
                        }
                        if(i == 'post_address') address = true;
                        if(i == 'post_city' || i == 'post_region' || i == 'post_country') {
                            if(jQuery("#" + i + " option:contains('" + item.value + "')").length == 0) {
                                jQuery("#" + i).append('<option value="' + item.value + '">' + item.value + '</option>');
                            }
                            jQuery('#' + i + ' option[value="' + item.value + '"]').attr("selected", true);
                            jQuery("#" + i).trigger("chosen:updated");
                        }
                        if(item.key == 'checkbox') {
                            var value = parseInt(item.value) > 0 ? 1 : 0;
                            jQuery('input[name="' + i + '"]').val(value);
                            var Ele = jQuery('input[name="' + i + '"][value="' + value + '"]');
                            if (jQuery(Ele).prop('type')!='checkbox') {
                                jQuery(Ele).closest('.geodir_form_row').find('input[type="checkbox"]').prop('checked', value);
                            }
                        }
                        if(item.key == 'radio') {
                            var value = item.value == false ? '' : item.value;
                            jQuery('input[name="' + i + '"][value="' + value + '"]').prop('checked', true);
                        }
                        if(item.key == 'select') {
                            var value = item.value == false ? '' : item.value;
                            jQuery('select[name="' + i + '"]').val(value);
                            jQuery('select[name="' + i + '"]').chosen().trigger("chosen:updated");
                        }
                        if(item.key == 'multiselect') {
                            var value = item.value == false ? '' : item.value;
                            var field_type = jQuery('[name="' + i + '[]"]').prop('type');
                            switch(field_type){
                                case 'checkbox':
                                case 'radio':
                                    jQuery('input[name="' + i + '[]"]').val(value);
                                    value = typeof value == 'object' && value ? value[0] : '';
                                    if (field_type == 'radio' && value != '') {
                                        jQuery('input[name="' + i + '[]"][value="' + value + '"]').prop('checked', true);
                                    }
                                    break;
                                default:
                                    jQuery('select[name="' + i + '[]"]').val(value);
                                    jQuery('select[name="' + i + '[]"]').chosen().trigger("chosen:updated");
                                    break;
                            }
                        }
                        if(item.key == 'datepicker' && item.value && item.value != '' ) {
                            jQuery('input[name="' + i + '"]').datepicker('setDate', item.value);
                        }
                        if(item.key == 'time' && item.value && item.value != '' ) {
                            jQuery('input[name="' + i + '"]').timepicker('setTime', new Date("January 1, 2015 "+item.value));
                        }
                        if(item.key == 'tags' && item.value && item.value != '' ) {
                            jQuery('input[name="post_tags"]').val(item.value);
                            jQuery('input[name="newtag[gd_event_tags]"]').val(item.value);
                        }
                    });
                    if(address) jQuery('#post_set_address_button').click();
                }
            });
        }
    });

    // now add an ajax function when value is entered in chose select text field
    /* alternative fix for chosen not supported on mobile device */
    if (!geodir_cpt_chosen_supported()) {
        jQuery('body').removeClass('gd-chosen-no-support').addClass('gd-chosen-no-support');
        
        jQuery("select#geodir_link_cpt_business").each(function() {
            var $el = jQuery(this);
            var $cont = $el.closest('.geodir_link_cpt_business_chosen_div');
            var hold = $el.attr('data-placeholder') ? $el.attr('data-placeholder') : '';
            $el.find('option[value=""]').text(geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_SELECT_BUSINESS);
            $el.removeAttr('size');
            $el.removeClass('gd-cpt-select-list');
            $el.find('.gd-cpt-no-chosen-search').remove();
            $el.wrap('<div class="gd-cpt-results"></div>');
            $el.before('<div class="chosen-search"><input type="text" class="gd-cpt-no-chosen-search" value="" onkeyup="javascript:geodir_cpt_no_chosen_search(this);" placeholder="' + hold + '" /><div class="gd-cpt-loader"><i class="fa fa-search"></i></div></div>');
        });
        
        var $select = jQuery(".gd-chosen-no-support select.geodir_link_cpt_business_chosen");
        var $val = $select.val();
        
        jQuery('body').click(function(e) {
            if (!jQuery(e.target).closest('.geodir_link_cpt_business_chosen_div').length) {
                geodir_cpt_relex_select($select);
            }
        });
        
        $select.click(function(e) {
            if (jQuery(this).val() == $val) {
                $select.change();
            }
            $val = jQuery(this).val();
        });
        
        $select.change(function(e) {
            geodir_cpt_relex_select(this);
            jQuery(this).closest('.geodir_link_cpt_business_chosen_div').find('.gd-cpt-no-chosen-search').val('');
        });
    } else {
        geodir_link_cpt_business_chosen_ajax();
    }
});

function geodir_link_cpt_business_chosen_ajax() {
    jQuery("select#geodir_link_cpt_business").each(function() {
        var curr_chosen = jQuery(this);
        var ajax_url = geodir_cpt_link_alert_js_var.geodir_cpt_link_ajax_url;
        var obj_name = curr_chosen.prop('name');
        var post_type = curr_chosen.data('post_type');
        var obbj_info = obj_name.split('_');
        listfor = obbj_info[1];
        
        if(curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined) {
            curr_chosen.ajaxChosen({
                keepTypingMsg: geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_KEEP_TYPE_TEXT,
                lookingForMsg: geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_LOOKING_FOR_TEXT,
                type: 'GET',
                url: ajax_url + '&task=geodir_cpt_link_fill_listings&post_type=' + post_type,
                dataType: 'html',
                success: function(data) {
                    curr_chosen.html(data).chosen().trigger("chosen:updated");
                }
            }, null,
            {
                no_results_text: geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_NO_RESULTS_MATCH_TEXT,
            });
        }
    });
}

/* alternative fix for chosen not supported on mobile device */
function geodir_cpt_chosen_supported() {
    if (window.navigator.appName === "Microsoft Internet Explorer") {
        return document.documentMode >= 8;
    }
    if (/iP(od|hone|ad)/i.test(window.navigator.userAgent)) {
        return false;
    }
    if (/Android/i.test(window.navigator.userAgent)) {
        if (/Mobile/i.test(window.navigator.userAgent)) {
            return false;
        }
    }
    return true;
}

function geodir_cpt_no_chosen_search(input) {
    var ajax_url = geodir_cpt_link_alert_js_var.geodir_cpt_link_ajax_url;
    var $input = jQuery(input);
    var $cont = $input.closest('.geodir_link_cpt_business_chosen_div');
    var $select = jQuery('select#geodir_link_cpt_business', $cont);
    var $val = $select.val();
    var post_type = $select.data('post_type');
    var term = $input.val();
    var $loader = jQuery('.gd-cpt-results .gd-cpt-loader .fa', $cont);
    term = typeof term != 'undefined' && term != '' ? term.replace(/^\s+/, '') : '';
    $loader.removeClass('fa-search').addClass('fa-refresh fa-spin');
    $select.attr('size', 1).html('<option value="">' + geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_SEARCHING + '</option>')
    jQuery.post(ajax_url + '&task=geodir_cpt_link_fill_listings&post_type=' + post_type + '&term=' + term, function(data) {
        $loader.removeClass('fa-refresh fa-spin').addClass('fa-search');
        $select.html(data);
        $select.find('option[value=""]').text(geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_SELECT_BUSINESS);
        $select.val($val).chosen().trigger("chosen:updated");
        var optCount = $select.children('option').length;
        if (parseInt(optCount) > 1) {
            $select.attr('size', optCount);
            $select.addClass('gd-cpt-select-list');
        } else {
            geodir_cpt_relex_select($select);
        }
    });
}

function geodir_cpt_relex_select(el) {
    jQuery(el).removeAttr('size').removeClass('gd-cpt-select-list');
}