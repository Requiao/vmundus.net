<?php
//Description: Output battle info for statistic
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$battle_id =  htmlentities(stripslashes(trim($_POST['battle_id'])), ENT_QUOTES);

	$user_id = $_SESSION['user_id'];

	if(!ctype_digit($battle_id)) {
		exit();
	}

	$reply = 'true|-|';
	
	/* countries damage */
	//for attacker country damage
	$query = "SELECT flag, country_name, c.country_id, SUM(damage) FROM country c, battle_user_damage bud
			  WHERE c.country_id = bud.country_id AND battle_id = '$battle_id' AND for_country_id = 
			 (SELECT attacker_id FROM battles WHERE battle_id = '$battle_id') GROUP BY bud.country_id
			  ORDER BY SUM(damage) DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($flag, $country_name, $country_id, $total_damage) = $row;
		
		if(strlen($country_name) > 15) {
			$country_name = substr($country_name, 0, 15) . "...";
		}
		$total_damage = number_format($total_damage, 2, ".", " ");
		
		$reply .= "$flag, $country_name, $country_id, $total_damage||";
	}
	
	$reply .= "|-|";
	
	//for defender country damage
	$query = "SELECT flag, country_name, c.country_id, SUM(damage) FROM country c, battle_user_damage bud
			  WHERE c.country_id = bud.country_id AND battle_id = '$battle_id' AND for_country_id = 
			 (SELECT defender_id FROM  battles WHERE battle_id = '$battle_id') GROUP BY bud.country_id
			  ORDER BY SUM(damage) DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($flag, $country_name, $country_id, $total_damage) = $row;
		
		if(strlen($country_name) > 15) {
			$country_name = substr($country_name, 0, 15) . "...";
		}
		$total_damage = number_format($total_damage, 2, ".", " ");
		
		$reply .= "$flag, $country_name, $country_id, $total_damage||";
	}
	
	$reply .= "|-|";
	
	/* players damage */
	//for attacker damage
	$query = "SELECT user_image, u.user_id, user_name, damage FROM user_profile up, users u, battle_user_damage bud
			  WHERE u.user_id = bud.user_id AND up.user_id = bud.user_id AND battle_id = '$battle_id'
			  AND for_country_id = (SELECT attacker_id FROM battles WHERE battle_id = '$battle_id') ORDER BY damage DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($user_image, $user_id, $user_name, $total_damage) = $row;
		
		if(strlen($user_name) > 15) {
			$user_name = substr($user_name, 0, 15) . "...";
		}
		$total_damage = number_format($total_damage, 2, ".", " ");
		
		$reply .= "$user_image, $user_name, $user_id, $total_damage||";
	}
	$reply .= "|-|";
	
	//for defender damage
	$query = "SELECT user_image, u.user_id, user_name, damage FROM user_profile up, users u, battle_user_damage bud
			  WHERE u.user_id = bud.user_id AND up.user_id = bud.user_id AND battle_id = '$battle_id'
			  AND for_country_id = (SELECT defender_id FROM battles WHERE battle_id = '$battle_id') ORDER BY damage DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($user_image, $user_id, $user_name, $total_damage) = $row;
		
		if(strlen($country_name) > 15) {
			$country_name = substr($country_name, 0, 15) . "...";
		}
		$total_damage = number_format($total_damage, 2, ".", " ");
		
		$reply .= "$user_image, $user_name, $user_id, $total_damage||";
	}
	
	$reply .= "|-|";
	
	/* country soldier loss */
	//for attacker loss
	$query = "SELECT flag, country_name, c.country_id, SUM(amount) FROM country c, person_battle_losses pbl
			  WHERE c.country_id = pbl.country_id AND battle_id = '$battle_id' AND for_country_id = 
			 (SELECT attacker_id FROM battles WHERE battle_id = '$battle_id') GROUP BY pbl.country_id 
			  ORDER BY SUM(amount) DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($flag, $country_name, $country_id, $total_damage) = $row;
		
		if(strlen($country_name) > 20) {
			$country_name = substr($country_name, 0, 20) . "...";
		}
		
		$reply .= "$flag, $country_name, $country_id, $total_damage||";
	}
	
	$reply .= "|-|";
	
	//for defender loss
	$query = "SELECT flag, country_name, c.country_id, SUM(amount) FROM country c, person_battle_losses pbl
			  WHERE c.country_id = pbl.country_id AND battle_id = '$battle_id' AND for_country_id = 
			 (SELECT defender_id FROM battles WHERE battle_id = '$battle_id') GROUP BY pbl.country_id 
			  ORDER BY SUM(amount) DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($flag, $country_name, $country_id, $total_damage) = $row;
		
		if(strlen($country_name) > 20) {
			$country_name = substr($country_name, 0, 20) . "...";
		}
		
		$reply .= "$flag, $country_name, $country_id, $total_damage||";
	}
	
	$reply .= "|-|";
	
	
	/* equipment used */
	//for attacker damage
	$query = "SELECT flag, country_name, c.country_id, product_name, SUM(amount) 
        FROM country c, user_battle_uses ubu, product_info pi
			  WHERE c.country_id = ubu.country_id AND battle_id = '$battle_id' AND for_country_id = 
			 (SELECT attacker_id FROM battles WHERE battle_id = '$battle_id') AND pi.product_id = ubu.product_id
			  GROUP BY ubu.country_id, ubu.product_id  
			  ORDER BY SUM(amount) DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($flag, $country_name, $country_id, $product_name, $total_use) = $row;
		
		if(strlen($country_name) > 20) {
			$country_name = substr($country_name, 0, 20) . "...";
		}
		$total_use = number_format($total_use, 2, ".", " ");
		
		$reply .= "$flag, $country_name, $country_id, $product_name, $total_use||";
	}
	
	$reply .= "|-|";
	
	//for defender damage
	$query = "SELECT flag, country_name, c.country_id, product_name, SUM(amount) 
			  FROM country c, user_battle_uses ubu, product_info pi
			  WHERE c.country_id = ubu.country_id AND battle_id = '$battle_id' AND for_country_id = 
			 (SELECT defender_id FROM battles WHERE battle_id = '$battle_id') AND pi.product_id = ubu.product_id
			  GROUP BY ubu.country_id, ubu.product_id  
			  ORDER BY SUM(amount) DESC LIMIT 5";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($flag, $country_name, $country_id, $product_name, $total_use) = $row;
		
		if(strlen($country_name) > 20) {
			$country_name = substr($country_name, 0, 20) . "...";
		}
		$total_use = number_format($total_use, 2, ".", " ");
		
		$reply .= "$flag, $country_name, $country_id, $product_name, $total_use||";
	}
	
	echo $reply;
	
	mysqli_close($conn);
?>