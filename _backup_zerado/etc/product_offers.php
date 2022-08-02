<?php
//Description: offer products on the market.

	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$country_id =  htmlentities(stripslashes(strip_tags(trim($_POST['country_id']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	//check product
	if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Product doesn't exist"
							  )));
	}
	$query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Product doesn't exist"
							  )));
	}
	
	//check country
	if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Country doesn't exist"
							  )));
	}
	$query = "SELECT * FROM country WHERE country_id = '$country_id'";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		exit(json_encode(array('success'=>false,
							   'error'=>"Country doesn't exist"
							  )));
	}
	
	//determine if governor
	//check if president
	$is_governor = false;
	$query = "SELECT * FROM country_government WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 1) {
		$is_governor = true;
	}

	$offers = array();
	
	//select all offers of specific product id
	$query = "SELECT user_name, user_image, up.user_id, pm.country_id, pm.product_id, pm.quantity, pm.price, pm.offer_id, currency_abbr, 
			  pi.product_icon, 'User' 
			  FROM product_market pm, user_profile up, country c, product_info pi, currency cu, users u
			  WHERE pm.product_id = '$product_id' AND pm.country_id = '$country_id' AND pm.country_id = c.country_id
			  AND pm.user_id = up.user_id AND pm.product_id = pi.product_id AND cu.currency_id = c.currency_id 
			  AND u.user_id = up.user_id
			  UNION
			  SELECT user_name, user_image, up.user_id, pm.country_id, pm.product_id, pm.quantity, pm.price, pm.offer_id, currency_abbr, 
			  pi.product_icon,  'Company'
			  FROM product_market pm, user_profile up, country c, product_info pi, currency cu, user_building ub, users u
			  WHERE pm.product_id = '$product_id' AND pm.country_id = '$country_id' AND pm.country_id = c.country_id
			  AND pm.product_id = pi.product_id AND cu.currency_id = c.currency_id AND ub.company_id = pm.company_id 
			  AND up.user_id = ub.user_id AND u.user_id = up.user_id 
			  UNION
			  SELECT user_name, user_image, up.user_id, pm.country_id, pm.product_id, pm.quantity, pm.price, pm.offer_id, currency_abbr, 
			  pi.product_icon, 'Government' 
			  FROM product_market pm, user_profile up, country c, product_info pi, currency cu, country_government cg, users u
			  WHERE pm.product_id = '$product_id'  AND pm.country_id = '$country_id' AND pm.country_id = c.country_id
			  AND pm.product_id = pi.product_id AND cu.currency_id = c.currency_id AND u.user_id = up.user_id
			  AND up.user_id = cg.user_id AND cg.position_id = pm.position_id AND cg.country_id = pm.for_country_id
			  ORDER BY price ASC";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($user_name, $user_image, $seller_id, , , $quantity, $price, $offer_id, $currency_abbr, $product_icon, $seller) = $row;
		array_push($offers, array("user_name"=>$user_name, "user_image"=>$user_image, "user_id"=>$seller_id, "quantity"=>$quantity,
				   "price"=>$price, "offer_id"=>$offer_id, "currency_abbr"=>$currency_abbr, "product_icon"=>$product_icon, 
				   "seller"=>$seller));
	}
	
	echo json_encode(array("success"=>true,
						   "offers"=>$offers,
						   "governor"=>$is_governor
						  ));
?>