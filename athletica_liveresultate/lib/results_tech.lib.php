<?php

/**********
 *
 *    tech results
 *    
 */

if (!defined('AA_RESULTS_TECH_LIB_INCLUDED'))
{
    define('AA_RESULTS_TECH_LIB_INCLUDED', 1);

    function AA_results_Tech($round, $layout, $cat, $dis, $roundName, $event, $id_back, $class_status, $class_status_long, $status) {
        global $content_list; 
             
        require('./config.inc.php');
        //require('./config.inc.end.php');   

        require('./lib/common.lib.php');
        require('./lib/heats.lib.php');     
        require('./lib/utils.lib.php');

        $p = "./tmp";
        $fp = @fopen($p."/live".$round.".php",'w');
        if(!$fp){
            AA_printErrorMsg($GLOBALS['strErrFileOpenFailed']);  
            return;
        }  

        $svm = AA_checkSVM(0, $round); // decide whether to show club or team name      
        $teamsm = AA_checkTeamSM(0, $round);  

        $mergedMain=AA_checkMainRound($round);  
        if ($mergedMain > 0) {
            $sqlRounds = AA_getMergedRounds($round);
            $sqlRounds = " IN " . $sqlRounds; 
            if ($mergedMain == 1) {           
                $round = AA_getMainRound($round); 
            }   
        }
        else {
              $sqlRounds = " = " . $round;
        }
        
        $content_list = "<?php\r\n ";
        $content_list .= "include('include/header.php');\r\n ";
        $content_list .= "?>\r\n ";
        $content_list .= "<div data-role='page' id='page' data-title='".$GLOBALS['strLiveResults']."'>\r\n"; 
        $content_list .= "<div data-role='header' data-theme='b' data-id='header' data-position='fixed' data-tap-toggle='false'>\r\n";
        $content_list .= "<a href='timetable".$id_back.".php' data-icon='back' data-transition='slide' data-direction='reverse'>".$GLOBALS['strBack']."</a>\r\n";
        $content_list .= "<a data-icon='refresh' onclick='refreshPage();'>".$GLOBALS['strRefresh']."</a>\r\n";
        
        //$status = '';
        // get status round 
        $sql_rnd = "
            SELECT                      
                r.Status As rnd_status,
                TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat') As rnd_starttime,
                DATE_FORMAT(r.Datum, '$cfgDBdateFormat') As rnd_date
            FROM
                athletica.runde as r                       
            WHERE
                r.xRunde=" .$round;
        $res_rnd = mysql_query($sql_rnd);
       
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        
        if (mysql_num_rows($res_rnd) == 1) {
            $row_rnd=mysql_fetch_assoc($res_rnd);              
            //$status =  $row_rnd['rnd_status'];
        } 

        // +++ athletes
        if ($status == $cfgRoundStatus['open'] || $status == $cfgRoundStatus['enrolement_done'] || $status == $cfgRoundStatus['enrolement_pending'] || $status == $cfgRoundStatus['heats_in_progress'] ) {         
            if ($teamsm){
                $sql = "
                    SELECT 
                        rt.Name,
                        rt.Typ, 
                        a.Startnummer,
                        at.Name,
                        at.Vorname,
                        at.Jahrgang,
                        t.Name,
                        r.Versuche,    
                        at.Land,
                        r.nurBestesResultat,
                        at.xAthlet,
                        st.Bestleistung
                    FROM 
                        athletica.start As st
                            LEFT JOIN athletica.runde AS r ON (r.xWettkampf = st.xWettkampf)    
                            LEFT JOIN athletica.anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN athletica.verein AS v ON (v.xVerein = at.xVerein)                                   
                            INNER JOIN athletica.teamsmathlet AS tat ON(a.xAnmeldung = tat.xAnmeldung)    
                            LEFT JOIN athletica.teamsm as t ON (t.xWettkampf = st.xWettkampf AND tat.xTeamsm = t.xTeamsm)                     
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt    ON rt.xRundentyp = r.xRundentyp   
                    WHERE 
                        t.Name is NOT NULL 
                        AND r.xRunde  " . $sqlRounds  . "
                        AND r.Gruppe = st.Gruppe                             
                    ORDER BY 
                        at.Name,
                        at.Vorname
                ";
            }   
            else {
                $sql = "
                    SELECT 
                        rt.Name,
                        rt.Typ,
                        a.Startnummer,
                        at.Name,
                        at.Vorname,
                        at.Jahrgang,
                        if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)),
                        r.Versuche,
                        at.Land,
                        r.nurBestesResultat,
                        at.xAthlet,
                        st.Bestleistung
                    FROM 
                        athletica.start As st
                            LEFT JOIN athletica.runde AS r ON (r.xWettkampf = st.xWettkampf)    
                            LEFT JOIN athletica.anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN athletica.verein AS v ON (v.xVerein = at.xVerein)                                   
                            LEFT JOIN athletica.team AS t ON(a.xTeam = t.xTeam) 
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt    ON rt.xRundentyp = r.xRundentyp   
                    WHERE 
                        r.xRunde  " . $sqlRounds  . "                             
                    ORDER BY
                        at.Name,
                        at.Vorname
                ";
            }       
        }
        // --- athletes 
        // +++ startlist
        else {  
            if ($teamsm) {
                $sql = "
                    SELECT 
                        rt.Name,
                        rt.Typ,
                        s.xSerie,
                        s.Bezeichnung,
                        s.Wind,
                        an.Bezeichnung,
                        ss.xSerienstart,
                        ss.Position,
                        ss.Rang,
                        a.Startnummer,
                        at.Name,
                        at.Vorname,
                        at.Jahrgang,
                        t.Name,
                        LPAD(s.Bezeichnung,5,'0') as heatid,
                        r.Versuche,
                        ss.Qualifikation,
                        at.Land,
                        r.nurBestesResultat,
                        ss.Bemerkung,
                        at.xAthlet,
                        st.Bestleistung
                    FROM 
                        athletica.serienstart As ss
                            LEFT JOIN athletica.start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN athletica.serie AS s ON (s.xSerie = ss.xSerie)
                            LEFT JOIN athletica.runde AS r ON (r.xRunde = s.xRunde)
                            LEFT JOIN athletica.anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN athletica.verein AS v ON (v.xVerein = at.xVerein)                                   
                            INNER JOIN athletica.teamsmathlet AS tat ON(a.xAnmeldung = tat.xAnmeldung)    
                            LEFT JOIN athletica.teamsm as t ON (t.xWettkampf = st.xWettkampf AND tat.xTeamsm = t.xTeamsm)   
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt  ON rt.xRundentyp = r.xRundentyp    
                            LEFT JOIN athletica.anlage AS an ON an.xAnlage = s.xAnlage                                   
                    WHERE 
                        t.Name is NOT NULL 
                        AND r.xRunde  " . $sqlRounds ."                              
                    ORDER BY 
                        heatid,
                        ss.Position
                ";
            } 
            else {
                $sql = "
                    SELECT 
                        rt.Name,
                        rt.Typ,
                        s.xSerie,
                        s.Bezeichnung,
                        s.Wind,
                        an.Bezeichnung,
                        ss.xSerienstart,
                        ss.Position,
                        ss.Rang,
                        a.Startnummer,
                        at.Name,
                        at.Vorname,
                        at.Jahrgang,
                        if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))  ,
                        LPAD(s.Bezeichnung,5,'0') as heatid,
                        r.Versuche,
                        ss.Qualifikation,
                        at.Land,
                        r.nurBestesResultat,
                        ss.Bemerkung,
                        at.xAthlet,
                        st.Bestleistung
                    FROM 
                        athletica.serienstart As ss
                            LEFT JOIN athletica.start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN athletica.serie AS s ON (s.xSerie = ss.xSerie)
                            LEFT JOIN athletica.runde AS r ON (r.xRunde = s.xRunde)
                            LEFT JOIN athletica.anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN athletica.verein AS v ON (v.xVerein = at.xVerein)                                   
                            LEFT JOIN athletica.team AS t ON(a.xTeam = t.xTeam) 
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt  ON rt.xRundentyp = r.xRundentyp    
                            LEFT JOIN athletica.anlage AS an ON an.xAnlage = s.xAnlage                                   
                    WHERE 
                        r.xRunde  " . $sqlRounds ."                              
                    ORDER BY 
                        heatid,
                        ss.Position
                ";
            }     
        }   
        // --- startlist                   
        $result = mysql_query($sql);
              
        if(mysql_errno() > 0) {        // DB error                          
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {                             
            // initialize variables
            $h = 0;
            $i = 0;
            $r = 0;  
            
            // set up category and discipline title information 
            if(!empty($roundName)) {
                if($roundName != "(ohne)"){
                    $round_name = ", ".$roundName;    
                }
            }    

            $content_list .= "<h1>".$dis.$round_name." - ".$cat."</h1>\r\n";
                    
            $content_list .= "</div>\r\n";
            $content_list .= "<div class='header-content' data-id='header-content'>\r\n";
            $content_list .= "<table width='100%' height='100%' style='vertical-align: middle;'>\r\n";
            $content_list .= "<tr>\r\n";
            $content_list .= "<td align='left' width='20%'>\r\n";
            if($cfgLogoLeft) {
                $content_list .= "<img class='logo_left' src='img/logo_left.png' height='70px'>\r\n";
            }
            $content_list .= "</td>\r\n";
            $content_list .= "<td>\r\n";
            $content_list .= "<td>\r\n";
            $content_list .= "<div class='list-".$class_status."-big'>".$class_status_long."</div>\r\n";
            $content_list .= "</td>\r\n";
            $content_list .= "<td align='right' width='20%'>\r\n";
            if($cfgLogoRight) {
                $content_list .= "<img class='logo_right' src='img/logo_right.png' height='70px'>\r\n";
            }
            $content_list .= "</td>\r\n";
            $content_list .= "</tr>\r\n";
            $content_list .= "</table>\r\n";
            $content_list .= "</div>\r\n";
            
            $content_list .= "<div data-role='content' id='content' data-theme='c'>\r\n";
            
            $div_res = false;
            $div_collapsible = false;
            $table = false;
            
            // +++ Teilnehmerliste  
            if ($status == $cfgRoundStatus['open'] || $status == $cfgRoundStatus['enrolement_done'] || $status == $cfgRoundStatus['enrolement_pending'] || $status == $cfgRoundStatus['heats_in_progress'] )  { 
                $content_list .= "<div class='ui-corner-all ui-shadow' style='padding: 5px;'>\r\n";
                            
                $div_res = true; 
                
                $content_list .= "<table class='table-athletes ui-responsive table-stroke'>\r\n";
                $content_list .= "<thead>\r\n";
                $content_list .= "<tr>\r\n";

                $table = true;          
    
                $content_list .= "<th class='bib' data-sort='int'>".$GLOBALS['strStartnumber']."</th>\r\n";
                $content_list .= "<th class='name' data-sort='string'>".$GLOBALS['strAthlete']."</th>\r\n";
                $content_list .= "<th class='yob'>".$GLOBALS['strYearShort']."</th>\r\n";
                $content_list .= "<th class='country'>".$GLOBALS['strCountry']."</th>\r\n";    
                if($svm){
                    $content_list .= "<th class='club' data-sort='string'>".$GLOBALS['strTeam']."</th>\r\n";
                } else{
                    $content_list .= "<th class='club' data-sort='string'>".$GLOBALS['strClub']."</th>\r\n";   
                }
                $content_list .= "<th class='topPerf' data-sort='float'>".$GLOBALS['strTopPerformance']."</th>\r\n";
                    
                $content_list .= "</tr>\r\n";
                $content_list .= "</thead>\r\n";
                $content_list .= "<tbody>\r\n";
                
                if(mysql_num_rows($result) > 0) {
                    
                while($row = mysql_fetch_row($result))
                    {
                        // format topPerf
                        $topPerf = $row[11];
                        
                        if(($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                            || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeJumpNoWind']])
                            || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']])
                            || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']])) {
                            $list_topPerf = AA_formatResultMeter($topPerf);
                        }
                        else {
                            if(($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
                            || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrackNoWind']])){
                                $list_topPerf = AA_formatResultTime($topPerf, true, true);
                            }else{
                                $list_topPerf = AA_formatResultTime($topPerf, true);
                            }
                        }
                         
                        $content_list .= "<tr>\r\n";

                        $content_list .= "<td class='bib'>".$row[2]."</td>\r\n"; 
                        $content_list .= "<td class='name'>".$row[3]." ".$row[4]."</td>\r\n";
                        $content_list .= "<td class='yob'>".AA_formatYearOfBirth($row[5])."</td>\r\n";
                        $content_list .= "<td class='country'>\r\n";
                        if ($row[8]!='' && $row[8]!='-') {
                            $content_list .= $row[8];
                        } else {
                            $content_list .= " "; 
                        }
                        $content_list .= "</td>\r\n";
                        $content_list .= "<td class='club'>".$row[6]."</td>";
                        $content_list .= "<td class='topPerf'>".$list_topPerf."</td>\r\n";
                         
                        $content_list .= "</tr>\r\n";    
                    }
                }
                else{
                    $content_list .= "<tr class=''>\r\n";
                    $content_list .= "<td class='bib'>&nbsp;</td>\r\n";
                    $content_list .= "<td class='name' colspan='4'><span id='empty'>" . $GLOBALS['strNoEntries'] ."</span></td>\r\n";
                    $content_list .= "</tr>";        
                }
            }
            // --- Teilnehmerliste
        
            // +++ Startliste
            else {
                
                while($row = mysql_fetch_row($result))
                {  
                    if($h != $row[2])        // new heat
                    {
                        $h = $row[2];                // keep heat ID

                        if(is_null($row[0])) {        // only one round
                            $title = $GLOBALS['strFinalround'];
                        }
                        else {        // more than one round
                            if($row[0] == "(ohne)"){  //TODO
                                $title = "";    
                            } else {
                                $title = $row[0]." " .$row[3];
                            }
                        } 
                        
                        if($table){
                            $content_list .= "</tbody>\r\n"; 
                            $content_list .= "</table>\r\n";    
                        }
                        
                        if($div_collapsible){
                            $content_list .= "</div>\r\n";    
                        }
                        
                        if($div_res){
                            $content_list .= "</div>\r\n";    
                        }
                        
                        if($title != "") {  
                            $content_list .= "<div data-role='collapsible' data-theme='b' data-content-theme='d' data-collapsed='false' data-collapsed-icon='' data-expanded-icon='' data-inset='true'>\r\n";
                            $div_collapsible = true;
                        
                            $content_list .= "<h4>" .$title."</h4>\r\n"; 
                        }

                        $content_list .= "<div class='ui-corner-all ui-shadow' style='padding: 5px;'>\r\n";
                                    
                        $div_res = true;
                        
                        $content_list .= "<table class='table-startlist ui-responsive table-stroke'>\r\n";
                        $content_list .= "<thead>\r\n";
                        $content_list .= "<tr>\r\n";
                        
                        $content_list .="<tr>";   
                        $content_list .= "<th class='pos' data-sort='int'>". $GLOBALS['strPositionShort'] ."</th>\r\n";   
                        $content_list .= "<th class='bib' data-sort='int'>". $GLOBALS['strStartnumber'] ."</th>\r\n";     
                        $content_list .= "<th class='name' data-sort='string'>". $GLOBALS['strAthlete'] ."</th>\r\n";   
                        $content_list .= "<th class='yob'>". $GLOBALS['strYearShort'] ."</th>\r\n";   
                        $content_list .= "<th class='country'>". $GLOBALS['strCountry'] ."</th>\r\n";   
                        $content_list .= "<th class='club' data-sort='string'>\r\n";
                        if($svm){ 
                            $content_list .= $GLOBALS['strTeam']; 
                        }else{ 
                            $content_list .= $GLOBALS['strClub'];
                        } 
                        $content_list .= "<th class='topPerf' data-sort='float'>".$GLOBALS['strTopPerformance']."</th>\r\n";
                           
                        $content_list .=  "</th>\r\n"; 
                        
                        $content_list .= "</tr>\r\n";
                        $content_list .= "</thead>\r\n";
                        $content_list .= "<tbody>\r\n";
                        
                        $table = true;    
                    }        // ET new heat

                    $topPerf = $row[21];
                        
                    if(($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                        || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeJumpNoWind']])
                        || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']])
                        || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']])) {
                        $list_topPerf = AA_formatResultMeter($topPerf);
                    }
                    else {
                        if(($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
                        || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrackNoWind']])){
                            $list_topPerf = AA_formatResultTime($topPerf, true, true);
                        }else{
                            $list_topPerf = AA_formatResultTime($topPerf, true);
                        }
                    }
                    
                    $content_list .= "<tr>\r\n";
                    $content_list .= "<td class='pos'>" . $row[7] ."</td>\r\n";                          
                    $content_list .= "<td class='bib'>". $row[9] ."</td>\r\n";            
                    $content_list .= "<td class='name'>" . $row[10] . " " . $row[11] ."</td>\r\n";                 
                    $content_list .= "<td class='yob'>" . AA_formatYearOfBirth($row[12]) ."</td>\r\n";
                    $content_list .= "<td class='country'>\r\n";
                    if ($row[17]!='' && $row[17]!='-') {
                        $content_list .= $row[17];
                    } else {
                        $content_list .= " "; 
                    }
                    $content_list .= "</td>\r\n";
                    $content_list .= "<td class='club'>" . $row[13] ."</td>\r\n";
                    $content_list .= "<td class='topPerf'>".$list_topPerf."</td>\r\n";
                    
                        
               }    // end while 
               $content_res .= "</tr>\r\n";  
           }
           
           mysql_free_result($result);
              
           if($table){
                $content_list .= "</tbody>\r\n"; 
                $content_list .= "</table>\r\n";    
            }

            if($div_collapsible){
                $content_list .= "</div>\r\n";    
            }

            if($div_res){
                $content_list .= "</div>\r\n";    
            }

            $content_list .= "</div>\r\n";

            $content_list .= "<div data-role='footer' data-theme='b' data-id='footer' data-position='fixed' data-tap-toggle='false'>\r\n";
            if($cfgLogoFooter) {
                $content_list .= "<div align='center'><img src='img/footer.png' width='100%'></div>\r\n";
            }
            $content_list .= "</div>\r\n";

            $content_list .= "</div>\r\n"; 
            
            $content_list .= "<?php\r\n ";
            $content_list .= "include('include/footer.php');\r\n ";
            $content_list .= "?>\r\n ";
            
            $content_list .= "<script type='text/javascript'>\r\n"; 
            $content_list .= "$(function(){\r\n"; 
            $content_list .= "$('table').stupidtable();\r\n"; 
            $content_list .= "});\r\n"; 
            $content_list .= "</script>\r\n"; 

            if (!fwrite($fp, utf8_encode($content_list))) {
                AA_printErrorMsg($GLOBALS['strErrFileWriteFailed']);    
                return;
            }  

            fclose($fp);
        }        // ET DB error
 

        //AA_UpdateStatusChanged($round);   
    

    }    // End Function AA_results_Tech

}    // AA_RESULTS_TECH_LIB_INCLUDED
?>
