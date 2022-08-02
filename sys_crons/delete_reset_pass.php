<?php
	//Description: Delete data from password reset if older then 15min. run every 5min.
	include('/var/www/html/connect_db.php');

	$query = "DELETE FROM password_reset WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL 15 MINUTE) <= NOW()";
	mysqli_query($conn, $query);
?>