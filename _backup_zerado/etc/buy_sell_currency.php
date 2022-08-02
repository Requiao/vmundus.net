<?php
	//Description: Return available currency for sale depending on the user's choice.
	//			   User can choose action currency to sell or/and buy
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php'); //function getTimeForId().
	
	$action = htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	$currency_id = htmlentities(stripslashes(trim($_POST['currency_id'])), ENT_QUOTES);
	$amount = htmlentities(stripslashes(trim($_POST['amount'])), ENT_QUOTES);
	$rate = htmlentities(stripslashes(trim($_POST['rate'])), ENT_QUOTES);
	$offer_id = htmlentities(stripslashes(trim($_POST['offer_id'])), ENT_QUOTES);
	$for_who = htmlentities(stripslashes(trim($_POST['for_who'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	//determine if governor
	$is_governor = false;
	$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$row = $result->fetch_row();
		list($position_id, $for_country_id) = $row;
		$is_governor = true;
	}

	//show all available offers of currency user wants to buy
	if($action == 'buying_currency') {
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists."
								  )));
		}
		
		$query = "SELECT u.user_id, user_name, user_image, cu.currency_abbr,
				  rate, amount, offer_id, 'user', action
				  FROM monetary_market mm, users u, currency cu, user_profile up
				  WHERE u.user_id = mm.user_id AND up.user_id = mm.user_id
				  AND mm.currency_id = '$currency_id' AND cu.currency_id = mm.currency_id
				  AND action = 'sell' 
				  UNION
				  SELECT u.user_id, user_name, user_image, currency_abbr,
				  rate, amount, offer_id, name, action
				  FROM monetary_market mm, currency cu, users u, user_profile up, 
				  country_government cg, government_positions gp
				  WHERE cu.currency_id = mm.currency_id AND cg.user_id = up.user_id 
				  AND cg.position_id = seller_position_id AND cg.country_id = seller_country_id
				  AND u.user_id = up.user_id AND mm.currency_id = '$currency_id' 
				  AND gp.position_id = seller_position_id AND action = 'sell'
				  ORDER BY rate ASC LIMIT 100";
		$result = $conn->query($query);
		$offers_array = generateOffers($result);
		
		echo json_encode(array("success"=>true,
							   "is_governor"=>$is_governor,
							   "offers"=>$offers_array
							   ));
	}
	else if ($action == 'buying_gold') {
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exists."
								  )));
		}
		
		$query = "SELECT u.user_id, user_name, user_image, cu.currency_abbr,
				  rate, amount, offer_id, 'user', action
				  FROM monetary_market mm, users u, currency cu, user_profile up
				  WHERE u.user_id = mm.user_id AND up.user_id = mm.user_id
				  AND mm.currency_id = '$currency_id' AND cu.currency_id = mm.currency_id
				  AND action = 'buy' 
				  UNION
				  SELECT u.user_id, user_name, user_image, currency_abbr,
				  rate, amount, offer_id, name, action
				  FROM monetary_market mm, currency cu, users u, user_profile up, 
				  country_government cg, government_positions gp
				  WHERE cu.currency_id = mm.currency_id AND cg.user_id = up.user_id 
				  AND cg.position_id = seller_position_id AND cg.country_id = seller_country_id
				  AND u.user_id = up.user_id AND mm.currency_id = '$currency_id' 
				  AND gp.position_id = seller_position_id AND action = 'buy'
				  ORDER BY rate ASC LIMIT 100";
		$result = $conn->query($query);
		$offers_array = generateOffers($result);

		echo json_encode(array("success"=>true,
							   "is_governor"=>$is_governor,
							   "offers"=>$offers_array
							   ));
	}
	else if($action == 'remove_offer' || $action == 'remove_ministry_offer') {//remove_offer
		if(!is_numeric($offer_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Offer doesn't exists."
								  )));
		}
		
		//check if belongs to the user
		if($action == 'remove_offer') {
			$query = "SELECT currency_id, amount, action FROM monetary_market WHERE offer_id = '$offer_id' AND user_id = '$user_id'";
		}
		else if($action == 'remove_ministry_offer') {
			if(!$is_governor) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You don't have permission to perform this action."
									  )));
			}
			$query = "SELECT currency_id, amount, action FROM monetary_market WHERE offer_id = '$offer_id' 
					  AND seller_position_id = '$position_id' AND seller_country_id = '$for_country_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Offer doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($currency_id, $amount, $sell_or_buy) = $row;
		
		//remove offer
		if($action == 'remove_offer') {
			if($sell_or_buy == 'sell') {
				$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT amount FROM user_currency 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS old_amount) + '$amount' 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT amount FROM (SELECT amount FROM user_product
						  WHERE user_id = '$user_id' AND product_id = '1') AS old_gold) 
						  + '$amount' WHERE user_id = '$user_id' AND product_id = '1'";
			}
		}
		else if($action == 'remove_ministry_offer') {
			if($sell_or_buy == 'sell') {
				$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
						  WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') 
						  AS temp) + '$amount' WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' 
						  AND position_id = '$position_id'";
			}
			else {
				$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
						  WHERE country_id = '$for_country_id' AND product_id = '1' AND position_id = '$position_id') 
						  AS temp) + '$amount' WHERE country_id = '$for_country_id' AND product_id = '1' 
						  AND position_id = '$position_id'";
			}
		}
		if($conn->query($query)) {
			$query = "DELETE FROM monetary_market WHERE offer_id = '$offer_id'";
			$conn->query($query);
			
			echo json_encode(array("success"=>true
								  ));
		}
	}
	else if ($action == 'offer_currency' || $action == 'offer_ministry_currency') {
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exist."
								  )));
		}
		$query = "SELECT * FROM currency WHERE currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exist."
								  )));
		}
		
		if(!is_numeric($amount)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid amount range is 1...10 000."
								  )));
		}
		$amount = round($amount);
		if($amount > 10000 || $amount < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid amount range is 1...10 000."
								  )));
		}
		
		if(!is_numeric($rate)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid rate range is 0.001...1."
								  )));
		}
		$rate = round($rate, 3);
		if($rate > 1 || $rate < 0.001) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid rate range is 0.001...1."
								  )));
		}
		
		//check if same type of currency offered more than 3 times
		if ($action == 'offer_currency') {//user's personal
			$query = "SELECT COUNT(*), currency_abbr
					  FROM monetary_market mm, currency c WHERE mm.currency_id = '$currency_id' 
					  AND c.currency_id = mm.currency_id AND user_id = '$user_id' AND action = 'sell'";
		}
		else if ($action == 'offer_ministry_currency') {
			if(!$is_governor) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You don't have permission to perform this action."
									  )));
			}
			$query = "SELECT COUNT(*), currency_abbr
					  FROM monetary_market mm, currency c WHERE mm.currency_id = '$currency_id'
					  AND c.currency_id = mm.currency_id
					  AND seller_position_id = '$position_id' 
					  AND seller_country_id = '$for_country_id' AND action = 'sell'";
		}
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($quantity, $currency_abbr) = $row;
		if($quantity >= 3) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You can only have 3 duplicate offers."
								  )));
		}
		
		//check if user have enough money
		if ($action == 'offer_currency') {//user's personal
			$query = "SELECT amount FROM user_currency WHERE currency_id = '$currency_id' AND user_id = '$user_id'";
		}
		else if ($action == 'offer_ministry_currency') {
			$query = "SELECT amount FROM ministry_budget WHERE currency_id = '$currency_id' 
					  AND position_id = '$position_id' AND country_id = '$for_country_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($available_money) = $row;
		}
		else {
			$available_money = 0;
		}
		
		if($available_money < $amount) {
			$amount = number_format($amount, 2, '.', ' ');
			$available_money = number_format($available_money, 2, '.', ' ');
			
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have enough money. You're trying to offer $amount $currency_abbr
											 but you only have $available_money $currency_abbr."
								  )));
		}
		
		$offer_id = getTimeForId() . $user_id;
		//offer
		if ($action == 'offer_currency') {//user's personal
			$query = "INSERT INTO monetary_market (offer_id, user_id, currency_id, rate, amount, action)
					  VALUES('$offer_id', '$user_id', '$currency_id', '$rate', '$amount', 'sell')";
		}
		else if ($action == 'offer_ministry_currency') {
			$query = "INSERT INTO monetary_market (offer_id, currency_id, rate, amount, seller_position_id,
					  seller_country_id, action)
					  VALUES('$offer_id', '$currency_id', '$rate', '$amount', '$position_id', '$for_country_id', 'sell')";
		}
		if($conn->query($query)) {
			if ($action == 'offer_currency') {//user's personal
				$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT amount FROM user_currency
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS old_currency) 
						  - '$amount' WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			}
			else if ($action == 'offer_ministry_currency') {
				$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
						  WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') 
						  AS temp) - '$amount' WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' 
						  AND position_id = '$position_id'";
			}
			$conn->query($query);
			
			if ($action == 'offer_currency') {//user's personal
				$query = "SELECT mm.user_id, user_name, user_image, '$currency_abbr', rate, amount, offer_id, 'user', action
						  FROM monetary_market mm, users u, currency cu, user_profile up
						  WHERE mm.user_id = u.user_id
						  AND cu.currency_id = mm.currency_id
						  AND up.user_id = u.user_id AND offer_id = '$offer_id'";
			}
			else if ($action == 'offer_ministry_currency') {
				$query = "SELECT u.user_id, user_name, user_image, currency_abbr, rate, amount, offer_id, name, action
						  FROM monetary_market mm, currency cu, users u, user_profile up, 
						  country_government cg, government_positions gp
						  WHERE cu.currency_id = mm.currency_id AND cg.user_id = up.user_id 
						  AND cg.position_id = seller_position_id AND cg.country_id = seller_country_id
						  AND u.user_id = up.user_id
						  AND gp.position_id = seller_position_id AND offer_id = '$offer_id'";
			}
			$result = $conn->query($query);
			$offers_array = generateOffers($result);
			
			echo json_encode(array("success"=>true,
								   "is_governor"=>$is_governor,
								   "msg"=>"Offer successfully created.",
								   "offers"=>$offers_array
								  ));
		}
	}
	else if ($action == 'offer_gold' || $action == 'offer_ministry_gold') {
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exist."
								  )));
		}
		$query = "SELECT * FROM currency WHERE currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Currency doesn't exist."
								  )));
		}
		
		if(!is_numeric($amount)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid amount range is 0.01...100."
								  )));
		}
		$amount = round($amount, 2);
		if($amount > 100 || $amount < 0.01) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid amount range is 0.01...100."
								  )));
		}
		
		if(!is_numeric($rate)) {
			exit(json_encode(array("success"=>false,
								    "error"=>"Valid rate range is 1...1 000."
								  )));
		}
		$rate = round($rate);
		if($rate > 1000 || $rate < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Valid rate range is 1...1 000."
								  )));
		}
		
		//check if same type of currency offered more than 3 times
		if ($action == 'offer_gold') {//user's personal
			$query = "SELECT COUNT(*), currency_abbr
					  FROM monetary_market mm, currency c WHERE mm.currency_id = '$currency_id' 
					  AND c.currency_id = mm.currency_id AND user_id = '$user_id' AND action = 'buy'";
		}
		else if ($action == 'offer_ministry_gold') {
			if(!$is_governor) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You don't have permission to perform this action."
									  )));
			}
			$query = "SELECT COUNT(*), currency_abbr
					  FROM monetary_market mm, currency c WHERE mm.currency_id = '$currency_id' 
					  AND seller_position_id = '$position_id' AND c.currency_id = mm.currency_id
					  AND seller_country_id = '$for_country_id' AND action = 'buy'";
		}
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($quantity, $currency_abbr) = $row;
		if($quantity >= 3) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You can only have 3 duplicate offers."
								  )));
		}
		
		//check if user have enough money
		if ($action == 'offer_gold') {//user's personal
			$query = "SELECT amount FROM user_product WHERE product_id = '1' AND user_id = '$user_id'";
		}
		else if ($action == 'offer_ministry_gold') {
			$query = "SELECT amount FROM ministry_product WHERE product_id = '1' 
					  AND position_id = '$position_id' AND country_id = '$for_country_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($available_gold) = $row;
		}
		else {
			$available_gold = 0;
		}
		
		if($available_gold < $amount) {
			$amount = number_format($amount, 2, '.', ' ');
			$available_gold = number_format($available_gold, 2, '.', ' ');
			
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have enough gold. You're trying to offer $amount Gold
											 but you only have $available_gold."
								  )));
		}
		
		$offer_id = getTimeForId() . $user_id;
		//offer
		if ($action == 'offer_gold') {//user's personal
			$query = "INSERT INTO monetary_market (offer_id, user_id, currency_id, rate, amount, action)
					  VALUES('$offer_id', '$user_id', '$currency_id', '$rate', '$amount', 'buy')";
		}
		else if ($action == 'offer_ministry_gold') {
			$query = "INSERT INTO monetary_market (offer_id, currency_id, rate, amount, seller_position_id,
					  seller_country_id, action)
					  VALUES('$offer_id', '$currency_id', '$rate',  '$amount', '$position_id', 
					  '$for_country_id', 'buy')";
		}
		if($conn->query($query)) {
			if ($action == 'offer_gold') {//user's personal
				$query = "UPDATE user_product SET amount = (SELECT amount FROM (SELECT amount FROM user_product
						  WHERE user_id = '$user_id' AND product_id = '1') AS old_gold) 
						  - '$amount' WHERE user_id = '$user_id' AND product_id = '1'";
			}
			else if ($action == 'offer_ministry_gold') {
				$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
						  WHERE country_id = '$for_country_id' AND product_id = '1' AND position_id = '$position_id') 
						  AS temp) - '$amount' WHERE country_id = '$for_country_id' AND product_id = '1' 
						  AND position_id = '$position_id'";
			}
			$conn->query($query);
			
			if ($action == 'offer_gold') {//user's personal
				$query = "SELECT mm.user_id, user_name, user_image, '$currency_abbr', rate, amount, offer_id, 'user', action
						  FROM monetary_market mm, users u, currency cu, user_profile up
						  WHERE mm.user_id = u.user_id
						  AND cu.currency_id = mm.currency_id
						  AND up.user_id = u.user_id AND offer_id = '$offer_id'";
			}
			else if ($action == 'offer_ministry_gold') {
				$query = "SELECT u.user_id, user_name, user_image, currency_abbr, rate, amount, offer_id, name, action
						  FROM monetary_market mm, currency cu, users u, user_profile up, 
						  country_government cg, government_positions gp
						  WHERE cu.currency_id = mm.currency_id AND cg.user_id = up.user_id 
						  AND cg.position_id = seller_position_id AND cg.country_id = seller_country_id
						  AND u.user_id = up.user_id
						  AND gp.position_id = seller_position_id AND offer_id = '$offer_id'";
			}
			$result = $conn->query($query);
			$offers_array = generateOffers($result);
			
			echo json_encode(array("success"=>true,
								   "is_governor"=>$is_governor,
								   "msg"=>"Offer successfully created.",
								   "offers"=>$offers_array
								  ));
		}
	}
	else if($action == 'buy_currency') {
		if(!is_numeric($offer_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Offer doesn't exists."
								  )));
		}
		if($for_who != 'user' && $for_who != 'country') {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid request."
								  )));
		}
		
		$query = "SELECT user_id, amount, rate, mm.currency_id, cu.currency_abbr, 
				  seller_position_id, seller_country_id, action
				  FROM monetary_market mm, currency cu
				  WHERE mm.currency_id = cu.currency_id AND offer_id = '$offer_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Offer doesn't exist anymore."
								  )));
		}
		$row = $result->fetch_row();
		list($seller_id, $offered_amount, $rate, $currency_id, $currency_abbr,
			 $seller_position_id, $seller_country_id, $action) = $row;
	
		if($action == 'sell') {
			$amount = round($amount);
			if($amount < 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Buying amount must be more than 1."
									  )));
			}
		}
		else {
			$amount = round($amount, 2);
			if($amount < 0.001) {
				exit(json_encode(array("success"=>false,
									   "error"=>"Buying amount must be more than 0.001."
									  )));
			}
		}
	
		if(!empty($seller_id)) {
			$seller = 'user';
		}
		else if(!empty($seller_position_id) && !empty($seller_country_id)) {
			$seller = 'ministry';
			
			$query = "SELECT user_id FROM country_government WHERE position_id = '$seller_position_id' 
					  AND country_id = '$seller_country_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($seller_id) = $row;
		}
		if($amount > $offered_amount) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Available amount is $offered_amount."
								  )));
		}

		//check if buyer has enough currency/gold for exchange
		//is enough money?
		if($for_who == 'user') {
			if($action == 'sell') {
				$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '1'";
			}
			else {
				$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			}
		}
		else if($for_who == 'country') {
			if($action == 'sell') {
				$query = "SELECT amount FROM ministry_product WHERE country_id = '$for_country_id' AND product_id = '1'
						  AND position_id = '$position_id'";
			}
			else {
				$query = "SELECT amount FROM ministry_budget WHERE country_id = '$for_country_id' AND currency_id = '$currency_id'
						  AND position_id = '$position_id'";

			}
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$action=="sell"?"You don't have enough Gold.":"You don't have enough money."
								  )));
		}
		$row = $result->fetch_row();
		list($user_money_gold) = $row;
	
		$total = $amount * $rate;
		if($user_money_gold < $total) {
			exit(json_encode(array("success"=>false,
								   "error"=>$action=="sell"?"You don't have enough Gold.":"You don't have enough money."
								  )));
		}
	
		//if buyer will buy all currency, then delete offer from market.
		if($amount == $offered_amount) {
			$query = "DELETE FROM monetary_market WHERE offer_id = '$offer_id'";
		}
		else {
			$query = "UPDATE monetary_market SET amount = (SELECT amount FROM 
					 (SELECT amount FROM monetary_market WHERE offer_id = '$offer_id') AS old_amount) 
					 - '$amount' WHERE offer_id = '$offer_id'";
		}
		if(!$conn->query($query)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Something went wrong. Please try again."
								  )));
		}
		
		//check if user has this currency type
		if($for_who == 'user') {
			if($action == 'sell') {
				$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			}
			else {
				$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '1'";
			}
		}
		else if($for_who == 'country') {
			if($action == 'sell') {
				$query = "SELECT amount FROM ministry_budget WHERE country_id = '$for_country_id' AND currency_id = '$currency_id'
						  AND position_id = '$position_id'";
			}
			else {
				$query = "SELECT amount FROM ministry_product WHERE country_id = '$for_country_id' AND product_id = '1'
						  AND position_id = '$position_id'";
			}
		}
		$result = $conn->query($query);
		//add money/gold to buyer
		if($result->num_rows == 1) {
			if($for_who == 'user') {
				if($action == 'sell') {
					$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT amount FROM user_currency WHERE user_id = '$user_id'
							  AND currency_id = '$currency_id') AS old_amount) + '$amount' WHERE user_id = '$user_id'
							  AND currency_id = '$currency_id'";
				}
				else {
					$query = "UPDATE user_product SET amount = (SELECT amount FROM (SELECT amount FROM user_product WHERE user_id = '$user_id'
							  AND product_id = '1') AS old_amount) + '$amount' WHERE user_id = '$user_id'
							  AND product_id = '1'";
				}
			}
			else if($for_who == 'country') {
				if($action == 'sell') {
					$query = "UPDATE ministry_budget SET amount = (SELECT amount FROM (SELECT amount FROM ministry_budget 
							  WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') 
							  AS temp) + '$amount' WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' 
							  AND position_id = '$position_id'";
				}
				else {
					$query = "UPDATE ministry_product SET amount = (SELECT amount FROM (SELECT amount FROM ministry_product 
							  WHERE country_id = '$for_country_id' AND product_id = '1' AND position_id = '$position_id') 
							  AS temp) + '$amount' WHERE country_id = '$for_country_id' AND product_id = '1' 
							  AND position_id = '$position_id'";
				}
			}
		}
		else {
			if($for_who == 'user') {
				if($action == 'sell') {
					$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', '$amount')";
				}
				else {
					$query = "INSERT INTO user_product VALUES('$user_id', '1', '$amount')";
				}
			}
			else if($for_who == 'country') {
				if($action == 'sell') {
					$query = "INSERT INTO ministry_budget VALUES('$for_country_id', '$position_id', '$currency_id', '$amount')";
				}
				else {
					$query = "INSERT INTO ministry_product VALUES('$for_country_id', '$position_id', '1', '$amount')";
				}
			}
		}
		$conn->query($query);
		
		//get money/gold from buyer
		if($for_who == 'user') {
			if($action == 'sell') {
				$query = "UPDATE user_product SET amount = (SELECT amount FROM (SELECT amount FROM user_product WHERE user_id = '$user_id'
						  AND product_id = '1') AS old_amount) - '$total' WHERE user_id = '$user_id'
						  AND product_id = '1'";
			}
			else {
				$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT amount FROM user_currency WHERE user_id = '$user_id'
						  AND currency_id = '$currency_id') AS old_amount) - '$total' WHERE user_id = '$user_id'
						  AND currency_id = '$currency_id'";
			}
		}
		else if($for_who == 'country') {
			if($action == 'sell') {
				$query = "UPDATE ministry_product SET amount = (SELECT amount FROM (SELECT amount FROM ministry_product 
						  WHERE country_id = '$for_country_id' AND product_id = '1' AND position_id = '$position_id') 
						  AS temp) - '$total' WHERE country_id = '$for_country_id' AND product_id = '1' 
						  AND position_id = '$position_id'";
			}
			else {
				$query = "UPDATE ministry_budget SET amount = (SELECT amount FROM (SELECT amount FROM ministry_budget 
						  WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') 
						  AS temp) - '$total' WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' 
						  AND position_id = '$position_id'";
			}
		}
		$result = $conn->query($query);
	
		//add money to seller
		if($seller == 'user') {
			if($action == 'sell') {
				$query = "SELECT amount FROM user_product WHERE user_id = '$seller_id' AND product_id = '1'";
			}
			else {
				$query = "SELECT amount FROM user_currency WHERE user_id = '$seller_id' AND currency_id = '$currency_id'";
			}
		}
		else if($seller == 'ministry') {
			if($action == 'sell') {
				$query = "SELECT * FROM ministry_product WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id'
						  AND product_id = '1'";
			}
			else {
				$query = "SELECT * FROM ministry_budget WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id'
						  AND currency_id = '$currency_id'";
			}
		}
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			if($seller == 'user') {
				if($action == 'sell') {
					$query = "UPDATE user_product SET amount = (SELECT amount FROM (SELECT amount FROM user_product 
							  WHERE user_id = '$seller_id'
							  AND product_id = '1') AS old_amount) + '$total' WHERE user_id = '$seller_id'
							  AND product_id = '1'";
				}
				else {
					$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT amount FROM user_currency 
							  WHERE user_id = '$seller_id'
							  AND currency_id = '$currency_id') AS old_amount) + '$total' WHERE user_id = '$seller_id'
							  AND currency_id = '$currency_id'";
				}
			}
			else if($seller == 'ministry') {
				if($action == 'sell') {
					$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
							  WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id' 
							  AND product_id = '1') AS temp) + '$total' 
							  WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id' 
							  AND product_id = '1'";
				}
				else {
					$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
							  WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id' 
							  AND currency_id = '$currency_id') AS temp) + '$total' 
							  WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id' 
							  AND currency_id = '$currency_id'";
				}
			}
		}
		else {
			if($seller == 'user') {
				if($action == 'sell') {
					$query = "INSERT INTO user_product VALUES('$seller_id', '1', '$total')";
				}
				else {
					$query = "INSERT INTO user_currency VALUES('$seller_id', '$currency_id', '$total')";
				}
			}
			else if($seller == 'ministry') {
				if($action == 'sell') {
					$query = "INSERT INTO ministry_product VALUES('$seller_country_id', '$seller_position_id', '1', '$total')";
				}
				else {
					$query = "INSERT INTO ministry_budget VALUES('$seller_country_id', '$seller_position_id', '$currency_id', '$total')";
				}
			}
		}
		$conn->query($query);
		
		//record purchase
		$seller_position_id = $seller_position_id?$seller_position_id:'NULL';
		$seller_country_id = $seller_country_id?$seller_country_id:'NULL';
		if($for_who == 'user') {
			$query = "INSERT INTO currency_purchase_history (seller_user_id, buyer_user, currency_id, amount,
					  rate, date, time, seller_position_id, seller_country_id, action)
					  VALUES('$seller_id', '$user_id', '$currency_id', '$amount', '$rate', 
					  CURRENT_DATE, CURRENT_TIME, $seller_position_id, $seller_country_id, '$action')";
		}
		else if($for_who == 'country') {
			$query = "INSERT INTO currency_purchase_history (seller_user_id, buyer_user, buyer_country_id, buyer_position_id, 
					  currency_id, amount, rate, date, time, seller_position_id, 
					  seller_country_id, action)
					  VALUES('$seller_id', '$user_id', '$for_country_id', '$position_id', '$currency_id', 
					 '$amount', '$rate', CURRENT_DATE, CURRENT_TIME, $seller_position_id, 
					  $seller_country_id, '$action')";
		}
		$conn->query($query);
		
		//summary
		$amount_left = number_format($offered_amount - $amount, 2, '.', ' ');
		$total = number_format($total, 2, '.', ' ');
		$amount = number_format($amount, 2, '.', ' ');
		
		if($action == 'sell') {
			$msg = "You have successfully bought $amount $currency_abbr for $total Gold.";
		}
		else {
			$msg = "You have successfully bought $amount Gold for $total $currency_abbr.";
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>$msg,
							   "currency_abbr"=>$currency_abbr,
							   "new_amount"=>$amount_left,
							   "buy_sell"=>$action
							   ));
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							  )));
	}
	
	
	function generateOffers($result) {
		if($result->num_rows <= 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"No offers available."
								  )));
		}

		$offers_array = array();
		while($row = $result->fetch_row()) {
			list($seller_id, $seller_name, $seller_img, $currency_abbr, $rate, $amount, $offer_id, $seller, $action) = $row;
			
			array_push($offers_array, array("seller_id"=>$seller_id, "seller_name"=>$seller_name, "seller_img"=>$seller_img, 
					   "currency_abbr"=>$currency_abbr, "rate"=>number_format($rate, ($action=='sell'?3:0), '.', ' '),
					   "amount"=>$amount, "offer_id"=>$offer_id, "seller"=>$seller));
		}
		return $offers_array;
	}
?>