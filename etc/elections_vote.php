<?php
	//Description: Vote for president/congress candidate.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_user_level.php');//getUserLevel($user_id); returns user level
	
	$election_id =  htmlentities(stripslashes(strip_tags(trim($_POST['election_id']))), ENT_QUOTES);
	$candidate_id =  htmlentities(stripslashes(strip_tags(trim($_POST['candidate_id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if ($action == 'vote') {
		if(!is_numeric($candidate_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exist."
								   )));
		}
		if(!is_numeric($election_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Elections doesn't exist.$election_id"
								   )));
		}
		
		//must have at least level 3
		if(getUserLevel($user_id) < 3) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You must have at least level 3 in order to vote in the elections."
								   )));
		}
		
		//check if elections exist and if user is allowed to vote in this country
		$query = "SELECT type FROM election_info WHERE election_id = '$election_id' AND country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND ended = 0";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed to vote in these elections."
								   )));
		}
		$row = $result->fetch_row();
		list($type) = $row;
		
		//check candidate id
		if($type == 1) {
			$query = "SELECT * FROM president_elections WHERE election_id = '$election_id' AND candidate_id = '$candidate_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Candidate doesn't exist."
									   )));
			}
		}
		else {
			if(strlen($candidate_id) < 10) {//party id always will be > 20
				$query = "SELECT * FROM congress_elections WHERE election_id = '$election_id' AND candidate_id = '$candidate_id'";
			}
			else {
				$query = "SELECT * FROM congress_elections WHERE election_id = '$election_id' AND party_id = '$candidate_id'";
			}
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Candidate doesn't exist."
									   )));
			}
		}
		
		//check if not voted already
		$query = "SELECT * FROM users_voted WHERE user_id = '$user_id' AND election_id = '$election_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You already voted in these elections."
								   )));
		}

		if($conn->query($query)) {
			if($type == 1) {
				$query = "UPDATE president_elections SET yes = (SELECT * FROM (SELECT yes FROM president_elections 
						  WHERE election_id = '$election_id' AND candidate_id = '$candidate_id') AS temp) + 1
						  WHERE election_id = '$election_id' AND candidate_id = '$candidate_id'";
			}
			else if($type == 3) {
				//cannot vote for self in congress elections
				if(strlen($candidate_id) < 10) {//party id always will be > 20
					if($user_id == $candidate_id) {
						exit(json_encode(array(
							'success'=>false,
							'error'=>"You are not allowed to vote for yourself in elections to congress."
						)));
					}
				
					$query = "UPDATE congress_elections SET yes = (SELECT * FROM (SELECT yes FROM congress_elections 
							  WHERE election_id = '$election_id' AND candidate_id = '$candidate_id') AS temp) + 1
							  WHERE election_id = '$election_id' AND candidate_id = '$candidate_id'";
				}
				else {
					$query = "UPDATE congress_elections SET yes = (SELECT * FROM (SELECT yes FROM congress_elections 
							  WHERE election_id = '$election_id' AND party_id = '$candidate_id') AS temp) + 1
							  WHERE election_id = '$election_id' AND party_id = '$candidate_id'";
				}
			}
			$conn->query($query);
		}
		
		//vote and mark user as voted
		if(strlen($candidate_id) < 10) {
			$query = "INSERT INTO users_voted (election_id, user_id, candidate_id) VALUES('$election_id', '$user_id', '$candidate_id')";
		}
		else {
			$query = "INSERT INTO users_voted (election_id, user_id, party_id) VALUES('$election_id', '$user_id', '$candidate_id')";
		}
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg"=>"You have successfuly voted.",
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
	
	
	mysqli_close($conn);
?>