<?php
	//Description: notify users that haven't logged in for a while
	include('../connect_db.php');
	include('../etc/verify_php.php');
	include('../php_functions/get_time_for_id.php');//getTimeForId()
	
	if($user_id != 1) {
		exit('error');
	}
	
	$query = "SELECT user_id, email FROM users WHERE
			  DATE_ADD(TIMESTAMP(last_active, last_active_time), INTERVAL 10 DAY) <= NOW()
              AND user_id NOT IN (SELECT user_id FROM banned_users WHERE date_until >= NOW())
              AND user_id <= 0 AND user_id >= 0";
	$result = $conn->query($query);
	$x = 0;
	while($row = $result->fetch_row()) {
		list($user_id, $email) = $row;
		if(sendNotificationEmail($user_id, $email)) {
			echo "Email sent to: $email <br>";
			$x++;
		}
		else {
			echo "ERROR: $email <br>";
		}
	}
	echo "Emails sent: $x";
	
function sendNotificationEmail($user_id, $email) {
	global $conn;
	
	//check if not unsubscribed
	$query = "SELECT unsubscribe, email_hash FROM email_notifications 
			  WHERE user_id = '$user_id'";
	$result = $conn->query($query);
	if($result->num_rows == 0) {
		$unsubscribe = false;
		
		$string_for_hash = getTimeForId() . $email;
		$email_hash = hash('sha256', $string_for_hash);
		
		$query = "INSERT INTO email_notifications VALUES ('$user_id', '$email_hash', FALSE)";
		$conn->query($query);
	}
	else {
		$row = $result->fetch_row();
		list($unsubscribe, $email_hash) = $row;
	}
	
	if($unsubscribe) {//user unsubscribed from emails
		return false;
	}
	
	
	$link = 'https://vmundus.com/unsubscribe?unsubscribe=' . $email_hash;
	
	
	$message = '<div style="padding-top: 5px;' . "\n" .
			   'width:100%; min-width:600px; background-color: rgb(245,246,247);">' . "\n" .
			   
			   '<div style="width:90%; margin-left:auto; margin-right:auto;' . "\n" .
			   ' background-color: white;">' . "\n" .
			   
			   '<div style="width: 100%;' . "\n" .
			   'background-color: rgb(71, 87, 102); padding-top: 25px; ' . "\n" .
			   'padding-bottom: 25px;margin-top: 10px; ' . "\n" .
			   '"><img alt="vmundus logo" src="https://vmundus.com/img/logo.png" ' . "\n" .
			   'style="margin-left: auto; margin-right: auto; display: block;">' . "\n" .
			   '</div>' . "\n" .
			   
			   '<div style="width100%; float:left;">' . "\n" .
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" . 
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;font-weight: bold;">' . "\n" .
			   'It looks like you haven\'t logged into your account for a while.' . "\n" .
			   ' The game has changed a lot since your last visit.' . "\n" .
			   ' Log in to view new game features and get rewards due to 300 days of the game!' . "\n" .
			   ' Below is a list of some latest updates:</p>' .
			   '</div>' . "\n" .
			   
			   '<div style="width100%; float:left;">' . "\n" .
			   '<ul style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;">' . "\n" .
			   '<li>Changes to the economic system</li>' . "\n" .
			   '<li>Buy clones and build cloning farms</li>' . "\n" .
			   '<li>New resistance war system</li>' . "\n" .
			   '<li>Taxes on production</li>' . "\n" .
			   '<li>New laws</li>' . "\n" .
			   '<li>Daily missions</li>' . "\n" .
			   '<li>Weekly missions</li>' . "\n" .
			   '<li>Earn experience points</li>' . "\n" .
			   '<li>Get bonuses for user level</li>' . "\n" .
			   '<li>New election system</li>' . "\n" .
			   '<li>Research new buildings</li>' . "\n" .
			   '<li>Region\'s productivity bonus</li>' . "\n" .
			   '<li>New monetary market</li>' . "\n" .
			   '<li>and more</li>' . "\n" .
			   '</ul>' . "\n" .
			   '</div>' . "\n" .
			   
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;">Game link: ' . "\n" .
			   
			   '<a style="font-family: Bell MT,Baskerville Old Face; font-size: 14px;' . "\n" .
			   'color: #345879; text-decoration: none; font-weight: 600;" ' . "\n" .
			   'href="https://vmundus.com/index">' . "\n" . 
			   'https://vmundus.com/index</a></p>' . "\n" .
			   
			   '<div style="width: 200px; border-radius: 5px; ' . "\n" .
			   'cursor: pointer; background-color: rgb(52, 88, 121);' . "\n" .
			   'color: white; font-size: 30px; text-align: center; ' . "\n" .
			   'padding-top: 10px; padding-bottom: 10px; ' . "\n" .
			   'margin-left: auto; margin-right: auto; clear: both;' . "\n" .
			   'font-family: Contana; margin-bottom:10px;"><a style="text-decoration: none;' . "\n" . 
			   'color: white; padding: 10px 45px 10px 45px;"' . "\n" .
			   'href="https://vmundus.com/index' . "\n" .
			   '">Login</a></div>' . "\n" .
			   
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'background-color: rgba(71, 87, 102, 0.15); color: black;">' . "\n" .
			   'If you are having any ' . "\n" .
			   'issues with your account, don\'t hesitate to ' . "\n" .
			   'contact us by vmundusgame@gmail.com. ' .
			   'If you do not wish to receive emails of this kind from vMundus ' .
			   'then please <a style="text-decoration: none;' . "\n" . 
			   'color: #345879; font-weight: 600;"' . "\n" .
			   'href="' . $link . '"' . "\n" .
			   '">unsubscribe</a>. Thank You!</p>' .
			   '</div>' . "\n" .
			   '</div>';
	
	
	$encoding = "utf-8";
	$subject = 'vMundus';
	$from_mail = 'vmundusgame@gmail.com';
	$from_name = 'vMundus';
    // Preferences for Subject field
    $subject_preferences = array(
        "input-charset" => $encoding,
        "output-charset" => $encoding,
        "line-length" => 76,
        "line-break-chars" => "\r\n"
    );

    // Mail header
    $header = "Content-type: text/html; charset=" . $encoding . " \r\n";
    $header .= "From: " . $from_name . " <" . $from_mail . "> \r\n";
    $header .= "MIME-Version: 1.0 \r\n";
    $header .= "Content-Transfer-Encoding: 8bit \r\n";
    $header .= "Date: " . date("r (T)") . " \r\n";
    $header .= iconv_mime_encode("Subject", $subject, $subject_preferences);

    // Send mail
	if(mail($email, $subject, $message, $header)) {
		return true;
	}
	else {
		return false;
	}
}	
?>