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

$events = getEvents(CFG_CURRENT_MEETING,CFG_CURRENT_EVENT);
$type = $glb_types_results[$events['disc_type']];
?>
<script type="text/javascript">
    $(document).ready(function(){
        $('button').button();
        
        $('#showEditForm').button({ icons: {primary:'ui-icon-gear'} });
        
        $('#resetPosition').click(function(){
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['RESETING_POSITIONS'])?>');
            
            var event = '<?=CFG_CURRENT_EVENT?>';
            $.ajax({
                url: '<?=$type?>/ajax_resetPosition.php',
                type: 'POST',
                data: 'event='+event,
                success: function(data) { 
                    if(data=='ok') {
                        location.reload();
                    } 
                }
            });   
        });
        
        $('#res_result, #res_wind').keyup(function(){
            validate();    
        });
        
        $('#res_result').blur(function(){
            var res = $(this).val();
            
            if ($(this).val()) {
                $.ajax({
                    url: '<?=$type?>/ajax_fillWind.php',
                    type: 'POST',
                    data: 'res='+res,
                    success: function(data) { 
                        if(data!='') {
                           $('#res_wind').val(data); 
                           validate();
                        } 
                    }
                }); 
                
                $.ajax({
                    url: '<?=$type?>/ajax_formatResult.php',
                    type: 'POST',
                    data: 'res='+res,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_INPUT_RESULT']?>');
                        } else {
                            $('#res_result').val(data); 
                            validate();
                        }
                    }
                });  
            }
            validate();
        });
        
        $('#res_wind').blur(function(){
            var wind = $(this).val();
            
            if ($(this).val()) {
                $.ajax({
                    url: '<?=$type?>/ajax_formatWind.php',
                    type: 'POST',
                    data: 'wind='+wind,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_INPUT_VALUE']?>');
                        } else {
                            $('#res_wind').val(data); 
                        }
                    }
                });  
            }
            validate();
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
        
        $('#submitResult').click(function(){       
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_RESULT'])?>');
            
            var ath_res =  $('#res_result').val();
            if($('#res_wind').val()) {
                var ath_wind =  $('#res_wind').val();
            } else {
                var ath_wind = '<?=$cfgResultsWindDefault?>';
            }
            
            var ath_id = $('#currentID').val();
            var res_id = 0;
            
            var round = $('#round').val();
            var event = $('#event').val();
            var attempt = $('#currentAttempt').val();
            
            data = {
                ath_res: ath_res,
                ath_wind: ath_wind,            
                ath_id: ath_id,            
                res_id: res_id,            
                round: round,            
                event: event,      
                attempt: attempt,      
            };

            $.ajax({
                url: '<?=$type?>/ajax_saveResult.php',
                type: 'POST',
                data: data,
                success: function(data) {
                    if(data=='result') {
                        alert('<?=$lg['ERROR_INPUT_RESULT']?>');
                    } else if(data=='db') {
                        alert('<?=$lg['ERROR_DB']?>')
                    } else {
                        var url = '<?=$type?>/results.php';
                        $('#div_results').load(url, function(response, status, req){
                            if(status=='success'){
                                $('#dialog_wait').dialog('close');                               
                                $('#res_result').focus(); 
                            }
                        });
                    }
                }
            });
            
        });
        
        $('#setNAA').click(function(){
           $('#res_result').val('<?=$glb_invalid_attempt_button['NAA']?>'); 
           $('#res_wind').val('<?=$cfgResultsWindDefault?>'); 
           $('#submitResult').show();
           $('#submitResult').trigger('click');
        });
        
        $('#setWAI').click(function(){
           $('#res_result').val('<?=$glb_invalid_attempt_button['WAI']?>'); 
           $('#res_wind').val('<?=$cfgResultsWindDefault?>'); 
           $('#submitResult').show();
           $('#submitResult').trigger('click');
        });
        
        $('#setDNS').click(function(){
           $('#res_result').val('<?=$glb_invalid_attempt_button['DNS']?>'); 
           $('#res_wind').val('<?=$cfgResultsWindDefault?>'); 
           $('#submitResult').show();
           $('#submitResult').trigger('click');
        });
        
        $('#setDSQ').click(function(){
           $('#res_result').val('<?=$glb_invalid_attempt_button['DSQ']?>'); 
           $('#res_wind').val('<?=$cfgResultsWindDefault?>'); 
           $('#submitResult').show();
           $('#submitResult').trigger('click');
        });
        
        function validate() {
            $('#submitResult').hide();
            if($('#res_result').val()) {
                if($('#wind').val()==1 && $('#res_wind').val() || $('#wind').val()==0) {
                    $('#submitResult').show(); 
                }
            }
        }
        
        $('#res_result').keyup(function(data){
            var code = data.keyCode;

            switch (code) {
                case 88:
                    $('#setNAA').trigger('click');
                    break;
                case 109:
                    $('#setWAI').trigger('click');
                    break;
            }
        });
        
        validate();
    });
</script>

<?php

if(CFG_CURRENT_EVENT > 0) {
    $maxRang = checkFinal(CFG_CURRENT_EVENT);
    //echo $maxRang;
    if($maxRang > 0) {
        $all = true;
    } else {
        $all = false;
    }
    
    if(checkDrop(CFG_CURRENT_EVENT, $maxRang)){
        dropPosition(CFG_CURRENT_EVENT, $maxRang);
    }

    $athletes = getNofResults(CFG_CURRENT_EVENT, 0, $maxRang, true, $all);

    $currentAthleteID = getCurrentAthlete($athletes);
    if($currentAthleteID > 0) {
        $currentAttempt = getNofResults(CFG_CURRENT_EVENT, $currentAthleteID, 0, false, $all) + 1;
        $currentAthleteData = getAthleteDetails($currentAthleteID, false, 'ath_pos', '0', true);
        $NextAthleteID = getNextAthlete($athletes, $athletes[$currentAthleteID]['Position']);
        if($NextAthleteID == 0){
            $last = true;
            $NextAthleteID == getFirstAthlete($athletes);
        } else{
            $last = false;
        }
        $NextAthleteData = getAthleteDetails($NextAthleteID, false, 'ath_pos', '0', true);
    }
    $settings = getTechSettings(CFG_CURRENT_EVENT);
        
    ?>
    <input type="hidden" name="round" id="round" value="<?=$events['xRunde']?>">
    <input type="hidden" name="event" id="event" value="<?=$events['xWettkampf']?>">
    <input type="hidden" name="wind" id="wind" value="<?=$events['event_wind']?>">
    
    <?php
    if($currentAthleteID == 0) {
        ?>
        <?=$lg['EVENT_FINISHED']?>
        <?php    
    }
    else if($currentAttempt > $settings['round_attempts']) {
        ?>
        <?=$lg['EVENT_FINISHED']?>
        <?php
    } else {  
        setActiveAthlete(CFG_CURRENT_EVENT, $currentAthleteID);
        ?>
        <input type="hidden" name="currentID" id="currentID" value="<?=$currentAthleteID?>">
        <input type="hidden" name="currentAttempt" id="currentAttempt" value="<?=$currentAttempt?>">
        
        <table>
            <colgroup>
                <col width="100">
                <col width="400">
                <col width="200">
                <col>
                <col>
                <col>
            </colgroup>
            <?php
            $colspan = 6;
            ?>

            <tr>
                <td class="text_gross_tech"><?=$currentAthleteData['ath_bib']?></td>
                <td class="text_gross_tech"><?=$currentAthleteData['ath_name']?> <?=$currentAthleteData['ath_firstname']?></td>
                <td><?=$currentAthleteData['ath_club']?></td>
                <td>
                    <?php
                    if(getNofResults(CFG_CURRENT_EVENT, $currentAthleteID, 0, false, $all) == 0) {
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
                <td colspan="<?=$colspan?>-1" class="results_past_tech">
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
                            if($events['event_wind'] == 1 && $athres['ath_res'] > 0){
                                $ath_res .= "(".$athres['ath_wind'].")";
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
                <col>
                <col width="40">
                <col>
                <col width="100">
                <col>
                
            </colgroup>
            <?php
            $colspan = 9;
            ?>
            <tr>
                <td class="result_title_tech"><?=$lg['PERFORMANCE']?>:</td>
                <td></td>
                <td class="wind_title">
                    <?php
                    if($events['event_wind'] == 1){
                        ?>
                        <?=$lg['WIND']?>:
                        <?php    
                    }
                    ?>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="remark_title"><?=$lg['REMARK']?>:</td>                
            </tr>
            <tr>
                
                <td class="result"><input type="number" name="res_result" id="res_result" class="result" autocomplete="off" tabindex="1">
                <script type="text/Javascript" language="JavaScript">
                <!--
                    document.getElementById("res_result").focus();
                -->
                </script>
                </td>
                <td></td>
                <td class="wind">
                    <?php
                    if($events['event_wind'] == 1){
                        ?>
                        <input type="text" name="res_wind" id="res_wind" class="wind" autocomplete="off" tabindex="2">
                        <?php    
                    }
                    ?>
                </td>
                <td></td>
                <td><button type="button" name="setNAA" id="setNAA" class="button_big"><?=$lg['RESULT_INVALID_NAA_BUTTON']?></button></td>
                <td><button type="button" name="setWAI" id="setWAI" class="button_big"><?=$lg['RESULT_INVALID_WAI_BUTTON']?></button></td>
                <td></td>
                <td><button type="button" name="submitResult" id="submitResult" tabindex="3" class="button_ok"><?=$lg['OK']?></button></td>
                <td></td>
                <td class="remark"><input type="text" name="res_remark" id="res_remark" class="remark" autocomplete="off" tabindex="4" maxlength="5" value="<?=$currentAthleteData['ath_remark']?>"></td>
            </tr>
        </table>
        <hr>
        <table>
            <colgroup>
                <col width="60">
                <col width="200">
                <col width="200">
            </colgroup>
            <?php
            $colspan = 3;
            ?>
            <tr>
                <td colspan="<?=$colspan?>"><b><?=$lg['NEXT']?>:</b></td>
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
                    <td><?=$NextAthleteData['ath_bib']?></td>
                    <td><?=$NextAthleteData['ath_name']?> <?=$NextAthleteData['ath_firstname']?></td>
                    <td><?=$NextAthleteData['ath_club']?></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
}
?>