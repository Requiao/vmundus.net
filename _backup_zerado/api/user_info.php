<?php
	//Description: .
	
    include('../connect_db.php');
    include('../php_functions/get_user_level.php');//getUserLevel($user_id);
	
	$user_id =  htmlentities(stripslashes(strip_tags(trim($_GET['user_id']))), ENT_QUOTES);
    $action = htmlentities(stripslashes(strip_tags(trim($_GET['action']))), ENT_QUOTES);
    

    if($action == "get_user_details") {
        if(!filter_var($user_id, FILTER_VALIDATE_INT)) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"User doesn't exists."
            )));
        }
    
        $query = "SELECT citizenship, user_name FROM user_profile up, users u
                  WHERE up.user_id = '$user_id' AND u.user_id = up.user_id";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"User doesn't exists."
            )));
        }
        $row = $result->fetch_row();
        list($citizenship, $user_name) = $row;

        $user_level = getUserLevel($user_id);
        
        echo json_encode(array(
            'success'=>true,
            'user_level'=>$user_level,
            'citizenship'=>$citizenship,
            'user_name'=>$user_name
        ));
    }
    else {
        exit(json_encode(array(
            'success'=>false,
            'msg'=>"Invalid request."
        )));
    }
?>