<?php

/**********
 *
 *	rankinglist team sm events
 *	
 */

if (!defined('AA_RANKINGLIST_TEAMSM_LIB_INCLUDED'))
{
	define('AA_RANKINGLIST_TEAMSM_LIB_INCLUDED', 1);


function AA_rankinglist_TeamSM($category, $event, $formaction, $break, $cover, $cover_timing=false, $date = '%')
{
	
	require('./lib/cl_gui_page.lib.php');
	require('./lib/cl_print_page.lib.php');
    require('./lib/cl_print_page_pdf.lib.php');
	require('./lib/cl_export_page.lib.php');
	
	require('./lib/common.lib.php');
	require('./lib/results.lib.php');
    require('./lib/cl_performance.lib.php'); 
	
	if(AA_connectToDB() == FALSE)	{ // invalid DB connection
		return;		// abort
	}
	
	if(AA_checkMeetingID() == FALSE) {		// no meeting selected
		return;		// abort
	}
	
	global $rFrom, $rTo, $limitRank, $date;
	$rFrom = 0; $rTo = 0; // limits rank if limitRank set to true
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
		$list = new GUI_TeamSMRankingList($_COOKIE['meeting']);
		$list->printPageTitle("$strRankingLists " . $_COOKIE['meeting']);
	}
	// start a new HTML print page
	elseif($formaction == "print") {
		$list = new PRINT_TeamSMRankingList_pdf($_COOKIE['meeting']);
		if($cover == true) {		// print cover page 
			$list->printCover($GLOBALS['strResults']);
		}
	}
	// export ranking
	elseif($formaction == "exportpress"){
		$list = new EXPORT_TeamSMRankingListPress($_COOKIE['meeting'], 'txt');
	}elseif($formaction == "exportdiplom"){
		$list = new EXPORT_TeamSMRankingListDiplom($_COOKIE['meeting'], 'csv');
	}
	
	$selection = '';
	if(!empty($event)) {		// show specific event
		$selection = " w.xWettkampf = $event";
	}
	elseif(!empty($category)) {	// show disciplines per specific category
		$selection = " w.xMeeting = ".$_COOKIE['meeting_id']." AND w.xKategorie = $category";
	}
	else{				// show events over all categories
		$selection = " w.xMeeting = ".$_COOKIE['meeting_id']." ";
	}
	
	//
	// get each discipline for selection and process
	//
	                
    $sql = "
        SELECT
            w.xWettkampf
            , d.Typ
            , k.Name
            , d.Name
            , w.Windmessung
        FROM
            wettkampf AS w
            LEFT JOIN kategorie AS k ON ( k.xKategorie = w.xKategorie)
            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
        WHERE
            $selection       
            AND w.Typ = " . $cfgEventType[$strEventTypeTeamSM] ."
        ORDER BY
            k.Anzeige
            , d.Anzeige
    ";
                   
    $result = mysql_query($sql);   
	
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		
		$cat = "";
		
		while($row = mysql_fetch_array($result)){
			
			if($cat != $row[2] && !empty($cat)){
				
			}
			
			processDiscipline($row[0], $row[1], $row[2], $row[3], $row[4], $list);
		}
		
	}
	
	$list->endPage();
	
} // EF function


function processDiscipline($event, $disctype, $catname, $discname, $windmeas, $list){
	
	global $rFrom, $rTo, $limitRank, $date;
	require('config.inc.php');
	
	$teams = array();	// team array
	$countRes = 3;		// results per team counting
	
	$order_perf = "";
    $order_perf_sort = "";  
	$valid_result = "";
	if(($disctype == $cfgDisciplineType[$strDiscTypeJumpNoWind])
		|| ($disctype == $cfgDisciplineType[$strDiscTypeThrow]))
	{
		$order_perf = "DESC";
        $order_perf_sort = "DESC"; 
	}
	else if($disctype == $cfgDisciplineType[$strDiscTypeJump])
	{
		if ($windmeas == 1) {			// with wind
			$order_perf = "DESC, r.Info ASC";
            $order_perf_sort = "DESC"; 
		}
		else {					// without wind
			$order_perf = "DESC";
            $order_perf_sort = "DESC"; 
		}
	}
	else if($disctype == $cfgDisciplineType[$strDiscTypeHigh])
	{
		$order_perf = "DESC";
        $order_perf_sort = "DESC"; 
		$valid_result =	" AND (r.Info LIKE '%O%' OR r.Leistung < 0)";
	}
	else
	{
		$order_perf = "ASC";
        $order_perf_sort = "ASC"; 
	}
	
	$sql_leistung = ($order_perf=='ASC') ? "r.Leistung" : "IF(r.Leistung<0, (If(r.Leistung = -99, -9, r.Leistung) * -1), r.Leistung)";
	                           
    $sql = "
        SELECT
            ts.xTeamsm
            , ts.Name
            , v.Name
            , at.Name
            , at.Vorname
            , a.Startnummer
            , ".$sql_leistung." AS leistung_neu
            , at.xAthlet
            , r.Leistung 
            , ss.Rang
        FROM
            teamsm AS ts
            LEFT JOIN verein AS v ON (v.xVerein = ts.xVerein)
            LEFT JOIN teamsmathlet AS tsa ON (tsa.xTeamsm = ts.xTeamsm)
            LEFT JOIN anmeldung AS a ON (a.xAnmeldung = tsa.xAnmeldung)
            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
            LEFT jOIN start AS st ON (st.xAnmeldung = tsa.xAnmeldung)
            LEFT JOIN serienstart AS ss ON (ss.xStart = st.xStart )
            LEFT JOIN resultat AS r ON ( r.xSerienstart = ss.xSerienstart)
            LEFT JOIN serie AS se ON (ss.xSerie = se.xSerie)
            LEFT JOIN runde as ru ON (se.xRunde = ru.xRunde)  
        WHERE
            ts.xWettkampf = $event       
            AND st.xWettkampf = $event  
            AND ru.Datum LIKE '".$date."'  
            $valid_result    
        ORDER BY
            ts.xTeamsm
            , leistung_neu $order_perf
    ";
    
    $res = mysql_query($sql);     
	
	if(mysql_errno() > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		
		$team = 0;		// current team
		$c = 0;			// count results
		$athletes = array();
        $teams_notValid = array();    // team array  
        
		
		while($row = mysql_fetch_array($res)){
			
			$row_res[6] = ($row_res[6]==1 || $row_res[6]==2 || $row_res[6]==3 || $row_res[6]==4) ? ($row_res[6] * -1) : (($row_res[6]==9) ? -99 : $row_res[6]);
			
			if(isset($athletes[$row[7]])){
				continue;
			}else{
				$athletes[$row[7]] = 1;
			}
			
			if($team != $row[0]){
				
				if($team > 0){
					$countAthl = count($teams[$team]['athletes']);
                    if ($countAthl < $countRes){
                        $teams[$team]['perf'] = 0; 
                        $teams[$team]['perfTot'] = 0; 
                        $teams[$team]['rankTot'] = 0; 
                        $teams[$team]['perfBest'] = 0; 
                        $teams[$team]['rankBest'] = 0; 
                        $teams[$team]['perfNotValid'] = 0;  
                        $teams_notValid[$team]['club'] = $teams[$team]['club'];
                        $teams_notValid[$team]['name'] = $teams[$team]['name'];  
                        $teams_notValid[$team]['perf'] = 0;   
                        $teams_notValid[$team]['athletes'] = $teams[$team]['athletes'];
                    }
                    else {   
                        
                        $notValidPerf = false; 
                        $countNotValid = 0;                           
                        foreach ($teams[$team]['athletes'] as $key => $val) {   
                                if ($pos = strpos($val, "-1") || $pos = strpos($val, "-2") || $pos = strpos($val, "-3") || $pos = strpos($val, "-4")){
                                    $notValidPerf = true;
                                    $countNotValid ++;
                                }
                        } 
                        if (!$notValidPerf){ 
                            $teams[$team]['perf'] /= $countRes; 
                       } 
                       else {
                             
                             $countValid = $countRes - $countNotValid;                             
                             if (isset($teams[$team]['athletes'][3])){                                 
                                 if ($pos = strpos($teams[$team]['athletes'][0], "-1") || $pos = strpos($teams[$team]['athletes'][0], "-2") || $pos = strpos($teams[$team]['athletes'][0], "-3") || $pos = strpos($teams[$team]['athletes'][0], "-4") ){
                                    $tmp_at = $teams[$team]['athletes'][0];
                                    $teams[$team]['athletes'][0] = substr($teams[$team]['athletes'][3],1,-1);
                                    list ($at,$atPerf) = split('[,]',$teams[$team]['athletes'][0]);                                    
                                    $teams[$team]['athletes'][3] = "[" . $tmp_at . "]";
                                    
                                    
                                 }
                                 elseif ($pos = strpos($teams[$team]['athletes'][1], "-1") || $pos = strpos($teams[$team]['athletes'][1], "-2") || $pos = strpos($teams[$team]['athletes'][1], "-3") || $pos = strpos($teams[$team]['athletes'][1], "-4") ){                                                                     
                                        $tmp_at = $teams[$team]['athletes'][1];
                                        $teams[$team]['athletes'][1] = substr($teams[$team]['athletes'][3],1,-1); 
                                        list ($at,$atPerf) = split('[,]',$teams[$team]['athletes'][1]);
                                        $teams[$team]['athletes'][3] = "[" . $tmp_at . "]"; 
                                       
                                 } 
                                 elseif ($pos = strpos($teams[$team]['athletes'][2], "-1") || $pos = strpos($teams[$team]['athletes'][2], "-2") || $pos = strpos($teams[$team]['athletes'][2], "-3") || $pos = strpos($teams[$team]['athletes'][2], "-4") ){                                                                     
                                         $tmp_at = $teams[$team]['athletes'][2];
                                         $teams[$team]['athletes'][2] = substr($teams[$team]['athletes'][3],1,-1);  
                                         list ($at,$atPerf) = split('[,]',$teams[$team]['athletes'][2]);                                    
                                         $teams[$team]['athletes'][3] = "[" . $tmp_at . "]"; 
                                      
                                 } 
                                 if(($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeNone']])
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']]) 
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeDistance']]) 
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']])  
                                        )
                                    {    
                                         
                                         $perf = new PerformanceTime($atPerf, $secFlag);
                                         $atPerf = $perf->getPerformance();
                                    }
                                    else if(($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJump']]) 
                                             || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJumpNoWind']])
                                             || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeThrow']])
                                             || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeHigh']]) )   
                                   
                                    {
                                        $perf = new PerformanceAttempt($performance);
                                        $atPerf = $perf->getPerformance();
                                    } 
                                    
                                 
                                 if ($countNotValid == 2){
                                     $teams[$team]['perf'] += $atPerf;
                                     $teams[$team]['perf'] = $teams[$team]['perf']/$countNotValid; 
                                 }
                                 elseif ($countNotValid == 1){                                           
                                         $teams[$team]['perf'] += $atPerf;
                                         $teams[$team]['perf'] =   $teams[$team]['perf']/$countRes; 
                                         $notValidPerf = false;
                                 }   
                                  elseif ($countNotValid == 3){
                                          $teams[$team]['perf'] += $atPerf; 
                                  }
                             }   
                             
                       }
                        if ($order_perf == 'ASC') {   
                            if ($notValidPerf){
                              $teams[$team]['perfNotValid'] = $teams[$team]['perf'];
                              $teams[$team]['perf'] = 99999999;                               
                            }
                        } 
                        else {
                             if ($notValidPerf){
                                  $teams[$team]['perfNotValid'] = $teams[$team]['perf']; 
                             }
                        }                         
                    }   
				}
				
				$team = $row[0];
				$teams[$team]['club'] = $row[2];
				$teams[$team]['name'] = $row[1];                  
				$c = 0;
			}
			
			$perf = 0;
			$perf_print = 0;
            $rank = 0;
            $asc = true;
			if(($disctype == $cfgDisciplineType[$strDiscTypeJump])
				|| ($disctype == $cfgDisciplineType[$strDiscTypeJumpNoWind])
				|| ($disctype == $cfgDisciplineType[$strDiscTypeThrow])
				|| ($disctype == $cfgDisciplineType[$strDiscTypeHigh])) {
				$perf = $row[6];
                $asc = false;
                if ($row[8] >= 0){
				    $perf_print = AA_formatResultMeter($row[6]);
                }
                else {
                      $perf_print = $perf * -1;
                }
			}
			else {
				$perf = (ceil($row[6]/10))*10; // round up 1000
                $asc = true;
				if(($disctype == $cfgDisciplineType[$strDiscTypeTrack])
				|| ($disctype == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
					$perf_print = AA_formatResultTime($row[6], true, true);
				}else{
					$perf_print = AA_formatResultTime($row[6], true);
				}
			}   
            $rank = $row[9];			
			if($c < $countRes){
				
				$teams[$team]['perf'] += $perf;
                $teams[$team]['perfTot'] += $perf;
                $teams[$team]['rankTot'] += $rank;
                if ($asc){
                    if ($perf < $teams[$team]['perfBest'] || $teams[$team]['perfBest'] == 0){
                         $teams[$team]['perfBest'] = $perf;
                         $teams[$team]['rankBest'] = $rank;
                    }
                   
                }
                else {
                    if ($perf > $teams[$team]['perfBest']){
                         $teams[$team]['perfBest'] = $perf;
                         $teams[$team]['rankBest'] = $rank;
                     }
                }
               
				$teams[$team]['athletes'][] = "$row[3] $row[4], $perf_print";
				
			}else{
				
				$teams[$team]['athletes'][] = "[$row[3] $row[4], $perf_print]";
				
			}
			
			$c++;
		}
		
		if($team > 0){ // calc last team
			 $countAthl = count($teams[$team]['athletes']);
			 if ($countAthl < $countRes){
                       $teams[$team]['perf'] = 0;   
                       $teams[$team]['perfNotValid'] = 0;  
                       $teams_notValid[$team]['club'] = $teams[$team]['club'];
                       $teams_notValid[$team]['name'] = $teams[$team]['name']; 
                       $teams_notValid[$team]['perf'] = 0;  
                       $teams_notValid[$team]['athletes'] = $teams[$team]['athletes'];
                    }
                    else {
                          
                          $notValidPerf = false;       
                          $countNotValid = 0;                                                
                          foreach ($teams[$team]['athletes'] as $key => $val) {   
                          if ($pos = strpos($val, "-1") || $pos = strpos($val, "-2") || $pos = strpos($val, "-3") || $pos = strpos($val, "-4")){
                                    $notValidPerf = true;
                                    $countNotValid ++;  
                                }
                          } 
                          if (!$notValidPerf){ 
                            $teams[$team]['perf'] /= $countRes; 
                          } 
                          else {
                                 $countValid = $countRes - $countNotValid;
                                 if (isset($teams[$team]['athletes'][3])){                                 
                                 if ($pos = strpos($teams[$team]['athletes'][0], "-1") || $pos = strpos($teams[$team]['athletes'][0], "-2") || $pos = strpos($teams[$team]['athletes'][0], "-3") || $pos = strpos($teams[$team]['athletes'][0], "-4") ){
                                    $tmp_at = $teams[$team]['athletes'][0];
                                    $teams[$team]['athletes'][0] = substr($teams[$team]['athletes'][3],1,-1);
                                    list ($at,$atPerf) = split('[,]',$teams[$team]['athletes'][0]);                                    
                                    $teams[$team]['athletes'][3] = "[" . $tmp_at . "]";
                                    
                                    
                                 }
                                 elseif ($pos = strpos($teams[$team]['athletes'][1], "-1") || $pos = strpos($teams[$team]['athletes'][1], "-2") || $pos = strpos($teams[$team]['athletes'][1], "-3") || $pos = strpos($teams[$team]['athletes'][1], "-4") ){                                                                     
                                        $tmp_at = $teams[$team]['athletes'][1];
                                        $teams[$team]['athletes'][1] = substr($teams[$team]['athletes'][3],1,-1); 
                                        list ($at,$atPerf) = split('[,]',$teams[$team]['athletes'][1]);
                                        $teams[$team]['athletes'][3] = "[" . $tmp_at . "]"; 
                                       
                                 } 
                                 elseif ($pos = strpos($teams[$team]['athletes'][2], "-1") || $pos = strpos($teams[$team]['athletes'][2], "-2") || $pos = strpos($teams[$team]['athletes'][2], "-3") || $pos = strpos($teams[$team]['athletes'][2], "-4") ){                                                                     
                                         $tmp_at = $teams[$team]['athletes'][2];
                                         $teams[$team]['athletes'][2] = substr($teams[$team]['athletes'][3],1,-1);  
                                         list ($at,$atPerf) = split('[,]',$teams[$team]['athletes'][2]);                                    
                                         $teams[$team]['athletes'][3] = "[" . $tmp_at . "]"; 
                                      
                                 } 
                                 if(($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeNone']])
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']]) 
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeDistance']]) 
                                        || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']])  
                                        )
                                    {    
                                         
                                         $perf = new PerformanceTime($atPerf, $secFlag);
                                         $atPerf = $perf->getPerformance();
                                    }
                                    else if(($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJump']]) 
                                             || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJumpNoWind']])
                                             || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeThrow']])
                                             || ($disctype == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeHigh']]) )   
                                   
                                    {
                                        $perf = new PerformanceAttempt($performance);
                                        $atPerf = $perf->getPerformance();
                                    } 
                                    
                                 
                                 if ($countNotValid == 2){
                                     $teams[$team]['perf'] += $atPerf;
                                     $teams[$team]['perf'] = $teams[$team]['perf']/$countNotValid; 
                                 }
                                 elseif ($countNotValid == 1){                                           
                                         $teams[$team]['perf'] += $atPerf;
                                         $teams[$team]['perf'] =   $teams[$team]['perf']/$countRes; 
                                         $notValidPerf = false;
                                 }   
                                  elseif ($countNotValid == 3){
                                          $teams[$team]['perf'] += $atPerf; 
                                  }
                             }   
                          }
                         
                          if ($order_perf == 'ASC') {   
                              if ($notValidPerf){
                                  $teams[$team]['perfNotValid'] = $teams[$team]['perf'];
                                  $teams[$team]['perf'] = 99999999;                                   
                              }                               
                          }
                          else {
                             if ($notValidPerf){
                                  $teams[$team]['perfNotValid'] = $teams[$team]['perf']; 
                             }
                          }                                                   
                    }
			
		}
		$teams_valid = array();
		foreach ($teams as $k => $arr_team){
            if (!isset($teams_notValid[$k]['name'])){                  
                 $teams_valid[$k]['name'] =  $teams[$k]['name'];
                 $teams_valid[$k]['club'] =  $teams[$k]['club'];  
                 $teams_valid[$k]['perf'] =  $teams[$k]['perf'];  
                 $teams_valid[$k]['perfTot'] =  $teams[$k]['perfTot'];    
                 $teams_valid[$k]['rankTot'] =  $teams[$k]['rankTot']; 
                 $teams_valid[$k]['perfBest'] =  $teams[$k]['perfBest'];
                 $teams_valid[$k]['rankBest'] =  $teams[$k]['rankBest'];     
                 $teams_valid[$k]['perfNotValid'] =  $teams[$k]['perfNotValid'];  
                 $teams_valid[$k]['athletes'] =  $teams[$k]['athletes']; 
            } 
        } 
       
        $teams = $teams_valid;
        
		//
		// print team ranking
		//
		if(count($teams)>0){
			
			$list->printSubTitle($catname, $discname, "");
			$list->startList();
			$list->printHeaderLine();
			
			usort($teams, "cmp_$order_perf_sort");	// sort by performance
            $teams = array_merge($teams, $teams_notValid); 
            
			$rank = 1;			// initialize rank
			$r = 0;				// start value for ranking
			$p = 0;
			
			foreach($teams as $team){
               				
				$r++;
				
				if($limitRank && ($r < $rFrom || $r > $rTo)){ // limit ranks if set (export)
					continue;
				}
                
                if ($team['perf'] > 0){
                   $rank = $r;  
                }      
                else {
                    $rank = '';
                }
				
                if ($team['perf']  == 99999999){
                     $team['perf']  =   $team['perfNotValid']; 
                }
				$perf = 0;
				if(($disctype == $cfgDisciplineType[$strDiscTypeJump])
					|| ($disctype == $cfgDisciplineType[$strDiscTypeJumpNoWind])
					|| ($disctype == $cfgDisciplineType[$strDiscTypeThrow])
					|| ($disctype == $cfgDisciplineType[$strDiscTypeHigh])) {
					$perf = AA_formatResultMeter($team['perf']);
				}
				else {
					if(($disctype == $cfgDisciplineType[$strDiscTypeTrack])
					|| ($disctype == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
						$perf = AA_formatResultTime($team['perf'], true, true);
					}else{
						$perf = AA_formatResultTime($team['perf'], true);
					}
				}
               
                if ($perf == 0 || !empty($team['perfNotValid'])){
                    $rank = '';   
                }
                              
				$list->printLine($rank, $team['name'], $team['club'], $perf);
				
				// print each athlete with result for team
				$tmp = "";   
                                     
				foreach($team['athletes'] as $athlete){
                        $end = "";
					    list ($name, $perfNotValid) = split(',',$athlete);
                        if (strpos($perfNotValid, "]")){
                            $perfNotValid = substr($perfNotValid, 0, -1);
                            $end = "]";
                        }
                               
                        if ($perfNotValid == $cfgInvalidResult['DNS']['code']) {
                            $perfNotValid = $cfgInvalidResult['DNS']['short'];
                            $athlete = $name . ', ' . $perfNotValid . $end;
                        }    
                        elseif ($perfNotValid == $cfgInvalidResult['DNF']['code']) {
                            $perfNotValid = $cfgInvalidResult['DNF']['short'];
                             $athlete = $name . ', ' . $perfNotValid . $end; 
                        }   
                        elseif ($perfNotValid == $cfgInvalidResult['DSQ']['code']) {
                            $perfNotValid = $cfgInvalidResult['DSQ']['short'];
                             $athlete = $name . ', ' . $perfNotValid . $end; 
                        }                
                        elseif ($perfNotValid == $cfgInvalidResult['NRS']['code']) {
                            $perfNotValid = $cfgInvalidResult['NRS']['short'];
                             $athlete = $name . ', ' . $perfNotValid .  $end;
                        } 
                        
					    //$list->printInfo($athlete);
					    $tmp .= $athlete." / ";
					
				}
				$list->printInfo(substr($tmp,0, -2));
				
				$p = $team['perf'];	// keep current performance
                $keep_team = $team;    // keep current performance
				
			}
			$list->endList();
			
		}
	}
	
}


//
// compare function to sort teams
// 
// rule by same team sm points 
// 1. the best total perf (not average)  --> perfTot
// 2. the best of total ranking Points (single results of athletes) (minimum) --> rankTot
// 3. the best ranking point of athlete in team (minimum) --> rankBest

function cmp_DESC ($a, $b) {
	if ($a["perf"]== $b["perf"]) {
        if ($a["perfTot"]== $b["perfTot"]) {
             if ($a["rankTot"]== $b["rankTot"]) {
                  if ($a["rankBest"]== $b["rankBest"]) return 0;                  
                  return ($a["rankBest"] > $b["rankBest"]) ? 1 : -1;
             } 
              return ($a["rankTot"] > $b["rankTot"]) ? 1 : -1;
        }
        return ($a["perfTot"] > $b["perfTot"]) ? -1 : 1;
    }
	return ($a["perf"] > $b["perf"]) ? -1 : 1;
}

function cmp_ASC ($a, $b) {
   if ($a["perf"]== $b["perf"]) {
        if ($a["perfTot"]== $b["perfTot"]) {
             if ($a["rankTot"]== $b["rankTot"]) { 
                  if ($a["rankBest"]== $b["rankBest"]) return 0;                  
                  return ($a["rankBest"] > $b["rankBest"]) ? 1 : -1;
             } 
              return ($a["rankTot"] > $b["rankTot"]) ? 1 : -1;
        }
        return ($a["perfTot"] > $b["perfTot"]) ? 1 : -1;
    }
    return ($a["perf"] > $b["perf"]) ? 1 : -1;
}






} // EF defined



?>
