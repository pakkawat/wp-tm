var datasend;
function oneSignalIosAndAndroid(device_id,is_sub) {
//function oneSignalIosAndAndroid() {
	var alertStr = "Onesignal Javascript"+is_sub+".";
	//alert(alertStr);
	/*
	var myloop = setInterval(function(){ testlogincheck(); }, 5000);
	
	function testlogincheck()
	{
		var datalogin = {
		action: 'is_user_logged_in'
		};
		jQuery.post(ajaxurl, datalogin, function(response) {
			if(response == 'yes') {
				console.log("hidetext start");
				sendiosdata();
				
				setTimeout(function(){ clearInterval(myloop); }, 5000);
			} else {
				console.log("Test catch login");
				sendiosdata();
			}
		});
	}
	*/


		//datasend = (is_sub == "true")?'action=updateusrnoti&doing=INSERT&device_id='+device_id+'&deviceType=Mobile':'action=updateusrnoti&doing=DELETE&deviceType=Mobile';
		datasend = (is_sub)?'action=updateusrnoti&doing=INSERT&device_id='+device_id+'&deviceType=Mobile':'action=updateusrnoti&doing=DELETE&deviceType=Mobile';
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: datasend,
			success: function() 
			{
	
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(textStatus);
			}
		});
		

}
//window.onload = hideText;



