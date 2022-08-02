<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Battles Statistics</p>
		<?php
			$query = "SELECT b.battle_id, ca.country_name, attacker_id, ca.flag, cd.country_name, defender_id, cd.flag, 
					  region_name, attacker_damage, defender_damage, type,
					  attacker_strength, rap.strength AS 'attacker_fixed_strength', defender_strength,
					  dci.strength AS' defender_fixed_strength', date_started, time_started, b.region_id,
					  date_ended, time_ended, winner_id, bp1.costs AS attacker_costs, bp2.costs AS defender_costs,
					  IFNULL(b.user_attacker_id, 0), IFNULL(user_name, 'n/a')
					  FROM country ca, country cd, regions r, region_attack_platform rap, def_const_info dci, battles b LEFT JOIN
					 (SELECT battle_id, for_country_id, SUM(amount * price)/(SELECT price FROM product_price WHERE product_id = 1) AS costs
					  FROM user_battle_uses ubu, product_price pp
					  WHERE ubu.product_id = pp.product_id
					  GROUP BY battle_id, for_country_id) AS bp1 ON bp1.battle_id = b.battle_id AND bp1.for_country_id = attacker_id LEFT JOIN
					 (SELECT battle_id, for_country_id, SUM(amount * price)/(SELECT price FROM product_price WHERE product_id = 1) AS costs
					  FROM user_battle_uses ubu, product_price pp
					  WHERE ubu.product_id = pp.product_id
					  GROUP BY battle_id, for_country_id) AS bp2 ON bp2.battle_id = b.battle_id AND bp2.for_country_id = defender_id
					  LEFT JOIN users u ON u.user_id = b.user_attacker_id
					  WHERE ca.country_id = attacker_id AND cd.country_id = defender_id AND r.region_id = b.region_id
					  AND active = FALSE AND rap.platform_id = b.attack_platform_id AND dci.def_loc_id = b.def_loc_id 
					  ORDER BY date_ended DESC, time_ended DESC
					  LIMIT 100";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($battle_id, $attacker_country_name, $attacker_id, $attacker_flag, $defender_country_name, $defender_id, 
					 $defender_flag, $region_name, $attacker_damage, $defender_damage, $type, $attacker_strength, 
					 $attacker_fixed_strength, $defender_strength, 
					 $defender_fixed_strength, $date_started, $time_started, $region_id,
					 $date_ended, $time_ended, $winner_id, $attacker_costs, $defender_costs,
					 $user_attacker_id, $user_attacker_name) = $row;
				
				$battle_type = ($type=='regular')?'Regular Battle':'Resistance Battle';
				
				$date = correctDate($date_started, $time_started);
				$time = correctTime($time_started);
				$battle_started_date = date('M j', strtotime($date_started)) . ' ' . $time_started;
				
				$date = correctDate($date_ended, $time_ended);
				$time = correctTime($time_ended);
				$battle_ended_date = date('M j', strtotime($date_ended)) . ' ' . $time_ended;
				
				//battle duration	
				$date1 = new DateTime($date_ended . ' ' . $time_ended);
				$date2 = new DateTime($date_started . ' ' . $time_started);
				$diff = date_diff($date1,$date2);
				$days_duration = $diff->format("%a");
				$time_duration = $diff->format("%H:%I:%S");
				$battle_duration = "$days_duration days $time_duration";
				
				//get wiiner name
				if($winner_id == $attacker_id) {
					$winner_name = $attacker_country_name;
				}
				else {
					$winner_name = $defender_country_name;
				}
				
				echo "\n\t\t" . '<div class="battle_info_div">' .
					 "\n\t\t\t" . '<p class="attacker_head">Attacker</p>' .
					 "\n\t\t\t" . '<p class="region_name_head">Region</p>' .
					 "\n\t\t\t" . '<p class="defender_head">Defender</p>' .
					 "\n\t\t\t" . '<a class="attacker_name" href="country?country_id=' . $attacker_id . '">' . 
								   $attacker_country_name . '</a>' .
					 "\n\t\t\t" . '<p class="battle_type">' . $battle_type . '</p>' .
					 "\n\t\t\t" . '<a class="defender_name" href="country?country_id=' . $defender_id . '">' . 
								   $defender_country_name . '</a>' .
					 "\n\t\t\t" . '<img class="attacker_flag" src="../country_flags/' . $attacker_flag . '" alt="' . $attacker_country_name . '">' .
					 "\n\t\t\t" . '<a class="region_name" href="region_info?region_id=' . $region_id . '">' .
								   $region_name . '</a>' .
					 "\n\t\t\t" . '<img class="defender_flag" src="../country_flags/' . $defender_flag . '" alt="' . $defender_flag . '">' .
					 "\n\t\t\t" . '<p class="battle_costs_head">Costs of equipment</p>' .
					 "\n\t\t\t" . '<p class="attacker_costs">' .  number_format($attacker_costs, 3, ".", " ") . ' Gold</p>' .
					 "\n\t\t\t" . '<p class="defender_costs">' .  number_format($defender_costs, 3, ".", " ") . ' Gold</p>' .
					 "\n\t\t\t" . '<p class="damage_made_head">Damage made</p>' .
					 "\n\t\t\t" . '<p class="attacker_damage">' .  number_format($attacker_damage, 2, ".", " ") . '</p>' .
					 "\n\t\t\t" . '<p class="defender_damage">' .  number_format($defender_damage, 2, ".", " ") . '</p>' .
					 "\n\t\t\t" . '<p class="platform_head">Platform strength</p>' .
					 "\n\t\t\t" . '<p class="attacker_strength">' .  number_format($attacker_fixed_strength, 2, ".", " ") . '</p>' .
					 "\n\t\t\t" . '<p class="defender_strength">' .  number_format($defender_fixed_strength, 2, ".", " ") . '</p>' .
					 "\n\t\t\t" . '<p class="battle_started_head">Battle started on</p>' .
					 "\n\t\t\t" . '<p class="battle_started_date">' .  $battle_started_date . '</p>' .
					 "\n\t\t\t" . '<p class="battle_duration_head">Battle lasted</p>' .
					 "\n\t\t\t" . '<p class="battle_duration_time">' .  $battle_duration . '</p>' .
					 "\n\t\t\t" . '<p class="battle_ended_head">Battle ended on</p>' .
					 "\n\t\t\t" . '<p class="battle_ended_date">' .  $battle_ended_date . '</p>' .
					 "\n\t\t\t" . '<p class="winner_head">Winner</p>' .
					 "\n\t\t\t" . '<a class="winner_name" href="country?country_id=' . $winner_id . '">' . 
								   $winner_name . '</a>' .
					 "\n\t\t\t" . '<p class="winner_head">Battle started by </p>' .
					 "\n\t\t\t" . '<a class="winner_name" href="user_profile?id=' . $user_attacker_id . '">' . 
								   $user_attacker_name . '</a>' .
					 "\n\t\t\t" . '<a class="battle_btn" href="battle?currency_id=0&battle_id=' . $battle_id . '&side=attacker"' .
								  '>View battle</a>' .
					 "\n\t\t" . '</div>'; 
			}
		?>

	</div>
</main>

<?php include('footer.php'); ?>