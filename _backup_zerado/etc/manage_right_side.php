<?php
	//Description: Manage right side
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id)
	
	$profile_id =  htmlentities(stripslashes(trim($_POST['profile_id'])), ENT_QUOTES);
	$accept_decline =  htmlentities(stripslashes(trim($_POST['accept_decline'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if($action == "add_to_friends") {
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"User doesn't exists."
								  )));
		}
		
		//check if not already friends
		$query = "SELECT * FROM friends WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
				   (user_id = '$profile_id' AND friend_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This user is already in your friend list."
								  )));
		}
		
		//check if request sent
		$query = "SELECT * FROM request_friendship WHERE (user_id = '$user_id' AND friend_id = '$profile_id') OR
				   (user_id = '$profile_id' AND friend_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This user didn't sent friendship request to you."
								  )));
		}
		
		//get user name
		$query = "SELECT user_name FROM users WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_name) = $row;
		
		if($accept_decline == 'accept') {
			//add to friends
			$query = "INSERT INTO friends VALUES('$profile_id', '$user_id')";
			if($conn->query($query)) {
				$query = "DELETE FROM request_friendship WHERE friend_id = '$user_id' AND user_id = '$profile_id'";
				$conn->query($query);
				echo json_encode(array('success'=>true,
									   'msg'=>"Added."
									  ));
				$notification = "$user_name accepted your friendship request.";
				sendNotification($notification, $profile_id);
			}
			else {
				echo json_encode(array('success'=>false,
									   'error'=>"Error."
									  ));
			}
		}
		else {
			$query = "DELETE FROM request_friendship WHERE friend_id = '$user_id' AND user_id = '$profile_id'";
			$conn->query($query);
			echo json_encode(array('success'=>true,
								   'msg'=>"Declined."
								  ));
			$notification = "$user_name declined your friendship request.";
			sendNotification($notification, $profile_id);
		}
	}

	$conn->close();
?>