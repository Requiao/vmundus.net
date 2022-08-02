<?php
	//runs * * * * * every 2nd minute each hour
	//Description: Proccess credit/deposit

	include('/var/www/html/connect_db.php');
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).

	//deposit
	$query = "SELECT deposit_id, user_id, amount, rate, earned, days_left, left_to_return, currency_id, country_id, type 
			  FROM user_deposit WHERE active = TRUE";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($deposit_id, $user_id, $amount, $rate, $earned, $days_left, $left_to_return, $currency_id, $country_id, $type) = $row;
		
		if($days_left == 0) {
			//bank didn't had enough currency/gold to pay back all deposit
			//pay back
			payBack($left_to_return, $deposit_id, $user_id, $type, $country_id, $currency_id);
			continue;
		}
		
		$earned += round(($amount + $earned) * ($rate/100), 3);
		//update earned
		$query = "UPDATE user_deposit SET earned = '$earned' WHERE deposit_id = '$deposit_id'";
		$conn->query($query);
		
		//update days
		$days_left--;
		$query = "UPDATE user_deposit SET days_left = '$days_left' WHERE deposit_id = '$deposit_id'";
		$conn->query($query);
		
		if($days_left == 0) {
			$left_to_return = $earned + $amount;
			
			//pay back deposit
			$query = "UPDATE user_deposit SET left_to_return = '$left_to_return' WHERE deposit_id = '$deposit_id'";
			$conn->query($query);
			
			payBack($left_to_return, $deposit_id, $user_id, $type, $country_id, $currency_id);
		}
	}
	
	function payBack($left_to_return, $deposit_id, $user_id, $type, $country_id, $currency_id = '', $product_id = 1) {
		global $conn;

		//check if enough currency
		if($type == 'currency') {
			$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
		}
		else if ($type == 'gold') {
			$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
		}
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($available_amount) = $row;
		
		if($available_amount == 0) {
			return;
		}
		
		if($available_amount > $left_to_return) {
			$return_amount = $left_to_return;
		}
		else {
			$return_amount = $available_amount;
		}
		
		//update user's currency/product
		if($type == 'currency') {
			$query = "UPDATE user_currency SET amount = (SELECT * FROM (SELECT amount FROM user_currency 
					  WHERE user_id = '$user_id' AND currency_id = '$currency_id') AS temp) + '$return_amount' WHERE user_id = '$user_id' 
					  AND currency_id = '$currency_id'";
		}
		else if ($type == 'gold') {
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) + '$return_amount' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		$conn->query($query);
		
		//update Banks currency/Gold
		if($type == 'currency') {
			$query = "UPDATE bank_currency SET amount = (SELECT * FROM (SELECT amount FROM bank_currency 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) - '$return_amount' 
					  WHERE country_id = '$country_id' 
					  AND currency_id = '$currency_id'";
		}
		else if ($type == 'gold') {
			$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
					  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) - '$return_amount' 
					  WHERE country_id = '$country_id' AND product_id = '$product_id'";
		}
		$conn->query($query);
		
		//update left_to_return
		$query = "UPDATE user_deposit SET left_to_return = '$left_to_return' - '$return_amount' WHERE deposit_id = '$deposit_id'";
		$conn->query($query);
		
		if($return_amount == $left_to_return) {//if returned all then deactivate
			$query = "UPDATE user_deposit SET active = FALSE WHERE deposit_id = '$deposit_id'";
			$conn->query($query);
		}
		
		//notify
		if($type == 'currency') {
			$query = "SELECT currency_abbr FROM currency WHERE currency_id = '$currency_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($currency_abbr) = $row;
		}
		else {
			$currency_abbr = 'Gold';
		}
		
		$notification = "Bank has paid you $return_amount $currency_abbr for the deposit you made.";
		sendNotification($notification, $user_id);
		
		return;
	}
	
	/* credit */
	//get gold price
	$query = "SELECT price FROM product_price WHERE product_id = 1";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($gold_price) = $row;
	
	$query = "SELECT credit_id, user_id, amount, rate, fee, days_left, returned, uc.currency_id, country_id, type, 
			  (CASE WHEN type = 'gold' THEN 'Gold' ELSE currency_abbr END) 
			  FROM user_credit uc, currency cu WHERE active = TRUE AND cu.currency_id = uc.currency_id";
	$result_credits = $conn->query($query);
	while($row_credits = $result_credits->fetch_row()) {
		list($credit_id, $user_id, $borrowed, $rate, $fee, $days_left, $returned, $currency_id, $country_id, $type, 
			 $currency_abbr) = $row_credits;
		
		$fee += round(($borrowed + $fee - $returned) * ($rate/100), 3);
		
		//did not repayed all dept. 
		$total_to_pay = $borrowed + $fee - $returned;
		
		if($days_left == 0 && $total_to_pay >= 0.001) {
			//update fee
			$query = "UPDATE user_credit SET fee = '$fee' WHERE credit_id = '$credit_id'";
			$conn->query($query);
			
			
			
			//try to collect from products
			if($type == 'currency') {//get currency price
				$query = "SELECT buy_currency_price FROM bank_details WHERE country_id = '$country_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($buy_currency_price) = $row;
				
				//determine credit in gold
				$total_in_vc = ceil(($total_to_pay * $buy_currency_price * $gold_price) * 1000) / 1000;//virtual currency. always fixed.
			}
			else {
				$total_in_vc =  ceil(($total_to_pay * $gold_price) * 1000) / 1000;//virtual currency. always fixed.
			}

			//get users products
			$returned_in_vc = 0;
			$query = "SELECT amount, price, up.product_id, product_name FROM user_product up, product_price pp, product_info pi
					  WHERE user_id = '$user_id' AND pp.product_id = up.product_id
					  AND pi.product_id = up.product_id ORDER BY product_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($amount, $price, $product_id, $product_name) = $row;
				
				if($amount <= 0) {
					continue;
				}
				
				$can_return = 0;
				
				$have_in_vc = $amount * $price;
				if($have_in_vc >= $total_in_vc) {
					$can_return = $total_in_vc;
				}
				else {
					$can_return = $have_in_vc;
				}
				
				//return
				$return_amount = ceil(($can_return / $price) * 1000) / 1000;
				
				//get user product
				$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
						  WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) - '$return_amount' 
						  WHERE user_id = '$user_id' AND product_id = '$product_id'";
				$conn->query($query);
				
				$total_in_vc -= $return_amount * $price;
				$returned_in_vc += $return_amount * $price;
				
				//notify user
				$notification = "Bank have confiscated $return_amount $product_name for the credit.";
				sendNotification($notification, $user_id);
				
				if($total_in_vc <= 0) {
					break;
				}
			}
			
			echo "$returned_in_vc, ";
			
			//update credit details
			$returned_in_gold = floor(($returned_in_vc / $gold_price) * 1000) / 1000;
			if($type == 'currency') {
				$returned = floor((($returned_in_vc / $gold_price) / $buy_currency_price) * 1000) / 1000;
			}
			else {
				$returned = $returned_in_gold;
			}
			
			$query = "UPDATE user_credit SET returned = '$returned' WHERE credit_id = '$credit_id'";
			$conn->query($query);
			
			$payed_all = false;
			if($returned >= $total_to_pay) {
				$payed_all = true;
				
				$query = "UPDATE user_credit SET active = FALSE WHERE credit_id = '$credit_id'";
				$conn->query($query);
			}
			
			//update Banks Gold
			$query = "SELECT * FROM bank_product WHERE country_id = '$country_id' AND product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO bank_product VALUES ('$country_id', '$product_id', '$returned_in_gold')";
			}
			else {
				$query = "UPDATE bank_product SET amount = (SELECT * FROM(SELECT amount FROM bank_product 
						  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp) + '$returned_in_gold' 
						  WHERE country_id = '$country_id' AND product_id = '$product_id'";
			}
			$conn->query($query);
		
			continue;
		}
		else if ($days_left == 0 && $total_to_pay < 0.001) {
			$query = "UPDATE user_credit SET active = FALSE WHERE credit_id = '$credit_id'";
			$conn->query($query);
		}
		
		//update fee
		$query = "UPDATE user_credit SET fee = '$fee' WHERE credit_id = '$credit_id'";
		$conn->query($query);
		
		$total_to_pay = round($borrowed + $fee - $returned, 3);
		if($days_left > 0) {
			$amount_to_pay = round(($total_to_pay / $days_left), 3);
		}
		else {
			$amount_to_pay = $total_to_pay;
		}
		
		$notification = "Don\'t forget to pay at least $amount_to_pay $currency_abbr today for the credit. The total left is" .
						" $total_to_pay $currency_abbr.";
		sendNotification($notification, $user_id);
		
		if($days_left <= 3) {
			$notification = "You have $days_left more day/s to pay back your debt in the amount of $total_to_pay $currency_abbr." .
							" Otherwise, Bank might confiscate your inventory or companies for repayment of the debt."; 
			sendNotification($notification, $user_id);
		}
		
		//update days
		$days_left--;
		$query = "UPDATE user_credit SET days_left = '$days_left' WHERE credit_id = '$credit_id'";
		$conn->query($query);
	}
?>