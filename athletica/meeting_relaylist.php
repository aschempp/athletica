<?php

/**********
 *
 *	meeting_relaylist.php
 *	---------------------
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

$arg = (isset($_GET['arg'])) ? $_GET['arg'] : ((isset($_COOKIE['sort_relaylist'])) ? $_COOKIE['sort_relaylist'] : 'nbr');
setcookie('sort_relaylist', $arg, time()+2419200);

$page = new GUI_Page('meeting_relaylist');
$page->startPage();

// sort argument
$img_name="img/sort_inact.gif";
$img_disc="img/sort_inact.gif";
$img_cat="img/sort_inact.gif";
$img_nbr="img/sort_inact.gif";

// sort argument
if ($arg=="name") {
	$argument="s.Name, k.Anzeige, d.Anzeige";
	$img_name="img/sort_act.gif";
} else if ($arg=="disc") {
	$argument="d.Anzeige, s.Name, k.Anzeige";
	$img_disc="img/sort_act.gif";
} else if ($arg=="cat") {
	$argument="k.Anzeige, s.Name, d.Anzeige";
	$img_cat="img/sort_act.gif";
} else if ($arg=="nbr") {
	$argument="s.Startnummer";
	$img_nbr="img/sort_act.gif";
} else {
	$argument="s.Startnummer, s.Name, d.Anzeige";
	$img_nbr="img/sort_act.gif";
}

?>
<script type="text/javascript">
<!--
	function selectRelay(item)
	{
		document.selection.item.value=item;
		document.selection.submit();
	}
//-->
</script>

<form action='meeting_relay.php' method='post' target='detail' name='selection'>
	<input type='hidden' name='item' value='' />
</form>

<table class='dialog'>
<tr>
	<th class='dialog'>
		<a href='meeting_relaylist.php?arg=nbr'><?php echo $strStartnumber; ?>
			<img src='<?php echo $img_nbr; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='meeting_relaylist.php?arg=cat'><?php echo $strCategoryShort; ?>
			<img src='<?php echo $img_cat; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='meeting_relaylist.php?arg=disc'><?php echo $strDiscipline; ?>
			<img src='<?php echo $img_disc; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='meeting_relaylist.php?arg=name'><?php echo $strRelay; ?>
			<img src='<?php echo $img_name; ?>' />
		</a>
	</th>
</tr>

<?php

// get all relays with athletes
// (remark: order of tables in FROM-clause is important for SQL performance)   
  $sql = "SELECT
        s.xStaffel
        , k.Kurzname
        , d.Kurzname
        , s.Name
        , s.Startnummer
    FROM
        start AS st
        LEFT JOIN staffel AS s ON (s.xStaffel = st.xStaffel)
        LEFT JOIN wettkampf AS w  ON (st.xWettkampf = w.xWettkampf)
        LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)  
        LEFT JOIN kategorie AS k ON (s.xKategorie = k.xKategorie)   
    WHERE 
        s.xMeeting = " . $_COOKIE['meeting_id'] . "   
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
		if($_GET['item'] == $row[0])	{		// active relay
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
	onClick='selectRelay(<?php echo $row[0]; ?>)' style="cursor: pointer;">
	<td>
		<?php echo $row[4] ?>
	</td>
	<td>
		<a name="item<?php echo $row[0]; ?>" ></a>
		<?php echo $row[1]; ?>
	</td>
	<td><?php echo $row[2]; ?></td>
	<td><?php echo $row[3]; ?></td>
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
