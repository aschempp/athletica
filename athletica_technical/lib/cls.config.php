<?php
/**
* class: provides functions for the management of config data
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

class config {

	/**
	* holds all strings for the current language
	* @var array
	*/
	public $cfg = array();

	/**
	* holds all strings for all translations
	* @var array
	*/
	public $cfg_value = array();

	/**
	* class constructor, gets languages and translation strings
	*
	* @return NULL
	*/
	function __construct(){
		global $glb_connection;

		if(!is_null($glb_connection)){
			try {
				$sql_config = "SELECT DISTINCT config_group
							   FROM t_config
						   ORDER BY config_group ASC;";
				$query_config = $glb_connection->query($sql_config);

				$configs = $query_config->fetchAll(PDO::FETCH_ASSOC);

				// +++ prepare strings query
				$sql_string = "SELECT *
								 FROM t_config
								WHERE config_group = :config_group
							 ORDER BY config_key ASC;";
				$query_string = $glb_connection->prepare($sql_string);
				// --- prepare strings query

				foreach($configs as $config){
					// +++ bind parameters
					$query_string->bindValue(':config_group', $config['config_group']);
					// --- bind parameters

					$query_string->execute();

					$values = $query_string->fetchAll(PDO::FETCH_ASSOC);
					foreach($values as $value){
						$this->cfg_value[$value['config_group']][$value['config_key']] = $value['config_value'];
					}
				}

			} catch(PDOException $e){
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}
	}
    
    function updateConfig($data){
        global $glb_connection;

        $return = 'error';
        
        if(!is_null($glb_connection)){
            try {
                $sql_config = "UPDATE t_config
                           SET config_value = :config_value
                           WHERE config_key = :config_key;";
                $query_config = $glb_connection->prepare($sql_config);
                
                foreach($data as $data_tmp) {
                    // +++ bind parameters
                    $query_config->bindValue(':config_value', $data_tmp['config_value']);
                    $query_config->bindValue(':config_key', $data_tmp['config_key']);
                    // --- bind parameters
                    $query_config->execute();
                }
                
                $return = 'ok';
                
            } catch(PDOException $e){
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }

        return $return;
    }
}

// instantiate the config class
$cls_config = new config();
$cfg_value = $cls_config->cfg_value;

?>