<?php
	//Description: Set timezone id if it's 4AM.  Sets automatically based on the timezone, if it's 4am for specific timezone
	//			   it will set that timezone id.

	$base_time = date('G');
	$base_time -= 4;
	if($base_time >= 12) {
		$base_time -= 24;
	}
	$query = "SELECT timezone_id, hours FROM timezones";
	$result = mysqli_query($conn, $query);
	while($row = mysqli_fetch_row($result)) {
		list($timezone_id, $hours) = $row;
		$zone = $base_time + $hours;
		if($zone == 0) {
			break;
		}
	}
?>