$(document).ready(function() {
	
	var flag = 'old';
	var scrolled_down = false;
	getMessages();
	
	/* resize input field automaticaly */
	function resizeInputField() {
		$("#enter_message").css('height', 0);
			var textarea_scroll = document.getElementById("enter_message").scrollHeight;
			var new_size = textarea_scroll;
			if(new_size > 250) {
				new_size = 250;
			}
			$("#enter_message").css('height', new_size);
			var message_div = $("#chat").css('height');
			message_div = message_div.replace("px", "");
			$("#messages").css('height', message_div - new_size - 85);
	}
	$('#enter_message').on('keydown paste drop', function() {	
		setTimeout(function () {
			resizeInputField();
		});
	});
	
	/* display/hide chat list */
	$('#chat_name').on('click', function() {
		$("#chat_list_menu").slideToggle();
	});
	$('#chat_list_menu').on('mouseleave', function() {
		$("#chat_list_menu").slideUp();
	});
	
	/* display selected chat */
	var get_msg_timeout = undefined;
	$('#chat_list_menu').on('click', 'div', function() {
		$("#chat_list_menu").slideUp();
		if(get_msg_timeout != undefined) {
			clearTimeout(get_msg_timeout);
		}
		$('#messages').empty();
		flag = 'old';//display old messages
		scrolled_down = false;
		$('#chat_name').html('<i class="fa fa-comments" aria-hidden="true"></i> ' + $(this).children('.chat_name').html() +
							 ' <i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>');
		$('#chat_add_favorite i').attr('class', 'glyphicon glyphicon-star-empty');
		var chat_id = $(this).children('.chat_id').html();
		$('#chat_id').html(chat_id);
		getMessages();
	});
	
	function getMessages() {
		var chat_id = $('#chat_id').html();
		if(chat_id == '') {
			$('.ci_new_messages').each(function() {
				if($(this).html() > 0) {
					$(this).css('display', 'initial');
				}
			});
			if($('#total_new_chat_messages').html() > 0) {
				$('#total_new_chat_messages').css('display', 'initial');
			}
			return;
		}
		displaySelectedChat(flag, chat_id);
		flag = 'new'; //display only new messages
		get_msg_timeout = setTimeout(getMessages, 7000);
	};

	function displaySelectedChat(flag, chat_id) {
		var token = $('#messages').next().html();
		var data = new FormData();
		data.append('flag', flag);
		data.append('token', token);
		data.append('chat_id', chat_id);
		data.append('action', 'get_messages');
		var url = "../etc/manage_chat";
		function displayMessages(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === false) {
				clearTimeout(get_msg_timeout); 
				$('#messages').append('<div class="msg_info">' +
									  '<p class="chat_my_mass">' + temp.error + '</p>' +
									  '</div>');
				return;
			}
			
			if(temp.is_fav_chat === true) {
				$('#chat_add_favorite i').attr('class', 'glyphicon glyphicon-star');
			}
			
			for (i = 0; i < temp.messages.length; i++) {
				temp.messages[i].message = $("<textarea/>").html(temp.messages[i].message).text();
				if(temp.messages[i].edited === true) {//for edited messages
					$('#' + temp.messages[i].message_id + ' .chat_he_mass').html(temp.messages[i].message);
				}
				else if(temp.messages[i].is_me === true) {
					$('#messages').append('<div class="msg_info" id="' + temp.messages[i].message_id + '">' +
									  '<p class="chat_my_message_time">' + temp.messages[i].cor_date + 
									  ' ' + temp.messages[i].cor_time + '</p>' +
									  '<p class="chat_my_mass">' + temp.messages[i].message + '</p>' +
									  '<p class="edit">Edit</p>' +
									  '<p class="delete">Delete</p>' +
									  '</div>');
				}
				else {
					$('#messages').append('<div class="msg_info" id="' + temp.messages[i].message_id + '">' +
									  '<a class="chat_msg_sender" href="user_profile?id=' + temp.messages[i].from_user_id + '">' +
									  temp.messages[i].user_name +
									  '</a>' +
									  '<p class="chat_he_message_time">' +
									  temp.messages[i].cor_date + ' ' + temp.messages[i].cor_time + '</p>' +
									  '<p class="chat_he_mass">' + temp.messages[i].message + '</p>' +
									  '</div>');
					}
			}
			$('#messages').next().html(temp.token);
			if(!scrolled_down) {
				$("#messages").animate({scrollTop: $('#messages').offset().top + $('#messages')[0].scrollHeight}, 250);
				scrolled_down = true;
			}
			
			//update new messages
			var total_new_msg = 0;
			$('.ci_new_messages').html(0).css('display', 'none');
			for (i = 0; i < temp.new_messages.length; i++) {
				total_new_msg += parseInt(temp.new_messages[i].new_msg);
				
				$('#cinm_' + temp.new_messages[i].chat_id).html(temp.new_messages[i].new_msg);
				$('#cinm_' + temp.new_messages[i].chat_id).css('display', 'initial');
			}
			if(total_new_msg > 0) {
				$('#total_new_chat_messages').html(total_new_msg);
				$('#total_new_chat_messages').css('display', 'initial');
			}
			else {
				$('#total_new_chat_messages').css('display', 'none');
			}
		}
		submitData(data, url, displayMessages, false);
	};
	
	/* delete message */
	$('#chat').on('click', '.delete', function() {
		var message_id = $(this).parent().attr('id');
		var data = new FormData();
		data.append('message_id', message_id);
		data.append('action', 'delete');
		var url = "../etc/manage_chat";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#' + message_id + ' .chat_my_mass').html('deleted');
			}
			else {
				$("#for_popups_pop").empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
	
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* when pasting, check for link */
	$('#enter_message').on('paste drop', function(e) {
		var validUrlRegex = /^(https?|ftp):\/\/(-\.)?([^\s\/?\.#]+\.?)+(\/[^\s]*)?[^\s\.,]$/ig;
		var doubleQuoteRegex = /"/g;
		
		if(e.type == 'paste') {
			var plane_data = event.clipboardData.getData('text/plain');
		}
		else {
			var plane_data = event.dataTransfer.getData('text/plain');
		}
		
		var data = plane_data.replace( validUrlRegex , '<a href="' + plane_data.replace(doubleQuoteRegex, '%22') + '">$&</a>');
		var caret_pos = $(this).prop("selectionStart");
		
		setTimeout(function () {
			var message = $('#enter_message').val();
			//will not work with duplicate data
			message = message.replace(message.substring(caret_pos, plane_data.length + caret_pos), data);
			$('#enter_message').val(message);
			
			resizeInputField();
		});
	});
	
	/* edit message */
	var message_id;
	$('#chat').on('click', '.edit', function() {
		message_id = $(this).parent().attr('id');
		
		var message = $('#' + message_id + ' .chat_my_mass').html();
		$('#enter_message').val(message);
	});
	
	function updateMessage() {//update edited msg
		var message = $('#enter_message').val();
		$('#enter_message').val('');
		var url = "../etc/manage_chat";
		var data = new FormData();
		data.append('message_id', message_id);
		data.append('message', message);
		data.append('action', 'edit');
		function editReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true){
				temp.msg = $("<textarea/>").html(temp.msg).text();
				$('#' + message_id + ' .chat_my_mass').html(temp.msg);
			}
			message_id = undefined;
		}
		submitData(data, url, editReply);
	}
	
	/* send message to the server */
	$('#enter_message').keydown(function (e) {
		if (e.ctrlKey && e.keyCode == 13) {
			if(!message_id){
				sendMessage();
			}
			else {
				updateMessage();
			}
		}
	});
	
	$('#chat span').on('click', function() {
		if(!message_id){
			sendMessage();
		}
		else {
			updateMessage();
		}
	});
	
	function sendMessage() {
		if(get_msg_timeout != undefined) {
			clearTimeout(get_msg_timeout);
		}
		getMessages();//check for new messages before sending
		
		var message = $('#enter_message').val();
		$('#enter_message').val('');
		$('#enter_message').css('height', 25);//input field hight might grow
		$('#messages').css('height', 600);//reset when msg sent
		var chat_id = $('#chat_id').html();
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('message', message);
		data.append('action', 'send_message');
		var url = "../etc/manage_chat";
		function sendMsgReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				temp.msg = $("<textarea/>").html(temp.msg).text();
				$('#messages').append('<div class="msg_info" id="' + temp.msg_id + '">' +
										 '<p class="chat_my_message_time">' + temp.post_time + '</p>' +
										 '<p class="chat_my_mass">' + temp.msg  + '</p>' +
										 '<p class="edit">Edit</p>' +
										 '<p class="delete">Delete</p>' +
										 '</div>');
			}
			else {
				$('#messages').append('<div class="msg_info">' +
											 '<p class="chat_my_mass">' + temp.error + '</p>' +
											 '</div>');
			}
		}	
		submitData(data, url, sendMsgReply);
		
		$("#messages").animate({scrollTop: $('#messages').offset().top + $('#messages')[0].scrollHeight}, 500);
	}
	
	/* add favorite chat */
	$('#chat_add_favorite').on('click', function() {
		var data = new FormData();
		var chat_id = $('#chat_id').html();
		data.append('chat_id', chat_id);
		data.append('action', 'add_favorite_chat');
	
		var url = "../etc/manage_chat";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#chat_add_favorite i').attr('class', 'glyphicon glyphicon-star');
			}
			else {
				$("#for_popups_pop").empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
	
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* create new chat */
	$('#create_new_chat_btn').on('click', function() {
		$("#for_popups_pop").empty();
		$("#for_popups_pop").append('<div id="create_new_chat_div"></div>');
		$("#create_new_chat_div").append('<p id="add_chat_heading">Create New Chat</p>' +
										 '<p id="cncd_reply"></p>' +
										 '<p id="chat_name_head">Chat Name:</p>' +
										 '<input id="chat_name_input" type="text" maxlength="15" placeholder="Chat Name">' +
										 '<p class="button blue" id="create_new_chat">Create</p>' +
										 '<p class="button red" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(250);
	});
	
	$('#for_popups_pop').on('click', '#create_new_chat', function() {
		var chat_name = $('#chat_name_input').val();
		var data = new FormData();
		data.append('chat_name', chat_name);
		data.append('action', 'create_chat');
		var url = "../etc/manage_chat";
		function cerateCharReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$("#for_popups_pop").empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				
				$('#chat #chat_list_menu').prepend('<div id="ci_' + temp.chat_id + '">' +
												   '<p class="chat_name">' + temp.chat_name + 
												   '<span id="cinm_' + temp.chat_id + 
												   '" class="ci_new_messages" style="display: none;">0</span></p>' +
												   '<p class="chat_id" hidden="">' + temp.chat_id + '</p>' +
												   '</div>');
			}
			else {
				$('#cncd_reply').html(temp.error);
			}
		}
		submitData(data, url, cerateCharReply);
	});
	
	/* chat settings */
	$('#chat_settings').on('click', function() {
		var chat_id = $('#chat_id').html();
		if(chat_id == '') {
			return;
		}
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('action', 'get_info');
		var url = "../etc/chat_settings";
		function displaySettings(reply) {
			$("#for_popups_pop").empty();
			$("#for_popups_pop").append('<div id="chat_settings_div"></div>');
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				var schat_name = '<p id="schat_name">' + temp.chat_name + '</p>';
				if(temp.user_access == 'founder') {			
					var close = '<span id="close" class="glyphicon glyphicon-remove-circle"></span>';
					
					var disband = $('<p class="button red" id="disband"></p>').text('Disband'); 
					var chat_rename = '<input id="chat_rename_input" type="text" maxlength="15" placeholder="Chat New Name">'; 
					var rename = $('<p class="button blue" id="rename"></p>').text('Rename'); 
					var mod_input = '<input id="mod_input_id" type="text" maxlength="7" placeholder="mod ID">';
					var remove_mod_input = $('<p class="button red" id="remove_mod"></p>').text('Remove'); 
					var new_mod_input = $('<p class="button blue" id="set_mod"></p>').text('Add New'); 
					var info = '<abbr title="You are alloved to have only 3 moderators."><span id="mod_info" ' +
							   'class="glyphicon glyphicon-info-sign"></span></abbr>';
					var new_member = $('<input id="new_member" type="text" maxlength="6" placeholder="new member ID">').text('');
					var add_member = $('<p class="button blue" id="add_member"></p>').html('<abbr title="add new member">ADD</abbr>'); 
						
					$('#chat_settings_div').append(close, schat_name, disband, chat_rename, rename, mod_input, 
											remove_mod_input, new_mod_input, info, new_member, add_member); 
						
				}
				else if(temp.user_access == 'moderator'){				
					var close = '<span id="close" class="glyphicon glyphicon-remove-circle"></span>';
					var leave = '<p class="button red" id="leave">Leave</p>';
					var new_member = $('<input id="new_member" type="text" maxlength="6" placeholder="new member ID">').text('');
					var add_member = $('<p class="button blue" id="add_member"></p>').html('<abbr title="add new member">ADD</abbr>'); 
					
					$('#chat_settings_div').append(close, schat_name, new_member, add_member, leave); 
				}
				else if(temp.user_access == 'member'){			
					var close = '<span id="close" class="glyphicon glyphicon-remove-circle"></span>';
					var leave = '<p class="button red" id="leave">Leave</p>';
					
					$('#chat_settings_div').append(close, schat_name, leave); 
				}
				$('#chat_settings_div').append('<div id="members_div"></div>');
				for (i = 0; i < temp.members.length; i++) {
					if(temp.user_access == 'founder' || temp.user_access == 'moderator') {
						var remove_from_chat = '<p class="button red remove_member_from_chat">Remove</p>';
					}
					else {
						var remove_from_chat = '';
					}
					$('#members_div').append('<div class="member_info" id="m' + temp.members[i].id + '">' +
											   '<a href="user_profile?id=' + temp.members[i].id + 
											   '" target="_blank">' + temp.members[i].name + '</a>' +
											   '<img src="../user_images/' + temp.members[i].image + '" alt="user image">' +
											   remove_from_chat +
											   '<p class="access_lvl">' + temp.members[i].access_lvl + '</p>' +
											   '<p class="member_id" hidden>' + temp.members[i].id + '</p>' +
											 '</div>');
				}
			}
			else {
				$("#for_popups_pop").empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
					
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, displaySettings);
	});
	
	/* disband chat */
	$('#for_popups_pop').on('click', '#disband', function() {
		$('#for_popups_pop2').empty();
		$('#for_popups_pop2').append('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to disband this chat? ' +
								'All members will be removed and all messages will be deleted.</p>');
		$('#reply_info').append('<p class="button red" id="disband_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop2").fadeIn(300);
	});
	
	$('#for_popups_pop2').on('click', '#disband_yes', function() {
		var chat_id = $('#chat_id').html();
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('action', 'disband');
		var url = "../etc/chat_settings";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#for_popups_pop').fadeOut(300);

				clearTimeout(get_msg_timeout);
				$('#chat_name').html('<i class="fa fa-comments" aria-hidden="true"></i> ' +
								     'Select Chat' +
								     ' <i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' +
									 '</p>');
				$('#messages').empty();
				$('#ci_' + chat_id).remove();
				
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop2').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* rename chat */
	$('#for_popups_pop').on('click', '#rename', function() {
		chat_id = $('#chat_id').html();
		var url = "../etc/chat_settings";
		var new_name = $('#chat_rename_input').val();
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('new_name', new_name);
		data.append('action', 'rename');
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#chat_name').html('<i class="fa fa-comments" aria-hidden="true"></i> ' +
								      temp.chat_name +
								     ' <i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' +
									 '</p>');
									 
				$('#schat_name').html(temp.chat_name);
				$('#ci_' + chat_id + ' .chat_name').html(temp.chat_name + 
														 '<span id="cinm_' + chat_id + 
														 '" class="ci_new_messages" style="display: none;">0</span>');
			}
			else {
				$('#for_popups_pop2').empty();
				$('#for_popups_pop2').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$('#for_popups_pop2').fadeIn(300);
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* set/remove moderator */
	$('#for_popups_pop').on('click', '#set_mod', function() {
		var chat_id = $('#chat_id').html();
		var user_id = $('#mod_input_id').val();
		var url = "../etc/chat_settings";
		
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('user_id', user_id);
		data.append('action', 'setmod');
		function replySettings(reply) {	
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				if(temp.new_member === true) {
					$('#chat_settings_div #members_div').append('<div class="member_info" id="m' + temp.id + '">' +
												   '<a href="user_profile?id=' + temp.id + '" target="_blank">' + temp.name +'</a>' +
												   '<img src="../user_images/' + temp.image + '" alt="user image">' +
												   '<p class="button red remove_member_from_chat">Remove</p>' +
												   '<p class="access_lvl">' + temp.access_lvl + '</p>' +
												   '<p class="member_id" hidden>' + temp.id + '</p>' +
												   '</div>');
				}
				else {
					$('#chat_settings_div #m' + user_id + ' .access_lvl').html(temp.access_lvl);
				}
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop2').fadeIn(300);
			
		}
		submitData(data, url, replySettings);
	});
	
	$('#for_popups_pop').on('click', '#remove_mod', function() {
		var chat_id = $('#chat_id').html();
		var user_id = $('#mod_input_id').val();
		var url = "../etc/chat_settings";
		
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('user_id', user_id);
		data.append('action', 'remove_mod');
		function replySettings(reply) {	
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#chat_settings_div #m' + user_id + ' .access_lvl').html(temp.access_lvl);
				
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop2').fadeIn(300);
		}
		submitData(data, url, replySettings);
	});
	
	/* add member */
	$('#for_popups_pop').on('click', '#add_member', function() {
		var chat_id = $('#chat_id').html();
		var url = "../etc/chat_settings";
		var user_id = $('#new_member').val();
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('user_id', user_id);
		data.append('action', 'add_member');
		function replySettings(reply) {	
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			var temp = JSON.parse(reply);	
			if(temp.success === true) {
				$('#chat_settings_div #members_div').append('<div class="member_info" id="m' + temp.id + '">' +
												   '<a href="user_profile?id=' + temp.id + '" target="_blank">' + temp.name +'</a>' +
												   '<img src="../user_images/' + temp.image + '" alt="user image">' +
												   '<p class="button red remove_member_from_chat">Remove</p>' +
												   '<p class="access_lvl">' + temp.access_lvl + '</p>' +
												   '<p class="member_id" hidden>' + temp.id + '</p>' +
												   '</div>');
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop2').fadeIn(300);
			
		}
		submitData(data, url, replySettings);
	});
	
	/* remove member from chat  */
	var member_id;
	$('#for_popups_pop').on('click', '.remove_member_from_chat', function() {
		member_id = $(this).next().next().html();
		var member_name = $(this).prev().prev().html();
		
		$('#for_popups_pop2').empty();
		$('#for_popups_pop2').append('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to remove <i>' + member_name + '</i> with user ID <i>' +
								member_id + '</i> from the chat?</p>');
		$('#reply_info').append('<p class="button red" id="remove_from_chat_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop2").fadeIn(300);
	});
	
	$('#for_popups_pop2').on('click', '#remove_from_chat_yes', function() {
		var chat_id = $('#chat_id').html();
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('user_id', member_id);
		data.append('action', 'kick_member');
		var url = "../etc/chat_settings";
		function replySettings(reply) {	
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#m' + member_id).remove();
				
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$('#for_popups_pop2').fadeIn(300);
		}
		submitData(data, url, replySettings);
	});
	
	/* exit from chat */
	$('#for_popups_pop').on('click', '#leave', function() {
		$('#for_popups_pop2').empty();
		$('#for_popups_pop2').append('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to leave this chat?</p>');
		$('#reply_info').append('<p class="button red" id="leave_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop2").fadeIn(300);
	});
	
	$('#setting_reply').on('click', '#leave_no', function() {
		$('#setting_reply').fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#leave_yes', function() {
		var chat_id = $('#chat_id').html();
		var url = "../etc/chat_settings";
		var data = new FormData();
		data.append('chat_id', chat_id);
		data.append('user_id', member_id);
		data.append('action', 'leave');
		var url = "../etc/chat_settings";
		function replySettings(reply) {	
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				clearTimeout(get_msg_timeout);
				$('#chat_name').html('<i class="fa fa-comments" aria-hidden="true"></i> ' +
								     'Select Chat' +
								     ' <i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' +
									 '</p>');
				$('#messages').empty();
				$('#ci_' + chat_id).remove();
				
				$('#for_popups_pop').fadeOut(300);
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop2').fadeIn(300);
		}
		submitData(data, url, replySettings);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#cancel', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#close', function() {
		$("#for_popups_pop").fadeOut(300);
	});

});