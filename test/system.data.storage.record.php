<?php

	# Change Working Folder and Load Bootstrap #
	chdir("../");
	include("bootstrap.php");

	# Create a new Storage Object #
	$sampleStore 		= new storage('sample');

	# Get Record with ID #2 #
	$dataOnly			= $sampleStore->record(2);
	$recordObj 			= new record($dataOnly);

	# Display Results #
	print_r(array(
		'Data Only Result'		=> $dataOnly,
		'Record Object Result'	=> $recordObj
	));

?>