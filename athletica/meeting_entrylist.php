<?php

/**********
 *
 *	meeting_entrylist.php
 *	---------------------
 *	
 */

require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/entries.lib.php');
require('./lib/cl_performance.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

 $arg = (!empty($_GET['arg'])) ? $_GET['arg'] : ((!empty($_COOKIE['sort_entrylist'])) ? $_COOKIE['sort_entrylist'] : 'nbr');   

setcookie('sort_entrylist', $arg, time()+2419200);

//
//	Display data
// ------------

$page = new GUI_Page('meeting_entrylist');
$page->startPage();

// sort argument
$img_nbr="img/sort_inact.gif";
$img_name="img/sort_inact.gif";
$img_verein="img/sort_inact.gif";

/*if(isset($_POST['arg'])){
	$_GET['arg'] = $_POST['arg'];
}*/

if ($arg=="nbr") {
	$argument="a.Startnummer";
	$img_nbr="img/sort_act.gif";
} else if ($arg=="name") {
	$argument="at.Name, at.Vorname";
	$img_name="img/sort_act.gif";
} else if ($arg=="verein") {
	$argument="v.Sortierwert";
	$img_verein="img/sort_act.gif";
} else {
	$argument="a.Startnummer";
	$img_nbr="img/sort_act.gif";
}

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

<form action='meeting_entry.php' method='post' target='detail' name='selection'>
	<input type='hidden' name='item' value='' />
	<input type='hidden' name='lsort' value='<?php echo $arg ?>' />
</form>

<table class='dialog'>
<tr>
	<th class='dialog'>
		<a href='meeting_entrylist.php?arg=nbr'><?php echo $strStartnumber; ?>
			<img src='<?php echo $img_nbr; ?>' />
		</a>
	</th>
	<th class='dialog'>
		<a href='meeting_entrylist.php?arg=name'><?php echo $strName; ?>
			<img src='<?php echo $img_name; ?>' />
		</a>
	</th>    
	<th class='dialog'>
		<a href='meeting_entrylist.php?arg=verein'><?php echo $strClub; ?>
			<img src='<?php echo $img_verein; ?>' />
		</a>
	</th>
</tr>
<?php

//
// Display athletes with disciplines
//

// read all entries   
  $sql="SELECT  
        a.xAnmeldung
        , a.Startnummer
        , at.Name
        , at.Vorname          
        , IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)
        , t.Name
        , w.Typ
    FROM
        anmeldung AS a
        LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
        LEFT JOIN verein AS v USING(xVerein)
        LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung)
        LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf) 
          LEFT JOIN team AS t ON(a.xTeam = t.xTeam)    
    WHERE a.xMeeting = " . $_COOKIE['meeting_id'] . "  
    GROUP BY at.xAthlet 
    ORDER BY
        $argument";
 $result = mysql_query($sql);
 
if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	// display list
	$i=0;

	while ($row = mysql_fetch_row($result))
	{
		$i++;
		if($_GET['item'] == $row[0]) {
			$rowclass = 'active';
		}
		else if( $i % 2 == 0 ) {		// even row number
			$rowclass = 'even';
		}
		else {	// odd row number
			$rowclass = 'odd';
		}
        
        // check if svm
        $svm = false;
        if($row[6] > $cfgEventType[$strEventTypeSingleCombined]
                    && $row[6] != $cfgEventType[$strEventTypeTeamSM]){ 
                      $svm = true;  
        }
        
        
		?> 
<tr class='<?php echo $rowclass; ?>'
	onClick='selectAthlete(<?php echo $row[0]; ?>)' style="cursor: pointer;">

	<td class='forms_right'>
		<a name='item<?php echo $row[0]; ?>'  id='item<?php echo $row[0]; ?>'></a>
		<?php echo $row[1]; ?>
	</td>
	<td nowrap><?php echo $row[2]. " ".$row[3]; ?></td>
	<td nowrap><?php if ($svm) {echo $row[5];} else {echo $row[4];} ?></td>
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

