<?php
if(!defined('GLOBAL_PATH')) {
    define('GLOBAL_PATH', '../../../');
}
if(!defined('ROOT_PATH')) {
    define('ROOT_PATH', '../../');
}
if(!defined('CURRENT_CATEGORY')) {
    define('CURRENT_CATEGORY', 'athletica_tech');
}
if(!defined('CURRENT_PAGE')) {
    define('CURRENT_PAGE', 'results');
}

require_once(ROOT_PATH.'lib/inc.init.php');

if(isset($_POST['frm_action']) && $_POST['frm_action']=='select_event'){
    $xSerie = $_POST['xSerie'];
    $xMeeting = $_POST['xMeeting'];
    // +++ set session and cookie
    if(!defined('CFG_CURRENT_EVENT')){
        define('CFG_CURRENT_EVENT', $xSerie);
    }

    $_SESSION[CFG_SESSION]['xSerie'] = $xSerie;
    set_cookie('xSerie', $xSerie);
    
    if(!defined('CFG_CURRENT_MEETING')){
        define('CFG_CURRENT_MEETING', $xMeeting);
    }

    $_SESSION[CFG_SESSION]['xMeeting'] = $xMeeting;
    set_cookie('xMeeting', $xMeeting);
    // --- set session and cookie
    location('index.php');
    
} elseif (isset($_POST['frm_action']) && $_POST['frm_action']=='quit_event'){
    resetActiveAthlete(CFG_CURRENT_EVENT);
    closeEvent($_POST['xRunde'], $glb_status_quit);
    location('index.php');
}

include(ROOT_PATH.'header.php');

// +++ get events
$meetings = getMeetings(CFG_CURRENT_MEETING);
$events = getEvents(CFG_CURRENT_MEETING,CFG_CURRENT_EVENT);
// --- get events
if(CFG_CURRENT_EVENT > 0){
    if($events){
        $type = $glb_types_results[$events['disc_type']];
    } else{
        closeEvent(0, $glb_status_quit);
        location('index.php');
    }
}

?>
<script type="text/javascript">
    $(document).ready(function(){
        show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_DATA'])?>');
        
        $('.button').button();

        <?php
        if(CFG_CURRENT_EVENT > 0) {
            ?>           
            $('#select_event').hide();
            
            $('#accordion_settings').accordion({ autoHeight: false
                                    , collapsible: true
                                    , navigation: true
                                    , active: false
            });            
            
            $('#accordion_results').accordion({ autoHeight: false
                                    , navigation: true
                                    , active: 0
            });
            
            $('#refreshEvent, #refreshEventList').button({ icons: {primary:'ui-icon-refresh'} });
            $('#quitEvent').button({ icons: {primary:'ui-icon-home'} });
            
            $('#quitEvent').click(function(){
                $('#frm_action').val('quit_event');
                $('#xRunde').val($('#round').val());
                $('#frm_select_event').submit();
            });
        
            $('#refreshEvent').click(function(){
                location.reload();
            });
            
            $('#showSettings').click(function(){
                $(document).unbind('keyup');
                show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_SETTINGS'])?>');
            
                var url = '<?=$type?>/dialog_settings.php';
                $('#dialog_settings').load(url, function(response, status, req){
                    if(status=='success'){
                        $('#dialog_wait').dialog('close');

                        $('#dialog_settings').dialog({
                            width: 'auto',
                            height: 'auto',
                            zIndex: 1000,

                            position: 'center',
                            
                            stack: false,
                            resizable: false,
                            draggable: true,
                            modal: true,

                            dialogClass: 'dialog_content',

                            open: function(){
                                $('#dialog_settings').prev().children('a.ui-dialog-titlebar-close').hide();
                            },

                            title: '<?=javascript_prepare($lg['SETTINGS'])?>',

                            buttons: {
                                '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                                    $(this).dialog('close');
                                    show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_DATA'])?>');
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
                    }
                });
            });
            
            $('#showStartlist').click(function(){
                $(document).unbind('keyup');
                var current = $('#currentID').val();
                
                show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_STARTLIST'])?>');
                
                var url = '<?=$type?>/dialog_startlist.php?current='+current;
                $('#dialog_startlist').load(url, function(response, status, req){
                    if(status=='success'){
                        $('#dialog_wait').dialog('close');

                        $('#dialog_startlist').dialog({
                            width: 'auto',
                            height: 'auto',
                            zIndex: 1000,

                            position: 'center',
                            
                            stack: true,
                            resizable: false,
                            draggable: true,
                            modal: true,

                            dialogClass: 'dialog_content',

                            open: function(){
                                $('#dialog_startlist').prev().children('a.ui-dialog-titlebar-close').hide();
                                if ($(this).parent().height() > $(window).height()*0.7) {
                                    $(this).height($(window).height()*0.7);
                                }
                                if ($(this).parent().width() > $(window).width()*0.7) {
                                    $(this).width($(window).width()*0.7);
                                }
                                $(this).dialog({position: "center"});
                            },

                            title: '<?=javascript_prepare($lg['STARTLIST'])?>',

                            buttons: {
                                '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                                    $(this).dialog('close');
                                    show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_DATA'])?>');
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
                    }
                });
            });
            
            $('#showResultlist').click(function(){
                $(document).unbind('keyup');
                show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_RESULTLIST'])?>');
                
                var url = '<?=$type?>/dialog_resultlist.php';
                $('#dialog_resultlist').load(url, function(response, status, req){
                    if(status=='success'){
                        $('#dialog_wait').dialog('close');

                        $('#dialog_resultlist').dialog({
                            width: 'auto',
                            height: 'auto',
                            zIndex: 1000,

                            position: 'center',

                            stack: true,
                            resizable: false,
                            draggable: true,
                            modal: true,

                            dialogClass: 'dialog_content',

                            open: function(){
                                $('#dialog_resultlist').prev().children('a.ui-dialog-titlebar-close').hide();
                                if ($(this).parent().height() > $(window).height()*0.7) {
                                    $(this).height($(window).height()*0.7);
                                }
                                if ($(this).parent().width() > $(window).width()*0.7) {
                                    $(this).width($(window).width()*0.7);
                                }
                                $(this).dialog({position: "center"});
                            },

                            title: '<?=javascript_prepare($lg['RESULTLIST'])?>',

                            buttons: {
                                '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                                    $(this).dialog('close');
                                    show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_DATA'])?>');
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
                    }
                });
            });
            
            $('#showEditForm').click(function(){
                $(document).unbind('keyup');
                show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_RESULTS'])?>');
                
                var url = '<?=$type?>/dialog_edit.php?';
                $('#dialog_edit').load(url, function(response, status, req){
                    if(status=='success'){
                        $('#dialog_wait').dialog('close');

                        $('#dialog_edit').dialog({
                            width: 'auto',
                            height: 'auto',
                            zIndex: 1000,

                            position: 'center',

                            stack: true,
                            resizable: false,
                            draggable: true,
                            modal: true,

                            dialogClass: 'dialog_content',

                            open: function(){
                                $('#dialog_edit').prev().children('a.ui-dialog-titlebar-close').hide();
                                if ($(this).parent().height() > $(window).height()*0.7) {
                                    $(this).height($(window).height()*0.7);
                                }
                                if ($(this).parent().width() > $(window).width()*0.7) {
                                    $(this).width($(window).width()*0.7);
                                }
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
            
            $('#showStartHeight').click(function(){
                $(document).unbind('keyup');
                show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_STARTHEIGHTS'])?>');
                
                var url = '<?=$type?>/dialog_startheight.php';
                $('#dialog_startheight').load(url, function(response, status, req){
                    if(status=='success'){
                        $('#dialog_wait').dialog('close');

                        $('#dialog_startheight').dialog({
                            width: 'auto',
                            height: 'auto',
                            zIndex: 1000,

                            position: 'center',
                            
                            stack: true,
                            resizable: false,
                            draggable: true,
                            modal: true,

                            dialogClass: 'dialog_content',

                            open: function(){
                                $('#dialog_startheight').prev().children('a.ui-dialog-titlebar-close').hide();
                                if ($(this).parent().height() > $(window).height()*0.7) {
                                    $(this).height($(window).height()*0.7);
                                }
                                if ($(this).parent().width() > $(window).width()*0.7) {
                                    $(this).width($(window).width()*0.7);
                                }
                                $(this).dialog({position: "center"});
                            },

                            title: '<?=javascript_prepare($lg['HEIGHTS_START'])?>',

                            buttons: {
                                '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                                    $(this).dialog('close');
                                    show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_STARTHEIGHTS'])?>');
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
                    }
                });
            });
            
            $('#showHeights').click(function(){
                $(document).unbind('keyup');
                show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['LOADING_HEIGHTS'])?>');
                
                var url = '<?=$type?>/dialog_heights.php';
                $('#dialog_heights').load(url, function(response, status, req){
                    if(status=='success'){
                        $('#dialog_wait').dialog('close');

                        $('#dialog_heights').dialog({
                            width: 'auto',
                            height: 'auto',
                            zIndex: 1000,

                            position: 'center',
                            
                            stack: true,
                            resizable: false,
                            draggable: true,
                            modal: true,

                            dialogClass: 'dialog_content',

                            open: function(){
                                $('#dialog_heights').prev().children('a.ui-dialog-titlebar-close').hide();
                                if ($(this).parent().height() > $(window).height()*0.7) {
                                    $(this).height($(window).height()*0.7);
                                }
                                if ($(this).parent().width() > $(window).width()*0.7) {
                                    $(this).width($(window).width()*0.7);
                                }
                                $(this).dialog({position: "center"});
                            },

                            title: '<?=javascript_prepare($lg['HEIGHTS'])?>',

                            buttons: {
                                '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                                    $(this).dialog('close');
                                    show_dialog_wait('<?=javascript_prepare($lg['PLEASE_WAIT'])?>', '<?=javascript_prepare($lg['SAVING_HEIGHTS'])?>');
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
                    }
                });
            });
            
            var url = '<?=$type?>/results.php';
            $('#div_results').load(url, function(response, status, req){
                if(status=='success'){
                    $('#dialog_wait').dialog('close');
                    $('#res_result').focus(); 
                }
            });
            
            <?php
        } else {
            ?>
            $('#select_event').show();
            
            $('#refreshEventList').click(function(){
                <?=closeEvent(0,0);?>
                location.reload();
            });
            
            $('#xMeeting').change(function(){
                if($(this).val()) {
                    $('#frm_select_event').submit();     
                }
            });
            
            $('#xSerie').change(function(){
               $('#frm_select_event').submit();     
            });
            
            $('#dialog_wait').dialog('close');
            <?php
        }
        ?>
    });
</script>

<div id="select_event" style="display: none;">
    <?php
    include_once('select_event.php');
    ?>
</div>

<?php
if(CFG_CURRENT_EVENT > 0) {
    $meeting = $meetings[0];
    
    require_once(ROOT_PATH.'lib/cls.result_'.$type.'.php');

    createResultTable(CFG_CURRENT_EVENT);
    rankAthletes(CFG_CURRENT_EVENT);
    ?>

    

    <div id="accordion_results">
        <h4><a href="#"><?=$lg['RESULTS']?></a></h4>
        <div id="div_results">

        </div>
    </div>
    <div id="header">
    <?php
    include_once($type.'/header.php');  
    ?>
    </div>

    <div id="dialog_settings" style="display: none;">
    </div> 

    <div id="dialog_startlist" style="display: none;">
    </div>

    <div id="dialog_resultlist" style="display: none;">
    </div>

    <div id="dialog_edit" style="display: none;">
    </div>
    
    <div id="dialog_edit_input" style="display: none;">
    </div>
    
    <div id="dialog_startheight" style="display: none;">
    </div>
    
    <div id="dialog_heights" style="display: none;">
    </div>
    <?php
} else {
    // dropTempresults();
}
include(ROOT_PATH.'footer.php');
?>
