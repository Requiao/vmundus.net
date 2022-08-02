<?php
    function createSession($conn, $user_id) {
		$session_id = getTimeForId() . $user_id;
		$ip = getIP();
		
		//record session
		$query = "INSERT INTO session_table 
                  VALUES('$session_id', '$user_id', '$ip', CURRENT_DATE, CURRENT_TIME, TRUE)";
		if(!$conn->query($query)) {
			return false;
		};

		$_SESSION['is_session'] = 'set';
		$_SESSION['session_id'] = $session_id;
		$_SESSION['user_id'] = $user_id;
		
		return true;
    }
?>