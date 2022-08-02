<?php
	include('../connect_db.php');
	echo 'I see you.';
	exit;
	//$user_id = 1001;
	$ipaddress = $_SERVER['REMOTE_ADDR'];
	$query = "INSERT INTO temp_ip VALUES('$ipaddress', CURRENT_DATE, CURRENT_TIME)";
	mysqli_query($conn, $query);
	
	//delete everything from all chats if user is founder
	$query = "SELECT chat_id FROM chat_mod WHERE founder='$user_id'";
	$result = mysqli_query($conn, $query);
	while($row = mysqli_fetch_row($result)) {
		list($chat_id) = $row;
		
		$query = "DELETE FROM chat_members WHERE chat_id = '$chat_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM chat WHERE chat_id = '$chat_id'";
		mysqli_query($conn, $query);

		$query = "DELETE FROM favorite_chat WHERE chat_id = '$chat_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM moderators WHERE chat_id = '$chat_id'";
		mysqli_query($conn, $query);
		
		$query = "SELECT message_id FROM chat WHERE chat_id = '$chat_id'";
		$result = mysqli_query($conn, $query);
		while($row = mysqli_fetch_row($result)) {
			list($message_id) = $row;
			$query = "DELETE FROM modified_messages WHERE message_id = '$message_id'";
			mysqli_query($conn, $query);
		}
	}
	$query = "DELETE FROM chat_mod WHERE founder_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from chats if user is a member
	$query = "DELETE FROM chat WHERE from_user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM chat_members WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);

	$query = "DELETE FROM favorite_chat WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM moderators WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM modified_messages WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete recieved and sent mail
	$query = "DELETE FROM messages WHERE user_id = '$user_id' OR to_user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM unread_messages WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from notification
	$query = "DELETE FROM notification WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from product_market
	$query = "DELETE FROM product_market WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from user_currency
	$query = "DELETE FROM user_currency WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from user_product
	$query = "DELETE FROM user_product WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from user_warehouse
	$query = "DELETE FROM user_warehouse WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);

	//delete from people and related tables
	$query = "SELECT person_id FROM people WHERE user_id = '$user_id'";
	$result = mysqli_query($conn, $query);
	while($row = mysqli_fetch_row($result)) {
		list($person_id) = $row;
		$query = "DELETE FROM people_house WHERE person_id = '$person_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM work_journal WHERE person_id = '$person_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM work_status WHERE person_id = '$person_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM hired_workers WHERE person_id = '$person_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM region_war_members WHERE person_id = '$person_id'";
		mysqli_query($conn, $query);
	}

	$query = "DELETE FROM user_houses WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM people WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM user_born_bar WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete user companies and related data
	$query = "SELECT company_id FROM user_building WHERE user_id = '$user_id'";
	$result = mysqli_query($conn, $query);
	$for_companies = $result;
	while($row = mysqli_fetch_row($result)) {
		list($company_id) = $row;
		$query = "DELETE FROM job_market WHERE company_id = '$company_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM hired_workers WHERE company_id = '$company_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM company_market WHERE company_id = '$company_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM product_warehouse WHERE company_id = '$company_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM resource_warehouse WHERE company_id = '$company_id'";
		mysqli_query($conn, $query);
	}
	
	$query = "DELETE FROM user_building WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	while($row = mysqli_fetch_row($for_companies)) {
		list($company_id) = $row;
		$query = "DELETE FROM companies WHERE company_id = '$company_id'";
		mysqli_query($conn, $query);
	}
	
	//delete from monetary market
	$query = "DELETE FROM monetary_market WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from country_law_info
	$query = "DELETE FROM country_law_info WHERE proposed_by = '$user_id'";
	mysqli_query($conn, $query);
	
	//delete from president_elections
	$query = "DELETE FROM president_elections WHERE candidate_id = '$user_id'";
	mysqli_query($conn, $query);
	
	/* delete user personal info, must be last */
	$query = "DELETE FROM session_table WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM users_ip WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM user_profile WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
	
	$query = "DELETE FROM users WHERE user_id = '$user_id'";
	mysqli_query($conn, $query);
?>





















