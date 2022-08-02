$(document).ready(function() {
	
	/* Create party */
	$('#create_party').on('click', function() {
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="create_party_info"></div>');
		$('#create_party_info').append('<p id="msg">Enter information about new party:</p>');
		$('#create_party_info').append('<input id="new_party_name" placeholder="Party Name" maxlength="20">');
		$('#create_party_info').append('<textarea id="new_party_desc" maxlength="350" placeholder="Description"></textarea>');	
		$('#create_party_info').append('<p id="party_img_info">Image(max 500kb):</p>');
		$('#create_party_info').append('<input type="file" id="party_img_upload">');
		$('#create_party_info').append('<p class="button green" id="create_blank_party">Create</p>');
		$('#create_party_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#create_blank_party', function() {
		var name = $('#new_party_name').val();
		var description = $('#new_party_desc').val();
		var form_data = new FormData();
		form_data.append('image', $('#party_img_upload')[0].files[0]);
		form_data.append('name', name);
		form_data.append('description', description);
		form_data.append('action', 'create');
		var url = "../etc/political_party_manage";
		function showInfo(reply) {
			var temp = reply.split("|");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#container').empty();
				$('#container').append(temp[2]);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		submitData(form_data, url, showInfo);
	});
	
	
	/* dissolve party */
	$('#container').on('click', '#dissolve_party', function() {
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to dissolve this party?</p>');
		$('#reply_info').append('<p class="button red" id="dissolve">Dissolve</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#dissolve', function() {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#container').empty();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("action=disband");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* edit party */
	$('#container').on('click', '#edit_party', function() {
		var party_name = $('#party_name').html();
		var party_desc = $('#party_desc').html();
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="create_party_info"></div>');
		$('#create_party_info').append('<p id="msg">Edit information about your party:</p>');
		$('#create_party_info').append('<input id="new_party_name" maxlength="20" value="' + party_name + '">');
		$('#create_party_info').append('<input id="new_party_leader" placeholder="New Party Leader ID" maxlength="7">');
		$('#create_party_info').append('<textarea id="new_party_desc" maxlength="350">' + party_desc + '</textarea>');	
		$('#create_party_info').append('<p id="party_img_info">Image(max 500kb):</p>');
		$('#create_party_info').append('<input type="file" id="party_img_upload">');
		$('#create_party_info').append('<p class="button green" id="edit_prty">Edit</p>');
		$('#create_party_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#edit_prty', function() {
		var name = $('#new_party_name').val();
		var description = $('#new_party_desc').val();
		var new_leader = $('#new_party_leader').val();
		var form_data = new FormData();
		form_data.append('image', $('#party_img_upload')[0].files[0]);
		form_data.append('name', name);
		form_data.append('new_leader', new_leader);
		form_data.append('description', description);
		form_data.append('action', 'edit');
		var url = "../etc/political_party_manage";
		function showInfo(reply) {
			var temp = reply.split("|");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				
				var info = temp[2].split(", ");
				$('#party_name').html(info[0]);
				$('#party_desc').html(info[1]);
				$('#party_leader').html("Party leader: <i>" + info[3] + "</i>");
				$('#party_leader').attr("href", "user_profile?id=" + info[2]);
				
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		submitData(form_data, url, showInfo);
	});
	
	/* join party */
	$('#container').on('click', '.join_party', function() {
		var party_id = $(this).attr('id');
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$(e).html('Cancel');
				$(e).removeClass('join_party');
				$(e).addClass('cancel_application');
				$(e).removeClass('green');
				$(e).addClass('red');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("party_id=" + party_id + "&action=join");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* cancel application */
	$('#container').on('click', '.cancel_application', function() {
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$(e).html('Join');
				$(e).removeClass('cancel_application');
				$(e).addClass('join_party');
				$(e).removeClass('red');
				$(e).addClass('green');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=cancel_app");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* kick party member */
	$('#container').on('click', '.kick_member', function() {
		var member_id = $(this).attr('id');
		var member_name = $(this).prev().html();
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to kick <i>' + member_name + '</i> from this party?</p>');
		$('#reply_info').append('<p class="button red yes_kick" id="' + member_id + '">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '.yes_kick', function() {
		var member_id = $(this).attr('id');
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#' + member_id).parent().remove();
				
				//update amount of party members
				var party_members = $('#party_members_head').html();
				party_members = party_members.match(/\d+/);
				party_members--;
				$('#party_members_head').html("Party members(" + party_members + ")");
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("member_id=" + member_id + "&action=kick");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* view applications */
	$('#container').on('click', '#view_applications', function() {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("||");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="show_party_users_apps"></div>');
			if(reply_array[0] == 1) {
				$('#show_party_users_apps').append('<p id="msg">Party applications:</p>');
				var temp = reply_array[2].split('|');
				var btn = "";
				for(var x = 0; x < temp.length - 1; x++) {
					var t = temp[x].split(', ');
				
					if(reply_array[1] == 1) {
						btn = '<p class="button blue accept_member" id="' + t[0] + '">Accept</p>'
					}
					else {
						btn = "";
					}
					
					$('#show_party_users_apps').append('<div class="member_div">' +
											'<img class="member_img" src="../user_images/' + t[2] + '">' +
											'<a class="member_name" href="user_profile?id=' + t[0] + '" target="_blank">' + t[1] + '</a>' +
											 btn +
											'</div>');
				}
			}
			else {
				$('#show_party_users_apps').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#show_party_users_apps').append('<p id="msg">' + reply_array[1] + '</p>');
			}
			$('#show_party_users_apps').append('<p class="button blue spua_ok" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=view_apps");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//accept member
	$('#for_popups_pop').on('click', '.accept_member', function() {
		var member_id = $(this).attr('id');
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				
				//update amount of applications
				var applications_left = $('#view_applications').html();
				applications_left = applications_left.match(/\d+/);
				applications_left--;
				$('#view_applications').html("View(" + applications_left + ")");
				
				//update amount of party members
				var party_members = $('#party_members_head').html();
				party_members = party_members.match(/\d+/);
				party_members++;
				$('#party_members_head').html("Party members(" + party_members + ")");
				
				//update party members
				$(e).removeClass('accept_member');
				$(e).addClass('kick_member');
				$(e).removeClass('blue');
				$(e).addClass('red');
				$(e).html('Kick');
				$('#members_div').append('<div class="member_div">' + $(e).parent().html() + '</div>');
				$(e).parent().remove();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("member_id=" + member_id + "&action=accept_member");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* leave party */
	$('#leave_party').on('click', function() {
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to leave this party?</p>');
		$('#reply_info').append('<p class="button red " id="leave_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#leave_yes', function() {
		var member_id = $(this).attr('id');
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("action=leave_party");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* congress manage */
	$('#container').on('click', '#view_elections_info', function() {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("||");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="show_cong_candidates"></div>');
			if(reply_array[0] == 1) {
				$('#show_cong_candidates').append('<p id="msg">Congress candidates from party:</p>');
				if(reply_array[1] == 1) {
					$('#show_cong_candidates').append('<p class="button blue" id="add_candidates">Add New</p>');
				}
				var temp = reply_array[2].split('|');
				for(var x = 0; x < temp.length - 1; x++) {
					var t = temp[x].split(', ');
				
					if(reply_array[1] == 1) {//display btns
					$('#show_cong_candidates').append('<div class="candidate_div" id="' + t[0] + '">' +
											'<p class="pos_num">' + t[3] + '</p>' +
											'<a class="candidate_name" href="user_profile?id=' + t[0] + '" target="_blank">' + t[1] + '</a>' +
											'<img class="candidate_img" src="../user_images/' + t[2] + '">' +
											'<p class="button red remove_candidate">Remove</p>' +
											'<p class="button green move_up_candidate">Up</p>' +
											'<p class="button blue move_down_candidate">Down</p>' +
											'</div>');
					}
					else {//no permission. view only
						$('#show_cong_candidates').append('<div class="candidate_div">' +
											'<p class="pos_num">' + t[3] + '</p>' +
											'<a class="candidate_name" href="user_profile?id=' + t[0] + '" target="_blank">' + t[1] + '</a>' +
											'<img class="candidate_img" src="../user_images/' + t[2] + '">' +
											'</div>');
					}
				}
			}
			else {
				$('#show_cong_candidates').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#show_cong_candidates').append('<p id="msg">' + reply_array[1] + '</p>');
			}
			$('#show_cong_candidates').append('<p class="button blue scc_ok" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=manage_cong_elec");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//add candidates
	$('#for_popups_pop').on('click', '#add_candidates', function() {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("||");
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="show_party_users_apps"></div>');
			if(reply_array[0] == 1) {
				$('#show_party_users_apps').append('<p id="msg">Add new candidate:</p>');
				var temp = reply_array[1].split('|');
				for(var x = 0; x < temp.length - 1; x++) {
					var t = temp[x].split(', ');	
					$('#show_party_users_apps').append('<div class="member_div">' +
											'<img class="member_img" src="../user_images/' + t[2] + '">' +
											'<a class="member_name" href="user_profile?id=' + t[1] + '" target="_blank">' + t[0] + '</a>' +
											'<p class="button blue add_new_candidate" id="' + t[1] + '">Add</p>' +
											'</div>');
				}
			}
			else {
				$('#show_party_users_apps').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#show_party_users_apps').append('<p id="msg">' + reply_array[1] + '</p>');
			}
			$('#show_party_users_apps').append('<p class="button blue spua_ok" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=add_pty_candidates");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//add new candidate
	$('#for_popups_pop2').on('click', '.add_new_candidate', function() {
		var member_id = $(this).attr('id');
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("|");
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="show_party_users_apps"></div>');
			if(reply_array[0] == 1) {
				var candidate_img = $(e).prev().prev().attr('src');
				var candidate_name = $(e).prev().html();
				var candidate_link = $(e).prev().attr('href');
				
				$('.scc_ok').before('<div class="candidate_div" id="' + member_id + '">' +
											'<p class="pos_num">' + reply_array[2] + '</p>' +
											'<a class="candidate_name" href="' + candidate_link + '" target="_blank">' + candidate_name + '</a>' +
											'<img class="candidate_img" src="' + candidate_img + '">' +
											'<p class="button red remove_candidate">Remove</p>' +
											'<p class="button green move_up_candidate">Up</p>' +
											'<p class="button blue move_down_candidate">Down</p>' +
											'</div>');
			}
			else {
				$('#show_party_users_apps').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#show_party_users_apps').append('<p id="msg">' + reply_array[1] + '</p>');
			$('#show_party_users_apps').append('<p class="button blue spua_ok" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("member_id=" + member_id + "&action=add_candidate_to_list");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//remove candidate from list
	$('#for_popups_pop').on('click', '.remove_candidate', function() {
		var member_id = $(this).parent().attr('id');
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("||");
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="show_party_users_apps"></div>');
			if(reply_array[0] == 1) {
				$(e).parent().remove();
				
				var temp = reply_array[2].split('|');
				var x = 0;
				$('.candidate_div').each(function() {
					var t = temp[x].split(', ');
					$(this).children().first().html(t[1]);
					x++;
				});
			}
			else {
				$('#show_party_users_apps').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#show_party_users_apps').append('<p id="msg">' + reply_array[1] + '</p>');
			$('#show_party_users_apps').append('<p class="button blue spua_ok" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("member_id=" + member_id + "&action=remove_candidate_from_list");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//move candidate up one position
	$('#for_popups_pop').on('click', '.move_up_candidate', function() {
		var member_id = $(this).parent().attr('id');
		var action = "move_up_candidate";
		moveCandidateDownUp(action, member_id);
	});
	
	//move candidate down one position
	$('#for_popups_pop').on('click', '.move_down_candidate', function() {
		var member_id = $(this).parent().attr('id');
		var action = "move_down_candidate";
		moveCandidateDownUp(action, member_id);
	});
	
	function moveCandidateDownUp(action, member_id) {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("||");
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="show_party_users_apps"></div>');
			if(reply_array[0] == 1) {			
				var temp = reply_array[2].split('|');
				var x = 0;
				$('.candidate_div').each(function() {
					var t = temp[x].split(', ');
					$(this).children().first().html(t[1]);
					x++;
				});
			}
			else {
				$('#show_party_users_apps').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#show_party_users_apps').append('<p id="msg">' + reply_array[1] + '</p>');
			$('#show_party_users_apps').append('<p class="button blue spua_ok" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("member_id=" + member_id + "&action=" + action);
		}
		loadDoc(url, showInfo, sendData);
	}

	/* participate in elections */
	$('#container').on('click', '#join_elections', function() {
		var election_id = $(this).attr('id');
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to participate in these elections?</p>');
		$('#reply_info').append('<p class="button green" id="participate">Participate</p>');
		$('#submit_candidature').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	
	$('#for_popups_pop').on('click', '#participate', function() {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("|");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(reply_array[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				var e = $('#join_elections').get(0);
				$(e).html('Stop Join');
				$(e).removeAttr('join_elections');
				$(e).attr('id', 'stop_join');
				$(e).removeClass('blue');
				$(e).addClass('red');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + reply_array[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=participate_elections");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	
	//cancel candidature
	$('#container').on('click', '#stop_join', function() {
		var election_id = $(this).attr('id');
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to stop participating in these elections?</p>');
		$('#reply_info').append('<p class="button red" id="stop_participate">Stop</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#stop_participate', function() {
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var reply_array = reply.split("|");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(reply_array[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				var e = $('#stop_join').get(0);
				$(e).html('Join Elections');
				$(e).removeAttr('stop_join');
				$(e).attr('id', 'join_elections');
				$(e).removeClass('red');
				$(e).addClass('blue');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + reply_array[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=stop_participate_elections");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$(this).parent().parent().fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$(this).parent().parent().fadeOut(300);
	});
});