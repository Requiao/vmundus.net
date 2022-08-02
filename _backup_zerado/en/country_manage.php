<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="page_head">Manage Country</p>
		<div id="page_menu">
			<p id="laws_in_progress">Proposed Laws</p>
			<p id="issue_laws">Issue laws</p>
			<p id="warehouse">Warehouse</p>
			<p id="country_currency">Currency</p>
		</div>
		
		<?php
			$is_governor = false;
			
			//check if president
			$query = "SELECT position_id, salary, elected, country_id FROM country_government WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$is_governor = true;
				$row = $result->fetch_row();
				list($position_id, $salary, $elected, $country_id) = $row;
			}
			else { //check if congressman
				$query = "SELECT salary, elected, country_id FROM congress_details WHERE country_id = 
						 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				if($result->num_rows == 1) { 
					$is_governor = true;
					$row = $result->fetch_row();
					list($salary, $elected, $country_id) = $row;
					$position_id = 3;
				}
			}

			//display country manage information
			if($is_governor) {
				/* display laws in progress */
				echo "\n\t\t" . '<div id="laws_in_progress_div">';
				
				echo "\n\t\t\t" . '<p id="quit_from_gov">Retire</p>';
				
				//display laws in progress that user has permissions to vote/sign
				$query = "SELECT cli.responsibility_id, responsibility, user_name, proposed_date, proposed_time, yes, no, user_id, law_id 
						  FROM political_responsibilities pr, country_law_info cli, users u, government_country_responsibilities gcr
						  WHERE pr.responsibility_id = cli.responsibility_id AND u.user_id = proposed_by
						  AND cli.country_id = '$country_id' AND gcr.country_id = cli.country_id AND gcr.responsibility_id = cli.responsibility_id
						  AND position_id = '$position_id' AND must_sign_vote = TRUE AND is_processed = 0
						  ORDER BY proposed_date DESC, proposed_time DESC";
				$result = $conn->query($query);
				include('../php_functions/law_description.php');
				while($row = $result->fetch_row()) {
					list($responsibility_id, $responsibility, $user_name, $proposed_date, $proposed_time, 
						 $yes, $no, $governor_id, $law_id) = $row;

					//correct date/time
					// + 24 hours to get expire time.
					$expire_date_time = date('Y-m-d H:i:s', strtotime($proposed_date . $proposed_time . ' + 24 hours'));
					
					
					$expire_time = date('H:i:s', strtotime($expire_date_time));
					$expire_date = date('Y-m-d', strtotime($expire_date_time));
					$expire_date = correctDate($expire_date, $expire_time);
					$expire_time = correctTime($expire_time);		

					//find out when expires
					$current_date = correctDate(date('Y-m-d'), date('H:i:s'));
					$current_time = correctTime(date('H:i:s'));
					
					$date1 = date_create($current_date . ' ' . $current_time);
					$date2 = date_create($expire_date . ' ' . $expire_time);
					$diff = date_diff($date1,$date2);
					$expires_in = $diff->format("%H:%I:%S");
					
					//to display proposed date/time
					$proposed_date = correctDate($proposed_date, $proposed_time);
					$proposed_time = correctTime($proposed_time);
					
					if(date('Y-m-d H:i:s', strtotime($current_date . ' ' . $current_time)) > 
					   date('Y-m-d H:i:s', strtotime($expire_date . ' ' . $expire_time))) {//if expired, then don't show
						continue;
					}
					
					//get description
					$description = lawDescription($responsibility_id, $law_id);
					
					//check if already voted
					$query = "SELECT * FROM users_voted_for_laws WHERE user_id = '$user_id' AND law_id = '$law_id'";
					$result_check = $conn->query($query);
					if($result_check->num_rows == 1) {
						$pli_no = "pli_no_vote pli_no_yes_voted";
						$pli_yes = "pli_yes_vote pli_no_yes_voted";
						$pli_times = "pli_times_vote pli_times_check_voted";
						$pli_check = "pli_check_vote pli_times_check_voted";
						$pli_no_votes = "pli_no_votes_vote pli_no_yes_votes_voted";
						$pli_yes_votes = "pli_yes_votes_vote pli_no_yes_votes_voted";						
					}
					else {
						$pli_no = "pli_no_vote vote";
						$pli_yes = "pli_yes_vote vote";
						$pli_times = "pli_times_vote";
						$pli_check = "pli_check_vote";
						$pli_no_votes = "pli_no_votes_vote";
						$pli_yes_votes = "pli_yes_votes_vote";
					}
					
					echo "\n\t\t" . '<div class="info_blocks" id="' . $law_id . '">' .
						 "\n\t\t\t" . '<p class="heads">' . $responsibility . '</p>' .
						 "\n\t\t\t" . '<div class="pli_description">Description: ' . $description . '</div>' .
						 "\n\t\t\t" . '<p class="pli_proposed_by">Proposed by: ' . 
						 "\n\t\t\t\t" . '<a href="user_profile?id=' . $governor_id . '" target="_blank">' . $user_name . '</a>' .
						 "\n\t\t\t" . '</p>' .
						 "\n\t\t\t" . '<p class="pli_proposed_on">Proposed on: ' . $proposed_date . ' ' . $proposed_time . '</p>' .
						 "\n\t\t\t" . '<p class="pli_expires_in">' . $expires_in . '</p>' .
						 "\n\t\t\t" . '<div class="' . $pli_yes . '" id="1">' .
						 "\n\t\t\t\t" . '<p class="' . $pli_check . '"><span class="fa fa-check" aria-hidden="true"></span></p>' .
						 "\n\t\t\t\t" . '<p class="' . $pli_yes_votes . '">' . $yes . '</p>' .
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t\t" . '<div class="' . $pli_no . '" id="0">' .
						 "\n\t\t\t\t" . '<p class="' . $pli_times . '"><span class="fa fa-times"></span></p>' .
						 "\n\t\t\t\t" . '<p class="' . $pli_no_votes . '">' . $no . '</p>' .
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t" . '</div>';
				}
				
				echo "\n\t\t" . '</div>';
				
				/* issue new laws */
				echo "\n\t\t" . '<div id="issue_laws_div">';
				$query = "SELECT gcr.responsibility_id, responsibility 
						  FROM government_country_responsibilities gcr, political_responsibilities pr 
						  WHERE country_id = '$country_id' AND position_id = '$position_id' 
						  AND pr.responsibility_id = gcr.responsibility_id
						  AND have_access = TRUE AND responsibility != 'n/a'
						  ORDER BY responsibility_id";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($responsibility_id, $responsibility_name) = $row;
					
					//change gov term length
					if($responsibility_id == 1) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="change_gov_term_length_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
							 
							 
							 
						//select positions
						echo "\n\t\t\t\t" . '<select id="new_term_governor_id">';	
						$query = "SELECT position_id, name FROM government_positions";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($gov_position_id, $position_name) = $row_positions;
							echo "\n\t\t\t\t\t" . '<option value="' . $gov_position_id . '">' . $position_name . '</option>';
						}
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<input id="new_term_length" type="text" placeholder="days" maxlength="2">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="1">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Join/leave alliance
					else if($responsibility_id == 2) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="join_leave_union_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
				
						//leave union
						$query = "SELECT union_name FROM unions WHERE union_id = 
								 (SELECT union_id FROM country_unions WHERE country_id = '$country_id')
								 ORDER BY union_name";
						$result_unions = $conn->query($query);
						if($result_unions->num_rows == 1) {
							$row_unions = $result_unions->fetch_row();
							list($union_name) = $row_unions;
							echo "\n\t\t\t\t" . '<p id="ul_head">Leave Union</p>' .
								 "\n\t\t\t\t" . '<p id="union_name">' . $union_name . '</p>';
						}
						else {	
							echo "\n\t\t\t\t" . '<p id="uj_head">Join Union</p>' .
								 "\n\t\t\t\t" . '<select id="unions_to_join">';
							//join union
							$query = "SELECT union_name, union_id FROM unions WHERE union_id";
							$result_unions = $conn->query($query);
							while($row_unions = $result_unions->fetch_row()) {
								list($union_name, $union_id) = $row_unions;
								echo "\n\t\t\t\t\t" . '<option value="' . $union_id . '">' . $union_name . '</option>';
							}
							echo "\n\t\t\t\t" . '</select>' .
								 "\n\t\t\t\t" . '<p id="jaf_head">Join as founder?</p>' .
								 "\n\t\t\t\t" . '<div id="as_founder">' .
								 "\n\t\t\t\t" . '<label><input type="radio" name="as_founder" value="1"  checked> Yes</label>' .
								 "\n\t\t\t\t" . '<label><input type="radio" name="as_founder" value="0"> No</label>' .
								 "\n\t\t\t\t" . '</div>';
						}
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="2">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Print money
					else if($responsibility_id == 3) {
						$query = "SELECT product_name, product_icon, amount, product_amount, 
								 (SELECT currency_abbr FROM currency WHERE currency_id = 
								 (SELECT currency_id FROM country WHERE country_id = '$country_id')) FROM product_info pi, print_money_cost pmc 
								  WHERE pi.product_id = pmc.product_id";
						$result_cost = $conn->query($query);
						$row_cost = $result_cost->fetch_row();
						list($product_name, $product_icon, $amount, $product_amount, $currency_abbr) = $row_cost;
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="print_money_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p id="print_money_cost_head">Product consumption per ' . $amount . ' currency.</p>' .
							 "\n\t\t\t\t" . '<div id="product_for_money_print" class="icon_amount">' .
							 "\n\t\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
											$product_icon. '" alt="' . $product_name . '"></abbr>' .
							 "\n\t\t\t\t\t" . '<p class="amount">-' . $product_amount . '</p>' .
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t" . '<input id="print_amount" type="text" maxlength="8" placeholder="amount">' .
							 "\n\t\t\t\t\t" . '<p id="print_currency_abbr">' . $currency_abbr . '</p>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="3">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Impeach president.
					else if($responsibility_id == 4) {
						$query = "SELECT user_name, user_id FROM users WHERE user_id = 
								 (SELECT user_id FROM country_government WHERE country_id = '$country_id' AND position_id = 1)";
						$result_cost = $conn->query($query);
						$row_cost = $result_cost->fetch_row();
						list($president_name, $president_id) = $row_cost;
						
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="print_money_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '*</p>' .
							 "\n\t\t\t\t" . '<p class="law_note">*must get 65% votes or more</p>' .
							 "\n\t\t\t\t" . '<p id="impeach_president_head">Impeach president  <a href="user_profile?id=' . 
											 $president_id . '" target="_blank">' . $president_name . '</a>.</p>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="4">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Dissolve Congress.
					else if($responsibility_id == 5) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="print_money_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '*</p>' .
							 "\n\t\t\t\t" . '<p class="law_note">*must get 65% votes or more</p>' .
							 "\n\t\t\t\t" . '<p id="dissolve_congress_head">Dissolve congress.</p>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="5">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Import permission/embargo.
					else if($responsibility_id == 6) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="button blue" id="disp_imprt_perm_info">Permission</p>' .
							 "\n\t\t\t\t" . '<p class="button red" id="disp_emp_embargo_info">Embargo</p>';

						//permission
						echo "\n\t\t\t\t" . '<div id="disp_imprt_perm_div">';
						
						//select products
						echo "\n\t\t\t\t\t" . '<select id="product_list_for_import_perm">';	
						$query = "SELECT product_name, product_id FROM product_info ORDER BY product_name";
						$result_product = $conn->query($query);
						while($row_product = $result_product->fetch_row()) {
							list($product_name, $product_id) = $row_product;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $product_id . '">' . $product_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
						
						echo "\n\t\t\t\t\t" . '<input id="imprt_perm_days" type="text" maxlength="2" placeholder="days">';
						echo "\n\t\t\t\t\t" . '<input id="imprt_perm_tax" type="text" maxlength="5" placeholder="tax">';
						
						//select countries
						echo "\n\t\t\t\t\t" . '<select id="country_list_for_import_perm">';	
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id' ORDER BY country_name";
						$result_country = $conn->query($query);
						while($row_country = $result_country->fetch_row()) {
							list($for_country_id, $for_country_name) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $for_country_id . '">' . $for_country_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '</div>';
						
						//embargo
						$query = "SELECT from_country_id, country_name, pit.product_id, product_name, sale_tax 
								  FROM country c, product_import_tax pit, product_info pi
								  WHERE c.country_id = pit.from_country_id AND permission = true AND pit.country_id = '$country_id'
								  AND pi.product_id = pit.product_id
								  ORDER BY country_name, product_name";
						$result_country = $conn->query($query);
						echo "\n\t\t\t\t\t" . '<select id="trade_info_agreement_list">';	
						while($row_country = $result_country->fetch_row()) {
							list($from_country_id, $from_country_name, $product_id, $product_name, $sale_tax) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $from_country_id . '_' . $product_id . '">' 
												  . $from_country_name . ' => ' . $product_name . ' => ' . $sale_tax . '%</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
	
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="6">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change responsibilities.
					else if($responsibility_id == 8) {
						//display responsibilities info
						$query = "SELECT name, responsibility, must_sign_vote, have_access
								  FROM government_positions gp, government_country_responsibilities gcr, political_responsibilities pr
								  WHERE gp.position_id = gcr.position_id AND pr.responsibility_id = gcr.responsibility_id 
								  AND country_id = '$country_id' AND (have_access = TRUE OR must_sign_vote = TRUE)
								  AND responsibility != 'n/a'
								  ORDER BY gcr.position_id, gcr.responsibility_id";
						$result_resp = $conn->query($query);
						echo "\n\t\t\t\t" . '<div id="responsibilities_info">' .
							 "\n\t\t\t\t" . '<div id="responsibilities_info_div">' .
							 "\n\t\t\t\t\t" . '<div id="responsibilities_info_heads">' .
							 "\n\t\t\t\t\t\t" . '<p id="rih_gov">Governor</p>' .
							 "\n\t\t\t\t\t\t" . '<p id="rih_resp">Responsibility</p>' .
							 "\n\t\t\t\t\t\t" . '<p id="rih_iss">Issue</p>' .
							 "\n\t\t\t\t\t\t" . '<p id="rih_vot">Vote</p>' .
							 "\n\t\t\t\t\t\t" . '<span id="rid_close" class="glyphicon glyphicon-remove-circle"></span>' .
							 "\n\t\t\t\t\t" . '</div>';
						while($row_resp = $result_resp->fetch_row()) {
							list($governor_name, $responsibility, $must_sign_vote, $have_access) = $row_resp;
							if($must_sign_vote == FALSE) {
								$can_sign = "--";
							}
							else if($must_sign_vote == TRUE) {
								$can_sign = "Yes";
							}
							if($have_access == TRUE) {
								$can_issue = "Yes";
							}
							else if($have_access == FALSE) {
								$can_issue = "--";
							}
							echo "\n\t\t\t\t\t" . '<div class="responsibilities_info_divs">' .
								 "\n\t\t\t\t\t\t" . '<p class="rid_gov_position">' . $governor_name . '</p>' .
								 "\n\t\t\t\t\t\t" . '<p class="rid_gov_responsibility">' . $responsibility . '</p>' .
								 "\n\t\t\t\t\t\t" . '<p class="rid_gov_issue">' . $can_issue  . '</p>' .
								 "\n\t\t\t\t\t\t" . '<p class="rid_gov_sign">' . $can_sign  . '</p>' .
								 "\n\t\t\t\t\t" . '</div>';
						}
						echo "\n\t\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t" . '</div>';
						
						
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="button blue" id="disp_resp_info">Details</p>';

						//select positions
						echo "\n\t\t\t\t\t" . '<select id="government_positions_list">';	
						$query = "SELECT position_id, name FROM government_positions";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($gov_position_id, $position_name) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $gov_position_id . '">' . $position_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';

						//select responsibilities
						echo "\n\t\t\t\t\t" . '<select id="responsibility_list">';	
						$query = "SELECT responsibility_id, responsibility FROM political_responsibilities";
						$result_country = $conn->query($query);
						while($row_country = $result_country->fetch_row()) {
							list($responsibility_id, $responsibility) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $responsibility_id . '">' . $responsibility . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
	
						echo "\n\t\t\t\t" . '<div id="cr_action">' .
							 "\n\t\t\t\t" . '<label><input type="radio" name="cr_action" value="1"  checked> Add</label>' .
							 "\n\t\t\t\t" . '<label><input type="radio" name="cr_action" value="0"> Remove</label>' .
							 "\n\t\t\t\t" . '</div>';
							 
						echo "\n\t\t\t\t" . '<div id="cr_vote">' .
							 "\n\t\t\t\t" . '<label><input type="checkbox" name="cr_vote" value="1">Vote</label>' .
							 "\n\t\t\t\t" . '<label><input type="checkbox" name="cr_issue" value="1">Issue</label>' .
							 "\n\t\t\t\t" . '</div>';	 
	
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="8">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Declare war.
					else if($responsibility_id == 9) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="war_to_country_id">';
						
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id' ORDER BY country_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($war_to_country_id, $war_to_country_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $war_to_country_id . '">' . $war_to_country_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="9">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Sign peace treaty.
					else if($responsibility_id == 10) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="peace_with_country_id">';
						
						$query = "SELECT country_id, country_name FROM country WHERE country_id IN 
								 (SELECT with_country_id FROM country_wars WHERE country_id = '$country_id'
								  AND active = TRUE)
								  UNION
								  SELECT country_id, country_name FROM country WHERE country_id IN
								 (SELECT country_id FROM country_wars WHERE with_country_id = '$country_id'
								  AND active = TRUE) ORDER BY country_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($peace_with_country_id, $peace_with_country_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $peace_with_country_id . '">' . $peace_with_country_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="10">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Assign ministers.
					else if($responsibility_id == 11) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
						
						//select positions
						echo "\n\t\t\t\t\t" . '<select id="ministers_list_id">';	
						$query = "SELECT position_id, name FROM government_positions WHERE position_id > 3";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($gov_position_id, $position_name) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $gov_position_id . '">' . $position_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t\t" . '<input id="new_minister_input" type="text" maxlength="7" placeholder="ID">' .
							 "\n\t\t\t\t" . '<p class="law_note">*input 0 to fire current minister without replacing.</p>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="11">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Assign Prime Minister.
					else if($responsibility_id == 12) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
			
						echo "\n\t\t\t\t\t" . '<input id="new_prime_minister_input" type="text" maxlength="7" placeholder="ID">' .
							 "\n\t\t\t\t" . '<p class="law_note">*input 0 to fire current minister without replacing.</p>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="12">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change taxes.
					else if($responsibility_id == 13) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="button blue" id="disp_product_info_tax">Product</p>' .
							 "\n\t\t\t\t" . '<p class="button blue" id="disp_income_info_tax">Income</p>';

						//product tax
						echo "\n\t\t\t\t" . '<div id="disp_product_tax_div">';
						//select products
						echo "\n\t\t\t\t\t" . '<select id="product_list_for_tax">';	
						$query = "SELECT product_name, product_id FROM product_info ORDER BY product_name";
						$result_product = $conn->query($query);
						while($row_product = $result_product->fetch_row()) {
							list($product_name, $product_id) = $row_product;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $product_id . '">' . $product_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
						
						echo "\n\t\t\t\t\t" . '<input id="new_product_tax" type="text" maxlength="5" placeholder="tax">' .
							 "\n\t\t\t\t" . '</div>';
						
						//income(work) tax
						echo "\n\t\t\t\t" . '<div id="disp_income_tax_div">' .
							 "\n\t\t\t\t\t" . '<p id="income_tax_head">Income (work) tax</p>' .
							 "\n\t\t\t\t\t" . '<input id="new_income_tax" type="text" maxlength="5" placeholder="tax">' .
							 "\n\t\t\t\t" . '</div>';
							 
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="13">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Travel agreement.
					else if($responsibility_id == 14) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="button blue" id="disp_allow_travel_info">Allow</p>' .
							 "\n\t\t\t\t" . '<p class="button red" id="disp_ban_travel_info">Ban</p>';

						//countries with no travel agreements
						echo "\n\t\t\t\t" . '<div id="allow_travel_info_div">';
						//select countries
						echo "\n\t\t\t\t\t" . '<select id="allow_country_travel">';	
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id' ORDER BY country_name";
						$result_country = $conn->query($query);
						while($row_country = $result_country->fetch_row()) {
							list($for_country_id, $for_country_name) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $for_country_id . '">' . $for_country_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
						
						echo "\n\t\t\t\t\t" . '<input id="travel_days_input" type="text" maxlength="2" placeholder="days">' .
							 "\n\t\t\t\t" . '</div>'; 
						
						//countries with travel agreements
						//select products
						echo "\n\t\t\t\t\t" . '<select id="ban_country_travel">';	
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id' 
								  AND country_id IN (SELECT from_country_id FROM travel_agreement WHERE country_id = '$country_id'
								  AND permission = 1) ORDER BY country_name;";
						$result_country = $conn->query($query);
						while($row_country = $result_country->fetch_row()) {
							list($for_country_id, $for_country_name) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $for_country_id . '">' . $for_country_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
							 
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="14">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Give/Ban permission for foreigners to build companies.
					else if($responsibility_id == 15) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="button blue" id="disp_build_perm_info">Permission</p>' .
							 "\n\t\t\t\t" . '<p class="button red" id="disp_ban_build_info">Ban</p>';

						//permission
						echo "\n\t\t\t\t" . '<div id="disp_build_perm_div">';
						
						//select buildings
						echo "\n\t\t\t\t\t" . '<select id="building_list_for_build_perm">';	
						$query = "SELECT name, building_id FROM building_info WHERE product_id IS NOT NULL AND
								  is_active = TRUE ORDER BY name";
						$result_building = $conn->query($query);
						while($row_building = $result_building->fetch_row()) {
							list($building_name, $building_id) = $row_building;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $building_id . '">' . $building_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
						
						echo "\n\t\t\t\t\t" . '<input id="build_perm_price" type="text" maxlength="11" placeholder="price">';
						
						//select countries
						echo "\n\t\t\t\t\t" . '<select id="country_list_for_build_perm">';	
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id' ORDER BY country_name";
						$result_country = $conn->query($query);
						while($row_country = $result_country->fetch_row()) {
							list($for_country_id, $for_country_name) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $for_country_id . '">' . $for_country_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '</div>';

						//ban
						$query = "SELECT foreign_country, country_name, fbp.building_id, name, price 
								  FROM country c, foreign_building_policy fbp, building_info bi
								  WHERE c.country_id = fbp.foreign_country AND foreigners = TRUE AND fbp.country_id = '$country_id'
								  AND bi.building_id = fbp.building_id AND bi.is_active = TRUE
								  ORDER BY country_name, name";
						$result_country = $conn->query($query);
						echo "\n\t\t\t\t\t" . '<select id="build_info_perm_list">';	
						while($row_country = $result_country->fetch_row()) {
							list($from_country_id, $from_country_name, $building_id, $building_name, $price) = $row_country;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $from_country_id . '_' . $building_id . '">' 
												  . $from_country_name . ' => ' . $building_name . ' => ' . $price . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';
	
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="15">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Assign new union leader
					else if($responsibility_id == 16) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="new_union_leader_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '*</p>' .
							 "\n\t\t\t\t" . '<p class="law_note">*75% of founder countries must approve</p>' .
							 "\n\t\t\t\t" . '<p id="new_union_id">New union leader</p>' .
							 "\n\t\t\t\t" . '<input id="new_union_id_input" type="text" placeholder="ID" maxlength="7">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="16">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change government salaries.
					else if($responsibility_id == 17) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
						
						//select positions
						echo "\n\t\t\t\t\t" . '<select id="ministers_salary_list_id">';	
						$query = "SELECT position_id, name FROM government_positions";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($gov_position_id, $position_name) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $gov_position_id . '">' . $position_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t\t" . '<input id="minister_salary_input" type="text" maxlength="8" placeholder="salary">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="17">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change credit/deposit rate.
					else if($responsibility_id == 18) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<input id="rate_input" type="text" maxlength="3" placeholder="rate">' .
							 "\n\t\t\t\t" . '<div id="credit_deposit_type_div">' .
							 "\n\t\t\t\t\t" . '<label><input type="radio" name="credit_deposit_type" value="credit"> Credit</label>' .
							 "\n\t\t\t\t\t" . '<label><input type="radio" name="credit_deposit_type" value="deposit"> Deposit</label>' .
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t" . '<div id="rate_type_div">' .
							 "\n\t\t\t\t\t" . '<label><input type="radio" name="rate_type" value="currency"> Currency</label>' .
							 "\n\t\t\t\t\t" . '<label><input type="radio" name="rate_type" value="gold"> Gold</label>' .
							 "\n\t\t\t\t" . '</div>';
						
						
					
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="18">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Create new union.
					else if($responsibility_id == 19) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs" id="create_new_union_div">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p id="new_union_name">Union name: </p>' .
							 "\n\t\t\t\t" . '<input id="new_union_name_input" type="text" maxlength="20">' .
							 "\n\t\t\t\t" . '<p id="union_abbr">Union abbreviation: </p>' .
							 "\n\t\t\t\t" . '<input id="union_abbr_input" type="text" maxlength="5">' .
							 "\n\t\t\t\t" . '<p id="union_color">Union color: </p>' .
							 "\n\t\t\t\t" . '<input type="color" id="union_color_input" value="#466888">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="19">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Allocate currency/gold for the Bank.
					else if($responsibility_id == 20) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="law_note">*input 0 to fire current manager without replacing.</p>' .
							 "\n\t\t\t\t" . '<input id="new_bank_manager_input" type="text" maxlength="7" placeholder="user id">' .
							 "\n\t\t\t\t" . '<input id="bank_manager_salary_input" type="text" maxlength="7" placeholder="salary">';
							 
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="20">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change production taxes
					else if($responsibility_id == 21) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
						//select products
						echo "\n\t\t\t\t" . '<select id="products_production_tax">';	
						$query = "SELECT product_name, product_id FROM product_info ORDER BY product_name";
						$result_product = $conn->query($query);
						while($row_product = $result_product->fetch_row()) {
							list($product_name, $product_id) = $row_product;
							echo "\n\t\t\t\t\t" . '<option value="' . $product_id . '">' . $product_name . '</option>';
						}
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<input id="new_production_tax" type="text" maxlength="5" placeholder="tax">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="21">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Budget Allocation.
					else if($responsibility_id == 22) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
						
						//select positions
						echo "\n\t\t\t\t\t" . '<select id="ministry_budget_list_id">';	
						$query = "SELECT position_id, name FROM government_positions WHERE position_id != 3";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($gov_position_id, $position_name) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $gov_position_id . '">' . $position_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';	
						
						//select currency
						echo "\n\t\t\t\t\t" . '<select id="ministry_currency_list_id">';	
						$query = "SELECT cc.currency_id, currency_name, amount FROM country_currency cc, currency cu 
								  WHERE cc.currency_id = cu.currency_id AND country_id = '$country_id'";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($currency_id, $currency_name, $amount) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $currency_id . '">' . $currency_name . ' (' . 
								 number_format($amount, '2', '.', ' ') . ')</option>';
						}
						
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t\t" . '<input id="ministry_budget_input" type="text" maxlength="10" placeholder="amount">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="22">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Sign defence agreement.
					else if($responsibility_id == 24) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="def_with_country_id">';
						
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id'
								  AND country_id NOT IN (SELECT with_country_id FROM country_wars WHERE country_id = '$country_id'
								  AND active = TRUE) AND country_id NOT IN (SELECT with_country_id FROM defence_agreements 
								  WHERE country_id = '$country_id' AND is_allies = TRUE) 
								  ORDER BY country_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($def_with_country_id, $def_with_country_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $def_with_country_id . '">' . $def_with_country_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t\t" . '<input id="def_days_input" type="text" maxlength="2" placeholder="days">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="24">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Product Allocation
					else if($responsibility_id == 25) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>';
						
						//select positions
						echo "\n\t\t\t\t\t" . '<select id="ministry_product_list_id">';	
						$query = "SELECT position_id, name FROM government_positions WHERE position_id != 3";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($gov_position_id, $position_name) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $gov_position_id . '">' . $position_name . '</option>';
						}
						echo "\n\t\t\t\t\t" . '</select>';	
						
						//select products
						echo "\n\t\t\t\t\t" . '<select id="country_product_list_id">';	
						$query = "SELECT cp.product_id, product_name, amount FROM country_product cp, product_info pi 
								  WHERE cp.product_id = pi.product_id AND country_id = '$country_id' ORDER BY product_name";
						$result_positions = $conn->query($query);
						while($row_positions = $result_positions->fetch_row()) {
							list($product_id, $product_name, $amount) = $row_positions;
							echo "\n\t\t\t\t\t\t" . '<option value="' . $product_id . '">' . $product_name . ' (' . 
								 number_format($amount, '3', '.', ' ') . ')</option>';
						}
						
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t\t" . '<input id="product_quantity_input" type="text" maxlength="9" placeholder="quantity">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="25">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change timezone.
					else if($responsibility_id == 26) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="timezones_id">';
						
						$query = "SELECT timezone_id, utc FROM timezones";
						$result_timezones = $conn->query($query);
						while($row_timezones = $result_timezones->fetch_row()) {
							list($timezone_id, $utc) = $row_timezones;
							echo "\n\t\t\t\t\t" . '<option value="' . $timezone_id . '">' . $utc . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="26">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Price change to build companies for citizens.
					else if($responsibility_id == 27) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>'; 
							 
						//select buildings
						echo "\n\t\t\t\t" . '<select id="building_list_for_citiz_price">';	
						$query = "SELECT name, building_id FROM building_info WHERE product_id IS NOT NULL 
								  AND is_active = TRUE ORDER BY name";
						$result_building = $conn->query($query);
						while($row_building = $result_building->fetch_row()) {
							list($building_name, $building_id) = $row_building;
							echo "\n\t\t\t\t\t" . '<option value="' . $building_id . '">' . $building_name . '</option>';
						}
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<input id="build_citiz_price" type="text" maxlength="11" placeholder="price" name="q">' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="27">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Remove country from the union
					else if($responsibility_id == 29) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<p class="law_note">*65% of founder countries must approve</p>' .
							 "\n\t\t\t\t" . '<select id="remove_union_country_id">';
						
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id' 
								  AND country_id IN (SELECT country_id FROM country_unions WHERE union_id =
								 (SELECT union_id FROM country_unions WHERE country_id ='$country_id')) ORDER BY country_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($union_country_id, $union_country_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $union_country_id . '">' . $union_country_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="29">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Welcome message
					else if($responsibility_id == 30) {
						$query = "SELECT heading, message FROM welcome_message WHERE country_id = '$country_id'";
						$result_msg = $conn->query($query);
						if($result_msg->num_rows != 1) {
							$heading = 'type heading here';
							$message = 'type message here';
						}
						else {
							$row_msg = $result_msg->fetch_row();
							list($heading, $message) = $row_msg;
						}
						
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<input id="message_heading_input" type="text" maxlength="30" value="' . $heading . '">' .
							 "\n\t\t\t\t" . '<textarea id="message_input">' . $message .
											'</textarea>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="30">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Assign titles
					else if($responsibility_id == 31) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="assign_titles_id">';
							 
						$query = "SELECT title_id, title FROM titles ORDER BY title_id";
						$result_titles = $conn->query($query);
						while($row_titles = $result_titles->fetch_row()) {
							list($title_id, $title) = $row_titles;
							echo "\n\t\t\t\t\t" . '<option value="' . $title_id . '">' . $title . '</option>';
						}
						
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<input id="title_user_id" type="text" maxlength="7" placeholder="user id">' .
							 "\n\t\t\t\t" . '<div id="add_remove_title_div">' .
							 "\n\t\t\t\t\t" . '<label><input type="radio" name="add_remove_title" value="add"> Add</label>' .
							 "\n\t\t\t\t\t" . '<label><input type="radio" name="add_remove_title" value="remove"> Remove</label>' .
							 "\n\t\t\t\t" . '</div>';
						
						
					
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="31">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change Capital Region
					else if($responsibility_id == 34) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="new_capital_region_id">';
						
						$query = "SELECT region_id, region_name FROM regions WHERE country_id = '$country_id'
								  ORDER BY region_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($new_capital_region_id, $new_capital_region_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $new_capital_region_id . '">' . $new_capital_region_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<p class="button green issue_law" id="34">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
					//Change Region Owner
					else if($responsibility_id == 35) {
						echo "\n\t\t\t" . '<div class="issue_laws_div_divs">' . 
							 "\n\t\t\t\t" . '<p class="heads">' . $responsibility_name . '</p>' .
							 "\n\t\t\t\t" . '<select id="owned_region_id">';
						
						//regions
						$query = "SELECT region_id, region_name FROM regions WHERE country_id = '$country_id'
								  ORDER BY region_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($owned_region_id, $owned_region_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $owned_region_id . '">' . $owned_region_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>';
						
						//new owner
						echo "\n\t\t\t\t" . '<p id="new_region_owner_lbl">New Owner</p>' .
							 "\n\t\t\t\t" . '<select id="new_owner_country_id">';
						$query = "SELECT country_id, country_name FROM country WHERE country_id != '$country_id'
								  ORDER BY country_name";
						$result_countries = $conn->query($query);
						while($row_countries = $result_countries->fetch_row()) {
							list($new_owner_country_id, $new_owner_country_name) = $row_countries;
							echo "\n\t\t\t\t\t" . '<option value="' . $new_owner_country_id . '">' . $new_owner_country_name . '</option>';
						}	 
						echo "\n\t\t\t\t" . '</select>' .
							 "\n\t\t\t\t" . '<p id="price_for_new_region_owner_lbl">Price in Gold</p>' .
							 "\n\t\t\t\t" . '<input id="price_for_new_region_owner" type="text" maxlength="7">';
						
						echo "\n\t\t\t\t" . '<p class="button green issue_law" id="35">Issue</p>' .
							 "\n\t\t\t" . '</div>';
					}
				}
				echo "\n\t\t" . '</div>';
				/* warehouse */
				echo "\n\t\t" . '<div id="warehouse_div">'; 
				$query = "SELECT mp.product_id, product_icon, amount, product_name FROM product_info pi, ministry_product mp 
						  WHERE pi.product_id = mp.product_id AND country_id = '$country_id' AND position_id = '$position_id'";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($product_id, $product_icon, $amount, $product_name) = $row;
					echo "\n\t\t\t" . '<div class="icon_amount">' .
						 "\n\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
										 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t\t" . '<p class="amount">' . number_format($amount, '2', '.', ' ') . '</p>' .
						 "\n\t\t\t\t" . '<p class="sell">Sell</p>' .
						 "\n\t\t\t\t" . '<p id="pi_' . $product_id . '" hidden>' . $product_id . '</p>' .
						 "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';

				/* currency manage */
				echo "\n\t\t" . '<div id="country_currency_div">' .
					 "\n\t\t\t" . '<p id="make_offer_btn">Make offer</p>';
				?>
				
				<div id="make_offer_div">
				<p id="offer_currency_h">Offer:</p>
				<div id="offering_item"><img class="gold_img" src="../img/gold.png"></div>
				<input id="offering_amount" type="text" maxlength="7" placeholder="amount">
				<i class="fa fa-exchange" id="switch_offering_items"></i>
				<p id="offer_for_currency_h">For:</p>
				<?php
					echo "\n\t\t" . '<div id="offer_for_item">' .
						 "\n\t\t\t" . '<div id="offer_currency_list">' .
						 "\n\t\t\t\t" . '<div id="offer_selected_currency">' . 
						 "\n\t\t\t\t\t" . '<p>Select Currency</p>' . 
						 "\n\t\t\t\t" . '</div>' .  
						 "\n\t\t\t\t" . '<p id="offer_currency_id" hidden>0</p>' .  
						 "\n\t\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t\t" . '<div id="offer_currency_div">';
					
					$query = "SELECT currency_name, currency_abbr, flag, cu.currency_id, IFNULL(amount, 0) 
							  FROM country c, currency cu LEFT JOIN ministry_budget mb 
							  ON cu.currency_id = mb.currency_id AND country_id = '$country_id' 
							  AND position_id = '$position_id' WHERE
							  cu.flag_id = c.country_id ORDER BY currency_name";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($currency_name, $currency_abbr, $flag, $currency_id, $amount) = $row;
						echo "\n\t\t\t\t" . '<div class="offer_currency" id="' . $currency_id . '">' . 
							 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
							 "\n\t\t\t\t\t" . '<p>' . $currency_abbr . ' (' . number_format($amount, 2, '.', ' ') . ')</p>' . 
							 "\n\t\t\t\t" . '</div>';
					}
					echo "\n\t\t\t" . '</div>' .
						 "\n\t\t" . '</div>';
				?>
					<input id="offering_rate" type="text" maxlength="7" placeholder="rate">
					<p id="place_offer_btn">Offer</p>
				</div>
				
				<?php
				$query = "SELECT amount, currency_abbr, flag FROM ministry_budget mb, currency cu, country c
						  WHERE mb.country_id = '$country_id' AND position_id = '$position_id' 
						  AND c.currency_id = cu.currency_id
						  AND cu.currency_id = mb.currency_id AND amount > 0 ORDER BY amount DESC";
				$result = $conn->query($query);

				while($row = $result->fetch_row()) {
					list($amount, $currency_abbr, $flag) = $row;
					echo "\n\t\t\t" . '<div class="ucd_info">' .
						 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '" alt="' . $currency_abbr . '">' .
						 "\n\t\t\t\t" . '<p>' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t" . '</div>';
				}

				
				echo "\n\t\t\t" . '<div id="user_offers">';
				$query = "SELECT u.user_id, user_name, user_image, cu.currency_abbr,
						  rate, amount, offer_id, action
						  FROM monetary_market mm, users u, currency cu, user_profile up,
						  country_government cg, government_positions gp
						  WHERE cu.currency_id = mm.currency_id AND cg.user_id = up.user_id 
						  AND cg.position_id = seller_position_id AND cg.country_id = seller_country_id
						  AND u.user_id = up.user_id AND seller_country_id = '$country_id' AND seller_position_id = '$position_id'
						  AND gp.position_id = seller_position_id
						  ORDER BY rate ASC";
				$result = $conn->query($query);
				echo "\n\t\t" . '<p id="user_offers_head">My Offers:</p>' . 
					 "\n\t\t" . '<p id="amount_head">Amount</p>' . 
					 "\n\t\t" . '<p id="price_head">Rate</p>';
				while($row = $result->fetch_row()) {
					list($seller_id, $seller_name, $seller_img, $currency_abbr, $rate,
						 $amount, $offer_id, $action) = $row;
					
					echo "\n\t\t" . '<div class="offer _' . $offer_id . '">' .
						 "\n\t\t\t" . '<p class="offer_id" hidden>' . $offer_id . '</p>' .
						 "\n\t\t\t" . '<a href="user_profile?id=' . $seller_id . '" class="user_name">' .
						 "\n\t\t\t" . '<p>' . $seller_name . '</p></a>' . 
						 "\n\t\t\t" . '<img class="user_image" src="../user_images/' . $seller_img . '" alt="user image" target="_new">';
					
					if($action == 'sell') {
						echo "\n\t\t\t" . '<p class="amount_selling">' . number_format($amount, 2, '.', ' ') . ' ' . $currency_abbr . '</p>' .
							 "\n\t\t\t" . '<p class="rate">1 ' . $currency_abbr . ' = ' . number_format($rate, 3, '.', ' ') . '</p>' .
							 "\n\t\t\t" . '<img class="gold_img" src="../img/gold.png">';
					}
					else if ($action == 'buy') {
						echo "\n\t\t\t" . '<p class="amount_selling">' . number_format($amount, 2, '.', ' ') . ' Gold</p>' .
							 "\n\t\t\t" . '<p class="gold_rate">1 </p>' .
							 "\n\t\t\t" . '<img class="gold_img" src="../img/gold.png">' .
							 "\n\t\t\t" . '<p class="rate">= ' . number_format($rate, 0, '.', ' ') . ' ' . $currency_abbr . '</p>';
							 
					}
					
					echo "\n\t\t\t" . '<p class="button red remove_offer">Remove</p>' .
						 "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
			else {
				echo "<p>You're not a governor and not allowed to view this information.</p>";
			}
		?>
	
	</div>
		<?php
		//offered products
		echo "\n\t\t" . '<div id="product_offers_div">' .
			 "\n\t\t\t" . '<p id="q">Quantity</p>' .
			 "\n\t\t\t" . '<p id="p">Price</p>' .
			 "\n\t\t\t" . '<p id="t">Tax</p>';
		$query = "SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr
				  FROM product_market pm, country c, product_import_tax pit, user_profile up, product_info pi, currency cu
				  WHERE c.country_id = pm.country_id AND pit.country_id = c.country_id AND from_country_id = up.citizenship 
				  AND pit.product_id = pm.product_id AND pi.product_id = pm.product_id AND c.currency_id = cu.currency_id
				  AND up.user_id = pm.user_id AND pm.position_id = '$position_id' AND pm.for_country_id = '$country_id'
				  UNION
				  SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr FROM product_market pm, country c, 
				  product_sale_tax pst, user_profile up, product_info pi, currency cu
				  WHERE c.country_id = pm.country_id AND pst.country_id = c.country_id AND pst.country_id = up.citizenship 
				  AND pst.product_id = pm.product_id AND pi.product_id = pm.product_id AND  c.currency_id = cu.currency_id
				  AND pm.position_id = '$position_id' AND pm.for_country_id = '$country_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($flag, $quantity, $price, $sale_tax, $product_icon, $product_name, $offer_id, $currency_abbr) = $row;
			echo "\n\t\t\t" . '<div class="product_on_sale">' .
				 "\n\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="pos_product_icon" src="../product_icons/' . 
				 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
				 "\n\t\t\t\t" . '<img class="country_flag" alt="' . $flag . '" src="../country_flags/' . $flag . '">' .
				 "\n\t\t\t\t" . '<p class="pos_quantity">' . number_format($quantity, '0', '', ' ') . '</p>' .
				 "\n\t\t\t\t" . '<p class="pos_price">' . number_format($price, '2', '.', ' ') . '</p>' .
				 "\n\t\t\t\t" . '<p class="pos_currency">' . $currency_abbr . '</p>' .
				 "\n\t\t\t\t" . '<p class="pos_tax">-' . $sale_tax . '%</p>' .
				 "\n\t\t\t\t" . '<p class="pos_remove button red">Remove</p>' .
				 "\n\t\t\t\t" . '<p id="oi_' . $offer_id . '" hidden>' . $offer_id . '</p>' .
				 "\n\t\t\t" . '</div>';
		}
		echo "\n\t\t" . '</div>';
		?>
		
</main>

<?php include('footer.php'); ?>