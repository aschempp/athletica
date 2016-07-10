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

if(!empty($_GET['club'])){
	$sqlName = " Sortierwert like '%".$_GET['club']."%' ";
}else{
	echo "<state>error</state>";
	return;
}

if($_GET['id'] != 0){
	$sqlId = " xVerein = ".$_GET['id'];
	$sqlName = "";
}

$sql = "SELECT * FROM verein WHERE $sqlName $sqlId ORDER BY Sortierwert DESC";

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
		
		echo "<name>".urlencode(trim($row['Name']))."</name>\n";
		echo "<sortvalue>".urlencode(trim($row['Sortierwert']))."</sortvalue>\n";
		echo "<id>".$row['xVerein']."</id>\n";
		
	}elseif($num <= 10){
		
		do{
			
			echo "<name>".urlencode(trim($row['Name']))."</name>\n";
			echo "<sortvalue>".urlencode(trim($row['Sortierwert']))."</sortvalue>\n";
			echo "<id>".$row['xVerein']."</id>\n";
			
		}while($row = mysql_fetch_assoc($res));
		
	}
	echo "</result>";
}
?>
