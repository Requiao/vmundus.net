$(document).ready(function() {

	//switch between windows
	var menu = {"mm_ban_user": {"is_selected": false, "block_name": "#ban_user_div"},
				"mm_find_multies": {"is_selected": false, "block_name": "#find_multies_div"},
				"mm_game_updates": {"is_selected": false, "block_name": "#game_updates_div"},
				"mm_vpn_users": {"is_selected": false, "block_name": "#vpn_users_div"},
				"mm_product_market_hist": {"is_selected": false, "block_name": "#product_market_hist_div"},
				"mm_country_requests": {"is_selected": false, "block_name": "#country_requests_div"},
			   };
			   
	var selected = 'mm_ban_user';
	$('#mg_menu p').on('click', function() {
		var item = $(this).attr('id');
		menu[selected].is_selected = false;
		$(menu[selected].block_name).css('display', 'none');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
		menu[item].is_selected = true;
	});


	/* product market history */
	$('#pmh_search').on('click', function() {
		var url = "../etc/manage_game";
		var profile_id = $('#pmh_profile_id_input').val();
		var bought_from = $('#pmh_bought_from_input').val();
		var percent = $('#pmh_percent_input').val();
		var country_id = $('#pmh_country_id_input').val();
		var history_days = $('#pmh_days_input').val();
		
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('bought_from', bought_from);
		data.append('percent', percent);
		data.append('country_id', country_id);
		data.append('history_days', history_days);
		data.append('action', 'product_market_history');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#pmh_result').empty();
			$('#pmh_error').empty();
			if(temp.success === true) {
				
				$('#pmh_result').append('<p id="pmh_head">Product Market History</p>');
				$('#pmh_result').append('<table id="pmh_table_result">' +
										'<tr>' +
										'<th>Profile ID</th>' +
										'<th>Bought from</th>' +
										'<th>Ctry</th>' +
										'<th>Position</th>' +
										'<th>Product</th>' +
										'<th>Quant</th>' +
										'<th>Price</th>' +
										'<th>AVG Price</th>' +
										'<th>Date</th>' +
										'<th>Time</th>' +
										'<th>Diff</th>' +
										'<th>OVP</th>' +
										'<th>SIP</th>' +
										'<th>From Gov</th>' +
										'</tr>' +
										'</table>');
				for(var x = 0; x < temp.history.length; x++) {
					if(temp.history[x].overpriced) {
						var overpriced = 'T';
					}
					else {
						var overpriced = '';
					}
					
					if(temp.history[x].trans_same_ip) {
						var trans_same_ip = 'T';
					}
					else {
						var trans_same_ip = '';
					}
					$('#pmh_result tr:last').after(
						'<tr>' +
						'<td><a href="user_profile?id=' + temp.history[x].profile_id + 
						'" target="_blank">' + temp.history[x].profile_id + '</a></td>' +
						'<td><a href="user_profile?id=' + temp.history[x].bought_from + 
						'" target="_blank">' + temp.history[x].bought_from + '</a></td>' +
						'<td><a href="country?country_id=' + temp.history[x].country_id + 
						'" target="_blank">' + temp.history[x].country_id + '</a></td>' +
						'<td>' + temp.history[x].position + '</td>' +
						'<td>' + temp.history[x].product_name + '</td>' +
						'<td>' + temp.history[x].quantity + '</td>' +
						'<td>' + temp.history[x].price + '</td>' +
						'<td>' + temp.history[x].avg_price + '</td>' +
						'<td>' + temp.history[x].date + '</td>' +
						'<td>' + temp.history[x].time + '</td>' +
						'<td>' + temp.history[x].difference + '</td>' +
						'<td>' + overpriced + '</td>' +
						'<td>' + trans_same_ip + '</td>' +
						'<td><a href="country?country_id=' + temp.history[x].from_gov + 
						'" target="_blank">' + temp.history[x].from_gov + '</a></td>' +
						'</tr>');
				}
			}
			else {
				$('#pmh_error').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* find multies */
	$('#fmd_search').on('click', function() {
		var url = "../etc/manage_game";
		var last_login = $('#fmd_last_login_in').val();
		var user_per_ip = $('#fmd_user_ip_in').val();
		var ip = $('#fmd_ip_in').val();
		var profile_id = $('#fmd_profile_id_in').val();
		var data = new FormData();
		data.append('last_login', last_login);
		data.append('user_per_ip', user_per_ip);
		data.append('ip', ip);
		data.append('profile_id', profile_id);
		data.append('action', 'find_multiple_accounts');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#fmd_result').empty();
			if(temp.success === true) {
				
				$('#fmd_result').append('<p id="fmdr_head">Users that share the same IP</p>');
				for(var x = 0; x < temp.multies.length; x++) {
					$('#fmd_result').append(
						'<div class="fmdr_info">' +
						'<a class="fmdri_user"  href="user_profile?id=' + temp.multies[x].profile_id + 
						'" target="_blank">' + temp.multies[x].profile_name + '</a>' +
						'<p class="fmdri_id">' + temp.multies[x].profile_id + '</p>' +
						'<p class="fmdri_ip">' + temp.multies[x].ip + '</p>' +
						'<p class="fmdri_date">' + temp.multies[x].log_date + '</p>' +
						'<p class="fmdri_time">Used IP: ' + temp.multies[x].ip_num + '</p>' +
						'</div>');
				}
			}
			else {
				$('#bud_profile_details_div').append('<p>' + temp.error + '</p>');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* ban user */
	$('#bud_find_user').on('click', function() {
		var url = "../etc/manage_game";
		var profile_id = $('#bud_user_id').val();
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('action', 'bud_find_user');
		function dataReply(reply) {	
			var temp = JSON.parse(reply);
			$('#bud_profile_details_div').empty();
			if(temp.success === true) {
				$('#bud_profile_details_div').append(
					'<p id="budpdd_profile_id" hidden>' + temp.user_info.profile_id + '<p>' +
					'<div id="bud_user_info">' +
					'<a href="user_profile?id=' + temp.user_info.profile_id + '"target="_blank">' +  temp.user_info.user_name + '</a>' +
					'<img src="../user_images/' + temp.user_info.user_image + '">' +
					'</div>');
					
				$('#bud_profile_details_div').append('<div id="bud_user_ban_history"></div>');
				for(var x = 0; x < temp.ban_history.length; x++) {
					$('#bud_user_ban_history').append(
						'<div class="budubh_ban_info">' +
						'<p class="budubh_ban_name">' + temp.ban_history[x].ban_name + '</p>' +
						'<p class="budubh_description">' + temp.ban_history[x].description + '</p>' +
						'<p class="budubh_extra_description">' + temp.ban_history[x].extra_description + '</p>' +
						'<a class="budubh_moderator_name" href="user_profile?id=' + temp.ban_history[x].moderator_id + 
						'" target="_blank">Moderator: ' + temp.ban_history[x].moderator_name + '</a>' +
						'<p class="budubh_points">Points: ' + temp.ban_history[x].points + '</p>' +
						'<p class="budubh_ban_date">' + temp.ban_history[x].ban_date + '</p>' +
						'<p class="budubh_ban_time">' + temp.ban_history[x].ban_time + '</p>' +
						'</div>');
				}
				
				$('#bud_profile_details_div').append('<div id="bud_ban_info">' +
													 '<p id="budbi_msg"></p>' +
													 '<select id="budubh_ban_select"></select>' +
													 '</div>');
				ban_details = {};
				for(var x = 0; x < temp.ban_info.length; x++) {
					ban_details[temp.ban_info[x].ban_id] = {"description": temp.ban_info[x].description,
															"points": temp.ban_info[x].points
															};
					$('#budubh_ban_select').append('<option value="' + temp.ban_info[x].ban_id + '">' + temp.ban_info[x].ban_name + '</option>');
				}
				$('#bud_ban_info').append('<p id="budbi_description"></p>');
				$('#bud_ban_info').append('<p id="budbi_points"></p>');
				$('#bud_ban_info').append('<textarea maxlength="500" id="budbi_extra_description"></textarea>');
				$('#bud_ban_info').append('<input type="text" id="budbi_ban_points">');
				$('#bud_ban_info').append('<p class="button" id="budbi_ban">Ban</p>');
			}
			else {
				$('#bud_profile_details_div').append('<p>' + temp.error + '</p>');
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#bud_profile_details_div').on('click', '#budubh_ban_select', function() {
		var id = $(this).val();
		$('#budbi_description').html(ban_details[id].description);
		$('#budbi_points').html("Points: " + ban_details[id].points);
		$('#budbi_ban_points').val(ban_details[id].points);
	});
	
	$('#bud_profile_details_div').on('click', '#budbi_ban',  function() {
		var url = "../etc/manage_game";
		var profile_id = $('#budpdd_profile_id').html();
		var ban_id = $('#budubh_ban_select').val();
		var description = $('#budbi_extra_description').val();
		var ban_points = $('#budbi_ban_points').val();
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('ban_id', ban_id);
		data.append('description', description);
		data.append('ban_points', ban_points);
		data.append('action', 'ban_user');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#budbi_msg').html(temp.msg);
			}
			else {
				$('#budbi_msg').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* game updates */
	$('#ngu_add_more_desc').on('click', function() {
		$(this).before('<input class="ngu_desc_input" type="text" maxlength="500">');
		
	});
	
	$('#ngu_add').on('click', function() {
		var url = "../etc/manage_game";
		var heading = $('#nguh_input').val();
		var description = '';
		$('.ngu_desc_input').each(function() {
			description += $(this).val()?$(this).val() + '__':'';
		});
		var data = new FormData();
		data.append('heading', heading);
		data.append('description', description);
		data.append('action', 'set_new_game_update');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#gud_reply').html(temp.msg);
			}
			else {
				$('#gud_reply').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#egu_heading_edit').on('click', function() {
		var url = "../etc/manage_game";
		var update_id = $('#eguh_id_input').val();
		var heading = $('#eguh_input').val();
		var data = new FormData();
		data.append('update_id', update_id);
		data.append('heading', heading);
		data.append('action', 'edit_heading_game_update');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#gud_reply').html(temp.msg);
			}
			else {
				$('#gud_reply').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#egu_desc_edit').on('click', function() {
		var url = "../etc/manage_game";
		var description_id = $('#egu_desc_id_input').val();
		var description = $('#egu_desc_input').val();
		var data = new FormData();
		data.append('description_id', description_id);
		data.append('description', description);
		data.append('action', 'edit_desc_game_update');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#gud_reply').html(temp.msg);
			}
			else {
				$('#gud_reply').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#egu_add_desc_edit').on('click', function() {
		var url = "../etc/manage_game";
		var update_id = $('#egu_add_desc_id_input').val();
		var description = $('#egu_add_desc_input').val();
		var data = new FormData();
		data.append('update_id', update_id);
		data.append('description', description);
		data.append('action', 'add_desc_game_update');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#gud_reply').html(temp.msg);
			}
			else {
				$('#gud_reply').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#egu_del_desc').on('click', function() {
		var url = "../etc/manage_game";
		var description_id = $('#egu_desc_del_input').val();
		var data = new FormData();
		data.append('description_id', description_id);
		data.append('action', 'delete_desc_game_update');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#gud_reply').html(temp.msg);
			}
			else {
				$('#gud_reply').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#egu_del_upd_edit').on('click', function() {
		var url = "../etc/manage_game";
		var update_id = $('#egu_upd_del_input').val();
		var data = new FormData();
		data.append('update_id', update_id);
		data.append('action', 'delete_game_update');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#gud_reply').html(temp.msg);
			}
			else {
				$('#gud_reply').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* VPN IPs */
	$('#vpn_search').on('click', function() {
		var url = "../etc/manage_game";
		var last_login = $('#vpn_last_login_input').val();
		var days_in_game = $('#vpn_days_in_input').val();
		var not_stable_ips = $('#vpn_ips_input').val();
		var profile_id = $('#vpn_profile_id_in').val();
		var data = new FormData();
		data.append('last_login', last_login);
		data.append('days_in_game', days_in_game);
		data.append('not_stable_ips', not_stable_ips);
		data.append('profile_id', profile_id);
		data.append('action', 'find_vpn_users');
		function dataReply(reply) {	
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#vpn_error').empty();
			$('#vpn_result').empty();
			if(temp.success === true) {
				$('#vpn_result').append('<p id="vpn_head">Users that use VPN</p>');
				for(var x = 0; x < temp.vpn_users.length; x++) {
					$('#vpn_result').append(
						'<div class="vpn_info">' +
						'<a class="vpn_user"  href="user_profile?id=' + temp.vpn_users[x].profile_id + 
						'" target="_blank">' + temp.vpn_users[x].profile_name + '</a>' +
						'<p class="vpn_user_id">' + temp.vpn_users[x].profile_id + '</p>' +
						'<p class="vpn_ips">Unique IPs: ' + temp.vpn_users[x].ips + '</p>' +
						'<p class="vpn_days_in_game">Days in game: ' + temp.vpn_users[x].days_in_game + '</p>' +
						'</div>');
				}
			}
			else {
				$('#vpn_result').html(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});

	/* Country requests */
	$('#mm_country_requests').on('click', function() {
		var data = new FormData();
		data.append('action', 'get_country_requests');
		var url = "../etc/manage_game";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			$('#country_requests_div').empty();

			if(temp.success == true) {
				temp.requests.map(item => {
					let colors = '';

					item.colors.map(color => {
						colors += '<p style="background-color: ' + color + '">' + color + '</p>';
					});

					$('#country_requests_div').append(
						'<div class="requesting_country">' +
							'<img class="rc_country_flag" alt="country flag"' +
							' src="../country_flags/' + item.country_flag+ '">' +
							'<p class="rc_country_name">' + item.country_name + '</p>' +

							'<p class="accept_country_request button blue" request_id="' + item.request_id + '">Accept</p>' +
							'<p class="decline_country_request button red" request_id="' + item.request_id + '">Decline</p>' +

							'<p class="country_color">Country color: </p>' +
							'<input type="color" class="country_color_input">' +

							'<div class="rc_sample_colors">' +
								colors +
							'</div>' +

							'<div class="shi_details">' +
								'<p class="shid_col1">Capital</p>' +
								'<a class="shid_col2" href="region_info?region_id=39">Maryland</a>' +
							'</div>' +
							'<div class="shi_details">' +
								'<p class="shid_col1">Country abbreviation</p>' +
								'<p class="shid_col2">' + item.country_abbr + '</p>' +
							'</div>' +
							'<div class="shi_details">' +
								'<p class="shid_col1">Currency name</p>' +
								'<p class="shid_col2">' + item.currency_name + '</p>' +
							'</div>' +
							'<div class="shi_details">' +
								'<p class="shid_col1">Currency abbreviation</p>' +
								'<p class="shid_col2">' + item.currency_abbr + '</p>' +
							'</div>' +
							'<div class="shi_details">' +
								'<p class="shid_col1">Fee paid</p>' +
								'<p class="shid_col2">' + item.fee_paid + ' Gold</p>' +
							'</div>' +
							'<div class="shi_details">' +
								'<p class="shid_col1">Requested by:</p>' +
								'<a class="shid_col2" href="user_profile?id=' + item.requested_by_id + 
								'">' + item.requested_by_name + '</a>' +
							'</div>' +
						'</div>'
					);
				});
			}
			else {
				$('#country_requests_div').append(temp.error);
			}
		});
	});

	$('body').on('click', '.decline_country_request', function() {
		let request_id = $(this).attr('request_id');
		var data = new FormData();
		data.append('request_id', request_id);
		data.append('action', 'decline_country_request');
		var url = "../etc/manage_game";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			let modal = new ModalBox('600px');
			if(temp.success === true) {
				$(this).parent().slideUp();

				modal.setSuccessModal(temp.msg);
			}
			else {
				modal.setErrorModal(temp.error);
			}
			modal.displayModal();
		});
	});

	$('body').on('click', '.accept_country_request', function() {
		let request_id = $(this).attr('request_id');
		let country_color = $(this).parent().children('.country_color_input').val();
		let rgb_array = country_color.match(/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i);
		country_color = "rgb(" + parseInt(rgb_array[1],(16)).toString() + "," +
						parseInt(rgb_array[2],(16)).toString() + "," +
						parseInt(rgb_array[3],(16)).toString() + ")";
		var data = new FormData();
		data.append('request_id', request_id);
		data.append('country_color', country_color);
		data.append('action', 'accept_country_request');
		var url = "../etc/manage_game";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			let modal = new ModalBox('600px');
			if(temp.success === true) {
				$(this).parent().slideUp();

				modal.setSuccessModal(temp.msg);
			}
			else {
				modal.setErrorModal(temp.error);
			}
			modal.displayModal();
		});
	});
});
