<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php	
			echo "\n\t" . '<p id="page_head">' . $lang['people'] . '</p>';
		
			$query = "SELECT born_bar FROM user_born_bar WHERE user_id = '$user_id'";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_row($result);
			list($born_bar) = $row;
			$available_people = floor($born_bar);
			$width = (($born_bar - $available_people) * 100);
			if($width >= 50 && $width < 75) {
				$color = 'rgb(223, 163, 58)';
			}
			elseif($width >= 75) {
				$color = 'rgb(128, 182, 109)';
			}
			else {
				$color = 'rgb(246, 120, 74)';
			}
				
			echo "\n\t\t" . '<div id="born_bar_div"><div id="progress" style="width: ' . $width . '%; 
							 background-color: ' . $color . ';"></div><p>Cloning progress: ' . 
							 ($born_bar - $available_people) . '</p></div>';
			
			//available clones
			echo "\n\t\t" . '<p id="available_people" class="sub_title">Buy more slots for ' . $available_people . ' clones.</p>';
			
			//regenerate div
			echo "\n\t\t" . '<p id="rec_head" class="action_all_heads">' . $lang['recover_energy_for_all_people'] . '</p>';

			//build cloning farm, buy clones
			echo "\n\t\t" . '<div id="buy_build_buttons">' .
				 "\n\t\t\t" . '<p id="build_clone_farm">Build cloning farm</p>' .
				 "\n\t\t\t" . '<p id="buy_clones">Buy clones</p>' .
				 "\n\t\t" . '</div>';

			echo "\n\t\t" . '<div id="recover_energy_div">' .
				 "\n\t\t\t" . '<p id="rec_reply"></p>';
			$purpose = 3; // is for food.
			$RECOVERY = 10;//do not change
			$query = "SELECT up.product_id, amount, product_name, product_icon 
					  FROM user_product up, product_info pi 
					  WHERE user_id = '$user_id' AND purpose = '$purpose'
					  AND up.product_id = pi.product_id";
			$result = mysqli_query($conn, $query);
			while($row = mysqli_fetch_row($result)) {
				list($product_id, $amount, $product_name, $product_icon) = $row;
				echo "\n\t\t\t" . '<div class="recover_all_info">' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
								  $product_icon . '" alt="product icon"></abbr>' .
					 "\n\t\t\t\t" . '<p class="recovery">+' . $RECOVERY . ' <i class="glyphicon glyphicon-flash"></i></p>' .
					 "\n\t\t\t\t" . '<p class="amount">' . number_format($amount, 2, ".", " ") . '</p>' .
					 "\n\t\t\t\t" . '<input type="number" class="quantity" min="1" max="10" value="1">' .
					 "\n\t\t\t\t" . '<p class="recover_all">' . $lang['recover'] . '</p>' .
					 "\n\t\t\t\t" . '<p hidden>' . $product_id . '</p>' .
					 "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t\t" . '<input class="select_all" id="uncheck_box" name="uncheck_box" type="checkbox" checked>' .
				 "\n\t\t\t" . '<label class="select_all" for="uncheck_box" id="uncheck_box">' . $lang['select_all'] . '</label>' .
				 "\n\t\t" . '</div>';
			
			//travel
			$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($country_id) = $row;
			
			
			//civil
				 "\n\t\t\t\t\t" . '<p>' . $lang['select_country'] . '</p>' .
				 "\n\t\t\t\t" . '</div>' .
				 "\n\t\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' .
				 "\n\t\t\t" . '</div>' .
			$query = "SELECT country_id, country_name, flag FROM country 
					  WHERE country_id IN (SELECT country_id FROM travel_agreement WHERE permission = TRUE 
					  AND from_country_id = '$country_id') OR country_id = '$country_id' 
					  OR country_id IN (SELECT country_id FROM regions WHERE country_id != rightful_owner 
					  AND rightful_owner = '$country_id')
					  ORDER BY country_name";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($to_country_id, $country_name, $flag) = $row;
					 "\n\t\t\t\t\t" .  '<img src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t\t" . '<p>' . $country_name . '</p>' .
					 "\n\t\t\t\t" . '</div>';
			}
				 
			//total people
			$query = "SELECT COUNT(*) FROM people WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total_people) = $row;
			
			//echo "\n\t\t" . '<p id="total_people">' . $lang['total_people'] . ': ' . $total_people . '/' . $tootal_room . '</p>';
			
			//display cloning farms
			echo "\n\t\t" . '<div class="info_blocks" id="cloning_farms">' .
				 "\n\t\t\t" . '<p class="heads">Cloning farms</p>';

			$query = "SELECT building_icon, name, days_left, growth
					  FROM building_info bi, cloning_farms_born_bar cfbb, users_cloning_farms ucf
					  WHERE bi.building_id = ucf.building_id AND cfbb.building_id = ucf.building_id
					  AND user_id = '$user_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($building_icon, $name, $days_left, $growth) = $row;
				
				echo "\n\t\t\t" . '<div class="cf_farms_div">' .
					 "\n\t\t\t\t" . '<img class="cf_building_icon" src="../building_icons/' . $building_icon . '" 
									 alt="' . $name . '">' .
					 "\n\t\t\t\t" . '<p class="cf_bonus">+' . $growth . ' BB<p>' .
					 "\n\t\t\t\t" . '<p class="cf_days_left">' . $days_left . ' days left</p>' .
					 "\n\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			//display people
			echo "\n\t\t" . '<div class="info_blocks" id="people_div">' .
				 "\n\t\t\t" . '<p class="heads">People</p>';

			//get total people
			$query = "SELECT COUNT(*) FROM people WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($total_people) = $row;

			echo "\n\t\t\t" . '<div id="total_people_div">' .
				 "\n\t\t\t\t" . '<p id="tpd_label">Total people:' .
				 "\n\t\t\t\t" . '<span id="tpd_total_people">' . $total_people . '</span></p>' .
				 "\n\t\t\t" . '</div>';

			//get user slot num and max available slot
			$query = "SELECT MAX(psc.slot_number), ups.slot_number 
					FROM people_slot_cost psc, user_people_slots ups WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($max_slot_number, $user_slot_number) = $row;
			if($user_slot_number < $max_slot_number) {
				$query = "SELECT price FROM people_slot_cost WHERE slot_number = 
						 (SELECT slot_number FROM user_people_slots WHERE user_id = '$user_id') + 1";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($slot_price) = $row;

				echo "\n\t\t\t" . '<div id="buy_new_slot_div">' .
					 "\n\t\t\t\t" . '<p id="bnsd_plus">+</p>' .
					 "\n\t\t\t\t" . '<p id="bnsd_label">Buy new slot</p>' .
					 "\n\t\t\t\t" . '<div id="bnsd_slot_price_div">' .
					 "\n\t\t\t\t\t" . '<p id="bnsd_slot_price">' . $slot_price . '</p>' .
					 "\n\t\t\t\t\t" . '<img id="bnsd_gold_img" src="../img/gold.png">' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
			}

			$query = "SELECT (slot_number - (SELECT COUNT(person_id) FROM people WHERE user_id = '$user_id')) AS available_slots 
					  FROM user_people_slots WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($available_slots) = $row;
			for($x = 0; $x < $available_slots && $available_slots > 0; $x++) {
				echo "\n\t\t\t" . '<div class="available_slot_div">' .
					 "\n\t\t\t\t" . '<p class="asd_label">Available</p>' .
					 "\n\t\t\t" . '</div>';
			}

			$query = "SELECT person_id, experience, p.energy, person_name, years, who, combat_exp, en.energy, worked
					  FROM people p, energy_consumption en WHERE en.cons_id = 5
					  AND user_id = '$user_id' ORDER BY years ASC";
			$result_person = mysqli_query($conn, $query);
			while($row_person = mysqli_fetch_row($result_person)) {
				list($person_id, $experience, $energy, $person_name, $years, $status, $combat_exp, $max_energy, $worked) = $row_person;
				
				//get work_lvl
				$query_skill = "SELECT MAX(skill_lvl) FROM experience WHERE required_exp <= '$experience'";
				$result_skill = mysqli_query($conn, $query_skill);
				$row_skill = mysqli_fetch_row($result_skill);
				list($skill_lvl) = $row_skill;

				$query_skill = "SELECT MAX(required_exp) FROM experience WHERE skill_lvl = '$skill_lvl' + 1";
				$result_skill = mysqli_query($conn, $query_skill);
				$row_skill = mysqli_fetch_row($result_skill);
				list($next_lvl_req_exp) = $row_skill;

				$work_lvl_width = ($experience / $next_lvl_req_exp) * 100;
				$energy_width = ($energy/$max_energy) * 100;
				
				if($worked == TRUE) {
					$worked_color_class = 'person_worked';
					$work_det = $lang['worked_today'];
				}
				else {
					$worked_color_class = 'person_can_work';
					$work_det = $lang['did_not_worked_today'];
				}
				
				echo "\n\t\t\t" . '<div class="about_persons" id="person_' . $person_id . '">' .
						"\n\t\t\t\t" . '<p class="get_person_id" hidden>' . $person_id . '</p>' .
						"\n\t\t\t\t" . '<input class="people_id_check" type="checkbox" value="' . $person_id . '" checked>' .
						"\n\t\t\t\t" . '<p class="person_name">' . $person_name . '</p>' .
						"\n\t\t\t\t" . '<span class="fa fa-user person_icon"></span>' .
						"\n\t\t\t\t" . '<abbr title="' . $lang['years'] . '"><p class="person_years">' . $years . '</p></abbr>' .
						"\n\t\t\t\t" . '<abbr title="' . $work_det . '"><p class="person_worked_stat ' . $worked_color_class . 
									'"><i class="fa fa-briefcase" aria-hidden="true"></i></p></abbr>' .
						"\n\t\t\t\t" . '<div class="bar">' .
						"\n\t\t\t\t\t" . '<div class="work_lvl_progress_bar" style="width:' . $work_lvl_width . '%;"></div>' .
						"\n\t\t\t\t\t" . '<p>Work level: ' . $skill_lvl . '</p> '. 
						"\n\t\t\t\t" . '</div>' .
						"\n\t\t\t\t" . '<abbr title="' . $lang['combat_experience'] . '"><p class="person_combat_exp">Combat exp: ' . $combat_exp . 
									'</p></abbr>' .
						"\n\t\t\t\t" . '<div class="bar">' .
						"\n\t\t\t\t\t" . '<div class="energy_progress_bar" style="width:' . $energy_width . '%;"></div>' .
						"\n\t\t\t\t\t" . '<p>Energy: ' . $energy . '/' . $max_energy . '</p> '. 
						"\n\t\t\t\t" . '</div>' .
						"\n\t\t\t\t" . '<p class="person_status ' . $status . '">' . $status . '</p>' .
						"\n\t\t\t\t" . '<p class="details_btn">' . $lang['details'] . '</p>' .
						"\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>