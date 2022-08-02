<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php
			$exists = TRUE;
			$union_id = htmlentities(stripslashes(trim($_GET['union_id'])), ENT_QUOTES);
			if(!empty($union_id) && is_numeric($union_id)) {
				$query = "SELECT union_id, leader FROM unions WHERE union_id = '$union_id'";
			}
			else {
				$query = "SELECT union_id, leader FROM unions WHERE union_id = 
						 (SELECT union_id FROM country_unions WHERE country_id = 
						 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id'))";
				$union_id = TRUE;
			}
			$result = $conn->query($query);
			if($result->num_rows == 0) {
				$exists = FALSE;
			}
			
			if(!empty($union_id) && $exists) {
				$row = $result->fetch_row();
				list($union_id, $leader) = $row;
				$query = "SELECT u.union_id, union_name, abbreviation, description, leader, union_flag, color, user_name, COUNT(cu.union_id)
						  FROM unions u, country_unions cu, users WHERE u.union_id = cu.union_id AND leader = user_id 
						  AND u.union_id = '$union_id' GROUP BY union_id";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($union_id, $union_name, $abbreviation, $description, $leader, $union_flag, $color, $leader_name, $members_count) = $row;
					
				echo "\n\t\t" . '<div id="union_div">' .
					 "\n\t\t\t" . '<p id="union_id" hidden>' . $union_id . '</p>' .
					 "\n\t\t\t" . '<img id="union_flag" src="../union_flags/' . $union_flag . '">' .
					 "\n\t\t\t" . '<p id="union_abbr">' . $abbreviation . '</p>' .
					 "\n\t\t\t" . '<p id="union_name">' . $union_name . '</p>' .
					 "\n\t\t\t" . '<p id="union_color" style="background-color:' . $color . '">Union Color</p>' .
					 "\n\t\t\t" . '<a id="union_leader" href="user_profile?id=' . $leader . '" target="_blank">Union leader: <i>' . $leader_name . 
								  '</i></a>' .
					 "\n\t\t\t" . '<p id="union_desc">' . $description . '</p>';
						 
				if($leader == $user_id) {
					//edit union
					echo "\n\t\t\t" . '<p class="button blue" id="edit_union">Edit</p>';
				}
				echo "\n\t\t" . '<div>';
				
				//display members
				$query = "SELECT country_name, c.country_id, flag, is_founder FROM country c, country_unions cu 
						  WHERE c.country_id IN (SELECT country_id FROM country_unions WHERE union_id = '$union_id') 
						  AND c.country_id = cu.country_id";
				$result = $conn->query($query);
					
				echo "\n\t\t" . '<div id="members_div">' .
					 "\n\t\t\t" . '<p id="union_members_head">Union members(' . $members_count . ')</p>';
			
				while($row = $result->fetch_row()) {
					list($member_name, $member_id, $member_flag, $is_founder) = $row;
					
					if($is_founder == 0) {
						$founder_members = 'Member';
					}
					else if($is_founder == 1) {
						$founder_members = 'Founder';
					}
					
					echo "\n\t\t\t" . '<div class="member_div">' .
						 "\n\t\t\t\t" . '<a class="member_name" href="country?country_id=' . $member_id . '" target="_blank">' . 
										 $member_name . '</a>' . 
						 "\n\t\t\t\t" . '<p class="is_founder">' . $founder_members .  '</p>' .
						 "\n\t\t\t\t" . '<img class="member_img" src="../country_flags/' . $member_flag . '">' .
						 "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
			}
			else if(!$exists && !empty($union_id)) {
				echo "\n\t\t" . '<p>Union doesn\'t exists</p>';
			}
			else {
				echo "\n\t\t" . '<p>Your country is not a member of any union</p>';
			}
		?>
	
	</div>
</main>

<?php include('footer.php'); ?>