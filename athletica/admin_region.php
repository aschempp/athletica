<?php

/**********
 *
 *	admin_clubs.php
 *	------------------
 *	This script accepts knows one default option:
 *		(default, no argument):	displays list of clubs, including methods
 *										to add or delete clubs
 */
 
$noMeetingCheck = true;

require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');


require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}


//
// Process add request
//
if ($_POST['arg']=="add")
{
	
	if(!empty($_POST['name']) && !empty($_POST['display']) && !empty($_POST['sortvalue'])){
		
		mysql_query("INSERT INTO region SET
					Name = '".$_POST['name']."'
					, Anzeige = '".$_POST['display']."'
					, Sortierwert = '".$_POST['sortvalue']."'");
		
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}
		
	}else{
		
		AA_printErrorMsg($strErrEmptyFields);
		
	}
	
}
//
// Process change
//
elseif($_POST['arg']=="change"){
	
	if(!empty($_POST['name']) && !empty($_POST['display']) && !empty($_POST['sortvalue']) && !empty($_POST['item'])){
		
		mysql_query("UPDATE region SET
					Name = '".$_POST['name']."'
					, Anzeige = '".$_POST['display']."'
					, Sortierwert = '".$_POST['sortvalue']."'
				WHERE xRegion = ".$_POST['item']);
		
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}
		
	}else{
		
		AA_printErrorMsg($strErrEmptyFields);
		
	}
	
}
//
// Process delete request
//
else if ($_GET['arg']=="del")
{
	mysql_query("LOCK TABLES athlet READ, region WRITE");

	// Still in use?
	$rows = AA_checkReference("athlet", "xRegion", $_GET['item']);

	// OK: not used anymore
	if($rows == 0)
	{
		mysql_query("DELETE FROM region WHERE xRegion=" . $_GET['item']);
	}
	// Error: still in use
	else
	{
		AA_printErrorMsg($strRegion . $strErrStillUsed);
	}
	mysql_query("UNLOCK TABLES");
}

// Check if any error returned from DB
if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

//
// Display current data
//

$page = new GUI_Page('admin_region', TRUE);
$page->startPage();
$page->printPageTitle($strRegion);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/clubs.html', $strHelp, '_blank');
$menu->printMenu();

// sort argument
$img_name="img/sort_inact.gif";
$img_display="img/sort_inact.gif";
$img_sort="img/sort_inact.gif";

if ($_GET['sort']=="name") {
	$argument="Name";
	$img_name="img/sort_act.gif";
}else if ($_GET['sort']=="display") {
	$argument="Anzeige";
	$img_display="img/sort_act.gif";
} else if ($_GET['sort']=="sort") {
	$argument="Sortierwert";
	$img_sort="img/sort_act.gif";
} else {							// relay event
	$argument="Sortierwert";
	$img_sort="img/sort_act.gif";
}


?>
<p/>
<table class='dialog'>
	<tr>
		<th class='dialog'>
			<a href='admin_region.php?sort=name'>
				<?php echo $strName; ?>
				<img src='<?php echo $img_name; ?>'>
			</a>
		</th>
		<th class='dialog'>
			<a href='admin_region.php?sort=display'>
				<?php echo $strDisplay; ?>
				<img src='<?php echo $img_display; ?>'>
			</a>
		</th>
		<th class='dialog'>
			<a href='admin_region.php?sort=sort'>
				<?php echo $strSortValue; ?>
				<img src='<?php echo $img_sort; ?>'>
			</a>
		</th>
	</tr>
	<tr>
		<form action='admin_region.php' method='post'>
		<td class='forms'>
			<input name='arg' type='hidden' value='add'>
			<input class='text' name='name' type='text' maxlength='50'
				value="(<?php echo $strNew; ?>)" ></td>
		<td class='forms'>
			<input name='display' type='text' size="6" maxlength='6'
				value='' ></td>
		<td class='forms'>
			<input name='sortvalue' type='text' size="6" maxlength='6'
				value='' ></td>
		<td class='forms'>
			<button type='submit'>
				<?php echo $strSave; ?>
			</button>
		</td>
		</form>	
	</tr>

<?php
$result = mysql_query("
	SELECT
		xRegion
		, Name
		, Anzeige
		, Sortierwert
	FROM
		region
	ORDER BY
		$argument
");

// each row
$l = 0;
$btn = new GUI_Button('', '');

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
			<form action='admin_region.php#item_<?php echo $row[0]; ?>'
				method='post' name='region<?php echo $i; ?>'>
			<td class='forms'>
				<input name='arg' type='hidden' value='change'>
				<input name='item' type='hidden' value='<?php echo $row[0]; ?>'>
				<input class='text' name='name' type='text' maxlength='30'
					value="<?php echo $row[1]; ?>"
					onChange='submitForm(document.region<?php echo $i; ?>)'>
			</td>
			<td class='forms'>
				<input name='display' type='text' maxlength='6' size="6"
					value="<?php echo $row[2]; ?>"
					onChange='submitForm(document.region<?php echo $i; ?>)'>
			</td>
			<td class='forms'>
				<input name='sortvalue' type='text' maxlength='30' size="4"
					value="<?php echo $row[3]; ?>"
					onChange='submitForm(document.region<?php echo $i; ?>)'>
			</td>
			<td>
<?php
	$btn->set("admin_region.php?arg=del&item=$row[0]", $strDelete);
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
?>
