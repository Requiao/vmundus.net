<?php 
	require_once('referral_process.php');//referralProcess($user_id);
	function addPointsToUserExperience($user_id, $points) {
		global $conn;
		
		$query = "SELECT experience FROM user_profile WHERE user_id = '$user_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($experience) = $row;

		$experience += $points;
	
		//check if user leveled up
		$earned_new_level = false;
		
		$query = "SELECT level_id, experience FROM user_exp_levels 
				  WHERE experience <= '$experience' ORDER BY level_id DESC LIMIT 1";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			$query = "SELECT level_id, experience FROM user_exp_levels WHERE level_id = 1";
			$result = $conn->query($query);
		}
		$row = $result->fetch_row();
		list($next_level, $req_experience) = $row;
		
		$query = "SELECT level_id FROM user_level_rewards WHERE user_id = '$user_id' ORDER BY level_id DESC LIMIT 1";
		$result = $conn->query($query);
		if($result->num_rows == 0 && $experience >= $req_experience) {//for first level
			$earned_new_level = true;
		}
		else {
			$row = $result->fetch_row();
			list($current_level) = $row;
			if($next_level > $current_level && $experience >= $req_experience) {//earned medal
				$earned_new_level = true;
			}
		}

		$query = "UPDATE user_profile SET experience = '$experience' WHERE user_id = '$user_id'";
		$conn->query($query);
		
		if($earned_new_level) {
			//if gained exp for more than 1 level.
			$earned_levels = $next_level - $current_level;
			$next_level -= $earned_levels;
			for($u = 0; $u < $earned_levels; $u++) {
				$next_level++;
				
				$query = "INSERT INTO user_level_rewards VALUES ('$user_id', '$next_level', FALSE)";
				$conn->query($query);
				
				//notify user
				$notification = "Congratulation. You were leveled up. You can collect your reward on your profile page.";
				sendNotification($notification, $user_id);

				referralProcess($user_id);
			}
		}
		
		//weekly missions progress
		$query = "SELECT IFNULL(experience_earned, 0), wm.mission_id 
				  FROM weekly_missions wm LEFT JOIN users_weekly_missions_progress uwmp ON uwmp.mission_id = wm.mission_id
				  AND user_id = '$user_id' ORDER BY date DESC, TIME DESC LIMIT 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($experience_earned, $mission_id) = $row;
			
		if($experience_earned > 0) {
			$experience_earned += $points;
			
			$query = "UPDATE users_weekly_missions_progress SET experience_earned = '$experience_earned' WHERE user_id = '$user_id'
					  AND mission_id = '$mission_id'";
			$conn->query($query);
		}
		else {
			$experience_earned = $points;
		
			$query = "INSERT INTO users_weekly_missions_progress VALUES ('$mission_id', '$user_id', '$experience_earned')";
			$conn->query($query);
		}
	}
?>