$(document).ready( function() {
	
	/* core region */
	$('#make_core').on('click', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'get_make_core_info');
		var url = "../etc/manage_region";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p class="button green" id="make_core_ok">Yes</p>');
				$('#reply_info').append('<p class="button red" id="cancel">Cancel</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn();
		});
	});

	$('#for_popups_pop').on('click', '#make_core_ok', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'make_core');
		var url = "../etc/manage_region";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');

				$('#make_core').parent().append(
					'<p id="make_core_label">Core creation in progress.</p>' +
					'<div id="core_creation_bar_div">' +
						'<div id="core_creation_progress" style="width: 0%;"></div>' +
						'<p>' + temp.hours_left + ' hours left</p>' +
					'</div>'
				);

				$('#make_core').prev().remove();
				$('#make_core').remove();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		});
	});

	/* repair defence system */
	$('.repair').on('click', function() {
		var region_id = $('#region_id').html();
		var product_id = $(this).next().html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('product_id', product_id);
		data.append('action', 'repair_info');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="repair_system_div"></div>');
			if(temp.success === true) {
				$('#repair_system_div').append('<p id="rsd_available_product">Country product: ' + temp.product_name + 
											   ' - ' + temp.amount + '</p>');
				$('#repair_system_div').append('<input id="rsd_use_prod_input" maxlength="5">');
				$('#repair_system_div').append('<p class="button blue" id="repair_apply">Repair</p>');
				$('#repair_system_div').append('<p hidden>' + temp.product_id + '</p>');
				$('#repair_system_div').append('<p class="button red" id="cancel">Cancel</p>');
			}
			else {
				$('#repair_system_div').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#repair_system_div').append('<p id="msg">' + temp.error + '</p>');
				$('#repair_system_div').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	//repair
	$('#for_popups_pop').on('click', '#repair_apply', function() {
		var region_id = $('#region_id').html();
		var product_id = $(this).next().html();
		var amount = $('#rsd_use_prod_input').val();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('product_id', product_id);
		data.append('amount', amount);
		data.append('action', 'repair');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').html('');
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$("#for_popups_pop").fadeOut(300);
				
				$('#position_progress').animate({width: + temp.strength_percentage + "%"}, 1000);
				$('#position_bar_div p').html(temp.strength_percentage + "%");
				$('#position_info').html(temp.region_strength + "/" + temp.base_strength);
				
				if(temp.repaired_all_product == true) {
					$('#rds_' + product_id).remove();
				}
				else {
					$('#rds_' + product_id).children('.amount').html(temp.new_required_amount);
				}
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* upgrade defence system */
	updateUpgradeClock();
	function updateUpgradeClock() {
		$('#upgrade_end_in').each(function() {
			countdownClock(this);
		});
		var t = setTimeout(updateUpgradeClock, 950);
	};
	
	$('#upgrade_def_sys').on('click', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'upgrade_info');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="upgrade_system_div"></div>');
			if(temp.success === true) {
				$('#upgrade_system_div').append('<div id="usd_def_info">' +
												'<img id="usd_def_img" src="../infrastructure/' + temp.def_info.const_img + 
												'" alt="defence system">' +
												'<p id="usd_strength"><i class="fa fa-shield" aria-hidden="true"></i> ' + 
												 temp.def_info.strength + '</p>' +
												'<p id="usd_time"><i class="fa fa-clock-o" aria-hidden="true"></i> ' + 
												 temp.def_info.time_min + ' minutes</p>' +
												'</div>');	
				var i = 0;
				while(typeof temp[i] !== "undefined") {
					$('#upgrade_system_div').append('<div class="icon_amount"><abbr title="' + temp[i].product_name + '">' +
													'<img class="product_icon" src="../product_icons/' + temp[i].icon + '" alt="' + 
													 temp[i].product_name + '"></abbr>' +
													'<p class="amount">' + temp[i].amount + '</p></div>');
					i++;												
				}
				$('#upgrade_system_div').append('<p class="button blue" id="upgrade_ok">Upgrade</p>');
				$('#upgrade_system_div').append('<p class="button red" id="cancel">Cancel</p>');
			}
			else {
				$('#upgrade_system_div').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#upgrade_system_div').append('<p id="msg">' + temp.error + '</p>');
				$('#upgrade_system_div').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#upgrade_ok', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'upgrade');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').html('');
			$('#for_popups_pop2').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$("#for_popups_pop").fadeOut(300);
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				
				$('#region_defence_system_info').after('<div id="region_upgrade_system_div">' +
													   '<p id="rusd_head">Upgrading</p>' +
													   '<img id="defence_system_img" src="../infrastructure/' + temp.image + 
													   '" alt="defence system">' +
													   '<p id="upgrade_end_in">' + temp.end_in + '</p>' +
													   '<p id="upgrading_def_strength"><i class="fa fa-shield" aria-hidden="true"></i> ' + 
													   temp.strength + '</p>' +
													   '</div>').css('display', 'none').slideDown();
				$('#upgrade_def_sys').slideUp();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* upgrade road */
	$('#upgrade_road').on('click', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'upgrade_road_info');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			
			if(temp.success === true) {
				$('#for_popups_pop').append('<div id="upgrade_road_info_div"></div>');
				
				$('#upgrade_road_info_div').append('<p id="msg">The following products will be used for the upgrade:</p>');
				
				$('#upgrade_road_info_div').append('<div id="urid_road_info">' +
												'<img id="urid_road_img" src="../infrastructure/' + temp.road_info.road_img + 
												'" alt="Road">' +
												'<p id="urid_road_level">Level: ' + temp.road_info.road_level + '</p>' +
												'<p id="urid_durability">Durability: ' + temp.road_info.durability + '</p>' +
												'<p id="urid_bonus">Bonus: ' + temp.road_info.productivity_bonus + '%</p>' +
												'</div>');	
				for(i = 0; i < temp.req_products.length; i++) {
					$('#upgrade_road_info_div').append('<div class="icon_amount"><abbr title="' + 
													temp.req_products[i].product_name + '">' +
													'<img class="product_icon" src="../product_icons/' + 
													temp.req_products[i].icon + '" alt="' + 
													temp.req_products[i].product_name + '"></abbr>' +
													'<p class="amount">' + temp.req_products[i].amount + '</p></div>');											
				}
				$('#upgrade_road_info_div').append('<p class="button blue" id="upgrade_road_ok">Upgrade</p>');
				$('#upgrade_road_info_div').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
			}
			else {
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#upgrade_road_ok', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'upgrade_road');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				
				$('.cr_region_road img').attr('src', '../infrastructure/' + temp.image);
				$('.crrr_level').html('Level: ' + temp.road_level);
				$('.crrr_durability_div p').html('Uses left: ' + temp.durability + '/' + temp.durability);
				$('.crrr_bonus').html('Productivity bonus: ' + temp.bonus);
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
	
	
	//repair road
	$('#repair_road').on('click', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'repair_road_info');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			
			if(temp.success === true) {
				$('#for_popups_pop').append('<div id="upgrade_road_info_div"></div>');
				
				$('#upgrade_road_info_div').append('<p id="msg">After repair, road durability will increase by ' + 
													temp.repair_durability + '. The following products will be used:</p>');
				for(i = 0; i < temp.req_products.length; i++) {
					$('#upgrade_road_info_div').append('<div class="icon_amount"><abbr title="' + 
													temp.req_products[i].product_name + '">' +
													'<img class="product_icon" src="../product_icons/' + 
													temp.req_products[i].icon + '" alt="' + 
													temp.req_products[i].product_name + '"></abbr>' +
													'<p class="amount">' + temp.req_products[i].amount + '</p></div>');											
				}
				$('#upgrade_road_info_div').append('<p class="button blue" id="repair_road_ok">Repair</p>');
				$('#upgrade_road_info_div').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
			}
			else {
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#repair_road_ok', function() {
		var region_id = $('#region_id').html();
		var data = new FormData();
		data.append('region_id', region_id);
		data.append('action', 'repair_road');
		var url = "../etc/manage_region";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$("#for_popups_pop").fadeIn();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				var bar_width = (100 / temp.total_durability) * temp.durability;
				$('.crrr_durability_div div').css('width', bar_width + '%');
				
				$('.crrr_durability_div p').html('Uses left: ' + numberFormat(temp.durability, 0, '', ' ') + 
												 '/' + numberFormat(temp.total_durability, 0, '', ' '));
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
		}
		submitData(data, url, dataReply);
	});
	
	/* support RW war */
	let rw_modal;
	let rw_country_id = $(this).attr('country_id');
	let rw_region_id = $(this).attr('region_id');
	$('.support_resistance').on('click', function() {
		rw_country_id = $(this).attr('country_id');
		rw_region_id = $(this).attr('region_id');
		var data = new FormData();
		data.append('country_id', rw_country_id);
		data.append('region_id', rw_region_id);
		data.append('action', 'support_war');
		var url = "../etc/region_war_organize";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			
			rw_modal = new ModalBox('600px');
			if(temp.success === true) {
				rw_modal.setHeading('Start Resistance');
				rw_modal.appendToModal('<p id="msg">' + temp.msg + '</p>');
				rw_modal.appendToModal('<p id="msg_reply"></p>');
				
				for(var x = 0; x < temp.product.length; x++) {
					rw_modal.appendToModal('<div class="icon_amount" id="pi_' + temp.product[x].product_id + '">' +
										    '<abbr title="' + temp.product[x].product_name + 
										    '"><img class="product_icon" src="../product_icons/' + temp.product[x].product_icon +
										    '" alt="' + temp.product[x].product_name + '"></abbr>' +
										    '<p class="amount">' + numberFormat(temp.product[x].amount, 2, '.', ' ') + '</p>' +
											'<input class="support_rec_input" type="text" placeholder="0.00">' +
											'<p class="support_rw">Support</p>' +
											'<p hidden="">' + temp.product[x].product_id + '</p>' +
											'</div>');
				}
				
				rw_modal.appendCancelButton('Ok');
				rw_modal.displayModal();
			}
			else {
				rw_modal.setErrorModal(temp.error);
			}
		}
		submitData(data, url, dataReply);
	});

	$('body').on('click', '.support_rw', function() {
		var quantity = $(this).prev().val();
		var product_id = $(this).next().html();
		var data = new FormData();
		data.append('quantity', quantity);
		data.append('product_id', product_id);
		data.append('country_id', rw_country_id);
		data.append('region_id', rw_region_id);
		data.append('action', 'support');
		var url = "../etc/region_war_organize";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			rw_modal.setErrorMsg('');
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#msg_reply').html(temp.msg);
				$('#msg_reply').css('color', 'green');
				$('#msg_reply').css('display', 'block');
				
				$(this).parent().children('.amount').html(numberFormat(temp.amount, 2, '.', ' '));
			
				$('#w_' + rw_country_id + ' .war_bar .progress').animate({width: temp.collected_perc + "%"});
				$('#w_' + rw_country_id + ' .war_bar .bar_mark').html(numberFormat(temp.collected_prods, 2, '.', ' ') + 
															  '(' + temp.collected_perc + '%)');
				if(temp.battle_started) {
					$('#region_population_div').find('.war_prep_info_div').slideUp();
					$('#region_upgrade_system_div').slideUp();
				}
			}
			else {
				rw_modal.setErrorMsg(temp.error);
			}
		});
	});

	//revolt
	let revolt_modal;
	$('#revolt').on('click', function() {
		let country_id = $('#revolt_countries_div selected item').attr('country_id');
		revolt_modal = new ModalBox('600px');
		if(country_id) {
			revolt_modal.setHeading('Start revolt');
			revolt_modal.appendToModal('<p class="modal_notification">Are you sure you want to start a revolt?</p>');
			revolt_modal.appendSubmitButton('Yes');
			revolt_modal.appendCancelButton('Cancel');
			revolt_modal.setSubmitButtonAction(start_revolt, [country_id]);
		}
		else {
			revolt_modal.setErrorModal("Choose country.");
		}
		revolt_modal.displayModal();
	});

	let start_revolt = function(country_id) {
		let region_id = $('#region_id').text();
		let data = new FormData();
		data.append('country_id', country_id);
		data.append('region_id', region_id);
		data.append('action', 'start_revolt');
		let url = "../etc/region_war_organize";
		revolt_modal.removeModal();
		serverRequest(data, url).then(reply => {
			console.log(reply);
			let temp = JSON.parse(reply);

			let modal = new ModalBox('600px');
			if(temp.success === true) {
				modal.setSuccessModal(temp.msg);
			}
			else {
				modal.setErrorModal(temp.error);
			}
			modal.displayModal();
		});
	};

	/* request new country */
	let create_country_modal;
	$('#rnc_label').on('click', function() {
		var data = new FormData();
		data.append('action', 'create_country_info');
		var url = "../etc/country";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			create_country_modal = new ModalBox('700px', '550px');

			create_country_modal.setLoadingSubmitBtn(false);

			if(temp.success == true && temp.requesting == false) {
				create_country_modal.setHeading('Request new country');
				create_country_modal.appendToModal(
					'<div id="create_country_div">' +

					'<p id="ccd_country_name_lbl">Country name:</p>' +
					'<input id="ccd_country_name" type="text" maxlength="20">' +

					'<p id="ccd_country_abbr_lbl">Country abbreviation:</p>' +
					'<input id="ccd_country_abbr" type="text" maxlength="3">' +

					'<img id="rc_country_flag" alt="country flag"' +
					' src="" style="display: none;">' +
					'<p id="ccd_country_flag_lbl">Country flag (2x1 ratio):</p>' +
					'<input type="file" id="ccd_country_flag" accept="image/png"></input>' +

					'<p id="ccd_currency_name_lbl">Currency name:</p>' +
					'<input id="ccd_currency_name" type="text" maxlength="15">' +

					'<p id="ccd_currency_abbr_lbl">Currency abbreviation:</p>' +
					'<input id="ccd_currency_abbr" type="text" maxlength="3">' +

					'<p id="ccd_cost_lbl">Fee: ' + temp.fee + '</p>' +
					'</div>'
				);
				create_country_modal.appendCancelButton('Cancel');
				create_country_modal.appendSubmitButton('Submit');
				create_country_modal.setSubmitButtonAction(requestNewCountry);
			}
			else if(temp.requesting == true) {
				create_country_modal.setHeading('Cancel new country request');

				create_country_modal.appendToModal(
					'<div id="requesting_country">' +
						'<p id="rc_heading">Cancel this country request?</p>' +
						'<img id="rc_country_flag" alt="country flag"' +
						' src="../country_flags/' + temp.country.country_flag+ '">' +
						'<p id="rc_country_name">' + temp.country.country_name + '</p>' +
						'<div class="shi_details">' +
							'<p class="shid_col1">Capital</p>' +
							'<a class="shid_col2" href="region_info?region_id=39">Maryland</a>' +
						'</div>' +
						'<div class="shi_details">' +
							'<p class="shid_col1">Country abbreviation</p>' +
							'<p class="shid_col2">' + temp.country.country_abbr + '</p>' +
						'</div>' +
						'<div class="shi_details">' +
							'<p class="shid_col1">Currency name</p>' +
							'<p class="shid_col2">' + temp.country.currency_name + '</p>' +
						'</div>' +
						'<div class="shi_details">' +
							'<p class="shid_col1">Currency abbreviation</p>' +
							'<p class="shid_col2">' + temp.country.currency_abbr + '</p>' +
						'</div>' +
						'<div class="shi_details">' +
							'<p class="shid_col1">Fee paid</p>' +
							'<p class="shid_col2">' + temp.country.fee_paid + ' Gold</p>' +
						'</div>' +
					'</div>'
				);

				create_country_modal.appendCancelButton('Cancel');
				create_country_modal.appendSubmitButton('Yes');
				create_country_modal.setSubmitButtonAction(cancelNewCountryRequest, [temp.country.request_id]);
			}
			else {
				create_country_modal.setErrorModal(temp.error);
			}
			create_country_modal.displayModal();
		});
	});

	$('body').on('change', '#ccd_country_flag', function(e) {
		console.log(URL.createObjectURL(e.target.files[0]));
		
		$('#rc_country_flag').attr('src', URL.createObjectURL(e.target.files[0]));
		$('#rc_country_flag').css('display', 'block');
	});

	const cancelNewCountryRequest = function(request_id) {
		var data = new FormData();
		data.append('request_id', request_id);
		data.append('action', 'cancel_country_request');
		var url = "../etc/country";

		create_country_modal.setLoadingSubmitBtn(true);

		serverRequest(data, url, false).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			create_country_modal.setLoadingSubmitBtn(false);

			if(temp.success === true) {
				create_country_modal.setSuccessModal(temp.msg);
			}
			else {
				create_country_modal.setErrorMsg(temp.error);
			}
		});
	}

	const requestNewCountry = function() {
		let region_id = $('#region_id').text();
		let country_name = $('#ccd_country_name').val();
		let country_abbr = $('#ccd_country_abbr').val();
		let country_flag = $('#ccd_country_flag')[0].files[0];
		let currency_name = $('#ccd_currency_name').val();
		let currency_abbr = $('#ccd_currency_abbr').val();

		//image
		if(country_flag) {
			let img_ext = country_flag.name.split('.').pop();
			if(img_ext.toLowerCase() != "png") {
				create_country_modal.setErrorMsg('Only png are allowed for the country flag.');
				return;
			}
			if(country_flag.size > 100000) {
				create_country_modal.setErrorMsg('Maximum image size is 50kb.');
				return;
			}
		}
		else {
			create_country_modal.setErrorMsg('Choose country flag.');
			return;
		}

		create_country_modal.setLoadingSubmitBtn(true);

		var data = new FormData();
		data.append('region_id', region_id);
		data.append('country_name', country_name);
		data.append('country_abbr', country_abbr);
		data.append('image', country_flag);
		data.append('currency_name', currency_name);
		data.append('currency_abbr', currency_abbr);
		data.append('action', 'request_new_country');
		var url = "../etc/country";
		
		serverRequest(data, url, false).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);

			create_country_modal.setLoadingSubmitBtn(false);

			if(temp.success === true) {
				create_country_modal.setSuccessModal(temp.msg);
			}
			else {
				create_country_modal.setErrorMsg(temp.error);
			}
		});
	}
	
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
})