<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Product Market</p>
		<?php	
			$query = "SELECT country_name, flag, country_id FROM country WHERE country_id = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_row($result);
			list($country_name, $flag, $country_id) = $row;
				
			echo "\n\t\t" . '<div id="country_list">' .
				 "\n\t\t\t" . '<div id="country">' . 
				 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
				 "\n\t\t\t\t" . '<p>' . $country_name . '</p>' . 
				 "\n\t\t\t" . '</div>' . 
				 "\n\t\t\t" . '<p id="get_country_id" hidden>' . $country_id . '</p>' .  
				 "\n\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
				 "\n\t\t" . '</div>' .
				 "\n\t\t\t" . '<div id="countries_div">';
			
			$query = "SELECT country_name, flag, country_id FROM country ORDER BY country_name";
			$result = mysqli_query($conn, $query);
			while($row = mysqli_fetch_row($result)) {
				list($country, $flag, $country_id) = $row;
				echo "\n\t\t\t\t" . '<div class="country" id="' . $country_id . '">' . 
					 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t\t" . '<p>' . $country . '</p>' . 
					 "\n\t\t\t\t" . '</div>';
			}
			echo "</div>";
		?>

		<div id="product_list">
			<?php
				$query = "SELECT * FROM product_info ORDER BY product_name";
				$result = mysqli_query($conn, $query);
				while($row = mysqli_fetch_row($result)) {
					list($product_id, $product_name, $product_icon) = $row;
					echo "\n\t\t\t" . '<div class="product_market_icon">' .
						 "\n\t\t\t\t" . '<abbr title="' . $product_name . '"><img src="../product_icons/' . 
						 $product_icon . '" alt="'  . $product_name . '"></abbr>' .
						 "\n\t\t\t\t" . '<p id="' . $product_id . '" hidden></p>' .
						 "\n\t\t\t" . '</div>';
				}
			?>
		
		</div>
		
		<div id="product_offers">
		</div>

	</div>
	
</main>

<?php include('footer.php'); ?>