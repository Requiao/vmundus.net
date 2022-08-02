<?php
	function passwordValidate($password) {
		if(iconv_strlen($password) < 5) {
			return false;
		}
		
		return true;
		
	}		
?>