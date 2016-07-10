<?php
/**
* settings for the entire application
*
* @package Athletica Technical Client
* @subpackage Global
* @category Settings
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

// +++ database settings
/**
* database engine
* @var string
*/
define('CFG_DB_ENGINE', 'mysql');
/**
* database host
* @var string
*/
define('CFG_DB_HOST', 'localhost');
/**
* database port
* @var string
*/
define('CFG_DB_PORT', '3306');
/**
* database username
* @var string
*/
define('CFG_DB_USERNAME', 'athletica');
/**
* database password
* @var string
*/
define('CFG_DB_PASSWORD', 'athletica');
/**
* database
* @var string
*/
define('CFG_DB_DATABASE', 'athletica_technical');

define('CFG_DB_TEMPRESULT_PREFIX', 'tempresult_');
// --- database settings

/**
* debug-mode
* @var boolean
*/
define('CFG_DEBUG', FALSE);

/**
* master password
* @var string
*/
define('CFG_MASTER_PASSWORD', 'ath_tech');

/**
* the application's timezone
* @var string
*/
define('CFG_TIMEZONE', 'Europe/Zurich');

// set timezone
date_default_timezone_set(CFG_TIMEZONE);
?>
