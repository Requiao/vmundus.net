$(document).ready(function() {
	//aliens' arrival
	//initialize elections clock.
	updateAliensArrivalClock();
	function updateAliensArrivalClock() {
		$('#ard_timeout').each(function() {
			countdownClock(this);
		});

		setTimeout(updateAliensArrivalClock, 950);
	};


	//initialize elections clock.
	updateElectionClock();
	function updateElectionClock() {
		$('.elections_clock').each(function() {
			countdownClock(this);
		});

		setTimeout(updateElectionClock, 950);
	};
	
	/* check for rewards */
	checkRewards();
	function checkRewards() {
		var data = new FormData();
		data.append('action', 'regular_rewards');
		var url = "../etc/user_rewards";
		function replyRewards(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$("#for_popups_pop").empty();
				$("#for_popups_pop").append('<div id="rewards_div"></div>');
				$("#rewards_div").append('<div id="rd_div"></div>');
				$("#rd_div").append('<p id="msg_head">' + temp.msg_head + '</p>');
				
				for (i = 0; i < temp.products.length ; i++) {
					$("#rd_div").append('<div class="icon_amount">' +
										'<abbr title="' + temp.products[i].product_name + '">' +
										'<img class="product_icon" src="../product_icons/' + temp.products[i].product_icon + '"' +
										' alt="' + temp.products[i].product_icon + '"></abbr>' +
										'<p class="amount">' + temp.products[i].amount + '</p>' +
										'</div>');
				}
				$("#rd_div").append('<p class="button blue check_again" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
			}
			else if (temp.success === false && temp.error != false) {
				$("#for_popups_pop").empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
	
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
			}
		}
		submitData(data, url, replyRewards, false);
	};
	
	$('#for_popups_pop').on('click', '.check_again', function() {
		checkRewards();
	});
	
	/* collect weekly missions rewards */
	$('.wmpbd_icon_div img').on('click', function() {
		var data = new FormData();
		var level = $(this).prev().html();
		data.append('level', level);
		data.append('action', 'weekly_missions_rewards');
		var url = "../etc/user_rewards";
		function replyRewards(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$("#for_popups_pop").empty();
				$("#for_popups_pop").append('<div id="rewards_div"></div>');
				$("#rewards_div").append('<div id="rd_div"></div>');
				$("#rd_div").append('<p id="msg_head">' + temp.msg_head + '</p>');
				
				for (i = 0; i < temp.products.length ; i++) {
					$("#rd_div").append('<div class="icon_amount">' +
										'<abbr title="' + temp.products[i].product_name + '">' +
										'<img class="product_icon" src="../product_icons/' + temp.products[i].product_icon + '"' +
										' alt="' + temp.products[i].product_icon + '"></abbr>' +
										'<p class="amount">' + temp.products[i].amount + '</p>' +
										'</div>');
				}
				$("#rd_div").append('<p class="button blue check_again" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(250);
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
		submitData(data, url, replyRewards, false);
	});
	
	/* chat */
	var flag = 'old';
	var scrolled_down = false;
	getMessages();
	
	/* resize input field automatically */
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
			console.log(reply);
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
					$('#' + temp.messages[i].message_id + ' .chat_he_mass').html(
						'<img class="chat_he_pic" src="' + $('#' + temp.messages[i].message_id + ' .chat_he_pic').attr("src") + '" alt="user image">' +
						temp.messages[i].message
					);
				}
				else if(temp.messages[i].is_me === true) {//my msgs
					$('#messages').append('<div class="msg_info" id="' + temp.messages[i].message_id + '">' +
									  '<p class="chat_my_message_time">' + temp.messages[i].cor_date + 
									  ' ' + temp.messages[i].cor_time + '</p>' +
									  '<p class="chat_my_mass">' + 
									  '<img class="chat_my_pic" src="../user_images/' + temp.messages[i].user_image + '" alt="user image">' +
									  temp.messages[i].message + '</p>' +
									  '<p class="edit">Edit</p>' +
									  '<p class="delete">Delete</p>' +
									  '</div>');
				}
				else {//other users msg
					let chat_admin_msg = "";
					if(temp.messages[i].from_admin) {
						chat_admin_msg = "chat_admin_msg";
					}
					$('#messages').append('<div class="msg_info" id="' + temp.messages[i].message_id + '">' +
									  '<a class="chat_msg_sender" href="user_profile?id=' + temp.messages[i].from_user_id + '">' +
									  temp.messages[i].user_name +
									  '</a>' +
									  '<p class="chat_he_message_time">' +
									  temp.messages[i].cor_date + ' ' + temp.messages[i].cor_time + '</p>' +
									  '<p class="chat_he_mass ' + chat_admin_msg + '">' +
									  '<img class="chat_he_pic" src="../user_images/' + temp.messages[i].user_image + '" alt="user image">' +
									  temp.messages[i].message + '</p>' +
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
		
		var message = $('#' + message_id + ' .chat_my_mass').text();
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
				console.log();
				$('#' + message_id + ' .chat_my_mass').html(
					'<img class="chat_my_pic" src="' + $('#' + message_id + ' .chat_my_pic').attr("src") + '" alt="user image">' +
					temp.msg
				);
			}
			message_id = undefined;
		}
		submitData(data, url, editReply);
	}
	
	/* send message to the server*/
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
	
	/* record if post was viewed */
	var view_time = 5000; //5sec
	var view_posts = {};
	var vp = 0;//for view posts indexing 
	//get all posts in the viewport
	detectPostViews();
	function detectPostViews() {
		$('.post_details').each(function() {
			var e = $(this).children('.post_div');
			var top_of_element = $(e).offset().top;
			var bottom_of_element = $(e).offset().top + $(e).outerHeight();
			var bottom_of_screen = $(window).scrollTop() + window.innerHeight;
			var top_of_screen = $(window).scrollTop();
			var element_height = $(e).outerHeight();
			if(element_height <= 0) {
				return true;
			}
			//element is in the viewport
			if((bottom_of_screen > top_of_element) && (top_of_screen < bottom_of_element)) {
				var visible_part = bottom_of_screen - top_of_element;
				var px_percentage = 100 / element_height;
				var visible_percent = px_percentage * visible_part;
				
				//if post height > 250 then must be visible at least 251
				if(visible_part < 250 && element_height > 250) {
					return true;
				}
				//if post height <= 250 then must be visible in all height
				if(element_height <= 250 && visible_percent < 100) {
					return true;
				}
				
				//element is visible
				var post_id = $(this).children('.get_post_id').html();
				var timestamp = $.now();
				for(var x = 0; x < vp; x++) {
					if(view_posts["_" + x].post_id == post_id) {
						if((view_posts["_" + x].timestamp + view_time) <= timestamp && !view_posts["_" + x].recorded
						&& view_posts["_" + x].detected) {
							markAsViewd(post_id);
							view_posts["_" + x].recorded = true;
						}
						//if scrolled away from the post before and then came back.
						else if(view_posts["_" + x].detected == false) {
							view_posts["_" + x].timestamp = timestamp;
						}
						view_posts["_" + x].in_view = true;
						view_posts["_" + x].detected = true;
						return true;
					}
				}
				view_posts["_" + vp] = {post_id: post_id, timestamp: timestamp, detected: true, recorded: false, in_view: true};
				vp++;
			}
		});
		for(var x = 0; x < vp; x++) {
			//if not in te viewport anymore
			if(!view_posts["_" + x].in_view) {
				view_posts["_" + x].detected = false;
			}
			view_posts["_" + x].in_view = false;
		}
		var post_views_t = setTimeout(detectPostViews, 1000);
	}

	function markAsViewd(post_id) {
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('action', 'mark_as_viewed');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#post_' + post_id).find('.views').addClass('liked_viewd');
				$('#post_' + post_id).find('.views').html('<i class="fa fa-eye" aria-hidden="true"></i> ' +
													 numberFormat(temp.views, 0, '', ' '));
			}
		}
		submitData(form_data, url, showInfo, false);
	}
	
	/* like/unlike post */
	$('#blog').on('click', '.likes i', function() {
		var post_id = $(this).parent().parent().parent().children('.get_post_id').html();
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('action', 'like_unlike');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				if(temp.liked) {
					$('#post_' + post_id).find('.likes').addClass('liked_viewd');
					$('#post_' + post_id).find('.likes').html('<i class="fas fa-thumbs-up"></i> ' +
														 numberFormat(temp.likes, 0, '', ' '));
				}
				else {
					$('#post_' + post_id).find('.likes').removeClass('liked_viewd');
					$('#post_' + post_id).find('.likes').html('<i class="far fa-thumbs-up"></i> ' +
														 numberFormat(temp.likes, 0, '', ' '));
				}
			}
		}
		submitData(form_data, url, showInfo);
	});
	
	/* load more posts */
	$('#load_more_posts').on('click', function() {
		var blog_id = 'all';
		var posts_loaded = $('#posts_loaded').html();
		var form_data = new FormData();
		form_data.append('posts_loaded', posts_loaded);
		form_data.append('blog_id', blog_id);
		form_data.append('action', 'load_more_posts');
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#posts_loaded').html(temp.posts_loaded);
				
				for(var x = 0; x < temp.posts.length; x++) {
					temp.posts[x].post = $("<textarea/>").html(temp.posts[x].post).text();
					
					var comments = '';
					for(var u = 0; u < temp.posts[x].comments.length; u++) {
						comments += '<div class="comment_div" id="_' + temp.posts[x].comments[u].comment_id + '">' +
									'<p class="comment_id" hidden>' + temp.posts[x].comments[u].comment_id + '</p>' +
									'<p class="time">' + temp.posts[x].comments[u].comment_date + 
									' <a href="user_profile?id=' + temp.posts[x].comments[u].posted_by_id + 
									'">' + temp.posts[x].comments[u].user_name + '</a></p>';
							 if(temp.posts[x].comments[u].owner) {
								comments += '<p class="edit_comment">Edit</p>' +
											'<p class="delete_comment">Delete</p>';
							 }
						comments += '<img src="../user_images/' + temp.posts[x].comments[u].user_image + '" alt="user image">' +
									'<p class="comment_msg">' + temp.posts[x].comments[u].comment + '</p>' +
									'</div>';
					}
					
					$('#load_more_posts').before('<div class="post_details" id="post_' + temp.posts[x].post_id + '">' +
										  '<p class="get_post_id" hidden>' + temp.posts[x].post_id + '</p>' +
										  '<img class="post_blog_img" src="../blog_images/' + temp.posts[x].blog_image + '">' +
										  '<a class="blog_name_link" href="blog_info?blog_id=' + temp.posts[x].blog_id + 
										  '">' + temp.posts[x].blog_name + '</i></a>' +
										  '<p class="post_date">' + temp.posts[x].post_date + '</p>' +
										  '<p class="edit_post_date">' + temp.posts[x].edit_post_text + '</p>' +
										  '<a class="post_title" href="blog_info?blog_id=' + temp.posts[x].blog_id + 
										  '&post_id=' + temp.posts[x].post_id + '">' + temp.posts[x].title + '</a>' +
										  '<div class="post_div">' + temp.posts[x].post + '</div>' +
										  '<div class="post_views_likes_div">' +
										  '<p class="likes ' + temp.posts[x].liked_class + '">' +
										  '<i class="fa ' + temp.posts[x].liked_tumb + '" aria-hidden="true"></i> ' + 
										  numberFormat(temp.posts[x].likes, 0, '', ' ') + '</p>' +
										  '<p class="views ' + temp.posts[x].viewd_class + '">' +
										  '<i class="fa fa-eye" aria-hidden="true"></i> ' + 
										  numberFormat(temp.posts[x].views, 0, '', ' ') + '</p>' +
										  '</div>' +
										  '<div class="comments_div">' +
										  comments +
										  '</div>' +
										  '<textarea class="comment_entry" maxlength="500"></textarea>' +
										  '<p class="error_comment_reply"></p>' +
										  '<span class="send_comment glyphicon glyphicon-send"></span>' +
										  '</div>');
				}
			}
			else {
				$('#load_more_posts').replaceWith('<p id="no_more_posts">End</p>');
			}
		}
		submitData(form_data, url, showInfo);
	});
	
	/* comment */
	var comment_id = undefined;
	var comment_post_id = undefined;
	
	$('#blog').on('click', '.send_comment', function() {
		var post_id = $(this).parent().children('.get_post_id').html();
		var comment = $(this).parent().children('.comment_entry').val();
		var e = this;
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('comment', comment);
		if(comment_id && comment_post_id == post_id) {
			form_data.append('comment_id', comment_id);
			form_data.append('action', 'edit_comment');
		}
		else {
			form_data.append('action', 'post_comment');
		}
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#post_' + post_id).children('.error_comment_reply').empty();
				
				if(comment_id && comment_post_id == post_id) {
					$('#_' + comment_id).children('.comment_msg').html(temp.comment);
					$(e).parent().children('.comment_entry').val('');
					comment_id = undefined;
					comment_post_id = undefined;
				}
				else {
					$(e).parent().children('.comment_entry').val('');
					$('#post_' + post_id).children('.error_comment_reply').empty();
					$('#post_' + post_id + ' .comments_div').append('<div class="comment_div" id="_' + temp.comment_id + '">' +
						'<p class="comment_id" hidden>' + temp.comment_id + '</p>' +
						'<p class="time">' + temp.time + ' <a href="user_profile?id=' + temp.user_id + 
						'">' + $('#user_name').html() + '</a></p>' +
						'<p class="edit_comment">Edit</p>' +
						'<p class="delete_comment">Delete</p>' +
						'<img src="' + $('#user_img img').attr('src') + '" alt="user image">' +
						'<p class="comment_msg">' + temp.comment + '</p>' +
						'</div>');
				}
			}
			else {
				$('#post_' + post_id).children('.error_comment_reply').html(temp.error);
			}
		}
		submitData(form_data, url, showInfo);
	});
	
	$('#blog').on('click', '.delete_comment', function() {
		var comment_id = $(this).parent().children('.comment_id').html();
		var e = this;
		var data = new FormData();
		data.append('comment_id', comment_id);
		data.append('action', 'delete_comment');
		
		var url = "../etc/manage_blog";
		function deleteReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$(e).parent().remove();
			}
			else {
				$('#' + post_id).children('.error_comment_reply').html(temp.error);
			}
		}
		submitData(data, url, deleteReply);
	});
	
	$('#blog').on('click', '.edit_comment', function() {
		comment_post_id = $(this).parent().parent().parent().children('.get_post_id').html();
		comment_id = $(this).prev().prev().html();
		var comment = $(this).parent().children('.comment_msg').html();
		$(this).parent().parent().parent().children('.comment_entry').val(comment);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
});