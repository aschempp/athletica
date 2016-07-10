<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$athlete = $_POST['athlete'];
$height = $_POST['height'];

if($height != "") {
    $height = formatResultDB($height);
} else{
    $height = 0;
}

if(checkStartHeight($height, CFG_CURRENT_EVENT)){
    $height_out = saveStartHeight($athlete, $height);
    createHeightTable(CFG_CURRENT_EVENT);
    updateResultTable($athlete, CFG_CURRENT_EVENT, $height);
} else{
    $height_out = 'height';
}

echo $height_out;


?>