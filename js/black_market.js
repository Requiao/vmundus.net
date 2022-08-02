$(document).ready(function() {
    
    //chose product to offer
    $('#user_products_list item').on('click', function() {
        $('#mol_available .mod_text').text($(this).attr('available_amount'));
        $('#mol_fee .mod_text').text($(this).attr('fee'));
        $('#mol_min_price .mod_text').html(
            '<p>' + $(this).attr('min_price') + '</p>' +
            '<img src="../img/gold.png">'
        );
        $('#mol_max_quantity .mod_text').text($(this).attr('max_quantity'));
    });

    //make offer
    $('#make_offer').on('click', function() {
        let product_id = $('#user_products_list selected item').attr('product_id');
        let quantity = $('#offer_quantity').val();
        let price = $('#offer_price').val();
        
        let data = new FormData();
		data.append('action', 'make_offer');
		data.append('product_id', product_id);
		data.append('quantity', quantity);
        data.append('price', price);
        var url = "../etc/black_market";

        $('#mod_error').empty();
		serverRequest(data, url).then(reply => {
            console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success == true) {
                $('#mol_available .mod_text').text(temp.available_amount);
                
                let item = temp.offer;
                $('#uod_offers').prepend(
                    '<div style="display: none" class="product_on_sale" offer_id=' + item.offer_id + '>' +
                        '<a class="seller_name" href="user_profile?id=' + item.seller_id + 
                        '">' + item.seller_name + '</a>' +
                        '<img class="user_image" src="../user_images/' + item.seller_image + 
                        '" alt="user image">' + 
                        '<img class="pos_product_icon" src="../product_icons/' + item.product_icon + 
                        '" alt="' + item.product_name + '">' +
                        '<p class="pos_quantity">' + item.quantity + '</p>' +
                        '<p class="pos_price">' + item.price + '</p>' +
                        '<img class="pos_gold_icon" src="../img/gold.png" alt="gold">' +
                        '<p class="pos_tax">' + item.fee + '</p>' +
                        '<p class="pos_remove button red">Remove</p>' +
                    '</div>'
                );
                $('.product_on_sale').first().slideDown();
			}
			else {
                $('#mod_error').text(temp.error);
			}
		});
    });

    //remove offer
    $('#user_offers_div').on('click', '.pos_remove', function() {
        let offer_id = $(this).parent().attr('offer_id');
        
        let data = new FormData();
		data.append('action', 'remove_offer');
		data.append('offer_id', offer_id);
        var url = "../etc/black_market";

        $('#mod_error').empty();
		serverRequest(data, url).then(reply => {
            console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$(this).parent().slideUp();
			}
			else {
                $('#mod_error').text(temp.error);
			}
		});
    });

    //get offers
    $('.product_market_icon').on('click', function() {
        let product_id = $(this).attr('product_id');
        
        let data = new FormData();
		data.append('action', 'get_offers');
		data.append('product_id', product_id);
        var url = "../etc/black_market";

        $('#po_error').empty();
        $('#po_offers').empty();
		serverRequest(data, url).then(reply => {
            console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success == true) {
                temp.offers.map((item) => {
                    $('#po_offers').append(
                        '<div class="product_on_sale" offer_id=' + item.offer_id + '>' +
                            '<a class="seller_name" href="user_profile?id=' + item.seller_id + 
                            '">' + item.seller_name + '</a>' +
                            '<img class="user_image" src="../user_images/' + item.seller_image + 
                            '" alt="user image">' + 
                            '<img class="pos_product_icon" src="../product_icons/' + item.product_icon + 
                            '" alt="' + item.product_name + '">' +
                            '<p class="pos_quantity">' + item.quantity + '</p>' +
                            '<p class="pos_price">' + item.price + '</p>' +
                            '<img class="pos_gold_icon" src="../img/gold.png" alt="gold">' +
                            '<p class="buy_product button green">Buy</p>' +
                            '<input class="buy_quantity_input" type="text" placeholder="quantity" maxlength="4">' +
                        '</div>'
                    )
                });
			}
			else {
                $('#po_error').text(temp.error);
			}
		});
    });

    //buy products
    $('#po_offers').on('click', '.buy_product', function() {
        let offer_id = $(this).parent().attr('offer_id');
        let quantity = $(this).next().val();

        let data = new FormData();
		data.append('action', 'buy_product');
		data.append('offer_id', offer_id);
		data.append('quantity', quantity);
        var url = "../etc/black_market";

		serverRequest(data, url).then(reply => {
            console.log(reply);
            var temp = JSON.parse(reply);
            $('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
                $(this).parent().children('.pos_quantity').text(temp.products_left);

                if(temp.products_left == 0) {
                    $(this).parent().slideUp();
                }

                $('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
                $('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
                $('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
            }
            $('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		});
    });

    /* cancel/ok btn */
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
});