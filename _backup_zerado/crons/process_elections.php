<?php
	//Description: End elections, assign new president/congress. cron time 1-25/5 * * * *
	//Description: Assign, activate elections.
	//Note: scheduled: ended = 1 and can_participate = 1
	//Note: activated: ended = 0 and can_participate = 0
	//Note: ended: ended = 1 and can_participate = 0

	include('/var/www/html/connect_db.php');
	include('/var/www/html/php_functions/get_time_for_id.php'); //function getTimeForId().
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	include('/var/www/html/crons/timezone_id.php'); //$timezone_id
	
	/* End elections, assign new president/congress*/
	//select electins in process
	$query = "SELECT election_id, country_id, start_date, start_time, type FROM election_info WHERE ended = 0";
	$result = $conn->query($query);
	$x=0;
	while($row = $result->fetch_row()) {
		list($election_id, $country_id, $start_date, $start_time, $type) = $row;
		$elections_date = date('Y-m-d H:i:s', strtotime($start_date . ' ' . $start_time . ' + 24 hours'));//elections are set to end in 24 hours
		$x++;
		if($elections_date <= date('Y-m-d H:i:s')) {//end elections
			if($type == 1) {//president elections
				//close elections
				$query = "UPDATE election_info SET ended = 1 WHERE election_id = '$election_id'";
				$conn->query($query);
				
				$query = "SElECT candidate_id, yes FROM president_elections WHERE election_id = '$election_id' ORDER BY yes DESC";
				$result_candidate = $conn->query($query);
				if($result_candidate->num_rows > 0) {//if no candidates, elections will be reassigned
					$candidates = array();
					$total_votes = 0;
					$x = 0;
					while($row_candidate = $result_candidate->fetch_row()) {
						list($candidate_id, $yes) = $row_candidate;
						$candidates[$x][0] = $candidate_id;
						$candidates[$x][1] = $yes;
						$total_votes += $yes;
						$x++;
					}
					
					//find winner(DESC order, winner always will be in the first row if not tie)
					$reschedule_elections = false;
					$winner_id = $candidates[0][0];
					$reschedule_elections_candidates_id = array();
					
					if(isset($candidates[1][1])) {//is there a second candidate?
						if ($candidates[0][1] == $candidates[1][1]) {//check if tie
							$reschedule_elections = true;
							$reschedule_elections_candidates_id[0] = $candidates[0][0];
							$reschedule_elections_candidates_id[1] = $candidates[1][0];
						}
					}
					
					//find percentage
					if($total_votes > 0) {
						$percent_per_vote = 100 / $total_votes;
					}
					else {
						$percent_per_vote = 0;
					}
					for($i = 0; $i < $x; $i++) {//percentage for each candidate
						$candidates[$i][2] = round($candidates[$i][1] * $percent_per_vote, 2);
					}
					
					//if none of the candidates received 50% or more, then reschedule elections
					if(!$reschedule_elections && isset($candidates[1][0])) {
						if($candidates[0][2] < 50) {//first candidate will have the most votes. (ordered DESC)
							$reschedule_elections = true;
						}
					}
					
					//reschedule elections
					if($reschedule_elections) {
						reschedulePresidentElections($reschedule_elections_candidates_id, $country_id, 1);
						
						//nobody won. notify
						for($i = 0; $i < $x; $i++) {
							$votes = $candidates[$i][1];
							$percent = $candidates[$i][2];
							
							if($reschedule_elections_candidates_id[0] == $candidates[$i][0] || 
							$reschedule_elections_candidates_id[0] == $candidates[$i][1]) {
								$notification = "Thank you for your participation in President elections. But nobody won. Since you were one 
												 of the candidates that received the same amount of votes, elections will be rescheduled 
												 and you will be one out of two candidates. You got $votes votes ($percent%).";
							}
							else {
								$notification = "Thank you for your participation in President elections. Unfortunately, nobody won these elections
												 because two other candidates received same amount of votes. Elections will be rescheduled. 
												 You got $votes votes ($percent%).";
							}
							sendNotification($notification, $candidates[$i][0]);	
						}
					}
					else {//assign new president
						//if governor, remove
						$query = "UPDATE country_government SET user_id = NULL WHERE
								  user_id = '$winner_id'";
						$conn->query($query);
						//if congresman, remove
						$query = "DELETE FROM congress_members WHERE user_id = '$winner_id'";
						$conn->query($query);
					
						$query = "UPDATE country_government SET user_id = '$winner_id' 
								  WHERE country_id = '$country_id' AND position_id = 1";
						$conn->query($query);

						$query = "UPDATE country_government SET elected = CURRENT_DATE
								  WHERE country_id = '$country_id' AND position_id = 1";
						$conn->query($query);
						
						for($i = 0; $i < $x; $i++) {
							$votes = $candidates[$i][1];
							$percent = $candidates[$i][2];
							if($candidates[$i][0] == $winner_id) {
								$notification = "Congratulations! You won President elections. You got $votes votes ($percent%).";
							}
							else {
								$winner_votes = $candidates[0][1];
								$winner_percent = $candidates[0][2];
								$notification = "Thank you for your participation in President elections. Unfortunately, another candidate won 
												 these elections with $winner_votes votes ($winner_percent%). 
												 You got $votes votes ($percent%).";
							}
							sendNotification($notification, $candidates[$i][0]);	
						}
					}
				}
			}
			else if($type == 3) {//congress elections
				//close elections
				$query = "UPDATE election_info SET ended = 1 WHERE election_id = '$election_id'";
				$conn->query($query);
			
				$query = "SElECT candidate_id, party_id, yes FROM congress_elections WHERE election_id = '$election_id'";
				$result_candidate = $conn->query($query);
				if($result_candidate->num_rows > 0) {//if no candidates, elections will be reassigned
				
					$candidates = array();
					$x = 0;
					while($row_candidate = $result_candidate->fetch_row()) {
						list($candidate_id, $party_id, $yes) = $row_candidate;
						$candidates[$x][0] = $candidate_id;
						$candidates[$x][1] = $party_id;
						$candidates[$x][2] = $yes;
						$x++;
					}
					
					//find total votes
					$total_votes = 0;
					for($i = 0; $i < $x; $i++) {
						$total_votes += $candidates[$i][2];
					}

					//find percentage
					if($total_votes > 0) {
						$percent_per_vote = 100 / $total_votes;
					}
					else {
						$percent_per_vote = 0;
					}
					for($i = 0; $i < $x; $i++) {//vote percentage for each candidate/party
						$candidates[$i][3] = round($candidates[$i][2] * $percent_per_vote, 2);
					}
					
					//assign new congress
					//get rid from old congress
					$query = "DELETE FROM congress_members WHERE country_id = '$country_id'";
					$conn->query($query);
					
					//update congress_details
					$query = "UPDATE congress_details SET elected = CURRENT_DATE WHERE country_id = '$country_id'";
					$conn->query($query);
					
					//get available congress seats
					$query = "SELECT seats FROM country_congress_seats WHERE country_id = '$country_id'";
					$result_seats = $conn->query($query);
					$row_seats = $result_seats->fetch_row();
					list($seats) = $row_seats;
					$available_seats = $seats;

					//for parties section
					for($i = 0; $i < $x; $i++) {
						if($candidates[$i][1] != 0) {//party
							//determine seats for each party
							$seats_won = round(($candidates[$i][3] / 100) * $seats);
							$votes = $candidates[$i][2];
							$percent = $candidates[$i][3];
							$party_id = $candidates[$i][1];
							
							//determine party congress candidates
							$query = "SELECT candidate_id, position_number FROM party_congress_candidates WHERE party_id = '$party_id'
									  ORDER BY position_number ASC";
							$result_candidates = $conn->query($query);
							while($row_candidates = $result_candidates->fetch_row()) {
								list($candidate_id, $position_number) = $row_candidates;
								
								if($available_seats > 0 && $seats_won > 0) {
									//if governor, remove
									$query = "SELECT position_id FROM country_government 
											  WHERE user_id = '$candidate_id' AND country_id = '$country_id'";
									$result_pos = $conn->query($query);
									if($result_pos->num_rows >= 1) {
										$row_pos = $result_pos->fetch_row();
										list($position_id) = $row_pos;
										
										$query = "UPDATE country_government SET user_id = NULL WHERE
												  user_id = '$candidate_id'";
										$conn->query($query);
									}
									
									$query = "INSERT INTO congress_members VALUES ('$country_id', '$candidate_id')";
									$conn->query($query);
									
									$notification = "Congratulations! You won Congress elections. Your party got $votes votes ($percent%).";
									
									$seats_won--;
									$available_seats--;
								}
								else {
									$notification = "Thank you for your participation in Congress elections. Unfortunately, your party 
													 did not received enough votes for you to get a seat in the congress. 
													 Your party got $votes votes ($percent%).";
								}
								sendNotification($notification, $candidate_id);
							}
						}
					}
					
					//assign new candidates for congress.
					for($i = 0; $i < $x; $i++) {
						//determine seats for each candidate
						if($candidates[$i][0] != 0) {//candidate
							$seats_won = round(($candidates[$i][3] / 100) * $seats);
							$votes = $candidates[$i][2];
							$percent = $candidates[$i][3];
							
							if($seats_won >= 1 && $available_seats >= 1) {//got enough votes for 1 or more seats.
								$candidate_id = $candidates[$i][0];
								
								//if governor, remove
								$query = "SELECT position_id FROM country_government 
										  WHERE user_id = '$candidate_id' AND country_id = '$country_id'";
								$result_pos = $conn->query($query);
								if($result_pos->num_rows >= 1) {
									$row_pos = $result_pos->fetch_row();
									list($position_id) = $row_pos;
									
									$query = "UPDATE country_government SET user_id = NULL WHERE
											  user_id = '$candidate_id'";
									$conn->query($query);
								}
								
								$query = "INSERT INTO congress_members VALUES ('$country_id', '$candidate_id')";
								$conn->query($query);
								
								$notification = "Congratulations! You won Congress elections. You got $votes votes ($percent%).";
															
								$available_seats--;
							}
							else {
								$notification = "Thank you for your participation in Congress elections. Unfortunately, you did not received 
												 enough votes. You got $votes votes ($percent%).";
							}
							sendNotification($notification, $candidates[$i][0]);
						}
					}
				}
			}
		}
		else {
			continue;
		}
	}

	function reschedulePresidentElections($candidates_id, $country_id) {
		global $conn;
		$election_id = getTimeForId() . $country_id;
		//create elections
		$query = "INSERT INTO election_info VALUES('$election_id', '$country_id', CURRENT_DATE, 
				  CURRENT_TIME, 0, 1, 0)";
		$conn->query($query);

		//insert candidates
		for($x = 0; $x < 2; $x++) {
			$query = "INSERT INTO president_elections VALUES('$election_id', '$candidates_id[$x]', 0)";
			$conn->query($query);
		}
	}
	
	
	
	
	/* check if new elections scheduled */
	//if yes, check if needs to be activated(5 days after schedule), 
	//schedule if not scheduled(5 days before start + 1 days for elections)

	//get list of countries where president, congress must be re-elected(term expired or no president/congress in country)
	//Schedule new elections if term will end in 6 days. type->president/congress
	$query = "SELECT cg.country_id, cg.position_id FROM country_government cg, government_term_length gtl WHERE
			  DATE_ADD(elected, INTERVAL (term - 2) DAY) <= CURRENT_DATE
			  AND gtl.country_id = cg.country_id AND cg.position_id = 1
			  AND gtl.position_id = cg.position_id AND cg.country_id IN 
			  (SELECT country_id FROM country WHERE timezone_id = '$timezone_id')
			  AND cg.country_id NOT IN (SELECT country_id FROM election_info WHERE type = 1 AND (ended = 1 AND can_participate = 1) 
			  OR (ended = 0 AND can_participate = 0))
			  UNION
			  SELECT country_id, 1 AS position_id FROM country c WHERE country_id IN
		     (SELECT country_id FROM country_government WHERE user_id IS NULL AND position_id = 1) AND timezone_id = '$timezone_id'
			  AND country_id NOT IN (SELECT country_id FROM election_info WHERE type = 1 AND (ended = 1 AND can_participate = 1) 
			  OR (ended = 0 AND can_participate = 0))
			  UNION
			  SELECT cd.country_id, gtl.position_id FROM congress_details cd, government_term_length gtl WHERE
			  DATE_ADD(elected, INTERVAL (term - 2) DAY) <= CURRENT_DATE
			  AND gtl.country_id = cd.country_id AND gtl.position_id = 3
			  AND cd.country_id IN (SELECT country_id FROM country WHERE timezone_id = '$timezone_id')
			  AND cd.country_id NOT IN (SELECT country_id FROM election_info WHERE type = 3 AND (ended = 1 AND can_participate = 1) 
			  OR (ended = 0 AND can_participate = 0))
			  UNION
			  SELECT country_id, 3 AS position_id FROM country c WHERE country_id NOT IN
			 (SELECT country_id FROM congress_members GROUP BY country_id) 
			  AND timezone_id = '$timezone_id' AND country_id NOT IN (SELECT country_id FROM election_info WHERE type = 3 AND (ended = 1 AND can_participate = 1) 
			  OR (ended = 0 AND can_participate = 0)) ORDER BY country_id";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($country_id, $position_id) = $row;
		
		$election_id = getTimeForId() . $country_id;
		
		//schedule elections
		$query = "INSERT INTO election_info VALUES('$election_id', '$country_id', CURRENT_DATE, 
				  CURRENT_TIME, 1, '$position_id', 1)";
		$conn->query($query);
	}

	/* Activate elections. Update start date/time */
	//get elections that was scheduled 5 (120 hours) days ago.
	$query = "SELECT election_id FROM election_info 
			  WHERE DATE_ADD(TIMESTAMP(start_date, start_time), INTERVAL 24 HOUR) <= NOW()
			  AND ended = 1 AND can_participate = 1";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($elections_id) = $row;
		//activate elections
		$query = "UPDATE election_info SET can_participate = 0 WHERE election_id = '$elections_id'";
		$conn->query($query);
		
		$query = "UPDATE election_info SET ended = 0 WHERE election_id = '$elections_id'";
		$conn->query($query);
		
		//update date/time
		$query = "UPDATE election_info SET start_date = CURRENT_DATE WHERE election_id = '$elections_id'";
		$conn->query($query);
		
		$query = "UPDATE election_info SET start_time = CURRENT_TIME WHERE election_id = '$elections_id'";
		$conn->query($query);
	}
	
	/* executes 20-30% slower, but easier to read/manage
	SELECT * FROM (SELECT cg.country_id, cg.position_id FROM country_government cg, government_term_length gtl WHERE
			  DATE_ADD(elected, INTERVAL (term - 6) DAY) <= CURRENT_DATE
			  AND gtl.country_id = cg.country_id AND cg.position_id = 1
			  AND gtl.position_id = cg.position_id
			  UNION
			  SELECT country_id, 1 AS position_id FROM country c WHERE country_id IN
			 (SELECT country_id FROM country_government WHERE user_id IS NULL)
			  UNION
			  SELECT cd.country_id, gtl.position_id FROM congress_details cd, government_term_length gtl WHERE
			  DATE_ADD(elected, INTERVAL (term - 6) DAY) <= CURRENT_DATE
			  AND gtl.country_id = cd.country_id AND gtl.position_id = 3
			  UNION
			  SELECT country_id, 3 AS position_id FROM country c WHERE country_id NOT IN
			 (SELECT country_id FROM congress_members GROUP BY country_id))
			  AS temp WHERE country_id NOT IN (SELECT country_id FROM election_info WHERE type = 3 AND (ended = 1 AND can_participate = 1) 
			  OR (ended = 0 AND can_participate = 0)) AND country_id IN 
			  (SELECT country_id FROM country WHERE timezone_id = '2')
			  ORDER BY country_id;
	*/

?>