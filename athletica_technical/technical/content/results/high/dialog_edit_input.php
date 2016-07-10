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

$result_id = $_GET['xResultat'];
$athlete_id = $_GET['athlete'];
$height = $_GET['height'];
$result = getResult($result_id);

$athlete = getAthleteDetails($athlete_id, false, 'ath_pos', 0, true);

?>
<script type="text/javascript">
    $(document).ready(function(){
        
        $('#updatePassed, #updateFailed, #updateWaived').each(function( index, element ) {
            if($(element).attr('result')==$('#result').val()) {
                $(element).addClass('ui-state-focus');
                $(element).addClass('ui-state-hover');
            } else {
                $(element).removeClass('ui-state-hover'); 
                $(element).removeClass('ui-state-focus'); 
            }
        });
        
        $('button').button();
        
        $('#updatePassed, #updateFailed, #updateWaived').click(function(){
           submitResult($(this).attr('result'));
        });
        
         $('#result_edit_result').keyup(function(){
        });
        
        $('#result_edit_result').blur(function(){
            var res = $(this).val();
            
            if (res) {
                $.ajax({
                    url: '<?=$type?>/ajax_formatResult.php',
                    type: 'POST',
                    data: 'res='+res,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_INPUT']?>');
                        } else {
                            $('#result_edit_result').val(data); 
                        }
                    }
                });  
            }
        });
        
        function submitResult(result){
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_RESULT'])?>');
                        
            if(result < 0) {
                var height = result;    
                var ath_res = '<?=$cfgResultsWindDefault?>';
            } else{
                var height = $('#height').val();
                var ath_res = result;
            }
            
            var ath_id = $('#xSerienstart').val();
            var res_id = $('#xResultat').val();
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
                        $('#dialog_edit_input').dialog('close');
                        var url = '<?=$type?>/results.php';
                        $('#div_results').load(url, function(response, status, req){
                            if(status=='success'){
                                $('#dialog_wait').dialog('close');
                            }
                        });
                    }
                }
            });
        }
    });
</script>
<input type="hidden" name="xResultat" id="xResultat" value="<?=$result_id?>">
<input type="hidden" name="xSerienstart" id="xSerienstart" value="<?=$athlete_id?>">
<input type="hidden" name="height" id="height" value="<?=$height?>">
<input type="hidden" name="result" id="result" value="<?=$result['info']?>">
<table>
    <colgroup>
        <col width="50">
        <col width="250">
        <col>
    </colgroup>
    <tr>
        <td><b><?=$athlete['ath_bib']?></b></td>
        <td><b><?=$athlete['ath_name']?> <?=$athlete['ath_firstname']?></b></td>
        <td><?=formatResultOutput($height).$lg['METER_SHORT']?></td>
    </tr>
    <tr>
        <td height="20"></td>
    </tr>
</table>

<table>
    
    <tr>
        <td><button type="button" class="high_button_input" name="updatePassed" id="updatePassed" result="O" tabindex="-1">O</button></td>
        <td></td>
        <td><button type="button" class="high_button_input" name="updatePassed" id="updatePassed" result="XO" tabindex="-1">XO</button></td>
        <td></td>
        <td><button type="button" class="high_button_input" name="updatePassed" id="updatePassed" result="XXO" tabindex="-1">XXO</button></td>
    </tr>
    <tr>
        <td><button type="button" class="high_button_input" name="updateFailed" id="updateFailed" result="X" tabindex="-1">X</button></td>
        <td></td>
        <td><button type="button" class="high_button_input" name="updateFailed" id="updateFailed" result="XX" tabindex="-1">XX</button></td>
        <td></td>
        <td><button type="button" class="high_button_input" name="updateFailed" id="updateFailed" result="XXX" tabindex="-1">XXX</button></td>
    </tr>
    <tr>
        <td><button type="button" class="high_button_input" name="updateWaived" id="updateWaived" result="-" tabindex="-1">-</button></td>
        <td></td>
        <td><button type="button" class="high_button_input" name="updateWaived" id="updateWaived" result="X-" tabindex="-1">X-</button></td>
        <td></td>
        <td><button type="button" class="high_button_input" name="updateWaived" id="updateWaived" result="XX-" tabindex="-1">XX-</button></td>
    </tr>
    
</table>