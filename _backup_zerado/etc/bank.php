<?php
	//Description: Manage Bank
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$amount =  htmlentities(stripslashes(strip_tags(trim($_POST['amount']))), ENT_QUOTES);
	$price =  htmlentities(stripslashes(strip_tags(trim($_POST['price']))), ENT_QUOTES);
	$type =  htmlentities(stripslashes(strip_tags(trim($_POST['type']))), ENT_QUOTES);
	$days =  htmlentities(stripslashes(strip_tags(trim($_POST['days']))), ENT_QUOTES);
	$credit_id =  htmlentities(stripslashes(strip_tags(trim($_POST['credit_id']))), ENT_QUOTES);
	$currency_country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$DAYS_IN_GAME = 10;//must be N days in game to get a credit
	$MAX_UNLIMITED_WAREHOUSE_CAPACITY = 900000000;
	$product_id = 1;//gold
	$CHANGE_INTERVAL = 24;//hours

	exit(json_encode(array(
		"success"=>false,
		"error"=>"Under development."
	)));
	
	//check if governor
	$is_governor = false;
	$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$is_governor = true;
		$row = $result->fetch_row();
		list($position_id, $country_id) = $row;
	}
	
	if($action == 'set_currency_sell_price') {
		//check if manager
		$query = "SELECT country_id FROM bank_details WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option"
								   )));
		}
		$row = $result->fetch_row();
		list($country_id) = $row;
		
		//test price
		if($price < 0.001) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Price must be more than or equal to 0.001"
								   )));
		}
		$price = round($price, 3);
		if($price > 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Price must be less than or equal to 1"
								   )));
		}
		
		//check when change made. allow to change every 24hours
		$query = "SELECT date, time FROM last_bank_changes WHERE 
				  DATE_ADD(TIMESTAMP(date, time), INTERVAL 24 HOUR) >= NOW()
				  AND change_made = 'sell_currency_price' AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row = $result->fetch_row();
			list($date, $time) = $row;
			
			$current_date = date('Y-m-d');
			$current_time = date('H:i:s');
			
			$date_time = date('Y-m-d H:i:s', strtotime($date . ' ' . $time . " + $CHANGE_INTERVAL hours"));
				
			$date1 = new DateTime($current_date . ' ' . $current_time);
			$date2 = new DateTime($date_time);
			$diff = date_diff($date1,$date2);
			$days_in = $diff->format("%a");
			$time_in = $diff->format("%H:%I:%S");
			
			$time = $time_in . " hours";
			
			exit(json_encode(array('success'=>false,
								   'error'=>"You can change 'currency buy price' every $CHANGE_INTERVAL hours. 
											 You will be able to change 'currency buy price' in $time"
								  )));
		}
			
		//update
		$query = "UPDATE bank_details SET sell_currency_price = '$price' WHERE country_id = '$country_id'";
		$conn->query($query);
		
		//record change
		$query = "SELECT * FROM last_bank_changes WHERE change_made = 'sell_currency_price' AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO last_bank_changes VALUES ('$country_id', 'sell_currency_price', CURRENT_DATE, CURRENT_TIME)";
			$conn->query($query);
		}
		else {
			$query = "UPDATE last_bank_changes SET date = CURRENT_DATE WHERE change_made = 'sell_currency_price' 
					  AND country_id = '$country_id'";
			$conn->query($query);
			
			$query = "UPDATE last_bank_changes SET time = CURRENT_TIME WHERE change_made = 'sell_currency_price' 
					  AND country_id = '$country_id'";
			$conn->query($query);
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"New currency buy price successfully set"
							  ));
	}
	else if($action == 'set_currency_buy_price') {
		//check if manager
		$query = "SELECT country_id FROM bank_details WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option"
								   )));
		}
		$row = $result->fetch_row();
		list($country_id) = $row;
		
		//test price
		if($price < 0.001) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Price must be more than or equal to 0.001"
								   )));
		}
		$price = round($price, 3);
		if($price > 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Price must be less than or equal to 1"
								   )));
		}
		
		//check when change made. allow to change every 24hours
		$query = "SELECT date, time FROM last_bank_changes WHERE 
				  DATE_ADD(TIMESTAMP(date, time), INTERVAL 24 HOUR) >= NOW()
				  AND change_made = 'buy_currency_price' AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row = $result->fetch_row();
			list($date, $time) = $row;
			
			$current_date = date('Y-m-d');
			$current_time = date('H:i:s');
			
			$date_time = date('Y-m-d H:i:s', strtotime($date . ' ' . $time . " + $CHANGE_INTERVAL hours"));
				
			$date1 = new DateTime($current_date . ' ' . $current_time);
			$date2 = new DateTime($date_time);
			$diff = date_diff($date1,$date2);
			$days_in = $diff->format("%a");
			$time_in = $diff->format("%H:%I:%S");
			
			$time = $time_in . " hours";
			
			exit(json_encode(array('success'=>false,
								   'error'=>"You can change 'currency buy price' every $CHANGE_INTERVAL hours. 
											 You will be able to change 'currency buy price' in $time"
								  )));
		}
			
		//update
		$query = "UPDATE bank_details SET buy_currency_price = '$price' WHERE country_id = '$country_id'";
		$conn->query($query);
		
		//record change
		$query = "SELECT * FROM last_bank_changes WHERE change_made = 'buy_currency_price' AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO last_bank_changes VALUES ('$country_id', 'buy_currency_price', CURRENT_DATE, CURRENT_TIME)";
			$conn->query($query);
		}
		else {
			$query = "UPDATE last_bank_changes SET date = CURRENT_DATE WHERE change_made = 'buy_currency_price' 
					  AND country_id = '$country_id'";
			$conn->query($query);
			
			$query = "UPDATE last_bank_changes SET time = CURRENT_TIME WHERE change_made = 'buy_currency_price' 
					  AND country_id = '$country_id'";
			$conn->query($query);
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"New currency buy price successfully set"
							  ));
	}
	else if($action == 'sell_currency') {
		if(!filter_var($currency_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists"
								   )));
		}
		
		//test amount
		if(!is_numeric($amount) || $amount < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 1"
								   )));
		}
		$amount = floor($amount);
		
		$query = "SELECT sell_currency_price, sell_currency_limit, currency_abbr, c.currency_id, user_id
				  FROM bank_details bd, currency cu, country c
				  WHERE bd.country_id = '$currency_country_id' AND cu.currency_id = c.currency_id AND c.country_id = bd.country_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists"
								   )));
		}
		$row = $result->fetch_row();
		list($sell_currency_price, $sell_currency_limit, $currency_abbr, $currency_id, $manager_id) = $row;
		
		//get sold currency for today
		$query = "SELECT IFNULL(SUM(amount), 0) FROM user_currency_sold 
				  WHERE user_id = '$user_id' AND country_id = '$currency_country_id' AND date = CURRENT_DATE";
		$result = $conn->query($query);
		$row =  $result->fetch_row();
		list($sold_currency) = $row;
		
		if($sold_currency + $amount > $sell_currency_limit) {
			$can_buy = $sell_currency_limit - $sold_currency;
			exit(json_encode(array("success"=>false,
								   "error"=>"Today you can only sell $can_buy $currency_abbr"
								   )));
		}
		
		$need_products = $sell_currency_price * $amount;
		
		//check if user have enough currency
		$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id' 
				  AND amount >= '$amount'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have enough currency."
								  )));
		}
		
		//check if enough gold in the bank
		$query = "SELECT amount FROM bank_product WHERE country_id = '$currency_country_id' AND product_id = '$product_id' 
				  AND amount >= '$need_products'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Not enough Gold in the Bank."
								  )));
		}
		
		//update user currency
		$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
				  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) - '$amount' WHERE user_id = '$user_id' 
				  AND currency_id = '$currency_id'";
		$conn->query($query);
		
		//update user product
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$need_products' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$need_products')";
		}
		$conn->query($query);
		
		//update bank product
		$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
				  WHERE country_id = '$currency_country_id' AND product_id = '$product_id') AS temp) - '$need_products' 
				  WHERE country_id = '$currency_country_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		//update bank currency
		$query = "SELECT * FROM bank_currency WHERE country_id = '$currency_country_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO bank_currency VALUES ('$currency_country_id', '$currency_id', '$amount')";
		}
		else {
			$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
					  WHERE country_id = '$currency_country_id' AND currency_id = '$currency_id') AS temp) + '$amount' 
					  WHERE country_id = '$currency_country_id' 
					  AND currency_id = '$currency_id'";
		}
		$conn->query($query);
		
		//record history
		$query = "INSERT INTO user_currency_sold VALUES ('$user_id', '$currency_country_id', '$amount', '$sell_currency_price',
				  CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		//if manager then display total currency/gold
		$manager = false;
		if(($manager_id == $user_id || $is_governor) && $country_id == $currency_country_id) {
			$query = "SELECT bc.amount, bp.amount FROM bank_currency bc, bank_product bp
					  WHERE bp.country_id = bc.country_id AND bc.country_id = '$currency_country_id'
					  AND currency_id = '$currency_id' AND product_id = 1";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($bank_currency, $bank_gold) = $row;
			$bank_currency = number_format($bank_currency, '3', '.', ' ');
			$bank_gold = number_format($bank_gold, '3', '.', ' ');
			
			$manager = true;
		}
		
		//get user's total currency
		$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_currency) = $row;
		
		echo json_encode(array("success"=>true,
							   "msg"=>"You have successfully sold $amount $currency_abbr for $need_products Gold.",
							   "currency_total"=>"$bank_currency ($currency_abbr)",
							   "gold_total"=>"$bank_gold (Gold)",
							   "manager"=>$manager,
							   "user_currency"=>number_format($user_currency, '2', '.', ' ') . " $currency_abbr",
							   "currency_id"=>$currency_id
							  ));
	}
	else if($action == 'buy_currency') {
		if(!filter_var($currency_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Choose currency"
								   )));
		}
		
		//test amount
		if(!is_numeric($amount) || $amount < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 1"
								   )));
		}
		$amount = floor($amount);
		
		$query = "SELECT buy_currency_price, buy_currency_limit, currency_abbr, c.currency_id, user_id
				  FROM bank_details bd, currency cu, country c
				  WHERE bd.country_id = '$currency_country_id' AND cu.currency_id = c.currency_id AND c.country_id = bd.country_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists"
								   )));
		}
		$row =  $result->fetch_row();
		list($buy_currency_price, $buy_currency_limit, $currency_abbr, $currency_id, $manager_id) = $row;
		
		//get bought currency for today
		$query = "SELECT IFNULL(SUM(amount), 0) FROM user_currency_bought 
				  WHERE user_id = '$user_id' AND country_id = '$currency_country_id' AND date = CURRENT_DATE";
		$result = $conn->query($query);
		$row =  $result->fetch_row();
		list($bought_currency) = $row;
		
		if($bought_currency + $amount > $buy_currency_limit) {
			$can_buy = $buy_currency_limit - $bought_currency;
			exit(json_encode(array("success"=>false,
								   "error"=>"Today you can only buy $can_buy $currency_abbr"
								   )));
		}
		
		$need_products = $buy_currency_price * $amount;
		
		//check if enough gold in user warehouse
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id' AND amount >= '$need_products'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have enough Gold."
								  )));
		}
		
		//check if enough currency in the bank
		$query = "SELECT amount FROM bank_currency WHERE country_id = '$currency_country_id' AND currency_id = '$currency_id' 
				  AND amount >= '$amount'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Not enough currency in the Bank."
								  )));
		}
		
		//buy currency
		$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
					  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) + '$amount' WHERE user_id = '$user_id' 
					  AND currency_id = '$currency_id'";
		}
		else {
			$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', '$amount')";
		}
		$conn->query($query);
		
		//update user product
		$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$need_products' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		//update bank product
		$query = "SELECT * FROM bank_product WHERE country_id = '$currency_country_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO bank_product VALUES ('$currency_country_id', '$product_id', '$need_products')";
		}
		else {
			$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
					  WHERE country_id = '$currency_country_id' AND product_id = '$product_id') AS temp) + '$need_products' 
					  WHERE country_id = '$currency_country_id' AND product_id = '$product_id'";
		}
		$conn->query($query);
		
		//update bank currency
		$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
				  WHERE country_id = '$currency_country_id' AND currency_id = '$currency_id') AS temp) - '$amount' 
				  WHERE country_id = '$currency_country_id' 
				  AND currency_id = '$currency_id'";
		$conn->query($query);
		
		//record history
		$query = "INSERT INTO user_currency_bought VALUES ('$user_id', '$currency_country_id', '$amount', '$buy_currency_price',
				  CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		//if manager then display total currency/gold
		$manager = false;
		if(($manager_id == $user_id || $is_governor) && $country_id == $currency_country_id) {
			$query = "SELECT bc.amount, bp.amount FROM bank_currency bc, bank_product bp
					  WHERE bp.country_id = bc.country_id AND bc.country_id = '$currency_country_id'
					  AND currency_id = '$currency_id' AND product_id = 1";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($bank_currency, $bank_gold) = $row;
			$bank_currency = number_format($bank_currency, '3', '.', ' ');
			$bank_gold = number_format($bank_gold, '3', '.', ' ');
			
			$manager = true;
		}
		
		//get user's total currency
		$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_currency) = $row;
		
		echo json_encode(array("success"=>true,
							   "msg"=>"You have successfully bought $amount $currency_abbr",
							   "currency_total"=>"$bank_currency ($currency_abbr)",
							   "gold_total"=>"$bank_gold (Gold)",
							   "manager"=>$manager,
							   "user_currency"=>number_format($user_currency, '2', '.', ' ') . " $currency_abbr",
							   "currency_id"=>$currency_id
							  ));
	}
	else if($action == 'buy_currency_info') {
		if(!filter_var($currency_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists"
								   )));
		}
		
		$query = "SELECT buy_currency_price, buy_currency_limit, currency_abbr FROM bank_details bd, currency cu, country c
				  WHERE bd.country_id = '$currency_country_id' AND cu.currency_id = c.currency_id AND c.country_id = bd.country_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists"
								   )));
		}
		$row =  $result->fetch_row();
		list($buy_currency_price, $buy_currency_limit, $currency_abbr) = $row;
		
		echo json_encode(array("success"=>true,
							   "currency_price"=>number_format($buy_currency_price, '3', '.', ' ')  . " Gold",
							   "buy_limit"=>number_format($buy_currency_limit, '2', '.', ' ')  . " $currency_abbr"
							  ));
	}
	else if($action == 'return_to_country') {
		//type
		if($type != 'gold' && $type != 'currency') {
			exit(json_encode(array("success"=>false,
								   "error"=>"Type error"
								   )));
		}
		//test amount
		if(!is_numeric($amount) || $amount < 0.1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 0.1"
								   )));
		}
		
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row =  $result->fetch_row();
		list($country_id) = $row;
		
		//check if manager
		$query = "SELECT user_id FROM bank_details WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		$row =  $result->fetch_row();
		list($manager_id) = $row;
		
		if($manager_id != $user_id) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option"
								   )));
		}
		
		//get currency abbr
		$query = "SELECT currency_abbr, currency_id FROM currency WHERE currency_id = 
				 (SELECT currency_id FROM country WHERE country_id = '$country_id')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($currency_abbr, $currency_id) = $row;
		
		//get available currency/gold
		if($type == 'gold') {
			$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND
					  product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Not enough gold in the bank"
									  )));
			}
			$row = $result->fetch_row();
			list($availabe_amount) = $row;
		}
		else {
			$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id'
					  AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Not enough currency in the bank"
									  )));
			}
			$row = $result->fetch_row();
			list($availabe_amount) = $row;
		}
	
		if($amount > $availabe_amount) {
			if($type == 'gold') {
				exit(json_encode(array("success"=>false,
									   "error"=>"You can only return $availabe_amount Gold"
									  )));
			}
			else {
				exit(json_encode(array("success"=>false,
									   "error"=>"You can only return $availabe_amount $currency_abbr"
									  )));
			}
		}
		
		if($type == 'gold') {
			//update country product
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
			
			//update bank warehouse
			$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
					  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) - '$amount' 
					  WHERE country_id = '$country_id' AND product_id = '$product_id'";
			$conn->query($query);
			
			//get total gold
			$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total) = $row;
			$total = number_format($total, '3', '.', ' ');
			
			echo json_encode(array("success"=>true,
								   "msg"=>"You have successfully returned to the country $amount Gold",
								   "total"=>"$total (Gold)"
								  ));
		}
		else if ($type == 'currency') {
			//update country budget
			$query = "SELECT * FROM country_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) + '$amount' 
						  WHERE country_id = '$country_id'  AND currency_id = '$currency_id'";
			}
			else {
				$query = "INSERT INTO country_currency VALUES('$country_id', '$currency_id', '$amount')";
			}
			$conn->query($query);

			//update bank budget
			$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) - '$amount' 
					  WHERE country_id = '$country_id' 
					  AND currency_id = '$currency_id'";
			$conn->query($query);
			
			//get total currency
			$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total) = $row;
			$total = number_format($total, '3', '.', ' ');
			
			echo json_encode(array("success"=>true,
								   "msg"=>"You have successfully returned to the country $amount $currency_abbr",
								   "total"=>"$total ($currency_abbr)"
								  ));
		}
	}
	else if($action == 'pay_debt') {
		if(!is_numeric($credit_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Credit doesn't exist."
								   )));
		}
		
		if(!is_numeric($amount) || $amount < 0.001) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 0.001"
								   )));
		}
		
		//check credit
		$query = "SELECT amount + fee - returned, amount + fee, type, country_id, uc.currency_id, returned,
				 (CASE WHEN type = 'gold' THEN 'Gold' ELSE currency_abbr END) FROM user_credit uc, currency cu
				  WHERE credit_id = '$credit_id' AND user_id = '$user_id' AND cu.currency_id = uc.currency_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Credit doesn't exist."
								   )));
		}
		$row =  $result->fetch_row();
		list($left_to_pay, $total_to_pay, $type, $country_id, $currency_id, $returned, $currency_abbr) = $row;
		
		if($amount > $left_to_pay) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You have to pay back only $left_to_pay $currency_abbr"
								   )));
		}
		
		//check if user has enough currency/gold
		//check if enough currency/gold
		if($type == 'gold') {
			$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id' AND amount >= '$amount'";
		}
		else {
			$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id' AND amount >= '$amount'";
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have enough $currency_abbr"
								   )));
		}
		
		//update Banks currency/Gold
		if($type == 'currency') {
			$query = "SELECT * FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO bank_currency VALUES ('$country_id', '$currency_id', '$amount')";
			}
			else {
				$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) + '$amount' 
						  WHERE country_id = '$country_id' 
						  AND currency_id = '$currency_id'";
			}
		}
		else if ($type == 'gold') {
			$query = "SELECT * FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO bank_product VALUES ('$country_id', '$product_id', '$amount')";
			}
			else {
				$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
						  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) + '$amount' 
						  WHERE country_id = '$country_id' AND product_id = '$product_id'";
			}
		}
		$conn->query($query);
		
		//update user currency/product
		if($type == 'gold') {
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$amount' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			
		}
		else {
			$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
					  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) - '$amount' WHERE user_id = '$user_id' 
					  AND currency_id = '$currency_id'";
		}
		$conn->query($query);
		
		$returned += $amount;
		$query = "UPDATE user_credit SET returned = '$returned' WHERE credit_id = '$credit_id'";
		$conn->query($query);
		
		$payed_all = false;
		if(round($returned, 3) >= $total_to_pay) {
			$payed_all = true;
			
			$query = "UPDATE user_credit SET active = FALSE WHERE credit_id = '$credit_id'";
			$conn->query($query);
		}
		
		
		//get manager id
		$query = "SELECT user_id FROM bank_details WHERE country_id = '$country_id'";
		$conn->query($query);
		$result = $conn->query($query);
		$row =  $result->fetch_row();
		list($manager_id) = $row;
		
		//if manager then display total currency/gold
		$manager = false;
		if($manager_id == $user_id || $is_governor) {
			if($type == 'gold') {
				$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = 1";
			}
			else {
				$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			}
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total) = $row;
			$total = number_format($total, '3', '.', ' ');
			
			$manager = true;
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Successfully returned $amount $currency_abbr.",
							   "payed_all"=>$payed_all,
							   "left"=>number_format($left_to_pay - $amount, '3', '.', ' ') . " $currency_abbr",
							   "total"=>$total . ' (' . (($type=='gold')?'Gold':$currency_abbr) . ')',
							   "manager"=>$manager,
							   "type"=>$type,
							   "total_to_pay"=>$total_to_pay
							  ));
	}
	else if($action == 'get_credit') {
		$query = "SELECT COUNT(*) FROM (SELECT user_id, logdate FROM session_table WHERE user_id = '$user_id' 
				  GROUP BY logdate, user_id) AS temp";
		$result = $conn->query($query);
		$row =  $result->fetch_row();
		list($log_amount) = $row;
		if($log_amount < $DAYS_IN_GAME) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You must be active in the game for at least $DAYS_IN_GAME days."
								   )));
		}
		
		//get user's country
		$query = "SELECT citizenship, currency_id FROM user_profile up, country c 
				  WHERE user_id = '$user_id' AND c.country_id = citizenship";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($country_id, $currency_id) = $row;

		//get credit details
		$query = "SELECT currency_credit_rate, currency_credit_limit, gold_credit_rate, gold_credit_limit, credit_max_days,
				  currency_abbr, user_id
				  FROM bank_details cdr, currency 
				  WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($currency_credit_rate, $currency_credit_limit, $gold_credit_rate, $gold_credit_limit, $credit_max_days,
			 $currency_abbr, $manager_id) = $row;
	
		//type
		if($type != 'gold' && $type != 'currency') {
			exit(json_encode(array("success"=>false,
								   "error"=>"Type error"
								   )));
		}
		
		//test amount
		if(!is_numeric($amount) || $amount < 0.1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 0.1"
								   )));
		}
		if($type == 'gold' && $amount > $gold_credit_limit) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum amount for Gold credit is $gold_credit_limit"
								   )));
		}
		if($type == 'currency' && $amount > $currency_credit_limit) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum amount for Currency credit is $currency_credit_limit"
								   )));
		}
		
		//test days
		if(!is_numeric($days) || $days < 3) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Days must be more than or equal to 3"
								   )));
		}
		if($days > $credit_max_days) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum days for credit is $credit_max_days"
								   )));
		}
		
		//check total user's credits
		$query = "SELECT IFNULL(SUM(CASE WHEN type = 'currency' THEN amount + fee ELSE 0 END), 0) AS total_currency_deposit,
				  IFNULL(SUM(CASE WHEN type = 'gold' THEN amount + fee ELSE 0 END), 0) AS total_gold_deposit
				  FROM user_credit WHERE user_id = '$user_id' AND active = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_currency_credit, $total_gold_credit) = $row;
		
		if($type == 'currency' && ($total_currency_credit + $amount > $currency_credit_limit)) {
			$left = $currency_credit_limit - $total_currency_credit;
			$left = $left<0?0:$left;
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum credit limit for Currency is $currency_credit_limit. You can get credit for" .
											" only $left $currency_abbr"
								   )));
		}
		if($type == 'gold' && ($total_gold_credit + $amount > $gold_credit_limit)) {
			$left = $gold_credit_limit - $total_gold_credit;
			$left = $left<0?0:$left;
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum credit limit for Gold is $gold_credit_limit You can get credit for" .
											" only $left Gold"
								   )));
		}
		
		//check if enough currency/gold in the bank
		if($type == 'gold') {
			$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id' 
					  AND amount >= '$amount'";
			
		}
		else {
			$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id' 
					  AND amount >= '$amount'";
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			if($type == 'gold') {
				$error = "Not enough Gold in the Bank.";
			}
			else if ($type == 'currency') {
				$error = "Not enough Currency in the Bank.";
			}
			exit(json_encode(array("success"=>false,
								   "error"=>$error
								   )));
		}
		
		//get credit
		$credit_id = getTimeForId() . $user_id;
		
		if($type == 'gold') {
			$rate = $gold_credit_rate;
		}
		else {
			$rate = $currency_credit_rate;
		}
		
		$query = "INSERT INTO user_credit VALUES ('$credit_id', '$country_id', '$currency_id', '$user_id', '$amount', '$days',
				 '$rate', 0, '$type', CURRENT_DATE, CURRENT_TIME, '$days', TRUE, 0)";
		if($conn->query($query)) {
			$fee += round($amount * ($rate/100), 3);
			//update earned
			$query = "UPDATE user_credit SET fee = '$fee' WHERE credit_id = '$credit_id'";
			$conn->query($query);
			
			//update user's currency/gold
			if($type == 'currency') {
				$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) { 
					$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
							  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) + '$amount' WHERE user_id = '$user_id' 
							  AND currency_id = '$currency_id'";
				}
				else {
					$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', '$amount')";
				}
			}
			else if ($type == 'gold') {
				$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) { 
					$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
							  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$amount' 
							  WHERE user_id = '$user_id' AND product_id = '$product_id'";
				}
				else {
					$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$amount')";
				}
			}
			if(!$conn->query($query)) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Error"
									   )));
			}
			
			//update Banks currency/Gold
			if($type == 'currency') {
				$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) - '$amount' 
						  WHERE country_id = '$country_id' 
						  AND currency_id = '$currency_id'";
			}
			else if ($type == 'gold') {
				$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
						  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) - '$amount' 
						  WHERE country_id = '$country_id' AND product_id = '$product_id'";
			}
			$conn->query($query);
			
			//get user image and name
			$query = "SELECT user_name, user_image FROM user_profile up, users u WHERE u.user_id = up.user_id
					  AND up.user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($user_name, $user_image) = $row;
			
			//if manager then display total currency/gold
			$manager = false;
			if($manager_id == $user_id || $is_governor) {
				if($type == 'gold') {
					$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = 1";
				}
				else {
					$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
				}
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($total) = $row;
				$total = number_format($total, '3', '.', ' ');
				
				$manager = true;
			}
			
			echo json_encode(array("success"=>true,
								   "msg"=>"Success",
								   "days"=>$days,
								   "rate"=>$rate,
								   "amount"=>$amount,
								   "currency_abbr"=>(($type=='gold')?'Gold':$currency_abbr),
								   "profile_id"=>$profile_id,
								   "user_name"=>$user_name,
								   "user_image"=>$user_image,
								   "total"=>$total . ' (' . (($type=='gold')?'Gold':$currency_abbr) . ')',
								   "manager"=>$manager,
								   "credit_id"=>$credit_id,
								   "fee"=>$fee,
								   "must_return"=>$amount + $fee
								  ));
		}	
	}
	else if($action == "invest") {
		//type
		if($type != 'gold' && $type != 'currency') {
			exit(json_encode(array("success"=>false,
								   "error"=>"Type error"
								   )));
		}
		//test amount
		if(!is_numeric($amount) || $amount < 0.1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 0.1"
								   )));
		}
		
		if(!$is_governor) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You must be a country governor"
								   )));
		}
		
		//get currency abbr
		$query = "SELECT currency_abbr, currency_id FROM currency WHERE currency_id = 
				 (SELECT currency_id FROM country WHERE country_id = '$country_id')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($currency_abbr, $currency_id) = $row;
		
		//get available currency/gold
		if($type == 'gold') {
			$query = "SELECT amount FROM ministry_product WHERE country_id = '$country_id' AND position_id = '$position_id' AND
					  product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Not enough gold in the ministry warehouse"
									  )));
			}
			$row = $result->fetch_row();
			list($availabe_amount) = $row;
		}
		else {
			$query = "SELECT amount FROM ministry_budget WHERE country_id = '$country_id' AND position_id = '$position_id'
					  AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Not enough currency in the ministry budget"
									  )));
			}
			$row = $result->fetch_row();
			list($availabe_amount) = $row;
		}
	
		if($amount > $availabe_amount) {
			if($type == 'gold') {
				exit(json_encode(array("success"=>false,
									   "error"=>"You can only invest $availabe_amount Gold"
									  )));
			}
			else {
				exit(json_encode(array("success"=>false,
									   "error"=>"You can only invest $availabe_amount $currency_abbr"
									  )));
			}
		}
		
		if($type == 'gold') {
			//check if enough capacity
			$query = "SELECT SUM(amount) FROM ministry_product WHERE country_id = '$country_id' AND position_id = '$position_id'";
			$result = $conn->query($query);
			list($warehouse_fill) = $row;
			if($MAX_UNLIMITED_WAREHOUSE_CAPACITY < ($warehouse_fill + $amount)) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Not enough capacity in the ministry warehouse."
									  )));
			}
			
			//get products
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
					  WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) 
					  - '$amount' WHERE country_id = '$country_id' AND product_id = '$product_id' 
					  AND position_id = '$position_id'";
			$conn->query($query);
			
			//update bank warehouse
			$query = "SELECT * FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO bank_product VALUES ('$country_id', '$product_id', '$amount')";
			}
			else {
				$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
						  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) + '$amount' 
						  WHERE country_id = '$country_id' AND product_id = '$product_id'";
			}
			$conn->query($query);
			
			//get total gold
			$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total) = $row;
			$total = number_format($total, '3', '.', ' ');
			
			echo json_encode(array("success"=>true,
								   "msg"=>"You have successfully invested $amount Gold",
								   "total"=>"$total (Gold)"
								  ));
		}
		else if ($type == 'currency'){
			//get currency
			$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
					  WHERE position_id = '$position_id' AND country_id = '$country_id' 
					  AND currency_id = '$currency_id') AS temp) - '$amount' 
					  WHERE position_id = '$position_id' AND country_id = '$country_id' AND currency_id = '$currency_id'";
			$conn->query($query);
			
			//update bank budget
			$query = "SELECT * FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO bank_currency VALUES ('$country_id', '$currency_id', '$amount')";
			}
			else {
				$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) + '$amount' 
						  WHERE country_id = '$country_id' 
						  AND currency_id = '$currency_id'";
			}
			$conn->query($query);
			
			//get total currency
			$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total) = $row;
			$total = number_format($total, '3', '.', ' ');
			
			echo json_encode(array("success"=>true,
								   "msg"=>"You have successfully invested $amount $currency_abbr",
								   "total"=>"$total ($currency_abbr)"
								  ));
		}
	}
	else if($action == 'make_deposit') {
		//get user's country
		$query = "SELECT citizenship, currency_id FROM user_profile up, country c 
				  WHERE user_id = '$user_id' AND c.country_id = citizenship";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($country_id, $currency_id) = $row;

		//get credit/deposit details
		$query = "SELECT currency_deposit_rate, currency_deposit_limit, gold_deposit_rate, gold_deposit_limit, deposit_max_days,
				  currency_abbr, user_id
				  FROM bank_details cdr, currency 
				  WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($currency_deposit_rate, $currency_deposit_limit, $gold_deposit_rate, $gold_deposit_limit, 
			 $deposit_max_days, $currency_abbr, $manager_id) = $row;
	
		//type
		if($type != 'gold' && $type != 'currency') {
			exit(json_encode(array("success"=>false,
								   "error"=>"Type error"
								   )));
		}
		
		//test amount
		if(!is_numeric($amount) || $amount < 0.1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Amount must be more than or equal to 0.1"
								   )));
		}
		if($type == 'gold' && $amount > $gold_deposit_limit) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum amount for Gold deposit is $gold_deposit_limit"
								   )));
		}
		if($type == 'currency' && $amount > $currency_deposit_limit) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum amount for Currency deposit is $currency_deposit_limit"
								   )));
		}
		
		//test days
		if(!is_numeric($days) || $days < 3) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Days must be more than or equal to 3"
								   )));
		}
		if($days > $deposit_max_days) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum days for deposit is $deposit_max_days"
								   )));
		}
		
		//check total user's deposits
		$query = "SELECT IFNULL(SUM(CASE WHEN type = 'currency' THEN amount ELSE 0 END), 0) AS total_currency_deposit,
				  IFNULL(SUM(CASE WHEN type = 'gold' THEN amount ELSE 0 END), 0) AS total_gold_deposit
				  FROM user_deposit WHERE user_id = '$user_id' AND active = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_currency_deposit, $total_gold_deposit) = $row;
		
		if($type == 'currency' && ($total_currency_deposit + $amount > $currency_deposit_limit)) {
			$left = $currency_deposit_limit - $total_currency_deposit;
			$left = $left<0?0:$left;
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum deposit limit for Currency is $currency_deposit_limit. You can make deposit for" .
											" only $left $currency_abbr"
								   )));
		}
		if($type == 'gold' && ($total_gold_deposit + $amount > $gold_deposit_limit)) {
			$left = $gold_deposit_limit - $total_gold_deposit;
			$left = $left<0?0:$left;
			exit(json_encode(array("success"=>false,
								   "error"=>"Maximum deposit limit for Gold is $gold_deposit_limit You can make deposit for" .
											" only $left Gold"
								   )));
		}
		
		//check if enough currency/gold
		if($type == 'gold') {
			$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id' AND amount >= '$amount'";
		}
		else {
			$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id' AND amount >= '$amount'";
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			if($type == 'gold') {
				$error = "You don't have enough Gold";
			}
			else if ($type == 'currency') {
				$error = "You don't have enough Currency";
			}
			exit(json_encode(array("success"=>false,
								   "error"=>$error
								   )));
		}
		
		//make deposit
		$deposit_id = getTimeForId() . $user_id;
		
		if($type == 'gold') {
			$rate = $gold_deposit_rate;
		}
		else {
			$rate = $currency_deposit_rate;
		}
		
		$query = "INSERT INTO user_deposit VALUES ('$deposit_id', '$country_id', '$currency_id', '$user_id', '$amount', '$days',
				 '$rate', 0, '$type', CURRENT_DATE, CURRENT_TIME, '$days', TRUE, 0)";
		if($conn->query($query)) {
			//update earned
			$earned = round($amount * ($rate/100), 3);
			$query = "UPDATE user_deposit SET earned = '$earned' WHERE deposit_id = '$deposit_id'";
			$conn->query($query);
		
			//update user's currency/gold
			if($type == 'currency') {
				$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) - '$amount' WHERE user_id = '$user_id' 
						  AND currency_id = '$currency_id'";
			}
			else if ($type == 'gold') {
				$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
						  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$amount' 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			if(!$conn->query($query)) {
				exit(json_encode(array("success"=>false,
								   "error"=>"Error"
								   )));
			}
			
			//update Banks currency/Gold
			if($type == 'currency') {
				$query = "SELECT * FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$query = "INSERT INTO bank_currency VALUES ('$country_id', '$currency_id', '$amount')";
				}
				else {
					$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
							  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) + '$amount' 
							  WHERE country_id = '$country_id' 
							  AND currency_id = '$currency_id'";
				}
			}
			else if ($type == 'gold') {
				$query = "SELECT * FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$query = "INSERT INTO bank_product VALUES ('$country_id', '$product_id', '$amount')";
				}
				else {
					$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
							  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) + '$amount' 
							  WHERE country_id = '$country_id' AND product_id = '$product_id'";
				}
			}
			$conn->query($query);
			
			//get user image and name
			$query = "SELECT user_name, user_image FROM user_profile up, users u WHERE u.user_id = up.user_id
					  AND up.user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($user_name, $user_image) = $row;
			
			//if manager then display total currency/gold
			$manager = false;
			if($manager_id == $user_id || $is_governor) {
				if($type == 'gold') {
					$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = 1";
				}
				else {
					$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
				}
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($total) = $row;
				$total = number_format($total, '3', '.', ' ');
				
				$manager = true;
			}
			
			echo json_encode(array("success"=>true,
								   "msg"=>"Success",
								   "days"=>$days,
								   "rate"=>$rate,
								   "amount"=>$amount,
								   "currency_abbr"=>(($type=='gold')?'Gold':$currency_abbr),
								   "profile_id"=>$profile_id,
								   "user_name"=>$user_name,
								   "user_image"=>$user_image,
								   "total"=>$total . ' (' . (($type=='gold')?'Gold':$currency_abbr) . ')',
								   "manager"=>$manager,
								   "earned"=>$earned
								  ));
		}
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							  )));
	}
?>