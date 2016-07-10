<?php

/**********
 *
 *	meeting_teams_print.php
 *	-----------------------
 *	
 */

require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$page = new GUI_Page('dlg_print_meeting_teams');
$page->startPage();
$page->printPageTitle($strPrint);

?>
<script type="text/javascript">
<!--
	function setPrint()
	{
		document.printdialog.formaction.value = 'print'
		document.printdialog.target = '_blank';
	}

	function setView()
	{
		document.printdialog.formaction.value = 'view'
		document.printdialog.target = '';
	}
//-->
</script>

<form action='print_meeting_teams.php' method='get' name='printdialog'>
<input type='hidden' name='formaction' value=''>

<table class='dialog'>
<tr>
	<td class='dialog'>
		<input type='radio' name='list' value='team' checked>
			<?php echo $strTeamList; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='radio' name='list' value='disc'>
			<?php echo $strDisciplineList; ?></input>
	</td>
</tr>

<tr><th class='dialog'><?php echo $strPageBreak; ?></th></tr>

<tr><td class='dialog'>
		<input type='radio' name='break' value='team' checked>
			<?php echo $strTeam; ?></input>
</td></tr>

<tr><td class='dialog'>
		<input type='radio' name='break' value='none'>
			<?php echo $strNoPageBreak; ?></input>
</td></tr>

<tr>
	<th class='dialog'><?php echo $strLimitSelection; ?></th>
</tr>
<tr>
	<td>
		<table>
			<tr>
				<td class='dialog'><?php echo $strCategory; ?>:</td>
				<?php
				$dd = new GUI_CategoryDropDown(0);
				?>
			</tr>
			<tr>
				<td class='dialog'><?php echo $strClub; ?></td>
				<?php
				$dd = new GUI_ClubDropDown(0);
				?>
			</tr>
		</table>
	</td>
</tr>

<tr>
	<td class='dialog' colspan='2'>
		<hr>
	</td>
</tr>

<tr>
	<td class='dialog' colspan='2'>
		<input type='checkbox' name='cover' value='cover'>
			<?php echo $strCover; ?></input>
	</td>
</tr>
</table>

<p />

<table>
<tr>
	<td>
		<button name='print' type='submit' onClick='setPrint()'>
			<?php echo $strPrint; ?>
		</button>
	</td>
	<td>
		<button name='view' type='submit' onClick='setView()'>
			<?php echo $strShow; ?>
		</button>
	</td>
</tr>
</table>

</form>

<?php
$page->endPage();

