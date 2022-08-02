<?php
	function addNewPerson($born_bar, $available_slots, $user_id) {
		global $conn;
		
		$query = "SELECT citizenship FROM user_profile up WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($citizenship) = $row;
		$clones_added = 0;
		
		while($born_bar >= 1 && $available_slots > 0) {
			$born_bar--;
			$available_slots--;
			$clones_added++;
			
			$person_id = getTimeForId() . $user_id;
			$query = "INSERT INTO people VALUES('$user_id', '$person_id', 1, 'available', 18, 
											    100, 'No Name', 0, 0, FALSE, 0)";
			$conn->query($query);

			//record for statistics
			countryPeopleBorn($person_id, $citizenship);
		
			//notify user
			$notification = 'Congratulations! You have new person!';
			sendNotification($notification, $user_id);
		}
		
		$query = "UPDATE user_born_bar SET born_bar = '$born_bar' WHERE user_id = '$user_id'";
		$conn->query($query);
		
		return($clones_added);
	}
?>