<?php
	//Description : Manage messages(send, delete), notifications...
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/upload_image.php'); //uploadImage($image_id, $folder_path, $old_path = NULL, $width = 400, $height = 200)
	include('../php_functions/user_name_validate.php');//userNameValidate($name)
	include('../php_functions/password_validate.php');//passwordValidate($pass)
	
	$user_name =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['user_name']))), ENT_QUOTES);
	$pass =  htmlentities(stripslashes(trim($_POST['password'])), ENT_QUOTES);
	$rpt_pass =  htmlentities(stripslashes(trim($_POST['rpt_password'])), ENT_QUOTES);
	$timezone =  htmlentities(stripslashes(strip_tags(trim($_POST['timezone']))), ENT_QUOTES);
	$old_pswd =  htmlentities(stripslashes(trim($_POST['old_pswd'])), ENT_QUOTES);
	$lang_code =  htmlentities(stripslashes(trim($_POST['lang_code'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if($action == 'change_language') {
		if(!filter_var($lang_code, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false
								  )));
		}
		$query = "SELECT abbr FROM languages WHERE lang_id = '$lang_code'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($lang_abbr) = $row;
			$query = "UPDATE user_profile SET lang = '$lang_abbr' WHERE user_id = '$user_id'";
			
			$conn->query($query);
			setcookie("lang", $lang_abbr, time() + (86400 * 90), "/"); // 86400 = 1 day
			exit(json_encode(array("success"=>true,
								   "msg"=>"Language successfully changed"
								  )));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Language doesn't exist"
								  )));
		}
	}
	
	//verify pass
	$query = "SELECT password, user_image FROM users u, user_profile up 
			  WHERE u.user_id = '$user_id' AND up.user_id = u.user_id";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($hash, $old_image) = $row;
	
	if(!password_verify($old_pswd, $hash)) {
		exit(json_encode(array('success'=>false,
							   'error'=>'Incorrect password.'
							  )));
	}
	
	$reply = array("success"=>true);
	
	//user_image
	if(file_exists($_FILES['image']['tmp_name'])) {
		if(!empty($old_image) && $old_image != 'avatar.png') {
			$user_img = uploadImage($user_id, '../user_images', '../user_images/' . $old_image, 280, 364);
		}
		else {
			$user_img = uploadImage($user_id, '../user_images', NULL, 280, 364);
		}
		if($user_img === 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"File is not an image."
								  )));
		}
		if($user_img === 2) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Your file is too large."
								  )));
		}
		if($user_img === 3) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Only png, jpeg, and jpg are allowed for the user image."
								  )));
		}
		if($user_img === 4) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Image not uploaded. Please try again."
								  )));
		}
		
		$reply["upload_img"] = true;
		
		$query = "UPDATE user_profile SET user_image = '$user_img' WHERE user_id = '$user_id'";
		$conn->query($query);
	}
	
	//user name
	if(!empty($user_name)) {
		if(!userNameValidate($user_name)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Invalid input for name."
								  )));
		}
	}
	
	//password
	if(!empty($pass)) {
		if($pass !== $rpt_pass) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Passwords don't match."
								  )));
		}
		else if (!passwordValidate($pass)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Password is not strong enough. Must have at least 5 characters."
								  )));
		}
	}
	
	//timezone
	if(!empty($timezone)) {
		//check when last time updated timezone.
		$CHANGE_INTERVAL = 60; //days
		
		$query = "SELECT date, time FROM user_timezone_change WHERE DATE_ADD(TIMESTAMP(date, time), 
				  INTERVAL '$CHANGE_INTERVAL' DAY) > NOW() AND user_id = '$user_id' ORDER BY date DESC, time DESC LIMIT 1";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($date, $time) = $row;
			
			$current_date = date('Y-m-d');
			$current_time = date('H:i:s');
			
			$date_time = date('Y-m-d H:i:s', strtotime($date . ' ' . $time . " + $CHANGE_INTERVAL days"));
				
			$date1 = new DateTime($current_date . ' ' . $current_time);
			$date2 = new DateTime($date_time);
			$diff = date_diff($date1,$date2);
			$days_in = $diff->format("%a");
			$time_in = $diff->format("%H:%I:%S");
			
			$time = $days_in . " days " . $time_in;
			
			exit(json_encode(array('success'=>false,
								   'error'=>"You can change timezone every $CHANGE_INTERVAL days. 
											 You will be able to change your timezone in $time"
								  )));
		}
		
		$query = "SElECT * FROM timezones WHERE timezone_id = '$timezone'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Timezone doesn't exist."
								  )));
		}
	}
	
	//make changes
	if(!empty($user_name)) {
		$query = "UPDATE users SET user_name = '$user_name' WHERE user_id = '$user_id'";
		$conn->query($query);
		$reply["user_name"] = true;
	}
	
	if(!empty($pass)) {
		$options = ['cost' => 12,];
		$hash = password_hash($pass, PASSWORD_DEFAULT, $options);
		
		$query = "UPDATE users SET password = '$hash' WHERE user_id = '$user_id'";
		$conn->query($query);
		$reply["pass"] = true;
	}
	
	if(!empty($timezone)) {
		$query = "UPDATE user_profile SET timezone_id = '$timezone' WHERE user_id = '$user_id'";
		$conn->query($query);
		
		$query = "INSERT INTO user_timezone_change VALUES('$user_id', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		$reply["timezone"] = true;
	}
	echo json_encode($reply);
?>
