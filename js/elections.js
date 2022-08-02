$(document).ready( function() {
	//update elections clock.
	updateElectionClock();
	function updateElectionClock() {
		$('.elections_clock').each(function() {
			countdownClock(this);
		});
		var t = setTimeout(updateElectionClock, 950);
	};
	
	/* vote */
	var election_id = '';
	var candidate_id = '';
	$('.cd_vote').on('click', function() {
		election_id = $(this).children('.election_id').html();
		candidate_id = $(this).children('.candidate_id').html();
		var candidate_name = $(this).parent().children('.candidate_name').html();
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to vote for ' + candidate_name + '?</p>');
		$('#reply_info').append('<p class="button green" id="yes_vote">Vote</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#yes_vote', function() {
		var data = new FormData();
		data.append('election_id', election_id);
		data.append('candidate_id', candidate_id);
		data.append('action', 'vote');
		var url = "../etc/elections_vote";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$('.' + election_id).each(function() {
					$(this).children().last().removeClass('cd_vote');
					$(this).children().last().addClass('cd_voted');
					
					$(this).children().last().children().first().removeClass('cd_yes_vote');
					$(this).children().last().children().first().addClass('cd_yes_voted');
					
					$(this).children().last().children().next().removeClass('cd_check');
					$(this).children().last().children().next().addClass('cd_checked');
				});
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
	
	/* show previous president elections */
	$('#prev_pres_elec_head').on('click', function() {
		$('#prev_president_elections').slideToggle();
	});
	
	/* show previous congress elections */
	$('#prev_cong_elec_head').on('click', function() {
		$('#prev_congress_elections').slideToggle();
	});
	
	/* participate in elections */
	$('.participate_in_elections').on('click', function() {
		var election_id = $(this).attr('id');
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to participate in these elections?</p>');
		$('#reply_info').append('<p class="participate button green" id="' + election_id + '">Participate</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '.participate', function() {
		var election_id = $(this).attr('id');
		var data = new FormData();
		data.append('election_id', election_id);
		data.append('action', 'apply');
		var url = "../etc/participate_elections";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				var history = '';
				for(var x = 0; x < temp.history.length; x++) {
					history += '<p>- ' + temp.history[x].position_name + ' (' + 
							   '<a href="country?country_id=' + temp.history[x].country_id + 
							   '">' + temp.history[x].country_name + '</a>' +
							   '): ' + temp.history[x].days + '</p>';
				}
				
				$('#' + temp.elections_id).after('<div class="candidate_div ' + temp.elections_id + '">' +
						'<a class="candidate_name" href="user_profile?id=' + temp.candidate_id + 
						'">' + temp.candidate_name + '</a>' +
						'<img src="../user_images/' + temp.candidate_image + '" class="candidate_image">' +
						'<div class="candidate_history">' +
						'<p>History:</p>' + 
						history	+ 
						'</div>' +
						'<p class="stop_participate_in_elections button red" id="' + temp.elections_id+ '">Cancel</p>' +
						'</div>');
				
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
	
	
	//cancel candidature
	var e = undefined;
	$('#container').on('click', '.stop_participate_in_elections', function() {
		var election_id = $(this).attr('id');
		e = this;
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to stop participating in these elections?</p>');
		$('#reply_info').append('<p class="stop_participate button red" id="' + election_id + '">Stop</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '.stop_participate', function() {
		var election_id = $(this).attr('id');
		var data = new FormData();
		data.append('election_id', election_id);
		data.append('action', 'cancel');
		var url = "../etc/participate_elections";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$(e).parent().remove();
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
	
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
});