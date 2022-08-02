<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php	
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Region Info</p>
		
		<?php
			$region_id = htmlentities(stripslashes((trim($_GET['region_id']))), ENT_QUOTES);
			$region_error = false;
			if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
				$region_error = true;
			}
			
			$query = "SELECT country_id, IFNULL(rn.region_id, false)
					  FROM regions r LEFT JOIN region_neighbors rn
					  ON rn.region_id = r.region_id AND neighbor = 0
					  WHERE r.region_id = '$region_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$region_error = true;
			}
			if(!$region_error) {
				$row = $result->fetch_row();
				list($country_id, $coastal) = $row;
				
				//get user citizenship
				$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($citizenship) = $row;
				
				
				/* region info */
				$query = "SELECT c.country_id, country_name, flag, c.country_id, region_name FROM regions r, country c
						  WHERE c.country_id = r.country_id AND r.region_id = '$region_id'";
				$result = $conn->query($query);
				if($result->num_rows == 0) {
					exit('Region doesn\'t exist.');
				}
				$row =  $result->fetch_row();
				list($country_id, $country_name, $flag, $region_country_id, $region_name) = $row; 

				/* check if can manage region */
				$can_manage_region = false;

				//check if president
				$is_governor = false;
				$position_id = 0;
				$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id' 
						  AND country_id = '$country_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$is_governor = true;
					$row = $result->fetch_row();
					list($position_id, $country_id) = $row;
				}
				else { //check if congressman
					$query = "SELECT country_id FROM congress_details WHERE country_id = 
							 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')  AND country_id = '$country_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) { 
						$is_governor = true;
						$row = $result->fetch_row();
						list($country_id) = $row;
						$position_id = 3;
					}
				}

				//check for active battle
				$query = "SELECT * FROM regions WHERE region_id = '$region_id' 
						  AND region_id NOT IN (SELECT region_id FROM battles WHERE active = TRUE 
						  AND region_id = '$region_id')";
	  			$result = $conn->query($query);
				if($result->num_rows == 1 && $is_governor && $country_id == $region_country_id) {
					$manage_region = 28;//responsibility
					$query = "SELECT gcr.responsibility_id, responsibility
								FROM government_country_responsibilities gcr, political_responsibilities pr
								WHERE country_id = '$country_id' AND position_id = '$position_id' 
								AND pr.responsibility_id = gcr.responsibility_id
								AND have_access = TRUE AND gcr.responsibility_id = '$manage_region'
								ORDER BY responsibility_id";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$can_manage_region = true;
					}
				}
					 
				echo "\n\t\t" . '<div id="country_name_flag">' .
					 "\n\t\t\t" . '<img id="country_flag" alt="country flag" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t" . '<a href="country?country_id=' . $country_id . '" id="country_name">' . $region_name . 
								  ' - ' . $country_name . '</a>' .
					 "\n\t\t\t" . '<p id="region_id" hidden>' . $region_id . '</p>';
				
				//check if under attack
				$query = "SELECT IFNULL(b.user_attacker_id, 0), IFNULL(user_name, 'n/a'), type FROM battles b LEFT JOIN users u 
						  ON u.user_id = b.user_attacker_id
						  WHERE active = TRUE AND region_id = '$region_id'";
				$result = $conn->query($query);
				$region_under_attack = false;
				if($result->num_rows == 1) {
					$row =  $result->fetch_row();
					list($user_attacker_id, $user_attacker_name, $type) = $row;
					$region_under_attack = true;
					
					if($type == 'resistance') {
						$type = "Resistance battle";
					}
					else if($type == 'revolt') {
						$type = "Revolt battle";
					}
					else {
						$type = "Regular battle";
					}
					echo "\n\t\t\t" . '<p id="under_attack">Under attack. ' . $type .  '. Battle started by <a href=user_profile?id=' . 
									   $user_attacker_id . '>' . $user_attacker_name . '</a>.</p>';
				}
				echo "\n\t\t" . '</div>';
				
				/* core owners */
				echo "\n\t\t\t" . '<div class="info_blocks info_blocks_no_border">' .
					 "\n\t\t\t\t" . '<p class="heads">Countries with core rights</p>';
				$query = "SELECT c.country_id, IF(r.country_id IS NULL, FALSE, TRUE), 
						  country_name, flag, hours_left 
						  FROM country c, country_core_regions ccr LEFT JOIN regions r 
						  ON r.country_id = ccr.country_id AND r.region_id = ccr.region_id
						  WHERE c.country_id = ccr.country_id AND ccr.region_id = '$region_id'";
				$result = $conn->query($query);
				if($result->num_rows == 0) {
					echo "\n\t\t\t\t" . '<p>Nobody has core rights in this region.</p>';
				}
				else {
					while($row =  $result->fetch_row()) {
						list($core_country_id, $is_owner, $core_country_name, $core_flag, $hours_left) = $row; 
						echo "\n\t\t\t\t" . '<div class="shi_details">' .
							 "\n\t\t\t\t\t" . '<img class="mini_flag" alt="country flag" src="../country_flags/' 
							 				. $core_flag . '">' .
							 "\n\t\t\t\t\t" . '<a class="shi_country_name" href="country?country_id=' 
							 				. $core_country_id . 
											  '">' . $core_country_name . '</a>' .
							 "\n\t\t\t\t\t" . '<p class="core_expiration">Expires ' . 
											   ($is_owner ? 'Never until owner' 
											   : ' in ' . $hours_left . ' hours (' .
											    (round($hours_left / 24, 2)) . ' days)')
							 				. '</p>' .
							 "\n\t\t\t\t" . '</div>';
					}
				}
				echo "\n\t\t\t" . '</div>';

				/* defender's moral */
				echo "\n\t\t\t" . '<div class="info_blocks info_blocks_no_border">' .
					 "\n\t\t\t\t" . '<p class="heads">Defender\'s moral</p>';
				$query = "SELECT * FROM country_core_regions ccr 
						  WHERE ccr.country_id = '$country_id' AND region_id = '$region_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$query = "SELECT moral FROM moral_effects WHERE effect_name = 'defending_core'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($defenders_moral) = $row;

					echo "\n\t\t\t\t" . '<p id="defenders_moral">' . $defenders_moral . '%</p>';
				}
				else {
					$query = "SELECT moral FROM moral_effects WHERE effect_name = 'defending_none_core'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($defending_none_core_moral) = $row;

					$query = "SELECT moral FROM moral_effects WHERE effect_name = 'max_negative_moral'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($max_negative_moral) = $row;

					$query = "SELECT (COUNT(r.region_id) - COUNT(ccr.region_id)) * me.moral 
							  AS base_none_core_moral
							  FROM moral_effects me, regions r LEFT JOIN country_core_regions ccr 
							  ON ccr.country_id = r.country_id AND r.region_id = ccr.region_id
							  WHERE r.country_id = '$country_id' AND me.effect_name = 'owning_none_core'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($base_none_core_moral) = $row;

					$total_moral = $defending_none_core_moral + $base_none_core_moral;
					$total_moral = $total_moral < $max_negative_moral ? $max_negative_moral : $total_moral;

					echo "\n\t\t\t\t" . '<p id="defenders_moral">' . $total_moral . '%</p>';

					//core creation in process
					$query = "SELECT hours_left, hours FROM core_creation, make_core_requirements 
							  WHERE region_id = '$region_id' AND country_id = '$country_id' 
							  AND is_active = TRUE";
					$result = $conn->query($query);
					if($result->num_rows >= 1) {
						$row = $result->fetch_row();
						list($hours_left, $req_hours) = $row;

						$progress = round((($req_hours - $hours_left) / $req_hours) * 100, 2);

						echo 
							!$region_under_attack ? '<p id="make_core_label">Core creation in progress.</p>'
							:  '<p id="make_core_label">Core creation paused. Region under attack.</p>';
						echo'<div id="core_creation_bar_div">' .
								'<div id="core_creation_progress" style="width: ' . $progress . '%;"></div>' .
								'<p>'. $hours_left . ' hours left</p>' .
							'</div>';
					}
					else {
						//make core
						if($can_manage_region) {
							echo "\n\t\t\t\t" . '<p id="make_core_label">Make this region your core' .
												' in order to eliminate negative moral effects.</p>' .
								"\n\t\t\t\t" . '<p id="make_core" class="button blue">Make core</p>';
						}
					}
				}
				echo "\n\t\t\t" . '</div>';

				/* productivity bonus */
				$query = "SELECT product_name, product_icon, bonus
						  FROM region_resource_bonus rrb, product_info pi
						  WHERE rrb.region_id = '$region_id' AND rrb.product_id = pi.product_id";
				$result_resources = $conn->query($query);
					
				echo "\n\t\t\t" . '<div class="info_blocks">' .
					 "\n\t\t\t\t" . '<p class="heads">Productivity bonus</p>';
				while($row_r = $result_resources->fetch_row()) {
					list($product_name, $product_icon, $bonus) = $row_r;
					echo "\n\t\t\t\t" . '<div class="icon_amount">' .
						 "\n\t\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
										  $product_icon . '" alt="' . $product_name . '"></abbr>' .
						 "\n\t\t\t\t\t" . '<p class="amount">' . $bonus . '%</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
				
				//is coastal
				if($coastal) {
					echo "\n\t\t\t" . '<div class="info_blocks">' .
						 "\n\t\t\t\t" . '<p class="heads">Sea access</p>' .
						 "\n\t\t\t\t" . '<div class="crb_coastal_region">' .
						 "\n\t\t\t\t\t" . '<img src="../country_flags/ocean.png" alt="Sea Access">' .
						 "\n\t\t\t\t\t" . '<p>Coastal region</p>' .
						 "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t" . '</div>';
				}
				
				/* defence location info */			
				$query = "SELECT rds.strength, dci.strength, const_img FROM region_defence_systems rds, def_const_info dci
						  WHERE region_id = '$region_id' AND dci.def_loc_id = rds.def_loc_id";
				$result_def_loc = $conn->query($query);
				$row_def_loc = $result_def_loc->fetch_row();
				list($defence_strength, $base_strength, $def_img) = $row_def_loc;
				$position_percentage = round(($defence_strength / $base_strength) * 100, 2);
				
				echo "\n\t\t\t" . '<div class="info_blocks">' .
					 "\n\t\t\t\t" . '<p class="heads">Region defense system:</p>' .
					 "\n\t\t\t\t" . '<div id="region_defence_system_info">' .
					 "\n\t\t\t\t\t" . '<img id="defence_system_img" src="../infrastructure/' . $def_img . '" alt="defence system">';
				if($can_manage_region) {
					//check if upgrade in progress, display upgrade btn
					//check if max level reached
					$query = "SELECT MAX(dci.def_loc_id), rds.def_loc_id 
							  FROM def_const_info dci, region_defence_systems rds WHERE region_id = '$region_id'";
					$result = $conn->query($query);				
					$row = $result->fetch_row();
					list($max_def_loc, $region_def_loc) = $row;
					if($region_def_loc < $max_def_loc) {
						$query = "SELECT * FROM defence_const_in_process WHERE region_id = '$region_id'";
						$result = $conn->query($query);
						if($result->num_rows == 0) {
							echo "\n\t\t\t\t" . '<p id="upgrade_def_sys">Upgrade</p>';
						}
					}
				}
				echo "\n\t\t\t\t\t" . '<div id="position_bar_div">' .
					 "\n\t\t\t\t\t\t" . '<div id="position_progress" style="width: ' . $position_percentage . '%;"></div>' .
					 "\n\t\t\t\t\t\t" . '<p>' . $position_percentage . '%</p>' .
					 "\n\t\t\t\t\t" . '</div>' .
					 "\n\t\t\t\t\t" . '<p id="position_info">' . number_format($defence_strength, 2, ".", " ") . 
									  '/' . number_format($base_strength, 2, ".", " ") . '</p>' .
					 "\n\t\t\t\t" . '</div>';
					 
				//select product required for repair. if has permission
				if($position_percentage < 100) {
					if($can_manage_region) {
						$query = "SELECT product_name, product_icon, amount, pi.product_id
									FROM repair_defence_construction rdc, product_info pi
									WHERE rdc.region_id = '$region_id' AND rdc.product_id = pi.product_id";
						$result_resources = $conn->query($query);
						
						echo "\n\t\t\t\t" . '<div id="defence_repair_div">';
						while($row_r = $result_resources->fetch_row()) {
							list($product_name, $product_icon, $amount, $product_id) = $row_r;
							echo "\n\t\t\t\t" . '<div class="repair_product_info" id="rds_' . $product_id . '">' .
									"\n\t\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" ' .
													'src="../product_icons/' . $product_icon . '" alt="' . $product_name . 
													'"></abbr>' .
									"\n\t\t\t\t\t" . '<p class="amount">' . number_format($amount, 0, "", " ") . '</p>' .
									"\n\t\t\t\t\t" . '<p class="repair">Repair</p>' .
									"\n\t\t\t\t\t" . '<p hidden>' . $product_id . '</p>' .
									"\n\t\t\t\t" . '</div>';
						}
						echo "\n\t\t\t\t" . '</div>';
					}
				}
				
				//check if upgrade in progress.
				$query = "SELECT start_date, start_time, strength, const_img, time_min 
						  FROM def_const_info dci, defence_const_in_process dcip
						  WHERE region_id = '$region_id' AND dcip.def_loc_id = dci.def_loc_id";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($start_date, $start_time, $strength, $const_img, $time_min) = $row;
					
					$end_date_time = date('Y-m-d H:i:s', strtotime($start_date . ' ' . $start_time . ' + ' . $time_min . ' minutes'));
					
					if(strtotime($end_date_time) > strtotime(date('Y-m-d H:i:s'))) {
						$date1 = new DateTime($end_date_time);
						$date2 = new DateTime(date('Y-m-d H:i:s'));
						$diff = date_diff($date1,$date2);
						
						$days = $diff->format("%a");
						$hours = $diff->format("%h");
						$hours += $days * 24;
						$mins = $diff->format("%i");
						$sec = $diff->format("%s");

						$remaining_time = sprintf('%02d:%02d:%02d', $hours, $mins, $sec);
						
						echo "\n\t\t\t\t" . '<div id="region_upgrade_system_div">' .
							 "\n\t\t\t\t\t" . '<p id="rusd_head">Upgrading</p>' .
							 "\n\t\t\t\t\t" . '<img id="defence_system_img" src="../infrastructure/' . $const_img .
											   '" alt="defence system">' .
							 "\n\t\t\t\t\t" . '<p id="upgrade_end_in">' . $remaining_time . '</p>' .
							 "\n\t\t\t\t\t" . '<p id="upgrading_def_strength"><i class="fa fa-shield" aria-hidden="true"></i> ' . 
											   number_format($strength , 0, "", " ") . '</p>' .
							 "\n\t\t\t\t" . '</div>';
					}
				}
				echo "\n\t\t\t" . '</div>';
				
				
				/* road info */
				$query = "SELECT rr.road_id, road_img, rr.durability, rci.durability, productivity_bonus, repair_durability 
						  FROM region_roads rr, road_const_info rci
						  WHERE rr.region_id = '$region_id' AND rci.road_id = rr.road_id";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($road_id, $road_img, $left_durability, $total_durability, $productivity_bonus, $repair_durability) = $row;
					$productivity_bonus = $productivity_bonus * 100;
					
					$bar_width = round((100 / $total_durability) * $left_durability, 2);
					if($bar_width >= 50 && $bar_width < 75) {
						$color = 'orange';
					}
					elseif($bar_width >= 75) {
						$color = 'green';
					}
					else {
						$color = 'red';
					}
					
					echo "\n\t\t\t" . '<div class="info_blocks">' .
						 "\n\t\t\t\t" . '<p class="heads">Region road</p>' .
						 "\n\t\t\t\t" . '<div class="cr_region_road">' .
						 "\n\t\t\t\t\t" . '<img src="../infrastructure/' . $road_img . '" alt="Road">' .
						 "\n\t\t\t\t\t" . '<p class="crrr_level">Level: ' . $road_id . '</p>' .
						 "\n\t\t\t\t\t" . '<div class="crrr_durability_div">' .
						 "\n\t\t\t\t\t\t" . '<div class="progress ' . $color . '" style="width: ' . $bar_width . '%;"></div>' .
						 "\n\t\t\t\t\t\t" . '<p>Uses left: ' . number_format($left_durability, 0, '', ' ') . '/' . 
											number_format($total_durability, 0, '', ' ') . '</p>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p class="crrr_bonus">Productivity bonus: ' . $productivity_bonus . '%</p>';
			
					//upgrade					
					if($is_governor && $country_id == $region_country_id) {
						echo "\n\t\t\t\t" . '<p id="upgrade_road">Upgrade</p>';
					}
					
					//repair
					if($left_durability <= $total_durability - $repair_durability) {
						echo "\n\t\t\t\t" . '<p id="repair_road">Repair</p>';
					}
					echo "\n\t\t\t\t" . '</div>';
				}
				else {
					echo "\n\t\t\t" . '<div class="info_blocks">' .
						 "\n\t\t\t\t" . '<p class="heads">Region road</p>' .
						 "\n\t\t\t\t" . '<div class="cr_region_road">' .
						 "\n\t\t\t\t\t" . '<img src="../infrastructure/road_0.png" alt="Road">' .
						 "\n\t\t\t\t\t" . '<p class="crrr_level">Level: 0</p>' .
						 "\n\t\t\t\t\t" . '<div class="crrr_durability_div">' .
						 "\n\t\t\t\t\t\t" . '<div class="progress green" style="width: 100%;"></div>' .
						 "\n\t\t\t\t\t\t" . '<p>Uses left: 0/0</p>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p class="crrr_bonus">Productivity bonus: 0%</p>';
						 
					//upgrade					
					if($is_governor && $country_id == $region_country_id) {
						echo "\n\t\t\t\t" . '<p id="upgrade_road">Upgrade</p>';
					}
					echo "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
				
				//select day count for statistics
				$query = "SELECT day_number, date FROM day_count
						  WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY) ORDER BY day_number";
				$result = $conn->query($query);
				
				$start_day = strtotime(correctDate(date('Y-m-d'), date('H:i:s'), $country_id) . '00:00:00 - 11 day');
				$day_count_info = array();
				$x = 0;
				while($row = $result->fetch_row()) {
					list($day_number, $date) = $row;
					
					//make sure to display right day number
					$country_date = correctDate($date, date('H:i:s'), $country_id);
					$country_date_start = strtotime("$country_date 00:00:00");
					
					//if selects 'old' data
					if($country_date_start < $start_day) {
						continue;
					}
					
					if(strtotime($country_date) > strtotime($date)) {
						$day_number++;
					}
					else if(strtotime($country_date) < strtotime($date)) {
						$day_number--;
					}
					
					$day_count_info[$x]['day_number'] = $day_number;
					$day_count_info[$x]['country_date_start'] = $country_date_start;
					$x++;
				}

				$day_count_info[$x]['country_date_start'] = strtotime("$country_date 00:00:00 + 1 day");
				
				function displayChart($daily_income_array, $bar_fill) {
					//find the greatest income
					$greatest_income = 0;
					for($x = 0; $x < count($daily_income_array); $x++) {
						if($daily_income_array[$x][0] > $greatest_income) {
							$greatest_income = $daily_income_array[$x][0];
						}
					}
					
					
					echo "\n\t\t" . '<svg width="850" height="270">' .
						 "\n\t\t\t" . '<rect x="50" width="800" height="250" id="cdi_rext_back"/>';
					
					//display horizontal line break and amount break
					$start_y = 25;
					$text_step = 30;
					
					$line_step = $greatest_income / 10;
					$original_display_value = $greatest_income;
					
					for($x = 1; $x < 10; $x++) {
						$display_value = round($original_display_value);
						$digits = strlen((string)$display_value);
						if($digits == 1) {
							$text_x = 35;
						}
						elseif($digits == 2) {
							$text_x = 25;
						}
						elseif($digits == 3) {
							$text_x = 12;
						}
						elseif($digits == 4) {
							$text_x = 5;
						}
						else {
							$text_x = 0;
						}
						echo "\n\t\t\t" . '<text x="' . $text_x . '" y="' . $text_step . '">' . $display_value . '</text>';
						echo "\n\t\t\t" . '<line x1="45" y1="' . $start_y  . '" x2="850" y2="' . $start_y  . 
							'" class="chart_line_break" />';
						$start_y += 25;
						$text_step += 25;
						$original_display_value -= $line_step;
					}
					
					//display bars
					$from_left = 65;
					if($greatest_income > 0) {
						$step = round(250 / $greatest_income, 10);
						$straight_step = round(25 / ($greatest_income * 0.2), 5);
					}
					else {
						$step = 0;
						$straight_step = 0;
					}
					
					for($x = 0; $x < count($daily_income_array); $x++) {
						if($daily_income_array[$x][0] == 0) {
							$height = 0;
						}
						else {
							if($daily_income_array[$x][0] < ($greatest_income * 0.2))  {
								//if income < 20% of max, then height distorts
								$height = round($daily_income_array[$x][0] * $straight_step);
							}
							else {
								$height = round(($daily_income_array[$x][0]  * $step) - 25); //find height of the char
							}
						}
						$from_top = 250 - $height;
		
						$digits = strlen((string)round($daily_income_array[$x][0]));
						if($digits > 5) {
							$text_from_left = $from_left - 10;
						}
						else {
							$text_from_left = $from_left;
						}
					
						echo "\n\t\t\t" . '<text x="' . $text_from_left . '" y="' . ($from_top - 5) . '">' . $daily_income_array[$x][0] . '</text>';
						echo "\n\t\t\t" . '<text x="' . $from_left . '" y="265" class="day_num_chart">Day ' . $daily_income_array[$x][1] . '</text>';
						echo "\n\t\t\t" . '<rect width="50" height="' . $height . '" x="' . $from_left . '" y="' . $from_top . '" 
							   style="fill:' . $bar_fill . ';" class="stat_tree"/>';
						$from_left += 65;
					}
					echo "\n\t\t" . '</svg>';
				}
				
				/* Daily Product Income Graph */
				if($citizenship == $country_id) {
					echo "\n\t\t\t" . '<div class="country_stat_div">' .
						"\n\t\t\t\t" . '<p class="heads difth">Daily income in Gold from Production taxes:</p>';
					
					//select 1 extra.
					$query = "SELECT SUM(amount*price)/(SELECT price FROM product_price WHERE product_id = 1), date, time
							FROM country_product_income cpi, product_price pp WHERE region_id = '$region_id'
							AND cpi.product_id = pp.product_id
							AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY) 
							GROUP BY date, time ORDER BY date, time";
					$result = $conn->query($query);
					$x = -1;
					$daily_income_array = array();

					while($row = $result->fetch_row()) {
						list($amount, $date, $time) = $row;
						
						if($amount > 1000) {
							$amount = round($amount);
						}
						else if($amount > 100) {
							$amount = round($amount, 1);
						}
						else {
							$amount = round($amount, 2);
						}
						
						$date = correctDate($date, $time, $country_id);
						$time = correctTime($time, $country_id);
						
						$country_stat_date_time = strtotime("$date $time - 4 hour");
						
						//if selects 'old' data
						if($country_date_start < $start_day) {
							continue;
						}
						
						$next_day = false;
						$flag = true;
						
						while($flag) {
							if($country_stat_date_time >= $day_count_info[$x+1]['country_date_start']
							&& $country_stat_date_time < $day_count_info[$x+2]['country_date_start']) {
								$next_day = true;
								$flag = false;
								$x++;
							}
							else if($country_stat_date_time >= $day_count_info[$x]['country_date_start']
									&& $country_stat_date_time < $day_count_info[$x+1]['country_date_start']){
								$next_day = false;
								$flag = false;
							}
							else {
								$x++;
								$daily_income_array[$x][0] = 0;
								$daily_income_array[$x][1] = $day_count_info[$x]['day_number'];
							}
						}
						
						if($next_day) {
							$daily_income_array[$x][0] = $amount;
							$daily_income_array[$x][1] = $day_count_info[$x]['day_number'];
						}
						else {
							$daily_income_array[$x][0] += $amount;
						}
					}

					while(count($daily_income_array) >= 13) {//if selects for more than 12 days
						array_shift($daily_income_array);
					}
					
					displayChart($daily_income_array, 'rgb(218, 180, 57)');
					echo  "\n\t\t\t" . '</div>';
				}
				else {
					echo'<div class="info_blocks info_blocks_no_border">' .
					 	'<p class="heads">Daily income in Gold from Production taxes</p>' .
						'<p class="top_secret">Top Secret</p>';
				}
				
				//Resistance War
				$RATIO = 0.5;//50% resources required for level platform
				
				echo'<div class="info_blocks info_blocks_no_border">' .
					'<p class="heads">Resistance War</p>';
				//check if occupied
				$query = "SELECT c.country_id, country_name, flag 
							FROM country c, country_core_regions ccr, regions r
							WHERE c.country_id = ccr.country_id AND ccr.region_id = '$region_id' 
							AND r.region_id = ccr.region_id
							AND r.country_id != ccr.country_id";
				$result_core = $conn->query($query);
				while($row_core = $result_core->fetch_row()) {
					list($core_country_id, $core_country_name, $flag) = $row_core;
					echo'<div class="shi_details">' .
						'<img class="mini_flag country_margin_flag" alt="' . $core_country_name . 
						'" src="../country_flags/' . $flag . '">' .
						'<a class="shi_country_name" href="country?country_id=' . $core_country_id . 
						'">' . $core_country_name . '</a>';
					
					//get required products for platform
					$query = "SELECT SUM(amount) FROM attack_platform_product WHERE platform_id = 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($req_products) = $row;
					$req_products = round($RATIO * $req_products, 2);
					
					//get collected products for platform
					$query = "SELECT IFNULL(SUM(amount), 0) FROM resistance_war_resources WHERE country_id = '$core_country_id' 
								AND region_id = '$region_id'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($collected_prods) = $row;
					
					$collected_percentage = round((100 / $req_products) * $collected_prods, 2);
					
					echo'<div class="war_prep_info_div" id="w_' . $core_country_id . '">';
					if(!$region_under_attack) {
						echo"\n\t\t\t\t\t\t" . '<p class="war_prep_res_coll_lbl">Resources Collected</p>' .
							"\n\t\t\t\t\t\t" . '<div class="war_bar">' .
							"\n\t\t\t\t\t\t\t" . '<div class="progress" style="width:' . $collected_percentage . 
												'%; background-color:rgb(245, 82, 88);"></div>' .
							"\n\t\t\t\t\t\t\t\t" . '<p class="bar_mark">' . $collected_prods . '(' . $collected_percentage . '%)</p> '. 
							"\n\t\t\t\t\t\t" . '</div>' .
							"\n\t\t\t\t\t\t" . '<p class="support_resistance button green" country_id="' . $core_country_id . 
												'" region_id="' . $region_id . '">Support</p>';
					}
					else {
						echo'<p>Region is under attack.</p>';
					}
					echo'</div>' .
						'</div>'; 
				}
				echo "\n\t\t\t" . '</div>';

				//Revolt War
				echo'<div class="info_blocks info_blocks_no_border">' .
					'<p class="heads">Revolt War</p>';
				
				echo '<drop-down-list id="revolt_countries_div" title="Chose country">';
				//get countries w/o core regions
				$query = "SELECT country_id, country_name, flag FROM country WHERE country_id NOT IN
						 (SELECT country_id FROM country_core_regions)";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($revolt_country_id, $revolt_country_name, $revolt_flag) = $row;
					echo'<item class="rcd_country" country_id="' . $revolt_country_id . '">' .
						'<img class="mini_flag country_margin_flag" alt="' . $revolt_country_name . 
						'" src="../country_flags/' . $revolt_flag . '">' .
						'<p class="shi_country_name">' . $revolt_country_name . '</p>' .
						'</item>';
				}
				echo'</drop-down-list>' .
					'<p id="rcd_cost_label">Cost: 5 Gold.</p>' .
					'<p class="button green" id="revolt">Revolt</p>' . 
					'<div id="request_new_country_div"><p id="rnc_label">Request new country</p></div>' .
					'</div>';
				
				//Region Companies
				echo "\n\t\t\t" . '<div class="info_blocks info_blocks_no_border">' .
					 "\n\t\t\t\t" . '<p class="heads">Companies located in the region</p>';
					 
				if($citizenship == $country_id) {
					echo "\n\t\t\t\t" . '<div id="rcd_heads" class="shi_details">' .
						 "\n\t\t\t\t\t" . '<p id="rcd_nation">Building Name</p>' .
						 "\n\t\t\t\t\t" . '<p id="rcd_citiz">Companies</p>' .
						 "\n\t\t\t\t" . '</div>';
					$query = "SELECT COUNT(company_id) AS companies, building_icon, name 
							  FROM companies c, building_info bi
							  WHERE bi.building_id = c.building_id AND location = '$region_id'
							  GROUP BY c.building_id ORDER BY companies DESC";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($companies, $building_icon, $building_name) = $row;
						
						echo "\n\t\t\t\t" . '<div class="region_companies_div shi_details">' .
							 "\n\t\t\t\t\t" . '<abbr title="' . $building_name . '">' .
											'<img class="building_icon" src="../building_icons/' . $building_icon .
											'" alt="' . $building_name . '"></abbr>' .
							 "\n\t\t\t\t\t" . '<p class="rcd_building_name">' . $building_name . '</p>' .
							 "\n\t\t\t\t\t" . '<p class="rcd_quantity">' . $companies . '</p>' .
							 "\n\t\t\t\t" . '</div>';
					}
				}
				else {
					echo "\n\t\t\t\t" . '<p class="top_secret">Top Secret</p>';
				}
				echo "\n\t\t\t" . '</div>';
			}
			else {
				echo "\n\t\t\t" . '<p>Region doesn\'t exists.</p>';
			}
		?>
	</div>
	
</main>

<?php include('footer.php'); ?>