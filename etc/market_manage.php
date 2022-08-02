<?php
	//Description: Manage market
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../paypal/add_workers.php');//addWorkers($num_workers, $profile_id, $skill_lvl, $combat_skill, $purchase_id)
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php');//sendNotification($notification, $user_id).
	
	//$purchase_id =  htmlentities(stripslashes(strip_tags(trim($_POST['id']))), ENT_QUOTES);
	//$quantity = htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	$PER_DAY_COLLECT_LIMIT = 25;
	if($action == 'collect_user_rewards') {
		//check quantity
		/*if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Quantity must be more than 0 and be a whole number."
			)));
		}
		if($quantity > 100 && $quantity < 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Quantity must be more than 0 and less than or equal to 100."
			)));
		}*/

		//check if has rewards
		$query = "SELECT available, collected, date_collected, time_collected 
				  FROM user_gold_rewards WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"You don't have rewards to collect."
			)));
		}
		$row = $result->fetch_row();
		list($available, $collected, $date_collected, $time_collected) = $row;

		//can collect only in a X amount of time
		$current_date_time = strtotime(date('Y-m-d H:i:s'));
		$can_collect_at = strtotime(date('Y-m-d H:i:s', strtotime("$date_collected $time_collected +20 hours")));

		if($can_collect_at > $current_date_time) {
			$date1 = new DateTime(date('Y-m-d H:i:s', $current_date_time));
			$date2 = new DateTime(date('Y-m-d H:i:s', $can_collect_at));
			$diff = date_diff($date1,$date2);
			$days = $diff->format("%a");
			$time = $diff->format("%H:%I:%S");

			exit(json_encode(array(
				"success"=>false,
				"error"=>"You will be able to collect in $days days $time."
			)));
		}

		if($available <= 0) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"You don't have any Gold tp collect."
			)));
		}

		$can_collect_quantity = 0;
		if($available < $PER_DAY_COLLECT_LIMIT) {
			$can_collect_quantity = $available;
		}
		else {
			$can_collect_quantity = $PER_DAY_COLLECT_LIMIT;
		}

		//check if enough room in the warehouse
		$query = "SELECT SUM(amount) AS total_fill, (SELECT capacity FROM user_warehouse 
                  WHERE user_id = '$user_id') AS warehouse_capacity
                  FROM user_product WHERE user_id = '$user_id'";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        list($total_fill, $warehouse_capacity) = $row;
        $need_space = ($total_fill + $can_collect_quantity) - $warehouse_capacity;
        if($need_space > 0) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You do not have enough room in the warehouse to store Gold." .
						 " You need $need_space extra space."
            )));
        }

		//reward with gold
        $query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '1'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = amount + '$can_collect_quantity' 
					  WHERE user_id = '$user_id' AND product_id = '1'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', '1', '$can_collect_quantity')";
        }
		$conn->query($query);
		
		$query = "UPDATE user_gold_rewards SET date_collected = CURRENT_DATE WHERE user_id = '$user_id'";
		$conn->query($query);

		$query = "UPDATE user_gold_rewards SET time_collected = CURRENT_TIME WHERE user_id = '$user_id'";
		$conn->query($query);

		$query = "UPDATE user_gold_rewards SET available = available - '$can_collect_quantity' WHERE user_id = '$user_id'";
		$conn->query($query);

		$query = "UPDATE user_gold_rewards SET collected = collected + '$can_collect_quantity' WHERE user_id = '$user_id'";
		$conn->query($query);


		echo json_encode(array(
			"success"=>true,
			"msg"=>"You have successfully collected $can_collect_quantity Gold.",
			"collected"=>number_format($collected + $can_collect_quantity, '2', '.', ' '),
			"available"=>number_format($available - $can_collect_quantity, '2', '.', ' ')
		));
	}

	/*if($action == 'if_bought_before') {
		$query = "SELECT item_name, purchase_id FROM purchases WHERE user_id = '$user_id' AND informed = false";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			$purchase = array();
			while($row = $result->fetch_row()) {
				list($item_name, $purchase_id) = $row;
				array_push($purchase, array("item_name"=>$item_name));
				$query = "UPDATE purchases SET informed = TRUE WHERE purchase_id = '$purchase_id'";
				$conn->query($query);
			}
			
			echo json_encode(array("success"=>true,
								   "msg_head"=>"Thank You!",
								   "msg"=>"You have successfully bought the following items:",
								   "purchase"=>$purchase
								  ));
		}
		else {
			exit(json_encode(array("success"=>false
								   )));
		}
	}
	else if($action == 'get_leftover') {
		$query = "SELECT item_number, item_name, type FROM purchases p, market_offers mo
				  WHERE user_id = '$user_id' AND item_number = offer_id AND purchase_id = '$purchase_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"This is not your purchase."
								   )));
		}
		
		$row = $result->fetch_row();
		list($item_number, $item_name, $type) = $row;
		
		if($type == 'person_offer_details') {
			$query = "SELECT wpd.quantity_left, skill_lvl, combat_skill FROM person_offer_details pod, workers_purchase_details wpd
					  WHERE offer_id = '$item_number' AND purchase_id = '$purchase_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($num_workers, $skill_lvl, $combat_skill) = $row;
			
			$left_workers = addWorkers($num_workers, $user_id, $skill_lvl, $combat_skill, $purchase_id);
			
			//update purchase
			$query = "UPDATE workers_purchase_details SET quantity_left = '$left_workers' WHERE purchase_id = '$purchase_id'";
			$conn->query($query);
		}
		if($num_workers == $left_workers) {
			$reply = array("success"=>false,
						   "error"=>"You don't have enough room in your houses."
						  );
		}
		else {
			$received_workers = $num_workers - $left_workers;
			$reply = array("success"=>true,
						   "msg"=>"You have successfully received $received_workers worker/s.",
						   "all"=>$left_workers?false:true,
						   "new_msg"=>"You still have $left_workers persons from $item_name"
						  );
		}
		echo json_encode($reply);
	}*/
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							  )));
	}
?>