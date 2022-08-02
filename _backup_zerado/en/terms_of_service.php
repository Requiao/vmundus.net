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
	<title>Terms of Service</title>
	<link rel="stylesheet" href="../css/terms_of_service.css">
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
		<p id="tos_head">Terms of Service</p>
		<ol>
			<li><b>Terms.</b> 
				<p>
					By visiting www.vmundus.com, you are agreeing to be bound these web site Terms and Conditions of Use, all applicable 
					laws and regulations, and agree that you are responsible for compliance with any applicable local laws.
					If you do not agree with any of these terms, you are prohibited from using or accessing this 
					site. The materials contained in this web site are protected by applicable copyright and trademark law.
				</p>
			</li>
			
			<li><b>Use License.</b>
				<ul>
					<li>
						Permission is granted to temporarily download one copy of the materials (information or software) on 
						vMundus web site for personal, non-commercial transitory viewing only. This is the grant of a 
						license, not a transfer of title, and under this license you may not:
						<ul>
							<li>modify or copy the materials;</li>
							<li>use the materials for any commercial purpose, or for any public display (commercial or non-commercial);</li>
							<li>attempt to decompile or reverse engineer any software contained on vMundus web site;</li>
							<li>remove any copyright or other proprietary notations from the materials;</li>
							<li>transfer the materials to another person or "mirror" the materials on any other server.</li>
						</ul>
					</li>
					<li>
						This license shall automatically terminate if you violate any of these restrictions and may be terminated by 
						vMundus at any time. Upon terminating your viewing of these materials or upon the termination of this license, 
						you must destroy any downloaded materials in your possession whether in electronic or printed format.
					</li>
				</ul>
			</li>
			
			<li><b>Safety</b>
				<p>
					We do our best to keep vMundus safe, but we cannot guarantee it. We need your help to keep vMundus safe, 
					which includes the following commitments by you:
				</p>
				<ul>
					<li>You will not post unauthorized commercial communications (such as spam) on vMundus.</li>
					<li>You will not collect users' content or information, or otherwise access vMundus, using automated means 
						(such as harvesting bots, robots, spiders, or scrapers).</li>
					<li>You will not upload viruses or other malicious code.</li>
					<li>You will not solicit login information or access an account belonging to someone else.</li>
					<li>You will not bully, intimidate, or harass any user.</li>
					<li>You will not post content that: is hate speech, threatening, or pornographic; incites violence; or contains nudity 
					or graphic or gratuitous violence.</li>
					<li>You will not use vMundus to do anything unlawful, misleading, malicious, or discriminatory.</li>
					<li>You will not do anything that could disable, overburden, or impair the proper working or appearance 
					of vMundus, such as a denial of service attack or interference with page rendering or other vMundus functionality.</li>
					<li>You will not facilitate or encourage any violations of this Statement or our policies.</li>
				</ul>
			</li>
			
			<li><b>Disclaimer</b>
				<p>
					The materials on vMundus web site are provided AS IS. vMundus makes no warranties, expressed or implied, 
					and hereby disclaims and negates all other warranties, including without limitation, implied warranties or 
					conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property 
					or other violation of rights. Further, vMundus does not warrant or make any representations concerning the 
					accuracy, likely results, or reliability of the use of the materials on its internet web site or otherwise 
					relating to such materials or on any sites linked to this site.
				</p>
			</li>
			
			<li><b>Links</b>
				<p>
					vMundus has not reviewed all of the sites linked to its internet web site and is not responsible for the contents 
					of any such linked site. The inclusion of any link does not imply endorsement by vMundus of the site. Use of any 
					such linked web site is at the user's own decision and risk.
				</p>
			
			<li><b>Limitations</b>
				<p>
					In no event shall vMundus or its suppliers be liable for any damages (including, without limitation, damages for 
					loss of data or profit, or due to business interruption,) arising out of the use or inability to use the materials
					on vMundus internet site, even if vMundus or a vMundus authorized representative has been notified orally or in 
					writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, 
					or limitations of liability for consequential or incidental damages, these limitations may not apply to you.
				</p>
			</li>
			
			<li><b>Termination</b>
				<p>
					If you violate this statement or otherwise create risk or possible legal exposure for us, 
					we can stop providing all or part of vMundus to you.
				</p>
			</li>
			
			<li><b>Payments</b>
				<p>
					vMundus is a free game. Playing the game does not require any type of payments. But, vMundus can provide options 
					for payments in order for the players to buy additional products and/or buildings. Refunds are not available at any time.
				</p>
			</li>
			
			<li><b>Amendments</b>
				<p>
					vMundus may revise these terms of use for its web site at any time without or with notifying you. By using this web site 
					you are agreeing to be bound by the then current version of these Terms and Conditions of Use.
				</p>
			</li>
		</ol>
	</div>
	
	<!--footer-->
	<?php include('footer.php'); ?>