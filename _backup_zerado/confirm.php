<?php
	//Description: If user responds to email, then register him.

	include('connect_db.php');
	include('php_functions/get_time_for_id.php');//getTimeForId();
	include('php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include("php_functions/get_user_language.php"); //getUserLanguage();
	
	$hash = htmlentities(stripslashes(trim($_GET['confirmation'])), ENT_QUOTES);
	
	if(strlen($hash) == 64) {
		$ip = getIP();
		if(!filter_var($ip, FILTER_VALIDATE_IP)) {
			exit(header('Location: index'));
		}
		
		$query = "SELECT email, citizenship, referer_id, user_name, password, timezone_id FROM confirmation 
				  WHERE confirmation_hash = '$hash'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row =  $result->fetch_row();
			list($email, $citizenship, $referer_id, $user_name, $password, $timezone_id) = $row;
			
			//detect if special user
			$query = "SELECT user_id FROM special_users WHERE email = '$email'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row =  $result->fetch_row();
				list($user_id) = $row;
				$query = "INSERT INTO users VALUES('$user_id', '$user_name', '$password', '$email', CURRENT_DATE, 
						  CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
				
				//mark user as registered and create permanent hash
				$string_for_hash = $email . random_bytes(25);
				$new_hash = hash('sha256', $string_for_hash);
				$query = "UPDATE special_users SET hash = '$new_hash' WHERE email = '$email'";
				$conn->query($query);
				$query = "UPDATE special_users SET is_new = FALSE WHERE email = '$email'";
				$conn->query($query);
			}
			else {
				//populate users table
				$query = "INSERT INTO users (user_name, password, email, register_date, last_active, last_active_time) 
						  VALUES('$user_name', '$password', '$email', CURRENT_DATE, CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
				
				$user_id = $conn->insert_id;
			}
		
			//populate 'user_profile' table
			$query = "SELECT capital FROM country WHERE country_id = '$citizenship'";
			$result = $conn->query($query);
			$row =  $result->fetch_row();
			list($loc_region) = $row;
			
			$lang = getUserLanguage();
			$query = "INSERT INTO user_profile VALUES('$user_id', '$citizenship', '$loc_region', 
					  'avatar.png', '$timezone_id', '$lang', 0, 1)";
			$conn->query($query);

			//currency by location
			$query = "SELECT currency_id FROM country WHERE country_id = 
					 (SELECT country_id FROM regions WHERE region_id = '$loc_region')";
			$result = $conn->query($query);
			$row =  $result->fetch_row();
			list($currency_id) = $row;

			//user_currency. give 250 currency
			$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', 250)";
			$conn->query($query);

			//currency by citizenship
			$query = "SELECT currency_id FROM country WHERE country_id = '$citizenship'";
			$result = $conn->query($query);
			$row =  $result->fetch_row();
			list($cz_currency_id) = $row;
			if($cz_currency_id != $currency_id) {
				//user_currency. give 250 currency
				$query = "INSERT INTO user_currency VALUES('$user_id', '$cz_currency_id', 250)";
				$conn->query($query);
			}
			
			//populate communication table
			$access_lvl = 3;//member
			$query = "INSERT INTO chat_members VALUES('$citizenship', '$user_id', '$access_lvl')";
			$conn->query($query);

			//world chat
			$query = "INSERT INTO chat_members VALUES('11111111111111111111111', '$user_id', '$access_lvl')";
			$conn->query($query);
			
			$query = "INSERT INTO favorite_chat VALUES('$user_id', '11111111111111111111111')";
			$conn->query($query);
			
			//sign up for admin blog
			$query = "INSERT INTO blog_subscribers VALUES('1', '$user_id')";
			$conn->query($query);
			
			//insert welcome message
			$query = "SELECT heading, message FROM welcome_message WHERE country_id = '$citizenship'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($heading, $message) = $row;
				
				//get governor id
				$query = "SELECT user_id, position_id FROM country_government WHERE country_id = '$citizenship' AND position_id IN 
						 (SELECT position_id FROM government_country_responsibilities WHERE country_id = '$citizenship' 
						  AND responsibility_id = '30' AND must_sign_vote = true) AND user_id IS NOT NULL
						  UNION
						  SELECT user_id, 3 FROM congress_members WHERE country_id = 
						 (SELECT country_id FROM government_country_responsibilities WHERE country_id = '$citizenship'
						  AND responsibility_id = '30' AND must_sign_vote = true AND position_id = 3)
						  ORDER BY position_id LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows != 0) {
					$row = $result->fetch_row();
					list($governor_id) = $row;
					
					$mail_id = getTimeForId() . $user_id;
					$message_id = getTimeForId() . $user_id;
					
					$query = "INSERT INTO mail_info VALUES ('$mail_id', '$heading')";
					$conn->query($query);
					
					$query = "INSERT INTO mail_participants VALUES('$mail_id', '$governor_id', TRUE)";
					$conn->query($query);
					
					$query = "INSERT INTO mail_participants VALUES('$mail_id', '$user_id', TRUE)";
					$conn->query($query);
					
					$query = "INSERT INTO messages VALUES ('$mail_id', '$message_id', '$governor_id', '$message', 
							  CURRENT_DATE, CURRENT_TIME)";
					$conn->query($query);
					
					$query = "INSERT INTO unread_messages VALUES ('$message_id', '$user_id')";
					$conn->query($query);
				}
			}
			
			//insert data into user_rewards
			$user_rewards = new UserDefaultRewards($user_id);
			$user_rewards->registerRwd();
			
			//populate 'referer_info' table
			if(!empty($referer_id)) {
				$user_rewards->referralRwd();
				
				//inform about new referer.
				$notification = "Congratulations! You have new referer with id $user_id";
				sendNotification($notification, $referer_id);
				
				//populate referer table
				$product_id = 1;//gold
				$query = "INSERT INTO referer_info VALUES('$referer_id', '$user_id', '$product_id', 0, 0)";
				$conn->query($query);
			}
			
			//warehouse
			$query = "INSERT INTO user_warehouse VALUES('$user_id', '1000')";
			$conn->query($query);
			
			//populate people and user_houses tables and related to people tables
			$query = "INSERT INTO user_born_bar VALUES('$user_id', '0.0')";
			$conn->query($query);
			
			//person
			$person_id = getTimeForId() . $user_id;
			$query = "INSERT INTO people VALUES('$user_id', '$person_id', 1, 'available', 18, 100, 
											  'Person 1', 0, 0, FALSE, 0)";
			$conn->query($query);
			
			//person
			$person_id = getTimeForId() . $user_id;
			$query = "INSERT INTO people VALUES('$user_id', '$person_id', 1, 'available', 18, 100, 
											  'Person 2', 0, 0, FALSE, 0)";
			$conn->query($query);

			//person
			$person_id = getTimeForId() . $user_id;
			$query = "INSERT INTO people VALUES('$user_id', '$person_id', 1, 'available', 18, 100, 
											  'Person 3', 0, 0, FALSE, 0)";
			$conn->query($query);

			//person
			$person_id = getTimeForId() . $user_id;
			$query = "INSERT INTO people VALUES('$user_id', '$person_id', 1, 'available', 18, 100, 
											  'Person 4', 0, 0, FALSE, 0)";
			$conn->query($query);

			//person
			$person_id = getTimeForId() . $user_id;
			$query = "INSERT INTO people VALUES('$user_id', '$person_id', 1, 'available', 18, 100, 
											  'Person 5', 0, 0, FALSE, 0)";
			$conn->query($query);

			$query = "INSERT INTO user_people_slots VALUES ('$user_id', 5)";
			$conn->query($query);
			
			//delete from confirmation
			$query = "DELETE FROM confirmation WHERE email = '$email'";
			$conn->query($query);
			header('Location: index');
			exit();
		}
		else {
			header('Location: index');
			exit();
		}
	}
	else {
		header('Location: index');
		exit();
	}
	
	class UserDefaultRewards {
		protected $register_rewards = 1;
		protected $referral_rewards = 3;
		protected $user_id;
		protected $conn;
		
		function __construct($user_id) {
			global $conn;
			$this->user_id = $user_id;
			$this->conn = $conn;
		}
		
		function registerRwd () {
			$query = "INSERT INTO user_rewards VALUES('$this->register_rewards', '$this->user_id', FALSE, CURRENT_DATE, CURRENT_TIME)";
			$this->conn->query($query);
		}
		
		function referralRwd () {
			$query = "INSERT INTO user_rewards VALUES('$this->referral_rewards', '$this->user_id', FALSE, CURRENT_DATE, CURRENT_TIME)";
			$this->conn->query($query);
		}
	};
	
	function getIP() {
		if(isset($_SERVER['REMOTE_ADDR']))
			return $_SERVER['REMOTE_ADDR'];
		else
			return false;
	};
	
	mysqli_close($conn);
?>