<?php

/**********
 *
 *	rankinglist single events
 *	
 */

if (!defined('AA_RANKINGLIST_SINGLE_LIB_INCLUDED'))
{
	define('AA_RANKINGLIST_SINGLE_LIB_INCLUDED', 1);

    function AA_rankinglist_Single($round, $event, $meeting, $show_efforts = 'none', $id_back, $class_status, $class_status_long, $ftp_host, $ftp_user, $ftp_pwd)
    {           
        require('./lib/cl_gui_page.lib.php'); 
        require('./lib/common.lib.php');   
        require('./config.inc.php');  
        //require('./config.inc.end.php'); 
                 
        if(AA_connectToDB_live() == FALSE)	{ // invalid DB connection
	        return;		// abort
        }

        if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	        return;		// abort
        }

        $p = "./tmp";
        $fp = @fopen($p."/live".$round.".php",'w');
        if(!$fp){
            AA_printErrorMsg($GLOBALS['strErrFileOpenFailed']);  
            return;
        }
        
        $saison = $_SESSION['meeting_infos']['Saison'];  
        if ($saison == ''){
            $saison = "O";  //if no saison is set take outdoor
        }
        
        $eventMerged=false;          
        $sqlEvents = AA_getMergedEventsFromEvent($event);
        if  ($sqlEvents!=''){              
            $selection = "w.xWettkampf IN " . $sqlEvents . " AND "; 
            $eventMerged=true; 
        }
        else {
            $selection = "r.xRunde =" . $round . " AND ";     
        }
        
        if ($eventMerged) { 
            // get event rounds from DB         
            $sql_rnd = "
                SELECT 
                    r.xRunde As rnd_id_r,
                    k.Name As rnd_cat, 
                    d.Name As rnd_dis,
                    d.Typ As rnd_typ,
                    w.xWettkampf As rnd_id_w,
                    r.QualifikationSieger As rnd_qualiSieger, 
                    r.QualifikationLeistung As rnd_qualiLeistung,
                    w.Punkteformel As rnd_points,
                    w.Windmessung As rnd_wind,
                    r.Speakerstatus,
                    d.Staffellaeufer As rnd_staffelLaeufer,
                    CONCAT(DATE_FORMAT(r.Datum,'$cfgDBdateFormat'), ' ', TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')),
                    w.xDisziplin,
                    r.Status As rnd_status,
                    rt.Name As rnd_rtName,
                    rt.Typ As rnd_rtTyp,
                    rt.Wertung As rnd_rtWertung,
                    rs.Hauptrunde As rnd_main 
                FROM
                    athletica.wettkampf AS w
                    LEFT JOIN athletica.kategorie AS k ON (k.xKategorie = w.xKategorie)
                      LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                      LEFT JOIN athletica.runde AS r ON (r.xWettkampf = w.xWettkampf) 
                      LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON (rt.xRundentyp = r.xRundentyp) 
                      LEFT JOIN athletica.rundenset AS rs ON (rs.xRunde = r.xRunde)
                WHERE " . $selection . "
                    w.xMeeting = " . $meeting . "     
                ORDER BY
                    rs.Hauptrunde DESC,
                    k.Anzeige,
                    d.Anzeige,
                    r.Datum,
                    r.Startzeit
            ";   
        }
        else {      
            // heats separate
            $sql_rnd = "
                SELECT DISTINCT 
                    r.xRunde As rnd_id_r, 
                    k.Name As rnd_cat, 
                    d.Name As rnd_dis, 
                    d.Typ As rnd_typ, 
                    w.xWettkampf As rnd_id_w, 
                    r.QualifikationSieger As rnd_qualiSieger, 
                    r.QualifikationLeistung As rnd_qualiLeistung, 
                    w.Punkteformel As rnd_points, 
                    w.Windmessung As rnd_wind, 
                    r.Speakerstatus, 
                    d.Staffellaeufer As rnd_staffelLaeufer, 
                    CONCAT(DATE_FORMAT(r.Datum,'%d.%m.%y'), 
                    TIME_FORMAT(r.Startzeit, '%H:%i')),
                    w.xDisziplin,  
                    rs.Hauptrunde,
                    r.Status As rnd_status,
                    rt.Name As rnd_rtName,
                    rt.Typ As rnd_rtTyp,
                    rt.Wertung As rnd_rtWertung,
                    r.StatusZeitmessung As rnd_status_timing
                FROM 
                    athletica.wettkampf AS w 
                    LEFT JOIN athletica.kategorie AS k ON (k.xKategorie = w.xKategorie) 
                    LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin) 
                    LEFT JOIN athletica.runde AS r ON (r.xWettkampf = w.xWettkampf) 
                    LEFT JOIN athletica.rundenset as rs ON (r.xRunde = rs.xRunde )
                    LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON (rt.xRundentyp = r.xRundentyp)           
                WHERE 
                    " . $selection . "  
                    w.xMeeting  = " . $meeting . "    
                ORDER BY
                    k.Anzeige,
                    d.Anzeige,
                    r.Datum,
                    r.Startzeit
            ";       
        }
        
        $res_rnd = mysql_query($sql_rnd);
        
        if(mysql_errno() > 0)    // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
            $event_act = 0;
            
            $content_res = "<?php\r\n ";
            $content_res .= "include('include/header.php');\r\n ";
            $content_res .= "?>\r\n ";
            $content_res .= "<div data-role='page' id='page' data-title='".$GLOBALS['strLiveResults']."'>\r\n";
            $content_res .= "<div data-role='header' data-theme='b' data-id='header' data-position='fixed' data-tap-toggle='false'>\r\n";
            $content_res .= "<a href='timetable".$id_back.".php' data-icon='back' data-transition='slide' data-direction='reverse'>".$GLOBALS['strBack']."</a>\r\n";
            $content_res .= "<a data-icon='refresh' onclick='refreshPage();'>".$GLOBALS['strRefresh']."</a>\r\n";
            
            while($row_rnd = mysql_fetch_assoc($res_rnd)){
                $show_all = false;
                // TODO: for a combined event, the rounds are merged, so jump until the next event  
               
                if(!$eventMerged || $row_rnd['rnd_main']==1){
                    if($cRounds > 1){
                        $cRounds--;
                        continue;
                    }
                    $roundSQL = "s.xRunde = ".$row_rnd['rnd_id_r'];
                    $cRounds = 0;
                    
                    if($row_rnd['rnd_rtTyp'] == '0'){
                        $roundName = "";
                    }else{
                        $roundName = $row_rnd['rnd_rtName'];
                    }
                    $eval = $row_rnd['rnd_rtWertung'];
                    
                    if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                        || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJumpNoWind']])
                        || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']])
                        || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']]))
                    {
                        $show_all = true;        
                    }
                    
                    if($event_act != $row_rnd['rnd_id_w'])        // new event -> repeat title
                    {                    
                        // set up category and discipline title information 
                        if(!empty($roundName)) {
                            $round_name = ", ".$roundName;
                        }          
        
                        $content_res .= "<h1>".$row_rnd['rnd_dis'].$round_name." - ".$row_rnd['rnd_cat']."</h1>\r\n";
                        
                        $content_res .= "</div>\r\n";
                        $content_res .= "<div class='header-content' data-id='header-content'>\r\n";
                        $content_res .= "<table width='100%' height='100%' style='vertical-align: middle;'>\r\n";
                        $content_res .= "<tr>\r\n";
                        $content_res .= "<td align='left' width='20%'>\r\n";
                        if($cfgLogoLeft) {
                            $content_res .= "<img class='logo_left' src='img/logo_left.png' height='70px'>\r\n";
                        }
                        $content_res .= "</td>\r\n";
                        $content_res .= "<td>\r\n";
                        $content_res .= "<td>\r\n";
                        $content_res .= "<div class='list-".$class_status."-big'>".$class_status_long."</div>\r\n";
                        $content_res .= "</td>\r\n";
                        $content_res .= "<td align='right' width='20%'>\r\n";
                        if($cfgLogoRight) {
                            $content_res .= "<img class='logo_right' src='img/logo_right.png' height='70px'>\r\n";
                        }
                        $content_res .= "</td>\r\n";
                        $content_res .= "</tr>\r\n";
                        $content_res .= "</table>\r\n";
                        $content_res .= "</div>\r\n";
                        
                        $content_res .= "<div data-role='content' id='content' data-theme='c'>\r\n";
                    
                        // print qualification mode if round selected
                        $info = '';
                        if($row_rnd['rnd_qualiSieger'] > 0 || $row_rnd['rnd_qualiLeistung'] > 0) {
                            $content_res .= "<div class='ui-corner-all ui-shadow' style='padding: 5px; font-size: 13px;'>\r\n";
                            $info = $GLOBALS['strQualification'].": "
                                    . $row_rnd['rnd_qualiSieger'] . $GLOBALS['strQualifyTop'].", "
                                    . $row_rnd['rnd_qualiLeistung'] . $GLOBALS['strQualifyPerformance'];
                            $content_res .= "<p>".$info."</p>\r\n";                      
                            
                            // print qualification descriptions if required 
                            $info = '';
     
                            foreach($cfgQualificationType as $qt)  
                            {
                                $info = $info . $qt['token'] . " = "
                                        . $qt['text'] . "&nbsp;&nbsp;&nbsp;";
                            }
                            $content_res .= "<p>".$info."</p>\r\n";
                            
                            $content_res .= "</div>\r\n";                    
                            
                            $qual_mode = TRUE;
                            
                            if($row_rnd['rnd_status'] == $cfgRoundStatus['results_done'])
                                {
                                    $qual_show = true;
                                } else{
                                    $qual_show = false;
                                }
                        }               
                        $event_act = $row_rnd['rnd_id_w'];    // keep event ID
                    }
                    
                    $relay = AA_checkRelay($row_rnd['rnd_id_w']);    // check, if this is a relay event
                    $svm = AA_checkSVM($row_rnd['rnd_id_w']);
                    
                                    
                    // If round evaluated per heat, group results accordingly    
                    $order_heat = "";
                    if ($row_rnd['rnd_status'] == $cfgRoundStatus['results_done']) {
                        if($eval == $cfgEvalType[$GLOBALS['strEvalTypeHeat']]) {    // eval per heat
                            $order_heat = "heat_id, ";
                        } 
                    } else{
                        if($row_rnd['rnd_status_timing'] == 1) {
                            $order_heat = "heat_id, ";                            
                        } else {
                            $order_heat = "";
                        }
                    }
                    
                    $valid_result ="";
                    
                    // Order performance depending on discipline type
                    $perf_sort = "r.Leistung";
                    
                    if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJumpNoWind']])
                        || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']]))
                    {
                        $order_perf = "DESC";
                    }
                    else if($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                    {
                        if ($row_rnd['rnd_wind'] == 1) {            // with wind
                            $order_perf = "DESC, r.Info ASC";
                        }
                        else {                            // without wind
                            $order_perf = "DESC";
                        }
                    }
                    else if($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']])
                    {
                        $order_perf = "DESC";
                        if ($row_rnd['rnd_status'] == $cfgRoundStatus['results_in_progress'] || $row_rnd['rnd_status'] == $cfgRoundStatus['results_live']){
                             $valid_result =    " AND (r.Info LIKE '%O%'"
                                                    . " OR r.Leistung < 0 OR r.Leistung is NULL)";                    
                        }
                        else {
                             $valid_result =    " AND (r.Info LIKE '%O%'"
                                                    . " OR r.Leistung < 0)";
                        }
                    }
                    else
                    {
                        $order_perf = "ASC";
                        $perf_sort = "IF(r.Leistung<=0 Or ISNULL(r.Leistung), 99999999, r.Leistung)";
                    }
                    
                    $order_rank = "res_rang_sort,";
                    //  TODO: ??                
                    if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeNone']])
                                            || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
                                            || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrackNoWind']])
                                            || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeDistance']])
                                            || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeRelay']]))
                    {
                        if ($row_rnd['rnd_status_timing'] == 1){  
                            if($relay) {
                                $order_rank = "res_perf_sort,";  
                            } else {
                                $order_rank = "res_perf_sort, res_pos,"; 
                            }
                            
                        } 
                    } else {
                    
                            if ($row_rnd['rnd_status'] == $cfgRoundStatus['results_in_progress'] || $row_rnd['rnd_status'] == $cfgRoundStatus['results_live']){  
                                if($relay) {
                                    $order_rank = "res_rang_sort,";  
                                } else {
                                    $order_rank = "res_rang_sort, res_pos,"; 
                                }
                                 
                            }
                        
                    }
                    
                    if($relay == FALSE) {                                 
                        $sql_res = "
                            SELECT 
                                ss.xSerienstart As res_id_ss, 
                                ss.Rang AS res_rang, 
                                IF(ss.Rang=0, 999999, ss.Rang) AS res_rang_sort, 
                                ss.Qualifikation As res_qualifikation, 
                                r.Leistung As res_perf,
                                ".$perf_sort." As res_perf_sort, 
                                r.Info, 
                                s.Bezeichnung As res_heat, 
                                s.Wind As res_wind, 
                                r.Punkte As res_punkte, 
                                IF('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)) As res_club, 
                                at.Name As res_name, 
                                at.Vorname As res_vorname, 
                                at.Jahrgang As res_yob, 
                                LPAD(s.Bezeichnung, 5, '0') As res_heat_format, 
                                IF(at.xRegion = 0, at.Land, re.Anzeige) AS res_land, 
                                at.xAthlet, 
                                ru.Datum, 
                                ru.Startzeit ,
                                ss.RundeZusammen,
                                ru.xRunde,  
                                k.Name As res_cat , 
                                k1.Name As res_cat_ath,                             
                                k1.Anzeige As res_catAnzeige_ath ,
                                ss.Bemerkung As res_remark,
                                w.Punkteformel,
                                w.info,
                                a.Startnummer As ath_bib,
                                s.Film As res_film,
                                ss.Bahn As res_bahn,
                                IF(ss.Position2 > 0, ss.Position2, ss.Position) As res_pos,
                                s.xSerie As heat_id
                            FROM 
                                athletica.serie AS s USE INDEX(Runde)
                                    LEFT JOIN athletica.serienstart AS ss USING(xSerie) 
                                    LEFT JOIN athletica.resultat AS r USING(xSerienstart) 
                                    LEFT JOIN athletica.start AS st ON(ss.xStart = st.xStart) 
                                    LEFT JOIN athletica.anmeldung AS a USING(xAnmeldung) 
                                    LEFT JOIN athletica.athlet AS at USING(xAthlet) 
                                    LEFT JOIN athletica.verein AS v USING(xVerein) 
                                    LEFT JOIN athletica.region AS re ON(at.xRegion = re.xRegion) 
                                    LEFT JOIN athletica.team AS t ON(a.xTeam = t.xTeam) 
                                    LEFT JOIN athletica.runde AS ru ON(s.xRunde = ru.xRunde) 
                                    LEFT JOIN athletica.wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                                    LEFT JOIN athletica.kategorie AS k On (w.xKategorie= k.xKategorie)
                                    LEFT JOIN athletica.kategorie AS k1 ON (a.xKategorie = k1.xKategorie)   
                            WHERE 
                                ".$roundSQL." 
                                ".$valid_result." 
                            ORDER BY 
                                ".$order_heat."                                
                                ".$order_rank."
                                at.Name, 
                                at.Vorname,
                                res_perf_sort " 
                                .$order_perf; 
                    }
                    else {                        // relay event
                        $sql_res = "
                            SELECT 
                                ss.xSerienstart As res_id_ss,                                   
                                ss.Rang AS res_rang, 
                                IF(ss.Rang=0, 999999, ss.Rang) AS res_rang_sort, 
                                ss.Qualifikation As res_qualifikation, 
                                r.Leistung As res_perf, 
                                ".$perf_sort." As res_perf_sort,
                                r.Info, 
                                s.Bezeichnung As res_heat, 
                                s.Wind As res_wind, 
                                r.Punkte As res_punkte, 
                                IF('".$svm."', t.Name, v.Name) As res_club, 
                                sf.Name As res_name, 
                                LPAD(s.Bezeichnung, 5, '0') AS res_heat_format, 
                                st.xStart As res_id_st, 
                                ru.Datum, 
                                ru.Startzeit, 
                                ss.RundeZusammen As res_zusammen,
                                ru.xRunde,
                                k.Name As res_cat,
                                ss.Bemerkung As res_remark,
                                s.Film As res_film,
                                ss.Bahn As res_bahn,
                                s.xSerie As heat_id    
                            FROM
                                athletica.serie AS s USE INDEX(Runde) 
                                    LEFT JOIN athletica.serienstart AS ss USING(xSerie) 
                                    LEFT JOIN athletica.resultat AS r USING(xSerienstart) 
                                    LEFT JOIN athletica.start AS st ON(ss.xStart = st.xStart) 
                                    LEFT JOIN athletica.staffel AS sf USING(xStaffel) 
                                    LEFT JOIN athletica.verein AS v USING(xVerein) 
                                    LEFT JOIN athletica.team AS t ON(sf.xTeam = t.xTeam) 
                                    LEFT JOIN athletica.runde AS ru ON(s.xRunde = ru.xRunde) 
                                    LEFT JOIN athletica.wettkampf AS w On (w.xWettkampf= st.xWettkampf)   
                                    LEFT JOIN athletica.kategorie AS k On (w.xKategorie= k.xKategorie) 
                            WHERE 
                                ".$roundSQL."  
                                ".$valid_result." 
                            GROUP BY 
                                ss.xSerienstart 
                            ORDER BY 
                                ".$order." 
                                ".$order_rank."
                                res_perf_sort 
                                ".$order_perf.", 
                                sf.Name
                            ";                         
                    }
                                                    
                    $res_res = mysql_query($sql_res);
                    if(mysql_errno() > 0) {        // DB error                 
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
                    else {
                        if (mysql_num_rows($res_res)==0){   
                            continue;             
                        }
                        
                        $ath_act = 0;
                        $heat_act = '';
                        $rang_act = '';
                        $div_collapsible = false;
                        $div_res = false;
                        $table = false;
                        
                        while($row_res = mysql_fetch_assoc($res_res)) {
                            if($row_res['res_id_ss'] != $ath_act)
                            { 
                                if(($row_res['heat_id'] != $heat_act && $eval == $cfgEvalType[$GLOBALS['strEvalTypeHeat']]) || $heat_act=='')
                                {
                                    $osvimg_name = str_pad($row_res['res_film'],3 ,'0', STR_PAD_LEFT)."01001.jpg";
                                                                        
                                    // heat name
                                    if($eval == $cfgEvalType[$GLOBALS['strEvalTypeHeat']]) {
                                        if(empty($roundName))    {            // no round type defined
                                            $roundName = $GLOBALS['strFinalround'] . " ";
                                        }
                                        $title = $roundName." ".$row_res['res_heat'];    // heat name with nbr.
                                    }
                                    else {
                                        $title = $roundName;    // heat name withour nbr.
                                    }
                                    
                                    $title = trim($title);
                    
                                    // wind per heat
                                    if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
                                            && ($row_rnd['rnd_wind'] == 1)
                                            && ($eval == $cfgEvalType[$GLOBALS['strEvalTypeHeat']]))
                                    {
                                        $heatwind = $row_res['res_wind'];        // wind per heat
                                    }
                                    else {
                                        $heatwind = '';                    // no wind 
                                    }

                                    $wind= FALSE;
                                    if(($row_rnd['rnd_typ'] == 1) 
                                        && ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']]) 
                                        || (($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']]) 
                                            && ($eval == $cfgEvalType[$GLOBALS['strEvalTypeAll']])))
                                    {
                                        $wind= TRUE;
                                    }

                                    // add column header 'points' if required
                                    $points= FALSE;
                                    if($row_rnd['rnd_points'] != '0') {
                                        $points= TRUE;
                                    }
                                    
                                    if($table){
                                        $content_res .= "</tbody>\r\n"; 
                                        $content_res .= "</table>\r\n";    
                                    }
                                    
                                    if($div_collapsible){
                                        $content_res .= "</div>\r\n";    
                                    }
                                    
                                    if($div_res){
                                        $content_res .= "</div>\r\n";    
                                    }
                                    
                                    if(!empty($title)) {
                                        $content_res .= "<div data-role='collapsible' data-theme='b' data-content-theme='d' data-collapsed='false' data-collapsed-icon='' data-expanded-icon='' data-inset='true'>\r\n";
                                        $div_collapsible = true;
                                    }
                                    
                                    // print heat header if title set (results evaluated per heat)
                                    if(!empty($title))
                                    {
                                        if(empty($heatwind))
                                        {                    
                                            $content_res .= "<h4>".$title."</h4>\r\n";
                                        }
                                        else
                                        {  
                                            $content_res .= "<h4>".$title."  (".$GLOBALS['strWind'].": ".$heatwind.")</h4>\r\n";
                                        }
                                    }    // ET heat title set
                                    
                                    if($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']]) {
                                        $str_Pos = $GLOBALS['strTrack'];    
                                    } else {
                                        $str_Pos = $GLOBALS['strPositionShort'];
                                    }
                                    if($cfgOSVIMG) {
                                        if(file_exists(dirname($_SERVER['SCRIPT_FILENAME'])."/tmp/".$osvimg_name)) {
                                            $content_res .= "<a href='osvimg/".$osvimg_name."' target='_blank'>Photofinish</a>";
                                            }
                                    }
                                    $content_res .= "<div class='ui-corner-all ui-shadow' style='padding: 5px;'>\r\n";
                                    
                                    $div_res = true;
                                    
                                    $content_res .= "<table class='table-results";
                                    if($show_all) {
                                        $content_res .= "-details";    
                                    }
                                    $content_res .= " ui-responsive table-stroke'>\r\n";
                                    $content_res .= "<thead>\r\n";
                                    $content_res .= "<tr>\r\n";
                                    if ($row_rnd['rnd_status'] == $cfgRoundStatus['results_done']){
                                        $content_res .= "<th class='rank'><span class='long'>".$GLOBALS['strRank']."</span><span class='short'>".$GLOBALS['strRankShort']."</span></th>\r\n";
                                    } else {
                                        if($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']]) {
                                            $content_res .= "<th class='rank'><span class='long'>".$str_Pos."</span><span class='short'>".$GLOBALS['strLaneShort']."</span></th>\r\n";    
                                        }
                                    }
                                    if ($row_rnd['rnd_status'] != $cfgRoundStatus['results_done']){
                                        $content_res .= "<th class='bib'>".$GLOBALS['strNbr']."</th>\r\n";
                                    }
                                    if($relay) {
                                        $content_res .= "<th class='name'>".$GLOBALS['strRelay']."</th>\r\n";
                                        
                                    } else {
                                        $content_res .= "<th class='name'>".$GLOBALS['strName']."</th>\r\n";
                                        $content_res .= "<th class='yob'>".$GLOBALS['strYearShort']."</th>\r\n";
                                        $content_res .= "<th class='country'>".$GLOBALS['strCountry']."</th>\r\n";    
                                    }
                                    if($svm){
                                        $content_res .= "<th class='club'>".$GLOBALS['strTeam']."</th>\r\n";
                                    } else{
                                        $content_res .= "<th class='club'>".$GLOBALS['strClub']."</th>\r\n";   
                                    }
                                    $content_res .= "<th class='result'><span class='long'>".$GLOBALS['strPerformance']."</span><span class='short'>".$GLOBALS['strPerformanceShort']."</span></th>\r\n";
                                    if($wind){
                                        $content_res .= "<th class='wind'>".$GLOBALS['strWind']."</th>\r\n";
                                    }
                                    if($points){
                                        $content_res .= "<th class='points'>".$GLOBALS['strPoints']."</th>\r\n";
                                    }
                                    if($qual_show) {
                                        $content_res .= "<th class='qual'></th>\r\n";
                                    }
                                    $content_res .= "<th class='remark'><span class='long'>".$GLOBALS['strResultRemark']."</span><span class='short'>".$GLOBALS['strResultRemarkShort']."</span></th>\r\n";
                                    $content_res .= "</tr>\r\n";
                                    $content_res .= "</thead>\r\n";
                                    $content_res .= "<tbody>\r\n";
                                    
                                    $table = true;
                                    
                                    if($cfgOSVIMG) {
                                        $ftp = new FTP_data();
                                        $ftp->open_connection($ftp_host, $ftp_user, $ftp_pwd);
                                        $ftp_tmp_path = "./tmp/";
                                        $ftp_tmp_name_round = $osvimg_name;                        
                                        $local = dirname($_SERVER['SCRIPT_FILENAME'])."/".$ftp_tmp_path.$ftp_tmp_name_round;       
                                        if (empty($GLOBALS['cfgDir'] )){
                                            $remote =  "osvimg/".$ftp_tmp_name_round;          
                                        }
                                        else {
                                            $remote =  $GLOBALS['cfgDir'] . "/osvimg/".$ftp_tmp_name_round;          
                                        } 
                                        
                                        $success = $ftp->put_file($local, $remote);
                                    }
                                    

                                } 
                                
                                // +++ result line
                                
                                
     
                                // rank
                                if(($row_res['res_rang'] == 0)         // invalid result
                                    || ($row_res['res_rang'] == $rang_act && $row_res['res_heat'] == $heat_act)) {        // same rank as previous
                                    $res_list_rang='';
                                }
                                else {
                                    $res_list_rang = $row_res['res_rang'];
                                }
                                $rang_act = $row_res['res_rang'];                // keep rank
                                
                                if ($row_rnd['rnd_status'] == $cfgRoundStatus['results_in_progress']){
                                   $res_list_rang = ''; 
                                }
                                
                                if($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']]) {
                                    $res_list_pos = $row_res['res_bahn'];
                                } else {
                                    $res_list_pos = $row_res['res_pos'];
                                }
                                
                                $res_list_bib = $row_res['ath_bib'];
                                
                                // name
                                if($relay == false) {
                                    $res_list_name = $row_res['res_name']." ".$row_res['res_vorname'];
                                } else {
                                    $res_list_name = $row_res['res_name'];   
                                }
                                
                                // yob
                                if($relay == false) {
                                    $res_list_yob = AA_formatYearOfBirth($row_res['res_yob']);
                                }
                                else {
                                    $res_list_yob = '';
                                }
                                
                                // country
                                if($relay == false) {
                                    $res_list_country = ($row_res['res_land'] != '-') ? $row_res['res_land'] : '';
                                }
                                else {
                                    $res_list_country = '';
                                }
                                
                                // club
                                $res_list_club = $row_res['res_club'];       
                                
                                // performance
                                if($row_res['res_perf'] == ""){
                                    $res_list_perf = "";    
                                }
                                elseif($row_res['res_perf'] < 0) {    // invalid result
                                    foreach($cfgInvalidResult as $value)    // translate value
                                    {
                                        if($value['code'] == $row_res['res_perf']) {
                                            $res_list_perf = $value['short'];
                                        }
                                    }
                                }
                                else if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                                    || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJumpNoWind']])
                                    || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']])
                                    || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']])) {
                                    $res_list_perf = AA_formatResultMeter($row_res['res_perf']);
                                }
                                else {
                                    if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
                                    || ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrackNoWind']])){
                                        $res_list_perf = AA_formatResultTime($row_res['res_perf'], true, true);
                                    }else{
                                        $res_list_perf = AA_formatResultTime($row_res['res_perf'], true);
                                    }
                                }
                                
                                // qual
                                if($row_res['res_qualifikation'] > 0 && $qual_show) {    // Athlete qualified
                                    foreach($cfgQualificationType as $qtype)
                                    {
                                        if($qtype['code'] == $row_res['res_qualifikation']) {
                                            $res_list_qual = $qtype['token'];
                                        }
                                    }
                                } else {
                                    $res_list_qual = "";
                                }
                                
                                // points
                                if($row_rnd['rnd_points'] != '0') {
                                    $res_list_points = $row_res['res_punkte'];
                                } else {
                                    $res_list_points = "";
                                }
                                
                                // wind info
                                $secondResult = false;
                                if($rang_act != 0 || $row_rnd['rnd_status'] == $cfgRoundStatus['results_in_progress'] || $row_rnd['rnd_status'] == $cfgRoundStatus['results_live'])    // valid result
                                {
                                    if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                                        && ($row_rnd['rnd_wind'] == 1))
                                    {
                                        $res_list_wind = $row_res[4];
                                        
                                        //
                                        // if wind bigger than max wind (2.0) show the next best result without wind too
                                        //
                                        if($res_list_wind > 2){
                                            $res_wind = mysql_query("
                                                    SELECT 
                                                        Info As wind_wind, 
                                                        Leistung As wind_perf
                                                    FROM
                                                        resultat
                                                    WHERE
                                                        xSerienstart = ".$row_res['res_id_ss']."
                                                    ORDER BY
                                                        Leistung ASC");
                                            if(mysql_errno() > 0) {        // DB error
                                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                            }else{
                                                while($row_wind = mysql_fetch_assoc($res_wind)){
                                                    
                                                    if($row_wind['wind_wind'] <= 2){
                                                        $secondResult = true;
                                                        $wind2 = $row_wind['wind_wind'].")";
                                                        $perf2 = "(".AA_formatResultMeter($row_wind['wind_perf']);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else if(($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
                                        && ($row_rnd['rnd_wind'] == 1)
                                        && ($eval == $cfgEvalType[$GLOBALS['strEvalTypeAll']])) 
                                    {
                                        $res_list_wind = $row_res['res_wind'];
                                    }
                                }
                                
                                // country
                                if($relay == false){
                                    $res_list_country = $row_res['res_land'];
                                } else {
                                    $res_list_country = "";
                                }
                                
                                // remark
                                $res_list_remark = $row_res['res_remark'];
                                
                                // show relay athletes
                                if($relay){  
                                    if ($row_res['res_zusammen'] > 0) {
                                        $sqlRound=$row_res['res_zusammen'];     // merged round
                                    } else {
                                        $sqlRound=$row_rnd['rnd_id_r'];
                                    } 
                                             
                                    $res_at = mysql_query("
                                            SELECT 
                                                at.Vorname As at_vorname,
                                                at.Name As at_name,
                                                at.Jahrgang As  at_yob
                                            FROM
                                                athletica.staffelathlet as sfat
                                                    LEFT JOIN athletica.start as st ON sfat.xAthletenstart = st.xStart
                                                    LEFT JOIN athletica.anmeldung as a USING(xAnmeldung)
                                                    LEFT JOIN athletica.athlet as at USING(xAthlet)
                                            WHERE
                                                sfat.xStaffelstart = ".$row_res['res_id_st']."
                                                AND    
                                                sfat.xRunde = $sqlRound 
                                            ORDER BY
                                                sfat.Position
                                            LIMIT ".$row_rnd['rnd_staffelLaeufer']."
                                    ");
                                    if(mysql_errno() > 0) {        // DB error
                                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                    }else{
                                        $text_att = "";
                                        while($row_at = mysql_fetch_assoc($res_at)){
                                            $text_att .= $row_at['at_name']." ".$row_at['at_vorname']." ".AA_formatYearOfBirth($row_at['at_yob'])." / ";
                                        }
                                        $text_att = substr($text_att, 0, (strlen($text_att)-2));
                                        
                                        
                                        $text_att = (trim($text_att)!='') ? '('.$text_att.')' : '';
                                    }
                                } elseif($show_all) // show all attempts
                                {
                                    $query_sort = ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']]) ? "ORDER BY Leistung ASC": "ORDER BY xResultat ASC";
                                        
                                    $res_att = mysql_query("
                                            SELECT * FROM 
                                                athletica.resultat 
                                            WHERE xSerienstart = ".$row_res['res_id_ss']."
                                            ".$query_sort."
                                            "); 
                                     
                                    if(mysql_errno() > 0) {        // DB error
                                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                    }else{
                                        $text_att = "";
                                        while($row_att = mysql_fetch_assoc($res_att)){
                                            if($row_att['Leistung'] < 0){
                                                $perf3 = $row_att['Leistung'];                                       
                                                if ($perf3 == $GLOBALS['cfgMissedAttempt']['db']){
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
                                                if ($saison == "O" ||  ($saison == "I"  && $row_rnd['rnd_typ'] != $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])) {        // outdoor  or (indoor and not jump)
                                                    if($row_att['Info'] != "-" && !empty($row_att['Info']) && $row_rnd['rnd_typ'] != $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']]){
                                                            
                                                            if ($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']]){
                                                                $text_att .= " , ".$row_att['Info'];  
                                                            }
                                                            else {
                                                                 if ($row_rnd['rnd_wind'] != 0){
                                                                    $text_att .= " , ".$row_att['Info'];   
                                                                 } 
                                                            }
                                                    }
                                                    elseif ($row_att['Info'] == "-"  && $row_rnd['rnd_typ'] != $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']] && $row_att['Leistung'] > 0){
                                                             $text_att .= " , ".$row_att['Info'];  
                                                    }  
                                                }                              
                                                $text_att .= " / ";
                                            }   
                                        }
                                        $text_att = substr($text_att, 0, (strlen($text_att)-2));
                                    }
                                }

                                $content_res .= "<tr>\r\n";
                                
                                if ($row_rnd['rnd_status'] == $cfgRoundStatus['results_done']){
                                    $content_res .= "<td class='rank'>".$res_list_rang."</td>\r\n";
                                } else {
                                    if($row_rnd['rnd_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']]) {
                                        $content_res .= "<td class='rank'>".$res_list_pos."</td>\r\n";    
                                    }
                                }
                                if ($row_rnd['rnd_status'] != $cfgRoundStatus['results_done']){
                                        $content_res .= "<td class='bib'>".$res_list_bib."\r\n";
                                }
                                $content_res .= "<td class='name'>".$res_list_name."\r\n";
                                if($show_all) {
                                    $content_res .= "<br>\r\n";
                                    if (empty($text_att)){
                                        $content_res .= "<span id='attempts'>".$GLOBALS['strAttempts']."</span>: \r\n";
                                    }
                                    else {
                                        $content_res .= "<span id='attempts'>".$GLOBALS['strAttempts'].": ( ".$text_att." )</span>\r\n"; 
                                    }
                                } elseif ($relay){
                                    $content_res .= "<br>\r\n";
                                    if (empty($text_att)){
                                        $content_res .= "";
                                    }
                                    else {
                                        $content_res .= "<span id='attempts'>".$text_att."</span>\r\n"; 
                                    }    
                                }
                                $content_res .= "</td>\r\n";
                                if($relay == false) {
                                    $content_res .= "<td class='yob'>".$res_list_yob."</td>\r\n";    
                                    $content_res .= "<td class='country'>".$res_list_country."</td>\r\n";    
                                }
                                $content_res .= "<td class='club'>".$res_list_club."</td>\r\n";
                                $content_res .= "<td class='result'>".$res_list_perf."</td>\r\n";
                                if($wind){
                                    $content_res .= "<td class='wind'>".$res_list_wind."</td>\r\n";
                                }
                                if($points){
                                    $content_res .= "<td class='points'>".$res_list_points."</td>\r\n";
                                }
                                if($qual_show){
                                    $content_res .= "<td class='qual'>".$res_list_qual."</td>\r\n";
                                }
                                $content_res .= "<td class='remark'>".$res_list_remark."</td>\r\n";
                                
                                                  
                                                            
                                // TODO: second result (wind)
                                
                                
                                $content_res .= "</tr>\r\n";
                                // --- result line
                                
                                $heat_act = $row_res['heat_id'];
                                $ath_act = $row_res['res_id_ss'];
                            }
                            
                        }            
                    
                    }
                    
                    
                }
                
                
                if($table){
                    $content_res .= "</tbody>\r\n"; 
                    $content_res .= "</table>\r\n";    
                }
                
                
                if($div_collapsible){
                    $content_res .= "</div>\r\n";    
                }
                
                if($div_res){
                    $content_res .= "</div>\r\n";    
                }
                
                $content_res .= "</div>\r\n";
                
                $content_res .= "<div data-role='footer' data-theme='b' data-id='footer' data-position='fixed' data-tap-toggle='false'>\r\n";
                if($cfgLogoFooter) {
                    $content_res .= "<div align='center'><img src='img/footer.png' width='100%'></div>\r\n";
                }
                $content_res .= "</div>\r\n";
                
                $content_res .= "</div>\r\n";
                
                $content_res .= "<?php\r\n ";
                $content_res .= "include('include/footer.php');\r\n ";
                $content_res .= "?>\r\n ";
            }
            
        }
        
        if (!fwrite($fp, utf8_encode($content_res))) {
            AA_printErrorMsg($GLOBALS['strErrFileWriteFailed']);    
            return;
        }  

        fclose($fp);
               
                        

        // will not be uploaded the next time per 
        //AA_UpdateStatusChanged($round);


    }	// end function AA_rankinglist_Single

}	// AA_RANKINGLIST_SINGLE_LIB_INCLUDED
?>
