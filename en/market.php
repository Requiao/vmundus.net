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
					'<p id="urd_can_collect_lbl">Can Collect</p>' .
					'<img id="urd_prod_icon" src="../product_icons/gold.png" alt="Gold">' .
					'<p id="urd_collected">' . number_format($collected, '2', '.', ' ') . '</p>' .
					'<p id="urd_available">' . number_format($available, '2', '.', ' ') . '</p>' .
					'<p id="urd_can_collect">' . number_format($PER_DAY_COLLECT_LIMIT, '2', '.', ' ') . '</p>' .
					'<p id="urd_collect" class="button blue">Collect</p>' .
					'</div>';
			}
		?>



		<?php
			
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
			
		?>
		
		
		
		<div class="purchase_item">
			<p class="pi_head">Small Baby Boom Package</p>
			<img src="../img/small_worker_package.png">
			<ul class="pi_details">
				<li>Workers: 2</li>
				<li>Years: 18</li>
				<li>Skill: 3</li>
				<li>Price: <span>4.98 EUR</span></li>
			</ul>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
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
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
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
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>

		</div>
		<p id="m_note">*If there is not enough room in the houses for all people from the package that you will buy, 
			then you will be able to get the rest later after making room, by returning back to this page 
			and clicking on the Get Now button.</p>
		
		<div class="purchase_item">	
			<p class="pi_head">Buy Golds</p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<table>
<tr><td><input type="hidden" name="on0" value="Golds">Golds</td></tr><tr><td><select name="os0">
	<option value="5 Golds -">5 Golds - €5,00 EUR</option>
	<option value="15 Golds -">15 Golds - €11,00 EUR</option>
	<option value="30 Golds -">30 Golds - €20,00 EUR</option>
	<option value="50 Golds -">50 Golds - €30,00 EUR</option>
</select> </td></tr>
</table>

<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>

</div>

<div class="purchase_item">	
			<p class="pi_head">Donate</p>
			<ul class="pi_details">
				<li>Donate to vMundus</li>
			 
			</ul>
<form action="https://www.paypal.com/donate" method="post" target="_top">
<input type="hidden" name="business" value="R8436SJW3W7ZQ" />
<input type="hidden" name="currency_code" value="USD" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
<img alt="" border="0" src="https://www.paypal.com/en_BR/i/scr/pixel.gif" width="1" height="1" />
</form>
	</div>	
		
	</div>
</main>

<?php include('footer.php'); ?>

