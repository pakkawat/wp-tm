jQuery(document).ready(function($){

    jQuery(document).on("click", ".express-btn", function(){


      if(isMobileDevice() === true){
        if (app.makeToast() === true){
          phone_number = app.phonestate();
          device_id = app.onesignal_device();
          var send_data = 'action=tamzang_express_login&user='+device_id+'&phone='+phone_number;

          $.ajax({
              type: "POST",
              url: geodir_var.geodir_ajax_url,
              data: send_data,
              success: function(msg){
                    if(msg.success){
                      window.location.href = "/";
                    }
              },
              error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
              }
          });
        }
        else{
          OneSignal.showSlidedownPrompt();
          $('#express-msg').html('<font color="red">กรูณากดลิงค์ด้านล่าง</font>');
          //window.location.href = "/";
        } 
      }
    });

});

function isMobileDevice() {
  //return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
  var ua = navigator.userAgent;
  if(/Chrome/i.test(ua))
    return false;
  else if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua))
     return true;
  else
    return false;
}

function onManageWebPushSubscriptionButtonClicked(event) {
  getSubscriptionState().then(function(state) {
    if (state.isOptedOut) {
        /* Opted out, opt them back in */
        OneSignal.setSubscription(true);
    } else {
        /* Unsubscribed, subscribe them */
        OneSignal.registerForPushNotifications();
    }
    OneSignal.getUserId().then(function(userId) {
      jQuery.post( geodir_var.geodir_ajax_url, { action: "updateOnesignal", doing: "INSERT", device_id: userId } );
    });
  });
  event.preventDefault();
}

function updateMangeWebPushSubscriptionButton(buttonSelector) {
  var hideWhenSubscribed = false;
  var subscribeText = "subscribe";
  var unsubscribeText = "Unsubscribe from Notifications";

  getSubscriptionState().then(function(state) {
      var buttonText = !state.isPushEnabled || state.isOptedOut ? subscribeText : unsubscribeText;

      var element = document.querySelector(buttonSelector);
      if (element === null) {
          return;
      }

      element.removeEventListener('click', onManageWebPushSubscriptionButtonClicked);
      element.addEventListener('click', onManageWebPushSubscriptionButtonClicked);
      element.textContent = buttonText;

      if (state.isPushEnabled) {
          element.style.display = "none";
      } else {
          element.style.display = "";
      }
  });
}

function getSubscriptionState() {
  return Promise.all([
    OneSignal.isPushNotificationsEnabled(),
    OneSignal.isOptedOut()
  ]).then(function(result) {
      var isPushEnabled = result[0];
      var isOptedOut = result[1];

      return {
          isPushEnabled: isPushEnabled,
          isOptedOut: isOptedOut
      };
  });
}

//var OneSignal = OneSignal || [];
var buttonSelector = "#my-notification-button";

/* This example assumes you've already initialized OneSignal */
OneSignal.push(function() {
  // If we're on an unsupported browser, do nothing
  if (!OneSignal.isPushNotificationsSupported()) {
      return;
  }
  updateMangeWebPushSubscriptionButton(buttonSelector);
  OneSignal.on("subscriptionChange", function(isSubscribed) {
      /* If the user's subscription state changes during the page's session, update the button text */
      updateMangeWebPushSubscriptionButton(buttonSelector);
  });
});