<?php

/**********
 *
 *    print_contest_speaker.php
 *    -----------------
 *    
 */

require('./lib/common.lib.php');
require('./lib/cl_print_contest.lib.php');
require('./lib/cl_print_contest_pdf.lib.php');
require('./lib/cl_print_contest_speaker_pdf.lib.php');
require('./lib/timing.lib.php');

if(AA_connectToDB() == FALSE)    // invalid DB connection
{
    return;
}

if(AA_checkMeetingID() == FALSE) {        // no meeting selected
    return;        // abort
}

  
  
$onlyBest = 'n';     

   
// get presets
// -----------
$round = 0;
if(!empty($_GET['round'])) {
    $round = $_GET['round'];
}


if(!empty($GLOBALS['AA_ERROR'])) {
    AA_printErrorMsg($GLOBALS['AA_ERROR']);
}

//
// get nbr of heats
// ----------------
$res = mysql_query("SELECT COUNT(*)"
                        . " FROM serie"
                        . " WHERE xRunde = $round");

if(mysql_errno() > 0) {        // DB error
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else {
    $row = mysql_fetch_row($res);
    $tot_heats = $row[0];
}
mysql_free_result($res);            

//
// Content
// -------
$saison = $_SESSION['meeting_infos']['Saison'];
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
                , d.Code
                , k.Alterslimite
                , k.Geschlecht
                , k.Code
                , k.Kurzname
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

if(mysql_errno() > 0)        // DB error
{
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{
    
    $combined = AA_checkCombined(0, $round);
    $svm = AA_checkSVM(0, $round); // decide whether to show club or team name   
    
    if (!empty($mRounds)){   
        $infoMerged = "";     
        while ($row = mysql_fetch_row($result)) {
            $catMerged .= $row[5]. " / ";
            if($row[9] <> "") $infoMerged .= $row[9]. " / ";
        }   
        $titel = substr($catMerged,0,-2); 
        $infoMerged = substr($infoMerged,0,-2); 
    }
    
    $result = mysql_query($sql);  
    $row = mysql_fetch_row($result);
    
    $agelimit = $row[19];
    $sex = $row[20];
    
    $category_short = $row[22]; 
    
    // remember staffell?ufer
    $maxRunners = $row[11];
    $discipline_id = $row[18];
    
    $xWettkampf = $row[4];   
    $round_temp = $round;
    $r = 0;
    while(AA_getNextRound($row[4], $round_temp) > 0){
        $round_temp = AA_getNextRound($row[4], $round_temp);
        $round_following[$r] = $round_temp;
        $r++;
        
    }
    
    $next_round = $round_following[0];
    

    $relay = AA_checkRelay($row[4]);    // check, if this is a relay event
    
    $layout = $row[7];            // sheet layout type
    
    $silent = ($row[13]==0);
    $wind=$row[8];
    
    if(!$combined) {

        switch($layout) {
            case($cfgDisciplineType[$strDiscTypeNone]):
                $doc = new PRINT_Contest_speaker_pdf($_COOKIE['meeting']);
            case($cfgDisciplineType[$strDiscTypeTrack]):
                if($row[8] == 1) {
                    $doc = new PRINT_ContestTrack_speaker_pdf($_COOKIE['meeting'],false, false, false, false);
                }
                else {
                    $doc = new PRINT_ContestTrack_speaker_pdf($_COOKIE['meeting'],false, false, false, false);
                }
                break;
            case($cfgDisciplineType[$strDiscTypeTrackNoWind]):
            case($cfgDisciplineType[$strDiscTypeDistance]):
                $doc = new PRINT_ContestTrack_speaker_pdf($_COOKIE['meeting'],false, false, false, false);
                break;
            case($cfgDisciplineType[$strDiscTypeRelay]):
                $doc = new PRINT_ContestRelay_pdf($_COOKIE['meeting']);
                break;
            case($cfgDisciplineType[$strDiscTypeJump]):
                if($row[8] == 1) {
                    $doc = new PRINT_ContestTech_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                }
                else {
                    $doc = new PRINT_ContestTech_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                }
                break;
            case($cfgDisciplineType[$strDiscTypeJumpNoWind]):
                $doc = new PRINT_ContestTech_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                break;
            case($cfgDisciplineType[$strDiscTypeThrow]):
                $doc = new PRINT_ContestTech_speaker_pdf($_COOKIE['meeting'],false ,false, false, false);
                break;
            case($cfgDisciplineType[$strDiscTypeHigh]):
                $doc = new PRINT_ContestTech_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                break;
        } 
    } else {
        switch($layout) {
            case($cfgDisciplineType[$strDiscTypeNone]):
                $doc = new PRINT_Contest_pdf($_COOKIE['meeting']);
            case($cfgDisciplineType[$strDiscTypeTrack]):
                if($row[8] == 1) {
                    $doc = new PRINT_ContestTrackCombined_speaker_pdf($_COOKIE['meeting'],false, false, false, false);
                }
                else {
                    $doc = new PRINT_ContestTrackCombined_speaker_pdf($_COOKIE['meeting'],false, false, false, false);
                }
                break;
            case($cfgDisciplineType[$strDiscTypeTrackNoWind]):
            case($cfgDisciplineType[$strDiscTypeDistance]):
                $doc = new PRINT_ContestTrackCombined_speaker_pdf($_COOKIE['meeting'],false, false, false, false);
                break;
            case($cfgDisciplineType[$strDiscTypeRelay]):
                $doc = new PRINT_ContestRelay_pdf($_COOKIE['meeting']);
                break;
            case($cfgDisciplineType[$strDiscTypeJump]):
                if($row[8] == 1) {
                    $doc = new PRINT_ContestTechCombined_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                }
                else {
                    $doc = new PRINT_ContestTechCombined_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                }
                break;
            case($cfgDisciplineType[$strDiscTypeJumpNoWind]):
                $doc = new PRINT_ContestTechCombined_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                break;
            case($cfgDisciplineType[$strDiscTypeThrow]):
                $doc = new PRINT_ContestTechCombined_speaker_pdf($_COOKIE['meeting'],false ,false, false, false);
                break;
            case($cfgDisciplineType[$strDiscTypeHigh]):
                $doc = new PRINT_ContestTechCombined_speaker_pdf($_COOKIE['meeting'], false, false, false, false );
                break;
        }
        
    }  
   
    if (empty($mRounds)){
        $doc->cat = "$row[5]";   
    }
    else {  
         $doc->cat = "$titel";  
    }  
    
    $catcode = $row[21];      
    
    if($row[10] == '0'){ // do not show "(ohne)"
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
    
    
    $et = '';    // set enrolement and manipulation time
    if($row[14] != '00:00'){
        $et = "($strEnrolementTime $row[14]";
    }
    if($row[15] != '00:00'){
        $et .= ", $strManipulationTime $row[15]";
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
        $res_qual = mysql_query("SELECT r.QualifikationSieger"
                            . ", r.QualifikationLeistung"
                                        . " FROM runde AS r"
                                        . " WHERE r.xRunde = $round");

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            $row_qual = mysql_fetch_row($res_qual);
            $qual_top =  $row_qual[0];
            $qual_perf =  $row_qual[1];
        }
            
        mysql_free_result($res_qual);
        
        
        $res = mysql_query("SELECT rt.Name"
                            . ", r.QualifikationSieger"
                            . ", r.QualifikationLeistung"
                            . ", DATE_FORMAT(r.Datum, '$cfgDBdateFormat')"
                            . ", TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')" 
                                        . " FROM runde AS r"
                                        . " LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt"
                                        . " ON r.xRundentyp = rt.xRundentyp"
                                        . " WHERE r.xRunde = $next_round");

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {

            $row = mysql_fetch_row($res);
            $qual_info = "/&nbsp;"
                            . "$strQualification $row[0]:"
                            . " $qual_top $strQualifyTop + "
                            . " $qual_perf $strQualifyPerformance";
        }
        mysql_free_result($res);
        
        $rounds_info = "";
        
        mysql_query("SET lc_time_names = 'de_DE'");
        
        foreach($round_following as $round_temp){
            $res = mysql_query("SELECT rt.Name"
                                . ", r.QualifikationSieger"
                                . ", r.QualifikationLeistung"
                                . ", DATE_FORMAT(r.Datum, '$cfgDBdateFormat')"
                                . ", TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')"
                                . ", DAYNAME(r.DAtum)"
                                            . " FROM runde AS r"
                                            . " LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt"
                                            . " ON r.xRundentyp = rt.xRundentyp"
                                            . " WHERE r.xRunde = $round_temp");

            if(mysql_errno() > 0)        // DB error
            {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {
                $row = mysql_fetch_row($res);
                if($rounds_info != "") {
                    $rounds_info .= " / ";
                } 
                $day_temp = substr($row[5],0,2);
                $rounds_info .= "$row[0]: $day_temp, $row[4]";
            }
            mysql_free_result($res);
        }
        
        $doc->setFollowing($rounds_info);
              
        
        $count == 0;
        
        
        
    }        // ET DB error
    
        $prev_rnd = 0;
        $prev_rnd_name = "";
        $sql_prev = "SELECT 
                      r.xRunde
                    , rt.Name 
                    , rt.Typ
                FROM 
                    runde AS r 
                LEFT JOIN 
                    rundentyp_" . $_COOKIE['language'] . " AS rt 
                    USING(xRundentyp) 
                WHERE r.xWettkampf = ".$xWettkampf." 
                ORDER BY 
                    r.Datum ASC, 
                    r.Startzeit ASC;";
        $res_prev = mysql_query($sql_prev);
        
        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            $tot_rounds = mysql_num_rows($res_prev);        // keep total nbr of rounds
            while ($row_prev = mysql_fetch_row($res_prev))
            {      
                $count++;
                if($row_prev[0] == $round)    {        // actual round found
                    break;    // terminate loop
                }
                $prev_rnd = $row_prev[0];            // keep round ID for further processing
                $prev_rnd_name = $row_prev[2];    // keep round Name for further processing
            }
            mysql_free_result($res_prev);
            $final = FALSE;
            $quali = TRUE;
            if($tot_rounds == $count) {    // final round)
                if ($row_prev[2] == 'S' || $row_prev[2] == 'O'){          // round typ: S = Serie ,  O = ohne 
                   $quali = FALSE;
                }
                else {
                    $final = TRUE;
                }
            }
        }

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
        $order2 = ($_GET['ranking'] == 1) ? "Rang_Sort ASC" : "ss.Position";
        // display all heats
        if($relay == FALSE) {        // single event
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
                            , at.xAthlet  
                            , IF(ss.Rang > 0, ss.Rang, '') As Rang
                            , IF(ss.Rang > 0, ss.Rang, 99999) As Rang_sort
                            , d.Typ
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
                            LEFT JOIN wettkampf AS w ON r.xWettkampf = w.xWettkampf
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin)
                    WHERE 
                            r.xRunde = " . $round ."  
                            $where_final                    
                     ORDER BY heatid ". $order.", $order2";  
                           
            }
            //echo $query;
            
                     
        }
        else {                                // relay event
            
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
        
        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            $doc->printFollowing();
            
            if($_GET['ranking']==1) {
                $doc->setFreeTxt($strRankingList);
                $prev_rnd_name = $strResult_short;   
            } else{
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
            }
            $doc->freetext_bool = true; 
            
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
                $b = 0;        // initialize track nbr (numeric)
                $h = 0;        // initialize heat counter
                $id = '';    // initialize heat ID (alphanumeric)
                $tracks = 0;
                
                $maxLines = ($doc->landscape) ? 9 : 13;
                $palmares_width = ($doc->landscape) ? 735 : 537;
                
                $sql_sr = "SELECT
                                rekorde.result As sr_result
                                , rekorde.lastname As sr_lastname
                                , rekorde.firstname As sr_firstname
                                , rekorde.date As sr_date
                                , rekorde.record_type as sr_type
                            FROM
                                rekorde
                                INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
                                    ON (d.Kurzname = rekorde.discipline)                 
                            WHERE rekorde.category = '$catcode'
                                AND d.Code = $discipline_id
                                AND rekorde.season = '$saison'
                            ORDER BY sr_result ASC
                            LIMIT 1";
                //echo  $sql_sr;
                $res_sr = mysql_query($sql_sr);
                                    
                if(mysql_num_rows($res_sr)==0){
                    $sr_result = "";
                    $sr_name = "";
                    $sr_type = "";
                }else{  
                    $row_sr = mysql_fetch_array($res_sr);
                    $timepices = explode(":", $row_sr['sr_result']);
                    $sr_result = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) + ($timepices[2] *  1000) + ($timepices[3]);
                    if($layout == $cfgDisciplineType[$strDiscTypeTrack] || $layout == $cfgDisciplineType[$strDiscTypeTrackNoWind]) {
                        $sr_result = AA_formatResultTime($sr_result, true, true);
                    } else {
                        $sr_result = AA_formatResultTime($sr_result,true,false);
                    }                             
                    $sr_name = $row_sr['sr_firstname']." ".$row_sr['sr_lastname']." (".date("Y",strtotime($row_sr['sr_date'])).")";
                    $sr_type = $row_sr['sr_type'];
                }
                
                $sl_i = 0;
                $sl_result = array();
                $sl_name = array();
                $sl_cat = array();
                
                $sql_sl_cat = "SELECT 
                            k.Alterslimite
                            , k.Geschlecht
                            , k.Code
                            , k.Kurzname
                     FROM 
                            runde AS r
                            LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
                            LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d1 ON (d1.Code = w.Mehrkampfcode)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            
                     WHERE 
                            r.xRunde " . $sqlRound;
                $res_sl_cat = mysql_query($sql_sl_cat);
                
                if(mysql_num_rows($res_sl_cat)==0){
                    $sl_result[$sl_i] = "";
                    $sl_name[$sl_i] = "";
                    $sl_cat[$sl_i] = "";
                }else{  
                    while($row_sl_cat = mysql_fetch_row($res_sl_cat))
                    {
                        $sex_cat = $row_sl_cat[1];
                        $agelimit_cat = $row_sl_cat[0];
                
                        $sql_sl = "SELECT
                                        bp.season_effort As sl_result
                                        , ba.lastname As sl_lastname
                                        , ba.firstname As sl_firstname
                                        , bp.season_effort_date As sl_date
                                    FROM
                                        athletica.base_performance As bp
                                        INNER JOIN athletica.base_athlete As ba
                                            ON (bp.id_athlete = ba.id_athlete)                 
                                    WHERE bp.season_effort <> '' 
                                        AND ba.sex = '$sex_cat'
                                        AND YEAR(ba.birth_date) >= ".date('Y')."-$agelimit_cat
                                        AND bp.discipline = $discipline_id
                                        AND bp.season = '$saison'
                                    ORDER BY sl_result ASC
                                    LIMIT 1";
                        //echo  $sql_sl;
                        $res_sl = mysql_query($sql_sl);
                                            
                        if(mysql_num_rows($res_sl)==0){
                            $sl_result[$sl_i] = "";
                            $sl_name[$sl_i] = "";
                            $sl_cat[$sl_i] = "";
                        }else{  
                            $sl_cat[$sl_i] =  $row_sl_cat[3];
                            $row_sl = mysql_fetch_array($res_sl);
                            $timepices = explode(":", $row_sl['sl_result']);
                            $sl_result_tmp = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) + ($timepices[2] *  1000) + ($timepices[3]);
                            if($layout == $cfgDisciplineType[$strDiscTypeTrack] || $layout == $cfgDisciplineType[$strDiscTypeTrackNoWind]) {
                                $sl_result[$sl_i] = AA_formatResultTime($sl_result_tmp, true, true);
                            } else {
                                $sl_result[$sl_i] = AA_formatResultTime($sl_result_tmp,true, false);
                            }                             
                            $sl_name[$sl_i] = $row_sl['sl_firstname']." ".$row_sl['sl_lastname']." (".date("d.m.",strtotime($row_sl['sl_date'])).")";
                            $sl_i++;
                        }
                    }
                }
                
                if(!$relay) {
                

                    while($row = mysql_fetch_row($result))
                    {
                        $sql_base = "SELECT
                                d.Name as DiszName
                                , d.Typ
                                , best_effort
                                , DATE_FORMAT(best_effort_date, '%d.%m.%Y') AS pb_date
                                , best_effort_event
                                , season_effort
                                , DATE_FORMAT(season_effort_date, '%d.%m.%Y') AS sb_date
                                , season_effort_event
                                , season
                            FROM
                                athletica.base_athlete
                                INNER JOIN athletica.base_performance As bp
                                    ON (base_athlete.id_athlete = bp.id_athlete)
                                INNER JOIN athletica.athlet 
                                    ON (athlet.Lizenznummer = base_athlete.license)
                                INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
                                    ON (d.Code = bp.discipline)                         
                            WHERE (athlet.xAthlet =$row[15])
                                AND bp.discipline = $discipline_id
                                AND season = '$saison' 
                                AND NOT (best_effort = '' AND season_effort = '')";
                        //echo $sql_base;
                    
                        $res_base = mysql_query($sql_base);
                                            
                        if(mysql_num_rows($res_base)==0){
                            $season_effort = "";
                            $season_effort_res = "";
                            $best_effort = "";
                            $best_effort_res = "";
                        }else{  
                            $row_base = mysql_fetch_array($res_base);
                            if($row_base['season_effort'] <> "") {
                                $timepices = explode(":", $row_base['season_effort']);
                                $season_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) + ($timepices[2] *  1000) + ($timepices[3]);
                                if($layout == $cfgDisciplineType[$strDiscTypeTrack] || $layout == $cfgDisciplineType[$strDiscTypeTrackNoWind]) {
                                    $season_effort_res = AA_formatResultTime($season_effort, true, true);
                                } else {
                                    $season_effort_res = AA_formatResultTime($season_effort,true, false);
                                }    
                                
                                $sql_sb_act = "SELECT
                                                COUNT(season_effort) + 1 As rank
                                            FROM
                                                athletica.base_performance As bp
                                                INNER JOIN athletica.base_athlete As ba
                                                    ON (bp.id_athlete = ba.id_athlete)                 
                                            WHERE bp.season_effort <> '' 
                                                AND ba.sex = '$sex'
                                                AND YEAR(ba.birth_date) >= ".date('Y')."-$agelimit
                                                AND bp.discipline = $discipline_id
                                                AND bp.season = '$saison'
                                                AND bp.season_effort < '$row_base[season_effort]'";
                                //echo  $sql_sb_act;
                                $res_sb_act = mysql_query($sql_sb_act);
                                                    
                                if(mysql_num_rows($res_sb_act)==0){
                                    $sb_rank_act = "";
                                }else{  
                                    $row_sb_act = mysql_fetch_array($res_sb_act);
                                    $sb_rank_act = $row_sb_act['rank'];
                                    $season_effort = $season_effort_res ." (".$sb_rank_act.")";
                                }
                                
                            } else {
                                $season_effort = "";

                            }
                            if($row_base['best_effort']<$row_base['season_effort'] || $row_base['season_effort']==""){
                                $best_effort_year = substr($row_base['pb_date'], -2,2);
                                             
                                $timepices = explode(":", $row_base['best_effort']);
                                $best_effort = ($timepices[0] * 360 * 1000) + ($timepices[1] * 60 * 1000) + ($timepices[2] *  1000) + ($timepices[3]);
                                if($layout == $cfgDisciplineType[$strDiscTypeTrack] || $layout == $cfgDisciplineType[$strDiscTypeTrackNoWind]) {
                                    $best_effort_res = AA_formatResultTime($best_effort, true, true);
                                    $best_effort = $best_effort_res . " (" . $best_effort_year . ")";
                                } else {
                                    $best_effort_res = AA_formatResultTime($best_effort, true, false);
                                    $best_effort = $best_effort_res . " (" . $best_effort_year . ")";
                                } 
                            } else {
                                $best_effort = "";
                                $best_effort_res = 0;
                                $sb_rank_act = "";
                            }
                        }
                                               
                        $sql_palmares = "SELECT
                                        palmares_national
                                        , palmares_international
                                    FROM
                                        athletica.palmares As p
                                    INNER JOIN athletica.athlet As a 
                                        ON (a.Lizenznummer = p.license)                 
                                    WHERE a.xAthlet =$row[15]";
                        //echo  $sql_palmares;
                        $res_palmares = mysql_query($sql_palmares);
                                            
                        if(mysql_num_rows($res_palmares)==0){
                            $palmares = "";
                        }else{  
                            $row_palmares = mysql_fetch_array($res_palmares);
                            $palmares_national = $row_palmares['palmares_national'];
                            $palmares_international = $row_palmares['palmares_international'];
                            if ($palmares_international <> "") {
                                $palmares = $palmares_international . "\n" . $palmares_national;
                            } else{
                                $palmares = $palmares_national;
                            }
                        }
                        
                    if($_GET['ranking'] == 1) {
                        $result_qual = "";
                        $sql_result = "SELECT 
                                        MIN(re.Leistung) As result
                                        , re.Punkte  As points
                                    FROM athletica.resultat As re
                                        INNER JOIN athletica.serienstart As ss
                                            USING(xSerienstart)
                                        INNER JOIN athletica.serie As s
                                            USING(xSerie)
                                        INNER JOIN athletica.start as st
                                            USING(xStart)
                                        INNER JOIN athletica.Anmeldung as a
                                            USING(xAnmeldung)        
                                    WHERE s.xRunde = $round
                                        AND a.xAthlet = $row[15]";
                        //echo $sql_result;
                        $res_result = mysql_query($sql_result);
                        
                        if(mysql_num_rows($res_result)==0){
                            
                        } else {
                            $res_prev_text = "";
                            $row_result = mysql_fetch_array($res_result);
                            if($row_result['result'] > 0) {
                                $res_prev_text = AA_formatResultTime($row_result['result'], true);
                            } else {
                                $pointTxt="" ;   
                                 foreach($cfgInvalidResult as $value)    // translate value
                                        {
                                         if($value['code'] == $row_result['result']) {
                                            $res_prev_text = $value['short'];
                                         }
                                 }
                             
                            }
                        }
                        $row[12] = $row[16];           // show rank
                        
                        $res_remark = "";
                        if ($res_prev_text > 0) {
                            if ($res_prev_text < $best_effort_res && $best_effort_res > 0) {
                                $res_remark = " PB";    
                            } elseif(is_numeric($res_prev_text) && $res_prev_text < $season_effort_res && $season_effort_res > 0) {
                                $res_remark = " SB";
                            } 
                        }
                                                              
                    } else {
                        
                        $sql_res_prv = "SELECT
                                            re.Leistung 
                                        FROM athletica.resultat As re
                                            INNER JOIN athletica.serienstart As ss
                                                USING(xSerienstart)
                                            INNER JOIN serie As s
                                                USING(xSerie)
                                            INNER JOIN start As st
                                                USING(xStart)
                                            INNER JOIN anmeldung As a
                                                USING(xAnmeldung)
                                            INNER JOIN athlet As at
                                                USING(xAthlet)    
                                        WHERE s.xRunde = $prev_rnd
                                            AND a.xAthlet = $row[15]";
                        $res_res_prv = mysql_query($sql_res_prv);
                                            
                        if(mysql_num_rows($res_res_prv)==0){
                            $res_prev_text = "";
                        }else{  
                            $row_res_prv = mysql_fetch_array($res_res_prv);
                            $res_prev_text = AA_formatResultTime($row_res_prv['Leistung'], true);
                        }  
                        
                    }
                          
                        $filmnr = $row[13];

                        $tracks = $row[0];    // keep nbr of planned tracks
                        $b++;                        // current track
                        
                        if(($id != $row[3] && (!$combined || $_GET['ranking']!=1)) || ($h == 0))        // new heat
                        {                      
                            if($h != 0)    {        // not first heat
                                $doc->printEndHeat();
                                  
                                $doc->insertPageBreak();                         
                            }                                   
                            
                            $b = 1;                        // (re-)start with track one
                            if(is_null($row[1]))        // only one round
                            {
                                $heat = "$strFinalround $row[3]";
                            }
                            else
                            {
                                if($row[2] == '0'){ // do not show "(ohne)"
                                    $heat = "$strHeat $row[3]";
                                }else{
                                    if($combined) {
                                        $heat = "$row[1]";
                                    } else {
                                        $heat = "$row[1] $strHeat $row[3]";
                                    }
                                }
                            }
                            $doc->printRecordSR($sr_result, $sr_name, $category_short, $sr_type);
                            
                            for($i = 0; $i <= $sl_i-1; $i++) {
                                $doc->printRecordSL($sl_result[$i], $sl_name[$i], $sl_cat[$i]);
                            }
                            
                            if($combined && $_GET['ranking'] == 1) {
                                $doc->printHeatTitleCombined($heat, $row[5], $filmnr, $prev_rnd_name);
                            } else  {
                                $doc->printHeatTitle($heat, $row[5], $filmnr, $prev_rnd_name);
                            }
                            $doc->printStartHeat($svm, $teamsm);

                            $id = $row[3];
                            $h++;            // nbr of heats
                        }
                        else if($doc->lp - ($doc->GetMultiCellHeight($palmares_width,15,$palmares)+30) < 0)    // 
                        {
                            // insert page break an repeat heat info
                            $doc->printEndHeat();                        
                            $doc->insertPageBreak();
                            
                            $doc->printRecordSR($sr_result, $sr_name, $category_short, $sr_type);
                            
                            for($i = 0; $i <= $sl_i-1; $i++) {
                                $doc->printRecordSL($sl_result[$i], $sl_name[$i], $sl_cat[$i]);
                            }
                            
                            $doc->printHeatTitle("$heat $strCont", $row[5], $filmnr, $prev_rnd_name);
                            $doc->printStartHeat($svm, $teamsm);
                        }


                        // show empty track if current track and position not identical
                        if($_GET['ranking'] != 1) { 
                            while($b < $row[12])
                            {
                                $doc->printHeatLine($b, $strEmpty);
                                $b++;
                            }
                        }
                        
                        if($relay == FALSE) {
                            
                            if($combined) {
                                $doc->printHeatLine($row[12], $row[6], "$row[8] ".strtoupper($row[7])
                                    , AA_formatYearOfBirth($row[9]), $row[10], $row[4], $row[14],$season_effort,$best_effort, $palmares, $res_prev_text.$res_remark, $row_result['points']);
                            } else {
                                $doc->printHeatLine($row[12], $row[6], "$row[8] ".strtoupper($row[7])
                                    , AA_formatYearOfBirth($row[9]), $row[10], $row[4], $row[14],$season_effort,$best_effort, $palmares, $res_prev_text.$res_remark);
                            }
                        }
                        else
                        {    
                            $team = $row[7];        // relay name
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
                            
                            if(mysql_errno() > 0)        // DB error
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
                    
                    if($_GET['ranking'] != 1) {
                        $b++;
                        while(($b > 1) && ($b <= $tracks))
                        {
                            $doc->printHeatLine($b, $strEmpty);
                            $b++;
                        }
                    }

                    $doc->printEndHeat();        // terminate last heat
                    break;
                } else {
                    
                    
                }

            //
            // technical disciplines
            // ---------------------
            case($cfgDisciplineType[$strDiscTypeJump]):
            case($cfgDisciplineType[$strDiscTypeJumpNoWind]):
            case($cfgDisciplineType[$strDiscTypeThrow]):
            case($cfgDisciplineType[$strDiscTypeHigh]):
            case($cfgDisciplineType[$strDiscTypeNone]):
                $b = 0;        // initialize track nbr (numeric)
                $h = 0;        // initialize heat counter 
                $id = '';        // initialize heat ID (alphanumeric)
                
                $palmares_width = ($doc->landscape) ? 735 : 537;
                $maxLines = ($doc->landscape) ? 8 : 17;

                $sql_sr = "SELECT
                                rekorde.result As sr_result
                                , rekorde.lastname As sr_lastname
                                , rekorde.firstname As sr_firstname
                                , rekorde.date As sr_date
                                , rekorde.record_type As sr_type
                            FROM
                                rekorde
                                INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
                                    ON (d.Kurzname = rekorde.discipline)                 
                            WHERE rekorde.category = '$catcode'
                                AND d.Code = $discipline_id
                                AND rekorde.season = '$saison'
                            ORDER BY sr_result DESC
                            LIMIT 1";
                //echo  $sql_sr;
                $res_sr = mysql_query($sql_sr);
                                    
                if(mysql_num_rows($res_sr)==0){
                    $sr_result = "";
                    $sr_name = "";
                    $sr_type = "";
                }else{  
                    $row_sr = mysql_fetch_array($res_sr);
                    $sr_result = AA_formatResultMeter(str_replace(".", "", $row_sr['sr_result']));                             
                    $sr_name = $row_sr['sr_firstname']." ".$row_sr['sr_lastname']." (".date("Y",strtotime($row_sr['sr_date'])).")";
                    $sr_type = $row_sr['sr_type'];
                }
                
                $sl_i = 0;
                $sl_result = array();
                $sl_name = array();
                $sl_cat = array();
                
                $sql_sl_cat = "SELECT 
                            k.Alterslimite
                            , k.Geschlecht
                            , k.Code
                            , k.Kurzname
                     FROM 
                            runde AS r
                            LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
                            LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d1 ON (d1.Code = w.Mehrkampfcode)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            
                     WHERE 
                            r.xRunde " . $sqlRound;
                $res_sl_cat = mysql_query($sql_sl_cat);
                
                if(mysql_num_rows($res_sl_cat)==0){
                    $sl_result[$sl_i] = "";
                    $sl_name[$sl_i] = "";
                    $sl_cat[$sl_i] = "";
                }else{  
                    while($row_sl_cat = mysql_fetch_row($res_sl_cat))
                    {
                        $sex_cat = $row_sl_cat[1];
                        $agelimit_cat = $row_sl_cat[0];
                
                        $sql_sl = "SELECT
                                        bp.season_effort As sl_result
                                        , ba.lastname As sl_lastname
                                        , ba.firstname As sl_firstname
                                        , bp.season_effort_date As sl_date
                                    FROM
                                        athletica.base_performance As bp
                                        INNER JOIN athletica.base_athlete As ba
                                            ON (bp.id_athlete = ba.id_athlete)                 
                                    WHERE bp.season_effort <> '' 
                                        AND ba.sex = '$sex_cat'
                                        AND YEAR(ba.birth_date) >= ".date('Y')."-$agelimit_cat
                                        AND bp.discipline = $discipline_id
                                        AND bp.season = '$saison'
                                    ORDER BY sl_result DESC
                                    LIMIT 1";
                        //echo  $sql_sl;
                        $res_sl = mysql_query($sql_sl);
                                            
                        if(mysql_num_rows($res_sl)==0){
                            $sl_result[$sl_i] = "";
                            $sl_name[$sl_i] = "";
                            $sl_cat[$sl_i] = "";
                        }else{  
                            $sl_cat[$sl_i] =  $row_sl_cat[3];
                            $row_sl = mysql_fetch_array($res_sl);
                            $sl_result[$sl_i] = AA_formatResultMeter(str_replace(".", "", $row_sl['sl_result']));                                                          
                            $sl_name[$sl_i] = $row_sl['sl_firstname']." ".$row_sl['sl_lastname']." (".date("d.m.",strtotime($row_sl['sl_date'])).")";
                            $sl_i++;
                        }
                    }
                }
                
                while($row = mysql_fetch_row($result))
                {
                    $b++;                        // current athlete
                    
                    $sql_base = "SELECT
                                d.Name as DiszName
                                , d.Typ
                                , best_effort
                                , DATE_FORMAT(best_effort_date, '%d.%m.%Y') AS pb_date
                                , best_effort_event
                                , season_effort
                                , DATE_FORMAT(season_effort_date, '%d.%m.%Y') AS sb_date
                                , season_effort_event
                                , season
                            FROM
                                athletica.base_athlete
                                INNER JOIN athletica.base_performance As bp
                                    ON (base_athlete.id_athlete = bp.id_athlete)
                                INNER JOIN athletica.athlet 
                                    ON (athlet.Lizenznummer = base_athlete.license)
                                INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
                                    ON (d.Code = bp.discipline)                         
                            WHERE (athlet.xAthlet =$row[15])
                                AND bp.discipline = $discipline_id
                                AND season = '$saison'
                                AND NOT (best_effort = '' AND season_effort = '')";
                    
                    $res_base = mysql_query($sql_base);
                                        
                    if(mysql_num_rows($res_base)==0){
                        $season_effort = "";
                        $season_effort_res = "";
                        $best_effort = "";
                        $best_effort_res = "";
                    }else{  
                        $row_base = mysql_fetch_array($res_base);
                        $season_effort = "";
                        $season_effort_res = "";
                        if($row_base['season_effort'] > 0) { 
                            $season_effort_res = AA_formatResultMeter(str_replace(".", "", $row_base['season_effort']));
                            
                            $sql_sb_act = "SELECT
                                                COUNT(season_effort) + 1 As rank
                                            FROM
                                                athletica.base_performance As bp
                                                INNER JOIN athletica.base_athlete As ba
                                                    ON (bp.id_athlete = ba.id_athlete)                 
                                            WHERE bp.season_effort <> '' 
                                                AND ba.sex = '$sex'
                                                AND YEAR(ba.birth_date) >= ".date('Y')."-$agelimit
                                                AND bp.discipline = $discipline_id
                                                AND bp.season = '$saison'
                                                AND bp.season_effort > '$row_base[season_effort]'";
                            
                            $res_sb_act = mysql_query($sql_sb_act);
                                                
                            if(mysql_num_rows($res_sb_act)==0){
                                $sb_rank_act = "";
                            }else{  
                                $row_sb_act = mysql_fetch_array($res_sb_act);
                                $sb_rank_act = $row_sb_act['rank'];
                                $season_effort = $season_effort_res ." (".$sb_rank_act.")";
                            }
                            
                        }
                        if($row_base['best_effort']>$row_base['season_effort']) {
                            $best_effort_year = substr($row_base['pb_date'], -2,2);
                            $best_effort_res = AA_formatResultMeter(str_replace(".", "", $row_base['best_effort']));
                            $best_effort = $best_effort_res . " (" . $best_effort_year . ")";
                        } else {
                            $best_effort_res = "";
                            $best_effort = "";
                        }
                    }
                    
                    $sql_palmares = "SELECT
                                    palmares_national
                                    , palmares_international
                                FROM
                                    athletica.palmares As p
                                INNER JOIN athletica.athlet As a 
                                    ON (a.Lizenznummer = p.license)                 
                                WHERE a.xAthlet =$row[15]";
                    //echo  $sql_palmares;
                    $res_palmares = mysql_query($sql_palmares);
                                        
                    if(mysql_num_rows($res_palmares)==0){
                        $palmares = "";
                    }else{  
                        $row_palmares = mysql_fetch_array($res_palmares);
                        $palmares_national = $row_palmares['palmares_national'];
                        $palmares_international = $row_palmares['palmares_international'];
                        if ($palmares_international <> "") {
                            $palmares = $palmares_international . "\n" . $palmares_national;
                        } else{
                            $palmares = $palmares_national;
                        }
                        
                    }
                    
                    if($_GET['ranking'] == 1) {
                        $result_qual = "";
                        
                        if($row[18] == $cfgDisciplineType[$strDiscTypeHigh])
                        {
                            $valid_result =    " AND (re.Info LIKE '%O%'"
                                                        . " OR re.Leistung < 0)";
                        }
                        $sql_result = "SELECT 
                                        MAX(re.Leistung) As result
                                        , re.Punkte  As points
                                    FROM athletica.resultat As re
                                        INNER JOIN athletica.serienstart As ss
                                            USING(xSerienstart)
                                        INNER JOIN athletica.serie As s
                                            USING(xSerie)
                                        INNER JOIN athletica.start as st
                                            USING(xStart)
                                        INNER JOIN athletica.Anmeldung as a
                                            USING(xAnmeldung)        
                                    WHERE s.xRunde = $round
                                        AND a.xAthlet = $row[15]
                                        $valid_result";
                        //echo $sql_result;
                        $res_result = mysql_query($sql_result);
                        
                        if(mysql_num_rows($res_result)==0){
                            
                        } else {
                            $res_prev_text = "";                            
                            $row_result = mysql_fetch_array($res_result);
                            if($row_result['result'] > 0) {
                                $result_qual = AA_formatResultMeter($row_result['result']);
                            } else {
                                $pointTxt="" ;   
                                 foreach($cfgInvalidResult as $value)    // translate value
                                        {
                                         if($value['code'] == $row_result['result']) {
                                            $result_qual = $value['short'];
                                         }
                                 }
                             
                            }
                        }
                        $row[4] = $row[16];           // show rank
                        
                        $res_remark = "";
                        if($result_qual > 0) {
                            if (is_numeric($result_qual) && $result_qual > $best_effort_res && $best_effort_res > 0) {
                                $res_remark = " PB";    
                            } elseif(is_numeric($result_qual) && $result_qual > $season_effort_res && $season_effort_res > 0) {
                                $res_remark = " SB";
                            } 
                        }                                 
                    }
                        
                        
                        
                        
                        
                    // new heat
                    if(($id != $row[3]) || ($h == 0))
                    {
                        if($h != 0)    {        // not first heat          
                            $doc->printEndHeat();                            
                            $doc->insertPageBreak();
                        }

                        $b = 1;                        // (re-)start with track one
                        if(is_null($row[1]))        // only one round
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
                        
                        $doc->printRecordSR($sr_result, $sr_name, $category_short, $sr_type);    
                        for($i = 0; $i <= $sl_i-1; $i++) {
                            $doc->printRecordSL($sl_result[$i], $sl_name[$i], $sl_cat[$i]);
                        }
                        
                        if($combined && $_GET['ranking'] == 1) {
                                $doc->printHeatTitleCombined($heat, $row[5], '', $prev_rnd_name);
                            } else  {
                                $doc->printHeatTitle($heat, $row[5], '', $prev_rnd_name);
                            }

                        $h++;
                        $id = $row[3];
                        
                    }
                    // new page after x athl. 
            
                   //else if($b > $maxLines)
                   else if($doc->lp - ($doc->GetMultiCellHeight($palmares_width,15,$palmares)+30) < 0)
                    {    
                        // insert page break an repeat heat info
                        $doc->printEndHeat();
                       
                        $doc->insertPageBreak();  
                        
                        $doc->printRecordSR($sr_result, $sr_name, $category_short, $sr_type);    
                        for($i = 0; $i <= $sl_i-1; $i++) {
                            $doc->printRecordSL($sl_result[$i], $sl_name[$i], $sl_cat[$i]);
                        }
                        
                        $doc->printHeatTitle("$heat $strCont", $row[5], '', $prev_rnd_name);
                        $b = 1;
                    }
                    
                    if($combined) {                  
                        $doc->printHeatLine($row[6], "$row[8] ".strtoupper($row[7])
                            , AA_formatYearOfBirth($row[9]), $row[10], $row[14],$season_effort,$best_effort, $row[4], $palmares, $result_qual.$res_remark, $row_result['points']);
                    } else {
                        $doc->printHeatLine($row[6], "$row[8] ".strtoupper($row[7])
                            , AA_formatYearOfBirth($row[9]), $row[10], $row[14],$season_effort,$best_effort, $row[4], $palmares, $result_qual.$res_remark);
                    }
                }
                break;
            }        // end switch "Layout"

            mysql_free_result($result);
        }        // ET DB error
    }        // ET round selected

    $doc->endPage();
}        // ET round data found
?>

