<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="calc_head">Calculator</p>
		<p id="calculate">Calculate</p>
		<p id="salary_lbl">Salary</p>
		<input id="salary_input" type="text" maxlength="10" value="10.00">
		<p id="skill_lbl">Skill</p>
		<input id="skill_input" type="text" maxlength="2" value="1">
		<p id="revenue_lbl">Revenue</p>
		<input id="revenue_input" type="text" maxlength="10" value="5">
		<?php
			//skill bonus
			$skills_bonus = "\n\n\n\t\t\tvar skills_bonus = {};";
			$query = "SELECT bonus, skill_lvl FROM experience ORDER BY skill_lvl";
			$result_skill = $conn->query($query);
			while($row_skill = $result_skill->fetch_row()) {
				list($bonus, $skill_lvl) = $row_skill;
				$skills_bonus .= "\n\t\t\tskills_bonus._$skill_lvl = {bonus: $bonus};";
			}
		
			//menu for energy types
			echo "\n\t\t" . '<div id="energy_types_menu_div">' .
				 "\n\t\t\t" . '<p id="etmd_head">Power stations used to calculate energy price:</p>';
			$query = "SELECT building_id, name FROM building_info bi WHERE purpose = 5";
			$result_dep = $conn->query($query);
			$js_energy_object = "\n\n\n\t\t\tvar energy_types = {};";
			$checked = false;
			$b_color = '#7a3232';
			while($row_dep = $result_dep->fetch_row()) {
				list($building_id, $building_name) = $row_dep;
					if($building_id == 28) {
						$checked = 'true';
						$b_color = '#327a37';
					}
					else {
						$checked = 'false';
						$b_color = '#7a3232';
					}
					$js_energy_object .= "\n\t\t\tenergy_types._$building_id = {type: \"_$building_id\", checked: $checked};";
					
				echo "\n\t\t\t" . '<div class="etmd_dep_product">' .
					 "\n\t\t\t\t" . '<p class="type_menu" style="background-color:' . $b_color . '">' . $building_name . '</p>' .
					 "\n\t\t\t\t" . '<p hidden>' . $building_id . '</p>' .
					 "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
		
			$query = "SELECT building_id, pi.product_id, product_name, product_icon, production, bi.purpose 
					  FROM product_info pi, product_production pp, building_info bi 
					  WHERE (pi.product_id = 16 OR pi.product_id = 26) AND pp.product_id = pi.product_id
					  AND bi.product_id = pi.product_id
					  UNION
					  SELECT building_id, pi.product_id, product_name, product_icon, production, bi.purpose
					  FROM product_info pi, product_production pp, building_info bi 
					  WHERE (pi.product_id != 16 OR pi.product_id != 26) AND pp.product_id = pi.product_id
					  AND bi.product_id = pi.product_id";
			$result = $conn->query($query);
			
			//create obj for js
			$js_script_declare_objects = "\n\n\n\t\t\tvar obj = {};";
			while($row = $result->fetch_row()) {
				list($building, $product, $product_name, $product_icon, $production, $purpose) = $row;
				$price = 0;
				$is_price_set = 'false';
				//populate obj for js
				$js_script_declare_objects .= "\n\n\t\t\tobj._$building = {};";
				if($building == 15) {//production equipment
					$price = 31.85;
				}
				else if($building == 28) {//coal energy
					$price = 1.09;
				}
				if($purpose == 5) {//energy
					$energy = 'true';
				}
				else {
					$energy = 'false';
				}
				$js_script_declare_objects .= "\n\t\t\tobj._$building.info = {user_price: 0, id: \"_$building\", set: false, " .
											  "icon_class: \"icon$product\", production: $production, calculated_price: 0, " .
											  "calculated: false, energy: $energy, productivity_id: \"p$building\"};";
				
				echo "\n\t\t" . '<div class="product_details_div" id="prod_div' . $building . '">' .
					 "\n\t\t\t" . '<p class="prod_head_name">' . $product_name . '</p>' .
					 "\n\t\t\t" . '<div class="about_product icon' . $product . '">' .
					 "\n\t\t\t\t" . '<i class="fa fa-times" aria-hidden="true"></i>' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
								    '<img class="product_icon" src="../product_icons/' . $product_icon .
								    '" alt="' . $product_name . '"></abbr>' .
					 "\n\t\t\t\t" . '<p class="amount">1/<span id="p' . $building . '">' . round($production, 2) . '</span></p>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<p class="price_lbl">Price</p>' .
					 "\n\t\t\t" . '<input class="price_input" type="text" maxlength="10" value="' . ($price == 0 ? '' : $price) . '">' .
					 "\n\t\t\t" . '<p class="calc_price_lbl">Calculated Price</p>' .
					 "\n\t\t\t" . '<p class="calc_price" id="_' . $building . '"></p>' .
					 "\n\t\t\t" . '<p class="depends_lbl">Products required for production:</p>' .
					 "\n\t\t\t" . '<div class="depend_div">';
					 
				$query = "SELECT bi2.building_id, product_name, product_icon, amount, bi2.purpose, required_id
						  FROM building_info bi2, product_product pp, building_info bi, product_info pi
						  WHERE pi.product_id = required_id AND bi.building_id = '$building' AND pp.building_id = bi.building_id
						  AND bi2.product_id = required_id
						  ORDER BY product_name";
				$result_dep = $conn->query($query);
				
				$product_type_count = 0;
				$product_type_id = 0;
				while($row_dep = $result_dep->fetch_row()) {
					list($building_id, $product_name, $product_icon, $amount, $purpose, $product_id) = $row_dep;
					if($product_type_id != $product_id) {
						$product_type_count = 0;
						$product_type_id = $product_id;
					}
					$product_type_count++;
					if($product_type_count == 1){//do not echo multiple icons
						echo "\n\t\t\t" . '<div class="dep_product icon' . $product_id . '">' .
							 "\n\t\t\t\t" . '<i class="fa fa-times" aria-hidden="true"></i>' .
							 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
										  '<img class="product_icon" src="../product_icons/' . $product_icon .
										  '" alt="' . $product_name . '"></abbr>' .
							 "\n\t\t\t\t" . '<p class="amount">' . $amount . '</p>' .
							 "\n\t\t\t" . '</div>';
					}
					if($purpose == 5) {//energy
						$energy = 'true';
					}
					else {
						$energy = 'false';
					}
				//js
				$js_script_declare_objects .= "\n\t\t\tobj._$building._$building_id = {amount:\"$amount\", " .
											  "product_id:\"_$building_id\", energy: $energy};";	 
				}
				
				echo "\n\t\t" . '</div>';
				echo "\n\t\t" . '</div>';
			}

			echo "\n\t\t<script>" .
				 "$js_energy_object" .
				 "\n\t\t$js_script_declare_objects" .
				 "\n\t\t$skills_bonus" .
				 "\n\t\t</script>";
		?>
	
	</div>
	
	<?php
		include('right_side.php');
	?>
</main>
	
<?php include('footer.php'); ?>