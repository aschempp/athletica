<?php
/**********
 *
 *	meeting_definition_header.php
 *	-----------------------------
 *	
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_select.lib.php');

require('./lib/meeting.lib.php');
require('./lib/common.lib.php');


if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

//
// Process changes to meeting data
//

// change genereal meeting data
if ($_POST['arg']=="change")
{
	AA_meeting_changeData();
}

// Check if any error returned from DB
if(mysql_errno() > 0) {
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}


/***************************
 *
 *		General meeting data
 *
 ***************************/

$page = new GUI_Page('meeting_definition_header');
$page->startPage();
$page->printPageTitle("$strMeeting $strMeetingDefinition: " . $_COOKIE['meeting']);
$menu = new GUI_Menulist();
$menu->addButton('print_meeting_definition.php', $strPrint, '_blank');
$menu->addButton('meeting_definition_event_add.php', $strNewEvent . " ...", 'detail');
$menu->addButton('print_meeting_statistics.php?arg=view', $strStatistics, 'detail');
$menu->addButton('print_meeting_statistics.php?arg=print', $strPrintStatistics, '_blank');
$menu->addButton('print_timetable.php', $strPrintTimetable, '_blank');
$menu->addButton('print_timetable.php?arg=comp', $strPrintTimetableComp, '_blank');
$menu->addButton($cfgURLDocumentation . 'help/meeting/definition.html', $strHelp, '_blank');
$menu->printMenu();
?>

<script type="text/javascript">
<!--
	function check(item)	// stadium has changed; check what to do
	{
		if(item=='stadium')
		{
			if (document.change_def.stadium.value=='new') {	// new stadium
				window.open("admin_stadiums.php", "main");
			}
			else {
				document.change_def.submit();
			}
		}

		if(item=='saison')
		{
			document.change_def.submit();
		}
	}


//-->
</script>

<?php
// get meeting from DB
$result = mysql_query("
	SELECT xMeeting
		, Name
		, Ort
		, DatumVon
		, DatumBis
		, Nummer
		, ProgrammModus
		, xStadion
		, Online
		, Organisator
		, Startgeld
		, StartgeldReduktion
		, Haftgeld
		, Saison
	FROM meeting
	WHERE xMeeting=" . $_COOKIE['meeting_id']
);

if(mysql_errno() > 0)	// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else		// no DB error
{
	$row = mysql_fetch_array($result);
?>
<br>
<form action='meeting_definition_header.php' method='post' name='change_def'>
<input name='arg' type='hidden' value='change' />
<input name='item' type='hidden' value='<?php echo $row['xMeeting']; ?>' />
<table >
  <tr>
	<th class='dialog'><?php echo $strName; ?></th>
	<td class='forms'>
	  <input class='text' name='name' type='text'
			maxlength='60' value="<?php echo $row['Name']; ?>"
			onchange='document.change_def.submit()' />    </td>
	<td width="15"></td>
	<th class='dialog'><?php echo $strOrganizer ?></th>
	<td class='forms'>
	  <input style="width:98%;" type="text" name="organisator" value="<?php echo $row['Organisator'] ?>"
			onchange='document.change_def.submit()' />    </td>
	<td width="15" ></td>
	<th class='dialog' style="display:none">
	  <?php echo $strDeposit; ?>    </th>
	<td class='forms' style="display:none"><input name="deposit" type="text" class="currency"
			onchange='document.change_def.submit()' value="<?php echo ($row['Haftgeld']/100) ?>" size="10" /></td>
  </tr>
  <tr>
	<th class='dialog'><?php echo $strPlace; ?></th>
	<td class='forms'>
	  <input class='text' name='place' type='text'
			maxlength='20' value="<?php echo $row['Ort']; ?>"
			onchange='document.change_def.submit()' />    </td>
	<td></td>
	<th class='dialog'><?php echo $strStadium; ?></th>
	<?php if (1==0){ //damit wysiwig in dreamweaver funzt ?><td></td>
	<?php } else {
			$dd = new GUI_StadiumDropDown($row['xStadion']);
		}?>	
	<td></td>
	<th class='dialog' style="display:none">
	  <?php echo $strFee; ?>   
	 </th>
	<td class='forms' style="display:none">
	  <input name="fee" type="text"  class="currency"
			onchange='document.change_def.submit()' value="<?php echo ($row['Startgeld']/100) ?>" size="10" />
	</td>

  </tr>
  <tr>
	<th class='dialog'><?php echo $strDateFrom; ?></th>
	<td class='forms' align="left"><table border="0" cellspacing="0" cellpadding="0">
		<tr>
		  <td class='forms'><?php AA_meeting_printDate('from', $row['DatumVon'], TRUE); ?></td>
		</tr>
	  </table></td>
	<td></td>
	<th class='dialog'><?php echo $strDateTo; ?></th>
	<td class='forms' align="left"><table border="0" cellspacing="0" cellpadding="0">
		<tr>
		  <td class='forms'><?php AA_meeting_printDate('to', $row['DatumBis'], TRUE); ?></td>
		</tr>
	  </table>    </td>
	<td></td>
	<th  class="dialog" style="display:none">
	  <?php echo $strFeeReduction;?>    </th>
	<td class='forms' style="display:none">
	  <input name="feereduction" type="text"  class="currency"
			onchange='document.change_def.submit()' value="<?php echo ($row['StartgeldReduktion']/100) ?>" size="10" />
	</td>
  </tr>
  <tr>
	<th class='dialog'><?php echo $strSaison; ?></th>
	<?php if (1==0){ //damit wysiwig in dreamweaver funzt ?><td></td><?php } else {
		 $dd = new GUI_SeasonDropDown($row['Saison']); 
	}?>   
	<td>&nbsp;</td>
	<td></td>
	<td>&nbsp;</td>
	<td></td>
	<!--<th class='dialog'><?php echo $strMeetingWithUpload ?>:</th>
	<td>
	<?php
		if($row['Online'] == 'y'){
			$check = "checked";
		}
	?>
	<input type="checkbox" value="yes" name="online"
			onChange='document.change_def.submit()' <?php echo $check ?>>
	  <?php echo $strYes ?></td>-->
	  <td colspan="2"><input type="hidden" name="online" value="yes"/></td>
  </tr>
  <?php
  if ($row['Nummer'] > 0){
      $dis = "disabled='disabled'";
  }
  ?>
  <tr>
	<th class='dialog'><?php echo $strMeetingNbr; ?></th>
	<td class='forms'><input class='text' name='nbr' type='text'
			maxlength='20' value="<?php echo $row['Nummer']; ?>"
			onchange='document.change_def.submit()'  <?php echo $dis; ?>/></td>
	<td></td>
	<th class='dialog'><?php echo $strProgramMode; ?></th>
	<td class='forms'><?php
	$dropdown = new GUI_Select('mode', 1, "document.change_def.submit()");
	foreach($cfgProgramMode as $key=>$value)
	{
		$dropdown->addOption($value['name'], $key);
		if($row['ProgrammModus'] == $key) {
			$dropdown->selectOption($key);
		}
	}
	$dropdown->printList();
	?></td>
	<td></td>
	<td class='forms'>&nbsp;</td>
	<td class='forms'>&nbsp;</td>
  </tr>
  </table>

</form>

<?php
	mysql_free_result($result);
}		// ET DB error

$page->endPage();
?>