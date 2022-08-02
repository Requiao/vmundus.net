<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head">Corporations</p>
		<div id="page_menu">
			<p id="my_corporations">My Corporations</p>
			<p id="corp_invitations">Invitations</p>
		</div>
		
		<div id="my_corps_div">
			<p id="create_new">Create Corporation</p>
		</div>
		
		<div id="invitations_div">
			<?php
				$query = "SELECT c.corporation_id, corporation_name, corporation_abbr, manager_id, user_name, user_image
						  FROM corporation_members_invitations cmi, users u, corporations c, user_profile up
						  WHERE c.corporation_id = cmi.corporation_id AND cmi.user_id = '$user_id' AND u.user_id = manager_id
						  AND up.user_id = manager_id ORDER BY corporation_abbr";
				$result_corps = $conn->query($query);
				while($row_corps = $result_corps->fetch_row()) {
					list($corporation_id, $corporation_name, $corporation_abbr, $manager_id, 
						 $manager_name, $manager_img) = $row_corps;
				
					echo "\n\t\t\t" . '<div class="corp_det_div" id="corp_' . $corporation_id . '">' .
						 "\n\t\t\t\t" . '<p class="cdd_abbr">' . $corporation_abbr . '</p>' .
						 "\n\t\t\t\t" . '<p class="cdd_corp_name">' . $corporation_name . '</p>' .
						 "\n\t\t\t\t" . '<a class="cdd_manager_name" href="user_profile?id=' . $manager_id . 
										'">' . $manager_name . '</a>' .
						 "\n\t\t\t\t" . '<img class="cdd_manager_img" src="../user_images/' . $manager_img . '">' .
						 "\n\t\t\t\t" . '<div class="cdd_corp_det_divs">' .
						 "\n\t\t\t\t" . '</div>' .
						
							'<p class="cdd_join_corp button blue" corporation_id="' . $corporation_id . '">Join</p>' .				
							'<p class="cdd_reject_invite button red" corporation_id="' . $corporation_id . '">Reject</p>' .				
						'</div>';
				}
			?>
		
		</div>
	</div>
</main>
	
<?php include('footer.php'); ?> 