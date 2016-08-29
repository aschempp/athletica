<?php

/**********
 *
 *	tech results regie
 *	
 */

if (!defined('AA_SPEAKER_RESULTS_TECH_LIB_INCLUDED'))
{
	define('AA_SPEAKER_RESULTS_TECH_LIB_INCLUDED', 1);

function AA_regie_Tech($event, $round, $layout, $cat, $disc)  
{
	require('./lib/cl_gui_resulttable.lib.php');
	require('./config.inc.php');
	require('./lib/common.lib.php');
	require('./lib/results.lib.php');
    require('./lib/cl_gui_page.lib.php');
    require('./lib/cl_gui_menulist.lib.php');      

	$status = AA_getRoundStatus($round);    
    $eval = AA_results_getEvaluationType($round);
    $combined = AA_checkCombined(0, $round);    
    
   $prog_mode = AA_results_getProgramMode();   
   
    $svm = AA_checkSVM(0, $round); // decide whether to show club or team name
    
     // if this is a combined event, rank all rounds togheter
    $roundSQL = "";   
    $roundSQL2 = "";  
    if($combined){
        $roundSQL = "AND s.xRunde IN ("; 
        $roundSQL2 = " s.xRunde IN (";
        $res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$event);
        while($row_c = mysql_fetch_array($res_c)){
            $roundSQL .= $row_c[0].",";
            $roundSQL2 .= $row_c[0].",";
        }
        $roundSQL = substr($roundSQL,0,-1).")";
        $roundSQL2 = substr($roundSQL2,0,-1).")";
    }else{
        $roundSQL = "AND s.xRunde = $round";
        $roundSQL2 = " s.xRunde = $round";
    }  
     $countAthlete = 0;
     $sql_at = "SELECT 
                   Count(*)
             FROM 
                    start AS s                      
             WHERE   
                    s.xWettkampf = " .$event;
     $result_at = mysql_query($sql_at);  
     if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
     }       
     $row_at = mysql_fetch_row($result_at);   
     if (mysql_num_rows($result_at) > 0){
         $countAthlete =  $row_at[0];
     }    
            
	 $r = 0;       
     $sql = "SELECT 
                    COUNT(*), 
                    ru.Versuche
             FROM 
                    resultat AS r
                    LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                    LEFT JOIN runde AS ru ON (s.xRunde = ru.xRunde)   
             WHERE   
                    $roundSQL2 
             GROUP BY r.xSerienstart
             ORDER BY 1 DESC";    
    
    $result = mysql_query($sql);   
    
    $row = mysql_fetch_row($result);
    
    if (mysql_num_rows($result) > 0) {          
         $r = $row[0];      // max. attempts                 
    }
   
    $r_attempts = $row[1];
     
         mysql_query("
                LOCK TABLES
                    resultat READ
                    , serie READ
                    , start READ 
                    , resultat as r READ
                    , serie as s READ
                    , start as st READ 
                    , serienstart as ss READ   
                    , wettkampf as w READ
                    , serienstart READ
                    , anmeldung as a READ 
                    , athlet as at READ 
                    , verein as v READ 
                    , rundentyp_de as rt READ
                    , rundentyp_fr as rt READ
                    , rundentyp_it as rt READ   
                    , tempTech WRITE
                    , tempTech as t READ  
            ");  
                                                                  
          mysql_query("DROP TABLE IF EXISTS `tempTech`");    // temporary table          
            
            // Set up a temporary table to hold all results for ranking.
            // The number of result columns varies according to the maximum
            // number of results per athlete.  
                        
            $qry = "
                CREATE TEMPORARY TABLE IF NOT EXISTS tempTech (
                    xSerienstart int(11)
                    , xSerie int(11)
                    , rang int(11)
                     , position int(11)";

            for($i=1; $i <= $r; $i++) {
                $qry = $qry . ", Res" . $i . " int(9) default '0'";
                $qry = $qry . ", Wind" . $i . " char(5) default '0'";
            }
            $qry = $qry . ") ENGINE=HEAP";
           
            mysql_query($qry);    // create temporary table      

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {  
                $result = mysql_query("
                    SELECT
                        r.Leistung
                        , r.Info
                        , ss.xSerienstart
                        , ss.xSerie
                        , ss.Position
                    FROM
                        resultat AS r
                        LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS s On (ss.xSerie = s.xSerie)
                    WHERE    
                        r.Leistung >= 0
                        $roundSQL 
                    ORDER BY                             
                        ss.xSerienstart
                        ,r.Leistung DESC
                ");
                
                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else
                {     
                    // initialize variables
                    $ss = 0;
                    $i = 0;
                    // process every result
                    while($row = mysql_fetch_row($result))
                    {
                        if($ss != $row[2])     // next athlete
                        {
                            // add one row per athlete to temp table
                            if($ss != 0) {
                                for(;$i < $r; $i++) { // fill remaining result cols.
                                    $qry = $qry . ",0,''";
                                }
                                
                                mysql_query($qry . ")");
                                 
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());     
                                }
                            }
                            // (re)set SQL statement
                            $qry = "INSERT INTO tempTech VALUES($row[2],$row[3], 0, $row[4]";
                            $i = 0;
                        }
                        $qry = $qry . ",$row[0],'$row[1]'";    // add current result to query
                        $ss = $row[2];                // keep athlete's ID
                        $i++;                                // count nbr of results
                    }
                    mysql_free_result($result);                                                              
              
                    // insert last pending data in temp table
                    if($ss != 0) {
                        for(;$i < $r; $i++) {    // fill remaining result cols.
                            $qry = $qry . ",0,''";
                        }
                        mysql_query($qry . ")");
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                    }
                }

                if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
                    $qry = "
                        SELECT
                            *
                        FROM
                            tempTech
                        ORDER BY
                            xSerie";

                    for($i=1; $i <= $r; $i++) {
                        $qry = $qry . ", Res" . $i . " DESC";
                    }  
                }
                else {    // default: rank results from all heats together
                    $qry = "
                        SELECT
                            *
                        FROM
                            tempTech
                        ORDER BY ";
                    $comma = "";
                    // order by available result columns
                    for($i=1; $i <= $r; $i++) {
                        $qry = $qry . $comma . "Res" . $i . " DESC";
                        $comma = ", ";
                    }  
                    if ($r == 0){
                         $qry = substr($qry,0,-9);
                    }  
                }                        
                $result = mysql_query($qry);

                if(mysql_errno() > 0) {        // DB error                          
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {
                    // initialize variables
                    $heat = 0;
                    $perf_old[] = '';
                    $j = 0;
                    $rank = 0;
                    // set rank for every athlete
                    while($row = mysql_fetch_row($result))
                    {
                        for($i=0; $i <= $r; $i++) {
                            $perf[$i] = $row[(2*$i)+3];
                            $wind[$i] = $row[(2*$i)+4];
                        }

                        if(($eval == $cfgEvalType[$strEvalTypeHeat])    // new heat
                            &&($heat != $row[1]))
                        {
                            $j = 0;        // restart ranking
                            $perf_old[] = '';
                        }

                        $j++;                                // increment ranking
                        if($perf_old != $perf) {    // compare performances
                            $rank = $j;    // next rank (only if not same performance)
                        }
                       
                        mysql_query("
                            UPDATE tempTech SET
                                rang = $rank
                            WHERE xSerienstart = $row[0]
                        ");

                        if(mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                        $heat = $row[1];        // keep current heat ID
                        $perf_old = $perf;
                    }
                    mysql_free_result($result);
                }

            }
            
        // find current athlete (show yellow) when more than one attempt
        $roundSQL_C = substr($roundSQL,4);
        $sql_curr="SELECT 
                    count( * ) , 
                    ss.xSerienstart,
                    if (ss.Position2 > 0, if (ss.Position3 > 0, ss.Position3, ss.Position2) , ss.Position ) as posOrder,
                    s.MaxAthlet,
                    ss.Position2,
                    ss.Position3
              FROM 
                    resultat as r 
                    LEFT JOIN serienstart AS ss ON ( r.xSerienstart = ss.xSerienstart)
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie )
              WHERE                     
                    $roundSQL_C  
              GROUP BY ss.xSerienstart
              ORDER BY posOrder ";

        $res_curr = mysql_query($sql_curr);  
        if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        
        }
        else {
            $ss = 0;
            $keep_ss = 0;                // current athlete to show yellow
            $keep_ss_first = 0;          // first athlete is current when all have same count of attempts
            $c = 0;
            $z = 0;
            $maxAthlete = 0;
           
            while ($row_curr = mysql_fetch_row($res_curr)) {
                if ($z > 0 && $z == $maxAthlete){
                    break;
                }
                if  ($c > 0){
                    if ($row_curr[0] < $c){
                         $keep_ss_save = $keep_ss;
                         $keep_ss = $row_curr[1];
                         break;
                    }
                }
                $c = $row_curr[0];
                $ss = $row_curr[1]; 
                if ($z == 0){
                    $keep_ss_first =  $row_curr[1];
                    if ($row_curr[4] > 0 || $row_curr[5] > 0) {
                          $maxAthlete = $row_curr[3]; 
                    }
                    
                }
                $z++; 
            }
        }  
        if ($prog_mode == 0) {
            if ($keep_ss == 0 && $r < $r_attempts) {
                $keep_ss =  $keep_ss_first;
            }
            else {
                 if ($r < $r_attempts){
                     $keep_ss = $keep_ss_save;
                 }
            }
        } 
        else {                 
             if ($keep_ss == 0 && $z%$countAthlete==0){                  
                   $keep_ss =  $keep_ss_first;
             }
        }  
        
       
	  
		
		$arg = (isset($_GET['arg1'])) ? $_GET['arg1'] : ((isset($_COOKIE['sort_regie'])) ? $_COOKIE['sort_regie'] : 'pos');
setcookie('sort_regie', $arg, time()+2419200);
		// display all athletes
		if ($arg=="nbr" && !$relay) {        
		$argument="a.Startnummer";
		$img_nbr="img/sort_act.gif";
	} else if ($arg=="pos") {
		$argument="ss.Position";
		$img_pos="img/sort_act.gif";
	} else if ($arg=="name") {
		$argument="at.Name, at.Vorname";
		$img_name="img/sort_act.gif";
	} else if ($arg=="club") {
        if ($svm){
            $argument="te.Name, a.Startnummer";
        }
        else {
            $argument="v.Name, a.Startnummer";
        }
		
		$img_club="img/sort_act.gif";
	} else if ($arg=="perf") {
		$argument="st.Bestleistung, ss.Position";
		$img_perf="img/sort_act.gif";
	} else if ($arg=="rang") {
		$argument="orderRang, ss.Position";
		$img_rang="img/sort_act.gif";
	} else if($relay == FALSE) {		// single event
		$argument="ss.Position";
		$img_pos="img/sort_act.gif";
	}
		
	    
	$sql = "SELECT
				rt.Name
				, rt.Typ
				, s.xSerie
				, s.Bezeichnung
				, s.Wind
				, s.Status
				, ss.xSerienstart
				, ss.Position
				, ss.Rang
				, a.Startnummer
				, at.Name
				, at.Vorname
				, at.Jahrgang
				, if('".$svm."', te.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))   
				, LPAD(s.Bezeichnung,5,'0') as heatid
				, w.Windmessung
				, st.Bestleistung
				, at.xAthlet
				, at.Land
				, t.rang
                , if (t.rang > 0,  t.rang, 999999) as orderRang
			FROM
            runde AS r
                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie) 
                LEFT JOIN start AS st ON (st.xStart = ss.xStart) 
                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung) 
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet) 
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein) 
                LEFT JOIN team AS te ON(a.xTeam = te.xTeam) 
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)  
				LEFT JOIN tempTech AS t ON (t.xSerienstart = ss.xSerienstart)
			    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
			WHERE r.xRunde = $round     
			ORDER BY s.xSerie, 
				" . $argument;
                
		$result = mysql_query($sql);
      
		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else
		{
			// initialize variables
			$h = 0;
			$i = 0;
			$r = 0;
            $current_athlete = false;
            $curr_class = '';

			$resTable = new GUI_TechResultTable($round, $layout, $status);
            $resTable->printHeatTitleRegie($cat, $disc);  

                       
			while($row = mysql_fetch_row($result))
			{
/*
 *  Heat headerline
 */
				if($h != $row[2])		// new heat
				{   $current_athlete = false; 
					$h = $row[2];				// keep heat ID

					if(is_null($row[0])) {		// only one round
						$title = "$strFinalround $row[3]";
					}
					else {		// more than one round
						$title = "$row[0]: $row[1]$row[3]";
					}

					$c = 0;
					
						$c++;		// increment colspan to include ranking
                        
					
					$resTable->printHeatTitle($row[2], $row[3], $title , $row[5], 'regie');
					$resTable->printAthleteHeader('regie', $round);
				}		// ET new heat

/*
 * Athlete data lines
 */
				
				$perfs = array();
				$fett = array();                       

				$sql = "SELECT
						    r.Leistung
						    , r.Info
                            , r.xSerienstart
				        FROM
						    resultat AS r
					    WHERE 
                            r.xSerienstart = $row[6]
					    ORDER BY
					        r.xResultat";
				$res = mysql_query($sql);   				
               
				if(mysql_errno() > 0) {		// DB error
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else
				{
					
				while($resrow = mysql_fetch_row($res))
					{           						
						$sql2 = "SELECT
						                max(r.Leistung)
						         FROM
						                resultat AS r 
						         WHERE 
                                        r.xSerienstart = $row[6]";
                        
				        $res2 = mysql_query($sql2);
				        $row2 = mysql_fetch_row($res2);      
					                							
					    if ($row2[0]==$resrow[0]) {  
						    $fett[]=1;
					    }
					    else {  
								$fett[]=0;
					    }
						        
					    $perf = AA_formatResultMeter($resrow[0]);
					    if($row[15] == 1) {		// with wind
							        $info = $resrow[1];
							        $perfs[] = "$perf ( $info )";
					    }
					    else {
							        $perfs[] = "$perf";
					    }
                        
				    
				}	// end loop every tech result acc. programm mode

					mysql_free_result($res);
				}                             
              
				//print_r($perfs);
                
                if ($row[19] == 0){
                    $row[19] = '';
                }
                
                $heatStart = AA_getCurrAthlete($row[2]);
               
               if ($heatStart > 0) {
                    if ($row[6] == $heatStart){
                         $curr_class = "active"; 
                    }
                }
                elseif ($keep_ss > 0){
                         if ($keep_ss == $row[6]){
                             $curr_class = "active";
                         }  
               }
               else {
                          if (empty($perfs) && !$current_athlete){
                                $current_athlete = true;
                                $curr_class = "active";
                          }
               }    
				 
				$resTable->printAthleteLine($row[7], $row[9], "$row[10] $row[11]"
					, '',$row[13], AA_formatResultMeter($row[16]) ,$perfs, $fett, $row[19], '', $row[17], $curr_class, 'regie');
                    
                 $curr_class = "";
			}
			$resTable->endTable();
			mysql_free_result($result);
		}		// ET DB error
	
       mysql_query("UNLOCK TABLES"); 
       
       $temp = mysql_query("DROP TABLE IF EXISTS `tempTech` ");  
	

}	// End Function AA_regie_Tech

}	// AA_SPEAKER_RESULTS_TECH_LIB_INCLUDED
?>
