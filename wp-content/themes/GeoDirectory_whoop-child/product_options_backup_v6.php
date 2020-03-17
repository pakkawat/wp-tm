<?php /* Template Name: Product options */?>
<?php

if(empty($_REQUEST['sid']))
  wp_redirect(home_url()+'/?sid=2337');

$shop_id = $_REQUEST['sid'];

get_header();?>
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
<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>

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
    $(this).parents("div:first").toggle();
    $(this).parents("div:first").prev().toggle();
    $(this).parents("div:first").next().toggle();
    $(this).siblings('input[name ="caid"]').remove();
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
      url: geodir_var.geodir_ajax_url,
      data: send_data,
      success: function(msg){
        if(msg.success){
          console.log( "return: " + JSON.stringify(msg) );
          var cList = $("<ul>", {class: "sort_option ui-sortable", "data-oid": msg.data});

          cList.append('<form class="add_option"><li class="unsortable"><button class="add-li">add</button><input type="submit" value="save"></li></form>');

          var li = $("<li>", {"data-oid": msg.data});
          li.text($('#option_title').val()+" Option "+msg.data);
          li.append('<button class="parent" style="float:right;">+</button>');
          li.css("margin-top", "20px");
          li.append(cList);
          $('#options').append(li);

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
    " option_name:"+$.trim(li.children( ".option_name" ).text())+
    " || option_value:"+$.trim(li.children( ".option_value" ).text()));
    var div = li.parents("ul:first").next();
    li.parents("ul:first").toggle();
    li.parents("ul:first").siblings( "button" ).toggle();
    div.toggle();
    var form = div.children();
    form.find('input[name ="option_name"]').val($.trim(li.children( ".option_name" ).text()));
    form.find('input[name ="option_value"]').val($.trim(li.children( ".option_value" ).text()));
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
              li.children( ".option_name" ).text(msg.data.option_name);
              li.children( ".option_value" ).text(msg.data.option_value);
            }else{
              var li = $("<li>", {class: "ui-sortable-handle", id:msg.data.id, "data-caid": msg.data.id});

              var div = $("<div>", {class: "order-col-3 option_name"});
              div.append(msg.data.option_name);
              li.append(div);
              var div = $("<div>", {class: "order-col-3 option_value"});
              div.append(msg.data.option_value);
              li.append(div);

              var div_6 = $("<div>", {class: "order-col-6"});
              var edit_button = $("<button>", {class: "update-option", "data-nonce": msg.data.nonce});
              edit_button.append('<i class="fa fa-pencil"></i>');
              div_6.append(edit_button);

              var delete_button = $("<button>", {class: "delete-li"});
              delete_button.append("x");

              div_6.append(delete_button);

              li.append(div_6);
              form.parents("div:first").prev().append(li);
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
    console.log($(this).text());
    if($(this).text() == "+")
      $(this).text("-");
    else
      $(this).text("+");
    $(this).next().slideToggle();
  });

  jQuery(document).on('click', '.btn-group .btn', function(event) {
    event.preventDefault();
    var radio = $(this).find("input");
    radio.attr('checked', 'checked');
    $(this).addClass('active').siblings("label").removeClass('active');
    if(radio.val() == "0"){
      $(this).siblings( "div" ).show("fast");
    }else{
      $(this).siblings( "div" ).hide("fast");
    }
  });


});

</script>
<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">


<div class="order-row">
  <div class="order-col-4">
    <ul id="list1" data-pid="l1" class="category_sort">
        <li data-cate="cate_1">Category 1
          <ul data-cate="cate_1" class="sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
        <li data-cate="cate_2">Category 2
          <ul data-cate="cate_2" class="sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
        <li data-cate="cate_3">Category 3
          <ul data-cate="cate_3" class="sort_product" style="padding: 10px;border: solid 1px blue;"></ul>
        </li>
    </ul>
  </div>
  <div class="order-col-4">
    <ul class="sort_product" style="padding: 10px;border: solid 1px green;">
      <li data-pid="product_1">
        <div class="card">
          <div class="card-header">
            Product 1
          </div>
          <div class="card-body">
            <ul class="product_options" data-pid="product_1" style="padding: 20px;border: solid 1px red;">
            </ul>
          </div>
        </div>
      </li>

      <li data-pid="product_2">
        <div class="card">
          <div class="card-header">
            Product 2
          </div>
          <div class="card-body">
            <ul class="product_options" data-pid="product_2" style="padding: 20px;border: solid 1px red;">
            </ul>
          </div>
        </div>
      </li>
    </ul>
  </div>
  <div class="order-col-4">

    <input type="text" id="option_title">
    <div id="optional" class="btn-group" data-toggle="buttons">
      <label class="btn btn-default active">
        <input type="radio" autocomplete="off" name="radioOptional" checked value="1"> optional
      </label>
      <label class="btn btn-default">
        <input type="radio" autocomplete="off" name="radioOptional" value="0"> mandatory
      </label>
      <div style="display: none">
        min <input type="number" id="force_min">
        max <input type="number" id="force_max">
      </div>
    </div>
    <!-- <input type="radio" autocomplete="off" name="radiooptional" checked value="1"> optional
    <input type="radio" autocomplete="off" name="radiooptional" value="0"> mandatory -->
    <button class="create-list" 
    data-sid="<?php echo $_REQUEST['sid']; ?>" 
    data-nonce="<?php echo wp_create_nonce( 'create_choice_group_'.$current_user->ID); ?>"
    >add</button>

    <ul id="options" class="product_options" style="padding: 20px;border: solid 1px blue;">
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
          echo '<li data-oid="'.$group->cg_id.'" style="margin-top:20px;">
                '.$group->group_title.'
                <button class="parent" style="float:right;">+</button>';
          echo '<div class="child"><ul class="sort_option ui-sortable" data-oid="'.$group->cg_id.'">
                ';
        }else if($group->cg_id != $temp_group_id){

          echo '</ul>
          <div style="display: none;">
          <form class="add_option">
          <input type="hidden" name="oid" value="'.$temp_group_id.'"/>
          <input type="hidden" name="nonce" value="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$temp_group_id).'"/>
          <input type="hidden" name="sid" value="'.$_REQUEST['sid'].'"/>
          <input type="text" name="option_name">
          <input type="text" name="option_value">
          <button class="cancel-add-choice">cancel</button>
          <input type="submit" value="save">
          </form>
          </div>
          <button class="add-choice" data-nonce="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$temp_group_id).'">add choice</button></div></li>';

          echo '<li data-oid="'.$group->cg_id.'" style="margin-top:20px;">
                '.$group->group_title.'
                <button class="parent" style="float:right;">+</button>';
          echo '<div class="child"><ul class="sort_option ui-sortable" data-oid="'.$group->cg_id.'">
                ';
        }

        if(!empty($group->ca_id)){
          echo '<li class="ui-sortable-handle clearfix" id="'.$group->ca_id.'" data-caid="'.$group->ca_id.'" >
                <div class="order-col-3 option_name">
                  '.$group->choice_adon_detail.'
                </div>
                <div class="order-col-3 option_value">
                  '.$group->extra_price.'
                </div>
                <div class="order-col-6">
                  <button class="update-option" data-nonce="'.wp_create_nonce( 'update_product_options_'.$current_user->ID.$group->ca_id).'">
                    <i class="fa fa-pencil"></i>
                  </button>
                  <button class="delete-li">x</button>
                </div>
                </li>';
        }

        $temp_group_id = $group->cg_id;


      }

      if($wpdb->num_rows > 0)
        echo '</ul>
        <div style="display: none;">
        <form class="add_option">
        <input type="hidden" name="oid" value="'.$group->cg_id.'"/>
        <input type="hidden" name="nonce" value="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$group->cg_id).'"/>
        <input type="hidden" name="sid" value="'.$_REQUEST['sid'].'"/>
        <input type="text" name="option_name">
        <input type="text" name="option_value">
        <button class="cancel-add-choice">cancel</button>
        <input type="submit" value="save">
        </form>
        </div>
        <button class="add-choice" data-nonce="'.wp_create_nonce( 'save_product_options_'.$current_user->ID.$group->cg_id).'">add choice</button></div></li>';
    ?>
    </ul>

  </div>
</div>
<div class="order-clear"></div>






    </div>
  </div>
</div>
<?php get_footer();?>
