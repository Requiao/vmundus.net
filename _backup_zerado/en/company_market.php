<?php 
	include('head.php'); 
	include('../php_functions/cut_long_name.php');//cutLongName($string, $max_length);
?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head">Company Market</p>
		<div id="my_corps_div">
			<?php
				$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($registration_country) = $row;
				
				if(!empty($registration_country)) {
					$query = "SELECT u.user_id, user_name, user_image, com.company_id, company_name, c.country_id, country_name, 
							  region_name, r.region_id, product_ware, resource_ware, name, workers, price, building_icon,
							  currency_abbr
							  FROM company_market cm, companies com, user_building ub, building_info bi, regions r, 
							  country c, users u, user_profile up, currency cur
							  WHERE com.company_id = cm.company_id AND ub.company_id = cm.company_id AND ub.user_id = u.user_id
							  AND com.building_id = bi.building_id AND r.region_id = location AND c.country_id = r.country_id
							  AND c.country_id = '$registration_country' AND up.user_id = ub.user_id AND c.currency_id = cur.currency_id
							  ORDER BY price ASC";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($seller_id, $seller_name, $seller_image, $company_id, $company_name, $country_id, $country_name,  
						     $region_name, $region_id, $product_ware, $resource_ware, $building_name, $workers, $price, $building_icon,
							 $currency_abbr) = $row;
						
						$seller_name = cutLongName($seller_name, 10);
						
						echo "\n\t\t\t" . '<div class="comp_info_div" id="co_' . $company_id . '">' .
							 "\n\t\t\t\t" . '<a href="user_profile?id=' . $seller_id . 
											'" class="cid_seller_name">' . $seller_name . '</a>' .
							 "\n\t\t\t\t" . '<img src="../user_images/' . $seller_image . '" class="sid_seller_img">' .
							 "\n\t\t\t\t" . '<p class="sid_company_name">' . $company_name . '</p>' .
							 "\n\t\t\t\t" . '<img src="../building_icons/' . $building_icon . '" class="sid_company_img">' .
							 "\n\t\t\t\t" . '<div class="cid_fp">' .
							 "\n\t\t\t\t\t" . '<p class="sid_building_name_head">Building</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_building_name">' . $building_name . '</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_region_head">Region</p>' .
							 "\n\t\t\t\t\t" . '<a class="sid_region" href="region_info?region_id=' . $region_id . 
											  '">' . $region_name . '</a>' .
							 "\n\t\t\t\t\t" . '<p class="sid_country_head">Country</p>' .
							 "\n\t\t\t\t\t" . '<a class="sid_country" href="country?country_id=' . $country_id . 
											  '">' . $country_name . '</a>' .
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t\t" . '<div class="cid_sp">' .
							 "\n\t\t\t\t\t" . '<p class="sid_prod_ware_head">Product Warehouse:</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_prod_ware">' . $product_ware . '</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_rec_ware_head">Resource Warehouse:</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_rec_ware">' . $resource_ware . '</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_level_head">Level:</p>' .
							 "\n\t\t\t\t\t" . '<p class="sid_level">' . $workers . '</p>' .
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t" . '<p class="button green buy">Buy</p>' .
							 "\n\t\t\t\t" . '<p hidden>' . $company_id . '</p>' .
							 "\n\t\t\t\t" . '<p class="sid_price">' . number_format($price, '0', '', ' ') . ' ' . $currency_abbr . '</p>' .
							 "\n\t\t\t" . '</div>';
					}
				}
			?>
			
		</div>
	</div>
</main>
	
<?php include('footer.php'); ?> 