<?php /* Template Name: Product options */?>
<?php

//is_tamzang_admin();

if(empty($_REQUEST['sid']))
  wp_redirect(home_url()+'/?sid=2337');

$shop_id = $_REQUEST['sid'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>ตามสั่ง</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php echo '<script type="text/javascript">var ajaxurl = "' .admin_url('admin-ajax.php'). '";</script>';?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
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
</style>


<script>
jQuery(document).ready(function($){


  $('.category_sort').sortable({
    update: function(ev, ui) {// ใช้ cate_id, position, action
        console.log("--start category_sort update--");
        console.log("Cate_id:"+ui.item.context.dataset.cate);
        console.log("New position: " + (ui.item.index()+1) );
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
          console.log(ui.item.context.dataset.pid);
          console.log("New position: " + (ui.item.index()+1) );
        }else{// ใช้ pid, action
          console.log("ส่งกลับไป Product list");
        }

        console.log("--end receive--");
    },
    update: function(ev, ui) {
        
        if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("cate") != null))
        {// ใช้ pid, position, action
          console.log("--start update Category--");
          console.log(ui.item.context.dataset.pid);
          console.log("New position: " + (ui.item.index()+1) );
          console.log("--end update Category--");
        }
        
    }
  });


  $('.product_options').sortable({
    connectWith: '.product_options',
    receive: function(ev, ui) {
        console.log("--start receive--");
        //ถ้า pid มีค่าคือ Option ส่งไปหา Product แต่ถ้าไม่มีค่าคือส่งกลับ option list
        if($(this).data("pid"))// ใช้ pid, oid, position, action
        {
          console.log("ตัวที่ได้รับ Product_id:"+$(this).data("pid"));
          console.log("v2 Option_id:"+ui.item.context.dataset.oid);
          console.log("New position: " + (ui.item.index()+1) );
        }else{// ใช้ oid, action
          console.log("ส่งกลับ option list Option_id:"+ui.item.context.dataset.oid);
        }

        console.log("--end receive--");
    },
    update: function(ev, ui) {
        if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("pid") != null))
        {
          console.log("--start update product_id: "+$(this).data("pid")+" --");
          console.log("v2 Option_id:"+ui.item.context.dataset.oid);
          console.log("New position: " + (ui.item.index()+1) );
          console.log("--end update product_id: "+$(this).data("pid")+" --");
        }
    }
  });



  function sortable_update( event, ui, ul ){
    console.log("--start sort option--");
    console.log("New position: " + (ui.item.index()+1) );
    console.log(ul.data("oid"));
    console.log(ui.item.context.id);
    console.log("test toArray");
    var idsInOrder = ul.sortable("toArray");
    console.log(idsInOrder);
    console.log("--end sort option--");
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
    var optional = $('input[name="radioOptional"]:checked').val();
    var send_data = 'action=create_choice_group&sid='+id+'&group_title='+group_title+'&nonce='+nonce+'&optional='+optional;
    
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
          var cList = $("<ul>", {class: "list-group sort_option ui-sortable", "data-oid": msg.data.id});

          var li = $("<li>", {class: "list-group-item ui-sortable-handle", "data-oid": msg.data.id});
          var div_child = $("<div>", {class: "child"});

          var div_form = $("<div>", {class: "div-form", style: "display:none;"});
          

          var button = $("<div>", {class: "btn btn-success add-choice", 
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
          li.text($('#option_title').val());
          li.append(`<button data-feather="more-vertical" class="parent" style="float:right;"></button>`);
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
    console.log(delete_li.attr("id"));
    delete_li.remove();

    // var id = $(this).data('pid');
    // var nonce = $(this).data('nonce');
    // var send_data = 'action=delete_product_options&pid='+id+'&nonce='+nonce;
    // $.ajax({
    //   type: "POST",
    //   url: geodir_var.geodir_ajax_url,
    //   data: send_data,
    //   success: function(msg){
    //     if(msg.success){
    //       console.log( "return: " + JSON.stringify(msg) );
    //     }
    //   },
    //   error: function(XMLHttpRequest, textStatus, errorThrown) {
    //       console.log(textStatus);
    //   }
    // });
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
    var input = $("<input>", {type: "hidden", name: "caid", value: li.attr("id")});
    form.prepend(input);
  });

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
    if(post_data[0].name == "caid")
      var nonce = post_data[2].value;
    else
      var nonce = post_data[1].value;
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
              var li = form.parents("div:first").prev().find('li#'+msg.data.ca_id);
              li.find( ".option_name" ).text(msg.data.option_name);
              li.find( ".option_value" ).text(msg.data.option_value);
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
              var delete_button = $("<button>", {class: "delete-li", "data-feather": "x-circle"});

              div_center.append(delete_button);
              div_row.append(div_center);

              li.append(div_row);
              form.parents("div:first").prev().append(li);
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

  });

  $('.child').hide();
  jQuery(document).on('click', '.parent', function(event) {
    //$(this).data('feather') = "more-horizontal";
    // $(this).attr("data-feather", "hmore-horizontal");
    // if($(this).text() == "+")
    //   $(this).text("-");
    // else
    //   $(this).text("+");
    $(this).next().slideToggle();
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


});

</script>
<body>

<div class="container">

<div class="row">
  <div class="col text-center">
    <?php echo '<h2><a href="'.get_permalink( $shop_id ).'">'.get_the_title( $shop_id ).'</a></h2>';?>
  </div>
</div>

<div class="row">
  <div class="col">
    <ul id="list1" data-pid="l1" class="list-group category_sort">
        <li class="list-group-item" data-cate="cate_1">Category 1
          <ul data-cate="cate_1" class="list-group sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
        <li class="list-group-item" data-cate="cate_2">Category 2
          <ul data-cate="cate_2" class="list-group sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
        <li class="list-group-item" data-cate="cate_3">Category 3
          <ul data-cate="cate_3" class="list-group sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
    </ul>
  </div>

  <div class="col">
    <ul class="list-group sort_product" style="padding: 10px;border: solid 1px green;">
      <li class="list-group-item" data-pid="product_1">
        <div class="card">
          <div class="card-header">
            Product 1
          </div>
          <div class="card-body">
            <ul class="list-group product_options" data-pid="product_1" style="padding: 20px;border: solid 1px red;">
            </ul>
          </div>
        </div>
      </li>

      <li class="list-group-item" data-pid="product_2">
        <div class="card">
          <div class="card-header">
            Product 2
          </div>
          <div class="card-body">
            <ul class="list-group product_options" data-pid="product_2" style="padding: 20px;border: solid 1px red;">
            </ul>
          </div>
        </div>
      </li>
    </ul>
  </div>

  <div class="col">

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
    <!-- <input type="radio" autocomplete="off" name="radiooptional" checked value="1"> optional
    <input type="radio" autocomplete="off" name="radiooptional" value="0"> mandatory -->
    <div class="form-group">
      <button class="btn btn-primary create-list" 
      data-sid="<?php echo $_REQUEST['sid']; ?>" 
      data-nonce="<?php echo wp_create_nonce( 'create_choice_group_'.$current_user->ID); ?>"
      >add</button>
    </div>

    <ul id="options" class="list-group product_options" style="padding: 20px;border: solid 1px blue;">
    <?php 

      $choice_groups  = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT cg.id as cg_id, cg.group_title, ca.id as ca_id, ca.choice_adon_detail, ca.extra_price
              FROM choice_group as cg
              LEFT JOIN choice_adons as ca
              ON cg.id = ca.choice_group_id
              WHERE cg.shop_id = %d AND cg.product_id IS NULL order by cg.id ",
            array($shop_id)
        )
      );

      $temp_group_id = 0;
      foreach ($choice_groups as $group) {
        if($temp_group_id == 0){
          echo '<li class="list-group-item" data-oid="'.$group->cg_id.'" style="margin-top:20px;">
                '.$group->group_title.'
                <button data-feather="more-vertical" class="parent" style="float:right;"></button>';
          echo '<div class="child"><ul class="list-group sort_option ui-sortable" data-oid="'.$group->cg_id.'">
                ';
        }else if($group->cg_id != $temp_group_id){

          echo '</ul>
          <div class="div-form" style="display: none;">
          <form class="add_option">
          <input type="hidden" name="oid" value="'.$temp_group_id.'"/>
          <input type="hidden" name="nonce" value="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$temp_group_id).'"/>
          <input type="hidden" name="sid" value="'.$_REQUEST['sid'].'"/>
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

          echo '<li class="list-group-item" data-oid="'.$group->cg_id.'" style="margin-top:20px;">
                '.$group->group_title.'
                <button data-feather="more-vertical" class="parent" style="float:right;"></button>';
          echo '<div class="child"><ul class="list-group sort_option ui-sortable" data-oid="'.$group->cg_id.'">
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
                    <button data-feather="x-circle" class="delete-li">
                    </button>
                </div>
                </div>
                </li>';
        }

        $temp_group_id = $group->cg_id;


      }

      if($wpdb->num_rows > 0)
        echo '</ul>
        <div class="div-form" style="display: none;">
        <form class="add_option">
        <input type="hidden" name="oid" value="'.$group->cg_id.'"/>
        <input type="hidden" name="nonce" value="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$group->cg_id).'"/>
        <input type="hidden" name="sid" value="'.$_REQUEST['sid'].'"/>
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
    ?>
    </ul>

  </div>



</div><!-- <div class="row"> -->


</div><!-- <div class="container"> -->
<script>
  feather.replace();
</script>
</body>
</html>
