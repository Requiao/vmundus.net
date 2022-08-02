<?php
	function deletePerson($person_id) {
		global $conn;
		global $user_id;
		
		//get person details for notification
		$query = "SELECT person_name, years, MAX(skill_lvl), experience, combat_exp
				  FROM people p, experience WHERE
				  person_id = '$person_id' AND required_exp <= experience";
		$result = $conn->query($query);
		$row= $result->fetch_row();
		list($person_name, $years, $skill_lvl, $combat_exp) = $row;
		
		$query = "SELECT company_name, company_id FROM companies WHERE company_id = 
				 (SELECT company_id FROM hired_workers WHERE person_id = '$person_id')";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			$row= $result->fetch_row();
			list($company_name, $company_id) = $row;
			
			$notification = 'Bad news! ' . $person_name . ' died. ' . $person_name . ' was ' . $years . ' years old. Person worked for' . 
							' <a href="company_manage?id=' . $company_id . '">' . $company_name . '</a> company.' .
							' Work level: ' .  $skill_lvl . '. Combat experience: ' . $combat_exp . '.';
		}
		else {
			$notification = 'Bad news! ' . $person_name . ' died. ' . $person_name . ' was ' . $years . ' years old. ' .
							'Person was unemployed. Work level: ' . $skill_lvl . '. Combat experience: ' . $combat_exp . '.';
		}
		sendNotification($notification, $user_id);
		
		$query = "DELETE FROM hired_workers WHERE person_id = '$person_id'";
		$conn->query($query);
					
		$query = "DELETE FROM people_house WHERE person_id = '$person_id'";
		$conn->query($query);

		$query = "DELETE FROM work_journal WHERE person_id = '$person_id'";
		$conn->query($query);

		$query = "DELETE FROM soldier_training WHERE person_id = '$person_id'";
		$conn->query($query);
		
		$query = "DELETE FROM people WHERE person_id = '$person_id'";
		if(!$conn->query($query)) {
			echo "Delete from people query failed. Person id $person_id. \n";
		}
	}
?>