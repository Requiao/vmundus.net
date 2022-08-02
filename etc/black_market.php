<?php
    //Description: Buy/offer product on black market.
    
    session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
    include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
    include('../php_functions/get_time_for_id.php');//getTimeForId();
    
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	$product_id =  htmlentities(stripslashes(strip_tags(trim($_POST['product_id']))), ENT_QUOTES);
	$quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	$price =  htmlentities(stripslashes(strip_tags(trim($_POST['price']))), ENT_QUOTES);
	$offer_id =  htmlentities(stripslashes(strip_tags(trim($_POST['offer_id']))), ENT_QUOTES);
	//$selected_quantity =  htmlentities(stripslashes(strip_tags(trim($_POST['quantity']))), ENT_QUOTES);
	
    $user_id = $_SESSION['user_id'];
    $MAX_OFFERS = 3;
    
    if($action == 'buy_product') {
        //check offer
        if(!is_numeric($offer_id)) {
			exit(json_encode(array(
                'success'=>false,
                'error'=>"Offer doesn't exist."
            )));
        }

        //check quantity
        $options = array(
            'options' => array(
                'min_range' => 1,
                'max_range' => 10000
            )
        );
        if(!filter_var($quantity, FILTER_VALIDATE_INT, $options)) {
            exit(json_encode(array('success'=>false,
                                   'error'=>"Quantity must be a whole number."
                                  )));
        }

        //get offer details
        $query = "SELECT seller_id, offer_id, bmf.product_id, 
                  product_name, quantity, price, tax, min_quantity
                  FROM black_market_fees bmf, product_info pi, black_market bm
                  WHERE pi.product_id = bmf.product_id AND bm.product_id = bmf.product_id 
                  AND offer_id = '$offer_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Offer doesn't exist."
            )));
        }
        $row = $result->fetch_row();
        list($seller_id, $offer_id, $product_id, $product_name, $offered_quantity, $price, 
             $fee, $min_quantity) = $row;
        
        //is enough offered
        if($quantity > $offered_quantity) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Only $offered_quantity $product_name are available to buy."
            )));
        }

        //is buying min_quantity
        if($quantity < $min_quantity) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You must buy at least $min_quantity $product_name."
            )));
        }

        //calculate payment
        $payment = $quantity * $price;
        $fee_collected = round($fee * $payment, 3);

        //check if seller has enough room for gold
        //check if enough space in the warehouse
		$query = "SELECT SUM(amount) AS total_fill, (SELECT capacity FROM user_warehouse 
                  WHERE user_id = '$seller_id') AS warehouse_capacity
                  FROM user_product WHERE user_id = '$seller_id'";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        list($total_fill, $warehouse_capacity) = $row;
        $need_space = ($total_fill + ($payment - $fee_collected)) - $warehouse_capacity;
        if($need_space > 0) {
            //warn seller
            $notification = "You do not have enough room in the warehouse to store Gold." .
                            " Products on the Black Market cannot be sold." .
                            " You need $need_space extra space.";
            sendNotification($notification, $seller_id);

            exit(json_encode(array(
                'success'=>false,
                'error'=>'Seller does not have enough room in his warehouse to store Gold.'
            )));
        }

        //check if buyer has enough gold
        $query = "SELECT amount FROM user_product WHERE user_id = '$user_id' AND product_id = 1";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You don't have enough Gold. You need $payment Gold."
            )));
        }
        list($buyers_gold) = $row;

        if($payment > $buyers_gold) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You don't have enough Gold. You need $payment Gold."
            )));
        }

        //check buyers warehouse
        $query = "SELECT SUM(amount) AS total_fill, (SELECT capacity FROM user_warehouse 
                  WHERE user_id = '$user_id') AS warehouse_capacity
                  FROM user_product WHERE user_id = '$user_id'";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        list($total_fill, $warehouse_capacity) = $row;
        $need_space = ($total_fill + $quantity) - $warehouse_capacity;
        if($need_space > 0) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You not have enough room in the warehouse. You need $need_space extra space."
            )));
        }

        //get gold from the buyer
        $query = "UPDATE user_product SET amount = amount - '$payment' WHERE user_id = '$user_id'
                  AND product_id = 1";
        $conn->query($query);

        //give product to the buyer
        $query = "SELECT * FROM user_product WHERE user_id = '$user_id' AND product_id = '$product_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = amount + '$quantity' 
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$user_id', '$product_id', '$quantity')";
        }
        $conn->query($query);
        
        //give gold to the seller
        $payment_after_fee = $payment - $fee_collected;
        $query = "SELECT * FROM user_product WHERE user_id = '$seller_id' AND product_id = '1'";
		$result = $conn->query($query);
		if($result->num_rows == 1) { 
			$query = "UPDATE user_product SET amount = amount + '$payment_after_fee' 
                      WHERE user_id = '$seller_id' AND product_id = '1'";
		}
		else {
			$query = "INSERT INTO user_product VALUES('$seller_id', '1', '$payment_after_fee')";
        }
        $conn->query($query);

        //update/remove offer
        if($offered_quantity <= $quantity) {
            $query = "DELETE FROM black_market WHERE offer_id = '$offer_id'";
            $conn->query($query);
        }
        else {
            $query = "UPDATE black_market SET quantity = quantity - '$quantity'
                      WHERE offer_id = '$offer_id'";
            $conn->query($query);
        }

        //record purchase
        $query = "INSERT INTO black_market_history VALUES ('$offer_id', '$seller_id', '$user_id',
                 '$product_id', '$quantity', '$price', '$fee', CURRENT_DATE, CURRENT_TIME)";
        $conn->query($query);

        //notify seller
        $notification = "$quantity $product_name have been sold for $payment Gold on the" .
                        " Black market. $fee_collected Gold fee have been collected.";
        sendNotification($notification, $seller_id);

        echo json_encode(array(
            'success'=>true,
            "msg"=>"You have successfully bought $quantity $product_name for $payment Gold.",
            "products_left"=>$offered_quantity - $quantity,
        ));
    }
    else if($action == 'get_offers') {
        //check product_id
        if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
            exit(json_encode(array('success'=>false,
                                   'error'=>"Product doesn't exist."
                                  )));
        }
        $query = "SELECT * FROM product_info WHERE product_id = '$product_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Product doesn't exist."
            )));
        }

        //get offers
        $query = "SELECT user_name, seller_id, user_image, offer_id, bmf.product_id, 
                  product_name, product_icon, quantity, price, tax 
                  FROM black_market_fees bmf, product_info pi, black_market bm, users u, user_profile up 
                  WHERE pi.product_id = bmf.product_id AND bm.product_id = bmf.product_id 
                  AND u.user_id = seller_id AND up.user_id = seller_id
                  AND bmf.product_id = '$product_id'
                  ORDER BY price ASC";
        $result = mysqli_query($conn, $query);
        if($result->num_rows == 0) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"No offers."
            )));
        }

        $offers = [];
        while($row = $result->fetch_row()) {
            list($seller_name, $seller_id, $seller_image, $offer_id, $product_id, $product_name, 
                 $product_icon, $quantity, $price, $fee) = $row;

            array_push($offers, array('seller_name'=>$seller_name, 'seller_id'=>$seller_id, 
                                      'seller_image'=>$seller_image,
                                      'offer_id'=>$offer_id, 'product_id'=>$product_id, 
                                      'product_name'=>$product_name, 'product_icon'=>$product_icon, 
                                      'quantity'=>$quantity, 
                                      'price'=>number_format($price, 3, '.', ' ')
                                    ));
        }
        echo json_encode(array(
            'success'=>true,
            'offers'=>$offers
        ));
    }
    else if($action == 'remove_offer') {
        //check offer
        if(!is_numeric($offer_id)) {
			exit(json_encode(array(
                'success'=>false,
                'error'=>"Offer doesn't exist."
            )));
        }
        
        $query = "SELECT quantity, product_id FROM black_market 
                  WHERE seller_id = '$user_id' AND offer_id = '$offer_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Offer doesn't exist."
            )));
        }
        $row = $result->fetch_row();
        list($quantity, $product_id) = $row;

        //check if enough space in the warehouse
		$query = "SELECT SUM(amount) AS total_fill, (SELECT capacity FROM user_warehouse 
                  WHERE user_id = '$user_id') AS warehouse_capacity
                  FROM user_product WHERE user_id = '$user_id'";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        list($total_fill, $warehouse_capacity) = $row;
        $need_space = ($total_fill + $payment) - $warehouse_capacity;
        if($need_space > 0) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Not enough space in the warehouse. You need " . 
                          number_format($need_space, '0', '', ' ') . " extra space."
            )));
        }

        $query = "DELETE FROM black_market WHERE offer_id = '$offer_id'";
		if($conn->query($query)) {
			$query = "UPDATE user_product SET amount = amount + '$quantity'
					  WHERE user_id = '$user_id' AND product_id = '$product_id'";
            $conn->query($query);
            
			echo json_encode(array(
                'success'=>true
            ));
		}
		else {
			exit(json_encode(array('success'=>false,
								   'error'=>"Error. Try again."
								  )));
		}
    }
    else if($action == 'make_offer') {
        //check product_id
        if(!filter_var($product_id, FILTER_VALIDATE_INT)) {
            exit(json_encode(array('success'=>false,
                                   'error'=>"Product doesn't exist."
                                  )));
        }
        $query = "SELECT * FROM black_market_fees WHERE product_id = '$product_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array('success'=>false,
                                   'error'=>"Product doesn't exist."
                                  )));
        }

        //check quantity
        $options = array(
            'options' => array(
                'min_range' => 1,
                'max_range' => 10000
            )
        );
        if(!filter_var($quantity, FILTER_VALIDATE_INT, $options)) {
            exit(json_encode(array('success'=>false,
                                   'error'=>"Quantity must be a whole number."
                                  )));
        }

        //check price
        if(!is_numeric($price)) {
			exit(json_encode(array(
                'success'=>false,
                'error'=>"Price must be more than or equal to 0.1."
            )));
        }

        //get total_offers & total_offered of this product
        $query = "SELECT IFNULL(SUM(quantity), 0), COUNT(*) FROM black_market 
                  WHERE seller_id = '$user_id' AND product_id = '$product_id'";
        
        $total_offered = 0;
        $result = mysqli_query($conn, $query);
        $row = $result->fetch_row();
        list($total_offered, $total_offers) = $row;
        if($total_offers >= $MAX_OFFERS) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You can only make $MAX_OFFERS for each product."
            )));
        }
        
        //get requirements for the offer
        $query = "SELECT bmf.product_id, product_name, product_icon, amount, tax, 
                  min_price, max_price, min_quantity, max_quantity 
                  FROM black_market_fees bmf, product_info pi, user_product up
                  WHERE pi.product_id = bmf.product_id AND up.product_id = bmf.product_id 
                  AND user_id = '$user_id' AND bmf.product_id = '$product_id'";
        $result = mysqli_query($conn, $query);
        $row = $result->fetch_row();
        list($product_id, $product_name, $product_icon, $available_amount, $fee, $min_price, 
             $max_price, $min_quantity, $max_quantity) = $row;
        
        //check max quantity
        if($quantity > $max_quantity) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You can only offer $max_quantity $product_name."
            )));
        }

        if($total_offered + $quantity > $max_quantity) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You can offer " . ($max_quantity - $total_offered < 0 
                    ? 0 : $max_quantity - $total_offered) . " more $product_name."
            )));
        }

        //check min quantity
        if($quantity < $min_quantity) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You must offer at least $min_quantity $product_name."
            )));
        }

        //check available quantity
        if($available_amount < $quantity) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"You don't have enough $product_name."
            )));
        }

        //check min price
        if($price < $min_price) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Price must be at least $min_price Gold for $product_name."
            )));
        }

        //check max price
        if($price > $max_price) {
            exit(json_encode(array(
                'success'=>false,
                'error'=>"Price must not be more than $max_price Gold for $product_name."
            )));
        }

        //create offer
        $offer_id = getTimeForId() . $user_id;
        $query = "INSERT INTO black_market VALUES ('$offer_id', '$user_id', '$product_id', 
                 '$quantity', '$price', CURRENT_DATE, CURRENT_TIME)";
        if($conn->query($query)) {
            //take products from the warehouse
            $query = "UPDATE user_product SET amount = amount - '$quantity' WHERE
                      user_id = '$user_id' AND product_id = '$product_id'";
            $conn->query($query);

            //get offers
            $query = "SELECT user_name, seller_id, user_image, offer_id, bmf.product_id, 
                      product_name, product_icon, quantity, price, tax 
                      FROM black_market_fees bmf, product_info pi, black_market bm, users u, user_profile up 
                      WHERE pi.product_id = bmf.product_id AND bm.product_id = bmf.product_id 
                      AND u.user_id = seller_id AND up.user_id = seller_id
                      AND offer_id = '$offer_id'
                      ORDER BY price ASC";
            $result = mysqli_query($conn, $query);
            $offer = [];
            $row = $result->fetch_row();
            list($seller_name, $seller_id, $seller_image, $offer_id, $product_id, $product_name, 
                 $product_icon, $quantity, $price, $fee) = $row;

            $offer = array('seller_name'=>$seller_name, 'seller_id'=>$seller_id, 
                            'seller_image'=>$seller_image,
                            'offer_id'=>$offer_id, 'product_id'=>$product_id, 
                            'product_name'=>$product_name, 'product_icon'=>$product_icon, 
                            'quantity'=>$quantity, 
                            'price'=>number_format($price, 3, '.', ' '), 
                            'fee'=>number_format($fee * 100, 2, '.', ' ') . '%');

            echo json_encode(array(
                'success'=>true,
                'msg'=>"Offer successfully made.",
                'available_amount'=>number_format($available_amount - $quantity, 2, '.', ' '),
                'offer'=>$offer
            ));
        }
    }
    else {
        exit(json_encode(array(
            'success'=>false,
            'error'=>"Invalid request"
        )));
    }
?>