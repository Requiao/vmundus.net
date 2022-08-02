<?php
//Description: Offer products on market.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');

	$quantity =  htmlentities(stripslashes(trim($_POST['quantity'])), ENT_QUOTES);
	$price =  htmlentities(stripslashes(trim($_POST['price'])), ENT_QUOTES);
	$country_id =  htmlentities(stripslashes(trim($_POST['country_id'])), ENT_QUOTES);
	$offer_id =  htmlentities(stripslashes(trim($_POST['offer_id'])), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$company_id =  htmlentities(stripslashes(strip_tags(trim($_POST['company_id']))), ENT_QUOTES);
	$sell_for =  htmlentities(stripslashes(strip_tags(trim($_POST['sell_for']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	

	$user_id = $_SESSION['user_id'];
	$OFFERS_LIMIT = 3;
	
	function checkIfGovernor(&$for_country_id = 0, &$position_id = 0) {
		global $conn;
		global $user_id;
		
		//check if president
		$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($position_id, $for_country_id) = $row;
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have permission to perform this action."
								  )));
		}
		return;
	}
	
	function isProductExist($product_id) {
		global $conn;
		
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist."
								  )));
		}
	}
	
	function isCountryExist($country_id) {
		global $conn;
		
		$query = "SELECT * FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist."
								  )));
		}
	}
	
	if($action == 'get_info' || $action == 'sell') {
		if($sell_for != 'company' && $sell_for != 'self' && $sell_for != 'ministry') {
			exit(json_encode(array('success'=>false,
								   'error'=>"Invalid request."
								  )));
		}
		if($sell_for == 'company') {
			if(!is_numeric($company_id)) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Company doesn't exist."
									  )));
			}
			$query = "SELECT * FROM user_building WHERE company_id = '$company_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
					exit(json_encode(array(
						'success'=>false,
						'error'=>"Corporations are not allowed to sell products."
					)));
			}
		}
		else if ($sell_for == 'ministry') {
			$for_country_id = 0;
			$position_id = 0;
			checkIfGovernor($for_country_id, $position_id);
		}
	}
	
	if($action == 'get_info') {//get information about countries
		if($sell_for != 'company') {
			if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Product doesn't exist."
									  )));
			}
			isProductExist($product_id);
		}
		
		//is selling from company or warehouse
		if($sell_for == 'company') {
			$query = "SELECT * FROM user_building WHERE company_id = '$company_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
					exit(json_encode(array(
						'success'=>false,
						'error'=>"Corporations are not allowed to sell products."
					)));
			}

			//get tax info from country company located at
			$query = "SELECT country_abbr, country_name, c.country_id, sale_tax 
					  FROM country c, product_sale_tax pst, companies co, regions r
					  WHERE c.country_id = r.country_id AND pst.country_id = c.country_id AND r.region_id = co.location
					  AND co.company_id = '$company_id'
					  AND pst.product_id = (SELECT product_id FROM building_info WHERE building_id = 
					 (SELECT building_id FROM companies WHERE company_id = '$company_id'))";
		}
		else if($sell_for == 'self' || $sell_for == 'ministry') {
			//get taxes info from user's country
			$query = "SELECT country_abbr, country_name, c.country_id, sale_tax FROM country c, user_profile up,  product_sale_tax pst 
					  WHERE user_id = '$user_id' AND c.country_id = up.citizenship AND pst.country_id = up.citizenship
					  AND product_id = '$product_id'";
		}
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$tax_info = array();
		list($country_abbr, $user_country_name, $user_country_id, $sale_tax) = $row;
		if(strlen($user_country_name) > 15) {
			$user_country_name = $country_abbr;
		}
		array_push($tax_info, array("country_name"=>$user_country_name, "country_id"=>$user_country_id, "tax"=>$sale_tax));
		
		//get taxes info from all countries
		$other_country_taxes = array();
		if($sell_for == 'company') {
			$query = "SELECT country_abbr, country_name, c.country_id, sale_tax FROM country c, product_import_tax pit, 
					  companies co, regions r
					  WHERE c.country_id = pit.country_id AND r.region_id = co.location
					  AND co.company_id = '$company_id' AND permission = TRUE AND from_country_id = r.country_id
					  AND pit.product_id = (SELECT product_id FROM building_info WHERE building_id = 
					 (SELECT building_id FROM companies WHERE company_id = '$company_id'))
					  AND c.country_id != '$user_country_id'";
		}
		else if($sell_for == 'self' || $sell_for == 'ministry') {
			$query = "SELECT country_abbr, country_name, c.country_id, sale_tax FROM country c, product_import_tax pit 
					  WHERE pit.country_id = c.country_id AND product_id = '$product_id' AND permission = TRUE
					  AND from_country_id = '$user_country_id'
					  ORDER BY country_name";
		}
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($country_abbr, $country_name, $country_id, $sale_tax) = $row;
			if(strlen($country_name) > 15) {
				$country_name = $country_abbr;
			}
			array_push($tax_info, array("country_name"=>$country_name, "country_id"=>$country_id, "tax"=>$sale_tax));
		}
		echo json_encode(array('success'=>true,
							   'tax_info'=>$tax_info
							  ));
	}
	else if($action == 'sell') {
		if($sell_for == 'ministry' || $sell_for == 'self') {
			if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Product doesn't exist."
									  )));
			}
			isProductExist($product_id);
		}
		
		if(!is_numeric($quantity) || $quantity < 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Amount must be more than 0 and must be a whole number."
								  )));
		}
		if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist."
								  )));
		}
		isCountryExist($country_id);
		
		if(!is_numeric($price) || $price < 0.1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Price must be more than or equal to 0.1."
								  )));
		}
		if($price > 100000) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Price must be less than 100 000."
								  )));
		}
		if($quantity > 1000) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Quantity must be less than 1 000."
								  )));
		}
		$quantity = floor($quantity);

		if($sell_for == 'company') {
			$query = "SELECT * FROM user_building WHERE company_id = '$company_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
					exit(json_encode(array(
						'success'=>false,
						'error'=>"Corporations are not allowed to sell products."
					)));
			}
		}
		
		//check if enough products in the warehouse
		if($sell_for == 'company') {
			//get product_id
			$query = "SELECT product_id FROM building_info WHERE building_id = (SELECT building_id FROM
					  companies WHERE company_id = '$company_id')";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($product_id) = $row;
			
			//check if company have enough products
			$query = "SELECT amount, country_id FROM product_warehouse pw, companies c, regions r
					  WHERE c.company_id = '$company_id' AND pw.company_id = c.company_id
					  AND region_id = location AND product_id = '$product_id'";
		}
		else if($sell_for == 'self') {
			//check if user have enough products
			$query = "SELECT amount, citizenship FROM user_product up, user_profile u WHERE u.user_id = '$user_id' 
					  AND product_id = '$product_id' AND up.user_id = u.user_id";
			
		}
		else if ($sell_for == 'ministry') {
			$query = "SELECT amount, country_id FROM ministry_product WHERE position_id = '$position_id' 
					  AND product_id = '$product_id' AND country_id = '$for_country_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have this product in the warehouse."
								  )));
		}
		$row = $result->fetch_row();
		list($amount, $warehouse_country) = $row;
		if($amount < $quantity) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough products in the warehouse."
								  )));
		}
		
		//check in not offering more than 3 items of the same type and in the same country
		if($sell_for == 'company') {
			$query = "SELECT COUNT(*) FROM product_market WHERE (user_id = '$user_id' OR company_id = '$company_id') 
					  AND product_id = '$product_id' AND country_id = '$country_id'";
		}
		else if($sell_for == 'self') {
			$query = "SELECT COUNT(*) FROM product_market WHERE (user_id = '$user_id' OR company_id IN 
					 (SELECT company_id FROM user_building WHERE user_id = '$user_id'))
					 AND product_id = '$product_id' AND country_id = '$country_id'";
		}
		else if ($sell_for == 'ministry') {
			$query = "SELECT COUNT(*) FROM product_market WHERE position_id = '$position_id' AND for_country_id = '$for_country_id'
					  AND product_id = '$product_id' AND country_id = '$country_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row = $result->fetch_row();
			list($offered_quantity) = $row;
			if($offered_quantity >= $OFFERS_LIMIT) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You are allowed to offer only $OFFERS_LIMIT products of the same type in each country."
									  )));
			}
		}
		
		//exporting
		if($country_id != $warehouse_country) {
			$query = "SELECT permission FROM product_import_tax WHERE country_id = '$country_id' 
					  AND from_country_id = '$warehouse_country' AND product_id = '$product_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($permission) = $row;
			if($permission == 0) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You are not allowed to export products into this country."
								  )));
			}
		}
		
		$offer_id = getTimeForId() . $user_id;
		
		if($sell_for == 'company') {
			$query = "INSERT INTO product_market (offer_id, country_id, product_id, quantity, price, company_id) 
					  VALUES('$offer_id', '$country_id', '$product_id', '$quantity', '$price', '$company_id')";
		}
		else if($sell_for == 'self') {
			$query = "INSERT INTO product_market (offer_id, user_id, country_id, product_id, quantity, price) 
					  VALUES('$offer_id', '$user_id', '$country_id', '$product_id', '$quantity', '$price')";
		}
		else if ($sell_for == 'ministry') {
			$query = "INSERT INTO product_market (offer_id, country_id, product_id, quantity, price, for_country_id, position_id) 
					  VALUES('$offer_id', '$country_id', '$product_id', '$quantity', '$price', '$for_country_id', '$position_id')";
		}
		if($conn->query($query)) {
			if($sell_for == 'company') {
				$query = "UPDATE product_warehouse SET amount = (SELECT amount - '$quantity' FROM 
						 (SELECT amount FROM product_warehouse WHERE company_id = '$company_id') AS temp)
						  WHERE company_id = '$company_id'";
			}
			else if($sell_for == 'self') {
				$query = "UPDATE user_product SET amount = (SELECT amount - '$quantity' FROM 
						 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			else if ($sell_for == 'ministry') {
				$query = "UPDATE ministry_product SET amount = (SELECT amount - '$quantity' FROM (SELECT amount FROM ministry_product 
						  WHERE country_id = '$for_country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) 
						  WHERE country_id = '$for_country_id' AND product_id = '$product_id' 
						  AND position_id = '$position_id'";
			}
			$conn->query($query);
				
			if($country_id == $warehouse_country) {//sells in his own country
				$query = "SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr
						  FROM product_market pm, country c, product_sale_tax pst, product_info pi, currency cu
						  WHERE c.country_id = pm.country_id AND pst.country_id = c.country_id AND pst.country_id = '$warehouse_country'
						  AND pst.product_id = pm.product_id AND pi.product_id = pm.product_id AND cu.currency_id = c.currency_id
						  AND offer_id = '$offer_id'";
			}
			else {//exporting
				$query = "SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr 
						  FROM product_market pm, country c, product_import_tax pit, product_info pi, currency cu
						  WHERE c.country_id = pm.country_id AND pit.country_id = c.country_id AND from_country_id = '$warehouse_country'
						  AND pit.product_id = pm.product_id AND pi.product_id = pm.product_id AND cu.currency_id = c.currency_id
						  AND offer_id = '$offer_id'";
			}
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($flag, $quantity, $price, $sale_tax, $product_icon, $product_name, $offer_id, $currency_abbr) = $row;
			echo json_encode(array('success'=>true,
								   'msg'=>"Product offered.",
								   'flag'=>$flag,
								   'quantity'=>$quantity,
								   'price'=>$price,
								   'sale_tax'=>$sale_tax,
								   'product_icon'=>$product_icon,
								   'product_name'=>$product_name,
								   'offer_id'=>$offer_id,
								   'currency_abbr'=>$currency_abbr,
								  ));
		}
	}
	else if ($action == 'remove_user_offer') {
		if(!is_numeric($offer_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Offer doesn't exist."
								  )));
		}
		//check if user owns offer
		$query = "SELECT quantity, product_id FROM product_market WHERE user_id = '$user_id' AND offer_id = '$offer_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Offer doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($quantity, $product_id) = $row;
		
		//check if enough space in the warehouse
		$query = "SELECT SUM(amount) AS total_fill, (SELECT capacity FROM user_warehouse WHERE user_id = '$user_id') AS warehouse_capacity
				  FROM user_product WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_fill, $warehouse_capacity) = $row;
		$need_space = $warehouse_capacity - ($total_fill + $quantity);
		if($need_space <= 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough space in the warehouse. You need " . 
											 number_format(($need_space*-1), '0', '', ' ') . " extra space."
								  )));
		}
		
		$query = "DELETE FROM product_market WHERE offer_id = '$offer_id'";
		if($conn->query($query)) {
			$query = "UPDATE user_product SET amount = (SELECT amount + '$quantity' FROM 
					 (SELECT amount FROM user_product WHERE user_id = '$user_id'  AND product_id = '$product_id') AS temp) 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			$conn->query($query);
			echo json_encode(array('success'=>true,
								   'quantity'=>$quantity,
								   'product_id'=>$product_id
								  ));
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"Error. Try again."
								  )));
		}
	}
	else if ($action == 'remove_company_offer') {
		if(!is_numeric($offer_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Offer doesn't exist."
								  )));
		}

		$query = "SELECT quantity, company_id, (SELECT product_ware FROM companies WHERE company_id = 
				 (SELECT company_id FROM product_market WHERE offer_id = '$offer_id')),
				 (SELECT amount FROM product_warehouse WHERE company_id = 
				 (SELECT company_id FROM product_market WHERE offer_id = '$offer_id'))
				  FROM product_market WHERE offer_id = '$offer_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($quantity, $company_id, $company_warehouse, $warehouse_fill) = $row;
		
		//check if company belongs to the user
		$query = "SELECT * FROM user_building WHERE user_id = '$user_id' AND company_id = '$company_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Offer doesn't exist."
			)));
		}
		
		$new_amount = $quantity + $warehouse_fill;
		if($new_amount > $company_warehouse) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough space in company warehouse."
								  )));
		}
		
		
		$query = "DELETE FROM product_market WHERE offer_id = '$offer_id'";
		if($conn->query($query)) {
			$query = "UPDATE product_warehouse SET amount = (SELECT amount + '$quantity' FROM 
					 (SELECT amount FROM product_warehouse WHERE company_id = '$company_id') AS temp) 
					  WHERE company_id = '$company_id'";
			$conn->query($query);
			echo json_encode(array('success'=>true,
								   'quantity'=>$quantity
								  ));
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"Error. Try again."
								  )));
		}
	}
	else if ($action == 'remove_ministry_offer') {
		if(!is_numeric($offer_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Offer doesn't exist."
								  )));
		}
		
		$for_country_id = 0;
		$position_id = 0;
		checkIfGovernor($for_country_id, $position_id);
		
		//check if ministry owns offer
		$query = "SELECT quantity, product_id FROM product_market WHERE position_id = '$position_id' 
				  AND for_country_id = '$for_country_id' AND offer_id = '$offer_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Offer doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($quantity, $product_id) = $row;
		
		$query = "DELETE FROM product_market WHERE offer_id = '$offer_id'";
		if($conn->query($query)) {
			$query = "UPDATE ministry_product SET amount = (SELECT amount + '$quantity' FROM (SELECT amount FROM ministry_product 
					  WHERE country_id = '$for_country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) 
					  WHERE country_id = '$for_country_id' AND product_id = '$product_id' 
					  AND position_id = '$position_id'";
			$conn->query($query);
			echo json_encode(array('success'=>true,
								   'quantity'=>$quantity,
								   'product_id'=>$product_id
								  ));
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"Error. Try again."
								  )));
		}
	}
	else {
		exit(json_encode(array('success'=>false,
								   'error'=>"Invalid request"
								  )));
	}
?>