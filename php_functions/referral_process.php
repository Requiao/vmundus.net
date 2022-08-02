<?php
	require_once('get_user_level.php');//getUserLevel($user_id);
	function referralProcess($user_id) {
		global $conn;
		//check if belong to a referral
		$query = "SELECT user_id FROM referer_info WHERE refering_id = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			return false;
		}
		$row =  $result->fetch_row();
		list($referer_id) = $row;
		
		//update referrers reward
		$reached_level = getUserLevel($user_id);
		if($reached_level % 10 == 0) {
			$reward = 1;//1 gold
		}
		else {
			$reward = 0.2;//0.2 gold
		}
		$notification = "Congratulation. Your referrer reached level $reached_level. You earned $reward gold.";
		sendNotification($notification, $referer_id);
		
		$query = "UPDATE referer_info SET available_amount = (SELECT * FROM (SELECT available_amount FROM
				  referer_info WHERE user_id = '$referer_id' AND refering_id = '$user_id') AS temp) + '$reward'
				  WHERE user_id = '$referer_id' AND refering_id = '$user_id'";
		$conn->query($query);
		
		//achievement
		//determine if reached level 10
		if($reached_level == 10) {
			$achievement_id = 1; //Society Builder;
			$query = "SELECT earned FROM user_achievements WHERE user_id = '$referer_id' 
					  AND achievement_id = '$achievement_id'";
			$result = $conn->query($query);
			if($result->num_rows == 0) {
				$query = "INSERT INTO user_achievements VALUES ('$referer_id', '$achievement_id', '0', '1')";
			}
			else {
				$row = $result->fetch_row();
				list($earned) = $row;
				$earned++;
				$query = "UPDATE user_achievements SET earned = '$earned' WHERE user_id = '$referer_id' 
						  AND achievement_id = '$achievement_id'";
			}
			$conn->query($query);
			
			//notify referer
			$notification = "Congratulation. You earned Society Builder medal.";
			sendNotification($notification, $referer_id);
		}
		
		return true;
	}		
?>