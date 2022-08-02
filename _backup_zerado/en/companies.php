<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head"><?php echo $lang['companies']; ?></p>
		
		<p id="create_company"><?php echo $lang['create_company']; ?></p>

		<select id="sort">
			<option value="1"><?php echo $lang['default']; ?></option>
			<option value="2"><?php echo $lang['company_name']; ?></option>
			<option value="3"><?php echo $lang['product_name']; ?></option>
			<option value="4"><?php echo $lang['region_name']; ?></option>
			<option value="5"><?php echo $lang['country_name']; ?></option>
			<option value="6"><?php echo $lang['storage_fill']; ?></option>
			<option value="7"><?php echo $lang['resources_fill']; ?></option>
		</select>
		<p id="sort_p"><?php echo $lang['sort_by']; ?>:</p>
		
		<p id="collect_products"><?php echo $lang['collect_all_products']; ?></p>
		
		<abbr title="Invest resources in all companies that are required for each worker in the company">
			<p id="invest_resources">Invest resources</p>
		</abbr>
		
		<?php
			//expected productivity
			//get user level bonus
			/*$query_bonus = "SELECT IFNULL(level_id, 0) * work_bonus FROM bonus_per_user_level bpul, user_profile up 
							LEFT JOIN user_exp_levels uxl 
							ON uxl.experience <= up.experience WHERE up.user_id = '$user_id'
							ORDER BY level_id DESC LIMIT 1";
			$result_bonus = $conn->query($query_bonus);
			$row_bonus = $result_bonus->fetch_row();
			list($user_level_bonus) = $row_bonus;
			$user_level_bonus = $user_level_bonus / 100;
			
			//display productivity
			echo "\n\t\t" . '<div class="expected_productivity_div">' .
				 "\n\t\t\t" . '<p class="epd_title">Expected productivity</p>';
			$query = "SELECT ppt.country_id, cou.country_abbr, cou.country_name, pi.product_id, product_name, product_icon,
					  SUM(production) AS total_production, production AS base_production, tax AS production_tax
					  FROM companies c, user_building ub, product_production pp, product_info pi, regions r, product_production_tax ppt,
					  building_info bi, country cou, hired_workers hw
					  WHERE ub.user_id = '$user_id' AND c.company_id = ub.company_id AND bi.building_id = c.building_id
					  AND pp.product_id = bi.product_id AND pi.product_id = bi.product_id AND r.region_id = location
					  AND ppt.country_id = r.country_id AND ppt.product_id = bi.product_id
					  AND cou.country_id = r.country_id AND ub.company_id = hw.company_id
					  GROUP BY bi.product_id, ppt.country_id
					  ORDER BY product_id, country_id";
			$result_productivity = $conn->query($query);
			while($row_productivity = $result_productivity->fetch_row()) {
				list($country_id, $country_abbr, $country_name, $product_id, $product_name, $product_icon,
					 $total_production, $base_production, $production_tax) = $row_productivity;
				
				$production_tax = $production_tax / 100;
				
				//get country productivity bonus
				$query_bonus = "SELECT IFNULL(SUM(bonus), 0) FROM region_resource_bonus
								WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
								AND product_id = '$product_id'";
				$result_bonus = $conn->query($query_bonus);
				$row_bonus = $result_bonus->fetch_row();
				list($country_bonus) = $row_bonus;
				if($country_bonus > 50) {
					$country_bonus = 50;
				}
				$country_bonus = round($country_bonus / 100);
				
				$total_productivity = ($total_production * $country_bonus) + ($total_production * $user_level_bonus) +
									   $total_production - ($total_production * $production_tax);
									   
				echo "\n\t\t\t" . '<div class="icon_amount">' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
					 "\n\t\t\t\t\t" . '<img class="product_icon" src="../product_icons/' . $product_icon . 
									  '" alt="' . $product_name . '">' .
					 "\n\t\t\t\t" . '</abbr>' .
					 "\n\t\t\t\t" . '<p class="amount">' . number_format($total_productivity, 2, '.', ' ') . '</p>' .
					 "\n\t\t\t\t" . '<abbr title="' . $country_name . '">' .
					 "\n\t\t\t\t\t" . '<a class="epd_country" href="country?country_id=' . $country_id . '">' . $country_abbr . '</a>' .
					 "\n\t\t\t\t" . '</abbr>' .
					 "\n\t\t\t" . '</div>';
			}
			
			
			echo "\n\t\t\t" . '<p class="epd_note">This productivity is based on the currently employed workers and' .
							  ' country productivity taxes. User level bonus and country bonuses are being added' .
							  ' to the expected productivity. Persons\' level bonus are not calculated.' .
							  ' Please, note that this is a demo version. Some data might not be accurate..' .
				 "\n\t\t" . '</div>';
			*/	 
			/* 
			//required products for production	
			echo "\n\t\t" . '<div class="expected_productivity_div">' .
				 "\n\t\t\t" . '<p class="epd_title">Required products for production</p>';
			$query = "SELECT required_id, product_name, product_icon, SUM(amount) AS total_required, cou.country_id, country_name, country_abbr 
					  FROM product_product pp, product_info pi, hired_workers hw, companies c, regions r, country cou
					  WHERE pp.building_id = c.building_id AND pi.product_id = required_id
					  AND c.company_id IN (SELECT company_id FROM user_building WHERE user_id = '$user_id')
					  AND hw.company_id = c.company_id AND r.region_id = c.location AND cou.country_id = r.country_id
					  GROUP BY required_id, cou.country_id
					  ORDER BY product_id, country_id";
			$result_productivity = $conn->query($query);
			while($row_productivity = $result_productivity->fetch_row()) {
				list($required_id, $product_name, $product_icon, $total_required, $country_id, $country_name, 
					 $country_abbr ) = $row_productivity;	
				
				echo "\n\t\t\t" . '<div class="icon_amount">' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
					 "\n\t\t\t\t\t" . '<img class="product_icon" src="../product_icons/' . $product_icon . 
									  '" alt="' . $product_name . '">' .
					 "\n\t\t\t\t" . '</abbr>' .
					 "\n\t\t\t\t" . '<p class="amount">' . number_format($total_required, 2, '.', ' ') . '</p>' .
					 "\n\t\t\t\t" . '<abbr title="' . $country_name . '">' .
					 "\n\t\t\t\t\t" . '<a class="epd_country" href="country?country_id=' . $country_id . '">' . $country_abbr . '</a>' .
					 "\n\t\t\t\t" . '</abbr>' .
					 "\n\t\t\t" . '</div>';
			}
			
			echo "\n\t\t\t" . '<p class="epd_note">This is the exact amount of resources that are' .
							  ' required for production based on the currently hired workers.' .
				 "\n\t\t" . '</div>';*/
		?>
		
		<div id="companies">	
			<?php
				$query = "SELECT bi.building_id, building_icon, company_name, product_name, region_name, country_name,
						  ROUND(COALESCE((100/product_ware)*pw.amount, 0),2) AS product_storage, workers,
						  ROUND(COALESCE((100/resource_ware)*SUM(rw.amount), 0),2) AS resource_storage, co.company_id, cycles_worked
						  FROM building_info bi, companies co LEFT JOIN resource_warehouse rw ON co.company_id = rw.company_id, 
						  user_building ub, product_info pi, regions reg, country, product_warehouse pw
						  WHERE bi.building_id = co.building_id AND ub.company_id = co.company_id AND pi.product_id = bi.product_id 
						  AND co.location = reg.region_id AND country.country_id = reg.country_id AND co.company_id = pw.company_id 
						  AND pw.company_id IN (SELECT company_id FROM user_building WHERE user_id = '$user_id') 
						  AND user_id = '$user_id' GROUP BY co.company_id, product_storage ORDER BY company_name";
				$result_comp = $conn->query($query);
				while($row_comp = $result_comp->fetch_row()) {
					list($building_id, $building_icon, $company_name, $product_name, $region_name, $country_name, $product_storage, 
						 $workers, $resource_storage, $company_id, $cycles_worked) = $row_comp;

					if($product_storage >= 75 && $product_storage < 90) {
						$ps_background_color = 'rgb(223, 163, 58)';
					}
					else if($product_storage >= 90) {
						$ps_background_color = 'rgb(246, 120, 74)';
					}
					else {
						$ps_background_color = 'rgb(128, 182, 109)';
					}
					
					if($resource_storage <= 25 && $resource_storage > 10) {
						$rs_background_color = 'rgb(223, 163, 58)';
					}
					else if($resource_storage <= 10) {
						$rs_background_color = 'rgb(255, 68, 0)';
					}
					else {
						$rs_background_color = 'rgb(128, 182, 109)';
					}
			
					echo "\n\t\t\t" . '<div class="company_div" id="comp_' . $company_id . '">' .
						 "\n\t\t\t\t\t" . '<img class="building_icon" src="../building_icons/' . $building_icon . '">' .
						 "\n\t\t\t\t" . '<div class="cd_info">' .
						 "\n\t\t\t\t\t" . '<p class="ch_name">' . $lang['name'] . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="cd_comp_name">' . $company_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="ch_product">' . $lang['product'] . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="cd_product_name">' . $product_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="ch_region">' . $lang['region'] . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="cd_region_name">' . $region_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="ch_country">' . $lang['country'] . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="cd_country_name">' . $country_name . '</p>' .
						 "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<div class="cd_bars">' .
						 "\n\t\t\t\t\t" . '<p class="ch_storage">' . $lang['storage_fill'] . '</p>' .
						 "\n\t\t\t\t\t" . '<div class="cd_products_bar">' .
						 "\n\t\t\t\t\t\t" . '<div class="bar">' .
						 "\n\t\t\t\t\t\t\t" . '<div class="progress" style="width:' . $product_storage . '%;
											 background-color:' . $ps_background_color . ';"></div>' .
						 "\n\t\t\t\t\t\t\t" . '<p>' . $product_storage . '%</p>' .
						 "\n\t\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p class="ch_resources">' . $lang['resources_fill'] . '</p>' .
						 "\n\t\t\t\t\t" . '<div class="cd_resources_bar">' .
						 "\n\t\t\t\t\t\t" . '<div class="bar">' .
						 "\n\t\t\t\t\t\t\t" . '<div class="progress" style="width:' . $resource_storage . '%;
											 background-color:' . $rs_background_color . ';"></div>' .
						 "\n\t\t\t\t\t\t\t" . '<p>' . $resource_storage . '%</p>' .
						 "\n\t\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<div class="cd_resource_cycles_div">' .
						 "\n\t\t\t\t\t" . '<p class="cd_cycles_head">' . $lang['available_resources_for_working_cycles'] . '</p>';
					
					//working cycles
					$query = "SELECT product_name, pp.amount, rw.amount 
							  FROM product_info pi, resource_warehouse rw, product_product pp
							  WHERE company_id = '$company_id' AND rw.product_id = required_id 
							  AND pi.product_id = required_id AND building_id = '$building_id'
							  ORDER BY product_name";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($product_name, $req_amount, $ava_amount) = $row;	
						//use round. floor is not working for 0.3/0.1 and 0.6/0.1
						$cycles = floor(round($ava_amount/$req_amount, 3));
						if($cycles <= 5) {
							$color = 'cd_red';
						}
						else if($cycles <= 10) {
							$color = 'cd_orange';
						}
						else {
							$color = 'cd_black';
						}
						echo "\n\t\t\t\t\t\t" . '<p class="cd_cycles_info">' . $product_name . 
												': <span class="' . $color . '">' . $cycles .  '</span></p>';
					}
					
					//workers details
					echo "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<div class="cd_workers_div">' .
						 "\n\t\t\t\t\t" . '<p class="cd_workers_head">' . $lang['workers_details'] . '</p>';
					$query = "SELECT COUNT(worked), (SELECT COUNT(*) FROM hired_workers WHERE company_id = '$company_id') 
							  FROM people WHERE worked = TRUE AND person_id IN
							 (SELECT person_id FROM hired_workers WHERE company_id = '$company_id')";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($workers_worked, $hired_workers) = $row;
					for($x = 0; $x < $workers; $x++) {
						if($workers_worked > 0) {
							echo "\n\t\t\t\t\t" . '<abbr title="' . $lang['worked'] . '"><span class="fa fa-user worked"></span></abbr>';
							$workers_worked--;
							$hired_workers--;
						}
						else if ($hired_workers > 0) {
							echo "\n\t\t\t\t\t" . '<abbr title="' . $lang['not_worked'] . '"><span class="fa fa-user not_worked"></span></abbr>';
							$hired_workers--;
						}
						else {
							echo "\n\t\t\t\t\t" . '<abbr title="' . $lang['not_hired'] . '"><span class="fa fa-user not_hired"></span></abbr>';
						}
					}
					
					//working cycles left
					echo "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<div class="cd_workers_div">' .
						 "\n\t\t\t\t\t" . '<p class="cd_workers_head">Working cycles</p>';
	
					for($x = 0; $x < $workers; $x++) {
						if($cycles_worked > 0) {
							echo "\n\t\t\t\t\t" . '<abbr title="used"><span class="fa fa-circle circle worked"></span></abbr>';
							$cycles_worked--;
						}
						else {
							echo "\n\t\t\t\t\t" . '<abbr title="not used"><span class="fa fa-circle not_worked"></span></abbr>';
						}
					}
					
					echo "\n\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '<a class="button manage" href="company_manage?id=' . $company_id . 
										'">' . $lang['manage'] . '</a>' .
						 "\n\t\t\t" . '</div>';	
				}
			?>
		</div>
	</div>
</main>

<div id="create_company_div">
</div>

<?php include('footer.php'); ?>