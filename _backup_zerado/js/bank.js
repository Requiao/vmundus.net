$(document).ready(function() {
	/* switch windows */
	var menu = {"pm_deposit": {"block_name": "#deposit_div"},
				"pm_credit": {"block_name": "#credit_div"},
				"pm_exchange": {"block_name": "#exchange_div"}
			   };
			   
	var selected = getCookie("bank_menu");
	if(selected == "") {
		selected = 'pm_deposit';
	}

	$('#' + selected).css('backgroundColor', 'rgb(255, 255, 255)');
	$('#' + selected).css('borderTop', '3px solid rgb(56, 75, 89)');
	$(menu[selected].block_name).fadeIn(250);
	$('#page_menu p').on('click', function() {
		var item = $(this).attr('id');
		$('#' + selected).css('borderTop', 'none');
		$('#' + selected).css('backgroundColor', '');
		$(menu[selected].block_name).css('display', 'none');
		$(this).css('backgroundColor', 'rgb(255, 255, 255)');
		$(this).css('borderTop', '3px solid rgb(56, 75, 89)');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
		
		document.cookie = "bank_menu=" + selected + "; path=/";
	});
	
	/* bank manage */
	$('.set_rule').on('click', function() {
		var price = $(this).prev().val();
		var action = $(this).next().html();
		var data = new FormData();
		data.append('price', price);
		data.append('action', action);
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				
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
	
	/* sell currency */
	$('.sell_currency').on('click', function() {
		var amount = $(this).prev().val();
		var country_id = $(this).next().html();
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('amount', amount);
		data.append('action', 'sell_currency');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				if(temp.manager) {
					$(".bank_currency:nth-child(2) .currency_amount").html(temp.currency_total);
					$(".bank_currency:nth-child(3) .currency_amount").html(temp.gold_total);
				}
				
				$('#ucu_' + temp.currency_id + ' .currency_amount').html(temp.user_currency);
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
	
	/* buy currency */
	var country_id = '';
	$('#buy_currency').on('click', function() {
		var amount = $(this).prev().val();
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('amount', amount);
		data.append('action', 'buy_currency');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				if(temp.manager) {
					$(".bank_currency:nth-child(2) .currency_amount").html(temp.currency_total);
					$(".bank_currency:nth-child(3) .currency_amount").html(temp.gold_total);
				}
				
				$('#ucu_' + temp.currency_id + ' .currency_amount').html(temp.user_currency);
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
	
	//display buy currency list
	$('#buy_currency_list').on('click', function() {
		$('#buy_currency_div').slideToggle();
	});
	$('#buy_currency_div').on('mouseleave', function() {
		$('#buy_currency_div').slideUp(250);
	});
	
	$('.buy_currency').on('click', function() {
		$('#buy_currency_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var currency = $(this).html();
		country_id = $(this).children('.buy_country_id').html();
		$('#buy_currency_list').append('<div id="buy_selected_currency">' + currency + '</div>');
		$('#buy_currency_list').append('<p id="buy_country_id" hidden>' + country_id + '</p>');
		$('#buy_currency_div').slideUp(250);

		//get currency info
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('action', 'buy_currency_info');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#currency_price').html(temp.currency_price);
				$('#buy_limit').html(temp.buy_limit);
			}
			else {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn();
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* return to country */
	$('.return_to_country').on('click', function() {
		var type = $(this).next().html();
		var amount = $(this).prev().val();
		var data = new FormData();
		data.append('type', type);
		data.append('amount', amount);
		data.append('action', 'return_to_country');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				(type == 'gold')?$(".bank_currency:nth-child(3) .currency_amount").html(temp.total):
				$(".bank_currency:nth-child(2) .currency_amount").html(temp.total);
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
	
	/* pay debt */
	$('#user_credits_div').on('click', '.return_credit', function() {
		var credit_id = $(this).next().html();
		var amount = $(this).prev().val();
		var e = this;
		var data = new FormData();
		data.append('credit_id', credit_id);
		data.append('amount', amount);
		data.append('action', 'pay_debt');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				if(temp.manager) {
					(temp.type == 'gold')?$(".bank_currency:nth-child(3) .currency_amount").html(temp.total):
					$(".bank_currency:nth-child(2) .currency_amount").html(temp.total);
				}
				
				if(temp.payed_all) {
					$(e).parent().slideUp();
				}
				
				$(e).parent().children('.ucdc_left_to_return').html(temp.left);
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
	
	/* credit */
	$('.credit_submit').on('click', function() {
		var type = $(this).next().html();
		var amount = $(this).prev().prev().val();
		var days = $(this).prev().val();
		var data = new FormData();
		data.append('type', type);
		data.append('amount', amount);
		data.append('days', days);
		data.append('action', 'get_credit');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$('#user_credits_div').append('<div class="ucd_credits">' +
					'<a class="ucdc_user_name" href="user_profile?id=' + temp.profile_id + 
					'" target="_blank">' + temp.user_name + '</a>' +
					'<img class="ucdc_image" src="../user_images/' + temp.user_image + '">' +
					'<p class="ucdc_amount">' + numberFormat(temp.amount, '2', '.', ' ') + ' ' + temp.currency_abbr + '</p>' +
					'<p class="ucdc_rate">+' + temp.rate + '%</p>' +
					'<p class="ucdc_fee">+' + numberFormat(temp.fee, '2', '.', ' ') + ' ' + temp.currency_abbr + '</p>' +
					'<p class="ucdc_days_left">' + temp.days + ' Days</p>' +
					'<p class="ucdc_left_to_return">' + numberFormat(temp.must_return, '2', '.', ' ') + ' ' + temp.currency_abbr + '</p>' +
					'<input class="ucdc_amount_input" type="text" maxlength="8" placeholder="amount">' +
					'<p class="button green return_credit">Return</p>' +
					'<p hidden>' + temp.credit_id + '</p>' +
					'</div>');
					
				if(temp.manager) {
					(type == 'gold')?$(".bank_currency:nth-child(3) .currency_amount").html(temp.total):
					$(".bank_currency:nth-child(2) .currency_amount").html(temp.total);
				
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
	
	/* invest */
	$('.invest_submit').on('click', function() {
		var type = $(this).next().html();
		var amount = $(this).prev().val();
		var data = new FormData();
		data.append('type', type);
		data.append('amount', amount);
		data.append('action', 'invest');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				(type == 'gold')?$(".bank_currency:nth-child(3) .currency_amount").html(temp.total):
				$(".bank_currency:nth-child(2) .currency_amount").html(temp.total);
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
	
	/* deposit */
	$('.deposit_submit').on('click', function() {
		var type = $(this).next().html();
		var amount = $(this).prev().prev().val();
		var days = $(this).prev().val();
		var data = new FormData();
		data.append('type', type);
		data.append('amount', amount);
		data.append('days', days);
		data.append('action', 'make_deposit');
		var url = "../etc/bank";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			
				$('#user_deposits_div').append('<div class="udd_deposits">' +
					'<a class="uddd_user_name" href="user_profile?id=' + temp.profile_id + 
					'" target="_blank">' + temp.user_name + '</a>' +
					'<img class="uddd_image" src="../user_images/' + temp.user_image + '">' +
					'<p class="uddd_amount">' + numberFormat(temp.amount, '2', '.', ' ') + ' ' + temp.currency_abbr + '</p>' +
					'<p class="uddd_rate">+' + temp.rate + '%</p>' +
					'<p class="uddd_earned">+' + temp.earned + ' ' + temp.currency_abbr + '</p>' +
					'<p class="uddd_days">' + temp.days + ' Days</p>' +
					'<p class="uddd_days_left">' + temp.days + ' Left</p>' +
					'</div>');
					
				if(temp.manager) {
					(type == 'gold')?$(".bank_currency:nth-child(3) .currency_amount").html(temp.total):
					$(".bank_currency:nth-child(2) .currency_amount").html(temp.total);
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
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
});