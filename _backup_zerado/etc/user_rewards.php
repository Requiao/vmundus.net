<?php
	//Description: Check rewards for the user when he visits home page.
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$user_id = $_SESSION['user_id'];
	
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	$level_id =  htmlentities(stripslashes(strip_tags(trim($_POST['level']))), ENT_QUOTES);
	$tutorial_id =  htmlentities(stripslashes(strip_tags(trim($_POST['tutorial']))), ENT_QUOTES);
	
	if($action == 'reward_for_tutorial') {
		if($tutorial_id < 1 || $tutorial_id > 4 ) {
			exit(json_encode(array('success'=>false,
								   'error'=>false
								  )));
		}
		
		//check if rewarded
		$query = "SELECT * FROM tutorial_rewards WHERE user_id = '$user_id' AND tutorial_id = '$tutorial_id'";
		$result = $conn->query($query);
		if($result->num_rows >= 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>false
								  )));
		}
		
		//record reward
		$query = "INSERT INTO tutorial_rewards VALUES ('$tutorial_id', '$user_id', TRUE)";
		$conn->query($query);
		
		//reward
		$query = "INSERT INTO user_rewards VALUE(11, '$user_id', 0, CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		echo json_encode(array("success"=>true
							   ));
	}
	else if($action == 'regular_rewards') {
		//check if has rewards
		$query = "SELECT ur.type_id, name, SUM(pr.amount), capacity, (SELECT SUM(amount) FROM user_product WHERE user_id = '$user_id'),
				  date, time
				  FROM user_rewards ur, reward_types rt, product_rewards pr, user_warehouse uw
				  WHERE ur.user_id = '$user_id' AND rt.type_id = ur.type_id AND collected = FALSE 
				  AND pr.type_id = ur.type_id AND uw.user_id = ur.user_id GROUP BY type_id, date, time LIMIT 1";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>false
								  )));
		}
		$row = $result->fetch_row();
		list($type_id, $name, $reward_amount, $ware_capacity, $used_capacity, $date, $time) = $row;
		if(($used_capacity + $reward_amount) > $ware_capacity) {
			$need_space = round($used_capacity + $reward_amount - $ware_capacity, 2);
			exit(json_encode(array('success'=>false,
								   'error'=>'There are some rewards available for you to collect but you don\'t have' .
										    ' enough space in the warehouse.' .
										    ' Please make space for at least ' . $need_space . ' items and return to this page again.'
								  )));
		}
		
		$products = array();
		
		$query = "SELECT pi.product_id, product_name, product_icon, amount 
				  FROM product_info pi, product_rewards pr 
				  WHERE pi.product_id = pr.product_id AND type_id = '$type_id'";
		$result_reward = $conn->query($query);
		while($row_reward = $result_reward->fetch_row()) {
			list($product_id, $product_name, $product_icon, $amount) = $row_reward;
			
			//check if already has this product type
			$query = "SELECT * FROM user_product WHERE product_id = '$product_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 0) {
				$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$amount')";//land food
				$conn->query($query);
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
						  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$amount' 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
				$conn->query($query);
			}
			array_push($products, array('product_name'=>$product_name,
									    'product_icon'=>$product_icon,
									    'amount'=>$amount
									   ));
		}
		$query = "UPDATE user_rewards SET collected = TRUE WHERE user_id = '$user_id' AND type_id = '$type_id'
				  AND date = '$date' AND time = '$time'";
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "msg_head"=>$name,
							   "products"=>$products
							   ));
	}
	else if ($action == 'weekly_missions_rewards') {
		//check lvl id
		if(!filter_var($level_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You haven't reached this level yet."
								  )));
		}
		
		$query = "SELECT * FROM weekly_missions_base_rewards WHERE level_id = '$level_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								  'error'=>"You haven't reached this level yet."
								   )));
		}
		
		//get missions id
		$query = "SELECT mission_id FROM weekly_missions ORDER BY date DESC, TIME DESC LIMIT 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($mission_id) = $row;
		
		//check if not already collected
		$query = "SELECT * FROM collected_weekly_mission_rewards 
				  WHERE mission_id = '$mission_id' AND user_id = '$user_id' AND level_id = '$level_id'
				  AND collected = TRUE";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								  'error'=>"You already collected this reward."
								   )));
		}
		
		//check if can collect
		$query = "SELECT amount, wmr.product_id, product_icon, product_name 
				  FROM weekly_missions_rewards wmr, users_weekly_missions_progress uwmp, 
				  weekly_missions_base_rewards wmbr, product_info pi
				  WHERE wmr.level_id = '$level_id' AND wmbr.required_experience <= uwmp.experience_earned 
				  AND wmbr.level_id = wmr.level_id AND user_id = '$user_id' AND pi.product_id = wmr.product_id
				  AND wmr.mission_id = '$mission_id' AND uwmp.mission_id = wmr.mission_id";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								  'error'=>"You haven't reached this level yet."
								   )));
		}
		
		$row = $result->fetch_row();
		list($amount, $product_id, $product_icon, $product_name) = $row;
		
		//update user product
		//check if already has this product type
		$query = "SELECT * FROM user_product WHERE product_id = '$product_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$amount')";//land food
			$conn->query($query);
		}
		else {
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$amount' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			$conn->query($query);
		}
		
		$products = array();
		array_push($products, array('product_name'=>$product_name,
									'product_icon'=>$product_icon,
									'amount'=>$amount
								   ));
		
		//mark as collected the reward
		$query = "INSERT INTO collected_weekly_mission_rewards VALUES ('$mission_id', '$level_id', '$user_id', TRUE)";
		$conn->query($query);

		echo json_encode(array("success"=>true,
							   "msg_head"=>'Weekly missions reward.',
							   "products"=>$products
							   ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>'Invalid request.'
							  )));
	}
	mysqli_close($conn);
?>