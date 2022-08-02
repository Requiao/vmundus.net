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
			var am_pm = lang.time.pm;
			hour = hour - 12;
		}
		else {
			var am_pm = lang.time.am;
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
				month_name = lang.month.january;
				break;
			case 1:
				month_name = lang.month.february;
				break;
			case 2:
				month_name = lang.month.march;
				break;
			case 3:
				month_name = lang.month.april;
				break;
			case 4:
				month_name = lang.month.may;
				break;
			case 5:
				month_name = lang.month.june;
				break;
			case 6:
				month_name =lang.month.july;
				break;
			case 7:
				month_name = lang.month.august;
				break;
			case 8:
				month_name = lang.month.september;
				break;
			case 9:
				month_name = lang.month.october;
				break;	
			case 10:
				month_name = lang.month.november;
				break;	
			case 11:
				month_name = lang.month.december;
				break;
		}
		$('#clock').html(hour + ':' + min + ':' + sec + ' ' + am_pm + ' ' + month_name + ' ' + date + ' ' + year);
		t = setTimeout(getTime, 1000);
	};
	
	/* stat */
	var menu = {"sd_work": {"block_name": "#sd_productivity"},
				"sd_fight": {"block_name": "#sd_millitary"},
				"sd_regions": {"block_name": "#sd_regions_stat"}
			   };
			   
	var selected = 'sd_work';
	$('#' + selected).css('boxShadow', '0 0 10px 5px #b2b5b8');

	$('#sd_stat_menu img').on('click', function() {
		switchTabs(this);
	});

	function switchTabs(e) {
		$('#' + selected).css('boxShadow', '0 0 1px 0px #4079ba');
		$(menu[selected].block_name).css('display', 'none');
		var item = $(e).attr('id');
		$(e).css('boxShadow', '0 0 10px 5px #b2b5b8');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
	}

	/* coming soon */
	/*updateComingSoonClock();
	function updateComingSoonClock() {
		var e = $('#coming_in span').get(0); 
		countdownClock(e);
		var t = setTimeout(updateComingSoonClock, 1000);
	};*/
	
	/* slide show */
	var slide_list = ['img/slide_1.png', 'img/slide_2.png', 'img/slide_3.png', 'img/slide_4.png', 'img/slide_5.png',
					  'img/slide_6.png', 'img/slide_7.png', 'img/slide_8.png', 'img/slide_9.png', 'img/slide_10.png',
					  'img/slide_11.png'];
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
	
	//keep track of opened window,reset if popup div if opened different
	var opened_window = '';
	
	/* login */
	let login_modal;
	$('#login_btn').on('click', function() {
		if(login_modal !== "undefined") {
			opened_window = 'login';
			
			login_modal = new ModalBox('375px', '450px');
			login_modal.removeErrorMsgTag();
			login_modal.appendToModal(
				'<div id="login_div">' +
					'<span id="close_login_modal" class="fa fa-times" aria-hidden="true"></span>' +
					'<p id="login_head">' + lang.general.login + '</p>' +
					'<p id="login_reply"></p>' +
					'<p id="resend_hash"></p>' +
					'<p id="log_email_lbl">' + lang.general.email + ':</p>' +
					'<input type="email" id="log_email">' +
					'<p id="log_pass_lbl">' + lang.general.password + ':</p>' +
					'<p id="forgot_pass_lbl">' + lang.general.forgot + 
					' <span class="tooltip">' + lang.general._password + '</span>?</p>' +
					/*'<p id="forgot_pass_tip" class="tooltip_text">' + 
					lang.general.instructions_will_be_sent_to_your_email + '</p>' +*/
					'<input type="password" id="log_pass">' +
					'<input type="checkbox" id="remember_me_checkbox" value="true">' +
					'<label id="remember_me_lbl" for="remember_me_checkbox">Remember me</label>' +
					'<p id="login_button">' + lang.general.login + '</p>' +
				'</div>'
			);
		}
		login_modal.displayModal();
	});

	$('body').on('click', '#close_login_modal', function() {
		login_modal.closeModal();
	});
	
	//forgot pass
	$('body').on('click', '#forgot_pass_lbl span', function() {
		var email = $('#log_email').val().trim();
		if(!testEmail(email)) {
			$('#login_reply').html(lang.general.incorect_email);
		}
		else {
			var data = new FormData();
			data.append('email', email);
			data.append('action', 'forgot_pass');
			var url = "index_etc/process_index";
			function forgotPassReply(reply) {
				var temp = JSON.parse(reply);
				let modal = new ModalBox();
				modal.setHeading(temp.msg_head);
				modal.appendToModal(temp.msg);
				modal.appendCancelButton(lang.general.ok);
				modal.displayModal();
			}
			submitData(data, url, forgotPassReply);
		}
	});
	
	//login send data
	$('body').on('click', '#login_button', function() {
		loginSubmit();
	});
	
	function loginSubmit() {
		$('#resend_hash').empty();
		$('#login_reply').empty();
		
		var verified = false;
		var email = $('#log_email').val().trim();
		var pass = $('#log_pass').val().trim();
		var remember_me = $('#remember_me_checkbox')[0].checked;

		if(!testEmail(email)) {
			$('#login_reply').html(lang.general.invalid_email);
		}
		else {
			verified = true;
		}
		
		if(verified) {
			var data = new FormData();
			data.append('email', email);
			data.append('pass', pass);
			data.append('remember_me', remember_me);

			var url = "etc/login";
			function loginReply(reply) {
				console.log(reply);
				var temp = JSON.parse(reply);
				console.log(temp.error);
				if (temp.success === true) {
					window.location = "en/index";
				}
				else {
					if(temp.hash == true) {
						$('#resend_hash').html(lang.general.resend_confirmation_link);
					}
					$('#login_reply').html(temp.error);
				}
			}
			submitData(data, url, loginReply);
		}
	};
	
	//resend hash
	$('body').on('click', '#resend_hash', function() {
		$('#login_reply').empty();
		
		var verified = false;
		if($('#log_email').get(0)) {
			var email = $('#log_email').val().trim();
		}
		else if($('#email').get(0)) {
			var email = $('#email').val().trim();
		}
		var regex_email = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;
		
		if(!testEmail(email)) {
			$('#login_reply').html(lang.general.invalid_email);
		}
		else {
			verified = true;
		}
		
		if(verified) {
			var data = new FormData();
			data.append('email', email);
			data.append('action', 'resend_hash');
			var url = "index_etc/process_index";
			function resendHashReply(reply) {
				if($('#log_email').get(0)) {
					$('#login_reply').html(reply);
				}
				else if($('#email').get(0)) {
					$('#register_reply').html(reply);
				}
			}
			submitData(data, url, resendHashReply);
		}
	});
	
	/* reset password */
	$(document).ready(function() {
		if($('#reset_pass').get(0)) {
			var reset_hash = $('#reset_pass').html();
			if(opened_window != 'pass_reset') {
				opened_window = 'pass_reset';
				$('#popup_div').empty();
				$('#popup_div').append('<div id="rest_pass_div">' +
									   '<span id="close_popup_div" class="fa fa-times" aria-hidden="true"></span>' +
									   '<p id="rpd_head">' + lang.general.reset_passsword + '</p>' +
									   '<p id="rpd_reply"></p>' +
									   '<p id="rpd_email_lbl">' + lang.general.email + ':</p>' +
									   '<input type="email" id="rpd_email">' +
									   '<p class="tooltip_text" id="rpd_pass_tip"></p>' +
									   '<p id="rpd_pass_lbl">' + lang.general.new_password + ':</p>' +
									   '<input type="password" id="rpd_pass">' +
									   '<p id="rpt_rpd_pass_lbl">' + lang.general.repeat_password + ':</p>' +
									   '<input type="password" id="rpt_rpd_pass">' +
									   '<p id="rst_pas_button">' + lang.general.reset + '</p>' +
									   '<p hidden>' + reset_hash + '</p>' +
									   '</div>');
			}
			$('#popup_div').fadeIn('300');
		}
	});
	
	//submit reset password
	$('body').on('click', '#rst_pas_button', function() {
		passwordResetSubmit();
	});
	
	function passwordResetSubmit() {
		$('#rpd_reply').empty();
		$('#rpd_pass_tip').css('display', 'none');
		$('#rpd_pass_tip').empty();
		
		var verified = false;
		var email = $('#rpd_email').val().trim();
		var pass = $('#rpd_pass').val().trim();
		var rpt_pass = $('#rpt_rpd_pass').val().trim();
		var reset_password = $('#reset_pass').html().trim();
		
		if(!testEmail(email)) {
			$('#rpd_reply').html(lang.general.invalid_email);
		}
		else if(pass.length < 5) {
			$('#rpd_pass_tip').html(lang.general.password_is_too_short);
			$('#rpd_pass_tip').fadeIn(250)
		}
		else if(pass !== rpt_pass) {
			$('#rpd_pass_tip').html(lang.general.passwords_dont_match);
			$('#rpd_pass_tip').fadeIn(250)
		}
		else {
			verified = true;
		}
		
		if(verified) {
			var data = new FormData();
			data.append('email', email);
			data.append('pass', pass);
			data.append('rpt_pass', rpt_pass);
			data.append('reset_password', reset_password);
			data.append('action', 'reset_pass');
			var url = "index_etc/process_index";
			function resetPassReply(reply) {
				$('#rpd_reply').html(reply);
			}
			submitData(data, url, resetPassReply);
		}
	};
	
	/* register */
	//diplay register form when register button is clicked
	$('#register_btn').on('click', function() {
		var url = "index_etc/process_index";
		var data = new FormData();
		data.append('action', 'get_countries');
		function generateRegisterDialog(reply) {
			var temp = JSON.parse(reply);
			var country_list = '';
			for(var x = 0; x < temp.length; x++) {
				//var t = temp[x].split(", ");
				country_list += '<div class="country" id="' + temp[x].country_id + '"><img src="country_flags/' + temp[x].flag + '">' +
								'<p>' + temp[x].country_name + '</p></div>';
			}
			if(opened_window != 'register') {
				opened_window = 'register';
				$('#popup_div').empty();
				$('#popup_div').append('<div id="register_div">' +
									   '<span id="close_popup_div" class="fa fa-times" aria-hidden="true"></span>' +
									   '<p id="register_head">' + lang.general.register + '</p>' +
									   '<p id="register_reply"></p>' +
									   '<p id="resend_hash"></p>' +
									   '<p id="country_lbl">' + lang.general.country + ':</p>' +
									   '<p id="country_reply" class="tooltip_text"></p>' +
									   '<div id="country_list">' +
									   '<div id="country">' +
									   '</div>' +
									   '<p id="get_country_id" hidden=""></p>' +
									   '<span class="glyphicon glyphicon-menu-down"></span>' +
									   '</div>' +
									   '<div id="countries_div">' +
									   country_list +
									   '</div>' +
									   '<p id="user_lbl">' + lang.general.username + ':</p>' +
									   '<p id="reply_name" class="tooltip_text"></p>' +
									   '<input type="text" id="user_name">' +
									   '<p id="email_lbl">' + lang.general.email + ':</p>' +
									   '<p id="reply_email" class="tooltip_text"></p>' +
									   '<input type="email" id="email">' +
									   '<p id="psw_lbl">' + lang.general.password + ':</p>' +
									   '<p id="pass_reply" class="tooltip_text"></p>' +
									   '<input type="password" id="pass">' +
									   '<p id="rpt_psw_lbl">' + lang.general.repeat_password + ':</p>' +
									   '<p id="rpt_pass_reply" class="tooltip_text"></p>' +
									   '<input type="password" id="rpt_pass">' +
									   '<p id="rd_terms_privacy">' + lang.general.by_creating_an_account_you_agree_to_our + 
									   '<a href="en/terms_of_service" target="_blank">' + lang.general.terms + '</a> & ' +
									   '<a href="en/privacy_policy" target="_blank">' + lang.general.privacy + '.</a></p>' +
									   '<p id="register_button">' + lang.general.register + '</p>' +
									   '</div>');
			}
			$('#popup_div').fadeIn('300');
		}
		submitData(data, url, generateRegisterDialog);
	});
	
	//display country list
	$('#popup_div').on('click', '#country_list', function() {
		clearRegisterTips();
		$('#countries_div').slideToggle();
	});
	$('#popup_div').on('mouseleave', '#countries_div', function() {
		$('#countries_div').slideUp();
	});
	
	//select country
	$('#popup_div').on('click', '.country', function() {
		clearRegisterTips();
		$('#country_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var country = $(this).html();
		var country_id = $(this).attr('id');
		$('#country_list').append('<div id="country">' + country + '</div>');
		$('#country_list').append('<p id="get_country_id" hidden>' + country_id + '</p>');
		$('#countries_div').slideUp(250);
	});
	
	$('#popup_div').on('click', '#register_button', function() {
		registerSubmit();	
	});
	
	function registerSubmit() {
		clearRegisterTips();
		var country_id = $('#get_country_id').html();
		var name = $('#user_name').val().trim();
		var email = $('#email').val().trim();
		var pass = $('#pass').val().trim();
		var rpt_pass = $('#rpt_pass').val().trim();
		if($('#referer_id').get(0)) {
			var referer = $('#referer_id').html();
		}
		else {
			var referer = 0;
		}
		
		var verified = true;
		if(country_id.length == 0) {
			$("#country_reply").html(lang.general.select_country);
			$("#country_reply").fadeIn();
			var verified = false;
		}
		if(name.length < 3) {
			$("#reply_name").html(lang.general.minimum_name_length_is_3_characters);
			$("#reply_name").fadeIn();
			var verified = false;
		}
		else if(name.length > 15) {
			$("#reply_name").html(lang.general.maximum_name_length_is_15_characters);
			$("#reply_name").fadeIn();
			var verified = false;
		}
		if(!testEmail(email)) {
			$('#reply_email').html(lang.general.invalid_email);
			$("#reply_email").fadeIn();
			var verified = false;
		}
		if(pass.length < 5) {
			$('#pass_reply').html(lang.general.minimum_password_ength_is_5_characters);
			$("#pass_reply").fadeIn();
			var verified = false;
		}
		if(pass != rpt_pass) {
			$('#rpt_pass_reply').html(lang.general.passwords_dont_match);
			$("#rpt_pass_reply").fadeIn();
			var verified = false;
		}
		
		if(verified) {
			var data = new FormData();
			data.append('country', country_id);
			data.append('name', name);
			data.append('email', email);
			data.append('pass', pass);
			data.append('rpt_pass', rpt_pass);
			data.append('referer', referer);
			data.append('hour', hour);
			var url = "etc/register";
			var time = new Date();
			var hour = time.getHours();
			function registerReport(reply) {
				var temp = JSON.parse(reply);
				if(temp.success == false) {
					$('#register_reply').html(temp.error);
					$("#register_reply").fadeIn();
					
					if(temp.send_hash === true) {
						$('#resend_hash').html(lang.general.resend_confirmation_link);
					}
				}
				else if(temp.success === true) {//registered
					$('#popup_div').empty();
					$('#popup_div').append('<div id="registered_div">' +
										   '<span class="glyphicon glyphicon-ok"></span>' +
										   '<p id="msg_head">' + temp.msg_head + '</p>' +
										   '<p id="msg">' + temp.msg + '</p>' +
										   '<p id="ok_btn">Ok</p>' +
										   '</div>');
				}
			}
			submitData(data, url, registerReport);
		}
	};
	
	$('#popup_div').on('click', '#ok_btn', function() {
		$(this).parent().parent().fadeOut(300);
	});
	
	function clearRegisterTips() {
		$('#rpd_pass_tip').fadeOut(250);
		$("#country_reply").fadeOut(250);
		$("#reply_name").fadeOut(250);
		$("#reply_email").fadeOut(250);
		$("#pass_reply").fadeOut(250);
		$("#rpt_pass_reply").fadeOut(250);
		$("#register_reply").fadeOut(250);
	}
	
	//close popup
	$('#popup_div').on('click', '#close_popup_div ', function() {
		$('#popup_div').fadeOut(250);
	});
	
	// When the user clicks anywhere outside of the popup_div, close it
	$('html').on('click', function(event) {
		var modal = $("#popup_div").get(0);
		if(event.target == modal) {
			$(modal).fadeOut(250);
		}
		if(event.target == $("#rest_pass_div").get(0)) {
			$('#rpd_pass_tip').fadeOut(250);
		}
		if(event.target == $("#register_div").get(0)) {
			clearRegisterTips();
		}
		$('input').each(function() {
			if(event.target == this) {
				clearRegisterTips();
			}
		});
	});
	
	//login/register on enter
	$('html').keydown(function (e) {
		if (e.keyCode == 13) {
			if($("#register_div").get(0)) {
				registerSubmit();
			}
			else if($("#login_div").get(0)) {
				loginSubmit();
			}
			else if($("#rest_pass_div").get(0)) {
				passwordResetSubmit();
			}
		}
	});
});