<?php

/**********
 *
 *	timing handling functions
 *	-------------------------
 *	
 */

if (defined('AA_TIMING_LIB_INCLUDED'))
{
	return;
}
define('AA_TIMING_LIB_INCLUDED', 1);

require("./lib/common.lib.php");
require("./lib/cl_omega.lib.php");
require("./lib/cl_alge.lib.php");
require('./config.inc.php');  

/**
 * get results from timing automaticaly (on reload of event monitor)
 * --> supress errors
 * --> change round status
 * --> import results only if not already entered
 * for ALGE this is experimental
 */
function AA_timing_getResultsAuto($round){
	
	$timing = AA_timing_getTiming();
	if($timing == "omega"){
		
		AA_results_getTimingOmega($round, true, true);
		
	}elseif($timing == "alge"){
		
		AA_results_getTimingAlge($round, true, true);
		
	}
	
}

/**
 * get results from timing on user demand
 * --> change round status
 * --> import results
 */
function AA_timing_getResultsManual($round){
	
	$timing = AA_timing_getTiming();
	if($timing == "omega"){
		
		AA_results_getTimingOmega($round, false, false);
		
	}elseif($timing == "alge"){
		
		AA_results_getTimingAlge($round, false, false);
		
	}else{
		AA_printErrorMsg($GLOBALS['strErrTimingNotConfigured']);
	}
	
}

/**
 * return timing type of meeting
 * (currently 'no', 'omega', 'alge')
 *
 */
function AA_timing_getTiming(){
	
	$res = mysql_query("SELECT Zeitmessung FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		
		$row = mysql_fetch_array($res);
		return $row[0];
		
	}
}


function AA_timing_getConfiguration(){
	
	$obj = null;
	$timing = AA_timing_getTiming();
	if($timing == "omega"){
		
		$obj = new omega();
		
	}elseif($timing == "alge"){
		
		$obj = new alge();
		
	}
	
	return $obj;
}


function AA_timing_saveConfiguration(){
	
	$obj = null;
	$timing = AA_timing_getTiming();
	if($timing == "omega"){
		
		$obj = new omega();
		$obj->set_configuration($_COOKIE['meeting_id']);
		
	}elseif($timing == "alge"){
		
		$obj = new alge();
		$obj->set_configuration($_COOKIE['meeting_id']);
		
	}
	
	return $obj;
}

/**
 * export start information on round for timing software
 *
 */
function AA_timing_setStartInfo($round, $silent = false){
	
	$timing = AA_timing_getTiming();
	if($timing == "omega"){
		
		$omega = new omega();
		$omega->set_allFiles();
		
	}elseif($timing == "alge"){
		
		$alge = new alge();
		$alge->export_round($round);
		
	}else{
		if(!$silent) AA_printErrorMsg($GLOBALS['strErrTimingNotConfigured']);
	}
	
}

/**
 * set timing type of meeting
 * (currently 'no', 'omega', 'alge')
 *
 */
function AA_timing_setTiming($system){
    
    if ($system == 'no'){
       $autorank = " , AutoRangieren = 'n' ";  
    }
	
	mysql_query("UPDATE meeting SET Zeitmessung = '$system' $autorank WHERE xMeeting = ".$_COOKIE['meeting_id']);
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}
    $t1=$GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']];
    $t2=$cfgDisciplineType[$strDiscTypeTrackNoWind];
    
    if ($system == 'no'){
        $timing = 0;        // remove timing and timing auto for all tracks 
    }
    else {
         $timing = 1;      // set timing and timing auto for all tracks 
    }
   
    $sql="SELECT 
                w.xWettkampf 
          FROM 
                wettkampf as w
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
          WHERE 
                d.Typ IN ( ". $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']] ."," 
                . $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']] .","
                . $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']] ."," 
                . $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeDistance']] .")  
                AND w.xMeeting = " . $_COOKIE['meeting_id'];              
            
    $res=mysql_query($sql);
    if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
    }
    else {
             while($row=mysql_fetch_array($res)){
                 mysql_query("UPDATE wettkampf SET Zeitmessung = " . $timing .", ZeitmessungAuto = " . $timing . " WHERE xWettkampf = " 
                                . $row[0] ." AND xMeeting = ".$_COOKIE['meeting_id']);  
                 
                 if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno().": ".mysql_error());
                 }
             } 
        }  
}

function AA_timing_setAutoRank($autorank) {   
    if ($autorank == 'no'){
       $autorank='y';
    }     
    else {  
        $autorank='n';  
    }
    
    mysql_query("UPDATE meeting SET AutoRangieren = '$autorank' WHERE xMeeting = ".$_COOKIE['meeting_id']);
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno().": ".mysql_error());
    } 
}

function AA_timing_getAutoRank() {
    $autorank = '';
    $res=mysql_query("SELECT AutoRangieren FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno().": ".mysql_error());
    }
    else {
        $row=mysql_fetch_row($res); 
        $autorank =  $row[0];  
    }
    return $autorank;   
}

?>
