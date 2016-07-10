<?php

/**********
 *
 *	meeting_entries_payment_print.php
 *	---------------------------------
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

$page = new GUI_Page('meeting_entries_print');
$page->startPage();
$page->printPageTitle($strPaymentControl);

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
	
	function setExport()
	{
		document.printdialog.formaction.value = 'export'
		document.printdialog.target = '';
	}
//-->
</script>

<form action='print_meeting_entries_payment.php' method='get' name='printdialog'>
<input type='hidden' name='formaction' value=''>

<table class='dialog'>
			
            <tr>
                <th class='dialog'><?php echo $strClub; ?></th>
                <?php
               $dd = new GUI_ClubDropDown(0);
                
                ?>
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

<br>


</form>

<?php
$page->endPage();
?>
