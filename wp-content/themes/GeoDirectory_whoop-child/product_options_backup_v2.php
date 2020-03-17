<?php /* Template Name: Product options */ ?>
<?php 

$pid = $_REQUEST['pid'];

get_header(); ?>

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

  $('.product_options').sortable({
    connectWith: '.product_options',
    receive: function(ev, ui) {
        console.log("--start receive--");
        //ถ้า pid มีค่าคือส่งระหว่าง Product กับ Product แต่ถ้าไม่มีค่าคือส่งกลับ option list
        if($(this).data("pid")){
          console.log("update options product_id:"+$(this).data("pid"));
          console.log("option_id: " + ui.item.context.id);
          console.log("New position: " + (ui.item.index()+1) );
        }else{
          console.log("delete product_id from this option_id: " + ui.item.context.id);
        }
        
        console.log("--end receive--");
    },
    update: function(ev, ui) {
        
      if (!ui.sender && this === ui.item.parent()[0]){
        console.log("--start update product option--");
        console.log("update options product_id:"+$(this).data("pid"));
        console.log("option_id: " + ui.item.context.id);
        console.log("New position: " + (ui.item.index()+1) );
        console.log("--end update--");
      }
             
    }
  }).disableSelection();

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
    //var cList = $('ul.sortable.ui-sortable')
    //var form_list = $("<form>", {class: "add_option"});
    
    //console.log('<h3>'+$('#option_title').val()+'-'+$('#option_type').val()+'</h3>');
    var cList = $("<ul>", {class: "sort_option ui-sortable child", "data-oid": "xxxxx"});
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
    var li = $('<li/>').text($('#option_title').val()+" Option "+Math.floor((Math.random() * 1000) + 1));
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

    <!-- <ul class="sortable" data-oid="1">
  <li id="81">Item 1</li>
  <li id="82">Item 2<button class="delete-li">x</button></li>
  <li id="83">Item 3</li>
  <li id="84">Item 4</li>
  <li id="85">Item 5<button class="delete-li">x</button></li>
  <li id="86">Item 6</li>
  <li id="87">Item 7<button class="delete-li">x</button></li>
</ul>

<br>
<hr>
<br>

<ul class="sortable" data-oid="2">
  <li id="91">Item 1<button class="delete-li">x</button></li>
  <li id="92">Item 2</li>
  <li id="93">Item 3<button class="delete-li">x</button></li>
  <li id="94">Item 4</li>
  <li id="95">Item 5<button class="delete-li">x</button></li>
  <li id="96">Item 6</li>
  <li id="97">Item 7</li>
</ul>

<br>
<hr>
<br> -->

<div class="order-row">
  <div class="order-col-6">

    <div class="card">
      <div class="card-header">
        Product 1
      </div>
      <div class="card-body">
        <ul class="product_options" data-pid="product_1" style="padding: 20px;border: solid 1px red;">
        </ul>
      </div>
    </div>


    <div class="card">
      <div class="card-header">
        Product 2
      </div>
      <div class="card-body">
        <ul class="product_options" data-pid="product_2" style="padding: 20px;border: solid 1px red;">
        </ul>
      </div>
    </div>

  </div>
  <div class="order-col-6">

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
<?php get_footer(); ?>
