<?php
//Description: Generate email confirmation page.
function sendResetPassEmail($email, $confirmation_hash) {
	global $conn;
	
	$message = '<div style="' . "\n" .
			   'width:100%; min-width:600px; background-color: rgb(245,246,247);">' . "\n" .
			   
			   '<div style="width:90%; margin-left:auto; margin-right:auto;' . "\n" .
			   ' background-color: white;">' . "\n" .
			   
			   '<div style="width: 100%;' . "\n" .
			   'background-color: rgb(71, 87, 102); padding-top: 25px; ' . "\n" .
			   'padding-bottom: 25px;margin-top: 10px; ' . "\n" .
			   '"><img alt="vmundus logo" src="https://vmundus.com/img/logo.png" ' . "\n" .
			   'style="margin-left: auto; margin-right: auto; display: block;">' . "\n" .
			   '</div>' . "\n" .
			   
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'font-weight: 800; font-size: 20px; margin-left: 10px;' . "\n" . 
			   'color: black;">You requested password reset for your ' . "\n" . 
			   'VMundus account. If you didn\'t performed this action,' . "\n" .  
			   'then you can disregard this email.</p>' . "\n" .
			   
			   '<div style="width100%; float:left;">' . "\n" .
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;margin-bottom: 30px;">Reset link:</p>' . "\n" .
			   
			   '<a style="font-family: Bell MT,Baskerville Old Face;font-size: 14px;' . "\n" .
			   'margin-right: 10px;float: right;width: 350px;text-align: left;' . "\n" .
			   'color: #345879; text-decoration: none; font-weight: 600;"' . "\n" .
			   'href="https://vmundus.com/index?reset_password=' . $confirmation_hash . '">' . "\n" . 
			   'https://vmundus.com/index?reset_password=' . "\r\n" . $confirmation_hash . '</a>' . "\n" .
			   '</div>' . "\n" .
			   
			   '<div style="width100%; float:left;">' . "\n" .
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'font-size: 17px; margin-left: 10px; float: left; ' . "\n" .
			   'clear: left; color: black;">Will expire in:</p>' . "\n" .
			   
			   '<p style="font-family: Bell MT,Baskerville Old Face;font-size: 14px;' . "\n" .
			   'margin-right: 10px;float: right;width: 350px;text-align: left;' . "\n" .
			   'font-weight: 600;">' . "\n" . 
			   '15 minutes.</p>' . "\n" .
			   '</div>' . "\n" .
			   
			   '<div style="width: 200px; border-radius: 5px; ' . "\n" .
			   'cursor: pointer; background-color: rgb(52, 88, 121);' . "\n" .
			   'color: white; font-size: 30px; text-align: center; ' . "\n" .
			   'padding-top: 10px; padding-bottom: 10px; ' . "\n" .
			   'margin-left: auto; margin-right: auto; clear: both;' . "\n" .
			   'font-family: Contana; margin-bottom:10px;"><a style="text-decoration: none;' . "\n" . 
			   'color: white; padding: 10px 65px 10px 65px;"' . "\n" .
			   'href="https://vmundus.com/index?reset_password=' . $confirmation_hash . '">Reset</a></div>' . "\n" .
			   '<p style="font-family: Bell MT, Baskerville Old Face;' . "\n" .
			   'background-color: rgba(71, 87, 102, 0.15); color: black;">' . "\n" .
			   'You\'re receiving this email because you registered ' . "\n" .
			   'an account with VMundus. If you are having any ' . "\n" .
			   'issues with your account don\'t hesitate to ' . "\n" .
			   'contact us by vmundusgame@gmail.com. Thank You!</p>' .
			   
			   '</div>' .
			   '</div>';
	
	
	$encoding = "utf-8";
	$subject = 'vMundus. Password reset.';
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
    $header  = "Content-type: text/html; charset=" . $encoding . " \r\n";
    $header .= "From: " . $from_name . " <" . $from_mail . "> \r\n";
    $header .= "X-Sender: <mundus@vmundus.com> \r\n";
	$header .= 'X-Mailer: PHP/' . phpversion() . " \r\n";
	$header .= "X-Priority: 1 \r\n"; // Urgent message!
	$header .= "Return-Path: <mundus@vmundus.com \r\n"; // Return path for errors
    $header .= "MIME-Version: 1.0 \r\n";
    $header .= "Content-Transfer-Encoding: 8bit \r\n";
    $header .= "Date: " . date("r (T)") . " \r\n";
    //$header .= iconv_mime_encode("Subject", $subject, $subject_preferences);//caused yahoo to reject email
	
    // Send mail
    if(mail($email, $subject, $message, $header)) {
		return true;
    }
    else {
		return false;
    }
	//return true;
}
?>