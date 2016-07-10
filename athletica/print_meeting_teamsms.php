<?php

/**********
 *
 *	print_meeting_teamsms.php
 *	-------------------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_gui_teampage.lib.php');
require('./lib/cl_print_teampage.lib.php');
require('./lib/cl_print_teampage_pdf.lib.php');
if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
	}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

//
// Content
// -------

$cat_clause="";
$disc_clause=""; 
$club_clause="";

// default sort argument: category, team sm name
$argument="v.sortierwert, k.anzeige, t.Name";  


// sort according to "group by" arguments
if ($_GET['discgroup'] == "yes") {
    $argument = "d.Anzeige, t.Name";
}
if ($_GET['catgroup'] == "yes") {
    $argument = "k.Anzeige, t.Name";
}
if ($_GET['clubgroup'] == "yes") {
    $argument = "v.Sortierwert, k.anzeige, t.Name";
}

// selection arguments
if($_GET['category'] > 0) {		// category selected
	$cat_clause=" AND w.xKategorie = " . $_GET['category'];
}

if($_GET['discipline'] > 0) {        // discipline selected
    $disc_clause =  " AND w.xDisziplin = " . $_GET['discipline'];
}

if($_GET['club'] > 0) {		// club selected
	$club_clause=" AND t.xVerein = " . $_GET['club'];
}


$print = false;
if($_GET['formaction'] == 'print') {		// page for printing 
	$print = true;
}


// Determine document type according to "group by" selection
// and start a new HTML page for printing
if (($_GET['clubgroup'] == "yes")
    && ($_GET['catgroup'] == "yes") 
    && ($_GET['discgroup'] == "yes"))
{
    if($print == true) {
        $doc = new PRINT_ClubCatDiscTeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_ClubCatDiscTeamPage($_COOKIE['meeting']);
    }
}
else if (($_GET['clubgroup'] == "yes")
    && ($_GET['catgroup'] == "yes"))
{
    if($print == true) {
        $doc = new PRINT_ClubCatTeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_ClubCatTeamPage($_COOKIE['meeting']);
    }
}
else if (($_GET['catgroup'] == "yes") 
    && ($_GET['discgroup'] == "yes"))
{
    if($print == true) {
        $doc = new PRINT_CatDiscTeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_CatDiscTeamPage($_COOKIE['meeting']);
    }
}
else if (($_GET['clubgroup'] == "yes") 
    && ($_GET['discgroup'] == "yes"))
{
    if($print == true) {
        $doc = new PRINT_ClubDiscTeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_ClubDiscTeamPage($_COOKIE['meeting']);
    }
}
else if ($_GET['clubgroup'] == "yes")
{
    if($print == true) {
        $doc = new PRINT_ClubTeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_ClubTeamPage($_COOKIE['meeting']);
    }
}
else if ($_GET['catgroup'] == "yes")
{
    if($print == true) {
        $doc = new PRINT_CatTeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_CatTeamPage($_COOKIE['meeting']);
    }
}
else {
    if($print == true) {
        $doc = new PRINT_TeamPage_pdf($_COOKIE['meeting']);
    }
    else {
        $doc = new GUI_TeamPage($_COOKIE['meeting']);
    }
}

if($_GET['cover'] == 'cover') {		// print cover page
	$doc->printCover("$strEntries $strTeamsTeamSM");
}

$enrolSheet = false;
if($_GET['enrolSheet'] == 'enrolSheet') {        // print cover page
    $enrolSheet = true;
}

// Read all teams
/*$result = mysql_query("
	SELECT
		t.xTeam
		, v.Name
		, k.Name
		, t.Name
		, t.xKategorie
	FROM
		team AS t
		, kategorie AS k
		, verein AS v
	WHERE t.xMeeting = " . $_COOKIE['meeting_id'] . "
	AND v.xVerein = t.xVerein
	AND k.xKategorie = t.xKategorie
	$cat_clause
	$club_clause
	ORDER BY
		v.Sortierwert
		, k.Anzeige
		, t.Name
");*/

// Read all teams
$sql = "SELECT DISTINCT t.xTeamsm
			   , v.Name
			   , k.Name
			   , t.Name
			   , t.xKategorie
               , t.Startnummer
               , d.name
               , w.xKategorie
               , d.xDisziplin
               , t.Quali
               , t.Leistung
               , d.Typ
               , TIME_FORMAT(r.Appellzeit, '$cfgDBtimeFormat')
               , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
               , TIME_FORMAT(r.Stellzeit, '$cfgDBtimeFormat')              
               , DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
               , w.info
		  FROM teamsm AS t
	 LEFT JOIN kategorie AS k ON(t.xKategorie = k.xKategorie)
	 LEFT JOIN verein AS v ON(t.xVerein = v.xVerein) 
     LEFT JOIN wettkampf AS w ON(w.xWettkampf = t.xWettkampf) 
     LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf)
     LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON(d.xDisziplin = w.xDisziplin) 
	 	 WHERE t.xMeeting = ".$_COOKIE['meeting_id']." 
	 	   ".$cat_clause."
           ".$disc_clause."   
	 	   ".$club_clause." 
	  ORDER BY " . $argument;   

$result = mysql_query($sql);

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	$s = 0;        // current team ID    //*******************************
    $d = "";        // current discipline
    $l = 0;        // line counter
    $k = "";        // current category
    $v = "";        // current club
   
    
    // Team loop 
    // full display ordered by name or start nbr   
    while ($row = mysql_fetch_row($result))
    {   
        
        
         $doc->event = $row[6] .$row[16];
         $doc->cat = $row[2];
         
         $et = "";
          $ot = "";
          if($row[12] != "00:00"){ // add enrolement time
              $et = ", " . $row[12];
          }
          $ot .= " ($strStarttime $row[13]"; // add starttime
          if($row[14] != "00:00"){ // add manipulation time
              $ot .= ", $strManipulationTime $row[14]";
          }
          $ot .= ")";
          
         $doc->time = $strEnrolement. ": " . $row[15] . $et;
         $doc->timeinfo = $ot;
        
        
        
        
        
        
        
        
          // print previous team sm, if any
        if($s != $row[0] && $s > 0)
        {
            if((is_a($doc, "PRINT_CatTeamPage"))
				|| (is_a($doc, "PRINT_CatTeamPage_pdf"))
                || (is_a($doc, "GUI_CatTeamPage")))
              {
                $doc->printLine($name, $club, $disc, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
            }
            else if((is_a($doc, "PRINT_ClubTeamPage"))
				|| (is_a($doc, "PRINT_ClubTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubTeamPage")))
              {
                $doc->printLine($name, $cat, $disc, $perf, $startnbr,$enrolSheet,$quali, $teamPerf );
            }
            else if((is_a($doc, "PRINT_CatDiscTeamPage"))
				|| (is_a($doc, "PRINT_CatDiscTeamPage_pdf"))
                || (is_a($doc, "GUI_CatDiscTeamPage")))
              {
                $doc->printLine($name, $club, $perf, $startnbr,$enrolSheet, $quali, $teamPerf );
            }
            else if((is_a($doc, "PRINT_ClubDiscTeamPage"))
				|| (is_a($doc, "PRINT_ClubDiscTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubDiscTeamPage")))
              {
                $doc->printLine($name, $cat, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
            }
            else if((is_a($doc, "PRINT_ClubCatTeamPage"))
				|| (is_a($doc, "PRINT_ClubCatTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubCatTeamPage")))
              {
                $doc->printLine($name, $disc, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
            }
            else if((is_a($doc, "PRINT_ClubCatDiscTeamPage"))
				|| (is_a($doc, "PRINT_ClubCatDiscTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubCatDiscTeamPage")))
              {
                $doc->printLine($name, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
            }
            else {
                $doc->printLine($name, $cat, $club, $disc, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
            }

            $athletes = '';
            $sql="SELECT a.xAnmeldung
                                , a.Startnummer
                                , at.Name
                                , at.Vorname
                                , at.Jahrgang 
                                , s.Bestleistung
                                , d.Typ 
                                , d.xDisziplin 
                           FROM anmeldung AS a 
                      LEFT JOIN teamsmathlet AS sma ON(a.xAnmeldung = sma.xAnmeldung) 
                      LEFT JOIN athlet AS at ON(a.xAthlet = at.xAthlet) 
                      LEFT JOIN start AS s ON(s.xAnmeldung = a.xAnmeldung) 
                      LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf) 
                      LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON (d.xDisziplin = w.xDisziplin)    
                            WHERE sma.xTeamsm = ".$s." 
                       ORDER BY at.Name
                                   , at.Vorname";
         
            $res = mysql_query($sql);
            
            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                $sep = "";

                while ($ath_row = mysql_fetch_row($res))
                {  
                    if ($disc_keep != $ath_row[7]) {
                       continue;
                    }
                    $perf = 0;
                    if(($ath_row[6] == $cfgDisciplineType[$strDiscTypeJump])
                        || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                        || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeThrow])
                        || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeHigh])) {
                        $perf = AA_formatResultMeter($ath_row[5]);
                    }
                    else {
                        if(($ath_row[6] == $cfgDisciplineType[$strDiscTypeTrack])
                            || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                            $perf = AA_formatResultTime($ath_row[5], true, true);
                        }else{
                            $perf = AA_formatResultTime($ath_row[5], true);
                        }
                    } 
                    
                    $athletes = $athletes . $sep
                         .  $ath_row[1] .". " .  $ath_row[2] . " " .    $ath_row[3] . " (" . $perf .") ";
                    $sep = ", ";
                }
                mysql_free_result($res);
            }

            $doc->printAthletes($athletes, true);
            $l++;            // increment line count
            

            $name = "";
            $cat = "";
            $club = "";
            $disc = "";
        }

        if(($_GET['clubgroup']=="yes") && ($v != $row[1])        // next club
             || ($_GET['catgroup']=="yes") && ($k != $row[4])    // next category
             || ($_GET['discgroup']=="yes") && ($d != $row[6]))    // next disc.
        {
            if($l != 0) {        // terminate previous block if not first row
				if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n"); }

                // check for page break after club / category
                if($print == true)
                {
                    if(($_GET['clubbreak']=="yes") && ($v != $row[1])
                        || ($_GET['catbreak']=="yes") && ($k != $row[4])
                        || ($_GET['discbreak']=="yes") && ($d != $row[6]))
                    {
                        $doc->insertPageBreak();                        
                    }
                }
            }

            if((is_a($doc, "PRINT_CatTeamPage"))
				|| (is_a($doc, "PRINT_CatTeamPage_pdf"))
                || (is_a($doc, "GUI_CatTeamPage")))
              {
                $doc->printTitle($row[2]);
            }
            else if((is_a($doc, "PRINT_ClubTeamPage"))
				|| (is_a($doc, "PRINT_ClubTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubTeamPage")))
              {
                $doc->printTitle($row[1]);
            }
            else if((is_a($doc, "PRINT_CatDiscTeamPage"))
				|| (is_a($doc, "PRINT_CatDiscTeamPage_pdf"))
                || (is_a($doc, "GUI_CatDiscTeamPage")))
              {
                $doc->printTitle($row[2] . " " . $row[6]);
            }
            else if((is_a($doc, "PRINT_ClubDiscTeamPage"))
				|| (is_a($doc, "PRINT_ClubDiscTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubDiscTeamPage")))
              {
                $doc->printTitle($row[1] . " " . $row[6]);
            }
            else if((is_a($doc, "PRINT_ClubCatTeamPage"))
				|| (is_a($doc, "PRINT_ClubCatTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubCatTeamPage")))
              {
                $doc->printTitle($row[1] . " " . $row[2]);
            }
            else if((is_a($doc, "PRINT_ClubCatDiscTeamPage"))
				|| (is_a($doc, "PRINT_ClubCatDiscTeamPage_pdf"))
                || (is_a($doc, "GUI_ClubCatDiscTeamPage")))
              {
                $doc->printTitle($row[1] . " " . $row[2] . " " . $row[6]);
            }
            else {
                $doc->printTitle($row[6]);
            }

            $l = 0;                // reset line counter
            $k = $row[4];        // keep current category
            $v = $row[1];        // keep current club
            $d = $row[6];        // keep current discipline
           
        }

        if($l == 0) {                    // new page, print header line
            if (strpos(get_class($doc),"pdf")==false) { printf("<table class='dialog'>\n"); }
            $doc->printHeaderLine($enrolSheet);

        }

        $name = $row[3];    // team name
        $cat = $row[2];        // category
        $club = $row[1];
        $disc = $row[6];    // discipline
        $startnbr = $row[5];    // start number        
        
        $quali = $row[9]; 
        if(($row[11] == $cfgDisciplineType[$strDiscTypeJump])
                        || ($row[11] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                        || ($row[11] == $cfgDisciplineType[$strDiscTypeThrow])
                        || ($row[11] == $cfgDisciplineType[$strDiscTypeHigh])) {
                        $teamPerf = AA_formatResultMeter($row[10]);
        }
        else {
            if(($row[11] == $cfgDisciplineType[$strDiscTypeTrack])
                            || ($row[11]== $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                            $teamPerf = AA_formatResultTime($row[10], true, true);
            }else{
                $teamPerf = AA_formatResultTime($row[10], true);
            }
        }        
        
        $l++;            // increment line count
        $s = $row[0];
        $disc_keep = $row[8];  
    }

    // print last relay, if any
    if($s  > 0)
    {
        if((is_a($doc, "PRINT_CatTeamPage"))
			|| (is_a($doc, "PRINT_CatTeamPage_pdf"))
            || (is_a($doc, "GUI_CatTeamPage")))
        {
            $doc->printLine($name, $club, $disc, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
        }
        else if((is_a($doc, "PRINT_ClubTeamPage"))
			|| (is_a($doc, "PRINT_ClubTeamPage_pdf"))
            || (is_a($doc, "GUI_ClubTeamPage")))
        {
            $doc->printLine($name, $cat, $disc, $perf, $startnbr,$enrolSheet, $quali, $teamPerf);
        }
        else if((is_a($doc, "PRINT_CatDiscTeamPage"))
			|| (is_a($doc, "PRINT_CatDiscTeamPage_pdf"))
            || (is_a($doc, "GUI_CatDiscTeamPage")))
        {
            $doc->printLine($name, $club, $perf, $startnbr,$enrolSheet);
        }
        else if((is_a($doc, "PRINT_ClubCatTeamPage"))
			|| (is_a($doc, "PRINT_ClubCatTeamPage_pdf"))
            || (is_a($doc, "GUI_ClubCatTeamPage")))
        {
            $doc->printLine($name, $disc, $perf, $startnbr,$enrolSheet,  $quali, $teamPerf);
        }
        else if((is_a($doc, "PRINT_ClubCatDiscTeamPage"))
			|| (is_a($doc, "PRINT_ClubCatDiscTeamPage_pdf"))
            || (is_a($doc, "GUI_ClubCatDiscTeamPage")))
        {
            $doc->printLine($name, $perf, $startnbr,$enrolSheet,  $quali, $teamPerf);
        }
        else {
            $doc->printLine($name, $cat, $club, $disc, $perf, $startnbr, $enrolSheet, $quali, $teamPerf );
        }

        $l++;            // increment line count

        $athletes = '';
            $sql="SELECT a.xAnmeldung
                                , a.Startnummer
                                , at.Name
                                , at.Vorname
                                , at.Jahrgang 
                                , s.Bestleistung
                                , d.Typ 
                                , d.xDisziplin 
                           FROM anmeldung AS a 
                      LEFT JOIN teamsmathlet AS sma ON(a.xAnmeldung = sma.xAnmeldung) 
                      LEFT JOIN athlet AS at ON(a.xAthlet = at.xAthlet) 
                      LEFT JOIN start AS s ON(s.xAnmeldung = a.xAnmeldung) 
                      LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf) 
                      LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON (d.xDisziplin = w.xDisziplin)    
                            WHERE sma.xTeamsm = ".$s." 
                       ORDER BY at.Name
                                   , at.Vorname";
        $res = mysql_query($sql); 
        
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
            $sep = "";

            while ($ath_row = mysql_fetch_row($res))
            {
                if ($disc_keep != $ath_row[7]) {
                       continue;
                    }
                    
                 $perf = 0;
                    if(($ath_row[6] == $cfgDisciplineType[$strDiscTypeJump])
                        || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                        || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeThrow])
                        || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeHigh])) {
                        $perf = AA_formatResultMeter($ath_row[5]);
                    }
                    else {
                        if(($ath_row[6] == $cfgDisciplineType[$strDiscTypeTrack])
                            || ($ath_row[6] == $cfgDisciplineType[$strDiscTypeTrackNoWind])){
                            $perf = AA_formatResultTime($ath_row[5], true, true);
                        }else{
                            $perf = AA_formatResultTime($ath_row[5], true);
                        }
                    } 
                    
                    $athletes = $athletes . $sep
                         .  $ath_row[1] .". " .  $ath_row[2] . " " .    $ath_row[3] . " (" . $perf .") ";
                    $sep = ", ";                      
               
            }
            mysql_free_result($res);
        }

        $doc->printAthletes($athletes, true);
    }

	if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n"); }
    mysql_free_result($result);
}                        // ET DB error
    
    

$doc->endPage();		// end HTML page for printing

?>
