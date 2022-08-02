<?php
//Description: Calculate and record user damage.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/record_statistics.php');
	include('../php_functions/add_points_to_user_experience.php'); //addPointsToUserExperience($user_id, $points)
	include('../php_functions/delete_person.php'); ///deletePerson($person_id)
	
	usleep(250000);
	$battle_id =  htmlentities(stripslashes(trim($_POST['battle_id'])), ENT_QUOTES);
	$side =  htmlentities(stripslashes(trim($_POST['side'])), ENT_QUOTES);
	$currency_id =  htmlentities(stripslashes(trim($_POST['currency_id'])), ENT_QUOTES);
	$weapon_id =  htmlentities(stripslashes(trim($_POST['weapon_id'])), ENT_QUOTES);
	$ammo_id =  htmlentities(stripslashes(trim($_POST['ammo_id'])), ENT_QUOTES);
	$armor_id =  htmlentities(stripslashes(trim($_POST['armor_id'])), ENT_QUOTES);
	$person_id =  htmlentities(stripslashes(trim($_POST['person_id'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if($action != "fight" && $action != "refresh") {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							   )));
	}
	
	if(!is_numeric($battle_id)) {
		exit(json_encode(array("success"=>false,
							   "error"=>"Battle doesn't exists.",
							   "ended"=>true
							   )));
	}
	if($action == "fight") {
		if(!is_numeric($currency_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Battle doesn't exists.",
								   "ended"=>true
								   )));
		}
		if($side != "attacker" && $side != "defender") {
			exit(json_encode(array("success"=>false,
								   "error"=>"Battle doesn't exists.",
								   "ended"=>true
								   )));
		}
		if(!is_numeric($weapon_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Weapon doesn't exists."
								   )));
		}
		if(!is_numeric($ammo_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Ammo doesn't exists."
								   )));
		}
		if(!is_numeric($armor_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Armor doesn't exists."
								   )));
		}
		if(!is_numeric($person_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"The soldier is not chosen. Make sure at least one soldier is located in the 
											 attacked country or in the country that is neighboring it."
								   )));
		}
		
		$hits = 1;
	}
	
	//battle info
	$query = "SELECT battle_id, type, region_id, attacker_id, defender_id, attacker_damage, defender_damage,
			  attacker_strength, rap.strength AS 'attacker_fixed_strength', defender_strength,
			  dci.strength AS' defender_fixed_strength', b.def_loc_id, date_started, time_started,
			  defenders_moral, attackers_moral
			  FROM battles b, region_attack_platform rap, def_const_info dci
			  WHERE battle_id = '$battle_id' AND active = TRUE
			  AND rap.platform_id = b.attack_platform_id AND dci.def_loc_id = b.def_loc_id";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array("success"=>false,
							   "error"=>"The battle is over.",
							   "ended"=>true
							   )));
	}
	$row = $result->fetch_row();
	list($battle_id, $battle_type, $attacked_region, $attacker_id, $defender_id, $attacker_damage, $defender_damage,
		 $attacker_strength, $attacker_fixed_strength, $defender_strength, $defender_fixed_strength, 
		 $def_loc_id, $date_started, $time_started, $defenders_moral, $attackers_moral) = $row;
	
	if($action == 'fight') {
		//check if country involved into this war
		if($side == "attacker" && $battle_type != 'revolt') {
			$query = "SELECT * FROM country WHERE (country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') 
					  AND country_id = '$attacker_id')
					  OR ('$defender_id' = 1000)
					  OR (country_id IN (SELECT with_country_id FROM defence_agreements WHERE country_id = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND is_allies = TRUE 
					  AND with_country_id = '$attacker_id'))";
		}
		if($side == "defender") {
			$query = "SELECT * FROM country WHERE (country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') 
					  AND country_id = '$defender_id')
					  OR ('$attacker_id' = 1000)
					  OR (country_id IN (SELECT with_country_id FROM defence_agreements WHERE country_id = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND is_allies = TRUE 
					  AND with_country_id = '$defender_id'))";
		}
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			if($side == "attacker") {
				exit(json_encode(array("success"=>false,
									   "error"=>"Your country is not involved in this war on the attacker's side."
									   )));
			}
			else {
				exit(json_encode(array("success"=>false,
									   "error"=>"Your country is not involved in this war on the defender's side."
									   )));
			}
		}
		
		//get person details
		$query = "SELECT person_id, p.energy, combat_exp, wound, ec.energy AS fight_energy, delete_chance, citizenship, person_name, years
				  FROM people p, energy_consumption ec, wound_delete_percentage wdp, user_profile up
				  WHERE ec.cons_id = 4 AND person_id = '$person_id' AND p.user_id = '$user_id'
				  AND wdp.wound_amount = p.wound AND up.user_id = p.user_id";  
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"This person cannot fight."
								   )));
		}
		$row = $result->fetch_row();
		list($person_id, $energy, $combat_exp, $wound, $energy_consum, $delete_chance, $citizenship, $person_name, $years) = $row;
		$fight_energy = $hits * $energy_consum;
		if($energy < $fight_energy) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Not enough energy."
								   )));
		}
		
		//get data about experience that can be earned
		$query = "SELECT points FROM experience_earnings WHERE experience_id = 2";//person
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($exp_for_person_fight) = $row;
		
		$query = "SELECT points FROM experience_earnings WHERE experience_id = 4";//user
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($exp_for_user_fight) = $row;

		//give x2 exp bonus if attacking alien owned regions
		if($defender_id == 1000 || $attacker_id == 1000) {
			$exp_for_person_fight += 1;
			$exp_for_user_fight += 1;
		}
		
		//chose side
		if($side == 'defender') {
			$country_side_id = $defender_id;
		}
		else if($side == 'attacker') {
			$country_side_id = $attacker_id;
		}
		
		//battle budget
		if($currency_id != 0) {
			$query = "SELECT damage_price, budget - used_budget FROM battle_budget WHERE country_id = '$country_side_id'
					  AND currency_id = '$currency_id' AND battle_id = '$battle_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$currency_id = 0;
				$damage_price = 0;
			}
			else {
				$row = $result->fetch_row();
				list($damage_price, $budget) = $row;
			}
		}
		else {
			$currency_id = 0;
			$damage_price = 0;
		}
		
		//check weapon
		$weapon_damage = 0;
		$req_weapons = 0;
		if($weapon_id != 0) {
			$query = "SELECT amount, damage  FROM weapons_info wi, user_product up  
					  WHERE user_id = '$user_id' AND up.product_id = weapon_id AND weapon_id = '$weapon_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($amount, $weapon_damage) = $row;
				$req_weapons = 0.01 * $hits;
				if($amount < $req_weapons) {
					$weapon_damage = 0;
					$req_weapons = 0;
				}
			}
		}
		
		//check ammo
		$ammo_damage = 0;
		$req_ammo = 0;
		if($ammo_id != 0) {
			$query = "SELECT amount, damage  FROM ammo_info ai, user_product up  
					  WHERE user_id = '$user_id' AND up.product_id = ammo_id AND ammo_id = '$ammo_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($amount, $ammo_damage) = $row;
				$req_ammo = 1 * $hits;
				if($amount < $req_ammo) {
					$ammo_damage = 0;
					$req_ammo = 0;
				}
			}
		}
		
		//check armor
		$armor_defence = 0;
		$req_armor = 0;
		if($armor_id != 0) {
			$query = "SELECT amount, defence  FROM armor_info ai, user_product up  
				  WHERE user_id = '$user_id' AND up.product_id = armor_id AND armor_id = '$armor_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($amount, $armor_defence) = $row;
				$req_armor = 0.01 * $hits;
				if($amount < $req_armor) {
					$armor_defence = 0;
					$req_armor = 0;
				}
			}
		}
		
		//get user level bonus
		//get bonus per user level
		$query = "SELECT millitary_bonus FROM bonus_per_user_level";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($USER_LEVEL_BONUS) = $row;
		
		$query_bonus = "SELECT IFNULL(level_id, 0) * $USER_LEVEL_BONUS FROM user_profile up LEFT JOIN user_exp_levels uxl 
						ON uxl.experience <= up.experience WHERE up.user_id = '$user_id'
						ORDER BY level_id DESC LIMIT 1";
		$result_bonus = $conn->query($query_bonus);
		$row_bonus = $result_bonus->fetch_row();
		list($user_bonus) = $row_bonus;
		
		//calculate damage
		$person_base_damage = 2;
		$damage = 0;
		$user_exp_earn = 0;
		for($u = 1; $u <= $hits; $u++) {
			$person_damage = $person_base_damage + ($combat_exp / 100);
			$damage += $person_damage + $weapon_damage + $ammo_damage + $user_bonus;
			$bonus_to_damage = 0;
			if($side == "attacker") {
				$bonus_to_damage = ($attackers_moral / 100) * $damage;
			}
			else if ($side == "defender") {
				$bonus_to_damage = ($defenders_moral / 100) * $damage;
			}
			$damage += $bonus_to_damage;
			$combat_exp += $exp_for_person_fight;
			$user_exp_earn += $exp_for_user_fight;
		
			//chance to wound
			$is_wounded = false;
			$wound_chance = 3 - $armor_defence;
			$index = mt_rand(1,100);
			for($x = 0; $x < $wound_chance; $x++) {
				if($index == mt_rand(1,100)) {//wounded
					$query = "UPDATE people SET wound = 
							 (SELECT * FROM (SELECT wound FROM people WHERE person_id = '$person_id') AS temp) 
							  + 1 WHERE person_id = '$person_id'";
					$conn->query($query);

					$wound++;
					$is_wounded = true;
					
					break;
				}
			}

			//chance to die
			$is_deleted = false;
			$delete_chance = $delete_chance - $armor_defence;
			$index = mt_rand(1,100);
			for($x = 0; $x < $delete_chance; $x++) {
				if($index == mt_rand(1,100)) {//deleted
					deletePerson($person_id);
					
					//record for statisctics
					countryPeopleDie($person_id, $citizenship, $years);
					
					//people losses. check if recorded before
					$query = "SELECT * FROM person_battle_losses WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
							  AND for_country_id = '$country_side_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$query = "UPDATE person_battle_losses SET amount = (SELECT * FROM (SELECT amount FROM person_battle_losses
								  WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
								  AND for_country_id = '$country_side_id') AS temp) + 1 WHERE
								  battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
								  AND for_country_id = '$country_side_id'";
						$conn->query($query);
					}
					else {
						$query = "INSERT INTO person_battle_losses VALUES ('$battle_id', '$user_id', '$citizenship', '$country_side_id', '1')";
						$conn->query($query);
					}
					
					$is_deleted = true;
					break 2;
				}
			}
			
			if($is_wounded) {//in order to prevent upredictable wounds/deletions.
				$fight_energy = $u * $energy_consum;
				
				if($req_armor > 0) {
					$req_armor = 0.01 * $u;
				}
				
				if($req_ammo > 0) {
					$req_ammo = 1 * $u;
				}
				
				if($req_weapons > 0) {
					$req_weapons = 0.01 * $u;
				}
				
				break;
			}
		}
		
		//reward for fighting against aliens
		$gold_reward = 0;
		if(($defender_id == 1000 && $side == 'attacker') || ($attacker_id == 1000 && $side == 'defender')) {
			$BASE_GOLD_REWARD = 0.001;
			$gold_reward = floor(($BASE_GOLD_REWARD * $damage) * 1000) / 1000;

			$query = "UPDATE user_product SET amount = amount + '$gold_reward' 
					  WHERE user_id = '$user_id' AND product_id = '1'";
			$conn->query($query);
		}

		//DONE CALCULATIONS
		//add to person combat exp
		$query = "UPDATE people SET combat_exp = '$combat_exp' WHERE person_id = '$person_id'";
		$conn->query($query);
		
		
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
		
		/* daily fight mission */
		$mission_id = 2;//fight
	
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
				$progress += $damage;
				if($progress > $req_progress) {
					$damage = $progress - $req_progress;
					$progress = $req_progress;
					$done = true;
				}
			
				$query = "UPDATE user_daily_missions SET progress = '$progress' WHERE mission_id = '$mission_id' 
						  AND user_id = '$user_id' AND mission_level = '$mission_level' AND date = '$date' AND time = '$time'";
				$conn->query($query);
				
				if($progress == $req_progress) {
					$query = "UPDATE user_daily_missions SET done = TRUE WHERE mission_id = '$mission_id' 
							  AND user_id = '$user_id' AND mission_level = '$mission_level' AND date = '$date' AND time = '$time'";
					$conn->query($query);
				}
			}
			
			if ($done && $mission_level < $max_level) {
				$mission_level++;//level up
				$progress = $damage;
				
				$query = "INSERT INTO user_daily_missions VALUES ('$user_id', '$mission_id', '$mission_level', '$progress', 
						  FALSE, FALSE, CURRENT_DATE, CURRENT_TIME)";
				$conn->query($query);
			}
		}
		else {
			$mission_level = 1;
			$progress = $damage;
			
			$query = "INSERT INTO user_daily_missions VALUES ('$user_id', '$mission_id', '$mission_level', '$progress', 
					  FALSE, FALSE, CURRENT_DATE, CURRENT_TIME)";
			$conn->query($query);
		}
		
		addPointsToUserExperience($user_id, $user_exp_earn);
		
		/* battle achievement */
		$query = "SELECT damage FROM user_total_damage WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($ba_damage) = $row;
			$ba_damage += $damage;
			
			//check if user received reward for damage
			$earned_new_level = false;
			
			$query = "SELECT level_id, total_damage FROM battle_legend_levels 
					  WHERE total_damage <= '$ba_damage' ORDER BY level_id DESC LIMIT 1";
			$result = $conn->query($query);
			if($result->num_rows == 0) {
				$query = "SELECT level_id, total_damage FROM battle_legend_levels WHERE level_id = 1";
				$result = $conn->query($query);
			}
			$row = $result->fetch_row();
			list($next_level, $ba_total_damage) = $row;
			
			$query = "SELECT level_id FROM user_battle_legend_rewards WHERE user_id = '$user_id' ORDER BY level_id DESC LIMIT 1";
			$result = $conn->query($query);
			if($result->num_rows == 0 && $ba_damage >= $ba_total_damage) {
				$earned_new_level = true;
			}
			else if ($result->num_rows != 0){
				$row = $result->fetch_row();
				list($current_level) = $row;
				if($next_level > $current_level && $ba_damage >= $ba_total_damage) {//earned medal
					$earned_new_level = true;
				}
			}
			
			if($earned_new_level) {
				//if made damage for more than 1 medal.
				$earned_levels = $next_level - $current_level;
				$next_level -= $earned_levels;
				for($u = 0; $u < $earned_levels; $u++) {
					$next_level++;
					
					$query = "INSERT INTO user_battle_legend_rewards VALUES ('$user_id', '$next_level')";
					$conn->query($query);
					
					$achievement_id = 2; //Battle Legend
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
					$notification = "Congratulation. You earned Battle Legend medal.";
					sendNotification($notification, $user_id);
				}
			}
		
			$query = "UPDATE user_total_damage SET damage = (SELECT * FROM (SELECT damage FROM user_total_damage
					  WHERE user_id = '$user_id')
					  AS t ) + '$damage' WHERE user_id = '$user_id'";
		}
		else {
			$query = "INSERT INTO user_total_damage VALUES ('$user_id', '$damage')";
		}
		$conn->query($query);
		
		//update user product table and battle uses
		if($req_weapons > 0) {
			$query = "UPDATE user_product SET amount = (SELECT * FROM (SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$weapon_id') AS temp) - '$req_weapons' 
					  WHERE user_id = '$user_id' AND product_id = '$weapon_id'";
			$conn->query($query);
			
			//check if recorded before
			$query = "SELECT * FROM user_battle_uses WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
					  AND for_country_id = '$country_side_id' AND product_id = '$weapon_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE user_battle_uses SET amount = (SELECT * FROM (SELECT amount FROM user_battle_uses
						  WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
						  AND for_country_id = '$country_side_id' AND product_id = '$weapon_id') AS temp) + '$req_weapons' WHERE
						  battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
						  AND for_country_id = '$country_side_id' AND product_id = '$weapon_id'";
				$conn->query($query);
			}
			else {
				$query = "INSERT INTO user_battle_uses VALUES ('$battle_id', '$user_id', '$citizenship', '$country_side_id', 
						 '$weapon_id', '$req_weapons')";
				$conn->query($query);
			}
		}
		
		if($req_ammo > 0) {
			$query = "UPDATE user_product SET amount = (SELECT * FROM (SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$ammo_id') AS temp) - '$req_ammo'
					  WHERE user_id = '$user_id' AND product_id = '$ammo_id'";
			$conn->query($query);
			
			//check if recorded before
			$query = "SELECT * FROM user_battle_uses WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
					  AND for_country_id = '$country_side_id' AND product_id = '$ammo_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE user_battle_uses SET amount = (SELECT * FROM (SELECT amount FROM user_battle_uses
						  WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
						  AND for_country_id = '$country_side_id' AND product_id = '$ammo_id') AS temp) + '$req_ammo' WHERE
						  battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
						  AND for_country_id = '$country_side_id' AND product_id = '$ammo_id'";
				$conn->query($query);
			}
			else {
				$query = "INSERT INTO user_battle_uses VALUES ('$battle_id', '$user_id', '$citizenship', '$country_side_id', 
						 '$ammo_id', '$req_ammo')";
				$conn->query($query);
			}
		}
		
		if($req_armor > 0) {
			$query = "UPDATE user_product SET amount = (SELECT * FROM (SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$armor_id') AS temp) - '$req_armor'
					  WHERE user_id = '$user_id' AND product_id = '$armor_id'";
			$conn->query($query);
			
			//check if recorded before
			$query = "SELECT * FROM user_battle_uses WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
					  AND for_country_id = '$country_side_id' AND product_id = '$armor_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE user_battle_uses SET amount = (SELECT * FROM (SELECT amount FROM user_battle_uses
						  WHERE battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
						  AND for_country_id = '$country_side_id' AND product_id = '$armor_id') AS temp) + '$req_armor' WHERE
						  battle_id = '$battle_id' AND user_id = '$user_id' AND country_id = '$citizenship'
						  AND for_country_id = '$country_side_id' AND product_id = '$armor_id'";
				$conn->query($query);
			}
			else {
				$query = "INSERT INTO user_battle_uses VALUES ('$battle_id', '$user_id', '$citizenship', '$country_side_id', 
						 '$armor_id', '$req_armor')";
				$conn->query($query);
			}
		}
		
		//update battle damage
		if($side == 'defender') {
			$query = "UPDATE battles SET defender_damage = (SELECT * FROM (SELECT defender_damage FROM battles WHERE
					  battle_id = '$battle_id') AS temp) + '$damage' WHERE battle_id = '$battle_id'";
			$conn->query($query);
			
			$defender_damage += $damage;
		}
		else if($side == 'attacker') {
			$query = "UPDATE battles SET attacker_damage = (SELECT * FROM (SELECT attacker_damage FROM battles WHERE
					  battle_id = '$battle_id') AS temp) + '$damage' WHERE battle_id = '$battle_id'";
			$conn->query($query);
			
			$attacker_damage += $damage;
		}
		
		//update energy
		$query = "UPDATE people SET energy = 
				 (SELECT * FROM (SELECT energy FROM people WHERE person_id = '$person_id') AS temp) 
				  - '$fight_energy' WHERE person_id = '$person_id'";
		$conn->query($query);
		
		//update battle_user_damage
		$query = "SELECT * FROM battle_user_damage WHERE user_id = '$user_id' AND battle_id = '$battle_id' AND country_id = '$citizenship'
				  AND for_country_id = '$country_side_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE battle_user_damage SET damage = (SELECT * FROM (SELECT damage FROM battle_user_damage
					  WHERE user_id = '$user_id' AND battle_id = '$battle_id' AND country_id = '$citizenship'
					  AND for_country_id = '$country_side_id') AS temp) + '$damage' WHERE
					  user_id = '$user_id' AND battle_id = '$battle_id' AND country_id = '$citizenship'
					  AND for_country_id = '$country_side_id'";
			$conn->query($query);
		}
		else {
			$query = "INSERT INTO battle_user_damage VALUES('$battle_id', '$user_id', '$damage', '$citizenship', '$country_side_id')";
			$conn->query($query);
		}
		
		//pay for damage
		$reward = 0;
		if($currency_id != 0) {
			$reward = round(($damage / 100) * $damage_price, 2);
			if($reward <= $budget) {
				//check if user has this currency type
				$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$query = "UPDATE user_currency SET amount = (SELECT amount FROM (SELECT amount FROM user_currency WHERE user_id = '$user_id'
							  AND currency_id = '$currency_id') AS old_amount) + '$reward' WHERE user_id = '$user_id'
							  AND currency_id = '$currency_id'";
				}
				else {
					$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', '$reward')";	
				}
				$conn->query($query);
				
				//update battle_budget
				$query = "UPDATE battle_budget SET used_budget = (SELECT * FROM (SELECT used_budget FROM battle_budget WHERE battle_id = '$battle_id'
						  AND currency_id = '$currency_id' AND country_id = '$country_side_id') AS old_amount) 
						  + '$reward' WHERE battle_id = '$battle_id'
						  AND currency_id = '$currency_id' AND country_id = '$country_side_id'";
				$conn->query($query);
			}
			else {
				$reward = 0;
			}
		}
	}
	
	//update attackers/defenders wall 
	if($attacker_damage != 0 || $defender_damage != 0) {
		$overral_force_balance = $attacker_damage + $defender_damage;
		$attacker_percentage = round(($attacker_damage / $overral_force_balance) * 100,2);
		$defender_percentage = 100 - $attacker_percentage;
	}
	else {
		$attacker_percentage = 50;
		$defender_percentage = 50;
	}
	
	$platform_percentage = round(($attacker_strength / $attacker_fixed_strength) * 100, 2);
	$position_percentage = round(($defender_strength / $defender_fixed_strength) * 100, 2);
	
	if($action == "fight") {
		echo json_encode(array("success"=>true,
							   "damage"=>round($damage, 2),
							   "reward"=>$reward,
							   "used_energy"=>$fight_energy,
							   "person_damage"=>$person_base_damage + ($combat_exp / 100),
							   "wound"=>$wound,
							   "combat_exp"=>$combat_exp,
							   "weapon"=>$req_weapons,
							   "ammo"=>$req_ammo,
							   "armor"=>$req_armor,
							   "attacker_percentage"=>$attacker_percentage,
							   "defender_percentage"=>$defender_percentage,
							   "platform_percentage"=>$platform_percentage,
							   "attacker_strength"=>$attacker_strength,
							   "attacker_fixed_strength"=>$attacker_fixed_strength,
							   "position_percentage"=>$position_percentage,
							   "defender_strength"=>$defender_strength,
							   "defender_fixed_strength_output"=>$defender_fixed_strength,
							   "attacker_damage"=>$attacker_damage,
							   "defender_damage"=>$defender_damage,
							   "is_wounded"=>$is_wounded,
							   "killed"=>$is_deleted,
							   "user_exp"=>$user_exp_earn,
							   "gold_reward"=>$gold_reward
							   ));
	}
	else if($action == 'refresh') {
		echo json_encode(array("success"=>true,
							   "damage"=>false,
							   "reward"=>false,
							   "used_energy"=>false,
							   "person_damage"=>false,
							   "wound"=>false,
							   "combat_exp"=>false,
							   "weapon"=>false,
							   "ammo"=>false,
							   "armor"=>false,
							   "attacker_percentage"=>$attacker_percentage,
							   "defender_percentage"=>$defender_percentage,
							   "platform_percentage"=>$platform_percentage,
							   "attacker_strength"=>$attacker_strength,
							   "attacker_fixed_strength"=>$attacker_fixed_strength,
							   "position_percentage"=>$position_percentage,
							   "defender_strength"=>$defender_strength,
							   "defender_fixed_strength_output"=>$defender_fixed_strength,
							   "attacker_damage"=>$attacker_damage,
							   "defender_damage"=>$defender_damage,
							   "is_wounded"=>false,
							   "killed"=>false,
							   "user_exp"=>false,
							   "gold_reward"=>false
							   ));
	}
	
	mysqli_close($conn);
?>