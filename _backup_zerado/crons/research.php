<?php
	//Description: Check if research ended. every 5 min.
	include('/var/www/html/connect_db.php');

	$query = "SELECT ur.research_id, user_id, category FROM user_researches ur, research_info ri
			  WHERE ri.research_id = ur.research_id AND is_researched = FALSE AND
			  DATE_ADD(TIMESTAMP(start_date, start_time), INTERVAL research_time MINUTE) <= NOW()";
	$result_research = $conn->query($query);
	while($row_research = $result_research->fetch_row()) {
		list($research_id, $user_id, $category) = $row_research;
		
		if($category == 'buildings') {
			$query = "SELECT building_id FROM building_research WHERE research_id = '$research_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($building_id) = $row;
			
			$query = "UPDATE user_researches SET is_researched = TRUE WHERE user_id = '$user_id' AND research_id = '$research_id'";
			$conn->query($query);
		}
	}
?>