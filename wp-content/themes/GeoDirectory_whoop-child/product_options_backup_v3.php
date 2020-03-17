<?php /* Template Name: Product options */?>
<?php

$pid = $_REQUEST['pid'];

get_header();?>

<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>

<script>

// $( function() {
//     $( "#sortable" ).sortable();
//     $( "#sortable" ).disableSelection();
// });
jQuery(document).ready(function($){


  $('.category_sort').sortable({
    update: function(ev, ui) {
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
        if($(this).data("cate") != null)
        {
          console.log("ตัวที่ได้รับ:"+$(this).data("cate"));
          console.log(ui.item.context.dataset.pid);
          console.log("New position: " + (ui.item.index()+1) );
        }else{
          console.log("ส่งกลับไป Product list");
        }

        console.log("--end receive--");
    },
    update: function(ev, ui) {
        
        if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("cate") != null))
        {
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
        if($(this).data("pid"))
        {
          console.log("ตัวที่ได้รับ Product_id:"+$(this).data("pid"));
          //console.log(ui.item.context);
          //console.log("Option_id:"+$(ui.item.context).children("ul").data("oid"));
          console.log("v2 Option_id:"+ui.item.context.dataset.oid);
          console.log("New position: " + (ui.item.index()+1) );
        }else{
          console.log("ส่งกลับ option list Option_id:"+ui.item.context.dataset.oid);
        }

        console.log("--end receive--");
    },
    update: function(ev, ui) {
        if (!ui.sender && this === ui.item.parent()[0] && ($(this).data("pid") != null))
        {
          console.log("--start update product_id: "+$(this).data("pid")+" --");
          //console.log(ui.item.context);
          //console.log("Option_id:"+$(ui.item.context).children("ul").data("oid"));
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

  // $('.product_options').sortable({
  //   connectWith: '.product_options',
  //   receive: function(ev, ui) {
  //       console.log("--start receive--");
  //       //ถ้า pid มีค่าคือส่งระหว่าง Product กับ Product แต่ถ้าไม่มีค่าคือส่งกลับ option list
  //       if($(this).data("pid")){
  //         console.log("update options product_id:"+$(this).data("pid"));
  //         console.log("option_id: " + ui.item.context.id);
  //         console.log("New position: " + (ui.item.index()+1) );
  //       }else{
  //         console.log("delete product_id from this option_id: " + ui.item.context.id);
  //       }

  //       console.log("--end receive--");
  //   },
  //   update: function(ev, ui) {

  //     if (!ui.sender && this === ui.item.parent()[0]){
  //       console.log("--start update product option--");
  //       console.log("update options product_id:"+$(this).data("pid"));
  //       console.log("option_id: " + ui.item.context.id);
  //       console.log("New position: " + (ui.item.index()+1) );
  //       console.log("--end update--");
  //     }

  //   }
  // }).disableSelection();

  jQuery(document).on("click", ".add-li", function(event){
    event.preventDefault();
    // var cList = $(this).parent();
    // cList.append('<li class="ui-sortable-handle" id="aaa">xxx</li>');

    //var li = '<li class="ui-sortable-handle" id="aaa">xxx<button class="delete-li">x</button></li>';
    var li = $("<li>", {class: "ui-sortable-handle", "id": "xxxxx"});
    li.append("test text");

    var input = $("<input>", {"type": "text", "name": "option_name"});
    li.append(input);
    var input = $("<input>", {"type": "text", "name": "option_value"});
    li.append(input);

    var delete_button = $("<button>", {class: "delete-li"});
    delete_button.append("x");

    li.append(delete_button);

    $(this).before(li);
  });

  jQuery(document).on("click", ".create-list", function(){
    console.log("click");
    var test_id = Math.floor((Math.random() * 1000) + 1);
    //var cList = $('ul.sortable.ui-sortable')
    //var form_list = $("<form>", {class: "add_option"});

    //console.log('<h3>'+$('#option_title').val()+'-'+$('#option_type').val()+'</h3>');
    var cList = $("<ul>", {class: "sort_option ui-sortable child", "data-oid": test_id});
    // for (i = 1; i < 8; i++) {
    //   // var li = $('<li/>')
    //   //   .addClass('ui-sortable-handle')
    //   //   .attr('id', i)
    //   //   .text("Item "+i)
    //   //   .appendTo(cList);
    //   cList.append('<li class="ui-sortable-handle" id="'+i+'">งง '+i+'<button class="delete-li">x</button></li>');
    // }
    cList.append('<form class="add_option"><li class="unsortable"><button class="add-li">add</button><input type="submit" value="save"></li></form>');
    //form_list.append(cList);
    //$('#options').append('<hr>');

    //var li = $('<li/>').text($('#option_title').val()+" Option "+test_id);
    var li = $("<li>", {"data-oid": test_id});
    li.text($('#option_title').val()+" Option "+test_id);
    li.append('<button class="parent" style="float:right;">+</button>');
    li.css("margin-top", "20px");
    li.append(cList);
    $('#options').append(li);
    //form_list.before('<h3>'+$('#option_title').val()+'-'+$('#option_type').val()+'</h3>');

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
  });

  jQuery(document).on("click", ".delete-li", function(){
    var delete_li = $(this).closest('li');
    console.log(delete_li.attr("id"));
    delete_li.remove();
  });

  jQuery(document).on('submit', '.add_option', function(event) {
  //$(".add_option").submit(function(event){
    event.preventDefault();
    //console.log($( this ).serializeArray());
    console.log("-----");
    var test = $( this ).serializeArray();
    $.each( test, function( key, value ) {
      console.log( key + ": name-" + value.name + " || value-" + value.value );
    });

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        'action': 'test_product_options',
        'post_data': $( this ).serializeArray()
      },
      success: function(msg){
          console.log( "response: " + JSON.stringify(msg) );
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
    <select id="option_type">
      <option value="select">select</option>
      <option value="text_area">text_area</option>
    </select>
    <button class="create-list" >add</button>

    <ul id="options" class="product_options" style="padding: 20px;border: solid 1px blue;"></ul>

  </div>
</div>
<div class="order-clear"></div>






    </div>
  </div>
</div>
<?php get_footer();?>
