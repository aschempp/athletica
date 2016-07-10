<?php

/**********
 *
 *	admin_installations.php
 *	-----------------------
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

// preload stadium params, either from GET or POST
$stadium_id = $_GET['stadium_id'];

if(!empty($_POST['stadium_id'])) {
	$stadium_id = $_POST['stadium_id'];
}

$back = '';
if(!empty($_GET['back'])) {
	$back = $_GET['back'];
}
if(!empty($_POST['back'])) {
	$back = $_POST['back'];
}


//
// Process add or change-request if required
//
if ($_POST['arg']=="add" || $_POST['arg']=="change")
{
	// Error: Empty fields
	if(empty($_POST['name']) || empty($stadium_id))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to add item
	else
	{
		mysql_query("LOCK TABLES stadion READ, anlage WRITE");
		// check if club is valid
		$result = mysql_query("SELECT xStadion FROM stadion WHERE xStadion=" . $_POST['stadium_id']);
		$rows = mysql_num_rows($result);
		mysql_free_result($result);
		
		if($_POST['nothomo'] == 'yes'){ $homo = 'n'; }else{ $homo = 'y'; }
		
		if($rows == 0)		// Stadium does not exist
		{
			AA_printErrorMsg($strStadium . $strErrNotValid);
		}
		else if ($_POST['arg']=="add")
		{
			mysql_query("
				INSERT INTO anlage SET 
					Bezeichnung=\"" . ($_POST['name']) . "\"
					, xStadion = $stadium_id
					, Homologiert = '$homo'
			");
		}
		else if ($_POST['arg']=="change")
		{
			mysql_query("
				UPDATE anlage SET 
					Bezeichnung=\"" . ($_POST['name']) . "\"
					, xStadion = $stadium_id
					, Homologiert = '$homo'
				WHERE xAnlage=" . $_POST['item']
			);
		}

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}
}
//
// Process delete-request if required
//
else if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES serie READ, anlage WRITE");

	// Check if not used anymore
	if(AA_checkReference("serie", "xAnlage", $_GET['item']) == 0)
	{
		mysql_query("DELETE FROM anlage WHERE xAnlage=" . $_GET['item']);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}
	// Error: still in use
	else
	{
		AA_printErrorMsg($strInstallation . $strErrStillUsed);
	}
	mysql_query("UNLOCK TABLES");
}

// get stadium name
$result = mysql_query("
	SELECT
		Name
	FROM
		stadion
	WHERE xStadion = $stadium_id
");

if(mysql_errno() > 0) {
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	$stadium_name = "";
}
else {
	$row = mysql_fetch_row($result);
	$stadium_name = $row[0];
	mysql_free_result($result);
}


// get installations from DB

$page = new GUI_Page('admin_installations', TRUE);
$page->startPage();
$page->printPageTitle($strInstallations . ", " . $stadium_name);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/installations.html', $strHelp, '_blank');
$menu->printMenu();
?>
<p/>
<table class='dialog'>
	<tr>
		<th class='dialog'><?php echo $strInstallation; ?></th>
		<th class='dialog' title='<?php echo $strNotHomo; ?>'>o</th>
	</tr>
	<tr>
		<form action='admin_installations.php' method='post'>
		<td class='forms'>
			<input name='stadium_id' type='hidden'
				value='<?php echo $stadium_id; ?>' />
			<input name='arg' type='hidden' value='add' />
			<input class='text' name='name' type='text' maxlength='20'
				value="(<?php echo $strNew; ?>)" ></td>
		<td class='forms'>
			<input type="checkbox" name="nothomo" value="yes">
		</td>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
		  	</button>
		</td>

		</form>	
	</tr>

<?php

// get installations from DB
$result = mysql_query("
	SELECT
		xAnlage
		, Bezeichnung
		, xStadion
		, Homologiert
	FROM
		anlage
	WHERE xStadion = $stadium_id
	ORDER BY
		Bezeichnung
");

if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

// display list
$l = 0;
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
		<form action='admin_installations.php#item_<?php echo $row[0]; ?>'
			method='post' name='inst<?php echo $i; ?>'>
		<td class='forms'>
			<input name='arg' type='hidden' value='change'>
			<input name='item' type='hidden' value='<?php echo $row[0]; ?>'>
			<input name='stadium_id' type='hidden'
				value='<?php echo $row[2]; ?>'>
			<input class='text' name='name' type='text' maxlength='30'
				value="<?php echo $row[1]; ?>"
				onChange='submitForm(document.inst<?php echo $i; ?>)'>
		</td>
		<td class='forms'>
			<input type="checkbox" name="nothomo" value="yes" 
			onchange="submitForm(document.inst<?php echo $i; ?>)" <?php if($row[3] == 'n'){ echo "checked"; } ?>>
		</td>
		<td>
	<?php
	$btn->set("admin_installations.php?arg=del&item=$row[0]&stadium_id=$row[2]", $strDelete);
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

