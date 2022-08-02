<?php
	//Description: upload image.
	
	function uploadImage($image_name, $folder_path, $old_path = NULL, $width = 400, $height = 200, $ext = 'all', $size = 500000) {
		global $conn;
		$target_file = basename($_FILES["image"]["name"]);
		$image_file_type = pathinfo($target_file, PATHINFO_EXTENSION);
		$image_name = strtolower($image_name);

		//img name might be spoofed
		$real_extension = image_type_to_mime_type(exif_imagetype($_FILES["image"]["tmp_name"]));
		if(($real_extension != "image/jpeg" && $real_extension != "image/jpg")
			&&  $real_extension != "image/png") {
			return 1;
		}
		if ($ext != 'all' && $real_extension != "image/$ext") {
			return 1;
		}
		
		if(exif_imagetype($_FILES["image"]["tmp_name"]) != IMAGETYPE_JPEG && 
		   exif_imagetype($_FILES["image"]["tmp_name"]) != IMAGETYPE_PNG) {
			return 1;
			//File is not an image.
		}
	
		// Check file size
		if ($_FILES["image"]["size"] > $size) {
			return 2;
			//Sorry, your file is too large.
		}
			
		// Allow certain file formats
		if(strtolower($image_file_type) != "jpg" && strtolower($image_file_type) != "png" && strtolower($image_file_type) != "jpeg") {
			return 3;
			//Sorry, only JPG, JPEG, PNG files are allowed.
		}
		
		// if everything is ok, try to upload file
		//create small image
		$img_file = $_FILES["image"]["tmp_name"];
		if($real_extension == "image/jpg" || $real_extension == "image/jpeg") {
			$temp_big = imagecreatefromjpeg($img_file);
		}
		elseif($real_extension == "image/png") {
			$temp_big = imagecreatefrompng($img_file); 
		}

		$bigsize = getimagesize($img_file);
		$big_width = $bigsize[0];
		$big_height = $bigsize[1];
		$temp_small = imagecreatetruecolor($width, $height); 
		imagecopyresampled($temp_small, $temp_big, 0, 0, 0, 0, $width, $height, $big_width, $big_height); 
		$image = $image_name . '.' . $image_file_type;//to store in database
		$path = $folder_path . '/' . $image_name . '.' . $image_file_type;

		if(!empty($old_path) && file_exists($old_path)) {
			unlink($old_path);
		}
		
		if($image_file_type == "jpg" || $image_file_type == "jpeg") {
			if(!imagejpeg($temp_small, $path , 100)) {
				return 4;
				//Image not uploaded.
			}
		}
		elseif($image_file_type == "png") {
			if(!imagepng($temp_small, $path , 9)) {
				return 4;
				//Image not uploaded.
			}
		}
		ImageDestroy($temp_big); 
		ImageDestroy($temp_small);
		
		return $image;
	}
?>