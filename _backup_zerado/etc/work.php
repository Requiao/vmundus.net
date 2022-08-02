<?php
//Description: Work from workplace.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/record_statistics.php');
	include('../php_functions/correct_date_time.php');
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/add_points_to_user_experience.php'); //addPointsToUserExperience($user_id, $points)
	
	$person_id =  htmlentities(stripslashes(strip_tags(trim($_POST['id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if($action == 'work') {
		if(empty($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Select at least one Person."
								  )));
		}
		
		//get bonus per user level
		$query = "SELECT work_bonus FROM bonus_per_user_level";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($USER_LEVEL_BONUS) = $row;
		
		//for missions
		//get timezone id
		$query = "SELECT hours FROM timezones WHERE timezone_id = 
				 (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($hours) = $row;
		$date = date('Y-m-d');	
		$time = date('04:00:00');
		
		$after_four_am = date('Y-m-d H:i:s', strtotime("$date $time -$hours hours"));
		$before_four_am = date('Y-m-d H:i:s', strtotime("$date $time + 1 days -$hours hours"));
		if(strtotime(date('Y-m-d H:i:s', strtotime($after_four_am))) > strtotime(date('Y-m-d H:i:s'))) {
			$after_four_am = date('Y-m-d H:i:s', strtotime("$after_four_am - 1 days"));
			$before_four_am = date('Y-m-d H:i:s', strtotime("$before_four_am - 1 days"));
		}
		else if(strtotime(date('Y-m-d H:i:s', strtotime($before_four_am))) < strtotime(date('Y-m-d H:i:s'))) {
			$after_four_am = date('Y-m-d H:i:s', strtotime("$after_four_am + 1 days"));
			$before_four_am = date('Y-m-d H:i:s', strtotime("$before_four_am + 1 days"));
		}
	
		$work_summary = array();
		$people_array = explode(',', $person_id);
		
		//get data about experience that can be earned
		$query = "SELECT points FROM experience_earnings WHERE experience_id = 1";//person
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($exp_for_person_work) = $row;
		
		$query = "SELECT points FROM experience_earnings WHERE experience_id = 3";//user
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($exp_for_user_work) = $row;
		
		//get citizenship
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($citizenship) = $row;
		
		//run work
		for($i = 0; $i < count($people_array); $i++) {
			$person_id = $people_array[$i];
		
			//check person
			if(!is_numeric($person_id)) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"Person doesn't exist"
												));
				continue;
			}
			
			$query = "SELECT person_name, who FROM people WHERE person_id = '$person_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"Person doesn't exist"
												));
				continue;
			}
			$row = $result->fetch_row();
			list($person_name, $who) = $row;
			
			//check if has working status
			if($who != 'working') {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"$person_name is in the army."
												));
				continue;
			}
			
			//check if hired
			$query = "SELECT company_id, salary FROM hired_workers WHERE person_id = '$person_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"$person_name is not hired anymore by this company"
												));
				continue;
			}
			$row = $result->fetch_row();
			list($company_id, $salary) = $row;
			
			//is enough energy and did not worked
			$query = "SELECT p.energy, experience, worked, ec.energy FROM people p, energy_consumption ec
					  WHERE person_id = '$person_id' AND cons_id = 1";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($energy, $experience, $work_status, $energy_consumption) = $row;
			
			//check if person worked today
			if($work_status == true) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"$person_name already worked today"
												));
				continue;
			}
			
			//check if enough energy
			if($energy < $energy_consumption) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"$person_name doesn't have enough energy"
												));
				continue;
			}
			
			//check if person is still hired and belongs to the right user
			$for_corporation = false;
			$query = "SELECT r.country_id, c.product_ware, pw.amount, tax, production, pw.product_id,
					  ub.user_id, r.country_id, location, product_icon, purpose, currency_abbr, 
					  cu.currency_id, building_id, cycles_worked, workers
					  FROM companies c, regions r, currency cu, user_building ub,
					  product_warehouse pw, income_tax it, product_production pp, product_info pi, country cou
					  WHERE pw.product_id = pi.product_id AND r.region_id = c.location AND it.country_id = r.country_id
					  AND pp.product_id = pi.product_id AND cou.country_id = r.country_id AND pw.company_id = c.company_id
					  AND cou.currency_id = cu.currency_id AND ub.company_id = c.company_id
					  AND c.company_id = '$company_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {//coporation
				$query = "SELECT r.country_id, c.product_ware, pw.amount, tax, production, pw.product_id,
						  cb.corporation_id, r.country_id, location, product_icon, purpose, currency_abbr, 
						  cu.currency_id, building_id, cycles_worked, workers
						  FROM companies c, regions r, currency cu, corporation_building cb,
						  product_warehouse pw, income_tax it, product_production pp, product_info pi, country cou
						  WHERE pw.product_id = pi.product_id AND r.region_id = c.location AND it.country_id = r.country_id
						  AND pp.product_id = pi.product_id AND cou.country_id = r.country_id AND pw.company_id = c.company_id
						  AND cou.currency_id = cu.currency_id AND cb.company_id = c.company_id
						  AND c.company_id = '$company_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					array_push($work_summary, array("success"=>false,
													"person_id"=>$person_id,
													"error"=>"Company doesn't exist where $person_name tries to work"
													));
					continue;
				}
				$for_corporation = true;
			}
			$row = $result->fetch_row();
			list($country_id, $product_ware, $product_fill, $tax, $production, $product_id,
				 $employer_id, $company_country, $company_location, $product_icon, $purpose,
				 $currency_abbr, $currency_id, $building_id, $cycles_worked, $workers) = $row;
		
			//check if company has available working cycles
			if($cycles_worked >= $workers) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"All available working cycles of this company is used for today."
												));
				continue;
			}
			
			//get company owner currency
			if($for_corporation) {
				$query = "SELECT amount FROM corporation_currency WHERE currency_id = '$currency_id'
						  AND corporation_id = '$employer_id' AND amount > '$salary'";
			}
			else {
				$query = "SELECT amount FROM user_currency WHERE currency_id = '$currency_id'
						  AND user_id = '$employer_id' AND amount > '$salary'";
			}
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"The employer doesn't have enough money to pay $person_name"
												));
				continue;
			}
			
			//get person exp bonus
			$query_bonus = "SELECT MAX(bonus) FROM experience WHERE required_exp <= '$experience'";
			$result_bonus = $conn->query($query_bonus);
			$row_bonus = $result_bonus->fetch_row();
			list($persons_bonus_perc) = $row_bonus;
			$persons_bonus = round($production * ($persons_bonus_perc / 100), 2);
			
			//get country productivity bonus
			$country_bonus = 0;
			$country_bonus_perc = 0;
			$get_bonus = true;
			if($purpose == 1) {//1 - resources.
				//check if company located in citizenship country
				if($country_id != $citizenship) {
					//check if country has build company agreement
					$query = "SELECT * FROM foreign_building_policy
							  WHERE building_id = '$building_id' AND foreigners = TRUE
							  AND country_id = '$country_id' AND foreign_country = '$citizenship'";
					$result = $conn->query($query);
					if($result->num_rows == 0) {		  
						$get_bonus = false;
					}
				}
				if($get_bonus) {
					$query_bonus = "SELECT IFNULL(SUM(bonus), 0) FROM region_resource_bonus
									WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
									AND product_id = '$product_id'";
					$result_bonus = $conn->query($query_bonus);
					$row_bonus = $result_bonus->fetch_row();
					list($country_bonus_perc) = $row_bonus;
					if($country_bonus_perc > 50) {
						$country_bonus_perc = 50;
					}
					$country_bonus = round($production * ($country_bonus_perc / 100), 2);
				}
			}
			
			//get user level bonus
			$query_bonus = "SELECT IFNULL(level_id, 0) * $USER_LEVEL_BONUS FROM user_profile up LEFT JOIN user_exp_levels uxl 
							ON uxl.experience <= up.experience WHERE up.user_id = '$user_id'
							ORDER BY level_id DESC LIMIT 1";
			$result_bonus = $conn->query($query_bonus);
			$row_bonus = $result_bonus->fetch_row();
			list($user_bonus_perc) = $row_bonus;
			$user_bonus = round($production * ($user_bonus_perc / 100), 2);
			
			//get road bonus
			$query = "SELECT productivity_bonus, rr.durability FROM road_const_info rcf, region_roads rr 
					  WHERE rcf.road_id = rr.road_id AND region_id = '$company_location'";
			$result_bonus = $conn->query($query);
			$row_bonus = $result_bonus->fetch_row();
			list($road_bonus_perc, $road_durability_left) = $row_bonus;
			$road_used = false;
			$road_bonus = 0;
			if($road_durability_left > 0) {
				$road_bonus = round($production * $road_bonus_perc, 2);
				$road_used = true;
			}
			
			//total productivity with bonus
			$productivity = $production + $persons_bonus + $country_bonus + $user_bonus + $road_bonus;

			//check if enough space in product warehouse
			if(($productivity + $product_fill) > $product_ware) {
				array_push($work_summary, array("success"=>false,
												"person_id"=>$person_id,
												"error"=>"Not enough space in the company's product warehouse where $person_name works"
												));
				continue;
			}

			//check if enough resources
			$required_array = array(); //hold required product id and quantity
			$counter = 0;
			//select required resources for production
			$query = "SELECT required_id, amount FROM product_product
					  WHERE building_id = '$building_id' ORDER BY required_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($required_id, $required_quantity) = $row;
				$required_array[$counter][0] = $required_id;
				$required_array[$counter][1] = $required_quantity;
				$counter++;
			}
			
			//select available company resources for production
			$available_array = array(); //hold available product id and quantity
			$counter = 0;
			$query = "SELECT product_id, amount FROM resource_warehouse WHERE company_id = '$company_id' ORDER BY product_id";
			$result =  $conn->query($query);
			while($row = $result->fetch_row()) {
				list($available_id, $available_quantity) = $row;
				$available_array[$counter][0] = $available_id;
				$available_array[$counter][1] = $available_quantity;
				$counter++;
			}

			//determine if enough company resources to produce.
			for($x = 0; $x < count($required_array); $x++) {
				if($required_array[$x][0] == $available_array[$x][0] && $available_array[$x][1] >= $required_array[$x][1]){
					continue;
				}
				else {
					array_push($work_summary, array("success"=>false,
													"person_id"=>$person_id,
													"error"=>"Not enough resources in the company required for production where" .
													" $person_name works"
													));
					continue 2;
				}
			}
			
			//Get resources for production
			$is_energy_used = false;
			$new_amount = 0;
			$required_product = null;
			for($x = 0; $x < count($required_array); $x++) {
				if($required_array[$x][2] == 5 && !$is_energy_used && $available_array[$x][1] >= $required_array[$x][1]) {//use only one type of energy
					$is_energy_used = true;
					$new_amount = $available_array[$x][1] - $required_array[$x][1];
					$required_product = $required_array[$x][0];
				}
				else if($required_array[$x][2] != 5) {
					$new_amount = $available_array[$x][1] - $required_array[$x][1];
					$required_product = $required_array[$x][0];
				}
				
				$query = "UPDATE resource_warehouse SET amount = '$new_amount' WHERE company_id = '$company_id' 
						  AND product_id = '$required_product'";
				$conn->query($query);
			}
			
			//update road durability
			if($road_used) {
				$query = "UPDATE region_roads SET durability = durability - 1 WHERE region_id = '$company_location'";
				$conn->query($query);
			}
			//update used company cycles
			$cycles_worked++;
			$query = "UPDATE companies SET cycles_worked = '$cycles_worked' WHERE company_id = '$company_id'";
			$conn->query($query);

			//pay salary
			//get money from the employer
			if($for_corporation) {
				$query = "UPDATE corporation_currency SET amount = (SELECT amount FROM (SELECT * FROM corporation_currency 
						  WHERE corporation_id = '$employer_id' AND currency_id = '$currency_id') AS old_currency) 
						  - '$salary' WHERE corporation_id = '$employer_id' AND currency_id = '$currency_id'";
			}
			else {
				$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT * FROM user_currency WHERE user_id = '$employer_id' AND
						  currency_id = '$currency_id') AS old_currency) 
						  - '$salary' WHERE user_id = '$employer_id' AND currency_id = '$currency_id'";
			}
			$conn->query($query);
			
			//calculate taxes
			$taxes = $salary * ($tax/100);
			$taxed_salary = $salary - $taxes;
			
			//update country_currency table with taxes
			$query = "SELECT * FROM country_currency WHERE country_id = '$company_country' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
						  WHERE country_id = '$company_country' AND currency_id = '$currency_id') AS temp) + '$taxes' 
						  WHERE country_id = '$company_country' AND currency_id = '$currency_id'";
				$conn->query($query);
			}
			else {
				$query = "INSERT INTO country_currency VALUES('$company_country', '$currency_id', '$taxes')";
				$conn->query($query);
			}
			
			//update country_daily_income statistic
			countryDailyIncome($company_country, $currency_id, $taxes);
			
			//pay worker
			$query = "SELECT * FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			$result =$conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT * FROM user_currency 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS old_currency ) 
						  + '$taxed_salary' WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
			}
			else {
				$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', '$taxed_salary')";
			}
			$conn->query($query);
			
			//set person work_status to worked
			$query = "UPDATE people SET worked = TRUE WHERE person_id = '$person_id'";
			$conn->query($query);
			
			//get energy for work
			$query = "UPDATE people SET energy = (SELECT energy FROM (SELECT * FROM people) AS temp WHERE person_id = '$person_id') - 
					  '$energy_consumption' WHERE person_id = '$person_id'";
			$conn->query($query);
			
			//get production taxes
			$query = "SELECT tax FROM product_production_tax WHERE country_id = '$company_country' AND product_id = '$product_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($production_tax) = $row;
			
			$tax_from_productivity = floor(($productivity * ($production_tax/100)) * 1000) / 1000;
			$taxed_productivity = $productivity - $tax_from_productivity;
			
			//update country_product with tax
			$query = "SELECT * FROM country_product WHERE country_id = '$company_country' AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
						  country_id = '$company_country' AND product_id = '$product_id') AS temp) + $tax_from_productivity
						  WHERE country_id = '$company_country' AND product_id = '$product_id'";
			}
			else {
				$query = "INSERT INTO country_product VALUES ('$company_country', '$product_id', '$tax_from_productivity')";
			}
			$conn->query($query);
			
			//update region product history
			countryDailyProductIncome($company_location, $product_id, $tax_from_productivity);
			
			//update region productivity
			$record = new CountryDailyProductivity($company_location, $product_id, $productivity);
			$record->recordCountryDailyProductivity();
			
			//update product warehouse. add produced product
			$query = "UPDATE product_warehouse SET amount = (SELECT * FROM (SELECT amount FROM product_warehouse 
					  WHERE company_id = '$company_id' AND product_id = '$product_id') AS temp) + '$taxed_productivity'
					  WHERE company_id = '$company_id' AND product_id = '$product_id'";
			$conn->query($query);
			
			//add points to experience
			$query = "SELECT MAX(required_exp) FROM experience";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($max_experience) = $row;
			if($max_experience > $experience) {
				$query = "UPDATE people SET experience = (SELECT experience FROM (SELECT * FROM people) AS temp 
						  WHERE person_id = '$person_id') + '$exp_for_person_work'
						  WHERE person_id = '$person_id'";
				$conn->query($query);
			}
			
			addPointsToUserExperience($user_id, $exp_for_user_work);

			/* daily work mission */
			$mission_id = 1;//work
			
			//get mission max level
			$query = "SELECT mission_level FROM mission_levels WHERE mission_id = '$mission_id'
					  ORDER BY mission_level DESC LIMIT 1";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($max_level) = $row;
			
			$query = "SELECT udm.mission_level, done, date, time, udm.progress, ml.progress
					  FROM user_daily_missions udm, mission_levels ml WHERE user_id = '$user_id' 
					  AND TIMESTAMP(date, time) >= '$after_four_am' AND TIMESTAMP(date, time) < '$before_four_am'
					  AND udm.mission_id = '$mission_id' AND ml.mission_id = udm.mission_id AND ml.mission_level = udm.mission_level
					  ORDER BY mission_level DESC LIMIT 1";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($mission_level, $done, $date, $time, $progress, $req_progress) = $row;
				
				if(!$done && $progress < $req_progress) {
					$progress++;
					$query = "UPDATE user_daily_missions SET progress = '$progress' WHERE mission_id = '$mission_id' 
							  AND user_id = '$user_id' AND mission_level = '$mission_level' AND date = '$date' AND time = '$time'";
					$conn->query($query);
					
					if($progress == $req_progress) {
						$query = "UPDATE user_daily_missions SET done = TRUE WHERE mission_id = '$mission_id' 
								  AND user_id = '$user_id' AND mission_level = '$mission_level' AND date = '$date' AND time = '$time'";
						$conn->query($query);
					}
				}
				else if ($done && $mission_level < $max_level) {
					$mission_level++;//level up
					$progress = 1;
					
					$query = "INSERT INTO user_daily_missions VALUES ('$user_id', '$mission_id', '$mission_level', '$progress', 
							  FALSE, FALSE, CURRENT_DATE, CURRENT_TIME)";
					$conn->query($query);
				}
			}
			else {
				$mission_level = 1;
				$progress = 1;
				
				$query = "INSERT INTO user_daily_missions VALUES ('$user_id', '$mission_id', '$mission_level', '$progress', 
						  FALSE, FALSE, CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
			}
			
			/* add 1 working cycle for the achievement */
			$query = "SELECT quantity FROM work_quantity WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($work_cycles) = $row;
				$work_cycles++;
				
				//check if user received reward for working cycles
				$earned_new_level = false;
				
				$query = "SELECT level_id, working_cycles FROM hard_worker_levels 
						  WHERE working_cycles <= '$work_cycles' ORDER BY level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 0) {
					$query = "SELECT level_id, working_cycles FROM hard_worker_levels WHERE level_id = 1";
					$result = $conn->query($query);
				}
				$row = $result->fetch_row();
				list($next_level, $working_cycles) = $row;
				
				$query = "SELECT level_id FROM user_hard_worker_rewards WHERE user_id = '$user_id' ORDER BY level_id DESC LIMIT 1";
				$result = $conn->query($query);
				if($result->num_rows == 0 && $work_cycles >= $working_cycles) {
					$earned_new_level = true;
				}
				else if($result->num_rows != 0) {
					$row = $result->fetch_row();
					list($current_level) = $row;
					if($next_level > $current_level && $work_cycles >= $working_cycles) {//earned medal
						$earned_new_level = true;
					}
				}
				
				if($earned_new_level) {
					$query = "INSERT INTO user_hard_worker_rewards VALUES ('$user_id', '$next_level')";
					$conn->query($query);
					
					$achievement_id = 3; //Hard Worker;
					$query = "SELECT earned FROM user_achievements WHERE user_id = '$user_id' AND achievement_id = '$achievement_id'";
					$result = $conn->query($query);
					if($result->num_rows == 0) {
						$query = "INSERT INTO user_achievements VALUES ('$user_id', '$achievement_id', '0', '1')";
					}
					else {
						$row = $result->fetch_row();
						list($earned) = $row;
						$earned++;
						$query = "UPDATE user_achievements SET earned = '$earned' WHERE user_id = '$user_id' 
								  AND achievement_id = '$achievement_id'";
					}
					$conn->query($query);
					
					//notify user
					$notification = "Congratulation. You earned Hard Worker medal.";
					sendNotification($notification, $user_id);
				}
			
				$query = "UPDATE work_quantity SET quantity = (SELECT * FROM (SELECT quantity FROM work_quantity WHERE user_id = '$user_id')
						  AS t ) + 1 WHERE user_id = '$user_id'";
			}
			else {
				$query = "INSERT INTO work_quantity VALUES ('$user_id', '1')";
			}
			$conn->query($query);
			
			//summarize. work_journal
			$query = "INSERT INTO work_journal VALUES('$person_id', '$company_id', CURRENT_DATE, CURRENT_TIME, '$productivity')";
			$conn->query($query);
			
			//select day number
			$query = "SELECT MAX(day_number), MAX(date) FROM day_count";
			$result = $conn->query($query);;
			$row = $result->fetch_row();
			list($day_number, $date) = $row;
			$time = date('H:i:s');
			
			//make sure to display right day number
			$server_date_time = strtotime("$date $time - 4 hour");
			$server_date = $date;
			
			$date = date('Y-m-d', $server_date_time);
			$time = date('H:i:s', $server_date_time);
			$user_date = date('Y-m-d', strtotime(correctDate($date, $time)));

			//by -4hours, date might go back one day number
			if(strtotime("$server_date") > strtotime("$date")) {
				$day_number--;
			}
			
			if($user_date > $date) {
				$day_number++;
			}
			else if($user_date < $date) {
				$day_number--;
			}
		
			array_push($work_summary, array("success"=>true,
											"msg"=>"$person_name successfully worked",
											"person_id"=>$person_id,
										    "productivity"=>number_format($productivity, 2, '.', ' '),
										    "product_id"=>$product_id,
										    "product_icon"=>$product_icon,
										    "after_tax_salary"=>number_format($taxed_salary, 2, '.', ' '),
										    "date"=>$date,
										    "time"=>$time,
										    "currency_abbr"=>$currency_abbr,
										    "new_energy"=>$energy - $energy_consumption,
										    "energy_consumption"=>$energy_consumption,
										    "taxes"=>number_format($taxes, 2, '.', ' '),
										    "salary"=>number_format($salary, 2, '.', ' '),
										    "day_number"=>$day_number,
											"base_productivity"=>number_format($production, 2, '.', ' '),
											"persons_bonus"=>number_format($persons_bonus, 2, '.', ' '),
											"persons_bonus_perc"=>number_format($persons_bonus_perc, 2, '.', ' '),
											"country_bonus"=>number_format($country_bonus, 2, '.', ' '),
											"country_bonus_perc"=>number_format($country_bonus_perc, 2, '.', ' '),
											"user_bonus"=>number_format($user_bonus, 2, '.', ' '),
											"user_bonus_perc"=>number_format($user_bonus_perc, 2, '.', ' '),
											"tax_from_productivity"=>number_format($tax_from_productivity, 2, '.', ' '),
											"tax_from_productivity_perc"=>number_format($production_tax, 2, '.', ' '),
											"taxed_productivity"=>number_format($taxed_productivity, 2, '.', ' '),
											"exp_for_user_work"=>$exp_for_user_work,
											"exp_for_person_work"=>$exp_for_person_work,
											"road_bonus_perc"=>number_format($road_bonus_perc * 100, 2, '.', ' '),
											"road_bonus"=>number_format($road_bonus, 2, '.', ' '),
										   ));
		}
		echo json_encode(array("success"=>true,
							   "work_summary"=>$work_summary
							  ));
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request"
							  )));
	}

	mysqli_close($conn);
?>