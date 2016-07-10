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

$settings = getTechSettings(CFG_CURRENT_EVENT);
?>
<table class="event_settings">
    <colgroup>
        <col width="200">
        <col width="">
    </colgroup>
    <?php
    $colspan = 2;
    ?>
    <tr class="event_settings">
        <td><?=$lg['ATTEMPTS']?>:</td>
        <td><?=$settings['round_attempts']?></td>
    </tr>
    <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
    </tr>
    <tr class="event_settings">
        <td><?=$lg['FINAL']?>:</td>
        <td><?=($settings['round_final'] == 1) ? $lg['YES'] : $lg['NO']?></td>
    </tr>
    <?php
    if($settings['round_final'] == 1){
        ?>
        <tr>
            <td height="5" colspan="<?=$colspan?>"></td>
        </tr>
        <tr class="event_settings">
            <td><?=$lg['FINAL_AFTER']?>:</td>
            <td><?=$settings['round_final_after']?></td>
        </tr>
        <tr>
            <td height="5" colspan="<?=$colspan?>"></td>
        </tr>
        <tr class="event_settings">
            <td><?=$lg['FINAL_ATHLETES']?>:</td>
            <td><?=$settings['round_finalists']?></td>
        </tr>
        <?php
    }
    $drop = explode(",",$settings['round_drop']);
    $i = 0;
    foreach($drop as $tmp) {
        if($tmp == 0){
            array_splice($drop,$i,1); 
        }else {
            $i++;
        }
    }

    if(count($drop) > 0) {
        $drops = "";
        $tmp = 1;
        foreach($drop as $drop_tmp) {
            $drops.=$drop_tmp;
            if($tmp == count($drop)-1) {
                $drops.=" ".$lg['AND']." ";    
            } elseif($tmp < count($drop)) {
                $drops.=", ";
            }
            $tmp++;
        }
        if($settings['round_drop'] == 1) {
            $drops = str_replace("%n%", $drops, $lg['AFTER_ATTEMPT']);
        } else {
            $drops = str_replace("%n%", $drops, $lg['AFTER_ATTEMPTS']);
        }
    } else {
        $drops = $lg['NO'];
    }
    ?>
    <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
    </tr>
    <tr class="event_settings">
        <td><?=$lg['DROP_POSITION']?>:</td>
        <td><?=$drops?></td>
    </tr>

</table>