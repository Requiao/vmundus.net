<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Black Market</p>
		
		<div id="make_offer_div">
			<p id="mod_title">Make offer</p>
			<p id="mod_error"></p>
			<?php
				//get user products
				echo '<drop-down-list id="user_products_list" title="Chose product">';

				$query = "SELECT bmf.product_id, product_name, product_icon, amount, tax, min_price, 
						  max_price, min_quantity, max_quantity 
						  FROM black_market_fees bmf, product_info pi, user_product up
						  WHERE pi.product_id = bmf.product_id AND up.product_id = bmf.product_id 
						  AND user_id = '$user_id'";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($product_id, $product_name, $product_icon, $available_amount, $fee, $min_price, 
						$max_price, $min_quantity, $max_quantity) = $row;
					echo 
						'<item class="upl_product_div" ' .
						' product_id=' . $product_id .
						' available_amount="' . number_format($available_amount, 2, '.', ' ') . '"' .
						' fee=' . $fee * 100 . '%' .
						' min_price=' . $min_price .
						' max_quantity=' . $max_quantity .
						'>' .
						'<img src="../product_icons/' . $product_icon . '" alt="' . $product_name . '">' .
						'<p>' . $product_name . '</p>' .
						'</item>';
				}
				echo '</drop-down-list>';
			?>

		<div id="mod_offer_div">
			<div id="mol_available">
				<p class="mod_label">Available</p>
				<p class="mod_text">N/A</p>
			</div>
			<div id="mol_fee">
				<p class="mod_label">Fee</p>
				<p class="mod_text">N/A</p>
			</div>

			<div id="mol_min_price">
				<p class="mod_label">Min price</p>
				<p class="mod_text">N/A</p>
			</div>
			<div id="mol_sell_quantity">
				<p class="mod_label">Quantity</p>
				<input id="offer_quantity" type="text" maxlength="4"/>
			</div>
			<div id="mol_price">
				<p class="mod_label">Price</p>
				<input id="offer_price" type="text" maxlength="5"/>
			</div>
			<p class="button blue" id="make_offer">Offer</p>
		</div>

		<div id="user_offers_div">
			<p id="uod_title">My offers</p>
			<div class="offer_labels">
				<p class="ol_quantity">Quantity</p>
				<p class="ol_price">Price</p>
				<p class="ol_fee">Fee</p>
			</div>
			<div id="uod_offers">
			<?php
				//get user offers
				$query = "SELECT user_name, seller_id, user_image, offer_id, bmf.product_id, 
						product_name, product_icon, quantity, price, tax 
						FROM black_market_fees bmf, product_info pi, black_market bm, users u, user_profile up 
						WHERE pi.product_id = bmf.product_id AND bm.product_id = bmf.product_id 
						AND seller_id = '$user_id'  AND u.user_id = seller_id AND up.user_id = seller_id
						ORDER BY product_name";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($seller_name, $seller_id, $seller_image, $offer_id, $product_id, $product_name, 
						$product_icon, $quantity, $price, $fee) = $row;

					echo
						'<div class="product_on_sale" offer_id=' . $offer_id . '>' .
							'<a class="seller_name" href="user_profile?id=' . $seller_id . 
							'">' . $seller_name . '</a>' .

							'<img class="user_image" src="../user_images/' . $seller_image . 
							'" alt="user image">' . 

							'<img class="pos_product_icon" src="../product_icons/' . $product_icon . 
							'" alt="' . $product_name . '">' .

							'<p class="pos_quantity">' . $quantity . '</p>' .
							'<p class="pos_price">' . number_format($price, 3, '.', ' ') . '</p>' .
							'<img class="pos_gold_icon" src="../img/gold.png" alt="gold">' .
							'<p class="pos_tax">' . number_format($fee * 100, 2, '.', ' ') . '%</p>' .
							'<p class="pos_remove button red">Remove</p>' .
						'</div>';
				}
			
			?>
			</div>
		</div>

		
		<div id="product_list">
			<p id="but_prods_title">Buy product</p>
			<?php
				$query = "SELECT * FROM product_info ORDER BY product_name";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($product_id, $product_name, $product_icon) = $row;
					echo "\n\t\t\t" . '<div class="product_market_icon" product_id="' . $product_id . '">' .
						 "\n\t\t\t\t" . '<abbr title="' . $product_name . '"><img src="../product_icons/' . 
						 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t" . '</div>';
				}
			?>
		
		</div>
		
		<div id="product_offers">
			<p id="po_error"></p>	
			<div class="offer_labels">
				<p class="ol_quantity">Quantity</p>
				<p class="ol_price">Price</p>
			</div>
			<div id="po_offers">

			</div>
		</div>
	
</main>
<?php include('footer.php'); ?>