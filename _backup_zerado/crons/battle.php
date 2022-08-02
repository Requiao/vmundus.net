<?php
	//runs * * * * * every minute
	//Description: process battle

	include('/var/www/html/connect_db.php');
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$BASE_DAMAGE = 100;

	//ALIEN BATTLES
	$query = "UPDATE battles SET defender_damage = defender_damage + 100 WHERE defender_id = 1000
			  AND active = TRUE";
	$conn->query($query);

	$query = "UPDATE battles SET attacker_damage = attacker_damage + 100 WHERE attacker_id = 1000
			  AND active = TRUE";
	$conn->query($query);
	
	$query = "SELECT war_id, battle_id, type, user_attacker_id, b.region_id, attacker_id, defender_id, 
			  attacker_damage, defender_damage, attacker_strength, rap.strength AS 'attacker_fixed_strength', 
			  defender_strength, dci.strength AS 'defender_fixed_strength', region_name, b.def_loc_id, 
			  date_started, time_started
			  FROM battles b, regions r, region_attack_platform rap, def_const_info dci
			  WHERE r.region_id = b.region_id AND active = TRUE
			  AND rap.platform_id = b.attack_platform_id AND dci.def_loc_id = b.def_loc_id";
	$result_battles = $conn->query($query);
	while($row_battles = $result_battles->fetch_row()) {
		list($war_id, $battle_id, $battle_type, $user_attacker_id, $attacked_region, $attacker_id, 
			 $defender_id, $attacker_damage, $defender_damage, $attacker_strength, 
			 $attacker_fixed_strength, $defender_strength, $defender_fixed_strength, $region_name, 
			 $def_loc_id, $date_started, $time_started) = $row_battles;
	
	
		//calculate defenders and attackers damage
		if($attacker_damage != 0 || $defender_damage != 0) {
			$overall_force_balance = $attacker_damage + $defender_damage;
			$attacker_percentage = round(($attacker_damage / $overall_force_balance) * 100,2);
			$defender_percentage = 100 - $attacker_percentage;
		}
		else {
			$attacker_percentage = 50;
			$defender_percentage = 50;
		}
		$attackers_wall_damaged = round(($defender_percentage / 100) * $BASE_DAMAGE, 2);
		$defenders_wall_damaged = $BASE_DAMAGE - $attackers_wall_damaged;

		//update wall 
		$end_battle = false;
		
		//attackers
		$attacker_strength -= $attackers_wall_damaged;
		if($attacker_strength <= 0) {
			$end_battle = true;
			$winner_id = $defender_id;
			$query = "UPDATE battles SET attacker_strength = 0 WHERE battle_id = '$battle_id'";
			$conn->query($query);
					
			if($defender_strength < 2000) {
				$defence_system_strength = 2000;
			}
			else {
				$defence_system_strength = $defender_strength;
			}
		}
		else {
			$query = "UPDATE battles SET attacker_strength = (SELECT * FROM (SELECT attacker_strength FROM battles WHERE
					  battle_id = '$battle_id') AS temp) - '$attackers_wall_damaged' WHERE battle_id = '$battle_id'";
			$conn->query($query);
		}
		//defenders
		$defender_strength -= $defenders_wall_damaged;
		if($defender_strength <= 0) {
			$end_battle = true;
			$winner_id = $attacker_id;
			$query = "UPDATE battles SET defender_strength = 0 WHERE battle_id = '$battle_id'";
			$conn->query($query);
		}
		else {
			$query = "UPDATE battles SET defender_strength = (SELECT * FROM (SELECT defender_strength FROM battles WHERE
					  battle_id = '$battle_id') AS temp) - '$defenders_wall_damaged' WHERE battle_id = '$battle_id'";
			$conn->query($query);
		}
		
		/* End Battle */
		if($end_battle) {
			//reward defender based on aliens damage if attacked by aliens
			if($attacker_id == 1000) {
				$reward_defender = $attacker_damage * 0.001;
				$query = "UPDATE country_product SET amount = amount + '$reward_defender' 
						  WHERE country_id = '$defender_id' AND product_id = 1";
				$conn->query($query);
			} 

			$query = "UPDATE battles SET active = FALSE WHERE battle_id = '$battle_id'";
			$conn->query($query);
			
			$query = "UPDATE battles SET winner_id = '$winner_id' WHERE battle_id = '$battle_id'";
			$conn->query($query);
			
			$query = "UPDATE battles SET date_ended = CURRENT_DATE WHERE battle_id = '$battle_id'";
			$conn->query($query);
			
			$query = "UPDATE battles SET time_ended = CURRENT_TIME WHERE battle_id = '$battle_id'";
			$conn->query($query);

			if($battle_type == 'resistance') {
				$query = "UPDATE country_wars SET active = FALSE WHERE war_id = '$war_id';";
				$conn->query($query);
			}
			
			//update new owner if conquered
			if($winner_id == $attacker_id) {
				$query = "UPDATE regions SET country_id = '$winner_id' WHERE region_id = '$attacked_region'";
				$conn->query($query);
				
				//if conquered set new defense system = to 20% strength of the first def_position
				$query = "SELECT strength FROM def_const_info WHERE def_loc_id = 1";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($position_strength) = $row;
				$position_strength = $position_strength * 0.2;
				
				$query = "UPDATE region_defence_systems SET strength = '$position_strength' WHERE region_id = '$attacked_region'";
				$conn->query($query);
				
				$query = "UPDATE region_defence_systems SET def_loc_id = '1' WHERE region_id = '$attacked_region'";
				$conn->query($query);
				
				//if was a revolt, set core
				if($battle_type == 'revolt') {
					$query = "INSERT INTO country_core_regions VALUES('$attacked_region', '$winner_id', 2160)";
					$conn->query($query);

					//country details
					$query = "SELECT country_name FROM country WHERE country_id = '$winner_id'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($country_name) = $row;

					//set user that started a battle as a president
					//if was a governor, remove
					$query = "UPDATE country_government SET user_id = NULL WHERE
							  user_id = '$user_attacker_id'";
					$conn->query($query);
					
					//if congressman, remove
					$query = "DELETE FROM congress_members WHERE user_id = '$user_attacker_id'";
					$conn->query($query);
				
					$query = "UPDATE country_government SET user_id = '$user_attacker_id' 
							  WHERE country_id = '$winner_id' AND position_id = 1";
					$conn->query($query);

					$query = "UPDATE country_government SET elected = CURRENT_DATE
							  WHERE country_id = '$winner_id' AND position_id = 1";
					$conn->query($query);

					$notification = "Congratulations! You won the Revolt!" . 
									" You have been assigned as a president of the $country_name.";
					sendNotification($notification, $user_attacker_id);

					//change cz
					$query = "UPDATE user_profile SET citizenship = '$winner_id' 
							  WHERE user_id = '$user_attacker_id'";
					$conn->query($query);

					$notification = "Your citizenship have been changed to $country_name.";
					sendNotification($notification, $user_attacker_id);

					//reward with currency
					$query = "INSERT INTO user_currency VALUES ('$user_attacker_id', '$winner_id', 250)";
					$conn->query($query);
				}

				$def_loc_id = 1;
				$damaged_rate = 0.8;
			}
			else {
				$query = "UPDATE region_defence_systems SET strength = '$defence_system_strength' WHERE region_id = '$attacked_region'";
				$conn->query($query);
				
				$damaged_rate = 1 - (((100 / $defender_fixed_strength) * $defence_system_strength) / 100);
			}
			
			//insert required product amount to repair defence system
			$query = "DELETE FROM repair_defence_construction WHERE region_id = '$attacked_region'";
			$conn->query($query);
			
			$query = "SELECT product_id, amount FROM def_const_product WHERE def_loc_id = '$def_loc_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_id, $amount) = $row;
				$amount = ceil($amount * $damaged_rate);
				$query = "INSERT INTO repair_defence_construction VALUES('$attacked_region', '$product_id', '$amount')";
				$conn->query($query);
			}
			
			//notify presidents
			$query = "SELECT user_id, country_id FROM country_government WHERE position_id = 1 
					  AND (country_id = '$defender_id' OR country_id = '$attacker_id')";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($president, $pres_country_id) = $row;
				if($pres_country_id == $winner_id) {
					$notification = "You won battle for $region_name!";
				}
				else {
					$notification = "You lost battle for $region_name!";
				}
				sendNotification($notification, $president);
			}
			
			//put remaining battle budget into country treasury.
			$query = "SELECT budget - used_budget, currency_id, country_id FROM battle_budget WHERE battle_id = '$battle_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($remaining_budget, $currency_id, $budget_country_id) = $row;
				//update country_currency table with taxes
				$query = "SELECT * FROM country_currency WHERE country_id = '$budget_country_id' AND currency_id = '$currency_id'";
				$result_cu = $conn->query($query);
				if($result_cu->num_rows == 1) {
					$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
							  WHERE country_id = '$budget_country_id' AND currency_id = '$currency_id') AS temp) + '$remaining_budget' 
							  WHERE country_id = '$budget_country_id' AND currency_id = '$currency_id'";
				}
				else {
					$query = "INSERT INTO country_currency VALUES('$budget_country_id', '$currency_id', '$remaining_budget')";
				}
				$conn->query($query);
			}
			
			//battle hero achievement
			$achievement_id = 5; //Battle Hero;
			$notification = 'Congratulation. You earned Battle Hero medal in' . 
							' <a href="battle?currency_id=0&battle_id=' . $battle_id . '&side=attacker">this</a> battle';
			
			$query = "SELECT * FROM (SELECT user_id FROM battle_user_damage
					  WHERE battle_id = '$battle_id' AND for_country_id = (SELECT attacker_id FROM battles WHERE battle_id = '$battle_id') 
					  ORDER BY damage DESC LIMIT 1) AS t
					  UNION
					  SELECT * FROM (SELECT user_id FROM battle_user_damage
					  WHERE battle_id = '$battle_id' AND for_country_id = (SELECT defender_id FROM battles WHERE battle_id = '$battle_id') 
					  ORDER BY damage DESC LIMIT 1) AS t";
			$result_hero = $conn->query($query);
			while($row_hero = $result_hero->fetch_row()) {
				list($hero_id) = $row_hero;
				
				$query = "SELECT earned FROM user_achievements WHERE user_id = '$hero_id' AND achievement_id = '$achievement_id'";
				$result = $conn->query($query);
				if($result->num_rows == 0) {
					$query = "INSERT INTO user_achievements VALUES ('$hero_id', '$achievement_id', '0', '1')";
				}
				else {
					$row = $result->fetch_row();
					list($earned) = $row;
					$earned++;
					$query = "UPDATE user_achievements SET earned = '$earned' WHERE user_id = '$hero_id' 
							  AND achievement_id = '$achievement_id'";
				}
				$conn->query($query);
				
				sendNotification($notification, $hero_id);
			}

			//if defender were creating core, return gold
			if($winner_id == $attacker_id) {
				$query = "SELECT cost FROM core_creation WHERE region_id = '$attacked_region'
						  AND country_id = '$defender_id' AND is_active = TRUE";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($total_cost) = $row;

					$query = "UPDATE core_creation SET is_active = FALSE 
							  WHERE country_id = '$defender_id' AND region_id = '$attacked_region' 
							  AND is_active = TRUE";
					$conn->query($query);

					$query = "UPDATE country_product SET amount = amount + '$total_cost' 
							  WHERE country_id = '$defender_id' AND product_id = '1'";
					$conn->query($query);
				}

				$hours_left = 24 * 90; //90 days
				$query = "UPDATE country_core_regions SET hours_left = '$hours_left' 
						  WHERE region_id = '$attacked_region' AND country_id = '$winner_id'";
				$conn->query($query);
			}
		}
	}
?>