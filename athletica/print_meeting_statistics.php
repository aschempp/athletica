<?php

/**********
 *
 *    print_meeting_statistics.php
 *    ----------------------------
 *    
 */

require('./lib/common.lib.php');
require('./lib/cl_print_page.lib.php');
require('./lib/cl_print_page_pdf.lib.php');
require('./lib/results.lib.php');
      
if(AA_connectToDB() == FALSE)    {                // invalid DB connection
    return;        // abort
}

if(AA_checkMeetingID() == FALSE) {        // no meeting selected
    return;        // abort
}

if($_GET['arg'] == 'print') {    // page for printing
    $doc = new PRINT_Statistics_pdf($_COOKIE['meeting']);
    $doc->printPageTitle($strStatistics . " " . $_COOKIE['meeting']);
}
else {
    $doc = new GUI_Statistics("Statistics");
}

//
//    Statistic 1: Entry overview
// ---------------------------

$doc->printSubTitle($strEntries);
$doc->startList();
$doc->printHeaderLine($strCategory, $strAthletes, $strRelays);

// read all entries
$result = mysql_query("
    SELECT
        k.xKategorie
        , k.Name
        , IF(a.xKategorie IS NULL,0,COUNT(*))
    FROM
        kategorie AS k
    LEFT JOIN anmeldung AS a
        ON a.xMeeting = " . $_COOKIE['meeting_id'] . "
    WHERE k.xKategorie = a.xKategorie
    GROUP BY
        a.xKategorie
    ORDER BY
        k.Anzeige
");
   
if(mysql_errno() > 0)        // DB error
{
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
}
else
{   
    $te = 0;        // totel entries
    $tr = 0;        // totel relays
    while ($row = mysql_fetch_row($result))
    {
        // get nbr of relays for this category
        $rel = 0;
        $res = mysql_query("
            SELECT
                COUNT(*)
            FROM
                staffel AS s
            WHERE s.xMeeting = " . $_COOKIE['meeting_id'] . "
            AND s.xKategorie = $row[0]
        ");

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            $relay_row = mysql_fetch_row($res);
            $rel = $relay_row[0];        // save nbr of relays
            mysql_free_result($res);
        }

        // print data
        $te = $te + $row[2];        // add entries
        $tr = $tr + $rel;            // add relays
        $doc->printLine($row[1], $row[2], $rel);
    }
    mysql_free_result($result);
}
// add total
$doc->printTotalLine($strTotal, $te, $tr);
$doc->endList();

  
//
//    Statistic 2: Entries per discipline
// -----------------------------------
 
$doc->printSubTitle($strStartsPerDisc);
$doc->startList();
$doc->printHeaderLine($strCategory, $strDiscipline, $strEntries, $strStarted);

                     
 mysql_query("DROP TABLE IF EXISTS result_tmp");    // temporary table    
                      
 $query_tmp="CREATE TEMPORARY TABLE result_tmp SELECT  
                                            MIN(r.Startzeit) AS Startzeit, 
                                            r.xWettkampf, 
                                            r.Status 
                                      FROM 
                                            runde AS r 
                                            LEFT JOIN wettkampf AS w USING (xWettkampf)
                                      WHERE w.xMeeting = " . $_COOKIE['meeting_id'] . "       
                                      GROUP BY r.xWettkampf                                 
                                       ";      
                                       
 $res_tmp = mysql_query($query_tmp);     
 
 if(mysql_errno() > 0)        // DB error
    {
    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
 }
 else  
     {      // read all events without timetable
       
      $sql = "SELECT 
                    r.Startzeit        
                    , w.xWettkampf  
                    , r.Status                                  
              FROM
                      disziplin_" . $_COOKIE['language'] . " AS d
                    LEFT JOIN wettkampf AS w ON (d.xDisziplin= w.xDisziplin )
                    LEFT JOIN kategorie AS wk ON (wk.xKategorie = w.xKategorie)     
                    LEFT JOIN start AS s ON (w.xWettkampf = s.xWettkampf)                                        
                    LEFT JOIN anmeldung AS an ON (s.xAnmeldung = an.xAnmeldung)
                    LEFT JOIN staffel AS st ON (s.xStaffel = st.xStaffel)
                    LEFT JOIN kategorie AS k ON ( k.xKategorie = 
                        IF(an.xKategorie > 0, an.xKategorie, st.xKategorie))
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." as dd ON (w.Info = dd.Kurzname)    
                    LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf)
                     LEFT JOIN athlet AS at ON (an.xAthlet = at.xAthlet) 
              WHERE w.xMeeting = " . $_COOKIE['meeting_id'] . "                    
                    AND r.Status IS NULL
                     AND ((d.Staffellaeufer = 0
                                    AND s.xAnmeldung > 0)
                                        OR (d.Staffellaeufer > 0
                                            AND s.xStaffel > 0))   
                    
              GROUP BY
                       s.xWettkampf";    
 
         $result = mysql_query($sql);

        if(mysql_errno() > 0)        // DB error
            {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {   
            while ($row = mysql_fetch_row($result) ){
                
                $sql="INSERT INTO result_tmp SET " 
                        . " xWettkampf = " . $row[1];   
             
                 $res = mysql_query($sql); 
                    
                if(mysql_errno() > 0) {        // DB error
                   AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }  
             }         
              
            mysql_query("DROP TABLE IF EXISTS result_tmp3");    // temporary table    
            
            // read all events (incl. relays) without combined event and save in temporary table 
            //              
            //
            $query_tmp3="CREATE TEMPORARY TABLE result_tmp3 SELECT DISTINCT
                            k.Name as kName
                            , d.Name as dName
                            , s.xWettkampf                             
                            , s.Anwesend
                            , IF(w.Mehrkampfcode > 0, dd.Name,w.Info) as DiszInfo
                            , wk.Name   
                            , IF(s.xAnmeldung > 0, an.xKategorie, st.xKategorie) AS Cat
                            , w.Mehrkampfcode
                            , r.Status                               
                            , an.xAnmeldung 
                            , k.Anzeige As kAnzeige
                            , d.Anzeige As dAnzeige 
                            , k.Kurzname
                            , wk.Anzeige As wkAnzeige 
                            , w.Typ                            
                    FROM
                            disziplin_" . $_COOKIE['language'] . " AS d
                            LEFT JOIN wettkampf AS w ON (d.xDisziplin= w.xDisziplin )
                            LEFT JOIN kategorie AS wk ON (wk.xKategorie = w.xKategorie)     
                            LEFT JOIN start AS s ON w.xWettkampf = s.xWettkampf
                                
                            LEFT JOIN anmeldung AS an ON (s.xAnmeldung = an.xAnmeldung)
                            LEFT JOIN staffel AS st ON (s.xStaffel = st.xStaffel)
                            LEFT JOIN kategorie AS k ON ( k.xKategorie = 
                                IF(an.xKategorie > 0, an.xKategorie, st.xKategorie))
                            LEFT JOIN disziplin_" . $_COOKIE['language'] ." as dd ON (w.Info = dd.Kurzname)    
                            LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf)
                            LEFT JOIN athlet AS at ON (an.xAthlet = at.xAthlet) 
                            LEFT JOIN result_tmp as t ON (s.xWettkampf = t.xWettkampf) 
                    WHERE 
                            w.xMeeting = " . $_COOKIE['meeting_id'] . "                                
                            AND ( t.Startzeit is Null Or t.Startzeit= r.Startzeit)  
                            AND w.mehrkampfcode = 0
                            AND ((d.Staffellaeufer = 0
                                    AND s.xAnmeldung > 0)
                                            OR (d.Staffellaeufer > 0
                                             AND s.xStaffel > 0))                    
                    ORDER BY
                              k.Anzeige
                            , k.Kurzname DESC
                            , w.Typ
                            , w.Mehrkampfcode 
                            , wk.Anzeige
                            , w.Mehrkampfende ASC          
                            , if (w.Mehrkampfcode>0,r.Startzeit,w.Mehrkampfende) 
                            , d.Anzeige
                            ,r.Datum
                             ";      
           
            $res_tmp3 = mysql_query($query_tmp3); 
             if(mysql_errno() > 0) {        // DB error
                   AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }  
                               
            mysql_query("DROP TABLE IF EXISTS result_tmp2");    // temporary table    
            
            $query_tmp2="CREATE TEMPORARY TABLE result_tmp2 SELECT 
                            t.kName
                            , t.dName
                            , IF(t.xWettkampf IS NULL,0,COUNT(*)) as enrolment 
                            , SUM(t.Anwesend) as present                             
                            , t.Anwesend
                            , t.DiszInfo
                            , t.Name   
                            , t.Cat
                            , t.Mehrkampfcode
                            , t.Status                               
                            , t.xAnmeldung 
                            , t.kAnzeige
                            , t.dAnzeige 
                            , t.Kurzname
                            , t.wkAnzeige 
                            , t.Typ         
                    FROM                           
                            result_tmp3 as t 
                    
                    GROUP BY
                            Cat, t.xWettkampf
                    ORDER BY
                              t.kAnzeige
                            , t.Kurzname DESC
                            , t.Typ
                            , t.Mehrkampfcode 
                            , t.wkAnzeige
                             ";      
               
              $res_tmp2 = mysql_query($query_tmp2); 
                                 
              if(mysql_errno() > 0)        // DB error
                {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }  
                
                
            // read all combined events and save in temporary table 
            $sql_mk=" SELECT DISTINCT k.Name ,   
                            s.Anwesend , 
                            dd.Name as DiszInfo , 
                            wk.Name ,                            
                            an.xKategorie AS Cat ,                              
                            w.Mehrkampfcode , 
                            r.Status ,                            
                            an.xAnmeldung 
                            ,k.Anzeige
                            ,d.Anzeige
                            ,k.Kurzname
                            , wk.Anzeige
                            , w.Typ
                     FROM 
                            disziplin_" . $_COOKIE['language'] . " AS d  
                            LEFT JOIN wettkampf AS w ON (d.xDisziplin= w.xDisziplin )
                            LEFT JOIN kategorie AS wk ON (wk.xKategorie = w.xKategorie)     
                            LEFT JOIN start AS s ON (w.xWettkampf = s.xWettkampf) 
                            LEFT JOIN anmeldung AS an ON (s.xAnmeldung = an.xAnmeldung)                            
                            LEFT JOIN kategorie AS k ON ( k.xKategorie = an.xKategorie) 
                            LEFT JOIN disziplin_" . $_COOKIE['language'] ." as dd ON (w.Info = dd.Kurzname) 
                            LEFT JOIN runde AS r ON (r.xWettkampf = w.xWettkampf) 
                            LEFT JOIN athlet AS at ON (an.xAthlet = at.xAthlet) 
                            LEFT JOIN result_tmp as t ON (s.xWettkampf = t.xWettkampf) 
                     WHERE  
                            w.xMeeting = " . $_COOKIE['meeting_id'] . "    
                           
                            AND ( t.Startzeit is Null Or t.Startzeit= r.Startzeit) 
                                AND w.mehrkampfcode > 0
                     ORDER BY k.Anzeige ,k.Kurzname Desc, wk.Anzeige , an.xAnmeldung,  w.Mehrkampfcode 
                            ";
                                                                                                         
            $res_mk = mysql_query($sql_mk);  
             if(mysql_errno() > 0) {        // DB error
                   AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }  
          
            $cEnrol=0; 
            $cPresent=0;  
            $statusStarted=0; 
            $first=true;  
            $keep0='';
            $keep3='';   
            $keep7='';  
              
            if(mysql_num_rows($res_mk) > 0){
                    while($row = mysql_fetch_array($res_mk)){                         
                      
                        if ($row[0]!=$keep0 OR $row[0]==NULL) { 
                               if (!$first){
                                    $cPresent+=$keep1;  
                                    if ($statusStarted ==  $cfgRoundStatus['results_done'] ||
                                        $statusStarted ==  $cfgRoundStatus['results_in_progress'] ||  
                                        $statusStarted ==  $cfgRoundStatus['results_sent'] )
                                    {
                                       $status=$statusStarted;  
                                    }
                                    else {
                                          $status=$keep6; 
                                    }                       
                                   
                                    $sql_mehrkampf="INSERT INTO result_tmp2 SET  
                                                            kName = \"".$keep0. "\"   
                                                            , dName = ''
                                                            , enrolment = '$cEnrol'    
                                                            , present = '$cPresent' 
                                                            , DiszInfo = '$keep2'   
                                                            , Name = \"".$keep3. "\"    
                                                            , Cat = '$keep4'   
                                                            , Mehrkampfcode = '$keep5'    
                                                            , Status = '$status'  
                                                            , xAnmeldung = '$keep7'   
                                                            , kAnzeige = '$keep8' 
                                                            , dAnzeige = '$keep9' 
                                                            , Kurzname = '$keep10'
                                                            , wkAnzeige = '$keep11' 
                                                            , Typ = '$keep12'   
                                                            ";     
                                    
                                    $res_mehrkampf = mysql_query($sql_mehrkampf); 
                                    if(mysql_errno() > 0) {        // DB error
                                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                    }  
                                    
                                    $cEnrol=1; 
                                    $cPresent=0; 
                                    $statusStarted=0;
                                    }
                                    else {if ($row[0]==NULL) {
                                                $cEnrol=0;
                                         }
                                         else {
                                                $cEnrol=1;
                                         } 
                                    }
                        }
                        else {   
                            if ($row[3] != $keep3){  
                                 if ($statusStarted ==  $cfgRoundStatus['results_done'] ||
                                        $statusStarted ==  $cfgRoundStatus['results_in_progress'] ||  
                                        $statusStarted ==  $cfgRoundStatus['results_sent'] ) 
                                        {
                                       $status=$statusStarted;  
                                    }
                                    else {
                                          $status=$keep6; 
                                    }
                                    $cPresent+=$keep1; 
                                     
                                    $sql_mehrkampf="INSERT INTO result_tmp2 SET  
                                                            kName = \"".$keep0. "\"   
                                                            , dName = ''
                                                            , enrolment = '$cEnrol' 
                                                            , present = '$cPresent' 
                                                            , DiszInfo = '$keep2'   
                                                            , Name = \"".$keep3. "\"   
                                                            , Cat = '$keep4'   
                                                            , Mehrkampfcode = '$keep5'    
                                                            , Status = '$status' 
                                                            , xAnmeldung = '$keep7'   
                                                            , kAnzeige = '$keep8' 
                                                            , dAnzeige = '$keep9' 
                                                            , Kurzname = '$keep10'
                                                            , wkAnzeige = '$keep11' 
                                                            , Typ = '$keep12'   
                                                            ";     
                                     
                                    $res_mehrkampf = mysql_query($sql_mehrkampf); 
                                    if(mysql_errno() > 0) {        // DB error
                                         AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                    }  
                                  
                                    $cEnrol=1;  
                                    $cPresent=0; 
                                    $statusStarted=0;     
                            }    
                            else {  
                                if ($row[7]!=$keep7) {  
                                    $cEnrol++;  
                                    $cPresent+=$keep1;  
                                }
                                else { 
                                    if ($keep6 ==  $cfgRoundStatus['results_done'] ||
                                        $keep6 ==  $cfgRoundStatus['results_in_progress'] ||  
                                        $keep6 ==  $cfgRoundStatus['results_sent'] )
                                        {
                                        $statusStarted=$keep6;  
                                  } 
                                }
                             
                            } 
                        }  
                        $first=false;
                        $keep0=$row[0];
                        $keep1=$row[1]; 
                        $keep2=$row[2];
                        $keep3=$row[3]; 
                        $keep4=$row[4]; 
                        $keep5=$row[5];
                        $keep6=$row[6];  
                        $keep7=$row[7]; 
                        $keep8=$row[8];   
                        $keep9=$row[9]; 
                        $keep10=$row[10]; 
                        $keep11=$row[11];    
                        $keep12=$row[12];     
                    } 
                    
                    // add last combined event
                    $cPresent+=$keep1;  
                    if ($statusStarted ==  $cfgRoundStatus['results_done'] ||
                           $statusStarted ==  $cfgRoundStatus['results_in_progress'] ||  
                           $statusStarted ==  $cfgRoundStatus['results_sent'] ) 
                           {
                           $status=$statusStarted;  
                    }
                    else {
                            $status=$keep6; 
                    }  
                                                 
                    $sql_mehrkampf="INSERT INTO result_tmp2 SET  
                                             kName = \"".$keep0. "\"   
                                             , dName = ''
                                             , enrolment = '$cEnrol'  
                                             , present = '$cPresent' 
                                             , DiszInfo = '$keep2'   
                                             , Name = \"".$keep3. "\" 
                                             , Cat = '$keep4'   
                                             , Mehrkampfcode = '$keep5'    
                                             , Status = '$status'  
                                             , xAnmeldung = '$keep7'   
                                             , kAnzeige = '$keep8' 
                                             , dAnzeige = '$keep9' 
                                             , Kurzname = '$keep10'
                                             , wkAnzeige = '$keep11' 
                                             , Typ = '$keep12'   
                                             ";     
                                      
                    $res_mehrkampf = mysql_query($sql_mehrkampf); 
                    if(mysql_errno() > 0) {        // DB error
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }  
            }  
            
               // read all events   
         
            $sql = "SELECT
                            t.kName
                            , t.dName
                            , t.enrolment  
                            , t.present
                            , t.DiszInfo
                            , t.Name   
                            , t.Cat
                            , t.Mehrkampfcode
                            , t.Status                
                    FROM
                            result_tmp2 AS t
                    ORDER BY t.kAnzeige, t.Kurzname DESC, t.Typ, t.wkAnzeige, t.Mehrkampfcode, t.dAnzeige";   
           
             $result = mysql_query($sql);
            
            if(mysql_errno() > 0)        // DB error
                {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }               
            else if(mysql_num_rows($result) > 0)  // data found
                    {
                    $e = 0;        // total entries per category
                    $s = 0;        // total startet per category
                    $te = 0;    // total entries overall
                    $ts = 0;    // total started
                    $catName = '';
                    $cat = '';  
                    $wkCat = '';
                    $mkCode = 0;    // combined code
                    $rowclass='odd';
                    $stats = array();
                    $clubs = array();    
    
                    while ($row = mysql_fetch_row($result))
                        {   
                        
                        if ($row[8]==$cfgRoundStatus['open']                           
                                || $row[8]== $cfgRoundStatus['enrolement_pending']
                                || $row[8]== $cfgRoundStatus['enrolement_done']
                                || $row[8]== $cfgRoundStatus['heats_in_progress']  
                                || $row[8]== $cfgRoundStatus['heats_done'] )
                                {                     
                                $row2 = 0;                  // no started athletes when enrolement open or pending
        
                        }else {
                                $row2 = $row[2] - $row[3];    // calculating started athletes:
                                                            // registrations - athletes with s.Anwesend = 1 (didn't show up at apell) 
                         }
        
                        $Info = ($row[4]!="") ? ' ('.$row[4].')': '';
                        $disc = $row[1] ." ". $row[5] . $Info;
                        $disc = ($row[7]>0) ? $row[4] . " " . $row[5] : $disc;
            
                        // add category total
                        if($catName != $row[0]) {
                            if($catName != '') {
                                $te = $te + $e;        // calculate entries grand total
                                $ts += $s;
                                $doc->printTotalLine($strTotal, '', $e, $s);
                                $e=0;
                                $s=0;
                                $stats = array(); 
                            }
                            $catName = $row[0]; 
                            $cat=$row[6];  
                                            
                            $doc->printLine($row[0], $disc, $row[2], $row2);    // line with category
                        }
                        else {      
                            
                            $doc->printLine('', $disc, $row[2], $row2);    // line without category
                           }
                        $e = $e + $row[2];                    // add entries
                        $s += $row2;
                        $catName=$row[0];                   // keep categorie
                           $cat=$row[6];                       // keep categorie 
                            
                    }       // end while
    
                    // add last category total
                    if($cat != '') {
                        $te = $te + $e;        // calculate entries grand total
                        $ts += $s;
                        $doc->printTotalLine($strTotal, '', $e, $s);
                        $doc->printTotalLine($strTotal." ".$strMeeting, '', $te, $ts);
                    }
                    mysql_free_result($result);
                    }
             }
    }
    $doc->endList();
    
 
    //                          
    //    Statistic 3: Fees and deposits 
    // ------------------------------
    $doc->printSubTitle($strFee." / ".$strDeposit);
    $doc->startList();   
    $doc->printHeaderLine($strClub, $strFee, $strDeposit, $strEntries, $strStarted, $strAssTax);

    // read all starts per club and add fee and deposit    
   
    mysql_query("DROP TABLE IF EXISTS result_tmp1");    // temporary table     
                               
    mysql_query("CREATE TEMPORARY TABLE result_tmp1(              
                              clubnr int(11)
                              , club varchar(30)
                              , ReductionAmount int(10) 
                              , Name varchar(25)
                              , Vorname varchar(25)
                              , Startzeit time
                              , started int(11) 
                              , anwesend char(5)
                              , Haftgeld float (11)
                              , Startgeld float (11)  
                              , enrolement int(11) 
                              , mehrkampfcode int(11) 
                              , Status int(11) 
                              , StartgeldReduktion float (11)
                              , Sortierwert varchar(30) 
                              , kAnzeige int(11) 
                              , kKurzname varchar(4) 
                              , kAlterslimite tinyint(4)    
                              )
                              ENGINE=HEAP");  
  
      // calculate started athlets only for combined event and write the 
      //         earliest one per athlete and per combined event in a temporary table  
              
    $sql="SELECT athlet.xVerein AS clubnr , 
                   v.Name AS club , 
                 StartgeldReduktion/100 as ReductionAmount,                  
                 athlet.Name ,
                 athlet.Vorname , 
                   t.Startzeit ,  
                   s.Anwesend , 
                    s.Anwesend , 
                    wettkampf.Haftgeld, 
                    wettkampf.Startgeld,                 
                 wettkampf.Mehrkampfcode,
                 r.status  ,  
                 StartgeldReduktion,   
                 wettkampf.xKategorie,
                 v.Sortierwert,
                 k.kurzname,
                 k.Anzeige,
                 k.Alterslimite,
                 anmeldung.xKategorie    
             FROM 
                     athlet 
                   INNER JOIN anmeldung ON (athlet.xAthlet = anmeldung.xAthlet) 
                   INNER JOIN start As s ON (anmeldung.xAnmeldung = s.xAnmeldung) 
                   INNER JOIN wettkampf ON (s.xWettkampf = wettkampf.xWettkampf) 
                   INNER JOIN meeting ON (wettkampf.xMeeting = meeting.xMeeting) 
                   LEFT JOIN verein AS v ON (athlet.xVerein=v.xVerein) 
                   LEFT JOIN runde AS r ON (r.xWettkampf = s.xWettkampf) 
                   LEFT JOIN result_tmp as t ON (s.xWettkampf = t.xWettkampf)
                LEFT JOIN kategorie as k ON (k.xKategorie =  anmeldung.xKategorie) 
             WHERE ((wettkampf.Mehrkampfcode >0 )) 
                AND anmeldung.xMeeting =  " . $_COOKIE['meeting_id'] . " 
                 AND (t.Startzeit is Null Or t.Startzeit= r.Startzeit)    
             ORDER BY athlet.xVerein, athlet.Name ,athlet.Vorname, 
                     wettkampf.mehrkampfcode,wettkampf.xKategorie,r.Startzeit ,r.Status"; 
   
    $res = mysql_query($sql);  
    
     if(mysql_errno() > 0)        // DB error
                {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }                 
   
    $club=''; 
    $deposit=0;
    $entries=0;
    $fee=0;   
    
   
    while($row = mysql_fetch_array($res)){  
        $starts=0; 
        $enrolment=1;
        if ($club==''){  
             if ($row['status']==$cfgRoundStatus['results_done'] 
                               || $row['status']== $cfgRoundStatus['results_in_progress'] 
                              || $row['status']== $cfgRoundStatus['results_sent'] )
                {  
                if ($row['Anwesend']==0){ 
                    $starts=1; 
                }
             }
             $startTime = $row[5]; 
             if (empty($row[5])){
                 $startTime = '00:00:00';
             }
              $status = $row[11]; 
              if (empty($row[11])){
                 $status = 0;
             }
            
             $sql_mk="INSERT INTO result_tmp1 SET  
                                    clubnr = $row[0]
                                  , club = \"" .$row[1]. "\"
                                  , ReductionAmount = '$row[2]'   
                                  ,    Name =\"" .$row[3]. "\"
                                  ,    Vorname =\"" .$row[4]. "\"  
                                  ,    Startzeit = '$startTime' 
                                  ,    started = $starts   
                                  ,    anwesend = $row[7]
                                  ,    Haftgeld = '$row[8]'  
                                  ,    Startgeld = '$row[9]' 
                                  ,    enrolement = $enrolment
                                  ,    Mehrkampfcode = '$row[10]' 
                                  ,    Status = '$status' 
                                  ,    StartgeldReduktion = '$row[12]'
                                  ,    Sortierwert = \"" .$row[14]. "\"                                    
                                  , kKurzname = \"" .$row[15]. "\"  
                                  , kAnzeige = $row[16]    
                                  , kAlterslimite = $row[17]    
                                   ";     
            
             $res_mk = mysql_query($sql_mk);    
             
             if(mysql_errno() > 0)        // DB error
                 { 
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
             }         
             $entries+=1; 
             $fee+=$row['Startgeld']; 
             $deposit+=$row['Haftgeld'];  
        }         
        else {  
              if ($club!=$row['clubnr']){ 
                  if ($row['status']==$cfgRoundStatus['results_done'] 
                               || $row['status']== $cfgRoundStatus['results_in_progress'] 
                              || $row['status']== $cfgRoundStatus['results_sent'] )
                            { 
                            if ($row['Anwesend']==0){ 
                                $starts=1; 
                            }
                   }
                     $sql_mk="INSERT INTO result_tmp1 SET  
                                    clubnr = $row[0]
                                  , club = \"" .$row[1]. "\"
                                  , ReductionAmount = '$row[2]'   
                                  ,    Name =\"" .$row[3]. "\"
                                  ,    Vorname =\"" .$row[4]. "\"  
                                  ,    Startzeit = '$row[5]' 
                                  ,    started = $starts     
                                  ,    anwesend = $row[7]   
                                  ,    Haftgeld = '$row[8]'  
                                  ,    Startgeld = '$row[9]' 
                                  ,    enrolement = $enrolment  
                                  ,    Mehrkampfcode = '$row[10]' 
                                  ,    Status = '$row[11]' 
                                  ,    StartgeldReduktion = '$row[12]' 
                                  ,    Sortierwert = \"" .$row[14]. "\"
                                  , kKurzname = \"" .$row[15]. "\"  
                                  , kAnzeige = $row[16]    
                                  , kAlterslimite = $row[17]   
                                    ";      
                      
                   $res_mk = mysql_query($sql_mk); 
                   if(mysql_errno() > 0) {        // DB error
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                   }     
                    
                  $starts=0;
                  $entries=1; 
                  $fee=$row['Startgeld'];  
                  $deposit=$row['Haftgeld'];   
              }
              else {
                         if ($name!=$row['Name'] || $firstName!=$row['Vorname'] || $mehrkampfCode!=$row['Mehrkampfcode']) {    
                               if ($row['status']==$cfgRoundStatus['results_done'] 
                                       || $row['status']== $cfgRoundStatus['results_in_progress'] 
                                      || $row['status']== $cfgRoundStatus['results_sent'] )
                                    {    
                                    if ($row['Anwesend']==0){ 
                                        $starts=1; 
                                    }
                           } 
                               $sql_mk="INSERT INTO result_tmp1 SET  
                                                clubnr = $row[0]
                                              , club = \"" .$row[1]. "\"
                                               , ReductionAmount = '$row[2]'   
                                              , Name =\"" .$row[3]. "\"
                                              , Vorname =\"" .$row[4]. "\"  
                                              , Startzeit = '$row[5]' 
                                             , started = $starts   
                                              , anwesend = $row[7]   
                                              , Haftgeld = '$row[8]'  
                                              , Startgeld = '$row[9]' 
                                                , enrolement = $enrolment  
                                                , Mehrkampfcode = '$row[10]' 
                                                , Status = '$row[11]' 
                                                , StartgeldReduktion = '$row[12]' 
                                                  , Sortierwert = \"" .$row[14]. "\"
                                            , kKurzname = \"" .$row[15]. "\"  
                                            , kAnzeige = $row[16]    
                                            , kAlterslimite = $row[17]   
                                              ";     
                                  
                           $res_mk = mysql_query($sql_mk);    
                           if(mysql_errno() > 0) {        // DB error
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                           }  
                           
                           $entries+=1;
                           $fee+=$row['Startgeld'];  
                           $deposit+=$row['Haftgeld'];  
                    }
                    else {
                           if ($event_cat!=$row['xKategorie']){
                                  $entries+=1; 
                                  $fee+=$row['Startgeld']; 
                                  $deposit+=$row['Haftgeld'];  
                                 
                               if ($row['status']==$cfgRoundStatus['results_done'] 
                                              || $row['status']== $cfgRoundStatus['results_in_progress'] 
                                          || $row['status']== $cfgRoundStatus['results_sent'] )
                                    { 
                                    
                                       if ($row['Anwesend']==0){
                                        $starts+=1;
                                        $deposit-=$row['Haftgeld']; 
                                    } 
                                  }  
                            }   
                    }
              }
        }     
        
        $club = $row['clubnr'];  
        $name = $row['Name']; 
        $firstName = $row['Vorname']; 
        $mehrkampfCode=$row['Mehrkampfcode']; 
        $status = $row['status'];  
        $anwesend = $row['Anwesend'];  
        $event_cat = $row['xKategorie']; 
        
    }  // end while      
                       
       
        $tf = 0;
        $td = 0;
        $te = 0;
        $ts = 0;
        $i = 0;  
        $club = 0;  
        $reduction = 0;
        $starts = 0;
        $fee = 0;
        $deposit = 0;
        $entries = 0; 
        $kKurzname = '';
        $katStarts = 0; 
       
        // calculate started athlets for single events  
        //            and write them into the same temporary table                                                                          
        $sql="SELECT
                    athlet.xVerein AS clubnr
                    , v.Name AS club
                    , (count(s.xWettkampf)-1) * (StartgeldReduktion/100) as ReductionAmount                                                                                        
                    , athlet.Name
                    , athlet.Vorname
                    , t.Startzeit
                    , SUM(if ((r.Status=4 OR r.Status=3) AND s.Anwesend=0,1,0)) as started 
                    , SUM(s.Anwesend) as anwesend
                    , SUM(if ((r.Status=4 OR r.Status=3) AND s.Anwesend=0,0,wettkampf.Haftgeld) )  AS Haftgeld   
                    , SUM(wettkampf.Startgeld) AS Startgeld
                    , count(s.xWettkampf) as enrolement
                    , wettkampf.mehrkampfcode
                    , r.status
                    , StartgeldReduktion
                    , v.Sortierwert
                    , wettkampf.xKategorie
                    , k.kurzname
                    , k.Anzeige
                    , k.Alterslimite
                    , anmeldung.xKategorie     
              FROM           
                    athlet         
                    INNER JOIN anmeldung ON (athlet.xAthlet = anmeldung.xAthlet)
                    INNER JOIN start As s ON (anmeldung.xAnmeldung = s.xAnmeldung) 
                    INNER JOIN wettkampf ON (s.xWettkampf = wettkampf.xWettkampf)
                    INNER JOIN disziplin_" . $_COOKIE['language'] . " as d on (d.xDisziplin = wettkampf.xDisziplin) 
                    INNER JOIN meeting ON (wettkampf.xMeeting = meeting.xMeeting) 
                    LEFT JOIN verein AS v ON (athlet.xVerein=v.xVerein)
                    LEFT JOIN runde AS r ON (r.xWettkampf = s.xWettkampf)   
                    LEFT JOIN result_tmp as t ON (s.xWettkampf = t.xWettkampf) 
                    LEFT JOIN kategorie as k ON (k.xKategorie = anmeldung.xKategorie)           
              WHERE ((wettkampf.Mehrkampfcode =0  ))   
                        AND anmeldung.xMeeting =  " . $_COOKIE['meeting_id'] . " 
                         AND (t.Startzeit is Null OR t.Startzeit= r.Startzeit)   
                         AND d.Staffellaeufer = 0  
              GROUP BY athlet.xVerein, athlet.xAthlet
              ORDER BY v.Sortierwert"; 
             
              $res = mysql_query($sql); 
              if(mysql_errno() > 0) {        // DB error
                   AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
              }  
                          
              while($row = mysql_fetch_array($res)){   
                       $startTime = $row[5];       
                       if (empty($row[5])){
                            $startTime = '00:00:00';
                       }
                       $statusSt = $row[12];
                       if  (empty($row[12])){
                            $statusSt = 0;
                       }
                       
                       $sql_t1="INSERT INTO result_tmp1 SET  
                                    clubnr = $row[0]
                                  , club = \"" .str_replace("\"","'",$row[1]) . "\"   
                                  , ReductionAmount  = '$row[2]' 
                                  ,    Name =\"" .str_replace("\"","'",$row[3]) . "\"
                                  ,    Vorname =\"" .str_replace("\"","'",$row[4]) . "\"  
                                  ,    Startzeit = '$startTime' 
                                  ,    started = '$row[6]'   
                                  ,    anwesend = '$row[7]' 
                                  ,    Haftgeld = '$row[8]'  
                                  ,    Startgeld = '$row[9]' 
                                  ,    enrolement = '$row[10]' 
                                  ,    Mehrkampfcode = '$row[11]' 
                                  ,    Status = $statusSt  
                                  ,    StartgeldReduktion  = '$row[13]' 
                                  ,    Sortierwert = \"" .str_replace("\"","'",$row[14]) . "\" 
                                  , kKurzname = \"" .$row[16]. "\"  
                                  , kAnzeige = $row[17]    
                                  , kAlterslimite = $row[18]           
                                   ";     
                  
                       $res_t1 = mysql_query($sql_t1); 
                     
                       if(mysql_errno() > 0)        // DB error
                        { 
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }      
                               
                }
                
                // calculate started athlets for relays 
                //       and write them into the same temporary table                                                                          
                $sql="SELECT
                            st.xVerein AS clubnr
                            , v.Name AS club                    
                            , t.Startzeit
                            , SUM(if ((r.Status=4 OR r.Status=3) AND s.Anwesend=0,1,0)) as started 
                            , SUM(s.Anwesend) as anwesend
                            , SUM(if ((r.Status=4 OR r.Status=3) AND s.Anwesend=0,0,w.Haftgeld) )  AS Haftgeld   
                            , SUM(w.Startgeld) AS Startgeld
                            , count(s.xWettkampf) as enrolement
                            , w.mehrkampfcode
                            , r.status
                            , StartgeldReduktion
                            , v.Sortierwert
                            , w.xKategorie
                            , k.kurzname
                            , k.Anzeige
                            , k.Alterslimite
                            , st.xKategorie     
                      FROM
                            start as s
                            INNER JOIN wettkampf AS w ON (s.xWettkampf = w.xWettkampf)  
                            INNER JOIN disziplin_" . $_COOKIE['language'] . " as d on (d.xDisziplin = w.xDisziplin)
                            INNER JOIN staffel st ON (st.xStaffel = s.xStaffel)                              
                            INNER JOIN meeting AS m ON (w.xMeeting = m.xMeeting) 
                            LEFT JOIN verein AS v ON (st.xVerein=v.xVerein) 
                            LEFT JOIN runde AS r ON (r.xWettkampf = s.xWettkampf) 
                            LEFT JOIN result_tmp as t ON (s.xWettkampf = t.xWettkampf) 
                            LEFT JOIN kategorie as k ON (k.xKategorie = st.xKategorie) 
                      WHERE 
                            st.xMeeting =  " . $_COOKIE['meeting_id'] . " 
                            AND (t.Startzeit is Null Or t.Startzeit= r.Startzeit)   
                      GROUP BY st.xVerein
                      ORDER BY v.Sortierwert"; 
               
                $res = mysql_query($sql);  
                if(mysql_errno() > 0) {        // DB error
                   AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }                        
                $reductionAmount = 0;
              
                while($row = mysql_fetch_array($res)){ 
                       $sql_t1="INSERT INTO result_tmp1 SET  
                                    clubnr = $row[0]
                                  , club = \"" .$row[1]. "\"   
                                  , ReductionAmount  = '$reductionAmount'                                   
                                  ,    Startzeit = '$row[2]' 
                                  ,    started = '$row[3]'   
                                  ,    anwesend = '$row[4]' 
                                  ,    Haftgeld = '$row[5]'  
                                  ,    Startgeld = '$row[6]' 
                                  ,    enrolement = '$row[7]' 
                                  ,    Mehrkampfcode = '$row[8]' 
                                  ,    Status = '$row[9]'   
                                  ,    StartgeldReduktion  = '$row[10]' 
                                  ,    Sortierwert = \"" .$row[11]. "\" 
                                  , kKurzname = \"" .$row[13]. "\"  
                                  , kAnzeige = $row[14]    
                                  , kAlterslimite = $row[15]           
                                   ";     
                       $res_t1 = mysql_query($sql_t1); 
                     
                       if(mysql_errno() > 0)        // DB error
                        {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }      
                               
                }
                 
                
                // read all events from the temporary table
                 $sql_temp="SELECT *
                           FROM
                                result_tmp1 as t1  
                           ORDER BY t1.Sortierwert ,t1.clubnr, t1.kAnzeige, t1.Name, t1.Vorname, t1.Mehrkampfcode";       
               
                $res_temp = mysql_query($sql_temp);  
               
                $arr_kat = array();
                $assTax1 = 0;
                $assTax2 = 0;  
                $assTax3 = 0;  
                // count fees and deposits for each club 
                   while($row = mysql_fetch_array($res_temp)){             
                                                             
                    if ($club!=$row['clubnr'])  {
                                                
                        $tf += $fee;
                       
                        $td += $deposit;
                        $te += $entries;
                        $ts += $starts;
                       
                        if ($i>0) {
                            $doc->printLineTax($clubName,$fee, $deposit, $entries, $starts); 
                            foreach ($arr_kat as $key => $val){ 
                                if ($val[1] >= 16) {
                                     $assTax = $val[0] * 4;
                                     $assTax1 = $assTax1 + $assTax;
                                }
                                elseif ($val[1] <= 13) { 
                                    $assTax = $val[0] * 1;
                                    $assTax3 = $assTax3 + $assTax;  
                                }
                                else {
                                    $assTax = $val[0] * 2; 
                                    $assTax2 = $assTax2 + $assTax;   
                                }
                                $doc->printLineTax($key,'', '', '', $val[0], $assTax);  
                            }
                            $arr_kat = array();  
                        }                       
                        $i++;
                        $reduction = 0;
                        $starts = 0;
                        $fee = 0;
                        $deposit = 0;
                        $entries = 0;
                        $katStarts = 0;
                    }
                   
                  
                    
                    if ($row['Mehrkampfcode'] > 0) {
                        if ($club!=$row['clubnr'] && $clubName!= $row['club'] && $name!= $row['Name'] && $firstName!= $row['Vorname']){                            
                            $fee+=$row['Startgeld']; 
                        }  
                    }
                    else {
                          if ($row['Startgeld'] > 0){
                              $fee+=$row['Startgeld']-$row['ReductionAmount'] ; 
                          }
                    }
                       $starts+=$row['started'];  
                    $deposit+=$row['Haftgeld'];
                    $entries+=$row['enrolement'];                     
                     $clubName=$row['club'];
                      $name=$row['Name'];
                      $firstName= $row['Vorname'];    
                    
                    if ($kKurzname!=$row['kKurzname'] || $club!=$row['clubnr']){    

                         $kKurzname=$row['kKurzname'];  
                         $katStarts=$row['started'];
                         $arr_kat[$row['kKurzname']][0] =$katStarts;  
                         $arr_kat[$row['kKurzname']][1] =$row['kAlterslimite']; 
                    }
                    else {  
                          $katStarts+=$row['started']; 
                          $arr_kat[$row['kKurzname']][0] =$katStarts;  
                    }
                     $club=$row['clubnr'];                         
                }  
                
                
                $doc->printLineTax($clubName,$fee, $deposit, $entries, $starts); 
                foreach ($arr_kat as $key => $val){ 
                                if ($val[1] >= 16){
                                     $assTax = $val[0] * 4;
                                     $assTax1 = $assTax1 + $assTax;
                                } 
                                elseif ($val[1] <= 13){  
                                    $assTax = $val[0] * 1;
                                    $assTax3 = $assTax3 + $assTax;
                                }
                                else {
                                    $assTax = $val[0] * 2; 
                                    $assTax2 = $assTax2 + $assTax; 
                                }
                                $doc->printLineTax($key,'', '', '', $val[0], $assTax);      
                                  
                            } 
                  
                $tf += $fee;
                $td += $deposit;
                $te += $entries;
                $ts += $starts;     
    
    // add grand total
    $doc->printTotalLineTax($strTotal, $tf, $td, $te, $ts);
   
    $doc->printLineTax($strCatLicense);  
    $doc->printLineTax($strActiv_statistic,$strActivCHF,'','','',$assTax1,2); 
    $doc->printLineTax($strU16,$strU16CHF,'','','',$assTax2,2);  
    $doc->printLineTax($strU14,$strU14CHF,'','','',$assTax3,2); 
     
    $assTaxTotal=$assTax1 + $assTax2 + $assTax3; 
     
     $doc->printTotalLineTax($strTotal, '', '', '', $assTaxTotal,3); 
          
    mysql_free_result($result);
   
    mysql_query("DROP TABLE IF EXISTS result_tmp"); 
    mysql_query("DROP TABLE IF EXISTS result_tmp1");  
    mysql_query("DROP TABLE IF EXISTS result_tmp2");  
    mysql_query("DROP TABLE IF EXISTS result_tmp3");
    
    
$doc->endList();
$doc->endPage();    // end HTML page
?>
