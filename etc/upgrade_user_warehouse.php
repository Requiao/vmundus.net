<?php
//Description: Upgrade user warehouse.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');

	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	$amount =  htmlentities(stripslashes(strip_tags(trim($_POST['amount']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	$reply = array();
	
	if($amount == "x10") {
		$UPGRADE_QUANTITY = 10;
	}
	else {
		$UPGRADE_QUANTITY = 1;
	}
	
	$CAPACITY_UPGRADE = 100 * $UPGRADE_QUANTITY;
	
	if($action == 'get_info') {
		$reply[0]['msg'] = "After an upgrade, the warehouse will have $CAPACITY_UPGRADE extra space.";
		
		$query = "SELECT product_name, product_icon, amount FROM product_info pi, building_upgrade bu 
				  WHERE bu.product_id = pi.product_id 
				  AND building_id = 17";
		$result =$conn->query($query);
		while($row = $result->fetch_row()) {
			list($product_name, $product_icon, $amount) = $row;
			array_push($reply, array("product_name"=>$product_name, "product_icon"=>$product_icon, 
									 "amount"=>($amount * $UPGRADE_QUANTITY)));
		}
		
		echo json_encode($reply);
	}
	else if($action == "upgrade") {
		$query = "SELECT capacity FROM user_warehouse WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($ware_capacity) = $row;
		//max capacity is 900 000. if more exit.
		if(($ware_capacity + $CAPACITY_UPGRADE) > 900000) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Maximum storage volume is 900 000."
								  )));
		}
		
		//determine if user has enough resources
		$query = "SELECT amount, product_id FROM building_upgrade WHERE building_id = 17";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			$amount = ($amount * $UPGRADE_QUANTITY);
			$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND amount >= '$amount' 
					  AND product_id = '$product_id'";
			$resultr = $conn->query($query);
			if($resultr->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You don't have enough resources."
									  )));
			}
		}
		
		/* get resources for upgrade */
		$query = "SELECT amount, product_id FROM building_upgrade WHERE building_id = 17";
		$result = $conn->query($query);
		$products_used = array();
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			$amount = ($amount * $UPGRADE_QUANTITY);
			$query = "UPDATE user_product SET amount = (SELECT amount - '$amount' FROM 
					 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp)  
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
			$conn->query($query);
			
			array_push($products_used, array('amount'=>$amount, 'product_id'=>$product_id));
		}
	
		// update user warehouse table
		$query = "UPDATE user_warehouse SET capacity = (SELECT capacity FROM 
				 (SELECT capacity FROM user_warehouse WHERE user_id = '$user_id') AS temp) + '$CAPACITY_UPGRADE'
				  WHERE user_id = '$user_id'";
		$conn->query($query);
		
		//get total products
		$query = "SELECT SUM(amount) FROM user_product WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total) = $row;

		echo json_encode(array('success'=>true,
							   'msg'=>"Warehouse successfuly upgraded.",
							   'capacity_add'=>$CAPACITY_UPGRADE,
							   'products_used'=>$products_used,
							   'total'=>$total
							  ));
	}

	
	$conn->close;
?>