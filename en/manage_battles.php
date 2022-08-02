<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Manage Battles</p>

		<?php
			//manage battles
			//check if president
			$is_governor = false;
			$position = 0;
			$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$is_governor = true;
				$row = $result->fetch_row();
				list($position_id, $country_id) = $row;
			}
			else { //check if congressman
				$query = "SELECT country_id FROM congress_details WHERE country_id = 
						 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				if($result->num_rows == 1) { 
					$is_governor = true;
					$row = $result->fetch_row();
					list($country_id) = $row;
					$position_id = 3;
				}
			}
			$can_manage = false;
			if($is_governor) {
				$query = "SELECT gcr.responsibility_id, responsibility
						  FROM government_country_responsibilities gcr, political_responsibilities pr
						  WHERE country_id = '$country_id' AND position_id = '$position_id' 
						  AND pr.responsibility_id = gcr.responsibility_id
						  AND have_access = TRUE AND gcr.responsibility_id = 23
						  ORDER BY responsibility_id";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$can_manage = true;
				}
			}
			
			if($can_manage) {
				//manage
				echo "\n\t\t" . '<div id="start_new_battle_div">' .
					 "\n\t\t\t" . '<p id="snbd_head">Start Battle</p>' .
					 "\n\t\t\t" . '<select id="snbd_attack_country_id">' .
					 "\n\t\t\t\t" . '<option id="0">Choose country</option>';
				
				//country list	
				$query = "SELECT country_id, country_name FROM country WHERE country_id IN 
						 (SELECT with_country_id FROM country_wars WHERE country_id = '$country_id'
						  AND active = TRUE AND is_resistance = FALSE)
						  UNION
						  SELECT country_id, country_name FROM country WHERE country_id IN
						 (SELECT country_id FROM country_wars WHERE with_country_id = '$country_id'
						  AND active = TRUE AND is_resistance = FALSE)";
				$result_countries = $conn->query($query);
				while($row_countries = $result_countries->fetch_row()) {
					list($attack_country_id, $attack_country_name) = $row_countries;
					echo "\n\t\t\t\t" . '<option value="' . $attack_country_id . '">' . $attack_country_name . '</option>';
				}	 
				echo "\n\t\t\t" . '</select>' .
					 "\n\t\t\t" . '<select id="snbd_attack_region_id">' .
					 "\n\t\t\t" . '</select>' .
					 "\n\t\t\t" . '<p id="snbd_ministry_budget">Ministry budget:</p>' .
					 "\n\t\t\t" . '<select id="snbd_ministry_budget_list">';
				
				//available ministry budget
				$query = "SELECT amount, currency_abbr, mb.currency_id FROM ministry_budget mb, currency cu
						  WHERE cu.currency_id = mb.currency_id AND country_id = '$country_id' AND position_id = '$position_id'";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($amount, $currency_abbr, $currency_id) = $row;
					$amount = number_format($amount, 2, ".", " ");
					echo "\n\t\t\t\t" . '<option value="' . $currency_id . '">' . $amount . ' ' . $currency_abbr . '</option>';
				}
				echo "\n\t\t\t" . '</select>' .
					 "\n\t\t\t" . '<p id="snbd_budget_amount">Budget amount:</p>' .
					 "\n\t\t\t" . '<input id="snbd_budget_input" maxlength="10">' .
					 "\n\t\t\t" . '<p id="snbd_damage_price">Damage price:</p>' .
					 "\n\t\t\t" . '<input id="snbd_damage_input" maxlength="7">' .
					 "\n\t\t\t" . '<p id="snbd_attacking_platform_head">Attacking platform:</p>' .
					 "\n\t\t\t" . '<select id="snbd_attacking_platform_id">';
				
				//platforms list
				$query = "SELECT platform_id, strength FROM region_attack_platform ORDER BY strength";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($platform_id, $strength) = $row;
					$strength = number_format($strength, 0, "", " ");
					echo "\n\t\t\t\t" . '<option value="' . $platform_id . '">' . $strength . ' strength</option>';
				}
				echo "\n\t\t\t" . '</select>' .
					 "\n\t\t\t" . '<div id="snbd_platform_products_div">' .
					 "\n\t\t\t\t" . '<p id="snbd_platform_product">Required products for the platform:</p>';
				
				//display resource for first platform_id
				$query = "SELECT pi.product_id, product_icon, amount, product_name FROM product_info pi, attack_platform_product app 
						  WHERE pi.product_id = app.product_id AND platform_id = 1";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($product_id, $product_icon, $amount, $product_name) = $row;
					echo "\n\t\t\t\t" . '<div class="snbd_product_info" id="p_' . $product_id . '">' . "\n" . 
						 "\n\t\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
											$product_icon . '" alt="'  . $product_name . '"></abbr>' . "\n" .
						 "\n\t\t\t\t\t" . '<p class="snbd_product_amount">' . $amount . '</p>' . "\n" .
						 "\n\t\t\t\t" . '</div>' . "\n";
				}
				echo "\n\t\t\t\t\t" . '<p class="button red" id="attack">Attack</p>' . "\n" .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			?>

			<div id="headings">
				<p id="attacker_head">Attacker</p>
				<p id="defender_head">Defender</p>			
			</div>
			
			<?php
				//active battles
				$query = "SELECT battle_id, ca.country_name, attacker_id, ca.flag, cd.country_name, defender_id, cd.flag, 
						  region_name, attacker_damage, defender_damage, 
						  attacker_strength, rap.strength AS 'attacker_fixed_strength', defender_strength,
						  dci.strength AS' defender_fixed_strength', date_started, time_started, b.region_id
						  FROM country ca, country cd, battles b, regions r, region_attack_platform rap, def_const_info dci
						  WHERE ca.country_id = attacker_id AND cd.country_id = defender_id AND r.region_id = b.region_id
						  AND active = TRUE AND rap.platform_id = b.attack_platform_id AND dci.def_loc_id = b.def_loc_id 
						  AND (defender_id = '$country_id' OR attacker_id = '$country_id')
						  ORDER BY date_started DESC, time_started DESC";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($battle_id, $attacker_country_name, $attacker_id, $attacker_flag, $defender_country_name, $defender_id, 
						 $defender_flag, $region_name, 
						 $attacker_damage, $defender_damage, $attacker_strength, $attacker_fixed_strength, $defender_strength, 
						 $defender_fixed_strength, $date_started, $time_started, $region_id) = $row;
					
					echo "\n\t\t" . '<div class="battle_info_div" id="b_' . $battle_id . '">' .
						 "\n\t\t" . '<p class="battle_id" hidden>' . $battle_id . '</p>' .
						 "\n\t\t\t" . '<a class="region_name" href="region_info?region_id=' . $region_id . '" target="_blank">' 
									. $region_name . '</a>' .
						 "\n\t\t\t" . '<a class="attacker_name" href="country?country_id=' . $attacker_id . '" target="_blank">' . 
										$attacker_country_name . '</a>' .
						 "\n\t\t\t" . '<img class="attacker_flag" src="../country_flags/' . $attacker_flag . '" alt="' . $attacker_country_name . '">' .
						 "\n\t\t\t" . '<a class="defender_name" href="country?country_id=' . $defender_id . '" target="_blank">' . 
										$defender_country_name . '</a>' .
						 "\n\t\t\t" . '<img class="defender_flag" src="../country_flags/' . $defender_flag . '" alt="' . $defender_flag . '">';
						 
					//display current force balance
					if($attacker_damage != 0 || $defender_damage != 0) {
						$overral_force_balance = $attacker_damage + $defender_damage;
						$attacker_percentage = round(($attacker_damage / $overral_force_balance) * 100,2);
						$defender_percentage = 100 - $attacker_percentage;
					}
					else {
						$attacker_percentage = 50;
						$defender_percentage = 50;
					}

					echo "\n\t\t\t" . '<div class="force_balance_div">' .
						 "\n\t\t\t\t" . '<div class="attacker_force_bar_div" style="width: ' . $attacker_percentage . '%;">' .
						 "\n\t\t\t\t\t" . '<div class="attacker_force_progress"></div>';
					if($attacker_percentage >= 10) {
						echo "\n\t\t\t\t\t" . '<p>' . $attacker_percentage . '%</p>';
					}
					else if($attacker_percentage >= 5) {
						echo "\n\t\t\t\t\t" . '<p>' . round($attacker_percentage, 1) . '%</p>';
					}
					else if($attacker_percentage >= 1) {
						echo "\n\t\t\t\t\t" . '<p>' . round($attacker_percentage) . '</p>';
					}
					echo "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<div class="defender_force_bar_div" style="width: ' . $defender_percentage . '%;">' .
						 "\n\t\t\t\t\t" . '<div class="defender_force_progress"></div>';
					if($defender_percentage >= 10) {
						echo "\n\t\t\t\t\t" . '<p>' . $defender_percentage . '%</p>';
					}
					else if($defender_percentage >= 5) {
						echo "\n\t\t\t\t\t" . '<p>' . round($defender_percentage, 1) . '%</p>';
					}
					else if($defender_percentage >= 1) {
						echo "\n\t\t\t\t\t" . '<p>' . round($defender_percentage) . '</p>';
					}
					echo "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t" . '</div>';
						 
					//display attackers platform strength
					$platform_percentage = round(($attacker_strength / $attacker_fixed_strength) * 100, 2);
					echo "\n\t\t\t" . '<div class="attacker_platform_bar_div">' .
						 "\n\t\t\t\t" . '<div class="attacker_platform_progress" style="width: ' . $platform_percentage . '%;"></div>' .
						 "\n\t\t\t\t" . '<p>' . $platform_percentage . '%</p>' .
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t\t" . '<p class="attacker_platform_info">' . number_format($attacker_strength, 2, ".", " ") . 
									  '/' . number_format($attacker_fixed_strength, 2, ".", " ") . '</p>';
					
					//display defender defence position strength
					$position_percentage = round(($defender_strength / $defender_fixed_strength) * 100, 2);
					echo "\n\t\t\t" . '<div class="defender_position_bar_div">' .
						 "\n\t\t\t\t" . '<div class="defender_position_progress" style="width: ' . $position_percentage . '%;"></div>' .
						 "\n\t\t\t\t" . '<p>' . $position_percentage . '%</p>' .
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t\t" . '<p class="defender_position_info">' . number_format($defender_strength, 2, ".", " ") . 
									  '/' . number_format($defender_fixed_strength, 2, ".", " ") . '</p>';
					
					//battle duration	
					$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
					$date2 = new DateTime($date_started . ' ' . $time_started);
					$diff = date_diff($date1,$date2);
					$days_duration = $diff->format("%d");
					$time_duration = $diff->format("%H:%I:%S");
					if($days_duration == 1) {
						echo "\n\t\t\t" . '<p class="battle_duration">' . $days_duration . ' day ' . $time_duration . '</p>';
					}
					else if($days_duration > 1) {
						echo "\n\t\t\t" . '<p class="battle_duration">' . $days_duration . ' days ' . $time_duration . '</p>';
					}
					else {
						echo "\n\t\t\t" . '<p class="battle_duration">' . $time_duration . '</p>';
					}
					
					//display attackers/defenders budget and edit btn
					$query = "SELECT budget, currency_abbr, damage_price, used_budget, country_id, bb.currency_id FROM
							  battle_budget bb, currency cu WHERE cu.currency_id = bb.currency_id AND battle_id = '$battle_id'
							  AND country_id = '$attacker_id'";
					$result_currency = $conn->query($query);
					echo "\n\t\t\t" . '<div class="attacker_budget_info_div">' .
						 "\n\t\t\t\t" . '<p class="attacker_remaining_budget">Volunteer</p>' .
						 "\n\t\t\t\t" . '<p class="attacker_damage_price">n/a</p>';
					if($result_currency->num_rows == 0 && $attacker_id == $country_id) {//add budget
						echo  "\n\t\t\t\t" . '<p class="button blue add_budget_attacker add_budget">Add</p>';
					}
					if($attacker_id == $country_id) {
						echo "\n\t\t\t\t" . '<p hidden>0</p>' .
							 "\n\t\t\t\t" . '<p class="button green place_order order_attacker">Order</p>';
					}
					while($row_currency = $result_currency->fetch_row()) {
						list($budget, $currency_abbr, $damage_price, $used_budget, $currency_country_id, $currency_id) = $row_currency;
						
						$remaining_budget = $budget - $used_budget;
						
						echo "\n\t\t\t\t" . '<p class="attacker_remaining_budget">' . number_format($remaining_budget, 2, ".", " ") . 
											' ' . $currency_abbr . '</p>' .
							 "\n\t\t\t\t" . '<p class="attacker_damage_price">' . number_format($damage_price, 2, ".", " ") . 
											' ' . $currency_abbr . ' for 100D</p>';
						if($attacker_id == $country_id) {//edit budget
							echo  "\n\t\t\t\t" . '<p class="button blue edit_budget_attacker edit_budget">Edit</p>' .
								  "\n\t\t\t\t" . '<p hidden>' . $currency_id . '</p>' .
								  "\n\t\t\t\t" . '<p class="button green place_order order_attacker">Order</p>';
						}
					}
					echo "\n\t\t\t" . '</div>';
					
					//display defenders budget and edit btn
					$query = "SELECT budget, currency_abbr, damage_price, used_budget, country_id, bb.currency_id FROM
							  battle_budget bb, currency cu WHERE cu.currency_id = bb.currency_id AND battle_id = '$battle_id'
							  AND country_id = '$defender_id'";
					$result_currency = $conn->query($query);
					echo "\n\t\t\t" . '<div class="defender_budget_info_div">' .
						 "\n\t\t\t\t" . '<p class="defender_remaining_budget">Volunteer</p>' .
						 "\n\t\t\t\t" . '<p class="defender_damage_price">n/a</p>';
					if($result_currency->num_rows == 0 && $defender_id == $country_id) {//add budget
						echo  "\n\t\t\t\t" . '<p class="button blue add_budget_defender add_budget">Add</p>';
					}
					if($defender_id == $country_id) { 
						echo "\n\t\t\t\t" . '<p hidden>0</p>' .
							 "\n\t\t\t\t" . '<p class="button green place_order order_defender">Order</p>';
					}
					while($row_currency = $result_currency->fetch_row()) {
						list($budget, $currency_abbr, $damage_price, $used_budget, $currency_country_id, $currency_id) = $row_currency;
						
						$remaining_budget = $budget - $used_budget;
			
						echo "\n\t\t\t\t" . '<p class="defender_remaining_budget">' . number_format($remaining_budget, 2, ".", " ") . 
											' ' . $currency_abbr . '</p>' .
							 "\n\t\t\t\t" . '<p class="defender_damage_price">' . number_format($damage_price, 2, ".", " ") . 
											' ' . $currency_abbr . ' for 100D</p>';
						if($defender_id == $country_id) {
							echo  "\n\t\t\t\t" . '<p class="button blue edit_budget_defender edit_budget">Edit</p>' .
								  "\n\t\t\t\t" . '<p hidden>' . $currency_id . '</p>' .
								  "\n\t\t\t\t" . '<p class="button green place_order order_defender">Order</p>';
						}
					}
					echo "\n\t\t\t" . '</div>';
					
					echo "\n\t\t" . '</div>';
					
				}
			}
		?>

	</div>
</main>

<?php include('footer.php'); ?>