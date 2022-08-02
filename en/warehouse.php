<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
	<div id="warehouse_div">
		<?php
			$query = "SELECT IFNULL(SUM(amount), 0), capacity FROM user_warehouse uw LEFT JOIN user_product up ON 
					 uw.user_id = up.user_id WHERE up.user_id = '$user_id'";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_row($result);
			list($filled, $capacity) = $row;
			echo "\n\t\t" . '<div id="warehouse_head_div">' .
				 "\n\t\t\t" . '<div id="warehouse_fill_details">' .
				 "\n\t\t\t\t" . '<p id="warehouse_fill">' . number_format($filled, '2', '.', ' ') . '</p>' .
				 "\n\t\t\t\t" . '<p>/</p>' .
				 "\n\t\t\t\t" . '<p id="max_warehouse_fill">' . number_format($capacity, '2', '.', ' ') . '</p>' .
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t\t" . '<p id="ware_head">Warehouse</p>' .
				 "\n\t\t\t" . '<p id="x10_upgrade_warehouse">X10 Upgrade</p>' .
				 "\n\t\t\t" . '<p id="upgrade_warehouse">Upgrade</p>' .
				 "\n\t\t" . '</div>';	
				 
			$query = "SELECT up.product_id, product_icon, amount, product_name FROM product_info pi, user_product up 
					  WHERE pi.product_id = up.product_id AND user_id = '$user_id'";
			$result = mysqli_query($conn, $query);
			while($row = mysqli_fetch_row($result)) {
				list($product_id, $product_icon, $amount, $product_name) = $row;
				echo "\n\t\t" . '<div class="icon_amount">' . "\n" . 
						"\t\t\t" . '<abbr title="' . $product_name . '"><img class="product_icon" src="../product_icons/' . 
						$product_icon . '" alt="'  . $product_name . '"></abbr>' . "\n" .
						"\t\t\t" . '<p class="amount">' . number_format($amount, 2, '.', ' ') . '</p>' . "\n" .
						"\t\t\t" . '<p class="sell">Sell</p>' . "\n" .
						"\t\t\t" . '<p id="pi_' . $product_id . '" hidden>' . $product_id . '</p>' . "\n" .
					 "\t\t" . '</div>' . "\n";
			}
		?>
		
	</div>
	
	<div id="product_offers_div">
		<p id="q">Quantity</p>
		<p id="p">Price</p>
		<p id="t">Tax</p>
		<?php
			$query = "SELECT flag, quantity, price, IFNULL(sale_tax, 1), product_icon, product_name, offer_id, currency_abbr
					  FROM user_profile up, product_info pi, currency cu, country c, product_market pm LEFT JOIN product_import_tax pit
					  ON pit.product_id = pm.product_id AND pit.country_id = pm.country_id 
					  AND from_country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')
					  WHERE c.country_id = pm.country_id
					  AND pi.product_id = pm.product_id AND c.currency_id = cu.currency_id
					  AND up.user_id = pm.user_id AND pm.user_id = '$user_id'
					  UNION
					  SELECT flag, quantity, price, sale_tax, product_icon, product_name, offer_id, currency_abbr 
					  FROM product_market pm, country c, product_sale_tax pst, user_profile up, product_info pi, currency cu
					  WHERE c.country_id = pm.country_id AND pst.country_id = c.country_id AND pst.country_id = up.citizenship 
					  AND pst.product_id = pm.product_id AND pi.product_id = pm.product_id AND  c.currency_id = cu.currency_id
					  AND up.user_id = pm.user_id AND pm.user_id = '$user_id'";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($flag, $quantity, $price, $sale_tax, $product_icon, $product_name, $offer_id, $currency_abbr) = $row;
				echo "\n\t\t" . '<div class="product_on_sale">' .
					 "\n\t\t\t" . '<abbr title="' . $product_name . '"><img class="pos_product_icon" src="../product_icons/' . 
					 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
					 "\n\t\t\t" . '<img class="country_flag" alt="' . $flag . '" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t" . '<p class="pos_quantity">' . number_format($quantity, '0', '', ' ') . '</p>' .
					 "\n\t\t\t" . '<p class="pos_price">' . number_format($price, '2', '.', ' ') . '</p>' .
					 "\n\t\t\t" . '<p class="pos_currency">' . $currency_abbr . '</p>' .
					 "\n\t\t\t" . '<p class="pos_tax">-' . $sale_tax . '%</p>' .
					 "\n\t\t\t" . '<p class="pos_remove button red">Remove</p>' .
					 "\n\t\t\t" . '<p id="oi_' . $offer_id . '" hidden>' . $offer_id . '</p>' .
					 "\n\t\t" . '</div>';
			}
		?>
		
	</div>
	</div>
	
</main>

<?php include('footer.php'); ?>