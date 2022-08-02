function getUrlParameter(parameter_name) {
    var page_url = decodeURIComponent(window.location.search.substring(1));
    var url_parameters = page_url.split('&');
	var paramenter;
	
    for (var i = 0; i < url_parameters.length; i++) {
        paramenter = url_parameters[i].split('=');

        if (paramenter[0] === parameter_name) {
            return paramenter[1] === undefined ? false : paramenter[1];
        }
    }
	return false;
};

function setNewUserGold() {
	var data = new FormData();
	data.append('action', 'get_user_gold');
	var url = "../etc/user_info";
	function deleteReply(reply) {
        var temp = JSON.parse(reply);
        
        if(temp.success == true) {
            $('#user_gold_amount').html(temp.user_gold + ' <span>Gold</span>');
        }
	}
	submitData(data, url, deleteReply, false);
}

function setNewExperience() {
	var data = new FormData();
	data.append('action', 'get_user_experience');
	var url = "../etc/user_info";
	function deleteReply(reply) {
        var temp = JSON.parse(reply);
        
        if(temp.success == true) {
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
	}
	submitData(data, url, deleteReply, false);
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function numberFormat(number, decimals, dec_point, thousands_sep){
    decimals = decimals || 0;
    number = parseFloat(number);

    if(number != 0) {
        var rounded_number = 
            number < 1 
                ? (1 * ('1e' + decimals) + '').slice(1, decimals) + '' 
                : '';
        rounded_number += Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';

        var numbers_string = decimals ? rounded_number.slice(0, decimals * -1) : rounded_number;
        var decimals_string = decimals ? rounded_number.slice(decimals * -1) : '';
        var formatted_number = "";

        while(numbers_string.length > 3){
            formatted_number += thousands_sep + numbers_string.slice(-3)
            numbers_string = numbers_string.slice(0,-3);
        }
        
        return (number < 0 ? '-' : '') + numbers_string + formatted_number + (decimals_string ? (dec_point + decimals_string) : '');
    }
    var str_zero = '';
    for(var x = 0; x < decimals; x++) {
        str_zero += '0';
    }
    return '0' + dec_point + str_zero;
}

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