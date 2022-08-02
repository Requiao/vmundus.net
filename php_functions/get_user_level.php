<?php 
	function getUserLevel($user_id) {
		global $conn;
		
		$query = "SELECT IFNULL(level_id, 0)
				  FROM user_profile up LEFT JOIN user_exp_levels uxl 
				  ON uxl.experience <= up.experience
				  WHERE up.user_id = '$user_id'
				  ORDER BY level_id DESC LIMIT 1";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($user_level) = $row;
		
		return $user_level;
	}
?>