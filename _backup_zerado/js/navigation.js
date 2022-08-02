$(document).ready(function() {
	//main menu dropdown
	$('nav ul li').on('mouseover', function(event) {
		var x = $(this).attr('id');
		var d = $('#' + x + ' ul').css('display');
		if(d == "none") {
			$("nav ul li ul").slideUp(250);
			$("#" + x + " ul").slideDown(250);
		}
	});
	$("nav ul").on('mouseleave', function() {
		$("nav ul li ul").slideUp(250);
	});
	//end main menu dropdown
	
	//fix fixed menu
	$(window).scroll(function(){
		var scroll = $(window).scrollTop();
		if(scroll >= 95) {
			$('#fixed_nav').css('position', 'fixed');
			$('#fixed_nav').css('marginTop', '-50px');
		}
		else if(scroll <= 80){
			$('#fixed_nav').css('position', 'initial');
			$('#fixed_nav').css('marginTop', '0px');
		}
	});
	
	/* set cookie to determine if msg or notif were clicked*/
	$('#message').on('click', function() {
		document.cookie = "notif_or_msg=msg; path=/";
	});
	$('#notification').on('click', function() {
		document.cookie = "notif_or_msg=notif; path=/";
	});
	
	/* reset country menu cookie of country is clicked from main menu */
	$('#country_main').on('click', function() {
		document.cookie = "country_menu=country_info_block; path=/";
	});
	
	/*set clock */
	var hour_diff = parseInt($('#get_hour_diff').html());
	getTime();
	function getTime() {
		var d = new Date(new Date());
		d = new Date(d.valueOf() + d.getTimezoneOffset() * 60000);
		d.setHours(d.getHours()+(hour_diff));
		var year = d.getFullYear();
		var month = d.getMonth();
		var month_name;
		var date = d.getDate();
		var hour = d.getHours();
		var min = d.getMinutes();
		var sec = d.getSeconds();
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
				month_name = "August";
				break;
			case 8:
				month_name = "September";
				break;
			case 9:
				month_name = "October";
				break;	
			case 10:
				month_name = "November";
				break;	
			case 11:
				month_name = "December";
				break;
		}

		$('#clock').html(hour + ':' + min + ':' + sec + ' ' + month_name + ' ' + date + ' ' + year);
		
		var t = setTimeout(getTime, 1000);
	};
	
	checkNotifMsg();
	function checkNotifMsg() {
		var data = new FormData();
		data.append('action', 'check_noti_msg');
		var url = "../etc/manage_messages";
		function notifMessReply(reply) {
			var temp = JSON.parse(reply);
			
			$("#favicon").attr("href", "../img/icon.png?" + Date.parse(document.lastModified));
			if(temp.notifications > 0 || temp.messages > 0) {
				$("#favicon").attr("href", "../img/icon_notif.png?" + Date.parse(document.lastModified));
			}
			
			if(temp.notifications > 0) {
				$('#noti_count').html(temp.notifications);
				$('#noti_count').fadeIn(500);
			}
			else {
				$('#noti_count').fadeOut(500);
			}
			if(temp.messages > 0) {
				$('#msg_count').html(temp.messages);
				$('#msg_count').fadeIn(500);
			}
			else {
				$('#msg_count').fadeOut(500);
			}
			setTimeout(checkNotifMsg, 15000);
		}
		submitData(data, url, notifMessReply, false);
	}
});
