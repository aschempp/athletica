<?php
// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
    header('Location: index.php');
    exit();
}
// +++ make sure that the file was not loaded directly

$class = (CURRENT_CATEGORY=='settings') ? 'current' : '';
?>
<li class="<?=$class?>">
    <a href="<?=ROOT_PATH?>content/settings/server.php" class="<?=$class?>"><?=$lg['CONFIGURATION']?></a>

    <ul>
        <?php
        include(ROOT_PATH.'content/navigation/settings_sub.php');
        ?>
    </ul>
</li>