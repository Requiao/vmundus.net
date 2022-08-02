<?php
	//Description: Manage blog.
	session_start();

	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/upload_image.php'); //uploadImage($image_id, $folder_path, $old_path = NULL, $width = 400, $height = 200)
	include('../php_functions/string_validate.php'); //stringValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).												  
	require_once '../htmlpurifier/library/HTMLPurifier.auto.php';
	
	$config = HTMLPurifier_Config::createDefault();
	$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
	$config->set('Attr.AllowedClasses', '');
	$config->set('CSS.AllowedFonts', 'Arial, Helvetica, sans-serif, Comic Sans MS, cursive, Courier New, Courier, monospace,
				 Georgia, Lucida Sans Unicode, Lucida Grande, Tahoma, Geneva, Times New Roman, Times, serif, Trebuchet MS, Verdana');
	$config->set('CSS.MaxImgLength', '2000px');
	$config->set('CSS.AllowedProperties', 'background-color, border, border-collapse, border-spacing, border-width, clear, color, float, font-family, font-size, font-style, font-weight, height, letter-spacing, line-height, margin, margin-bottom, margin-left, margin-right, margin-top, max-height, max-width, padding, padding-bottom, padding-left, padding-right, padding-top, table-layout, text-align, text-decoration, text-indent, width, word-spacing');
	$config->set('HTML.MaxImgLength', '2000');
	$config->set('HTML.AllowedElements', 'a, abbr, address, b, bdo, blockquote, br, caption, cite, code, col, colgroup, dd, del, div, dl, dt, em, h1, h2, h3, h4, h5, h6, hr, i, img, kbd, li, ol, p, pre, q, s, samp, small, span, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var');
	$config->set('HTML.TargetBlank', true);
	
	$purifier = new HTMLPurifier($config);

	$name =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['name']))), ENT_QUOTES);
	$description =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['description']))), ENT_QUOTES);
	$title =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['title']))), ENT_QUOTES);
	$post =  htmlspecialchars($purifier->purify(stripslashes(trim($_POST['post']))), ENT_QUOTES);
	$comment =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['comment']))), ENT_QUOTES);
	$comment_id =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['comment_id']))), ENT_QUOTES);
	$blog_id =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['blog_id']))), ENT_QUOTES);
	$post_id =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['post_id']))), ENT_QUOTES);
	$posts_loaded =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['posts_loaded']))), ENT_QUOTES);
	$action =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	$reg_string = '/(*UTF8)^[\s\S]*$/i';
	$unmatched_regex = '/(*UTF8)[\s\S]*/i';	
	
	if ($action == 'like_unlike') {
		if(!is_numeric($post_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Post doesn't exists."
									  )));
		}
		$query = "SELECT ub.blog_id, user_id FROM blog_posts bp, user_blog ub
				  WHERE post_id = '$post_id' AND ub.blog_id = bp.blog_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Post doesn't exists"
									  )));
		}
		$row = $result->fetch_row();
		list($blog_id, $poster_id) = $row;
		
		//check if alread liked
		$query = "SELECT liked FROM post_likes_views WHERE post_id = '$post_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			//mark as viewed
			$query = "INSERT INTO post_likes_views VALUES ('$post_id', '$user_id', TRUE, FALSE)";
			$conn->query($query);
			
			//record like
			$query = "SELECT * FROM post_views_rewards WHERE user_id = '$poster_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE post_views_rewards SET likes = likes + 1 WHERE user_id = '$poster_id'";
			}
			else {
				$query = "INSERT INTO post_views_rewards VALUES ('$poster_id', '0', '0', '1', '0')";
			}
			$conn->query($query);
		}
		else {
			$row = $result->fetch_row();
			list($liked) = $row;
			if($liked) {//already likes. unlike
				$query = "UPDATE post_likes_views SET liked = FALSE WHERE post_id = '$post_id' AND user_id = '$user_id'";
				$conn->query($query);
				
				$query = "UPDATE post_views_rewards SET likes = likes - 1 WHERE user_id = '$poster_id'";
				$conn->query($query);
			}
			else {//like again
				$query = "UPDATE post_likes_views SET liked = TRUE WHERE post_id = '$post_id' AND user_id = '$user_id'";
				$conn->query($query);
				
				//record like
				$query = "SELECT * FROM post_views_rewards WHERE user_id = '$poster_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$query = "UPDATE post_views_rewards SET likes = likes + 1 WHERE user_id = '$poster_id'";
				}
				else {
					$query = "INSERT INTO post_views_rewards VALUES ('$poster_id', '0', '0', '1', '0')";
				}
				$conn->query($query);
			}
		}

		//get like number
		$query = "SELECT COUNT(user_id) FROM post_likes_views WHERE post_id = '$post_id' AND liked = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($likes) = $row;
		
		echo json_encode(array("success"=>true,
							   "likes"=>$likes,
							   "liked"=>!$liked
							  ));
	}
	else if ($action == 'mark_as_viewed') {
		if(!is_numeric($post_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Post doesn't exists."
									  )));
		}
		$query = "SELECT ub.blog_id, user_id FROM blog_posts bp, user_blog ub
				  WHERE post_id = '$post_id' AND ub.blog_id = bp.blog_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Post doesn't exists"
									  )));
		}
		$row = $result->fetch_row();
		list($blog_id, $poster_id) = $row;
		
		//check if alread viewed
		$query = "SELECT viewed FROM post_likes_views WHERE post_id = '$post_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			//mark as viewed
			$query = "INSERT INTO post_likes_views VALUES ('$post_id', '$user_id', FALSE, TRUE)";
			$conn->query($query);
		}
		else {
			$row = $result->fetch_row();
			list($viewed) = $row;
			if($viewed) {
				exit(json_encode(array("success"=>false,
									   "error"=>"You already viewed this post"
									  )));
			}
			else {
				$query = "UPDATE post_likes_views SET viewed = TRUE WHERE post_id = '$post_id' AND user_id = '$user_id'";
				$conn->query($query);
			}
		}
		
		//record view
		$query = "SELECT * FROM post_views_rewards WHERE user_id = '$poster_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE post_views_rewards SET views = views + 1 WHERE user_id = '$poster_id'";
		}
		else {
			$query = "INSERT INTO post_views_rewards VALUES ('$poster_id', '1', '0', '0', '0')";
		}
		$conn->query($query);

		//get view number
		$query = "SELECT COUNT(user_id) FROM post_likes_views WHERE post_id = '$post_id' AND viewed = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($views) = $row;
		
		echo json_encode(array("success"=>true,
							   "views"=>$views
							  ));
	}
	else if($action == "create_new_blog") {
		//check for blogs. must have only 1
		$query = "SELECT COUNT(*) FROM user_blog WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_blogs) = $row;	
		if($user_blogs >= 1) {
			exit("0|You can only have 1 blog.");
		}

		stringValidate($name, 1, 30, 'Blog name');
		stringValidate($description, 0, 350, 'Description');
		
		//check if name not duplicate
		if(!empty($name)) {
			$query = "SELECT * FROM user_blog WHERE blog_name = '$name'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Blog with this name already exists.");
			}
		}
		
		$blog_id = getTimeForId() . $user_id;
		
		if(file_exists($_FILES['image']['tmp_name'])) {//upload image
			$blog_img = uploadImage($blog_id, '../blog_images');
			if($blog_img == 1) {
				exit("0|File is not an image.");
			}
			if($blog_img == 2) {
				exit("0|Sorry, your file is too large.");
			}
			if($blog_img == 3) {
				exit("0|Sorry, only JPG, JPEG, PNG files are allowed.");
			}
			if($blog_img == 4) {
				exit("0|Image not uploaded. Please try again");
			}
		}
		else {
			$blog_img = null;
		}
		
		$query = "INSERT INTO user_blog VALUES('$blog_id', '$user_id', '$name', '$description', '$blog_img')";
		if($conn->query($query)) {
			$query = "INSERT INTO blog_subscribers VALUES('$blog_id', '$user_id')";
			$conn->query($query);
			
			echo "true|You have successfully created new blog.|";

			$query = "SELECT blog_id, blog_name, description, blog_image FROM user_blog WHERE blog_id = '$blog_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($blog_id, $blog_name, $description, $blog_image) = $row;	
			
			echo '<div class="blog_list_div">' .
				 '<img class="blog_img" src="../blog_images/' . $blog_image . '">' .
				 '<p class="blog_name">' . $blog_name . '</p>' .
				 '<p class="blog_desc">' . $description . '</p>' .
				 '<p class="blog_subs">Subscribers: 1</p>' .
				 '<a class="button blue details" href="blog_info?blog_id=' . $blog_id . '" target="_blank">Manage</a>' .
				 '</div>';
		}
		else {
			echo "0|Unable to create blog $name.";
			if(file_exists($_FILES['fileToUpload']['tmp_name'])) {
				unlink('../party_flags/' . $flag);
			}
		}
	}
	else if($action == "publish_post") {
		if(!is_numeric($blog_id)) {
			exit();
		}

		//select users blog
		$query = "SELECT blog_id FROM user_blog WHERE user_id = '$user_id' AND blog_id = '$blog_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have a blog.");
		}
		$row = $result->fetch_row();
		list($blog_id) = $row;
		
		//can publish 2 posts per day
		$query = "SELECT COUNT(*) FROM blog_posts WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL 1 DAY) >= NOW()
				  AND blog_id = '$blog_id'";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			$row = $result->fetch_row();
			list($posted_posts_per_day) = $row;
			
			if($posted_posts_per_day >= 2 && $user_id != 1) {
				exit("0|You can only post 2 times per day.");
			}
		}
		
		stringValidate($title, 1, 60, 'Title');
		stringValidate($post, 1, 21700, 'Post');
		
		$post_id = getTimeForId() . $user_id;
		
		$query = "INSERT INTO blog_posts VALUES('$post_id', '$blog_id', '$title', '$post', CURRENT_DATE, CURRENT_TIME, NULL, NULL)";
		if($conn->query($query))  {		
			$time = date('H:i:s');
			$date = correctDate(date('Y-m-d'), $time);
			$time = correctTime($time);
			$post_date = date('M j', strtotime($date)) . ' ' . $time;
			
			echo "1|Post successfully published.|$post_id|$title|$post|$post_date|$user_id";
		}
		else {
			echo "0|Error. Try again.";
		}
	}
	else if($action == "delete_post") {
		if(!is_numeric($post_id)) {
			exit("0|You don't have permission to delete this post.");
		}
		
		//check if post belongs to the user
		$query = "SELECT * FROM user_blog WHERE blog_id = 
				  (SELECT blog_id FROM blog_posts WHERE post_id = '$post_id') AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have permission to delete this post.");
		}
		
		//check if post is not user's campaign
		$query = "SELECT election_id FROM congress_elections WHERE post_id = '$post_id'
				  AND election_id IN (SELECT election_id FROM election_info 
				  WHERE (ended = 1 and can_participate = 1) || (ended = 0 and can_participate = 0))
				  UNION
				  SELECT election_id FROM president_elections WHERE post_id = '$post_id'
				  AND election_id IN (SELECT election_id FROM election_info 
				  WHERE (ended = 1 and can_participate = 1) || (ended = 0 and can_participate = 0))";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"This post is used in your election campaign, you cannot delete it."
								  )));
		}

		
		$query = "DELETE FROM post_comments WHERE post_id = '$post_id'";
		$conn->query($query);
		
		$query = "DELETE FROM post_likes_views WHERE post_id = '$post_id'";
		$conn->query($query);
		
		$query = "DELETE FROM public_post_views WHERE post_id = '$post_id'";
		$conn->query($query);
		
		$query = "DELETE FROM blog_posts WHERE post_id = '$post_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Post have been successfully deleted"
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error. Try again."
								  )));
		}
	}
	else if($action == "edit_post") {					  
		if(!is_numeric($post_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have permission to delete this post."
								  )));
		}
		
		//check if post belongs to the user
		$query = "SELECT * FROM user_blog WHERE blog_id = 
				  (SELECT blog_id FROM blog_posts WHERE post_id = '$post_id') AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have permission to delete this post."
								  )));
		}
		
		stringValidate($title, 1, 60, 'Title');
		stringValidate($post, 1, 21700, 'Post');
		
		$query = "UPDATE blog_posts SET title = '$title' WHERE post_id = '$post_id'";
		$conn->query($query);
		
		$query = "UPDATE blog_posts SET post = '$post' WHERE post_id = '$post_id'";
		if($conn->query($query))  {	
			$query = "UPDATE blog_posts SET edit_date = CURRENT_DATE WHERE post_id = '$post_id'";
			$conn->query($query);
			
			$query = "UPDATE blog_posts SET edit_time = CURRENT_TIME WHERE post_id = '$post_id'";
			$conn->query($query);
			
			$edit_date = correctDate(date('Y-m-d'), date('H:i:s'));
			$edit_time = correctTime(date('H:i:s'));

			$edit_post_date = date('M j', strtotime($edit_date)) . ' ' . $edit_time;
	
			$edit_post_date = 'Modified on: ' . $edit_post_date . '</p>';
			
			echo json_encode(array("success"=>true,
								   "msg"=>"Post successfully updated.",
								   "title"=>$title,
								   "post"=>$post,
								   "edit_post_date"=>$edit_post_date
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Something went wrong. Please try again."
								  )));
		}
	}
	else if($action == "subscribe") {
		if(!is_numeric($blog_id)) {
			exit($blog_id);
		}
		
		//check if user is subscribed to this blog
		$query = "SELECT * FROM blog_subscribers WHERE blog_id = '$blog_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You already subscribed to this blog.");
		}
		
		$query = "INSERT INTO blog_subscribers VALUES('$blog_id', '$user_id')";
		if($conn->query($query))  {
			/* Media Tycoon achievement */
			$query = "SELECT user_id FROM user_blog WHERE blog_id = '$blog_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($blogger_id) = $row;
			
			$query = "SELECT COUNT(*) FROM blog_subscribers WHERE blog_id IN
					 (SELECT blog_id FROM user_blog WHERE user_id = '$blogger_id')";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($user_subscribers) = $row;
				
				//check if user received reward for subscribers
				$earned_new_level = false;
				
				$query = "SELECT level_id, total_subscribers FROM user_blog_subscribers_levels 
						  WHERE total_subscribers <= '$user_subscribers' ORDER BY level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 0) {
					$query = "SELECT level_id, total_subscribers FROM user_blog_subscribers_levels WHERE level_id = 1";
					$result = $conn->query($query);
				}
				$row = $result->fetch_row();
				list($next_level, $total_subscribers) = $row;
				
				$query = "SELECT level_id FROM user_blog_subscribers_rewards WHERE user_id = '$blogger_id' ORDER BY level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 0 && $user_subscribers >= $total_subscribers) {
					$earned_new_level = true;
				}
				else if ($result->num_rows != 0) {
					$row = $result->fetch_row();
					list($current_level) = $row;
					if($next_level > $current_level && $user_subscribers >= $total_subscribers) {//earned medal
						$earned_new_level = true;
					}
				}
				
				if($earned_new_level) {
					//if made damage for more than 1 medal.
					$earned_levels = $next_level - $current_level;
					$next_level -= $earned_levels;
					for($u = 0; $u < $earned_levels; $u++) {
						$next_level++;
						
						$query = "INSERT INTO user_blog_subscribers_rewards VALUES ('$blogger_id', '$next_level')";
						$conn->query($query);
						
						$achievement_id = 4; //Media Tycoon
						$query = "SELECT earned FROM user_achievements WHERE user_id = '$blogger_id' AND achievement_id = '$achievement_id'";
						$result = $conn->query($query);
						if($result->num_rows == 0) {
							$query = "INSERT INTO user_achievements VALUES ('$blogger_id', '$achievement_id', '0', '1')";
						}
						else {
							$row = $result->fetch_row();
							list($earned) = $row;
							$earned++;
							$query = "UPDATE user_achievements SET earned = '$earned' WHERE user_id = '$blogger_id' 
									  AND achievement_id = '$achievement_id'";
						}
						$conn->query($query);
						
						//notify user
						$notification = "Congratulation. You earned Media Tycoon medal.";
						sendNotification($notification, $blogger_id);
					}
				}
			}
			echo "1|You have successfully subscribed to this blog.";
		}
		else {
			echo "0|Error. Try again.";
		}
	}
	else if($action == "unsubscribe") {
		if(!is_numeric($blog_id)) {
			exit();
		}
		
		//check if user is subscribed to this blog
		$query = "SELECT * FROM blog_subscribers WHERE blog_id = '$blog_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You are not subscribed to this blog.");
		}
		
		$query = "DELETE FROM blog_subscribers WHERE user_id = '$user_id' AND blog_id = '$blog_id'";
		if($conn->query($query))  {			
			echo "1|You have successfully unsubscribed to this blog.";
		}
		else {
			echo "0|Error. Try again.";
		}
	}
	if($action == "edit_blog") {
		if(!is_numeric($blog_id)) {
			exit();
		}

		//is blog belongs to the user
		$query = "SELECT blog_id, blog_image FROM user_blog WHERE user_id = '$user_id' AND blog_id = '$blog_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit("0|You are not allowed to edit this blog.");
		}
		$row = $result->fetch_row();
		list($blog_id, $blog_image) = $row;
	
		stringValidate($name, 1, 30, 'Blog name');
		stringValidate($description, 0, 350, 'Description');
		
		//check if name not duplicate
		if(!empty($name)) {
			$query = "SELECT * FROM user_blog WHERE blog_name = '$name' AND blog_id != '$blog_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Blog with this name already exists.");
			}
		}
		
		$query = "UPDATE user_blog SET description = '$description' WHERE blog_id = '$blog_id'";
		if($conn->query($query)) {
			echo "true|true|You have successfully updated description.|";
			
			$query = "UPDATE user_blog SET blog_name = '$name' WHERE blog_id = '$blog_id'";
			if($conn->query($query)) {
				echo "true|You have successfully updated name.|";
				
				if(file_exists($_FILES['image']['tmp_name'])) {//upload image
					if(!empty($blog_image)) {
						$blog_img = uploadImage($blog_id, '../blog_images', '../blog_images/' . $blog_image);
					}
					else {
						$blog_img = uploadImage($blog_id, '../blog_images');
					}
					if($blog_img === 1) {
						echo "false|File is not an image.|";
					}
					else if($blog_img === 2) {
						echo "false|Your file is too large.|";
					}
					else if($blog_img === 3) {
						echo "false|Only png, jpeg, and jpg are allowed for the user image.|";
					}
					else if($blog_img === 4) {
						echo "false|Image not uploaded. Please try again.|";
					}
					else {
						$query = "UPDATE user_blog SET blog_image = '$blog_img' WHERE blog_id = '$blog_id'";
						$conn->query($query);
						echo "true|You have successfully updated blog image.|$blog_img?" . 
							  filemtime('../blog_images/' . $blog_image);
					}
				}
			}
		}
		else {
			echo "0|Error. Try again.";
		}
	}
	if($action == "delete_blog") {
		if(!is_numeric($blog_id)) {
			exit();
		}
		
		//is blog belongs to the user
		$query = "SELECT blog_id, blog_image FROM user_blog WHERE user_id = '$user_id' AND blog_id = '$blog_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit("0|You are not allowed to delete this blog.");
		}
		$row = $result->fetch_row();
		list($blog_id, $blog_image) = $row;
	
		//check if any of the posts is used in campaign
		$query = "SELECT election_id FROM congress_elections WHERE post_id IN 
				 (SELECT post_id FROM blog_posts WHERE blog_id = '$blog_id')
				  AND election_id IN (SELECT election_id FROM election_info 
				  WHERE (ended = 1 and can_participate = 1) || (ended = 0 and can_participate = 0))
				  UNION
				  SELECT election_id FROM president_elections WHERE post_id IN 
				  (SELECT post_id FROM blog_posts WHERE blog_id = '$blog_id')
				  AND election_id IN (SELECT election_id FROM election_info 
				  WHERE (ended = 1 and can_participate = 1) || (ended = 0 and can_participate = 0))";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit("0|Some posts from this blog are used in your election campaign, a blog cannot be deleted.");
		}
		
		$query = "DELETE FROM blog_subscribers WHERE blog_id = '$blog_id'";
		$conn->query($query);
		
		$query = "DELETE FROM post_comments WHERE post_id IN (SELECT post_id FROM blog_posts WHERE blog_id = '$blog_id')";
		$conn->query($query);

		$query = "DELETE FROM post_likes_views WHERE post_id IN
				 (SELECT post_id FROM blog_posts WHERE blog_id = '$blog_id'";
		$conn->query($query);

		$query = "DELETE FROM public_post_views WHERE post_id IN
				 (SELECT post_id FROM blog_posts WHERE blog_id = '$blog_id'";
		$conn->query($query);
		
		$query = "DELETE FROM blog_posts WHERE blog_id = '$blog_id'";
		$conn->query($query);
		
		$query = "DELETE FROM user_blog WHERE blog_id = '$blog_id'";
		if($conn->query($query)) {
			if(!empty($blog_image)) {
				unlink('../blog_images/' . $blog_image);
			}
			
			echo "true|You have successfully deleted this blog.";
		}
		else {
			echo "0|Error. Try again.";
		}
	}
	else if ($action == 'load_more_posts') {
		if($blog_id != 'all') {
			if(!is_numeric($blog_id)) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Blog doesn't exists."
									   )));
			}
		}
		if(!is_numeric($posts_loaded) || $posts_loaded < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid request."
								   )));
		}

		$load_posts = 5;

		if($blog_id == 'all') {
			$query = "SELECT post_id, title, post, date, time, blog_image, blog_name, ub.blog_id, user_id, edit_date, edit_time
					  FROM blog_posts bp, user_blog ub
					  WHERE ub.blog_id IN 
					 (SELECT blog_id FROM blog_subscribers WHERE user_id = '$user_id') 
					  AND bp.blog_id = ub.blog_id ORDER BY date DESC, time DESC LIMIT $posts_loaded, $load_posts";
		}
		else {
			$query = "SELECT post_id, title, post, date, time, blog_image, blog_name, ub.blog_id, user_id, edit_date, edit_time 
					  FROM blog_posts bp, user_blog ub 
					  WHERE ub.blog_id = '$blog_id' AND bp.blog_id = ub.blog_id ORDER BY date DESC, time DESC
					  LIMIT $posts_loaded, $load_posts";
		}
		$result_posts = $conn->query($query);
		if($result_posts->num_rows < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Nothing to load."
								   )));
		}
		$posts = array();
		while($row_posts = $result_posts->fetch_row()) {
			list($post_id, $title, $post, $date, $time, $blog_image, $blog_name, $blog_id, $blogger_id, 
				 $edit_date, $edit_time) = $row_posts;
			
			//publish time
			$date = correctDate($date, $time);
			$time = correctTime($time);
			
			$post_date = date('M j', strtotime($date)) . ' ' . $time;
			
			//edit time
			if(!empty($edit_date)) {
				$edit_date = correctDate($edit_date, $edit_time);
				$edit_time = correctTime($edit_time);
				
				$edit_post_date = date('M j', strtotime($edit_date)) . ' ' . $edit_time;
				
				$edit_text = "\n\t\t\t" . '<p class="edit_post_date">Modified on: ' . $post_date . '</p>';
			}
			else {
				$edit_text = '';
			}
		
			//get likes and views
			$query = "SELECT COUNT(user_id), (SELECT COUNT(user_id) FROM post_likes_views WHERE post_id = '$post_id' 
					  AND liked = TRUE) FROM post_likes_views WHERE post_id = '$post_id' AND viewed = TRUE";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($views, $likes) = $row;
			
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
			$liked_tumb = 'fa-thumbs-o-up';
			if($viewed) {
				$viewd_class = 'liked_viewd';
			}
			if($liked) {
				$liked_class = 'liked_viewd';
				$liked_tumb = 'fa-thumbs-up';
			}

			if($user_id == $blogger_id) {
				$owner = true;
			}
			else {
				$owner = false;
			}
			
			//select comments
			$query = "SELECT u.user_id, user_name, user_image, comment, comment_id, date, time 
					  FROM users u, user_profile up, post_comments pc
					  WHERE u.user_id = up.user_id AND up.user_id = pc.user_id
					  AND post_id = '$post_id' ORDER BY date ASC, time ASC";
			$result_comments = $conn->query($query);
			$comments = array();
			while($row_comments = $result_comments->fetch_row()) {
				list($posted_by_id, $user_name, $user_image, $comment, $comment_id, $date, $time) = $row_comments;
				$date = CorrectDate($date, $time);
				$time = CorrectTime($time);
				
				$comment_date = date('M j', strtotime($date)) . ' ' . $time;
				
				if($user_id == $posted_by_id) {
					$owner = true;
				}
				else {
					$owner = false;
				}
				array_push($comments, array("posted_by_id"=>$posted_by_id, "user_name"=>$user_name, "user_image"=>$user_image,
											"comment"=>$comment, "comment_id"=>$comment_id, "comment_date"=>$comment_date,
											"owner"=>$owner));
			}
			array_push($posts, array("post_id"=>$post_id, "blog_id"=>$blog_id, "blog_image"=>$blog_image, "blog_name"=>$blog_name,
									 "owner"=>$owner, "title"=>$title, "comments"=>$comments,
									 "post_date"=>$post_date, "post"=>$post, "viewd_class"=>$viewd_class, 
									 "liked_class"=>$liked_class, "liked_tumb"=>$liked_tumb, "views"=>$views, "likes"=>$likes,
									 "edit_post_text"=>$edit_text));
		}
		$reply = array("success"=>true,
					   "posts"=>$posts,
					   "posts_loaded"=>$load_posts + $posts_loaded
					  );
		echo json_encode($reply);
	}
	else if($action == 'post_comment') {
		if(!is_numeric($post_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Post doesn't exists."
								  )));
		}
		
		//check if post exists
		$query = "SELECT * FROM blog_posts WHERE post_id = '$post_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit(json_encode(array("success"=>false,
								   "error"=>"Post doesn't exists."
								  )));
		}
		strValidate($comment, 1, 500, 'Comment');
		$comment_id = getTimeForId() . $user_id;
		
		//insert comment
		$query = "INSERT INTO post_comments VALUES ('$comment_id', '$post_id', '$user_id', '$comment', CURRENT_DATE, CURRENT_TIME)";
		if($conn->query($query)) {
			$date = CorrectDate(date("Y-m-d"), date('H:i:s'));
			$time = CorrectTime(date('H:i:s'));
			
			$comment_date = date('M j', strtotime($date)) . ' ' . $time;
			
			echo json_encode(array("success"=>true,
								   "comment"=>$comment,
								   "time"=>$comment_date,
								   "comment_id"=>$comment_id,
								   "user_id"=>$user_id
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error. Please try again."
								  )));
		}
	}
	else if($action == 'delete_comment') {
		if(!is_numeric($comment_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Comment doesn't exists."
								  )));
		}
		
		//check if post exists
		$query = "SELECT * FROM post_comments WHERE comment_id = '$comment_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit(json_encode(array("success"=>false,
								   "error"=>"Comment doesn't exists."
								  )));
		}
		else {
			$query = "DELETE FROM post_comments WHERE comment_id = '$comment_id'";
			if($conn->query($query)) {
				echo json_encode(array("success"=>true));
			}
			else {
				exit(json_encode(array("success"=>false,
									   "error"=>"Error. Please try again."
									  )));
			}
		}
	}
	else if($action == 'edit_comment') {
		if(!is_numeric($comment_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Comment doesn't exists."
								  )));
		}
		
		//check if post exists
		$query = "SELECT * FROM post_comments WHERE comment_id = '$comment_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Comment doesn't exists."
								  )));
		}
		else {
			strValidate($comment, 1, 500, 'Comment');
			
			$query = "UPDATE post_comments SET comment = '$comment' WHERE comment_id = '$comment_id'";
			if($conn->query($query)) {
				echo json_encode(array("success"=>true,
									   "comment"=>$comment
									  ));
			}
			else {
				exit(json_encode(array("success"=>false,
									   "error"=>"Error. Please try again."
									  )));
			}
		}
	}
	
	mysqli_close($conn);
?>