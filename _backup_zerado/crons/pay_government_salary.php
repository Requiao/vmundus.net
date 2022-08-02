<?php
	//Description: Pay salary for governors.

	include('/var/www/html/connect_db.php');
	include('/var/www/html/crons/timezone_id.php'); //$timezone_id
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	/* pay salary */
	$query = "SELECT cg.country_id, user_id, salary, currency_id, name, cg.position_id 
			  FROM country_government cg, country c, government_positions gp
			  WHERE c.country_id = cg.country_id AND timezone_id = '$timezone_id' AND gp.position_id = cg.position_id
			  UNION
			  SELECT cm.country_id, user_id, salary, currency_id, name, gp.position_id
			  FROM congress_members cm, congress_details cd, country c, government_positions gp
			  WHERE cm.country_id = c.country_id AND cd.country_id = cm.country_id AND timezone_id = '$timezone_id'
			  AND gp.position_id = 3
			  UNION
			  SELECT bd.country_id, user_id, manager_salary, c.currency_id, 'Secretary of the Treasury', 0
			  FROM bank_details bd, country c WHERE c.country_id = bd.country_id AND timezone_id = '$timezone_id'";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($country_id, $user_id, $salary, $currency_id, $position_name, $position_id) = $row;
		
		//check if user already has that currency type.
		$query = "SELECT uc.amount, cu.amount, currency_abbr FROM currency c, country_currency cu 
				  LEFT JOIN user_currency uc ON cu.currency_id = uc.currency_id
				  AND user_id = '$user_id' WHERE country_id = '$country_id' AND cu.currency_id = '$currency_id' 
				  AND c.currency_id = cu.currency_id";
		$result_usr_currency = $conn->query($query);
		$row_usr_currency = $result_usr_currency->fetch_row();
		list($user_amount, $country_amount, $currency_abbr) = $row_usr_currency;
		
		if($country_amount < $salary) {//not enough money in country treasury
			$notification = "Country\'s treasury is empty and will not be able to pay you today for working as a $position_name.";
			sendNotification($notification, $user_id);
		}
		else {//pay salary
			if(empty($user_amount)) {
				$query = "INSERT INTO user_currency VALUES('$user_id', '$currency_id', '$salary')";
				$conn->query($query);
			}
			else {
				$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) + '$salary' 
						  WHERE user_id = '$user_id' AND currency_id = '$currency_id'";
				$conn->query($query);
			}
			//update country currency
			$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) - '$salary' 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$conn->query($query);
			
			$notification = "Your salary for today is $salary $currency_abbr for working as a $position_name.";
			sendNotification($notification, $user_id);
		}
		
		//update country_government_history table
		if($position_id != 0) {
			$query = "SELECT * FROM country_government_history WHERE country_id = '$country_id' AND user_id = '$user_id' 
					  AND position_id = '$position_id'";
			$result_hist = $conn->query($query);
			if($result_hist->num_rows == 1) {//update
				$query = "UPDATE country_government_history SET days = (SELECT * FROM (SELECT days FROM country_government_history 
						  WHERE user_id = '$user_id' AND country_id = '$country_id' AND position_id = '$position_id') AS temp) + 1
						  WHERE user_id = '$user_id' AND country_id = '$country_id' AND position_id = '$position_id'";
				$conn->query($query);
			}
			else {//insert
				$query = "INSERT INTO country_government_history VALUES('$country_id', '$user_id', '$position_id', 1)";
				$conn->query($query);
			}
		}
	}
?>