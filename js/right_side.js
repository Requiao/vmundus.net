$(document).ready(function() {
	/* add to freinds */
	$('#a_decline_friend').on('click', function() {
		var profile_id = $(this).next().next().html();
		friendshipRequest('decline', profile_id)
	});
	
	$('#a_add_friend').on('click', function() {
		var profile_id = $(this).next().html();
		friendshipRequest('accept', profile_id)
	});
	
	function friendshipRequest(action, profile_id) {
		var data = new FormData();
		data.append('profile_id', profile_id);
		data.append('accept_decline', action);
		data.append('action', 'add_to_friends');
		var url = "../etc/manage_right_side";
		function proccessReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#a_reply').empty();
			if (temp.success === true) {
				$('#addfriend').empty();
				$('#addfriend').append('<p id="a_reply"></p>');
				$('#a_reply').html(temp.msg);
				$('#a_reply').css('color', 'rgb(54, 124, 57)');
				$('#a_reply').css('fontSize', '25px');
				$('#addfriend').css('height', '30px');
				$('#addfriend').fadeOut(5000);
			}
			else {
				$('#a_reply').html(temp.error);
				$('#a_reply').css('color', 'rgb(192, 13, 13)');
			}

		}
		submitData(data, url, proccessReply);
	}

	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
});