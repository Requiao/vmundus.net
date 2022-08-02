$(document).ready(function() {
	/* add to freinds */
	$('body').on('click', '#add_to_friends', function() {
		var profile_id = $('#profile_id').html();
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('action', 'add_to_friends');
		var url = "../etc/manage_user_profile";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if (temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#add_to_friends').remove();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	$('#remove_from_friends').on('click', function() {
		$('#for_popups_pop').empty();
		$('#for_popups_pop').append('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">' + lang.are_you_sure_you_want_to_remove_this_user_from_your_friends + '</p>');
		$('#reply_info').append('<p class="button red" id="yes_remove_friend">' + lang.yes + '</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">' + lang.cancel + '</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#yes_remove_friend', function() {
		var profile_id = $('#profile_id').html();
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('action', 'remove_from_friends');
		var url = "../etc/manage_user_profile";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if (temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#remove_from_friends').attr('id', 'add_to_friends');
				$('#add_to_friends').html('<i class="fa fa-plus" aria-hidden="true"></i>' +
									      '<i class="fa fa-user" aria-hidden="true"></i>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	/* send message */
	$('#compose_message').on('click', function() {
		var profile_id = $('#profile_id').html();
		$("#for_popups_pop").html('');
		$("#for_popups_pop").html('<div id="compose_msg_div">' +
								  '<span id="message_form_close" class="glyphicon glyphicon-remove-circle"></span>' +
								  '<p id="message_heading">' + lang.compose_message + '</p>' +
								  '<p id="msg_reply"></p>' +
								  '<input id="message_heading_input" type="text" maxlength="30" placeholder="' + lang.heading + '">' +
								  '<input id="message_to" readonly type="text" maxlength="7" value="' + profile_id + '">' +
								  '<textarea id="message_input"></textarea>' +
								  '<p id="compose">' + lang.send + '</p>' +
								  '</div>');
		CKEDITOR.replace("message_input", {toolbar : 'basic', height: '250px'});
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
				$("#for_popups_pop").css('display', 'none');
				$("#for_popups_pop").fadeIn(300);
				$("#for_popups_pop").empty();;
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
	
	/* collect level rewards */
	$('#collect_level_rewards').on('click', function() {
		var data = new FormData();
		data.append('action', 'collect_level_rewards');
		var url = "../etc/manage_user_profile";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<p id="cpd_reply"></p>');
				for(var x = 0; x < temp.rewards.length; x++) {
					$('#reply_info').append('<div class="icon_amount">' +
						'<abbr title="' + temp.rewards[x].product_name + 
						'"><img class="product_icon" src="../product_icons/' + temp.rewards[x].product_icon + 
						'" alt="' + temp.rewards[x].product_name + '"></abbr>' +
						'<p class="amount">' + temp.rewards[x].amount + '</p>' +
						'<p class="clr_collect">Collect</p>' +
						'<p  hidden>' + temp.rewards[x].level_id + '</p>' +
						'</div>');
				}
				
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '.clr_collect', function() {
		var level_id = $(this).next().html();
		var e = this;
		var data = new FormData();
		data.append('level_id', level_id);
		data.append('action', 'collect_clr_ok');
		var url = "../etc/manage_user_profile";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#cpd_reply').empty();
			if(temp.success === true) {
				$('#cpd_reply').html(temp.msg);
				$('#cpd_reply').css('color', 'green');
					
				$(e).parent().fadeOut();
				
				if(temp.collected_all == true) {
					$('#collect_level_rewards').slideUp();
				}
			}
			else {
				$('#cpd_reply').html(temp.error);
				$('#cpd_reply').css('color', 'rgb(207, 28, 28)');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* collect referal rewards */
	$('.collect_ref_reward').on('click', function() {
		var user_id = $(this).next().html();
		var event = this;
		var data = new FormData();
		data.append('user_id', user_id);
		data.append('action', 'collect_ref_rewards');
		var url = "../etc/manage_user_profile";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$(event).prev().html('0.0');
				
				$(event).prev().prev().html(temp.new_amount);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	/* switch windows */
	var menu = {"user_friends": {"is_selected": false, "block_name": "#user_friends_div"},
				"user_currency": {"is_selected": false, "block_name": "#user_currency_div"},
				"user_referals": {"is_selected": false, "block_name": "#user_referers_div"},
				"user_achievements": {"is_selected": false, "block_name": "#user_achievements_div"}
			   };
			   
	var selected = 'user_achievements';
	$('#' + selected).css('backgroundColor', 'rgb(255, 255, 255)');
	$('#' + selected).css('borderTop', '2px solid rgb(104, 168, 201)');
	$('#profile_menu p').on('click', function() {
		switchTabs(this);
	});
	$('#user_profile_menu p').on('click', function() {
		switchTabs(this);
	});
	function switchTabs(e) {
		$('#' + selected).css('borderTop', 'none');
		$('#' + selected).css('backgroundColor', '');
		menu[selected].is_selected = false;
		$(menu[selected].block_name).css('display', 'none');
		var item = $(e).attr('id');
		$(e).css('backgroundColor', 'rgb(255, 255, 255)');
		$(e).css('borderTop', '2px solid rgb(104, 168, 201)');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
		menu[item].is_selected = true;
	}
	
	//display country list
	$('#for_popups_pop').on('click', '#country_list', function() {
		$('#countries_div').slideToggle(250);
	});
	$('#for_popups_pop').on('mouseleave', '#countries_div', function() {
		$('#countries_div').slideUp(250);
	});

	$('#for_popups_pop').on('click', '.country', function() {
		$('#country_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var country = $(this).html();
		var country_id = $(this).attr('id');
		$('#country_list').append('<div id="country">' + country + '</div>');
		$('#country_list').append('<p id="get_country_id" hidden>' + country_id + '</p>');
		$('#countries_div').slideUp(250);
	});

	/* change citizenship */
	$('#cz_change').on('click', function() {
		var data = new FormData();
		data.append('action', 'cz_change_info');
		var url = "../etc/manage_user_profile";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			if(temp.success) {
				$('#for_popups_pop').prepend('<div id="travel_info"></div>');
				
				$('#travel_info').append('<p id="msg">' + lang.select_country_to_which_you_want_to_change_your_citizenship + '</p>');
				
				$('#travel_info').append('<div id="country_list"></div>');
				$('#country_list').append('<div id="country"></div>');
				$('#country').append('<p>' + lang.select_country + '</p>');
				$('#country_list').append('<span class="glyphicon glyphicon-menu-down"></span>');
				
				$('#travel_info').append('<div id="countries_div"></div>');
				
				//show countries and regions

				for(var x = 0; x < temp.countries.length; x++) {
					//countries
					$('#countries_div').append('<div class="country" id="' + temp.countries[x].country_id + '">' +
											   '<img src="../country_flags/' + temp.countries[x].flag + '">' +
											   '<p>' + temp.countries[x].country_name + '</p>' +
											   '</div>');
				}
		
				$('#travel_info').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
				$('#travel_info').append('<p class="button blue" id="cz_change_btn">' + lang.apply + '</p>');
			}
			else {
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				if(temp.requesting) {
					$('#reply_info').append('<p class="button red" id="cancel_cz_app">' + lang.yes + '</p>');
					$('#reply_info').append('<p class="button blue" id="cancel">' + lang.no + '</p>');
				}
				else {
					$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
				}
			}
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	$('#for_popups_pop').on('click', '#cz_change_btn', function() {
		var country_id = $('#get_country_id').html();
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('action', 'change_citizenship');
		var url = "../etc/manage_user_profile";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);	
			$('#travel_reply').remove();
			$('#travel_info').append('<div id="travel_reply"></div>');
			if(temp.success === true) {
				$('#travel_reply').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#travel_reply').append('<p>' + temp.msg + '</p>');
			}
			else {
				if(temp.requesting) {
					$('#travel_info').fadeOut(0);
					
					$('#for_popups_pop').prepend('<div id="reply_info"></div>');
					$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
					$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
					$('#reply_info').append('<p class="button red" id="cancel_cz_app">' + lang.yes + '</p>');
					$('#reply_info').append('<p class="button blue" id="cancel">' + lang.no + '</p>');
				}
				else {
					$('#travel_reply').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
					$('#travel_reply').append('<p>' + temp.error + '</p>');
				}
			}
		}
		submitData(data, url, proccessReply);
	});
	
	$('#for_popups_pop').on('click', '#cancel_cz_app', function() {
		var data = new FormData();
		data.append('action', 'cancel_cz_app');
		var url = "../etc/manage_user_profile";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);	
			$('#for_popups_pop').empty()
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	/* collect achievement rewards */
	$('.colect_achiev_reward').on('click', function() {
		var achiev_id = $(this).next().html();
		var e = this;
		var data = new FormData();
		data.append('achiev_id', achiev_id);
		data.append('action', 'collect_achievement_reward');
		var url = "../etc/manage_user_profile";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty()
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				for(i = 0; i < temp.products.length; i++) {
				$('#reply_info').append('<div class="reward_icon"><abbr title="' + temp.products[i].product_name + '">' +
												'<img src="../product_icons/' + temp.products[i].product_icon + 
												'" alt="' + temp.products[i].product_name + '"></abbr>' +
												'<p class="amount">' + temp.products[i].amount + '</p></div>');		
				}
				if(temp.all == true) {
					$(e).remove();
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});

	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
});