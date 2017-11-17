// JavaScript Document
jQuery(document).ready(function() {
	// selection checkboxes
	jQuery("input[name='checkedall']").click(function() {
		if (jQuery(this).is(':checked')) {
			jQuery("input[type='checkbox']").each(function() {
				jQuery(this).prop('checked', true);
			});
		} else {
			jQuery("input[type='checkbox']").prop('checked', false);
		}
	});
	
	jQuery(document).on("click", ".three-tab li, .post-action span", function() {
		var ajax_actions = jQuery(this).attr('action');
		
		if (ajax_actions == 'ratingshowhide') {
			var comment_images_div = jQuery(this).closest('li').find('.edit-form-comment-images');
			var comment_rating_div = jQuery(this).closest('li').find('.edit-form-comment-rating');
			if (comment_rating_div.css('display') == 'none') {
				jQuery(this).find('a').html(geodir_reviewrating_all_js_msg.geodir_reviewrating_hide_ratings);
				comment_rating_div.slideDown('slow');
				jQuery(this).closest('li').find("[action='commentimages']").find('a').html('Show Images');
				comment_images_div.slideUp();
			} else {
				jQuery(this).find('a').html(geodir_reviewrating_all_js_msg.geodir_reviewrating_show_ratings);
				comment_rating_div.slideUp('slow');
			}
			return false;
		}
		
		if (ajax_actions == 'commentimages') {
			var comment_images_div = jQuery(this).closest('li').find('.edit-form-comment-images');
			var comment_rating_div = jQuery(this).closest('li').find('.edit-form-comment-rating');
			if (comment_images_div.css('display') == 'none') {
				jQuery(this).find('a').html(geodir_reviewrating_all_js_msg.geodir_reviewrating_hide_images);
				comment_images_div.slideDown('slow');
				jQuery(this).closest('li').find("[action='ratingshowhide']").find('a').html('Show MultiRatings');
				comment_rating_div.slideUp('slow');
			} else {
				jQuery(this).find('a').html(geodir_reviewrating_all_js_msg.geodir_reviewrating_show_images);
				comment_images_div.slideUp('slow');
			}
			return false;
		}
		
		var chkvalues = '';
		if (jQuery(this).attr('data-comment-id')) {
			chkvalues = jQuery(this).attr('data-comment-id');
		} else {
			jQuery("input[name='chk-action[]']:checked").each(function() {
				chkvalues += ',' + jQuery(this).val();
			});
		}
		
		var geodir_comment_search = jQuery('input[name="geodir_comment_search"]').val();
		var geodir_comment_posttype = jQuery('select[name="geodir_comment_posttype"]').val();
		var geodir_comment_sort = jQuery('select[name="geodir_comment_sort"]').val();
		var paged = jQuery('input[name="geodir_review_paged"]').val();
		var show_post = jQuery('input[name="geodir_review_show_post"]').val();
		var subtab = jQuery('input[name="subtab"]').val();
		var nonce = jQuery('input[name="geodir_review_action_nonce_field"]').val();
		
		jQuery.ajax({
			type: "POST",
			url: geodir_reviewrating_all_js_msg.geodir_reviewrating_admin_ajax_url,
			data: {
				ajax_action: 'comment_actions',
				comment_action: ajax_actions,
				comment_ids: chkvalues,
				subtab: subtab,
				geodir_comment_search: geodir_comment_search,
				geodir_comment_posttype: geodir_comment_posttype,
				geodir_comment_sort: geodir_comment_sort,
				paged: paged,
				show_post: show_post,
				_wpnonce: nonce
			}
		}).done(function(data) {
			if (data != '') {
				jQuery('.comment-listing').html(data);
				jQuery("input[name='checkedall']").prop('checked', false);
			}
			
			jQuery(function() {
				jQuery.ajax({
					type: "POST",
					url: geodir_reviewrating_all_js_msg.geodir_reviewrating_admin_ajax_url,
					data: {
						ajax_action: 'show_tab_head',
						gd_tab_head: subtab
					}
				}).done(function(data) {
					jQuery('.gd-tab-head').html(data);
				});
			});
		});
	});
	
	// review rating delete images //
	jQuery(".review_rating_thumb_remove").click(function() {
		var confirmbox = confirm(geodir_reviewrating_all_js_msg.geodir_reviewrating_delete_image_confirmation);
		
		if (confirmbox) {
			var this_var = jQuery(this);
			var removeimage_id = jQuery(this).find('input[name="comment_id"]').val();
			var delimgwpnonce = jQuery(this).find('input[name="delimgwpnonce"]').val();
			var image_url = jQuery(this).closest('li').find(".review_rating_images img").attr("src");
			
			jQuery.ajax({
				type: "POST",
				url: geodir_reviewrating_all_js_msg.geodir_reviewrating_admin_ajax_url,
				data: {
					ajax_action: 'remove_images_by_url',
					img_url: image_url,
					remove_image_id: removeimage_id,
					_wpnonce: delimgwpnonce
				}
			}).done(function(data) {
				if (jQuery.trim(data) == '0') {
					jQuery('.post-action').find('span').each(function() {
						if (jQuery(this).attr('action') == 'commentimages') jQuery(this).remove();
					});
				}
				this_var.closest('li').remove();
			});
		}
	});	
	
	//bulk actions
	jQuery('.three-tab ul li').click(function() {
		jQuery(this).attr('action');
	});
	
	jQuery("#gdcomment-filter_button").click(function() {
		var url = jQuery('input[name="review_url"]').val();
		var tab = jQuery('input[name="tab"]').val();
		var subtab = jQuery('input[name="subtab"]').val();
		var geodir_comment_search = jQuery('input[name="geodir_comment_search"]').val();
		var geodir_comment_posttype = jQuery('select[name="geodir_comment_posttype"]').val();
		var geodir_comment_sort = jQuery('select[name="geodir_comment_sort"]').val();
		
		window.location = url + '&tab=' + tab + '&subtab=' + subtab + '&geodir_comment_search=' + geodir_comment_search + '&geodir_comment_posttype=' + geodir_comment_posttype + '&geodir_comment_sort=' + geodir_comment_sort;
	});
	
	jQuery("select[name='geodir_comment_sort']").change(function() {
		jQuery("#gdcomment-filter_button").click();
	});
	
	jQuery(document).delegate(".comments_review_likeunlike .gdrr-btn-like", "click", function() {
		var $this = this;
		var cont = jQuery($this).closest('.comments_review_likeunlike');
		var comment_id = jQuery(cont).data('comment-id');
		var task = jQuery(cont).data('like-action');
		var wpnonce = jQuery(cont).data('wpnonce');
		if (!comment_id || !wpnonce || !(task == 'like' || task == 'unlike'))
			return false;
		
		var btnlike = jQuery($this).context.outerHTML;
		jQuery($this).replaceWith('<i class="fa fa-refresh fa-spin"></i>');		
		jQuery.post(geodir_reviewrating_all_js_msg.geodir_reviewrating_admin_ajax_url + "&ajax_action=review_update_frontend", {
			task: task,
			comment_id: comment_id,
			_wpnonce: wpnonce
		}).done(function(data) {
			if (data && data !== '0') {
				cont.replaceWith(data);
			} else {
				jQuery('.fa-refresh', cont).replaceWith(btnlike);
			}
		});
	});	
});
