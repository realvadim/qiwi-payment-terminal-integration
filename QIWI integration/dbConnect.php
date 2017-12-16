<?php
	//Connection to the database
	$db = mysql_connect("localhost","username","password");
	mysql_select_db("database_name", $db);
	
	mysql_query("set character_set_client='utf8'"); 
	mysql_query("set character_set_results='utf8'"); 
	mysql_query("set collation_connection='utf8_general_ci'");
?>