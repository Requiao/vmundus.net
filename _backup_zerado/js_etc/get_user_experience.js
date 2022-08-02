function setNewExperience() {
	var data = new FormData();
	data.append('action', 'get_user_experience');
	var url = "../etc/user_info";
	function deleteReply(reply) {
		var temp = JSON.parse(reply);
		var prev_lvl = $('#ui_user_lvl p').html();
		
		if(prev_lvl != temp.current_level) {
			$('#ui_user_lvl p').fadeOut(100, function() {
				$(this).html(temp.current_level).fadeIn(100);
			});
		}

		$('#ui_user_lvl_progres').animate({width: + temp.progress + '%'});
		
		$('#ui_user_lvl_progres_bar p').fadeOut(100, function() {
			$(this).html(temp.experience + '/' + temp.next_experience).fadeIn(50);
		});
	}
	submitData(data, url, deleteReply, false);
}