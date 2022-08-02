<?php
//Description: Perform conection to database.

//connect to database
$servername = "localhost";
$username = "u153769202_vmundus";
$password = "b6^~@Qi1";
$database = "u153769202_vmundus";

// Create connection
$conn = connectdb($servername, $username, $password, $database);
function connectdb($servername, $username, $password, $database) {
	$conn = mysqli_connect($servername, $username, $password, $database);
	return $conn;
}
$conn->set_charset("utf8");
?>