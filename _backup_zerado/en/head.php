<?php
	session_start();
	
	//check if logged in
	include('../connect_db.php');
	include('../verify.php');
	include('../php_functions/correct_date_time.php');//correctTime($time, $country_id = 0, $user_id = 0),
													  //correctDate($date, $time, $country_id = 0, $user_id = 0)
	include('../php_functions/get_user_language.php'); //getUserLanguage();
	include('../lang/' . getUserLanguage() . '.php');
	
	$user_id = $_SESSION['user_id'];
	
	//update last active date
	$query = "UPDATE users SET last_active = CURRENT_DATE where user_id = '$user_id'";
	$conn->query($query);

	$query = "UPDATE users SET last_active_time = CURRENT_TIME where user_id = '$user_id'";
	$conn->query($query);
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
	<meta name="description" content="Online Strategy Game that allows players to rule a country, have own business empire and/or 
	conquer other countries.">
	<title><?php	
		$p_title = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
		$company_manage_reg = '/\/en\/company_manage\??id=[0-9]{1,23}/';
		$show_message_reg = '/\/en\/show_message\??id=[0-9]{1,23}/';
		$country_reg = '/\/en\/country(?!_manage)(\??country_id=[0-9]{1,3})?/';
		$party_info_reg = '/\/en\/party_info(\??party_id=[0-9]{1,23})?/';
		$union_info_reg = '/\/en\/union_info(\??union_id=[0-9]{1,23})?/';
		$map_reg = '/\/en\/map\??map_type=[a-z]{6,7}?/';
		$battle_reg = '/\/en\/battle\??(currency_id=[0-9]{1,3})&battle_id=[0-9]{1,23}&side=[a-z]{5,10}?/';
		$region_info_reg = '/\/en\/region_info\??region_id=[0-9]{1,3}?/';
		$user_profile_reg = '/\/en\/user_profile\??id=[0-9]{0,7}?/';
		$blog_reg = '/\/en\/blog_info(\?)?(blog_id=[0-9]{1,23})?(post_id=[0-9]{1,23})?/';
		$corporation_info_reg = '/\/en\/corporation_info(\??corp_id=[0-9]{1,23})?/';
		$product_market_reg = '/\/en\/product_market(\??corp_id=[0-9]{1,23})?/';
		$currency_exchange_reg = '/\/en\/currency_exchange(\??corp_id=[0-9]{1,23})?/';
		$company_market_reg = '/\/en\/company_market(\??corp_id=[0-9]{1,23})?/';
		
		if($p_title == '/en/index' || 
		   $p_title == '/en/'){
			echo 'HOME';
		}
		elseif($p_title == '/en/settings') {
			echo 'Settings';
		}
		elseif(preg_match($map_reg, $p_title)) {
			echo 'Map';
		}
		elseif($p_title == '/en/chat') {
			echo 'Chat';
		}
		elseif($p_title == '/en/companies') {
			echo 'Companies';
		}
		elseif(preg_match($company_manage_reg, $p_title)) {
			echo 'Company Manage';
		}
		elseif(preg_match($currency_exchange_reg, $p_title)) {
			echo 'Currency Exchange';
		}
		elseif($p_title == '/en/messages') {
			echo 'Messages';
		}
		elseif(preg_match($show_message_reg, $p_title)) {
			echo "Message";
		}
		elseif($p_title == '/en/warehouse') {
			echo 'Warehouse';
		}
		elseif(preg_match($product_market_reg, $p_title)) {
			echo 'Product Market';
		}
		elseif($p_title == '/~petro/vmundus/en/people') {
			echo 'People';
		}
		elseif($p_title == '/en/job_offers') {
			echo "Jobs";
		}
		elseif($p_title == '/en/work') {
			echo "Work";
		}
		elseif(preg_match($country_reg, $p_title)) {
			echo "Country";
		}
		elseif($p_title == '/en/elections') {
			echo "Elections";
		}
		elseif(preg_match($party_info_reg, $p_title)) {
			echo "Party Info";
		}
		elseif($p_title == '/en/parties') {
			echo "Parties";
		}
		elseif($p_title == '/en/country_manage') {
			echo "Country Manage";
		}
		elseif(preg_match($union_info_reg, $p_title)) {
			echo "Union Info";
		}
		elseif($p_title == '/en/unions') {
			echo "Unions";
		}
		elseif($p_title == '/en/active_battles') {
			echo "Battles";
		}
		elseif(preg_match($battle_reg, $p_title)) {
			echo "Battle";
		}
		elseif($p_title == '/en/manage_battles') {
			echo "Manage Battles";
		}
		elseif(preg_match($region_info_reg, $p_title)) {
			echo "Region Info";
		}
		elseif(preg_match($user_profile_reg, $p_title)) {
			echo "User profile";
		}
		elseif($p_title == '/en/blogs') {
			echo "Blogs";
		}
		elseif(preg_match($blog_reg, $p_title)) {
			echo "Blog";
		}
		elseif($p_title == '/en/players') {
			echo "Players";
		}
		elseif($p_title == '/en/calculator') {
			echo "Calculator";
		}
		elseif($p_title == '/en/manage_game') {
			echo "Manage";
		}
		elseif($p_title == '/en/market') {
			echo "Market";
		}
		elseif($p_title == '/en/updates') {
			echo "Updates";
		}
		elseif($p_title == '/en/translation') {
			echo "Translation";
		}
		elseif($p_title == '/en/gold_market') {
			echo "Gold Market";
		}
		elseif($p_title == '/en/corporations') {
			echo "Corporations";
		}
		elseif(preg_match($corporation_info_reg, $p_title)) {
			echo "Corporation";
		}
		elseif(preg_match($company_market_reg, $p_title)) {
			echo "Company market";
		}
		elseif($p_title == '/en/bank') {
			echo "Bank";
		}
		elseif($p_title == '/en/latest_posts') {
			echo "Latest Posts";
		}
		elseif($p_title == '/en/millitary_stat') {
			echo "Statistics";
		}
		elseif($p_title == '/en/black_market') {
			echo "Black market";
		}
		elseif($p_title == '/en/research') {
			echo "Research";
		}
	?></title>
	<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Goudy+Bookletter+1911" rel="stylesheet">
	
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/v4-shims.css">
	
	<link rel="stylesheet" href="../css/bootstrap.css">
	<link rel="stylesheet" href="../css/navigation.css?<?php echo filemtime('../css/navigation.css'); ?>">
	<link rel="stylesheet" href="../css/user_info.css?<?php echo filemtime('../css/user_info.css'); ?>">
	<link rel="stylesheet" href="../css/footer.css?<?php echo filemtime('../css/footer.css'); ?>">
	<link rel="stylesheet" href="../css/global_css.css?<?php echo filemtime('../css/global_css.css'); ?>">
	<?php 
		if($p_title == '/en/index' || 
		   $p_title == '/en/'){
			echo '<link rel="stylesheet" href="../css/home.css?' . filemtime('../css/home.css') . '">';
			echo "\n\t" . '<link rel="stylesheet" href="../css/right_side.css?' . filemtime('../css/right_side.css') . '">';
		}
		elseif($p_title == '/en/settings') {
			echo '<link rel="stylesheet" href="../css/settings.css?' . filemtime('../css/settings.css') . '">';
			echo "\n\t\t" . '<link rel="stylesheet" href="../css/right_side.css?' . filemtime('../css/right_side.css') . '">';
		}
		elseif(preg_match($map_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/map.css?' . filemtime('../css/map.css') . '">';
		}
		elseif($p_title == '/en/chat') {
			echo '<link rel="stylesheet" href="../css/chat.css?' . filemtime('../css/chat.css') . '">';
			echo "\n\t\t" . '<link rel="stylesheet" href="../css/right_side.css?' . filemtime('../css/right_side.css') . '">';
		}
		elseif($p_title == '/en/companies') {
			echo '<link rel="stylesheet" href="../css/companies.css?' . filemtime('../css/companies.css') . '">';
		}
		elseif(preg_match($company_manage_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/company_manage.css?' . filemtime('../css/company_manage.css') . '">';
		}
		elseif($p_title == '/en/messages') {
			echo '<link rel="stylesheet" href="../css/messages.css?' . filemtime('../css/messages.css') . '">';
		}
		elseif(preg_match($show_message_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/show_message.css?' . filemtime('../css/show_message.css') . '">';
			echo "\n\t" . '<link rel="stylesheet" href="../css/right_side.css?' . filemtime('../css/right_side.css') . '">';
		}
		elseif($p_title == '/en/warehouse') {
			echo '<link rel="stylesheet" href="../css/warehouse.css?' . filemtime('../css/warehouse.css') . '">';
		}
		elseif(preg_match($product_market_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/product_market.css?' . filemtime('../css/product_market.css') . '">';
		}
		elseif($p_title == '/en/people') {
			echo '<link rel="stylesheet" href="../css/people.css?' . filemtime('../css/people.css') . '">';
		}
		elseif($p_title == '/en/job_offers') {
			echo '<link rel="stylesheet" href="../css/job_offers.css?' . filemtime('../css/job_offers.css') . '">';
		}
		elseif($p_title == '/en/work') {
			echo '<link rel="stylesheet" href="../css/work.css?' . filemtime('../css/work.css') . '">';
		}
		elseif(preg_match($currency_exchange_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/currency_exchange.css?' . filemtime('../css/currency_exchange.css') . '">';
		}
		elseif(preg_match($country_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/country.css?' . filemtime('../css/country.css') . '">';
		}
		elseif($p_title == '/en/elections') {
			echo '<link rel="stylesheet" href="../css/elections.css?' . filemtime('../css/elections.css') . '">';
		}
		elseif(preg_match($party_info_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/party_info.css?' . filemtime('../css/party_info.css') . '">';
		}
		elseif($p_title == '/en/parties') {
			echo '<link rel="stylesheet" href="../css/parties.css?' . filemtime('../css/parties.css') . '">';
		}
		elseif($p_title == '/en/country_manage') {
			echo '<link rel="stylesheet" href="../css/country_manage.css?' . filemtime('../css/country_manage.css') . '">';
		}
		elseif(preg_match($union_info_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/union_info.css?' . filemtime('../css/union_info.css') . '">';
		}
		elseif($p_title == '/en/unions') {
			echo '<link rel="stylesheet" href="../css/unions.css?' . filemtime('../css/unions.css') . '">';
		}
		elseif($p_title == '/en/active_battles') {
			echo '<link rel="stylesheet" href="../css/active_battles.css?' . filemtime('../css/active_battles.css') . '">';
		}
		elseif(preg_match($battle_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/battle.css?' . filemtime('../css/battle.css') . '">';
		}
		elseif($p_title == '/en/manage_battles') {
			echo '<link rel="stylesheet" href="../css/manage_battles.css?' . filemtime('../css/manage_battles.css') . '">';
		}
		elseif(preg_match($region_info_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/region_info.css?' . filemtime('../css/region_info.css') . '">';
		}
		elseif(preg_match($user_profile_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/user_profile.css?' . filemtime('../css/user_profile.css') . '">';
		}
		elseif($p_title == '/en/blogs') {
			echo '<link rel="stylesheet" href="../css/blogs.css?' . filemtime('../css/blogs.css') . '">';
		}
		elseif(preg_match($blog_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/blog_info.css?' . filemtime('../css/blog_info.css') . '">';
		}
		elseif($p_title == '/en/players') {
			echo '<link rel="stylesheet" href="../css/players.css?' . filemtime('../css/players.css') . '">';
		}
		elseif($p_title == '/en/calculator') {
			echo '<link rel="stylesheet" href="../css/calculator.css?' . filemtime('../css/calculator.css') . '">';
			echo "\n\t" . '<link rel="stylesheet" href="../css/right_side.css?' . filemtime('../css/right_side.css') . '">';
		}
		elseif($p_title == '/en/manage_game') {
			echo '<link rel="stylesheet" href="../css/manage_game.css?' . filemtime('../css/manage_game.css') . '">';
		}
		elseif($p_title == '/en/market') {
			echo '<link rel="stylesheet" href="../css/market.css?' . filemtime('../css/market.css') . '">';
		}
		elseif($p_title == '/en/updates') {
			echo '<link rel="stylesheet" href="../css/updates.css?' . filemtime('../css/updates.css') . '">';
		}
		elseif($p_title == '/en/translation') {
			echo '<link rel="stylesheet" href="../css/translation.css?' . filemtime('../css/translation.css') . '">';
		}
		elseif($p_title == '/en/gold_market') {
			echo '<link rel="stylesheet" href="../css/gold_market.css?' . filemtime('../css/gold_market.css') . '">';
			echo "\n\t\t" . '<link rel="stylesheet" href="../css/right_side.css?' . filemtime('../css/right_side.css') . '">';
		}
		elseif($p_title == '/en/corporations') {
			echo '<link rel="stylesheet" href="../css/corporations.css?' . filemtime('../css/corporations.css') . '">';
		}
		elseif(preg_match($corporation_info_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/corporation_info.css?' . filemtime('../css/corporation_info.css') . '">';
		}
		elseif(preg_match($company_market_reg, $p_title)) {
			echo '<link rel="stylesheet" href="../css/company_market.css?' . filemtime('../css/company_market.css') . '">';
		}
		elseif($p_title == '/en/bank') {
			echo '<link rel="stylesheet" href="../css/bank.css?' . filemtime('../css/bank.css') . '">';
		}
		elseif($p_title == '/en/latest_posts') {
			echo '<link rel="stylesheet" href="../css/latest_posts.css?' . filemtime('../css/latest_posts.css') . '">';
		}
		elseif($p_title == '/en/millitary_stat') {
			echo '<link rel="stylesheet" href="../css/millitary_stat.css?' . filemtime('../css/millitary_stat.css') . '">';
		}
		elseif($p_title == '/en/countries_productivity_stat') {
			echo '<link rel="stylesheet" href="../css/countries_productivity_stat.css?' . 
				  filemtime('../css/countries_productivity_stat.css') . '">';
		}
		elseif($p_title == '/en/black_market') {
			echo '<link rel="stylesheet" href="../css/black_market.css?' . filemtime('../css/black_market.css') . '">';
		}
		elseif($p_title == '/en/research') {
			echo '<link rel="stylesheet" href="../css/research.css?' . filemtime('../css/research.css') . '">';
		}
	?>
	
	<script src="../js/jquery.js"></script>
	<script src="../js_etc/colors.js?<?php echo filemtime('../js_etc/colors.js'); ?>"></script>
	<script src="../js/navigation.js?<?php echo filemtime('../js/navigation.js'); ?>"></script>
	<script src="../js/footer.js?<?php echo filemtime('../js/footer.js'); ?>"></script>
	<script src="../js_etc/load_doc_ajax.js?<?php echo filemtime('../js_etc/load_doc_ajax.js'); ?>"></script>
	<script src="../js_etc/submit_data.js?<?php echo filemtime('../js_etc/submit_data.js'); ?>"></script>
	<script src="../js_etc/server_request.js?<?php echo filemtime('../js_etc/server_request.js'); ?>"></script>
	<script src="../js_etc/custom_functions.js?<?php echo filemtime('../js_etc/custom_functions.js'); ?>"></script>
	<script src="../js/user_info.js?<?php echo filemtime('../js/user_info.js'); ?>"></script>
	<script src="../js/tutorials.js?<?php echo filemtime('../js/tutorials.js'); ?>"></script>
	<script src="../js_etc/custom_elements.js?<?php echo filemtime('../js_etc/custom_elements.js'); ?>"></script>
	<script src="../js_etc/class_elements.js?<?php echo filemtime('../js_etc/class_elements.js'); ?>"></script>
	<?php
		echo '<script src="../lang/' . getUserLanguage() . '.js?' . filemtime('../lang/' . getUserLanguage() . '.js') . '"></script>' .
			 "\n\t" ;
		
		if($p_title == '/en/index' || 
		   $p_title == '/en/'){
			echo '<script src="../js/home.js?' . filemtime('../js/home.js') . '"></script>';
			echo "\n\t" . '<script src="../js/right_side.js?' . filemtime('../js/right_side.js') . '"></script>';
		}
		elseif($p_title == '/en/settings') {
			echo '<script src="../js/settings.js?' . filemtime('../js/settings.js') . '"></script>';
			echo "\n\t" . '<script src="../js/right_side.js?' . filemtime('../js/right_side.js') . '"></script>';
		}
		elseif(preg_match($map_reg, $p_title)) {
			echo '<script src="../js/map.js?' . filemtime('../js/map.js') . '"></script>';
		}
		elseif($p_title == '/en/chat') {
			echo '<script src="../js/chat.js?' . filemtime('../js/chat.js') . '"></script>';
			echo "\n\t" . '<script src="../js/right_side.js?' . filemtime('../js/right_side.js') . '"></script>';
		}
		elseif($p_title == '/en/companies') {
			echo '<script src="../js/companies.js?' . filemtime('../js/companies.js') . '"></script>';
		}
		elseif(preg_match($company_manage_reg, $p_title)) {
			echo '<script src="../js/company_manage.js?' . filemtime('../js/company_manage.js') . '"></script>';
		}
		elseif($p_title == '/en/messages') {
			echo '<script src="../js/messages.js?' . filemtime('../js/messages.js') . '"></script>';
			echo "\n\t" . '<script src="../ckeditor/ckeditor.js?' . filemtime('../ckeditor/ckeditor.js') . '"></script>';
			echo "\n\t" . '<script> CKEDITOR.timestamp=' . filemtime('../ckeditor/config.js') . '; </script>';
		}
		elseif(preg_match($show_message_reg, $p_title)) {
			echo '<script src="../js/show_message.js?' . filemtime('../js/show_message.js') . '"></script>';
			echo "\n\t" . '<script src="../js/right_side.js?' . filemtime('../js/right_side.js') . '"></script>';
			echo "\n\t" . '<script src="../ckeditor/ckeditor.js?' . filemtime('../ckeditor/ckeditor.js') . '"></script>';
			echo "\n\t" . '<script> CKEDITOR.timestamp=' . filemtime('../ckeditor/config.js') . '; </script>';
		}	
		elseif($p_title == '/en/warehouse') {
			echo '<script src="../js/warehouse.js?' . filemtime('../js/warehouse.js') . '"></script>';
		}
		elseif(preg_match($product_market_reg, $p_title)) {
			echo '<script src="../js/product_market.js?' . filemtime('../js/product_market.js') . '"></script>';
		}
		elseif($p_title == '/en/people') {
			echo '<script src="../js/people.js?' . filemtime('../js/people.js') . '"></script>';
		}
		elseif($p_title == '/en/job_offers') {
			echo '<script src="../js/job_offers.js?' . filemtime('../js/job_offers.js') . '"></script>';
		}
		elseif($p_title == '/en/work') {
			echo '<script src="../js/work.js?' . filemtime('../js/work.js') . '"></script>';
		}
		elseif(preg_match($currency_exchange_reg, $p_title)) {
			echo '<script src="../js/currency_exchange.js?' . filemtime('../js/currency_exchange.js') . '"></script>';
		}
		elseif(preg_match($country_reg, $p_title)) {
			echo '<script src="../js/country.js?' . filemtime('../js/country.js') . '"></script>';
		}
		elseif($p_title == '/en/elections') {
			echo '<script src="../js/elections.js?' . filemtime('../js/elections.js') . '"></script>';
		}
		elseif(preg_match($party_info_reg, $p_title)) {
			echo '<script src="../js/party_info.js?' . filemtime('../js/party_info.js') . '"></script>';
		}
		elseif($p_title == '/en/parties') {
			echo '<script src="../js/parties.js?' . filemtime('../js/parties.js') . '"></script>';
		}
		elseif($p_title == '/en/country_manage') {
			echo '<script src="../js/country_manage.js?' . filemtime('../js/country_manage.js') . '"></script>';
			echo "\n\t" . '<script src="../ckeditor/ckeditor.js?' . filemtime('../ckeditor/ckeditor.js') . '"></script>';
			echo "\n\t" . '<script> CKEDITOR.timestamp=' . filemtime('../ckeditor/config.js') . '; </script>';
		}
		elseif(preg_match($union_info_reg, $p_title)) {
			echo '<script src="../js/union_info.js?' . filemtime('../js/union_info.js') . '"></script>';
		}
		elseif($p_title == '/en/unions') {
			//echo '<script src="../js/unions.js?' . filemtime('../js/unions.js') . '"></script>';
		}
		elseif($p_title == '/en/active_battles') {
			echo '<script src="../js/active_battles.js?' . filemtime('../js/active_battles.js') . '"></script>';
		}
		elseif(preg_match($battle_reg, $p_title)) {
			echo '<script src="../js/battle.js?' . filemtime('../js/battle.js') . '"></script>';
		}
		elseif($p_title == '/en/manage_battles') {
			echo '<script src="../js/manage_battles.js?' . filemtime('../js/manage_battles.js') . '"></script>';
		}
		elseif(preg_match($region_info_reg, $p_title)) {
			echo "\n\t" . '<script src="../js/region_info.js?' . filemtime('../js/region_info.js') . '"></script>';
		}
		elseif(preg_match($user_profile_reg, $p_title)) {
			echo '<script src="../js/user_profile.js?' . filemtime('../js/user_profile.js') . '"></script>';
			echo "\n\t" . '<script src="../ckeditor/ckeditor.js?' . filemtime('../ckeditor/ckeditor.js') . '"></script>';
			echo "\n\t" . '<script> CKEDITOR.timestamp=' . filemtime('../ckeditor/config.js') . '; </script>';
		}
		elseif($p_title == '/en/blogs') {
			echo '<script src="../js/blogs.js?' . filemtime('../js/blogs.js') . '"></script>';
		}
		elseif(preg_match($blog_reg, $p_title)) {
			echo '<script src="../js/blog_info.js?' . filemtime('../js/blog_info.js') . '"></script>';
			echo "\n\t" . '<script src="../ckeditor/ckeditor.js?' . filemtime('../ckeditor/ckeditor.js') . '"></script>';
			echo "\n\t" . '<script> CKEDITOR.timestamp=' . filemtime('../ckeditor/config.js') . '; </script>';
		}
		elseif($p_title == '/en/players') {
			echo '<script src="../js/players.js?' . filemtime('../js/players.js') . '"></script>';
		}
		elseif($p_title == '/en/calculator') {
			echo '<script src="../js/calculator.js?' . filemtime('../js/calculator.js') . '"></script>';
			echo "\n\t" . '<script src="../js/right_side.js?' . filemtime('../js/right_side.js') . '"></script>';
		}
		elseif($p_title == '/en/manage_game') {
			echo '<script src="../js/manage_game.js?' . filemtime('../js/manage_game.js') . '"></script>';
		}
		elseif($p_title == '/en/market') {
			echo '<script src="../js/market.js?' . filemtime('../js/market.js') . '"></script>';
		}
		elseif($p_title == '/en/translation') {
			echo '<script src="../js/translation.js?' . filemtime('../js/translation.js') . '"></script>';
		}
		elseif($p_title == '/en/gold_market') {
			echo '<script src="../js/gold_market.js?' . filemtime('../js/gold_market.js') . '"></script>';
			echo "\n\t" . '<script src="../js/right_side.js?' . filemtime('../js/right_side.js') . '"></script>';
		}
		elseif($p_title == '/en/corporations') {
			echo '<script src="../js/corporations.js?' . filemtime('../js/corporations.js') . '"></script>';
		}
		elseif(preg_match($corporation_info_reg, $p_title)) {
			echo '<script src="../js/corporation_info.js?' . filemtime('../js/corporation_info.js') . '"></script>';
		}
		elseif(preg_match($company_market_reg, $p_title)) {
			echo '<script src="../js/company_market.js?' . filemtime('../js/company_market.js') . '"></script>';
		}
		elseif($p_title == '/en/bank') {
			echo '<script src="../js/bank.js?' . filemtime('../js/bank.js') . '"></script>';
		}
		elseif($p_title == '/en/black_market') {
			echo '<script src="../js/black_market.js?' . filemtime('../js/black_market.js') . '"></script>';
		}
		elseif($p_title == '/en/research') {
			echo '<script src="../js/research.js?' . filemtime('../js/research.js') . '"></script>';
		}
	//favIcons
	echo "\n\t" . '<link id="favicon" rel="icon" href="../img/icon.png?' . filemtime('../img/icon.png') . '" type="image/png">';
	?>
	
</head>

<body>
<div id="page_body">
<div id="navigation">
<nav>
	<a href="index"><img src="../img/in_logo.png?<?php echo filemtime('../img/in_logo.png'); ?>" id="logo" alt="logo"></a>
	<ul>
		<?php
			echo "\n\t\t" . '<li id="economy">' .
							$lang['economy'] .
				 "\n\t\t\t" . '<ul class="submenu">' .
				 "\n\t\t\t\t" . '<a href="bank"><li class="subli">Bank</li></a>' .
				 "\n\t\t\t\t" . '<a href="black_market"><li class="subli">Black market</li></a>' .
				 "\n\t\t\t\t" . '<a href="calculator"><li class="subli">' . $lang['calculator'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="companies"><li class="subli">' . $lang['companies'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="company_market"><li class="subli">' . $lang['company_market'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="corporations"><li class="subli">' . $lang['corporations'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="currency_exchange"><li class="subli">' . $lang['currency_exchange'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="gold_market"><li class="subli">' . $lang['gold_market'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="job_offers"><li class="subli">' . $lang['job_offers'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="product_market"><li class="subli">' . $lang['product_market'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="people"><li class="subli">' . $lang['people'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="research"><li class="subli">Research</li></a>' .
				 "\n\t\t\t\t" . '<a href="warehouse"><li class="subli">' . $lang['warehouse'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="work"><li class="subli">' . $lang['work'] . '</li></a>' .
				 "\n\t\t\t" . '</ul>' .
				 "\n\t\t" . '</li>' .
				 "\n\t\t" . '<li id="military">' .
						    $lang['military'] .
				 "\n\t\t\t" . '<ul class="submenu">' .
				 "\n\t\t\t\t" . '<a href="active_battles"><li class="subli">' . $lang['active_battles'] . '</li></a>' .
				 "\n\t\t\t" . '</ul>' .
				 "\n\t\t" . '</li>' .
				 "\n\t\t" . '<li id="politics">' .
						    $lang['politics'] .
				 "\n\t\t\t" . '<ul class="submenu">' .
				 "\n\t\t\t\t" . '<a href="country"><li class="subli">' . $lang['country'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="elections"><li class="subli">' . $lang['elections'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="party_info"><li class="subli">' . $lang['party_info'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="parties"><li class="subli">' . $lang['parties'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="union_info"><li class="subli">' . $lang['union_info'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="unions"><li class="subli">' . $lang['unions'] . '</li></a>' .
				 "\n\t\t\t" . '</ul>' .
				 "\n\t\t" . '</li>' .
				 "\n\t\t" . '<li id="statistics">' .
						    $lang['statistics'] .
				 "\n\t\t\t" . '<ul class="submenu">' .
				 "\n\t\t\t\t" . '<a href="countries_productivity_stat"><li class="subli">Productivity</li></a>' .
				 "\n\t\t\t\t" . '<a href="millitary_stat"><li class="subli">Millitary</li></a>' .
				 "\n\t\t\t" . '</ul>' .
				 "\n\t\t" . '</li>' .
				 "\n\t\t" . '<li id="social">' .
						    $lang['social'] .
				 "\n\t\t\t" . '<ul class="submenu">' .
				 "\n\t\t\t\t" . '<a href="blogs"><li class="subli">' . $lang['blogs'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="chat"><li class="subli">' . $lang['chat'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="latest_posts"><li class="subli">Latest Posts</li></a>' .
				 "\n\t\t\t\t" . '<a href="players"><li class="subli">' . $lang['players'] . '</li></a>' .
				 "\n\t\t\t" . '</ul>' .
				 "\n\t\t" . '</li>' .
				 "\n\t\t" . '<li id="map">' .
						    $lang['map'] .
				 "\n\t\t\t" . '<ul class="submenu">' .
				 "\n\t\t\t\t" . '<a href="map?map_type=regular"><li class="subli">' . $lang['countries_map'] . '</li></a>' .
				 "\n\t\t\t\t" . '<a href="map?map_type=unions"><li class="subli">' . $lang['unions_map'] . '</li></a>' .
				 "\n\t\t\t" . '</ul>' .
				 "\n\t\t" . '</li>' .
				 "\n\t\t" . '<a href="market"><li id="store">' . $lang['store'] . '</li></a>';
		?>
	</ul>
</nav>

<div id="fixed_nav">
	<div id="mess_notif_div">
		<a id="message" href="../en/messages">
			<abbr title="messages">
				<span class="glyphicon glyphicon-envelope"></span>
				<p id="msg_count"></p>
			</abbr>
		</a>
		
		<a id="notification" href="../en/messages">
			<abbr title="notifications">
				<span class="fa fa-bell"></span>
				<p id="noti_count"></p>
			</abbr>
		</a>
		
		<?php
			//check if governor.
			$is_governor = false;
			$query = "SELECT name, country_id FROM government_positions gp, country_government cg 
					  WHERE gp.position_id = cg.position_id AND user_id = '$user_id'
					  UNION
					  SELECT name, country_id FROM government_positions gp, congress_members cm
					  WHERE position_id = 3 AND user_id = '$user_id'";
			$result = mysqli_query($conn, $query);
			if($result->num_rows == 1) {
				$row = mysqli_fetch_row($result);
				list($position_name, $country_id) = $row;
				$is_governor = true;
				
				echo "\n\t\t\t" . '<a id="governor_link" href="country_manage">Greetings, ' . $position_name . '</a>';
			}
		?>
		
	</div>
	
	<div id="clock_info_div">
		<p id="clock"></p>
		<?php
			$query = "SELECT MAX(day_number), MAX(date) FROM day_count";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_row($result);
			list($day_number, $date) = $row;
			$time = date('H:i:s');
			
			//make sure to display right day number
			$server_date_time = strtotime("$date $time - 4 hour");
			$server_date = $date;
			
			$date = date('Y-m-d', $server_date_time);
			$time = date('H:i:s', $server_date_time);
			$user_date = date('Y-m-d', strtotime(correctDate($date, $time)));

			//by -4hours, date might go back one day number
			if(strtotime("$server_date") > strtotime("$date")) {
				$day_number--;
			}
			
			if($user_date > $date) {
				$day_number++;
			}
			else if($user_date < $date) {
				$day_number--;
			}
			
			echo "\n\t\t" . '<p id="day_number">Day: ' . $day_number .'</p>';
			
			//get user timezone hour difference for js clock
			$query = "SELECT hours FROM timezones WHERE timezone_id = (SELECT timezone_id FROM user_profile WHERE user_id = '$user_id')";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($hour_diff) = $row;
			echo "\n\t\t" . '<p id="get_hour_diff" hidden>' . $hour_diff . '</p>';
		?>
		
	</div>
	
	<div id="logout_search_div">
		<a id="logout" href="../etc/logout"><abbr title="Log Out"><span class="fa fa-sign-out"></span></abbr></a>
		
		<p id="search">
			<abbr title="search">
				<span class="glyphicon glyphicon-search"></span>
			</abbr>
		</p>
	
		<input id="search_input" type="text" placeholder="search">
	</div>
</div>

<div id="beta">
	<p>Beta version.</p>
</div>

</div>