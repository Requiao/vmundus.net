	<div id="col4">
		<div id="tutorials_div">
			<p id="td_heading">Tutorials</p>
			<p class="td_tutorial">1. Create a company</p>
			<p hidden>1</p>
			<p class="td_tutorial">2. Make a job offer</p>
			<p hidden>2</p>
			<p class="td_tutorial">3. Get a job</p>
			<p hidden>3</p>
			<p class="td_tutorial">4. Work</p>
			<p hidden>4</p>
			
		</div>
	
		<?php
			//check if requesting friedship
			$query = "SELECT u.user_id, user_name, user_image FROM users u, user_profile up
					  WHERE up.user_id = u.user_id AND u.user_id IN 
					  (SELECT user_id FROM request_friendship WHERE friend_id = '$user_id') LIMIT 1";
			$result = $conn->query($query);
			if($result->num_rows > 0) {
				$row = $result->fetch_row();
				list($friend_id, $friend_name, $friend_image) = $row;
				if(iconv_strlen($friend_name) > 12) {
					$friend_name = substr($friend_name, 0, 12) . "...";
				}
				echo "\n\t\t" . '<div id="addfriend">' .
					 "\n\t\t\t" . '<p id="a_fr">Friend Request</p>' .
					 "\n\t\t\t" . '<p id="a_reply"></p>' .
					 "\n\t\t\t" . '<img id="a_img" src="../user_images/' . $friend_image . '">' .
					 "\n\t\t\t" . '<a id="a_name" href="user_profile?id=' . $friend_id . '">' . $friend_name . '</a>' .
					 "\n\t\t\t" . '<div id="a_add_dec_div">' .
					 "\n\t\t\t\t" . '<p id="a_decline_friend" class="fa fa-times"></p>' .
					 "\n\t\t\t\t" . '<p id="a_add_friend" class="fa fa-check"></p>' .
					 "\n\t\t\t\t" . '<p id="new_friend_id" hidden>' . $friend_id . '</p>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
			
			/* gold market bonus */
			$query = "SELECT MAX(bonus) FROM gold_market_offers";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($bonus) = $row;
			if($bonus > 0) {
				echo'<div id="gold_market_div">' .
					'<img src="../img/gold_market_sale.png" alt="gold market sales">' .
					'<a id="gmd_market_head" href="gold_market">Gold Market Sale!</a>' .
					'</div>';
			}
		?>
		
		<div id="social_media_links">
			<p id="sml_heading">vMunuds on:</p>
			 <div id="social_div">
			<div class="iamgem_rs">  
			<a href="https://discord.gg/QvP4fNT"><img src="https://vmundus.online/img/discord.png" ></a></div>
			<div class="iamgem_fb">
			<a href="https://www.facebook.com/vmundus"><img src="https://vmundus.online/img/facebook.png"></a></div>
			<div class="iamgem_in">
			<a href="https://www.instagram.com/vmundus.online"><img src="https://vmundus.online/img/instagram.png"></a></div>
			<div class="iamgem_re">
			<a href="https://www.reddit.com/r/vMundus/"><img src="https://vmundus.online/img/reddit.png" ></a></div>
			  </div>
			
			</a>
		</div>
		
		<?php
			//online users
			echo "\n\t\t" . '<div id="online_users_div">' .
				 "\n\t\t\t" . '<p id="oud_heading">Online users</p>';
			$query = "SELECT u.user_id, user_name, user_image FROM user_profile up, users u WHERE up.user_id = u.user_id AND
					  DATE_ADD(TIMESTAMP(last_active, last_active_time), INTERVAL 2 MINUTE) >= NOW() LIMIT 20";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($profile_id, $profile_name, $profile_image) = $row;
				echo "\n\t\t\t" . '<a class="oud_users" href="user_profile?id=' . $profile_id . '">' .
					 "\n\t\t\t\t" . '<img src="../user_images/' . $profile_image . '?' . filemtime('../user_images/' . $profile_image) . 
									'" alt="user image">' .
					 "\n\t\t\t\t" . '<p>' . $profile_name . '</p>' .
					 "\n\t\t\t\t" . '<i class="fa fa-circle" aria-hidden="true"></i>' .
					 "\n\t\t\t" . '</a>';
			}
			
			echo "\n\t\t" . '</div>';
		?>
		
		<div id="events" hidden>
			<p>Upcomming Events</p>
		</div>
	</div>