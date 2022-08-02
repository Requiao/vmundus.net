<?php
	//Description: Check if defense system construction ended. every 5 min.
	include('/var/www/html/connect_db.php');

	$query = "SELECT region_id, dcip.def_loc_id, strength
			  FROM defence_const_in_process dcip, def_const_info dci
			  WHERE dci.def_loc_id = dcip.def_loc_id AND 
			  DATE_ADD(TIMESTAMP(start_date, start_time), INTERVAL time_min MINUTE) <= NOW()";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($region_id, $def_loc_id, $strength) = $row;
		
		$query = "UPDATE region_defence_systems set def_loc_id = '$def_loc_id' WHERE region_id = '$region_id'";
		$conn->query($query);
		
		$query = "UPDATE region_defence_systems set strength = '$strength' WHERE region_id = '$region_id'";
		$conn->query($query);
		
		$query = "DELETE FROM defence_const_in_process WHERE region_id = '$region_id'";
		$conn->query($query);
	}
?>