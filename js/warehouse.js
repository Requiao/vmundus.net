$(document).ready(function() {
	
	//upgrade warehouse
	var quantity_upgrade = "x1";
	$('#upgrade_warehouse').on('click', function() {
		var data = new FormData();
		quantity_upgrade = "x1";
		data.append('action', 'get_info');
		data.append('amount', quantity_upgrade);
		warehouseUpgrade(data);
	});
	
	$('#x10_upgrade_warehouse').on('click', function() {
		var data = new FormData();
		quantity_upgrade = "x10";
		data.append('action', 'get_info');
		data.append('amount', quantity_upgrade);
		warehouseUpgrade(data);
	});
	
	function warehouseUpgrade(data) {
		var url = "../etc/upgrade_user_warehouse";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="pop_up_info"></div>');
			$('#pop_up_info').append('<p id="msg">' + temp[0].msg + '</p>');
			
			for(i = 1; i < temp.length; i++) {
				$('#pop_up_info').append('<div><abbr title="' + temp[i].product_name + '">' +
												'<img src="../product_icons/' + temp[i].product_icon + 
												'" alt="' + temp[i].product_name + '"></abbr>' +
												'<p class="amount">' + temp[i].amount + '</p></div>');											
			}
			$('#pop_up_info').append('<p class="button blue" id="upgrade_ok">Upgrade</p>');
			$('#pop_up_info').append('<p class="button red" id="cancel">Cancel</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, proccessReply);
	}
	
	$('#for_popups_pop').on('click', '#upgrade_ok', function() {
		var data = new FormData();
		data.append('action', 'upgrade');
		data.append('amount', quantity_upgrade);
		var url = "../etc/upgrade_user_warehouse";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$('#max_warehouse_fill').fadeOut(0);
				$('#warehouse_fill').fadeOut(0);
				var max_warehouse_fill = parseFloat($('#max_warehouse_fill').html().replace(/ /g,""));
				max_warehouse_fill += temp.capacity_add;
				$('#max_warehouse_fill').html(numberFormat(max_warehouse_fill, 2, '.', ' '));
				$('#warehouse_fill').html(numberFormat(temp.total, 2, '.', ' '));
				$('#max_warehouse_fill').fadeIn();
				$('#warehouse_fill').fadeIn();
				
				for(i = 0; i < temp.products_used.length; i++) {
					
					var amount = parseFloat($('#pi_' + temp.products_used[i].product_id).prev().prev().html().replace(/ /g,""));
					amount = amount - temp.products_used[i].amount;
					$('#pi_' + temp.products_used[i].product_id).prev().prev().html(numberFormat(amount, 2, '.', ' '));			
				}
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
	
	/* sell products */
	var tax_array = []; //store taxes for each country
	
	$('.sell').on('click', function() {
		var product_id = $(this).next().html();
		var data = new FormData();
		data.append('product_id', product_id);
		data.append('sell_for', 'self');
		data.append('action', 'get_info');
		var event = this;
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			var product_element = $(event).parent().html();
			$('#for_popups_pop').empty();
			if(temp.success == true) {
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
		var quantity = parseFloat($('#amount_input').val());
		var price =  $('#price_input').val();
		var data = new FormData();
		data.append('product_id', product_id);
		data.append('country_id', country_id);
		data.append('quantity', quantity);
		data.append('price', price);
		data.append('sell_for', 'self');
		data.append('action', 'sell');
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				var warehouse_fill = parseFloat($('#warehouse_fill').html().replace(/ /g,""));
				$('#warehouse_fill').html(numberFormat((warehouse_fill - temp.quantity), 2, '.', ' '));
				
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
	
	/* remove offer */
	$('#product_offers_div').on('click', '.pos_remove', function() {
		var offer_id = $(this).next().html();
		var data = new FormData();
		data.append('offer_id', offer_id);
		data.append('action', 'remove_user_offer');
		var e = this;
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			
			if(temp.success === true) {
				$(e).parent().fadeOut(300);
				var quantity = parseFloat(temp.quantity);
				
				var warehouse_fill = parseFloat($('#warehouse_fill').html().replace(/ /g,""));
				$('#warehouse_fill').html(numberFormat((warehouse_fill + quantity), 2, '.', ' '));
				
				var amount = parseFloat($('#pi_' + temp.product_id).prev().prev().html().replace(/ /g,""));
				var amount = numberFormat((amount + quantity), 2, '.', ' ');
				$('#pi_' + temp.product_id).prev().prev().html(amount);
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
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});

});