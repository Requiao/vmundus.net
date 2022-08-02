<?php
	//Description: Add year to person or die. Add new persons.

	include('/var/www/html/connect_db.php');
	include('/var/www/html/crons/timezone_id.php'); //$timezone_id
	include('/var/www/html/php_functions/get_time_for_id.php'); //function getTimeForId().
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('/var/www/html/php_functions/record_statistics.php');
	include('/var/www/html/php_functions/delete_person.php');//deletePerson($person_id)
	include('/var/www/html/php_functions/add_new_person.php');//addNewPerson($born_bar, $room, $user_id);

	$MAX_YEARS = 220;
	$BORN_BAR_GROWTH = 0.25;
	
	$query = "SELECT * FROM life_chance ORDER BY years DESC";//ORDER IN DESC!
	$result = $conn->query($query);
	$length = 0;
	while($row = $result->fetch_row()) {
		list($life_years, $chance) = $row;
		$life_chance[$length]['life_years'] = $life_years;
		$life_chance[$length]['chance'] = $chance;
		$length++;
	}
	
	$query = "SELECT person_id, years, person_name, p.user_id, citizenship, wound
			  FROM people p, user_profile up WHERE p.user_id IN 
			 (SELECT user_id FROM user_profile WHERE timezone_id = '$timezone_id') AND p.user_id = up.user_id 
			  ORDER BY citizenship, user_id, years DESC";
	$result = $conn->query($query);
	$country_id = 0;//to prevent multiple requests for survive chance in each country for every person. must be odered by citizenship
	
	$users_lost_person = array();
	$person_deleted = false;
	
	while($row = $result->fetch_row()) {
		list($person_id, $years, $person_name, $user_id, $country_id, $wound) = $row;
		
		$person_deleted = false;
		for($y = 0; $y < $length; $y++) {//run through all chances until years match
			//if years match with life chance
			if($years >= $life_chance[$y]['life_years']) {
				//check if user already lost person. Max lose is 1 Person Per Day.
				if(!empty($users_lost_person["$user_id"]) && $users_lost_person["$user_id"]) {
					$person_deleted = true;
				}
				
				if(!$person_deleted || $years >= $MAX_YEARS) {
					$index = mt_rand(1,100);
					$chance = $life_chance[$y]['chance'];//chance to be deleted
					
					//loop that determined if a person will be deleted
					for($x = 0; $x < $chance; $x++) {
						if($index == mt_rand(1,100) || $years >= $MAX_YEARS) {
							deletePerson($person_id);
							
							//record for statistics
							countryPeopleDie($person_id, $country_id, $years);
							
							//record user. person deleted
							$users_lost_person["$user_id"] = true;
							break 2;
						}
					}
				}
				addYear($years, $person_id);
				resetWorkStatus($person_id);
				resetRecoveredEnergy($person_id);
				updateDaysWorked($person_id);
				updateWound($person_id, $wound);			
				break;
			}
		}
	}
	
	function addYear($years, $person_id) {
		global $conn;
		$years++;
		$query = "UPDATE people SET years = '$years' WHERE person_id = '$person_id'";
		$conn->query($query);
	}
	
	function resetWorkStatus($person_id) {
		global $conn;
		$query = "UPDATE people SET worked = FALSE WHERE person_id = '$person_id'";
		$conn->query($query);
	}
	
	function resetRecoveredEnergy($person_id) {
		global $conn;
		$query = "UPDATE people SET recovered_energy = 0 WHERE person_id = '$person_id'";
		$conn->query($query);
	}
	
	function updateDaysWorked($person_id) {
		global $conn;
		$query = "SELECT time_hired FROM hired_workers WHERE person_id = '$person_id'";
		$result =$conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE hired_workers SET time_hired = 
					 (SELECT * FROM (SELECT time_hired FROM hired_workers WHERE person_id = '$person_id') AS temp) + 1 
					  WHERE person_id = '$person_id'";
			$conn->query($query);
		}
	}
	
	function updateWound($person_id, $wound) {
		global $conn;
		if($wound > 0) {
			$wound--;
			$query = "UPDATE people SET wound = '$wound' WHERE person_id = '$person_id'";
			$conn->query($query);
		}
	}
	
	//add persons
	$query = "SELECT user_id, citizenship, capital FROM user_profile up, country c
			  WHERE up.timezone_id = '$timezone_id' AND c.country_id = up.citizenship ORDER BY citizenship;";
	$result_users = $conn->query($query);

	while($row_users = $result_users->fetch_row()) {
		list($user_id, $citizenship, $capital) = $row_users;
		//get born bar and free room in the houses
		$query = "SELECT slot_number - (SELECT COUNT(person_id) FROM people WHERE user_id = '$user_id'), 
				 (SELECT born_bar FROM user_born_bar WHERE user_id = '$user_id'),
				 (SELECT SUM(growth) FROM users_cloning_farms ucf, cloning_farms_born_bar cfbb
				  WHERE user_id = '$user_id' AND ucf.building_id = cfbb.building_id)
				  FROM user_people_slots ups WHERE ups.user_id = '$user_id'";
		$result_room_bar = $conn->query($query);
		$row_room_bar = $result_room_bar->fetch_row();
		list($available_slots, $born_bar, $growth_bonus) = $row_room_bar;
		$total_bb_growth = $growth_bonus + $BORN_BAR_GROWTH;
		
		//update cloning farms expiration days
		$query = "UPDATE users_cloning_farms SET days_left = days_left - 1 WHERE user_id = '$user_id'";
		$conn->query($query);
		
		//delete exhausted farms
		$query = "SELECT name, farm_id FROM building_info bi, users_cloning_farms ucf
				  WHERE user_id = '$user_id' AND bi.building_id = ucf.building_id AND days_left <= 0";
		$result_farms = $conn->query($query);
		while($row_farms = $result_farms->fetch_row()) {
			list($name, $farm_id) = $row_farms;
			
			$query = "DELETE FROM users_cloning_farms WHERE farm_id = '$farm_id'";
			$conn->query($query);
			
			//notify
			$notification = "\'$name\' has been exhausted.";
			sendNotification($notification, $user_id);
		}
		
		//add to born bar
		if($born_bar <= (50 - $total_bb_growth)) {
			$born_bar = $total_bb_growth + $born_bar;
		}
		addNewPerson($born_bar, $available_slots, $user_id);
	}
?>