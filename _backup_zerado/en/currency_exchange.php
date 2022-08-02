<?php 
	include('head.php'); 
	include('../php_functions/cut_long_name.php')//cutLongName($string, $max_length).php'); 

?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Currency Exchange</p>
		
		<div id="currency_exchange_controls">
			<p id="buy_currency_h">Buy:</p>
			<div id="buying_item"><img class="gold_img" src="../img/gold.png"></div>
			<i class="fa fa-exchange" id="switch_buying_items"></i>
			<p id="sell_currency_h">Sell:</p>
			<?php
			
				//currently governor
				$buy_for_country = '';
				$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$buy_for_country = '<p class="button blue buy_for_country">Ministry</p>';
				}
			
				//if redirected from corporation
				$corporation_id =  htmlentities(stripslashes(strip_tags(trim($_GET['corp_id']))), ENT_QUOTES);
				$buy_for_corp = '';
				if(!empty($corporation_id) && is_numeric($corporation_id)) {
					$query = "SELECT manager_id FROM corporations WHERE corporation_id = '$corporation_id' AND corporation_id =
							  (SELECT corporation_id FROM corporation_stock_owners WHERE user_id = '$user_id'
							  AND corporation_id = '$corporation_id' AND amount > 0)";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						echo "\n\t\t" . '<p id="corp_id" hidden>' . $corporation_id . '</p>';
						$buy_for_corp = '<p class="button green buy_for_corp">Corp. Buy</p>';
					}
					$buy_for_country = '';
				}
			
				//buy currency
				echo "\n\t\t" . '<div id="selling_item">' .
					 "\n\t\t\t" . '<div id="currency_list">' .
					 "\n\t\t\t\t" . '<div id="selected_currency">' . 
					 "\n\t\t\t\t\t" . '<p>Select Currency</p>' . 
					 "\n\t\t\t\t" . '</div>' .  
					 "\n\t\t\t\t" . '<p id="currency_id" hidden>0</p>' .  
					 "\n\t\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div id="currency_div">';
				
				$query = "SELECT currency_name, currency_abbr, flag, cu.currency_id FROM country c, currency cu WHERE
						  cu.flag_id = c.country_id ORDER BY currency_name";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($currency_name, $currency_abbr, $flag, $currency_id) = $row;
					echo "\n\t\t\t\t" . '<div class="currency" id="' . $currency_id . '">' . 
						 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
						 "\n\t\t\t\t\t" . '<p>' . $currency_name . ' (' . $currency_abbr . ')</p>' . 
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			?>
			<p id="make_offer_btn">Make offer</p>
		</div>
		
		<div id="make_offer_div">
			<p id="offer_currency_h">Offer:</p>
			<div id="offering_item"><img class="gold_img" src="../img/gold.png"></div>
			<input id="offering_amount" type="text" maxlength="7" placeholder="amount">
			<i class="fa fa-exchange" id="switch_offering_items"></i>
			<p id="offer_for_currency_h">For:</p>
			<?php
				echo "\n\t\t" . '<div id="offer_for_item">' .
					 "\n\t\t\t" . '<div id="offer_currency_list">' .
					 "\n\t\t\t\t" . '<div id="offer_selected_currency">' . 
					 "\n\t\t\t\t\t" . '<p>Select Currency</p>' . 
					 "\n\t\t\t\t" . '</div>' .  
					 "\n\t\t\t\t" . '<p id="offer_currency_id" hidden>0</p>' .  
					 "\n\t\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t\t" . '<div id="offer_currency_div">';
				
				$query = "SELECT currency_name, currency_abbr, flag, cu.currency_id, IFNULL(amount, 0) 
						  FROM country c, currency cu LEFT JOIN user_currency uc 
						  ON cu.currency_id = uc.currency_id AND user_id = '$user_id' WHERE
						  cu.flag_id = c.country_id 
						  ORDER BY currency_name";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($currency_name, $currency_abbr, $flag, $currency_id, $amount) = $row;
					echo "\n\t\t\t\t" . '<div class="offer_currency" id="' . $currency_id . '">' . 
						 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
						 "\n\t\t\t\t\t" . '<p>' . $currency_abbr . ' (' . number_format($amount, 2, '.', ' ') . ')</p>' . 
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			?>
			<input id="offering_rate" type="text" maxlength="7" placeholder="rate">
			<p id="place_offer_btn">Offer</p>
		</div>
		
		<div id="user_offers">
		<?php
			$query = "SELECT u.user_id, user_name, user_image, cu.currency_abbr,
					  rate, amount, offer_id, action
					  FROM monetary_market mm, users u, currency cu, user_profile up
					  WHERE u.user_id = mm.user_id AND up.user_id = mm.user_id
					  AND cu.currency_id = mm.currency_id AND mm.user_id = '$user_id'
					  ORDER BY rate ASC";
			$result = $conn->query($query);
			echo "\n\t\t" . '<p id="user_offers_head">My Offers:</p>' . 
				 "\n\t\t" . '<p id="amount_head">Amount</p>' . 
				 "\n\t\t" . '<p id="price_head">Rate</p>';
			while($row = $result->fetch_row()) {
				list($seller_id, $seller_name, $seller_img, $currency_abbr, $rate,
					 $amount, $offer_id, $action) = $row;
				
				echo "\n\t\t" . '<div class="offer _' . $offer_id . '">' .
					 "\n\t\t\t" . '<p class="offer_id" hidden>' . $offer_id . '</p>' .
					 "\n\t\t\t" . '<a href="user_profile?id=' . $seller_id . '" class="user_name">' .
					 "\n\t\t\t" . '<p>' . $seller_name . '</p></a>' . 
					 "\n\t\t\t" . '<img class="user_image" src="../user_images/' . $seller_img . '" alt="user image" target="_new">';
				
				if($action == 'sell') {
					echo "\n\t\t\t" . '<p class="amount_selling">' . number_format($amount, 2, '.', ' ') . ' ' . $currency_abbr . '</p>' .
						 "\n\t\t\t" . '<p class="rate">1 ' . $currency_abbr . ' = ' . number_format($rate, 3, '.', ' ') . '</p>' .
						 "\n\t\t\t" . '<img class="gold_img" src="../img/gold.png">';
				}
				else if ($action == 'buy') {
					echo "\n\t\t\t" . '<p class="amount_selling">' . number_format($amount, 2, '.', ' ') . ' Gold</p>' .
						 "\n\t\t\t" . '<p class="gold_rate">1 </p>' .
						 "\n\t\t\t" . '<img class="gold_img" src="../img/gold.png">' .
						 "\n\t\t\t" . '<p class="rate">= ' . number_format($rate, 0, '.', ' ') . ' ' . $currency_abbr . '</p>';
						 
				}
				
				echo "\n\t\t\t" . '<p class="button red remove_offer">Remove</p>' .
					 "\n\t\t\t" . '</div>';
			}
		?>
		
		</div>
		
		<div id="currency_offers">
		</div>

	</div>
	
</main>

<?php include('footer.php'); ?>