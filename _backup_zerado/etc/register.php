<?php
	//Description: Register.

	include('../connect_db.php');
	include('../index_etc/email_confirm.php');//sendVerificationEmail($email)
	include('../php_functions/password_validate.php');//passwordValidate($pass)
	include('../php_functions/user_name_validate.php');//userNameValidate($name)
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/get_ip.php');//getIP()
	
	usleep(100000);

	$email =  htmlentities(stripslashes(trim($_POST['email'])), ENT_QUOTES);
	$country_id = htmlentities(stripslashes(trim($_POST['country'])), ENT_QUOTES);
	$user_name = htmlentities(stripslashes(trim($_POST['name'])), ENT_QUOTES);
	$pass =  htmlentities(stripslashes(trim($_POST['pass'])), ENT_QUOTES);
	$rpt_pass =  htmlentities(stripslashes(trim($_POST['rpt_pass'])), ENT_QUOTES);
	$referer = htmlentities(stripslashes(trim($_POST['referer'])), ENT_QUOTES);
	$hour = htmlentities(stripslashes(trim($_POST['hour'])), ENT_QUOTES);
	$ip = getIP();
	
	if(!filter_var($hour, FILTER_VALIDATE_INT)) {
		$hour = 0;
	}
	if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Country doesn't exists."
							  )));
	}
	else if (!userNameValidate($user_name)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid input for name."
							  )));
	}
	else if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid input for email."
							  )));
	}
	else if ($pass !== $rpt_pass) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Passwords don't match."
							  )));
	}
	else if (!passwordValidate($pass)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Password is not strong enough. Must have special char."
							  )));
	}
	else if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Failed to register. Unable to verify user."
							  )));
	}
	else if($hour > 24 || $hour < 0) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Failed to register. Unable to set timezone."
							  )));
	}
	else {
		//check if email domain is acceptable
		preg_match_all("/\@\w+\./m", $email, $d_domains);
		preg_match_all("/\w+/m", $d_domains[0][0], $domain);

		$d = $domain[0][0];
		$query = "SELECT * FROM accepted_mails WHERE mail_name = '$d'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"$d email service is not allowed.",
			)));
		}

		//check if user already registered but not verified email
		$query = "SELECT email FROM confirmation WHERE email = '$email'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Your acount is not activated.",
								   'send_hash'=>true
								   )));
		}
		
		//query that checks if email address is not duplicate
		$query = "SELECT email FROM users WHERE email = '$email'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You already have account."
								   )));
		}

		//query that checks if user_name is not duplicate
		$query = "SELECT user_name FROM users WHERE user_name = '$user_name'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This user name is already in use."
								   )));
		}

		//query that checks if user_name is not duplicate
		$query = "SELECT user_name FROM confirmation WHERE user_name = '$user_name'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This user name is already in use."
								   )));
		}
		
		//query that checks if country exists
		$query = "SELECT country_id FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist."
								   )));
		}
		else {
			$row = $result->fetch_row();
			list($country_id) = $row;

			if($country_id == 1000) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"You are not alien!"
				)));
			}
			
			//check if referer number exist
			if(!empty($referer)) {
				if(!filter_var($referer, FILTER_VALIDATE_INT)) {
					exit(json_encode(array('success'=>false,
										   'error'=>"Invalid referer id."
										   )));
				}
				$query = "SELECT user_id FROM users WHERE user_id = '$referer'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					exit(json_encode(array('success'=>false,
										   'error'=>"Invalid referer id."
										   )));
				}
			}
			
			//set timezone
			$base_time = date('G');
			if($base_time < 12 && $hour > 12){
				$base_time += 24;
				$timezone = $hour - $base_time;
			}
			else {
				$timezone = $hour - $base_time;
			}
			
			if($timezone < -11) {
				$timezone = $hour - ($base_time - 24);
			}
			
			$query = "SELECT timezone_id FROM timezones WHERE hours = '$timezone'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($timezone_id) = $row;
			
			$options = ['cost' => 12,];
			$hash = password_hash($pass, PASSWORD_DEFAULT, $options);
			
			if($hash) {
				//generate confirmation hash
				$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=.,?<>';
				$random_string = '';
				$y = rand(10, 15);
				for ($x = 0; $x < $y; $x++) {
					$random_string .= $chars[rand(0, strlen($chars) - 1)];
				}
				
				$string_for_hash = getTimeForId() . $email . $random_string;
				$email_hash = hash('sha256', $string_for_hash);
				
				//populate confirmation table
				if(empty($referer)) {
					$referer = 'NULL';
				}
				$query = "INSERT INTO confirmation VALUES('$email', '$email_hash',  CURRENT_DATE, CURRENT_TIME, '$country_id',
						  $referer, '$user_name', '$hash', '$timezone_id')";

				if($conn->query($query)) {
					//send confirmation email
					if(sendVerificationEmail($email)) {
						echo json_encode(array('success'=>true,
											   'msg_head'=>"Account Successfuly Created",
											   'msg'=>"Your account is not activated, to activate your account, 
													   check $email for the activation link."
											  ));
					}
				}
				else {
					exit(json_encode(array('success'=>false,
										   'error'=>"Failed to register, something went wrong. Please try again."
										   )));
				}	
			}
			else {
				exit(json_encode(array('success'=>false,
									   'error'=>"Failed to register, something went wrong. Please try again."
									   )));
			}
		}
	}
	mysqli_close($conn);
?>