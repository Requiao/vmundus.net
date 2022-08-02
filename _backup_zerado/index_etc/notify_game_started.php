<?php
	//Description: Generate email that game registration is openned.
	include('../connect_db.php');
	include('../etc/verify_php.php');
	
	//$query = "SELECT email FROM pre_regester WHERE notify = TRUE";
	//$result = $conn->query($query);
	$x = 0;
	while($row = $result->fetch_row()) {
		list($email) = $row;
		if(sendNotificationEmail($email)) {
			echo "Email sent to: $email <br>";
		}
		else {
			echo "ERROR: $email <br>";
		}
		$x++;
	}
	echo "Emails sent: $x";
	
function sendNotificationEmail($email) {
	global $conn;
	
	$message = '<div style="' . "\n" .
			   'width:100%; min-width:600px; background-color: rgb(245,246,247);">' . "\n" .
			   
			   '<div style="width:90%; margin-left:auto; margin-right:auto;' . "\n" .
			   ' background-color: white;">' . "\n" .
			   
			   '<p style="width: 100%; font-size: 30px; text-align: center; ' . "\n" .
			   'background-color: rgb(71, 87, 102); padding-top: 25px; ' . "\n" .
			   'padding-bottom: 25px; color: white; margin-top: 10px; ' . "\n" .
			   'font-family: Times New Roman;' . "\n" .
			   '">VMundus Registration Is Now Opened</p>' . "\n" .
			   
			   '<div style="width100%; float:left;">' . "\n" .
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" . 
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;font-weight: bold;">' . "\n" .
			   'Now you can register an account in the vMundus game!</p>' .

			   
			   '<div style="width100%; float:left;">' . "\n" .
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;">Game link:</p>' . "\n" .
			   
			   '<a style="font-family: Bell MT,Baskerville Old Face; font-size: 14px;' . "\n" .
			   'margin-left: 10px; float: left; width: 350px; text-align: left; ' . "\n" .
			   'color: #345879; text-decoration: none; font-weight: 600; ' . "\n" .
			   'margin-top: 19px;" href="https://vmundus.com">' . "\n" . 
			   'https://vmundus.com</a>' . "\n" .
			   '</div>' . "\n" .
			   
			   '<div style="width: 200px; border-radius: 5px; ' . "\n" .
			   'cursor: pointer; background-color: rgb(52, 88, 121);' . "\n" .
			   'color: white; font-size: 30px; text-align: center; ' . "\n" .
			   'padding-top: 10px; padding-bottom: 10px; ' . "\n" .
			   'margin-left: auto; margin-right: auto; clear: both;' . "\n" .
			   'font-family: Contana; margin-bottom:10px;"><a style="text-decoration: none;' . "\n" . 
			   'color: white; padding: 10px 45px 10px 45px;"' . "\n" .
			   'href="https://vmundus.com' . "\n" .
			   '">Register</a></div>' . "\n" .
			   
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'background-color: rgba(71, 87, 102, 0.15); color: black;">' . "\n" .
			   'If you are having any ' . "\n" .
			   'issues with your account don\'t hesitate to ' . "\n" .
			   'contact us by vmundusgame@gmail.com. Thank You!</p>' .
			   '</div>' .
			   '</div>';
	
	
	$encoding = "utf-8";
	$subject = 'VMundus. Game starts!.';
	$from_mail = 'vmundusgame@gmail.com';
	$from_name = 'VMundus';
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