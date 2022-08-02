<?php
	//Description: Manage corporation.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$corp_name =  htmlentities(stripslashes(strip_tags(trim($_POST['corp_name']))), ENT_QUOTES);
	$corp_abbr =  htmlentities(stripslashes(strip_tags(trim($_POST['corp_abbr']))), ENT_QUOTES);
	$corporation_id =  htmlentities(stripslashes(strip_tags(trim($_POST['corp_id']))), ENT_QUOTES);
	$offer_id =  htmlentities(stripslashes(strip_tags(trim($_POST['offer_id']))), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$currency_id =  htmlentities(stripslashes(strip_tags(trim($_POST['currency_id']))), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$amount =  htmlentities(stripslashes(strip_tags(trim($_POST['amount']))), ENT_QUOTES);
	$player_name =  htmlentities(stripslashes(strip_tags(trim($_POST['player_name']))), ENT_QUOTES);
	$member_id =  htmlentities(stripslashes(strip_tags(trim($_POST['member_id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if($action == 'expel_corp_member') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT corporation_name, manager_id 
				  FROM corporations WHERE corporation_id = '$corporation_id' AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($corporation_name, $manager_id) = $row;

		//check member_id
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Member doesn't exist.")));
		}

		//check if user is a member
		$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This user is not a member of this corporation."
			)));
		}

		if($manager_id == $member_id) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Manager cannot be expelled."
			)));
		}

		//expel
		$query = "DELETE FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$member_id'";
		if($conn->query($query)) {
			//notify member
			$notification = "You have been expelled from $corporation_name corporation.";
			sendNotification($notification, $member_id);

			echo json_encode(array(
				'success'=>true,
				'msg'=>"User have been successfully expelled from this corporation."
			));
		}
		else {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	else if($action == 'reject_invitation') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT corporation_name, manager_id FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($corporation_name, $manager_id) = $row;

		//check if have been invited
		$query = "SELECT * FROM corporation_members_invitations 
				  WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You haven't been invited."
			)));
		}

		//get user_name
		$query = "SELECT user_name FROM users WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_name) = $row;

		//reject
		$query = "DELETE FROM  corporation_members_invitations 
				  WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		if($conn->query($query)) {
			//notify manager
			$notification = "User $user_name rejected your invitation to $corporation_name corporation.";
			sendNotification($notification, $manager_id);

			echo json_encode(array(
				'success'=>true,
				'msg'=>"Invitation have been rejected.",
			));
		}
		else {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	else if($action == 'join_corporation') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT corporation_name, manager_id FROM corporations 
				  WHERE corporation_id = '$corporation_id' AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($corporation_name, $manager_id) = $row;

		//check if user in not already a member
		$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You are already a member of this corporation."
			)));
		}

		//check if have been invited
		$query = "SELECT * FROM corporation_members_invitations 
				  WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You haven't been invited."
			)));
		}

		//total members
		$query = "SELECT COUNT(*) FROM corporation_members WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_members) = $row;
		if($total_members >= 15) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation can only have 15 members."
			)));
		}

		//get user_name
		$query = "SELECT user_name FROM users WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_name) = $row;

		//join
		$query = "INSERT INTO corporation_members VALUES ('$corporation_id', '$user_id')";
		if($conn->query($query)) {
			//delete invitation
			$query = "DELETE FROM corporation_members_invitations 
					  WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
			$conn->query($query);

			//notify new manager
			$notification = "User $user_name joined your $corporation_name corporation.";
			sendNotification($notification, $manager_id);

			$corp_info = getCorpInfo($corporation_id);

			echo json_encode(array(
				'success'=>true,
				'msg'=>"You have successfully joined this corporation.",
				'corp_info'=>$corp_info
			));
		}
		else {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	else if($action == 'invite_new_member') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT corporation_name FROM corporations 
				  WHERE corporation_id = '$corporation_id' AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($corporation_name) = $row;

		//check if manager
		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id' AND manager_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have control over this corporation"
			)));
		}

		//total members
		$query = "SELECT COUNT(*) FROM corporation_members WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_members) = $row;
		if($total_members >= 15) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation can only have 15 members."
			)));
		}


		//check user name
		strValidate($player_name, 3, 15, 'User name');
		$query = "SELECT user_id FROM users WHERE user_name = '$player_name'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"User with this name doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($member_id) = $row;

		//check if user in not already a member
		$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This user is already a member of this corporation."
			)));
		}

		//check if new member has appropriate level
		$query = "SELECT IFNULL(level_id, 0), open_at_level 
				  FROM level_locked_features llf, user_profile up LEFT JOIN user_exp_levels uxl ON uxl.experience <= up.experience
				  WHERE up.user_id = '$member_id' AND llf.feature_id = 1
				  ORDER BY level_id DESC LIMIT 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($member_level, $open_at_level) = $row;
		if($member_level < $open_at_level) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This member must have at least level $open_at_level."
			)));
		}

		//check if this invitation is already pending
		$query = "SELECT * FROM corporation_members_invitations 
				  WHERE corporation_id = '$corporation_id' AND user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Invitation for this user is already sent."
			)));
		}

		$query = "INSERT INTO corporation_members_invitations VALUES ('$corporation_id', '$member_id')";
		if($conn->query($query)) {
			//notify new member
			$notification = "You have been invited to $corporation_name corporation." .
				" Go to Economy->Corporations->Invitations to accept or decline this invitation.";
			sendNotification($notification, $member_id);

			echo json_encode(array(
				'success'=>true,
				'msg'=>"Invitation sent."
			));
		}
		else {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	else if($action == 'view_members') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id' AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}

		//check if manager
		$is_manager = false;
		$is_member = false;
		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id' AND manager_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$is_manager = true;
		}

		//check if member
		if(!$is_manager) {
			$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$is_member = true;
			}
		}

		if(!$is_manager && !$is_member) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have access to this corporation."
			)));
		}

		//get members
		$query = "SELECT u.user_id, user_name, user_image FROM corporation_members cm, users u, user_profile up
				  WHERE corporation_id = '$corporation_id' AND u.user_id = cm.user_id AND up.user_id = cm.user_id";
		$result_members = $conn->query($query);
		$members = [];
		while($row_members = $result_members->fetch_row()) {
			list($member_id, $member_name, $member_image) = $row_members;

			//get users invested and earned products
			$query = "SELECT pi.product_id, product_name, product_icon, SUM(collected), SUM(amount)
					  FROM 
					 (SELECT product_id, collected, 0 AS amount
					  FROM corporation_user_products cup
					  WHERE corporation_id = '$corporation_id' AND user_id = '$member_id'
					  UNION
					  SELECT product_id, 0 AS collected, amount
					  FROM corporation_user_invested cui
					  WHERE corporation_id = '$corporation_id' AND user_id = '$member_id') 
					  AS temp, product_info pi
					  WHERE pi.product_id = temp.product_id
					  GROUP BY product_id";
			$result = $conn->query($query);
			$products_info = [];
			while($row = $result->fetch_row()) {
				list($product_id, $product_name, $product_icon, $earned, $invested) = $row;

				array_push($products_info, array("product_id"=>$product_id, "product_name"=>$product_name, 
					"product_icon"=>$product_icon, "invested"=>number_format($invested, 2, '.', ' '), 
					"earned"=>number_format($earned, 2, '.', ' ')));
			}

			array_push($members, array("member_id"=>$member_id, "member_name"=>$member_name, 
				"member_image"=>$member_image, "products_info"=>$products_info));
		}
		echo json_encode(array(
			'success'=>true,
			'members'=>$members,
			"is_manager"=>$is_manager
		));
	}
	else if($action == 'leave_corp_info') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT corporation_name, manager_id FROM corporations 
				  WHERE corporation_id = '$corporation_id' AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($corporation_name, $manager_id) = $row;

		//check if manager
		$query = "SELECT * FROM corporations 
				  WHERE corporation_id = '$corporation_id' AND manager_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Managers cannot leave or disband corporations."
			)));
		}

		//check if member
		$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You are not a member of this corporation."
			)));
		}

		//check if has products to collect
		$query = "SELECT IFNULL(SUM(available), 0) FROM corporation_user_products
				  WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($available_prod) = $row;

		if($available_prod > 0) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You still have products to collect."
			)));
		}

		$query = "DELETE FROM corporation_members WHERE user_id = '$user_id' 
				  AND corporation_id = '$corporation_id'";
		if($conn->query($query)) {
			if($is_member) {
				//get user_name
				$query = "SELECT user_name FROM users WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($user_name) = $row;

				//notify new manager
				$notification = "$user_name left your $corporation_name corporation.";
				sendNotification($notification, 1);
			}

			echo json_encode(array(
				'success'=>true
			));
		}
		else {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Something went wrong."
			)));
		}
	}
	else if($action == 'giveaway_currency_info') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have control over this corporation"
			)));
		}
		
		$query = "SELECT manager_id FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have control over this corporation."
			)));
		}
		$row = $result->fetch_row();
		list($manager_id) = $row;
		
		//check if manager
		if($manager_id != $user_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough access."
								  )));
		}
		
		//is currency exist
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Currency doesn't exist"
			)));
		}
		$query = "SELECT curr.currency_id, currency_abbr, flag 
				  FROM country c, currency curr
				  WHERE c.country_id = curr.flag_id AND curr.currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Currency doesn't exist"
			)));
		}
		$row = $result->fetch_row();
		list($currency_id, $currency_abbr, $flag) = $row;

		//get corporation currency
		$query = "SELECT amount FROM corporation_currency WHERE corporation_id = '$corporation_id'
				  AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't have enough $currency_abbr."
			)));
		}
		$row = $result->fetch_row();
		list($available_amount) = $row;

		//get members
		$query = "SELECT u.user_id, user_name, user_image FROM corporation_members cm, users u, user_profile up
				  WHERE corporation_id = '$corporation_id' AND u.user_id = cm.user_id AND up.user_id = cm.user_id";
		$result_members = $conn->query($query);
		$members = [];
		while($row_members = $result_members->fetch_row()) {
			list($member_id, $member_name, $member_image) = $row_members;

			array_push($members, array("member_id"=>$member_id, "member_name"=>$member_name, 
				"member_image"=>$member_image)
			);
		}

		echo json_encode(array(
			'success'=>true,
			'members'=>$members,
			'currency_info'=>array("currency_id"=>$currency_id, "flag"=>$flag, 
								   "currency_abbr"=>$currency_abbr,
								   "available_amount"=>number_format($available_amount, '2', '.', ' '))
		));
	}
	else if ($action == 'giveaway_currency') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$query = "SELECT corporation_name, manager_id FROM corporations 
				  WHERE corporation_id = '$corporation_id' AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Corporation doesn't exist."
			)));
		}
		$row = $result->fetch_row();
		list($corporation_name, $manager_id) = $row;
		
		//check if manager
		if($manager_id != $user_id) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have enough access"
			)));
		}
		
		//is currency exist
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Currency doesn't exist"
								  )));
		}
		$query = "SELECT currency_abbr FROM currency WHERE currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Currency doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($currency_abbr) = $row;
		
		//is enough currency
		if(!filter_var($amount, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Amount must be more than 0 and be a whole number"
								  )));
		}
		$query = "SELECT amount FROM corporation_currency WHERE corporation_id = '$corporation_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough currency in the corporation."
								  )));
		}
		$row = $result->fetch_row();
		list($available_amount) = $row;
		
		if($available_amount < $amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough currency in the corporation."
								  )));
		}

		//check if user is a member
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Member doesn't exist."
			)));
		}
		$query = "SELECT user_name FROM corporation_members cm, users u, user_profile up
				  WHERE corporation_id = '$corporation_id' AND u.user_id = cm.user_id AND up.user_id = cm.user_id
				  AND cm.user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This user is not a member of this corporation."
			)));
		}
		$row = $result->fetch_row();
		list($member_name) = $row;
		
		//giveaway_currency
		$query = "SELECT * FROM user_currency WHERE user_id = '$member_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_currency SET amount = amount + '$amount' 
					  WHERE user_id = '$member_id' AND currency_id = '$currency_id'";
		}
		else {
			$query = "INSERT INTO user_currency VALUES('$member_id', '$currency_id', '$amount')";
		}
		$conn->query($query);
		
		$notification = "You have received $amount $currency_abbr from the $corporation_name corporation";
		sendNotification($notification, $member_id);
		
		
		//update corporation currency
		$query = "UPDATE corporation_currency SET amount = amount - '$amount' 
				  WHERE corporation_id = '$corporation_id' AND currency_id = '$currency_id'";
		$conn->query($query);

		//update corporation_currency_transactions
		$query = "SELECT * FROM corporation_currency_transactions WHERE user_id = '$user_id' 
				  AND corporation_id = '$corporation_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE corporation_currency_transactions SET received = received + '$amount' 
					  WHERE corporation_id = '$corporation_id' AND currency_id = '$currency_id'
					  AND user_id = '$user_id'";
		}
		else {
			$query = "INSERT INTO corporation_currency_invested VALUES('$corporation_id', '$user_id', 
					  '$currency_id', 0, '$amount', CURRENT_DATE, CURRENT_TIME)";
		}
		$conn->query($query);
		
		//get new amount
		$new_amount = number_format($available_amount - $amount, '2', '.', ' ');
		
		echo json_encode(array('success'=>true,
							   'msg'=>"Success",
							   'new_amount'=>"$new_amount $currency_abbr"
							  ));
	}
	else if($action == 'invest_currency') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have control over this corporation"
								  )));
		}
		
		$query = "SELECT manager_id, corporation_name FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have control over this corporation"
								  )));
		}
		$row = $result->fetch_row();
		list($manager_id, $corporation_name) = $row;
		
		//check if member
		$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You are not a member of this corporation."
			)));
		}
		
		//get user_name
		$query = "SELECT user_name FROM users WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_name) = $row;
		
		//is currency exist
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Currency doesn't exist"
			)));
		}
		$query = "SELECT curr.currency_id, currency_abbr, flag 
				  FROM country c, currency curr
				  WHERE c.country_id = curr.flag_id AND curr.currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Currency doesn't exist"
			)));
		}
		$row = $result->fetch_row();
		list($currency_id, $currency_abbr, $flag) = $row;

		//test amount
		if(!filter_var($amount, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Amount must be more than 0 and be a whole number"
			)));
		}
		
		//is enough currency
		$query = "SELECT amount FROM user_currency WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have enough $currency_abbr."
			)));
		}
		$row = $result->fetch_row();
		list($available_amount) = $row;
		
		if($available_amount < $amount) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have enough $currency_abbr"
			)));
		}

		//update corporation currency
		$query = "SELECT * FROM corporation_currency WHERE corporation_id = '$corporation_id' 
				  AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE corporation_currency SET amount = amount + '$amount' 
					  WHERE corporation_id = '$corporation_id' AND currency_id = '$currency_id'";
		}
		else {
			$query = "INSERT INTO corporation_currency VALUES('$corporation_id', '$currency_id', '$amount')";
		}
		$conn->query($query);

		//update user currency
		$query = "UPDATE user_currency SET amount = amount - '$amount' 
				  WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
		$conn->query($query);

		//update corporation_currency_invested
		$query = "SELECT * FROM corporation_currency_transactions WHERE user_id = '$user_id' 
				  AND corporation_id = '$corporation_id' AND currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE corporation_currency_transactions SET invested = invested + '$amount' 
					  WHERE corporation_id = '$corporation_id' AND currency_id = '$currency_id'
					  AND user_id = '$user_id'";
		}
		else {
			$query = "INSERT INTO corporation_currency_invested VALUES('$corporation_id', '$user_id', 
					  '$currency_id', '$amount', 0, CURRENT_DATE, CURRENT_TIME)";
		}
		$conn->query($query);

		//notify manager
		$notification = "$user_name invested $amount $currency_abbr into $corporation_name corporation.";
		sendNotification($notification, $manager_id);

		echo json_encode(array(
			'success'=>true,
			'msg'=>"Successfully invested $amount $currency_abbr.",
			'currency_info'=>array("currency_id"=>$currency_id, "flag"=>$flag, 
								   "currency_abbr"=>$currency_abbr)
		));
	}
	else if ($action == 'invest_currency_info') {
		//get users products
		$query = "SELECT uc.currency_id, currency_abbr, flag, amount 
				  FROM user_currency uc, country c, currency curr
				  WHERE curr.currency_id = uc.currency_id AND c.country_id = curr.flag_id
				  AND user_id = '$user_id'";
		$result = $conn->query($query);
		$user_currency = [];
		while($row = $result->fetch_row()) {
			list($currency_id, $currency_abbr, $flag, $available_amount) = $row;
			array_push($user_currency, array("currency_id"=>$currency_id, "currency_abbr"=>$currency_abbr, 
				"flag"=>$flag, "available_amount"=>number_format($available_amount, 2, '.', ' ')));
		}

		echo json_encode(array(
			'success'=>true,
			'user_currency'=>$user_currency
		));
	}
	else if ($action == 'collect_ok') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Corporation doesn't exist"
								  )));
		}

		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Corporation doesn't exist"
								  )));
		}
		
		//is product exist
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist"
								  )));
		}
		$query = "SELECT product_name FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($product_name) = $row;
		
		//quantity that can collect
		$query = "SELECT available FROM corporation_user_products WHERE user_id = '$user_id'
				  AND corporation_id = '$corporation_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"No products available to collect"
								  )));
		}
		$row = $result->fetch_row();
		list($available) = $row;
		
		if($available == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"No products available to collect"
								  )));
		}
		
		//get warehouse space
		$can_collect = $available;
		
		$query = "SELECT capacity, SUM(amount) FROM user_warehouse uw, user_product up
				  WHERE uw.user_id = '$user_id' AND up.user_id = uw.user_id";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($capacity, $warehouse_fill) = $row;
		if($capacity < ($warehouse_fill + $available)) {
			$can_collect =  floor(($capacity - $warehouse_fill) * 100) / 100;
		}
		
		if ($can_collect <= 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Not enough space in the warehouse"
								  )));
		}
		
		//update product warehouse
		$query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$can_collect' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$can_collect')";
		}
		$conn->query($query);
		
		//update corporation_user_products
		$query = "UPDATE corporation_user_products SET available = (SELECT * FROM (SELECT available FROM corporation_user_products 
				  WHERE user_id = '$user_id' AND product_id = '$product_id' AND corporation_id = '$corporation_id') AS temp)
				  - '$can_collect' WHERE user_id = '$user_id' AND product_id = '$product_id' AND corporation_id = '$corporation_id'";
		$conn->query($query);
		
		$query = "UPDATE corporation_user_products SET collected = (SELECT * FROM (SELECT collected FROM corporation_user_products 
				  WHERE user_id = '$user_id' AND product_id = '$product_id' AND corporation_id = '$corporation_id') AS temp)
				  + '$can_collect' WHERE user_id = '$user_id' AND product_id = '$product_id' AND corporation_id = '$corporation_id'";
		$conn->query($query);
		
		echo json_encode(array('success'=>true,
							   'msg'=>"Successfully collected $can_collect $product_name",
							   "left"=>$available - $can_collect,
							   "collected"=>$can_collect
							  ));
	}
	else if($action == 'collect') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Corporation doesn't exist"
								  )));
		}

		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Corporation doesn't exist"
								  )));
		}
		
		$query = "SELECT cup.product_id, available, product_name, product_icon 
				  FROM corporation_user_products cup, product_info pi WHERE user_id = '$user_id'
				  AND corporation_id = '$corporation_id' AND pi.product_id = cup.product_id
				  AND available > 0";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"No products available to collect"
								  )));
		}
		$products = array();
		while($row = $result->fetch_row()) {
			list($product_id, $quantity, $product_name, $product_icon) = $row;
			array_push($products , array("product_id"=>$product_id, "quantity"=>$quantity, 
						"product_name"=>$product_name, "product_icon"=>$product_icon));
		}
		
		echo json_encode(array('success'=>true,
							   'products'=>$products
							  ));
	}
	else if($action == 'giveaway_product_info') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have control over this corporation"
			)));
		}
		
		$query = "SELECT manager_id FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You don't have control over this corporation"
			)));
		}
		$row = $result->fetch_row();
		list($manager_id) = $row;
		
		//check if manager
		if($manager_id != $user_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough access"
								  )));
		}
		
		//is product exist
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Product doesn't exist"
			)));
		}
		$query = "SELECT product_id, product_icon, product_name FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Product doesn't exist"
			)));
		}
		$row = $result->fetch_row();
		list($product_id, $product_icon, $product_name) = $row;

		//get members
		$query = "SELECT u.user_id, user_name, user_image FROM corporation_members cm, users u, user_profile up
				  WHERE corporation_id = '$corporation_id' AND u.user_id = cm.user_id AND up.user_id = cm.user_id";
		$result_members = $conn->query($query);
		$members = [];
		while($row_members = $result_members->fetch_row()) {
			list($member_id, $member_name, $member_image) = $row_members;

			//get user investments in gold
			$query = "SELECT IFNULL(SUM(price * amount) / (SELECT price FROM product_price WHERE product_id = 1), 0) 
					  FROM product_price pp, corporation_user_invested cui
					  WHERE cui.product_id = pp.product_id AND corporation_id = '$corporation_id' AND user_id = '$member_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total_invested) = $row;

			//get user earnings in gold
			$query = "SELECT IFNULL(SUM(price * collected) / (SELECT price FROM product_price WHERE product_id = 1), 0) 
					  FROM product_price pp, corporation_user_products cup
					  WHERE cup.product_id = pp.product_id AND corporation_id = '$corporation_id' AND user_id = '$member_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total_earned) = $row;

			array_push($members, array("member_id"=>$member_id, "member_name"=>$member_name, 
				"member_image"=>$member_image, 
				"invested_in_gold"=>number_format($total_invested, '2', '.', ' '), 
				"earned_in_gold"=>number_format($total_earned, '2', '.', ' ')
			));
		}

		echo json_encode(array(
			'success'=>true,
			'members'=>$members,
			'product_info'=>array("product_id"=>$product_id, "product_icon"=>$product_icon, 
								  "product_name"=>$product_name)
		));
	}
	else if($action == 'giveaway_product') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have control over this corporation"
								  )));
		}
		
		$query = "SELECT manager_id, corporation_name FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have control over this corporation"
								  )));
		}
		$row = $result->fetch_row();
		list($manager_id, $corporation_name) = $row;
		
		//check if manager
		if($manager_id != $user_id) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough access"
								  )));
		}
		
		//is product exist
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist"
								  )));
		}
		$query = "SELECT product_name FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($product_name) = $row;

		//test quantity
		if(!is_numeric($quantity) || $quantity < 0.01) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>"Quantity must be more than or equal to 0.01"
			)));
		}
		$quantity = round($quantity, 2);
		
		//is enough products
		$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
				  AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough products"
								  )));
		}
		$row = $result->fetch_row();
		list($available_quantity) = $row;
		
		if($available_quantity < $quantity) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough products"
								  )));
		}

		//check if user is a member
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Member doesn't exist."
			)));
		}
		$query = "SELECT user_name FROM corporation_members cm, users u, user_profile up
				  WHERE corporation_id = '$corporation_id' AND u.user_id = cm.user_id AND up.user_id = cm.user_id
				  AND cm.user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This user is not a member of this corporation."
			)));
		}
		$row = $result->fetch_row();
		list($member_name) = $row;

		//giveaway_product
		$query = "SELECT * FROM corporation_user_products WHERE user_id = '$member_id' AND product_id = '$product_id' 
				  AND corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE corporation_user_products SET available = available + '$quantity' 
					  WHERE user_id = '$member_id' AND product_id = '$product_id' AND corporation_id = '$corporation_id'";
		}
		else {
			$query = "INSERT INTO corporation_user_products VALUES('$corporation_id', '$member_id', '$product_id', 
					  '$quantity', 0)";
		}
		$conn->query($query);
		
		//update corporation product
		$query = "UPDATE corporation_product SET amount = amount - '$quantity' 
				  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		$conn->query($query);

		//notify member
		$notification = "You received $quantity $product_name from $corporation_name corporation. " .
						"You can collect it on the Corporations page.";
		sendNotification($notification, $member_id);
		
		echo json_encode(array('success'=>true,
							   'msg'=>"Success",
							   'quantity'=>$quantity
							  ));
	}
	else if($action == 'invest_product') {
		//test corporation
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have control over this corporation"
								  )));
		}
		
		$query = "SELECT manager_id, corporation_name FROM corporations WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have control over this corporation"
								  )));
		}
		$row = $result->fetch_row();
		list($manager_id, $corporation_name) = $row;
		
		//check if member
		$query = "SELECT * FROM corporation_members WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You are not a member of this corporation."
			)));
		}
		
		//get user_name
		$query = "SELECT user_name FROM users WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_name) = $row;
		
		//is product exist
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist"
								  )));
		}
		$query = "SELECT product_id, product_icon, product_name FROM product_info 
				  WHERE product_id = '$product_id' AND product_id NOT IN (12, 13, 18, 19, 20, 21, 23, 24, 25)";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist"
								  )));
		}
		$row = $result->fetch_row();
		list($product_id, $product_icon, $product_name) = $row;

		//test quantity
		if(!filter_var($quantity, FILTER_VALIDATE_INT)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Quantity must be more than 0 and be a whole number"
			)));
		}
		
		//is enough products
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough products"
								  )));
		}
		$row = $result->fetch_row();
		list($available_quantity) = $row;
		
		if($available_quantity < $quantity) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have enough products"
								  )));
		}

		//update corporation product
		$query = "SELECT * FROM corporation_product WHERE corporation_id = '$corporation_id' 
				  AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE corporation_product SET amount = amount + '$quantity' 
					  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO corporation_product VALUES('$corporation_id', '$product_id', '$quantity')";
		}
		$conn->query($query);

		//update user product
		$query = "UPDATE user_product SET amount = amount - '$quantity' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);

		//update corporation_user_invested
		$query = "SELECT * FROM corporation_user_invested WHERE user_id = '$user_id' 
				  AND corporation_id = '$corporation_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE corporation_user_invested SET amount = amount + '$quantity' 
					  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'
					  AND user_id = '$user_id'";
		}
		else {
			$query = "INSERT INTO corporation_user_invested VALUES('$corporation_id', '$user_id', 
					  '$product_id', '$quantity', CURRENT_DATE, CURRENT_TIME)";
		}
		$conn->query($query);

		//notify manager
		$notification = "$user_name invested $quantity $product_name into $corporation_name corporation.";
		sendNotification($notification, $manager_id);

		echo json_encode(array(
			'success'=>true,
			'msg'=>"Successfully invested $quantity $product_name.",
			"product_info"=>array("product_id"=>$product_id, "product_icon"=>$product_icon, 
								  "product_name"=>$product_name)
		));
	}
	else if ($action == 'invest_products_info') {
		//get users products
		$query = "SELECT pi.product_id, product_name, product_icon, amount FROM user_product up, product_info pi
				  WHERE pi.product_id = up.product_id AND up.user_id = '$user_id' 
				  AND up.product_id NOT IN (12, 13, 18, 19, 20, 21, 23, 24, 25)";
		$result = $conn->query($query);
		$products = [];
		while($row = $result->fetch_row()) {
			list($product_id, $product_name, $product_icon, $available_amount) = $row;
			array_push($products, array("product_id"=>$product_id, "product_name"=>$product_name, 
				"product_icon"=>$product_icon, "available_amount"=>number_format($available_amount, 2, '.', ' ')));
		}

		echo json_encode(array(
			'success'=>true,
			'products'=>$products
		));
	}
	else if ($action == 'create_new_corp') {
		//check if user has appropriate level
		$query = "SELECT IFNULL(level_id, 0), open_at_level 
				  FROM level_locked_features llf, user_profile up LEFT JOIN user_exp_levels uxl ON uxl.experience <= up.experience
				  WHERE up.user_id = '$user_id' AND llf.feature_id = 2
				  ORDER BY level_id DESC LIMIT 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($member_level, $open_at_level) = $row;
		if($member_level < $open_at_level) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You must have at least level $open_at_level."
			)));
		}

		//can only create one corporation
		$query = "SELECT COUNT(*) FROM corporations WHERE manager_id = '$user_id' AND is_active = TRUE";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_corporations) = $row;
		if($total_corporations >= 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"You can only have 1 corporation."
			)));
		}
		
		strValidate($corp_name, 1, 15, 'Corporation name');
		strValidate($corp_abbr, 1, 5, 'Corporation abbreviation');

		//check if corp name unique
		$query = "SELECT * FROM corporations WHERE corporation_name = '$corp_name'";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This name already exists."
			)));
		}

		//check if corp abbr unique
		$query = "SELECT * FROM corporations WHERE corporation_abbr = '$corp_abbr'";
		$result = $conn->query($query);
		if($result->num_rows != 0) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"This abbreviation already exists."
			)));
		}
		
		$corporation_id = getTimeForId() . $user_id;
		
		$query = "INSERT INTO corporations VALUES ('$corporation_id', '$corp_name', '$corp_abbr',
				  '$user_id', CURRENT_DATE, CURRENT_TIME, TRUE)";
		$conn->query($query);
	
		$query = "INSERT INTO corporation_members VALUES ('$corporation_id', '$user_id')";
		$conn->query($query);

		$corp_info = getCorpInfo($corporation_id);
		
		echo json_encode(array('success'=>true,
							   'msg'=>"Corporation created.",
							   'corp_info'=>$corp_info
							  ));
	}
	else if($action == 'get_corporations') {
		$query = "SELECT c.corporation_id FROM corporation_members cm, corporations c
				  WHERE user_id = '$user_id' AND c.corporation_id = cm.corporation_id
				  AND is_active = TRUE ORDER BY corporation_name ASC";
		$result = $conn->query($query);
		$corporations = array();
		while($row = $result->fetch_row()) {
			list($corporation_id) = $row;
			array_push($corporations, getCorpInfo($corporation_id));
		}
		echo json_encode(array(
			'success'=>true,
			'corporations'=>$corporations
	    ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
	
	function getCorpInfo($corporation_id) {
		global $conn;
		global $user_id;
		//corp info
		$query = "SELECT c.corporation_id, corporation_name, corporation_abbr, manager_id, user_name, user_image
				  FROM corporation_members cm, users u, corporations c, user_profile up
				  WHERE c.corporation_id = cm.corporation_id AND c.corporation_id = '$corporation_id' AND u.user_id = manager_id
				  AND up.user_id = manager_id ORDER BY corporation_abbr";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($corporation_id, $corporation_name, $corporation_abbr, $manager_id, 
			$manager_name, $manager_img) = $row;

		//select collected/available products
		$query = "SELECT IFNULL(SUM(available), 0) FROM corporation_user_products
		WHERE corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($available_prod) = $row;

		//total members
		$query = "SELECT COUNT(*) FROM corporation_members WHERE corporation_id = '$corporation_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_members) = $row;

		//get user investments in gold
		$query = "SELECT IFNULL(SUM(price * amount) / (SELECT price FROM product_price WHERE product_id = 1), 0) 
				  FROM product_price pp, corporation_user_invested cui
				  WHERE cui.product_id = pp.product_id AND corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_invested) = $row;

		//get user earnings in gold
		$query = "SELECT IFNULL(SUM(price * collected) / (SELECT price FROM product_price WHERE product_id = 1), 0) 
				  FROM product_price pp, corporation_user_products cup
				  WHERE cup.product_id = pp.product_id AND corporation_id = '$corporation_id' AND user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($total_earned) = $row;

		$is_manager = false;
		if($manager_id == $user_id) {
			$is_manager = true;
		}


		$corp_info = array("corporation_id"=>$corporation_id, "corporation_name"=>$corporation_name, 
			"corporation_abbr"=>$corporation_abbr, "manager_id"=>$manager_id, "is_manager"=>$is_manager,
			"manager_name"=>$manager_name, "manager_img"=>$manager_img, "total_members"=>$total_members,
			'available_prod'=>number_format($available_prod, '2', '.', ' '),
			'total_invested'=>number_format($total_invested, '3', '.', ' '),
			'total_earned'=>number_format($total_earned, '2', '.', ' ')
		);

		return $corp_info;
	}
?>