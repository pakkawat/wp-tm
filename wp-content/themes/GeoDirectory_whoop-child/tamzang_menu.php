<?php /* Template Name: tamzang_menu */ 
global $wpdb;

if ( !isset( $_GET['pid'] ) || empty(get_post( $_GET['pid'] ))) {
    return;
}

$pid = $_GET['pid'];

$query_args = array(
  'is_geodir_loop' => true,
  'post_type' => 'gd_product',
  'posts_per_page' => -1,
  'order_by' => 'default_category_ASC'
);

add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);

$uploads = wp_upload_dir();

//$arrProducts = geodir_get_widget_listings($query_args);
// $arrProducts = $wpdb->get_results(
//   $wpdb->prepare(
//      "SELECT tc.id as cate_id, tc.name as cate_name , p.post_id, p.post_title, p.featured_image, p.geodir_price, p_post.post_content
//      FROM tamzang_catagory as tc
//      LEFT JOIN wp_geodir_gd_product_detail as p
//      ON tc.id = p.tamzang_category_id
//      LEFT JOIN wp_posts as p_post
//      ON p.post_id = p_post.ID
//      WHERE tc.shop_id = %d
//      ORDER BY tc.orderby asc, p.orderby asc",
//       array($pid)
//   )
// );
$arrProducts = $wpdb->get_results(
  $wpdb->prepare(
      "SELECT tc.id as cate_id, tc.name as cate_name , p.post_id, p.post_title, p.featured_image, p.geodir_price, p_post.post_content
      FROM tamzang_catagory as tc
      LEFT JOIN wp_geodir_gd_product_detail as p
      ON tc.id = p.tamzang_category_id
      LEFT JOIN wp_posts as p_post
      ON p.post_id = p_post.ID
      WHERE tc.shop_id = %d
      AND tc.visibility <> 2
      AND ( tc.visibility = 1 
           OR ( tc.visibility = 4 AND POWER(2,DAYOFWEEK(NOW())-1)&tc.day_of_week > 0 ) 
           OR ( tc.visibility = 3  AND ( now() > CONCAT(tc.hide_until_date,' ',tc.hide_until_time)) )
          )
      ORDER BY tc.orderby asc, p.orderby asc",
      array($pid)
  )
);

$array_shop_time = check_shop_open($pid);

$is_shop_open = $array_shop_time['is_shop_open'];
$shop_time = $array_shop_time['shop_time'];



function min_max_text($group){
  if(!$group->is_optional){
    if($group->force_min != $group->force_max)
      return '<small class="text-muted mm_font">เลือกอย่างน้อย '.$group->force_min.' อย่าง แต่ไม่เกิน '.$group->force_max.' อย่าง</small>';
    else
      return '<small class="text-muted mm_font">ต้องเลือกแค่ '.$group->force_max.' อย่าง</small>';
  }else{
    return;
  }
}

function create_popover_content($product_id,$shop_id,$img,$product_price,$product_title,$is_shop_open){
  global $wpdb, $current_user;

  $choice_groups  = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT cg.id as cg_id, cg.group_title, cg.is_optional, ca.id as ca_id, ca.choice_adon_detail, ca.extra_price, cg.is_optional, cg.force_min, cg.force_max
        FROM choice_group as cg
        LEFT JOIN choice_adons as ca
        ON cg.id = ca.choice_group_id
        LEFT JOIN product_options as po
        ON po.choice_group_id = cg.id
        WHERE cg.shop_id = %d AND po.product_id = %d order by po.orderby asc, ca.orderby asc ",
        array($shop_id,$product_id)
    )
  );


  echo '<div id="option_'.$product_id.'" style="display:none;" >';
  echo '<div class="popover-body">';
  echo '<h3 style="text-align:center;ont-weight: bold;" onClick="closeNav()"> < '.$product_title.'</h3>';
  if(!empty($img))
    echo '<img class="rounded mx-auto d-block" src="'.$img.'" style="height: 128px;margin-bottom: 10px;">';  
  echo '<form name="tamzang_product">';
  //echo '<input type="hidden" name="action" value="tamzang_menu_add_to_cart">';
  echo '<input type="hidden" name="nonce" value="'.wp_create_nonce('tamzang_menu_add_to_cart_' . $product_id.$current_user->ID).'">';
  echo '<input type="hidden" name="post_id" value="'.$shop_id.'">';
  echo '<input type="hidden" name="product_id" value="'.$product_id.'">';
  echo '<input type="hidden" name="product_title" value="'.$product_title.'">';
  echo '<input type="hidden" name="product_price" value="'.$product_price.'">';
  echo '<input type="hidden" name="price_w_ex" value="'.$product_price.'">';
  $temp_group_id = 0;
  foreach ($choice_groups as $group){
    if($temp_group_id == 0){
      echo '<div class="card" style="margin-bottom: 10px;">';
      echo '<div class="card-header">';
      echo '<h5>'.$group->group_title.' '.min_max_text($group).'<label class="error" for="choice_adons_'.$group->cg_id.'[]" style="color: red;"></label> </h5>';
      echo '</div>';
      echo '<ul class="list-group list-group-flush classToValidate" data-cg_id="'.$group->cg_id.'" data-optional="'.$group->is_optional.'" data-fmin="'.$group->force_min.'" data-fmax="'.$group->force_max.'">';

    }else if($group->cg_id != $temp_group_id){
      echo '</ul>';
      echo '</div>'; // ปิด div card

      echo '<div class="card" style="margin-bottom: 10px;">';
      echo '<div class="card-header">';
      echo '<h5>'.$group->group_title.' '.min_max_text($group).'<label class="error" for="choice_adons_'.$group->cg_id.'[]" style="color: red;"></label> </h5>';
      echo '</div>';
      echo '<ul class="list-group list-group-flush classToValidate" data-cg_id="'.$group->cg_id.'" data-optional="'.$group->is_optional.'" data-fmin="'.$group->force_min.'" data-fmax="'.$group->force_max.'">';
    }

    if(!empty($group->ca_id)){
      
      echo '<li class="list-group-item">';
      echo '<label class="form-check-label d-flex justify-content-between align-items-center for-label" for="choice_adons_'.$product_id.$group->cg_id.$group->ca_id.'">';
      echo '<div class="form-check">';
      echo '<input class="form-check-input" type="checkbox" value="'.$group->ca_id.':'.$group->group_title.':'.$group->choice_adon_detail.':'.$group->extra_price.'" 
            data-extra_price="'.$group->extra_price.'"
            name="choice_adons_'.$group->cg_id.'[]" id="choice_adons_'.$product_id.$group->cg_id.$group->ca_id.'"> ';
      echo '<h5>'.$group->choice_adon_detail.'</h5>';
      echo '</div>';
      echo '<h5><span>'.$group->extra_price.'</span></h5>';
      echo '</label>';
      echo '</li>';
      
      // echo '';
      // echo '';
      // echo '';

    }

    $temp_group_id = $group->cg_id;
  }

  if(count($choice_groups) > 0){
    echo '</ul>';
    echo '</div>'; // ปิด div card
  }
  echo '<div class="form-group">';
  echo '<h5><label for="Special">สิ่งที่อยากได้เป็นพิเศษ</label></h5>';
  echo '<textarea class="form-control" id="Special" name="Special" rows="3"></textarea>';
  echo '</div>';
  echo '<div class="form-group">';
  echo '<h5><label>Quantity <label class="error" for="quantity" style="color: red;"></label></label></h5>';
  echo '<div class="input-group justify-content-center">';
  //echo '<input type="button" value="-" class="button-minus" data-field="quantity">';
  echo '<button type="button" class="button-minus" data-field="quantity">-</button>';
  echo '<input type="number" value="1" name="quantity" class="quantity-field">';
  echo '<button type="button" class="button-plus" data-field="quantity">+</button>';
  //echo '<input type="button" value="+" class="button-plus" data-field="quantity">';
  echo '</div>';
  echo '</div>';

  $geodir_tamzang_id = geodir_get_post_meta( $shop_id, 'geodir_tamzang_id', true );
  $show_button = geodir_get_post_meta( $product_id, 'geodir_show_addcart', true );
  if(!empty($geodir_tamzang_id)&&$show_button){
    echo '  <div class="row">
              <div class="col-4 col-lg-4 ">
              <a class="btn btn-danger  d-flex w-100 align-items-center" style ="color: white;" onClick="closeNav()">
              <div class="vertical-line" style="height: 45px;margin-right: 0px;margin-left: 0px;" ></div>
              <div class="w-100">ยกเลิก</div>
              <div class="vertical-line" style="height: 45px;margin-right: 0px;margin-left: 0px;"></div>
              </a>
              </div>';
    if($is_shop_open){
      echo '<div class="col-8 col-lg-8">
            <button type="submit" class="btn btn-success d-flex w-100 align-items-center">
            <div class="p-price">'.$product_price.'</div>
            <div class="vertical-line" style="height: 45px;"></div>
            <div class="w-100">สั่งสินค้า</div>
            </button>
            </div>';
    }          
    echo '</div>';
  }
  else{
    echo '  <div class="row">
              <div class="col-xs-12 col-12 col-lg-12 ">
              <button class="btn btn-danger d-flex w-100 align-items-center" onClick="closeNav()">
              <div class="vertical-line" style="height: 45px;margin-right: 0px;margin-left: 0px;" ></div>
              <div class="w-100">ยกเลิก (สินค้าหมดชั่วคราว)</div>
              <div class="vertical-line" style="height: 45px;margin-right: 0px;margin-left: 0px;"></div>
              </button>
              </div>                            
            </div>
          ';

  }
  echo '</form>';

  echo '</div>';
  echo '</div>';
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js"></script>
  
</head>

<style>

html, body, {
    position:fixed;
    top:0;
    bottom:0;
    left:0;
    right:0;
    width:100%;
    height:100%;
    overflow-x: hidden;
}

.container{
  overflow-x: hidden;
}

.order-status-loading:before {
display: flex;
flex-direction: column;
justify-content: center;
content: 'รอสักครู่...';
text-align: center;
font-size: 20px;
background: rgba(0, 0, 0, .8);
position: absolute;
top: 0px;
bottom: 0;
left: 0;
right: 0;
color: #EEE;
z-index: 1000;
width: 100%;
height: 100%;
}

@media only screen and (min-width: 990px) {
  .image-type-lg{
    height:100%;
  }
}

@media only screen and (max-width: 990px) {
  .image-type{
    height:387px;
  }
}
/*
 .overlay {
  position: relative;
  background-color: rgba(0, 0, 0, 0);
}*/
.overlay {
  /*
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  height: 100%;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 100;*/
  width: 0;
  position: fixed;
  z-index: 13;
  top: 0;
  left: 0;
  background-color: rgb(0,0,0);
  background-color: rgba(0,0,0, 0.9);
  overflow-x: hidden;
  transition: 0.5s;
} 


/* Position the content inside the overlay */
.overlay-content {
  /*position: relative;*/
  width: 100%; /* 100% width */
  text-align: center; /* Centered text/links */
  /*margin-top: 30px; /* 30px top margin to avoid conflict with the close button on smaller screens */
}

.popover{
  width: 100%;
  position: relative;
  z-index: 102;
  max-width: 100%;
  transform:none!important;  
  left: 0 !important;
  margin-left: 0px;
  margin-right: 0px;
}

.popover-content{
  width: 100%;
  top: 5%;
  position: relative;
  z-index: 102;
}

.input-group {
  clear: both;
  position: relative;
}

.input-group button[type='button'] {
  cursor: pointer;
  background-color: #eeeeee;
  min-width: 38px;
  width: auto;
  transition: all 300ms ease;
  border: 1px solid rgba(0,0,0,.125);
}

.input-group .button-minus,
.input-group .button-plus {
  font-weight: bold;
  height: 45px;
  padding: 0;
  width: 38px;
  position: relative;
}

.input-group .quantity-field {
  position: relative;
  height: 45px;
  left: -6px;
  text-align: center;
  width: 50%;
  display: inline-block;
  font-size: 20px;
  margin: 0 0 5px;
  resize: vertical;
  border : 1px solid rgba(0,0,0,.125);
}

.button-plus {
  left: -13px;
}

input[type="number"] {
  -moz-appearance: textfield;
  -webkit-appearance: none;
}

.header-menu{
  z-index:10;
  position:fixed;
  left:0;
  right:0;
  display:-webkit-box;
  display:-ms-flexbox;
  display:flex;
  -webkit-box-pack:center;
  -ms-flex-pack:center;
  justify-content:center;
  /* background-color: #e34f43; */
  background-color: white;
}

.header-menu .menu-row{
  border-radius:0;
  margin:0;
  display:-webkit-box;
  display:-ms-flexbox;
  display:flex;
  -webkit-box-align:center;
  -ms-flex-align:center;
  align-items:center;
  -webkit-box-pack:center;
  -ms-flex-pack:center;
  justify-content:center;
  width:100%;
  border: solid 1px;
}

div.vertical-line{
  width: 1px; /* Line width */
  background-color: white; /* Line color */
  height: 100%; /* Override in-line if you want specific height. */
  margin: 0 1rem;
  /* display:inline-block; */
}

.img-fluid{
  max-width: 100%;
  max-height: 100vh;
  margin: auto;
}

.row{
  margin :0 auto;
}

.mm_font{
  font-size:60%;
}

.shop_msg{
  z-index:10;
  position:fixed;
  bottom:0;
  left:0;
  right:0;
  top:0;
  display:-webkit-box;
  display:-ms-flexbox;
  display:flex;
  -webkit-box-pack:center;
  -ms-flex-pack:center;
  justify-content:center;
  background-color: #ef4d4b;
  color:#fff;
  height: 100px;
  width: 100%;
  justify-content: center;
}
</style>



<script>
function closeNav() {
  //restore position menu screen
  document.documentElement.scrollTop = $('#myOv').data("value");


  document.getElementById("myOv").style.width = "0%";
  document.getElementById("myOv").style.height = "0%";
  $('.popover').popover('hide');
  $("#MyCloseOv").removeAttr("style").hide();

  if(parent.document.getElementById("header-back")){
    parent.document.getElementById("header-back").style.display = "block"; 
  }
 
}

$(function () {
  var inputs = $('[data-toggle="popover"]');

  // Popover init
  inputs.popover({
      //'content'   : $('#popover-content').html(),
      html: true,
      sanitize: false,
      //placement : 'auto',
      trigger: 'click',
      container:'#popover-content',
      content: function() {
          var content = $(this).attr("data-popover-content");
          return  $(content).children(".popover-body").html();
      }
  });

  inputs.on('show.bs.popover', function() {
    //save position menu screen
    var menu_position = document.documentElement.scrollTop;    
    $('#myOv').data("value",menu_position);
    document.documentElement.scrollTop = 10;

    $('.popover').not(this).popover('hide');
    //$(".overlay").toggleClass("on");
    document.getElementById("myOv").style.width = "100%";
    document.getElementById("myOv").style.height = "100%";
    $("#MyCloseOv").removeAttr("style").show();

    if(parent.document.getElementById("header-back")){
      parent.document.getElementById("header-back").style.display = "none";
    }

  });

  inputs.on('shown.bs.popover', function() {
    var content = $("body").find("#"+$(this).attr('aria-describedby'));
    var pop_over = $(this);
    //console.log("2");
    //console.log($(this));
    //console.log($(this).attr('aria-describedby'));
    var form = $(content).find("form[name='tamzang_product']");
    var i = 0;
    $(form).find(".for-label").each(function () {
      $(this).attr("for","newId"+i);
      $(this).find("input:checkbox").attr("id","newId"+i);
      i++;
    });
    var myValidateObj = {
      // rules: {
      //   'choice_adons[]': { required: true, rangelength: [2,4] },
      // },
      // messages:{
      //   'choice_adons[]': { required: "กรุณาเลือก", 
      //                       rangelength: jQuery.validator.format("เลือกอย่างน้อย {0}-{1} ชิ้น")
      //                     }
      // },
      rules: {
        quantity: { required: true, integer: true, range: [1,99] },
      },
      messages:{
        quantity: { 
          required: "จำนวนไม่ถูกต้อง", 
          range: jQuery.validator.format("จำนวนไม่ถูกต้อง {0}-{1} ชิ้น")
        }
      },
      highlight: function(element) {
        //$(element).parent().addClass('has-error');
        //console.log('highlight');
        //console.log($(element).parents(".card"));
        console.log($(element).parents("ul:first").prev().find(".error"));
        $(element).parents(".card").addClass('border-danger');
        if(!$(element).parents("ul:first").prev().find(".error").prev().is( "br" ))
          $(element).parents("ul:first").prev().find(".error").before("<br>");
      },
      unhighlight: function(element) {
        //$(element).parent().removeClass('has-error');
        //console.log('unhighlight');
        //console.log($(element));
        //console.log($(element).parents(".card"));
        $(element).parents(".card").removeClass('border-danger');
        if($(element).parents("ul:first").prev().find(".error").prev().is( "br" ))
          $(element).parents("ul:first").prev().find(".error").prev().remove();
      },
      submitHandler: function(form) {
        console.log($(form).serializeArray());
        var post_data = $(form).serializeArray();
        var nonce = post_data[0].value;
        console.log(nonce);
        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: {
            'action': 'tamzang_menu_add_to_cart',
            'nonce': nonce,
            'post_data': post_data
          },
          success: function(msg){
            console.log( "add_to_cart: " + JSON.stringify(msg) );
            closeNav();
            //pop_over.popover('hide');
            if(msg.success){
              if(msg.data.type == "qty"){
                //var qty = $('#tb_cart_'+msg.data.cart_id).find('td:nth-child(1)').text();
                var qty = $('#tb_cart_'+msg.data.cart_id).find('td:nth-child(1)');
                console.log(qty.text());
                qty.text(parseInt(qty.text())+parseInt(msg.data.quantity));
                var old_price = $('#'+msg.data.cart_id+'-total');
                console.log("old price:"+old_price.data("total"));
                var new_price = parseFloat(old_price.data("total"))+parseFloat(msg.data.sum_price);
                old_price.data('total', new_price);
                old_price.text(new Intl.NumberFormat('th-TH').format(new_price));
              }else{
                console.log(msg.data);
                console.log("----");
                console.log(msg.data.choice_addons);
                var tr = $("<tr>", {id: "tb_cart_"+msg.data.cart_id});
                tr.append(`<td>`+msg.data.quantity+`</td>`);
                var td_product = $("<td>");
                td_product.append(`<div class="row"><h4>`+msg.data.product_title+`</h4></div>`);
                var div = $("<div>", {class: "row"});
                $.each(msg.data.choice_addons, function( key, value ) {
                  div.append(`<div class="col-12 p-options">`+value.group_title+` : `+value.choice_adon_detail+` (`+value.extra_price+`)</div>`);
                });
                div.append(`<div class="col-12 p-options">`+msg.data.special+`</div>`);
                td_product.append(div);
                tr.append(td_product);
                tr.append(`<td><strong><div id="`+msg.data.cart_id+`-total" class="price" data-total="`+msg.data.sum_price+`">`+(new Intl.NumberFormat('th-TH').format(msg.data.sum_price))+`</div></strong></td>`);
                tr.append(`<td><i class="fa fa-remove remove_cart_item" data-nonce="`+msg.data.nonce+`" data-cart_id="`+msg.data.cart_id+`"></i></td>`);
                var tr_length = $('#tb_cart').children('tr').length;
                if(tr_length>2){
                  $('#tb_cart').find('tr:last').prev().prev().after(tr);
                }else{
                  $('#tb_cart').find('tr:last').prev().before(tr);
                }

                
                var count = parseInt($('#item_count').text());
                if(isNaN(count)){
                  $('#item_count').text('1');
                }else{
                  $('#item_count').text(count+1);
                }
              }
              var sum = parseFloat($('#sum').data("sum"));
              sum += msg.data.sum_price;
              $('#sum').data('sum', sum);
              $('#sum').text((new Intl.NumberFormat('th-TH').format(sum))+" บาท");
            }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);
            pop_over.popover('hide');
          }
        });

      }
    };
    $(form).validate(myValidateObj);
    var group = 0;
    var optional = 0;
    var min = 0;
    var max = 0;
    var ValidateObj = [];
    var rules = [];
    var messages = [];

    $(form).find(".classToValidate").each(function () {
      //console.log($(this));
      if($(this).is("ul")){
        group = $(this).data("cg_id");
        optional = $(this).data("optional");
        min = $(this).data("fmin");
        max = $(this).data("fmax");
        console.log(group+"-"+optional);
        
        if(optional == "0"){
          //console.log($(form).find("input[name='choice_adons_"+group+"[]']"));
          $(form).find("input[name='choice_adons_"+group+"[]']").each(function () {
            $(this).rules('add', {
              required: true,
              rangelength: [min,max],
              messages: {
                  required: "กรุณาเลือก",
                  rangelength: jQuery.validator.format("เลือกอย่างน้อย {0} อย่าง แต่ไม่เกิน {1} อย่าง")
              }
            });
          });
        }}
      // }else if($(this).is("input.quantity-field")){
      //   console.log($(this));
      //   $(this).rules('add', {
      //     required: true,
      //     integer: true,
      //     range: [1,5],
      //     messages: {
      //         required: "จำนวนไม่ถูกต้อง",
      //         rangelength: jQuery.validator.format("จำนวนไม่ถูกต้อง {0}-{1} ชิ้น")
      //     }
      //   });
      // }
    });



  });

})

jQuery(document).ready(function() {

function incrementValue(e) {
  e.preventDefault();
  var fieldName = $(e.target).data('field');
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal)) {
    parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
    var div_price = $(e.target).parents("form[name='tamzang_product']").find("div.p-price");
    var p_ex = $(e.target).parents("form[name='tamzang_product']").find('input:hidden[name="price_w_ex"]');
    var price = parseFloat(p_ex.val());
    div_price.text((price * (currentVal + 1)).toFixed(2));
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
  }
}

function decrementValue(e) {
  e.preventDefault();
  var fieldName = $(e.target).data('field');
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal) && currentVal > 1) {
    parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
    var div_price = $(e.target).parents("form[name='tamzang_product']").find("div.p-price");
    var price = parseFloat(div_price.text());
    var p_ex = parseFloat($(e.target).parents("form[name='tamzang_product']").find('input:hidden[name="price_w_ex"]').val());
    div_price.text((price - p_ex).toFixed(2));
  } else {
    parent.find('input[name=' + fieldName + ']').val(1);
  }
}

$(document).on('click', '.button-plus', function(e) {
  //console.log("incrementValue");
  incrementValue(e);
});

$(document).on('click', '.button-minus', function(e) {
  //console.log("decrementValue");
  decrementValue(e);
});

$(document).on('change', '.form-check-input', function(e){
  var div_price = $(this).parents("form[name='tamzang_product']").find("div.p-price");
  var price = parseFloat(div_price.text());
  var extra = parseFloat($(this).data("extra_price"));
  var p_ex = $(this).parents("form[name='tamzang_product']").find('input:hidden[name="price_w_ex"]');
  var p_ex_val = parseFloat(p_ex.val());
  
  var quantity = $(this).parents("form[name='tamzang_product']").find("input.quantity-field");
  if(this.checked) {
    div_price.text((price + (extra*quantity.val())).toFixed(2));
    p_ex.val(p_ex_val + extra);
  }else{
    div_price.text((price - (extra*quantity.val())).toFixed(2));
    p_ex.val(p_ex_val - extra);
  }
  console.log(p_ex.val()); 
});

$(document).on('click', '.remove_cart_item', function(e) {
  var btn = $(this);
  var cart_id = $(this).data("cart_id");
  var price = parseFloat($('#'+cart_id+'-total').data("total"));
  var sum = parseFloat($('#sum').data("sum"));
  var nonce = $(this).data("nonce");
  var send_data = 'action=remove_cart_item&cart_id='+cart_id+'&nonce='+nonce;
  $.ajax({
    type: "POST",
    url: ajaxurl,
    data: send_data,
    success: function(msg){
      console.log( "return: " + JSON.stringify(msg) );
      if(msg.success){
        btn.parents("tr:first").remove();
        var new_sum = sum - price;
        $('#sum').data('sum', new_sum);
        $('#sum').text((new Intl.NumberFormat('th-TH').format(new_sum))+" บาท");
        var count = parseInt($('#item_count').text());
        count -= 1;
        if(count > 0){
          $('#item_count').text(count);
        }else{
          $('#item_count').text('');
        }
      }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(textStatus);
    }
  });
});

$(document).on('click', '#cart-tab', function(e) {
  console.log("Cart Tab Click");
  //console.log(parent.document.getElementById("footer-check-out"));
  parent.document.getElementById("footer-check-out").style.display = "block";
});

$(document).on('click', '#home-tab', function(e) {
  console.log("Cart Tab Click");
  //console.log(parent.document.getElementById("footer-check-out"));
  parent.document.getElementById("footer-check-out").style.display = "none";
});


});




</script>
<body>
<!-- bank 
<div id="popover-content" style="display:none;">
</div>
-->
<?php
$item_count = $wpdb->get_var(
  $wpdb->prepare(
    "SELECT count(cart.id) FROM shopping_cart as cart
    LEFT OUTER JOIN wp_geodir_gd_product_detail as product
    ON product.post_id = cart.product_id
    WHERE wp_user_id = %d AND product.geodir_shop_id = %d ", 
    array($current_user->ID, $pid)
  )
);
$display_count = '';
if($item_count > 0)
$display_count .= $item_count;
?>
<div class="container" id = "container" style ="position: relative;padding: 0;margin: 0;max-width: 100%;">
  <div class="header-menu" style ="position: relative;top:7px;">
    <div class="row menu-row">
      <?php if ( is_user_logged_in() ){ 
        if(wp_is_mobile()){
      ?>
          <div class="col-12" style="background-color: #dee2e6;text-align: center;height: 40px;">
            <?php  
                  echo '<h3 '; 
                  if(is_english(get_the_title($pid))){
                    echo strlen(get_the_title($pid)) < 30 ? 'style="font-size: 30px;"' : 'style="font-size: 15px;"';
                  }
                  else{
                    echo strlen(get_the_title($pid)) < 60 ? 'style="font-size: 30px;"' : 'style="font-size: 15px;"';
                  }                   
                  echo'>'.get_the_title($pid).'</h3>'; 
                  
                  ?>
          </div> 
          <div class="col-12" style="padding: 2px;top:2px;"> 
            <ul class="nav nav-pills" id="menu_tab" role="tablist" style="float: left;width:100%">   
              <li class="nav-item">
                <a class="nav-link" id="cart-tab" style="padding: 0;" data-toggle="pill" href="#cart" role="tab" aria-controls="cart" aria-selected="false" >
                <img src="https://test02.tamzang.com/wp-content/uploads/2020/06/shopping-cart.png" width="45" height="45"><span id="item_count" class="badge badge-danger"><?php echo $display_count; ?></span></a>
              </li>           
              <li class="nav-item" style="position: absolute;right: 1%;top: 10px;">
                <a class="nav-link active" style="padding: 5px;" id="home-tab" data-toggle="pill" href="#home" role="tab" aria-controls="home" aria-selected="true">เมนู (Menu)</a>
              </li>
             <!-- </div> -->
            <!--  <div class="col-6" style="padding-right: 0px;">  -->
          
            </ul>
          </div> 
        <?php }
        else {?>
          <div class="col-6 ">
            <?php echo '<h3 id="closeModal" ';
                  if(is_english(get_the_title($pid))){
                    echo strlen(get_the_title($pid)) < 30 ? 'style="font-size: 30px;"' : 'style="font-size: 15px;"';
                  }
                  else{
                    echo strlen(get_the_title($pid)) < 90 ? 'style="font-size: 30px;"' : 'style="font-size: 15px;"';
                  }
                  echo'>'.get_the_title($pid).'</h3>'; ?>
          </div>
          <div class="col-6" style="padding-right: 0px;">    
            <ul class="nav nav-pills" id="menu_tab" role="tablist" style="float: right;">
              <li class="nav-item" style="margin-right: 10px;padding-top: 5px;">
                <a class="nav-link active" id="home-tab" data-toggle="pill" href="#home" role="tab" aria-controls="home" aria-selected="true">เมนู (Menu)</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="cart-tab" data-toggle="pill" href="#cart" role="tab" aria-controls="cart" aria-selected="false">
                <img src="https://test02.tamzang.com/wp-content/uploads/2020/06/shopping-cart.png" width="45" height="45"><span id="item_count" class="badge badge-danger"><?php echo $display_count; ?></span></a>
              </li>
            </ul>
          </div>
        <?php } ?>
      <?php }else{echo '<div class="row" style="text-align:center;">กรุณา login</div>';}?>
    </div>
  </div>

  <div id="myOv" class="overlay" data-value="">
    <div class="overlay-content">
      <div id="popover-content">

      </div>
    </div>
  </div>

  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
      <div class="row justify-content-center">
        <?php 
        $path = $uploads['basedir'] . '/shop_menu_image/'.$pid;
        $img_exit = false;
        if(file_exists($path.".jpeg")){
          $path = $uploads['baseurl'] . '/shop_menu_image/'.$pid.'.jpeg';
          $img_exit = true;
        }
        if(file_exists($path.".jpg")){
          $path = $uploads['baseurl'] . '/shop_menu_image/'.$pid.'.jpg';
          $img_exit = true;
        }
        if(file_exists($path.".png")){
          $path = $uploads['baseurl'] . '/shop_menu_image/'.$pid.'.png';
          $img_exit = true;
        }
        if($img_exit){?>
        <img class="img-fluid" src="<?php echo $path.'?x='.rand(); ?>" class="food-img">
        <?php } ?>
      </div>
      <?php

      $category = "";
      $i = 0;
      $count_row = 1;
      foreach ( $arrProducts as $product ){
        // echo print_r($product);
        // echo "<br><br><hr>";
        if($product->cate_name != $category)
        {
          echo '<div style="padding: 20px;"><div class="row p-2" style="background-color: rgba(240,240,240,.9);" data-toggle="collapse" data-target="#collapse'.$product->cate_id.'">';
          echo '<h2>'.$product->cate_name.'</h2>';
          echo '</div>';

          echo '<div class="row collapse show" id="collapse'.$product->cate_id.'">';
          echo '<div class="row flex-grow-1">';

          $category = $product->cate_name;

          if(!empty($product->post_id))
          {
            $arr_images = geodir_get_images($product->post_id, 'medium', get_option('geodir_listing_no_img'));
            $rand_keys = array_rand($arr_images, 1);
            $rand_image = $arr_images[$rand_keys];
            echo '<div class="col-12 order-1 col-lg-6 order-lg-2" style="padding:0">';
            //echo '<div class="row ml-lg-3 image-type image-type-lg" style="background-image: url('.$rand_image->src.');"></div>';
            echo '</div>';
          }

          $count_row = 1;
        }

        if(!empty($product->post_id))
        {
          $img = '';
          if(!empty($product->featured_image))
            $img = $uploads['baseurl'].$product->featured_image;

          echo '<div class="col-12 order-2 col-lg-6 order-lg-'.($count_row > 1 ? '3' : '1').' border-top border-bottom" 
          data-toggle="popover" data-pid="'.$product->post_id.'" data-popover-content="#option_'.$product->post_id.'" style="padding:0">';
          echo '<div class="row py-3" style="margin-right: 10px;">';
            echo '<div class="col-9 col-md-9 " style="padding:0;">';
              echo '<div class="col" style="padding:0;">';
                echo '<div class="float-left mr-3">';
                  if(!empty($img))
                    echo '<img src="'.$img.'" style="width: 64px; height: 64px;">';
                echo '</div>';
                echo '<div>';
                  echo '<h4>'.$product->post_title.'</h4>';
                echo '</div>';
                echo '<div>';
                  echo $product->post_content;
                echo '</div>';
              echo '</div>';
            echo '</div>';
            echo '<div class="col-3 col-md-3" style="text-align: right;padding:0;">';
              echo '<h5>'.str_replace(".00", "",number_format($product->geodir_price,2)).'<h5>';
            echo '</div>';
          echo '</div>';
          echo '</div>';
          create_popover_content($product->post_id, $pid, $img, $product->geodir_price, $product->post_title,$is_shop_open);
        }
        if($product->cate_name != $arrProducts[$i+1]->cate_name)
        {
          echo '</div>';// class="row"
          echo '</div>';//class="row collapse show"
          echo '</div>';
        }
        echo '';
        echo '';
        echo '';
        echo '';
        echo '';
        echo '';
        echo '';
        $i++;
        $count_row++;
      }// foreach $arrProducts
      ?>
    </div>
    <div class="tab-pane fade" id="cart" role="tabpanel" aria-labelledby="cart-tab">Cart<?php get_template_part( 'ajax-cart' ); ?></div>
  </div>



</div>




</body>
</html>


