<?php
	session_start();
	$file_name = 'main_index';
	
	//check if logged in
	include('connect_db.php');
	include('verify.php');
	include("php_functions/get_user_language.php"); //getUserLanguage();
	
	//check if language is set
	$lang = htmlentities(stripslashes(trim($_GET['lang'])), ENT_QUOTES);
	$query = "SELECT abbr FROM languages WHERE abbr = '$lang'";
	$result = $conn->query($query);
	$lang_abbr = null;
	if($result->num_rows == 1) {
		$row = $result->fetch_row();
		list($lang_abbr) = $row;
		setcookie("lang", $lang_abbr, time() + (86400 * 90), "/"); // 86400 = 1 day
	}
	else {
		$lang_abbr = getUserLanguage();
	}
	include('lang/m_' . $lang_abbr . '.php');
	
	
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
<!-- Copyright 2016 - <?php echo date('Y'); ?>. All rights reserved. Copying and/or distributing this code 
is strictly prohibited and any attempt to do so will be prosecuted. -->
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<meta name="keywords" content="vMundus, virtual world, new world">
	<meta name="author" content="vMundus">
	<meta name="copyright" content="vMundus">
	<meta name="name" content="vMundus | <?php echo $lang['meta_name']; ?>">
	<meta name="description" content="<?php echo $lang['meta_description']; ?>">
	<title>vMundus</title>
	<link rel="stylesheet" href="css/main_index.css?<?php echo filemtime('css/main_index.css'); ?>">
	<link rel="stylesheet" href="css/footer.css?<?php echo filemtime('css/footer.css'); ?>">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<?php 
		//check available languages for JAVASCRIPT.
		echo '<script src="lang/m_' . $lang_abbr . '.js"></script>';
	?>
	
	<script src="js/jquery.js"></script>
	<script src="js_etc/countdown_clock.js"></script>
	<script src="js_etc/class_elements.js?<?php echo filemtime('js_etc/class_elements.js'); ?>"></script>
	<script src="js_etc/custom_elements.js?<?php echo filemtime('js_etc/custom_elements.js'); ?>"></script>
	<script src="js_etc/submit_data.js"></script>
	<script src="js/main_script.js?<?php echo filemtime('js/main_script.js'); ?>"></script>
	<script src="js/footer.js?<?php echo filemtime('js/footer.js'); ?>"></script>
	
	<?php
		echo "\n\t" . '<link class="favicon" rel="icon" href="img/icon.png?' . filemtime('../img/icon.png') . '" type="image/png">';
	?>
</head>
<body>

	<!--top bar-->
	<div id="top">
		<img src="img/logo.png" id="logo">
		
		<div id="lang_bar_div">
			<?php
				$query = "SELECT language, flag, abbr FROM languages ORDER BY language";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($language, $flag, $lang_abbr) = $row;
					echo "\n\t\t\t" . '<abbr title="' . $language . '">' .
						 "\n\t\t\t\t" . '<a href="index?lang=' . $lang_abbr . '">' .
						 "\n\t\t\t\t\t" . '<img src="country_flags/' . $flag. '" abbr="' . $language . ' language" class="change_lang">' .
						 "\n\t\t\t\t" . '</a>' .
						 "\n\t\t\t" . '</abbr>';
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
				echo "$day_number";
			?>	
		</div>
	</div>
	
	<div id="beta">
		<p>Beta version.</p>
	</div>
	
	<!--Main content-->
	<div id="main">
		<?php
			$referer_id = htmlentities(stripslashes(trim($_GET['referer'])), ENT_QUOTES);
			if(!empty($referer_id)) {
				echo '<p id="referer_id" hidden>' . $referer_id . '</p>';
			}
			
			$reset_password = htmlentities(stripslashes(trim($_GET['reset_password'])), ENT_QUOTES);
			if(!empty($reset_password)) {
				echo '<p id="reset_pass" hidden>' . $reset_password . '</p>';
			}
		?>
		
		<div id="login_btn">
			<p><?php echo $lang['login']; ?> </p>
		</div>
	
		<?php 
			
			echo'<div id="register_btn">' .
					'<p>' . $lang['register'] . '</p>' .
				'</div>';
			
		?>
		
		<?php
			//active players
			//$query = "SELECT COUNT(*) FROM users WHERE last_active >= DATE_SUB(CURRENT_DATE, INTERVAL 120 DAY)";
			$query = "SELECT COUNT(*) FROM users";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($active_players) = $row;
			$active_players = number_format($active_players, 0, "", " ");
			echo "\n\t\t" . '<p id="active_players">' . $lang['active_palyers'] . ': ' . $active_players . '</p>';
		?>
		
		<div id="stat_div">
			<div id="sd_stat_menu">
				<img id="sd_work" src="img/dm_work.png">
				<img id="sd_fight" src="img/dm_fight.png">
				<img id="sd_regions" src="img/regions_stat.png">
			</div>
			
			<?php
				//productivity stat
				echo "\n\t\t\t" . '<div class="sd_details" id="sd_productivity">' .
					 "\n\t\t\t\t" . '<p class="sdd_head">Top countries by productivity for the last 10 days</p>';
			
				$query = "SELECT flag, country_name, SUM(productivity * price)/(SELECT price FROM product_price WHERE product_id = 1) 
						  AS prod_in_gold 
						  FROM region_product_productivity rpp, product_price pp, regions r, country c
						  WHERE rpp.region_id = r.region_id AND pp.product_id = rpp.product_id AND c.country_id = r.country_id
						  AND DATE_ADD(TIMESTAMP(date, time), INTERVAL 10 DAY) >= NOW()
						  GROUP BY r.country_id ORDER BY prod_in_gold DESC LIMIT 5";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($flag, $country_name, $productivity) = $row;
					
					echo "\n\t\t\t\t" . '<div class="sd_stat_details">' .
						 "\n\t\t\t\t\t" . '<img class="sd_flag" src="country_flags/' . $flag . '">' .
						 "\n\t\t\t\t\t" . '<p class="sd_country_name">' . $country_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="sd_amount">' . number_format($productivity, 2, ".", " ") . '</p>' .
						 "\n\t\t\t\t\t" . '<img class="sd_gold" src="img/gold.png">' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
				
				//countries damage stat
				echo "\n\t\t\t" . '<div class="sd_details" id="sd_millitary">' .
					 "\n\t\t\t\t" . '<p class="sdd_head">Top countries that can make daily the most damage.</p>';

				$query = "SELECT flag, country_name, SUM(damage_bonus + 10 + (level * 0.02)) *
						 (SELECT energy/10 FROM energy_consumption WHERE cons_id = 2) AS total_damage 
						  FROM (SELECT user_id, combat_exp * 0.01 AS damage_bonus FROM people) AS p, 
						  user_profile up, country c, user_levels ul
						  WHERE p.user_id = up.user_id AND c.country_id = citizenship AND ul.user_id = up.user_id
						  GROUP BY citizenship ORDER BY total_damage DESC LIMIT 5";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($flag, $country_name, $total_damage) = $row;
					
					echo "\n\t\t\t\t" . '<div class="sd_stat_details">' .
						 "\n\t\t\t\t\t" . '<img class="sd_flag" src="country_flags/' . $flag . '">' .
						 "\n\t\t\t\t\t" . '<p class="sd_country_name">' . $country_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="sd_amount">' . number_format($total_damage, 2, ".", " ") . '</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
				
				
				//countries damage stat
				echo "\n\t\t\t" . '<div class="sd_details" id="sd_regions_stat">' .
					 "\n\t\t\t\t" . '<p class="sdd_head">Top countries with the most regions.</p>';
			
				//create temp table with user lvls
				$query = "SELECT flag, country_name, COUNT(r.country_id) AS total_regions 
						  FROM country c, regions r
						  WHERE c.country_id = r.country_id
						  GROUP BY r.country_id ORDER BY total_regions DESC LIMIT 5";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($flag, $country_name, $total_regions) = $row;
					
					echo "\n\t\t\t\t" . '<div class="sd_stat_details">' .
						 "\n\t\t\t\t\t" . '<img class="sd_flag" src="country_flags/' . $flag . '">' .
						 "\n\t\t\t\t\t" . '<p class="sd_country_name">' . $country_name . '</p>' .
						 "\n\t\t\t\t\t" . '<p class="sd_amount">' . $total_regions . '</p>' .
						 "\n\t\t\t\t" . '</div>';
				}
				echo "\n\t\t\t" . '</div>';
			?>
			
		</div>
		
		<?php
			/*
			<div id="latest_posts">
				<a id="lp_heading" href="en/public_posts">Latest News</a>
				<div id="pl_posts_div" class="scroll_style">
				<?php
					$query = "SELECT bp.post_id, COUNT(CASE WHEN viewed = true THEN 1 END) AS views,
							title, user_name, bp.date
							FROM blog_posts bp, post_likes_views plv, user_blog ub, users u
							WHERE plv.post_id = bp.post_id AND ub.blog_id = bp.blog_id
							AND u.user_id = ub.user_id
							AND DATE_ADD(TIMESTAMP(bp.date, bp.time), INTERVAL 30 DAY) >= NOW()
							GROUP BY post_id
							ORDER BY date DESC, time DESC
							LIMIT 12";
					$result = $conn->query($query);
					while($row = $result->fetch_row()) {
						list($post_id, $views, $title, $blogger_name, $date) = $row;
						$post_date = date('M j', strtotime($date));
						
						echo "\n\t\t" . '<a class="ppd_post_name" href="en/public_posts?post_id=' . $post_id . '">' .
							"\n\t\t\t" . '<p class="ppd_post_title">' . $title . '</p>' .
							"\n\t\t\t" . '<p class="ppd_blogger_name">' . $blogger_name . '</p>' .
							"\n\t\t\t" . '<p class="ppd_post_data">' . $post_date . '</p>' .
							"\n\t\t" . '</a>';
					}
				?>
				</div>
			</div>
			*/
		?>

		<?php
			/*
			$coming_on = "2020-02-20 22:00:00";
			
			$date1 = new DateTime(date('Y-m-d') . ' ' . date('H:i:s'));
			$date2 = new DateTime($coming_on);
			$diff = date_diff($date1,$date2);
			$come_in_days = $diff->format("%a");
			$come_in = $diff->format("%H:%I:%S");
			
			echo'<div id="com_soon_div">' .
				'<p id="coming_in">Coming In <span>' . $come_in_days . ' Days ' . $come_in . '</span></p>' .
				'</div>';
			*/
		?>

		<div id="popup_div">
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
		<div id="fe_col1">
			<p id="fe_col1_privacy_lbl">Privacy</p>
			<a id="privacy_policy" href="en/privacy_policy" target="_blank">Privacy Policy</a>
			<a id="terms_of_service" href="en/terms_of_service" target="_blank">Terms of Service</a>
		</div>
		
		<div id="fe_col2">
			<p id="fe_col2_contact_lbl">Contact</p>
			<p id="fe_col2_email">vmundusgame@gmail.com</p>
			<a id="discord_link" href="https://discord.gg/QvP4fNT" target="_blank">Discord</a>
		</div>	
		
		<div id="fe_col3">
			<p id="fe_col2_about_lbl">About</p>
			<a id="fe_col2_wiki" href="wiki/index" target="_blank">Game Wiki</a>
		</div>	
	</div>
	<p id="allrr">All Rights Reserved. &copy; 2017-<?php echo date('Y'); ?></p>
	<p id="up"><span class="fa fa-chevron-circle-up"></span></p>
</footer>

</body>
</html>
<?php
	mysqli_close($conn);
?>