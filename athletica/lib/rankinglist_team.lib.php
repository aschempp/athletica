<?php

/**********
 *
 *	rankinglist team events
 *	
 */

if (!defined('AA_RANKINGLIST_TEAM_LIB_INCLUDED'))
{
	define('AA_RANKINGLIST_TEAM_LIB_INCLUDED', 1);

 
function AA_rankinglist_Team($category, $formaction, $break, $cover, &$parser, $event, $heatSeparate, $type, $catFrom, $catTo)  
{   
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_print_page.lib.php');
require('./lib/cl_print_page_pdf.lib.php');
require('./lib/cl_export_page.lib.php');  

require('./lib/common.lib.php');
require('./lib/results.lib.php');

if(AA_connectToDB() == FALSE)	{ // invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

global $rFrom, $rTo, $limitRank; // limits rank if limitRank set to true
$rFrom = 0; $rTo = 0;
$limitRank = false;
if($_GET['limitRank'] == "yes" && substr($formaction,0,6) == "export"){ // check if ranks are limited
	if(!empty($_GET['limitRankFrom']) && !empty($_GET['limitRankTo'])){
		$limitRank = true;
		$rFrom = $_GET['limitRankFrom'];
		$rTo = $_GET['limitRankTo'];
	}
}

// start a new HTML display page
if($formaction == 'view') {
	$GLOBALS[$list] = new GUI_TeamRankingList($_COOKIE['meeting']);
	$GLOBALS[$list]->printPageTitle("$strRankingLists " . $_COOKIE['meeting']);
}
// catch output and do nothing exept for theam rankings
// these will be added to the xml result file
elseif($formaction == "xml"){
	$GLOBALS['xmladdon'] = true;
	$GLOBALS[$list] = new XML_TeamRankingList($parser);
}
// start a new HTML print page
elseif($formaction == "print") {                  
	$GLOBALS[$list] = new PRINT_TeamRankingList_pdf($_COOKIE['meeting']);
	if($cover == true) {		// print cover page 
		$GLOBALS[$list]->printCover($GLOBALS['strResults']);
	}
}
// export ranking
elseif($formaction == "exportpress"){
	$GLOBALS[$list] = new EXPORT_TeamRankingListPress($_COOKIE['meeting'], 'txt');
}elseif($formaction == "exportdiplom"){
	$GLOBALS[$list] = new EXPORT_TeamRankingListDiplom($_COOKIE['meeting'], 'csv');
}

$selection = ''; 
if($formaction != "xml"){
	if ($event!='')  {
    	$mergedCat=AA_mergedCatEvent($category, $event);  
		}
	else {
    	$mergedCat=AA_mergedCat($category); 
	}
}
  
if(!empty($category)) {        // show every category  
    if ($mergedCat=='') {
        $selection = " AND k.xKategorie = $category";
    }
    else  {
        if ($heatSeparate){ 
            $selection = " AND k.xKategorie = $category"; 
        }
        else { 
            $selection = " AND k.xKategorie IN $mergedCat"; 
        }
    }
} 
   // show category from .... to
if($catFrom > 0) {        
     $getSortCat=AA_getSortCat($catFrom,$catTo);
	 if ($getSortCat[0]) {
	 	if ($catTo > 0){
			$selection = " AND k.Anzeige >=" . $getSortCat[$catFrom] . " AND k.Anzeige <=" . $getSortCat[$catTo] . " "; 
		}	 
		else {
			$selection = "AND k.Anzeige =" . $getSortCat[$catFrom] . " ";
		}
	 }
}  

// evaluation per category
global $cfgEventType, $strEventTypeSingleCombined, $strEventTypeClubMA, 
	$strEventTypeClubMB, $strEventTypeClubMC, $strEventTypeClubFA, 
	$strEventTypeClubFB, $strEventTypeClubBasic, $strEventTypeClubAdvanced, 
	$strEventTypeClubTeam, $strEventTypeClubCombined, $strEventTypeTeamSM;
	 
$results = mysql_query("
	SELECT Distinct
	  	k.xKategorie
	  	, k.Name
		, w.Typ
        , ks.xKategorie_svm
        , ks.Code
  	FROM
	  	wettkampf AS w
	  	LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
        LEFT JOIN kategorie_svm AS ks ON (ks.xKategorie_svm = w.xKategorie_svm)
  	WHERE 
        w.xMeeting = " . $_COOKIE['meeting_id'] ."
	    " . $selection . "   
        AND w.Typ >=  " . $cfgEventType[$strEventTypeClubBasic] ."   
	    AND w.Typ <  " . $cfgEventType[$strEventTypeTeamSM] ."
	
	ORDER BY
		k.Anzeige, ks.Code
");

        
    
if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{              
    mysql_query("DROP TABLE IF EXISTS tmp_team");    // temporary table     
          
    mysql_query("CREATE TEMPORARY TABLE tmp_team(              
                              xKategorie int(11)
                              , xDisziplin int(11)  
                              , Punkte float
                              , xTeam int(11)  
                              )
                              ENGINE=HEAP");       
     if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error()); 
     }
    
	// process all teams per category
	while($row = mysql_fetch_row($results))
	{
		// Club rankinglist:Combined 
		if ($row[2] == $cfgEventType[$strEventTypeClubCombined])
		{  
			processCombined($row[0], $row[1], $type, $row[2]);
		}
		// Club rankinglist: Single
		else
		{    
			processSingle($row[0], $row[1],$row[3], $row[4]);
		}
	} 
    
    mysql_query("DROP TABLE IF EXISTS tmp_team");    // temporary table          
         
	mysql_free_result($results);
}	// ET DB error categories 

$GLOBALS[$list]->endPage();	// end HTML page for printing

}	// end function AA_rankinglist_Team

//
//	process club single events
//

function processSingle($xCategory, $category, $xCat_svm, $cat_svm)
{         	
    $GLOBALS[$rFrom];
    $GLOBALS[$rTo]; 
    $GLOBALS[$limitRank]; 
	require('./config.inc.php');

	mysql_query("
		LOCK TABLES
	  		anmeldung AS a 
  			, disziplin_" . $_COOKIE['language'] . " READ
  			, resultat READ
  			, serienstart READ
  			, staffel READ
  			, start READ
			, team READ
			, verein READ
  			, wettkampf READ
  			, tempresult WRITE
			, kategorie READ
			, athlet as a READ
	");

	// get all teams for this category
	$results = mysql_query("
		SELECT  Distinct
			t.xTeam
			, t.Name
			, v.Name                  
            ,t.xKategorie   
              
            ,t.xKategorie_svm  
		FROM
			team AS t
			LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
            LEFT JOIN wettkampf AS w ON (w.xKategorie = t.xKategorie)
            LEFT JOIN kategorie AS k ON (t.xKategorie = k.xKategorie)
		WHERE t.xMeeting = " . $_COOKIE['meeting_id'] ."
		AND t.xKategorie = $xCategory AND t.xKategorie_svm = '$xCat_svm'
        Order By w.xKategorie_svm");            
     
	if(mysql_errno() > 0) {		// DB error
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		return;
	}

	$tm = 0;			// team
	$info = '';
	$points = 0;
    
	$sep = '';
	$temptable = false;

	// process all teams
	while($row = mysql_fetch_row($results))
	{
        
        $catMkMw = $cfgSVM[$cat_svm."_D"]; 
        if  ($catMkMw  == 'MK' || $catMkMw  == 'MW'){
                     $catMkMw = " $catMkMw";
        }
        else {
              $catMkMw = "";
        }

        
		// store previous before processing new team
		if(($tm != $row[0])		// new team
			&& ($tm > 0))			// first team processed
		{  
			$teamList[] = array(
				"points"=>$points
				, "team"=>$team
				, "club"=>$club
				, "info"=>$info                      
				, "id"=>$tm		// needed for result upload      
			);

			$info = '';
			$points = 0;
			$sep = '';
		}

		// single events
		// -------------        		
        $sql = "
              SELECT
                  d.Kurzname
                  , MAX(r.Punkte) AS pts
                  , w.Typ
                  , d.Typ
                  , k.Code
                  , at.Geschlecht   
              FROM
                  wettkampf AS w
                  LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                  LEFT JOIN start AS st ON (st.xWettkampf = w.xWettkampf )
                  LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart )
                  INNER JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart) 
                  LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                  LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie             )
                  LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)                  
              WHERE 
                  w.xMeeting = " . $_COOKIE['meeting_id'] ."
                  AND w.xKategorie = $xCategory                  
                  AND a.xTeam = $row[0]  
              GROUP BY
                    st.xStart
              ORDER BY
                    d.Anzeige
                    , pts DESC";     
        
        $res = mysql_query($sql);  
	   
		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			$c = 0;
			$d = '';
			$g = 0;
			$p = 0;
			$mixedTeamCount = array('m'=>0, 'w'=>0);               
            
			while($pt_row = mysql_fetch_row($res))
			{   
                                 
				// nbr of athletes to be included in total points
				switch($pt_row[2]) {
					case $cfgEventType[$strEventTypeSVMNL]: // new national league mode since 2007
										// simply 2 athletes per disc and 1 relay
						$a = 2; 
						break;
					case $cfgEventType[$strEventTypeClubBasic]:
						$a = 1;
						break;
					case $cfgEventType[$strEventTypeClubAdvanced]:
						$a = 2;
						break;
					case $cfgEventType[$strEventTypeClubTeam]:
						$a = 5;
						break;
					case $cfgEventType[$strEventTypeClubMixedTeam]:
						$a = 6;
						break;
					case $cfgEventType[$strEventTypeClubMA]: // old NL modes, updated in 2006
					case $cfgEventType[$strEventTypeClubMB]:
					case $cfgEventType[$strEventTypeClubMC]:
					case $cfgEventType[$strEventTypeClubFA]:
					case $cfgEventType[$strEventTypeClubFB]:
						$a = 1;
						if($c == 0) {	// first time here: initialize temp. table
							mysql_query("
								CREATE TABLE tempresult(
									Disziplinengruppe tinyint(4)
									, Punkte smallint(6)
									, Wettkampftyp tinyint(4)
									, Disizplinentyp tinyint(4)
									)
								  ENGINE=HEAP 
							");
						}       
                        
						if(mysql_errno() > 0) {		// DB error
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
						else {
							$temptable = true;
						}
						break;
					default:
						$a = 1;
				}
		
				if($pt_row[0] != $d) 	// new discipline
				{
					// accumulate points of previous event
					if($p > 0) {
						$info = $info . $sep . $d;
						$sep = ", ";
						$points = $points + $p;		// accumulate points
					}
                  
					$c = 0;					// athlete counter
					$p = 0;					// point counter
					$mixedTeamCount = array('m'=>0, 'w'=>0);// mixedteam counter
				}

				if($c < $a)		// add only top ranking athletes
				{
					// special mixed-team schüler svm
					// get 3 men results and 3 women results
					if($pt_row[2] == $cfgEventType[$strEventTypeClubMixedTeam]){
						
						if(empty($pt_row[5])){
							if(substr($pt_row[4],0,1) == 'M' || substr($pt_row[4],3,1) == 'M'){
								$mixedTeamCount['m']++;
								if($mixedTeamCount['m'] > 3){
									continue;
								}
							}else{
								$mixedTeamCount['w']++;
								if($mixedTeamCount['w'] > 3){
									continue;
								}
							}
						}else{
							if($pt_row[5] == "m"){
								$mixedTeamCount['m']++;
								if($mixedTeamCount['m'] > 3){
									continue;
								}
							}else{
								$mixedTeamCount['w']++;
								if($mixedTeamCount['w'] > 3){
									continue;
								}
							}
						}
						
					}
					
					// average points
					if($pt_row[2] == $cfgEventType[$strEventTypeClubAdvanced]) {  
						$p = $p + $pt_row[1] / 2;      
					}elseif($pt_row[2] == $cfgEventType[$strEventTypeSVMNL]) {
						//$p = $p + $pt_row[1] / 2;   
						$p = $p + $pt_row[1];
					}elseif($pt_row[2] == $cfgEventType[$strEventTypeClubMixedTeam]) {                       
						$p = $p + $pt_row[1] / 6;
						$p = round($p, $cfgResultsPointsPrecision);
					}elseif($pt_row[2] == $cfgEventType[$strEventTypeClubTeam]) {                       
						$p = $p + $pt_row[1] / 5;
						$p = round($p, $cfgResultsPointsPrecision);
					}
					
					// total points
					else {
						$p = $p + $pt_row[1];
					}
					
					// last athlete
					if(($c + 1) == $a){
						/*if($pt_row[2] == $cfgEventType[$strEventTypeClubMixedTeam]) {
							$p = $p * ($cfgResultsPointsPrecision * 10);
							$p = floor($p / 6);
							$p = $p/($cfgResultsPointsPrecision * 10);
						}*/
					}
				}
				else if ($temptable == true)	// temp table created
				{
					switch($pt_row[3]) {
						// group 1 = run
						case $cfgDisciplineType[$strDiscTypeTrack]:
						case $cfgDisciplineType[$strDiscTypeTrackNoWind]:
						case $cfgDisciplineType[$strDiscTypeDistance]:
						case $cfgDisciplineType[$strDiscTypeRelay]:
							$g = 1;
							break;
						// group 2 = jump
						case $cfgDisciplineType[$strDiscTypeJump]:
						case $cfgDisciplineType[$strDiscTypeJumpNoWind]:
						case $cfgDisciplineType[$strDiscTypeHigh]:
							$g = 2;
							break;
						// group 3 = throw
						case $cfgDisciplineType[$strDiscTypeThrow]:
							$g = 3;
							break;
						default:
							$g = 4;
					}

					// add result to list for further processing
					mysql_query("
						INSERT INTO tempresult
						VALUES(
							$g
							, $pt_row[1]
							, $pt_row[2]
							, $pt_row[3])
					");

					if(mysql_errno() > 0) {		// DB error
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}

				}

				$c++;
				$d = $pt_row[0];				// keep discipline
                                                  
              
			}	// END WHILE team events
           
			// accumulate points of last event
			if($p > 0) {
				$info = $info . $sep . $d;
				$sep = ", ";
				$points = $points + $p;		// accumulate points
			}

			mysql_free_result($res);
		}

		// relay events
		// ------------  
        $sql = "
              SELECT
                  d.Kurzname
                  , MAX(r.Punkte) AS pts
                  , w.Typ
                  , d.Typ
              FROM
                  wettkampf AS w
                  LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin) 
                  LEFT JOIN start AS st ON (st.xWettkampf = w.xWettkampf     )
                  LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart) 
                  INNER JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart) 
                  LEFT JOIN staffel AS s ON (s.xStaffel = st.xStaffel)
              WHERE 
                  w.xMeeting = " . $_COOKIE['meeting_id'] ."
                  AND w.xKategorie = $xCategory   
                  AND s.xTeam = $row[0]
              GROUP BY
                  st.xStart
              ORDER BY
                    d.Anzeige
                    , pts DESC";      
        
        $res = mysql_query($sql);    
	    
		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			$a = 1;
			$c = 0;
			$d = '';
			$p = 0;

			while($pt_row = mysql_fetch_row($res))
			{
				if($pt_row[0] != $d) 	// new discipline
				{
					// accumulate points of previous event
					if($p > 0) {
						$info = $info . $sep . $d;
						$sep = ", ";
						$points = $points + $p;		// accumulate points
					}

					$c = 0;					// ranking counter
					$p = 0;					// point counter
				}

				if($c < $a)		// count only top ranking results
				{
					// calculate points
					$p = $p + $pt_row[1];
				}
				else if ($temptable == true)
				{
					// add result to list for further processing (group 1 = run)
					mysql_query("
						INSERT INTO tempresult
						VALUES(
							1
							, $pt_row[1]
							, $pt_row[2]
							, $pt_row[3])
					");

					if(mysql_errno() > 0) {		// DB error
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
				}

				$c++;
				$d = $pt_row[0];				// keep discipline
			}	// END WHILE team events

			// accumulate points of last event
			if($p > 0) {
				$info = $info . $sep . $d;
				$sep = ", ";
				$points = $points + $p;		// accumulate points
			}

			mysql_free_result($res);
		}

		// evaluate remaining results (Event Type: ClubMA to ClubFB)
		if($temptable == true)
		{
			$res = mysql_query("
				SELECT
					Disziplinengruppe
					, Punkte
					, Wettkampftyp
					, Disizplinentyp
				FROM
					tempresult
				ORDER BY
					Disziplinengruppe ASC
					, Punkte DESC
			");

			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else {

				$c = 0;					// athlete counter
				$g = 0;					// group indicator
				$p = 0;					// point counter
               
				while($pt_row = mysql_fetch_row($res))
				{
					// nbr of athletes per disc. group to be included in total points
					switch($pt_row[2]) {
						case $cfgEventType[$strEventTypeClubMA]:
							$a = 3;
							break;
						case $cfgEventType[$strEventTypeClubMB]:
							$a = 2;
							break;
						case $cfgEventType[$strEventTypeClubMC]:
							$a = 1;
							break;
						case $cfgEventType[$strEventTypeClubFA]:
							$a = 2;
							break;
						case $cfgEventType[$strEventTypeClubFB]:
							$a = 1;
							break;
						default:
							$a = 0;
					}
			
					if($pt_row[0] != $g) 	// new discipline	group
					{
						// accumulate points of previous event
						if($p > 0) {
							$points = $points + $p;		// accumulate points
						}
						
						$c = 0;					// athlete counter
						$p = 0;					// point counter
					}

					if($c < $a)		// add only top ranking athletes
					{
						$p = $p + $pt_row[1];
					}
					else if ($pt_row[3] != $cfgDisciplineType[$strDiscTypeRelay])
					{
						// add result to list for further processing (group 1 = run)
						mysql_query("
							INSERT INTO tempresult
							VALUES(
								0
								, $pt_row[1]
								, $pt_row[2]
								, 0)
						");
					}

					if(mysql_errno() > 0) {		// DB error
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}


					$c++;
					$g = $pt_row[0];
				}	// END WHILE remaining events

				// accumulate points of last event
				if($p > 0) {
					$points = $points + $p;		// accumulate
				}
				
				mysql_free_result($res);
			}

			$res = mysql_query("
				SELECT
					Disziplinengruppe
					, Punkte
					, Wettkampftyp
					, Disizplinentyp
				FROM
					tempresult
				WHERE Disziplinengruppe = 0
				ORDER BY
					Punkte DESC
			");

			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else {

				$c = 0;					// athlete counter
				$p = 0;					// point counter
               
				while($pt_row = mysql_fetch_row($res))
				{
					// nbr of remaining athletes to be included in total points
					switch($pt_row[2]) {
						case $cfgEventType[$strEventTypeClubMA]:
							$a = 6;
							break;
						case $cfgEventType[$strEventTypeClubMB]:
							$a = 3;
							break;
						case $cfgEventType[$strEventTypeClubMC]:
							$a = 2;
							break;
						case $cfgEventType[$strEventTypeClubFA]:	
							$a = 4;
							break;
						case $cfgEventType[$strEventTypeClubFB]:
							$a = 4;
							break;
						default:
							$a = 0;
					}
			
					if($pt_row[0] != $g) 	// new discipline	group
					{
						// accumulate points of previous event
						if($p > 0) {
                            
							$points = $points + $p;		// accumulate points
						}
						
						$c = 0;					// athlete counter
						$p = 0;					// point counter
					}

					if($c < $a)		// add only top ranking athletes
					{
						$p = $p + $pt_row[1];
					}

					$c++;
					$g = $pt_row[0];
				}	// END WHILE remaining events

				// accumulate points of last event
				if($p > 0) {
                    
					$points = $points + $p;		// accumulate
				}

				mysql_free_result($res);
			}

		}

		$tm = $row[0];
		$team = $row[1];
		$club = $row[2];

		mysql_query("DROP TABLE IF EXISTS tempresult");
		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		$temptable = false;	// reset temp table indicator

	}	// END WHILE teams	

	if(!empty($tm))		// add last team if any
	{    
		$teamList[] = array(
			"points"=>$points
			, "team"=>$team
			, "club"=>$club
			, "info"=>$info                
			, "id"=>$tm              
		);
	}
    
    if (!empty($teamList)){
        
    
    $category .= $catMkMw; 
	$GLOBALS[$list]->printSubTitle($category, "", "", $catMkMw);
	$GLOBALS[$list]->startList();
	$GLOBALS[$list]->printHeaderLine();
    
    
	usort($teamList, "cmp");
	$rank = 1;									// initialize rank
	$r = 0;										// start value for ranking
	$p = 0;  
    
    }
    foreach($teamList as $team)
                {
                    $r++;
                    
                    if($limitRank && ($r < $rFrom || $r > $rTo)){ // limit ranks if set (export)
                        continue;
                    }
                    
                    if($p != $team['points']) {    // not same points as previous team
                        $rank = $r;        // next rank
                    }
                    else {
                        $rank = '';
                    }
                    
                    if($GLOBALS['xmladdon']){ 
                        $GLOBALS[$list]->printLine($rank, $team['team'], $team['club'], $team['points'], $team['id']);
                    }else{
                        $GLOBALS[$list]->printLine($rank, $team['team'], $team['club'], $team['points']);
                    }
                    $GLOBALS[$list]->printInfo($team['info']);
                    $p = $team['info'];            // keep current points
                }
    
	
	$GLOBALS[$list]->endList();

	mysql_query("UNLOCK TABLES");

}	// end function processSingle()


//
//	process club combined events
//

function processCombined($xCategory, $category, $type, $wTyp)
{  
	//global $rFrom, $rTo, $limitRank;
    $GLOBALS[$rFrom];
    $GLOBALS[$rTo];
    $GLOBALS[$limitRank];  
    
	require('./config.inc.php');
    
    
    $n = 0;
    
    

	// get athlete info per category and team   	
    $sql = "
        SELECT
            DISTINCT(a.xAnmeldung)
            , at.Name
            , at.Vorname
            , at.Jahrgang
            , t.xTeam
            , t.Name
            , v.Name 
            , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land
            , v.xVerein
        FROM
            anmeldung AS a
            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
            INNER JOIN team AS t ON (t.xTeam = a.xTeam)
            LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
            LEFT JOIN start as st ON (st.xAnmeldung = a.xAnmeldung)
            LEFT JOIN wettkampf as w ON (w.xWettkampf = st.xWettkampf)
            LEFT JOIN region AS re ON (at.xRegion = re.xRegion) 
        WHERE 
            a.xMeeting = " . $_COOKIE['meeting_id'] ." 
            AND w.xKategorie = $xCategory
            AND w.Typ = $wTyp    
        ORDER BY
            t.xTeam
    ";                        
                
    $results = mysql_query($sql);    
   
	if(mysql_errno() > 0) {		// DB error
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{  
        $evaluationPt = 5;              //  nbr of athletes fot calcualte points  
		if ($type=='teamAll')   
           $evaluation = 99999999;      //  all athletes    
        else                                                                                                     
           $evaluation = 5;	            // nbr of athletes included in total result
       
		$a = 0;
		$club = '';
		$info = '';
		$name = '';
		$points = 0;
		$team = '';
        $xTeam = 0;   
		$sep = '';
		$tm = '';
		$year = '';
        $country = '';  
	
		while($row = mysql_fetch_row($results))
		{    
			// store previous athlete before processing new athlete
			if(($a != $row[0])		// new athlete
				&& ($a > 0))			// first athlete processed
			{
				$athleteList[] = array(
					"points"=>$points
					, "name"=>$name
					, "year"=>$year
					, "info"=>$info
                    , "country"=>$country
                    , "club"=>$club 
				);
                 
				$points = 0;
				$info = '';
				$sep = '';
                
			}

			// store previous team before processing new team
			if(($tm != $row[4])		// new athlete
				&& ($tm > 0))			// first athlete processed
			{
				usort($athleteList, "cmp");	// sort athletes by points

				// nbr of athletes to include in team result
				$total = 0;
				for($i=0; $i < $evaluationPt; $i++) {
					$total = $total + $athleteList[$i]['points'];
				}    
                
				$teamList[] = array(
					"points"=>$total
                    , "rank"=>$n  
					, "team"=>$team
                    , "teamNr"=>$xTeam 
					, "club"=>$club
					, "athletes"=>$athleteList
					, "id"=>$tm                      
				);

				$team = '';
                $xTeam = 0;   
				$club = '';
				unset($athleteList);
				$sep = '';
			}

            if ($type != 'teamP'){     
			    $tm = $row[4];		// keep current team
            }

			// events       
            $sql = "
                SELECT
                    d.Kurzname
                    , d.Typ
                    , MAX(r.Leistung)
                    , r.Info
                    , MAX(r.Punkte) AS pts
                    , s.Wind
                    , w.Windmessung
                    , st.xAnmeldung
                    , d.xDisziplin
                    , w.Typ 
                FROM
                    start AS st USE INDEX (Anmeldung)
                    LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart   )
                    LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart) 
                    LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie)
                    LEFT JOIN runde AS ru ON (ru.xRunde = s.xRunde) 
                    LEFT JOIN wettkampf AS w ON (w.xWettkampf = st.xWettkampf)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin) 
                WHERE 
                    st.xAnmeldung = $row[0]                    
                    AND w.Typ = " . $cfgEventType[$strEventTypeClubCombined] . "                  
                    AND 
                    ((d.Typ = 6 && (r.Info !=  '" . $cfgResultsHighOut . "' && r.Info !=  '" . $cfgResultsHighOut1 . "' 
                                                 && r.Info !=  '" . $cfgResultsHighOut2 . "'  && r.Info !=  '" . $cfgResultsHighOut3 . "'  && r.Info !=  '" . $cfgResultsHighOut4 . "'
                                                 && r.Info !=  '" . $cfgResultsHighOut5 . "' && r.Info !=  '" . $cfgResultsHighOut6 . "' )
                      OR (d.Typ != 6 ) ))
                    
                    
                GROUP BY
                    st.xStart
                ORDER BY
                    ru.Datum
                    , ru.Startzeit
            ";     
            //r.Info != '" . $cfgResultsHighOut . "'        
          
            $res = mysql_query($sql);      
           
			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else
			{   
				while($pt_row = mysql_fetch_row($res))
				{
                    if ($row[8] != NULL && $pt_row[4] != NULL) {
                       
                            $sql = "INSERT INTO tmp_team 
                                            VALUES (
                                                 $xCategory   
                                                , $pt_row[8]
                                                , $pt_row[4] 
                                                , $row[4]
                                                
                                            )";
                            mysql_query($sql);
                           
                            if(mysql_errno() > 0) {        // DB error                                 
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }   
                    }
					// set wind, if required
					if($pt_row[6] == 1)
					{
						if($pt_row[1] == $cfgDisciplineType[$strDiscTypeTrack]) {
							$wind = " / " . $pt_row[5];
						}
						else if($pt_row[1] == $cfgDisciplineType[$strDiscTypeJump]) {
							$wind = " / " . $pt_row[3];
						}
					}
					else {
						$wind = '';
					}

					// format output
					if(($pt_row[1] == $cfgDisciplineType[$strDiscTypeJump])
						|| ($pt_row[1] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
						|| ($pt_row[1] == $cfgDisciplineType[$strDiscTypeThrow])
						|| ($pt_row[1] == $cfgDisciplineType[$strDiscTypeHigh])) {
						$perf = AA_formatResultMeter($pt_row[2]);
					}
					else {  
						$perf = $pt_row[2];   
                       
                        $perf = $perf/1000;
                        list($sec, $mili) = explode(".", $perf);
                        list($hour, $rest) = explode(".", ($sec/3600));
                        list($min, $rest) = explode(".", (($sec-($hour*3600))/60));
                        list($sec, $rest) = explode(".", ($sec-($hour*3600)-($min*60)));
                       
                        // round up to hundredth  (examples: 651 --> 660 and 650 --> 650)
                        $mili=ceil(sprintf ("%-03s",$mili)/10);              
                        list($a,$mili)=explode(".",($mili/100));         
                        $sec+=$a;   
                        
                        // display milli (two decimal after point without 0 in front)
                        $time = '';
                        if ($hour > 0) {
                            $time = sprintf("%02d", $hour).":".sprintf("%02d", $min).":".sprintf("%02d", $sec).".".sprintf("%-02s", $mili);  
                        }
                        elseif ($min > 0) { 
                                $time = sprintf("%02d", $min).":".sprintf("%02d", $sec).".".sprintf("%-02s", $mili);  
                        }
                        else {
                             $time =  $sec .".".sprintf("%-02s", $mili); 
                        }    
                        $perf = $time;  
					}
                   
					// calculate points
					$points = $points + $pt_row[4];	// accumulate points
                    
                    if ($perf != $cfgInvalidResult['DNS']['code']){ 
					    if ($perf < 0){ 
                            foreach($cfgInvalidResult as $value)    // translate value
                                {if($value['code'] == $perf) {
                                        $perf = $value['short'];
                                    }
                                } 
                        } 
					    $info = $info . $sep . $pt_row[0] . "&nbsp;(" . $perf . $wind . "/ " .$pt_row[4] .")";
					    $sep = ", ";
                    }    
				}	// END WHILE combined events
				mysql_free_result($res);
			}

			$a = $row[0];
			$name = $row[1] . " " . $row[2];
			$year = AA_formatYearOfBirth($row[3]);
			$team = $row[5];
            $xTeam = $row[4];   
			$club = $row[6];
            $country = $row[7];
		}	// END WHILE athlete per category

		mysql_free_result($results);

		
        if (!empty($tm) || $type == 'teamP')        // add last team if any  or team independent 
		{
			// last athlete
			$athleteList[] = array(
				"points"=>$points
				, "name"=>$name
				, "year"=>$year
				, "info"=>$info
                , "country"=>$country  
                , "club"=>$club     
			);
                
			// last team
			usort($athleteList, "cmp");	// sort athletes by points

			$total = 0;    
			for($i=0; $i < $evaluationPt; $i++) {
				$total = $total + $athleteList[$i]['points'];
			}

			$teamList[] = array(
				"points"=>$total
                 , "rank"=>$n     
				, "team"=>$team
                , "teamNr"=>$xTeam  
				, "club"=>$club
				, "athletes"=>$athleteList
				, "id"=>$tm                     
			);
		}
       
		$GLOBALS[$list]->printSubTitle("$category", "", "");
		$GLOBALS[$list]->startList();
		$GLOBALS[$list]->printHeaderLine();
        
		usort($teamList, "cmp");
		$rank = 1;									// initialize rank
		$r = 0;										// start value for ranking
		$p = 0;
		$tp = 0;
        
         $t = 0;

        foreach($teamList as $team)
            {
               
                
                if($limitRank && ($r < $rFrom || $r > $rTo)){ // limit ranks if set (export)
                    continue;
                }
                
                if($p == $team['points']) {    // not same points as previous team
                    $rank = $t;        // next rank
                    $arr_same[] = $t;
                }
               
                 $p = $team['points'];            // keep current points     
                 $t++;                            
                 $team['rank'] = $t;
            }
        $key_1 = array();   
        $key_2 = array();  
        $p = 0;     
        $i = 0;  
        
        foreach($teamList as $team)
            {
                $r++;
                
                if($limitRank && ($r < $rFrom || $r > $rTo)){ // limit ranks if set (export)
                    continue;
                }
                
                if($p != $team['points']) {    // not same points as previous team
                    $rank = $r;        // next rank
                    $teamList[$i]['rank'] = $rank;   
                }
                else {
                       $teamList[$i]['rank'] = $r;   
                      $arr_team = check_samePoints($xCategory);                         
                     
                      if ($arr_team[$teamNr_keep] < $arr_team[$team['teamNr']] ) {          //change ranking position
                           $key_1[] = $i -1;
                           $key_2[] = $i;  
                      }                         
                }
             $i++;
             $p = $team['points'];              // keep current points  
             $teamNr_keep = $team['teamNr'];    // keep current xTeam
            }
       if (count($key_1) > 0){
            foreach ($key_1 as $k) {
                    $teamList[$key_1[$k]][rank] = $r;   
                    $teamList[$key_2[$k]][rank] = $r - 1;       
            }
          
       }       
        
       usort($teamList, "cmp_rank");      

        
        if ($type == 'teamP'){                     // ranking athletes not depending on team                
            
                usort($athleteList, "cmp");    // sort athletes by points  
          
                $xmlinfo = "";
                $rank = 0;
                $i = 0;
               
                foreach($athleteList as $key => $athlete)
                {                         
                    if ($keep_points != $athlete['points']) {
                        $i++; 
                        $rank = $i;
                    }
                    else {                                
                          $rank = '';
                    }
                     
                    $GLOBALS[$list]->printAthleteLine($athlete['name'], $athlete['year'], $athlete['points'], $athlete['country'], $athlete['club'], $rank, $type);
                    if($GLOBALS['xmladdon']){
                        $xmlinfo .= $athlete['name']." (".$athlete['points'].") / ";
                    }else{
                        $GLOBALS[$list]->printInfo($athlete['info']);
                    }
                    $keep_points = $athlete['points'];
                }
                
                if($GLOBALS['xmladdon']){
                    $GLOBALS[$list]->printInfo(substr($xmlinfo,0,strlen($xmlinfo)-2));
                }        
        }
        else {                             // ranking per team
         
		    foreach($teamList as $team)
		    {
			    $r++;
			    
			    if($limitRank && ($r < $rFrom || $r > $rTo)){ // limit ranks if set (export)
				    continue;
			    }      
               
			    if($GLOBALS['xmladdon']){
				    $GLOBALS[$list]->printLine($team['rank'], $team['team'], $team['club'], $team['points'], $team['id']);
			    }else{
				    $GLOBALS[$list]->printLine($team['rank'], $team['team'], $team['club'], $team['points']);
			    }
                
			    $p = $team['points'];			// keep current points

			    $i = 0;
			    $xmlinfo = "";
              
			    foreach($team['athletes'] as $athlete)
			        {   
				        if($i >= $evaluation) {	// show only athletes included in end result
					        break;
				        }
				        $i++;   
                        
				        $GLOBALS[$list]->printAthleteLine($athlete['name'], $athlete['year'], $athlete['points'], $athlete['country']);
				        if($GLOBALS['xmladdon']){
					        $xmlinfo .= $athlete['name']." (".$athlete['points'].") / ";
				        }else{
					        $GLOBALS[$list]->printInfo($athlete['info']);
				        }
			    }    
			    
			    if($GLOBALS['xmladdon']){
				    $GLOBALS[$list]->printInfo(substr($xmlinfo,0,strlen($xmlinfo)-2));
			    } 
                                        
                $club = $team['club'];  
		    }
         } 

		$GLOBALS[$list]->endList();
	}	// ET DB error all teams

        

    
}	// end function processCombined()


//
// compare function to sort teamList
// 
function cmp ($a, $b) {
    if ($a["points"]== $b["points"]) return 0;
    return ($a["points"] > $b["points"]) ? -1 : 1;
}

function cmp_rank ($a, $b) {
    if ($a["rank"]== $b["rank"]) return 0;
    return ($a["rank"] > $b["rank"]) ? 1 : -1;
}

function check_samePoints($xCategory) {
    
    $sql = "SELECT 
                Punkte,
                xDisziplin,
                xTeam
            FROM tmp_team 
            WHERE xKategorie = " .$xCategory ."
            ORDER BY xDisziplin, Punkte";
    
    $res = mysql_query($sql); 
    if (mysql_errno() > 0) {
       AA_printErrorMsg(mysql_errno() . ": " . mysql_error());  
    }
    else {
        $disc = 0;
        $arr_team = array();
        while ($row = mysql_fetch_row($res)) {
            
             if ($disc != $row[1]){
                  $arr_team[$row[2]] ++;
                 
             }
            
             $disc = $row[1];
            
        }
        
    }
    
  return  $arr_team; 
}



}	// AA_RANKINGLIST_TEAM_LIB_INCLUDED
?>
