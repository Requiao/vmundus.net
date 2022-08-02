<?php
//Description: Person details.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$person_id =  htmlentities(stripslashes(trim($_POST['person_id'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if(empty($person_id)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Select at least one Person."
							  )));
	}
	
	$summary = array();
	$people_array = explode(',', $person_id);
	
	for($i = 0; $i < count($people_array); $i++) {
		$person_id = $people_array[$i];
		
		//check person
		if(!is_numeric($person_id)) {
			array_push($summary, array("success"=>false,
											"person_id"=>$person_id,
											"error"=>"Person doesn't exist"
											));
			continue;
		}
		
		$query = "SELECT person_name, who FROM people WHERE person_id = '$person_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			array_push($summary, array("success"=>false,
									   "person_id"=>$person_id,
									   "error"=>"Person doesn't exist"
									  ));
			continue;
		}
		$row = $result->fetch_row();
		list($person_name, $who) = $row;
	
		//check if person is still hired and belongs to the right user
		$query = "SELECT * FROM hired_workers hw WHERE person_id = '$person_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			array_push($summary, array("success"=>false,
									   "person_id"=>$person_id,
									   "error"=>"$person_name is already unemployed."
									  ));
			continue;
		}
		
		$query ="DELETE FROM hired_workers WHERE person_id = '$person_id'";
		if($conn->query($query)) {
			if($who == 'working') {
				$query = "Update people SET who = 'available' WHERE person_id = '$person_id'";
				$conn->query($query);
			}
			
			array_push($summary, array("success"=>true,
									   "person_id"=>$person_id,
									   "msg"=>"$person_name " . $lang['successfully_retired_from_his_job'],
									   "status"=>$who
									  ));
		}
	}
	
	echo json_encode(array("success"=>true,
						   "summary"=>$summary
						  ));

	mysqli_close($conn);
?>