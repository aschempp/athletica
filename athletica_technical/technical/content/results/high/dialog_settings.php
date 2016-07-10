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
        $('#diffUntil1').change(function(){
            var height = $(this).val(); 
            var diff = 1;   
            if(height == ''){
                alert('<?=$lg['ERROR_INPUT_REQUIRED']?>')
            } else{
                $.ajax({
                    url: '<?=$type?>/ajax_saveDiffUntil.php',
                    type: 'POST',
                    data: 'height='+height+'&diff='+diff,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_DB']?>');
                        } else {
                            $.ajax({
                                url: '<?=$type?>/ajax_formatHeight.php',
                                type: 'POST',
                                data: 'height='+height,
                                success: function(data) {
                                    if(data=='error') {
                                       alert('<?=$lg['ERROR_INPUT_VALUE']?>');
                                    } else {
                                        $('#diffUntil1').val(data); 
                                    }
                                }
                            });     
                        }
                    }
                }); 
            }
        });    
        
        $('#diffUntil2').change(function(){
            var height = $(this).val(); 
            var diff = 2;   
            if(height == ''){
                alert('<?=$lg['ERROR_INPUT_REQUIRED']?>')
            } else{
                $.ajax({
                    url: '<?=$type?>/ajax_saveDiffUntil.php',
                    type: 'POST',
                    data: 'height='+height+'&diff='+diff,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_DB']?>');
                        } else {
                            $.ajax({
                                url: '<?=$type?>/ajax_formatHeight.php',
                                type: 'POST',
                                data: 'height='+height,
                                success: function(data) {
                                    if(data=='error') {
                                       alert('<?=$lg['ERROR_INPUT_VALUE']?>');
                                    } else {
                                        $('#diffUntil2').val(data); 
                                    }
                                }
                            });     
                        }
                    }
                }); 
            }
        });
        
        $('#diffValue1').change(function(){
            var diff = 1;
            var value = $(this).val();
            if(value == ''){
                alert('<?=$lg['ERROR_INPUT_REQUIRED']?>')
            } else{
                $.ajax({
                    url: '<?=$type?>/ajax_saveDiffValue.php',
                    type: 'POST',
                    data: 'value='+value+'&diff='+diff,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_DB']?>');   
                        }
                    }
                }); 
            }
        }); 
        
        $('#diffValue2').change(function(){
            var diff = 2;
            var value = $(this).val();
            if(value == ''){
                alert('<?=$lg['ERROR_INPUT_REQUIRED']?>')
            } else{
                $.ajax({
                    url: '<?=$type?>/ajax_saveDiffValue.php',
                    type: 'POST',
                    data: 'value='+value+'&diff='+diff,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_DB']?>');   
                        }
                    }
                }); 
            }
        });
        
        $('#diffValue3').change(function(){
            var diff = 3;
            var value = $(this).val();
            if(value == ''){
                alert('<?=$lg['ERROR_INPUT_REQUIRED']?>')
            } else{
                $.ajax({
                    url: '<?=$type?>/ajax_saveDiffValue.php',
                    type: 'POST',
                    data: 'value='+value+'&diff='+diff,
                    success: function(data) {
                        if(data=='error') {
                           alert('<?=$lg['ERROR_DB']?>');   
                        }
                    }
                }); 
            }
        }); 
    });
</script>

<?php
$settings = getHighSettings(CFG_CURRENT_EVENT);
?>
<form id="testForm">
<table class="event_settings">
    <colgroup>
        <col width="100">
        <col>
    </colgroup>
    <?php
    $colspan = 2;
    ?>
    <tr class="event_settings">
        <td><b><?=$lg['HEIGHTS_DIFF']?></b></td>
        <td></td>
    </tr>
    <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
    </tr>
    <tr class="event_settings">
        <td><?=$lg['TO']?>&nbsp;&nbsp;<input type="text" name="diffUntil1" id="diffUntil1" class="diffHeight number required" value="<?=formatResultOutput($settings['diff_1_until'])?>"> :</td>
        <td><input type="text" name="diffValue1" id="diffValue1" class="diffHeightDiff number required" value="<?=$settings['diff_1_value']?>"> <?=$lg['CENTIMETER_SHORT']?></td>
    </tr>
    <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
    </tr>
    <tr class="event_settings">
        <td><?=$lg['TO']?>&nbsp;&nbsp;<input type="text" name="diffUntil2" id="diffUntil2" class="diffHeight number required" value="<?=formatResultOutput($settings['diff_2_until'])?>"> :</td>
        <td><input type="text" name="diffValue2" id="diffValue2" class="diffHeightDiff number required" value="<?=$settings['diff_2_value']?>"> <?=$lg['CENTIMETER_SHORT']?></td>
    </tr>
    <tr>
        <td height="5" colspan="<?=$colspan?>"></td>
    </tr>
    <tr class="event_settings">
        <td><?=$lg['AFTERWARDS']?>:</td>
        <td><input type="text" name="diffValue3" id="diffValue3" class="diffHeightDiff number required" value="<?=$settings['diff_3_value']?>"> <?=$lg['CENTIMETER_SHORT']?></td>
    </tr>

</table>
</form>