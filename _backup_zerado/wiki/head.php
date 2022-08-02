<?php
	$file_name = 'wiki';
	
	include('../connect_db.php');
	require_once '../php_functions/get_ip.php';//getIP();
	$ip = getIP();
	
	include("../php_functions/register_page_visitors.php"); //registerPageVisitors('$file_name', $visitor_ip));
	registerPageVisitors($file_name, $ip);
	
	//record previous page visited
	$referer_url = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
	$to_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
	if(!empty($referer_url)) {
		if(iconv_strlen($referer_url) > 50) {
			$referer_url = substr($referer_url, 0,50);
		}
		$query = "INSERT INTO referer_url VALUES('$ip', '$referer_url', CURRENT_DATE, CURRENT_TIME, '$to_url')";
		$conn->query($query);
	}
?>
<!-- Copyright 2017 - <?php echo date('Y'); ?>. All rights reserved. Copying and/or distributing this code 
is strictly prohibited and any attempt to do so will be prosecuted. -->
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<meta name="keywords" content="vMundus, virtual world, new world">
	<meta name="author" content="vMundus">
	<meta name="copyright" content="vMundus">
	<meta name="name" content="vMundus | Online Strategy Game">
	<meta name="description" content="VMundus is an online Strategy Game that allows players to rule a country, have own business empire, conquer other countries and research new technologies.">
	<title>vMundus Wiki</title>
	<link rel="stylesheet" href="../css/wiki.css?<?php echo filemtime('../css/wiki.css'); ?>">
	<link rel="stylesheet" href="../css/footer.css?<?php echo filemtime('../css/footer.css'); ?>">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	<link rel="stylesheet" href="../css/font-awesome.min.css">
	
	<script src="../js/jquery.js"></script>
	<script src="../js/wiki.js?<?php echo filemtime('../js/wiki.js'); ?>"></script>
	<script src="../js/footer.js?<?php echo filemtime('../js/footer.js'); ?>"></script>
	<!--favIcons-->
	<link rel="icon" href="../img/icon.png" type="image/x-icon">
	<!--favIcon for IE-->
	<link rel="shortcut icon" href="../img/icon.png" type="image/x-icon">
</head>
<body>

	<div id="top">
		<img src="../img/logo.png" id="logo">
		
		<div id="clock_info_div">
			<p id="clock"></p>
			<p id="day_number">Day 
			<?php
				$query = "SELECT MAX(day_number), MAX(date) FROM day_count";
				$result = mysqli_query($conn, $query);
				$row = mysqli_fetch_row($result);
				list($day_number) = $row;	
				echo "$day_number";
			?>	
		</div>
	</div>
	
	<div id="menu">
		<a class="menu_item" href="../index">To Game</a>
		<a class="menu_item" href="index">Wiki Home</a>
		<a class="menu_item" href="economy_module_part_I">Economy module. Part I.</a>
		<a class="menu_item" href="economy_module_part_II">Economy module. Part II.</a>
		<a class="menu_item" href="military_module">Military module.</a>
		<a class="menu_item" href="political_module">Political module.</a>
		<a class="menu_item" href="create_a_blog">Create a Blog</a>
		<a class="menu_item" href="houses_page">Houses page</a>
		<a class="menu_item" href="travel">About Travel</a>
		<a class="menu_item" href="get_job_work">Get a job and work</a>
	</div>