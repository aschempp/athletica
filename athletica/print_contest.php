<?php

/**********
 *
 *	print_contest.php
 *	-----------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_print_contest.lib.php');
require('./lib/cl_print_contest_pdf.lib.php');
require('./lib/timing.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}


  
$onlyBest = 'n';     
if($_POST['onlyBest'] == 'y'){  
    $onlyBest = 'y';
} 
   
// get presets
// -----------
$round = 0;
if(!empty($_POST['round'])) {
	$round = $_POST['round'];
}

$tracks = 0;
if(!empty($_POST['tracks'])) {
	$tracks = $_POST['tracks'];
}

$next_round = 0;
if(!empty($_POST['next_round'])) {
	$next_round = $_POST['next_round'];
}

$qual_top = 0;
if(!empty($_POST['qual_top'])) {
	$qual_top = $_POST['qual_top'];
}

$qual_perf = 0;
if(!empty($_POST['qual_perf'])) {
	$qual_perf = $_POST['qual_perf'];
}

$print = 'no';
if(!empty($_POST['print'])) {
    $print = $_POST['print'];
}

$dTyp= 0;
if (isset($_POST['d_Typ'])){
       $dTyp = $_POST['d_Typ'];
}

$teamsm = false;
if($_POST['w_Typ'] == $cfgEventType[$strEventTypeTeamSM]){
    $teamsm = true;
}
 
$endEvent = 0;
$countFinalist = 0;
$countFinalAfter = 0;
$changePos1 = 0;  
$changePos2 = 0; 
if(isset($_POST['endEvent'])) {
    $endEvent = 1;
    $countFinalist = $cfgFinalist;  
    $countFinalAfter = ceil($cfgCountAttempts[$dTyp]/2);  
    $changePos1 = $countFinalAfter;   
}

if(isset($_POST['orig_CountFinalist']) && $_POST['orig_CountFinalist'] > 0) {
       $countFinalist = $_POST['orig_CountFinalist'];
}   

if(isset($_POST['countFinalAfter']) && $_POST['orig_CountFinalist'] > 0) {
       $countFinalAfter = $_POST['countFinalAfter'];
}  

if(isset($_POST['changePos1'])) {
       $changePos1 = $_POST['changePos1'];
}

if(isset($_POST['changePos2'])) {
       $changePos2 = $_POST['changePos2'];
}
if ($changePos1 == '-'){
    $changePos1 =0;
}
if ($changePos2 == '-'){
    $changePos2 =0;
}
 $changePos = $changePos1 .",". $changePos2;

AA_utils_changeRoundStatus($round, $cfgRoundStatus['heats_done']);
if(!empty($GLOBALS['AA_ERROR'])) {
	AA_printErrorMsg($GLOBALS['AA_ERROR']);
}

AA_utils_logRoundEvent($round, $strHeatsPrinted);
                                   

//
// get nbr of heats
// ----------------
$res = mysql_query("SELECT COUNT(*)"
						. " FROM serie"
						. " WHERE xRunde = $round");

if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else {
	$row = mysql_fetch_row($res);
	$tot_heats = $row[0];
}
mysql_free_result($res);            
 

//
// Update qualification parameters
// -------------------------------

mysql_query("LOCK TABLES runde WRITE");
                 
  $sql   ="UPDATE
        runde
    SET
        QualifikationSieger = $qual_top
        , QualifikationLeistung = $qual_perf
        , Bahnen = $tracks
        , Endkampf  =  '$endEvent'
        , Finalisten  =  $countFinalist
        , FinalNach =  $countFinalAfter
        , Drehen = '$changePos'
    WHERE xRunde = $round"; 
    
mysql_query($sql); 

if(mysql_errno() > 0)		// DB error
{   
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

mysql_query("UNLOCK TABLES");

//
// Content
// -------

$mRounds= AA_getMergedRounds($round);
$sqlRound = '';
if (empty($mRounds)){
   $sqlRound = "= ". $round;  
}
else {
     $sqlRound = "IN ". $mRounds;  
}
// get round info           
$sql = "SELECT 
                DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
                , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                , r.Bahnen
                , rt.Name
                , w.xWettkampf
                , k.Name
                , d.Name
                , d.Typ
                , w.Windmessung
                , w.Info
                , rt.Typ
                , d.Staffellaeufer
                , r.Gruppe
                , w.Zeitmessung
                , TIME_FORMAT(r.Appellzeit, '$cfgDBtimeFormat')
                , TIME_FORMAT(r.Stellzeit, '$cfgDBtimeFormat')
                , w.Mehrkampfcode                
                , d1.Name
         FROM 
                runde AS r
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d1 ON (d1.Code = w.Mehrkampfcode)
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                
         WHERE 
                r.xRunde " . $sqlRound;    
 
$result = mysql_query($sql);       

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{
	
	$combined = AA_checkCombined(0, $round);
	$svm = AA_checkSVM(0, $round); // decide whether to show club or team name   
    
    if (!empty($mRounds)){        
        while ($row = mysql_fetch_row($result)) {
            $catMerged .= $row[5]. " / ";
            $infoMerged .= $row[9]. " / ";
        }   
        $titel = substr($catMerged,0,-2);  
    }
    
    $result = mysql_query($sql);  
	$row = mysql_fetch_row($result);
	
	// remember staffell?ufer
	$maxRunners = $row[11];
	
	// get attempts for tech disc
	if($_POST['countattempts'] == ""){
		$_POST['countattempts'] = $cfgCountAttempts[$row[7]];
	}
	if($row[7] == $cfgDisciplineType[$strDiscTypeJump]
		|| $row[7] == $cfgDisciplineType[$strDiscTypeJumpNoWind]
		|| $row[7] == $cfgDisciplineType[$strDiscTypeThrow])
	{
		mysql_query("
			UPDATE
				runde
			SET
				Versuche = ".$_POST['countattempts']."
                , nurBestesResultat = '".$onlyBest."'  
			WHERE xRunde = $round
		");
		
		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
	}

	$relay = AA_checkRelay($row[4]);	// check, if this is a relay event
	
	$layout = $row[7];			// sheet layout type
	
	$silent = ($row[13]==0);
	$wind=$row[8];
   
	switch($layout) {
		case($cfgDisciplineType[$strDiscTypeNone]):
			$doc = new PRINT_Contest_pdf($_COOKIE['meeting']);
		case($cfgDisciplineType[$strDiscTypeTrack]):
            if ($print == 'no') {
			    AA_timing_setStartInfo($round, $silent); // set timing if new heats, not if print startlist only
            }
			if($row[8] == 1) {
				$doc = new PRINT_ContestTrack_pdf($_COOKIE['meeting']);
			}
			else {
				$doc = new PRINT_ContestTrackNoWind_pdf($_COOKIE['meeting']);
			}
			break;
		case($cfgDisciplineType[$strDiscTypeTrackNoWind]):
		case($cfgDisciplineType[$strDiscTypeDistance]):
            if ($print == 'no') {
			    AA_timing_setStartInfo($round, $silent); // set timing if new heats, not if print startlist only
            }
			$doc = new PRINT_ContestTrackNoWind_pdf($_COOKIE['meeting']);
			break;
		case($cfgDisciplineType[$strDiscTypeRelay]):
            if ($print == 'no') {
			    AA_timing_setStartInfo($round, $silent); // set timing if new heats, not if print startlist only
            }
			$doc = new PRINT_ContestRelay_pdf($_COOKIE['meeting']);
			break;
		case($cfgDisciplineType[$strDiscTypeJump]):
			if($row[8] == 1) {
				$doc = new PRINT_ContestTech_pdf($_COOKIE['meeting']);
			}
			else {
				$doc = new PRINT_ContestTechNoWind_pdf($_COOKIE['meeting']);
			}
			break;
		case($cfgDisciplineType[$strDiscTypeJumpNoWind]):
			$doc = new PRINT_ContestTechNoWind_pdf($_COOKIE['meeting']);
			break;
		case($cfgDisciplineType[$strDiscTypeThrow]):
			$doc = new PRINT_ContestTechNoWind_pdf($_COOKIE['meeting']);
			break;
		case($cfgDisciplineType[$strDiscTypeHigh]):
			$doc = new PRINT_ContestHigh_pdf($_COOKIE['meeting']);
			$doc->resultinfo = "X = $strHighInvalid, O = $strHighValid";
			break;
	}
    
   
    if (empty($mRounds)){
        $doc->cat = "$row[5]";   
    }
    else {  
         $doc->cat = "$titel";  
    }        
	
	if(true || $row[10] == '0'){ // do not show "(ohne)"
		$doc->event = "$row[6]";
	}else{
		if($combined && !empty($row[12])){
			$doc->event = "$row[6] $row[3] G$row[12]";
		}else{
			$doc->event = "$row[6] $row[3]";
		}
	}
    if ($row[16] > 0){                                                                                // combined events
	    if ($row[16] == 796 || $row[16] == 797 || $row[16] == 798|| $row[16] == 799 ){
            $mkName3L =substr("$row[17]",0, 3);
            if ($mkName3L ==  "..."){
                $doc->info = "";                         // do not show ...kampf etc. 
            }
            else {
                 $doc->info = "$row[17]";               // discipline Name
            }
        }
        else {
            $doc->info = "$row[17]";                    // discipline Name
        }
    }
    else {
        if (!empty($infoMerged)){
            $doc->info = $infoMerged;                // wettkampf.info merged
        }
        else {
              $doc->info = "$row[9]";                        // wettkampf.info
        }
      
         
    }
	
	
	$et = '';	// set enrolement and manipulation time
	if($row[14] != '00:00'){
		//$et = "($strEnrolementTime $row[14]";
	}
	if($row[15] != '00:00'){
		//$et .= ", $strManipulationTime $row[15]";
	}
	if(!empty($et)){ $et.=")"; }
	$doc->time = "$row[0], $row[1]";
	$doc->timeinfo = $et;
	
	$doc->printHeaderLine();

	// set up qualification parameters for next round, if any
	// ------------------------------------------------------
	$qual_info = '';
	if($next_round > 0)
	{
		$res = mysql_query("SELECT rt.Name"
										. " FROM runde AS r"
										. " LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt"
										. " ON r.xRundentyp = rt.xRundentyp"
										. " WHERE r.xRunde = $next_round");

		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{

			$row = mysql_fetch_row($res);
			$qual_info = "&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;"
							. "$strQualification $row[0]:"
							. " $qual_top $strQualifyTop + "
							. " $qual_perf $strQualifyPerformance";
		}
		mysql_free_result($res);
	}		// ET DB error

	mysql_free_result($result);

    // check if round is final     
    $sql_r="SELECT 
                    rt.Typ
            FROM
                    runde as r
                    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " as rt USING (xRundentyp)
            WHERE
                    r.xRunde=" .$round;
    $res_r = mysql_query($sql_r);
       
    if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
        
    $order="ASC";   
    if (mysql_num_rows($res_r) == 1) {
        $row_r=mysql_fetch_row($res_r);  
        if ($row_r[0]=='F'){
            $order="DESC";  
        }
    }
        
	// read round data
	if($round > 0)
	{
		// display all heats
		if($relay == FALSE) {		// single event
			if ($teamsm){
                 $query = "SELECT DISTINCT
                            r.Bahnen
                            , rt.Name
                            , rt.Typ
                            , s.Bezeichnung
                            , ss.Position
                            , an.Bezeichnung
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang
                            , t.Name
                            , LPAD(s.Bezeichnung,5,'0') as heatid
                            , ss.Bahn
                            , s.Film
                            , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land  
                     FROM 
                            runde AS r
                            LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                            INNER JOIN teamsmathlet as tat ON (a.xAnmeldung = tat.xAnmeldung)
                            LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)
                            LEFT JOIN region AS re ON at.xRegion = re.xRegion  
                    WHERE 
                            r.xRunde = " . $round ."                      
                     ORDER BY heatid ". $order." , ss.Position";                           
                   
            }
            else {
               $query = "SELECT 
                            r.Bahnen
                            , rt.Name
                            , rt.Typ
                            , s.Bezeichnung
                            , ss.Position
                            , an.Bezeichnung
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang
                            , if('$svm', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))
                            , LPAD(s.Bezeichnung,5,'0') as heatid
                            , ss.Bahn
                            , s.Film
                            , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land  
                     FROM 
                            runde AS r
                            LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                            LEFT JOIN team as t ON a.xTeam = t.xTeam
                            LEFT JOIN region AS re ON at.xRegion = re.xRegion  
                    WHERE 
                            r.xRunde = " . $round ."                      
                     ORDER BY heatid ". $order." , ss.Position";        
            }
            
                     
		}
		else {								// relay event
			
            $query = "SELECT 
                            r.Bahnen
                            , rt.Name
                            , rt.Typ
                            , s.Bezeichnung
                            , ss.Position
                            , an.Bezeichnung
                            , sf.xStaffel
                            , sf.Name
                            , if('$svm', t.Name, v.Name)
                            , LPAD(s.Bezeichnung,5,'0') as heatid
                            , r.xRunde
                            , s.Film
                            , sf.Startnummer
                            , ss.RundeZusammen   
                     FROM 
                            runde AS r
                            LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN staffel AS sf ON (sf.xStaffel = st.xStaffel )
                            LEFT JOIN verein AS v ON (v.xVerein = sf.xVerein  )
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                            LEFT JOIN team AS t ON sf.xTeam = t.xTeam
                     WHERE 
                            r.xRunde = " . $round ."                        
                     ORDER BY heatid ". $order.", ss.Position";    
                     
		}  
        
        $result = mysql_query($query); 
		
		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			// set up free text line (statistical info)
			// ----------------------------------------
			if($relay == FALSE) {
				$doc->setFreetxt(mysql_num_rows($result) . " " . $strAthletes);
			}
			else {
				$doc->setFreetxt(mysql_num_rows($result) . " " . $strRelays);
			}

			$doc->addFreetxt(", $tot_heats ");
			
			$actStrHeats = ($tot_heats==1) ? $strHeat : $strHeats;

			if(!empty($qual_info)) {
				$doc->addFreetxt("$actStrHeats $qual_info");
			}
			else {
				$doc->addFreetxt("$actStrHeats");
			}

			$doc->printFreeTxt();
           
			//
			// track disciplines
			// -----------------
			switch($layout)
			{
			case($cfgDisciplineType[$strDiscTypeTrack]):
			case($cfgDisciplineType[$strDiscTypeTrackNoWind]):
			case($cfgDisciplineType[$strDiscTypeRelay]):
			case($cfgDisciplineType[$strDiscTypeDistance]):
				$b = 0;		// initialize track nbr (numeric)
				$h = 0;		// initialize heat counter
				$id = '';	// initialize heat ID (alphanumeric)
				$tracks = 0;

				while($row = mysql_fetch_row($result))
				{
					if($relay){
						$filmnr = $row[11];
					}else{
						$filmnr = $row[13];
					}
					$tracks = $row[0];	// keep nbr of planned tracks
					$b++;						// current track
					
					if(($id != $row[3]) || ($h == 0))		// new heat
					{
						// fill old heat with empty tracks
						while(($b > 1) && ($b <= $tracks))
						{
							$doc->printHeatLine($b, $strEmpty);
							$b++;
						}
		                
						if($h != 0)	{		// not first heat
							$doc->printEndHeat();

							// page break if:
							// - 9th athlete/heat
							// - or after two heats
							// - or after each heat if relay
							// - or page break per heat is selected
							
						   
							if(($b > 9) || ($h % 2 == 0) || ($layout == 3) || $_POST['heatpagebreak'] == "yes") {                                                        
									$doc->insertPageBreak();
							}
						}                                   
						
						$b = 1;						// (re-)start with track one
						if(is_null($row[1]))		// only one round
						{
							$heat = "$strFinalround $row[3]";
						}
						else
						{
							if($row[2] == '0'){ // do not show "(ohne)"
								$heat = "$strHeat $row[3]";
							}else{
								$heat = "$row[1] $strHeat $row[3]";
							}
						}

						$doc->printHeatTitle($heat, $row[5], $filmnr);
						$doc->printStartHeat($svm, $teamsm);

						$id = $row[3];
						$h++;			// nbr of heats
					}
					else if($b % 30 == 0)	// after 30 athletes in this heat
					{
						// insert page break an repeat heat info
						$doc->printEndHeat();                        
						$doc->insertPageBreak();
						$doc->printHeatTitle("$heat $strCont", $row[5], $filmnr);
						$doc->printStartHeat($svm, $teamsm);
					}


					// show empty track if current track and position not identical
					while($b < $row[4])
					{
						$doc->printHeatLine($b, $strEmpty);
						$b++;
					}
                    
					if($relay == FALSE) {
						$doc->printHeatLine($row[4], $row[6], "$row[7] $row[8]"
								, AA_formatYearOfBirth($row[9]), $row[10], $row[12], $row[14]);
					}
					else
					{	
						$team = $row[7];		// relay name
                        if ($row[13] > 0)
                            $sqlRound=$row[13];     // merged round
                        else
                            $sqlRound=$row[10]; 
                            
						// get the relay athletes   
                        $sql = "SELECT 
                                    at.Name
                                    , at.Vorname
                                    , sta.Position
                                    , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land
                                    , a.Startnummer                                
                                FROM 
                                        athlet AS at
                                        LEFT JOIN anmeldung AS a ON (a.xAthlet = at.xAthlet)                                                 
                                        LEFT JOIN start AS st ON (st.xAnmeldung = a.xAnmeldung)
                                        LEFT JOIN staffelathlet AS sta  ON (sta.xAthletenstart = st.xStart)
                                        LEFT JOIN start AS ss ON (sta.xStaffelstart = ss.xStart)                                           
                                        LEFT JOIN region AS re On (at.xRegion = re.xRegion) 
                                WHERE 
                                        ss.xStaffel = " . $row[6] ."                                          
                                        AND sta.xRunde = ". $sqlRound ."
                                ORDER BY sta.Position
                                                    LIMIT $maxRunners";    
                        
                        $res = mysql_query($sql);          
                        
						if(mysql_errno() > 0)		// DB error
						{
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
						else
						{

							while($athl_row = mysql_fetch_row($res))
							{   
								$team = $team . "<br />&nbsp;&nbsp;&nbsp;"
										. $athl_row[2] . ". "
										. $athl_row[0] . " "                                        
                                        . $athl_row[1] . ", Nr. "
                                        . $athl_row[4]
                                        . (($athl_row[3]!='' && $athl_row[3]!='-') ? ', '.$athl_row[3] : '');
							}
							mysql_free_result($res);
						}    
                        $doc->printHeatLine($row[4], $row[12].". ".$team, $row[8]);  
					}
				}

				// fill last heat with empty tracks
				$b++;
				while(($b > 1) && ($b <= $tracks))
				{
					$doc->printHeatLine($b, $strEmpty);
					$b++;
				}

				$doc->printEndHeat();		// terminate last heat
				break;

			//
			// technical disciplines
			// ---------------------
			case($cfgDisciplineType[$strDiscTypeJump]):
			case($cfgDisciplineType[$strDiscTypeJumpNoWind]):
			case($cfgDisciplineType[$strDiscTypeThrow]):
			case($cfgDisciplineType[$strDiscTypeHigh]):
			case($cfgDisciplineType[$strDiscTypeNone]):
				$b = 0;		// initialize track nbr (numeric)
				$h = 0;		// initialize heat counter 
				$id = '';		// initialize heat ID (alphanumeric)

				while($row = mysql_fetch_row($result))
				{
					$b++;						// current athlete
					
					// new heat
					if(($id != $row[3]) || ($h == 0))
					{
						if($h != 0)	{		// not first heat          
							$doc->printEndHeat();                            
							$doc->insertPageBreak();
						}

						$b = 1;						// (re-)start with track one
						if(is_null($row[1]))		// only one round
						{
							$heat = "$strFinalround $row[3]";
						}
						else
						{
							if($row[2] == 0){ // do not show "(ohne)"
								$heat = "$strHeat $row[3]";
							}else{
								$heat = "$row[1] $strHeat $row[3]";
							}
						}

						$doc->printHeatTitle($heat, $row[5]);

						$h++;
						$id = $row[3];
					}
					// new page after 8 athl. (tech) or 10 athl. (tech, no wind)
			
			       else if((($layout == 4) && ($b > 6) && ($wind==1) ) 
                            || ($layout == 4) && ($b > 9) && ($wind==0)
                            || ($layout == 6) && ($b > 9)
							|| ($b > 10))
					   
					{     
						// insert page break an repeat heat info
						$doc->printEndHeat();
					   
						$doc->insertPageBreak();  
						
						$doc->printHeatTitle("$heat $strCont", $row[5]);
						$b = 1;
					}
                   
					$doc->printHeatLine($row[6], "$row[7] $row[8]"
							, AA_formatYearOfBirth($row[9]), $row[10], $row[14]);
				}
				break;
			}		// end switch "Layout"

			mysql_free_result($result);
		}		// ET DB error
	}		// ET round selected

	$doc->endPage();
}		// ET round data found
?>

