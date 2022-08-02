<?php
	//Description: Manage coutrny. Quit from government
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	//check if governor
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
			exit(json_encode(array("success"=>false,
								   "error"=>"You're not a governor and not allowed to perform this action."
								   )));
		}
	}
	
	if($action == 'quit_from_gov') {
		if($position_id == 3) {
			$query = "DELETE FROM congress_members WHERE user_id = '$user_id'";
		}
		else {
			$query = "UPDATE country_government SET user_id = NULL WHERE user_id = '$user_id'";
			$conn->query($query);
			
			//get blog info
			$query = "SELECT blog_id FROM country_blogs WHERE country_id = '$country_id' AND position_id = '$position_id'";
			$result_blog = $conn->query($query);
			$row_blog = $result_blog->fetch_row();
			list($blog_id) = $row_blog;
			
			//remove access to blog
			$query = "UPDATE user_blog SET user_id = '1' WHERE blog_id = '$blog_id'";
			$conn->query($query);
			
			//check if at least one governor or congress have access to 'Change responsibilities' law
			$query = "SELECT COUNT(*) FROM government_country_responsibilities 
					  WHERE country_id = '$country_id' AND responsibility_id = 8 AND must_sign_vote = TRUE AND have_access = TRUE
					  AND (position_id IN (SELECT position_id FROM country_government 
					  WHERE country_id = '$country_id' AND user_id IS NOT NULL)
					  OR position_id IN (SELECT 3 FROM congress_members WHERE country_id = '$country_id' AND user_id IS NOT NULL))";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($governors_that_have_access) = $row;
			
			if($governors_that_have_access == 0) {
				$query = "UPDATE government_country_responsibilities SET must_sign_vote = TRUE 
						  WHERE country_id = '$country_id' AND position_id = 1 
						  AND responsibility_id = 8";
				$conn->query($query);
				
				$query = "UPDATE government_country_responsibilities SET have_access = TRUE 
						  WHERE country_id = '$country_id' AND position_id = 1 
						  AND responsibility_id = 8";
				$conn->query($query);
				
				$query = "UPDATE government_country_responsibilities SET must_sign_vote = TRUE 
						  WHERE country_id = '$country_id' AND position_id = 3 
						  AND responsibility_id = 8";
				$conn->query($query);
				
				$query = "UPDATE government_country_responsibilities SET have_access = TRUE 
						  WHERE country_id = '$country_id' AND position_id = 3 
						  AND responsibility_id = 8";
				$conn->query($query);
			}
		}
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"You have successfully resigned from the government."
								  ));
		}
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							   )));
	}
?>