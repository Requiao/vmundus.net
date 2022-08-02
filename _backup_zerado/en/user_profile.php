<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
		require_once('../php_functions/get_user_level.php');
	?>
	
	</div>
	
	<div id="container">	
		<?php
			$profile_id = htmlentities(stripslashes(trim($_GET['id'])), ENT_QUOTES);
			if(empty($profile_id)) {
				$is_user_exist = false;
				echo "\n\t\t" . '<p>User doesn\'t exist.</p>';
			}
			else {
				$is_user_exist = true;
				$query = "SELECT user_name, user_image, IFNULL(level_id, 0)
						  FROM users u, user_profile up LEFT JOIN user_exp_levels uxl 
						  ON uxl.experience <= up.experience
						  WHERE u.user_id = '$profile_id' AND up.user_id = u.user_id 
						  ORDER BY level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 0) {
					$is_user_exist = false;
					echo "\n\t\t" . '<p>User doesn\'t exist.</p>';
				}
			}
			if($is_user_exist) {
				$row = $result->fetch_row();
				list($user_name, $user_image, $level_id) = $row;
				
				echo "\n\t\t" . '<p id="profile_id">' . $profile_id . '</p>';
				
				//check if friend
				if($user_id != $profile_id) {
					$query_f = "SELECT * FROM friends WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
							  (user_id = '$profile_id' AND friend_id = '$user_id')";
					$result_f = $conn->query($query_f);
					if($result_f->num_rows == 0) {
						//check if request already sent
						$query_f = "SELECT * FROM request_friendship WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
								   (user_id = '$profile_id' AND friend_id = '$user_id')";
						$result_f = $conn->query($query_f);
						if($result_f->num_rows == 0) {
							echo "\n\t\t" . '<div id="add_to_friends">' .
								 "\n\t\t\t" . '<i class="fa fa-plus" aria-hidden="true"></i>' .
								 "\n\t\t\t" . '<i class="fa fa-user" aria-hidden="true"></i>' .
								 "\n\t\t" . '</div>';
						}
					}
					else {
						echo "\n\t\t" . '<div id="remove_from_friends">' .
							 "\n\t\t\t" . '<i class="fa fa-minus" aria-hidden="true"></i>' .
							 "\n\t\t\t" . '<i class="fa fa-user" aria-hidden="true"></i>' .
							 "\n\t\t" . '</div>';
					}
				}
				
				//send message
				echo "\n\t\t" . '<p id="compose_message"><i class="fa fa-envelope" aria-hidden="true"></i></p>';
				
				//if banned. display ban details.
				$query = "SELECT date_until, time_until, ban_name FROM banned_users bu, user_ban_points ubp, ban_points_info bpi 
						  WHERE bu.user_id = '$profile_id'
						  AND ubp.user_id = bu.user_id AND bpi.ban_id = ubp.ban_id
						  AND TIMESTAMP(date_until, time_until) > NOW() ORDER BY date_until DESC, time_until DESC, 
						  date DESC, time DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows > 0) {
					$row = $result->fetch_row();
					list($date_until, $time_until, $extra_description) = $row;
					
					$end_on = "$date_until $time_until";
		
					$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
					$date2 = new DateTime($end_on);
					$diff = date_diff($date1,$date2);
					$days = $diff->format("%a");
					$time = $diff->format("%H:%I:%S");

					echo "\n\t\t" . '<div id="ban_details">' .
						 "\n\t\t\t" . '<p id="ban_details_head">Account is banned for ' . $days . ' days ' . $time . '</p>' .
						 "\n\t\t\t" . '<p id="ban_details_reason">Reason: <i>' . $extra_description . '</i></p>' .
						 "\n\t\t" . '</div>';
				}
				
				$query = "SELECT country_name, country_id, flag FROM country WHERE country_id = (SELECT citizenship FROM user_profile 
				          WHERE user_id = '$profile_id')";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($country_name, $citizenship, $flag) = $row;
				
				//check if has assigned title
				$has_title = false;
				$query = "SELECT title FROM titles WHERE title_id = (SELECT title_id FROM user_titles WHERE user_id = '$profile_id')";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$is_governor = true;
					$row = $result->fetch_row();
					list($title) = $row;
					$has_title = true;
				}
				
				//check if governor
				$is_governor = false;
				$query = "SELECT name FROM government_positions WHERE position_id = 
						  (SELECT position_id FROM country_government WHERE user_id = '$profile_id')";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$is_governor = true;
					$row = $result->fetch_row();
					list($position_name) = $row;
				}
				else { //check if congressman
					$query = "SELECT name FROM government_positions WHERE position_id =
							 (SELECT 3 FROM congress_members WHERE user_id = '$profile_id')";
					$result = $conn->query($query);
					if($result->num_rows == 1) { 
						$is_governor = true;
						$row = $result->fetch_row();
						list($position_name) = $row;
					}
				}
				
				//check if online
				$query = "SELECT * FROM users WHERE DATE_ADD(TIMESTAMP(last_active, last_active_time), INTERVAL 2 MINUTE) >= NOW() 
						  AND user_id = '$profile_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$is_online = 'user_online';
				}
				else {
					$is_online = 'user_ofline';
				}
				
				$collect = '';
				if($profile_id == $user_id) {
					$query = "SELECT * FROM user_level_rewards WHERE collected = FALSE AND user_id = '$user_id'";
					$result = $conn->query($query);
					if($result->num_rows != 0) {
						$collect = '<p id="collect_level_rewards">' . $lang['collect'] . '</p>';
					}
				}
				
				echo "\n\t\t" . '<div id="profile_info">' .
					 "\n\t\t\t" . '<p id="profile_name">' . $user_name .
								  '<i class="fa fa-circle" id="' . $is_online . '" aria-hidden="true"></i></p>' .
					 "\n\t\t\t" . '<p class="user_level">Level ' . $level_id . '</p>' .
					 "\n\t\t\t" . $collect .
					 "\n\t\t\t" . '<img id="profile_img" src="../user_images/' . $user_image . 
								  '?' . filemtime('../user_images/' . $user_image) . '" alt="user image">' .
					 "\n\t\t\t" . '<div id="pi_info">';
				
				if($has_title) {
					echo "\n\t\t\t\t" . '<div id="pi_title">' .
						 "\n\t\t\t\t\t" . '<p><span class="fa fa-empire"></span>' . $lang['title'] . '</p>' .
						 "\n\t\t\t\t\t" . '<p id="title">' . $title . '</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				
				if($is_governor) {
					echo "\n\t\t\t\t" . '<div id="pi_governor">' .
						 "\n\t\t\t\t\t" . '<p><span class="fa fa-university"></span>' . $lang['position'] . '</p>' .
						 "\n\t\t\t\t\t" . '<p>' . $position_name . '</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				
				if($profile_id == $user_id) {
					$travel = '<p id="travel"><span class="fa fa-plane" aria-hidden="true"></span>' . $lang['travel'] . '</p>';
					$cz_change = '<p id="cz_change"><span class="fa fa-edit" aria-hidden="true"></span>' . $lang['change'] . '</p>';
				}
				else {
					$travel = "";
					$cz_change = "";
				}
				
				//citizenship
				echo "\n\t\t\t\t" . '<div id="pi_citizenship">' .
					 "\n\t\t\t\t\t" . '<p><span class="fa fa-id-card-o"></span>' . $lang['citizenship'] . '</p>' .
					 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '" alt="' . $country_name . '">' .
					 "\n\t\t\t\t\t" . '<a href="country?country_id=' . $citizenship . '">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t\t" . $cz_change .
					 "\n\t\t\t\t" . '<div id="pi_blog">' .
					 "\n\t\t\t\t\t" . '<p><span class="fa fa-book"></span>' . $lang['blogs'] . '</p>';
				
				//blogs
				$query = "SELECT blog_name, blog_id FROM user_blog WHERE user_id = '$profile_id' ORDER BY blog_name LIMIT 5";
				$result = $conn->query($query);
				$x = 1;
				while($row = $result->fetch_row()) {
					list($blog_name, $blog_id) = $row;
					if(iconv_strlen($blog_name) >= 15) {
						$blog_name = substr($blog_name, 0,15);
					}
					echo  "\n\t\t\t\t\t" . '<a href="blog_info?blog_id=' . $blog_id . '">' . $x . '. ' . $blog_name . '</a>';
					$x++;
				}
				echo "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>' . 
					 "\n\t\t" . '</div>';
			
				if($profile_id == $user_id) {
					/* user managment */
					echo "\n\t\t" . '<div id="profile_menu">' .
						 "\n\t\t\t" . '<p id="user_achievements">' . $lang['achievements'] . '</p>' .
						 "\n\t\t\t" . '<p id="user_currency">' . $lang['currency'] . '</p>' .
						 "\n\t\t\t" . '<p id="user_friends">' . $lang['friends'] . '</p>' .
						 "\n\t\t\t" . '<p id="user_referals">' . $lang['referrals'] . '</p>' .
						 "\n\t\t" . '</div>';
				}
				else {
					/* for other users */
					echo "\n\t\t" . '<div id="user_profile_menu">' .
						 "\n\t\t\t" . '<p id="user_achievements">' . $lang['achievements'] . '</p>' .
						 "\n\t\t\t" . '<p id="user_friends">' . $lang['friends'] . '</p>' .
						 "\n\t\t" . '</div>';
				}

				/* achievements */
				echo "\n\t\t" . '<div id="user_achievements_div">' .
					 "\n\t\t\t" . '<div id="achievement_divs">';
				
				//display all achievements
				$query = "SELECT achievement_name, achievement_description, achievement_img, na_achievements_img, 
						  IFNULL(collected + earned, 0), a.achievement_id
						  FROM achievements a LEFT JOIN user_achievements ua ON ua.achievement_id = a.achievement_id
						  AND user_id = '$profile_id'
						  ORDER BY a.achievement_id";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($achievement_name, $achievement_description, $achievement_img, $na_achievements_img, $total,
						 $achievement_id) = $row;
					if($total == 0) {
						$achievement_img = $na_achievements_img;
					}
					echo "\n\t\t\t\t" . '<div class="achiev_divs">' .
						 "\n\t\t\t\t\t" . '<img src="../img/' . $achievement_img . '">' .
						 "\n\t\t\t\t\t" . '<div class="achiev_description">' .
						 "\n\t\t\t\t\t\t" . '<p class="ad_head">' . $achievement_name . '</p>' .
						 "\n\t\t\t\t\t\t" . '<p class="ad_desc">' . $achievement_description . '</p>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p class="achiev_count">' . $total . '</p>';
					if($user_id == $profile_id) {
						$query = "SELECT earned FROM user_achievements WHERE user_id = '$user_id' 
								  AND achievement_id = '$achievement_id'";
						$result_rew = $conn->query($query);
						$row_rew = $result_rew->fetch_row();
						list($earned) = $row_rew;
						if($earned > 0) {
							echo "\n\t\t\t\t" . '<p class="colect_achiev_reward">' . $lang['collect'] . '</p>' .
								 "\n\t\t\t\t" . '<p class="achiev_reward_id" hidden>' . $achievement_id . '</p>';
						}
					}
					echo "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
				
				//display level progress
				$query = "SELECT IFNULL(level_id, 0), up.experience, IFNULL(uxl.experience, 0),
						 (SELECT level_id FROM user_exp_levels ORDER BY level_id DESC LIMIT 1)
						  FROM user_profile up LEFT JOIN user_exp_levels uxl ON uxl.experience <= up.experience
						  WHERE up.user_id = '$profile_id'
						  ORDER BY level_id DESC LIMIT 1";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($current_user_level, $experience, $prev_experience, $max_level) = $row;
				
				if($max_level == $current_user_level) {//reached max level
					$progress == 100;
					$next_experience = $prev_experience;
				}
				else {
					$query = "SELECT experience FROM user_exp_levels 
							  WHERE level_id = '$current_user_level' + '1' LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($next_experience) = $row;
					
					$progress = (100 / ($next_experience - $prev_experience)) * ($experience - $prev_experience);
				}
				
				echo "\n\t\t\t" . '<div class="achivements_progress_div">' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_head">Experience progress</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_desc">Experience</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_reach">' . number_format($experience, '0', '', ' ') . '</p>' .
					 "\n\t\t\t\t" . '<div class="ahiev_progress_bar">' .
					 "\n\t\t\t\t\t" . '<div class="apb_progress" style="width:' . $progress . '%;"></div>' .
					 "\n\t\t\t\t\t" . '<p>' . number_format(($experience - $prev_experience), '0', '', ' ') . 
									  '/' . number_format(($next_experience - $prev_experience), '0', '', ' ') . '</p>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
				
				//display Hard Worker progress
				$query = "SELECT uhwr.level_id, wq.quantity, hwl.working_cycles,
						 (SELECT level_id FROM hard_worker_levels ORDER BY level_id DESC LIMIT 1)
						  FROM work_quantity wq, hard_worker_levels hwl, user_hard_worker_rewards uhwr
						  WHERE wq.user_id = '$profile_id' AND uhwr.user_id = wq.user_id AND hwl.level_id = uhwr.level_id
						  ORDER BY uhwr.level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($current_level, $worked_quantity, $prev_working_cycles, $max_level) = $row;
				}
				else {
					$query = "SELECT hwl.working_cycles, (SELECT level_id FROM hard_worker_levels ORDER BY level_id DESC LIMIT 1)
							  FROM hard_worker_levels hwl WHERE hwl.level_id = 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($next_working_cycles, $max_level) = $row;
					$prev_working_cycles = 0;
					
					$query = "SELECT quantity FROM work_quantity WHERE user_id = '$profile_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$row = $result->fetch_row();
						list($worked_quantity) = $row;
					}
					else {
						$progress = 0;
						$worked_quantity = 0;
					}
					
					$current_level = 0;
				}
				
				if($max_level == $current_level) {//reached max level
					$progress == 100;
					$next_working_cycles = $prev_working_cycles;
				}
				else {
					$query = "SELECT working_cycles FROM hard_worker_levels 
							  WHERE level_id = '$current_level' + '1' LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($next_working_cycles) = $row;
					
					$progress = (100 / ($next_working_cycles - $prev_working_cycles)) * ($worked_quantity - $prev_working_cycles);
				}
				
				echo "\n\t\t\t" . '<div class="achivements_progress_div">' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_head">' . $lang['hard_worker_progress'] . '</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_desc">' . $lang['number_of_times_worked'] . '</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_reach">' . number_format($worked_quantity, '0', '', ' ') . '</p>' .
					 "\n\t\t\t\t" . '<div class="ahiev_progress_bar">' .
					 "\n\t\t\t\t\t" . '<div class="apb_progress" style="width:' . $progress . '%;"></div>' .
					 "\n\t\t\t\t\t" . '<p>' . number_format(($worked_quantity - $prev_working_cycles), '0', '', ' ') . 
									  '/' . number_format(($next_working_cycles - $prev_working_cycles), '0', '', ' ') . '</p>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
				
				//Display battle legend progress
				$query = "SELECT ublr.level_id, utd.damage, bll.total_damage,
						 (SELECT level_id FROM battle_legend_levels ORDER BY level_id DESC LIMIT 1)
						  FROM user_total_damage utd, battle_legend_levels bll, user_battle_legend_rewards ublr
						  WHERE utd.user_id = '$profile_id' AND ublr.user_id = utd.user_id AND bll.level_id = ublr.level_id
						  ORDER BY ublr.level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($current_level, $user_damage, $prev_total_damage, $max_level) = $row;
				}
				else {
					$query = "SELECT level_id FROM battle_legend_levels ORDER BY level_id DESC LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($max_level) = $row;
					$prev_total_damage = 0;
					
					$query = "SELECT damage FROM user_total_damage WHERE user_id = '$profile_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$row = $result->fetch_row();
						list($user_damage) = $row;
					}
					else {
						$progress = 0;
						$user_damage = 0;
					}
					
					$current_level = 0;
				}
				
				if($max_level == $current_level) {//reached max level
					$progress == 100;
					$next_total_damage = $prev_total_damage;
				}
				else {
					$query = "SELECT total_damage FROM battle_legend_levels 
							  WHERE level_id = '$current_level' + '1' LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($next_total_damage) = $row;
					
					$progress = (100 / ($next_total_damage - $prev_total_damage)) * ($user_damage - $prev_total_damage);
				}
				
				echo "\n\t\t\t" . '<div class="achivements_progress_div">' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_head">' . $lang['battle_legend_progress'] . '</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_desc">' . $lang['total_damage'] . '</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_reach">' . number_format($user_damage, '2', '.', ' ') . '</p>' .
					 "\n\t\t\t\t" . '<div class="ahiev_progress_bar">' .
					 "\n\t\t\t\t\t" . '<div class="apb_progress" style="width:' . $progress . '%;"></div>' .
					 "\n\t\t\t\t\t" . '<p>' . number_format(($user_damage - $prev_total_damage), '2', '.', ' ') . 
									  '/' . number_format(($next_total_damage - $prev_total_damage), '2', '.', ' ') . '</p>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
				
				//Display media tycoon progress
				$query = "SELECT ubsr.level_id, 
						 (SELECT COUNT(*) FROM blog_subscribers WHERE blog_id IN
						 (SELECT blog_id FROM user_blog WHERE user_id = '$profile_id')),
						  ubsl.total_subscribers,
						 (SELECT level_id FROM user_blog_subscribers_levels ORDER BY level_id DESC LIMIT 1)
						  FROM user_blog_subscribers_levels ubsl, user_blog_subscribers_rewards ubsr
						  WHERE ubsr.user_id = '$profile_id' AND ubsl.level_id = ubsr.level_id
						  ORDER BY ubsr.level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($current_level, $user_subscribers, $prev_total_subscribers, $max_level) = $row;
				}
				else {
					$query = "SELECT level_id FROM user_blog_subscribers_levels ORDER BY level_id DESC LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($max_level) = $row;
					$prev_total_subscribers = 0;
					
					$query = "(SELECT COUNT(*) FROM blog_subscribers WHERE blog_id IN
							  (SELECT blog_id FROM user_blog WHERE user_id = '$profile_id'))";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$row = $result->fetch_row();
						list($user_subscribers) = $row;
					}
					else {
						$progress = 0;
						$user_subscribers = 0;
					}
					
					$current_level = 0;
				}
				
				if($max_level == $current_level) {//reached max level
					$progress == 100;
					$next_total_subscribers = $prev_total_subscribers;
				}
				else {
					$query = "SELECT total_subscribers FROM user_blog_subscribers_levels 
							  WHERE level_id = '$current_level' + '1' LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($next_total_subscribers) = $row;
					
					$progress = (100 / ($next_total_subscribers - $prev_total_subscribers)) 
								* ($user_subscribers - $prev_total_subscribers);
				}
				
				echo "\n\t\t\t" . '<div class="achivements_progress_div">' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_head">Media Tycoon Progress</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_desc">Total subscribers</p>' .
					 "\n\t\t\t\t" . '<p class="ahiev_det_reach">' . $user_subscribers . '</p>' .
					 "\n\t\t\t\t" . '<div class="ahiev_progress_bar">' .
					 "\n\t\t\t\t\t" . '<div class="apb_progress" style="width:' . $progress . '%;"></div>' .
					 "\n\t\t\t\t\t" . '<p>' . ($user_subscribers - $prev_total_subscribers) . 
									  '/' . ($next_total_subscribers - $prev_total_subscribers) . '</p>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
				
				//user bonuses
				$query = "SELECT work_bonus, millitary_bonus FROM bonus_per_user_level";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($work_bonus, $millitary_bonus) = $row;
				$productivity_bonus = $current_user_level * $work_bonus;
				$damage_bonus = $millitary_bonus * $current_user_level;
				
				echo "\n\t\t\t" . '<div id="user_bonus_summary">' .
					 "\n\t\t\t\t" . '<p id="ubm_head">User level bonus</p>' .
					 "\n\t\t\t\t" . '<p class="ubm_info">Productivity bonus: <span>' . number_format($productivity_bonus, 2, '.', ' ') 
								  . '%</span></p>' .
					 "\n\t\t\t\t" . '<p class="ubm_info">Bonus to damage: <span>' . number_format($damage_bonus, 2, '.', ' ') 
								  . '</span></p>' .
					 "\n\t\t\t" . '</div>';
				
				echo "\n\t\t" . '</div>';
				
				/* friends */
				echo "\n\t\t" . '<div id="user_friends_div">';
				$query = "SELECT u.user_id, user_name, user_image, flag, country_name,
						  country_abbr, c.country_id
						  FROM users u, user_profile up, country c
						  WHERE up.user_id = u.user_id AND c.country_id = up.citizenship AND
						 (u.user_id IN (SELECT user_id FROM friends WHERE friend_id = '$profile_id') 
						  OR u.user_id IN (SELECT friend_id FROM friends WHERE user_id = '$profile_id'))
						  ORDER BY user_name";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($friend_id, $friend_name, $friend_image, $country_flag, $country_name, $country_abbr, 
						 $country_id) = $row;
					if(iconv_strlen($country_name > 10)) {
						$country_name = $country_abbr;
					}

					$friend_level = getUserLevel($friend_id);

					echo "\n\t\t\t" . '<div class="ufd_friend_div">' .
						 "\n\t\t\t\t" . '<a href="user_profile?id=' . $friend_id . '" class="friend_name" target="_blank">' 
										. $friend_name . '</a>' .
						 "\n\t\t\t\t" . '<img src="../user_images/' . $friend_image . '" class="friend_image">' .
						 "\n\t\t\t\t" . '<div class="ufd_friend_info">' .
						 "\n\t\t\t\t\t" . '<a class="friend_citizenship" href="country?country_id=' . $country_id . '">' .
										  '<img src="../country_flags/' . $country_flag . '">' . $country_name . '</a>' .
						 "\n\t\t\t\t\t" . '<p class="friend_level">Level: ' . $friend_level . '</p>' .
						 "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
				
				if($profile_id == $user_id) {
					/* currency div */
					echo "\n\t\t" . '<div id="user_currency_div">';
					$query = "SELECT amount, currency_abbr, flag FROM user_currency uc, currency cu, country c
							  WHERE user_id = '$user_id' AND c.currency_id = cu.currency_id
							  AND cu.currency_id = uc.currency_id AND amount > 0 ORDER BY amount DESC";
					$result = $conn->query($query);

					while($row = $result->fetch_row()) {
						list($amount, $currency_abbr, $flag) = $row;
						echo "\n\t\t\t" . '<div class="ucd_info">' .
							 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '" alt="' . $currency_abbr . '">' .
							 "\n\t\t\t\t" . '<p>' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
							 "\n\t\t\t" . '</div>';
					}
					echo "\n\t\t" . '</div>';
					
					/* referals */
					echo "\n\t\t" . '<div id="user_referers_div">';
					$query = "SELECT refering_id, user_name, IFNULL(user_image, 0), product_icon, product_name, available_amount, collected_amount
							  FROM user_profile up, users u, referer_info ri, product_info pi
							  WHERE ri.user_id = '$user_id' AND up.user_id = refering_id AND u.user_id = refering_id
							  AND pi.product_id = ri.product_id  ORDER BY available_amount DESC";
					$result = $conn->query($query);
					
					//referal link
					echo'<p id="user_refering_link">vmundus.com/index?referer=' . $user_id . '</p>' .
						'<p id="ref_desc">' . 
						'Get 1 gold every time the invited user passes 10 levels, and 0.2 gold every' .
						' time the user gains a new level.' .
						'</p>';
					
					echo "\n\t\t\t" . '<div id="urd_heads">' .
						 "\n\t\t\t\t" . '<p id="urdh_prod">' . $lang['product_collected'] . '</p>' .
						 "\n\t\t\t\t" . '<p id="urdh_avail">' . $lang['available'] . '</p>' .
						 "\n\t\t\t" . '</div>';
					
					
					while($row = $result->fetch_row()) {
						list($refering_id, $refering_name, $user_image, $product_icon, $product_name, 
							 $available_amount, $collected_amount) = $row;
						echo "\n\t\t\t" . '<div class="urd_info">' .
							 "\n\t\t\t\t" . '<a class="ref_name" href="user_profile?id=' . $refering_id . '">' . $refering_name . '</a>' .
							 "\n\t\t\t\t" . '<img class="ref_img" src="../user_images/' . $user_image . '" alt="' . $refering_name . '">' .
							 "\n\t\t\t\t" . '<img class="ref_prod_icon" src="../product_icons/' . $product_icon . '" alt="' . $product_name . '">' .
							 "\n\t\t\t\t" . '<p class="ref_collected">' . number_format($collected_amount, '2', '.', ' ') . '</p>' .
							 "\n\t\t\t\t" . '<p class="ref_available">' . number_format($available_amount, '2', '.', ' ') . '</p>' .
							 "\n\t\t\t\t" . '<p class="collect_ref_reward button blue">' . $lang['collect'] . '</p>' .
							 "\n\t\t\t\t" . '<p hidden>' . $refering_id . '</p>' .
							 "\n\t\t\t" . '</div>';
					}
					echo "\n\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
			}
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>