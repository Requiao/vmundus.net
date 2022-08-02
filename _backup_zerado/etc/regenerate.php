<?php
//Description: Regenerate person's energy.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$person_id =  htmlentities(stripslashes(trim($_POST['person_id'])), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(trim($_POST['product_id'])), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(trim($_POST['quantity'])), ENT_QUOTES); //number of times to use same food
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	$RECOVERY = 10;//do not change. 1 product recovers 10 energy
	
	$purpose = 3; //3 is food.
	if($action == 'get_regenerate_info') { // select products available for energy recover
		if(!is_numeric($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}
		$query = "SELECT up.product_id, amount, product_name, product_icon
				  FROM user_product up, product_info pi
				  WHERE user_id = '$user_id' AND purpose = '$purpose'
				  AND up.product_id = pi.product_id";
		$result = $conn->query($query);
		$recovery_info = array();
		while($row = $result->fetch_row()) {
			list($product_id, $amount, $product_name, $product_icon) = $row;
			array_push($recovery_info, array("product_id"=>$product_id, "amount"=>$amount, "product_name"=>$product_name, 
										"product_icon"=>$product_icon, "recovers"=>$RECOVERY, "person_id"=>$person_id));
		}
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['chose_how_you_want_to_recover_persons_energy'],
							   "recovery"=>$recovery_info
							  ));
	}
	elseif($action == 'recover_energy'){//recover energy
		if(!is_numeric($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}

		//check if product exists
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exists."
								  )));
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exists."
								  )));
		}
		
		//test quantity
		if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['the_number_of_food_you_want_to_consume_must_be_a_number_and_between_1_and_10']
								  )));
		}
		if($quantity > 10 || $quantity < 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['the_number_of_food_you_want_to_consume_must_be_a_number_and_between_1_and_10']
								  )));
		}
		
		//get user food
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_this_product_in_your_warehouse']
								  )));
		}
		$row = $result->fetch_row();
		list($user_food) = $row;
		if($user_food < 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_enough_food_in_the_warehouse']
								  )));
		}
		
		//max energy
		$query = "SELECT energy FROM energy_consumption WHERE cons_id = 5";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_energy) = $row;
		
		//how many energy can recover
		$query = "SELECT energy FROM energy_consumption WHERE cons_id = 2";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($recovery_limit) = $row;
		
		$used_food = 0;
		$query = "SELECT recovered_energy, energy FROM people WHERE user_id = '$user_id' AND person_id = '$person_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($recovered_energy, $energy) = $row;
		$new_energy = $energy;
		$can_recover = $recovery_limit - $recovered_energy;
		$recover_times = $quantity;

		if($recovered_energy >= $recovery_limit) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['today_you_cannot_recover_more_energy_for_this_person']
								  )));
		}
		
		while(($can_recover/$RECOVERY) > 0 && $recover_times > 0) {
			if(floor($user_food) <= $used_food) {//food ended
				break;
			}
			
			if($new_energy >= $max_energy) {
				break;
			}
			$new_energy += $RECOVERY;
			$can_recover -= $RECOVERY;
			
			$recover_times--;
			$used_food++;
		}
		
		if($new_energy == $energy) {//didn't recovered anything
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['this_person_already_has_full_energy']
								  )));
		}
		
		$recovered_energy += $new_energy - $energy;
		$query = "UPDATE people SET energy = '$new_energy' WHERE person_id = '$person_id'";
		if($conn->query($query)) {
			$query = "UPDATE people SET recovered_energy = '$recovered_energy' WHERE person_id = '$person_id'";
			$conn->query($query);
		}
		$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$used_food' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		$total_recovery = $used_food * $RECOVERY;
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['you_have_successfully_recovered_energy_in_total_of'] . ' ' . $total_recovery,
							   "max_energy"=>$max_energy,
							   "person_id"=>$person_id,
							   "new_energy"=>$new_energy
							  ));
	}
	else if($action == 'recover_during_battle') { //recover energy w/o amount
		if(!is_numeric($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}

		//check if product exists
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exists."
								  )));
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exists."
								  )));
		}
		
		//get user food
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_enough_food_in_the_warehouse']
								  )));
		}
		$row = $result->fetch_row();
		list($user_food) = $row;
		if($user_food < 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_enough_food_in_the_warehouse']
								  )));
		}
		
		//max energy
		$query = "SELECT energy FROM energy_consumption WHERE cons_id = 5";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_energy) = $row;
		
		//how many energy can recover
		$query = "SELECT energy FROM energy_consumption WHERE cons_id = 2";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($recovery_limit) = $row;
		
		$used_food = 0;
		$query = "SELECT recovered_energy, energy FROM people WHERE user_id = '$user_id' AND person_id = '$person_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Person doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($recovered_energy, $energy) = $row;
		$new_energy = $energy;
		$can_recover = $recovery_limit - $recovered_energy;

		if($recovered_energy >= $recovery_limit) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['today_you_cannot_recover_more_energy_for_this_person']
								  )));
		}

		while(($can_recover/$RECOVERY) > 0) {
			if(floor($user_food) <= $used_food) {//food ended
				break;
			}
			
			if($new_energy >= $max_energy) {
				break;
			}
			$new_energy += $RECOVERY;
			$can_recover -= $RECOVERY;
			
			$used_food++;
		}
		
		if($new_energy == $energy) {//didn't recovered anything
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['this_person_already_has_full_energy']
								  )));
		}
		
		$recovered_energy += $new_energy - $energy;
		$query = "UPDATE people SET energy = '$new_energy' WHERE person_id = '$person_id'";
		if($conn->query($query)) {
			$query = "UPDATE people SET recovered_energy = '$recovered_energy' WHERE person_id = '$person_id'";
			$conn->query($query);
		}
		$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$used_food' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		echo json_encode(array("success"=>true,
							   "max_energy"=>$max_energy,
							   "person_id"=>$person_id,
							   "new_energy"=>$new_energy,
							   "left_food"=>number_format($user_food - $used_food, 2, '.', ' ')
							  ));
	}
	else if($action == 'recover_for_all') { //recover energy for all people
		if(empty($person_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['select_at_least_one_person']
								  )));
		}
		
		//check if product exists
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exists."
								  )));
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exists."
								  )));
		}
		
		//test quantity
		if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['the_number_of_food_you_want_to_consume_must_be_a_number_and_between_1_and_10']
								  )));
		}
		if($quantity > 10 || $quantity < 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['the_number_of_food_you_want_to_consume_must_be_a_number_and_between_1_and_10']
								  )));
		}
		
		//get user food
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_this_product_in_your_warehouse']
								  )));
		}
		$row = $result->fetch_row();
		list($user_food) = $row;
		if($user_food < 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_enough_food_in_the_warehouse']
								  )));
		}
		
		//max energy
		$query = "SELECT energy FROM energy_consumption WHERE cons_id = 5";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($max_energy) = $row;
		
		//how many energy can recover
		$query = "SELECT energy FROM energy_consumption WHERE cons_id = 2";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($recovery_limit) = $row;
		
		$used_food = 0;
		$person_rec = array();
		
		$people_array = explode(',', $person_id);
		for($x = 0; $x < count($people_array); $x++) {
			$person_id = $people_array[$x];
			if(!is_numeric($person_id)) {
				exit(json_encode(array('success'=>false,
									   'error'=>"Person doesn't exist"
									  )));
			}
			
			//select person details
			$query = "SELECT recovered_energy, energy FROM people WHERE user_id = '$user_id' AND person_id = '$person_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				break;
			}
			$row = $result->fetch_row();
			list($recovered_energy, $energy) = $row;
			$new_energy = $energy;
			$can_recover = $recovery_limit - $recovered_energy;
			$recover_times = $quantity;
			if($can_recover == 0) {//cannot recover more
				continue;
			}
			while(($can_recover/$RECOVERY) > 0 && $recover_times > 0) {
				if(floor($user_food) <= $used_food) {//food ended
					break;
				}
				
				if($new_energy >= $max_energy) {
					break;
				}
				$new_energy += $RECOVERY;
				$can_recover -= $RECOVERY;
				
				$recover_times--;
				$used_food++;
			}
			
			if($new_energy == $energy) {//didn't recovered anything
				continue;
			}
			
			$recovered_energy += $new_energy - $energy;
			$query = "UPDATE people SET energy = '$new_energy' WHERE person_id = '$person_id'";
			if($conn->query($query)) {
				$query = "UPDATE people SET recovered_energy = '$recovered_energy' WHERE person_id = '$person_id'";
				$conn->query($query);
				
				array_push($person_rec, array("person_id"=>$person_id, "new_energy"=>$new_energy));
			}
		}
		$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
				  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$used_food' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		$total_recovery = $used_food * $RECOVERY;
		
		echo json_encode(array("success"=>true,
							   "msg"=>$lang['you_have_successfully_recovered_energy_in_total_of'] . ' ' . $total_recovery,
							   "max_energy"=>$max_energy,
							   "left_food"=>number_format($user_food - $used_food, 2, '.', ' '),
							   "person_rec"=>$person_rec
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
	
	mysqli_close($conn);
?>