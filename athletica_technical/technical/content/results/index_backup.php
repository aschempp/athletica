<?php
define('GLOBAL_PATH', '../../../');
define('ROOT_PATH', '../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');

if(isset($_POST['frm_action']) && $_POST['frm_action']=='select_event'){
    $xSerie = $_POST['xSerie'];
    // +++ set session and cookie
    if(!defined('CFG_CURRENT_EVENT')){
        define('CFG_CURRENT_EVENT', $xSerie);
    }

    $_SESSION[CFG_SESSION]['xSerie'] = $xSerie;
    set_cookie('xSerie', $xSerie);
    // --- set session and cookie
    location('index.php');
    
} elseif (isset($_POST['frm_action']) && $_POST['frm_action']=='quit_event'){
    $cls_result = new result(CFG_CURRENT_EVENT);
    $cls_result->closeEvent(3);
    
    location('index.php');
}

include(ROOT_PATH.'header.php');

$cls_result = new result(CFG_CURRENT_EVENT);

?>
<script type="text/javascript">
    $(document).ready(function(){
        $('.button').button();
        
        
        $('#refreshEvent, #refreshEventList').button({ icons: {primary:'ui-icon-refresh'} });
        $('#quitEvent').button({ icons: {primary:'ui-icon-home'} });
                
        $('#accordion_settings').accordion({ autoHeight: false
                                    , collapsible: true
                                    , navigation: true
                                    , active: false
        });
        $('#accordion_results').accordion({ autoHeight: false
                                    , navigation: true
                                    , active: 0
        });
        
        
        
        $('#xSerie').change(function(){
           $('#frm_select_event').submit();     
        });
        
        $('#quitEvent').click(function(){
            $('#frm_action').val('quit_event');
            $('#frm_select_event').submit();
        });
        
        $('#showStartlist').click(function(){
            $('#dialog_startlist').dialog({
                width: 'auto',
                zIndex: 1,
                position: 'center',
                resizable: false,
                draggable: true,
                modal: true,
                dialogClass: 'dialog_info',

                title: '<?=javascript_prepare($lg['STARTLIST'])?>',

                buttons: {
                    '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                        $(this).dialog('close');
                        $('#result').focus();
                    }
                }
            });
        });
        $('#showResultlist').click(function(){
            $('#dialog_resultlist').dialog({
                width: 'auto',
                zIndex: 1,
                position: 'center',
                resizable: false,
                draggable: true,
                modal: true,
                dialogClass: 'dialog_info',

                title: '<?=javascript_prepare($lg['RESULTLIST'])?>',

                buttons: {
                    '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                        $(this).dialog('close');
                        $('#result').focus();
                    }
                }
            });
        });
        $('#showEditForm').click(function(){         
            $('#dialog_edit').dialog({
                width: 'auto',
                zIndex: 1,
                position: 'center',
                resizable: false,
                draggable: true,
                modal: true,
                dialogClass: 'dialog_info',

                title: '<?=javascript_prepare($lg['RESULTS_CHANGE'])?>',

                buttons: [
                {
                    text: '<?=javascript_prepare($lg['CLOSE'])?>',
                    click: function() {
                        $(this).dialog('close');
                        $('#result').focus();
                    }
                }
                ]
            });
        });
        $('#refreshEvent').click(function(){
            location.reload();
        });
            
        $('#refreshEventList').click(function(){
            location.reload();
        });
        
        $('#res_result, #res_info').keyup(function(){
            $('#submitResult').attr('disabled', 'disabled');
            if($('#res_result').val()) {
                if($('#wind').val()==1 && $('#res_info').val() || $('#wind').val()==0) {
                    $('#submitResult').removeAttr('disabled');    
                }
            }    
        });
        
        $('#result_edit_result, #result_edit_info').keyup(function(){
            $('#editResult').attr('disabled', 'disabled');
            if($('#result_edit_result').val()) {
                if($('#wind').val()==1 && $('#result_edit_info').val() || $('#wind').val()==0) {
                    $('#editResult').removeAttr('disabled'); 
                }
            }    
        });
        
        $('#res_result').blur(function(){
            var res = $('#res_result').val();
            var info = $('#res_info').val();
            
            if ($('#res_result').val()) {
                $.ajax({
                    url: 'ajax_formatResult.php',
                    type: 'POST',
                    data: 'res='+res,
                    success: function(data) {
                        if(data=='result') {
                           alert('<?=$lg['ERROR_RESULT_INPUT']?>');
                        } else {
                            $('#res_result').val(data); 
                        }
                    }
                });
                
                $.ajax({
                    url: 'ajax_formatResultInfo.php',
                    type: 'POST',
                    data: 'res='+res+'&info='+info,
                    success: function(data) {
                        if(data=='error') {
                            alert('<?=$lg['ERROR_FUNCTION']?>');
                        } else {
                            $('#res_info').val(data); 
                        }
                    }
                });  
            }
        });
        
        $('#result_edit_result').blur(function(){
            var res = $('#result_edit_result').val();
            var info = $('#result_edit_info').val();
            
            if ($('#result_edit_result').val()) {
                $.ajax({
                    url: 'ajax_formatResult.php',
                    type: 'POST',
                    data: 'res='+res,
                    success: function(data) {
                        if(data=='result') {
                           alert('<?=$lg['ERROR_RESULT_INPUT']?>');
                        } else {
                            $('#result_edit_result').val(data); 
                        }
                    }
                });
                
                $.ajax({
                    url: 'ajax_formatResultInfo.php',
                    type: 'POST',
                    data: 'res='+res+'&info='+info,
                    success: function(data) {
                        if(data=='error') {
                            alert('<?=$lg['ERROR_FUNCTION']?>');
                        } else {
                            $('#result_edit_info').val(data); 
                        }
                    }
                });  
            }
        });
        
        $('div[name="result"]').click(function(){
            var xResult = $(this).attr('xResultat');
            var result = $(this).attr('result');
            var info = $(this).attr('info');
            
            $('#result_id').val(xResult);
            $('#result_edit_result').val(result);
            $('#result_edit_info').val(info);
            
            $('#result_edit_result').trigger('keyup');
            
            $('#dialog_edit_input').dialog({
                width: 'auto',
                zIndex: 1,
                position: 'center',
                resizable: false,
                draggable: true,
                modal: true,
                dialogClass: 'dialog_info',

                title: '<?=javascript_prepare($lg['RESULTS_CHANGE'])?>',

                buttons: [
                {
                    text: '<?=javascript_prepare($lg['CLOSE'])?>',
                    click: function() {  
                        $(this).dialog('close');
                    }
                }
                ]
            });
        });
        
        $('#editResult').click(function(){
            var event = $('#event').val();
            var round = $('#round').val();
            
            data = {
                result_id: $('#result_id').val(),
                result: $('#result_edit_result').val(),            
                info: $('#result_edit_info').val(),     
                event: event,
                round: round,          
            };
            
            $.ajax({
                url: 'ajax_editResult.php',
                type: 'POST',
                data: data,
                success: function(data) {
                    if(data=='result') {
                        alert('<?=$lg['ERROR_RESULT_INPUT']?>');
                    } else if(data=='db') {
                        alert('<?=$lg['ERROR_DB']?>')
                    } else {
                        $('#dialog_edit_input').dialog('close');
                        $('#dialog_edit').dialog('close');
                        //reload!!!
                        $('#result').focus();
                    }
                }
            });   
        });
        
        $('input[name="result_input"]').blur(function(){
            $('#result_value').val($(this).val());            
        });
        
        $('#submitResult').click(function(){
            
            var ath_res =  $('#res_result').val();
            if($('#res_info').val()) {
                var ath_info =  $('#res_info').val();
            } else {
                var ath_info = '-';
            }
            var ath_id =  $('#res_athlete').val();
            var event = $('#event').val();
            var round = $('#round').val();
            
            data = {
                ath_res: ath_res,
                ath_info: ath_info,            
                ath_id: ath_id,            
                event: event,
                round: round,           
            };

            $.ajax({
                url: 'ajax_saveResult.php',
                type: 'POST',
                data: data,
                success: function(data) {
                    if(data=='result') {
                        alert('<?=$lg['ERROR_RESULT_INPUT']?>');
                    } else if(data=='db') {
                        alert('<?=$lg['ERROR_DB']?>')
                    } else {
                        location.reload();  
                    }
                }
            });
        });
        
    
        
        <?php
        if(in_array($cls_result->status, $glb_status_results)){
        ?>
            $('#select_event').hide();
        <?php    
        }
        ?>
        $('#res_result').trigger('keyup');
        $('#result_edit_result').trigger('keyup');
    });
</script>
         
<?php
    
// +++ get events
$cls_event = new event();
$events = $cls_event->get();
// --- get events
?>
<div name="select_event" id="select_event">
<form name="frm_select_event" id="frm_select_event" action="index.php" method="post">
<input type="hidden" name="frm_action" id="frm_action" value="select_event" />
<b><label for="xSerie"><?=$lg['EVENT']?>:</label></b>
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
            $sel = ($event['xSerie']==CFG_CURRENT_EVENT) ? ' selected="selected"' : '';
            $round_bez = ($event['round_type'] != '0') ? "- ".$event['round_name']." ".$event['serie_bez'] : "";
            ?>
            <option value="<?=$event['xSerie']?>"<?=$sel?>><?=$event['disc_name']?> - <?=$event['cat_name']?> <?=$round_bez?> (<?=$event['round_start_date']?> - <?=$event['round_start_time']?>)</option>
            <?php
        }
    }
    ?>
</select>
<button type="button" name="refreshEventList" id="refreshEventList"><?=$lg['REFRESH']?></button>
<br><br><br>
</form>
</div>

    <?php

if(in_array($cls_result->status, $glb_status_results)) {
        
    $cls_result->_set_order(array('Position', 'ASC'));
    $cls_result->getAthleteData();
    $cls_result->getAthleteRanking();
  
    ?>
    <div id="dialog_startlist" style="display: none;">
        <table>
            <colgroup>
                <col width="50">
                <col width="50">
                <col width="200">
                <col width="50">
                <col width="">
            </colgroup>
        <?php
        foreach($cls_result->athletes_pos as $athlete) {
            if($athlete['xSerienstart'] == $cls_result->currentAthleteID){
                $class = "startlist_act";
            } else{
                $class = "startlist";
            }
            ?>
            <tr class="<?=$class?>">
                <td><?=$athlete['ath_pos']?></td>
                <td><?=$athlete['ath_bib']?></td>
                <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
                <td><?=substr($athlete['ath_yob'], -2)?></td>
                <td><?=$athlete['ath_club']?></td>
            </tr>
            <?php    
        }
        ?>
        </table>
    </div>

    <div id="dialog_resultlist" style="display: none;">
        <table>
            <colgroup>
                <col width="50">
                <col width="50">
                <col width="200">
                <col width="50">
                <col width="200">
                <col width="">
            </colgroup>
        <?php
        foreach($cls_result->athletes_rank as $athlete) {
            ?>
            <tr class="resultlist">
                <td><?=$athlete['ath_rank_out']?></td>
                <td><?=$athlete['ath_bib']?></td>
                <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
                <td><?=substr($athlete['ath_yob'], -2)?></td>
                <td><?=$athlete['ath_club']?></td>
                <td><?=$cls_result->formatResultOutput($athlete['ath_res'], 'RANKING')?></td>
            </tr>
            <?php    
        }
        ?>
        </table>
    </div>
    
    <div id="dialog_edit" style="display: none;">
        <input type="hidden" name="result_id" id="result_id">
        <table>
            <colgroup>
                <col width="50">
                <col width="200">
                <col width="">
            </colgroup>
        <?php
        foreach($cls_result->athletes_rank as $athlete) {
            $ath_res = $cls_result->getAthleteResults($athlete['xSerienstart']);
            ?>
            <tr class="resultlist">
                <td><?=$athlete['ath_bib']?></td>
                <td><?=$athlete['ath_name']." ".$athlete['ath_firstname']?></td>
                <?php
                foreach($ath_res as $res){
                ?>
                <td>
                    <div name="result" id="result" xResultat="<?=$res['xResultat']?>" result="<?=$cls_result->formatResultOutput($res['ath_res'], 'RANKING')?>" info="<?=$res['ath_info']?>" style="cursor: pointer;"><?=$cls_result->formatResultOutput($res['ath_res'], 'RANKING')?></div>
                </td>
                <?php
                }
                ?>
            </tr>
            <?php    
        }
        ?>
        </table>
    </div>
    <div id="dialog_edit_input" style="display: none;">
        <table>
            <tr>
                <td><input type="text" name="result_edit_result" id="result_edit_result" class="result" autocomplete="off" tabindex="101">
                <td class="info">
                    <?php
                    if($cls_result->wind == 1){
                        ?>
                        <input type="text" name="result_edit_info" id="result_edit_info" class="info" autocomplete="off" tabindex="102">
                        <?php    
                    }
                    ?>
                </td>
                <td><button type="button" name="editResult" id="editResult" tabindex="103"><?=$lg['OK']?></button></td>
            </tr>
        </table>
    </div>

    <table width="100%">
        <colgroup>
            <col width="50%">
            <col width="50%">
        </colgroup>
        <tr>
            <td valign="top">
                <table>
                    <colgroup>
                        <col width="150">
                        <col width="80">
                        <col width="80">
                        <col width="50">
                        <col width="80">
                    </colgroup>
                    <?php
                    $colspan = 5;
                    ?>
                    
                    <tr>
                        <td><?=$cls_result->discipline?></td>
                        <td><?=$cls_result->category?></td>
                        <td><?=($cls_result->round!='') ? $cls_result->round." ".$cls_result->desc : ''?></td>
                        <td><?=$lg['TIME_START']?>:</td>
                        <td><?=$cls_result->start_time?></td>
                    </tr>
                    <tr>
                        <td colspan="<?=$colspan-2?>"></td>
                        <td><?=$lg['TIME_CALL']?>:</td>
                        <td><?=$cls_result->call_time?></td>
                    </tr>
                    <tr>
                        <td height="10" colspan="<?=$colspan?>"></td>
                    </tr>
                </table>
                <table>
                    <colgroup>
                        <col width="100">
                        <col width="10">
                        <col width="100">
                        <col width="10">
                        <col width="100">
                        <col width="10">
                        <col width="250">
                        <col width="10">
                        <col width="100">
                    </colgroup>
                    <?php
                    $colspan = 9;
                    ?>
                    <tr>
                        <td><button type="button" name="showStartlist" id="showStartlist"><?=$lg['STARTLIST']?></button></td>
                        <td></td>
                        <td><button type="button" name="showResultlist" id="showResultlist"><?=$lg['RESULTLIST']?></button></td>
                        <td></td>
                        <td><button type="button" name="showEditForm" id="showEditForm"><?=$lg['RESULTS_CHANGE']?></button></td>
                        <td></td>
                        <td><button type="button" name="quitEvent" id="quitEvent"><?=$lg['EVENT_QUIT']?></button></td>
                        <td></td>
                        <td><button type="button" name="refreshEvent" id="refreshEvent"><?=$lg['REFRESH']?></button></td>
                    </tr>
                    <tr>
                        <td height="30" colspan="<?=$colspan?>"></td>
                    </tr>
                </table>
            </td>
            <td valign="top">
                <div id="accordion_settings">
                    <h4><a href="#"><?=$lg['SETTINGS']?></a></h4>       
                    <div>
                    <table>
                        <colgroup>
                            <col width="200">
                            <col width="">
                        </colgroup>
                        <?php
                        $colspan = 2;
                        ?>
                        <tr>
                            <td><?=$lg['ATTEMPTS']?>:</td>
                            <td><?=$cls_result->attempts?></td>
                        </tr>
                        <tr>
                            <td height="5" colspan="<?=$colspan?>"></td>
                        </tr>
                        <tr>
                            <td><?=$lg['FINAL']?>:</td>
                            <td><?=($cls_result->final == 1) ? $lg['YES'] : $lg['NO']?></td>
                        </tr>
                        <?php
                        if($cls_result->final == 1){
                            ?>
                            <tr>
                                <td height="5" colspan="<?=$colspan?>"></td>
                            </tr>
                            <tr>
                                <td><?=$lg['FINAL_ATHLETES']?>:</td>
                                <td><?=$cls_result->finalists?></td>
                            </tr>
                            <?php
                        }
                        if(isset($cls_result->drop)) {
                            $drop = explode(",",$cls_result->drop);
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
                            $drops = str_replace("%n%", $drops, $lg['AFTER_ATTEMPTS']);
                        } else {
                            $drops = $lg['NO'];
                        }
                        ?>
                        <tr>
                            <td height="5" colspan="<?=$colspan?>"></td>
                        </tr>
                        <tr>
                            <td><?=$lg['DROP_POSITION']?>:</td>
                            <td><?=$drops?></td>
                        </tr>
                    
                    </table>
                    </div> 
                </div>
            </td>
        </tr>
    </table>
    <div id="accordion_results">
        <h4><a href="#"><?=$lg['RESULTS']?></a></h4>
        <div>
            <input type="hidden" name="res_athlete" id="res_athlete" value="<?=$cls_result->currentAthleteID?>">
            <input type="hidden" name="round" id="round" value="<?=$cls_result->xRunde?>">
            <input type="hidden" name="event" id="event" value="<?=$cls_result->xWettkampf?>">
            <input type="hidden" name="wind" id="wind" value="<?=$cls_result->wind?>">
            <table>
                <colgroup>
                    <col width="80">
                    <col width="200">
                    <col width="200">
                    <col>
                    <col>
                </colgroup>
                <?php
                $colspan = 4;
                ?>
            
                <tr>
                    <td><?=$cls_result->athletes_id[$cls_result->currentAthleteID]['ath_bib']?></td>
                    <td><?=$cls_result->athletes_id[$cls_result->currentAthleteID]['ath_name']?> <?=$cls_result->athletes_id[$cls_result->currentAthleteID]['ath_firstname']?></td>
                    <td><?=$cls_result->athletes_id[$cls_result->currentAthleteID]['ath_club']?></td>
                    <td>
                        <?php
                        if($cls_result->nofResults[$cls_result->currentAthleteID]['results'] == 0) {
                        ?>
                        <button type="button" name="setDNS" id="setDNS"><?=$lg['RESULT_INVALID_DNS_SHORT']?></button>
                        <?php
                        }
                        ?>
                    </td>
                    <td><button type="button" name="setDSQ" id="setDSQ"><?=$lg['RESULT_INVALID_DSQ_SHORT']?></button></td>
                </tr>
                <tr>
                    <td height="10" colspan="<?=$colspan?>"></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="<?=$colspan?>-1">
                        <?php
                        $ath_res = "";
                        if(count($cls_result->results_ath)) {
                            $x = 0;
                            foreach($cls_result->results_ath as $athres) {
                                if($x != 0) {
                                    $ath_res .= " / ";
                                }
                                $ath_res .= $cls_result->formatResultOutput($athres['ath_res'], 'SHORT')." ";
                                if($cls_result->wind == 1 && $athres['ath_res'] > 0){
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
                    <col width="">
                    <col width="20">
                    <col width="">
                </colgroup>
                <?php
                $colspan = 3;
                ?>
                <tr>
                    <td class="result"><?=$lg['PERFORMANCE']?>:</td>
                    <td></td>
                    <td class="wind">
                        <?php
                        if($cls_result->wind == 1){
                            ?>
                            <?=$lg['WIND']?>:
                            <?php    
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    
                    <td><input type="text" name="res_result" id="res_result" class="result" autocomplete="off" tabindex="1">
                    <script type="text/Javascript" language="JavaScript">
                    <!--
                        document.getElementById("res_result").focus();
                    //-->
                    </script>
                    </td>
                    <td></td>
                    <td class="info">
                        <?php
                        if($cls_result->wind == 1){
                            ?>
                            <input type="text" name="res_info" id="res_info" class="info" autocomplete="off" tabindex="2">
                            <?php    
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td height="20" colspan="<?=$colspan?>"></td>
                </tr>
                <tr>
                    <td><button type="button" name="submitResult" id="submitResult" tabindex="3"><?=$lg['OK']?></button></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td height="20" colspan="<?=$colspan?>"></td>
                </tr>
            </table>
            <hr>
            <table>
                <tr>
                    <td colspan="<?=$colspan?>"><?=$lg['NEXT']?>:</td>
                </tr>
                <tr>
                    <td><?=$cls_result->athletes_id[$cls_result->nextAthleteID]['ath_bib']?></td>
                    <td><?=$cls_result->athletes_id[$cls_result->nextAthleteID]['ath_name']?> <?=$cls_result->athletes_id[$cls_result->nextAthleteID]['ath_firstname']?></td>
                    <td><?=$cls_result->athletes_id[$cls_result->nextAthleteID]['ath_club']?></td>
                </tr>
            </table>
            </form>
        </div>
    </div> 
    <br><br>    
    
    <?php
    
    
}




include(ROOT_PATH.'footer.php');
?>
