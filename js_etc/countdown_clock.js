function countdownClock(e) {
	var clock = $(e).html();
	var clock = clock.split(' ');
	if(typeof clock[1] !== 'undefined') {//with day
		var day = clock[0];
		var d = clock[2].split(':');
	}
	else {//no day
		var d = clock[0].split(':');
	}
	var hour = d[0];
	var min = d[1];
	var sec = d[2];
	
	if(typeof clock[1] !== 'undefined') {
		if(sec == 0 && min == 0 && hour == 0 && day == 0) {//timeout
			return false;
		}
		else if(sec == 0 && min == 0 && hour == 0) {
			day--;
			hour = 23;
		}
	}
	else {
		if(sec == 0 && min == 0 && hour == 0) {//timeout
			return false;
		}
	}
	if(sec == 0) {
		if(min == 0) {
			hour--;
			min = 59;
			if(hour < 10) {
				hour = '0' + hour;
			}
		}
		else if(min > 0) {
			min--;
			if(min < 10) {
				min = '0' + min;
			}
		}
		sec = 60;
	}
	sec--;
	
	if(sec < 10) {
		sec = '0' + sec;
	}
	
	//display
	if(typeof clock[1] !== 'undefined') {
		$(e).html(day + ' days ' + hour + ':' + min + ':' + sec);
	}
	else {
		$(e).html(hour + ':' + min + ':' + sec);
	}
}