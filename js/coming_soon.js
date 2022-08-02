$(document).ready(function() {
	/*set clock */
	getTime();
	function getTime() {
		var d = new Date();
		var year = d.getFullYear();
		var month = d.getMonth();
		var month_name;
		var date = d.getDate();
		var hour = d.getHours();
		var min = d.getMinutes();
		var sec = d.getSeconds();
		if(hour >= 12) {
			var am_pm = "pm";
			hour = hour - 12;
		}
		else {
			var am_pm = "am";
		}
		if(hour < 10) {
			hour = '0' + hour;
		}
		if(min < 10) {
			min = '0' + min;
		}
		if(sec < 10) {
			sec = '0' + sec;
		}
		switch(month) {
			case 0:
				month_name = "January";
				break;
			case 1:
				month_name = "February";
				break;
			case 2:
				month_name = "March";
				break;
			case 3:
				month_name = "April";
				break;
			case 4:
				month_name = "May";
				break;
			case 5:
				month_name = "June";
				break;
			case 6:
				month_name = "July";
				break;
			case 7:
				month_name = lang.month.august;
				break;
			case 8:
				month_name = "September";
				break;
			case 9:
				month_name = "Ocotber";
				break;	
			case 10:
				month_name = "November";
				break;	
			case 5:
				month_name = "December";
				break;
		}
		$('#clock').html(hour + ':' + min + ':' + sec + ' ' + am_pm + ' ' + month_name + ' ' + date + ' ' + year);
		t = setTimeout(getTime, 1000);
	};
	
	updateComingSoonClock();
	function updateComingSoonClock() {
		var e = $('#coming_in span').get(0); 
		countdownClock(e);
		var t = setTimeout(updateComingSoonClock, 950);
	};
	
	/* slide show */
	var slide_list = ['img/slide_1.png', 'img/slide_2.png', 'img/slide_3.png', 'img/slide_4.png', 'img/slide_5.png',
					  'img/slide_6.png', 'img/slide_7.png', 'img/slide_8.png', 'img/slide_9.png', 'img/slide_10.png'];
	var p = 0;
	
	$('#prev_img').on('click', function() {
		$('#slide_show_div img').attr('src', slide_list[p]).fadeOut(0);
		p--;
		if(p == -1) {
			var last = slide_list.length - 1;
			$('#slide_show_div img').attr('src', slide_list[last]).fadeIn();
			p = last;
		}
		else {
			$('#slide_show_div img').attr('src', slide_list[p]).fadeIn();
		}
	});
	
	$('#next_img').on('click', function() {
		$('#slide_show_div img').attr('src', slide_list[p]).fadeOut(0);
		p++;
		if(p > slide_list.length - 1) {
			var first = 0;
			$('#slide_show_div img').attr('src', slide_list[first]).fadeIn();
			p = first;
		}
		else {
			$('#slide_show_div img').attr('src', slide_list[p]).fadeIn();
		}
	});
	
	$('#slide_show_div img').on('click', function() {
		$('#popup_div').empty();
		$('#popup_div').html('<div id="slide_show_zoom_div">' +
							 '<p id="zprev_img"><i class="fa fa-chevron-left" aria-hidden="true"></i></p>' +
							  '<img src="' + slide_list[p] + '">' +
							  '<p id="znext_img"><i class="fa fa-chevron-right" aria-hidden="true"></i></p>' +
							  '</div>');
		$('#popup_div').fadeIn();
	});
	
	$('#popup_div').on('click', '#zprev_img', function() {
		$('#slide_show_div img').attr('src', slide_list[p]).fadeOut(0);
		$('#slide_show_zoom_div img').attr('src', slide_list[p]).fadeOut(0);
		p--;
		if(p == -1) {
			var last = slide_list.length - 1;
			$('#slide_show_div img').attr('src', slide_list[last]).fadeIn();
			$('#slide_show_zoom_div img').attr('src', slide_list[last]).fadeIn();
			p = last;
		}
		else {
			$('#slide_show_div img').attr('src', slide_list[p]).fadeIn();
			$('#slide_show_zoom_div img').attr('src', slide_list[p]).fadeIn();
		}
	});
	
	$('#popup_div').on('click', '#znext_img', function() {
		$('#slide_show_div img').attr('src', slide_list[p]).fadeOut(0);
		$('#slide_show_zoom_div img').attr('src', slide_list[p]).fadeOut(0);
		p++;
		if(p > slide_list.length - 1) {
			var first = 0;
			$('#slide_show_div img').attr('src', slide_list[first]).fadeIn();
			$('#slide_show_zoom_div img').attr('src', slide_list[first]).fadeIn();
			p = first;
		}
		else {
			$('#slide_show_div img').attr('src', slide_list[p]).fadeIn();
			$('#slide_show_zoom_div img').attr('src', slide_list[p]).fadeIn();
		}
	});
	
	// When the user clicks anywhere outside of the popup_div, close it
	$('html').on('click', function(event) {
		var modal = $("#popup_div").get(0);
		if(event.target == modal) {
			$(modal).fadeOut(250);
		}
	});
	
	/* test email */
	function testEmail(email) {
		var regex_email = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;
		if(!regex_email.test(email)) {
			return false;
		}
		else {
			return true;
		}
	}
	
	$('#submit_email').on('click', function() {
		preRegister();
	});
	
	function preRegister() {
		var email = $('#email').val().trim();
		var notify = $('#notify:checked').val() ? $('#notify:checked').val() : 0;
		var regex_email = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;
		if(!regex_email.test(email)) {
			$('#reply').html('Invalid email.');
			$('#reply').css('color', 'rgb(190, 47, 47)');
		}
		else {
			var data = new FormData();
			data.append('email', email);
			data.append('notify', notify);
			data.append('action', 'pre_regester');
			var url = "pre_regester";
			function preRegesterReply(reply) {
				var reply = JSON.parse(reply);
				$('#reply').html(reply.msg);
				if(reply.is_suc == true) {
					email = $('#email').val('');
					$('#reply').css('color', 'rgb(88, 102, 117)');
				}
				else {
					$('#reply').css('color', 'rgb(190, 47, 47)');
				}
			}
			submitData(data, url, preRegesterReply);
		}
	}
	
	//pre register on enter
	$('html').keydown(function (e) {
		if (e.keyCode == 13) {
			preRegister();
		}
	});
});