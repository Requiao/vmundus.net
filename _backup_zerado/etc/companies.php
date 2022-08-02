<?php
	//Description: Get a list of available companies that can be built. For companies.php page (build company).
	//			   When user creates a company, js prompts to chose region location and company name.
	//			   This scripts gives a list of regions in the country user currently located.
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/record_statistics.php');
	include('../php_functions/get_time_for_id.php');
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)

	$order_id =  htmlentities(stripslashes(strip_tags(trim($_POST['order_by']))), ENT_QUOTES);
	
	$building_id =  htmlentities(stripslashes(strip_tags(trim($_POST['building_id']))), ENT_QUOTES);
	$company_name =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['comp_name']))), ENT_QUOTES);
	$region_id =  htmlentities(stripslashes(strip_tags(trim($_POST['reg_id']))), ENT_QUOTES);
	$corporation_id =  htmlentities(stripslashes(strip_tags(trim($_POST['corp_id']))), ENT_QUOTES);
	$country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	$MAX_UNLIMITED_WAREHOUSE_CAPACITY = 900000000;
	
	$for_corporation = false;
	if(!empty($corporation_id)) {
		if(!is_numeric($corporation_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['corporation_doesnt_exist']
								   )));
		}
		
		$query = "SELECT * FROM corporations WHERE corporation_id = '$corporation_id' 
				  AND manager_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_dont_have_access_to_this_corporation']
								   )));		
		}
		$for_corporation = true;
	}
	$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	$row = $result->fetch_row();
	list($citizenship_country) = $row;
	
	if ($action == 'invest_resources') {
		$summary = array();
		
		//get user companies that have hired workers
		if($for_corporation) {
			$query = "SELECT c.company_id, COUNT(person_id), company_name FROM hired_workers hw, companies c 
					  WHERE c.company_id IN (SELECT company_id FROM corporation_building WHERE corporation_id = '$corporation_id')
					  AND hw.company_id = c.company_id GROUP BY company_id";
		}
		else {
			$query = "SELECT c.company_id, COUNT(person_id), company_name FROM hired_workers hw, companies c 
					  WHERE c.company_id IN (SELECT company_id FROM user_building WHERE user_id = '$user_id')
					  AND hw.company_id = c.company_id GROUP BY company_id";
		}
		$result_companies = $conn->query($query);
		if($result_companies->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>'There are no workers working in your companies'
								   )));		
		}
		while($row_companies = $result_companies->fetch_row()) {
			list($company_id, $workers, $company_name) = $row_companies;
		
			$quantity = $workers;//invest for n working cycles
			
			//get required products
			$total_required = 0;
			$required_resources = array();
			$query = "SELECT required_id, amount, product_name FROM product_product pp, product_info pi WHERE building_id = 
					 (SELECT building_id FROM companies WHERE company_id = '$company_id')
					  AND pi.product_id = required_id";
			$result_req = $conn->query($query);
			$x = 0;
			while($row_req = $result_req->fetch_row()) {
				list($product_id, $required_amount, $product_name) = $row_req;
				$req_resources = $required_amount * $quantity;
				$total_required += $req_resources;
				
				//check if user have resources
				if($for_corporation) {
					$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
							  AND product_id = '$product_id'";
				}
				else {
					$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
				}
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($amount) = $row;
				if($amount < $req_resources) {
					$need = round(ceil(($req_resources - $amount) * 100), 2) / 100;
					
					array_push($summary, array("success"=>false,
												"company_name"=>$company_name,
												"company_id"=>$company_id,
												"error"=>$lang['you_dont_have_enough'] . " $product_name. " . $lang['you_need'] . 
												" $need " . $lang['more_items']
												));
					continue 2;
				}
				$required_resources[$x]['product_id'] = $product_id;
				$required_resources[$x]['req_resources'] = $req_resources;
				$x++;
			}
			$length = $x;
			
			//check if company have enough space in resource warehouse
			$query = "SELECT SUM(amount) AS total FROM resource_warehouse WHERE company_id = '$company_id' 
					  HAVING (SUM(amount) + '$total_required') <= (SELECT resource_ware FROM companies WHERE company_id = '$company_id')";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				array_push($summary, array("success"=>false,
											"company_name"=>$company_name,
											"company_id"=>$company_id,
											"error"=>$lang['not_enough_space_in_the_companys_resource_warehouse']
											));
													
				continue;
			}

			//update resource warehouse
			for($x = 0; $x < $length; $x++) {
				$req_resources = $required_resources[$x]['req_resources'];
				$product_id = $required_resources[$x]['product_id'];
				
				$query = "UPDATE resource_warehouse SET amount = (SELECT amount + '$req_resources' FROM 
						 (SELECT amount FROM resource_warehouse WHERE company_id = '$company_id' AND product_id = '$product_id') AS temp) 
						  WHERE company_id = '$company_id' AND product_id = '$product_id'";
				$conn->query($query);
				
				if($for_corporation) {
					$query = "UPDATE corporation_product SET amount = (SELECT amount - '$req_resources' FROM 
							 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
							  AND product_id = '$product_id') AS temp) 
							  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
				}
				else {
					$query = "UPDATE user_product SET amount = (SELECT amount - '$req_resources' FROM 
							 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
							  WHERE user_id = '$user_id' AND product_id = '$product_id'";
				}
				$conn->query($query);
			}
			
			//get company details
			$query = "SELECT ROUND(COALESCE((100/resource_ware) * SUM(rw.amount), 0), 2) AS resource_storage_fill
					  FROM resource_warehouse rw, companies c WHERE c.company_id = '$company_id' AND rw.company_id = c.company_id";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($resource_storage_fill) = $row;
			
			//working cycles
			$resources_working_cycles = array();
			$query = "SELECT product_name, pp.amount, rw.amount 
					  FROM product_info pi, resource_warehouse rw, product_product pp, companies c
					  WHERE c.company_id = '$company_id' AND rw.product_id = required_id 
					  AND pi.product_id = required_id AND pp.building_id =  c.building_id
					  AND rw.company_id = c.company_id ORDER BY product_name";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($product_name, $req_amount, $ava_amount) = $row;	
				//use round. floor is not working for 0.3/0.1 and 0.6/0.1
				$cycles = floor(round($ava_amount/$req_amount, 3));
				
				array_push($resources_working_cycles, array("product_name"=>$product_name, "cycles"=>$cycles));
			}
		
			array_push($summary, array("success"=>true,
									   "company_name"=>$company_name,
									   "company_id"=>$company_id,
									   "resource_storage_fill"=>$resource_storage_fill,
									   "resources_working_cycles"=>$resources_working_cycles,
									   "msg"=>$lang['products_successfully_invested'],
									   "resource_details"=>'a'
									  ));
		}
		echo json_encode(array("success"=>true,
							   "summary"=>$summary
							  ));
	}
	else if($action == 'collect_products') {
		//get all companies and product info
		if($for_corporation) {
			$query = "SELECT company_id, product_id, amount FROM product_warehouse 
					  WHERE company_id IN (SELECT company_id FROM corporation_building WHERE corporation_id = '$corporation_id')
					  AND amount > 0.09 ORDER BY amount DESC";
		}
		else {
			$query = "SELECT company_id, product_id, amount FROM product_warehouse 
					  WHERE company_id IN (SELECT company_id FROM user_building WHERE user_id = '$user_id')
					  AND amount > 0.09 ORDER BY amount DESC";
		}
		$result_comp = $conn->query($query);
		if($result_comp->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['no_products_available_to_collect']
								   )));
		}
		
		$summary = array();
		$flag = false;//if didn't collected any resources, then display error
		$error = '';
		while($row_comp = $result_comp->fetch_row()) {
			list($company_id, $product_id, $quantity) = $row_comp;
			
			$quantity = floor($quantity * 100) / 100;//round down to 2 decimal places
			
			//check if enough space in the warehouse
			if($for_corporation) {
				$query = "SELECT SUM(amount) + '$quantity'
						  FROM corporation_product WHERE corporation_id = '$corporation_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($warehouse_fill) = $row;
				if($warehouse_fill > $MAX_UNLIMITED_WAREHOUSE_CAPACITY) {
					$error = $lang['not_enough_capacity_in_the_corporation_warehouse'];
					break;
				}
			}
			else {
				$query = "SELECT SUM(amount) + '$quantity', (SELECT capacity FROM user_warehouse WHERE user_id = '$user_id')
						  FROM user_product WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($warehouse_fill, $warehouse_capacity) = $row;
				if($warehouse_fill > $warehouse_capacity) {
					$more_room = round($warehouse_fill - $warehouse_capacity, 2);
					$error =  $lang['not_enough_capacity_in_the_warehouse'] . ". " .
							  $lang['you_need_space_for_at_least'] . " $more_room " . $lang['items']; 
					break;
				}
			}
			
			//update product warehouse
			$query = "UPDATE product_warehouse SET amount = (SELECT amount - '$quantity' FROM 
					 (SELECT amount FROM product_warehouse WHERE company_id = '$company_id' AND product_id = '$product_id') AS temp) 
					  WHERE company_id = '$company_id' AND product_id = '$product_id'";
			$conn->query($query);
		
			/* update user warehouse */
			//detect if user has or not product id in his warehouse
			if($for_corporation) {
				$query = "SELECT product_id FROM corporation_product WHERE corporation_id = '$corporation_id' 
						  AND product_id = '$product_id'";
			}
			else {
				$query = "SELECT product_id FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
			}
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				if($for_corporation) {
					$query = "UPDATE corporation_product SET amount = (SELECT amount + '$quantity' FROM 
							 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
							  AND product_id = '$product_id') AS temp) 
							  WHERE corporation_id = '$corporation_id' AND product_id = '$product_id'";
				}
				else {
					$query = "UPDATE user_product SET amount = (SELECT amount + '$quantity' FROM 
							 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id') AS temp) 
							  WHERE user_id = '$user_id' AND product_id = '$product_id'";
				}
			}
			else {
				if($for_corporation) {
					$query = "INSERT INTO corporation_product VALUES('$corporation_id', '$product_id', '$quantity')";
				}
				else {
					$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$quantity')";
				}
			}
			$conn->query($query);
			
			//report
			$flag = true;
			$query = "SELECT product_name, product_icon FROM product_info WHERE product_id = '$product_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($product_name, $product_icon) = $row;
			
			array_push($summary, array('product_name'=>$product_name,
									   'product_icon'=>$product_icon,
									   'amount'=>$quantity,
									   'company_id'=>$company_id
									  ));
		}
		if(!$flag) {
			exit(json_encode(array('success'=>false,
								   'error'=>$error
								  )));
		}
		echo json_encode(array('success'=>true,
							   'msg'=>$lang['successfully_collected'] . ":",
							   'summary'=>$summary
							  ));
	}
	else if($action == 'get_available_countries') {
		$query = "SELECT country_id, country_name, flag FROM country WHERE (country_id = '$citizenship_country' OR 
				  country_id IN (SELECT country_id FROM foreign_building_policy 
				  WHERE foreign_country = '$citizenship_country'
				  AND foreigners = TRUE) OR country_id IN 
				 (SELECT r.country_id FROM regions r, country_core_regions ccr 
				  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country'
				  AND r.country_id != ccr.country_id))";
		$result = $conn->query($query);
		$countries = array();
		while($row = $result->fetch_row()) {
			list($country_id, $country_name, $flag) = $row;
			array_push($countries, array("country_id"=>$country_id, "country_name"=>$country_name, "flag"=>$flag));
		}
		echo json_encode(array('success'=>true,
							   'msg'=>"Chose country where you want to build your company.",
							   'countries'=>$countries
							  ));
	}
	else if($action == 'get_available_companies') {
		//check country_id
		$query = "SELECT * FROM country WHERE (country_id = '$citizenship_country' OR 
				  country_id IN (SELECT country_id FROM foreign_building_policy 
				  WHERE foreign_country = '$citizenship_country'
				  AND foreigners = TRUE) OR country_id IN 
				 (SELECT r.country_id FROM regions r, country_core_regions ccr 
				  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country'
				  AND r.country_id != ccr.country_id)) 
				  AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array(
				"success"=>false,
				"error"=>$lang['you_are_not_allowed_to_build_companies_in_this_country']
			)));
		}

		//determine if building in the invader's territory
		$query = "SELECT * FROM regions r, country_core_regions ccr 
				  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country' 
				  AND r.country_id != ccr.country_id AND r.country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			$citizenship_country = $country_id;//treat as citizen
		}
		
		//determine if inside the country or outside
		if($citizenship_country == $country_id) {//citizen
			$query = "SELECT bi.building_id, bi.building_icon, bi.name, pi.product_name, bp.price
					  FROM product_info pi, building_info bi, building_policy bp
					  WHERE pi.product_id = bi.product_id AND bp.building_id = bi.building_id 
					  AND req_research = FALSE AND is_active = TRUE AND bp.country_id = '$citizenship_country'
					  UNION
					  SELECT bi.building_id, bi.building_icon, bi.name, pi.product_name, bp.price
					  FROM product_info pi, building_info bi, building_policy bp, building_research br, user_researches ur
					  WHERE pi.product_id = bi.product_id AND bp.building_id = bi.building_id 
					  AND req_research = TRUE AND is_active = TRUE 
					  AND br.building_id = bi.building_id AND br.research_id = ur.research_id AND ur.user_id = '$user_id'
					  AND bp.country_id = '$citizenship_country' AND ur.is_researched = TRUE
					  ORDER BY building_id";
			$result = $conn->query($query);
		}
		else {//foreigner
			$query = "SELECT bi.building_id, bi.building_icon, bi.name, pi.product_name, bp.price
					  FROM product_info pi, building_info bi, foreign_building_policy bp
					  WHERE pi.product_id = bi.product_id AND bp.building_id = bi.building_id 
					  AND req_research = FALSE AND is_active = TRUE AND bp.country_id = '$country_id'
					  AND foreign_country = '$citizenship_country'
					  UNION
					  SELECT bi.building_id, bi.building_icon, bi.name, pi.product_name, bp.price
					  FROM product_info pi, building_info bi, foreign_building_policy bp, building_research br, user_researches ur
					  WHERE pi.product_id = bi.product_id AND bp.building_id = bi.building_id 
					  AND req_research = TRUE AND is_active = TRUE 
					  AND br.building_id = bi.building_id AND br.research_id = ur.research_id AND ur.user_id = '$user_id'
					  AND bp.country_id = '$country_id' AND ur.is_researched = TRUE
					  AND foreign_country = '$citizenship_country'
					  ORDER BY building_id";
			$result = $conn->query($query);
		}
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_are_not_allowed_to_build_companies_in_this_country']
								   )));
		}

		$companies = array();
		while($row = $result->fetch_row()) {
			list($building_id, $building_icon, $building_name, $product_name, $price) = $row;
			$query_bp = "SELECT product_name, product_icon, amount, bp.product_id FROM product_info pi, building_product bp 
						 WHERE building_id = '$building_id' AND pi.product_id = bp.product_id";
			$result_bp = $conn->query($query_bp);
			$resources = array();
			while($row_bp = mysqli_fetch_row($result_bp)) {
				list($req_product_name, $product_icon, $amount, $product_id) = $row_bp;
				array_push($resources, array("req_product_name"=>$req_product_name, "product_icon"=>$product_icon, 
											 "amount"=>$amount, "product_id"=>$product_id));
			}
			array_push($companies, array("building_icon"=>$building_icon, "building_name"=>$building_name, 
										 "product_name"=>$product_name, "price"=>$price,
										 "building_id"=>$building_id, "resources"=>$resources
										));
		}
		
		//get available products
		if($for_corporation) {
			$query = "SELECT bp.product_id, IFNULL(cp.amount, 0) FROM building_product bp LEFT JOIN corporation_product cp
					  ON corporation_id = '$corporation_id' AND bp.product_id = cp.product_id
					  GROUP BY product_id";
		}
		else {
			$query = "SELECT bp.product_id, IFNULL(up.amount, 0) FROM building_product bp LEFT JOIN user_product up
					  ON user_id = '$user_id' AND bp.product_id = up.product_id
					  GROUP BY product_id";
		}
		$result = $conn->query($query);
		$user_product = array();
		while($row = $result->fetch_row()) {
			list($product_id, $amount) = $row;
			array_push($user_product, array("product_id"=>$product_id, "amount"=>$amount));
		}
		
		$reply = array("success"=>true,
					   "companies"=>$companies,
					   "user_product"=>$user_product
					  );
		echo json_encode($reply);
	}
	else if ($action == 'company_data') {
		//check country_id
		$query = "SELECT country_name FROM country WHERE (country_id = '$citizenship_country' OR 
				  country_id IN (SELECT country_id FROM foreign_building_policy 
				  WHERE foreign_country = '$citizenship_country'
				  AND foreigners = TRUE) OR country_id IN 
				 (SELECT r.country_id FROM regions r, country_core_regions ccr 
				  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country'
				  AND r.country_id != ccr.country_id)) 
				  AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {//citizen
			exit(json_encode(array(
				"success"=>false,
				"error"=>$lang['you_are_not_allowed_to_build_companies_in_this_country']
			)));
		}
		$row = $result->fetch_row();
		list($country_name) = $row;
		
		//check if in invaders territory
		$select_all_regions = false;
		$in_invader_country = false; //located in the invader's country
		$query = "SELECT * FROM regions r, country_core_regions ccr 
				  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country' 
				  AND r.country_id != ccr.country_id AND r.country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			$in_invader_country = true;
			//check if invader allows to build companies for citizens of the invaded country
			$query = "SELECT * FROM foreign_building_policy WHERE 
					  country_id = '$country_id' AND foreign_country = '$citizenship_country' AND foreigners = TRUE";
			$result = $conn->query($query);
			if($result->num_rows > 0) {
				$select_all_regions = true;
			}
		}
		
		$regions = array();
		if($in_invader_country && !$select_all_regions) {
			$query = "SELECT region_name, r.region_id FROM regions r, country_core_regions ccr 
					  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country' 
					  AND r.country_id != ccr.country_id AND r.country_id = '$country_id'";
		}
		else {
			$query = "SELECT region_name, region_id FROM regions WHERE country_id = '$country_id' ORDER BY region_name";
		}
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($region_name, $region_id) = $row;
			array_push($regions, array("region_name"=>$region_name, "region_id"=>$region_id));
		}
		$reply = array("country_name"=>$country_name,
					   "regions"=>$regions
					   );
		echo json_encode($reply);
	}
	else if ($action == 'create_company') {
		//check country_id
		$query = "SELECT country_name FROM country WHERE (country_id = '$citizenship_country' OR 
				  country_id IN (SELECT country_id FROM foreign_building_policy 
				  WHERE foreign_country = '$citizenship_country'
				  AND foreigners = TRUE) OR country_id IN 
				 (SELECT r.country_id FROM regions r, country_core_regions ccr 
				  WHERE r.region_id = ccr.region_id AND ccr.country_id = '$citizenship_country'
				  AND r.country_id != ccr.country_id)) 
				  AND country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {//citizen
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_are_not_allowed_to_build_companies_in_this_country']
								   )));
		}
		
		strValidate($company_name, 1, 20, $lang['company_name']);
	
		//check building
		if(!filter_var($building_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['building_doesnt_exists']
								  )));
		}
		$query = "SELECT building_id, product_id, purpose, req_research FROM building_info
				  WHERE building_id = '$building_id' AND purpose <= 5 AND is_active = TRUE";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>$lang['building_doesnt_exists']
			)));
		}
		$row = $result->fetch_row();
		list($building_id, $product_id, $purpose, $req_research) = $row;
		
		//check region
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['region_doesnt_exists']
								  )));
		}
		$query = "SELECT * FROM regions WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['region_doesnt_exists']
								  )));
		}

		//check if region belongs to the citizenship_country
		$query = "SELECT * FROM regions WHERE region_id = '$region_id' AND country_id = '$citizenship_country'";
		$result = $conn->query($query);
		$in_invader_country = false;
		if($result->num_rows != 1) {
			//check if belongs to the invader
			$query = "SELECT * FROM country_core_regions WHERE region_id = '$region_id' 
					  AND country_id = '$citizenship_country'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "SELECT * FROM foreign_building_policy
						  WHERE foreign_country = '$citizenship_country' 
						  AND country_id = '$country_id' AND building_id = '$building_id'
						  AND foreigners = TRUE";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					exit(json_encode(array(
						'success'=>false,
						'error'=>'You are not allowed to build companies in this region.'
					)));
				}
			}
			$in_invader_country = true;
		}
		
		if($req_research) {
			//check if user researched this building
			$query = "SELECT * FROM building_research br, user_researches ur WHERE br.building_id = '$building_id' 
					  AND user_id = '$user_id' AND br.research_id = ur.research_id AND is_researched = TRUE";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									'error'=>'This building is not researched.'
									)));
			}
		}
		
		//determine price for building whether user in his own country or outside
		if($country_id == $citizenship_country || $in_invader_country) {//citizen or threat as citizen
			$query = "SELECT price FROM building_policy WHERE building_id = '$building_id' AND country_id = '$country_id'";
		}
		else {//foreigner
			//and check if has permission to build
			$query = "SELECT price FROM foreign_building_policy WHERE building_id = '$building_id' 
					  AND country_id = '$country_id' AND foreign_country = '$citizenship_country' AND foreigners = TRUE";
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_are_not_allowed_to_build_this_company_in_this_country']
								  )));
		}
		$row = $result->fetch_row();
		list($price) = $row;
		
		//determine whether builder has enough gold
		if($for_corporation) {
			$query = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' 
					  AND product_id = '1' AND amount >= '$price'";
		}		
		else {
			$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' 
					  AND product_id = '1' AND amount >= '$price'";
		}
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>$lang['you_dont_have_enough_gold']
								  )));
		}

		//determine whether user has enough resources
		$query = "SELECT amount, product_id FROM building_product WHERE building_id = '$building_id'";
		$result = $conn->query($query);
	
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			if($for_corporation) {
				$queryr = "SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id' AND amount >= '$amount' 
						   AND product_id = '$product_id'";
			}
			else {
				$queryr = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND amount >= '$amount' 
						   AND product_id = '$product_id'";
			}
			$resultr = $conn->query($queryr);
			if($resultr->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>$lang['you_dont_have_enough_resources']
									  )));
			}
		}

		//create company
		//get gold for company
		if($for_corporation) {
			$query = "UPDATE corporation_product SET amount = (SELECT amount - '$price' FROM 
					 (SELECT amount FROM  corporation_product WHERE corporation_id = '$corporation_id'
					  AND product_id = '1') AS temp)  
					  WHERE corporation_id = '$corporation_id' AND product_id = '1'";
		}
		else {
			$query = "UPDATE user_product SET amount = (SELECT amount - '$price' FROM 
					 (SELECT amount FROM  user_product WHERE user_id = '$user_id' AND product_id = '1') AS temp)  
					  WHERE user_id = '$user_id' AND product_id = '1'";
		}
		$conn->query($query);
	
		//send money to country treasury
		$query = "SELECT * FROM country_product WHERE country_id = '$country_id' AND product_id = '1'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE country_product SET amount = (SELECT amount + '$price' FROM 
					 (SELECT amount FROM  country_product WHERE country_id = '$country_id' AND product_id = '1') AS temp)  
					  WHERE country_id = '$country_id' AND product_id = '1'";
			$conn->query($query);
		}
		else {
			$query = "INSERT INTO country_product VALUES('$country_id', '1', '$price')";
			$conn->query($query);
		}
	
		//update country_product_income statistic
		countryDailyProductIncome($region_id, 1, $price);
	
		//get resources for company
		$query = "SELECT amount, product_id FROM building_product WHERE building_id = '$building_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($amount, $product_id) = $row;
			if($for_corporation) {
				$query = "UPDATE corporation_product SET amount = (SELECT amount - '$amount' FROM 
						 (SELECT amount FROM corporation_product WHERE corporation_id = '$corporation_id'
						  AND product_id = $product_id) AS temp)  
						  WHERE corporation_id = '$corporation_id' AND product_id = $product_id";
			}
			else {
				$query = "UPDATE user_product SET amount = (SELECT amount - '$amount' FROM 
						 (SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = $product_id) AS temp)  
						  WHERE user_id = '$user_id' AND product_id = $product_id";
			}
			$conn->query($query);
		}
			
		/* create company */
		$company_id = getTimeForId() . $user_id;
		
		//'register' company
		$query = "INSERT INTO companies VALUES('$company_id', '$region_id', '100', '100', '1', '$company_name', '$building_id',
											    CURRENT_DATE, CURRENT_TIME, 0)";
		$conn->query($query);
		
		//make user the onwer
		if($for_corporation) {
			$query = "INSERT INTO corporation_building VALUES('$corporation_id', '$company_id')";
		}
		else {
			$query = "INSERT INTO user_building VALUES('$user_id', '$company_id')";
		}
		$conn->query($query);
		
		//prepare resource_warehouse
		$query = "SELECT required_id FROM product_product WHERE building_id = '$building_id'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			while($row = $result->fetch_row()) {
				list($product_id) = $row;
				$query = "INSERT INTO resource_warehouse VALUES('$company_id', '$product_id', '0')";
				$conn->query($query);
			}
		}
	
		//prepare product_warehouse
		$query = "SELECT product_id FROM building_info WHERE building_id = '$building_id'";
		$result =  $conn->query($query);
		$row = $result->fetch_row();
		list($product_id) = $row;
		$query = "INSERT INTO product_warehouse VALUES('$company_id', '$product_id', '0')";
		$conn->query($query);
		
		$query = "SELECT building_icon, company_name, product_name, region_name, country_name
				  FROM building_info bi, companies co, regions reg, country c, product_info pi
				  WHERE bi.building_id = co.building_id AND co.location = reg.region_id 
				  AND c.country_id = reg.country_id AND pi.product_id = bi.product_id 
				  AND co.company_id = '$company_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($building_icon, $company_name, $product_name, $region_name, $country_name) = $row;
	
		//resources required for production
		$query = "SELECT product_name, pp.amount, rw.amount 
				  FROM product_info pi, resource_warehouse rw, product_product pp
				  WHERE company_id = '$company_id' AND rw.product_id = required_id 
				  AND pi.product_id = required_id AND building_id = '$building_id'";
		$result = $conn->query($query);
		$resources_prod = array();
		while($row = $result->fetch_row()) {
			list($product_name, $req_amount, $ava_amount) = $row;	
			$cycles = floor($ava_amount/$req_amount);
			array_push($resources_prod, array("product_name"=>$product_name, "cycles"=>$cycles));
		}
	
		$companies = array("building_icon"=>$building_icon, "company_name"=>$company_name, "product_name"=>$product_name,
						   "region_name"=>$region_name, "country_name"=>$country_name, "product_storage"=>0,
						   "resource_storage"=>0, "company_id"=>$company_id, "resources_prod"=>$resources_prod);
			
		$reply = array("success"=>true,
					   "msg"=>$lang['company_created'],
					   "companies"=>$companies
					  );
		echo json_encode($reply);
	}
	else if ($action == 'sort_companies') {
		if(!filter_var($order_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['invalid_sorting_option']
								  )));
		}
		if($order_id == 1) {
			$order_by = 'co.company_name';
		}
		else if ($order_id == 2) {
			$order_by = 'co.company_name';
		}
		else if ($order_id == 3) {
			$order_by = 'pi.product_name';
		}
		else if ($order_id == 4) {
			$order_by = 'reg.region_name';
		}
		else if ($order_id == 5) {
			$order_by = 'country.country_name';
		}
		else if ($order_id == 6) {
			$order_by = 'ROUND(COALESCE((100/product_ware)*SUM(pw.amount), 0),2) DESC';
		}
		else if ($order_id == 7) {
			$order_by = 'ROUND(COALESCE((100/resource_ware)*SUM(rw.amount), 0),2) DESC';
		}
		else {
			$order_by = 'co.building_id';//default
		}
		if($for_corporation) {
			$query = "SELECT bi.building_id, building_icon, company_name, product_name, region_name, country_name,
					  ROUND(COALESCE((100/product_ware)*pw.amount, 0),2) AS product_storage, workers,
					  ROUND(COALESCE((100/resource_ware)*SUM(rw.amount), 0),2) AS resource_storage, 
					  co.company_id, cycles_worked
					  FROM building_info bi, companies co LEFT JOIN resource_warehouse rw ON co.company_id = rw.company_id, 
					  corporation_building cb, product_info pi, regions reg, country, product_warehouse pw
					  WHERE bi.building_id = co.building_id AND cb.company_id = co.company_id AND pi.product_id = bi.product_id 
					  AND co.location = reg.region_id AND country.country_id = reg.country_id AND co.company_id = pw.company_id 
					  AND pw.company_id = co.company_id
					  AND corporation_id = '$corporation_id' GROUP BY co.company_id, product_storage ORDER BY " . $order_by;
		}
		else {
			$query = "SELECT bi.building_id, building_icon, company_name, product_name, region_name, country_name,
					  ROUND(COALESCE((100/product_ware)*pw.amount, 0),2) AS product_storage, workers,
					  ROUND(COALESCE((100/resource_ware)*SUM(rw.amount), 0),2) AS resource_storage, 
					  co.company_id, cycles_worked
					  FROM building_info bi, companies co LEFT JOIN resource_warehouse rw ON co.company_id = rw.company_id, 
					  user_building ub, product_info pi, regions reg, country, product_warehouse pw
					  WHERE bi.building_id = co.building_id AND ub.company_id = co.company_id AND pi.product_id = bi.product_id 
					  AND co.location = reg.region_id AND country.country_id = reg.country_id AND co.company_id = pw.company_id 
					  AND pw.company_id = co.company_id
					  AND user_id = '$user_id' GROUP BY co.company_id, product_storage ORDER BY " . $order_by;
		}
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>$lang['you_dont_own_any_company']
								  )));
		}
		$reply = array("success"=>true);
		$companies = array();
		while($row = $result->fetch_row()) {
			list($building_id, $building_icon, $company_name, $product_name, $region_name, $country_name, $product_storage, 
				 $workers, $resource_storage, $company_id, $cycles_worked) = $row;
			
			//resources required for production
			$query = "SELECT product_name, pp.amount, rw.amount 
					  FROM product_info pi, resource_warehouse rw, product_product pp
					  WHERE company_id = '$company_id' AND rw.product_id = required_id 
					  AND pi.product_id = required_id AND building_id = '$building_id'";
			$result_prod = $conn->query($query);
			$resources_prod = array();
			while($row_prod = $result_prod->fetch_row()) {
				list($rec_product_name, $req_amount, $ava_amount) = $row_prod;	
				$cycles = floor($ava_amount/$req_amount);
				array_push($resources_prod, array("product_name"=>$rec_product_name, "cycles"=>$cycles));
			}
			
			//workers details
			$query = "SELECT COUNT(worked), (SELECT COUNT(*) FROM hired_workers WHERE company_id = '$company_id') 
					  FROM people WHERE worked = TRUE AND person_id IN
					 (SELECT person_id FROM hired_workers WHERE company_id = '$company_id')";
			$result_workers = $conn->query($query);
			$row_workers = $result_workers->fetch_row();
			list($workers_worked, $hired_workers) = $row_workers;
			
			array_push($companies, array("building_icon"=>$building_icon, "company_name"=>$company_name, "product_name"=>$product_name,
										 "region_name"=>$region_name, "country_name"=>$country_name, "product_storage"=>$product_storage,
										 "resource_storage"=>$resource_storage, "company_id"=>$company_id, 
										 "resources_prod"=>$resources_prod, "workers"=>$workers, "workers_worked"=>$workers_worked,
										 "hired_workers"=>$hired_workers, "cycles_worked"=>$cycles_worked));
		}
		$reply = array("success"=>true,
					   "companies"=>$companies
					   );
		echo json_encode($reply);
	}
?>