<?php

/**********
 *
 *	print_meeting_teams.php
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
$club_clause="";

// selection arguments
if($_GET['category'] > 0) {		// category selected
	$cat_clause=" AND t.xKategorie = " . $_GET['category'];
}

if($_GET['club'] > 0) {		// club selected
	$club_clause=" AND t.xVerein = " . $_GET['club'];
}


$print = false;
if($_GET['formaction'] == 'print') {		// page for printing 
	$print = true;
}


// start a new HTML page for printing
if ($_GET['list']=='team')
{
	if($print == true) {
		$doc = new PRINT_TeamsPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_TeamsPage($_COOKIE['meeting']);
	}
}
else {
	if($print == true) {
		$doc = new PRINT_TeamDiscPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_TeamDiscPage($_COOKIE['meeting']);
	}
}

if($_GET['cover'] == 'cover') {		// print cover page
	$doc->printCover("$strEntries $strTeams");
}

// Read all teams
$result = mysql_query("
	SELECT
		t.xTeam
		, v.Name
		, k.Name
		, t.Name
		, t.xKategorie
	FROM
		team AS t
		LEFT JOIN kategorie AS k ON (k.xKategorie = t.xKategorie)
		LEFT JOIN verein AS v ON (v.xVerein = t.xVerein)
	WHERE t.xMeeting = " . $_COOKIE['meeting_id'] . "	
	$cat_clause
	$club_clause
	ORDER BY
		v.Sortierwert
		, k.Anzeige
		, t.Name
");

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	$l = 0;		// line counter

	// Team loop
	while ($row = mysql_fetch_row($result))
	{
		if($print == true)
		{	
			if(($_GET['break'] == 'team')
				&& ($l !=0))						// page break after each team 
			{
				$doc->insertPageBreak();
			}
		}
		$doc->printSubTitle("$row[1] $row[2]: $row[3]");
		$l = 0;					// reset line counter

		
		if($_GET['list'] == 'team')	// Print athlete list
		{
			// read all athletes per Team
			$list_res = mysql_query("
				SELECT
					a.xAnmeldung
					, a.Startnummer
					, at.Name
					, at.Vorname
					, at.Jahrgang
				FROM
					anmeldung AS a
					LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
				WHERE a.xTeam = $row[0]    
				ORDER BY
					at.Name
					, at.Vorname
			");                
           
		}
		else									 // Print discipline list
		{
			// read all athletes per team discipline (not relays)   
            $sql = "SELECT
                      d.Name
                      , a.Startnummer
                      , at.Name
                      , at.Vorname
                      , at.Jahrgang
                  FROM
                      anmeldung AS a
                      LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet )
                      LEFT JOIN start AS st ON (a.xAnmeldung = st.xAnmeldung  )
                      LEFT JOIN wettkampf AS w ON (st.xWettkampf = w.xWettkampf) 
                      LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d   ON (d.xDisziplin = w.xDisziplin)  
                WHERE w.xMeeting = " . $_COOKIE['meeting_id'] . "
                  AND w.xKategorie = $row[4]
                  AND d.Typ != " . $cfgDisciplineType[$strDiscTypeRelay] . "  
                  AND a.xTeam = $row[0]    
                GROUP BY
                    st.xStart
                ORDER BY
                    d.Anzeige
                    , at.Name
                    , at.Vorname";     
           
            $list_res = mysql_query($sql);      
		}      

		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else if(mysql_num_rows($list_res) > 0)  // data found
		{
			$d = '';
			while ($list_row = mysql_fetch_row($list_res))
			{
				if($l == 0) {					// new page, print header line
					if (strpos(get_class($doc),"pdf")==false) { printf("<table class='dialog'>\n"); }
					$doc->printHeaderLine();
				}

				if($_GET['list'] == 'team')
				{
					$disc = '';		// list of disciplines      
					
                     $sql = "SELECT
                            d.Kurzname, d.Typ, s.Bestleistung
                        FROM
                            disziplin_" . $_COOKIE['language'] . " AS d
                            LEFT JOIN wettkampf AS w ON (w.xDisziplin = d.xDisziplin )   
                            LEFT JOIN start AS s ON (s.xWettkampf = w.xWettkampf)  
                        WHERE 
                            s.xAnmeldung = $list_row[0]                        
                        ORDER BY
                            d.Anzeige";    
                    
                    $disc_res = mysql_query($sql);         
                    
					if(mysql_errno() > 0)		// DB error
					{
						AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
					}
					else
					{
						$sep = '';	
						while ($disc_row = mysql_fetch_row($disc_res))
						{
							$disc = $disc . $sep . $disc_row[0];	// add discipline  
                           
                            if(($disc_row[1] == $cfgDisciplineType[$strDiscTypeTrack])
                                || ($disc_row[1] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                                || ($disc_row[1] == $cfgDisciplineType[$strDiscTypeRelay])
                                || ($disc_row[1] == $cfgDisciplineType[$strDiscTypeDistance]))
                                {
                                $perf = AA_formatResultTime($disc_row[2]);
                            }
                            else {
                                $perf = AA_formatResultMeter($disc_row[2]);              
                            }
                            
                            if ($perf > 0){
                                $disc = $disc . "(" . $perf. ")"; 
                            }	
							$sep = ", ";	
						}
						mysql_free_result($disc_res);
					}	// ET DB error

					$doc->printLine($list_row[1], $list_row[2] . " " . $list_row[3],
						AA_formatYearOfBirth($list_row[4]), $disc);
				}
				else		// discipline list
				{
					$disc = '';
					if($list_row[0] != $d) {
						$disc = $list_row[0];
					}
					$d = $list_row[0];	// keep discipline

					$doc->printLine($disc, $list_row[1],
						$list_row[2] . " " . $list_row[3],
						AA_formatYearOfBirth($list_row[4]));
				}
				$l++;			// increment line count
			}	// END LOOP Athletes
			mysql_free_result($list_res);
		}	// ET DB error athlets

		// read all relays per Team
		
		 $sql = "SELECT
                s.xStaffel
                , s.Name
                , d.Kurzname
                , d.Name
                , s.Startnummer
            FROM
                staffel AS s
                LEFT JOIN start AS st ON (st.xStaffel = s.xStaffel)   
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = st.xWettkampf  )
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d  ON (d.xDisziplin = w.xDisziplin)                   
            WHERE s.xTeam = $row[0] 
            ORDER BY
                d.Anzeige";        
         
        $rel_res = mysql_query($sql);      

		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else if(mysql_num_rows($rel_res) > 0)  // data found
		{
			// Relay loop
			while ($rel_row = mysql_fetch_row($rel_res))
			{
				if($l == 0) {	// first relay
					if (strpos(get_class($doc),"pdf")==false) { printf("<table class='dialog'>\n"); }
					$doc->printHeaderLine();
				}
				$r++;
				if($_GET['list'] == 'team') {	// athlete list
					$doc->printLine($rel_row[4], $rel_row[1], '', $rel_row[2]);
				}
				else {		// discipline list
					$doc->printLine($rel_row[3], '', $rel_row[1], '');
				}
				$l++;			// increment line count    
				
				 $sql = "SELECT
                        a.Startnummer
                        , at.Name
                        , at.Vorname
                    FROM
                        athlet AS at
                        LEFT JOIN anmeldung AS a ON (a.xAthlet = at.xAthlet)
                        LEFt JOIN start AS st ON (st.xAnmeldung = a.xAnmeldung)
                        LEFT JOIN staffelathlet AS sta ON (sta.xAthletenstart = st.xStart)
                        LEFT JOIN start AS ss ON (sta.xStaffelstart = ss.xStart)  
                    WHERE 
                        ss.xStaffel = $rel_row[0]   
                    ORDER BY
                        sta.Position";        
                
                $ath_res = mysql_query($sql);     

				if(mysql_errno() > 0)		// DB error
				{
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else
				{
					$athletes = '';
					$sep = "";

					while ($ath_row = mysql_fetch_row($ath_res))
					{
						$athletes = $athletes . $sep
							. $ath_row[0] . ". " .  $ath_row[1] . " " .	$ath_row[2];
						$sep = ", ";
					}
					mysql_free_result($ath_res);
				}	// ET DB error athletes per relay

				$doc->printAthletes($athletes);
				$l++;			// increment line count
			}	// END LOOP Relays
			mysql_free_result($rel_res);
		}	// ET DB error relays

		if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n"); }

	}	// END LOOP Team
}	// ET DB error teams

$doc->endPage();		// end HTML page for printing

?>
