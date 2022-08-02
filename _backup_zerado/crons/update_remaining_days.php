<?php
	//Description: Update remaining days for different agreements. Run every hour

	include('/var/www/html/connect_db.php');
	include('/var/www/html/crons/timezone_id.php'); //$timezone_id
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).

	
	/* Import permission/embargo */
	$query = "SELECT country_id, from_country_id, product_id, days FROM product_import_tax 
			  WHERE permission = TRUE AND country_id IN 
			 (SELECT country_id FROM country WHERE timezone_id = '$timezone_id')";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($country_id, $from_country_id, $product_id, $days) = $row;
		$days--;
		if($days <= 0) {//expired.
			$query = "UPDATE product_import_tax SET permission = FALSE WHERE country_id = '$country_id'
					  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
			$conn->query($query);
		}
		else {
			$query = "UPDATE product_import_tax SET days = '$days' WHERE country_id = '$country_id'
					  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
			$conn->query($query);
		}
	}
	
	/* Travel agreement. */
	$query = "SELECT country_id, from_country_id, days FROM travel_agreement
			  WHERE permission = TRUE AND country_id IN 
			  (SELECT country_id FROM country WHERE timezone_id = '$timezone_id')";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($country_id, $from_country_id, $days) = $row;
		$days--;
		if($days <= 0) {//expired.
			$query = "UPDATE travel_agreement SET permission = FALSE WHERE country_id = '$country_id'
					  AND from_country_id = '$from_country_id'";
			$conn->query($query);
		}
		else {
			$query = "UPDATE travel_agreement SET days = '$days' WHERE country_id = '$country_id'
					  AND from_country_id = '$from_country_id'";
			$conn->query($query);
		}
	}
	
	/* defence agreement. */
	$query = "SELECT country_id, with_country_id, days FROM defence_agreements WHERE is_allies = TRUE AND
			  country_id IN  (SELECT country_id FROM country WHERE timezone_id = '$timezone_id')";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($country_id, $with_country_id, $days) = $row;
		$days--;
		if($days <= 0) {//expired.
			$query = "UPDATE defence_agreements SET is_allies = FALSE WHERE country_id = '$country_id'
					  AND with_country_id = '$with_country_id'";
			$conn->query($query);
		}
		else {
			$query = "UPDATE defence_agreements SET days = '$days' WHERE country_id = '$country_id'
					  AND with_country_id = '$with_country_id'";
			$conn->query($query);
		}
	}
?>