<?php

/**********
 *
 *	event_log.php
 *	-------------------
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

$page = new GUI_Page('event_log');
$page->startPage();
$page->printPageTitle($strEventLog . ": " . $_COOKIE['meeting']);

//
//	Display data
// ------------
?>
<table class='dialog'>
	<tr>
		<th class='dialog'><?php echo $strTime; ?></th>
		<th class='dialog'><?php echo $strCategory; ?></th>
		<th class='dialog'><?php echo $strRound; ?></th>
		<th class='dialog'><?php echo $strLogEvent; ?></th>
	</tr>

<?php
// read all entries
/*$result = mysql_query("
	SELECT
		d.Kurzname
		, k.Name
		, DATE_FORMAT(rl.Zeit, '%d.%b.%Y, %T')
		, rl.Ereignis
		, rt.Typ
	FROM
		disziplin_" . $_COOKIE['language'] . " AS d
		, kategorie AS k
		, wettkampf AS w
		, runde AS r
		, rundenlog AS rl
	LEFT JOIN rundentyp AS rt
		ON r.xRundentyp = rt.xRundentyp
	WHERE r.xRunde = rl.xRunde
	AND w.xWettkampf = r.xWettkampf
	AND k.xKategorie = w.xKategorie
	AND d.xDisziplin = w.xDisziplin
	ORDER BY
		rl.Zeit DESC
");*/

$sql = "SELECT
			  d.Kurzname
			, k.Name
			, DATE_FORMAT(rl.Zeit, '%d.%b.%Y, %T')
			, rl.Ereignis
			, rt.Typ
		FROM
			disziplin_" . $_COOKIE['language'] . " AS d
		LEFT JOIN
			wettkampf AS w ON(d.xDisziplin = w.xDisziplin)
		LEFT JOIN 
			kategorie AS k USING(xKategorie)
		LEFT JOIN 
			runde AS r ON(w.xWettkampf = r.xWettkampf)
		LEFT JOIN
			rundenlog AS rl USING(xRunde)
		LEFT JOIN
			rundentyp_" . $_COOKIE['language'] . " AS rt ON(r.xRundentyp = rt.xRundentyp)
		WHERE w.xMeeting = ".$_COOKIE['meeting_id']. "
		ORDER BY
			rl.Zeit DESC;";
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
		if( $i % 2 == 0 ) {		// even row number
			$rowclass = 'even';
		}
		else {	// odd row number
			$rowclass = 'odd';
		}
?>
	<tr class='<?php echo $rowclass; ?>'>
		<td><?php echo $row[2]; ?></td>
		<td><?php echo $row[1]; ?></td>
		<td><?php echo "$row[0] $row[4]"; ?></td>
		<td><?php echo $row[3]; ?></td>
	</tr>
<?php
		$i++;
	}
	mysql_free_result($result);
}						// ET DB error
?>
</table>
<?php

$page->endPage();
