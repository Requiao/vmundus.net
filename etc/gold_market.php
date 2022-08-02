<?php
	//Description: Buy product on market.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$product_id =  htmlentities(stripslashes(trim($_POST['product_id'])), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(trim($_POST['quantity'])), ENT_QUOTES);
	$for_who =  htmlentities(stripslashes(trim($_POST['for_who'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Product doesn't exist."
							  )));
	}
	$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Product doesn't exist."
							  )));
	}
	
	if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Quantity must be more than 0 and be a whole number."
							  )));
	}
	if ($quantity > 100) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Quantity must be less than or equal to 100."
							  )));
	}
	
	//get product quantity
	$query = "SELECT price, bonus, product_name
			  FROM gold_market_offers gmo, product_info pi
			  WHERE pi.product_id = gmo.product_id AND available = TRUE AND gmo.product_id = '$product_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Offer doesn't exists."
							  )));
	}
	$row = $result->fetch_row();
	list($price, $bonus, $product_name) = $row;
	
	$bonus_quantity = round($quantity * ($bonus/ 100), 2);
	$buying_quantity = $bonus_quantity + $quantity;
	
	$for_country_id = 'NULL';//later to be stored in the table for history
	$position_id = 'NULL';//later to be stored in the table for history
	if($for_who == 'user') {
		//determine if enough capacity in warehouse
		$query = "SELECT capacity, SUM(amount) FROM user_warehouse uw, user_product up
				  WHERE uw.user_id = '$user_id' AND up.user_id = uw.user_id GROUP BY capacity;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($capacity, $warehouse_fill) = $row;
		if($capacity < ($warehouse_fill + $buying_quantity)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough capacity in the warehouse."
								  )));
		}
		
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = 1";
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
		$MAX_MINISTRY_WAREHOUSE_CAPACITY = 900000000;
		$query = "SELECT SUM(amount) FROM ministry_product WHERE country_id = '$for_country_id' AND position_id = '$position_id'";
		$result = $conn->query($query);
		list($warehouse_fill) = $row;
		if(!empty($warehouse_fill) && $MAX_MINISTRY_WAREHOUSE_CAPACITY < ($warehouse_fill + $buying_quantity)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough capacity in the ministry warehouse."
								  )));
		}
		
		$query = "SELECT amount FROM ministry_product WHERE country_id = '$for_country_id' AND product_id = 1
				  AND position_id = '$position_id'";
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"You are allowed to buy products only for yourself and for your country."
							  )));
	}
	
	//is enough gold?
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($gold) = $row;
	$payment = $quantity * $price;
	if($payment > $gold) {
		exit(json_encode(array('success'=>false,
							   'error'=>"You don't have enough gold."
							  )));
	}
	
	//add product to new owner
	//determine if user already has that product type
	if($for_who == 'user') {
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$buying_quantity' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$buying_quantity')";
		}
	}
	else if($for_who == 'country') {
		$query = "SELECT * FROM ministry_product WHERE country_id = '$for_country_id' AND product_id = '$product_id' 
				  AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
					  WHERE country_id = '$for_country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) 
					  + '$buying_quantity' WHERE country_id = '$for_country_id' AND product_id = '$product_id' 
					  AND position_id = '$position_id'";
		}
		else {
			$query = "INSERT INTO ministry_product VALUES('$for_country_id', '$position_id', '$product_id', '$buying_quantity')";
		}
	}
	if(!$conn->query($query)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Error. Please try again."
							  )));
	}
	
	//update buyer's gold
	if($for_who == 'user') {
		$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = 1) AS temp) - '$payment' 
				  WHERE user_id = '$user_id' AND product_id = 1";
	}
	else if($for_who == 'country') {
		$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
				  WHERE country_id = '$for_country_id' AND product_id = 1 AND position_id = '$position_id') AS temp) 
				  - '$payment' WHERE country_id = '$for_country_id' AND product_id = 1 
				  AND position_id = '$position_id'";
	}
	$conn->query($query);
	
	//record purchase
	$query = "INSERT INTO gold_market_history VALUES('$user_id', $position_id, $for_country_id, '$product_id',
			 '$buying_quantity', '$price', '$bonus', CURRENT_DATE, CURRENT_TIME)";
	$conn->query($query);
	
	//display summary
	echo json_encode(array("success"=>true,
						   "msg"=>"You have successfully bought $buying_quantity $product_name for $payment Gold."
						   ));
?>