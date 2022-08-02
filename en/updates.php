<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="updates_head">Game Updates</p>
		<div id="timeline">
		<?php
			$query = "SELECT update_id, update_name, u.date, time, day_number FROM updates u, day_count dc
					  WHERE u.date = dc.date 
					  UNION
					  SELECT update_id, update_name, u.date, time, 'n/a' FROM updates u
					  WHERE u.date NOT IN (SELECT date FROM day_count)
					  ORDER BY date DESC, time DESC";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($update_id, $update_name, $date, $time, $day_number) = $row;
				
				$user_date = correctDate($date, $time);
			
				if(strtotime($user_date) > strtotime($date)) {
					$day_number++;
				}
				else if(strtotime($user_date) < strtotime($date)) {
					$day_number--;
				}

				echo "\n\t\t" . '<div class="update_info">' .
					 "\n\t\t\t" . '<p class="update_day">Day ' . $day_number . '</p>' .
					 "\n\t\t\t" . '<div class="description">' .
					 "\n\t\t\t\t" . '<p class="update_heading"  id="' . $update_id . '">' . $update_name . '</p>' . 
					 "\n\t\t\t\t" . '<ul>';
					 
				$query = "SELECT update_desc_id, description FROM updates_info WHERE update_id = '$update_id'";
				$result_desc = $conn->query($query);
				while($row_desc = $result_desc->fetch_row()) {
					list($update_desc_id, $description) = $row_desc;
					echo "\n\t\t\t\t\t" . '<li id="' . $update_desc_id . '">' . $description . '</li>';
				}
				
				echo "\n\t\t\t\t" . '</ul>' .
					 "\n\t\t\t" . '</div>' .
					 "\n\t\t" . '</div>';
			}
		?>
		</div>
	</div>
</main>

<?php include('footer.php'); ?>