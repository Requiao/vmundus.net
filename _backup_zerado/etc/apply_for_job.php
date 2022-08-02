<?php
//Description: Get a list of available people and apply for a job. Get available jobs.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/cut_long_name.php');//cutLongName($string, $max_length)
	
	$job_id =  htmlentities(stripslashes(strip_tags(trim($_POST['job_id']))), ENT_QUOTES);
	$person_id =  htmlentities(stripslashes(strip_tags(trim($_POST['person_id']))), ENT_QUOTES);
	$country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	$region_id =  htmlentities(stripslashes(strip_tags(trim($_POST['region_id']))), ENT_QUOTES);
	$skill =  htmlentities(stripslashes(strip_tags(trim($_POST['skill']))), ENT_QUOTES);
	$my_jobs =  htmlentities(stripslashes(strip_tags(trim($_POST['my_jobs']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if($action == 'get_region_jobs') {
		//check if country exist
		if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Region doesn't exist"
								  )));
		}
		$query = "SELECT * FROM regions WHERE region_id = '$region_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {//if enough work spots then create new job
			exit(json_encode(array("success"=>false,
								  "error"=>"Region doesn't exist"
								  )));
		
		}
		
		//test skill
		if(!filter_var($skill, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid input for skill."
								  )));
		}
		if($skill > 25 || $skill <= 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Skill level must be in the range from 1 to 25."
								  )));
		}
		
		//job offers
		$jobs = array();
		$query = "SELECT job_id, user_name, u.user_id, region_name, company_name, building_icon, 
				  salary, skill_lvl, currency_abbr, up.user_image
				  FROM users u, user_building ub, companies c, building_info bi, job_market jm, regions r, country co, 
				  currency cu, user_profile up
				  WHERE u.user_id = ub.user_id AND ub.company_id = c.company_id AND bi.building_id = c.building_id 
				  AND jm.company_id = c.company_id AND location = '$region_id' AND r.region_id = location
				  AND co.country_id = r.country_id AND co.currency_id = cu.currency_id AND up.user_id = u.user_id
				  AND skill_lvl >= '$skill'
				  UNION
				  SELECT job_id, user_name, u.user_id, region_name, company_name, building_icon, 
				  salary, skill_lvl, currency_abbr, up.user_image
				  FROM users u, corporation_building cb, companies c, building_info bi, job_market jm, regions r, country co, 
				  currency cu, user_profile up, corporations corp
				  WHERE u.user_id = manager_id AND cb.company_id = c.company_id AND corp.corporation_id = cb.corporation_id
				  AND bi.building_id = c.building_id 
				  AND jm.company_id = c.company_id AND location = '$region_id' AND r.region_id = location
				  AND co.country_id = r.country_id AND co.currency_id = cu.currency_id AND up.user_id = u.user_id
				  AND skill_lvl >= '$skill'
				  ORDER BY skill_lvl ASC, salary DESC LIMIT 100";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($job_id, $employer_name, $employer_id, $region_name, $company_name, $building_icon, 
				 $salary, $skill_lvl, $currency_abbr, $employer_img) = $row;
				
			$employer_name = cutLongName($employer_name, 7);
			
			array_push($jobs, array("job_id"=>$job_id, "employer_name"=>$employer_name, "employer_id"=>$employer_id, 
									"region_name"=>$region_name, "company_name"=>$company_name, "building_icon"=>$building_icon, 
									"salary"=>$salary, "skill"=>$skill_lvl, "currency_abbr"=>$currency_abbr, 
									"employer_img"=>$employer_img));
		}

		echo json_encode(array("success"=>true,
							   "jobs"=>$jobs
							  ));
	}
	else if($action == 'get_jobs') {
		//check if country exist
		if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Country doesn't exist"
								  )));
		}
		$query = "SELECT * FROM country WHERE country_id = '$country_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {//if enough work spots then create new job
			exit(json_encode(array("success"=>false,
								  "error"=>"Country doesn't exist"
								  )));
		
		}
		
		//test skill
		if(!filter_var($skill, FILTER_VALIDATE_INT)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Invalid input for skill."
								  )));
		}
		if($skill > 25 || $skill <= 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Skill level must be in the range from 1 to 25."
								  )));
		}
		
		$regions = array();
		//regions
		$query = "SELECT region_id, region_name FROM regions WHERE country_id = '$country_id' ORDER BY region_name";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($region_id, $region_name) = $row;
			array_push($regions, array("region_id"=>$region_id, "region_name"=>$region_name));
		}
		
		if($my_jobs == 'true') {
			$from_user = $user_id;
		}
		else {
			$from_user = '%%';
		}
		
		//job offers
		$jobs = array();
		$query = "SELECT job_id, user_name, u.user_id, region_name, company_name, building_icon, 
				  salary, skill_lvl, currency_abbr, up.user_image
				  FROM users u, user_building ub, companies c, building_info bi, job_market jm, regions r, country co, 
				  currency cu, user_profile up
				  WHERE u.user_id = ub.user_id AND ub.company_id = c.company_id AND bi.building_id = c.building_id 
				  AND jm.company_id = c.company_id AND location = region_id AND r.country_id = '$country_id'
				  AND co.country_id = r.country_id AND co.currency_id = cu.currency_id AND up.user_id = u.user_id
				  AND skill_lvl >= '$skill' AND ub.user_id LIKE '$from_user'
				  UNION
				  SELECT job_id, user_name, u.user_id, region_name, company_name, building_icon, 
				  salary, skill_lvl, currency_abbr, up.user_image
				  FROM users u, corporation_building cb, companies c, building_info bi, job_market jm, regions r, country co, 
				  currency cu, user_profile up, corporations corp
				  WHERE u.user_id = manager_id AND cb.company_id = c.company_id AND corp.corporation_id = cb.corporation_id
				  AND bi.building_id = c.building_id 
				  AND jm.company_id = c.company_id AND location = region_id AND r.country_id = '$country_id'
				  AND co.country_id = r.country_id AND co.currency_id = cu.currency_id AND up.user_id = u.user_id
				  AND skill_lvl >= '$skill' AND manager_id LIKE '$from_user'
				  ORDER BY skill_lvl ASC, salary DESC LIMIT 100";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($job_id, $employer_name, $employer_id, $region_name, $company_name, $building_icon, 
				 $salary, $skill_lvl, $currency_abbr, $employer_img) = $row;
				
			$employer_name = cutLongName($employer_name, 7);
			
			array_push($jobs, array("job_id"=>$job_id, "employer_name"=>$employer_name, "employer_id"=>$employer_id, 
									"region_name"=>$region_name, "company_name"=>$company_name, "building_icon"=>$building_icon, 
									"salary"=>$salary, "skill"=>$skill_lvl, "currency_abbr"=>$currency_abbr, 
									"employer_img"=>$employer_img));
		}

		echo json_encode(array("success"=>true,
							   "regions"=>$regions,
							   "jobs"=>$jobs,
							   "my_jobs"=>$my_jobs,
							   "from_user"=>$from_user
							  ));
	}
	else if($action == 'get_workers') {
		if(!is_numeric($job_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Job offer doesn't exists."
								  )));
		}
		$query = "SELECT * FROM job_market WHERE job_id = '$job_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Job offer doesn't exists."
								  )));
		}
		
		$query = "SELECT person_id, experience, who, energy, person_name, years, worked
				  FROM people WHERE user_id = '$user_id'
				  AND experience >= (SELECT required_exp FROM experience WHERE skill_lvl =
				 (SELECT skill_lvl FROM job_market WHERE job_id = '$job_id'))
				  AND who = 'available' AND worked = FALSE ORDER BY experience DESC";
		$result_people = $conn->query($query);
		if($result_people->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>'At least one person must have required skill level for this job. 
											 Only People who didn\'t worked today will be displayed. Go to 
											 <a href="people"> People page </a> to check status of your People.'
								  )));
		}
		$workers = array();
		while($row_people = $result_people->fetch_row()) {
			list($person_id, $experience, $who, $energy, $person_name, $years, $worked) = $row_people;
			$person_name = cutLongName($person_name, 10);
			
			$query = "SELECT skill_lvl FROM experience WHERE required_exp <= '$experience' ORDER BY skill_lvl DESC LIMIT 1";
			$result = $conn->query($query);;
			$row = $result->fetch_row();
			list($skill_lvl) = $row;
			
			array_push($workers, array("person_id"=>$person_id, "experience"=>$skill_lvl, "status"=>$who, 
									   "energy"=>$energy, "person_name"=>$person_name, "years"=>$years, 
									   "worked"=>$worked));
		}
		echo json_encode(array(
			"success"=>true,
			"workers"=>$workers
		));
	}
	else if($action == 'apply_for_job'){
		if(!is_numeric($job_id)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Job offer doesn't exists."
								  )));
		}
		
		$query = "SELECT person_id, experience, who, energy, person_name, years, worked
				  FROM people WHERE user_id = '$user_id'
				  AND experience >= (SELECT required_exp FROM experience WHERE skill_lvl =
				 (SELECT skill_lvl FROM job_market WHERE job_id = '$job_id'))
				  AND who = 'available' AND worked = FALSE AND person_id = '$person_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Person does not have enough experience for this job or job is not available anymore."
								  )));
		}
		
		$query = "SELECT company_id, salary FROM job_market WHERE job_id = '$job_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($company_id, $salary) = $row;
		//hire
		$query = "INSERT INTO hired_workers VALUES('$person_id', '$company_id', '$salary', '$job_id', '0')";
		if(!$conn->query($query)) {
			exit(json_encode(array("success"=>false,
								   "error"=>"Please try again."
								  )));
		}
		$query = "DELETE FROM job_market WHERE job_id = '$job_id'";
		$conn->query($query);
		
		//change person who
		$query = "Update people SET who = 'working' WHERE person_id = '$person_id'";
		$conn->query($query);
	
		echo json_encode(array("success"=>true,
							   "msg"=>"Hired!"
							   ));
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
								 )));
	}
	mysqli_close($conn);
?>