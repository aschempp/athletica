<?php

/**********
*
*	admin_athletes.php
*	------------------
*	
*/

$noMeetingCheck = true;

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE) {		// invalid DB connection
	return;		// abort
}

// correct two-digit year
if(!empty($_POST['year'])) {
	$_POST['year'] = AA_setYearOfBirth($_POST['year']);
}


//
// Process add request
//
if ($_POST['arg']=="add")
{
	// Error: Empty fields
	if(empty($_POST['name']) || empty($_POST['first']) || empty($_POST['club']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to add item
	else
	{
		mysql_query("LOCK TABLES verein READ, athlet WRITE");
		// check if club is valid
		if(AA_checkReference("verein", "xVerein", $_POST['club']) == 0)	// Club does not exist (anymore)
		{
			AA_printErrorMsg($strClub . $strErrNotValid);
		}
		// OK to add
		else	
		{
			$lic = ($_POST['license']!='' && is_numeric($_POST['license'])) ? $_POST['license'] : 0;
			mysql_query("
				INSERT INTO athlet SET 
					Name=\"" . ($_POST['name']) . "\"
					, Vorname=\"" . $_POST['first'] . "\"
					, Jahrgang='" . $_POST['year'] . "'
					, xVerein='" . $_POST['club'] . "'
					, Lizenznummer = '".$lic."'
			");
			// Check if any error returned from DB
			if(mysql_errno() > 0) {
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else {
				$_POST['item'] = mysql_insert_id();	// redisplay new athlete
			}
		}
		mysql_query("UNLOCK TABLES");
	}
}


//
// Process change request
//
else if ($_POST['arg']=="change")
{
	// Error: Empty fields
	if(empty($_POST['name']) || empty($_POST['first']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to change item
	else
	{
		$lic = ($_POST['license']!='' && is_numeric($_POST['license'])) ? $_POST['license'] : 0;
		
		mysql_query("LOCK TABLES athlet WRITE");

		mysql_query("
			UPDATE athlet SET
				Name=\"" . ($_POST['name']) . "\"
				, Vorname=\"" . $_POST['first'] . "\"
				, Jahrgang='" . $_POST['year'] . "'
				, Lizenznummer = '".$lic."'
			WHERE xAthlet=" . $_POST['item']);
		// Check if any error returned from DB
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}
}

//
// Process change request
//
else if ($_POST['arg']=="change_club")
{
	// Error: Empty fields
	if(empty($_POST['club']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK: try to change item
	else
	{
		mysql_query("LOCK TABLES anmeldung READ, staffelathlet READ,"
			. " verein READ, athlet WRITE");

		// Still in use?
		$rows = AA_checkReference("anmeldung", "xAthlet", $_POST['item']);

		// OK: not used anymore
		if($rows == 0)
		{
			// check if club is valid
			if(AA_checkReference("verein", "xVerein", $_POST['club']) == 0)	// Club does not exist (anymore)
			{
				AA_printErrorMsg($strClub . $strErrNotValid);
			}
			else
			{
				mysql_query("UPDATE athlet SET "
							 . "xVerein='" . $_POST['club']
							 . "' WHERE xAthlet=" . $_POST['item']);
				// Check if any error returned from DB
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
			}
		}
		// Error: still in use
		else
		{
			AA_printErrorMsg($strErrClubChange);
		}
		mysql_query("UNLOCK TABLES");
	}
}


//
// Process delete-request if required
//
else if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES anmeldung READ, staffelathlet READ, athlet WRITE");

	// Still in use?
	$rows = AA_checkReference("anmeldung", "xAthlet", $_GET['item']);
	// OK: not used anymore
	if($rows == 0)
	{
		mysql_query("DELETE FROM athlet WHERE xAthlet=" . $_GET['item']);

		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}
	// Error: still in use
	else
	{
		AA_printErrorMsg($strAthlete . $strErrStillUsed);
	}
	mysql_query("UNLOCK TABLES");
}


//
// Full display
// ------------
//

$page = new GUI_Page('admin_athletes', TRUE);
$page->startPage();
$page->printPageTitle($strAthletes);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/athletes.html', $strHelp, '_blank');
$menu->printMenu();

// sort argument
$img_name="img/sort_inact.gif";
$img_year="img/sort_inact.gif";
$img_license="img/sort_inact.gif";
$img_club="img/sort_inact.gif";

if ($_GET['arg']=="name") {
	$argument="a.Name, a.Vorname";
	$img_name="img/sort_act.gif";
} else if ($_GET['arg']=="year") {
	$argument="a.Jahrgang, a.Name, a.Vorname";
	$img_year="img/sort_act.gif";
} else if ($_GET['arg']=="license") {
	$argument="a.Lizenznummer, a.Name, a.Vorname";
	$img_license="img/sort_act.gif";
} else if ($_GET['arg']=="club") {
	$argument="v.Sortierwert, a.Name, a.Vorname";
	$img_club="img/sort_act.gif";
} else {							// relay event
	$argument="a.Name, a.Vorname";
	$img_name="img/sort_act.gif";
}

?>
<script type="text/javascript">
<!--
	function selectAthlete(item)
	{
		document.selection.item.value=item;
		submitForm(document.selection);
	}

	function check()
	{
		if(document.add_athlete.club.value == 'new')
		{
			window.open("admin_clubs.php", "_self");
		}
	}

	function checkNew()
	{
		if(document.change_club.club.value == 'new')
		{
			window.open("admin_clubs.php", "_self");
		}
		else
		{
			document.change_club.submit()
		}
	}
//-->
</script>

<form action='admin_athletes.php' method='post' name='selection'>
	<input type='hidden' name='item' value='' />
</form>

<table class='dialog'>
<tr>
	<th class='dialog'>
		<a href='admin_athletes.php?arg=name'>
			<img src='<?php echo $img_name; ?>' />
			<?php echo $strName; ?>
		</a>
	</th>
	<th class='dialog'><?php echo $strFirstname; ?></th>
	<th class='dialog'>
		<a href='admin_athletes.php?arg=year'>
			<img src='<?php echo $img_year; ?>' />
			<?php echo $strYear; ?>
		</a>
	</th>
	<th class='dialog'>
		<a href='admin_athletes.php?arg=license'>
			<img src='<?php echo $img_license; ?>' />
			<?php echo $strLicenseNr; ?>
		</a>
	</th>
	<th class='dialog'>
		<a href='admin_athletes.php?arg=club'>
			<img src='<?php echo $img_club; ?>' />
			<?php echo $strClub; ?>
		</a>
	</th>
</tr>

<tr>
	<form action='admin_athletes.php' method='post' name='add_athlete'>
	<td class='forms'>
		<input name='arg' type='hidden' value='add'>
		<input class='text' name='name' type='text' maxlength='25'
			value="(<?php echo $strNew; ?>)" ></td>
	<td class='forms'>
		<input class='text' name='first' type='text' maxlength='25'></td>
	<td class='forms_ctr'>
		<input class='nbr' name='year' type='text' maxlength='4'></td>
	<td class='forms_ctr'>
		<input class='text' name='license' type='text'></td>
<?php
$dd = new GUI_ClubDropDown(0, true, "check()");
?>
	<td class='forms'>
		<button type='submit'>
			<?php echo $strSave; ?>
		</button>
	</td>
	</form>	
</tr>

<?php
// get athletes from DB
$sql = "SELECT
			a.xAthlet
			, a.Name
			, a.Vorname
			, a.Jahrgang
			, v.Name
			, v.xVerein
			, a.Lizenznummer
		FROM
			athlet AS a 
		LEFT JOIN 
			verein AS v USING(xVerein) 
		ORDER BY
			$argument";
$result = mysql_query($sql);

if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

// display list
$i=0;
while ($row = mysql_fetch_row($result))
{
	$i++;
	if($_POST['item'] == $row[0]) {
		$rowclass = 'active';
	}
	else if( $i % 2 == 0 ) {		// even row number
		$rowclass = 'even';
	}
	else {	// odd row number
		$rowclass = 'odd';
	}

	if($rowclass == 'active') {
?>
	<tr class='<?php echo $rowclass; ?>'>
<?php	
	}
	else
	{
?>
	<tr class='<?php echo $rowclass; ?>'
		onClick='selectAthlete(<?php echo $row[0]; ?>)'>
<?php	
	}
	if($_POST['item'] == $row[0])			// active athlete
	{
?>
	<form action='admin_athletes.php' method='post' name='change'>
		<td class='forms'>
			<input name='arg' type='hidden' value='change' />
			<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
			<input class='text' name='name' type='text'
				maxlength='25' value="<?php echo $row[1]; ?>"
				onChange='submitForm(document.change)'>
		</td>
		<td class='forms'>
			<input class='text' name='first' type='text'
				maxlength='25' value="<?php echo $row[2]; ?>"
				onChange='submitForm(document.change)'>
		</td>
		<td class='forms_ctr'>
			<input class='nbr' name='year' type='text'
				maxlength='4' value='<?php echo AA_formatYearOfBirth($row[3]); ?>'
				onChange='submitForm(document.change)'>
		</td>
		<td class='forms'>
			<input class='text' name='license' type='text'
				maxlength='25' value="<?php echo $row[6]; ?>"
				onChange='submitForm(document.change)'>
		</td>
	</form>	
	<form action='admin_athletes.php' method='post' name='change_club'>
		<input name='arg' type='hidden' value='change_club' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<?php
		$dd = new GUI_ClubDropDown($row[5], true, "checkNew()");
		?>
	</form>	
		<td>
		<?php
		$btn = new GUI_Button("admin_athletes.php?arg=del&item=$row[0]", $strDelete);
		$btn->printButton();
		?>
		</td>
		<?php
	}
	else	// athlete not active
	{
		$licenseNr = ($row[6]!='' && $row[6]>0) ? $row[6] : '-';
?>
		<td><?php echo $row[1]; ?></td>
		<td><?php echo $row[2]; ?></td>
		<td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[3]); ?></td>
		<td><?php echo $licenseNr; ?></td>
		<td><?php echo $row[4]; ?></td>
<?php
	}	// ET athlete active
	printf("</tr>\n");
}
mysql_free_result($result);
?>
	</table>

<script type="text/javascript">
<!--
	scrollDown();
	if(document.change) {
		document.change.name.focus();
	}
//-->
</script>

<?php

$page->endPage();

