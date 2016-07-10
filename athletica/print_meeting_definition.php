<?php

/**********
 *
 *	print_meeting_definition.php
 *	----------------------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_print_page.lib.php');
require('./lib/cl_print_page_pdf.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

//
// Content
// -------

$doc = new PRINT_Definitions_pdf("Definitions");

// get meeting from DB
$result = mysql_query("SELECT m.Name"
							. ", m.Ort"
							. ", m.DatumVon"
							. ", m.DatumBis"
							. ", s.Name"
							. ", DATE_FORMAT(m.DatumVon, '$cfgDBdateFormat')"
							. ", DATE_FORMAT(m.DatumBis, '$cfgDBdateFormat')"
							. " FROM meeting AS m"
							. " LEFT JOIN stadion AS s ON (m.xStadion = s.xStadion)"
							. " WHERE m.xMeeting = ". $_COOKIE['meeting_id']);
							

if(mysql_errno() > 0)	// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else		// no DB error
{
	$row = mysql_fetch_row($result);
	$doc->printPageTitle($row[0]);
	$title = "$row[4], $row[1]";
	$title = "$title, $row[5]";

	if($row[2] != $row[3]) {
		$title = "$title $strDateTo $row[6]";
	}

	$doc->printSubTitle($title);

	mysql_free_result($result);

/*****************************************
 *
 *	 Events: disciplines per categories	
 *
 *****************************************/

	// get events from DB     	
    $sql = "SELECT 
                    w.xWettkampf
                    , w.xKategorie
                    , k.Name
                    , d.Kurzname
                FROM 
                    wettkampf AS w
                    LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (w.xDisziplin = d.xDisziplin)
                WHERE w.xMeeting =" . $_COOKIE['meeting_id'] ."                 
                ORDER BY k.Anzeige, d.Anzeige";  
   
   $result = mysql_query($sql);

	if(mysql_errno() > 0)	// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else			// no DB error
	{
		// display list
		$disc="";
		$k=0;
		$l=0;

		while ($row = mysql_fetch_row($result))
		{
			if($k!=$row[1])		// new kategorie -> new row
			{
				if($l != 0)	{		// not first line
					$doc->printLine($disc);
					$disc="";
				}
				$doc->printSubTitle($row[2]);
				$k=$row[1];
				$l=0;
			}
			if($l!=0) {				// not first line
				$disc = $disc . ", ";	// add separator
			}
			$disc = $disc . $row[3];	// add discipline
			$l++;
		}
		$doc->printLine($disc);
		mysql_free_result($result);
	}			// ET DB error meeting
}		// ET DB error disciplines

$doc->endPage();		// Terminate HTML page
?>
