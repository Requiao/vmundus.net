<?php
	function registerPageVisitors($file_name, $visitor_ip) {
		global $conn;
		
		//get page id
		$query = "SELECT file_id FROM mundus_pages WHERE file_name = '$file_name'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		list($file_id) = $row;	
		
		$query = "INSERT INTO page_visitors VALUES('$file_id', '$visitor_ip', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
		
		
	}		
?>