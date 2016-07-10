<?php

/**********
 *
 *	print_meeting_entries.php
 *	-------------------------
 *	
 */                            
require('./lib/cl_gui_entrypage.lib.php');
require('./lib/cl_print_entrypage.lib.php');
require('./lib/cl_print_entrypage_pdf.lib.php');
require('./lib/cl_export_entrypage.lib.php');
require('./lib/common.lib.php');


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
$contestcat_clause="";
$athlete_clause="";
$licType_clause=""; 


// basic sort argument (default: sort by name)
if ($_GET['sort'] == "nbr") {
	$argument = "a.Startnummer, at.Name, at.Vorname, d.Anzeige";
} elseif ($_GET['sort'] == "bestperf" && ($_GET['discgroup'] || $_GET['discipline'] > 0)) { 
      $argument = "s.Bestleistung, at.Name, at.Vorname"; 
}
 else
 {
	//$argument = "at.Name, at.Vorname, d.Anzeige";
	$argument = "at.Name, at.Vorname, at.xAthlet, d2.Name, d.Anzeige";   
}

// sort according to "group by" arguments
if ($_GET['discgroup'] == "yes") { 
	$argument = "d.Anzeige, " . $argument;
}
if ($_GET['catgroup'] == "yes") {
	$argument = "k.Anzeige, " . $argument;
}
if ($_GET['contestcatgroup'] == "yes") {
	$argument = "ck.Anzeige, d2.Anzeige,  " . $argument;
}
if ($_GET['clubgroup'] == "yes") {
	$argument = "v.Sortierwert, " . $argument;
}

// selection arguments
if($_GET['athletecat'] > 0) {		// category selected
	$cat_clause = " AND a.xKategorie = " . $_GET['athletecat'];
}
if($_GET['discipline'] > 0) {		// discipline selected
	$disc_clause = " AND w.xDisziplin = " . $_GET['discipline'];   
}
if($_GET['club'] > 0) {		// club selected
	$club_clause = " AND v.xVerein = " . $_GET['club'];
} 
if($_GET['licenseType'] > 0) {		// licensetype selected
		if ($_GET['licenseType']==1){
		     $licType_clause = " AND at.Lizenztyp IN (0, " . $_GET['licenseType']. ") ";   
		}
		elseif ($_GET['licenseType']==4){ 
               $licType_clause = " AND at.Lizenztyp IN (0, 1) AND ba.license_paid = 'n' ";      
        }
        else {
        	 $licType_clause = " AND at.Lizenztyp = " . $_GET['licenseType'];
		}
}   

if($_GET['paymentStatus'] > 0) {        // payment staus selected      
        $_GET['payment'] = 'payment';
        $paymentStatus = $_GET['paymentStatus'];
}   

 
if($_GET['category'] > 0){
	$contestcat_clause = " AND w.xKategorie = " .$_GET['category']; 
}  
$date = '%';  
if(isset($_GET['date']) && !empty($_GET['date'])) {     
		$date_clause = " AND  r.Datum LIKE '" . $_GET['date'] ."'";
}   

$print = false;
if($_GET['formaction'] == 'print') {		// page for printing 
	$print = true;
}

$export = false;
$limitNrSQL = "";

// if exporting, only one layout is needed
if($_GET['formaction'] == 'export'){
	if($_GET['limitNr'] == "yes"){ // check if numbers are limited
		if(!empty($_GET['limitNrFrom']) && !empty($_GET['limitNrTo'])){
			$limitNrSQL = " AND a.Startnummer >= ".$_GET['limitNrFrom']." AND a.Startnummer <= ".$_GET['limitNrTo']." ";
		}
	}
	$doc = new EXPORT_EntryPage($_COOKIE['meeting'], "csv");
	$export = true;
}
// Determine document type according to "group by" selection
// and start a new HTML page for printing
elseif (($_GET['clubgroup'] == "yes")
	&& ($_GET['catgroup'] == "yes" || $_GET['contestcatgroup'] == "yes") 
	&& ($_GET['discgroup'] == "yes"))
{
	if($print == true) {
		$doc = new PRINT_ClubCatDiscEntryPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubCatDiscEntryPage($_COOKIE['meeting']);
	}
}
else if (($_GET['clubgroup'] == "yes")
	&& ($_GET['catgroup'] == "yes" || $_GET['contestcatgroup'] == "yes"))
{
	if($print == true) {
		$doc = new PRINT_ClubCatEntryPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubCatEntryPage($_COOKIE['meeting']);
	}
}
else if (($_GET['catgroup'] == "yes" || $_GET['contestcatgroup'] == "yes")
	&& ($_GET['discgroup'] == "yes"))
{
	if($print == true) {
		$doc = new PRINT_CatDiscEntryPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_CatDiscEntryPage($_COOKIE['meeting']);
	}
}
else if ($_GET['clubgroup'] == "yes")
{
	if($print == true) {
		$doc = new PRINT_ClubEntryPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubEntryPage($_COOKIE['meeting']);
	}
}
else if ($_GET['catgroup'] == "yes" || $_GET['contestcatgroup'] == "yes")
{
	if($print == true) {
		$doc = new PRINT_CatEntryPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_CatEntryPage($_COOKIE['meeting']);
	}  
}   
else
{
	if($print == true) {
		$doc = new PRINT_EntryPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_EntryPage($_COOKIE['meeting']);
	}
}
   
if($_GET['cover'] == 'cover' && !$export) { // print cover page
	$doc->printCover("$strEntries $strAthletes");
	if (strpos(get_class($doc),"pdf")==false) { printf("<p/>"); }

}
 
 $reduction=AA_getReduction();      

    $sql = "SELECT 
                DISTINCT a.xAnmeldung
                , a.Startnummer
                , at.Name
                , at.Vorname
                , at.Jahrgang
                , k.Kurzname
                , k.Name
                , v.Name
                , t.Name
                , d.Kurzname
                , d.Name
                , d.Typ
                , s.Bestleistung
                , if(at.xRegion = 0, at.Land, re.Anzeige)
                , ck.Kurzname
                , ck.Name
                , s.Bezahlt
                , w.Info
                , d2.Kurzname
                , d2.Name
                , v.Sortierwert
                , k.Anzeige
                , w.Startgeld  
                , w.mehrkampfcode                 
            FROM
                anmeldung AS a
                LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie) 
                LEFT JOIN athlet AS at  ON (a.xAthlet = at.xAthlet)
                LEFT JOIN start AS s ON (s.xAnmeldung = a.xAnmeldung)
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf) 
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)                 
                LEFT JOIN kategorie AS ck ON (ck.xKategorie = w.xKategorie)                     
                LEFT JOIN verein AS v ON (at.xVerein = v.xVerein  )                       
                LEFT JOIN runde AS r ON (s.xWettkampf = r.xWettkampf) 
                LEFT JOIN team AS t ON a.xTeam = t.xTeam
                LEFT JOIN region as re ON at.xRegion = re.xRegion
                LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d2 ON (w.Typ = 1 AND w.Mehrkampfcode = d2.Code)
                LEFT JOIN base_athlete AS ba ON (ba.license = at.Lizenznummer)                             
            WHERE 
                a.xMeeting = " . $_COOKIE['meeting_id'] . "  
                $cat_clause
                $disc_clause
                $club_clause
                $contestcat_clause
                $date_clause
                $athlete_clause 
                $licType_clause                 
                $limitNrSQL           
           ORDER BY
            $argument";      
 
$result = mysql_query($sql); 
  
if(mysql_errno() > 0)		// DB error
{
	AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else if(mysql_num_rows($result) > 0)  // data found
{
	$a = 0;			// current athlete ID
	$d = "";		// current discipline  
	$l = 0;			// line counter
	$k = "";		// current category
	$v = "";		// current club
	$ck = "";		// current contest category
	$dd = "";       // current discipline 
	$paymentPrint = true;
      
	// full list, sorted by name or start nbr
	while ($row = mysql_fetch_row($result))
	{        
              
		// print previous athlete, if any   
		$pl=false;    
	 
	   if ($_GET['clubgroup']=="yes" 
				&& $_GET['contestcatgroup']=="yes" 
				&& $_GET['discgroup']=="yes"){
		   if (($a != $row[0] && $a > 0)  ||   ($v != $row[7]  && $a > 0)  || ($a == $row[0] && $d!= $row[9] && $a > 0)) {	 	   	  
			   $pl=true;	
		   }
	   }
	   elseif ($_GET['clubgroup']=="yes" 
				&& $_GET['catgroup']=="yes" 
				&& $_GET['discgroup']=="yes"){
		   if (($a != $row[0] && $a > 0)  ||   ($v != $row[7]  && $a > 0) || ($a == $row[0] && $d!= $row[9] && $a > 0)) {	
			   $pl=true;	
		   }
	   }
	   elseif ($_GET['clubgroup']=="yes" 
				&& $_GET['catgroup']=="yes" 
				&& $_GET['contestcatgroup']=="yes" 
				&& $_GET['discgroup']=="yes"){
		   if (($a != $row[0] && $a > 0)  ||   ($v != $row[7]  && $a > 0) || ($a == $row[0] && $d!= $row[9] && $a > 0)) {	
			   $pl=true;	
		   }
	   }
	   elseif ( $_GET['contestcatgroup']=="yes" 
				&& $_GET['discgroup']=="yes"){
		   if (($a != $row[0] && $a > 0)  ||   ($dd != $row[9]  && $a > 0)) {                  
			   $pl=true;	
		   }
	   }
	   else {
			 if($a != $row[0] && $a > 0){
			   $pl=true;	
			 } 
	   }  
	 
      if ($pl) { 
              if ($paymentStatus > 0){
                 if($paymentStatus == 1) {                   
                   if (strpos($disc, '[N]')){                       
                       $paymentPrint = false;
                       
                   }  
                   else {
                       $paymentPrint = true;
                   }
                   
                 }
                 elseif ($paymentStatus == 2) {        
                    if (strpos($disc, '[N]') && strpos($disc, '[Y]')){                                           
                        $paymentPrint = true;
                   }  
                   else {
                       $paymentPrint = false;
                   }      
                 }
                 elseif ($paymentStatus == 3) {        
                    if (strpos($disc, '[Y]')){                       
                       $paymentPrint = false;
                   }        
                 }
                 if (strpos($disc, '[]')){                       
                     $paymentPrint = false;
                }
            }
     
      }
     
     
     
		 if ($pl) { 
		    //if ($row[23] > 0 && !isset($cfgCombinedDef[$row[23]])){  
		    //   $disc=substr($disc,0,-3).")";  
			//} 
            
		    if ($paymentPrint) {
               
                if (strpos($disc, '[]')){                       
                    $disc = '';
                }
                
			    if((is_a($doc, "PRINT_CatEntryPage_pdf"))
					|| (is_a($doc, "PRINT_CatEntryPage"))
				    || (is_a($doc, "GUI_CatEntryPage")))
			    {  
				    $doc->printLine($nbr, $name, $year, $club, $disc, $ioc);
			    }
			    else if((is_a($doc, "PRINT_ClubEntryPage_pdf"))
					|| (is_a($doc, "PRINT_ClubEntryPage"))
				    || (is_a($doc, "GUI_ClubEntryPage")))
			    {     
				    $doc->printLine($nbr, $name, $year, $cat, $disc, $ioc);
			    }
			    else if((is_a($doc, "PRINT_CatDiscEntryPage_pdf")) 
					|| (is_a($doc, "PRINT_CatDiscEntryPage"))
				    || (is_a($doc, "GUI_CatDiscEntryPage")))
			    {   
				    $doc->printLine($nbr, $name, $year, $club, $perf, $ioc);
			    }
			    else if((is_a($doc, "PRINT_ClubCatEntryPage_pdf")) 
					|| (is_a($doc, "PRINT_ClubCatEntryPage")) 
				    || (is_a($doc, "GUI_ClubCatEntryPage")))
			    {     
				    $doc->printLine($nbr, $name, $year, $disc, $ioc);
			    }
			    else if((is_a($doc, "PRINT_ClubCatDiscEntryPage_pdf")) 
					|| (is_a($doc, "PRINT_ClubCatDiscEntryPage")) 
				    || (is_a($doc, "GUI_ClubCatDiscEntryPage")))
			    {  
				    $doc->printLine($nbr, $name, $year, $perf, $ioc);  
			    }
			    else
			    {  
				    $doc->printLine($nbr, $name, $year, $cat, $club, $disc, $ioc, $paid, $perf, $mkcode);  
                  
			    }                                      
            }
            else {
                $paymentPrint = true;
            }
		  if ($_GET['discgroup']=="yes" 
							&& $_GET['clubgroup']=="yes" 
							&& $_GET['contestcatgroup']==""  
							&& $_GET['catgroup']=="") {   
				$nbr = "";
				$name = "";
				$year = "";
				$cat = "";
				$club = "";
				$disc = "";
				$saso = "";
				$sep = "";
				$ioc = "";
				$paid = "";
				$m = "";
                $mkcode = "";  
		  }  
		  elseif (($_GET['discgroup']=="yes") 
							&& ( ($_GET['catgroup']=="yes") 
										||  ($_GET['contestcatgroup']=="yes") 
										||  ($_GET['clubgroup']=="yes"))){
		  }
		  else { 
				$nbr = "";
				$name = "";
				$year = "";
				$cat = "";
				$club = "";
				$disc = "";
				$saso = "";
				$sep = "";
				$ioc = "";
				$paid = "";
				$m = "";
                $mkcode = "";  
		  }  
		}
	
		if(($_GET['clubgroup']=="yes") && ($v != $row[7])		// next club
			 || ($_GET['catgroup']=="yes") && ($k != $row[5])	// next category
			 || ($_GET['discgroup']=="yes") && ($d != $row[9])	// next discipline
			 || ($_GET['contestcatgroup']=="yes") && ($ck != $row[14]))	// next contest category
		{  
			if($l != 0) {		// terminate previous block if not first row
				if(!$export and strpos(get_class($doc),"pdf")==false){ printf("</table>\n"); }
				
				// check for page break after club / category
				if($print == true)
				{
					if(($_GET['clubbreak']=="yes") && ($v != $row[7])
						|| ($_GET['catbreak']=="yes") && ($k != $row[5])
						|| ($_GET['discbreak']=="yes") && ($d != $row[9])
						|| ($_GET['contestcatbreak']=="yes") && ($ck != $row[14]))
					{
						$doc->insertPageBreak();
					}
				}
			}
			
			if(($_GET['contestcatgroup']=="yes") && ($ck != $row[14])){
				$catName = $row[15];
			}
			if(($_GET['catgroup']=="yes") && ($k != $row[5])){
				$catName = $row[6];
			}
		  
			if((is_a($doc, "PRINT_CatEntryPage_pdf"))
				|| (is_a($doc, "PRINT_CatEntryPage"))
				|| (is_a($doc, "GUI_CatEntryPage")))
			{
				$doc->printSubTitle($catName);
			}
			else if((is_a($doc, "PRINT_ClubEntryPage_pdf"))
				|| (is_a($doc, "PRINT_ClubEntryPage"))
				|| (is_a($doc, "GUI_ClubEntryPage")))
			{
				$doc->printSubTitle($row[7]);
			}
			else if((is_a($doc, "PRINT_CatDiscEntryPage_pdf")) 
				|| (is_a($doc, "PRINT_CatDiscEntryPage")) 
				|| (is_a($doc, "GUI_CatDiscEntryPage")))
			{
                if ($row[23] > 0){
                        $doc->printSubTitle($catName . " " . $row[10]. " " . $row[19]);
                }	
                else {
                      $doc->printSubTitle($catName . " " . $row[10]);                    
                }			                                       
			}
			else if((is_a($doc, "PRINT_ClubCatEntryPage_pdf")) 
				|| (is_a($doc, "PRINT_ClubCatEntryPage")) 
				|| (is_a($doc, "GUI_ClubCatEntryPage")))
			{
				$doc->printSubTitle($row[7] . " " . $catName);
			}
			else if((is_a($doc, "PRINT_ClubCatDiscEntryPage_pdf")) 
				|| (is_a($doc, "PRINT_ClubCatDiscEntryPage")) 
				|| (is_a($doc, "GUI_ClubCatDiscEntryPage")))
			{   
				$doc->printSubTitle($row[7] . " " . $catName . " " . $row[10]);
			}
			// "group by discipline" only, which is rather senseless ...
			else if($_GET['discgroup'] == "yes") {
				$doc->printSubTitle($row[10]);
			}
			
			$l = 0;				// reset line counter
			$k = $row[5];		// keep current category
			$v = $row[7];		// keep current club
			$d = $row[9];		// keep current discipline
			$m = $row[19];      // keep current combined
			$ck = $row[14];
			
		}
		 
		if($l == 0) {					// new page, print header line
			if(!$export and strpos(get_class($doc),"pdf")==false){ printf("<table class='dialog'>\n"); }
			$doc->printHeaderLine();
		}
		
		if($a != $row[0])		// new athlete
		{ 
			$fee=0;
			$nbr = $row[1];
			$name = $row[2] . " " . $row[3];		// assemble name field
			$year = AA_formatYearOfBirth($row[4]);
			$cat = $row[5];
			if(empty($row[8])) {		// not assigned to a team
				$club = $row[7];		// use club name
			}
			else {
				$club = $row[8];		// use team name
			}
			$ioc = $row[13];
            $mkcode=$row[23];
            
		}
		
		if(($row[11] == $cfgDisciplineType[$strDiscTypeTrack])
			|| ($row[11] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
			|| ($row[11] == $cfgDisciplineType[$strDiscTypeRelay])
			|| ($row[11] == $cfgDisciplineType[$strDiscTypeDistance]))
		{
			$perf = AA_formatResultTime($row[12]);
		}
		else {
			$perf = AA_formatResultMeter($row[12]);              
		}
		  
		if((!is_a($doc, "PRINT_CatDiscEntryPage_pdf")) 
			&& (!is_a($doc, "PRINT_CatDiscEntryPage")) 
			&& (!is_a($doc, "GUI_CatDiscEntryPage"))
			&& (!is_a($doc, "PRINT_ClubCatDiscEntryPage_pdf"))
			&& (!is_a($doc, "PRINT_ClubCatDiscEntryPage"))
			&& (!is_a($doc, "GUI_ClubCatDiscEntryPage")))
		{   
		 
		    // 403 = UBS Kids Cup
            // 799 = ...kampf
            // show all disziplines (also if performance is 0) by UBS Kids Cup and ...kampf              
			if( ($perf == 0 && $row[23] != 408 && $row[23] != 799) || ( $row[23] > 0 && $row[23] != 408 && $row[23] != 799 && isset($cfgCombinedDef[$row[23]]) && $perf > 0 )) {            
				//$Info = ($row[17]!="") ? ' ('.$row[17].')' : '';  								
	 		   	$Info = ($row[18]!="") ? ' ('.$row[18].')' : ''; 
				
				$noFee=false;  
				if  ($row[18]!="" && $m != $row[19]) { 
					if ($row[23] > 0 && isset($cfgCombinedDef[$row[23]])){  // normal combined
						$disc = $disc . $sep . $row[19] . $Info;    // add combined 
                        
					}
					else {  // combined with choosed disziplines
						   if ($m != $row[19] && $disc=='') {     
						   	   if ($perf == 0){     
					     			$disc = $row[18] ." ("  . $row[9]  . " / ";    // add combined  
							   }
							   else {     
							        $disc = $row[18] ." ("  . $row[9] .",".$perf . " / ";    // add combined 
							   }
						   }
						   elseif ($m != $row[19]) {     
						   	   			 if ($perf == 0){     
						               	 		$disc = substr($disc,0,-2).") , " . $row[18] ." (" . $row[9]  . " / ";    // add combined 
										 }
										 else {   
										      	$disc = substr($disc,0,-2).") , " . $row[18] ." (" . $row[9] .",".$perf . " / ";    // add combined       
										 }
						   }
						   else {  
						   	     if ($perf == 0){      
						         		$disc = $disc . $sep . $row[18] ." (" . $row[9]  . " / ";    // add combined 
								 }
								 else {  
								      $disc = $disc . $sep . $row[18] ." (" . $row[9] .",".$perf . " / ";    // add combined 
								 }
						   }
					    
					}  
				}
				else {  
					 if ($row[23] > 0 && isset($cfgCombinedDef[$row[23]])){    // normal combined                      
					 	if  ($row[18]!="" && $m == $row[19]) {   
						  	$noFee=true;                        // the same combined
					 		}
					 	else {  
							$disc = $disc . $sep . $row[9] . $Info .",". $perf.")";	// add discipline                               
						}
					 }
					 else { 
					 		if ($row[23] > 0 && (!isset($cfgCombinedDef[$row[23]]))){    // combined with choosed disziplines 					 		   
					 			 if ($perf > 0) {   
					             	$disc = $disc . $row[9] .$sep . $perf ." / ";	// add discipline    
					        	 }
					        	 else {   
					     		 	$disc = $disc . $row[9] ." / ";	// add discipline 
								 } 
							} 
							else {         // not combined
							      	$disc = $disc . $sep . $row[9] . $Info;	// add discipline                                        
							}  	
					 }
				}   
			}else{    
				    $Info = ($row[17]!="") ? $row[17] .', ' : '';  
                    if ($Info == '' && $perf == 0) {                    
                            $disc = $disc . $sep . $row[9];    // add discipline  
                    } 
                    elseif ($perf == 0){                    
                            $Info=substr($Info,0,-2);   
                            $disc = $disc . $sep . $row[9] . " (".$Info .")";    // add discipline  
                    }	
                    else {			    
					    $disc = $disc . $sep . $row[9] . " (".$Info . $perf.")";	// add discipline
                    }
			 
		  	}
			$sep = ", ";
		}
		else
		{
			if($perf == 0){
				$perf = "-";
			}
		}
		 
	  //  if (!$noFee) {
	  //      if ($fee==0) {
		//     $fee+=$row[22];  
	  //       }
	  //       else {
	   //      $fee+=($row[22] - ($reduction/100));  
	  //       }   
	  //  }  
	  
	//	$mehrkampf = ($row[18]!='') ? $row[19] : '';
	//	$mehrkampfInfo = ($mehrkampf!='' && $row[17]!='' && $mehrkampf!=$row[17]) ? ' ('.$row[17].')' : '';   
   //   $disc = ($mehrkampf!='') ? $mehrkampf . $mehrkampfInfo : $disc;   
	 
		// show payment status
		if(isset($_GET['discgroup'])){
			if(isset($_GET['payment'])){
				$paid = strtoupper($row[16]);
			}
		}else{
			if(isset($_GET['payment'])){
				$disc = $disc. " [".strtoupper($row[16])."]";
			}
		}
		
		$l++;			// increment line count
		$a = $row[0];
		$m = $row[19];    // keep combined
		$dd = $row[9];     // keep discipline
	}
	
	// print last athlete, if any
	if($a > 0)
	{    
        
         if ($paymentStatus > 0){
                 if($paymentStatus == 1) {                   
                   if (strpos($disc, '[N]')){                       
                       $paymentPrint = false;                       
                   }  
                   else {
                       $paymentPrint = true;
                   }
                   
                 }
                 elseif ($paymentStatus == 2) {        
                    if (strpos($disc, '[N]') && strpos($disc, '[Y]')){                                           
                        $paymentPrint = true;
                   }  
                   else {
                       $paymentPrint = false;
                   }      
                 }
                 elseif ($paymentStatus == 3) {        
                    if (strpos($disc, '[Y]')){                       
                       $paymentPrint = false;
                   }        
                 }
            }
        
        if (strpos($disc, '[]')){                       
                    $disc = '';
                }
        
        
        if ($paymentPrint) {                                                                   
		    if((is_a($doc, "PRINT_CatEntryPage_pdf"))
				|| (is_a($doc, "PRINT_CatEntryPage"))
			    || (is_a($doc, "GUI_CatEntryPage")))
		    {
			    $doc->printLine($nbr, $name, $year, $club, $disc, $ioc);
		    }
		    else if((is_a($doc, "PRINT_ClubEntryPage_pdf"))
				|| (is_a($doc, "PRINT_ClubEntryPage"))
			    || (is_a($doc, "GUI_ClubEntryPage")))
		    {
			    $doc->printLine($nbr, $name, $year, $cat, $disc, $ioc);
		    }
		    else if((is_a($doc, "PRINT_CatDiscEntryPage_pdf")) 
				|| (is_a($doc, "PRINT_CatDiscEntryPage")) 
			    || (is_a($doc, "GUI_CatDiscEntryPage")))
		    {
			    $doc->printLine($nbr, $name, $year, $club, $perf, $ioc);
		    }
		    else if((is_a($doc, "PRINT_ClubCatEntryPage_pdf")) 
				|| (is_a($doc, "PRINT_ClubCatEntryPage")) 
			    || (is_a($doc, "GUI_ClubCatEntryPage")))
		    {
			    $doc->printLine($nbr, $name, $year, $disc, $ioc);
		    }
		    else if((is_a($doc, "PRINT_ClubCatDiscEntryPage_pdf")) 
				|| (is_a($doc, "PRINT_ClubCatDiscEntryPage")) 
			    || (is_a($doc, "GUI_ClubCatDiscEntryPage")))
		    {
			    $doc->printLine($nbr, $name, $year, $perf, $ioc);
		    }
		    else
		    {  
			    $doc->printLine($nbr, $name, $year, $cat, $club, $disc, $ioc, $paid, $perf ); 
			    
		    }
        }
	}
	
	if(!$export and strpos(get_class($doc),"pdf")==false){ printf("</table>\n"); }
	mysql_free_result($result);
}else if(mysql_num_rows($result) == 0)  // data found
{
	echo $strNoData;
}						// ET DB error

$doc->endPage();		// end HTML page for printing

?>
