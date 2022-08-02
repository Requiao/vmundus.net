$(document).ready(function() {
	
	//display country list
	$('#country_list').on('click', function() {
		$('#countries_div').slideToggle(250);
	});
	$('#countries_div').on('mouseleave', function() {
		$('#countries_div').slideUp(250);
	});
	
	var product_id = 0;
	$('.country').on('click', function() {
		$('#country_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var country = $(this).html();
		var country_id = $(this).attr('id');
		$('#country_list').append('<div id="country">' + country + '</div>');
		$('#country_list').append('<p id="get_country_id" hidden>' + country_id + '</p>');
		$('#countries_div').slideUp(250);
		$('#region_list').html('<div class="region"><img><p>All</p></div><span class="glyphicon glyphicon-menu-down"></span>');
		if(product_id != 0) {
			getProductList();
		}
	});
	
	//get offer list
	$('.product_market_icon').on('click', function() {
		product_id = $(this).children().next().attr('id');
		getProductList();
	});	
	
	function getProductList() {
		var country_id = $('#get_country_id').html();
		var corp_id = $('#corp_id').html()?$('#corp_id').html():'';
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('product_id', product_id);
		data.append('corp_id', corp_id);
		var url = "../etc/product_offers";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#product_offers').empty();
			$('#product_offers').html('<div id="pq_heads">' +
									  '<p id="p">Price</p>' + 
									  '<p id="q">Quantity</p>' +
									  '</div>');
			if(temp.success == false) {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
				return;
			}
			for(i = 0; i < temp.offers.length; i++) {
				var buy_for_country = '';
				var buy_for_corp = '';
				var buy_for_self = '<p class="button green buy">Buy</p>';
				
				if(temp.governor) {
					buy_for_country = '<p class="button green buy_for_country">Country</p>';
				}
				
				$('#product_offers').append('<div id="_' + temp.offers[i].offer_id + '">' +
											'<a class="seller_name" href="user_profile?id=' + temp.offers[i].user_id + '">' + 
											temp.offers[i].user_name + ' (' + temp.offers[i].seller + ')</a>' +
											'<img class="user_image" src="../user_images/' + temp.offers[i].user_image + 
											'" alt="user image">' +
											'<img class="offered_product_img" src="../product_icons/' + temp.offers[i].product_icon + 
											'" alt="product icon"></a>' +
											'<p class="price">' + temp.offers[i].price + ' ' + temp.offers[i].currency_abbr + '</p>' +
											'<p class="quantity">' + temp.offers[i].quantity + '</p>' +
											'<p class="offer_id" hidden>' + temp.offers[i].offer_id + '</p>' +
											buy_for_self +
											buy_for_country +
											buy_for_corp +
											'<input class="quantity_input" type="text" placeholder="quantity">' +
											'</div>');											
			}
			$('#product_offers').fadeIn(300);
		}
		submitData(data, url, dataReply);
	}
	
	/* buy product */
	$('#product_offers').on('click', '.buy', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var quantity = $(this).parent().children('.quantity_input').val();
		var for_who = 'user';
		buyProduct(for_who, offer_id, quantity)
	});
	
	$('#product_offers').on('click', '.buy_for_country', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var quantity = $(this).next().val();
		var for_who = 'country';
		buyProduct(for_who, offer_id, quantity)
	});
	
	$('#product_offers').on('click', '.buy_for_corp', function() {
		var offer_id = $(this).parent().children('.offer_id').html();
		var quantity = $(this).parent().children('.quantity_input').val();
		var corp_id = $('#corp_id').html();
		var for_who = 'corporation';
		buyProduct(for_who, offer_id, quantity, corp_id)
	});
	
	function buyProduct(for_who, offer_id, quantity, corp_id = '') {
		var data = new FormData();
		data.append('offer_id', offer_id);
		data.append('quantity', quantity);
		data.append('corp_id', corp_id);
		data.append('for_who', for_who);
		var url = "../etc/buy_product";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if (temp.success == false) {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			else {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				if(temp.products_left == 0) {
					$('#_' + offer_id).remove();
				}
				else {
					$('#_' + offer_id).children('.quantity').html(numberFormat(temp.products_left, 0, '.', ' '));
				}
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	}
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
});