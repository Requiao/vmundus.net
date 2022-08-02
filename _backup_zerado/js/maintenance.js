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
			case 11:
				month_name = "December";
				break;
		}
		$('#clock').html(hour + ':' + min + ':' + sec + ' ' + am_pm + ' ' + month_name + ' ' + date + ' ' + year);
		t = setTimeout(getTime, 1000);
	};
	
	/* language */
	$('.change_lang').on('click', function() {
		var lang_code = $(this).next().html();
		var data = new FormData();
		data.append('lang_code', lang_code);
		data.append('action', 'change_language');
		var url = "index_etc/process_index";
		function dateReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				location.reload();
			}
		}
		submitData(data, url, dateReply);
	});
	
	/* slide show */
	var slide_list = ['img/slide_1.png', 'img/slide_2.png', 'img/slide_3.png', 'img/slide_4.png', 'img/slide_5.png',
					  'img/slide_6.png', 'img/slide_7.png', 'img/slide_8.png'];
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
	
	
});