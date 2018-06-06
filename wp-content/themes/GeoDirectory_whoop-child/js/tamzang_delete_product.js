jQuery(document).ready(function($){
  console.log("delete product loaded");
  $('#confirm-delete').on('click', '.btn-ok', function(e) {
    var $modalDiv = $(e.delegateTarget);
    var id = $(this).data('recordId');
    var nonce = $(this).data('recordNonce');
    //console.log(id);
    // $.ajax({url: '/api/record/' + id, type: 'DELETE'})
    // $.post('/api/record/' + id).then()

    var send_data = 'action=delete_product&id='+id+'&nonce='+nonce;
    $.ajax({
      type: "POST",
      url: geodir_var.geodir_ajax_url,
      data: send_data,
      success: function(msg){
            console.log( "Data deleted: " + JSON.stringify(msg) );
            $('#' + id).remove();
            //console.log( "Data Saved: " + msg );
            //console.log(tamzang_ajax_settings.ajaxurl);
            // ถ้า msg = 0 แสดงว่าไม่ได้ login
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         console.log(textStatus);
      }
    });

    $modalDiv.addClass('loading');
    setTimeout(function() {
        $modalDiv.modal('hide').removeClass('loading');
    }, 1000)

    //console.log(id);
    });

    $('#confirm-delete').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.title', this).text(data.recordTitle);
        $('.btn-ok', this).data('recordId', data.recordId);
        $('.btn-ok', this).data('recordNonce', data.recordNonce);
        //console.log(data);
    });



});
