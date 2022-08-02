<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<?php
			//get user access
			$query = "SELECT name, al.access_id, description FROM users_special_access usa, access_levels al
					  WHERE user_id = '$user_id' AND usa.access_id = al.access_id";
			$result = $conn->query($query);
			if($result->num_rows != 0) {
				/*$user_access = array(1=>false, //give_access
									 2=>false, //delete_users
									 3=>false, //ban_user
									 4=>false, //remove_ban
									 5=>false, //change_user_name
									 6=>false, //world_chats
									 7=>false, //country_chats
									 8=>false, //world_messages
									 9=>false, //world_posts
									 10=>false, //country_posts
									 11=>false, //find_multies
									 12=>false, //game updates
									 13=>false, //vpn users
									 14=>false, //product market history
									 15=>false //worked with same IP
									);*/
				$user_access = array();

				while($row = $result->fetch_row()) {
					list($access_name, $access_id, $description) = $row;

					array_push($user_access, array("access_id"=>$access_id, "access_name"=>$access_name, 
						"description"=>$description, "have_access"=>true));
				}

				echo "\n\t\t" . '<div id="mg_menu">';
				foreach($user_access as $item) {
					echo'<p id="mm_' . $item['access_name'] . '">' . $item['access_name'] . '</p>';
				}
				echo "\n\t\t" . '</div>';		
				
				foreach($user_access as $item) {
					if($item['access_name'] == 'ban_user') {//ban_user
						echo "\n\t\t" . '<div id="ban_user_div">' .
							"\n\t\t\t" . '<div id="bud_search_div">' .
							"\n\t\t\t\t" . '<input type="text" id="bud_user_id">' .
							"\n\t\t\t\t" . '<p id="bud_find_user" class="button">Find User</p>' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div id="bud_profile_details_div">' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t" . '</div>';
					}
					
					if($item['access_name'] == 'find_multies') {//find multies
						echo "\n\t\t" . '<div id="find_multies_div">' .
							"\n\t\t\t" . '<div id="fmd_search_div">' .
							"\n\t\t\t\t" . '<p id="fmd_last_login_lbl">Last Active Days</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="fmd_last_login_in" type="text" maxlength="5" value="5">' .
							"\n\t\t\t\t" . '<p id="fmd_user_ip_lbl">Users per IP</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="fmd_user_ip_in" type="text" maxlength="2" value="3">' .
							"\n\t\t\t\t" . '<p id="fmd_ip_lbl">IP</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="fmd_ip_in" type="text">' .
							"\n\t\t\t\t" . '<p id="fmd_profile_id_lbl">Profile ID</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="fmd_profile_id_in" type="text" maxlength="7">' .
							"\n\t\t\t\t" . '<p id="fmd_search" class="button">Search</p>' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div id="fmd_result">' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t" . '</div>';
					}
				
					if($item['access_name'] == 'game_updates') {//game updates
						echo "\n\t\t" . '<div id="game_updates_div">' .
							"\n\t\t" . '<p id="gud_reply"></p>' .
							"\n\t\t\t" . '<div class="gud_divs">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Heading:</p>' .
							"\n\t\t\t\t" . '<input id="nguh_input" type="text" maxlength="50">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Descriptions:</p>' .
							"\n\t\t\t\t" . '<input class="ngu_desc_input" type="text" maxlength="500">' .
							"\n\t\t\t\t" . '<p id="ngu_add_more_desc" class="button">Add more</p>' .
							"\n\t\t\t\t" . '<p id="ngu_add" class="button">Insert</p>' .
							
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div class="gud_divs">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Update ID:</p>' .
							"\n\t\t\t\t" . '<input id="eguh_id_input" type="text" maxlength="23">' .
							"\n\t\t\t\t" . '<p class="gud_lbl">New Heading:</p>' .
							"\n\t\t\t\t" . '<input id="eguh_input" type="text" maxlength="50">' .
							"\n\t\t\t\t" . '<p id="egu_heading_edit" class="button">Update</p>' .
							
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div class="gud_divs">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Description ID:</p>' .
							"\n\t\t\t\t" . '<input id="egu_desc_id_input" type="text" maxlength="23">' .
							"\n\t\t\t\t" . '<p class="gud_lbl">New Description:</p>' .
							"\n\t\t\t\t" . '<input id="egu_desc_input" type="text" maxlength="500">' .
							"\n\t\t\t\t" . '<p id="egu_desc_edit" class="button">Update</p>' .
							
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div class="gud_divs">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Add Description to update ID:</p>' .
							"\n\t\t\t\t" . '<input id="egu_add_desc_id_input" type="text" maxlength="23">' .
							"\n\t\t\t\t" . '<p class="gud_lbl">Description:</p>' .
							"\n\t\t\t\t" . '<input id="egu_add_desc_input" type="text" maxlength="500">' .
							"\n\t\t\t\t" . '<p id="egu_add_desc_edit" class="button">Add</p>' .
							
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div class="gud_divs">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Delete Description ID:</p>' .
							"\n\t\t\t\t" . '<input id="egu_desc_del_input" type="text" maxlength="23">' .
							"\n\t\t\t\t" . '<p id="egu_del_desc" class="button">Delete</p>' .
							
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div class="gud_divs">' .
							
							"\n\t\t\t\t" . '<p class="gud_lbl">Delete Update:</p>' .
							"\n\t\t\t\t" . '<input id="egu_upd_del_input" type="text" maxlength="23">' .
							"\n\t\t\t\t" . '<p id="egu_del_upd_edit" class="button">Delete</p>' .
							
							"\n\t\t\t" . '</div>' .
							"\n\t\t" . '</div>';
					}

					if($item['access_name'] == 'vpn_users') {//vpn users
						echo "\n\t\t" . '<div id="vpn_users_div">' .
							"\n\t\t\t" . '<p id="vpn_error"></p>' .
							"\n\t\t\t" . '<div id="vpn_search_div">' .
							"\n\t\t\t\t" . '<p id="vpn_last_login_lbl">Last Active Days</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="vpn_last_login_input" type="text" maxlength="5" value="3">' .
							"\n\t\t\t\t" . '<p id="vpn_days_in_lbl">Days in game</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="vpn_days_in_input" type="text" maxlength="5" value="3">' .
							"\n\t\t\t\t" . '<p id="vpn_ip_lbl">Not Stable IPs</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="vpn_ips_input" type="text" value="3">' .
							"\n\t\t\t\t" . '<p id="vpn_profile_id_lbl">Profile ID</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="vpn_profile_id_in" type="text" maxlength="7">' .
							"\n\t\t\t\t" . '<p id="vpn_search" class="button">Search</p>' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div id="vpn_result">' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t" . '</div>';
					
					}
				
					if($item['access_name'] == 'product_market_hist') {//product market history
						echo "\n\t\t" . '<div id="product_market_hist_div">' .
							"\n\t\t\t" . '<p id="pmh_error"></p>' .
							"\n\t\t\t" . '<div id="pmh_search_div">' .
							"\n\t\t\t\t" . '<p id="pmh_profile_id_lbl">Profile ID: </p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="pmh_profile_id_input" type="text" maxlength="7">' .
							"\n\t\t\t\t" . '<p id="pmh_bought_from_lbl">Bought from ID: </p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="pmh_bought_from_input" type="text" maxlength="7">' .
							"\n\t\t\t\t" . '<p id="pmh_percent_lbl">Difference in %</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="pmh_percent_input" type="text" value="30">' .
							"\n\t\t\t\t" . '<p id="pmh_country_id_lbl">For Country ID</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="pmh_country_id_input" type="text" maxlength="3">' .
							"\n\t\t\t\t" . '<p id="pmh_days_lbl">History Days</p>' .
							"\n\t\t\t\t" . '<input class="mg_input" id="pmh_days_input" type="text" value="3">' .
							"\n\t\t\t\t" . '<p id="pmh_search" class="button">Search</p>' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t\t" . '<div id="pmh_result">' .
							"\n\t\t\t" . '</div>' .
							"\n\t\t" . '</div>';
					}
				
					if($item['access_name'] == 'country_requests') {//Manage country requests.
						echo'<div id="country_requests_div"></div>';
					}
				}
			}
			else {
				echo "\n\t\t" . '<p>You are not allowed to view this page</p>';
			}
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>