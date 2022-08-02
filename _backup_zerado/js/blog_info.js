$(document).ready(function() {
	
	/* scroll down to the selected post */
	var post_id = getUrlParameter('post_id');
	if(post_id) {
		$("html").animate({scrollTop: $("#post_" + post_id).offset().top}, 500);
	}
	
	/* write post */
	$('#write_post').on('click', function(e) {
		CKEDITOR.replace("post_manage_div");
		$('#post_write_div').slideDown(250);
		$('#write_post').css('display', 'none');
		$('#edit_post').css('display', 'none');
		$('#cancel_write_post').css('display', 'inherit');
		$('#publish_post').css('display', 'inherit');
	});
	
	$('#cancel_write_post').on('click', function(e) {
		$('#post_write_div').slideUp(250);
		CKEDITOR.instances.post_manage_div.destroy();
		$('#write_post').css('display', 'inherit');
		$('#cancel_write_post').css('display', 'none');
	});
	
	/* publish post */
	$('#publish_post').on('click', function(e) {
		var post = CKEDITOR.instances.post_manage_div.getData();
		var blog_id = $('#blog_id').html();
		var title = $('#post_head_input').val();
		var form_data = new FormData();
		form_data.append('post', post);
		form_data.append('blog_id', blog_id);
		form_data.append('title', title);
		form_data.append('action', 'publish_post');
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#post_write_div').slideUp(250);
				CKEDITOR.instances.post_manage_div.destroy();
				$('#write_post').css('display', 'inherit');
				$('#cancel_write_post').css('display', 'none');
				
				temp[4] = temp[4].replace(/\\n/g, '');
				temp[4] = $("<textarea/>").html(temp[4]).text();
				var blog_image = $('#blog_img').attr('src');
				var blog_id = $('#blog_id').html();
				var blog_name = $('#blog_name').html();
				$('#posts_container').prepend('<div class="post_details" id="post_' + temp[2] + '">' +
									  '<p class="get_post_id" hidden>' + temp[2] + '</p>' +
									  '<img class="post_blog_img" src="' + blog_image + '">' +
									  '<a class="blog_name_link" href="blog_info?blog_id=' + blog_id + 
									  '">' + blog_name + '</i></a>' +
									  '<p class="post_date">' + temp[5] + '</p>' +
									  '<p class="delete_post">Delete Post</p>' +
									  '<p class="edit_post">Edit Post</p>' +
									  '<a class="post_title" href="blog_info?blog_id=' + blog_id + 
									  '&post_id=' + temp[2] + '">' + temp[3] + '</a>' +
									  '<div class="post_div">' + temp[4] + '</div>' +
									  '<div class="pd_blog_author">' +
									  '<p>Written by:</p>' +
									  '<a class="pd_blogger" href="user_profile?id=' + temp[5] + '">' + 
									   $('#user_name').html() + '</a>' +
									  '</div>' +
									  '<div class="post_views_likes_div">' +
									  '<p class="likes">' +
									  '<i class="fa fa-thumbs-o-up" aria-hidden="true"></i> 0</p>' +
									  '<p class="views">' +
									  '<i class="fa fa-eye" aria-hidden="true"></i> 0</p>' +
									  '</div>' +
									  '<div class="comments_div">' +
									  '</div>' +
									  '<textarea class="comment_entry" maxlength="500"></textarea>' +
									  '<p class="error_comment_reply"></p>' +
									  '<span class="send_comment glyphicon glyphicon-send"></span>' +
									  '</div>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(form_data, url, showInfo);
	});
	
	
	/* delete post */
	var post_id = undefined;
	$('#posts_container').on('click', '.delete_post', function(e) {
		post_id = $(this).parent().children('.get_post_id').html();
		$('#for_popups_pop').empty();
		$('#for_popups_pop').append('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to delete this post?</p>');
		$('#reply_info').append('<p class="button red" id="yes_delete_post">Delete</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#yes_delete_post', function(e) {
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('action', 'delete_post');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$('#post_' + post_id).slideUp();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(form_data, url, showInfo);
	});
	
	/* edit post */
	$('#posts_container').on('click', '.edit_post', function(e) {
		post_id = $(this).parent().children('.get_post_id').html();
		if (CKEDITOR.instances.post_manage_div) {
			CKEDITOR.instances.post_manage_div.destroy();
		}
		CKEDITOR.replace("post_manage_div");
		var edit_post = $('#post_' + post_id + ' .post_div').html();
		var post_title = $('#post_' + post_id + ' .post_title').html();
		$('#post_manage_div').val(edit_post);
		$('#post_head_input').val(post_title);
		$('#post_write_div').slideDown(250);
		$('#write_post').css('display', 'none');
		$('#publish_post').css('display', 'none');
		$('#cancel_write_post').css('display', 'inherit');
		$('#edit_post').css('display', 'inherit');
		
		$("html, body").animate({scrollTop: $('#post_write_div').offset().top - 50 }, 250);
	});
	
	$('#edit_post').on('click', function(e) {
		var post = CKEDITOR.instances.post_manage_div.getData();
		var title = $('#post_head_input').val();
		var form_data = new FormData();

		form_data.append('post', post);
		form_data.append('post_id', post_id);
		form_data.append('title', title);
		form_data.append('action', 'edit_post');
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#post_write_div').slideUp(250);
				CKEDITOR.instances.post_manage_div.destroy();
				$('#write_post').css('display', 'inherit');
				$('#cancel_write_post').css('display', 'none');
				
				temp.post= temp.post.replace(/\\n/g, '');
				temp.post = $("<textarea/>").html(temp.post).text();
				
				$('#post_' + post_id + ' .post_title').html(temp.title);
				$('#post_' + post_id + ' .post_div').html(temp.post);
				$('#post_' + post_id + ' .edit_post_date').html(temp.edit_post_date);
				
				$("html, body").delay(250).animate({scrollTop: $('#post_' + post_id).offset().top - 900 }, 250);
				
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(form_data, url, showInfo);
	});
	
	/* subscribe */
	$('#subscribe').on('click', function(e) {
		var blog_id = $('#blog_id').html();
		var form_data = new FormData();
		form_data.append('blog_id', blog_id);
		form_data.append('action', 'subscribe');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');

				$('#unsubscribe').css('display', 'inherit');
				$('#subscribe').css('display', 'none');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(form_data, url, showInfo);
	});
	
	/* unsubscribe */
	$('#unsubscribe').on('click', function(e) {
		var blog_id = $('#blog_id').html();
		var form_data = new FormData();
		form_data.append('blog_id', blog_id);
		form_data.append('action', 'unsubscribe');
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp[0] == 1) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');

				$('#unsubscribe').css('display', 'none');
				$('#subscribe').css('display', 'inherit');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(form_data, url, showInfo);
	});
	
	/* edit blog */
	$('#edit_blog').on('click', function() {
		var blog_name = $('#blog_name').html();
		var blog_desc = $('#blog_desc').html();
		
		$('#for_popups_pop').empty();
		$('#for_popups_pop').append('<div id="edit_blog_div"></div>');
		
		$('#edit_blog_div').append('<p id="ebd_head">Edit Blog:</p>');
		$('#edit_blog_div').append('<p id="ebd_reply"></p>');
		$('#edit_blog_div').append('<input id="ebd_blog_name" placeholder="Blog name" maxlength="20" value="' + blog_name + '">');
		$('#edit_blog_div').append('<textarea id="ebd_blog_desc" maxlength="350" placeholder="Description">' + blog_desc + '</textarea>');	
		$('#edit_blog_div').append('<p id="ebd_image">Image(max 500kb):</p>');
		$('#edit_blog_div').append('<input type="file" id="ebd_blog_img_upload">');


		$('#edit_blog_div').append('<p class="button blue" id="ebd_edit">Edit</p>');
		$('#edit_blog_div').append('<p class="button red" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#ebd_edit', function() {
		var blog_id = $('#blog_id').html();
		var name = $('#ebd_blog_name').val();
		var description = $('#ebd_blog_desc').val();
		var form_data = new FormData();
		form_data.append('image', $('#ebd_blog_img_upload')[0].files[0]);
		form_data.append('blog_id', blog_id);
		form_data.append('name', name);
		form_data.append('description', description);
		form_data.append('action', 'edit_blog');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			console.log(reply);
			var temp = reply.split('|');
			if(temp[0] == 'true') {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				
				if(temp[1] == 'true') {
					$('#blog_desc').html(description);
					$('#reply_info').append('<p id="msg">' + temp[2] + '</p>');
				}
				
				if(temp[3] == 'true') {
					$('#blog_name').html(name);
					$('#reply_info').append('<p id="msg">' + temp[4] + '</p>');
				}
				
				if(temp[5] == 'true') {
					$('#blog_img').attr('src', '../blog_images/' + temp[7]);
					$('#reply_info').append('<p id="msg">' + temp[6] + '</p>');
				}
				else if(temp[5] == 'false') {
					$('#reply_info').append('<p id="msg">' + temp[6] + '</p>');
				}
				
				$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
			}
			else {
				$('#ebd_reply').html(temp[1]);
			}
			
		}
		submitData(form_data, url, showInfo);
	});
	
	/* delete blog */
	var blog_id = undefined;
	$('#container').on('click', '#delete_blog', function() {
		blog_id = $('#blog_id').html();
		$('#for_popups_pop').empty();
		$('#for_popups_pop').append('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want to delete this blog?</p>');
		$('#reply_info').append('<p class="button red" id="yes_delete_blog">Delete</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">Cancel</p>');
		$("#for_popups_pop").fadeIn(300);
	});

	$('#for_popups_pop').on('click', '#yes_delete_blog', function() {
		var form_data = new FormData();
		form_data.append('blog_id', blog_id);
		form_data.append('action', 'delete_blog');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = reply.split('|');
			$('#for_popups_pop').empty();
			$('#for_popups_pop').append('<div id="reply_info"></div>');
			if(temp[0] == 'true') {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');

				$('#container').empty();
				$('#posts_container').empty();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
			}
			$('#reply_info').append('<p id="msg">' + temp[1] + '</p>');
			$('#reply_info').append('<p class="button blue force_reload" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(form_data, url, showInfo);
	});
	
	/* record if post was viewed */
	var view_time = 5000; //5sec
	var view_posts = {};
	var vp = 0;//for view posts indexing 
	//get all posts in the viewport
	detectPostViews();
	function detectPostViews() {
		$('.post_details').each(function() {
			var e = $(this).children('.post_div');
			var top_of_element = $(e).offset().top;
			var bottom_of_element = $(e).offset().top + $(e).outerHeight();
			var bottom_of_screen = $(window).scrollTop() + window.innerHeight;
			var top_of_screen = $(window).scrollTop();
			var element_height = $(e).outerHeight();
			if(element_height <= 0) {
				return true;
			}
			//element is in the viewport
			if((bottom_of_screen > top_of_element) && (top_of_screen < bottom_of_element)) {
				var visible_part = bottom_of_screen - top_of_element;
				var px_percentage = 100 / element_height;
				var visible_percent = px_percentage * visible_part;
				
				//if post height > 250 then must be visible at least 251
				if(visible_part < 250 && element_height > 250) {
					return true;
				}
				//if post height <= 250 then must be visible in all height
				if(element_height <= 250 && visible_percent < 100) {
					return true;
				}
				
				//element is visible
				var post_id = $(this).children('.get_post_id').html();
				var timestamp = $.now();
				for(var x = 0; x < vp; x++) {
					if(view_posts["_" + x].post_id == post_id) {
						if((view_posts["_" + x].timestamp + view_time) <= timestamp && !view_posts["_" + x].recorded
						&& view_posts["_" + x].detected) {
							markAsViewd(post_id);
							view_posts["_" + x].recorded = true;
						}
						//if scrolled away from the post before and then came back.
						else if(view_posts["_" + x].detected == false) {
							view_posts["_" + x].timestamp = timestamp;
						}
						view_posts["_" + x].in_view = true;
						view_posts["_" + x].detected = true;
						return true;
					}
				}
				view_posts["_" + vp] = {post_id: post_id, timestamp: timestamp, detected: true, recorded: false, in_view: true};
				vp++;
			}
		});
		for(var x = 0; x < vp; x++) {
			//if not in te viewport anymore
			if(!view_posts["_" + x].in_view) {
				view_posts["_" + x].detected = false;
			}
			view_posts["_" + x].in_view = false;
		}
		var post_views_t = setTimeout(detectPostViews, 1000);
	}

	function markAsViewd(post_id) {
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('action', 'mark_as_viewed');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#post_' + post_id).find('.views').addClass('liked_viewd');
				$('#post_' + post_id).find('.views').html('<i class="fa fa-eye" aria-hidden="true"></i> ' +
													 numberFormat(temp.views, 0, '', ' '));
			}
		}
		submitData(form_data, url, showInfo, false);
	}
	
	/* like/unlike post */
	$('#posts_container').on('click', '.likes i', function() {
		var post_id = $(this).parent().parent().parent().children('.get_post_id').html();
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('action', 'like_unlike');
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				if(temp.liked) {
					$('#post_' + post_id).find('.likes').addClass('liked_viewd');
					$('#post_' + post_id).find('.likes').html('<i class="fas fa-thumbs-up"></i> ' +
														 numberFormat(temp.likes, 0, '', ' '));
				}
				else {
					$('#post_' + post_id).find('.likes').removeClass('liked_viewd');
					$('#post_' + post_id).find('.likes').html('<i class="far fa-thumbs-up"></i> ' +
														 numberFormat(temp.likes, 0, '', ' '));
				}
			}
		}
		submitData(form_data, url, showInfo);
	});
	
	/* load more posts */
	$('#load_more_posts').on('click', function() {
		var blog_id = $('#blog_id').html();
		var posts_loaded = $('#posts_loaded').html();
		var form_data = new FormData();
		form_data.append('posts_loaded', posts_loaded);
		form_data.append('blog_id', blog_id);
		form_data.append('action', 'load_more_posts');
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#posts_loaded').html(temp.posts_loaded);
				
				for(var x = 0; x < temp.posts.length; x++) {
					temp.posts[x].post = $("<textarea/>").html(temp.posts[x].post).text();
					
					if(temp.posts[x].owner == true) {
						var delete_post = '<p class="delete_post">Delete Post</p>';
						var edit_post = '<p class="edit_post">Edit Post</p>';
					}
					else {
						var delete_post = '';
						var edit_post = '';
					}
					
					var comments = '';
					for(var u = 0; u < temp.posts[x].comments.length; u++) {
						comments += '<div class="comment_div" id="_' + temp.posts[x].comments[u].comment_id + '">' +
									'<p class="comment_id" hidden>' + temp.posts[x].comments[u].comment_id + '</p>' +
									'<p class="time">' + temp.posts[x].comments[u].comment_date + 
									' <a href="user_profile?id=' + temp.posts[x].comments[u].posted_by_id + 
									'">' + temp.posts[x].comments[u].user_name + '</a></p>';
							 if(temp.posts[x].comments[u].owner) {
								comments += '<p class="edit_comment">Edit</p>' +
											'<p class="delete_comment">Delete</p>';
							 }
						comments += '<img src="../user_images/' + temp.posts[x].comments[u].user_image + '" alt="user image">' +
									'<p class="comment_msg">' + temp.posts[x].comments[u].comment + '</p>' +
									'</div>';
					}
					
					$('#load_more_posts').before('<div class="post_details" id="post_' + temp.posts[x].post_id + '">' +
										  '<p class="get_post_id" hidden>' + temp.posts[x].post_id + '</p>' +
										  '<img class="post_blog_img" src="../blog_images/' + temp.posts[x].blog_image + '">' +
										  '<a class="blog_name_link" href="blog_info?blog_id=' + temp.posts[x].blog_id + 
										  '">' + temp.posts[x].blog_name + '</i></a>' +
										  '<p class="post_date">' + temp.posts[x].post_date + '</p>' +
										  '<p class="edit_post_date">' + temp.posts[x].edit_post_text + '</p>' +
										   delete_post +
										   edit_post +
										  '<a class="post_title" href="blog_info?blog_id=' + temp.posts[x].blog_id + 
										  '&post_id=' + temp.posts[x].post_id + '">' + temp.posts[x].title + '</a>' +
										  '<div class="post_div">' + temp.posts[x].post + '</div>' +
										  '<div class="post_views_likes_div">' +
										  '<p class="likes ' + temp.posts[x].liked_class + '">' +
										  '<i class="fa ' + temp.posts[x].liked_tumb + '" aria-hidden="true"></i> ' + 
										  numberFormat(temp.posts[x].likes, 0, '', ' ') + '</p>' +
										  '<p class="views ' + temp.posts[x].viewd_class + '">' +
										  '<i class="fa fa-eye" aria-hidden="true"></i> ' + 
										  numberFormat(temp.posts[x].views, 0, '', ' ') + '</p>' +
										  '</div>' +
										  '<div class="comments_div">' +
										  comments +
										  '</div>' +
										  '<textarea class="comment_entry" maxlength="500"></textarea>' +
										  '<p class="error_comment_reply"></p>' +
										  '<span class="send_comment glyphicon glyphicon-send"></span>' +
										  '</div>');
				}
			}
			else {
				$('#load_more_posts').replaceWith('<p id="no_more_posts">End</p>');
			}
		}
		submitData(form_data, url, showInfo);
	});
	
	/* comment */
	$('#posts_container').on('click', '.send_comment', function() {
		var post_id = $(this).parent().children('.get_post_id').html();
		var comment = $(this).parent().children('.comment_entry').val();
		var e = this;
		var form_data = new FormData();
		form_data.append('post_id', post_id);
		form_data.append('comment', comment);
		if(comment_id && comment_post_id == post_id) {
			form_data.append('comment_id', comment_id);
			form_data.append('action', 'edit_comment');
		}
		else {
			form_data.append('action', 'post_comment');
		}
		
		var url = "../etc/manage_blog";
		function showInfo(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#post_' + post_id).children('.error_comment_reply').empty();
				
				if(comment_id && comment_post_id == post_id) {
					$('#_' + comment_id).children('.comment_msg').html(temp.comment);
					$(e).parent().children('.comment_entry').val('');
					comment_id = undefined;
					comment_post_id = undefined;
				}
				else {
					$(e).parent().children('.comment_entry').val('');
					$('#post_' + post_id).children('.error_comment_reply').empty();
					$('#post_' + post_id + ' .comments_div').append('<div class="comment_div" id="_' + temp.comment_id + '">' +
						'<p class="comment_id" hidden>' + temp.comment_id + '</p>' +
						'<p class="time">' + temp.time + ' <a href="user_profile?id=' + temp.user_id + 
						'">' + $('#user_name').html() + '</a></p>' +
						'<p class="edit_comment">Edit</p>' +
						'<p class="delete_comment">Delete</p>' +
						'<img src="' + $('#user_img img').attr('src') + '" alt="user image">' +
						'<p class="comment_msg">' + temp.comment + '</p>' +
						'</div>');
				}
			}
			else {
				$('#post_' + post_id).children('.error_comment_reply').html(temp.error);
			}
		}
		submitData(form_data, url, showInfo);
	});
	
	$('#posts_container').on('click', '.delete_comment', function() {
		var comment_id = $(this).parent().children('.comment_id').html();
		var e = this;
		var data = new FormData();
		data.append('comment_id', comment_id);
		data.append('action', 'delete_comment');
		
		var url = "../etc/manage_blog";
		function deleteReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$(e).parent().remove();
			}
			else {
				$('#post_' + post_id).children('.error_comment_reply').html(temp.error);
			}
		}
		submitData(data, url, deleteReply);
	});
	
	var comment_id = undefined;
	var comment_post_id = undefined;
	$('#posts_container').on('click', '.edit_comment', function() {
		comment_post_id = $(this).parent().parent().parent().children('.get_post_id').html();
		comment_id = $(this).prev().prev().html();

		var comment = $(this).parent().children('.comment_msg').html();
		$(this).parent().parent().parent().children('.comment_entry').val(comment);
	});
	
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
});