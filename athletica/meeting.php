<?php

/**********
 *
 *	meeting.php
 *	------------------
 *	
 */

require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');
require('./lib/meeting.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if (isset($_GET['meetingId'])){
    $_POST['arg'] = 'select';
    $_POST['item'] = $_GET['meetingId'];
}

// Select active meeting
if (isset($_POST['arg']) && $_POST['arg']=="select")
{
	if(empty($_POST['item']))
	{
		// delete cookies
		setcookie("meeting_id", "", time()-3600);
		setcookie("meeting", "", time()-3600);
	}
	// OK: try to add cookie
	else
	{
		// get stadium name
		$result = mysql_query("
			SELECT
				Name
			FROM
				meeting
			WHERE xMeeting = " . $_POST['item']
		);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			$meeting_name = "";
		}
		else {
			$row = mysql_fetch_row($result);
			$meeting_name = $row[0];
			mysql_free_result($result);
		}

		// store cookies on browser
		setcookie("meeting_id", $_POST['item'], time()+$cfgCookieExpires);
		setcookie("meeting", $meeting_name, time()+$cfgCookieExpires);
		// update current cookies
		$_COOKIE['meeting_id'] = $_POST['item'];
		$_COOKIE['meeting'] = $meeting_name;
	}
}


//
// Process add-request if required
//
if (isset($_POST['arg']) && $_POST['arg']=="add")
{
	$fromdate = $_POST['from_year'] . $_POST['from_month'] . $_POST['from_day'];
	$todate = $_POST['to_year'] . $_POST['to_month'] . $_POST['to_day'];
	
	$online = "";
	if($_POST['online'] == 'yes'){
		$online = "y";
	}else{
		$online = "n";
	}
	
	// Error: Empty fields
	if(empty($_POST['name']) || empty($_POST['place'])
		|| empty($_POST['from_day']) || empty($_POST['from_month'])
		|| empty($_POST['from_year'])|| empty($_POST['ukc']))
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	// Error: invalid date
	else if(!is_numeric($fromdate) || !is_numeric($todate))
	{
		AA_printErrorMsg($strErrInvalidDate);
	}
	else if($fromdate > $todate)			// invalid order
	{
		AA_printErrorMsg($strErrTodateLowerFromdate);
	}
	else
	{
	// OK: try to add item
	
		mysql_query("LOCK TABLES stadion READ, meeting WRITE");
		// check if stadium is valid
		$result = mysql_query("SELECT xStadion FROM stadion WHERE xStadion=" . $_POST['stadium']);
		$rows = mysql_num_rows($result);
		mysql_free_result($result);
		if($rows == 0)		// Stadium does not exist
		{
			AA_printErrorMsg($strStadium . $strErrNotValid);
		}
		else if ($_POST['saison']==""){
			AA_printErrorMsg($strSaison . $strErrNotValid);
		} 
        else if ($_POST['ukc']=="-"){
            AA_printErrorMsg($strEventType . $strErrNotValid);
        } 
		else if ($_POST['arg']=="add")
		{
			mysql_query("
				INSERT INTO meeting SET 
					Name=\"" . $_POST['name'] . "\"
					, Ort=\"" . $_POST['place'] . "\"
					, DatumVon='" . $fromdate . "'
					, DatumBis='" . $todate . "'
					, Online = '$online'
					, Organisator = '".$_POST['organisator']."'
					, xStadion=" . $_POST['stadium'] . "
					, Saison='" . $_POST['saison'] ."'
                    , UKC='" . $_POST['ukc'] ."'" 
			);
		}
		// Check if any error returned from DB
		if(mysql_errno() > 0)
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		mysql_query("UNLOCK TABLES");
	}
}

//
// Display current data
//

$page = new GUI_Page('meeting');
$page->startPage();
$page->printPageTitle($cfgApplicationName
			. " (Version " . $cfgApplicationVersion . ")");

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/meeting/index.html', $strHelp, '_blank');
$menu->printMenu();

?>
<script type="text/javascript">
<!--
	function selectMeeting(meetingID)
	{  
		document.selection.item.value=meetingID;
		document.selection.submit();
	}

	function check(item)
	{
		if((item == 'stadium')
			&& (document.add.stadium.value=='new'))	// new stadium
		{
			window.open("admin_stadiums.php", "_self");
		}
	}

//-->
</script>
<?php

if(empty($_COOKIE['meeting_id'])) {
	AA_printWarningMsg($strNoMeetingSelected);
}

//
// Display list of meetings 
//

// sort argument
if (isset($_GET['arg']) && $_GET['arg']=="name") {
	$argument="m.Name";
	$img_name="img/sort_act.gif";
	$img_date="img/sort_inact.gif";
} else if (isset($_GET['arg']) && $_GET['arg']=="date") {
	$argument="m.DatumVon";
	$img_name="img/sort_inact.gif";
	$img_date="img/sort_act.gif";
} else {
	$argument="m.DatumVon";
	$img_name="img/sort_inact.gif";
	$img_date="img/sort_act.gif";
}
?>

<form action='index.php' method='post' target="_parent"
	name='selection'>
	<input type='hidden' name='arg' value='select' />
	<input type='hidden' name='item' value='' />
    <input type='hidden' name='meetingID' value='<?php echo $row["meetingID"];?>' />
</form>

<h2><?php echo $strMeetings; ?></h2>

<table class='dialog'>
	<tr>
		<th class='dialog'>
			<a href='meeting.php?arg=name'><?php echo $strName; ?>
				<img src='<?php echo $img_name; ?>' border='0' />
			</a></th>
		<th class='dialog'><?php echo $strPlace; ?></a></th>
		<th class='dialog'><?php echo $strStadium; ?></a></th>
		<th class='dialog'>
			<a href='meeting.php?arg=date'><?php echo $strDateFrom; ?>
				<img src='<?php echo $img_date; ?>' border='0' />
			</a></th>
		<th class='dialog'><?php echo $strDateTo; ?></a></th>
		<th class='dialog'><?php echo $strSaison; ?></a></th>
        <th class='dialog'><?php echo $strEventType; ?></a></th>
	</tr>

<?php
	
// get meetings from DB
/*$result = mysql_query("
	SELECT
		m.xMeeting
		, m.Name
		, m.Ort
		, DATE_FORMAT(m.DatumVon, '$cfgDBdateFormat')
		, DATE_FORMAT(m.DatumBis, '$cfgDBdateFormat')
		, s.Name
	FROM
		meeting AS m
		, stadion AS s
	WHERE s.xStadion = m.xStadion
	ORDER BY
		$argument, m.xMeeting
");*/
$sql = "SELECT
			  m.xMeeting
			, m.Name
			, m.Ort
			, DATE_FORMAT(m.DatumVon, '".$cfgDBdateFormat."') as DatumVon
			, DATE_FORMAT(m.DatumBis, '".$cfgDBdateFormat."') as DatumBis
			, s.Name as Stadion
			, m.Saison
            , m.UKC
		FROM
			meeting AS m
		LEFT JOIN
			stadion AS s USING(xStadion)
		ORDER BY
			  ".$argument."
			, m.xMeeting";
$result = mysql_query($sql);

if(mysql_errno() > 0)
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

// display list
$i=0;
while ($row = mysql_fetch_array($result))
{
	$i++;
    if (isset($_GET['arg'])){
        $meeting_ID = $_GET['arg'];
    }
    else {
        $meeting_ID = $_COOKIE['meeting_id'];
    }
    
	if($row['xMeeting'] == $meeting_ID ) 	// selected meeting
	{
		?>
	<tr class='active'>
		<td><?php echo $row['Name']; ?></td>
		<td><?php echo $row['Ort']; ?></td>
		<td><?php echo $row['Stadion']; ?></td>
		<td><?php echo $row['DatumVon']; ?></td>
		<td><?php echo $row['DatumBis']; ?></td>
		<td><?php if ($row['Saison']=="I"){ echo "Indoor";} if ($row['Saison']=="O"){ echo "Outdoor";}?></td>
        <td><?php if ($row['UKC']=="y"){ echo $strUKC;} else {echo $strMeetingTitle; }?></td>
		<td>
		<?php
		$btn = new GUI_Button('meeting_delete.php', "$strDelete ...");
		$btn->printButton();
		$btn = new GUI_Button('meeting_copy.php', "$strCopy ...");
		$btn->printButton();
		?>
		</td>
	</tr>
		<?php
	}
	else				// meeting not active
	{
		if($i % 2 == 0 ) {		// even row number
			$class="even";
		}
		else {	// odd row number
			$class="odd";
		}
		?>
	<tr class='<?php echo $class; ?>'
		onClick='selectMeeting(<?php echo $row['xMeeting']; ?>)' style="cursor: pointer;">
		<td><?php echo $row['Name']; ?></td>
		<td><?php echo $row['Ort']; ?></td>
		<td><?php echo $row['Stadion']; ?></td>
		<td><?php echo $row['DatumVon']; ?></td>
		<td><?php echo $row['DatumBis']; ?></td>
		<td><?php if ($row['Saison']=="I"){ echo "Indoor";} if ($row['Saison']=="O"){ echo "Outdoor";}?></td>
         <td><?php if ($row['UKC']=="y"){ echo $strUKC;} else {echo $strMeetingTitle; }?></td>  
	</tr>
		<?php
	}		// ET meeting active	
}



mysql_free_result($result);


//
// Display add-form
//

// calc current date
$date = date("Y-m-d");
$day=substr($date, 8, 2);
$month=substr($date, 5, 2);
$year=substr($date, 0, 4);
?>
</table>

<h2><?php echo $strNewMeeting; ?></h2>
<form action='meeting.php' method='post' name='add'>
	<input name='arg' type='hidden' value='add'>
	<table>
		<tr>
			<th class='dialog'><?php echo $strName; ?></th>
			<th class='dialog'><?php echo $strPlace; ?></th>
			<th class='dialog'><?php echo $strOrganizer; ?></th>
			<th class='dialog'><?php echo $strStadium; ?></th>
			<th class='dialog'><?php echo $strSaison; ?></th>
            <th class='dialog'><?php echo $strEventType; ?></a></th>    
		</tr>
		<tr>
			<td class='forms'>
				<input class='text' name='name' type='text' maxlength='60'>
			</td>
			<td class='forms'>
				<input class='text' name='place' type='text' maxlength='20'
					value='' />
			</td>
			<td class='forms'>
				<input class='text' name='organisator' type='text' maxlength='20'
					value='' />
			</td>
			<?php
				$dd = new GUI_StadiumDropDown();
			?>
			<?php
				$dd = new GUI_SeasonDropDown();
			?>
            
                <td class='forms'>
                <select name="ukc" onchange="document.ukc.submit()">
                 <option value="-">-</option>
                    <option value="n"><?php echo $strMeetingTitle; ?></option>     
                    <option value="y"><?php echo $strUKC; ?></option>
                 </select>
                </td>
           
		</tr>
	</table>
	<table>
		<tr>
			<th class='dialog' colspan='3'><?php echo $strDateFrom; ?></th>
			<th class='dialog' colspan='3'><?php echo $strDateTo; ?></th>
		</tr>
		<tr>
			<?php AA_meeting_printDate('from', ''); ?>
			<?php AA_meeting_printDate('to', ''); ?>
		</tr>
		
	</table><br/>
	<table>
		<!--<tr>
			<th class='dialog' colspan='3'><?php echo $strMeetingWithUpload; ?></th>
			<th class='dialog' colspan='3'><input type="checkbox" name="online" value='yes' checked> <?php echo $strYes ?></th>
		</tr>-->
		<tr>
			<td class='forms' colspan='3'>
				<button type='submit'>
					<?php echo $strSave; ?>
				</button>
			<td />
		</tr>
	</table>
	<input type="hidden" name="online" value="yes"/>
</form>	


<?php


$page->endPage();
?>
