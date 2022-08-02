<?php
	//Description: Check for expired laws and process them. Run every 3 min.

	include('/var/www/html/connect_db.php');
	include('/var/www/html/php_functions/get_time_for_id.php'); //function getTimeForId().
	include('/var/www/html/php_functions/send_notification.php'); //function sendNotification($notification, $user_id).
	
	$query = "SELECT law_id, country_id, responsibility_id, yes, no, proposed_by FROM country_law_info
			  WHERE DATE_ADD(TIMESTAMP(proposed_date, proposed_time), INTERVAL 24 HOUR) <= NOW() AND is_processed = 0";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($law_id, $country_id, $responsibility_id, $yes, $no, $proposed_by) = $row;
		processLaw($law_id, $country_id, $responsibility_id, $yes, $no, $proposed_by);
	}
	
	$query = "SELECT law_id, country_id, responsibility_id, yes, no, proposed_by FROM country_law_info
			  WHERE is_processed = 0";
	$result = $conn->query($query);
	while($row = $result->fetch_row()) {
		list($law_id, $country_id, $responsibility_id, $yes, $no, $proposed_by) = $row;
		$query = "SELECT COUNT(user_id) FROM users_voted_for_laws 
				  WHERE law_id = '$law_id' HAVING COUNT(user_id) >= 
				  CEIL(((SELECT COUNT(user_id) FROM country_government WHERE position_id
				  IN (SELECT position_id FROM government_country_responsibilities 
				  WHERE country_id = '$country_id' AND responsibility_id = '$responsibility_id' AND must_sign_vote = TRUE) 
				  AND country_id = '$country_id') + 
				 (SELECT COUNT(user_id) FROM congress_members WHERE country_id = '$country_id'
				  AND ((SELECT must_sign_vote FROM government_country_responsibilities 
				  WHERE country_id = '$country_id' AND responsibility_id = '$responsibility_id' AND position_id = 3)) = TRUE)) * 0.65)";
		$result_check = $conn->query($query);
		if($result_check->num_rows >= 1) {
			processLaw($law_id, $country_id, $responsibility_id, $yes, $no, $proposed_by);
		}
	}
	
	function processLaw($law_id, $country_id, $responsibility_id, $yes, $no, $proposed_by) {
		global $conn;
		//change gov term length
		if($responsibility_id == 1) {
			if($yes > $no) {
				$query = "SELECT position_id, new_term FROM change_gov_term_length WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($position_id, $new_term) = $row_law;
				
				$query = "UPDATE government_term_length SET term = '$new_term' WHERE country_id = '$country_id' 
						  AND position_id = '$position_id'";
				$conn->query($query);
			}
			setLawToProcessed($law_id);
		}
	
		//join leave union
		else if($responsibility_id == 2) {
			if($yes > $no) {
				$query = "SELECT action, union_id FROM join_leave_union WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($action, $union_id) = $row_law;
				
				if($action == 0) {//leave
					$query = "DELETE FROM country_unions WHERE country_id = '$country_id'";
					$conn->query($query);
					
					//delete from processing_union_application if country is founder and new member wants to join union
					$query = "SELECT law_id FROM country_law_info WHERE responsibility_id = 7 AND country_id = '$country_id'";
					$result_app = $conn->query($query);
					if($result_app->num_rows > 0) {
						while($row_app = $result_app->fetch_row()) {
							list($law_id_app) = $row_app;
							$query = "DELETE FROM processing_union_application WHERE founder_law_id = '$law_id_app'";
							$conn->query($query);
							
							$query = "DELETE FROM users_voted_for_laws WHERE law_id = '$law_id_app'";
							$conn->query($query);
							
							$query = "DELETE FROM country_law_info WHERE law_id = '$law_id_app'";
							$conn->query($query);
						}
					}
					
					//delete union if no members left
					$query = "SELECT COUNT(*) FROM country_unions WHERE union_id = '$union_id'";
					$result_unions = $conn->query($query);
					$row_unions = $result_unions->fetch_row();
					list($members) = $row_unions;
					if($members == 0) {
						$query = "DELETE FROM unions WHERE union_id = '$union_id'";
						$conn->query($query);
					}
				}
				else if ($action == 1) {//join
					//update join_leave_union to "processing application (action = 2)"
					$query = "UPDATE join_leave_union SET action = 2 WHERE law_id = '$law_id'";
					$conn->query($query);
					
					//issue law for all union founders, to accept or decline country
					$query = "SELECT country_id FROM country_unions WHERE union_id = '$union_id' AND is_founder = TRUE";
					$result_founders = $conn->query($query);
					while($row_founders = $result_founders->fetch_row()) {
						list($founder_id) = $row_founders;
						$law_id_founder = getTimeForId() . $founder_id;
						
						//register new law
						$query = "INSERT INTO country_law_info VALUES('$law_id_founder', '$founder_id', 7, 0, 0, 
														  '$proposed_by', CURRENT_DATE, CURRENT_TIME, 0)";
						$conn->query($query);
						
						$query = "INSERT INTO processing_union_application VALUES('$law_id', '$law_id_founder')";
						$conn->query($query);
						
						//inform government
						notifyToVote($founder_id, 7);
					}
				}
			}
			setLawToProcessed($law_id);
		}
		
		//Print money
		else if($responsibility_id == 3) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT pm.amount AS printing_amount, pmc.amount AS stack, pmc.product_amount AS required_products_per_stack, 
						  IFNULL(mp.amount,0) AS available_products, currency_id, pmc.product_id, pm.position_id
						  FROM print_money pm, print_money_cost pmc, ministry_product mp WHERE mp.product_id = pmc.product_id
						  AND mp.country_id = '$country_id' AND law_id = '$law_id' AND mp.position_id = pm.position_id";
				$result_law = $conn->query($query);
				if($result_law->num_rows != 1) {
					return;
				}
				$row_law = $result_law->fetch_row();
				list($printing_amount, $stack, $required_products_per_stack, $available_products, $currency_id, $product_id, $position_id) = $row_law;
				
				$required_products = round(($printing_amount / $stack) * $required_products_per_stack, 2);
				
				if($available_products >= $required_products) {
					//update ministry product table
					$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
							  WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id')
							  AS temp) - '$required_products' WHERE country_id = '$country_id' AND product_id = '$product_id'
							  AND position_id = '$position_id'";
					$conn->query($query);

					//update country_currency table with new currency
					$query = "SELECT * FROM country_currency WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
					$result_law = $conn->query($query);
					if($result_law->num_rows == 1) { 
						$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
								  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp) + '$printing_amount' 
								  WHERE country_id = '$country_id'  AND currency_id = '$currency_id'";
					}
					else {
						$query = "INSERT INTO country_currency VALUES('$country_id', '$currency_id', '$printing_amount')";
					}
					$conn->query($query);
				}
				else {
					$notification = 'Print money law has been failed. Not enough paper in the ministry warehouse.';
					notifyLawFailed($notification, $responsibility_id, $country_id);
				}
			}
		}
		
		//Impeach president.
		else if($responsibility_id == 4) {
			setLawToProcessed($law_id);
			if(($yes + $no) != 0) {
				//determine if more than 65% decided to impeach president.
				$percentage_yes = (100 / ($yes + $no)) * $yes;
				if($percentage_yes >= 65) {
					//notify president
					$query = "SELECT user_id FROM country_government WHERE country_id = '$country_id' AND position_id = 1";
					$result_law = $conn->query($query);
					$notification = "Government decided to impeach you. You\'re not a president anymore.";
					while($row_law = $result_law->fetch_row()) {
						list($president_id) = $row_law;
						sendNotification($notification, $president_id);
					}
					
					//impeach
					$query = "UPDATE country_government SET user_id = NULL WHERE country_id = '$country_id' AND position_id = 1";
					$conn->query($query);
				}
			}
		}
		
		//Dissolve Congress.
		else if($responsibility_id == 5) {
			setLawToProcessed($law_id);
			if(($yes + $no) != 0) {
				//determine if more than 65% decided to dissolve congress.
				$percentage_yes = (100 / ($yes + $no)) * $yes;
				if($percentage_yes >= 65) {
					//notify congress
					$query = "SELECT user_id FROM congress_members WHERE country_id = '$country_id'";
					$result_law = $conn->query($query);
					$notification = "Congress has been dissolved. You\'re not a congressman anymore.";
					while($row_law = $result_law->fetch_row()) {
						list($congressman_id) = $row_law;
						sendNotification($notification, $congressman_id);
					}
					//disslove
					$query = "DELETE FROM congress_members WHERE country_id = '$country_id'";
					$conn->query($query);
				}
			}
		}
		
		//Import permission/embargo.
		else if($responsibility_id == 6) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT from_country_id, sale_tax, product_id, days, action FROM product_import_law_info
						  WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($from_country_id, $sale_tax, $product_id, $days, $action) = $row_law;
				if($action == 0) {//embargo
					$query = "UPDATE product_import_tax SET permission = FALSE WHERE country_id = '$country_id'
							  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
					$conn->query($query);
				}
				else if($action == 1) {//permission
					$query = "SELECT * FROM product_import_tax WHERE country_id = '$country_id'
							  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$query = "UPDATE product_import_tax SET permission = TRUE WHERE country_id = '$country_id'
								  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
						$conn->query($query);
						
						$query = "UPDATE product_import_tax SET sale_tax = '$sale_tax' WHERE country_id = '$country_id'
								  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
						$conn->query($query);
						
						$query = "UPDATE product_import_tax SET days = '$days' WHERE country_id = '$country_id'
								  AND from_country_id = '$from_country_id' AND product_id = '$product_id'";
						$conn->query($query);
					} 
					else {
						$query = "INSERT INTO product_import_tax VALUES('$country_id', '$from_country_id', 
								 '$sale_tax', '$product_id', TRUE, '$days')";
						$conn->query($query);
					}
				}
			}
		}
		
		//Accept/decline new member application to union.
		else if($responsibility_id == 7) {
			setLawToProcessed($law_id);
			//check if all founders finished voting
			$query = "SELECT COUNT(*) FROM country_law_info WHERE law_id IN 
					 (SELECT founder_law_id FROM processing_union_application WHERE applicant_law_id = 
					 (SELECT applicant_law_id FROM processing_union_application WHERE founder_law_id = '$law_id'))
					  AND is_processed = 0";
			$result_law = $conn->query($query);
			$row_law = $result_law->fetch_row();
			list($founders_not_voted) = $row_law;
			$voted_yes = 0;
			$voted_no = 0;
				
			if($founders_not_voted == 0) {//if yes, determine if new member is accepted or not.
				//get country president
				$query = "SELECT c.country_id, union_name, u.union_id,
						  (SELECT as_founder FROM join_leave_union WHERE law_id = 
						  (SELECT applicant_law_id FROM processing_union_application WHERE founder_law_id = '$law_id'))
						  FROM unions u, country_unions cu, country c
						  WHERE u.union_id = cu.union_id AND
						  c.country_id = (SELECT country_id FROM country_law_info WHERE law_id = 
						 (SELECT applicant_law_id FROM processing_union_application WHERE founder_law_id = '$law_id'))
						  AND cu.country_id = (SELECT country_id FROM country_law_info WHERE law_id = '$law_id')";
				$result_president = $conn->query($query);
				$row_president = $result_president->fetch_row();
				list($applicant_id, $union_name, $union_id, $is_founder) = $row_president;
				
				//get information about votes
				$query = "SELECT cli.country_id, country_name, yes, no FROM country_law_info cli, country c WHERE law_id IN 
						 (SELECT founder_law_id FROM processing_union_application WHERE applicant_law_id = 
						 (SELECT applicant_law_id FROM processing_union_application WHERE founder_law_id = '$law_id'))
						  AND is_processed = 1 AND cli.country_id = c.country_id";
				$result_votes = $conn->query($query);
				while($row_votes = $result_votes->fetch_row()) {
					list($country_id, $country_name, $yes, $no) = $row_votes;
					if($yes > $no) {
						$voted_yes++;
						$notification = "$country_name voted for your country\'s application to join $union_name union.";
					}
					else {
						$voted_no++;
						$notification = "$country_name voted against your country\'s application to join $union_name union.";
					}
		
					//notify applicants
					notifyCitizens($notification, $applicant_id);
				}
				
				//determine if more than 65% decided to accept
				$percentage_yes = (100 / ($voted_yes + $voted_no)) * $voted_yes;
				if($percentage_yes >= 65) {
					$query = "INSERT INTO country_unions VALUES('$applicant_id', '$union_id', '$is_founder')";
					$conn->query($query);
					$notification = "Congratulations! Your country is now a member of $union_name union.";
				}
				else {
					$notification = "Only $percentage_yes out of 65% and more decided to accept your country to join union.";
				}
				
				//notify applicants
				notifyCitizens($notification, $applicant_id);
			}
		}
		
		//Change responsibilities.
		else if($responsibility_id == 8) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT position_id, responsibility_id, must_sign_vote, add_remove, can_issue 
						  FROM change_responsibilities WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($position_id, $responsibility_id, $must_sign_vote, $add_remove, $can_issue) = $row_law;
				if($add_remove == 0) {//remove
					if($must_sign_vote == TRUE && $can_issue == TRUE) {
						$query = "DELETE FROM government_country_responsibilities
								  WHERE country_id = '$country_id' AND position_id = '$position_id' 
								  AND responsibility_id = '$responsibility_id'";
						$conn->query($query);		  
					}
					else if ($must_sign_vote == TRUE || $can_issue == TRUE) {
						if($must_sign_vote == TRUE) {
							$query = "UPDATE government_country_responsibilities SET must_sign_vote = FALSE 
									WHERE country_id = '$country_id' AND position_id = '$position_id' 
									AND responsibility_id = '$responsibility_id'";
							$conn->query($query);
						}
						if($can_issue == TRUE) {
							$query = "UPDATE government_country_responsibilities SET have_access = FALSE 
									WHERE country_id = '$country_id' AND position_id = '$position_id' 
									AND responsibility_id = '$responsibility_id'";
							$conn->query($query);
						}
					}
				}
				else if($add_remove == 1) {//add
					//check if had responsibility
					$query = "SELECT * FROM government_country_responsibilities
							  WHERE country_id = '$country_id' AND position_id = '$position_id' 
							  AND responsibility_id = '$responsibility_id'";
					$result_res = $conn->query($query);
					if($result_res->num_rows >= 1) {
						if($must_sign_vote == TRUE) {
							$query = "UPDATE government_country_responsibilities SET must_sign_vote = TRUE 
									  WHERE country_id = '$country_id' AND position_id = '$position_id' 
									  AND responsibility_id = '$responsibility_id'";
							$conn->query($query);
						}
						if($can_issue == TRUE) {
							$query = "UPDATE government_country_responsibilities SET have_access = TRUE 
									  WHERE country_id = '$country_id' AND position_id = '$position_id' 
									  AND responsibility_id = '$responsibility_id'";
							$conn->query($query);
						}	
					}
					else {
						$query = "INSERT INTO government_country_responsibilities VALUES('$country_id', '$position_id', 
						 		 '$responsibility_id', '$must_sign_vote', '$can_issue')";
						$conn->query($query);
					}
				}
			}
		}
	
		//Declare war.
		else if($responsibility_id == 9) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//select info about war
				$query = "SELECT with_country_id FROM declare_war
						  WHERE law_id = '$law_id'";
				$result_war = $conn->query($query);
				$row_war = $result_war->fetch_row();
				list($with_country_id) = $row_war;
				
				//check if allies
				$query = "SELECT * FROM defence_agreements WHERE country_id = '$country_id' AND with_country_id = '$with_country_id'";
				$result_law = $conn->query($query);
				if($result_law->num_rows == 1) { 
					$query = "UPDATE defence_agreements SET is_allies = FALSE WHERE country_id = '$country_id'
							  AND with_country_id = '$with_country_id'";
					$conn->query($query);
				}
				
				//Declare war
				$war_id = $law_id;
				$query = "INSERT INTO country_wars VALUES('$war_id', '$country_id', '$with_country_id', CURRENT_DATE, CURRENT_TIME, 1, 0)";
				$conn->query($query);

				//notify country president to who war was declared
				$query = "SELECT user_id, country_name FROM country_government cg, country c
						  WHERE cg.country_id = '$with_country_id' AND position_id = 1 AND c.country_id = '$country_id'";
				$result_president = $conn->query($query);
				$row_president = $result_president->fetch_row();
				list($president, $country_name) = $row_president;
				
				sendNotification("$country_name declared war to your country!", $president);
			}
		}
		
		//Sign peace treaty.
		else if($responsibility_id == 10) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//select info about war
				$query = "SELECT with_country_id, status FROM sign_peace_treaty
						  WHERE law_id = '$law_id'";
				$result_war = $conn->query($query);
				$row_war = $result_war->fetch_row();
				list($with_country_id, $status) = $row_war;
				
				if($status == 0) {//voted, but not approved by the other side	
					$new_law_id = getTimeForId() . $proposed_by;
					
					//propose/issue law
					$query = "INSERT INTO country_law_info VALUES('$new_law_id', '$with_country_id', '$responsibility_id', 0, 0, 
																  '$proposed_by', CURRENT_DATE, CURRENT_TIME, 0)";
					$conn->query($query);
					
					$query = "INSERT INTO sign_peace_treaty VALUES('$new_law_id', '$country_id', 1)";
					$conn->query($query);
					
					notifyToVote($with_country_id, $responsibility_id);
				}
				else if($status == 1) {//approved by both sides. end war.
					$query = "UPDATE country_wars SET active = FALSE WHERE country_id = '$country_id' AND with_country_id = '$with_country_id'
							  AND active = TRUE";
					$conn->query($query);
					
					$query = "UPDATE country_wars SET active = FALSE WHERE country_id = '$with_country_id' AND with_country_id = '$country_id'
							  AND active = TRUE";
					$conn->query($query);
					
					//notify country presidents
					$query = "SELECT user_id, country_name FROM country_government cg, country c
							  WHERE cg.country_id = '$with_country_id' AND position_id = 1 AND c.country_id = '$country_id'";
					$result_president = $conn->query($query);
					$row_president = $result_president->fetch_row();
					list($president, $country_name) = $row_president;
					sendNotification("$country_name agreed to sign peace treaty with your country!", $president);
					
					$query = "SELECT user_id, country_name FROM country_government cg, country c
							  WHERE cg.country_id = '$country_id' AND position_id = 1 AND c.country_id = '$with_country_id'";
					$result_president = $conn->query($query);
					$row_president = $result_president->fetch_row();
					list($president, $country_name) = $row_president;
					sendNotification("$country_name agreed to sign peace treaty with your country!", $president);
				}
			}
		}
		
		//Assign ministers. //Assign Prime Minister
		else if($responsibility_id == 11 || $responsibility_id == 12) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//select info about assign_ministers_law
				$query = "SELECT user_id, aml.position_id, assign, name FROM assign_ministers_law aml,
						  government_positions gp WHERE law_id = '$law_id' AND gp.position_id = aml.position_id";
				$result_war = $conn->query($query);
				$row_war = $result_war->fetch_row();
				list($new_minister_id, $position_id, $assign, $position_name) = $row_war;
				
				
				
				if($assign == 0) {//fire w/o replacing
					$query = "UPDATE country_government SET user_id = NULL WHERE
							  position_id = '$position_id' AND country_id = '$country_id'";
					$conn->query($query);
				}
				else if($assign == 1) {//assign new minister
					//if minister of different ministry or congress member then remove
					$query = "DELETE FROM congress_members WHERE user_id = '$new_minister_id'";
					$conn->query($query);
					
					$query = "UPDATE country_government SET user_id = NULL WHERE user_id = '$new_minister_id'";
					$conn->query($query);
					
					$query = "UPDATE country_government SET user_id = '$new_minister_id' 
							  WHERE country_id = '$country_id' AND position_id = '$position_id'";
					$conn->query($query);
					
					$query = "UPDATE country_government SET elected = CURRENT_DATE
							  WHERE country_id = '$country_id' AND position_id = '$position_id'";
					$conn->query($query);
					
					sendNotification("You have been assigned as a new $position_name!", $new_minister_id);
				}
			}
		}
	
		//Change taxes.
		else if($responsibility_id == 13) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT tax, product_id, type FROM new_tax_law
						  WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($tax, $product_id, $type) = $row_law;
				if($type == 0) {//income
					$query = "UPDATE income_tax SET tax = '$tax' WHERE country_id = '$country_id'";
					$conn->query($query);
				}
				else if($type == 1) {//product
					$query = "UPDATE product_sale_tax SET sale_tax = '$tax' WHERE country_id = '$country_id'
							  AND product_id = '$product_id'";
					$conn->query($query);
				}
			}
		}
		
		//Travel agreement.
		else if($responsibility_id == 14) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT from_country_id, days, action FROM travel_agreement_law
						  WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($from_country_id, $days, $action) = $row_law;
				if($action == 0) {//ban
					$query = "UPDATE travel_agreement SET permission = FALSE WHERE country_id = '$country_id'
							  AND from_country_id = '$from_country_id'";
					$conn->query($query);
				}
				else if($action == 1) {//allow
					$query = "SELECT * FROM travel_agreement WHERE country_id = '$country_id'
							  AND from_country_id = '$from_country_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						$query = "UPDATE travel_agreement SET permission = TRUE WHERE country_id = '$country_id'
								  AND from_country_id = '$from_country_id'";
						$conn->query($query);
						
						$query = "UPDATE travel_agreement SET days = '$days' WHERE country_id = '$country_id'
								  AND from_country_id = '$from_country_id'";
						$conn->query($query);
					}
					else {
						$query = "INSERT INTO travel_agreement VALUES('$country_id', '$from_country_id', TRUE, '$days')";
						$conn->query($query);
					}
				}
			}
		}
		
		//Give/Ban permission for foreigners to build companies.
		else if($responsibility_id == 15) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT foreign_country_id, price, building_id, action FROM foreign_building_policy_law
						  WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($foreign_country_id, $price, $building_id, $action) = $row_law;
				if($action == 0) {//ban
					$query = "UPDATE foreign_building_policy SET foreigners = FALSE WHERE country_id = '$country_id'
							  AND foreign_country = '$foreign_country_id' AND building_id = '$building_id'";
					$conn->query($query);
				}
				else if($action == 1) {//allow
					$query = "SELECT * FROM foreign_building_policy WHERE country_id = '$country_id' 
							  AND foreign_country = '$foreign_country_id' AND building_id = '$building_id'";
					$result = $conn->query($query);
					if($result->num_rows != 0) {
						$query = "UPDATE foreign_building_policy SET foreigners = TRUE WHERE country_id = '$country_id'
								  AND foreign_country = '$foreign_country_id' AND building_id = '$building_id'";
						$conn->query($query);
						
						$query = "UPDATE foreign_building_policy SET price = '$price' WHERE country_id = '$country_id'
								  AND foreign_country = '$foreign_country_id' AND building_id = '$building_id'";
						$conn->query($query);
					}
					else {
						$query = "INSERT INTO foreign_building_policy VALUES('$country_id', '$building_id', '$price', TRUE, '$foreign_country_id')";
						$conn->query($query);
					}
				}
			}
		}
	
		//Assign new union leader
		else if($responsibility_id == 16) {
			setLawToProcessed($law_id);
			//check if all founders finished voting
			$query = "SELECT COUNT(*) FROM country_law_info WHERE law_id IN 
					 (SELECT founders_law_id FROM processing_union_leader WHERE primary_law_id = 
					 (SELECT primary_law_id FROM processing_union_leader WHERE founders_law_id = '$law_id'))
					  AND is_processed = 0";
			$result_law = $conn->query($query);
			$row_law = $result_law->fetch_row();
			list($founders_not_voted) = $row_law;
			$voted_yes = 0;
			$voted_no = 0;

			if($founders_not_voted == 0) {//if yes, determine if new leader is assigned.
				//get info about new leader
				$query = "SELECT nul.user_id, user_name, nul.union_id, union_name FROM new_union_leader nul, unions u, users up
						  WHERE law_id = (SELECT primary_law_id FROM processing_union_leader WHERE founders_law_id = '$law_id')
						  AND u.union_id = nul.union_id AND up.user_id = nul.user_id";
				$result_leader = $conn->query($query);
				$row_leader = $result_leader->fetch_row();
				list($new_leader_id, $new_leader_name, $union_id, $union_name) = $row_leader;
				
				//inform countries founders government
				$president_country = array();
				$x = 0;
				$query = "SELECT user_id, cu.country_id FROM country_government cg, country_unions cu, government_country_responsibilities gcr
						  WHERE cg.country_id = cu.country_id 
						  AND union_id = '$union_id' AND is_founder = TRUE
						  AND responsibility_id = '16' AND must_sign_vote = true AND gcr.country_id = cu.country_id 
						  AND gcr.position_id = cg.position_id AND user_id IS NOT NULL
						  UNION
						  SELECT user_id, country_id FROM congress_members WHERE country_id IN 
						 (SELECT country_id FROM government_country_responsibilities WHERE country_id IN 
						 (SELECT country_id FROM country_unions 
						  WHERE union_id = '$union_id' AND is_founder = TRUE) 
						  AND responsibility_id = '16' AND must_sign_vote = true AND position_id = 3)";
				$result_president = $conn->query($query);
				while($row_president = $result_president->fetch_row()) {
					list($president, $country_founder) = $row_president;
					$president_country[$x][0] = $president;
					$president_country[$x][1] = $country_founder;
					$x++;
				}

				//get information about votes
				$query = "SELECT cli.country_id, country_name, yes, no FROM country_law_info cli, country c WHERE law_id IN 
						 (SELECT founders_law_id FROM processing_union_leader WHERE primary_law_id = 
						 (SELECT primary_law_id FROM processing_union_leader WHERE founders_law_id = '$law_id'))
						  AND is_processed = TRUE AND cli.country_id = c.country_id";
				$result_votes = $conn->query($query);
				while($row_votes = $result_votes->fetch_row()) {
					list($country_id, $country_name, $yes, $no) = $row_votes;
					if($yes > $no) {
						$voted_yes++;
						$notification = "$country_name voted for new union leader $new_leader_name.";
					}
					else {
						$voted_no++;
						$notification = "$country_name voted against new union leader $new_leader_name.";
					}
					for($x = 0; $x < count($president_country); $x++) {
						if($president_country[$x][1] != $country_id) {
							sendNotification($notification, $president_country[$x][0]);
						}
					}
				}
			
				//determine if more than 75% decided to assign new leader.
				$percentage_yes = (100 / ($voted_yes + $voted_no)) * $voted_yes;
				if($percentage_yes >= 75) {
					$query = "UPDATE unions SET leader = '$new_leader_id' WHERE union_id = '$union_id'";
					$conn->query($query);
					sendNotification("Congratulations! You have been elected for union leader of $union_name union.", $new_leader_id);
				}
				else {
					sendNotification("Only $percentage_yes out of 75% and more decided to assign you new union leader.", $new_leader_id);
				}
			}
		}
		
		//Change government salaries.
		else if($responsibility_id == 17) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT position_id, salary FROM change_gov_salary WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($position_id, $salary) = $row_law;
				if($position_id == 3) {//congress
					$query = "UPDATE congress_details SET salary = '$salary' WHERE country_id = '$country_id'";
				}
				else {
					$query = "UPDATE country_government SET salary = '$salary' WHERE country_id = '$country_id' 
							  AND position_id = '$position_id'";
				}
				$conn->query($query);
			}
		}
		
		//Change credit/deposit rate
		else if($responsibility_id == 18) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT rate, type, credit_deposit_type FROM change_credit_deposit_rate WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($rate, $type, $credit_deposit_type) = $row_law;
				if($type == 'gold' && $credit_deposit_type == 'credit') {
					$query = "UPDATE bank_details SET gold_credit_rate = '$rate' WHERE country_id = '$country_id'";
				}
				else if($type == 'currency' && $credit_deposit_type == 'credit') {
					$query = "UPDATE bank_details SET currency_credit_rate = '$rate' WHERE country_id = '$country_id'";
				}
				else if($type == 'gold' && $credit_deposit_type == 'deposit') {
					$query = "UPDATE bank_details SET gold_deposit_rate = '$rate' WHERE country_id = '$country_id'";
				}
				else if($type == 'currency' && $credit_deposit_type == 'deposit') {
					$query = "UPDATE bank_details SET currency_deposit_rate = '$rate' WHERE country_id = '$country_id'";
				}
				$conn->query($query);
			}
		}
		
		//Create new union
		else if($responsibility_id == 19) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//select info about union
				$query = "SELECT union_id, union_name, abbreviation, leader, color, country_id 
						  FROM create_new_union cnu, country_law_info cli
						  WHERE cnu.law_id = '$law_id' AND cli.law_id = cnu.law_id";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($union_id, $union_name, $abbreviation, $leader, $color, $country_id) = $row_law;
				
				//create union
				$query = "INSERT INTO unions VALUES('$union_id', '$union_name', '$abbreviation', '$leader', NULL, NULL, '$color')";
				$conn->query($query);
				
				$query = "INSERT INTO country_unions VALUES('$country_id', '$union_id', 1)";
				$conn->query($query);
				
				//notify country president
				$query = "SELECT user_id FROM country_government WHERE country_id = '$country_id' AND position_id = 1";
				$result_president = $conn->query($query);
				$row_president = $result_president->fetch_row();
				list($president) = $row_president;
				sendNotification("Congratulations! Your country is now a founder of a $union_name union.", $president);
			}
		}
		
		//Assign new Secretary of the Treasury
		else if($responsibility_id == 20) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT user_id, salary FROM assign_bank_manager WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($new_manager, $salary) = $row_law;
				
				if($new_manager != 0) {
					//check if still citizen of the country
					$query = "SELECT * FROM user_profile WHERE user_id = '$new_manager' AND citizenship = '$country_id'";
					$result = $conn->query($query);
					if($result->num_rows != 1) { 
						return;
					}
					
					//get old manager
					$query = "SELECT user_id FROM bank_details WHERE country_id = '$country_id'";
					$result_law = $conn->query($query);
					$row_law = $result_law->fetch_row();
					list($old_manager) = $row_law;
				
					//assign new bank manager
					$query = "UPDATE bank_details SET user_id = '$new_manager' WHERE country_id = '$country_id'";
					$conn->query($query);
					
					$query = "UPDATE bank_details SET manager_from_date = CURRENT_DATE WHERE country_id = '$country_id'";
					$conn->query($query);
					
					$query = "UPDATE bank_details SET manager_salary = '$salary' WHERE country_id = '$country_id'";
					$conn->query($query);
					
					$notification = "You have been fired from Secretary of the Treasury position.";
					sendNotification($notification, $old_manager);
					
					$notification = "You have been assigned to the Secretary of the Treasury position.";
					sendNotification($notification, $new_manager);
				}
				else {
					//get old manager
					$query = "SELECT user_id FROM bank_details WHERE country_id = '$country_id'";
					$result_law = $conn->query($query);
					$row_law = $result_law->fetch_row();
					list($old_manager) = $row_law;
					
					//fire old bank manager
					$query = "UPDATE bank_details SET user_id = NULL WHERE country_id = '$country_id'";
					$conn->query($query);
					
					$notification = "You have been fired from Secretary of the Treasury position.";
					sendNotification($notification, $old_manager);
				}
			}
		}
		
		//Change production taxes
		else if($responsibility_id == 21) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT tax, product_id FROM new_production_tax_law WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($tax, $product_id) = $row_law;
				
				$query = "UPDATE product_production_tax SET tax = '$tax' WHERE country_id = '$country_id'
						  AND product_id = '$product_id'";
				$conn->query($query);
			}
		}
		
		//Budget Allocation.
		else if($responsibility_id == 22) {
			setLawToProcessed($law_id);
			$query = "SELECT position_id, currency_id, amount FROM budget_allocation WHERE law_id = '$law_id'";
			$result_law = $conn->query($query);
			$row_law = $result_law->fetch_row();
			list($position_id, $currency_id, $amount) = $row_law;
			if($yes > $no) {
				//determine if ministry already have this currency type and insert/update
				$query = "SELECT * FROM ministry_budget WHERE country_id = '$country_id' AND currency_id = '$currency_id'
						  AND position_id = '$position_id'";
				$result_law = $conn->query($query);
				if($result_law->num_rows == 1) { 
					//update
					$query = "UPDATE ministry_budget SET amount = (SELECT * FROM (SELECT amount FROM ministry_budget 
							  WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id') AS temp) 
							  + '$amount' WHERE country_id = '$country_id' AND currency_id = '$currency_id' AND position_id = '$position_id'";
				}
				else {
					//insert
					$query = "INSERT INTO ministry_budget VALUES('$country_id', '$position_id', '$currency_id', '$amount')";
				}
				
				$conn->query($query);
			}
			else {
				//update country budget
				$query = "UPDATE country_currency SET amount = (SELECT * FROM (SELECT amount FROM country_currency 
						  WHERE country_id = '$country_id' AND currency_id = '$currency_id') AS temp)
						  + '$amount' WHERE country_id = '$country_id' AND currency_id = '$currency_id'";
				$conn->query($query);
			}
		}
		
		//Sign defence agreement.
		else if($responsibility_id == 24) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//select info about war
				$query = "SELECT with_country_id, days, status FROM sign_defence_agreement
						  WHERE law_id = '$law_id'";
				$result_war = $conn->query($query);
				$row_war = $result_war->fetch_row();
				list($with_country_id, $days, $status) = $row_war;
				
				if($status == 0) {//voted, but not approved by the other side	
					$new_law_id = getTimeForId() . $proposed_by;
					
					//propose/issue law
					$query = "INSERT INTO country_law_info VALUES('$new_law_id', '$with_country_id', '$responsibility_id', 0, 0, 
																  '$proposed_by', CURRENT_DATE, CURRENT_TIME, 0)";
					$conn->query($query);
					
					$query = "INSERT INTO sign_defence_agreement VALUES('$new_law_id', '$country_id', '$days', 1)";
					$conn->query($query);
					
					notifyToVote($with_country_id, $responsibility_id);
				}
				else if($status == 1) {//approved by both sides. sign defence agreement.
					$query = "SELECT * FROM defence_agreements WHERE country_id = '$country_id'
							  AND with_country_id = '$with_country_id'";
					$result = $conn->query($query);
					if($result->num_rows == 1) {
						//Sign agreement
						$query = "UPDATE defence_agreements SET is_allies = TRUE WHERE country_id = '$country_id'
								  AND with_country_id = '$with_country_id'";
						$conn->query($query);
						
						$query = "UPDATE defence_agreements SET days = '$days' WHERE country_id = '$country_id'
								  AND with_country_id = '$with_country_id'";
						$conn->query($query);

						//Sign agreement
						$query = "UPDATE defence_agreements SET is_allies = TRUE WHERE country_id = '$with_country_id'
								  AND with_country_id = '$country_id'";
						$conn->query($query);
						
						$query = "UPDATE defence_agreements SET days = '$days' WHERE country_id = '$with_country_id'
								  AND with_country_id = '$country_id'";
						$conn->query($query);
					}
					else {
						$query = "INSERT INTO defence_agreements VALUES('$country_id', '$with_country_id', '$days', TRUE)";
						$conn->query($query);
						
						$query = "INSERT INTO defence_agreements VALUES('$with_country_id', '$country_id', '$days', TRUE)";
						$conn->query($query);
					}
					//notify country presidents
					$query = "SELECT user_id, country_name FROM country_government cg, country c
							  WHERE cg.country_id = '$with_country_id' AND position_id = 1 AND c.country_id = '$country_id'";
					$result_president = $conn->query($query);
					$row_president = $result_president->fetch_row();
					list($president, $country_name) = $row_president;
					sendNotification("$country_name agreed to sign defense agreement with your country!", $president);
					
					$query = "SELECT user_id, country_name FROM country_government cg, country c
							  WHERE cg.country_id = '$country_id' AND position_id = 1 AND c.country_id = '$with_country_id'";
					$result_president = $conn->query($query);
					$row_president = $result_president->fetch_row();
					list($president, $country_name) = $row_president;
					sendNotification("$country_name agreed to sign defense agreement with your country!", $president);
				}
			}
		}
		
		//Product Allocation.
		else if($responsibility_id == 25) {
			setLawToProcessed($law_id);
			$query = "SELECT position_id, product_id, amount FROM product_allocation WHERE law_id = '$law_id'";
			$result_law = $conn->query($query);
			$row_law = $result_law->fetch_row();
			list($position_id, $product_id, $amount) = $row_law;
			if($yes > $no) {
				//determine if ministry already have this currency type and insert/update
				$query = "SELECT * FROM ministry_product WHERE country_id = '$country_id' AND product_id = '$product_id'
						  AND position_id = '$position_id'";
				$result_law = $conn->query($query);
				if($result_law->num_rows == 1) { 
					//update
					$query = "UPDATE ministry_product SET amount = (SELECT * FROM (SELECT amount FROM ministry_product 
							  WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id') AS temp) 
							  + '$amount' WHERE country_id = '$country_id' AND product_id = '$product_id' AND position_id = '$position_id'";
				}
				else {
					//insert
					$query = "INSERT INTO ministry_product VALUES('$country_id', '$position_id', '$product_id', '$amount')";
				}
				
				$conn->query($query);
			}
			else {
				//update country budget
				$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product 
						  WHERE country_id = '$country_id' AND product_id = '$product_id') AS temp)
						  + '$amount' WHERE country_id = '$country_id' AND product_id = '$product_id'";
				$conn->query($query);
			}
		}
		
		//Change timezone.
		else if($responsibility_id == 26) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT timezone_id FROM change_timezone WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($timezone_id) = $row_law;
				$query = "UPDATE country SET timezone_id = '$timezone_id' WHERE country_id = '$country_id'";
				$conn->query($query);
			}
		}
		
		//Price change to build companies for citizens.
		else if($responsibility_id == 27) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT price, building_id FROM building_policy_law WHERE law_id = '$law_id'";
				$result_law = $conn->query($query);
				$row_law = $result_law->fetch_row();
				list($price, $building_id) = $row_law;
				$query = "UPDATE building_policy SET price = '$price' WHERE country_id = '$country_id'
						  AND building_id = '$building_id'";
				$conn->query($query);
			}
		}
		
		//Expel country from the union
		else if($responsibility_id == 29) {
			setLawToProcessed($law_id);
			$query = "SELECT action, rcu.country_id, union_id, country_name
					  FROM remove_country_union rcu, country c WHERE law_id = '$law_id' AND c.country_id = rcu.country_id";
			$result_law = $conn->query($query);
			$row_law = $result_law->fetch_row();
			list($action, $remove_country_id, $union_id, $remove_country_name) = $row_law;
			
			if($action == 1) {
				if($yes > $no) {
					//update remove_country_union to "processing (action = 2)"
					$query = "UPDATE remove_country_union SET action = 2 WHERE law_id = '$law_id'";
					$conn->query($query);
					
					//issue law for all union founders, to accept or decline country
					$query = "SELECT country_id FROM country_unions WHERE union_id = '$union_id' AND is_founder = TRUE
							  AND country_id != '$country_id'";
					$result_founders = $conn->query($query);
					if($result_founders->num_rows == 0) {
						expelCountryFromUnion($union_id, $remove_country_name, $remove_country_id);
					}
					else {
						while($row_founders = $result_founders->fetch_row()) {
							list($founder_id) = $row_founders;
							$law_id_founder = getTimeForId() . $founder_id;
							
							//register new law
							$query = "INSERT INTO country_law_info VALUES('$law_id_founder', '$founder_id', 29, 0, 0, 
									 '$proposed_by', CURRENT_DATE, CURRENT_TIME, 0)";
							$conn->query($query);
							
							$query = "INSERT INTO remove_country_union_application VALUES('$law_id', '$law_id_founder')";
							$conn->query($query);
							
							$action = 2;//processing
							$query = "INSERT INTO remove_country_union VALUES('$law_id_founder', '$remove_country_id', 
									 '$union_id', '$action')";
							$conn->query($query);
							
							//inform government
							notifyToVote($founder_id, 29);
						}
					}
				}
			}
			else {//process
				expelCountryFromUnion($union_id, $remove_country_name, $remove_country_id);
			}
		}
		
		//Welcome message
		else if($responsibility_id == 30) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT heading, message FROM change_welcome_message WHERE law_id = '$law_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($heading, $message) = $row;
				
				$query = "SELECT * FROM welcome_message WHERE country_id = '$country_id'";
				$result = $conn->query($query);
				if($result->num_rows != 1) {
					$query = "INSERT INTO welcome_message VALUES ('$country_id', '$heading', '$message')";
					$conn->query($query);
				}
				else {
					$query = "UPDATE welcome_message SET heading = '$heading' WHERE country_id = '$country_id'";
					$conn->query($query);
					
					$query = "UPDATE welcome_message SET message = '$message' WHERE country_id = '$country_id'";
					$conn->query($query);
				}
			}
		}
		
		//Assign titles
		else if($responsibility_id == 31) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				$query = "SELECT u.user_id, user_name, t.title_id, action, title FROM users u, assign_titles at, titles t
						  WHERE u.user_id = at.user_id AND t.title_id = at.title_id AND law_id = '$law_id'";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($user_id, $user_name, $title_id, $action, $title) = $row;
				if($action == 'remove') {
					$query = "DELETE FROM user_titles WHERE user_id = '$user_id'";
					$conn->query($query);
					
					$notification = "Unfortunately, the government has decided to take away \'$title\' title from you.";
					sendNotification($notification, $user_id);
				}
				else {
					$query = "SELECT * FROM user_titles WHERE user_id = '$user_id'";
					$result = $conn->query($query);
					if($result->num_rows == 0) {
						$query = "INSERT INTO user_titles VALUES ('$user_id', '$title_id')";
						$conn->query($query);
					}
					else {
						$query = "UPDATE user_titles SET title_id = '$title_id' WHERE user_id = '$user_id'";
						$conn->query($query);
					}
					
					$notification = "Congratulations, you have been assigned \'$title\' title!";
					sendNotification($notification, $user_id);
				}
			}
		}
		
		//Change Capital Region
		else if($responsibility_id == 34) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//get new capital
				$query = "SELECT region_id FROM change_capital_region WHERE law_id = '$law_id'";
				$result_war = $conn->query($query);
				$row_war = $result_war->fetch_row();
				list($new_capital_region_name) = $row_war;
				
				//set new capital
				$query = "UPDATE country SET capital = '$new_capital_region_name' WHERE country_id = '$country_id'";
				$conn->query($query);
			}
		}
		
		//Change Region Owner
		else if($responsibility_id == 35) {
			setLawToProcessed($law_id);
			if($yes > $no) {
				//get details
				$query = "SELECT region_id, country_id, action, price FROM change_region_owner WHERE law_id = '$law_id'";
				$result_war = $conn->query($query);
				$row_war = $result_war->fetch_row();
				list($region_id, $target_country_id, $action, $price) = $row_war;
				
				if($action == 1) {
					$new_law_id = getTimeForId() . $target_country_id;
					
					//register new law
					$query = "INSERT INTO country_law_info VALUES('$new_law_id', '$target_country_id', 35, 0, 0, 
													  '$proposed_by', CURRENT_DATE, CURRENT_TIME, FALSE)";
					$conn->query($query);
					
					$query = "INSERT INTO change_region_owner VALUES('$new_law_id', '$country_id', '$region_id',
							 '$price', 2)";
					$conn->query($query);
					
					notifyToVote($target_country_id, $responsibility_id);
				}
				else if ($action == 2) {
					$product_id = 1;//gold
					
					//details
					$query = "SELECT region_name, rightful_owner, country_name, price FROM regions r, country c, change_region_owner cro
							  WHERE law_id = '$law_id' AND c.country_id = cro.country_id AND r.region_id = cro.region_id";
					$result = $conn->query($query);
					$row = $result->fetch_row();
					list($region_name, $rightful_owner, $country_name, $price) = $row;
					
					//if not rightful owner, get fee.
					$fee = 0;
					if($country_id != $rightful_owner) {
						$fee = 8;
					}
					$total = $price + $fee;
					
					//check if enough gold in the warehouse
					if($total > 0) {
						$query = "SELECT * FROM country_product WHERE country_id = '$country_id' AND product_id = '$product_id'
								  AND amount >= '$total'";
						$result = $conn->query($query);
						if($result->num_rows != 1) {
							//notify users
							$notification = "Your country did not had enough Gold in order to buy $region_name region for 
											 $total Gold (region price: $price Gold, additional fee: $fee Gold)
											 from $country_name.";
							notifyCitizens($notification, $country_id);
							
							return;
						}
						
						//get gold
						$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
								  country_id = '$country_id' AND product_id = '$product_id') AS temp) - '$total'
								  WHERE country_id = '$country_id' AND product_id = '$product_id'";
						$conn->query($query);
						
						//receive gold
						$query = "SELECT * FROM country_product WHERE country_id = '$target_country_id' AND product_id = '$product_id'";
						$result = $conn->query($query);
						if($result->num_rows == 1) {
							$query = "UPDATE country_product SET amount = (SELECT * FROM (SELECT amount FROM country_product WHERE
									  country_id = '$target_country_id' AND product_id = '$product_id') AS temp) + '$price'
									  WHERE country_id = '$target_country_id' AND product_id = '$product_id'";
						}
						else {
							$query = "INSERT INTO country_product VALUES ('$target_country_id', '$product_id', '$price')";
						}
						$conn->query($query);
					}
					
					//set new owner
					$query = "UPDATE regions SET country_id = '$country_id' WHERE region_id = '$region_id'";
					$conn->query($query);
					
					//notify users
					$notification = "Congratulations! Your country bought $region_name region for $total Gold
									 (region price: $price Gold, additional fee: $fee Gold) from $country_name.";
					notifyCitizens($notification, $country_id);
					
					return;
				}
			}
		}
	}
	
	function notifyCitizens($notification, $country_id) {
		global $conn;
		
		$query = "SELECT user_id FROM user_profile WHERE citizenship = '$country_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($user_id) = $row;
			sendNotification($notification, $user_id);
		}
	}
	
	function expelCountryFromUnion($union_id, $remove_country_name, $remove_country_id) {
		global $conn;
		global $law_id;
		global $responsibility_id;
		//check if all founders finished voting
		$query = "SELECT COUNT(*) FROM country_law_info WHERE law_id IN 
				 (SELECT founder_law_id FROM remove_country_union_application WHERE applicantion_law_id = 
				 (SELECT applicantion_law_id FROM remove_country_union_application WHERE founder_law_id = '$law_id'))
				  AND is_processed = 0";
		$result_law = $conn->query($query);
		$row_law = $result_law->fetch_row();
		list($founders_not_voted) = $row_law;
		$voted_yes = 0;
		$voted_no = 0;
		
		if($founders_not_voted == 0) {//if yes, determine if new member is accepted or not.
			//notify all governors with access in all countries union members
			$query = "SELECT user_id FROM country_government WHERE country_id IN
					 (SELECT country_id FROM country_unions WHERE union_id = '$union_id') AND position_id IN 
					 (SELECT position_id FROM government_country_responsibilities WHERE country_id IN
					 (SELECT country_id FROM country_unions WHERE union_id = '$union_id')
					  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true)
					  UNION
					  SELECT user_id FROM congress_members WHERE country_id IN 
					 (SELECT country_id FROM government_country_responsibilities WHERE country_id IN
					 (SELECT country_id FROM country_unions WHERE union_id = '$union_id')
					  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true AND position_id = 3)";
			$result = $conn->query($query);
			$governors = array();
			$x = 0;
			while($row = $result->fetch_row()) {
				list($governor_id) = $row;
				$governors[$x++] = $governor_id;
			}
		
			//get information about votes
			$query = "SELECT cli.country_id, country_name, yes, no FROM country_law_info cli, country c WHERE (law_id IN 
					 (SELECT applicantion_law_id FROM remove_country_union_application WHERE founder_law_id = '$law_id')
					  OR law_id = '$law_id')
					  AND is_processed = TRUE AND cli.country_id = c.country_id";
			$result_votes = $conn->query($query);
			while($row_votes = $result_votes->fetch_row()) {
				list($country_id, $country_name, $yes, $no) = $row_votes;
				if($yes > $no) {
					$voted_yes++;
					$notification = "$country_name voted for the proposition to expel $remove_country_name from
									 the union.";
				}
				else {
					$voted_no++;
					$notification = "$country_name voted against the proposition to expel $remove_country_name from
									 the union.";
				}
				for($x = 0; $x < count($governors); $x++) {
					sendNotification($notification, $governors[$x]);
				}
			}
			
			//determine if more than 65% decided to expel
			$percentage_yes = (100 / ($voted_yes + $voted_no)) * $voted_yes;
			if($percentage_yes >= 65) {
				$query = "DELETE FROM country_unions WHERE country_id = '$remove_country_id'";
				$conn->query($query);
				
				$notification = "$remove_country_name was expeled from the union";
			}
			else {
				$notification = "$remove_country_name was not expeled from the union";
			}
			for($x = 0; $x < count($governors); $x++) {
				sendNotification($notification, $governors[$x]);
			}
		}
	}
	
	function notifyLawFailed($notification, $responsibility_id, $country_id) {
		global $conn;
		//notify everyone who have access sign/vote to the law
		$query = "SELECT user_id FROM country_government WHERE country_id = '$country_id' AND position_id IN 
				 (SELECT position_id FROM government_country_responsibilities WHERE country_id = '$country_id' 
				  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true)
				  UNION
				  SELECT user_id FROM congress_members WHERE country_id = 
				 (SELECT country_id FROM government_country_responsibilities WHERE country_id = '$country_id'
				  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true AND position_id = 3)";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($governor_id) = $row;
			$notification = $notification;
			sendNotification($notification, $governor_id);
		}
	}
	
	function setLawToProcessed($law_id) {
		global $conn;
		$query = "UPDATE country_law_info SET is_processed = 1 WHERE law_id = '$law_id'";
		$conn->query($query);
	}
	
	function notifyToVote($country_id, $responsibility_id) {
		global $conn;
		//notify everyone who must sign/vote for the law
		$query = "SELECT user_id FROM country_government WHERE country_id = '$country_id' AND position_id IN 
				 (SELECT position_id FROM government_country_responsibilities WHERE country_id = '$country_id' 
				  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true)
				  UNION
				  SELECT user_id FROM congress_members WHERE country_id = 
				 (SELECT country_id FROM government_country_responsibilities WHERE country_id = '$country_id'
				  AND responsibility_id = '$responsibility_id' AND must_sign_vote = true AND position_id = 3)";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($governor_id) = $row;
			$notification = "A new law has been proposed.";
			sendNotification($notification, $governor_id);
		}
	}
?>