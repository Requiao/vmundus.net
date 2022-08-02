<?php
	//Description: Pre Register.

	include('connect_db.php');
	include('php_functions/get_ip.php');//getIP();
	
	usleep(100000);

	$email =  htmlentities(stripslashes(trim($_POST['email'])), ENT_QUOTES);
	$notify =  htmlentities(stripslashes(trim($_POST['notify'])), ENT_QUOTES);
	$ip = getIP();
	

	
	if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		exit(json_encode(array('msg' => "Invalid input for email.")));
	}
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit(json_encode(array('msg' => "Failed to register.")));
	}
	
	//check if user already pre registered
	$query = "SELECT email FROM pre_regester WHERE email = '$email'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		exit(json_encode(array('msg' => 'You already signed up.')));
	}
	
	if($notify == '1') {
		$notify = 'TRUE';
		$msg = 'Thank You! You will be notified when the game starts.';
	}
	else {
		$notify = 'FALSE';
		$msg = 'Thank You! You have successfully signed up.';
	}
		
	$query = "INSERT INTO pre_regester VALUES('$email', '$ip', $notify, CURRENT_DATE, CURRENT_TIME)";
	if($conn->query($query)) {
		exit(json_encode(array('msg' => $msg, 
							   'is_suc' => true)));
	}
	else {
		exit(json_encode(array('msg' => 'Failed to sign up, something went wrong. Please try again.')));
	}		
	
	mysqli_close($conn);
?>