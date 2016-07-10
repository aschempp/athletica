<?php
if(!defined('GLOBAL_PATH')) {
    define('GLOBAL_PATH', '../../../../');
}
if(!defined('ROOT_PATH')) {
    define('ROOT_PATH', '../../../');
}
if(!defined('CURRENT_CATEGORY')) {
    define('CURRENT_CATEGORY', 'athletica_tech');
}
if(!defined('CURRENT_PAGE')) {
    define('CURRENT_PAGE', 'results');
}

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_tech.php');

$currentID = $_GET['current'];
$maxRang = checkFinal(CFG_CURRENT_EVENT);
if($maxRang > 0) {
    $all = true;
} else {
    $all = false;
}
$athletes = getAthleteDetails(0, false, 'ath_pos', $maxRang,false,$all);
?>
<table>
    <colgroup>
        <col width="50">
        <col width="200">
        <col width="50">
        <col>
    </colgroup>
<?php
foreach($athletes as $athlete) {
    if($athlete['xSerienstart'] == $currentID){
        $class = "startlist_curr";
    } else{
        $class = "startlist";
    }
    ?>
    <tr class="<?=$class?>">
        <td><?=$athlete['ath_bib']?></td>
        <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
        <td><?=substr($athlete['ath_yob'], -2)?></td>
        <td><?=$athlete['ath_club']?></td>
    </tr>
    <?php    
}
?>
</table>