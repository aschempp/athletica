<?php

/**********
 *
 *	entries maintenance functions
 *	-----------------------------
 *	
 */

if (!defined('AA_ENTRIES_LIB_INCLUDED'))
{
	define('AA_ENTRIES_LIB_INCLUDED', 1);



/**
 *	assign startnumbers
 *	-------------------
 */
function AA_entries_assignStartnumbers()
{
	require('./lib/common.lib.php');

	//
	// Content
	// -------

	if(empty($_COOKIE['meeting_id'])) {
		AA_printErrorMsg($GLOBALS['strNoMeetingSelected']);
	}

	if ($_GET['sort']!="del")		// assign startnumbers
	{
		// sort argument
		if ($_GET['sort']=="name") {
		  $argument="athlet.Name, athlet.Vorname";
	  	} else if ($_GET['sort']=="nbr") {
		  $argument="anmeldung.Startnummer";
		} else if ($_GET['sort']=="cat") {
		  $argument="kategorie.Anzeige, athlet.Name, athlet.Vorname";
		} else if ($_GET['sort']=="club") {
		  $argument="verein.Sortierwert, athlet.Name, athlet.Vorname";
		} else if ($_GET['sort']=="club_cat") {
		  $argument="verein.Sortierwert, kategorie.Anzeige, athlet.Name, athlet.Vorname";
		} else {
		  $argument="athlet.Name, athlet.Vorname";
		}

	  	// assignment rules
		if(!empty($_GET['start'])) {
		  $nbr = $_GET['start'] - 1;		// first number
		}
		else {
			$nbr = $cfgNbrStartWith - 1;	// default
		}

		if((!empty($_GET['catgap'])) || ($_GET['catgap'] == '0')) {
		  $catgap = $_GET['catgap'];		// nbr gap between each category
		}
		else {
			$catgap = $cfgNbrCategoryGap;	// default
		}

	  	if((!empty($_GET['clubgap'])) || ($_GET['clubgap'] == '0'))  {
		  $clubgap = $_GET['clubgap'];	// nbr gap between each club
		}
		else {
		  $clubgap = $cfgNbrClubyGap;	// default
		}

		//
		// Read athletes
		//

		mysql_query("LOCK TABLES athlet AS at READ, kategorie AS k READ, verein AS v READ"
				  . ", anmeldung AS a wRITE");      
		
         $sql = "SELECT 
                        a.xAnmeldung
                        , a.xKategorie
                        , at.xVerein
                 FROM 
                        anmeldung AS a
                        LEFT JOIN athlet AS at ON (a.xAthlet = at.xAthlet)
                        LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)
                        LEFT JOIN verein AS v ON (at.xVerein = v.xVerein)
                 WHERE 
                        a.xMeeting = " . $_COOKIE['meeting_id'] ."                          
                 ORDER BY " . $argument;      
                 
        $result = mysql_query($sql);     

		if(mysql_errno() > 0)		// DB error
		{
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else if(mysql_num_rows($result) > 0)  // data found
		{
		  $k = 0;	// initialize current category
		  $v = 0;	// initialize current club

		  // Assign startnumbers
		  while ($row = mysql_fetch_row($result))
		  {
		  		if (($v != $row[2])		// new club
			  		&& ($clubgap > 0)		// gap between clubs
			  		&& ($v > 0)				// not first row
			  		&& (($_GET['sort']=="club_cat")	// gap after cat
				  		|| ($_GET['sort']=="club")))
		  		{
				  $nbr = $nbr + $clubgap;	// calculate next number
			  	}
			  	else if (($k != $row[1])		// new category
			  		&& ($catgap > 0)				// gap between categories
			  		&& ($k > 0)						// not first row
					&& (($_GET['sort']=="club_cat")	// gap after cat
					  || ($_GET['sort']=="cat")))
			  	{
				  $nbr = $nbr + $catgap;				// calculate next number
			  	}
				else {
					$nbr++;
				}
			  	mysql_query("UPDATE anmeldung SET"
						  . " Startnummer='" . $nbr
						  . "' WHERE xAnmeldung='" . $row[0] . "'");

			  	if(mysql_errno() > 0) {
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			  	}

			  	$k = $row[1];	// keep current category
			  	$v = $row[2];	// keep current club
		  	}
		  	mysql_free_result($result);
	  	}						// ET DB error
		mysql_query("UNLOCK TABLES");
	}
	else		// delete startnumbers
	{
		mysql_query("LOCK TABLE anmeldung WRITE");

	  	mysql_query("UPDATE anmeldung SET"
					  . " Startnummer='0'"
					  . " WHERE xMeeting='" . $_COOKIE['meeting_id'] . "'");
		if(mysql_errno() > 0)
		{
		  AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}

		mysql_query("UNLOCK TABLES");
	}
}


}		// AA_ENTRIES_LIB_INCLUDED
?>
