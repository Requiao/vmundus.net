<?php
	//Description: Create union.
	session_start();
	
	include('../connect_db.php');
	include('verify_php.php');
	include('../php_functions/upload_image.php'); //uploadImage($image_id, $folder_path, $old_path = NULL, $width = 400, $height = 200)
	include('../php_functions/string_validate.php'); //stringValidate($string, $min_len, $max_len, $str_name)
	
	$name =  htmlentities(stripslashes(strip_tags(trim($_POST['name']))), ENT_QUOTES);
	$abbr =  htmlentities(stripslashes(strip_tags(trim($_POST['abbr']))), ENT_QUOTES);
	$description =  htmlentities(stripslashes(strip_tags(trim($_POST['description']))), ENT_QUOTES);
	$color =  htmlentities(stripslashes(trim($_POST['color'])), ENT_QUOTES);

	$user_id = $_SESSION['user_id'];

	//get users union
		$query = "SELECT union_id, union_flag FROM unions WHERE leader = '$user_id'";
		$result = $conn->query($query);
		if($result->num_rows != 1) {
			exit("0|You're not a leader of any union.");
		}
		$row = $result->fetch_row();
		list($union_id, $union_flag) = $row;
		
		stringValidate($name, 1, 20, 'Union name');
		stringValidate($abbr, 1, 5, 'Union abbreviation');
		stringValidate($description, 0, 350, 'Description');
		
		//check color
		if(!empty($color)) {
			$color_reg = '/^rgb\((\d{0,3}),\s*(\d{0,3}),\s*(\d{0,3})\)$/';
			if(!preg_match($color_reg, $color)) {
				exit("0|Incorrect input for color.");
			}
			$query = "SELECT * FROM unions WHERE color = '$color' AND union_id != '$union_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) {
				exit("0|Union with this color already exist.");
			}
			
			//check if color is allowed
			$pattern = '/[rgb\(\) ]?/';
			$replacement = '';
			$clean_rgb = preg_replace($pattern, $replacement, $color);
			$rgb_colors = preg_split('/,/', $clean_rgb);
			$alert = 0;
			for($x = 0; $x < 3; $x++) {
				if($rgb_colors[$x] < 50) {
					$alert++;
				}
			}
			if($alert >= 3) {
				exit("0|Color is too dark. At least one color have to have value 50 and more in RGB(Red, Green, Blue)." .
					 " Current value is $color");
			}
		}
		
		//check if name not duplicate
		if(!empty($name)) {
			$query = "SELECT * FROM unions WHERE union_name = '$name' AND union_id != '$union_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Union with this name already exist.");
			}
		}
		
		//check if abbreviation is not duplicate
		if(!empty($abbr)) {
			$query = "SELECT * FROM unions WHERE abbreviation = '$abbr' AND union_id != '$union_id'";
			$result = $conn->query($query);
			if($result->num_rows == 1) { 
				exit("0|Union with this abbreviation already exist.");
			}
		}
		
		//update image
		if(file_exists($_FILES['image']['tmp_name'])) {//upload image
			if(!empty($union_flag)) {
				$union_flag = uploadImage($union_id, '../union_flags', '../union_flags/' . $union_flag);
			}
			else {
				$union_flag = uploadImage($union_id, '../union_flags');
			}
			if($union_flag == 1) {
				exit("0|File is not an image.");
			}
			if($union_flag == 2) {
				exit("0|Sorry, your file is too large.");
			}
			if($union_flag == 3) {
				exit("0|Sorry, only JPG, JPEG, PNG files are allowed.");
			}
			if($union_flag == 4) {
				exit("0|Image not uploaded. Please try again");
			}
			
			$query = "UPDATE unions SET union_flag = '$union_flag' WHERE union_id = '$union_id'";
			$conn->query($query);
			
			$note = "In order to see updated image right away, refresh the page with Ctrl+F5.";
		}
		else {
			$union_flag = null;
		}
		
		//update name
		if(!empty($name)) {
			$query = "UPDATE unions SET union_name = '$name' WHERE union_id = '$union_id'";
			$conn->query($query);
		}
		
		//update abbreviation
		if(!empty($abbr)) {
			$query = "UPDATE unions SET abbreviation = '$abbr' WHERE union_id = '$union_id'";
			$conn->query($query);
		}
		
		//update color
		if(!empty($color)) {
			$query = "UPDATE unions SET color = '$color' WHERE union_id = '$union_id'";
			$conn->query($query);
		}
		
		//update description
		if(!empty($description)) {
			$query = "UPDATE unions SET description = '$description' WHERE union_id = '$union_id'";
			$conn->query($query);
		}
		
		$query = "SELECT union_name, abbreviation, description FROM unions WHERE union_id = '$union_id'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		
		list($union_name, $abbreviation, $description) = $row;
		
		echo "1|You have successfully updated your union. $note|$union_name, $abbreviation, $description";

	
	mysqli_close($conn);
?>