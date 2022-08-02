<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>	
	
	<div id="container">
		
		<?php
			//get favorite chat
			$query = "SELECT fc.chat_id, chat_name FROM favorite_chat fc, chat_info cm 
					  WHERE user_id = '$user_id' AND fc.chat_id = cm.chat_id";
			$result = $conn->query($query);
			if($result->num_rows != 1) {
				$fav_chat_set = false;
			}
			else {
				$fav_chat_set = true;
				$row = $result->fetch_row();
				list($chat_id, $chat_name) = $row;
			}
		
			//chat controls
			echo "\n\t\t" . '<div id="chat_controls">';
			if(!$fav_chat_set) {
				echo  "\n\t\t\t" . '<p id="chat_add_favorite"><abbr title="Make favorite">' .
								   '<i class="glyphicon glyphicon-star-empty"></i></abbr></p>';
			}
			else {
				echo  "\n\t\t\t" . '<p id="chat_add_favorite"><abbr title="Make favorite">' .
								   '<i class="glyphicon glyphicon-star"></i></abbr></p>';
			}
			echo "\n\t\t\t" . '<p id="create_new_chat_btn"><abbr title="Create New"><i class="fa fa-plus"></abbr></i></p>' .
				 "\n\t\t\t" . '<p id="chat_settings"><abbr title="Settings"><i class="glyphicon glyphicon-cog"></abbr></i></p>' .
				 "\n\t\t" . '</div>';
		
			echo "\n\t\t" . '<div id="chat">';
			
			//check favorite chat
			if(!$fav_chat_set) {
				//get total unviewd messages
				//js will check if chat is set
				$query = "SELECT COUNT(ucm.message_id) FROM unread_chat_messages ucm, chat c
						  WHERE c.message_id = ucm.message_id AND user_id = '$user_id' GROUP BY user_id";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				list($total_new_chat_messages) = $row;
				
				echo "\n\t\t\t" . '<p id="chat_name">' .
					 "\n\t\t\t\t" . '<i class="fa fa-comments" aria-hidden="true"></i> ' . 
					 "\n\t\t\t\t" . 'Select Chat'  .
					 "\n\t\t\t\t" . '<i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' .
					 "\n\t\t\t" . '</p>' .
					 "\n\t\t\t" . '<p id="total_new_chat_messages">' . $total_new_chat_messages . '</p>' .
					 "\n\t\t\t" . '<p id="chat_id" hidden></p>';
			}
			else {
				echo "\n\t\t\t" . '<p id="chat_name">' .
					 "\n\t\t\t\t" . '<i class="fa fa-comments" aria-hidden="true"></i> ' . 
					 "\n\t\t\t\t" . $chat_name  .
					 "\n\t\t\t\t" . '<i class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></i>' .
					 "\n\t\t\t" . '</p>' .
					 "\n\t\t\t" . '<p id="total_new_chat_messages"></p>' .
					 "\n\t\t\t" . '<p id="chat_id" hidden>' . $chat_id . '</p>';
			}
			
			echo "\n\t\t\t" . '<div id="chat_list_menu" class="scroll_style">';
			
			$query = "SELECT chat_name, ci.chat_id, COUNT(ucm.message_id) FROM chat_info ci LEFT JOIN chat c ON
					  c.chat_id = ci.chat_id LEFT JOIN unread_chat_messages ucm ON
					  c.message_id = ucm.message_id AND user_id = '$user_id'
					  WHERE ci.chat_id IN 
					 (SELECT chat_id FROM chat_members WHERE user_id = '$user_id') 
					  GROUP BY chat_id ORDER BY chat_name";
			$result = $conn->query($query);
			while($row = $result->fetch_row()) {
				list($chat_name, $chat_id, $new_messages) = $row;

				echo "\n\t\t\t\t" . '<div id="ci_' . $chat_id . '">' .
					 "\n\t\t\t\t\t" . '<p class="chat_name">' . $chat_name . 
									  '<span id="cinm_' . $chat_id . '" class="ci_new_messages">' . $new_messages . '</span>' .
									  '</p>' .
					 "\n\t\t\t\t\t" . '<p class="chat_id" hidden>' . $chat_id . '</p>' .
					 "\n\t\t\t\t" . '</div>';
			}
			
			echo "\n\t\t\t" . '</div>' .
				 "\n\t\t\t" . '<div id="messages" class="scroll_style"></div>';
		?>
			
			<p hidden></p>
			<textarea id="enter_message" maxlength="500"></textarea>
			<span id="send_message" class="glyphicon glyphicon-send"></span>
		</div>
	</div>
	
	<?php
		include('right_side.php');
	?>
	
</main>

<?php include('footer.php'); ?>