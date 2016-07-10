<?php
/**
* class for the error handling
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

class error_handling extends obj {

	/**
	* holds all errors thrown during the execution of the script
	*
	* @var array
	*/
	private $errors = array();

	/**
	* holds all ignore-patterns
	*
	* @var array
	*/
	private $ignore = array(
		'^unserialize\(\)',
	);

	/**
	* intizializes the error handler class
	*
	* @return NULL
	*/
	function __construct(){
		// set the error handler
		set_error_handler(array($this, 'handle_error'));

		// register a shutdown function that outputs the error messages
		register_shutdown_function(array($this, 'process_errors'));

		// +++ set order settings
		$this->order_setting = array(
			'log_error_date' => array(
				array('log_error_level', 'DESC'),
				array('log_error_file', 'ASC'),
				array('log_error_line', 'ASC'),
				array('log_error_message', 'ASC'),
			),
			'log_error_level' => array(
				array('log_error_date', 'ASC'),
				array('log_error_file', 'ASC'),
				array('log_error_line', 'ASC'),
				array('log_error_message', 'ASC'),
			),
			'log_error_line' => array(
				array('log_error_date', 'ASC'),
				array('log_error_level', 'DESC'),
				array('log_error_file', 'ASC'),
				array('log_error_message', 'ASC'),
			),
			'default' => array(
				array('log_error_date', 'ASC'),
				array('log_error_level', 'DESC'),
				array('log_error_file', 'ASC'),
			),
		);

		$this->_set_order(array('log_error_date', 'DESC'));
		// --- set order settings

		return;
	}

	/**
	* handles errors
	*
	* @param integer $log_error_level
	* @param string $log_error_message
	* @param string $log_error_file
	* @param integer $log_error_line
	* @return NULL
	*/
	function handle_error($log_error_level, $log_error_message, $log_error_file, $log_error_line){
		$ignore = FALSE;
		foreach($this->ignore as $pattern){
			if(preg_match('/'.$pattern.'/i', $log_error_message)){
				$ignore = TRUE;
				break;
			}
		}

		if(!$ignore){
			$this->errors[] = array(
				'log_error_date' => time(),
				'log_error_level' => $log_error_level,
				'log_error_message' => $log_error_message,
				'log_error_file' => $log_error_file,
				'log_error_line' => $log_error_line,
			);
		}

		return;
	}

	/**
	* outputs errors while in debug mode and writes them into the error log
	*
	* @return NULL
	*/
	function process_errors(){
		global $glb_connection;
		global $glb_login;

		if(!is_null($glb_connection) && count($this->errors)>0){
			try {
				$sql_insert = "INSERT INTO t_log_error
									   SET log_error_date = :log_error_date,
										   log_error_level = :log_error_level,
										   log_error_message = :log_error_message,
										   log_error_file = :log_error_file,
										   log_error_line = :log_error_line;";
				$query_insert = $glb_connection->prepare($sql_insert);

				foreach($this->errors as $error){
					try {
						$query_insert->bindValue(':log_error_date', date('Y-m-d H:i:s', $error['log_error_date']));
						$query_insert->bindValue(':log_error_level', $error['log_error_level']);
						$query_insert->bindValue(':log_error_message', $error['log_error_message']);
						$query_insert->bindValue(':log_error_file', $error['log_error_file']);
						$query_insert->bindValue(':log_error_line', $error['log_error_line']);

						$query_insert->execute();
					} catch(PDOException $e){
					}
				}
			} catch(PDOException $e){
			}
		}

		if(CFG_DEBUG && count($this->errors)>0){
			print_arr($this->errors);
		}

		return;
	}

	/**
	* gets errors
	*
	* @return array array with all errors found, array with all elements of an error if an ID is provided or NULL if no particular error was found
	*/
	function get(){
		global $glb_connection;

		$return = array();

		try {
			$select_get = ($this->get_total) ? "COUNT(*) AS total " : "*";
			$and_get = $this->_get_filter();
			$order_get = $this->_get_order();
			$limit_get = $this->_get_limit();

			$sql_get = "SELECT ".$select_get."
						  FROM t_log_error
						 WHERE log_error_id > 0
						   ".$and_get."
					  ".$order_get."
						 ".$limit_get.";";
			$query_get = $glb_connection->prepare($sql_get);

			// +++ bind parameters
			foreach($this->filter as $field => $value){
				$field = str_replace('.', '_', $field);

				$query_get->bindValue(':'.$field, $value);
			}

			foreach($this->filter_like as $field => $value){
				$field = str_replace('.', '_', $field);

				$query_get->bindValue(':'.$field, $value."%");
			}

			foreach($this->filter_special as $filter_element){
				foreach($filter_element['parameters'] as $parameter => $value){
					$query_get->bindValue(':'.$parameter, $value."%");
				}
			}
			// --- bind parameters

			$query_get->execute();
			$rows = $query_get->fetchAll(PDO::FETCH_ASSOC);

			foreach($rows as $row){
				if($this->get_total){
					$return = (isset($row['total'])) ? $row['total'] : 0;
				} else {
					$return[] = $row;
				}
			}

			if($this->get_single_row){
				if(count($return)==1){
					$return = $return[0];
				} else {
					$return = NULL;
				}
			}
		} catch(PDOException $e){
			trigger_error($e->getMessage());
		}

		return $return;
	}

	/**
	* deletes one or all errors from the database
	*
	* @param integer $log_error_id the error to be deleted, 0 for truncate
	* @return string returns ok if the error(s) was/were deleted, otherwise the error message
	*/
	static function delete($log_error_id){
		global $glb_connection;

		$return = 'error';

		try {
			$sql_delete = "";

			if($log_error_id==0){
				$sql_delete = "TRUNCATE TABLE t_log_error;";
			} else {
				$sql_delete = "DELETE FROM t_log_error
									 WHERE log_error_id = ".$log_error_id.";";
			}
			$query_delete = $glb_connection->query($sql_delete);

			$return = 'ok';
		} catch(PDOException $e){
			trigger_error($e->getMessage());
		}

		return $return;
	}

}

// instantiate class
$cls_error_handling = new error_handling();
?>