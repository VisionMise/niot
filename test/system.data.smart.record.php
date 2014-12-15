<h1>Smart Record</h1>


<p>
	Create Storage and get a record by it's ID
	<pre>
	# Create a Store Object #
	$store 		= new storage('sample');

	# We want a record with an id of 2 #
	$id 		= 2;

	# Get the record as an array from the storage #
	$recData	= $store->record($id);

	# Create a new object with useful methods form the array #
	# Attach the storage at the time of creation #
	$dumbObj 	= new record($recData, $store);

	# Change the object field values with a SET #
	$dumbObj->name = "New Name";

	# No need to commit, once the field is set it is auto-committed to the storage #

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
	$dumbObj 	= new record($recData, $store);

	# Change the object field values with a SET #
	$dumbObj->name = "New Name";

	?><pre><?php print_r($dumbObj); ?></pre>