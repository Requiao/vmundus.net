<?php
	session_start();
	
	$file_name = 'public_posts';
	
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
	
	//select post
	$post_id = htmlentities(stripslashes(strip_tags(trim($_GET['post_id']))), ENT_QUOTES);
	$post_error = true;
	$title = "Online Strategy Game that allows players to rule a country, have own business empire and/or conquer other countries.";
	$query = "SELECT ub.blog_id, post_id, title, post, date, time, blog_image, blog_name
			  FROM blog_posts bp, user_blog ub 
			  WHERE post_id = '$post_id' AND bp.blog_id = ub.blog_id
			  ORDER BY date DESC, time DESC";
	$result_posts = $conn->query($query);
	if($result_posts->num_rows != 0) {
		$row_posts = $result_posts->fetch_row();
		list($blog_id, $post_id, $post_title, $post, $date, $time, $blog_image, $blog_name) = $row_posts;
		$post_date = date('M j', strtotime($date)) . ' ' . $time;
		$post_error = false;
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
	<meta name="description" content="<?php echo $post_title ?>">
	<title>Posts</title>
	<link rel="stylesheet" href="../css/public_posts.css?<?php echo filemtime('../css/public_posts.css'); ?>">
	<link rel="stylesheet" href="../css/footer.css?<?php echo filemtime('../css/footer.css'); ?>">
	<link href="https://fonts.googleapis.com/css?family=Enriqueta" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">
	
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/v4-shims.css">
	
	<script src="../js/jquery.js"></script>
	<script src="../js/wiki.js?<?php echo filemtime('../js/wiki.js'); ?>"></script>
	<script src="../js/footer.js?<?php echo filemtime('../js/footer.js'); ?>"></script>
	<!--favIcons-->
	<link rel="icon" href="../img/icon.png" type="image/png">
	<!--favIcon for IE-->
	<link rel="shortcut icon" href="../img/icon.png" type="image/png">
</head>
<body>

	<!-- Load Facebook SDK for JavaScript -->
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

	<div id="top">
		<a href="../"><img src="../img/logo.png" id="logo"></a>
		
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
	
	<p id="page_head">Latest News</p>
	
	<div id="popular_posts_div">
		<p id="ppd_heading">Popular News</p>
		
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
				list($p_post_id, $p_views, $p_title, $p_blogger_name, $p_post_date) = $row;
				$p_post_date = date('M j', strtotime($date));
				
				echo "\n\t\t" . '<a class="ppd_post_name" href="public_posts?post_id=' . $p_post_id . '">' .
					 "\n\t\t\t" . '<p class="ppd_post_title">' . $p_title . '</p>' .
					 "\n\t\t\t" . '<p class="ppd_blogger_name">' . $p_blogger_name . '</p>' .
					 "\n\t\t\t" . '<p class="ppd_post_data">' . $p_post_date . '</p>' .
					 "\n\t\t" . '</a>';
			}
		?>

	</div>
	
	<div id="posts_container">
	<?php
		if(!$post_error) {
			//get likes and views
			$query = "SELECT IFNULL(ppv.views, 0), COUNT(CASE WHEN plv.viewed = true THEN 1 END)
					  FROM post_likes_views plv LEFT JOIN public_post_views ppv
					  ON ppv.post_id = plv.post_id
					  WHERE plv.post_id = '$post_id' 
					  GROUP BY ppv.views";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			list($public_views, $in_game_views) = $row;
			
			echo "\n\t\t" . '<div class="post_details" id="post_' . $post_id . '">' .
				 "\n\t\t\t" . '<p class="get_post_id" hidden>' . $post_id . '</p>' .
				 "\n\t\t\t" . '<img class="post_blog_img" src="../blog_images/' . $blog_image . '?' . 
							 filemtime('../blog_images/' . $blog_image) . '">' .
				 "\n\t\t\t" . '<a class="blog_name_link" href="blog_info?blog_id=' . $blog_id . 
							  '">' . $blog_name . '</i></a>' .
				 "\n\t\t\t" . '<p class="post_date">' . $post_date . '</p>' .
				 "\n\t\t\t" . '<a class="post_title" href="blog_info?blog_id=' . $blog_id . '&post_id=' . $post_id . 
							  '">' . $post_title . '</a>' .
				 "\n\t\t\t" . '<div class="post_div">' . html_entity_decode($post, ENT_QUOTES) . '</div>' .
				 "\n\t\t\t" . '<div class="post_views_likes_div">' .
				 "\n\t\t\t\t" . '<p class="views">Public views: ' .
								'<i class="far fa-eye" aria-hidden="true"></i> ' . 
								number_format($public_views, 0, '', ' ') . '</p>' .
				  "\n\t\t\t\t" . '<p class="views">In-game views: ' .
								'<i class="far fa-eye" aria-hidden="true"></i> ' . 
								number_format($in_game_views, 0, '', ' ') . '</p>' .
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t\t" . '<div class="fb-share-button"' . 
				 "\n\t\t\t" . 'data-href="public_posts?post_id=' . $post_id . '"' . 
				 "\n\t\t\t" . 'data-layout="button_count">' .
				 "\n\t\t\t" . '</div>' .
				 "\n\t\t" . '</div>';
				 
				 
			//update post with public view
			$viewed = false;
			if($_SESSION['viewed_public_post_' . $post_id] == 'true') {
				$viewed = true;
			}
			if(!$viewed) {
				$query = "SELECT * FROM public_post_views WHERE post_id = '$post_id'";
				$result = $conn->query($query);
				if($result->num_rows == 1) {
					$query = "UPDATE public_post_views SET views = views + 1 WHERE post_id = '$post_id'";
				}
				else {
					$query = "INSERT INTO public_post_views VALUES('$post_id', '1')";
				}
				$conn->query($query);	

				$_SESSION['viewed_public_post_' . $post_id] = 'true';
			}
		}
		else {
			echo '<div id="default_post">' .
				 '<p style="text-align:center"><span style="color:#2980b9"><strong><span style="font-size:22px">Welcome</span><span style="font-size:22px"> to </span></strong></span><span style="color:#2980b9"><strong><span style="font-size:22px">vMundus</span></strong></span><span style="color:#2980b9"><strong><span style="font-size:22px"> news!</span></strong></span></p>' .

				  '<p><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px">We hope that you will find what you were looking for.</span></span></p>' .

				  '<p><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px">News on the </span></span><span style="font-size:22px"><span style="font-family:Arial,Helvetica,sans-serif">vMundus</span></span><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px"> is written by people from all over the world and you can be part of this community.</span></span></p>' .

				  '<p><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px">If you have an idea and want to share it with the rest of the world, this is the place for you. Register and start your career as a writer, politician, economist&nbsp;or a general of your own army. </span></span></p>' .

				  '<p><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px">This world needs You!</span></span></p>' .

				  '<p>&nbsp;</p>' .

				  '<p><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px">More details about this game can be found </span></span><span style="font-size:22px"><span style="font-family:Arial,Helvetica,sans-serif"><span>on</span></span><span style="font-family:Arial,Helvetica,sans-serif"> our </span></span><span style="font-family:Arial,Helvetica,sans-serif"><span style="font-size:22px"><a href="https://vmundus.com/wiki/index">Wiki</a>.</span></span></p>' .
				  '</div>';
		}
	?>
	
	</div>
	
<?php include('../en/footer.php'); ?> 