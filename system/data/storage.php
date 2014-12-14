<?php

	class storage {

		public 		$ready 				= false;

		protected 	$table;
		protected 	$database;
		protected 	$sqlLog;
		protected  	$errors				= array();

		private 	$definition;
		private 	$definitionFile;
		private 	$exists;
		private 	$createdTable;

		function __construct($tableName, $autoCreateTable = false) {
			global $database;

			$this->table 		= $tableName;
			$this->database 	= $database;

			$this->exists 		= $this->tableExists();
			$this->definition 	= $this->definition();

			if ($autoCreateTable and !$this->exists) {
				$this->exists 	= $this->createTable();
			}

			$this->ready 		= $this->exists;
		}

		protected function definition() {
			$this->definitionFile 		= getcwd().("/store/{$this->table}/table.def");
			if (!$this->definitionFile) return $this->reportError("No Definition File: {$this->definitionFile}");

			$content 			= file_get_contents($this->definitionFile);
			if (!$content) 		return $this->reportError("Definition Empty: {$this->definitionFile}");

			$transformed 		= implode(",\n", explode("\n", $content));

			$sql 				= "CREATE TABLE IF NOT EXISTS {$this->table} ($transformed);";
			return $sql;
		}

		protected function tableExists() {
			$sql 			= "DESCRIBE `{$this->table}`";
			$this->sqlLog[]	= $sql;
			$result 		= $this->database->query($sql);
			return ($result !== false);
		}

		protected function update(array $record, $insertIfEmpty = false) {
			if (!$this->exists) return $this->reportError("Cannot Update. Table does not Exist: {$this->table}");

			if (!isset($record['id'])) {
				if ($insertIfEmpty) {
					return $this->insert($record);
				} else {
					return false;
				}
			}

			$item 	= $this->record($record['id']);
			if (!$item or !isset($item['id'])) {
				if ($insertIfEmpty) {
					return $this->insert($record);
				} else {
					return false;
				}
			}

			$rId 			= $record['id'];
			unset($record['id']);

			$updateBuffer	= array();
			$updateStr 		= null;
			foreach ($record as $field => $value) {
				$itemValue 			= $item[$field];
				if ($itemValue == $value) continue;
				$updateBuffer[] 	= "`$field` = '$value'";
			}
			$updateStr 		= implode(", ", $updateBuffer);

			$sql 			= "UPDATE `{$this->table}` SET $updateStr WHERE `id` = '$rId';";
			$this->sqlLog[]	= $sql;
			$result 		= $this->database->query($sql);
			if (!$result) return false;

			return $rId;
		}

		protected function insert(array $record) {
			if (!$this->exists) return $this->reportError("Cannot Insert. Table does not Exist: {$this->table}");

			$fieldStr 		= "`".implode("`,`", array_keys($record))."`";
			$valueStr 		= "'".implode("','", array_values($record))."'";
			$sql 			= "INSERT INTO `{$this->table}` ($fieldStr) VALUES ($valueStr)";
			$this->sqlLog[]	= $sql;
			$result 		= $this->database->query($sql);
			if (!$result) 	return false;

			$rId 	 		= $this->database->insert_id;
			return $rId;
		}

		protected function reportError($err) {
			$this->errors[microtime(true)] 	= $err;
			return false;
		}

		protected function createTable() {
			if (!$this->definitionFile or !$this->definition) {
				return $this->reportError("Cannot Create Table. Definition is Empty: {$this->table}");
			}

			$this->sqlLog[]	= $this->definition;
			$result 	= $this->database->query($this->definition);
			if ($result === false) return $this->reportError("Table NOT Created!: {$this->table}");;

			if ($this->tableExists()) {
				$this->createdTable 	= true;
				return true;
			}

			return false;
		}

		protected function dropTable() {

			if (!$this->exists) return $this->reportError("Cannot Drop Table. Table does not Exist: {$this->table}");

			return true;
		}

		public function queryTable($sql) {
			if (!$this->exists) return $this->reportError("Cannot Query. Table does not Exist: {$this->table}");

			$this->sqlLog[]	= $sql;
			$result	= $this->database->query($sql);
			if (!$result) return $this->reportError("Cannot Query. Bad SQL:\n$sql");;

			$records 	= array();
			while ($row = $result->fetch_assoc()) {
				$record 	= new record($row, $this);
				$records[]	= $record;
			}

			return (!$records or count($records) == 0)
				? false
				: $records
			;
		}

		public function deleteRecord(record $record) {
			if (!$this->exists) return $this->reportError("Cannot Delete Record. Table does not Exist: {$this->table}");

			if (!$record or !isset($record['id'])) return $this->reportError("Cannot Delete Record. Record or Record ID does not Exist: {$this->table}");;
			$sql 			= "DELETE FROM `{$this->table}` WHERE `id` = '{$record['id']}'";
			$this->sqlLog[]	= $sql;
			$result 		= $this->database->query($sql);
			if (!$result) 	return false;
			return true;
		}

		public function commitRecord(record $record) {
			if (!$this->exists) return $this->reportError("Cannot Commit Record. Table does not Exist: {$this->table}");;

			if ($record->id) {
				$id 	= $this->update($record(), true);
			} else {
				$id 	= $this->insert($record());
			}

			return $id;
		}

		public function record($id) {
			if (!$this->exists) return $this->reportError("Cannot Retrieve Record. Table does not Exist: {$this->table}");;

			$sql 			= "SELECT * FROM `{$this->table}` WHERE `id` = '$id'";
			$this->sqlLog[]	= $sql;
			$result			= $this->database->query($sql);
			if (!$result) 	return false;

			$records 		= array();
			while ($row = $result->fetch_assoc()) {
				$records[]	= $row;
			}

			return (!$records or count($records) == 0)
				? false
				: $records[0]
			;
			
		}

	}

	class record {

		protected $fields		= array();
		protected $values 		= array();
		protected $autoCommit 	= false;

		private $storage		= null;

		function __construct(array $newRecord = array(), storage &$store = null) {
			if ($store) $this->autoCommit($store);
			$this($newRecord);
		}

		function __invoke(array $newRecord = array()) {

			if (!empty($newRecord)) {

				if ($this->isAssoc($newRecord)) {
					if (!isset($newRecord['id'])) {
						$this->fields 	= array_keys($newRecord);
						$this->values 	= array_values($newRecord);

						if ($this->autoCommit) $this->commit($this->storage);
					} elseif ($newRecord['id']) {
						if ($this->storage) {
							$record 	= $this->storage->record($newRecord['id']);

							if ($record) {
								$this->fields 	= array_keys($record);
								$this->values 	= array_values($record);
							}
						} else {
							$this->fields 	= array_keys($newRecord);
							$this->values 	= array_values($newRecord);
						}
					}
				}

			}

			return array_combine($this->fields, $this->values);
		}

		function __toString() {
			return explode(",", $this->values);
		}

		function __get($key) {
			$array 	= $this->asArray();

			return (array_key_exists($key, $array))
				? $array[$key]
				: null
			;
		}

		function __set($key, $value) {
			$array 			= $this->asArray();
			$array[$key]	= $value;
			//$this($array);

			$this->fields 	= array_keys($array);
			$this->values 	= array_values($array);

			if ($this->autoCommit and $this->storage) {
				$this->commit($this->storage);
			}
		}

		public function autoCommit(storage &$store) {
			if (!$store) return false;

			$this->autoCommit 	= true;
			$this->storage 		= &$store;
		}

		public function delete(storage &$store = null) {
			if (!$store and !$this->storage) return false;

			$handler 	= ($store) ? $store : $this->storage;
			return $handler->deleteRecord($this);
		}

		public function asArray() {
			return $this();
		}


		public function commit(storage &$store) {
			$id 		= $store->commitRecord($this);
			if ($id == false) return false;
			return $id;
		}

		protected function prop($key, $value = null) {
			if ($value) {
				$array 			= $this->asArray();
				$array[$key]	= $value;
				$this->fields 	= array_keys($array);
				$this->values 	= array_values($array);
			}

			return $this->$key;
		}

		private function isAssoc($arr) {
    		return array_keys($arr) !== range(0, count($arr) - 1);
		}

	}

?>