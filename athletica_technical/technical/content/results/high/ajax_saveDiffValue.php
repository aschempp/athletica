<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$nr = $_POST['diff'];
$value = $_POST['value'];

if($value > 0){
    $out = updateHighSettings(CFG_CURRENT_EVENT, 'diff_'.$nr.'_value', $value);
}

echo $out;


?>