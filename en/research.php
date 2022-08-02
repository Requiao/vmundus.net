<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<p id="page_head">Research</p>
        
        <?php
		    $query = "SELECT ri.research_id, research_name, research_description, research_time, research_icon, 
					  IFNULL(is_researched, FALSE), IFNULL(start_date, FALSE), IFNULL(start_time, FALSE) 
					  FROM research_info ri LEFT JOIN user_researches cr ON
					  cr.research_id = ri.research_id AND user_id = '$user_id'
					  ORDER BY research_id";
			$result_research = $conn->query($query);
			while($row_research = $result_research->fetch_row()) {
				list($research_id, $research_name, $research_description, $research_time, $research_icon, 
					 $researched, $start_date, $start_time) = $row_research;
				
				$not_researched_div = '';
				$research_bar = '';
				$invest_resources = '';
				$research_time_str = "\n\t\t\t\t\t" . '<p class="rbd_time"><i class="fa fa-clock-o" aria-hidden="true"></i> ' . 
									   $research_time . ' minutes</p>';
				$end_time = '';
				if(!$researched && $start_date) {//research in progress
					$end_date_time = date('Y-m-d H:i:s', strtotime($start_date . ' ' . $start_time . 
									' + ' . $research_time . ' minutes'));
					$date1 = new DateTime(date('Y-m-d H:i:s'));
					$date2 = new DateTime($end_date_time);
					$diff = date_diff($date1,$date2);
					$end_in_days = $diff->format("%d");
					$end_in_hours = $diff->format("%H");
					$end_in_minutes = $diff->format("%I");
					$ends_in = $diff->format("%H:%I:%S");

					$end_time = '<p class="research_end_time">' . $end_in_days . ' days ' . $ends_in . '</p>';
					
					$progress = 100 - ((100 / $research_time) * (($end_in_days * 24 * 60) + ($end_in_hours * 60) + $end_in_minutes));
				
					$height = 195 - (195 * ($progress / 100));
					$not_researched_div = '<div class="rbd_not_researched" style="height: ' . $height . 'px"></div>';
					
					$research_time_str = '';
				}
				else if (!$researched) {//collecting products for research
					$query = "SELECT SUM(amount) FROM resources_research_req WHERE research_id = '$research_id'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($req_products) = $row;
					
					//get collected products for research
					$query = "SELECT IFNULL(SUM(amount), 0) FROM collected_products_user_research WHERE user_id = '$user_id' 
							  AND research_id = '$research_id'";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($collected_prods) = $row;
					
					$collected_percentage = round((100 / $req_products) * $collected_prods, 2);
					
					$research_bar = '<div class="building_research_bar">' .
                                        '<div class="progress" style="width:' . $collected_percentage . 
                                        '%; background-color:rgb(106, 180, 76);"></div>' .
                                        '<p class="bar_mark">' . $collected_prods . 
                                        '(' . $collected_percentage . '%)</p>' .
								    '</div>';
					
					$research_started = false;
				
                    $invest_resources = '<p class="invest_resources_for_research">Invest Resources</p>' .
										'<p hidden>' . $research_id . '</p>';
					$not_researched_div = '<div class="rbd_not_researched" style="height: 195px"></div>';
				}
				
				echo "\n\t\t\t\t" . '<div class="research_building_div" id="re_' . $research_id . '">' .
					 "\n\t\t\t\t\t" . '<p class="rbd_name">' . $research_name . '</p>' .
					 "\n\t\t\t\t\t" . '<img class="rbd_image" src="../building_icons/' . $research_icon . '">' .
									   $end_time .
									   $research_bar .
					 "\n\t\t\t\t\t" .  $invest_resources .
									   $research_time_str .
					 "\n\t\t\t\t\t" . '<p class="rbd_description">' . $research_description . '</p>' .
					 "\n\t\t\t\t\t" .  $not_researched_div .
                     "\n\t\t\t\t" . '</div>';
            }
        ?>
    </div>
</main>
<?php include('footer.php'); ?>