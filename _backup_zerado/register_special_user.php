<html>
<body>

<form action="register_special_user" method="post">
<p>For users with account</p>
Email: <input type="email" name="email"><br>
Password: <input type="password" name="pass"><br>
<input type="submit">
</form>

<form action="register_special_user" method="post">
<p>For first time users:</p>
Email: <input type="email" name="email"><br>
Code: <input type="text" name="code"><br>
<input type="submit">
</form>

<?php
	session_start();

	include('connect_db.php');
	include('php_functions/get_ip.php');//getIP();
	
	$ip = getIP();	
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit("<br>error");
	}
	
	$email = htmlentities(stripslashes(trim($_POST['email'])), ENT_QUOTES);
	$pass = htmlentities(stripslashes(trim($_POST['pass'])), ENT_QUOTES);
	$code = htmlentities(stripslashes(trim($_POST['code'])), ENT_QUOTES);
	
	
	
	if(!empty($code)) {//one time access
		if(empty($email)) {
			exit("<br>All fields must be filled in.");
		}
	
		$query = "SELECT hash FROM special_users WHERE email = '$email' AND hash = '$code' AND is_new = TRUE";
		$result = $conn->query($query);

		//check if registered but account is not activated
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($special_hash) = $row;
			$_SESSION['special_email'] = $email;
			$_SESSION['special_user'] = 'true';
			$_SESSION['special_hash'] = $special_hash;
			echo '<p>Success. This access is valid only for this session.</p>';
		}
		else {
			echo 'Incorrect email or code';
		}
	}
	else {//permanent user
		if(empty($email) || empty($pass)) {
			exit("<br>All fields must be filled in.");
		}
		
		$query = "SELECT hash, password FROM users u, special_users su
				  WHERE su.email = '$email' AND u.email = su.email";
		$result = $conn->query($query);

		//check if registered but account is not activated
		if($result->num_rows == 1) {
			$row = $result->fetch_row();
			list($special_hash, $hash) = $row;
			if (password_verify($pass, $hash)) {
				$_SESSION['special_email'] = $email;
				$_SESSION['special_user'] = 'true';
				$_SESSION['special_hash'] = $special_hash;
				echo '<p>Success. This access is valid only for this session.</p>';
			}
			else {
				echo 'Incorrect email or password';
			}
		}
	}
?>

</body>
</html>
