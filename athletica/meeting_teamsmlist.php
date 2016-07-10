<?php

/**********
 *
 *	meeting_teamsmlist.php
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

$arg = (isset($_GET['arg'])) ? $_GET['arg'] : ((isset($_COOKIE['sort_teamsm'])) ? $_COOKIE['sort_teamsm'] : 'name');
setcookie('sort_teamsm', $arg, time()+2419200);

$page = new GUI_Page('meeting_teamsms');
$page->startPage();

// sort argument
$img_name="img/sort_inact.gif";
$img_cat="img/sort_inact.gif";
$img_disc="img/sort_inact.gif";
$img_nbr="img/sort_inact.gif"; 
$img_group="img/sort_inact.gif";   

if ($arg=="name") {
	$argument="t.Name, k.Anzeige";
	$img_name="img/sort_act.gif";
} else if ($arg=="cat") {
	$argument="k.Anzeige, t.Name";
	$img_cat="img/sort_act.gif";
} else if ($arg=="disc") {
	$argument="d.Anzeige, t.Name";
	$img_disc="img/sort_act.gif";
} else if ($arg=="group") {
    $argument="t.Gruppe, t.Name";
    $img_group="img/sort_act.gif";
} else if ($arg=="nbr") {
    $argument="t.Startnummer";
    $img_nbr="img/sort_act.gif";
} else {
	$argument="t.Name, k.Anzeige";
	$img_nbr="img/sort_act.gif"; 
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

<form action='meeting_teamsm.php' method='post' target='detail' name='selection'>
	<input type='hidden' name='item' value='' />
</form>

<table class='dialog'>
	<tr>
        <th class='dialog'>
        <a href='meeting_teamsmlist.php?arg=nbr'><?php echo $strStartnumber; ?>
            <img src='<?php echo $img_nbr; ?>' />
        </a>
    </th>
		<th class='dialog'>
			<a href='meeting_teamsmlist.php?arg=name'><?php echo $strName; ?>
				<img src='<?php echo $img_name; ?>' />
			</a>
		</th>
		<th class='dialog'>
			<a href='meeting_teamsmlist.php?arg=cat'><?php echo $strCategory; ?>
				<img src='<?php echo $img_cat; ?>' />
			</a>
		</th>
		<th class='dialog'>
			<a href='meeting_teamsmlist.php?arg=disc'><?php echo $strDiscipline; ?>
				<img src='<?php echo $img_disc; ?>' />
			</a>
		</th>
        <th class='dialog'>
            <a href='meeting_teamsmlist.php?arg=group'><?php echo $strGroup; ?>
                <img src='<?php echo $img_group; ?>' />
            </a>
        </th>
	</tr>

<?php
// get all teams       
  $sql = "
    SELECT
        t.xTeamsm
        , t.Name
        , k.Kurzname
        , d.Kurzname
        , t.Startnummer
        , t.Gruppe
    FROM
        teamsm AS t
        LEFT JOIN kategorie AS k ON (k.xKategorie = t.xKategorie)
        LEFT JOIN wettkampf AS w ON (w.xWettkampf = t.xWettkampf)
        LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
    WHERE 
        t.xMeeting = " . $_COOKIE['meeting_id'] . "     
    ORDER BY
        $argument";    
 
$result = mysql_query($sql);       

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
        <?php echo $row[4] ?>
        </td>
		<td>
			<a name="item<?php echo $row[0]; ?>" id='item<?php echo $row[0]; ?>'></a>
			<?php echo $row[1]; ?>
		</td>
		<td><?php echo $row[2]; ?></td>
		<td><?php echo $row[3]; ?></td>
        <td><?php echo $row[5]; ?></td>   
	</tr>
		<?php
	}
	mysql_free_result($result);
}						// ET DB error
?>
</table>

<script>
	 document.getElementById('item<?php echo $_GET['item']; ?>').scrollIntoView("true");  
    
</script>
<?php

$page->endPage();

?>
