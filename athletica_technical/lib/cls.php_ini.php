<?php
/**
* class for php.ini checks
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

class php_ini {

	/**
	* defines if the current PHP settings are valid
	* @var boolean
	*/
	public $valid = TRUE;

	/**
	* holds the result of the ini-check
	* @var array
	*/
	public $result = array();

	/**
	* needed PHP settings and extensions
	* @var array
	*/
	private $needed_configuration = array(
		'settings' => array(
			array(
				'name' => '__php_version__',
				'recommended_value' => '5.2',
				'recommended_operator' => '>=',
				'recommended_text' => '',
				'bindValue' => array(),
				'info' => 'The current application requires functions which are not available until PHP version <b>%PHP_VERSION_RECOMMENDED%</b>.',
				'error_type' => 'fatal',
			),
			array(
				'name' => 'magic_quotes_gpc',
				'recommended_value' => 0,
				'recommended_operator' => '=',
				'recommended_text' => 'Off',
				'bindValue' => array(':empty:' => 'Off', '0' => 'Off', '1' => 'On'),
				'info' => 'The setting magic_quotes_gpc is obsolete and results in major problems with form inputs. The turning off of this setting is mandatory.',
				'error_type' => 'fatal',
			),
			array(
				'name' => 'magic_quotes_runtime',
				'recommended_value' => 0,
				'recommended_operator' => '=',
				'recommended_text' => 'Off',
				'bindValue' => array(':empty:' => 'Off', '0' => 'Off', '1' => 'On'),
				'info' => 'The setting magic_quotes_runtime is obsolete and results in major problems with form inputs. The turning off of this setting is mandatory.',
				'error_type' => 'fatal',
			),
			array(
				'name' => 'register_globals',
				'recommended_value' => 0,
				'recommended_operator' => '=',
				'recommended_text' => 'Off',
				'bindValue' => array(':empty:' => 'Off', '0' => 'Off', '1' => 'On'),
				'info' => 'The setting register_globals is obsolete and a common security problem for every application.',
				'error_type' => 'fatal',
			),
		),
		'extensions' => array(
			array(
				'name' => 'json',
				'info' => 'This extension implements the JavaScript Object Notation (JSON) data-interchange format.',
				'error_type' => 'warning',
			),
			array(
				'name' => 'pdo',
				'info' => 'The extension PDO provides important database functions and is required to estabilish a connection to the database server.',
				'error_type' => 'fatal',
			),
			array(
				'name' => 'pdo_mysql',
				'info' => 'This extensions extends PDO\'s basic database functions with specific functions for the MySQL database engine.',
				'error_type' => 'fatal',
			),
			array(
				'name' => 'pdo_odbc',
				'info' => 'This extensions extends PDO\'s basic database functions with specific functions for the use with ODBC drivers.',
				'error_type' => 'fatal',
			),
		),
	);

	/**
	* intizializes the php.ini class and checks the current PHP settings
	*
	* @return NULL
	*/
	function __construct(){
		foreach($this->needed_configuration as $type => $settings){
			foreach($settings as $setting){
				$passed = false;
				$message_type = 'ok';

				if($type=='settings'){
					$ini_value = ($setting['name']=='__php_version__') ? PHP_VERSION : ini_get($setting['name']);
					$key = ($ini_value=='') ? ':empty:' : $ini_value;

					$value_text = (isset($setting['bindValue'][$key])) ? $setting['bindValue'][$key] : $ini_value;

					if($setting['name']=='__php_version__'){
						$passed = version_compare($ini_value, $setting['recommended_value'], $setting['recommended_operator']);
					} else {
						switch($setting['recommended_operator']){
							case '=';
								$passed = (intval($ini_value)==$setting['recommended_value']);
								break;
							case '>';
								$passed = (intval($ini_value)>$setting['recommended_value']);
								break;
							case '>=';
								$passed = (intval($ini_value)>=$setting['recommended_value']);
								break;
							case '<';
								$passed = (intval($ini_value)<$setting['recommended_value']);
								break;
							case '<=';
								$passed = (intval($ini_value)<=$setting['recommended_value']);
								break;
							case '!=';
								$passed = (intval($ini_value)!=$setting['recommended_value']);
								break;
						}
					}

					$message_type = (($passed) ? 'success' : $setting['error_type']);

					$this->result[$type][] = array(
						'name' => (($setting['name']=='__php_version__') ? 'PHP' : $setting['name']),
						'value' => $value_text,
						'message_type' => $message_type,
						'recommended' => (($setting['recommended_text']!='') ? $setting['recommended_text'] : $setting['recommended_value']),
						'info' => $setting['info'],
					);
				} else {
					//$setting['name'] = format($setting['name'], '', '%DATABASE_ENGINE%', $cfg_db['engine']);

					$passed = (extension_loaded($setting['name']));
					$message_type = (($passed) ? 'success' : $setting['error_type']);

					$this->result[$type][] = array(
						'name' => $setting['name'],
						'message_type' => $message_type,
						'info' => $setting['info'],
					);
				}

				if($message_type=='fatal'){
					$this->valid = FALSE;
				}
			}
		}

		return;
	}

}

// instantiate the php.ini class
$cls_php_ini = new php_ini();
?>
