<?php
	//Description: Delete all chat messages and notifications if older than N days or leave last N messages

	include('/var/www/html/connect_db.php');
	
	$DAYS = 30;
	$MESSAGES = 500;
	
	//delete messages
	//older than N days
	$query = "DELETE FROM modified_messages WHERE message_id IN
			  (SELECT message_id FROM chat WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL '$DAYS' DAY) <= NOW())";
	$conn->query($query);
	
	$query = "DELETE FROM unread_chat_messages WHERE message_id IN
			  (SELECT message_id FROM chat WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL '$DAYS' DAY) <= NOW())";
	$conn->query($query);
	
	$query = "DELETE FROM chat WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL '$DAYS' DAY) 
			  <= NOW()";
	$conn->query($query);
	
	//if more than N messages in the chat
	$query = "SET @num := 0"; 
	$conn->query($query);
	$query = "SET @chat_id := ''";
	$conn->query($query);
	
	$query = "DELETE FROM unread_chat_messages WHERE message_id IN (SELECT message_id FROM (
			  SELECT chat_id, message_id, date, time,
			  @num := IF(@chat_id = chat_id, @num + 1, 1) AS row_number,
			  @chat_id := chat_id AS dummy
			  FROM chat
			  ORDER BY chat_id, date DESC, time DESC) AS x 
			  WHERE x.row_number > '$MESSAGES')";
	$conn->query($query);
	
	$query = "DELETE FROM modified_messages WHERE message_id IN (SELECT message_id FROM (
			  SELECT chat_id, message_id, date, time,
			  @num := IF(@chat_id = chat_id, @num + 1, 1) AS row_number,
			  @chat_id := chat_id AS dummy
			  FROM chat
			  ORDER BY chat_id, date DESC, time DESC) AS x 
			  WHERE x.row_number > '$MESSAGES')";
	$conn->query($query);
	
	$query = "DELETE FROM chat WHERE message_id IN (SELECT message_id FROM (
			  SELECT chat_id, message_id, date, time,
			  @num := IF(@chat_id = chat_id, @num + 1, 1) AS row_number,
			  @chat_id := chat_id AS dummy
			  FROM chat
			  ORDER BY chat_id, date DESC, time DESC) AS x 
			  WHERE x.row_number > '$MESSAGES')";
	$conn->query($query);
	
	
	
	//delete notifications
	$query = "DELETE FROM notifications WHERE DATE_ADD(TIMESTAMP(date, time), INTERVAL '$DAYS' DAY) <= NOW()";
	$conn->query($query);
?>