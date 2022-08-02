<?php
	//Description: Delete data from confirmation if older then 24 hours. run every 30min.
	include('/var/www/html/connect_db.php');

	$query = "DELETE FROM confirmation WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL 24 HOUR) <= NOW()";
	mysqli_query($conn, $query);
?>