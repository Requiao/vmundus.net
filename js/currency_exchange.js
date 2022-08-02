$(document).ready(function() {
	
	/* switch items */
	var buying_type = 'buying_gold';
	$('#switch_buying_items').on('click', function() {
		var buying = $('#buying_item').html();
		var selling = $('#selling_item').html();
		$('#buying_item').html(selling);
		$('#selling_item').html(buying);
		buying_type = buying_type=='buying_gold'?'buying_currency':'buying_gold';
	});
	
	//display buy currency list
	$('#currency_exchange_controls').on('click', '#currency_list', function() {
		$('#currency_div').slideToggle(250);
	});
	$('#currency_exchange_controls').on('mouseleave', '#currency_div', function() {
		$('#currency_div').slideUp(250);
	});
	
	$('#currency_exchange_controls').on('click', '.currency', function() {
		var currency = $(this).html();
		var currency_id = $(this).attr('id');
		$('#currency_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		$('#currency_list').append('<div id="selected_currency">' + currency + '</div>');
		$('#currency_list').append('<p id="currency_id" hidden>' + currency_id + '</p>');
		$('#currency_div').slideUp(250);
		getCurrencyOffers(currency_id)
	});
	
	$('#currency_exchange_controls').on('click', '#switch_buying_items', function() {
		var currency_id = $('#currency_id').html();
		if(currency_id != 0) {
			getCurrencyOffers(currency_id)
		}
	});
	
	function getCurrencyOffers(currency_id)  {
		var corp_id = $('#corp_id').html()?$('#corp_id').html():'';
		var data = new FormData();
		data.append('currency_id', currency_id);
		data.append('corp_id', corp_id);
		data.append('action', buying_type);
		var url = "../etc/buy_sell_currency";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#currency_offers').empty();

			if(temp.success == true) {
				$('#currency_offers').append('<p id="other_offers_head">Offers</p>' +
											 '<p id="amount_head">Amount</p>' +
											 '<p id="price_head">Rate</p>'
											);
				for(i = 0; i < temp.offers.length; i++) {	
					var buy_for_country = '';
					var buy_for_corp = '';
					var buy_for_self = '<p class="button green buy">Buy</p>';
					
					if(temp.is_corp) {
						buy_for_corp = '<p class="button green buy_for_corp">cBuy</p>';
						buy_for_self = '';
					}
					else if(temp.is_governor) {
						var buy_for_country = '<p class="button green buy_for_country">Country</p>';
					}
					
					if(buying_type == 'buying_currency') {
						$('#currency_offers').append('<div class="offer _' + temp.offers[i].offer_id + '">' +
											'<p class="offer_id" hidden>' + temp.offers[i].offer_id + '</p>' + 
											'<a href="user_profile?id=' + temp.offers[i].seller_id + '" class="user_name">' +
											temp.offers[i].seller_name + '</a>' + 
											'<img class="user_image" src="../user_images/' + temp.offers[i].seller_img + 
											'" alt="user image" target="_new">' +
											'<p class="offered_by">Offered by ' + temp.offers[i].seller + '</p>' +
											'<p class="amount_selling">' + temp.offers[i].amount + 
											' ' + temp.offers[i].currency_abbr + '</p>' +
											'<p class="rate">1 ' + temp.offers[i].currency_abbr + ' = ' + temp.offers[i].rate + '</p>' +
											'<img class="gold_img" src="../img/gold.png">' +
											'<input class="amount_input" type="text" placeholder="0.00" maxlength="8">' +
											buy_for_self +
											buy_for_country +
											buy_for_corp +
											'</div>');	
					}
					else {
						$('#currency_offers').append('<div class="offer _' + temp.offers[i].offer_id + '">' +
											'<p class="offer_id" hidden>' + temp.offers[i].offer_id + '</p>' + 
											'<a href="user_profile?id=' + temp.offers[i].seller_id + '" class="user_name">' +
											temp.offers[i].seller_name + '</a>' + 
											'<img class="user_image" src="../user_images/' + temp.offers[i].seller_img + 
											'" alt="user image" target="_new">' +
											'<p class="offered_by">Offered by ' + temp.offers[i].seller + '</p>' +
											'<p class="amount_selling">' + temp.offers[i].amount + ' Gold</p>' +
											'<p class="gold_rate">1 </p>' +
											'<img class="gold_img" src="../img/gold.png">' +
											'<p class="rate">= ' + temp.offers[i].rate + ' ' + temp.offers[i].currency_abbr + '</p>' +
											'<input class="amount_input" type="text" placeholder="0.00" maxlength="8">' +
											buy_for_self +
											buy_for_country +
											buy_for_corp +
											'</div>');	
					}
				}
			}
			else {
				$('#currency_offers').append('<p id="error_offers">' + temp.error + '</p>');
			}
			
		}
		submitData(data, url, dataReply);
	};
	
	//Make offer
	$('#make_offer_btn').on('click', function() {
		$('#make_offer_div').slideToggle();
	});
	
	var offering = 'offer_gold';
	$('#switch_offering_items').on('click', function() {
		var buying = $('#offering_item').html();
		var selling = $('#offer_for_item').html();
		$('#offering_item').html(selling);
		$('#offer_for_item').html(buying);
		offering = offering=='offer_gold'?'offer_currency':'offer_gold';
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
				if(offering == 'offer_currency') {
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
	
	//buy currency
	$('#container').on('click', '.buy', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var amount = $(this).parent().children('.amount_input').val();
		var for_who = 'user';
		buyCurrency(for_who, offer_id, amount)
	});
	
	$('#container').on('click', '.buy_for_country', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var amount = $(this).parent().children('.amount_input').val();
		var for_who = 'country';
		buyCurrency(for_who, offer_id, amount)
	});
	
	$('#container').on('click', '.buy_for_corp', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var amount = $(this).parent().children('.amount_input').val();
		var for_who = 'corporation';
		buyCurrency(for_who, offer_id, amount)
	});
	
	function buyCurrency(for_who, offer_id, amount) {
		var corp_id = $('#corp_id').html()?$('#corp_id').html():'';
		var data = new FormData();
		data.append('amount', amount);
		data.append('for_who', for_who);
		data.append('offer_id', offer_id);
		data.append('corp_id', corp_id);
		data.append('action', 'buy_currency');
		var url = "../etc/buy_sell_currency";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');

				if(temp.new_amount == 0) {
					$('._' + offer_id).slideUp();
				}
				else {
					if(temp.buy_sell == 'sell') {
						$('._' + offer_id).children('.amount_selling').html(temp.new_amount + ' ' + temp.currency_abbr);
					}
					else {
						$('._' + offer_id).children('.amount_selling').html(temp.new_amount + ' Gold');
					}
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
	}
	
	/* remove offer */
	$('#container').on('click', '.remove_offer', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var data = new FormData();
		data.append('offer_id', offer_id);
		data.append('action', 'remove_offer');
		var url = "../etc/buy_sell_currency";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('._' + offer_id).slideUp();
			}
			else {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="up_down_grade_info"></div>');
				$('#up_down_grade_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#up_down_grade_info').append('<p id="msg">' + temp.error + '</p>');
				$('#up_down_grade_info').append('<p class="button blue" id="co_ok">Upgrade</p>');
				$('#up_down_grade_info').append('<p class="button red" id="cancel">Cancel</p>');
				$('#for_popups_pop').fadeIn(300);
			}
			
		}
		submitData(data, url, dataReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
	
});