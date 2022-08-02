<?php
	session_start();
	include('connect_db.php');
	include('php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('php_functions/get_time_for_id.php');//getTimeForId()
	require_once('php_functions/referral_process.php');//referralProcess($user_id);
	require_once('php_functions/get_user_level.php');//getUserLevel($user_id);

	$user_id = $_SESSION['user_id'];

	if($user_id != 1 && $user_id != 1000) {
		exit("Error.");
	}

	//exit("Error.");

	/* FIND BOTS */
	echo "Bots with the same DEVICE: <br>";
	$query = "SELECT user_id FROM users 
			  WHERE user_id NOT IN (SELECT user_id FROM banned_users WHERE date_until > CURRENT_DATE())";
	$result_users = $conn->query($query);
	while($row_users = $result_users->fetch_row()) {
		list($user_id) = $row_users;

		$query = "SELECT user_id FROM user_devices WHERE 
				  device_id IN (SELECT device_id FROM user_devices WHERE user_id = '$user_id')
				  AND user_id NOT IN (SELECT user_id FROM banned_users WHERE date_until > CURRENT_DATE())
				  GROUP BY user_id";
		$result = $conn->query($query);
		if($result->num_rows > 2) {
			//echo "$user_id: ";
			while($row = $result->fetch_row()) {
				list($user_id) = $row;	
				echo "$user_id, ";
			}
			echo "</br></br>";
		}
	}

	echo "Bots with the same IP: <br>";
	$query = "SELECT user_id FROM users 
			  WHERE user_id NOT IN (SELECT user_id FROM banned_users WHERE date_until > CURRENT_DATE())";
	$result_users = $conn->query($query);
	while($row_users = $result_users->fetch_row()) {
		list($user_id) = $row_users;

		$query = "SELECT user_id FROM session_table WHERE 
				  ip IN (SELECT ip FROM session_table WHERE user_id = '$user_id')
				  AND user_id NOT IN (SELECT user_id FROM banned_users WHERE date_until > CURRENT_DATE())
				  GROUP BY user_id";
		$result = $conn->query($query);
		if($result->num_rows > 2) {
			//echo "$user_id: ";
			while($row = $result->fetch_row()) {
				list($user_id) = $row;	
				echo "$user_id, ";
			}
			echo "</br></br>";
		}
	}

	exit();

	/*
	//REFUND FOR PURCHASING CLONES
	$query = "SELECT COUNT(item_number) * pod.quantity AS clones, (COUNT(item_number) * pod.quantity) * 6 AS gold_refund, user_id 
			  FROM  purchases p, person_offer_details pod
			  WHERE pod.offer_id = p.item_number
			  GROUP BY user_id, item_number";
	$result_ref = $conn->query($query);
	while($row_ref = $result_ref->fetch_row()) {
		list($clones, $gold_refund, $user_id) = $row_ref;

		$query = "SELECT * FROM user_gold_rewards WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			$query = "INSERT INTO user_gold_rewards VALUES ('$user_id', '$gold_refund', '0', '2020-01-01', '00:00:00')";
		}
		else {
			$query = "UPDATE user_gold_rewards SET available = available + '$gold_refund' WHERE user_id = '$user_id'";
		}

		if(!$conn->query($query)) {
			echo "F: $user_id clones: $clones, refund: $gold_refund<br>";
		}
		else {
			$notification = "You have received $gold_refund Gold for purchasing $clones clones." .
							" You can collect it on the Store page.";
			sendNotification($notification, $user_id);

			echo "S: $user_id clones: $clones, refund: $gold_refund<br>";
		}
	}*/

	/* COMPENSATION FOR UPGRADING WAREHOUSE */
	/*$query = "SELECT user_id, ((capacity - 1000) / 100 ) * 1.364 FROM user_warehouse WHERE capacity > 1000";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($user_id, $compensation) = $row;

		$query = "UPDATE user_gold_rewards SET available = available + '$compensation' WHERE user_id = '$user_id'";
		if(!$conn->query($query)) {
			echo "$user_id <br>";
		}
		else {
			$notification = "You have received $compensation Gold compensation for upgrading warehouse." .
							" You can collect it on the Store page.";
			sendNotification($notification, $user_id);

			echo "v: $user_id <br>";
		}
	}*/

	/*$query = "SELECT hours FROM timezones WHERE timezone_id = 
			 (SELECT timezone_id FROM user_profile WHERE user_id = '3125')";
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
	echo "$after_four_am $before_four_am";*/
	exit("Error.");

	/*$query = "SELECT responsibility_id FROM political_responsibilities";
	$result = $conn->query($query);
	$x = 0;
	$responsibilities_arr = array();
	while($row = $result->fetch_row()) {
		list($responsibility_id) = $row;	
		$responsibilities_arr[$x] = $responsibility_id;
		$x++;
    }
    
    $query = "SELECT country_id FROM country";
	$result = $conn->query($query);
	$x = 0;
	$countries = array();
	while($row = $result->fetch_row()) {
		list($country_id) = $row;	
		$countries[$x] = $country_id;
		$x++;
    }

	for($c = 0; $c < count($countries); $c++) {
            for($a = 0; $a < count($responsibilities_arr); $a++) {
                $query = "INSERT INTO government_country_responsibilities VALUES('$countries[$c]', 3, 
                        '$responsibilities_arr[$a]', TRUE, TRUE)";
                $conn->query($query);
        }
    }*/

	exit();

	//aliens declare war to everyone
	/*$query = "SELECT country_id FROM country";
	$result_c = $conn->query($query);
	while($row_c = $result_c->fetch_row()) {
		list($country_id) = $row_c;

		$war_id = getTimeForId();
		$query = "INSERT INTO country_wars VALUES('$war_id', '1000', '$country_id', CURRENT_DATE, CURRENT_TIME, 1, 0)";
		$conn->query($query);
	}*/

	
	/* UPDATE users tables after reset */
	/*$query = "SELECT u.user_id, citizenship, user_name FROM users u, user_profile up 
			  WHERE up.user_id = u.user_id AND u.user_id NOT IN 
			 (SELECT user_id FROM banned_users WHERE date_until > CURRENT_TIME)";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($player_id, $citizenship) = $row;

		//user_currency. give 250 currency
		$currency_id = $citizenship;
		$query = "INSERT INTO user_currency VALUES('$player_id', '$currency_id', 250)";
		$conn->query($query);

		$query = "INSERT INTO user_currency VALUES('$player_id', '1000', 1000)";
		$conn->query($query);

		//warehouse
		$query = "INSERT INTO user_warehouse VALUES('$player_id', '1000')";
		$conn->query($query);

		//register rewards
		$query = "INSERT INTO user_rewards VALUES('1', '$player_id', FALSE, CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);

		$query = "INSERT INTO user_product VALUES ('$player_id', '1', 2)";
		$conn->query($query);

		//populate people
		//person
		$person_id = getTimeForId() . $player_id;
		$query = "INSERT INTO people VALUES('$player_id', '$person_id', 1, 'available', 18, 100, 
										  'Person 1', 0, 0, FALSE, 0)";
		$conn->query($query);
		
		//person
		$person_id = getTimeForId() . $player_id;
		$query = "INSERT INTO people VALUES('$player_id', '$person_id', 1, 'available', 18, 100, 
										  'Person 2', 0, 0, FALSE, 0)";
		$conn->query($query);

		//person
		$person_id = getTimeForId() . $player_id;
		$query = "INSERT INTO people VALUES('$player_id', '$person_id', 1, 'available', 18, 100, 
										  'Person 3', 0, 0, FALSE, 0)";
		$conn->query($query);

		//person
		$person_id = getTimeForId() . $player_id;
		$query = "INSERT INTO people VALUES('$player_id', '$person_id', 1, 'available', 18, 100, 
										  'Person 4', 0, 0, FALSE, 0)";
		$conn->query($query);

		//person
		$person_id = getTimeForId() . $player_id;
		$query = "INSERT INTO people VALUES('$player_id', '$person_id', 1, 'available', 18, 100, 
										  'Person 5', 0, 0, FALSE, 0)";
		$conn->query($query);
	}*/


	exit();

	//BUILDING POLICY
	$building_list = array();
	$x = 0;
	$query = "SELECT building_id FROM building_info WHERE product_id IS NOT NULL ORDER BY building_id";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($building_id) = $row;
		$building_list[$x] = $building_id;
		$x++;
	}

	$count = 0;
	for($u = 0; $u < sizeof($building_list); $u++) {
		$query = "INSERT INTO building_policy VALUES('$country_id', '$building_list[$u]', 0.25)"; 
		$conn->query($query);
		$count++;
	}


	/* POLITICAL RESPONSIBILITIES */
	$query = "SELECT position_id FROM government_positions";
	$result = $conn->query($query);
	$x = 0;
	$positions_arr = array();
	while($row = $result->fetch_row()) {
		list($position_id) = $row;	
		$positions_arr[$x] = $position_id;
		$x++;
	}

	$query = "SELECT responsibility_id FROM political_responsibilities";
	$result = $conn->query($query);
	$x = 0;
	$responsibilities_arr = array();
	while($row = $result->fetch_row()) {
		list($responsibility_id) = $row;	
		$responsibilities_arr[$x] = $responsibility_id;
		$x++;
	}

	for($q = 0; $q < count($positions_arr); $q++) {
		for($a = 0; $a < count($responsibilities_arr); $a++) {
			if($positions_arr[$q] == 1) {//president
				$query = "INSERT INTO government_country_responsibilities VALUES('$country_id', $positions_arr[$q], 
						 '$responsibilities_arr[$a]', TRUE, TRUE)";
				$conn->query($query);
			}
		}
	}


	/* PRODUCT PRODUCTION TAX */
	$query = "SELECT product_id FROM product_info ORDER BY product_id";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($product_id) = $row;

		$query = "INSERT INTO product_production_tax VALUES('$country_id', '$product_id', 1)";
		$conn->query($query);
	}

	/* PRODUCT SALE TAX */
	$query = "SELECT product_id FROM product_info ORDER BY product_id";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($product_id) = $row;

		$query = "INSERT INTO product_sale_tax VALUES('$country_id', 1, '$product_id')";
		$conn->query($query);
	}

	/* CHAT */
	$chat_id = getTimeForId() . $country_id;
	$chat_name = $country_name . ' Chat';
	if(strlen($chat_name) > 15) {
		$chat_name = $country_abbr . ' Chat';
	}
	$member_id = 1;
	$access_lvl = 1;

	$query = "INSERT INTO chat_info VALUES('$chat_id', '$chat_name', CURRENT_DATE, CURRENT_TIME)";
	if($conn->query($query)) {
		$query = "INSERT INTO chat_members VALUES('$chat_id', '$member_id', '$access_lvl')";
		$conn->query($query);
		$count++;
	}


	/* auto populate user_rewards table */
	/*$query = "SELECT user_id FROM users";
	$result = $conn->query($query);
	while($row = mysqli_fetch_row($result)) {
		list($user_id) = $row;

		$query = "INSERT INTO user_rewards VALUES (14, '$user_id', FALSE, CURRENT_DATE, CURRENT_TIME)";
		if(!$conn->query($query)) {
			echo "$user_id </br>";
		}
	}*/
	/*
	$increment_value = 25;
	$base_value = 1000;
	$total = 19525;
	for($x = 0; $x < 10; $x++) {
		$total += $base_value;
		$base_value += $increment_value;

		echo "$total<br>";
	}
	*/

/*
		$time = microtime();
		echo "$time <br>";
		$time = str_replace('0.', '', $time);
		echo "$time <br>";
		$time = substr_replace($time, '', 6, 3);
		echo "$time <br>";
		//return $time;
		echo strlen($time);
		//16 digits
		*/
/*
	$subs = 5;
	for($x = 1; $x <= 1000; $x++) {
		$query = "INSERT INTO user_blog_subscribers_levels VALUES ('$x', '$subs')";
		mysqli_query($conn, $query);
		$subs += 5;
	}*/
	
	/*
	$query = "SELECT (COUNT(user_id) * 0.05), user_id FROM translation_rewarded_users GROUP BY user_id";
	$result = mysqli_query($conn, $query);
	while($row = mysqli_fetch_row($result)) {
		list($amount, $user_id) = $row;
		$query = "UPDATE user_product SET amount = (SELECT amount + '$amount' FROM 
				 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '1') AS temp) 
				  WHERE user_id = '$user_id' AND product_id = '1'";
		$conn->query($query);
		
		$notification = "You have received additional gold for the translation in the amount of $amount.";
		
		sendNotification($notification, $user_id);
	}*/
	
	/*
	$regions = 1471;
	$total_reousrces = 5884;
	
	$_15 = 6;//Powder(250)
	$_14 = 4;//Stone(350)
	$_13 = 12;//Uranium(100)
	$_12 = 9;//Diamonds(150)
	$_11 = 5;//Iron Ore(300)
	$_10 = 5;//Coal(250)
	$_09 = 6;//Gas(200)
	$_08 = 6;//Salt(250)
	$_07 = 4;//Clay(350)
	$_06 = 4;//Sand(350)
	
	//$_05 = 0;//Fish(200)
	//$_04 = 0;//Water(300)
	
	$_03 = 5;//Oil(200)
	//$_02 = 4;//Timber(350)
	$_01 = 6;//Gold(200)
	
	$resources = array(//product_id, regions per resource, temporary(true/false)
					array(15, 5, 0, false),//Powder
					array(14, 3, 0, false),//Stone
					array(13, 11, 0, false),//Uranium
					array(12, 9, 0, false),//Diamonds
					array(11, 3, 0, false),//Iron Ore
					array(10, 5, 0, false),//Coal
					array(9, 6, 0, false),//Gas
					array(8, 6, 0, false),//Salt
					array(7, 3, 0, false),//Clay
					array(6, 3, 0, false),//Sand
					array(3, 5, 0, false),//Oil
					array(1, 7, 0, false)//Gold
					);
	$total = 2190;
	//$total = 0;
	for($u = 0; $u <= 11; $u++) {
		$total += floor($regions / $resources[$u][1]);
		//$total += floor(89 / $resources[$u][1]);
	}				
	//echo "$total == 5884<br><br>";
	//exit();
	
	//echo "INSERT INTO region_resource_bonus VALUES ('$region_id', '$product_id', '$bonus');";
	echo "INSERT INTO region_resource_bonus VALUES ";
	$query = "SELECT region_id FROM regions ORDER BY region_id LIMIT 2000";
	$result_regions = mysqli_query($conn, $query);
	while($row_regions = mysqli_fetch_row($result_regions)) {
		list($region_id) = $row_regions;
		
		for($x = 0; $x <= 11; $x++) {
			$resources[$x][2]++;
			
			if($resources[$x][2] >= $resources[$x][1] && $resources[$x][3] == true) {
				$resources[$x][2] = $resources[$x][2] - $resources[$x][1];
				$resources[$x][3] = false;
			}
		}
		
		$query = "SELECT COUNT(region_id) FROM region_resource_bonus WHERE region_id = '$region_id'";
		$result = mysqli_query($conn, $query);
		$row = mysqli_fetch_row($result);
		list($region_resources) = $row;
		
		$item_id = null;
		
		for($u = 0; $u < 4 - $region_resources; $u++) {
			$flag = true;
			
			//////////////////////////
			$false = 0;
			for($x = 0; $x <= 11; $x++) {
				if($resources[$x][3] == false) {
					$false++;
					break;
				}
			}
			if($false == 0) {
				break 2;
			}
			//////////////////////////
			
			while($flag) {
				$item_id = mt_rand(0, 11);
				
				if($resources[$item_id][3] == false) {
					$resources[$item_id][3] = true;
					$flag = false;
				}
			};
			
			$bonus = mt_rand(3, 5);
			
			$product_id = $resources[$item_id][0];
			echo "('$region_id', '$product_id', '$bonus'),";
		}
	}
	
	for($x = 0; $x <= 11; $x++) {
		//if($resources[$x][2] >= $resources[$x][1]) {
			//echo $resources[$x][0] . ' ' . $resources[$x][1]  . ' ' . $resources[$x][2] . ' ' . ($resources[$x][3]?'true':'false') .  "<br>";
		//}
	}
	*/
	
	/*
	for($x = 1; $x <= 15; $x++) {
		$_7 += $prod_7;
		$_11 += $prod_11;
		$_14 += $prod_14;
		$_2 += $prod_2;
		
		echo "UPDATE def_const_product SET amount = '$_7' WHERE def_loc_id = '$x' AND product_id = '7'; <br>";
		echo "UPDATE def_const_product SET amount = '$_11' WHERE def_loc_id = '$x' AND product_id = '11'; <br>";
		echo "UPDATE def_const_product SET amount = '$_14' WHERE def_loc_id = '$x' AND product_id = '14'; <br>";
		echo "UPDATE def_const_product SET amount = '$_2' WHERE def_loc_id = '$x' AND product_id = '2'; <br><br>";
		$_7 -= 4;
		$_11 -= 1;
		$_14 -= 6;
		$_2 -= 9;
	}*/
	
	
	
	/*
	//DELETE REQUIRED PRODUCT FROM COMPANY
	$query = "SELECT user_id, product_id, amount, c.company_id FROM resource_warehouse rw, user_building ub, companies c WHERE product_id = 16
			 AND building_id IN
			 (SELECT building_id FROM building_info WHERE product_id IN (SELECT product_id FROM product_info WHERE purpose = 1))
			 AND ub.company_id = c.company_id
			 AND rw.company_id = c.company_id";
	$result = mysqli_query($conn, $query);
	while($row = mysqli_fetch_row($result)) {
		list($user_id, $product_id, $amount, $company_id) = $row;	
		$query = "UPDATE user_product SET amount = amount + '$amount' WHERE user_id = '$user_id' AND product_id = '$product_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM resource_warehouse WHERE company_id = '$company_id' AND product_id = '$product_id'";
		mysqli_query($conn, $query);
	}
	
	$query = "SELECT corporation_id, product_id, amount, c.company_id FROM resource_warehouse rw, corporation_building ub, companies c WHERE product_id = 16
			  AND building_id IN
			 (SELECT building_id FROM building_info WHERE product_id IN (SELECT product_id FROM product_info WHERE purpose = 1))
			  AND ub.company_id = c.company_id
			  AND rw.company_id = c.company_id";
	$result = mysqli_query($conn, $query);
	while($row = mysqli_fetch_row($result)) {
		list($corporation_id, $product_id, $amount, $company_id) = $row;	
		$query = "UPDATE corporation_product SET amount = amount + '$amount' WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
		mysqli_query($conn, $query);
		
		$query = "DELETE FROM resource_warehouse WHERE company_id = '$company_id' AND product_id = '$product_id'";
		mysqli_query($conn, $query);
	}
	*/

	
	/* NEW LAWS */
	/*
	$query = "SELECT country_id FROM country ORDER BY country_id";
	$result = mysqli_query($conn, $query);
	$x = 0;
	$country_arr = array();
	while($row = mysqli_fetch_row($result)) {
		list($country_id) = $row;	
		$country_arr[$x] = $country_id;
		$x++;
	}

	$query = "SELECT position_id FROM government_positions";
	$result = mysqli_query($conn, $query);
	$x = 0;
	$positions_arr = array();
	while($row = mysqli_fetch_row($result)) {
		list($position_id) = $row;	
		$positions_arr[$x] = $position_id;
		$x++;
	}

	$query = "SELECT responsibility_id FROM political_responsibilities WHERE responsibility_id >= 36";
	$result = mysqli_query($conn, $query);
	$x = 0;
	$responsibilities_arr = array();
	while($row = mysqli_fetch_row($result)) {
		list($responsibility_id) = $row;	
		$responsibilities_arr[$x] = $responsibility_id;
		$x++;
	}

	for($x = 0; $x < count($country_arr); $x++) {
		for($q = 0; $q < count($positions_arr); $q++) {
			for($a = 0; $a < count($responsibilities_arr); $a++) {
				$query = "INSERT INTO government_country_responsibilities VALUES('$country_arr[$x]', $positions_arr[$q], 
						'$responsibilities_arr[$a]', FALSE, FALSE)";
				if(mysqli_query($conn, $query)) {
					$count++;
				}
			}
		}
	}
	
	echo date('H:i:s');
	echo "<br>$count";*/
?>