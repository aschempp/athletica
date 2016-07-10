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
        
        $('input[name="startHeight"]').change(function(){
            var athlete = $(this).attr('xSerienstart');    
            var height = $(this).val();    
            
            $.ajax({
                url: '<?=$type?>/ajax_saveStartHeight.php',
                type: 'POST',
                data: 'athlete='+athlete+'&height='+height,
                success: function(data) {
                    if(data=='error') {
                       alert('<?=$lg['ERROR_DB']?>');
                    } else if(data=='height'){
                       alert('<?=$lg['ERROR_INPUT_STARTHEIGHT']?>'); 
                       $('input[xSerienstart='+athlete+']').val('')
                    } else {
                        $.ajax({
                            url: '<?=$type?>/ajax_formatHeight.php',
                            type: 'POST',
                            data: 'height='+height,
                            success: function(data) {
                                if(data=='error') {
                                   alert('<?=$lg['ERROR_INPUT_VALUE']?>');
                                } else {
                                    $('input[xSerienstart='+athlete+']').val(data); 
                                }
                            }
                        });     
                    }
                }
            }); 
        });
        $('img[name="delete_result"]').click(function(){
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['DELETING_RESULT'])?>');
            var ath_id = $(this).attr('xSerienstart');    
            var ath_start = $(this).attr('start');    
            var ath_res = $(this).attr('result');    
            
            $.ajax({
                url: '<?=$type?>/ajax_deleteResult.php',
                type: 'POST',
                data: 'ath_id='+ath_id+'&ath_start='+ath_start+'&ath_res='+ath_res,
                success: function(data) {
                    if(data=='error') {
                       alert('<?=$lg['ERROR_DB']?>');
                    } else {
                        var url = 'high/dialog_startheight.php';
                        $('#dialog_startheight').load(url, function(response, status, req){ 
                            if(status == 'success'){
                                $('#dialog_wait').dialog('close');
                            }
                        });
                    }
                }
            });
            
        });
        
        $('button[name="setDNS"]').click(function(){
           submitResult($(this).attr('xSerienstart'), '<?=$glb_results_skip['DNS']?>'); 
        });
        $('button[name="setDSQ"]').click(function(){
           submitResult($(this).attr('xSerienstart'), '<?=$glb_results_skip['DSQ']?>'); 
        });
        
        function submitResult(athlete, result){    
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_RESULT'])?>');
                
            var ath_res = '<?=$cfgResultsWindDefault?>';
            var res_id = 0;
            var round = '<?=$events['xRunde']?>';
            var event = '<?=$events['xWettkampf']?>';
            
            data = {
                ath_res: ath_res,
                ath_id: athlete,            
                res_id: res_id,  
                round: round,
                event: event,          
                height: result,          
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
                        var url = 'high/dialog_startheight.php';
                        $('#dialog_startheight').load(url, function(response, status, req){ 
                            if(status == 'success'){
                                $('#dialog_wait').dialog('close');
                            }
                        });
                    }
                }
            });
        }
    });
</script>

<?php
$athletes = getAthleteDetails(0, true, 'ath_pos');
?>
<table>
    <colgroup>
        <col width="50">
        <col width="200">
        <col width="50">
        <col width="250">
        <col>
        <col width="10">
        <col>
        <col width="5">
        <col>
    </colgroup>
<?php
$i = 1;
foreach($athletes as $athlete) {
    $class = "startlist";
    ?>
    <tr class="<?=$class?>">
        <td><?=$athlete['ath_bib']?></td>
        <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
        <td><?=substr($athlete['ath_yob'], -2)?></td>
        <td><?=$athlete['ath_club']?></td>
        <td>
            <?php
            if(in_array($athlete['ath_res'], $glb_results_skip)){
                ?>
                <?=$lg['RESULT_INVALID_'.$glb_invalid_attempt[$athlete['ath_res']].'_RANKING']?>
                <img src="<?=ROOT_PATH?>img/icon_delete.png" name="delete_result" id="delete_result" xSerienstart="<?=$athlete['ath_id']?>" start="<?=$athlete['ath_start']?>" result="<?=$athlete['ath_res']?>" width="16" height="16" alt="icon_delete" style="cursor: pointer;">
                <?php    
            } else{
                ?>
                <input type="number" name="startHeight" id="startHeight" class="startHeight" xSerienstart="<?=$athlete['ath_id']?>" value="<?=formatResultOutput($athlete['ath_start'])?>" tabindex="<?=$i?>">
                <?php             
            }
            ?>   
        </td>
        <td></td>
        <td>
            <?php
            if(!in_array($athlete['ath_res'], $glb_results_skip)){
                ?>
                <button type="button" name="setDNS" id="setDNS" xSerienstart="<?=$athlete['ath_id']?>" class="button_small"><?=$lg['RESULT_INVALID_DNS_BUTTON']?></button>
                <?php
            }
            ?>
        </td>
        <td></td>
        <td>
            <?php
            if(!in_array($athlete['ath_res'], $glb_results_skip)){
                ?>
                <button type="button" name="setDSQ" id="setDSQ" xSerienstart="<?=$athlete['ath_id']?>" class="button_small"><?=$lg['RESULT_INVALID_DSQ_BUTTON']?></button>
                <?php
            }
            ?>
        </td>
    </tr>
    <?php   
    $i++;
}
?>
</table>