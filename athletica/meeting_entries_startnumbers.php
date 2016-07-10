<?php

/**********
 *
 *	meeting_entries_startnumbers.php
 *	--------------------------------
 *	
 */            
  
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
$anzahl_cat = "0";

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(empty($_COOKIE['meeting_id'])) {
	AA_printErrorMsg($GLOBALS['strNoMeetingSelected']);
}


$clubGap = 0;
if ($_GET['clubGap']){
    $clubgap = $_GET['clubGap'];            // nbr gap between each club or team
}

$teams = false;
if ($_GET['teams']){
    $teams = $_GET['teams'];            
}

$max_startnr = 0;
$max_startnr_track1 = 0;
$max_startnr_track2 = 0;

$max_startnr_tech = 0;
$nbr1 = 0;
$nbr2 = 0; 
$nbr3 = 0; 
$limit1 = 0;
$limit2 = 0;
$limit3 = 0;

$allNr = false;
$sex = false;


 if ( ($_GET['of_sex1'] > 0 || $_GET['of_sex2'] > 0)) {
     $_GET["sort"] == 'sex';
     $sex = true;
 }
         

   
   
 
?>

<?php
//
// check if a heat is assigned
//
$heats_done = "false";
$res = mysql_query("
		SELECT r.xRunde,w.xWettkampf FROM
			runde as r 
			LEFT JOIN wettkampf as w On (r.xWettkampf = w.xWettkampf)  		
        WHERE
            (Status = ".$cfgRoundStatus['heats_done']."
            OR Status = ".$cfgRoundStatus['results_in_progress']."
            OR Status = ".$cfgRoundStatus['results_done'].")
            AND xMeeting = ".$_COOKIE['meeting_id']
     );
 
if(mysql_errno() > 0){
	AA_printErrorMsg(mysql_errno().": ".mysql_error());
}else{
	if(mysql_num_rows($res) > 0){
        $row = mysql_fetch_row($res);
		$heats_done = "true";
	}
}        

if($_GET['arg'] == 'assign')
{
    if ($teams) {
        
            $sql="SELECT 
                    t.xTeam, t.Name , v.xVerein,v.Name
            FROM
                team as t
                LEFT JOIN anmeldung AS a ON (t.xTeam = a.xTeam)
                LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
            WHERE
                t.xMeeting = ".$_COOKIE['meeting_id'] ."
                ORDER BY t.Name, t.xTeam, v.Sortierwert";
           
            $res=mysql_query($sql);
           
             if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno().": ".mysql_error());
             }else{  
                $noCat = true;   
           
                // check if choosen per name/club or per category/team
                while ($row = mysql_fetch_row($res))
                { 
                    if (($_GET["of_$row[0]"] != 0)  ) 
                        {   
                        $noCat = false;   
                    }  
                } 
            }                    
    }
    
	if ($_GET['sort']!="del" && !$teams || ($_GET['sort']!="del" && $_GET['teams'] && $noCat))		// assign startnumbers     
	{    
		// sort argument 
        $argument1='';   
        $groupby = '';
		if ($_GET['sort']=="name") {
		  $argument2="at.Name, at.Vorname"; 	  	
		} else if ($_GET['sort']=="club" && !$teams) {
		  $argument2="v.Sortierwert, at.Name, at.Vorname";   
        } else if ($_GET['sort']=="club" && $teams) {
          $argument2="at.Name, at.Vorname";   
          $argument1="t.Name,";                          
		} else {
		  $argument2="at.Name, at.Vorname";
		}   
        
        if ( ($_GET["of_sex1"] != 0)  ||   ($_GET["of_sex2"] != 0) ){  
                $argument3="at.Geschlecht,";                
                $groupby = ' GROUP BY a.xAnmeldung ';   
        }   
		
		// assign per contest cat      
			$argument = "k.Anzeige, k.xKategorie ";    
			
			//
			// Read athletes
			//
			
			mysql_query("
				LOCK TABLES
					athlet AS a READ
					, kategorie AS k READ
					, verein AS v READ
					, anmeldung AS a WRITE
					, wettkampf AS w READ
					, start AS s READ
					, team AS t READ
			");  
            
            if ($sex){
                      $sql="SELECT 
                            DISTINCT (a.xAnmeldung) ,
                            w.xKategorie , 
                            at.xVerein , 
                            a.xTeam, 
                            at.Name, 
                            at.Vorname,
                            t.Name,  
                            t.Name,
                            tat.xTeamsm,
                            tsm.Name, 
                            at.Geschlecht                      
                        FROM 
                            anmeldung AS a
                            LEFT JOIN athlet AS at ON a.xAthlet = at.xAthlet        
                            LEFT JOIN verein AS v ON at.xVerein = v.xVerein 
                            LEFT JOIN start AS s ON s.xAnmeldung = a.xAnmeldung 
                            LEFT JOIN wettkampf AS w USING (xWettkampf)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d On (w.xdisziplin = d.xDisziplin)
                            LEFT JOIN kategorie AS k ON k.xKategorie = w.xKategorie
                            LEFT JOIN team AS t ON t.xTeam = a.xTeam 
                            LEFT JOIN teamsmathlet AS tat ON ( tat.xAnmeldung = a.xAnmeldung )
                            LEFT JOIN teamsm AS tsm ON tsm.xTeamsm = tat.xTeamsm
                        WHERE 
                            a.xMeeting = " . $_COOKIE['meeting_id'] . " 
                            
                            ORDER BY      
                                 $argument3 $argument2, $argument1 $argument,  tat.xTeamsm ";    
                  
            }
            else {
            $sql="SELECT 
                    DISTINCT (a.xAnmeldung) ,
                    w.xKategorie , 
                    at.xVerein , 
                    a.xTeam, 
                    at.Name, 
                    at.Vorname,
                    t.Name, 
                    IF( (d.Typ = ".$cfgDisciplineType[$strDiscTypeTrack]." 
                                    || d.Typ = ".$cfgDisciplineType[$strDiscTypeTrackNoWind]."   
                                     ),2, IF( (d.Typ = ".$cfgDisciplineType[$strDiscTypeDistance]."
                                     || d.Typ = ".$cfgDisciplineType[$strDiscTypeRelay]."                                       
                                     ),3, 1 ) ) as discSort,
                   tat.xTeamsm,
                   tsm.Name, 
                   at.Geschlecht                      
                FROM 
                    anmeldung AS a
                    LEFT JOIN athlet AS at ON a.xAthlet = at.xAthlet        
                    LEFT JOIN verein AS v ON at.xVerein = v.xVerein 
                    LEFT JOIN start AS s ON s.xAnmeldung = a.xAnmeldung 
                    LEFT JOIN wettkampf AS w USING (xWettkampf)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d On (w.xdisziplin = d.xDisziplin)
                    LEFT JOIN kategorie AS k ON k.xKategorie = w.xKategorie
                    LEFT JOIN team AS t ON t.xTeam = a.xTeam 
                    LEFT JOIN teamsmathlet AS tat ON ( tat.xAnmeldung = a.xAnmeldung )
                    LEFT JOIN teamsm AS tsm ON tsm.xTeamsm = tat.xTeamsm
                WHERE 
                    a.xMeeting = " . $_COOKIE['meeting_id'] . " 
                    $groupby
                    ORDER BY      
                         $argument3 $argument1 $argument, discSort, tat.xTeamsm, $argument2";    
            
            }
            $result = mysql_query($sql);  
           
			if(mysql_errno() > 0)		// DB error
			{
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else if(mysql_num_rows($result) > 0)  // data found
			{ 
            $noCat = true;   
           // $sex = false;
           
              // check if choosen per name/club or per category
              while ($row = mysql_fetch_row($result))
              { 
                if (($_GET["of_$row[1]"] != 0)   ||
                     ($_GET["of_tech_$row[1]"] != 0) ||
                      ($_GET["of_track1_$row[1]"] != 0) ||
                      ($_GET["of_track2_$row[1]"] != 0)) 
                { 
                      $noCat = false;                    
              } 
              }
              if ( ($_GET["of_sex1"] != 0)  ||   ($_GET["of_sex2"] != 0) ){
                    $noCat = false;
                    $sex = true; 
              }
              
               
              if ($noCat && !$teams){       // set per name or per club  
                  
                   $sql="SELECT 
                            DISTINCT (a.xAnmeldung) ,
                            w.xKategorie , 
                            at.xVerein , 
                            a.xTeam, 
                            at.Name, 
                            at.Vorname,
                            t.Name, 
                            IF( (d.Typ = ".$cfgDisciplineType[$strDiscTypeTrack]." 
                                    || d.Typ = ".$cfgDisciplineType[$strDiscTypeTrackNoWind]."   
                                     ),2, IF( (d.Typ = ".$cfgDisciplineType[$strDiscTypeDistance]."
                                     || d.Typ = ".$cfgDisciplineType[$strDiscTypeRelay]."                                       
                                     ),3, 1 ) ) as discSort
                         FROM 
                            anmeldung AS a
                            LEFT JOIN athlet AS at ON a.xAthlet = at.xAthlet        
                            LEFT JOIN verein AS v ON at.xVerein = v.xVerein 
                            LEFT JOIN start AS s ON s.xAnmeldung = a.xAnmeldung 
                            LEFT JOIN wettkampf AS w USING (xWettkampf)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d On (w.xdisziplin = d.xDisziplin)
                            LEFT JOIN kategorie AS k ON k.xKategorie = w.xKategorie
                            LEFT JOIN team AS t ON t.xTeam = a.xTeam 
                         WHERE 
                            a.xMeeting = " . $_COOKIE['meeting_id'] . " 
                         ORDER BY      
                            $argument2 , discSort"; 
              }                          
              
              $result = mysql_query($sql); 
              if(mysql_errno() > 0)        // DB error
                    {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
              }         
              
			  $k = 0;	// initialize current category
              $s = '';    // initialize current sex 
			  $v = 0;	// initialize current club   
              $first = true;
              
              $arr_enrolment = array();   
              
              $nbr_rest = $_GET["of_rest"];
			  
			  // Assign startnumbers
			  while ($row = mysql_fetch_row($result))
			  { 
                if (($row[3] > 0 && $teams) || ($row[3] == 0 && !$teams)){       // by teams only xTeam > 0                    
                
				    // set per category from, to  and per disciplines (all or tech and/or track under 400m and/or track over 400m)  
                    if (($v != $row[2] )         // new club
                            && ($clubgap > 0)           // gap between clubs
                            && ($v > 0)                 // not first row
                            && ($_GET['sort']=="club")) // gap after cat 
                        { 
                     
                        if ($noCat){           // set per name or per club 
                            $nbr = $nbr + $clubgap - 1;    // calculate next number  all disciplines     
                        }
                        else {
                            if (!empty($_GET["of_$row[1]"])){  
                                $nbr = $nbr + $clubgap - 1;    // calculate next number  all disciplines
                            }
                            else { 
                                if (!empty($_GET["of_tech_$row[1]"])){ 
                                    $nbr1 = $nbr1 + $clubgap - 1;    // calculate next number tech  
                                }
                                if (!empty($_GET["of_track1_$row[1]"])){    
                                    $nbr2 = $nbr2 + $clubgap - 1;    // calculate next number track under 400m
                                }
                                if (!empty($_GET["of_track2_$row[1]"])){
                                    $nbr3 = $nbr3 + $clubgap -1 ;    // calculate next number track over 400m  
                                }  
                            }
                        }
                    }
            
                    if ($noCat) {                  // set per name or per club 
                        if ($first){    
                            if (!empty($_GET["club_of"]) && $_GET["sort"] == 'club'){    
                                 $nbr = $_GET["club_of"];                             // set nbr of per club
                            }
                            else {
                                $nbr = 0;   
                            } 
                        
                            $nbr1 = 0; 
                            $nbr2 = 0; 
                            $nbr3 = 0; 
                            $nbr_sex1 = 0;
                            $nbr_sex2 = 0;  
                            $limit = 0;
                            $limit1 = 0;
                            $limit2 = 0;
                            $limit3 = 0;
                            $limit_sex1 = 0;  
                            $limit_sex2 = 0;     
                            $all = false; 
                       
                            $limit = 9999999;
                            $all = true;                          
                            $allNr = true;
                                 
                            $nbr=($nbr==0 && $limit>0)?1:$nbr;   
                            $nbr1=($nbr1==0 && $limit1>0)?1:$nbr1;
                            $nbr2=($nbr2==0 && $limit2>0)?1:$nbr2; 
                            $nbr3=($nbr3==0 && $limit3>0)?1:$nbr3; 
                            
                            $nbr_sex1=($nbr_sex1==0 &&   $limit_sex1>0)?1:$nbr_sex1; 
                            $nbr_sex2=($nbr_sex2==0 &&   $limit_sex2>0)?1:$nbr_sex2;   
                        
                            $first = false;
                        }
                        else {
                            if(($limit > 0 && $nbr > $limit) || $limit == 0){
                                $nbr = 0;
                                $limit = 0;
                            } 
                    
                            if(($limit1 > 0 && $nbr1 > $limit1) || $limit1 == 0){
                                $nbr1 = 0;
                                $limit1 = 0;
                            } 
                            if(($limit2 > 0 && $nbr2 > $limit2) || $limit2 == 0){
                                $nbr2 = 0;
                                $limit2 = 0;
                            }
                            if(($limit3 > 0 && $nbr3 > $limit3) || $limit3 == 0){
                                $nbr3 = 0;
                                $limit3 = 0;
                            } 
                             if(($limit_sex1 > 0 && $nbr_sex1 > $limit_sex1) || $limit_sex1 == 0){
                                $nbr_sex1 = 0;
                                $limit_sex1 = 0;
                            } 
                             if(($limit_sex2 > 0 && $nbr_sex2 > $limit_sex2) || $limit_sex2 == 0){
                                $nbr_sex2 = 0;
                                $limit_sex2 = 0;
                            } 
                        } 
                    }
                    else {                          
                       
                       
				       if (($k != $row[1] && !$sex) || ($s != $row[10] && $sex)){			// new category or new sex 
                            $nbr = 0;  
                            $nbr1 = 0; 
                            $nbr2 = 0; 
                            $nbr3 = 0; 
                            $limit = 0;
                            $limit1 = 0;
                            $limit2 = 0;
                            $limit3 = 0;
                            $nbr_sex1 = 0;
                            $nbr_sex2 = 0;   
                            $limit_sex1 = 0; 
                            $limit_sex2 = 0;
                            $all = false;
                                                       
                            if ($sex){
                                if (!empty($_GET["of_sex1"])){
                                    $nbr_sex1 = $_GET["of_sex1"];
                                    $limit_sex1 = $_GET["to_sex1"];   
                                } 
                                if (!empty($_GET["of_sex2"])){
                                    $nbr_sex2 = $_GET["of_sex2"];
                                    $limit_sex2 = $_GET["to_sex2"];   
                                } 
                            }
                            else {
                                if (!empty($_GET["of_$row[1]"])){
                                    $nbr = $_GET["of_$row[1]"];
                                    $limit = $_GET["to_$row[1]"]; 
                                    $all = true;                          
                                } 
                                else {
                                    if (!empty($_GET["of_tech_$row[1]"])){
                                        $nbr1 = $_GET["of_tech_$row[1]"];
                                        $limit1 = $_GET["to_tech_$row[1]"];  
                                    } 
                                    if (!empty($_GET["of_track1_$row[1]"])){
                                        $nbr2 = $_GET["of_track1_$row[1]"];
                                        $limit2 = $_GET["to_track1_$row[1]"]; 
                                    }
                                    if (!empty($_GET["of_track2_$row[1]"])){
                                        $nbr3 = $_GET["of_track2_$row[1]"];
                                        $limit3 = $_GET["to_track2_$row[1]"]; 
                                    } 
                                }
                            }
                            $nbr=($nbr==0 && $limit>0)?1:$nbr;   
                            $nbr1=($nbr1==0 && $limit1>0)?1:$nbr1;
                            $nbr2=($nbr2==0 && $limit2>0)?1:$nbr2; 
                            $nbr3=($nbr3==0 && $limit3>0)?1:$nbr3; 
                            
                            $nbr_sex1=($nbr_sex1==0 && $limit_sex1>0)?1:$nbr_sex1;  
                            $nbr_sex2=($nbr_sex2==0 && $limit_sex2>0)?1:$nbr_sex2;   
                
				        }else{ 
                            if(($limit > 0 && $nbr > $limit) || $limit == 0){
                                $nbr = 0;
                                $limit = 0;
                            }   
					        if(($limit1 > 0 && $nbr1 > $limit1) || $limit1 == 0){
						        $nbr1 = 0;
						        $limit1 = 0;
					        } 
                            if(($limit2 > 0 && $nbr2 > $limit2) || $limit2 == 0){
                                $nbr2 = 0;
                                $limit2 = 0;
                            }
                            if(($limit3 > 0 && $nbr3 > $limit3) || $limit3 == 0){
                                $nbr3 = 0;
                                $limit3 = 0;
                            }
                             if(($limit_sex1 > 0 && $nbr_sex1 > $limit_sex1) || $limit_sex1 == 0){
                                $nbr_sex1 = 0;
                                $limit_sex1 = 0;
                            }
                             if(($limit_sex2 > 0 && $nbr_sex2 > $limit_sex2) || $limit_sex2 == 0){
                                $nbr_sex2 = 0;
                                $limit_sex2 = 0;
                            }
				        }   
                    }   
				    if ($sex){
                        if ($row[10] == 'm'){
                           if (!isset($arr_enrolment[$row[0]])){ 
                                      if ($nbr_sex1 > 0){            
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_sex1'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                
                                                if ($nbr_sex1 > 0){
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr_sex1++;   
                                                }  
                                      }
                                               
                           }
                        }
                           else {
                                if (!isset($arr_enrolment[$row[0]])){ 
                                        if ($nbr_sex2 > 0){
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_sex2'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                
                                                if ($nbr_sex2 > 0){
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr_sex2++;   
                                                }  
                                        }
                                                 
                                }
                           }
                        
                    }
                    else {
                        switch ($row[7]){
                           case 1:  if ($all){
                                         if (!isset($arr_enrolment[$row[0]])){ 
                                                if ($nbr == 0 && $nbr_rest > 0){                                                     
                                                      mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_rest'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                    $nbr_rest++;   
                                                }
                                                else {
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                }
                                                if ($nbr > 0){
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr++;
                                                }                          
                                         }  
                                    }
                                    else {  
                                          if (!isset($arr_enrolment[$row[0]])){ 
                                               if ($nbr1 == 0 && $nbr_rest > 0){                                                     
                                                      mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_rest'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                    $nbr_rest++;   
                                                }
                                                else {
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr1'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                }
                                                if ($nbr1 > 0){ 
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr1++;
                                                }
                                          }
                                    }
                                    break;   
                           case 2:  if ($all){
                                         if (!isset($arr_enrolment[$row[0]])){ 
                                              if ($nbr == 0 && $nbr_rest > 0){                                                     
                                                      mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_rest'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                    $nbr_rest++;   
                                                }
                                                else {
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                }
                                                if ($nbr > 0){ 
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr++; 
                                                }   
                                         }
                               
                                    }
                                    else {   
                                          if (!isset($arr_enrolment[$row[0]])){  
                                                if ($nbr2 == 0 && $nbr_rest > 0){                                                     
                                                      mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_rest'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                    $nbr_rest++;   
                                                }
                                                else { 
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr2'
                                                    WHERE xAnmeldung = $row[0]
                                                ");
                                                }
                                                if ($nbr2 > 0){ 
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr2++; 
                                                }   
                                             }
                                    }
                                    break;                     
                           case 3:  if ($all){
                                         if (!isset($arr_enrolment[$row[0]])){   
                                              if ($nbr == 0 && $nbr_rest > 0){                                                     
                                                      mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_rest'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                    $nbr_rest++;   
                                                }
                                                else {
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr'
                                                    WHERE xAnmeldung = $row[0]
                                                ");
                                                }
                                                if ($nbr > 0){ 
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr++;
                                                }    
                                         } 
                                    }
                                    else {   
                                          if (!isset($arr_enrolment[$row[0]])){ 
                                               if ($nbr3 == 0 && $nbr_rest > 0){                                                     
                                                      mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr_rest'
                                                    WHERE xAnmeldung = $row[0]
                                                    ");
                                                    $nbr_rest++;   
                                                }
                                                else {
                                                mysql_query("
                                                    UPDATE anmeldung SET
                                                           Startnummer='$nbr3'
                                                    WHERE xAnmeldung = $row[0]
                                                ");
                                                }
                                                if ($nbr3 > 0){ 
                                                    $arr_enrolment [$row[0]] = 'y';   
                                                    $nbr3++;    
                                                }
                                          }
                                    }
                                    break;                     
                           default: break;  
                        }
                    }
				    if(mysql_errno() > 0) {
					    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				    }
				   
                    if (!$noCat) {
				        $k = $row[1];	// keep current category
                        $s = $row[10];    // keep current sex 
                    }
				    $v = $row[2];	// keep current club  
			    }  
              }
			  mysql_free_result($result);
			}						// ET DB error
			mysql_query("UNLOCK TABLES");      
	
	}
    
    // assign startnumners to teams       
         
    elseif ($_GET['sort']!="del" && $teams)  {                
               
            // sort argument
            if ($_GET['sort']=="name") {
                $argument2="at.Name, at.Vorname";           
            } else if ($_GET['sort']=="club") {
                $argument2="v.Sortierwert, at.Name, at.Vorname";  
            } else if ($_GET['sort']=="team") {  
                $argument2="v.Sortierwert, at.Name, at.Vorname";     
            } else {
                $argument2="at.Name, at.Vorname";
            }                                         
        
            $first = true;       
            
            //
            // Read athletes
            //
            
            mysql_query("
                LOCK TABLES
                    athlet AS a READ
                    , kategorie AS k READ
                    , verein AS v READ
                    , anmeldung AS a WRITE  
                    , wettkampf AS w READ
                    , start AS s READ
                    , team AS t READ
            ");     
         
          $sql="SELECT 
                t.xTeam, t.Name
          FROM
                team as t
                LEFT JOIN anmeldung AS a ON (t.xTeam = a.xTeam)
                LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
          WHERE
                t.xMeeting = ".$_COOKIE['meeting_id'] ."
                ORDER BY v.Sortierwert";
                
            $res=mysql_query($sql);
           
             if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno().": ".mysql_error());
             }else{
                 
                 $t = 0;    // initialize current team  
                 $arr_enrolment = array();   
                 
                 while($row = mysql_fetch_array($res)){        
                                      
                       $sql=" SELECT DISTINCT
                                    a.xAnmeldung,  
                                    t.xTeam ,  
                                    t.Name   
                              FROM 
                                    anmeldung AS a 
                                    LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                                    LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                                    LEFT JOIN wettkampf AS w USING (xWettkampf) 
                                    LEFT JOIN team AS t ON (t.xTeam = a.xTeam) 
                                    LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)    
                              WHERE 
                                    a.xMeeting = ".$_COOKIE['meeting_id'] ."
                                    AND t.xTeam = " .$row[0] ."     
                                    ORDER BY v.Sortierwert, $argument2";  
                       
                        $res_team=mysql_query($sql);   
                        if(mysql_errno() > 0){
                            AA_printErrorMsg(mysql_errno().": ".mysql_error());
                        }else{    
                            $nbr = 0;  
                            $limit = 0;
              
           
                            // check if choosen per name/club or per category
                            while ($row_team = mysql_fetch_row($res_team))
                                {                                          
                                if ($first){      
                                    if (!empty($_GET["of_$row[0]"])){
                                        $nbr = $_GET["of_$row[0]"];
                                        $limit = $_GET["to_$row[0]"]; 
                                    }  
                                    $nbr=($nbr==0 && $limit>0)?1:$nbr;   
                                    $first = false; 
                                }
                                else {
                                    if ($t != $row[0]  ){            // new team
                                        $nbr = 0;  
                                        $limit = 0;
                                           
                                        if (!empty($_GET["of_$row[0]"])){
                                            $nbr = $_GET["of_$row[0]"];
                                            $limit = $_GET["to_$row[0]"];  
                                        }  
                                        $nbr=($nbr==0 && $limit>0)?1:$nbr;   
                                    }else{ 
                                        if(($limit > 0 && $nbr > $limit) || $limit == 0){
                                            $nbr = 0;
                                            $limit = 0;
                                        }  
                                    }  
                                }   
                                 
                                 if ($nbr != 0 || $limit !=0) {                
                                    if (!isset($arr_enrolment[$row_team[0]])){ 
                                   
                                        mysql_query("UPDATE anmeldung SET
                                                        Startnummer='$nbr'
                                                        WHERE xAnmeldung = $row_team[0]
                                                        ");    
                            
                                        $arr_enrolment [$row_team[0]] = 'y';   
                                        $nbr++;   
                                    }
                           
                                }  
                   
                                if(mysql_errno() > 0) {
                                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }
               
                                $t = $row[0];    // keep current category      
                             }
                       }
               }  
              mysql_free_result($res);
            }                        // ET DB error
            mysql_query("UNLOCK TABLES");
                                            
        
    }      
	else		// delete startnumbers
	{
		mysql_query("LOCK TABLE anmeldung WRITE");

	  	mysql_query("
			UPDATE anmeldung SET
				Startnummer = 0
			WHERE xMeeting=" . $_COOKIE['meeting_id']
		);

		if(mysql_errno() > 0)
		{
		  AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}

		mysql_query("UNLOCK TABLES");
	}
}

//
// show dialog 
//

$page = new GUI_Page('meeting_entries_startnumbers');
$page->startPage();
$page->printPageTitle($strAssignStartnumbers);

if($_GET['arg'] == 'assign')	// refresh list
{
	?>
	<script>
		window.open("meeting_entrylist.php", "list")
	</script>
	<?php
}
?>



 <?php


 // check if there are teams in this meeting
    $teams = false;
    $sql="SELECT 
                xTeam
          FROM
                team as t
          WHERE
                t.xMeeting = ".$_COOKIE['meeting_id'];
                
    $res = mysql_query($sql);
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno().": ".mysql_error());
    }else{
         if (mysql_num_rows($res) > 0) {
             $teams = true;
         }
         
 ?>

<form action='meeting_entries_startnumbers.php' method='get' id='startnr'>
<input type='hidden' name='arg' value='assign'>
<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strSortBy; ?></th>
    <th class='dialog'><?php echo $strOf; ?></th>     
	<th class='dialog' colspan='6'><?php echo $strRules; ?></th>
</tr>
<tr>
	<td class='dialog'>
		<input type='radio' name='sort' id='name' onChange='check_of()' value='name' checked='checked'>
        <?php echo $strName; ?> 
    </td>
    <td class='forms'>&nbsp;          
    </td>  
</tr>   

<tr>
	<td class='dialog'>
		<input type='radio' name='sort' id='club' value='club' onChange='check_of()'>
         <?php if ($teams) {echo $strTeam;} else {echo $strClub; }?></input> 
     </td>  
     <td class='forms'>
        <input type="text" size="3" value="<?php echo $_GET['club_of'];?>" name="club_of" >  
     </td> 
      <td class='dialog' colspan='4'>
        <?php echo $strGapBetween . " " . $strClub; ?>    </td>
    <td class='dialog'>
        <input class='nbr' type='text' name='clubGap' maxlength='4' value='<?php echo $_GET['clubGap']; ?>'>    </td>
    <td class='dialog'>&nbsp;</td> 
</tr>   
<?php
    
      
       // check disziplines man           
          $res_sex1 = mysql_query("SELECT           
                        a.xAnmeldung
                    FROM 
                        anmeldung AS a  
                        LEFT JOIN athlet AS at On (at.xAthlet = a.xAthlet)                            
                    WHERE 
                        a.xMeeting = ".$_COOKIE['meeting_id']." 
                        AND at.Geschlecht = 'm'    
                    GROUP BY a.xAnmeldung
                    ");     
      
          if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno().": ".mysql_error());
          }else{
                if (mysql_num_rows($res_sex1)>0){ 
                    $max_startnr_sex1=mysql_num_rows($res_sex1); 
                }
          } 
          // check disziplines wom
          $res_sex2 = mysql_query("SELECT           
                        a.xAnmeldung
                    FROM 
                        anmeldung AS a  
                        LEFT JOIN athlet AS at On (at.xAthlet = a.xAthlet)   
                    WHERE 
                        a.xMeeting = ".$_COOKIE['meeting_id']." 
                        AND at.Geschlecht = 'w'    
                    GROUP BY a.xAnmeldung   
                    ");     
      
          if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno().": ".mysql_error());
          }else{
                if (mysql_num_rows($res_sex2)>0){ 
                    $max_startnr_sex2=mysql_num_rows($res_sex2); 
                }
          }         
          
      if (!$teams) {
       ?>    
       
             <tr>     
                <th class='dialog'><?php echo $strSex; ?></th>
                <th class='dialog' > <?php echo $strOf ?></th>
                <th class='dialog' ><?php echo $strTo ?></th>
                <th class='dialog' ><?php echo $strMax; ?>  </th>
                <th class='dialog' colspan='11'>
             </tr>
                
             <tr>
                <td class='dialog'><?php echo $strMan; ?></td> 
                <td class='forms'>    
                <input type="text" size="3" value="<?php echo $_GET['of_sex1']; ?>"  name="of_sex1" id="of_sex1" ></td>
                 <td class='forms_right'>
                                 
                <input type="text" size="3" value="<?php echo $_GET['to_sex1']; ?>"  name="to_sex1" id="to_sex1"></td>
                <td class='forms_right_grey'><?php echo $max_startnr_sex1; ?></td>  
             </tr>
             
             <tr>
                <td class='dialog'><?php echo $strWom; ?></td> 
                <td class='forms'>  
                <input type="text" size="3" value="<?php echo $_GET['of_sex2']; ?>"  name="of_sex2" id="of_sex2" ></td>
                <td class='forms_right'>  
                <input type="text" size="3" value="<?php echo $_GET['to_sex2']; ?>"  name="to_sex2" id="to_sex2"></td>
                <td class='forms_right_grey'><?php echo $max_startnr_sex2; ?></td>  
             </tr>   
<?php

          }
        
    
   if (!$teams){
$i = 0;
// get all used categories in contest
$res = mysql_query("SELECT 
				DISTINCT(w.xKategorie)
				, k.Kurzname
			FROM
				wettkampf as w
				LEFT JOIN kategorie as k ON (w.xKategorie = k.xKategorie) 
			WHERE   
				w.xMeeting = ".$_COOKIE['meeting_id']."
			ORDER BY
				k.Anzeige");
             
if(mysql_errno() > 0){
	AA_printErrorMsg(mysql_errno().": ".mysql_error());
}else{
    $arr_cat = array();
	while($row = mysql_fetch_array($res)){        
           $max_startnr = 0;
           $max_startnr_track1 = 0;
           $max_startnr_track2 = 0;
           $max_startnr_tech = 0;
           
           $sql=" SELECT 
                        a.xAnmeldung                       
                  FROM 
                        anmeldung AS a 
                        LEFT JOIN start AS s ON s.xAnmeldung = a.xAnmeldung 
                        LEFT JOIN wettkampf AS w USING (xWettkampf) 
                        LEFT JOIN kategorie AS k ON k.xKategorie = w.xKategorie 
                  WHERE 
                        a.xMeeting = ".$_COOKIE['meeting_id'] ."
                        AND w.xKategorie = " .$row[0] ."
                  GROUP BY a.xAnmeldung";   
        
          $res_count=mysql_query($sql);
          if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
          }else{                 
                if (mysql_num_rows($res_count)>0){  
                    $max_startnr=mysql_num_rows($res_count);   
                }
          }  
          
          // check track disziplines in this meeting under 400 m
          $selection_disciplines="(" . $cfgDisciplineType[$strDiscTypeTrack] . ","  
                           . $cfgDisciplineType[$strDiscTypeTrackNoWind]  . ")";   
                             
        
          $res_track1 = mysql_query("SELECT           
                        a.xAnmeldung                        
                    FROM 
                        anmeldung AS a  
                        LEFT JOIN start AS s ON a.xAnmeldung = s.xAnmeldung 
                        LEFT JOIN wettkampf AS w USING ( xWettkampf ) 
                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d USING ( xDisziplin )  
                    WHERE 
                        a.xMeeting = ".$_COOKIE['meeting_id']." 
                        AND w.xKategorie = " .$row[0] ." 
                        AND d.Typ IN" . $selection_disciplines ." 
                    GROUP BY a.xAnmeldung
                    ");     
      
          if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno().": ".mysql_error());
          }else{
                if (mysql_num_rows($res_track1)>0){ 
                    
                    $max_startnr_track1=mysql_num_rows($res_track1);   
                    
                }
          } 
          
          
         // check track disziplines in this meeting  over 400m
         $selection_disciplines="(" . $cfgDisciplineType[$strDiscTypeDistance]  . ","   
                            . $cfgDisciplineType[$strDiscTypeRelay] . ")";   
        
         $res_track2 = mysql_query("SELECT           
                        a.xAnmeldung
                    FROM 
                        anmeldung AS a  
                        LEFT JOIN start AS s ON a.xAnmeldung = s.xAnmeldung 
                        LEFT JOIN wettkampf AS w USING ( xWettkampf ) 
                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d USING ( xDisziplin )  
                    WHERE 
                        a.xMeeting = ".$_COOKIE['meeting_id']."  
                        AND w.xKategorie = " .$row[0] ." 
                        AND d.Typ IN" . $selection_disciplines ." 
                    GROUP BY a.xAnmeldung
                    ");   
                            
              
         if(mysql_errno() > 0){
                AA_printErrorMsg(mysql_errno().": ".mysql_error());
         }else{
                if (mysql_num_rows($res_track2)>0){  
                    $max_startnr_track2=mysql_num_rows($res_track2);
                }
         } 
          
        // check tech disziplines in this meeting
        $selection_disciplines="(" . $cfgDisciplineType[$strDiscTypeJump] . ","  
                           . $cfgDisciplineType[$strDiscTypeJumpNoWind] . ","  
                           . $cfgDisciplineType[$strDiscTypeHigh] . ","  
                           . $cfgDisciplineType[$strDiscTypeThrow] . ","   
                           . $cfgDisciplineType[$strDiscCombined] . ")";     
            
        $res_tech = mysql_query("SELECT           
                        a.xAnmeldung
                    FROM 
                        anmeldung AS a  
                        LEFT JOIN start AS s ON a.xAnmeldung = s.xAnmeldung 
                        LEFT JOIN wettkampf AS w USING ( xWettkampf ) 
                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d USING ( xDisziplin )  
                    WHERE 
                        a.xMeeting = ".$_COOKIE['meeting_id']."  
                        AND w.xKategorie = " .$row[0] ." 
                        AND d.Typ IN" . $selection_disciplines ." 
                    GROUP BY a.xAnmeldung
                    ");   
            
                       
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
        }else{
                if (mysql_num_rows($res_tech)>0){  
                    $max_startnr_tech=mysql_num_rows($res_tech); 
                }
        }   
          
        if ($i == 0) {
		?>
        
        <input type='hidden' name='teams' value='<?php echo $teams; ?>'>        <!-- if teams exist in this meeting -->
        
        <tr>
        <th class='dialog' />
        <th class='dialog' colspan='3'>Alle</th>
        <th class='dialog' colspan='3'><?php echo $strTrack1; ?></th>
        <th class='dialog' colspan='3'><?php echo $strTrack2; ?></th>
        <th class='dialog' colspan='3'><?php echo $strTech; ?></th>
         <th class='dialog' colspan='3'><?php echo $strRest; ?></th> 
        </tr>
        
        <tr>
        <th class='dialog' />
        <th class='dialog' > <?php echo $strOf ?></th>
        <th class='dialog' ><?php echo $strTo ?></th>
        <th class='dialog' ><?php echo $strMax; ?>  </th>
          <th class='dialog' > <?php echo $strOf ?></th>
        <th class='dialog' ><?php echo $strTo ?></th>
         <th class='dialog' ><?php echo $strMax; ?>  </th>
          <th class='dialog' > <?php echo $strOf ?></th>
        <th class='dialog' ><?php echo $strTo ?></th>
         <th class='dialog' ><?php echo $strMax; ?>  </th>
         <th class='dialog' > <?php echo $strOf ?></th>
        <th class='dialog' ><?php echo $strTo ?></th>
         <th class='dialog' ><?php echo $strMax; ?>  </th>
         <th class='dialog' > <?php echo $strOf ?></th> 
        </tr>
        
         <tr>
    <td class='dialog' colspan="13"></td>
       <td class='forms'>
                         
        <input type="text" size="3" value="<?php echo $_GET['of_rest']; ?>"  name="of_rest" id="of_rest" ></td> 
        
      </tr>  
        
        <?php 
        }
        ?>
      
        
        
<tr>
	<td class='dialog'><?php echo $row[1] ?></td>
   	<td class='forms'>
		                 
		<input type="text" size="3" value="<?php echo $_GET['of_'.$row[0]]; ?>"  name="of_<?php echo $row[0] ?>"  id="of_<?php echo $row[0] ?>"></td>
	<td class='forms_right'>
		
		<input type="text" size="3" value="<?php echo $_GET['to_'.$row[0]]; ?>" name="to_<?php echo $row[0] ?>" id="to_<?php echo $row[0] ?>">	</td>
        
        </td>  
    <td class='forms_right_grey'><?php echo $max_startnr; ?></td>
    
    
    
    <td class='forms'>
        
        <input type="text" size="3" value="<?php echo $_GET['of_track1_'.$row[0]]; ?>" name="of_track1_<?php echo $row[0] ?>" id="of_track1_<?php echo $row[0] ?>">    </td>
    <td class='forms_right'>
       
        <input type="text" size="3" value="<?php echo $_GET['to_track1_'.$row[0]]; ?>" name="to_track1_<?php echo $row[0] ?>" id="to_track1_<?php echo $row[0] ?>">    </td>
    
    
    <td class='forms_right_grey'><?php echo $max_startnr_track1; ?></td>
    
    
     <td class='forms'>
        
        <input type="text" size="3" value="<?php echo $_GET['of_track2_'.$row[0]]; ?>" name="of_track2_<?php echo $row[0] ?>" id="of_track2_<?php echo $row[0] ?>">    </td>
    <td class='forms_right'>
        
        <input type="text" size="3" value="<?php echo $_GET['to_track2_'.$row[0]]; ?>" name="to_track2_<?php echo $row[0] ?>" id="to_track2_<?php echo $row[0] ?>">    </td>
    
    <td class='forms_right_grey'><?php echo $max_startnr_track2; ?></td>
    
    
    <td class='forms'>
       
        <input type="text" size="3" value="<?php echo $_GET['of_tech_'.$row[0]]; ?>" name="of_tech_<?php echo $row[0] ?>" id="of_tech_<?php echo $row[0] ?>">    </td>
    <td class='forms_right'>
       
        <input type="text" size="3" value="<?php echo $_GET['to_tech_'.$row[0]]; ?>" name="to_tech_<?php echo $row[0] ?>" id="to_tech_<?php echo $row[0] ?>">    </td>
   
    
    <td class='forms_right_grey'><?php echo $max_startnr_tech; ?></td>
</tr>
		<?php
        $i++;
        $arr_cat[] = $row[0];
       
	}
}
    $anzahl_cat = count($arr_cat);  
   
    
 
   }
   else {
 
         // list all teams
       
         $i = 0;
         // get all teams in this meeting
         $sql="SELECT DISTINCT
                t.xTeam, t.Name, k.Name
          FROM
                anmeldung AS a 
                LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung) 
                LEFT JOIN wettkampf AS w USING (xWettkampf) 
                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                LEFT JOIN team as t  ON (t.xKategorie = k.xKategorie)
                LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
          WHERE
                t.xMeeting = ".$_COOKIE['meeting_id'] ."
                ORDER BY v.sortierwert";     
          
          $res = mysql_query($sql);     
             
          if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
          }else{
                while($row = mysql_fetch_array($res)){        
                        $max_startnr = 0;
                        $max_startnr_track1 = 0;
                        $max_startnr_track2 = 0;
                        $max_startnr_tech = 0;
           
                        $sql="SELECT 
                                    DISTINCT a.xAnmeldung,  
                                    count(t.xTeam)
                              FROM 
                                    anmeldung AS a 
                                    LEFT JOIN start AS s ON s.xAnmeldung = a.xAnmeldung 
                                    LEFT JOIN wettkampf AS w USING (xWettkampf)
                                    LEFT JOIN team AS t ON (t.xTeam = a.xTeam)  
                              WHERE 
                                    a.xMeeting = ".$_COOKIE['meeting_id'] ."
                                    AND t.xTeam = " .$row[0] ."   
                                    GROUP BY t.xTeam";   
         
                        $res_count=mysql_query($sql);
                        if(mysql_errno() > 0){
                            AA_printErrorMsg(mysql_errno().": ".mysql_error());
                        }else{
                            $row_count = mysql_fetch_array($res_count);
                            $max_startnr=$row_count[1];
                        } 
       
                        if ($i == 0) {
                        ?>
                            <input type='hidden' name='teams' value='<?php echo $teams; ?>'>        <!-- if teams exist in this meeting -->   
        
                            <th class='dialog'><?php echo $strTeam ?></th>  
                            <th class='dialog'><?php echo $strCategory ?></th>    
                            <th class='dialog' > <?php echo $strOf ?></th>
                            <th class='dialog' ><?php echo $strTo ?></th>
                            <th class='dialog' ><?php echo $strMax; ?>  </th>
                            <th class='dialog' width='70'></th>
                            <th class='dialog' colspan='2'></th>  
                            </tr>
        
                    <?php 
                    }
                    ?>
                    <tr>
                        <td class='dialog'><?php echo $row[1] ?></td>
                        <td class='dialog'><?php echo $row[2] ?></td> 
                        <td class='forms'>
        
                        <input type="text" size="3" value="<?php echo $_GET['of_'.$row[0]]; ?>" name="of_<?php echo $row[0] ?>" >    </td>
                        <td class='forms_right'>
        
                        <input type="text" size="3" value="<?php echo $_GET['to_'.$row[0]]; ?>" name="to_<?php echo $row[0] ?>" >    </td>
        
                        </td>  
                        <td class='forms_right_grey'><?php echo $max_startnr; ?></td>
    
                        <td class='dialog' />  
                    </tr>
                    <?php
                    $i++;
     
   
                }  // end while
 
        }
   }
?>


 
<tr>
	<td class='dialog' colspan = '13'>
		<hr>
		<input type='radio' name='sort' value='del'>
			<?php echo $strDeleteStartnumbers; ?></input>	</td>
</tr>
</table>

<p />

<table>
<tr>
	<td>
		<button type='submit' onclick="return check_rounds();">
			<?php echo $strAssign; ?>
	  	</button>
	</td>
</tr>
</table>

</form>
 <?php
 }
 ?>
 
<script type="text/javascript">     

function check_rounds(){        
   
       var cat = '';
       var setCat = false;
       <?php for($i=0;$i<$anzahl_cat;$i++){
           ?>
               cat = "<?php echo $arr_cat[$i] ?>";
               if (document.getElementById("of_"+cat).value > 0 || document.getElementById("of_track1_"+cat).value > 0 || document.getElementById("of_track2_"+cat).value > 0 || document.getElementById("of_tech_"+cat).value > 0 ||
                     document.getElementById("to_"+cat).value > 0 || document.getElementById("to_track1_"+cat).value > 0 || document.getElementById("to_track2_"+cat).value > 0 || document.getElementById("to_tech_"+cat).value > 0 ||
                     document.getElementById("of_rest").value > 0) {                                                                                                                                                
                     setCat = true;
               }                  
           <?php                       
        }       
        ?>  
     
        if ((document.getElementById("of_sex1").value > 0 || document.getElementById("to_sex1").value > 0 || document.getElementById("of_sex2").value > 0 || document.getElementById("to_sex2").value > 0) 
            && (setCat)){                
            alert("<?php echo $strNoDoubleFill ?>"); 
            return false;  
        }          
        
        check = confirm("<?php echo $strStartNrConfirm ?>");
        return check;
   
    
}
         
function check_of(){
    
    // check of values in name_of and club_of
   
   if (document.getElementById("name").checked){       
       document.getElementById("club_of").value = '';
   }
  
}       

  

</script>
 
 
 
 
</body>
</html>
