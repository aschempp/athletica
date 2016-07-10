<?php

/**********
 *
 *	meeting_entries_search.php
 *	--------------------------
 *	
 */

require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

// prepare search argument
if(is_numeric($_POST['searchfield'])) {
	$searchparam = " AND a.Startnummer = '" . $_POST['searchfield'] . "'";
}	
else {
	$searchparam = " AND (at.Name like '%" . $_POST['searchfield'] . "%' OR at.Vorname like '%" . $_POST['searchfield'] . "%') ";
}


//
//	Display data
// ------------

$page = new GUI_Page('meeting_entries', TRUE);
$page->startPage();
$page->printPageTitle($strSearchResults);

?>
<script type="text/javascript">
<!--
	function selectAthlete(item)
	{
		document.selection.item.value=item;
		document.selection.submit();
	}
//-->
</script>

<form action='meeting_entry.php' method='post' name='selection'>
	<input type='hidden' name='item' value='' />
</form>

<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strStartnumber; ?></th>
	<th class='dialog'><?php echo $strName; ?></th>
	<th class='dialog'><?php echo $strYear; ?></th>
	<th class='dialog'><?php echo $strCategory; ?></th>
	<th class='dialog'><?php echo $strTeam; ?></th>
	<th class='dialog'><?php echo $strClub; ?></th>
</tr>
<?php

// read all entries        
  $sql = "SELECT
        a.xAnmeldung
        , a.xKategorie
        , a.Startnummer
        , at.xAthlet
        , at.Name
        , at.Vorname
        , at.Jahrgang
        , k.Kurzname
        , v.Name
        , v.xVerein
        , t.Name
        , t.xTeam
    FROM
        anmeldung AS a
        LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet )
        LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)
        LEFT JOIN verein AS v ON (at.xVerein = v.xVerein)
    LEFT JOIN team AS t
    ON a.xTeam = t.xTeam
    WHERE a.xMeeting = " . $_COOKIE['meeting_id'] . "      
    $searchparam
    ORDER BY
        a.Startnummer";         
 
$result = mysql_query($sql);  

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	// display list
	$i=0;
	$rowclass = "odd";

	while ($row = mysql_fetch_row($result))
	{
		$i++;
		if( $i % 2 == 0 ) {		// even row number
			$rowclass = 'even';
		}
		else {	// odd row number
			$rowclass = 'odd';
		}
//
//	Show athlete's personal data
//
?>
<tr class='<?php echo $rowclass; ?>'
	onClick='selectAthlete(<?php echo $row[0]; ?>)'>

	<td class='forms_right'><?php echo $row[2]; ?></td>
	<td nowrap><?php echo $row[4]. " ".$row[5]; ?></td>
	<td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[6]); ?></td>
	<td><?php echo $row[7]; ?></td>
	<td><?php echo $row[10]; ?></td>
	<td><?php echo $row[8]; ?></td>
</tr>
<?php
	}
	mysql_free_result($result);
}						// ET DB error

?>
</table>
<?php
$page->endPage();

