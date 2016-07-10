<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_tech.php');

$return = "error";

$remark = $_POST['remark'];
$ath_id = $_POST['ath_id'];

try{      
    $sql_update = "UPDATE serienstart
                    SET Bemerkung = :remark
                    WHERE xSerienstart = :ath_id;";
    $query_update = $glb_connection_server->prepare($sql_update);
        
    // +++ bind parameters
    $query_update->bindValue(':remark', $remark);   
    $query_update->bindValue(':ath_id', $ath_id);    
    // --- bind parameters
    $query_update->execute();
      
    
    $return = "ok";
    
}catch(PDOException $e){
    trigger_error($e->getMessage());
    $return = 'error';
}
    
echo $return;


//---------------------------------------------------------------------

?>
