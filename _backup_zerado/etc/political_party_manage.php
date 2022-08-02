<?php
	//Description: Create or dissolve party.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/send_notification.php'); //function sendNotification($notification, $user_id)
	include('../php_functions/upload_image.php'); //uploadImage($image_id, $folder_path, $old_path = NULL, $width = 400, $height = 200)
	include('../php_functions/string_validate.php'); //stringValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/get_user_level.php');//getUserLevel($user_id); returns user level
	
	$name =  htmlentities(stripslashes(strip_tags(trim($_POST['name']))), ENT_QUOTES);
	$new_leader =  htmlentities(stripslashes(trim($_POST['new_leader'])), ENT_QUOTES);
	$description =  htmlentities(stripslashes(strip_tags(trim($_POST['description']))), ENT_QUOTES);
	$member_id =  htmlentities(stripslashes(trim($_POST['member_id'])), ENT_QUOTES);
	$party_id =  htmlentities(stripslashes(trim($_POST['party_id'])), ENT_QUOTES);
	$action =  htmlentities(stripslashes(trim($_POST['action'])), ENT_QUOTES);

	$user_id = $_SESSION['user_id'];
	
	if($action == "create") {
		//check if already in a party/admin of a party(always will be a member)
		$query = "SELECT * FROM party_members WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You already have a party.");
		}
		
		//check if already a leader of a party
		$query = "SELECT * FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You already have a party.");
		}
		
		stringValidate($name, 1, 20, 'Party name');
		stringValidate($description, 0, 350, 'Description');
		
		//check if name not duplicate
		if(!empty($name)) {
			$query = "SELECT * FROM political_parties WHERE party_name = '$name'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Party with this name already exist.");
			}
		}
		
		$party_id = getTimeForId() . $user_id;
		
		if(file_exists($_FILES['image']['tmp_name'])) {//upload image
			$flag = uploadImage($party_id, '../party_flags');
			if($flag == 1) {
				exit("0|File is not an image.");
			}
			if($flag == 2) {
				exit("0|Sorry, your file is too large.");
			}
			if($flag == 3) {
				exit("0|Sorry, only JPG, JPEG, PNG files are allowed.");
			}
			if($flag == 4) {
				exit("0|Image not uploaded. Please try again");
			}
		}
		else {
			$flag = null;
		}

		$query = "INSERT INTO political_parties VALUES('$party_id', '$name', '$description', '$user_id', '$flag')";
		if($conn->query($query)) {
			$query = "INSERT INTO party_members VALUES('$party_id', '$user_id')";
			$conn->query($query);
			
			echo "1|You have successfully created new party.|";

			$query = "SELECT pp.party_id, party_name, description, leader, flag, user_name, COUNT(pm.party_id), COUNT(pa.party_id)
					  FROM political_parties pp, users u, party_members pm LEFT JOIN party_applications pa ON pa.party_id = pm.party_id
					  WHERE leader = '$user_id' AND u.user_id = pp.leader AND pm.party_id = pp.party_id GROUP BY pp.party_id";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($party_id, $party_name, $description, $leader, $flag, $leader_name, $members, $applications) = $row;	
			echo "\n\t\t" . '<div id="party_div">' .
				 "\n\t\t\t" . '<p id="party_id" hidden>' . $party_id . '</p>' .
				 "\n\t\t\t" . '<img id="party_flag" src="../party_flags/' . $flag . '">' .
				 "\n\t\t\t" . '<p id="party_name">' . $party_name . '</p>' .
				 "\n\t\t\t" . '<a id="party_leader" href="profile.php?id=' . $leader . '" target="_new">Party leader: <i>' . $leader_name . 
							  '</i></a>' .
				 "\n\t\t\t" . '<p id="party_desc">' . $description . '</p>';
					 
				
			//party applications
			echo "\n\t\t\t" . '<p id="party_app_head">Party applications: </p>' .
				 "\n\t\t\t" . '<p class="button blue" id="view_applications">View(' . $applications . ')</p>';
				
			//congress elections
			echo "\n\t\t\t" . '<p id="elections_head">Congress elections info: </p>' .
				 "\n\t\t\t" . '<p class="button blue" id="view_elections_info">Details</p>';
				
			if($user_id == $leader) {//founder/leader of the party. has admin rights
				//upload/change image
			
				//edit party
				echo "\n\t\t\t" . '<p class="button blue" id="edit_party">Edit</p>';
			
				//dissolve
				echo "\n\t\t\t" . '<p class="button red" id="dissolve_party">Dissolve</p>';	
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
					 "\n\t\t\t\t" . '<a class="member_name" href="profile.php?id=' . $member_id . '" target="_new">' . $member_name . '</a>';
					 
				if($user_id == $leader && $member_id != $user_id) {//founder/leader of the party. has admin rights
					//kick
					echo "\n\t\t\t" . '<p class="button red kick_member" id="' . $member_id . '">Kick</p>';
					
				}	 
			 
				echo "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
		}
		else {
			if(file_exists($_FILES['fileToUpload']['tmp_name'])) {
				unlink('../party_flags/' . $flag);
			}
		}
	}
	else if($action == "disband") {	
		//check user's party
		$query = "SELECT party_id, flag FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have any party to disband.");
		}
		$row = $result->fetch_row();
		list($party_id, $flag) = $row;
		
		if(!empty($flag)) {
			unlink('../party_flags/' . $flag);
		}
		
		//disband
		$query = "DELETE FROM party_congress_candidates WHERE party_id = '$party_id'";
		$conn->query($query);
		
		$query = "DELETE FROM congress_elections WHERE party_id = '$party_id'";
		$conn->query($query);
		
		$query = "DELETE FROM party_members WHERE party_id = '$party_id'";
		$conn->query($query);
		
		$query = "DELETE FROM party_applications WHERE party_id = '$party_id'";
		$conn->query($query);
		
		$query = "DELETE FROM users_voted WHERE party_id = '$party_id'";
		$conn->query($query);
		
		$query = "DELETE FROM political_parties WHERE party_id = '$party_id'";
		$conn->query($query);
		
		echo "1|Your party have been successfully disband.";
	}
	else if($action == "edit") {
		//get users party
		$query = "SELECT party_id, flag FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have a party.");
		}
		$row = $result->fetch_row();
		list($party_id, $flag) = $row;
		
		stringValidate($name, 1, 20, 'Party name');
		stringValidate($description, 0, 350, 'Description');
		
		//check if name not duplicate
		if(!empty($name)) {
			$query = "SELECT * FROM political_parties WHERE party_name = '$name' AND party_id != '$party_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Party with this name already exist.");
			}
		}
		
		//leader of the party must have same citizenship and must not be a leader of existing party
		if(!empty($new_leader)) {
			if(!filter_var($new_leader, FILTER_VALIDATE_INT)) {
				exit("0|User doesn't exist.");
			}
			
			//check if user is not already a leader of a party
			$query = "SELECT * FROM political_parties WHERE leader = '$new_leader'";
			$result = $conn->query($query);
			if($result->num_rows != 0) {
				exit("0|This user is already a leader of an party.");
			}
			
			//new leader must have same citizenship as current leader
			$query = "SELECT user_id FROM user_profile WHERE citizenship = 
					 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND user_id = '$new_leader'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|A new leader must have the same citizenship as you.");
			}
		}
		
		//update image
		if(file_exists($_FILES['image']['tmp_name'])) {//upload image
			if(!empty($flag)) {
				$flag = uploadImage($party_id, '../party_flags', '../party_flags/' . $flag);
			}
			else {
				$flag = uploadImage($party_id, '../party_flags');
			}
			
			if($flag == 1) {
				exit("0|File is not an image.");
			}
			if($flag == 2) {
				exit("0|Sorry, your file is too large.");
			}
			if($flag == 3) {
				exit("0|Sorry, only JPG, JPEG, PNG files are allowed.");
			}
			if($flag == 4) {
				exit("0|Image not uploaded. Please try again");
			}
			
			$query = "UPDATE political_parties SET flag = '$flag' WHERE party_id = '$party_id'";
			$conn->query($query);
			
			$note = "In order to see updated image right away, refresh the page with Ctrl+F5.";
		}
		else {
			$flag = null;
		}
		
		//update name
		if(!empty($name)) {
			$query = "UPDATE political_parties SET party_name = '$name' WHERE party_id = '$party_id'";
			$conn->query($query);
		}
		
		//update description
		if(!empty($description)) {
			$query = "UPDATE political_parties SET description = '$description' WHERE party_id = '$party_id'";
			$conn->query($query);
		}
		
		if(!empty($new_leader)) {
			//check if new leader is available
			$query = "SELECT * FROM political_parties WHERE user_id = '$new_leader'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				exit("0|New leader can't be assigned because he already has a party.");
			}
			
			//check if user exists
			$query = "SELECT * FROM users WHERE user_id = '$new_leader'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				exit("0|User doesn't exist.");
			}
				
			//check if party member
			$query = "SELECT * FROM party_members WHERE user_id = '$new_leader'";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$query = "INSERT INTO party_members VALUES('$party_id', '$new_leader')";
				$conn->query($query);
			}
			
			//update  new leader
			$query = "UPDATE political_parties SET leader = '$new_leader' WHERE party_id = '$party_id'";
			$conn->query($query);
			
			$leader_changed = 'leader_changed';
		}
		$query = "SELECT party_name, description, leader, user_name
				  FROM political_parties pp, users u WHERE pp.party_id = '$party_id' AND u.user_id = pp.leader;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		
		list($party_name, $description, $leader, $leader_name) = $row;
		
		echo "1|You have successfully updated your party. $note|$party_name, $description, $leader, $leader_name";
	}
	else if($action == "kick") {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		
		//get users party
		$query = "SELECT party_id, flag, party_name FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have a party.");
		}
		$row = $result->fetch_row();
		list($party_id, $flag, $party_name) = $row;
		
		if($member_id == $user_id) {
			exit("0|You cannot exclude yourself from the party.");
		}
		
		//if congress candidate delete
		$query = "DELETE FROM party_congress_candidates WHERE user_id = '$member_id'";
		$conn->query($query);
		
		$query = "DELETE FROM party_members WHERE user_id = '$member_id'";
		$conn->query($query);
		
		$notification = "You have been kicked from the party $party_name";
		sendNotification($notification, $member_id);
		
		echo "1|You have successfully excluded this member from the party.";
	}
	else if($action == "join") {
		if(!ctype_digit($party_id)) {
			exit();
		}
		
		//check if party exist
		$query = "SELECT party_id, party_name, leader, citizenship FROM political_parties, user_profile 
				  WHERE party_id = '$party_id' AND user_id = leader";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Party doesn't exist.");
		}
		$row = $result->fetch_row();
		list($party_id, $party_name, $leader, $leader_citizenship) = $row;
		
		/* check if same citizenship (must be in the same country) */
		$query = "SELECT citizenship FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($citizenship) = $row;
		if($citizenship != $leader_citizenship) {
			exit("0|You must have the same citizenship as the leader of a party.");
		}
		
		//check if already a member
		$query = "SELECT * FROM party_members WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You already a member of another party.");
		}
		
		//check if already applied to another party
		$query = "SELECT * FROM party_applications WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You already submited application to another party.");
		}
		
		//apply
		$query = "INSERT INTO party_applications VALUES('$party_id', '$user_id')";
		if($conn->query($query)) {
			$notification = "A new user wants to join your party.";
			sendNotification($notification, $leader);
			
			echo "1|You have successfully submited application to join <i>$party_name</i> party. 
					Party leader will decide whether to accept you or not.";
		}
	}
	else if($action == "cancel_app") {
		//check if applied to a party
		$query = "SELECT * FROM party_applications WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You didn't applied to any party.");
		}
		
		//cancel
		$query = "DELETE FROM party_applications WHERE user_id = '$user_id'";
		if($conn->query($query)) {
			echo "1|You have successfully canceled your application.";
		}
	}
	else if($action == "view_apps") {
		//get user party
		$query = "SELECT pp.party_id, leader FROM political_parties pp, party_members pm 
				  WHERE pp.party_id = pm.party_id AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have a party.");
		}
		$row = $result->fetch_row();
		list($party_id, $leader) = $row;
		
		if($leader == $user_id) {
			$permission = 1;//show accept btn
		}
		else {
			$permission = 0;//do not show accept btn
		}
		
		$reply = "1||$permission||";
		
		//select all applications
		$query = "SELECT u.user_id, user_name, user_image FROM party_applications pa, users u, user_profile up 
				  WHERE u.user_id = pa.user_id AND up.user_id = u.user_id AND party_id = '$party_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($applicant_id, $applicant_name, $applicant_image) = $row;
			
			$reply .= "$applicant_id, $applicant_name, $applicant_image|";
		}
		echo $reply;
	}
	else if($action == "accept_member") {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		
		//get user party
		$query = "SELECT party_name, party_id FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have a party or required permissions to perform this task.");
		}
		$row = $result->fetch_row();
		list($party_name, $party_id) = $row;
		
		//is user applied
		$query = "SELECT * FROM party_applications WHERE user_id = '$member_id' AND party_id = '$party_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|This user did not apply for your party.");
		}
		
		//accept member
		$query = "INSERT INTO party_members VALUES('$party_id', '$member_id')";
		$conn->query($query);
		
		//delete from party_applications
		$query = "DELETE FROM party_applications WHERE user_id = '$member_id'";
		$conn->query($query);
		
		$notification = "You have been accepted to <i>$party_name</i> party.";
		sendNotification($notification, $member_id);
		
		echo "1|You have accepted new party member.";
	}
	else if($action == "leave_party") {
		//check if member of a party
		$query = "SELECT * FROM party_members WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You're not a member of any party.");
		}
		
		//if founder cannot leave
		$query = "SELECT * FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You are founder of this party.");
		}
		
		//if congress candidate, delete
		$query = "DELETE FROM party_congress_candidates WHERE candidate_id = '$user_id'";
		$conn->query($query);
		
		//delete from party members
		$query = "DELETE FROM party_members WHERE user_id = '$user_id'";
		$conn->query($query);
		
		echo "1|You have successfully left this party.";
	}
	else if($action == "manage_cong_elec") {
		//get user party
		$query = "SELECT pp.party_id, leader FROM political_parties pp, party_members pm 
				  WHERE pp.party_id = pm.party_id AND user_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You don't have a party.");
		}
		$row = $result->fetch_row();
		list($party_id, $leader) = $row;
		
		if($leader == $user_id) {
			$permission = 1;//can manage
		}
		else {
			$permission = 0;//cannot manage, only view
		}
		
		$reply = "1||$permission||";
	
		//select all applicants
		$query = "SELECT u.user_id, user_name, user_image, position_number FROM party_congress_candidates, users u, user_profile up 
				  WHERE u.user_id = candidate_id AND up.user_id = u.user_id AND party_id = '$party_id' ORDER BY position_number";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($applicant_id, $applicant_name, $applicant_image, $position_number) = $row;
			
			$reply .= "$applicant_id, $applicant_name, $applicant_image, $position_number|";
		}
		echo $reply;
	
	}
	else if($action == "add_pty_candidates") {
		//select members
		$query = "SELECT user_name, u.user_id, user_image FROM party_members pm, users u, user_profile up 
				  WHERE u.user_id = pm.user_id AND up.user_id = u.user_id AND party_id = 
				 (SELECT party_id FROM political_parties WHERE leader = '$user_id') AND u.user_id NOT IN 
				 (SELECT candidate_id FROM party_congress_candidates)";
		$result = $conn->query($query);
		
		if($result->num_rows == 0) {//don't have permissions or no available members left
			exit("0||There is no available members left.");
		}
		
		$reply = "1||";
		
		while($row = $result->fetch_row()) {
			list($member_name, $member_id, $member_image) = $row;
			$reply .= "$member_name, $member_id, $member_image|";
		}
		
		echo $reply;
	}
	else if($action == "add_candidate_to_list") {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit($member_id);
		}
		
		//must have at least level 5
		if(getUserLevel($user_id) < 5) {
			exit("0|Candidate must have at least level 5 in order to join the elections.");
		}
		
		//check if not governor
		$query = "SELECT * FROM country_government WHERE user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {//user not party member
			exit("0|This user is already a Governor.");
		}
		
		//get user party
		$party_id = getUserParty();
		
		//check if enough seats in congress
		$number_seats = getCountryCongressSeats();
		$candidates_from_party = getNumberCandidatesParty($party_id);
		if($number_seats == $candidates_from_party) {
			exit("0|You are not allowed to have more candidates than seats in the congress.");
		}
		
		//check if candidate party member
		$query = "SELECT * FROM party_members WHERE party_id = '$party_id' AND user_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {//user not party member
			exit("0|This user is not a member of your party.");
		}
		
		//check if applied for president elections(cannot be candidate for both)
		$query = "SELECT * FROM president_elections WHERE election_id IN (SELECT election_id FROM election_info  WHERE country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$member_id') AND (can_participate = TRUE AND type = TRUE)
				  OR (can_participate = FALSE AND type = FALSE)) 
				  AND candidate_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|This user already applied for president elections.");
		}
		
		//check if applied for elections by self
		$query = "SELECT * FROM congress_elections WHERE candidate_id = '$member_id' AND election_id IN 
				 (SELECT election_id FROM election_info  WHERE country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$member_id') AND (can_participate = TRUE AND type = TRUE)
				  OR (can_participate = FALSE AND type = FALSE))";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|This user already applied for congress elections by himself.");
		}
		
		//check if applied for elections by party
		$query = "SELECT * FROM party_congress_candidates WHERE candidate_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|You already added this user to congress candidates.");
		}
		
		//select last position_number
		$query = "SELECT MAX(position_number) FROM party_congress_candidates WHERE party_id = '$party_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($position_number) = $row;
			$position_number++;
		}
		else {
			$position_number = 0;
		}
		
		//add_candidate_to_list
		$query = "INSERT INTO party_congress_candidates VALUES('$party_id', '$member_id', '$position_number')";
		if($conn->query($query)) {
			$notification = "You have been added to the party congress elections list. Your position number is $position_number.";
			sendNotification($notification, $member_id);
			
			echo "1|You have successfully added this member to your party candidates list.|$position_number";
		}
		else {
			echo "0|Something went wrong. Please try again.";
		}
	}
	else if($action == "remove_candidate_from_list") {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		
		//get user party
		$party_id = getUserParty();
		
		//check if candidate applied from this party
		$member_position_number = isAppliedFromPartyToCongress($party_id, $member_id);
		
		$reply = "";
		$query = "SELECT * FROM party_congress_candidates WHERE party_id = '$party_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list(, $candidate_id, $position_number) = $row;
			
			if($candidate_id == $member_id) {
				continue;
			}
			
			if($position_number > $member_position_number) {
				$position_number--;
			}
			
			$query = "UPDATE party_congress_candidates SET position_number = '$position_number' WHERE candidate_id = '$candidate_id'";
			$conn->query($query);
			
			$reply .= "$candidate_id, $position_number|";
		}

		$query = "DELETE FROM party_congress_candidates WHERE candidate_id = '$member_id' AND party_id = '$party_id'";
		if($conn->query($query)) {
			$notification = "You have been removed from the party congress elections list.";
			sendNotification($notification, $member_id);
			
			echo "1||You have successfully removed this candidate from your party candidates list to Congress.||$reply";
		}
		else {
			echo "0||Something went wrong. Please try again.";
		}
	}
	else if($action == "move_up_candidate") {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		
		//get user party
		$party_id = getUserParty();
		
		//check if candidate applied from this party
		$member_position_number = isAppliedFromPartyToCongress($party_id, $member_id);
		if($member_position_number == 1) {
			exit("0||You cannot move this candidate's position up anymore.");
		}
		$member_position_number--;//move up
		$query = "UPDATE party_congress_candidates SET position_number = '$member_position_number' WHERE candidate_id = '$member_id'";
		$conn->query($query);

		$reply = "";
		$query = "SELECT * FROM party_congress_candidates WHERE party_id = '$party_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list(, $candidate_id, $position_number) = $row;
			
			if($position_number >= $member_position_number && ($candidate_id != $member_id)) {
				$position_number++;
				
				$notification = "You have been moved down by one position in your party elections list. Your new position is $position_number.";
				sendNotification($notification, $candidate_id);
			}
			
			$query = "UPDATE party_congress_candidates SET position_number = '$position_number' WHERE candidate_id = '$candidate_id'";
			$conn->query($query);
			
			$reply .= "$candidate_id, $position_number|";
		}
		
		$notification = "You have been moved up by one position in your party elections list. Your new position is $member_position_number.";
		sendNotification($notification, $member_id);
		
		echo "1||candidate's position successfully moved up.||$reply";
	}
	else if($action == "move_down_candidate") {
		if(!filter_var($member_id, FILTER_VALIDATE_INT)) {
			exit();
		}
		
		//get user party
		$party_id = getUserParty();
		
		//check if enough seats in congress
		$number_seats = getCountryCongressSeats();
		
		//check if candidate applied from this party
		$member_position_number = isAppliedFromPartyToCongress($party_id, $member_id);
		if($number_seats < ($member_position_number + 1)) {
			exit("0||You cannot move this candidate's position, down anymore.");
		}
		$member_position_number++;//move down
		$query = "UPDATE party_congress_candidates SET position_number = '$member_position_number' WHERE candidate_id = '$member_id'";
		$conn->query($query);

		$reply = "";
		$query = "SELECT * FROM party_congress_candidates WHERE party_id = '$party_id'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list(, $candidate_id, $position_number) = $row;
			
			if($position_number >= $member_position_number && $candidate_id != $member_id && ($position_number - 1) != $member_position_number) {
				$position_number--;
				
				$notification = "You have been moved up by one position in your party elections list. Your new position is $position_number.";
				sendNotification($notification, $candidate_id);
			}
			
			$query = "UPDATE party_congress_candidates SET position_number = '$position_number' WHERE candidate_id = '$candidate_id'";
			$conn->query($query);
			
			$reply .= "$candidate_id, $position_number|";
		}
		
		$notification = "You have been moved down by one position in your party elections list. Your new position is $member_position_number.";
		sendNotification($notification, $member_id);
		
		echo "1||Candidate's position successfully moved down.||$reply";
	}
	else if($action == "participate_elections") {
		//get user party
		$party_id = getUserParty();
		
		//determine if elections is scheduled
		$query = "SELECT election_id FROM election_info WHERE country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND can_participate = 1 AND type = 3";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|There is no scheduled congress elections in your country, you cannot perform this action at this moment.");
		}
		$row = $result->fetch_row();
		list($election_id) = $row;

		//detrmine if already participating
		$query = "SELECT * FROM congress_elections WHERE election_id = '$election_id' AND party_id = '$party_id'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			exit("0|Your party is already participating in these elections.");
		}
		
		//participate
		$query = "INSERT INTO congress_elections VALUES('$election_id', NULL, '$party_id', 0)";
		if($conn->query($query)) {
			echo "1|You have successfully joined these elections.";
		}
	}
	else if($action == "stop_participate_elections") {
		//get user party
		$party_id = getUserParty();
		
		//determine if elections is scheduled
		$query = "SELECT election_id FROM election_info WHERE country_id = 
				 (SELECT citizenship FROM user_profile WHERE user_id = '$user_id') AND can_participate = 1 AND type = 3";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|There is no scheduled congress elections in your country, you cannot perform this action anymore.");
		}
		$row = $result->fetch_row();
		list($election_id) = $row;

		//detrmine if participating
		$query = "SELECT * FROM congress_elections WHERE election_id = '$election_id' AND party_id = '$party_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|Your party is not participating in these elections.");
		}
		
		//stop participate
		$query = "DELETE FROM congress_elections WHERE party_id = '$party_id' AND election_id = '$election_id'";
		if($conn->query($query)) {
			echo "1|You have successfully cancled your application.";
		}
	}

	function getNumberCandidatesParty($party_id) {
		global $conn;
		$query = "SELECT COUNT(*) FROM party_congress_candidates WHERE party_id = '$party_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($candidates_from_party) = $row;
		return $candidates_from_party;
	}
	
	function getCountryCongressSeats() {
		global $conn;
		global $user_id;
		$query = "SELECT seats FROM country_congress_seats WHERE country_id = (SELECT citizenship FROM user_profile WHERE user_id = '$user_id')";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($number_seats) = $row;
		return $number_seats;
	}
	
	function getUserParty() {
		global $conn;
		global $user_id;
		$query = "SELECT party_id FROM political_parties WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0||You don't have a party.");
		}
		$row = $result->fetch_row();
		list($party_id) = $row;
		return $party_id;
	}
	
	function isAppliedFromPartyToCongress($party_id, $member_id) {
		global $conn;
		$query = "SELECT * FROM party_congress_candidates WHERE party_id = '$party_id' AND candidate_id = '$member_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {//user not party member
			exit("0||This user is not a member of your party.");
		}
		$row = $result->fetch_row();
		list(, , $member_position_number) = $row;
		return $member_position_number;
	}
	
	mysqli_close($conn);
?>