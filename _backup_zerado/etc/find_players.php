<?php
	//Description: Get players list.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');

	$player_name = htmlentities(stripslashes(strip_tags(trim($_POST['player_name']))), ENT_QUOTES);
	$from_days = htmlentities(stripslashes(strip_tags(trim($_POST['from_days']))), ENT_QUOTES);
	$to_days = htmlentities(stripslashes(strip_tags(trim($_POST['to_days']))), ENT_QUOTES);
	$country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);

	//test country_id
	if(!is_numeric($country_id)) {
		$country_id = 0;
	}
	$query = "SELECT * FROM country WHERE country_id = '$country_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		$country_id = 0;
	}

	//test player_name
	if(iconv_strlen($player_name) > 15) {
		exit(json_encode(array('msg'=>'Found 0 players.')));
	}
	if(empty($player_name) || iconv_strlen($player_name) == 0) {
		$player_name = "%";
	}
	else {
		$player_name = '%' . $player_name . '%';
	}

	//test from_days
	if(!is_numeric($from_days)) {
		$from_days = 0;
	}
	if($from_days < 0) {
		$from_days = 0;
	}
	if($from_days > 99999) {
		$from_days = 9999;
	}
	
	//test to_days
	if(!is_numeric($to_days)) {
		$to_days = 99999;
	}
	if($to_days < 0) {
		$to_days = 99999;
	}
	if($to_days > 99999) {
		$to_days = 99999;
	}
	
	//test to_days from_days
	if($from_days > $to_days) {
		$from_days = 0;
		$to_days = 99999;
	}

	//query
	if($country_id == 0) {
		$query = "SELECT u.user_id, user_name, user_image, flag, DATEDIFF(CURRENT_DATE, register_date)
				  FROM users u, user_profile up, country c
				  WHERE up.user_id = u.user_id AND c.country_id = up.citizenship AND
				  u.user_name LIKE '$player_name' AND DATEDIFF(CURRENT_DATE, register_date) >= '$from_days' 
				  AND DATEDIFF(CURRENT_DATE, register_date) <= '$to_days' LIMIT 100";
	}
	else {
		$query = "SELECT u.user_id, user_name, user_image, flag, DATEDIFF(CURRENT_DATE, register_date) 
				  FROM users u, user_profile up, country c
				  WHERE up.user_id = u.user_id AND c.country_id = up.citizenship AND
				  u.user_name LIKE '$player_name' AND DATEDIFF(CURRENT_DATE, register_date) >= '$from_days' 
				  AND DATEDIFF(CURRENT_DATE, register_date) <= '$to_days' AND citizenship = '$country_id' LIMIT 100";
	}
	$result = $conn->query($query);
	$x = 0;
	while($row = $result->fetch_row()) {
		list($user_id, $user_name, $user_image, $flag, $days_in_game) = $row;
		
		$players[$x]['user_id'] = $user_id;
		$players[$x]['user_name'] = $user_name;
		$players[$x]['user_image'] = $user_image;
		$players[$x]['flag'] = $flag;
		$players[$x]['days_in_game'] = $days_in_game;
		$x++;
	}
	if(count($players) == 0) {
		exit(json_encode(array('msg'=>'Found 0 players.')));
	}
	echo json_encode($players);
	
?>