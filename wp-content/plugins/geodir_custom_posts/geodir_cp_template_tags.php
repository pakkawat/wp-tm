<?php
function geodir_custom_post_type_script() {
    wp_enqueue_script( 'custom_post_type_js', plugins_url( 'js/script.js' , __FILE__ ));
}

function geodir_cp_listing() {
    if (isset($_REQUEST['confirm']) && $_REQUEST['confirm'] == 'true' && isset($_REQUEST['geodir_customposttype']) && $_REQUEST['geodir_customposttype'] != '') {
        $cpt = sanitize_text_field($_REQUEST['geodir_customposttype']);
    ?>
    <div id="message" class="updated fade"><p><strong>Click to <a href="<?php echo geodir_cp_ajax_url();?>&geodir_deleteposttype=<?php echo $cpt; ?>"><?php _e('Delete','geodir_custom_posts'); ?></a></strong> all posts of (<?php echo $cpt; ?>) post type. If you don't delete posts of this post type now and create a same name post types in future then all the posts will be assigned to (<?php echo $cpt; ?>) post type again.</strong></p></div>
    <?php } ?>
    <div class="inner_content_tab_main">
        <div class="gd-content-heading active">
            <h3><?php _e('Manage Custom Post Types','geodir_custom_posts'); ?></h3>
            <p style="padding-left:15px;"><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_addedit'?>"><strong><?php _e('Add Post Type','geodir_custom_posts'); ?></strong></a></p>
            <table cellpadding="5" class="widefat post fixed" >
                <thead>
                    <tr>
                        <th width="120" align="left"><strong><?php _e('Post Type','geodir_custom_posts'); ?></strong></th>
                        <th width="120" align="left"><strong><?php _e('Listing Slug','geodir_custom_posts'); ?></strong></th>
                        <th width="240" align="left"><strong><?php _e('Taxonomies','geodir_custom_posts'); ?></strong></th>
                        <th width="60" align="left"><strong><?php _e('Image','geodir_custom_posts'); ?></strong></th>
                        <th width="100" align="left"><strong><?php _e('Can Export','geodir_custom_posts'); ?> </strong></th>
                        <th width="70" align="left"><strong><?php _e('Edit','geodir_custom_posts'); ?></strong></th>
                        <th width="70" align="left"><strong><?php _e('Delete','geodir_custom_posts'); ?></strong></th>
                    </tr>
                <?php
                $geodir_post_types = get_option( 'geodir_post_types' );
                $total_zero_count = 0;
                
                $geodir_post_types_in_new_order = array();
                $geodir_temp_post_types = array();
                $geodir_temp_post_type_keys = array();
                
                foreach ($geodir_post_types as $key => $value) {
                    if (!empty($geodir_temp_post_types)) {
                        if (!isset($value['listing_order']) || $value['listing_order'] == 0) {
                            $total_zero_count++;
                        }
                        
                        if (!isset($value['listing_order']) || $value['listing_order'] == 0 || array_key_exists($value['listing_order'], $geodir_temp_post_types))
                            $value['listing_order'] = max(array_keys($geodir_temp_post_types)) + 1;
                    } else {
                        if (!isset($value['listing_order']) || $value['listing_order'] == 0) {
                            $value['listing_order'] = 1;
                            $total_zero_count++;
                        }
                    }
                    $geodir_temp_post_types[$value['listing_order']] = $value;
                    $geodir_temp_post_type_keys[$value['listing_order']] = $key;
                }
                
                ksort($geodir_temp_post_types);
                
                foreach ($geodir_temp_post_types as $key => $value) {
                    $geodir_post_types_in_new_order[$geodir_temp_post_type_keys[$key]] = $value;
                }
                
                if ($total_zero_count == count($geodir_post_types_in_new_order)) {
                    update_option('geodir_post_types', $geodir_post_types_in_new_order);
                }
                
                foreach($geodir_post_types as $key => $value) { 
                    $cpt_image = get_option('geodir_cpt_img_' . $key);
                    ?>
                    <tr>
                        <td><?php echo $key;?></td>
                        <td><?php echo $value['has_archive'];?></td>
                        <td> <?php if (!empty($value['taxonomies'])) { echo implode(', ', $value['taxonomies']); }?></td>
                        <td><?php if ($cpt_image != '') { ?><a target="_blank" href="<?php echo $cpt_image;?>"><img src="<?php echo $cpt_image;?>" class="geodir-cpt-img" style="width:45px" /></a><?php } ?></td>
                        <td><?php if ($value['can_export']) { _e('Yes','geodir_custom_posts'); } else { _e('No','geodir_custom_posts'); } ?></td>
                        <td><a href="<?php echo admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_addedit&posttype='.$key; ?>"><?php _e('Edit','geodir_custom_posts'); ?></a></td>
                        <td>
                            <?php if (isset($value['is_custom']) && $value['is_custom'] != '') { ?>
                            <a class="delete_posttype" package_id="<?php echo $key;?>" href="javascript:void(0);"><?php _e('Delete','geodir_custom_posts'); ?></a>
                            <?php } else { echo "&nbsp;"; } ?>
                        </td>
                    </tr>
                <?php } ?>
                </thead>
            </table>
        </div>
    </div>
    <script type="text/javascript" language="javascript">
    jQuery(".delete_posttype").click(function() {
        var posttype = jQuery(this).attr('package_id');
        var confirm_post = confirm('<?php echo addslashes(__('Are you wish to delete this Post Type?', 'geodir_custom_posts')); ?>');
        if (confirm_post) {
            window.location.href = "<?php echo admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_delete&posttype=';?>"+posttype;
        }
    });
    </script> 
    <?php 
}

function geodir_cp_add_edit_form() {
    global $cp_error;

    $listing_order = 0;
    $listing_slug = '';
    $linkable_to = '';
    $linkable_from = '';
    $custom_post_type = null;

    if ( isset( $_REQUEST['posttype'] ) && $_REQUEST['posttype'] != '' ) {
        $geodir_post_types = get_option( 'geodir_post_types' );

        $post_type_array = $geodir_post_types[$_REQUEST['posttype']];
        
        $nav_menus_posts = $nav_menus_cats = $nav_menus_tags = 0;
        
        $custom_post_type = $_REQUEST['posttype'];
        
        if ( !empty( $post_type_array ) ) {
            $hide_fields = 'readonly="readonly"';
            $listing_slug = $post_type_array['has_archive'];
            $listing_order = (int)$post_type_array['listing_order'];
            $name = stripslashes($post_type_array['labels']['name']);
            $singular_name = stripslashes($post_type_array['labels']['singular_name']);
            $add_new = stripslashes($post_type_array['labels']['add_new']);
            $add_new_item = stripslashes($post_type_array['labels']['add_new_item']);
            $edit_item = stripslashes($post_type_array['labels']['edit_item']);
            $new_item = stripslashes($post_type_array['labels']['new_item']);
            $view_item = stripslashes($post_type_array['labels']['view_item']);
            $search_item = stripslashes($post_type_array['labels']['search_items']);
            $not_found = stripslashes($post_type_array['labels']['not_found']);
            $not_found_trash = stripslashes($post_type_array['labels']['not_found_in_trash']);
            $support = $post_type_array['supports'];
            $description = wp_kses_post(stripslashes($post_type_array['description']));
            $menu_icon = $post_type_array['menu_icon'];
            $can_export = $post_type_array['can_export'];
            $geodir_cp_meta_keyword = (isset($post_type_array['seo']['meta_keyword'])) ? stripslashes($post_type_array['seo']['meta_keyword']) : '';
            $geodir_cp_meta_description = (isset($post_type_array['seo']['meta_description'])) ? stripslashes($post_type_array['seo']['meta_description']) : '';
            
            $taxonomies = get_option('geodir_taxonomies');
            
            $nav_menus_posts = isset( $post_type_array['show_in_nav_menus'] ) && $post_type_array['show_in_nav_menus'] != 1 ? 1 : 0;
            $nav_menus_cats = !empty( $taxonomies ) && isset( $taxonomies[$custom_post_type . 'category']['args']['show_in_nav_menus'] ) && $taxonomies[$custom_post_type . 'category']['args']['show_in_nav_menus'] != 1 ? 1 : 0;
            $nav_menus_tags = !empty( $taxonomies ) && isset( $taxonomies[$custom_post_type . '_tags']['args']['show_in_nav_menus'] ) && $taxonomies[$custom_post_type . '_tags']['args']['show_in_nav_menus'] != 1 ? 1 : 0;

            //link business
            $link_business = isset( $post_type_array['link_business'] ) && (int)$post_type_array['link_business'] == 1 ? 1 : 0;
            $linkable_to = isset($post_type_array['linkable_to']) ? stripslashes_deep($post_type_array['linkable_to']) : '';
            $linkable_from = isset($post_type_array['linkable_from']) ? stripslashes_deep($post_type_array['linkable_from']) : '';
        }
    }

    $label_post_profile = !empty($post_type_array) && isset($post_type_array['labels']['label_post_profile']) ? stripslashes_deep($post_type_array['labels']['label_post_profile']) : '';
    $label_post_info = !empty($post_type_array) && isset($post_type_array['labels']['label_post_info']) ? stripslashes_deep($post_type_array['labels']['label_post_info']) : '';
    $label_post_images = !empty($post_type_array) && isset($post_type_array['labels']['label_post_images']) ? stripslashes_deep($post_type_array['labels']['label_post_images']) : '';
    $label_post_map = !empty($post_type_array) && isset($post_type_array['labels']['label_post_map']) ? stripslashes_deep($post_type_array['labels']['label_post_map']) : '';
    $label_reviews = !empty($post_type_array) && isset($post_type_array['labels']['label_reviews']) ? stripslashes_deep($post_type_array['labels']['label_reviews']) : '';
    $label_related_listing = !empty($post_type_array) && isset($post_type_array['labels']['label_related_listing']) ? stripslashes_deep($post_type_array['labels']['label_related_listing']) : '';
    $cpt_image = isset($custom_post_type) ? get_option('geodir_cpt_img_' . $custom_post_type) : '';

    if ( isset ( $_REQUEST['geodir_save_post_type'] ) ) {
        $custom_post_type = sanitize_text_field(stripslashes($_REQUEST['geodir_custom_post_type']));
        $listing_slug = urldecode(sanitize_title($_REQUEST['geodir_listing_slug']));
        $listing_order = (int)$_REQUEST['geodir_listing_order'];
        $name = sanitize_text_field(stripslashes($_REQUEST['geodir_name']));
        $singular_name = sanitize_text_field(stripslashes($_REQUEST['geodir_singular_name']));
        $add_new = sanitize_text_field(stripslashes($_REQUEST['geodir_add_new']));
        $add_new_item = sanitize_text_field(stripslashes($_REQUEST['geodir_add_new_item']));
        $edit_item = sanitize_text_field(stripslashes($_REQUEST['geodir_edit_item']));
        $new_item = sanitize_text_field(stripslashes($_REQUEST['geodir_new_item']));
        $view_item = sanitize_text_field(stripslashes($_REQUEST['geodir_view_item']));
        $search_item = sanitize_text_field(stripslashes($_REQUEST['geodir_search_item']));
        $not_found = sanitize_text_field(stripslashes($_REQUEST['geodir_not_found']));
        $not_found_trash = sanitize_text_field(stripslashes($_REQUEST['geodir_not_found_trash']));
        $support = array_map( 'sanitize_text_field', $_REQUEST['geodir_support']  );
        $description = stripslashes_deep($_REQUEST['geodir_description']);
        $menu_icon = stripslashes_deep($_REQUEST['geodir_menu_icon']);
        $can_export = sanitize_text_field($_REQUEST['geodir_can_export']);
        $geodir_cp_meta_keyword = sanitize_text_field(stripslashes($_REQUEST['geodir_cp_meta_keyword']));
        $geodir_cp_meta_description = sanitize_text_field(stripslashes($_REQUEST['geodir_cp_meta_description']));
        $label_post_profile = stripslashes_deep($_REQUEST['geodir_label_post_profile']);
        $label_post_info = stripslashes_deep($_REQUEST['geodir_label_post_info']);
        $label_post_images = stripslashes_deep($_REQUEST['geodir_label_post_images']);
        $label_post_map = stripslashes_deep($_REQUEST['geodir_label_post_map']);
        $label_reviews = stripslashes_deep($_REQUEST['geodir_label_reviews']);
        $label_related_listing = stripslashes_deep($_REQUEST['geodir_label_related_listing']);
        
        $nav_menus_posts = isset( $_REQUEST['geodir_disable_nav_menus']['posts'] ) && (int)$_REQUEST['geodir_disable_nav_menus']['posts'] == 1 ? 1 : 0;
        $nav_menus_cats = isset( $_REQUEST['geodir_disable_nav_menus']['cats'] ) && (int)$_REQUEST['geodir_disable_nav_menus']['cats'] == 1 ? 1 : 0;
        $nav_menus_tags = isset( $_REQUEST['geodir_disable_nav_menus']['tags'] ) && (int)$_REQUEST['geodir_disable_nav_menus']['tags'] == 1 ? 1 : 0;
        
        $cpt_image = isset($_REQUEST['geodir_cpt_img']) ? sanitize_text_field($_REQUEST['geodir_cpt_img']) : '';

        //link business
        $link_business = isset( $_REQUEST['link_business'] ) && (int)$_REQUEST['link_business'] == 1 ? 1 : 0;
        $linkable_to = isset($_REQUEST['linkable_to']) ? stripslashes_deep($_REQUEST['linkable_to']) : '';
        $linkable_from = isset($_REQUEST['linkable_from']) ? stripslashes_deep($_REQUEST['linkable_from']) : '';
    }

    if(isset($cp_error) && $cp_error != ''){
        echo $cp_error;
    }

    //link business
    $post_types = geodir_get_posttypes( 'object' );

    $geodir_posttypes = array();
    $post_type_options = array();

    foreach ( $post_types as $key => $post_types_obj ) {

        if ($key != 'gd_event') {
            $geodir_posttypes[] = $key;

            $post_type_options[$key] = $post_types_obj->labels->singular_name;
        }

    }
?>
<dl class="gd-tab-head cpt-form-view-switcher">
    <dd class="geodir_option_tabs gd-tab-active">
        <a href="#" id="cpt_form_view_simple">Simple Mode</a>
    </dd>
    <dd class="geodir_option_tabs">
        <a href="#" id="cpt_form_view_advanced">Advanced Mode</a>
    </dd>
</dl>
<div class="inner_content_tab_main">
    <div class="gd-content-heading active">
        <h3><?php _e('Post Type','geodir_custom_posts');?></h3>
        <table class="form-table">
            <tbody>
                <tr valign="top" class="single_select_page cpt_simple_view">
                    <th class="titledesc" scope="row"><?php _e('Post type','geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input <?php if(isset($hide_fields)){ echo $hide_fields;}?> maxlength="17" onkeyup="return strForceLower(this);" class="require" type="text"  size="80" style="width:440px" id="geodir_custom_post_type" name="geodir_custom_post_type" value="<?php if(isset($custom_post_type)){ echo $custom_post_type = preg_replace('/gd_/', '',$custom_post_type, 1); }  ?>" /><span class="description"><?php _e('The new post type system name ( max. 17 characters ). Lower-case characters and underscores only. Min 2 letters. Once added the post type system name cannot be changed. <b>Usually Singular.</b>','geodir_custom_posts');?></span>
                            <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_simple_view">
                    <th class="titledesc" scope="row"><?php _e('Listing slug', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input maxlength="20" onkeyup="return strForceLower(this);" class="require" type="text"  size="80" style="width:440px" id="geodir_listing_slug" name="geodir_listing_slug" value="<?php if(isset($listing_slug)){ echo $listing_slug;} ?>" /><span class="description"><?php _e("The listing slug name ( max. 20 characters ). Alphanumeric lower-case characters and underscores  and hyphen(-) only. Min 2 letters. <b>Usually Plural.</b>",'geodir_custom_posts');?></span>
                            <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Order in post type list', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input maxlength="20" class="require" type="number"  size="80" style="width:440px" id="geodir_listing_order" name="geodir_listing_order" value="<?php if(isset($listing_order) && !empty($listing_order)){ echo $listing_order;} else { echo geodir_cp_generate_post_type_order(); } ?>" step="1" min="0" max="1000" /><span class="description"><?php _e("Position at which this post type will appear in post type list everywhere on the website.",'geodir_custom_posts');?></span>
                             <span class="description"><b><?php _e("Note: If the entered value is already an order of other post type then this will not make any effect.",'geodir_custom_posts');?></b></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="cpt_advanced_view">
                  <th class="titledesc" scope="row"><?php _e('Upload default image', 'geodir_custom_posts');?></th>
                  <td class="forminp"><input type="file" id="geodir_cpt_img" name="geodir_cpt_img" />
                    <input type="hidden" value="0" id="geodir_cpt_img_remove" name="geodir_cpt_img_remove" />
                    <span class="description"><?php _e("Upload default post type image.",'geodir_custom_posts');?></span>
                    <?php if ($cpt_image != '') { ?><span class="description"><a target="_blank" href="<?php echo $cpt_image;?>"><?php echo $cpt_image;?></a> <i class="fa fa-times gd-remove-file" onclick="jQuery('#geodir_cpt_img_remove').val('1'); jQuery( this ).parent().text('<?php _e('Save to remove file', 'geodir_custom_posts');?>');" title="<?php _e('Remove file (set to empty)', 'geodir_custom_posts');?>"></i></span><?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table" style="display:none;">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Categories','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" checked="checked"  type="checkbox" value="geodir_categories" name="geodir_categories">&nbsp;
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Tags','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" checked="checked" type="checkbox" value="geodir_tags" name="geodir_tags">&nbsp;
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                    
            </tbody>
        </table>
        <h3><?php _e('Labels','geodir_custom_posts');?></h3>
        <table class="form-table">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Name','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input class="require" type="text"  size="80" style="width:440px" id="geodir_name" name="geodir_name" value="<?php if(isset($name)){echo $name;} ?>" /><span class="description"><?php _e('General name for the post type, <b>Usually Plural</b>.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Singular name','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input class="require" type="text"  size="80" style="width:440px" id="geodir_singular_name" name="geodir_singular_name" value="<?php if(isset($singular_name)){ echo $singular_name;} ?>" /><span class="description"><?php _e('Name for one object of this post type. Defaults to value of name.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Add new','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input class="require" type="text"  size="80" style="width:440px" id="geodir_add_new" name="geodir_add_new" value="<?php if(isset($add_new)){ echo $add_new;} ?>" /><span class="description"><?php _e('The add new text. The default is Add New for both hierarchical and non-hierarchical types.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Add new item','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input class="require" type="text"  size="80" style="width:440px" id="geodir_add_new_item" name="geodir_add_new_item" value="<?php if(isset($add_new_item)){ echo $add_new_item;} ?>" /><span class="description"><?php _e('The add new item text. Default is Add New Post/Add New Page.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Edit item','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input class="require" type="text"  size="80" style="width:440px" id="geodir_edit_item" name="geodir_edit_item" value="<?php if(isset($edit_item)){echo $edit_item;} ?>" /><span class="description"><?php _e('The edit item text. Default is Edit Post/Edit Page.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                    
                    <tr valign="top" class="single_select_page cpt_advanced_view">
                            <th class="titledesc" scope="row"><?php _e('New item','geodir_custom_posts');?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild">
                                 <input class="require" type="text"  size="80" style="width:440px" id="geodir_new_item" name="geodir_new_item" value="<?php if(isset($new_item)){echo $new_item;} ?>" /><span class="description"><?php _e('The new item text. Default is New Post/New Page.','geodir_custom_posts');?></span>
                                 <div class="gd-location_message_error2"></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                    </tr>
                    
                    <tr valign="top" class="single_select_page cpt_advanced_view">
                            <th class="titledesc" scope="row"><?php _e('View item','geodir_custom_posts');?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild">
                                 <input class="require" type="text"  size="80" style="width:440px" id="geodir_view_item" name="geodir_view_item" value="<?php if(isset($view_item)){echo $view_item;} ?>" /><span class="description"><?php _e('The view item text. Default is View Post/View Page.','geodir_custom_posts');?></span>
                                 <div class="gd-location_message_error2"></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                    </tr>
                    
                    <tr valign="top" class="single_select_page cpt_advanced_view">
                            <th class="titledesc" scope="row"><?php _e('Search items','geodir_custom_posts');?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild">
                                 <input class="require" type="text"  size="80" style="width:440px" id="geodir_search_item" name="geodir_search_item" value="<?php if(isset($search_item)){echo $search_item;} ?>" /><span class="description"><?php _e('The search items text. Default is Search Posts/Search Pages.','geodir_custom_posts');?></span>
                                 <div class="gd-location_message_error2"></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                    </tr>
                    
                    <tr valign="top" class="single_select_page cpt_advanced_view">
                            <th class="titledesc" scope="row"><?php _e('Not found','geodir_custom_posts');?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild">
                                 <input class="require" type="text"  size="80" style="width:440px" id="geodir_not_found" name="geodir_not_found" value="<?php if(isset($not_found)){echo $not_found;} ?>" /><span class="description"><?php _e('The not found text. Default is No posts found/No pages found.','geodir_custom_posts');?></span>
                                 <div class="gd-location_message_error2"></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                    </tr>
                    
                    <tr valign="top" class="single_select_page cpt_advanced_view">
                            <th class="titledesc" scope="row"><?php _e('Not found in trash','geodir_custom_posts');?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild">
                                 <input class="require" type="text"  size="80" style="width:440px" id="geodir_not_found_trash" name="geodir_not_found_trash" value="<?php if(isset($not_found_trash)){echo $not_found_trash;} ?>" /><span class="description"><?php _e('The not found in trash text. Default is No posts found in Trash/No pages found in Trash.','geodir_custom_posts');?></span>
                                 <div class="gd-location_message_error2"></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                    </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Profile tab label', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_profile" name="geodir_label_post_profile" value="<?php echo $label_post_profile;?>" />
                            <span class="description"><?php _e('Text label for "Profile" tab on post detail page.(optional)', 'geodir_custom_posts');?></span>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('More Info tab label', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_info" name="geodir_label_post_info" value="<?php echo $label_post_info;?>" />
                            <span class="description"><?php _e('Text label for "More Info" tab on post detail page.(optional)', 'geodir_custom_posts');?></span>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Photo tab label', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_images" name="geodir_label_post_images" value="<?php echo $label_post_images;?>" />
                            <span class="description"><?php _e('Text label for Photo" tab on post detail page.(optional)', 'geodir_custom_posts');?></span>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Map tab label', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input class="require" type="text"  size="80" style="width:440px" id="geodir_label_post_map" name="geodir_label_post_map" value="<?php echo $label_post_map;?>" />
                            <span class="description"><?php _e('Text label for "Map" tab on post detail page.(optional)', 'geodir_custom_posts');?></span>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Reviews tab label', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input class="require" type="text"  size="80" style="width:440px" id="geodir_label_reviews" name="geodir_label_reviews" value="<?php echo $label_reviews;?>" />
                            <span class="description"><?php _e('Text label for "Reviews" tab on post detail page.(optional)', 'geodir_custom_posts');?></span>
                        </div>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page cpt_advanced_view">
                    <th class="titledesc" scope="row"><?php _e('Related Listing tab label', 'geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input class="require" type="text"  size="80" style="width:440px" id="geodir_label_related_listing" name="geodir_label_related_listing" value="<?php echo $label_related_listing;?>" />
                            <span class="description"><?php _e('Text label for "Related Listing" tab on post detail page.(optional)', 'geodir_custom_posts');?></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3 class="cpt_advanced_view"><?php _e( 'Nav Menus', 'geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
          <tbody>
            <tr valign="top" class="single_select_page">
              <th class="titledesc" scope="row"><?php _e('Posts', 'geodir_custom_posts');?></th>
              <td class="forminp">
                <div class="gtd-formfeild">
                  <input id="geodir_navmenu_disable_posts" type="checkbox" <?php echo (isset($nav_menus_posts) && $nav_menus_posts == 1 ? 'checked="checked"' : '' );?> value="1" name="geodir_disable_nav_menus[posts]">&nbsp;<?php _e('Disable posts in nav menus.', 'geodir_custom_posts');?>
                </div>
                <span class="description"></span></td>
            </tr>
            <tr valign="top" class="single_select_page">
              <th class="titledesc" scope="row"><?php _e('Categories', 'geodir_custom_posts');?></th>
              <td class="forminp">
                <div class="gtd-formfeild">
                  <input id="geodir_navmenu_disable_cats" type="checkbox" <?php echo (isset($nav_menus_cats) && $nav_menus_cats == 1 ? 'checked="checked"' : '' );?> value="1" name="geodir_disable_nav_menus[cats]">&nbsp;<?php _e('Disable post categories in nav menus.', 'geodir_custom_posts');?>
                </div>
                <span class="description"></span></td>
            </tr>
            <tr valign="top" class="single_select_page">
              <th class="titledesc" scope="row"><?php _e('Tags', 'geodir_custom_posts');?></th>
              <td class="forminp">
                <div class="gtd-formfeild">
                  <input id="geodir_navmenu_disable_tags" type="checkbox" <?php echo (isset($nav_menus_tags) && $nav_menus_tags == 1 ? 'checked="checked"' : '' );?> value="1" name="geodir_disable_nav_menus[tags]">&nbsp;<?php _e('Disable posts tags in nav menus.', 'geodir_custom_posts');?>
                </div>
                <span class="description"></span></td>
            </tr>
          </tbody>
        </table>
        <h3 class="cpt_advanced_view"><?php _e('Supports','geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Supports','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                            <?php _e('Register support of certain features for a post type.','geodir_custom_posts');?>
                             
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page" style="display:none;">
                    <th class="titledesc" scope="row"><?php _e('Title','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" type="checkbox" checked="checked" value="title" name="geodir_support[]">&nbsp;<?php _e('Title','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page" style="display:none;">
                    <th class="titledesc" scope="row"><?php _e('Editor','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" type="checkbox" checked="checked" value="editor" name="geodir_support[]">&nbsp;<?php _e('Editor - Content','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Author','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                         
                             <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('author', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="author" name="geodir_support[]">&nbsp;<?php _e('Author', 'geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Thumbnail','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('thumbnail', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="thumbnail" name="geodir_support[]">&nbsp;<?php _e('Thumbnail - featured image - current theme must also support post-thumbnails.','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Excerpt','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('excerpt', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="excerpt" name="geodir_support[]">&nbsp;<?php _e('Excerpt', 'geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Custom fields','geodir_custom_posts');?> </th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('custom-fields', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="custom-fields" name="geodir_support[]">&nbsp;<?php _e('Custom fields','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Comments','geodir_custom_posts');?>  </th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" <?php if(!empty($support)){if(in_array('comments', $support)){echo 'checked="checked"';}}elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="comments" name="geodir_support[]">&nbsp;<?php _e('Comments - also will see comment count balloon on edit screen.','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Post formats','geodir_custom_posts');?> </th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input id="geodir_listing_expiry_disable" <?php
                             if(!empty($support)){if(in_array('post-formats', $support)){echo 'checked="checked"';}}
                             elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="post-formats" name="geodir_support[]">&nbsp;<?php _e('Post formats - add post formats.','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <?php if (defined('WP_POST_REVISIONS') && WP_POST_REVISIONS){?>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Revisions','geodir_custom_posts');?> </th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <input id="geodir_listing_expiry_disable" <?php
                            if(!empty($support)){if(in_array('revisions', $support)){echo 'checked="checked"';}}
                            elseif(!isset($support)){echo 'checked="checked"';}?> type="checkbox" value="revisions" name="geodir_support[]">&nbsp;<?php _e('Revisions - allow for post content (not custom fields) revisions to be stored (not recommended).','geodir_custom_posts');?>
                            <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <h3 class="cpt_advanced_view"><?php _e('Description','geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Description','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <textarea name="geodir_description" class="require" style="width:440px"><?php if(isset($description)){echo $description;} ?></textarea><span class="description"><?php _e('A short descriptive summary of what the post type is.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3 class="cpt_advanced_view"><?php _e('Menu Icon','geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Menu Icon','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <input class="require" type="text"  size="80" style="width:440px" id="geodir_menu_icon" name="geodir_menu_icon" value="<?php if(isset($menu_icon)){echo $menu_icon;} ?>" /><span class="description"><?php _e('The url to the icon to be used for this menu.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                    
            </tbody>
        </table>
        <h3 class="cpt_advanced_view"><?php _e('Can Export','geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Can Export','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                            <input id="geodir_tiny_editor1" <?php if(isset($can_export) && ($can_export == true || $can_export == 1)){echo 'checked="checked"';}?> type="radio" value="true" name="geodir_can_export">&nbsp;<?php _e('True','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row">&nbsp;</th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                            <input id="geodir_tiny_editor1" <?php if(!isset($can_export) || $can_export == false || $can_export == 0){echo 'checked="checked"';}?> type="radio" value="false" name="geodir_can_export">&nbsp;<?php _e('False','geodir_custom_posts');?>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3 class="cpt_advanced_view"><?php _e('SEO','geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
            <tbody>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Meta Keywords','geodir_custom_posts');?></th>
                    <td class="forminp">
                         <div class="gtd-formfeild">
                             <textarea name="geodir_cp_meta_keyword" class="require" style="width:440px"><?php if(isset($geodir_cp_meta_keyword)){echo $geodir_cp_meta_keyword;} ?></textarea><span class="description"><?php _e('Meta keywords will appear in head tag of this post type listing page.','geodir_custom_posts');?></span>
                             <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
                <tr valign="top" class="single_select_page">
                    <th class="titledesc" scope="row"><?php _e('Meta Description','geodir_custom_posts');?></th>
                    <td class="forminp">
                        <div class="gtd-formfeild">
                            <textarea name="geodir_cp_meta_description" class="require" style="width:440px"><?php if(isset($geodir_cp_meta_description)){echo $geodir_cp_meta_description;} ?></textarea><span class="description"><?php _e('Meta description will appear in head tag of this post type listing page.','geodir_custom_posts');?></span>
                            <div class="gd-location_message_error2"></div>
                        </div>
                        <span class="description"></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3 id="post_type_link_business" class="cpt_advanced_view"><?php _e('Link Business','geodir_custom_posts');?></h3>
        <table class="form-table cpt_advanced_view">
            <tbody>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php _e('Link Business','geodir_custom_posts');?></th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <input id="link_business" type="checkbox" <?php echo (isset($link_business) && $link_business == 1 ? 'checked="checked"' : '' );?> value="1" name="link_business">&nbsp;<?php _e('Link Business', 'geodir_custom_posts');?>
                        <div class="gd-location_message_error2"></div>
                    </div>
                    <span class="description"></span>
                </td>
            </tr>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php _e('Linkable To','geodir_custom_posts');?></th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <input type="hidden" name="old_linkable_to" value="<?php echo $linkable_to; ?>">
                        <select name="linkable_to" id="linkable_to">
                            <option value=""><?php _e('Select Post Type','geodir_custom_posts');?></option>
                            <?php
                            foreach ($post_type_options as $key => $post_type_option) {
                                echo '<option value="'.$key.'" '.selected( $linkable_to, $key, false).'>'.$post_type_option.'</option>';
                            }
                            ?>
                        </select>
                        <div class="gd-location_message_error2">
                            <?php
                            if ($linkable_to) {
                                _e('If you modify this setting, all your existing links will be lost','geodir_custom_posts');
                            }
                            ?>
                        </div>
                    </div>
                    <span class="description"></span>
                </td>
            </tr>
            <tr valign="top" class="single_select_page">
                <th class="titledesc" scope="row"><?php _e('Linkable From','geodir_custom_posts');?></th>
                <td class="forminp">
                    <div class="gtd-formfeild">
                        <?php
                        $geodir_linked_post_types = get_option('geodir_linked_post_types');

                        $curr_post_type = 'gd_'.$custom_post_type;
                        if(isset($curr_post_type) && isset($geodir_linked_post_types[$curr_post_type])){
                            $linked_post_type = $geodir_linked_post_types[$curr_post_type];
                        } else {
                            $linked_post_type = false;
                        }
                        ?>
                        <select disabled="disabled" name="linkable_from" id="linkable_from">
                            <option value=""><?php _e('Select Post Type','geodir_custom_posts');?></option>
                            <?php
                            if ($linked_post_type) {
                                $linkable_from = $linked_post_type;
                            }
                            foreach ($post_type_options as $key => $post_type_option) {
                                echo '<option value="'.$key.'" '.selected( $linkable_from, $key, false).'>'.$post_type_option.'</option>';
                            }
                            ?>
                        </select>
                        <div class="gd-location_message_error2">
                            <?php
                            if ($linked_post_type) {
                                $post_type_edit_link = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_addedit&posttype='.$linked_post_type.'#post_type_link_business';
                                echo __('This post type is already linked with <b>'.$linked_post_type.'</b> post type. To modify edit <a href="'.$post_type_edit_link.'">'.$linked_post_type.'</a>','geodir_custom_posts');
                            } elseif ($linkable_from) {
                                _e('If you modify this setting, all your existing links will be lost','geodir_custom_posts');
                            }
                            ?>
                        </div>
                    </div>
                    <span class="description"></span>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit" style="margin-top:10px;">
            <input name="geodir_save_post_type" class="button-primary" type="submit" value="<?php _e( 'Save changes','geodir_custom_posts' ); ?>" />
            <input type="hidden" name="subtab" id="last_tab" />
        </p>
        <script type="text/javascript">
            jQuery( document ).ready(function() {
                jQuery('#cpt_form_view_advanced').click(function(){
                    jQuery('#cpt_form_view_simple').parent().removeClass('gd-tab-active');
                    jQuery(this).parent().addClass('gd-tab-active');
                    jQuery('.cpt_advanced_view').show();
                });

                jQuery('#cpt_form_view_simple').click(function(){
                    jQuery('#cpt_form_view_advanced').parent().removeClass('gd-tab-active');
                    jQuery(this).parent().addClass('gd-tab-active');
                    jQuery('.cpt_advanced_view').hide();
                });
            });
            function strForceLower(strInput) {
                strInput.value=strInput.value.toLowerCase();
            }
        </script>
    </div>
</div>
<?php
}

function geodir_cp_add_edit_form_css() {
    ?>
    <style type="text/css">
        dl.cpt-form-view-switcher dd a:active,
        dl.cpt-form-view-switcher dd a:focus,
        dl.cpt-form-view-switcher dd a.active {
            background-image: none;
            outline: 0;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        .cpt_advanced_view {
            display: none;
        }
    </style>
    <?php
}
add_action('admin_head', 'geodir_cp_add_edit_form_css');

function geodir_cp_generate_post_type_order() {
    $all_postypes = geodir_get_posttypes('array');
    $values = array();
    if ($all_postypes) {
        foreach($all_postypes as $postype) {

            $values[] = isset($postype['listing_order']) ? (int) $postype['listing_order'] : 0;
        }
    }

    if ($values) {
        $order = max($values) + 1;
    } else {
        $order = 1;
    }
    return $order;
}
?>