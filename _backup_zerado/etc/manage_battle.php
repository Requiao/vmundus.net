<?php
//Description: Start and manage battle.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$battle_id =  htmlentities(stripslashes(trim($_POST['battle_id'])), ENT_QUOTES);
	$currency_id =  htmlentities(stripslashes(trim($_POST['currency_id'])), ENT_QUOTES);
	$add_to_budget =  htmlentities(stripslashes(trim($_POST['add_to_budget'])), ENT_QUOTES);
	$new_price =  htmlentities(stripslashes(trim($_POST['new_price'])), ENT_QUOTES);
	$new_budget =  htmlentities(stripslashes(trim($_POST['new_budget'])), ENT_QUOTES);
	$side =  htmlentities(stripslashes(trim($_POST['side'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if(!is_numeric($battle_id)) {
		exit("0|Error.");
	}

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
			exit("0|You're not a governor and not allowed to perform this action.");
		}
	}
	
	//check if has rights to perform this action
	$manage_start_battle_resp = 23;
	$query = "SELECT * FROM government_country_responsibilities 
			  WHERE country_id = '$country_id' AND responsibility_id = '$manage_start_battle_resp' AND position_id = '$position_id'
			  AND have_access = TRUE";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit("0|You are not allowed to perform this action. You don't have appropriate permissions.");
	}

	$reply = '';
	if($action == 'add_budget_info') {
		//battle_info
		$query = "SELECT battle_id, attacker_id, defender_id, region_id FROM battles b
				  WHERE active = TRUE AND (defender_id = '$country_id' OR attacker_id = '$country_id')
				  AND battle_id = '$battle_id' ORDER BY date_started DESC, time_started DESC";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Battle ended.");
		}
		$row = $result->fetch_row();
		list($battle_id, $attacker_id, $defender_id, $region_id) = $row;
		
		//available ministry budget
		$query = "SELECT amount, currency_abbr, mb.currency_id FROM ministry_budget mb, currency cu
				  WHERE cu.currency_id = mb.currency_id AND country_id = '$country_id' AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit("0|Your ministry doesn't have money.");
		}
		while($row = $result->fetch_row()) {
			list($amount, $currency_abbr, $currency_id) = $row;
			$amount = number_format($amount, 2, ".", " ");
			$reply .= "$amount, $currency_abbr, $currency_id|";
		}
		echo "true|$reply";
	}
	else if($action == 'apply_add_budget') {
		//battle_info
		$query = "SELECT battle_id, attacker_id, defender_id, region_id FROM battles b
				  WHERE active = TRUE AND (defender_id = '$country_id' OR attacker_id = '$country_id')
				  AND battle_id = '$battle_id' ORDER BY date_started DESC, time_started DESC";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Battle ended.");
		}
		$row = $result->fetch_row();
		list($battle_id, $attacker_id, $defender_id, $region_id) = $row;
		
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit("0|Error.");
		}
		if(!is_numeric($new_budget)) {
			exit('0|Invalid input for budget. Must be a number.');
		}
		if($new_budget < 100) {
			exit('0|Input for budget must be more than or equal to 100.');
		}
		if($new_budget > 1000000) {
			exit('0|Input for budget must be less than or equal to 1 000 000.');
		}

		if(!is_numeric($new_price)) {
			exit('0|Invalid input for damage price. Must be a number.');
		}
		if($new_price < 1) {
			exit('0|Input for damage price must be more than or equal to 1.');
		}
		if($new_price > 9999) {
			exit('0|Input for damage price must be more less than or equal to 9 999.');
		}
		
		//check current budgets. must be only one
		$query = "SELECT COUNT(*) FROM battle_budget WHERE battle_id = '$battle_id' AND country_id = '$country_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($budgets) = $row;
		if($budgets >= 1) {
			exit("0|You can only have one budget per battle.");
		}
		
		//available budget
		$query = "SELECT amount, currency_abbr, mb.currency_id FROM ministry_budget mb, currency cu
				  WHERE cu.currency_id = mb.currency_id AND country_id = '$country_id' AND position_id = '$position_id'
				  AND mb.currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Your ministry doesn't have this currency type.");
		}
		$row = $result->fetch_row();
		list($ministry_budget, $currency_abbr, $currency_id) = $row;

		//check if enough ministry budget
		if($new_budget > $ministry_budget) {
			exit("0|Your ministry doesn't have enough money.");
		}
			
		//create battle budget
		$query = "INSERT INTO battle_budget VALUES('$battle_id', '$country_id', '$new_budget', '$new_price', 0, '$currency_id')";
		$conn->query($query);
		
		//update ministry budget
		$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
				  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') AS temp) - '$new_budget' 
				  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id'";
		$conn->query($query);
		
		$new_budget = number_format($new_budget, 2, ".", " ");
		$new_price = number_format($new_price, 2, ".", " ");
		
		if($attacker_id == $country_id) {
			$side = 'attacker';
		}
		else if($defender_id == $country_id) {
			$side = 'defender';
		}
		echo "true|Battle budget created.|$new_budget|$currency_abbr|$currency_id|$new_price|$side";
	}
	else if($action == 'edit_budget_info') {
		//battle_info
		$query = "SELECT battle_id, attacker_id, defender_id, region_id FROM battles b
				  WHERE active = TRUE AND (defender_id = '$country_id' OR attacker_id = '$country_id')
				  AND battle_id = '$battle_id' ORDER BY date_started DESC, time_started DESC";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Battle ended.");
		}
		$row = $result->fetch_row();
		list($battle_id, $attacker_id, $defender_id, $region_id) = $row;
		
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit("0|Error.");
		}
		//current budget
		$query = "SELECT budget, damage_price, bb.currency_id, currency_abbr FROM battle_budget bb, currency cu
				  WHERE bb.battle_id = '$battle_id' AND country_id = '$country_id' AND bb.currency_id = '$currency_id' 
				  AND cu.currency_id = bb.currency_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|There is no battle budget with this currency.");
		}
		$row = $result->fetch_row();
		list($budget, $damage_price, $currency_id, $currency_abbr) = $row;
		
		//available ministry budget
		$query = "SELECT amount FROM ministry_budget WHERE country_id = '$country_id' AND position_id = '$position_id' 
				  AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Your ministry doesn't have this currency type.");
		}
		$row = $result->fetch_row();
		list($amount) = $row;
		$budget = number_format($budget, 2, ".", " ");
		$amount = number_format($amount, 2, ".", " ");
		echo "true|$budget|$damage_price|$currency_id|$currency_abbr|$amount";
	}
	else if($action == 'apply_edit_budget') {
		//battle_info
		$query = "SELECT battle_id, attacker_id, defender_id, region_id FROM battles b
				  WHERE active = TRUE AND (defender_id = '$country_id' OR attacker_id = '$country_id')
				  AND battle_id = '$battle_id' ORDER BY date_started DESC, time_started DESC";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Battle ended.");
		}
		$row = $result->fetch_row();
		list($battle_id, $attacker_id, $defender_id, $region_id) = $row;
		
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit("0|Error.");
		}
		if(!empty($add_to_budget)) {
			if(!is_numeric($add_to_budget)) {
				exit('0|Invalid input for add to budget. Must be a number.');
			}
			if($add_to_budget < 100) {
				exit('0|Input for add to budget must be more than or equal to 100.');
			}
			if($add_to_budget > 1000000) {
				exit('0|Input for add to budget must be less than or equal to 1 000 000.');
			}
		}
		else {
			$add_to_budget = 0;
		}
		
		if(!is_numeric($new_price)) {
			exit('0|Invalid input for new damage price. Must be a number.');
		}
		if($new_price < 1) {
			exit('0|Input for new damage price must be more than or equal to 1.');
		}
		if($new_price > 9999) {
			exit('0|Input for new damage price must be more less than or equal to 9 999.');
		}
		
		if($add_to_budget > 0) {
			//current budget
			$query = "SELECT budget, damage_price FROM battle_budget bb
					  WHERE bb.battle_id = '$battle_id' AND country_id = '$country_id' AND bb.currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|There is no battle budget with this currency.");
			}
			$row = $result->fetch_row();
			list($battle_budget, $damage_price) = $row;
			
			//available budget
			$query = "SELECT amount, currency_abbr FROM ministry_budget mb, currency cu
					  WHERE cu.currency_id = mb.currency_id AND country_id = '$country_id' AND position_id = '$position_id'
					  AND mb.currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|Your ministry doesn't have this currency type.");
			}
			$row = $result->fetch_row();
			list($ministry_budget, $currency_abbr) = $row;
			
			//check if overall battle budget will not exceed 9 999 999.
			$overall_budget = $battle_budget + $add_to_budget;
			if($overall_budget > 9999999) {
				exit("0|Maximum budget is 9 999 999.");
			}
			
			//check if enough ministry budget
			if($add_to_budget > $ministry_budget) {
				exit("0|Your ministry doesn't have enough money.");
			}
			
			if($overall_budget != $battle_budget) {
				//update battle budget
				$query = "UPDATE battle_budget SET budget = (SELECT * FROM (SELECT budget FROM battle_budget 
						  WHERE currency_id = '$currency_id' AND battle_id = '$battle_id') AS temp) + '$add_to_budget' 
						  WHERE currency_id = '$currency_id' AND battle_id = '$battle_id'";
				$conn->query($query);
				
				//update ministry budget
				$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') AS temp) - '$add_to_budget' 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id'";
				$conn->query($query);
			}
		}
		//update price for damage
		if($new_price != $damage_price) {
			$query = "UPDATE battle_budget SET damage_price = '$new_price' 
					  WHERE currency_id = '$currency_id' AND battle_id = '$battle_id'";
			$conn->query($query);
		}
		
		if($attacker_id == $country_id) {
			$side = 'attacker';
		}
		else if($defender_id == $country_id) {
			$side = 'defender';
		}
		$overall_budget = number_format($overall_budget, 2, ".", " ");
		
		echo "true|Battle budget updated.|$overall_budget|$currency_abbr|$new_price|$side";
	}
	else if ($action == 'set_order') {
		//battle_info
		$query = "SELECT battle_id, attacker_id, defender_id, region_id FROM battles b
				  WHERE active = TRUE AND battle_id = '$battle_id' 
				  ORDER BY date_started DESC, time_started DESC";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Battle ended.");
		}
		$row = $result->fetch_row();
		list($battle_id, $attacker_id, $defender_id, $region_id) = $row;
		
		//check if for self or ally.
		if($side == 'attacker') {
			$query = "SELECT * FROM defence_agreements WHERE country_id = '$country_id' AND
					  with_country_id = '$attacker_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|You cannot set order for this country.");
			}
			$for_country_id = $attacker_id;
		}
		else if ($side == 'defender') {
			$query = "SELECT * FROM defence_agreements WHERE country_id = '$country_id' AND
					  with_country_id = '$defender_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|You cannot set order for this country.");
			}
			$for_country_id = $defender_id;
		}
		else {
			if($country_id == $defender_id || $country_id == $attacker_id) {
				$for_country_id = $country_id;
			}
			else {
				exit("0|You cannot set order for this country.");
			}
		}
		
		if(!is_numeric($currency_id)) {
			exit("0|Error.");
		}	
		
		//check for battle budget with this currency type
		if($currency_id != 0) {
			$query = "SELECT * FROM battle_budget WHERE battle_id = '$battle_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|Budget with this currency type doesn't exists.");
			}
		}
		else {
			$currency_id = 'NULL';
		}
		
		//set order
		$query = "DELETE FROM country_orders WHERE country_id = '$country_id'";
		$conn->query($query);
		
		$query = "INSERT INTO country_orders VALUES('$country_id', '$for_country_id', '$battle_id', $currency_id)";
		if($conn->query($query)) {
			echo "true|The battle order successfully set.";
		}
	}
	
	mysqli_close($conn);
?>