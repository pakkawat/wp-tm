<?php

//
// From http://non-diligent.com/articles/yelp-apiv2-php-example/
//


// if class exists bail
if (class_exists('GeodirYelpAuthException')) {
    //do nothing
} else {
    // Enter the path that the oauth library is in relation to the php file
    require_once('inc/yelp-oauth.php');
}


function gdfi_yelp_example()
{
// For example, request business with id 'the-waterboy-sacramento'
    $unsigned_url = "http://api.yelp.com/v2/business/the-waterboy-sacramento";

    global $wpdb;
    $gdfi_config_yelp = get_option('gdfi_config_yelp');
// Set your keys here
    $consumer_key = $gdfi_config_yelp['key'];
    $consumer_secret = $gdfi_config_yelp['key_secret'];
    $token = $gdfi_config_yelp['token'];
    $token_secret = $gdfi_config_yelp['token_secret'];

// Token object built using the OAuth library
    $token = new GeodirYelpAuthToken($token, $token_secret);

// Consumer object built using the OAuth library
    $consumer = new GeodirYelpAuthConsumer($consumer_key, $consumer_secret);

// Yelp uses HMAC SHA1 encoding
    $signature_method = new GeodirYelpAuthSignatureMethod_HMAC_SHA1();

// Build OAuth Request using the OAuth PHP library. Uses the consumer and token object created above.
    $oauthrequest = GeodirYelpAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $unsigned_url);

// Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);

// Get the signed URL
    $signed_url = $oauthrequest->to_url();

// Send Yelp API Call
    $ch = curl_init($signed_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch); // Yelp response
    curl_close($ch);

// Handle Yelp response data
    $response = json_decode($data);

// Print it for debugging
    print_r($response);
}


function gdfi_yelp_get($page_id)
{
    $gdfi_config_yelp = get_option('gdfi_config_yelp');
    
    if(isset($gdfi_config_yelp['app_id']) && $gdfi_config_yelp['app_id']){
        return gdfi_yelp_get_v3($page_id);
    }
    
// For example, request business with id 'the-waterboy-sacramento'
    $aip_url = "http://api.yelp.com/v2/business/";
    $unsigned_url = $aip_url . $page_id;


    global $wpdb;
   
// Set your keys here
    $consumer_key = $gdfi_config_yelp['key'];
    $consumer_secret = $gdfi_config_yelp['key_secret'];
    $token = $gdfi_config_yelp['token'];
    $token_secret = $gdfi_config_yelp['token_secret'];

// Token object built using the OAuth library
    $token = new GeodirYelpAuthToken($token, $token_secret);

// Consumer object built using the OAuth library
    $consumer = new GeodirYelpAuthConsumer($consumer_key, $consumer_secret);

// Yelp uses HMAC SHA1 encoding
    $signature_method = new GeodirYelpAuthSignatureMethod_HMAC_SHA1();

// Build OAuth Request using the OAuth PHP library. Uses the consumer and token object created above.
    $oauthrequest = GeodirYelpAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $unsigned_url);

// Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);

// Get the signed URL
    $signed_url = $oauthrequest->to_url();

// Send Yelp API Call
    $ch = curl_init($signed_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch); // Yelp response
    curl_close($ch);

// Handle Yelp response data
    $response = json_decode($data);//print_r($response);
    if ($response && !isset($response->error)) {
        $response->is_yelp = true;
        $country_name = $wpdb->get_var($wpdb->prepare("SELECT Country FROM " . GEODIR_COUNTRIES_TABLE . " WHERE ISO2=%s", array($response->location->country_code)));
        $response->location->country = $country_name;
        $response->image_url = str_replace("ms.jpg", "l.jpg", $response->image_url);
    } elseif (is_object($response)) {
        $response->is_yelp = true;
    }
// Print it for debugging
    return json_encode($response);
}

function gdfi_yelp_get_v3($page_id){
    $gdfi_config_yelp = get_option('gdfi_config_yelp');

    global $wpdb;

// Set your keys here
    $app_id = $gdfi_config_yelp['app_id'];
    $app_secret = $gdfi_config_yelp['app_secret'];


// Token object built using the OAuth library
    $yelp = new GDYelp($app_id, $app_secret);
//
//    print_r($yelp);
//
//    echo '###'.$page_id;

    $business = $yelp->business($page_id);
   // print_r($business);
   // exit;




// Handle Yelp response data
    $response = $business;//print_r($response);
    if ($response && !isset($response['error'])) {
        $response['is_yelp'] = true;
        $country_name = $wpdb->get_var($wpdb->prepare("SELECT Country FROM " . GEODIR_COUNTRIES_TABLE . " WHERE ISO2=%s", array($response['location']['country'])));
        $response['location']['country'] = $country_name;
        $response['image_url'] = false;
        $response['location']['address'] = $response['location']['display_address'];
        $response['location']['postal_code'] = $response['location']['zip_code'];
        $response['location']['coordinates'] = $response['coordinates'];
        $response['categories'][0] = array_values($response['categories'][0]);

    } elseif (is_array($response)) {
        $response['is_yelp'] = true;
    }
//// Print it for debugging
    return json_encode($response);
}


function gdfi_yelp_integration_setting_fields()
{
    global $wpdb;

    ?>

    <div class="inner_content_tab_main">
        <div class="gd-content-heading active">

            <h3><?php _e('Enter your Yelp API V3 (fusion) settings', 'geodir_socialimporter'); ?> <a
                    href="https://www.yelp.co.uk/developers/v3/manage_app" target="_blank"><?php _e('Find them here', 'geodir_socialimporter'); ?></a>
                <br /><small><?php _e('Filling out the V3 settings will mean they will be used instead of the depreciated V2 settings.', 'geodir_socialimporter'); ?></small>
            </h3>
            <?php
            //gdfi_yelp_example();

            ?>


            <table class="form-table">
                <?php
                $gdfi_config_yelp = get_option('gdfi_config_yelp');
                ?>
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('App ID', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_yelp_app_id" id="gdfi_yelp_app_id" type="text" style=" min-width:300px;"
                               value="<?php if (!empty($gdfi_config_yelp['app_id'])) {
                                   echo $gdfi_config_yelp['app_id'];
                               }?>">
                        <span
                            class="description"><?php _e('Enter your Yelp App ID', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('App Secret', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_yelp_app_secret" id="gdfi_yelp_app_secret" type="password"
                               style=" min-width:300px;" value="<?php if (!empty($gdfi_config_yelp['app_secret'])) {
                            echo $gdfi_config_yelp['app_secret'];
                        }?>">
                        <span
                            class="description"><?php _e('Enter your Yelp App Secret', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                </tbody>
            </table>


            <h3><?php _e('Enter your Yelp API V2 settings (DEPRECIATED Legacy Settings)', 'geodir_socialimporter'); ?> <a
                    href="http://www.yelp.co.uk/developers/manage_api_keys" target="_blank"><?php _e('Find them here', 'geodir_socialimporter'); ?></a>
            </h3>
            <?php
            //gdfi_yelp_example();
            //gdfi_yelp_get('savoy-cafe-down');
            ?>


            <table class="form-table" style="background-color: pink;">
                <?php
                $gdfi_config_yelp = get_option('gdfi_config_yelp');
                ?>
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Consumer Key', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_yelp_key" id="gdfi_yelp_key" type="text" style=" min-width:300px;"
                               value="<?php if (!empty($gdfi_config_yelp['key'])) {
                                   echo $gdfi_config_yelp['key'];
                               }?>">
                        <span
                            class="description"><?php _e('Enter your Yelp Consumer Key', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Consumer Secret', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_yelp_key_secret" id="gdfi_yelp_key_secret" type="password"
                               style=" min-width:300px;" value="<?php if (!empty($gdfi_config_yelp['key_secret'])) {
                            echo $gdfi_config_yelp['key_secret'];
                        }?>">
                        <span
                            class="description"><?php _e('Enter your Yelp Consumer Secret', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Token', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_yelp_token" id="gdfi_yelp_token" type="text" style=" min-width:300px;"
                               value="<?php if (!empty($gdfi_config_yelp['token'])) {
                                   echo $gdfi_config_yelp['token'];
                               }?>">
                        <span class="description"><?php _e('Enter your Yelp Token', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Token Secret', 'geodir_socialimporter'); ?></th>
                    <td class="forminp">
                        <input name="gdfi_yelp_token_secret" id="gdfi_yelp_token_secret" type="password"
                               style=" min-width:300px;" value="<?php if (!empty($gdfi_config_yelp['token_secret'])) {
                            echo $gdfi_config_yelp['token_secret'];
                        }?>">
                        <span
                            class="description"><?php _e('Enter your Yelp Token Secret', 'geodir_socialimporter'); ?></span>
                    </td>
                </tr>


                </tbody>
            </table>

            <p class="submit" style="margin-top:10px;">
                <input name="gdfi_yelp_integration_options_save" class="button-primary" type="submit"
                       value="<?php _e('Save changes', 'geodir_socialimporter'); ?>"/>
                <input type="hidden" name="subtab" id="last_tab"
                       value="<?php echo sanitize_text_field($_REQUEST['subtab']);?>"/>
            </p>

        </div>
    </div>

<?php

}

function gdfi_yelp_integration_from_submit_handler()
{
    global $wpdb;
    if (isset($_REQUEST['gdfi_yelp_integration_options_save'])) {

        $gdfi_config_yelp = get_option('gdfi_config_yelp');
        if (!$gdfi_config_yelp) {
            $gdfi_config_new = array('app_id' => $_REQUEST['gdfi_yelp_app_id'],'app_secret' => $_REQUEST['gdfi_yelp_app_secret'],'key' => $_REQUEST['gdfi_yelp_key'], 'key_secret' => $_REQUEST['gdfi_yelp_key_secret'], 'token' => $_REQUEST['gdfi_yelp_token'], 'token_secret' => $_REQUEST['gdfi_yelp_token_secret']);
        } else {
            $gdfi_config_new = $gdfi_config_yelp;
        }


        if (!empty($_REQUEST['gdfi_yelp_app_id'])) {
            $gdfi_config_new['app_id'] = $_REQUEST['gdfi_yelp_app_id'];
        }
        if (!empty($_REQUEST['gdfi_yelp_app_secret'])) {
            $gdfi_config_new['app_secret'] = $_REQUEST['gdfi_yelp_app_secret'];
        }
        if (!empty($_REQUEST['gdfi_yelp_key'])) {
            $gdfi_config_new['key'] = $_REQUEST['gdfi_yelp_key'];
        }
        if (!empty($_REQUEST['gdfi_yelp_key_secret'])) {
            $gdfi_config_new['key_secret'] = $_REQUEST['gdfi_yelp_key_secret'];
        }
        if (!empty($_REQUEST['gdfi_yelp_token'])) {
            $gdfi_config_new['token'] = $_REQUEST['gdfi_yelp_token'];
        }
        if (!empty($_REQUEST['gdfi_yelp_token_secret'])) {
            $gdfi_config_new['token_secret'] = $_REQUEST['gdfi_yelp_token_secret'];
        }
        update_option('gdfi_config_yelp', $gdfi_config_new);

    }

}