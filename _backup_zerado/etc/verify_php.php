<?php 
	include('../php_functions/get_ip.php');//getIP();
	if(filter_var(!getIP(), FILTER_VALIDATE_IP)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Error"
							  )));
	}

	/*exit(json_encode(array(
		'success'=>false,
		'error'=>"The game is unavailable."
	)));*/

	$user_id = $_SESSION['user_id'];
	
	//check if not banned
	$query = "SELECT date_until, time_until FROM banned_users WHERE user_id = '$user_id'
			  AND date_until >= CURRENT_DATE AND time_until > CURRENT_TIME";
	$result = $conn->query($query);
	if($result->num_rows > 0) {
		session_unset(); 
		session_destroy(); 
		exit(json_encode(array('success'=>false,
							   'error'=>"Account is banned"
							  )));
	}

	//check if special user to keep logged in and give access to the site
	$special_user = FALSE;
	if(!empty($_SESSION['special_user']) && $_SESSION['special_user'] == 'true') {//check if special session is set
		$special_hash = $_SESSION['special_hash'];
		$special_email = $_SESSION['special_email'];
		$query = "SELECT email FROM special_users WHERE hash = '$special_hash' AND email = '$special_email'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$special_user = TRUE;
		}
		else {
			$_SESSION['special_user'] = 'false';
		}
	}
	else {
		$_SESSION['special_user'] = 'false';
	}
	
	//check if site under maintenance
	$query = "SELECT * FROM maintenance_info WHERE is_under_maintenance = TRUE
			  AND end_date >= CURRENT_DATE AND end_time > CURRENT_TIME";
	$result = $conn->query($query);
	if($result->num_rows == 1 && !$special_user) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Under maintenance"
							  )));
	}

	//Description: Verify user identity for "back-end" php files.
	include("../php_functions/get_user_language.php"); //getUserLanguage('extension');
	include('../lang/' . getUserLanguage('php') . '.php');
	
	if(empty($_SESSION['is_session'])) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Session expired. Please login again."
							  )));
	}
	elseif($_SESSION['is_session'] == 'set') {
		$session_id = $_SESSION['session_id'];
		$query = "SELECT ip FROM session_table WHERE session_id = '$session_id' AND is_active = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($session_ip) = $row;
		
		if(getIP() != $session_ip) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Session expired. Please login again."
								  )));
		}
	}
	
	//update last active date
	$query = "UPDATE users SET last_active = CURRENT_DATE where user_id = '$user_id'";
	$conn->query($query);

	$query = "UPDATE users SET last_active_time = CURRENT_TIME where user_id = '$user_id'";
	$conn->query($query);
?>