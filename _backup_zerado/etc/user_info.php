<?php
	//Description: missions.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/correct_date_time.php');
	include('../php_functions/add_points_to_user_experience.php'); //addPointsToUserExperience($user_id, $points)
	
	$mission_id =  htmlentities(stripslashes(strip_tags(trim($_POST['mission_id']))), ENT_QUOTES);
	$mission_level =  htmlentities(stripslashes(strip_tags(trim($_POST['mission_level']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	//get timezone id
	if($action == 'get_daily_mission_rewards' || $action == 'daily_mission_details') {
		$query = "SELECT hours FROM timezones WHERE timezone_id = 
				 (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($hours) = $row;
		$date = date('Y-m-d');	
		$time = date('04:00:00');
		
		$after_four_am = date('Y-m-d H:i:s', strtotime("$date $time -$hours hours"));
		$before_four_am = date('Y-m-d H:i:s', strtotime("$date $time + 1 days -$hours hours"));
		if(strtotime(date('Y-m-d H:i:s', strtotime($after_four_am))) > strtotime(date('Y-m-d H:i:s'))) {
			$after_four_am = date('Y-m-d H:i:s', strtotime("$after_four_am - 1 days"));
			$before_four_am = date('Y-m-d H:i:s', strtotime("$before_four_am - 1 days"));
		}
		else if(strtotime(date('Y-m-d H:i:s', strtotime($before_four_am))) < strtotime(date('Y-m-d H:i:s'))) {
			$after_four_am = date('Y-m-d H:i:s', strtotime("$after_four_am + 1 days"));
			$before_four_am = date('Y-m-d H:i:s', strtotime("$before_four_am + 1 days"));
		}
	}
	
	if($action == 'get_user_gold') {
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '1'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_gold) = $row;

		

		echo json_encode(array(
			"success"=>true,
			"user_gold"=>number_format($user_gold, '3', '.', ' ')
		));
	}
	else if($action == 'get_user_experience') {
		$query = "SELECT IFNULL(level_id, 0), up.experience, IFNULL(uxl.experience, 0),
				 (SELECT level_id FROM user_exp_levels ORDER BY level_id DESC LIMIT 1)
				  FROM user_profile up LEFT JOIN user_exp_levels uxl ON uxl.experience <= up.experience
				  WHERE up.user_id = '$user_id'
				  ORDER BY level_id DESC LIMIT 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($current_level, $experience, $prev_experience, $max_level) = $row;
		
		if($max_level == $current_level) {//reached max level
			$progress == 100;
			$next_experience = $prev_experience;
		}
		else {
			$query = "SELECT experience FROM user_exp_levels 
					  WHERE level_id = '$current_level' + '1' LIMIT 1";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($next_experience) = $row;
			
			$progress = round((100 / ($next_experience - $prev_experience)) * ($experience - $prev_experience), 2);
		}
		
		echo json_encode(array('success'=>true,
							   'progress'=>$progress,
							   'experience'=>$experience - $prev_experience,
							   'next_experience'=>$next_experience - $prev_experience,
							   'current_level'=>$current_level,
							  ));
	}
	else if($action == 'get_daily_mission_rewards') {
		//test mission
		if(!filter_var($mission_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission doesn't exist."
								  )));
		}
		$query = "SELECT mission_id, mission_name, mission_description, icon, exp_reward FROM daily_missions 
				  WHERE mission_id = '$mission_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($mission_id, $mission_name, $mission_description, $icon, $exp_reward) = $row;
		
		//mission levels
		if(!filter_var($mission_level, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission level doesn't exist."
								  )));
		}
		$query = "SELECT * FROM mission_levels WHERE mission_id = '$mission_id' AND mission_level = '$mission_level'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission level doesn't exist."
								  )));
		}
		
		//check if mission passed
		$query = "SELECT done, collected FROM user_daily_missions WHERE mission_id = '$mission_id' AND mission_level = '$mission_level'
				  AND user_id = '$user_id' AND TIMESTAMP(date, time) >= '$after_four_am' AND TIMESTAMP(date, time) < '$before_four_am'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission is incomplete."
								  )));
		}
		$row = $result->fetch_row();
		list($done, $collected) = $row;
		if(!$done) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission is incomplete.$done"
								  )));
		}
		
		if($collected) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You already collected reward for this mission"
								  )));
		}
		
		//check if enough room in the warehouse
		$query = "SELECT SUM(amount) FROM mission_rewards WHERE mission_id = '$mission_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_rewards) = $row;
		
		//determine if enough capacity in the warehouse
		$query = "SELECT capacity, SUM(amount) FROM user_warehouse uw, user_product up
				  WHERE uw.user_id = '$user_id' AND up.user_id = uw.user_id GROUP BY capacity;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($capacity, $warehouse_fill) = $row;
		if($capacity < ($warehouse_fill + $total_rewards)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough capacity in the warehouse."
								  )));
		}
		
		//get mission rewards
		$rewards = array();
		$query = "SELECT mr.product_id, amount, product_name, product_icon FROM mission_rewards mr, product_info pi
				  WHERE pi.product_id = mr.product_id AND mission_id = '$mission_id'";
		$result_rewards = $conn->query($query);
		while($row_rewards = $result_rewards->fetch_row()) {
			list($product_id, $amount, $product_name, $product_icon) = $row_rewards;
			array_push($rewards, array("amount"=>$amount, "product_name"=>$product_name, "product_icon"=>$product_icon));
			
			$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
						  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$amount' 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			else {
				$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$amount')";
			}
			$conn->query($query);
		}
		
		addPointsToUserExperience($user_id, $exp_reward);
		
		//update user reward
		$query = "UPDATE user_daily_missions SET collected = TRUE WHERE mission_id = '$mission_id' AND mission_level = '$mission_level'
				  AND user_id = '$user_id' AND TIMESTAMP(date, time) >= '$after_four_am' AND TIMESTAMP(date, time) < '$before_four_am'";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>"Reward successfully collected.",
							   'icon'=>$icon,
							   'exp_reward'=>$exp_reward,
							   'rewards'=>$rewards,
							  ));
	}
	else if($action == 'daily_mission_details') {
		if(!filter_var($mission_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission doesn't exist."
								  )));
		}
		
		//get mission details
		$query = "SELECT mission_id, mission_name, mission_description, icon, exp_reward FROM daily_missions 
				  WHERE mission_id = '$mission_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Mission doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($mission_id, $mission_name, $mission_description, $icon, $exp_reward) = $row;
		
		//get mission rewards
		$rewards = array();
		$query = "SELECT amount, product_name, product_icon FROM mission_rewards mr, product_info pi
				  WHERE pi.product_id = mr.product_id AND mission_id = '$mission_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($amount, $product_name, $product_icon) = $row;
			array_push($rewards, array("amount"=>$amount, "product_name"=>$product_name, "product_icon"=>$product_icon));
		}
		
		//get mission levels
		$levels = array();
		$query = "SELECT ml.progress, IFNULL(udm.progress, 0), ml.mission_level, IFNULL(udm.done, FALSE), IFNULL(udm.collected, FALSE)
				  FROM mission_levels ml LEFT JOIN user_daily_missions udm
				  ON udm.mission_id = ml.mission_id AND udm.mission_level = ml.mission_level
				  AND TIMESTAMP(date, time) >= '$after_four_am' AND TIMESTAMP(date, time) < '$before_four_am'
				  AND user_id = '$user_id'
				  WHERE ml.mission_id = '$mission_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($level_req, $user_progress, $mission_level, $done, $collected) = $row;
			if($mission_id == 1) {
				$user_progress = number_format($user_progress, 0, '.', ' ');
			}
			array_push($levels, array("level_req"=>$level_req, "user_progress"=>$user_progress, "mission_level"=>$mission_level,
									  "done"=>$done, "collected"=>$collected));
		}
		
		echo json_encode(array('success'=>true,
							   'mission_name'=>$mission_name,
							   'mission_description'=>$mission_description,
							   'icon'=>$icon,
							   'exp_reward'=>$exp_reward,
							   'rewards'=>$rewards,
							   'levels'=>$levels
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
?>