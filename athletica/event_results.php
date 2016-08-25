<?php

/**********
 *
 *	event_results.php
 *	-----------------
 *	
 */
	
require('./lib/common.lib.php');
require('./lib/heats.lib.php');
require('./lib/results.lib.php');
require('./lib/results_track.lib.php');
require('./lib/results_tech.lib.php');
require('./lib/results_high.lib.php');
require('./lib/utils.lib.php');
require('./lib/timing.lib.php');  


if(AA_connectToDB() == FALSE)	{			// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$round = 0;
if(!empty($_GET['round'])){
	$round = $_GET['round'];
}
else if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}

$singleRound=0;
if(!empty($_POST['singleRound'])){
    $singleRound = $_POST['singleRound'];
}
  
//
// update rank manually
//
if($_POST['arg'] == 'save_rank')
{
	if((!empty($_POST['rank'])) || ($_POST['rank']==0))
	{   
		mysql_query("LOCK TABLES serienstart WRITE");

		mysql_query("UPDATE serienstart SET"
					. " Rang = " . $_POST['rank']
					. ", Qualifikation = 0"
					. " WHERE xSerienstart=" . $_POST['item']);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}

		mysql_query("UNLOCK TABLES");
	}
	else
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
	
	AA_results_resetQualification($round);
}

//
// save new qualification parameters
//
else if($_POST['arg'] == 'set_qual')
{
	$qual_top = 0;
	if(!empty($_POST['qual_top'])) {
		$qual_top = $_POST['qual_top'];
	}

	$qual_perf = 0;
	if(!empty($_POST['qual_perf'])) {
		$qual_perf = $_POST['qual_perf'];
	}
  
	mysql_query("LOCK TABLES runde WRITE");

	mysql_query("UPDATE runde SET"
				. " QualifikationSieger = " . $qual_top
				. ", QualifikationLeistung = " . $qual_perf
				. " WHERE xRunde=" . $round);

	if(mysql_errno() > 0) {
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}

	mysql_query("UNLOCK TABLES");
	
	AA_results_resetQualification($round);
}

//
// change athlete's qualification
//
else if($_POST['arg'] == 'change_qual')
{
	if((!empty($_POST['qual'])) || ($_POST['qual']==0))
	{   
		mysql_query("LOCK TABLES serienstart WRITE, resultat READ, serie READ");

		$sql = "UPDATE serienstart 
				   SET Qualifikation = ".$_POST['qual']." 
				 WHERE xSerienstart = ".$_POST['item'].";";
		
		mysql_query($sql);

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		
		//
		// if flag waived was set, search for next best athlete to qualify
		//
		if($_POST['qual'] == $cfgQualificationType['waived']['code']){
			/*if($_POST['oldqual'] == $cfgQualificationType['top']['code'] //.. on rank in same heat
				|| $_POST['oldqual'] == $cfgQualificationType['top_rand']['code']){
				
				$result = mysql_query("SELECT
								xSerienstart
							FROM 
								serienstart
							WHERE
								xSerie = ".$_POST['heat']."
							AND	Qualifikation = 0
							AND	Rang > 0
							ORDER BY
								Rang ASC
							LIMIT 1");
				
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					if(mysql_num_rows($result) > 0){
						$row = mysql_fetch_array($result);
						//echo "found: ".$row[0]." in ".$_POST['heat'];
						mysql_query("UPDATE serienstart SET"
								. " Qualifikation = " . $cfgQualificationType['top_rand']['code']
								. " WHERE xSerienstart=" . $row[0]);
					}
				}
				
			}else
            */
            if($_POST['oldqual'] == $cfgQualificationType['perf']['code'] //.. on performance
				|| $_POST['oldqual'] == $cfgQualificationType['perf_rand']['code']
                || $_POST['oldqual'] == $cfgQualificationType['top']['code'] //.. on rank in same heat
                || $_POST['oldqual'] == $cfgQualificationType['top_rand']['code']){
                
                
                
					$sql = "SELECT 
								  serienstart.xSerienstart
								, resultat.Leistung
								, serienstart.Qualifikation
							FROM
								resultat
							LEFT JOIN
								serienstart USING(xSerienstart)
							LEFT JOIN 
								serie USING(xSerie)
							WHERE 
								resultat.Leistung > 0
							AND
								serienstart.Qualifikation = 0
							AND
								serie.xRunde = ".$round."
							ORDER BY
								  resultat.Leistung ASC 
								, RAND();";
					$result = mysql_query($sql);
				
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}else{
					if(mysql_num_rows($result) > 0){
						$row = mysql_fetch_array($result);
						mysql_query("UPDATE serienstart SET"
								. " Qualifikation = " . $cfgQualificationType['perf_rand']['code']
								. " WHERE xSerienstart=" . $row[0]);
					}
				}
				
			}else{
				AA_printErrorMsg($strErrAthleteNotYetQualified);
			}
		}
		
		mysql_query("UNLOCK TABLES");
	}
	else
	{
		AA_printErrorMsg($strErrEmptyFields);
	}
}

// change heat ID
else if($_POST['arg'] == 'change_heat_name') {		// change heat id
	AA_heats_changeHeatName($round);
	if(!empty($GLOBALS['AA_ERROR'])) {
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
	}
}

else if($_GET['arg'] == 'change_results') {	// change results (after ranking)
	AA_utils_changeRoundStatus($round, $cfgRoundStatus['results_in_progress']);
	if(!empty($GLOBALS['AA_ERROR'])) {
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
	}
}

else if($_POST['arg'] == 'add_start') {	// add new athlete/relay
	AA_heats_addStart($round);
	if(!empty($GLOBALS['AA_ERROR'])) {
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
	}            
    AA_timing_setStartInfo($round);
}

else if($_GET['arg'] == 'del_start') {	// delete athlete/relay  
	AA_heats_deleteStart();
	if(!empty($GLOBALS['AA_ERROR'])) {
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
	}
     AA_timing_setStartInfo($round);
}

elseif($_GET['arg'] == "del_results"){ // delete all results
	
	AA_results_deleteResults($round);
	
}
elseif($_GET['arg'] == "live_results"){ // reset status live
                             
    AA_utils_changeRoundStatus($round, $GLOBALS['cfgRoundStatus']['results_in_progress']);
    
}
 
//
//	form layout (depending on discipline type)
//

$layout = AA_getDisciplineType($round);	// type determines layout

// track disciplines, with or without wind
if(($layout == $cfgDisciplineType[$strDiscTypeNone])
	|| ($layout == $cfgDisciplineType[$strDiscTypeTrack])
	|| ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
	|| ($layout == $cfgDisciplineType[$strDiscTypeDistance])
	|| ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
{    
	AA_results_Track($round, $layout);
}
// technical disciplines, with or withour wind
else if(($layout == $cfgDisciplineType[$strDiscTypeJump])
	|| ($layout == $cfgDisciplineType[$strDiscTypeJumpNoWind])
	|| ($layout == $cfgDisciplineType[$strDiscTypeThrow]))
{
	AA_results_Tech($round, $layout);
	//AA_results_High($round, $layout);
}
// high jump, pole vault
else if($layout == $cfgDisciplineType[$strDiscTypeHigh])
{
	AA_results_High($round, $layout, $singleRound);
}

 //  }
 //  else {    
 //       AA_printErrorMsg($strErrMergedRound); 
 //   } 
