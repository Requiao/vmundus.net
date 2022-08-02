<?php
	//Description: Manage chat, send and edit messages.

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
	$config->set('CSS.AllowedFonts', '');
	$config->set('CSS.MaxImgLength', '250');
	$config->set('CSS.AllowedProperties', '');
	$config->set('HTML.MaxImgLength', '250');
	$config->set('HTML.AllowedElements', 'a, b, cite, code, del, i, kbd, samp, small, strong, var');
	$config->set('HTML.TargetBlank', true);
	
	$purifier = new HTMLPurifier($config);
	
	$chat_name = htmlspecialchars(stripslashes(strip_tags(trim($_POST['chat_name']))), ENT_QUOTES);
	$message_id = htmlentities(stripslashes(strip_tags(trim($_POST['message_id']))), ENT_QUOTES);
	$chat_id = htmlentities(stripslashes(strip_tags(trim($_POST['chat_id']))), ENT_QUOTES);
	$message = htmlspecialchars($purifier->purify(stripslashes(trim($_POST['message']))), ENT_QUOTES);
	$token =  htmlentities(stripslashes(strip_tags(trim($_POST['token']))), ENT_QUOTES);
	$flag =  htmlentities(stripslashes(strip_tags(trim($_POST['flag']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);

	$user_id = $_SESSION['user_id'];

	if($action == 'create_chat') {
		strValidate($chat_name, 1, 15, 'Chat name');
		
		$chat_id = getTimeForId() . $user_id;
		
		$access_lvl = 1;//founder
		$query = "INSERT INTO chat_info VALUES('$chat_id', '$chat_name', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		$query = "INSERT INTO chat_members VALUES('$chat_id', '$user_id', '$access_lvl')";
		if($conn->query($query)) {
			echo json_encode(array('success'=>true,
								   'msg'=>"Chat '$chat_name' successfuly created.",
								   'chat_id'=>$chat_id,
								   'chat_name'=>$chat_name
								  ));
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"Something went wrong. Please try again.")));
		}
	}
	else if ($action == 'send_message') {
		//usleep(500000);//0.5sec
		if(!is_numeric($chat_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Chat not selected."
								  )));
		}
		
		strValidate($message, 1, 500, 'Message');
		
		$message_id = getTimeForId() . $user_id;
		
		//check if chat member
		$query = "SELECT chat_id FROM chat_members WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>'You are not allowed to write messages in this chat.'
								  )));
		}
		
		$query = "INSERT INTO chat VALUES('$chat_id', '$message_id', '$message', '$user_id', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		$query = "SELECT user_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id != '$user_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($member_id) = $row;
			
			$query = "INSERT INTO unread_chat_messages VALUES ('$member_id', '$message_id')";
			$conn->query($query);
		}

		echo json_encode(array('success'=>true, 
							   'msg'=>$message,
							   'post_time'=>date('M j H:i:s', strtotime(correctDate(date("Y-m-d"), date('H:i:s')) . ' ' .  
									correctTime(date("H:i:s")))),
							   'msg_id'=>$message_id
							  ));
	}
	else if($action == 'delete') {
		if(!is_numeric($message_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Message doesn't exist."
								  )));
		}
		
		//check if message belongs to the user
		$query = "SELECT * FROM chat WHERE from_user_id = '$user_id' AND message_id = '$message_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Message doesn't exist."
								  )));
		}
		
		//select all users in the chat.
		$query = "SELECT user_id FROM chat_members WHERE chat_id = (SELECT chat_id FROM chat WHERE message_id = '$message_id') 
				  AND user_id IN (SELECT user_id FROM users 
				  WHERE DATE_ADD(TIMESTAMP(last_active, last_active_time), INTERVAL 10 MINUTE) >= NOW())";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($uid) = $row;
			if($uid != $user_id) {
				//to display modified msg for the rest of the users
				$query = "INSERT INTO modified_messages VALUES('$uid', '$message_id')";
				$conn->query($query);
			}
		}
		$query = "UPDATE chat SET message = 'deleted' WHERE from_user_id = '$user_id' AND message_id = '$message_id'";
		if($conn->query($query)) {
			exit(json_encode(array('success'=>true
								  )));
		}
	}
	elseif($action == 'edit') {
		if(!is_numeric($message_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Message doesn't exist.")));
		}
		
		//check if message belongs to the user
		$query = "SELECT * FROM chat WHERE from_user_id = '$user_id' AND message_id = '$message_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This message is not your's.")));
		}
		
		$reg_string = '/(*UTF8)^[0-9\p{L}' .
					  ' \?<>!~\'\":^+.,\$%\-_=*&#\/;\(\)\{\}\[\]\n\r\t№]*$/i';
		$unmatched_regex = '/(*UTF8)[0-9\p{L}' .
					  ' \?<>!~\'\":^+.,\$%\-_=*&#\/;\(\)\{\}\[\]\n\r\t№]*/i';
		
		if (!preg_match($reg_string, $message)) {
			$unmatched_chars = preg_replace($unmatched_regex, '', $message);
			exit(json_encode(array('success'=>false,
						   'error'=>"Unexeptable characters $unmatched_chars")));
		}
		else if (iconv_strlen($message) < 0) {
			exit(json_encode(array('success'=>false,
						   'error'=>"$str_name is too short, must be more than 0 chars.")));
		}
		else if (iconv_strlen($message) > 500) {
			exit(json_encode(array('success'=>false,
						   'error'=>"$str_name is too long, must be less than 500 chars.")));
		}
		else {
			$message = mysqli_real_escape_string($conn, $message);
		}
		
		//select all users in the chat.
		$query = "SELECT user_id FROM chat_members WHERE chat_id = (SELECT chat_id FROM chat WHERE message_id = '$message_id') 
				  AND user_id IN (SELECT user_id FROM users 
				  WHERE DATE_ADD(TIMESTAMP(last_active, last_active_time), INTERVAL 10 MINUTE) >= NOW())";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($uid) = $row;
			if($uid != $user_id) {
				//to display modified msg for the rest of the users
				$query = "INSERT INTO modified_messages VALUES('$uid', '$message_id')";
				$conn->query($query);
			}
		}
		
		$query = "UPDATE chat SET message = '$message' WHERE from_user_id = '$user_id' AND message_id = '$message_id'";
		$conn->query($query);
		if($conn->query($query)) {
			echo json_encode(array('success'=>true, 
								   'msg'=>$message
								  ));
		}
	}
	else if ($action == 'add_favorite_chat') {
		if(!is_numeric($chat_id)) {
			exit();
		}
		
		//check if member of a chat
		$query = "SELECT * FROM chat_members WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			echo json_encode(array('success'=>false
							  ));
		}
		
		$query = "SELECT * FROM favorite_chat WHERE user_id = $user_id";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE favorite_chat SET chat_id = '$chat_id' WHERE user_id = '$user_id'";
		}
		else {
			$query = "INSERT INTO favorite_chat VALUES('$user_id', '$chat_id')";
		}
		$conn->query($query);
		echo json_encode(array('success'=>true
							  ));
	}
	else if ($action == 'get_messages') {
		$reply = array();
		if(!is_numeric($chat_id)) {
			exit(json_encode(array(array('success'=>false))));
		}
		
		//check if member of a chat
		$query = "SELECT * FROM chat_members WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(array('success'=>false))));
		}
		
		$is_fav_chat = false;
		$messages = array();
		if($flag == 'new') {
			//if token is invalid, than display messages by default from last 5sec.
			if(!filter_var($token, FILTER_VALIDATE_INT) || $token < strtotime(date('Y-m-d H:i:s')) - 10 
				|| $token > strtotime(date('Y-m-d H:i:s'))) {
				$token = strtotime(date('Y-m-d H:i:s')) - 10;
			}
			$from_date = date('Y-m-d H:i:s', $token);
			
			//modified messages
			$query = "SELECT message, message_id FROM chat WHERE message_id IN 
					 (SELECT message_id FROM modified_messages WHERE user_id = '$user_id')
					  AND chat_id = '$chat_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($message, $message_id) = $row;
				$query = "DELETE FROM modified_messages WHERE message_id = '$message_id' AND user_id = '$user_id'";
				$conn->query($query);
				array_push($messages, array("message"=>$message, "message_id"=>$message_id, "edited"=>true));
			}
			
			//get unread messages	
			$query = "SELECT message, user_name, u.user_id, date, time, message_id, user_image 
					  FROM chat, users u, user_profile up
					  WHERE u.user_id = from_user_id AND chat_id = '$chat_id' AND from_user_id != '$user_id' 
					  AND up.user_id = u.user_id
					  AND TIMESTAMP(date, time) > '$from_date' ORDER BY date ASC, time ASC";
		}
		else if ($flag == 'old') {
			//get favorite chat
			$query = "SELECT chat_id FROM favorite_chat WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($favorite_chat) = $row;
			if($favorite_chat == $chat_id){
				$is_fav_chat = true;//to display star by the chat name
			}
		
			//get messages
			$query = "DELETE FROM modified_messages WHERE message_id IN
					 (SELECT message_id FROM chat WHERE chat_id = '$chat_id') AND user_id = '$user_id'";
			$conn->query($query);
			
			$query = "SELECT message, user_name, u.user_id, date, time, message_id, user_image  
					  FROM chat, users u, user_profile up
					  WHERE  u.user_id = from_user_id AND up.user_id = u.user_id
					  AND chat_id = (SELECT chat_id FROM chat_members WHERE
					  user_id = '$user_id' AND chat_id = '$chat_id') ORDER BY date ASC, time ASC";
		}
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($message, $user_name, $from_user_id, $date, $time, $message_id, $user_image) = $row;

			$cor_date = date('M j', strtotime(correctDate($date, $time)));
			$cor_time = correctTime($time);
	
			if($user_id == $from_user_id) {
				$is_me = true;
			}
			else {
				$is_me = false;
			}
			$from_admin = false;
			$from_user_id == 1 ? $from_admin = true : $from_admin = false;
			array_push($messages, array("message"=>$message, "user_name"=>$user_name, "cor_date"=>$cor_date, 
										"cor_time"=>$cor_time, "is_me"=>$is_me, "from_user_id"=>$from_user_id, 
										"message_id"=>$message_id, "user_image"=>$user_image, 
										"from_admin"=>$from_admin));
			
			$token = strtotime($date . ' ' . $time);
		}

		
		$query = "DELETE FROM unread_chat_messages WHERE user_id = '$user_id' AND message_id IN
				 (SELECT message_id FROM chat WHERE chat_id = '$chat_id')";
		$conn->query($query);
		
		//get unread messages from other chats
		$new_messages = array();
		$query = "SELECT COUNT(ucm.message_id), chat_id FROM unread_chat_messages ucm, chat c
				  WHERE c.message_id = ucm.message_id AND user_id = '$user_id' GROUP BY chat_id, user_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($new_msg, $chat_id) = $row;
			array_push($new_messages, array("new_msg"=>$new_msg, "chat_id"=>$chat_id));
		}
		
		echo json_encode(array('success'=>true, 
							   'token'=>$token,
							   'is_fav_chat'=>$is_fav_chat,
							   'messages'=>$messages,
							   'new_messages'=>$new_messages
							  ));
	}
?>