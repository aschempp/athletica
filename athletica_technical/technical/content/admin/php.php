<?php
define('GLOBAL_PATH', '../../../');
define('ROOT_PATH', '../../');
define('CURRENT_CATEGORY', 'admin');
define('CURRENT_PAGE', 'php');

include(ROOT_PATH.'lib/inc.init.php');

include(ROOT_PATH.'header.php');

?>
<h1>PHP configuration</h1>

<?php
$title = 'Valid PHP configuration';
$message = 'The current PHP configuration is valid.';
$box_type = 'success';

if(!$cls_php_ini->valid){
	$title = 'Invalid PHP configuration';
	$message = 'The current PHP configuration is not valid, the application cannot run on this system.';
	$box_type = 'error';
}

box($message, $title, $box_type, 0, 25);

foreach($cls_php_ini->result as $type => $settings){
	if($type=='settings'){
		?>
		<h2>Settings</h2>

		<table width="100%" border="0" cellpadding="5" cellspacing="2" id="list_table">
			<colgroup>
				<col width="200"/>
				<col width="200"/>
				<col width="200"/>
				<col/>
			</colgroup>
			<tr>
				<th style="vertical-align: top;"><b>Setting</b></th>
				<th style="vertical-align: top;"><b>Current value</b></th>
				<th style="vertical-align: top;"><b>Recommended value</b></th>
				<th style="vertical-align: top;"><b>Description</b></th>
			</tr>
		<?php
	} else {
		?>
		<h2>Extensions</h2>

		<table width="100%" border="0" cellpadding="5" cellspacing="2" id="list_table">
			<colgroup>
				<col width="200"/>
				<col/>
			</colgroup>
			<tr>
				<th style="vertical-align: top;"><b>Extension</b></th>
				<th style="vertical-align: top;"><b>Description</b></th>
			</tr>
		<?php
	}

	foreach($settings as $setting){
		$search = array(
			'%PHP_VERSION%',
			'%PHP_VERSION_RECOMMENDED%',
		);
		$replace = array(
			PHP_VERSION,
			((isset($setting['recommended'])) ? $setting['recommended'] : ''),
		);

		if($type=='settings'){
			?>
			<tr id="list_<?=$setting['message_type']?>">
				<td valign="top"><b><?=$setting['name']?></b></td>
				<td valign="top"><?=$setting['value']?></td>
				<td valign="top"><?=$setting['recommended']?></td>
				<td valign="top"><?=str_replace($search, $replace, $setting['info'])?></td>
			</tr>
			<?php
		} else {
			?>
			<tr id="list_<?=$setting['message_type']?>">
				<td valign="top"><b><?=$setting['name']?></b></td>
				<td valign="top"><?=str_replace($search, $replace, $setting['info'])?></td>
			</tr>
			<?php
		}
	}

	?>
	</table><br/><br/>
	<?php
}

include(ROOT_PATH.'footer.php');
?>