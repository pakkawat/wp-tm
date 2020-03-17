jQuery(document).ready(function($){
    console.log("express login");

    jQuery(document).on("click", ".express-btn", function(){

        console.log("express-btn click");
        var user = "123456";
        var send_data = 'action=tamzang_express_login&user='+user;

        $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                console.log( "express login: " + JSON.stringify(msg) );
                  if(msg.success){
                    window.location.href = "/";
                  }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log(textStatus);
            }
          });

    });
});