<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<?php
			//view other unions
			$query = "SELECT union_id, union_name, description, union_flag, user_name, leader FROM unions, users
					  WHERE leader = user_id";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($union_id, $union_name, $description, $flag, $leader_name, $leader) = $row;
				echo "\n\t\t" . '<div class="union_list_div">' .
					 "\n\t\t\t" . '<img class="union_flag" src="../union_flags/' . $flag . '">' .
					 "\n\t\t\t" . '<p class="union_name">' . $union_name . '</p>' .
					 "\n\t\t\t" . '<a class="union_leader" href="user_profile?id=' . $leader . '" target="_blank">Union leader: <i>' . $leader_name . 
								  '</i></a>' .
					 "\n\t\t\t" . '<p class="union_desc">' . $description . '</p>' .
					 "\n\t\t\t" . '<a class="button blue details" href="union_info?union_id=' . $union_id . '" target="_blank">Details</a>';
				echo "\n\t\t" . '</div>';
			}
		?>
	
	</div>
</main>

<?php include('footer.php'); ?>