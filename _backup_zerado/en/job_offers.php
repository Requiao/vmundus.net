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
		<p id="skill">Skill:</p>
		<input id="skill_input" value="1" maxlength="2">
		<p class="button blue" id="search_job">Search</p>
			<?php
				$query = "SELECT country_name, flag, country_id FROM country WHERE country_id = (SELECT citizenship FROM user_profile
						  WHERE user_id = '$user_id')";
				$result = mysqli_query($conn, $query);
				$row = mysqli_fetch_row($result);
				list($country, $flag, $user_country_id) = $row;
				
				echo "\n\t\t" . '<div id="country_list">' .
					 "\n\t\t\t" . '<div id="country">' . 
					 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<p>' . $country . '</p>' . 
					 "\n\t\t\t" . '</div>' . 
					 "\n\t\t\t" . '<p id="get_country_id" hidden>' . $user_country_id . '</p>' .  
					 "\n\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
					 "\n\t\t" . '</div>' .
					 "\n\t\t" . '<div id="countries_div">';
				
				$query = "SELECT country_name, flag, country_id FROM country ORDER BY country_name";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($country, $flag, $country_id) = $row;
					echo "\n\t\t\t" . '<div class="country" id="' . $country_id . '">' . 
						 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
						 "\n\t\t\t\t" . '<p>' . $country . '</p>' . 
						 "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
			
				//regions section
				echo "\n\t\t" . '<div id="region_list">' .
					 "\n\t\t\t" . '<div class="region">' . 
					 "\n\t\t\t\t" . '<p>All</p>' . 
					 "\n\t\t\t" . '</div>' . 
					 "\n\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
					 "\n\t\t" . '</div>' .
					 "\n\t\t\t" . '<div id="regions_div">';
				
				$query = "SELECT region_id, region_name FROM regions WHERE country_id = (SELECT citizenship FROM user_profile
						  WHERE user_id = '$user_id') ORDER BY region_name";
				$result = mysqli_query($conn, $query);
				
				echo "\n\t\t\t\t" . '<div class="region" id="0">' . 
						 "\n\t\t\t\t\t" . '<p id="' . $user_country_id . '">All</p>' . 
						 "\n\t\t\t\t" . '</div>';
						 
				while($row = mysqli_fetch_row($result)) {
					list($region_id, $region_name) = $row;
					echo "\n\t\t\t\t" . '<div class="region" id="' . $region_id . '">' . 
						 "\n\t\t\t\t\t" . '<p>' . $region_name . '</p>' . 
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
				
				echo "\n\t\t" . '<p class="button green" id="my_offers">My Offers</p>';
				
				/* job offers */
				echo '<div id="job_offers_div">' .
					 '<p id="company_name_head">Company Name</p>' .
					 '<p id="location_head">Location</p>' .
					 '<p id="skill_head">Skill</p>' .
					 '<p id="salary_head">Salary</p>';
				
				$query = "SELECT job_id, user_name, u.user_id, c.company_id, region_name, company_name, building_icon, 
						  salary, skill_lvl, currency_abbr, up.user_image, r.region_id
						  FROM users u, user_building ub, companies c, building_info bi, job_market jm, regions r, country co, 
						  currency cu, user_profile up
						  WHERE u.user_id = ub.user_id AND ub.company_id = c.company_id AND bi.building_id = c.building_id 
						  AND jm.company_id = c.company_id AND location = region_id AND r.country_id = 
						 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND co.country_id = r.country_id 
						  AND co.currency_id = cu.currency_id AND up.user_id = u.user_id
						  UNION
						  SELECT job_id, user_name, u.user_id, c.company_id, region_name, company_name, building_icon, 
						  salary, skill_lvl, currency_abbr, up.user_image, r.region_id
						  FROM users u, corporation_building cb, companies c, building_info bi, job_market jm, regions r, country co, 
						  currency cu, user_profile up, corporations corp
						  WHERE u.user_id = manager_id AND cb.company_id = c.company_id AND corp.corporation_id = cb.corporation_id
						  AND bi.building_id = c.building_id 
						  AND jm.company_id = c.company_id AND location = region_id AND r.country_id = 
						 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND co.country_id = r.country_id 
						  AND co.currency_id = cu.currency_id AND up.user_id = u.user_id
						  ORDER BY skill_lvl, salary DESC LIMIT 100";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($job_id, $user_name, $user_id, $company_id, $region_name, $company_name, $building_icon, 
						 $salary, $bonus, $currency_abbr, $user_image, $region_id) = $row;
						 
					$user_name = cutLongName($user_name, 7);
					
					echo '<div class="job">' .
						 '<a href="user_profile?id=' . $user_id . '" class="employer_name">' . $user_name . '</a>' . 
						 '<img src="../user_images/' . $user_image . '" class="user_img">' .
						 '<p class="company_name">' . $company_name . '</p>' .
						 '<img src="../building_icons/' . $building_icon . '" class="company_img">' .
						 '<a class="region_name" href="region_info?region_id=' . $region_id . '">' . $region_name . '</a>' .
						 '<p class="bonus">' . $bonus . '</p>' .
						 '<p class="salary">' . $salary . ' ' . $currency_abbr . '</p>' .
						 '<p class="button blue apply" id="' . $job_id . '">Apply</p>' . 
						 '</div>';
				}
				echo '</div>';
			?>

	</div>
</main>

<?php include('footer.php'); ?>