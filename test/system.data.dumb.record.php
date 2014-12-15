<h1>Dumb Record</h1>


<p>
	Create Storage and get a record by it's ID
	<pre>
	# Create a Store Object #
	$store 		= new storage('sample');

	# We want a record with an id of 2 #
	$id 		= 2;

	# Get the record as an array from the storage #
	$recData	= $store->record($id);

	# Create a new object with useful methods from the array #
	$dumbObj 	= new record($recData);

	# Change the object field values with a SET #
	$dumbObj->name = "New Name";

	# Commit any changes to the storage #
	$dumbObj->commit($store);

	</pre>
</p>
<?php
	
	# Change Working Folder and Load Bootstrap #
	chdir("../");
	include("bootstrap.php");

	# Create a Store Object #
	$store 		= new storage('sample');

	# We want a record with an id of 2 #
	$id 		= 2;

	# Get the record as an array from the storage #
	$recData	= $store->record($id);

	# Create a new object with useful methods form the array #
	$dumbObj 	= new record($recData);

	# Change the object field values with a SET #
	$dumbObj->name = "New Name";

	?><pre><?php print_r($dumbObj); ?></pre>