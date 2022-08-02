<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="market_head">Game Store</p>

		<?php
			//REWARD FOR USER LEVEL / CLONE PURCHASE BEFORE RESET
			$query = "SELECT available, collected, date_collected, time_collected 
					  FROM user_gold_rewards WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($available, $collected, $last_date_collected, $last_time_collected) = $row;

				echo'<div id="user_rewards_div">' .
					'<p id="urd_collected_lbl">Collected</p>' .
					'<p id="urd_available_lbl">Available</p>' .
					//'<p id="urd_can_collect_lbl">Can Collect</p>' .
					'<img id="urd_prod_icon" src="../product_icons/gold.png" alt="Gold">' .
					'<p id="urd_collected">' . number_format($collected, '2', '.', ' ') . '</p>' .
					'<p id="urd_available">' . number_format($available, '2', '.', ' ') . '</p>' .
					//'<p id="urd_can_collect">' . number_format($PER_DAY_COLLECT_LIMIT, '2', '.', ' ') . '</p>' .
					'<p id="urd_collect" class="button blue">Collect</p>' .
					'</div>';
			}
		?>



		<?php
			/* OLD STORE
			//check if bought something and not received
			$query = "SELECT p.purchase_id, item_name, quantity_left FROM purchases p, workers_purchase_details wpd
					  WHERE p.purchase_id = wpd.purchase_id AND quantity_left > 0 AND user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows > 0) {
				echo "\n\t\t\t" . '<div id="left_over_purchases">';
				while($row = $result->fetch_row()) {
					list($purchase_id, $item_name, $quantity_left) = $row;
					echo "\n\t\t\t\t" . '<p class="leftover_info">You still have ' . $quantity_left . 
										' persons from ' . $item_name . '</p>';
					echo "\n\t\t\t\t" . '<p class="collect_leftover" id="' . $purchase_id . '">Get Now</p>';
				}
				echo "\n\t\t\t" . '</div>';
			}
			*/
		?>
		
		
		<!--
		<div class="purchase_item">
			<p class="pi_head">Small Baby Boom Package</p>
			<img src="../img/small_worker_package.png">
			<ul class="pi_details">
				<li>Workers: 2</li>
				<li>Years: 18</li>
				<li>Skill: 3</li>
				<li>Price: <span>4.98 EUR</span></li>
			</ul>
			<form class="pi_form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="Y3E8LL3TW5N5C">
				<input type="hidden" name="custom" value="<?php //echo $user_id ?>">
				<input type="hidden" name="item_name" value="Small Baby Boom Package">
				<input type="hidden" name="item_number" value="100">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
		
		<div class="purchase_item">
			<p class="pi_head">Regular Baby Boom Package</p>
			<img src="../img/regular_worker_package.png">
			<ul class="pi_details">
				<li>Workers: 3</li>
				<li>Years: 18</li>
				<li>Skill: 5</li>
				<li>Price: <span>6.98 EUR</span></li>
			</ul>
		<form class="pi_form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="QVJEKEQ9NRUYQ">
			<input type="hidden" name="custom" value="<?php //echo $user_id ?>">
			<input type="hidden" name="item_name" value="Regular Baby Boom Package">
			<input type="hidden" name="item_number" value="200">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		</div>
		
		<div class="purchase_item">
			<p class="pi_head">Big Baby Boom Package</p>
			<img src="../img/big_worker_package.png">
			<ul class="pi_details">
				<li>Workers: 5</li>
				<li>Years: 18</li>
				<li>Skill: 7</li>
				<li>Price: <span>9.98 EUR</span></li>
			</ul>
		<form class="pi_form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="EQG47M8ZRT28G">
			<input type="hidden" name="custom" value="<?php //echo $user_id ?>">
			<input type="hidden" name="item_name" value="Big Baby Boom Package">
			<input type="hidden" name="item_number" value="300">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		</div>
		<p id="m_note">*If there is not enough room in the houses for all people from the package that you will buy, 
			then you will be able to get the rest later after making room, by returning back to this page 
			and clicking on the Get Now button.</p>
		-->
		
	</div>
</main>

<?php include('footer.php'); ?>