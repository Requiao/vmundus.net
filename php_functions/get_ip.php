<?php
	function getIP() {
		$ip = htmlentities(stripslashes(strip_tags(trim($_SERVER['REMOTE_ADDR']))), ENT_QUOTES);

		if(!filter_var($ip, FILTER_VALIDATE_IP)) {
			exit(json_encode(array(
				'success'=>false,
				'error'=>"Failed identification."
			)));
		}

		return $ip;
	};
?>