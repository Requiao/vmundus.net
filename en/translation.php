<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	
	</div>
	
	<div id="container">
		<p id="translation_head">Translation</p>
		<?php
			//add new variable to translate
			if($user_id == 1) {
				//select available pages
				$query = "SELECT file_id, file_name FROM mundus_pages ORDER BY file_name";
				$result = $conn->query($query);
				echo '<div id="add_new_word_div">' .
					 '<p id="anwd_reply"></p>' .
					 '<div id="anwd_controls">' .
					 '<p id="file_id_label">Page</p>' .
					 '<select id="file_id">';
				while($row = $result->fetch_row()) {
					list($file_id, $file_name) = $row;
					echo '<option value="' . $file_id . '">' . $file_name . '</option>';
				}
				echo '</select>' .
					 '<p id="var_name_label">Variable Name</p>' .
					 '<input id="var_name_input" type="text" maxlength="100">' .
					 '<p id="word_label">Word</p>' .
					 '<input id="word_input" type="text" maxlength="100">' .
					 '<p class="button blue" id="add_word">Add</p>' .
					 '</div>' .
					 '<div id="added_words">' .
					 '</div>' .
					 '</div>';
			}
			
			$query = "SELECT lang_id, language FROM languages
					  WHERE lang_id IN (SELECT lang_id FROM translation_access WHERE user_id = '$user_id' AND access = TRUE)";
			$result = $conn->query($query);
			if($result->num_rows > 0 || $user_id == 1) {
				echo '<select id="alang_id">' .
					 '<option></option>';
				while($row = $result->fetch_row()) {
					list($lang_id, $language) = $row;
					echo '<option value="' . $lang_id . '">' . $language . '</option>';
				}
				if($user_id == 1) {
					echo '<option value="0">Words</option>';
				}
				echo '</select>' .
					 '<select id="afile_id" hidden>' .
					 '</select>' .
					 '<div id="translate_div"' .
					 '</div>';
			}
			else {
				echo '<p>You don\'t have access to view this page</p>';
			}
		?>

	</div>
	
</main>

<?php include('footer.php'); ?>