<?php
	include('connect_db.php');
	include('php_functions/get_ip.php');//getIP();
	
	//check if site under maintenance
	$query = "SELECT end_date, end_time FROM maintenance_info WHERE is_under_maintenance = TRUE
			  AND end_date >= CURRENT_DATE AND end_time > CURRENT_TIME";
	$result = $conn->query($query);
	if($result->num_rows != 1) {
		//check if loged in and redirect to the right page
		include('verify.php');
	}
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_row($result);
	list($end_date, $end_time) = $row;	
	
	$ip = getIP();
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit("You are not allowed to view this page.");
	}

	include("php_functions/get_user_language.php"); //getUserLanguage();
	require_once "php_functions/register_page_visitors.php"; //registerPageVisitors('$file_name', $visitor_ip));
	registerPageVisitors('maintenance', $ip);
	
	include('lang/m_' . getUserLanguage() . '.php');

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
	<link rel="stylesheet" href="css/maintenance.css?1">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Slabo+27px" rel="stylesheet">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<?php 
		//check available languages for JAVASCRIPT.
		echo '<script src="lang/m_' . getUserLanguage() . '.js"></script>';
	?>
	
	<script src="js/jquery.js"></script>
	<script src="js_etc/countdown_clock.js"></script>
	<script src="js_etc/submit_data.js"></script>
	<script src="js/maintenance.js?1"></script>
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
			<?php
				$query = "SELECT language, flag, lang_id FROM languages ORDER BY language";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($language, $flag, $lang_id) = $row;
					echo "\n\t\t\t" . '<abbr title="' . $language . '"><img src="country_flags/' . $flag. 
									  '" abbr="' . $language . ' language" class="change_lang"><p hidden>' . $lang_id . '</p></abbr>';
				}
			?>
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
		
			//get maintenance end time
			$end_on = "$end_date $end_time";
		
			$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
			$date2 = new DateTime($end_on);
			$diff = date_diff($date1,$date2);
			$end_in_days = $diff->format("%a");
			$end_in = $diff->format("%H:%I:%S");
			
			echo "\n\t\t" . '<div id="maint_end_div">' .
				 "\n\t\t\t" . '<p id="end_in">Maintenance End In <span>' . $end_in_days . ' days ' . $end_in . '</span></p>' .
				 "\n\t\t" . '</div>';
			?>
			
		</div>
	</div>	

	<p id="about_head"><?php echo $lang['about_the_game']; ?></p>
	
	<div id="desc_div">
		<div id="desc_div_desc">
			<p id="short_desc"><?php echo $lang['description']; ?></p>
			
			<ul id="general_ul">
				<p><?php echo $lang['general']; ?></p>
				<li>1471 <?php echo $lang['regions']; ?></li>
				<li>163 <?php echo $lang['countries']; ?></li>
			</ul>
			
			<ul id="politics_ul">
				<p><?php echo $lang['politics']; ?></p>
				<li>28 <?php echo $lang['unique_laws']; ?></li>
				<li><?php echo $lang['import_embargo_on_unique_products']; ?></li>
				<li><?php echo $lang['change_president_congress_term_length']; ?></li>
				<li><?php echo $lang['change_political_responsibilities']; ?></li>
				<li><?php echo $lang['travel_agreements']; ?></li>
				<li><?php echo $lang['change_government_salaries']; ?></li>
				<li><?php echo $lang['budget_allocation']; ?></li>
			</ul>
			
			<ul id="economy_ul">
				<p><?php echo $lang['economy']; ?></p>
				<li>28 <?php echo $lang['unique_products']; ?></li>
				<li>30 <?php echo $lang['unique_buildings']; ?></li>
				<li><?php echo $lang['each_country_has_unique_currency']; ?></li>
				<li><?php echo $lang['tax_on_income']; ?></li>
				<li><?php echo $lang['product_import_tax']; ?></li>
				<li><?php echo $lang['internal_product_tax']; ?></li>
				<li><?php echo $lang['countries_can_have_more_than_4500_taxes']; ?></li>
				<li><?php echo $lang['research_new_technologies']; ?></li>
				<li><?php echo $lang['banking_system']; ?></li>
				<li><?php echo $lang['corporations']; ?></li>
				<li><?php echo $lang['and_more']; ?>...</li>
			</ul>
			
			<ul id="military_ul">
				<p><?php echo $lang['military']; ?></p>
				<li><?php echo $lang['conquer_other_countries']; ?></li>
				<li><?php echo $lang['sign_defence_agreements']; ?></li>
				<li><?php echo $lang['support_your_allies']; ?></li>
				<li><?php echo $lang['build_defence_systems']; ?></li>
				<li><?php echo $lang['research_new_defence_systems']; ?></li>
			</ul>
			
			<ul id="comunication_ul">
				<p><?php echo $lang['communication']; ?></p>
				<li><?php echo $lang['chats']; ?></li>
				<li><?php echo $lang['blogs']; ?></li>
				<li><?php echo $lang['messages']; ?></li>
				<li><?php echo $lang['notifications']; ?></li>
			</ul>
			
			<div id="slide_show_div">
				<p id="prev_img"><i class="fa fa-chevron-left" aria-hidden="true"></i></p>
				<img src="img/slide_1.png">
				<p id="next_img"><i class="fa fa-chevron-right" aria-hidden="true"></i></p>
				<p id="ssd_desc"><?php echo $lang['screenshots_of_the_game']; ?></p>
			</div>
		</div>
	</div>

	<footer>
		<div id="footer_elements">
			<a href="en/privacy_policy" target="_blank">Privacy Policy</a>
			<a href="en/terms_of_service" target="_blank">Terms of Service</a>
			<p>Contact: vmundusgame@gmail.com</p>
		</div>
		<p id="allrr"><?php echo $lang['all_rights_reserved']; ?>. &copy; 2017-<?php echo date('Y'); ?>.</p>
	</footer>

</body>
</html>