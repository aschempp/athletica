<?php

/**********
 *
 *	track results (regie)
 *	
 */
        

if (!defined('AA_SPEAKER_RESULTS_TRACK_LIB_INCLUDED'))
{
	define('AA_SPEAKER_RESULTS_TRACK_LIB_INCLUDED', 1);    
   

function AA_regie_Track($event, $round, $layout, $cat, $disc)
{
	require('./lib/cl_gui_menulist.lib.php');
	require('./lib/cl_gui_resulttable.lib.php');
	require('./config.inc.php');
	require('./lib/common.lib.php');
    require('./lib/results.lib.php'); 

	$relay = AA_checkRelay($event);	// check, if this is a relay event
  
	$status = AA_getRoundStatus($round);

    $combined = AA_checkCombined(0,$round);
	$eval = AA_results_getEvaluationType($round);
    
    $svm = AA_checkSVM(0, $round); // decide whether to show club or team name  
    
    if ( ($eval == $cfgEvalType[$strEvalTypeAll])  ||  
                          ($eval == $cfgEvalType[$strEvalTypeHeat] &&   (isset($eventType['club']))) ) 
    {    // eval all heats together
        $heatorder = "";
    }
    else      
    {    // default: rank results per heat
        $heatorder = "serie.xSerie, ";
    }     

		// show qualification info if another round follows
	$nextRound = AA_getNextRound($event, $round);

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
					echo "$strQualification: $row[0] $strQualifyTop, $row[1] $strQualifyPerformance";
					echo "<p/>";
				}
			}	// ET DB error
			mysql_free_result($result);
		}	// ET next round
          
       mysql_query("
                LOCK TABLES
                    resultat READ
                    , serie READ
                    , start READ                    
                    , serienstart READ
                     , runde as r READ  
                    , resultat as r READ
                    , serie as s READ
                    , start as st READ                    
                    , serienstart as ss READ
                    , anmeldung as a READ 
                    , athlet as at READ 
                    , verein as v READ 
                    , anlage as an READ 
                    , rundentyp_de as rt READ  
                    , rundentyp_fr as rt READ
                    , rundentyp_it as rt READ 
                    , tempTrack WRITE
            ");
                                                                  
      
        mysql_query("TRUNCATE TABLE tempTrack");           
    
        // if this is a combined event, rank all rounds togheter
        $roundSQL = "";
        if($combined){
            $roundSQL = "WHERE serie.xRunde IN (";
            $res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = $event");
            while($row_c = mysql_fetch_array($res_c)){
                $roundSQL .= $row_c[0].",";
            }
            $roundSQL = substr($roundSQL,0,-1).")";
        }else{
            $roundSQL = "WHERE serie.xRunde = $round";
        }       
        
       
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {                  
            $sql = "SELECT DISTINCT 
                           resultat.Leistung, 
                           serienstart.xSerienstart, 
                           serienstart.xSerie, 
                           serienstart.xStart, 
                           serie.Wind 
                     FROM 
                           resultat 
                           LEFT JOIN serienstart USING(xSerienstart) 
                           LEFT JOIN serie USING(xSerie) 
                           ".$roundSQL." 
                     ORDER BY ".$heatorder."
                            resultat.Leistung ASC;";   
                   
            $result = mysql_query($sql);         
            $heat = 0;
            $perf = 0;
            $perfRounded = 0;
            $i = 0;
            $rank = 0;
          
            while($row = mysql_fetch_row($result))
            {
                // check on codes < 0
                if($row[0] < 0){
                    mysql_query("INSERT INTO tempTrack SET"
                            . " Leistung = " . $row[0] 
                            . " , xSerienstart = " . $row[1]  
                            . " , xSerie = " . $row[2]  
                            . " , rang = 0");     
                                   
                }else{  
                                
                    if ( !($eval == $cfgEvalType[$strEvalTypeHeat] &&  (isset($eventType['club']))) ){ 
                         if(($eval != $cfgEvalType[$strEvalTypeAll])    // new heat
                            &&($heat != $row[2]))
                            {
                            $i = 0;        // restart ranking   (not SVM with single heat)
                            $perf = 0;
                            $perfRounded = 0;  
                         }                 
                    }      
                          
                    $i++;                            // increment ranking
                    if($perf < $row[0]) {    // compare with previous performance
                        $rank = $i;                // next rank (only if not same performance)   
                    }
                    
                    mysql_query("INSERT INTO tempTrack SET "    
                                    . " Leistung = " . $row[0] 
                                    . " , xSerienstart = " . $row[1]  
                                    . " , xSerie = " . $row[2]                              
                                    . " , rang = " . $rank);                        
        
                    if(mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
        
                  
        
                    if(mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
        
                    $heat = $row[2];        // keep current heat ID
                    $perf = $row[0];        // keep current performance                        
                }              
     
            mysql_free_result($temp);
        }                 
  
		// display all athletes
		if($relay == FALSE) {		// single event
			$query = ("
				SELECT
					r.Bahnen
					, rt.Name
					, rt.Typ
					, s.xSerie
					, s.Bezeichnung
					, s.Wind
					, s.Film
					, s.Status
					, ss.xSerienstart
					, ss.Position
					, ss.Rang
					, ss.Qualifikation
					, a.Startnummer
					, at.Name
					, at.Vorname
					, at.Jahrgang
					, if('".$svm."', te.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))  
					, LPAD(s.Bezeichnung,5,'0') as heatid
					, at.Land
					, st.Bestleistung
					, at.xAthlet
                    , t.rang  
                    , if (t.Rang > 0, t.Rang, 999999 ) as orderRang                       
				FROM
					runde AS r
					LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
					LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
					LEFT JOIN start AS st ON (st.xStart = ss.xStart)
					LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
					LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
					LEFT JOIN verein AS v  ON (v.xVerein = at.xVerein)
                    LEFT JOIN team AS te ON(a.xTeam = te.xTeam)
                    LEFT JOIN tempTrack AS t ON (t.xSerienstart = ss.xSerienstart)  
				    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
				    LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
				WHERE 
                    r.xRunde = $round    				
				ORDER BY
					heatid
                     , orderRang , ss.Position       
			");        
		}
		else {								// relay event
			$query = ("
				SELECT
					r.Bahnen
					, rt.Name
					, rt.Typ
					, s.xSerie
					, s.Bezeichnung
					, s.Wind
					, s.Film
					, s.Status
					, ss.xSerienstart
					, ss.Position
					, ss.Rang
					, ss.Qualifikation
					, sf.Name
					, if('".$svm."', te.Name, v.Name)  
					, LPAD(s.Bezeichnung,5,'0') as heatid
					, r.xRunde
					, st.xStart
                    , t.rang 
                    , if (t.Rang > 0, t.Rang, 999999 ) as orderRang
				FROM
					runde AS r
					LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
					LEFT JOIN serienstart AS ss  ON (ss.xSerie = s.xSerie )
					LEFT JOIN start AS st ON (st.xStart = ss.xStart) 
					LEFT JOIN staffel AS sf ON (sf.xStaffel = st.xStaffel) 
					LEFT JOIN verein AS v ON (v.xVerein = sf.xVerein) 
                    LEFT JOIN team AS te ON(sf.xTeam = te.xTeam)
                    LEFT JOIN tempTrack AS t ON (t.xSerienstart = ss.xSerienstart)   
				    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
				    LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
				WHERE 
                    r.xRunde = $round  				
				ORDER BY
					heatid
					, orderRang , ss.Position       
			");
		}           
       
		$result = mysql_query($query);

		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			// initialize variables
			$h = 0;		// heat counter
			$p = 0;		// position counter (to evaluate empty heats
			$tracks = 0;
                       
			$resTable = new GUI_TrackResultTable($round, $layout, $status, $nextRound);
            $resTable->printHeatTitleRegie($cat, $disc);  
            
			while($row = mysql_fetch_row($result))
			{
				$p++;			// increment position counter
/*
 *  Heat headerline
 */
				if($h != $row[3])		// new heat
				{
					$tracks = $row[0];	// keep nbr of planned tracks
                    						
					$h = $row[3];				// keep heat ID
					$p = 1;						// start with track one

					if(is_null($row[1])) {		// only one round
						$title = "$strFinalround $row[4]";
					}
					else {		// more than one round
						$title = "$row[1]: $row[2]$row[4]";
					}

					// increment colspan to include ranking and qualification
					$c = 0;
					if($status == $cfgRoundStatus['results_done']) {
						$c++;
						if($nextRound > 0) {
							$c++;
						}
					}
                   
					$resTable->printHeatTitle($row[3], $row[4], $title, $row[7], $row[6], $row[5], 'regie', $relay);

					if($relay == FALSE) {	// athlete display
						$resTable->printAthleteHeader('regie', $round);
					}
					else {		// relay display
						$resTable->printRelayHeader('regie', $round);
					}
				}		// ET new heat



/*
 * Athlete/Relay data lines
 */
				// get performance
				$perf = '';
                $perfRounded = '';
				$res = mysql_query("
					SELECT
						rs.xResultat
						, rs.Leistung
						, rs.Info
					FROM
						resultat AS rs
					WHERE rs.xSerienstart = $row[8]
					ORDER BY
						rs.Leistung ASC
				");
              
				if(mysql_errno() > 0) {		// DB error
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else
				{
					$resrow = mysql_fetch_row($res);
					if($resrow != NULL) {		// result found
						$perf = AA_formatResultTime($resrow[1]);
                        $perfRounded = AA_formatResultTime($resrow[1], true);  
					}
					mysql_free_result($res);
				}	// ET DB error

				// print lines
                
				if($relay == FALSE) {
					  $resTable->printAthleteLine($row[9], $row[12]
                            , "$row[13] $row[14]", '',
                             $row[16], AA_formatResultTime($row[19], true), $perfRounded, $row[10], $row[11], '', $row[20], 'regie', $row[21]);                       
				}
				else {	// relay
					
					// get Athletes
					$arrAthletes = array();
					$sql = "SELECT at.Vorname, at.Name FROM
								staffelathlet as sfat
								LEFT JOIN start as st ON sfat.xAthletenstart = st.xStart
								LEFT JOIN anmeldung as a USING(xAnmeldung)
								LEFT JOIN athlet as at USING(xAthlet)
							WHERE
								sfat.xStaffelstart = $row[16]
							AND	sfat.xRunde = $row[15]
							ORDER BY
								sfat.Position";
					$res_at = mysql_query($sql);
					if(mysql_errno() > 0) {		// DB error
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}else{
						while($row_at = mysql_fetch_array($res_at)){
							$arrAthletes[] = array($row_at[1], $row_at[0]);
						}
					}
					
					$arrAthletes = (count($arrAthletes)>0) ? $arrAthletes : 0;
					
					$resTable->printRelayLine($row[9], $row[12], $row[13] 
							, $perfRounded, $row[10], $row[11], $arrAthletes, 'regie', $row[17]);                      

				}
			}

			// Fill last heat with empty tracks for disciplines run in
			// individual tracks
			if(($layout == $cfgDisciplineType[$strDiscTypeTrack])
				|| ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
				|| ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
			{
				if($p > 0) {	// heats set up
					$p++;
					$resTable->printEmptyTracks($p, $tracks, 6+$c);
				}
			}	// ET track disciplines
           
			$resTable->endTable();
			mysql_free_result($result);
		}		// ET DB error
        }
    
        mysql_query("UNLOCK TABLES");  
  
}	// End function AA_regie_Track


}	// AA_SPEAKER_RESULTS_TRACK_LIB_INCLUDED
?>
