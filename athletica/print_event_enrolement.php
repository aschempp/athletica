<?php

/**********
 *
 *	print_event_enrolement.php
 *	-------------------------
 *	
 */
       
include('./config.inc.php');
require('./lib/common.lib.php');
require('./lib/cl_print_entrypage.lib.php');
require('./lib/cl_print_entrypage_pdf.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
	}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

$cCat = 0; // vars for combined event
$cCode = 0;

$argument = "w.xMeeting = " . $_COOKIE['meeting_id'];   

if ((($_GET['catFrom'] > 0)  ||  ($_GET['discFrom'] > 0 || $_GET['mDate'] != '')) ) {     
    if ($_GET['catFrom'] > 0 & $_GET['discFrom'] > 0){
        $catFrom=$_GET['catFrom']; 
        $catTo=$_GET['catTo']; 
           $argument = "w.xKategorie >= " . $_GET['catFrom'] . " AND w.xKategorie <= " . $_GET['catTo'] 
           . " AND w.xDisziplin >= " . $_GET['discFrom'] . " AND w.xDisziplin <= " . $_GET['discTo'] 
           . " AND w.xMeeting = " . $_COOKIE['meeting_id']; 
    }  
    else
    if ($_GET['catFrom'] > 0){  
        $catFrom=$_GET['catFrom']; 
        $catTo=$_GET['catTo']; 
           $argument = "w.xKategorie >= " . $_GET['catFrom'] . " AND w.xKategorie <= " . $_GET['catTo'] 
         . " AND w.xMeeting = " . $_COOKIE['meeting_id']; 
    }
    else
        {  if ($_GET['discFrom'] > 0){ 
          		$discFrom=$_GET['discFrom']; 
          		$discTo=$_GET['discTo']; 
          		$argument = " w.xDisziplin >= " . $_GET['discFrom'] . " AND w.xDisziplin <= " . $_GET['discTo']
        		. " AND w.xMeeting = " . $_COOKIE['meeting_id']; 
		   }
        }
    if  (!empty($_GET['mDate'])) { 
         $mDate=$_GET['mDate'];
         $argument .= " AND r.Datum = '" . $_GET['mDate'] ."'";
        
    }
}
 
else { 
    if(!empty($_GET['event'])) {   
        $sqlEvents=AA_getMergedEventsFromEvent($_GET['event']);
    
        if ($sqlEvents=='' )
            $argument .= " AND w.xWettkampf = " . $_GET['event']." "; 
        else
            $argument .= " AND w.xWettkampf IN ".$sqlEvents." ";   
    } 
    else if(!empty($_GET['category'])) {
	    $argument .= " AND w.xKategorie = " . $_GET['category']
			
				. " AND d.Appellzeit > 0";
    }
    
    elseif(!empty($_GET['comb'])){
	    list($cCat, $cCode) = explode("_", $_GET['comb']);
	    $argument .= " AND w.xKategorie = $cCat
			AND w.Mehrkampfcode = $cCode";
		   
    }
}     
    
$pagebreak = "no";
if(isset($_GET['pagebreak'])){
	$pagebreak = $_GET['pagebreak'];
}

$teamsm = false;
if (isset($_GET['teamsm'])){
    $teamsm = $_GET['teamsm'];
} 

$mk_group = '';
$tm_group = ''; 
if(!empty($_GET['group'])) {
    if ($teamsm) {
         $tm_group = $_GET['group']; 
    }
    else {
         $mk_group = $_GET['group']; 
    } 
}  

$sort = "at.Name, at.Vorname";


// start a new HTML page for printing
$doc = new PRINT_EnrolementPage_pdf($_COOKIE['meeting'],False,False, true, true);
       
// get event title data    
  $sql = "SELECT 
                DISTINCT d.Name
                , k.Name
                , DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
                , TIME_FORMAT(r.Appellzeit, '$cfgDBtimeFormat')
                , w.xWettkampf
                , r.xRunde
                , r.Status
                , w.xKategorie
                , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                , TIME_FORMAT(r.Stellzeit, '$cfgDBtimeFormat')
                , w.Mehrkampfcode
                , dm.Name
                , w.Info 
                , d.Typ     
          FROM 
                disziplin_" . $_COOKIE['language'] . " AS d
                LEFT JOIN wettkampf AS w ON (d.xDisziplin = w.xDisziplin ) 
                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie ) 
                LEFT JOIN runde AS r On (r.xWettkampf = w.xWettkampf)
                LEFT JOIN disziplin_" . $_COOKIE['language'] ." as dm ON w.Mehrkampfcode = dm.Code
                LEFT JOIN start AS s ON(s.xWettkampf=w.xWettkampf)  
                LEFT JOIN anmeldung as a ON (s.xAnmeldung = a.xAnmeldung)   
          WHERE " . $argument ."   
            ORDER BY w.xKategorie, w.Mehrkampfcode, r.xWettkampf, r.Datum, r.Startzeit";     
 
$result = mysql_query($sql);

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{
	$i = 0;
	$event = 0;
	$xCat = 0;
	$xComb = 0;
    $noDisc = false;
	
	while($row = mysql_fetch_row($result))   
	{            
        $discHeader=($row[0]!='') ? $row[0] : $row[11];   
		if($row[4] != $event)	// only first round per event      
		{
			
			if($combined && $xComb == $row[10] && $xCat == $row[7]){   
			  continue;
		  	} 		   
			// change round status only if nothing done yet
          
			if(isset($row[6]) && $row[6] == $cfgRoundStatus['open']) {	
				AA_utils_changeRoundStatus($row[5],
					$cfgRoundStatus['enrolement_pending']);
				if(!empty($GLOBALS['AA_ERROR'])) {
					AA_printErrorMsg($GLOBALS['AA_ERROR']);
				}
			}
			
			// handle page break
		  if($i > 0 && $pagebreak == "discipline" && !$noDisc) {	// not first event               
			  $doc->insertPageBreak();
		  }
		  if($i > 0 && $xCat != $row[7] && $pagebreak == "category"){
			  $doc->insertPageBreak();
		  }
		  $xCat = $row[7];
		  $i++;

		  $event = $row[4];
		  $relay = AA_checkRelay($event);
		  $combined = AA_checkCombined($event);
		  $svm = AA_checkSVM($event);
         
		  if($svm){
			  $sortAddition = "t.Name, ";
		  }  
          
		  $xComb = $row[10];
		  
          if (!empty($row[12])) {
             $row[12] = " ($row[12])"; 
          }
          
		  if($combined){
			  $doc->event = $row[11] . $row[12];
			   if ($_GET['event'] > 0){             // only one disciplin of combined event
			  		$doc->comb_disc = $row[0];  
			  }
		  }else{
			  $doc->event = $row[0] .$row[12];
		  }
		  $doc->cat = $row[1];
		  $et = "";
		  $ot = "";
		  if($row[3] != "00:00"){ // add enrolement time
			  $et = ", " . $row[3];
		  }
		  $ot .= " ($strStarttime $row[8]"; // add starttime
		  if($row[9] != "00:00"){ // add manipulation time
			  $ot .= ", $strManipulationTime $row[9]";
		  }
		  $ot .= ")";
		  $doc->time = $strEnrolement. ": " . $row[2] . $et;
		  $doc->timeinfo = $ot;
                   
          if ($event > 0 && ($catFrom == '' ||  $discFrom == '') ) {  
            $sqlEvents = " WHERE s.xWettkampf = ".$event." ";
          }  
            
          if ($catFrom > 0 && $discFrom > 0){ 
                $getSortDisc = AA_getSortDisc($discFrom,$discTo);         // sort display from category
                $getSortCat = AA_getSortCat($catFrom,$catTo);             // sort display from dicipline 
                if ($getSortCat[0] && $getSortDisc[0]){ 
                    if ($catTo > 0)     
                        $sqlEvents = " WHERE k.Anzeige >= ".$getSortCat[$catFrom]." AND k.Anzeige <= ".$getSortCat[$catTo]." ";
                    else
                        $sqlEvents = " WHERE k.Anzeige = ".$$getSortCat[$catFrom]." "; 
                    if ($discTo > 0)                              
                        $sqlEvents .= " AND d.Anzeige >= ".$getSortDisc[$discFrom]." AND d.Anzeige <= ".$getSortDisc[$discTo]." "; 
                    else
                        $sqlEvents .= " AND d.Anzeige = ".$getSortDisc[$discFrom]." ";  
                    $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id']; 
                } 
                else    
                    $sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id']; 
                    
                $sqlGroup = " GROUP BY at.Name, at.Vorname, d.xDisziplin ";  
         }
         elseif ($catFrom > 0){ 
                $getSortCat = AA_getSortCat($catFrom,$catTo);             // sort display from category  
                if ($getSortCat[0]) {  
                    if ($catTo > 0)     
                        $sqlEvents = " WHERE k.Anzeige >= ".$getSortCat[$catFrom]." AND k.Anzeige <= ".$getSortCat[$catTo]." ";
                    else
                        $sqlEvents = " WHERE k.Anzeige = ".$getSortCat[$catFrom]." ";  
                    $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id']; 
                }
                else
                    $sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id'];  
                $sqlGroup = " GROUP BY at.Name, at.Vorname, d.xDisziplin ";  
         }
         elseif ($discFrom > 0) {
                $getSortDisc = AA_getSortDisc($discFrom,$discTo);          // sort display from dicipline
                if ($getSortDisc[0]){   
                    if ($discTo > 0)                              
                        $sqlEvents = " WHERE  d.Anzeige >= ".$getSortDisc[$discFrom]." AND d.Anzeige <= ".$getSortDisc[$discTo]." "; 
                    else
                        $sqlEvents = " WHERE d.Anzeige = ".$getSortDisc[$discFrom]." "; 
                    $sqlEvents.=" AND w.xMeeting = ". $_COOKIE['meeting_id']; 
                }
                else
                    $sqlEvents.=" w.xMeeting = ". $_COOKIE['meeting_id'];  
                    
                $sqlGroup = " GROUP BY at.Name, at.Vorname, d.xDisziplin ";    
         }    
         
         if ($mDate > 0){
                if ($sqlEvents!='')
                    $sqlEvents.=" AND r.Datum = '" . $mDate . "' ";
                else
                    $sqlEvents.=" r.Datum = '" . $mDate . "' ";    
         }  
               
         $order = "";
         if ($_GET['sort'] == 'bestperf'){    
         
             if(($row[13] == $cfgDisciplineType[$strDiscTypeTrack])
                || ($row[13] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                || ($row[13] == $cfgDisciplineType[$strDiscTypeRelay])
                || ($row[13] == $cfgDisciplineType[$strDiscTypeDistance])) {
                    $order = "ASC";
                }
             else {
                   $order = "DESC"; 
             }
         }  
         
		  // read event entries
		  if($relay == FALSE) {		// single event
          
              if(isset($_GET['sort'])){
                if  ($_GET['sort'] == 'nbr') {
                    $sort = "a.Startnummer, at.Name, at.Vorname"; 
                }
                elseif ($_GET['sort'] == 'club') {  
                    $sort = "v.Sortierwert, at.Name, at.Vorname"; 
                }  
                elseif ($_GET['sort'] == 'bestperf') {  
                    $sort = "Bestleistung $order, at.Name, at.Vorname"; 
                }  
                else {  
                    $sort = "at.Name, at.Vorname"; 
                }  
              }
              $sqlGroup = "";
              if  (!empty($mk_group)) {
                              $sqlGroup = " AND a.Gruppe =  " .$mk_group; 
              }
              elseif (!empty($tm_group)){
                                $sqlGroup =" AND s.Gruppe =  " .$tm_group; 
              }                
			  if($combined){
			  	  $sqlEvt = '';
			  	  if ($_GET['event'] > 0){   
			  	  		$sqlEvt = " w.xWettkampf = ". $event ." AND ";
				  }
                  if ($_GET['sort'] == 'bestperf') {  
                        $sort = "a.BestleistungMK DESC, at.Name, at.Vorname"; 
                  }
				  
                  if  (!empty($sqlGroup)) {   
                        $query = "SELECT
                                     
                                     a.Startnummer
                                    , at.Name
                                    , at.Vorname
                                    , at.Jahrgang                          
                                    , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))    
                                    , a.BestleistungMK
                                    , a.Gruppe
                                    , IF(at.xRegion = 0, at.Land, re.Anzeige)
                                    
                                FROM
                                    anmeldung AS a
                                LEFT JOIN
                                    athlet AS at USING(xAthlet)
                                LEFT JOIN 
                                    start AS s ON(s.xAnmeldung = a.xAnmeldung)
                                LEFT JOIN 
                                    verein AS v ON(at.xVerein = v.xVerein)
                                LEFT JOIN
                                    team AS t ON(a.xTeam = t.xTeam)  
                                LEFT JOIN region as re ON at.xRegion = re.xRegion    
                                LEFT JOIN meeting as m ON (m.xMeeting = " . $_COOKIE['meeting_id'] .")
                                WHERE " .$sqlGroup ."
                                GROUP BY a.xAnmeldung                          
                                ORDER BY
                                     a.xAnmeldung;";
                                    
                         
                  }
                  else {
                      $query = "SELECT 
                                        a.Startnummer
                                      , at.Name
                                      , at.Vorname
                                      , at.Jahrgang
                                      , IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo) 
                                      , a.BestleistungMK
                                      , d.Typ
                                      , IF(at.xRegion = 0, at.Land, re.Anzeige)
                                      , w.xDisziplin  
                                      , w.xWettkampf  
                                      , s.Bestleistung  
                               FROM 
                                        anmeldung AS a
                                        LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet )
                                        LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung )  
                                        LEFT JOIN wettkampf AS w ON ( s.xWettkampf = w.xWettkampf)  
                                        LEFT JOIN verein AS v ON (at.xVerein = v.xVerein)  
                                        LEFT JOIN region as re ON at.xRegion = re.xRegion
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d ON w.xDisziplin = d.xDisziplin
                               WHERE "     
                                        .  $sqlEvt ."  
                                        w.xKategorie = " . $xCat ."
                                        AND w.Mehrkampfcode = " . $xComb  ."
                                        AND w.xMeeting = ". $_COOKIE['meeting_id'] ."  
                               GROUP BY a.xAnmeldung
                               ORDER BY $sort";      
                      
                  }     
			  }else{
				       if ($teamsm) {
                         $query = "SELECT
                                 a.Startnummer
                                , at.Name
                                , at.Vorname
                                , at.Jahrgang                          
                                , t.Name   
                                , if ((s.Bestleistung = 0 AND (d.Typ < $cfgDisciplineType[$strDiscTypeJump] OR d.Typ = $cfgDisciplineType[$strDiscTypeDistance])), 9999999, s.Bestleistung)  as Bestleistung
                                 , d.Typ
                                 , IF(at.xRegion = 0, at.Land, re.Anzeige)
                                 , d.Name 
                            FROM
                                anmeldung AS a
                            LEFT JOIN
                                athlet AS at USING(xAthlet)
                            LEFT JOIN 
                                start AS s ON(s.xAnmeldung = a.xAnmeldung)    
                             LEFT JOIN wettkampf as w ON s.xWettkampf = w.xWettkampf
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d ON w.xDisziplin = d.xDisziplin                     
                            LEFT JOIN 
                                verein AS v ON(at.xVerein = v.xVerein)     
                            LEFT JOIN region as re ON at.xRegion = re.xRegion                   
                           INNER JOIN
                                teamsmathlet AS tat ON(a.xAnmeldung = tat.xAnmeldung)    
                            LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                            WHERE s.Gruppe = '" .$tm_group ."'
                                  AND s.xWettkampf = " . $event  ."
                            GROUP BY a.xAnmeldung                          
                            ORDER BY
                                ".$sort;
                         
                       }
                       else {
                           
                       
                          $query = "SELECT 
                                        DISTINCT a.Startnummer
                                        , at.Name
                                        , at.Vorname
                                        , at.Jahrgang
                                        , if('$svm', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)) 
                                        , if ((s.Bestleistung = 0 AND (d.Typ < $cfgDisciplineType[$strDiscTypeJump] OR d.Typ = $cfgDisciplineType[$strDiscTypeDistance])), 9999999, s.Bestleistung)  as Bestleistung
                                        , d.Typ
                                        , IF(at.xRegion = 0, at.Land, re.Anzeige)
                                        , d.Name 
                                    FROM 
                                        anmeldung AS a
                                        LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
                                        LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung)
                                        LEFT JOIN verein AS v ON (at.xVerein = v.xVerein)
                                        LEFT JOIN region as re ON at.xRegion = re.xRegion
                                        LEFT JOIN wettkampf as w ON s.xWettkampf = w.xWettkampf
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." as d ON w.xDisziplin = d.xDisziplin
                                        LEFT JOIN kategorie AS k ON(w.xKategorie = k.xKategorie)   
                                        LEFT JOIN team as t ON a.xTeam = t.xTeam
                                        LEFT JOIN runde AS r ON(r.xWettkampf = w.xWettkampf)" 
                                  . $sqlEvents ."   
                                        AND w.Mehrkampfcode = 0 "                                    
                                  . $sqlGroup ."
                                  ORDER BY " . $sortAddition .$sort;  
                       }
			  }
		  }
		  else {							// relay event
			 
			//
			// get each athlete from all registered relays
			//      
            
           $sort = "st.Name";
           if(isset($_GET['sort'])){
                if  ($_GET['sort'] == 'nbr') {
                    $sort = "st.Startnummer, st.Name "; 
                }
           elseif ($_GET['sort'] == 'club') {  
                    $sort = "v.Sortierwert, st.Name "; 
           }             
           else {  
                $sort = "st.Name "; 
                }  
           }    
                    
            $query = "SELECT 
                            s2.xStart
                            , s2.Anwesend
                            , st.Name
                            , if('$svm', t.Name, v.Name) 
                            , a.Startnummer
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang
                            , at.Land
                             , stat.Position 
                             , st.Startnummer    
                       FROM 
                            staffel AS st     
                            LEFT JOIN start AS s ON (s.xStaffel = st.xStaffel  )
                            LEFT JOIN verein AS v  ON (st.xVerein = v.xVerein)
                            LEFT JOIN staffelathlet as stat ON (stat.xStaffelstart = s.xStart )
                            LEFT JOIN start as s2 ON (s2.xStart = stat.xAthletenstart)
                            LEFT JOIN anmeldung as a ON (a.xAnmeldung = s2.xAnmeldung)    
                            LEFT JOIN athlet as at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN team as t ON st.xTeam = t.xTeam
                     WHERE 
                            s.xWettkampf = " . $event ."       
                     ORDER BY $sortAddition $sort , stat.position ";     
                   
		  }

   
          $res = mysql_query($query);    
           
          $first=true;
          $athleteLine = '';
		  if(mysql_errno() > 0)		// DB error
		  {
			  AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		  }
		  else if(mysql_num_rows($res) > 0)  // data found
		  {  
			  $l = 0;		// line counter
              $noDisc = false;      
              
			  // full list
			  while ($row = mysql_fetch_row($res))
			  {   
                  if (!$relay){        // not relay and not combined        
                        // print only disciplines related to header 
                        if ($row[8]!=$discHeader & $xComb==0 ){ 
                            continue;
                         }
                  }
                  
				  if($l == 0) {					// new page, print header line
					  $doc->printTitle();   					   
					  if (strpos(get_class($doc),"pdf")==false) { printf("<table>\n"); }
					  $doc->printHeaderLine($relay, $svm);
				  }               
                  
				  if($relay == FALSE)
				  {     if($combined){  
				  	    	if ($_GET['event'] > 0){
				  	    	 	$pf=$row[10];          // best effort by choosed combined disziplin
								}
							else {
						      	$pf=$row[5];          // best MK
							}
				        }
				        else {
				             $pf=$row[5];            // best effort
				        }  
                        
                         if ($pf == 9999999){
                          $pf = 0;
                      }                				  	    	                       
				  	   
						// show top performance of athletes
						if(($row[6] == $cfgDisciplineType[$strDiscTypeJump])
							|| ($row[6] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
							|| ($row[6] == $cfgDisciplineType[$strDiscTypeThrow])
							|| ($row[6] == $cfgDisciplineType[$strDiscTypeHigh])) {
							   $perf = AA_formatResultMeter($pf); 
							//$perf = AA_formatResultMeter($row[5]);
						}else {
							if(($row[6] == $cfgDisciplineType[$strDiscTypeTrack])
							|| ($row[6] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
							  // $perf = AA_formatResultTime($row[5], true, true);
							     $perf = AA_formatResultTime($pf, true, true); 
							}else{
							  //$perf = AA_formatResultTime($row[5], true);
							  $perf = AA_formatResultTime($pf, true);  
							   
							}
						}
					  	if($combined){    
						    if ($_GET['event'] == 0){       // the whole combined event    
				  	      		$perf=$pf;         // points 
				  	  	  }
					   	}
                        
                         
                     
						$doc->printLine($row[0], $row[1] . " " . $row[2], AA_formatYearOfBirth($row[3]), $row[4], $row[7], $perf);
				  }
				  else
				  {   
                      if ($row[5] >= 9999999){
                          $row[5] = 0;
                      } 
                      
                      if ($keep_stName != $row[2]){
                            if (!$first) {
                                 $athleteLine=substr($athleteLine,0,-2);
                                 $doc->printLineAthlete($athleteLine); 
                                
                            }
                            $first=false;
                            $doc->printLine($row[10],$row[2],'','','','',$row[3]); 
                           
                            $athleteLine='';
                            $athleteLine.=$row[4].". " .$row[5] . " " . $row[6] .", ";   
                      }
                      else {
                            $athleteLine.=$row[4].". " .$row[5] . " " . $row[6] .", "; 
                      } 
				  }
				  $l++;			// increment line count
                  $keep_stName = $row[2];
			  }
              // print last relay with athletes
               if ($relay) {
                    $athleteLine=substr($athleteLine,0,-2);
                    $doc->printLineAthlete($athleteLine);
               } 
                                 
			  if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n"); }
			  mysql_free_result($res);   
		  }	
          else {
              $noDisc = true;              
          }	// ET DB error  
		}		// END same round
	}		// END WHILE events
	mysql_free_result($result);
}	// ET DB error event data

$doc->endPage();		// end a HTML page for printing

?>
