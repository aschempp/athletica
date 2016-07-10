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
require_once(ROOT_PATH.'lib/cls.result_high.php');

$athletes = getAthleteDetails(0, true, "ath_rank, ath_res DESC","","",true);
?>
<table>
    <colgroup>
        <col width="50">
        <col width="50">
        <col width="200">
        <col width="50">
        <col width="250">
        <col>
    </colgroup>
<?php

foreach($athletes as $athlete) {
    $out = checkOut($athlete['ath_id']);
    if($out == 1 || $athlete['ath_res'] < 0){
        $class = "resultlist_inactive";
    } else {
        $class = "resultlist";
    }
    ?>
    <tr class="<?=$class?>">
        <td><?=$athlete['ath_rank_out']?></td>
        <td><?=$athlete['ath_bib']?></td>
        <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
        <td><?=substr($athlete['ath_yob'], -2)?></td>
        <td><?=$athlete['ath_club']?></td>
        <td><?=formatResultOutput($athlete['ath_res'])?></td>
    </tr>
    <?php    
}
?>
</table>