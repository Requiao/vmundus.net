<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php
			//is user applied to join a party
			$query = "SELECT * FROM party_applications WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($apllied_for_party_id) = $row;
			}
	
			//view other parties
			$query = "SELECT party_id, party_name, description, flag, user_name, leader 
					  FROM political_parties pp, users u WHERE leader = user_id
					  AND user_id IN (SELECT user_id FROM user_profile WHERE citizenship = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id'))";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($party_id, $party_name, $description, $flag, $leader_name, $leader) = $row;
				echo "\n\t\t" . '<div class="party_list_div">' .
					 "\n\t\t\t" . '<img class="party_flag" src="../party_flags/' . $flag . '">' .
					 "\n\t\t\t" . '<p class="party_name">' . $party_name . '</p>' .
					 "\n\t\t\t" . '<a class="party_leader" href="profile?id=' . $leader . '" target="_blank">Party leader: <i>' . $leader_name . 
								  '</i></a>' .
					 "\n\t\t\t" . '<p class="party_desc">' . $description . '</p>' .
					 "\n\t\t\t" . '<a class="button blue details" href="party_info?party_id=' . $party_id . '" target="_blank">Details</a>';
				if($apllied_for_party_id == $party_id) {
					echo "\n\t\t\t" . '<p class="button red cancel_application" id="' . $party_id . '">Cancel</p>';
				}
				else {
					echo "\n\t\t\t" . '<p class="button green join_party" id="' . $party_id . '">Join</p>';
				}
				echo "\n\t\t" . '</div>';
			}
		?>
	
	</div>
</main>

<?php include('footer.php'); ?>