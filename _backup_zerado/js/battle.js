$(document).ready( function() {
	//update battle clock.
	updateBattleClock();
	function updateBattleClock() {
		if($('#battle_duration').length) {
			countupClock($('#battle_duration').get(0));
			rc = setTimeout(updateBattleClock, 990);
		}
	};
	
	/* switch person */
	var soldier_list = [];
	var p = 0;
	$('.soldier_info').each(function() {
		soldier_list[p] = this;
		p++;
	});
	
	//display first soldier
	if(soldier_list.length > 0) {
		$(soldier_list[0]).css('display', 'block');
		p = 0;
	}
	
	$('#previous_soldier').on('click', function() {
		$(soldier_list[p]).fadeOut();
		p--;
		if(p == -1) {
			var last = soldier_list.length - 1;
			$(soldier_list[last]).fadeIn();
			p = last;
		}
		else {
			$(soldier_list[p]).fadeIn();
		}
	});
	
	$('#next_soldier').on('click', function() {
		nextSoldier();
	});

	const nextSoldier = function() {
		let nextSoldier = new Promise((resolve, reject) => {
			$(soldier_list[p]).fadeOut();
			p++;
			if(p > soldier_list.length - 1) {
				$(soldier_list[0]).fadeIn();
				p = 0;
			}
			else {
				$(soldier_list[p]).fadeIn();
			}
			resolve(true);
		});

		return nextSoldier;
	};
	
	
	/* switch weapon */
	var weapon_list = [];
	var w = 0;
	$('.weapon_info').each(function() {
		weapon_list[w] = this;
		w++;
	});
	
	//display first weapon
	if(weapon_list.length > 0) {
		$(weapon_list[0]).css('display', 'block');
		w = 0;
	}
	
	$('#previous_weapon').on('click', function() {
		$(weapon_list[w]).fadeOut();
		w--;
		if(w == -1) {
			var last = weapon_list.length - 1;
			$(weapon_list[last]).fadeIn();
			w = last;
		}
		else {
			$(weapon_list[w]).fadeIn();
		}
	});
	
	$('#next_weapon').on('click', function() {
		$(weapon_list[w]).fadeOut();
		w++;
		if(w > weapon_list.length - 1) {
			var first = 0;
			$(weapon_list[first]).fadeIn();
			w = first;
		}
		else {
			$(weapon_list[w]).fadeIn();
		}
	});
	
	
	/* switch ammo */
	var ammo_list = [];
	var a = 0;
	$('.ammo_info').each(function() {
		ammo_list[a] = this;
		a++;
	});

	//display first ammo
	if(ammo_list.length > 0) {
		$(ammo_list[0]).css('display', 'block'); 
		a = 0;
	}
	
	$('#previous_ammo').on('click', function() {
		$(ammo_list[a]).fadeOut();
		a--;
		if(a == -1) {
			var last = ammo_list.length - 1;
			$(ammo_list[last]).fadeIn();
			a = last;
		}
		else {
			$(ammo_list[a]).fadeIn();
		}
	});
	
	$('#next_ammo').on('click', function() {
		$(ammo_list[a]).fadeOut();
		a++;
		if(a > ammo_list.length - 1) {
			var first = 0;
			$(ammo_list[first]).fadeIn();
			a = first;
		}
		else {
			$(ammo_list[a]).fadeIn();
		}
	});
	
	
	/* switch armor */
	var armor_list = [];
	var r = 0;
	$('.armor_info').each(function() {
		armor_list[r] = this;
		r++;
	});
	
	//display first armor
	if(armor_list.length > 0) {
		$(armor_list[0]).css('display', 'block');
		r = 0;
	}
	
	$('#previous_armor').on('click', function() {
		$(armor_list[r]).fadeOut();
		r--;
		if(r == -1) {
			var last = armor_list.length - 1;
			$(armor_list[last]).fadeIn();
			r = last;
		}
		else {
			$(armor_list[r]).fadeIn();
		}
	});
	
	$('#next_armor').on('click', function() {
		nextArmor();
	});

	const nextArmor = () => {
		$(armor_list[r]).fadeOut();
		r++;
		if(r > armor_list.length - 1) {
			var first = 0;
			$(armor_list[first]).fadeIn();
			r = first;
		}
		else {
			$(armor_list[r]).fadeIn();
		}
	}
	
	/* switch recovery */
	var recovery_list = [];
	var f = 0;
	$('.recovery_info').each(function() {
		recovery_list[f] = this;
		f++;
	});
	
	//display first recovery
	if(recovery_list.length > 0) {
		$(recovery_list[0]).css('display', 'block');
		f = 0;
	}
	
	$('#previous_food').on('click', function() {
		$(recovery_list[f]).fadeOut();
		f--;
		if(f == -1) {
			var last = recovery_list.length - 1;
			$(recovery_list[last]).fadeIn();
			f = last;
		}
		else {
			$(recovery_list[f]).fadeIn();
		}
	});
	
	$('#next_food').on('click', function() {
		$(recovery_list[f]).fadeOut();
		f++;
		if(f > recovery_list.length - 1) {
			var first = 0;
			$(recovery_list[first]).fadeIn();
			f = first;
		}
		else {
			$(recovery_list[f]).fadeIn();
		}
	});
	
	/* fight btn */
	var flag_refresh_battle = false;
	setFlagToRefresh();
	function setFlagToRefresh() {
		if(flag_refresh_battle == false) {
			flag_refresh_battle = true;
		}
		sf = setTimeout(setFlagToRefresh, 10000);
	};
	
	//refresh battle info
	refreshBattle();
	function refreshBattle() {
		if($('#battle_id').length) {
			if(flag_refresh_battle) {
				var battle_id = $('#battle_id').html();
				var data = new FormData();
				data.append('battle_id', battle_id);
				data.append('action', 'refresh');
				var url = "../etc/fight";
				function dataReply(reply) {	
					var temp = JSON.parse(reply);
					if(temp.success == true) {
						updateBattleInfo(temp);
					}
					else {
						if(temp.ended == true) {
							$('#battle_duration').attr('id', 'battle_ended');
							if(typeof rb !== 'undefined') {
								clearTimeout(rb);
							}
							if(typeof sf !== 'undefined') {
								clearTimeout(sf);
							}
							if(typeof rc !== 'undefined') {
								clearTimeout(rc);
							}
						}
					}
				}
				submitData(data, url, dataReply, false);
			}
			rb = setTimeout(refreshBattle, 10000);
		}
	};

	/* switch auto fight energy */
	var fue_energy = 20;
	$('.fued_energy:nth-of-type(3)').css('backgroundColor', '#4c82b4');
	$('.fued_energy:nth-of-type(3)').css('color', '#fff');

	$('.fued_energy').on('click', function() {
		$('.fued_energy').css('backgroundColor', '#949494');
		$('.fued_energy').css('color', '#000');

		$(this).css('backgroundColor', '#4c82b4');
		$(this).css('color', '#fff');

		fue_energy = parseInt($(this).attr('value'));
	});
	
	/* fight */
	var auto_fight = false;
	var fight_btn_action_txt = $('#auto_fight').attr('btn_text');
	var fight_btn_initial_txt = $('#auto_fight').text();

	$('#fight_btn').on('click', function() {
		if(!auto_fight) {
			fight();
		}
	});

	$('#auto_fight').on('click', function() {
		if(auto_fight == false) {
			auto_fight = true;
			$('#auto_fight').css('color', 'white');
			if(fight_btn_action_txt == 'Defend') {
				$('#auto_fight').css('backgroundColor', 'rgb(51, 90, 125)');
			}
			else {
				$('#auto_fight').css('backgroundColor', 'rgb(155, 52, 52)');
			}
			$('#auto_fight').text('Stop ' + $('#fight_btn').text());
			autoFight();
		}
		else {
			auto_fight = false;
			$('#auto_fight').css('color', 'black');
			$('#auto_fight').css('backgroundColor', '#949494');
			$('#auto_fight').text(fight_btn_initial_txt);
		}
	});

	function autoFight() {
		if(auto_fight) {
			//get persons energy
			let p_energy = parseInt($(soldier_list[p]).attr('energy'));

			//next person
			console.log("need recovery: " + (p_energy <= fue_energy));
			if(p_energy <= fue_energy) {
				recoverEnergy(false).then(reply => {
					console.log("recoverEnergy: " + reply);
					if(reply == false) {
						if(p >= soldier_list.length - 1) {//last soldier
							auto_fight = false;
							$('#for_popups_pop').empty();
							$('#for_popups_pop').append('<div id="reply_info"></div>');
							$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
							$('#reply_info').append('<p id="msg">Last soldier reached</p>');
							$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
							$("#for_popups_pop").fadeIn(300);
							
							$('#auto_fight').css('color', 'black');
							$('#auto_fight').css('backgroundColor', '#949494');
							$('#auto_fight').text(fight_btn_initial_txt);
							return;
						}
						else {
							nextSoldier().then(reply => {
								console.log('next person');
								
								reply ? setTimeout(autoFight, 250) : false;
							});

							return;
						}
					}
					setTimeout(autoFight, 250);
					return;
				});
			}
			else {
				let wounds = parseInt($(soldier_list[p]).children('.wound').attr('wounds'));
				let armor = parseInt($(armor_list[r]).attr('armor'));
				if(wounds != armor) {
					for(let x = 0; x < armor_list.length; x++) {
						if(parseInt($(armor_list[r]).attr('armor')) == wounds) {
							armor = parseInt($(armor_list[r]).attr('armor'));
							break;
						}
						else {
							nextArmor();
						}
					}
				}
			
				let available_armor = parseFloat($(armor_list[r]).attr('amount'));
				if(wounds != armor || available_armor <= 0) {
					nextSoldier().then(reply => {
						console.log('next person');
						
						reply ? setTimeout(autoFight, 250) : false;
					});
					
					return;
				}
				fight(false).then(() => {
					setTimeout(autoFight, 400);
				});

				return;
			}
		}
		else {
			auto_fight = false;
			$('#auto_fight').css('color', 'black');
			$('#auto_fight').css('backgroundColor', '#949494');
			$('#auto_fight').text(fight_btn_initial_txt);
		}
	}

	function fight(display_errors = true) {
		flag_refresh_battle = false;
		var battle_id = $('#battle_id').html();
		var side = $('#side').html();
		var currency_id = $('#currency_id').html();
		var weapon_id = $(weapon_list[w]).attr('id');
		var ammo_id = $(ammo_list[a]).attr('id');
		var armor_id = $(armor_list[r]).attr('id');
		var person_id = $(soldier_list[p]).attr('id');
		var data = new FormData();
		data.append('battle_id', battle_id);
		data.append('side', side);
		data.append('currency_id', currency_id);
		data.append('weapon_id', weapon_id);
		data.append('ammo_id', ammo_id);
		data.append('armor_id', armor_id);
		data.append('person_id', person_id);
		data.append('action', 'fight');
		var url = "../etc/fight";
		return serverRequest(data, url, display_errors).then(reply => {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				setNewExperience();
				setNewUserGold();
				updateBattleInfo(temp);
			}
			else if(display_errors) {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
			}
		});
	};
	
	function updateBattleInfo(temp) {
		//update energy info person details
		if(temp.used_energy) {
			var energy_ar = $(soldier_list[p]).children().last().children('p').html();
			energy_ar = energy_ar.split("/");
			var old_energy = energy_ar[0];
			var new_energy = old_energy - temp.used_energy;
			var new_energy_percent = (new_energy/energy_ar[1]) * 100;
			$(soldier_list[p]).children().last().children('.person_progress').animate({width: + new_energy_percent + '%'});
			$(soldier_list[p]).children().last().children('p').html(new_energy + '/' + energy_ar[1]);
			$(soldier_list[p]).attr('energy', new_energy);
		}	
		//add to user damage
		if(temp.damage) {
			$('#user_damage_temp').css('display', 'none');
			$('#user_damage_temp').html("+" + temp.damage);
			$('#user_damage_temp').fadeIn(0);
			$('#user_damage_temp').fadeOut(700);
		}
		
		//user reward
		if(temp.reward) {
			$('#user_reward_temp').css('display', 'none');
			$('#user_reward_temp').html("+" + temp.reward);
			$('#user_reward_temp').fadeIn(0);
			$('#user_reward_temp').fadeOut(700);
		}
		
		//user reward
		if(temp.user_exp) {
			$('#user_exp_reward_temp').css('display', 'none');
			$('#user_exp_reward_temp').html('<div class="icon_amount">' +
										'<i class="glyphicon glyphicon-star" aria-hidden="true"></i>' +
										'<p class="amount">' + temp.user_exp + '</p>' +
										'</div>');
			$('#user_exp_reward_temp').fadeIn(0);
			$('#user_exp_reward_temp').fadeOut(700);
		}
		
		if(temp.damage) {
			var steps = Math.round((temp.damage / 50) * 100) / 100;
			updateUserDamage(temp.damage, steps);
			function updateUserDamage(damage, steps) {
				var old_damage = parseFloat(($('#user_damage').html()).replace(/\s/g, ''));
				var new_damage = old_damage;
				if(damage > 0) {
					if(damage > steps) {
						new_damage += steps;
						damage -= steps;
					}
					else {
						new_damage += damage;
						damage -= damage;
					}
					new_damage = new_damage.toFixed(2);
					$('#user_damage').html(numberFormat(new_damage, 2, '.', ' '));
					ud = setTimeout(updateUserDamage, 1, damage, steps);
				}
				else {
					clearTimeout(ud);
				}
			}
		}
		
		//update inventory
		if(temp.weapon != 0) {
			var old_amount = parseFloat($(weapon_list[w]).children('.inventory_amount').html().replace(/ /g, ''));
			var new_amount = old_amount - temp.weapon;
			$(weapon_list[w]).children('.inventory_amount').html(new_amount.toFixed(2))
		}
		if(temp.ammo != 0) {
			var old_amount = parseInt($(ammo_list[a]).children('.inventory_amount').html().replace(/ /g, ''));
			var new_amount = old_amount - temp.ammo;
			$(ammo_list[a]).children('.inventory_amount').html(new_amount)
		}
		if(temp.armor != 0) {
			var old_amount = parseFloat($(armor_list[r]).attr('amount'));
			var new_amount = old_amount - temp.armor;
			$(armor_list[r]).children('.inventory_amount').html(new_amount.toFixed(2))
			$(armor_list[r]).attr('amount', new_amount);
		}
		
		//update person info
		if(temp.person_damage) {
			$(soldier_list[p]).children('.base_damage').html(temp.person_damage + " D");
			$(soldier_list[p]).children('.wound').html(temp.wound + " W");
			$(soldier_list[p]).children('.wound').attr('wounds', temp.wound);
			$(soldier_list[p]).children('.combat_exp').html(temp.combat_exp + " CE");
		}
		
		//update force bar
		$('#attacker_force_bar_div').animate({width: + temp.attacker_percentage + "%"});
		$('#afbd_perc').html(temp.attacker_percentage.toFixed(2) + "%");
		$('#defender_force_bar_div').animate({width: + temp.defender_percentage + "%"});
		$('#dfbd_perc').html(temp.defender_percentage.toFixed(2) + "%");
		
		//update strength
		$('#attacker_platform_progress').animate({width: + Math.floor(temp.platform_percentage * 10000) / 10000 + "%"});
		$('#attacker_platform_bar_div p').html(temp.platform_percentage + "%");
		$('#attacker_platform_info').html(numberFormat(temp.attacker_strength, 2, '.', ' ') + 
		"/" + numberFormat(temp.attacker_fixed_strength, 2, '.', ' '));
		
		$('#defender_position_progress').animate({width: + Math.floor(temp.position_percentage * 10000) / 10000 + "%"});
		$('#defender_position_bar_div p').html(temp.position_percentage + "%");
		$('#defender_position_info').html(numberFormat(temp.defender_strength, 2, '.', ' ') + 
		"/" + numberFormat(temp.defender_fixed_strength_output, 2, '.', ' '));
		
		//attacker/defender damage
		$('#attacker_damage').html(numberFormat(temp.attacker_damage, 2, '.', ' '));
		$('#defender_damage').html(numberFormat(temp.defender_damage, 2, '.', ' '));
	
		if(temp.killed) {//deleted
			$(soldier_list[p]).remove();
			soldier_list = [];
			p = 0;
			$('.soldier_info').each(function() {
				soldier_list[p] = this;
				p++;
			});
			p = 0;
			if(soldier_list.length > 0) {
				$(soldier_list[0]).fadeIn();
			}
			//notify
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			$('#reply_info').append('<p id="msg">Soldier was killed!</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		
		if(temp.is_wounded && !temp.killed) {//wounded
			//notify
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			$('#reply_info').append('<p id="msg">Soldier has been wounded for the ' + temp.wound + ' time!</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		
		/*if(temp.end_battle) {
			$('#battle_duration').attr('id', 'battle_ended');
		}*/
	}
	
	/* regenerate */
	$('#regenerate_btn').on('click', function() {
		recoverEnergy();
	});

	const recoverEnergy = (display_errors = true) => {
		var product_id = $(recovery_list[f]).attr('id');
		var person_id = $(soldier_list[p]).attr('id');
		var data = new FormData();
		data.append('product_id', product_id);
		data.append('person_id', person_id);
		data.append('action', 'recover_during_battle');
		var url = "../etc/regenerate";
		return serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				//update energy info person details
				$(soldier_list[p]).children().last().children('.person_progress').animate({width: temp.new_energy + '%'});
				$(soldier_list[p]).children().last().children('p').html(temp.new_energy + '/' + temp.max_energy);
				$(soldier_list[p]).attr('energy', temp.new_energy);

				$(recovery_list[f]).children('.inventory_amount').html(temp.left_food);

				return true;
			}
			else if(display_errors) {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
			}
			return false;
		})
		.catch(error => {
			console.log(error);
		});
    }
	
	//battle statistics
	$('#battle_stat').on('click', function () {
		var battle_id = $('#battle_id').html();
		var url = "../etc/active_battle_statistic";
		function showInfo(xhttp) {	
			var reply = xhttp.responseText;
			var temp = reply.split("|-|");
			if(temp[0] == "true") {
				var attacker_name = $('#attacker_name').text();
				var defender_name = $('#defender_name').text();
				$('#for_popups_pop').html('');
				$('#for_popups_pop').append('<div id="battle_statistic"></div>');
				$('#battle_statistic').append('<div id="stat_menu"></div>');
				$('#stat_menu').append('<p id="country_damage_stat">Countries</p>');
				$('#stat_menu').append('<p id="player_damage_stat">Players</p>');
				$('#stat_menu').append('<p id="soldier_loss_stat">Soldier loss</p>');
				$('#stat_menu').append('<p id="equipment_used_stat">Equipment use</p>');
				$('#battle_statistic').append('<div id="countries_side">' +
											  '<p id="cs_attacker">' + attacker_name + '</p>' +
											  '<p id="cs_defender">' + defender_name + '</p>' +
											  '</div>');
				
				/* countries damage */
				$('#battle_statistic').append('<div id="country_damage_stat_div"></div>');
				$('#country_damage_stat_div').append('<div id="attacker_country_damage_stat_div"></div>');
				$('#attacker_country_damage_stat_div').append('<div id="country_damage_stat_div_heads">' +
													 '<p>Country Name</p>' +
													 '<p>Damage</p>' +
													 '</div>');
				var countries_damage = temp[1].split("||");
				for(var x = 0; x < countries_damage.length - 1; x++) {
					var t = countries_damage[x].split(", ");
					$('#attacker_country_damage_stat_div').append('<div>' +
														 '<img src="../country_flags/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="country?country_id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p>' + t[3] + '</p>' +
														 '</div>');
				}
				
				$('#country_damage_stat_div').append('<div id="defender_country_damage_stat_div"></div>');
				$('#defender_country_damage_stat_div').append('<div id="country_damage_stat_div_heads">' +
													 '<p>Country Name</p>' +
													 '<p>Damage</p>' +
													 '</div>');
				var countries_damage = temp[2].split("||");
				for(var x = 0; x < countries_damage.length - 1; x++) {
					var t = countries_damage[x].split(", ");
					$('#defender_country_damage_stat_div').append('<div>' +
														 '<img src="../country_flags/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="country?country_id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p>' + t[3] + '</p>' +
														 '</div>');
				}
				$('#country_damage_stat').css('backgroundColor', 'rgb(52, 75, 97)');
				
				
				/* players damage */
				$('#battle_statistic').append('<div id="players_damage_stat_div"></div>');
				$('#players_damage_stat_div').append('<div id="attacker_players_damage_stat_div"></div>');
				$('#attacker_players_damage_stat_div').append('<div id="player_damage_div_heads">' +
													 '<p id="pddh_player">Player Name</p>' +
													 '<p id="pddh_damage">Damage</p>' +
													 '</div>');
				var players_damage = temp[3].split("||");
				for(var x = 0; x < players_damage.length - 1; x++) {
					var t = players_damage[x].split(", ");
					$('#attacker_players_damage_stat_div').append('<div>' +
														 '<img src="../user_images/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="user_profile?id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p>' + t[3] + '</p>' +
														 '</div>');
				}
				
				$('#players_damage_stat_div').append('<div id="defender_players_damage_stat_div"></div>');
				$('#defender_players_damage_stat_div').append('<div id="player_damage_div_heads">' +
													 '<p id="pddh_player">Player Name</p>' +
													 '<p id="pddh_damage">Damage</p>' +
													 '</div>');
				var players_damage = temp[4].split("||");
				for(var x = 0; x < players_damage.length - 1; x++) {
					var t = players_damage[x].split(", ");
					$('#defender_players_damage_stat_div').append('<div>' +
														 '<img src="../user_images/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="user_profile?id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p>' + t[3] + '</p>' +
														 '</div>');
				}
				$('#players_damage_stat_div').css('display', 'none');
				
				
				/* country soldier loss */
				$('#battle_statistic').append('<div id="country_soldier_loss_stat_div"></div>');
				$('#country_soldier_loss_stat_div').append('<div id="attacker_country_soldier_loss_stat_div"></div>');
				$('#attacker_country_soldier_loss_stat_div').append('<div id="country_loss_stat_div_heads">' +
													 '<p id="clsdv_country">Country Name</p>' +
													 '<p id="clsdv_loss">Soldiers</p>' +
													 '</div>');
				var countries_loss = temp[5].split("||");
				for(var x = 0; x < countries_loss.length - 1; x++) {
					var t = countries_loss[x].split(", ");
					$('#attacker_country_soldier_loss_stat_div').append('<div>' +
														 '<img src="../country_flags/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="country?country_id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p>' + t[3] + '</p>' +
														 '</div>');
				}
				
				$('#country_soldier_loss_stat_div').append('<div id="defender_country_soldier_loss_stat_div"></div>');
				$('#defender_country_soldier_loss_stat_div').append('<div id="country_loss_stat_div_heads">' +
													 '<p id="clsdv_country">Country Name</p>' +
													 '<p id="clsdv_loss">Soldiers</p>' +
													 '</div>');
				var countries_loss = temp[6].split("||");
				for(var x = 0; x < countries_loss.length - 1; x++) {
					var t = countries_loss[x].split(", ");
					$('#defender_country_soldier_loss_stat_div').append('<div>' +
														 '<img src="../country_flags/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="country?country_id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p>' + t[3] + '</p>' +
														 '</div>');
				}
				$('#country_soldier_loss_stat_div').css('display', 'none');
				
				
				/* equipment used */
				$('#battle_statistic').append('<div id="country_equipment_stat_div"></div>');
				$('#country_equipment_stat_div').append('<div id="attacker_country_equipment_stat_div"></div>');
				var countries_equipment = temp[7].split("||");
				for(var x = 0; x < countries_equipment.length - 1; x++) {
					var t = countries_equipment[x].split(", ");
					$('#attacker_country_equipment_stat_div').append('<div>' +
														 '<img src="../country_flags/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="country?country_id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p class="cesd_product_name">' + t[3] + '</p>' +
														 '<p class="cesd_amount">' + t[4] + '</p>' +
														 '</div>');
				}
				
				$('#country_equipment_stat_div').append('<div id="defender_country_equipment_stat_div"></div>');
				var countries_equipment = temp[8].split("||");
				for(var x = 0; x < countries_equipment.length - 1; x++) {
					var t = countries_equipment[x].split(", ");
					$('#defender_country_equipment_stat_div').append('<div>' +
														 '<img src="../country_flags/' + t[0] + '" alt="' + t[1] + '">' +
														 '<a href="country?country_id=' + t[2] + '" target="_blank">' + t[1] + '</a>' +
														 '<p class="cesd_product_name">' + t[3] + '</p>' +
														 '<p class="cesd_amount">' + t[4] + '</p>' +
														 '</div>');
				}
				$('#country_equipment_stat_div').css('display', 'none');
				
				$('#battle_statistic').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
			}
		}
		function sendData(xhttp) {
			xhttp.send("battle_id=" + battle_id);
		}
		loadDoc(url, showInfo, sendData);
	});
	
	$('#for_popups_pop').on('click', '#country_damage_stat', function() {
		$('#players_damage_stat_div').fadeOut(0);
		$('#player_damage_stat').css('backgroundColor', '');
		
		$('#country_soldier_loss_stat_div').fadeOut(0);
		$('#soldier_loss_stat').css('backgroundColor', '')
		
		$('#country_equipment_stat_div').fadeOut(0);
		$('#equipment_used_stat').css('backgroundColor', '');
		
		$('#country_damage_stat_div').fadeIn(200);
		$('#country_damage_stat').css('backgroundColor', 'rgb(52, 75, 97)');
	});
	
	$('#for_popups_pop').on('click', '#player_damage_stat', function() {
		$('#country_damage_stat_div').fadeOut(0);
		$('#country_damage_stat').css('backgroundColor', '');
		
		$('#country_soldier_loss_stat_div').fadeOut(0);
		$('#soldier_loss_stat').css('backgroundColor', '')
		
		$('#country_equipment_stat_div').fadeOut(0);
		$('#equipment_used_stat').css('backgroundColor', '');
		
		$('#players_damage_stat_div').fadeIn(200);
		$('#player_damage_stat').css('backgroundColor', 'rgb(52, 75, 97)');
	});
	
	$('#for_popups_pop').on('click', '#soldier_loss_stat', function() {
		$('#country_damage_stat_div').fadeOut(0);
		$('#country_damage_stat').css('backgroundColor', '');
		
		$('#players_damage_stat_div').fadeOut(0);
		$('#player_damage_stat').css('backgroundColor', '')
		
		$('#country_equipment_stat_div').fadeOut(0);
		$('#equipment_used_stat').css('backgroundColor', '');
		
		$('#country_soldier_loss_stat_div').fadeIn(200);
		$('#soldier_loss_stat').css('backgroundColor', 'rgb(52, 75, 97)');
	});
	
	$('#for_popups_pop').on('click', '#equipment_used_stat', function() {
		$('#country_damage_stat_div').fadeOut(0);
		$('#country_damage_stat').css('backgroundColor', '');
		
		$('#players_damage_stat_div').fadeOut(0);
		$('#player_damage_stat').css('backgroundColor', '')
		
		$('#country_soldier_loss_stat_div').fadeOut(0);
		$('#soldier_loss_stat').css('backgroundColor', '');
		
		$('#country_equipment_stat_div').fadeIn(200);
		$('#equipment_used_stat').css('backgroundColor', 'rgb(52, 75, 97)');
	});
	
	//close popup
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	// When the user clicks anywhere outside of the modal, close it
	$('html').on('click', function() {
		var modal = $("#for_popups_pop").get(0);
		if(event.target == modal) {
			$(modal).fadeOut(300);
		}
	});
});