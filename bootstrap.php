<?php

	# Connect to Database #
	global $database;
	include('data/connection.php');
	$mysql 		= new data_connection();
	$database 	= $mysql->connection();

?>
