<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php
			$battle_id = htmlentities(stripslashes(trim($_GET['battle_id'])), ENT_QUOTES);
			$currency_id = htmlentities(stripslashes(trim($_GET['currency_id'])), ENT_QUOTES);
			$side = htmlentities(stripslashes(trim($_GET['side'])), ENT_QUOTES);

			$flag = true;
			if(!is_numeric($battle_id)) {
				$flag = false;
			}
			if(!is_numeric($currency_id)) {
				$flag = false;
			}
			if($side != "attacker" && $side != "defender") {
				$flag = false;
			}
			
			if($flag) {
				if($side == 'attacker') {
					$select_damage = 'attacker_id';
				}
				else if($side == 'defender') {
					$select_damage = 'defender_id';
				}
				$query = "SELECT b.battle_id, ca.country_name, attacker_id, ca.flag, cd.country_name, 
						  defender_id, cd.flag, region_name, attacker_damage, defender_damage, 
						  attacker_strength, rap.strength AS 'attacker_fixed_strength', defender_strength,
						  dci.strength AS' defender_fixed_strength', date_started, time_started, 
						  IFNULL(damage, 0), b.region_id, active, b.region_id, date_ended, time_ended,
						  defenders_moral, attackers_moral
						  FROM country ca, country cd, battles b LEFT JOIN battle_user_damage bud ON bud.battle_id = b.battle_id 
						  AND for_country_id = $select_damage
						  AND user_id = '$user_id', regions r, region_attack_platform rap, def_const_info dci
						  WHERE ca.country_id = attacker_id AND cd.country_id = defender_id AND r.region_id = b.region_id
						  AND rap.platform_id = b.attack_platform_id AND dci.def_loc_id = b.def_loc_id AND b.battle_id = '$battle_id'
						  ORDER BY date_started DESC, time_started DESC";
				$result = $conn->query($query);
				if($result->num_rows >= 1) {
					$row = $result->fetch_row();
					list($battle_id, $attacker_country_name, $attacker_id, $attacker_flag, $defender_country_name, 
						 $defender_id, $defender_flag, $region_name, $attacker_damage, $defender_damage, 
						 $attacker_strength, $attacker_fixed_strength, $defender_strength, 
						 $defender_fixed_strength, $date_started, $time_started, $user_damage, $attacked_region,
						 $active, $region_id, $date_ended, $time_ended, $defenders_moral, $attackers_moral) = $row;

					//battle duration
					if($active) {
						$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
					}
					else {
						$date1 = new DateTime(date('Y-m-d', strtotime($date_ended)) . ' ' . date('H:i:s', strtotime($time_ended)));
					}
					$date2 = new DateTime($date_started . ' ' . $time_started);
					$diff = date_diff($date1,$date2);
					$days_duration = $diff->format("%a");
					$time_duration = $diff->format("%H:%I:%S");	 
			
					//if before twelve, display a message.
					if (!$active) {
						echo "\n\t\t\t" . '<p id="before_twelve">The battle is over</p>';
					}
					
					echo "\n\t\t" . '<div id="battle_info_div">' .
						 "\n\t\t\t" . '<a id="region_name" href="region_info?region_id=' . $region_id . '" target="_blank">' 
									. $region_name . '</a>' .
						 "\n\t\t\t" . '<a id="attacker_name" href="country?country_id=' . $attacker_id . '" target="_blank">' . 
										$attacker_country_name . '</a>' .
						 "\n\t\t\t" . '<img id="attacker_flag" src="../country_flags/' . $attacker_flag . '" alt="' . $attacker_country_name . '">' .
						 "\n\t\t\t" . '<div id="attacker_flag_div"></div>' .
						 "\n\t\t\t" . '<a id="defender_name" href="country?country_id=' . $defender_id . '" target="_blank">' . 
										$defender_country_name . '</a>' .
						 "\n\t\t\t" . '<img id="defender_flag" src="../country_flags/' . $defender_flag . '" alt="' . $defender_flag . '">' .
						 "\n\t\t\t" . '<div id="defender_flag_div"></div>';
						 
					//display current force balance
					if($attacker_damage != 0 || $defender_damage != 0) {
						$overall_force_balance = $attacker_damage + $defender_damage;
						$attacker_percentage = round(($attacker_damage / $overall_force_balance) * 100,2);
						$defender_percentage = 100 - $attacker_percentage;
					}
					else {
						$attacker_percentage = 50;
						$defender_percentage = 50;
					}

					echo "\n\t\t\t" . '<div id="force_balance_div">' .
						 "\n\t\t\t\t" . '<div id="attacker_force_bar_div" style="width: ' . $attacker_percentage . '%;">' .
						 "\n\t\t\t\t\t" . '<div id="attacker_force_progress"></div>' .
						 "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<div id="defender_force_bar_div" style="width: ' . $defender_percentage . '%;">' .
						 "\n\t\t\t\t\t" . '<div id="defender_force_progress"></div>' .
						 "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p id="afbd_perc">' . $attacker_percentage . '%</p>' .
						 "\n\t\t\t\t\t" . '<p id="dfbd_perc">' . $defender_percentage . '%</p>' .
						 "\n\t\t\t" . '</div>';
						 
					echo "\n\t\t\t" . '<p id="attacker_damage">' . number_format($attacker_damage, 2, ".", " ") . '</p>'; 
					echo "\n\t\t\t" . '<p id="defender_damage">' . number_format($defender_damage, 2, ".", " ") . '</p>'; 
						 
					//display attackers platform strength
					$platform_percentage = round(($attacker_strength / $attacker_fixed_strength) * 100, 2);
					echo "\n\t\t\t" . '<div id="attacker_platform_bar_div">' .
						 "\n\t\t\t\t" . '<div id="attacker_platform_progress" style="width: ' . $platform_percentage . '%;"></div>' .
						 "\n\t\t\t\t" . '<p>' . $platform_percentage . '%</p>' .
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t\t" . '<p id="attacker_platform_info">' . number_format($attacker_strength, 2, ".", " ") . 
									  '/' . number_format($attacker_fixed_strength, 2, ".", " ") . '</p>' .
						 "\n\t\t\t" . '<p id="attacker_moral">Moral: ' . $attackers_moral . '%</p>';			  ;
					
					//display defender defence position strength
					$position_percentage = round(($defender_strength / $defender_fixed_strength) * 100, 2);
					echo "\n\t\t\t" . '<div id="defender_position_bar_div">' .
						 "\n\t\t\t\t" . '<div id="defender_position_progress" style="width: ' . $position_percentage . '%;"></div>' .
						 "\n\t\t\t\t" . '<p>' . $position_percentage . '%</p>' .
						 "\n\t\t\t" . '</div>' .
						 "\n\t\t\t" . '<p id="defender_position_info">' . number_format($defender_strength, 2, ".", " ") . 
									  '/' . number_format($defender_fixed_strength, 2, ".", " ") . '</p>' .
						"\n\t\t\t" . '<p id="defender_moral">Moral: ' . $defenders_moral . '%</p>';
					
					//battle duration
					if($days_duration == 1) {
						if($active == '1') {
							echo "\n\t\t\t" . '<p id="battle_duration">' . 
											   $days_duration . ' day ' . $time_duration . '</p>';
						}
						else {
							echo "\n\t\t\t" . '<p id="battle_ended">' . 
											   $days_duration . ' day ' . $time_duration . '</p>';
						}
					}
					else if($days_duration > 1) {
						if($active == true) {
							echo "\n\t\t\t" . '<p id="battle_duration">' . 
											   $days_duration . ' days ' . $time_duration . '</p>';
						}
						else {
							echo "\n\t\t\t" . '<p id="battle_ended">' . 
											   $days_duration . ' days ' . $time_duration . '</p>';
						}
					}
					else {
						if($active == '1') {
							echo "\n\t\t\t" . '<p id="battle_duration">' . $time_duration . '</p>';
						}
						else {
							echo "\n\t\t\t" . '<p id="battle_ended">' . $time_duration . '</p>';
						}
					}
					
					//user damage and msg
					echo "\n\t\t\t" . '<p id="user_damage">' . number_format($user_damage, 2, ".", " ") . '</p>' .
						 "\n\t\t\t" . '<div id="user_damage_temp"></div>' . 
						 "\n\t\t\t" . '<p id="user_reward_temp"></p>' .
						 "\n\t\t\t" . '<div id="user_exp_reward_temp"></div>';

					/* battle controls */
					$person_base_damage = 2;
					echo "\n\t\t\t" . '<div id="soldier_inventory">';
					
					//available people
					echo "\n\t\t\t" . '<div id="soldiers_div">';

					$query = "SELECT person_id, p.energy, person_name, combat_exp, wound, ec.energy  
							  FROM people p, energy_consumption ec 
							  WHERE user_id = '$user_id' AND ec.cons_id = 5 ORDER BY years ASC";
					$result = $conn->query($query);
					echo "\n\t\t\t\t" . '<div id="previous_soldier"><p class="fa fa-angle-double-left" aria-hidden="true"></p></div>';
					while($row = $result->fetch_row()) {
						list($person_id, $energy, $person_name, $combat_exp, $wound, $max_energy) = $row;
						$person_damage = $person_base_damage + ($combat_exp / 100);
						$energy_width = ($energy/$max_energy) * 100;

						echo "\n\t\t\t\t" . '<div class="soldier_info" id="' . $person_id . '" energy="' . $energy . '">' .
							 "\n\t\t\t\t\t" . '<p class="soldier_name">' . $person_name . '</p>' .
							 "\n\t\t\t\t\t" . '<img class="soldier_image" src="../img/soldier.png" alt="soldier image">' .
							 "\n\t\t\t\t\t" . '<p class="base_damage">' . $person_damage . ' D</p>' .
							 "\n\t\t\t\t\t" . '<p class="wound" wounds="' . $wound . '">' . $wound . ' W</p>' .
							 "\n\t\t\t\t\t" . '<p class="combat_exp">' . $combat_exp . ' CE</p>' .
							 "\n\t\t\t\t\t" . '<div class="person_energy_bar">' .
							 "\n\t\t\t\t\t\t" . '<div class="person_progress" style="width: ' . $energy_width . '%;"></div>' .
							 "\n\t\t\t\t\t\t" . '<p>' . $energy . '/' . $max_energy . '</p>' .
							 "\n\t\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t" . '</div>';
					}
					echo "\n\t\t\t\t" . '<div id="next_soldier"><p class="fa fa-angle-double-right" aria-hidden="true"></p></div>';
					echo "\n\t\t\t" . '</div>';

					//fight until X energy
					echo "\n\t\t\t\t" . '<div id="fight_until_energy_div">' .
						 "\n\t\t\t\t\t" . '<p id="fued_label">Auto Fight until energy</p>' .
						 "\n\t\t\t\t\t" . '<p class="fued_energy" value="0">0</p>' .
						 "\n\t\t\t\t\t" . '<p class="fued_energy" value="20">20</p>' .
						 "\n\t\t\t\t\t" . '<p class="fued_energy" value="40">40</p>' .
						 "\n\t\t\t\t\t" . '<p class="fued_energy" value="60">60</p>' .
						 "\n\t\t\t\t\t" . '<p class="fued_energy" value="80">80</p>' .
						 "\n\t\t\t\t" . '</div>';
					
					//display inventory
					//weapons
					$query = "SELECT IFNULL(amount, 0), product_icon, product_name, pi.product_id, damage 
							  FROM weapons_info wi, product_info pi LEFT JOIN user_product up ON
							  pi.product_id = up.product_id AND user_id = '$user_id' WHERE pi.product_id 
							  IN (SELECT weapon_id FROM weapons_info) AND pi.product_id = weapon_id";
					$result = $conn->query($query);
					echo "\n\t\t\t\t" . '<div id="weapons_div">' .
						 "\n\t\t\t\t" . '<div class="next_prev_inv" id="previous_weapon"><p class="fa fa-angle-double-up" aria-hidden="true"></p></div>';
					
					echo "\n\t\t\t\t" . '<div class="weapon_info" id="0">' .
						 "\n\t\t\t\t\t" . '<p class="inventory_damage">+0D</p>' .
						 "\n\t\t\t\t\t" . '<img class="inventory_icon" src="../product_icons/0.png">' .
						 "\n\t\t\t\t\t" . '<p class="inventory_amount">0.00</p>' .
						 "\n\t\t\t\t" . '</div>';
					while($row = $result->fetch_row()) {
						list($amount, $product_icon, $product_name, $product_id, $damage) = $row;
						echo "\n\t\t\t\t" . '<div class="weapon_info" id="' . $product_id . '">' .
							 "\n\t\t\t\t\t" . '<p class="inventory_damage">+' . $damage . 'D</p>' .
							 "\n\t\t\t\t\t" . '<img class="inventory_icon" src="../product_icons/' . $product_icon . '" alt="' . $product_name . '">' .
							 "\n\t\t\t\t\t" . '<p class="inventory_amount">' . number_format($amount, 2, ".", " ") . '</p>' .
							 "\n\t\t\t\t" . '</div>';
					}
					echo "\n\t\t\t\t" . '<div class="next_prev_inv" id="next_weapon"><p class="fa fa-angle-double-down" aria-hidden="true"></p></div>' .
						 "\n\t\t\t\t" . '</div>';
					
					//ammo
					$query = "SELECT IFNULL(amount, 0), product_icon, product_name, pi.product_id, damage 
							  FROM ammo_info wi, product_info pi LEFT JOIN user_product up ON
							  pi.product_id = up.product_id AND user_id = '$user_id' WHERE pi.product_id 
							  IN (SELECT ammo_id FROM ammo_info) AND pi.product_id = ammo_id";
					$result = $conn->query($query);
					echo "\n\t\t\t\t" . '<div id="ammo_div">' .
						 "\n\t\t\t\t" . '<div class="next_prev_inv" id="previous_ammo"><p class="fa fa-angle-double-up" aria-hidden="true"></p></div>' .
						 "\n\t\t\t\t" . '<div class="ammo_info" id="0">' .
						 "\n\t\t\t\t\t" . '<p class="inventory_damage">+0D</p>' .
						 "\n\t\t\t\t\t" . '<img class="inventory_icon" src="../product_icons/0.png">' .
						 "\n\t\t\t\t\t" . '<p class="inventory_amount">0</p>' .
						 "\n\t\t\t\t" . '</div>';
					while($row = $result->fetch_row()) {
						list($amount, $product_icon, $product_name, $product_id, $damage) = $row;
						echo "\n\t\t\t\t" . '<div class="ammo_info" id="' . $product_id . '">' .
							 "\n\t\t\t\t\t" . '<p class="inventory_damage">+' . $damage . 'D</p>' .
							 "\n\t\t\t\t\t" . '<img class="inventory_icon" src="../product_icons/' . $product_icon . '" alt="' . $product_name . '">' .
							 "\n\t\t\t\t\t" . '<p class="inventory_amount">' . number_format(floor($amount), 0, "", " ") . '</p>' .
							 "\n\t\t\t\t" . '</div>';
					}
					echo "\n\t\t\t\t" . '<div class="next_prev_inv" id="next_ammo"><p class="fa fa-angle-double-down" aria-hidden="true"></p></div>' .
						 "\n\t\t\t\t" . '</div>';

					//armor
					$query = "SELECT IFNULL(amount, 0), product_icon, product_name, pi.product_id, defence 
							  FROM armor_info ai, product_info pi LEFT JOIN user_product up ON
							  pi.product_id = up.product_id AND user_id = '$user_id' WHERE pi.product_id 
							  IN (SELECT armor_id FROM armor_info) AND pi.product_id = armor_id";
					$result = $conn->query($query);
					echo "\n\t\t\t\t" . '<div id="armor_div">' .
						 "\n\t\t\t\t" . '<div class="next_prev_inv" id="previous_armor"><p class="fa fa-angle-double-up" aria-hidden="true"></p></div>' .
						 "\n\t\t\t\t" . '<div class="armor_info" id="0" armor="0">' .
						 "\n\t\t\t\t\t" . '<p class="inventory_damage">+0% G</p>' .
						 "\n\t\t\t\t\t" . '<img class="inventory_icon" src="../product_icons/0.png">' .
						 "\n\t\t\t\t\t" . '<p class="inventory_amount">0.00</p>' .
						 "\n\t\t\t\t" . '</div>';
					while($row = $result->fetch_row()) {
						list($amount, $product_icon, $product_name, $product_id, $defence) = $row;
						echo'<div class="armor_info" id="' . $product_id . '" armor="' . $defence . '"' .
							' amount="' . $amount . '">' .
								'<p class="inventory_damage">+' . $defence . '% G</p>' .
								'<img class="inventory_icon" src="../product_icons/' . $product_icon . '" alt="' . $product_name . '">' .
								'<p class="inventory_amount">' . number_format($amount, 2, ".", " ") . '</p>' .
							'</div>';
					}
					echo "\n\t\t\t\t" . '<div class="next_prev_inv" id="next_armor"><p class="fa fa-angle-double-down" aria-hidden="true"></p></div>' .
						 "\n\t\t\t\t" . '</div>';
					
					//select products available for energy recover
					echo "\n\t\t\t\t" . '<div id="food_div">' .
						 "\n\t\t\t\t" . '<div id="previous_food"><p class="fa fa-angle-double-left" aria-hidden="true"></p></div>';
					$query = "SELECT pi.product_id, IFNULL(amount, 0), product_name, product_icon
							  FROM product_info pi LEFT JOIN user_product up ON up.product_id = pi.product_id AND user_id = '$user_id'
							  WHERE purpose = 3";
					$result = mysqli_query($conn, $query);
					while($row = $result->fetch_row()) {
						list($product_id, $amount, $product_name, $product_icon) = $row;
						echo "\n\t\t\t\t" . '<div class="recovery_info" id="' . $product_id . '">' .
							 "\n\t\t\t\t\t" . '<img class="inventory_icon" src="../product_icons/' . $product_icon . '" alt="' . $product_name . '">' .
							 "\n\t\t\t\t\t" . '<p class="inventory_amount">' .  number_format($amount, 2, ".", " ") . '</p>' .
							 "\n\t\t\t\t" . '</div>';
					}
					echo "\n\t\t\t\t" . '<div id="next_food"><p class="fa fa-angle-double-right" aria-hidden="true"></p></div>' .
						 "\n\t\t\t\t" . '</div>';

					//attack/defend btn
					if($side == 'attacker') {
						$button = 'Attack';
						$auto_button = 'Auto Attack';
						$btn_color = 'rgb(155, 52, 52)';
					}
					else if ($side == 'defender') {
						$button = 'Defend';
						$auto_button = 'Auto Defend';
						$btn_color = 'rgb(51, 90, 125)';
					}
					echo "\n\t\t\t" . '<p id="fight_btn" style="background-color:' . $btn_color . ';">' . $button . '</p>';
					echo "\n\t\t\t" . '<p id="auto_fight">' . $auto_button . '</p>';
					echo "\n\t\t\t" . '<p id="battle_id" hidden>' . $battle_id . '</p>';
					echo "\n\t\t\t" . '<p id="side" hidden>' . $side . '</p>';
					echo "\n\t\t\t" . '<p id="currency_id" hidden>' . $currency_id . '</p>';
					echo "\n\t\t\t" . '<p id="regenerate_btn">Regenerate</p>';
					echo "\n\t\t\t" . '<p class="fa fa-bar-chart" aria-hidden="true" id="battle_stat"></p>';
					
					
					echo "\n\t\t\t" . '</div>';
					
					//display damage price
					if($currency_id != 0) {
						if($side == 'attacker') {
							$query = "SELECT currency_abbr, damage_price FROM
									  battle_budget bb, currency cu WHERE cu.currency_id = bb.currency_id AND battle_id = '$battle_id'
									  AND country_id = '$attacker_id' AND bb.currency_id = '$currency_id'";
						}
						else if($side == 'defender') {
							$query = "SELECT currency_abbr, damage_price FROM
									  battle_budget bb, currency cu WHERE cu.currency_id = bb.currency_id AND battle_id = '$battle_id'
									  AND country_id = '$defender_id' AND bb.currency_id = '$currency_id'";
						}
						$result_currency = $conn->query($query);
						$row_currency = $result_currency->fetch_row();
						list($currency_abbr, $damage_price) = $row_currency;
			
						echo "\n\t\t" . '<p id="damage_price">' . number_format($damage_price, 2, ".", " ") . 
										' ' . $currency_abbr . ' for 100D</p>';
					}
					if($defender_id == 1000 && ($attacked_region < 1476 || $attacked_region > 1485) && $side == 'attacker') {
						echo "\n\t\t" . '<p id="damage_price">0.001 GOLD for 1 Damage</p>';
					}

					echo "\n\t\t" . '</div>';
				}
				else {
					echo "\n\t\t" . '<p> Battle doesn\'t exists</p>';
				}
			}
		?>

	</div>
</main>

<?php include('footer.php'); ?>