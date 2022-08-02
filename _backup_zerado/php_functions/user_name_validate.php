<?php
	function userNameValidate($name) {
		global $conn;
		
		$MIN_CHARS = 3;
		$MAX_CHARS = 15;
		
		$reg_user_name = '/(*UTF8)^[a-z0-9\p{Arabic}\p{Armenian}\p{Cyrillic}\p{Ethiopic}\p{Georgian}\p{Greek}\p{Hebrew}\p{Mongolian} .\-_\=*]*$/i';
		
		if (!preg_match($reg_user_name, $name)) {
			return false;
		}
		
		if (iconv_strlen($name) < $MIN_CHARS || iconv_strlen($name) > $MAX_CHARS) {
			return false;
		}
		
		//check if in the list of invalid names
		$name = strtolower($name);
		$query = "SELECT * FROM invalid_user_names WHERE invalid_name LIKE '%$name%'";
		$result = $conn->query($query);
		if($result->num_rows > 0) {
			return false;
		}
		return true;
	}
?>