<?php
	//Description: Process requests from main index page
	include('../connect_db.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId();
	include('email_confirm.php');// sendVerificationEmail($email)
	include('reset_pass_email.php');// sendResetPassEmail($email, $confirmation_hash)
	include('../php_functions/password_validate.php');//passwordValidate($email)
	include("../php_functions/get_user_language.php"); //getUserLanguage();
	include('../lang/m_' . getUserLanguage() . '.php');
	
	$email =  htmlentities(stripslashes(trim($_POST['email'])), ENT_QUOTES);
	$password =  htmlentities(stripslashes(trim($_POST['pass'])), ENT_QUOTES);
	$rpt_password =  htmlentities(stripslashes(trim($_POST['rpt_pass'])), ENT_QUOTES);
	$user_hash =  htmlentities(stripslashes(trim($_POST['reset_password'])), ENT_QUOTES);
	$lang_code =  htmlentities(stripslashes(trim($_POST['lang_code'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$ip = getIP();
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit();
	}
	
	if($action == 'get_countries') {
		$query = "SELECT country_id, country_name, flag FROM country ORDER BY country_name";
		$result = $conn->query($query);
		$reply = array();
		while($row = $result->fetch_row()) {
			list($country_id, $country_name, $flag) = $row;
			array_push($reply, array("country_id"=>$country_id, "country_name"=>$country_name, "flag"=>$flag));
		}
		echo json_encode($reply);
	}
	else if($action == 'forgot_pass') {
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			exit(json_encode(array('msg_head'=>$lang['reset_password'],
								   'msg'=>$lang['invalid_email']
								  )));
		}
		
		//check if user exists with this email
		$query = "SELECT user_id FROM users WHERE email = '$email'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('msg_head'=>$lang['reset_password'],
								   'msg'=>$lang['if_you_have_VMundus_account_then_the_instructions_was_sent_to_the_email_you_provided']
								  )));
		}
		$row = $result->fetch_row();
		list($user_id, $user_name, $register_date) = $row;
		
		//generate hash
		$query = "DELETE FROM password_reset WHERE email = '$email'";
		$conn->query($query);
		
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=.,?<>';
		$random_string = '';
		$y = rand(10, 15);
		for ($x = 0; $x < $y; $x++) {
			$random_string .= $chars[rand(0, strlen($chars) - 1)];
		}
		
		$string_for_hash = $email . getTimeForId() . $random_string;
		$hash = hash('sha256', $string_for_hash);
		
		$query = "INSERT INTO password_reset VALUES('$email', '$hash', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		if(sendResetPassEmail($email, $hash)) {
			exit(json_encode(array('msg_head'=>$lang['reset_password'],
								   'msg'=>$lang['if_you_have_VMundus_account_then_the_instructions_was_sent_to_the_email_you_provided']
								  )));
		}
		else {
			exit(json_encode(array('msg_head'=>$lang['reset_password'],
								   'msg'=>$lang['please_try_again']
								  )));
		}
	}
	elseif($action == 'resend_hash'){
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			exit($lang['invalid_email']);
		}
		//check if email not confirmed
		$query = "SELECT * FROM confirmation WHERE email = '$email'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit($lang['email_not_found']);
		}
		
		//send confirmation email
		if(sendVerificationEmail($email)) {
			echo 'Email sent. Check your spam folder.';
		}
		else {
			echo $lang['please_try_again'];
		}
	}
	else if($action == 'reset_pass') {
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			exit($lang['invalid_email']);
		}
		
		//check password
		if(strlen($password) < 5) {
			exit($lang['minimum_password_ength_is_5_characters']);
		}
		if($password !== $rpt_password) {
			exit($lang['passwords_dont_match']);
		}
		else if (!passwordValidate($password)) {
			exit($lang['password_is_not_strong_enough']);
		}
		
		//check if confirmation hash matches
		$query = "SELECT confirmation_hash FROM password_reset WHERE email = '$email'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("You did not requested password reset.");
		}
		$row = $result->fetch_row();
		list($confirmation_hash) = $row;
		
		if($confirmation_hash !== $user_hash) {
			exit("You did not requested password reset.");
		}
		
		$options = ['cost' => 12,];
		$hash = password_hash($password, PASSWORD_DEFAULT, $options);
		
		$query = "UPDATE users SET password = '$hash' WHERE email = '$email'";
		$conn->query($query);
		
		if($conn->query($query)) {
			echo 'Password was reset successfully.';
			
			$query = "DELETE FROM password_reset WHERE email = '$email'";
			$conn->query($query);
		}
		else {
			echo $lang['please_try_again'];;
		}
	}
	
	function getIP() {
		$ipaddress = '';
		if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	};
	
	mysqli_close($conn);
?>