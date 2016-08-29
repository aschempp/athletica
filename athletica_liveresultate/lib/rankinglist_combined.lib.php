<?php

/**********
 *
 *	rankinglist single events
 *	
 */

if (!defined('AA_RANKINGLIST_COMBINED_LIB_INCLUDED'))
{
	define('AA_RANKINGLIST_COMBINED_LIB_INCLUDED', 1);

    function AA_rankinglist_Combined($comb_id, $cat_id, $meeting, $id_back, $class_status, $class_status_long)
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
        $fp = @fopen($p."/liveComb".$comb_id."_".$cat_id.".php",'w');
        if(!$fp){
            AA_printErrorMsg($GLOBALS['strErrFileOpenFailed']);  
            return;
        }
        
        // get athlete info per contest category    
        $sql_comb="SELECT DISTINCT 
                a.xAnmeldung As ath_id
                , at.Name As ath_name
                , at.Vorname As ath_vorname
                , at.Jahrgang As ath_yob
                , k.Name
                , IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo) As ath_club
                , IF(at.xRegion = 0, at.Land, re.Anzeige) As ath_region
                , w.Mehrkampfcode        
                , d.Name As comb_name
                , w.xKategorie
                , ka.Code As cat_code
                , ka.Name As cat_name
                , ka.Alterslimite As cat_limit 
                , d.Code As dis_code 
                , at.xAthlet
                , at.Geschlecht As ath_sex               
            FROM
                athletica.anmeldung AS a
                LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet )
                LEFT JOIN athletica.verein AS v  ON (v.xVerein = at.xVerein  )
                LEFT JOIN athletica.start as st ON (st.xAnmeldung = a.xAnmeldung ) 
                LEFT JOIN athletica.wettkampf as w  ON (w.xWettkampf = st.xWettkampf)
                LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " as d ON (w.Mehrkampfcode = d.Code)
                LEFT JOIN athletica.kategorie AS k ON (k.xKategorie = w.xKategorie)
                LEFT JOIN athletica.kategorie AS ka ON (ka.xKategorie = a.xKategorie)     
                LEFT JOIN athletica.region as re ON (at.xRegion = re.xRegion) 
            WHERE w.xMeeting  = " . $meeting . "
                AND w.xKategorie = ".$cat_id." 
                AND w.Mehrkampfcode = ".$comb_id."
            AND st.anwesend = 0  
            ORDER BY 
                k.Anzeige
                , w.Mehrkampfcode 
                , ka.Alterslimite DESC
            ";      
                     
        $res_comb = mysql_query($sql_comb);     
        
        if(mysql_errno() > 0)    // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
            $catEntry = '';
            $catEntryLimit = "";
            $u23name = "";
            $comb = 0; // hold combined type
            $combName = "";
            $lastTime = ""; // hold start time of last event for print list
            $a = 0;
            $info = '';
            $points = 0;
            $sep = '';
            $title=false;
            
            $content_res = "<?php\r\n ";
            $content_res .= "include('include/header.php');\r\n ";
            $content_res .= "?>\r\n ";
            $content_res .= "<div data-role='page' id='page' data-title='".$GLOBALS['strLiveResults']."'>\r\n";
            $content_res .= "<div data-role='header' data-theme='b' data-id='header' data-position='fixed' data-tap-toggle='false'>\r\n";
            $content_res .= "<a href='timetable".$id_back.".php' data-icon='back' data-transition='slide' data-direction='reverse'>".$GLOBALS['strBack']."</a>\r\n";
            $content_res .= "<a data-icon='refresh' onclick='refreshPage();'>".$GLOBALS['strRefresh']."</a>\r\n";
            
            while($row_comb = mysql_fetch_assoc($res_comb))
            {
                if($title==false) {
                    $content_res .= "<h1>".$row_comb['comb_name']." - ".$row_comb['cat_name']."</h1>\r\n";
                        
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
                    
                    $content_res .= "<div class='ui-corner-all ui-shadow' style='padding: 5px;'>\r\n";
                    
                    $content_res .= "<table class='table-results-combined ui-responsive table-stroke'>\r\n";
                    $content_res .= "<thead>\r\n";
                    $content_res .= "<tr>\r\n";
                    $content_res .= "<th class='rank'><span class='long'>".$GLOBALS['strRank']."</span><span class='short'>".$GLOBALS['strRankShort']."</span></th>\r\n";
                    $content_res .= "<th class='name'>".$GLOBALS['strName']."</th>\r\n";
                    $content_res .= "<th class='yob'>".$GLOBALS['strYearShort']."</th>\r\n";
                    $content_res .= "<th class='country'>".$GLOBALS['strCountry']."</th>\r\n";    
                    $content_res .= "<th class='club'>".$GLOBALS['strClub']."</th>\r\n";   
                    $content_res .= "<th class='points'>".$GLOBALS['strPoints']."</th>\r\n";
                    $content_res .= "</tr>\r\n";
                    $content_res .= "</thead>\r\n";
                    
                    $title=true;
                }
                
               
                $dCode = $row_comb['dis_code']; 
            
                // store previous before processing new athlete
                if(($a != $row_comb['ath_id'])        // new athlete
                    && ($a > 0))            // first athlete processed
                {                      
                    
                        $points_arr[] = $points;  

                        $points_arr_more_disc_all[$cat_id][] = $points_disc;
                                             
                        $name_arr[] = $name; 
                                       
                        $year_arr[] = $year;
                        $club_arr[] = $club;
                        $info_arr[] = $info;
                        $ioc_arr[] = $ioc;
                        $x_arr[] = $a;
                        $rank_arr[] = $rank;   
                                        
                        $info = '';
                        $points = 0;
                        $sep = '';
                
                    
                }
                 
                
                $catEntry = $row_comb['cat_code'];
                $catEntryLimit = $row_comb['cat_limit'];
                $combName = $row_comb['comb_name'];
                
                $order = " w.Mehrkampfreihenfolge ASC, ru.Datum, ru.Startzeit"; 
                $selectionMk = " AND w.Mehrkampfcode = " .$comb_id;               
                $selectionDisc = '';    
                
                // events  
                $sql_res="SELECT
                        d.Kurzname As dis_name
                        , d.Typ dis_typ
                        , MAX(IF ((r.Info='-') && (d.Typ = 6) ,0,r.Leistung)) As res_perf
                        , r.Info As res_info
                        , MAX(IF ((r.Info='-') && (d.Typ = 6),0,r.Punkte)) AS res_pts    
                        , s.Wind As res_wind
                        , w.Windmessung As dis_wind
                        , st.xStart As start_id
                        , CONCAT(DATE_FORMAT(ru.Datum,'$cfgDBdateFormat'), ' ', TIME_FORMAT(ru.Startzeit, '$cfgDBtimeFormat')) As res_time
                        , w.Mehrkampfreihenfolge 
                        , ss.Bemerkung As res_remark
                        , w.info As comp_info
                        , ss.xSerienstart 
                        , d.Code
                    FROM
                        athletica.start AS st USE INDEX (Anmeldung)
                        LEFT JOIN athletica.serienstart AS ss ON (ss.xStart = st.xStart )
                        LEFT JOIN athletica.resultat AS r ON (r.xSerienstart = ss.xSerienstart) 
                        LEFT JOIN athletica.serie AS s ON (s.xSerie = ss.xSerie)
                        LEFT JOIN athletica.runde AS ru ON (ru.xRunde = s.xRunde)
                        LEFT JOIN athletica.wettkampf AS w ON (w.xWettkampf = st.xWettkampf)
                        LEFT JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                    WHERE st.xAnmeldung = ".$row_comb['ath_id']."  
                        $selectionDisc 
                        AND ( (r.Info = '" . $cfgResultsHighOut . "' && d.Typ = 6 && r.Leistung < 0)  OR  (d.Typ = 6 && (r.Info !=  '" . $cfgResultsHighOut . "' && r.Info !=  '" . $cfgResultsHighOut1 . "' 
                                                         && r.Info !=  '" . $cfgResultsHighOut2 . "'  && r.Info !=  '" . $cfgResultsHighOut3 . "'  && r.Info !=  '" . $cfgResultsHighOut4 . "'
                                                         && r.Info !=  '" . $cfgResultsHighOut5 . "' && r.Info !=  '" . $cfgResultsHighOut6 . "' && r.Info !=  '" . $cfgResultsHighOut7 . "' && r.Info !=  '" . $cfgResultsHighOut7 . "'))
                              OR (d.Typ != 6 ) )   
                        
                       AND w.xKategorie = ".$cat_id."
                        $selectionMk   
                        AND ru.Status = " . $cfgRoundStatus['results_done'] . "   
                    GROUP BY
                        st.xStart
                    ORDER BY
                        $order";                     
                       
                $res_res = mysql_query($sql_res);    
               
                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                
                else{
                    $count_disc=0;
                    $remark='';
                    $points_disc = array();
                     
                    while($row_res = mysql_fetch_assoc($res_res))
                    {
                        $remark=$row_res['res_remark'];  
                        $lastTime = $row_res['res_time'];
                        
                        if($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']]){
                            $res2 = mysql_query("SELECT r.Info FROM 
                                        resultat as r
                                        LEFT JOIN serienstart as ss USING(xSerienstart)
                                    WHERE
                                        ss.xStart = ".$row_res['start_id']."
                                    AND    r.Punkte = ".$row_res['res_pts']);
                            $row2 = mysql_fetch_array($res2);
                            $row_res['res_info'] = $row2[0];
                        }
                        
                        // set wind, if required
                        if($row_res['dis_wind'] == 1)
                        {
                            if($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']]) {
                                $wind = " / " . $row_res['res_wind'];
                            }
                            else if($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']]) {
                                $wind = " / " . $row_res['res_info'];
                            }
                        }
                        else {
                            $wind = '';
                        }
                        
                        // format output
                        if(($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJump']])
                            || ($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeJumpNoWind']])
                            || ($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeThrow']])
                            || ($row_res['dis_typ'] == $cfgDisciplineType[$GLOBALS['strDiscTypeHigh']])) {
                            $perf = AA_formatResultMeter($row_res['res_perf']);
                        }
                        else {
                            $perf = AA_formatResultTime($row_res['res_perf'], true);
                        }
                        
                        if($row_res['res_pts'] > 0) {       // any points for this event 

                            $points = $points + $row_res['res_pts'];      // calculate points  
                               
                            if ($comb_id == 408) {                // UBS Kids Cup
                                switch ($row_res['dis_typ']){
                                    case 1:
                                    case 2: $c=1;          // track
                                        break;
                                    case 4:
                                    case 5: 
                                    case 6: $c=2;          // jump and high
                                        break; 
                                    case 8: $c=3;          // throw
                                        break;  
                                    default: $c=0;
                                        break;
                                }
                                $points_disc[$c]=$row_res['res_pts'];
                            } 
                            else {
                                $points_disc[$cat_id]=$row_res['res_pts'];       
                            }
                               
                            $info = $info . $sep . $row_res['dis_name'] . " " . "&nbsp;(" . $perf . $wind . ", ".$row_res['res_pts'].")";                      
                            $sep = ", ";     
                        }
                        elseif ($row_res['res_pts'] == 0 && $row_res['res_perf'] >= 0){          //  athlete with 0 points                                   
                                $info = $info . $sep . $row_res['dis_name'] . " " . "&nbsp;(" . $perf . $wind . ", ".$row_res['res_pts'].")";                      
                                $sep = ", ";       
                        }  
                        else{ 
                            $count_disc--;   
                            $pointTxt="" ;   
                            foreach($cfgInvalidResult as $value)    // translate value
                                {
                                 if($value['code'] == $perf) {
                                    $pointTxt = $value['short'];
                                 }
                            }  

                            $info = $info . $sep . $row_res['dis_name'] . $row_res['comp_info'] . "&nbsp;(" . $perf . $wind . ", ".$pointTxt.")";                      
                            $sep = ", ";     
                       }
                    }
                }
                
                $a = $row_comb['ath_id'];
                $name = $row_comb['ath_name'] . " " . $row_comb['ath_vorname'];
                $year = AA_formatYearOfBirth($row_comb['ath_yob']);
                $birthDate = $row_comb['ath_yob'];
                $sex = $row_comb['ath_sex'];
                $club = $row_comb['ath_club'];
                $ioc = $row_comb['ath_region'];       
                $remark_arr[] = $remark;             
                $points_disc_keep = $points_disc;
                $dCode_keep = $dCode;
            }
            
            if(!empty($a))        // add last athlete if any
            {
                $points_arr[] = $points;    
                $points_arr_more_disc_all[$cat_id][] = $points_disc; 
                $name_arr[] = $name;
                $year_arr[] = $year;
                $club_arr[] = $club;
                $info_arr[] = $info;
                $ioc_arr[] = $ioc;
                $x_arr[] = $a;
                $remark_arr[] = $remark;
                $rank_arr[] = $rank;
            }
            
            arsort($points_arr, SORT_NUMERIC);    // sort descending by points
        
            $rank = 1;                                    // initialize rank
            $r = 0;                                        // start value for ranking
            $p = 0;  
            $k = 0;  
            
            $no_rank=999999;
            $max_rank=$no_rank;       
                    
            foreach($points_arr as $key => $val)
            {    
                $r++;                           
                
                if($p != $val) {    // not same points as previous athlete
                    $rank = $r;        // next rank
                }
                                                
                // not set rank for invalid results 
                if (preg_match("@\(-[1]{1}@", $info_arr[$key])){ 
                    $rank=$max_rank; 
                    $max_rank+=1;      
                    $r--;  
                 }     
                            
                $p = $val;            // keep current points
                $k = $key;            // keep current key
                $rank_arr[$key]  = $rank;   
            }   
                  
            asort($rank_arr, SORT_NUMERIC);    // sort descending by rank       
            
             $rank_keep = 0; 
             
             foreach($rank_arr as $key => $v){
                    $val=$points_arr[$key];  
                    $rank=$v;   
                   
                    if ($rank == $rank_keep){                       
                            $c=0;
                            $keep_c=0;
                            // first rule 
                            for ($i=1; $i <= sizeof($points_arr_more_disc_all[$cat_id][$key]); $i++){                                 
                                 if  ($points_arr_more_disc_all[$cat_id][$key_keep][$i] > $points_arr_more_disc_all[$cat_id][$key][$i]){
                                      $keep_c ++;
                                 }
                                 else {
                                     $c++;
                                 }
                            }
                            
                            $more=ceil(sizeof($points_arr_more_disc_all[$cat_id][$key])/2);  
                            if (sizeof($points_arr_more_disc_all[$cat_id][$key]) % 2 == 0){              // combined with even number discs
                                 $more++;                                   
                            }
                            if     ($keep_c >= $more && $keep_c > $c){
                                    $rank_arr[$key]++;
                            }
                            else {
                                 if  ($c >= $more && $c > $keep_c){   
                                    $rank_arr[$key_keep]++;     
                                 }
                                 else {
                                      // second rule 
                                      // check the best points of the highest points of discipline<br>
                                      
                                      $k = AA_get_AthletBestPointDisc($points_arr_more_disc_all[$cat_id][$key_keep], $points_arr_more_disc_all[$cat_id][$key], $key_keep, $key);
                                      if ($k != 0){
                                           $rank_arr[$k]++;     
                                      }
                                      // if $k is 0, all points of diszipline are the same -->   athletes with same rank
                                     
                                 }   
                            }                         
                    }            
                    $rank_keep = $rank;  
                    $key_keep = $key; 
                    }
                     
                    asort($rank_arr, SORT_NUMERIC);    // sort descending by rank          
                           
                    foreach($rank_arr as $key => $v)
                        {   
                        $val=$points_arr[$key];  
                        $rank=$v;    
               
                        if ($rank>=$no_rank) {
                            $rank='';
                        }
               
                        $content_res .= "<tr>\r\n";
                        $content_res .= "<td class='rank'>".$rank."</td>\r\n";
                        $content_res .= "<td class='name'>".$name_arr[$key]."\r\n";
                        $content_res .= "<br>\r\n";
                        $content_res .= "<span id='attempts'>".$info_arr[$key]."</span>\r\n";
                        $content_res .= "</td>\r\n";
                        $content_res .= "<td class='yob'>".$year_arr[$key]."</td>\r\n";    
                        $content_res .= "<td class='country'>".$ioc_arr[$key]."</td>\r\n";
                        $content_res .= "<td class='club'>".$club_arr[$key]."</td>\r\n";
                        $content_res .= "<td class='points'>".$val."</td>\r\n";
                        $content_res .= "</tr>\r\n";
               
                        //$list->printLine($rank, $name_arr[$key], $year_arr[$key], $club_arr[$key], $val, $ioc_arr[$key]);  
                        //$list->printInfo($info_arr[$key]);   

                    }
            
            $content_res .= "</tbody>\r\n"; 
            $content_res .= "</table>\r\n";    
            
            $content_res .= "</div>\r\n";
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
            
            
            if (!fwrite($fp, utf8_encode($content_res))) {
                AA_printErrorMsg($GLOBALS['strErrFileWriteFailed']);    
                return;
            }  
            
            fclose($fp);
        }


    }	// end function AA_rankinglist_Single

}	// AA_RANKINGLIST_SINGLE_LIB_INCLUDED
?>
