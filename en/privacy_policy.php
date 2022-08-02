<?php 
	//check available languages for js. include lang file
	include('../connect_db.php');
	include("../php_functions/get_user_language.php"); //getUserLanguage('extension');
	include('../lang/' . getUserLanguage('php') . '.php');
?>
<!-- Copyright 2016 - <?php echo date('Y'); ?>. All rights reserved. Copying and/or distributing this code 
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
	<title>Privacy Policy</title>
	<link rel="stylesheet" href="../css/privacy_policy.css">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	<!--favIcons-->
	<link rel="icon" href="img/icon.png" type="image/x-icon">
	<!--favIcon for IE-->
	<link rel="shortcut icon" href="../img/icon.png" type="image/x-icon">
	<link rel="stylesheet" href="../css/footer.css">
	<script src="../js/jquery.js"></script>
	<script src="../js/footer.js"></script>
</head>
<body>

	<!--top bar-->
	<div id="top">
		<a href="../index"><img src="../img/logo.png" id="logo"></a>
	</div>
	
	<!--Main content-->
	<div id="main">
		<p id="pp_head">Privacy Policy</p>
		<ol>
			<li><b>Information that we may collect.</b> 
				<ul>
					<li>Information that you submit when you register for the website (username and e-mail address).</li>
					<li>Information that you submit when using our in game communication options, such as
						posts, messages and comments.</li>
				</ul>
			</li>
			
			<li><b>How collected information is used.</b>
				<p>
					Information that we collect may be used to:
				</p>
				<ul>
					<li>Identify you as a user.</li>
					<li>Send notifications</li>
				</ul>
			</li>
			
			<li><b>Information that we share with third parties.</b>
				<p>
					We do not disclose to any third party personal information that you provide to us, unless we have your permission to do so, 
					or law enforcement legally permits or requires it.
				</p>
			</li>
			
			<li><b>How can you manage information about you:</b>
				<ul>
					<li>You can delete your account at any time and all contents related to it.</li>
					<li>You can delete or update any information you submited to vMundus at any time.</li>
				</ul>
			</li>
			
			<li><b>Cookies</b>
				<p>
					vMundus uses cookies which are small text files placed on your computer in order to record your preferences
					and improve your experience on our website.
				</p>
			</li>
			
			<li><b>How you will be notified of any changed to this policy</b>
				<p>We will notify you of any changes made to this policy.</p>
			</li>
		</ol>
	</div>
	
	<!--footer-->
	<?php include('footer.php'); ?>