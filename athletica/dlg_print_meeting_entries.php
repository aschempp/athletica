<?php

/**********
 *
 *	dlg_print_meeting_entries.php
 *	-----------------------------
 *	
 */

require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$page = new GUI_Page('dlg_print_meeting_entries');
$page->startPage();
$page->printPageTitle($strPrint . ": " . $strEntries);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/meeting/print_entries.html', $strHelp, '_blank');
$menu->printMenu();

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

<form action='print_meeting_entries.php' method='get' name='printdialog'>
<input type='hidden' name='formaction' value=''>

<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strGroupBy; ?></th>
	<th class='dialog' colspan='2'><?php echo $strPageBreak; ?></th>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='clubgroup' value='yes'>
			<?php echo $strClubs; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='clubbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='clubbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='catgroup' value='yes'>
			<?php echo $strCategories; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='catbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='catbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='discgroup' value='yes'>
			<?php echo $strDisciplines; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='discbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='discbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>

<tr>
	<td class='dialog' colspan='3'>
		<hr>
	</td>
</tr>

<tr>
	<th class='dialog' colspan='3'><?php echo $strSortBy; ?></th>
</tr>
<tr>
	<td class='dialog' colspan='3'>
		<input type='radio' name='sort' value='name' checked>
			<?php echo $strName; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog' colspan='3'>
		<input type='radio' name='sort' value='nbr'>
			<?php echo $strStartnumbers; ?></input>
	</td>
</tr>

<tr>
	<td class='dialog' colspan='3'>
		<hr>
	</td>
</tr>

<tr>
	<th class='dialog' colspan='3'><?php echo $strLimitSelection; ?></th>
</tr>
<tr>
	<td colspan='3'>
		<table>
			<tr>
				<td class='dialog'><?php echo $strCategory; ?>:</td>
				<?php
				$dd = new GUI_CategoryDropDown(0);
				?>
			</tr>
			<tr>
				<td class='dialog'><?php echo $strDiscipline; ?>:</td>
				<?php
				$dd = new GUI_DisciplineDropDown();
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
	<td>
		<button name='reset' type='reset' onClick='window.open("meeting_entries.php", "main")'>
			<?php echo $strCancel; ?>
		</button>
	</td>
</tr>
</table>

</form>

<?php
$page->endPage();

