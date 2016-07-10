<?php
/**
* provides functions for the tempresult table handling 
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

function dropTempresults(){
    global $glb_connection;
      
    try {
        
        $sql_select = "SHOW TABLES FROM ".CFG_DB_DATABASE;
        
        $query_select = $glb_connection->prepare($sql_select);
        
        $query_select->execute();
        
        $tables = $query_select->fetchAll(PDO::FETCH_NUM);   
          
        foreach($tables as $table){  
            if (substr($table[0], 0, strlen(CFG_DB_TEMPRESULT_PREFIX)) == CFG_DB_TEMPRESULT_PREFIX) {
         
                $tablename = $table[0];
                $sql_drop = "DROP TABLE " . $tablename;
             
                $query_drop = $glb_connection->prepare($sql_drop);
            
                $query_drop->execute();
            }
         }

     } catch(PDOException $e) {
        trigger_error($e->getMessage());
     }
}

?>