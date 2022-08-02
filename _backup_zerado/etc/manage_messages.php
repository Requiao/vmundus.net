<?php
	//Description : Manage messages(send, delete), notifications...
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../htmlpurifier/library/HTMLPurifier.auto.php');
	
	$config = HTMLPurifier_Config::createDefault();
	$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
	$config->set('Attr.AllowedClasses', '');
	$config->set('CSS.AllowedFonts', 'Arial, Helvetica, sans-serif, Comic Sans MS, cursive, Courier New, Courier, monospace,
				 Georgia, Lucida Sans Unicode, Lucida Grande, Tahoma, Geneva, Times New Roman, Times, serif, Trebuchet MS, Verdana');
	$config->set('CSS.MaxImgLength', '800px');
	$config->set('CSS.AllowedProperties', 'background-color, border, border-collapse, border-spacing, border-width, clear, color, float, font-family, font-size, font-style, font-weight, height, letter-spacing, line-height, margin, margin-bottom, margin-left, margin-right, margin-top, max-height, max-width, padding, padding-bottom, padding-left, padding-right, padding-top, table-layout, text-align, text-decoration, text-indent, width, word-spacing');
	$config->set('HTML.MaxImgLength', '800');
	$config->set('HTML.AllowedElements', 'a, abbr, address, b, bdo, blockquote, br, caption, cite, code, col, colgroup, dd, del, div, dl, dt, em, h1, h2, h3, h4, h5, h6, hr, i, img, kbd, li, ol, p, pre, q, s, samp, small, span, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var');
	$config->set('HTML.TargetBlank', true);
	
	$purifier = new HTMLPurifier($config);
	
	$heading = htmlspecialchars(stripslashes(strip_tags(trim($_POST['heading']))), ENT_QUOTES);
	$mail_id = htmlspecialchars(stripslashes(strip_tags(trim($_POST['mail_id']))), ENT_QUOTES);
	$message_id = htmlspecialchars(stripslashes(strip_tags(trim($_POST['message_id']))), ENT_QUOTES);
	$message = htmlspecialchars($purifier->purify(stripslashes(trim($_POST['message']))), ENT_QUOTES);
	$to_id =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['to_id']))), ENT_QUOTES);
	$action =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	
	$user_id = $_SESSION['user_id'];

	if($action == 'check_noti_msg') {//check if received a notification of message
		$query = "SELECT COUNT(unread), (SELECT COUNT(*) FROM unread_messages WHERE user_id = '$user_id' AND message_id IN
				 (SELECT message_id FROM messages WHERE mail_id IN
				 (SELECT mail_id FROM mail_participants WHERE user_id = '$user_id' AND active = TRUE))) 
				  FROM notifications WHERE user_id = '$user_id' AND unread = TRUE";
		if($result = $conn->query($query)) {
			$row = $result->fetch_row();
			list($notif_count, $msg_count) = $row;

			echo json_encode(array('notifications' => $notif_count, 'messages' => $msg_count));
		}
	}
	else if($action == 'set_notif_as_viewed') {//sets notifications as viewed
		$query = "UPDATE notifications SET unread = FALSE WHERE user_id = '$user_id'";
		$conn->query($query);
	}
	else if ($action == 'compose_message') {
		if(!filter_var($to_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		if($to_id == $user_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You cannot send message to yourself.")));
		}
		
		strValidate($heading, 1, 30, 'Heading');
		strValidate($message, 1, 21000, 'Message');
		
		//determine if user exists
		$query = "SELECT user_name, user_image FROM users u, user_profile up
				  WHERE u.user_id = '$to_id' AND up.user_id = u.user_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($to_user_name, $to_user_image) = $row;
		
		$mail_id = getTimeForId() . $user_id;
		$message_id = getTimeForId() . $user_id;
		
		$query = "INSERT INTO mail_info VALUES ('$mail_id', '$heading')";
		if($conn->query($query)) {
			$query = "INSERT INTO mail_participants VALUES('$mail_id', '$user_id', TRUE)";
			$conn->query($query);
			
			$query = "INSERT INTO mail_participants VALUES('$mail_id', '$to_id', TRUE)";
			$conn->query($query);
			
			$query = "INSERT INTO messages VALUES ('$mail_id', '$message_id', '$user_id', '$message', CURRENT_DATE, CURRENT_TIME)";
			$conn->query($query);
			
			$query = "INSERT INTO unread_messages VALUES ('$message_id', '$to_id')";
			$conn->query($query);
		
			$reply = array('success'=>true,
						   'msg'=>$lang['message_sent'],
						   'message'=>$message,
						   'heading'=>$heading, 
						   'message_time'=>correctTime(date("H:i:s")),
						   'mail_id'=>$mail_id,
						   'to_user_name'=>$to_user_name,
						   'to_user_image'=>$to_user_image,
						   'to_user_id'=>$to_id
					);
			$reply = json_encode($reply);
			echo $reply;
		}
	}
	else if ($action == 'delete_messages') {
		if(empty($message_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['choose_at_least_one_mail_to_delete']
								  )));
		}
		$message_array = explode(',', $message_id);
		for($x = 0; $x < count($message_array); $x++) {
			if(!is_numeric($message_array[$x])) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Mail doesn't exist.")));
			}
			$query = "SELECT * FROM mail_participants WHERE mail_id = '$message_array[$x]' AND user_id = '$user_id' AND active = TRUE";
			$result = $conn->query($query);
			if($result->num_rows == 0) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Mail doesn't exist.")));
			}
		}
		
		for($x = 0; $x < count($message_array); $x++) {
			$query = "UPDATE mail_participants SET active = FALSE WHERE mail_id = '$message_array[$x]' AND user_id = '$user_id'";
			$conn->query($query);
			
			//if no members left, delete mail
			$query = "SELECT COUNT(*) FROM mail_participants WHERE mail_id = '$message_array[$x]' AND active = TRUE";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($members_left) = $row;
			if($members_left == 0) {
				$query = "DELETE FROM unread_messages WHERE message_id IN
						 (SELECT message_id FROM messages WHERE mail_id = '$message_array[$x]')";
				$conn->query($query);
				
				$query = "DELETE FROM messages WHERE mail_id = '$message_array[$x]'";
				$conn->query($query);
				
				$query = "DELETE FROM mail_participants WHERE mail_id = '$message_array[$x]'";
				$conn->query($query);
				
				$query = "DELETE FROM mail_info WHERE mail_id = '$message_array[$x]'";
				$conn->query($query);
			}
		}
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['messages_successfully_deleted']
							  ));
	}
	else if($action == 'send_message') {
		if(!is_numeric($mail_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mail doesn't exist.")));
		}

		//check if mail exist and user is participant
		$query = "SELECT * FROM mail_participants WHERE mail_id = '$mail_id' AND user_id = '$user_id' AND active = true";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mail doesn't exist.")));
		}
		
		//check if more than 1 participants left
		$query = "SELECT COUNT(*) FROM mail_participants WHERE mail_id = '$mail_id' AND active = true";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($members_left) = $row;
		if($members_left <= 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['other_member_deleted_this_conversation']
								  )));
		}
		
		
		strValidate($message, 1, 21000, 'Message');
		
		$message_id = getTimeForId() . $user_id;
		
		$query = "INSERT INTO messages VALUES ('$mail_id', '$message_id', '$user_id', '$message', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
			
		//get all mail users
		$query = "SELECT user_id FROM mail_participants WHERE mail_id = '$mail_id' AND user_id != '$user_id' AND active = true";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($member_id) = $row;
			$query = "INSERT INTO unread_messages VALUES ('$message_id', '$member_id')";
			$conn->query($query);
		}
		
		$reply = array('success'=>true,
					   'message'=>$message,
					   'message_time'=>correctTime(date("H:i:s"))
					  );
		$reply = json_encode($reply);
		echo $reply;
	}
?>