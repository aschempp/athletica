<?php
define('GLOBAL_PATH', '../../../');
define('ROOT_PATH', '../../');
define('CURRENT_CATEGORY', 'settings');
define('CURRENT_PAGE', 'start');

require_once(ROOT_PATH.'lib/inc.init.php');

include(ROOT_PATH.'header.php');

?>
<h1 class="content_title"><?=$lg['SETTINGS']?></h1>

<ul>
<?php include ROOT_PATH.'content/navigation/settings_sub.php'?>
</ul>
<br>
<br>



<?php
include(ROOT_PATH.'footer.php');
?>
