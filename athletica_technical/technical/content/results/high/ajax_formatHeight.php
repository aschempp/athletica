<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$height = $_POST['height'];

if($height == ""){
    $height_out = "";
} else {
    $height_out = formatHeightInput($height);
}

echo $height_out;


?>