<?php
	//Description: Record product price. Run at 47min.

	include('/var/www/html/connect_db.php');
	
	$query = "SET @num := 0"; 
	$conn->query($query);
	$query = "SET @product_id := ''";
	$conn->query($query);
	$query = "SELECT country_id, product_id, price, quantity FROM (
			  SELECT country_id, product_id, price, quantity,
			  @num := IF(@product_id = product_id, @num + 1, 1) AS row_number,
			  @product_id := product_id AS dummy
			  FROM product_market
			  ORDER BY country_id, product_id, price) AS x 
			  WHERE x.row_number <= 3 ORDER BY product_id, country_id";
	$result = $conn->query($query);
	
	$product_array = array();
	$x = 0;
	$current_prod = 0;
	$current_country_id = 0;
	$total_products = 0;
	
	$num_rows = $result->num_rows;
	while($row = $result->fetch_row()) {
		list($country_id, $product_id, $price, $quantity) = $row;

		if(($current_prod != $product_id && $current_prod != 0) ||
		($current_country_id != $country_id && $current_country_id != 0)) {//calculate avg. records only on product and country change
			recordAvgPrice($product_array, $total_products);
			
			$product_array = array();
			$total_products = 0;
			$x = 0;
		}

		$current_prod = $product_id;
		$current_country_id = $country_id;
		$product_array[$x]['country_id'] = $country_id;
		$product_array[$x]['product_id'] = $product_id;
		$product_array[$x]['price'] = $price;
		$product_array[$x]['quantity'] = $quantity;
		$total_products += $quantity;
		$x++;
	}
	if($num_rows > 0) {//if it's a last type, it will not go into the loop. call func manually
		recordAvgPrice($product_array, $total_products);
	}
	
	function recordAvgPrice($product_array, $total_products) {
		global $conn;
		if($total_products > 0) {
			$product_influence_percent = 100/$total_products;
		}
		else {
			$product_influence_percent = 0;
		}
		$average_price = 0;
		
		for($x = 0; $x < count($product_array); $x++) {
			$average_price += $product_influence_percent * $product_array[$x]['quantity'] * $product_array[$x]['price'];
		}
		$average_price = round($average_price / 100, 2);
		
		//record
		$country_id = $product_array[0]['country_id'];
		$product_id = $product_array[0]['product_id'];
		$query = "SELECT price, avg_quantity FROM average_product_price WHERE country_id = '$country_id' AND
				 product_id = '$product_id' AND date = CURRENT_DATE";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "INSERT INTO average_product_price VALUES('$country_id', '$product_id', '$average_price', 
					  '$total_products', CURRENT_DATE)";
			$conn->query($query);	  
		}
		else {
			$row = $result->fetch_row();
			list($price, $avg_quantity) = $row;
			$avg_price = ($price + $average_price) / 2;
			
			$avg_quantity = ($total_products + $avg_quantity) / 2;
			
			$query = "UPDATE average_product_price SET price = '$avg_price' WHERE country_id = '$country_id' AND
					 product_id = '$product_id' AND date = CURRENT_DATE";
			$conn->query($query);
			
			$query = "UPDATE average_product_price SET avg_quantity = '$avg_quantity' WHERE country_id = '$country_id' AND
					 product_id = '$product_id' AND date = CURRENT_DATE";
			$conn->query($query);
		}
	}
?>