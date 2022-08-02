<?php
	//Description: Send notification to the user.
	
	function sendNotification($notification, $user_id) {
		global $conn;
		$query = "INSERT INTO notifications VALUES('$user_id', '$notification', CURRENT_DATE, CURRENT_TIME, 1)";
		$conn->query($query);
	}
?>