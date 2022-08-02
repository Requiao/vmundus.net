<?php
	//Description: Repair/build roads.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$region_id =  htmlentities(stripslashes(trim($_POST['region_id'])), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(trim($_POST['product_id'])), ENT_QUOTES);
	$use_amount =  htmlentities(stripslashes(trim($_POST['amount'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
		exit(json_encode(array("success"=>false,
							   "error"=>"Region doesn't exists."
							  )));
	}
	
	$query = "SELECT country_id FROM regions WHERE region_id = '$region_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array("success"=>false,
							   "error"=>"Region doesn't exists."
							  )));
	}
	$row = $result->fetch_row();
	list($country_id) = $row;

	//check if governor
	//check if president
	$is_governor = false;
	$query = "SELECT position_id FROM country_government WHERE user_id = '$user_id' AND country_id = '$country_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$is_governor = true;
		$row = $result->fetch_row();
		list($position_id) = $row;
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"You're not a governor of this country and is not allowed to perform this action."
							  )));
	}
	
	//check if under attack
	$query = "SELECT * FROM regions WHERE region_id = '$region_id' 
			  AND region_id NOT IN (SELECT region_id FROM battles WHERE active = TRUE AND region_id = '$region_id')";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array("success"=>false,
							   "error"=>"Region is under attack. You cannot perform this action."
							  )));
	}

	//check if has rights to perform this action
	$manage_region = 28;
	$query = "SELECT * FROM government_country_responsibilities 
			  WHERE country_id = '$country_id' AND responsibility_id = '$manage_region' 
			  AND position_id = '$position_id' AND have_access = TRUE";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array("success"=>false,
							   "error"=>"You are not allowed to perform this action. You don't have appropriate permission."
							  )));
	}
	
	if($action == 'get_make_core_info') {
		//check if not already core
		$query = "SELECT * FROM country_core_regions ccr 
				  WHERE ccr.country_id = '$country_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"This region is already your core."
			)));
		}
		
		//check if not already making core
		$query = "SELECT hours_left, hours FROM core_creation, make_core_requirements 
				  WHERE region_id = '$region_id' AND country_id = '$country_id' 
				  AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Core creation is already in progress."
			)));
		}

		//get requirements
		$query = "SELECT gold_amount, add_per_region, hours FROM make_core_requirements";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($gold_amount, $add_per_region, $hours) = $row;

		//get total regions
		$query = "SELECT COUNT(*) FROM country_core_regions WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_core_regions) = $row;

		$total_cost = ($total_core_regions * $add_per_region) + $gold_amount;

		echo json_encode(array(
			'success'=>true,
			'msg'=>"It will cost $total_cost Gold to make this region your core" .
							 " and it will take $hours hours. Continue?"
		));
	}
	else if($action == 'make_core') {
		//check if not already core
		$query = "SELECT * FROM country_core_regions ccr 
				  WHERE ccr.country_id = '$country_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"This region is already your core."
			)));
		}

		//check if not already making core
		$query = "SELECT hours_left, hours FROM core_creation, make_core_requirements 
				  WHERE region_id = '$region_id' AND country_id = '$country_id' 
				  AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Core creation is already in progress."
			)));
		}

		//get requirements
		$query = "SELECT gold_amount, add_per_region, hours FROM make_core_requirements";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($gold_amount, $add_per_region, $hours) = $row;

		//get total regions
		$query = "SELECT COUNT(*) FROM country_core_regions WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_core_regions) = $row;

		$total_cost = ($total_core_regions * $add_per_region) + $gold_amount;

		//check if enough gold in the ministry
		$query = "SELECT amount FROM ministry_product
				  WHERE country_id = '$country_id' AND product_id = 1
				  AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"There is no gold in the ministry's warehouse."
			)));
		}				
		$row = $result->fetch_row();
		list($available_gold) = $row;
		
		if($available_gold < $total_cost) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"There is not enough gold in the ministry's warehouse."
			)));
		}

		//make core
		$query = "INSERT INTO core_creation VALUES ('$region_id', '$country_id', '$hours', 
				 '$total_cost', CURRENT_DATE, CURRENT_TIME, TRUE)";
		if($conn->query($query)) {
			//get gold
			$query = "UPDATE ministry_product SET amount = amount - '$total_cost' 
					  WHERE country_id = '$country_id' AND product_id = '1' 
					  AND position_id = '$position_id'";
			$conn->query($query);

			echo json_encode(array(
				'success'=>true,
				'msg'=>"Region will become your core in $hours hours.",
				'hours_left'=>$hours
			));
		}
	}
	else if($action == 'repair_info') {
		//region defence system info
		$query = "SELECT dci.def_loc_id, rds.strength, dci.strength FROM def_const_info dci, region_defence_systems rds
				  WHERE region_id = '$region_id' AND dci.def_loc_id = rds.def_loc_id";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Region doesn't exists."
			)));
		}
		$row = $result->fetch_row();
		list($def_loc_id, $region_strength, $base_strength) = $row;

		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product doesn't exists."
								  )));
		}
		if($region_strength == $base_strength) {
			exit(json_encode(array("success"=>false,
							   "error"=>"Defence system is not damaged."
							  )));
		}

		//select available products in country to repair.
		$query = "SELECT product_name, mp.amount, rdc.product_id
				  FROM repair_defence_construction rdc, ministry_product mp, product_info pi
				  WHERE country_id = '$country_id' AND rdc.product_id = mp.product_id 
				  AND pi.product_id = rdc.product_id AND region_id = '$region_id' AND mp.amount > 0
				  AND mp.product_id = '$product_id' AND position_id = '$position_id'";
		$result_resources = $conn->query($query);
		if($result_resources->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"There are no products in the country's warehouse."
								  )));
		}				
		$row_r = $result_resources->fetch_row();
		list($product_name, $amount, $product_id) = $row_r;
		$amount = number_format($amount, 0, "", " ");
		
		echo json_encode(array('success'=>true,
							   'product_name'=>$product_name, 
							   'amount'=>$amount, 
							   'product_id'=>$product_id
							  ));
	}
	else if($action == 'repair') {
		//region defence system info
		$query = "SELECT dci.def_loc_id, rds.strength, dci.strength FROM def_const_info dci, region_defence_systems rds
				  WHERE region_id = '$region_id' AND dci.def_loc_id = rds.def_loc_id";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Region doesn't exists."
			)));
		}
		$row = $result->fetch_row();
		list($def_loc_id, $region_strength, $base_strength) = $row;

		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Product doesn't exists."
								  )));
		}
		if($region_strength == $base_strength) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Defence system is not damaged."
							      )));
		}
		if(!filter_var($use_amount, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
							   "error"=>"Amount must be more than 0 and be a whole number."
							  )));
		}
		
		//select available products in country to repair.
		$query = "SELECT mp.amount, rdc.amount, rdc.product_id, 
				 (SELECT SUM(amount) FROM def_const_product WHERE def_loc_id = '$def_loc_id')
				  FROM repair_defence_construction rdc, ministry_product mp
				  WHERE country_id = '$country_id' AND rdc.product_id = mp.product_id 
				  AND region_id = '$region_id' AND mp.amount > 0 AND mp.product_id = '$product_id'
				  AND position_id = '$position_id'";
		$result_resources = $conn->query($query);
		if($result_resources->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"There are no products in the country's warehouse."
								  )));
		}
		$row_r = $result_resources->fetch_row();
		list($available_amount, $required_amount, $product_id, $overall_cons_amount) = $row_r;
		if($use_amount > $required_amount) {
			$use_amount = $required_amount;
		}
		if($available_amount < $use_amount) {
			exit(json_encode(array("success"=>false,
				"error"=>"Not enough products."
		    )));
		}

		$product_strength = $base_strength / $overall_cons_amount;
		$repaired_strength = ceil($product_strength * $use_amount * 100) / 100;
		$region_strength += $repaired_strength;
		if($region_strength > $base_strength) {
			$region_strength = $base_strength;
		}
		
		$query = "UPDATE region_defence_systems SET strength = '$region_strength' WHERE region_id = '$region_id'";
		$conn->query($query);
		
		$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
					WHERE country_id = '$country_id' 
					AND product_id = '$product_id' AND position_id = '$position_id') AS temp) - '$use_amount' 
					WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id'";
		$conn->query($query);
		
		$repaired_all_product = false;
		if($use_amount == $required_amount) {
			$query = "DELETE FROM repair_defence_construction WHERE region_id = '$region_id' AND product_id = '$product_id'";
			$conn->query($query);
			
			$repaired_all_product = true;
		}
		else {
			$query = "UPDATE repair_defence_construction SET amount = (SELECT * FROM (SELECT amount FROM repair_defence_construction 
						WHERE region_id = '$region_id' AND product_id = '$product_id') AS temp) - '$use_amount' 
						WHERE region_id = '$region_id' AND product_id = '$product_id'";
			$conn->query($query);
		}
		
		$strength_percentage = round(($region_strength / $base_strength) * 100, 2);
		
		if($strength_percentage >= 100) {
			$query = "DELETE FROM repair_defence_construction WHERE region_id = '$region_id'";
			$conn->query($query);
		}
		
		$repaired_strength = number_format($repaired_strength, 2, ".", " ");
		$region_strength = number_format($region_strength, 2, ".", " ");
		$base_strength = number_format($base_strength, 0, "", " ");
		$new_required_amount = number_format($required_amount - $use_amount, 0, "", " ");
		
		echo json_encode(array('success'=>true,
								'msg'=>"Defence system repaired. Used $use_amount products. Repaired $repaired_strength strength.", 
								'region_strength'=>$region_strength, 
								'base_strength'=>$base_strength,
								'strength_percentage'=>$strength_percentage,
								'repaired_all_product'=>$repaired_all_product,
								'new_required_amount'=>$new_required_amount
								));
	}
	else if($action == 'upgrade_info') {
		//check if max level reached
		$query = "SELECT MAX(dci.def_loc_id), rds.def_loc_id 
				  FROM def_const_info dci, region_defence_systems rds WHERE region_id = '$region_id'";
		$result = $conn->query($query);				
		$row = $result->fetch_row();
		list($max_def_loc, $region_def_loc) = $row;
		if($region_def_loc >= $max_def_loc) {
			exit(json_encode(array("success"=>false,
								   "error"=>"The maximum level of defense system reached."
								  )));
		}
		
		//select next level def info
		$query = "SELECT strength, time_min, const_img FROM def_const_info WHERE def_loc_id =
				 (SELECT def_loc_id FROM region_defence_systems WHERE region_id = '$region_id') + 1";
		$result = $conn->query($query);				
		$row = $result->fetch_row();
		list($strength, $time_min, $const_img) = $row;
		
		$reply = array("success"=>true,
					   "def_info"=>array("strength"=>number_format($strength , 0, "", " "),
										 "time_min"=>number_format($time_min , 0, "", " "),
										 "const_img"=>$const_img
										));
		
		//select required products for upgrade.(- products required for prev def lvl)
		$query = "SELECT product_icon, product_name, dcp.amount - dcp2.amount, dcp.product_id
				  FROM def_const_product dcp, def_const_product dcp2, product_info pi
				  WHERE pi.product_id = dcp.product_id AND dcp.def_loc_id = 
				  (SELECT def_loc_id FROM region_defence_systems WHERE region_id = $region_id) + 1
				  AND  dcp2.product_id = dcp.product_id AND dcp2.def_loc_id = 
				  (SELECT def_loc_id FROM region_defence_systems WHERE region_id = $region_id)";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_icon, $product_name, $amount) = $row;
			array_push($reply, array("product_name"=>$product_name,
									 "icon"=>$product_icon,
									 "amount"=>$amount
									));
		}
		echo json_encode($reply);
	}
	else if($action == 'upgrade') {
		//check if max level reached
		$query = "SELECT MAX(dci.def_loc_id), rds.def_loc_id 
				  FROM def_const_info dci, region_defence_systems rds WHERE region_id = '$region_id'";
		$result = $conn->query($query);				
		$row = $result->fetch_row();
		list($max_def_loc, $region_def_loc) = $row;
		if($region_def_loc >= $max_def_loc) {
			exit(json_encode(array("success"=>false,
								   "error"=>"The maximum level of defense system reached."
								  )));
		}
		
		//check if upgrade in progress
		$query = "SELECT * FROM defence_const_in_process WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Upgrade is already in process."
								  )));
		}
		
		//check if still needs to be repaired
		$query = "SELECT * FROM repair_defence_construction WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"In order to upgrade, defence system must be repaired."
								  )));
		}
		
		//select next level def info
		$query = "SELECT time_min, def_loc_id, (SELECT country_id FROM regions WHERE region_id = '$region_id'),
				  const_img, strength
				  FROM def_const_info WHERE def_loc_id =
				 (SELECT def_loc_id FROM region_defence_systems WHERE region_id = '$region_id') + 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($time_min, $def_loc_id, $country_id, $const_img, $strength) = $row;
		
		//check if enough products
		//works only if warehouse had/have at least 1 product
		/*$query = "SELECT * FROM ministry_product mp, def_const_product dcp, def_const_product dcp2
				  WHERE mp.product_id = dcp.product_id AND dcp.def_loc_id = '$def_loc_id' AND dcp2.product_id = dcp.product_id
				  AND dcp2.def_loc_id = '$def_loc_id' - 1 AND mp.amount < dcp.amount - dcp2.amount
				  AND country_id = '$country_id' AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Not enough products in the ministry warehouse."
								  )));
		}*/

		//determine if user has enough resources
		$query = "SELECT dcp.amount - dcp2.amount, dcp.product_id FROM def_const_product dcp, def_const_product dcp2 WHERE
				  dcp.def_loc_id = '$def_loc_id' + 1 AND dcp2.product_id = dcp.product_id 
				  AND dcp2.def_loc_id = '$def_loc_id'";
		$result_req = $conn->query($query);
		while($row_req = $result_req->fetch_row()) {
			list($req_amount, $product_id) = $row_req;

			$query = "SELECT amount FROM ministry_product 
					  WHERE position_id = '$position_id' AND country_id = '$country_id' AND amount >= '$req_amount' 
					  AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array(
					"success"=>false,
					"error"=>"Not enough products in the ministry warehouse."
				)));
			}
		}
		
		//get product for upgrade
		$query = "SELECT dcp.amount - dcp2.amount, dcp.product_id FROM def_const_product dcp, def_const_product dcp2 WHERE
				  dcp.def_loc_id = '$def_loc_id' + 1 AND dcp2.product_id = dcp.product_id 
				  AND dcp2.def_loc_id = '$def_loc_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($req_amount, $product_id) = $row;
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product WHERE
					  country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) - $req_amount
					  WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id'";
			$conn->query($query);
		}
		
		$query = "INSERT INTO defence_const_in_process VALUES ('$region_id', '$def_loc_id', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
				
		$end_date_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + ' . $time_min . ' minutes'));

		$date1 = new DateTime(date('Y-m-d H:i:s'));
		$date2 = new DateTime($end_date_time);
		$diff = date_diff($date1,$date2);
		
		$days = $diff->format("%a");
		$hours = $diff->format("%h");
		$hours += $days * 24;
		$mins = $diff->format("%i");
		$sec = $diff->format("%s");

		$remaining_time = sprintf('%02d:%02d:%02d', $hours, $mins, $sec);
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Will be upgraded in $time_min minutes.",
							   "end_in"=>$remaining_time,
							   "image"=>$const_img,
							   "strength"=>number_format($strength , 0, "", " "),
							  ));
	}
	else if ($action == 'upgrade_road_info') {
		//check if max level
		$query = "SELECT MAX(rci.road_id), IFNULL((SELECT rr.road_id FROM region_roads rr
				  WHERE rr.region_id = '$region_id'), 0)  FROM road_const_info rci";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_road_level, $region_road_level) = $row;
		if($max_road_level == $region_road_level) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Road cannot be upgraded more"
								  )));
		}
		
		//get information about new road level
		$query = "SELECT rci.road_id, rci.durability, road_img, productivity_bonus FROM road_const_info rci
				  WHERE rci.road_id = '$region_road_level' + 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($road_id, $durability, $road_img, $productivity_bonus) = $row;
		
		//select required products for upgrade.
		$req_products = array();
		$query = "SELECT product_icon, product_name, rcp.amount FROM road_const_product rcp, product_info pi WHERE
				  rcp.road_id = '$road_id' AND pi.product_id = rcp.product_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_icon, $product_name, $amount) = $row;
			array_push($req_products, array("product_name"=>$product_name,
											"icon"=>$product_icon,
											"amount"=>$amount
										   ));
		}
		echo json_encode(array("success"=>true,
							   "road_info"=>array("durability"=>number_format($durability , 0, "", " "),
												  "productivity_bonus"=>$productivity_bonus * 100,
												  "road_level"=>$road_id,
												  "road_img"=>$road_img
												 ),
								"req_products"=>$req_products				 
							   ));
	}
	else if($action == 'upgrade_road') {
		//check if max level
		$query = "SELECT MAX(rci.road_id), IFNULL((SELECT rr.road_id FROM region_roads rr
				  WHERE rr.region_id = '$region_id'), 0)  FROM road_const_info rci";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_road_level, $region_road_level) = $row;
		if($max_road_level == $region_road_level) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Road cannot be upgraded more"
								  )));
		}
		
		//get information about new road level
		$query = "SELECT rci.road_id, rci.durability, road_img, productivity_bonus FROM road_const_info rci
				  WHERE rci.road_id = '$region_road_level' + 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($road_level, $durability, $road_img, $productivity_bonus) = $row;
		
		//check if has enough products in the ministry warehouse
		//works only if warehouse had/have at least 1 product
		/*$query = "SELECT * FROM ministry_product mp, road_const_product rcp
				  WHERE mp.product_id = rcp.product_id AND rcp.road_id = '$road_level'
				  AND mp.amount < rcp.amount
				  AND country_id = '$country_id' AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Not enough products in the ministry warehouse."
								  )));
		}*/

		//determine if user has enough resources
		$query = "SELECT rcp.amount, rcp.product_id FROM road_const_product rcp WHERE
				  rcp.road_id = '$road_level'";
		$result_req = $conn->query($query);
		while($row_req = $result_req->fetch_row()) {
			list($req_amount, $product_id) = $row_req;

			$query = "SELECT amount FROM ministry_product 
					  WHERE position_id = '$position_id' AND country_id = '$country_id' AND amount >= '$req_amount' 
					  AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array(
					"success"=>false,
					"error"=>"Not enough products in the ministry warehouse."
				)));
			}
		}

		//get product for upgrade
		$query = "SELECT rcp.amount, rcp.product_id FROM road_const_product rcp WHERE
				  rcp.road_id = '$road_level'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($req_amount, $product_id) = $row;
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product WHERE
					  country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) - $req_amount
					  WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id'";
			$conn->query($query);
		}
		
		//upgrade road
		if($region_road_level == 0) {
			$query = "INSERT INTO region_roads VALUES ('$region_id', '$road_level', '$durability')";
			$conn->query($query);
			
		}
		else {
			$query = "UPDATE region_roads SET road_id = '$road_level' WHERE region_id = '$region_id'";
			$conn->query($query);
			
			$query = "UPDATE region_roads SET durability = '$durability' WHERE region_id = '$region_id'";
			$conn->query($query);
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Road successfully upgraded.",
							   "road_level"=>$road_level,
							   "image"=>$road_img,
							   "bonus"=>$productivity_bonus * 100 . '%',
							   "durability"=>number_format($durability , 0, "", " "),
							  ));
	}
	else if ($action == 'repair_road_info') {
		//get road details
		$query = "SELECT rr.road_id, rr.durability, rci.durability, repair_durability 
				  FROM region_roads rr, road_const_info rci
				  WHERE rr.region_id = '$region_id' AND rci.road_id = rr.road_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"This region does not have a road to repair."
								  )));
		}
		$row = $result->fetch_row();
		list($road_id, $left_durability, $total_durability, $repair_durability) = $row;
		
		//check if needs repair
		if($left_durability > $total_durability - $repair_durability) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Road in this region does not need to be repaired."
								  )));
		}
		
		//select required products for repair.
		$req_products = array();
		$query = "SELECT product_icon, product_name, rrcp.amount FROM repair_road_const_product rrcp, product_info pi WHERE
				  rrcp.road_id = '$road_id' AND pi.product_id = rrcp.product_id";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_icon, $product_name, $amount) = $row;
			array_push($req_products, array("product_name"=>$product_name,
											"icon"=>$product_icon,
											"amount"=>$amount
										   ));
		}
		echo json_encode(array("success"=>true,
							   "repair_durability"=>number_format($repair_durability , 0, "", " "),
								"req_products"=>$req_products				 
							   ));
	}
	else if($action == 'repair_road') {
		//get road details
		$query = "SELECT rr.road_id, rr.durability, rci.durability, repair_durability 
				  FROM region_roads rr, road_const_info rci
				  WHERE rr.region_id = '$region_id' AND rci.road_id = rr.road_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"This region does not have a road to repair."
								  )));
		}
		$row = $result->fetch_row();
		list($road_id, $left_durability, $total_durability, $repair_durability) = $row;
					
		//check if needs repair
		if($left_durability > $total_durability - $repair_durability) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Road in this region does not need to be repaired."
								  )));
		}
		
		//check if has enough products in the ministry warehouse
		$query = "SELECT * FROM ministry_product mp, repair_road_const_product rrcp
				  WHERE mp.product_id = rrcp.product_id AND rrcp.road_id = '$road_id'
				  AND mp.amount < rrcp.amount
				  AND country_id = '$country_id' AND position_id = '$position_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Not enough products in the ministry warehouse."
								  )));
		}

		//get product for repair
		$query = "SELECT rrcp.amount, rrcp.product_id FROM repair_road_const_product rrcp WHERE
				  rrcp.road_id = '$road_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($req_amount, $product_id) = $row;
			$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product WHERE
					  country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) - $req_amount
					  WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id'";
			$conn->query($query);
		}
		
		//repair road
		$query = "UPDATE region_roads SET durability = durability + '$repair_durability' WHERE region_id = '$region_id'";
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Road successfully repaired.",
							   "durability"=>$left_durability + $repair_durability,
							   "total_durability"=>$total_durability
							  ));
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							  )));
	}
	
	mysqli_close($conn);
?>