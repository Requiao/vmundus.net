$(document).ready(function() {

	$('body').on('click', '#urd_collect', function() {
		let data = new FormData();
		data.append('action', 'collect_user_rewards');
		let url = "../etc/market_manage";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			let modal = new ModalBox('800px', '500px');
			if(temp.success === true) {
				modal.setSuccessModal(temp.msg);
				$('#urd_collected').text(temp.collected);
				$('#urd_available').text(temp.available);
			}
			else {
				modal.setErrorModal(temp.error);
			}
		});
	});
	
	/* check if bought before */
	/*checkIfBought();
	function checkIfBought() {
		var data = new FormData();
		data.append('action', 'if_bought_before');
		var url = "../etc/market_manage";
		function replyRewards(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$("#for_popups_pop").empty();
				$("#for_popups_pop").append('<div id="purchase_sum"></div>');
				$("#purchase_sum").append('<span class="glyphicon glyphicon-ok-circle"></span>' +
										  '<p id="ps_head">' + temp.msg_head + '</p>' +
										  '<p id="ps_msg">' + temp.msg + '</p>'
										  );
				
				for (i = 0; i < temp.purchase.length ; i++) {
					$("#purchase_sum").append('<p class="items_bough">' + (i + 1) + '. ' + temp.purchase[i].item_name + '</p>');
				}
				$("#purchase_sum").append('<p class="button blue" id="reply_ok">Close</p>');
				$("#for_popups_pop").fadeIn(250);
			}
		}
		submitData(data, url, replyRewards, false);
	};
	
	$('.collect_leftover').on('click', function() {
		var id = $(this).attr('id');
		var data = new FormData();
		data.append('id', id);
		data.append('action', 'get_leftover');
		var url = "../etc/market_manage";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				if(temp.all == true) {
					$('#' + id).prev().remove();
					$('#' + id).remove();
				}
				else {
					$('#' + id).prev().html(temp.new_msg);
				}
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
		$('#for_popups_pop').empty();
	});*/
});