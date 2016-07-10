<?php

/**********
 *
 *	speaker_results_new.php
 *	-------------------
 *
 */

require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_menulist.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');
require('./lib/speaker_results_high.lib.php');
require('./lib/speaker_results_tech.lib.php');
require('./lib/speaker_results_track.lib.php');


if(AA_connectToDB() == FALSE)	{			// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}
else if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}

$presets = AA_results_getPresets($round);	// read GET/POST variables

//
// print HTML page header
//
$page = new GUI_Page('speaker_results');
$page->startPage();
$page->printPageTitle($strResults. ": " . $_COOKIE['meeting']);
$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/speaker/results.html', $strHelp, '_blank');
$menu->addSearchfield('speaker_entry.php', '_self', 'post', 'speaker_results.php?round=' . $round);
$menu->printMenu();

//
// print round selection menu
//
?>
<p/>

<table><tr>
	<td class='forms'>
		<?php	AA_printCategorySelection("speaker_results.php", $presets['category']); ?>
	</td>
	<td class='forms'>
		<?php	AA_printEventSelection("speaker_results.php", $presets['category'], $presets['event'], "post"); ?>
	</td>

<?php
	if($presets['event'] > 0)		// event selected
	{
		printf("<td class='forms'>\n");
		AA_printRoundSelection("speaker_results.php", $presets['category'], $presets['event'], $round);
		printf("</td>\n");
	}
	if($round > 0)		// round selected
	{
        $layout = AA_getDisciplineType($round);    // type determines layout
        
		printf("<td class='forms'>\n");
		AA_printHeatSelection($round, true);
		printf("</td>\n");
        
    ?>
        <td>
        <button type="button" onclick="window.open('print_contest_speaker.php?round=<?=$round?>')"><?=$strStartlist?></button>
</td>
<?php
/*
    if(($layout == $cfgDisciplineType[$strDiscTypeThrow])
        || ($layout == $cfgDisciplineType[$strDiscTypeJump])
        || ($layout == $cfgDisciplineType[$strDiscTypeJumpNoWind]))
    {
?>
<td>
        <button type="button" onclick="window.open('print_contest_speaker.php?round=<?=$round?>&final=1')"><?=$strStartlistFinal?></button>
</td>
    <?php
    }
    */
	}
?>

</tr>
</table>
<?php

//
//	form layout (depending on discipline type)
//
if($round > 0)
{
	// track disciplines, with or without wind
	if(($layout == $cfgDisciplineType[$strDiscTypeNone])
		|| ($layout == $cfgDisciplineType[$strDiscTypeTrack])
		|| ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
		|| ($layout == $cfgDisciplineType[$strDiscTypeDistance])
		|| ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
	{
		AA_speaker_Track($presets['event'], $round, $layout);
	}
	// technical disciplines, with or withour wind
	else if(($layout == $cfgDisciplineType[$strDiscTypeThrow])
		|| ($layout == $cfgDisciplineType[$strDiscTypeJump])
		|| ($layout == $cfgDisciplineType[$strDiscTypeJumpNoWind]))
	{
		AA_speaker_Tech($presets['event'], $round, $layout);
	}
	// high jump, pole vault
	else if($layout == $cfgDisciplineType[$strDiscTypeHigh])
	{
		AA_speaker_High($presets['event'], $round, $layout);
	}
}
