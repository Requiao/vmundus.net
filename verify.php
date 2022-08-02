<?php
	//Description: Verify user identity.

	require_once 'php_functions/get_ip.php';//getIP();
	require_once 'php_functions/get_time_for_id.php';//getTimeForId();
	include('php_functions/create_session.php'); //function createSession($conn, $user_id).
	include('php_functions/remember_me_cookies.php'); //function rememberMeCookies($conn, $user_id, $ip).

	$rm_token = htmlentities(stripslashes(strip_tags(trim($_COOKIE['rm_token']))), ENT_QUOTES);//remember me token
	$t_token = htmlentities(stripslashes(strip_tags(trim($_COOKIE['t_token']))), ENT_QUOTES);//remember me token
	$t_user = htmlentities(stripslashes(strip_tags(trim($_COOKIE['user']))), ENT_QUOTES);//remember me token

	$ip = getIP();
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit("You are not allowed to view this page.");
	}
	//exit("The game is unavailable.");

	//record page visitors
	if(empty($file_name)) {//file name for main index page already set;
		$file_name = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
		$file_name = preg_replace('/(\?)(.)*/i', '', $file_name);
		$file_name = basename($file_name);
		if($file_name == 'index') {
			$file_name = "home";
		}
	}
	include("php_functions/register_page_visitors.php"); //registerPageVisitors('$file_name', $visitor_ip));
	registerPageVisitors($file_name, $ip);
	
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

	if(!$special_user) {
		$coming_on = "2017-11-03 00:00:00";
		
		$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
		$date2 = new DateTime($coming_on);
		$diff = date_diff($date1,$date2);
		$come_in_days = $diff->format("%a");
		$come_in = $diff->format("%H:%I:%S");
		
		if(strtotime($coming_on) > strtotime(date('Y-m-d H:i:s'))) {
			header("Location: /coming_soon");
			exit;
		}
	}

	$is_under_maintenance = false;
	//check if site under maintenance
	$query = "SELECT * FROM maintenance_info WHERE is_under_maintenance = TRUE
			  AND end_date >= CURRENT_DATE AND end_time > CURRENT_TIME";
	$result = $conn->query($query);
	if($result->num_rows == 1 && !$special_user) {
		//check if special user
		$is_under_maintenance = true;
	}

	$url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
	if($is_under_maintenance && !$special_user) {
		$maint_url_regex = '/\/maintenance/';
		if(!preg_match($maint_url_regex, $url)) {
			header('Location: /maintenance');
			exit;
		}
	}
	else {
		$url_referer_regex = '/^\/index\?referer=[0-9]{0,7}$/';
		$url_reset_pass_regex = '/^\/index\?reset_password=[0-9a-z]{64}$/';
		$url_language = '/^\/index\?lang=[a-z]{2}$/';
		
		if(empty($_SESSION['is_session'])) {
			if(loginThroughToken() == true) {
				if($url == '/index' || $url == '/' || preg_match($url_referer_regex, $url) 
				|| preg_match($url_reset_pass_regex, $url) || $url == '/maintenance' 
				|| preg_match($url_language, $url)) {
					header('Location: /en/index');
					exit;
				}
			}

			$regular_url_regex = '/^\/$/';

			if(!preg_match($regular_url_regex, $url) && !preg_match($url_referer_regex, $url) 
				&& !preg_match($url_reset_pass_regex, $url) && !preg_match($url_language, $url)) {
				sendToMainPage();
			}
		}
		else if($_SESSION['is_session'] == 'set') {
			//check if banned
			$user_id = $_SESSION['user_id'];

			//check if not banned
			$query = "SELECT date_until, time_until FROM banned_users WHERE user_id = '$user_id'
					  AND TIMESTAMP(date_until, time_until) > NOW() 
					  ORDER BY date_until DESC, time_until DESC LIMIT 1";
			$result = $conn->query($query);
			if($result->num_rows > 0) {
				session_unset(); 
				session_destroy(); 
				header('Location: /');
				exit;
			}
			
			//check if session is still active
			$session_id = $_SESSION['session_id'];
			$query = "SELECT ip FROM session_table WHERE session_id = '$session_id'  AND is_active = TRUE";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($session_ip) = $row;
			
			if(getIP() == $session_ip) {
				if($url == '/index' || $url == '/' || preg_match($url_referer_regex, $url) 
					|| preg_match($url_reset_pass_regex, $url) || $url == '/maintenance' 
					|| preg_match($url_language, $url)) {
					header('Location: /en/index');
					exit;
				}
			}
			else if((getIP() != $session_ip) && ($url != '/')) {
				session_unset(); 
				session_destroy(); 
				sendToMainPage();
			}
		}
	}

	function loginThroughToken () {
		global $conn;
		global $rm_token;
		global $t_token;
		global $t_user;

		$ip = getIP();

		if($rm_token && $t_token && $t_user) {
			$query = "SELECT user_id, ip, is_active FROM remember_me 
					  WHERE rm_token = '$rm_token' AND time_token = '$t_token' AND user_id = '$t_user'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($user_id, $token_ip, $is_active) = $row;

				if($is_active != true) {
					return false;
				}

				if($ip != $token_ip) {
					return false;
				}

				if(!createSession($conn, $user_id)) {
					return false;
				}

				if(!rememberMeCookies($conn, $user_id, $ip)) {
					return false;
				}
				return true;
			}
		}
		return false;
	}

	function sendToMainPage() {
		global $file_name;
		if($file_name != 'terms_of_service' && $file_name != 'privacy_policy') {
			header('Location: /');
			exit;
		}
	}
?>