<?php
	$coming_on = "2017-11-02 23:00:00";
		
	$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
	$date2 = new DateTime($coming_on);
	$diff = date_diff($date1,$date2);
	$come_in_days = $diff->format("%a");
	$come_in = $diff->format("%H:%I:%S");
		
	if(strtotime($coming_on) < strtotime(date('Y-m-d H:i:s'))) {
		header("Location: /");
		exit;
	}

	include('connect_db.php');
	include('php_functions/get_ip.php');//getIP();
	$ip = getIP();
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit("You are not allowed to view this page.");
	}

	
	include("php_functions/get_user_language.php"); //getUserLanguage('extension');
	include("php_functions/register_page_visitors.php"); //registerPageVisitors('$file_name', $visitor_ip));
	registerPageVisitors('coming_soon', $ip);
	
	include('lang/' . getUserLanguage('php') . '.php');
	
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
	<meta name="description" content="VMundus is an online Strategy Game that allows players to rule a country, have own business 
	empire and/or conquer other countries.">
	<title>vMundus</title>
	<link rel="stylesheet" href="css/coming_soon.css">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Slabo+27px" rel="stylesheet">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<?php 
		//check available languages for JAVASCRIPT.
		echo '<script src="lang/' . getUserLanguage('js') . '.js"></script>';
	?>
	
	<script src="js/jquery.js"></script>
	<script src="js/coming_soon.js"></script>
	<script src="js_etc/submit_data.js"></script>
	<script src="js/footer.js"></script>
	<script src="js_etc/countdown_clock.js"></script>
	<!--favIcons-->
	<link rel="icon" href="img/icon.png" type="image/x-icon">
	<!--favIcon for IE-->
	<link rel="shortcut icon" href="img/icon.png" type="image/x-icon">
</head>
<body>

	<!--top bar-->
	<div id="top">
		<img src="img/logo.png" id="logo">
		
		<div id="lang_bar_div">
			<abbr title="english"><img src="country_flags/us.png" abbr="english language"></abbr>
		</div>
		
		<div id="clock_info_div">
			<p id="clock"></p>
			<p id="day_number"><?php echo $lang['day']; ?> 
			<?php
				$query = "SELECT MAX(day_number), MAX(date) FROM day_count";
				$result = mysqli_query($conn, $query);
				$row = mysqli_fetch_row($result);
				list($day_number) = $row;	
				echo "$day_number.";
			?>	
		</div>
	</div>
	
	<!--Main content-->
	<div id="main">
		<?php
		
		$coming_on = "2017-11-02 23:00:00";
		
		$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
		$date2 = new DateTime($coming_on);
		$diff = date_diff($date1,$date2);
		$come_in_days = $diff->format("%a");
		$come_in = $diff->format("%H:%I:%S");
		
		echo "\n\t\t" . '<div id="com_soon_div">' .
			 "\n\t\t\t" . '<p id="coming_in">Coming In <span>' . $come_in_days . ' Days ' . $come_in . '</span></p>' .
			 "\n\t\t\t" . '<p id="p_notify"><input type="checkbox" id="notify" value="1">Notify me when the game starts</p>' .
			 "\n\t\t\t" . '<p id="reply"></p>' .
			 "\n\t\t\t" . '<input type="email" id="email" placeholder="sample@email.com">' .
			 "\n\t\t\t" . '<p id="submit_email">Submit</p>' .
			 "\n\t\t\t" . '<div id="pre_reg_info_div">' .
			 "\n\t\t\t\t" . '<p class="pre_reg_info">Sign up now and get extra bonuses when game starts!</p>' .
			 "\n\t\t\t" . '</div>' .
			 "\n\t\t" . '</div>';
			?>
		</div>
	
		<div id="popup_div">
		</div>
	</div>	

	<p id="about_head">About</p>
	
	<div id="desc_div">
		<div id="desc_div_desc">
			<p id="short_desc">vMundus - is a free browser-based multiplayer strategy game. The game is based on real life economy 
			and politics. Everything depends on resources, to build new companies, country infrastructure or conquer other countries.
			The more resources your country controls, the more powerful it is. In this game you are the boss of your own people, you can 
			train them and fight for your country or work on your own or other players factories, organize resistance wars in other 
			countries regions to join your country or reveal your own country from invaders. You can organize or join unions,
			create your own economic system and much more. Join us today!</p>
			
			<ul id="general_ul">
				<p>General</p>
				<li>1471 regions</li>
				<li>163 countries</li>
			</ul>
			
			<ul id="economy_ul">
				<p>Economy</p>
				<li>28 Unique products</li>
				<li>30 Unique buildings</li>
				<li>Each country has unique currency</li>
				<li>Tax on income</li>
				<li>Product import tax</li>
				<li>Internal product tax</li>
				<li>Can have more than 4500 different taxes</li>
				<li>Research more new buildings</li>
			</ul>
			
			<ul id="politics_ul">
				<p>Politics</p>
				<li>28 Unique laws
				<li>Import embargo on unique products</li>
				<li>Change president/congress term length</li>
				<li>Change political responsibilities</li>
				<li>Travel agreements</li>
				<li>Change government salaries</li>
				<li>Budget allocation</li>
				<li>and more...</li>
			</ul>
			
			<ul id="military_ul">
				<p>Military</p>
				<li>Conquer other countries</li>
				<li>Sign defence agreements</li>
				<li>Support your allies</li>
				<li>Build defence systems</li>
				<li>Reserach new defence systems</li>
			</ul>
			
			<ul id="comunication_ul">
				<p>Communication</p>
				<li>Chats</li>
				<li>Blogs</li>
				<li>Messages</li>
				<li>Notifications</li>
			</ul>
			
			<div id="slide_show_div">
				<p id="prev_img"><i class="fa fa-chevron-left" aria-hidden="true"></i></p>
				<img src="img/slide_1.png">
				<p id="next_img"><i class="fa fa-chevron-right" aria-hidden="true"></i></p>
				<p id="ssd_desc">Screenshots of the game.</p>
			</div>
		</div>
	</div>	

	<footer>
		<div id="footer_elements">
			<a href="en/privacy_policy" target="_blank">Privacy Policy</a>
			<a href="en/terms_of_service" target="_blank">Terms of Service</a>
			<p>Contact: vmundusgame@gmail.com</p>
		</div>
		<p id="allrr">All Rigths Reserved. &copy; 2017-<?php echo date('Y'); ?>.</p>
		<p id="up"><span class="fa fa-chevron-circle-up"></span></p>
	</footer>

</body>
</html>