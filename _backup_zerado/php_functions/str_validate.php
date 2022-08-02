<?php
	function strValidate(&$string, $min_len, $max_len, $str_name) {
		global $conn;
		$reg_string = '/(*UTF8)^[\s\S]*$/i';
		$unmatched_regex = '/(*UTF8)[\s\S]*/i';
		
		if (!preg_match($reg_string, $string)) {
			$unmatched_chars = preg_replace($unmatched_regex, '', $string);
			exit(json_encode(array('success'=>false,
								   'error'=>"Unexeptable characters $unmatched_chars")));
		}
		else if (iconv_strlen($string) < $min_len) {
			exit(json_encode(array('success'=>false,
								   'error'=>"$str_name is too short, must be more than $min_len chars.")));
		}
		else if (iconv_strlen($string) > $max_len) {
			exit(json_encode(array('success'=>false,
								   'error'=>"$str_name is too long, must be less than $max_len chars." .
											" Current length is " . iconv_strlen($string))));
		}
		else {
			$string = mysqli_real_escape_string($conn, $string);
			return true;
		}
	}
?>