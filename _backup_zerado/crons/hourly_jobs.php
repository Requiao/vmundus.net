<?php
	//runs */2 */1 * * * every 2nd minute each hour
	//Description: Record country population
	//Description: Reset companies' cycles worked
	//Description: resign ministers if term length expired
	//Description: end battles
	//Description: update user level table with new user levels

	include('/var/www/html/connect_db.php');
	include('/var/www/html/php_functions/record_statistics.php');
	include('/var/www/html/crons/timezone_id.php'); //$timezone_id
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('/var/www/html/php_functions/get_time_for_id.php');//getTimeForId()
 
	/* start aliens attack */
	$max_attacks = 8;
	$attempts = 0;//temp
	while($max_attacks > 0 && $attempts <= 10000) {
		if(startAlienAttack(mt_rand(1, 1485))) {
			$max_attacks--;
		}
		$attempts++;
	}
	function startAlienAttack($rand_region) {
		global $conn;
		$query = "SELECT region_id, country_id FROM regions WHERE country_id != 1000
				  AND region_id NOT IN (
				  SELECT region_id FROM battles WHERE active = TRUE)
				  AND region_id IN
				 (SELECT region_id FROM region_defence_systems WHERE strength >= 0)";
		$result_c = $conn->query($query);
		while($row_c = $result_c->fetch_row()) {
			list($region_id, $country_id) = $row_c;

			if($region_id != $rand_region) {
				continue;
			}

			//start battle
			$battle_id = getTimeForId() . '1000';
			$battle_type = 'regular';
			$is_active = 1;
			$date_eneded = 'NULL';
			$time_eneded = 'NULL';
			$winner_id = 'NULL';
			$attacker_damage = 100;
			$defender_damage = 0;

			$query = "SELECT war_id FROM country_wars WHERE country_id = 1000 
					AND with_country_id = '$country_id' AND active = TRUE";
			$result = $conn->query($query);
			$war_id = '';
			if($result->num_rows != 1) {
				$war_id = getTimeForId() . '1000';
				$query = "INSERT INTO country_wars VALUES('$war_id', '1000', '$country_id', CURRENT_DATE, CURRENT_TIME, 1, 0)";
				$conn->query($query);
			}
			else {
				$row = $result->fetch_row();
				list($war_id) = $row;
			}

			$query = "SELECT region_name FROM regions WHERE region_id = '$region_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($region_name) = $row;

			//get defenders position id and strength
			$query = "SELECT def_loc_id, strength FROM region_defence_systems WHERE region_id = '$region_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($def_loc_id, $position_strength) = $row;

			//get defenders moral
			$defenders_moral = 0;
			$query = "SELECT * FROM country_core_regions ccr 
					WHERE ccr.country_id = '$country_id' AND region_id = '$region_id'";
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
							WHERE r.country_id = '$country_id' AND me.effect_name = 'owning_none_core'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($base_none_core_moral) = $row;

				$total_moral = $defending_none_core_moral + $base_none_core_moral;
				$defenders_moral = $total_moral < $max_negative_moral ? $max_negative_moral : $total_moral;
			}
			
			$query = "INSERT INTO battles VALUES ('$war_id', '$battle_id', '$region_id', '1000', 
					'$country_id', CURRENT_DATE,  CURRENT_TIME, $date_eneded, $time_eneded, 
					'$battle_type', TRUE, $winner_id, '5000', '1',
					'$attacker_damage', '$defender_damage', '$position_strength', '$def_loc_id', 
					NULL, '$defenders_moral', '0')";
			$conn->query($query);

			//notify defenders
			$query = "SELECT user_id FROM user_profile WHERE citizenship = '$country_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($defenders_user_id) = $row;
				$notification = "$region_name has been attacked by Aliens! Help to defend your country from these cruel creatures!";
				sendNotification($notification, $defenders_user_id);
			}

			return true;
		}

		return false;
	}

	/* reward for post views */
	$reward_per_view = 0.02;
	$reward_per_like = 0.04;
	$query = "SELECT user_id, views, likes FROM post_views_rewards WHERE views > 0";
	$result_views = $conn->query($query);
	while($row_views = $result_views->fetch_row()) {
		list($user_id, $views, $likes) = $row_views;
		
		$reward = ($views * $reward_per_view) + ($likes * $reward_per_like);
		
		//update user product
		//check if already has this product type
		$query = "SELECT * FROM user_product WHERE product_id = '1' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "INSERT INTO user_product VALUES('$user_id', '1', '$reward')";
			$conn->query($query);
		}
		else {
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '1') AS temp) + '$reward' 
					  WHERE user_id = '$user_id' AND product_id = '1'";
			$conn->query($query);
		}
		
		//update post_views_rewards
		$query = "UPDATE post_views_rewards SET rewarded_views = rewarded_views +  '$views' WHERE user_id = '$user_id'";
		$conn->query($query);
		
		$query = "UPDATE post_views_rewards SET views = 0 WHERE user_id = '$user_id'";
		$conn->query($query);
		
		$query = "UPDATE post_views_rewards SET rewarded_likes = rewarded_likes + '$likes' WHERE user_id = '$user_id'";
		$conn->query($query);
		
		$query = "UPDATE post_views_rewards SET likes = 0 WHERE user_id = '$user_id'";
		$conn->query($query);
		
		//notify
		$notification = "You have been rewarded with $reward Gold for likes and views of your posts.";
		sendNotification($notification, $user_id);
	}
	
	/* Record country population */
	$query = "SELECT citizenship, COUNT(person_id) FROM user_profile up, people p
			  WHERE p.user_id = up.user_id
			  GROUP BY citizenship ORDER BY citizenship";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($country_id, $population) = $row;
		recordCountryPopulation($country_id, $population);
	}
	
	/* Reset companies' cycles worked */
	$query = "UPDATE companies SET cycles_worked = 0 WHERE company_id IN
			 (SELECT company_id FROM user_building WHERE user_id IN
			 (SELECT user_id FROM user_profile WHERE timezone_id = '$timezone_id'))
			  OR company_id IN
			 (SELECT company_id FROM corporation_building WHERE corporation_id IN
			 (SELECT corporation_id FROM corporations WHERE manager_id IN
			 (SELECT user_id FROM user_profile WHERE timezone_id = '$timezone_id')))";
	$conn->query($query);
	
	/* resign ministers if term length expired */
	$query = "SELECT cg.country_id, cg.position_id, user_id FROM country_government cg, government_term_length gtl WHERE
			  DATE_ADD(elected, INTERVAL term DAY) <= CURRENT_DATE
			  AND gtl.country_id = cg.country_id
			  AND gtl.position_id = cg.position_id AND cg.country_id IN 
			  (SELECT country_id FROM country WHERE timezone_id = '$timezone_id') AND user_id IS NOT NULL";
	$result_ministers = $conn->query($query);
	while($row_ministers = $result_ministers->fetch_row()) {
		list($country_id, $position_id, $governor_id) = $row_ministers;
		
		//notify
		$notification = "Your term has ended. You have been automatically resigned from your position.";
		sendNotification($notification, $governor_id);
		
		//remove from position
		$query = "UPDATE country_government SET user_id = NULL WHERE user_id = '$governor_id'";
		$conn->query($query);
		
		//check if at least one governor or congress have access to 'Change responsibilities' law
		$query = "SELECT COUNT(*) FROM government_country_responsibilities 
				  WHERE country_id = '$country_id' AND responsibility_id = 8 AND must_sign_vote = TRUE AND have_access = TRUE
				  AND (position_id IN (SELECT position_id FROM country_government 
				  WHERE country_id = '$country_id' AND user_id IS NOT NULL)
				  OR position_id IN (SELECT 3 FROM congress_members WHERE country_id = '$country_id' AND user_id IS NOT NULL))";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($governors_that_have_access) = $row;
		
		if($governors_that_have_access == 0) {
			$query = "UPDATE government_country_responsibilities SET must_sign_vote = TRUE 
					  WHERE country_id = '$country_id' AND position_id = 1 
					  AND responsibility_id = 8";
			$conn->query($query);
			
			$query = "UPDATE government_country_responsibilities SET have_access = TRUE 
					  WHERE country_id = '$country_id' AND position_id = 1 
					  AND responsibility_id = 8";
			$conn->query($query);
			
			$query = "UPDATE government_country_responsibilities SET must_sign_vote = TRUE 
					  WHERE country_id = '$country_id' AND position_id = 3 
					  AND responsibility_id = 8";
			$conn->query($query);
			
			$query = "UPDATE government_country_responsibilities SET have_access = TRUE 
					  WHERE country_id = '$country_id' AND position_id = 3 
					  AND responsibility_id = 8";
			$conn->query($query);
		}
	}

	/* resign congress */
	$query = "SELECT cd.country_id, user_id 
			  FROM congress_details cd, government_term_length gtl, congress_members cm WHERE
			  DATE_ADD(elected, INTERVAL term DAY) <= CURRENT_DATE
			  AND gtl.country_id = cd.country_id
			  AND gtl.position_id = 3 AND cm.country_id = cd.country_id
			  AND cd.country_id IN (SELECT country_id FROM country WHERE timezone_id = '$timezone_id')";
	$result = $conn->query($query);
	if($result->num_rows > 0) {
		$row = $result->fetch_row();
		list($country_id, $governor_id) = $row;
		
		//notify
		$notification = "Your term has ended. You have been automatically resigned from your position.";
		sendNotification($notification, $governor_id);

		//remove congress
		$query = "DELETE FROM congress_members WHERE country_id = '$country_id'";
		$conn->query($query);
	}

	/* update remaining region core time if not owner */
	$query = "SELECT ccr.region_id, c.country_id, hours_left 
			  FROM country c, country_core_regions ccr, regions r
			  WHERE c.country_id = ccr.country_id AND r.region_id = ccr.region_id 
			  AND r.country_id != ccr.country_id";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($region_id, $country_id, $hours_left) = $row;

		$query = "UPDATE country_core_regions SET hours_left = hours_left - 1
				  WHERE country_id = '$country_id' AND region_id = '$region_id'";
		$conn->query($query);

		if($hours_left - 1 <= 0) {
			$query = "DELETE FROM country_core_regions 
					  WHERE country_id = '$country_id' AND region_id = '$region_id'";
			$conn->query($query);
		}
	}

	/* update core_creation table */
	$query = "SELECT region_id, country_id FROM core_creation WHERE is_active = TRUE";
	$result_cc = $conn->query($query);
	while($row_cc = $result_cc->fetch_row()) {
		list($region_id, $country_id) = $row_cc;
		
		$query = "SELECT * FROM battles WHERE active = TRUE AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "UPDATE core_creation SET hours_left = hours_left - 1 
					  WHERE is_active = TRUE AND region_id = '$region_id' AND country_id = '$country_id'";
			$conn->query($query);
		}
	}

	/* create cores */
	$query = "SELECT region_id, country_id FROM core_creation WHERE hours_left <= 0 AND is_active = TRUE";
	$result_cc = $conn->query($query);
	$hours_left = 24 * 90; //90 days
	while($row_cc = $result_cc->fetch_row()) {
		list($region_id, $country_id) = $row_cc;

		$query = "SELECT * FROM country_core_regions ccr 
				  WHERE ccr.country_id = '$country_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO country_core_regions VALUES ('$region_id', '$country_id', '$hours_left')";
			$conn->query($query);
		}
		$query = "UPDATE core_creation SET is_active = FALSE 
				  WHERE country_id = '$country_id' AND region_id = '$region_id' AND is_active = TRUE";
		$conn->query($query);
	}
	
	
	/* update user level table with new user levels */
	$query = "CALL populateUserLevels()";
	$conn->query($query);
?>