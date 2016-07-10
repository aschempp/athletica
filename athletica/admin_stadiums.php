<?php

/**********
 *
 *	admin_stadium.php
 *	------------------
 *	This script accepts no arguments
 *		(default, no argument):	displays list of stadiums, including methods
 *										to add or delete stadiums
 *
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');


if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}


//
// Process add or change-request if required
//
if ($_POST['arg']=="add" || $_POST['arg']=="change")
{
	// Error: Empty fields
	
	if($_POST['thousend'] == 'yes'){ $o1000m = 'y'; }else{ $o1000m = 'n'; }
	if($_POST['hall'] == 'yes'){ $inhall = 'y'; }else{ $inhall = 'n'; }
	
	if(empty($_POST['name']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to add item
	else if ($_POST['arg']=="add")
	{
		mysql_query("
			INSERT INTO stadion SET 
				Name=\"" . $_POST['name'] . "\"
				, Bahnen=\"" . $_POST['tracks'] . "\"
				, BahnenGerade=\"".$_POST['tracks2']."\"
				, Ueber1000m=\"$o1000m\"
				, Halle=\"$inhall\"
		");
	}
	// OK: try to change item
	else if ($_POST['arg']=="change")
	{
		mysql_query("
			UPDATE stadion SET 
				Name=\"" . $_POST['name'] . "\"
				, Bahnen=" . $_POST['tracks'] . "
				, BahnenGerade=\"".$_POST['tracks2']."\"
				, Ueber1000m=\"$o1000m\"
				, Halle=\"$inhall\"
			WHERE xStadion=" . $_POST['item']
		);
	}
}
//
// Process delete-request if required
//
else if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES anlage READ, meeting READ, stadion WRITE");

	// Still in use?
	$rows = AA_checkReference("anlage", "xStadion", $_GET['item']);
	$rows = $rows + AA_checkReference("meeting", "xStadion", $_GET['item']);

	// OK: not used anymore
	if($rows == 0)
	{
		mysql_query("DELETE FROM stadion WHERE xStadion=" . $_GET['item']);
	}
	// Error: still in use
	else
	{
		AA_printErrorMsg($strStadium . $strErrStillUsed);
	}
	mysql_query("UNLOCK TABLES");
}

// Check if any error returned from DB
if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

//
//	Display current data
//

$page = new GUI_Page('admin_stadiums', TRUE);
$page->startPage();
$page->printPageTitle($strStadium);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/stadiums.html', $strHelp, '_blank');
$menu->printMenu();
?>
<p/>
<table class='dialog'>
	<tr>
		<th class='dialog'><?php echo $strStadium; ?></th>
		<th class='dialog'><?php echo $strTracks; ?></th>
		<th class='dialog'><?php echo $strTracks." ".$strStraight; ?></th>
		<th class='dialog' title='<?php echo $strOver1000m; ?>'>A</th>  
	</tr>
	<tr>
		<form action='admin_stadiums.php' method='post'>
		<td class='forms'>
			<input name='arg' type='hidden' value='add'>
			<input class='text' name='name' type='text' maxlength='50'
				value="(<?php echo $strNew; ?>)" ></td>
<?php
$dd = new GUI_ConfigDropDown('tracks', 'cfgTrackOrder', 0, '', true);
$dd = new GUI_ConfigDropDown('tracks2', 'cfgTrackOrder', 0, '', true);
?>
		<td class='forms'>
			<input type="checkbox" value="yes" name="thousend" title='<?php echo $strOnlyOver1000m; ?>'>
		</td>
		
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
			</button>
		</td>

		</form>	
	</tr>

<?php
$result = mysql_query("
	SELECT
		xStadion
		, Name
		, Bahnen
		, BahnenGerade
		, Ueber1000m
		, Halle
	FROM
		stadion
	ORDER BY
		Name
");

$i = 0;
$btn = new GUI_Button('', '');	// create button object

while ($row = mysql_fetch_row($result))
{
	$i++;		// line counter

	if( $i % 2 == 0 ) {		// even row number
		$rowclass = 'odd';
	}
	else {	// odd row number
		$rowclass = 'even';
	}
	?>
	<tr class='<?php echo $rowclass; ?>'>
		<form action='admin_stadiums.php#item_<?php echo $row[0]; ?>'
			method='post' name='stad<?php echo $i; ?>'>
		<td class='forms'>
			<input name='arg' type='hidden' value='change'>
			<input name='item' type='hidden' value='<?php echo $row[0]; ?>'>
			<input class='text' name='name' type='text' maxlength='50'
				value="<?php echo $row[1]; ?>"
				onChange='submitForm(document.stad<?php echo $i; ?>)'>
		</td>
	<?php
	$dd = new GUI_ConfigDropDown('tracks', 'cfgTrackOrder', $row[2], "submitForm(document.stad$i)", true);
	$dd = new GUI_ConfigDropDown('tracks2', 'cfgTrackOrder', $row[3], "submitForm(document.stad$i)", true);
	?>
		<td class='forms'>
			<input type="checkbox" name="thousend" value="yes" title='<?php echo $strOnlyOver1000m; ?>'
			onchange="submitForm(document.stad<?php echo $i ?>)" <?php if($row[4] == 'y'){ echo "checked"; } ?>>
		</td> 		
		<td>
	<?php
	$btn->set("admin_stadiums.php?arg=del&item=$row[0]", $strDelete);
	$btn->printButton();
	?>
		</td>
		<td>
	<?php
	$btn->set("admin_installations.php?stadium_id=$row[0]", $strInstallations . " ...");
	$btn->printButton();
	?>
		</td>
		</form>
	</tr>
<?php
}

mysql_free_result($result);
?>

</table>

<script type="text/javascript">
<!--
	scrollDown();
//-->
</script>

<?php

$page->endPage();

