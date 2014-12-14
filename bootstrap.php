<?php

	# Connect to Database #
	global $database;
	include('system/data/connection.php');
	$mysql 		= new data_connection();
	$database 	= $mysql->connection();


	# Include Storage and Record Classes #
	include('system/data/storage.php');


	# Include Operations Procedures #
	include('system/operations.php');
	

?>
