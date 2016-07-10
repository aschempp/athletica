<?php
/**
* initializes common settings
*
* @package Athletica Technical Client
* @subpackage Global
* @category Init
*
* @author mediasprint gmbh, Domink Hadorn <dhadorn@mediasprint.ch>
* @copyright Copyright (c) 2012, mediasprint gmbh
*/

// +++ include global init file
require_once(GLOBAL_PATH.'lib/inc.init.php');
// --- include global init file

// +++ include all needed classes
require_once(ROOT_PATH.'lib/cls.session.php');
require_once(ROOT_PATH.'lib/cls.pagination.php');
// --- include all needed classes

// +++ needed global variables and constants
define('LOGGED_IN', session::check_login());
$glb_login = (LOGGED_IN && isset($_SESSION[CFG_SESSION]['login'])) ? $_SESSION[CFG_SESSION]['login'] : NULL;
// --- needed global variables and constants



// +++ extend max execution time for AJAX scripts
if(CURRENT_CATEGORY=='ajax'){
	set_time_limit(3600);
}
// --- extend max execution time for AJAX scripts
?>