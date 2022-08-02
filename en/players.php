<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="page_head">Players</p>
		<div class="search_par_div">
			<p id="spd_days_in_game">Days in game:</p>
			<input id="from_days" type="text" placeholder="from" maxlength="5">
			<input id="to_days" type="text" placeholder="to" maxlength="5">
		</div>
		<?php	
			//display country list
			$query = "SELECT country_name, flag, country_id FROM country WHERE country_id = (SELECT citizenship FROM user_profile
					  WHERE user_id = '$user_id')";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($country, $flag, $user_country_id) = $row;
			
			echo "\n\t\t" . '<div id="country_list">' .
				 "\n\t\t\t" . '<div id="country">' . 
				 "\n\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
				 "\n\t\t\t\t" . '<p>' . $country . '</p>' . 
				 "\n\t\t\t" . '</div>' . 
				 "\n\t\t\t" . '<p id="get_country_id" hidden>' . $user_country_id . '</p>' .  
				 "\n\t\t\t" . '<span class="glyphicon glyphicon-menu-down"></span>' . 
				 "\n\t\t" . '</div>' .
				 "\n\t\t\t" . '<div id="countries_div">';
			
			//null value
			echo "\n\t\t\t" . '<div class="country" id="0">' . 
				 "\n\t\t\t\t" . '<p>World</p>' . 
				 "\n\t\t\t" . '</div>';
			
			$query = "SELECT country_name, flag, country_id FROM country ORDER BY country_name";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($country, $flag, $country_id) = $row;
				echo "\n\t\t\t\t" . '<div class="country" id="' . $country_id . '">' . 
					 "\n\t\t\t\t\t" . '<img src="../country_flags/' . $flag . '">' .
					 "\n\t\t\t\t\t" . '<p>' . $country . '</p>' . 
					 "\n\t\t\t\t" . '</div>';
			}
			echo "</div>";
		?>
		<div class="search_par_div">
			<p id="spd_plr_name">Player Name:</p>
			<input id="player_name" type="text" placeholder="player name" maxlength="15">
		</div>
		
		<p id="find_players" class="button blue">Find</p>
		
		<div id="player_list">
		</div>
	</div>
	
</main>

<?php include('footer.php'); ?>