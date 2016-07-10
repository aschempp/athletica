<?php
// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
	header('Location: index.php');
	exit();
}
// +++ make sure that the file was not loaded directly

if(count($lg_languages)>1){
	$uri = $glb_uri.((strstr($glb_uri, '?')===FALSE) ? '?' : '&').'lang='.CFG_CURRENT_LANGUAGE;
	?>
	<li>
		<a href="<?=$uri?>"><?=$lg['LANGUAGE']?></a>

		<ul>
			<?php
			include(ROOT_PATH.'content/navigation/language_sub.php');
			?>
		</ul>
	</li>
	<?php
}
?>