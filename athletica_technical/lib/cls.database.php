<?php
/**
* class for database connections
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

class database {
    
	/**
	* holds the database connection
	* @var object
	*/
	public $connection = NULL;

	/**
	* holds the last database error
	*
	* @var string
	*/
	public $error = NULL;

	/**
	* intizializes the database class and tries to estabilish database connections
	*
	* @return NULL
	*/
	function __construct(){
		//	+++ main database connection
		try {
			$dsn = CFG_DB_ENGINE.':host='.CFG_DB_HOST.';port='.CFG_DB_PORT.';dbname='.CFG_DB_DATABASE;
			$this->connection = new PDO($dsn, CFG_DB_USERNAME, CFG_DB_PASSWORD);

			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// +++ set names to UTF-8
			try {
				$sql_names = "SET NAMES 'utf8';";
				$query_names = $this->connection->exec($sql_names);

				$sql_character_set = "SET CHARACTER SET utf8;";
				$query_character_set = $this->connection->exec($sql_character_set);
			} catch(PDOException $e){
			}
			// --- set names to UTF-8
		} catch(PDOException $e){
			$this->connection = NULL;

			$this->error = $e->getMessage();
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
		$this->connection = NULL;

		return;
	}

}

// instantiate the database class
$cls_database = new database();
$glb_connection = $cls_database->connection;
?>