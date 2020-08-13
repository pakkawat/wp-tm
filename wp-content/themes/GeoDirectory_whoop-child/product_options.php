<?php /* Template Name: Product options */?>
<?php

is_tamzang_admin();

if(empty($_REQUEST['pid']))
  wp_redirect(home_url()+'/?pid=2337');

$shop_id = $_REQUEST['pid'];

$query_args = array(
  'is_geodir_loop' => true,
  'post_type' => 'gd_product',
  'posts_per_page' => -1,
  'order_by' => 'default_category_ASC'
);

function where_product_without_category($where, $post_type){
  $where .= ' AND tamzang_category_id is NULL ';
  //echo "<h1>{$where}</h1>";
  return $where;
}

add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);
add_filter('geodir_filter_widget_listings_where', 'where_product_without_category', 11, 2);


$arrProducts_without_category = geodir_get_widget_listings($query_args);

function create_choice_group_html($choice_groups, $num_rows){
  global $current_user;
  $temp_group_id = 0;
  foreach ($choice_groups as $group) {
    if($temp_group_id == 0){
      echo '<li class="list-group-item count_options" data-oid="'.$group->cg_id.'" style="margin-top:20px;">
            <span>'.$group->group_title.'</span> - <span class="admin_note">'.$group->admin_note.'</span>
            <button data-feather="more-vertical" class="parent" style="float:right;"></button>
            <button data-feather="edit-2" class="edit-choice-group" style="float:right;" data-toggle="modal" data-target="#edit_choice_group"
            data-id="'.$group->cg_id.'" data-title="'.$group->group_title.'" data-sid="'.$group->shop_id.'" data-admin_note="'.$group->admin_note.'"
            data-is_optional="'.$group->is_optional.'" data-force_min="'.$group->force_min.'" data-force_max="'.$group->force_max.'"
            data-nonce="'.wp_create_nonce( 'edit_choice_group_'.$current_user->ID.$group->cg_id).'">
            </button>
            <button data-feather="copy" class="copy-choice-group" data-nonce="'.wp_create_nonce( 'copy_choice_group_'.$current_user->ID.$group->cg_id).'" style="float:right;margin-right: 30px;"></button>
            <button data-feather="x-circle" style="float:left;"
                data-nonce="'.wp_create_nonce( 'delete_option_'.$current_user->ID.$group->cg_id).'" class="delete-option">
                </button>';
      echo '<div class="child"><ul class="list-group sort_option ui-sortable" 
              data-nonce="'.wp_create_nonce( 'sort_options_'.$current_user->ID.$group->cg_id).'"
              data-oid="'.$group->cg_id.'">
            ';
    }else if($group->cg_id != $temp_group_id){

      echo '</ul>
      <div class="div-form" style="display: none;">
      <form class="add_option">
      <input type="hidden" name="oid" value="'.$temp_group_id.'"/>
      <input type="hidden" name="nonce" value="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$temp_group_id).'"/>
      <input type="hidden" name="sid" value="'.$_REQUEST['pid'].'"/>
      <div class="row">
        <div class="col">
          <input type="text" name="option_name" class="form-control" placeholder="ชื่อ">
        </div>
        <div class="col">
          <input type="text" name="option_value" class="form-control" placeholder="ราคา">
        </div>
        <div class="w-100"></div>
        <div class="col text-center">
          <button class="btn btn-outline-secondary cancel-add-choice">ยกเลิก</button>
        </div>
        <div class="col text-center">
          <input class="btn btn-outline-success" type="submit" value="บันทึก">
        </div>
      </div>
      </form>
      </div>
      <button class="btn btn-success add-choice" data-nonce="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$temp_group_id).'">add choice</button></div></li>';

      echo '<li class="list-group-item count_options" data-oid="'.$group->cg_id.'" style="margin-top:20px;">
      <span>'.$group->group_title.'</span> - <span class="admin_note">'.$group->admin_note.'</span>
            <button data-feather="more-vertical" class="parent" style="float:right;"></button>
            <button data-feather="edit-2" class="edit-choice-group" style="float:right;" data-toggle="modal" data-target="#edit_choice_group"
            data-id="'.$group->cg_id.'" data-title="'.$group->group_title.'" data-sid="'.$group->shop_id.'" data-admin_note="'.$group->admin_note.'"
            data-is_optional="'.$group->is_optional.'" data-force_min="'.$group->force_min.'" data-force_max="'.$group->force_max.'"
            data-nonce="'.wp_create_nonce( 'edit_choice_group_'.$current_user->ID.$group->cg_id).'">
            </button>
            <button data-feather="copy" class="copy-choice-group" data-nonce="'.wp_create_nonce( 'copy_choice_group_'.$current_user->ID.$group->cg_id).'" style="float:right;margin-right: 30px;"></button>
            <button data-feather="x-circle" style="float:left;"
                data-nonce="'.wp_create_nonce( 'delete_option_'.$current_user->ID.$group->cg_id).'" class="delete-option">
                </button>';
      echo '<div class="child"><ul class="list-group sort_option ui-sortable" 
              data-nonce="'.wp_create_nonce( 'sort_options_'.$current_user->ID.$group->cg_id).'"
              data-oid="'.$group->cg_id.'">
            ';
    }

    if(!empty($group->ca_id)){
      echo '<li class="list-group-item ui-sortable-handle clearfix" id="'.$group->ca_id.'" data-caid="'.$group->ca_id.'" >
            <div class="row">
              <div class="col option_name">
                '.$group->choice_adon_detail.'
              </div>
              <div class="col option_value">
                '.$group->extra_price.'
              </div>
              <div class="w-100"></div>
              <div class="col text-center">
                <button data-feather="edit-2" class="update-option" data-nonce="'.wp_create_nonce( 'update_product_options_'.$current_user->ID.$group->ca_id).'">
                </button>
              </div>
              <div class="col text-center">
                <button data-feather="x-circle" 
                data-nonce="'.wp_create_nonce( 'delete_product_options_'.$current_user->ID.$group->ca_id).'" class="delete-li">
                </button>
            </div>
            </div>
            </li>';
    }

    $temp_group_id = $group->cg_id;


  }

  if($num_rows > 0)
  {
    echo '</ul>
    <div class="div-form" style="display: none;">
    <form class="add_option">
    <input type="hidden" name="oid" value="'.$group->cg_id.'"/>
    <input type="hidden" name="nonce" value="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$group->cg_id).'"/>
    <input type="hidden" name="sid" value="'.$_REQUEST['pid'].'"/>
    <div class="row">
      <div class="col">
        <input type="text" name="option_name" class="form-control" placeholder="ชื่อ">
      </div>
      <div class="col">
        <input type="text" name="option_value" class="form-control" placeholder="ราคา">
      </div>
      <div class="w-100"></div>
      <div class="col text-center">
        <button class="btn btn-outline-secondary cancel-add-choice">ยกเลิก</button>
      </div>
      <div class="col text-center">
        <input class="btn btn-outline-success" type="submit" value="บันทึก">
      </div>
    </div>
    </form>
    </div>
    <button class="btn btn-success add-choice" data-nonce="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$group->cg_id).'">add choice</button></div></li>';
  }


}

function create_product_li($product_id,$product_title,$shop_id){
  global $wpdb, $current_user;
  if(empty($product_id))
    return;
  echo '<li class="list-group-item" data-pid="'.$product_id.'">';
  echo '<div class="card">';
  echo '<div class="card-header">';
  echo $product_title;
  echo '<a type="button" class="btn btn-secondary" style="float: right;" href="'. home_url('/add-listing/') . '?pid='.$product_id .'" target="_blank">Edit</a>';
  echo '<a type="button" class="btn btn-danger" href="#" style="float: right;margin-right: 10px;" data-id="'.$product_id.'" data-ptitle="'.$product_title.'" data-nonce="'.wp_create_nonce( 'delete_product_' . $current_user->ID.$product_id ).'" data-toggle="modal" data-target="#delete-product" >Delete</a>';
  echo '</div>';
  echo '<div class="card-body">';
  echo '<ul class="list-group product_options" data-pid="'.$product_id.'" 
        data-nonce="'.wp_create_nonce( 'sort_options_'.$current_user->ID.$product_id).'"
        style="padding: 20px;border: solid 1px red;">';
  // $choice_groups  = $wpdb->get_results(
  //   $wpdb->prepare(
  //       "SELECT cg.id as cg_id, cg.group_title, ca.id as ca_id, ca.choice_adon_detail, ca.extra_price
  //         FROM choice_group as cg
  //         LEFT JOIN choice_adons as ca
  //         ON cg.id = ca.choice_group_id
  //         WHERE cg.shop_id = %d AND cg.product_id = %d order by cg.orderby asc, ca.orderby asc ",
  //       array($shop_id,$product_id)
  //   )
  // );
  $choice_groups  = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT cg.id as cg_id, cg.group_title, cg.shop_id, cg.is_optional, cg.force_min, cg.force_max,
        ca.id as ca_id, ca.choice_adon_detail, ca.extra_price, cg.admin_note
        FROM choice_group as cg
        LEFT JOIN choice_adons as ca
        ON cg.id = ca.choice_group_id
        LEFT JOIN product_options as po
        ON po.choice_group_id = cg.id
        WHERE cg.shop_id = %d AND po.product_id = %d order by po.orderby asc, ca.orderby asc ",
        array($shop_id,$product_id)
    )
  );
  create_choice_group_html($choice_groups, $wpdb->num_rows);
  echo '</ul>';
  echo '</div>';
  echo '</div>';
  echo '</li>';
  echo '';

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>ตามสั่ง</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php echo '<script type="text/javascript">var ajaxurl = "' .admin_url('admin-ajax.php'). '";</script>';?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<style>
/* .sort_option ul li {
    display: block;
    float: left;
    height: 27px;
    list-style: none;
    margin-right: 20px;
} */
.clearfix::after {
  content: "";
  clear: both;
  display: table;
}

.group-number{
  display:none;
  position:absolute;
  top:15px;
  right:-10px;
  padding:1px 7px 0 7px;
  border-radius:10px;
  background-clip:padding-box;
  background-color:#59c15d;
  font-family:Arial,sans-serif;
  font-size:12px;
  font-weight:700;
  color:#fff
}

.msg_reload{
  background-color: #ffc107;
  border: solid 1px #c3c3c3;
}
</style>


<script>
jQuery(document).ready(function($){

  function update_order(nonce,parent_id,idsInOrder,table){
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': 'sort_options',
        'nonce': nonce,
        'parent_id': parent_id,
        'orders': idsInOrder,
        'table': table
      },
      success: function(msg){
        console.log( "return: " + JSON.stringify(msg) );
        // if(msg.success){
        //   console.log( "return: " + JSON.stringify(msg) );
        // }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  }

  $('.category_sort').sortable({
    update: function(ev, ui) {// ใช้ cate_id, position, action
        console.log("--start category_sort update--");
        // console.log("Cate_id:"+ui.item.context.dataset.cate);
        // console.log("New position: " + (ui.item.index()+1) );
        console.log($(this).data("nonce"));
        var idsInOrder = $(this).sortable("toArray", { attribute: 'data-cate' });
        console.log(idsInOrder);
        update_order($(this).data("nonce"),null,idsInOrder,'tamzang_catagory');
        console.log("--end category_sort update--");
    }
  });

  $('.sort_product').sortable({
    connectWith: '.sort_product',
    receive: function(ev, ui) {
        console.log("--start receive--");
        //ถ้า cate มีค่าคือการส่ง Product ไปหา Category แต่ถ้าไม่มีค่าคือส่งกลับ Product list
        if($(this).data("cate") != null)// ใช้ cate_id, pid, position, action
        {
          console.log("ตัวที่ได้รับ:"+$(this).data("cate"));
          var idsInOrder = $(this).sortable("toArray", { attribute: 'data-pid' });
          console.log(idsInOrder);
          update_order($(this).data("nonce"),$(this).data("cate"),idsInOrder,'sort_product');
        }else{// ใช้ pid, action
          console.log("ส่งกลับไป Product list");
          console.log($(this).data("nonce")+"-"+ui.item.attr('data-pid'));
          update_order($(this).data("nonce"),ui.item.attr('data-pid'),null,'remove_product');
        }

        console.log("--end receive--");
    },
    update: function(ev, ui) {
        
        if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("cate") != null))
        {// ใช้ pid, position, action
          console.log("--start update Category--");
          console.log("update Category:"+$(this).data("cate"));
          var idsInOrder = $(this).sortable("toArray", { attribute: 'data-pid' });
          console.log(idsInOrder);
          update_order($(this).data("nonce"),$(this).data("cate"),idsInOrder,'sort_product');
          console.log("--end update Category--");
        }
        
    }
  });


  // $('.product_options').sortable({
  //   connectWith: '.product_options',
  //   receive: function(ev, ui) {
  //       console.log("--start receive--");
  //       //ถ้า pid มีค่าคือ Option ส่งไปหา Product แต่ถ้าไม่มีค่าคือส่งกลับ option list
  //       if($(this).data("pid"))// ใช้ pid, oid, position, action
  //       {
  //         console.log("ตัวที่ได้รับ Product_id:"+$(this).data("pid"));
  //         console.log("v2 Option_id:"+ui.item.attr('data-oid'));
  //         var idsInOrder = $(this).sortable("toArray", { attribute: 'data-oid' });
  //         console.log(idsInOrder);
  //         update_order($(this).data("nonce"),$(this).data("pid"),idsInOrder,'choice_group');
  //       }else{// ใช้ oid, action
  //         update_order($(this).data("nonce"),ui.item.attr('data-oid'),null,'option');
  //         console.log("ส่งกลับ option list Option_id:"+ui.item.attr('data-oid'));
  //       }

  //       console.log("--end receive--");
  //   },
  //   update: function(ev, ui) {
  //       if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("pid") != null))
  //       {
  //         console.log("--start update product_id: "+$(this).data("pid")+" --");
  //         console.log("v2 Option_id:"+ui.item.attr('data-oid'));
  //         var idsInOrder = $(this).sortable("toArray", { attribute: 'data-oid' });
  //         console.log(idsInOrder);
  //         update_order($(this).data("nonce"),$(this).data("pid"),idsInOrder,'choice_group');
  //         console.log("--end update product_id: "+$(this).data("pid")+" --");
  //       }
  //   }
  // });

  $("#options").sortable({
      connectWith: ".product_options",
      forcePlaceholderSize: false,
      helper: function (e, li) {
          copyHelper = li.clone().insertAfter(li);
          return li.clone();
      },
      stop: function () {
          copyHelper && copyHelper.remove();
      }
  });
  $(".product_options").sortable({
      receive: function (e, ui) {
        copyHelper = null;
        var id =   $(this).find('li[data-oid="'+ui.item.attr('data-oid')+'"]'); 
        console.log(id.length);
        if(id.length > 1){
          ui.item.remove();
          return;
        }
        console.log("ตัวที่ได้รับ Product_id:"+$(this).data("pid"));
        console.log("v2 Option_id:"+ui.item.attr('data-oid'));
        var idsInOrder = $(this).sortable("toArray", { attribute: 'data-oid' });
        console.log(idsInOrder);
        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: {
            'action': 'insert_option',
            'nonce': $(this).data("nonce"),
            'product_id': $(this).data("pid"),
            'orders': idsInOrder,
            'choice_group_id': ui.item.attr('data-oid')
          },
          success: function(msg){
            console.log( "return: " + JSON.stringify(msg) );
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log(textStatus);
          }
        });
      },
      update: function(ev, ui) {
        if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("pid") != null))
        {
          console.log("--start update product_id: "+$(this).data("pid")+" --");
          console.log("v2 Option_id:"+ui.item.attr('data-oid'));
          var idsInOrder = $(this).sortable("toArray", { attribute: 'data-oid' });
          console.log(idsInOrder);
          update_order($(this).data("nonce"),$(this).data("pid"),idsInOrder,'sort_product_options');
          console.log("--end update product_id: "+$(this).data("pid")+" --");
        }
      }
  });


  function sortable_update( event, ui, ul ){
    console.log("--start sort option--");
    //console.log("New position: " + (ui.item.index()+1) );
    console.log(ul.data("oid"));
    console.log(ul.data("nonce"));
    //console.log(ui.item.context.id);
    console.log("test toArray");
    var idsInOrder = ul.sortable("toArray");
    console.log(idsInOrder);
    var parent_id = ul.data("oid");
    var nonce = ul.data("nonce");
    update_order(nonce,parent_id,idsInOrder,'choice_adons');
    console.log("--end sort option--");
    $(".msg_reload").slideDown("slow");
  }
  $( ".sort_option" ).sortable({
    items: "li:not(.unsortable)",
    update: function( event, ui ) {
      sortable_update( event, ui, $(this) );
    },
    sort: function(ev, ui) {
      $(this).css('border', '1px solid red');
    },
    stop: function(ev, ui) {
      $(this).css('border', 'none');
    }
    ,containment: "parent"
  }).disableSelection();

  jQuery(document).on("click", ".add-choice", function(event){
    event.preventDefault();
    // var li = $("<li>", {class: "ui-sortable-handle"});

    // var input = $("<input>", {"type": "text", "name": "option_name"});
    // li.append(input);
    // var input = $("<input>", {"type": "text", "name": "option_value"});
    // li.append(input);

    // var delete_button = $("<button>", {class: "delete-li"});
    // delete_button.append("x");

    // li.append(delete_button);

    // $(this).before(li);
    var nonce = $(this).data('nonce');
    $(this).toggle();
    $(this).siblings( "ul" ).toggle();
    $(this).siblings( "div" ).toggle();
    $(this).siblings( "div" ).find('input[name ="nonce"]').val(nonce);
    $(this).siblings( "div" ).find('input[name ="option_name"]').val(null);
    $(this).siblings( "div" ).find('input[name ="option_value"]').val(null);
    $(this).siblings( "div" ).find('input[name ="caid"]').remove();
  });

  jQuery(document).on("click", ".cancel-add-choice", function(event){
    event.preventDefault();
    // $(this).parents("div:first").toggle();
    // $(this).parents("div:first").prev().toggle();
    // $(this).parents("div:first").next().toggle();
    $(this).parents(".div-form").toggle();
    $(this).parents(".div-form").prev().toggle();
    $(this).parents(".div-form").next().toggle();

    $(this).parents(".add_option").find('input[name ="caid"]').remove();
  });

  jQuery(document).on("click", ".create-list", function(){
    var id = $(this).data('sid');
    var nonce = $(this).data('nonce');
    var group_title = $('#option_title').val();
    var admin_note = $('#admin_note').val();
    var optional = $('input[name="radioOptional"]:checked').val();
    var send_data = 'action=create_choice_group&sid='+id+'&group_title='+group_title+'&nonce='+nonce+'&optional='+optional+'&admin_note='+admin_note;
    
    if(optional == "0"){
      send_data += '&force_min='+$('#force_min').val()+'&force_max='+$('#force_max').val();
    }

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );
          var cList = $("<ul>", {class: "list-group sort_option ui-sortable",
                                "data-nonce": msg.data.nonce2,
                                "data-oid": msg.data.id});

          var li = $("<li>", {class: "list-group-item ui-sortable-handle count_options", "data-oid": msg.data.id});
          var div_child = $("<div>", {class: "child"});

          var div_form = $("<div>", {class: "div-form", style: "display:none;"});
          

          var button = $("<button>", {class: "btn btn-success add-choice", 
                                  "data-nonce": msg.data.nonce,
                                  text: "add choice"});
          var form = '<form class="add_option">' +
          '<input type="hidden" name="oid" value="'+msg.data.id+'">' +
          '<input type="hidden" name="nonce" value="'+msg.data.nonce+'">' +
          '<input type="hidden" name="sid" value="'+msg.data.sid+'">' +
          '<div class="row">' +
          '<div class="col">' +
          '<input type="text" name="option_name" class="form-control" placeholder="ชื่อ">' +
          '</div>' +
          '<div class="col">' +
          '<input type="text" name="option_value" class="form-control" placeholder="ราคา">' +
          '</div>' +
          '<div class="w-100"></div>' +
          '<div class="col text-center">' +
          '<button class="btn btn-outline-secondary cancel-add-choice">ยกเลิก</button>' +
          '</div>' +
          '<div class="col text-center">' +
          '<input class="btn btn-outline-success" type="submit" value="บันทึก">' +
          '</div>' +
          '</div>' +
          '</form>';

          //li.text($('#option_title').val());
          li.append(`<span>`+$('#option_title').val()+`</span> - <span class="admin_note">`+ admin_note + `</span>`);
          li.append(`<button data-feather="more-vertical" class="parent" style="float:right;"></button>`);
          li.append(`<button data-feather="edit-2" class="edit-choice-group" style="float:right;" data-toggle="modal" data-target="#edit_choice_group"
            data-id="`+msg.data.id+`" data-title="`+$('#option_title').val()+`" data-admin_note="`+$('#admin_note').val()+`" data-sid="`+msg.data.sid+`" 
            data-is_optional="`+msg.data.is_optional+`" data-force_min="`+msg.data.force_min+`" data-force_max="`+msg.data.force_max+`"
            data-nonce="`+msg.data.nonce4+`">
            </button>`);
          li.append(`<button data-feather="copy" class="copy-choice-group" data-nonce="`+msg.data.nonce5+`" class="parent" style="float:right;margin-right: 30px;"></button>`);
          li.append(`<button data-feather="x-circle" style="float:left;"
                data-nonce="`+msg.data.nonce3+`" class="delete-option">
                </button>`);
          li.css("margin-top", "20px");

          div_child.append(cList);
          div_form.append(form);
          div_child.append(div_form);
          div_child.append(button);
          //div_child.append();
          li.append(div_child);
          $('#options').append(li);
          feather.replace();
          $( ".sort_option" ).sortable({
            items: "li:not(.unsortable)",
            update: function( event, ui ) {
              sortable_update( event, ui, $(this) );
            },
            sort: function(ev, ui) {
              $(this).css('border', '1px solid red');
            },
            stop: function(ev, ui) {
              $(this).css('border', 'none');
            }
            ,containment: "parent"
          }).disableSelection();
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });

  });

  jQuery(document).on("click", ".delete-li", function(){
    var delete_li = $(this).closest('li');
    var choice_addon_id = delete_li.attr("id");
    console.log(delete_li.attr("id"));
    console.log($(this).data('nonce'));

    var choice_group = delete_li.parents('ul:first');
    console.log(choice_group.attr("data-oid"));

    var choice_group_id = choice_group.attr("data-oid");

    var id = delete_li.attr("id");
    var nonce = $(this).data('nonce');
    var send_data = 'action=delete_product_options&id='+id+'&nonce='+nonce;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );

          $('#cate_list > li').each(function(){
            $(this).find('li[data-oid="'+choice_group_id+'"]').each(function(){
              $(this).find('li[data-caid="'+choice_addon_id+'"]').remove();
            });
          });

          $('#product_list > li').each(function(){
            $(this).find('li[data-oid="'+choice_group_id+'"]').each(function(){
              $(this).find('li[data-caid="'+choice_addon_id+'"]').remove();
            });
          });

          $('#options').find('li[data-oid="'+choice_group_id+'"]').find('li[data-caid="'+choice_addon_id+'"]').remove();
          //delete_li.remove();
          
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  });

  jQuery(document).on("click", ".update-option", function(){
    var li = $(this).closest('li');
    var nonce = $(this).data('nonce');
    console.log(li.attr("id")+
    " option_name:"+$.trim(li.find( ".option_name" ).text())+
    " || option_value:"+$.trim(li.find( ".option_value" ).text()));
    var div = li.parents("ul:first").next();
    li.parents("ul:first").toggle();
    li.parents("ul:first").siblings( "button" ).toggle();
    div.toggle();
    var form = div.children();
    form.find('input[name ="option_name"]').val($.trim(li.find( ".option_name" ).text()));
    form.find('input[name ="option_value"]').val($.trim(li.find( ".option_value" ).text()));
    form.find('input[name ="nonce"]').val(nonce);
    if(form.find('input[name ="caid"]').length){
      form.find('input[name ="caid"]').val(li.attr("id"));
    }else{
      var input = $("<input>", {type: "hidden", name: "caid", value: li.attr("id")});
      form.prepend(input);
    }
    
  });

  function add_option(form, post_data){
    var current_li = form.parents('li:first')[0];
    
    console.log(post_data);
    var choice_group_id = 0;
    if(post_data[0].name == "caid"){
      choice_group_id = post_data[1].value;
      var nonce = post_data[2].value;
    }else{
      choice_group_id = post_data[0].value;
      var nonce = post_data[1].value;
    }

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': 'save_product_options',
        'nonce': nonce,
        'post_data': post_data
      },
      success: function(msg){
          console.log( "response: " + JSON.stringify(msg) );
          if(msg.success){
            if(msg.data.ca_id != null ){

              $('#cate_list > li').each(function(){
                $(this).find('li[data-oid="'+choice_group_id+'"]').each(function(){
                  $(this).children('div.child:first').children('ul:first').find('li[data-caid="'+msg.data.ca_id+'"]').find( ".option_name" ).text(msg.data.option_name);
                  $(this).children('div.child:first').children('ul:first').find('li[data-caid="'+msg.data.ca_id+'"]').find( ".option_value" ).text(msg.data.option_value);
                });
              });

              $('#product_list > li').each(function(){
                $(this).find('li[data-oid="'+choice_group_id+'"]').each(function(){
                  $(this).children('div.child:first').children('ul:first').find('li[data-caid="'+msg.data.ca_id+'"]').find( ".option_name" ).text(msg.data.option_name);
                  $(this).children('div.child:first').children('ul:first').find('li[data-caid="'+msg.data.ca_id+'"]').find( ".option_value" ).text(msg.data.option_value);
                });
              });

              var li = $('#options').find('li[data-oid="'+choice_group_id+'"]');
              li.children('div.child:first').children('ul:first').find('li[data-caid="'+msg.data.ca_id+'"]').find( ".option_name" ).text(msg.data.option_name);
              li.children('div.child:first').children('ul:first').find('li[data-caid="'+msg.data.ca_id+'"]').find( ".option_value" ).text(msg.data.option_value);

            }else{
              var li = $("<li>", {class: "list-group-item ui-sortable-handle", id:msg.data.id, "data-caid": msg.data.id});

              var div_row = $("<div>", {class: "row"});

              var div = $("<div>", {class: "col option_name"});
              div.append(msg.data.option_name);
              div_row.append(div);
              var div = $("<div>", {class: "col option_value"});
              div.append(msg.data.option_value);
              div_row.append(div);

              div_row.append($("<div>", {class: "w-100"}));

              var div_center = $("<div>", {class: "col text-center"});

              var edit_button = $("<button>", {class: "update-option", 
                                                "data-nonce": msg.data.nonce, 
                                                "data-feather": "edit-2"});
              div_center.append(edit_button);
              div_row.append(div_center);

              var div_center = $("<div>", {class: "col text-center"});
              var delete_button = $("<button>", {class: "delete-li",
                                    "data-nonce": msg.data.nonce2,
                                    "data-feather": "x-circle"});

              div_center.append(delete_button);
              div_row.append(div_center);

              li.append(div_row);
              form.parents("div:first").prev().append(li.clone());// option ได้เพิ่มลง list แล้ว ณ ตำแหน่งที่เพิ่ม
              $('#cate_list > li').each(function(){
                $(this).find('li[data-oid="'+choice_group_id+'"]').not(current_li).each(function(){
                  $(this).children('div.child:first').children('ul:first').append(li.clone());
                });
              });

              $('#product_list > li').each(function(){
                $(this).find('li[data-oid="'+choice_group_id+'"]').not(current_li).each(function(){
                  $(this).children('div.child:first').children('ul:first').append(li.clone());
                });
              });

              var options_li = $('#options').find('li[data-oid="'+choice_group_id+'"]').not(current_li);
              if(options_li){ // ถ้าหาเขอแสดงว่าเป็นการเพิ่ม option จาก category หรือ product
                options_li.children('div.child:first').children('ul:first').append(li.clone());
              }
              feather.replace();
            }

            form.parents("div:first").toggle();
            form.parents("div:first").prev().toggle();
            form.parents("div:first").next().toggle();
          }

      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(textStatus);
      }
    });
  }

  jQuery(document).on('submit', '.add_option', function(event) {
  //$(".add_option").submit(function(event){
    event.preventDefault();
    var form = $(this);
    
    //console.log($( this ).serializeArray());
    console.log("--add_option---");
    // var test = $( this ).serializeArray();
    // $.each( test, function( key, value ) {
    //   console.log( key + ": name-" + value.name + " || value-" + value.value );
    // });
    var post_data = $( this ).serializeArray();
    add_option(form, post_data);

  });

  jQuery(document).on("click", ".delete-option", function(){
    var delete_li = $(this).parents('li:first');
    console.log("data-oid:"+delete_li.attr("data-oid"));
    console.log("data-pid:"+delete_li.parents('ul:first').attr("data-pid"));
    console.log($(this).data('nonce'));


    var choice_group_id = delete_li.attr("data-oid");
    var product_id = delete_li.parents('ul:first').attr("data-pid");
    if(jQuery.type(delete_li.parents('ul:first').attr("data-pid")) === "undefined"){
      product_id = 0;
    }
    var nonce = $(this).data('nonce');
    var send_data = 'action=delete_option&choice_group_id='+choice_group_id+'&product_id='+product_id+'&nonce='+nonce;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
        console.log( "return: " + JSON.stringify(msg) );
        if(msg.success){
          if(product_id == 0){
            $('li[data-oid="'+choice_group_id +'"]').each(function() {
                $(this).remove();   
            });
          }else{
            delete_li.remove();
          }
          
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  });

  jQuery(document).on('submit', '.edit_category_form', function(event) {
    event.preventDefault();
    var form = $(this);
    var post_data = $( this ).serializeArray();
    console.log(post_data);
    console.log(post_data[1].value);
    var nonce = post_data[1].value;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': 'edit_category',
        'nonce': nonce,
        'post_data': post_data
      },
      success: function(msg){
        //console.log( "return: " + JSON.stringify(msg) );
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );
          form.parents(".child").prevAll("span").text(msg.data.category_name);
          form.parents(".div-form").toggle();
          form.parents(".div-form").prev().toggle();
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  });

  jQuery(document).on("click", ".edit-category", function(){
    $(this).siblings( "div" ).children( "ul" ).toggle();
    $(this).siblings( "div" ).children( "div" ).toggle();
    //$(this).siblings( "div" ).slideToggle();
  });

  jQuery(document).on("click", ".cancel-edit-category", function(event){
    event.preventDefault();
    $(this).parents(".div-form").toggle();
    $(this).parents(".div-form").prev().toggle();
  });

  //jQuery(document).on("click", ".add_category", function(){
  jQuery(document).on('submit', '.add_category', function(event) {
    event.preventDefault();
    //var ul = $(this).parents('ul:first');
    var post_data = $( this ).serializeArray();
    console.log(post_data);
    console.log(post_data[0].value);
    var nonce3 = post_data[0].value;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': 'create_category',
        'nonce': nonce3,
        'post_data': post_data
      },
      success: function(msg){
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );

          var li = $("<li>", {class: "list-group-item", "data-cate": msg.data.id});


          var delete_button = $("<button>", {class: "delete-category", style: "float:left;margin-right:10px;",
                          "data-nonce": msg.data.nonce2,
                          "data-feather": "x-circle"});
          var edit_button = $("<button>", {class: "edit-category", style: "float:right;margin-right:10px;",
                          "data-feather": "edit-2"});
          var more_button = $("<button>", {class: "parent", style: "float:right;margin-right:10px;",
                            "data-feather": "more-vertical"});
          var visibility_button = $("<button>", {class: "edit-category", style: "float:right;margin-right:10px;",
                            "data-feather": "eye", "data-toggle": "modal", "data-target": "#category_visibility",
                            "data-id": msg.data.id, "data-visibility": "1", "data-nonce": msg.data.nonce3,
                            "data-hide_until_date": "", "data-hide_until_time": "", "data-show_only_from": "",
                            "data-show_only_to": "", "data-day_of_week": "0" });

          var div_child = $("<div>", {class: "child", style: "display:none;"});



          var List = $("<ul>", {class: "list-group sort_product ui-sortable",
                                      "data-nonce": msg.data.nonce, style: "padding: 20px;border: solid 1px blue;",
                                      "data-oid": msg.data.id});

          var div_form = $("<div>", {class: "div-form", style: "display:none;"});
          var form = '<form class="edit_category_form">' +
                '<input type="hidden" name="oid" value="'+msg.data.id+'">' +
                '<input type="hidden" name="nonce" value="'+msg.data.nonce+'">' +
                '<input type="hidden" name="sid" value="'+msg.data.sid+'">' +
                '<div class="row">' +
                '<input type="text" name="category_name" class="form-control" placeholder="Category name" value="'+msg.data.cat_name+'">' +
                '</div>' +
                '<div class="row">' +
                '<input type="text" name="category_description" class="form-control" placeholder="Category description" value="'+msg.data.cat_des+'">' +
                '</div>' +
                '<div class="row">' +
                '<button class="btn btn-outline-secondary cancel-edit-category">ยกเลิก</button>' +
                '<input class="btn btn-outline-success" type="submit" value="บันทึก">' +
                '</div>' +
                '</form>';

          li.append('<span>'+msg.data.cat_name+'</span>');
          
          li.append(more_button);
          li.append(edit_button);
          li.append(visibility_button);
          li.append(delete_button);

          div_child.append(List);
          div_form.append(form);
          div_child.append(div_form);

          li.append(div_child);

          $('#cate_list').append(li);
          feather.replace();
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  });

  jQuery(document).on("click", ".delete-category", function(){
    var delete_li = $(this).parents('li:first');
    console.log(delete_li.attr("data-cate"));
    console.log($(this).data('nonce'));

    var id = delete_li.attr("data-cate");
    var nonce = $(this).data('nonce');
    var send_data = 'action=delete_category&id='+id+'&nonce='+nonce;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );
          delete_li.remove();
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  });

  $('.child').hide();
  $('[data-toggle="popover"]').popover();
  jQuery(document).on('click', '.parent', function(event) {
    //$(this).data('feather') = "more-horizontal";
    // $(this).attr("data-feather", "hmore-horizontal");
    // if($(this).text() == "+")
    //   $(this).text("-");
    // else
    //   $(this).text("+");
    //$(this).next().slideToggle();
    $(this).siblings( "div" ).slideToggle();
  });

  jQuery(document).on('click', '.btn-group .btn', function(event) {
    event.preventDefault();
    var radio = $(this).find("input");
    radio.attr('checked', 'checked');
    $(this).addClass('active').siblings("label").removeClass('active');
    if(radio.val() == "0"){
      //$(this).siblings( "div" ).show("fast");
      $(this).parents("div:first").next().show("fast");
    }else{
      //$(this).siblings( "div" ).hide("fast");
      $(this).parents("div:first").next().hide("fast");
    }
  });

  $('#preview-modal').on('show.bs.modal', function(e) {
    $( '#tamzang-preview-modal' ).attr( 'src', function ( i, val ) { return val; });
  });

  $('#edit_choice_group').on('show.bs.modal', function(e) {
    var data = $(e.relatedTarget.dataset);
    data = JSON.parse(JSON.stringify(data))[0];
    console.log(data);
    $(this).find('input[name=id]').val(data.id);
    $(this).find('input[name=nonce]').val(data.nonce);
    $(this).find('input[name=sid]').val(data.sid);
    $(this).find('input[name=group_title]').val(data.title);
    $(this).find('input[name=admin_note]').val(data.admin_note);
    $(this).find('input[name=force_min]').val(data.force_min);
    $(this).find('input[name=force_max]').val(data.force_max);
    if(data.is_optional == "1"){
      $(this).find('#radio_optional').prop("checked", true);
      $(this).find('#radio_mandatory').prop("checked", false);
    }else{
      $(this).find('#radio_optional').prop("checked", false);
      $(this).find('#radio_mandatory').prop("checked", true);
    }
  });

  jQuery(document).on('submit', '.form_edit_choice_group', function(event) {
    event.preventDefault();
    var post_data = $(this).serializeArray();
    console.log(post_data);
    var nonce = post_data[6].value;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': 'edit_choice_group',
        'nonce': nonce,
        'post_data': post_data
      },
      success: function(msg){
        console.log( "return: " + JSON.stringify(msg) );
        if(msg.success){
          //console.log( "return: " + JSON.stringify(msg) );
          var li = $("#options").find('li[data-oid="'+post_data[5].value +'"]').children(".edit-choice-group");
          //console.log(li);
          $("#options").find('li[data-oid="'+post_data[5].value +'"]').children("span").text(post_data[0].value);
          $("#options").find('li[data-oid="'+post_data[5].value +'"]').children("span.admin_note").text(post_data[4].value);
          li.attr("data-title", post_data[0].value);
          li.attr("data-is_optional", post_data[1].value);
          li.attr("data-force_min", post_data[2].value);
          li.attr("data-force_max", post_data[3].value);
          li.attr("data-admin_note", post_data[4].value);

          $('#cate_list > li').each(function(){
            $(this).find('li[data-oid="'+post_data[5].value+'"]').each(function(){
              $(this).children("span").text(post_data[0].value);
              var li = $(this).children(".edit-choice-group");
              li.attr("data-title", post_data[0].value);
              li.attr("data-is_optional", post_data[1].value);
              li.attr("data-force_min", post_data[2].value);
              li.attr("data-force_max", post_data[3].value);
              li.attr("data-admin_note", post_data[4].value);
            });
          });

          $('#product_list > li').each(function(){
            $(this).find('li[data-oid="'+post_data[5].value+'"]').each(function(){
              $(this).children("span").text(post_data[0].value);
              var li = $(this).children(".edit-choice-group");
              li.attr("data-title", post_data[0].value);
              li.attr("data-is_optional", post_data[1].value);
              li.attr("data-force_min", post_data[2].value);
              li.attr("data-force_max", post_data[3].value);
              li.attr("data-admin_note", post_data[4].value);
            });
          });
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });

    $('#edit_choice_group').modal('toggle');

  });

  $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });

  $(document).on('click', '.cb_visibility', function() {
    if(parseInt($(this).val()) > 2){
      $(this).parents("div:first").next().toggle();
    }
    $('.cb_visibility').not(this).each(function() {
      $(this).prop('checked', false);
      if($(this).val() == "2")
      return;
      var div = $(this).parents("div:first").next();
      if(div.is(':visible')){
        div.toggle();
      }
    });
  });

  $('#category_visibility').on('show.bs.modal', function(e) {
    var data = $(e.relatedTarget.dataset);
    data = JSON.parse(JSON.stringify(data))[0];
    console.log(data);
    $(this).find('input:checkbox[name="visibility"]').prop('checked',false);
    $(this).find('input:checkbox[name="day_of_week[]"]').prop('checked',false);
    $(this).find('.hide_div').hide();
    $(this).find('input[name=id]').val(data.id);
    $(this).find('input[name=nonce]').val(data.nonce);
    var cb = $(this).find('input:checkbox[name="visibility"][value="' + data.visibility + '"]');
    cb.prop('checked', true);
    if(data.visibility > 2)
      cb.parents("div:first").next().toggle();
    
    $(this).find('input[name=hide_until_date]').val(data.hide_until_date);
    $(this).find('select[name=hide_until_time]').val(data.hide_until_time.substring(0, 5));
    $(this).find('select[name=show_only_from]').val(data.show_only_from.substring(0, 5));
    $(this).find('select[name=show_only_to]').val(data.show_only_to.substring(0, 5));
    $(this).find('input:checkbox[id="day_of_week_M"]').prop('checked',(data.day_of_week&2));
    $(this).find('input:checkbox[id="day_of_week_T"]').prop('checked',(data.day_of_week&4));
    $(this).find('input:checkbox[id="day_of_week_W"]').prop('checked',(data.day_of_week&8));
    $(this).find('input:checkbox[id="day_of_week_R"]').prop('checked',(data.day_of_week&16));
    $(this).find('input:checkbox[id="day_of_week_F"]').prop('checked',(data.day_of_week&32));
    $(this).find('input:checkbox[id="day_of_week_U"]').prop('checked',(data.day_of_week&64));
    $(this).find('input:checkbox[id="day_of_week_S"]').prop('checked',(data.day_of_week&1));

    if(data.shop_time){
      $(this).find('input[name=shop_time]').val(1);
    }else{
      $(this).find('input[name=shop_time]').val('');
    }
    // $(this).find('input[name=sid]').val(data.sid);
    // $(this).find('input[name=group_title]').val(data.title);
    // $(this).find('input[name=force_min]').val(data.force_min);
    // $(this).find('input[name=force_max]').val(data.force_max);
    // if(data.is_optional == "1"){
    //   $(this).find('#radio_optional').prop("checked", true);
    //   $(this).find('#radio_mandatory').prop("checked", false);
    // }else{
    //   $(this).find('#radio_optional').prop("checked", false);
    //   $(this).find('#radio_mandatory').prop("checked", true);
    // }
  });

  jQuery(document).on('submit', '.form_edit_category_visibility', function(event) {
    event.preventDefault();
    var post_data = $(this).serializeArray();
    console.log(post_data);
    var visibility = 1;
    var day_of_week = 0;
    if(post_data[2].name == "visibility" || post_data[1].name == "visibility"){
      visibility = 4;
    }
    var dataObj = {};
    $(post_data).each(function(i, field){
      if(visibility == 4 && field.name == "day_of_week[]"){
        day_of_week += parseInt(field.value);
      }else{
        dataObj[field.name] = field.value;
      }
    });
    if(visibility == 4){
      dataObj['day_of_week'] = day_of_week;
    }
    //console.log(dataObj['visibility']);
    console.log(dataObj);
    var action = 'edit_category_visibility';
    if(dataObj['shop_time'])
      action = 'edit_shop_time';

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': action,
        'nonce': dataObj['nonce'],
        'post_data': dataObj
      },
      success: function(msg){
        //console.log( "return: " + JSON.stringify(msg) );
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );
          if(dataObj['shop_time']){
            var li = $("#shop_time_btn");
          }else{
            var li = $("#cate_list").find('li[data-cate="'+dataObj['id'] +'"]').children(".edit-category");
          }
          
          if($.isEmptyObject( dataObj['visibility'] )){
            li.attr("data-visibility", 1);
          }else{
            li.attr("data-visibility", dataObj['visibility']);
          }
          
          if(dataObj['visibility'] == "3"){
            li.attr("data-hide_until_date", dataObj['hide_until_date']);
            li.attr("data-hide_until_time", dataObj['hide_until_time']);
          }else if(dataObj['visibility'] == "4"){
            li.attr("data-show_only_from", dataObj['show_only_from']);
            li.attr("data-show_only_to", dataObj['show_only_to']);
            li.attr("data-day_of_week", dataObj['day_of_week']);
          }
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });

    $('#category_visibility').modal('toggle');

  });

  // $(document).on('mouseover', '.count_options', function() {
  //   console.log($(this));
  // });

  $(document).on({
    mouseenter: function () {
        var oid = $(this).data("oid");
        $(this).css('background-color', '#59c15d');
        $('#cate_list > li').each(function(){
          var count = 0;
          $(this).find('li[data-oid="'+oid+'"]').each(function(){
            $(this).css('background-color', '#59c15d');
            count++;
          });
          if(count > 0){
            $(this).children('div.group-number:first').text(count);
            $(this).children('div.group-number:first').show();
          }
        });
    },
    mouseleave: function () {
        var oid = $(this).data("oid");
        $(this).css('background-color', '');
        $('#cate_list > li').each(function(){
          $(this).find('li[data-oid="'+oid+'"]').each(function(){
            $(this).css('background-color', '');
          });
          $(this).children('div.group-number:first').hide();
        });
    }
  }, ".count_options"); //pass the element as an argument to .on

  $('#shop_image').change(function() {
    var file = $(this).get(0).files[0];
    //var preview = $('#preview');
    var img = document.createElement('img');
    img.src = window.URL.createObjectURL(file);
    $('#preview_shop_image').html(img);
    var reader = new FileReader();
    reader.onload = function(e) {
        window.URL.revokeObjectURL(this.src);
    }
    reader.readAsDataURL(file);
    $('#preview_shop_image img').css({'width':'200px'});
  });

  jQuery(document).on('submit', '.shop_image_form', function(event) {
    event.preventDefault();
    var post_data = new FormData(this);

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: post_data,
      processData: false,
      contentType: false,
      success: function(msg){
        console.log( "return: " + JSON.stringify(msg) );
        if(msg.success){
          $('#upload-modal').modal('toggle');
        }else{
          $('#preview_shop_image').html(msg.data);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });

  });

  jQuery(document).on('keypress', 'input[name ="option_value"]', function(event) {
    if(event.which == 13){
      console.log("enter");
      var form = $(this).parents('form:first');
      var post_data = form.serializeArray();
      add_option(form, post_data);
      return false;
    }
  });

  jQuery(document).on("click", ".copy-choice-group", function(){
    var li = $(this).parents('li:first');
    console.log(li.attr("data-oid"));
    console.log($(this).data('nonce'));

    var id = li.attr("data-oid");
    var nonce = $(this).data('nonce');
    var send_data = 'action=copy_choice_group&id='+id+'&nonce='+nonce;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
        console.log( "return: " + JSON.stringify(msg) );
        if(msg.success){
          location.reload();
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log(textStatus);
      }
    });
  });

  $('#delete-product').on('show.bs.modal', function(e) {
    var data = $(e.relatedTarget).data();
    $('.title', this).text(data.ptitle);
    $('.btn-ok', this).data('id', data.id);
    $('.btn-ok', this).data('nonce', data.nonce);
    $('.btn-ok', this).data('product', $(e.relatedTarget));
    console.log(data);
  });

  $('#delete-product').on('click', '.btn-ok', function(e) {
    var id = $(this).data('id');
    var nonce = $(this).data('nonce');
    var delete_btn = $(this).data('product');
    console.log(id);
    console.log(nonce);
    console.log(delete_btn);
    var send_data = 'action=delete_product&id='+id+'&nonce='+nonce;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
            console.log( "Data deleted: " + JSON.stringify(msg) );
            if(msg.success)
              delete_btn.parents('li:first').remove();
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         console.log(textStatus);
      }
    });

    $(e.delegateTarget).modal('hide');
  });

});

</script>
<body>

<div class="container" style="margin:10px;max-width:1920px;">
<?php
global $wpdb;
$shop_time = $wpdb->get_row(
  $wpdb->prepare(
      "SELECT * FROM tamzang_shop_time where shop_id = %d ", array($shop_id)
  )
);

?>
<div class="row">
  <div class="col text-center">
    <?php echo '<h2><a href="'.get_permalink( $shop_id ).'">'.get_the_title( $shop_id ).'</a>';?>
    <?php
      echo '<button data-feather="eye" id="shop_time_btn" style="margin-right:10px;"  data-toggle="modal" data-target="#category_visibility"';
      echo' data-id="'.$shop_id.'" data-visibility="'.$shop_time->visibility.'" data-shop_time="1" ';
      echo ' data-nonce="'.wp_create_nonce( 'edit_shop_time_'.$current_user->ID.$shop_id).'" ';
      echo ' data-hide_until_date="'.$shop_time->hide_until_date.'" data-hide_until_time="'.$shop_time->hide_until_time.'" ';
      echo ' data-show_only_from="'.$shop_time->show_only_from.'" data-show_only_to="'.$shop_time->show_only_to.'" ';
      echo ' data-day_of_week="'.$shop_time->day_of_week.'" ';
      echo '/></h2>';
    ?>
  </div>
</div>
<div class="row msg_reload" style="display: none;">
  <div class="col text-center">
    กรุณารีโหลด
  </div>
</div>
<style>
@media (min-width: 992px) {
  /* .modal-dialog {
    width: 900px;
  } */
  .modal-dialog .modal-content .modal-body{
    height: 900px;
  }
}
</style>
<div class="modal fade" id="preview-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header" style="border-bottom: 0 none;padding: 15px 15px 0 15px;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="myModalLabel">
            <?php get_the_title( $shop_id )
            ?></h4>
          </div>
          <div class="modal-body" style="padding:0;">


          <iframe id="tamzang-preview-modal" src="<?php echo home_url('/tamzang_menu/'); ?>?pid=<?php echo $shop_id;?>" height="100%" width="100%"></iframe>


          </div>
          <div class="modal-footer">
          </div>
      </div>
  </div>
</div>
<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#preview-modal">
Preview
</button>


<div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Upload</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding:0;height: 400px;">
          <form class="shop_image_form" name="shop_image_form" enctype="multipart/form-data">
            <label>รูปภาพ<span>*</span> </label>
            <input id="shop_image" name="shop_image" type="file" />
            <div id="preview_shop_image"></div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="shop_id" value="<?php echo $_REQUEST['pid']; ?>"  />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'upload_shop_image_'.$current_user->ID.$_REQUEST['pid']); ?>"  />
            <input type="hidden" name="action" value="upload_shop_image"  />
            <button type="submit" class="btn btn-success">บันทึก</button>
          </div>
          </form>
      </div>
  </div>
</div>
<button type="button" class="btn btn-success" data-toggle="modal" data-target="#upload-modal">
Upload
</button>
<div class="row">
  <div class="col">
    <div><h4>Category</h4></div>
    <ul id="cate_list" class="list-group category_sort" 
    data-nonce="<?php echo wp_create_nonce( 'category_sort_'.$current_user->ID); ?>">
        <!-- <li class="list-group-item" data-cate="cate_1">Category 1
          <ul data-cate="cate_1" class="list-group sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
        <li class="list-group-item" data-cate="cate_2">Category 2
          <ul data-cate="cate_2" class="list-group sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
        <li class="list-group-item" data-cate="cate_3">Category 3
          <ul data-cate="cate_3" class="list-group sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li> -->
        <?php 

        function create_category_visibility_btn($category){
          global $current_user;
          $return = '';
          $return .= '<button data-feather="eye" class="edit-category" style="float:right;margin-right:10px;"  data-toggle="modal" data-target="#category_visibility"';
          $return .= ' data-id="'.$category->id.'" data-visibility="'.$category->visibility.'" ';
          $return .= ' data-nonce="'.wp_create_nonce( 'edit_category_visibility_'.$current_user->ID.$category->id).'" ';
          $return .= ' data-hide_until_date="'.$category->hide_until_date.'" data-hide_until_time="'.$category->hide_until_time.'" ';
          $return .= ' data-show_only_from="'.$category->show_only_from.'" data-show_only_to="'.$category->show_only_to.'" ';
          $return .= ' data-day_of_week="'.$category->day_of_week.'" ';
          $return .= '/>';

          return $return;
        }
          $categories = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT tc.* , p.post_id, p.post_title
                FROM tamzang_catagory as tc
                LEFT JOIN wp_geodir_gd_product_detail as p
                ON tc.id = p.tamzang_category_id
                WHERE tc.shop_id = %d
                ORDER BY tc.orderby asc, p.orderby asc",
                array($shop_id)
            )
          );
          $num_rows = $wpdb->num_rows; 
          $temp_cate_id = 0;
          $temp_name = '';
          $temp_description = '';

          // $temp_product_id = 0;
          // $temp_product_title = '';

          foreach ( $categories as $category ){
            if($temp_cate_id == 0){
              echo '<li class="list-group-item" data-cate="'.$category->id.'" >
              <span>'.$category->name.'</span><div class="group-number">0</div>
              <button data-feather="more-vertical" class="parent" style="float:right;margin-right:10px;"></button>
              <button data-feather="edit-2" class="edit-category" style="float:right;margin-right:10px;">
              '.create_category_visibility_btn($category).'
              <button data-feather="x-circle" class="delete-category" 
              data-nonce="'.wp_create_nonce( 'delete_category_'.$current_user->ID.$category->id).'"
              style="float:left;margin-right:10px;">
              </button>
              ';
              echo '<div class="child"><ul class="list-group sort_product" 
                      data-nonce="'.wp_create_nonce( 'sort_options_'.$current_user->ID.$category->id).'"
                      data-cate="'.$category->id.'" style="padding: 20px;border: solid 1px blue;"> ';
              create_product_li($category->post_id,$category->post_title,$shop_id);
            }else if($category->id == $temp_cate_id){
              create_product_li($category->post_id,$category->post_title,$shop_id);
            }else{
              echo '</ul>
              <div class="div-form" style="display: none;">
              <form class="edit_category_form">
              <input type="hidden" name="cate_id" value="'.$temp_cate_id.'"/>
              <input type="hidden" name="nonce" value="'.wp_create_nonce( 'edit_category_'.$current_user->ID.$temp_cate_id).'"/>
              <input type="hidden" name="sid" value="'.$_REQUEST['pid'].'"/>
              <div class="row">
                <input type="text" name="category_name" class="form-control" placeholder="Category name" value="'.$temp_name.'">
              </div>
              <div class="row">
                <input type="text" name="category_description" class="form-control" placeholder="Category description"  value="'.$temp_description.'">
              </div>
              <div class="row">
                <button class="btn btn-outline-secondary cancel-edit-category">ยกเลิก</button>
                <input class="btn btn-outline-success" type="submit" value="บันทึก">
              </div>
              </form>
              </div>
              </div>
              </li>';
              echo '<li class="list-group-item" data-cate="'.$category->id.'" >
              <span>'.$category->name.'</span><div class="group-number">0</div>
              <button data-feather="more-vertical" class="parent" style="float:right;margin-right:10px;"></button>
              <button data-feather="edit-2" class="edit-category" style="float:right;margin-right:10px;">
              '.create_category_visibility_btn($category).'
              <button data-feather="x-circle" class="delete-category" 
              data-nonce="'.wp_create_nonce( 'delete_category_'.$current_user->ID.$category->id).'"
              style="float:left;margin-right:10px;">
              </button>
              ';
              echo '<div class="child"><ul class="list-group sort_product" 
                      data-nonce="'.wp_create_nonce( 'sort_options_'.$current_user->ID.$category->id).'"
                      data-cate="'.$category->id.'" style="padding: 20px;border: solid 1px blue;"> ';
              create_product_li($category->post_id,$category->post_title,$shop_id);
            }

            $temp_cate_id = $category->id;
            $temp_name = $category->name;
            $temp_description = $category->description;
            // $temp_product_id = $category->post_id;
            // $temp_product_title = $category->post_title;
          }

          if($num_rows > 0){

            echo '</ul>
            <div class="div-form" style="display: none;">
            <form class="edit_category_form">
            <input type="hidden" name="cate_id" value="'.$temp_cate_id.'"/>
            <input type="hidden" name="nonce" value="'.wp_create_nonce( 'edit_category_'.$current_user->ID.$temp_cate_id).'"/>
            <input type="hidden" name="sid" value="'.$_REQUEST['pid'].'"/>
            <div class="row">
              <input type="text" name="category_name" class="form-control" placeholder="Category name" value="'.$temp_name.'">
            </div>
            <div class="row">
              <input type="text" name="category_description" class="form-control" placeholder="Category description"  value="'.$temp_description.'">
            </div>
            <div class="row">
              <button class="btn btn-outline-secondary cancel-edit-category">ยกเลิก</button>
              <input class="btn btn-outline-success" type="submit" value="บันทึก">
            </div>
            </form>
            </div>
            </div>
            </li>';
          }
        ?>
    </ul>

    <form class="add_category" style="margin-top: 10px;">
      <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'create_category_'.$current_user->ID); ?>"/>
      <input type="hidden" name="id" value="<?php echo $_REQUEST['pid']; ?>"/>
      <div class="card">
        <div class="card-body">
          <div class="form-group">
            <input type="text" class="form-control" name="category_name" placeholder="Category name">
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="category_description" placeholder="Category description">
          </div>
        </div>
        <div class="card-footer bg-transparent">
          <input class="btn btn-outline-success" type="submit" value="Add Category">
        </div>
      </div>
    </form>

  </div>

  <div class="col">
    <div><h4>Product <a href="<?php echo home_url('/product-list/') . '?pid='.$_REQUEST['pid'];?>" style="float: right;" target="_blank">หน้าแก้ไขของร้านค้า</a></h4></div>
    <ul id="product_list" class="list-group sort_product" style="padding: 20px;border: solid 1px green;"
    data-nonce="<?php echo wp_create_nonce( 'remove_option_from_parent_'.$current_user->ID); ?>"
    >
      <?php 
      foreach ( $arrProducts_without_category as $product ){
        // echo '<li class="list-group-item" data-pid="'.$product->ID.'">';
        // echo '<div class="card">';
        // echo '<div class="card-header">';
        // echo $product->post_title;
        // echo '</div>';
        // echo '<div class="card-body">';
        // echo '<ul class="list-group product_options" data-pid="'.$product->ID.'" 
        //       data-nonce="'.wp_create_nonce( 'sort_options_'.$current_user->ID.$product->ID).'"
        //       style="padding: 20px;border: solid 1px red;">';
        // $choice_groups  = $wpdb->get_results(
        //   $wpdb->prepare(
        //       "SELECT cg.id as cg_id, cg.group_title, ca.id as ca_id, ca.choice_adon_detail, ca.extra_price
        //         FROM choice_group as cg
        //         LEFT JOIN choice_adons as ca
        //         ON cg.id = ca.choice_group_id
        //         WHERE cg.shop_id = %d AND cg.product_id = %d order by cg.orderby ",
        //       array($shop_id,$product->ID)
        //   )
        // );
        // create_choice_group_html($choice_groups, $wpdb->num_rows);
        // echo '</ul>';
        // echo '</div>';
        // echo '</div>';
        // echo '</li>';
        // echo '';

        create_product_li($product->ID,$product->post_title,$shop_id);
      }
      ?>
    </ul>
  </div>

  <div class="col">
    <div><h4>Option</h4></div>
    <div class="form-group">
      <input type="text" id="option_title" placeholder="Eg. Extra toppings, Choose type">
    </div>
    <div id="optional" class="btn-group btn-group-toggle form-group" data-toggle="buttons">
      <label class="btn btn-outline-secondary">
        <input type="radio" autocomplete="off" name="radioOptional" checked value="1"> optional
      </label>
      <label class="btn btn-outline-secondary">
        <input type="radio" autocomplete="off" name="radioOptional" value="0"> mandatory
      </label>
    </div>
    <div style="display: none">
      <div class="form-group">
        <label>min</label> <input type="number" id="force_min">
      </div>
      <div class="form-group">
        <label>max</label> <input type="number" id="force_max">
      </div>
    </div>
    <div class="form-group">
      <input type="text" id="admin_note" placeholder="Note">
    </div>
    <!-- <input type="radio" autocomplete="off" name="radiooptional" checked value="1"> optional
    <input type="radio" autocomplete="off" name="radiooptional" value="0"> mandatory -->
    <div class="form-group">
      <button class="btn btn-primary create-list" 
      data-sid="<?php echo $_REQUEST['pid']; ?>" 
      data-nonce="<?php echo wp_create_nonce( 'create_choice_group_'.$current_user->ID); ?>"
      >add</button>
    </div>

    <ul id="options" class="list-group product_options" style="padding: 20px;border: solid 1px blue;"
    data-nonce="<?php echo wp_create_nonce( 'remove_option_from_parent_'.$current_user->ID); ?>"
    >
    <?php 

      $choice_groups  = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT cg.id as cg_id, cg.group_title, cg.shop_id, cg.is_optional, cg.force_min, cg.force_max,
            ca.id as ca_id, ca.choice_adon_detail, ca.extra_price, ca.orderby, cg.admin_note
              FROM choice_group as cg
              LEFT JOIN choice_adons as ca
              ON cg.id = ca.choice_group_id
              WHERE cg.shop_id = %d order by cg.id asc, ca.orderby asc ",
            array($shop_id)
        )
      );
      create_choice_group_html($choice_groups, $wpdb->num_rows);

    ?>
    </ul>

  </div>



</div><!-- <div class="row"> -->

<!-- Modal -->
<div class="modal fade" id="edit_choice_group" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content"> 
      <form class="form_edit_choice_group">
        <div class="modal-body" style="height: 400px;">
          <div class="form-group">
            <label for="group_title">Title</label>
            <input type="text" class="form-control" name="group_title" placeholder="Eg. Extra toppings, Choose type">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="radioOptional" id="radio_optional" value="1">
            <label class="form-check-label">
              optional
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="radioOptional" id="radio_mandatory" value="0">
            <label class="form-check-label">
              mandatory
            </label>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Min</label>
              <input type="number" class="form-control" name="force_min" placeholder="min">
            </div>
            <div class="form-group col-md-6">
              <label>Max</label>
              <input type="number" class="form-control" name="force_max" placeholder="max">
            </div>
          </div>
          <div class="form-group">
            <label for="admin_note">Note</label>
            <input type="text" class="form-control" name="admin_note">
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" value=""/>
          <input type="hidden" name="nonce" value=""/>
          <input type="hidden" name="sid" value=""/>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <input class="btn btn-success" type="submit" value="Save changes">
        </div>
      </form>
    </div>
  </div>
</div>


<?php

function get_times( $default = '08:00', $interval = '+15 minutes' ) {

  $output = '';

  $current = strtotime( '00:00' );
  $end = strtotime( '23:59' );

  while( $current <= $end ) {
      $time = date( 'H:i', $current );
      $sel = ( $time == $default ) ? ' selected' : '';

      $output .= "<option value=\"{$time}\"{$sel}>" . $time .'</option>';
      $current = strtotime( $interval, $current );
  }

  return $output;
}

?>
<!-- Modal -->
<div class="modal fade" id="category_visibility" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >visibility</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="form_edit_category_visibility">
        <div class="modal-body" style="height: 400px;">
          <div class="form-check">
            <input class="form-check-input cb_visibility" type="checkbox" value="2" name="visibility" id="Hide_1">
            <label class="form-check-label" for="Hide_1">
              Hide
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input cb_visibility" type="checkbox" value="3" name="visibility" id="Hide_until">
            <label class="form-check-label" for="Hide_until">
              Hide until
            </label>
          </div>
          <div class="form-group hide_div" style="display: none;">
            Hide until
            <input type="text" class="datepicker" name="hide_until_date">
            <select name="hide_until_time"><?php echo get_times(); ?></select>
          </div>
          <div class="form-check">
            <input class="form-check-input cb_visibility" type="checkbox" value="4" name="visibility" id="Show_only_from">
            <label class="form-check-label" for="Show_only_from">
              Show only from
            </label>
          </div>
          <div class="form-group hide_div" style="display: none;"><br>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="From">From</label>
                <select name="show_only_from"><?php echo get_times(); ?></select>
              </div>
              <div class="form-group col-md-6">
                <label for="To">To</label>
                <select name="show_only_to"><?php echo get_times(); ?></select>
              </div>
            </div>
            <div class="row">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_M"
                          value="2" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_M">
                          Monday
                        </label>
                      </div>
                    </td>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_T"
                          value="4" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_T">
                          Tuesday
                        </label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_W"
                          value="8" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_W">
                          Wednesday
                        </label>
                      </div>
                    </td>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_R"
                          value="16" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_R">
                          Thursday
                        </label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_F"
                          value="32" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_F">
                          Friday
                        </label>
                      </div>
                    </td>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_U"
                          value="64" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_U">
                          Saturday
                        </label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day_of_week_S"
                          value="1" name="day_of_week[]">
                        <label class="form-check-label" for="day_of_week_S">
                          Sunday
                        </label>
                      </div>
                    </td>
                    <td>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" value=""/>
          <input type="hidden" name="nonce" value=""/>
          <input type="hidden" name="shop_time" value=""/>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <input class="btn btn-success" type="submit" value="Save changes">
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="delete-product" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Delete</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding:0;height: 400px;">
            <p>คุณกำลังจะลบสินค้า <b><i class="title"></i></b> ออกจากร้าน</p>
            <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
          </div>
      </div>
  </div>
</div>

</div><!-- <div class="container"> -->
<script>
  feather.replace();
</script>
</body>
</html>
