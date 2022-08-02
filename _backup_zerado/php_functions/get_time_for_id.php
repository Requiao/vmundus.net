<?php
	//returns 16 digits
	function getTimeForId() {
		$time = microtime();
		$time = str_replace('0.', '', $time);
		$time = substr_replace($time, '', 6, 3);
		return $time;
	}
?>