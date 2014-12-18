<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="../assets/js/niot.js"></script>
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

	# Create a new object with useful methods from the array #
	# Attach the storage at the time of creation #
	$smartObj 	= new record($recData, $store);

	# Change the object field values with a SET #
	$smartObj->name = "New Name";

	# No need to commit, once the field is set it is auto-committed to the storage #

	</pre>

	You can change the smartCommit property which performs a second select statement
	on the record which may be unneeded. With smartCommit set to false, record->commit()
	will commit no questions asked.
	<br/>
	In contrast, with smartCommit set to true, updating will check the database to see
	if the record needs updated before updating. A read could save a write. 
</p>
<hr/>
<p>
	<script>
		function test() {

			var niot = new niotStore('sample', '../ajax.php');

			niot.record(2, function(result) {
				console.log(result);
			});
		}
	</script>
	<button onclick="test();" type="button">Here</button>
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
	$smartObj 	= new record($recData, $store);

	# Change the object field values with a SET #
	$smartObj->name = "New Name";

	?><pre><?php print_r($smartObj); ?></pre>