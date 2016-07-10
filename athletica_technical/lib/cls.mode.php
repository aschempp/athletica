<?php
/**
* class: provides functions for the management of input modes
* outside: checks the currently selected mode and loads the right data
*
* @package Athletica Technical Client
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

class mode {

	/**
	* holds all modes
	* @var array
	*/
	public $modes = array();

	/**
	* class constructor
	*
	* @return NULL
	*/
	function __construct(){
		global $glb_connection;
        global $modes;

		if(!is_null($glb_connection)){
			try {
				foreach($modes as $mode){
					$this->modes[$mode] = $mode;
				}

				// +++ get current mode
				$current_mode = CFG_DEFAULT_MODE;

				if(isset($_GET['mode']) && ctype_alpha($_GET['mode']) && in_array($_GET['mode'], $this->modes)){
					$current_mode = $_GET['mode'];
				} elseif(isset($_SESSION[CFG_SESSION]['mode']) && ctype_alpha($_SESSION[CFG_SESSION]['mode']) && in_array($_SESSION[CFG_SESSION]['mode'], $this->modes)){
					$current_mode = $_SESSION[CFG_SESSION]['mode'];
				} elseif(isset($_COOKIE[CFG_COOKIE])){
					$cookie = unserialize($_COOKIE[CFG_COOKIE]);

					if(isset($cookie['mode']) && ctype_alpha($cookie['mode']) && in_array($cookie['mode'], $this->modes)){
						$current_mode = $cookie['mode'];
					}
				}

				define('CFG_CURRENT_MODE', $current_mode);
				// --- get current language

			} catch(PDOException $e){
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}

	}
}

// instantiate the mode class
$cls_mode = new mode();

// +++ set session and cookie
if(!defined('CFG_CURRENT_MODE')){
	define('CFG_CURRENT_MODE', CFG_DEFAULT_MODE);
}

$_SESSION[CFG_SESSION]['mode'] = CFG_CURRENT_MODE;
set_cookie('mode', CFG_CURRENT_MODE);
// --- set session and cookie
?>