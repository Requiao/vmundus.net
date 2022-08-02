<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Top countries by productivity</p>
		<?php
		echo "\n\t\t" . '<div id="stat_div">';
			$query = "SELECT flag, country_name, SUM(productivity * price)/(SELECT price FROM product_price WHERE product_id = 1) 
					  AS prod_in_gold, r.country_id 
					  FROM region_product_productivity rpp, product_price pp, regions r, country c
					  WHERE rpp.region_id = r.region_id AND pp.product_id = rpp.product_id AND c.country_id = r.country_id
					  AND DATE_ADD(TIMESTAMP(date, time), INTERVAL 10 DAY) >= NOW()
					  GROUP BY r.country_id ORDER BY prod_in_gold DESC";
			$result = $conn->query($query);
			$position = 1;
			while($row = $result->fetch_row()) {
				list($flag, $country_name, $productivity, $country_id) = $row;
				
				echo "\n\t\t\t" . '<div class="sd_stat_details">' .
					 "\n\t\t\t\t" . '<p class="sd_country_position">' . $position++ . '</p>' .
					 "\n\t\t\t\t" . '<img class="sd_flag" src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t" . '<a class="sd_country_name" href="country?country_id=' . $country_id . '">' . $country_name . '</a>' .
					 "\n\t\t\t\t" . '<p class="sd_amount">' . number_format($productivity, 2, ".", " ") . 
									  '<span> Gold per 10 days</span></p>' .
					 "\n\t\t\t\t" . '<img class="sd_gold" src="../img/gold.png">' .
					 "\n\t\t\t" . '</div>';
			}
			
		echo "\n\t\t" . '</div>';
		?>

	</div>
</main>

<?php include('footer.php'); ?>