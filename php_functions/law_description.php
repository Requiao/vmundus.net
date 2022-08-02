<?php
	function lawDescription($responsibility_id, $law_id) {
		global $conn;
		global $country_id;
		
		//generate description
		$description = '';
		//change gov term length
		if($responsibility_id == 1) {
			$query = "SELECT name, new_term FROM government_positions gp, change_gov_term_length cgtl
					  WHERE law_id = '$law_id' AND cgtl.position_id = gp.position_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($name, $new_term) = $row_desc;
			$description = "Set new term for $name to $new_term days."; 
		}
		//join leave union
		else if($responsibility_id == 2) {
			$query = "SELECT action, union_name, u.union_id, as_founder FROM join_leave_union jlu, unions u
					  WHERE jlu.union_id = u.union_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($action, $union_name, $union_id, $as_founder) = $row_desc;
			if($action == 0) {
				$description = 'Leave union <a href="union_info?union_id=' . $union_id . '" target="_blank">' . $union_name . '</a>.'; 
			}
			else if ($action == 1) {
				if($as_founder == 0) {
					$description = 'Join union <a href="union_info?union_id=' . $union_id . 
								   '" target="_blank">' . $union_name . '</a> without founder rights.';
				}
				else {
					$description = 'Join union <a href="union_info?union_id=' . $union_id . 
								   '" target="_blank">' . $union_name . '</a> with founder rights.';
				}
			}
		}
		//Print money
		else if($responsibility_id == 3) {
			$query = "SELECT amount, currency_abbr FROM print_money pm, currency cu
					  WHERE cu.currency_id = pm.currency_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($amount, $currency_abbr) = $row_desc;
			$description = 'Do you agree to print ' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '?'; 
		}
		//Impeach president.
			else if($responsibility_id == 4) {
				$description = 'Do you agree to impeach president?'; 
			}
		//Dissolve Congress.
		else if($responsibility_id == 5) {
			$description = 'Do you agree to dissolve congress?'; 
		}
		//Import permission/embargo.
		else if($responsibility_id == 6) {
			$query = "SELECT country_id, country_name, product_name, sale_tax, days, action 
					  FROM product_import_law_info pili, country c, product_info pi
					  WHERE pili.law_id = '$law_id' AND country_id = from_country_id
					  AND pi.product_id = pili.product_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($from_country_id, $from_country_name, $product_name, $sale_tax, $days, $action ) = $row_desc;
			if($action == 0) { //embargo
				$description = 'Do you agree to set embargo on ' . $product_name . ' from <a href="country?country_id=' . 
								$from_country_id . '" target="_blank">' . $from_country_name . '</a>?';
			}
			else if($action == 1) {//permission
				$description = 'Do you agree to give permission to import ' . $product_name . ' from 
								<a href="country?country_id=' . $from_country_id . '" target="_blank">' . 
								$from_country_name . '</a> for the period of ' . $days . ' days with ' . 
								number_format($sale_tax, '2', '.', ' ') . '% tax?';
			}
		}
		//Accept/decline new member application to union.
		else if($responsibility_id == 7) {
			$query = "SELECT c.country_id, country_name, as_founder FROM join_leave_union jlu, country c
					  WHERE law_id = (SELECT applicant_law_id FROM processing_union_application WHERE founder_law_id = '$law_id')
					  AND c.country_id = jlu.country_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($accept_country_id, $applicant_name, $as_founder) = $row_desc;
			if($as_founder == 0) {
				$description = 'Do you agree to accept <a href="country?country_id=' . $accept_country_id . '" target="_blank">' . 
								$applicant_name . '</a> to your union without founder rights?'; 
			}
			else if ($as_founder == 1) {
				$description = 'Do you agree to accept <a href="country?country_id=' . $accept_country_id . '" target="_blank">' . 
								$applicant_name . '</a> to your union with founder rights?'; 
			}
		}
		//Change responsibilities.
		else if($responsibility_id == 8) {
			$query = "SELECT name, responsibility, must_sign_vote, add_remove, can_issue 
					  FROM change_responsibilities cr, government_positions gp, political_responsibilities pr
					  WHERE gp.position_id = cr.position_id AND pr.responsibility_id = cr.responsibility_id
					  AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($position_name, $responsibility_name, $must_sign_vote, $add_remove, $can_issue) = $row_desc;
			if($add_remove == 0) {//remove
				if($can_issue == 1 && $must_sign_vote == 1) {
					$description = "Do you want to take away permission from $position_name to ISSUE and VOTE for " .
								   "\"$responsibility_name\" law?";
				}
				else if ($can_issue == 1) {
					$description = "Do you want to take away permission from $position_name to ISSUE " .
								   "\"$responsibility_name\" law?";
				}
				else if($must_sign_vote == 1) {
					$description = "Do you want to take away permission from $position_name to VOTE for " .
								   "\"$responsibility_name\" law?";
				}
			}
			else if($add_remove == 1) {//add
				if($can_issue == 1 && $must_sign_vote == 1) {
					$description = "Do you want to give permission for $position_name to ISSUE and VOTE for " .
								   "\"$responsibility_name\" law?";
				}
				else if ($can_issue == 1) {	   
					$description = "Do you want to give permission for $position_name to ISSUE " .
								   "\"$responsibility_name\" law without ability to VOTE for it?";
				}
				else if($must_sign_vote == 1) {
					$description = "Do you want to give permission for $position_name to VOTE for " .
								   "\"$responsibility_name\" law?";
				}
			}
		}
		//Declare war.
		else if($responsibility_id == 9) {
			$query = "SELECT country_name FROM country
					  WHERE country_id = (SELECT with_country_id FROM declare_war WHERE law_id = '$law_id')";
			$result_war = $conn->query($query);
			$row_war = $result_war->fetch_row();
			list($war_to_country_name) = $row_war;
			$description = 'Do you want to declare war to ' . $war_to_country_name . '?'; 
		}
		//Sign peace treaty.
		else if($responsibility_id == 10) {
			$query = "SELECT country_name FROM country
					  WHERE country_id = (SELECT with_country_id FROM sign_peace_treaty WHERE law_id = '$law_id')";
			$result_war = $conn->query($query);
			$row_war = $result_war->fetch_row();
			list($war_to_country_name) = $row_war;
			$description = 'Do you want to sign peace treaty with ' . $war_to_country_name . '?'; 
		}
		//Assign ministers. //Assign Prime Minister
		else if($responsibility_id == 11 || $responsibility_id == 12) {
			$query = "SELECT aml.user_id, user_name, name, assign FROM assign_ministers_law aml LEFT JOIN users u
					  ON u.user_id = aml.user_id, government_positions gp
					  WHERE gp.position_id = aml.position_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($new_minister_id, $new_minister_name, $position_name, $assign) = $row_desc;
			if($assign == 1) {
				$description = 'Do you want <a href="user_profile?id=' . $new_minister_id . '" target="_blank">' . 
								$new_minister_name . '</a> to become new ' . $position_name . ' ?'; 
			}
			else if($assign == 0) {
				$description = 'Do you want fire ' . $position_name . ' ?'; 
			}
		}
		//Change taxes.
		else if($responsibility_id == 13) {
			$query = "SELECT product_name, tax, type FROM new_tax_law ntl LEFT JOIN product_info pi ON
					  ntl.product_id = pi.product_id WHERE law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($product_name, $tax, $type) = $row_desc;
			if($type == 0) { //income
				$description = 'Do you agree to set new tax on income at ' . number_format($tax, '2', '.', ' ') . '%?';
			}
			else if($type == 1) {//product
				$description = 'Do you agree to set new tax on ' . $product_name . ' at ' . number_format($tax, '2', '.', ' ') . '%?';
			}
		}
		//Travel agreement.
		else if($responsibility_id == 14) {
			$query = "SELECT country_id, country_name, days, action FROM country c, travel_agreement_law tal
				  WHERE c.country_id = tal.from_country_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($from_country_id, $from_country_name, $days, $action ) = $row_desc;
			if($action == 0) { //ban
				$description = 'Do you agree to ban entry permit for citizens from <a href="country?country_id=' . 
								$from_country_id . '" target="_blank">' . $from_country_name . '</a>?';
			}
			else if($action == 1) {//allow
				$description = 'Do you agree to give entry permit for citizens from
								<a href="country?country_id=' . $from_country_id . '" target="_blank">' . 
								$from_country_name . '</a> for the period of ' . $days . ' days?';
			}
		}
		//Give/Ban permission for foreigners to build companies.
		else if($responsibility_id == 15) {
			$query = "SELECT country_id, country_name, name, price, action 
					  FROM foreign_building_policy_law fbpl, country c, building_info bi
					  WHERE fbpl.law_id = '$law_id' AND country_id = foreign_country_id
					  AND bi.building_id = fbpl.building_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($from_country_id, $from_country_name, $building_name, $price, $action ) = $row_desc;
			if($action == 0) { //ban
				$description = 'Do you want to suspend building permit for ' . $building_name . ' for
								<a href="country?country_id=' . 
								$from_country_id . '" target="_blank">' . $from_country_name . '</a> citizens?';
			}
			else if($action == 1) {//permission
				$description = 'Do you agree to give permit for citizens from
								<a href="country?country_id=' . $from_country_id . '" target="_blank">' . 
								$from_country_name . '</a>  to build ' . $building_name . ' for ' . 
								number_format($price, '2', '.', ' ') . ' Gold?';
			}
		}
		//Assign new union leader
		else if($responsibility_id == 16) {
			$query = "SELECT user_name, nul.user_id FROM new_union_leader nul, users u
					  WHERE u.user_id = nul.user_id AND law_id = 
					  (SELECT primary_law_id FROM processing_union_leader WHERE founders_law_id = '$law_id')";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($new_leader_name, $new_leader_id) = $row_desc;
			$description = 'Do you want <a href="user_profile?id=' . $new_leader_id . '" target="_blank">' . 
							$new_leader_name . '</a> to become new union leader?'; 
		}
		//Change government salaries.
		else if($responsibility_id == 17) {
			$query = "SELECT name, salary FROM change_gov_salary cgl, government_positions gp
					  WHERE gp.position_id = cgl.position_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($position_name, $salary) = $row_desc;
			$description = 'Do you want to change salary for ' . $position_name . ' to ' .
							number_format($salary, '2', '.', ' ') . ' country currency?';
		}
		//Change credit/deposit rate
		else if($responsibility_id == 18) {
			$query = "SELECT rate, type, credit_deposit_type FROM change_credit_deposit_rate WHERE law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($rate, $type, $credit_deposit_type) = $row_desc;
			if($type == 'gold' && $credit_deposit_type == 'credit') {
				$description = 'Do you want to change <b>Gold Credit Rate</b> to ' . $rate . '%?';
			}
			else if($type == 'currency' && $credit_deposit_type == 'credit') {
				$description = 'Do you want to change <b>Currency Credit Rate</b> to ' . $rate . '%?';
			}
			else if($type == 'gold' && $credit_deposit_type == 'deposit') {
				$description = 'Do you want to change <b>Gold Deposit Rate</b> to ' . $rate . '%?';
			}
			else {
				$description = 'Do you want to change <b>Currency Deposit Rate</b> to ' . $rate . '%?';
			}
		}
		//Create new union
		else if($responsibility_id == 19) {
			$query = "SELECT union_name, abbreviation, color, user_id, user_name FROM create_new_union cnu, users
					  WHERE law_id = '$law_id' AND leader = user_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($union_name, $union_abbr, $union_color, $leader_id, $leader_name) = $row_desc;
			$description = 'Do you agree to create a new union with the name ' . $union_name . ' and abbreviation ' . $union_abbr . '?
							Color of the union will be <i id="new_union_color" style="background-color:' . $union_color . '">union color</i>
							New union leader will be <a href="user_profile?id=' . $leader_id . '" target="_blank">' . 
							$leader_name . '</a> and your country will become a member of this union.'; 
		}
		//Assign new Secretary of the Treasury.
		else if($responsibility_id == 20) {
			$query = "SELECT abm.user_id, user_name, salary, 
					  (SELECT currency_abbr FROM currency WHERE currency_id = (SELECT currency_id FROM country 
					  WHERE country_id = '$country_id'))
					  FROM assign_bank_manager abm, users u
					  WHERE law_id = '$law_id' AND u.user_id = abm.user_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($new_manager, $manager_name, $salary, $currency_abbr) = $row_desc;
			if($new_manager != 0) {
				$description = 'Do you want <a href="user_profile?id=' . $new_manager . '" target="_blank">' . $manager_name . 
							   '</a> to become new Secretary of the Treasury? Salary of the Secretary will be ' . $salary . ' ' .
							   $currency_abbr . '.' .
							   ' Secretary of the Treasury will be able to manage the Bank.';
			}
			else {
				$description = 'Do you want to fire current Secretary of the Treasury?';
			}
		}
		//Change production taxes
		else if($responsibility_id == 21) {
			$query = "SELECT product_name, tax FROM new_production_tax_law nptl, product_info pi
					  WHERE nptl.product_id = pi.product_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($product_name, $tax) = $row_desc;
			$description = 'Do you agree to set ' . number_format($tax, '2', '.', ' ') . '% production tax on ' . $product_name . '?';
		}
		//Budget allocation.
		else if($responsibility_id == 22) {
			$query = "SELECT currency_name, amount, name FROM budget_allocation ba, currency cu, government_positions gp
					  WHERE ba.currency_id = cu.currency_id AND law_id = '$law_id' AND gp.position_id = ba.position_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($currency_name, $amount, $position_name) = $row_desc;
			$description = 'Do you want to allocate ' . number_format($amount, '2', '.', ' ') . ' ' . $currency_name . ' 
						    for ' . $position_name . '?';
		}
		//Sign defence agreement.
		else if($responsibility_id == 24) {
			$query = "SELECT country_id, country_name, days FROM country c, sign_defence_agreement sda
					  WHERE c.country_id = with_country_id AND law_id = '$law_id'";
			$result_war = $conn->query($query);
			$row_war = $result_war->fetch_row();
			list($def_with_country_id, $def_with_country_name, $days) = $row_war;
			$description = 'Do you want to sign defence agreement with <a href="country?country_id=' . $def_with_country_id . '" 
							target="_blank">' . $def_with_country_name . '</a> for the period of 
						   ' . $days . ' days?'; 
		}
		//Product allocation.
		else if($responsibility_id == 25) {
			$query = "SELECT product_name, amount, name FROM product_allocation pa, product_info pi, government_positions gp
					  WHERE pa.product_id = pi.product_id AND law_id = '$law_id' AND gp.position_id = pa.position_id";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($product_name, $amount, $position_name) = $row_desc;
			$description = 'Do you want to allocate ' . number_format($amount, '2', '.', ' ') . ' ' . $product_name . ' 
						    for ' . $position_name . '?';
		}
		//Change timezone.
		else if($responsibility_id == 26) {
			$query = "SELECT utc FROM timezones WHERE timezone_id = 
					 (SELECT timezone_id FROM change_timezone WHERE law_id = '$law_id')";
			$result_timezones = $conn->query($query);
			$row_timezones = $result_timezones->fetch_row();
			list($utc) = $row_timezones;
			$description = 'Do you want to change timezone to ' . $utc .'?'; 
		}
		//Price change to build companies for citizens.
		else if($responsibility_id == 27) {
			$query = "SELECT name, price FROM building_policy_law bpl, building_info bi
					  WHERE bi.building_id = bpl.building_id AND law_id = '$law_id'";
			$result_desc = $conn->query($query);
			$row_desc = $result_desc->fetch_row();
			list($building_name, $price) = $row_desc;
			$description = 'Do you want to change cost build of the ' . $building_name . ' to ' . 
							number_format($price, '2', '.', ' ') . ' Gold?';
		}
		//Remove country from the union
		else if($responsibility_id == 29) {
			$query = "SELECT c.country_id, country_name FROM country c, remove_country_union rcu
					  WHERE c.country_id = rcu.country_id AND law_id = '$law_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($remove_country_id, $remove_country_name) = $row;
			$description = 'Do you want to expel <a href="country?country_id=' . $remove_country_id . 
						   '" target="_blank">' . $remove_country_name . '</a> from your union?'; 
		}
		//Welcome message
		else if($responsibility_id == 30) {
			$query = "SELECT heading, message FROM change_welcome_message WHERE law_id = '$law_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($heading, $message) = $row;
			$description = '<div>Change heading in the welcome message to:<br>' .
						   $heading . '<br><br>' .
						   'and message to:<br>' .
						   '<div class="welcome_msg">' . html_entity_decode($message, ENT_QUOTES) . '</div></div>'; 
		}
		//Assign titles
		else if($responsibility_id == 31) {
			$query = "SELECT u.user_id, user_name, title, action FROM users u, assign_titles at, titles t
					  WHERE u.user_id = at.user_id AND t.title_id = at.title_id AND law_id = '$law_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($user_id, $user_name, $title, $action) = $row;
			if($action == 'remove') {
				$description = 'Do you want to take away <b>\'' . $title . '\'</b> title from the <a href="user_profile?id=' . $user_id . 
							   '" target="_blank">' . $user_name . '</a>?'; 
			}
			else {
				$description = 'Do you want to assign <b>\'' . $title . '\'</b> title to the <a href="user_profile?id=' . $user_id . 
							   '" target="_blank">' . $user_name . '</a>?'; 
			}
		}
		//Change Capital Region
		else if($responsibility_id == 34) {
			$query = "SELECT region_name FROM regions
					  WHERE region_id = (SELECT region_id FROM change_capital_region WHERE law_id = '$law_id')";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($new_capital_region_name) = $row;
			$description = 'Do you want to change your country\'s capital to ' . $new_capital_region_name . '?'; 
		}
		//Change Region Owner
		else if($responsibility_id == 35) {
			$query = "SELECT region_name, rightful_owner, country_name, price, action FROM regions r, country c, change_region_owner cro
					  WHERE law_id = '$law_id' AND c.country_id = cro.country_id AND r.region_id = cro.region_id";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($region_name, $rightful_owner, $country_name, $price, $action) = $row;
			if($action == 1) {//propose
				$description = 'Do you want to transfer ownership rights of your region ' . $region_name . 
							   ' to ' . $country_name .  ' for ' . $price . ' Gold?'; 
			}
			else if ($action == 2) {//proccess
				if($country_id == $rightful_owner) {
					$description = 'Do you want to gain ownership rights of ' . $region_name . 
								   ' region from the ' . $country_name .  ' for ' . $price . ' Gold?'; 
				}
				else {
					$description = 'Do you want to gain ownership rights of ' . $region_name . 
								   ' region from the ' . $country_name .  ' for ' . $price . ' Gold?' .
								   ' Additional fee will be taken from the country treasury in the amount of 8 Gold.';
				}
			}
		}
		
		return $description;
	}
?>