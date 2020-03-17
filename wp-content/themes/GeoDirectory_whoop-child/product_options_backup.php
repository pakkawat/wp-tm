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
    console.log("New position: " + (ui.item.index()+1) );
    console.log(ul.data("oid"));
    console.log(ui.item.context.id);
    console.log("test toArray");
    var idsInOrder = ul.sortable("toArray");
    console.log(idsInOrder);
  }
  $( ".sortable" ).sortable({
    items: "li:not(.unsortable)",
    update: function( event, ui ) {
      sortable_update( event, ui, $(this) );
    }
  }).disableSelection();

  // $( ".sortable" ).sortable({
  //   update: function( event, ui ) {
  //     console.log("New position: " + (ui.item.index()+1) );
  //     console.log($(this).data("oid"));
  //     console.log(ui.item.context.id);
  //     //console.log(ui.item.context);
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
    //var cList = $('ul.sortable.ui-sortable')
    var form_list = $("<form>", {class: "add_option"});
    
    //console.log('<h3>'+$('#option_title').val()+'-'+$('#option_type').val()+'</h3>');
    var cList = $("<ul>", {class: "sortable ui-sortable", "data-oid": "xxxxx"});
    // for (i = 1; i < 8; i++) {
    //   // var li = $('<li/>')
    //   //   .addClass('ui-sortable-handle')
    //   //   .attr('id', i)
    //   //   .text("Item "+i)
    //   //   .appendTo(cList);
    //   cList.append('<li class="ui-sortable-handle" id="'+i+'">งง '+i+'<button class="delete-li">x</button></li>');
    // }
    cList.append('<li class="unsortable"><button class="add-li">add</button><input type="submit" value="save"></li>');
    form_list.append(cList);
    $('#options').append('<br><hr><br>');
    $('#options').append(form_list);
    form_list.before('<h3>'+$('#option_title').val()+'-'+$('#option_type').val()+'</h3>');

    $( ".sortable" ).sortable({
      items: "li:not(.unsortable)",
      update: function( event, ui ) {
        sortable_update( event, ui, $(this) );
      }
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
  </div>
  <div class="order-col-6">
  </div>
</div>
<div class="order-clear"></div>




<input type="text" id="option_title">
<select id="option_type">
  <option value="select">select</option>
  <option value="text_area">text_area</option>
</select>
<button class="create-list" >add</button>

<div id="options"></div>

    </div>
  </div>
</div>
<?php get_footer(); ?>
