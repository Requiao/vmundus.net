	//update battle clock.
	function countupClock(e) {
		var clock = $(e).html();
		var clock = clock.split(' ');
		if(typeof clock[1] !== 'undefined') {//with day
			var day = clock[0];
			var d = clock[2].split(':');
		}
		else {//no day
			var d = clock[0].split(':');
		}
		var hour = parseInt(d[0]);
		var min = parseInt(d[1]);
		var sec = parseInt(d[2]);
		
		if(typeof clock[1] !== 'undefined') {//with day
			if(sec == 59 && min == 59) {
				hour++;
				if(hour == 24) {
					day++;
					hour = 0;
				}
				min = 0;
				sec = 0;
			}
		}
		else {
			if(sec == 59 && min == 59) {//no day
				hour++;
				min = 0;
				sec = 0;
			}
		}
		if(sec == 59) {
			min++;
			sec = 0;
			if(min == 59) {
				hour++;
				min = 0;
			}
		}
		else {
			sec++;
		}
		if(sec < 10) {
			sec = '0' + sec;
		}
		if(min < 10) {
			min = '0' + min;
		}
		if(hour < 10) {
			hour = '0' + hour;
		}
		//display
		if(typeof clock[1] !== 'undefined') {
			$(e).html(day + ' days ' + hour + ':' + min + ':' + sec);
		}
		else {
			$(e).html(hour + ':' + min + ':' + sec);
		}
	}