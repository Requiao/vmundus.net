<?php
	//Description : Manage messages(send, delete), notifications...
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$region_id =  htmlentities(stripslashes(strip_tags(trim($_POST['region_id']))), ENT_QUOTES);
	$war_id =  htmlentities(stripslashes(strip_tags(trim($_POST['war_id']))), ENT_QUOTES);
	$join_country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$RATIO = 0.5;//50% resources required for level platform
	
	if($action == "start_revolt") {
		$REVOLT_COST = 5;

		//check if region not under attack
		$query = "SELECT * FROM battles WHERE region_id = '$region_id' AND active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This region is already under attack."
								  )));
		}

		//check country
		if(!filter_var($join_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Country doesn't exist."
			)));
		}
		$query = "SELECT * FROM country WHERE country_id = '$join_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Country doesn't exist."
			)));
		}
		if($join_country_id == 1000) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You look like a human."
			)));
		}

		//check if country doesn't have cores
		$query = "SELECT * FROM country_core_regions WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This country already has a core."
			)));
		}

		//test region
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								'error'=>"Region doesn't exist."
								)));
		}
		$query = "SELECT country_id FROM regions WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Region doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($region_owner) = $row;

		//cannot revolt for the ownership country
		if($region_owner == $join_country_id) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You can only start revolt for countries other than the region owner country."
			)));
		}

		//can only start one revolt
		$query = "SELECT * FROM battles WHERE attacker_id = '$join_country_id' 
				  AND (active = TRUE OR winner_id = attacker_id)";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You can only start one revolt per country."
			)));
		}
		
		//check if enough user gold
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '1'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($available_gold) = $row;
		
		if($available_gold < $REVOLT_COST) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have enough gold"
			)));
		}

		//update user products
		$query = "UPDATE user_product SET amount = amount - '$REVOLT_COST' 
				  WHERE user_id = '$user_id' AND product_id = '1'";
		$conn->query($query);
		
		$platform_id = 1;
		
		$query = "SELECT country_id, region_id, region_name FROM regions WHERE 
				  region_id = '$region_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($defender_id, $region_id, $region_name) = $row;
			
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
				$left_amount = $req_amount * (1 - $completed);
				
				$query = "SELECT * FROM country_product WHERE country_id = '$defender_id' AND product_id = '$product_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
								country_id = '$defender_id' AND product_id = '$product_id') AS temp) + $left_amount
								WHERE country_id = '$defender_id' AND product_id = '$product_id'";
				}
				else {
					$query = "INSERT INTO country_product VALUES ('$defender_id', '$product_id', '$left_amount')";
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
				  WHERE ccr.country_id = '$join_country_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "SELECT moral FROM moral_effects WHERE effect_name = 'revolution'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($attackers_moral) = $row;
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
		
		//start battle
		$war_id = getTimeForId() . $user_id;
	
		$battle_id = getTimeForId() . $join_country_id;
		$battle_type = 'revolt';
		$is_active = 1;
		$date_ended = 'NULL';
		$time_ended = 'NULL';
		$winner_id = 'NULL';
		$attacker_damage = 0;
		$defender_damage = 0;
	
		$query = "INSERT INTO country_wars VALUES ('$war_id', '$join_country_id', '$defender_id', CURRENT_DATE, CURRENT_TIME,
				  TRUE, FALSE)";
		$conn->query($query);
		
		$query = "INSERT INTO battles VALUES ('$war_id', '$battle_id', '$region_id', '$join_country_id', '$defender_id', 
				  CURRENT_DATE, CURRENT_TIME, $date_ended, $time_ended, '$battle_type', TRUE, $winner_id, 
				 '$platform_strength', '$platform_id', '$attacker_damage', '$defender_damage', '$position_strength', 
				 '$def_loc_id', '$user_id', '$defenders_moral', '$attackers_moral')";
		if($conn->query($query)) {
			//notify defenders
			$query = "SELECT user_id FROM user_profile WHERE citizenship = '$defender_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($defenders_user_id) = $row;
				$notification = "Revolt war started in the $region_name!";
				sendNotification($notification, $defenders_user_id);
			}
			
			echo json_encode(array(
				"success"=>true,
				"msg"=>"Battle started!"
			));
		}
	}
	else if($action == 'support_war') {
		//check if region not under attack
		$query = "SELECT * FROM battles WHERE region_id = '$region_id' AND active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This region is already under attack."
								  )));
		}

		//check country
		if(!filter_var($join_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								'error'=>"Country doesn't exist."
								)));
		}
		$query = "SELECT * FROM country WHERE country_id = '$join_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Country doesn't exist."
								  )));
		}

		//test region
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								'error'=>"Region doesn't exist."
								)));
		}
		$query = "SELECT country_id FROM regions WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Region doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($region_owner) = $row;

		//cannot organize for the ownership country
		if($region_owner == $join_country_id) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You can only start resistance to join countries other than the region owner country."
			)));
		}

		//get user's citizenship
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($citizenship) = $row;
		
		if($citizenship != $join_country_id) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You must have the same citizenship of a country that you are supporting in Resistance War."
			)));
		}

		//check if rw is organized
		$query = "SELECT war_id, region_id FROM resistance_war_prep 
				  WHERE join_country_id = '$join_country_id' AND active = TRUE AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows < 1) {//prep tables for RW.
			//check if country has core on this region
			$query = "SELECT * FROM country_core_regions WHERE region_id = '$region_id' AND country_id = '$join_country_id'";
			$result = $conn->query($query);
			if($result->num_rows == 0) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Your country does not have core rights on this region."
				)));
			}

			//organize resistance war
			$war_id = getTimeForId() . $user_id;
			
			$query = "INSERT INTO resistance_war_prep VALUES('$war_id', '$region_id', '$join_country_id', '$user_id', TRUE,
					  CURRENT_DATE, CURRENT_TIME)";
			$conn->query($query);
			
			//populate resistance_war_resources table
			$query = "SELECT product_id FROM attack_platform_product WHERE platform_id = 1";
			$result_prod = $conn->query($query);
			while($row_prod = $result_prod->fetch_row()) {
				list($product_id) = $row_prod;
			
				//check if already populated
				$query = "SELECT * FROM resistance_war_resources WHERE country_id = '$rightful_owner' 
						AND region_id = '$region_id' AND product_id = '$product_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$query = "INSERT INTO resistance_war_resources VALUES('$rightful_owner', '$region_id','$product_id', '0')";
					$conn->query($query);
				}
			}
		}
		
		//select required resources
		$query = "SELECT (app.amount * '0.5') - IFNULL(rwr.amount, 0) AS req_amount, app.product_id, product_name, product_icon 
				  FROM product_info pi, attack_platform_product app LEFT JOIN resistance_war_resources rwr
				  ON rwr.product_id = app.product_id AND country_id = '$join_country_id' AND region_id = '$region_id'
				  WHERE pi.product_id = app.product_id AND platform_id = 1
				  HAVING req_amount > 0 ORDER BY pi.product_id";
		$result = $conn->query($query);
		$products = array();
		while($row = $result->fetch_row()) {
			list($req_amount, $product_id, $product_name, $product_icon) = $row;
			$req_amount = ceil($req_amount * 100) / 100;//ceil important
			
			array_push($products, array("amount"=>$req_amount, "product_id"=>$product_id, "product_name"=>$product_name, 
									   "product_icon"=>$product_icon));
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Support this war with the following resources:",
							   "product"=>$products
							  ));
	}
	else if($action == 'support') {
		//check if region not under attack
		$query = "SELECT * FROM battles WHERE region_id = '$region_id' AND active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This region is already under attack."
								  )));
		}

		//check country
		if(!filter_var($join_country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Country doesn't exist. $join_country_id"
			)));
		}
		$query = "SELECT * FROM country WHERE country_id = '$join_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Country doesn't exist. $join_country_id"
			)));
		}

		//test region
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								'error'=>"Region doesn't exist."
								)));
		}
		$query = "SELECT * FROM regions WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Region doesn't exist."
								  )));
		}
		
		//get user's citizenship
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($citizenship) = $row;
		
		if($citizenship != $join_country_id) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You must have the same citizenship of a country that you are supporting in Resistance War."
			)));
		}
		
		$query = "SELECT war_id, user_id FROM resistance_war_prep 
				  WHERE join_country_id = '$join_country_id' AND active = TRUE AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Resistance War is not organized."
								  )));
		}
		$row = $result->fetch_row();
		list($war_id, $organizer_id) = $row;

		//check if region not under attack
		$query = "SELECT * FROM battles WHERE region_id = '$region_id' AND active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This region is already under attack."
								  )));
		}
		
		//test product
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist."
								  )));
		}
		$query = "SELECT product_id FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist."
							      )));
		}
		
		//check quantity
		if(!is_numeric($quantity) || $quantity < 0.1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Quantity must be more than or equal to 0.1"
								  )));
		}
		$quantity= round($quantity, 1);
		if($quantity > 10000) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Quantity must be less than or equal to 10 000"
								  )));
		}
		
		//select required resources
		$query = "SELECT (app.amount * '0.5') - IFNULL(rwr.amount, 0) AS req_amount, product_name
				  FROM product_info pi, attack_platform_product app LEFT JOIN resistance_war_resources rwr
				  ON rwr.product_id = app.product_id AND country_id = '$join_country_id' AND region_id = '$region_id'
				  WHERE pi.product_id = app.product_id AND platform_id = 1
				  AND app.product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This product is not required. $product_id"
							      )));
		}
		$row = $result->fetch_row();
		list($req_amount, $product_name) = $row;
		$req_amount = ceil($req_amount * 100) / 100;//ceil important
		
		if($quantity > $req_amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Only $req_amount $product_name is required"
							      )));
		}
		
		//check if enough user products
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($available_amount) = $row;
		
		if($quantity > $available_amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You can only support with $available_amount $product_name"
							      )));
		}
		
		//support
		$query = "SELECT * FROM resistance_war_resources WHERE country_id = '$join_country_id' 
				  AND region_id = '$region_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "INSERT INTO resistance_war_resources VALUES ('$join_country_id', '$region_id', 
					  '$product_id', '$quantity')";
		}
		else {
			$query = "UPDATE resistance_war_resources SET amount = amount + '$quantity' 
					  WHERE country_id = '$join_country_id' AND region_id = '$region_id' AND product_id = '$product_id'";
		}
		$conn->query($query);

		//update user products
		$query = "UPDATE user_product SET amount = (SELECT * FROM (SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$quantity' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		//record history
		$query = "INSERT INTO donated_war_resources_history VALUES ('$war_id', '$join_country_id', '$region_id', '$product_id', 
				 '$quantity', '$user_id', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		//summary
		$query = "SELECT SUM(amount) FROM attack_platform_product WHERE platform_id = 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($req_products) = $row;
		$req_products = round($RATIO * $req_products, 2);
		
		//get collected products for platform
		$query = "SELECT IFNULL(SUM(amount), 0) FROM resistance_war_resources WHERE country_id = '$join_country_id' 
				  AND region_id = '$region_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($collected_prods) = $row;
		
		$collected_percentage = round((100 / $req_products) * $collected_prods, 2);
		
		$battle_started = false;
		$msg = "You have successfully supported this war with $quantity $product_name";
		
		//if collected all resources, start battle
		if(($req_products - $collected_prods) <= 0) {
			$platform_id = 1;
			
			$query = "SELECT country_id, region_id, region_name FROM regions WHERE 
					  region_id = '$region_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($defender_id, $region_id, $region_name) = $row;
				
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
					$left_amount = $req_amount * (1 - $completed);
					
					$query = "SELECT * FROM country_product WHERE country_id = '$defender_id' AND product_id = '$product_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
								  country_id = '$defender_id' AND product_id = '$product_id') AS temp) + $left_amount
								  WHERE country_id = '$defender_id' AND product_id = '$product_id'";
					}
					else {
						$query = "INSERT INTO country_product VALUES ('$defender_id', '$product_id', '$left_amount')";
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
					  WHERE ccr.country_id = '$join_country_id' AND region_id = '$region_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "SELECT moral FROM moral_effects WHERE effect_name = 'attacking_core'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($attackers_moral) = $row;
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
			
			//start battle
			$battle_id = getTimeForId() . $join_country_id;
			$battle_type = 'resistance';
			$is_active = 1;
			$date_ended = 'NULL';
			$time_ended = 'NULL';
			$winner_id = 'NULL';
			$attacker_damage = 0;
			$defender_damage = 0;
		
			$query = "INSERT INTO country_wars VALUES ('$war_id', '$join_country_id', '$defender_id', CURRENT_DATE, CURRENT_TIME,
					  TRUE, TRUE)";
			$conn->query($query);
			
			$query = "INSERT INTO battles VALUES ('$war_id', '$battle_id', '$region_id', '$join_country_id', '$defender_id', 
					  CURRENT_DATE, CURRENT_TIME, $date_ended, $time_ended, '$battle_type', TRUE, $winner_id, 
					  '$platform_strength', '$platform_id', '$attacker_damage', '$defender_damage', '$position_strength', 
					  '$def_loc_id', '$organizer_id', '$defenders_moral', '$attackers_moral')";
			if($conn->query($query)) {
				//notify defenders
				$query = "SELECT user_id FROM user_profile WHERE citizenship = '$defender_id'";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($defenders_user_id) = $row;
					$notification = "Resistance war started in the $region_name!";
					sendNotification($notification, $defenders_user_id);
				}
			
				//stop resistance war preperation
				$query = "UPDATE resistance_war_resources SET amount = 0 WHERE region_id = '$region_id' 
						  AND country_id = '$join_country_id'";
				$conn->query($query);
				
				$query = "UPDATE resistance_war_prep SET active = FALSE WHERE war_id = '$war_id'";
				$conn->query($query);
				
				$battle_started = true;
				$msg = "Battle started!";
			}
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>$msg,
							   "amount"=>$req_amount - $quantity,
							   "collected_perc"=>$collected_percentage,
							   "collected_prods"=>$collected_prods,
							   "battle_started"=>$battle_started
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
?>