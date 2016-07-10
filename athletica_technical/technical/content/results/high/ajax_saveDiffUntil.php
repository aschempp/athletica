<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$height = $_POST['height'];
$diff = $_POST['diff'];

$height = formatResultDB($height);
if($height > 0){
    $height_out = updateHighSettings(CFG_CURRENT_EVENT, 'diff_'.$diff.'_until', $height);
}

echo $height_out;


?>