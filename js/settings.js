$(document).ready(function() {
	$('#update').on('click', function() {
		$("#error").empty();
		var data = new FormData();
		
		//image
		var image =  $('#new_image')[0].files[0];
		//var image =  $('#new_image').get(0);
		if(image) {
			var img_ext = image.name.split('.').pop();
			if(img_ext.toLowerCase() != "png" && img_ext.toLowerCase() != "jpeg" && img_ext.toLowerCase() != "jpg") {
				$("#error").html("Only png, jpeg, and jpg are allowed for the user image.");
				return;
			}
			if(image.size > 500000) {
				$("#error").html("Maximum image size is 500kb.");
				return;
			}
			data.append('image', image);
		}
		
		//user name
		var user_name = $('#new_user_name').val();
		if(user_name.length > 0) {
			if(user_name.length < 3 && user_name.length > 15) {
				$("#error").html("User name length must be between 3..15 characters.");
				return;
			}
			data.append('user_name', user_name);
		}
		
		//password
		var password = $('#new_pswd').val();
		var rpt_password = $('#rpt_new_pswd ').val();
		if(password.length > 0) {
			
			if(password.length < 5) {
				$('#error').html('Minimum password length is 5 characters.');
				return;
			}
			if(password != rpt_password) {
				$('#error').html('Passwords don\'t match.');
				return;
			}
			
			data.append('password', password);
			data.append('rpt_password', rpt_password);
		}
		
		//timezone
		var timezone = $('#timezones_id').val();
		if(timezone.length > 0) {
			data.append('timezone', timezone);
		}
		
		//confirm
		var old_pass = $('#old_pswd').val();
		if(old_pass.length <= 0) {
			$('#error').html('In order to update your information, you must enter your old password.');
			return;
		}
		data.append('old_pswd', old_pass);
		
		var url = "../etc/settings";
		function updateInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				var msg = '';
				
				if(temp.upload_img === true) {
					msg += "- Image updated.<br>";
				}
				if(temp.user_name === true) {
					msg += "- User name updated.<br>";
				}
				if(temp.pass === true) {
					msg += "- Password updated.<br>";
				}
				if(temp.timezone === true) {
					msg += "- Timezone updated.<br>";
				}
				if(Object.keys(temp).length == 1) {
					msg = "You didn't updated anything.";
				}
				$("#for_popups_pop").empty();
				$("#for_popups_pop").append('<div id="reply_info"></div>');
				$("#reply_info").append('<p id="msg">' + msg + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
			}
			else {
				$('#error').html(temp.error);
			}
		}
		submitData(data, url, updateInfo);
	});
	
	//change language
	$('.change_lang').on('click', function() {
		var lang_code = $(this).next().html();
		var data = new FormData();
		data.append('lang_code', lang_code);
		data.append('action', 'change_language');
		var url = "../etc/settings";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				location.reload();
			}
			else {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
});
