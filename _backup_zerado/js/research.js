$(document).ready(function() {
    /* Research */
	updateResearchClock();
	function updateResearchClock() {
		$('.research_end_time').each(function() {
			countdownClock(this);
		});
		var t = setTimeout(updateResearchClock, 950);
	};
	
	var research_id;
	$('#container').on('click', '.invest_resources_for_research', function() {
		research_id = $(this).next().html();
		var e = this;
		var data = new FormData();
		data.append('research_id', research_id);
		data.append('action', 'invest_resources');
		var url = "../etc/research";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p id="msg_reply"></p>');
				
				for(var x = 0; x < temp.product.length; x++) {
					$('#reply_info').append('<div class="icon_amount" id="pi_' + temp.product[x].product_id + '">' +
										    '<abbr title="' + temp.product[x].product_name + 
										    '"><img class="product_icon" src="../product_icons/' + temp.product[x].product_icon +
										    '" alt="' + temp.product[x].product_name + '"></abbr>' +
										    '<p class="amount">' + numberFormat(temp.product[x].amount, 2, '.', ' ') + '</p>' +
											'<input class="support_rec_input" type="text" placeholder="0.00">' +
											'<p class="support_research">Support</p>' +
											'<p hidden="">' + temp.product[x].product_id + '</p>' +
											'</div>');
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
	});

	$('#for_popups_pop ').on('click', '.support_research', function() {
		var e = this;
		var quantity = $(this).prev().val();
		var product_id = $(this).next().html();
		var data = new FormData();
		data.append('quantity', quantity);
		data.append('product_id', product_id);
		data.append('research_id', research_id);
		data.append('action', 'support_research');
		var url = "../etc/research";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#msg_reply').html(temp.msg);
				$('#msg_reply').css('color', 'green');
				$('#msg_reply').css('display', 'block');
			
				$(e).parent().children('.amount').html(numberFormat(temp.amount, 2, '.', ' '));
			
				$('#re_' + research_id + ' .progress').animate({width: temp.collected_perc + "%"});
				$('#re_' + research_id + ' .bar_mark').html(numberFormat(temp.collected_prods, 2, '.', ' ') + 
															  '(' + temp.collected_perc + '%)');
				if(temp.research_started) {
					$('#re_' + research_id + ' .progress').parent().slideUp();
					$('#re_' + research_id + ' .invest_resources_for_research').slideUp();
					$('#re_' + research_id + ' .rbd_time').slideUp();
					$('#re_' + research_id + ' .rbd_image').after('<p class="research_end_time">' + temp.end_time + '</p>');
					
				}
			}
			else {
				$('#msg_reply').html(temp.error);
				$('#msg_reply').css('color', 'rgb(207, 28, 28)');
				$('#msg_reply').css('display', 'block');
			}
		}
		submitData(data, url, dataReply);
	});
});