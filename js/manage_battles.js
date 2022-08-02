$(document).ready( function() {
	//update battle clock.
	updateBattleClock();
	function updateBattleClock() {
		$('.battle_duration').each(function() {
			countupClock(this);
		});
		t = setTimeout(updateBattleClock, 950);
	};
	
	/* add new budget */
	$('.add_budget').on('click', function() {
		var battle_id = $(this).parent().parent().children('.battle_id').html();
		var url = "../etc/manage_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="battle_budget_manage_div"></div>');
			if(temp[0].trim() == "true") {
				$('#battle_budget_manage_div').append('<p id="bbmd_available_budget_head">Ministry budget:</p>');
				$('#battle_budget_manage_div').append('<select id="bbmd_ministry_budget_list"></select>');
				for(var x = 1; x < temp.length - 1; x++) {
					var t = temp[x].split(", ");
					$('#bbmd_ministry_budget_list').append('<option value="' + t[2] + '">' + t[0] + ' ' + t[1] + '</option>');
				}
				$('#battle_budget_manage_div').append('<p id="bbmd_add_to_budget_head">Add new budget:</p>');
				$('#battle_budget_manage_div').append('<input id="bbmd_add_to_budget_input" maxlength="10">');
				$('#battle_budget_manage_div').append('<p id="bbmd_damage_price_head">Damage price for 100D:</p>');
				$('#battle_budget_manage_div').append('<input id="bbmd_damage_price_input" value="1.00" maxlength="7">');
				$('#battle_budget_manage_div').append('<p class="button blue" id="add_new_budget">Add</p>');
				$('#battle_budget_manage_div').append('<p hidden>' + battle_id + '</p>');
				$('#battle_budget_manage_div').append('<p class="button red" id="cancel">Cancel</p>');
			}
			else {
				$('#battle_budget_manage_div').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#battle_budget_manage_div').append('<p id="msg">' + temp[1] + '</p>');
				$('#battle_budget_manage_div').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("battle_id=" + battle_id + "&action=add_budget_info");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//apply add
	$('#for_popups_pop').on('click', '#add_new_budget', function() {
		var battle_id = $(this).next().html();
		var currency_id = $('#bbmd_ministry_budget_list').val();
		var budget = $('#bbmd_add_to_budget_input').val();
		var price = $('#bbmd_damage_price_input').val();
		var url = "../etc/manage_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			$('#for_popups_pop2').html('');
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == "true") {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$("#for_popups_pop").fadeOut(300);
				
				//display new budget
				if(temp[6].trim() == 'defender') {
					$('#b_' + battle_id).children('.defender_budget_info_div').html('<p class="defender_remaining_budget">' + temp[2] + 
										' ' + temp[3] + '</p>' +
										'<p class="defender_damage_price">' + temp[5] + ' ' + temp[3] + ' for 100D</p>' +
										'<p class="button blue edit_budget_defender edit_budget">Edit</p>' +
										'<p hidden="">' + temp[4] + '</p>');
				}
				else if(temp[6].trim() == 'attacker') {
					$('#b_' + battle_id).children('.attacker_budget_info_div').html('<p class="attacker_remaining_budget">' + temp[2] + 
										' ' + temp[3] + '</p>' +
										'<p class="attacker_damage_price">' + temp[5] + ' ' + temp[3] + ' for 100D</p>' +
										'<p class="button blue edit_budget_attacker edit_budget">Edit</p>' +
										'<p hidden="">' + temp[4] + '</p>');
				}
				
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("battle_id=" + battle_id + "&currency_id=" + currency_id + "&action=apply_add_budget" + 
					   "&new_budget=" + budget + "&new_price=" + price);
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* edit budget */
	$('.battle_info_div').on('click', '.edit_budget', function() {
		var battle_id = $(this).parent().parent().children('.battle_id').html();
		var currency_id = $(this).next().html();
		var url = "../etc/manage_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="battle_budget_manage_div"></div>');
			if(temp[0].trim() == "true") {
				$('#battle_budget_manage_div').append('<p id="bbmd_current_budget_head">Battle budget:</p>');
				$('#battle_budget_manage_div').append('<p id="bbmd_current_budget">' + temp[1] + ' ' + temp[4] + '</p>');
				$('#battle_budget_manage_div').append('<p id="bbmd_available_budget_head">Ministry budget:</p>');
				$('#battle_budget_manage_div').append('<p id="bbmd_available_budget">' + temp[5] + ' ' + temp[4] + '</p>');
				$('#battle_budget_manage_div').append('<p id="bbmd_add_to_budget_head">Add to budget:</p>');
				$('#battle_budget_manage_div').append('<input id="bbmd_add_to_budget_input" maxlength="10">');
				$('#battle_budget_manage_div').append('<p id="bbmd_damage_price_head">Damage price for 100D:</p>');
				$('#battle_budget_manage_div').append('<input id="bbmd_damage_price_input" value="' + temp[2] + '" maxlength="7">');
				$('#battle_budget_manage_div').append('<p class="button blue" id="edit_new_budget">Edit</p>');
				$('#battle_budget_manage_div').append('<p hidden>' + temp[3] + '</p>');
				$('#battle_budget_manage_div').append('<p hidden>' + battle_id + '</p>');
				$('#battle_budget_manage_div').append('<p class="button red" id="cancel">Cancel</p>');
			}
			else {
				$('#battle_budget_manage_div').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#battle_budget_manage_div').append('<p id="msg">' + temp[1] + '</p>');
				$('#battle_budget_manage_div').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("battle_id=" + battle_id + "&currency_id=" + currency_id + "&action=edit_budget_info");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//apply edit
	$('#for_popups_pop').on('click', '#edit_new_budget', function() {
		var battle_id = $(this).next().next().html();
		var currency_id = $(this).next().html();
		var add_to_budget = $('#bbmd_add_to_budget_input').val();
		var new_price = $('#bbmd_damage_price_input').val();
		var url = "../etc/manage_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			$('#for_popups_pop2').html('');
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == "true") {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$("#for_popups_pop").fadeOut(300);
				
				//update budget
				if(temp[5].trim() == 'defender') {
					if(temp[2] != 0) {
						$('#b_' + battle_id).children('.defender_budget_info_div').children('.defender_remaining_budget').html(
													 temp[2] + ' ' + temp[3]);
					}
					var currency_abbr = $('#b_' + battle_id).children('.defender_budget_info_div')
										.children('.defender_damage_price').html().split(" ")[1];
					$('#b_' + battle_id).children('.defender_budget_info_div').children('.defender_damage_price').html(
												 temp[4] + ' ' + currency_abbr + ' for 100D');
				}
				else if(temp[5].trim() == 'attacker') {
					if(temp[2] != 0) {
						$('#b_' + battle_id).children('.attacker_budget_info_div').children('.attacker_remaining_budget').html(
													 temp[2] + ' ' + temp[3]);
					}
					var currency_abbr = $('#b_' + battle_id).children('.attacker_budget_info_div')
										.children('.attacker_damage_price').html().split(" ")[1];
					$('#b_' + battle_id).children('.attacker_budget_info_div').children('.attacker_damage_price').html(
												 temp[4] + ' ' + currency_abbr + ' for 100D');												 
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		function sendData(xhttp) {
			xhttp.send("battle_id=" + battle_id + "&currency_id=" + currency_id + "&action=apply_edit_budget" + 
					   "&add_to_budget=" + add_to_budget + "&new_price=" + new_price);
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* start battle */
	//display regions
	$('#snbd_attack_country_id').on('change', function() {
		var country_id = $(this).val();
		$('#snbd_attack_country_id').children("#0").remove();
		var url = "../etc/start_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			if(temp[0].trim() == "true") {
				$('#snbd_attack_region_id').empty();
				for(var x = 1; x < temp.length - 1; x++) {
					var t = temp[x].split(", ");
					$('#snbd_attack_region_id').append('<option value="' + t[0] + '">' + t[1] + '</option>');
				}
			}
		}
		function sendData(xhttp) {
			xhttp.send("country_id=" + country_id  + "&action=get_region_list");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//update product amount for platform
	$('#snbd_attacking_platform_id').on('change', function() {
		var platform_id = $(this).val();
		var url = "../etc/start_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			if(temp[0].trim() == "true") {
				for(var x = 1; x < temp.length - 1; x++) {
					var t = temp[x].split(", ");
					$('#p_' + t[0]).children('p').html(t[1]);
				}
			}
		}
		function sendData(xhttp) {
			xhttp.send("platform_id=" + platform_id  + "&action=get_platform_product_amount");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	//attack
	$('#attack').on('click', function() {
		var region_id = $('#snbd_attack_region_id').val();
		var currency_id = $('#snbd_ministry_budget_list').val();
		var battle_budget = $('#snbd_budget_input').val();
		var damage_price = $('#snbd_damage_input').val();
		var platform_id = $('#snbd_attacking_platform_id').val();
		var url = "../etc/start_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			console.log(reply);
			var temp = reply.split("|");
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == "true") {
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
			xhttp.send("region_id=" + region_id + "&currency_id=" + currency_id + "&battle_budget=" + battle_budget + 
					   "&damage_price=" + damage_price + "&platform_id=" + platform_id + "&action=start_battle");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* set order */
	$('.place_order').on('click', function() {
		var battle_id = $(this).parent().parent().children('.battle_id').html();
		var currency_id = $(this).prev().html();
		var url = "../etc/manage_battle";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|");
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0].trim() == "true") {
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
			xhttp.send("battle_id=" + battle_id + "&currency_id=" + currency_id + "&action=set_order");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
});