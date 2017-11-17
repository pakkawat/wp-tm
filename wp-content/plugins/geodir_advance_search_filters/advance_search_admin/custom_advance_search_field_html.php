<?php /* ====== Custom fields form  ======*/
global $post_type;
$field_admin_title = '';
$field_site_type = '';
$htmlvar_name = '';
$field_input_type = '';
$field_type = '';

$field_info = stripslashes_deep($field_info); // strip slashes

if (!isset($field_info->post_type)) {
    $post_type = esc_attr($_REQUEST['listing_type']);
} else
    $post_type = $field_info->post_type;

$nonce = wp_create_nonce('custom_advance_search_fields_' . $result_str);


if (isset($field_info->admin_title))
    $field_admin_title = $field_info->admin_title;


if (isset($field_info->field_site_name)){
    if($field_info->field_site_type=='fieldset'){
        $field_site_name = __('Fieldset:', 'geodiradvancesearch').' '. $field_info->front_search_title;
    }else{
        $field_site_name = $field_info->field_site_name;
    }

}
else
    $field_site_name = esc_attr($_REQUEST['site_field_title']);


if (isset($_REQUEST['htmlvar_name']) && $_REQUEST['htmlvar_name'] != '')
    $htmlvar_name = esc_attr($_REQUEST['htmlvar_name']);
else
    $htmlvar_name = $field_info->site_htmlvar_name;

if (isset($_REQUEST['field_type']) && !empty($_REQUEST['field_type'])) {
    $field_type = esc_attr($_REQUEST['field_type']);
}

$search_condition = "SINGLE";
$field_input_type = "SINGLE";
$field_data_type = "VARCHAR";

if (isset($_REQUEST['field_data_type']) && $_REQUEST['field_data_type']) {
    $field_data_type = esc_attr($_REQUEST['field_data_type']);

    if ($field_data_type == 'DATE' || $field_data_type == 'TIME') {
        $search_condition = "SINGLE";
        $field_input_type = "DATE";
    } elseif ($field_data_type == 'INT') {
        $search_condition = "SELECT";
        $field_input_type = "RANGE";
    } elseif ($field_data_type == 'taxonomy' || $field_data_type == 'select') {
        $search_condition = "SINGLE";
        $field_input_type = "SELECT";
        $field_data_type = "VARCHAR";
    }

}
if (isset($field_info->search_condition) && !empty($field_info->search_condition))
    $search_condition = $field_info->search_condition;

if (isset($field_info->field_data_type) && !empty($field_info->field_data_type))
    $field_data_type = $field_info->field_data_type;

if (isset($field_info->field_input_type) && !empty($field_info->field_input_type))
    $field_input_type = $field_info->field_input_type;

if (isset($field_info->field_site_type) && !empty($field_info->field_site_type))
    $field_type = $field_info->field_site_type;

$extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : NULL;
$search_operator = !empty($extra_fields) && isset($extra_fields['search_operator']) && $extra_fields['search_operator'] == 'OR' ? 'OR' : 'AND';

if ($field_data_type == 'VARCHAR') {
    $field_data_type = 'XVARCHAR';
}

if(isset($htmlvar_name)){
    if(!is_object($field_info)){$field_info = new stdClass();}
    if($htmlvar_name=='dist'){
        $field_info->field_icon = 'fa fa-map-marker';
    }
    elseif($htmlvar_name=='event'){
        $field_info->field_icon = 'fa fa-calendar';
    }else{
        $field_info->field_icon = $wpdb->get_var(
            $wpdb->prepare("SELECT field_icon FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE htmlvar_name = %s", array($htmlvar_name))
        );
    }
}

if (isset($field_info->field_icon) && strpos($field_info->field_icon, 'fa fa-') !== false) {
    $field_icon = '<i class="'.$field_info->field_icon.'" aria-hidden="true"></i>';
}elseif(isset($field_info->field_icon) && $field_info->field_icon){
    $field_icon = '<b style="background-image: url("'.$field_info->field_icon.'")"></b>';
}
elseif(isset($field_info->field_site_type) && $field_info->field_site_type=='fieldset'){
    $field_icon = '<i class="fa fa-arrows-h" aria-hidden="true"></i>';
}else{
    $field_icon = '<i class="fa fa-cog" aria-hidden="true"></i>';
}


$radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);

?>
<li class="text" id="licontainer_<?php echo $result_str; ?>">
    <form><!-- we need to wrap in a form so we can use radio buttons with same name -->
    <div class="title title<?php echo $result_str; ?> gt-fieldset"
         title="<?php _e('Double Click to toggle and drag-drop to sort', 'geodiradvancesearch'); ?>"
         ondblclick="show_hide_advance_search('field_frm<?php echo $result_str; ?>')">
        <?php

        $nonce = wp_create_nonce('custom_advance_search_fields_' . $result_str);
        ?>

        <?php if ($default): ?>
            <div title="<?php _e('Drag and drop to sort', 'geodiradvancesearch'); ?>"
                 onclick="delete_advance_search_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name; ?>')"
                 class="handlediv close"><i class="fa fa-times" aria-hidden="true"></i></div>
        <?php else: ?>
            <div title="<?php _e('Click to remove field', 'geodiradvancesearch'); ?>"
                 onclick="delete_advance_search_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name; ?>')"
                 class="handlediv close"><i class="fa fa-times" aria-hidden="true"></i></div>
        <?php endif;
        echo $field_icon;
        ?>
        <b style="cursor:pointer;"
           onclick="show_hide_advance_search('field_frm<?php echo $result_str; ?>')"><?php echo geodir_ucwords( ' ' . $field_site_name); ?></b>

    </div>

    <div id="field_frm<?php echo $result_str; ?>" class="field_frm"
         style="display:<?php if ($field_ins_upd == 'submit') {
             echo 'block;';
         } else {
             echo 'none;';
         } ?>">
        <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
        <input type="hidden" name="listing_type" id="listing_type" value="<?php echo $post_type; ?>"/>
        <input type="hidden" name="field_type" id="field_type" value="<?php echo esc_attr($field_type); ?>"/>
        <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr($result_str); ?>"/>
        <input type="hidden" name="data_type" id="data_type" value="<?php echo esc_attr($field_input_type); ?>"/>
        <input type="hidden" name="is_active" id="is_active" value="1"/>
        <input type="hidden" name="site_field_title" id="site_field_title"
               value="<?php echo esc_attr($field_site_name); ?>"/>
        <input type="hidden" name="field_data_type" id="field_data_type"
               value="<?php echo esc_attr($field_data_type); ?>"/>
        <ul class="widefat post fixed" border="0" style="width:100%;">


            <?php

            if ($field_type == 'taxonomy' || $field_type == 'select' || $field_type == 'radio' || $field_type == 'checkbox' || $field_type == 'datepicker' || ($field_type == 'text' && $field_data_type=='FLOAT' ) ){
            $value = '';
            if (isset($field_info->main_search)) {
                $value = esc_attr($field_info->main_search);
            }
            ?>
                <li >
                    <label for="main_search" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show in main search bar?', 'geodirectory'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('This will show the filed in the main search bar as a select input, it will no longer show in the advanced search dropdown.', 'geodirectory'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap gd-switch">
    
                        <input type="radio" id="main_search_yes<?php echo $radio_id;?>" name="main_search" class="gdri-enabled"  value="1"
                            <?php if ($value == '1') {
                                echo 'checked';
                            } ?>/>
                        <label onclick="show_hide_radio(this,'show','main_search_priority');"  for="main_search_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>
    
                        <input type="radio" id="main_search_no<?php echo $radio_id;?>" name="main_search" class="gdri-disabled" value="0"
                            <?php if ($value == '0' || !$value) {
                                echo 'checked';
                            } ?>/>
                        <label onclick="show_hide_radio(this,'hide','main_search_priority');" for="main_search_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>
    
                    </div>
                </li>


                <?php
                $value = '';
                if (isset($field_info->main_search_priority) && $field_info->main_search_priority!='0') {
                    $value = esc_attr($field_info->main_search_priority);
                }else{
                    $value = '15';
                }
                ?>
                <li class="main_search_priority" <?php if ((isset($field_info->main_search) && $field_info->main_search == '0') || !isset($field_info->main_search)) {echo "style='display:none;'";}?>>

                    <label for="main_search_priority" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Search bar priority', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Where in the search bar you want it to be placed (recommended 15). CPT input: 10, Search input:20, Near input:30', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <input
                            type="number" name="main_search_priority"
                            value="<?php echo esc_attr($value);?>"/>
                    </div>

                </li>

            <?php }?>

            <?php if ($field_type == 'taxonomy' || $field_type == 'select' || $field_type == 'radio' || $field_type == 'multiselect') { ?>

                <li>

                    <label for="data_type" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Field Data Type', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Select Custom Field type.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <select name="data_type" id="data_type"
                                onchange="select_search_custom(this.value,'<?php echo $result_str; ?>');">
                            <option
                                value="SELECT" <?php if (isset($field_info->field_input_type) && $field_info->field_input_type == 'SELECT') {
                                echo 'selected="selected"';
                            } ?>><?php _e('SELECT', 'geodiradvancesearch'); ?></option>
                            <option
                                value="CHECK" <?php if (isset($field_info->field_input_type) && $field_info->field_input_type == 'CHECK') {
                                echo 'selected="selected"';
                            } ?>><?php _e('CHECK', 'geodiradvancesearch'); ?></option>
                            <option
                                value="RADIO" <?php if (isset($field_info->field_input_type) && $field_info->field_input_type == 'RADIO') {
                                echo 'selected="selected"';
                            } ?>><?php _e('RADIO', 'geodiradvancesearch'); ?></option>
                            <option
                                value="LINK" <?php if (isset($field_info->field_input_type) && $field_info->field_input_type == 'LINK') {
                                echo 'selected="selected"';
                            } ?>><?php _e('LINK', 'geodiradvancesearch'); ?></option>
                        </select>
                    </div>

                </li>








            <?php } else if ($field_data_type == 'INT' || $field_data_type == 'FLOAT' && ($field_type != 'fieldset')) {
                if ($htmlvar_name != 'dist') {
                    ?>

                    <li>

                        <label for="data_type_change" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Field Data Type', 'geodiradvancesearch'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Select Custom Field type.', 'geodiradvancesearch'); ?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">

                            <select name="data_type_change" id="data_type_change"
                                    onchange="select_search_type(this.value,'<?php echo $result_str;?>');">
                                <option
                                    value="SELECT" <?php if (!empty($field_info->search_condition) && $field_info->search_condition == 'SELECT') {
                                    echo 'selected="selected"';
                                }?>><?php _e('Range in SELECT', 'geodiradvancesearch');?></option>
                                <option
                                    value="LINK" <?php if (!empty($field_info->search_condition) && $field_info->search_condition == 'LINK') {
                                    echo 'selected="selected"';
                                }?>><?php _e('Range in LINK', 'geodiradvancesearch');?></option>
                                <option
                                    value="TEXT" <?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                                    echo 'selected="selected"';
                                }?>><?php _e('Range in TEXT', 'geodiradvancesearch');?></option>
                            </select>
                        </div>

                    </li>

                <?php } ?>



                <li class="search_type_text"
                    style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {

                    } else {
                        echo 'none';
                    } ?>">

                    <label for="search_condition_select" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Searching Type', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Select searching type.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <select id="search_condition_select" name="search_condition_select"
                                onchange="select_range_option(this.value,'<?php echo $result_str; ?>');" >
                            <option
                                value="SINGLE" <?php if (isset($field_info->search_condition) && $field_info->search_condition == 'SINGLE') {
                                echo 'selected="selected"';
                            } ?>><?php _e('Range single', 'geodiradvancesearch'); ?></option>
                            <option
                                value="FROM" <?php if (isset($field_info->search_condition) && $field_info->search_condition == 'FROM') {
                                echo 'selected="selected"';
                            } ?>><?php _e('Range from', 'geodiradvancesearch'); ?></option>
                        </select>
                    </div>

                </li>

                <?php if ($htmlvar_name != 'dist') { ?>

                    <li class="search_type_drop"
                        style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                            echo 'none';
                        } ?>">

                        <label for="search_min_value" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Starting Search Range', 'geodiradvancesearch'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Starting Search Range', 'geodiradvancesearch'); ?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">

                            <input
                                type="text" name="search_min_value"
                                value="<?php if (isset($field_info->search_min_value)) {
                                    echo esc_attr($field_info->search_min_value);
                                } ?>"/>
                        </div>

                    </li>

                <?php } ?>


                <li class="search_type_drop"
                    style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                        echo 'none';
                    } ?>">

                    <label for="search_max_value" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Maximum Search Range', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Enter the maximum radius of the search zone you want to create, for example if you want your visitors to search any listing within 50 miles or kilometers from the current location, then you would enter 50.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">
                        <input type="number" name="search_max_value"
                               min="1"
                               value="<?php if (isset($field_info->search_max_value)) {
                                   echo esc_attr($field_info->search_max_value);
                               } ?>"/>
                    </div>

                </li>


                <li class="search_type_drop"
                    style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                        echo 'none';
                    }  ?>">

                    <label for="search_max_value" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Difference in Search Range', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Here you decide how many different search radii you make available to your visitors. If you enter a fifth of the Maximum Search Range, there will be 5 options; if you enter half of the Maximum Search Range, then there will be 2 options.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">
                        <input type="number"
                               min="1"                               name="search_diff_value"
                               value="<?php if (isset($field_info->search_diff_value)) {
                                   echo esc_attr($field_info->search_diff_value);
                               } ?>" <?php if ($htmlvar_name != 'dist') { ?> onkeyup="search_difference_value(this.value);" onchange="search_difference_value(this.value);" <?php } ?> />
                        <span class="search_diff_value"
                              style="display: <?php if (isset($field_info->search_diff_value) && $field_info->search_diff_value == 1) {
                                  echo 'block';
                              } else {
                                  echo 'none';
                              } ?>;"> <input type="checkbox" name="searching_range_mode"
                                             value="1" <?php if (isset($field_info->searching_range_mode) && $field_info->searching_range_mode == 1) {
                                echo 'checked="checked"';
                            } ?>  /><?php _e('You want to searching with single range', 'geodiradvancesearch'); ?></span>
                    </div>

                </li>



                <?php if ($htmlvar_name != 'dist') { ?>

                    <li class="search_type_drop"
                        style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                            echo 'none';
                        } ?>">

                        <label for="first_search_value" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('First Search Range', 'geodiradvancesearch'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('First Search Range', 'geodiradvancesearch'); ?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">

                            <input
                                type="text" name="first_search_value"
                                value="<?php if (isset($field_info->first_search_value)) {
                                    echo esc_attr($field_info->first_search_value);
                                } ?>"/>
                        </div>

                    </li>


                    <li class="search_type_drop"
                        style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                            echo 'none';
                        }?>">

                        <label for="first_search_text" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('First Search Range Text', 'geodiradvancesearch'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('First Search Range Text', 'geodiradvancesearch'); ?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">

                            <input
                                type="text" name="first_search_text"
                                value="<?php if (isset($field_info->first_search_text)) {
                                    echo esc_attr($field_info->first_search_text);
                                } ?>"/> <br/><span><?php _e('Less than', 'geodiradvancesearch'); ?></span>
                        </div>

                    </li>

                    <li class="search_type_drop"
                        style="display:<?php if (!empty($field_info->search_condition) && ($field_info->search_condition == 'SINGLE' || $field_info->search_condition == 'FROM')) {
                            echo 'none';
                        } ?>">

                        <label for="last_search_text" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Last Search Range Text', 'geodiradvancesearch'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Last Search Range Text', 'geodiradvancesearch'); ?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">

                            <input
                                type="text" name="last_search_text"
                                value="<?php if (isset($field_info->last_search_text)) {
                                    echo esc_attr($field_info->last_search_text);
                                } ?>"/><br/><span><?php _e('More than', 'geodiradvancesearch'); ?></span>
                        </div>

                    </li>

                <?php
                }
            } elseif ($field_input_type == 'DATE') { ?>

                <li >

                    <label for="last_search_text" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Searching Type', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Searching Type', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <select id="search_condition_select" name="search_condition_select"
                                onchange="select_range_option(this.value,'<?php echo $result_str; ?>');"
                                style="width:100%">
                            <option
                                value="SINGLE" <?php if (isset($field_info->search_condition) && $field_info->search_condition == 'SINGLE') {
                                echo 'selected="selected"';
                            } ?>><?php _e('Range in single', 'geodiradvancesearch'); ?></option>
                            <option
                                value="FROM" <?php if (isset($field_info->search_condition) && $field_info->search_condition == 'FROM') {
                                echo 'selected="selected"';
                            } ?>><?php _e('Range in from', 'geodiradvancesearch'); ?></option>
                        </select>
                    </div>

                </li>

            <?php
            } ?>
            <?php
            $serach_field_name = '';

            if (isset($htmlvar_name) && $htmlvar_name == 'post') {
                $serach_field_name = $htmlvar_name . '_' . $field_type;

            } else if (isset($htmlvar_name) && $htmlvar_name == $post_type . 'category') {
                $serach_field_name = $post_type . 'category';

            } else {
                $serach_field_name = $htmlvar_name;
            }

            ?>
            <input type="hidden" name="search_condition" id="search_condition"
                   value="<?php if (isset($search_condition)) {
                       echo esc_attr($search_condition);
                   } ?>"/>
            <input type="hidden" name="site_htmlvar_name" value="<?php echo $htmlvar_name ?>"/>
            <input type="hidden" name="field_title" id="field_title" value="<?php if (isset($serach_field_name)) {
                echo esc_attr($serach_field_name);
            } ?>" size="50"/>&nbsp;


            <li class="expand_custom_area"
                style="display:<?php if ((isset($search_condition) && $search_condition == "LINK") || $field_input_type == "LINK" || $field_input_type == "CHECK" || $htmlvar_name == 'dist') {
                } else {
                    echo 'none';
                } ?>" >

                <label for="last_search_text" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Expand Search Range', 'geodiradvancesearch'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('If you leave this blank, then all options as per Difference in Search Range will be shown. Entering a  number lower than the number of options as per Difference in Search Range will only show that lower number of options, and will add a More link to expand the options so all will be shown.', 'geodiradvancesearch'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">

                    <input min="1"
                           width="35px" type="number" name="expand_custom_value" id="expand_custom_value"
                           value="<?php if (!empty($field_info->expand_custom_value))
                               echo esc_attr($field_info->expand_custom_value); ?>"/><br/>
                    <input type="checkbox" name="expand_search" id="expand_search"
                           value="1" <?php if (!empty($field_info->expand_search)) echo 'checked="checked"'; ?>  /><?php _e('Please check to expand Search Range', 'geodiradvancesearch'); ?>

            </li>


            <?php
            if (isset($htmlvar_name) && $htmlvar_name == 'dist') {

                $extra_fields = '';
                if (isset($field_info->extra_fields) && $field_info->extra_fields != '')
                    $extra_fields = unserialize($field_info->extra_fields);
                $geodir_distance_sorting = isset($extra_fields['is_sort']) ? $extra_fields['is_sort'] : '';
                $search_asc = isset($extra_fields['asc']) ? $extra_fields['asc'] : '';
                $search_asc_title = isset($extra_fields['asc_title']) ? $extra_fields['asc_title'] : '';
                $search_desc = isset($extra_fields['desc']) ? $extra_fields['desc'] : '';
                $search_desc_title = isset($extra_fields['desc_title']) ? $extra_fields['desc_title'] : '';


                ?>

                <li>
                    <label for="geodir_distance_sorting" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show distance sorting', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Select if you want to show option in distance sort.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <input type="checkbox" name="geodir_distance_sorting" id="geodir_distance_sorting"
                               value="1" <?php if (isset($geodir_distance_sorting) && $geodir_distance_sorting == '1') echo 'checked="checked"'; ?>  />
                    </div>

                </li>


                <?php
                $show_sort_fields = ' style="display:none ;"';

                if (isset($geodir_distance_sorting) && $geodir_distance_sorting == '1')
                    $show_sort_fields = '';

                ?>

                <li class="geodir_distance_sort_options" <?php echo $show_sort_fields;?>>
                    <label for="search_as" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Select Nearest', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Select if you want to show option in distance sort.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <input type="checkbox" name="search_asc" id="search_asc"
                               value="1" <?php if (isset($search_asc) && $search_asc == '1') {
                            echo 'checked="checked"';
                        }?>/>

                        <img src="<?php echo geodir_plugin_url();?>/geodirectory-assets/images/arrow18x11.png"
                             class="field_sort_icon"/>
                        <input type="text" name="search_asc_title" id="search_asc_title"
                               value="<?php if (isset($search_asc_title)) {
                                   echo esc_attr($search_asc_title);
                               }?>" style="width:75%;"
                               placeholder="<?php esc_attr_e('Ascending title', 'geodiradvancesearch'); ?>"/>
                    </div>

                </li>



                <li class="geodir_distance_sort_options" <?php echo esc_attr($show_sort_fields);?>>
                    <label for="search_desc" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Select Farthest', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Select if you want to show option in distance sort.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <input type="checkbox" name="search_desc" id="search_desc"
                               value="1" <?php if (isset($search_desc) && $search_desc == '1') {
                            echo 'checked="checked"';
                        }?>/>

                        <img src="<?php echo geodir_plugin_url();?>/geodirectory-assets/images/down-arrow18x11.png"
                             class="field_sort_icon"/>
                        <input type="text" name="search_desc_title" id="search_desc_title"
                               value="<?php if (isset($search_desc_title)) {
                                   echo esc_attr($search_desc_title);
                               }?>" style="width:75%;"
                               placeholder="<?php esc_attr_e('Descending title', 'geodiradvancesearch'); ?>"/>
                        <br/>
                        <span><?php _e('Select if you want to show option in distance sort.', 'geodiradvancesearch');?></span>
                    </div>

                </li>


            <?php }

            if ($field_type == 'taxonomy' || $field_type == 'multiselect' || $field_type == 'select') {

                $show_operator_field = ' style="display:none ;"';

                if (isset($field_input_type) && $field_input_type == 'CHECK')
                    $show_operator_field = '';
                ?>

                <li class="gd-search-operator" <?php echo $show_operator_field;?>>
                    <?php $value = isset($field_info->front_search_title) ? $field_info->front_search_title: '';?>

                    <label for="data_type" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Search Operator', 'geodiradvancesearch'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('( Works with Checkbox type only. )  If AND is selected then the listing must contain all the selected options, if OR is selected then the listing must contain 1 selected item.', 'geodiradvancesearch'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">

                        <select name="search_operator" id="data_type" >
                            <option
                                value="AND" <?php selected($search_operator, 'AND'); ?>><?php _e('AND', 'geodiradvancesearch'); ?></option>
                            <option
                                value="OR" <?php selected($search_operator, 'OR'); ?>><?php _e('OR', 'geodiradvancesearch'); ?></option>
                        </select>
                    </div>

                </li>

            <?php } ?>



            <li>
                <?php $value = isset($field_info->front_search_title) ? $field_info->front_search_title: '';?>

                <label for="site_title" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Frontend  title', 'geodiradvancesearch'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('This is the text used for the advanced search field.', 'geodiradvancesearch'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">

                    <input type="text"
                           name="front_search_title" id="front_search_title"
                           value="<?php echo esc_attr( $value );?>"/>
                </div>

            </li>

            <li>
                <?php $value = isset($field_info->field_desc) ? $field_info->field_desc: '';?>

                <label for="field_desc" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Frontend description', 'geodiradvancesearch'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('This is the text used for the advanced search field.', 'geodiradvancesearch'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">

                    <input type="text" 
                           name="field_desc" id="field_desc"
                           value="<?php echo esc_attr( $value );?>"/>
                </div>

            </li>


            <li>

                <label for="save" class="gd-cf-tooltip-wrap">
                    <h3></h3>
                </label>
                <div class="gd-cf-input-wrap">

                    <input type="button" class="button button-primary" name="save" id="save"
                           value="<?php esc_attr_e('Save', 'geodiradvancesearch'); ?>"
                           onclick="save_advance_search_field('<?php echo $result_str; ?>')"/>
                    <input type="button" name="delete" value="<?php esc_attr_e('Delete', 'geodiradvancesearch'); ?>"
                           onclick="delete_advance_search_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name ?>')"
                           class="button"/>

                </div>
            </li>
        </ul>

    </div>
        </form>
</li>