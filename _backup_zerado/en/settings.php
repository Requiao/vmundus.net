<?php include('head.php'); ?>

<main>
	<div id="col1">
	<?php
		include('user_info.php');
	?>
	</div>
	
	<div id="container">
		<p id="page_head">Settings</p>
		
		<div id="lang_bar_div">
			<?php
				$query = "SELECT language, flag, lang_id FROM languages ORDER BY language";
				$result = $conn->query($query);
				while($row = $result->fetch_row()) {
					list($language, $flag, $lang_id) = $row;
					echo "\n\t\t\t" . '<abbr title="' . $language . '"><img src="../country_flags/' . $flag. 
									  '" abbr="' . $language . ' language" class="change_lang"><p hidden>' . $lang_id . '</p></abbr>';
				}
			?>
		</div>
		
		<div id="update_img">
			<p class="settings_head">New User Image</p>
			<p class="hh">Image(max 500kb):</p>
			<input type="file" id="new_image">
		</div>
		
		<div id="update_username">
			<p class="settings_head">New Username</p>
			<p class="hh">Username:</p>
			<input id="new_user_name" type="text" maxlength="15">
		</div>
		
		<div id="pswd_update_div">
			<p class="settings_head">New Password:</p>
			<p class="hh">Password:</p>
			<input id="new_pswd" type="password">
			<p class="hh">Repeat New Password:</p>
			<input id="rpt_new_pswd" type="password">
			
		</div>
		
		<div id="update_timezone">
			<p class="settings_head">New Timezone</p>
			<select id="timezones_id">
			<option></option>
			<?php			
				$query = "SELECT timezone_id, utc FROM timezones ORDER BY timezone_id";
				$result_timezones = $conn->query($query);
				while($row_timezones = $result_timezones->fetch_row()) {
					list($timezone_id, $utc) = $row_timezones;
					echo "\n\t\t\t\t\t" . '<option value="' . $timezone_id . '">' . $utc . '</option>';
				}	
			?>
			</select>
		</div>
		
		<div id="confirm_div">
			<p class="settings_head">Confirm</p>
			<p class="hh">Enter old password to update profile:</p>
			<input id="old_pswd" type="password">
			<p id="error"></p>
			<p id="update">Update</p>
		</div>
	</div>

</main>
	
<?php include('footer.php'); ?>