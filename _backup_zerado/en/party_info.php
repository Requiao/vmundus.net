<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php
			$party_id = htmlentities(stripslashes(trim($_GET['party_id'])), ENT_QUOTES);
			
			if(!empty($party_id)) {
				$query = "SELECT party_id, leader FROM political_parties WHERE party_id = '$party_id'";
			}
			else {
				$query = "SELECT party_id, leader FROM political_parties WHERE party_id = 
						 (SELECT party_id FROM party_members WHERE user_id = '$user_id')";
			}
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($party_id, $leader) = $row;
			
			if($leader != $user_id) {//create party
				$query = "SELECT * FROM party_members WHERE user_id = '$user_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$without_party = true;
					echo "\n\t\t" . '<p class="button green" id="create_party">Create Party</p>';
				}
			}
			
			if(!empty($party_id)) {
				$query = "SELECT pp.party_id, party_name, description, leader, flag, user_name, COUNT(pm.party_id), 
						 (SELECT COUNT(*) FROM party_applications WHERE party_id = '$party_id')
						  FROM political_parties pp, users u, party_members pm
						  WHERE pp.party_id = '$party_id' AND u.user_id = pp.leader AND pm.party_id = pp.party_id 
						  GROUP BY pm.party_id";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($party_id, $party_name, $description, $leader, $flag, $leader_name, $members, $applications) = $row;
					
				echo "\n\t\t" . '<div id="party_div">' .
					 "\n\t\t\t" . '<p id="party_id" hidden>' . $party_id . '</p>' .
					 "\n\t\t\t" . '<img id="party_flag" src="../party_flags/' . $flag . '">' .
					 "\n\t\t\t" . '<p id="party_name">' . $party_name . '</p>' .
					 "\n\t\t\t" . '<a id="party_leader" href="user_profile?id=' . $leader . '" target="_blank">Party leader: <i>' . $leader_name . 
								  '</i></a>' .
					 "\n\t\t\t" . '<p id="party_desc">' . $description . '</p>';
						 
				if($leader == $user_id) {	//founder/leader of the party. has admin rights
					//party applications
					echo "\n\t\t\t" . '<p id="party_app_head">Party applications: </p>' .
						 "\n\t\t\t" . '<p class="button blue" id="view_applications">View(' . $applications . ')</p>';
					
					//congress elections
					echo "\n\t\t\t" . '<p id="elections_head">Congress elections info: </p>' .
						 "\n\t\t\t" . '<p class="button blue" id="view_elections_info">Details</p>';
						 
					$query = "SELECT election_id FROM election_info WHERE ended = 1 AND can_participate = 1 AND type = 3 
							  AND country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$row = $result->fetch_row();
						list($election_id) = $row;
						//check if already joined
						$query = "SELECT * FROM congress_elections WHERE election_id = '$election_id' AND party_id = '$party_id'";
						$result = $conn->query($query);
						if($result->num_rows == 1) {
							echo "\n\t\t\t" . '<p class="button red" id="stop_join">Stop Join</p>';
						}
						else {
							echo "\n\t\t\t" . '<p class="button blue" id="join_elections">Join Elections</p>';
						}
					}

					//edit party
					echo "\n\t\t\t" . '<p class="button blue" id="edit_party">Edit</p>';
					
					//dissolve
					echo "\n\t\t\t" . '<p class="button red" id="dissolve_party">Dissolve</p>';
				}
				else {//member
					$query = "SELECT * FROM party_members WHERE user_id = '$user_id' AND party_id = '$party_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						//leave
						echo "\n\t\t\t" . '<p class="button red" id="leave_party">Leave</p>';	
					}
				}
				if($without_party) {
					//is applied to join this party
					$query = "SELECT * FROM party_applications WHERE user_id = '$user_id'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($apllied_for_party_id) = $row;
					
					if($apllied_for_party_id == $party_id) {
						echo "\n\t\t\t" . '<p class="button red cancel_application" id="' . $party_id . '">Cancel</p>';
					}
					else {
						echo "\n\t\t\t" . '<p class="button green join_party" id="' . $party_id . '">Join</p>';
					}
				}
				echo "\n\t\t" . '<div>';
				
				//select members
				$query = "SELECT user_name, u.user_id, user_image FROM party_members pm, users u, user_profile up 
						  WHERE u.user_id = pm.user_id AND up.user_id = u.user_id AND party_id = '$party_id'";
				$result = $conn->query($query);
					
				echo "\n\t\t" . '<div id="members_div">' .
					 "\n\t\t\t" . '<p id="party_members_head">Party members(' . $members . ')</p>';
			
				while($row = $result->fetch_row()) {
					list($member_name, $member_id, $member_image) = $row;
					
					echo "\n\t\t\t" . '<div class="member_div">' .
						 "\n\t\t\t\t" . '<img class="member_img" src="../user_images/' . $member_image . '">' .
						 "\n\t\t\t\t" . '<a class="member_name" href="user_profile?id=' . $member_id . '" target="_blank">' . $member_name . '</a>';
					 
					if($user_id == $leader && $member_id != $user_id) {//founder/leader of the party. has admin rights
						//kick
						echo "\n\t\t\t" . '<p class="button red kick_member" id="' . $member_id . '">Drive out</p>';
						
					}	 
						 
					echo "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t" . '</div>';
			}
		?>
	
	</div>
</main>

<?php include('footer.php'); ?>