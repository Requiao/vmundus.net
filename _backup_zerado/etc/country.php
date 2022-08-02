<?php
	//Description: Buy product on market.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/record_statistics.php');
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/law_description.php');
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/get_user_level.php');//getUserLevel($user_id); returns user level
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	//uploadImage($image_name, $folder_path, $old_path = NULL, $width = 400, $height = 200, $ext = 'all', $size = 500000)
	include('../php_functions/upload_image.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId();

	$country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	$profile_id =  htmlentities(stripslashes(strip_tags(trim($_POST['profile_id']))), ENT_QUOTES);
	$amount =  htmlentities(stripslashes(strip_tags(trim($_POST['amount']))), ENT_QUOTES);

	$region_id =  htmlentities(stripslashes(strip_tags(trim($_POST['region_id']))), ENT_QUOTES);
	$country_name =  htmlentities(stripslashes(strip_tags(trim($_POST['country_name']))), ENT_QUOTES);
	$country_abbr =  htmlentities(stripslashes(strip_tags(trim($_POST['country_abbr']))), ENT_QUOTES);
	$currency_name =  htmlentities(stripslashes(strip_tags(trim($_POST['currency_name']))), ENT_QUOTES);
	$currency_abbr =  htmlentities(stripslashes(strip_tags(trim($_POST['currency_abbr']))), ENT_QUOTES);
	$request_id =  htmlentities(stripslashes(strip_tags(trim($_POST['request_id']))), ENT_QUOTES);

	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$MAX_INVESTEMENTS = 3;
	$MAX_INVESTEMENT_AMOUNT = 5;
	$MIN_USER_LVL = 10;
	
	if($action == 'cz_requests' || $action == 'accept_cz' || $action == 'decline_cz') {
		//check if governor
		//check if president
		$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$is_governor = true;
			$row = $result->fetch_row();
			list($position_id, $country_id) = $row;
		}
		else { //check if congressman
			$query = "SELECT country_id FROM congress_details WHERE country_id = 
					 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				$is_governor = true;
				$row = $result->fetch_row();
				list($country_id) = $row;
				$position_id = 3;
			}
			else {
				exit(json_encode(array('success'=>false,
									   'error'=>"You're not a governor and not allowed to perform this action."
									  )));
			}
		}

		//check if has rights to perform this action
		$query = "SELECT * FROM government_country_responsibilities 
				  WHERE country_id = '$country_id' AND responsibility_id = '32' AND position_id = '$position_id'
				  AND have_access = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to perform this action. You don't have appropriate permissions."
								  )));
		}
	}
	
	if($action == 'cancel_country_request') {
		//check request_id
		if(!is_numeric($request_id)) {
			exit(json_encode(array(
                'success'=>false,
                'error'=>"Request doesn't exist."
            )));
		}
		
		$query = "SELECT request_id, fee_paid, flag FROM request_country 
				  WHERE user_id = '$user_id' AND request_id = '$request_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Request doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($request_id, $fee_paid, $country_flag) = $row;

		//update users gold
		$query = "UPDATE user_product SET amount = amount + '$fee_paid' 
				  WHERE user_id = '$user_id' AND product_id = 1";
		$conn->query($query);

		//remove request
		$query = "DELETE FROM request_country WHERE request_id = '$request_id'";
		if($conn->query($query)) {
			unlink("../country_flags/$country_flag");

			$notification = "$fee_paid Gold paid for the country request have been returned to your account.";
			sendNotification($notification, $user_id);

			echo json_encode(array(
				'success'=>true,
				'msg'=>'Request successfully deleted.'
			));
		}
		else {
			exit(json_encode(array(
				"success"=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	elseif($action == 'create_country_info') {
		//check if requesting a country
		$query = "SELECT request_id, country_name, country_abbr, flag, region_name, 
				  currency_abbr, currency_name, fee_paid 
				  FROM request_country rc, regions r 
				  WHERE r.region_id = rc.capital AND user_id = '$user_id'  AND accepted = FALSE 
				  AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			$row = $result->fetch_row();
			list($request_id, $country_name, $country_abbr, $flag, $region_name, 
				 $currency_abbr, $currency_name, $fee_paid) = $row;
			
			echo json_encode(array(
				"success"=>true,
				"requesting"=>true,
				"country"=>array("request_id"=>$request_id, "country_name"=>$country_name, 
					"country_abbr"=>$country_abbr, "country_flag"=>$flag, "region_name"=>$region_name, 
					"currency_abbr"=>$currency_abbr, "currency_name"=>$currency_name, "fee_paid"=>$fee_paid)
			));
		}
		else {
			echo json_encode(array(
				"success"=>true,
				"requesting"=>false,
				"fee"=>"25 Gold"
			));
		}
	}
	else if($action == 'request_new_country') {
		$FEE = 25;//gold

		//can request only one country at a time
		$query = "SELECT * FROM request_country WHERE user_id = '$user_id' 
				  AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"You already sent a request."
			)));
		}

		//user can create only one country
		$query = "SELECT COUNT(*) FROM request_country WHERE user_id = '$user_id' AND accepted = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($created_countries) = $row;
		if($created_countries >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"You can only create one country."
			)));
		}

		//check region_id
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Region doesn't exists."
			)));
		}
		$query = "SELECT * FROM regions WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Region doesn't exists."
			)));
		}

		//check country name
		strValidate($country_name, 5, 20, "Country name");

		$query = "SELECT country_name FROM country WHERE country_name = '$country_name'
				  UNION
				  SELECT country_name FROM request_country 
				  WHERE country_name = '$country_name' AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Country with this name already exists."
			)));
		}
		
		//country abbr
		strValidate($country_abbr, 3, 3, "Country abbreviation");

		$query = "SELECT country_abbr FROM country WHERE country_abbr = '$country_abbr'
				  UNION
				  SELECT country_abbr FROM request_country 
				  WHERE country_abbr = '$country_abbr' AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Country with this abbreviation already exists."
			)));
		}

		//currency_name
		strValidate($currency_name, 5, 15, "Currency name");

		$query = "SELECT currency_name FROM currency WHERE currency_name = '$currency_name'
				  UNION
				  SELECT currency_name FROM request_country 
				  WHERE currency_name = '$currency_name' AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Currency with this name already exists."
			)));
		}

		//currency_abbr
		strValidate($currency_abbr, 3, 3, "Currency abbreviation");

		$query = "SELECT currency_abbr FROM currency WHERE currency_abbr = '$currency_abbr'
				  UNION
				  SELECT currency_abbr FROM request_country 
				  WHERE currency_abbr = '$currency_abbr' AND is_processed = FALSE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Currency with this abbreviation already exists."
			)));
		}

		//check if user has enough gold
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have enough Gold. You need $FEE Gold."
			)));
		}
		list($user_gold) = $row;

		if($FEE > $user_gold) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have enough Gold. You need $FEE Gold."
			)));
		}

		//check flag
		if(file_exists($_FILES['image']['tmp_name'])) {
			$country_flag = uploadImage($country_abbr, '../country_flags', NULL, 280, 140, 'png', 100000);
				
			if($country_flag === 1) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Image must be of png type."
				)));
			}
			if($country_flag === 2) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Your file is too large."
				)));
			}
			if($country_flag === 3) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Only png, jpeg, and jpg are allowed for the user image."
				)));
			}
			if($country_flag === 4) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Image not uploaded. Please try again."
				)));
			}
		}
		else {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Country flag is missing."
			)));
		}

		//create request
		$request_id = getTimeForID() . $user_id;
		$query = "INSERT INTO request_country VALUES('$user_id', '$request_id', '$country_name',
				 '$country_abbr', '$country_flag', '$region_id', '$currency_abbr', '$currency_name',
				 '$FEE', FALSE, FALSE, CURRENT_DATE, CURRENT_TIME, NULL, NULL, NULL)";
		if($conn->query($query)) {
			//get gold for the request
			$query = "UPDATE user_product SET amount = amount - '$FEE' 
					  WHERE user_id = '$user_id' AND product_id = 1";
			$conn->query($query);

			//notify admin
			$notification = "A new country has been requested.";
			sendNotification($notification, 1);

			echo json_encode(array(
				'success'=>true,
				'msg'=>'Request successfully sent and will be processed in the next 48 hours.'
			));
		}
		else {
			unlink("../country_flags/$country_flag");

			exit(json_encode(array(
				'success'=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	elseif($action == 'invest_gov_gold') {
		$product_id = 1;
		
		//check if president/minister
		$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($position_id, $country_id) = $row;
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"You're not a governor and not allowed to perform this action."
								  )));
		}
		
		//test amount
		if(!is_numeric($amount)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Amount must be more than 0."
								  )));
		}
		$amount = round($amount, 2);
		if($amount > $MAX_INVESTEMENT_AMOUNT || $amount < 0.01) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Amount must be less than $MAX_INVESTEMENT_AMOUNT and more than 0.01."
								  )));
		}
		
		//check how many times donated per day
		$query = "SELECT COUNT(*) FROM user_country_invest_history WHERE position_id = '$position_id' AND country_id = '$country_id'
				  AND DATE_ADD(TIMESTAMP(date, time), INTERVAL 1 DAY) >= NOW() GROUP BY position_id, country_id";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			$row = $result->fetch_row();
			list($investements_per_day) = $row;
			if($investements_per_day >= $MAX_INVESTEMENTS) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You can only make $MAX_INVESTEMENTS donations per day."
								  )));
			}
		}
		
		//check if enough products in the warehouse
		$query = "SELECT amount FROM ministry_product WHERE position_id = '$position_id' AND country_id = '$country_id'
				  AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have this product in the warehouse."
								  )));
		}
		$row = $result->fetch_row();
		list($available_amount) = $row;
		if($available_amount < $amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough products in the warehouse."
								  )));
		}
		
		//donate
		$query = "SELECT * FROM country_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
					  country_id = '$country_id' AND product_id = '$product_id') AS temp) + $amount
					  WHERE country_id = '$country_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO country_product VALUES ('$country_id', '$product_id', '$amount')";
		}
		$conn->query($query);
		
		//update user product
		$query = "UPDATE ministry_product SET amount = (SELECT * FROM(SELECT amount FROM ministry_product 
				  WHERE position_id = '$position_id' AND country_id = '$country_id' AND product_id = '$product_id') AS temp) - '$amount' 
				  WHERE position_id = '$position_id' AND country_id = '$country_id' AND product_id = '$product_id'";
		$conn->query($query);

		//record history
		$query = "INSERT INTO user_country_invest_history (user_id, position_id, country_id, for_country_id, amount, date, time) 
				  VALUES ('$user_id', '$position_id', '$country_id', '$country_id', '$amount', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>'You have successfully invested ' . $amount . ' Gold in the country warehouse.'
							  ));
	}
	else if ($action == 'invest_gold') {
		$product_id = 1;
		
		//check user level
		if(getUserLevel($user_id) < $MIN_USER_LVL) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You must have at least level $MIN_USER_LVL"
								  )));
		}
		
		//test country
		$query = "SELECT * FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist"
								  )));
		}
		
		//test amount
		if(!is_numeric($amount)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Amount must be more than 0."
								  )));
		}
		$amount = round($amount, 2);
		if($amount > $MAX_INVESTEMENT_AMOUNT || $amount < 0.01) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Amount must be less than $MAX_INVESTEMENT_AMOUNT and more than 0.01."
								  )));
		}
		
		//check how many times donated per day
		$query = "SELECT COUNT(*) FROM user_country_invest_history WHERE user_id = '$user_id' AND 
				  DATE_ADD(TIMESTAMP(date, time), INTERVAL 1 DAY) >= NOW() GROUP BY user_id";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			$row = $result->fetch_row();
			list($investements_per_day) = $row;
			if($investements_per_day >= $MAX_INVESTEMENTS) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You can only make $MAX_INVESTEMENTS donations per day."
								  )));
			}
		}
		
		//check if enough products in the warehouse
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' 
				  AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have this product in the warehouse."
								  )));
		}
		$row = $result->fetch_row();
		list($available_amount) = $row;
		if($available_amount < $amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough products in the warehouse."
								  )));
		}
		
		//donate
		$query = "SELECT * FROM country_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
					  country_id = '$country_id' AND product_id = '$product_id') AS temp) + $amount
					  WHERE country_id = '$country_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO country_product VALUES ('$country_id', '$product_id', '$amount')";
		}
		$conn->query($query);
		
		//update user product
		$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$amount' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		//record history
		$query = "INSERT INTO user_country_invest_history (user_id, for_country_id, amount, date, time) 
				  VALUES ('$user_id', '$country_id', '$amount', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);

		echo json_encode(array('success'=>true,
							   'msg'=>'You have successfully invested ' . $amount . ' Gold in the country warehouse.'
							  ));
	}
	else if($action == 'decline_cz') {
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist."
								  )));
		}
		$query = "SELECT * FROM users WHERE user_id = '$profile_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist."
								  )));
		}
		
		//check if user is applying
		$query = "SELECT user_id FROM citizenship_change WHERE user_id = '$profile_id'
				  AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This user is not requesting citizenship of your country."
								  )));
		}
		$row = $result->fetch_row();
		list($profile_id) = $row;
		
		//decline
		$query = "DELETE FROM citizenship_change WHERE user_id = '$profile_id'";
		$conn->query($query);
		
		$query = "SELECT country_name FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($country_name) = $row;
		
		$notification = "$country_name declined your citizenship request.";
		sendNotification($notification, $profile_id);
		
		echo json_encode(array('success'=>true,
							   'msg'=>'You have declined citizenship request from this user.'
							  ));
	}
	else if($action == 'accept_cz') {
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist."
								  )));
		}
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$profile_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($old_citizenship) = $row;

		//check if user is applying
		$query = "SELECT user_id FROM citizenship_change WHERE user_id = '$profile_id'
				  AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This user is not requesting citizenship of your country."
								  )));
		}
		
		//update chat
		$query = "DELETE FROM chat_members WHERE user_id = '$profile_id' AND chat_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$profile_id')";
		$conn->query($query);
		
		$access_lvl = 3;//members
		$query = "INSERT INTO chat_members VALUES('$country_id', '$profile_id', '$access_lvl')";
		$conn->query($query);
		
		//check if minister of the treasury
		$query = "SELECT * FROM bank_details WHERE user_id = '$profile_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE bank_details SET user_id = NULL WHERE user_id = '$profile_id'";
			$conn->query($query);
		}
		
		//accept
		$query = "UPDATE user_profile SET citizenship = '$country_id' WHERE user_id = '$profile_id'";
		$conn->query($query);
		
		//history
		$query = "INSERT INTO citizenship_change_history 
			VALUES ('$profile_id', '$old_citizenship, '$country_id', CURRENT_DATE, CURRENT_TIME, '$user_id')";
		$conn->query($query);
		
		$query = "DELETE FROM citizenship_change WHERE user_id = '$profile_id'";
		$conn->query($query);
		
		$query = "SELECT country_name, currency_id FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($country_name, $currency_id) = $row;

		//give currency
		$query = "SELECT * FROM user_currency WHERE user_id = '$profile_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			$query = "UPDATE user_currency SET amount = amount + 250 
					  WHERE user_id = '$profile_id' AND currency_id = '$currency_id'";
			$conn->query($query);		  
		}
		else {
			$query = "INSERT INTO user_currency VALUES ('$profile_id', '$currency_id', 250)";
			$conn->query($query);
		}
		
		$notification = "$country_name accepted your citizenship request.";
		sendNotification($notification, $profile_id);
		
		echo json_encode(array('success'=>true,
							   'msg'=>'You have accepted citizenship request from this user.'
							  ));
	}
	else if($action == 'cz_requests') {
		//get applications
		$query = "SELECT cc.user_id, user_name, user_image FROM users u, user_profile up, citizenship_change cc
				  WHERE u.user_id = cc.user_id AND up.user_id = cc.user_id AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"There are no citizenship requests."
								  )));
		}
		$requests = array();
		while($row = $result->fetch_row()) {
			list($profile_id, $user_name, $user_image) = $row;
			array_push($requests, array("profile_id"=>$profile_id, "user_name"=>$user_name, "user_image"=>$user_image));
		}
		
		echo json_encode(array('success'=>true,
							   'msg'=>'Citizenship requests',
							   'requests'=>$requests
							  ));
	}
	else if($action == 'law_history') {
		if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist."
								  )));
		}
		$query = "SELECT * FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist."
								  )));
		}
		
		$laws = array();
		$query = "SELECT cli.responsibility_id, responsibility, user_name, proposed_date, proposed_time, yes, no, user_id, law_id 
				  FROM political_responsibilities pr, country_law_info cli, users u
				  WHERE pr.responsibility_id = cli.responsibility_id AND u.user_id = proposed_by
				  AND country_id = '$country_id' AND is_processed = TRUE
				  ORDER BY proposed_date DESC, proposed_time DESC LIMIT 100";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($responsibility_id, $responsibility, $governor_name, $proposed_date, $proposed_time, 
				 $yes, $no, $governor_id, $law_id) = $row;
	
			//correct date/time
			$proposed_date = correctDate($proposed_date, $proposed_time);
			$proposed_time = correctTime($proposed_time);
	
			$description = lawDescription($responsibility_id, $law_id);
			
			array_push($laws, array("responsibility"=>$responsibility, "description"=>$description, "governor_id"=>$governor_id,
					   "governor_name"=>$governor_name, "proposed_date"=>$proposed_date, "proposed_time"=>$proposed_time,
					   "no"=>$no, "yes"=>$yes));
		}
	
		echo json_encode(array('success'=>true,
							   'laws'=>$laws
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
?>