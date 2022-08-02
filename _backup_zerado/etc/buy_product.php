<?php
	//Description: Buy product on market.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/record_statistics.php');
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$offer_id =  htmlentities(stripslashes(strip_tags(trim($_POST['offer_id']))), ENT_QUOTES);
	$selected_quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$for_who =  htmlentities(stripslashes(strip_tags(trim($_POST['for_who']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$MAX_UNLIMITED_WAREHOUSE_CAPACITY = 900000000;
	
	if(!is_numeric($offer_id)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Offer doesn't exists."
							  )));
	}
	if(!is_numeric($selected_quantity)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Quantity must be more than 0 and be a whole number."
							  )));
	}
	$selected_quantity = floor($selected_quantity);
	if($selected_quantity < 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Quantity must be more than 0."
							  )));
	}
	
	//check if offer exists and its details
	$query = "SELECT user_id, company_id, position_id, for_country_id, quantity, price, 
		 	  product_id, pm.country_id, c.currency_id, country_name
			  FROM product_market pm, country c
			  WHERE offer_id = '$offer_id' AND pm.country_id = c.country_id";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Looks like this offer doesn't exist anymore."
							  )));
	}
	$row = $result->fetch_row();
	list($seller_id, $company_id, $seller_position_id, $seller_country_id, $quantity, $price, 
		 $product_id, $country_id, $currency_id, $location_country_name) = $row;

	//determine if enough offered products
	if($selected_quantity > $quantity) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Not enough available products."
							  )));
	}
	
	$for_country_id = 'NULL';//later to be stored in the table for history
	$position_id = 'NULL';//later to be stored in the table for history
	if($for_who == 'user') {
		//determine if enough capacity in warehouse
		$query = "SELECT capacity, SUM(amount) FROM user_warehouse uw, user_product up
				  WHERE uw.user_id = '$user_id' AND up.user_id = uw.user_id GROUP BY capacity;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($capacity, $warehouse_fill) = $row;
		if($capacity < ($warehouse_fill + $selected_quantity)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough capacity in the warehouse."
								  )));
		}
		
		$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
	}
	else if($for_who == 'country') {
		//check if president/minister
		$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($position_id, $for_country_id) = $row;
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"You're not a governor and not allowed to perform this action."
								  )));
		}
		
		$query = "SELECT SUM(amount) FROM ministry_product WHERE country_id = '$for_country_id' AND position_id = '$position_id'";
		$result = $conn->query($query);
		list($warehouse_fill) = $row;
		if(!empty($warehouse_fill) && $MAX_UNLIMITED_WAREHOUSE_CAPACITY < ($warehouse_fill + $selected_quantity)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough capacity in the ministry warehouse."
								  )));
		}
		
		$query = "SELECT amount FROM ministry_budget WHERE country_id = '$for_country_id' AND currency_id = '$currency_id'
				  AND position_id = '$position_id'";
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"You are allowed to buy products only for yourself and for your country."
							  )));
	}

	//is enough money?
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Not enough money."
							  )));
	}
	$row = $result->fetch_row();
	list($money) = $row;
	$payment = $selected_quantity * $price;
	if($payment > $money) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Not enough money."
							  )));
	}
	
	//add product to new owner
	//determine if user already has that product type
	if($for_who == 'user') {
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$selected_quantity' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$selected_quantity')";
		}
	}
	else if($for_who == 'country') {
		$query = "SELECT * FROM ministry_product WHERE country_id = '$for_country_id' AND product_id = '$product_id' 
				  AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
					  WHERE country_id = '$for_country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) 
					  + '$selected_quantity' WHERE country_id = '$for_country_id' AND product_id = '$product_id' 
					  AND position_id = '$position_id'";
		}
		else {
			$query = "INSERT INTO ministry_product VALUES('$for_country_id', '$position_id', '$product_id', '$selected_quantity')";
		}
	}
	if(!$conn->query($query)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Error. Please try again."
							  )));
	}
	
	//updates buyer's currency table
	if($for_who == 'user') {
		$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
				  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) - '$payment' WHERE user_id = '$user_id' 
				  AND currency_id = '$currency_id'";
	}
	else if($for_who == 'country') {
		$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
				  WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') 
				  AS temp) - '$payment' WHERE country_id = '$for_country_id' AND currency_id = '$currency_id' 
				  AND position_id = '$position_id'";
	}
	$conn->query($query);
	
	/* get taxes */
	//determine if seller is exporting
	if(!is_null($company_id)) {//selling by company
		$query = "SELECT country_id FROM country WHERE country_id = 
				 (SELECT country_id FROM regions WHERE region_id = 
				 (SELECT location FROM companies WHERE company_id = '$company_id'))";
	}
	else if(!is_null($seller_id)) {//selling by user
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$seller_id'";
	}
	
	if (!is_null($seller_position_id)) {//government selling
		$sellers_country = $for_country_id;
	}
	else {
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($sellers_country) = $row;
	}
	
	//get taxes from the sellers revenue
	if($country_id == $sellers_country) {//normal tax
		$query = "SELECT sale_tax FROM product_sale_tax WHERE country_id = '$country_id' AND product_id = '$product_id'";
	}
	else {//export tax
		$query = "SELECT sale_tax FROM product_import_tax 
				  WHERE country_id = '$country_id' AND product_id = '$product_id' AND from_country_id = '$sellers_country'";
	}
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($taxes) = $row;
	
	$tax = ($payment * $taxes)/100;
	
	//update country_daily_income statistic
	countryDailyIncome($country_id, $currency_id, $tax);

	//update sellers currency table
	$payment = $payment - $tax;
	if (!is_null($seller_position_id)) {//buying from ministry
		$query = "SELECT user_id FROM country_government WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($seller_id) = $row;
	
		$query = "SELECT * FROM ministry_budget WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id'
				  AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
					  WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id' 
					  AND currency_id = '$currency_id') AS temp) + '$payment' 
					  WHERE position_id = '$seller_position_id' AND country_id = '$seller_country_id' AND currency_id = '$currency_id'";
		}
		else {
			$query = "INSERT INTO ministry_budget VALUES('$seller_country_id', '$seller_position_id', '$currency_id', '$payment')";
		}
		$conn->query($query);
	}
	else {
		$seller = 'user';
		if(is_null($seller_id)) {//if selling from company, then get owner id
			$query = "SELECT user_id FROM user_building WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($seller_id) = $row;
		}
		if($seller == 'user') {
			$query = "SELECT * FROM user_currency WHERE user_id = '$seller_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
						  WHERE user_id = '$seller_id' AND currency_id = '$currency_id') AS temp) + '$payment' 
						  WHERE user_id = '$seller_id' AND currency_id = '$currency_id'";
			}
			else {
				$query = "INSERT INTO user_currency VALUES('$seller_id', '$currency_id', '$payment')";
			}
			$conn->query($query);
		}
	}

	//update country_currency table with taxes
	$query = "SELECT * FROM country_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) { 
		$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
				  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) + '$tax' 
				  WHERE country_id = '$country_id'  AND currency_id = '$currency_id'";
	}
	else {
		$query = "INSERT INTO country_currency VALUES('$country_id', '$currency_id', '$tax')";
	}
	$conn->query($query);
	
	//record purchase
	$seller_position_id = $seller_position_id?$seller_position_id:'NULL';
	$seller_country_id = $seller_country_id?$seller_country_id:'NULL';
	$query = "INSERT INTO product_purchase_history VALUES('$user_id', '$seller_id', $for_country_id, $position_id, '$product_id',
			 '$selected_quantity', '$price', '$currency_id', CURRENT_DATE, CURRENT_TIME, $seller_position_id, $seller_country_id)";
	$conn->query($query);

	//delete offer if bought everything
	$new_quantity = $quantity - $selected_quantity;
	if($new_quantity == 0) {
		//delete
		$query = "DELETE FROM product_market WHERE offer_id = '$offer_id'";
		$conn->query($query);
	}
	else {
		//update
		$query = "UPDATE product_market SET quantity = '$new_quantity' WHERE offer_id = '$offer_id'";
		$conn->query($query);
	}	
	
	//display summary
	$query = "SELECT product_name, currency_abbr FROM product_info, country c, currency cu 
			  WHERE product_id = '$product_id' AND country_id = '$country_id' AND c.currency_id = cu.currency_id";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($product_name, $currency_abbr) = $row;
	$price = $payment + $tax;

	//notify seller
	$notification = "$selected_quantity " . 
		(
			$seller_country_id !== 'NULL' 
				? " country " 
				: ""
		) . 
	" $product_name have been sold for $price $currency_abbr. 
	$tax $currency_abbr taxes have been paid to the $location_country_name treasury.";
	sendNotification($notification, $seller_id);

	echo json_encode(array("success"=>true,
						   "msg"=>"You have successfully bought $selected_quantity $product_name for $price $currency_abbr.",
						   "products_left"=>$new_quantity,
						   ));
?>