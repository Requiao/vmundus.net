$(document).ready(function() {
	//set colors
	var theme = 'light';
	$('#container').css('backgroundColor', colors.container_background[theme]);
	$('#container').css('boxShadow', colors.container_shadow[theme]);
	$('#page_head').css('backgroundColor', colors.page_heading[theme]);
	$('.heads').css('backgroundColor', colors.sub_title_background[theme]);
	$('.product_icon').css('background', colors.icon_background[theme]);
	$('.cf_building_icon').css('background', colors.icon_background[theme]);

	$('.sub_title').css('backgroundColor', colors.sub_title_background[theme]);
	$('.action_all_heads').css('backgroundColor', colors.button_background_two[theme]);

	$('.recover_all_info, .cf_farms_div, .about_persons').css('boxShadow', colors.container_shadow[theme]);
	$('.details_btn').css('border', colors.button_border_one[theme]);
	$('.recover_all').css('backgroundColor', colors.button_background_one[theme]);
	$('.details_btn').css('backgroundColor', colors.button_background_two[theme]);
	$('.recovery, .cf_bonus').css('backgroundColor', colors.text_background_one[theme]);
	$('.amount, .cf_days_left, #bnsd_slot_price_div').css('backgroundColor', colors.text_background_two[theme]);


	/* buy slots for people */
	$('#buy_new_slot_div').on('click', function() {
		$("#for_popups_pop").empty();
		$("#for_popups_pop").fadeIn();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Do you want to buy extra slot for your people for ' +
								 $('#bnsd_slot_price').html() + ' gold?</p>');
		$('#reply_info').append('<p class="button blue" id="buy_slot_ok">Buy</p>');
		$('#reply_info').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
	});

	$('#for_popups_pop').on('click', '#buy_slot_ok', function() {
		var data = new FormData();
		data.append('action', 'buy_slot_for_people');
		var url = "../etc/manage_people";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			
			if(temp.success == true) {
				$("#for_popups_pop").fadeOut(250);

				$('#buy_new_slot_div').after('<div class="available_slot_div">' +
											 '<p class="asd_label">Available</p>' +
											 '</div>');

				if(temp.reached_max_slot) {
					$('#buy_new_slot_div').fadeOut(250);
				}
				else {
					$('#buy_new_slot_div #bnsd_slot_price').html(temp.new_slot_price);
				}
				setNewUserGold();
			}
			else {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$('#for_popups_pop').fadeIn();
			}
		}
		submitData(data, url, dataReply);
	});


	/* change person_details height window on zoom */
	$(window).resize(function() {
		adjustPersonDetailsHeight();
	});
	function adjustPersonDetailsHeight() {
		var height = $(window).height();
		if(height < 760) {
			$('#person_details').css('height', '580px');
		}
		else if(height >= 760) {
			$('#person_details').css('height', 'auto');
		}
	}
	
	/* buy clones */
	$('#buy_clones').on('click', function() {
		var data = new FormData();
		data.append('action', 'get_clone_purchase_details');
		var url = "../etc/manage_people";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			
			$('#for_popups_pop').empty();
			if(temp.success == true) {
				$('#for_popups_pop').append('<div id="reply_info"></div>'); 
				//$('#reply_info').append('<p id="clones_sale">30% Off</p>'); 
				$('#reply_info').append('<div id="clone_details">' +
						'<input id="cd_person_name" type="text" value="No Name" maxlength="10">' +
						'<span class="glyphicon glyphicon-user person_icon"></span>' +
						'<abbr title="' + lang.years + '"><p class="person_years">18</p></abbr>' +
						'<abbr title="Did not worked today"><p class="person_worked_stat person_can_work">' +
						'<i class="fa fa-briefcase" aria-hidden="true"></i></p></abbr>' +
						
						'<p id="cd_work_experience">' + lang.work_experience + '</p>' +
						'<input id="cd_person_experience" type="text" value="1" placeholder="1...20">' +
						
						'<p id="cd_combat_experience">' + lang.combat_experience + '</p>' +
						'<input id="cd_person_combat_exp" type="text" value="0" placeholder="0...500">' +
						
						'<div class="bar">' +
						'<div class="progress energy_progress_bar" style="width:100%;"></div>' +
						'<p>100/100</p>' +
						'</div>' +
						'<p id="buy_clone">Buy</p>' +
						'</div>' +
						'</div>');
				
				recalculatePurchaseCost.clone_cost = parseFloat(temp.person_price); 
				recalculatePurchaseCost.work_exp_cost = parseFloat(temp.work_exp_price); 
				recalculatePurchaseCost.combat_exp_cost = parseFloat(temp.combat_exp_price);
				recalculatePurchaseCost.work_exp = $('#cd_person_experience').val();;
				
				$('#reply_info').append('<div id="cd_reply_details"></div>');
				
				$('#reply_info').append('<div id="cd_prices">' +
						'<div id="clone_price">Clone price: ' +
						'<p class="cd_price">' + temp.person_price + 
						' <img class="cd_gold_img" src="../img/gold.png"></p></div>' +
						
						'<div id="work_experience_price">Work exp price: ' +
						'<p class="cd_price">' + temp.work_exp_price + 
						' <img class="cd_gold_img" src="../img/gold.png"></p></div>' + 
						
						'<div id="combat_experience_price">Combat exp price: ' +
						'<p class="cd_price">' + temp.combat_exp_price + 
						' <img class="cd_gold_img" src="../img/gold.png"></p></div>' + 

						'<div id="total_clone_price">Total: <p class="cd_price">' + recalculatePurchaseCost.get_new_price() +
						' <img class="cd_gold_img" src="../img/gold.png"></p></div>' + 
						'</div>');

				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			else {
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$('#for_popups_pop').fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#buy_clone', function() {
		var person_name = $('#cd_person_name').val()
		var work_exp_lvl = $('#cd_person_experience').val()
		var combat_exp = $('#cd_person_combat_exp').val()
		var data = new FormData();
		data.append('person_name', person_name);
		data.append('work_exp_lvl', work_exp_lvl);
		data.append('combat_exp', combat_exp);
		data.append('action', 'purchase_clone');
		var url = "../etc/manage_people";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			
			$('#cd_reply_details').empty();
			if(temp.success == true) {
				$('#cd_reply_details').append('<p style="color:rgb(97, 133, 118)">' + temp.msg + '</p>');
				
				$('.available_slot_div').last().replaceWith(
					'<div class="about_persons" id="person_' + temp.person_id + '">' +
					'<p class="get_person_id" hidden>' + temp.person_id + '</p>' +
					'<input class="people_id_check" type="checkbox" value="' + temp.person_id + '" checked>' +
					'<p class="person_name">' + temp.person_name + '</p>' +
					'<span class="glyphicon glyphicon-user person_icon"></span>' +
					'<abbr title="' + lang.years + '"><p class="person_years">' + temp.years + '</p></abbr>' +
					'<abbr title="Did not work today"><p class="person_worked_stat person_can_work' +
					'"><i class="fa fa-briefcase" aria-hidden="true"></i></p></abbr>' +
					'<div class="bar">' +
					'<div class="work_lvl_progress_bar" style="width:' + temp.work_lvl_bar_width + '%;"></div>' +
					'<p>Work level: ' + temp.work_exp_lvl + '</p> ' + 
					'</div>' +
					'<abbr title="' + lang.combat_experience + '"><p class="person_combat_exp">Combat exp: ' + 
					temp.combat_exp + '</p></abbr>' +
					'<div class="bar">' +
					'<div class="energy_progress_bar" style="width:' + temp.energy_width + '%;"></div>' +
					'<p>Energy: ' + temp.energy + '/' + temp.max_energy + '</p>' + 
					'</div>' +
					'<p class="person_status ' + temp.status + '">' + temp.status + '</p>' +
					'<p class="details_btn">Details</p>' +
					'</div>'
				);
				$('.details_btn').css('backgroundColor', colors.button_background_two[theme]);
				$('.details_btn').css('border', colors.button_border_one[theme]);

				//recalculate total people
				let total_people = parseInt($('#tpd_total_people').text());
				$('#tpd_total_people').text(++total_people);

				setNewUserGold();
			}
			else {
				$('#cd_reply_details').append('<p style="color:rgb(246,102,92)">' + temp.error + '</p>');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* recalculate total purchase cost */
	/*function RecalculatePurchaseCost (clone_cost, work_exp_cost, combat_exp_cost) {
		this.clone_cost =  clone_cost;
		this.work_exp_cost = work_exp_cost;
		this.combat_exp_cost = combat_exp_cost;
		this.total = 0;
		this.work_exp = 1;
		this.combat_exp = 1;
		this.calculate = function () {
			this.total = this.clone_cost + 
					    (this.work_exp_cost * this.work_exp) +
					    (this.combat_exp_cost * this.combat_exp);
		};
		this.get_new_price = function () {
			this.calculate();
			return numberFormat(this.total, 3, '.', ' ');
		};
	}*/
	var recalculatePurchaseCost = {
		clone_cost:  0,
		work_exp_cost: 0,
		combat_exp_cost: 0,
		total: 0,
		work_exp: 0,
		combat_exp: 0,
		
		calculate: function () {
			this.total = this.clone_cost + 
					    (this.work_exp_cost * (this.work_exp - 1)) +
					    (this.combat_exp_cost * this.combat_exp);
		},
		
		get_new_price: function () {
			this.calculate();
			return numberFormat(this.total, 3, '.', ' ');
		}
	}

	$('#for_popups_pop').on('change keyup', '#cd_person_experience',  function(e) {
			var exp = $(this).val();

			if(e.key == 'Backspace') return;

			if(exp < 1 || isNaN(exp) || exp == '') {
				exp = 1;
				$(this).val(exp);
			}
			if(exp > 20) {
				exp = 20;
				$(this).val(exp);
			}
		
			recalculatePurchaseCost.work_exp = exp;
			$('#total_clone_price .cd_price').html(recalculatePurchaseCost.get_new_price() + ' <img class="cd_gold_img" src="../img/gold.png">');
	});
	
	$('#for_popups_pop').on('change keyup', '#cd_person_combat_exp',  function(e) {
			var exp = $(this).val();

			if(e.key == 'Backspace') return;

			if(exp < 0 || isNaN(exp) || exp == '') {
				exp = 0;
				$(this).val(exp);
			}

			if(exp > 500) {
				exp = 500;
				$(this).val(exp);
			}
		
			recalculatePurchaseCost.combat_exp = exp;
			$('#total_clone_price .cd_price').html(recalculatePurchaseCost.get_new_price() + ' <img class="cd_gold_img" src="../img/gold.png">');
	});
	
	/* build clone farm */
	$('#build_clone_farm').on('click', function() {
		var data = new FormData();
		data.append('action', 'get_cloning_systems');
		var url = "../etc/manage_people";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			
			$('#for_popups_pop').empty();
			if(temp.success == true) {
				$('#for_popups_pop').append('<div id="buildings_info_div"></div>'); 
				$('#buildings_info_div').append('<div id="form_headings">' +
												'<span id="create_form_close" class="glyphicon glyphicon-remove-circle"></span>' +
												'<p id="n_heading">' + lang.building_name + '</p>' +
												'<p id="bb_heading">Bonus to Born Bar</p>' +
												'<p id="bbd_heading">Days</p>' +
												'</div>'); 
				
				//generate class of avilable products
				var user_product = [];
				for(x = 0; x < temp.user_product.length; x++) {
					user_product[temp.user_product[x].product_id] =  temp.user_product[x].amount;
				}
				
				var req_product = '';
				var total_req = 0;
				var user_prod = 0;
				var avail_str = '';
				for (i = 0; i < temp.farms.length; i++) {
					req_product = '';
					for (x = 0; x < temp.farms[i].resources.length; x++) {
						total_req = parseFloat(temp.farms[i].resources[x].amount);
						user_prod = parseFloat(user_product[temp.farms[i].resources[x].product_id]);
						if(total_req <= user_prod) {
							avail_str = '<p class="available">' + total_req + "/" + numberFormat(user_prod, 2, '.', ' ') + '</p>';
						}
						else {
							avail_str = '<p class="available not_enough_prod">' + total_req + "/" + numberFormat(user_prod, 2, '.', ' ') + '</p>';
						}
						req_product += '<div class="icon_amount">' +
									   '<abbr title="' + temp.farms[i].resources[x].req_product_name + '">' +
									   '<img class="product_icon" src="../product_icons/' + 
									   temp.farms[i].resources[x].product_icon + '" alt="product icon">' +
									   '</abbr>' +
									   '<p class="amount">' + temp.farms[i].resources[x].amount + '</p>' +
									   avail_str +
									   '</div>';
					}
					
					$('#buildings_info_div').append('<div class="building_info">' + 
									  '<img class="building_icon" src="../building_icons/' + temp.farms[i].building_icon + 
									  '" alt="building image">' +
									  '<p class="building_name">' + temp.farms[i].building_name + '</p>' +
									  '<p class="bb_bonus">+' + temp.farms[i].bb_growth + 'BB</p>' +
									  '<p class="bb_days">' + temp.days + '</p>' +
									  '<p class="button build_cloning_farm">' + lang.build + '</p>' +
									  '<p hidden>' + temp.farms[i].building_id + '</p>' +
									  '<div class="product_div">' +
									  req_product +
									  '</div>' +
									  '</div>');
				}
			}
			else {
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$('#for_popups_pop').fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '.build_cloning_farm', function() {
		var id = $(this).next().html();
		var data = new FormData();
		data.append('id', id);
		data.append('action', 'build_cloning_system');
		var url = "../etc/manage_people";
		function housesReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$('#cloning_farms').append('<div class="cf_farms_div">' +
										   '<img class="cf_building_icon" src="../building_icons/' + temp.building_icon + 
										   '" alt="' + temp.building_name + '">' +
										   '<p class="cf_bonus">+' + temp.growth + ' BB<p>' +
										   '<p class="cf_days_left">' + temp.days + ' days left</p>' +
										   '</div>');

				$('.cf_building_icon').css('background', colors.icon_background[theme]);		
				$('.cf_farms_div').css('boxShadow', colors.container_shadow[theme]);
				$('.cf_bonus').css('backgroundColor', colors.text_background_one[theme]);
				$('.cf_days_left').css('backgroundColor', colors.text_background_two[theme]);				   
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, housesReply);
	});
	
	//close form
	$('#for_popups_pop').on('click', '#create_form_close', function() {
		$('#for_popups_pop').fadeOut();
	});
	
	/* regenerate energy for all people */
	$('#rec_head').on('click', function () {
		$('#recover_energy_div').slideToggle();
		$('.people_id_check').slideToggle();
	});
	
	$('#uncheck_box').on('change', function () {
		if ($(this).prop('checked') == true) {
            $('.people_id_check').prop('checked', true);
        }
		else {
			$('.people_id_check').prop('checked', false);
		}
	});
	
	$('.people_id_check').on('change', function () {
		$('#uncheck_box').prop('checked', true);
		$('.people_id_check').each(function() {
			if ($(this).prop('checked') == false) {
				$('#uncheck_box').prop('checked', false);
				return;
			}
		});
	});
	
	$('.recover_all').on('click', function() {
		var person_id = [];
		$('.people_id_check:checkbox:checked').each(function(e) {
			person_id[e] = ($(this).val());
		});
		var product_id = $(this).next().html();
		var quantity = $(this).prev().val();
		var e = this;
		var data = new FormData();
		data.append('person_id', person_id);
		data.append('product_id', product_id);
		data.append('quantity', quantity);
		data.append('action', 'recover_for_all');
		var url = "../etc/regenerate";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#rec_reply').empty();
			if(temp.success == true) {
				$('#rec_reply').html(temp.msg);
				$('#rec_reply').css('color', 'green');
				
				for(var x = 0; x < temp.person_rec.length; x++) {
					$('#person_' + temp.person_rec[x].person_id + ' .bar .energy_progress_bar').animate({width: temp.person_rec[x].new_energy + "%"});
					$('#person_' + temp.person_rec[x].person_id + ' .bar .energy_progress_bar').next().html("Energy: " + temp.person_rec[x].new_energy + '/' + temp.max_energy);
				}
				
				$(e).parent().children('.amount').html(temp.left_food);
			}
			else {
				$('#rec_reply').html(temp.error);
				$('#rec_reply').css('color', 'rgb(207, 28, 28)');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* person details */
	$('#container').on('click', '.details_btn', function() {
		var person_id = $(this).parent().children('.get_person_id').html();
		var data = new FormData();
		data.append('person_id', person_id);
		data.append('action', 'person_details');
		var url = "../etc/manage_people";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			if(temp.success == false) {
				$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop2").fadeIn(300);
				return;
			}
			
			$('#for_popups_pop').append('<div id="person_details"></div>');
			$('#person_details').append('<p id="person_id" hidden>' + temp.person.person_id + '</p>');
			$('#person_details').append('<input id="person_name" value="' + temp.person.person_name + '" maxlength="15">');
			$('#person_details').append('<p id="rename" class="button blue">' + lang.rename + '</p>');
			$('#person_details').append('<p id="years">' + temp.person.years + ' ' + lang.years + '</p>');
			$('#person_details').append('<p id="exp">' + lang.skill + ': ' + temp.person.skill + '</p>');
			$('#person_details').append('<p id="energy">Energy: ' + temp.person.energy + '/' + temp.person.max_energy + '</p>');
			$('#person_details').append('<p id="regenerate" class="button green">' + lang.regenerate + '</p>');
			var energy_percent = Math.round((temp.person.energy/temp.person.max_energy) * 100);
			var pct = getStrokeDashofset(energy_percent);
			$('#person_details').append('<div id="cont" data-pct="' + energy_percent + '">' +
										'<svg id="svg" width="210" height="210" viewPort="0 0 100 100">' +
										'<circle r="90" cx="105" cy="105" fill="transparent" stroke-dasharray="565.48"' +
										'stroke-dashoffset="0"></circle>' +
										'<circle id="bar" r="90" cx="-105" cy="105" fill="transparent" stroke-dasharray="565.48"' +
										'stroke-dashoffset="' + pct +'" transform="rotate(270)"></circle>' +
										'</svg></div>');
			var bar_width = (100 / parseInt(temp.person.required_exp)) * parseInt(temp.person.experience);
			$('#person_details').append('<p id="skill_info">' + lang.work_experience + ':</p>');
			$('#person_details').append('<div id="skill_bar">' +
											'<div class="progress work_lvl_progress_bar" style="width:' + bar_width +'%;"></div>' +
												'<p>' + temp.person.experience + '/' + temp.person.required_exp + '</p>' +
										'</div>');
			$('#person_details').append('<p id="combat_skill_head">' + lang.combat_experience + ':</p>');
			$('#person_details').append('<p id="combat_skill_info">' + temp.person.combat_exp + '</p>');
			$('#person_details').append('<p id="wound_head">' + lang.wounds + ':</p>');
			$('#person_details').append('<p id="wound_info">' + temp.person.wound + '</p>');
			$('#person_details').append('<span id="close" class="glyphicon glyphicon-remove-circle"></span>');
	
			if(temp.job_info) {
				$('#person_details').append('<div id="job">' +
											'<a href="user_profile?id=' + temp.job_info.employer_id + 
											'" id="employer_name" target="_blank">' + temp.job_info.employer_name + '</a>' +
											'<img src="../user_images/' + temp.job_info.employer_image + '" id="employer_img">' +
											'<p id="company_name">' + temp.job_info.company_name + '</p>' +
											'<img src="../building_icons/' + temp.job_info.building_icon + '" id="company_img">' +
											'<p id="company_region_name">' + lang.location + ': ' + temp.job_info.comp_region_name + '</p>' +
											'<p id="salary">' + temp.job_info.salary + ' ' + temp.job_info.currency_abbr + '</p>' +
											'<p id="days_hired">' + lang.days_hired + ' ' + temp.job_info.time_hired + ':</p>' +
											'<p id="retire" class="button red">' + lang.retire + '</p>' +
											'</div>');
			}
			else {
				$('#person_details').append('<p id="get_job" class="button blue"><a href="job_offers" target="_blank">' + lang.get_job + 
											'</a></p>');
			}
		
			$("#for_popups_pop").fadeIn(300);
			adjustPersonDetailsHeight();
		}
		submitData(data, url, dataReply);
	});

	function getStrokeDashofset(val) {
		var val = parseInt(val);
		var r = 90;
		var c = Math.PI*(r*2);
		var pct = ((100-val)/100)*c;
		return pct;
	}
	
	/* rename person */
	$('#for_popups_pop').on('click', '#rename', function() {
		var person_id = $('#person_id').html();
		var person_name = $('#person_name').val();
		var e = this;
		var data = new FormData();
		data.append('person_id', person_id);
		data.append('person_name', person_name);
		data.append('action', "rename_person");
		var url = "../etc/manage_people";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$(e).prev().val(temp.person_name);
				$('#person_' + person_id).children('.person_name').html(temp.person_name);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});

	/* regenerate */
	$('#for_popups_pop').on('click', '#regenerate', function() {
		var person_id = $('#person_id').html();
		var data = new FormData();
		data.append('person_id', person_id);
		data.append('action', 'get_regenerate_info');
		var url = "../etc/regenerate";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			
			if(temp.success == true) {
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				//show recovery options
				for(var x = 0; x < temp.recovery.length; x++) {
					$('#reply_info').append('<div class="recovery_info" id="">' +
											'<abbr title="' + temp.recovery[x].product_name + 
											'"><img class="product_icon" src="../product_icons/' + 
											temp.recovery[x].product_icon + '" alt="product icon"></abbr>' +
											'<p class="recovery">+' + temp.recovery[x].recovers + 
											' <i class="glyphicon glyphicon-flash"></i></p>' +
											'<p class="amount">' + temp.recovery[x].amount + '</p>' +
											'<input type="number" class="quantity" min="1" max="10" value="1">' +
											'<p class="recover" id="' + temp.recovery[x].product_id + '">' + lang.regenerate + '</p>' +
											'<p hidden>' + temp.recovery[x].product_id + '</p>' +
											'<p hidden>' + temp.recovery[x].person_id + '</p>' +
											'</div>');
				}
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.cancel + '</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});	
	
	$('#for_popups_pop2').on('click', '.recover', function() {
		var product_id = $(this).next().html();
		var person_id = $(this).next().next().html();
		var quantity = $(this).prev().val();
		var data = new FormData();
		data.append('product_id', product_id);
		data.append('person_id', person_id);
		data.append('quantity', quantity);
		data.append('action', 'recover_energy');
		var url = "../etc/regenerate";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				//update energy info person details
				$('#cont').attr('data-pct', temp.new_energy);
				var new_stroke = getStrokeDashofset(temp.new_energy);
				$('#bar').attr('stroke-dashoffset', new_stroke);
				$('#energy').html('Energy ' + temp.new_energy + '/' + temp.max_energy);
				
				//update energy info main page
				$('#person_' + temp.person_id + ' .bar .energy_progress_bar').animate({width: temp.new_energy + "%"});
				$('#person_' + temp.person_id + ' .bar .energy_progress_bar').next().html("Energy: " + temp.new_energy + '/' + temp.max_energy);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* retire from job */
	$('#for_popups_pop').on('click', '#retire', function() {
		$('#for_popups_pop2').empty();
		$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to retire from this job?</p>');
		$('#reply_info').append('<p class="button red" id="retire_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel2">No</p>');
		
		$("#for_popups_pop2").fadeIn(300);
	});
	
	$('#for_popups_pop2').on('click', '#retire_yes', function() {
		var person_id = $('#person_id').html();
		var data = new FormData();
		data.append('person_id', person_id);
		var url = "../etc/retire_from_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				if(temp.summary[0].success == true) {
					$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
					$('#person_' + temp.summary[0].person_id).children('.person_status').html(temp.status);
					if(temp.status == 'available') {
						$('#person_' + temp.summary[0].person_id).children('.person_status').removeClass('working');
						$('#person_' + temp.summary[0].person_id).children('.person_status').addClass('available');
					}
					
					$('#job').slideUp();
					$('#retire').fadeOut(0);
					$('#person_details').append('<p id="get_job" class="button blue"><a href="job_offers" target="_blank">Get Job</a></p>');
					$('#reply_info').append('<p id="msg">' + temp.summary[0].msg + '</p>');
				}
				else {
					$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
					$('#reply_info').append('<p class="wsd_msg">' + temp.summary[0].error + '</p>');
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.ok + '</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#travel_div').on('click', '#cancel', function() {
		$("#travel_div").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#cancel2', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	$('#for_popups_pop').on('click', '#close', function() { //close details
		 $("#for_popups_pop").fadeOut(300);
	});
	$('#for_popups_pop').on('click', '#form_close', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
});