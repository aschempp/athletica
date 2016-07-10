<?php

/**********
 *
 *	print_timetable.php
 *	-------------------
 *	
 */

require('./lib/common.lib.php');
require('./lib/cl_print_page.lib.php');
require('./lib/cl_print_page_pdf.lib.php');
if(AA_connectToDB() == FALSE)	// invalid DB connection
{
	return;
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}

if(isset($_GET['arg']) && $_GET['arg']=='comp'){
	$doc = new PRINT_TimetableComp_pdf($_COOKIE['meeting']); 
	$doc->printPageTitle($strTimetableComp);
	
	//
	// Display current data
	//
	$sql = "SELECT 
                DISTINCT(r.Datum) 
			    , r.Datum
			FROM 
                runde AS r 
			    LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
			WHERE 
                w.xMeeting = ".$_COOKIE['meeting_id']."  
			ORDER BY r.Datum ASC;";
	$query = mysql_query($sql);
	
	if(mysql_errno() > 0)	// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());    
	}
	else			// no DB error
	{
		//
		// Display all days
		//
		while($row = mysql_fetch_row($query)){
			$dateDayOfWeek = $strDaysOfWeek[date('w', strtotime($row[1]))];
			$dateDay = date('j', strtotime($row[1]));
			$dateMonth = $strMonths[date('n', strtotime($row[1]))];
			$dateYear = date('Y', strtotime($row[1]));
			$dateFormat = $dateDayOfWeek.', '.$dateDay.'. '.$dateMonth.' '.$dateYear;
			
			//
			// Table header
			//
			$doc->startHeaderComp();
			$doc->printHeaderLine($dateFormat);
			$doc->endHeaderComp();
			
			$doc->startTableComp();
			
			//
			// Display all rounds for the current day
			//                     
            $sql2 = "SELECT 
                            TIME_FORMAT(r.Appellzeit, '$cfgDBtimeFormat')
                            , TIME_FORMAT(r.Stellzeit, '$cfgDBtimeFormat')
                            , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                            , rt.Typ
                            , k.Kurzname
                            , d.Kurzname
                            , w.Info
                            , r.Gruppe
                     FROM 
                            runde AS r
                            LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf )
                            LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d  ON (w.xDisziplin = d.xDisziplin)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON r.xRundentyp = rt.xRundentyp
                     WHERE 
                        w.xMeeting = ". $_COOKIE['meeting_id']."                          
                        AND r.Datum = '".$row[0]."'                         
                     ORDER BY r.Appellzeit ASC
                     , r.Stellzeit ASC
                     , r.Startzeit ASC
                     , k.Anzeige
                     , d.Anzeige;";       
            
            $query2 = mysql_query($sql2);   
			
			if(mysql_errno() > 0)	// DB error
			{
				AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
			}
			else			// no DB error
			{
				while($row2 = mysql_fetch_row($query2)){
                    
                    if ($row2[3] == '0'){                // round typ: 0 = ohne
                             $roundtype = " ";
                    }
                    else {
                             $roundtype = $row2[3];
                    }
                    
					$grp = ($row2[7]!='') ? $row2[7] : 0;
					$doc->printLine($row2[0], $row2[4], $row2[5], $roundtype, $grp, $row2[1], $row2[2], $row2[6]);
				}
			}
			
			$doc->endTableComp();
		}
	}
	
	$doc->endPage();
} else {
	$doc = new PRINT_Timetable($_COOKIE['meeting']);
	$doc->printPageTitle($strTimetable . " " . $_COOKIE['meeting']);
	
	//
	// Display current data
	//
	$result = mysql_query("SELECT DISTINCT k.Kurzname"
								. " FROM wettkampf AS w"
								. " LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)"
								. " WHERE w.xMeeting=" . $_COOKIE['meeting_id']  								
								. " ORDER BY k.Anzeige");
	
	if(mysql_errno() > 0)	// DB error
	{
		AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
	}
	else			// no DB error
	{
		$c = 0;
		$catline = "";
		// assemble headerline and category array
		while ($row = mysql_fetch_row($result))
		{
			$catline = $catline . "<th class='timetable_cat'>$row[0]</th>";
			$cats[$row[0]] = '';			// category array
			$c++;									// count items
		}
		mysql_free_result($result);   
		
	    $sql="SELECT 
                    DISTINCT r.Datum
                    , r.Startzeit
                    , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                    , DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
               FROM 
                    runde AS r
                    LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
               WHERE 
                    w.xMeeting=" . $_COOKIE['meeting_id'] . "                                      
               ORDER BY r.Datum
               , r.Startzeit";
               
        $result = mysql_query($sql);   
            
		if(mysql_errno() > 0)	// DB error
		{   
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else			// no DB error
		{
			// display timetable, new row per date/time
			$l=0;
			$d=0;
			while ($row = mysql_fetch_row($result))
			{
				if($row[0] != $d)		// different date = new table
				{
					if($l != 0) {				// not first row
						if (strpos(get_class($doc),"pdf")==false) { printf("</table>\n"); }	// terminate table	
						$doc->insertPageBreak();
						$l = 0;					// reset line counter
					}
					$headerline = "<th class='timetable_date'>$row[3]</th>$catline\n";
					$d = $row[0];				// keep actual date
				}
	
				if($l == 0) {		// new page, print header line
					if (strpos(get_class($doc),"pdf")==false) { printf("<table class='timetable' border='1' rules='all';>\n"); }	
					$doc->printHeaderLine($headerline);
				}
	
				$line = "<th class='timetable_time'>$row[2]</th>";	// time
	
				foreach ($cats as $key => $value)
				{
					$cats[$key]='';		// initialize
				}
	
				// all rounds per date/time     
                $sql = "SELECT 
                                r.xRunde
                                , rt.Typ
                                , k.Kurzname
                                , d.Kurzname
                         FROM 
                                runde AS r
                                LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
                                LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
                                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin  )
                                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON r.xRundentyp = rt.xRundentyp
                         WHERE 
                                w.xMeeting=" . $_COOKIE['meeting_id'] ."                                 
                                AND r.Datum = '" . $row[0] ."' 
                                AND r.Startzeit = '" . $row[1] ." '        
                         ORDER BY r.Datum
                                    , r.Startzeit
                                    , k.Anzeige
                                    , d.Anzeige";    
                
                $res = mysql_query($sql);     
                                    
				if(mysql_errno() > 0)	// DB error
				{
					AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
				}
				else			// no DB error
				{
					
					$j=0;
					$k='';
					while ($tt_row = mysql_fetch_row($res))
					{
                        if ($tt_row[1] == '0'){                // round typ: 0 = ohne
                             $roundtype = " ";
                        }
                        else {
                             $roundtype = $tt_row[1];
                        }
						if($k != $tt_row[2]) 	// different category
						{
							if($j != 0) {				// not first item
								$cats[$k] = $cats[$k] . "</td>";	// terminate previous category
							}
							$k = $tt_row[2];		// keep current category
	
							$cats[$k] = "<td class='timetable_round'>$tt_row[3] $roundtype"; }
						else 									// another event for the same category
						{
							$cats[$k] = $cats[$k] . "\n<br />$tt_row[3] $roundtype";
						}
					}
	
					if($j != 0) {				// items found
						$cats[$k] = $cats[$k] . "</td>";	// terminate previous category
					}
					mysql_free_result($res);
				}
	
				foreach ($cats as $key => $value)
				{
					if(!empty($value)) {
						$line = $line . $cats[$key];	// item per category
					}
					else {
						$line = $line . "<td />";	// no item
					}
				}
	
				$doc->printLine($line);
				$l++;
			}
	
			if($l != 0) {						// not first row
				if (strpos(get_class($doc),"pdf")==false) { printf("</table>");	}	// terminate last table	
			}
	
			mysql_free_result($result);
		}			// ET DB header line error
	}			// ET DB timetable item error
	
	$doc->endPage();
}

?>