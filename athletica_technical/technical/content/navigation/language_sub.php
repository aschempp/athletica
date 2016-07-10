<?php
global $glb_uri;
foreach($lg_languages as $nav_language_code => $nav_language){
	$class = (CFG_CURRENT_LANGUAGE==$nav_language_code) ? 'current' : '';
	
	$uri = $glb_uri.((strstr($glb_uri, '?')===FALSE) ? '?' : '&').'lang='.$nav_language_code;
	?>
	<li class="<?=$class?>">
		<a href="<?=$uri?>" class="<?=$class?>"><?=$nav_language['language_name']?></a>
	</li>
	<?php
}
?>