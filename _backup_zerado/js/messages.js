$(document).ready(function() {
	/*messages or notifications*/
	// messages or notifications clicked on fixed menu
	var notif_or_msg = getCookie("notif_or_msg");
	
	if(notif_or_msg == "msg") {
		$('#notifications_div').css('display', 'none');
		$('#messages_inner_div').fadeIn('250');
	}
	else {
		$('#messages_inner_div').css('display', 'none');
		$('#delete_messages').css('display', 'none');
		$('#notifications_div').fadeIn('250');
		setNotifAsViewed();
	}
	
	// messages or notifications
	$('#messages').on('click', function() {
		$('#notifications_div').css('display', 'none');
		$('#messages_inner_div').fadeIn('250');
		$('#delete_messages').fadeIn('250');
	});
	
	$('#notifications').on('click', function() {
		$('#messages_inner_div').css('display', 'none');
		$('#delete_messages').css('display', 'none');
		$('#notifications_div').fadeIn('250');
		setNotifAsViewed();
	});
	
	//set Notifications As Viewed
	function setNotifAsViewed() {
		var data = new FormData();
		data.append('action', 'set_notif_as_viewed');
		var url = "../etc/manage_messages";
		function sNAV() {
			
		}
		submitData(data, url, sNAV, false);
	}
	
	/* delete messages */
	$('#delete_messages').on('click', function() {
		$("#for_popups_pop").fadeIn(300);
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">' + lang.are_you_sure_you_want_to_delete_these_messages + '</p>');
		$('#reply_info').append('<p class="button red" id="delete_ok">' + lang.yes + '</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">' + lang.cancel + '</p>');
	});
	
	$('#for_popups_pop').on('click', '#delete_ok', function() {
		var url = "../etc/manage_messages";
		var messages_id = [];
		$('.checkboxes:checkbox:checked').each(function(e) {
			messages_id[e] = ($(this).val());
		});
		var data = new FormData();
		data.append('message_id', messages_id);
		data.append('action', 'delete_messages');
		function deleteReply(reply) {	
			var temp = JSON.parse(reply);
			$("#for_popups_pop").empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span id="ok" class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
	
				for(var x = 0; x < messages_id.length; x++) {
					$('#m' +  messages_id[x]).slideUp();
					$('#m' +  messages_id[x] + ' input').remove();
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, deleteReply);
	});
	
	// cancel/ok btn
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
		$('#for_popups_pop').html('');
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
		$('#for_popups_pop').html('');
	});
	
	$('#compose_message').on('click', function() {
		$("#for_popups_pop").html('');
		$("#for_popups_pop").html('<div id="compose_msg_div">' +
								  '<span id="message_form_close" class="glyphicon glyphicon-remove-circle"></span>' +
								  '<p id="message_heading">' + lang.compose_message + '</p>' +
								  '<p id="msg_reply"></p>' +
								  '<input id="message_heading_input" type="text" maxlength="30" placeholder="Heading">' +
								  '<input id="message_to" type="text" maxlength="7" placeholder="To ID">' +
								  '<textarea id="message_input" placeholder="Message..."></textarea>' +
								  '<p id="compose">' + lang.send + '</p>' +
								  '</div>');
		CKEDITOR.replace("message_input", {toolbar : 'basic', height: '220px'});
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#compose', function() {
		var heading = $('#message_heading_input').val();
		var to_id = $('#message_to').val();
		var message = CKEDITOR.instances.message_input.getData();
		var data = new FormData();
		data.append('heading', heading);
		data.append('to_id', to_id);
		data.append('message', message);
		data.append('action', 'compose_message');

		var url = "../etc/manage_messages";
		function sendMsgReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true){
				temp.message = temp.message.replace(/\\n/g, '');;
				temp.message = $("<textarea/>").html(temp.message).text();
				
				$("#for_popups_pop").css('display', 'none');
				$('#messages_inner_div').prepend('<div class="all_messages_div unread_by_other" id="m' + temp.mail_id + '">' +
												 '<input name="message_id" class="checkboxes" type="checkbox"' + 
												 'value="' + temp.mail_id + '">' +
												 '<div class="all_messages">' +
												 '<a class="name" href="user_profile?id=' + temp.to_user_id + 
												 '">' + temp.to_user_name + '</a>' +
												 '<img class="user_image" src="../user_images/' + temp.to_user_image + 
												 '" alt="user image">' +
												 '<a class="heading" href="show_message?id=' + temp.mail_id + 
												 '">' + temp.heading + '</a>' +
												 '<div class="short_message">' + temp.message + '</div>' +
												 ' <p class="date">' + temp.message_time + '</p>' +
												 '</div></div>');
				$('#messages_inner_div').prepend();		

				$("#for_popups_pop").css('display', 'none');
				$("#for_popups_pop").fadeIn(300);
				$("#for_popups_pop").empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="ok" class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');					
			}
			else {
				$('#msg_reply').html(temp.error);
			}
		}
		submitData(data, url, sendMsgReply);
	});
	
	//close msg form 
	$('#for_popups_pop').on('click', '#message_form_close', function() {
		$("#for_popups_pop").fadeOut(300);
		$('#for_popups_pop').empty();
	});
});