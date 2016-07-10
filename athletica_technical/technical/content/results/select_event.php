<form name="frm_select_event" id="frm_select_event" action="index.php" method="post">
<input type="hidden" name="frm_action" id="frm_action" value="select_event" />
<input type="hidden" name="xRunde" id="xRunde" />
<?php
    if(CFG_CURRENT_EVENT==0) {    
        ?>
        <table>
            <colgroup>
                <col width="150">
                <col>
                <col width="20">
                <col>
            </colgroup>
            <?php
            $colspan = 4;
            ?>
            <tr>
                <td><b><label for="xMeeting"><?=$lg['MEETING']?>:</label></b></td>
                <td>
                    <select name="xMeeting" id="xMeeting">
                        <?php
                        if(count($meetings)==0) {
                            ?>
                            <option value="">-- <?=$lg['MEETINGS_EMPTY']?> --</option>
                            <?php
                        } else {
                            ?>
                            <option value="">-- <?=$lg['CHOOSE']?> --</option>
                            <?php
                            foreach($meetings as $meeting){
                                $sel = ($meeting['xMeeting']==CFG_CURRENT_MEETING) ? ' selected="selected"' : '';
                                $meeting_date = ($meeting['meeting_date_from'] != $meeting['meeting_date_to']) ? datetime_format('d.m.Y', $meeting['meeting_date_from'])." - ".datetime_format('d.m.Y', $meeting['meeting_date_to']) : datetime_format('d.m.Y', $meeting['meeting_date_from']);
                                ?>
                                <option value="<?=$meeting['xMeeting']?>"<?=$sel?>><?=$meeting['meeting_name']?> - <?=$meeting['meeting_ort']?> (<?=$meeting_date?>)</option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
                <td></td>
                <td valign="middle"><button type="button" name="refreshEventList" id="refreshEventList"><?=$lg['REFRESH']?></button></td>
            </tr>
            <tr>
                <td colspan="<?=$colspan?>"></td>
            </tr>
            <?php
            if(CFG_CURRENT_MEETING!=0) {    
            ?>
                <tr>
                    <td><b><label for="xSerie"><?=$lg['EVENT']?>:</label></b></td>
                    <td>                
                        <select name="xSerie" id="xSerie">
                            <?php
                            if(count($events)==0) {
                                ?>
                                <option value="">-- <?=$lg['EVENTS_EMPTY']?> --</option>
                                <?php
                            } else {
                                ?>
                                <option value="">-- <?=$lg['CHOOSE']?> --</option>
                                <?php
                                foreach($events as $event){
                                    $merged = getMergedRounds($event['xRunde']);
                                    if($merged != "") {
                                        $categories = getMergedCategories($merged);
                                        $cat_string = "";
                                        $tmp = "";
                                        foreach($categories as $cat) {
                                            $cat_string .= $tmp.$cat['cat_name'];
                                            $tmp = "/";
                                        }
                                    } else {
                                        $cat_string = $event['cat_name'];
                                    }

                                    $sel = ($event['xSerie']==CFG_CURRENT_EVENT) ? ' selected="selected"' : '';
                                    $round_bez = ($event['round_type'] != '0') ? $event['round_name']." ".$event['serie_bez'] : $event['serie_bez'];
                                    ?>
                                    <option value="<?=$event['xSerie']?>"<?=$sel?>><?=$event['disc_name']?> - <?=$cat_string?> - <?=$round_bez?> (<?=$event['round_start_date']?> - <?=$event['round_start_time']?>)</option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td></td>
                </tr>
        </table>
        <?php
        } else {
            ?>
            <input type="hidden" name="xSerie" id="xSerie" value="0">
            <?php
        }
        ?>
        
        
        <?php   
    }
?>
</form>

