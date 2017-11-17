<?php
/**
 * This is the main plugin file, everything starts from here
 *
 * @package     GeoDirectory_Social_Importer
 * @copyright   2016 AyeCode Ltd
 * @license     GPL-2.0+
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: GeoDirectory Social Importer
 * Plugin URI: http://wpgeodirectory.com
 * Description: GeoDirectory Social Importer
 * Version: 1.3.3
 * Author: GeoDirectory
 * Author URI: https://wpgeodirectory.com
 * Update URL: https://wpgeodirectory.com
 * Update ID: 65886
 */

define('GEODIRSOCIALIMPORT_VERSION', '1.3.3');
if (!defined('GEODIRSOCIALIMPORT_TEXTDOMAIN')) define('GEODIRSOCIALIMPORT_TEXTDOMAIN', 'geodir_socialimporter');

global $wpdb, $plugin_prefix, $geodir_addon_list;

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
    if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
        require_once('gd_update.php'); // require update script
    }
}

///GEODIRECTORY CORE ALIVE CHECK START
if (is_admin()) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (!is_plugin_active('geodirectory/geodirectory.php')) {
        return;
    }
}
/// GEODIRECTORY CORE ALIVE CHECK END

if (!isset($plugin_prefix))
    $plugin_prefix = $wpdb->prefix . 'geodir_';

add_action('plugins_loaded', 'geodir_load_translation_socialimporter');
function geodir_load_translation_socialimporter()
{
    $locale = apply_filters('plugin_locale', get_locale(), 'geodir_socialimporter');
    load_textdomain('geodir_socialimporter', WP_LANG_DIR . '/' . 'geodir_socialimporter' . '/' . 'geodir_socialimporter' . '-' . $locale . '.mo');
    load_plugin_textdomain('geodir_socialimporter', false, dirname(plugin_basename(__FILE__)) . '/geodir-social-importer-languages');
}

require_once('yelp-functions.php');

if (!defined('GEODIR_COUNTRIES_TABLE')) define('GEODIR_COUNTRIES_TABLE', $plugin_prefix . 'countries');
if (is_admin() || ( defined( 'WP_CLI' ) && WP_CLI )) {
    register_activation_hook(__FILE__ , 'geodir_social_importer_activation');
    register_deactivation_hook(__FILE__ , 'geodir_social_importer_deactivation');
    
    add_filter('geodir_settings_tabs_array', 'gdfi_adminpage_facebook_integration', 5);
    add_action('geodir_admin_option_form', 'gdfi_facebook_integration_tab_content', 5);
    add_action('admin_init', 'gdfi_facebook_integration_oauth');
    add_action('wp_ajax_gdfi_facebook_integration_ajax_action', "gdfi_facebook_integration_ajax");
    add_action('admin_init', 'geodir_social_importer_activation_redirect');
    add_filter('geodir_plugins_uninstall_settings', 'geodir_social_importer_uninstall_settings', 10, 1);
}

/**
 * Plugin activation hook.
 *
 * @since 1.2.4
 */
function geodir_social_importer_activation() {
    if ( get_option( 'geodir_installed' ) ) {
        add_option( 'geodir_social_importer_activation_redirect', 1 );
    }
}

/**
 * Plugin deactivation hook.
 *
 * @since 1.2.4
 */
function geodir_social_importer_deactivation() {
    // Plugin deactivation stuff here.
}

/**
 * Check GeoDirectory plugin installed.
 *
 * @since 1.2.4
 */
function geodir_social_importer_plugin_activated( $plugin ) {
    if ( !get_option( 'geodir_installed' ) )  {
        $file = plugin_basename( __FILE__ );
        
        if ( $file == $plugin ) {
            $all_active_plugins = get_option( 'active_plugins', array() );
            
            if ( !empty( $all_active_plugins ) && is_array( $all_active_plugins ) ) {
                foreach ( $all_active_plugins as $key => $plugin ) {
                    if ( $plugin == $file ) {
                        unset( $all_active_plugins[$key] );
                    }
                }
            }
            update_option( 'active_plugins', $all_active_plugins );
        }
        
        wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir_socialimporter' ) );
    }
}

/**
 * Plugin activation redirect.
 *
 * @since 1.2.4
 */
function geodir_social_importer_activation_redirect() {
    if ( get_option( 'geodir_social_importer_activation_redirect', false ) ) {
        delete_option( 'geodir_social_importer_activation_redirect' );
        
        wp_redirect( admin_url( 'admin.php?page=geodirectory&tab=facebook_integration&subtab=geodir_gdfi_options' ) ); 
    }
}

/**
 * Facebook Page Feed Parser
 *
 * @using cURL
 */
function gdfi_facebook_integration_ajax()
{
    if (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_gdfi_options') {

        gdfi_facebook_integration_from_submit_handler();

        $msg = __("Your settings have been saved.", 'geodir_socialimporter');

        $msg = urlencode($msg);

        $location = admin_url() . "admin.php?page=geodirectory&tab=facebook_integration&subtab=" . $_REQUEST['subtab'] . "&claim_success=" . $msg;

        wp_redirect($location);
        gd_die();

    } elseif (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'manage_gdfi_options_yelp') {
        gdfi_yelp_integration_from_submit_handler();

        $msg = __("Your settings have been saved.", 'geodir_socialimporter');

        $msg = urlencode($msg);

        $location = admin_url() . "admin.php?page=geodirectory&tab=facebook_integration&subtab=" . $_REQUEST['subtab'] . "&claim_success=" . $msg;

        wp_redirect($location);
        gd_die();

    }
}

function gdfi_adminpage_facebook_integration($tabs)
{

    $tabs['facebook_integration'] = array('label' => __('Social Importer', 'geodir_socialimporter'),
        'subtabs' => array(
            array('subtab' => 'geodir_gdfi_options',
                'label' => __('Facebook', 'geodir_socialimporter'),
                'form_action' => admin_url('admin-ajax.php?action=gdfi_facebook_integration_ajax_action')),
            array('subtab' => 'manage_gdfi_options_yelp',
                'label' => __('Yelp', 'geodir_socialimporter'),
                'form_action' => admin_url('admin-ajax.php?action=gdfi_facebook_integration_ajax_action')),
        )
    );

    return $tabs;

}

function gdfi_facebook_integration_tab_content()
{
    global $wpdb;

    if (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'geodir_gdfi_options') {
        gdfi_facebook_integration_setting_fields();
    }

    if (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] == 'manage_gdfi_options_yelp') {
        gdfi_yelp_integration_setting_fields();
    }
}

function gdfi_facebook_integration_setting_fields() {
    global $wpdb;
    $post_types = geodir_social_post_types_allowed();
    $disable_post_to_fb = (int)get_option('geodir_social_disable_post_to_fb');
    $gdsi_posttypes = get_option('geodir_social_cpt_to_fb');
    $disable_auto_post = (int)get_option('geodir_social_disable_auto_post');
    ?>
    <div class="inner_content_tab_main">
        <div class="gd-content-heading active">
            <h3><?php _e('Enter your Facebook app details.', 'geodir_socialimporter'); ?></h3>

            <table class="form-table">
                <?php
                $gdfi_config = get_option('gdfi_config');
                ?>
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Facebook App ID', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_app_id" id="gdfi_app_id" type="text" style=" min-width:300px;"
                               value="<?php if (!empty($gdfi_config['app_id'])) {
                                   echo $gdfi_config['app_id'];
                               }?>">
                        <span
                            class="description"><?php _e('Enter your Facebook app ID', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Facebook App Secret', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_app_secret" id="gdfi_app_secret" type="password" style=" min-width:300px;"
                               value="<?php if (!empty($gdfi_config['app_secret'])) {
                                   echo $gdfi_config['app_secret'];
                               }?>">
                        <span
                            class="description"><?php _e('Enter your Facebook app secret', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"
                        class="titledesc"><?php _e('OAuth redirect URI', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input type="text" readonly value="<?php
                        echo admin_url( 'admin.php?page=geodirectory&tab=facebook_integration' );
                        ?>" class="large-text" >
                        <span class="description"><?php _e('This setting needs to be added to your app settings Products>Facebook Login>OAuth redirect URI', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                <?php if (!empty($gdfi_config['app_id']) && !empty($gdfi_config['app_secret'])) { ?>
                    <script type="text/javascript">
                        win = '';
                        function gdfi_auth_popup() {
                            win = window.open("https://www.facebook.com/dialog/oauth?client_id=<?php echo $gdfi_config['app_id'];?>&display=popup&redirect_uri=<?php echo urlencode(admin_url()."admin.php?page=geodirectory&tab=facebook_integration");?>&scope=email,publish_actions,rsvp_event,manage_pages,publish_pages", "gdfi_auth", "scrollbars=no,menubar=no,height=400,width=600,resizable=yes,toolbar=no,status=no");
                            var pollTimer = window.setInterval(function () {
                                if (win.closed !== false) { // !== is required for compatibility with Opera
                                    window.clearInterval(pollTimer);
                                    location.reload();// reload the page to show the app as connected
                                }
                            }, 200);
                            return false;
                        }

                    </script>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e('Connect App', 'geodir_socialimporter'); ?></th>
                        <td class="forminp">
                            <a class="button-primary" onclick="gdfi_auth_popup();" href="" ><?php if (!empty($gdfi_config['access_token'])) {
                                    _e('Refresh Access Token', 'geodir_socialimporter');
                                } else {
                                    _e('Connect Your App', 'geodir_socialimporter');
                                } ?></a>
                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e('Post to page', 'geodir_socialimporter'); ?></th>
                        <td class="forminp">
                            <select name="gdfi_app_page_post" id="gdfi_app_page_post">
                                <option
                                    value=""><?php _e('Select page - DISABLED', 'geodir_socialimporter'); ?></option>
                                <?php
                                $set_page = isset($gdfi_config['app_page_post']) ? $gdfi_config['app_page_post'] : '';
                                echo gdfi_get_fb_pages($set_page); ?>
                            </select>
                            <span
                                class="description"><?php _e('Select a Facebook page to post new listings to.', 'geodir_socialimporter'); ?></span>
                        </td>
                    </tr>

                <?php }

                if (!empty($gdfi_config['access_token'])) {
                    ?>

                    <tr valign="top">
                        <th scope="row"
                            class="titledesc"><?php _e('Facebook Access Token', 'geodir_socialimporter'); ?></th>
                        <td class="forminp">
                            <?php _e('Active, expires: ', 'geodir_socialimporter');

                            if (is_numeric($gdfi_config['access_token_expire']) && $gdfi_config['access_token_expire'] > '0') {
                                echo date('F j, Y, g:i a', $gdfi_config['access_token_expire']);
                            } elseif ($gdfi_config['access_token_expire'] == '0') {
                                echo __('Never', 'geodir_socialimporter');
                            }?>
                            <span class="description"><?php ?></span>
                        </td>
                    </tr>


                <?php }?>
                </tbody>
            </table>
            <h3><?php _e('Post to Facebook settings.', 'geodir_socialimporter'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th class="titledesc" scope="row"><?php _e('Disable Post To Facebook', 'geodir_socialimporter'); ?></th>
                        <td class="forminp">
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Disable Post To Facebook', 'geodir_socialimporter'); ?></span></legend>
                                <label for="geodir_social_disable_post_to_fb"><input type="checkbox" <?php checked($disable_post_to_fb, 1); ?> value="1" id="geodir_social_disable_post_to_fb" name="geodir_social_disable_post_to_fb"> <?php _e('Tick to disable post to Facebook feature for all post types.', 'geodir_socialimporter'); ?></label><br>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th class="titledesc" scope="row"><?php _e('Enable post type for Post To Facebook', 'geodir_socialimporter'); ?></th>
                        <td class="forminp">
                            <select option-ajaxchosen="false" data-placeholder="<?php esc_attr_e('Select post type', 'geodir_socialimporter'); ?>" class="chosen_select" style="min-width:300px;display:none;" id="geodir_social_cpt_to_fb" name="geodir_social_cpt_to_fb[]" multiple="multiple">
                                <?php if (!empty($post_types)) { foreach( $post_types as $name => $label) { ?>
                                <option value="<?php echo $name;?>" <?php selected((!empty($gdsi_posttypes) && in_array($name, $gdsi_posttypes)), true); ?>><?php echo $label; ?></option>
                                <?php } } ?>
                            </select>
                            <span class="description"><?php _e('Select post type to enable post to Facebook when listing published. Leave blank to enable post to Facebook for all allowed post types.', 'geodir_socialimporter'); ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th class="titledesc" scope="row"><?php _e('Disable auto Post To Facebook', 'geodir_socialimporter'); ?></th>
                        <td class="forminp">
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Disable auto Post To Facebook', 'geodir_socialimporter'); ?></span></legend>
                                <label for="geodir_social_disable_auto_post"><input type="checkbox" <?php checked($disable_auto_post, 1); ?> value="1" id="geodir_social_disable_auto_post" name="geodir_social_disable_auto_post"> <?php _e('Tick to disable auto post to Facebook when lisitng published. If disabled you still able to post by using "Post to Facebook" button from edit page.', 'geodir_socialimporter'); ?></label><br>
                            </fieldset>
                        </td>
                    </tr>
                <tbody>
            </table>
            <p class="submit" style="margin-top:10px;">
                <input name="gdfi_facebook_integration_options_save" class="button-primary" type="submit"
                       value="<?php _e('Save changes', 'geodir_socialimporter'); ?>"/>
                <input type="hidden" name="subtab" id="last_tab"
                       value="<?php echo sanitize_text_field($_REQUEST['subtab']);?>"/>
            </p>

        </div>
    </div>

<?php
}

function gdfi_facebook_integration_from_submit_handler() {
    if (isset($_REQUEST['gdfi_facebook_integration_options_save'])) {
        $gdfi_config = get_option('gdfi_config');
        if (!$gdfi_config) {
            $gdfi_config_new = array('app_id' => $_REQUEST['gdfi_app_id'], 'app_secret' => $_REQUEST['gdfi_app_secret']);
        } else {
            $gdfi_config_new = $gdfi_config;
        }

        if (!empty($_REQUEST['gdfi_app_id'])) {
            $gdfi_config_new['app_id'] = $_REQUEST['gdfi_app_id'];
        }
        if (!empty($_REQUEST['gdfi_app_secret'])) {
            $gdfi_config_new['app_secret'] = $_REQUEST['gdfi_app_secret'];
        }
        if (isset($_REQUEST['gdfi_app_page_post'])) {
            $gdfi_config_new['app_page_post'] = $_REQUEST['gdfi_app_page_post'];
        }
        
        $disable_post_to_fb = !empty($_REQUEST['geodir_social_disable_post_to_fb']) ? 1 : 0;
        $geodir_social_cpt_to_fb = !empty($_REQUEST['geodir_social_cpt_to_fb']) ? $_REQUEST['geodir_social_cpt_to_fb'] : '';
        $disable_auto_post = !empty($_REQUEST['geodir_social_disable_auto_post']) ? 1 : 0;
        update_option('geodir_social_disable_post_to_fb', $disable_post_to_fb);
        update_option('geodir_social_cpt_to_fb', $geodir_social_cpt_to_fb);
        update_option('geodir_social_disable_auto_post', $disable_auto_post);

        update_option('gdfi_config', $gdfi_config_new);
    }
}

function gdfi_facebook_integration_oauth()
{
    if (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'facebook_integration' && isset($_REQUEST['code'])) {

        $error_msg = __('Something went wrong', 'geodir_socialimporter');
        $gdfi_config = get_option('gdfi_config');
        $code = $_REQUEST['code'];
        $response = wp_remote_get("https://graph.facebook.com/oauth/access_token?client_id=" . $gdfi_config['app_id'] . "&redirect_uri=" . urlencode(admin_url() . "admin.php?page=geodirectory&tab=facebook_integration") . "&client_secret=" . $gdfi_config['app_secret'] . "&code=$code", array('timeout' => 15));

        if (!empty($response['response']['code']) && $response['response']['code'] == 200) {

            $rjson = json_decode($response['body']);

            if (!isset($rjson->access_token)) {

                if(isset($rjson->error) && isset($rjson->error->message)){
                    echo $error_msg.': '.$rjson->error->message;
                }else{
                    echo $error_msg;
                }
                exit;
            } else {
                $gdfi_config_new = $gdfi_config;
                $gdfi_config_new['access_token'] = $rjson->access_token;
                if (isset($rjson->expires_in)) {
                    $gdfi_config_new['access_token_expire'] = time() + $rjson->expires_in;
                } else {
                    $gdfi_config_new['access_token_expire'] = '0';
                }

                update_option('gdfi_config', $gdfi_config_new);
                ?>
                <script>window.close();</script><?php
            }
        } else {
            $rjson = json_decode($response['body']);
            if(isset($rjson->error) && isset($rjson->error->message)){
                echo $error_msg.': '.$rjson->error->message;
            }else{
                echo $error_msg;
            }

            exit;
        }
        exit;
    }
}

function gdfi_fb_at_debug()
{
    global $wpdb;
    $gdfi_config = get_option('gdfi_config');
    $url = 'https://graph.facebook.com/debug_token?input_token=' . $gdfi_config['access_token'] . '&access_token=' . $gdfi_config['access_token'];
    $result = wp_remote_get($url, array('timeout' => 15));
    print_r($result);
}

function gdfi_fb_post($msg = '', $link = '') {
    global $wpdb;
    $gdfi_config = get_option('gdfi_config');
    $page_at = gdfi_get_fb_page_accesstoken();
    if (!isset($gdfi_config['app_page_post']) || $gdfi_config['app_page_post'] == '') {
        return;
    }// if not fb page to post to bail.
    if (!isset($page_at) || $page_at == '') {
        return;
    }// if not fb page access token bail.

    $url = 'https://graph.facebook.com/' . $gdfi_config['app_page_post'] . '/feed';

    $args = array();

    $args['body']['access_token'] = $page_at;
    $args['body']['message'] = $msg;
    $args['body']['link'] = $link;
    $args['timeout'] = 30;

    $result = wp_remote_post($url, $args);
    if (is_wp_error($result)) {
        return false;
    } else if ($result['response']['code'] == '200') {
        return true;
    }
}

function gdfi_get_videos($video_url)
{
    $videos = '';
    foreach ($video_url as $video) {
        foreach ($video->format as $vid) {
            if ($vid->filter == "480x480") {
                $videos .= $vid->embed_html;
                return $videos;// only return one video, many slow down the page load
            }
        }
    }
    return $videos;
}

function gdfi_get_images($image_url)
{
    $images = array();
    foreach ($image_url as $image) {

        if (isset($image->images[0]->source)) {
            $images[] = $image->images[0]->source;
        }

    }

    if (!empty($images)) {
        return implode(",", $images);
    }
}

function gdfi_get_fb_pages($at = '')
{
    global $wpdb;
    $gdfi_config = get_option('gdfi_config');
    if (!isset($gdfi_config['access_token'])) {
        return;
    }
    $url = 'https://graph.facebook.com/me/accounts?limit=1000&access_token=' . $gdfi_config['access_token'];

    $result = wp_remote_get($url, array('timeout' => 15));
    $result_arr = json_decode($result['body']);


    $result_page_arr = '';
    if (!empty($result_arr)) {
        foreach ($result_arr->data as $fpage) {

            if ($at == $fpage->id) {
                $selected = "selected='selected'";
            } else {
                $selected = '';
            }
            $result_page_arr .= '<option ' . $selected . ' value="' . $fpage->id . '">' . $fpage->name . '</option>';
        }

        return $result_page_arr;
    }
}

function gdfi_get_fb_page_accesstoken()
{
    global $wpdb;
    $gdfi_config = get_option('gdfi_config');
    if (!isset($gdfi_config['access_token'])) {
        return;
    }
    if (!isset($gdfi_config['app_page_post'])) {
        return;
    }
    $url = 'https://graph.facebook.com/me/accounts?limit=1000&access_token=' . $gdfi_config['access_token'];

    $result = wp_remote_get($url, array('timeout' => 15));
    $result_arr = json_decode($result['body']);


    if (!empty($result_arr)) {
        foreach ($result_arr->data as $fpage) {
            if ($gdfi_config['app_page_post'] == $fpage->id) {
                return $fpage->access_token;
            }
        }

        return '';
    }
}

function gdfi_get_fb_owner($page_id)
{
    global $wpdb;
    $gdfi_config = get_option('gdfi_config');
    $url = 'https://graph.facebook.com/v2.9/' . $page_id . '?metadata=1&access_token=' . $gdfi_config['access_token'];

    $result = wp_remote_get($url, array('timeout' => 15));

    $result_arr = json_decode($result['body']);
    if ($result_arr) {
        if (!empty($result_arr->location)) {
            unset($result_arr->location);
        }
        if (!empty($result_arr->name)) {
            unset($result_arr->name);
        }
        if (!empty($result_arr->description)) {
            unset($result_arr->description);
        }
        return $result_arr;
    }
}

function gdfi_get_fb_meta($page_id, $event = false)
{
    global $wpdb;
    $gdfi_config = get_option('gdfi_config');
    $fields = gdfi_page_fields($event);
    $url = 'https://graph.facebook.com/v2.9/' . $page_id . '?metadata=1&fields=' . $fields . '&access_token=' . $gdfi_config['access_token'];

    $result = wp_remote_get($url, array('timeout' => 15));

    if (!empty($result['response']['code']) && $result['response']['code'] == 200) {
        $result_arr = json_decode($result['body']);

        if (isset($result_arr->videos->data[0]) && $result_arr->videos->data[0]) {
            $videos = gdfi_get_videos($result_arr->videos->data);
            $result_arr->videos = $videos;
            $result['body'] = json_encode($result_arr);
        }

        if (isset($result_arr->photos->data[0]) && $result_arr->photos->data[0]) {
            $photos = gdfi_get_images($result_arr->photos->data);

            if ($photos !== null) {
                $result_arr->photos = $photos;
                $result['body'] = json_encode($result_arr);
            }
        }

        if (isset($result_arr->cover->source) && $result_arr->cover->source) {
                $result_arr->photos = $result_arr->cover->source.','.$result_arr->photos;
                $result['body'] = json_encode($result_arr);
        }

        if (isset($result_arr->emails[0]) && $result_arr->emails[0]) {
            $email = $result_arr->emails[0];

            if ($email) {
                $result_arr->email = $email;
                $result['body'] = json_encode($result_arr);
            }
        }

        if (isset($result_arr->owner->id) && $result_arr->owner->id) {
            $owner = gdfi_get_fb_owner($result_arr->owner->id);
            if ($owner) {
                $result_arr = (object)array_merge((array)$result_arr, (array)$owner);
                $result['body'] = json_encode($result_arr);
            }
        }

        if ($event && isset($result_arr->place->location) && $result_arr->place->location) {
            $location = $result_arr->place->location;

            if ($location) {
                $result_arr->location = $result_arr->place->location;
                $result['body'] = json_encode($result_arr);
            }
        }

        $format = function_exists('geodir_event_field_date_format') ? geodir_event_field_date_format() : 'Y-m-d';
        if (isset($result_arr->start_time) && $result_arr->start_time) {
            $date = (array)date_create($result_arr->start_time);
            $datetime = strtotime($date['date']);
            $result_arr->event_start_date = date_i18n($format, $datetime);
            $result_arr->event_start_time = date_i18n('H:i', $datetime);
            $result['body'] = json_encode($result_arr);
        }

        if (isset($result_arr->end_time) && $result_arr->end_time) {
            $date = (array)date_create($result_arr->end_time);
            $datetime = strtotime($date['date']);
            $result_arr->event_end_date = date_i18n($format, $datetime);
            $result_arr->event_end_time = date_i18n('H:i', $datetime);            
            $result['body'] = json_encode($result_arr);
        }
        return $result['body'];
    } else {
        $result_arr = json_decode($result['body']);
        if (isset($result_arr->error->code)) {
            if ($result_arr->error->code == '100') {
                return __('Something went wrong[100], this page/event may not be public', 'geodir_socialimporter');
            } elseif ($result_arr->error->code == '104') {
                return __('Something went wrong[104], the admin must authorize this app in the backend', 'geodir_socialimporter');
            } else {
                return __('Something went wrong', 'geodir_socialimporter') . "[" . $result_arr->error->code . "]";
            }
        }

        return __('Something went wrong[111]', 'geodir_socialimporter');
    }
}

function gdfi_get_import_page_id($url)
{
    if (strpos($url, 'facebook.com/') !== false) {
        $event = false;
        if (strpos($url, '?') !== false) {
            $temp_url = explode('?', $url);
            $url = $temp_url[0];
        }
        if (strpos($url, 'facebook.com/') !== false) {
            $temp_url = explode('facebook.com/', $url);
            $url = $temp_url[1];
        }
        if (strpos($url, 'groups/') !== false) {
            $temp_url = explode('groups/', $url);
            $url = $temp_url[1];
        }
        if (strpos($url, 'pages/') !== false) {
            $temp_url = explode('pages/', $url);
            $url = $temp_url[1];
        }
        if (strpos($url, 'events/') !== false) {
            $temp_url = explode('events/', $url);
            $url = $temp_url[1];
            $event = true;
        }
        if (strpos($url, '/') !== false) {
            $temp_url = explode('/', $url);
            if (is_numeric($temp_url[1]) && strlen($temp_url[1]) > 5) {
                $url = $temp_url[1];
            } else {
                $url = $temp_url[0];
            }
        }
        // account for new facebook page url's 24/09/2015
        if (strpos($url, '-') !== false) {
            $temp_url = explode('-', $url);
            $temp_url = end($temp_url);
            if (is_numeric($temp_url) && strlen($temp_url) >= 10) {
                $url = $temp_url;
            }
        }

        echo gdfi_get_fb_meta($url, $event);
    } elseif (strpos($url, 'www.yelp.') !== false) {
        if (strpos($url, '?') !== false) {
            $temp_url = explode('?', $url);
            $url = $temp_url[0];
        }
        if (strpos($url, '/biz/') !== false) {
            $temp_url = explode('/biz/', $url);
            $url = $temp_url[1];
        }
        if (strpos($url, '#') !== false) {
            $temp_url = explode('#', $url);
            $url = $temp_url[0];
        }
        echo gdfi_yelp_get($url);
    }
}

function gd_add_listing_bottom_code() {
    if (isset($_REQUEST['pid']) && $_REQUEST['pid']) {
        return;
    } // if editing a listing then don't show
    
    $city_option = get_option('geodir_enable_city');
    $selected_cities = $city_option == 'selected' ? get_option('geodir_selected_cities') : array();
    ?>
    <script type="text/javascript">
        gdfi_codeaddress = false;
        gdfi_city = '';
        gdfi_street = '';
        gdfi_zip = '';
        // Here is a VERY basic generic trigger method
        function gdfi_triggerEvent(el, type) {
            if ((el[type] || false) && typeof el[type] == 'function') {
                el[type](el);
            }
        }

        jQuery(document).ready(function () {
            jQuery("#gd_facebook_import").click(function () {

                var gdfi_url = jQuery('#gdfi_import_url').val();
                if (!gdfi_url) {
                    alert('<?php _e('Please enter a value','geodir_socialimporter'); ?>');
                    return false;
                }
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo admin_url().'admin-ajax.php';?>",
                    data: {action: 'gdfi_get_fb_page_data', gdfi_url: gdfi_url},
                    beforeSend: function () {
                        jQuery('#modal-loading').css("visibility", "visible");
                    },
                    success: function (data) {
                        try {
                            data = jQuery.parseJSON(data);
                        }
                        catch (err) {
                            alert(data);
                            jQuery('#modal-loading').css("visibility", "hidden");
                            return
                        }
                        //if a yelp error throw it and bail.
                        if (data.hasOwnProperty('error')) {
                            alert(data.error.text);
                            jQuery('#modal-loading').css("visibility", "hidden");
                            return;
                        }

                        jQuery('#modal-loading').css("visibility", "hidden");

                        var tags = '';
                        
                        if (data.is_yelp) { // fix things for yelp
console.log(data);
                            if (!data.description && data.snippet_text) {
                                data.description = data.snippet_text;
                            }
                            if (data.categories) {
                                jQuery.each(data.categories, function (index, value) {
                                    if (tags == '') {
                                        tags = value[0];
                                    } else {
                                        tags = tags + ',' + value[0];
                                    }
                                });
                            }

                            if (data.location.address[0]) {
                                data.location.street = data.location.address[0];
                            }
                            if (data.location.postal_code) {
                                data.location.zip = data.location.postal_code;
                            }
                            if (data.display_phone) {
                                data.phone = data.display_phone;
                            }
                            if (data.image_url) {
                                data.photos = data.image_url;
                            }

                            if (data.deals && jQuery('#geodir_special_offers').length) {
                                deals_txt = '';
                                u_open = '';
                                u_close = '';
                                if (data.deals[0].url) {
                                    u_open = "<a href='" + data.deals[0].url + "' target='_blank' >";
                                    u_close = "</a>";
                                }
                                if (data.deals[0].title) {
                                    deals_txt = u_open + "<h2>" + data.deals[0].title + "</h2>" + u_close;
                                }
                                if (data.deals[0].what_you_get) {
                                    deals_txt = deals_txt + data.deals[0].what_you_get;
                                }
                                if (data.deals[0].image_url) {
                                    deals_txt = deals_txt + "<img s" + "rc='" + data.deals[0].image_url + "' />";
                                }
                                jQuery('#geodir_special_offers').val(deals_txt);
                            }
                            if (data.url && jQuery('#geodir_website').length) {
                                data.website = data.url;
                            }
                        }

                        // Standard facebook
                        //enforce desc maxlength
                        if (data.description && jQuery('#post_desc').attr('maxlength')) {
                            descMax = jQuery('#post_desc').attr('maxlength');
                            if (data.description.length > descMax) {
                                data.description = data.description.substring(0, descMax);
                            }
                        }

                        if (data.name && jQuery('#post_title').length) {
                            jQuery('#post_title').val(data.name);
                            jQuery("#post_title").change();
                        }
                        if (data.description && jQuery('#post_desc').length) {
                            jQuery('#post_desc').val(data.description);
                            jQuery('#post_desc').change();
                        }
                        if (typeof(tinyMCE) != "undefined") {
                            if (data.description && tinyMCE.get('post_desc')) {
                                tinyMCE.get('post_desc').setContent(data.description.replace(/\n/g, "<br />"));
                                jQuery('#post_desc').change();
                            }
                        }
                        if (!data.description && data.about && jQuery('#post_desc').length) {
                            jQuery('#post_desc').val(data.about);
                        }
                        if (data.category_list && jQuery('#post_tags').length) {
                            jQuery.each(data.category_list, function (index, value) {
                                if (tags == '') {
                                    tags = value.name;
                                } else {
                                    tags = tags + ',' + value.name;
                                }
                            });
                        }
                        else if (data.category && jQuery('#post_tags').length) {
                            if (tags == '') {
                                tags = data.category
                            } else {
                                tags = tags + ',' + data.category;
                            }
                        }

                        // hack for events location
                        if (data.venue) {
                            data.location = data.venue;
                        }

                        if (data.location && data.location.street && jQuery('#post_address').length) {
                            jQuery('#post_address').val(data.location.street);

                            // reset the regions and city
                            jQuery('#post_region').val('');
                            jQuery("#post_region").trigger("chosen:updated");
                            jQuery('#post_city').val('');
                            jQuery("#post_city").trigger("chosen:updated");
                        }

                        if (data.location && data.location.latitude && data.location.longitude) {
                            if (window.gdMaps == 'google') {
                                latlon = new google.maps.LatLng(data.location.latitude, data.location.longitude);
                                jQuery.goMap.map.setCenter(latlon);
                                updateMarkerPosition(latlon);
                                centerMarker();
                                if (!data.location.street) {
                                    google.maps.event.trigger(baseMarker, 'dragend');
                                } // geocode address only if no street name present
                            } else if (window.gdMaps == 'osm') {
                                latlon = new L.latLng(data.location.latitude, data.location.longitude);
                                jQuery.goMap.map.setView(latlon, jQuery.goMap.map.getZoom());
                                updateMarkerPositionOSM(latlon);
                                centerMarker();
                                if (!data.location.street) {
                                    baseMarker.fireEvent('moveend');
                                } // geocode address only if no street name present
                            }
                        }

                        if (data.location && data.location.country && jQuery('#post_country').length) {
                            jQuery('#post_country').val(data.location.country);
                            /*jQuery("#post_country").trigger('change');*/
                            jQuery("#post_country").trigger("chosen:updated");
                        }
                        if (data.location && (location_region = data.location.state) && jQuery('#post_region').length) {
                            location_region = location_region.replace(/'/g, "\\'");
                            if (jQuery("#post_region option[value='" + location_region + "']").length > 0) {
                                jQuery('#post_region').val(data.location.state);
                                jQuery("#post_region").trigger("chosen:updated");
                            } else {
                                jQuery('#post_region').append(jQuery('<option>', {
                                    value: data.location.state,
                                    text: data.location.state
                                }));
                                jQuery('#post_region').val(data.location.state);
                                jQuery("#post_region").trigger("chosen:updated");
                            }
                        }
                        if (data.location && (location_city = data.location.city) && jQuery('#post_city').length) {
                            location_city = location_city.replace(/'/g, "\\'");
                            if (jQuery("#post_city option[value='" + location_city + "']").length > 0) {
                                jQuery('#post_city').val(data.location.city);
                                jQuery("#post_city").trigger("chosen:updated");
                            } else {
                                <?php if (!empty($selected_cities) && is_array($selected_cities)) { ?>
                                var city_array = <?php echo json_encode($selected_cities); ?>;
                                <?php } ?>
                                if (typeof city_array !== 'undefined') {
                                    console.log(city_array);
                                } else {
                                    jQuery('#post_city').append(jQuery('<option>', {
                                        value: data.location.city,
                                        text: data.location.city
                                    }));
                                    jQuery('#post_city').val(data.location.city);
                                    jQuery("#post_city").trigger("chosen:updated");
                                }
                            }
                        }

                        if (data.location && data.location.zip && jQuery('#post_zip').length) {
                            jQuery('#post_zip').val(data.location.zip);
                        }

                        if (data.location && data.location.country && data.location.city && data.location.zip && data.location.street) {
                            gdfi_codeaddress = true;
                            gdfi_city = data.location.city;
                            gdfi_street = data.location.street;
                            gdfi_zip = data.location.zip;
                            geodir_codeAddress(true);
                            setTimeout(function () {
                                if (window.gdMaps == 'google') {
                                    google.maps.event.trigger(baseMarker, 'dragend');
                                } else if (window.gdMaps == 'osm') {
                                    baseMarker.fireEvent('moveend');
                                }
                            }, 600);

                            //incase the drag marker changes the street and post code we should fix it.
                            setTimeout(function () {
                                if (data.location && data.location.street && jQuery('#post_address').length) {
                                    jQuery('#post_address').val(data.location.street);
                                }
                                if (data.location && data.location.zip && jQuery('#post_zip').length) {
                                    jQuery('#post_zip').val(data.location.zip);
                                }
                            }, 1000);
                        }

                        if (data.phone && jQuery('#geodir_contact').length) {
                            jQuery('#geodir_contact').val(data.phone);
                        }
                        if (data.email && jQuery('#geodir_email').length) {
                            jQuery('#geodir_email').val(data.email);
                        }
                        else if (data.press_contact && jQuery('#geodir_email').length) {
                            jQuery('#geodir_email').val(data.press_contact);
                        }
                        if (data.website && jQuery('#geodir_website').length) {
                            jQuery('#geodir_website').val(data.website);
                        }
                        if (data.twitter && jQuery('#geodir_twitter').length) {
                            jQuery('#geodir_twitter').val(data.twitter);
                        }
                        if (data.link && jQuery('#geodir_facebook').length) {
                            jQuery('#geodir_facebook').val(data.link);
                        }
                        if (data.videos && jQuery('#geodir_video').length) {
                            jQuery('#geodir_video').val(data.videos);
                        }

                        if (data.photos && jQuery('#post_images').length && jQuery('#post_imagesimage_limit').length && jQuery('#post_imagesimage_limit').val() != '') {
                            var iLimit = jQuery('#post_imagesimage_limit').val();
                            var iArray = data.photos.split(",");
                            if (iArray.length > iLimit) {
                                iArray = iArray.slice(0, iLimit);
                                data.photos = iArray.join();
                            }
                        }
                        if (data.photos && jQuery('#post_images').length) {
                            jQuery('#post_images').val(data.photos);
                            plu_show_thumbs('post_images');
                        }

                        // facebook Events
                        if (data.owner && data.owner.category_list && jQuery('#post_tags').length) {
                            jQuery.each(data.owner.category_list, function (index, value) {
                                if (tags == '') {
                                    tags = value.name;
                                } else {
                                    tags = tags + ',' + value.name;
                                }
                            });
                        }
                        else if (data.owner && data.owner.category && jQuery('#post_tags').length) {
                            if (tags == '') {
                                tags = data.owner.category
                            } else {
                                tags = tags + ',' + data.owner.category;
                            }
                        }

                        if (data.event_start_date && jQuery('#event_start').length) {
                            if (jQuery('#dates').length) {
                                jQuery('#dates').val(data.event_start_date);
                            }
                            if (data.event_start_date) jQuery('#event_start').val(data.event_start_date);
                            if (data.event_end_date) jQuery('#event_end').val(data.event_end_date);

                            if (jQuery('#dates').length) {
                                var event_start_msec = Date.parse(data.event_start_date);
                                var js_start_date = new Date(event_start_msec );

                                cal.select(js_start_date);
                                var selectedDates = cal.getSelectedDates();
                                if (selectedDates.length > 0) {
                                    var firstDate = selectedDates[0];
                                    cal.cfg.setProperty("pagedate", (firstDate.getMonth() + 1) + "/" + firstDate.getFullYear());
                                    cal.render();
                                }
                            }
                        }

                        if (data.event_start_time && jQuery('#starttime').length) {
                            jQuery('#starttime').val(data.event_start_time);
                            jQuery("#starttime").trigger("chosen:updated");
                        }
                        if (data.event_end_time && jQuery('#starttime').length) {
                            jQuery('#endtime').val(data.event_end_time);
                            jQuery("#endtime").trigger("chosen:updated");
                        }

                        // populate tags last
                        if (tags && jQuery('#post_tags').length) {
                            jQuery('#post_tags').val(tags);
                        }

                        //enforce tags maxlength
                        if (jQuery('#post_tags').length && jQuery('#post_tags').attr('maxlength')) {
                            tagsMax = jQuery('#post_tags').attr('maxlength');
                            if (jQuery('#post_tags').val().length > tagsMax) {
                                jQuery('#post_tags').val(jQuery('#post_tags').val().substring(0, tagsMax));
                            }
                        }
                    }
                });

                return false;
            });
        });
    </script>
<?php
}

function gd_add_listing_top_code()
{
    if (isset($_REQUEST['pid']) && $_REQUEST['pid']) {
        return;
    }// if editing a listing then don't show
    ?>
    <h5><?php _e('Import Details from Social', 'geodir_socialimporter'); ?></h5>
    <input type="text" placeholder="<?php _e('Enter facebook page/event url or Yelp url', 'geodir_socialimporter'); ?>"
           id="gdfi_import_url"/>
    <button id="gd_facebook_import" style="margin-top:0px;"
          class="geodir_button"><?php _e('Import Details', 'geodir_socialimporter'); ?></button>
    <div id="modal-loading" style="margin:0px;display:inline-block;visibility:hidden;"><i
            class="fa fa-refresh fa-spin"></i></div>

<?php
}

add_action('geodir_before_detail_fields', 'gd_add_listing_top_code', 3);
add_action('geodir_after_main_form_fields', 'gd_add_listing_bottom_code', 10);

function gdfi_post_to_facebook($new_status, $old_status, $post)
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'geodir_import_export') {
        return;
    }//dont post imports from csv to facebook

    if ($old_status != 'publish' && $new_status == 'publish') {
        if (get_post_meta($post->ID, 'gdfi_posted_facebook', true)) {
            return;
        }// if posted to facebook don't post again.
        if (geodir_social_cpt_post_to_facebook($post->post_type) && !get_option('geodir_social_disable_auto_post')) {
            global $wpdb;
            $to_post = get_option('gdfi_post_to_facebook');
            $to_post[$post->ID] = array('ID' => $post->ID, 'title' => $post->post_title);
            update_option('gdfi_post_to_facebook', $to_post);

            update_post_meta($post->ID, 'gdfi_posted_facebook', '1'); // mark it as posted to facebook
        }
    }
}
add_action('transition_post_status', 'gdfi_post_to_facebook', 5000, 3);

function gdfi_check_post_fb()
{
    global $wpdb;
    if ($posts = get_option('gdfi_post_to_facebook')) {
        foreach ($posts as $p) {
            $permalink = get_permalink($p['ID']);
            if (geodir_social_cpt_post_to_facebook(get_post_type($p['ID']))) {
                gdfi_fb_post($p['title'], $permalink); // post to facebook
            }
        }
        delete_option('gdfi_post_to_facebook');
    }
}

if (isset($_REQUEST['pid']) || isset($_REQUEST['post'])) {
    add_action('shutdown', 'gdfi_check_post_fb', 5000);
}

function gdfi_get_fb_page_data()
{
//do something
    gdfi_get_import_page_id($_POST['gdfi_url']);
    gd_die();
}
add_action('wp_ajax_gdfi_get_fb_page_data', 'gdfi_get_fb_page_data');

function gdfi_add_listing_codeaddress_js_vars_change()
{
    if (isset($_REQUEST['pid']) && $_REQUEST['pid']) {
        return;
    }// if editing a listing then don't show
    ?>
    if(gdfi_codeaddress==true){
    city = gdfi_city;
    region = '';
    zip = gdfi_zip;
    address = gdfi_street;
    address = '';
    address = address + ',' + zip + ',' + city + ',' + country;
    }
<?php
}
add_action('geodir_add_listing_codeaddress_js_vars', 'gdfi_add_listing_codeaddress_js_vars_change', 10);

function gdfi_add_listing_geocode_js_vars_change()
{
    if (isset($_REQUEST['pid']) && $_REQUEST['pid']) {
        return;
    }// if editing a listing then don't show
    if (!is_admin()) {
        ?>
        if(gdfi_codeaddress==true){
        getAddress = gdfi_street;
        gdfi_codeaddress=false;
        }
    <?php }
}
add_action('geodir_add_listing_geocode_js_vars', 'gdfi_add_listing_geocode_js_vars_change', 10);

function gdfi_page_fields($event = false)
{
    $fields = array();
    if (!$event) {
        $fields[] = 'about'; // about info
        $fields[] = 'name'; // page name
        $fields[] = 'description'; // page description
        $fields[] = 'category_list'; // category array
        $fields[] = 'category'; // main category name
        $fields[] = 'location'; // page location info
        $fields[] = 'phone'; // phone number
        $fields[] = 'emails'; // emails NEED TO CAHNGE THIS FROM ARRAY TO STRING ######################################
        $fields[] = 'press_contact'; // backup check for email
        $fields[] = 'website'; // website url
        $fields[] = 'link'; // page link
        /* 20171109 Bank Close video import */
        /*$fields[] = 'videos{format}'; // page videos*/
        $fields[] = 'photos{images}'; // page photos
        $fields[] = 'cover'; // cover image
    } else {
        $fields[] = 'name'; // page name
        $fields[] = 'description'; // page description
        $fields[] = 'category'; // main category name
        $fields[] = 'place'; // page location info
        $fields[] = 'photos{images}'; // page photos

        // events
        $fields[] = 'owner'; // events owner
        $fields[] = 'start_time'; // event start date
        $fields[] = 'end_time'; // event end date
        $fields[] = 'cover'; // cover image

    }
    return implode(",", $fields);
}
// add tool to clear any post to FB que

add_action('geodir_diagnostic_tool', 'geodir_add_post_to_facebook_clear_tool', 1);
function geodir_add_post_to_facebook_clear_tool()
{
    ?>
    <tr>
        <td><?php _e('Clear post to facebook', 'geodir_socialimporter');?></td>
        <td>
            <small><?php _e('Clear the option that stores the social importer, post to facebook post IDs', 'geodir_socialimporter');?></small>
        </td>
        <td><input type="button" value="<?php _e('Run', 'geodir_socialimporter');?>"
                   class="button-primary geodir_diagnosis_button" data-diagnose="run_clear_ptfb"/>
        </td>
    </tr>
<?php
}

function geodir_diagnose_run_clear_ptfb()
{
    global $wpdb, $plugin_prefix;

    $output_str = '';

    update_option('gdfi_post_to_facebook', '');
    $output_str .= "<li>" . __('Done', 'geodir_socialimporter') . "</li>";

    $info_div_class = "geodir_noproblem_info";
    $fix_button_txt = '';

    echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";

}

add_action('post_submitbox_misc_actions', 'geodir_post_to_facebook_edit_page', 200);
function geodir_post_to_facebook_edit_page() {
    global $post;
    
    if (!empty($post->ID) && !empty($post->post_type) && geodir_social_cpt_post_to_facebook($post->post_type)) {
        $posted_facebook = '';
        $posted_facebook_msg = __('Post to facebook', 'geodir_socialimporter');
        if (get_post_meta($post->ID, 'gdfi_posted_facebook', true)) {
            $posted_facebook_msg = __('Re-post to facebook', 'geodir_socialimporter');
            $posted_facebook = 'gdfi-posted';
        }
        ?>
        <div class="misc-pub-section  misc-pub-social-importer-post-to-facebook">
            <div title="No focus keyword set."
                 class="gdfi-edit-post-fb-icon fa fa-facebook-official <?php echo $posted_facebook; ?>"></div>
            GD:
            <span onclick="gdfi_post_fb_ajax();"
                  class="geodir-posted-facebook-btn button button-primary button-small"><?php echo $posted_facebook_msg; ?></span>
            <span style="display: none" class="fa fa-spinner fa-spin gdfi-posting-wait"></span>
        </div>
        <style>
            .gdfi-edit-post-fb-icon {
                font-size: 16px;
                color: #888;
                padding-right: 8px;
            }
            .gdfi-edit-post-fb-icon.gdfi-posted {
                color: blue;
            }
        </style>
        <script type="text/javascript">
            gdfi_sending_post = false;

            function gdfi_post_fb_ajax() {
                if (gdfi_sending_post) {
                    alert("<?php  _e('Currently posting, please wait!', 'geodir_socialimporter'); ?>");
                    return;
                }
                var data = {
                    'action': 'gdfi_post_to_facebook_ajax',
                    'post_id': <?php echo $post->ID;?>,
                    'security': '<?php echo wp_create_nonce( "gdfi-ajax-nonce" ); ?>'
                };

                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'html',
                    data: data,
                    beforeSend: function () {
                        gdfi_sending_post = true;
                        jQuery('.gdfi-posting-wait').show();
                    },
                    success: function (data, textStatus, xhr) {
                        jQuery('.gdfi-posting-wait').hide();
                        if (data == '2') {
                            alert("<?php  _e('Please publish this post first or you might break the internet!', 'geodir_socialimporter'); ?>");
                        } else if (data == '1') {
                            jQuery('.gdfi-edit-post-fb-icon').addClass('gdfi-posted');
                            jQuery('.geodir-posted-facebook-btn').html('<?php _e('Re-post to facebook', 'geodir_socialimporter');?>');
                            alert("<?php  _e('Post posted to facebook!', 'geodir_socialimporter'); ?>");
                        } else {
                            alert("<?php  _e('Something went wrong while posting to facebook!', 'geodir_socialimporter'); ?>")
                        }
                        gdfi_sending_post = false;
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        jQuery('.gdfi-posting-wait').hide();
                        alert(textStatus);
                        gdfi_sending_post = false;
                    }
                });
            }
            ;
        </script>
    <?php
    }
}

add_action('wp_ajax_gdfi_post_to_facebook_ajax', 'gdfi_post_to_facebook_ajax');
function gdfi_post_to_facebook_ajax()
{
    check_ajax_referer('gdfi-ajax-nonce', 'security');

    if (isset($_POST['post_id']) && $_POST['post_id']) {
        $post_id = $_POST['post_id'];
    } else {
        echo '0';
        die();
    }

    if (get_post_status($post_id) != 'publish') {
        echo '2';
        die();
    }
    
    $post_type = get_post_type($post_id);
    if (!geodir_social_cpt_post_to_facebook($post_type)) {
        echo '0'; // Bail!
        die();
    }

    $permalink = get_permalink($post_id);
    $title = html_entity_decode( get_the_title($post_id), ENT_COMPAT, 'UTF-8' );
    // post to facebook
    if (gdfi_fb_post($title, $permalink)) {
        update_post_meta($post_id, 'gdfi_posted_facebook', '1'); // mark it as posted to facebook
        echo '1';
    } else {
        echo '0';
    }
    die();
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.2.4
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_social_importer_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}

/**
 *
 * @since 1.2.6
 *
 */
function geodir_social_post_types_allowed() {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    
    $not_allowed = array('page', 'attachment', 'revision', 'nav_menu_item', 'wpi_invoice');
    $not_allowed = apply_filters('geodir_social_post_types_not_allowed', $not_allowed);
    
    $allowed = array();
    if (!empty($post_types)) {
        $gd_post_types = geodir_get_posttypes();
        
        foreach ($post_types as $post_type => $object) {
            if (!empty($object->labels->name) && !in_array($post_type, $not_allowed)) {
                $allowed[$post_type] = in_array($post_type, $gd_post_types) ? __($object->labels->name, 'geodirectory') : __($object->labels->name);
            }
        }
    }
    
    return apply_filters('geodir_social_post_types_allowed', $allowed);
}

/**
 *
 * @since 1.2.6
 *
 */
function geodir_social_cpt_post_to_facebook($post_type = '') {
    $return = false;
    
    if (empty($post_type)) {
        return $return;
    }
    
    $allowed_cpt = geodir_social_post_types_allowed();
    
    if (!isset($allowed_cpt[$post_type]) || (int)get_option('geodir_social_disable_post_to_fb')) {
        return $return;
    }
    
    $post_types = get_option('geodir_social_cpt_to_fb');
    if (empty($post_types) || (!empty($post_types) && in_array($post_type, $post_types))) {
        $return = true;
    }

    return apply_filters('geodir_social_cpt_post_to_facebook', (bool)$return);
}