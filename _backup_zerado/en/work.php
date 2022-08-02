<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Work</p>
		
		<p id="work_all" class="button blue">Work All</p>
		<input id="uncheck_box" name="uncheck_box" type="checkbox" checked>
		<label for="uncheck_box" id="uncheck_box">Select All</label>
		
		
		<input id="runcheck_box" name="runcheck_box" type="checkbox">
		<label for="runcheck_box" id="runcheck_box">Select All</label>
		<p id="retire_all" class="button red">Retire All</p>
		
		<div id="headings">
			<p id="person_head">Person</p>
			<p id="employer_head">Employer</p>
			<p id="company_head">Company Details</p>			
		</div>
		<?php
			//select day count
			$query = "SELECT day_number, date FROM day_count
					  WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) ORDER BY day_number";
			$result = $conn->query($query);
			$start_day = strtotime(correctDate(date('Y-m-d'), date('H:i:s')) . "00:00:00 - 6 day");
			
			$day_count_info = array();
			$x = 0;
			while($row = $result->fetch_row()) {
				list($day_number, $date) = $row;
				
				//make sure to display right day number
				$user_date = correctDate($date, date('H:i:s'));
				$user_date_start = strtotime("$user_date 00:00:00");
				
				//if selects 'old' data
				if($user_date_start < $start_day) {
					continue;
				}
				
				if(strtotime($user_date) > strtotime($date)) {
					$day_number++;
				}
				else if(strtotime($user_date) < strtotime($date)) {
					$day_number--;
				}
				
				$day_count_info[$x]['day_number'] = $day_number;
				$day_count_info[$x]['user_date_start'] = $user_date_start;
				$x++;
			}
			//extra day to compare with
			$day_count_info[$x]['user_date_start'] = strtotime("$user_date 00:00:00 + 1 day");	
			
			$query = "SELECT person_id, experience, p.energy, person_name, years, worked, combat_exp, ec.energy, worked 
					  FROM people p, energy_consumption ec 
					  WHERE user_id = '$user_id' AND p.person_id IN
					 (SELECT person_id FROM hired_workers) AND ec.cons_id = 5";
			$result_person = $conn->query($query);
			while($row_person = $result_person->fetch_row()) {
				//display person info
				list($person_id, $experience, $energy, $person_name, $years, $work_status, $combat_exp, $max_energy, 
					 $worked) = $row_person;
				
				//get skill lvl
				$query_skill = "SELECT MAX(skill_lvl) FROM experience WHERE required_exp <= '$experience'";
				$result_skill = $conn->query($query_skill);
				$row_skill = $result_skill->fetch_row();
				list($skill) = $row_skill;
				
				$energy_width = ($energy/$max_energy) * 100;
				
				if($worked == TRUE) {
					$worked_color_class = 'person_worked';
					$work_det = "Worked today";
				}
				else {
					$worked_color_class = 'person_can_work';
					$work_det = "Did not worked today";
				}
				
				echo "\n\t\t" . '<div class="person_job">' .
					 "\n\t\t\t" . '<div class="person" id="p_' . $person_id . '">';
				if($work_status == false) {
					echo "\n\t\t\t\t" . '<input class="people_id_check" type="checkbox" value="' . $person_id . '" checked>';
				}					 
				echo "\n\t\t\t\t" . '<p class="person_name">' . $person_name . '</p>' .
					 "\n\t\t\t\t" . '<span class="glyphicon glyphicon-user person_icon"></span>' .
					 "\n\t\t\t\t" . '<abbr title="Years"><p class="person_years">' . $years . '</p></abbr>' .
					  "\n\t\t\t\t" . '<abbr title="' . $work_det . '"><p class="person_worked_stat ' . $worked_color_class . 
									'"><i class="fa fa-briefcase" aria-hidden="true"></i></p></abbr>' .
					 "\n\t\t\t\t" . '<abbr title="Combat experience"><p class="person_combat_exp">' . $combat_exp . 
									'</p></abbr>' .
					 "\n\t\t\t\t" . '<abbr title="Work experience"><p class="person_experience">' . $skill . '</p></abbr>' .
					 "\n\t\t\t\t" . '<div class="bar">' .
					 "\n\t\t\t\t\t" . '<div class="progress" style="width:' . $energy_width . '%;"></div>' .
					 "\n\t\t\t\t\t" . '<p>' . $energy . '/' . $max_energy . '</p> '. 
					 "\n\t\t\t\t" . '</div>';
				
				if($work_status == false) {
					echo "\n\t\t\t\t" . '<p class="work">Work</p>';
				}
				else if($work_status == true) {
					echo "\n\t\t\t\t" . '<p class="worked">Worked</p>';
				}
				echo "\n\t\t\t\t" . '<p class="person_id" hidden>' . $person_id . '</p>' .
					 "\n\t\t\t" . '</div>' . "\n";
				
				//display job info
				$query = "SELECT user_name, u.user_id, company_name, c.company_id, region_name, salary, time_hired, building_icon, currency_abbr,
						  region_id, up.user_image
						  FROM users u, companies c, hired_workers hw, user_building ub, regions r, building_info bi, country co,
						  currency cu, user_profile up
						  WHERE u.user_id = ub.user_id AND c.company_id = hw.company_id AND ub.company_id = c.company_id 
						  AND r.region_id = location AND bi.building_id = c.building_id AND person_id = '$person_id'
						  AND co.country_id = r.country_id AND cu.currency_id = co.currency_id
						  AND up.user_id = u.user_id
						  UNION
						  SELECT user_name, u.user_id, company_name, c.company_id, region_name, salary, time_hired, building_icon, currency_abbr,
						  region_id, up.user_image
						  FROM users u, companies c, hired_workers hw, corporation_building cb,
						  regions r, building_info bi, country co,
						  currency cu, user_profile up, corporations corp
						  WHERE u.user_id = manager_id AND c.company_id = cb.company_id AND cb.corporation_id = corp.corporation_id
						  AND c.company_id = hw.company_id  
						  AND r.region_id = location AND bi.building_id = c.building_id AND person_id = '$person_id'
						  AND co.country_id = r.country_id AND cu.currency_id = co.currency_id
						  AND up.user_id = u.user_id";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($user_name, $user_id, $company_name, $company_id, $region_name, $salary, $time_hired, $building_icon, $currency_abbr,
					 $region_id, $user_img) = $row;
				//cut user_name if too long
				if(iconv_strlen($user_name) > 10) {
					$user_name = substr($user_name, 0,10);
				}
				
				echo "\n\t\t\t" . '<div class="job">' .
					 "\n\t\t\t\t" . '<a href="user_profile?id=' . $user_id . '" class="employer_name">' . $user_name . '</a>' .
					 "\n\t\t\t\t" . '<img src="../user_images/' . $user_img . '" class="employer_img">' .
					 "\n\t\t\t\t" . '<a class="company_name" href="company_manage?id=' . $company_id . '">' . $company_name . '</a>' .
					 "\n\t\t\t\t" . '<img src="../building_icons/' . $building_icon . '" class="company_img">' .
					 "\n\t\t\t\t" . '<p class="company_region_name">Location: <a href="region_info?region_id=' . $region_id .
									'">' . $region_name . '</a></p>' .
					 "\n\t\t\t\t" . '<p class="salary">' . $salary . ' ' . $currency_abbr . '</p>' .
					 "\n\t\t\t\t" . '<p class="days_hired">Days Hired: ' . $time_hired . '</p>' .
					 "\n\t\t\t\t" . '<input class="r_people_id_check" type="checkbox" value="' . $person_id . '">' .
					 "\n\t\t\t\t" . '<p class="button red retire">Retire</p>' .
					 "\n\t\t\t\t" . '<p class="person_id" hidden>' . $person_id . '</p>' .
					 "\n\t\t\t\t" . '<div class="production_div">';

				//display production info
				$query = "SELECT produced, date, time FROM work_journal WHERE person_id = '$person_id' 
						  AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) ORDER BY date ASC, time ASC";
				$result = $conn->query($query);
				$x = -1;

				while($row = $result->fetch_row()) {
					list($produced, $date, $time) = $row;
					$date = correctDate($date, $time);
					$time = correctTime($time); 
					
					$user_work_date_time = strtotime("$date $time - 4 hour");
					
					//if selects 'old' data
					if($user_work_date_time < $start_day) {
						continue;
					}
					
					$next_day = false;
					$flag = true;
 					
					while($flag) {
						if($user_work_date_time >= $day_count_info[$x+1]['user_date_start']
						   && $user_work_date_time < $day_count_info[$x+2]['user_date_start']) {
							$next_day = true;
							$flag = false;
							$x++;
						}
						else if($user_work_date_time >= $day_count_info[$x]['user_date_start']
								&& $user_work_date_time < $day_count_info[$x+1]['user_date_start']){
							$next_day = false;
							$flag = false;
						}
						else {
							$x++;
							$clas = "fa fa-user-o";
							$worker_summary_class = "worker_missed";
							$production = "n/w";
							$echo_date = date('Y-m-d', $day_count_info[$x]['user_date_start']);
							$echo_time = "";
							$day_number = $day_count_info[$x]['day_number'];
							displayInfo();
						}
					}

					if($next_day) {
						$clas = "fa fa-user";
						$worker_summary_class = "worker_worked";
						$production = $produced;
						$echo_date = $date;
						$echo_time = $time;
						$day_number = $day_count_info[$x]['day_number'];
						displayInfo();
					}
				}
				echo "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>' .	
					 "\n\t\t" . '</div>' . "\n";
			}
			
			function displayInfo() {
				global $clas;
				global $worker_summary_class;
				global $production;
				global $echo_date;
				global $echo_time;
				global $day_number;
				echo "\n\t\t\t\t\t" . '<div class="' . $worker_summary_class . '">' .
					 "\n\t\t\t\t\t\t" . '<abbr title="Day ' . $day_number .' ' . $echo_date . ' ' . $echo_time . '">
										 <span class="' . $clas . '"></span></abbr>' .
					 "\n\t\t\t\t\t\t" . '<abbr title="produced"><p>' . $production . '</p></abbr>' .
					 "\n\t\t\t\t\t" . '</div>';
			}
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>