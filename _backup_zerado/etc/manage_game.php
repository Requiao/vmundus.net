<?php
	//Description:Process manage_game requests.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/find_similar_colors.php'); //function findSimilarColors($base_color, $colors).
	
	$profile_id = htmlentities(stripslashes(strip_tags(trim($_POST['profile_id']))), ENT_QUOTES);
	
	$ban_id = htmlentities(stripslashes(strip_tags(trim($_POST['ban_id']))), ENT_QUOTES);
	$description = htmlentities(stripslashes(strip_tags(trim($_POST['description']))), ENT_QUOTES);
	$ban_points = htmlentities(stripslashes(strip_tags(trim($_POST['ban_points']))), ENT_QUOTES);
	
	$last_login = htmlentities(stripslashes(strip_tags(trim($_POST['last_login']))), ENT_QUOTES);
	$user_per_ip = htmlentities(stripslashes(strip_tags(trim($_POST['user_per_ip']))), ENT_QUOTES);
	$ip = htmlentities(stripslashes(strip_tags(trim($_POST['ip']))), ENT_QUOTES);
	
	$heading = htmlentities(stripslashes(strip_tags(trim($_POST['heading']))), ENT_QUOTES);
	$update_id = htmlentities(stripslashes(strip_tags(trim($_POST['update_id']))), ENT_QUOTES);
	$description = htmlentities(stripslashes(strip_tags(trim($_POST['description']))), ENT_QUOTES);
	$description_id = htmlentities(stripslashes(strip_tags(trim($_POST['description_id']))), ENT_QUOTES);
	
	$days_in_game = htmlentities(stripslashes(strip_tags(trim($_POST['days_in_game']))), ENT_QUOTES);
	$not_stable_ips = htmlentities(stripslashes(strip_tags(trim($_POST['not_stable_ips']))), ENT_QUOTES);
	
	$bought_from = htmlentities(stripslashes(strip_tags(trim($_POST['bought_from']))), ENT_QUOTES);
	$history_days = htmlentities(stripslashes(strip_tags(trim($_POST['history_days']))), ENT_QUOTES);
	$country_id = htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	$percent = htmlentities(stripslashes(strip_tags(trim($_POST['percent']))), ENT_QUOTES);

	$request_id = htmlentities(stripslashes(strip_tags(trim($_POST['request_id']))), ENT_QUOTES);
	$country_color = htmlentities(stripslashes(strip_tags(trim($_POST['country_color']))), ENT_QUOTES);
	
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	

	$user_id = $_SESSION['user_id'];
	
	if($profile_id == 1 || $profile_id == 1000) {
		exit(json_encode(array("success"=>false,
							   "error"=>"You ar not allowed to view information about this user."
							  )));
	}
	
	if($action == 'accept_country_request') {
		//check request_id
		if(!is_numeric($request_id)) {
			exit(json_encode(array(
                'success'=>false,
                'error'=>"Request doesn't exist."
            )));
		}

		$query = "SELECT request_id, country_name, country_abbr, flag, capital, 
				  currency_abbr, currency_name, user_id
				  FROM request_country rc
				  WHERE request_id = '$request_id' AND accepted = FALSE 
				  AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Request doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($request_id, $country_name, $country_abbr, $country_flag, $country_capital, 
			 $currency_abbr, $currency_name, $requested_by_id) = $row;

		//check country color
		$color_reg = '/^rgb\((\d{0,3}),\s*(\d{0,3}),\s*(\d{0,3})\)$/';
		if(!preg_match($color_reg, $country_color)) {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Invalid input for color.$country_color"
			)));
		}

		$colors = array();
        $query = "SELECT color FROM country";
        $result = $conn->query($query);
        while($row = $result->fetch_row()) {
            list($color) = $row;

            array_push($colors, $color);
		}
		
		if(!findSimilarColors($country_color, $colors)) {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Similar color is already in use."
			)));
		}

		//get country_id
		$query = "SELECT MAX(country_id) FROM country";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_country_id) = $row;

		//create a country
		$country_id = $max_country_id + 1;

		$timezone_id = 1;
		
		$query = "INSERT INTO currency VALUES ('$country_id', '$currency_abbr', '$currency_name', '$country_id')";
		if(!$conn->query($query)) {
			echo "failed currency</br>";
		}

		$query = "INSERT INTO country VALUES ('$country_id', '$country_name', '$country_abbr', '$country_color', 
				'$country_flag', '$country_capital', '$country_id', '$timezone_id')";
		if(!$conn->query($query)) {
			echo "failed country</br>";
		}

		$query = "INSERT INTO congress_details VALUES ('$country_id', 0.00, CURRENT_DATE)";
		if(!$conn->query($query)) {
			echo "failed congress_details</br>";
		}

		$query = "INSERT INTO `bank_details` VALUES ('$country_id',7.00,500.00,7.00,3.00,30,2.50,500.00,5.00,3.00,30,0.007,
				500.00,0.003,500.00, NULL, CURRENT_DATE,10.00);";
		if(!$conn->query($query)) {
			echo "failed bank_details</br>";
		}

		$query = "INSERT INTO country_congress_seats VALUES ('$country_id', 10);";
		if(!$conn->query($query)) {
			echo "failed country_congress_seats</br>";
		}

		$query = "INSERT INTO government_term_length VALUES ('$country_id', 1, 20)";
		if(!$conn->query($query)) {
			echo "failed government_term_length</br>";
		}

		$query = "INSERT INTO government_term_length VALUES ('$country_id', 3, 20)";
		if(!$conn->query($query)) {
			echo "failed government_term_length</br>";
		}

		$query = "INSERT INTO income_tax VALUES ('$country_id', 10)";
		if(!$conn->query($query)) {
			echo "failed income_tax</br>";
		}

		//BUILDING POLICY
		$building_list = array();
		$x = 0;
		$query = "SELECT building_id FROM building_info WHERE product_id IS NOT NULL ORDER BY building_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($building_id) = $row;
			$building_list[$x] = $building_id;
			$x++;
		}

		$count = 0;
		for($u = 0; $u < sizeof($building_list); $u++) {
			$query = "INSERT INTO building_policy VALUES('$country_id', '$building_list[$u]', 0.25)"; 
			$conn->query($query);
			$count++;
		}


		/* POLITICAL RESPONSIBILITIES */
		$query = "SELECT position_id FROM government_positions";
		$result = $conn->query($query);
		$x = 0;
		$positions_arr = array();
		while($row = $result->fetch_row()) {
			list($position_id) = $row;	
			$positions_arr[$x] = $position_id;
			$x++;
		}

		$query = "SELECT responsibility_id FROM political_responsibilities";
		$result = $conn->query($query);
		$x = 0;
		$responsibilities_arr = array();
		while($row = $result->fetch_row()) {
			list($responsibility_id) = $row;	
			$responsibilities_arr[$x] = $responsibility_id;
			$x++;
		}

		for($q = 0; $q < count($positions_arr); $q++) {
			for($a = 0; $a < count($responsibilities_arr); $a++) {
				if($positions_arr[$q] == 1) {//president
					$query = "INSERT INTO government_country_responsibilities VALUES('$country_id', $positions_arr[$q], 
							'$responsibilities_arr[$a]', TRUE, TRUE)";
					$conn->query($query);
				}
			}
		}

		/* COUNTRY GOVERNMENT */
		for($q = 0; $q < count($positions_arr); $q++) {
			$query = "INSERT INTO country_government VALUES ('$country_id', $positions_arr[$q], NULL, 0.00, '2020-01-01')";
			$conn->query($query);
		}

		/* PRODUCT PRODUCTION TAX */
		$query = "SELECT product_id FROM product_info ORDER BY product_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_id) = $row;

			$query = "INSERT INTO product_production_tax VALUES('$country_id', '$product_id', 1)";
			$conn->query($query);
		}

		/* PRODUCT SALE TAX */
		$query = "SELECT product_id FROM product_info ORDER BY product_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_id) = $row;

			$query = "INSERT INTO product_sale_tax VALUES('$country_id', 1, '$product_id')";
			$conn->query($query);
		}

		/* CHAT */
		$chat_id = $country_id;
		$chat_name = $country_name . ' Chat';
		if(strlen($chat_name) > 15) {
			$chat_name = $country_abbr . ' Chat';
		}
		$member_id = 1;
		$access_lvl = 1;

		$query = "INSERT INTO chat_info VALUES('$chat_id', '$chat_name', CURRENT_DATE, CURRENT_TIME)";
		if($conn->query($query)) {
			$query = "INSERT INTO chat_members VALUES('$chat_id', '$member_id', '$access_lvl')";
			$conn->query($query);
			$count++;
		}

		//update request
		$query = "UPDATE request_country SET is_processed = TRUE WHERE request_id = '$request_id'";
		$conn->query($query);

		$query = "UPDATE request_country SET accepted = TRUE WHERE request_id = '$request_id'";
		$conn->query($query);

		$query = "UPDATE request_country SET decision_date = CURRENT_DATE WHERE request_id = '$request_id'";
		$conn->query($query);

		$query = "UPDATE request_country SET decision_time = CURRENT_TIME WHERE request_id = '$request_id'";
		$conn->query($query);

		$query = "UPDATE request_country SET moderator = '$user_id' WHERE request_id = '$request_id'";
		$conn->query($query);

		$notification = "$country_name have been created! You can now start a revolt!";
		sendNotification($notification, $requested_by_id);

		echo json_encode(array(
			'success'=>true,
			'msg'=>'New country successfully created.'
		));
	}
	else if($action == 'decline_country_request') {
		//check request_id
		if(!is_numeric($request_id)) {
			exit(json_encode(array(
                'success'=>false,
                'error'=>"Request doesn't exist."
            )));
		}
		
		$query = "SELECT request_id, fee_paid, flag, user_id FROM request_country 
				  WHERE request_id = '$request_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Request doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($request_id, $fee_paid, $country_flag, $requested_by_id) = $row;

		//update users gold
		$query = "UPDATE user_product SET amount = amount + '$fee_paid' 
				  WHERE user_id = '$requested_by_id' AND product_id = 1";
		$conn->query($query);

		//remove request
		$query = "UPDATE request_country SET is_processed = TRUE WHERE request_id = '$request_id'";
		if($conn->query($query)) {
			unlink("../country_flags/$country_flag");

			$notification = "Request to create a country have been declined.";
			sendNotification($notification, $requested_by_id);

			$notification = "$fee_paid Gold paid for the country request have been returned to your account.";
			sendNotification($notification, $requested_by_id);

			echo json_encode(array(
				'success'=>true,
				'msg'=>'Request successfully declined.'
			));
		}
		else {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	else if($action == 'get_country_requests') {
		//check if has access
		checkIfHasAccess('country_requests');

		//get all country requests
		$query = "SELECT request_id, country_name, country_abbr, flag, region_name, 
				  currency_abbr, currency_name, fee_paid, u.user_id, user_name  
				  FROM request_country rc, regions r, users u 
				  WHERE r.region_id = rc.capital AND accepted = FALSE 
				  AND is_processed = FALSE AND u.user_id = rc.user_id";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			$requests = array();
			while($row = $result->fetch_row()) {
				list($request_id, $country_name, $country_abbr, $flag, $region_name, 
					$currency_abbr, $currency_name, $fee_paid, $requested_by_id, $requested_by_name) = $row;
				
				//get sample colors
				$colors = array();

				$x = 0;
				while($x < 5) {
					$rand_color = 'rgb(' . mt_rand(0, 255) . ', ' . mt_rand(0, 255) . ', ' . mt_rand(0, 255) . ')';
					
					if(findSimilarColors($rand_color, $colors)) {
						array_push($colors, $rand_color);
						$x++;
					}
				}

				array_push($requests, array("request_id"=>$request_id, "country_name"=>$country_name, 
					"country_abbr"=>$country_abbr, "country_flag"=>$flag, "region_name"=>$region_name, 
					"currency_abbr"=>$currency_abbr, "currency_name"=>$currency_name, "fee_paid"=>$fee_paid,
					"requested_by_id"=>$requested_by_id, "requested_by_name"=>$requested_by_name, 
					"colors"=>$colors));
			}
			echo json_encode(array(
				"success"=>true,
				"requests"=>$requests
			));
		}
		else {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"No country requests."
			)));
		}
	}
	else if($action == 'product_market_history') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 14";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}

		if(!empty($profile_id) && !filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"User doesn't exists."
								  )));
		}
		if(!empty($bought_from) && !filter_var($bought_from, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Bought from user doesn't exists."
								  )));
		}
		if((empty($percent) && $percent != 0) || !is_numeric($percent)) {
			$percent = 30;
		}
		
		if(empty($history_days)) {
			$history_days = 3;
		}
		
		if(!empty($profile_id)) {
			$profile_id = "= '$profile_id'";
		}
		else {
			$profile_id = "LIKE '%$profile_id%'";
		}
		
		if(!empty($bought_from)) {
			$bought_from = "= '$bought_from'";
		}
		else {
			$bought_from = "LIKE '%$bought_from%'";
		}
		/*
		if(!empty($country_id)) {
			$country_id = "= '$country_id'";
		}
		else {
			$country_id = "LIKE '%$country_id%'";
		}*/
		ini_set ('memory_limit', '500M');
		$history = array();
		$query = "SELECT pph.user_id, bought_from, IFNULL(pph.country_id, 0), IFNULL(name, 0), product_name, 
				  quantity, pph.price, currency_id, pph.date, time, IFNULL(avp.price, 0), cg.user_id
				  FROM product_info pi, product_purchase_history pph LEFT JOIN average_product_price avp 
				  ON avp.country_id = pph.currency_id
				  AND avp.product_id = pph.product_id AND avp.date = pph.date LEFT JOIN government_positions gp
				  ON gp.position_id = pph.position_id LEFT JOIN country_government cg ON cg.position_id = pph.seller_position_id
				  AND cg.country_id = pph.seller_country_id
				  WHERE pph.product_id = pi.product_id
				  AND pph.user_id $profile_id AND bought_from $bought_from
				  AND DATE_ADD(TIMESTAMP(pph.date, time), INTERVAL '$history_days' DAY) >= NOW()
				  AND bought_from != 1
				  ORDER BY date DESC, time DESC LIMIT 200";
		$result = $conn->query($query);
		$percent = $percent / 100;
		while($row = $result->fetch_row()) {
			list($profile_id, $bought_from, $country_id, $position, $product_name, $quantity, $price,
				 $currency_id, $date, $time, $avg_price, $from_gov) = $row;
			
			//check if user traded with the same IP
			$query_ip = "SELECT * FROM users_ip WHERE user_id = '$profile_id' AND ip IN
						(SELECT ip FROM users_ip WHERE user_id = '$bought_from' OR user_id = '$from_gov') LIMIT 1";
			$result_ip = $conn->query($query_ip);
			if($result_ip->num_rows >= 1) {
				$trans_same_ip = true;
			}
			else {
				$trans_same_ip = false;
			}
			
			$percent_from_price = $price * $percent;
			$difference = $price - $avg_price; 
			$difference = ($difference < 0)?($difference*-1):$difference;
			//mark if overpriced
			if($difference >= $percent_from_price) {
				$overpriced = true;
			}
			else {
				$overpriced = false;
			}
			
			array_push($history, array("profile_id"=>$profile_id,
									   "bought_from"=>$bought_from,
									   "country_id"=>$country_id,
									   "position"=>$position,
									   "product_name"=>$product_name,
									   "quantity"=>$quantity,
									   "price"=>$price,
									   "avg_price"=>$avg_price,
									   "date"=>$date,
									   "time"=>$time,
									   "difference"=>$difference,
									   "overpriced"=>$overpriced,
									   "trans_same_ip"=>$trans_same_ip,
									   "from_gov"=>$from_gov
									   ));
		}
		
		echo json_encode(array("success"=>true,
							   "history"=>$history
							  ));
	}
	else if($action == 'find_vpn_users') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 13";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		if(!empty($profile_id) && !filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"User doesn't exists."
								  )));
		}
		
		if(empty($days_in_game)) {
			$days_in_game = 3;
		}
		
		if(empty($not_stable_ips)) {
			$not_stable_ips = 3;
		}
		
		if(empty($last_login)) {
			$last_login = 3;
		}
		
		if(!empty($profile_id)) {
			$profile_id = "= '$profile_id'";
		}
		else {
			$profile_id = "LIKE '%$profile_id%'";
		}
		
		$vpn_users = array();

		$query = "SELECT t.user_id, user_name, COUNT(t.user_id) AS vpn_ips, DATEDIFF(CURRENT_DATE, register_date) AS days_in_game FROM
				 (SELECT ip, user_id FROM users_ip WHERE DATE_ADD(log_date, INTERVAL '$last_login' DAY) >= NOW() AND user_id $profile_id
				  GROUP BY ip, user_id HAVING COUNT(ip) = 1) AS t, users u
				  WHERE u.user_id = t.user_id
				  GROUP BY t.user_id, register_date
				  HAVING days_in_game >= '$days_in_game' AND vpn_ips >= '$not_stable_ips'
				  ORDER BY register_date DESC, t.user_id";
		$result = $conn->query($query);
		$hidden_ips = array();
		$x = 0;
		while($row = $result->fetch_row()) {
			list($profile_id, $profile_name, $ips, $days_in_game) = $row;
			
			if($profile_id == 1) {
				$hidden_ips[$x] = $ip;
				$x++;
			}
			for($i = 0; $i < $x; $i++) {
				if($hidden_ips[$i] == $ip) {
					continue 2;
				}
			}
			
			
			array_push($vpn_users, array("profile_id"=>$profile_id,
									     "profile_name"=>$profile_name,
									     "ips"=>$ips,
									     "days_in_game"=>$days_in_game
									    ));
		}
		
		echo json_encode(array("success"=>true,
							   "vpn_users"=>$vpn_users
							  ));
	}
	else if($action == 'delete_game_update') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 12";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		if(!is_numeric($update_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Update doesn't exists."
								  )));
		}
		$query = "SELECT * FROM updates WHERE update_id = '$update_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Update doesn't exists."
								  )));
		}

		$query = "DELETE FROM updates_info WHERE update_id = '$update_id'";
		$conn->query($query);
		
		$query = "DELETE FROM updates WHERE update_id = '$update_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Update successfully deleted."
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error."
								  )));
		}
	}
	else if($action == 'delete_desc_game_update') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 12";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		if(!is_numeric($description_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Description doesn't exists."
								  )));
		}
		$query = "SELECT * FROM updates_info WHERE update_desc_id = '$description_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Description doesn't exists."
								  )));
		}

		$query = "DELETE FROM updates_info WHERE update_desc_id = '$description_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Description successfully deleted."
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error."
								  )));
		}
	}
	else if($action == 'add_desc_game_update') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 12";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		if(!is_numeric($update_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Description doesn't exists."
								  )));
		}
		$query = "SELECT * FROM updates WHERE update_id= '$update_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Update doesn't exists."
								  )));
		}
		strValidate($description, 1, 500, 'description');

		$desc_id = getTimeForId() . $user_id;
		$query = "INSERT INTO updates_info VALUES ('$desc_id', '$update_id', '$description')";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Description successfully added."
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error."
								  )));
		}
	}
	else if($action == 'edit_desc_game_update') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 12";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		if(!is_numeric($description_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Description doesn't exists."
								  )));
		}
		$query = "SELECT * FROM updates_info WHERE update_desc_id= '$description_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Description doesn't exists."
								  )));
		}
		strValidate($description, 1, 500, 'description');

		$query = "UPDATE updates_info SET description = '$description' WHERE update_desc_id = '$description_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Description successfully updated."
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error."
								  )));
		}
	}
	else if($action == 'edit_heading_game_update') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 12";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		if(!is_numeric($update_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Update doesn't exists."
								  )));
		}
		$query = "SELECT * FROM updates WHERE update_id= '$update_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Update doesn't exists."
								  )));
		}
		strValidate($heading, 1, 50, 'heading');

		$query = "UPDATE updates SET update_name = '$heading' WHERE update_id = '$update_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Heading ssuccessfully updated."
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error."
								  )));
		}
	}
	else if($action == 'set_new_game_update') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 12";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		strValidate($heading, 1, 50, 'heading');

		$descrip = preg_split("/__/", $description);
		for($x = 0; $x < count($descrip); $x++) {
			if(empty($descrip[$x]) && $x > 0) {
				continue;
			}
			strValidate($descrip[$x], 1, 500, 'description');
		}
		
		$update_id = getTimeForId() . $user_id;
		$query = "INSERT INTO updates VALUES ('$update_id', '$heading', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		for($x = 0; $x < count($descrip); $x++) {
			if(empty($descrip[$x])) {
				continue;
			}
			$desc_id = getTimeForId() . $user_id;
			$query = "INSERT INTO updates_info VALUES ('$desc_id', '$update_id', '$descrip[$x]')";
			$conn->query($query);
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Update ssuccessfully inserted"
							  ));
	}
	else if($action == 'find_multiple_accounts') {
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 11";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		
		if(!empty($profile_id) && !filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"User doesn't exists."
								  )));
		}
		
		if(!filter_var($last_login, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Last login error."
								  )));
		}
		
		if(!filter_var($user_per_ip, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Users per IP error."
								  )));
		}
		
		if(empty($last_login) || $last_login <= 0) {
			$last_login = 5;
		}
		
		if(empty($user_per_ip) || $user_per_ip <= 0) {
			$user_per_ip = 2;
		}
		$multies = array();

		if(!empty($profile_id)) {
			$profile_id = "= '$profile_id'";
		}
		else {
			$profile_id = "LIKE '%$profile_id%'";
		}
		
		$query = "SELECT u.user_id, user_name, ip, log_date, COUNT(ip) FROM users u, users_ip ui
				  WHERE DATE_ADD(log_date, INTERVAL '$last_login' DAY) >= NOW() 
				  AND ip IN (SELECT ip FROM (SELECT user_id, ip FROM users_ip WHERE
				  DATE_ADD(TIMESTAMP(log_date, log_time), INTERVAL '$last_login' DAY) >= NOW()
				  GROUP BY user_id, ip) as t
				  GROUP BY ip HAVING count(ip) >= '$user_per_ip') AND u.user_id $profile_id AND ip LIKE '%$ip%'
				  AND u.user_id = ui.user_id GROUP BY user_id, ip, log_date ORDER BY log_date DESC, u.user_id";
		$result = $conn->query($query);
		$hidden_ips = array();
		$x = 0;
		while($row = $result->fetch_row()) {
			list($profile_id, $profile_name, $ip, $log_date, $ip_num) = $row;
			$log_date = correctDate($log_date, date('H:i:s'));
			
			if($profile_id == 1) {
				$hidden_ips[$x] = $ip;
				$x++;
			}
			for($i = 0; $i < $x; $i++) {
				if($hidden_ips[$i] == $ip) {
					continue 2;
				}
			}
			
			array_push($multies, array("profile_id"=>$profile_id,
									   "profile_name"=>$profile_name,
									   "ip"=>$ip,
									   "log_date"=>$log_date,
									   "ip_num"=>$ip_num,
									   "profile_id"=>$profile_id
									   ));
		}
		
		echo json_encode(array("success"=>true,
							   "multies"=>$multies
							  ));
	}
	else if($action == 'bud_find_user') {
		//check if has access to ban users
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 3";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
							  "error"=>"User doesn't exists."
							 )));
		}
		
		$query = "SELECT user_name, user_image
				  FROM users u, user_profile up 
				  WHERE u.user_id = '$profile_id' AND up.user_id = u.user_id";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$is_user_exist = false;
			exit(json_encode(array("success"=>false,
								   "error"=>'User doesn\'t exists.'
								   )));
		}
		$row = $result->fetch_row();
		list($user_name, $user_image) = $row;
		$user_info = array("user_name"=>$user_name,
						   "user_image"=>$user_image,
						   "profile_id"=>$profile_id
						  );
		
		//user ban history
		$query = "SELECT ban_name, description, extra_description, user_name, moderator_id, date, time, ubp.points 
				  FROM users u, ban_points_info bpi, user_ban_points ubp 
				  WHERE ubp.user_id = '$profile_id' AND bpi.ban_id = ubp.ban_id AND u.user_id = ubp.moderator_id";
		$result = $conn->query($query);
		$ban_history = array();
		if($result->num_rows > 0) {
			while($row = $result->fetch_row()) {
				list($ban_name, $description, $extra_description, $moderator_name, $moderator_id, $ban_date, $ban_time, $points) = $row;
				array_push($ban_history, array("ban_name"=>$ban_name,
											   "description"=>$description,
											   "extra_description"=>$extra_description,
											   "moderator_name"=>$moderator_name,
											   "moderator_id"=>$moderator_id,
											   "ban_date"=>correctDate($ban_date, $ban_time),
											   "ban_time"=>correctTime($ban_time),
											   "points"=>$points
											  ));
			}
		}
		
		//ban info
		$query = "SELECT ban_id, ban_name, description, points FROM ban_points_info";
		$result = $conn->query($query);
		$ban_info = array();
		while($row = $result->fetch_row()) {
			list($ban_id, $ban_name, $description, $points) = $row;
			array_push($ban_info, array("ban_id"=>$ban_id,
									    "ban_name"=>$ban_name,
									    "description"=>$description,
										"points"=>$points
										));
		}
		echo json_encode(array("success"=>true, 'user_info'=>$user_info, 'ban_history'=>$ban_history, 'ban_info'=>$ban_info));
	}
	else if ($action == 'ban_user') {
		//check if has access to ban users
		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND usa.access_id = 3";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"User doesn't exists."
								  )));
		}
		if(!filter_var($ban_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Ban type doesn't exists."
								  )));
		}
		if(!is_numeric($ban_points)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid input for points."
								  )));
		}
		if(iconv_strlen($description) < 10) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Description must be at least 10 chars."
								  )));
		}
		
		//check if user exists
		$query = "SELECT * FROM users WHERE user_id = '$profile_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"User doesn't exists."
								  )));
		}
		
		//check if ban exists
		$query = "SELECT * FROM ban_points_info WHERE ban_id = '$ban_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Ban type doesn't exists."
								  )));
		}
		
		//Ban user
		$query = "INSERT INTO user_ban_points VALUES ('$profile_id', '$user_id', '$ban_points', '$ban_id', 
													  '$description', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		$query = "SELECT SUM(points) FROM user_ban_points WHERE user_id = '$profile_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($sum_points) = $row;

		if($sum_points >= 1000) {
			$date_time = date("9999-01-01 00:00:00");
			$date = date("Y-m-d", strtotime($date_time));
			$time = date("H:i:s", strtotime($date_time));
		}
		else {
			$date_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " + $sum_points hours"));
			$date = date("Y-m-d", strtotime($date_time));
			$time = date("H:i:s", strtotime($date_time));
		}

		
		$query = "INSERT INTO banned_users VALUES ('$profile_id', '$date', '$time')";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"User ssuccessfully banned for $sum_points hour/s. You banned for $ban_points."
								  ));
		}
		else {
			echo json_encode(array("success"=>true,
								   "msg"=>"Error."
								  ));
		}
	}
	else {
		echo json_encode(array(
			"success"=>false,
			"error"=>"Invalid request."
		));
	}

	function checkIfHasAccess($access_name) {
		global $conn, $user_id;

		$query = "SELECT name, al.access_id FROM users_special_access usa, access_levels al
				  WHERE user_id = '$user_id' AND usa.access_id = al.access_id AND al.name = '$access_name'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"You don't have access to this option."
			)));
		}
	}
	
?>