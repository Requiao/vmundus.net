<?php
	//Description: Buy company
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');

	$company_id =  htmlentities(stripslashes(strip_tags(trim($_POST['company_id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($registration_country) = $row;

	
	//check if company exist
	if(!is_numeric($company_id)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Company doesn't exist $company_id"
							  )));
	}
	$query = "SELECT * FROM company_market WHERE company_id = '$company_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array("success"=>false,
							   "error"=>"This company was removed from the market"
							   )));		
	}
	
	if($action == 'buy_company') {
		//get seller
		$query = "SELECT user_id FROM user_building WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Company doesn't exist."
			)));		
		}
		$row = $result->fetch_row();
		list($seller_id) = $row;
		
		//check if not buying from self
		if($seller_id == $user_id) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You are not allowed to buy from yourself"
								   )));		
		}
		
		//get company registration country
		$query = "SELECT country_id, building_id FROM regions r, companies co WHERE region_id = location
				  AND company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($company_registration_country, $building_id) = $row;
		
		//determine if allowed to build(buy) companies in this country
		if($company_registration_country != $registration_country) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You are not allowed to buy this company in this country"
								   )));		
		}
		
		//get price
		$query = "SELECT price, (SELECT currency_id FROM country WHERE country_id = '$company_registration_country') 
				  FROM company_market WHERE company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($price, $currency_id) = $row;
		
		//determine if buyer has enough money
		$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id' 
				  AND amount > '$price'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
									"error"=>"You don't have enough money"
							)));		
		}
		
		//delete from market
		$query = "DELETE FROM company_market WHERE company_id = '$company_id'";
		$conn->query($query);
		
		//buy
		//update company owner
		$query = "DELETE FROM user_building WHERE company_id = '$company_id'";
		$conn->query($query);

		$query = "INSERT INTO user_building VALUES ('$user_id', '$company_id')";
		$conn->query($query);
		
		//update buyer's currency
		$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
				  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) - '$price' 
				  WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
		$conn->query($query);
		
		//update seller's currency
		$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
				  WHERE user_id = '$seller_id' AND currency_id = '$currency_id') AS temp) + '$price' 
				  WHERE user_id = '$seller_id' AND currency_id = '$currency_id'";
		$conn->query($query);
		
		//record history
		$query = "INSERT INTO company_market_history (company_id, price, buyer_id, seller_id, date, time)
				  VALUES ('$company_id', '$price', '$user_id', '$seller_id',
				  CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg"=>"You have successfully bought this company"
							   ));
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request"
							   )));	
	}
?>