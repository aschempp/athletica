<?php

/**********
 *
 *	print_meeting_relays.php
 *	------------------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_gui_relaypage.lib.php');
require('./lib/cl_print_relaypage.lib.php');
require('./lib/cl_print_relaypage_pdf.lib.php');
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

// default sort argument: category, relay name
$argument="s.Startnummer, s.Name, k.anzeige, d.Anzeige";

// sort according to "group by" arguments
if ($_GET['discgroup'] == "yes") {
	$argument = "d.Anzeige, " . $argument;
}
if ($_GET['catgroup'] == "yes") {
	$argument = "k.Anzeige, " . $argument;
}
if ($_GET['clubgroup'] == "yes") {
	$argument = "v.Sortierwert, " . $argument;
}

// selection arguments
if($_GET['category'] > 0) {		// category selected
	$cat_clause = " AND s.xKategorie = " . $_GET['category'];
}
if($_GET['discipline'] > 0) {		// discipline selected
	$disc_clause = 	" AND w.xDisziplin = " . $_GET['discipline'];
}
if($_GET['club'] > 0) {		// discipline selected
	$club_clause = " AND v.xVerein = " . $_GET['club'];
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
		$doc = new PRINT_ClubCatDiscRelayPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubCatDiscRelayPage($_COOKIE['meeting']);
	}
}
else if (($_GET['clubgroup'] == "yes")
	&& ($_GET['catgroup'] == "yes"))
{
	if($print == true) {
		$doc = new PRINT_ClubCatRelayPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubCatRelayPage($_COOKIE['meeting']);
	}
}
else if (($_GET['catgroup'] == "yes") 
	&& ($_GET['discgroup'] == "yes"))
{
	if($print == true) {
		$doc = new PRINT_CatDiscRelayPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_CatDiscRelayPage($_COOKIE['meeting']);
	}
}
else if ($_GET['clubgroup'] == "yes")
{
	if($print == true) {
		$doc = new PRINT_ClubRelayPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubRelayPage($_COOKIE['meeting']);
	}
}
else if ($_GET['catgroup'] == "yes")
{
	if($print == true) {
		$doc = new PRINT_CatRelayPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_CatRelayPage($_COOKIE['meeting']);
	}
}
else {
	if($print == true) {
		$doc = new PRINT_RelayPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_RelayPage($_COOKIE['meeting']);
	}
}

if($_GET['cover'] == 'cover') {		// print cover page
	$doc->printCover("$strEntries $strRelays");
	if (strpos(get_class($doc),"pdf")==false) { printf("<p/>"); }
}           

    $sql = "SELECT
                s.xStaffel
                , s.Name
                , d.Kurzname
                , d.Name
                , k.Kurzname
                , k.Name
                , st.xStart
                , v.Name
                , t.Name
                , st.Bestleistung
                , s.Startnummer
            FROM
                start AS st
                LEFT JOIN staffel AS s ON (s.xStaffel = st.xStaffel)
                LEFT JOIN wettkampf AS w ON (st.xWettkampf = w.xWettkampf)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)
                LEFT JOIN kategorie AS k ON (s.xKategorie = k.xKategorie)
                LEFT JOIN verein AS v ON (s.xVerein = v.xVerein)                 
                LEFT JOIN team AS t ON s.xTeam = t.xTeam
            WHERE 
                s.xMeeting = " . $_COOKIE['meeting_id'] . "  
                $cat_clause
                $disc_clause
                $club_clause
            ORDER BY
                $argument";    
        
        $result = mysql_query($sql);    

if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	$s = 0;		// current relay ID
	$d = "";		// current discipline
	$l = 0;		// line counter
	$k = "";		// current category
	$v = "";		// current club

	// full display ordered by name or start nbr
	while ($row = mysql_fetch_row($result))
	{
		// print previous relay, if any
		if($s != $row[0] && $s > 0)
		{
			if((is_a($doc, "PRINT_CatRelayPage"))
				|| (is_a($doc, "PRINT_CatRelayPage_pdf"))
				|| (is_a($doc, "GUI_CatRelayPage")))
		  	{
				$doc->printLine($name, $club, $disc, $perf, $startnbr);
			}
			else if((is_a($doc, "PRINT_ClubRelayPage"))
				|| (is_a($doc, "PRINT_ClubRelayPage_pdf"))
				|| (is_a($doc, "GUI_ClubRelayPage")))
		  	{
				$doc->printLine($name, $cat, $disc, $perf, $startnbr);
			}
			else if((is_a($doc, "PRINT_CatDiscRelayPage"))
				|| (is_a($doc, "PRINT_CatDiscRelayPage_pdf"))
				|| (is_a($doc, "GUI_CatDiscRelayPage")))
		  	{
				$doc->printLine($name, $club, $perf, $startnbr);
			}
			else if((is_a($doc, "PRINT_ClubCatRelayPage"))
				|| (is_a($doc, "PRINT_ClubCatRelayPage_pdf"))
				|| (is_a($doc, "GUI_ClubCatRelayPage")))
		  	{
				$doc->printLine($name, $disc, $perf, $startnbr);
			}
			else if((is_a($doc, "PRINT_ClubCatDiscRelayPage"))
				|| (is_a($doc, "PRINT_ClubCatDiscRelayPage_pdf"))
				|| (is_a($doc, "GUI_ClubCatDiscRelayPage")))
		  	{
				$doc->printLine($name, $perf, $startnbr);
			}
			else {
				$doc->printLine($name, $cat, $club, $disc, $perf, $startnbr);
			}

			$athletes = '';     
                    
            $sql = "SELECT
                            a.Startnummer
                            , at.Name
                            , at.Vorname
                    FROM
                            athlet AS at
                            LEFT JOIN anmeldung AS a ON (a.xAthlet = at.xAthlet)
                            LEFT JOIN start AS st ON (st.xAnmeldung = a.xAnmeldung)
                            LEFt JOIN staffelathlet AS sta ON (sta.xAthletenstart = st.xStart )
                            LEFT JOIN start AS ss ON (sta.xStaffelstart = ss.xStart )  
                    WHERE 
                        ss.xStaffel = $s   
                    GROUP BY
                        sta.xAthletenstart
                    ORDER BY
                        sta.Position";   
           
            $res = mysql_query($sql);    
            
			if(mysql_errno() > 0) {		// DB error
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else {
				$sep = "";

				while ($ath_row = mysql_fetch_row($res))
				{
					$athletes = $athletes . $sep
						.  $ath_row[1] . " " .	$ath_row[2];
					$sep = ", ";
				}
				mysql_free_result($res);
			}

			$doc->printAthletes($athletes);
			$l++;			// increment line count

			$name = "";
			$cat = "";
			$club = "";
			$disc = "";
		}

		if(($_GET['clubgroup']=="yes") && ($v != $row[7])		// next club
			 || ($_GET['catgroup']=="yes") && ($k != $row[4])	// next category
			 || ($_GET['discgroup']=="yes") && ($d != $row[2]))	// next disc.
		{
			if($l != 0) {		// terminate previous block if not first row
				if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n");}

				// check for page break after club / category
				if($print == true)
				{
					if(($_GET['clubbreak']=="yes") && ($v != $row[7])
						|| ($_GET['catbreak']=="yes") && ($k != $row[4])
						|| ($_GET['discbreak']=="yes") && ($d != $row[2]))
					{
						$doc->insertPageBreak();
					}
				}
			}

			if((is_a($doc, "PRINT_CatRelayPage"))
				|| (is_a($doc, "PRINT_CatRelayPage_pdf"))
				|| (is_a($doc, "GUI_CatRelayPage")))
		  	{
				$doc->printSubTitle($row[5]);
			}
			else if((is_a($doc, "PRINT_ClubRelayPage"))
				|| (is_a($doc, "PRINT_ClubRelayPage_pdf"))
				|| (is_a($doc, "GUI_ClubRelayPage")))
		  	{
				$doc->printSubTitle($row[7]);
			}
			else if((is_a($doc, "PRINT_CatDiscRelayPage"))
				|| (is_a($doc, "PRINT_CatDiscRelayPage_pdf"))
				|| (is_a($doc, "GUI_CatDiscRelayPage")))
		  	{
				$doc->printSubTitle($row[5] . " " . $row[3]);
			}
			else if((is_a($doc, "PRINT_ClubCatRelayPage"))
				|| (is_a($doc, "PRINT_ClubCatRelayPage_pdf"))
				|| (is_a($doc, "GUI_ClubCatRelayPage")))
		  	{
				$doc->printSubTitle($row[7] . " " . $row[5]);
			}
			else if((is_a($doc, "PRINT_ClubCatDiscRelayPage"))
				|| (is_a($doc, "PRINT_ClubCatDiscRelayPage_pdf"))
				|| (is_a($doc, "GUI_ClubCatDiscRelayPage")))
		  	{
				$doc->printSubTitle($row[7] . " " . $row[5] . " " . $row[3]);
			}
			else {
				$doc->printSubTitle($row[3]);
			}

			$l = 0;				// reset line counter
			$k = $row[4];		// keep current category
			$v = $row[7];		// keep current club
			$d = $row[2];		// keep current discipline
		}

		if($l == 0) {					// new page, print header line
			if (strpos(get_class($doc),"pdf")==false) { printf("<table class='dialog'>\n"); }
			$doc->printHeaderLine();
		}

		$name = $row[1];	// relay name
		$cat = $row[4];		// category
		$disc = $row[2];	// discipline
		$startnbr = $row[10];	// start number
		$perf = AA_formatResultTime($row[9]);

		if(empty($row[7])) {		// not assigned to a team
			$club = $row[6];		// use club name
		}
		else {
			$club = $row[7];		// use team name
		}
		
		$l++;			// increment line count
		$s = $row[0];
	}

	// print last relay, if any
	if($s  > 0)
	{
		if((is_a($doc, "PRINT_CatRelayPage"))
			|| (is_a($doc, "PRINT_CatRelayPage_pdf"))
			|| (is_a($doc, "GUI_CatRelayPage")))
		{
			$doc->printLine($name, $club, $disc, $perf, $startnbr);
		}
		else if((is_a($doc, "PRINT_ClubRelayPage"))
			|| (is_a($doc, "PRINT_ClubRelayPage_pdf"))
			|| (is_a($doc, "GUI_ClubRelayPage")))
		{
			$doc->printLine($name, $cat, $disc, $perf, $startnbr);
		}
		else if((is_a($doc, "PRINT_CatDiscRelayPage"))
			|| (is_a($doc, "PRINT_CatDiscRelayPage_pdf"))
			|| (is_a($doc, "GUI_CatDiscRelayPage")))
		{
			$doc->printLine($name, $club, $perf, $startnbr);
		}
		else if((is_a($doc, "PRINT_ClubCatRelayPage"))
			|| (is_a($doc, "PRINT_ClubCatRelayPage_pdf"))
			|| (is_a($doc, "GUI_ClubCatRelayPage")))
		{
			$doc->printLine($name, $disc, $perf, $startnbr);
		}
		else if((is_a($doc, "PRINT_ClubCatDiscRelayPage"))
			|| (is_a($doc, "PRINT_ClubCatDiscRelayPage_pdf"))
			|| (is_a($doc, "GUI_ClubCatDiscRelayPage")))
		{
			$doc->printLine($name, $perf, $startnbr);
		}
		else {
			$doc->printLine($name, $cat, $club, $disc, $perf, $startnbr);
		}

		$l++;			// increment line count

		$athletes = '';      		
		
        $sql = "SELECT
                    a.Startnummer
                    , at.Name
                    , at.Vorname
                FROM
                    athlet AS at
                    LEFT JOIN anmeldung AS a ON (a.xAthlet = at.xAthlet  )
                    LEFT JOIN start AS st ON (st.xAnmeldung = a.xAnmeldung)
                    LEFt JOIN staffelathlet AS sta ON (sta.xAthletenstart = st.xStart)  
                    LEFT JOIN start AS ss ON (sta.xStaffelstart = ss.xStart)  
                WHERE 
                    ss.xStaffel = $s  
                GROUP BY
                    sta.xAthletenstart
                ORDER BY
                    sta.Position";     
         
        $res = mysql_query($sql);         
      
		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			$sep = "";

			while ($ath_row = mysql_fetch_row($res))
			{
				$athletes = $athletes . $sep
					. $ath_row[1] . " " .	$ath_row[2];
				$sep = ", ";
			}
			mysql_free_result($res);
		}

		$doc->printAthletes($athletes);
	}

	if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n");}
	mysql_free_result($result);
}						// ET DB error

$doc->endPage();		// end a HTML page for printing

?>
