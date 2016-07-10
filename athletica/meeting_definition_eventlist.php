<?php
/**********
 *
 *	meeting_definition_eventlist.php
 *	--------------------------------
 *	
 */

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');


if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}


$category = 0;
if(!empty($_POST['cat'])) {
	$category = $_POST['cat'];
}
else if(!empty($_GET['cat'])) {
	$category = $_GET['cat'];
}

if($category > 0)
{
	?>
<script type="text/javascript">
<!--
	window.open("meeting_definition_category.php?cat=<?php echo $category; ?>", "detail")
//-->
</script>
	<?php
}

if(!empty($_GET['updateCat'])) {
	$category = $_GET['updateCat'];
}

/***************************
 *
 *		General meeting data
 *
 ***************************/

$page = new GUI_Page('meeting_definition_eventlist');
$page->startPage();
?>
<script type="text/javascript">
<!--
	function selectCategory(item)
	{
		window.open("meeting_definition_eventlist.php", "detail")
	}

	function selectEvent(item)
	{
		document.selection.item.value=item;
		document.selection.submit();
	}
//-->
</script>

<form action='meeting_definition_event.php' method='post' target='detail'
	name='selection'>
	<input type='hidden' name='item' value='' />
	<input type='hidden' name='cat' value='<?php echo $category; ?>' />
</form>

<?php
// show category selection
$result = mysql_query("
	SELECT
		w.xKategorie
		, k.Kurzname
	FROM
		wettkampf AS w
		LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
	WHERE w.xMeeting =" . $_COOKIE['meeting_id'] . "   
	GROUP BY
		w.xKategorie
	ORDER BY
		k.Anzeige
");

if(mysql_errno() > 0) {	// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else			// no DB error
{
?>
<table>
<?php
	$d=0;

	while($row = mysql_fetch_row($result))
	{
		if( $d % 3 == 0 ) {		// new row after three events
			if ( $d != 0 ) {
				printf("</td></tr>");	// terminate previous row
			}
			printf("<tr><td>");
		}
		$btn = new GUI_Button("meeting_definition_eventlist.php?cat=$row[0]", $row[1]);
		$btn->printButton();
		$d++;
	}
		
?>
</table>
<?php
	mysql_free_result($result);
}	// ET DB error


/*****************************************
 *
 *	 Events: disciplines per categories	
 *
 *****************************************/    
        
$sql = "SELECT
		w.xWettkampf
		, w.xKategorie
		, k.Name
		, d.Kurzname
		, w.Info
	FROM
		wettkampf AS w
		LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
		LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (w.xDisziplin = d.xDisziplin)
	WHERE w.xMeeting =" . $_COOKIE['meeting_id'] . "
	AND w.xKategorie = $category 	
	ORDER BY
		d.Anzeige";
 
$result = mysql_query($sql);     

if(mysql_errno() > 0) {	// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else			// no DB error
{
?>
<p/>
<table class='dialog'>
<?php
	// display list
	$i=0;

	while ($row = mysql_fetch_row($result))
	{
		if($i==0)	// first row: show category headerline
		{
			//	Headerline category
?>
	<tr>
		<th class='dialog'><?php echo $strCategory; ?></th>
		<td class='dialog'><?php echo $row[2]; ?></td>
	</tr>
	<tr>
		<th class='dialog' colspan='2'><?php echo $strEvents; ?></th>
	<tr>
<?php
		}

		$i++;
		// Print disciplines
		if($_GET['item'] == $row[0]) {		// active event
			$rowclass='active';
		}
		else if($i % 2 == 0 ) {		// even row number
			$rowclass='even';
		}
		else {	// odd row number
			$rowclass='odd';
		}

?>
	<tr class='<?php echo $rowclass; ?>'
		onClick='selectEvent(<?php echo $row[0]; ?>)' style="cursor: pointer;">
		<td class='dialog' colspan='2'>
			<a name='item<?php echo $row[0]; ?>'></a>
			<?php 
				if(strlen($row[4])!=0){
					echo $row[3]." (".$row[4].")"; 
				}else{
					echo $row[3];
				}
			?>
		</td>
	</tr>
<?php
	}		// ET category displayed
	mysql_free_result($result);
}		// ET DB error
?>

</table>

<script>
	document.all.item<?php echo $_GET['item']; ?>.scrollIntoView("true");
</script>

<?php
$page->startPage();
