<?php
	//Description: If user responds to email, then register him.
	usleep(100000);
	
	include('connect_db.php');
	
	$hash = htmlentities(stripslashes(trim($_GET['unsubscribe'])), ENT_QUOTES);
	
	//check if not unsubscribed
	$query = "SELECT unsubscribe FROM email_notifications 
			  WHERE email_hash = '$hash'";
	$result = $conn->query($query);
	if($result->num_rows == 0) {
		echo "Error";
	}
	else {
		$row = $result->fetch_row();
		list($unsubscribe) = $row;
		
		if($unsubscribe) {
			echo "You already unsubscribed from vMundus emails.";
		}
		else {
			$query = "UPDATE email_notifications SET unsubscribe = TRUE WHERE email_hash = '$hash'";
			$conn->query($query);
			
			echo "Success.";
		}
	}
	
	mysqli_close($conn);
?>