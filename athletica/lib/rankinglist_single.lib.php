<?php

/**********
 *
 *    rankinglist single events
 *    
 */
    
if (!defined('AA_RANKINGLIST_SINGLE_LIB_INCLUDED'))
{
    define('AA_RANKINGLIST_SINGLE_LIB_INCLUDED', 1);

function AA_rankinglist_Single($category, $event, $round, $formaction, $break, $cover, $biglist = false, $cover_timing = false, $date = '%',  $show_efforts = 'none',$heatSeparate,$catFrom,$catTo,$discFrom,$discTo,$heatFrom,$heatTo, $athleteCat, $withStartnr, $ranklistAll, $ukc)
{   
// anstead of remove the function "rankinglist ubs kids cup"", set $ukc for the moment false  --> later remove it perhaps
// "rankinglist ubs kids cup" is solved in rankinglist_combined.lib.php
$ukc = false;
        
require('./lib/cl_gui_page.lib.php');
require('./lib/cl_print_page.lib.php');  
require('./lib/cl_print_page_pdf.lib.php');   
require('./lib/cl_export_page.lib.php');

require('./lib/common.lib.php');
require('./lib/results.lib.php');
require('./lib/utils.lib.php'); 

if(AA_connectToDB() == FALSE)    { // invalid DB connection
    return;        // abort
}

if(AA_checkMeetingID() == FALSE) {        // no meeting selected
    return;        // abort
}  

// check teamsm
$teamsm = AA_checkTeamsm(0,0);

// set up ranking list selection
$selection = '';
$eventMerged = false;
$catMerged = false;
$flagSubtitle=false;
$flagInfoLine1=false; 
$flagInfoLine2=false;
 $results_ukc = FALSE;  
       
 $selectionHeats='';
 $orderAthleteCat=''; 
 
 $saison = $_SESSION['meeting_infos']['Saison'];  
 if ($saison == ''){
    $saison = "O";  //if no saison is set take outdoor
 }
 
if($round > 0) {    // show a specific round        

    $eventMerged=false;          
    $sqlEvents = AA_getMergedEventsFromEvent($event);
    if  ($sqlEvents!=''){              
         $selection = "w.xWettkampf IN " . $sqlEvents . " AND "; 
         $eventMerged=true; 
    }
    else            
          $selection = "r.xRunde =" . $round . " AND ";         
         
}
elseif($category == 0) {        // show all disciplines for every category    
      
         $catMerged=true;    
}
elseif ($event == 0) {    // show all disciplines for a specific category    
         $catMerged=false;
         $mergedCat=AA_mergedCat($category);
         if  ($mergedCat!=''){                   
                $selection = "w.xKategorie IN " . $mergedCat . " AND ";  
                $catMerged=true; 
         }
         else
                $selection = "w.xKategorie =" . $category . " AND ";    
}                            
else if($round == 0) {    // show all rounds for a specific event    
    $eventMerged=false;          
    $sqlEvents = AA_getMergedEventsFromEvent($event);
    if  ($sqlEvents!=''){              
         $selection = "w.xWettkampf IN " . $sqlEvents . " AND "; 
         $eventMerged=true; 
    }
    else
          $selection = "w.xWettkampf =" . $event . " AND ";  
} 

if($catFrom > 0) {    //         
     $getSortCat=AA_getSortCat($catFrom,$catTo);
     if ($getSortCat[0]) {
         if ($catTo > 0){
            $selection = "k.Anzeige >=" . $getSortCat[$catFrom] . " AND k.Anzeige <=" . $getSortCat[$catTo] . " AND "; 
        }     
        else {
            $selection = "k.Anzeige =" . $getSortCat[$catFrom] . " AND ";
        }
     }
}

if($discFrom > 0) {    //          
     $getSortDisc=AA_getSortDisc($discFrom, $discTo);
     if ($getSortDisc[0]) {
         if ($discTo > 0){
            $selection .= "d.Anzeige >=" . $getSortDisc[$discFrom] . " AND d.Anzeige <=" . $getSortDisc[$discTo] . " AND "; 
        }     
        else {
            $selection .= "d.Anzeige =" . $getSortDisc[$discFrom] . " AND ";
        }
     }
} 
 if($heatFrom > 0) {    
      $selectionHeats=' AND s.xSerie >= ' . $heatFrom .' AND s.xSerie <= ' . $heatTo .' ';  
 }
 
 if($athleteCat) {    
      $orderAthleteCat=' k1.Anzeige, ';  
 }         
 
 if (($catMerged & !$heatSeparate) || ($eventMerged & !$heatSeparate)) { 
 // get event rounds from DB         
    if ($ukc){       
          $selection = " (d.Code = " . $cfgUKC_disc[0] ." || d.Code = " . $cfgUKC_disc[1]  . " || d.Code = " . $cfgUKC_disc[2] . ") AND ";   
    }      
    if ($teamsm){
         $sql = "SELECT 
                    group_concat(r.xRunde)
                    , k.Name
                    , d.Name
                    , d.Typ
                    , w.xWettkampf
                    , r.QualifikationSieger
                    , r.QualifikationLeistung
                    , w.Punkteformel
                    , w.Windmessung
                    , r.Speakerstatus
                    , d.Staffellaeufer
                    , CONCAT(DATE_FORMAT(r.Datum,'$cfgDBdateFormat'), ' ', TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat'))     
                    , w.xDisziplin  
                    , w.info         
                FROM
                    wettkampf AS w
                    LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                      LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                      LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf) 
                WHERE " . $selection . "
                w.xMeeting = " . $_COOKIE['meeting_id'] . "     
                AND r.Status = " . $cfgRoundStatus['results_done'] . " 
                AND r.Datum LIKE '".$date."'
                GROUP BY w.xWettkampf
                ORDER BY
                    k.Anzeige
                    , d.Anzeige
                    , r.Datum
                    , r.Startzeit";
      
       $results = mysql_query($sql);

    } 
    else {  
   
            $sql = "SELECT 
                r.xRunde
                , k.Name
                , d.Name
                , d.Typ
                , w.xWettkampf
                , r.QualifikationSieger
                , r.QualifikationLeistung
                , w.Punkteformel
                , w.Windmessung
                , r.Speakerstatus
                , d.Staffellaeufer
                , CONCAT(DATE_FORMAT(r.Datum,'$cfgDBdateFormat'), ' ', TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat'))
                , w.xDisziplin  
                , w.info          
            FROM
                wettkampf AS w
                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                  LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                  LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf) 
            WHERE " . $selection . "
            w.xMeeting = " . $_COOKIE['meeting_id'] . "     
            AND r.Status = " . $cfgRoundStatus['results_done'] . " 
            AND r.Datum LIKE '".$date."'
            ORDER BY
                k.Anzeige
                , d.Anzeige
                , r.Datum
                , r.Startzeit";
          
           $results = mysql_query($sql);
    
    }
  
  }     
 else {      
     // heats separate
       
       $sql = "SELECT DISTINCT 
                r.xRunde , 
                k.Name , 
                d.Name , 
                d.Typ , 
                w.xWettkampf , 
                r.QualifikationSieger , 
                r.QualifikationLeistung , 
                w.Punkteformel , 
                w.Windmessung , 
                r.Speakerstatus , 
                d.Staffellaeufer , 
                CONCAT(DATE_FORMAT(r.Datum,'%d.%m.%y'), 
                ' ', 
                TIME_FORMAT(r.Startzeit, '%H:%i')) ,
                w.xDisziplin ,  
                rs.Hauptrunde,
                w.info     
            FROM 
                wettkampf AS w 
                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie) 
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf) 
                LEFT JOIN rundenset as rs ON (r.xRunde=rs.xRunde )           
            WHERE 
                " . $selection . "  
                w.xMeeting  = " . $_COOKIE['meeting_id'] . " 
                AND r.Status = 4  
                AND r.Datum LIKE '%' 
            ORDER BY
                k.Anzeige
                , d.Anzeige
                , r.Datum
                , r.Startzeit";

    $results = mysql_query($sql);
   
}        

if(mysql_errno() > 0) {        // DB error      
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());  
}
else {
      
    $limitRankSQL = "";
    $limitRank = false;
    if($_GET['limitRank'] == "yes"){ // check if ranks are limited, but limitRankSQL will set only if export is pressed
        if(!empty($_GET['limitRankFrom']) && !empty($_GET['limitRankTo'])){
            $limitRank = true;
        }
    }
    
    // start a new HTML display page
    if(($formaction == 'view')
        ||    ($formaction == 'speaker')) {    // display page
        $list = new GUI_RankingList($_COOKIE['meeting']);
        $list->printPageTitle("$strRankingLists " . $_COOKIE['meeting']);
    }
    // start a new HTML print page
    elseif($formaction == "print") {
        $list = new PRINT_RankingList_pdf($_COOKIE['meeting']);
        if($cover == true) {        // print cover page 
            $list->printCover($GLOBALS['strResults'], $cover_timing);              
        }
    }
    
    // export ranking
    elseif($formaction == "exportpress"){
        $list = new EXPORT_RankingListPress($_COOKIE['meeting'], 'txt');
        if($limitRank){
            $limitRankSQL = " AND ss.Rang <= ".$_GET['limitRankTo']." AND ss.Rang >= ".$_GET['limitRankFrom']." ";
        }
    }elseif($formaction == "exportdiplom"){
        $list = new EXPORT_RankingListDiplom($_COOKIE['meeting'], 'csv');
        if($limitRank){
            $limitRankSQL = " AND ss.Rang <= ".$_GET['limitRankTo']." AND ss.Rang >= ".$_GET['limitRankFrom']." ";
        }
    }
    
    // initialize variables
    $cat = '';
    $evnt = 0;            
    
    if (mysql_num_rows($results) == 0) {
        echo "<br><br><b><blockquote>$strErrNoResults</blockquote></b>";
    }
    
    $rounds = array();      
    $catUkc = "";
    $roundsUkc = array(); 
    $discUkc = "";
    $roundsInfo = array();
    $mergedRounds = '';
         
    while($row = mysql_fetch_row($results)){
        
         if (!$teamsm){
            $mergedRounds=AA_getMergedRounds($row[0]);  
         }
         if  (!empty($mergedRounds) ){                    
               if ($pos = strpos($mergedRounds, $row[0])){                    
                   $rounds[$i] = $row[0];  
                   if ($heatSeparate) {
                          if (!empty($row[14])){                
                            $roundsInfo[$row[0]] .= $row[14] ." / ";
                        }
                   }
                   else {
                        if (!empty($row[13])){                
                            $roundsInfo[$row[0]] .= $row[13] ." / ";       
                        }
                   }                     
               }
               else {
                    if (!empty($row[13])){                
                        $roundsInfo[$row[0]] .= $row[13] ." / ";   
                    }               
               }
         } 
          if ($row[2] != $discUkc){
               $roundsUkc[$row[1]][] =  $row[0];
          }
          $discUkc = $row[2];              
    }
    
    $results = mysql_query($sql);
     if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }

    while($row = mysql_fetch_row($results))
    {           
        // for a combined event, the rounds are merged, so jump until the next event
        if($cRounds > 1){
            $cRounds--;                    
            continue;
        }
                
        if ($ukc){
            $catUkc = $row[1];
           
            $rounds_in = "";                   
            for ($i = 0; $i < 3; $i++){
                if (!empty($roundsUkc[$catUkc][$i])) {
                     $rounds_in .= $roundsUkc[$catUkc][$i] . ",";
                }
            }
            $rounds_in = substr($rounds_in,0,strlen($rounds_in)-1);
            if (!empty($r1)) {
                
            }
            $roundSQL = "s.xRunde IN ($rounds_in) ";  
            $GroupByUkc = " Group By at.xAthlet, w.xDisziplin ";                  
            
           
        }
        else {
             $roundSQL = "s.xRunde = $row[0]";
             $GroupByUkc = "";
             
        }
        if ($teamsm){
               $roundSQL = "s.xRunde IN ($row[0]) ";  
        }
        
        $cRounds = 0;
        
        // check page  break
        if((is_a($list, "PRINT_RankingList") 
		|| is_a($list, "PRINT_RankingList_pdf"))   // page for printing
            && ($cat != '')                        // not first result row
            && (($break == 'discipline')    // page break after each discipline
                || (($break == 'category')    // or after new category
                    && ($row[1] != $cat))))
        {
            $list->insertPageBreak();
        }
        
        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                || ($row[3] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                || ($row[3] == $cfgDisciplineType[$strDiscTypeRelay]))
        {
            $eval = $cfgEvalType[$strEvalTypeHeat];
        }
        else
        {
            $eval = $cfgEvalType[$strEvalTypeAll];
        }

        $roundName = '';
        $type = '';
        if ($teamsm){
              $res = mysql_query("
                SELECT
                    rt.Name
                    , rt.Typ
                    , rt.Wertung
                FROM
                    runde
                    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON (rt.xRundentyp = runde.xRundentyp)
                WHERE 
                    runde.xRunde IN ($row[0])             
            ");
        }
        else {
              $res = mysql_query("
                SELECT
                    rt.Name
                    , rt.Typ
                    , rt.Wertung
                FROM
                    runde
                    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON (rt.xRundentyp = runde.xRundentyp)
                WHERE 
                    runde.xRunde = $row[0]             
                    ");
        }
       

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            if(mysql_num_rows($res) > 0) {
                $row_rt = mysql_fetch_row($res);
                
                if($row_rt[1] == '0'){
                    $type = " ";
                    $row_rt[0] = '';
                }else{
                    $type = $row_rt[0]." ";
                }
                
                $eval = $row_rt[2];
                if($round != 0) {        // specific round selected
                    $roundName = $row_rt[0];
                }
            }
            mysql_free_result($res);
        }
         
        if($evnt != $row[4])        // new event -> repeat title
        {   
            // if this is a combined event, dont fragment list by rounds
            $combined = AA_checkCombined($row[4]);
            // not selectet a specific round
            if($round == 0 && $combined && !$ukc){
                $res_c = mysql_query("SELECT 
                                r.xRunde
                            FROM
                                wettkampf as w
                                LEFT JOIN runde as r ON (r.xWettkampf = w.xWettkampf)
                            WHERE    
                                w.xWettkampf = $row[4]
                                AND r.status = 4");
                
                if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }else{
                    $cRounds = mysql_num_rows($res_c);                        
                    $roundSQL = "s.xRunde IN (";
                    while($row_c = mysql_fetch_array($res_c)){
                        $roundSQL .= $row_c[0].",";
                    }
                    $roundSQL = substr($roundSQL, 0, -1).")";
                }
            }
            
            // set up category and discipline title information 
               $flagSubtitle=true;       // set flag to print the subtitle later
        
            if(($formaction == 'speaker')     // speaker display page
                && (AA_getNextRound($row[4], $row[0]) == 0))
            {
                // last round: show ceremony status
                $list->printCeremonyStatus($row[0], $row[9]);
            }

            // print qualification mode if round selected
            $info = '';
            if(($round > 0)
                && (($row[5] > 0) || ($row[6] > 0)))
            {
                $info = "$strQualification: "
                            . $row[5] . " $strQualifyTop, "
                            . $row[6] . " $strQualifyPerformance";
                $flagInfoLine1=true;         // set flag to print later the qualification mode if round selected  
                $info_save1=$info;
                //$list->printInfoLine($info);
                $qual_mode = TRUE;
            }                     
            // print qualification descriptions if required 
            $info = '';
            if(($row[5] > 0) || ($row[6] > 0))
            {
                foreach($cfgQualificationType as $qt)
                {
                    $info = $info . $qt['token'] . " ="
                            . $qt['text'] . "&nbsp;&nbsp;&nbsp;";
                }
                $flagInfoLine2=true;         // set flag to print later the qualification descriptions if required
                $info_save2=$info; 
                //$list->printInfoLine($info);
                $qual_mode = TRUE;
            }               
            $evnt = $row[4];    // keep event ID            
        } // ET new event
              
        $relay = AA_checkRelay($row[4]);    // check, if this is a relay event
        $svm = AA_checkSVM($row[4]);    
        
        // If round evaluated per heat, group results accordingly    
        $order_heat = "";  
        if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
            $order_heat = "heatid, ";
        }
       
        $valid_result ="";
        // Order performance depending on discipline type
        if(($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
            || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow]))
        {
            $order_perf = "DESC";
            $order_perf2 = "DESC"; 
        }
        else if($row[3] == $cfgDisciplineType[$strDiscTypeJump])
        {
            if ($row[8] == 1) {            // with wind
                $order_perf = "DESC, r.Info ASC";
                $order_perf2 = "DESC , r.Info ASC";
            }
            else {                            // without wind
                $order_perf = "DESC";
                $order_perf2 = "DESC";
            }
        }
        else if($row[3] == $cfgDisciplineType[$strDiscTypeHigh])
        {
            $order_perf = "DESC";
            $order_perf2 = "DESC"; 
            $valid_result =    " AND (r.Info LIKE '%O%'"
                                        . " OR r.Leistung < 0)";
        }
        else
        {
            $order_perf = "ASC";
            $order_perf1 = "ASC"; 
        }
       
        $sqlSeparate='';    
        if (($catMerged || $eventMerged) & $heatSeparate) {   
            
             if ($row[0] > 0 && $row[13] != NULL) {  
                $roundSQL = '';  
                if (empty($limitRankSQL) && empty($valid_result)){
                        $sqlSeparate=" ss.RundeZusammen = " . $row[0];   
                } 
                else {
                     if (empty($limitRankSQL) ){
                           $valid_result = substr($valid_result,4, strlen($valid_result));
                     }
                     elseif (empty($valid_result) ){
                             $limitRankSQL = substr($limitRankSQL,4, strlen($limitRankSQL));   
                     }
                    $sqlSeparate=" AND ss.RundeZusammen = " . $row[0];   
                }  
             }  
        } 
        
        // get all results ordered by ranking; for invalid results (Rang=0), the
        // rank is set to max_rank to put them to the end of the list.
        $oder2 = "";
        $max_rank = 999999999;  
        if ($ukc){
             $order2 = " d.Anzeige, rank, at.Name, at.Vorname, leistung_neu " ;                               
             $sql_leistung = ($order_perf=='ASC') ? "max(r.Leistung)" : "IF(r.Leistung<0, (If(r.Leistung = -99, -9, (If (r.Leistung = -98, -8,max(r.Leistung)))) * -1), max(r.Leistung))";                                                                                                                
        }                              
        else {
             $order2 = " rank, at.Name, at.Vorname, leistung_neu " ;               
             $sql_leistung = ($order_perf=='ASC') ? "r.Leistung" : "IF(r.Leistung<0, (If(r.Leistung = -99, -9, (If (r.Leistung = -98, -8,r.Leistung))) * -1), r.Leistung)";           
        }
                                           
        $order= $order_heat;          
        
        if ($ukc){
                if ($athleteCat){
                    $order=$orderAthleteCat . $order_heat;   
                }
                $selection2 = ""; 
                $checkyear= date('Y') - 16;
                $selection2 = " at.Jahrgang > $checkyear AND (d.Code = " . $cfgUKC_disc[0] ." || d.Code = " . $cfgUKC_disc[1]  . " || d.Code = " . $cfgUKC_disc[2] .") AND ";    
                
                $query = "SELECT ss.xSerienstart, 
                             IF(ss.Rang=0, $max_rank, ss.Rang) AS rank, 
                             ss.Qualifikation, 
                             ".$sql_leistung." AS leistung_neu, 
                             r.Info, 
                             s.Bezeichnung, 
                             s.Wind, 
                             r.Punkte, 
                             IF('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)), 
                             at.Name, 
                             at.Vorname, 
                             at.Jahrgang, 
                             LPAD(s.Bezeichnung, 5, '0') AS heatid, 
                             IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land, 
                             at.xAthlet, 
                             ru.Datum, 
                             ru.Startzeit ,
                             ss.RundeZusammen,
                             ru.xRunde,  
                             k.Name , 
                             k1.Name ,                             
                             k1.Anzeige ,
                             ss.Bemerkung,
                             w.Punkteformel,
                             w.info,
                             a.Startnummer,
                             w.xWettkampf,
                             at.Geschlecht
                             
                        FROM serie AS s USE INDEX(Runde)
                   LEFT JOIN serienstart AS ss USING(xSerie) 
                   LEFT JOIN resultat AS r USING(xSerienstart) 
                   LEFT JOIN start AS st ON(ss.xStart = st.xStart) 
                   LEFT JOIN anmeldung AS a USING(xAnmeldung) 
                   LEFT JOIN athlet AS at USING(xAthlet) 
                   LEFT JOIN verein AS v USING(xVerein) 
                   LEFT JOIN region AS re ON(at.xRegion = re.xRegion) 
                   LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                   LEFT JOIN runde AS ru ON(s.xRunde = ru.xRunde) 
                   LEFT JOIN wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                   LEFT JOIN kategorie AS k On (w.xKategorie= k.xKategorie)
                   LEFT JOIN kategorie AS k1 ON (a.xKategorie = k1.xKategorie)   
                   LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin)   
                       WHERE " . $selection2 .$roundSQL." 
                       ".$limitRankSQL." 
                       ".$valid_result." 
                       ".$sqlSeparate." 
                       ".$selectionHeats." 
                     $GroupByUkc 
                    ORDER BY ".$order                                
                              .$order2
                             .$order_perf1;                
                                  
        }
         else {
        if($relay == FALSE) {                                 
                
                if ($athleteCat){
                    $order=$orderAthleteCat . $order_heat;   
                }
                $selection2 = "";
                
                if ($ukc){
                    $checkyear= date('Y') - 16;                          
                    $selection2 = " at.Jahrgang > $checkyear AND (d.Code = " . $cfgUKC_disc[0] ." || d.Code = " . $cfgUKC_disc[1]  . " || d.Code = " . $cfgUKC_disc[2] .") AND ";   
                    
                }
                if ($teamsm){
                    $query = "SELECT ss.xSerienstart, 
                             IF(ss.Rang=0, $max_rank, ss.Rang) AS rank, 
                             ss.Qualifikation, 
                             ".$sql_leistung." AS leistung_neu, 
                             r.Info, 
                             s.Bezeichnung, 
                             s.Wind, 
                             r.Punkte, 
                             t.Name, 
                             at.Name, 
                             at.Vorname, 
                             at.Jahrgang, 
                             LPAD(s.Bezeichnung, 5, '0') AS heatid, 
                             IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land, 
                             at.xAthlet, 
                             ru.Datum, 
                             ru.Startzeit ,
                             ss.RundeZusammen,
                             ru.xRunde,  
                             k.Name , 
                             k1.Name ,                             
                             k1.Anzeige ,
                             ss.Bemerkung,
                             w.Punkteformel,
                             w.info,
                             a.Startnummer,
                             w.xWettkampf,
                             at.Geschlecht
                             
                        FROM serie AS s USE INDEX(Runde)
                   LEFT JOIN serienstart AS ss USING(xSerie) 
                   LEFT JOIN resultat AS r USING(xSerienstart) 
                   LEFT JOIN start AS st ON(ss.xStart = st.xStart) 
                   LEFT JOIN anmeldung AS a USING(xAnmeldung) 
                   LEFT JOIN athlet AS at USING(xAthlet) 
                   LEFT JOIN verein AS v USING(xVerein) 
                   LEFT JOIN region AS re ON(at.xRegion = re.xRegion) 
                   INNER JOIN teamsmathlet AS tat ON(st.xAnmeldung = tat.xAnmeldung)
                   LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                   LEFT JOIN runde AS ru ON(s.xRunde = ru.xRunde) 
                   LEFT JOIN wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                   LEFT JOIN kategorie AS k On (w.xKategorie= k.xKategorie)
                   LEFT JOIN kategorie AS k1 ON (a.xKategorie = k1.xKategorie)   
                   LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin)   
                       WHERE " . $selection2 .$roundSQL." 
                       ".$limitRankSQL." 
                       ".$valid_result." 
                       ".$sqlSeparate." 
                       ".$selectionHeats." 
                     $GroupByUkc 
                    ORDER BY ".$order                                
                              .$order2
                             .$order_perf;  
                }
                else {
                    $query = "SELECT ss.xSerienstart, 
                             IF(ss.Rang=0, $max_rank, ss.Rang) AS rank, 
                             ss.Qualifikation, 
                             ".$sql_leistung." AS leistung_neu, 
                             r.Info, 
                             s.Bezeichnung, 
                             s.Wind, 
                             r.Punkte, 
                             IF('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)), 
                             at.Name, 
                             at.Vorname, 
                             at.Jahrgang, 
                             LPAD(s.Bezeichnung, 5, '0') AS heatid, 
                             IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land, 
                             at.xAthlet, 
                             ru.Datum, 
                             ru.Startzeit ,
                             ss.RundeZusammen,
                             ru.xRunde,  
                             k.Name , 
                             k1.Name ,                             
                             k1.Anzeige ,
                             ss.Bemerkung,
                             w.Punkteformel,
                             w.info,
                             a.Startnummer,
                             w.xWettkampf,
                             at.Geschlecht
                             
                        FROM serie AS s USE INDEX(Runde)
                   LEFT JOIN serienstart AS ss USING(xSerie) 
                   LEFT JOIN resultat AS r USING(xSerienstart) 
                   LEFT JOIN start AS st ON(ss.xStart = st.xStart) 
                   LEFT JOIN anmeldung AS a USING(xAnmeldung) 
                   LEFT JOIN athlet AS at USING(xAthlet) 
                   LEFT JOIN verein AS v USING(xVerein) 
                   LEFT JOIN region AS re ON(at.xRegion = re.xRegion) 
                   LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                   LEFT JOIN runde AS ru ON(s.xRunde = ru.xRunde) 
                   LEFT JOIN wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                   LEFT JOIN kategorie AS k On (w.xKategorie= k.xKategorie)
                   LEFT JOIN kategorie AS k1 ON (a.xKategorie = k1.xKategorie)   
                   LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin)   
                       WHERE " . $selection2 .$roundSQL." 
                       ".$limitRankSQL." 
                       ".$valid_result." 
                       ".$sqlSeparate." 
                       ".$selectionHeats." 
                     $GroupByUkc 
                    ORDER BY ".$order                                
                              .$order2
                             .$order_perf;  
                }
                
                 
        }
        else {                        // relay event
                                
            $query = "SELECT ss.xSerienstart,                                   
                             IF(r.Leistung < 0 , $max_rank, if (ss.Rang=0, $max_rank-1, ss.Rang)) AS rank, 
                             ss.Qualifikation, 
                             ".$sql_leistung." AS leistung_neu, 
                             r.Info, 
                             s.Bezeichnung, 
                             s.Wind, 
                             r.Punkte, 
                             IF('".$svm."', t.Name, v.Name), 
                             sf.Name, 
                             LPAD(s.Bezeichnung, 5, '0') AS heatid, 
                             st.xStart, 
                             ru.Datum, 
                             ru.Startzeit, 
                             ss.RundeZusammen,
                             ru.xRunde,
                             k.Name ,
                             ss.Bemerkung    
                        FROM serie AS s USE INDEX(Runde) 
                   LEFT JOIN serienstart AS ss USING(xSerie) 
                   LEFT JOIN resultat AS r USING(xSerienstart) 
                   LEFT JOIN start AS st ON(ss.xStart = st.xStart) 
                   LEFT JOIN staffel AS sf USING(xStaffel) 
                   LEFT JOIN verein AS v USING(xVerein) 
                   LEFT JOIN team AS t ON(sf.xTeam = t.xTeam) 
                   LEFT JOIN runde AS ru ON(s.xRunde = ru.xRunde) 
                   LEFT JOIN wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                   LEFT JOIN kategorie AS k On (w.xKategorie= k.xKategorie) 
                       WHERE ".$roundSQL." 
                      ".$limitRankSQL." 
                      ".$valid_result." 
                      ".$sqlSeparate."                         
                    GROUP BY r.xSerienstart 
                    ORDER BY ".$order." 
                             rank, 
                             r.Leistung 
                             ".$order_perf.", 
                             sf.Name;";   
                 
        }    
         }
         
       
        $res = mysql_query($query);
        if(mysql_errno() > 0) {        // DB error   
       
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
              if (mysql_num_rows($res)==0){                          
                    continue;             
              }  
                               
              
            // initialize variables
            $heat = '';
            $h = 0;
            $info = '';
            $id = '';
            $r = '';
            $count_rank=0;
            $perf_save = '';  
            //$list->startList();
            $nr = 0;             
            $atCat = ''; 
            
            
            if ($ukc){
                  $arr_xAthlet = array();
                  while($row_at = mysql_fetch_array($res)) {
                        if   (array_key_exists($row_at[14], $arr_xAthlet)){
                              $arr_xAthlet[$row_at[14]] ++;                     
                        }
                        else {
                              $arr_xAthlet[$row_at[14]] = 1;                     
                        }
                      
                  }  
            } 
                              
            $res = mysql_query($query); 
            // process every result
            while($row_res = mysql_fetch_array($res))
            {     
                if ($ukc){  
                
                    if ($arr_xAthlet[$row_res[14]] < 3){
                         $id = $row_res[0];                // keep current athletes ID
                        if ($relay)
                             $catM = $row_res[16];      // keep merged category relay
                        else
                            $catM = $row_res[19];       // keep merged category   
                        continue;
                    }  
                    if ($row[0] !=  $row_res[18]) {
                         $id = $row_res[0];                // keep current athletes ID
                        if ($relay)
                             $catM = $row_res[16];      // keep merged category relay
                        else
                            $catM = $row_res[19];       // keep merged category   
                        continue; 
                    }   
                    $results_ukc = TRUE;                  
                    $pointsUKC = AA_utils_calcPointsUKC($row_res[26],$row_res[3],0, $row_res[27], $row_res[0]);  
                    
                    mysql_query("UPDATE resultat SET
                                    Punkte = $pointsUKC
                                WHERE
                                    xSerienstart = $row_res[0]");
                            if(mysql_errno() > 0) {
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }
                    AA_StatusChanged(0,0, $row_res[0]);
                   
                }
                
                if ($flagSubtitle ){  
                    $r_info = '';
                    $mainRound = '';
                    if (!$teamsm) {
                        $mainRound = AA_getMainRound($row[0]);
                    }
                    if ($mainRound > 0){ 
                        if ($heatSeparate) {                     
                              $r_info = $roundsInfo[$row[0]];                                       
                              $r_info = substr($r_info,0, -3);   
                        }
                        else {
                               
                               $mergedRounds=AA_getMergedRounds($row[0]);       
                               $mRounds = split(',',substr($mergedRounds,1,-1));
                               foreach ($mRounds as $key => $val){
                                    $r_info .= $roundsInfo[$val];    
                               }
                               $r_info = substr($r_info,0, -3);   
                        }
                    }
                    else {
                         $r_info = $row_res[24];     
                    }
                    
                    $nr = 0;    
                    if ($heatSeparate) {
                        if ($relay)
                            $list->printSubTitle($row_res[16], $row[2], $roundName,$r_info); 
                        else {
                             if (!$athleteCat){  
                                  $list->printSubTitle($row_res[19], $row[2], $roundName, $r_info); 
                             } 
                        }  
                    }
                    else {
                        if (!$athleteCat){  
                            $list->printSubTitle($row[1], $row[2], $roundName, $r_info, $heatFrom, $heatTo, $row_rt[2]);                         
                        }   
                    }                       
                    $flagSubtitle=false; 
                }
                if (!$athleteCat){ 
                    if ($flagInfoLine1){   
                        $list->printInfoLine($info_save1);
                        $flagInfoLine1=false;  
                    }
                }
                if (!$athleteCat){ 
                     if ($flagInfoLine2){  
                        $list->printInfoLine($info_save2);
                        $flagInfoLine2=false;  
                    }
                } 
                 
                $row_res[3] = ($row_res[3]==1 || $row_res[3]==2 || $row_res[3]==3 || $row_res[3]==4) ? ($row_res[3] * -1) : (($row_res[3]==9) ? -99 : (($row_res[3]==8) ? -98 : $row_res[3]));                                                                                             
                 
                if($row_res[0] != $id)    // athlete not processed yet
                {  
                    if(($h == 0)                        // first header line or ...
                        || (($row_res[5] != $heat) // new header after each heat
                            && ($eval == $cfgEvalType[$strEvalTypeHeat])) || ($athleteCat && ($row_res[21] != $atCat)))
                    {  
                        $count_rank=0;
                        $nr=0;
                        // heat name
                        
                        if($eval == $cfgEvalType[$strEvalTypeHeat]) {
                            if(empty($type))    {            // no round type defined
                                $type = $strFinalround . " ";
                                
                            }
                            $title = $type . $row_res[5];    // heat name with nbr.
                             
                        }
                        else {
                            $title = $type;    // heat name withour nbr.
                             
                        }
                        
                        $title = trim($title);
                         
                        // wind per heat
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                                && ($row[8] == 1)
                                && ($eval == $cfgEvalType[$strEvalTypeHeat]))
                        {
                            $heatwind = $row_res[6];        // wind per heat
                        }
                        else {
                            $heatwind = '';                    // no wind 
                        }

                        $wind= FALSE;
                        if(($row[8] == 1) 
                            && ($row[3] == $cfgDisciplineType[$strDiscTypeJump]) 
                            || (($row[3] == $cfgDisciplineType[$strDiscTypeTrack]) 
                                && ($eval == $cfgEvalType[$strEvalTypeAll])))
                        {
                            $wind= TRUE;
                        }

                        // add column header 'points' if required
                        $points= FALSE;
                        if($row[7] != '0' || $row_res[23] != '0') {
                            $points= TRUE;
                        }
                        elseif ($ukc){
                               $points= TRUE;
                        }
                       
                        if ($show_efforts == 'sb_pb'){
                            $base_perf = true;
                        } 
                                               
                        if ($athleteCat && !$relay){                         
                          if($formaction == 'print') {   
                                if ($row_res[20].$row[2]!=$atCatName){                        
                                       $list->printSubTitle($row_res[20], $row[2], $roundName, $row_res[24]);  
                                       $atCatName=$row_res[20].$row[2];
                                       if ($flagInfoLine1){   
                                        $list->printInfoLine($info_save1);
                                        $flagInfoLine1=false;  
                                    } 
                                    if ($flagInfoLine2){   
                                        $list->printInfoLine($info_save2);
                                        $flagInfoLine2=false;  
                                    } 
                                }  
                              }                            
                           }     
                       
                           $list->startList(); 
                        if ($saison == "I"){
                            $heatwind = '';
                        }
                        if ($relay && !$svm){   
                           $points = false;
                        }
                        
                        $list->printHeaderLine($title, $relay, $points, $wind, $heatwind, $row[11], $svm, $base_perf, $qual_mode, $eval, $withStartnr, $teamsm);
                        
                          
                         if ($athleteCat && !$relay){                         
                            if($formaction == 'view') {
                                 if ($row_res[20].$row[2]!=$atCatName){                        
                                           $list->printSubTitle($row_res[20], $row[2], $roundName, $row_res[24]);                                    
                                           $atCatName=$row_res[20].$row[2];   
                                           if ($flagInfoLine1){   
                                            $list->printInfoLine($info_save1,$athleteCat);
                                            $flagInfoLine1=false;  
                                        } 
                                          if ($flagInfoLine2){   
                                              $list->printInfoLine($info_save2,$athleteCat);
                                              $flagInfoLine2=false;  
                                          }
                                                    
                                    }                          
                            }
                         }
                                                              
                        $heat = $row_res[5];            // keep heat description
                        $atCat = $row_res[21];             // keep athlete category                          
                        $h++;                        // increment if evaluation per heat
                    } 
                  
                    $count_rank++;                       
                   
                    // rank    
                    if ($teamsm){
                        if ($perf_save !=''){
                            if ($perf_save == $row_res[3]){
                                $count_rank--;
                                $rank = '';
                            }
                            else {
                                 $rank= $count_rank;     
                            }
                        }
                        else {
                             $rank= $count_rank;    
                        }
                        
                    } 
                    else { 
                         if ($heatSeparate){
                              if ($row_res[1]==$max_rank || $row_res[1]==$max_rank-1) {   // invalid result
                                    $rank='';
                              } 
                              elseif ($r == $row_res[1] && $heat_keep == $row_res[5]) { // same rank as previous
                                        $rank= $count_rank--;
                              }  
                              else {
                                    if ($ukc){
                                        $rank= $count_rank;
                                    }
                                    else {
                                        $rank= $row_res[1];   
                                    }  
                             }
                         }    
                         else {
                             if(($row_res[1]==$max_rank || $row_res[1]==$max_rank-1)         // invalid result
                                    || ($r == $row_res[1] && $heat_keep == $row_res[5])) {        // same rank as previous
                                    $rank='';
                             }
                             else {
                                if ($ukc){
                                    $rank= $count_rank;
                                }
                                else {
                                    $rank= $row_res[1];   
                                }  
                             }
                         }  
                    }
                            
                    $r= $row_res[1];                // keep rank  
                    $heat_keep= $row_res[5];        // keep heat    
                    

                    // name
                    $name = $row_res[9];
                    if($relay == FALSE) {
                        $name = $name . " " . $row_res[10];
                    }

                    // year of birth
                    if($relay == FALSE) {
                        $year = AA_formatYearOfBirth($row_res[11]);
                    }
                    else {
                        $year = '';
                    }
                    
                    // year of birth
                    if($relay == FALSE) {
                        $land = ($row_res[13]!='' && $row_res[13]!='-') ? $row_res[13] : '';
                    }
                    else {
                        $year = '';
                    }

                    // performance                      
                    if($row_res[3] < 0) {    // invalid result
                        if ($row_res[3] == '-98'){ 
                            if ($row[3] == $cfgDisciplineType[$strDiscTypeJump] || $row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind] || $row[3] == $cfgDisciplineType[$strDiscTypeThrow]){
                                  $perf = $cfgInvalidResult['NRS']['short'];  
                            }  
                            else {
                                  $perf = $cfgInvalidResult['NAA']['code'];  
                            }                        
                                                       
                        }
                        elseif ($row_res[3] == '-99'){                           
                            $perf = $cfgInvalidResult['WAI']['short']; 
                       }
                        else {
                            foreach($cfgInvalidResult as $value)    // translate value
                                {   
                                if($value['code'] == $row_res[3]) {
                                    $perf = $value['short'];                                       
                                }
                            }
                        }
                    }                    
                    else if(($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeHigh])) {
                        $perf = AA_formatResultMeter($row_res[3]);
                    }
                    else {
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                            $perf = AA_formatResultTime($row_res[3], true, true);
                        }else{
                            $perf = AA_formatResultTime($row_res[3], true);
                        }
                    }

                    $qual = '';
                    if($row_res[2] > 0) {    // Athlete qualified
                        foreach($cfgQualificationType as $qtype)
                        {
                            if($qtype['code'] == $row_res[2]) {
                                $qual = $qtype['token'];
                            }
                        }
                    }    // ET athlete qualified

                    // points for performance
                    $points = '';
                    if($row[7] != '0') {
                        $points = $row_res[7];
                    }
                    else {
                        if($row_res[23] != '0') {
                            $points = $row_res[7];
                        }
                    }
                    
                      
                    // wind info
                    $wind = '';
                    $secondResult = false;                   
                   
                    if($r != $max_rank)     // valid result
                    {                                    
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                            && ($row[8] == 1))
                        {
                            $wind = $row_res[4];
                           
                            if ($saison == 'I'){  
                                $wind = '';           // indoor: never wind  
                            }                                    
                           
                            
                            //
                            // if wind bigger than max wind (2.0) show the next best result without wind too
                            //
                            if($wind > 2){
                                $res_wind = mysql_query("
                                        SELECT Info, Leistung FROM
                                            resultat
                                        WHERE
                                            xSerienstart = $row_res[0]
                                        ORDER BY
                                            Leistung ASC");
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }else{
                                    while($row_wind = mysql_fetch_array($res_wind)){
                                        
                                        if($row_wind[0] <= 2){
                                            $secondResult = true;
                                            $wind2 = $row_wind[0].")";
                                            $perf2 = "(".AA_formatResultMeter($row_wind[1]);
                                        }
                                        
                                    }
                                }
                            }
                            
                            
                        }
                        else if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                            && ($row[8] == 1)
                            && ($eval == $cfgEvalType[$strEvalTypeAll])) 
                        {
                            $wind = $row_res[6];
                            
                            if ($saison == 'I'){  
                                $wind = '';           // indoor: never wind  
                            }                                    
                        }
                    }
                    
                    
                    // ioc country code
                    $ioc = '';
                    if($relay == false){
                        $ioc = $row_res[13];
                    }
                    
                    $checkWind = ($is_jump) ? $row_res[4] : $row_res[6];
                    
                    //show performances from base
                    if($show_efforts == 'sb_pb' && $relay == false){
                                                                                               
                        $sql = "SELECT 
                                    season_effort
                                    , DATE_FORMAT(season_effort_date, '%d.%m.%Y') AS sb_date
                                    , season_effort_event
                                    , best_effort
                                    , DATE_FORMAT(best_effort_date, '%d.%m.%Y') AS pb_date
                                    , best_effort_event
                                    , season
                                    , xAnmeldung
                        FROM 
                            base_performance
                        LEFT JOIN 
                            base_athlete USING (id_athlete)
                        LEFT JOIN 
                            disziplin_" . $_COOKIE['language'] . " ON (discipline = Code)
                        LEFT JOIN 
                            athlet ON (license = Lizenznummer)
                        LEFT JOIN
                            anmeldung USING(xAthlet) 
                        WHERE 
                            athlet.xAthlet = $row_res[14]
                            AND xDisziplin = $row[12]
                            AND season = '$saison' 
                            AND xMeeting = ".$_COOKIE['meeting_id'].";";
                        $res_perf = mysql_query($sql);
                        
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }else{
                            if ($res_perf){
                                $row_perf = mysql_fetch_array($res_perf);
                            
                                $is_jump = (($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeHigh]));
                                $order = ($is_jump) ? 'DESC' : 'ASC';
                                
                                $best_previous = '';    
                                $previous_date = '';                            
                                if($row_perf!==false){
                                    $best_previous = AA_getBestPrevious($row[12], $row_perf['xAnmeldung'], $order, $row_res['Datum'], $row_res['Startzeit'], &$previous_date);
                                }
                                
                                if($is_jump) {
                                    $sb_perf = AA_formatResultMeter(str_replace(".", "", $row_perf['season_effort']));
                                    $pb_perf = AA_formatResultMeter(str_replace(".", "", $row_perf['best_effort']));
                                    $bp_perf = AA_formatResultMeter(str_replace(".", "", $best_previous));
                                    if($checkWind <= 2) {
                                        if($bp_perf>0 && $bp_perf>$sb_perf){
                                            $sb_perf = $bp_perf;
                                            $row_perf['season_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                            $row_perf['sb_date'] = date('d.m.Y', strtotime($previous_date));
                                        }
                                        
                                        if($bp_perf>0 && $bp_perf>$pb_perf){
                                            $pb_perf = $bp_perf;
                                            $row_perf['best_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                            $row_perf['pb_date'] = date('d.m.Y', strtotime($previous_date));
                                        }
                                        
                                        //highlight sb or pb if new performance is better
                                        if (is_numeric($perf)){ //prevent special-codes (disq, n.a. usw)
                                            if ($formaction!='print'){
                                                if ($pb_perf!='' && $perf>$pb_perf){
                                                    $perf = "<b>PB $perf</b> ";
                                                } else {
                                                    if ($sb_perf!='' && $perf>$sb_perf){
                                                        $perf = "<b>SB $perf</b>";
                                                    }
                                                }                                        
                                            } else {
                                                if ($pb_perf!='' && $perf>$pb_perf){
                                                    $perf = "<b>PB</b> $perf";
                                                } else {
                                                    if ($sb_perf!='' && $perf>$sb_perf){
                                                        $perf = "<b>SB</b> $perf";
                                                    }
                                                }                                        
                                            }
                                        }
                                    }

                                } else {
                                    //convert performance-time to milliseconds
                                    $timepices = explode(":", $row_perf['season_effort']);
                                    $season_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) +($timepices[2] *  1000) + ($timepices[3]);
                                    $timepices = explode(":", $row_perf['best_effort']);
                                    $best_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) +($timepices[2] *  1000) + ($timepices[3]);
                                    $previous_effort = intval($best_previous);
                                    
                                    if($checkWind <= 2) {
                                    
                                        if($previous_effort>0 && $previous_effort<$season_effort){
                                            $season_effort = $previous_effort;
                                            $row_perf['season_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                            $row_perf['sb_date'] = date('d.m.Y', strtotime($previous_date));
                                        }
                                        
                                        if($previous_effort>0 && $previous_effort<$best_effort){
                                            $best_effort = $previous_effort;
                                            $row_perf['best_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                            $row_perf['pb_date'] = date('d.m.Y', strtotime($previous_date));
                                        }
                                        
                                        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                                        || ($row[3] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                                            $sb_perf = AA_formatResultTime($season_effort, true, true);
                                            $pb_perf = AA_formatResultTime($best_effort, true, true);
                                        }else{
                                            $sb_perf = AA_formatResultTime($season_effort, true);
                                            $pb_perf = AA_formatResultTime($best_effort, true);
                                        }
                                        if ($formaction!='print'){
                                            //highlight sb or pb if new performance is better
                                            if ($pb_perf!='' && $perf<$pb_perf){
                                                $perf = "<b>PB $perf</b>";
                                            } else {
                                                if ($sb_perf!='' && $perf<$sb_perf){
                                                    $perf = "<b>SB $perf</b>";
                                                }
                                            }
                                        } else {
                                            if ($pb_perf!='' && $perf<$pb_perf){
                                                $perf = "<b>PB</b> $perf";
                                            } else {
                                                if ($sb_perf!='' && $perf<$sb_perf){
                                                    $perf = "<b>SB</b> $perf";
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                if (!empty($row_perf['season_effort'])){
                                    $sb = "<a href=\"#\" class=\"info\">$sb_perf<span>$row_perf[sb_date]<br>$row_perf[season_effort_event]</span></a>";
                                } else {
                                    $sb = "&nbsp;";
                                }
                                
                                if (!empty($row_perf['best_effort'])){
                                    $pb = "<a href=\"#\" class=\"info\">$pb_perf<span>$row_perf[pb_date]<br>$row_perf[best_effort_event]</span></a>";
                                } else {
                                    $pb = "&nbsp;";
                                }
                            }        
                        }        
                    }
                    if ($heatSeparate && $row_res[17] > 0) {    
                         $rank=$count_rank;                             
                         if ( $row_res[3] < 0)        // invalid result 
                             $rank='';  
                    }    
                    
                    if ($athleteCat && !$relay){
                        $nr++;                                 
                        if ($rank!='') {
                            if ($formaction == "print") {
                                $rank=$nr.". (".$rank.")"; 
                            }
                            else {
                                $rank=$nr." (".$rank.")"; 
                            }   
                        }          
                    } 
                    else { if ($formaction == "print") {
                                if ($rank!='') {
                                    $rank.=".";
                                }
                           }
                    }
                    if ($relay){
                        $remark=$row_res[17];
                    }        
                    else {
                        $remark=$row_res[22];  
                    }
                    if ($wind == '-' && $perf == 0 )  {
                         $wind = '';       
                    }
                    
                    if ($ukc){
                        $points = $pointsUKC;
                    }    
                    $list->printLine($rank, $name, $year, $row_res[8], $perf, $wind, $points, $qual, $ioc, $sb, $pb,$qual_mode,$athleteCat,$remark, '', $withStartnr, $row_res[25]);
                 
                    if($secondResult){
                        $list->printLine("","","","",$perf2,$wind2,"","","","","",$qual_mode,"","", $secondResult);
                    }
                    $perf_save=$row_res[3];// keep performance
                    // 
                    // if relay, show started ahtletes in right order under the result
                    //
                    if($relay){  
                               
                         if ($row_res[14] > 0)
                            $sqlRound=$row_res[14];     // merged round
                        else
                            $sqlRound=$row[0]; 
                                 
                        $res_at = mysql_query("
                                SELECT at.Vorname, at.Name, at.Jahrgang FROM
                                    staffelathlet as sfat
                                    LEFT JOIN start as st ON sfat.xAthletenstart = st.xStart
                                    LEFT JOIN anmeldung as a USING(xAnmeldung)
                                    LEFT JOIN athlet as at USING(xAthlet)
                                WHERE
                                    sfat.xStaffelstart = $row_res[11]
                                AND    sfat.xRunde = $sqlRound 
                                ORDER BY
                                    sfat.Position
                                LIMIT $row[10]
                        ");
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }else{
                            $text_at = "";
                            while($row_at = mysql_fetch_array($res_at)){
                                $text_at .= $row_at[1]." ".$row_at[0]." ".AA_formatYearOfBirth($row_at[2])." / ";
                            }
                            $text_at = substr($text_at, 0, (strlen($text_at)-2));
                            
                            
                            $text_at = (trim($text_at)!='') ? '('.$text_at.')' : '';
                            $list->printAthletesLine($text_at);
                        }
                    }
                    
                    // 
                    // if biglist, show all attempts
                    //
                    if($biglist){
                        
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                            || ($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                            || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow])
                            || ($row[3] == $cfgDisciplineType[$strDiscTypeHigh]))
                        {
                        
                        $query_sort = ($row[3]==$cfgDisciplineType[$strDiscTypeHigh]) ? "ORDER BY Leistung ASC": "ORDER BY xResultat ASC";
                            
                        $res_att = mysql_query("
                                SELECT * FROM 
                                    resultat 
                                WHERE xSerienstart = $row_res[0]
                                ".$query_sort."
                                "); 
                                                        
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }else{
                            $text_att = "";
                            while($row_att = mysql_fetch_array($res_att)){
                                if($row_att['Leistung'] < 0){
                                    $perf3 = $row_att['Leistung'];                                       
                                    if ($perf3 == $GLOBALS['cfgMissedAttempt']['db']){
                                       // $perf3 = '-';
                                        $perf3 = $GLOBALS['cfgMissedAttempt']['code'];
                                    }
                                    elseif  ($perf3 == $GLOBALS['cfgMissedAttempt']['dbx']){ 
                                             $perf3 = $GLOBALS['cfgMissedAttempt']['codeX'];  
                                    }
                                    foreach($cfgInvalidResult as $value)    // translate value
                                    {
                                        if($value['code'] == $perf3) {
                                            $text_att .= $value['short'];
                                        }
                                    }
                                    $text_att .= " / ";
                                }else{
                                    $text_att .= ($row_att['Leistung']=='-') ? '-' : AA_formatResultMeter($row_att['Leistung']);
                                    if ($saison == "O" ||  ($saison == "I"  && $row[3] != $cfgDisciplineType[$strDiscTypeJump])) {        // outdoor  or (indoor and not jump)
                                        if($row_att['Info'] != "-" && !empty($row_att['Info']) && $row[3] != $cfgDisciplineType[$strDiscTypeThrow]){
                                                
                                                if ($row[3] == $cfgDisciplineType[$strDiscTypeHigh]){
                                                    $text_att .= " , ".$row_att['Info'];  
                                                }
                                                else {
                                                     if ($row[8] != 0){
                                                        $text_att .= " , ".$row_att['Info'];   
                                                     } 
                                                }
                                            
                                        }
                                        elseif ($row_att['Info'] == "-"  && $row[3] != $cfgDisciplineType[$strDiscTypeThrow] && $row_att['Leistung'] > 0){
                                                 $text_att .= " , ".$row_att['Info'];  
                                        }  
                                    }                              
                                    $text_att .= " / ";
                                }   
                            }
                            $text_att = substr($text_att, 0, (strlen($text_att)-2));
                            
                            $list->printAthletesLine("$strAttempts: ( $text_att )");
                        }
                        
                        }
                    }
                }        // ET athlete processed

                $id = $row_res[0];                // keep current athletes ID
                if ($relay)
                     $catM = $row_res[16];      // keep merged category relay
                else
                    $catM = $row_res[19];       // keep merged category 
                
            }    // END WHILE result lines
            
       
            
            mysql_free_result($res);
            $list->endList();
        }    // ET DB error result rows   

        $cat = $row[1];    // keep category 
        $round_keep = $row[0];  
       
        
    }    // END WHILE event rounds
    mysql_free_result($results);
    
    if ($ukc && !$results_ukc){
        echo "<br><br><b><blockquote>$strErrNoResults</blockquote></b>";  
    }
    
          //************** rankinglist over all series 
           
            if ($ranklistAll) {      
                
                if (($catMerged & !$heatSeparate) || ($eventMerged & !$heatSeparate)) { 
                     // get event rounds from DB         
                    $results = mysql_query("
                        SELECT 
                            r.xRunde
                            , k.Name
                            , d.Name
                            , d.Typ
                            , w.xWettkampf
                            , r.QualifikationSieger
                            , r.QualifikationLeistung
                            , w.Punkteformel
                            , w.Windmessung
                            , r.Speakerstatus
                            , d.Staffellaeufer
                            , CONCAT(DATE_FORMAT(r.Datum,'$cfgDBdateFormat'), ' ', TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat'))
                            , w.xDisziplin  
                        FROM
                            wettkampf AS w
                            LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                              LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                              LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf) 
                        WHERE " . $selection . "
                        w.xMeeting = " . $_COOKIE['meeting_id'] . "     
                        AND r.Status = " . $cfgRoundStatus['results_done'] . " 
                        AND r.Datum LIKE '".$date."'
                        AND (d.Typ = " . $cfgDisciplineType[$strDiscTypeTrack] ." OR d.Typ = " . $cfgDisciplineType[$strDiscTypeTrackNoWind] . " 
                                OR d.Typ = " . $cfgDisciplineType[$strDiscTypeRelay] . " OR d.Typ = " . $cfgDisciplineType[$strDiscTypeDistance] .")             
                        ORDER BY
                            k.Anzeige
                            , d.Anzeige
                            , r.Datum
                            , r.Startzeit
                    ");   
                    
                }
                else {      
                     // heats separate
                       $results = mysql_query("
                            SELECT DISTINCT 
                                r.xRunde , 
                                k.Name , 
                                d.Name , 
                                d.Typ , 
                                w.xWettkampf , 
                                r.QualifikationSieger , 
                                r.QualifikationLeistung , 
                                w.Punkteformel , 
                                w.Windmessung , 
                                r.Speakerstatus , 
                                d.Staffellaeufer , 
                                CONCAT(DATE_FORMAT(r.Datum,'%d.%m.%y'), 
                                ' ', 
                                TIME_FORMAT(r.Startzeit, '%H:%i')) ,
                                w.xDisziplin ,  
                                rs.Hauptrunde     
                            FROM 
                                wettkampf AS w 
                                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie) 
                                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                                LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf) 
                                LEFT JOIN rundenset as rs ON (r.xRunde=rs.xRunde )           
                            WHERE 
                                " . $selection . "  
                                w.xMeeting  = " . $_COOKIE['meeting_id'] . " 
                                AND r.Status = 4  
                                AND r.Datum LIKE '%' 
                            ORDER BY
                                k.Anzeige
                                , d.Anzeige
                                , r.Datum
                                , r.Startzeit
                ");  
                
                }        
 
if(mysql_errno() > 0) {        // DB error             
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());  
}
else {
      
    $limitRankSQL = "";
    $limitRank = false;
    if($_GET['limitRank'] == "yes"){ // check if ranks are limited, but limitRankSQL will set only if export is pressed
        if(!empty($_GET['limitRankFrom']) && !empty($_GET['limitRankTo'])){
            $limitRank = true;
        }
    }
    
    // start a new HTML display page
    if(($formaction == 'view')
        ||    ($formaction == 'speaker')) {    // display page        
        $list->printPageTitle("$strRanklistAll " . $_COOKIE['meeting']);
    }
    // start a new HTML print page
    elseif($formaction == "print") {   
                
        $list->insertPageBreak();
        $list->printPageTitle("$strRanklistAll");                     
    }
        
    // initialize variables
    $cat = '';
    $evnt = 0;            
    
    if (mysql_num_rows($results) == 0) {
        echo "<br><br><b><blockquote>$strErrNoResults</blockquote></b>";
    }   
    
    while($row = mysql_fetch_row($results))
    {                  
        // for a combined event, the rounds are merged, so jump until the next event
        if($cRounds > 1){
            $cRounds--;                    
            continue;
        }        
        $roundSQL = "s.xRunde = $row[0]";  
        $cRounds = 0;
        
        // check page  break
        if((is_a($list, "PRINT_RankingList")  
			|| is_a($list, "PRINT_RankingList_pdf"))// page for printing
            && ($cat != '')                        // not first result row
            && (($break == 'discipline')    // page break after each discipline
                || (($break == 'category')    // or after new category
                    && ($row[1] != $cat))))
        {
            $list->insertPageBreak();
        }
        
        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                || ($row[3] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                || ($row[3] == $cfgDisciplineType[$strDiscTypeRelay]))
        {
            $eval = $cfgEvalType[$strEvalTypeHeat];
        }
        else
        {
            $eval = $cfgEvalType[$strEvalTypeAll];
        }

        $roundName = '';
        $type = '';
        $res = mysql_query("
            SELECT
                rt.Name
                , rt.Typ
                , rt.Wertung
            FROM
                runde
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON (rt.xRundentyp = runde.xRundentyp)
            WHERE 
                runde.xRunde = $row[0]");

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            if(mysql_num_rows($res) > 0) {
                $row_rt = mysql_fetch_row($res);
                
                if($row_rt[1] == '0'){
                    $type = " ";
                    $row_rt[0] = '';
                }else{
                    $type = $row_rt[0]." ";
                }
                
                $eval = $row_rt[2];
                if($round != 0) {        // specific round selected
                    $roundName = $row_rt[0];
                }
            }
            mysql_free_result($res);
        }
         
        $flagSubtitle=false;  // set flag to print the subtitle later    
         
        if($evnt != $row[4])        // new event -> repeat title
        {    
            
            // if this is a combined event, dont fragment list by rounds
            $combined = AA_checkCombined($row[4]);
            // not selectet a specific round
            if($round == 0 && $combined){
                
                $sql = "SELECT 
                                r.xRunde
                            FROM
                                wettkampf as w
                                LEFT JOIN runde as r ON (r.xWettkampf = w.xWettkampf)
                            WHERE    
                                w.xWettkampf = $row[4]
                                AND r.status = 4";     
               
                $res_c = mysql_query($sql);

                if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }else{
                    $cRounds = mysql_num_rows($res_c);                        
                    $roundSQL = "s.xRunde IN (";
                    while($row_c = mysql_fetch_array($res_c)){
                        $roundSQL .= $row_c[0].",";
                    }
                    $roundSQL = substr($roundSQL, 0, -1).")";
                }
            }
            
            // set up category and discipline title information         
            $flagSubtitle=true;       // set flag to print the subtitle later      
        
            if(($formaction == 'speaker')     // speaker display page
                && (AA_getNextRound($row[4], $row[0]) == 0))
            {
                // last round: show ceremony status
                $list->printCeremonyStatus($row[0], $row[9]);
            }

            // print qualification mode if round selected
            $info = '';
            if(($round > 0)
                && (($row[5] > 0) || ($row[6] > 0)))
            {
                $info = "$strQualification: "
                            . $row[5] . " $strQualifyTop, "
                            . $row[6] . " $strQualifyPerformance";
                $flagInfoLine1=true;         // set flag to print later the qualification mode if round selected  
                $info_save1=$info;
                //$list->printInfoLine($info);
                $qual_mode = TRUE;
            }                     
            // print qualification descriptions if required 
            $info = '';
            if(($row[5] > 0) || ($row[6] > 0))
            {
                foreach($cfgQualificationType as $qt)
                {
                    $info = $info . $qt['token'] . " ="
                            . $qt['text'] . "&nbsp;&nbsp;&nbsp;";
                }
                $flagInfoLine2=true;         // set flag to print later the qualification descriptions if required
                $info_save2=$info; 
                //$list->printInfoLine($info);
                $qual_mode = TRUE;
            }               
            $evnt = $row[4];    // keep event ID            
        } // ET new event
              
        $relay = AA_checkRelay($row[4]);    // check, if this is a relay event
        $svm = AA_checkSVM($row[4]);    
        
        // If round evaluated per heat, group results accordingly    
        $order_heat = "";  
        if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
            $order_heat = "heatid, ";
        }
       
        $valid_result ="";
        // Order performance depending on discipline type
        if(($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
            || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow]))
        {
            $order_perf = "DESC";
        }
        else if($row[3] == $cfgDisciplineType[$strDiscTypeJump])
        {
            if ($row[8] == 1) {            // with wind
                $order_perf = "DESC, r.Info ASC";
            }
            else {                            // without wind
                $order_perf = "DESC";
            }
        }
        else if($row[3] == $cfgDisciplineType[$strDiscTypeHigh])
        {
            $order_perf = "DESC";
            $valid_result =    " AND (r.Info LIKE '%O%'"
                                        . " OR r.Leistung < 0)";
        }
        else
        {
            $order_perf = "ASC";
        }
       
        $sqlSeparate='';    
        if (($catMerged || $eventMerged) & $heatSeparate) {   
            
             if ($row[0] > 0 && $row[13] != NULL) {  
                $roundSQL = '';  
                if (empty($limitRankSQL) && empty($valid_result)){
                        $sqlSeparate=" ss.RundeZusammen = " . $row[0];   
                } 
                else {
                     if (empty($limitRankSQL) ){
                           $valid_result = substr($valid_result,4, strlen($valid_result));
                     }
                     elseif (empty($valid_result) ){
                             $limitRankSQL = substr($limitRankSQL,4, strlen($limitRankSQL));   
                     }
                    $sqlSeparate=" AND ss.RundeZusammen = " . $row[0];   
                }  
             }  
        } 
        
        // get all results ordered by ranking; for invalid results (Rang=0), the
        // rank is set to max_rank to put them to the end of the list.
        $max_rank = 999999999;  
        $sql_leistung = ($order_perf=='ASC') ? "r.Leistung" : "IF(r.Leistung<0, (If(r.Leistung = -99, -9, (If (r.Leistung = -98, -8,r.Leistung))) * -1), r.Leistung)";        
        $sql_leistung_order = "IF(r.Leistung is NULL, 999999999  , (IF (r.Leistung < 0,999999999 - r.Leistung,  r.Leistung )))";       
        $order= $order_heat;          
        
        if($relay == FALSE) {                                 
                
                if ($athleteCat){
                    $order=$orderAthleteCat . $order_heat;   
                }
                $query = "SELECT ss.xSerienstart, 
                             IF(ss.Rang=0, $max_rank, ss.Rang) AS rank, 
                             ss.Qualifikation, 
                             ".$sql_leistung." AS leistung_neu, 
                             r.Info, 
                             s.Bezeichnung, 
                             s.Wind, 
                             r.Punkte, 
                             IF('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)), 
                             at.Name, 
                             at.Vorname, 
                             at.Jahrgang, 
                             LPAD(s.Bezeichnung, 5, '0') AS heatid, 
                             IF(at.xRegion = 0, at.Land, re.Anzeige) AS Land, 
                             at.xAthlet, 
                             ru.Datum, 
                             ru.Startzeit ,
                             ss.RundeZusammen,
                             ru.xRunde,  
                             k.Name , 
                             k1.Name ,                             
                             k1.Anzeige ,
                             ss.Bemerkung,
                             w.Punkteformel,
                             w.info,
                             a.Startnummer,
                             ".$sql_leistung_order." AS leistung_order, 
                             r.Leistung
                        FROM serie AS s USE INDEX(Runde)
                   LEFT JOIN serienstart AS ss USING(xSerie) 
                   LEFT JOIN resultat AS r USING(xSerienstart) 
                   LEFT JOIN start AS st ON(ss.xStart = st.xStart) 
                   LEFT JOIN anmeldung AS a USING(xAnmeldung) 
                   LEFT JOIN athlet AS at USING(xAthlet) 
                   LEFT JOIN verein AS v USING(xVerein) 
                   LEFT JOIN region AS re ON(at.xRegion = re.xRegion) 
                   LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                   LEFT JOIN runde AS ru ON(s.xRunde = ru.xRunde) 
                   LEFT JOIN wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                   LEFT JOIN kategorie AS k On (w.xKategorie= k.xKategorie)
                   LEFT JOIN kategorie AS k1 ON (a.xKategorie = k1.xKategorie)   
                       WHERE ".$roundSQL." 
                       ".$limitRankSQL." 
                       ".$valid_result." 
                       ".$sqlSeparate." 
                       ".$selectionHeats."  
                    ORDER BY leistung_order " 
                             .$order_perf;  
                  
        }
        else {                        // relay event
                                
            $query = "SELECT ss.xSerienstart,                                   
                             IF(r.Leistung < 0 , $max_rank, if (ss.Rang=0, $max_rank-1, ss.Rang)) AS rank, 
                             ss.Qualifikation, 
                             ".$sql_leistung." AS leistung_neu, 
                             r.Info, 
                             s.Bezeichnung, 
                             s.Wind, 
                             r.Punkte, 
                             IF('".$svm."', t.Name, v.Name), 
                             sf.Name, 
                             LPAD(s.Bezeichnung, 5, '0') AS heatid, 
                             st.xStart, 
                             ru.Datum, 
                             ru.Startzeit, 
                             ss.RundeZusammen,
                             ru.xRunde,
                             k.Name ,
                             ss.Bemerkung    
                        FROM serie AS s USE INDEX(Runde) 
                   LEFT JOIN serienstart AS ss USING(xSerie) 
                   LEFT JOIN resultat AS r USING(xSerienstart) 
                   LEFT JOIN start AS st ON(ss.xStart = st.xStart) 
                   LEFT JOIN staffel AS sf USING(xStaffel) 
                   LEFT JOIN verein AS v USING(xVerein) 
                   LEFT JOIN team AS t ON(sf.xTeam = t.xTeam) 
                   LEFT JOIN runde AS ru ON(s.xRunde = ru.xRunde) 
                   LEFT JOIN wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                   LEFT JOIN kategorie AS k On (w.xKategorie= k.xKategorie) 
                       WHERE ".$roundSQL." 
                      ".$limitRankSQL." 
                      ".$valid_result." 
                      ".$sqlSeparate."                         
                    GROUP BY r.xSerienstart 
                    ORDER BY ".$order." 
                             rank, 
                             r.Leistung 
                             ".$order_perf.", 
                             sf.Name;";   
                 
        }    
        
        $res = mysql_query($query);
        if(mysql_errno() > 0) {        // DB error   
       
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
              if (mysql_num_rows($res)==0){                          
                    continue;             
              }  
            // initialize variables
            $heat = '';
            $h = 0;
            $info = '';
            $id = '';
            $r = '';
            $count_rank=0;
            $perf_save = '';                
            $nr = 0;
            
            if ($formaction == 'view') {
                $list->startList();   
            }
            $atCat = '';        
           
                // process every result
            while($row_res = mysql_fetch_array($res))
            {   
                if ($flagSubtitle ){     
                    $nr = 0;    
                    if ($heatSeparate) {
                        if ($relay) {       
                            $list->printSubTitle($row_res[16], $row2_keep, $roundName, $row_res[24]);                                
                        }
                        else {
                             if (!$athleteCat){  
                                  $list->printSubTitle($row_res[19], $row2_keep, $roundName, $row_res[24]);                                 
                             } 
                        }  
                    }
                    else {
                        if (!$athleteCat){  
                           if ($formaction == 'print') {
                                 $list->printSubTitle($row[1], $row[2], $roundName, $row_res[24]); 
                           }
                           else {                                              
                                $list->printSubTitle($row[1], $row[2], $roundName, $row_res[24]);   
                           }
                            
                        }   
                    }                       
                    $flagSubtitle=false; 
                
                if ($formaction == 'print')   {
                      $list->startList();   
                } 
                    
             
                }
                  
                /*
                    if (!$athleteCat){ 
                        if ($flagInfoLine1){   
                            $list->printInfoLine($info_save1);
                            $flagInfoLine1=false;  
                        }
                    }
                    if (!$athleteCat){ 
                         if ($flagInfoLine2){  
                            $list->printInfoLine($info_save2);
                            $flagInfoLine2=false;  
                        }
                    } 
                */
                
                $row_res[3] = ($row_res[3]==1 || $row_res[3]==2 || $row_res[3]==3 || $row_res[3]==4) ? ($row_res[3] * -1) : (($row_res[3]==9) ? -99 : (($row_res[3]==8) ? -98 : $row_res[3]));                                                                                             
                            
                $nr=0;
                        
                $title = trim($title);     
                 
                if($row_res[0] != $id)    // athlete not processed yet
                {  
                    if($h == 0)                       // first header line or ...
                       // || (($row_res[5] != $heat) // new header after each heat
                       //     && ($eval == $cfgEvalType[$strEvalTypeHeat])) || ($athleteCat && ($row_res[21] != $atCat)))
                    {  
                        $count_rank=0;
                        $nr=0;
                        
                        // heat name                           
                        if($eval == $cfgEvalType[$strEvalTypeHeat]) {
                            if(empty($type))    {            // no round type defined
                                $type = $strFinalround . " ";
                                
                            }                                        
                            $title = $type;    // heat name withour nbr.  
                             
                        }
                        else {
                            $title = $type;    // heat name withour nbr.
                             
                        }
                        
                        $title = trim($title);
                         
                        // wind per heat
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                                && ($row[8] == 1)
                                && ($eval == $cfgEvalType[$strEvalTypeHeat]))
                        {
                            $heatwind = $row_res[6];        // wind per heat
                        }
                        else {
                            $heatwind = '';                    // no wind 
                        }

                        $wind= FALSE;
                        if(($row[8] == 1) 
                            && ($row[3] == $cfgDisciplineType[$strDiscTypeJump]) 
                            || (($row[3] == $cfgDisciplineType[$strDiscTypeTrack]) 
                                && ($eval == $cfgEvalType[$strEvalTypeAll])))
                        {
                            $wind= TRUE;
                        }

                        // add column header 'points' if required
                        $points= FALSE;
                        if($row[7] != '0' || $row_res[23] != '0') {
                            $points= TRUE;
                        }
                       
                        if ($show_efforts == 'sb_pb'){
                            $base_perf = true;
                        } 
                     
                        if ($athleteCat && !$relay){                         
                          if($formaction == 'print') {   
                                if ($row_res[20].$row[2]!=$atCatName){                        
                                       $list->printSubTitle($row_res[20], $row2_keep, $roundName, $row_res[24]);  
                                       $atCatName=$row_res[20].$row[2];                                          
                                       if ($flagInfoLine1){   
                                        $list->printInfoLine($info_save1);
                                        $flagInfoLine1=false;  
                                    } 
                                    if ($flagInfoLine2){   
                                        $list->printInfoLine($info_save2);
                                        $flagInfoLine2=false;  
                                    } 
                                }  
                              }                            
                           }     
                       
                       //  $list->startList();  
                            
                        if ($saison == "I"){
                            $heatwind = '';
                        }
                        
                      
                       $list->printHeaderLine($title, $relay, $points, $wind, $heatwind, $row[11], $svm, $base_perf, $qual_mode, $eval, $withStartnr);
                      
                          
                         if ($athleteCat && !$relay){                         
                            if($formaction == 'view') {
                                 if ($row_res[20].$row[2]!=$atCatName){                        
                                           $list->printSubTitle($row_res[20], $row[2], $roundName, $row_res[24]);                                                  
                                           $atCatName=$row_res[20].$row[2];                                             
                                           if ($flagInfoLine1){   
                                            $list->printInfoLine($info_save1,$athleteCat);
                                            $flagInfoLine1=false;  
                                        } 
                                          if ($flagInfoLine2){   
                                              $list->printInfoLine($info_save2,$athleteCat);
                                              $flagInfoLine2=false;  
                                          }
                                                    
                                    }                          
                            }
                         }
                                                              
                        $heat = $row_res[5];            // keep heat description
                        $atCat = $row_res[21];             // keep athlete category                          
                        $h++;                        // increment if evaluation per heat
                    } 
                     
                    $count_rank++;
                   
                    // rank
                    if(($row_res[1]==$max_rank || $row_res[1]==$max_rank-1)         // invalid result
                        || ($r == $row_res[1] && $heat_keep == $row_res[5])) {        // same rank as previous
                        $rank='';
                    }
                    else {
                        $rank= $row_res[1];
                    }
                    $r= $row_res[1];                // keep rank
                    $heat_keep= $row_res[5];        // keep rank   
                   

                    // name
                    $name = $row_res[9];
                    if($relay == FALSE) {
                        $name = $name . " " . $row_res[10];
                    }

                    // year of birth
                    if($relay == FALSE) {
                        $year = AA_formatYearOfBirth($row_res[11]);
                    }
                    else {
                        $year = '';
                    }
                    
                    // year of birth
                    if($relay == FALSE) {
                        $land = ($row_res[13]!='' && $row_res[13]!='-') ? $row_res[13] : '';
                    }
                    else {
                        $year = '';
                    }

                    // performance                      
                    if($row_res[3] < 0) {    // invalid result
                        if ($row_res[3] == '-98'){                           
                            $perf = $cfgInvalidResult['NAA']['code'];                             
                        }
                        elseif ($row_res[3] == '-99'){                           
                            $perf = $cfgInvalidResult['WAI']['short']; 
                       }
                        else {
                            foreach($cfgInvalidResult as $value)    // translate value
                                {   
                                if($value['code'] == $row_res[3]) {
                                    $perf = $value['short'];                                       
                                }
                            }
                        }
                    }                    
                    else if(($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeHigh])) {
                        $perf = AA_formatResultMeter($row_res[3]);
                    }
                    else {
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                        || ($row[3] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                            $perf = AA_formatResultTime($row_res[3], true, true);
                        }else{
                            $perf = AA_formatResultTime($row_res[3], true);
                        }
                    }

                    $qual = '';
                    if($row_res[2] > 0) {    // Athlete qualified
                        foreach($cfgQualificationType as $qtype)
                        {
                            if($qtype['code'] == $row_res[2]) {
                                $qual = $qtype['token'];
                            }
                        }
                    }    // ET athlete qualified

                    // points for performance
                    $points = '';
                    if($row[7] != '0') {
                        $points = $row_res[7];
                    }
                    else {
                        if($row_res[23] != '0') {
                            $points = $row_res[7];
                        }
                    }
                    
                    
                    // wind info
                    $wind = '';
                    $secondResult = false;                   
                   
                    if($r != $max_rank)     // valid result
                    {                                    
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                            && ($row[8] == 1))
                        {
                            $wind = $row_res[4];
                           
                            if ($saison == 'I'){  
                                $wind = '';           // indoor: never wind  
                            }                                    
                           
                            
                            //
                            // if wind bigger than max wind (2.0) show the next best result without wind too
                            //
                            if($wind > 2){
                                $res_wind = mysql_query("
                                        SELECT Info, Leistung FROM
                                            resultat
                                        WHERE
                                            xSerienstart = $row_res[0]
                                        ORDER BY
                                            Leistung ASC");
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }else{
                                    while($row_wind = mysql_fetch_array($res_wind)){
                                        
                                        if($row_wind[0] <= 2){
                                            $secondResult = true;
                                            $wind2 = $row_wind[0].")";
                                            $perf2 = "(".AA_formatResultMeter($row_wind[1]);
                                        }
                                        
                                    }
                                }
                            }
                            
                            
                        }
                        else if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                            && ($row[8] == 1)
                            && ($eval == $cfgEvalType[$strEvalTypeAll])) 
                        {
                            $wind = $row_res[6];
                            
                            if ($saison == 'I'){  
                                $wind = '';           // indoor: never wind  
                            }                                    
                        }
                    }
                    
                    
                    // ioc country code
                    $ioc = '';
                    if($relay == false){
                        $ioc = $row_res[13];
                    }
                    
                    //show performances from base
                    if($show_efforts == 'sb_pb' && $relay == false){
                                                                                               
                        $sql = "SELECT 
                                    season_effort
                                    , DATE_FORMAT(season_effort_date, '%d.%m.%Y') AS sb_date
                                    , season_effort_event
                                    , best_effort
                                    , DATE_FORMAT(best_effort_date, '%d.%m.%Y') AS pb_date
                                    , best_effort_event
                                    , season
                                    , xAnmeldung
                        FROM 
                            base_performance
                        LEFT JOIN 
                            base_athlete USING (id_athlete)
                        LEFT JOIN 
                            disziplin_" . $_COOKIE['language'] . " ON (discipline = Code)
                        LEFT JOIN 
                            athlet ON (license = Lizenznummer)
                        LEFT JOIN
                            anmeldung USING(xAthlet) 
                        WHERE 
                            athlet.xAthlet = $row_res[14]
                            AND xDisziplin = $row[12]
                            AND season = '$saison' 
                            AND xMeeting = ".$_COOKIE['meeting_id'].";";
                        $res_perf = mysql_query($sql);
                        
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }else{
                            if ($res_perf){
                                $row_perf = mysql_fetch_array($res_perf);
                            
                                $is_jump = (($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeHigh]));
                                $order = ($is_jump) ? 'DESC' : 'ASC';
                                
                                $best_previous = '';    
                                $previous_date = '';                            
                                if($row_perf!==false){
                                    $best_previous = AA_getBestPrevious($row[12], $row_perf['xAnmeldung'], $order, $row_res['Datum'], $row_res['Startzeit'], &$previous_date);
                                }
                                
                                if($is_jump) {
                                    $sb_perf = AA_formatResultMeter(str_replace(".", "", $row_perf['season_effort']));
                                    $pb_perf = AA_formatResultMeter(str_replace(".", "", $row_perf['best_effort']));
                                    $bp_perf = AA_formatResultMeter(str_replace(".", "", $best_previous));
                                    
                                    if($bp_perf>0 && $bp_perf>$sb_perf){
                                        $sb_perf = $bp_perf;
                                        $row_perf['season_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                        $row_perf['sb_date'] = date('d.m.Y', strtotime($previous_date));
                                    }
                                    
                                    if($bp_perf>0 && $bp_perf>$pb_perf){
                                        $pb_perf = $bp_perf;
                                        $row_perf['best_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                        $row_perf['pb_date'] = date('d.m.Y', strtotime($previous_date));
                                    }
                                    
                                    //highlight sb or pb if new performance is better
                                    if (is_numeric($perf)){ //prevent special-codes (disq, n.a. usw)
                                        if ($formaction!='print'){
                                            if ($pb_perf!='' && $perf>$pb_perf){
                                                $perf = "<b>PB $perf</b> ";
                                            } else {
                                                if ($sb_perf!='' && $perf>$sb_perf){
                                                    $perf = "<b>SB $perf</b>";
                                                }
                                            }                                        
                                        } else {
                                            if ($pb_perf!='' && $perf>$pb_perf){
                                                $perf = "<b>PB</b> $perf";
                                            } else {
                                                if ($sb_perf!='' && $perf>$sb_perf){
                                                    $perf = "<b>SB</b> $perf";
                                                }
                                            }                                        
                                        }
                                    }

                                } else {
                                    //convert performance-time to milliseconds
                                    $timepices = explode(":", $row_perf['season_effort']);
                                    $season_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) +($timepices[2] *  1000) + ($timepices[3]);
                                    $timepices = explode(":", $row_perf['best_effort']);
                                    $best_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) +($timepices[2] *  1000) + ($timepices[3]);
                                    $previous_effort = intval($best_previous);
                                    
                                    if($previous_effort>0 && $previous_effort<$season_effort){
                                        $season_effort = $previous_effort;
                                        $row_perf['season_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                        $row_perf['sb_date'] = date('d.m.Y', strtotime($previous_date));
                                    }
                                    
                                    if($previous_effort>0 && $previous_effort<$best_effort){
                                        $best_effort = $previous_effort;
                                        $row_perf['best_effort_event'] = $_SESSION['meeting_infos']['Name'];
                                        $row_perf['pb_date'] = date('d.m.Y', strtotime($previous_date));
                                    }
                                    
                                    if(($row[3] == $cfgDisciplineType[$strDiscTypeTrack])
                                    || ($row[3] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                                        $sb_perf = AA_formatResultTime($season_effort, true, true);
                                        $pb_perf = AA_formatResultTime($best_effort, true, true);
                                    }else{
                                        $sb_perf = AA_formatResultTime($season_effort, true);
                                        $pb_perf = AA_formatResultTime($best_effort, true);
                                    }
                                    if ($formaction!='print'){
                                        //highlight sb or pb if new performance is better
                                        if ($pb_perf!='' && $perf<$pb_perf){
                                            $perf = "<b>PB $perf</b>";
                                        } else {
                                            if ($sb_perf!='' && $perf<$sb_perf){
                                                $perf = "<b>SB $perf</b>";
                                            }
                                        }
                                    } else {
                                        if ($pb_perf!='' && $perf<$pb_perf){
                                            $perf = "<b>PB</b> $perf";
                                        } else {
                                            if ($sb_perf!='' && $perf<$sb_perf){
                                                $perf = "<b>SB</b> $perf";
                                            }
                                        }
                                    }
                                }
                                
                                if (!empty($row_perf['season_effort'])){
                                    $sb = "<a href=\"#\" class=\"info\">$sb_perf<span>$row_perf[sb_date]<br>$row_perf[season_effort_event]</span></a>";
                                } else {
                                    $sb = "&nbsp;";
                                }
                                
                                if (!empty($row_perf['best_effort'])){
                                    $pb = "<a href=\"#\" class=\"info\">$pb_perf<span>$row_perf[pb_date]<br>$row_perf[best_effort_event]</span></a>";
                                } else {
                                    $pb = "&nbsp;";
                                }
                            }        
                        }        
                    }
                    if ($heatSeparate && $row_res[17] > 0) {    
                         $rank=$count_rank;                             
                         if ( $row_res[3] < 0)        // invalid result 
                             $rank='';  
                    }    
                    
                    if ($athleteCat && !$relay){
                        $nr++;                                 
                        if ($rank!='') {
                            if ($formaction == "print") {
                                $rank=$nr.". (".$rank.")"; 
                            }
                            else {
                                $rank=$nr." (".$rank.")"; 
                            }   
                        }          
                    } 
                    else { if ($formaction == "print") {
                                if ($rank!='') {
                                    $rank.=".";
                                }
                           }
                    }
                    if ($relay){
                        $remark=$row_res[17];
                    }        
                    else {
                        $remark=$row_res[22];  
                    }
                    if ($wind == '-' && $perf == 0 )  {
                         $wind = '';       
                    }
                    
                    if ($row_res[1] == 999999999){
                     $count_show = '';   
                    }
                    else {
                          $count_show = $count_rank;   
                    }
                    
                    $list->printLine($count_show, $name, $year, $row_res[8], $perf, $wind, $points, $qual, $ioc, $sb, $pb,$qual_mode,$athleteCat,$remark, '', $withStartnr, $row_res[25]);
                   
                    if($secondResult){
                        $list->printLine("","","","",$perf2,$wind2,"","","","","",$qual_mode,"","", $secondResult);
                    }
                    $perf_save=$row_res[3];// keep performance
                    // 
                    // if relay, show started ahtletes in right order under the result
                    //
                    if($relay){  
                               
                         if ($row_res[14] > 0)
                            $sqlRound=$row_res[14];     // merged round
                        else
                            $sqlRound=$row[0]; 
                                 
                        $res_at = mysql_query("
                                SELECT at.Vorname, at.Name, at.Jahrgang FROM
                                    staffelathlet as sfat
                                    LEFT JOIN start as st ON sfat.xAthletenstart = st.xStart
                                    LEFT JOIN anmeldung as a USING(xAnmeldung)
                                    LEFT JOIN athlet as at USING(xAthlet)
                                WHERE
                                    sfat.xStaffelstart = $row_res[11]
                                AND    sfat.xRunde = $sqlRound 
                                ORDER BY
                                    sfat.Position
                                LIMIT $row[10]
                        ");
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }else{
                            $text_at = "";
                            while($row_at = mysql_fetch_array($res_at)){
                                $text_at .= $row_at[1]." ".$row_at[0]." ".AA_formatYearOfBirth($row_at[2])." / ";
                            }
                            $text_at = substr($text_at, 0, (strlen($text_at)-2));
                            
                            
                            $text_at = (trim($text_at)!='') ? '('.$text_at.')' : '';
                            $list->printAthletesLine($text_at);
                        }
                    }
                    
                    // 
                    // if biglist, show all attempts
                    //
                    if($biglist){
                        
                        if(($row[3] == $cfgDisciplineType[$strDiscTypeJump])
                            || ($row[3] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                            || ($row[3] == $cfgDisciplineType[$strDiscTypeThrow])
                            || ($row[3] == $cfgDisciplineType[$strDiscTypeHigh]))
                        {
                        
                        $query_sort = ($row[3]==$cfgDisciplineType[$strDiscTypeHigh]) ? "ORDER BY Leistung ASC": "ORDER BY xResultat ASC";
                            
                        $res_att = mysql_query("
                                SELECT * FROM 
                                    resultat 
                                WHERE xSerienstart = $row_res[0]
                                ".$query_sort."
                                "); 
                                                        
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }else{
                            $text_att = "";
                            while($row_att = mysql_fetch_array($res_att)){
                                if($row_att['Leistung'] < 0){
                                    $perf3 = $row_att['Leistung'];                                       
                                    if ($perf3 == $GLOBALS['cfgMissedAttempt']['db']){
                                       // $perf3 = '-';
                                        $perf3 = $GLOBALS['cfgMissedAttempt']['code'];
                                    }
                                    elseif  ($perf3 == $GLOBALS['cfgMissedAttempt']['dbx']){ 
                                             $perf3 = $GLOBALS['cfgMissedAttempt']['codeX'];  
                                    }
                                    foreach($cfgInvalidResult as $value)    // translate value
                                    {
                                        if($value['code'] == $perf3) {
                                            $text_att .= $value['short'];
                                        }
                                    }
                                    $text_att .= " / ";
                                }else{
                                    $text_att .= ($row_att['Leistung']=='-') ? '-' : AA_formatResultMeter($row_att['Leistung']);
                                    if ($saison == "O" ||  ($saison == "I"  && $row[3] != $cfgDisciplineType[$strDiscTypeJump])) {        // outdoor  or (indoor and not jump)
                                        if($row_att['Info'] != "-" && !empty($row_att['Info']) && $row[3] != $cfgDisciplineType[$strDiscTypeThrow]){
                                                
                                                if ($row[3] == $cfgDisciplineType[$strDiscTypeHigh]){
                                                    $text_att .= " , ".$row_att['Info'];  
                                                }
                                                else {
                                                     if ($row[8] != 0){
                                                        $text_att .= " , ".$row_att['Info'];   
                                                     } 
                                                }
                                            
                                        }
                                        elseif ($row_att['Info'] == "-"  && $row[3] != $cfgDisciplineType[$strDiscTypeThrow] && $row_att['Leistung'] > 0){
                                                 $text_att .= " , ".$row_att['Info'];  
                                        }  
                                    }                              
                                    $text_att .= " / ";
                                }   
                            }
                            $text_att = substr($text_att, 0, (strlen($text_att)-2));
                            
                            $list->printAthletesLine("$strAttempts: ( $text_att )");
                        }
                        
                        }
                    }
                }        // ET athlete processed

                $id = $row_res[0];                // keep current athletes ID
                if ($relay)
                     $catM = $row_res[16];      // keep merged category relay
                else
                    $catM = $row_res[19];       // keep merged category   
                    
                    
                $info_save1_keep  = $info_save1;
                $info_save2_keep  = $info_save2;     
                 
            }    // END WHILE result lines
              
            }
         $row1_keep = $row[1];   
         $row2_keep = $row[2];     
        
            
    } 
    
}
$list->endList();       
            }
            
            //*******************************
  if ($ukc){
         AA_rankinglist_Combined($category, $formaction, $break, $cover, $sepu23, $cover_timing, $date, $disc_nr,$catFrom,$catTo, $ukc);   
  }   
    

    $list->endPage();    // end HTML page for printing
} // ET DB error event rounds


}    // end function AA_rankinglist_Single

}    // AA_RANKINGLIST_SINGLE_LIB_INCLUDED
?>
