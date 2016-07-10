<?php

/**********
 *
 *	print_meeting_entries_payment.php
 *	---------------------------------
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

$i= 0;
$club_clause="";

if($_GET['club'] > 0) {        // club selected
    $club_clause = " AND v.xVerein = " . $_GET['club'];
} 

$print = false;
if($_GET['formaction'] == 'print') {		// page for printing 
	$print = true;
}

if($_GET['event'] > 0) {        // club selected
    $event = $_GET['event'];
} 

if($_GET['comb'] > 0) {        // combined selected
    $comb= $_GET['comb'];
} 

if (isset($_GET['payed'])) {      // athlete payed
    $payed = 'y';
} else {
    $payed = 'n';
}  

if (isset($_GET['payed_club'])) {      // athlete payed
    $payed_club = 'y';
} else {
    $payed_club = 'n';
}      

	if($print == true) {
		$doc = new PRINT_ClubEntryPayedPage_pdf($_COOKIE['meeting']);
	}
	else {
		$doc = new GUI_ClubEntryPayedPage($_COOKIE['meeting']);
	}
    
 
//
// Update change payed per disziplines
//
if($_GET['arg'] == 'change')
{
    mysql_query(" TABLES start as s READ, verein as v READ, anmeldung as a READ,athlet as at READ,wettkampf as w READ, disziplin_de as d READ, disziplin_fr as d READ , disziplin_it as d READ, start WRITE");
        
      
      // check combined      
        $sql = "SELECT 
                        s.xStart,
                        w.Mehrkampfcode 
                    FROM 
                        anmeldung as a
                        LEFT JOIN start as s ON (s.xAnmeldung = a.xAnmeldung)
                        LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf)
                    WHERE
                        a.xAnmeldung = '" .$_GET['item']  . "'
                        AND w.Mehrkampfcode ='" .$_GET['comb']  . "'";
                        
           
             $res = mysql_query($sql);    
              if (mysql_num_rows($res) > 0 && $_GET['mkcode'] != 0){    // if combined set payed for all starts                  
                  
                  while ($row = mysql_fetch_row($res)){
                      $arr_xStart[] = $row[0];
                  }
                  
              }
              else {
                  $arr_xStart[] = $_GET['xStart'];
              }
             
                      
            foreach ($arr_xStart as $key => $val){
                
                $sql = "UPDATE start SET 
                         Bezahlt='$payed'
                        WHERE xStart='" .$val  . "'";
            
               mysql_query($sql);  
                
            }       
           
        if(mysql_errno() > 0)
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
    
    
    mysql_query("UNLOCK TABLES");
}

//
// Update change payed per club
//
if($_GET['arg'] == 'change_club')
{ 
    mysql_query(" TABLES start as s READ, verein as v READ, anmeldung as a READ,athlet as at READ,wettkampf as w READ, disziplin_de as d READ, disziplin_fr as d READ , disziplin_it as d READ, start WRITE");
        

    $sql = "UPDATE 
                start as s    
                LEFT JOIN anmeldung as a ON (a.xAnmeldung = s.xAnmeldung)
                LEFT JOIN athlet as at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN verein as v ON (v.xVerein = at.xVerein)
            SET 
                s.Bezahlt='$payed_club'
            WHERE v.xVerein='" .$_GET['xclub']  . "'";
            
            mysql_query($sql);  
            if(mysql_errno() > 0)        // DB error
            {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
      
      $sql = "UPDATE 
                    start as s   
                    LEFT JOIN staffel as st on (s.xStaffel = st.xStaffel) 
                    LEFT JOIN verein as v ON (v.xVerein = st.xVerein)
              SET 
                    s.Bezahlt='$payed_club'
              WHERE v.xVerein='" .$_GET['xclub']  . "'";
            
            mysql_query($sql);  
            if(mysql_errno() > 0)        // DB error
            {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        
        mysql_query("UNLOCK TABLES");
}
  
//       
//  show payed athletes
//   
    $sql = "SELECT 
                count(*) as anzahl
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
                $club_clause  
           GROUP BY a.xAnmeldung
           ORDER BY
           anzahl DESC";      
    
    $result = mysql_query($sql);    


$max_count = 0;
     
if(mysql_errno() > 0)        // DB error
{
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
  else {
      if (mysql_num_rows($result) > 0) {
          $row = mysql_fetch_row($result);
          $max_count = $row[0]; 
      }
  }  
    
    // check club payed
    $arr_club = array();
    $sql = "SELECT DISTINCT
                    s.Bezahlt,
                    v.xVerein,
                    v.Name
            FROM
                    start as s    
                    LEFT JOIN anmeldung as a ON (a.xAnmeldung = s.xAnmeldung)
                    LEFT JOIN athlet as at ON (at.xAthlet = a.xAthlet)
                    LEFT JOIN wettkampf as w ON (w.xWettkampf = s.xWettkampf) 
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON (d.xDisziplin = w.xDisziplin)
                    LEFT JOIN verein as v ON (v.xVerein = at.xVerein)
            WHERE v.xVerein is NOT NULL AND d.Typ != 3";
            
    $res = mysql_query($sql); 
    while ($row = mysql_fetch_row($res)) {
        $xClub = $row[1];
        $arr_club[$xClub] .= "/" . $row[0];
    }
    //
     // check club payed for relays
    $sql = "SELECT DISTINCT
                    s.Bezahlt,
                    v.xVerein
            FROM
                    start as s    
                     LEFT JOIN staffel as st on (s.xStaffel = st.xStaffel) 
                    LEFT JOIN verein as v ON (v.xVerein = st.xVerein)
            WHERE v.xVerein is NOT NULL";
            
    $res = mysql_query($sql); 
    while ($row = mysql_fetch_row($res)) {
        $xClub = $row[1];
        $arr_club[$xClub] .= "/" . $row[0];
    }
 
  
  mysql_query("DROP TABLE IF EXISTS athletes_tmp");    // temporary table    
                   
 $query_tmp="CREATE TEMPORARY TABLE athletes_tmp SELECT 
                DISTINCT  a.xAnmeldung
                , a.Startnummer
                , at.Name
                , at.Vorname
                , at.Jahrgang
                , k.Kurzname as kKurzname
                , k.Name as kName
                , v.Name as vName
                , t.Name as tName
                , d.Kurzname as dKurzname
                , d.Name as dName
                , d.Typ
                , ck.Kurzname as ckKurzname
                , ck.Name as ckName
                , s.Bezahlt
                , w.Info
                , d2.Kurzname as d2Kurzname
                , d2.Name as d2Name
                , v.Sortierwert
                , w.Mehrkampfcode 
                , s.xStart 
                , w.xWettkampf 
                , v.xVerein  
                , s.xStaffel                  
                , w.Mehrkampfende
                , d.Anzeige as dAnzeige        
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
                $club_clause                
           ORDER BY 
            v.Sortierwert, at.Name, at.Vorname, w.Mehrkampfcode, w.Mehrkampfende, d.Anzeige";      
            
    $res_tmp = mysql_query($query_tmp);   
    
     if(mysql_errno() > 0)        // DB error
    {
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
 }  
else {
    $sql = "SELECT 
                s.xAnmeldung
                , st.Startnummer
                , st.Name               
                , s.xStart 
                , s.xWettkampf 
                , st.xVerein 
                , st.xStaffel 
                , v.Sortierwert
                , v.Name
                , k.Kurzname
                , d.Kurzname
                , s.Bezahlt
            FROM
                start AS s
                LEFT JOIN staffel as st on (s.xStaffel = st.xStaffel) 
                LEFT JOIN wettkampf AS w  ON (s.xWettkampf = w.xWettkampf)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)  
                LEFT JOIN kategorie AS k ON (k.xKategorie = st.xKategorie)                     
                LEFT JOIN verein AS v ON (st.xVerein = v.xVerein)          
            WHERE
                 st.xMeeting = " . $_COOKIE['meeting_id'] ."
                 $club_clause                
                 AND st.xStaffel is not NULL";   
      
      $result = mysql_query($sql);    
     
        if(mysql_errno() > 0)		// DB error
        {
	        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
            
            while ($row = mysql_fetch_row($result)){
                  
                $sql = "INSERT INTO athletes_tmp SET xAnmeldung = $row[0], Startnummer = $row[1], Name = '$row[2]', xStart = $row[3], xWettkampf = $row[4], xVerein = $row[5], xStaffel = $row[6], Sortierwert = '$row[7]' , vName = '$row[8]', kKurzname = '$row[9]', dKurzname = '$row[10]', Bezahlt = '$row[11]'";
                $res = mysql_query($sql);  
                if(mysql_errno() > 0)        // DB error
                {  
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }                 
            }            
        }
        
}     
        
 $sql = "SELECT 
                 xAnmeldung
                , Startnummer
                , Name
                , Vorname
                , Jahrgang
                , kKurzname
                , kName
                , vName
                , tName
                , dKurzname
                , dName
                , Typ
                , ckKurzname
                , ckName
                , Bezahlt
                , Info
                , d2Kurzname
                , d2Name
                , Sortierwert
                , Mehrkampfcode 
                , xStart 
                , xWettkampf 
                , xVerein  
                , xStaffel 
                , Mehrkampfende
                , dAnzeige                     
            FROM
                athletes_tmp             
           ORDER BY 
            Sortierwert, Name, Vorname, Mehrkampfcode, Mehrkampfende, dAnzeige";                                  
            
    $result = mysql_query($sql);          
  
     if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }    
        
 if(mysql_num_rows($result) > 0)  // data found
{
	$a = 0;			// current athlete ID
    $s = 0;         // current relay ID
	$d = "";		// current discipline  
	$l = 0;			// line counter
	$k = "";		// current category
	$v = "";		// current club
	$ck = "";		// current contest category
	$dd = "";       // current discipline 
    $m= "";       // current discipline 
	$paymentPrint = true;
    $i = 0;
    $disc_print = '';
	$start = true;	//dont finish <table> on first line
      
	// full list, sorted by name or start nbr
	while ($row = mysql_fetch_row($result))
	{        
              
		// print previous athlete, if any   
		$pl=false;   
         
	    $relay = AA_checkRelay($row[21]);
        if ($relay && $row[3] != NULL){
            continue;
        }
	   
       if ($row[3] == NULL){
           if($s != $row[23] && $i > 0){
                $pl=true;    
           }
           
       }
       else {
	           if($a != $row[0] && $i > 0){
			           $pl=true;	
	           } 
	    }     
     
		 if ($pl) { 
                
                $count_td =split('<td>',$disc);
                $len = count($count_td);
                               
                $colmax_count = $max_count * 2 - $len;                   
			    $last_pos = strrpos($disc, '<td>');
                $last_pos_next = $last_pos + 4;
                $disc = substr($disc,0, $last_pos) . '<td colspan="' . $colmax_count . '">' . substr($disc,$last_pos_next);
                
                $count_td =split('<td>',$disc_print);
                $len = count($count_td);
               
                $colmax_count = $max_count * 2 - $len;                  
                $last_pos = strrpos($disc_print, '<td>');
                $last_pos_next = $last_pos + 4;
                
                $disc_print = substr($disc_print,0, $last_pos) . '<td colspan="' . $colmax_count . '">' . substr($disc_print,$last_pos_next);
                
                 if ($noDisc){
                    $disc = "<td colspan='" .$max_count*2 ."'></td>";
                      $disc_print = "<td colspan='" .$max_count*2 ."'></td>";
                     $noDisc = false;
                }
                
                if (empty($row[9])){
                    $noDisc = true;
                }
                
                 if($print == true) {
                      if ($flag_subtitle) {
						if (!$start) {
							if (strpos(get_class($doc),"pdf")==false) { printf("</table>"); }
						} else {
							$start = false;
						}
                              $doc->printSubTitle($subtitle); 
                              $flag_subtitle = false;
							if (strpos(get_class($doc),"pdf")==false) { printf("<table class='dialog'>\n");}
								$doc->printHeaderLine($max_count);
                      }
                }
                
                 if($print == true) { 
				    $doc->printLine($nbr, $name, $year, $cat, $disc_print, $len);                     
                 }
                 else {
                     $doc->printLine($nbr, $name, $year, $cat, $disc, $len);   
                 }              
		 
				$nbr = "";
				$name = "";
				$year = "";
				$cat = "";
				$club = "";
				$disc = "";
				$saso = "";
				$sep = "";
				$disc_print = "";
				$paid = "";
				$m = "";
                $mkcode = "";  
		    
		}
	
		if ( $v != $row[7])	{               // next club			 
		  
            if (strpos($arr_club[$row[22]], "n") ){
                $club_checked = '';
            } else {
                
                $club_checked = 'checked="checked"';
            }            
			
		   $form_club = "<form action='print_meeting_entries_payment.php#" . $row[0] ."' method='get' name='change_club_" . $row[22] ."'>";     
           $hidden_club = "<input name='arg' type='hidden' value='change_club' />";
           $hidden_club .= "<input name='item' type='hidden' value='" . $row[0] ."' />";
           $hidden_club .= "<input name='xStart' type='hidden' value='" .$row[20] ."' />";
           $hidden_club .= "<input name='category' type='hidden' value='" .$category ."' />";
           $hidden_club .= "<input name='event' type='hidden' value='" .$row[21] ."' />";          
           $hidden_club .= "<input name='xclub' type='hidden' value='". $row[22]. "' />";
           $hidden_club .= "<input name='club' type='hidden' value='". $_GET['club'] ."' />";
           
           $ckb_club = '<input name="payed_club" value="' . $arr_club[$row[22]] .'"  ' . $club_checked . ' onclick="document.change_club_' .$row[22] .'.submit()" type="checkbox">';
           
           $ckb_club = $form_club . $hidden_club . $ckb_club;         
          
          if($print == true) {
              $flag_subtitle = true;
              $subtitle = $row[7];
          }
          else {
              $doc->printSubTitle($row[7],$ckb_club);
          }
           
			if (strpos(get_class($doc),"pdf")==false) { echo "</form>";	}
						
			$l = 0;				// reset line counter
			$k = $row[5];		// keep current category
			$v = $row[7];		// keep current club
			$d = $row[9];		// keep current discipline			
			$ck = $row[12];
			
		}
		 
		if($l == 0 and !$print) {					// new page, print header line
			if (strpos(get_class($doc),"pdf")==false) { printf("<table class='dialog'>\n");}
			$doc->printHeaderLine($max_count);
		}
		
        if ($row[3] == NULL){
               if($s != $row[23] ){
                 
                $nbr = $row[1];
                $name = $row[2] . " " . $row[3];        // assemble name field
                $year = AA_formatYearOfBirth($row[4]);
                $cat = $row[5];
                if(empty($row[8])) {        // not assigned to a team
                    $club = $row[7];        // use club name
                }
                else {
                    $club = $row[8];        // use team name
                }                
                $mkcode=$row[19];                   
               }               
        }
        else {
		    if($a != $row[0])		// new athlete
		    {                 
                $i++;             
			    
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
                $mkcode=$row[19];
                
		    }
		}
        
          
          if ($row[14]=="y") {
                $payed_checked = 'checked="checked"';
            } else {
                $payed_checked = '';
            }
            
        
		         
           $form_payed = "<form action='print_meeting_entries_payment.php#" . $row[0] ."' method='get' name='change_present_" . $row[20] ."'>";     
		   $hidden = "<input name='arg' type='hidden' value='change' />";
           $hidden .= "<input name='item' type='hidden' value='" . $row[0] ."' />";
           $hidden .= "<input name='xStart' type='hidden' value='" .$row[20] ."' />";
           $hidden .= "<input name='category' type='hidden' value='" .$category ."' />";
           $hidden .= "<input name='event' type='hidden' value='" .$row[21] ."' />";          
           $hidden .= "<input name='comb' type='hidden' value='". $row[19]. "' />";
           $hidden .= "<input name='club' type='hidden' value='". $_GET['club'] ."' />";
           
           $ckb = '<td><input name="payed" value="' . $row[14] .'"  ' . $payed_checked . ' onclick="document.change_present_' .$row[20] .'.submit()" type="checkbox"></td>';
           
           $ckb_print = '<td><input value="' . $row[14] .'"  ' . '  type="checkbox"></td>';
           
           $ckb = $form_payed . $hidden . $ckb ."</form>";           
		    
            // show all disziplines 
              
            $noFee=false;  
            if  ($row[16]!="" && $m != $row[17]) { 
                    if ($row[19] > 0 && isset($cfgCombinedDef[$row[19]])){  // normal combined
                        $disc = $disc . $ckb . '<td>'  . $row[17] . '</td>';    // add combined 
                        $disc_print = $disc_print . $ckb_print . '<td>'  . $row[17] . '</td>';                           
                    }
            }
            else {  
                     if ($row[19] > 0 && isset($cfgCombinedDef[$row[19]])){    // normal combined     
                                 $noFee=true;                        // the same combined                                
                         } 
                    
                     else { 
                             if ($row[19] == 0 ){         
                                   $disc = $disc . $ckb . '<td>' . $row[9] . '</td>'; ;    // add discipline  
                                   $disc_print = $disc_print . $ckb_print  . '<td>'  . $row[9] . '</td>';                           
                            } 
                     }
            }   
		
		$l++;			// increment line count
		$a = $row[0];
        $s = $row[23];
		$m = $row[17];    // keep combined
		$dd = $row[9];     // keep discipline
        $i++;
	}
	
	// print last athlete, if any
	if($i > 0)
	{                   
        if ($noDisc){
                    $disc = "<td colspan='" .$max_count ."'></td>";
                     $noDisc = false;
                }
                
                if (empty($row[9])){
                    $noDisc = true;
                }                
       
		  if($print == true) { 
                    $doc->printLine($nbr, $name, $year, $cat, $disc_print, $len);   
          }
          else {
                     $doc->printLine($nbr, $name, $year, $cat, $disc, $len);   
          }              
        
	}
	if (strpos(get_class($doc),"pdf")==false) { printf("</table>");}
	
	mysql_free_result($result);
}else 
if(mysql_num_rows($result) == 0)  // data found
{
	echo $strNoData;
}						// ET DB error
if (strpos(get_class($doc),"pdf")==false) { echo "</div>";}

 mysql_query("DROP TABLE IF EXISTS athletes_tmp");    // temporary table    

$doc->endPage();		// end HTML page for printing

?>
