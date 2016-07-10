<?php

/**********
 *
 *	speaker_rankinglists.php
 *	------------------------
 *	
 */

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$page = new GUI_Page('speaker_rankinglists');
$page->startPage();
$page->printPageTitle($strRankingLists . ": " . $_COOKIE['meeting']);
$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/speaker/rankinglists.html', $strHelp, '_blank');
$menu->printMenu();

?>
<script type="text/javascript">
<!--   
    
    function checkDisc() 
    {  
       e = document.getElementById("combined"); 
       e.checked=true; 
    }
    
   
//-->
</script>
<?php

// get presets
$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}
else if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}

$presets = AA_results_getPresets($round);

?>
<table><tr>
	<td class='forms'>
		<?php	AA_printCategorySelection('speaker_rankinglists.php'
			, $presets['category'], 'post'); ?>
	</td>
	<td class='forms'>
		<?php	AA_printEventSelection('speaker_rankinglists.php'
			, $presets['category'], $presets['event'], 'post'); ?>
	</td>
<?php
if($presets['event'] > 0) {		// event selected
?>
	<td class='forms'>
		<?php AA_printRoundSelection('speaker_rankinglists.php'
			, $presets['category'] , $presets['event'], $round); ?>
	</td>
<?php
}
?>

<form action='print_rankinglist.php' method='get' name='printdialog'>

<input type='hidden' name='category' value='<?php echo $presets['category']; ?>'>
<input type='hidden' name='event' value='<?php echo $presets['event']; ?>'>
<input type='hidden' name='round' value='<?php echo $round; ?>'>
<input type='hidden' name='formaction' value='view'>

<?php
// Rankginglists for club and combined-events
if(empty($presets['event']))	// no event selected
{
	
$eventTypeCat = AA_getEventTypesCat();	
	
?>
<table class='dialog'>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='single' checked>
			<?php echo $strSingleEvent; ?></input>
	</th>
</tr>

<?php if (isset($eventTypeCat['combined'])){?>
<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='combined' id='combined'>
			<?php echo $strCombinedEvent; ?></input>
	</th>
</tr>
<tr> 
    <td class='dialog'>&nbsp;&nbsp;&nbsp;&nbsp;Disziplin: 1 bis   
            <select name='disc_nr' onchange='checkDisc()'>
                <option value="99">99</option>
                <?php
                for ($i=1;$i<=99;$i++){
                    ?>
                    <option value="<?php echo $i?>"><?php echo $i?></option>
                    <?php
                }
                ?>
            </select>
        </td>
</tr>
</table>
  <p />   
     <table>
<tr>
    <th class='dialog'>
        <input type='radio' name='type' value='ukc' id='combined' >
             <?php echo $strRankingList . " " . $strUKC; ?></input>   
    </th>
</tr>
</table>



<?php } ?>

<?php if (isset($eventTypeCat['club'])){?>

<tr>
	<th class='dialog'>
		<input type='radio' name='type' value='team'>
			<?php echo $strClubEvent; ?></input>
	</th>
</tr>

<tr>
	<td class='dialog_sub'>
		<input type='radio' name='team' value='ranking' checked>
			<?php echo $strClubRanking; ?></input>
	</td>
</tr>

<tr>
	<td class='dialog_sub'>
		<input type='radio' name='team' value='sheets'>
			<?php echo $strClubSheets; ?></input>
	</td>
</tr>
<?php  } ?>
</table>

<?php
}	// ET event selected
?>

<p/>

<table>
<tr>
	<td>
		<button type="submit">
			<?php echo $strShow; ?>
		</button>
		<button type="submit" name="show_efforts" value="sb_pb">
			<?php echo $strRankingListEfforts; ?>
		</button>
	</td>

</tr>
</table>

</form>
<?php
	
$page->endPage();

