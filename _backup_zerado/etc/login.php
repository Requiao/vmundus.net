<?php
//Description: Perform login.

	session_start();
	session_regenerate_id(true);
	
	include('../connect_db.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId();
	include('../php_functions/get_ip.php');//getIP();
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/create_session.php'); //function createSession($conn, $user_id).
	include('../php_functions/remember_me_cookies.php'); //function rememberMeCookies($conn, $user_id, $ip).
	
	usleep(10000);//0.01sec
	
	$email =  htmlentities(stripslashes(trim($_POST['email'])), ENT_QUOTES);
	$pass =  htmlentities(stripslashes(trim($_POST['pass'])), ENT_QUOTES);
	$remember_me =  htmlentities(stripslashes(trim($_POST['remember_me'])), ENT_QUOTES);

	if (!empty($email) && !empty($pass)) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Invalid input for email."
			)));
		}

		if($remember_me !== 'true' && $remember_me !== 'false') {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Invalid input for remember me."
		   	)));
		}
		
		$ip = getIP();
		
		//block IP if entered invalid email several times
		//block user if entered invalid password several times. get logins from the last 6 hours
		$query = "SELECT COUNT(*), MAX(date), MAX(time) FROM invalid_logins 
				  WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL 6 HOUR) >= NOW() AND ip = '$ip'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($invalid_logins, $last_log_date, $last_log_time) = $row;
		
		blockForFailedLoginAttempts($invalid_logins, $last_log_date, $last_log_time);
		
		//get user pass and id
		$query = "SELECT user_id, password FROM users WHERE email = '$email'";
		$result = $conn->query($query);
		
		//check if registered but account is not activated
		if($result->num_rows == 0) {
			$query = "SELECT email FROM confirmation WHERE email = '$email'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Your account is not activated.",
									   'hash'=>true
									   )));
			}
			else {
				//record invalid login in order to block IP
				$query = "INSERT INTO invalid_logins (email, ip, date, time) VALUES ('$email', '$ip', CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
				
				exit(json_encode(array('success'=>false,
									   'error'=>"Incorrect email or password."
									   )));
			}
		}
		else {
			$row = $result->fetch_row();
			list($user_id, $hash) = $row;

			/*if($user_id != 1000 && $user_id != 1) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Game is currently under maintenance."
				)));
			}*/
			
			//block user if entered invalid password several times. get logins from the last 6 hours
			$query = "SELECT COUNT(*), MAX(date), MAX(time) FROM invalid_logins 
					  WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL 6 HOUR) >= NOW() AND user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($invalid_logins, $last_log_date, $last_log_time) = $row;
			
			blockForFailedLoginAttempts($invalid_logins, $last_log_date, $last_log_time);
			
			if (password_verify($pass, $hash)) {
				//check if not banned
				$query = "SELECT date_until, time_until FROM banned_users WHERE user_id = '$user_id'
						  AND TIMESTAMP(date_until, time_until) > NOW() ORDER BY date_until DESC, time_until DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows > 0) {
					$row = $result->fetch_row();
					list($date_until, $time_until) = $row;
					
					$end_on = "$date_until $time_until";
		
					$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
					$date2 = new DateTime($end_on);
					$diff = date_diff($date1,$date2);
					$days = $diff->format("%a");
					$time = $diff->format("%H:%I:%S");

					exit(json_encode(array('success'=>false,
										   'error'=>"Account is banned for $days days $time"
										   )));
				}
				
				//register user_device
				//check if logged in before from this device
				if(isset($_COOKIE['device'])) {
					$device_id = htmlentities(stripslashes(strip_tags(trim($_COOKIE['device']))), ENT_QUOTES);
					if(!is_numeric($device_id)) {
						setcookie("device", "", time() - 3600, "/");//unset cookie
						exit(json_encode(array('success'=>false,
											   'error'=>"Failed identification"
											   )));
					}
					$query = "SELECT * FROM user_devices WHERE device_id = '$device_id'";
					$result = $conn->query($query);
					if($result->num_rows == 0) {
						setcookie("device", "", time() - 3600, "/");//unset cookie
						exit(json_encode(array('success'=>false,
											   'error'=>"Failed identification"
											  )));
					}
					$query = "INSERT INTO user_devices VALUES ('$user_id', '$device_id', CURRENT_DATE, CURRENT_TIME)";
					$conn->query($query);
				}
				else {
					$device_id = getTimeForId() . $user_id;
					$query = "INSERT INTO user_devices VALUES ('$user_id', '$device_id', CURRENT_DATE, CURRENT_TIME)";
					$conn->query($query);
					
					setcookie("device", $device_id, time() + (86400 * 160), "/"); // 86400 = 1 day
				}
				
				if(!createSession($conn, $user_id)) {
					exit(json_encode(array(
						'success'=>false,
						'error'=>"Failed to login. Please try again."
					)));
				}
				
				// The cost parameter can change over time as hardware improves
				$options = array('cost' => 12);

				// Check if a newer hashing algorithm is available
				// or the cost has changed
				if (password_needs_rehash($hash, PASSWORD_DEFAULT, $options)) {
					// If so, create a new hash, and replace the old one
					$new_hash = password_hash($pass, PASSWORD_DEFAULT, $options);
					$query = "UPDATE users SET password = '$new_hash' WHERE user_id = '$user_id'";
					$conn->query($query);
				}

				//is remember me on
				if($remember_me === 'true') {
					rememberMeCookies($conn, $user_id, $ip);
				}
				
				//redirect user to main index page if logged in successfully
				exit(json_encode(array('success'=>true,
									  )));
			}
			else {
				//record invalid login
				$query = "INSERT INTO invalid_logins (user_id, ip, date, time) VALUES ('$user_id', '$ip', CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
				
				exit(json_encode(array('success'=>false,
									   'error'=>"Incorrect email or password."
									   )));
			}
		}
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"All fields must be filled in."
							  )));
	}
	
	function blockForFailedLoginAttempts($invalid_logins, $last_log_date, $last_log_time) {
		$blocked = false;
		if($invalid_logins >= 5) {
			$time_blocked = date('Y-m-d H:i:s', strtotime(date("$last_log_date $last_log_time") . ' + 5 minutes'));
			
			$blocked = true;
		}
		else if($invalid_logins >= 4) {
			$time_blocked = date('Y-m-d H:i:s', strtotime(date("$last_log_date $last_log_time") . ' + 3 minutes'));
			
			$blocked = true;
		}
		else if ($invalid_logins >= 3) {
			$time_blocked = date('Y-m-d H:i:s', strtotime(date("$last_log_date $last_log_time") . ' + 1 minutes'));
			
			$blocked = true;
		}
			
		if($blocked) {
			if(strtotime($time_blocked) > strtotime(date('Y-m-d H:i:s'))) {
				$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
				$date2 = new DateTime($time_blocked);
				$diff = date_diff($date1,$date2);
				$days = $diff->format("%a");
				$time = $diff->format("%H:%I:%S");
				
				exit(json_encode(array('success'=>false,
									   'error'=>"Account is locked for $time"
									   )));
			}
		}
	}
	mysqli_close($conn);
?>