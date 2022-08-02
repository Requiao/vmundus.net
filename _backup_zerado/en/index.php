<?php include('head.php'); ?>

<p>
	<div id="col1">
	<?php
		include('user_info.php');
		
		//reward all users
		$current_date = correctDate(date('Y-m-d'), date('H:i:s'));
		$current_time = correctTime(date('H:i:s'));

		$end_date = correctDate(date('2020-06-10'), date('00:00:00'));
		$end_time = correctTime(date('00:00:00'));
		
		$end_date_time = strtotime($end_date . ' ' . $end_time);
		$current_date_time = strtotime($current_date . ' ' . $current_time);
		
		$reward_type = 16;
		
		if($end_date_time >= $current_date_time) {
			$query = "SELECT * FROM user_rewards WHERE user_id = '$user_id' AND type_id = '$reward_type'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO user_rewards VALUES ('$reward_type', '$user_id', FALSE, CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
			}
		}
	?>
	</div>
	
	<div id="weekly_missions">
		<p id="wm_heading">Weekly Missions</p>
		<?php
			//get missions id
			$query = "SELECT mission_id FROM weekly_missions ORDER BY date DESC, TIME DESC LIMIT 1";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($mission_id) = $row;
			
			//exp earned
			$query = "SELECT experience_earned FROM users_weekly_missions_progress WHERE user_id = '$user_id'
					  AND mission_id = '$mission_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($experience_earned) = $row;
			}
			else {
				$experience_earned = 0;
			}
			
			//get max required exp for 10 levels
			$query = "SELECT MAX(required_experience), MAX(level_id)
					  FROM weekly_missions_base_rewards 
					  WHERE required_experience <= '$experience_earned' + 1500";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($max_required_experience, $max_level) = $row;
			
			$base = 100 / $max_required_experience;
			$width = $base * $experience_earned;
		
			//get rewards
			$query = "SELECT wmr.level_id, amount, product_icon, required_experience, product_name, IFNULL(collected, FALSE) 
					  FROM product_info pi, weekly_missions_base_rewards wmbr, weekly_missions_rewards wmr
					  LEFT JOIN collected_weekly_mission_rewards cwmr ON
					  cwmr.mission_id = wmr.mission_id AND user_id = '$user_id' AND cwmr.level_id = wmr.level_id
					  WHERE pi.product_id = wmr.product_id AND wmr.mission_id = '$mission_id' 
					  AND wmr.level_id = wmbr.level_id AND wmbr.level_id <= $max_level";
			$result_rewards = $conn->query($query);
			while($row_rewards = $result_rewards->fetch_row()) {
				list($level_id, $amount, $product_icon, $required_experience, $product_name, $collected) = $row_rewards;
				
				if($collected) {
					$can_collect = 'collected';
				}
				else {
					$can_collect = '';
				}
				
				$left = $base * $required_experience - 2.5;
				
				if($required_experience <= $experience_earned) {
					$rewards .= "\n\t\t\t\t" . '<div class="wmpbd_icon_div" style="margin-left: ' . $left . '%;">' .
											   '<p hidden>' . $level_id . '</p>' .
											   '<img src="../product_icons/' . $product_icon . '">' .
											   '<p class="wmpbd_decription">' . $amount . ' ' . $product_name . 
											   ' (' . $can_collect . ')</p>' .
											   '</div>';
				}
				else {
					$rewards .= "\n\t\t\t\t" . '<div class="wmpbd_icon_div" style="margin-left: ' . $left . '%;">' .
											   '<span class="fa fa-question-circle"></span>' .
											   '<p class="wmpbd_decription">Earn ' . $required_experience . ' experience points.</p>' .
											   '</div>';
				}
			}
			
			echo "\n\t\t" . '<div id="weekly_mission_progress_bar_div">' .
				 "\n\t\t\t" . '<div id="wmpbd_progress" style="width: ' . $width . '%;"></div>' .
				 "\n\t\t" . '</div>' .
				 "\n\t\t" . '<div id="wmpbd_icons">' . $rewards . 
				 "\n\t\t" . '</div>';
				 
			echo "\n\t\t" . '<p id="wmpbd_progress_details">Progress: ' . $experience_earned . '/' . $max_required_experience . '</p>';
		?>
	</div>
	
	<div id="col2">
		<?php
			//get user country
			$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($citizenship) = $row;

			/* Aliens' arrival */
			/*$date1 = new DateTime(date('Y-m-d G:i:s', strtotime("2020-02-21 6:00:00")));
			$date2 = new DateTime(date('Y-m-d G:i:s', strtotime(date('Y-m-d G:i:s'))));

			$diff = date_diff($date1,$date2);
			$end_in_days = $diff->format("%d");
			$ends_in_hours = $diff->format("%h");
			$total_hours = ($end_in_days * 24) + $ends_in_hours;
			$arrive_in = $total_hours . ':' . $diff->format('%I:%S');


			echo'<div id="aliens_arrival_div">' .
				'<img src="../img/aliens_arrival.png" alt="aliens will arrive soon">' .
				//'<p id="ard_title">Aliens will arrive soon!</p>' .
				'<p id="ard_title">Aliens arrived! RUN AND HIDE!</p>' .
				//'<p id="ard_timeout">'. $arrive_in . '</p>' .
				//'<p id="ard_timeout">THEY ARE HERE!</p>' .
				'</div>';*/

			echo'<div id="aliens_arrival_div">' .
				'<img src="../img/kick_aliens.png" alt="kick aliens">' .
				'<p id="ard_title">Time to kick out these cruel creatures from the earth!</p>' .
				'<p id="ard_note">Get +1 exp and 0.001 Gold per 1 damage for attacking regions occupied by aliens.</p>' .
				'</div>';

			/* Admin Blog */
			$query = "SELECT post_id, title FROM blog_posts WHERE blog_id = 1 AND
					  date > DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY) ORDER BY date DESC, time DESC LIMIT 1";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($post_id, $title) = $row;
				echo'<div id="news">' .
					'<img src="../img/news.png" alt="news">' .
				    '<a id="n_title" href=blog_info?post_id=' . $post_id . 
					'><i class="fa fa-newspaper-o" aria-hidden="true"></i> ' . $title . '</a>' .
				    '</div>';
			}
			
			/* elections */
			//president elections
			//display activated president elections
			$query = "SELECT election_id, start_date, start_time FROM election_info WHERE country_id = '$citizenship'
				      AND ended = 0 AND type = 1";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($election_id, $start_date, $start_time) = $row;
				
				//correct date/time
				$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), $start_time); //will end in 1 day
				$end_time = correctTime($start_time);

				//find out when expires and display
				echo "\n\t\t\t" . '<a class="elections_div" href="elections">' .
					 "\n\t\t\t" . '<p class="ed_elec_head">' . $lang['presidential_elections'] . '</p>';
				expireTime($end_date, $end_time, "End");
				echo "\n\t\t\t" . '</a>';
			}

			//display scheduled president elections
			$query = "SELECT election_id, start_date, start_time FROM election_info WHERE country_id = '$citizenship'
					  AND can_participate = 1 AND type = 1";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($election_id, $start_date, $start_time) = $row;
				
				//correct date/time
				$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), $start_time); //will start in 1 days
				$end_time = correctTime($start_time);

				//find out when starts and display
				echo "\n\t\t\t" . '<a class="elections_div" href="elections">' .
					 "\n\t\t\t" . '<p class="ed_elec_head">' . $lang['presidential_elections'] . '</p>';
				expireTime($end_date, $end_time, "Start");
				echo "\n\t\t\t" . '</a>';
			}				
				
			//display activated congress elections
			$query = "SELECT election_id, start_date, start_time FROM election_info WHERE country_id = '$citizenship'
					  AND ended = 0 AND type = 3";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($election_id, $start_date, $start_time) = $row;
				
				//correct date/time
				//will end in 1 day
				$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), $start_time); 
				$end_time = correctTime($start_time);
				
				//find out when expires and display
				echo "\n\t\t\t" . '<a class="elections_div" href="elections">' .
					 "\n\t\t\t" . '<p class="ed_elec_head">' . $lang['elections_to_congress'] . '</p>';
				expireTime($end_date, $end_time, "End");
				echo "\n\t\t\t" . '</a>';
			}
			
			//display scheduled congress elections
			$query = "SELECT election_id, start_date, start_time FROM election_info WHERE country_id = '$citizenship'
					  AND can_participate = 1 AND type = 3";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($election_id, $start_date, $start_time) = $row;
				
				//correct date/time
				//will start in 1 days
				$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), date('H:i:s', strtotime($start_time))); 
				$end_time = correctTime(date('H:i:s', strtotime($start_time)));
				
				
				//find out when expires and display
				echo "\n\t\t\t" . '<a class="elections_div" href="elections">' .
					 "\n\t\t\t" . '<p class="ed_elec_head">' . $lang['elections_to_congress'] . '</p>';
				expireTime($end_date, $end_time, "Start");
				echo "\n\t\t\t" . '</a>';
			}
				
				
			function expireTime($end_date, $end_time, $end_start) {
				global $conn;
				$current_date = correctDate(date('Y-m-d'), date('H:i:s'));
				$current_time = correctTime(date('H:i:s'));

				$end_date_time = strtotime($end_date . ' ' . $end_time);
				$current_date_time = strtotime($current_date . ' ' . $current_time);

				$date1 = new DateTime($current_date . ' ' . $current_time);
				$date2 = new DateTime($end_date . ' ' . $end_time);
				$diff = date_diff($date1,$date2);
				$end_in_days = $diff->format("%d");
				$ends_in_hours = $diff->format("%h");
				$total_hours = ($end_in_days * 24) + $ends_in_hours;
				$time = $total_hours . ':' . $diff->format('%I:%S');
				
				//do not show if elections supposed to end but still scheduled
				if($end_date_time <= $current_date_time) {
					return;
				}
				
				echo "\n\t\t\t\t" . '<p class="elections_end_in">' . $end_start . ' in </p>' .
					 "\n\t\t\t\t" . '<p class="elections_clock">' . $time . '</p>';
				return;
			}
		?>
		
		<div id="order_div">
			<a id="order_div_head" href="<?php echo $is_governor?'manage_battles':''; ?>"><i class="glyphicon glyphicon-screenshot" 
			aria-hidden="true"></i><?php echo $lang['country_order']; ?></a>
			<?php
				//batle info
				$query = "SELECT co.battle_id, attacker_id, defender_id, IFNULL(co.currency_id, 0), damage_price, budget, used_budget,
						  region_name, country_name
						  FROM country c, regions r, battles b, country_orders co LEFT JOIN battle_budget bb 
						  ON bb.country_id = co.country_id AND bb.battle_id = co.battle_id
						  WHERE b.battle_id = co.battle_id AND co.country_id = '$citizenship' AND r.region_id = b.region_id
						  AND c.country_id = for_country_id";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($battle_id, $attacker_id, $defender_id, $currency_id, $damage_price, $budget, $used_budget, 
						 $region_name, $country_name) = $row;
					
					$remaining_budget = $budget - $used_budget;
					
					echo "\n\t\t\t" . '<div id="budget_info_div">' .
						 "\n\t\t\t\t" . '<p id="remaining_budget">' . number_format($remaining_budget, 2, ".", " ") . 
										' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t\t" . '<p id="damage_price">' . number_format($damage_price, 2, ".", " ") . 
										' ' . $currency_abbr . ' for 100D</p>' .
						 "\n\t\t\t" . '</div>';
					
					if($citizenship == $attacker_id) {
						echo "\n\t\t\t" . '<a class="join_battle_btn" id="od_attacker_btn" href="battle?currency_id=' . 
										  $currency_id . '&battle_id=' . 
										  $battle_id . '&side=attacker">' . $lang['join_attacker'] . '</a>';
					}
					else if ($citizenship == $defender_id) {
						echo "\n\t\t\t" . '<a class="join_battle_btn" id="od_attacker_btn" href="battle?currency_id=' . 
										  $currency_id . '&battle_id=' . 
										  $battle_id . '&side=defender">' . $lang['join_defender'] . '</a>';
					}

					echo "\n\t\t\t" . '<p id="battle_side">For ' . $country_name . ' in ' . $region_name . '</p>';
				}
				else {
					echo "\n\t\t\t" . '<p id="od_no_order">There is no battle order.</p>';
				}
			?>
		</div>
		
		<?php
			//latest posts
			echo "\n\t\t" .'<div id="latest_posts_div">' .
				 "\t\t\t\t" . '<a id="latest_posts_div_head" href="latest_posts"><i class="fa fa-file-text-o" aria-hidden="true"></i> ' .
							  'Top Posts</a>';
			$query = "SELECT bp.post_id, COUNT(CASE WHEN liked = true THEN 1 END) AS likes, 
					  COUNT(CASE WHEN viewed = true THEN 1 END) AS views,
					  title, blog_name, ub.user_id, user_name, user_image, bp.date, bp.time
					  FROM blog_posts bp, post_likes_views plv, user_blog ub, users u, user_profile up
					  WHERE plv.post_id = bp.post_id AND ub.blog_id = bp.blog_id
					  AND up.user_id = ub.user_id AND u.user_id = ub.user_id
					  AND DATE_ADD(TIMESTAMP(bp.date, bp.time), INTERVAL 10 DAY) >= NOW()
					  GROUP BY post_id
					  ORDER BY likes DESC, views DESC
					  LIMIT 12";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($post_id, $likes, $views, $title, $blog_name, $blogger_id, $blogger_name, $blogger_image, $date, $time) = $row;
				
				$date = correctDate($date, $time);
				$time = correctTime($time);
				$post_date = date('M j', strtotime($date)) . ' ' . $time;
				
				echo "\n\t\t\t" . '<div class="lpd_short_descript">' .
					 "\n\t\t\t\t" . '<img src="../user_images/' . $blogger_image . '" alt="user image">' .
					 "\n\t\t\t\t" . '<a class="lpd_blogger_name" href="user_profile?id=' . $blogger_id . '">' . $blogger_name . '</a>' .
					 "\n\t\t\t\t" . '<a class="lpd_title" href="blog_info?post_id=' . $post_id . '">' .
									$title . '</a>' .
					 "\n\t\t\t\t" . '<p class="lpd_time">' . $post_date . '</p>' .
					 "\n\t\t\t\t" . '<div class="lpd_post_views_likes_div">' .
					 "\n\t\t\t\t\t" . '<p class="likes"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> ' . $likes . '</p>' .
					 "\n\t\t\t\t\t" . '<p class="views"><i class="fa fa-eye" aria-hidden="true"></i> ' . $views . '</p>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
		?>
		
	</div>
	
	<div id="col3">
		<div id="chat">
			<?php
				//get favorite chat
				$query = "SELECT fc.chat_id, chat_name FROM favorite_chat fc, chat_info cm WHERE user_id = '$user_id' AND fc.chat_id = cm.chat_id";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$query = "SELECT COUNT(ucm.message_id) FROM unread_chat_messages ucm, chat c
							  WHERE c.message_id = ucm.message_id AND user_id = '$user_id' GROUP BY user_id";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($total_new_chat_messages) = $row;
					
					echo "\n\t\t\t" . '<p id="chat_name">' .
					     "\n\t\t\t\t" . '<i class="fa fa-comments" aria-hidden="true"></i> ' . 
						 "\n\t\t\t\t" . 'Select Chat'  .
						 "\n\t\t\t\t" . '<i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' .
						 "\n\t\t\t" . '</p>' .
						 "\n\t\t\t" . '<p id="total_new_chat_messages">' . $total_new_chat_messages . '</p>' .
						 "\n\t\t\t" . '<p id="chat_id" hidden></p>';
				}
				else {
					$row = $result->fetch_row();
					list($chat_id, $chat_name) = $row;
					
					echo "\n\t\t\t" . '<p id="chat_name">' .
									  '<i class="fa fa-comments" aria-hidden="true"></i> ' . 
									   $chat_name  .
									  ' <i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' .
						 "\n\t\t\t" . '</p>' .
						 "\n\t\t\t" . '<p id="total_new_chat_messages">' . $total_new_chat_messages . '</p>' .
						 "\n\t\t\t" . '<p id="chat_id" hidden>' . $chat_id . '</p>';
				}
				
				echo "\n\t\t\t" . '<div id="chat_list_menu" class="scroll_style">';
				
				$query = "SELECT chat_name, ci.chat_id, COUNT(ucm.message_id) FROM chat_info ci LEFT JOIN chat c ON
						  c.chat_id = ci.chat_id LEFT JOIN unread_chat_messages ucm ON
						  c.message_id = ucm.message_id AND user_id = '$user_id'
						  WHERE ci.chat_id IN 
						 (SELECT chat_id FROM chat_members WHERE user_id = '$user_id') 
						  GROUP BY chat_id ORDER BY chat_name";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($chat_name, $chat_id, $new_messages) = $row;

					echo "\n\t\t\t\t" . '<div id="ci_' . $chat_id . '">' .
						 "\n\t\t\t\t\t" . '<p class="chat_name">' . $chat_name . 
										  '<span id="cinm_' . $chat_id . '" class="ci_new_messages">' . $new_messages . '</span>' .
										  '</p>' .
						 "\n\t\t\t\t\t" . '<p class="chat_id" hidden>' . $chat_id . '</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div id="messages" class="scroll_style"></div>';
			?>
			
			
			<p hidden></p>
			<textarea id="enter_message" maxlength="500"></textarea>
			<span id="send_message" class="glyphicon glyphicon-send"></span>
		</div>
	</div>
	
	<?php
		include('right_side.php');
	?>
	
	<div id="blog">
		<?php
			/* display all posts */
			$select_posts = 5;
			echo "\n\t\t" . '<p id="posts_loaded" hidden>' . $select_posts . '</p>';
			$query = "SELECT post_id, title, post, date, time, blog_image, blog_name, ub.blog_id, user_name, ub.user_id,
					  edit_date, edit_time 
					  FROM blog_posts bp, user_blog ub, users u
					  WHERE ub.blog_id IN 
					 (SELECT blog_id FROM blog_subscribers WHERE user_id = '$user_id') 
					  AND bp.blog_id = ub.blog_id AND u.user_id = ub.user_id
					  ORDER BY date DESC, time DESC  LIMIT 0, $select_posts";
			$result_posts = $conn->query($query);
			while($row_posts = $result_posts->fetch_row()) {
				list($post_id, $title, $post, $date, $time, $blog_image, $blog_name, $blog_id, $blogger_name, 
					 $blogger_id, $edit_date, $edit_time) = $row_posts;
				
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
				
				echo "\n\t\t" . '<div class="post_details" id="post_' . $post_id . '">' .
					 "\n\t\t\t" . '<p class="get_post_id" hidden>' . $post_id . '</p>' .
					 "\n\t\t" . '<img class="post_blog_img" src="../blog_images/' . $blog_image . '">' .
					 "\n\t\t\t" . '<a class="blog_name_link" href="blog_info?blog_id=' . $blog_id . 
								  '">' . $blog_name . '</i></a>' .
					 "\n\t\t" . '<p class="post_date">' . $post_date . '</p>' .
					 "\n\t\t\t" . '<p class="edit_post_date">' . $edit_post_date . '</p>' .
					 "\n\t\t\t" . '<a class="post_title" href="blog_info?blog_id=' . $blog_id . '&post_id=' . $post_id . 
								  '">' . $title . '</a>' .
					 "\n\t\t\t" . '<div class="post_div">' . html_entity_decode($post, ENT_QUOTES) . '</div>' .
					 "\n\t\t\t" . '<div class="pd_blog_author">' .
					 "\n\t\t\t\t" . '<p>Written by:</p>' .
					 "\n\t\t\t\t" . '<a class="pd_blogger" href="user_profile?id=' . $blogger_id . '">' . $blogger_name . '</a>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div class="post_views_likes_div">' .
					 "\n\t\t\t\t" . '<p class="likes ' . $liked_class . '">' .
									'<i class="' . $liked_tumb . '" aria-hidden="true"></i> ' . 
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
		?>
	</div>
	
</main>

<?php include('footer.php'); ?>