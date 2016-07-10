<?php

/**********
 *
 *	admin_clubs.php
 *	------------------
 *	This script accepts knows one default option:
 *		(default, no argument):	displays list of clubs, including methods
 *										to add or delete clubs
 */
 
$noMeetingCheck = true;

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');


require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}


//
// Process add or change request
//
if ($_POST['arg']=="add" || $_POST['arg']=="change")
{
	// Error: Empty fields
	if(empty($_POST['name']) || empty($_POST['sortvalue']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	else if ($_POST['arg']=="add")
	{
		mysql_query("
			INSERT INTO verein SET
				Name=\"" . $_POST['name'] . "\"
				, Sortierwert=\"" . $_POST['sortvalue'] . "\"
		");
	}
	// OK: try to change item
	else if ($_POST['arg']=="change")
	{
		mysql_query("
			UPDATE verein SET
				Name=\"" . $_POST['name'] . "\"
				, Sortierwert=\"" . $_POST['sortvalue'] . "\"
			WHERE xVerein=" . $_POST['item']
		);
	}
}
//
// Process delete request
//
else if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES athlet READ, staffel READ, team READ, verein WRITE");

	// Still in use?
	$rows = AA_checkReference("athlet", "xVerein", $_GET['item']);
	$rows = $rows + AA_checkReference("staffel", "xVerein", $_GET['item']);
	$rows = $rows + AA_checkReference("team", "xVerein", $_GET['item']);

	// OK: not used anymore
	if($rows == 0)
	{
		mysql_query("DELETE FROM verein WHERE xVerein=" . $_GET['item']);
	}
	// Error: still in use
	else
	{
		AA_printErrorMsg($strClub . $strErrStillUsed);
	}
	mysql_query("UNLOCK TABLES");
}

// Check if any error returned from DB
if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

//
// Display current data
//

$page = new GUI_Page('admin_clubs', TRUE);
$page->startPage();
$page->printPageTitle($strClubs);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/clubs.html', $strHelp, '_blank');
$menu->printMenu();

// sort argument
$img_club="img/sort_inact.gif";
$img_sort="img/sort_inact.gif";

if ($_GET['arg']=="club") {
	$argument="Name";
	$img_club="img/sort_act.gif";
} else if ($_GET['arg']=="sort") {
	$argument="Sortierwert";
	$img_sort="img/sort_act.gif";
} else {							// relay event
	$argument="Sortierwert";
	$img_sort="img/sort_act.gif";
}


?>
<p/>
<table class='dialog'>
	<tr>
		<th class='dialog'>
			<a href='admin_clubs.php?arg=club'>
				<?php echo $strName; ?>
				<img src='<?php echo $img_club; ?>'>
			</a>
		</th>
		<th class='dialog'>
			<a href='admin_clubs.php?arg=sort'>
				<?php echo $strSortValue; ?>
				<img src='<?php echo $img_sort; ?>'>
			</a>
		</th>
	</tr>
	<tr>
		<form action='admin_clubs.php' method='post'>
		<td class='forms'>
			<input name='arg' type='hidden' value='add'>
			<input class='text' name='name' type='text' maxlength='30'
				value="(<?php echo $strNew; ?>)" ></td>
		<td class='forms'>
			<input class='text' name='sortvalue' type='text' maxlength='30'
				value='' ></td>
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
		xVerein
		, Name
		, Sortierwert
	FROM
		verein
	ORDER BY
		$argument
");

// each row
$l = 0;
$btn = new GUI_Button('', '');

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
			<form action='admin_clubs.php#item_<?php echo $row[0]; ?>'
				method='post' name='club<?php echo $i; ?>'>
			<td class='forms'>
				<input name='arg' type='hidden' value='change'>
				<input name='item' type='hidden' value='<?php echo $row[0]; ?>'>
				<input class='text' name='name' type='text' maxlength='30'
					value="<?php echo $row[1]; ?>"
					onChange='submitForm(document.club<?php echo $i; ?>)'>
			</td>
			<td class='forms'>
				<input class='text' name='sortvalue' type='text' maxlength='30'
					value="<?php echo $row[2]; ?>"
					onChange='submitForm(document.club<?php echo $i; ?>)'>
			</td>
			<td>
<?php
	$btn->set("admin_clubs.php?arg=del&item=$row[0]", $strDelete);
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

