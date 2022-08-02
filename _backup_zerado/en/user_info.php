			<div id="user">
				<?php
					$query = "SELECT user_name, user_image, days_in_game
							  FROM users u, user_profile up
							  WHERE u.user_id = '$user_id' AND up.user_id = u.user_id";
					$result = mysqli_query($conn, $query);
					$row = mysqli_fetch_row($result);
					list($user_name, $user_image, $days_in_game) = $row;
					
					//display level progress
					$query = "SELECT IFNULL(level_id, 0), up.experience, IFNULL(uxl.experience, 0),
							 (SELECT level_id FROM user_exp_levels ORDER BY level_id DESC LIMIT 1)
							  FROM user_profile up LEFT JOIN user_exp_levels uxl ON uxl.experience <= up.experience
							  WHERE up.user_id = '$user_id'
							  ORDER BY level_id DESC LIMIT 1";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($current_level, $experience, $prev_experience, $max_level) = $row;
					
					if($max_level == $current_level) {//reached max level
						$progress == 100;
						$next_experience = $prev_experience;
					}
					else {
						$query = "SELECT experience FROM user_exp_levels 
								  WHERE level_id = '$current_level' + '1' LIMIT 1";
						$result = $conn->query($query);
						$row = $result->fetch_row();
						list($next_experience) = $row;
						
						$progress = round((100 / ($next_experience - $prev_experience)) * ($experience - $prev_experience), 2);
					}
					
					if(date('n') == 12 && date('j') >= 25) {//display santa hat
						echo '<img id="santa_hat" src="../img/santa_hat.png?' . filemtime('../img/santa_hat.png') . 
							 '" alt="santa hat">';
					}
					
					
					echo "\n\t\t\t\t" . '<a id="user_name" href="user_profile?id=' . $user_id . '">' . $user_name . '</a>' .
						 "\n\t\t\t\t" . '<div id="ui_user_level">' .
						 "\n\t\t\t\t\t" . '<div id="ui_user_lvl"><p>' . $current_level . '</p></div>' .
						 "\n\t\t\t\t\t" . '<div id="ui_user_lvl_progres_bar">' .
                         "\n\t\t\t\t\t\t" . '<div id="ui_user_lvl_progres" style="width:' . $progress . '%"></div>' .
						 "\n\t\t\t\t\t\t" . '<p>' . number_format(($experience - $prev_experience), '0', '', ' ') . 
											'/' . number_format(($next_experience - $prev_experience), '0', '', ' ') . '</p>' .
                         "\n\t\t\t\t\t" . '</div>' .
						 "\n\t\t\t\t" . '</div>';
				?>
				
				<div id="user_img">
					<?php
						echo '<img src="../user_images/' . $user_image . '?' . filemtime('../user_images/' . $user_image) . 
							 '" alt="user image">';
					?>
					
				</div>
				
				<?php
					if($user_id < 1000) {
						echo "\n\t\t" . '<a href="manage_game" style="color:black; width:100%; text-align:center;' .
										' display: block;">Manage Game</a>';
					}
				?>
				
				<div id="user_info">
					<abbr title="citizenship">
						<div id="citizenship">
							<i class="far fa-id-card"></i>
							<?php
								$sql = "SELECT country_name, country_id, flag FROM country 
										WHERE country_id = (SELECT citizenship FROM user_profile 
										WHERE user_id = '$user_id')";
								$result = mysqli_query($conn, $sql);
								$row = mysqli_fetch_row($result);
								list($country_name, $citizenship, $flag) = $row;
								echo "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '" alt="' . $country_name . '">' .
									 "\n\t\t\t\t\t" . '<a href="country?country_id=' . $citizenship . '">' . $country_name . '</a>';
							?>
							
						</div>
					</abbr>
					
					<abbr title="money">
						<div id="money">
							<i class="far fa-money-bill-alt"></i>
							<?php
								$sql = "SELECT IFNULL(amount, 0), currency_abbr, flag FROM currency cu, country c LEFT JOIN user_currency uc
										ON uc.currency_id = c.currency_id AND user_id = '$user_id'
										WHERE cu.currency_id = (SELECT currency_id FROM country WHERE country_id = 
										(SELECT citizenship FROM user_profile WHERE user_id = '$user_id'))
										AND c.currency_id = cu.currency_id";
								$result = mysqli_query($conn, $sql);
								$row = mysqli_fetch_row($result);
								list($amount, $currency_abbr, $flag) = $row;
								echo "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
									 "\n\t\t\t\t\t" . '<p id="currency">' . number_format($amount, 2, ".", " ") . 
													  ' <span>' . $currency_abbr . '</span></p>';
							?>
							
						</div>
					</abbr>
					
					<abbr title="Gold">
						<div id="usr_gold">
							<?php
								$sql = "SELECT amount FROM user_product WHERE user_id = '$user_id'
										AND product_id = 1";
								$result = mysqli_query($conn, $sql);
								$row = mysqli_fetch_row($result);
								list($amount) = $row;
								echo "\n\t\t\t\t\t" . '<img id="usr_gold_img" src="../img/gold.png">' .
									 "\n\t\t\t\t\t" . '<p id="user_gold_amount">' . number_format($amount, 3, ".", " ") . 
													  ' <span>Gold</span></p>';
							?>
							
						</div>
					</abbr>
					

					<!-- <abbr title="Active days in game">
						<div id="days_in">
							<span class="fa fa-calendar"></span>
							<?php
								/* if($days_in_game == 1) {
									echo '<p>' . $days_in_game . ' <span>day in game</span></p>';
								}
								else {
									echo '<p>' . $days_in_game . ' <span>days in game</span></p>';
								} */
								
							?>
							
						</div>
					</abbr> -->

				</div>
				
				<?php
				//time until day change
				$date = correctDate(date("Y-m-d"), date("H:i:s"));
				$time = correctTime(date("H:i:s"));
				$current_time = date('Y-m-d H:i:s', strtotime("$date $time"));
				$change_time = date('Y-m-d H:i:s', strtotime("$date 04:00:00 + 1 DAY"));
				
				$date1 = date_create($current_time);
				$date2 = date_create($change_time);
				$diff = date_diff($date1,$date2);

				$hours = $diff->format("%h");
				$mins = $diff->format("%i");
				$sec = $diff->format("%s");

				$remaining_time = sprintf('%02d:%02d:%02d', $hours, $mins, $sec);
				echo "\n\t\t" . '<div id="until_day_change_div">' .
					 "\n\t\t\t" . '<p id="until_day_change_h">Day change in</p>' .
					 "\n\t\t\t" . '<p id="until_day_change">' . $remaining_time . '</p>' .
					 "\n\t\t" . '</div>';
				?>
				
				<!-- Daily missions -->
				<div id="daily_missions_div">
					<p id="daily_missions_head">Daily Missions</p>
					<?php
						//get timezone id
						$query = "SELECT hours FROM timezones WHERE timezone_id = 
								 (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
						$result = $conn->query($query);
						$row = $result->fetch_row();
						list($hours) = $row;
						$date = date('Y-m-d');	
						$time = date('04:00:00');

						
						$after_four_am = date('Y-m-d H:i:s', strtotime("$date $time -$hours hours"));
						$before_four_am = date('Y-m-d H:i:s', strtotime("$date $time + 1 days -$hours hours"));
						
						if(strtotime(date('Y-m-d H:i:s', strtotime($after_four_am))) > strtotime(date('Y-m-d H:i:s'))) {
							$after_four_am = date('Y-m-d H:i:s', strtotime("$after_four_am - 1 days"));
							$before_four_am = date('Y-m-d H:i:s', strtotime("$before_four_am - 1 days"));
						}
						else if(strtotime(date('Y-m-d H:i:s', strtotime($before_four_am))) < strtotime(date('Y-m-d H:i:s'))) {
							$after_four_am = date('Y-m-d H:i:s', strtotime("$after_four_am + 1 days"));
							$before_four_am = date('Y-m-d H:i:s', strtotime("$before_four_am + 1 days"));
						}
						
						//get daily missions
						$query = "SELECT mission_id, mission_name, mission_description, icon FROM daily_missions ORDER BY mission_id";
						$result_missions = $conn->query($query);
						while($row_missions = $result_missions->fetch_row()) {
							list($mission_id, $mission_name, $mission_description, $icon) = $row_missions;

							echo "\n\t\t\t\t" . '<img id="m_' . $mission_id . '" src="../img/' . $icon . '"></img>' .
								 "\n\t\t\t\t" . '<p hidden>' . $mission_id . '</p>';
						}
					?>
				
				</div>
				<p id="usr_setting"><a href="settings"><span class="glyphicon glyphicon-wrench"></span>Settings</a></p>
			</div>
			
			<!-- Top 10 currency a user owns -->
			<div id="users_top_currency_div">
				<p id="utcd_head">My top 10 currency</p>
					<?php
						$query = "SELECT amount, currency_abbr, flag FROM user_currency uc, currency cu, country c
								  WHERE user_id = '$user_id' AND c.currency_id = cu.currency_id
								  AND cu.currency_id = uc.currency_id AND amount > 0 ORDER BY amount DESC LIMIT 10";
						$result = $conn->query($query);
						while($row = $result->fetch_row()) {
							list($amount, $currency_abbr, $flag) = $row;
							echo "\n\t\t\t" . '<div class="utcd_currency">' .
								 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '" alt="' . $currency_abbr . '">' .
								 "\n\t\t\t\t" . '<p>' . number_format($amount, '2', '.', ' ') . ' ' . $currency_abbr . '</p>' .
								 "\n\t\t\t" . '</div>';
						}
					?>
			</div>
			
			<!-- Average price of products in the country -->
			<div id="economystat">
				<p id="econ_head"><?php echo $lang['product_price_status']; ?></p>
				<div id="prod_stat_div">
					<div id="psd_heads">
						<p id="psdh_n">Name</p>
						<p id="psdh_p">Price</p>
						<p id="psdh_s">Diff</p>
						<p id="psdh_g">G</p>
					</div>
					<?php
						$query = "SELECT product_name, app.price, IFNULL(app2.price, 0)
								  FROM average_product_price app LEFT JOIN average_product_price app2 
								  ON app2.date = DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)
								  AND app2.country_id = app.country_id AND app2.product_id = app.product_id, product_info pi
								  WHERE pi.product_id = app.product_id AND app.date = CURRENT_DATE
								  AND app.country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')";
						$result = $conn->query($query);
						while($row = $result->fetch_row()) {
							list($product_name, $price, $price_old) = $row;
							
							if(iconv_strlen($product_name) > 7) {
								$product_name = substr($product_name, 0, 7);
							}
							
							$price_diff = $price - $price_old;
							if($price_diff == 0) {
								$sign = '<i class="fa fa-minus psd_no_growth" aria-hidden="true"></i>';
								$price_diff_color = 'psd_price_diff_black';
								$font_weight = "psd_normal";
							}
							else if($price_diff > 0) {
								$sign = '<i class="fa fa-caret-up psd_grow_up" aria-hidden="true"></i>';
								$price_diff_color = 'psd_price_diff_green';
								$font_weight = "psd_bold";
							}
							else if($price_diff < 0) {
								$sign = '<i class="fa fa-caret-down psd_grow_down" aria-hidden="true"></i>';
								$price_diff_color = 'psd_price_diff_red';
								$font_weight = "psd_bold";
							}
							echo "\n\t\t\t\t" . '<div class="psd_product_info">' .
								 "\n\t\t\t\t\t" . '<p class="' . $font_weight . '">' . $product_name . '</p>' .
								 "\n\t\t\t\t\t" . '<p class="psd_price ' . $font_weight . '">' . number_format($price, 2, ".", " ") . '</p>' .
								 "\n\t\t\t\t\t" . '<p class="psd_price_diff ' . $price_diff_color . ' ' . $font_weight .
												  '">' . number_format($price_diff, 2, ".", " ") . '</p>' .
								 "\n\t\t\t\t\t" . '<p>' . $sign . '</p>' .
								 "\n\t\t\t\t" . '</div>';
						}
					?>
					
				</div>
			</div>