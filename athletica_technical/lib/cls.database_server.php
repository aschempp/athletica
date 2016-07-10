<?php
/**
* class for server database connections
*
* @package Athletica Technical Client
* @subpackage Classes
*
* @author mediasprint gmbh, Domink Hadorn <dhadorn@mediasprint.ch>
* @copyright Copyright (c) 2012, mediasprint gmbh
*/

// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
	header('Location: index.php');
	exit();
}
// +++ make sure that the file was not loaded directly

class database_server {

	/**
	* holds the database connection
	* @var object
	*/
	public $connection_server = NULL;

	/**
	* holds the last database error
	*
	* @var string
	*/
	public $error_server = NULL;

	/**
	* intizializes the database class and tries to estabilish database connections
	*
	* @return NULL
	*/
	function __construct(){
        global $cfg_value;
		//	+++ main database connection
		try {
			$dsn = $cfg_value['server']['server_engine'].':host='.$cfg_value['server']['server_host'].';port='.$cfg_value['server']['server_port'].';dbname='.$cfg_value['server']['server_db'];
			$this->connection_server = new PDO($dsn, $cfg_value['server']['server_username'], $cfg_value['server']['server_password']);

			$this->connection_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// +++ set names to UTF-8
			try {
				$sql_names = "SET NAMES 'utf8';";
				$query_names = $this->connection_server->exec($sql_names);

				$sql_character_set = "SET CHARACTER SET utf8;";
				$query_character_set = $this->connection_server->exec($sql_character_set);
			} catch(PDOException $e){
			}
			// --- set names to UTF-8
		} catch(PDOException $e){
			$this->connection_server = NULL;

			$this->error_server = $e->getMessage();
		}
		//	--- main database connection


		// register a shutdown function that closes the database connection
		register_shutdown_function(array($this, 'disconnect'));

		return;
	}

	/**
	* closes the database connection
	*
	* @return NULL
	*/
	public function disconnect(){
		$this->connection_server = NULL;

		return;
	}

}

// instantiate the database class
$cls_database_server = new database_server();
$glb_connection_server = $cls_database_server->connection_server;

?>