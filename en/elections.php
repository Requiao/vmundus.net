<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="page_head">Elections</p>
		<?php
			function getGovernmentHistory($candidate_id) {
				global $conn;
				if(strlen($candidate_id) < 10) {//party id always will be > 20
					$history = '';
					$query = "SELECT name, days, country_name, c.country_id
							  FROM country_government_history cgh, government_positions gp, country c
							  WHERE user_id = '$candidate_id' AND cgh.position_id = gp.position_id AND c.country_id = cgh.country_id
							  ORDER BY days DESC LIMIT 15";
					$result_hist = $conn->query($query);
					if($result_hist->num_rows == 0) {
						$history = 'This candidate is without experience.';
					}
					else {
						while($row_hist = $result_hist->fetch_row()) {
							list($position_name, $days, $country_name, $country_id) = $row_hist;
							$history .= '<p>- ' . $position_name . ' (<a href="country?country_id=' . $country_id . 
										  '">' . $country_name . '</a>): ' . $days . ' days</p>';
						}
					}
					return $history;
				}
			}
			
			function expireTime($end_date, $end_time, $end_start) {
				global $conn;
				$current_date = correctDate(date('Y-m-d'), date('H:i:s'));
				$current_time = correctTime(date('H:i:s'));
				
				$end_date_time = strtotime($end_date . ' ' . $end_time);
				$current_date_time = strtotime($current_date . ' ' . $current_time);

				$date1 = new DateTime($current_date . ' ' . $current_time);
				$date2 = new DateTime($end_date . ' ' . $end_time);
				$diff = date_diff($date1,$date2);
				$end_in_days = $diff->format("%a");
				$ends_in = $diff->format("%H:%I:%S");
				
				//do not show if timer supposed to stop
				if($end_date_time <= $current_date_time) {
					return;
				}
				
				echo "\n\t\t\t" . '<div class="elections_clock_div">' .
					 "\n\t\t\t\t" . '<p class="elections_end_in">Elections will ' . $end_start . ' in </p>';
				if($end_in_days > 0) {
					echo "\n\t\t\t\t" . '<p class="elections_clock">' . $end_in_days . ' days ' . $ends_in . '</p>';
				}
				else {
					echo "\n\t\t\t\t" . '<p class="elections_clock">' . $ends_in . '</p>';
				}
				echo "\n\t\t\t" . '</div>';
				
				return;
			}
			
			/* Display president elections */
			echo "\n\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t" . '<p class="heads">President Elections</p>';
			
			//display activated president elections
			$query = "SELECT election_id, start_date, start_time, ended, can_participate FROM election_info WHERE country_id = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND (ended = FALSE OR can_participate = TRUE)
					  AND type = 1";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($election_id, $start_date, $start_time, $ended, $can_participate) = $row;

				//scheduled president elections
				if($can_participate) {
					//correct date/time
					$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), $start_time); //will start in 1 days
					$end_time = correctTime($start_time);
				
					//find out when expires and display
					expireTime($end_date, $end_time, "start");
					
					//display button to participate in elections
					echo "\n\t\t\t" . '<p class="participate_in_elections button blue" id="' . $election_id . '">Apply</p>';
					
					//select candidates
					$query = "SELECT user_name, u.user_id, user_image FROM users u, user_profile up, president_elections 
							  WHERE election_id = '$election_id' AND u.user_id = candidate_id AND up.user_id = u.user_id";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($candidate_name, $candidate_id, $candidate_image) = $row;
						
						//if user participates in elections, display button to cancel his candidature
						if($candidate_id == $user_id) {
							$cancel_candidature = "\n\t\t\t\t" . '<p class="stop_participate_in_elections button red" id="' . 
															  $election_id . '">Cancel</p>';
						}
						else {
							$cancel_candidature = "";
						}
						
						//get candidate government history
						$history = getGovernmentHistory($candidate_id);
						
						echo "\n\t\t\t" . '<div class="candidate_div">' .
							 "\n\t\t\t\t" . '<a class="candidate_name" href="user_profile?id=' . $candidate_id . 
										  '">' . $candidate_name . '</a>' .
							 "\n\t\t\t\t" . '<img src="../user_images/' . $candidate_image . '" class="candidate_image">' .
							 "\n\t\t\t\t" . '<div class="candidate_history">' . 
							 "\n\t\t\t\t\t" . '<p>History: </p>' .
											   $history . 
							 "\n\t\t\t\t" . '</div>' .
							 $cancel_candidature .
							 "\n\t\t\t" . '</div>';
					}
				}
				
				// activated president elections
				if(!$ended) {
					//correct date/time
					$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), $start_time); //will end in 1 day
					$end_time = correctTime($start_time);
				
					//find out when expires and display
					expireTime($end_date, $end_time, "end");
				
					//select candidates
					$query = "SELECT user_name, u.user_id, user_image FROM users u, user_profile up, president_elections 
							  WHERE election_id = '$election_id' AND u.user_id = candidate_id AND up.user_id = u.user_id";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($candidate_name, $candidate_id, $candidate_image) = $row;
						
						//get candidate government history
						$history = getGovernmentHistory($candidate_id);
						
						//check if already voted
						$query = "SELECT * FROM users_voted WHERE user_id = '$user_id' AND election_id = '$election_id'";
						$result_check = $conn->query($query);
						if($result_check->num_rows == 1) {
							$cd_class_vote = "cd_voted";
							$cd_class_vote_yes = "cd_yes_voted";
							$cd_class_check = "cd_checked";
							$vote_voted = "Voted";
						}
						else {
							$cd_class_vote = "cd_vote";
							$cd_class_vote_yes = "cd_yes_vote";
							$cd_class_check = "cd_check";
							$vote_voted = "Vote";
						}
						
						echo "\n\t\t\t" . '<div class="candidate_div ' . $election_id . '">' .
							 "\n\t\t\t\t" . '<a class="candidate_name" href="user_profile?id=' . $candidate_id . 
										  '">' . $candidate_name . '</a>' .
							 "\n\t\t\t\t" . '<img src="../user_images/' . $candidate_image . '" class="candidate_image">' .
							 "\n\t\t\t\t" . '<div class="candidate_history">' . 
							 "\n\t\t\t\t\t" . '<p>History: </p>' .
											   $history . 
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t" . '<div class="' . $cd_class_vote . '">' .
							 "\n\t\t\t\t\t" . '<p class="' . $cd_class_vote_yes . '">' . $vote_voted . '</p>' .
							 "\n\t\t\t\t\t" . '<p class="' . $cd_class_check . '"><span class="fa fa-check" aria-hidden="true"></span></p>' .
							 "\n\t\t\t\t\t" . '<p class="candidate_id" hidden>' . $candidate_id . '</p>' .
							 "\n\t\t\t\t\t" . '<p class="election_id" hidden>' . $election_id . '</p>' .
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t" . '</div>';
					}
				}
			}
			else {//display next elections
				$query = "SELECT term - 2, day_number, DATE_ADD(elected, INTERVAL (term - 2) DAY)
						  FROM country_government cg, day_count dc, government_term_length gcl 
						  WHERE cg.country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') 
						  AND cg.position_id = 1 AND dc.date = elected AND gcl.position_id = cg.position_id
						  AND gcl.country_id = cg.country_id AND user_id IS NOT NULL";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($term, $day_elected, $elections_date) = $row;
					$next_elections_date = correctDate($elections_date, date('H:i:s'));
					$next_elections_day = $day_elected + $term;
					
					if(strtotime($next_elections_date) < strtotime($elections_date)) {
						$next_elections_day++;
					}
					else if(strtotime($next_elections_date) > strtotime($elections_date)) {
						$next_elections_day--;
					}
					
					echo "\n\t\t\t" . '<p class="next_elections">Next President Elections will be on day ' . $next_elections_day .
									' (' . date('M j', strtotime($next_elections_date)) . ')</p>';
				}
				else {
					echo "\n\t\t\t" . '<p class="next_elections">Next President Elections will be scheduled tomorrow since there is no 
									   president in the country.</p>';
				}
			}//end display next elections		
			
			// display president elections history
			echo "\n\t\t\t" . '<div class="prev_elctions_div">' .
				 "\n\t\t\t\t" . '<p class="prev_elec_head" id="prev_pres_elec_head">Previous Presidential Elections ' .
								'<i class="fa fa-link" aria-hidden="true"></i></p>' .
				 "\n\t\t\t" . '</div>';
			
			$query = "SELECT pe.election_id, start_date, start_time, day_number, SUM(yes) 
					  FROM election_info ei, day_count, president_elections pe 
					  WHERE country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND ended = 1
					  AND date = start_date AND pe.election_id = ei.election_id AND type = 1 GROUP BY election_id, day_number
					  ORDER BY start_date DESC, start_time DESC LIMIT 10";
			$result = $conn->query($query);
			echo "\n\t\t\t" . '<div id="prev_president_elections">';
			if($result->num_rows > 0) {
				while($row = $result->fetch_row()) {
					list($election_id, $start_date, $start_time, $day_number, $overall_vote_number) = $row;

					//count percent per vote
					if($overall_vote_number > 0) {
						$percent_per_vote = 100 / $overall_vote_number;
					}
					else {
						$percent_per_vote = 0;
					}

					$corrected_start_date = correctDate($start_date, $start_time);
					
					if(strtotime($corrected_start_date) < strtotime($start_date)) {
						$day_number++;
					}
					else if(strtotime($corrected_start_date) > strtotime($start_date)) {
						$day_number--;
					}
					
					//select candidates
					$query = "SELECT user_name, u.user_id, user_image, yes FROM users u, user_profile up, president_elections 
							  WHERE election_id = '$election_id' AND u.user_id = candidate_id AND up.user_id = u.user_id";
					$result_candidates = $conn->query($query);
					echo "\n\t\t\t\t" . '<div class="prev_president_elections_divs">';
					echo "\n\t\t\t\t\t" . '<p class="prev_president_elections_date">Elections date: day ' . $day_number .
									  ' (' . date('M j', strtotime($corrected_start_date)) . ')</p>';
					while($row_candidates = $result_candidates->fetch_row()) {
						list($candidate_name, $candidate_id, $candidate_image, $votes) = $row_candidates;
						
						//count percentage of votes
						$vote_percent = round($votes * $percent_per_vote, 2);
						
						//get candidate government history
						$history = getGovernmentHistory($candidate_id);
						
						echo "\n\t\t\t\t\t" . '<div class="candidate_div">' .
							 "\n\t\t\t\t\t\t" . '<a class="candidate_name" href="user_profile?id=' . $candidate_id . 
										  '">' . $candidate_name . '</a>' .
							 "\n\t\t\t\t\t\t" . '<img src="../user_images/' . $candidate_image . '" class="candidate_image">' .			  
							 "\n\t\t\t\t" . '<div class="candidate_history">' . 
							 "\n\t\t\t\t\t" . '<p>History: </p>' .
											   $history . 
							 "\n\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t\t\t" . '<div class="vote_bar">' .
							 "\n\t\t\t\t\t\t\t" . '<div class="progress" style="width:' . $vote_percent . '%;"></div>' .
							 "\n\t\t\t\t\t\t\t" . '<p>' . $votes . ' (' . $vote_percent . '%)</p> '. 
							 "\n\t\t\t\t\t\t" . '</div>' .
							 "\n\t\t\t\t\t" . '</div>';
							 "\n\t\t\t\t\t" . '</div>';
					}
					echo "\n\t\t\t\t" . '</div>';
				}
			}
			else {
				echo "\n\t\t\t\t" . '<p>There\'s no president elections history.</p>';
			}
			echo "\n\t\t\t" . '</div>';
			echo "\n\t\t" . '</div>';
			
			
			/* congress elections */
			echo "\n\n\t\t" . '<div class="info_blocks">' .
				 "\n\t\t\t" . '<p class="heads">Congress Elections</p>';
		
			//congress elections
			$query = "SELECT election_id, start_date, start_time, ended, can_participate FROM election_info WHERE country_id = 
					  (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') 
					  AND (ended = FALSE OR can_participate = TRUE) AND type = 3";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				$row = $result->fetch_row();
				list($election_id, $start_date, $start_time, $ended, $can_participate) = $row;
				
				if($can_participate) {//display scheduled congress elections
					//correct date/time
					//will start in 1 days
					$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), date('H:i:s', strtotime($start_time))); 
					$end_time = correctTime(date('H:i:s', strtotime($start_time)));
					
					//find out when will start and display
					expireTime($end_date, $end_time, "start");
						
					//display button to participate in elections
					echo "\n\t\t\t" . '<p class="participate_in_elections button blue" id="' . $election_id . '">Apply</p>';
						
					//select candidates
					$query = "SELECT user_name, u.user_id, user_image FROM users u, user_profile up, congress_elections 
							  WHERE election_id = '$election_id' AND u.user_id = candidate_id AND up.user_id = u.user_id
							  UNION
							  SELECT party_name, pp.party_id, flag FROM political_parties pp, congress_elections ce
							  WHERE election_id = '$election_id' AND pp.party_id = ce.party_id";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($candidate_name, $candidate_id, $candidate_image) = $row;
						
						//get candidate government history
						$history = getGovernmentHistory($candidate_id);
						
						//if user participates in elections, display button to cancel his candidature
						if($candidate_id == $user_id) {
							$cancel_candidature = "\n\t\t\t\t" . '<p class="stop_participate_in_elections button red" id="' . 
															  $election_id . '">Cancel</p>';
						}
						else {
							$cancel_candidature = "";
						}
						
						if(strlen($candidate_id) < 10) {//party id always will be > 20
							echo "\n\t\t\t" . '<div class="candidate_div">' .
								 "\n\t\t\t\t" . '<a class="candidate_name" href="user_profile?id=' . $candidate_id . 
											  '">' . $candidate_name . '</a>' .
								 "\n\t\t\t\t" . '<img src="../user_images/' . $candidate_image . '" class="candidate_image">' .
								 "\n\t\t\t\t" . '<div class="candidate_history">' . 
								 "\n\t\t\t\t\t" . '<p>History: </p>' .
												   $history . 
								 "\n\t\t\t\t" . '</div>' .
								 $cancel_candidature .
								 "\n\t\t\t" . '</div>';
						}
						else {//for party
							echo "\n\t\t\t" . '<div class="candidate_div">' .
								 "\n\t\t\t\t" . '<a class="candidate_name" href="party_info?party_id=' . $candidate_id . 
											  '">' . $candidate_name . '</a>' .
								 "\n\t\t\t\t" . '<img src="../party_flags/' . $candidate_image . '" class="party_image">' .
								 "\n\t\t\t\t" . '<p hidden></p>' .
								 "\n\t\t\t" . '</div>';
						}
					}
				}
				
				if(!$ended) {//display activated congress elections
					//correct date/time
					//will end in 1 day
					$end_date = correctDate(date('Y-m-d', strtotime($start_date . ' + 1 days')), $start_time); 
					$end_time = correctTime($start_time);
					
					//find out when expires and display
					expireTime($end_date, $end_time, "end");
					
					//select candidates
					$query = "SELECT user_name, u.user_id, user_image FROM users u, user_profile up, congress_elections 
							  WHERE election_id = '$election_id' AND u.user_id = candidate_id AND up.user_id = u.user_id
							  UNION
							  SELECT party_name, pp.party_id, flag FROM political_parties pp, congress_elections ce
							  WHERE election_id = '$election_id' AND pp.party_id = ce.party_id";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($candidate_name, $candidate_id, $candidate_image) = $row;
						
						//get candidate government history
						$history = getGovernmentHistory($candidate_id);
						
						//check if already voted
						$query = "SELECT * FROM users_voted WHERE user_id = '$user_id' AND election_id = '$election_id'";
						$result_check = $conn->query($query);
						if($result_check->num_rows == 1) {
							$cd_class_vote = "cd_voted";
							$cd_class_vote_yes = "cd_yes_voted";
							$cd_class_check = "cd_checked";
							$vote_voted = "Voted";
						}
						else {
							$cd_class_vote = "cd_vote";
							$cd_class_vote_yes = "cd_yes_vote";
							$cd_class_check = "cd_check";
							$vote_voted = "Vote";
						}
						
						if(strlen($candidate_id) < 10) {//party id always will be >= 23
							echo "\n\t\t\t" . '<div class="candidate_div ' . $election_id . '">' .
								 "\n\t\t\t\t" . '<a class="candidate_name" href="user_profile?id=' . $candidate_id . 
											  '">' . $candidate_name . '</a>' .
								 "\n\t\t\t\t" . '<img src="../user_images/' . $candidate_image . '" class="candidate_image">' .
								 "\n\t\t\t\t" . '<div class="candidate_history">' . 
								 "\n\t\t\t\t\t" . '<p>History: </p>' .
												   $history . 
								 "\n\t\t\t\t" . '</div>' .
								 "\n\t\t\t\t" . '<div class="' . $cd_class_vote . '">' .
								 "\n\t\t\t\t\t" . '<p class="' . $cd_class_vote_yes . '">' . $vote_voted . '</p>' .
								 "\n\t\t\t\t\t" . '<p class="' . $cd_class_check . '"><span class="fa fa-check" aria-hidden="true"></span></p>' .
								 "\n\t\t\t\t\t" . '<p class="candidate_id" hidden>' . $candidate_id . '</p>' .
								 "\n\t\t\t\t\t" . '<p class="election_id" hidden>' . $election_id . '</p>' .
								 "\n\t\t\t\t" . '</div>' .
								 "\n\t\t\t" . '</div>';
						}
						else {//for party
							echo "\n\t\t\t" . '<div class="candidate_div ' . $election_id . '">' .
								 "\n\t\t\t\t" . '<a class="candidate_name" href="party_info?party_id=' . $candidate_id . 
											  '">' . $candidate_name . '</a>' .
								 "\n\t\t\t\t" . '<img src="../party_flags/' . $candidate_image . '" class="party_image">' .
								 "\n\t\t\t\t" . '<p hidden></p>' .
								 "\n\t\t\t\t" . '<div class="' . $cd_class_vote . '">' .
								 "\n\t\t\t\t\t" . '<p class="' . $cd_class_vote_yes . '">' . $vote_voted . '</p>' .
								 "\n\t\t\t\t\t" . '<p class="' . $cd_class_check . '"><span class="fa fa-check" aria-hidden="true"></span></p>' .
								 "\n\t\t\t\t\t" . '<p class="candidate_id" hidden>' . $candidate_id . '</p>' .
								 "\n\t\t\t\t\t" . '<p class="election_id" hidden>' . $election_id . '</p>' .
								 "\n\t\t\t\t" . '</div>' .
								 "\n\t\t\t" . '</div>';
						}
					}
				}
			}//end display activated congress elections
			else {//display next elections
				$query = "SELECT term - 2, day_number, DATE_ADD(elected, INTERVAL (term - 2) DAY)
						  FROM congress_details cd, day_count dc, government_term_length gcl 
						  WHERE cd.country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') 
						  AND dc.date = elected AND gcl.position_id = 3
						  AND gcl.country_id = cd.country_id AND (SELECT COUNT(*) FROM congress_members 
						  WHERE country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')) > 0";
				$result = $conn->query($query);
				
				
				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					list($term, $day_elected, $elections_date) = $row;
					$next_elections_date = correctDate($elections_date, date('H:i:s'));
					$next_elections_day = $day_elected + $term;
					
					if(strtotime($next_elections_date) < strtotime($elections_date)) {
						$next_elections_day++;
					}
					else if(strtotime($next_elections_date) > strtotime($elections_date)) {
						$next_elections_day--;
					}
					
					echo "\n\t\t\t" . '<p class="next_elections" id="next_cong_elections">Next Congress Elections will be on day ' . $next_elections_day .
							    ' (' . date('M j', strtotime($next_elections_date)) . ')</p>';
				}
				else {
					echo "\n\t\t\t" . '<p class="next_elections">Next Elections to Congress will be scheduled tomorrow since there is no 
									   congress members in the country.</p>';
				}	
			}

			// display congress elections history
			echo "\n\t\t\t" . '<div class="prev_elctions_div">' .
				 "\n\t\t\t\t" . '<p class="prev_elec_head" id="prev_cong_elec_head">Previous Congress Elections ' .
								'<i class="fa fa-link" aria-hidden="true"></i></p>' .
				 "\n\t\t\t" . '</div>';
			
			$query = "SELECT ce.election_id, start_date, start_time, day_number, SUM(yes) 
					  FROM election_info ei, day_count, congress_elections ce 
					  WHERE country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND ended = 1
					  AND date = start_date AND ce.election_id = ei.election_id AND type = 3 GROUP BY election_id, day_number";
			$result_hist = $conn->query($query);
			echo "\n\t\t\t" . '<div id="prev_congress_elections">';
			if($result_hist->num_rows > 0) {	
				while($row_hist = $result_hist->fetch_row()) {
					list($election_id, $start_date, $start_time, $day_number, $overall_vote_number) = $row_hist;

					//count percent per vote
					if($overall_vote_number > 0) {
						$percent_per_vote = 100 / $overall_vote_number;
					}
					else {
						$percent_per_vote = 0;
					}

					$corrected_start_date = correctDate($start_date, $start_time);
					
					if(strtotime($corrected_start_date) < strtotime($start_date)) {
						$day_number++;
					}
					else if(strtotime($corrected_start_date) > strtotime($start_date)) {
						$day_number--;
					}
					
					//select candidates
					$query = "SELECT user_name, u.user_id, user_image, yes FROM users u, user_profile up, congress_elections 
							  WHERE election_id = '$election_id' AND u.user_id = candidate_id AND up.user_id = u.user_id
							  UNION
							  SELECT party_name, pp.party_id, flag, yes FROM political_parties pp, congress_elections ce
							  WHERE election_id = '$election_id' AND pp.party_id = ce.party_id";
					$result = $conn->query($query);
					echo "\n\t\t\t\t" . '<div class="prev_cong_elections_divs">';
					echo "\n\t\t\t\t\t" . '<p class="prev_cong_elections_date">Elections date: day ' . $day_number .
									  ' (' . date('M j', strtotime($corrected_start_date)) . ')</p>';
					while($row = $result->fetch_row()) {
						list($candidate_name, $candidate_id, $candidate_image, $votes) = $row;
						
						//count percentage of votes
						$vote_percent = round($votes * $percent_per_vote, 2);
						
						//get candidate government history
						$history = getGovernmentHistory($candidate_id);
						
						if(strlen($candidate_id) < 10) {//party id always will be > 20
							echo "\n\t\t\t\t" . '<div class="candidate_div ' . $election_id . '">' .
								 "\n\t\t\t\t\t" . '<a class="candidate_name" href="user_profile?id=' . $candidate_id . 
											  '">' . $candidate_name . '</a>' .
								 "\n\t\t\t\t\t" . '<img src="../user_images/' . $candidate_image . '" class="candidate_image">' .
								 "\n\t\t\t\t" . '<div class="candidate_history">' . 
								 "\n\t\t\t\t\t" . '<p>History: </p>' .
											   $history . 
								 "\n\t\t\t\t" . '</div>' .
								 "\n\t\t\t\t\t\t" . '<div class="vote_bar">' .
								 "\n\t\t\t\t\t\t\t" . '<div class="progress" style="width:' . $vote_percent . '%;"></div>' .
								 "\n\t\t\t\t\t\t\t" . '<p>' . $votes . ' (' . $vote_percent . '%)</p> '. 
								 "\n\t\t\t\t\t\t" . '</div>' .
								 "\n\t\t\t\t" . '</div>';
						}
						else {//for party
							echo "\n\t\t\t\t" . '<div class="candidate_div ' . $election_id . '">' .
								 "\n\t\t\t\t\t" . '<a class="candidate_name" href="party?party_id=' . $candidate_id . 
											  '">' . $candidate_name . '</a>' .
								 "\n\t\t\t\t\t" . '<img src="../party_flags/' . $candidate_image . '" class="party_image">' .
								 "\n\t\t\t\t\t" . '<p hidden></p>' .
								 "\n\t\t\t\t\t\t" . '<div class="vote_bar">' .
								 "\n\t\t\t\t\t\t\t" . '<div class="progress" style="width:' . $vote_percent . '%;"></div>' .
								 "\n\t\t\t\t\t\t\t" . '<p>' . $votes . ' (' . $vote_percent . '%)</p> '. 
								 "\n\t\t\t\t\t\t" . '</div>' .
								 "\n\t\t\t\t" . '</div>';
						}
					}
					echo "\n\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
			}
			else {
				echo "\n\t\t" . '<p>There\'s no congress elections history.</p>';
			}
			echo "\n\t\t" . '</div>';
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>