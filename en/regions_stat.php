<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Top countries with the most regions.</p>
		<?php
		echo "\n\t\t" . '<div id="stat_div">';
				$query = "SELECT flag, country_name, COUNT(r.country_id) AS total_regions 
						  FROM country c, regions r
						  WHERE c.country_id = r.country_id
						  GROUP BY r.country_id ORDER BY total_regions DESC LIMIT 20";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($flag, $country_name, $total_regions) = $row;
					
					echo "\n\t\t\t\t" . '<div class="sd_stat_details">' .
						 "\n\t\t\t\t\t" . '<img class="sd_flag" src="../country_flags/' . $flag . '">' .
						 "\n\t\t\t\t\t" . '<p class="sd_country_name">' . $country_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="sd_amount">' . $total_regions . ' regions </p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>'; 
			?>  	

	</div>
</main>

<?php include('footer.php'); ?>