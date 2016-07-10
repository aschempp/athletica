<?php

header("Content-Type: text/xml");

/**********
 *
 *	meeting_entry_club_search.php
 *	---------------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	echo "<state>error</state>";
	return;		// abort
}


$sqlName = "";
$sqlId = "";

if(!empty($_GET['clubinfo'])){
	$sqlName = " Vereinsinfo like '%".$_GET['clubinfo']."%' ";
}else{
	echo "<state>error</state>";
	return;
}

if($_GET['id'] != 0){
	$sqlId = " xAnmeldung = ".$_GET['id'];
	$sqlName = "";
}

$sql = "SELECT
		Vereinsinfo
		, xAnmeldung
	FROM
		anmeldung
	WHERE $sqlName $sqlId AND xMeeting = ".$_COOKIE['meeting_id']."
	GROUP BY Vereinsinfo
	ORDER BY Vereinsinfo DESC
	";

$res = mysql_query($sql);
if(mysql_errno > 0){
	echo "<state>error</state>";
}else{
	echo "<result>\n";
	echo "<state>ok</state>\n";
	$num = mysql_num_rows($res);
	$row = mysql_fetch_assoc($res);
	
	
	echo "<num>$num</num>\n";
	if($num == 1){
		
		echo "<name>".urlencode(trim($row['Vereinsinfo']))."</name>\n";
		echo "<id>".$row['xAnmeldung']."</id>\n";
		
	}elseif($num <= 10){
		
		do{
			
			echo "<name>".urlencode(trim($row['Vereinsinfo']))."</name>\n";
			echo "<id>".$row['xAnmeldung']."</id>\n";
			
		}while($row = mysql_fetch_assoc($res));
		
	}
	echo "</result>";
}
?>
