<?php
	//Description: Manage company
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/cut_long_name.php');//cutLongName($string, $max_length);
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	
	$company_id =  htmlentities(stripslashes(strip_tags(trim($_POST['co_id']))), ENT_QUOTES);
	$warehouse_type =  htmlentities(stripslashes(strip_tags(trim($_POST['ware_type']))), ENT_QUOTES);
	$job_id =  htmlentities(stripslashes(strip_tags(trim($_POST['job_id']))), ENT_QUOTES);
	$salary =  htmlentities(stripslashes(strip_tags(trim($_POST['salary']))), ENT_QUOTES);
	$skill =  htmlentities(stripslashes(trim($_POST['skill_lvl'])), ENT_QUOTES);
	$corporation_id =  htmlentities(stripslashes(strip_tags(trim($_POST['corp_id']))), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$price =  htmlentities(stripslashes(strip_tags(trim($_POST['price']))), ENT_QUOTES);
	$company_name =  htmlentities(stripslashes(strip_tags(trim($_POST['company_name']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$MAX_WORKERS = 5;
	$RETURN_RATE = 0.9;//products being returned
	$UPGRADE_CAPACITY = 100;
	$MAX_UNLIMITED_WAREHOUSE_CAPACITY = 900000000;
	
	$for_corporation = false;
	if(!empty($corporation_id)) {
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Corporation doesn't exist."
								  )));
		}
		
		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id' 
				  AND manager_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this corporation"
								   )));		
		}
		$for_corporation = true;
	}
	
	if(!is_numeric($company_id)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"You don't have access to this company"
							  )));
	}
	
	if($for_corporation) {
		$query = "SELECT company_id FROM corporation_building WHERE company_id = '$company_id' AND corporation_id = '$corporation_id'";
	}
	else {
		$query = "SELECT company_id FROM user_building WHERE company_id = '$company_id' AND user_id = '$user_id'";
	}
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array("success"=>false,
							   "error"=>"You don't have access to this company"
							   )));
	}
	
	if ($action == 'get_upgrade_warehouse_info' || $action == 'upgrade_warehouse' || 
	   $action == 'downgrade_warehouse' || $action == 'downgrade_warehouse_info') {
		if($warehouse_type == 'prod') {
			$warehouse = 'product_ware';
			$warehouse_type = 'product_warehouse';
		}
		else if($warehouse_type == 'rec') {
			$warehouse = 'resource_ware';
			$warehouse_type = 'resource_warehouse';
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Error. Invalid request."
								   )));
		}
	}

	if($action == 'rename_company') {
		strValidate($company_name, 1, 20, 'Company name');
		
		$query = "UPDATE companies SET company_name = '$company_name' WHERE company_id = '$company_id'";
		$conn->query($query);
		
		
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Company successfully renamed",
							   "company_name"=>($for_corporation?$lang['corporation']:'') . ' ' . $company_name . ' ' . $lang['company']
							  ));
	}
	else if($action == 'remove_com_market') {
		$query = "SELECT company_id FROM company_market WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {//sell
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['this_company_is_not_on_the_market']
									   )));
		}
		$query = "DELETE FROM company_market WHERE company_id = '$company_id'";
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['successfully_removed_company_from_market']
							  ));
	}
	else if($action == 'sell_company') {
		if($for_corporation) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Corporations are not allowed to sell companies."
				)));
		}
		$query = "SELECT company_id FROM company_market WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['this_company_is_already_on_the_market']
								   )));
		}
		if(!is_numeric($price) || $price < 100) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['price_must_be_more_than_or_equal_to'] . '100'
								   )));
		}
		$price = round($price, 2);
		if($price > 100000000) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['price_must_be_less_than_or_equal_to'] . '100 000 000'
								   )));
		}
		$query = "SELECT currency_name FROM currency WHERE currency_id = 
				 (SELECT currency_id FROM country WHERE country_id =
				 (SELECT country_id FROM regions WHERE region_id =
				 (SELECT location FROM companies WHERE company_id = '$company_id')))";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($currency_name) = $row;
		
		$query = "INSERT INTO company_market VALUES('$company_id', '$price')";
		$conn->query($query);
		$price = number_format($price, 2, '.', ' ');
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['successfully_offered_company_for'] . " $price $currency_name"
							  ));
	}
	else if($action == 'get_upgrade_info') {
		$req_products = array();
		$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_upgrade bu 
				  WHERE bu.product_id = pi.product_id 
				  AND building_id = (SELECT building_id FROM companies WHERE company_id = '$company_id')";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_name, $product_icon, $amount) = $row;
			array_push($req_products, array("product_name"=>$product_name, "product_icon"=>$product_icon, "amount"=>$amount));
		}
		$reply = array("success"=>true,
					   "msg"=>$lang['after_upgrade_company_will_have_extra_working_place_maximum_is'] . " $MAX_WORKERS.",
					   "req_products"=>$req_products
					  );
		echo json_encode($reply);
	}
	else if($action == 'upgrade_company') {
		$query = "SELECT workers FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($workers) = $row;
		if($workers >= $MAX_WORKERS) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You are allowed to upgrade your company to have only $MAX_WORKERS workers."
								   )));
		}
		
		//determine if user has enough resources
		$query = "SELECT amount, product_id FROM building_upgrade WHERE building_id = (SELECT building_id FROM companies 
				  WHERE company_id = '$company_id')";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			if($for_corporation) {
				$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' AND amount >= '$amount' 
						  AND product_id = '$product_id'";
			}
			else {
				$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND amount >= '$amount' 
						  AND product_id = '$product_id'";
			}
			$resultr = $conn->query($query);
			if($resultr->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['you_dont_have_enough_resources']
									   )));
			}
		}
			
		// update companies table
		$query = "UPDATE companies SET workers = (SELECT workers FROM 
				 (SELECT workers FROM companies WHERE company_id = '$company_id') AS temp) + 1 
				  WHERE company_id = '$company_id'";
		if($conn->query($query)) {
			/* get resources for upgrade */
			$query = "SELECT amount, product_id FROM building_upgrade WHERE building_id = (SELECT building_id FROM companies 
					  WHERE company_id = '$company_id')";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($amount, $product_id) = $row;
				if($for_corporation) {
					$query = "UPDATE corporation_product SET amount = (SELECT amount - '$amount' FROM 
							 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
							  AND product_id = $product_id) AS temp)  
							  WHERE corporation_id = '$corporation_id' AND product_id = $product_id";
				}
				else {
					$query = "UPDATE user_product SET amount = (SELECT amount - '$amount' FROM 
							 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = $product_id) AS temp)  
							  WHERE user_id = '$user_id' AND product_id = $product_id";
				}
				$conn->query($query);
			}
			echo json_encode(array("success"=>true,
								   "msg"=>$lang['company_upgraded']
								  ));
		}
		else {
			echo json_encode(array("success"=>false,
								   "error"=>"Please try again."
								  ));
		}
	}
	else if ($action == 'downgrade_company_info') {
		//company level
		$query = "SELECT workers FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($workers) = $row;
		$products = array();
		if($workers > 1) {//downgrade
			//get resources that wil be returned
			$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_upgrade bu 
					  WHERE bu.product_id = pi.product_id 
					  AND building_id = (SELECT building_id FROM companies WHERE company_id = '$company_id')";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_name, $product_icon, $amount) = $row;
				array_push($products, array("product_name"=>$product_name, "product_icon"=>$product_icon, 
											"amount"=>$amount * $RETURN_RATE));
			}
			
			$reply = array("success"=>true,
						   "msg"=>$lang['after_downgrade_you_will_receive'] . " " . $RETURN_RATE * 100 . 
								  "% " . $lang['of_the_products_spent_on_the_company_upgrade'],
						   "products"=>$products,
						   "button_name"=>$lang['downgrade']
						  );
		}
		else { //destroy
			$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_product bp 
					  WHERE bp.product_id = pi.product_id 
					  AND building_id = (SELECT building_id FROM companies WHERE company_id = '$company_id')";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_name, $product_icon, $amount) = $row;
				array_push($products, array("product_name"=>$product_name, "product_icon"=>$product_icon, 
											"amount"=>$amount * $RETURN_RATE));
			}
			$reply = array("success"=>true,
						   "msg"=>$lang['after_downgrade_you_will_receive'] . " " . $RETURN_RATE * 100 . 
								  "% " . $lang['of_the_products_spent_on_the_company_build'],
						   "products"=>$products,
						   "button_name"=>$lang['destroy']
						  );
		}
		echo json_encode($reply);
	}
	else if ($action == 'downgrade_company') {
		//select available work seats
		$query = "SELECT workers FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($workers) = $row;//workers also stands for the company level 1...5
		
		//count hired workers. if all positions are taken, exit
		$query = "SELECT COUNT(*) FROM hired_workers WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($hired_workers) = $row;
		if($hired_workers == $workers) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_must_first_fire_at_least_one_worker']
								   )));
		}
		
		//get job offers
		$query = "SELECT COUNT(*) FROM job_market WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($offered_jobs) = $row;
		if(($offered_jobs + $hired_workers) == $workers) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_must_first_remove_at_least_one_job_offer_from_the_market']
								   )));
		}
		
		if($workers > 1) {//downgrade
			//check if main storage won't be overfilled
			if($for_corporation) {
				$query = "SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_upgrade WHERE building_id = (SELECT building_id FROM companies 
						  WHERE company_id = '$company_id')) FROM corporation_product WHERE corporation_id = '$corporation_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($warehouse_fill) = $row;
				if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
					exit(json_encode(array('success'=>false,
										   'error'=>$lang['not_enough_capacity_in_the_corporation_warehouse']
										  )));
				}
			}
			else {
				$query = "SELECT capacity FROM user_warehouse WHERE user_id = '$user_id'
						  HAVING capacity >= (SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_upgrade WHERE building_id = (SELECT building_id FROM companies 
						  WHERE company_id = '$company_id')) FROM user_product WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					exit(json_encode(array("success"=>false,
										   "error"=>$lang['after_downgrade_main_storage_will_be_overfilled_downgrade_failed']
										   )));
				}
			}
			
			// update companies table
			$query = "UPDATE companies SET workers = (SELECT workers FROM 
					 (SELECT workers FROM companies WHERE company_id = '$company_id') AS temp) - 1 
					  WHERE company_id = '$company_id'";
			if($conn->query($query)) {
				/* give resources for downgrade */
				$query = "SELECT amount, product_id FROM building_upgrade WHERE building_id = (SELECT building_id FROM companies 
						  WHERE company_id = '$company_id')";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($amount, $product_id) = $row;
					$amount = $amount * $RETURN_RATE;
					if($for_corporation) {
						$query = "UPDATE corporation_product SET amount = (SELECT amount + '$amount' FROM 
								 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
								  AND product_id = $product_id) AS temp)  
								  WHERE corporation_id = '$corporation_id' AND product_id = $product_id";
					}
					else {
						$query = "UPDATE user_product SET amount = (SELECT amount + '$amount' FROM 
								 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = $product_id) AS temp)  
								  WHERE user_id = '$user_id' AND product_id = $product_id";
					}
					$conn->query($query);
				}
				echo json_encode(array("success"=>true,
									   "msg"=>$lang['company_downgraded'],
									   "status"=>"downgraded"
									  ));
			}
			else {
				echo json_encode(array("success"=>false,
									   "error"=>"Please try again."
									  ));
			}
		}
		else {//destroy
			if($for_corporation) {
				$query = "SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_product WHERE building_id = (SELECT building_id FROM companies 
						  WHERE company_id = '$company_id')) FROM corporation_product WHERE corporation_id = '$corporation_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($warehouse_fill) = $row;
				if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
					exit(json_encode(array('success'=>false,
										   'error'=>$lang['not_enough_capacity_in_the_corporation_warehouse']
										  )));
				}
			}
			else {
				$query = "SELECT capacity FROM user_warehouse WHERE user_id = '$user_id'
						  HAVING capacity >= (SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_product WHERE building_id = (SELECT building_id FROM companies 
						  WHERE company_id = '$company_id')) FROM user_product WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				if($result->num_rows != 1) {
					exit(json_encode(array("success"=>false,
										   "error"=>$lang['destroy_failed_main_storage_will_be_overfilled']
										   )));
				}
			}
			//check if selling products
			$query = "SELECT * FROM product_market WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			if($result->num_rows > 0) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['before_destroying_this_company_remove_all_product_offers_from_the_company_market']
									   )));
			}
			
			//check if warehouses already destroyed
			$query = "SELECT resource_ware, product_ware FROM companies WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($resource_ware, $product_ware) = $row;
			if($resource_ware > 100) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['destroy_your_resource_warehouse']
									   )));
			}
			else if($product_ware > 100) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['destroy_your_product_warehouse']
									   )));
			}
			
			//check if products left in the warehouses
			$query = "SELECT SUM(amount) FROM resource_warehouse WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($resource_warehouse_fill) = $row;
			if($resource_warehouse_fill >= 0.01) {
				exit(json_encode(array("success"=>false,
									   "error"=>'Withdraw all products from the company resource storage'
									   )));
			}
			
			$query = "SELECT SUM(amount) FROM product_warehouse WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($product_warehouse_fill) = $row;
			if($product_warehouse_fill >= 0.01) {
				exit(json_encode(array("success"=>false,
									   "error"=>'Withdraw all products from the company product storage'
									   )));
			}
			
			/* give resources for destroy */
			$query = "SELECT amount, product_id FROM building_product WHERE building_id = (SELECT building_id FROM companies 
					  WHERE company_id = '$company_id')";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($amount, $product_id) = $row;
				$amount = $amount * $RETURN_RATE;
				if($for_corporation) {
					$query = "UPDATE corporation_product SET amount = (SELECT amount + '$amount' FROM 
							 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
							  AND product_id = $product_id) AS temp)  
							  WHERE corporation_id = '$corporation_id' AND product_id = $product_id";
				}
				else {
					$query = "UPDATE user_product SET amount = (SELECT amount + '$amount' FROM 
							  (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = $product_id) AS temp)  
							   WHERE user_id = '$user_id' AND product_id = $product_id";
				}
				$conn->query($query);
			}
		
			if($for_corporation) {
				$query = "DELETE FROM corporation_building WHERE company_id = '$company_id'";
			}
			else {
				$query = "DELETE FROM user_building WHERE company_id = '$company_id'";
			}
			$conn->query($query);
			$query = "DELETE FROM resource_warehouse WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM product_warehouse WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM job_market WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM hired_workers WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM product_market WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM work_journal WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM company_market WHERE company_id = '$company_id'";
			$conn->query($query);
			$query = "DELETE FROM companies WHERE company_id = '$company_id'";
			$conn->query($query);
			
			echo json_encode(array("success"=>true,
								   "msg"=>$lang['company_destroyed'],
								   "status"=>"destroyed"
								  ));
		}
	}
	else if ($action == 'get_upgrade_warehouse_info') {
		$query = "SELECT $warehouse FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($ware_capacity) = $row;
		if($ware_capacity == 0) {//build
			$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_product bp 
					  WHERE bp.product_id = pi.product_id 
					  AND building_id = 17";
		}
		else if($ware_capacity > 0) {//upgrade
			$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_upgrade bu 
					  WHERE bu.product_id = pi.product_id 
					  AND building_id = 17";
		}
		$products = array();
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_name, $product_icon, $amount) = $row;
			array_push($products, array("product_name"=>$product_name, "product_icon"=>$product_icon, 
										"amount"=>$amount
									   ));
		}
		$reply = array("success"=>true,
					   "msg"=>$lang['after_upgrade_warehouse_will_have'] . " $UPGRADE_CAPACITY " . $lang['extra_space'],
					   "products"=>$products
					  );
		echo json_encode($reply);
	}
	else if ($action == 'upgrade_warehouse') {
		$query = "SELECT $warehouse FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($ware_capacity) = $row;
		
		//max capacity is 900 000. if more exit.
		if(($ware_capacity + $UPGRADE_CAPACITY) > 90000) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['maximum_storage_volume_is'] . " 90 000."
								  )));
		}
		
		//determine if user has enough resources
		if($ware_capacity == 0) {
			$query_bu = "SELECT amount, product_id FROM building_product WHERE building_id = 17";
		}
		else if($ware_capacity > 0) {
			$query_bu = "SELECT amount, product_id FROM building_upgrade WHERE building_id = 17";
		}
		$result = $conn->query($query_bu);
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			if($for_corporation) {
				$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' AND amount >= '$amount' 
						  AND product_id = '$product_id'";
			}
			else {
				$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND amount >= '$amount' 
						  AND product_id = '$product_id'";
			}
			$resultr = $conn->query($query);
			if($resultr->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['you_dont_have_enough_resources']
									  )));
			}
		}				
		
		// update companies table
		$query = "UPDATE companies SET $warehouse = (SELECT * FROM 
				 (SELECT $warehouse FROM companies WHERE company_id = '$company_id') AS temp) + 100
				  WHERE company_id = '$company_id'";
		if($conn->query($query)) {
			/* get resources for upgrade */
			$result = $conn->query($query_bu);
			while($row = $result->fetch_row()) {
				list($amount, $product_id) = $row;
				if($for_corporation) {
					$query = "UPDATE corporation_product SET amount = (SELECT amount - '$amount' FROM 
							 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
							  AND product_id = $product_id) AS temp)  
							  WHERE corporation_id = '$corporation_id' AND product_id = $product_id";
				}
				else {
					$query = "UPDATE user_product SET amount = (SELECT amount - '$amount' FROM 
							 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = $product_id) AS temp)  
							  WHERE user_id = '$user_id' AND product_id = $product_id";
				
				}
				$conn->query($query);
			}

			echo json_encode(array("success"=>true,
								   "msg"=>$lang['warehouse_upgraded'],
								   "capacity_add"=>$UPGRADE_CAPACITY
								  ));
		}
	}
	else if ($action == 'downgrade_warehouse_info') {
		$query = "SELECT $warehouse FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($ware_capacity) = $row;
		
		if($ware_capacity <= 100) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['warehouse_cannot_be_downgraded_more']
									   )));
		}
		
		$products = array();
		//get resources that wil be returned
		if($ware_capacity > $UPGRADE_CAPACITY) {//downgrade
			$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_upgrade bu 
					  WHERE bu.product_id = pi.product_id AND building_id = 17";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_name, $product_icon, $amount) = $row;
				array_push($products, array("product_name"=>$product_name, "product_icon"=>$product_icon, 
											"amount"=>$amount * $RETURN_RATE));
			}
		}
		else { //destroy
			$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_product bp 
					  WHERE bp.product_id = pi.product_id AND building_id = 17";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_name, $product_icon, $amount) = $row;
				array_push($products, array("product_name"=>$product_name, "product_icon"=>$product_icon, 
											"amount"=>$amount * $RETURN_RATE));
			}
		}
		$reply = array("success"=>true,
					   "msg"=>$lang['after_downgrade_you_will_receive'] . " " . $RETURN_RATE * 100 . 
							 "% " . $lang['of_the_products_spent_on_the_warehouse_upgrade'],
					   "products"=>$products,
					   "button_name"=>$lang['downgrade']
					  );
		echo json_encode($reply);
	}
	else if ($action == 'downgrade_warehouse') {
		$query = "SELECT $warehouse FROM companies WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($ware_capacity) = $row;

		if($ware_capacity <= 100) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['warehouse_cannot_be_downgraded_more']
									   )));
		}
		
		//check for products in the warehouses
		if($warehouse_type == 'product_warehouse') {
			$query = "SELECT amount FROM product_warehouse WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($product_fill) = $row;
			$product_fill = floor($product_fill * 100) / 100;//important due to datatype decimal(8,3)
			if($product_fill > ($ware_capacity - $UPGRADE_CAPACITY)) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['withdraw_at_least'] . " "  . 
												($product_fill - ($ware_capacity - $UPGRADE_CAPACITY)) . 
												" " . $lang['produced_products_from_this_company']
									   )));
			}
		}
		else if($warehouse_type == 'resource_warehouse') {
			$query = "SELECT SUM(amount) FROM resource_warehouse WHERE company_id = '$company_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($product_fill) = $row;
			if($product_fill > ($ware_capacity - $UPGRADE_CAPACITY)) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['withdraw_at_least'] . " "  . 
												($product_fill - ($ware_capacity - $UPGRADE_CAPACITY)) . 
												" " . $lang['resources_from_this_company']
									   )));
			}
		}

		if($ware_capacity <= $UPGRADE_CAPACITY) {//destroy
			//check if main storage won't be overfilled
			if($for_corporation) {
				$query = "SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_product WHERE building_id = 17) 
						  FROM corporation_product WHERE corporation_id = '$corporation_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($warehouse_fill) = $row;
				if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
					exit(json_encode(array('success'=>false,
										   'error'=>$lang['not_enough_capacity_in_the_corporation_warehouse']
										  )));
				}
			}
			else {
				$query = "SELECT capacity FROM user_warehouse WHERE user_id = '$user_id'
						  HAVING capacity >= (SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_product WHERE building_id = 17) FROM user_product WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				if($result->num_rows != 1) {
					exit(json_encode(array("success"=>false,
										   "error"=>$lang['after_downgrade_main_storage_will_be_overfilled_downgrade_failed']
										   )));
				}
			}
			//select resources user will get after downgrade
			$query = "SELECT amount, product_id FROM building_product WHERE building_id = 17";
		}
		else if($ware_capacity > $UPGRADE_CAPACITY) {//downgrade
			//check if main storage won't be overfilled
			if($for_corporation) {
				$query = "SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_upgrade WHERE building_id = 17) 
						  FROM corporation_product WHERE corporation_id = '$corporation_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($warehouse_fill) = $row;
				if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
					exit(json_encode(array('success'=>false,
										   'error'=>$lang['not_enough_capacity_in_the_corporation_warehouse']
										  )));
				}
			}
			else {
				$query = "SELECT capacity FROM user_warehouse WHERE user_id = '$user_id'
						  HAVING capacity >= (SELECT SUM(amount) + (SELECT SUM(amount) * $RETURN_RATE
						  FROM building_upgrade WHERE building_id = 17) FROM user_product WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				if($result->num_rows != 1) {
					exit(json_encode(array("success"=>false,
										   "error"=>$lang['after_downgrade_main_storage_will_be_overfilled_downgrade_failed']
										   )));
				}
			}
			$query = "SELECT amount, product_id FROM building_upgrade WHERE building_id = 17";
		}
		else if($ware_capacity <= 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['warehouse_cannot_be_downgraded_more']
								  )));
		}

		//give back resources spent on the warehouse
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			$amount = $amount * $RETURN_RATE;
			if($for_corporation) {
				$query = "UPDATE corporation_product SET amount = (SELECT amount + '$amount' FROM 
						 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
						  AND product_id = '$product_id') AS temp)  
						  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT amount + '$amount' FROM 
						 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp)  
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			$conn->query($query);
		}
		
		// update companies table
		$query = "UPDATE companies SET $warehouse = (SELECT $warehouse FROM 
				 (SELECT $warehouse FROM companies WHERE company_id = '$company_id') AS temp) - $UPGRADE_CAPACITY 
				  WHERE company_id = '$company_id'";
		if($conn->query($query)) {
			if($ware_capacity > $UPGRADE_CAPACITY) {
				echo json_encode(array("success"=>true,
									   "msg"=>$lang['warehouse_downgraded'],
								 	   "capacity_add"=>$UPGRADE_CAPACITY
									  ));
			}
			else {
				echo json_encode(array("success"=>true,
									   "msg"=>$lang['warehouse_destroyed'],
								 	   "capacity_add"=>$UPGRADE_CAPACITY
									  ));
			}
			
		}
	}
	else if($action == 'offer_job') {
		if(!filter_var($skill, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['invalid_input_for_skill']
								  )));
		}
		if(!is_numeric($salary)) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['invalid_input_for_salary']
								  )));
		}
		
		//test quantity
		if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_a_whole_number']
								  )));
		}
		if($quantity < 0 || $quantity > 5) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_in_the_range'] . " 1...5"
								  )));
		}
		$query = "SELECT COUNT(person_id), (SELECT workers FROM companies WHERE company_id = '$company_id'),
				  (SELECT COUNT(*) FROM job_market WHERE company_id = '$company_id')
				  FROM hired_workers WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($hired_workers, $workers, $offered_jobs) = $row;
		$can_offer_jobs = $workers - $hired_workers - $offered_jobs;
		
		if($can_offer_jobs < $quantity) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_can_make_only']  . " $can_offer_jobs " . $lang['cm_job_offers']
								  )));
		}
		
		//max exp is 25
		if($skill > 25 || $skill <= 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['skill_level_must_be_in_the_range'] . "1...25."
								  )));
		}
		
		if($salary > 1000 || $salary < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['salary_level_must_be_in_the_range'] . "1...1 000."
								  )));
		}
		$salary = round($salary, 2);
		
		for($x = 0; $x < $quantity; $x++) { 
			$job_id = getTimeForId() . $user_id; //create job id
			$query = "INSERT INTO job_market VALUES('$company_id', '$salary', '$skill', '$job_id')";
			$conn->query($query);
		}
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['jobs_successfully_offered'] . ". " . $lang['salary'] .  ": $salary, " .
									  $lang['skill'] . ": $skill."
							  ));
	}
	else if ($action == 'get_offered_jobs') {
		//select current offers
		$query = "SELECT salary, currency_abbr, skill_lvl, job_id FROM job_market, currency WHERE company_id = '$company_id' 
				  AND currency_id = (SELECT currency_id FROM country WHERE country_id = (SELECT country_id FROM regions WHERE region_id = 
				 (SELECT location FROM companies WHERE company_id = '$company_id')))";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false
								  )));
		}
		$offered_jobs = array();
		while($row = $result->fetch_row()) {
			list($salary, $currency_abbr, $skill_lvl, $job_id) = $row;
			$salary = number_format($salary, 2, '.', ' ');
			array_push($offered_jobs, array("salary"=>$salary, "currency_abbr"=>$currency_abbr, "skill_lvl"=>$skill_lvl,
					   "job_id"=>$job_id));
		}
		echo json_encode(array("success"=>true,
							   "offered_jobs"=>$offered_jobs
							  ));
	}
	else if($action == 'remove_job') {
		if(!is_numeric($job_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Job doesn't exists."
								  )));
		}
		$query = "DELETE FROM job_market WHERE job_id = '$job_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true
								  ));
		}
	}
	else if($action == 'get_hired_workers') {
		//show all workers
		$query = "SELECT user_image, user_name, salary, currency_abbr, job_id, p.person_id, person_name, u.user_id, p.experience
				  FROM country c, user_profile up,  users u, people p, hired_workers hw, currency cu
				  WHERE up.user_id = u.user_id AND p.user_id = up.user_id AND hw.person_id = p.person_id AND c.currency_id = cu.currency_id
				  AND country_id = (SELECT country_id FROM regions WHERE region_id = 
				  (SELECT location FROM companies WHERE company_id = '$company_id')) AND company_id = '$company_id'";
		$result_workers = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false
								  )));
		}
		$hired = array();
		while($row_workers = $result_workers->fetch_row()) {
			list($user_image, $user_name, $salary, $currency_abbr, $job_id, $person_id, $person_name, $profile_id, $experience) = $row_workers;
			//get skill lvl
			$query_skill = "SELECT MAX(skill_lvl) FROM experience WHERE required_exp <= '$experience'";
			$result_skill = $conn->query($query_skill);
			$row_skill =$result_skill->fetch_row();
			list($skill) = $row_skill;
			$salary = number_format($salary, 2, '.', ' ');
			
			//select day count
			$query = "SELECT day_number, date FROM day_count
					  WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) ORDER BY day_number";
			$result = $conn->query($query);
			$start_day = strtotime(correctDate(date('Y-m-d'), date('H:i:s'), 0, $profile_id) . "00:00:00 - 6 day");
			
			$day_count_info = array();
			$x = 0;
			while($row = $result->fetch_row()) {
				list($day_number, $date) = $row;
				
				//make sure to display right day number
				$user_date = correctDate($date, date('H:i:s'), 0, $profile_id);
				$user_date_start = strtotime("$user_date 00:00:00");
				
				//if selects 'old' data
				if($user_date_start < $start_day) {
					continue;
				}
				
				if(strtotime($user_date) > strtotime($date)) {
					$day_number++;
				}
				else if(strtotime($user_date) < strtotime($date)) {
					$day_number--;
				}
				
				$day_count_info[$x]['day_number'] = $day_number;
				$day_count_info[$x]['user_date_start'] = $user_date_start;
				$x++;
			}
			//extra day to compare with
			$day_count_info[$x]['user_date_start'] = strtotime("$user_date 00:00:00 + 1 day");	
			
			//show workers production
			$production = array();
			$query = "SELECT produced, date, time
					  FROM work_journal WHERE company_id = '$company_id' AND person_id = '$person_id'
					  AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) ORDER BY date ASC, time ASC";
			$result = $conn->query($query);
			$x = -1;
			while($row = $result->fetch_row()) {
				list($produced, $date, $time) = $row;
				$date = correctDate($date, $time, 0, $profile_id);
				$time = correctTime($time, 0, $profile_id); 
				
				$user_work_date_time = strtotime("$date $time - 4 hour");
				
				//if selects 'old' data
				if($user_work_date_time < $start_day) {
					continue;
				}
				
				$next_day = false;
				$flag = true;
					
				while($flag) {
					if($user_work_date_time >= $day_count_info[$x+1]['user_date_start']
					   && $user_work_date_time < $day_count_info[$x+2]['user_date_start']) {
						$next_day = true;
						$flag = false;
						$x++;
					}
					else if($user_work_date_time >= $day_count_info[$x]['user_date_start']
							&& $user_work_date_time < $day_count_info[$x+1]['user_date_start']){
						$next_day = false;
						$flag = false;
					}
					else {
						$x++;
						$day_number = $day_count_info[$x]['day_number'];
						array_push($production, array("produced"=>"n/w", "day_number"=>$day_number));
					}
				}
				
				if($next_day) {
					$day_number = $day_count_info[$x]['day_number'];
					array_push($production, array("produced"=>$produced, "day_number"=>$day_number));
				}
			}
			
			$person_name = cutLongName($person_name, 10);
			array_push($hired, array("user_image"=>$user_image, "user_name"=>$user_name, "skill"=>$skill, "salary"=>$salary,
									 "currency_abbr"=>$currency_abbr, "job_id"=>$job_id, "person_name"=>$person_name,
									 "profile_id"=>$profile_id, 'production'=>$production));
		}
		echo json_encode(array("success"=>true,
							   "hired"=>$hired
							  ));
	}
	else if ($action == 'change_salary') {
		if(!is_numeric($job_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid job."
								  )));
		}
		if(!is_numeric($salary)) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['invalid_input_for_salary']
								  )));
		}
		if($salary > 1000 || $salary < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['salary_level_must_be_in_the_range'] . "1...1 000."
								  )));
		}
		$salary = round($salary, 2);
		
		$query = "SELECT u.user_name, person_name, p.user_id, company_name, salary, currency_abbr, location 
				  FROM users u, people p, companies c, hired_workers hw, country co, regions r, currency cu
				  WHERE u.user_id = '$user_id' AND p.person_id = hw.person_id AND job_id = '$job_id' AND co.currency_id = cu.currency_id
				  AND c.company_id = hw.company_id AND co.country_id = r.country_id AND r.region_id = c.location";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"This worker is not hired by your company."
								  )));
		}
		$row = $result->fetch_row();
		list($user_name, $person_name, $user_hired_id, $company_name, $old_salary, $currency_abbr) = $row;
		
		$notification = 'Employer "' . $user_name . '" changed salary for "' . $person_name . '" that
						is hired by "' . $company_name . '" company from ' . $old_salary . ' ' . $currency_abbr . 
						' to ' . $salary . ' ' . $currency_abbr .'.';
		sendNotification($notification, $user_hired_id);
		$query = "UPDATE hired_workers SET salary = '$salary' WHERE job_id = '$job_id'";
		if($conn->query($query)) {
			$salary = number_format($salary, 2, '.', ' ');
			echo json_encode(array("success"=>true,
								   "msg"=>$lang['salary_successfully_changed'],
								   "salary"=>$salary,
								   "currency_abbr"=>$currency_abbr
								  ));
		}
		else {
			exit(json_encode(array("success"=>false,
								   "error"=>"Please try again."
								  )));
		}
	}
	else if($action == 'fire_worker') {
		if(!is_numeric($job_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid job."
								  )));
		}
		$query = "SELECT u.user_name, person_name, p.user_id, company_name, hw.person_id, who 
				  FROM users u, people p, companies c, hired_workers hw
				  WHERE u.user_id = '$user_id' AND p.person_id = hw.person_id AND job_id = '$job_id'
				  AND c.company_id = hw.company_id AND hw.person_id = p.person_id";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_name, $person_name, $user_hired_id, $company_name, $person_id, $who) = $row;
		
		$notification =	'Employer "' . $user_name . '" fired "' . $person_name . '" from "' . $company_name . '".'; 
		sendNotification($notification, $user_hired_id);
		
		$query = "DELETE FROM hired_workers WHERE job_id = '$job_id'";
		$conn->query($query);
		
		if($who == 'working') {
			$query = "Update people SET who = 'available' WHERE person_id = '$person_id'";
			$conn->query($query);
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['fired']
							  ));
	}
	else if($action == 'fire_all_workers') {
		$query = "SELECT u.user_name, person_name, p.user_id, company_name, hw.person_id, who, job_id 
				  FROM users u, people p, companies c, hired_workers hw
				  WHERE u.user_id = '$user_id' AND p.person_id = hw.person_id AND c.company_id = '$company_id'
				  AND c.company_id = hw.company_id AND hw.person_id = p.person_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($user_name, $person_name, $user_hired_id, $company_name, $person_id, $who, $job_id) = $row;
			
			$notification =	'Employer "' . $user_name . '" fired "' . $person_name . '" from "' . $company_name . '".'; 
			sendNotification($notification, $user_hired_id);
			
			$query = "DELETE FROM hired_workers WHERE job_id = '$job_id'";
			$conn->query($query);
			
			if($who == 'working') {
				$query = "Update people SET who = 'available' WHERE person_id = '$person_id'";
				$conn->query($query);
			}
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['fired']
							  ));
	}
	else if ($action == 'withdraw_product') {
		if(!is_numeric($quantity) || $quantity < 0.01) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_more_than_or_equal_to'] . " 0.01"
								  )));
		}
		$quantity = round($quantity, 2);
		
		//check if company have products
		$query = "SELECT amount FROM product_warehouse WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($amount) = $row;
		
		//check if user have enough space in warehouse
		if($amount < $quantity) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['company_has_less_products_than_you_want_to_withdraw']
								  )));
		}
		
		if($for_corporation) {
			$query = "SELECT SUM(amount) + '$quantity'
					  FROM corporation_product WHERE corporation_id = '$corporation_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($warehouse_fill) = $row;
			if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
				exit(json_encode(array('success'=>false,
									   'error'=>$lang['not_enough_capacity_in_the_corporation_warehouse']
									  )));
			}
		}
		else {
			$query = "SELECT SUM(amount) AS total FROM user_product WHERE user_id = '$user_id' 
					  HAVING (SUM(amount) + '$quantity') <= (SELECT capacity FROM user_warehouse WHERE user_id = '$user_id')";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['not_enough_capacity_in_the_warehouse']
									  )));
			}
		}
		
		//detect product id
		$query = "SELECT product_id FROM product_info WHERE product_id = 
				 (SELECT product_id FROM building_info WHERE building_id = 
				 (SELECT building_id FROM companies WHERE company_id = '$company_id'))";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($product_id) = $row;
		
		//update product warehouse
		$query = "UPDATE product_warehouse SET amount = (SELECT amount - '$quantity' FROM 
				 (SELECT amount FROM product_warehouse WHERE company_id = '$company_id'  AND product_id = '$product_id') AS temp) 
				  WHERE company_id = '$company_id' AND product_id = '$product_id'";
		$conn->query($query);
				
		/* update user warehouse */
		//detect if user has or not product id in his warehouse
		if($for_corporation) {
			$query = "SELECT product_id FROM corporation_product WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		}
		else {
			$query = "SELECT product_id FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			if($for_corporation) {
				$query = "UPDATE corporation_product SET amount = (SELECT amount + '$quantity' FROM 
						 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
						  AND product_id = '$product_id') AS temp) 
						  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT amount + '$quantity' FROM 
						 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
		}
		else {
			if($for_corporation) {
				$query = "INSERT INTO corporation_product VALUES('$corporation_id', '$product_id', '$quantity')";
			}
			else {
				$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$quantity')";
			}
		}
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['products_in_the_quantity_of'] . " $quantity " . $lang['successfully_derived'],
							   "quantity"=>$quantity
							  ));
	}
	else if ($action == 'withdraw_resource') {
		if(!is_numeric($quantity) || $quantity < 0.01) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_more_than_or_equal_to'] . " 0.01"
								  )));
		}
		$quantity = round($quantity, 2);
		
		//test product
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product doesn't exist"
								  )));
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product doesn't exist"
								  )));
		}
		
		//test if product is required
		$query = "SELECT required_id FROM product_product WHERE required_id = '$product_id' AND building_id = 
				 (SELECT building_id FROM companies WHERE company_id = '$company_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product you're trying to withdraw doesn't exist in the company"
								  )));
		}
		
		//check if company have resources
		$query = "SELECT amount FROM resource_warehouse WHERE company_id = '$company_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($amount) = $row;
		
		//check if user have enough space in warehouse
		if($amount < $quantity) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['company_has_less_products_than_you_want_to_withdraw']
								  )));
		}
		
		if($for_corporation) {
			$query = "SELECT SUM(amount) + '$quantity'
					  FROM corporation_product WHERE corporation_id = '$corporation_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($warehouse_fill) = $row;
			if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
				exit(json_encode(array('success'=>false,
									   'error'=>$lang['not_enough_capacity_in_the_corporation_warehouse']
									  )));
			}
		}
		else {
			$query = "SELECT SUM(amount) AS total FROM user_product WHERE user_id = '$user_id' 
					  HAVING (SUM(amount) + '$quantity') <= (SELECT capacity FROM user_warehouse WHERE user_id = '$user_id')";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['not_enough_capacity_in_the_warehouse']
									  )));
			}
		}
	
		//update resource warehouse
		$query = "UPDATE resource_warehouse SET amount = (SELECT amount - '$quantity' FROM 
				 (SELECT amount FROM resource_warehouse WHERE company_id = '$company_id'  AND product_id = '$product_id') AS temp) 
				  WHERE company_id = '$company_id' AND product_id = '$product_id'";
		$conn->query($query);
				
		/* update user warehouse */
		if($for_corporation) {
			$query = "SELECT product_id FROM corporation_product WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		}
		else {
			$query = "SELECT product_id FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			if($for_corporation) {
				$query = "UPDATE corporation_product SET amount = (SELECT amount + '$quantity' FROM 
						 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
						  AND product_id = '$product_id') AS temp) 
						  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT amount + '$quantity' FROM 
						 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
		}
		else {
			if($for_corporation) {
				$query = "INSERT INTO corporation_product VALUES('$corporation_id', '$product_id', '$quantity')";
			}
			else {
				$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$quantity')";
			}
		}
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['products_in_the_quantity_of'] . " $quantity " . $lang['successfully_derived'],
							   "quantity"=>$quantity
							  ));
	}
	else if ($action == 'invest_resource') {
		if(!is_numeric($quantity) || $quantity < 0.01) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_more_than_or_equal_to'] . " 0.01"
								  )));
		}
		$quantity = round($quantity, 2);
		
		//test product
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product doesn't exist"
								  )));
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product doesn't exist"
								  )));
		}
		
		//test if product is required
		$query = "SELECT required_id FROM product_product WHERE required_id = '$product_id' AND building_id = 
				 (SELECT building_id FROM companies WHERE company_id = '$company_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product you're trying to invest doesn't exist in the company"
								  )));
		}

		//check if user have resources
		if($for_corporation) {
			$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		}
		else {
			$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($amount) = $row;
		if($amount < $quantity) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_dont_have_enough_products']
								  )));
		}
		
		//check if company have enough space in resource warehouse
		$query = "SELECT SUM(amount) AS total FROM resource_warehouse WHERE company_id = '$company_id' 
				  HAVING (SUM(amount) + '$quantity') <= (SELECT resource_ware FROM companies WHERE company_id = '$company_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['not_enough_space_in_the_companys_resource_warehouse']
								  )));
		}
	
		//update resource warehouse
		$query = "UPDATE resource_warehouse SET amount = (SELECT amount + '$quantity' FROM 
				 (SELECT amount FROM resource_warehouse WHERE company_id = '$company_id'  AND product_id = '$product_id') AS temp) 
				  WHERE company_id = '$company_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		if($for_corporation) {
			$query = "UPDATE corporation_product SET amount = (SELECT amount - '$quantity' FROM 
					 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
					  AND product_id = '$product_id') AS temp) 
					  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		}
		else {
			$query = "UPDATE user_product SET amount = (SELECT amount - '$quantity' FROM 
					 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		$conn->query($query);		

		echo json_encode(array("success"=>true,
							   "msg"=>$lang['products_in_the_quantity_of'] . " $quantity " . $lang['successfully_invested'],
							   "quantity"=>$quantity
							  ));
	}
	else if ($action == 'invest_n_resources') {
		if(!is_numeric($quantity) || $quantity < 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_a_whole_number']
								  )));
		}
		
		//test product
		if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['quantity_must_be_a_whole_number']
								  )));
		}
		
		//get required products
		$total_required = 0;
		$required_resources = array();
		$query = "SELECT required_id, amount, product_name FROM product_product pp, product_info pi WHERE building_id = 
				 (SELECT building_id FROM companies WHERE company_id = '$company_id')
				  AND pi.product_id = required_id";
		$result_req = $conn->query($query);
		$x = 0;
		while($row_req = $result_req->fetch_row()) {
			list($product_id, $required_amount, $product_name) = $row_req;
			$req_resources = $required_amount * $quantity;
			$total_required += $req_resources;
			
			//check if user have resources
			if($for_corporation) {
				$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
			}
			else {
				$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($amount) = $row;
			if($amount < $req_resources) {
				$need = round(ceil(($req_resources - $amount) * 100), 2) / 100;
				
				exit(json_encode(array("success"=>false,
									   "error"=>$lang['you_dont_have_enough'] . " $product_name. " . $lang['you_need'] . 
												" $need " . $lang['more_items']
									  )));
			}
			$required_resources[$x]['product_id'] = $product_id;
			$required_resources[$x]['req_resources'] = $req_resources;
			$x++;
		}
		$length = $x;
		//check if company have enough space in resource warehouse
		$query = "SELECT SUM(amount) AS total FROM resource_warehouse WHERE company_id = '$company_id' 
				  HAVING (SUM(amount) + '$total_required') <= (SELECT resource_ware FROM companies WHERE company_id = '$company_id')";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['not_enough_space_in_the_companys_resource_warehouse']
								  )));
		}

		//update resource warehouse
		$summary = array();
		for($x = 0; $x < $length; $x++) {
			$req_resources = $required_resources[$x]['req_resources'];
			$product_id = $required_resources[$x]['product_id'];
			
			$query = "UPDATE resource_warehouse SET amount = (SELECT amount + '$req_resources' FROM 
					 (SELECT amount FROM resource_warehouse WHERE company_id = '$company_id' AND product_id = '$product_id') AS temp) 
					  WHERE company_id = '$company_id' AND product_id = '$product_id'";
			$conn->query($query);
			
			if($for_corporation) {
				$query = "UPDATE corporation_product SET amount = (SELECT amount - '$req_resources' FROM 
						 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
						  AND product_id = '$product_id') AS temp) 
						  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT amount - '$req_resources' FROM 
						 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			$conn->query($query);
			
			array_push($summary, array("product_id"=>$product_id, "amount"=>$req_resources));
		}

		echo json_encode(array("success"=>true,
							   "msg"=>$lang['products_successfully_invested'],
							   "summary"=>$summary,
							   "total"=>$total_required
							  ));
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							  )));
	}
?>