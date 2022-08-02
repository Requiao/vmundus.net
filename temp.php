<?php
	session_start();
	include('connect_db.php');
	/*
	$user_id = $_SESSION['user_id'];

	if($user_id != 1) {
		exit();
	}
	
	$FILE_ID = 12;
	$LANG = 'php';
	//$LANG = 'js';
	
	$LANGUAGE = 'word';
	$LANGUAGE = 'portuguese';
	
	//php
	if($LANG == "php") {
		$query = "SELECT var_name, $LANGUAGE FROM translation WHERE file_id = '$FILE_ID'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($var_name, $word) = $row;
			$word = preg_replace('/\[.*?\]/', '', $word);
			echo "\"$var_name\"=>\"$word\",<br>";
		}
	}
	
	//js
	if($LANG == "js") {
		$query = "SELECT var_name, $LANGUAGE FROM translation WHERE file_id = '$FILE_ID'";
		$result = $conn->query($query);
		while($row = $result->fetch_row()) {
			list($var_name, $word) = $row;
			$word = preg_replace('/\[.*?\]/', '', $word);
			echo "$var_name: \"$word\",<br>";
		}
	}*/
?>