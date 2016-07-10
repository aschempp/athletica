<?php
// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
    header('Location: index.php');
    exit();
}
// +++ make sure that the file was not loaded directly
if(CFG_CURRENT_MODE == 'live') {
    $class = (CURRENT_CATEGORY=='settings' && CURRENT_PAGE=='server') ? 'current' : '';
    ?>
    <li class="<?=$class?>"><a href="<?=ROOT_PATH?>content/settings/server.php" class="<?=$class?>"><?=$lg['ATHLETICA_SERVER']?></a></li>
<?php
}
/*
$class = (CURRENT_CATEGORY=='settings' && CURRENT_PAGE=='settings') ? 'current' : '';
?>
<li class="<?=$class?>"><a href="<?=ROOT_PATH?>content/settings/settings.php" class="<?=$class?>"><?=$lg['SETTINGS']?></a></li>
*/