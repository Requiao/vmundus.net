<?php
	//Description: .
	
    include('../connect_db.php');
	
	$country_id =  htmlentities(stripslashes(strip_tags(trim($_GET['country_id']))), ENT_QUOTES);
    $action = htmlentities(stripslashes(strip_tags(trim($_GET['action']))), ENT_QUOTES);
    

    if($action == "get_country_production_taxes") {
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
        
        $production_taxes = array();
        $query = "SELECT c.country_id, country_name, pi.product_id, product_name, tax
                  FROM country c, product_info pi,
                 (SELECT * FROM product_production_tax WHERE country_id = '$country_id') AS ppt
                  WHERE c.country_id = ppt.country_id AND pi.product_id = ppt.product_id";
        $result = $conn->query($query);
        while($row = $result->fetch_row()) {
            list($country_id, $country_name, $product_id, $product_name, $tax) = $row;
            $tax /= 100;


            array_push($production_taxes, array("country_id"=>$country_id, 
                "country_name"=>$country_name, "product_id"=>$product_id, 
                "product_name"=>$product_name, "tax"=>$tax));
        }

        echo json_encode(array(
            'success'=>true,
            'production_taxes'=>$production_taxes
        ));
    }
    else if($action == "get_regions_info") {
        $regions_info = array();
        $query = "SELECT r.country_id, country_name, region_id, region_name FROM regions r, country c
                  WHERE c.country_id = r.country_id";
        $result = $conn->query($query);
        while($row = $result->fetch_row()) {
            list($country_id, $country_name, $region_id, $region_name) = $row;
       
            array_push($regions_info, array("country_id"=>$country_id, 
                "country_name"=>$country_name, "region_id"=>$region_id, 
                "region_name"=>$region_name));
        }

        echo json_encode(array(
            'success'=>true,
            'regions_info'=>$regions_info
        ));
    }
    else {
        exit(json_encode(array(
            'success'=>false,
            'msg'=>"Invalid request."
        )));
    }
?>