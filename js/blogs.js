$(document).ready(function() {
	/* create blog */
	$('#create_blog').on('click', function() {
		$('#for_popups_pop').html('');
		$('#for_popups_pop').prepend('<div id="create_blog_div"></div>');
		
		$('#create_blog_div').append('<p id="cbd_head">Create New Blog:</p>');
		$('#create_blog_div').append('<p id="cbd_reply"></p>');
		$('#create_blog_div').append('<input id="cbd_blog_name" placeholder="Blog name" maxlength="20">');
		$('#create_blog_div').append('<textarea id="cbd_blog_desc" maxlength="350" placeholder="Description"></textarea>');	
		$('#create_blog_div').append('<p id="sbd_image">Image(max 500kb):</p>');
		$('#create_blog_div').append('<input type="file" id="cbd_blog_img_upload">');


		$('#create_blog_div').append('<p class="button blue" id="cbd_create">Create</p>');
		$('#create_blog_div').append('<p class="button red" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#cbd_create', function() {
		var name = $('#cbd_blog_name').val();
		var description = $('#cbd_blog_desc').val();
		var form_data = new FormData();
		form_data.append('image', $('#cbd_blog_img_upload')[0].files[0]);
		form_data.append('name', name);
		form_data.append('description', description);
		form_data.append('action', 'create_new_blog');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = reply.split("|");
			if(temp[0] == 'true') {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
				
				$('#create_blog').after(temp[2]);
			}
			else {
				$('#cbd_reply').html(temp[1]);
			}
		}
		submitData(form_data, url, showInfo);
	});

	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
});