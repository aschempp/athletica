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

$wind = $events['event_wind'];
$result_id = $_GET['result'];
$athlete_id = $_GET['athlete'];
$attempt = $_GET['attempt'];
$result = getResult($result_id);

$athlete = getAthleteDetails($athlete_id, false, 'ath_pos', 0, true);

?>
<script type="text/javascript">
    $(document).ready(function(){
        $('button').button();
        
         $('#result_edit_result, #result_edit_wind').keyup(function(){
            validate();   
        });
        
        $('#result_edit_result').blur(function(){
            var res = $(this).val();
            
            if (res) {
                $.ajax({
                    url: '<?=$type?>/ajax_fillWind.php',
                    type: 'POST',
                    data: 'res='+res,
                    success: function(data) { 
                        if(data!='') {
                           $('#result_edit_wind').val(data); 
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
                            $('#result_edit_result').val(data); 
                            validate();
                        }
                    }
                });  
            }
            validate();
        });
        
        $('#result_edit_wind').blur(function(){
            var wind = $(this).val();
            
            if (wind) {
                $.ajax({
                    url: '<?=$type?>/ajax_formatWind.php',
                    type: 'POST',
                    data: 'wind='+wind,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_INPUT_VALUE']?>');
                        } else {
                            $('#result_edit_wind').val(data); 
                        }
                    }
                });  
            }
            validate();
        });
        
        $('#btn_editResult').click(function(){
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_RESULT'])?>');
            
            var ath_res =  $('#result_edit_result').val();
            if($('#result_edit_wind').val()) {
                var ath_wind =  $('#result_edit_wind').val();
            } else {
                var ath_wind = '<?=$cfgResultsWindDefault?>';
            }
            
            var ath_id = $('#xSerienstart').val();
            var res_id = $('#xResultat').val();
            var round= $('#round').val();
            var event = $('#event').val();
            var attempt = $('#attempt').val();
            
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
                        $('#dialog_edit_input').dialog('close');
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
        
        function validate() {
            $('#btn_editResult').hide();
            if($('#result_edit_result').val()) {
                if($('#wind').val()==1 && $('#result_edit_wind').val() || $('#wind').val()==0) {
                    $('#btn_editResult').show(); 
                }
            }
        }
        validate();
    });
</script>
<input type="hidden" name="xResultat" id="xResultat" value="<?=$result_id?>">
<input type="hidden" name="xSerienstart" id="xSerienstart" value="<?=$athlete_id?>">
<input type="hidden" name="wind" id="wind" value="<?=$wind?>">
<input type="hidden" name="attempt" id="attempt" value="<?=$attempt?>">
<table>
    <colgroup>
        <col width="50">
        <col width="250">
        <col>
    </colgroup>
    <tr>
        <td><b><?=$athlete['ath_bib']?></b></td>
        <td><b><?=$athlete['ath_name']?> <?=$athlete['ath_firstname']?></b></td>
        <td><?=$attempt.". ".$lg['ATTEMPT']?></td>
    </tr>
    <tr>
        <td height="20px"></td>
    </tr>
</table>
<table>
    <tr>
        <td class="result"><input type="text" name="result_edit_result" id="result_edit_result" class="result" autocomplete="off" tabindex="101" value="<?=formatResultInput($result['result'])?>">
        <td class="wind">
            <?php
            if($wind == 1){
                ?>
                <input type="text" name="result_edit_wind" id="result_edit_wind" class="wind" autocomplete="off" tabindex="102" value="<?=formatWindInput($result['wind'])?>">
                <?php    
            }
            ?>
        </td>
        <td><button type="button" name="btn_editResult" id="btn_editResult" tabindex="103"><?=$lg['OK']?></button></td>
    </tr>
</table>