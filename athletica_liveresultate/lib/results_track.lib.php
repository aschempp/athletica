<?php

/**********
 *
 *	track results
 *	
 */

if (!defined('AA_RESULTS_TRACK_LIB_INCLUDED'))
{
    define('AA_RESULTS_TRACK_LIB_INCLUDED', 1);

    function AA_results_Track($round, $layout, $cat, $dis, $roundName, $event, $id_back, $class_status, $class_status_long, $status){    
        global $content_list;  
 
        require('./config.inc.php');
        //require('./config.inc.end.php');   

        require('./lib/common.lib.php');
        require('./lib/heats.lib.php');  
        require('./lib/utils.lib.php');
        require_once('./lib/timing.lib.php');
                                             
        $p = "./tmp";
        $fp = @fopen($p."/live".$round.".php",'w');
        if(!$fp){
            AA_printErrorMsg($GLOBALS['strErrFileOpenFailed']);  
            return;
        }   

        $relay = AA_checkRelay($event);	// check, if this is a relay event
        $svm = AA_checkSVM(0, $round); // decide whether to show club or team name  
        $teamsm = AA_checkTeamSM($event);  

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
        // check if round is final
        $sql_rnd= "
            SELECT 
                rt.Typ As rnd_typ,
                r.Status As rnd_status,
                r.QualifikationSieger As rnd_qualiSieger, 
                r.QualifikationLeistung As rnd_qualiLeistung,
                TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat') As rnd_starttime,
                DATE_FORMAT(r.Datum, '$cfgDBdateFormat') As rnd_date
            FROM
                athletica.runde as r
                    LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " as rt USING (xRundentyp)
            WHERE
                r.xRunde=" .$round;
        $res_rnd = mysql_query($sql_rnd);
        
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        
        $order="ASC";   
        if (mysql_num_rows($res_rnd) == 1) {
            $row_rnd=mysql_fetch_assoc($res_rnd);  
            if ($row_rnd['rnd_typ']=='F'){
                $order="DESC";  
            }
            //$status =  $row_rnd['rnd_status'];
        }
        mysql_num_rows($res_rnd);    
        $sql_check= "
            SELECT 
                COUNT(r.xRunde)
            FROM 
                athletica.runde AS r  
            WHERE 
                r.xWettkampf = " . $event ."                         
        ";
        
        $sql_check_first= "
            SELECT 
                r.xRunde
            FROM 
                athletica.runde AS r  
            WHERE 
                r.xWettkampf = " . $event ."
            ORDER BY
                Datum,
                Startzeit
            LIMIT 1                          
        ";
        
        $res_check = mysql_query($sql_check);
        $res_check_first = mysql_query($sql_check_first);
                   
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
            $row_check = mysql_fetch_row($res_check);
            $row_check_first = mysql_fetch_row($res_check_first);
            
            if($row_check[0] > 1 && $row_check_first[0] != $round){
                $sql_qual = " AND ss.Qualifikation > 0";    
            } else {
                $sql_qual = "";    
            }
        }            
       
        // +++ single event
		if($relay == FALSE) {
            // +++ athletes single event
            if ($status == $cfgRoundStatus['open'] || $status == $cfgRoundStatus['enrolement_done'] || $status == $cfgRoundStatus['enrolement_pending'] || $status == $cfgRoundStatus['heats_in_progress'] ) {   
                if ($teamsm){
                    $query = "
                        SELECT 
                            r.Bahnen,
                            rt.Name,
                            rt.Typ,
                            a.Startnummer,
                            at.Name,
                            at.Vorname,
                            at.Jahrgang,
                            t.Name,
                            at.Land,
                            at.xAthlet,
                            r.status,
                            st.Bestleistung
                        FROM 
                            athletica.start As st
                                LEFT JOIN athletica.runde AS r ON (r.xWettkampf = st.xWettkampf)    
                                LEFT JOIN athletica.anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                                LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet)
                                LEFT JOIN athletica.verein AS v ON (v.xVerein = at.xVerein)
                                INNER JOIN athletica.teamsmathlet AS tat ON(a.xAnmeldung = tat.xAnmeldung)    
                                LEFT JOIN athletica.teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                                LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp    
                        WHERE
                            r.xRunde  " . $sqlRounds ."   
                        ORDER BY 
                            at.Name,
                            at.Vorname
                    ";      
                }
                else {
                     $query = "
                        SELECT 
                            r.Bahnen,
                            rt.Name,
                            rt.Typ,
                            a.Startnummer,
                            at.Name,
                            at.Vorname,
                            at.Jahrgang,
                            if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)),
                            at.Land,
                            at.xAthlet,
                            r.status,
                            ss.Qualifikation,
                            st.Bestleistung
                        FROM 
                            athletica.start As st
                                LEFT JOIN athletica.serienstart As ss ON (ss.xStart = st.xStart)
                                LEFT JOIN athletica.runde AS r ON (r.xWettkampf = st.xWettkampf)    
                                LEFT JOIN athletica.anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                                LEFT JOIN athletica.athlet AS at ON (at.xAthlet = a.xAthlet)
                                LEFT JOIN athletica.verein AS v ON (v.xVerein = at.xVerein)
                                LEFT JOIN athletica.team AS t ON(a.xTeam = t.xTeam)
                                LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp    
                        WHERE
                            r.xRunde  " . $sqlRounds ." 
                            ".$sql_qual."  
                        ORDER BY 
                            at.Name,
                            at.Vorname
                     ";   
                }        
            }
            // --- athletes single event
            // +++ startlist single event
            else {
                if ($teamsm){
                    $query = "
                        SELECT 
                            r.Bahnen,
                            rt.Name,
                            rt.Typ,
                            s.xSerie,
                            s.Bezeichnung,
                            s.Wind,
                            s.Film,
                            an.Bezeichnung,
                            ss.xSerienstart,
                            ss.Position,
                            ss.Rang,
                            ss.Qualifikation,
                            a.Startnummer,
                            at.Name,
                            at.Vorname,
                            at.Jahrgang,  
                            t.Name,
                            LPAD(s.Bezeichnung,5,'0') as heatid,
                            s.Handgestoppt,
                            at.Land,
                            ss.Bemerkung,  
                            at.xAthlet,
                            r.status,
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
                                LEFT JOIN athletica.teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                                LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                                LEFT JOIN athletica.anlage AS an ON an.xAnlage = s.xAnlage
                        WHERE
                            r.xRunde  " . $sqlRounds ."   
                        ORDER BY
                            heatid ".$order .",
                            ss.Position
                    ";      
                  }
                  else {
                    $query = "
                        SELECT 
                            r.Bahnen,
                            rt.Name,
                            rt.Typ,
                            s.xSerie,
                            s.Bezeichnung,
                            s.Wind,
                            s.Film,
                            an.Bezeichnung,
                            ss.xSerienstart,
                            ss.Position,
                            ss.Rang,
                            ss.Qualifikation,
                            a.Startnummer,
                            at.Name,
                            at.Vorname,
                            at.Jahrgang,
                            if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)),
                            LPAD(s.Bezeichnung,5,'0') as heatid,
                            s.Handgestoppt,
                            at.Land,
                            ss.Bemerkung,
                            at.xAthlet,
                            r.status,
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
                                LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                                LEFT JOIN athletica.anlage AS an ON an.xAnlage = s.xAnlage
                        WHERE
                            r.xRunde  " . $sqlRounds ."   
                        ORDER BY
                            heatid ".$order .",
                            ss.Position
                    ";      
                 }
            }
            // --- startlist single event
		}
        // --- single event
        
        // +++ relay
		else {								
            if ($status == $cfgRoundStatus['open'] || $status == $cfgRoundStatus['enrolement_done'] || $status == $cfgRoundStatus['enrolement_pending'] || $status == $cfgRoundStatus['heats_in_progress'] ) {    
                $query= "
                    SELECT 
                        sf.Startnummer,    
                        rt.Name,
                        rt.Typ,
                        sf.Name,
                        if('".$svm."', t.Name, v.Name),
                        r.xRunde,
                        st.Bestleistung  
                    FROM 
                        athletica.start AS st   
                            LEFT JOIN athletica.runde AS r ON (r.xWettkampf = st.xWettkampf)
                            INNER JOIN athletica.staffel AS sf ON (sf.xStaffel = st.xStaffel)
                            LEFT JOIN athletica.verein AS v ON (v.xVerein = sf.xVerein)                    
                            LEFT JOIN athletica.team AS t ON(sf.xTeam = t.xTeam)
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                    WHERE 
                        r.xRunde " . $sqlRounds ."                          
                    ORDER BY 
                        sf.Name
                ";               
            }
            else {    
			    $query= "
                    SELECT 
                        r.Bahnen,
                        rt.Name,
                        rt.Typ,
                        s.xSerie,
                        s.Bezeichnung,
                        s.Wind,
                        s.Film,
                        an.Bezeichnung,
                        ss.xSerienstart,
                        ss.Position,
                        ss.Rang,
                        ss.Qualifikation,
                        sf.Name,
                        if('".$svm."', t.Name, v.Name),
                        LPAD(s.Bezeichnung,5,'0') as heatid,
                        s.Handgestoppt,
                        ss.Bemerkung,
                        st.Bestleistung
                    FROM 
                        athletica.serienstart As ss
                            LEFT JOIN athletica.start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN athletica.serie AS s ON (s.xSerie = ss.xSerie)
                            LEFT JOIN athletica.runde AS r ON (r.xRunde = s.xRunde)
                            LEFT JOIN athletica.staffel AS sf ON (sf.xStaffel = st.xStaffel)
                            LEFT JOIN athletica.verein AS v ON (v.xVerein = sf.xVerein)                    
                            LEFT JOIN athletica.team AS t ON(sf.xTeam = t.xTeam)
                            LEFT JOIN athletica.rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                            LEFT JOIN athletica.anlage AS an ON an.xAnlage = s.xAnlage
                    WHERE 
                        r.xRunde  " . $sqlRounds ."                          
                    ORDER BY
                        heatid ".$order .",
                        ss.Position
                ";   
             }
        }
        // --- relay 
		$result = mysql_query($query);
       
		if(mysql_errno() > 0) {		// DB error             
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
            	 
			// initialize variables
			$h = 0;		// heat counter
			$p = 0;		// position counter (to evaluate empty heats
			$i = 0;		// input counter (an individual id is assigned to each
							// input field, focus is then moved to the next input
							// field by calling $i+1)
			$tracks = 0;
            
            // set up category and discipline title information 
            if(!empty($roundName)) {
                $round_name = ", ".$roundName;
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
    
                if($relay == FALSE) {    // athlete display
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
                } else {
                    $content_list .= "<th class='bib' data-sort='int'>".$GLOBALS['strStartnumber']."</th>\r\n";
                    $content_list .= "<th class='name' data-sort='string'>".$GLOBALS['strRelay']."</th>\r\n";
                    if($svm){
                        $content_list .= "<th class='club'>".$GLOBALS['strTeam']."</th>\r\n";
                    } else{
                        $content_list .= "<th class='club' data-sort='string'>".$GLOBALS['strClub']."</th>\r\n";   
                    }
                    $content_list .= "<th class='topPerf' data-sort='float'>".$GLOBALS['strTopPerformance']."</th>\r\n";
                }
                    
                $content_list .= "</tr>\r\n";
                $content_list .= "</thead>\r\n";
                $content_list .= "<tbody>\r\n";    
                
            
                while($row = mysql_fetch_row($result))
                { 
                    $content_list .= "<tr>\r\n";
                    
                    // format topPerf
                    $topPerf = ($relay) ? $row[6] : $row[12];
                    
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
                        
                    if($relay == FALSE) {
                            
                        $content_list .= "<td class='bib'>".$row[3]."</td>\r\n"; 
                        $content_list .= "<td class='name'>".$row[4]." ".$row[5]."</td>\r\n";
                        $content_list .= "<td class='yob'>".AA_formatYearOfBirth($row[6])."</td>\r\n";
                        $content_list .= "<td class='country'>\r\n";
                        if ($row[8]!='' && $row[8]!='-') {
                            $content_list .= $row[8];
                        } else {
                            $content_list .= " "; 
                        }
                        $content_list .= "</td>\r\n";
                        $content_list .= "<td class='club'>".$row[7]."</td>\r\n";
                        $content_list .= "<td class='topPerf'>".$list_topPerf."</td>\r\n";
                    }
                    else {    // relay

                        $content_list .= "<td class='bib'>" . $row[0] ."</td>\r\n";
                        $content_list .= "<td class='name'>" . $row[3] ."</td>\r\n"; 
                        $content_list .= "<td class='club' >" . $row[4] ."</td>\r\n";
                        $content_list .= "<td class='topPerf'>".$list_topPerf."</td>\r\n";
                    } 
                    $content_list .= "</tr>\r\n";      
                }
            }
            // --- Teilnehmerliste
            
            // +++ Startliste
            else {
			    while($row = mysql_fetch_row($result))
			    {
           
				    $p++;			// increment position counter

				    if($h != $row[3])		// new heat
				    {
					    $tracks = $row[0];	// keep nbr of planned tracks

					    // fill previous heat with empty tracks
					    if($p > 1) {
						     printEmptyTracks($p, $tracks, 4);
					    }
	    
					    $h = $row[3];				// keep heat ID
					    $p = 1;						// start with track one

					    if(is_null($row[1])) {		// only one round
						    $title = $GLOBALS['strFinalround'];
					    }
					    else {		// more than one round
						    $title = "$row[1]";
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
                          
                        $content_list .= "<div data-role='collapsible' data-theme='b' data-content-theme='d' data-collapsed='false' data-collapsed-icon='' data-expanded-icon='' data-inset='true'>\r\n";
                        $div_collapsible = true;
                        
	                    $content_list .= "<h4>" .$title ." " .$row[4] ."</h4>"; 

	                    $content_list .= "<div class='ui-corner-all ui-shadow' style='padding: 5px;'>\r\n";
                                    
                        $div_res = true;
                        
                        $content_list .= "<table class='table-startlist ui-responsive table-stroke'>\r\n";
                        $content_list .= "<thead>\r\n";
                        $content_list .= "<tr>\r\n";

    /*
     *  Column header
     */
					    if($relay == FALSE) {	// athlete display

	                        $content_list .="<tr>\r\n";   
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
					    }
					    else {		// relay display

	                        $content_list .="<tr>";   
		                    $content_list .= "<th class='pos' data-sort='int'>". $GLOBALS['strPositionShort']."</th>\r\n";         
		                    $content_list .= "<th class='name' data-sort='string'>". $GLOBALS['strRelay'] ."</th>\r\n";
		                    $content_list .= "<th class='club' data-sort='string'>\r\n";
                            if($svm){ 
                                $content_list .= $GLOBALS['strTeam']; 
                            }else{ 
                                $content_list .= $GLOBALS['strClub'];
                            }
                            $content_list .= "<th class='topPerf' data-sort='float'>".$GLOBALS['strTopPerformance']."</th>\r\n";
					    }  

	                    $content_list .= "</tr>\r\n";
                        $content_list .= "</thead>\r\n";
                        $content_list .= "<tbody>\r\n";
                        
                        $table = true;   

				    }

				    if(($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
					    || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrackNoWind']])
					    || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeRelay']]))
				    {
					    // current track and athlete's position not identical
					    if($p < $row[9]) {
						    $p = printEmptyTracks($p, ($row[9]-1), 5+$c);
					    }
				    }
				    $p = $row[9];			// keep position
                    
                    // format topPerf
                    $topPerf = ($relay) ? $row[17] : $row[23];
                    
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

				    if($relay == FALSE) {
       
	                    $content_list .= "<tr>\r\n";
		                $content_list .= "<td class='pos'>" . $row[9] ."</td>\r\n";                          
		                $content_list .= "<td class='bib'>". $row[12] ."</td>\r\n";            
		                $content_list .= "<td class='name'>" . $row[13] . " " . $row[14] ."</td>\r\n";                 
		                $content_list .= "<td class='yob'>" . AA_formatYearOfBirth($row[15]) ."</td>\r\n";
		                $content_list .= "<td class='country'>\r\n";
                        if ($row[19]!='' && $row[19]!='-') {
                            $content_list .= $row[19];
                        } else {
                            $content_list .= " "; 
                        }
                        $content_list .= "</td>\r\n";
                        $content_list .= "<td class='club'>" . $row[16] ."</td>\r\n"; 
		                $content_list .= "<td class='topPerf'>" . $list_topPerf ."</td>\r\n"; 
				    }
				    else {

	                    $content_list .= "<tr>\r\n";
		                $content_list .= "<td class='pos'>" . $row[9] ."</td>\r\n";
                        $content_list .= "<td class='name'>" . $row[12] ."</td>\r\n";
		                $content_list .= "<td class='club'>" . $row[13] ."</td>\r\n";
                        $content_list .= "<td class='topPerf'>" . $list_topPerf ."</td>\r\n";
				    }  
                   
			    }
                $content_res .= "</tr>\r\n";

			    // Fill last heat with empty tracks for disciplines run in
			    // individual tracks
			    if(($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrack']])
				    || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeTrackNoWind']])
				    || ($layout == $cfgDisciplineType[$GLOBALS['strDiscTypeRelay']]))
			    {
				    if($p > 0) {	// heats set up
					    $p++;
					    printEmptyTracks($p, $tracks, 4);
				    }
			    }	// ET track disciplines
                    
            }
            // --- Startliste
       
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
            $content_list .= "include('include/fotter.php');\r\n ";
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

        }		// ET DB error
        //AA_UpdateStatusChanged($round);
    }   


    /**
     * print empty tracks
     * ------------------
     * arg 1 (int): heat position
     * arg 2 (int): up to this position
     * arg 3 (int): column span
     *
     * returns next position
     */
    function printEmptyTracks($position, $last, $span)
    {
	    require('./lib/common.lib.php');
	    include('./config.inc.php');
        
        global $content_list;

	    while($position <= $last)
	    {
	        $content_list .= "<tr class=''>";
            $content_list .= "<td class='pos'>".$position."</td>";
		    $content_list .= "<td class='bib'>&nbsp;</td>";
		    $content_list .= "<td class='name' colspan='".$span."'><span id='empty'>" . $GLOBALS['strEmpty'] ."</span></td>";
	        $content_list .= "</tr>";

		    $position++;
	    }
	    return $position;
    }
}	// AA_RESULTS_TRACK_LIB_INCLUDED
?>