<?php
define('GLOBAL_PATH', '../../../');
define('ROOT_PATH', '../../');
define('CURRENT_CATEGORY', 'settings');
define('CURRENT_PAGE', 'server');

require_once(ROOT_PATH.'lib/inc.init.php');

if(isset($_POST['frm_action']) && $_POST['frm_action']=='settings_server'){
    $data = array(
        array('config_key' => 'server_host', 'config_value' => $_POST['server_host'])
        , array('config_key' => 'server_port', 'config_value' => $_POST['server_port'])
        , array('config_key' => 'server_username', 'config_value' => $_POST['server_username'])
        , array('config_key' => 'server_password', 'config_value' => $_POST['server_password'])
        , array('config_key' => 'server_db', 'config_value' => $_POST['server_db'])
    );    
    $cls_config->updateConfig($data);
    location('server.php');
}



include(ROOT_PATH.'header.php');

?>
<script type="text/javascript">
    $(document).ready(function(){
        // +++ button
        $('button').button();
        $('.button').button();
        // --- button
        
        // +++ form validator
        $('#frm_settings_server').validate({
            errorPlacement: function(error, element){},

            highlight: function(element, errorClass){
                $(element).addClass('ui-state-error');
            },
            unhighlight: function(element, errorClass){
                $(element).removeClass('ui-state-error');
            }
        });
        // --- form validator
        
        
    });
</script>
         
<?php

$value_server_host = (isset($_POST['server_host'])) ? $_POST['server_host'] : $cfg_value['server']['server_host'];
$value_server_port = (isset($_POST['server_port'])) ? $_POST['server_port'] : $cfg_value['server']['server_port'];
$value_server_username = (isset($_POST['server_username'])) ? $_POST['server_username'] : $cfg_value['server']['server_username'];
$value_server_password = (isset($_POST['server_password'])) ? $_POST['server_password'] : $cfg_value['server']['server_password'];
$value_server_db = (isset($_POST['server_db'])) ? $_POST['server_db'] : $cfg_value['server']['server_db'];

$tabindex = 0;
?>
<h1 class="content_title"><?=$lg['ATHLETICA_SERVER']?></h1>
<form name="frm_settings_server" id="frm_settings_server" action="server.php" method="post">
<input type="hidden" name="frm_action" id="frm_action" value="settings_server" />
<?php
if(CFG_CURRENT_MODE=='live' && is_null($glb_connection_server)) {
    box($lg['ERROR_SERVER_TEXT'], $lg['ERROR_SERVER_TITLE'], 'error', 0, 20);
} else {
    box($lg['SUCCESS_SERVER_TEXT'], $lg['SUCCESS_SERVER_TITLE'], 'success', 0, 20);
}
?>
<table>
    <colgroup>
        <col width="40%">
        <col width="20%">
        <col width="40%">
    </colgroup>
    <tr>
        <td><b><label for="server_host"><?=$lg['SERVER_HOST']?></label></b></td>
        <td></td>
        <td><b><label for="server_port"><?=$lg['SERVER_PORT']?></label></b></td>
    </tr>
    <tr>
        <td>
            <?php
            $tabindex++;
            ?>
            <input type="text" id="server_host" name="server_host" value=<?=$value_server_host?> class="input required" tabindex="<?=$tabindex?>"></td>
        <td></td>
        <td>
            <?php
            $tabindex++;
            ?>
            <input type="text" id="server_port" name="server_port" value=<?=$value_server_port?> class="input required" tabindex="<?=$tabindex?>"></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td><?=$lg['DEFAULT']?>: 3306</td>
    </tr>
    <tr>
        <td height="20" colspan="3"></td>
    </tr>
    <tr>
        <td><b><label for="server_username"><?=$lg['SERVER_USERNAME']?></label></b></td>
        <td></td>
        <td><b><label for="server_password"><?=$lg['SERVER_PASSWORD']?></label></b></td>
    </tr>
    <tr>
        <td>
            <?php
            $tabindex++;
            ?>
            <input type="text" id="server_username" name="server_username" value=<?=$value_server_username?> class="input" tabindex="<?=$tabindex?>"></td>
        <td></td>
        <td>
            <?php
            $tabindex++;
            ?>
            <input type="password" id="server_password" name="server_password" value=<?=$value_server_password?> class="input" tabindex="<?=$tabindex?>"></td>
    </tr>
    <tr>
        <td><?=$lg['DEFAULT']?>: athletica</td>
        <td></td>
        <td><?=$lg['DEFAULT']?>: athletica</td>
    </tr>
    <tr>
    <tr>
        <td height="20" colspan="3"></td>
    </tr>
    <tr>
        <td colspan="3"><b><label for="server_db"><?=$lg['SERVER_DATABASE']?></label></b></td>
    </tr>
    <tr>
        <td>
            <?php
            $tabindex++;
            ?>
            <input type="text" id="server_db" name="server_db" value=<?=$value_server_db?> class="input required" tabindex="<?=$tabindex?>"></td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=$lg['DEFAULT']?>: athletica</td>
    </tr>
    <tr>
    <tr>
        <td height="20" colspan="3"></td>
    </tr>
    <tr>
        <td>
            <?php
            $tabindex++;
            ?>
            <button type="submit" name="btn_submit" id="btn_submit" class="button" tabindex="<?=$tabindex?>"/><?=$lg['SAVE']?></td>
    </tr>

</table>
</form>



<?php
include(ROOT_PATH.'footer.php');
?>
