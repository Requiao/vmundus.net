$(document).ready(function() {

	/* buy product */
	$('.buy').on('click', function() {
		var quantity = $(this).prev().val();
		var product_id = $(this).next().html();
		var for_who = 'user';
		var e = this;
		buyProduct(for_who, product_id, quantity, e)
	});
	
	$('.buy_for_country').on('click', function() {
		var quantity = $(this).prev().prev().prev().val();
		var product_id = $(this).prev().html();
		var for_who = 'country';
		var e = this;
		buyProduct(for_who, product_id, quantity, e)
	});
	
	function buyProduct(for_who, product_id, quantity, e) {
		var data = new FormData();
		data.append('quantity', quantity);
		data.append('product_id', product_id);
		data.append('for_who', for_who);
		var url = "../etc/gold_market";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('.reply_msg').empty();
			if (temp.success == true) {
				$(e).parent().children('.reply_msg').html(temp.msg);
				$(e).parent().children('.reply_msg').css('color', 'green');
				$(e).parent().children('.reply_msg').css('display', 'block');
				
			}
			else {
				$(e).parent().children('.reply_msg').html(temp.error);
				$(e).parent().children('.reply_msg').css('color', 'rgb(207, 28, 28)');
				$(e).parent().children('.reply_msg').css('display', 'block');
			}
		}
		submitData(data, url, dataReply);
	}
});