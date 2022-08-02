<?php
	//Description : Research new technologies
	
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$research_id =  htmlentities(stripslashes(strip_tags(trim($_POST['research_id']))), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];

	if($action == 'invest_resources') {
		//test research_id
		if(!is_numeric($research_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Research doesn't exist."
								  )));
		}
		$query = "SELECT * FROM research_info WHERE research_id = '$research_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Research doesn't exist."
								  )));
		}
		
		//check if not already researched
		$query = "SELECT * FROM user_researches WHERE user_id = '$user_id' AND is_researched = TRUE AND
				  research_id = '$research_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You already researched this technology."
								  )));
		}		  
		
		//check if research is not in progress
		$query = "SELECT * FROM user_researches 
				  WHERE user_id = '$user_id' AND is_researched = FALSE 
				  AND research_id = '$research_id' AND start_date IS NOT NULL";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Research is in progress."
								  )));
		}
		
		//select required resources
		$query = "SELECT rrr.amount - IFNULL(cpur.amount, 0), rrr.product_id , product_name, product_icon
				  FROM product_info pi, resources_research_req rrr LEFT JOIN collected_products_user_research cpur
				  ON cpur.product_id = rrr.product_id AND user_id = '$user_id' AND cpur.research_id = rrr.research_id
				  WHERE rrr.research_id = '$research_id' AND pi.product_id = rrr.product_id";
		$result = $conn->query($query);
		$product = array();
		while($row = $result->fetch_row()) {
			list($amount, $product_id, $product_name, $product_icon) = $row;
			$req_amount = ceil($req_amount * 100) / 100;//ceil important
			
			array_push($product, array("amount"=>$amount, "product_id"=>$product_id, "product_name"=>$product_name, 
									   "product_icon"=>$product_icon));
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Support this research with the following resources:",
							   "product"=>$product
							  ));
	}
	else if($action == 'support_research') {
		//test research_id
		if(!is_numeric($research_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Research doesn't exist."
								  )));
		}
		$query = "SELECT research_time FROM research_info WHERE research_id = '$research_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Research doesn't exist."
								  )));
		}
		$row = $result->fetch_row();
		list($research_time) = $row;
		
		//check if research required for this research and if researched
		$query = "SELECT req_research_id, research_name FROM research_order ro, research_info ri 
				  WHERE ro.research_id = '$research_id' AND ri.research_id = req_research_id";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($req_research_id, $req_technology) = $row;
			
			$query = "SELECT * FROM user_researches WHERE user_id = '$user_id' AND is_researched = TRUE AND
					  research_id = '$req_research_id'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit(json_encode(array('success'=>false,
									   'error'=>"You must first research $req_technology"
									  )));
			}
		}
		
		//check if not already researched
		$query = "SELECT * FROM user_researches WHERE user_id = '$user_id' AND is_researched = TRUE AND
				  research_id = '$research_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You already researched this technology."
								  )));
		}
		
		//check if research is not in progress
		$query = "SELECT * FROM user_researches WHERE user_id = '$user_id' AND is_researched = FALSE AND
				  start_date IS NOT NULL";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Research is in progress."
								  )));
		}
		
		//test product
		if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist."
								  )));
		}
		$query = "SELECT product_id FROM product_info WHERE product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Product doesn't exist."
							      )));
		}
		
		//check quantity
		if(!is_numeric($quantity) || $quantity < 0.01) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Quantity must be more than or equal to 0.01"
								  )));
		}
		$quantity= round($quantity, 2);
		if($quantity > 10000) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Quantity must be less than or equal to 10 000"
								  )));
		}
		
		//select required resources
		$query = "SELECT rrr.amount - IFNULL(cpur.amount, 0), product_name 
				  FROM product_info pi, resources_research_req rrr LEFT JOIN collected_products_user_research cpur
				  ON cpur.product_id = rrr.product_id AND user_id = '$user_id' AND cpur.research_id = rrr.research_id
				  WHERE rrr.research_id = '$research_id' AND pi.product_id = rrr.product_id AND rrr.product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"This product is not required."
							      )));
		}
		$row = $result->fetch_row();
		list($req_amount, $product_name) = $row;
		
		if($quantity > $req_amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Only $req_amount $product_name is required"
							      )));
		}
		
		//check if enough user products
		$query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You can only support with 0 $product_name"
							      )));
		}
		$row = $result->fetch_row();
		list($available_amount) = $row;
		
		if($quantity > $available_amount) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You can only support with $available_amount $product_name"
							      )));
		}
		
		//invest
		$query = "SELECT * FROM collected_products_user_research WHERE user_id = '$user_id' 
				  AND research_id = '$research_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "INSERT INTO collected_products_user_research VALUES ('$research_id', '$user_id', '$product_id', '$quantity')";
		}
		else {
			$query = "UPDATE collected_products_user_research SET amount = amount + '$quantity'
					  WHERE user_id = '$user_id' AND research_id = '$research_id' AND product_id = '$product_id'";
		}
		$conn->query($query);
		
		//update user_product
		$query = "UPDATE user_product SET amount = amount - '$quantity' 
				  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$conn->query($query);
		
		//summary
		$query = "SELECT SUM(amount) FROM resources_research_req WHERE research_id = '$research_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($req_products) = $row;
		
		//get collected products for research
		$query = "SELECT IFNULL(SUM(amount), 0) FROM collected_products_user_research WHERE user_id = '$user_id' 
				  AND research_id = '$research_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($collected_prods) = $row;
		
		$research_started = false;
		$end_time = '';
		
		//if collected all resources, start research
		if(($req_products - $collected_prods) <= 0) {
			$query = "INSERT INTO user_researches VALUES ('$research_id', '$user_id', CURRENT_DATE, CURRENT_TIME, FALSE)";
			$conn->query($query);
			
			$end_date_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + ' . $research_time . ' minutes'));
			$date1 = new DateTime(date('Y-m-d H:i:s'));
			$date2 = new DateTime($end_date_time);
			$diff = date_diff($date1,$date2);
			$end_in_days = $diff->format("%d");
			$ends_in = $diff->format("%H:%I:%S");

			$end_time = "$end_in_days days $ends_in";
			
			$research_started = true;
		}
		
		echo json_encode(array("success"=>true,
							   "msg"=>"You have successfully supported this research with $quantity $product_name",
							   "amount"=>$req_amount - $quantity,
							   "collected_perc"=>round((100 / $req_products) * $collected_prods, 2),
							   "collected_prods"=>$collected_prods,
							   "research_started"=>$research_started,
							   "end_time"=>$end_time
							  ));
	}
	else {
		exit(json_encode(array('success'=>false,
							   'error'=>"Invalid request."
							  )));
	}
?>