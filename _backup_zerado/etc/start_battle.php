<?php
//Description: Start and manage battle.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$region_id =  htmlentities(stripslashes(trim($_POST['region_id'])), ENT_QUOTES);
	$currency_id =  htmlentities(stripslashes(trim($_POST['currency_id'])), ENT_QUOTES);
	$damage_price =  htmlentities(stripslashes(trim($_POST['damage_price'])), ENT_QUOTES);
	$battle_budget =  htmlentities(stripslashes(trim($_POST['battle_budget'])), ENT_QUOTES);
	$target_country_id =  htmlentities(stripslashes(trim($_POST['country_id'])), ENT_QUOTES);
	$platform_id =  htmlentities(stripslashes(trim($_POST['platform_id'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	/* check if governor */
	//check if president
	$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$is_governor = true;
		$row = $result->fetch_row();
		list($position_id, $country_id) = $row;
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
	if($action == 'get_region_list') {
		if(!filter_var($target_country_id, FILTER_VALIDATE_INT)) {
			exit("0|Country doesn't exists.");
		}
		$reply = "true|";
		$query = "SELECT region_id, region_name FROM regions WHERE country_id = '$target_country_id' 
				  AND region_id NOT IN (SELECT region_id FROM battles WHERE active = TRUE)
				  ORDER BY region_name ASC";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($region_id, $region_name) = $row;
			$reply .= "$region_id, $region_name|";
		}
		echo $reply;
	}
	else if($action == 'get_platform_product_amount') {
		if(!filter_var($platform_id, FILTER_VALIDATE_INT)) {
			exit("0|Platform doesn't exists");
		}
		$reply = "true|";
		$query = "SELECT product_id, amount FROM attack_platform_product WHERE platform_id = '$platform_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_id, $amount) = $row;
			$reply .= "$product_id, $amount|";
		}
		echo $reply;
	}
	else if($action == 'start_battle') {
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit("0|Region doesn't exists.");
		}
		if(!filter_var($platform_id, FILTER_VALIDATE_INT)) {
			exit("0|Platform doesn't exists.");
		}
		
		//check budget
		if($battle_budget > 0) {
			if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
				exit("0|Currency doesn't exists.");
			}
			if(!is_numeric($battle_budget)) {
				exit('0|Invalid input for budget. Must be a number.');
			}
			if($battle_budget < 100) {
				exit('0|Input for budget must be more than or equal to 100.');
			}
			if($battle_budget > 5000000) {
				exit('0|Input for budget must be less than or equal to 5 000 000.');
			}
			
			//check damage
			if(!is_numeric($damage_price)) {
				exit('0|Invalid input for damage price. Must be a number.');
			}
			if($damage_price < 1) {
				exit('0|Input for damage price must be more than or equal to 1.');
			}
			if($damage_price > 9999) {
				exit('0|Input for damage price must be more less than or equal to 9 999.');
			}
			
			//check if enough money in the ministry
			//available budget
			$query = "SELECT amount, currency_id FROM ministry_budget
					  WHERE country_id = '$country_id' AND position_id = '$position_id'
					  AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|Your ministry doesn't have this currency type.");
			}
			$row = $result->fetch_row();
			list($ministry_budget, $currency_id) = $row;
			//check if enough ministry budget
			if($battle_budget > $ministry_budget) {
				exit("0|Your ministry doesn't have enough money.");
			}
		}
		
		//check if can attack region. must be at war and neighbor
		$query = "SELECT country_id, region_id, region_name FROM regions WHERE 
				  region_id = '$region_id' AND (country_id = (SELECT with_country_id FROM country_wars WHERE active = TRUE
				  AND is_resistance = FALSE
				  AND country_id = '$country_id' AND with_country_id = (SELECT country_id FROM regions 
				  WHERE region_id = '$region_id'))
				  OR country_id = (SELECT country_id FROM country_wars WHERE active = TRUE AND is_resistance = FALSE
				  AND with_country_id = '$country_id' AND country_id = (SELECT country_id FROM regions 
				  WHERE region_id = '$region_id')))
				  AND region_id IN ((SELECT region_id FROM region_neighbors WHERE neighbor IN 
				 (SELECT region_id FROM regions WHERE country_id = '$country_id') OR neighbor = 
				 (SELECT neighbor FROM region_neighbors WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
				  AND neighbor = 0 LIMIT 1))) AND region_id NOT IN (SELECT region_id FROM battles WHERE active = TRUE)";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit("0|You cannot attack this region.");
		}
		$row = $result->fetch_row();
		list($defender_id, $region_id, $region_name) = $row;
		
		//check if recently had RW
		$block_days = 2;
		$query = "SELECT date_ended, time_ended FROM battles 
				  WHERE DATE_ADD(TIMESTAMP(date_ended, time_ended), INTERVAL '$block_days' DAY) >= NOW()
				  AND war_id IN (SELECT war_id FROM country_wars WHERE is_resistance = TRUE) AND region_id = '$region_id'
				  AND attacker_id = winner_id";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			$row = $result->fetch_row();
			list($date_ended, $time_ended) = $row;
			
			$end_date = date('Y-m-d H:i:s', strtotime("$date_ended $time_ended + $block_days days")); //cannot attack for 1 day
			
			$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
			$date2 = new DateTime($end_date);
			$diff = date_diff($date1,$date2);
			$days = $diff->format("%a");
			$time = $diff->format("%H:%I:%S");
			
			exit("0|Resistance War recently ended in this region. You are not allowed to attack it in the next $time hours.");
		}

		//if battle with this country is still active and battle was started by the attacker. block next attack
		$query = "SELECT * FROM battles WHERE war_id IN (SELECT war_id FROM country_wars WHERE is_resistance = FALSE)
				  AND attacker_id = '$country_id' AND defender_id = '$defender_id' 
				  AND active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit("0|You already attacked this country. You are allowed to attack only" .
				 " one region at a time, that belongs to the attacked country.");
		}
		
		//check if enough product for platform
		$required_array; //hold required product id and quantity
		$counter = 0;
		//select required resources for platform
		$query = "SELECT product_id, amount FROM attack_platform_product
				  WHERE platform_id = '$platform_id' ORDER BY product_id";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit("0|Platform doesn't exists.");
		}
		while($row = $result->fetch_row()) {
			list($product_id, $amount) = $row;
			$required_array[$counter]['product_id'] = $product_id;
			$required_array[$counter]['amount'] = $amount;
			$counter++;
		}
		//select available country products for platform
		$available_array; //hold available product id and quantity
		$counter = 0;
		$query = "SELECT product_id, amount FROM ministry_product WHERE country_id = '$country_id' AND position_id = '$position_id'
				  AND product_id IN (SELECT product_id FROM attack_platform_product WHERE platform_id = '$platform_id') 
				  ORDER BY product_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($available_id, $available_quantity) = $row;
			$available_array[$counter]['available_id'] = $available_id;
			$available_array[$counter]['available_quantity'] = $available_quantity;
			$counter++;
		}
		//determine if enough country products to attack
		for($x = 0; $x < count($required_array); $x++) {
			if($required_array[$x]['product_id'] == $available_array[$x]['available_id'] 
				&& $available_array[$x]['available_quantity'] >= $required_array[$x]['amount']) {
				continue;
			}
			else {
				exit('0|Not enough products in the ministry warehouse.');
			}
		}
		//Get products for platform
		for($x = 0; $x < count($required_array); $x++) {
			$required_amount = $required_array[$x]['amount'];
			$required_product = $required_array[$x]['product_id'];
			
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
					  WHERE country_id = '$country_id' AND product_id = '$required_product' AND position_id = '$position_id') 
					  AS temp) - '$required_amount' 
					  WHERE country_id = '$country_id' AND product_id = '$required_product' AND position_id = '$position_id'";
			$conn->query($query);
		}
		
		//get platform strength
		$query = "SELECT platform_id, strength FROM region_attack_platform WHERE platform_id = '$platform_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($platform_id, $platform_strength) = $row;

		//check if defender is upgrading their defence system.
		$query = "SELECT dci.strength - dci2.strength, dci.time_min, dcip.def_loc_id, start_time, start_date, rds.strength
				  FROM defence_const_in_process dcip, def_const_info dci, def_const_info dci2, region_defence_systems rds
				  WHERE dcip.region_id = '$region_id' AND dcip.def_loc_id = dci.def_loc_id AND rds.region_id = dcip.region_id
				  AND dci2.def_loc_id = dci.def_loc_id - 1";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			$row = $result->fetch_row();
			list($upgrade_strength, $time_min, $def_loc_id, $start_time, $start_date, $current_strength) = $row;
			
			$end_date_time = date('Y-m-d H:i:s', strtotime($start_date . ' ' . $start_time . ' + ' . $time_min . ' minutes'));
	
			$date1 = new DateTime($end_date_time);
			$date2 = new DateTime(date('Y-m-d H:i:s'));
			$diff = date_diff($date1,$date2);
			
			$days = $diff->format("%a");
			$hours = $diff->format("%h");
			$mins = $diff->format("%i") + ($days * 24 * 60) + ($hours * 60);
			$min_complete = $time_min - $mins;
			
			$completed = ((100/$time_min) * $min_complete) / 100;//time in % required to complete
			
			$upgrade_strength = $upgrade_strength * $completed;
			$new_strength = $current_strength + $upgrade_strength;
			
			//stop upgrade. determine how many upgraded, add new strength, return remaining products
			//return product spent for upgrade
			$query = "SELECT dcp.amount - dcp2.amount, dcp.product_id FROM def_const_product dcp, def_const_product dcp2 WHERE
					  dcp.def_loc_id = '$def_loc_id' AND dcp2.product_id = dcp.product_id 
					  AND dcp2.def_loc_id = '$def_loc_id' - 1";
			$result_prod = $conn->query($query);
			while($row_prod = $result_prod->fetch_row()) {
				list($req_amount, $product_id) = $row_prod;
				$req_amount = $req_amount * (1 - $completed);
				
				$query = "SELECT * FROM country_product WHERE country_id = '$defender_id' AND product_id = '$product_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
							  country_id = '$defender_id' AND product_id = '$product_id') AS temp) + $req_amount
							  WHERE country_id = '$defender_id' AND product_id = '$product_id'";
				}
				else {
					$query = "INSERT INTO country_product VALUES ('$defender_id', '$product_id', '$req_amount')";
				}
				$conn->query($query);
			}
			
			$query = "UPDATE region_defence_systems SET def_loc_id = '$def_loc_id' WHERE region_id = '$region_id'";
			$conn->query($query);
			
			$query = "UPDATE region_defence_systems SET strength = '$new_strength' WHERE region_id = '$region_id'";
			$conn->query($query);
			
			$query = "DELETE FROM defence_const_in_process WHERE region_id = '$region_id'";
			$conn->query($query);
		}

		//get defenders position id and strength
		$query = "SELECT def_loc_id, strength FROM region_defence_systems WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($def_loc_id, $position_strength) = $row;

		//get attackers moral
		$attackers_moral = 0;
		$query = "SELECT * FROM country_core_regions ccr 
				  WHERE ccr.country_id = '$country_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'attacking_core'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($attackers_moral) = $row;
		}
		else {
			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'attacking_none_core'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($attacking_none_core_moral) = $row;

			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'max_negative_moral'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($max_negative_moral) = $row;

			$query = "SELECT (COUNT(r.region_id) - COUNT(ccr.region_id)) * me.moral 
					  AS base_none_core_moral
					  FROM moral_effects me, regions r LEFT JOIN country_core_regions ccr 
					  ON ccr.country_id = r.country_id AND r.region_id = ccr.region_id
					  WHERE r.country_id = '$country_id' AND me.effect_name = 'owning_none_core'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($base_none_core_moral) = $row;

			$total_moral = $attacking_none_core_moral + $base_none_core_moral;
			$attackers_moral = $total_moral < $max_negative_moral ? $max_negative_moral : $total_moral;
		}
		if($defender_id == 1000 && $attackers_moral < 0) {
			$attackers_moral = 0;
		}

		//get defenders moral
		$defenders_moral = 0;
		$query = "SELECT * FROM country_core_regions ccr 
				  WHERE ccr.country_id = '$defender_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'defending_core'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($defenders_moral) = $row;
		}
		else {
			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'defending_none_core'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($defending_none_core_moral) = $row;

			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'max_negative_moral'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($max_negative_moral) = $row;

			$query = "SELECT (COUNT(r.region_id) - COUNT(ccr.region_id)) * me.moral 
						AS base_none_core_moral
						FROM moral_effects me, regions r LEFT JOIN country_core_regions ccr 
						ON ccr.country_id = r.country_id AND r.region_id = ccr.region_id
						WHERE r.country_id = '$defender_id' AND me.effect_name = 'owning_none_core'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($base_none_core_moral) = $row;

			$total_moral = $defending_none_core_moral + $base_none_core_moral;
			$defenders_moral = $total_moral < $max_negative_moral ? $max_negative_moral : $total_moral;
		}

		
		//select war id
		$query = "SELECT war_id FROM country_wars WHERE ((country_id = '$country_id' AND with_country_id = '$defender_id') OR
				 (country_id = '$defender_id' AND with_country_id = '$country_id')) AND active = TRUE AND is_resistance = FALSE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($war_id) = $row;
		
		//start battle
		$battle_id = getTimeForId() . $country_id;
		$battle_type = 'regular';
		$is_active = 1;
		$date_eneded = 'NULL';
		$time_eneded = 'NULL';
		$winner_id = 'NULL';
		$attacker_damage = 0;
		$defender_damage = 0;
		
		$query = "INSERT INTO battles VALUES ('$war_id', '$battle_id', '$region_id', '$country_id', 
				 '$defender_id', CURRENT_DATE,  CURRENT_TIME, $date_eneded, $time_eneded, 
				 '$battle_type', TRUE, $winner_id, '$platform_strength', '$platform_id',
				 '$attacker_damage', '$defender_damage', '$position_strength', '$def_loc_id', 
				 '$user_id', '$defenders_moral', '$attackers_moral')";
		$conn->query($query);
		
		//create battle budget
		if($battle_budget > 0) {
			//create battle budget
			$query = "INSERT INTO battle_budget VALUES('$battle_id', '$country_id', '$battle_budget', '$damage_price', 0, '$currency_id')";
			$conn->query($query);

			//update ministry budget
			$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') AS temp) - '$battle_budget' 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id'";
			$conn->query($query);
		}
		
		//notify defenders
		$query = "SELECT user_id FROM user_profile WHERE citizenship = '$defender_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($defenders_user_id) = $row;
			$notification = "$region_name has been attacked! Help to defend your country.";
			sendNotification($notification, $defenders_user_id);
		}
		//stop resistance war preperation
		$query = "SELECT * FROM resistance_war_prep WHERE region_id = '$region_id' AND active = TRUE";
		while($row = $result->fetch_row()) {
			list($war_id) = $row;
			$query = "UPDATE resistance_war_prep SET active = FALSE WHERE war_id = '$war_id'";
			$conn->query($query);
		}

		echo "true|Battle started!";
	}
	
	mysqli_close($conn);
?>