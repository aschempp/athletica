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
        <td><?=$events['round_attempts']?></td>
    </tr>
    <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
    </tr>
    <tr class="event_settings">
        <td><?=$lg['FINAL']?>:</td>
        <td><?=($events['round_final'] == 1) ? $lg['YES'] : $lg['NO']?></td>
    </tr>
    <?php
    if($events['round_final'] == 1){
        ?>
        <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
        </tr>
        <tr class="event_settings">
            <td><?=$lg['FINAL_AFTER']?>:</td>
            <td><?=$events['round_final_after']?></td>
        </tr>
        <tr>
            <td height="5" colspan="<?=$colspan?>"></td>
        </tr>
        <tr class="event_settings">
            <td><?=$lg['FINAL_ATHLETES']?>:</td>
            <td><?=$events['round_finalists']?></td>
        </tr>
        <?php
    }
    if(isset($events['round_drop'])) {
        $drop = explode(",",$cls_event->drop);
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
        if($cls_event->drop == 1) {
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