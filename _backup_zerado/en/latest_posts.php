<?php 
	include('head.php');
?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head">Latest Posts</p>
		
		<?php
			$query = "SELECT bp.post_id, COUNT(CASE WHEN liked = true THEN 1 END) AS likes, 
					  COUNT(CASE WHEN viewed = true THEN 1 END) AS views,
					  title, blog_name, ub.user_id, user_name, user_image, bp.date, bp.time
					  FROM blog_posts bp, post_likes_views plv, user_blog ub, users u, user_profile up
					  WHERE plv.post_id = bp.post_id AND ub.blog_id = bp.blog_id
					  AND up.user_id = ub.user_id AND u.user_id = ub.user_id
					  GROUP BY post_id
					  ORDER BY bp.date DESC, bp.time DESC
					  LIMIT 100";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($post_id, $likes, $views, $title, $blog_name, $blogger_id, $blogger_name, $blogger_image, $date, $time) = $row;

				$date = correctDate($date, $time);
				$time = correctTime($time);
				$post_date = date('M j', strtotime($date)) . ' ' . $time;
				
				echo "\n\t\t\t" . '<div class="lpd_short_descript">' .
					 "\n\t\t\t\t" . '<img src="../user_images/' . $blogger_image . '" alt="user image">' .
					 "\n\t\t\t\t" . '<a class="lpd_blogger_name" href="user_profile?id=' . $blogger_id . '">' . $blogger_name . '</a>' .
					 "\n\t\t\t\t" . '<a class="lpd_title" href="blog_info?post_id=' . $post_id . '" target="_blank">' .
									$title . '</a>' .
					 "\n\t\t\t\t" . '<p class="lpd_time">' . $post_date . '</p>' .
					 "\n\t\t\t\t" . '<div class="lpd_post_views_likes_div">' .
					 "\n\t\t\t\t\t" . '<p class="likes"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> ' . $likes . '</p>' .
					 "\n\t\t\t\t\t" . '<p class="views"><i class="fa fa-eye" aria-hidden="true"></i> ' . $views . '</p>' .
					 "\n\t\t\t\t" . '</div>' .
					 "\n\t\t\t" . '</div>';
			}
			echo "\n\t\t" . '</div>';
		
		?>
		
	</div>
	
</main>

<?php include('footer.php'); ?> 