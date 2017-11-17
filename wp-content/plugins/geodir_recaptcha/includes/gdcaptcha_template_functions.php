<?php
/**
 * Contains functions related to GD captcha templates.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 */

// MUST have WordPress.
if ( !defined( 'WPINC' ) )
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * ReCaptcha settings form content.
 *
 * @since 1.0.0
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $tab_name Tab name.
 */
function geodir_recaptcha_get_option_form( $tab_name ) {
	switch ( $tab_name ) {
		case 'gdcaptcha_settings': {
			geodir_admin_fields( geodir_recaptcha_settings() );
			?>
<!--suppress ALL -->
            <p class="submit">
  <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodir-recaptcha' ); ?>" />
  <input type="hidden" name="subtab" value="gdcaptcha_settings" id="last_tab" />
</p>
</div>
		<?php
		}
		break;		
	}// end of switch
}

/**
 * captcha language, see https://developers.google.com/recaptcha/docs/language
 *
 * @since 1.0.0
 * @since 1.0.8 Added special language tag zh-HK.
 * @package GeoDirectory_ReCaptcha
 *
 * @param string $default The default language.
 * @return string Language code.
 */
function geodir_recaptcha_language( $default = 'en' ) {
	$current_lang = get_locale();
	
	$current_lang = $current_lang != '' ? $current_lang : $default;
	
	$special_lang = array( 'zh-HK', 'zh-CN', 'zh-TW', 'en-GB', 'fr-CA', 'de-AT', 'de-CH', 'pt-BR', 'pt-PT', 'es-419' );
	if ( !in_array( $current_lang, $special_lang ) ) {
		$current_lang = substr( $current_lang, 0, 2 );
	}

    /**
     * Filters the recaptcha api language.
     *
     * @since 1.0.0
     * @package GeoDirectory_ReCaptcha
     */
	$language = apply_filters( 'geodir_recaptcha_api_language', $current_lang );
	
	return $language;
}

/**
 * Displays ReCaptcha form code.
 *
 * @since 1.0.0
 * @since 1.0.7 Fix success recaptcha response on ajax forms.
 * @package GeoDirectory_ReCaptcha
 *
 * @global object $bp BuddyPress object.
 *
 * @param string $form The form name.
 * @param string $extra_class Extra HTML classes.
 */
function geodir_recaptcha_display( $form, $extra_class='' ) {
	$site_key = get_option( 'geodir_recaptcha_site_key' );
	$secret_key = get_option( 'geodir_recaptcha_secret_key' );
    $captcha_version = get_option( 'geodir_recaptcha_client_version' );
	
	if ( strlen( $site_key ) > 10 && strlen( $secret_key ) > 10 ) {
		$captcha_title = get_option( 'geodir_recaptcha_title' );
				
		$language = geodir_recaptcha_language();
		$captcha_theme = geodir_recaptcha_theme();
        /**
         * Filters the recaptcha title.
         *
         * @since 1.0.0
         * @package GeoDirectory_ReCaptcha
         */
		$captcha_title = apply_filters( 'geodir_recaptcha_captcha_title', $captcha_title );
		
		$ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$div_id = 'gdcaptcha_' . $form;
		?>
		<div class="gd-captcha gd-captcha-<?php echo $form;?> <?php echo $extra_class;?>" style="margin:7px 0">
		<?php if ( trim( $captcha_title ) != '' ) { ?><label class="gd-captcha-title"><?php _e( $captcha_title ) ;?></label><?php } ?>
		<?php if ( $form == 'bp_registration' ) { global $bp; if ( !empty( $bp->signup->errors['gd_recaptcha_field'] ) ) { ?>
		<div class="error"><?php echo $bp->signup->errors['gd_recaptcha_field'];?></div>
		<?php } } ?>
		<div id="<?php echo $div_id;?>" class="gd-captcha-render"></div>
		<?php
		if ( $ajax ) { 
		?>
		<script type="text/javascript">
            <?php if ( $captcha_version != 'invisible' ): ?>
			var gdCaptchaSize = (jQuery( document ).width() < 1200) ? 'compact' : 'normal';
			<?php endif ?>
		try {
			var <?php echo $div_id;?> = function() {
                if (typeof grecaptcha == 'undefined') {
                    var to;
                    clearInterval(to);
                    to = setInterval(function(){
                        if ( typeof grecaptcha != 'undefined' ) {
                            clearInterval(to);
                            <?php if ( $captcha_version == 'invisible' ): ?>
                            for (var i = 0; i < document.forms.length; ++i) {
                                var form = document.forms[i];
                                var holder = form.querySelector('.gd-captcha-render');

                                if (null === holder) {
                                    continue;
                                }

                                (function(frm) {
                                    jQuery(holder).html('');

                                    if ( !jQuery(holder).html() ) {
                                        var holderId = grecaptcha.render(holder, {
                                            'sitekey': '<?php echo $site_key;?>',
                                            'size': 'invisible',
                                            'badge': 'inline', // possible values: bottomright, bottomleft, inline
                                            'callback': function (recaptchaToken) {
                                                HTMLFormElement.prototype.submit.call(frm);
                                            }
                                        });

                                        frm.onsubmit = function (evt) {
                                            evt.preventDefault();
                                            grecaptcha.execute(holderId);
                                        };
                                    }
                                })(form);
                            }
                            <?php else: ?>
                            if ( !jQuery('#<?php echo $div_id;?>').html() ) {
                                grecaptcha.render('<?php echo $div_id;?>', {'sitekey': '<?php echo $site_key;?>', 'theme': '<?php echo $captcha_theme;?>', 'callback': gdcaptcha_callback_<?php echo $div_id;?>, 'size': gdCaptchaSize});
                            }
                            <?php endif ?>
                        }
                    }, 50);
                } else {
                    <?php if ( $captcha_version == 'invisible' ): ?>
                    for (var i = 0; i < document.forms.length; ++i) {
                        var form = document.forms[i];
                        var holder = form.querySelector('.gd-captcha-render');

                        if (null === holder) {
                            continue;
                        }

                        (function(frm) {
                            if ( !jQuery(holder).html() ) {
                                var holderId = grecaptcha.render(holder, {
                                    'sitekey': '<?php echo $site_key;?>',
                                    'size': 'invisible',
                                    'badge': 'inline', // possible values: bottomright, bottomleft, inline
                                    'callback': function (recaptchaToken) {
                                        HTMLFormElement.prototype.submit.call(frm);
                                    }
                                });

                                frm.onsubmit = function (evt) {
                                    evt.preventDefault();
                                    grecaptcha.execute(holderId);

                                };
                            }
                        })(form);
                    }
                    <?php else: ?>
                    if ( !jQuery('#<?php echo $div_id;?>').html() ) {
                        grecaptcha.render('<?php echo $div_id;?>', {'sitekey': '<?php echo $site_key;?>', 'theme': '<?php echo $captcha_theme;?>', 'callback': gdcaptcha_callback_<?php echo $div_id;?>, 'size': gdCaptchaSize });
                    }
                    <?php endif ?>
                }
			}
			jQuery(function() {
                if (typeof grecaptcha == 'undefined') { // captcha API not loaded
                    jQuery.getScript('https://www.google.com/recaptcha/api.js?onload=<?php echo $div_id;?>&hl=<?php echo $language;?>&render=explicit').done(function (script, textStatus) {
                        <?php echo $div_id ?>();
                    }).fail(function (jqxhr, settings, exception) {
                        console.log(exception);
                    });
                } else {
                    <?php echo $div_id ?>();
                }
			});
		} catch(err) {
			console.log(err);
		}
        <?php if ( $captcha_version != 'invisible' ): ?>
		function gdcaptcha_callback_<?php echo $div_id;?>(res) {
			if (typeof res != 'undefined' && res) {
				jQuery('#<?php echo $div_id;?> .g-recaptcha-response').val(res);
			}
		}
		<?php endif ?>
		</script>
		<?php } else { ?>
		<script type="text/javascript">
        <?php if ( $captcha_version != 'invisible' ): ?>
        var gdCaptchaSize = (jQuery( document ).width() < 1200) ? 'compact' : 'normal';
        <?php endif ?>
		try {
            var <?php echo $div_id;?> = function() {
                <?php if ( $captcha_version == 'invisible' ): ?>
                for (var i = 0; i < document.forms.length; ++i) {
                    var form = document.forms[i];
                    var holder = form.querySelector('.gd-captcha-render');

                    if (null === holder) {
                        continue;
                    }

                    (function(frm) {
                        if ( !jQuery(holder).html() ) {
                            var holderId = grecaptcha.render(holder, {
                                'sitekey': '<?php echo $site_key;?>',
                                'size': 'invisible',
                                'badge': 'inline', // possible values: bottomright, bottomleft, inline
                                'callback': function (recaptchaToken) {
                                    HTMLFormElement.prototype.submit.call(frm);
                                }
                            });

                            console.log("NO AJAX <?php echo $div_id;?> / holder ID: " + holderId);

                            frm.onsubmit = function (evt) {
                                console.log("NO AJAX else: <?php echo $div_id;?>  / holder ID: " + holderId);
                                evt.preventDefault();
                                <?php if ($form == 'add_listing'): ?>
                                // Check required fields
                                add_listing_form_check_delegate(evt);
                                jQuery(".geodir_message_error:visible").length;

                                // Wait for required fields errors
                                setTimeout(function() {
                                    // NO errors, call the invisible captcha
                                    if (jQuery(".geodir_message_error:visible").length < 1) {
                                        grecaptcha.execute(holderId);
                                    }
                                }, 100);
                                <?php else: ?>
                                grecaptcha.execute(holderId);
                                <?php endif ?>
                            };
                        }
                    })(form);
                }
                <?php else: ?>
				if ( ( typeof jQuery != 'undefined' && !jQuery('#<?php echo $div_id;?>').html() ) || '<?php echo $form;?>'=='registration' ) {
                    grecaptcha.render('<?php echo $div_id;?>', { 'sitekey' : '<?php echo $site_key;?>', 'theme' : '<?php echo $captcha_theme;?>', 'size' : gdCaptchaSize });
				}
                <?php endif ?>
			};
		} catch(err) {
			console.log(err);
		}
		if ( typeof grecaptcha != 'undefined' && grecaptcha ) {
			<?php echo $div_id;?>();
		}
		</script>
        <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?onload=<?php echo $div_id;?>&hl=<?php echo $language;?>&render=explicit" async defer></script>
		<?php
		}
		?>
		</div>
		<?php
	} else {
		$plugin_settings_link = admin_url( '/admin.php?page=geodirectory&tab=geodir_recaptcha&subtab=gdcaptcha_settings' );
		?>
		<div class="gd-captcha gd-captcha-<?php echo $form; ?>">
			<div class="gd-captcha-err"><?php echo sprintf( __( 'To use reCAPTCHA you must get an API key from  <a target="_blank" href="https://www.google.com/recaptcha/admin">here</a> and enter keys in the plugin settings page at <a target="_blank" href="%s">here</a>' ), $plugin_settings_link ); ?></div>
		</div>
		<?php
	}
}
