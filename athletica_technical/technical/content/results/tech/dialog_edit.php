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
        $('div[name="result_edit"]').click(function(){
            var xResult = $(this).attr('xResultat');
            var athlete = $(this).attr('xSerienstart');
            var attempt = $(this).attr('attempt');
            var wind = $('#wind').val();
            
            show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_RESULT'])?>');
            
            var url = '<?=$type?>/dialog_edit_input.php?result='+xResult+'&athlete='+athlete+'&wind='+wind+'&attempt='+attempt;
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
    });
</script>

<?php
$wind = $events['event_wind'];
$maxRang = checkFinal(CFG_CURRENT_EVENT);
    if($maxRang > 0) {
        $all = true;
    } else {
        $all = false;
    }
$athletes = getAthleteDetails(0, false, 'ath_pos', 0, false, $all);
?>

<table>
    <colgroup>
        <col width="50">
        <col width="250">
        <col>
    </colgroup>
<?php
foreach($athletes as $athlete) {
    ?>
    <tr class="resultlist">
        <td><?=$athlete['ath_bib']?></td>
        <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
        <?php
        $i = 1;
        foreach(getAthleteResults($athlete['xSerienstart']) as $res){
            $ath_res = formatResultOutput($res['ath_res'], 'DB')." ";
            if($wind == 1 && $res['ath_res'] > 0){
                $ath_res .= "(".$res['ath_wind'].")";
            }
            ?>
            <td width="100" align="center">
                <div name="result_edit" id="result_edit" xResultat="<?=$res['xResultat']?>" xSerienstart="<?=$res['xSerienstart']?>" attempt="<?=$i?>" style="cursor: pointer;"><?=$ath_res?></div>
            </td>
            <?php
            $i++;
        }
        ?>
    </tr>
    <?php    
}
?>
</table>