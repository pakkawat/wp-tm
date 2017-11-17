<?php
function handle_hover_share_form_data() {
    if (isset($_POST['share-modal-form'])) {
//        var_dump($_POST);
//        die();
        if (isset($_POST['whoop-share-name'])) {
            $names = '';
            if(is_array($_POST['whoop-share-name'])) {
                $names = implode(",", $_POST['whoop-share-name']);
            }
            $names = trim(strip_tags(esc_sql($names)));
        } else {
            $names = '';
        }

        if (isset($_POST['whoop-share-email'])) {
            $emails = trim(strip_tags(esc_sql($_POST['whoop-share-email'])));
        } else {
            $emails = '';
        }

        if (isset($_POST['url'])) {
            $url = trim(strip_tags(esc_sql($_POST['url'])));
        } else {
            $url = home_url('/');
        }

        if (isset($_POST['notes'])) {
            $message = strip_tags(esc_sql($_POST['notes']));
        } else {
            $message = '';
        }

        $e_items = explode(',', $emails);
        $e_names = explode(',', $names);
        $items = array_merge($e_items, $e_names);
        $emails = array();
        foreach($items as $item) {
            if ( is_email( $item ) ) {
                $emails[] = $item;
            } elseif ( username_exists($item)) {
                // User exists
                $uid = bp_core_get_userid($item);
                $email = bp_core_get_user_email( $uid );
                $emails[] = $email;
            }
        }

        $current_user = wp_get_current_user();
        $sender_name = whoop_bp_member_name(whoop_get_current_user_name($current_user));
        $subject = sprintf( __( '%s wants to tell you about a business on %s', GEODIRECTORY_FRAMEWORK ), $sender_name, get_bloginfo( 'name' ) );
        if ($emails) {
            $body = $message . '\r\n\r\n' . $url;
            wp_mail( $emails, $subject, $body );
            bp_core_add_message( __( 'Message sent successfully!', GEODIRECTORY_FRAMEWORK ) );
        } else {
            bp_core_add_message( __( 'There was an error!', GEODIRECTORY_FRAMEWORK ) );
        }
        bp_core_redirect( $url );
    }
}
add_action( 'init', 'handle_hover_share_form_data', 99 );

function whoop_hover_share($url) {
    ?>
    <div class="comp-modal whoop-ajax-share">
        <div class="comp-modal-content-wrap">
            <div class="comp-modal-title">
                <a href="" class="bp-comp-cancel"><i class="fa fa-times comp-close-x" style="color: #333"></i></a>
                <h2><?php echo __( 'Share review', GEODIRECTORY_FRAMEWORK ); ?></h2>
            </div>
            <div class="comp-modal-content">
                <ul class="whoop-ajax-share-ul inline-layout">
                    <li>
                        <a href="http://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" class="whoop-btn whoop-btn-primary whoop-fb-btn whoop-btn-full whoop-fb-share">
                            <i class="fa fa-facebook" style="color: #2A396B;"></i> <?php echo __( 'Facebook', GEODIRECTORY_FRAMEWORK ); ?>
                        </a>
                        <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery('.whoop-fb-share').click(function(e) {
                                    e.preventDefault();
                                    window.open(jQuery(this).attr('href'), 'fbShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
                                    return false;
                                });
                            });
                        </script>
                    </li>
                    <li>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo $url; ?>" class="whoop-btn whoop-btn-primary whoop-tweet-btn whoop-btn-full">
                            <i class="fa fa-twitter" style="color: #007FBB;"></i>  <?php echo __( 'Twitter', GEODIRECTORY_FRAMEWORK ); ?>
                        </a>
                    </li>
                </ul>
                <div class="whoop-share-link">
                    <i class="fa fa-share"></i>
                    <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="<?php echo $url; ?>">
                </div>
                <fieldset class="hr-line">
                    <legend align="center">OR</legend>
                </fieldset>
                <?php
                if ( class_exists( 'BuddyPress' ) ) {
                    $friend_ids = friends_get_friend_user_ids(get_current_user_id());
                    $names = array();
                    if ($friend_ids) {
                        foreach ($friend_ids as $friend_id) {
                            $names[] = bp_core_get_username($friend_id);
                        }
                    }
                    //$usernames = implode("', '", $names);
                    $usernames = $names;
                } else {
                    //$usernames = '';
                    $usernames = array();
                }
                ?>
                <form action="" class="whoop-ajax-share-form" method="post">
                    <label for="whoop-share-name">To <span class="tiny-span-text"><?php echo __( 'You can either fill username or email or both', GEODIRECTORY_FRAMEWORK ); ?></span></label>
                    <?php
                    echo '<select multiple id="whoop-share-name-select" class="whoop_share_chosen_select" name="whoop-share-name[]">';

                    foreach ($usernames as $name) {
                        echo '<option  value="' . $name . '">' . $name . '</option>';
                    }
                    echo '</select>';
                    ?>
                    <span class="tiny-span-text" style="display: block; text-align: center"><?php echo __( 'and/or', GEODIRECTORY_FRAMEWORK ); ?></span>
                    <input style="width: 100%" type="text" name="whoop-share-email" id="whoop-share-email" placeholder="<?php echo __( 'Email addresses (Comma separated)', GEODIRECTORY_FRAMEWORK ); ?>"/>
                    <label for="notes"><?php echo __( 'Add a Note', GEODIRECTORY_FRAMEWORK ); ?> <span class="tiny-span-text"><?php echo __( 'Optional', GEODIRECTORY_FRAMEWORK ); ?></span></label>
                    <textarea name="notes" maxchar="1000"></textarea>
                    <input type="hidden" name="url" value="<?php echo $url; ?>"/>
                    <div class="whoop-pop-buttons">
                        <button type="submit" class="comp-submit-btn comp-share-submit-btn" name="share-modal-form" value="submit"><?php echo __( 'Share', GEODIRECTORY_FRAMEWORK ); ?></button>
                        <a class="bp-comp-cancel" href="#"><?php echo __( 'Cancel', GEODIRECTORY_FRAMEWORK ); ?></a>
                    </div>
                </form>

<!--                <script src="--><?php //echo get_template_directory_uri(); ?><!--/library/js/tag-it.min.js" type="text/javascript" charset="utf-8"></script>-->
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery('.whoop_share_chosen_select').chosen({
                            placeholder_text_multiple : '<?php echo __('Select Username(s)', GEODIRECTORY_FRAMEWORK); ?>'
                        });
                        jQuery('a.bp-comp-cancel').click(function (e) {
                            e.preventDefault();
                            var container = jQuery('.comp-modal');
                            container.hide();
                        });
//                        jQuery('form.whoop-ajax-share-form').submit(function (e) {
//                            e.preventDefault();
//                            var names = jQuery("#whoop-share-name-select").chosen().val();
//                            alert(names);
//                            jQuery("#whoop-share-name-select").val(names);
//                            return true;
//                        });

                    });
                </script>
            </div>
        </div>
    </div>
<?php
}

function whoop_tagit_enqueue_script() {
    wp_enqueue_script( 'tagit-min-js', get_template_directory_uri() . '/library/js/tag-it.min.js', array( 'jquery', 'jquery-ui' ) );
}

//add_action( 'wp_enqueue_scripts', 'whoop_tagit_enqueue_script' );

function whoop_hover_modal_ajax()
{
    check_ajax_referer('whoop-hover-nonce', 'whoop_hover_nonce');
    $type = strip_tags(esc_sql($_POST['type']));
    $post_id = strip_tags(esc_sql($_POST['pid']));
    $receiver_id = strip_tags(esc_sql($_POST['receiver_id']));
    $url = strip_tags(esc_sql($_POST['clink']));
    if ($type == 'share') {
        whoop_hover_share($url);
    } elseif ($type == 'compliment') {
        bp_compliments_modal_form($post_id, $receiver_id);
    }
    wp_die();
}

//Ajax functions
add_action('wp_ajax_whoop_hover_modal_ajax', 'whoop_hover_modal_ajax');

//Javascript
add_action('geodir_detail_before_main_content', 'whoop_hover_js');
function whoop_hover_js() {
    $ajax_nonce = wp_create_nonce("whoop-hover-nonce");
    ?>
    <div class="comp-modal" style="display: none;">
        <div class="comp-modal-content-wrap">
            <div class="comp-modal-title comp-loading-icon">
                <i class="fa fa-cog fa-spin"></i>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('a.whoop-hover-ajax').click(function (e) {
                e.preventDefault();
                var container = jQuery('.comp-modal');
                var type = jQuery(this).attr('data-type');
                var clink = jQuery(this).attr('data-clink');
                var receiver_id = jQuery(this).attr('data-receiver');
                var pid = jQuery(this).attr('data-pid');
                container.show();
                var data = {
                    'action': 'whoop_hover_modal_ajax',
                    'whoop_hover_nonce': '<?php echo $ajax_nonce; ?>',
                    'clink': clink,
                    'type': type,
                    'pid': pid,
                    'receiver_id': receiver_id
                };

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                    container.replaceWith(response);
                });
            });
        });
    </script>
<?php
}