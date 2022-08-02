<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<?php
			$corporation_id =  htmlentities(stripslashes(strip_tags(trim($_GET['corp_id']))), ENT_QUOTES);
			if(!is_numeric($corporation_id)) {
				exit(json_encode(array(
					'success'=>false,
					'error'=>"Corporation doesn't exist."
				)));
			}

			//check if is_manager
			$is_manager = false;
			$is_member = false;
			$corp_exists = false;
			$query = "SELECT manager_id, corporation_name FROM corporations WHERE corporation_id = '$corporation_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($manager_id, $corporation_name) = $row;
				if($manager_id == $user_id) {
					$is_manager = true;
					$is_member = true;
				}
				$corp_exists = true;
			}
			
			//check if member
			$query = "SELECT * FROM corporation_members WHERE user_id = '$user_id'
					  AND corporation_id = '$corporation_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$is_member = true;
				exit(json_encode(array(
					'success'=>false,
					'error'=>"You don't have access to this corporation."
				)));
			}
			echo "\n\t\t" . '<p id="page_head">' . $corporation_name . ' Corporation</p>';
		?>
		
		<div id="page_menu">
			<p id="pm_companies">Companies</p>
			<p id="pm_warehouse">Warehouse</p>
			<p id="pm_currency">Currency</p>
		</div>
		<?php
			if($corp_exists) {
				
				echo "\n\t\t" . '<p id="corp_id" hidden>' . $corporation_id . '</p>' .
					 "\n\t\t" . '<div id="companies_div">';
				
				if($is_manager) {
					echo "\n\t\t\t" . '<p id="create_company">' . $lang['create_company'] . '</p>' .
						 "\n\t\t\t" . '<select id="sort">' .
						 "\n\t\t\t\t" . '<option value="1">' . $lang['default'] . '</option>' .
						 "\n\t\t\t\t" . '<option value="2">' . $lang['company_name'] . '</option>' .
						 "\n\t\t\t\t" . '<option value="3">' . $lang['product_name'] . '</option>' .
						 "\n\t\t\t\t" . '<option value="4">' . $lang['region_name'] . '</option>' .
						 "\n\t\t\t\t" . '<option value="5">' . $lang['country_name'] . '</option>' .
						 "\n\t\t\t\t" . '<option value="6">' . $lang['storage_fill'] . '</option>' .
						 "\n\t\t\t\t" . '<option value="7">' . $lang['resources_fill'] . '</option>' .
						 "\n\t\t\t" . '</select>' .
						 "\n\t\t\t" . '<p id="sort_p">' . $lang['sort_by'] . ':</p>' .
						 "\n\t\t\t" . '<p id="collect_products">' . $lang['collect_all_products'] . '</p>' .
						 "\n\t\t\t" . '<abbr title="Invest resources in all companies that are required ' .
									  'for each worker in the company">' .
						 "\n\t\t\t\t" . '<p id="invest_resources">Invest resources</p>' .
						 "\n\t\t\t" . '</abbr>';
				}
				
				echo "\n\t\t" . '<div id="companies">';
				
				$query = "SELECT bi.building_id, building_icon, company_name, product_name, region_name, country_name,
						  ROUND(COALESCE((100/product_ware)*pw.amount, 0),2) AS product_storage, workers,
						  ROUND(COALESCE((100/resource_ware)*SUM(rw.amount), 0),2) AS resource_storage, co.company_id, cycles_worked
						  FROM building_info bi, companies co LEFT JOIN resource_warehouse rw ON co.company_id = rw.company_id, 
						  corporation_building cb, product_info pi, regions reg, country, product_warehouse pw
						  WHERE bi.building_id = co.building_id AND cb.company_id = co.company_id AND pi.product_id = bi.product_id 
						  AND co.location = reg.region_id AND country.country_id = reg.country_id AND co.company_id = pw.company_id 
						  AND pw.company_id IN (SELECT company_id FROM corporation_building WHERE corporation_id = '$corporation_id') 
						  AND corporation_id = '$corporation_id' GROUP BY co.company_id, product_storage ORDER BY company_name";
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
						 "\n\t\t\t\t" . '</div>';

					echo "\n\t\t\t\t" . '<div class="cd_bars">' .
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
				
					$query = "SELECT product_name, pp.amount, rw.amount 
								FROM product_info pi, resource_warehouse rw, product_product pp
								WHERE company_id = '$company_id' AND rw.product_id = required_id 
								AND pi.product_id = required_id AND building_id = '$building_id'";
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
					
					echo "\n\t\t\t\t" . '</div>';
					
					if($is_manager) {
						echo "\n\t\t\t\t" . '<a class="button manage" href="company_manage?id=' . $company_id . 
											'">' . $lang['manage'] . '</a>';
					}
					echo "\n\t\t\t" . '</div>';	
				}
				echo "\n\t\t" . '</div>';
				echo "\n\t\t" . '</div>';
				
				/* warehouse */
				echo "\n\t\t" . '<div id="warehouse_div">';
				echo'<div class="invest_buttons">' .
					'<p id="ccd_invest_prod" corporation_id="' . $corporation_id . '">Invest products</p>' .
					'</div>';

				$query = "SELECT cp.product_id, product_icon, amount, product_name FROM product_info pi, corporation_product cp 
							WHERE pi.product_id = cp.product_id AND corporation_id = '$corporation_id'";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($product_id, $product_icon, $amount, $product_name) = $row;
					echo'<div class="icon_amount">' .
						'<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
							$product_icon . '" alt="'  . $product_name . '"></abbr>' .
						'<p class="amount" id="pi_' . $product_id . '">' . number_format($amount, '2', '.', ' ') . '</p>';
					if($is_manager) {
						echo'<p class="giveaway_product" product_id="' . $product_id . 
							'" corporation_id="' . $corporation_id . '">Giveaway</p>';
					}
					echo'</div>';
				}

				echo "\n\t\t" . '</div>';
				
				/* currency manage */
				echo "\n\t\t" . '<div id="currency_div">';
				echo'<div class="invest_buttons">' .
					'<p id="ccd_invest_currency" corporation_id="' . $corporation_id . '">Invest currency</p>' .
					'</div>';
				if($is_member) {
					$query = "SELECT amount, currency_abbr, flag, cc.currency_id FROM corporation_currency cc, currency cu, country c
							  WHERE corporation_id = '$corporation_id' AND c.currency_id = cu.currency_id
							  AND cu.currency_id = cc.currency_id AND amount > 0 ORDER BY amount DESC";
					$result = $conn->query($query);

					while($row = $result->fetch_row()) {
						list($amount, $currency_abbr, $flag, $currency_id) = $row;
						echo'<div class="ucd_info">' .
							'<img class="ucd_flag" src="../country_flags/' . $flag . '" alt="' . $currency_abbr . '">' .
							'<p class="c_currency" id="ci_' . $currency_id . 
							'">' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>';
						if($is_manager) {
							echo'<p class="giveaway_currency" currency_id="' . $currency_id . 
								'" corporation_id="' . $corporation_id . '">Giveaway</p>';
						}
						echo "\n\t\t\t" . '</div>';
					}
				}
				else {
					echo "\n\t\t\t" . '<p>You don\'t have enough access</p>';
				}
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
			else {
				echo "Corporation doesn't exist";
			}
		?>
		
	</div>
</main>

<div id="create_company_div">
</div>
	
<?php include('footer.php'); ?> 