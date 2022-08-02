<?php
	//Description: Build new house or a clone farm.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/add_new_person.php');//addNewPerson($born_bar, $room, $user_id);
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/cut_long_name.php'); //cutLongName($string, $max_length)
	include('../php_functions/record_statistics.php');
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$building_id =  htmlentities(stripslashes(trim($_POST['id'])), ENT_QUOTES);
	$person_id =  htmlentities(stripslashes(strip_tags(trim($_POST['person_id']))), ENT_QUOTES);
	$person_name =  htmlentities(stripslashes(strip_tags(trim($_POST['person_name']))), ENT_QUOTES);
	$work_exp_lvl =  htmlentities(stripslashes(trim($_POST['work_exp_lvl'])), ENT_QUOTES);
	$combat_exp =  htmlentities(stripslashes(trim($_POST['combat_exp'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	$CLONING_DAYS = 30;//number of days clone incubator lasts
	
	if ($action == 'buy_slot_for_people') {
		//get user slot num and max available slot
		$query = "SELECT MAX(psc.slot_number), ups.slot_number 
				  FROM people_slot_cost psc, user_people_slots ups WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_slot_number, $user_slot_number) = $row;
		if($user_slot_number >= $max_slot_number) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You have reached the maximum slot."
								  )));
		}

		//get slot price
		$query = "SELECT price, slot_number FROM people_slot_cost WHERE slot_number = 
				 $user_slot_number + 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($slot_price, $slot_number) = $row;
		
		//get user gold
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($gold) = $row;
		if($slot_price > $gold) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough gold."
								  )));
		}

		//update users slots
		$query = "UPDATE user_people_slots SET slot_number = '$slot_number' WHERE user_id = '$user_id'";
		if($conn->query($query)) {
			
			//get gold
			$query = "UPDATE user_product SET amount = amount - '$slot_price' 
			WHERE user_id = '$user_id' AND product_id = 1";
			$conn->query($query);

			$reached_max_slot = false;
			if($slot_number >= $max_slot_number) {
				$reached_max_slot = true;
			}
			else {
				$query = "SELECT price FROM people_slot_cost WHERE slot_number = 
						  $slot_number + 1";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($slot_price) = $row;
			}

			echo json_encode(array('success'=>true,
								   'new_slot_price'=>$slot_price,
								   'reached_max_slot'=>$reached_max_slot
								  ));
		}
	}
	else if ($action == 'get_cloning_systems') {
		$query = "SELECT bi.building_id, bi.building_icon, bi.name, growth
				  FROM building_info bi, cloning_farms_born_bar cfbb, building_research br, user_researches ur
				  WHERE cfbb.building_id = bi.building_id
				  AND is_researched = TRUE AND br.building_id = bi.building_id AND br.research_id = ur.research_id
				  AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You haven't researched this technology."
								  )));
		}
		
		$farms = array();
		while($row = $result->fetch_row()) {
			list($building_id, $building_icon, $building_name, $bb_growth) = $row;
			$query_bp = "SELECT product_name, product_icon, amount, bp.product_id FROM product_info pi, building_product bp 
						 WHERE building_id = '$building_id' AND pi.product_id = bp.product_id";
			$result_bp = $conn->query($query_bp);
			$resources = array();
			while($row_bp = mysqli_fetch_row($result_bp)) {
				list($req_product_name, $product_icon, $amount, $product_id) = $row_bp;
				array_push($resources, array("req_product_name"=>$req_product_name, "product_icon"=>$product_icon, 
											 "amount"=>$amount, "product_id"=>$product_id));
			}
			array_push($farms, array("building_icon"=>$building_icon, "building_name"=>$building_name, 
									 "product_name"=>$product_name, "price"=>$price, "currency_abbr"=>$currency_abbr, 
									 "building_id"=>$building_id, "bb_growth"=>$bb_growth, "resources"=>$resources
									));
		}
		
		//get available products
		$query = "SELECT bp.product_id, IFNULL(up.amount, 0) FROM building_product bp LEFT JOIN user_product up
				  ON user_id = '$user_id' AND bp.product_id = up.product_id
				  GROUP BY product_id";
		$result = $conn->query($query);
		$user_product = array();
		while($row = $result->fetch_row()) {
			list($product_id, $amount) = $row;
			array_push($user_product, array("product_id"=>$product_id, "amount"=>$amount));
		}
	
		echo json_encode(array('success'=>true,
							   'farms'=>$farms,
							   'user_product'=>$user_product,
							   'days'=>$CLONING_DAYS
							  ));
	}
	else if($action == 'build_cloning_system') {
		if(!filter_var($building_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>'Building doesn\'t exist.'
								  )));
		}
		$query = "SELECT building_icon, name, growth FROM building_info bi, cloning_farms_born_bar cfbb
				  WHERE bi.building_id = '$building_id' AND cfbb.building_id = bi.building_id";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>'Building doesn\'t exist.'
								  )));
		}
		$row = $result->fetch_row();
		list($building_icon, $building_name, $growth) = $row;
		
		//check if user researched this building
		$query = "SELECT * FROM user_researches WHERE is_researched = TRUE AND research_id = 
				 (SELECT research_id FROM building_research WHERE building_id = '$building_id') 
				  AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>'Your country has not researched yet this technology.'
								  )));
		}
		
		//limit is 5 farms
		$query = "SELECT COUNT(*) FROM users_cloning_farms WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_farms) = $row;
		if($total_farms >= 5) {
			exit(json_encode(array('success'=>false,
								   'error'=>'You can only build 5 cloning farms.'
								  )));
		}
	
		//determine whether user has enough products
		$query = "SELECT amount, product_id FROM building_product WHERE building_id = '$building_id'";
		$result = $conn->query($query);
		$product_req = array();
		$x = 0;
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			$product_req[$x]['amount'] = $amount;
			$product_req[$x]['product_id'] = $product_id;
			$x++;
		}
		
		for($x = 0; $x < count($product_req); $x++) {
			$product_id = $product_req[$x]['product_id']; 
			$amount = $product_req[$x]['amount']; 
			$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND amount >= '$amount' 
					  AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>$lang['you_dont_have_enough_products']
									  )));
			}
		}
		
		//build
		//get products
		for($x = 0; $x < count($product_req); $x++) {
			$product_id = $product_req[$x]['product_id']; 
			$amount = $product_req[$x]['amount']; 
			$query = "UPDATE user_product SET amount = (SELECT amount - '$amount' FROM 
					 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') 
					  AS temp) WHERE user_id = '$user_id' AND product_id = '$product_id'";
			$conn->query($query);
		}
		
		$farm_id = getTimeForId() . $user_id;
		$query = "INSERT INTO users_cloning_farms VALUES($user_id, '$farm_id', '$building_id', '$CLONING_DAYS')";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>'Cloning farm successfully built.',
							   'days'=>$CLONING_DAYS,
							   'growth'=>$growth,
							   'building_name'=>$building_name,
							   'building_icon'=>$building_icon
							  ));
	}
	else if($action == "get_clone_purchase_details") {
		//get details
		$details = array();
		$query = "SELECT person_price, work_exp_price, combat_exp_price FROM person_price";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($person_price, $work_exp_price, $combat_exp_price) = $row;
		
		echo json_encode(array("success"=>true,
							   "person_price"=>number_format($person_price, 3, '.', ' '),
							   "work_exp_price"=>number_format($work_exp_price, 3, '.', ' '),
							   "combat_exp_price"=>number_format($combat_exp_price, 3, '.', ' ')
							  ));
	}
	else if($action == 'purchase_clone') {
		$MAX_WORK_EXP_LVL = 20;
		$MAX_COMBAT_EXP = 500;
		
		strValidate($person_name, 1, 10, 'Person name');
		
		//check work_exp_lvl
		if(!is_numeric($work_exp_lvl)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person's work experience level must be in the range of 1...$MAX_WORK_EXP_LVL"
								  )));
		}
		if($work_exp_lvl < 1 || $work_exp_lvl > $MAX_WORK_EXP_LVL) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person's work experience level must be in the range of 1...$MAX_WORK_EXP_LVL"
								  )));
		}
		
		//check combat_exp
		if(!is_numeric($combat_exp)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person's combat experience must be in the range of 0...$MAX_COMBAT_EXP"
								  )));
		}
		if($combat_exp < 0 || $combat_exp > $MAX_COMBAT_EXP) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person's combat experience must be in the range of 0...$MAX_COMBAT_EXP"
								  )));
		}
		
		//get person prices
		$query = "SELECT person_price, work_exp_price, combat_exp_price FROM person_price";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($person_price, $work_exp_price, $combat_exp_price) = $row; 
		
		//calculate price. lvl 1 $work_exp_price is free
		$total = $person_price + ($work_exp_price * $work_exp_lvl) + ($combat_exp_price * $combat_exp) - $work_exp_price;
		
		//check if user have enough gold
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = 1 AND amount >= '$total'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough gold."
								  )));
		}
		
		//check if has less than available slots
		$query = "SELECT COUNT(*), slot_number FROM people p, user_people_slots ups 
				  WHERE p.user_id = '$user_id' AND ups.user_id = p.user_id";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_people, $user_slot_number) = $row;
		if($total_people >= $user_slot_number) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough available slots for your new Person."
								  )));
		}

		//check if haven't reached daily limit
		$query = "SELECT COUNT(*) FROM people_purchase_history WHERE user_id = '$user_id' AND date = CURRENT_DATE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($bought_today) = $row;
		if($bought_today >= 3) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You can only purchase 3 clones per day."
			)));
		}
		
		//give new person to the user
		//get user details
		$query = "SELECT citizenship, capital FROM user_profile up, country c
				  WHERE c.country_id = up.citizenship AND user_id = '$user_id'";
		$result_usr = $conn->query($query);
		$row_usr = $result_usr->fetch_row();
		list($citizenship, $capital) = $row_usr;
		
		//get skill level
		$query = "SELECT required_exp FROM experience WHERE skill_lvl = '$work_exp_lvl'";
		$result_exp = $conn->query($query);
		$row_exp = $result_exp->fetch_row();
		list($experience) = $row_exp;

		$query = "SELECT required_exp FROM experience WHERE skill_lvl = '$work_exp_lvl' + 1";
		$result_exp = $conn->query($query);
		$row_exp = $result_exp->fetch_row();
		list($next_lvl_req_exp) = $row_exp;
		$work_lvl_bar_width = ($experience / $next_lvl_req_exp) * 100;
		
		//add new person
		$person_id = getTimeForId() . $user_id;
		$query = "INSERT INTO people VALUES('$user_id', '$person_id', '$experience', 'available', 18, 
				  100, '$person_name', 0, '$combat_exp', FALSE, 0)";
		if(!$conn->query($query)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Error. Please contact Admin and try again."
								  )));
		}
		
		//record for statisctics
		countryPeopleBorn($person_id, $citizenship);
		
		//get gold for purchase
		$query = "UPDATE user_product SET amount = (SELECT * FROM (SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '1') AS temp) - '$total' 
				  WHERE user_id = '$user_id' AND product_id = '1'";
		$conn->query($query);
		
		//record history
		$query = "INSERT INTO people_purchase_history VALUES ('$user_id', '$person_id', '$person_price', '$work_exp_lvl',
				  '$work_exp_price', '$combat_exp', '$combat_exp_price', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		//get person_details
		$query = "SELECT person_id, experience, who, p.energy, person_name, years, combat_exp, en.energy, worked
				  FROM people p, energy_consumption en WHERE person_id = '$person_id' AND en.cons_id = 5";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($person_id, $experience, $status, $energy, $person_name, $years, $combat_exp, $max_energy, $worked) = $row;
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Congratulations! You have new person!",
							   "person_id"=>$person_id,
							   "work_exp_lvl"=>$work_exp_lvl,
							   "status"=>$status,
							   "energy"=>$energy,
							   "person_name"=>$person_name,
							   "years"=>$years,
							   "combat_exp"=>$combat_exp,
							   "max_energy"=>$max_energy,
							   "worked"=>$worked,
							   "energy_width"=>($energy/$max_energy) * 100,
							   "work_lvl_bar_width"=>$work_lvl_bar_width
							  ));
	}
	else if($action == 'person_details') {
		if(!is_numeric($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}
	
		$query = "SELECT person_id, person_name, years, MAX(skill_lvl), p.energy, experience, 
				  combat_exp, ec.energy, wound, (SELECT skill_lvl FROM experience ORDER BY skill_lvl DESC LIMIT 1)
				  FROM people p, experience, energy_consumption ec WHERE user_id = '$user_id' 
				  AND person_id = '$person_id' AND required_exp <= experience AND ec.cons_id = 5";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($person_id, $person_name, $years, $skill, $energy, $experience, $combat_exp, 
			 $max_energy, $wound, $max_skill_lvl) = $row;
		
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}
		
		//select location
		if($skill == $max_skill_lvl) {
			$next_skill = $max_skill_lvl;
		}
		else {
			$next_skill = $skill + 1;
		}
		$query = "SELECT required_exp FROM experience e WHERE skill_lvl = '$next_skill'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($required_exp) = $row;

		$person = array("person_id"=>$person_id, "person_name"=>$person_name,
						"years"=>$years, "skill"=>$skill, "energy"=>$energy, "c_flag"=>$c_flag, 
						"c_country_name"=>$c_country_name, "experience"=>$experience, 
						"required_exp"=>$required_exp, "combat_exp"=>$combat_exp, "max_energy"=>$max_energy, "wound"=>$wound);

		//check if hired
		$query = "SELECT * FROM hired_workers WHERE person_id = '$person_id'";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$query = "SELECT user_name, u.user_id, company_name, region_name, salary, time_hired, building_icon, currency_abbr,
					  user_image
					  FROM users u, companies c, hired_workers hw, user_building ub, regions r, building_info bi, 
					  country co, currency cu, user_profile up
					  WHERE u.user_id = ub.user_id AND c.company_id = hw.company_id AND ub.company_id = c.company_id 
					  AND r.region_id = location AND bi.building_id = c.building_id AND person_id = '$person_id'
					  AND co.country_id = r.country_id AND cu.currency_id = co.currency_id AND up.user_id = u.user_id
					  UNION
					  SELECT user_name, u.user_id, company_name, region_name, salary, time_hired, building_icon, currency_abbr,
					  user_image
					  FROM users u, companies c, hired_workers hw, corporation_building cb, regions r, building_info bi, 
					  country co, currency cu, user_profile up, corporations corp
					  WHERE u.user_id = corp.manager_id AND corp.corporation_id = cb.corporation_id AND c.company_id = hw.company_id 
					  AND cb.company_id = c.company_id AND r.region_id = location AND bi.building_id = c.building_id 
					  AND person_id = '$person_id' AND co.country_id = r.country_id AND cu.currency_id = co.currency_id 
					  AND up.user_id = u.user_id";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($employer_name, $employer_id, $company_name, $comp_region_name, $salary, $time_hired, $building_icon, 
				 $currency_abbr, $employer_image) = $row;
			
			//cut user_name if too long
			$user_name = cutLongName($user_name, 7);
			
			$job_info = array("employer_name"=>$employer_name, "employer_id"=>$employer_id, "company_name"=>$company_name, 
							  "comp_region_name"=>$comp_region_name, "salary"=>$salary, "time_hired"=>$time_hired, 
							  "building_icon"=>$building_icon, "currency_abbr"=>$currency_abbr, "employer_image"=>$employer_image);
		}
		else {
			$job_info = false;
		}
		
		echo json_encode(array("success"=>true,
							   "person"=>$person,
							   "job_info"=>$job_info
							  ));
	}
	else if ($action == 'rename_person') {
		if(!is_numeric($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}
		
		strValidate($person_name, 1, 10, 'Person name');
		
		$query = "SELECT person_id, person_name FROM people WHERE user_id = '$user_id' AND person_id = '$person_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person deosn't exist."
								  )));
		}
		
		$query = "UPDATE people SET person_name = '$person_name' WHERE user_id = '$user_id' AND person_id = '$person_id'";
		$conn->query($query);
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['the_person_successfully_renamed'],
							   "person_name"=>$person_name
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>'Invalid request.'
							  )));
	}
?>