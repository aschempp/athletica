<?php

/**********
 *
 *	rankinglist team sheets
 *	
 */

if (!defined('AA_RANKINGLIST_SHEET_LIB_INCLUDED'))
{
	define('AA_RANKINGLIST_SHEET_LIB_INCLUDED', 1);


function AA_rankinglist_Sheets($category, $event, $formaction, $cover, $cover_timing=false, $heatSeparate, $catFrom,$catTo,$discFrom,$discTo)
{  
   // $heatSeparate=true (always show heat separate)
  $heatSeparate = true;
    
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_print_page.lib.php');
require('./lib/cl_print_page_pdf.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');

if(AA_connectToDB() == FALSE)	{ // invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

// start a new HTML display page
if($formaction == 'view') { 
	$GLOBALS[$list] = new GUI_TeamSheet($_COOKIE['meeting']);
	$GLOBALS[$list]->printPageTitle("$strClubSheets " . $_COOKIE['meeting']);
}
// start a new HTML print page
else {
	$GLOBALS[$list] = new PRINT_TeamSheet_pdf($_COOKIE['meeting']);
	if($cover == true) {		// print cover page 
		$GLOBALS[$list]->printCover($strClubSheets, $cover_timing);
	}
}
$selection = ''; 
if ($event!=''){
    $mergedCat=AA_mergedCatEvent($category, $event);  
}
else {
    $mergedCat=AA_mergedCat($category);   
}

if(!empty($category)) {		// show every category  
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

if($catFrom > 0) {    //         
     $getSortCat=AA_getSortCat($catFrom,$catTo);
     if ($getSortCat[0]) {
         if ($catTo > 0){
            $selection = " AND k.Anzeige >=" . $getSortCat[$catFrom] . " AND k.Anzeige <=" . $getSortCat[$catTo];
        }     
        else {
            $selection = " AND k.Anzeige =" . $getSortCat[$catFrom];
        }
     }
}


if($discFrom > 0) {    //          
     $getSortDisc=AA_getSortDisc($discFrom, $discTo);
     if ($getSortDisc[0]) {
         if ($discTo > 0){
            $selection2 .= " AND d.Anzeige >=" . $getSortDisc[$discFrom] . " AND d.Anzeige <=" . $getSortDisc[$discTo];
        }     
        else {
            $selection2 .= " AND d.Anzeige =" . $getSortDisc[$discFrom];
        }
     }
} 

// evaluation per category      

mysql_query("DROP TABLE IF EXISTS tempresult");
mysql_query("DROP TABLE IF EXISTS sheet_tmp");     
  
if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}

    
$sql = "SELECT
          k.xKategorie
          , k.Name
        , w.Typ
      FROM
          kategorie AS k
          LEFT JOIN wettkampf AS w ON (k.xKategorie = w.xKategorie)
      WHERE w.xMeeting = " . $_COOKIE['meeting_id'] ."       
    " . $selection 
      ." AND w.Typ >=  " . $cfgEventType[$strEventTypeClubBasic] ."  
    GROUP BY
        k.xKategorie,
        w.Typ
    ORDER BY
        k.Anzeige";
    
 $results = mysql_query($sql);
 
if(mysql_errno() > 0) {		// DB error
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{   
	$GLOBALS['AA_TC'] = 0;		// team counter

    
	// process all categories
	while($row = mysql_fetch_row($results))
	{  
		// Team sheet: Combined 
		if ($row[2] == $cfgEventType[$strEventTypeClubCombined])
		{   
			AA_sheets_processCombined($row[0], $row[1], $row[2]);
		}
		// Team sheet: Single
		else
		{  
			AA_sheets_processSingle($row[0], $row[1], $selection2);
		}

	}

	mysql_free_result($results);
}	// ET DB error categories 

$GLOBALS[$list]->endPage();	// end HTML page for printing

}	// end function AA_rankinglist_Team

//
//	process club single events
//

function AA_sheets_processSingle($xCategory, $category, $selection2)
{  
	require('./config.inc.php');

	mysql_query("
		LOCK TABLES
	  		anmeldung AS a 
  			, athlet READ
  			, disziplin_" . $_COOKIE['language'] . " READ
  			, resultat READ
  			, serie READ
  			, serienstart READ
  			, staffel READ
  			, start READ
			, team READ
			, verein READ
  			, wettkampf READ
  			, tempresult WRITE
			, kategorie READ
	");

	// get all teams
	$results = mysql_query("
		SELECT
			t.Name
			, t.xTeam
			, v.Name
		FROM
			team AS t
			LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
		WHERE 
            t.xMeeting = " . $_COOKIE['meeting_id'] ."
		    AND t.xKategorie = $xCategory 		
	");   
    
	if(mysql_errno() > 0) {		// DB error
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		// keep list of all teams for this category
		while($row = mysql_fetch_row($results))
		{
			$teamList[] = array(
				"name"=>$row[0]
				, "xTeam"=>$row[1]
				, "club"=>$row[2]
			);
		}
		mysql_free_result($results);

		// process every team
		foreach($teamList as $team)
		{  
			// page break after each team
			if((is_a($GLOBALS[$list], "PRINT_TeamSheet")
			|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf"))	// page for printing
				&& ($GLOBALS['AA_TC'] > 0)) {		// not first result row
				$GLOBALS[$list]->insertPageBreak();
			}
			$GLOBALS['AA_TC']++;		// team counter
			$total = 0;
			$temptable = false;

			if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
			|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing
				// set up list of other competitors
				$sep = '';
				$competitors = '';
				foreach($teamList as $comp)
				{
					if($comp['xTeam'] != $team['xTeam'])	// not current team
					{
						$competitors = $competitors . $sep . $comp['club'];	// club
						$sep = ', ';
					}
				}

				$GLOBALS[$list]->printHeader($team['club']." (".$team['name'].")", $category, $competitors);
			}
			else {
				$GLOBALS[$list]->printHeader($team['club']." (".$team['name'].")", $category);
			}
           
			// single events
			// -------------    
           
            
                        
              $query_tmp="CREATE TEMPORARY TABLE sheet_tmp SELECT  
                    d.Name AS dName
                    , d.Typ AS dTyp
                    , at.Name
                    , at.Vorname
                    , at.Jahrgang
                    , r.Leistung
                    , IF(d.Typ = 6 && r.info = '-','XXX', r.info) AS info 
                    , r.Punkte AS pts
                    , w.Typ
                    , s.Wind
                    , w.Windmessung
                    , k.Code
                    , at.Geschlecht
                    , ss.Bemerkung
                    , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land 
                    , st.xStart
                    , d.Anzeige    
                FROM
                    anmeldung AS a
                    LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                    LEFT JOIN start AS st ON ( a.xAnmeldung = st.xAnmeldung   ) 
                    LEFT JOIN wettkampf AS w ON (st.xWettkampf = w.xWettkampf) 
                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                    LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart)   
                    LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie)                      
                    LEFT JOIN resultat AS r  ON (r.xSerienstart = ss.xSerienstart)  
                    LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)
                    LEFT JOIN region AS re ON (at.xRegion = re.xRegion)  
                WHERE 
                    w.xMeeting = " . $_COOKIE['meeting_id'] ."
                    AND w.xKategorie = $xCategory                    
                    AND a.xTeam = " . $team['xTeam'] . "   
                      " . $selection2 . "    
                ORDER BY
                    d.Anzeige
                    , pts DESC";        
                     
            $results = mysql_query($query_tmp);     
                      
            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                   $sql = "
                SELECT
                    t.dName
                    , t.dTyp
                    , t.Name
                    , t.Vorname
                    , t.Jahrgang
                    , MAX(t.Leistung)
                    , t.Info
                    , MAX(t.pts) AS pts
                    , t.Typ
                    , t.Wind
                    , t.Windmessung
                    , t.Code
                    , t.Geschlecht
                    , t.Bemerkung
                    , t.Land   
                FROM
                    sheet_tmp AS t  
                WHERE                      
                     t.info != '$cfgResultsHighOut'                     
                GROUP BY
                    t.xStart
                ORDER BY
                    t.Anzeige
                    , pts DESC";   
            }     
            
            $results = mysql_query($sql);      
           
			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else
			{
				$a = 0;
				$c = 0;
				$d = '';
				$r = 0;
				$mixedTeamCount = array('m'=>0,'w'=>0);
				
				while($pt_row = mysql_fetch_row($results))
				{
					if($pt_row[0] != $d) // new discipline
					{
						for(;$c < $a; $c++)
						{
							$points = '';
							if($c + 1 == $a)	// last line
							{
								if($r == $cfgEventType[$strEventTypeClubAdvanced]) {
									$points = $p / 2;         
									//$points = $p;
								}elseif($r == $cfgEventType[$strEventTypeSVMNL]) {
									//$points = $p / 2;
									$points = $p;
								}/*elseif($r == $cfgEventType[$strEventTypeClubMixedTeam]){
									$points = $p / 6;
								}*/else {
									$points = $p;
								}
                               
								$total = $total + $points;	// accumulate total points
								$points = round($points,$cfgResultsPointsPrecision);
							}                             
							$GLOBALS[$list]->printLine('', $cfgResultsInfoFill, $cfgResultsInfoFill, '', '0', $points);	// empty line
						}

						// nbr of athletes to be included in total points
						switch($pt_row[8]) {
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
							case $cfgEventType[$strEventTypeClubMB]: // kept for compatibility reasons
							case $cfgEventType[$strEventTypeClubMC]: // and to demonstrate respect for the guys who coded this ;)
							case $cfgEventType[$strEventTypeClubFA]:
							case $cfgEventType[$strEventTypeClubFB]:
								$a = 1;
								if($c == 0) {	// first time here: initialize temp. table
									mysql_query("
										CREATE TABLE tempresult(
											Disziplinengruppe tinyint(4)
											, Disziplin varchar(30)
											, Name varchar(25)
											, Vorname varchar(25)
											, `Jahrgang` year(4)
											, Leistung int(9)
											, Info char(5)
											, Punkte smallint(6)
											, Wettkampftyp tinyint(4)
											, Wind char(5)
											, Windmessung tinyint(4)
											, Disizplinentyp tinyint(4)
											, xStaffel int(11)
											)
										ENGINE=HEAP
									");

									if(mysql_errno() > 0) {		// DB error
										AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
									}
									else {
										$temptable = true;
									}

									$GLOBALS[$list]->printSubHeader($strTeamRankingSubtitle1);
								}
								break;

							default:
								$a = 1;
						}

						$c = 0;					// athlete counter
						$p = 0;					// point counter
						$mixedTeamCount = array('m'=>0,'w'=>0);	// mixedteam counter
					}

					if($c < $a)		// show only top ranking athletes
					{
						// special mixed-team schüler svm
						// get 3 men results and 3 women results
						if($pt_row[8] == $cfgEventType[$strEventTypeClubMixedTeam]){
							
							if(empty($pt_row[12])){
								if(substr($pt_row[11],0,1) == 'M' || substr($pt_row[11],3,1) == 'M'){
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
								if($pt_row[12] == "m"){
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
						
						$windsep='';
						if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
						|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing
							$windsep="/ ";
						}

						// set wind, if required
						if($pt_row[10] == 1)
						{
							if($pt_row[1] == $cfgDisciplineType[$strDiscTypeTrack]) {
								$wind = $windsep . $pt_row[9];
							}
							else if($pt_row[1] == $cfgDisciplineType[$strDiscTypeJump]) {
								$wind = $windsep . $pt_row[6];
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
							$perf = AA_formatResultMeter($pt_row[5], true);
						}
						else {
							$perf = AA_formatResultTime($pt_row[5], true);
						}
						$year = AA_formatYearOfBirth($pt_row[4]);
                        $country = $pt_row[14];  

						// calculate points
						if($pt_row[8] == $cfgEventType[$strEventTypeClubMixedTeam]){
							$p = $p + $pt_row[7] / 6;
							$p = round($p,$cfgResultsPointsPrecision);
						}elseif($pt_row[8] == $cfgEventType[$strEventTypeClubTeam]){
							$p = $p + $pt_row[7] / 5;
							$p = round($p,$cfgResultsPointsPrecision);
						}else{
							$p = $p + $pt_row[7];	// accumulate points per discipline
						}

						if(($c + 1) == $a) {	// last athlete
							if($pt_row[8] == $cfgEventType[$strEventTypeClubAdvanced]) {
								$p = $p / 2;                               
								//$p = $p;
							}elseif($pt_row[8] == $cfgEventType[$strEventTypeSVMNL]) {
								//$p = $p / 2;
								$p = $p;
							}/*elseif($pt_row[8] == $cfgEventType[$strEventTypeClubMixedTeam]){
								$p = $p / 6;
							}*/

							$points = round($p,$cfgResultsPointsPrecision);
						}
						else {					// not last athlete
							$points = '';
						}

						$total = $total + $points;	// accumulate total points

						// print athlete line
						$ip = '';
						if($pt_row[8] == $cfgEventType[$strEventTypeClubAdvanced]
							|| $pt_row[8] == $cfgEventType[$strEventTypeClubMixedTeam]
							|| $pt_row[8] == $cfgEventType[$strEventTypeClubTeam]
							|| $pt_row[8] == $cfgEventType[$strEventTypeSVMNL])
						{
							$ip = $pt_row[7];      
                            
						}       
            
						if($pt_row[0] != $d)		// new discipline
						{
							$GLOBALS[$list]->printLine($pt_row[0],
								$pt_row[2] . " " . $pt_row[3] .", " . $year .", " . $country,
								$perf, $wind, $ip, $points, $pt_row[13]);
						}
						else {
							$GLOBALS[$list]->printLine('',
								$pt_row[2] . " " . $pt_row[3] . ", " . $year .", " . $country, 
								$perf, $wind, $ip, $points, $pt_row[13]);
						}
					}
					else if ($temptable == true)	// temp table created
					{
						switch($pt_row[1]) {
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
								, '$pt_row[0]'
								, '$pt_row[2]'
								, '$pt_row[3]'
								, $pt_row[4]
								, $pt_row[5]
								, '$pt_row[6]'
								, $pt_row[7]
								, $pt_row[8]
								, '$pt_row[9]'
								, $pt_row[10]
								, $pt_row[1]
								, 0)
						");

						if(mysql_errno() > 0) {		// DB error
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}

					}

					$c++;
					$d = $pt_row[0];	// keep discipline
					$r = $pt_row[8];	// keep rating type                      
				}	// END WHILE team events

				// print remaining empty lines for last disciplines (if any)
				for(;$c < $a; $c++)
				{
					$points = '';
					if($c + 1 == $a)	// last line
					{
						if($r == $cfgEventType[$strEventTypeClubAdvanced]) {
							$points = $p / 2;
						}elseif($r == $cfgEventType[$strEventTypeSVMNL]) {
							//$points = $p / 2;
							$points = $p;
						}/*elseif($r == $cfgEventType[$strEventTypeClubMixedTeam]){
							$points = $p / 6;
						}*/
						else {
							$points = $p;
						}
						$total = $total + $points;	// accumulate total points
					}
					$GLOBALS[$list]->printLine('', $cfgResultsInfoFill, $cfgResultsInfoFill, '', '0', round($points,$cfgResultsPointsPrecision));	// empty line
				}

				mysql_free_result($results);
			}

			// relay events
			// -------------       
            $sql = "
                SELECT
                    d.Name
                    , st.Name
                    , r.Leistung
                    , MAX(r.Punkte) AS pts
                    , st.xStaffel
                    , w.Typ
                    , d.Typ
                    , ss.Bemerkung
                FROM
                    wettkampf AS w
                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin) 
                    LEFT JOIN start AS s ON (s.xWettkampf = w.xWettkampf) 
                    LEFT jOIN serienstart AS ss ON (ss.xStart = s.xStart)     
                    LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)                    
                    LEFT JOIN staffel AS st ON (st.xStaffel = s.xStaffel)  
                WHERE 
                    w.xMeeting = " . $_COOKIE['meeting_id'] ."
                    AND w.xKategorie = $xCategory  
                    AND st.xTeam = " . $team['xTeam'] . "
                       " . $selection2 . "    
                GROUP BY
                    s.xStart
                ORDER BY
                    d.Anzeige
                    , pts DESC
            ";      
             
            $results = mysql_query($sql);      
           
			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else
			{
				$a = 1;
				$c = 0;
				$d = '';
				$r = 0;
				$p = 0;

				while($pt_row = mysql_fetch_row($results))
				{
					if($pt_row[0] != $d) 	// new discipline
					{
						// accumulate points of previous relay event
						if($p > 0) {
							$points = $p;
							$total = $total + $points;	// accumulate total points
							$GLOBALS[$list]->printLine('', $cfgResultsInfoFill, $cfgResultsInfoFill, '', '0', round($points,$cfgResultsPointsPrecision)); // empty line
						}

						$c = 0;					// athlete counter
						$p = 0;					// point counter
					}

					if($c < $a)		// show only top ranking relays
					{
						$perf = AA_formatResultTime($pt_row[2], true, false);

						// calculate points
						$p = $p + $pt_row[3];			// accumulate points per discipline
						if(($c + 1) == $a) {	// last athlete
							$points = $p;
						}
						else {					// not last athlete
							$points = '';
						}

						$total = $total + $points;	// accumulate total points

						// print line
						$ip = '';
						if($pt_row[8] == $cfgEventType[$strEventTypeClubAdvanced])
						{
							$ip = $pt_row[3];	// set individual points
						}

						if($pt_row[0] != $d)		// new discipline
						{
							$GLOBALS[$list]->printLine($pt_row[0], $pt_row[1], $perf, '',
								$ip, round($points,$cfgResultsPointsPrecision), $pt_row[7]);

						}
						else {
							$GLOBALS[$list]->printLine('', $pt_row[1], $perf, '', $ip, round($points,$cfgResultsPointsPrecision), $pt_row[7]);
						}

						AA_sheets_printRelayAthletes($pt_row[4]);
					}	// ET top ranking relays
					else if ($temptable == true)
					{
						// add result to list for further processing (group 1 = run)
						mysql_query("
							INSERT INTO tempresult
							VALUES(
								1
								, '$pt_row[0]'
								, '$pt_row[1]'
								, '' 
								, 0
								, $pt_row[2]
								, ''
								, $pt_row[3]
								, $pt_row[5]
								, ''
								, 0
								, $pt_row[6]
								, $pt_row[4])
						");

						if(mysql_errno() > 0) {		// DB error
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
					}



					$c++;
					$d = $pt_row[0];	// keep discipline
					$r = $pt_row[5];	// keep rating type
				}	// END WHILE team events

				$GLOBALS[$list]->printSubTotal(round($total,$cfgResultsPointsPrecision));

				mysql_free_result($results);

				// evaluate remaining results per discipline group
				//	(Event Type: ClubMA to ClubFB)
				if($temptable == true)
				{
					// get next results per discipline group
					$res = mysql_query("
						SELECT
							Disziplinengruppe
							, Disziplin
							, Name
							, Vorname
							, Jahrgang
							, Leistung
							, Info
							, Punkte
							, Wettkampftyp
							, Wind
							, Windmessung
							, Disizplinentyp
							, xStaffel
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

						$GLOBALS[$list]->printSubHeader($strTeamRankingSubtitle2);

						$c = 0;					// athlete counter
						$g = 0;					// group indicator
						$p = 0;					// point counter

						while($pt_row = mysql_fetch_row($res))
						{
							// nbr of athletes per disc. group to be included in total points
							switch($pt_row[8]) {
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

							if($c < $a)		// show only top ranking athletes
							{
								$windsep='';
								if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
								|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing
									$windsep="/ ";
								}

								// set wind, if required
								if($pt_row[10] == 1)
								{
									if($pt_row[11] == $cfgDisciplineType[$strDiscTypeTrack]) {
										$wind = $windsep . $pt_row[9];
									}
									else if($pt_row[11] == $cfgDisciplineType[$strDiscTypeJump]) {
										$wind = $windsep . $pt_row[6];
									}
								}
								else {
									$wind = '';
								}

								// format output
								if(($pt_row[11] == $cfgDisciplineType[$strDiscTypeJump])
									|| ($pt_row[11] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
									|| ($pt_row[11] == $cfgDisciplineType[$strDiscTypeThrow])
									|| ($pt_row[11] == $cfgDisciplineType[$strDiscTypeHigh])) {
									$perf = AA_formatResultMeter($pt_row[5], true);
								}
								else {
									$perf = AA_formatResultTime($pt_row[5], true);
								}

								$year = '';
								if($pt_row[11] != $cfgDisciplineType[$strDiscTypeRelay])
								{
									$year = ", " . AA_formatYearOfBirth($pt_row[4]);
								}
		
								$p = $pt_row[7];
								$points = round($p,$cfgResultsPointPrecision);
								$total = $total + $points;		// accumulate points

								$GLOBALS[$list]->printLine($pt_row[1],
									$pt_row[2] . " " . $pt_row[3] . $year,
									$perf, $wind, "", $points);

								if ($pt_row[11] == $cfgDisciplineType[$strDiscTypeRelay])
								{
									AA_sheets_printRelayAthletes($pt_row[12]);
								}

							}
							else if ($pt_row[11] != $cfgDisciplineType[$strDiscTypeRelay])
							{
								// add result to list for further processing (group 1 = run)
								mysql_query("
									INSERT INTO tempresult
									VALUES(
										0
										, '$pt_row[1]'
										, '$pt_row[2]'
										, '$pt_row[3]'
										, $pt_row[4]
										, $pt_row[5]
										, '$pt_row[6]'
										, $pt_row[7]
										, $pt_row[8]
										, '$pt_row[9]'
										, $pt_row[10]
										, $pt_row[11]
										, 0)
								");
							}

							if(mysql_errno() > 0) {		// DB error
								AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
							}


							$c++;
							$g = $pt_row[0];
						}	// END WHILE next results

						$GLOBALS[$list]->printSubTotal(round($total,$cfgResultsPointsPrecision));
						mysql_free_result($res);
					}
                    
					// get remaining results
					$res = mysql_query("
						SELECT
							Disziplinengruppe
							, Disziplin
							, Name
							, Vorname
							, Jahrgang
							, Leistung
							, Info
							, Punkte
							, Wettkampftyp
							, Wind
							, Windmessung
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

						$GLOBALS[$list]->printSubHeader($strTeamRankingSubtitle3);

						$c = 0;					// athlete counter
						$p = 0;					// point counter

						while($pt_row = mysql_fetch_row($res))
						{
							// nbr of remaining athletes to be included in total points
							switch($pt_row[8]) {
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
					
							if($c < $a)		// add only top ranking athletes
							{
								$windsep='';
								if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
								|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing
									$windsep="/ ";
								}

								// set wind, if required
								if($pt_row[10] == 1)
								{
									if($pt_row[11] == $cfgDisciplineType[$strDiscTypeTrack]) {
										$wind = $windsep . $pt_row[9];
									}
									else if($pt_row[11] == $cfgDisciplineType[$strDiscTypeJump]) {
										$wind = $windsep . $pt_row[6];
									}
								}
								else {
									$wind = '';
								}

								// format output
								if(($pt_row[11] == $cfgDisciplineType[$strDiscTypeJump])
									|| ($pt_row[11] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
									|| ($pt_row[11] == $cfgDisciplineType[$strDiscTypeThrow])
									|| ($pt_row[11] == $cfgDisciplineType[$strDiscTypeHigh])) {
									$perf = AA_formatResultMeter($pt_row[5], true);
								}
								else {
									$perf = AA_formatResultTime($pt_row[5], true);
								}
								$year = AA_formatYearOfBirth($pt_row[4]);

								$p = $pt_row[7];
								$points = round($p,$cfgResultsPointPrecision);
								$total = $total + $points;		// accumulate points

								$GLOBALS[$list]->printLine($pt_row[1],
									$pt_row[2] . " " . $pt_row[3] .", " . $year,
									$perf, $wind, "", $points);
							}

							$c++;
						}	// END WHILE remaining results

						mysql_free_result($res);
					}

				}

				$GLOBALS[$list]->printTotal(round($total,$cfgResultsPointsPrecision));
			}

			if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
			|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing                 
				$GLOBALS[$list]->printFooter();
			}

			mysql_query("DROP TABLE IF EXISTS tempresult");
            mysql_query("DROP TABLE IF EXISTS sheet_tmp");               
            
			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			$temptable = false;	// reset temp table indicator
          
		}	// END FOREACH every team
	}	// ET DB error all teams

	mysql_query("UNLOCK_TABLES");

}	// end function processSingle()


//
//	process club combined events
//

function AA_sheets_processCombined($xCategory, $category, $wTyp)
{
	require('./config.inc.php');

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
        FROM
            anmeldung AS a
            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
            INNER JOIN team AS t ON (t.xTeam = a.xTeam   )
            LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
            LEFT JOIN start as st ON (st.xAnmeldung = a.xAnmeldung  )
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
		$evaluation = 5;		// nbr of athletes included in total result
		$a = 0;
		$club = '';
		$info = '';
		$name = '';
		$points = 0;
		$team = '';
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
				);

				$points = 0;
				$info = '';
				$sep = '';
			}

			// store previous team before processing new team
			if(($tm != $row[4])		// new athlete
				&& ($tm > 0))			// first athlete processed
			{
				usort($athleteList, "AA_sheets_cmp");	// sort athletes by points

				// nbr of athletes to include in team result
				$total = 0;
				for($i=0; $i < $evaluation; $i++) {
					$total = $total + $athleteList[$i]['points'];
				}

				$teamList[] = array(
					"points"=>$total
					, "name"=>$team
					, "club"=>$club
					, "athletes"=>$athleteList
				);

				$team = '';
				$club = '';
				unset($athleteList);
				$sep = '';
			}

			$tm = $row[4];		// keep current team

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
                FROM
                    start AS st USE INDEX (Anmeldung)
                    LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart)
                    LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)
                    LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie) 
                    LEFT JOIN runde AS ru ON (ru.xRunde = s.xRunde)
                    LEFT JOIN wettkampf AS w  ON (w.xWettkampf = st.xWettkampf)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                WHERE 
                    st.xAnmeldung = $row[0]                 
                    AND w.Typ = " . $cfgEventType[$strEventTypeClubCombined] . "  
                    AND r.Info != '" . $cfgResultsHighOut . "'
                GROUP BY
                    st.xStart
                ORDER BY
                    ru.Datum
                    , ru.Startzeit
            ";     
          
              
            $res = mysql_query($sql);    
		
			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else
			{   
				while($pt_row = mysql_fetch_row($res))
				{    
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
						$perf = AA_formatResultMeter($pt_row[2], true);
					}
					else {
						$perf = AA_formatResultTime($pt_row[2], true);
					}

					// calculate points
					$points = $points + $pt_row[4];	// accumulate points

					if($pt_row[4] > 0) {					// any points for this event
						$info = $info . $sep . $pt_row[0] . "&nbsp;(" . $perf . $wind . ")";
						$sep = ", ";
					}
				}	// END WHILE combined events
				mysql_free_result($res);
			}

			$a = $row[0];
			$name = $row[1] . " " . $row[2];
			$year = AA_formatYearOfBirth($row[3]);
			$team = $row[5];
			$club = $row[6];
            $country = $row[7];
		}	// END WHILE athlete per category

		mysql_free_result($results);

		if(!empty($tm))		// add last team if any
		{
			// last athlete
			$athleteList[] = array(
				"points"=>$points
				, "name"=>$name
				, "year"=>$year
				, "info"=>$info
                , "country"=>$country 
			);

			// last team
			usort($athleteList, "AA_sheets_cmp");	// sort athletes by points

			$total = 0;
			for($i=0; $i < $evaluation; $i++) {
				$total = $total + $athleteList[$i]['points'];
			}

			$teamList[] = array(
				"points"=>$total
				, "name"=>$team
				, "club"=>$club
				, "athletes"=>$athleteList
			);
		}

		// print team sheets
		usort($teamList, "AA_sheets_cmp");
        
        
         
		foreach($teamList as $team)
		{
			if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
			|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing
                
				// page break after each team
				if($GLOBALS['AA_TC'] > 0) {				// not first team   
					$GLOBALS[$list]->insertPageBreak();
                   
				}
				$GLOBALS['AA_TC']++;		// team counter
                

				// set up list of other competitors
				$sep = '';
				$competitors = '';
				foreach($teamList as $comp)
				{
					if($comp['name'] != $team['name'])	// not current team
					{
						$competitors = $competitors . $sep . $comp['club'];	// club
						$sep = ', ';
					}
				}

				$GLOBALS[$list]->printHeader($team['club']." (".$team['name'].")", $category, $competitors);
			}
			else {
				$GLOBALS[$list]->printHeaderCombined($team['club']." (".$team['name'].")", $category);
			}

			$i = 0;
			foreach($team['athletes'] as $athlete)
			{
				if($i >= $evaluation) {	// show only athletes included in end result
					break;
				}
				$i++;

				$GLOBALS[$list]->printLineCombined($athlete['name'], $athlete['year'], $athlete['points'], $athlete['country']);
				$GLOBALS[$list]->printDisciplinesCombined($athlete['info']);
			}


			if(is_a($GLOBALS[$list], "PRINT_TeamSheet")
			|| is_a($GLOBALS[$list], "PRINT_TeamSheet_pdf")) {	// page for printing
				$GLOBALS[$list]->printTotal($team['points']);
				$GLOBALS[$list]->printFooter();
			}
			else {
				$GLOBALS[$list]->printTotalCombined($team['points']);
			}
		}	// FOREACH team

	}	// ET DB error all teams
    
   
}	// end function processCombined()


//
// compare function to sort teamList
// 
function AA_sheets_cmp ($a, $b) {
    if ($a["points"]== $b["points"]) return 0;
    return ($a["points"] > $b["points"]) ? -1 : 1;
}



//
// print list of relay athletes
// 
function AA_sheets_printRelayAthletes($relay)
{     	
    $sql = "
        SELECT
          at.Name
          , at.Vorname
          , at.Jahrgang
          , sta.Position
          , IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land     
      FROM
          athlet AS at 
          LEFT JOIN anmeldung AS a ON (at.xAthlet = a.xAthlet    ) 
          LEFT JOIN start AS st ON (a.xAnmeldung = st.xAnmeldung)
          LEFT JOIN staffelathlet AS sta ON (st.xStart = sta.xAthletenstart)    
          LEFT JOIN start AS s ON (sta.xStaffelstart = s.xStart)  
          LEFT JOIN region AS re ON (at.xRegion = re.xRegion)    
      WHERE 
          s.xStaffel = $relay  
      ORDER BY
          sta.Position
    ";   
     
    $at_res = mysql_query($sql);   

	if(mysql_errno() > 0) {		// DB error
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		while($at_row = mysql_fetch_row($at_res))
		{
			$year = AA_formatYearOfBirth($at_row[2]);
			$GLOBALS[$list]->printRelayAthlete("$at_row[3]. $at_row[0] $at_row[1], $year, $at_row[4]");
		}
		mysql_free_result($at_res);
	}
}
             


}	// AA_RANKINGLIST_SHEET_LIB_INCLUDED
?>
