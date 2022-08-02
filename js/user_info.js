$(document).ready(function() {
	//update clock
	updateRemainingTime();
	function updateRemainingTime() {
		if($('#until_day_change').length) {
			countdownClock('#until_day_change');
			var t = setTimeout(updateRemainingTime, 1000);
		}
	};

	var usr_name = $('#user #user_name').text();
	$('#user #user_name').on('mouseover', function() {
		$(this).text('View profile');
		$(this).css('backgroundColor', 'rgb(189, 189, 189)');
		$(this).css('boxShadow', 'inset 0px 0px 25px 0px rgb(53, 53, 53)');
		$(this).css('fontSize', '20px');
	});
	$('#user #user_name').on('mouseout', function() {
		$(this).text(usr_name);
		$(this).css('backgroundColor', 'rgb(91, 165, 202)');
		$(this).css('boxShadow', 'inset 0px 0px 25px 0px rgb(61, 105, 128)');
		$(this).css('fontSize', '23px');
	});
	
	
	/* daily missions */
	$('#for_popups_pop').on('click', '.collect_mission_reward', function() {
		var mission_level = $(this).next().html();
		var e = this;
		var data = new FormData();
		data.append('mission_id', mission_id);
		data.append('mission_level', mission_level);
		data.append('action', 'get_daily_mission_rewards');
		var url = "../etc/user_info";
		function deleteReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				setNewExperience();
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');

				for(var x = 0; x < temp.rewards.length; x++) {
					$('#reply_info').append('<div class="icon_amount">' +
											'<abbr title="' + temp.rewards[x].product_name + '">' +
											'<img class="product_icon" src="../product_icons/' + temp.rewards[x].product_icon + 
											'" alt="' + temp.rewards[x].product_name + '"></abbr>' +
											'<p class="amount">' + temp.rewards[x].amount + '</p>' +
											'</div>');
				}
				
				$('#reply_info').append('<div class="icon_amount">' +
										'<i class="glyphicon glyphicon-star" aria-hidden="true"></i>' +
										'<p class="amount">' + temp.exp_reward + '</p>' +
										'</div>');
			
				$(e).replaceWith('<p class="collected_mission_reward">Collected</p>');
				$(e).next().remove();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop2').fadeIn(300);
		}
		submitData(data, url, deleteReply);
	});
	
	/* mission details */
	var mission_id = null;
	$('#daily_missions_div img').on('click', function() {
		mission_id = $(this).next().html();
		var data = new FormData();
		data.append('mission_id', mission_id);
		data.append('action', 'daily_mission_details');
		
		var url = "../etc/user_info";
		function deleteReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$("#for_popups_pop").empty();
			if(temp.success === true) {
				$('#for_popups_pop').prepend('<div id="mission_details"></div>');
				$('#mission_details').append('<p id="mission_name">' + temp.mission_name + '</p>' +
										'<p id="mission_description">' + temp.mission_description + '</p>'
										);
										
				
				$('#mission_details').append('<div class="mission_levels_heads">' +
										'<p id="mission_level_lbl">Level</p>' +
										'<p id="missions_req_lbl">Progress</p>' +
										'</div>');
										
				for(var x = 0; x < temp.levels.length; x++) {
					
					if(temp.levels[x].done == true && temp.levels[x].collected == false) {
						var collect_button = '<p class="collect_mission_reward">Collect</p>' +
											 '<p hidden>' + temp.levels[x].mission_level + '</p>';
					}
					else if (temp.levels[x].collected == true) {
						var collect_button = '<p class="collected_mission_reward">Collected</p>';
					}
					else {
						var collect_button = '';
					}
					
					$('#mission_details').append('<div class="mission_levels">' +
											'<p class="ml_level">' + temp.levels[x].mission_level + '</p>' +
											'<p class="ml_progress">' + temp.levels[x].user_progress + '/' + 
											temp.levels[x].level_req + '</p>' +
											collect_button +
											'</div>');
				}
				
				$('#mission_details').append('<div id="mission_rewards">' +
										'<p id="mr_head">Mission rewards</p>' +
										'</div>');
				for(var x = 0; x < temp.rewards.length; x++) {
					$('#mission_rewards').append('<div class="icon_amount">' +
										'<abbr title="' + temp.rewards[x].product_name + '">' +
										'<img class="product_icon" src="../product_icons/' + temp.rewards[x].product_icon + 
										'" alt="' + temp.rewards[x].product_name + '"></abbr>' +
										'<p class="amount">' + temp.rewards[x].amount + '</p>' +
										'</div>');
				}
				
				$('#mission_rewards').append('<div class="icon_amount">' +
										'<i class="glyphicon glyphicon-star" aria-hidden="true"></i>' +
										'<p class="amount">' + temp.exp_reward + '</p>' +
										'</div>');
				$('#mission_details').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			else {
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, deleteReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#cancel', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
});