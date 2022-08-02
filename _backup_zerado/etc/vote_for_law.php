<?php
	//Description: Vote for law.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$law_id =  htmlentities(stripslashes(trim($_POST['law_id'])), ENT_QUOTES);
	$decision =  htmlentities(stripslashes(trim($_POST['decision'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if(!is_numeric($law_id)) {
		exit();
	}
	if($decision > 1 && $decision < 0) {
		exit();
	}
	
	//check if president
	$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$is_governor = true;
		$row = $result->fetch_row();
		list($position_id, $country_id) = $row;
	}
	else { //check if congressman
		$query = "SELECT country_id FROM congress_details WHERE country_id = 
				 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$is_governor = true;
			$row = $result->fetch_row();
			list($country_id) = $row;
			$position_id = 3;
		}
		else {
			exit("0|You're not a governor and not allowed to perform this action.");
		}
	}
	
	
	//check if law exist, not processed and if user is allowed to vote for this law
	$query = "SELECT gcr.* FROM government_country_responsibilities gcr, country_law_info cli
			  WHERE gcr.country_id = cli.country_id AND gcr.responsibility_id = cli.responsibility_id AND law_id = '$law_id'
			  AND position_id = '$position_id' AND cli.country_id = '$country_id' AND must_sign_vote = TRUE
			  AND is_processed = FALSE";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit('0|You are not allowed to vote in this law.');
	}
	
	//check if already voted
	$query = "SELECT * FROM users_voted_for_laws WHERE user_id = '$user_id' AND law_id = '$law_id'";
	$result = $conn->query($query);
	if($result->num_rows >= 1) {
		exit('0|You already voted for this law.');
	}

	//vote and mark user as voted
	if($decision == 1) {
		$query = "UPDATE country_law_info SET yes = (SELECT * FROM (SELECT yes FROM country_law_info 
				  WHERE law_id = '$law_id') AS temp) + 1 WHERE law_id = '$law_id'";
	}
	else if($decision == 0) {
		$query = "UPDATE country_law_info SET no = (SELECT * FROM (SELECT no FROM country_law_info 
				  WHERE law_id = '$law_id') AS temp) + 1 WHERE law_id = '$law_id'";
	}
	
	if($conn->query($query)) {
		$query = "INSERT INTO users_voted_for_laws VALUES('$law_id', '$user_id')";
		$conn->query($query);
		
		echo '1|You have successfuly voted for this law.';
	}
	
	
	mysqli_close($conn);
?>