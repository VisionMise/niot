<?php

	class data_connection {

		protected $settings;
		protected $connection;

		function __construct() {
			$this->settings 	= $this->loadSettings();
		}

		function __destruct() {
			$this->connection 	= null;
		}

		function __invoke() {
			return $this->connection;
		}

		private function loadSettings() {
			$file 		= realpath("cfg/database.json");
			$content 	= file_get_contents($file);
			$settings 	= json_decode($content, true);
			return $settings; 
		}

		public function connection() {
			return $this->connection;
		}

		public function connect() {
			$s 					= $this->settings;
			$this->connection 	= new mysqli($s['host'], $s['user'], $s['password'], $s['schema'], $s['port']);
		}

	}