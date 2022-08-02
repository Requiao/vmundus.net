
<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head">Gold Market</p>
		<?php
			echo "\n\t\t" . '<div id="id_heads">' .
				 "\n\t\t\t" . '<p id="idh_bonus">Bonus</p>' .
				 "\n\t\t\t" . '<p id="idh_price">Price in Gold</p>' .
				 "\n\t\t" . '</div>';
			
			//check if president/minister
			$query = "SELECT position_id, country_id FROM country_government WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$buy_for_country = '<p class="buy_for_country button green">Country</p>';
			}
			else {
				$buy_for_country = '';
			}
			
			
			$query = "SELECT price, bonus, product_name, product_icon, pi.product_id
					  FROM gold_market_offers gmo, product_info pi
					  WHERE pi.product_id = gmo.product_id AND available = TRUE";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($price, $bonus, $product_name, $product_icon, $product_id) = $row;
				echo "\n\t\t" . '<div class="item_div">' .
					 "\n\t\t\t" . '<p class="reply_msg"></p>' .
					 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img src="../product_icons/' . $product_icon . '"></abbr>' .
					 "\n\t\t\t" . '<p class="quantity">' . $quantity . '</p>' .
					 "\n\t\t\t" . '<p class="bonus">+' . $bonus . '%</p>' .
					 "\n\t\t\t" . '<p class="price">' . $price . '</p>' .
					 "\n\t\t\t" . '<input class="buy_quantity" placeholder="quantity" maxlength="3">' .
					 "\n\t\t\t" . '<p class="buy button green">Buy</p>' .
					 "\n\t\t\t" . '<p hidden>' . $product_id . '</p>' .
					 "\n\t\t\t" . $buy_for_country .
					 "\n\t\t" . '</div>';
			}
		?>
	
	</div>

</main>
	
<?php include('footer.php'); ?> 