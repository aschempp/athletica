<?php

/**********
 *
 *	meeting_teamlist.php
 *	--------------------
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

$arg = (isset($_GET['arg'])) ? $_GET['arg'] : ((isset($_COOKIE['sort_teamlist'])) ? $_COOKIE['sort_teamlist'] : 'cat');
setcookie('sort_teamlist', $arg, time()+2419200);

$page = new GUI_Page('meeting_teamlist');
$page->startPage();

// sort argument
$img_name="img/sort_inact.gif";
$img_cat="img/sort_inact.gif";
$img_club="img/sort_inact.gif";

if ($arg=="name") {
	$argument="t.Name, ks.Code";
	$img_name="img/sort_act.gif";
} else if ($arg=="cat") {
	$argument="ks.Code, t.Name";
	$img_cat="img/sort_act.gif";
} else {
	$argument="ks.Code, t.Name";
	$img_cat="img/sort_act.gif";
}

?>
<script type="text/javascript">
<!--
	function selectTeam(item)
	{
		document.selection.item.value=item;
		document.selection.submit();
	}
//-->
</script>

<form action='meeting_team.php' method='post' target='detail' name='selection'>
	<input type='hidden' name='item' value='' />
</form>

<table class='dialog'>
	<tr>
		<th class='dialog'>
			<a href='meeting_teamlist.php?arg=cat'><?php echo $strSvmCategory; ?>
				<img src='<?php echo $img_cat; ?>' />
			</a>
		</th>
		<th class='dialog'>
			<a href='meeting_teamlist.php?arg=name'><?php echo $strName; ?>
				<img src='<?php echo $img_name; ?>' />
			</a>
		</th>
	</tr>

<?php
// get all teams
$result = mysql_query("
	SELECT
		t.xTeam
		, t.Name
		, ks.Name
	FROM
		team AS t
		LEFT JOIN kategorie_svm AS ks ON (ks.xKategorie_svm = t.xKategorie_svm)
	WHERE 
        t.xMeeting = " . $_COOKIE['meeting_id'] . "  
	ORDER BY
		$argument
");

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else				// no DB error
{
	// display list
	$i=0;

	while ($row = mysql_fetch_row($result))
	{
		$i++;
		if($_GET['item'] == $row[0])	{		// active team
			$rowclass='active';
		}
		else if( $i % 2 == 0 ) {		// even row number
			$rowclass='even';
		}
		else {
			$rowclass='odd';
		}
		?>
	<tr class='<?php echo $rowclass; ?>'
		onClick='selectTeam(<?php echo $row[0]; ?>)' style="cursor: pointer;">

		<td>
			<a name="item<?php echo $row[0]; ?>"></a>
			<?php echo substr($row[2], 5); ?>
		</td>
		<td><?php echo $row[1]; ?></td>
	</tr>
		<?php
	}
	mysql_free_result($result);
}						// ET DB error
?>
</table>

<script>
	document.all.item<?php echo $_GET['item']; ?>.scrollIntoView("true");
</script>
<?php

$page->endPage();
