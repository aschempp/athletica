<?php

/**********
 *
 *	track results
 *	
 */

if (!defined('AA_RESULTS_TRACK_LIB_INCLUDED'))
{
	define('AA_RESULTS_TRACK_LIB_INCLUDED', 1);

function AA_results_Track($round, $layout, $autoRank='')
{              
require('./lib/cl_gui_button.lib.php');
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_select.lib.php');

require('./config.inc.php');
require('./lib/common.lib.php');
require('./lib/heats.lib.php');
require('./lib/results.lib.php');
require('./lib/utils.lib.php');
include_once('./lib/timing.lib.php');

$presets = AA_results_getPresets($round);	// read GET/POST variables

$relay = AA_checkRelay($presets['event']);	// check, if this is a relay event

$svm = AA_checkSVM(0, $round); // decide whether to show club or team name  

$teamsm = AA_checkTeamSM(0, $round); 

// $flagMain=AA_getMainRound($round);
//   if ($flagMain) {
//
// terminate result processing
//
if($_GET['arg'] == 'results_done')
{   
	$eval = AA_results_getEvaluationType($round);  
	$combined = AA_checkCombined(0,$round); 
	$eventType=AA_getEventTypes($round);
	
	mysql_query("
		LOCK TABLES
			rundentyp_de READ
            , rundentyp_fr READ
            , rundentyp_it READ
			, runde READ
			, serie READ
            , serie AS s READ 
			, resultat READ
            , resultat AS r READ 
			, wettkampf READ
			, start WRITE
			, serienstart WRITE
            , serienstart AS ss WRITE 
	");

	if ( ($eval == $cfgEvalType[$strEvalTypeAll])  ||  
						  ($eval == $cfgEvalType[$strEvalTypeHeat] &&   (isset($eventType['club']))) ) 
	{	// eval all heats together
		$heatorder = "";             
	}
	else      
	{	// default: rank results per heat
		$heatorder = "s.xSerie, ";                  
	}     
	
	$nextRound = AA_getNextRound($presets['event'], $round);
	
	// if this is a combined event, rank all rounds togheter
	$roundSQL = "";
	if($combined){
		$roundSQL = "WHERE s.xRunde IN (";
		$res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$presets['event']);
		while($row_c = mysql_fetch_array($res_c)){
			$roundSQL .= $row_c[0].",";
		}
		$roundSQL = substr($roundSQL,0,-1).")";
	}else{
		$roundSQL = "WHERE s.xRunde = $round";
	}
	
	/*$result = mysql_query("
		SELECT
			resultat.Leistung
			, serienstart.xSerienstart
			, serienstart.xSerie
			, serienstart.xStart
			, serie.Wind
		FROM
			resultat
			, serienstart
			, serie
		WHERE resultat.xSerienstart = serienstart.xSerienstart
		
		AND serienstart.xSerie = serie.xSerie
		$roundSQL
		ORDER BY
			$heatorder
			resultat.Leistung ASC
	");  */   
	$sql = "SELECT DISTINCT 
				   r.Leistung, 
				   ss.xSerienstart, 
				   ss.xSerie, 
				   ss.xStart, 
				   s.Wind 
			  FROM resultat AS r
		 LEFT JOIN serienstart AS ss USING(xSerienstart) 
		 LEFT JOIN serie AS s USING(xSerie) 
			 ".$roundSQL." 
		  ORDER BY ".$heatorder."
				   r.Leistung ASC;";
	$result = mysql_query($sql);
   
	if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		$heat = 0;
		$perf = 0;
		$i = 0;
		$rank = 0;
		while($row = mysql_fetch_row($result))
		{
			// check on codes < 0
			if($row[0] < 0){
				mysql_query("UPDATE serienstart SET"
						. " Rang = 0"
						. " WHERE xSerienstart = " . $row[1]);
			   				
			}else{  
							
				if ( !($eval == $cfgEvalType[$strEvalTypeHeat] &&  (isset($eventType['club']))) ){ 
					 if(($eval != $cfgEvalType[$strEvalTypeAll])    // new heat
						&&($heat != $row[2]))
						{
						$i = 0;        // restart ranking   (not SVM with single heat)
						$perf = 0;
					 }                 
				}  	
					  
				$i++;							// increment ranking
				if($perf < $row[0]) {	// compare with previous performance
					$rank = $i;				// next rank (only if not same performance)   
				}
				
				mysql_query("UPDATE serienstart SET"                                
								. " Rang = " . $rank
								. " WHERE xSerienstart = " . $row[1]);    
	
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
	
				// keep performance for information (heat seeding)
				if($nextRound > 0){   
					mysql_query("
						UPDATE start SET
							start.Bestleistung = $row[0]
						WHERE start.xStart = $row[3]
					");
				}
	
				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
	
				$heat = $row[2];		// keep current heat ID
				$perf = $row[0];		// keep current performance
				
			}
		}
		mysql_free_result($result);
	}
	mysql_query("UNLOCK TABLES");

	AA_results_setNotStarted($round);	// update athletes with no result

	AA_utils_changeRoundStatus($round, $cfgRoundStatus['results_done']);
	if(!empty($GLOBALS['AA_ERROR'])) {
		AA_printErrorMsg($GLOBALS['AA_ERROR']);
	}

	AA_results_resetQualification($round);
}	// ET terminate results
 
 
//
// Qualify athletes after ranks are set
//
if(($_GET['arg'] == 'results_done')
 || ($_POST['arg'] == 'save_rank')
 || ($_POST['arg'] == 'set_qual'))
{
	// read qualification criteria
	$qual_top = 0;
	$qual_perf = 0;
	$result = mysql_query("SELECT QualifikationSieger"
											. ", QualifikationLeistung"
											. " FROM runde"
											. " WHERE xRunde = " . $round);

	if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		if(($row = mysql_fetch_row($result)) == TRUE);
		{
			$qual_top = $row[0];
			$qual_perf = $row[1];
		}
		mysql_free_result($result);
	}	// ET DB error
   
	// qualify top athletes for next round
	if($qual_top > 0)
	{
		mysql_query("LOCK TABLES serie READ, serie As s READ,  serienstart WRITE, serienstart AS ss WRITE");

		// get athletes by qualifying rank (random order if same rank)
		
               // don't limit rank    
               // don't update athletes who got 'waived' flag         
      
         // don't update athletes who got 'waived' flag                      
         $sql = "SELECT 
                        ss.xSerienstart
                        , ss.xSerie
                        , ss.Rang
                 FROM 
                        serienstart AS ss
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)  
                 WHERE 
                        ss.Rang > 0                        
                        AND s.xRunde = " . $round ."
                        AND ss.Qualifikation = 0 
                 ORDER BY 
                        ss.xSerie
                            , ss.Rang ASC
                            , RAND()";   
         
        $result = mysql_query($sql);    

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			$h = 0;
			unset($heats);		// clear array containing heats

			while($row = mysql_fetch_row($result))
			{
				if($h != $row[1]) {	// new heat
					if(count($starts) > 0) {	// count athletes
						$heats[] = $starts;		// keep athletes per heat
					}
					unset($starts);
					$c = 0;
				}
				$starts[$row[0]] = $row[2];	// keep athlete's rank
				$h = $row[1];						// keep heat
			}
			$heats[] = $starts;					// keep remaining athletes
			mysql_free_result($result);

			foreach($heats as $starts)		// process every heat
			{
				$rankcount = array_count_values($starts);	// count athletes/rank

				$q = 0;
				foreach($starts as $id=>$rank)	// process every athlete
				{
					// check if more athletes per rank than qualifying spots
					if($rankcount[$rank] > ($qual_top - $rank + 1)) {
						$qual = $cfgQualificationType['top_rand']['code'];
					}
					else {
						$qual = $cfgQualificationType['top']['code'];
					}

					if($q < $qual_top)		// not more than qualifying spots
					{   
						mysql_query("UPDATE serienstart SET"
										. " Qualifikation = " . $qual
										. " WHERE xSerienstart = " . $id);

						if(mysql_errno() > 0) {
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
						$q++;		// count nbr of qualified athletes
					}
				}

			}	// END loop every heat
		}	// ET DB error
		mysql_query("UNLOCK TABLES");
	}	// ET top athletes
	
	// qualify top performing athletes for next round
	if($qual_perf > 0)
	{
		mysql_query("LOCK TABLES resultat READ, serie READ, serienstart WRITE, resultat AS r READ, serie AS s READ, serienstart AS ss WRITE");

		// get remaining athletes by performance (random order if equal performance)

		/* other possible criteria to order equal performances:
		 * - ranking within heat (not implemented)
		 * - wind (not implemented)
		 */    
		
          $sql = "SELECT 
                        ss.xSerienstart
                        , r.Leistung
                        , ss.Qualifikation
                  FROM 
                        resultat AS r
                        LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                  WHERE     
                        r.Leistung > 0
                        AND (ss.Qualifikation = 0 
                                         OR ss.Qualifikation = ".$cfgQualificationType['waived']['code'].")   
                        AND s.xRunde = " . $round  ."
                        ORDER BY r.Leistung ASC
                        , RAND()";     
        
        $result = mysql_query($sql);     

		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			$i=1;
			$perf=0;
			$cWaived = 0;
			while($row = mysql_fetch_row($result))
			{
				// count waived qualifyings
				if($row[2] == $cfgQualificationType['waived']['code']){
					$cWaived++;
					continue;
				}
				
				if($i > $qual_perf) {	// terminate if enough top performers found
					if($perf != $row[1]) {	// last perf. worse than last qualified
						$perf=0;
					}
					break;
				}
				
				// if athletes waived on qualifying, set random code for next best athletes
				$code = $cfgQualificationType['perf']['code'];
				if($i+$cWaived > $qual_perf){
					$code = $cfgQualificationType['perf_rand']['code'];
				}
				
				mysql_query("UPDATE serienstart SET"
							. " Qualifikation = " . $code
							. " WHERE xSerienstart = " . $row[0]);

				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				$i++;
				$perf = $row[1];	// keep performance
			}

			// reset performance if enough qualifing spots
			if(mysql_num_rows($result) <= $qual_perf) {
				$perf=0;
			}

			mysql_free_result($result);

			// Change qualification type to "perf_rand" for athletes with same
			// performance as the 1st unqualified athlete
			if($perf != 0)
			{     				
                 $sql = "SELECT 
                                ss.xSerienstart
                         FROM 
                                resultat AS r
                                LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                                LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                         WHERE   
                                r.Leistung = " . $perf ."
                                AND ss.Qualifikation > 0  
                                AND s.xRunde = " . $round;   
        
                $result = mysql_query($sql);      

				if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else
				{
					while($row = mysql_fetch_row($result))
					{   
						mysql_query("UPDATE serienstart SET"
											. " Qualifikation = " . $cfgQualificationType['perf_rand']['code']
											. " WHERE xSerienstart = " . $row[0]);

						if(mysql_errno() > 0) {
							AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}
					}
					mysql_free_result($result);
				}
			} // ET unqualified athlete

		}	// ET DB error qualified by performance

		mysql_query("UNLOCK TABLES");
	}	// ET top performances
}


//
// calculate ranking points if needed
//
if(($_GET['arg'] == 'results_done')
|| ($_POST['arg'] == 'save_rank')){

	AA_utils_calcRankingPoints($round);    
 	
	// only for SVM with heat single --> set back the ranks per heat    
	if ($eval == $cfgEvalType[$strEvalTypeHeat] &&   (isset($eventType['club']))) {  
	          
	mysql_query("
		LOCK TABLES
			rundentyp_de READ
            , rundentyp_fr READ
            , rundentyp_it READ
			, runde READ
			, serie READ
            , serie AS s READ   
			, resultat READ   
            , resultat AS r READ   
			, serienstart WRITE
            , serienstart AS ss WRITE    
	");
	// if this is a combined event, rank all rounds togheter
	         	
	$heatorder = "s.xSerie, ";   
                   
	$roundSQL = "";
   
	if($combined){
		$roundSQL = " s.xRunde IN (";              
		$res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$presets['event']);
		while($row_c = mysql_fetch_array($res_c)){
			$roundSQL .= $row_c[0].",";
		}
		$roundSQL = substr($roundSQL,0,-1).")";
	}else{
		$roundSQL = " s.xRunde = $round";                
	}     
	
    $sql = "
        SELECT DISTINCT
            r.Leistung
            , ss.xSerienstart
            , ss.xSerie
            , ss.xStart
            , s.Wind
            , ss.Rang            
        FROM
            resultat AS r
            LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
            LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
        WHERE  
            $roundSQL
        ORDER BY
            $heatorder
            r.Leistung ASC
    ";          
   
    $result = mysql_query($sql);  
   
	if(mysql_errno() > 0) {        // DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else
	{
		$heat = 0;
		$perf = 0;
		$i = 0;
		$rank = 0;
		while($row = mysql_fetch_row($result))
		{
			// check on codes < 0
			if($row[0] < 0){
				mysql_query("UPDATE serienstart SET"
						. " Rang = 0"
						. " WHERE xSerienstart = " . $row[1]);
			}else{   
			  
				if(($eval != $cfgEvalType[$strEvalTypeAll])    // new heat
					&&($heat != $row[2]))
				{
					$i = 0;        // restart ranking
					$perf = 0;
				}
			   	if ($row[5] != 0) {  // rank     
			   	    
					$i++;                            // increment ranking
					if($perf < $row[0]) {    // compare with previous performance
						$rank = $i;                // next rank (only if not same performance)
						}
					mysql_query("UPDATE serienstart SET"
								. " Rang = " . $rank
								. " WHERE xSerienstart = " . $row[1]);     
	
					if(mysql_errno() > 0) {
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
						}   
			   	}       
			   
				$heat = $row[2];        // keep current heat ID
				$perf = $row[0];        // keep current performance   
			}
		}
		mysql_free_result($result);
	}
	
	mysql_query("UNLOCK TABLES");    
   
	}  // end:   only for SVM with heat single --> set back the ranks per heat
   	  
}

 if ($autoRank){        // automatic ranking returns to event monitor
   return;  
 }
 
//
// get results from timing system
//  - save directly in database
//
if($_GET['arg'] == "time_measurement"){

	AA_timing_getResultsManual($round);
	
}

//
// print HTML page header
//
AA_results_printHeader($presets['category'], $presets['event'], $round);

$mergedMain=AA_checkMainRound($round);
if ($mergedMain != 1) {
	
// read round data
if($round > 0)
{
	$status = AA_getRoundStatus($round);
	   
	// No action yet
	if(($status == $cfgRoundStatus['open'])
		|| ($status == $cfgRoundStatus['enrolement_done'])
		|| ($status == $cfgRoundStatus['heats_in_progress']))
	{
		AA_printWarningMsg($strHeatsNotDone);
	}
	// Enrolement pending
	else if($status == $cfgRoundStatus['enrolement_pending'])
	{
		AA_printWarningMsg($strEnrolementNotDone);
	}
	// Heat seeding completed, ready to enter results
	else if($status >= $cfgRoundStatus['heats_done'])
	{
		AA_heats_printNewStart($presets['event'], $round, "event_results.php");

		$nextRound = AA_getNextRound($presets['event'], $round);

		// show qualification form if another round follows
		if($nextRound > 0)
		{
			$result = mysql_query("
				SELECT
					QualifikationSieger
					, QualifikationLeistung
				FROM
					runde
				WHERE xRunde = $round
			");

			if(mysql_errno() > 0) {		// DB error
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else
			{
				if(($row = mysql_fetch_row($result)) == TRUE);
				{
?>
<p/>
<table class='dialog'>
	<tr>
	<form action='event_results.php' method='post' name='qualification'>
		<td class='dialog'>
			<input type='hidden' name='arg' value='set_qual' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<?php echo $strQualification . " " . $strQualifyTop; ?></td>
		<td class='dialog'>
			<input class='nbr' name='qual_top' type='text' maxlength='4'
				value='<?php echo $row[0]; ?>' /></td>
		<td class='dialog'>
			<?php echo $strQualification . " " . $strQualifyPerformance; ?></td>
		<td class='dialog'>
			<input class='nbr' name='qual_perf' type='text' maxlength='4'
				value='<?php echo $row[1]; ?>' /></td>
		<td>
			<button type='submit'>
				<?php echo $strChange; ?>
			</button>
		</td>
	</form>
	</tr>
</table>
<p/>
<?php
					$printed = TRUE;		// qualification parameters printed
				}	// ET round found
				mysql_free_result($result);
			}	// ET DB error
		}	// ET next round

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
         
        
		// display all athletes
		if($relay == FALSE) {		// single event
			  if ($teamsm){
                  $query = "SELECT 
                                r.Bahnen
                                , rt.Name
                                , rt.Typ
                                , s.xSerie
                                , s.Bezeichnung
                                , s.Wind
                                , s.Film
                                , an.Bezeichnung
                                , ss.xSerienstart
                                , ss.Position
                                , ss.Rang
                                , ss.Qualifikation
                                , a.Startnummer
                                , at.Name
                                , at.Vorname
                                , at.Jahrgang  
                                , t.Name 
                                , LPAD(s.Bezeichnung,5,'0') as heatid
                                , s.Handgestoppt
                                , at.Land   
                                , ss.Bemerkung  
                                , at.xAthlet                    
                         FROM 
                                runde AS r
                                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                                LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                                LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                                INNER JOIN teamsmathlet AS tat ON(st.xAnmeldung = tat.xAnmeldung)
                                LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                                LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                         WHERE
                                r.xRunde = " . $round ."   
                         ORDER BY heatid ".$order .", ss.Position";      

              }
              else {
                  $query = "SELECT 
                                r.Bahnen
                                , rt.Name
                                , rt.Typ
                                , s.xSerie
                                , s.Bezeichnung
                                , s.Wind
                                , s.Film
                                , an.Bezeichnung
                                , ss.xSerienstart
                                , ss.Position
                                , ss.Rang
                                , ss.Qualifikation
                                , a.Startnummer
                                , at.Name
                                , at.Vorname
                                , at.Jahrgang  
                                , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))  
                                , LPAD(s.Bezeichnung,5,'0') as heatid
                                , s.Handgestoppt
                                , at.Land   
                                , ss.Bemerkung  
                                , at.xAthlet                    
                         FROM 
                                runde AS r
                                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                                LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                                LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                                LEFT JOIN team AS t ON(a.xTeam = t.xTeam)
                                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                                LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                         WHERE
                                r.xRunde = " . $round ."   
                         ORDER BY heatid ".$order .", ss.Position";      

              }
              
		}
		else {								// relay event
			
            $query= "SELECT 
                            r.Bahnen
                            , rt.Name
                            , rt.Typ
                            , s.xSerie
                            , s.Bezeichnung
                            , s.Wind
                            , s.Film
                            , an.Bezeichnung
                            , ss.xSerienstart
                            , ss.Position
                            , ss.Rang
                            , ss.Qualifikation
                            , sf.Name
                            , if('".$svm."', t.Name, v.Name)  
                            , LPAD(s.Bezeichnung,5,'0') as heatid
                            , s.Handgestoppt
                            , ss.Bemerkung   
                     FROM 
                            runde AS r
                            LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN staffel AS sf ON (sf.xStaffel = st.xStaffel)
                            LEFT JOIN verein AS v ON (v.xVerein = sf.xVerein)                    
                            LEFT JOIN team AS t ON(sf.xTeam = t.xTeam)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                     WHERE 
                            r.xRunde = " . $round ."                          
                    ORDER BY heatid ".$order .", ss.Position";    

		}  
		$result = mysql_query($query);
       
		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			AA_results_printMenu($round, $status, $prog_mode, 'track');

			// initialize variables
			$h = 0;		// heat counter
			$p = 0;		// position counter (to evaluate empty heats
			$i = 0;		// input counter (an individual id is assigned to each
							// input field, focus is then moved to the next input
							// field by calling $i+1)
			$rowclass = 'odd';
			$tracks = 0;

			$btn = new GUI_Button('', '');	// create button object
?>
<p/>
<table class='dialog'>
<?php
			while($row = mysql_fetch_row($result))
			{
				$p++;			// increment position counter
/*
 *  Heat headerline
 */
				if($h != $row[3])		// new heat
				{
					$tracks = $row[0];	// keep nbr of planned tracks

					// fill previous heat with empty tracks
					if($p > 1) {
						printEmptyTracks($p, $tracks, 5+$c);
					}
	
					$h = $row[3];				// keep heat ID
					$p = 1;						// start with track one

					if(is_null($row[1])) {		// only one round
						$title = "$strFinalround";
					}
					else {		// more than one round
						$title = "$row[1]";
					}

					// increment colspan to include ranking and qualification
					$c = 0;
					if($status == $cfgRoundStatus['results_done']) {
						$c++;
						if($nextRound > 0) {
							$c++;
						}
					}

?>
	<tr>
		<form action='event_results.php#heat_<?php echo $row[4]; ?>' method='post'
			name='heat_id_<?php echo $h; ?>'>

		<th class='dialog' colspan='3'>
			<?php echo $title; ?>
			<input type='hidden' name='arg' value='change_heat_name' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
			<input class='nbr' type='text' name='id' maxlength='2'
				value='<?php echo $row[4]; ?>'
				onChange='document.heat_id_<?php echo $h;?>.submit()' />
				<a name='heat_<?php echo $row[4]; ?>' />
		</th>
		</form>
<?php
					if($status != $cfgRoundStatus['results_done'])
					{
?>
		<form action='controller.php' method='post'
			name='filmheat_<?php echo $row[4]; ?>' target='controller'>
		<th class='dialog' colspan='2'>
			<?php echo $strFilm; ?>
			<input type='hidden' name='act' value='saveFilm' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
			<input class='nbr' type='text' name='film' id='in_<?php echo $i; ?>'
				maxlength='3' value='<?php echo $row[6];?>'
				onChange="submitForm(document.filmheat_<?php echo $row[4]; ?>, 'in_<?php echo $i+1; ?>')" />
		</th>
		</form>
<?php
						$i++;		// next element
					}
					else {	// results done
?>
		<th class='dialog' colspan='2'>
			<?php echo $strFilm . " " . $row[6]; ?>
		</th>
<?php
					}
					// track discipline with wind
					if($layout == $cfgDisciplineType[$strDiscTypeTrack])
					{
						if($status != $cfgRoundStatus['results_done'])
						{
?>
		<form action='controller.php' method='post'
			name='windheat_<?php echo $row[4]; ?>' target='controller'>
		<th class='dialog' colspan='<?php echo 1+$c; ?>'>
			<?php echo $strWind; ?>
			<input type='hidden' name='act' value='saveWind' />
			<input type='hidden' name='obj' value='windheat_<?php echo $row[4]; ?>' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
			<input class='nbr' type='text' name='wind'  id='in_<?php echo $i; ?>'
				maxlength='5' value='<?php echo $row[5];?>'
				onChange="submitForm(document.windheat_<?php echo $row[4]; ?>, 'in_<?php echo $i+1; ?>')" />
		</th>
		</form>
<?php
							$i++;		// next element
						}
						else {	// results done
?>
		<th class='dialog' colspan='2'>
			<?php echo $strWind . " " . $row[5]; ?>
		</th>
<?php
						}
					}
					else	// no wind
					{
?>
		<th class='dialog' colspan='<?php echo 1+$c; ?>' />
<?php
					}	// ET track discipline with wind
					
					// can set "hand taken time"
					if($row[18] == 1 && $relay == false){
						$handstopped = "checked";
					}elseif($row[15] == 1 && $relay == true){
						$handstopped = "checked";
					}else{
						$handstopped = "";
					}
					if($status != $cfgRoundStatus['results_done']){
?>
		<form action='controller.php' method='post'
			name='handstopped_<?php echo $row[4]; ?>' target='controller'>
		<th class='dialog'><?php echo $strHandStopped ?> 
			<input type='hidden' name='act' value='saveHandStopped' />
			<input type='hidden' name='obj' value='handstopped_<?php echo $row[4]; ?>' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='item' value='<?php echo $row[3]; ?>' />
			<input type="checkbox" name="handstopped" id='in_<?php echo $i; ?>'
				onChange="submitForm(document.handstopped_<?php echo $row[4]; ?>, 'in_<?php echo $i+1; ?>')"
				<?php echo $handstopped ?> >
		</th>
		</form>
<?php
						$i++; // next element
					}else{
?>
		<th class='dialog'><?php echo $strHandStopped ?>
			<input type="checkbox" name="handstopped" <?php echo $handstopped ?> disabled>
		</th>
<?php
					}
					
?>
	</tr>
<?php
/*
 *  Column header
 */
					if($relay == FALSE) {	// athlete display
?>
	<tr>
		<th class='dialog'><?php echo $strPositionShort; ?></th>
		<th class='dialog' colspan='2'><?php echo $strAthlete; ?></th>
		<th class='dialog'><?php echo $strYearShort; ?></th>
		<th class='dialog'><?php echo $strCountry; ?></th>
		<th class='dialog'><?php if($svm){ echo $strTeam; } elseif ($teamsm){ echo $strTeamsm;} else {echo $strClub;} ?></th>
		<th class='dialog'><?php echo $strPerformance; ?></th>
        
<?php
					}
					else {		// relay display
?>
	<tr>
		<th class='dialog'><?php echo $strPositionShort; ?></th>
		<th class='dialog'><?php echo $strRelay; ?></th>
		<th class='dialog'><?php if($svm){ echo $strTeam; }else{ echo $strClub;} ?></th>
		<th class='dialog'><?php echo $strPerformance; ?></th>
       
<?php
					}
					if($status == $cfgRoundStatus['results_done']) {
?>
		<th class='dialog'><?php echo $strRank; ?></th>
<?php
						if($nextRound > 0) {
?>
		<th class='dialog'><?php echo $strQualification; ?></th>
<?php
						}
					}

?>
<th class='dialog'><?php echo $strResultRemark; ?></th>     
	</tr>
<?php
				}		// ET new heat

/*
 * Empty tracks
 */
				if(($layout == $cfgDisciplineType[$strDiscTypeTrack])
					|| ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
					|| ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
				{
					// current track and athlete's position not identical
					if($p < $row[9]) {
						$p = printEmptyTracks($p, ($row[9]-1), 5+$c);
					}
				}	// ET empty tracks

/*
 * Athlete data lines
 */
				$p = $row[9];			// keep position
				if($p % 2 == 0) {		// even row numer
					$rowclass='even';
				}
				else {							// odd row number
					$rowclass='odd';
				}	

				if($relay == FALSE) {
?>
	<tr class='<?php echo $rowclass; ?>'>
		<td class='forms_right'><?php echo $row[9]; /* position */ ?></td>
		<td class='forms_right'><?php echo $row[12]; /* start nbr */ ?></td>
		<td><?php echo $row[13] . " " . $row[14];  /* name */ ?></td>
		<td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[15]); ?></td>
		<td><?php echo (($row[19]!='' && $row[19]!='-') ? $row[19] : '&nbsp;')?></td>
		<td><?php echo $row[16]; /* club */ ?></td>
      
<?php
				}
				else {	// relay
?>
	<tr class='<?php echo $rowclass; ?>'>
		<td class='forms_right'><?php echo $row[9]; /* position */ ?></td>
		<td><?php echo $row[12]; /* relay name */ ?></td>
		<td><?php echo $row[13];  /* club */ ?></td>
<?php
				}

				$sql = "SELECT rs.xResultat, 
							   rs.Leistung, 
							   rs.Info, 
							   d.Strecke 
						  FROM resultat AS rs 
					 LEFT JOIN serienstart AS ss USING(xSerienstart) 
					 LEFT JOIN serie AS se USING(xSerie) 
					 LEFT JOIN runde AS ru USING(xRunde) 
					 LEFT JOIN wettkampf AS w USING(xWettkampf) 
					 LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d USING(xDisziplin) 
						 WHERE rs.xSerienstart = ".$row[8]." 
					  ORDER BY rs.Leistung ASC;";
				$res = mysql_query($sql);

				if(mysql_errno() > 0) {		// DB error
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else
				{
					$perf = '';
					$resrow = mysql_fetch_array($res);
					if($resrow != NULL) {		// result found
						/*$secflag = false;
						if(substr($resrow[1],0,2) >= 60){
							$secflag = true;
						}*/
						$secflag = (intval($resrow['Strecke'])<=400);
						$perf = AA_formatResultTime($resrow[1], false, $secflag);
					}

					if($status != $cfgRoundStatus['results_done'])
					{
?>
		<form action='controller.php' method='post'
			name='perf_<?php echo $i; ?>' target='controller'>
		<td>
			<input type='hidden' name='act' value='saveResult' />
			<input type='hidden' name='obj' value='perf_<?php echo $i; ?>' />
			<input type='hidden' name='type' value='<?php echo $layout; ?>' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='start' value='<?php echo $row[8]; ?>' />
			<input type='hidden' name='item' value='<?php echo $resrow[0]; ?>' />
			<input class='perftime' type='text' name='perf' id='in_<?php echo $i; ?>'
				maxlength='12' value='<?php echo $perf; ?>'
				onChange="submitForm(document.perf_<?php echo $i; ?>, 'in_<?php echo $i+1; ?>')" />
		</td>
		</form>
        
        <form action='controller.php' method='post'
            name='remark_<?php echo $i; ?>' target='controller'>
        <td>
            <input type='hidden' name='act' value='saveResult' />
            <input type='hidden' name='obj' value='perf_<?php echo $i; ?>' />
            <input type='hidden' name='type' value='<?php echo $layout; ?>' />
            <input type='hidden' name='round' value='<?php echo $round; ?>' />
            <input type='hidden' name='start' value='<?php echo $row[8]; ?>' />
            <input type='hidden' name='item' value='<?php echo $resrow[0]; ?>' />
            <input type='hidden' name='xAthlete' value='<?php echo $row[21]; ?>' />   
            <input class='textshort' type='text' name='remark' id='in_<?php echo $i; ?>'
                maxlength='7' value='<?php if ($relay){echo $row[16];} else {echo $row[20]; }?>'
                onChange="submitForm(document.remark_<?php echo $i; ?>, 'in_<?php echo $i+1; ?>')" />
        </td>
        </form>
        
       
<?php
						$i++;		// next element
					}
					else {	// results done
?>
		<td class='forms_right'><?php echo $perf; ?></td>
<?php
					}

					mysql_free_result($res);

					if($status == $cfgRoundStatus['results_done'])
					{
						if($row[10] > 0)	// rank OK, athlete has valid result
						{
?>
		<form action='event_results.php' method='post'
			name='rank_<?php echo $i; ?>'>
		<td>
			<input type='hidden' name='arg' value='save_rank' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='focus' value='rank_<?php echo $i; ?>' />
			<input type='hidden' name='item' value='<?php echo $row[8]; ?>' />
			<input class='nbr' type='text' name='rank' maxlength='3'
				value='<?php echo $row[10]; ?>'
				onChange='document.rank_<?php echo $i; ?>.submit()' />
		</td>
		</form>     
        
       
<?php
							$i++;		// next element

							if($nextRound > 0) { 
?>
		<form action='event_results.php' method='post'
			name='qual_<?php echo $i; ?>'>
		<td>
			<input type='hidden' name='arg' value='change_qual' />
			<input type='hidden' name='round' value='<?php echo $round; ?>' />
			<input type='hidden' name='focus' value='qual_<?php echo $i; ?>' />
			<input type='hidden' name='item' value='<?php echo $row[8]; ?>' />
			<input type='hidden' name='oldqual' value='<?php echo $row[11]; ?>' />
			<input type='hidden' name='heat' value='<?php echo $row[3]; ?>' />
<?php

								$dropdown = new GUI_Select('qual', 1, "document.qual_$i.submit()");
								$dropdown->addOptionNone();
								foreach($cfgQualificationType as $type)
								{
									$dropdown->addOption($type['text'], $type['code']);
									if($type['code'] == $row[11]) {
										$dropdown->selectOption($type['code']);
									}
								}
								$dropdown->printList();
?>
		</td>
		</form>
<?php
								$i++;		// next element
							}	// qualification info
                           
						}
						else
						{	// no rank
?>
                        <td /> 
                        
                        
<?php
							
						}	// ET no rank
                        
?>                        
                     <td class='perftime'><?php if ($relay){echo $row[16];} else {echo $row[20]; } ?></td>      
<?php                        
					}	// ET 'results_done'
?>
		<td>
<?php
					$btn->set("event_results.php?arg=del_start&item=$row[8]&round=$round", $strDelete);
					$btn->printButton();
?>
		</td>
<?php
				}	// ET DB error
               
			}

			// Fill last heat with empty tracks for disciplines run in
			// individual tracks
			if(($layout == $cfgDisciplineType[$strDiscTypeTrack])
				|| ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
				|| ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
			{
				if($p > 0) {	// heats set up
					$p++;
					printEmptyTracks($p, $tracks, 5+$c);
				}
			}	// ET track disciplines
?>
</table>
<?php
			mysql_free_result($result);
		}		// ET DB error
	}
}		// ET round selected

if(!empty($presets['focus'])) {
?>

<script type="text/javascript">
<!--
	if(<?php echo $presets['focus']; ?>.rank) {
		<?php echo $presets['focus']; ?>.rank.focus();
		<?php echo $presets['focus']; ?>.rank.select();
		window.scrollBy(0,200);
	}
	else if(<?php echo $presets['focus']; ?>.qual) {
		<?php echo $presets['focus']; ?>.qual.focus();
		window.scrollBy(0,200);
	}
//-->
</script>
<?php
}
?>
</body>
</html>
<?php
}
else
{
		AA_printErrorMsg($strErrMergedRound); 
	} 
}



/**
 * print empty tracks
 * ------------------
 * arg 1 (int): heat position
 * arg 2 (int): up to this position
 * arg 3 (int): column span
 *
 * returns next position
 */
function printEmptyTracks($position, $last, $span)
{
	require('./lib/common.lib.php');
	include('./config.inc.php');

	while($position <= $last)
	{
		// switch row class again
		if($position % 2 == 0) {			// even row numer
			$rowclass='even';
		}
		else {						// odd row number
			$rowclass='odd';
		}	
?>
	<tr class='<?php echo $rowclass; ?>'>
		<td class='forms_right'><?php echo $position; ?></td>
		<td colspan='<?php echo $span; ?>'><?php echo $strEmpty; ?></td>
	</tr>
<?php
		$position++;
	}

	return $position;
}


}	// AA_RESULTS_TRACK_LIB_INCLUDED
?>
