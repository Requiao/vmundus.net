<?php
	//Description: Manage user profile. Collect rewards for inviting friend. Collect rewards for the Achievements
	//			   Change user's location. Add/Remove from friends
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/send_notification.php');//function sendNotification($notification, $user_id)

	$refering_id =  htmlentities(stripslashes(trim($_POST['user_id'])), ENT_QUOTES);
	$profile_id =  htmlentities(stripslashes(trim($_POST['profile_id'])), ENT_QUOTES);
	$to_country_id =  htmlentities(stripslashes(trim($_POST['country_id'])), ENT_QUOTES);
	$region_id =  htmlentities(stripslashes(trim($_POST['reg_id'])), ENT_QUOTES);
	$achiev_id =  htmlentities(stripslashes(trim($_POST['achiev_id'])), ENT_QUOTES);
	$level_id =  htmlentities(stripslashes(trim($_POST['level_id'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if($action == 'collect_clr_ok') {
		if(!filter_var($level_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Reward doesn't exists."
								  )));
		}
		
		//check if achievement exists
		$query = "SELECT * FROM user_exp_levels WHERE level_id = '$level_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Reward doesn't exists."
								  )));
		}
		
		//check if has something to collect
		$query = "SELECT * FROM user_level_rewards WHERE collected = FALSE AND user_id = '$user_id' AND level_id = '$level_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['no_products_available_to_collect']
								  )));
		}
		
		$query = "SELECT reward FROM user_exp_levels WHERE level_id = '$level_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($amount) = $row;
		
		//determine if enough capacity in warehouse
		$query = "SELECT capacity, SUM(amount) FROM user_warehouse uw, user_product up
				  WHERE uw.user_id = '$user_id' AND up.user_id = uw.user_id";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($capacity, $warehouse_fill) = $row;
		if($capacity < ($warehouse_fill + $amount)) {
			exit(json_encode(array('success'=>false,
							  	   'error'=>$lang['not_enough_room_in_the_warehouse']
								  )));
		}
		
		//update user warehouse
		//determine if user already has that product type
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = 1";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = 1) AS temp) + '$amount' 
					  WHERE user_id = '$user_id' AND product_id = 1";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', 1, '$amount')";
		}
		$conn->query($query);
		
		$query = "UPDATE user_level_rewards SET collected = TRUE WHERE user_id = '$user_id' AND level_id = '$level_id'";
		$conn->query($query);
		
		//check if has something to collect
		$collected_all = false;
		$query = "SELECT * FROM user_level_rewards WHERE collected = FALSE AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$collected_all = true;
		}
		
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['successfully_collected'],
							   'collected_all'=>$collected_all
							  ));
	}	
	else if($action == 'collect_level_rewards') {
		//check if has something to collect
		$query = "SELECT * FROM user_level_rewards WHERE collected = FALSE AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['no_products_available_to_collect']
								  )));
		}
		
		$rewards = array();
		$query = "SELECT uxl.level_id, product_icon, product_name, reward 
				  FROM user_exp_levels uxl, user_level_rewards ulr, product_info pi
				  WHERE uxl.level_id = ulr.level_id AND collected = FALSE AND product_id = 1 AND user_id = '$user_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($level_id, $product_icon, $product_name, $amount) = $row;
			array_push($rewards, array("product_icon"=>$product_icon, "product_name"=>$product_name, "amount"=>$amount, 
									   "level_id"=>$level_id));
		}
		echo json_encode(array('success'=>true,
							   'rewards'=>$rewards
							  ));
	}
	else if($action == 'cancel_cz_app') {
		//check if not already requesting
		$query = "SELECT country_name FROM country 
				  WHERE country_id = (SELECT country_id FROM citizenship_change WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$row = $result->fetch_row();
			list($country_name) = $row;
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_are_not_requesting_citizenship_change']
								  )));
		}
		
		//remove application
		$query = "DELETE FROM citizenship_change WHERE user_id = '$user_id'";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['the_application_was_successfully_canceled']
							  ));
	}
	else if($action == 'change_citizenship') {
		//test country
		if(!filter_var($to_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exists."
								  )));
		}
		$query = "SELECT * FROM country WHERE country_id = '$to_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exists."
								  )));
		}

		if($to_country_id == 1000) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You are not alien!"
			)));
		}
		
		//check if not requesting current citizenship
		$query = "SELECT * FROM country WHERE country_id != (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')
				  AND country_id = '$to_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_are_not_allowed_to_request_current_citizenship']
								  )));
		}
		
		//check if not already requesting
		$query = "SELECT country_name FROM country 
				  WHERE country_id = (SELECT country_id FROM citizenship_change WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row = $result->fetch_row();
			list($country_name) = $row;
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_are_already_requesting_citizenship_from'] . " $country_name. " .
											$lang['do_you_want_to_cancel_this_application'],
								   'requesting'=>true
								  )));
		}
		
		//check when last time requested
		$CHANGE_CZ_DAYS = 30; //days
		$query = "SELECT date, time FROM citizenship_change_history WHERE 
				  DATE_ADD(TIMESTAMP(date, time), INTERVAL '$CHANGE_CZ_DAYS' DAY) >= NOW()
				  AND user_id = '$user_id' ORDER BY date DESC, time DESC LIMIT 1";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row = $result->fetch_row();
			list($date, $time) = $row;
			
			$current_date = date('Y-m-d');
			$current_time = date('H:i:s');
			
			$date_time = date('Y-m-d H:i:s', strtotime($date . ' ' . $time . " + $CHANGE_CZ_DAYS days"));
				
			$date1 = new DateTime($current_date . ' ' . $current_time);
			$date2 = new DateTime($date_time);
			$diff = date_diff($date1,$date2);
			$days_in = $diff->format("%a");
			$time_in = $diff->format("%H:%I:%S");
			
			$time = $time_in . " hours";
			
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_can_change_your_citizenship_every'] . " $CHANGE_CZ_DAYS days. " . 
											$lang['you_will_be_able_to_change_your_citizenship_in'] . "$days_in days $time"
								  )));
		}
		
		//check if member of the party
		$query = "SELECT * FROM party_members WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Leave the political party before changing you citizenship."
								  )));
		}
		
		//check if governor
		//check if president
		$query = "SELECT position_id FROM country_government WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_are_a_governor_in_your_country_resign_from_your_position']
								  )));
		}
		else { //check if congressman
			$query = "SELECT country_id FROM congress_details WHERE country_id = 
					 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit(json_encode(array('success'=>false,
									   'error'=>$lang['you_are_a_governor_in_your_country_resign_from_your_position']
									  )));
			}
		}

		//old citizenship
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($old_citizenship) = $row;

		//check if applied for elections
		$query = "SELECT candidate_id FROM president_elections WHERE candidate_id = '$user_id'
				  AND election_id IN (SELECT election_id FROM election_info 
				  WHERE ((ended = TRUE AND can_participate = TRUE) 
				  OR (ended = FALSE AND can_participate = FALSE)) AND type = 1 AND country_id = '$old_citizenship')
				  UNION
				  SELECT candidate_id FROM congress_elections WHERE candidate_id = '$user_id'
				  AND election_id IN (SELECT election_id FROM election_info 
				  WHERE ((ended = TRUE AND can_participate = TRUE) 
				  OR (ended = FALSE AND can_participate = FALSE)) AND type = 3 AND country_id = '$old_citizenship')";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You applied for elections. You must first revoke your application or wait until elections are over."
			)));
		}
		
		//check if governor exists in the country that can accept cz
		$query = "SELECT user_id FROM country_government WHERE country_id = '$to_country_id' AND position_id IN 
				 (SELECT position_id FROM government_country_responsibilities WHERE country_id = '$to_country_id' 
				  AND responsibility_id = '32' AND must_sign_vote = true) AND user_id IS NOT NULL
				  UNION
				  SELECT user_id FROM congress_members WHERE country_id = 
				 (SELECT country_id FROM government_country_responsibilities WHERE country_id = '$to_country_id'
				  AND responsibility_id = '32' AND must_sign_vote = true AND position_id = 3)";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			//update chat
			$query = "DELETE FROM chat_members WHERE user_id = '$user_id' AND chat_id = '$old_citizenship''";
			$conn->query($query);
			
			$access_lvl = 3;//members
			$query = "INSERT INTO chat_members VALUES('$to_country_id', '$user_id', '$access_lvl')";
			$conn->query($query);
			
			//check if minister of the treasury
			$query = "SELECT * FROM bank_details WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE bank_details SET user_id = NULL WHERE user_id = '$user_id'";
				$conn->query($query);
			}
			
			//change cz
			$query = "UPDATE user_profile SET citizenship = '$to_country_id' WHERE user_id = '$user_id'";
			$conn->query($query);
			
			//history
			$query = "INSERT INTO citizenship_change_history 
				VALUES ('$user_id', '$old_citizenship', '$to_country_id', CURRENT_DATE, CURRENT_TIME, NULL)";
			$conn->query($query);

			$query = "SELECT country_name, currency_id FROM country WHERE country_id = '$to_country_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($country_name, $currency_id) = $row;

			//give currency
			$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows >= 1) {
				$query = "UPDATE user_currency SET amount = amount + 250 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
				$conn->query($query);		  
			}
			else {
				$query = "INSERT INTO user_currency VALUES ('$user_id', '$currency_id', 250)";
				$conn->query($query);
			}
			
			$notification = "$country_name " . $lang['accepted_your_citizenship_request'];
			sendNotification($notification, $user_id);
		}
		else {
			$query = "INSERT INTO citizenship_change VALUES ('$user_id', '$to_country_id', CURRENT_DATE, CURRENT_TIME)";
			$conn->query($query);
			
			while($row = $result->fetch_row()) {
				list($governor_id) = $row;
				
				$notification = $lang['you_have_new_citizenship_request'];
				sendNotification($notification, $governor_id);
			}
		}
		
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['you_have_successfully_submitted_your_application_for_citizenship_change']
							  ));
	}
	else if($action == 'cz_change_info') {
		//check if not already requesting
		$query = "SELECT country_name FROM country 
				  WHERE country_id = (SELECT country_id FROM citizenship_change WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row = $result->fetch_row();
			list($country_name) = $row;
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_are_already_requesting_citizenship_from'] . " $country_name. " .
											$lang['do_you_want_to_cancel_this_application'],
								   'requesting'=>true
								  )));
		}
		
		//get countries
		$query = "SELECT country_id, country_name, flag FROM country 
				  WHERE country_id != (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')
				  ORDER BY country_name";
		$result = $conn->query($query);
		$countries = array();
		while($row = $result->fetch_row()) {
			list($country_id, $country_name, $flag) = $row;
			array_push($countries, array("country_id"=>$country_id, "country_name"=>$country_name, "flag"=>$flag));
		}
		echo json_encode(array('success'=>true,
							   'countries'=>$countries
							  ));
	}
	else if($action == 'collect_ref_rewards') {
		if(!filter_var($refering_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Referer doesn't exists."
								  )));
		}
		
		//get available amount
		$query = "SELECT available_amount, collected_amount FROM referer_info WHERE refering_id = '$refering_id' 
				  AND user_id = '$user_id' AND product_id = 1";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Referer doesn't exists."
								  )));
		}
		$row = $result->fetch_row();
		list($available_amount, $collected_amount) = $row;
		
		if($available_amount <= 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['no_products_available_to_collect']
								  )));
		}
		
		//determine if enough capacity in warehouse
		$query = "SELECT capacity, SUM(amount) FROM user_warehouse uw, user_product up
				  WHERE uw.user_id = '$user_id' AND up.user_id = uw.user_id GROUP BY capacity;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($capacity, $warehouse_fill) = $row;
		if($capacity < ($warehouse_fill + $available_amount)) {
			exit(json_encode(array('success'=>false,
							  	   'error'=>$lang['not_enough_room_in_the_warehouse']
								  )));
		}
		
		//update user warehouse
		//determine if user already has that product type
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = 1";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = 1) AS temp) + '$available_amount' 
					  WHERE user_id = '$user_id' AND product_id = 1";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', 1, '$available_amount')";
		}
		$conn->query($query);
		
		$query = "UPDATE referer_info SET available_amount = 0 WHERE user_id = '$user_id' 
				  AND product_id = 1 AND refering_id = '$refering_id'";
		$conn->query($query);
		
		$query = "UPDATE referer_info SET collected_amount = (SELECT * FROM (SELECT collected_amount FROM referer_info 
				  WHERE user_id = '$user_id' AND product_id = 1 AND refering_id = '$refering_id') AS temp) + '$available_amount' 
				  WHERE user_id = '$user_id' AND product_id = 1 AND refering_id = '$refering_id'";
		$conn->query($query);
		
		$new_avail_amount = number_format(($available_amount + $collected_amount), '2', '.', ' ');

		echo json_encode(array('success'=>true,
							   'msg'=>$lang['you_have_successfully_collected'] . " $available_amount.",
							   'new_amount'=>$new_avail_amount
							  ));
	}
	else if($action == "add_to_friends") {
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exists."
								  )));
		}
		
		//check if not already friends
		$query = "SELECT * FROM friends WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
				   (user_id = '$profile_id' AND friend_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['this_user_is_already_in_your_friend_list']
								  )));
		}
		
		//check if request already sent
		$query = "SELECT * FROM request_friendship WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
				   (user_id = '$profile_id' AND friend_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_already_sent_friendship_request_to_this_user']
								  )));
		}
		
		//send request
		$query = "INSERT INTO request_friendship VALUES ('$user_id', '$profile_id')";
		if($conn->query($query)) {
			echo json_encode(array('success'=>true,
								   'msg'=>$lang['friendship_request_sent']
								  ));
		}
		else {
			echo json_encode(array('success'=>false,
								   'error'=>"Error. Please try again."
								  ));
		}
	}
	else if($action == "remove_from_friends") {
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exists."
								  )));
		}
		
		//check if friends
		$query = "SELECT * FROM friends WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
				   (user_id = '$profile_id' AND friend_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['this_user_is_not_in_your_friend_list']
								  )));
		}
		
		//remove
		$query = "DELETE FROM friends WHERE friend_id = '$user_id' AND user_id = '$profile_id'";
		$conn->query($query);
		$query = "DELETE FROM friends WHERE friend_id = '$profile_id' AND user_id = '$user_id'";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['successfully_removed_this_user_from_your_friends']
							  ));
	}
	else if ($action == "collect_achievement_reward") {
		if(!filter_var($achiev_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Achievement doesn't exists."
								  )));
		}
		
		//check if achievement exists
		$query = "SELECT * FROM achievements WHERE achievement_id = '$achiev_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Achievement doesn't exists."
								  )));
		}
		
		//check if earned achievement
		$query = "SELECT earned, collected FROM user_achievements WHERE user_id = '$user_id' AND achievement_id = '$achiev_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have any rewards for this achievement."
								  )));
		}
		$row = $result->fetch_row();
		list($earned, $collected) = $row;
		if($earned <= 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You already collected rewards for all achievements of this type."
								  )));
		}
		
		//check if will be enough space in the warehouse
		$query = "SELECT capacity FROM user_warehouse WHERE user_id = '$user_id'
				  HAVING capacity >= (SELECT SUM(amount) + (SELECT SUM(amount)
				  FROM achievement_rewards WHERE achievement_id = '$achiev_id') 
				  FROM user_product WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['not_enough_room_in_the_warehouse']
								  )));
		}
		
		//get rewards
		$products = array();
		$query = "SELECT ar.product_id, amount, product_icon, product_name FROM achievement_rewards ar, product_info pi
				  WHERE achievement_id = '$achiev_id' AND pi.product_id = ar.product_id";
		$result_rewards = $conn->query($query);
		while($row_rewards = $result_rewards->fetch_row()) {
			list($product_id, $amount, $product_icon, $product_name) = $row_rewards;
			array_push($products, array("product_id"=>$product_id, "amount"=>$amount, 
										"product_name"=>$product_name, "product_icon"=>$product_icon));
			//determine if user already has that product type and update warehosue
			$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				$query = "UPDATE user_product SET amount = (SELECT * FROM (SELECT amount FROM user_product 
						  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$amount' 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			else {
				$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$amount')";
			}
			$conn->query($query);
		}
		
		//update user_achievements
		$earned--;
		$query = "UPDATE user_achievements SET earned = '$earned' WHERE user_id = '$user_id' 
				  AND achievement_id = '$achiev_id'";
		$conn->query($query);
		
		$collected++;
		$query = "UPDATE user_achievements SET collected = '$collected' WHERE user_id = '$user_id' 
				  AND achievement_id = '$achiev_id'";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['you_have_successfully_collected'],
							   'products'=>$products,
							   'all'=>($earned?false:true)
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
	
	$conn->close();
?>