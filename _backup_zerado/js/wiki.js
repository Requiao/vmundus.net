$(document).ready( function() {
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
		t = setTimeout(getTime, 1000);
	};
	
	/*var menu = {"home_page": {"is_selected": false, "block_name": "#home_page_div"},
				"calculator_page": {"is_selected": false, "block_name": "#calculator_page_div"},
				"houses_page": {"is_selected": false, "block_name": "#houses_page_div"},
				"work_page": {"is_selected": false, "block_name": "#work_page_div"},
				"battle_page": {"is_selected": false, "block_name": "#battle_page_div"},
				"job_apply_page": {"is_selected": false, "block_name": "#job_apply_page_div"}
			   };
	
	//check page, if set
	function getUrlParam(name) {
		var results = new RegExp('[\?&]' + name + '=([^]*)').exec(window.location.href);
		if (results == null) {
		   return null;
		}
		else {
		   return results[1] || 0;
		}
	}
	var selected = getUrlParam('page');
	if(menu[selected]) {
		$(menu[selected].block_name).fadeIn(250);
		history.pushState(null, '', '/~petro/vmundus/en/wiki?page=' + selected);
	}
	else {
		$('#home_page_div').fadeIn(250);
		history.pushState(null, '', '/~petro/vmundus/en/wiki?page=home_page');
		selected = 'home_page';
	}
	
	$('.menu_item').on('click', function() {
		var item = $(this).attr('id');
		history.pushState(null, '', '/~petro/vmundus/en/wiki?page=' + item);
		menu[selected].is_selected = false;
		$(menu[selected].block_name).css('display', 'none');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
		menu[item].is_selected = true;
	});*/
});