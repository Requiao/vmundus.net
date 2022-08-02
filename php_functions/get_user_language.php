<?php
	//Description: detect language and include language file
	function getUserLanguage() {
		global $conn;
		
		if(isset($_COOKIE['lang'])) {
			$site_lang = htmlentities(stripslashes(trim($_COOKIE['lang'])), ENT_QUOTES);
			$query = "SELECT * FROM languages WHERE abbr = '$site_lang'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				return $site_lang;
			}
		}
		else if($_SESSION['issession'] == 'set' && isset($_SESSION['user_id'])) {
			//check database if logged
			$user_id = $_SESSION['user_id'];
			$query = "SELECT lang FROM user_profile WHERE user_id = '$user_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($site_lang) = $row;
			return $site_lang;
		}
		else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {//detect if first time
			// break up string into pieces (languages and q factors)
			$pref_lang = htmlentities(stripslashes(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), ENT_QUOTES);
			preg_match_all('/([a-z]{2}(-[a-z]{2})?)/i', strtolower($pref_lang), $user_lang);
		
			for($x = 0; $x <= count($user_lang); $x++) {
				$default_lang = $user_lang[0][$x];
				$query = "SELECT * FROM languages WHERE abbr = '$default_lang'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					return $default_lang;
				}
			}
		}
		//default
		return 'en';
	}
?>