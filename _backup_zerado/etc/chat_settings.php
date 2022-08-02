<?php
//Description: Chat setting. Add, kick, disband, rename and etc.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)

	$chat_id = htmlentities(stripslashes(strip_tags(trim($_POST['chat_id']))), ENT_QUOTES);
	$member_id = htmlentities(stripslashes(strip_tags(trim($_POST['user_id']))), ENT_QUOTES);
	$new_name = htmlentities(stripslashes(strip_tags(trim($_POST['new_name']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if(!is_numeric($chat_id)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Chat doesn't exist.")));
	}
	
	if($action == 'get_info') {
		//get users access level(founder, moderator, user)
		$query = "SELECT chat_name, access_lvl FROM chat_info ci, chat_members cm 
				  WHERE ci.chat_id = '$chat_id' AND cm.user_id = '$user_id' AND ci.chat_id = cm.chat_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to view this information."
								  )));
		}

		$row = $result->fetch_row();
		list($chat_name, $access_lvl) = $row;
		
		if($access_lvl == 1) {
			$access = 'founder';	
		}
		else if($access_lvl == 2) {
			$access = 'moderator';
		}
		if($access_lvl == 3) {
			$access = 'member';
		}
		
		//get members
		$members = array();
		$query = "SELECT IFNULL(user_image, ''), user_name, u.user_id, access_lvl 
				  FROM users u, user_profile up, chat_members cm WHERE u.user_id = cm.user_id 
				  AND up.user_id = cm.user_id AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($member_image, $member_name, $member_id, $access_lvl) = $row;
			if($access_lvl == 1) {
				$who = 'founder';	
			}
			else if($access_lvl == 2) {
				$who = 'moderator';
			}
			if($access_lvl == 3) {
				$who = 'member';
			}
			array_push($members, array('access_lvl'=>$who, 'image'=>$member_image, 'name'=>$member_name, 'id'=>$member_id));
		}
		
		echo json_encode(array('success'=>true,
							   'chat_name'=>$chat_name,
							   'user_access'=>$access,
							   'chat_id'=>$chat_id,
							   'members'=>$members
							  ));
	}
	else if($action == 'disband') {
		$query = "SELECT chat_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id = '$user_id' AND access_lvl = 1";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have access to this feature."
								  )));
		}
		
		$query = "DELETE FROM unread_chat_messages WHERE message_id IN (SELECT message_id FROM chat WHERE chat_id = '$chat_id')";
		$conn->query($query);

		$query = "DELETE FROM modified_messages WHERE message_id IN (SELECT message_id FROM chat WHERE chat_id = '$chat_id')";
		$conn->query($query);
		
		$query = "DELETE FROM chat_members WHERE chat_id = '$chat_id'";
		$conn->query($query);

		$query = "DELETE FROM chat WHERE chat_id = '$chat_id'";
		$conn->query($query);

		$query = "DELETE FROM favorite_chat WHERE chat_id = '$chat_id'";
		$conn->query($query);
		
		$query = "DELETE FROM chat_info WHERE chat_id = '$chat_id'";
		$conn->query($query);

		echo json_encode(array('success'=>true,
							   'msg'=>"Chat successfully deleted."
							  ));
	}
	else if($action == 'rename') {
		$query = "SELECT chat_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id = '$user_id' AND access_lvl = 1";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have access to this feature."
								  )));
		}
		
		strValidate($new_name, 1, 15, 'Chat name');
		
		$query = "UPDATE chat_info SET chat_name = '$new_name' WHERE chat_id = '$chat_id'";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'chat_name'=>$new_name
							  ));
		
	}
	else if($action == 'setmod') {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		if($user_id == $member_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to add yourself.")));
		}
		
		$query = "SELECT chat_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id = '$user_id' AND access_lvl = 1";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to add new moderators in this chat.")));
		}
		
		//cannot have more than 5 mods per chat
		$query = "SELECT COUNT(*) FROM chat_members WHERE chat_id = '$chat_id' AND access_lvl = 2";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($mods_number) = $row;
		if($mods_number >= 5) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Only 5 moderators allowed per chat.")));
		}
		
		//determine if user exists
		$query = "SELECT user_image, user_name, u.user_id FROM user_profile up, users u
				  WHERE u.user_id = '$member_id' AND up.user_id = u.user_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		$row = $result->fetch_row();
		list($member_image, $member_name, $member_id) = $row;
		
		//determine if member of a chat
		$access_lvl = 2; //moderator
		$query = "SELECT * FROM chat_members WHERE user_id = '$member_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO chat_members VALUES('$chat_id', '$member_id', '$access_lvl')";
			$new_member = true;
		}
		else {
			//update access_lvl
			$query = "UPDATE chat_members SET access_lvl = '$access_lvl' WHERE user_id = '$member_id' AND chat_id = '$chat_id'";	
			$new_member = false;
		}
		if($conn->query($query)) {
			echo json_encode(array('success'=>true,
								   'image'=>$member_image,
								   'name'=>$member_name,
								   'id'=>$member_id,
								   'access_lvl'=>'moderator',
								   'new_member'=>$new_member,
								   'msg'=>'Successully added new moderator.'
								  ));
		}
	}
	else if($action == 'remove_mod') {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		if($user_id == $member_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to remove yourself.")));
		}
		
		$query = "SELECT chat_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id = '$user_id' AND access_lvl = 1";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to remove moderators in this chat.")));
		}
		
		//determine if user exists
		$query = "SELECT * FROM users WHERE user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		//determine if member of a chat
		$access_lvl = 3; //member
		$query = "SELECT * FROM chat_members WHERE user_id = '$member_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User is not a member of this chat.")));
		}
		else {
			//update access_lvl
			$query = "UPDATE chat_members SET access_lvl = '$access_lvl' WHERE user_id = '$member_id' AND chat_id = '$chat_id'";	
			$new_member = false;
		}
		if($conn->query($query)) {
			echo json_encode(array('success'=>true,
								   'access_lvl'=>'member',
								   'msg'=>'Moderator successfully removed.'));
		}
	}
	else if($action == 'add_member') {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		if($user_id == $member_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to add yourself.")));
		}
		
		//determine if have access
		$query = "SELECT chat_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id = '$user_id' AND (access_lvl = 1 || access_lvl = 2)";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to add new members to this chat.")));
		}
		
		//determine if user exists
		$query = "SELECT user_image, user_name, u.user_id FROM user_profile up, users u
				  WHERE u.user_id = '$member_id' AND up.user_id = u.user_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		$row = $result->fetch_row();
		list($member_image, $member_name, $member_id) = $row;
		
		//determine if member of a chat
		$access_lvl = 3; //member
		$query = "SELECT * FROM chat_members WHERE user_id = '$member_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User already a member of this chat.")));
			
		}
		else {
			$query = "INSERT INTO chat_members VALUES('$chat_id', '$member_id', '$access_lvl')";
		}
		if($conn->query($query)) {
			echo json_encode(array('success'=>true,
								   'image'=>$member_image,
								   'name'=>$member_name,
								   'id'=>$member_id,
								   'access_lvl'=>'member',
								   'msg'=>'Successully added new member.'));
		}
	}
	else if($action == 'kick_member') {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		if($user_id == $member_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to remove yourself.")));
		}
		
		$query = "SELECT chat_id FROM chat_members WHERE chat_id = '$chat_id' AND user_id = '$user_id' AND (access_lvl = 1 || access_lvl = 2)";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to remove members from this chat.")));
		}
		
		//determine if user exists
		$query = "SELECT * FROM users WHERE user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist.")));
		}
		
		//is chat member
		$query = "SELECT access_lvl FROM chat_members WHERE user_id = '$member_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User is not a member of this chat.")));
		}
		else {
			$row = $result->fetch_row();
			list($access_lvl) = $row;
			if($access_lvl == 1) {
				exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to remove founder of this chat.")));
			}
			//remove member and all his messages
			$query = "DELETE FROM unread_chat_messages WHERE message_id IN (SELECT message_id FROM chat WHERE chat_id = '$chat_id')";
			$conn->query($query);
		
			$query = "DELETE FROM modified_messages WHERE message_id IN 
					 (SELECT message_id FROM chat WHERE from_user_id = '$member_id' AND chat_id = '$chat_id')";
			$conn->query($query);
			
			$query = "DELETE FROM chat_members WHERE user_id = '$member_id' AND chat_id = '$chat_id'";
			$conn->query($query);

			$query = "DELETE FROM chat WHERE from_user_id = '$member_id' AND chat_id = '$chat_id'";
			$conn->query($query);

			$query = "DELETE FROM favorite_chat WHERE user_id = '$member_id' AND chat_id = '$chat_id'";
			$conn->query($query);
		}
		if($conn->query($query)) {
			echo json_encode(array('success'=>true,
								   'msg'=>'Member successfully removed.'));
		}
	}
	else if ($action == 'leave') {
		//is chat member
		$query = "SELECT access_lvl FROM chat_members WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not a member of this chat.")));
		}
		else {
			$row = $result->fetch_row();
			list($access_lvl) = $row;
			if($access_lvl == 1) {
				exit(json_encode(array('success'=>false,
								   'error'=>"Founders cannot leave chats. You can only disband this chat.")));
			}
			
			//remove member and all his messages
			$query = "DELETE FROM modified_messages WHERE message_id IN 
					 (SELECT message_id FROM chat WHERE from_user_id = '$member_id' AND chat_id = '$chat_id')";
			$conn->query($query);
			
			$query = "DELETE FROM chat_members WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
			$conn->query($query);

			$query = "DELETE FROM chat WHERE from_user_id = '$user_id' AND chat_id = '$chat_id'";
			$conn->query($query);

			$query = "DELETE FROM favorite_chat WHERE user_id = '$user_id' AND chat_id = '$chat_id'";
			$conn->query($query);

			echo json_encode(array('success'=>true,
								   'msg'=>'You have successfully left this chat.'));
		}
	}
	
		
	mysqli_close($conn);
?>