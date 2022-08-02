<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php
			$company_id =  htmlentities(stripslashes(strip_tags(trim($_GET['id']))), ENT_QUOTES);
			$have_access = false;
			$corporation = false;
			//show general information
			$query = "SELECT company_id FROM user_building WHERE company_id = '$company_id' AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "SELECT corporation_id FROM corporation_building WHERE company_id = '$company_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($corporation_id) = $row;
				echo "\n\t\t" . '<p id="corp_id" hidden>' . $corporation_id . '</p>';
				
				$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id' AND manager_id = '$user_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$have_access = true;
					$corporation = true;
				}
			}
			else {
				$have_access = true;
			}
			
			if($have_access) {
				//get citizenship
				$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($citizenship) = $row;
				
				//company details
				$query = "SELECT building_icon, company_name, product_name, r.region_id, region_name, c.country_id,
						  country_name, currency_abbr, workers, pi.product_id, production, bi.name, bi.building_id, tax, 
						  cycles_worked, r.country_id, pi.purpose
						  FROM building_info bi, companies co, regions r, country c, product_info pi, product_production pp, 
						  currency cu, product_production_tax ppt
						  WHERE company_id = '$company_id' AND co.location = r.region_id AND pi.product_id = pp.product_id
						  AND c.country_id = r.country_id AND bi.building_id = co.building_id 
						  AND pi.product_id = bi.product_id AND c.currency_id = cu.currency_id
						  AND ppt.country_id = r.country_id AND ppt.product_id = bi.product_id";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($building_icon, $company_name, $product_name, $region_id, $region_name, $country_id, $country_name, 
					 $currency_abbr, $workers, $product_id, $production, $building_name, $building_id, $production_tax, 
					 $cycles_worked, $country_id, $purpose) = $row;
			
				//get country production bonus
				if($purpose == 1) {//1 - resources.
					$country_bonus_perc = 0;
					$get_bonus = true;
					if($country_id != $citizenship) {
						//check if country has build company agreement
						$query = "SELECT * FROM foreign_building_policy
								  WHERE building_id = '$building_id' AND foreigners = TRUE
								  AND country_id = '$country_id' AND foreign_country = '$citizenship'";
						$result = $conn->query($query);
						if($result->num_rows == 0) {		  
							$get_bonus = false;
						}
					}
					
					if($get_bonus) {
						$query = "SELECT IFNULL(SUM(bonus), 0) FROM region_resource_bonus
								  WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
								  AND product_id = '$product_id'";
						$result = $conn->query($query);
						$row = $result->fetch_row();
						list($country_bonus_perc) = $row;
						if($country_bonus_perc > 50) {
							$country_bonus_perc = 50;
						}
					}
					
					$country_bonus_str = '<p><span>Country bonus:</span> '  . $country_bonus_perc . '%</p>';
				}
				else {
					$country_bonus_str = '';
				}
				
				//get road bonus
				$query = "SELECT productivity_bonus FROM road_const_info rcf WHERE road_id =
						 (SELECT road_id FROM region_roads WHERE region_id = '$region_id')";
				$result_bonus = $conn->query($query);
				if($result_bonus->num_rows == 1) {
					$row_bonus = $result_bonus->fetch_row();
					list($road_bonus_perc) = $row_bonus;
					$road_bonus_perc = $road_bonus_perc * 100;
				}
				else {
					$road_bonus_perc = 0;
				}
				
				if($corporation) {
					echo "\n\t\t" . '<p id="page_head">' . $lang['corporation'] . ' ' . $company_name . ' ' .
									$lang['company'] . '</p>';
				}
				else {
					echo "\n\t\t" . '<p id="page_head">' . $company_name . ' ' . $lang['company'] . '</p>';
				}
				
				echo "\n\t\t" . '<p id="company_id" hidden>' . $company_id . '</p>' .
					 "\n\t\t" . '<p id="rename" class="button green">Rename</p>' .
					 "\n\t\t" . '<p id="upgrade" class="button">' . $lang['upgrade'] . '</p>';
				
				if($workers == 1) {
					echo "\n\t\t" . '<p id="downgrade" class="button">' . $lang['destroy'] . '</p>';
				}
				else {
					echo "\n\t\t" . '<p id="downgrade" class="button">' . $lang['downgrade'] . '</p>';
				}
				$query = "SELECT company_id FROM company_market WHERE company_id = '$company_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					echo "\n\t\t" . '<p id="sell" class="button">' . $lang['selling'] . '</p>' .
						 "\n\t\t" . '<p hidden>selling</p>';
				}
				else {
					echo "\n\t\t" . '<p id="sell" class="button">' . $lang['sell'] . '</p>' .
						 "\n\t\t" . '<p hidden>sell</p>';
				}
		
				
				echo "\n\t\t" . '<img id="company_icon" src="../building_icons/' . $building_icon . '" alt="' . $building_name . '">';
				
				echo "\n\t\t" . '<div id="company_details_div">' .
					 "\n\t\t\t" . '<p><span>' . $lang['production'] . ': </span> ' . $product_name . '</p>' .
					 "\n\t\t\t" . '<p id="location"><span>' . $lang['location'] . ': </span> ' . 
								  '<a href="region_info?region_id=' . $region_id . '">' . $region_name . '</a>, ' . 
								  '<a href="country?country_id=' . $country_id . '">' . $country_name . '</a>' . 
								  '</p>' .
					 "\n\t\t\t" . '<p id="produce"><span>' . $lang['production_per_worker'] . ':</span> '  . $production . '</p>' .
					 "\n\t\t\t" . '<p id="produc_tax"><span>' . $lang['production_tax'] . ':</span> '  . $production_tax . '%</p>' .
					 "\n\t\t\t" . $country_bonus_str .
					 "\n\t\t\t" . '<p><span>Region road bonus:</span> '  . $road_bonus_perc . '%</p>' .
					 "\n\t\t" . '</div>';
					 
				//workers details
				echo "\n\t\t" . '<div class="workers">' .
					 "\n\t\t\t" . '<p id="workers_btn" class="button">' . $lang['workers'] . '</p>' .
					 "\n\t\t\t" . '<p class="cd_workers_head">' . $lang['workers_details'] . '</p>';
				
				$query = "SELECT COUNT(worked), (SELECT COUNT(*) FROM hired_workers WHERE company_id = '$company_id'),
						 (SELECT COUNT(*) FROM job_market WHERE company_id = '$company_id')
						  FROM people WHERE worked = TRUE AND person_id IN
						 (SELECT person_id FROM hired_workers WHERE company_id = '$company_id')";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($workers_worked, $hired_workers, $offered_jobs) = $row;
				$can_offer_jobs = $workers - $hired_workers - $offered_jobs;
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
				echo "\n\t\t" . '</div>';
	
				//working cycles left
				echo "\n\t\t\t\t" . '<div class="workers">' .
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
				echo "\n\t\t" . '</div>';
				
				echo "\n\t\t" . '<div id="hire_div">' .
					 "\n\t\t\t" . '<p id="hire_head">' . $lang['offer_job'] . ':</p>' .
					 "\n\t\t\t" . '<input class="h_input" id="h_exp_input" type="text" maxlength="3"  placeholder="1-25">' .
					 "\n\t\t\t" . '<p id="h_exp">' . $lang['skill'] . ':</p>' .
					 "\n\t\t\t" . '<input class="h_input" id="h_salary_input" type="text" maxlength="7"  placeholder="98.99">' .
					 "\n\t\t\t" . '<p id="h_salary">' . $lang['salary'] . ':</p>' .
					 "\n\t\t\t" . '<input class="h_input" id="h_quantity_input" type="text" maxlength="1" value="' . 
								   $can_offer_jobs . '">' .
					 "\n\t\t\t" . '<p id="h_salary">' . $lang['quantity'] . ':</p>' .
					 "\n\t\t\t" . '<p class="button green" id="offers">' . $lang['offered'] . '</p>' .
					 "\n\t\t\t" . '<p class="button blue" id="hire">' . $lang['offer'] . '</p>' .
					 "\n\t\t" . '</div>';
				//show resources required per worker
				echo "\n\t\t" . '<div id="product_required_div">' .
					 "\n\t\t\t" . '<p id="resources_heading">' . $lang['resources_required_per_one_worker'] . ':</p>';
				$query = "SELECT product_icon, amount, product_name FROM product_info pi, product_product pp 
						  WHERE building_id = '$building_id' AND pi.product_id = pp.required_id";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($product_icon, $amount, $product_name) = $row;
					echo "\n\t\t\t" . '<div class="icon_amount">' .
						 "\n\t\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
										 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t\t" . '<p class="amount">' . $amount . '</p>' .
						 "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
		
				//warehouse
				$query = "SELECT pw.amount, product_ware, (SELECT COALESCE(SUM(amount), 0) FROM resource_warehouse 
						  WHERE company_id = '$company_id'),
						  resource_ware FROM companies co, product_warehouse pw
						  WHERE co.company_id = pw.company_id AND co.company_id = '$company_id' GROUP BY pw.amount";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($product_fill, $product_storage, $resource_fill, $resource_storage) = $row;
				$product_fill = floor($product_fill * 100) / 100;//important due to datatype decimal(8,3)
				
				//product
				echo "\n\t\t" . '<div id="product_storage_div">';
				echo "\n\t\t\t" . '<div id="product_storage">';
				echo "\n\t\t\t\t" . '<p class="storage_head">' . $lang['product_storage'] . '</p>';
				echo "\n\t\t\t\t" . '<div id="ps_img">';
				echo "\n\t\t\t\t\t" . '<img src="../building_icons/storage.png" alt="storage">';
				echo "\n\t\t\t\t\t" . '<p>' . number_format($product_fill, '2', '.', ' ') . '/' . $product_storage . '</p>';
				echo "\n\t\t\t\t" . '</div>';
				echo "\n\t\t\t" . '</div>';
				echo "\n\t\t\t" . '<p id="ps_upgrade" class="button blue">' . $lang['upgrade'] . '</p>';
				echo "\n\t\t\t" . '<p id="ps_downgrade" class="button">' . $lang['downgrade'] . '</p>';
				echo "\n\t\t\t" . '<input id="ps_withdraw_input" type="text" maxlength="6">';
				echo "\n\t\t\t" . '<p id="ps_withdraw" class="button">' . $lang['withdraw'] . '</p>';
				echo "\n\t\t\t" . '<p id="ps_sell_product" class="button">' . $lang['sell'] . '</p>';
				echo "\n\t\t" . '</div>';
				
				//resource
				echo "\n\t\t" . '<div id="resource_storage_div">';
				echo "\n\t\t\t" . '<div id="resource_storage">';
				echo "\n\t\t\t\t" . '<p class="storage_head">' . $lang['resource_storage'] . '</p>';
				echo "\n\t\t\t\t" . '<div id="rs_img">';
				echo "\n\t\t\t\t\t" . '<img src="../building_icons/storage.png" alt="storage">';
				echo "\n\t\t\t\t\t" . '<p>' . number_format($resource_fill, '2', '.', ' ') . '/' . $resource_storage . '</p>';
				echo "\n\t\t\t\t" . '</div>';
				echo "\n\t\t\t" . '</div>';
				echo "\n\t\t\t" . '<p id="rs_upgrade" class="button blue">' . $lang['upgrade'] . '</p>';
				echo "\n\t\t\t" . '<p id="rs_downgrade" class="button">' . $lang['downgrade'] . '</p>';
				echo "\n\t\t" . '</div>';
				
				//invest resources for N working cycles
				echo "\n\t\t" . '<div id="invest_n_resources_div">' .
					 "\n\t\t\t" . '<p id="ird_head">' . $lang['invest_resources_for_working_cycles'] . '</p>' .
					 "\n\t\t\t" . '<input id="resource_n_invest" type="text" maxlength="6" value="' . $workers . '">' .
					 "\n\t\t\t" . '<p class="button green" id="n_invest">' . $lang['invest'] . '</p>' .
					 "\n\t\t" . '</div>';
				
				//resource in stock
				$query = "SELECT pi.product_id, product_icon, product_name, amount
						  FROM product_info pi, resource_warehouse rw WHERE pi.product_id = rw.product_id 
						  AND company_id = '$company_id'";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($r_product_id, $r_product_icon, $r_product_name, $r_amount) = $row;
					echo "\n\t\t" . '<div class="resource_stock" id="res_prod_' . $r_product_id . '">';
					echo "\n\t\t\t" . '<img src="../product_icons/' . $r_product_icon . '" alt="' . $r_product_name . '">';
					echo "\n\t\t\t" . '<p class="r_product_name">' . $r_product_name . '</p>';
					echo "\n\t\t\t" . '<p class="r_amount">' . $r_amount . '</p>';
					echo "\n\t\t\t" . '<input class="resource_invest" type="text" maxlength="6" id="' . $r_product_id . '">';
					echo "\n\t\t\t" . '<p class="button r_invest">' . $lang['invest'] . '</p>';
					echo "\n\t\t\t" . '<p class="button r_withdraw">' . $lang['withdraw'] . '</p>';
					echo "\n\t\t" . '</div>';
				}
			}
			else {
				echo "\n\t\t" . '<p>You don\'t have access to this company</p>';
			}
		?>

	</div>
	
	<div id="product_offers_div">
		<?php
		echo "\n\t\t" . '<p id="q">' . $lang['quantity'] . '</p>' .
			 "\n\t\t" . '<p id="p">' . $lang['price'] . '</p>' .
			 "\n\t\t" . '<p id="t">' . $lang['tax'] . '</p>';
		
			if($have_access) {
				//show product offers
				$query = "SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr 
						  FROM product_market pm, country c, product_sale_tax pst, product_info pi, currency cu, companies co, regions r
						  WHERE c.country_id = pm.country_id AND pst.country_id = c.country_id AND pst.country_id = r.country_id
						  AND pst.product_id = pm.product_id AND pi.product_id = pm.product_id AND cu.currency_id = c.currency_id
						  AND pm.company_id = '$company_id' AND co.company_id = pm.company_id AND region_id = location
						  UNION         
						  SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr 
						  FROM product_market pm, country c, product_import_tax pit, product_info pi, currency cu, companies co, regions r
						  WHERE c.country_id = pm.country_id AND pit.country_id = c.country_id AND from_country_id = r.country_id
						  AND pit.product_id = pm.product_id AND pi.product_id = pm.product_id AND cu.currency_id = c.currency_id
						  AND pm.company_id = '$company_id' AND co.company_id = pm.company_id AND region_id = location";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($flag, $quantity, $price, $sale_tax, $product_icon, $product_name, $offer_id, $currency_abbr) = $row;
					echo "\n\t\t" . '<div class="product_on_sale">' .
						 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img class="pos_product_icon" src="../product_icons/' . 
						 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t" . '<img class="country_flag" alt="' . $flag . '" src="../country_flags/' . $flag . '">' .
						 "\n\t\t\t" . '<p class="pos_quantity">' . number_format($quantity, '0', '', ' ') . '</p>' .
						 "\n\t\t\t" . '<p class="pos_price">' . number_format($price, '2', '.', ' ') . '</p>' .
						 "\n\t\t\t" . '<p class="pos_currency">' . $currency_abbr . '</p>' .
						 "\n\t\t\t" . '<p class="pos_tax">-' . $sale_tax . '%</p>' .
						 "\n\t\t\t" . '<p class="pos_remove button red">' . $lang['remove'] . '</p>' .
						 "\n\t\t\t" . '<p id="oi_' . $offer_id . '" hidden>' . $offer_id . '</p>' .
						 "\n\t\t" . '</div>';
				}
			}
		?>
		
	</div>
</main>

<?php include('footer.php'); ?>
