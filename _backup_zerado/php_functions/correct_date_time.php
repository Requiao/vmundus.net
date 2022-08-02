<?php
	function correctTime($time, $country_id = 0, $user_id = 0) {
		global $conn;
		//get hours difference based on timezone
		if($country_id == 0 && $user_id == 0) {
			$user_id = $_SESSION['user_id'];
			$query_time = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
		}
		else if ($country_id != 0){
			$query_time = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM country WHERE country_id = '$country_id')";
		}
		else if ($user_id != 0) {
			$query_time = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
		}
		$result_time = mysqli_query($conn, $query_time);
		$row_time = mysqli_fetch_row($result_time);
		list($hours) = $row_time;

		return date('H:i:s', strtotime("$time +$hours hours"));
	}
		
	function correctDate($date, $time, $country_id = 0, $user_id = 0) {
		global $conn;
		//get hours difference based on timezone
		if($country_id == 0 && $user_id == 0) {
			$user_id = $_SESSION['user_id'];
			$query_time = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
		}
		else if ($country_id != 0){
			$query_time = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM country WHERE country_id = '$country_id')";
		}
		else if ($user_id != 0) {
			$query_time = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
		}
		$result_time = mysqli_query($conn, $query_time);
		$row_time = mysqli_fetch_row($result_time);
		list($hours) = $row_time;

		return date('Y-m-d', strtotime("$date $time +$hours hours"));
	}
?>