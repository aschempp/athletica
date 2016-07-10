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

/**
* flag to avoid the direct access to files
* @var boolean
*/
define('CTRL_DIRECT_ACCESS', TRUE);

// set the error reporting to ALL in order to allow a more bug-free programming
error_reporting(E_ALL);

// start the session
session_start();

// +++ include all needed files (settings, error handling, DB connection, etc.)
require_once(GLOBAL_PATH.'lib/cls.obj.php');
require_once(GLOBAL_PATH.'lib/cls.php_ini.php');
require_once(GLOBAL_PATH.'lib/inc.settings.php');
require_once(ROOT_PATH.'lib/inc.settings.php');
require_once(GLOBAL_PATH.'lib/inc.common.php');
require_once(GLOBAL_PATH.'lib/cls.error_handling.php');
require_once(GLOBAL_PATH.'lib/cls.dBug.php');
require_once(GLOBAL_PATH.'lib/cls.database.php');
require_once(GLOBAL_PATH.'lib/cls.config.php');
require_once(GLOBAL_PATH.'lib/cls.database_server.php');
require_once(GLOBAL_PATH.'lib/cls.language.php');
require_once(GLOBAL_PATH.'lib/cls.mode.php');
// +++ load language strings from athletica used fpr different functions
require_once(ROOT_PATH.'lang/'.CFG_CURRENT_LANGUAGE.'.inc.php');
// ---
require_once(ROOT_PATH.'lib/inc.convtables.php');
require_once(ROOT_PATH.'lib/inc.saveResult.php');
require_once(ROOT_PATH.'lib/cls.tempresult.php');
// --- include all needed files (settings, error handling, DB connection, etc.)

// +++ include all needed classes
require_once(ROOT_PATH.'lib/cls.meeting.php');
require_once(ROOT_PATH.'lib/cls.event.php');
//require_once(ROOT_PATH.'lib/cls.result.php');
// --- include all needed classes

require_once(ROOT_PATH.'lib/inc.settings_athletica.php');

$glb_uri = get_URI('lang');

?>
