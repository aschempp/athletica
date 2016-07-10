<?php

/**********
 *
 *	timetable extension
 *	-------------------
 * The timetable function is used to print an overview of all events
 * of a meeting.   
 */
if (!defined('AA_TIMETABLE_LIB_INCLUDED'))
{
	define('AA_TIMETABLE_LIB_INCLUDED', 1);



    /**
     *	show timetable
     *	-------------------
     */
    function AA_timetable_display()
    {       
        require_once('./lib/cl_http_data.lib.php'); //include class      
        require_once('./lib/common.lib.php'); //include class  
     
        require('./config.inc.php');  
        require('./config.inc.end.php');    
        
        require('./lib/rankinglist_single.lib.php'); 
        require('./lib/rankinglist_combined.lib.php'); 
        require('./lib/results_track.lib.php');  
        require('./lib/results_tech.lib.php');      
        require("./lib/cl_ftp_data.lib.php"); 
        
        if(AA_connectToDB() == FALSE)    { // invalid DB connection
            return;        // abort
        } 
              
        $res_ftp = mysql_query("
            SELECT
                *
            FROM
                athletica_liveResultate.config");
        if(mysql_errno() > 0) {
            
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
            
            $row_ftp = mysql_fetch_assoc($res_ftp);
            $ftp_host = $row_ftp['ftpHost'];
            $ftp_user = $row_ftp['ftpUser'];    
            $ftp_pwd = $row_ftp['ftpPwd'];    
            $url = $row_ftp['url'];   
            mysql_free_result($result);
        }
        $ftp = new FTP_data();
        
        if(AA_connectToDB_live() == FALSE)    { // invalid DB connection
            return;        // abort
        }     
           
	    $sql_meeting = "
		    SELECT
                m.xMeeting As meeting_id,
			    m.Name As meeting_name,
                m.StatusChanged As meeting_statusChanged
		    FROM
			    athletica.meeting AS m
		    WHERE m.xMeeting = " . $_COOKIE['meeting_id'] . "  		
	    ";
        
        $res_meeting = mysql_query($sql_meeting);
        $row_meeting = mysql_fetch_assoc($res_meeting);
        
	    if(mysql_errno() > 0)	// DB error
	    {
             
		    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	    }
	    else {			// no DB error
            $m_statusChanged = $row_meeting['meeting_statusChanged'];
            
            
          
            // +++ timetableX.php
            $t = 1;
               
            $ftp_tmp_path = "./tmp/";
            $ftp_tmp_name = "timetable".$t.".php";
            $fp = @fopen($ftp_tmp_path.$ftp_tmp_name,'w');

            if(!$fp){     
                AA_printErrorMsg($GLOBALS['strErrFileOpenFailed']);  
                return;
            }
            
            $content  = "<?php\r\n ";
            $content .= "include('include/header.php');\r\n ";
            $content .= "?>\r\n ";
            
            
            
            $content .= "<div data-role='page' id='page' data-title='Live Resultate'>\r\n ";
            $content .= "<div data-role='header' data-theme='b' data-id='header' data-position='block' data-tap-toggle='false'>\r\n";
            if($cfgLogoHeader) {
                $content .= "<div class='header_img' align='center' style='display: block;'><img src='img/header.png' width='100%'></div>\r\n";
            }
            $content .= "</div>\r\n";
            $content .= "<div data-role='header' data-theme='b' data-id='header' data-position='block' data-tap-toggle='false'>\r\n ";
            $content .= "<a href='../index.php' data-icon='home' data-transition='slide' data-direction='reverse'>Home</a>\r\n";
            $content .= "<a class='ui-btn-right' data-icon='refresh' onclick='refreshPage();'>refresh</a>\r\n";
            $content .= "<h1>&copy; Dominik Hadorn, 2015</h1>\r\n";
            $content .= "</div>\r\n";
            
            
            $content .= "<div data-role='content' id='content' data-theme='c'>\r\n";
            // $content .= "<h3>".$row_meeting['meeting_name']."</h3>\r\n";
            
            $sql_cat = "
                SELECT DISTINCT
                    k.xKategorie As cat_id,
                    k.Name As cat_name
                FROM
                    athletica.runde AS r
                    LEFT JOIN athletica.wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
                    LEFT JOIN athletica.kategorie AS k ON (w.xKategorie = k.xKategorie)
                WHERE w.xMeeting = '" . $row_meeting['meeting_id'] . "'       
                ORDER BY
                    k.Anzeige
            ";
            
            $res_cat = mysql_query($sql_cat);
    
            if(mysql_errno() > 0)    // DB error
            {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                $content .= "<div data-role='listview' data-inset='true' data-filter='true' data-filter-placeholder='".$GLOBALS['strSearch']."' data-children='> div, > div table tr'>\r\n";
                
                while ($row_cat = mysql_fetch_assoc($res_cat)) {
                    $content .= "<div data-role='collapsible' data-theme='b' data-content-theme='d' data-collapsed='false' data-collapsed-icon='plus' data-expanded-icon='minus' data-inset='true'>\r\n";        
                    $content .= "<h2>".$row_cat['cat_name']."</h2>\r\n"; 
                           
                    $content .= "<table class='table-timetable ui-responsive table-stroke'>\r\n";
                    $content .= "<tbody>\r\n";
                    
                    // +++ MK
                    
                    $sql_comb = "
                        SELECT
                            w.Mehrkampfcode As comb_id,
                            d.Name as comb_name
                        FROM
                            athletica.wettkampf As w
                            LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d ON (w.Mehrkampfcode = d.Code)
                        WHERE w.xMeeting = '" . $row_meeting['meeting_id'] . "'
                            AND w.xKategorie = '" . $row_cat['cat_id'] ."'
                            AND w.Mehrkampfcode > 0                                    
                        GROUP BY
                            comb_id    
                        ";
                        
                        
                    $res_comb = mysql_query($sql_comb);
    
                    if(mysql_errno() > 0)    // DB error
                    {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
                    else {
                        
                        if(mysql_num_rows($res_comb) > 0) {
                            while($row_comb = mysql_fetch_assoc($res_comb))
                            {
                                $class = "results_combined";
                                $class_short = "MK";
                                $class_long = "Mehrkampf";
                                
                                $content .= "<tr onclick=\"document.location='liveComb".$row_comb['comb_id']."_".$row_cat['cat_id'].".php'\">\r\n";
                                $content .= "<td class='dis'>".$row_comb['comb_name']."</td>\r\n";
                                $content .= "<td class='time'></td>\r\n";
                                $content .= "<td class='status'><span class='long'><span class='list-".$class."-big'>".$class_long."</span></span><span class='short'><span class='list-".$class."-small'>".$class_short."</span></span></td>\r\n";
                                $content .= "</tr>\r\n";
                                
                                $ftp_tmp_name_round = "liveComb".$row_comb['comb_id']."_".$row_cat['cat_id'].".php";
                                
                                
                                AA_rankinglist_Combined($row_comb['comb_id'], $row_cat['cat_id'], $row_meeting['meeting_id'], $t, $class, $class_long); 
                                
                                $ftp->open_connection($ftp_host, $ftp_user, $ftp_pwd);
                                
                                $local = dirname($_SERVER['SCRIPT_FILENAME'])."/".$ftp_tmp_path.$ftp_tmp_name_round;       
                                if (empty($GLOBALS['cfgDir'] )){
                                    $remote =  $ftp_tmp_name_round;          
                                }
                                else {
                                    $remote =  $GLOBALS['cfgDir'] . "/".$ftp_tmp_name_round;          
                                } 
                                $success = $ftp->put_file($local, $remote);
                                 
                            }
                        }
                        
                    } 
                    
                    // --- MK   
                    
                    $sql_rnd = "
                        SELECT
                            r.xRunde As rnd_id_r,
                            w.xWettkampf As rnd_id_w,
                            d.Kurzname As rnd_dis_kurz,
                            d.Name As rnd_dis,
                            TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat') As rnd_starttime,
                            DATE_FORMAT(r.Datum, '$cfgDBdateFormat') As rnd_date,
                            rt.Typ As rnd_typ,
                            rt.Name As rnd_typName,
                            w.Info As rnd_info,
                            w.Mehrkampfende As rnd_mkEnde,
                            r.Gruppe As rnd_gruppe,
                            r.Status As rnd_status,
                            r.StatusChanged As rnd_rundeStatusChanged,
                            m.StatusChanged As rnd_meetingStatusChanged,
                            r.StatusZeitmessung As rnd_status_timing
                        FROM
                            athletica.runde AS r
                            LEFT JOIN athletica.wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
                            LEFT JOIN athletica.kategorie AS k ON (w.xKategorie = k.xKategorie)
                            LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt
                                ON r.xRundentyp = rt.xRundentyp
                            LEFT JOIN athletica.start AS s
                                ON w.xWettkampf = s.xWettkampf
                                AND s.Anwesend = 0
                                AND ((d.Staffellaeufer = 0
                                AND s.xAnmeldung > 0)
                                OR (d.Staffellaeufer > 0
                                AND s.xStaffel > 0))
                            LEFT JOIN athletica.rundenset AS rs ON (rs.xRunde = r.xRunde AND rs.xMeeting = " . $row_meeting['meeting_id'] .") 
                            LEFT JOIN athletica.meeting AS m ON (w.xMeeting = m.xMeeting)
                        WHERE w.xMeeting = '" . $row_meeting['meeting_id'] . "'
                            AND w.xKategorie = '" . $row_cat['cat_id'] ."'          
                            
                        GROUP BY
                            r.xRunde
                            , s.xWettkampf
                        ORDER BY
                            r.Datum
                            , r.Startzeit                
                            , k.Kurzname
                            , d.Anzeige
                        ";
                                                
                    $res_rnd = mysql_query($sql_rnd);
    
                    if(mysql_errno() > 0)    // DB error
                    {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
                    else {
                        while ($row_rnd = mysql_fetch_assoc($res_rnd)) {
                            $ftp_tmp_name_round = "live".$row_rnd['rnd_id_r'].".php";
                            
                            if($row_rnd['rnd_mkEnde'] != 1 && !empty($row_rnd['rnd_gruppe'])) {
                                $combGroup = "&nbsp;g".$row_rnd['rnd_gruppe'];
                            }
                            
                            $typ = ($row_rnd['rnd_typ'] != "" && $row_rnd['rnd_typ'] != '0' && $row_rnd['rnd_typ'] !='D') ? "&nbsp;".$row_rnd['rnd_typ'] : "";
                            $info = ($row_rnd['rnd_info'] != "") ? "&nbsp;<span class='starttime'>(".$row_rnd['rnd_info'].")</span>" : "";
                            
                            $class = "athletes";
                            $status = $row_rnd['rnd_status'];
                            
                            if($row_rnd['rnd_status_timing'] == 1 && $row_rnd['rnd_status'] != $cfgRoundStatus['results_done']){
                                $status = $cfgRoundStatus['results_live'];
                                $m_statusChanged = 'y';
                            }
                             
                            
                            switch($status) {
                                case  $cfgRoundStatus['results_done']:
                                    $class = "results";
                                    $class_short = "R";
                                    $class_long = "Rangliste";
                                    break; 
                                case  $cfgRoundStatus['results_live']:
                                    $class = "live";
                                    $class_short = "L";
                                    $class_long = "Live";
                                    break;
                                case  $cfgRoundStatus['results_in_progress']:
                                    $class = "results_pending";
                                    $class_short = "R";
                                    $class_long = "Resultate";
                                    break;
                                case  $cfgRoundStatus['heats_in_progress']:
                                    $class = "athletes";
                                    $class_short = "T";
                                    $class_long = "Teilnehmer";
                                    break;
                                case  $cfgRoundStatus['heats_done']:
                                    $class = "startlist";
                                    $class_short = "S";
                                    $class_long = "Startliste";
                                    break;
                                case  $cfgRoundStatus['open']:
                                    $class = "athletes";
                                    $class_short = "T";
                                    $class_long = "Teilnehmer";
                                    break;
                                case  $cfgRoundStatus['enrolement_done']:
                                    $class = "athletes";
                                    $class_short = "T";
                                    $class_long = "Teilnehmer";
                                    break;
                                case  $cfgRoundStatus['enrolement_pending']:
                                    $class = "athletes";
                                    $class_short = "T";
                                    $class_long = "Teilnehmer";
                                    break;  
                            }
                            
                            $content .= "<tr onclick=\"document.location='live".$row_rnd['rnd_id_r'].".php'\">\r\n";
                            $content .= "<td class='dis'>".$row_rnd['rnd_dis_kurz'].$typ.$combGroup.$info."</td>\r\n";
                            $content .= "<td class='time'>".$row_rnd['rnd_starttime']."&nbsp;&nbsp;&nbsp;(".$row_rnd['rnd_date'].")</td>\r\n";
                            $content .= "<td class='status'><span class='long'><span class='list-".$class."-big'>".$class_long."</span></span><span class='short'><span class='list-".$class."-small'>".$class_short."</span></span></td>\r\n";
                            $content .= "</tr>\r\n";
    
                            
                            
                            if($row_rnd['rnd_rundeStatusChanged'] == 'y' || $GLOBALS['arg'] == 'start' || ($row_rnd['rnd_status_timing'] == 1 && $row_rnd['rnd_status'] != $cfgRoundStatus['results_done'])) {
                                
                                if ($status == $cfgRoundStatus['results_done'] || $status == $cfgRoundStatus['results_in_progress'] || $status == $cfgRoundStatus['results_live'] ) {                                 
                                        AA_rankinglist_Single($row_rnd['rnd_id_r'], $row_rnd['rnd_id_w'], $row_meeting['meeting_id'], false, $t, $class, $class_long, $ftp_host, $ftp_user, $ftp_pwd);
                                                                             
                                        
                                        
                                } else {
                                    
                                    $layout = AA_getDisciplineType($row_rnd['rnd_id_r']);    // type determines layout
                                    
                                    // track disciplines, with or without wind
                                    if(($layout == $cfgDisciplineType[$strDiscTypeNone])
                                            || ($layout == $cfgDisciplineType[$strDiscTypeTrack])
                                            || ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                                            || ($layout == $cfgDisciplineType[$strDiscTypeDistance])
                                            || ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
                                    {  
                                        AA_results_Track($row_rnd['rnd_id_r'], $layout, $row_cat['cat_name'], $row_rnd['rnd_dis'] ,$row_rnd['rnd_typName'], $row_rnd['rnd_id_w'], $t, $class, $class_long, $row_rnd['rnd_status']);
                                    }
                                    // technical disciplines
                                    else
                                    {
                                        AA_results_Tech($row_rnd['rnd_id_r'], $layout, $row_cat['cat_name'], $row_rnd['rnd_dis'] ,$row_rnd['rnd_typName'], $row_rnd['rnd_id_w'], $t, $class, $class_long, $row_rnd['rnd_status']);
                                    }
                                }
                                
                                $ftp->open_connection($ftp_host, $ftp_user, $ftp_pwd);
                                
                                $local = dirname($_SERVER['SCRIPT_FILENAME'])."/".$ftp_tmp_path.$ftp_tmp_name_round; 
                                   
                                if (empty($GLOBALS['cfgDir'] )){
                                    $remote =  $ftp_tmp_name_round;          
                                }
                                else {
                                    $remote =  $GLOBALS['cfgDir'] . "/".$ftp_tmp_name_round;          
                                } 
                                $success = $ftp->put_file($local, $remote);
                                
                                AA_UpdateStatusChanged($row_rnd['rnd_id_r']);
                            }    
                        }
                    }         
                    $content .= "</tbody>\r\n";           
                    $content .= "</table>\r\n";           
                    $content .= "</div>\r\n";
                               
                }
                $content .= "</div>\r\n";

                $content .= "<div data-role='footer' data-theme='b' data-id='footer' data-position='fixed' data-tap-toggle='false'>\r\n";
                if($cfgLogoFooter) {
                    $content .= "<div align='center'><img src='img/footer.png' width='100%'></div>\r\n";
                }
                $content .= "</div>\r\n";

                $content .= "</div>\r\n";
                $content .= "</div>\r\n";
                $content .= "<?php\r\n ";
                $content .= "include('include/footer.php');\r\n ";
                $content .= "?>\r\n ";
            } 
            $t++;
            
            if (!fwrite($fp, utf8_encode($content))) {
                AA_printErrorMsg($GLOBALS['strErrFileWriteFailed']);    
                return;
            } else{
                // send files per ftp
                $local = dirname($_SERVER['SCRIPT_FILENAME'])."/".$ftp_tmp_path.$ftp_tmp_name;
                if (empty($GLOBALS['cfgDir'] )){
                     $remote = $ftp_tmp_name;       
                }
                else {
                     $remote = $GLOBALS['cfgDir'] . "/".$ftp_tmp_name;       
                }
                   
                // upload result file  
                
                $ftp->open_connection($ftp_host, $ftp_user, $ftp_pwd);
                
                if ($m_statusChanged == 'y') {
                    $success = $ftp->put_file($local, $remote);
                    if(!$success){
                         AA_printErrorMsg($strErrFtpNoPut); 
                    }
                }
                
            }
            
            // --- timetableX.php
            
	    }		// ET DB timetable item error     
       
              
          
       
        $ftp->close_connection();          
                                   
       
    }        // End AA_timetable_display

}		// AA_TIMETABLE_LIB_INCLUDED
?>