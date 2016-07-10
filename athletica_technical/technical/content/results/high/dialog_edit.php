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
        $('div[name="result_edit"]').click(function(){
            var xResultat = $(this).attr('xResultat');
            var athlete = $(this).attr('xSerienstart');
            var height = $(this).attr('height');
            
            $(document).unbind('keyup');
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_RESULT'])?>');
            
            var url = '<?=$type?>/dialog_edit_input.php?xResultat='+xResultat+'&athlete='+athlete+'&height='+height;
            $('#dialog_edit_input').load(url, function(response, status, req){
                if(status=='success'){
                    $('#dialog_wait').dialog('close');
                    $('#dialog_edit').dialog('close');

                    $('#dialog_edit_input').dialog({
                        width: 'auto',
                        height: 'auto',
                        zIndex: 2000,

                        position: 'center',

                        stack: true,    
                        resizable: false,
                        draggable: true,
                        modal: true,

                        dialogClass: 'dialog_content',

                        open: function(){
                            $('#dialog_edit_input').prev().children('a.ui-dialog-titlebar-close').hide();
                            $(this).dialog({position: "center"});
                        },

                        title: '<?=javascript_prepare($lg['RESULTS_CHANGE'])?>',

                        buttons: {
                            '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                                $(this).dialog('close');
                                $('#res_result').focus();
                            }
                        }
                    });
                }
            });
        });   
        
        $('img[name="delete_result"]').click(function(){
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['DELETING_RESULT'])?>');
            var ath_id = $(this).attr('athlete');    
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
                        var url = 'high/dialog_edit.php';
                        $('#dialog_edit').load(url, function(response, status, req){ 
                            if(status == 'success'){
                                $('#dialog_edit').dialog('close');
                                var url = '<?=$type?>/results.php';
                                $('#div_results').load(url, function(response, status, req){
                                    if(status=='success'){
                                        $('#dialog_wait').dialog('close');
                                    }
                                });      
                            }
                        });
                    }
                }
            });
            
        });
    });
</script>

<?php
$athletes = getAthleteDetails(0, true, 'ath_pos');
$heights = getHeights(CFG_CURRENT_EVENT);
$heightsPerRow = 10;
//$width = (count($heights) <= $heightsPerRow) ? count($heights)*60 + 400 : $heightsPerRow*60 + 400;
?>
<table>
    <tr>
        <td colspan="2" width="100%">
            <table>
                <colgroup>
                    <col width="400">
                    <col>
                </colgroup>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <table>
                        <?php
                        $class = "high_edit_odd";
                        $i = 0;
                        foreach($heights as $height) {
                            if($i == $heightsPerRow){
                                ?>
                                </tr>
                                <?php
                                $i = 0;
                                $class = ($class=="high_edit_odd") ? "high_edit_even" : "high_edit_odd";
                            }
                            if($i == 0){
                                ?>
                                <tr>
                                <?php
                            }
                            ?>
                            <td width="60" align="center" class="<?=$class?>"><b><?=formatResultOutput($height['height'])?></b></td>
                            <?php
                            $i++;
                        }
                        if($i == $heightsPerRow){
                            ?>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php
    foreach($athletes as $athlete) {
        ?>
        <tr>
            <td valign="top" colspan="2" width="100%">
                <table>
                    <colgroup>
                        <col width="400">
                        <col>
                    </colgroup>
                    <tr>
                        <td valign="top">
                            <table>
                                <colgroup>
                                    <col width="60">
                                    <col width="250">
                                    <col width="90">
                                    <col width="90">
                                </colgroup>
                                <tr class="resultlist">
                                    <td><?=$athlete['ath_bib']?></td>
                                    <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
                                    <td><?=formatResultOutput($athlete['ath_start'])?></td>
                                    <td>
                                        <?php
                                        if(in_array($athlete['ath_res'], $glb_results_skip)){
                                            ?>
                                            <?=$lg['RESULT_INVALID_'.$glb_invalid_attempt[$athlete['ath_res']].'_RANKING']?>
                                            <img src="<?=ROOT_PATH?>img/icon_delete.png" name="delete_result" id="delete_result" athlete="<?=$athlete['ath_id']?>" start="<?=$athlete['ath_start']?>" result="<?=$athlete['ath_res']?>" width="16" height="16" alt="icon_delete" style="cursor: pointer;">
                                            <?php    
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table>
                                <?php
                                $i = 0;
                                $class = "high_edit_odd";
                                foreach($heights as $height) {
                                    $ath_res = getAthleteResults($athlete['ath_id'], $height['height']);
                                    if($i == $heightsPerRow){
                                        ?>
                                        </tr>
                                        <?php
                                        $i = 0;
                                        $class = ($class=="high_edit_odd") ? "high_edit_even" : "high_edit_odd";
                                    }
                                    if($i == 0){
                                        ?>
                                        <tr>
                                        <?php
                                    }
                                    ?>
                                    <td width="60" align="center" class="<?=$class?>">
                                        <?php
                                        if($ath_res){
                                            ?>
                                            <div name="result_edit" id="result_edit" xSerienstart="<?=$athlete['ath_id']?>" xResultat="<?=$ath_res['res_id']?>" height="<?=$height['height']?>" style="cursor: pointer;"><?=$ath_res['ath_info']?></div>
                                            <?php
                                        } else{
                                            ?>
                                            &nbsp;
                                            <?php
                                        }
                                        ?>
                                    </td>
                                    <?php
                                    $i++;
                                }
                                if($i == $heightsPerRow){
                                    ?>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </td>                
                    </tr>
                </table>
            </td>
        </tr>
    <?php
    }
    ?>
</table>