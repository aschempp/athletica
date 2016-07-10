<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$height_id = $_POST['height_id'];

deleteHeight($height_id);
createHeightTable(CFG_CURRENT_EVENT);
createResultTable(CFG_CURRENT_EVENT);
?>