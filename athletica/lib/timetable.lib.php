<?php

/**********
 *
 *    timetable extension
 *    -------------------
 * The timetable display function is used by the event monitor
 * and the speaker monitor to print an overview of all events
 * of a meeting.
 * Set the arg-parameter as follows:
 * - monitor
 * - speaker
 */

if (!defined('AA_TIMETABLE_LIB_INCLUDED'))
{
    define('AA_TIMETABLE_LIB_INCLUDED', 1);



/**
 *    show timetable
 *    -------------------
 */
function AA_timetable_display($arg = 'monitor')
{      


    require('./config.inc.php');
    require('./lib/common.lib.php');
   
   
     
    $result = mysql_query("
        SELECT DISTINCT
            k.Name
        FROM
            wettkampf AS w
            , kategorie AS k
        WHERE w.xMeeting = " . $_COOKIE['meeting_id'] . "
        AND w.xKategorie = k.xKategorie
        ORDER BY
            k.Anzeige,
            k.Kurzname
    ");
     
    if(mysql_errno() > 0)    // DB error
    {   
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else            // no DB error
    {
        $headerline = "";
        // assemble headerline and category array
        $cats = array();
        while ($row = mysql_fetch_row($result))
        {
            $headerline = $headerline . "<th class='timetable'>$row[0]</th>";
            $cats[] = $row[0];        // category array
        }
        mysql_free_result($result);
       
        // all rounds ordered by date/time
        // - count nbr of present athletes or relays (don't include
        //   athletes starting in relays)
        // - group by r.xRunde to show event-rounds entered more than once
        // - group by s.xWettkampf to count athletes per event
        // (the different date and time fields are required to properly set
        // up the table)
       
        $sql = "
            SELECT
                r.xRunde
                , r.Status
                , rt.Typ
                , k.Name
                , d.Kurzname
                , IF(s.xWettkampf IS NULL,0,COUNT(*))
                , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                , TIME_FORMAT(r.Startzeit, '%H')
                , DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
                , r.Datum
                , r.xWettkampf
                , k.xKategorie
                , r.Speakerstatus
                , w.Info
                , r.StatusZeitmessung
                , r.Gruppe
                , w.Typ
                , w.Mehrkampfende
                , w.Mehrkampfcode
                , rs.xRundenset
                , rs.Hauptrunde
                , d.xDisziplin                     
            FROM
                runde AS r
                LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
                LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON r.xRundentyp = rt.xRundentyp
                LEFT JOIN start AS s ON w.xWettkampf = s.xWettkampf
                    AND s.Anwesend = 0
                    AND ((d.Staffellaeufer = 0
                        AND s.xAnmeldung > 0)
                        OR (d.Staffellaeufer > 0
                        AND s.xStaffel > 0))
                LEFT JOIN rundenset AS rs ON (rs.xRunde = r.xRunde AND rs.xMeeting = " . $_COOKIE['meeting_id'] .") 
            WHERE 
                w.xMeeting=" . $_COOKIE['meeting_id'] ."               
            GROUP BY
                r.xRunde
                , s.xWettkampf
             ORDER BY
                r.Datum
                , r.Startzeit
                , k.Anzeige
                , k.Kurzname
                , d.Anzeige";    
        
        $res = mysql_query($sql);  
                     
        if(mysql_errno() > 0)    // DB error
        {    
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else            // no DB error
        {
            $date = 0;
            $hour = '';
            $time = 0;
            $i=0;
            $k='';
            $events = array();    // array to hold last processed round per event
            ?>
<table class=timetable> 
            <?php   
            
            while ($row = mysql_fetch_row($res))
            {   
                 //
                // read merged rounds an select all events
                //
     
                $sqlEvents = "";
                $eventMerged = false;
                $result = mysql_query("SELECT xRundenset FROM rundenset
                        WHERE    xRunde = $row[0] 
                        AND    xMeeting = ".$_COOKIE['meeting_id']);
               
                if(mysql_errno() > 0){
                   
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }else{
                    $rsrow = mysql_fetch_array($result); // get round set id
                    mysql_free_result($result);
                }
                $event = $row[10];
   
    
                if($rsrow[0] > 0){
                    $sql = "SELECT
                                r.xWettkampf 
                            FROM
                                rundenset AS s
                            LEFT JOIN 
                                runde AS r USING(xRunde)
                            WHERE
                                s.xMeeting = ".$_COOKIE['meeting_id']." 
                                AND s.xRundenset = ".$rsrow[0].";";
                    $result = mysql_query($sql);
                    if(mysql_errno() > 0){
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        $sqlEvents .= " st.xWettkampf = ".$event." ";
                    }else{ 
                        if(mysql_num_rows($result) == 0){ // no merged rounds
                            $sqlEvents .= " st.xWettkampf = ".$event." ";
                        }else{   
                            $eventMerged = true;
                            $sqlEvents .= "( st.xWettkampf = ".$event." ";
                            while($row_m = mysql_fetch_array($result)){
                                if($row_m[0] != $event){ // if there are additional events (merged rounds) add them as sql statement
                                    $sqlEvents .= " OR st.xWettkampf = ".$row_m[0]." ";
                                }
                            }
                            $sqlEvents .= ") ";       
                        }
                    mysql_free_result($result);  
                    }
                }else{
                    $sqlEvents .= " st.xWettkampf = ".$event." ";
                }  
                
                $combGroup = "";    // combined group if set
                $combined = false;    // is combined event
                $teamsm = false;    // is team sm event
                if($row[16] == $cfgEventType[$strEventTypeSingleCombined]){
                    $combined = true;
                }
                if($row[16] == $cfgEventType[$strEventTypeTeamSM]){
                    $teamsm = true;
                }
                $roundSet = $row[19];    // set if round is merged
                $roundSetMain = $row[20];    // main round flag of round set
                                // if main round -> count athletes from all merged rounds
                
                // new date or time: start a new line
                if(($date != $row[8]) || ($time != $row[6]))    // new date or time
                {
                    if($date != 0) {        // not first item
                        ?>
    </td>
                        <?php
                        // fill previous line with cell items if necessary
                        while(current($cats) == TRUE) {
                            ?>
    <td class='monitor' />
                            <?php
                            next($cats);
                        }
                        ?>
</tr>
                        <?php
                    }

                    if($date != $row[8])    {    // new date -> headerline with date
                        ?>
<tr>
    <th class='date' id='<?php echo "$row[9]$row[7]"; ?>'><?php echo $row[8]; ?></th>
    <?php echo $headerline; ?>
</tr>
                        <?php
                    }
                    else if ($hour != $row[7]) {    // new hour -> headerline
                        ?>
<tr>
    <th class='timetable_sub' id='<?php echo "$row[9]$row[7]"; ?>' />
    <?php echo $headerline; ?>
</tr>
                        <?php
                    }        // ET new date or new hour

                    if($i % 2 == 0 ) {        // even row number
                        $class='even';
                    }
                    else {    // odd row number
                        $class='odd';
                    }    
                    $i++;
                    reset($cats);        // reset category array to first item
                    $k = '';
                    ?>
<tr class='<?php echo $class; ?>'>
    <th class='timetable_sub'><?php echo $row[6]; ?></th>
                    <?php
                }        // ET new date, time

                $time = $row[6];
                $hour = $row[7];
                $date = $row[8];
                
                // check round status and set correct link
                if($arg == 'monitor')        // event monitor
                {   
                    // check status
                    switch($row[1]) {
                    case($cfgRoundStatus['open']):
                        $class = "";
                        //$href = "event_heats.php?round=$row[0]";
                        if($combined){ 
                            $href = "event_enrolement.php?category=$row[11]&comb=$row[11]_$row[18]_$row[21]&group=$row[15]&round=$row[0]";
                        }else{
                            if ($teamsm){
                                  $href = "event_enrolement.php?category=$row[11]&event=$row[10]&round=$row[0]&teamsm=$teamsm&group=$row[15]";
                            }
                            else {
                                $href = "event_enrolement.php?category=$row[11]&event=$row[10]&round=$row[0]";
                            }
                                
                        }
                        break;
                    case($cfgRoundStatus['enrolement_pending']):
                        $class = "st_enrlmt_pend";
                        if($combined){  
                            $href = "event_enrolement.php?category=$row[11]&comb=$row[11]_$row[18]_$row[21]&round=$row[0]&group=$row[15]";    
                        }else{
                             if ($teamsm){  
                                 $href = "event_enrolement.php?category=$row[11]&event=$row[10]&round=$row[0]&teamsm=$teamsm&group=$row[15]";   
                             }
                             else {
                                  $href = "event_enrolement.php?category=$row[11]&event=$row[10]&round=$row[0]"; 
                             }                                    
                        }
                        break;
                    case($cfgRoundStatus['enrolement_done']):
                        $class = "st_enrlmt_done";
                        $href = "event_heats.php?round=$row[0]";
                        break;
                    case($cfgRoundStatus['heats_in_progress']):
                        $class = "st_heats_work";
                        $href = "event_heats.php?round=$row[0]";
                        break;
                    case($cfgRoundStatus['heats_done']):
                        $class = "st_heats_done";
                        $href = "event_results.php?round=$row[0]";
                        break;
                    case($cfgRoundStatus['results_in_progress']):
                        $class = "st_res_work";
                        $href = "event_results.php?round=$row[0]";
                        break;
                    case($cfgRoundStatus['results_live']):
                        $class = "st_res_live";
                        $href = "event_results.php?round=$row[0]";
                        break;       
                    case($cfgRoundStatus['results_done']):
                        $class = "st_res_done";
                        $href = "event_results.php?round=$row[0]";
                        break;                      
                    }
                    if($row[14] == 1 && $row[1] == $cfgRoundStatus['heats_done']){ // results importet from timing
                        $class = "st_res_timing";
                    }
                }
                else if($arg == 'speaker')        // speaker monitor
                {
                    // check round status and set CSS class
                    switch($row[1]) {
                    case($cfgRoundStatus['open']):
                    case($cfgRoundStatus['enrolement_pending']): 
                        $class = "";  
                        if ($teamsm){  
                            $href = "speaker_entries.php?round=$row[0]&teamsm=$teamsm&group=$row[15]";   
                        }
                        else {
                             $href = "speaker_entries.php?round=$row[0]&group=$row[15]";   
                        }
                        break;  
                    case($cfgRoundStatus['enrolement_done']): 
                       $class = "st_enrlmt_done"; 
                        $href = "speaker_entries.php?round=$row[0]&group=$row[15]";
                        break;   
                    case($cfgRoundStatus['heats_in_progress']): 
                         $class = "st_heats_work"; 
                        $href = "speaker_entries.php?round=$row[0]&group=$row[15]";
                        break;                  
                    case($cfgRoundStatus['heats_done']):
                        $class = "st_heats_done";
                        $href = "speaker_results.php?round=$row[0]";
                        break;
                    case($cfgRoundStatus['results_in_progress']):
                        $class = "st_res_work";
                        $href = "speaker_results.php?round=$row[0]";
                        break;
                    case($cfgRoundStatus['results_live']):
                        $class = "st_res_live";
                        $href = "speaker_results.php?round=$row[0]";
                        break;    
                    case($cfgRoundStatus['results_done']):
                        $class = "st_res_done";
                        $href = "speaker_results.php?round=$row[0]";
                        break;
                    }

                    // overrule by speaker status, CSS class
                    switch($row[12]) {
                    case($cfgSpeakerStatus['announcement_pend']):
                        $class = "st_anct_pend";
                        break;
                    case($cfgSpeakerStatus['announcement_done']):
                        $class = "st_anct_done";
                        break;
                    case($cfgSpeakerStatus['ceremony_done']):
                        $class = "st_crmny_done";
                        break;
                    }
                }

                
                // next event is in a different category: go to next cell
                if($k != $row[3])
                {  
                    if(key($cats) != 0) {     // not first category
                        ?>
    </td>
                        <?php
                    }

                    $k = $row[3];        // keep current category
                    while(current($cats) != $k) {
                        ?>
    <td class='monitor' />
                        <?php
                        if((next($cats)) == FALSE) {        // after end of array
                            break;
                        }
                    }
                   
                    if(array_key_exists($row[10], $events) == TRUE         // not first round (count qualified athletes)
                        && $combined == false && $teamsm == false)     // no combined event
                    {    
                        $starts = "-";
                        // get number of athletes/relays with valid result
                        if ($row[2] == 'S' || $row[2] == '0'){                // round typ: S = Serie ,  0 = ohne
                           
                            $sql = "
                                SELECT
                                    COUNT(*)
                                FROM
                                    serienstart AS ss
                                    LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie )
                                WHERE                                     
                                    s.xRunde =" . $events[$row[10]];    
                        }
                        else {
                            
                            $sql="SELECT
                                COUNT(*)
                            FROM
                                serienstart AS ss
                                LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie)
                            WHERE 
                                ss.Qualifikation > 0                                  
                                AND s.xRunde =" . $events[$row[10]];     
                        }
                        
                        $result=mysql_query($sql);
                           
                        if(mysql_errno() > 0) {        // DB error
                        
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                        else {
                            $start_row = mysql_fetch_row($result);
                            $starts = $start_row[0];
                            mysql_free_result($result);
                        }
                    }elseif($combined || $teamsm){ // for combined rounds, count starts for correct group
                        
                         if($roundSet > 0){
                               
                        if($roundSetMain == 0){
                            $starts = "m";                               
                }else{
                        if($row[17] == 1){ // if this is a combined last event, every athlete starts
                            $starts = $row[5];
                        }elseif(empty($row[15])){ // if no group is set
                            $starts = $row[5];
                           
                            $sql_c="SELECT COUNT(*) FROM
                                            start as st
                                            LEFT JOIN anmeldung as a ON (st.xAnmeldung = a.xAnmeldung)
                                        WHERE    
                                            $sqlEvents
                                        AND    a.Gruppe = '$row[15]'
                                        AND st.Anwesend = 0";    
                                        
                            $result = mysql_query($sql_c);  
                           
                            if(mysql_errno() > 0) {    
                             
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{
                                $start_row = mysql_fetch_array($result);
                                $starts = $start_row[0];
                            }
                           
                        }else{      
                           
                            $sql = "SELECT COUNT(*) 
                                    FROM
                                            start as st
                                            LEFT JOIN anmeldung as a ON (st.xAnmeldung = a.xAnmeldung)
                                    WHERE    
                                            st.xWettkampf = $row[10]
                                            AND a.Gruppe = '$row[15]'
                                            AND st.Anwesend = 0";     
                             
                            $result = mysql_query($sql);   
                            
                            if(mysql_errno() > 0) {     
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{
                                $start_row = mysql_fetch_array($result);
                                $starts = $start_row[0];
                                mysql_free_result($result);
                            }
                            $combGroup = "&nbsp;g".$row[15];
                        }
                        
                        }
                         }
                         else {
                             if($row[17] == 1){ // if this is a combined last event, every athlete starts
                            $starts = $row[5];
                        }elseif(empty($row[15])){ // if no group is set
                               if ($teamsm) { 
                                   $sql = "SELECT
                                              DISTINCT st.xAnmeldung                             
                                          FROM
                                              start AS st
                                          INNER JOIN
                                              anmeldung AS a USING(xAnmeldung)
                                          INNER JOIN
                                              teamsmathlet AS tat ON (st.xAnmeldung = tat.xAnmeldung)
                                          WHERE 
                                              st.xWettkampf = $row[10]
                                              AND    st.Gruppe = '$row[15]'
                                              AND st.Anwesend = 0";  
                                  
                                   $result = mysql_query($sql);   
                                   if(mysql_errno() > 0) {     
                                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                   }
                                   $starts = mysql_num_rows($result);   
                                   
                               } 
                               else {
                                   $starts = $row[5];   
                               }  
                           
                        }else{      
                              if ($teamsm) {    
                                  
                                       $sql = "SELECT COUNT(*) 
                                            FROM
                                                    start as st
                                                    LEFT JOIN anmeldung as a ON (st.xAnmeldung = a.xAnmeldung)
                                            WHERE    
                                                    st.xWettkampf = $row[10]
                                                    AND    st.Gruppe = '$row[15]'
                                                    AND st.Anwesend = 0";    
                                   
                              }
                              else {
                                    $sql = "SELECT COUNT(*) 
                                    FROM
                                            start as st
                                            LEFT JOIN anmeldung as a ON (st.xAnmeldung = a.xAnmeldung)
                                    WHERE    
                                            st.xWettkampf = $row[10]
                                            AND    a.Gruppe = '$row[15]'
                                            AND st.Anwesend = 0";    
                              }
                                                                       
                            
                            $result = mysql_query($sql);   
                            
                            if(mysql_errno() > 0) {     
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{
                                 
                                 $start_row = mysql_fetch_array($result);
                                 $starts = $start_row[0];
                                 mysql_free_result($result);
                                                       
                               
                            }
                            $combGroup = "&nbsp;g".$row[15];
                        }
                         }
                    }elseif($roundSet > 0){
                           
                        if($roundSetMain == 0){
                            $starts = "m";                                
                        }else{        
                           
                            $sql = "SELECT 
                                            COUNT(*) 
                                    FROM
                                            rundenset AS rs
                                            LEFT JOIN runde AS r ON (r.xRunde = rs.xRunde  )
                                            LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
                                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                                            LEFT JOIN start AS s ON w.xWettkampf = s.xWettkampf
                                                AND s.Anwesend = 0
                                                AND ((d.Staffellaeufer = 0
                                                    AND s.xAnmeldung > 0)
                                                    OR (d.Staffellaeufer > 0
                                                    AND s.xStaffel > 0))
                                    WHERE
                                            rs.xRundenset = $roundSet                                        
                                            AND s.xWettkampf > 0 
                                            AND rs.xMeeting = " . $_COOKIE['meeting_id'] ."  
                                            AND w.xMeeting = " . $_COOKIE['meeting_id'];   
                           
                             $result = mysql_query($sql);

                            if(mysql_errno() > 0) {        // DB error
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{ 
                                $start_row = mysql_fetch_array($result);
                                $starts = $start_row[0];
                                mysql_free_result($result);
                            }
                        }
                        
                    }else
                    {
                        $starts = $row[5];
                    }
                    
                    if ($row[2] == '0'){                // round typ:  0 = ohne
                        $roundtype = " ";
                    }
                    else {
                         $roundtype = $row[2];
                    }
                    
                    ?>
<td class='monitor'>
    <div class='<?php echo $class; ?>'>
        <a href='<?php echo $href; ?>'>
            <?php echo "&nbsp;$row[4]&nbsp;$roundtype$combGroup"; ?>
        (<?php echo $starts; ?>) <?php echo $row[13]; ?></a>
    </div>
                    <?php
                    next($cats);
                }

                // next event has same category: linebreak within cell
                else        // same category
                {   
                    if(array_key_exists($row[10], $events) == TRUE         // not first round (count qualified athletes)
                        && $combined == false && $teamsm == false)     // no combined event, no team sm event
                    {
                        $starts = "-";
                        // get number of athletes/relays with valid result                          
                        $sql = "
                            SELECT
                                COUNT(*)
                            FROM
                                serienstart AS ss
                                LEFT JOIN serie AS s ON (s.xSerie = ss.xSerie)
                            WHERE 
                                ss.Qualifikation > 0                            
                                AND s.xRunde =" . $events[$row[10]];    
                                
                           $result = mysql_query($sql);      

                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                        else {
                            $start_row = mysql_fetch_row($result);
                            $starts = $start_row[0];
                            mysql_free_result($result);
                        }
                    }elseif($combined || $teamsm){ // for combined rounds, count starts for correct group
                        
                        if($row[17] == 1){ // if this is a combined last event, every athlete starts
                            $starts = $row[5];
                        }elseif(empty($row[15])){ // if no group is set
                            $starts = $row[5];
                        }else{   
                             if ($teamsm) {   
                                            
                                        $sql = "SELECT 
                                            COUNT(*) 
                                         FROM   
                                                start as st
                                                LEFT JOIN anmeldung as a  ON (st.xAnmeldung = a.xAnmeldung)
                                         WHERE    
                                                st.xWettkampf = " .$row[10] ."
                                                AND st.Gruppe = '" .$row[15] ."'";    
                             }  
                             else {
                                 
                                                           
                                 $sql = "SELECT 
                                            COUNT(*) 
                                         FROM   
                                                start as st
                                                LEFT JOIN anmeldung as a  ON (st.xAnmeldung = a.xAnmeldung)
                                         WHERE    
                                                st.xWettkampf = " .$row[10] ."
                                                AND a.Gruppe = '" .$row[15] ."'";    
                             } 
                             $result = mysql_query($sql);
                             
                            if(mysql_errno() > 0) {        // DB error
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{
                                $start_row = mysql_fetch_array($result);
                                $starts = $start_row[0];
                                mysql_free_result($result);
                            }
                            $combGroup = "&nbsp;g".$row[15];
                        }
                        
                    }elseif($roundSet > 0){
                        
                        if($roundSetMain == 0){
                            $starts = "m";   
                        }else{                                 
                          
                            $sql = "SELECT 
                                            COUNT(*) 
                                    FROM
                                            rundenset AS rs
                                            LEFT JOIN runde AS r ON (r.xRunde = rs.xRunde)
                                            LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
                                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)
                                            LEFT JOIN start AS s ON w.xWettkampf = s.xWettkampf
                                                AND s.Anwesend = 0
                                                AND ((d.Staffellaeufer = 0
                                                    AND s.xAnmeldung > 0)
                                                    OR (d.Staffellaeufer > 0
                                                    AND s.xStaffel > 0))
                                    WHERE
                                            rs.xRundenset = $roundSet                                           
                                            AND s.xWettkampf > 0 
                                            AND rs.xMeeting = " . $_COOKIE['meeting_id'] ."  
                                            AND w.xMeeting = " . $_COOKIE['meeting_id'];     
                            
                             $result = mysql_query($sql);

                            if(mysql_errno() > 0) {        // DB error
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{
                                $start_row = mysql_fetch_array($result);  
                                $starts = $start_row[0];
                                mysql_free_result($result);
                            }
                        }
                        
                    }
                    else {   
                        $starts = $row[5];
                    }
                    ?>
    <div class='<?php echo $class; ?>'>
        <a <?php echo $class; ?> href='<?php echo $href; ?>'>
            <?php echo "&nbsp;$row[4]&nbsp;$row[2]$combGroup&nbsp; " ?>
        &nbsp;(<?php echo $starts; ?>) <?php echo $row[13]; ?></a>
    </div>
                    <?php
                }

                $events[$row[10]] = $row[0]; // keep last processed round per event
            }    // END while every event round
            mysql_free_result($res);
            ?>
    </td>
            <?php
            while(current($cats) == TRUE) {
                ?>
    <td />
                <?php
                next($cats);
            }
            ?>
</tr>
            <?php
        }        // ET DB error event rounds
        ?>
</table>
        <?php
    }        // ET DB timetable item error
}

// timetable regie (display only starttime < timestamp + status=heats in progress) 
function AA_timetable_display_regie($timestamp)
{
    require('./config.inc.php');
    require('./lib/common.lib.php');
    require('./lib/regie_results_track.lib.php');   
    require('./lib/regie_results_tech.lib.php');   
    require('./lib/regie_results_high.lib.php');    
        
    mysql_query("DROP TABLE IF EXISTS `tempTrack`");    // temporary table     
    mysql_query("DROP TABLE IF EXISTS `tempHigh`");    // temporary table   
                                                                
            $temp = mysql_query("
            
            CREATE TEMPORARY TABLE IF NOT EXISTS `tempTrack` (
                          `leistung` int(11) NOT NULL default '0',  
                          `xSerienstart` int(10) NOT NULL default '0',
                          `xSerie` int(10) NOT NULL default '0',  
                          `rang` int(10) NOT NULL default '0',
                          PRIMARY KEY  (`xSerienstart`)
                        )  ENGINE=HEAP 
                        ");
            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {                                                     
                 // Set up a temporary table to hold all results for ranking.
                        mysql_query("
                            CREATE TEMPORARY TABLE IF NOT EXISTS tempHigh (
                                xSerienstart int(11)
                                , xSerie int(11)
                                , Leistung int(9)
                                , TopX int(1)
                                , TotalX int(2)
                                , `rang` int(10) NOT NULL default '0' 
                                )
                                ENGINE=HEAP 
                        "); 
                  if(mysql_errno() > 0) {        // DB error
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }
                  else {     
     
                       $timestamp = time();      
                        
                       $sql = "SELECT DISTINCT
                                        k.Name
                               FROM
                                        wettkampf AS w
                                        LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
                               WHERE 
                                        w.xMeeting = " . $_COOKIE['meeting_id'] . "                                         
                               ORDER BY
                                        k.Anzeige,
                                        k.Kurzname";       
                           
                        $result = mysql_query($sql);      
                       
                        if(mysql_errno() > 0)    // DB error
                        {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                        else            // no DB error
                        {
                            $headerline = "";
                            // assemble headerline and category array
                            $cats = array();
                            while ($row = mysql_fetch_row($result))
                            {
                                $headerline = $headerline . "<th class='timetable'>$row[0]</th>";
                                $cats[] = $row[0];        // category array
                            }
                            mysql_free_result($result);
                           
                            // all rounds ordered by date/time
                            // - count nbr of present athletes or relays (don't include
                            //   athletes starting in relays)
                            // - group by r.xRunde to show event-rounds entered more than once
                            // - group by s.xWettkampf to count athletes per event
                            // (the different date and time fields are required to properly set
                            // up the table)             
                          
                            $sql="SELECT
                                    r.xRunde
                                    , r.Status
                                    , rt.Typ
                                    , k.Name
                                    , d.Kurzname
                                     , IF(s.xWettkampf IS NULL,0,COUNT(*))   
                                    , TIME_FORMAT(r.Startzeit, '$cfgDBtimeFormat')
                                    , TIME_FORMAT(r.Startzeit, '%H')
                                    , DATE_FORMAT(r.Datum, '$cfgDBdateFormat')
                                    , r.Datum
                                    , r.xWettkampf
                                    , k.xKategorie
                                    , r.Speakerstatus
                                    , w.Info
                                    , r.StatusZeitmessung
                                    , r.Gruppe
                                    , w.Typ
                                    , w.Mehrkampfende
                                    , w.Mehrkampfcode
                                    , rs.xRundenset
                                    , rs.Hauptrunde
                                    , d.xDisziplin
                                    , r.Startzeit                   
                                FROM
                                    runde AS r
                                    LEFT JOIN wettkampf AS w ON (r.xWettkampf = w.xWettkampf)
                                    LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
                                    LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)
                                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt
                                    ON r.xRundentyp = rt.xRundentyp
                                LEFT JOIN start AS s
                                    ON w.xWettkampf = s.xWettkampf
                                    AND s.Anwesend = 0
                                    AND ((d.Staffellaeufer = 0
                                        AND s.xAnmeldung > 0)
                                        OR (d.Staffellaeufer > 0
                                        AND s.xStaffel > 0))
                                LEFT JOIN rundenset AS rs ON (rs.xRunde = r.xRunde AND rs.xMeeting = " . $_COOKIE['meeting_id'] .") 
                                WHERE w.xMeeting=" . $_COOKIE['meeting_id'] ."             
                                AND (r.status = " . $cfgRoundStatus['results_in_progress'] . "
                                OR r.status = " . $cfgRoundStatus['results_live'] . ")  
                                 GROUP BY
                                    r.xRunde
                                    , s.xWettkampf             
                                 ORDER BY
                                    r.Datum
                                    , r.Startzeit
                                    , k.Anzeige
                                    , k.Kurzname
                                    , d.Anzeige";    
                            $res = mysql_query($sql); 
                           
                            if(mysql_errno() > 0)    // DB error
                            {
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }
                            else            // no DB error
                            {
                                $date = 0;
                                $hour = '';
                                $time = 0;
                                $i=0;
                                $k='';
                                $events = array();    // array to hold last processed round per event
                                ?>     
                                
                    <table> 
                                <?php   
                                
                                while ($row = mysql_fetch_row($res))
                                { 
                                   // if (strtotime($row[22]) >= $timestamp){           
                                   //      continue;
                                   // }
                                   
                                    if ($row[20] == 0 && $row[20] != NULL)  {               // don't' show merged  rounds
                                          continue;  
                                    }
                                                          
                                    if ( ($i % 2) == 0){                         
                                        ?>
                                        <tr>
                                        <td class="dialog-top">
                                       <?php
                                    }
                                    else {    
                                         ?>                    
                                         <td class="dialog-top">   
                                       <?php
                                    }
                                    $layout = AA_getDisciplineType($row[0]);    // type determines layout  
                                    
                                    // track disciplines, with or without wind
                                    if(($layout == $cfgDisciplineType[$strDiscTypeNone])
                                        || ($layout == $cfgDisciplineType[$strDiscTypeTrack])
                                        || ($layout == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                                        || ($layout == $cfgDisciplineType[$strDiscTypeDistance])
                                        || ($layout == $cfgDisciplineType[$strDiscTypeRelay]))
                                    {
                                        AA_regie_Track($row[10], $row[0], $layout, $row[3], $row[4]);
                                    }
                                    // technical disciplines, with or withour wind
                                    else if(($layout == $cfgDisciplineType[$strDiscTypeThrow])
                                        || ($layout == $cfgDisciplineType[$strDiscTypeJump])
                                        || ($layout == $cfgDisciplineType[$strDiscTypeJumpNoWind]))
                                    {
                                        AA_regie_Tech($row[10], $row[0], $layout, $row[3], $row[4]); 
                                    }
                                    // high jump, pole vault
                                    else if($layout == $cfgDisciplineType[$strDiscTypeHigh])
                                    {
                                        AA_regie_High($row[10], $row[0], $layout, $row[3], $row[4]); 
                                    } 
                                    
                                    $i++;  
                                    if ( ($i % 2) == 0){                           
                                        ?>
                                        </td> 
                                        </tr> 
                                       <?php
                                    }
                                    else {      
                                         ?>                    
                                        </td>
                                       <?php
                                    }    
                                  
                                }    // END while every event round 
                                
                                mysql_free_result($res);
                               
                                ?>
                    </tr>
                                <?php
                            }        // ET DB error event rounds
                            ?>
                    </table>
                            <?php
          }
        }  
                     
    }        // ET DB timetable item error
    
       $temp = mysql_query("DROP TABLE IF EXISTS `tempTrack`");  
     
       $temp = mysql_query("DROP TABLE IF EXISTS `tempHigh` ");
}

}        // AA_TIMETABLE_LIB_INCLUDED
    
