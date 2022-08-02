$(document).ready(function() {
	/* join party */
	$('#container').on('click', '.join_party', function() {
		var party_id = $(this).attr('id');
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$(e).html('Cancel');
				$(e).removeClass('join_party');
				$(e).addClass('cancel_application');
				$(e).removeClass('green');
				$(e).addClass('red');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("party_id=" + party_id + "&action=join");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* cancel application */
	$('#container').on('click', '.cancel_application', function() {
		var e = this;
		var url = "../etc/political_party_manage";
		function showInfo(xhttp) {
			var reply = xhttp.responseText;
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$(e).html('Join');
				$(e).removeClass('cancel_application');
				$(e).addClass('join_party');
				$(e).removeClass('red');
				$(e).addClass('green');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		function sendData(xhttp) {
			xhttp.send("action=cancel_app");
		}
		loadDoc(url, showInfo, sendData);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$(this).parent().parent().fadeOut(300);
	});
});