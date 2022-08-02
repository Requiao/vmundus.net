<?php
	//Description: Issue laws.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('../php_functions/string_validate.php'); //stringValidate($string, $min_len, $max_len, $str_name)
	include('../htmlpurifier/library/HTMLPurifier.auto.php');
	
	$config = HTMLPurifier_Config::createDefault();
	$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
	$config->set('Attr.AllowedClasses', '');
	$config->set('CSS.AllowedFonts', 'Arial, Helvetica, sans-serif, Comic Sans MS, cursive, Courier New, Courier, monospace,
				  Georgia, Lucida Sans Unicode, Lucida Grande, Tahoma, Geneva, Times New Roman, Times, serif, Trebuchet MS, Verdana');
	$config->set('CSS.MaxImgLength', '800px');
	$config->set('CSS.AllowedProperties', 'background-color, border, border-collapse, border-spacing, border-width, clear, color, float, font-family, font-size, font-style, font-weight, height, letter-spacing, line-height, margin, margin-bottom, margin-left, margin-right, margin-top, max-height, max-width, padding, padding-bottom, padding-left, padding-right, padding-top, table-layout, text-align, text-decoration, text-indent, width, word-spacing');
	$config->set('HTML.MaxImgLength', '800');
	$config->set('HTML.AllowedElements', 'a, abbr, address, b, bdo, blockquote, br, caption, cite, code, col, colgroup, dd, del, div, dl, dt, em, h1, h2, h3, h4, h5, h6, hr, i, img, kbd, li, ol, p, pre, q, s, samp, small, span, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var');
	$config->set('HTML.TargetBlank', true);
	
	$purifier = new HTMLPurifier($config);
	
	$what_law = htmlspecialchars(stripslashes(trim($_POST['what_law'])), ENT_QUOTES);
	$governor_position_id =  htmlspecialchars(stripslashes(trim($_POST['new_term_governor_id'])), ENT_QUOTES);
	$new_term =  htmlspecialchars(stripslashes(trim($_POST['new_term'])), ENT_QUOTES);
	$action_union =  htmlspecialchars(stripslashes(trim($_POST['action_union'])), ENT_QUOTES);//join or leave
	$union_id =  htmlspecialchars(stripslashes(trim($_POST['union_id'])), ENT_QUOTES);
	$as_founder =  htmlspecialchars(stripslashes(trim($_POST['as_founder'])), ENT_QUOTES);
	$new_union_leader =  htmlspecialchars(stripslashes(trim($_POST['new_union_leader'])), ENT_QUOTES);
	$union_name =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['union_name']))), ENT_QUOTES);
	$union_abbr =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['union_abbr']))), ENT_QUOTES);
	$color =  htmlspecialchars(stripslashes(trim($_POST['color'])), ENT_QUOTES);
	$amount =  htmlspecialchars(stripslashes(trim($_POST['amount'])), ENT_QUOTES);
	$from_country_id =  htmlspecialchars(stripslashes(trim($_POST['from_country_id'])), ENT_QUOTES);
	$product_id =  htmlspecialchars(stripslashes(trim($_POST['product_id'])), ENT_QUOTES);
	$days =  htmlspecialchars(stripslashes(trim($_POST['days'])), ENT_QUOTES);
	$tax =  htmlspecialchars(stripslashes(trim($_POST['tax'])), ENT_QUOTES);
	$import_perm_emb =  htmlspecialchars(stripslashes(trim($_POST['import_perm_emb'])), ENT_QUOTES);//give oermission or embargo
	$gov_position_id =  htmlspecialchars(stripslashes(trim($_POST['position_id'])), ENT_QUOTES);
	$responsibility_id =  htmlspecialchars(stripslashes(trim($_POST['responsibility_id'])), ENT_QUOTES);
	$can_vote =  htmlspecialchars(stripslashes(trim($_POST['can_vote'])), ENT_QUOTES);
	$can_issue =  htmlspecialchars(stripslashes(trim($_POST['can_issue'])), ENT_QUOTES);
	$add_rem_resp =  htmlspecialchars(stripslashes(trim($_POST['add_rem_resp'])), ENT_QUOTES);//add/remove gov responsibility
	$war_to_country_id =  htmlspecialchars(stripslashes(trim($_POST['war_to_country_id'])), ENT_QUOTES);
	$peace_with_country_id =  htmlspecialchars(stripslashes(trim($_POST['peace_with_country_id'])), ENT_QUOTES);
	$new_minister =  htmlspecialchars(stripslashes(trim($_POST['user_id'])), ENT_QUOTES);
	$new_tax_type =  htmlspecialchars(stripslashes(trim($_POST['new_tax_type'])), ENT_QUOTES);//product/income tax
	$travel_allow_ban =  htmlspecialchars(stripslashes(trim($_POST['travel_allow_ban'])), ENT_QUOTES);
	$building_id =  htmlspecialchars(stripslashes(trim($_POST['building_id'])), ENT_QUOTES);
	$price =  htmlspecialchars(stripslashes(trim($_POST['price'])), ENT_QUOTES);
	$build_perm_ban =  htmlspecialchars(stripslashes(trim($_POST['build_perm_ban'])), ENT_QUOTES);
	$defence_wth_country_id =  htmlspecialchars(stripslashes(trim($_POST['defence_wth_country_id'])), ENT_QUOTES);
	$salary =  htmlspecialchars(stripslashes(trim($_POST['salary'])), ENT_QUOTES);
	$currency_id =  htmlspecialchars(stripslashes(trim($_POST['currency_id'])), ENT_QUOTES);
	$timezone_id =  htmlspecialchars(stripslashes(trim($_POST['timezone_id'])), ENT_QUOTES);
	$rate =  htmlspecialchars(stripslashes(trim($_POST['rate'])), ENT_QUOTES);
	$type =  htmlspecialchars(stripslashes(trim($_POST['type'])), ENT_QUOTES);
	$credit_deposit_type =  htmlspecialchars(stripslashes(trim($_POST['credit_deposit_type'])), ENT_QUOTES);
	$new_manager =  htmlspecialchars(stripslashes(trim($_POST['user_id'])), ENT_QUOTES);
	$remove_country_id =  htmlspecialchars(stripslashes(trim($_POST['remove_country_id'])), ENT_QUOTES);
	$message = htmlspecialchars($purifier->purify(stripslashes(trim($_POST['message']))), ENT_QUOTES);
	$heading = htmlspecialchars(stripslashes(strip_tags(trim($_POST['heading']))), ENT_QUOTES);
	$add_remove =  htmlspecialchars(stripslashes(trim($_POST['add_remove'])), ENT_QUOTES);
	$profile_id =  htmlspecialchars(stripslashes(trim($_POST['user_id'])), ENT_QUOTES);
	$title_id =  htmlspecialchars(stripslashes(trim($_POST['title_id'])), ENT_QUOTES);
	$region_id =  htmlspecialchars(stripslashes(trim($_POST['region_id'])), ENT_QUOTES);
	$target_country_id =  htmlspecialchars(stripslashes(trim($_POST['country_id'])), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	if(!filter_var($what_law, FILTER_VALIDATE_INT)) {
			exit("0|Error");
	}

	//check if governor
	//check if president
	$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$is_governor = true;
		$row = $result->fetch_row();
		list($position_id, $country_id) = $row;
	}
	else { //check if congressman
		$query = "SELECT country_id FROM congress_details WHERE country_id = 
				 (SELECT country_id FROM congress_members WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$is_governor = true;
			$row = $result->fetch_row();
			list($country_id) = $row;
			$position_id = 3;
		}
		else {
			exit("0|You're not a governor and not allowed to perform this action.");
		}
	}
	
	//check if has rights to perform this action
	$query = "SELECT * FROM government_country_responsibilities 
			  WHERE country_id = '$country_id' AND responsibility_id = '$what_law' AND position_id = '$position_id'
			  AND have_access = TRUE";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit("0|You are not allowed to perform this action. You don't have appropriate permissions.");
	}
	
	//issue laws
	//change gov term length
	if($what_law == 1) {
		if(!filter_var($new_term, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for days.');
		}
		if(!filter_var($governor_position_id, FILTER_VALIDATE_INT)) {
			exit('0|You can only change term length for president and congress.');
		}
		$query = "SELECT * FROM government_positions WHERE position_id = '$governor_position_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit('0|Invalid governor position.');
		}
		if($new_term < 10 || $new_term > 50) {
			exit('0|Term length can only be between 10 and 50 days.');
		}
		$law = new ChangeGovTermLength($governor_position_id, $new_term);
		$law->proposeLaw();
	}
	// join/leave alliance
	else if($what_law == 2) {
		if(!is_numeric($union_id)) {
			if($union_id != 'leave') {
				exit("0|Error $union_id");
			}
			else {
				$union_id = 0;
			}
		}
		$law = new JoinLeaveUnion($union_id, $as_founder);
		$law->proposeLaw();
	}
	//Print money
	else if($what_law == 3) {
		if(!is_numeric($amount)) {
			exit("0|Invalid input for amount.");
		}
		if($amount < 100) {
			exit("0|Amount must be more than or equal to 100.");
		}
		if($amount > 10000) {
			exit("0|You are not allowed to print more than 10 000.");
		}
		$amount = round($amount);
		
		//check if country is owner of the currency id and select currency_id
		$query = "SELECT currency_id FROM country WHERE country_id = '$country_id' AND currency_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Your country doesn't have founder rights on currency it uses. You cannot print this currency.");
		}
		$row = $result->fetch_row();
		list($currency_id) = $row;
		
		//check if has enough paper
		$query = "SELECT amount FROM ministry_product WHERE position_id = '$position_id' AND country_id = '$country_id'
				  AND product_id = 25";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Your ministry doesn't have enough paper.");
		}
		$row = $result->fetch_row();
		list($ministry_paper) = $row;
		
		if($amount > $ministry_paper) {
			exit("0|Your ministry doesn't have enough paper.");
		}
		
		$law = new PrintMoney($amount, $currency_id, $position_id);
		$law->proposeLaw();
	}
	//Impeach President
	else if($what_law == 4) {
		$law = new ImpeachPresident();
		$law->proposeLaw();
	}
	//Dissolve Congress
	else if($what_law == 5) {
		$law = new DissolveCongress();
		$law->proposeLaw();
	}
	//Import permission/embargo.
	else if($what_law == 6) {
		if($import_perm_emb != 0 && $import_perm_emb != 1) {
			exit("0|Error.");
		}
		//check country
		if(!filter_var($from_country_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for country.');
		}
		$query = "SELECT * FROM country WHERE country_id = '$from_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		
		//check product
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Product doesn\'t exist.');
		}
		
		if($import_perm_emb == 0) {
			$days = 0;
			$tax = 0;
		}
		else if($import_perm_emb == 1) {
			if(!filter_var($days, FILTER_VALIDATE_INT)) {
				exit('0|Invalid input for days.');
			}
			if($days < 1) {
				exit("0|Days must be more than 0.");
			}
			if($days > 90) {
				exit("0|Days must be less than or equal to 90.");
			}
			
			if(!is_numeric($tax)) {
				exit("0|Invalid input for tax.");
			}
			$tax = round($tax, 2);
			if($tax < 1) {
				exit("0|Tax must be more than or equal to 1%.");
			}
			if($tax > 30) {
				exit("0|Tax must be less than or equal to 30%.");
			}
		}
		
		$law = new ImportPermissionEmbargo($from_country_id, $product_id, $days, $tax, $import_perm_emb);
		$law->proposeLaw();
	}
	//Change responsibilities.
	else if($what_law == 8) {
		if($add_rem_resp != 0 && $add_rem_resp != 1) {
			exit("0|Error.");
		}
		//check position_id
		if(!filter_var($gov_position_id, FILTER_VALIDATE_INT)) {
			exit('0|Government position doesn\'t exist.');
		}
		$query = "SELECT * FROM government_positions WHERE position_id = '$gov_position_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Government position doesn\'t exist.');
		}
		
		//check responsibility_id
		if(!filter_var($responsibility_id, FILTER_VALIDATE_INT)) {
			exit('0|Government responsibility doesn\'t exist.');
		}
		$query = "SELECT * FROM political_responsibilities WHERE responsibility_id = '$responsibility_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Government responsibility doesn\'t exist.');
		}
		
		if(($can_vote != 0 && $can_vote != 1) || ($can_issue != 0 && $can_issue != 1)) {
			exit("0|Error.");
		}
		if($can_vote == 0 && $can_issue == 0) {
			exit("0|Choose Vote or Issue.");
		}
		
		if($add_rem_resp == 0) {
			//check if position has this responsibility
			$query = "SELECT * FROM government_country_responsibilities WHERE country_id = '$country_id' 
					  AND position_id = '$gov_position_id' AND responsibility_id = '$responsibility_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|Governor with this position doesn\'t have that responsibility.');
			}
		}
		else if($add_rem_resp == 1) {
			//if changing for congress, check responsibilities that congress cannot have access to
			if($gov_position_id == 3 && $can_issue == 1) {
				$black_list_resp[0] = 3;//Print money
				$black_list_resp[1] = 23;//Start/Manage battle
				$black_list_resp[2] = 33;//Research
				for($x = 0; $x < count($black_list_resp); $x++) {
					if($responsibility_id == $black_list_resp[$x]) {
						exit("0|Congress is not allowed to have access to this law.");
					}
				}
			}
		}
		$law = new ChangeResponsibilities($add_rem_resp, $gov_position_id, $responsibility_id, $can_vote, $can_issue);
		$law->proposeLaw();
	}
	//Declare war
	else if($what_law == 9) {
		//check country
		if(!filter_var($war_to_country_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM country WHERE country_id = '$war_to_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		$law = new DeclareWar($war_to_country_id);
		$law->proposeLaw();
	}
	//Sign peace treaty.
	else if($what_law == 10) {
		//check country
		if(!filter_var($peace_with_country_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM country WHERE country_id = '$peace_with_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		$law = new SignPeaceTreaty($peace_with_country_id);
		$law->proposeLaw();
	}
	//Assign ministers. Assign Prime Minister
	else if($what_law == 11 || $what_law == 12) {
		if($what_law == 11) {
			//check position_id
			if(!filter_var($gov_position_id, FILTER_VALIDATE_INT)) {
				exit('0|Invalid input for country.');
			}
			$query = "SELECT * FROM government_positions WHERE position_id = '$gov_position_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|Government position doesn\'t exist.');
			}
		}
		else {
			$gov_position_id = 2;//PM
		}
		//check minister_id
		if(!is_numeric($new_minister)) {
			exit('0|Invalid input for user id.');
		}
		
		if($new_minister != 0) {
			$query = "SELECT * FROM users WHERE user_id = '$new_minister'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|User with this user id doesn\'t exist.');
			}
			
			//check if governor
			//check if president
			$query = "SELECT * FROM country_government WHERE user_id = '$new_minister'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				exit("0|This user is already a governor. He cannot be assigned on this position.");
			}
			else { //check if congressman
				$query = "SELECT * FROM congress_details WHERE country_id = 
						 (SELECT country_id FROM congress_members WHERE user_id = '$new_minister')";
				$result = $conn->query($query);
				if($result->num_rows == 1) { 
					exit("0|This user is a congressman. He cannot be assigned on this position.");
				}
			}
			
			//new minister must be a citizen of a country
			$query = "SELECT user_id FROM user_profile WHERE citizenship = '$country_id' AND user_id = '$new_minister'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|A new minister must be a citizen of a country.");
			}
		}
		else {
			//check if minister is assigned
			$query = "SELECT * FROM country_government WHERE position_id = '$gov_position_id' AND user_id IS NOT NULL 
					  AND country_id = '$country_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|There is no minister on this position.");
			}
		}
		if($what_law == 11) {
			$law = new AssignMinisters($new_minister, $gov_position_id);
		}
		else if($what_law == 12) {
			$law = new AssignPrimeMinister($new_minister, 2);
		}
		$law->proposeLaw();
	}
	//Change taxes.
	else if($what_law == 13) {
		if($new_tax_type != 0 && $new_tax_type != 1) {
			exit("0|Error.");
		}
		
		if($new_tax_type == 1) {//product
			//check product
			if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
				exit();
			}
			$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|Product doesn\'t exist.');
			}
		}
		
		if(!is_numeric($tax)) {
			exit("0|Invalid input for tax.");
		}
		$tax = round($tax, 2);
		if($tax < 1) {
			exit("0|Tax must be more than or equal to 1%.");
		}
		if($tax > 25) {
			exit("0|Tax must be less than or equal to 25%.");
		}
		
		$law = new ChangeTaxes($product_id, $tax, $new_tax_type);
		$law->proposeLaw();
	}
	//Travel agreement
	else if($what_law == 14) {
		if($travel_allow_ban != 0 && $travel_allow_ban != 1) {
			exit("0|Error.");
		}
		exit('0|This option is not used anymore. Soon Work agreement will replace it.');
		if($travel_allow_ban == 0) {
			$days = 0;
			
			//check country
			if(!filter_var($from_country_id, FILTER_VALIDATE_INT)) {
				exit('0|Invalid input for country.');
			}
			$query = "SELECT * FROM travel_agreement WHERE country_id = '$country_id' AND from_country_id = '$from_country_id'
					  AND permission = TRUE";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|You don\'t have travel agreement with this country.');
			}
		}
		else if($travel_allow_ban == 1) {
			if(!filter_var($days, FILTER_VALIDATE_INT)) {
				exit('0|Invalid input for days.');
			}
			if($days < 1) {
				exit("0|Days must be more than 0.");
			}
			if($days > 90) {
				exit("0|Days must be less than or equal to 90.");
			}
			
			//check country
			if(!filter_var($from_country_id, FILTER_VALIDATE_INT)) {
				exit('0|Invalid input for country.');
			}
			$query = "SELECT * FROM country WHERE country_id = '$from_country_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|Country doesn\'t exist.');
			}
		}
		
		$law = new TravelAgreement($from_country_id, $days, $travel_allow_ban);
		$law->proposeLaw();
	}
	//Give/Ban permission for foreigners to build companies.
	else if($what_law == 15) {
		if($build_perm_ban != 0 && $build_perm_ban != 1) {
			exit("0|Error.");
		}
		//check country
		if(!filter_var($from_country_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for country.');
		}
		$query = "SELECT * FROM country WHERE country_id = '$from_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		
		//check building
		if(!filter_var($building_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM building_info WHERE building_id = '$building_id' AND product_id IS NOT NULL";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Building doesn\'t exist.');
		}
		
		if($build_perm_ban == 0) {
			$price = 0;
		}
		else if($build_perm_ban == 1) {	
			if(!is_numeric($price)) {
				exit("0|Invalid input for price.");
			}
			$price = round($price, 2);
			if($price <= 0) {
				exit("0|Price must be more than 0 Gold.");
			}
			if($price > 10) {
				exit("0|Price must be less than or equal to 10 Gold.");
			}
		}
		
		$law = new BanPermitForeignersBuild($from_country_id, $building_id, $price, $build_perm_ban);
		$law->proposeLaw();
	}
	//Assign new union leader
	else if($what_law == 16) {
		if(!is_numeric($new_union_leader)) {
			exit("0|User with this user id doesn't exist.");
		}
		$law = new ChangeUnionLeader($new_union_leader);
		$law->proposeLaw();
	}
	//Change government salaries.
	else if($what_law == 17) {
		//check position_id
		if(!filter_var($gov_position_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for position.');
		}
		$query = "SELECT * FROM government_positions WHERE position_id = '$gov_position_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Government position doesn\'t exist.');
		}
		
		//check salary
		if(!is_numeric($salary)) {
			exit("0|Invalid input for salary.");
		}
		$salary = round($salary, 2);
		if($salary <= 0) {
			exit("0|Salary must be more than 0.");
		}
		if($salary > 1000) {
			exit("0|Salary must be less than or equal to 1 000.");
		}
		
		$law = new ChangeGovernmentSalaries($gov_position_id, $salary);
		$law->proposeLaw();
	}
	//Change credit/deposit rate.
	else if($what_law == 18) {
		//check rate
		if(!is_numeric($rate)) {
			exit("0|Invalid input for rate.");
		}
		$rate = round($rate, 2);
		if($rate < 1) {
			exit("0|Rate must be more than or equal to 1%.");
		}
		if($rate > 10) {
			exit("0|Rate must be less than or equal to 10%.");
		}
		
		//check type
		if($credit_deposit_type != 'deposit' && $credit_deposit_type != 'credit') {
			exit("0|Chose Deposit or Credit.");
		}
		
		//check credit_deposit_type
		if($type != 'currency' && $type != 'gold') {
			exit("0|Chose Currency or Gold.");
		}
		
		$law = new ChangeCreditDepositRate($rate, $type, $credit_deposit_type);
		$law->proposeLaw();
	}
	//Create new union
	else if($what_law == 19) {
		stringValidate($union_name, 1, 20, 'Union name');
		stringValidate($union_abbr, 1, 5, 'Union abbreviation');
		
		//check color
		$color_reg = '/^rgb\((\d{0,3}),\s*(\d{0,3}),\s*(\d{0,3})\)$/';
		if(!preg_match($color_reg, $color)) {
			exit("0|Invalid input for color.");
		}
		$query = "SELECT union_id FROM unions WHERE color = '$color'
				  UNION
				  SELECT union_id FROM create_new_union WHERE color = '$color'
				  AND law_id NOT IN (SELECT law_id FROM country_law_info WHERE is_processed = TRUE)";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|Union with this color already exist.");
		}
		
		//check if color is allowed
		$pattern = '/[rgb\(\) ]?/';
		$replacement = '';
		$clean_rgb = preg_replace($pattern, $replacement, $color);
		$rgb_colors = preg_split('/,/', $clean_rgb);
		$alert = 0;
		for($x = 0; $x < 3; $x++) {
			if($rgb_colors[$x] < 50) {
				$alert++;
			}
		}
		if($alert >= 3) {
			exit("0|Color is too dark. At least one color have to have value 50 and more in RGB(Red, Green, Blue)." .
				 " Current value is $color");
		}

		//check if name not duplicate
		$query = "SELECT union_id FROM unions WHERE union_name = '$union_name'
				  UNION
				  SELECT union_id FROM create_new_union WHERE union_name = '$union_name'
				  AND law_id NOT IN (SELECT law_id FROM country_law_info WHERE is_processed = TRUE)";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			exit("0|Union with this name already exist.");
		}
		
		//check if abbreviation is not duplicate
		$query = "SELECT union_id FROM unions WHERE abbreviation = '$union_abbr'
				  UNION
				  SELECT union_id FROM create_new_union WHERE abbreviation = '$union_abbr'
				  AND law_id NOT IN (SELECT law_id FROM country_law_info WHERE is_processed = TRUE)";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			exit("0|Union with this abbreviation already exist.");
		}
		
		$law = new CreateNewUnion($union_name, $union_abbr, $color);
		$law->proposeLaw();
	}
	//Assign new Secretary of the Treasury.
	else if($what_law == 20) {
		if(!is_numeric($new_manager)) {
			exit('0|Invalid input for user id.');
		}
		
		if($new_manager != 0) {
			$query = "SELECT * FROM users WHERE user_id = '$new_manager'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit('0|User doesn\'t exist.');
			}
			
			//check if citizen of the country
			$query = "SELECT * FROM user_profile WHERE user_id = '$new_manager' AND citizenship = '$country_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) { 
				exit("0|The user must have the citizenship of your country.");
			}
			
			//get old manager
			$query = "SELECT user_id FROM bank_details WHERE country_id = '$country_id'";
			$result_law = $conn->query($query);
			$row_law = $result_law->fetch_row();
			list($old_manager) = $row_law;
			
			if($old_manager == $new_manager) {
				exit("0|This user is already assigned to this position.");
			}
			
			//check salary
			if(!is_numeric($salary)) {
				exit("0|Invalid input for salary.");
			}
			$salary = round($salary, 2);
			if($salary <= 0) {
				exit("0|Salary must be more than 0.");
			}
			if($salary > 1000) {
				exit("0|Salary must be less than or equal to 1 000.");
			}
		}
		else {
			//check if manager is assigned
			$query = "SELECT * FROM bank_details WHERE country_id = '$country_id' AND user_id IS NULL";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Secretary of the Treasury is not assigned.");
			}
			
			$salary = 0;
		}
		
		$law = new AssignNewBankManager($new_manager, $salary);
		$law->proposeLaw();
	}
	//Change production taxes
	else if($what_law == 21) {
		//check product
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Product doesn\'t exist.');
		}
		
		if(!is_numeric($tax)) {
			exit("0|Invalid input for tax.");
		}
		$tax = round($tax, 2);
		if($tax < 1) {
			exit("0|Tax must be more than or equal to 1%.");
		}
		if($tax > 10) {
			exit("0|Tax must be less than or equal to 10%.");
		}
		
		$law = new ChangeProductionTaxes($product_id, $tax);
		$law->proposeLaw();
	}
	//Budget Allocation.
	else if($what_law == 22) {
		//check position_id
		if(!filter_var($gov_position_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for position.');
		}
		$query = "SELECT * FROM government_positions WHERE position_id = '$gov_position_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Government position doesn\'t exist.');
		}
		
		//cannot allocate to congress
		if($gov_position_id == 3) {
			exit('0|Congress cannot have its own budget.');
		}
		
		//check currency_id
		if(!filter_var($currency_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for position.');
		}
		$query = "SELECT * FROM currency WHERE currency_id = '$currency_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Currency doesn\'t exist.');
		}
		
		//check amount
		if(!is_numeric($amount)) {
			exit("0|Invalid input for amount.");
		}
		$amount = round($amount, 2);
		if($amount <= 0) {
			exit("0|Amount must be more than 0.");
		}
		if($amount > 10000) {
			exit("0|Amount must be less than or equal to 10 000.");
		}
		
		$law = new BudgetAllocation($gov_position_id, $amount, $currency_id);
		$law->proposeLaw();
	}
	//Sign defence agreement.
	else if($what_law == 24) {
		//check country
		if(!filter_var($defence_wth_country_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM country WHERE country_id = '$defence_wth_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		
		//check days
		if(!filter_var($days, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for days.');
		}
		if($days < 1) {
			exit("0|Days must be more than 0.");
			}
		if($days > 90) {
			exit("0|Days must be less than or equal to 90.");
		}
		
		
		$law = new DefenceAgreement($defence_wth_country_id, $days);
		$law->proposeLaw();
	}
	//Product Allocation.
	else if($what_law == 25) {
		//check position_id
		if(!filter_var($gov_position_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for position.');
		}
		$query = "SELECT * FROM government_positions WHERE position_id = '$gov_position_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Government position doesn\'t exist.');
		}
		
		//cannot allocate to congress
		if($gov_position_id == 3) {
			exit('0|Congress cannot have its own products.');
		}
		
		//check product_id
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit('0|Invalid input for position.');
		}
		$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Product doesn\'t exist.');
		}
		
		//check amount
		if(!is_numeric($amount)) {
			exit("0|Invalid input for amount.");
		}
		$amount = round($amount, 2);
		if($amount < 0.01) {
			exit("0|Amount must be more than or equal to 1.");
		}
		if($amount > 1000) {
			exit("0|Amount must be less than or equal to 1 000.");
		}
		
		$law = new ProductAllocation($gov_position_id, $amount, $product_id);
		$law->proposeLaw();
	}
	//Change timezone.
	else if($what_law == 26) {
		//check country
		if(!filter_var($timezone_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM timezones WHERE timezone_id = '$timezone_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Timezone doesn\'t exist.');
		}
		
		$law = new ChangeTimezone($timezone_id);
		$law->proposeLaw();
	}
	//Price change to build companies for citizens.
	else if($what_law == 27) {	
		//check building
		if(!filter_var($building_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM building_info WHERE building_id = '$building_id' AND product_id IS NOT NULL";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Building doesn\'t exist.');
		}

		if(!is_numeric($price)) {
			exit("0|Invalid input for price.");
		}
		
		$price = round($price, 2);
		
		if($price <= 0) {
			exit("0|Price must be more than 0 Gold.");
		}
		if($price > 5) {
			exit("0|Price must be less than or equal to 5 Gold.");
		}
		
		
		$law = new BuildingPriceForCitizens($building_id, $price);
		$law->proposeLaw();
	}
	//Remove country from the union
	else if($what_law == 29) {
		//check country
		if(!filter_var($remove_country_id, FILTER_VALIDATE_INT)) {
			exit('0|Country doesn\'t exist.');
		}
		$query = "SELECT * FROM country WHERE country_id = '$remove_country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		
		if($remove_country_id == $country_id) {
			exit('0|You are not allowed to expel your own country from the union.');
		}
		
		$law = new RemoveCountryFromUnion($remove_country_id);
		$law->proposeLaw();
	}
	//Welcome message
	else if($what_law == 30) {
		stringValidate($heading, 1, 30, 'Heading');
		stringValidate($message, 1, 21000, 'Message');
		
		$law = new ChangeWelcomeMessage($heading, $message);
		$law->proposeLaw();
	}
	//Assign titles
	else if($what_law == 31) {
		//check title
		if(!filter_var($title_id, FILTER_VALIDATE_INT)) {
			exit('0|Title doesn\'t exist.');
		}
		$query = "SELECT * FROM titles WHERE title_id = '$title_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Title doesn\'t exist.');
		}
		
		//check user
		if(!filter_var($profile_id, FILTER_VALIDATE_INT)) {
			exit('0|User doesn\'t exist.');
		}
		$query = "SELECT * FROM users WHERE user_id = '$profile_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|User doesn\'t exist.');
		}
		
		//check citizenship
		$query = "SELECT * FROM user_profile WHERE user_id = '$profile_id' AND citizenship = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|The user must have citizenship of your country.');
		}
		
		if($add_remove != 'add' && $add_remove != 'remove') {
			exit('0|You can only add or remove a title.');
		}
		
		$law = new AssignTitles($title_id, $profile_id, $add_remove);
		$law->proposeLaw();
	}
	//Change Capital Region
	else if($what_law == 34) {
		//check region
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM regions WHERE region_id = '$region_id' AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|This region does not belong to your country.');
		}
		$law = new ChangeCapitalRegion($region_id);
		$law->proposeLaw();
	}
	//Change Region Owner
	else if($what_law == 35) {
		//check region
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM regions WHERE region_id = '$region_id' AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|This region does not belong to your country.');
		}
		
		//check country
		if(!filter_var($target_country_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		$query = "SELECT * FROM country WHERE country_id = '$target_country_id' AND country_id != '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) { 
			exit('0|Country doesn\'t exist.');
		}
		
		//check if new owner is the rightful owner
		$rightful_owner = false;
		$query = "SELECT * FROM regions WHERE rightful_owner = '$target_country_id' AND region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$rightful_owner = true;
		}
		
		//test price
		if(!is_numeric($price) || $price < 5) {
			exit('0|Price must be more than 5.');
		}
		
		if($price < 5 && !$rightful_owner) {
			exit('0|Price for the new owner that does not have Rightful Owner rights must be more than 5.');
		}
		
		if($price > 25) {
			exit('0|Price must be less than 25.');
		}
		
		$law = new ChangeRegionOwner($region_id, $target_country_id, $price);
		$law->proposeLaw();
	}
	else {
		exit("0|Invalid request.");
	}
	
	
	#==============================#
	#==============================#
	class RegisterLaw {
		protected $law_id;
		protected $responsibility_id;
		protected $user_id;
		protected $country_id;
		protected $conn;
		
		protected function registerVariables() {
			global $user_id;
			global $country_id;
			global $conn;
			$this->user_id = $user_id;
			$this->country_id = $country_id;
			$this->conn = $conn;
		}
		
		protected function makeLaw() {
			$this->law_id = getTimeForId() . $this->user_id;
			//propose/issue law
			$query = "INSERT INTO country_law_info VALUES('$this->law_id', '$this->country_id', '$this->responsibility_id', 0, 0, 
														  '$this->user_id', CURRENT_DATE, CURRENT_TIME, 0)";
			$this->conn->query($query);
		}
		
		protected function notifyToVote() {
			//notify everyone else who must sign/vote for the law
			$query = "SELECT user_id FROM country_government WHERE country_id = '$this->country_id' AND position_id IN 
					 (SELECT position_id FROM government_country_responsibilities WHERE country_id = '$this->country_id' 
					  AND responsibility_id = '$this->responsibility_id' AND must_sign_vote = true) AND user_id IS NOT NULL
					  UNION
					  SELECT user_id FROM congress_members WHERE country_id = 
					 (SELECT country_id FROM government_country_responsibilities WHERE country_id = '$this->country_id'
					  AND responsibility_id = '$this->responsibility_id' AND must_sign_vote = true AND position_id = 3)";
			$result = $this->conn->query($query);
			while($row = $result->fetch_row()) {
				list($governor_id) = $row;
				$notification = "A new law has been proposed.";
				sendNotification($notification, $governor_id);
			}
			echo "1|New law have been successfully proposed.";
		}
		
		protected function checkIfLawProposed() {
			//determine if this kind of law already proposed.
			$query = "SELECT * FROM country_law_info WHERE is_processed = FALSE AND responsibility_id = $this->responsibility_id 
					  AND country_id = '$this->country_id'";
			$result = $this->conn->query($query);
			if($result->num_rows >= 1) {
				exit("0|This kind of law is already proposed.");
			}
		}
	}
	
	#==============================#
	#==============================#
	class ChangeGovTermLength extends RegisterLaw {
		private $governor_position;
		private $new_term;
		
		public function __construct($governor_position, $new_term) {
			$this->governor_position = $governor_position;
			$this->new_term = $new_term;
			$this->responsibility_id = 1;
			$this->registerVariables();
		}
		public function proposeLaw() {
			//check if not duplicate
			$query = "SELECT * FROM country_law_info WHERE is_processed = FALSE AND responsibility_id = $this->responsibility_id 
					  AND country_id = '$this->country_id' AND law_id IN
					  (SELECT law_id FROM change_gov_term_length WHERE position_id = '$this->governor_position')";
			$result = $this->conn->query($query);
			if($result->num_rows >= 1) {
				exit("0|This kind of law is already proposed.");
			}
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO change_gov_term_length VALUES('$this->law_id', '$this->governor_position', '$this->new_term')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class JoinLeaveUnion extends RegisterLaw {
		private $union_id;
		private $as_founder;
		
		public function __construct($union_id, $as_founder) {
			$this->union_id = $union_id;
			$this->as_founder = $as_founder;
			$this->responsibility_id = 2;
			$this->registerVariables();
		}
		public function proposeLaw() {
			if($this->as_founder != 0 && $this->as_founder != 1) {
				exit("0|You can only join with or without founder rights.");
			}
			
			$this->checkIfLawProposed();
			
			//join or leave
			if($this->union_id == 'leave') {
				//detrmine country union
				$query = "SELECT union_id FROM country_unions WHERE country_id = '$this->country_id'";
				$result_unions = $this->conn->query($query);
				if($result_unions->num_rows != 1) {
					exit("0|Your country is not a member of a union.");
				}
				$row_unions = $result_unions->fetch_row();
				list($this->union_id) = $row_unions;
				
				//register data about the law
				$this->makeLaw();
				$query = "INSERT INTO join_leave_union VALUES('$this->law_id', '$this->country_id', '$this->union_id', 0, 0)";
				$this->conn->query($query);
			}
			else {//join
				//determine if not creating new union
				$query = "SELECT * FROM country_law_info WHERE is_processed = 0 AND responsibility_id = 19 AND country_id = '$this->country_id'";
				$result_unions = $this->conn->query($query);
				if($result_unions->num_rows == 1) {
					exit("0|Law to create a union is proposed. You cannot join new union at this moment.");
				}
			
				//determine if not already a member of an union
				$query = "SELECT * FROM country_unions WHERE country_id = '$this->country_id'";
				$result_unions = $this->conn->query($query);
				if($result_unions->num_rows == 1) {
					exit("0|Your country is already a member of a union.");
				}
				
				//check if other countries is voting to except new union member
				$query = "SELECT * FROM processing_union_application WHERE founder_law_id IN 
						 (SELECT law_id FROM country_law_info WHERE is_processed = FALSE AND responsibility_id = 7)
						  AND applicant_law_id IN (SELECT law_id FROM join_leave_union WHERE country_id = '$this->country_id')";
				$result_unions = $this->conn->query($query);
				if($result_unions->num_rows != 0) {
					exit("0|Your country already submitted application to join a union.");
				}
				
				//check if union exists
				$query = "SELECT * FROM unions WHERE union_id = '$this->union_id'";
				$result_unions = $this->conn->query($query);
				if($result_unions->num_rows != 1) {
					exit("0|Union doesn't exist.");
				}
				
				//check if law to join union already proposed
				$query = "SELECT * FROM country_law_info WHERE is_processed = 0 AND responsibility_id = 2 AND country_id = '$this->country_id'";
				$result_unions = $this->conn->query($query);
				if($result_unions->num_rows == 1) {
					exit("0|Law to join a union is already proposed.");
				}
				
				//register data about the law
				$this->makeLaw();
				$query = "INSERT INTO join_leave_union VALUES('$this->law_id', '$this->country_id', '$this->union_id', 1, 
						 '$this->as_founder')";
				$this->conn->query($query);
			}
	
			$this->notifyToVote();
		}
	}

	#==============================#
	#==============================#
	class PrintMoney extends RegisterLaw {
		private $amount;
		private $currency_id;
		private $position_id;
		
		public function __construct($amount, $currency_id, $position_id) {
			$this->amount = $amount;
			$this->currency_id = $currency_id;
			$this->position_id = $position_id;
			$this->responsibility_id = 3;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//determine if this kind of law already proposed.
			$this->checkIfLawProposed();
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO print_money VALUES('$this->law_id', '$this->currency_id', '$this->amount', '$this->position_id')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class ImpeachPresident extends RegisterLaw {	
		public function __construct() {
			$this->responsibility_id = 4;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//determine if this kind of law already proposed
			//determine if this kind of law already proposed.
			$this->checkIfLawProposed();
			
			//register data about the law
			$this->makeLaw();
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class DissolveCongress extends RegisterLaw {	
		public function __construct() {
			$this->responsibility_id = 5;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//determine if this kind of law already proposed.
			$this->checkIfLawProposed();
			
			//register data about the law
			$this->makeLaw();
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class ImportPermissionEmbargo extends RegisterLaw {
		private $from_country_id;
		private $product_id;
		private $days;
		private $tax;
		private $import_perm_emb;
		
		public function __construct($from_country_id, $product_id, $days, $tax, $import_perm_emb) {
			$this->from_country_id = $from_country_id;
			$this->product_id = $product_id;
			$this->days = $days;
			$this->tax = $tax;
			$this->import_perm_emb = $import_perm_emb;
			$this->responsibility_id = 6;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			if($this->import_perm_emb == 0) {
				$query = "SELECT * FROM product_import_tax WHERE country_id = '$this->country_id'
						  AND from_country_id = '$this->from_country_id' AND product_id = '$this->product_id'
						  AND permission = TRUE";
				$result = $this->conn->query($query);
				if($result->num_rows == 0) { 
					exit("0|Citizens of this country are not allowed to import these products to your country.");
				}
			}
			//check if law not already proposed
			$query = "SELECT * FROM product_import_law_info WHERE from_country_id = '$this->from_country_id' AND
					  product_id = '$this->product_id' AND law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Import law for this country and product already proposed.");
			}

			//register info about law
			$this->makeLaw();
			
			$query = "INSERT INTO product_import_law_info VALUES('$this->law_id', '$this->from_country_id', '$this->tax',
					 '$this->product_id', '$this->days', '$this->import_perm_emb')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class ChangeResponsibilities extends RegisterLaw {
		private $add_rem_resp;
		private $position_id;
		private $gov_responsibility_id;
		private $can_vote;
		private $can_issue;
		
		public function __construct($add_rem_resp, $position_id, $gov_responsibility_id, $can_vote, $can_issue) {
			$this->add_rem_resp = $add_rem_resp;
			$this->position_id = $position_id;
			$this->gov_responsibility_id = $gov_responsibility_id;
			$this->can_vote = $can_vote;
			$this->can_issue = $can_issue;
			$this->responsibility_id = 8;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			if($this->gov_responsibility_id == 8) {
				//check if at least 1 governor will be left with ability to change responsibilities
				if($this->add_rem_resp == 0) {
					$query = "SELECT COUNT(*) FROM government_country_responsibilities 
							  WHERE country_id = '$this->country_id' AND responsibility_id = 8 AND must_sign_vote = 1 AND have_access = 1";
					$result = $this->conn->query($query);
					$row = $result->fetch_row();
					list($left_quantity) = $row;
					if($left_quantity == 1) { 
						exit("0|Error. At least one governor must have access to issue and vote for this law.");
					}

					//check if law that will remove all responsibilities is in proccess
					$query = "SELECT COUNT(*) FROM change_responsibilities WHERE
							  responsibility_id = 8 AND law_id IN (SELECT law_id FROM country_law_info 
							  WHERE responsibility_id = 8 AND is_processed = 0 AND country_id = '$this->country_id')";
					$result = $this->conn->query($query);
					$row = $result->fetch_row();
					list($in_process_quantity) = $row;
					if(($left_quantity - $in_process_quantity) <= 1) {
						exit("0|Error. After issuing this law, there will be a chance that all governors will lose access to" .
							 " this responsibility.");
					}
				}
			}
			
			$query = "SELECT * FROM change_responsibilities WHERE position_id = '$this->position_id' AND
					  responsibility_id = '$this->gov_responsibility_id' AND law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Change responsibility law for this position and responsibility already proposed.");
			}

			//register info about law
			$this->makeLaw();
			
			$query = "INSERT INTO change_responsibilities VALUES('$this->law_id', '$this->position_id', '$this->gov_responsibility_id',
					 '$this->can_vote', '$this->add_rem_resp', '$this->can_issue')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class DeclareWar extends RegisterLaw {
		private $war_to_country_id;
		
		public function __construct($war_to_country_id) {
			$this->war_to_country_id = $war_to_country_id;
			$this->responsibility_id = 9;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM declare_war WHERE with_country_id = '$this->war_to_country_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Declare war law to this country is already proposed.");
			}
			
			//check if there's no war between countries
			$query = "SELECT * FROM country_wars WHERE ((with_country_id = '$this->war_to_country_id'
					  AND country_id = '$this->country_id' AND active = TRUE) OR (with_country_id = '$this->country_id'
					  AND country_id = '$this->war_to_country_id' AND active = TRUE)) AND is_resistance = FALSE";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Your country is already in war with this country.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO declare_war VALUES('$this->law_id', '$this->war_to_country_id')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class SignPeaceTreaty extends RegisterLaw {
		private $peace_with_country_id;
		
		public function __construct($peace_with_country_id) {
			$this->peace_with_country_id = $peace_with_country_id;
			$this->responsibility_id = 10;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if in war with that country
			$query = "SELECT * FROM country_wars WHERE (with_country_id = '$this->peace_with_country_id'
					  AND country_id = '$this->country_id' AND active = TRUE AND is_resistance = FALSE) 
					  OR (with_country_id = '$this->country_id'
					  AND country_id = '$this->peace_with_country_id' AND active = TRUE AND is_resistance = FALSE)";
			$result = $this->conn->query($query);
			if($result->num_rows == 0) { 
				exit("0|You don't have war with this country.");
			}
			
			//check if law not already proposed
			$query = "SELECT * FROM sign_peace_treaty WHERE with_country_id = '$this->peace_with_country_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Sign peace treaty law with this country is already proposed.");
			}
			
			//check if law not already proposed from other side
			$query = "SELECT * FROM sign_peace_treaty WHERE with_country_id = '$this->country_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->peace_with_country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|This country already proposed law to sign peace treaty with your country.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO sign_peace_treaty VALUES('$this->law_id', '$this->peace_with_country_id', 0)";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	

	#==============================#
	#==============================#
	class AssignMinisters extends RegisterLaw {
		private $minister_id;
		private $gov_position_id;
		
		public function __construct($minister_id, $gov_position_id) {
			$this->minister_id = $minister_id;
			$this->gov_position_id = $gov_position_id;
			$this->responsibility_id = 11;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM assign_ministers_law WHERE position_id = '$this->gov_position_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Assign minister law for this position already proposed.");
			}

			//determine if this user is already proposed on this position
			$query = "SELECT * FROM assign_ministers_law WHERE user_id = '$this->minister_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|This user is already proposed on one of the ministry positions.");
			}
			
			//register data about the law
			$this->makeLaw();
			if($this->minister_id == 0) {
				$query = "INSERT INTO assign_ministers_law VALUES('$this->law_id', '$this->gov_position_id', NULL, 0)";
			}
			else {
				$query = "INSERT INTO assign_ministers_law VALUES('$this->law_id', '$this->gov_position_id', '$this->minister_id', 1)";
			}
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class AssignPrimeMinister extends AssignMinisters {
		public function __construct($minister_id, $gov_position_id) {
			parent::__construct($minister_id, $gov_position_id);
			$this->responsibility_id = 12;
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeTaxes extends RegisterLaw {
		private $product_id;
		private $tax;
		private $new_tax_type;
		
		public function __construct($product_id, $tax, $new_tax_type) {
			$this->product_id = $product_id;
			$this->tax = $tax;
			$this->new_tax_type = $new_tax_type;
			$this->responsibility_id = 13;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			if($this->new_tax_type == 1) {
				$query = "SELECT * FROM new_tax_law WHERE product_id = '$this->product_id' AND law_id IN 
						(SELECT law_id FROM country_law_info WHERE responsibility_id = '$this->responsibility_id' 
						  AND is_processed = 0 AND country_id = '$this->country_id')
						  AND type = '$this->new_tax_type'";
			}
			else if($this->new_tax_type == 0) {
				$query = "SELECT * FROM new_tax_law WHERE product_id IS NULL AND law_id IN 
						 (SELECT law_id FROM country_law_info WHERE responsibility_id = '$this->responsibility_id' 
						  AND is_processed = 0 AND country_id = '$this->country_id')
						  AND type = '$this->new_tax_type'";
			}
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Law to change tax for this product/income already proposed.");
			}

			//register info about law
			$this->makeLaw();
			if($this->new_tax_type == 1) {
				$query = "INSERT INTO new_tax_law VALUES('$this->law_id', '$this->tax', '$this->product_id', '$this->new_tax_type')";
			}
			else if($this->new_tax_type == 0) {
				$query = "INSERT INTO new_tax_law VALUES('$this->law_id', '$this->tax', NULL, '$this->new_tax_type')";
			}
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class TravelAgreement extends RegisterLaw {
		private $from_country_id;
		private $days;
		private $travel_allow_ban;
		
		public function __construct($from_country_id, $days, $travel_allow_ban) {
			$this->from_country_id = $from_country_id;
			$this->days = $days;
			$this->travel_allow_ban = $travel_allow_ban;
			$this->responsibility_id = 14;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			if($this->travel_allow_ban == 0) {
				$query = "SELECT * FROM travel_agreement WHERE country_id = '$this->country_id'
						  AND from_country_id = '$this->from_country_id'
						  AND permission = TRUE";
				$result = $this->conn->query($query);
				if($result->num_rows == 0) { 
					exit("0|Citizens of this country are not allowed to travel into your country.");
				}
			}
			
			//check if law not already proposed
			$query = "SELECT * FROM travel_agreement_law WHERE from_country_id = '$this->from_country_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Travel agreement law for this country already proposed.");
			}

			//register info about law
			$this->makeLaw();
			
			$query = "INSERT INTO travel_agreement_law VALUES('$this->law_id', '$this->from_country_id', '$this->days', 
					 '$this->travel_allow_ban')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	

	#==============================#
	#==============================#
	class BanPermitForeignersBuild extends RegisterLaw {
		private $from_country_id;
		private $building_id;
		private $price;
		private $build_perm_ban;
		
		public function __construct($from_country_id, $building_id, $price, $build_perm_ban) {
			$this->from_country_id = $from_country_id;
			$this->building_id = $building_id;
			$this->price = $price;
			$this->build_perm_ban = $build_perm_ban;
			$this->responsibility_id = 15;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			if($this->build_perm_ban == 0) {
				$query = "SELECT * FROM foreign_building_policy WHERE country_id = '$this->country_id' 
						  AND foreign_country = '$this->from_country_id' AND building_id = '$this->building_id'
						  AND foreigners = TRUE";
				$result = $this->conn->query($query);
				if($result->num_rows == 0) {
					exit("0|Citizens of this country don't have permission to build this company in your country.");
				}
			}
			
			//check if law not already proposed
			$query = "SELECT * FROM foreign_building_policy_law WHERE foreign_country_id = '$this->from_country_id' AND
					  building_id = '$this->building_id' AND law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Give/Ban permission law to build companies for this country and building already proposed.");
			}

			//register info about law
			$this->makeLaw();
			
			$query = "INSERT INTO foreign_building_policy_law VALUES('$this->law_id', '$this->price',
					 '$this->building_id', '$this->from_country_id', '$this->build_perm_ban')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeUnionLeader extends RegisterLaw {
		private $new_leader_id;
		
		public function __construct($new_leader_id) {
			$this->new_leader_id = $new_leader_id;
			$this->responsibility_id = 16;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			$this->checkIfLawProposed();
			
			//determine union_id
			$query = "SELECT union_id FROM country_unions WHERE country_id = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$this->user_id')";
			$result = $this->conn->query($query);
			if($result->num_rows != 1) {
				exit("0|Your country is not a member of any union.");
			}
			$row = $result->fetch_row();
			list($union_id) = $row;
			
			//new leader must be a citizen of a country founder
			$query = "SELECT user_id FROM user_profile WHERE citizenship IN 
					 (SELECT country_id FROM country_unions WHERE union_id = '$union_id') AND user_id = '$this->new_leader_id'";
			$result = $this->conn->query($query);
			if($result->num_rows != 1) {
				exit("0|A new leader must be a citizen of a country that has founder rights.");
			}
			
			//check if user is not already a leader of a union
			$query = "SELECT * FROM unions WHERE leader = '$this->new_leader_id'";
			$result = $this->conn->query($query);
			if($result->num_rows != 0) {
				exit("0|This user is already a leader of an union.");
			}
			
			//check if user is already electing for union leader
			$query = "SELECT * FROM new_union_leader WHERE user_id = '$this->new_leader_id' 
					  AND law_id IN (SELECT law_id FROM country_law_info WHERE is_processed = FALSE
					  AND responsibility_id = '$this->responsibility_id')";
			$result = $this->conn->query($query);
			if($result->num_rows != 0) {
				exit("0|This user already has been proposed for the union leader.");
			}
			
			//issue law for all union founders, to accept or decline new leader
			$this->makeLaw();
			$query = "INSERT INTO new_union_leader VALUES('$this->law_id', '$this->new_leader_id', '$union_id')";
			$this->conn->query($query);
			
			$query = "INSERT INTO processing_union_leader VALUES('$this->law_id', '$this->law_id')";
			$this->conn->query($query);
		
			$query = "SELECT country_id FROM country_unions WHERE union_id = '$union_id' AND is_founder = TRUE
					  AND country_id != '$this->country_id'";
			$result_founders = $this->conn->query($query);
			while($row_founders = $result_founders->fetch_row()) {
				list($founder_id) = $row_founders;
				$law_id_founder = getTimeForId() . $founder_id;
				
				//register new law
				$query = "INSERT INTO country_law_info VALUES('$law_id_founder', '$founder_id', 16, 0, 0, 
												  '$this->user_id', CURRENT_DATE, CURRENT_TIME, 0)";
				$this->conn->query($query);
				
				$query = "INSERT INTO processing_union_leader VALUES('$this->law_id', '$law_id_founder')";
				$this->conn->query($query);
				
				//inform government
				notifyToVoteByCountry($founder_id, 16);
			}
			$this->notifyToVote();
		}
	}
	

	#==============================#
	#==============================#
	class ChangeGovernmentSalaries extends RegisterLaw {
		private $salary;
		private $gov_position_id;
		
		public function __construct($gov_position_id, $salary) {
			$this->salary = $salary;
			$this->gov_position_id = $gov_position_id;
			$this->responsibility_id = 17;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM change_gov_salary WHERE position_id = '$this->gov_position_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Change salary law for this governor already proposed.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO change_gov_salary VALUES('$this->law_id', '$this->gov_position_id', '$this->salary')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeCreditDepositRate extends RegisterLaw {
		private $rate;
		private $type;
		private $credit_deposit_type;
		
		public function __construct($rate, $type, $credit_deposit_type) {
			$this->rate = $rate;
			$this->type = $type;
			$this->credit_deposit_type = $credit_deposit_type;
			$this->responsibility_id = 18;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM change_credit_deposit_rate WHERE type = '$this->type' AND
					  credit_deposit_type = '$this->credit_deposit_type' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = FALSE AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows != 0) { 
				if($this->type == 'gold' && $this->credit_deposit_type == 'credit') {
					exit("0|'Change Gold Credit Rate' law already proposed.");
				}
				else if($this->type == 'currency' && $this->credit_deposit_type == 'credit') {
					exit("0|'Change Currency Credit Rate' law already proposed.");
				}
				else if($this->type == 'gold' && $this->credit_deposit_type == 'deposit') {
					exit("0|'Change Gold Deposit Rate' law already proposed.");
				}
				else {
					exit("0|'Change Currency Deposit Rate' law already proposed.");
				}
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO change_credit_deposit_rate VALUES('$this->law_id', '$this->rate', '$this->type', 
					 '$this->credit_deposit_type')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	#==============================#
	#==============================#
	class CreateNewUnion extends RegisterLaw {
		private $union_name;
		private $union_abbr;
		private $union_color;
		private $union_id;
		
		public function __construct($union_name, $union_abbr, $union_color) {
			$this->union_name = $union_name;
			$this->union_abbr = $union_abbr;
			$this->union_color = $union_color;
			$this->responsibility_id = 19;
			$this->registerVariables();
			$this->union_id = getTimeForId() . $this->user_id;;
		}
		
		public function proposeLaw() {
			//determine if this kind of law already proposed.
			$this->checkIfLawProposed();
			
			//determine if not already a member of an union
			$query = "SELECT * FROM country_unions WHERE country_id = '$this->country_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows != 0) {
				exit("0|Your country is already a member of a union.");
			}
			
			//check if other countries is voting to except new union member
			$query = "SELECT * FROM processing_union_application WHERE founder_law_id IN 
					 (SELECT law_id FROM country_law_info WHERE is_processed = FALSE AND responsibility_id = 7)
					  AND applicant_law_id IN (SELECT law_id FROM join_leave_union WHERE country_id = '$this->country_id')";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows != 0) {
				exit("0|Your country already submitted application to join a union.");
			}
			
			//check if user not already a union leader
			$query = "SELECT * FROM unions WHERE leader = '$this->user_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows != 0) {
				exit("0|You already a union leader and cannot create a new union.");
			}
			
			//check if not applied to join a union
			//determine if this kind of law already proposed.
			$query = "SELECT * FROM country_law_info WHERE is_processed = 0 AND responsibility_id = 2 AND country_id = '$this->country_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows == 1) {
				exit("0|Law to join a union is proposed. You cannot create new union.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO create_new_union VALUES('$this->union_id', '$this->union_name', '$this->union_abbr', '$this->user_id', 
					 '$this->union_color', $this->law_id)";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class AssignNewBankManager extends RegisterLaw {
		private $manager_id;
		private $salary;
		
		public function __construct($manager_id, $salary) {
			$this->manager_id = $manager_id;
			$this->salary = $salary;
			$this->responsibility_id = 20;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			$this->checkIfLawProposed();

			//register info about law
			$this->makeLaw();

			$query = "INSERT INTO assign_bank_manager VALUES('$this->law_id', '$this->manager_id', '$this->salary')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeProductionTaxes extends RegisterLaw {
		private $product_id;
		private $tax;
		
		public function __construct($product_id, $tax) {
			$this->product_id = $product_id;
			$this->tax = $tax;
			$this->responsibility_id = 21;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM new_production_tax_law WHERE product_id = '$this->product_id' AND law_id IN 
					 (SELECT law_id FROM country_law_info WHERE responsibility_id = '$this->responsibility_id' 
					  AND is_processed = FALSE AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Law to change tax for this product already proposed.");
			}

			//register info about law
			$this->makeLaw();

			$query = "INSERT INTO new_production_tax_law VALUES('$this->law_id', '$this->tax', '$this->product_id')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class BudgetAllocation extends RegisterLaw {
		private $amount;
		private $gov_position_id;
		private $currency_id;
		
		public function __construct($gov_position_id, $amount, $currency_id) {
			$this->amount = $amount;
			$this->gov_position_id = $gov_position_id;
			$this->currency_id = $currency_id;
			$this->responsibility_id = 22;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if enough amount in country budget
			$query = "SELECT * FROM country_currency WHERE country_id = '$this->country_id' 
					  AND currency_id = '$this->currency_id' AND amount >= '$this->amount'";
			$result = $this->conn->query($query);
			if($result->num_rows != 1) { 
				exit("0|Not enough currency in the country budget.");
			}
			
			//check if law not already proposed
			$query = "SELECT * FROM budget_allocation WHERE position_id = '$this->gov_position_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')
					  AND currency_id = '$this->currency_id'";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Budget allocation law for this ministry already proposed.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO budget_allocation VALUES('$this->law_id', '$this->gov_position_id', '$this->currency_id', '$this->amount')";
			$this->conn->query($query);
			
			//update country budget
			$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
					  WHERE country_id = '$this->country_id' AND currency_id = '$this->currency_id') AS temp)
					  - '$this->amount' WHERE country_id = '$this->country_id' AND currency_id = '$this->currency_id'";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class DefenceAgreement extends RegisterLaw {
		private $defence_wth_country_id;
		private $days;
		
		public function __construct($defence_wth_country_id, $days) {
			$this->defence_wth_country_id = $defence_wth_country_id;
			$this->days = $days;
			$this->responsibility_id = 24;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM sign_defence_agreement WHERE with_country_id = '$this->defence_wth_country_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Defence agreement law with this country is already proposed.");
			}
			
			//check if law not already proposed from other side
			$query = "SELECT * FROM sign_defence_agreement WHERE with_country_id = '$this->country_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->defence_wth_country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|This country already proposed law to sign defence agreement with your country.");
			}
			
			//check if there's no war between countries
			$query = "SELECT * FROM country_wars WHERE country_id = '$this->country_id' AND with_country_id = '$this->defence_wth_country_id'
					  ANC active = TRUE";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Your country is in war with this country.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO sign_defence_agreement VALUES('$this->law_id', '$this->defence_wth_country_id', '$this->days', 0)";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ProductAllocation extends RegisterLaw {
		private $amount;
		private $gov_position_id;
		private $product_id;
		
		public function __construct($gov_position_id, $amount, $product_id) {
			$this->amount = $amount;
			$this->gov_position_id = $gov_position_id;
			$this->product_id = $product_id;
			$this->responsibility_id = 25;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if enough amount in country budget
			$query = "SELECT * FROM country_product WHERE country_id = '$this->country_id' 
					  AND product_id = '$this->product_id' AND amount >= '$this->amount'";
			$result = $this->conn->query($query);
			if($result->num_rows != 1) { 
				exit("0|Not enough products in the country warehouse.");
			}
			
			//check if law not already proposed
			$query = "SELECT * FROM product_allocation WHERE position_id = '$this->gov_position_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 
					  AND country_id = '$this->country_id') AND product_id = '$this->product_id'";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Product allocation law for this ministry and product already proposed.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO product_allocation VALUES('$this->law_id', '$this->gov_position_id', '$this->product_id', 
					 '$this->amount')";
			$this->conn->query($query);
			
			//update country budget
			$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product 
					  WHERE country_id = '$this->country_id' AND product_id = '$this->product_id') AS temp)
					  - '$this->amount' WHERE country_id = '$this->country_id' AND product_id = '$this->product_id'";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeTimezone extends RegisterLaw {
		private $timezone_id;
		
		public function __construct($timezone_id) {
			$this->timezone_id = $timezone_id;
			$this->responsibility_id = 26;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			$this->checkIfLawProposed();
			
			//if there's active or scheduled elections, then exit
			$query = "SELECT * FROM election_info WHERE country_id = '$this->country_id' AND ((ended = 1 AND can_participate = 1)
					  OR ( ended = 0 AND can_participate = 0))";
			$result = $this->conn->query($query);
			if($result->num_rows >= 1) { 
				exit("0|You cannot change timezone during elections.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO change_timezone VALUES('$this->law_id', '$this->timezone_id')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class BuildingPriceForCitizens extends RegisterLaw {
		private $price;
		private $building_id;
		
		public function __construct($building_id, $price) {
			$this->price = $price;
			$this->building_id = $building_id;
			$this->responsibility_id = 27;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law not already proposed
			$query = "SELECT * FROM building_policy_law WHERE building_id = '$this->building_id' AND
					  law_id IN (SELECT law_id FROM country_law_info 
					  WHERE responsibility_id = '$this->responsibility_id' AND is_processed = 0 AND country_id = '$this->country_id')";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Price change for this building already proposed.");
			}
			
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO building_policy_law VALUES('$this->law_id', '$this->building_id', '$this->price')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class RemoveCountryFromUnion extends RegisterLaw {
		private $remove_country_id;
		
		public function __construct($remove_country_id) {
			$this->remove_country_id = $remove_country_id;
			$this->responsibility_id = 29;
			$this->registerVariables();
		}
		public function proposeLaw() {
			//detrmine country union
			$query = "SELECT union_id, is_founder FROM country_unions WHERE country_id = '$this->country_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows != 1) {
				exit("0|Your country is not a member of a union.");
			}
			$row_unions = $result_unions->fetch_row();
			list($union_id, $is_founder) = $row_unions;
	
			if(!$is_founder) {
				exit("0|Your country must have founder rights in order for you to perform this action.");
			}
			
			//check if law proposed
			$query = "SELECT * FROM remove_country_union WHERE law_id IN
					 (SELECT law_id FROM country_law_info WHERE is_processed = FALSE AND responsibility_id = '$this->responsibility_id')
					  AND country_id = '$this->remove_country_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows >= 1) {
				exit("0|This law is already proposed by one of the union founders.");
			}
			
			//check if country is a member of this union
			$query = "SELECT * FROM country_unions WHERE country_id = '$this->remove_country_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows != 1) {
				exit("0|This country is not a member of your union.");
			}
			
			//register data about the law
			$action = 1;//first stage. propose only inside the country
			$this->makeLaw();
			$query = "INSERT INTO remove_country_union VALUES('$this->law_id', '$this->remove_country_id', 
					 '$union_id', '$action')";
			$this->conn->query($query);

			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeWelcomeMessage extends RegisterLaw {
		private $heading;
		private $message;
		
		public function __construct($heading, $message) {
			$this->heading = $heading;
			$this->message = $message;
			$this->responsibility_id = 30;
			$this->registerVariables();
		}
		public function proposeLaw() {
			$this->checkIfLawProposed();
			
			$this->makeLaw();
			
			$query = "INSERT INTO change_welcome_message VALUES('$this->law_id', '$this->heading', '$this->message')";
			$this->conn->query($query);

			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class AssignTitles extends RegisterLaw {
		private $title_id;
		private $profile_id;
		private $add_remove;
		
		public function __construct($title_id, $profile_id, $add_remove) {
			$this->title_id = $title_id;
			$this->profile_id = $profile_id;
			$this->add_remove = $add_remove;
			$this->responsibility_id = 31;
			$this->registerVariables();
		}
		public function proposeLaw() {
			//if remove check if titles is assigned
			if($this->add_remove == 'remove') {
				$query = "SELECT * FROM user_titles WHERE title_id = '$this->title_id' AND user_id = '$this->profile_id'";
				$result = $this->conn->query($query);
				if($result->num_rows == 0) {
					exit('0|This title is not assigned to the user.');
				}
			}
			
			//check if law proposed
			$query = "SELECT * FROM assign_titles WHERE law_id IN
					 (SELECT law_id FROM country_law_info WHERE is_processed = FALSE AND responsibility_id = '$this->responsibility_id')
					  AND title_id = '$this->title_id' AND user_id = '$this->profile_id'";
			$result_unions = $this->conn->query($query);
			if($result_unions->num_rows >= 1) {
				exit("0|Law to add/remove title from the user is already proposed.");
			}
			
			$this->makeLaw();
			
			$query = "INSERT INTO assign_titles VALUES ('$this->law_id', '$this->profile_id', '$this->title_id', 
					 '$this->add_remove')";
			$this->conn->query($query);

			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeCapitalRegion extends RegisterLaw {
		private $region_id;
		
		public function __construct($region_id) {
			$this->region_id = $region_id;
			$this->responsibility_id = 34;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			$this->checkIfLawProposed();
	
			//register data about the law
			$this->makeLaw();
			$query = "INSERT INTO change_capital_region VALUES('$this->law_id', '$this->country_id', '$this->region_id')";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	#==============================#
	#==============================#
	class ChangeRegionOwner extends RegisterLaw {
		private $region_id;
		private $target_country_id;
		private $price;
		
		public function __construct($region_id, $target_country_id, $price) {
			$this->region_id = $region_id;
			$this->target_country_id = $target_country_id;
			$this->price = $price;
			$this->responsibility_id = 35;
			$this->registerVariables();
		}
		
		public function proposeLaw() {
			//check if law proposed
			$query = "SELECT * FROM change_region_owner WHERE region_id = '$this->region_id'
					  AND law_id IN (SELECT law_id FROM country_law_info WHERE is_processed = FALSE 
					  AND responsibility_id = '$this->responsibility_id')";
			$result = $this->conn->query($query);
			if($result->num_rows >= 1) {
				exit("0|Law to change the owner of this region is already in proccess.");
			}
	
			//register data about the law
			$this->makeLaw();
			//1 action is when proposing. 2 is when approving
			$query = "INSERT INTO change_region_owner VALUES('$this->law_id', '$this->target_country_id', '$this->region_id',
					  '$this->price', 1)";
			$this->conn->query($query);
			
			$this->notifyToVote();
		}
	}
	
	
	function notifyToVoteByCountry($country_id, $responsibility_id) {
		global $conn;
		//notify everyone else who must sign/vote for the law
		$query = "SELECT user_id FROM country_government WHERE country_id = '$country_id' AND position_id IN 
				 (SELECT position_id FROM government_country_responsibilities WHERE country_id = '$country_id' 
				  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true) 
				  UNION
				  SELECT user_id FROM congress_members WHERE country_id = 
				 (SELECT country_id FROM government_country_responsibilities WHERE country_id = '$country_id'
				  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true AND position_id = 3)";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($governor_id) = $row;
			$notification = "A new law has been proposed.";
			sendNotification($notification, $governor_id);
		}
	}
	
	mysqli_close($conn);
?>