<?php

/**********
 *
 *	dlg_startnumbers.php
 *	--------------------
 *	
 */

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

$page = new GUI_Page('dlg_startnumbers');
$page->startPage();
$page->printPageTitle($strAssignStartnumbers);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/meeting/startnumbers.html', $strHelp, '_blank');
$menu->printMenu();
?>

<form action='meeting_entries.php' method='get'>
<input type='hidden' name='arg' value='assign_startnbrs'>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strSortBy; ?></th>
	<th class='dialog'><?php echo $strRules; ?></th>
</tr>
<tr>
	<td class='dialog'>
		<input type='radio' name='sort' value='name' checked>
			<?php echo $strName; ?></input>
	</td>
	<td class='dialog'>
		<?php echo $strBeginningWith; ?>
	</td>
	<td>
		<input class='nbr' type='text' name='start' maxlength='5' value='<?php echo $cfgNbrStartWith; ?>'>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='radio' name='sort' value='cat'>
			<?php echo $strCategory; ?></input>
	</td>
	<td colspan='2'>
		<?php echo $strGapBetween; ?>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='radio' name='sort' value='club'>
			<?php echo $strClub; ?></input>
	</td>
	<td class='dialog'>
		<?php echo $strCategory; ?>
	</td>
	<td class='dialog'>
		<input class='nbr' type='text' name='catgap' maxlength='4' value='<?php echo $cfgNbrCategoryGap; ?>'>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='radio' name='sort' value='club_cat'>
			<?php echo $strClub . " & " . $strCategory; ?></input>
	</td>
	<td class='dialog'>
		<?php echo $strClub; ?>
	</td>
	<td class='dialog'>
		<input class='nbr' type='text' name='clubgap' maxlength='4' value='<?php echo $cfgNbrClubGap; ?>'>
	</td>
</tr>
<tr>
	<td class='dialog' colspan = 3>
		<hr>
		<input type='radio' name='sort' value='del'>
			<?php echo $strDeleteStartnumbers; ?></input>
	</td>
</tr>

</table>

<p />

<table>
<tr>
	<td>
		<button type='reset'
			onClick='window.open("meeting_entries.php", "main")'>
			<?php echo $strCancel; ?>
	  	</button>
	</td>
	<td>
		<button type='submit'>
			<?php echo $strAssign; ?>
	  	</button>
	</td>
</tr>
</table>

</form>

</body>
</html>

