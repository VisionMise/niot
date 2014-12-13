<?php

	class storage {

		protected $table;
		protected $database;

		function __construct($tableName, $autoCreateTable = false) {
			global $database;

			$this->table 	= $tableName;
			$this->database = $database;

			if ($autoCreateTable and !$this->tableExists()) {
				$this->createTable($autoCreateTable);
			}
		}

		private function tableExists() {
			$sql 		= "DESCRIBE `{$this->table}`";
			$result 	= $this->database->query($sql);
			return ($result !== false);
		}

		private function update(array $record, $insertIfEmpty = false) {
			if (!isset($record['id'])) {
				if ($insertIfEmpty) {
					return $this->insert($record)
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
				$updateBuffer[] 	= "`$field` = '$value`";
			}
			$updateStr 		= implode(", ", $updateBuffer);

			$sql 			= "UPDATE `{$this->table}` SET $updateStr WHERE `id` = '$rId'"
			$result 		= $this->database->query($sql);
			if (!$result) return false;

			return $rId;
		}

		private function insert(array $record) {

			$fieldStr 	= "`".implode("`,`", array_keys($record))."`";
			$valueStr 	= "'".implode("','", array_values($record))."'";
			$sql 		= "INSERT INTO `{$this->table}` ($fieldStr) VALUES ($valueStr)";
			$result 	= $this->database->query($sql);
			if (!$result) return false;

			$rId 	 	= $this->database->insert_id;
			return $rId;
		}


		public function createTable(array $fields) {

		}

		public function deleteTable() {

		}

		public function queryTable($sql) {
			$result	= $this->database->query($sql);
			if (!$result) return false;

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
			if (!$record or !isset($record['id'])) return false;
			$sql 	= "DELETE FROM `{$this->table}` WHERE `id` = '{$record['id']}'";
			$result = $this->database->query($sql);
			if (!$result) return false;
			return true;
		}

		public function commitRecord(record $record) {
			if (isset($record['id'])) {
				$id 	= $this->update($record, true);
			} else {
				$id 	= $this->insert($record);
			}

			return $id;
		}

		public function record($id) {
			$sql 	= "SELECT * FROM `{$this->table}` WHERE `id` = '$id'";
			$result	= $this->database->query($sql);
			if (!$result) return false;

			$records 	= array();
			while ($row = $result->fetch_assoc()) {
				$record 	= new record($row, $this);
				$records[]	= $record;
			}

			return (!$records or count($records) == 0)
				? false
				: $records[0]
			;
			
		}

	}

	class record() {

		protected $fields		= array();
		protected $values 		= array();
		protected $autoCommit 	= false;

		private $storage		= null;

		function __construct(array $newRecord = array(), storage &$store = null) {
			$this($newRecord);
			if ($store) $this->autoCommit($store);
		}

		function __invoke(array $newRecord = array()) {
			if ($newRecord) {
				if ($this->isAssoc($newRecord)) {
					$this->fields 	= array_keys($newRecord);
					$this->values 	= array_values($newRecord);
				}
			}

			return array_combine($this->fields, $this->values);
		}

		function __toString() {
			return explode(",", $this->values);
		}

		function __get($key) {
			$array 	= $this->array();
			return (array_key_exists($key, $array))
				? $array[$key]
				: null
			;
		}

		function __set($key, $value) {
			$array 			= $this->array();
			$array[$key]	= $value;
			$this($array);

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

		public function array() {
			return $this();
		}


		public function commit(storage &$store) {
			$id 		= $store->commitRecord($this);
			if ($id == false) return false;

			$this->id 	= $id;
			return $this->id;
		}

		private function isAssoc($arr) {
    		return array_keys($arr) !== range(0, count($arr) - 1);
		}

	}

?>