<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<?php
			$flag = true;
			$mail_id = htmlentities(stripslashes(strip_tags(trim($_GET['id']))), ENT_QUOTES);
			if(!is_numeric($mail_id)) {
				$flag = false;
			}
				
			//show general information
			$query = "SELECT heading FROM mail_info WHERE mail_id = 
					 (SELECT mail_id FROM mail_participants WHERE mail_id = '$mail_id' AND user_id = '$user_id')";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$flag = false;
			}
			if($flag) {
				$row = $result->fetch_row();
				list($heading) = $row;
				echo "\n\t\t" . '<p id="page_head">' . $heading . '</p>' .
					 "\n\t\t" . '<div id="message_div" class="scroll_style">' .
					 "\t\t\t\t" . '<p id="mail_id" hidden>' . $mail_id . '</p>';

				$query = "DELETE FROM unread_messages WHERE message_id IN 
						 (SELECT message_id FROM messages WHERE mail_id = '$mail_id') AND user_id = '$user_id'";
				$conn->query($query);
				
				$query = "SELECT m.user_id, user_image, user_name, message, date, time, um.message_id
						  FROM users u, user_profile up, mail_participants mp, messages m LEFT JOIN unread_messages um ON
						  m.message_id = um.message_id AND um.user_id = '$user_id'
						  WHERE mp.mail_id = '$mail_id' AND m.mail_id = mp.mail_id
						  AND mp.user_id = '$user_id' AND u.user_id = m.user_id AND up.user_id = u.user_id
						  ORDER BY date ASC, time ASC";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($muser_id, $user_image, $user_name, $message, $date, $time, $unread) = $row;
					if($muser_id == $user_id) {
						$user_name = 'me';
					}
					
					if($user_id == $muser_id) {
						$from_who_class = 'out_message_div';
					}
					else {
						$from_who_class = 'in_message_div';
					}
					echo 
						 "\n\t\t\t" . '<div class="' . $from_who_class . '">' .
						 "\n\t\t\t\t" . '<p class="time">' . correctDate($date, $time) . ' ' .  correctTime($time) . ' ' .
									   '<a href="user_profile?id=' . $muser_id . '" target="_blank">' . 
										$user_name . '</a></p>' .
						 "\n\t\t\t\t" . '<img class="user_image" src="../user_images/' . $user_image . '" alt="user image">' .
						 "\n\t\t\t\t" . '<div class="message_divs">' . html_entity_decode($message, ENT_QUOTES) . '</div>' .
						 "\n\t\t\t" . '</div>';
				}
				
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div id="enter_message">' .
					 "\n\t\t\t\t" . '<p id="msg_error"></p>' .
					 "\n\t\t\t\t" . '<textarea id="message_input"></textarea>' .
					 "\n\t\t\t\t" . '<span id="send" class="glyphicon glyphicon-send"></span>' .
					 "\n\t\t\t" . '</div>';
			}
			if(!$flag) {
				echo "\n\t\t" . '<p>Mail doesn\'t exists</p>';
			}
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>
