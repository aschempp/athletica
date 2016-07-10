<?php
// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
    header('Location: index.php');
    exit();
}
// +++ make sure that the file was not loaded directly

// set charset to utf-8
header("Content-Type: text/html; charset=utf-8");

$application_name = (!is_null($glb_connection)) ? $lg['APPLICATION_NAME'] : CFG_APPLICATION_NAME;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?=html_prepare($application_name)?></title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="Cache-Control" content="no-cache"/>

        <link rel="shortcut icon" type="image/x-icon" href="<?=GLOBAL_PATH?>img/athletica.ico"/>

        <link rel="stylesheet" type="text/css" href="<?=GLOBAL_PATH?>css/style.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=ROOT_PATH?>css/style.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="<?=ROOT_PATH?>css/print.css" media="print"/>
        <!--[if IE]>
            <link rel="stylesheet" type="text/css" href="<?=ROOT_PATH?>css/ie.css"/>
        <![endif]-->

        <script type="text/javascript">
            var ROOT_PATH = '<?=javascript_prepare(ROOT_PATH)?>';
        </script>

        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/inc.functions.js"></script>
        <script type="text/javascript" src="<?=ROOT_PATH?>lib/inc.functions.js"></script>

        <!-- JQUERY & UI -->
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/ui/jquery-ui-1.8.18.custom.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=GLOBAL_PATH?>lib/jquery/ui/css/vader/jquery-ui-1.8.18.custom.css" media="screen"/>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery.validate.min.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/language/validate/messages_<?=strtolower(CFG_CURRENT_LANGUAGE)?>.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/language/validate/methods_custom.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/language/datepicker/jquery.ui.datepicker-<?=strtolower(CFG_CURRENT_LANGUAGE)?>.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/ui/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery.doubleSelect.min.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery.scrollTo-min.js"></script>
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery.hotkeys-0.7.9.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=ROOT_PATH?>css/jquery-ui-dialog.custom.css"/>

        <!-- DATATABLES -->
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=ROOT_PATH?>css/datatables_jui.css" media="screen"/>

        <!-- MULTISELECT -->
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/multiselect/jquery.multiselect.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=GLOBAL_PATH?>lib/jquery/multiselect/css/ui.multiselect.css" media="screen"/>

        <!-- JQPrint -->
        <script type="text/javascript" src="<?=GLOBAL_PATH?>lib/jquery/jquery.jqprint.js"></script>

        <!-- SUPERFISH -->
        <link rel="stylesheet" type="text/css" media="screen" href="<?=ROOT_PATH?>lib/superfish/superfish.css"/>
        <link rel="stylesheet" type="text/css" media="screen" href="<?=ROOT_PATH?>lib/superfish/superfish-navbar.css"/>
        <script type="text/javascript" src="<?=ROOT_PATH?>lib/superfish/hoverIntent.js"></script>
        <script type="text/javascript" src="<?=ROOT_PATH?>lib/superfish/superfish.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $('ul.sf-menu').superfish({
                    pathClass:  'current',
                    //animation:   {opacity:'show',height:'show'},      // fade-in and slide-down animation
                    speed:       'fast',                              // faster animation speed
                    delay: 1200,
                    dropShadows: false                              // disable drop shadows
                });
                
                $('button').button();
        
                $("#application_mode").buttonset();
                $('input[name="application_mode"]').click(function(){
                    var mode = $("#application_mode :checked").val();
                    $.ajax({
                      url: '<?=ROOT_PATH?>mode_switch.php?mode='+mode,
                      success: function(data) {
                        if(mode=='live') {
                            window.location.href = '<?=ROOT_PATH?>content/settings/server.php';
                        } else{
                            window.location.href = '<?=ROOT_PATH?>content/index.php'
                        }
                      }
                    });
                })
            });

            function show_dialog_wait(title, text){
                $('#dialog_wait').dialog('close');

                $('#dialog_wait').html(text);
                $('#dialog_wait').dialog({
                    width: 'auto',
                    height: 100,
                    zIndex: 9,

                    position: 'center',

                    stack: true,
                    resizable: false,
                    draggable: false,
                    modal: true,
                    closeOnEscape: false,

                    dialogClass: 'dialog_info',

                    open: function(){
                        $('#dialog_wait').prev().children('a.ui-dialog-titlebar-close').hide();
                    },

                    title: title,

                    buttons: {}
                });
            }

            function show_dialog_result(dialogClass, title, text){
                $('#dialog_result').dialog('close');

                $('#dialog_result').html(text);
                $('#dialog_result').dialog({
                    width: 500,
                    height: 120,
                    zIndex: 10,

                    position: 'center',

                    stack: true,
                    resizable: false,
                    draggable: false,
                    modal: true,

                    dialogClass: dialogClass,

                    open: function(){
                        $('#dialog_result').prev().children('a.ui-dialog-titlebar-close').hide();
                    },

                    title: title,

                    buttons: {
                        '<?=javascript_prepare($lg['CLOSE'])?>': function() {
                            $(this).dialog('close');
                        }
                    }
                });
            }
        </script>
    </head>
    <body>
        <div style="display: none;">
            <div id="dialog"></div>
        </div>

        <div style="display: none;">
            <div id="dialog_wait"></div>
        </div>

        <div style="display: none;">
            <div id="dialog_question"></div>
        </div>

        <div style="display: none;">
            <div id="dialog_result"></div>
        </div>

        <a name="top"></a>
            
        <br clear="all"/>

        <?php
        if($cls_php_ini->valid && !is_null($glb_connection)){
            ?>
            <div id="menu">
                <ul id="mainmenu" class="sf-menu sf-navbar sf-js-enabled">
                    <?php
                        $class = (CURRENT_CATEGORY=='start' && CURRENT_PAGE=='start') ? 'current' : '';
                        ?>
                        <li class="<?=$class?>">
                            <a href="<?=ROOT_PATH?>content/results/index.php" class="<?=$class?>"><?=$lg['RESULTS']?></a>
                        </li>
                        <?php
                        
                        //Menu
                        include(ROOT_PATH.'content/navigation/settings.php');
                        ?>
                        <?php

                        if(LOGGED_IN){ //Menu Admin
                            
                        }

                        include(ROOT_PATH.'content/navigation/language.php');

                        if(LOGGED_IN){
                            ?>
                            <li>
                                <a href="<?=ROOT_PATH?>index.php?a=logout"><?=$lg['LOGOUT']?> <span style="font-size: 10px;">(<?=$glb_login['GENNUMBER']?>)</span></a>
                            </li>
                            <?php
                        }
                    ?>
                    <div id="application_mode" style="display: block;">
                        <?php 
                        foreach($modes as $nav_mode_code){
                            ?>
                            <input type="radio" id="application_mode_<?=$nav_mode_code?>" name="application_mode" value="<?=$nav_mode_code?>" <?=CFG_CURRENT_MODE==$nav_mode_code?'checked="checked"':''?> /><label for="application_mode_<?=$nav_mode_code?>"><?=$lg['MODE_'.strtoupper($nav_mode_code)]?></label>
                            <?php
                        }
                        ?>
                    </div>
                </ul>
                
            </div>
            <?php
        }
        ?>

        <div id="content">