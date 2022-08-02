<?php
	//Description: Update day_count with new day.
	include('/var/www/html/connect_db.php');

	$query = "SELECT MAX(day_number) FROM day_count";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_row($result);
	list($day_number) = $row;
	$day_number++;
	
	$query = "INSERT INTO day_count VALUES('$day_number' , CURRENT_DATE)";
	mysqli_query($conn, $query);
	
	
?>