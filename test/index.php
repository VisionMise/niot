<pre><?php

	chdir("../");

	include("bootstrap.php");


	$test 	= new storage('sample', true);

	$data 	= array(
		'id'		=> 2
	);

	$record = new record($data);


	$record->name = "hfhfhf".microtime();
	
	$record->commit($test);

	print_r($record);

?></pre>