<?php
	function cutLongName($string, $max_length) {
		//if name is too long
		if(iconv_strlen($string) > $max_length) {
			$string = substr($string, 0, $max_length);
		}
		return $string;
	}
?>