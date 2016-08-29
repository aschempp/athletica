<?php

/**********
 *
 *	speaker.php
 *	-----------
 *	
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_searchfield.lib.php');

require('./lib/common.lib.php');
require('./lib/timetable.lib.php');

if(AA_connectToDB() == FALSE) {	// invalid DB connection
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$now = getdate();
$zero = '';
if($now['minutes'] < 10) {
	$zero = '0';
}

$timestamp = $now['mday']
				. "." . $now['mon']
				. "." . $now['year']
				. ", " . $now['hours']
				. "." . $zero. $now['minutes'];

$page = new GUI_Page('speaker');
$page->startPage();
$page->printPageTitle($strSpeakerMonitor . " " . $_COOKIE['meeting'] . " ($timestamp Uhr)");

$hlpbtn = new GUI_Button($cfgURLDocumentation . 'help/speaker/index.html', $strHelp, '_blank');
?>

<table>
<tr>
	<th class='dialog' rowspan='2'>
		<?php echo $strStatus; ?>:
	</th>
	<td class='forms'>
		<div class='st_heats_work'>&nbsp;<?php echo $strHeatsInWork; ?>&nbsp;</div>
	</td>
	<td class='forms'>
		<div class='st_res_work'>&nbsp;<?php echo $strResultsInWork; ?>&nbsp;</div>
	</td>
	<td class='forms'>
		<div class='st_anct_pend'>&nbsp;<?php echo $strResultAnnouncement; ?>&nbsp;</div>
	</td>
	<td class='forms'>
		<div class='st_crmny_done'>&nbsp;<?php echo $strCeremonyDone; ?>&nbsp;</div>
	</td>
	<td class='forms'>
	<?php $hlpbtn->printButton(); ?>
	</td>
</tr>

<tr>
	<td class='forms'>
		<div class='st_heats_done'>&nbsp;<?php echo $strHeatsDone; ?>&nbsp;</div>
	</td>
	<td class='forms'>
		<div class='st_res_live'>&nbsp;<?php echo $strResultsLive; ?>&nbsp;</div>
	</td>
	<td class='forms'>
		<div class='st_anct_done'>&nbsp;<?php echo $strResultsAnnounced; ?>&nbsp;</div>
	</td>
	<td class='forms' />
</tr>
<tr>
    <td class='forms' />
    <td class='forms' /> 
    <td class='forms' />
        <div class='st_res_done'>&nbsp;<?php echo $strResultsDone; ?>&nbsp;</div>   
    </td> 
    <td class='forms' />  
</tr>
</table>

<p />
<?php
$search = new GUI_Searchfield('speaker_entry.php', '_self', 'post', 'speaker.php');
$search->printSearchfield();
?>
<p />
<?php AA_timetable_display('speaker');

    $hour= date("H");   
        
   
    ?>  
<script type="text/javascript">
<!--
	window.setTimeout("updatePage()", <?php echo $cfgMonitorReload * 1000; ?>);

    <?php
        for ($h = 0; $h <= $hour; $h++) {
            if ($h < 10) {
                $dateHour = date("Y-m-d") . "0" . $h;     
            } else {
                $dateHour = date("Y-m-d") . $h;   
            }
                
            ?>
	        // scroll to put current time - 2 hours line approximately to the top of the screen     	                             
	        if(document.getElementById('<?php echo $dateHour; ?>'))  {   
		        document.getElementById('<?php echo $dateHour; ?>').scrollIntoView("true");   
	        }
            <?php
        }
        ?>

	function updatePage()
	{
		window.open("speaker.php", "main");
	}

	//-->
</script>

<?php

$page->endPage();

