<?php

/**********
 *
 *	print_rankinglist.php
 *	---------------------
 *	
 */         
    
require('./lib/common.lib.php');
require('./lib/rankinglist_sheets.lib.php');
require('./lib/rankinglist_single.lib.php');
require('./lib/rankinglist_combined.lib.php');
require('./lib/rankinglist_team.lib.php');
require('./lib/rankinglist_teamsm.lib.php');

if(AA_connectToDB() == FALSE)	{ // invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}
	   

// get presets
// -----------
$category = 0;
if(!empty($_GET['category'])) {
	$category = $_GET['category'];
}

$event = 0;
if(!empty($_GET['event'])) {
	$event = $_GET['event'];
}

$round = 0;
if(!empty($_GET['round'])) {
	$round = $_GET['round'];   
}
  
  
$catFrom = 0;
if(!empty($_GET['catFrom'])) {
	$catFrom = $_GET['catFrom'];
}
$catTo = 0;
if(!empty($_GET['catTo'])) {
	$catTo = $_GET['catTo'];
}
$discFrom = 0;
if(!empty($_GET['discFrom'])) {
	$discFrom = $_GET['discFrom'];
}
$discTo = 0;
if(!empty($_GET['discTo'])) {
	$discTo = $_GET['discTo'];
} 
$heatFrom = 0;
if(!empty($_GET['heatFrom'])) {
	$heatFrom = $_GET['heatFrom'];
}
$heatTo = 0;
if(!empty($_GET['heatTo'])) {
	$heatTo = $_GET['heatTo'];
}     
 
$type = 'single';
if(!empty($_GET['type'])) {
	$type = $_GET['type'];
}

$heatSeparate = false;     
if($_GET['heatSeparate'] == "yes"){  
    $heatSeparate = true;
}      

$withStartnr = false;     
if($_GET['withStartnr'] == "yes"){  
    $withStartnr = true;
}      

$ranklistAll = false;     
if($_GET['ranklistAll'] == "yes"){  
    $ranklistAll = true;
}      

$team = 'ranking';
if(!empty($_GET['team'])) {
	$team = $_GET['team'];
}

$date = '%';
if(isset($_GET['date']) && !empty($_GET['date'])) {
	$date = $_GET['date'];
}

$cover = FALSE;
$cover_timing = false;
if($_GET['cover'] == 'cover') {
	$cover = TRUE;
	$cover_timing = (isset($_GET['cover_timing']));
}

$formaction = 'view';
if(!empty($_GET['formaction'])) {
	$formaction = $_GET['formaction'];
}

$break = 'none';
if(!empty($_GET['break'])) {
	$break = $_GET['break'];
}

$biglist = false;
if($type == "single_attempts"){
	$type = "single";
	$biglist = true;
}

$sepu23 = false;
if($_GET['sepu23'] == "yes"){
	$sepu23 = true;
}                     

$disc_nr = 99;
if(!empty($_GET['disc_nr'])){
	$disc_nr = $_GET['disc_nr'];
}                  

$show_efforts = 'none';
if(!empty($_GET['show_efforts'])){
	$show_efforts = $_GET['show_efforts'];
}

$athleteCat = false;     
if($_GET['athleteCat'] == "yes"){  
    $athleteCat = true;
}   

$show_ukc = false;     
if($_GET['show_ukc'] == "ukc"){  
    $show_ukc = true;
}         
    
// Ranking list single event
if($type == 'single')
{     
	AA_rankinglist_Single($category, $event, $round, $formaction, $break, $cover, $biglist, $cover_timing, $date, $show_efforts,$heatSeparate,$catFrom,$catTo,$discFrom,$discTo,$heatFrom,$heatTo,$athleteCat , $withStartnr, $ranklistAll, $show_ukc);
}                                                                                                                                                                                                     

// Ranking list combined events
else if($type == 'combined' || $type  == 'ukc')
{   
    if ($type == 'ukc'){
        $show_ukc = true;
    }   
    else {
           $show_ukc = false;   
    }                                                                                                      
	AA_rankinglist_Combined($category, $formaction, $break, $cover, $sepu23, $cover_timing, $date, $disc_nr,$catFrom,$catTo, $show_ukc);
}                                                                                                  

// Ranking list teams events
else if($type == 'team' || $type == 'teamAll' || $type == 'teamP')
{   
    @AA_rankinglist_Team($category, $formaction, $break, $cover, $parser=false, $event, $heatSeparate,$type, $catFrom, $catTo);  
}                                                                                                                   

// Team sheets
else if($type == 'sheets')
{  
	AA_rankinglist_Sheets($category, $event, $formaction, $cover,'',$heatSeparate, $catFrom, $catTo, $discFrom,$discTo);
}

// Ranking list team sm events
else if($type == 'teamsm')
{  
	AA_rankinglist_TeamSM($category, $event, $formaction, $break, $cover, $cover_timing, $date);
}                                         

?>
