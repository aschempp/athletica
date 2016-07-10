<?php
define('GLOBAL_PATH', '../');
define('ROOT_PATH', '');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'mode');

require_once(ROOT_PATH.'lib/inc.init.php');

if(isset($_GET['mode'])){
    $_SESSION[CFG_SESSION]['mode'] = $_GET['mode'];
}
?>