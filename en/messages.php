<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<?php
		echo "\n\t\t" . '<p id="delete_messages" class="button">' . $lang['delete'] . '</p>' .
			 "\n\t\t" . '<p id="messages">' . $lang['messages'] . '</p>' .
			 "\n\t\t" . '<p id="notifications">' . $lang['notifications'] . '</p>' .
			 "\n\t\t" . '<p id="compose_message">' . $lang['compose_message'] . '</p>' .
			 "\n\t\t" . '<div id="messages_inner_div">';
			
				$user_mail = array();
				$x = 0;
				$query = "SELECT mail_id, heading FROM mail_info WHERE mail_id IN
						 (SELECT mail_id FROM mail_participants WHERE user_id = '$user_id' AND active = TRUE)";
				$result_m = $conn->query($query);
				while($row_m = $result_m->fetch_row()) {
					list($mail_id, $heading) = $row_m;
					
					//check if all participants left
					//check if more than 1 participants left
					$all_left = '';
					$query = "SELECT COUNT(*) FROM mail_participants WHERE mail_id = '$mail_id' AND active = TRUE";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($members_left) = $row;
					if($members_left <= 1) {
						$all_left = '<abbr title="' . $lang['other_member_deleted_this_conversation'] . 
									'"><p class="deleted">' . $lang['deleted'] . '</p></abbr>';
					}
		
					//get last message and its info
					$query = "SELECT message, mp.user_id, date, time, user_name, user_image, um.user_id
							  FROM mail_participants mp, users u, user_profile up, messages m LEFT JOIN unread_messages um 
							  ON um.message_id = m.message_id
							  WHERE m.mail_id = '$mail_id' AND u.user_id = mp.user_id AND up.user_id = mp.user_id
							  AND mp.user_id != '$user_id' AND mp.mail_id = m.mail_id
							  ORDER BY date DESC, time DESC LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($message, $from_user_id, $date, $time, $user_name, $user_image, $unread_by_id) = $row;
					
					$user_mail[$x]['mail_id'] = $mail_id;
					$user_mail[$x]['heading'] = $heading;
					$user_mail[$x]['message'] = $message;
					$user_mail[$x]['from_user_id'] = $from_user_id;
					$user_mail[$x]['date'] = correctDate($date, $time);
					$user_mail[$x]['time'] = correctTime($time);
					$user_mail[$x]['user_name'] = $user_name;
					$user_mail[$x]['user_image'] = $user_image;
					$user_mail[$x]['unread_by_id'] = $unread_by_id;
					$user_mail[$x]['date_time'] = strtotime(date('Y-m-d H:i:s', strtotime("$date $time")));
					$user_mail[$x]['all_left'] = $all_left;
					$x++;
				}
				
				usort($user_mail, function($a, $b) {
					return $b['date_time'] <=> $a['date_time'];
				});
				
				for($i = 0; $i < $x; $i++) {
					if(!empty($user_mail[$i]['unread_by_id']) && $user_mail[$i]['unread_by_id'] == $user_id) {
						$b_color_class = "unread_by_me";
					}
					else if(!empty($user_mail[$i]['unread_by_id']) && $user_mail[$i]['unread_by_id'] == $user_mail[$i]['from_user_id']) {
						$b_color_class = "unread_by_other";
					}
					else {
						$b_color_class = "messages_read";
					}
					
					echo "\n\t\t\t" . '<div class="all_messages_div ' . $b_color_class . '" id="m' . $user_mail[$i]['mail_id'] . '">' .
						 "\n\t\t\t\t" . '<input class="checkboxes" type="checkbox" value="' . 
						  $user_mail[$i]['mail_id'] . '">' .
						 "\n\t\t\t\t" . '<div class="all_messages">' .
						 "\n\t\t\t\t\t" . '<a class="name" href="user_profile?id=' . $user_mail[$i]['from_user_id'] . 
										   '">' . $user_mail[$i]['user_name'] . '</a>' .
						 "\n\t\t\t\t\t" . '<img class="user_image" src="../user_images/' . $user_mail[$i]['user_image'] . 
						 '" alt="user image">' .
						 "\n\t\t\t\t\t" . '<a class="heading" href="show_message?id=' . 
						 $user_mail[$i]['mail_id'] . '">' . $from_who . ' ' . $user_mail[$i]['heading'] . '</a>' .
						 $user_mail[$i]['all_left'] .
						 "\n\t\t\t\t\t" . '<div class="short_message">' . html_entity_decode($user_mail[$i]['message'], ENT_QUOTES) . 
										  '</div>';
					if(strtotime($user_mail[$i]['date']) == strtotime(correctDate(date("Y-m-d"), date("H:i:s")))) {
						echo "\n\t\t\t\t\t" . '<p class="date">' . $user_mail[$i]['time'] . '</p>';
					}
					else {
						echo "\n\t\t\t\t\t" . '<p class="date">' . $user_mail[$i]['date']. '</p>';
					}
					
					echo "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t" . '</div>';
				}
			?>
			
		</div>
		
		<div id="notifications_div">
			<?php
				$query = "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY date DESC, time DESC";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list(, $notification, $date, $time, $unread) = $row;
					if($unread == true) {
						$n_color_class = "notification_unread";
					}
					else if ($unread == false) {
						$n_color_class = "notification_read";
					}
					echo "\n\t\t\t" . '<div class="all_notifications">' .
							"\n\t\t\t\t" . '<p class="notification ' . $n_color_class . '">' . $notification . '</p>';
					if($date == date("Y-m-d")) {
						echo "\n\t\t\t\t" . '<p class="note_date">' . correctTime($time) . '</p>';
					}
					else {
						$temp_date = date('Y-M-j', strtotime(correctDate($date, $time)));
						echo "\n\t\t\t\t" . '<p class="note_date">' . $temp_date . '</p>';
					}		
					echo "\n\t\t\t" . '</div>' . "\n";
				}		
			?>
		
		</div>
	</div>
	
</main>

<?php include('footer.php'); ?>