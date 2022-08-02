<?php
	//Description: Manage corporation.
	
    include('../connect_db.php');
    include('../php_functions/get_user_level.php');//getUserLevel($user_id);
	
	$region_id =  htmlentities(stripslashes(strip_tags(trim($_GET['region_id']))), ENT_QUOTES);
	$country_id =  htmlentities(stripslashes(strip_tags(trim($_GET['country_id']))), ENT_QUOTES);
	$user_id =  htmlentities(stripslashes(strip_tags(trim($_GET['user_id']))), ENT_QUOTES);
    $action = htmlentities(stripslashes(strip_tags(trim($_GET['action']))), ENT_QUOTES);
    

    if($action == "get_user_bonuses") {
        if(!filter_var($user_id, FILTER_VALIDATE_INT)) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"User doesn't exists."
            )));
        }
    
        $query = "SELECT * FROM users WHERE user_id = '$user_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"User doesn't exists."
            )));
        }

        $user_level = getUserLevel($user_id);

        $query = "SELECT work_bonus, millitary_bonus FROM bonus_per_user_level";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        list($work_bonus, $military_bonus) = $row;
        $productivity_bonus = round($user_level * $work_bonus / 100, 3);
        $damage_bonus = round($user_level * $military_bonus, 2);
        
        echo json_encode(array(
            'success'=>true,
            'damage_bonus'=>$damage_bonus,
            'productivity_bonus'=>$productivity_bonus
        ));
    }
    else if($action == "get_country_product_bonus") {
        if(!filter_var($country_id, FILTER_VALIDATE_INT)) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"Country doesn't exists."
            )));
        }
    
        $query = "SELECT * FROM country WHERE country_id = '$country_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"Country doesn't exists."
            )));
        }

        $country_resource_bonus = array();
        $query = "SELECT pi.product_id, product_icon, product_name, SUM(bonus)
                  FROM region_resource_bonus rrb, product_info pi
                  WHERE region_id IN (SELECT region_id FROM regions WHERE country_id = '$country_id')
                  AND pi.product_id = rrb.product_id
                  GROUP BY rrb.product_id";
        $result = $conn->query($query);
        while($row = $result->fetch_row()) {
            list($product_id, $product_icon, $product_name, $bonus) = $row;
            if($bonus > 50) {
                $bonus = 50;
            }
            $bonus /= 100;
            
            array_push($country_resource_bonus, array("product_id"=>$product_id, 
                "product_name"=>$product_name, "bonus"=>$bonus));
        }

        echo json_encode(array(
            'success'=>true,
            'country_resource_bonus'=>$country_resource_bonus
        ));
    }
    else if ($action == 'get_region_road_bonus') {
        //validate region
        if(!filter_var($region_id, FILTER_VALIDATE_INT)) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"Region doesn't exists."
            )));
        }

        $query = "SELECT region_name FROM regions WHERE region_id = '$region_id'";
        $result = $conn->query($query);
        if($result->num_rows != 1) {
            exit(json_encode(array(
                'success'=>false,
                'msg'=>"Region doesn't exists."
            )));
        }
        $row = $result->fetch_row();
        list($region_name) = $row;

        //get road bonus
        $road_bonus = 0;
        $query = "SELECT productivity_bonus FROM region_roads rr, road_const_info rci
                  WHERE rci.road_id = rr.road_id AND region_id = '$region_id'";
        $result = $conn->query($query);
        if($result->num_rows == 1) {
            $row = $result->fetch_row();
            list($bonus, $durability_left) = $row;
            if($durability_left > 0) {
                $region_name = $region_name;
            }
        }

        echo json_encode(array(
            'success'=>true,
            'road_bonus'=>$road_bonus,
            'region_name'=>$region_name
        ));
    }
    else {
        exit(json_encode(array(
            'success'=>false,
            'msg'=>"Invalid request."
        )));
    }
?>