<?php
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/str_validate.php'); //strValidate($string, $min_len, $max_len, $str_name)
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	
	$word =  htmlspecialchars(stripslashes(strip_tags(trim($_POST['word']))), ENT_QUOTES);
	$var_name =  htmlentities(stripslashes(strip_tags(trim($_POST['var_name']))), ENT_QUOTES);
	$file_id =  htmlentities(stripslashes(strip_tags(trim($_POST['file_id']))), ENT_QUOTES);
	$word_id =  htmlentities(stripslashes(strip_tags(trim($_POST['word_id']))), ENT_QUOTES);
	$lang_id =  htmlentities(stripslashes(strip_tags(trim($_POST['lang_id']))), ENT_QUOTES);
	$action =  htmlentities(stripslashes(strip_tags(trim($_POST['action']))), ENT_QUOTES);
	
	$user_id = $_SESSION['user_id'];
	
	//check if has access
	if($user_id != 1) {
		$query = "SELECT lang_id, language FROM languages
				  WHERE lang_id IN (SELECT lang_id FROM translation_access WHERE user_id = '$user_id' AND access = TRUE)";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have access."
								  )));
		}
	}
	$REWARD = 0.1;
	
	if($action == 'translate_word') {	
		//check if word exists
		if(!is_numeric($word_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Word doesn't exist."
								  )));
		}
		$query = "SELECT * FROM translation WHERE word_id = '$word_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Word doesn't exist."
								  )));
		}
		
		//check if language exists
		if(!is_numeric($lang_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Language doesn't exist."
								  )));
		}
		$query = "SELECT * FROM languages WHERE lang_id = '$lang_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1 && $lang_id != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Language doesn't exist."
								  )));
		}
		
		$query = "SELECT * FROM translation t, translation_access ta
				  WHERE user_id = '$user_id' AND t.file_id = ta.file_id AND word_id = '$word_id' AND access = TRUE
				  AND lang_id = '$lang_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have access."
								  )));
		}

		$query = "SELECT language, abbr FROM languages WHERE lang_id = '$lang_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($language, $abbr) = $row;
		
		$trdate = $abbr . "date";
		$trtime = $abbr . "time";
		$translator_id = $language . "_user_id";
		
		strValidate($word, 1, 120, 'Word');
		
		$query = "UPDATE translation SET $language = '$word' WHERE word_id = '$word_id'";
		$conn->query($query);
		
		$query = "UPDATE translation SET $trdate = CURRENT_DATE WHERE word_id = '$word_id'";
		$conn->query($query);
		
		$query = "UPDATE translation SET $trtime = CURRENT_TIME WHERE word_id = '$word_id'";
		$conn->query($query);
		
		$query = "UPDATE translation SET $translator_id = '$user_id' WHERE word_id = '$word_id'";
		$conn->query($query);
		
		//reward user
		$query = "SELECT * FROM translation_rewarded_users WHERE user_id = '$user_id' AND word_id = '$word_id'
				  AND lang_id = '$lang_id'";
		$result = $conn->query($query);
		$reward_error = false;
		$rewarded = false;
		if($result->num_rows == 0) {
			$query = "UPDATE user_product SET amount = (SELECT * FROM(SELECT amount FROM user_product 
					  WHERE user_id = '$user_id' AND product_id = '1') AS temp) + '$REWARD' 
					  WHERE user_id = '$user_id' AND product_id = '1'";
			if(!$conn->query($query)) {
				$reward_error = true;
			}
			else {
				$query = "INSERT INTO translation_rewarded_users VALUES('$word_id', '$user_id', '$lang_id')";
				$conn->query($query);
				$rewarded = true;
			}
		}	
		
		echo json_encode(array("success"=>true,
							   "msg"=>"Updated. " . ($rewarded?"+ $REWARD gold.":"") . ($reward_error?"reward error.":""),
							   "word"=>$word
							  ));
	}
	else if($action == 'get_words') {	
		//check if page exists
		if(!filter_var($file_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Page doesn't exist."
								  )));
		}
		$query = "SELECT * FROM mundus_pages WHERE file_id = '$file_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Page doesn't exist."
								  )));
		}
		
		//check if language exists
		if(!is_numeric($lang_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Language doesn't exist."
								  )));
		}
		if($user_id != 1 && $lang_id == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have access to this option."
								  )));
		}
		$query = "SELECT * FROM languages WHERE lang_id = '$lang_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1 && $lang_id != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Language doesn't exist."
								  )));
		}
		
		$query = "SELECT * FROM translation_access WHERE user_id = '$user_id' 
				  AND lang_id = '$lang_id' AND access = TRUE AND file_id = '$file_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			if($user_id == 1 && $lang_id == 0) {
				$query = "SELECT word_id, var_name, word, wdate, wtime FROM translation WHERE file_id = '$file_id'
						  ORDER BY word";
				$result = $conn->query($query);
				$words = array();
				while($row = $result->fetch_row()) {
					list($word_id, $var_name, $word, $wdate, $wtime) = $row;
					$date = correctDate($wdate, $wtime);
					$time = correctTime($wtime);
					
					array_push($words, array("word_id"=>$word_id, "var_name"=>$var_name, "word"=>$word, "date"=>$date, "time"=>$time,
											 "old"=>false));
				}
				echo json_encode(array("success"=>true,
									   "words"=>$words,
									   "base"=>true,
									   "mod"=>true
									  ));
			}
			else {
				exit(json_encode(array('success'=>false,
									   'error'=>"You don't have access."
								  )));
			}
		}
		else {
			$query = "SELECT language, abbr FROM languages WHERE lang_id = '$lang_id'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($language, $abbr) = $row;
			
			$translator_id = $language . "_user_id";
			$trdate = $abbr . "date";
			$trtime = $abbr . "time";
			
			$query = "SELECT word_id, word, wdate, wtime, $language, $translator_id, $trdate, $trtime 
					  FROM translation WHERE file_id = '$file_id' ORDER BY word";
			$result = $conn->query($query);
			$words = array();
			while($row = $result->fetch_row()) {
				list($word_id, $word, $wdate, $wtime, $translated, $translator, $tdate, $ttime) = $row;
				if(strtotime(date("Y-m-d H:i:s", strtotime("$wdate $wtime")) )
					> strtotime(date("Y-m-d H:i:s", strtotime("$tdate $ttime")))) {
					$old = true;
				}
				else {
					$old = false;
				}
				$translator = $user_id==1?$translator:false;
				$translated = $translated?$translated:'';
				
				array_push($words, array("word_id"=>$word_id, "word"=>$word, "translated"=>$translated,
										 "translator"=>$translator, "old"=>$old));
			}
			echo json_encode(array("success"=>true,
								   "words"=>$words,
								   "base"=>false,
								   "mod"=>$user_id==1?true:false
								  ));
		}
	}
	else if($action == 'get_pages') {
		//check if page exists
		if(!is_numeric($lang_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Language doesn't exist."
								  )));
		}
		$query = "SELECT * FROM languages WHERE lang_id = '$lang_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1 && $lang_id != 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Language doesn't exist."
								  )));
		}
		if($user_id != 1 && $lang_id == 0) {
			exit(json_encode(array('success'=>false,
								   'error'=>"You don't have access to this option."
								  )));
		}
		
		$query = "SELECT file_name, file_id FROM mundus_pages
				  WHERE file_id IN (SELECT file_id FROM translation_access WHERE user_id = '$user_id' 
				  AND lang_id = '$lang_id' AND access = TRUE)
				  ORDER BY file_name";
		$result = $conn->query($query);
		if($result->num_rows == 0) {
			if($user_id == 1 && $lang_id == 0) {
				$query = "SELECT file_name, file_id FROM mundus_pages ORDER BY file_name";
				$result = $conn->query($query);
			}
			else {
				exit(json_encode(array('success'=>false,
									   'error'=>"You don't have access."
								  )));
			}
		}
		$pages = array();
		while($row = $result->fetch_row()) {
			list($file_name, $file_id) = $row;
			array_push($pages, array("file_name"=>$file_name, "file_id"=>$file_id));
		}
		echo json_encode(array("success"=>true,
							   "pages"=>$pages
							  ));
	}
	else if($action == 'delete_word') {
		if($user_id != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		
		//check if word exists
		if(!is_numeric($word_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Word doesn't exist."
								  )));
		}
		$query = "SELECT * FROM translation WHERE word_id = '$word_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Word doesn't exist."
								  )));
		}
		
		$query = "DELETE FROM translation WHERE word_id = '$word_id'";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Deleted"
								  ));
		}
		else {
			echo json_encode(array("success"=>false,
								   "error"=>"Error."
								  ));
		}
	}
	else if($action == 'edit_word') {
		if($user_id != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		
		//check if word exists
		if(!is_numeric($word_id)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Word doesn't exist."
								  )));
		}
		$query = "SELECT * FROM translation WHERE word_id = '$word_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Word doesn't exist."
								  )));
		}
		
		strValidate($var_name, 1, 120, 'Var name');
		strValidate($word, 1, 120, 'Word');
		
		$query_var = "UPDATE translation SET var_name = '$var_name' WHERE word_id = '$word_id'";
		$query_word = "UPDATE translation SET word = '$word' WHERE word_id = '$word_id'";
		$conn->query($query_word);
		$conn->query($query_var);
		
		$query = "UPDATE translation SET wdate = CURRENT_DATE WHERE word_id = '$word_id'";
		$conn->query($query);
		
		$query = "UPDATE translation SET wtime = CURRENT_TIME WHERE word_id = '$word_id'";
		$conn->query($query);
		echo json_encode(array("success"=>true,
							   "msg"=>"Updated",
							   "word"=>$word,
							   "var_name"=>$var_name
							  ));
	}
	else if($action == 'add_new_word') {
		if($user_id != 1) {
			exit(json_encode(array("success"=>false,
								   "error"=>"You don't have access to this option."
								  )));
		}
		
		//check if page exists
		if(!filter_var($file_id, FILTER_VALIDATE_INT)) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Page doesn't exist."
								  )));
		}
		$query = "SELECT * FROM mundus_pages WHERE file_id = '$file_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit(json_encode(array('success'=>false,
								   'error'=>"Page doesn't exist."
								  )));
		}
		
		strValidate($var_name, 1, 120, 'Var name');
		strValidate($word, 1, 120, 'Word');
		
		$word_id = getTimeForId() . $user_id;
		
		$query = "INSERT INTO translation (word_id, file_id, var_name, word, wdate, wtime) 
				  VALUES ('$word_id', '$file_id', '$var_name', '$word', CURRENT_DATE, CURRENT_TIME)";
		if($conn->query($query)) {
			echo json_encode(array("success"=>true,
								   "msg"=>"Added",
								   "word_id"=>$word_id,
								   "word"=>$word,
								   "var_name"=>$var_name
								  ));
		}
		else {
			echo json_encode(array("success"=>false,
								   "error"=>"Error. Duplicate data?"
								  ));
		}
	}
	else {
		exit(json_encode(array("success"=>false,
							   "error"=>"Invalid request."
							  )));
	}
?>