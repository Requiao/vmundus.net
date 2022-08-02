<?php
	//Description:Submit application to participate in elections or stop participating.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_user_level.php');//getUserLevel($user_id); returns user level
	
	$elections_id =  htmlentities(stripslashes(trim($_POST['election_id'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if(!is_numeric($elections_id)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Elections doesn't exists"
							   )));
	}
	
	if($action == "apply") {
		//must have at least level 5
		if(getUserLevel($user_id) < 5) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You must have at least level 5 in order to join the elections."
								   )));
		}
		
		//check if user is a citizen of this country, elections haven't started/eneded yet.
		$query = "SELECT election_id, type FROM election_info WHERE election_id = '$elections_id' AND country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND can_participate = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed anymore to participate in these election."
								   )));
		}
		$row = $result->fetch_row();
		list($elections_id, $type) = $row;

		//check if not already participating
		//check if applied for president elections
		$query = "SELECT * FROM president_elections WHERE election_id = (SELECT election_id FROM election_info  WHERE country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND can_participate = TRUE AND type = 1) 
				  AND candidate_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You're already participating in presidential elections."
								   )));
		}
		
		//check if applied for conrgess elections
		$query = "SELECT * FROM congress_elections WHERE election_id = (SELECT election_id FROM election_info  WHERE country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND can_participate = TRUE AND type = 3) 
				  AND candidate_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You're already participating in congress elections."
								   )));
		}
		
		//check if applied for congress elections from party
		$query = "SELECT * FROM party_congress_candidates WHERE candidate_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You're already participating in congress elections from the party."
								   )));
		}
		
		//record user's candidature
		if($type == 1) {
			$query = "INSERT INTO president_elections VALUES('$elections_id', '$user_id', 0)";
		}
		else if($type == 3) {
			$query = "INSERT INTO congress_elections VALUES('$elections_id', '$user_id', NULL, 0)";
		}
		
		if($conn->query($query)) {
			if($type == 1) {
				$query = "SELECT user_name, u.user_id, user_image FROM users u, user_profile up, president_elections 
						  WHERE election_id = '$elections_id' AND u.user_id = '$user_id' AND up.user_id = u.user_id";
			}
			else if($type == 3) {
				$query = "SELECT user_name, u.user_id, user_image FROM users u, user_profile up, congress_elections 
						  WHERE election_id = '$elections_id' AND u.user_id = '$user_id' AND up.user_id = u.user_id";
			}
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($candidate_name, $candidate_id, $candidate_image) = $row;
			
			$history = array();
			$query = "SELECT name, days, country_name, c.country_id 
					  FROM country_government_history cgh, government_positions gp, country c
					  WHERE user_id = '$candidate_id' AND cgh.position_id = gp.position_id AND c.country_id = cgh.country_id
					  ORDER BY days DESC LIMIT 15";
			$result_hist = $conn->query($query);
			while($row_hist = $result_hist->fetch_row()) {
				list($position_name, $days, $country_name, $country_id) = $row_hist;
				array_push($history, array("success"=>true, "position_name"=>$position_name, 
							"country_name"=>$country_name, "days"=>"$days days", "country_id"=>$country_id));
			}
			echo json_encode(array("success"=>true,
								   "msg"=>"You have successfully applied to participate in elections.",
								   "candidate_name"=>$candidate_name,
								   "candidate_id"=>$candidate_id,
								   "candidate_image"=>$candidate_image,
								   "elections_id"=>$elections_id,
								   "history"=>$history
								  ));
		}
	}
	else if($action == "cancel") {
		//check if still alowed to cancel candidature.
		$query = "SELECT election_id, type FROM election_info WHERE election_id = '$elections_id' AND country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND can_participate = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You are not allowed anymore to cancel your candidature from these elections."
								   )));
		}
		$row = $result->fetch_row();
		list($elections_id, $type) = $row;
		
		if($type == 1) {
			$query = "DELETE FROM president_elections WHERE election_id = '$elections_id' AND candidate_id = '$user_id'";
			$conn->query($query);
		}
		else if($type == 3) {
			$query = "DELETE FROM congress_elections WHERE election_id = '$elections_id' AND candidate_id = '$user_id'";
			$conn->query($query);
		}
		echo json_encode(array("success"=>true,
							   "msg"=>"You have successfully taken off your candidature from elections."
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
	
	mysqli_close($conn);
?>