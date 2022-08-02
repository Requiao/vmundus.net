<?php
	//Description: Logout.
	session_start();

	include('../connect_db.php');
	
	$user_id = $_SESSION['user_id'];

	//disable remember_me
	$query = "UPDATE remember_me SET is_active = FALSE WHERE user_id = '$user_id'";
	if(!$conn->query($query)) {
		exit(json_encode(array(
			'success'=>false,
			'error'=>'Failed to logout.'
		)));
	}

	$query = "UPDATE session_table SET is_active = FALSE WHERE user_id = '$user_id'";
	if(!$conn->query($query)) {
		exit(json_encode(array(
			'success'=>false,
			'error'=>'Failed to logout.'
		)));
	}

	session_unset();
	session_regenerate_id(true);
	session_destroy();
	header('Location: /~petro/vmundus/index');
?>