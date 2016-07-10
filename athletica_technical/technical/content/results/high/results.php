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

$events = getEvents(CFG_CURRENT_MEETING,CFG_CURRENT_EVENT);
$type = $glb_types_results[$events['disc_type']];
?>
<script type="text/javascript">
    $(document).ready(function(){
        $('button').button();
        
        $('#showEditForm').button({ icons: {primary:'ui-icon-gear'} });
                
        $('#setPassed, #setFailed, #setWaived').click(function(){
           submitResult($(this).attr('result'));
        });

        
        $('#setDNS').click(function(){
            submitResult('<?=$glb_invalid_attempt_button['DNS']?>');
        });
        
        $('#setDSQ').click(function(){
           submitResult('<?=$glb_invalid_attempt_button['DSQ']?>');
        });
        
        $('#res_remark').change(function(){
            var remark = $(this).val();
            var ath_id = $('#currentID').val();

            $.ajax({
                url: '<?=$type?>/ajax_saveRemark.php',
                type: 'POST',
                data: 'remark='+remark+'&ath_id='+ath_id,
                success: function(data) {
                    if(data=='error') {
                       alert('<?=$lg['ERROR_INPUT_VALUE']?>');
                    }
                }
            });  
        });
        
        function submitResult(result){    
            $(document).unbind('keyup');
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_RESULT'])?>');
            
            if(result < 0) {
                var height = result;    
                var ath_res = '<?=$cfgResultsWindDefault?>';
            } else{
                var height = $('#currentHeight').val();
                var ath_res = result;
            }
            
            var ath_id = $('#currentID').val();
            var res_id = ($('#currentResID').val() > 0) ? $('#currentResID').val() : 0;
            var round= $('#round').val();
            var event = $('#event').val();
            
            
            data = {
                ath_res: ath_res,
                ath_id: ath_id,            
                res_id: res_id,  
                round: round,
                event: event,          
                height: height,          
            };
            
            $.ajax({
                url: '<?=$type?>/ajax_saveResult.php',
                type: 'POST',
                data: data,
                success: function(data) {
                    if(data=='result') {
                        $('#dialog_wait').dialog('close');
                        alert('<?=$lg['ERROR_INPUT']?>');
                    } else if(data=='db') {
                        $('#dialog_wait').dialog('close');
                        alert('<?=$lg['ERROR_DB']?>')
                    } else {
                        var url = '<?=$type?>/results.php';
                        $('#div_results').load(url, function(response, status, req){
                            if(status=='success'){
                                $('#dialog_wait').dialog('close');
                                //location.reload();
                            }
                        });
                    }
                }
            });
        }
        
        $(document).keyup(function(data){
            var code = data.keyCode;

            switch (code) {
                case 48:
                    $('#setPassed').trigger('click');
                    break;
                case 79:
                    $('#setPassed').trigger('click');
                    break;
                case 88:
                    $('#setFailed').trigger('click');
                    break;
                case 109:
                    $('#setWaived').trigger('click');
                    break;
            }
        });
    });
</script>

<?php

if(CFG_CURRENT_EVENT > 0) {
    $checkStartHeights = checkStartheightsComplete(CFG_CURRENT_EVENT);
    $athletes = getAthletes(CFG_CURRENT_EVENT, 0);
    if($checkStartHeights) {
        createHeightTable(CFG_CURRENT_EVENT);
        $currentAthlete = getCurrentAthlete(CFG_CURRENT_EVENT);
        $currentAthleteID = $currentAthlete['ath_id'];
        $currentHeight = $currentAthlete['curr_height'];
        $currentResultData = getCurrentResult($currentAthleteID, $currentHeight);
        $currentResult = $currentResultData['ath_res'];
        $currentResultID = $currentResultData['res_id'];
        if($currentAthleteID > 0) {
            $currentAttempt = $currentAthlete['curr_miss'] + 1;
            $currentAthleteData = getAthleteDetails($currentAthleteID, false, 'ath_pos', '0', true);
            $nextAthlete = getNextAthlete($athletes, $currentAthlete, CFG_CURRENT_EVENT);
            $nextAthleteID = $nextAthlete['ath_id'];
            $nextHeight = $nextAthlete['curr_height'];
            if($nextAthleteID == 0){
                $last = true;
                $nextAthleteID == getFirstAthlete($athletes);
            } else{
                $last = false;
            }
            $nextAthleteData = getAthleteDetails($nextAthleteID, false, 'ath_pos', '0', true);
            $nextAttempt = $nextAthlete['curr_miss'] + 1;
        }
    }
    $settings = getHighSettings(CFG_CURRENT_EVENT);
    
    ?>
    <input type="hidden" name="round" id="round" value="<?=$events['xRunde']?>">
    <input type="hidden" name="event" id="event" value="<?=$events['xWettkampf']?>">
    
    <?php
    if(!$checkStartHeights){
        ?>
        <?=$lg['HEIGHTS_START_DEFINE']?>
        <?php
    } elseif($currentAthleteID == 0) {
        ?>
        <?=$lg['EVENT_FINISHED']?>
        <?php    
    } else {  
        setActiveAthlete(CFG_CURRENT_EVENT, $currentAthleteID);
        ?>
        <input type="hidden" name="currentID" id="currentID" value="<?=$currentAthleteID?>">
        <input type="hidden" name="currentHeight" id="currentHeight" value="<?=$currentHeight?>">
        <input type="hidden" name="currentResID" id="currentResID" value="<?=$currentResultID?>">
        
        <table>
            <colgroup>
                <col width="100">
                <col width="600">
                <col width="20">
                <col>
                <col width="20">
                <col>
                <col>
            </colgroup>
            <?php
            $colspan = 7;
            ?>

            <tr>
                <td class="text_gross_high"><?=$currentAthleteData['ath_bib']?></td>
                <td class="text_gross_high"><?=$currentAthleteData['ath_name']?> <?=$currentAthleteData['ath_firstname']?></td>
                <td></td>
                <td><?=$currentAthleteData['ath_club']?></td>
                <td></td>
                <td>
                    <?php
                    if(checkFirstAttempt($currentAthleteID, CFG_CURRENT_EVENT)) {
                        ?>
                        <button type="button" name="setDNS" id="setDNS"><?=$lg['RESULT_INVALID_DNS_BUTTON']?></button>
                        <?php
                    }
                    ?>
                </td>
                <td><button type="button" name="setDSQ" id="setDSQ"><?=$lg['RESULT_INVALID_DSQ_BUTTON']?></button></td>
            </tr>
            <tr>
                <td height="5" colspan="<?=$colspan?>"></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3" class="results_past_high">
                    <?php
                    $ath_res = "";
                    $results = getAthleteResults($currentAthleteID);
                    if(count($results)) {
                        $x = 0;
                        foreach($results as $athres) {
                            if($x != 0) {
                                $ath_res .= " / ";
                            }
                            $ath_res .= formatResultOutput($athres['ath_res'], 'SHORT')." ";
                            if($athres['ath_res'] > 0){
                                $ath_res .= "(".$athres['ath_info'].")";
                            }
                            $x++;
                        }
                    }
                    ?>
                    <i><?=$ath_res?></i>
                </td>
            </tr>
            <tr>
                <td height="20" colspan="<?=$colspan?>"></td>
            </tr>
        </table>
        <table>
            <colgroup>
                <col>
                <col width="20">
                <col>
                <col width="20">
                <col>
                <col width="150">
                <col>
            </colgroup>
            <?php
            $colspan = 7;
            ?>
            <tr>
                <td class="result_title_high" colspan="4"><?=$currentAttempt.". ".$lg['ATTEMPT']?></td>
                <td class="result_title_high"><?=formatResultOutput($currentHeight).$lg['METER_SHORT']?></td>
                <td></td>
                <td class="remark_title"><?=$lg['REMARK']?>:</td>
            </tr>
            <tr>
                <td height="15px"></td>
            </tr>
            <tr>
                <td><button type="button" class="high_button_passed" name="setPassed" id="setPassed" result="<?=$currentResult.$glb_high_attempt_passed?>"><?=$lg['RESULT_HIGH_PASSED_BUTTON']?></button></td>
                <td></td>
                <td><button type="button" class="high_button_failed" name="setFailed" id="setFailed" result="<?=$currentResult.$glb_high_attempt_failed?>"><?=$lg['RESULT_HIGH_FAILED_BUTTON']?></button></td>
                <td></td>
                <td><button type="button" class="high_button_input" name="setWaived" id="setWaived" result="<?=$currentResult.$glb_high_attempt_waived?>"><?=$lg['RESULT_HIGH_WAIVED_BUTTON_SHORT']?></button></td>
                <td></td>
                <td class="remark"><input type="text" name="res_remark" id="res_remark" class="remark" autocomplete="off" tabindex="4" maxlength="5" value="<?=$currentAthleteData['ath_remark']?>"></td>
            </tr>
            <tr>
                <td height="20" colspan="<?=$colspan?>"></td>
            </tr>
        </table>
        <hr>
        <table>
            <colgroup>
                <col width="60">
                <col>
                <col width="20">
                <col>
                <col width="20">
                <col>
                <col width="20">
                <col>
            </colgroup>
            <?php
            $colspan = 5;
            ?>
            <tr>
                <td colspan="<?=$colspan?>" class="next_titel_high"><b><?=$lg['NEXT']?>:</b></td>
            </tr>
            <?php
            if($last) {
                $nextAttempt = $currentAttempt + 1;
                
                if($nextAttempt <= $events['round_attempts']){
                    ?>
                    <tr>
                        <td colspan="<?=$colspan?>"><?=$lg['ATTEMPT']." ".$nextAttempt?></td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <tr>
                        <td colspan="<?=$colspan?>"><?=$lg['EVENT_FINISHED']?></td>
                    </tr>
                    <?php
                }
            } else{
                ?>
                <tr>
                    <td class="next_details_high"><?=$nextAthleteData['ath_bib']?></td>
                    <td class="next_details_high"><?=$nextAthleteData['ath_name']?> <?=$nextAthleteData['ath_firstname']?></td>
                    <td></td>
                    <td class="next_details_small_high"><?=$nextAthleteData['ath_club']?></td>
                    <td></td>
                    <td class="next_details_high"><?=$nextAttempt.". ".$lg['ATTEMPT']?></td>
                    <td></td>
                    <td class="next_details_high"><?=formatResultOutput($nextAthlete['curr_height']).$lg['METER_SHORT']?></td>  
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
}
?>