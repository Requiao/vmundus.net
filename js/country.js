$(document).ready( function() {
	/* switch windows */
	var menu = {"country_info": {"block_name": "#country_info_block"},
				"region_info": {"block_name": "#country_regions_block"},
				"government_info": {"block_name": "#country_government_block"},
				"politics_info": {"block_name": "#politics_info_block"}
			   };
	
	var selected = getCookie("ncountry_menu");
	if(selected == "" || menu[selected] == undefined || !menu[selected]) {
		selected = 'country_info';
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
		
		document.cookie = "ncountry_menu=" + selected + "; path=/";
	});
	
	//display country list
	$('#country_list').on('click', function() {
		$('#countries_div').slideToggle(250);
	});
	$('#countries_div').on('mouseleave', function() {
		$('#countries_div').slideUp(250);
	});
	
	$('.country').on('click', function() {
		var country_id = $(this).attr('id');
		window.location = "country?country_id=" + country_id;
	});
	
	/* invest gold */
	$('#invest_gold_btn').on('click', function() {
		amount = $('#invest_gold_amount').val();
		country_id = $('#get_country_id').html();
		var data = new FormData();
		data.append('amount', amount);
		data.append('country_id', country_id);
		data.append('action', 'invest_gold');
		var url = "../etc/country";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p id="msg_reply"></p>');
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
	
	$('#invest_gov_gold_btn').on('click', function() {
		amount = $('#invest_gov_gold_amount').val();
		var data = new FormData();
		data.append('amount', amount);
		data.append('action', 'invest_gov_gold');
		var url = "../etc/country";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p id="msg_reply"></p>');
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
	
	/* Research */
	updateResearchClock();
	function updateResearchClock() {
		$('.research_end_time').each(function() {
			countdownClock(this);
		});
		var t = setTimeout(updateResearchClock, 950);
	};
	
	var research_id;
	$('.info_blocks').on('click', '.invest_resources_for_research', function() {
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
	
	/* Politics */
	//update clock
	updateLawRemainingTime();
	function updateLawRemainingTime() {
		$('.pli_expires_in').each(function() {
			countdownClock(this);
		});
		var t = setTimeout(updateLawRemainingTime, 950);
	};
	
	/* law history*/
	$('#law_history').on('click', function() {
		var data = new FormData();
		var country_id = $('#get_country_id').html();
		data.append('country_id', country_id);
		data.append('action', 'law_history');
		var url = "../etc/country";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$("#for_popups_pop").empty();
			$("#for_popups_pop").append('<div id="law_history_div"></div>');
			if(temp.success == true) {
				$('#law_history_div').append('<div id="law_history_div_head">' +
							 '<span id="law_history_div_close" class="glyphicon glyphicon-remove-circle"></span>' +
							 '<p id="n_heading">Last 100 Laws</p>' +
							 '</div>');
				for(var x = 0; x < temp.laws.length; x++) {
					var pli_yes = "";
					var pli_check = "";
					var pli_yes_voted = "";
					
					var pli_no = "";
					var pli_times = "";
					var pli_no_voted = "";
					
					if(temp.laws[x].yes > temp.laws[x].no) {
						pli_no = "pli_gray";
						pli_times = "pli_gray_sign";
						pli_no_voted = "pli_gray_voted";
					}
					else if (temp.laws[x].no > temp.laws[x].yes) {
						pli_yes = "pli_gray";
						pli_check = "pli_gray_sign";
						pli_yes_voted = "pli_gray_voted";
					}
					$('#law_history_div').append('<div class="info_blocks">' +
						'<p class="heads">' + temp.laws[x].responsibility + '</p>' +
						'<div class="pli_description">Description: ' + temp.laws[x].description + '</div>' +
						'<p class="pli_proposed_by">Proposed by: ' + 
						'<a href="user_profile?id=' + temp.laws[x].governor_id + 
						'" target="_blank">' + temp.laws[x].governor_name + '</a>' +
						'</p>' +
						'<p class="pli_proposed_on">Proposed on: ' + temp.laws[x].proposed_date + 
						' ' + temp.laws[x].proposed_time + '</p>' +
						'<div class="pli_yes ' + pli_yes + '">' +
						'<p class="pli_check ' + pli_check + '"><span class="fa fa-check" aria-hidden="true"></span></p>' +
						'<p class="pli_yes_voted ' + pli_yes_voted + '">' + temp.laws[x].yes + '</p>' +
						'</div>' +
						'<div class="pli_no ' + pli_no + '">' +
						'<p class="pli_times ' + pli_times + '"><span class="fa fa-times"></span></p>' +
						'<p class="pli_no_voted ' + pli_no_voted + '">' + temp.laws[x].no + '</p>' +
						'</div>' +
						'</div>');
				}
			}
			else {
				$('#law_history_div').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#law_history_div').html(temp.error);
			}
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	/* cz requests */
	$('#cz_requests').on('click', function() {
		var data = new FormData();
		data.append('action', 'cz_requests');
		var url = "../etc/country";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$("#for_popups_pop").empty();
			$("#for_popups_pop").append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				for(var x = 0; x < temp.requests.length; x++) {
				$('#reply_info').append('<div class="cz_app_info">' +
										'<img src="../user_images/' + temp.requests[x].user_image + '" alt="user image">' +
										'<a href="user_profile?id=' + temp.requests[x].profile_id + '" target="_blank">' +
										temp.requests[x].user_name + '</a>' +
										'<p class="button blue accept_cz">Accept</p>' +
										'<p class="button red decline_cz">Decline</p>' +
										'<p hidden>' + temp.requests[x].profile_id + '</p>' +
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
	
	//accept cz
	$('#for_popups_pop').on('click', '.accept_cz', function() {
		var profile_id = $(this).next().next().html();
		var e = this;
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('action', 'accept_cz');
		var url = "../etc/country";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$("#for_popups_pop2").empty();
			$("#for_popups_pop2").append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#for_popups_pop2 #reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#for_popups_pop2 #reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$(e).parent().slideUp();
			}
			else {
				$('#for_popups_pop2 #reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#for_popups_pop2 #reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#for_popups_pop2 #reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	//decline cz
	$('#for_popups_pop').on('click', '.decline_cz', function() {
		var profile_id = $(this).next().html();
		var e = this;
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('action', 'decline_cz');
		var url = "../etc/country";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$("#for_popups_pop2").empty();
			$("#for_popups_pop2").append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#for_popups_pop2 #reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#for_popups_pop2 #reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$(e).parent().slideUp();
			}
			else {
				$('#for_popups_pop2 #reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#for_popups_pop2 #reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#for_popups_pop2 #reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
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

	$('#for_popups_pop').on('click', '#law_history_div_close', function() {
		$("#for_popups_pop").fadeOut(300);
	});
})