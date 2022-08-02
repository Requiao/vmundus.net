$(document).ready(function() {
	$("#message_div").animate({scrollTop: ($('#message_div')[0].scrollHeight)}, 250);
	
	/* make div editable */
	CKEDITOR.replace("message_input", {toolbar : 'basic', height: '350px'});
	
	$('#send').on('click', function() {
		var mail_id = $('#mail_id').html();
		var message = CKEDITOR.instances.message_input.getData();
		var data = new FormData();
		data.append('mail_id', mail_id);
		data.append('message', message);
		data.append('action', 'send_message');
		var url = "../etc/manage_messages";
		function sendMsgReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true){
				temp.message = temp.message.replace(/\\n/g, '');;
				temp.message = $("<textarea/>").html(temp.message).text();
				$('#msg_error').empty();
				var user_img = $('#user_img img').attr('src');
				var user_url = $('#user_name').attr('href');
				var user_name = $('#user_name').html();
				$('#message_div').append('<div class="out_message_div">' +
										 '<p class="time">' + temp.message_time +
									     '<a href="' + user_url + '" target="_blank"> ' + user_name + '</a></p>' +
										 '<img class="user_image" src="../user_images/' + user_img + '" alt="user image">' +
										 '<div class="message_divs">' + temp.message + '</div>' +
										 '</div>'
										).fadeIn();
				$("#message_div").animate({scrollTop: ($('#message_div')[0].scrollHeight)}, 250);
				
				CKEDITOR.instances.message_input.setData('');
			}
			else {
				$('#msg_error').html(temp.error);
			}
		}
		submitData(data, url, sendMsgReply);
	});
});
