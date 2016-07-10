<table>
    <colgroup>
        <col width="100">
        <col width="10">
        <col width="100">
        <col width="10">
        <col>
    </colgroup>
    <?php
    $colspan = 5;
    ?>
    <tr>
        <td><button type="button" name="showStartlist" id="showStartlist"><?=$lg['STARTLIST']?></button></td>
        <td></td>
        <td><button type="button" name="showResultlist" id="showResultlist"><?=$lg['RESULTLIST']?></button></td>
        <td></td>
        <td><button type="button" name="showEditForm" id="showEditForm"><?=$lg['RESULTS_CHANGE']?></button></td>
        <!--
        <td></td>
        <td><button type="button" name="resetPosition" id="resetPosition"><?=$lg['POSITION_RESET']?></button></td>
        -->
    </tr>
</table>
<?php
$meeting_date = ($meeting['meeting_date_from'] != $meeting['meeting_date_to']) ? $meeting['meeting_date_from']." - ".$meeting['meeting_date_from'] : datetime_format('d.m.Y', $meeting['meeting_date_from']);
?>
<hr>
<b><?=$meeting['meeting_name']?></b>
<table>
    <colgroup>
        <col width="150">
        <col width="150">
        <col width="200">
        <col width="150">
        <col width="10">
        <col width="200">
        <col width="10">
        <col width="150">
    </colgroup>
    <?php
    $colspan = 8;
    ?>
    
    <tr class="event_header">
        <td><b><?=$events['disc_name']?></b></td>
        <td><b><?=$events['cat_name']?></b></td>
        <td><?=$events['round_start_time']?>&nbsp;(<?=$lg['TIME_CALL']?>&nbsp;<?=$events['round_call_time']?>)</td>
        <td rowspan="2">
            <button type="button" name="showSettings" id="showSettings"><?=$lg['SETTINGS']?></button>
        </td>
        <td></td>
        <td rowspan="2">
            <button type="button" name="quitEvent" id="quitEvent" xRunde="<?=$events['xRunde']?>"><?=$lg['QUIT']?></button>
        </td>
        <td></td>
        <td rowspan="2">
            <button type="button" name="refreshEvent" id="refreshEvent"><?=$lg['REFRESH']?></button>
        </td>
    </tr>
    <tr class="event_header">
        <td><?=($events['round_name']!='') ? $events['round_name']." ".$events['serie_bez'] : ''?></td>
    </tr>
    
</table>