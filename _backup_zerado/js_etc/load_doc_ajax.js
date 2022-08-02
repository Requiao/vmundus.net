	function loadDoc(url, cfunc, sendData, animate = true) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 1 && animate) {
				$('body').append('<div id="for_loading_div"><i id="loading" class="fa fa-spinner" aria-hidden="true"></i></div>');
				rotateLoad();
			}
			else if (xhttp.readyState == 4 && xhttp.status == 200) {
				clearTimeout(rotate_load_time);
				$('#for_loading_div').remove();
				cfunc(xhttp);
			}
		};
		xhttp.open("POST", url, true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		sendData(xhttp);
	};
	
	
	
	//animate load
	var degree = 0;
	var rotate_load_time;
	function rotateLoad() {
		if(degree >= 360) {
			degree = 0;
		}
		$('#loading').css('-webkit-transform', 'rotate(' + degree + 'deg)',
                        '-moz-transform', 'rotate(' + degree + 'deg)',
                        '-ms-transform', 'rotate(' + degree + 'deg)',
                        '-o-transform', 'rotate(' + degree + 'deg)',
                        'transform', 'rotate(' + degree + 'deg)');
		degree += 3;
		
		rotate_load_time = setTimeout(rotateLoad, 15);
	};