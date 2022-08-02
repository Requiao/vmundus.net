$(document).ready(function() {	
	/* edit union */
	$('#edit_union').on('click', function() {
		var union_name = $('#union_name').html();
		var union_abbr = $('#union_abbr').html();
		var union_desc = $('#union_desc').html();
		var union_color = $('#union_color').css('backgroundColor');
		var rgb = union_color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		function hex(x) {
			return ("0" + parseInt(x).toString(16)).slice(-2);
		}
		union_color = "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
		
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="edit_union_info"></div>');
		$('#edit_union_info').append('<p id="msg">Edit information about your union:</p>');
		$('#edit_union_info').append('<input id="new_union_abbr" maxlength="5" value="' + union_abbr + '">');
		$('#edit_union_info').append('<input id="new_union_name" maxlength="20" value="' + union_name + '">');
		$('#edit_union_info').append('<input type="color" id="new_union_color" value="' + union_color + '">');
		$('#edit_union_info').append('<textarea id="new_union_desc" maxlength="350">' + union_desc + '</textarea>');	
		$('#edit_union_info').append('<p id="union_img_info">Image(max 500kb):</p>');
		$('#edit_union_info').append('<input type="file" id="union_img_upload">');
		$('#edit_union_info').append('<p class="button green" id="edit_unon">Edit</p>');
		$('#edit_union_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#edit_unon', function() {
		var name = $('#new_union_name').val();
		var abbr = $('#new_union_abbr').val();
		var color = $('#new_union_color').val();
		var description = $('#new_union_desc').val();
		var form_data = new FormData();
		

		var rgb_array = color.match(/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i);
		var color = "rgb(" + parseInt(rgb_array[1],(16)).toString() + "," +
							 parseInt(rgb_array[2],(16)).toString() + "," +
							 parseInt(rgb_array[3],(16)).toString() + ")";

		form_data.append('image', $('#union_img_upload')[0].files[0]);
		form_data.append('name', name);
		form_data.append('color', color);
		form_data.append('abbr', abbr);
		form_data.append('description', description);
		var url = "../etc/edit_union_info";
		function showInfo(reply) {
			console.log(reply);
			var temp = reply.split("|");
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				
				var info = temp[2].split(", ");
				$('#union_name').html(info[0]);
				$('#union_abbr').html(info[1]);
				$('#union_desc').html(info[2]);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);	
		}
		submitData(form_data, url, showInfo);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$(this).parent().parent().fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$(this).parent().parent().fadeOut(300);
	});
});