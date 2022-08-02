$(document).ready( function() {
	//update clock
	updateLawRemainingTime();
	function updateLawRemainingTime() {
		$('.pli_expires_in').each(function() {
			countdownClock(this);
		});
		var t = setTimeout(updateLawRemainingTime, 950);
	};

	//prepare editor
	if($('#message_input').html() != undefined) {
		var message = $("<textarea/>").html($('#message_input').html()).text();
		CKEDITOR.replace("message_input", {toolbar : 'basic', height: '350px'});
		$('#message_input').val(message);
	}
	
	/* switch windows */
	var menu = {"laws_in_progress": {"block_name": "#laws_in_progress_div"},
				"issue_laws": {"block_name": "#issue_laws_div"},
				"warehouse": {"block_name": "#warehouse_div"},
				"country_currency": {"block_name": "#country_currency_div"}
			   };
	
	var selected = getCookie("country_manage_menu");
	if(selected == "") {
		selected = 'laws_in_progress';
	}
	
	$('#' + selected).css('backgroundColor', 'rgb(255, 255, 255)');
	$('#' + selected).css('borderTop', '3px solid rgb(56, 75, 89)');
	$(menu[selected].block_name).fadeIn(250);
	if(selected == 'warehouse') {
		$('#product_offers_div').fadeIn(250);
	}
	$('#page_menu p').on('click', function() {
		var item = $(this).attr('id');
		$('#' + selected).css('borderTop', 'none');
		$('#' + selected).css('backgroundColor', '');
		$(menu[selected].block_name).css('display', 'none');
		$(this).css('backgroundColor', 'rgb(255, 255, 255)');
		$(this).css('borderTop', '3px solid rgb(56, 75, 89)');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
		
		document.cookie = "country_manage_menu=" + selected + "; path=/";
		
		if(selected == 'warehouse') {
			$('#product_offers_div').fadeIn(250);
		}
		else {
			$('#product_offers_div').fadeOut(0);
		}
	});
	
	/* retire */
	$('#quit_from_gov').on('click', function() {
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to quit government? You will not be able to vote' +
								' and issue laws anymore.</p>');
		$('#reply_info').append('<p class="button red" id="yes_quit">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#yes_quit', function() {
		var data = new FormData();
		data.append('action', 'quit_from_gov');
		var url = "../etc/country_manage";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				$('#container').empty();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button red" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* vote */
	$('.vote').on('click', function() {
		var law_id = $(this).parent().attr('id');
		var yes_no = $(this).attr('id');
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure?</p>');
		$('#reply_info').append('<p class="button green" id="yes_vote">Vote</p>');
		$('#reply_info').append('<p hidden>' + law_id + '</p>');
		$('#reply_info').append('<p hidden>' + yes_no + '</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#yes_vote', function() {
		var law_id = $(this).next().text();
		var decision = $(this).next().next().text();
		var url = "../etc/vote_for_law";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				
				$('#' + law_id).children().last().prev().addClass('pli_no_yes_voted');
				$('#' + law_id).children().last().addClass('pli_no_yes_voted');
				

				$('#' + law_id).children().last().prev().children().first().addClass('pli_times_check_voted');
				$('#' + law_id).children().last().children().first().addClass('pli_times_check_voted');
	
				$('#' + law_id).children().last().prev().children().next().addClass('pli_no_yes_votes_voted');
				$('#' + law_id).children().last().children().next().addClass('pli_no_yes_votes_voted');
				
				if(decision == 0) {
					var votes = $('#' + law_id).children().last().children().next().html();
					votes++;
					$('#' + law_id).children().last().children().next().html(votes);
				}
				else {
					var votes = $('#' + law_id).children().last().prev().children().next().html();
					votes++;
					$('#' + law_id).children().last().prev().children().next().html(votes);
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("law_id=" + law_id + "&decision=" + decision);
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* import/export switch between options */
	var import_perm_emb = -1;
	$('#disp_imprt_perm_info').on('click', function() {
		$('#trade_info_agreement_list').css('display', 'none');
		$('#disp_imprt_perm_div').css('display', 'block');
		import_perm_emb = 1;
	});
	
	$('#disp_emp_embargo_info').on('click', function() {
		$('#disp_imprt_perm_div').css('display', 'none');
		$('#trade_info_agreement_list').css('display', 'block');
		import_perm_emb = 0;
	});
	
	/* change taxes. switch between taxes */
	var new_tax_type = -1;
	$('#disp_product_info_tax').on('click', function() {
		$('#disp_income_tax_div').css('display', 'none');
		$('#disp_product_tax_div').css('display', 'block');
		new_tax_type = 1;
	});
	
	$('#disp_income_info_tax').on('click', function() {
		$('#disp_product_tax_div').css('display', 'none');
		$('#disp_income_tax_div').css('display', 'block');
		new_tax_type = 0;
	});
	
	/* Travel agreement. switch between options */
	var travel_allow_ban = -1;
	$('#disp_allow_travel_info').on('click', function() {
		$('#ban_country_travel').css('display', 'none');
		$('#allow_travel_info_div').css('display', 'block');
		travel_allow_ban = 1;
	});
	
	$('#disp_ban_travel_info').on('click', function() {
		$('#allow_travel_info_div').css('display', 'none');
		$('#ban_country_travel').css('display', 'block');
		travel_allow_ban = 0;
	});
	
	/* Give/Ban permission for foreigners to build companies */
	var build_perm_ban = -1;
	$('#disp_build_perm_info').on('click', function() {
		$('#build_info_perm_list').css('display', 'none');
		$('#disp_build_perm_div').css('display', 'block');
		build_perm_ban = 1;
	});
	
	$('#disp_ban_build_info').on('click', function() {
		$('#disp_build_perm_div').css('display', 'none');
		$('#build_info_perm_list').css('display', 'block');
		build_perm_ban = 0;
	});
	
	/* issue laws */
	var function_laws = [];
	$('.issue_law').on('click', function() {
		var responsibility_id = $(this).attr('id');
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure?</p>');
		$('#reply_info').append('<p class="button green" id="yes_issue">Issue</p>');
		$('#reply_info').append('<p hidden>' + responsibility_id + '</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#yes_issue', function() {
		var responsibility_id = $(this).next().html();
		function_laws[responsibility_id]();
	});
	
	/* change gov term length */
	function_laws[1] = function() {
		var new_term_governor_id = $('#new_term_governor_id').val();
		var new_term = $('#new_term_length').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=1&new_term_governor_id=" + new_term_governor_id + "&new_term=" + new_term);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	/* join/leave unions */
	function_laws[2] = function() {
		var union_id = $('#unions_to_join').val();
		var as_founder = $("input[name='as_founder']:checked").val();
		if(union_id == undefined) {
			union_id = 'leave';
		}
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=2&union_id=" + union_id + "&as_founder=" + as_founder);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Print money
	function_laws[3] = function() {
		var amount = $('#print_amount').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=3&amount=" + amount);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Impeach president.
	function_laws[4] = function() {
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=4");
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Dissolve Congress.
	function_laws[5] = function() {
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=5");
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Import permission/embargo.
	function_laws[6] = function() {
		if(import_perm_emb == -1) {
			return;
		}
		else if(import_perm_emb == 1) {
			var product_id = $('#product_list_for_import_perm').val();
			var from_country_id = $('#country_list_for_import_perm').val();
			var days = $('#imprt_perm_days').val();
			var tax = $('#imprt_perm_tax').val();
		}
		else if(import_perm_emb == 0) {
			var prod_country_id = $('#trade_info_agreement_list').val();
			prod_country_id = prod_country_id.split("_");
			var product_id = prod_country_id[1];
			var from_country_id = prod_country_id[0];
		}
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			if(import_perm_emb == 1) {
				xhttp.send("what_law=6&from_country_id=" + from_country_id + "&product_id=" + product_id + "&days=" + days + 
						   "&tax=" + tax + "&import_perm_emb=" + import_perm_emb);
			}
			else if(import_perm_emb == 0) {
				xhttp.send("what_law=6&from_country_id=" + from_country_id + "&product_id=" + product_id + 
						   "&import_perm_emb=" + import_perm_emb);
			}
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change responsibilities.
	function_laws[8] = function() {
		var position_id = $('#government_positions_list').val();
		var responsibility_id = $('#responsibility_list').val();
		var can_vote = $('input[name=cr_vote]:checked').val()?$('input[name=cr_vote]:checked').val():0;
		var can_issue = $('input[name=cr_issue]:checked').val()?$('input[name=cr_issue]:checked').val():0;
		var add_rem_resp = $('input[name=cr_action]:checked').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=8&position_id=" + position_id + "&responsibility_id=" + responsibility_id + "&can_vote=" + can_vote +
					   "&can_issue=" + can_issue + "&add_rem_resp=" + add_rem_resp);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	$('#rid_close').on('click', function() {//display/hide gov responsibilities
		$("#responsibilities_info").fadeOut(250);
	});
	
	$('#disp_resp_info').on('click', function() {
		$("#responsibilities_info").fadeIn(250);
	});
	
	//Declare war
	function_laws[9] = function() {
		var country_id = $('#war_to_country_id').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=9&war_to_country_id=" + country_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Sign peace treaty.
	function_laws[10] = function() {
		var country_id = $('#peace_with_country_id').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=10&peace_with_country_id=" + country_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Assign ministers.
	function_laws[11] = function() {
		var position_id = $('#ministers_list_id').val();
		var user_id = $('#new_minister_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=11&position_id=" + position_id + "&user_id=" + user_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Assign Prime Minister
	function_laws[12] = function() {
		var user_id = $('#new_prime_minister_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=12&user_id=" + user_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change taxes.
	function_laws[13] = function() {
		if(new_tax_type == -1) {
			return;
		}
		else if(new_tax_type == 1) {
			var product_id = $('#product_list_for_tax').val();
			var tax = $('#new_product_tax').val();
		}
		else if(new_tax_type == 0) {
			var prod_country_id = 0;
			var tax = $('#new_income_tax').val();
		}
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=13&product_id=" + product_id + "&tax=" + tax + "&new_tax_type=" + new_tax_type);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Travel agreement.
	function_laws[14] = function() {
		if(travel_allow_ban == -1) {
			return;
		}
		else if(travel_allow_ban == 1) {
			var from_country_id = $('#allow_country_travel').val();
			var days = $('#travel_days_input').val();
		}
		else if(travel_allow_ban == 0) {
			var from_country_id = $('#ban_country_travel').val();
			var days = 0;
		}
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=14&from_country_id=" + from_country_id + "&days=" + days + "&travel_allow_ban=" + travel_allow_ban);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Give/Ban permission for foreigners to build companies.
	function_laws[15] = function() {
		if(build_perm_ban == -1) {
			return;
		}
		else if(build_perm_ban == 1) {
			var building_id = $('#building_list_for_build_perm').val();
			var from_country_id = $('#country_list_for_build_perm').val();
			var price = $('#build_perm_price').val();
		}
		else if(build_perm_ban == 0) {
			var build_country_id = $('#build_info_perm_list').val();
			build_country_id = build_country_id.split("_");
			var building_id = build_country_id[1];
			var from_country_id = build_country_id[0];
			var price = 0;
		}
		console.log('reply');	
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			console.log(reply);	
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=15&from_country_id=" + from_country_id + "&building_id=" + building_id +
					   "&price=" + price + "&build_perm_ban=" + build_perm_ban);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Assign new union leader
	function_laws[16] = function() {
		var new_union_leader = $('#new_union_id_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=16&new_union_leader=" + new_union_leader);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change government salaries.
	function_laws[17] = function() {
		var position_id = $('#ministers_salary_list_id').val();
		var salary = $('#minister_salary_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=17&position_id=" + position_id + "&salary=" + salary);
		}
		loadDoc(url, showInfo, sendData);
	};

	//Change credit/deposit rate.
	function_laws[18] = function() {
		var rate = $('#rate_input').val();
		var credit_deposit_type =  $('input[name=credit_deposit_type]:checked').val();
		var type =  $('input[name=rate_type]:checked').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=18&rate=" + rate + "&type=" + type + "&credit_deposit_type=" + credit_deposit_type);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Create new union
	function_laws[19] = function() {
		var union_name = $('#new_union_name_input').val();
		var union_abbr = $('#union_abbr_input').val();
		var union_color = $('#union_color_input').val();
		
		var rgb_array = union_color.match(/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i);
		var union_color = "rgb(" + parseInt(rgb_array[1],(16)).toString() + "," +
							 parseInt(rgb_array[2],(16)).toString() + "," +
							 parseInt(rgb_array[3],(16)).toString() + ")";
		
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=19&union_name=" + union_name + "&union_abbr=" + union_abbr + "&color=" + union_color);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Assign new Secretary of the Treasury
	function_laws[20] = function() {
		var user_id = $('#new_bank_manager_input').val();
		var salary = $('#bank_manager_salary_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=20&user_id=" + user_id + "&salary=" + salary);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change production taxes
	function_laws[21] = function() {
		var product_id = $('#products_production_tax').val();
		var tax = $('#new_production_tax').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=21&product_id=" + product_id + "&tax=" + tax);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Budget Allocation.
	function_laws[22] = function() {
		var position_id = $('#ministry_budget_list_id').val();
		var currency_id = $('#ministry_currency_list_id').val();
		var amount = $('#ministry_budget_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=22&position_id=" + position_id + "&currency_id=" + currency_id + "&amount=" + amount);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Sign defence agreement.
	function_laws[24] = function() {
		var country_id = $('#def_with_country_id').val();
		var days = $('#def_days_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=24&defence_wth_country_id=" + country_id + "&days=" + days);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Product Allocation
	function_laws[25] = function() {
		var position_id = $('#ministry_product_list_id').val();
		var product_id = $('#country_product_list_id').val();
		var amount = $('#product_quantity_input').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=25&position_id=" + position_id + "&product_id=" + product_id + "&amount=" + amount);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change timezone.
	function_laws[26] = function() {
		var timezone_id = $('#timezones_id').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=26" + "&timezone_id=" + timezone_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Give/Ban permission for foreigners to build companies.
	function_laws[27] = function() {
		var building_id = $('#building_list_for_citiz_price').val();
		var price = $('#build_citiz_price').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=27" + "&building_id=" + building_id + "&price=" + price);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Remove country from the union
	function_laws[29] = function() {
		var remove_country_id = $('#remove_union_country_id').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=29" + "&remove_country_id=" + remove_country_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Welcome message
	function_laws[30] = function() {
		var heading = $('#message_heading_input').val();
		var message = CKEDITOR.instances.message_input.getData();
		var data = new FormData();
		data.append('heading', heading);
		data.append('message', message);
		data.append('what_law', '30');
		var url = "../etc/issue_laws";
		function dataReply(reply) {
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	};
	
	//Assign titles
	function_laws[31] = function() {
		var title_id = $('#assign_titles_id').val();
		var user_id = $('#title_user_id').val();
		var add_remove = $('input[name=add_remove_title]:checked').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=31" + "&title_id=" + title_id + "&user_id=" + user_id + "&add_remove=" + add_remove);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change Capital Region
	function_laws[34] = function() {
		var region_id = $('#new_capital_region_id').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=34&region_id=" + region_id);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	//Change Region Owner
	function_laws[35] = function() {
		var region_id = $('#owned_region_id').val();
		var country_id = $('#new_owner_country_id').val();
		var price = $('#price_for_new_region_owner').val();
		var url = "../etc/issue_laws";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("what_law=35&region_id=" + region_id + "&country_id=" + country_id + "&price=" + price);
		}
		loadDoc(url, showInfo, sendData);
	};
	
	/* sell products */
	var tax_array = []; //store taxes for each country
	
	$('.sell').on('click', function() {
		var product_id = $(this).next().html();
		var data = new FormData();
		data.append('product_id', product_id);
		data.append('sell_for', 'ministry');
		data.append('action', 'get_info');
		var event = this;
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			if(temp.success == true) {
				var product_element = $(event).parent().html();
				$('#for_popups_pop').prepend('<div id="pop_up_info"></div>');
				$('#pop_up_info').append('<div class="icon_amount">' + product_element + '</div>');
				$('#pop_up_info .sell').remove();
				$('#pop_up_info').append('<select id="countries">');
				
				for(x = 0; x < temp.tax_info.length; x++) {
					tax_array[temp.tax_info[x].country_id] =  temp.tax_info[x].tax;
					$('#pop_up_info #countries').append('<option value="' + temp.tax_info[x].country_id + 
														'">' +  temp.tax_info[x].country_name + '</option>');
				}
				
				$('#pop_up_info').append('</select>');
				$('#pop_up_info').append('<input class="input" id="amount_input" type="text" placeholder="quantity">');
				$('#pop_up_info').append('<input class="input" id="price_input" type="text" placeholder="price">');
				$('#pop_up_info').append('<p id="tax">tax: ' + temp.tax_info[0].tax + '%</p>');
				$('#pop_up_info').append('<p class="button green" id="sell">Offer</p>');
				$('#pop_up_info').append('<p class="button red" id="cancel">Cancel</p>');
			}
			else {
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error+ '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	//change displayed tax amount on country change
	$('#for_popups_pop').on('change', '#countries', function() {
		var id = $(this).children(':selected').val();
		var x = id;
		$('#tax').text('tax: ' + tax_array[x] + '%');
	});
	
	//sell ok
	$('#for_popups_pop').on('click', '#sell', function() {
		var product_id = $('#pop_up_info').children().children().next().next().html();
		var country_id = $('#countries').children(':selected').val();
		var quantity = $('#amount_input').val();
		var price =  $('#price_input').val();
		var data = new FormData();
		data.append('product_id', product_id);
		data.append('country_id', country_id);
		data.append('quantity', quantity);
		data.append('price', price);
		data.append('sell_for', 'ministry');
		data.append('action', 'sell');
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				var amount = parseFloat($('#pi_' + product_id).prev().prev().html().replace(/ /g,""));
				amount = amount - temp.quantity;
				$('#pi_' + product_id).prev().prev().html(numberFormat(amount, 2, '.', ' '));

				$('#product_offers_div').append('<div class="product_on_sale">' +
												'<abbr title="' + temp.product_name + 
												'"><img class="pos_product_icon" src="../product_icons/' + 
												temp.product_icon + '" alt="' + temp.product_name + '"></abbr>' +
												'<img class="country_flag" alt="' + temp.product_name + '" src="../country_flags/' + 
												temp.flag + '">' +
												'<p class="pos_quantity">' + temp.quantity + '</p>' +
												'<p class="pos_price">' + numberFormat(temp.price, 2, '.', ' ') + '</p>' +
												'<p class="pos_currency">' + temp.currency_abbr + '</p>' +
												'<p class="pos_tax">-' + temp.sale_tax + '%</p>' +
												'<p class="pos_remove button red">Remove</p>' +
												'<p id="oi_' + temp.offer_id + '" hidden>' + temp.offer_id + '</p>' +
												'</div>');
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
	
	/* remove product offer */
	$('#product_offers_div').on('click', '.pos_remove', function() {
		var offer_id = $(this).next().html();
		var data = new FormData();
		data.append('offer_id', offer_id);
		data.append('action', 'remove_ministry_offer');
		var e = this;
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			
			if(temp.success === true) {
				$(e).parent().fadeOut(300);
				var quantity = parseFloat(temp.quantity);
				
				var amount = parseFloat($('#pi_' + temp.product_id).prev().prev().html().replace(/ /g,""));
				$('#pi_' + temp.product_id).prev().prev().html(numberFormat((amount + quantity), 2, '.', ' '));
			}
			else {
				$('#for_popups_pop').html('');
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);	
			}
		}
		submitData(data, url, proccessReply);
	});
	
	//offer currency
	//Make offer
	$('#make_offer_btn').on('click', function() {
		$('#make_offer_div').slideToggle();
	});
	
	var offering = 'offer_ministry_gold';
	$('#switch_offering_items').on('click', function() {
		var buying = $('#offering_item').html();
		var selling = $('#offer_for_item').html();
		$('#offering_item').html(selling);
		$('#offer_for_item').html(buying);
		offering = offering=='offer_ministry_gold'?'offer_ministry_currency':'offer_ministry_gold';
	});
	
	$('#make_offer_div').on('click', '#offer_currency_list', function() {
		$('#offer_currency_div').slideToggle(250);
	});
	$('#make_offer_div').on('mouseleave', '#offer_currency_div', function() {
		$('#offer_currency_div').slideUp(250);
	});
	
	$('#make_offer_div').on('click', '.offer_currency', function() {
		var currency = $(this).html();
		var currency_id = $(this).attr('id');
		$('#offer_currency_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		$('#offer_currency_list').append('<div id="offer_selected_currency">' + currency + '</div>');
		$('#offer_currency_list').append('<p id="offer_currency_id" hidden>' + currency_id + '</p>');
		$('#offer_currency_div').slideUp(250);
	});
	
	$('#place_offer_btn').on('click', function() {
		var currency_id = $('#offer_currency_id').html();
		var amount = $('#offering_amount').val();
		var rate = $('#offering_rate').val();
		var data = new FormData();
		data.append('currency_id', currency_id);
		data.append('amount', amount);
		data.append('rate', rate);
		data.append('action', offering);
		var url = "../etc/buy_sell_currency";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				var buy_for_country = '';
				var buy_for_corp = '';
				var buy_for_self = '<p class="button green buy">Buy</p>';
				
				if(temp.is_corp) {
					buy_for_corp = '<p class="button green buy_for_corp">cBuy</p>';
					buy_for_self = '';
				}
				else if(temp.is_governor) {
					var buy_for_country = '<p class="button blue buy_for_country">Ministry</p>';
				}
				
				var i = 0;
				if(offering == 'offer_ministry_currency') {
					$('#user_offers').append('<div class="offer _' + temp.offers[i].offer_id + '">' +
											 '<p class="offer_id" hidden>' + temp.offers[i].offer_id + '</p>' + 
											 '<a href="user_profile?id=' + temp.offers[i].seller_id + '" class="user_name">' +
											 temp.offers[i].seller_name + '</a>' + 
											 '<img class="user_image" src="../user_images/' + temp.offers[i].seller_img + 
											 '" alt="user image">' +
											 '<p class="amount_selling">' + temp.offers[i].amount + 
											 ' ' + temp.offers[i].currency_abbr + '</p>' +
											 '<p class="rate">1 ' + temp.offers[i].currency_abbr + ' = ' + temp.offers[i].rate + '</p>' +
											 '<img class="gold_img" src="../img/gold.png">' +
											 '<p class="button red remove_offer">Remove</p>' +
											 '</div>');	
				}
				else {
					$('#user_offers').append('<div class="offer _' + temp.offers[i].offer_id + '">' +
											'<p class="offer_id" hidden>' + temp.offers[i].offer_id + '</p>' + 
											'<a href="user_profile?id=' + temp.offers[i].seller_id + '" class="user_name">' +
											temp.offers[i].seller_name + '</a>' + 
											'<img class="user_image" src="../user_images/' + temp.offers[i].seller_img + 
											'" alt="user image" target="_new">' +
											'<p class="amount_selling">' + temp.offers[i].amount + ' Gold</p>' +
											'<p class="gold_rate">1 </p>' +
											'<img class="gold_img" src="../img/gold.png">' +
											'<p class="rate">= ' + temp.offers[i].rate + ' ' + temp.offers[i].currency_abbr + '</p>' +
											'<p class="button red remove_offer">Remove</p>' +
											'</div>');	
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	
	/* remove currency offer */
	$('#container').on('click', '.remove_offer', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var data = new FormData();
		data.append('offer_id', offer_id);
		data.append('action', 'remove_ministry_offer');
		var url = "../etc/buy_sell_currency";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('._' + offer_id).slideUp();
			}
			else {
				$('#for_popups').empty();
				$('#for_popups').prepend('<div id="up_down_grade_info"></div>');
				$('#up_down_grade_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#up_down_grade_info').append('<p id="msg">' + temp.error + '</p>');
				$('#up_down_grade_info').append('<p class="button blue" id="co_ok">Upgrade</p>');
				$('#up_down_grade_info').append('<p class="button red" id="cancel">Cancel</p>');
				$('#for_popups').fadeIn(300);
			}
			
		}
		submitData(data, url, dataReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
})
