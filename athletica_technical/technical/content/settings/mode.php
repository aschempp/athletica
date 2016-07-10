<?php
define('GLOBAL_PATH', '../../../');
define('ROOT_PATH', '../../');
define('CURRENT_CATEGORY', 'settings');
define('CURRENT_PAGE', 'mode');

require_once(ROOT_PATH.'lib/inc.init.php');

include(ROOT_PATH.'header.php');

?>
<script type="text/javascript">
    $(function(){
        $('button').button();
        
        $("#mode_radio").buttonset();
        $('input[name="mode_radio"]').click(function(){
            var mode = $("#mode_radio :checked").val();
            $('#loading').html('<br><br><img src="<?=ROOT_PATH?>img/loadingAnimation.gif" />');
            $.ajax({
              url: 'mode_switch.php?mode='+mode,
              success: function(data) {
                window.location.href = 'mode.php';
              }
            });
        })
        
    })
</script>        
        
        
<h1 class="content_title"><?=$lg['MODE']?></h1>

<form name="frm_mode" id="frm_mode" action="mode.php" method="post">
<div id="mode_radio" style="width: 250px; display: block;">
    <?php 
    foreach($modes as $nav_mode_code){
        ?>
        <input type="radio" id="mode_radio_<?=$nav_mode_code?>" name="mode_radio" value="<?=$nav_mode_code?>" <?=CFG_CURRENT_MODE==$nav_mode_code?'checked="checked"':''?> /><label for="mode_radio_<?=$nav_mode_code?>"><?=$lg['MODE_'.strtoupper($nav_mode_code)]?></label>
        <?php
    }
    ?>
</div>
<div id="loading" style="width: 250px;"></div>
</form>
<br>
<br>



<?php
include(ROOT_PATH.'footer.php');
?>
