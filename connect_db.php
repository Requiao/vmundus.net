<?php
//Description: Perform conection to database.

//connect to database
$servername = "localhost";
$username = "u153769202_lixo";
$password = "e8EMxmyL=";
$database = "u153769202_lixo";

// Create connection
$conn = connectdb($servername, $username, $password, $database);
function connectdb($servername, $username, $password, $database) {
	$conn = mysqli_connect($servername, $username, $password, $database);
	return $conn;
}
$conn->set_charset("utf8");
?>