<?php

/**
 *
 * meeting_online_convert.php
 * --------------------------
 *
 * updating meeting relays and teams with new id's like they should be for result upload
 * (Control Number)
 *
**/

require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}


//
// display data
//
$page = new GUI_Page('meeting_online_convert');
$page->startPage();
$page->printPageTitle("Meeting Online Convert");

$mid = $_COOKIE['meeting_id'];
$c = 33;

$res = mysql_query("SELECT xControl FROM meeting WHERE xMeeting = $mid");
if(mysql_errno()>0){
	printf(mysql_error());
	die;
}else{
	$row = mysql_fetch_array($res);
	$control = $row[0];
}
mysql_free_result($res);

$res = mysql_query("SELECT xStaffel FROM staffel WHERE xMeeting = $mid");
if(mysql_errno()>0){
	printf(mysql_error());
}else{
	while($row = mysql_fetch_array($res)){
		
		$id = $control.sprintf("%03d", $c);
		echo "Staffel $row[0] --> $id<br>";
		
		mysql_query("UPDATE staffel SET xStaffel = $id, Athleticagen = 'y' WHERE xStaffel = $row[0]");
		if(mysql_errno()>0){
			printf(mysql_error());
		}
		
		mysql_query("UPDATE start SET xStaffel = $id WHERE xStaffel = $row[0]");
		if(mysql_errno()>0){
			printf(mysql_error());
		}
		
		$c++;
	}
}
mysql_free_result($res);

$res = mysql_query("SELECT xTeam FROM team WHERE xMeeting = $mid");
if(mysql_errno()>0){
	printf(mysql_error());
}else{
	while($row = mysql_fetch_array($res)){
		
		$id = $control.sprintf("%03d", $c);
		echo "Team $row[0] --> $id<br>";
		
		mysql_query("UPDATE team SET xTeam = $id, Athleticagen = 'y' WHERE xTeam = $row[0]");
		if(mysql_errno()>0){
			printf(mysql_error());
		}
		
		mysql_query("UPDATE anmeldung SET xTeam = $id WHERE xTeam = $row[0]");
		if(mysql_errno()>0){
			printf(mysql_error());
		}
		
		mysql_query("UPDATE staffel SET xTeam = $id WHERE xTeam = $row[0]");
		if(mysql_errno()>0){
			printf(mysql_error());
		}
		
		$c++;
	}
}

echo "<br>Fertig! <br>";
echo "Counter: $c<br>";

$page->endPage();
?>
