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
$serie = CFG_CURRENT_EVENT;
?>

<script type="text/javascript">
    $(document).ready(function(){
        $('img[name="delete_height"]').click(function(){
            var height_id = $(this).attr('xHeight');    
            
            $.ajax({
                url: '<?=$type?>/ajax_deleteHeight.php',
                type: 'POST',
                data: 'height_id='+height_id,
                success: function(data) {
                    if(data=='error') {
                       alert('<?=$lg['ERROR_DB']?>');
                    } else {
                        var url = 'high/dialog_heights.php';
                        $('#dialog_heights').load(url, function(response, status, req){ 
                            if(status == 'success'){
                                $('#newHeight').focus();
                            }
                        });
                    }
                }
            }); 
        });
        $('#newHeight').change(function(){
            var serie = '<?=$serie?>';
            var height = $(this).val();
            $.ajax({
                url: '<?=$type?>/ajax_insertHeight.php',
                type: 'POST',
                data: 'serie='+serie+'&height='+height,
                success: function(data) {
                    if(data=='error') {
                       alert('<?=$lg['ERROR_DB']?>');
                    } else {
                        var url = 'high/dialog_heights.php';
                        $('#dialog_heights').load(url, function(response, status, req){ 
                            if(status == 'success'){
                                $('#newHeight').focus();
                            }
                        });
                    }
                }
            }); 
               
            $('#newHeight').focus();
        });
    });
</script>
<?php
$heights = getHeights(CFG_CURRENT_EVENT);
$heightsPerRow = 10;
$i = 0;
?>
<table cellpadding="5">
    <?php
    foreach($heights as $height){
        if($i == $heightsPerRow){
            ?>
            </tr>
            <?php
            $i = 0;
        }
        if($i == 0){
            ?>
            <tr>
            <?php
        }
        ?>
        <td align="center" valign="middle"><?=formatResultOutput($height['height'])?> <img src="<?=ROOT_PATH?>img/icon_delete.png" name="delete_height" id="delete_height" xHeight="<?=$height['xHeight']?>" width="16" height="16" alt="icon_delete" style="cursor: pointer;"></td>
        <?php
        
        $i++;
    }
    if($i == $heightsPerRow){
        ?>
        </tr>
        <?php
        $i = 0;
    }
    if($i = 0){
        ?>
        <tr>
        <?php
    }
    ?>
    <td align="center"><input name="newHeight" id="newHeight" class="newHeight" value=""></td>
    </tr>
</table>