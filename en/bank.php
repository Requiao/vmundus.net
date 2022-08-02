<?php 
	include('head.php'); 
	include('../php_functions/cut_long_name.php');//cutLongName($string, $max_length)
?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head">Bank</p>
		
		<div id="page_menu">
			<p id="pm_deposit">Deposit</p>
			<p id="pm_credit">Credit</p>
			<p id="pm_exchange">Exchange</p>
		</div>
		<?php
			//get user's country
			$query = "SELECT citizenship, currency_id FROM user_profile up, country c 
					  WHERE user_id = '$user_id' AND c.country_id = citizenship";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($country_id, $currency_id) = $row;
			
			//get credit/deposit details
			$query = "SELECT currency_credit_rate, currency_credit_limit, gold_credit_rate, gold_credit_limit, credit_max_days,
					  currency_deposit_rate, currency_deposit_limit, gold_deposit_rate, gold_deposit_limit, deposit_max_days,
					  currency_abbr
					  FROM bank_details cdr, currency 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($currency_credit_rate, $currency_credit_limit, $gold_credit_rate, $gold_credit_limit, $credit_max_days,
				 $currency_deposit_rate, $currency_deposit_limit, $gold_deposit_rate, $gold_deposit_limit, 
				 $deposit_max_days, $currency_abbr) = $row;
			
			//display bank manager
			$query = "SELECT cdr.user_id, user_name, user_image, manager_salary, day_number, manager_from_date 
					  FROM bank_details cdr, users u, user_profile up, day_count dc
					  WHERE cdr.country_id = '$country_id' AND u.user_id = cdr.user_id AND up.user_id = cdr.user_id
					  AND dc.date = cdr.manager_from_date";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$manager_id = 0;
				$manager_name = "Not Assigned";
				$salary = 0;
			}
			else {
				$row = $result->fetch_row();
				list($manager_id, $manager_name, $manager_img, $salary, $day_elected, $date_elected) = $row;
				
				$correct_date_elected = date('M j', strtotime(correctDate($date_elected, date('H:i:s', strtotime('0:0:0')), $country_id)));
				$date_elected = date('M j', strtotime($date_elected));
				if(strtotime($date_elected) < strtotime($correct_date_elected)) {
					$day_elected++;
				}
				else if(strtotime($date_elected) > strtotime($correct_date_elected)) {
					$day_elected--;
				}
			}
			
			echo "\n\t\t" . '<div id="manager_div">' .
				 "\n\t\t\t" . '<a href="user_profile?id=' . $manager_id . 
							  '" id="cgd_name" target="_blank">' . $manager_name .'</a>' .
				 "\n\t\t\t" . '<img src="../user_images/' . $manager_img . '" id="manager_image">' .
				 "\n\t\t\t" . '<p id="position_name">Secretary of the Treasury</p>' .
				 "\n\t\t\t" . '<div id="manager_info_class">' .
				 "\n\t\t\t\t" . '<p id="elected_since" class="manager_info">On duty since day: <span>' . $day_elected . '</span></p>' .
				 "\n\t\t\t\t" . '<p id="elected_until_day" class="manager_info">On duty until day: <span>N/A</span></p>' .
				 "\n\t\t\t\t" . '<p id="manager_salary" class="manager_info">Salary: <span>' . 
								 number_format($salary, '2', '.', ' ') . ' ' . $currency_abbr . '</span></p>' .
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t" . '</div>';

			//if governor then allow to invest currency into the bank
			//check if governor
			$is_governor = false;
			$query = "SELECT position_id FROM country_government WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$is_governor = true;
				$row = $result->fetch_row();
				list($position_id) = $row;
			}
			
			if($is_governor) {
				echo "\n\t\t" . '<div id="invest_into_bank_div">' .
					 "\n\t\t\t" . '<p id="invest_gold_lbl">Invest Gold</p>' .
					 "\n\t\t\t" . '<input id="invest_gold_amount" type="text" maxlength="7" placeholder="amount">' .
					 "\n\t\t\t" . '<p class="button green invest_submit" id="invest_gold">Invest</p>' .
					 "\n\t\t\t" . '<p hidden>gold</p>' .
					 "\n\t\t\t" . '<p id="invest_currency_lbl">Invest Currency</p>' .
					 "\n\t\t\t" . '<input id="invest_currency_amount" type="text" maxlength="7" placeholder="amount">' .
					 "\n\t\t\t" . '<p class="button green invest_submit" id="invest_currency">Invest</p>' .
					 "\n\t\t\t" . '<p hidden>currency</p>' .
					 "\n\t\t" . '</div>';
			}
			
			//manage bank
			if($user_id == $manager_id) {
				echo "\n\t\t" . '<div id="manage_bank">' .
					 "\n\t\t" . '<p class="div_head">Manage Bank</p>' .
					 "\n\t\t\t" . '<div class="mb_divs">' .
					 "\n\t\t\t\t" . '<p class="mbd_lbl">New currency buy price</p>' .
					 "\n\t\t\t\t" . '<input class="mbd_input" type="text" maxlength="7" placeholder="price">' .
					 "\n\t\t\t\t" . '<p class="button green set_rule">Apply</p>' .
					 "\n\t\t\t\t" . '<p hidden>set_currency_buy_price</p>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div class="mb_divs">' .
					 "\n\t\t\t\t" . '<p class="mbd_lbl">New currency sell price</p>' .
					 "\n\t\t\t\t" . '<input class="mbd_input" type="text" maxlength="7" placeholder="price">' .
					 "\n\t\t\t\t" . '<p class="button green set_rule">Apply</p>' .
					 "\n\t\t\t\t" . '<p hidden>set_currency_sell_price</p>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
			
			//if governor or manager, then display bank's treasury
			if($is_governor || $user_id == $manager_id) {
				$query = "SELECT amount FROM bank_product WHERE country_id = '$country_id' AND product_id = 1";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$bank_gold = 0;
				}
				else {
					$row = $result->fetch_row();
					list($bank_gold) = $row;
				}
					
				$query = "SELECT amount FROM bank_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$bank_currency = 0;
				}
				else {
					$row = $result->fetch_row();
					list($bank_currency) = $row;
				}
				
				echo "\n\t\t" . '<div id="bank_treasury">' .
					 "\n\t\t\t" . '<p class="div_head">Bank Treasury</p>' .
					 "\n\t\t\t" . '<div class="bank_currency">' .
					 "\n\t\t\t\t" . '<p class="currency_amount">' . number_format($bank_currency, '3', '.', ' ') . 
								  ' (' . $currency_abbr . ')</p>';
				if($user_id == $manager_id) {
					echo "\n\t\t\t\t" . '<input class="return_amount" type="text" maxlength="7" placeholder="amount">' .
						 "\n\t\t\t\t" . '<p class="button green return_to_country">Return</p>' .
						 "\n\t\t\t\t" . '<p hidden>currency</p>';
				}
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div class="bank_currency">' .
					 "\n\t\t\t\t" . '<p class="currency_amount">' . number_format($bank_gold, '3', '.', ' ') . ' (Gold)</p>';
				if($user_id == $manager_id) {
					echo "\n\t\t\t\t" . '<input class="return_amount" type="text" maxlength="7" placeholder="amount">' .
						 "\n\t\t\t\t" . '<p class="button green return_to_country">Return</p>' .
						 "\n\t\t\t\t" . '<p hidden>gold</p>';
				}
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
			
			echo "\n\t\t" . '<div id="credit_deposit_info_div">' .
				 "\n\t\t\t" . '<div class="cdid_info_div">' .
				 "\n\t\t\t\t" . '<p class="div_head">Credit Rate</p>' .
				 "\n\t\t\t\t" . '<div class="cdid_headings">' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_type">Type</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_rate">Rate</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_limit">Limit</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_days">Days</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<div class="cdid_details">' .
				 "\n\t\t\t\t\t" . '<p class="cdid_type_name">' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_rate credit_rate">' . $currency_credit_rate . '%</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_limit">' . number_format($currency_credit_limit, '2', '.', ' ') . '</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_days">' . $credit_max_days . '</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<div class="cdid_details">' .
				 "\n\t\t\t\t\t" . '<p class="cdid_type_name">Gold</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_rate credit_rate">' . $gold_credit_rate . '%</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_limit">' . $gold_credit_limit . '</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_days">' . $credit_max_days . '</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t\t" . '<div class="cdid_info_div">' .
				 "\n\t\t\t\t" . '<p class="div_head">Deposit Rate</p>' .
				 "\n\t\t\t\t" . '<div class="cdid_headings">' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_type">Type</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_rate">Rate</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_limit">Limit</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdidh_days">Days</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<div class="cdid_details">' .
				 "\n\t\t\t\t\t" . '<p class="cdid_type_name">' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_rate debosit_rate">' . $currency_deposit_rate . '%</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_limit">' . number_format($currency_deposit_limit, '2', '.', ' ') . '</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_days">' . $deposit_max_days . '</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<div class="cdid_details">' .
				 "\n\t\t\t\t\t" . '<p class="cdid_type_name">Gold</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_rate debosit_rate">' . $gold_deposit_rate . '%</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_limit">' . $gold_deposit_limit . '</p>' .
				 "\n\t\t\t\t\t" . '<p class="cdid_days">' . $deposit_max_days . '</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t" . '</div>';
				 
			/* Deposit div */
			echo "\n\t\t" . '<div id="deposit_div">' .
				 "\n\t\t\t" . '<div class="credit_deposit_divs">' .
				 "\n\t\t\t\t" . '<div class="make_credit_deposit_div">' .
				 "\n\t\t\t\t\t" . '<p class="mdd_head">Make Deposit in Currency</p>' .
				 "\n\t\t\t\t\t" . '<p class="mdd_type">' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t\t" . '<input class="amount_input" type="text" maxlength="8" placeholder="amount">' .
				 "\n\t\t\t\t\t" . '<input class="days_input" type="text" maxlength="2" placeholder="days">' .
				 "\n\t\t\t\t\t" . '<p class="button green deposit_submit">Submit</p>' .
				 "\n\t\t\t\t\t" . '<p hidden>currency</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<div class="make_credit_deposit_div">' .
				 "\n\t\t\t\t\t" . '<p class="mdd_head">Make Deposit in Gold</p>' .
				 "\n\t\t\t\t\t" . '<p class="mdd_type">Gold</p>' .
				 "\n\t\t\t\t\t" . '<input class="amount_input" type="text" maxlength="8" placeholder="amount">' .
				 "\n\t\t\t\t\t" . '<input class="days_input" type="text" maxlength="2" placeholder="days">' .
				 "\n\t\t\t\t\t" . '<p class="button green deposit_submit">Submit</p>' .
				 "\n\t\t\t\t\t" . '<p hidden>gold</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t" . '</div>';
			
			//user deposits
			echo "\n\t\t\t" . '<div id="user_deposits_div">' .
				 "\n\t\t\t\t" . '<p class="div_head">My Deposits</p>' .
				 "\n\t\t\t\t" . '<div class="udd_heads">' .
				 "\n\t\t\t\t\t" . '<p class="uddh_user">User</p>' .
				 "\n\t\t\t\t\t" . '<p class="uddh_amount">Deposit</p>' .
				 "\n\t\t\t\t\t" . '<p class="uddh_rate">Rate</p>' .
				 "\n\t\t\t\t\t" . '<p class="uddh_earned">Earned</p>' .
				 "\n\t\t\t\t\t" . '<p class="uddh_days">Days</p>' .
				 "\n\t\t\t\t\t" . '<p class="uddh_days_left">Days Left</p>' .
				 "\n\t\t\t\t" . '</div>';
			$query = "SELECT amount, days, rate, earned, days_left, ud.user_id, user_image, user_name,
					  (CASE WHEN type = 'currency' THEN currency_abbr ELSE 'Gold' END)
					  FROM user_deposit ud, currency cu, users u, user_profile up
					  WHERE cu.currency_id = ud.currency_id AND active = TRUE AND ud.user_id = '$user_id'
					  AND u.user_id = ud.user_id AND up.user_id = u.user_id
					  ORDER BY days_left";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($amount, $days, $rate, $earned, $days_left, $profile_id, $user_image, $user_name, $currency_abbr) = $row;
				
				echo "\n\t\t\t\t" . '<div class="udd_deposits">' .
					 "\n\t\t\t\t\t" . '<a class="uddd_user_name" href="user_profile?id=' . $profile_id . 
									  '" target="_blank">' . $user_name . '</a>' .
					 "\n\t\t\t\t\t" . '<img class="uddd_image" src="../user_images/' . $user_image . '">' .
					 "\n\t\t\t\t\t" . '<p class="uddd_amount">' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddd_rate">+' . $rate . '%</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddd_earned">+' . number_format($earned, '3', '.', ' ') . ' ' . $currency_abbr . '</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddd_days">' . $days . ' Days</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddd_days_left">' . $days_left . ' Left</p>' .
					 "\n\t\t\t\t" . '</div>';
			}
			echo "\n\t\t\t" . '</div>';
			
			//if bank manager then display all deposits
			if($user_id == $manager_id) {
				echo "\n\t\t\t" . '<div id="all_deposits_div">' .
					 "\n\t\t\t\t" . '<p class="div_head">All Deposits</p>' .
					 "\n\t\t\t\t" . '<div class="udd_heads">' .
					 "\n\t\t\t\t\t" . '<p class="uddh_user">User</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddh_amount">Deposit</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddh_rate">Rate</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddh_earned">Earned</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddh_days">Days</p>' .
					 "\n\t\t\t\t\t" . '<p class="uddh_days_left">Days Left</p>' .
					 "\n\t\t\t\t" . '</div>';
				$query = "SELECT amount, days, rate, earned, days_left, ud.user_id, user_image, user_name,
						 (CASE WHEN type = 'currency' THEN currency_abbr ELSE 'Gold' END)
						  FROM user_deposit ud, currency cu, users u, user_profile up
						  WHERE cu.currency_id = ud.currency_id AND active = TRUE AND country_id = '$country_id'
						  AND u.user_id = ud.user_id AND up.user_id = u.user_id
						  ORDER BY days_left";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($amount, $days, $rate, $earned, $days_left, $profile_id, $user_image, $user_name, $currency_abbr) = $row;
					
					echo "\n\t\t\t\t" . '<div class="udd_deposits">' .
						 "\n\t\t\t\t\t" . '<a class="uddd_user_name" href="user_profile?id=' . $profile_id . 
										  '" target="_blank">' . $user_name . '</a>' .
						 "\n\t\t\t\t\t" . '<img class="uddd_image" src="../user_images/' . $user_image . '">' .
						 "\n\t\t\t\t\t" . '<p class="uddd_amount">' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="uddd_rate">+' . $rate . '%</p>' .
						 "\n\t\t\t\t\t" . '<p class="uddd_earned">+' . number_format($earned, '3', '.', ' ') . ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="uddd_days">' . $days . ' Days</p>' .
						 "\n\t\t\t\t\t" . '<p class="uddd_days_left">' . $days_left . ' Left</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			/* credit div */
			echo "\n\t\t" . '<div id="credit_div">' .
				 "\n\t\t\t" . '<div class="credit_deposit_divs">' .
				 "\n\t\t\t\t" . '<div class="make_credit_deposit_div">' .
				 "\n\t\t\t\t\t" . '<p class="mdd_head">Get Credit in Currency</p>' .
				 "\n\t\t\t\t\t" . '<p class="mdd_type">' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t\t" . '<input class="amount_input" type="text" maxlength="8" placeholder="amount">' .
				 "\n\t\t\t\t\t" . '<input class="days_input" type="text" maxlength="2" placeholder="days">' .
				 "\n\t\t\t\t\t" . '<p class="button green credit_submit">Submit</p>' .
				 "\n\t\t\t\t\t" . '<p hidden>currency</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<div class="make_credit_deposit_div">' .
				 "\n\t\t\t\t\t" . '<p class="mdd_head">Get Credit in Gold</p>' .
				 "\n\t\t\t\t\t" . '<p class="mdd_type">Gold</p>' .
				 "\n\t\t\t\t\t" . '<input class="amount_input" type="text" maxlength="8" placeholder="amount">' .
				 "\n\t\t\t\t\t" . '<input class="days_input" type="text" maxlength="2" placeholder="days">' .
				 "\n\t\t\t\t\t" . '<p class="button green credit_submit">Submit</p>' .
				 "\n\t\t\t\t\t" . '<p hidden>gold</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t" . '</div>';
		
			//user credits
			echo "\n\t\t\t" . '<div id="user_credits_div">' .
				 "\n\t\t\t\t" . '<p class="div_head">My Credits</p>' .
				 "\n\t\t\t\t" . '<div class="ucd_heads">' .
				 "\n\t\t\t\t\t" . '<p class="ucdh_user">User</p>' .
				 "\n\t\t\t\t\t" . '<p class="ucdh_amount">Credit</p>' .
				 "\n\t\t\t\t\t" . '<p class="ucdh_rate">Rate</p>' .
				 "\n\t\t\t\t\t" . '<p class="ucdh_fee">Fee</p>' .
				 "\n\t\t\t\t\t" . '<p class="ucdh_days_left">Days Left</p>' .
				 "\n\t\t\t\t\t" . '<p class="ucdh_left_to_return">Must Return</p>' .
				 "\n\t\t\t\t" . '</div>';
			$query = "SELECT credit_id, amount, days, rate, fee, days_left, uc.user_id, user_image, user_name, returned,
					 (CASE WHEN type = 'currency' THEN currency_abbr ELSE 'Gold' END)
					  FROM user_credit uc, currency cu, users u, user_profile up
					  WHERE cu.currency_id = uc.currency_id AND active = TRUE AND uc.user_id = '$user_id'
					  AND u.user_id = uc.user_id AND up.user_id = u.user_id
					  ORDER BY days_left, credit_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($credit_id, $amount, $days, $rate, $fee, $days_left, $profile_id, $user_image, $user_name, $returned, 
					 $currency_abbr) = $row;
	
				$left_to_return = $amount + $fee - $returned;
				
				echo "\n\t\t\t\t" . '<div class="ucd_credits">' .
					 "\n\t\t\t\t\t" . '<a class="ucdc_user_name" href="user_profile?id=' . $profile_id . 
									  '" target="_blank">' . $user_name . '</a>' .
					 "\n\t\t\t\t\t" . '<img class="ucdc_image" src="../user_images/' . $user_image . '">' .
					 "\n\t\t\t\t\t" . '<p class="ucdc_amount">' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdc_rate">+' . $rate . '%</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdc_fee">+' . number_format($fee, '3', '.', ' ') . ' ' . $currency_abbr . '</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdc_days_left">' . $days_left . ' Left</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdc_left_to_return">' . number_format($left_to_return, '3', '.', ' ') . 
									  ' ' . $currency_abbr . '</p>' .
					 "\n\t\t\t\t\t" . '<input class="ucdc_amount_input" type="text" maxlength="8" placeholder="amount">' .
					 "\n\t\t\t\t\t" . '<p class="button green return_credit">Return</p>' .
					 "\n\t\t\t\t\t" . '<p hidden>' . $credit_id . '</p>' .
					 "\n\t\t\t\t" . '</div>';
			}
			echo "\n\t\t\t" . '</div>';
			
			//if bank manager then display all credits
			if($user_id == $manager_id) {
				echo "\n\t\t\t" . '<div id="all_credits_div">' .
					 "\n\t\t\t\t" . '<p class="div_head">All Credits</p>' .
					 "\n\t\t\t\t" . '<div class="ucd_heads">' .
					 "\n\t\t\t\t\t" . '<p class="ucdh_user">User</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdh_amount">Credit</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdh_rate">Rate</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdh_fee">Fee</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdh_days_left">Days Left</p>' .
					 "\n\t\t\t\t\t" . '<p class="ucdh_left_to_return">Must Return</p>' .
					 "\n\t\t\t\t" . '</div>';
				$query = "SELECT amount, days, rate, fee, days_left, uc.user_id, user_image, user_name, returned,
						 (CASE WHEN type = 'currency' THEN currency_abbr ELSE 'Gold' END)
						  FROM user_credit uc, currency cu, users u, user_profile up
						  WHERE cu.currency_id = uc.currency_id AND active = TRUE AND country_id = '$country_id'
						  AND u.user_id = uc.user_id AND up.user_id = u.user_id
						  ORDER BY days_left, credit_id";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($amount, $days, $rate, $fee, $days_left, $profile_id, $user_image, $user_name, $returned, $currency_abbr) = $row;
					
					$left_to_return = $amount + $fee - $returned;
					
					echo "\n\t\t\t\t" . '<div class="ucd_credits">' .
						 "\n\t\t\t\t\t" . '<a class="ucdc_user_name" href="user_profile?id=' . $profile_id . 
										  '" target="_blank">' . $user_name . '</a>' .
						 "\n\t\t\t\t\t" . '<img class="ucdc_image" src="../user_images/' . $user_image . '">' .
						 "\n\t\t\t\t\t" . '<p class="ucdc_amount">' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="ucdc_rate">+' . $rate . '%</p>' .
						 "\n\t\t\t\t\t" . '<p class="ucdc_fee">+' . number_format($fee, '3', '.', ' ') . ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="ucdc_days_left">' . $days_left . ' Left</p>' .
						 "\n\t\t\t\t\t" . '<p class="ucdc_left_to_return">' . number_format($left_to_return, '3', '.', ' ') . 
									  ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
			}
		echo "\n\t\t" . '</div>';
		
		//exchange
		echo "\n\t\t" . '<div id="exchange_div">';
		
		//buy currency
		echo "\n\t\t\t" . '<div class="buy_currency_block">' .
			 "\n\t\t\t\t" . '<p class="div_head">Buy Currency</p>' .
			 "\n\t\t\t\t" . '<div id="bcb_heads">' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_currency">Currency</p>' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_price">Price for 1cc</p>' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_limit">Limit</p>' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_amount">Amount</p>' .
			 "\n\t\t\t\t" . '</div>' .
			 "\n\t\t\t\t" . '<div id="buy_currency_list">' .
			 "\n\t\t\t\t\t" . '<div id="buy_selected_currency">' . 
			 "\n\t\t\t\t\t\t" . '<p>Select Currency</p>' . 
			 "\n\t\t\t\t\t" . '</div>' .  
			 "\n\t\t\t\t\t" . '<p id="buy_country_id" hidden>0</p>' .  
			 "\n\t\t\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
			 "\n\t\t\t\t" . '</div>' .
			 "\n\t\t\t\t" . '<div id="buy_currency_div">';
		
		$query = "SELECT currency_name, currency_abbr, flag, c.country_id FROM country c, currency cu WHERE
				  cu.flag_id = c.country_id ORDER BY currency_name";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($currency_name, $currency_abbr, $flag, $country_id) = $row;
			echo "\n\t\t\t\t\t" . '<div class="buy_currency">' . 
				 "\n\t\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
				 "\n\t\t\t\t\t\t" . '<p>' . $currency_name . ' (' . $currency_abbr . ')</p>' . 
				 "\n\t\t\t\t\t\t" . '<p class="buy_country_id" hidden>' . $country_id . '</p>' . 
				 "\n\t\t\t\t\t" . '</div>';
		}
		echo "\n\t\t\t\t" . '</div>' .
			 "\n\t\t\t\t" . '<p id="currency_price">N/A</p>' .
			 "\n\t\t\t\t" . '<p id="buy_limit">N/A</p>' .
			 "\n\t\t\t\t" . '<input id="buy_currency_amount" type="text" maxlength="8" placeholder="amount">' .
			 "\n\t\t\t\t" . '<p class="button green" id="buy_currency">Buy</p>' .
			 "\n\t\t\t" . '</div>';
		
		//sell currency
		echo "\n\t\t\t" . '<div class="buy_currency_block">' .
			 "\n\t\t\t\t" . '<p class="div_head">Sell Currency</p>' .
			 "\n\t\t\t\t" . '<div id="bcb_heads">' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_currency">Currency</p>' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_price">Price for 1cc</p>' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_limit">Limit</p>' .
			 "\n\t\t\t\t\t" . '<p id="bcbh_amount">Amount</p>' .
			 "\n\t\t\t\t" . '</div>';
		
		//get user currency
		$query = "SELECT amount, currency_abbr, flag, sell_currency_price, sell_currency_limit, c.country_id 
				  FROM user_currency uc, currency cu, country c, bank_details bd
				  WHERE uc.user_id = '$user_id' AND c.currency_id = cu.currency_id
				  AND cu.currency_id = uc.currency_id AND amount > 0
                  AND bd.country_id = c.country_id
                  ORDER BY amount DESC";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($amount, $currency_abbr, $flag, $sell_currency_price, $sell_currency_limit, $country_id) = $row;
			echo "\n\t\t\t" . '<div class="bcb_info" id="ucu_' . $country_id . '">' .
				 "\n\t\t\t\t" . '<img class="bcbi_img" src="../country_flags/' . $flag . '" alt="' . $currency_abbr . '">' .
				 "\n\t\t\t\t" . '<p class="currency_amount">' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t" . '<p id="currency_price">' . number_format($sell_currency_price, '3', '.', ' ') . ' Gold</p>' .
				 "\n\t\t\t\t" . '<p id="buy_limit">' . number_format($sell_currency_limit, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t" . '<input id="buy_currency_amount" type="text" maxlength="8" placeholder="amount">' .
				 "\n\t\t\t\t" . '<p class="button green sell_currency">Sell</p>' .
				 "\n\t\t\t\t" . '<p hidden>' . $country_id . '</p>' . 
				 "\n\t\t\t" . '</div>';
		}
		echo "\n\t\t\t\t" . '</div>';
		
		
		echo "\n\t\t\t" . '</div>';
		
		?>
	
	</div>
	
</main>
	
<?php include('footer.php'); ?> 