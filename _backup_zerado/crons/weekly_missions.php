<?php
	//runs every week minute each hour
	//Description: Record country population

	include('/var/www/html/connect_db.php');
	include('/var/www/html/php_functions/get_time_for_id.php'); //function getTimeForId().
	
	/* create new weekly mission */
	$query = "SELECT mission_id FROM weekly_missions WHERE DATE_ADD(date, INTERVAL 1 DAY) >= CURRENT_DATE";
	$result = $conn->query($query);
	if($result->num_rows <= 0) {
		$mission_id = getTimeForId();

		//create mission
		$query = "INSERT INTO weekly_missions VALUES ('$mission_id', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		//get total products id for random id
		$query = "SELECT COUNT(*) FROM product_info";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_products) = $row;
		
		//get gold price
		$query = "SELECT price FROM product_price WHERE product_id = 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($gold_price) = $row;
		
		//generate rewards
		$query = "SELECT level_id, gold_rewards, required_experience FROM weekly_missions_base_rewards";
		$result_rewards = $conn->query($query);
		while($row_rewards = $result_rewards->fetch_row()) {
			list($level_id, $gold_rewards, $required_experience) = $row_rewards;
			
			do {
				$product_id = mt_rand(1, $total_products);
			} while($product_id == 5 || $product_id == 8 || $product_id == 19 || $product_id == 20);
			
			//get amount of products that can be bought for gold_rewards
			$query = "SELECT price FROM product_price WHERE product_id = '$product_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($product_price) = $row;
			
			$amount = round(($gold_price * $gold_rewards) / $product_price, 2);

			//create mission
			$query = "INSERT INTO weekly_missions_rewards VALUES ('$mission_id', '$level_id', '$product_id', '$amount')";
			$conn->query($query);
			
		}
	}
?>