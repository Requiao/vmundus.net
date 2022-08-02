<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="page_head">Blog</p>
		<?php
			$blog_id = htmlentities(stripslashes(strip_tags(trim($_GET['blog_id']))), ENT_QUOTES);
			$post_id = htmlentities(stripslashes(strip_tags(trim($_GET['post_id']))), ENT_QUOTES);
			
			$blog_set = false;
			$specific_post = false;
			if(isset($blog_id) && is_numeric($blog_id)) {
				$query = "SELECT blog_id FROM user_blog WHERE blog_id = '$blog_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$blog_set = true;
				}
			}
			
			if(isset($post_id) && is_numeric($post_id)) {
				$query = "SELECT blog_id FROM blog_posts WHERE post_id = '$post_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($blog_id) = $row;
					$specific_post = true;
					$blog_set = true;
				}
			}
			
			/* display blog details */
			if($blog_set) {
				//determine if blog belongs to the user_blog		  
				$query = "SELECT ub.blog_id, blog_name, description, blog_image, COUNT(bs.blog_id), user_name, u.user_id
						  FROM user_blog ub, blog_subscribers bs, users u
						  WHERE ub.blog_id = bs.blog_id AND u.user_id = ub.user_id AND ub.blog_id = '$blog_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($blog_id, $blog_name, $description, $blog_image, $subscribers, $blogger_name, $blogger_id) = $row;	
				
				/* display blog info */
				echo "\n\t\t" . '<div id="blog_info">' .
					 "\n\t\t\t" . '<p id="blog_id" hidden>' . $blog_id . '</p>';
				if($user_id != $blogger_id) {
					//check if subscribed
					$query = "SELECT * FROM blog_subscribers WHERE blog_id = '$blog_id' AND user_id = '$user_id'";
					$result = $conn->query($query);
					if($result->num_rows != 1) {
						echo "\n\t\t\t" . '<p id="subscribe">Subscribe</p>';
						echo "\n\t\t\t" . '<p id="unsubscribe" hidden>Unubscribe</p>';
					}
					else {
						echo "\n\t\t\t" . '<p id="subscribe" hidden>Subscribe</p>';
						echo "\n\t\t\t" . '<p id="unsubscribe">Unsubscribe</p>';
					}
					
				}
				else {/* if user is blogger, then enable management controls */
					echo "\n\t\t" . '<p id="delete_blog">Delete Blog</p>' .
						 "\n\t\t" . '<p id="write_post">Write Post</p>' .
						 "\n\t\t" . '<p id="cancel_write_post">Cancel Write</p>' .
						 "\n\t\t\t" . '<p id="edit_blog">Edit Blog</p>';
				}
				
				echo "\n\t\t\t" . '<img id="blog_img" src="../blog_images/' . $blog_image . '?' . 
								  filemtime('../blog_images/' . $blog_image) . '">' .
					 "\n\t\t\t" . '<p id="blog_name">' . $blog_name . '</p>' .
					 "\n\t\t\t" . '<p id="blogger_info">Blogger: <a href="user_profile?id=' . $blogger_id . 
								  '">' . $blogger_name . '</a></p>' .
					 "\n\t\t\t" . '<p id="blog_subs">Subscribers: ' . $subscribers . '</p>' .
					 "\n\t\t\t" . '<p id="blog_desc">' . $description . '</p>' .
					 "\n\t\t" . '</div>';
				
				//write post div
				if($user_id == $blogger_id) {
					echo "\n\t\t" . '<div id="post_write_div">' .
						 "\n\t\t\t" . '<p id="publish_post">Publish</p>' .
						 "\n\t\t\t" . '<p id="edit_post">Edit</p>' .
						 "\n\t\t\t" . '<input id="post_head_input" maxlength="60" placeholder="Title...">' .
						 "\n\t\t" . '<textarea id="post_manage_div"></textarea>' .
						 "\n\t\t" . '</div>';
				}
			}
			else {
				echo "<p>Blog doesn't exists.</p>";
			}
			?>
			
			</div>
			
			<?php
				if($blog_set) {
					echo "\n\t" . '<div id="posts_container">';
					
					/* display all posts */
					if(!isset($post_id) || !is_numeric($post_id)) {
						$post_id = false;
					}
					if($post_id) {
						$query = "SELECT COUNT(post_id) FROM blog_posts WHERE 
								  TIMESTAMP(date, time) >= (SELECT TIMESTAMP(date, time) 
								  FROM blog_posts WHERE post_id = '$post_id')
								  AND blog_id = '$blog_id'";
						$result = $conn->query($query);
						$row = $result->fetch_row();
						list($select_posts) = $row;
					}
					else {
						$select_posts = 5;
					}
					
					echo "\n\t\t" . '<p id="posts_loaded" hidden>' . $select_posts . '</p>';
					
					$query = "SELECT post_id, title, post, date, time, blog_image, blog_name, edit_date, edit_time
							  FROM blog_posts bp, user_blog ub 
							  WHERE ub.blog_id = '$blog_id' AND bp.blog_id = ub.blog_id
							  ORDER BY date DESC, time DESC
							  LIMIT 0, $select_posts";
					$result_posts = $conn->query($query);
					while($row_posts = $result_posts->fetch_row()) {
							list($post_id, $title, $post, $date, $time, $blog_image, $blog_name, $edit_date, $edit_time) = $row_posts;
							
							//publish time
							$date = correctDate($date, $time);
							$time = correctTime($time);
							
							$post_date = date('M j', strtotime($date)) . ' ' . $time;
							
							//edit time
							if(!empty($edit_date)) {
								$edit_date = correctDate($edit_date, $edit_time);
								$edit_time = correctTime($edit_time);
							
								$edit_post_date = date('M j', strtotime($edit_date)) . ' ' . $edit_time;
								
								$edit_post_date = 'Modified on: ' . $edit_post_date . '</p>';
							}
							else {
								$edit_post_date = '';
							}
							
							//get likes and views
							$query = "SELECT COUNT(CASE WHEN liked = true THEN 1 END), COUNT(CASE WHEN viewed = true THEN 1 END)
									  FROM post_likes_views WHERE post_id = '$post_id'";
							$result = $conn->query($query);
							$row = $result->fetch_row();
							list($likes, $views) = $row;
							
							//check if liked and/or viewd
							$query = "SELECT liked, viewed FROM post_likes_views WHERE post_id = '$post_id' AND user_id = '$user_id'";
							$result = $conn->query($query);
							if($result->num_rows == 1) {
								$row = $result->fetch_row();
								list($liked, $viewed) = $row;
							}
							else {
								$liked = $viewed = false;
							}
							
							$viewd_class = '';
							$liked_class = '';
							$liked_tumb = 'far fa-thumbs-up';
							if($viewed) {
								$viewd_class = 'liked_viewd';
							}
							if($liked) {
								$liked_class = 'liked_viewd';
								$liked_tumb = 'fas fa-thumbs-up';
							}
							
							if($user_id == $blogger_id) {
								$delete_post = "\n\t\t\t" . '<p class="delete_post">Delete Post</p>';
								$edit_post = "\n\t\t\t" . '<p class="edit_post">Edit Post</p>';
							}
							else {
								$delete_post = "";
								$edit_post = "";
							}
							
							echo "\n\t\t" . '<div class="post_details" id="post_' . $post_id . '">' .
								 "\n\t\t\t" . '<p class="get_post_id" hidden>' . $post_id . '</p>' .
								 "\n\t\t\t" . '<img class="post_blog_img" src="../blog_images/' . $blog_image . '?' . 
											 filemtime('../blog_images/' . $blog_image) . '">' .
								 "\n\t\t\t" . '<a class="blog_name_link" href="blog_info?blog_id=' . $blog_id . 
											  '">' . $blog_name . '</i></a>' .
								 "\n\t\t\t" . '<p class="post_date">' . $post_date . '</p>' .
								 "\n\t\t\t" . '<p class="edit_post_date">' . $edit_post_date . '</p>' .
								 $delete_post .
								 $edit_post .
								 "\n\t\t\t" . '<a class="post_title" href="blog_info?blog_id=' . $blog_id . '&post_id=' . $post_id . 
											  '">' . $title . '</a>' .
								 "\n\t\t\t" . '<div class="post_div">' . html_entity_decode($post, ENT_QUOTES) . '</div>' .
								 "\n\t\t\t" . '<div class="pd_blog_author">' .
								 "\n\t\t\t\t" . '<p>Written by:</p>' .
								 "\n\t\t\t\t" . '<a class="pd_blogger" href="user_profile?id=' . $blogger_id . 
												'">' . $blogger_name . '</a>' .
								 "\n\t\t\t" . '</div>' .
								 "\n\t\t\t" . '<div class="post_views_likes_div">' .
								 "\n\t\t\t\t" . '<p class="likes ' . $liked_class . '">' .
												'<i class="' . $liked_tumb . '"></i> ' . 
												number_format($likes, 0, '', ' ') . '</p>' .
								 "\n\t\t\t\t" . '<p class="views ' . $viewd_class . '">' .
												'<i class="far fa-eye" aria-hidden="true"></i> ' . 
												number_format($views, 0, '', ' ') . '</p>' .
								 "\n\t\t\t" . '</div>' .
								 "\n\t\t\t" . '<div class="comments_div">';
							
							//select comments
							$query = "SELECT u.user_id, user_name, user_image, comment, comment_id, date, time 
									   FROM users u, user_profile up, post_comments pc
									   WHERE u.user_id = up.user_id AND up.user_id = pc.user_id
									   AND post_id = '$post_id' ORDER BY date ASC, time ASC";
							$result_comments = $conn->query($query);
							while($row_comments = $result_comments->fetch_row()) {
								list($posted_by_id, $user_name, $user_image, $comment, $comment_id, $date, $time) = $row_comments;
								$date = CorrectDate($date, $time);
								$time = CorrectTime($time);
								
								$comment_date = date('M j', strtotime($date)) . ' ' . $time;
								echo "\n\t\t\t\t" . '<div class="comment_div" id="_' . $comment_id . '">' .
									 "\n\t\t\t\t\t" . '<p class="comment_id" hidden>' . $comment_id . '</p>' .
									 "\n\t\t\t\t\t" . '<p class="time">' . $comment_date . 
													  ' <a href="user_profile?id=' . $posted_by_id . 
													  '">' . $user_name . '</a></p>';
									 if($user_id == $posted_by_id) {
										echo "\n\t\t\t\t\t" . '<p class="edit_comment">Edit</p>' .
											 "\n\t\t\t\t\t" . '<p class="delete_comment">Delete</p>';
									 }
								echo "\n\t\t\t\t\t" . '<img src="../user_images/' . $user_image . '" alt="user image">' .
									 "\n\t\t\t\t\t" . '<p class="comment_msg">' . $comment . '</p>' .
									 "\n\t\t\t\t" . '</div>';
							}
							echo "\n\t\t\t" . '</div>' .
								 "\n\t\t\t" . '<textarea class="comment_entry" maxlength="500"></textarea>' .
								 "\n\t\t\t" . '<p class="error_comment_reply"></p>' .
								 "\n\t\t\t" . '<span class="send_comment glyphicon glyphicon-send"></span>' .
								 "\n\t\t" . '</div>';
					}
					echo "\n\t\t" . '<p id="load_more_posts">Load more</p>';
				}
		?>
		
	</div>
	
</main>

<?php include('footer.php'); ?>