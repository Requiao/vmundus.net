<?php 
	include('head.php'); 
	include('../php_functions/law_description.php');
	include('../php_functions/statistics_classes.php');
	
?>

<main>
	<div id="col1">
	<?php	
		include('user_info.php');
		
		$is_governor = false;
		if($citizenship == $country_id) {
			//check if governor
			//check if president
			$is_governor = false;
			$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$is_governor = true;
				$row = $result->fetch_row();
				list($position_id, $governor_of_country_id) = $row;
			}
			else { //check if congressman
				$query = "SELECT country_id FROM congress_details WHERE country_id = 
						 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
				$result = $conn->query($query);
				if($result->num_rows == 1) { 
					$is_governor = true;
					$position_id = 3;
				}
			}
		}
	?>
	
	</div>
	
	<div id="container">
		<?php
		echo "\n\t\t" . '<p id="page_head">' . $lang['country'] . '</p>' .
			 "\n\t\t" . '<div id="page_menu">' .
			 "\n\t\t\t" . '<p id="country_info">' . $lang['country'] . '</p>' .
			 "\n\t\t\t" . '<p id="region_info">' . $lang['regions'] . '</p>' .
			 "\n\t\t\t" . '<p id="government_info">' . $lang['government'] . '</p>' .
			 "\n\t\t\t" . '<p id="politics_info">' . $lang['politics'] . '</p>' .
			 "\n\t\t" . '</div>';

			$country_id = htmlentities(stripslashes(strip_tags(trim($_GET['country_id']))), ENT_QUOTES);

			if(empty($country_id)) {
				//get country id
				$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($country_id) = $row;
			}
			if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
				exit('Country doesn\'t exist.');
			}
			$query = "SELECT capital, country_name, flag, region_name,
					 (SELECT COUNT(*) FROM users WHERE last_active > DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) AND user_id IN
					 (SELECT user_id FROM user_profile WHERE citizenship = '$country_id')) AS active_players,
					 (SELECT COUNT(*) FROM regions WHERE country_id = '$country_id') AS region_quantity,
					 (SELECT COUNT(*) FROM users WHERE register_date > DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY)
					  AND user_id IN (SELECT user_id FROM user_profile WHERE citizenship = '$country_id')) AS new_players,
					 (SELECT AVG(years) FROM country_people_die WHERE country_id = '$country_id' AND
					  DATE_ADD(TIMESTAMP(date, time), INTERVAL 10 DAY) >= NOW()) AS life_expectancy,
					  (SELECT citizenship FROM user_profile 
					  WHERE user_id = '$user_id') AS citizenship,
					  u.union_id, union_name, country_abbr, currency_name, currency_abbr, utc, hours
					  FROM timezones t, currency cu, regions r, country c LEFT JOIN country_unions uc ON uc.country_id = c.country_id 
					  LEFT JOIN unions u ON u.union_id = uc.union_id
					  WHERE c.country_id = '$country_id' AND c.currency_id = cu.currency_id
					  AND r.region_id = capital AND t.timezone_id = c.timezone_id";
	
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit('Country doesn\'t exist.');
			}
			$row = $result->fetch_row();
			list($capital_id, $country_name, $flag, $capital_name, $active_players, $region_quantity, $new_players,
				 $life_expectancy, $citizenship, $union_id, $union_name, $country_abbr, $currency_name, $currency_abbr, 
				 $timezone, $timezone_hours) = $row;

			if(empty($union_id)) {
				$union_id = 0;
				$union_name = "N/A";
			}
	
			echo "\n\t\t" . '<div id="country_name_flag">' .
				 "\n\t\t\t" . '<img id="country_flag" alt="country flag" src="../country_flags/' . $flag . '">' .
				 "\n\t\t\t" . '<p id="country_name">' . $country_name . '</p>';
		
			//display country list
			echo "\n\t\t\t" . '<div id="country_list">' .
				 "\n\t\t\t\t" . '<div id="country">' . 
				 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
				 "\n\t\t\t\t\t" . '<p>' . $country_name . '</p>' . 
				 "\n\t\t\t\t" . '</div>' . 
				 "\n\t\t\t\t" . '<p id="get_country_id" hidden>' . $country_id . '</p>' .  
				 "\n\t\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t\t" . '<div id="countries_div">';
			
			$query = "SELECT country_name, flag, country_id FROM country ORDER BY country_name";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($country, $flag, $country_id_list) = $row;
				echo "\n\t\t\t\t" . '<div class="country" id="' . $country_id_list . '">' . 
					 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t\t" . '<p>' . $country . '</p>' . 
					 "\n\t\t\t\t" . '</div>';
			}
			echo "\n\t\t\t" . '</div>';
			echo "\n\t\t" . '</div>';
		?>
		
		<div id="country_info_block">
		<?php
			/* country info */
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['capital'] . '</p>' .
				 "\n\t\t\t\t" . '<a class="shid_col2" href="region_info?region_id=' . $capital_id . '">' . $capital_name . '</a>' .
				 "\n\t\t\t" . '</div>' .
				
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['country_abbreviation'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . $country_abbr . '</p>' .
				 "\n\t\t\t" . '</div>' .
				
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['currency'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . $currency_name . ' (' . $currency_abbr . ')</p>' .
				 "\n\t\t\t" . '</div>' .
				 
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['timezone'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . $timezone . '</p>' .
				 "\n\t\t\t" . '</div>' .
				
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['member_of_the_union'] . '</p>' .
				 "\n\t\t\t\t" . '<a class="shid_col2" href="union_info?union_id=' . $union_id . '">' . $union_name . '</a>' .
				 "\n\t\t\t" . '</div>' .

				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['active_players'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . $active_players . '</p>' .
				 "\n\t\t\t" . '</div>' .
		
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['new_players'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . $new_players . '</p>' .
				 "\n\t\t\t" . '</div>' .
				 
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['regions'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . $region_quantity . '</p>' .
				 "\n\t\t\t" . '</div>' .
				
				 "\n\t\t\t" . '<div class="shi_details">' .
				 "\n\t\t\t\t" . '<p class="shid_col1">' . $lang['persons_life_expectancy'] . '</p>' .
				 "\n\t\t\t\t" . '<p class="shid_col2">' . round($life_expectancy,2) . ' years</p>' .
				 "\n\t\t\t" . '</div>' .

				 "\n\t\t" . '</div>';
			
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
			
			/* Daily Income Graph */
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t" . '<p class="heads difth">' . $lang['daily_income_from_taxes'] . '</p>';
			

			//select 1 extra.
			$query = "SELECT amount, date, time
					  FROM country_daily_income WHERE 
					  country_id = '$country_id' AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY) ORDER BY date, time";
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
						$daily_income_array[$x]['value'] = 0;
						$daily_income_array[$x]['name'] = $lang['day'] . ' ' . $day_count_info[$x]['day_number'];
					}
				}
				
				if($next_day) {
					$daily_income_array[$x]['value'] = $amount;
					$daily_income_array[$x]['name'] = $lang['day'] . ' ' . $day_count_info[$x]['day_number'];
				}
				else {
					$daily_income_array[$x]['value'] += $amount;
				}
			}

			while(count($daily_income_array) >= 13) {//if selects for more than 12 days
				array_shift($daily_income_array);
			}
			
			if(count($daily_income_array) < 12)  {
				for($x = count($daily_income_array); $x < 12; $x++) {
					$daily_income_array[$x]['value'] = 0;
					$daily_income_array[$x]['name'] = $lang['day'] . ' ' . $day_count_info[$x]['day_number'];
				}
			}
			
			$daily_income_stat = new BarGraph(850, 270, $daily_income_array, 10);
			$daily_income_stat->setBarsBackgroundColor('rgb(92, 153, 184)');
			$daily_income_stat->setMarkupFontSize(17);
			$daily_income_stat->setSignsFontSize(17);
			$daily_income_stat->setBarsWidth(50);
			$daily_income_stat->setSvgClass('country_stat_div');
			$daily_income_stat->generateGraph();
			
			echo  "\n\t\t" . '</div>';
			
			//display all country currency
			$query = "SELECT currency_name, currency_abbr, amount, flag FROM country_currency cc, currency cou, country c
					  WHERE cou.currency_id = cc.currency_id AND cc.country_id = '$country_id' 
					  AND cou.flag_id = c.country_id AND amount != 0 ORDER BY amount DESC";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks" id="currency_block">' .
				 "\n\t\t\t" . '<p id="" class="heads">' . $lang['country_currency'] . '</p>';
			while($row = $result->fetch_row()) {
				list($currency_name, $currency_abbr, $amount, $flag) = $row;
				echo "\n\t\t\t" . '<div class="country_currency_divs">' .
					 "\n\t\t\t\t" . '<abbr title="' . $currency_name . '"><img class="mini_flag" src="../country_flags/' . $flag . 
									'" alt="country flag"></abbr>' .
					 "\n\t\t\t\t" . '<p class="currency_amount">' . number_format($amount, 2, ".", " ") . ' (' . $currency_abbr . ')</p>' .
					 "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			/* Daily Product Income Graph */
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t" . '<p class="heads difth">' . $lang['daily_income_in_gold_from_production_taxes'] . '</p>';

			//select 1 extra.
			$query = "SELECT SUM(amount*price)/(SELECT price FROM product_price WHERE product_id = 1), date, time
					  FROM country_product_income cpi, product_price pp WHERE region_id IN
					 (SELECT region_id FROM regions WHERE country_id = '$country_id')
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
						$daily_income_array[$x]['value'] = 0;
						$daily_income_array[$x]['name'] = $lang['day'] . ' ' . $day_count_info[$x]['day_number'];
					}
				}
				
				if($next_day) {
					$daily_income_array[$x]['value'] = $amount;
					$daily_income_array[$x]['name'] = $lang['day'] . ' ' . $day_count_info[$x]['day_number'];
				}
				else {
					$daily_income_array[$x]['value'] += $amount;
				}
			}

			while(count($daily_income_array) >= 13) {//if selects for more than 12 days
				array_shift($daily_income_array);
			}
			
			if(count($daily_income_array) < 12)  {
				for($x = count($daily_income_array); $x < 12; $x++) {
					$daily_income_array[$x]['value'] = 0;
					$daily_income_array[$x]['name'] = $lang['day'] . ' ' . $day_count_info[$x]['day_number'];
				}
			}
			
			$daily_gold_income_stat = new BarGraph(850, 270, $daily_income_array, 10);
			$daily_gold_income_stat->setBarsBackgroundColor('rgb(218, 180, 57)');
			$daily_gold_income_stat->setMarkupFontSize(17);
			$daily_gold_income_stat->setSignsFontSize(17);
			$daily_gold_income_stat->setBarsWidth(50);
			$daily_gold_income_stat->setSvgClass('country_stat_div');
			$daily_gold_income_stat->generateGraph();
			
			echo  "\n\t\t" . '</div>';
			
			//country products
			echo "\n\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['country_products'] . '</p>';
		
			if($citizenship == $country_id) {
				$query = "SELECT cp.product_id, product_icon, amount, product_name FROM product_info pi, country_product cp 
						  WHERE pi.product_id = cp.product_id AND country_id = '$country_id'";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($product_id, $product_icon, $amount, $product_name) = $row;
					echo "\n\t\t" . '<div class="icon_amount">' .
						 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
							$product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t" . '<p class="amount">' . number_format($amount, '2', '.', ' ') . '</p>' . 
						 "\n\t\t" . '</div>';
				}
			}
			else {
				echo "\n\t\t" . '<p class="top_secret">' . $lang['top_secret'] . '</p>';
			}
			
			//invest gold
			echo "\n\t\t\t" . '<div id="invest_gold_div">' .
				 "\n\t\t\t\t" . '<input id="invest_gold_amount" type="text" maxlength="8" placeholder="amount">' .
				 "\n\t\t\t\t" . '<p id="invest_gold_btn">Invest personal gold</p>';
			if($is_governor && $position_id != 3 && $country_id == $governor_of_country_id) {
				echo "\n\t\t\t\t" . '<input id="invest_gov_gold_amount" type="text" maxlength="8" placeholder="amount">' .
					 "\n\t\t\t\t" . '<p id="invest_gov_gold_btn">Invest government gold</p>';
			}	 
				 
			echo "\n\t\t\t" . '</div>';
			
			echo  "\n\t\t" . '</div>';
			
			
			/* People statistic graph */
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t" . '<p class="heads difth">' . $lang['population_change_graph'] . '</p>';

			//select 1 extra. (>=) !
			//population history
			$query = "SELECT people, date, time FROM country_current_population WHERE country_id = '$country_id'
					  AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY)";
			$population_array = array();
			proccessPeopleInfo($query, $population_array);

			while(count($population_array) >= 13) {//if selects for more than 12 days
				array_shift($population_array);
			}
			
			
			$current_population_details = array();
			for($x = 0; $x < 12; $x++) {
				$current_population_details['name'][$x] = $lang['day'] . ' ' . $population_array[$x][1];
				//statistics gets recorded every hour. need to divide by number of times it was recorded per day
				//(can be more or less than 24)
				if($population_array[$x][0] > 0) {
					$current_population_details[0][$x] = round($population_array[$x][0]/$population_array[$x][2], 1);
				}
				else {
					
					$current_population_details[0][$x] = 0;
				}
			}
			
			//new people
			$population_details = array();
			$query = "SELECT COUNT(person_id), date, time
					  FROM country_people_born WHERE country_id = '$country_id'
					  AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY) 
					  GROUP BY date, time ORDER BY date, time";
			$population_array = array();
			proccessPeopleInfo($query, $population_array);
			while(count($population_array) >= 13) {//if selects for more than 12 days
				array_shift($population_array);
			}
			
			for($x = 0; $x < 12; $x++) {
				$population_details['name'][$x] = $lang['day'] . ' ' . $population_array[$x][1];
				
				if(empty($population_array[$x][0])) {
					$population_details[0][$x] = 0;
				}
				else {
					$population_details[0][$x] = $population_array[$x][0];
				}
			}
			
			//died people
			$query = "SELECT COUNT(person_id), date, time
					  FROM country_people_die WHERE country_id = '$country_id'
					  AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY) 
					  GROUP BY date, time ORDER BY date, time";
			$population_array = array();
			proccessPeopleInfo($query, $population_array);
			while(count($population_array) >= 13) {//if selects for more than 12 days
				array_shift($population_array);
			}
			
			for($x = 0; $x < 12; $x++) {
				$population_details[1][$x] = $population_array[$x][0];
			}

			function proccessPeopleInfo($query, &$population_array) {
				global $conn;
				global $country_id;
				global $country_date_start;
				global $start_day;
				global $day_count_info;
				
				$result = $conn->query($query);
				$x = -1;
				
				while($row = $result->fetch_row()) {
					list($quantity, $date, $time) = $row;
					
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
						//next day after 4am
						if($country_stat_date_time >= $day_count_info[$x+1]['country_date_start']
						   && $country_stat_date_time < $day_count_info[$x+2]['country_date_start']) {
							$next_day = true;
							$flag = false;
							$x++;
						}
						//same day
						else if($country_stat_date_time >= $day_count_info[$x]['country_date_start']
								&& $country_stat_date_time < $day_count_info[$x+1]['country_date_start']){
							$next_day = false;
							$flag = false;
						}
						else {//prev day
							$x++;
							$population_array[$x][0] = 0;
							$population_array[$x][1] = $day_count_info[$x]['day_number'];
							$population_array[$x][2] = 1;//only for total country pop
						}
					}
					
					if($next_day) {
						$population_array[$x][0] = $quantity;
						$population_array[$x][1] = $day_count_info[$x]['day_number'];
						$population_array[$x][2] = 1;//only for total country pop
					}
					else {
						$population_array[$x][0] += $quantity;
						$population_array[$x][2]++;//only for total country pop
					}
				}
			}
			
			$colors[0] = '#0074d9';
			
			$description[0] = $lang['current_population'];
			
			$population_chart = new LineGraph(850, 200, $current_population_details, 6, $description);
			$population_chart->setMarkupFontSize(16);
			$population_chart->setSignsFontSize(17);
			$population_chart->setSvgClass('country_stat_div');
			$population_chart->setSignsLinesColors($colors);
			$population_chart->generateGraph();
			
			$colors[0] = '#4a674a';
			$colors[1] = '#a93434';
			
			$description[0] = $lang['new_people'];
			$description[1] = $lang['passed_away_people'];
			
			$population_chart = new LineGraph(850, 350, $population_details, 12, $description);
			$population_chart->setMarkupFontSize(16);
			$population_chart->setSignsFontSize(17);
			$population_chart->setSvgClass('country_stat_div');
			$population_chart->setSignsLinesColors($colors);
			$population_chart->generateGraph();
			
			echo  "\n\t\t" . '</div>';
			

			/* People statistic graph */
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t" . '<p class="heads difth" id="demographic_head">' . $lang['country_population_graph'] . '</p>';
			
			$query = "SELECT years, COUNT(person_id) FROM people p
					  WHERE user_id IN (SELECT user_id FROM user_profile WHERE citizenship = '$country_id') 
					  GROUP BY years ORDER BY years DESC";
			$result = $conn->query($query);
			
			$demographic = array();
			$demographic[0]['value'] = 0;
			$demographic[0]['name'] = 'Years 18...30';
			$demographic[1]['value'] = 0;
			$demographic[1]['name'] = 'Years 31...40';
			$demographic[2]['value'] = 0;
			$demographic[2]['name'] = 'Years 41...50';
			$demographic[3]['value'] = 0;
			$demographic[3]['name'] = 'Years 51...60';
			$demographic[4]['value'] = 0;
			$demographic[4]['name'] = 'Years 61...70';
			$demographic[5]['value'] = 0;
			$demographic[5]['name'] = 'Years 71...80';
			$demographic[6]['value'] = 0;
			$demographic[6]['name'] = 'Years 81...100';
			$demographic[7]['value'] = 0;
			$demographic[7]['name'] = 'Years 101...120';
			$demographic[8]['value'] = 0;
			$demographic[8]['name'] = 'Years 121...140';
			$demographic[9]['value'] = 0;
			$demographic[9]['name'] = 'Years 141...160';
			$demographic[10]['value'] = 0;
			$demographic[10]['name'] = 'Years 161...180';
			$demographic[11]['value'] = 0;
			$demographic[11]['name'] = 'Years 181...200';
			$demographic[12]['value'] = 0;
			$demographic[12]['name'] = 'Years 201...220';
			
			while($row = $result->fetch_row()) {
				list($years, $people) = $row;
				if($years <= 30) {
					$demographic[0]['value'] += $people;
					$demographic[0]['name'] = $lang['years'] . ' 18...30';
				}
				else if ($years > 30 && $years <= 40) {
					$demographic[1]['value'] += $people;
					$demographic[1]['name'] = $lang['years'] . ' 31...40';
				}
				else if ($years > 40 && $years <= 50) {
					$demographic[2]['value'] += $people;
					$demographic[2]['name'] = $lang['years'] . ' 41...50';
				}
				else if ($years > 50 && $years <= 60) {
					$demographic[3]['value'] += $people;
					$demographic[3]['name'] = $lang['years'] . ' 51...60';
				}
				else if ($years > 60 && $years <= 70) {
					$demographic[4]['value'] += $people;
					$demographic[4]['name'] = $lang['years'] . ' 61...70';
				}
				else if ($years > 70 && $years <= 80) {
					$demographic[5]['value'] += $people;
					$demographic[5]['name'] = $lang['years'] . ' 71...80';
				}
				else if ($years > 80 && $years <= 100) {
					$demographic[6]['value'] += $people;
					$demographic[6]['name'] = $lang['years'] . ' 81...100';
				}
				else if ($years > 100 && $years <= 120) {
					$demographic[7]['value'] += $people;
					$demographic[7]['name'] = $lang['years'] . ' 101...120';
				}
				else if ($years > 120 && $years <= 140) {
					$demographic[8]['value'] += $people;
					$demographic[8]['name'] = $lang['years'] . ' 121...140';
				}
				else if ($years > 140 && $years <= 160) {
					$demographic[9]['value'] += $people;
					$demographic[9]['name'] = $lang['years'] . ' 141...160';
				}
				else if ($years > 160 && $years <= 180) {
					$demographic[10]['value'] += $people;
					$demographic[10]['name'] = $lang['years'] . ' 161...180';
				}
				else if ($years > 180 && $years <= 200) {
					$demographic[11]['value'] += $people;
					$demographic[11]['name'] = $lang['years'] . ' 181...200';
				}
				else if ($years > 200 && $years <= 220) {
					$demographic[12]['value'] += $people;
					$demographic[12]['name'] = $lang['years'] . ' 201...220';
				}
			}
			
			$demographic_stat = new HorizontalBarGraph(850, 520, $demographic, 20);
			$demographic_stat->setBarsBackgroundColor('rgb(119, 156, 99)');
			$demographic_stat->setMarkupFontSize(17);
			$demographic_stat->setSignsFontSize(17);
			$demographic_stat->setSvgClass('country_stat_div');
			$demographic_stat->generateGraph();
			
			echo "\n\t\t" . '</div>';
			
			//Productivity bonus
			echo "\n\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t" . '<p class="heads">Productivity bonus</p>';
				 
			$query = "SELECT product_icon, product_name, SUM(bonus)
					  FROM region_resource_bonus rrb, product_info pi
					  WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
					  AND pi.product_id = rrb.product_id
					  GROUP BY rrb.product_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_icon, $product_name, $bonus) = $row;
				if($bonus > 50) {
					$bonus = 50;
				}
				echo "\n\t\t" . '<div class="icon_amount">' .
					 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
								   $product_icon . '" alt="'  . $product_name . '"></abbr>' .
					 "\n\t\t\t" . '<p class="amount country_resources_amount">' . $bonus . '%</p>' . 
					 "\n\t\t" . '</div>';
			}
			
			echo  "\n\t\t" . '</div>';
			
			
			//regions with Productivity bonus
			echo "\n\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t" . '<p class="heads">Number of regions with specific resources</p>';
				 
			$query = "SELECT product_icon, product_name, COUNT(rrb.product_id)
					  FROM region_resource_bonus rrb, product_info pi
					  WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
					  AND pi.product_id = rrb.product_id
					  GROUP BY rrb.product_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_icon, $product_name, $regions) = $row;
				if($bonus > 50) {
					$bonus = 50;
				}
				echo "\n\t\t" . '<div class="icon_amount">' .
					 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
								   $product_icon . '" alt="'  . $product_name . '"></abbr>' .
					 "\n\t\t\t" . '<p class="amount country_resources_amount">' . $regions . '</p>' . 
					 "\n\t\t" . '</div>';
			}
			
			echo  "\n\t\t" . '</div>';
		?>
		
		</div>
		
		<div id="country_regions_block">
		<?php
			/* Regions block */
			$query = "SELECT region_name, r.region_id, IFNULL(rn.region_id, false)
					  FROM regions r LEFT JOIN region_neighbors rn
					  ON rn.region_id = r.region_id AND neighbor = 0
					  WHERE country_id = '$country_id' ORDER BY region_name";	
			$result_regions = $conn->query($query);
			while($row_regions = $result_regions->fetch_row()) {
				list($region_name, $region_id, $coastal) = $row_regions;
				echo "\n\t\t\t" . '<div class="info_blocks">' .
					 "\n\t\t\t\t" . '<a class="cr_region_name" href="region_info?region_id=' . $region_id . '">' . $region_name . 
									'<i class="fa fa-link" aria-hidden="true"></i></a>';
			
				//check if under attack
				$query = "SELECT * FROM battles WHERE active = TRUE AND region_id = '$region_id'";
				$result = $conn->query($query);
				if($result->num_rows > 0) {
					echo "\n\t\t\t\t" . '<p class="under_attack">' . $lang['under_attack'] . '</p>';
				}
			
				//get region resource bonus
				$query = "SELECT product_icon, product_name, bonus FROM product_info pi, region_resource_bonus rrb 
						  WHERE pi.product_id = rrb.product_id AND region_id = '$region_id'";
				echo "\n\t\t\t\t" . '<div class="cr_info_div">' .
					 "\n\t\t\t\t\t" . '<p class="cr_heads">' . $lang['region_resources'] . '</p>';
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($product_icon, $product_name, $bonus) = $row;
					echo "\n\t\t" . '<div class="icon_amount">' .
						 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
							$product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t" . '<p class="amount country_resources_amount">' . $bonus . '%</p>' . 
						 "\n\t\t" . '</div>';
				}
				echo "\n\t\t\t\t" . '</div>';
			
				//average income from production taxes
				echo "\n\t\t\t\t" . '<div class="cr_info_div region_avg_prod_taxes">' .
						"\n\t\t\t\t\t" . '<p class="cr_heads">' . $lang['average_income_from_production_taxes'] . '</p>';
				if($citizenship == $country_id) {
					$query = "SELECT SUM(amount*price)/(SELECT price FROM product_price WHERE product_id = 1),
							(SELECT COUNT(*) FROM (SELECT COUNT(date) FROM country_product_income WHERE region_id = '$region_id'
							GROUP BY date) AS temp)
							FROM country_product_income cpi, product_price pp WHERE region_id = '$region_id'
							AND cpi.product_id = pp.product_id
							AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY)";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($total_income, $days) = $row;
					if($days > 0) {
						$average_income = round($total_income / $days, 3);
					}
					else {
						$average_income = 0;
					}
					
					echo"\n\t\t\t\t\t" . '<div class="icon_amount">' .
						"\n\t\t\t\t\t\t" . '<abbr title="Gold"><img class="product_icon" src="../product_icons/gold.png' . 
											'" alt="Gold"></abbr>' .
						"\n\t\t\t\t\t\t" . '<p class="amount country_resources_amount">' . 
											number_format($average_income, '3', '.', ' ') . '</p>' . 
						"\n\t\t\t\t\t" . '</div>';
				}
				else {
					echo '<p class="top_secret">Top Secret</p>';
				}
				echo "\n\t\t\t\t" . '</div>';
					 
				//defence system
				$query = "SELECT rds.strength, dci.strength FROM region_defence_systems rds, def_const_info dci
						  WHERE region_id = '$region_id' AND dci.def_loc_id = rds.def_loc_id";
				$result_def_loc = $conn->query($query);
				$row_def_loc = $result_def_loc->fetch_row();
				list($defence_srength, $base_strength) = $row_def_loc;
				$position_percentage = round(($defence_srength / $base_strength) * 100, 2);
				
				echo "\n\t\t\t\t" . '<div class="cr_info_div region_def_system">' .
					 "\n\t\t\t\t\t" . '<p class="cr_heads">' . $lang['defense_system'] . '</p>' .
					 "\n\t\t\t\t\t" . '<div id="position_bar_div">' .
					 "\n\t\t\t\t\t\t" . '<div id="position_progress" style="width: ' . $position_percentage . '%;"></div>' .
					 "\n\t\t\t\t\t\t" . '<p><i class="fa fa-shield" aria-hidden="true"></i> ' . $position_percentage . '%</p>' .
					 "\n\t\t\t\t\t" . '</div>' .
					 "\n\t\t\t\t\t" . '<p id="position_info">' . number_format($defence_srength, 2, ".", " ") . 
									  '/' . number_format($base_strength, 2, ".", " ") . '</p>' .
					 "\n\t\t\t\t" . '</div>';	 

				if($coastal) {
					echo "\n\t\t\t\t" . '<div class="crb_coastal_region">' .
						 "\n\t\t\t\t\t" . '<img src="../country_flags/ocean.png" alt="Sea Access">' .
						 "\n\t\t\t\t\t" . '<p>Coastal region</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				
				//roads
				$query = "SELECT rr.road_id, road_img, rr.durability, rci.durability, productivity_bonus 
						  FROM region_roads rr, road_const_info rci
						  WHERE rr.region_id = '$region_id' AND rci.road_id = rr.road_id";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($road_id, $road_img, $left_durability, $total_durability, $productivity_bonus) = $row;
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
			
					echo "\n\t\t\t\t" . '<div class="cr_region_road">' .
						 "\n\t\t\t\t\t" . '<p class="crrr_head">Region road</p>' .
						 "\n\t\t\t\t\t" . '<img src="../infrastructure/' . $road_img . '" alt="Road">' .
						 "\n\t\t\t\t\t" . '<p class="crrr_level">Level: ' . $road_id . '</p>' .
						 "\n\t\t\t\t\t" . '<div class="crrr_durability_div">' .
						 "\n\t\t\t\t\t\t" . '<div class="progress ' . $color . '" style="width: ' . $bar_width . '%;"></div>' .
						 "\n\t\t\t\t\t\t" . '<p>Uses left: ' . number_format($left_durability, 0, '', ' ') . '/' . 
											number_format($total_durability, 0, '', ' ') . '</p>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p class="crrr_bonus">Productivity bonus: ' . $productivity_bonus . '%</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				else {
					echo "\n\t\t\t\t" . '<div class="cr_region_road">' .
						 "\n\t\t\t\t\t" . '<p class="crrr_head">Region road</p>' .
						 "\n\t\t\t\t\t" . '<img src="../infrastructure/road_0.png" alt="Road">' .
						 "\n\t\t\t\t\t" . '<p class="crrr_level">Level: 0</p>' .
						 "\n\t\t\t\t\t" . '<div class="crrr_durability_div">' .
						 "\n\t\t\t\t\t\t" . '<div class="progress green" style="width: 100%;"></div>' .
						 "\n\t\t\t\t\t\t" . '<p>Uses left: 0/0</p>' .
						 "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t\t" . '<p class="crrr_bonus">Productivity bonus: 0%</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>'; 
		?>

		<div id="country_government_block">
		<?php
			//get country currency
			$query = "SELECT currency_abbr FROM currency WHERE currency_id =
					 (SELECT currency_id FROM country WHERE country_id = '$country_id')";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($currency_abbr) = $row;
		
			function displayGovernorInfo($governor_name, $governor_id, $position_name, $date_elected, $day_elected, $term, $salary, 
										 $governor_img, $party_id = 0, $party_name = '') {
				global $citizenship, $country_id, $currency_abbr, $lang;
				//make sure to display right elected day number
				$correct_date_elected = date('M j', strtotime(correctDate($date_elected, date('H:i:s', strtotime('0:0:0')), $country_id)));
				$date_elected = date('M j', strtotime($date_elected));
				if(strtotime($date_elected) < strtotime($correct_date_elected)) {
					$day_elected++;
				}
				else if(strtotime($date_elected) > strtotime($correct_date_elected)) {
					$day_elected--;
				}
				
				if($term > 0) {
					$elected_until_day = $day_elected + $term;
				}
				else {
					$elected_until_day = "N/A";
				}
	
				echo "\n\t\t\t" . '<div class="country_government_div">' .
					 
					 "\n\t\t\t\t" . '<a href="user_profile?id=' . $governor_id . 
									'" class="cgd_name" target="_blank">' . $governor_name .'</a>' .
					 "\n\t\t\t\t" . '<img src="../user_images/' . $governor_img . '" class="governor_image">' .
					 "\n\t\t\t\t" . '<p class="position_name">' . $position_name . '</p>' .
					 "\n\t\t\t\t" . '<div class="governor_info_class">' .
					 "\n\t\t\t\t\t" . '<p class="elected_since governor_info">' . $lang['on_duty_since_day'] . 
									  ' <span>' . $day_elected . '</span></p>' .
					 "\n\t\t\t\t\t" . '<p class="elected_until_day governor_info">' . $lang['on_duty_until_day'] . 
									  ' <span>' . $elected_until_day . '</span></p>' .
					 "\n\t\t\t\t\t" . '<p class="governor_salary governor_info">' . $lang['salary'] . 
									  ' <span>' . 
									  number_format($salary, '2', '.', ' ') . ' ' . $currency_abbr . '</span></p>';
	
				if($party_id != 0) {
					echo "\n\t\t\t\t\t" . '<p class="governor_info">Party: <a href="party_info?party_id=' . $party_id . 
										'" class="cgd_party" target="_blank">' . $party_name .'</a></p>';
				}
				echo "\n\t\t\t\t" . '</div>';
				echo "\n\t\t\t" . '</div>';
			}
			
		
			$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($citizenship) = $row;

			//display government information
			echo "\n\t\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t\t" . '<p class="heads">' . $lang['government'] . '</p>';
			$query = "SELECT user_name, cg.user_id, name, elected, day_number, term, salary, user_image FROM user_profile up, users u, 
					  country_government cg LEFT JOIN government_term_length gtl ON gtl.country_id = cg.country_id 
					  AND gtl.position_id = cg.position_id, day_count dc, government_positions gp
					  WHERE cg.country_id = '$country_id' AND u.user_id = cg.user_id AND gp.position_id = cg.position_id 
					  AND dc.date = elected
					  AND up.user_id = u.user_id ORDER BY cg.position_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($governor_name, $governor_id, $position_name, $date_elected, $day_elected, $term, $salary, $governor_img) = $row;
				displayGovernorInfo($governor_name, $governor_id, $position_name, $date_elected, 
									$day_elected, $term, $salary, $governor_img);
			}
			echo "\n\t\t\t" . '</div>';
			
			//show congress
			echo "\n\t\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t\t" . '<p class="heads">' . $lang['congress'] . '</p>';
			
			$query = "SELECT user_name, cg.user_id, name, elected, day_number, term, salary, user_image, pm.party_id, pp.party_name
					  FROM users u LEFT JOIN party_members pm ON pm.user_id = u.user_id LEFT JOIN political_parties pp
					  ON pp.party_id = pm.party_id,
					  congress_members cg, government_term_length gtl, day_count dc, government_positions gp,
					  congress_details cd, user_profile up
					  WHERE cg.country_id = '$country_id' AND u.user_id = cg.user_id AND dc.date = elected AND cd.country_id = cg.country_id
					  AND gp.position_id = 3 AND gtl.country_id = cg.country_id
					  AND gtl.position_id = gp.position_id AND up.user_id = u.user_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($governor_name, $governor_id, $position_name, $date_elected, $day_elected, $term, $salary, $governor_img,
					 $party_id, $party_name) = $row;

				displayGovernorInfo($governor_name, $governor_id, $position_name, $date_elected, $day_elected, $term, $salary, 
									$governor_img, $party_id, $party_name);
			}
			echo "\n\t\t\t" . '</div>';
			
			echo "\n\t\t" . '</div>';
		?>
		
		<div id="politics_info_block">
		<?php
			echo "\n\t\t" . '<p id="law_history">' . $lang['law_history'] . '</p>';
		
			if($citizenship == $country_id) {
				if($is_governor) {
					//check if has rights to perform this action
					$query = "SELECT * FROM government_country_responsibilities 
							  WHERE country_id = '$country_id' AND responsibility_id = '32' AND position_id = '$position_id'
							  AND must_sign_vote = TRUE";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						echo "\n\t\t" . '<p id="cz_requests">Citizenship requests</p>';
					}
				}
			}
		
			/* Politic */
			// display laws in progress
			$query = "SELECT cli.responsibility_id, responsibility, user_name, proposed_date, proposed_time, yes, no, user_id, law_id 
					  FROM political_responsibilities pr, country_law_info cli, users u
					  WHERE pr.responsibility_id = cli.responsibility_id AND u.user_id = proposed_by
					  AND country_id = '$country_id' AND is_processed = FALSE
					  ORDER BY proposed_date DESC, proposed_time DESC";
			$result = $conn->query($query);
	
			while($row = $result->fetch_row()) {
				list($responsibility_id, $responsibility, $user_name, $proposed_date, $proposed_time, 
					 $yes, $no, $governor_id, $law_id) = $row;

				//correct date/time
				// + 24 hours to get expire time.
				$expire_date_time = date('Y-m-d H:i:s', strtotime($proposed_date . $proposed_time . ' + 24 hours'));
				
				
				$expire_time = date('H:i:s', strtotime($expire_date_time));
				$expire_date = date('Y-m-d', strtotime($expire_date_time));
				$expire_date = correctDate($expire_date, $expire_time);
				$expire_time = correctTime($expire_time);		

				//find out when expires
				$current_date = correctDate(date('Y-m-d'), date('H:i:s'));
				$current_time = correctTime(date('H:i:s'));
				
				$date1 = date_create($current_date . ' ' . $current_time);
				$date2 = date_create($expire_date . ' ' . $expire_time);
				$diff = date_diff($date1,$date2);
				$expires_in = $diff->format("%H:%I:%S");
				
				//to display proposed date/time
				$proposed_date = correctDate($proposed_date, $proposed_time);
				$proposed_time = correctTime($proposed_time);
				
				if(date('Y-m-d H:i:s', strtotime($current_date . ' ' . $current_time)) > 
				   date('Y-m-d H:i:s', strtotime($expire_date . ' ' . $expire_time))) {//if expired, then don't show
					continue;
				}
				
				//get description
				$description = lawDescription($responsibility_id, $law_id);
				
				echo "\n\t\t" . '<div class="info_blocks">' .
					 "\n\t\t\t" . '<p class="heads">' . $responsibility . '</p>' .
					 "\n\t\t\t" . '<div class="pli_description">Description: ' . $description . '</div>' .
					 "\n\t\t\t" . '<p class="pli_proposed_by">Proposed by: ' . 
					 "\n\t\t\t\t" . '<a href="user_profile?id=' . $governor_id . '" target="_blank">' . $user_name . '</a>' .
					 "\n\t\t\t" . '</p>' .
					 "\n\t\t\t" . '<p class="pli_proposed_on">Proposed on: ' . $proposed_date . ' ' . $proposed_time . '</p>' .
					 "\n\t\t\t" . '<p class="pli_expires_in">' . $expires_in . '</p>' .
					 "\n\t\t\t" . '<div class="pli_yes">' .
					 "\n\t\t\t\t" . '<p class="pli_check"><span class="fa fa-check" aria-hidden="true"></span></p>' .
					 "\n\t\t\t\t" . '<p class="pli_yes_voted">' . $yes . '</p>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div class="pli_no">' .
					 "\n\t\t\t\t" . '<p class="pli_times"><span class="fa fa-times"></span></p>' .
					 "\n\t\t\t\t" . '<p class="pli_no_voted">' . $no . '</p>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
			
			//display government term length
			$query = "SELECT term, name FROM government_term_length gtl, government_positions gp
					  WHERE gp.position_id = gtl.position_id AND country_id = '$country_id'";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['government_term_length'] . '</p>';
				  
			
			while($row = $result->fetch_row()){
				list($term, $name) = $row;
				echo "\n\t\t\t" . '<div class="shi_details shi">' .
					 "\n\t\t\t\t" . '<p class="shid_col1">' . $name . '</p>' .
					 "\n\t\t\t\t" . '<p class="shid_col2">' . $term . ' days</p>' .
					 "\n\t\t\t" . '</div>';
			}
			
			echo "\n\t\t" . '</div>';
			
			//war with countries
			$query = "SELECT country_name, flag, country_id FROM country WHERE country_id IN
					 (SELECT with_country_id FROM country_wars WHERE country_id = '$country_id'
					  AND active = TRUE AND is_resistance = FALSE)
					  UNION
					  SELECT country_name, flag, country_id FROM country WHERE country_id IN
					 (SELECT country_id FROM country_wars WHERE with_country_id = '$country_id'
					  AND active = TRUE AND is_resistance = FALSE)
					  ORDER BY country_name";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['active_wars'] . '</p>';
		
			if($result->num_rows == 0) {
				echo "\n\t\t\t" . '<p>' . $lang['this_country_is_peaceful'] . '</p>';
			}
			while($row = $result->fetch_row()) {
				list($country_name, $flag, $with_country_id) = $row;

				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<img class="mini_flag country_margin_flag" alt="' . $country_name . 
									'" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="country_wars" href="country?country_id=' . $with_country_id . 
									'">' . $country_name . '</a>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			//income tax
			$query = "SELECT tax FROM income_tax WHERE country_id = '$country_id'";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['income_tax'] . '</p>';
				  
			$row = $result->fetch_row();
			list($tax) = $row;
			echo "\n\t\t\t" . '<p class="shid_col1">' . $lang['income_tax'] . '</p>';
			echo "\n\t\t\t" . '<p class="shid_col2 tax_color">' . $tax . '%</p>';
			echo "\n\t\t" . '</div>';
			
			//taxes on products for inside country companies
			$query = "SELECT product_icon, product_name, sale_tax FROM product_info pi, product_sale_tax pst
					  WHERE pi.product_id = pst.product_id AND pst.country_id = '$country_id'
					  ORDER BY pst.product_id";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['taxes_on_product_sale'] . '</p>';
				  
			while($row = $result->fetch_row()){
				list($product_icon, $product_name, $sale_tax) = $row;
				echo "\n\t\t\t" . '<div class="icon_amount">' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
								    '<img class="product_icon" src="../product_icons/' . $product_icon .'" alt="' . $product_name . '"></abbr>' .
					 "\n\t\t\t\t" . '<p class="pst_product_name">' . $product_name . '</p>' .
					 "\n\t\t\t\t" . '<p class="pst_tax tax_color">' . $sale_tax . '%</p>' .
				     "\n\t\t\t" . '</div>';;
			}
			echo "\n\t\t" . '</div>';
			
			
			//import permission and taxes on products for foreigners
			$query = "SELECT product_icon, product_name, flag, country_name, sale_tax, days, from_country_id
					  FROM product_info pi, product_import_tax pit, country c
					  WHERE pi.product_id = pit.product_id AND pit.country_id = '$country_id' AND permission = TRUE
					  AND c.country_id = pit.from_country_id
					  ORDER BY country_name";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['taxes_on_import'] . '</p>';
		
			if($result->num_rows == 0) {
				echo "\n\t\t\t" . '<p>' . $lang['importing_products_into_this_country_is_not_allowed'] . '</p>';
			}
			while($row = $result->fetch_row()){
				list($product_icon, $product_name, $flag, $country_name, $sale_tax, $days, $from_country_id) = $row;

				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
								      '<img class="pit_product_icon" src="../product_icons/' . 
									  $product_icon .'" alt="' . $product_name . '"></abbr>' .
					 "\n\t\t\t\t" . '<p class="pit_product_name">' . $product_name . '</p>' .
					 "\n\t\t\t\t" . '<img class="mini_flag import_from_country_flag" alt="' . $country_name . 
									'" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="pit_country_name" href="country?country_id=' . $from_country_id . 
									'">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '<p class="pit_tax tax_color">' . $sale_tax . '%</p>' .
					 "\n\t\t\t\t" . '<p class="pit_days">' . $days . ' ' . $lang['days_left'] . '</p>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			//taxes on production
			$query = "SELECT product_icon, product_name, tax FROM product_production_tax ppt, product_info pi
					  WHERE pi.product_id = ppt.product_id AND country_id = '$country_id'
					  ORDER BY ppt.product_id";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['taxes_on_production'] . '</p>';
				  
			while($row = $result->fetch_row()){
				list($product_icon, $product_name, $sale_tax) = $row;
				echo "\n\t\t\t" . '<div class="icon_amount">' .
					 "\n\t\t\t\t" . '<abbr title="' . $product_name . '">' .
								    '<img class="product_icon" src="../product_icons/' . $product_icon .'" alt="' . $product_name . '"></abbr>' .
					 "\n\t\t\t\t" . '<p class="pst_product_name">' . $product_name . '</p>' .
					 "\n\t\t\t\t" . '<p class="pst_tax tax_color">' . $sale_tax . '%</p>' .
				     "\n\t\t\t" . '</div>';;
			}
			echo "\n\t\t" . '</div>';
			
			//company price for citizens
			$query = "SELECT building_icon, name, price 
					  FROM building_info bi, building_policy bp
					  WHERE bi.building_id = bp.building_id AND bp.country_id = '$country_id' AND bi.is_active = TRUE";
			$result = $conn->query($query);
		
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['taxes_to_build_a_company'] . '</p>';
				  
			while($row = $result->fetch_row()){
				list($building_icon, $building_name, $price) = $row;
				
				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<abbr title="' . $building_name . '">' .
								    '<img class="building_icon" src="../building_icons/' . $building_icon .
									'" alt="' . $building_name . '"></abbr>' .
					 "\n\t\t\t\t" . '<p class="cbp_building_name">' . $building_name . '</p>' .
					 "\n\t\t\t\t" . '<div class="cbp_price">' .	
					 "\n\t\t\t\t\t" . '<p>' . number_format($price, "2", ".", " ") . '</p>' .
					 "\n\t\t\t\t\t" . '<img src="../img/gold.png">' .
					 "\n\t\t\t\t" . '</div>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			//company price for foreigners
			$query = "SELECT building_icon, name, country_name, flag, price, foreign_country 
					  FROM building_info bi, foreign_building_policy bp, country c
					  WHERE bi.building_id = bp.building_id AND bp.country_id = '$country_id' AND foreigners = TRUE 
					  AND c.country_id = foreign_country AND bi.is_active = TRUE";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['taxes_for_foreigners_to_build_a_company'] . '</p>';
		
			if($result->num_rows == 0) {
				echo "\n\t\t\t" . '<p>' . $lang['foreigners_are_not_allowed_to_build_companies_in_this_country'] . '</p>';
			}
			while($row = $result->fetch_row()){
				list($building_icon, $building_name, $country_name, $flag, $price, $foreign_country) = $row;

				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<abbr title="' . $building_name . '">' .
								    '<img class="building_icon" src="../building_icons/' . 
									$building_icon .'" alt="' . $building_name . '"></abbr>' .
					 "\n\t\t\t\t" . '<p class="cbp_building_name">' . $building_name . '</p>' .
					 "\n\t\t\t\t" . '<img class="mini_flag foreigners_from_country_flag" alt="' . $country_name . 
									'" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="fcbp_country_name" href="country?country_id=' . $foreign_country . 
									'">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '<div class="fcbp_price">' .				
					 "\n\t\t\t\t\t" . '<p>' . number_format($price, "2", ".", " ") . '</p>' .
					 "\n\t\t\t\t\t" . '<img src="../img/gold.png">' .
					 "\n\t\t\t\t" . '</div>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			
			//travel agreements to
			$query = "SELECT flag, country_name, days, ta.country_id FROM country c, travel_agreement ta
					  WHERE c.country_id = ta.country_id AND permission = TRUE AND from_country_id = '$country_id'";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['travel_agreements_can_travel_to'] . '</p>';
		
			if($result->num_rows == 0) {
				echo "\n\t\t\t" . '<p>' . $lang['citizens_of_this_country_cannot_travel_to_any_other_country'] . '</p>';
			}
			while($row = $result->fetch_row()){
				list($flag, $country_name, $days, $to_country_id) = $row;

				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<img class="mini_flag country_margin_flag" alt="' . $country_name . '" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="ta_country_name" href="country?country_id=' . $to_country_id . 
									'">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '<p class="ta_days">' . $lang['expires_in'] . ' ' . $days . ' ' . $lang['days'] .  '</p>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			
			//travel agreements from
			$query = "SELECT flag, country_name, days, from_country_id FROM country c, travel_agreement ta
					  WHERE ta.from_country_id = c.country_id AND permission = TRUE AND ta.country_id = '$country_id'";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['travel_agreements_can_travel_from'] . '</p>';
		
			if($result->num_rows == 0) {
				echo "\n\t\t\t" . '<p>' . $lang['nobody_can_enter_this_country'] . '</p>';
			}
			while($row = $result->fetch_row()){
				list($flag, $country_name, $days, $from_country_id) = $row;

				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<img class="mini_flag country_margin_flag" alt="' . $country_name . '" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="ta_country_name" href="country?country_id=' . $from_country_id . 
									'">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '<p class="ta_days">' . $lang['expires_in'] . ' ' . $days . ' ' . $lang['days'] .  '</p>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			
			//defence agreements
			$query = "SELECT flag, country_name, days, c.country_id FROM country c, defence_agreements da
					  WHERE c.country_id = with_country_id AND da.country_id = '$country_id' AND is_allies = TRUE";
			$result = $conn->query($query);
			
			echo "\n\t\t" . '<div class="info_blocks info_blocks_no_border">' .
				 "\n\t\t\t" . '<p class="heads">' . $lang['defense_agreements'] . '</p>';
		
			if($result->num_rows == 0) {
				echo "\n\t\t\t" . '<p>' . $lang['this_country_has_no_allies'] . '</p>';
			}
			while($row = $result->fetch_row()){
				list($flag, $country_name, $days, $with_country_id) = $row;

				echo "\n\t\t\t" . '<div class="shi_details">' .
					 "\n\t\t\t\t" . '<img class="mini_flag country_margin_flag" alt="' . $country_name . '" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="ta_country_name" href="country?country_id=' . $with_country_id . 
									'">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '<p class="ta_days">' . $lang['expires_in'] . ' ' . $days . ' ' . $lang['days'] .  '</p>' .
				     "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
			
			
			echo "\n\t\t" . '</div>';
		?>
		
	</div>
	
</main>

<?php include('footer.php'); ?>