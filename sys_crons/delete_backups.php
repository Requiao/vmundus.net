<?php
	//Description: Delete backups older than 30 days. run every 72 hours
	include('/var/www/html/connect_db.php');

	$files = scandir('/home/mundus/bkup', 1);
	$dir_path = "/home/mundus/bkup/";
	foreach ($files as $value) {
		if(filemtime($dir_path . $value) <= strtotime(" - 30 days", time())) {
			if($value != '.' && $value != '..' && $value != 'mysqldump.sh') {
				unlink($dir_path . $value);
				echo "file deleted: $value \n";
			}
		}
	}
?>