<?php
define('GLOBAL_PATH', '../');
define('ROOT_PATH', '');
define('CURRENT_CATEGORY', 'start');
define('CURRENT_PAGE', 'db');

require_once(ROOT_PATH.'lib/inc.init.php');

include(ROOT_PATH.'header.php');

?>
<h1><?=$lg['ERROR_DB']?></h1>

<?php
box($lg['ERROR_DB_TEXT'], $lg['ERROR_DB_TITLE'], 'error', 0, 20);

if(CFG_DEBUG){
	$status_db = (!is_null($cls_database->error)) ? $cls_database->error : '<b style="color: #008000;">Connection established</b>';
	$class_db = (!is_null($cls_database->error)) ? 'error' : 'ok';
	$status_server = (!is_null($cls_database_server->error_server)) ? $cls_database_server->error_server : '<b style="color: #008000;">Connection established</b>';
	$class_server = (!is_null($cls_database_server->error_server)) ? 'error' : 'ok';
	?>
	<b>Local Database:</b><br/>
	<b class="status_<?=$class_db?>"><?=$status_db?></b><br/><br/>

	<b>Server:</b><br/>
	<b class="status_<?=$class_server?>"><?=$status_server?></b><br/><br/><br/>
	<?php
}
?>

<input type="button" name="btn_reload" id="btn_reload" class="button" value="Reload" onclick="document.location.href = '<?=ROOT_PATH?>index.php';"/>
<?php

include(ROOT_PATH.'footer.php');
?>