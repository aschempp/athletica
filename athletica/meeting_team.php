<?php

/**********
 *
 *	meeting_team.php
 *	-----------------
 *	
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

//
// change team name
//
if ($_POST['arg']=="change")
{
	// Error: Empty fields
	if(empty($_POST['item']) || empty($_POST['name']))
	{
 		AA_printErrorMsg($strErrEmptyFields);
	}
	// OK
	else
	{
		mysql_query("
			LOCK TABLES
				team WRITE
		");

 		mysql_query("
			UPDATE team SET
				Name=\"" . $_POST['name'] . "\"
			WHERE xTeam=" . $_POST['item']
		);
		if(mysql_errno() > 0) {
 			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}
}

//
// Process del-request
//
if ($_GET['arg']=="del")
{
	mysql_query("
		LOCK TABLES
			anmeldung READ
			, team WRITE
	");

	// Still in use?
	if(AA_checkReference("anmeldung", "xTeam",  $_GET['item']) > 0)
	{
		AA_printErrorMsg($strTeam . $strErrStillUsed);
	}
	// OK to delete
	else	// no DB error
	{
		// delete team events
		mysql_query("
			DELETE FROM team
			WHERE xTeam = " . $_GET['item']
		);
		if(mysql_errno() > 0)	// check DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}			// ET DB error (still in use)
	mysql_query("UNLOCK TABLES");

	$_POST['item'] = $_GET['item'];	// show empty form after delete
}

//
// display data
//
$page = new GUI_Page('meeting_team');
$page->startPage();
$page->printPageTitle($strTeam);

if($_GET['arg'] == 'del')	// refresh list
{
	?>
	<script type="text/javascript">
		window.open("meeting_teamlist.php", "list")
	</script>
	<?php
}
else 
{
	?>
	<script type="text/javascript">
		window.open("meeting_teamlist.php?item="
			+ <?php echo $_POST['item']; ?>, "list");
	</script>
	<?php
}

// get team          
 $sql =  "SELECT
        t.xTeam
        , t.Name
        , k.Kurzname
        , v.Name
        , v.xVerein
        , k.xKategorie
        , k_svm.Name 
    FROM
        team AS t
        LEFT JOIN kategorie AS k ON (k.xKategorie = t.xKategorie)
        LEFT JOIN kategorie_svm AS k_svm ON (k_svm.xKategorie_svm = t.xKategorie_svm)    
        LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
    WHERE 
        t.xTeam = " . $_POST['item'];   
  
$result = mysql_query($sql);   

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	$row = mysql_fetch_row($result);

	?>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strCategory; ?></th>
	<td class='dialog'><?php echo $row[2]; ?></td>
</tr>
<tr>
    <th class='dialog'><?php echo $strSvmCategory; ?></th>
    <td class='dialog'><?php echo $row[6]; ?></td>
</tr>

<tr>
	<th class='dialog'><?php echo $strClub; ?></th>
	<td class='dialog'><?php echo $row[3]; ?></td>
</tr>

<tr>
	<form action='meeting_team.php' method='post' name='change'>
	<th class='dialog'><?php echo $strName; ?></th>
	<td class='forms'>
		<input name='arg' type='hidden' value='change' />
		<input name='item' type='hidden' value='<?php echo $row[0]; ?>' />
		<input class='text' name='name' type='text'
			maxlength='40' value="<?php echo $row[1]; ?>"
			onchange='document.change.submit()' />
	</td>
	</form>
</tr>
</table>
	<?php
	mysql_free_result($result);
}						// ET DB error
?>
<p />
<?php
$btn = new GUI_Button("meeting_team.php?arg=del&item=$row[0]", $strDelete);
$btn->printButton();

$page->endPage();
