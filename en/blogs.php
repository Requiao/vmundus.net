<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">	
		<?php
			$blog_id = htmlentities(stripslashes(trim($_GET['blog_id'])), ENT_QUOTES);
			$post_id = htmlentities(stripslashes(trim($_GET['post_id'])), ENT_QUOTES);
			
			/* create blog */
			echo "\n\t\t" . '<p id="my_blogs_head">My blogs</p>';
			echo "\n\t\t" . '<p id="create_blog">Create Blog</p>';
			
			/* users blog list */
			$query = "SELECT ub.blog_id, blog_name, description, blog_image, COUNT(bs.blog_id)
					  FROM user_blog ub, blog_subscribers bs
					  WHERE ub.blog_id = bs.blog_id AND ub.user_id = '$user_id' GROUP BY blog_id ORDER BY blog_name";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($blog_id, $blog_name, $description, $blog_image, $subscribers) = $row;	
				
				echo "\n\t\t" . '<div class="blog_list_div">' .
					 "\n\t\t\t" . '<img class="blog_img" src="../blog_images/' . $blog_image . '">' .
					 "\n\t\t\t" . '<p class="blog_name">' . $blog_name . '</p>' .
					 "\n\t\t\t" . '<p class="blog_subs">Subscribers: ' . $subscribers . '</p>' .
					 "\n\t\t\t" . '<p class="blog_desc">' . $description . '</p>' .
					 "\n\t\t\t" . '<a class="button blue details" href="blog_info?blog_id=' . $blog_id . '" target="_blank">Manage</a>' .
					 "\n\t\t" . '</div>';
			}
			
			/* subscribed blogs */
			echo "\n\t\t" . '<p id="my_sub_blogs_head">My subscriptions</p>';
			
			$query = "SELECT ub.blog_id, blog_name, description, blog_image, COUNT(bs.blog_id), user_name, u.user_id
					  FROM user_blog ub, blog_subscribers bs, users u
					  WHERE ub.blog_id = bs.blog_id AND u.user_id = ub.user_id
					  AND ub.blog_id IN (SELECT blog_id FROM blog_subscribers WHERE user_id = '$user_id')
					  AND ub.blog_id NOT IN (SELECT blog_id FROM user_blog WHERE user_id = '$user_id')
					  GROUP BY blog_id ORDER BY COUNT(*) DESC;";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($blog_id, $blog_name, $description, $blog_image, $subscribers, $blogger_name, $blogger_id) = $row;	
				
				echo "\n\t\t" . '<div class="blog_list_div">' .
					 "\n\t\t\t" . '<img class="blog_img" src="../blog_images/' . $blog_image . '">' .
					 "\n\t\t\t" . '<p class="blog_name">' . $blog_name . '</p>' .
					 "\n\t\t\t" . '<a class="blogger_name" href="user_profile?id=' . $blogger_id . 
								  '" target="_blank">Blogger: <i>' . $blogger_name . '</i></a>' .
					 "\n\t\t\t" . '<p class="blog_subs">Subscribers: ' . $subscribers . '</p>' .
					 "\n\t\t\t" . '<p class="blog_desc">' . $description . '</p>' .			  
					 "\n\t\t\t" . '<a class="button blue details" href="blog_info?blog_id=' . $blog_id . '" target="_blank">View</a>' .
					 "\n\t\t" . '</div>';
			}
			
			/* top 10 blog list */
			echo "\n\t\t" . '<p id="other_blogs_head">Top 10 blogs</p>';
			$query = "SELECT ub.blog_id, blog_name, description, blog_image, COUNT(bs.blog_id), user_name, u.user_id
					  FROM user_blog ub, blog_subscribers bs, users u
					  WHERE ub.blog_id = bs.blog_id AND u.user_id = ub.user_id GROUP BY blog_id ORDER BY COUNT(*) DESC LIMIT 10";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($blog_id, $blog_name, $description, $blog_image, $subscribers, $blogger_name, $blogger_id) = $row;	
				
				$description = html_entity_decode($description, ENT_QUOTES);
				
				echo "\n\t\t" . '<div class="blog_list_div">' .
					 "\n\t\t\t" . '<img class="blog_img" src="../blog_images/' . $blog_image . '">' .
					 "\n\t\t\t" . '<p class="blog_name">' . $blog_name . '</p>' .
					 "\n\t\t\t" . '<a class="blogger_name" href="user_profile?id=' . $blogger_id . 
								  '" target="_blank">Blogger: <i>' . $blogger_name . '</i></a>' .
					 "\n\t\t\t" . '<p class="blog_subs">Subscribers: ' . $subscribers . '</p>' .
					 "\n\t\t\t" . '<p class="blog_desc">' . $description . '</p>' .			  
					 "\n\t\t\t" . '<a class="button blue details" href="blog_info?blog_id=' . $blog_id . '" target="_blank">View</a>' .
					 "\n\t\t" . '</div>';
			}
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>