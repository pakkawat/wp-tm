jQuery(document).ready(function($){

  jQuery.validator.addMethod("currency", function (value, element) {
    if (/^\d{0,6}(\.\d{0,2})?$/.test(value)) {
        return true;
    } else {
        return false;
    };
  }, "กรุณาใส่ราคาสินค้าให้ถูกต้อง.");

  $("form[name='product_form']").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      product_name: "required",
      price:{
        required: true,
        currency: true,
        min: 0
      },
      stock:{
        required: {
          depends: function(element) {
              return $("#unlimited").val() != "1";
          }
        },
        number: true
      }
    },
    // Specify validation error messages
    messages: {
      product_name: "กรุณากรอกชื่อสินค้า",
      price: "กรุณาใส่ราคาสินค้าให้ถูกต้อง",
      stock: "กรุณาใส่จำนวนสินค้าให้ถูกต้อง"
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      form.submit();
    }
  });

});
