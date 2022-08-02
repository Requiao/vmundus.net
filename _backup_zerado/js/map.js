$(document).ready(function(){
	
	//show/hide region names 
	$('#show_names').on('click', function(e) {
		if($('text').css('display') == "none") {
			$('text').css('display', 'block');
		}
		else {
			$('text').css('display', 'none');
		}
	});
	
	//move svg
	var flag = false;
	var x = 0;
	var y = 0;
	
	//count is for zoom //count number of zoom(allow only 9 zooms to prevent map disformation)
	var count = 9;
	var MAX_ZOOMS = 9;
	
	$('#svgTag').on('mousedown', function(e) {
		$('#svgTag').css('cursor', '-webkit-grabbing');
		$('#svgTag').css('cursor', 'grabbing');
		var left = $('#svgTag').css('left').replace("px", "");
		var top = $('#svgTag').css('top').replace("px", "");
		x = e.clientX - left;
		y = e.clientY - top;
		flag = true;
	});
	
	$('#svgTag').on('mousemove', function(e) {
		flag?moveMap(e):'';
	});

	function moveMap(e, move_by_click = false, up_down = 0, left_right = 0) {
		if(!move_by_click) {
			var diffX = parseFloat(e.clientX - x);
			var diffY = parseFloat(e.clientY - y);
		}
		else {
			var left = $('#svgTag').css('left').replace("px", "");
			var top = $('#svgTag').css('top').replace("px", "");
			var diffX = left - left_right;
			var diffY = top - up_down;
		}
		
		//prevent from going out of bounds
		//important to get svg dimensions after zooming
		var doc_width = $(window).width();
		var doc_height = $(window).height();
		var svg_width = (parseFloat($('#svgTag').css('width').replace("px", "")) * -1) + doc_width;
		var svg_height = (parseFloat($('#svgTag').css('height').replace("px", "")) * -1) + doc_height;
		
		if(diffX < 0 && diffX > svg_width) {
			$('#svgTag').css('left', diffX);
			setCookie('svgTagLeft', diffX, -1);
			setCookie('svgTagLeft', diffX, 90);
		}
		else if (diffX < svg_width){
			$('#svgTag').css('left', svg_width);
		}
		
		if(diffY < 0 && diffY > svg_height) {
			$('#svgTag').css('top', diffY);
			setCookie('svgTagTop', diffY, -1);
			setCookie('svgTagTop', diffY, 90);
		}
		else if(diffY < svg_height) {
			$('#svgTag').css('top', svg_height);
		}
		
		//remember last zoom
		var zoomCount = MAX_ZOOMS - count;
		setCookie('zoomIndex', zoomCount, -1);
		setCookie('zoomIndex', zoomCount, 90);
	}
	
	$('#svgTag').on('mouseleave', function(e) {
		flag = false;
		$('#svgTag').css('cursor', 'auto');
	});
	
	$('#svgTag').on('mouseup', function(e) {
		flag = false;
		$('#svgTag').css('cursor', 'auto');
	});
	
	//move with click
	var step = 20;
	$('#map_up').on('mousedown touchstart', function(e) {
        moveMapOnClick(e, move_by_click = true, up_down = -step, left_right = 0);
    });
	
	$('#map_down').on('mousedown touchstart', function(e) {
		moveMapOnClick(e, move_by_click = true, up_down = step, left_right = 0);
    });
	
	$('#map_left').on('mousedown touchstart', function(e) {
        moveMapOnClick(e, move_by_click = true, up_down = 0, left_right = -step);
    });
	
	$('#map_right').on('mousedown touchstart', function(e) {
        moveMapOnClick(e, move_by_click = true, up_down = 0, left_right = step);
    });
	
	var t_moveMapOnClick;
	function moveMapOnClick(e, move_by_click, up_down, left_right) {
		moveMap(e, move_by_click, up_down, left_right);
		
		t_moveMapOnClick = setTimeout(function() {moveMapOnClick(e, move_by_click, up_down, left_right)}, 5);
	}

	$('#map_controls p').on('mouseup touchend', function(e) {
		clearTimeout(t_moveMapOnClick);
    });
	
	//zoom functions
	function zoomIn() {
		if(count < MAX_ZOOMS && count >= 0) {
			count++;
			var svgSize = document.getElementById("svgTag");
			var height = svgSize.style.height;
			var width = svgSize.style.width;
			width = width.replace("px", "");
			height = height.replace("px", "");
			height = height * 1.2;
			width = width * 1.2;
			svgSize.style.height = height + "px";
			svgSize.style.width = width + "px";
		
			//center svg when zooming
			var left = $('#svgTag').css('left');
			var top = $('#svgTag').css('top');
			left = left.replace("px", "");
			top = top.replace("px", "")
			left = (left * 1.2) - 100;
			top = (top * 1.2) - 100;
			$('#svgTag').css('left', left);
			$('#svgTag').css('top', top);
		
			var zoom = "zoomIn";
			zoomSvg(zoom);
		}
	}

	function zoomOut() {
		if(count <= MAX_ZOOMS && count > 0) {
			count--;
			var svgSize = document.getElementById("svgTag");
			var height = svgSize.style.height;
			var width = svgSize.style.width;
			width = width.replace("px", "");
			height = height.replace("px", "");
			height = height / 1.2;
			width = width / 1.2;
			svgSize.style.height = height + "px";
			svgSize.style.width = width + "px";
		
			//center svg when zooming
			var left = $('#svgTag').css('left');
			var top = $('#svgTag').css('top');
			left = left.replace("px", "");
			top = top.replace("px", "")
			if(left <= -100) {
				left = (left / 1.2) + 100;
			}
			if(top <= -100) {
				top = (top / 1.2) + 100;
			}
			$('#svgTag').css('left', left);
			$('#svgTag').css('top', top);
		
			var zoom = "zoomOut";
			zoomSvg(zoom);
			
			//normalize position
			//important to get svg dimensions after zooming
			var doc_width = $(window).width();
			var doc_height = $(window).height();
			var svg_width = (parseFloat($('#svgTag').css('width').replace("px", "")) * -1) + doc_width;
			var svg_height = (parseFloat($('#svgTag').css('height').replace("px", "")) * -1) + doc_height;
			if(left < svg_width) {
				$('#svgTag').css('left', svg_width);
			}
			if(top < svg_height) {
				$('#svgTag').css('top', svg_height);
			}
		}
	}
	
	function zoomSvg(zoom) {
		var zoom = zoom;
		var poly_id = 0;
		while(poly_id <= 1485) {
			//change polygon and text coords for regions with multiple polygons
			if($('#' + poly_id).length != 1) {
				var s = 0;
				var special = "s" + s;
				while($('#' + poly_id + '_' + s).length == 1){
					special = poly_id + "_" + s;
					
					var points = document.getElementById(special).getAttribute('points');
					points = points.replace(/ ?\r?\n|\r/g, " ");
					points = points.trim();
					points = points.split(" ");
					for(p = 0; p < points.length; p++) {
						var a = points[p].split(",");
						if ( zoom == "zoomIn") {
							a[0] = (a[0] * 1.2).toFixed(4);
							a[1] = (a[1] * 1.2).toFixed(4);
						}
						else if (zoom == "zoomOut") {
							a[0] = (a[0] / 1.2).toFixed(4);
							a[1] = (a[1] / 1.2).toFixed(4);
						}
						points[p] = a.join(",");
					}
					points = points.join(" ");
					document.getElementById(special).setAttribute('points', points);
					s++;
				}
				s--;
				special = poly_id + "_" + s;
				//change text coords
				var text = document.getElementById(special).nextElementSibling;
				var textx = text.getAttribute('x');
				var texty = text.getAttribute('y');
				if ( zoom == "zoomIn") {
					textx = textx * 1.2;
					texty = texty * 1.2;
				}
				else if (zoom == "zoomOut") {
					textx = textx / 1.2;
					texty = texty / 1.2;
				}
				text.setAttribute('x', textx);
				text.setAttribute('y', texty);
			}
			else {
				//change text coords
				var text = document.getElementById(poly_id).nextElementSibling;
				var textx = text.getAttribute('x');
				var texty = text.getAttribute('y');
				if ( zoom == "zoomIn") {
					textx = textx * 1.2;
					texty = texty * 1.2;
				}
				else if (zoom == "zoomOut") {
					textx = textx / 1.2;
					texty = texty / 1.2;
				}
				text.setAttribute('x', textx);
				text.setAttribute('y', texty);
				
				//change polygon coords
				var points = document.getElementById(poly_id).getAttribute('points');
				points = points.replace(/ ?\r?\n|\r/g, " ");
				points = points.trim();
				points = points.split(" ");
				for(p = 0; p < points.length; p++) {
					var a = points[p].split(",");
					if ( zoom == "zoomIn") {
						a[0] = (a[0] * 1.2).toFixed(4);
						a[1] = (a[1] * 1.2).toFixed(4);
					}
					else if (zoom == "zoomOut") {
						a[0] = (a[0] / 1.2).toFixed(4);
						a[1] = (a[1] / 1.2).toFixed(4);
					}
					points[p] = a.join(",");
				}
				points = points.join(" ");
				document.getElementById(poly_id).setAttribute('points', points);
			}
			poly_id++;
		}
	}
	
	//zoom when mouse click
	$('#zoom_in').on('click', function(e){
        zoomIn();
    });
	$('#zoom_out').on('click', function(e){
        zoomOut();
    });
	
	//zoom with mouse wheel
	$('#svgTag').on('wheel', function(e){
        if(e.originalEvent.wheelDelta > 0) {
            zoomIn();
        }
        else {
            zoomOut();
        }
    });
	
	//call zoom functions if zoomIndex cookie is set
	var zoomIndex = getCookie("zoomIndex");
	if (zoomIndex != "") {
		for(var x = 0; x < zoomIndex; x++) {
			zoomOut();
		}
	}
	
	//show map location from where user left
	var svgTagLeft = getCookie("svgTagLeft");
	var svgTagTop = getCookie("svgTagTop");
	if (svgTagLeft != "") {
		$('#svgTag').css('left', svgTagLeft);
	}
	if (svgTagTop != "") {
		$('#svgTag').css('top', svgTagTop);
	}
	
	// set/get cookies
	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires;
	}
	
	function getCookie(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length,c.length);
			}
		}
		return "";
	}
	
	/* pin. hide/show info and neighbor divs */
	var selected_tab = 'info';
	$('#pin').on('click', function(e) {
		var tabs = $('#tabs').css('left');
		tabs = tabs.replace("px", "");
		console.log(tabs);
		if(tabs == 300) {
			$("#region_info").animate({width: "0px"}, () => {
				$("#ri_blocks").css('display', 'none')
			});
			$("#pin span").css('-ms-transform', ' rotate(0deg)');
			$("#pin span").css('-webkit-transform', ' rotate(0deg)');
			$("#pin span").css('transform', ' rotate(0deg)');
		}
		else if(tabs == 0) {
			$("#region_info").animate({width: "300px"}, () => {
				$("#ri_blocks").css('display', 'block')
			});
			$("#pin span").css('-ms-transform', ' rotate(43deg)');
			$("#pin span").css('-webkit-transform', ' rotate(43deg)');
			$("#pin span").css('transform', ' rotate(43deg)');
		}
	});
	
	var region_info_selected = undefined;
	$('#neighbor').on('click', function(e) {
		selected_tab = 'neighbor';
		var tabs = $('#tabs').css('left');
		tabs = tabs.replace("px", "");
		if(tabs != 300) {
			$('.' + region_info_selected + ' .region_neighbors').css('display', 'block');
			$('.' + region_info_selected + ' .region_name_resources').css('display', 'none');
			$("#pin span").css('-ms-transform', ' rotate(43deg)');
			$("#pin span").css('-webkit-transform', ' rotate(43deg)');
			$("#pin span").css('transform', ' rotate(43deg)');
			$("#region_info").animate({width: "300px"}, () => {
				$("#ri_blocks").css('display', 'block')
			});
		}
		$('.' + region_info_selected + ' .region_neighbors').css('display', 'block');
		$('.' + region_info_selected + ' .region_name_resources').css('display', 'none');
	});
	$('#info').on('click', function(e) {
		selected_tab = 'info';
		var tabs = $('#tabs').css('left');
		tabs = tabs.replace("px", "");
		if(tabs != 300) {
			$("#pin span").css('-ms-transform', ' rotate(43deg)');
			$("#pin span").css('-webkit-transform', ' rotate(43deg)');
			$("#pin span").css('transform', ' rotate(43deg)');
			$("#region_info").animate({width: "300px"}, () => {
				$("#ri_blocks").css('display', 'block')
			});
		}
		$('.' + region_info_selected + ' .region_neighbors').css('display', 'none');
		$('.' + region_info_selected + ' .region_name_resources').css('display', 'block');
	});
	
	
	/* get info about region */
	$('polygon').on('click', function() {
		var region_id = $(this).attr('id');
		$('polygon').css('opacity', '1');
		if(region_id == 0) {//if clicked on water, exit
			return;
		}
		
		if(isNaN(region_id)) {//for regions with multiple poligons
			var temp = region_id.split("_");
			var region_id = temp[0];
			var s = 0;
			var s_region_id = '#' + region_id + '_' + s;
			while($(s_region_id).length == 1) {
				$(s_region_id).css('opacity', '0.8');
				s++;
				s_region_id = '#' + region_id + '_' + s;
			}
		}
		else {
			$(this).css('opacity', '0.8');
		}
		
		region_info_selected = region_id;

		//display selected_tab
		if(selected_tab == 'neighbor') {
			$('.region_neighbors').css('display', 'block');
			$('.region_name_resources').css('display', 'none');
		}
		else if(selected_tab == 'info') {
			$('.region_neighbors').css('display', 'none');
			$('.region_name_resources').css('display', 'block');
		}
		
		//hide previous region info
		$('.region_details').css('display', 'none');
		
		
		//Display region info
		$("." + region_id).css('display', 'block');
		
		
		//Display region neighbors
		$('.' + region_id + ' .region_neighbors .neighbor_info .neighbor_id').each( function() {
			neighbor_id = $(this).html();
			if($('#' + neighbor_id).length == 0) {//for regions with multiple poligons
				var s = 0;
				var s_neighbor_id = '#' + neighbor_id + '_' + s;
				while($(s_neighbor_id).length == 1) {
					$(s_neighbor_id).css('opacity', '0.5');
					s++;
					s_neighbor_id = '#' + neighbor_id + '_' + s;
				}
			}
			else if(neighbor_id != 0) {
				$('#' + neighbor_id).css('opacity', '0.5');
			}
		});
	});
});