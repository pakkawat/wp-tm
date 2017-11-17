jQuery(function(){
    jQuery('input.gd-franchise-chk').click(function(){
        var $this = this;
        var $form = jQuery($this).closest('form');
        if (jQuery($this).is(':checked')) {
            jQuery('input[name="gd_is_franchise"]', $form).val('1');
            
            if(!jQuery('#gd_franchise_lock_row', $form).is(':visible')) {
                jQuery('#gd_franchise_lock_row', $form).slideToggle();
            }
        } else {
            jQuery('input[name="gd_is_franchise"]', $form).val('0');
            
            if(jQuery('#gd_franchise_lock_row', $form).is(':visible')) {
                jQuery('#gd_franchise_lock_row', $form).slideToggle();
            }
        }
    });

    jQuery('input[name="gd_is_franchise"]', '#geodir_franchise_admin_field').click(function(){
        if (jQuery(this).val() == '1') {
            if(!jQuery('.franchise-fields-row', '#geodir_franchise_admin_field').is(':visible')) {
                jQuery('.franchise-fields-row', '#geodir_franchise_admin_field').slideToggle();
            }
        } else {
            if(jQuery('.franchise-fields-row', '#geodir_franchise_admin_field').is(':visible')) {
                jQuery('.franchise-fields-row', '#geodir_franchise_admin_field').slideToggle();
            }
        }
    });

    jQuery('select#gd_my_franchise').change(function(){
        var $this = this;
        var $form = jQuery($this).closest('form');
        
        var nonce = jQuery('#gd_franchise_nonce', $form).val();
        var post_id = jQuery($this).val();
        var franchise = jQuery('#franchise', $form).val();
                
        jQuery($form).addClass('gd-ajax-spinner');
        
        jQuery.ajax({
            url: gdFranchise.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                _wpnonce: nonce,
                task: 'franchise_data',
                post_id: post_id,
                franchise: franchise
            },
            beforeSend: function() {
                //jQuery($form).addClass('gd-ajax-spinner');
                jQuery('.gd-franchise-msg-error.gdfr-hide-msg', $form).remove();
            },
            success: function(data, textStatus, xhr) {
                try {
                    if ( data && typeof(data) == 'object' ) {
                        var address = false;
                        
                        var page_id = post_id ? post_id : jQuery('#gd_page_id', $form).val(); 
                        jQuery('input[name="add_listing_page_id"]', $form).val(post_id);
                        
                        jQuery('input[name="pid"]', $form).remove();
                        if (parseInt(post_id) > 0) {
                            jQuery('input[name="add_listing_page_id"]', $form).after('<input type="hidden" value="'+ post_id +'" name="pid" />');
                        }
                        
                        jQuery.each(data, function(name, field) {
                            var type = field.type;
                            var value = typeof field.value != 'undefined' && field.value != null ? field.value : '';
                            
                            switch (type) {
                                case 'text':
                                    jQuery('input[name="' + name + '"]', $form).val(value);
                                    
                                    if (name == 'post_address') {
                                        address = true;
                                    }
                                    
                                    if ((name == 'post_country' || name == 'post_region' || name == 'post_city') && value) {
                                        jQuery('#' + name, $form).val("");
                                        if(jQuery("#" + name + " option:contains('" + value + "')", $form).length == 0) {
                                            jQuery("#" + name, $form).append('<option value="' + value + '">' + value + '</option>');
                                        }
                                        jQuery('#' + name + ' option[value="' + value + '"]', $form).prop("selected", "selected");
                                        jQuery("#" + name, $form).trigger("chosen:updated");
                                    }
                                break;
                                case 'textarea':
                                    jQuery('#' + name, $form).val(value);
                                
                                    if(typeof tinymce != 'undefined') {
                                        if(tinyMCE.get('content') && name == 'post_desc') {
                                            name = 'content';
                                            jQuery('#title', $form).focus();
                                        }
                                        if (tinymce.editors.length > 0 && tinyMCE.get(name)) {
                                            tinyMCE.get(name).setContent(value);
                                        }
                                    }
                                break;
                                case 'select':
                                    jQuery('select[name="' + name + '"]', $form).val(value);
                                    jQuery('select[name="' + name + '"]', $form).chosen().trigger("chosen:updated");
                                break;
                                case 'multiselect':
                                    var field_type = jQuery('[name="' + name + '[]"]', $form).prop('type');
                                
                                    switch (field_type) {
                                        case 'checkbox':
                                        case 'radio':
                                            jQuery('input[name="' + name + '[]"]', $form).val(value);
                                            value = typeof value == 'object' && value ? value[0] : '';
                                            if (field_type == 'radio' && value != '') {
                                                jQuery('input[name="' + name + '[]"][value="' + value + '"]', $form).prop('checked', true);
                                            }
                                        break;
                                        default:
                                            jQuery('select[name="' + name + '[]"]', $form).val(value);
                                            jQuery('select[name="' + name + '[]"]', $form).chosen().trigger("chosen:updated");
                                        break;
                                    }
                                break;
                                case 'checkbox':
                                    var value = parseInt(value) > 0 ? 1 : 0;
                                    jQuery('input[name="' + name + '"]').val(value);
                                    var el = jQuery('input[name="' + name + '"][value="' + value + '"]', $form);
                                    if (jQuery(el).prop('type') == 'checkbox') {
                                        jQuery(el).prop('checked', value);
                                    } else {
                                        jQuery(el).closest('.geodir_form_row').find('input[type="checkbox"]', $form).prop('checked', value);
                                    }
                                    
                                    if (name == 'all_day') {
                                        jQuery(el).trigger('change');
                                    }
                                break;
                                case 'radio':
                                    jQuery('input[name="' + name + '"][value="' + value + '"]', $form).prop('checked', true);
                                break;
                                case 'datepicker':
                                    jQuery('input[name="' + name + '"]', $form).datepicker('setDate', value);
                                break;
                                case 'time':
                                    jQuery('input[name="' + name + '"]', $form).timepicker('setTime', new Date("January 1, 2015 "+value));
                                break;
                                case 'taxonomy':
                                    jQuery('div#' + name, $form).html( field.html );
                                    jQuery('#' + name + ' select', $form).chosen().trigger("chosen:updated");
                                break;
                                case 'tags':
                                    jQuery('input[name="post_tags"]', $form).val(value);
                                break;
                                case 'images':
                                    if (jQuery('#post_images', $form).length) {
                                        if (value && jQuery('#post_images', $form).length && jQuery('#post_imagesimage_limit', $form).length && jQuery('#post_imagesimage_limit', $form).val()!='') {
                                            var iLimit = jQuery('#post_imagesimage_limit', $form).val();
                                            var iArray = value.split(",");
                                            
                                            if (iArray.length > iLimit) {
                                                iArray = iArray.slice(0, iLimit);
                                                value = iArray.join();
                                            }
                                        }
                                        var total = 0;
                                        if (value != '') {
                                            iArray = value.split(",");
                                            total = iArray.length;
                                        }
                                        
                                        jQuery('#post_images', $form).val(value);
                                        jQuery('#post_imagestotImg', $form).val(total);
                                        
                                        plu_show_thumbs('post_images');
                                    }
                                break;
                                case 'file':
                                    if (jQuery('input[name="' + name + '"]', $form).length) {
                                        jQuery('input[name="' + name + '"]', $form).val(value);
                                    
                                        plu_show_thumbs(name);
                                    }
                                break;
                            }
                        });
                        
                        if (typeof baseMarker != 'undefined') {
                            var lat = jQuery('#post_latitude', $form).val();
                            var lng = jQuery('#post_longitude', $form).val();
                            if (lat && lng) {
                                baseMarker.setPosition(new google.maps.LatLng(lat, lng));
                                centerMap();
                            }
                        }
                    }
                } catch(err) {
                    console.log('GD ERROR FILL DATA: ' + err.message);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            complete: function(xhr, textStatus) {
                var to;
                clearTimeout(to);
                to = setTimeout(function(){
                    jQuery($form).removeClass('gd-ajax-spinner');
                }, 100);
            }
        });
    });

    jQuery(document).delegate('#propertyform', 'submit', function(e){
        var isValidate = true;
        var $form = this;
        if (!jQuery('#gd_my_franchise', $form).length && !jQuery($form).hasClass('gd-franchise-form')) {
            return true;
        }
        
        jQuery($form).find(".required_field:visible").each(function() {
            jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload").each(function() {
                
                if (jQuery(this).is('.chosen_select, .geodir_location_add_listing_chosen')) {
                    var chosen_ele = jQuery(this);
                    jQuery('#' + jQuery(this).attr('id') + '_chzn').mouseleave(function() {
                        validate_field(chosen_ele);
                    });
                }
                if (!validate_field(this)) {
                    isValidate = validate_field(this);
                }
            });
        });

        if (isValidate) {
            e.stopPropagation();
            e.preventDefault();
            gd_franchise_submit($form);
            return true;
        } else {
            jQuery(window).scrollTop(jQuery(".geodir_message_error:visible:first", $form).closest('.required_field').offset().top);
        }
        return false;
    });
});

function gd_franchise_form_init(form, franchise_id) {
    jQuery(form).addClass('gd-franchise-form gd-frm-franchise-s');
    jQuery(form).attr('gd-action', jQuery(form).attr('action'));
    jQuery(form).attr('action', 'javascript:void(0);');
    jQuery('#geodir-add-listing-submit [type="submit"]', form).val(gdFranchise.btn_text_save);
    var pay_text = jQuery('#gd_franchise_pay_row', form);
    jQuery('#gd_franchise_pay_row', form).remove();
    jQuery('#geodir-add-listing-submit', form).after(pay_text);
    
    gd_franchise_remove_elements(form);

    gd_franchise_duplicate_image_init(form, franchise_id, false);

    gd_franchise_check_payments(form, franchise_id);
}

function gd_franchise_duplicate_image_init(form, franchise_id, backend) {
    if (jQuery('#post_imagesdropbox', jQuery(form)).length > 0) {
        if (backend) {
            jQuery('#post_imagesdropbox', jQuery(form)).closest('.inside').after('<div class="geodir_form_row clearfix" id="geodir_img_duplicate_row"><input type="button" class="button button-primary button-large" value="' + gdFranchise.btn_duplicate_image + '" id="gdf_img_duplicate" onclick="javascript:gd_franchise_duplicate_images(this, ' + franchise_id + ', true);"></div>');
        } else {
            var limitImg = jQuery('#post_imagesimage_limit', jQuery(form)).val();
            
            if (limitImg === '' || parseInt(limitImg) > 0) {
                jQuery('#post_imagesdropbox', jQuery(form)).before('<div class="geodir_form_row clearfix" id="geodir_img_duplicate_row"><input type="button" class="geodir_button" value="' + gdFranchise.btn_duplicate_image + '" id="gdf_img_duplicate" onclick="javascript:gd_franchise_duplicate_images(this, ' + franchise_id + ', false);"></div>');
            }
        }
    }
}

function gd_franchise_lock_fields(fields, el, admin) {
    try {        
        if (typeof fields == 'object') {
            var field = '';
                        
            for (var index in fields) {
                if (fields.hasOwnProperty(index) && fields[index]) {
                    field = fields[index];
                    var fieldEl = jQuery('#'+field, el);
                    if (!jQuery(fieldEl).length > 0) {
                        if (jQuery('input[name="'+field+'"]', el).length > 0) {
                            fieldEl = jQuery('input[name="'+field+'"]', el);
                        } else if (jQuery('input[name="'+field+'[]"]', el).length > 0) {
                            fieldEl = jQuery('input[name="'+field+'[]"]', el);
                        }
                    }
                    
                    var row = jQuery(fieldEl).addClass('gd-locked').closest('.geodir_form_row');
                    if ( admin ) {
                        if (field == 'post_title') {
                            jQuery('[name="post_title"]', el).addClass('gd-locked').closest('#titlediv').hide();
                        } else if (field == 'post_tags') {
                            jQuery('[id^="new-tag-gd_"]', el).addClass('gd-locked').closest('.postbox').addClass('gd-hidden');
                        } else if (field == 'post_desc') {
                            jQuery('#content', el).addClass('gd-locked').closest('#postdivrich').hide();
                        }
                    }
                    
                    if (field == 'post') {
                        jQuery('#post_address', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('.geodir_location_add_listing_all_chosen_container', el).hide();
                        jQuery('#post_zip', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('#geodir_post_map_row', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('#post_latitude', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('#post_longitude', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('#post_mapview', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('#post_mapzoom', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                    } else if (field == 'claimed') {
                        jQuery('[name="claimed"]', el).addClass('gd-locked').closest('.geodir_form_row').hide();
                        jQuery('[name="claimed"]', el).addClass('gd-locked').closest('#geodir_claim_listing_information').addClass('gd-hidden');
                    } else {
                        jQuery(row).hide();
                    }
                    
                    if (field=='post_images') {
                        $prev = jQuery(row).prev();
                        if (jQuery($prev).attr('id') == 'geodir_form_title_row') {
                            jQuery($prev).hide();
                        }
                    }
                }
            }
            // Hide fieldset if all associated fields are locked.
            gd_franchise_hide_empty_fieldsets(el);
        }
            
        jQuery('[name="claimed"]', el).addClass('gd-locked').closest('.geodir_form_row').hide();
    } catch(err) {
        console.log('GD ERROR LOCK FIELDS: ' + err.message);
    }
}

function gd_franchise_submit(form) {
    var nonce = jQuery('#gd_franchise_nonce', form).val();
    var post_id = jQuery('#gd_my_franchise', form).val();
    post_id = post_id ? post_id : '';

    var btnCont = jQuery('[type="submit"]', form).closest('.geodir_form_row');
    jQuery.ajax({
        url: gdFranchise.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: 'task=save_franchise&post_id=' + post_id + '&_wpnonce=' + nonce + '&' + jQuery(form).serialize(),
        beforeSend: function() {
            jQuery(form).addClass('gd-ajax-spinner');
            jQuery('[type="submit"]', form).val(gdFranchise.btn_text_saving);
            jQuery('.gd-franchise-msg.gdfr-hide-msg', form).remove();
        },
        success: function(data, textStatus, xhr) {
            var err = '';
            if ( data && typeof(data) == 'object' ) {
                if (data.success && data.post_id && data.post_text) {
                    if (typeof(data.success_msg) != 'undefined' && data.success_msg != '') {
                        jQuery(btnCont).after('<div class="gd-franchise-msg gd-franchise-msg-success gdfr-hide-msg">' + data.success_msg + '</div>');
                    }
                    var myf = jQuery('option[value="'+data.post_id+'"]','#gd_my_franchise');
                    
                    if (jQuery(myf).text()) {
                        jQuery(myf).text(data.post_text);
                    } else {
                        jQuery('#gd_my_franchise', form).append('<option value="'+ data.post_id +'">'+ data.post_text +'</option>');
                    }
                    jQuery('#gd_my_franchise', form).val(data.post_id).chosen().trigger("chosen:updated");
                    jQuery('#gd_my_franchise', form).trigger("change");
                    
                    gd_franchise_check_payments(form, jQuery('#franchise', form).val());
                } else {
                    if (!data.success && typeof(data.error) != 'undefined' && data.error) {
                        err = data.error;
                    } else {
                        err = gdFranchise.txt_save_error;
                    }
                }
            } else {
                err = gdFranchise.txt_save_error;
            }
            
            if (err) {
                jQuery(btnCont).append('<div class="gd-franchise-msg gd-franchise-msg-error gdfr-hide-msg">' + err + '</div>');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(errorThrown);
            jQuery(btnCont).append('<div class="gd-franchise-msg gd-franchise-msg-error gdfr-hide-msg">' + gdFranchise.txt_save_error + '</div>');
        },
        complete: function(xhr, textStatus) {
            var to;
            clearTimeout(to);
            to = setTimeout(function(){
                jQuery('[type="submit"]', form).val(gdFranchise.btn_text_save);
                jQuery(form).removeClass('gd-ajax-spinner');
            }, 100);
        }
    });
}

function gd_franchise_check_payments(form, franchise_id) {
    var nonce = jQuery('#gd_franchise_nonce', form).val();

    jQuery.ajax({
        url: gdFranchise.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: 'task=check_payments&franchise_id=' + franchise_id + '&_wpnonce=' + nonce,
        beforeSend: function() {},
        success: function(data, textStatus, xhr) {
            var gdf_pay_el = jQuery('#gd_franchise_cost_row', form);
            var gdf_btn_el = jQuery('#gd_franchise_btn_row', form);
            
            if (data && typeof(data) == 'object' && data.amount && parseFloat(data.amount) > 0) {
                jQuery(gdf_pay_el).html('<span>' + data.info + '</span>');
                jQuery(gdf_btn_el).html('<input id="gd_franchise_pay_for" onclick="gd_franchise_pay_franchises(' + franchise_id + ');" type="button" class="geodir_button" value="' + gdFranchise.btn_pay_for_franchises + '" />');
                jQuery('#gd_franchise_pay_row').show();
            } else {
                jQuery(gdf_pay_el).html('');
                jQuery(gdf_btn_el).html('');
                jQuery('#gd_franchise_pay_row').hide();
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        complete: function(xhr, textStatus) {}
    });
    }

    function gd_franchise_pay_franchises(franchise_id) {
    if (!confirm(gdFranchise.btn_pay_are_you_sure)) {
        return false;
    }

    if (!parseInt(franchise_id)>0) {
        return false;
    }
    jQuery('#gd_franchise_pay_for').val(gdFranchise.txt_processing).prop('disabled', true);

    var _wpnonce = jQuery('input#gd_franchise_nonce').val();

    var form = '';
    form += '<form name="gd_pay_for_franchises" id="gd_pay_for_franchises" method="POST" action="' + gdFranchise.ajax_url + '">';
    form += '<input type="hidden" name="geodir_ajax" value="add_franchise" />';
    form += '<input type="hidden" name="ajax_action" value="checkout_now" />';
    form += '<input type="hidden" name="_wpnonce" value="' + _wpnonce + '" />';
    form += '<input type="hidden" name="franchise_id" value="' + franchise_id + '" />';
    form += '</form>';
    jQuery('form#gd_pay_for_franchises').remove();
    jQuery('body').append(form);
    jQuery('form#gd_pay_for_franchises').submit();
}

function gd_franchise_add_another() {
    var $form = jQuery('.gd-franchise-form #gd_my_franchises');
    jQuery('#gd_my_franchise', $form).val(0).chosen().trigger("chosen:updated");
    jQuery('#gd_my_franchise', $form).trigger("change");

    var scrollTo = parseInt($form.offset().top + $form.scrollTop())
    scrollTo = Math.max(0, (scrollTo - 80));
    jQuery('body, html').animate({ scrollTop: scrollTo}, 750);

    jQuery('.gd-franchise-form .gd-franchise-msg.gdfr-hide-msg').remove();
    return;
}

function gd_franchise_hide_empty_fieldsets(el) {
    var fSet, fSetId, fSetClass, fieldRow, hide;

    jQuery('.geodir-fieldset-row', el).each(function() {
        hide = false;
        fSet = this;
        fSetId = jQuery(fSet).attr('gd-fieldset');
        
        if (typeof fSetId != 'undefined' && fSetId != '') {
            fSetClass = '.gd-fieldset-' + fSetId;
            
            if (jQuery(fSetClass, el).attr('class')) {
                hide = true;
                jQuery(fSetClass, el).each(function() {
                    
                    if (jQuery(this).is(':visible')) {
                        hide = false;
                        return false;
                    }
                });
            };
            
            if (hide) {
                jQuery(fSet).hide();
            }
        }
    });
}

function gd_franchise_duplicate_images(el, franchise_id, backend) {
    el = jQuery(el);
    var form = el.closest('form');
    if (!parseInt(franchise_id) > 0)
        return false;

    var nonce = jQuery('#gd_franchise_nonce', form).val();
    jQuery.ajax({
        url: gdFranchise.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: 'task=duplicate_images&franchise_id=' + franchise_id + '&_wpnonce=' + nonce,
        beforeSend: function() {
            el.val(gdFranchise.btn_duplicating_image).prop('disabled', true);
            jQuery('.gd-franchise-msg.gdfr-hide-msg', form).remove();
        },
        success: function(res, textStatus, xhr) {
            if (res.success) {
                var images = res.data.images;
                
                if (images && images.length > 0) {
                    jQuery('input#post_images', form).val(images.join());
                    jQuery('input#post_imagestotImg', form).val(images.length);
                    
                    if (backend) {
                        jQuery('#geodir_post_images.postbox .inside .geodir_thumbnail', form).html('<img src="' + images[0] + '" alt="image" style="max-height:125px;">');
                    }
                    
                    plu_show_thumbs('post_images');
                } else if (res.error) {
                    var err = '<div class="gd-franchise-msg gd-franchise-msg-success gdfr-hide-msg">' + res.error + '</div>';
                    if (backend) {
                        el.closest('.geodir_form_row').before(err);
                    } else {
                        el.closest('.geodir_form_row').after(err);
                    }
                }
            } else if (res.error) {
                var err = '<div class="gd-franchise-msg gd-franchise-msg-error gdfr-hide-msg">' + res.error + '</div>';
                if (backend) {
                    el.closest('.geodir_form_row').before(err);
                } else {
                    el.closest('.geodir_form_row').after(err);
                }
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(errorThrown);
            var err = '<div class="gd-franchise-msg gd-franchise-msg-error gdfr-hide-msg">' + gdFranchise.err_duplicate_image + '</div>';
            if (backend) {
                el.closest('.geodir_form_row').before(err);
            } else {
                el.closest('.geodir_form_row').after(err);
            }
        },
        complete: function(xhr, textStatus) {
            el.val(gdFranchise.btn_duplicate_image).prop('disabled', false);
        }
    });
}

function gd_franchise_remove_elements(el) {
    var h = jQuery('#geodir_coupon_code_row', jQuery(el)).prev();
    if ( h.prop('tagName') === 'H5' || h.prop('tagName') === 'h5' ) {
        h.remove();
    }
    var h = jQuery('#gdfi_import_url', jQuery(el)).prev();
    if ( h.prop('tagName') === 'H5' || h.prop('tagName') === 'h5' ) {
        h.remove();
    }
    
}