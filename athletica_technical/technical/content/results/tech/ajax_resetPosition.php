<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_tech.php');

$return = "error";

$event = $_POST['event'];

try{      
    resetPosition($event);
      
    
    $return = "ok";
    
}catch(PDOException $e){
    trigger_error($e->getMessage());
    $return = 'error';
}
    
echo $return;


//---------------------------------------------------------------------

?>
