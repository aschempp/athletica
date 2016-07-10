<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$serie = $_POST['serie'];
$height = $_POST['height'];

$height = formatResultDB($height);

$height_out = insertHeight($height, CFG_CURRENT_EVENT);
createHeightTable(CFG_CURRENT_EVENT);
createResultTable(CFG_CURRENT_EVENT);

echo $height_out;


?>