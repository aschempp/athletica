<?php    
function AA_results_Tech($round, $event) { //results_tech.lib.php
    global $cfgEvalType;
    global $cfg_value;
    global $strEvalTypeHeat, $strEventTypeSingleCombined;
    
    $db = mysql_pconnect($cfg_value['server']['server_host'].':'.$cfg_value['server']['server_port'], $cfg_value['server']['server_username'], $cfg_value['server']['server_password']);
    mysql_select_db($cfg_value['server']['server_db'], $db);
    
    $eval = AA_results_getEvaluationType($round);
    $combined = AA_checkCombined(0, $round);
    
    mysql_query("LOCK TABLES r READ, s READ, ss READ, runde READ");
    
    // if this is a combined event, rank all rounds togheter
    $roundSQL = "";
    $roundSQL2 = "";
    if($combined){
        $roundSQL = " s.xRunde IN (";
        $roundSQL2 = " s.xRunde IN (";
        $res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$event);
        while($row_c = mysql_fetch_array($res_c)){
            $roundSQL .= $row_c[0].",";
            $roundSQL2 .= $row_c[0].",";
        }
        $roundSQL = substr($roundSQL,0,-1).")";
        $roundSQL2 = substr($roundSQL2,0,-1).")";
    }else{
        $roundSQL = " s.xRunde = $round";
        $roundSQL2 = " s.xRunde = $round";
    }
    
    // number of athletes
    
    $sql = "SELECT 
                    ss.xSerienstart  
             FROM 
                    runde AS r
                    LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                    LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                    LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                    LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                    LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
             WHERE r.xRunde = " . $round ."
                   ";
   
    $res = mysql_query($sql);
   
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else {
        $count_athlete = mysql_num_rows($res);     
    }
    
    // evaluate max. nbr of results entered
    $r = 0;
    
    $result = mysql_query("SELECT COUNT(*), ru.Versuche"
                        . " FROM resultat AS r"
                        . " LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)"
                        . " LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)"
                        . " LEFT JOIN runde AS ru ON (s.xRunde = ru.xRunde)" 
                        . " WHERE "                                
                        . " $roundSQL2 "
                        . " GROUP BY r.xSerienstart"
                        . " ORDER BY 1 DESC");  
                        
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else {
        $row = mysql_fetch_row($result);
        $r = $row[0];
          
        mysql_free_result($result);
    }            
                     
    if($r > 0)        // any results found
    {
            mysql_query("DROP TABLE IF EXISTS tempresult_".$round."");    // temporary table

        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            mysql_query("
                LOCK TABLES
                    resultat READ
                    , serie READ
                    , wettkampf READ
                    , serienstart WRITE
                    , tempresult_".$round." WRITE
            ");

            // Set up a temporary table to hold all results for ranking.
            // The number of result columns varies according to the maximum
            // number of results per athlete.
            $qry = "
                CREATE TABLE tempresult_".$round." (
                    xSerienstart int(11)
                    , xSerie int(11)";

            for($i=1; $i <= $r; $i++) {
                $qry = $qry . ", Res" . $i . " int(9) default '0'";
                $qry = $qry . ", Wind" . $i . " char(5) default '0'";
            }
            $qry = $qry . ") TYPE=HEAP";
          
            mysql_query($qry);    // create temporary table

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {  
                // reset rank to 0  first
                $sql=" SELECT
                        r.Leistung
                        , r.Info
                        , ss.xSerienstart
                        , ss.xSerie
                    FROM
                        resultat as r
                        LEFT JOIN serienstart as ss ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                    WHERE   
                    $roundSQL
                    AND r.Leistung <= 0
                    ORDER BY
                        ss.xSerienstart
                        ,r.Leistung DESC";
                $result = mysql_query($sql);
               
                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {
                      while($row = mysql_fetch_row($result))
                        {
                         mysql_query("
                            UPDATE serienstart SET
                                Rang = 0
                            WHERE xSerienstart = $row[2]
                        ");

                        if(mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }  
                      }
                }
                
                $result = mysql_query("
                    SELECT
                        r.Leistung
                        , r.Info
                        , ss.xSerienstart
                        , ss.xSerie
                    FROM
                       resultat as r
                        LEFT JOIN serienstart as ss ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                    WHERE 
                    $roundSQL
                    AND r.Leistung >= 0
                    ORDER BY
                        ss.xSerienstart
                        ,r.Leistung DESC
                ");
                         
            
                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else
                {   
                    
                    // initialize variables
                    $ss = 0;
                    $i = 0;
                    // process every result
                    while($row = mysql_fetch_row($result))
                    {
                        if($ss != $row[2])     // next athlete
                        {
                            // add one row per athlete to temp table
                            if($ss != 0) {
                                for(;$i < $r; $i++) { // fill remaining result cols.
                                    $qry = $qry . ",0,''";
                                }
                                
                                mysql_query($qry . ")");
                                 
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());     
                                }
                            }
                            // (re)set SQL statement
                            $qry = "INSERT INTO tempresult_".$round." VALUES($row[2],$row[3]";
                            $i = 0;
                        }
                        $qry = $qry . ",$row[0],'$row[1]'";    // add current result to query
                        $ss = $row[2];                // keep athlete's ID
                        $i++;                                // count nbr of results
                    }
                    mysql_free_result($result);
                   
                    // insert last pending data in temp table
                    if($ss != 0) {
                        for(;$i < $r; $i++) {    // fill remaining result cols.
                            $qry = $qry . ",0,''";
                        }
                        mysql_query($qry . ")");
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                    }
                }

                if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
                    $qry = "
                        SELECT
                            *
                        FROM
                            tempresult_".$round."
                        ORDER BY
                            xSerie";

                    for($i=1; $i <= $r; $i++) {
                        $qry = $qry . ", Res" . $i . " DESC";
                    }   
                                                                                                                            
                }
                else {    // default: rank results from all heats together
                    $qry = "
                        SELECT
                            *
                        FROM
                            tempresult_".$round."
                        ORDER BY ";
                    $comma = "";
                    // order by available result columns
                    for($i=1; $i <= $r; $i++) {
                        $qry = $qry . $comma . "Res" . $i . " DESC";
                        $comma = ", ";
                    }

                }
               
                $result = mysql_query($qry);

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {
                    // initialize variables
                    $heat = 0;
                    $perf_old[] = '';
                    $j = 0;
                    $rank = 0;
                    // set rank for every athlete
                    while($row = mysql_fetch_row($result))
                    {
                        for($i=0; $i < $r; $i++) {
                            $perf[$i] = $row[(2*$i)+2];
                            $wind[$i] = $row[(2*$i)+3];
                        }

                        if(($eval == $cfgEvalType[$strEvalTypeHeat])    // new heat
                            &&($heat != $row[1]))
                        {
                            $j = 0;        // restart ranking
                            $perf_old[] = '';
                        }

                        $j++;                                // increment ranking
                        if($perf_old != $perf) {    // compare performances
                            $rank = $j;    // next rank (only if not same performance)
                        }

                        mysql_query("
                            UPDATE serienstart SET
                                Rang = $rank
                            WHERE xSerienstart = $row[0]
                        ");

                        if(mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                        $heat = $row[1];        // keep current heat ID
                        $perf_old = $perf;
                    }
                    mysql_free_result($result);
                }

                mysql_query("DROP TABLE IF EXISTS tempresult_".$round."");

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
            }    // ET DB error (create temp table)

            mysql_query("UNLOCK TABLES");
        }    // ET DB error (drop temp table)
    }    // ET any results found
    
    //AA_results_setNotStarted($round);    // update athletes with no result
    //AA_results_resetQualification($round);    
    //AA_utils_calcRankingPoints($round);
}

function AA_results_getEvaluationType($round) {
    global $glb_connection_server;
    
    if(!empty($round))
    {
        try {
            $sql_eval = "SELECT Wertung
                            FROM rundentyp_" . CFG_CURRENT_LANGUAGE  . "
                                LEFT JOIN runde USING(xRundentyp)
                            WHERE xRunde = :round";
                                        
            $query_eval = $glb_connection_server->prepare($sql_eval);
            
            $query_eval->bindValue(':round', $round);
            
            $query_eval->execute();
            $eval = $query_eval->fetch(PDO::FETCH_ASSOC);
            $query_eval->closeCursor();
                
            $eval = $eval['Wertung'];
            
        } catch(PDOException $e){
            trigger_error($e->getMessage());
        }
    }
    return $eval;
}


function AA_checkCombined($event=0, $round=0){
    global $glb_connection_server;
    global $cfgEventType;
    global $strEventTypeSingleCombined;

    if($event > 0){
        try {
            $sql_comb = "SELECT Typ 
                            FROM wettkampf
                            WHERE xWettkampf = :event";
            
            $query_comb = $glb_connection_server->prepare($sql_comb);
                
            $query_comb->bindValue(':round', $round);
            
            $query_comb->execute();
            $combined = $query_comb->fetch(PDO::FETCH_ASSOC);
            $query_comb->closeCursor();
                            
            if($combined['Typ'] == $cfgEventType[$strEventTypeSingleCombined]){
                return true;
            }else{
                return false;
            }
        } catch(PDOException $e){
            trigger_error($e->getMessage());
        }
 
    }elseif($round > 0){    
        try {
            $sql_comb ="SELECT Typ 
                            FROM wettkampf
                                LEFT JOIN runde USING(xWettkampf)
                            WHERE xRunde = :round";
            
            $query_comb = $glb_connection_server->prepare($sql_comb);
                
            $query_comb->bindValue(':round', $round);
            
            $query_comb->execute();
            $combined = $query_comb->fetch(PDO::FETCH_ASSOC);
            $query_comb->closeCursor();

            if($combined['Typ'] == $cfgEventType[$strEventTypeSingleCombined]){
                return true;
            }else{
                return false;
            }
        } catch(PDOException $e){
            trigger_error($e->getMessage());
        }
    } 
}

function AA_results_setNotStarted($round) {
    global $glb_invalid_attempt;
    
    if(!empty($round))
    {
        mysql_query("LOCK TABLES serie READ, serie AS s READ "
                    . ", resultat WRITE, resultat AS r WRITE , serienstart WRITE, serienstart AS ss WRITE");    
    
        $sql = "SELECT 
                    DISTINCT ss.xSerienstart
                FROM 
                    serienstart AS ss
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                    LEFT JOIN resultat AS r ON (ss.xSerienstart = r.xSerienstart)
                WHERE 
                    r.Leistung = ".  array_search('DNS',$glb_invalid_attempt) . "                      
                    AND s.xRunde = " . $round;     
        
        $result = mysql_query($sql);       
       
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            // add result "Did Not Start or "No Result" to every athlete    
            while($row = mysql_fetch_row($result))
            {   
                mysql_query("UPDATE serienstart SET"
                            . " Rang = 0"
                            . " WHERE xSerienstart=" . $row[0]);

                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
            }
            mysql_free_result($result);
        }
        mysql_query("UNLOCK TABLES");
    }    // ET valid round
    return;
}

function AA_results_resetQualification($round) {
    global $cfgQualificationType;

    if(!empty($round))
    {
        mysql_query("LOCK TABLES serie AS s READ, serienstart AS ss WRITE, serie READ, serienstart WRITE");

        // get athletes by qualifying rank (random order if same rank)   
        // don't requalify athletes who waived to continue                    
        $sql = "SELECT 
                    ss.xSerienstart
                FROM 
                    serienstart AS ss 
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                WHERE 
                    ss.Qualifikation > 0    
                    AND ss.Qualifikation != ".$cfgQualificationType['waived']['code'] ."  
                    AND s.xRunde = " . $round;            
         
         $result = mysql_query($sql);     

        if(mysql_errno() > 0) {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            while($row = mysql_fetch_row($result))
            {
                mysql_query("UPDATE serienstart SET"
                            . " Qualifikation = 0"
                            . " WHERE xSerienstart = " . $row[0]);

                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
            }
            mysql_free_result($result);
        }
        mysql_query("UNLOCK TABLES");
    }
}

function AA_utils_calcRankingPoints($round){    
    global $strConvtableRankingPoints, $strConvtableRankingPointsU20, $cfgEventType;
    global $strEventTypeSVMNL, $strEventTypeSingleCombined, $strEventTypeClubAdvanced
        , $strEventTypeClubBasic, $strEventTypeClubTeam, $strEventTypeClubMixedTeam;
    global $cvtTable;
    
    $valid = false;
    $minus=true; 
    //
    // initialize parameters
    //
    $pStart = 0;
    $pStep = 0;
    $bSVM = false; // set if contest type has a result limitation for only best athletes
            // e.g.: for svm NL only the 2 best athletes of a team are counting -> distribute points on these athletes
    $countMaxRes = 0; // set to the maximum of countet results in case of an svm contest  

    $sql = "
        SELECT
            w.Punktetabelle
            , w.Punkteformel
            , w.Typ
        FROM
            runde as r
            LEFT JOIN wettkampf as w  ON (r.xWettkampf = w.xWettkampf )
        WHERE                 
            r.xRunde = $round";   
     
    $res = mysql_query($sql);      
    
    if(mysql_errno() > 0) {
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
        
        $row = mysql_fetch_array($res);
        mysql_free_result($res);
        $rpt = "";
        if ($row[0] == $cvtTable[$strConvtableRankingPoints]){
            $rpt = $cvtTable[$strConvtableRankingPoints];          
        }
        elseif ($row[0] == $cvtTable[$strConvtableRankingPointsU20]){  
                $rpt = $cvtTable[$strConvtableRankingPointsU20]; 
        }         
        if($row[0] == $rpt){
           
            // if mode is team
            if($row[2] > $cfgEventType[$strEventTypeSingleCombined]){
                $bSVM = true;                    
                switch($row[2]){
                    case $cfgEventType[$strEventTypeSVMNL]:   
                        $countMaxRes = 1;
                        break;
                    case $cfgEventType[$strEventTypeClubBasic]:
                        $countMaxRes = 1;
                        break;
                    case $cfgEventType[$strEventTypeClubAdvanced]:
                        $countMaxRes = 2;
                        break;
                    case $cfgEventType[$strEventTypeClubTeam]:
                        $countMaxRes = 5;
                        break;
                    case $cfgEventType[$strEventTypeClubMixedTeam]:
                        $countMaxRes = 6;
                        break;
                    default:
                        $countMaxRes = 1;
                }
            }
          
            //list($pStart, $pStep) = explode(" ", $GLOBALS['cvtFormulas'][$rpt][$row[1]]);
            list($pStart, $pStep) = explode(" ", $row[1]);
            if (strpos($row[1], '-') ){ 
                $pStep = str_replace('-', '', $pStep);
                $minus=true;
            }
            else {
                 $pStep = str_replace('+', '', $pStep);
                $minus=false;
            }
            $valid = true;
            
        }
        
    }
    
    //
    // calculate points
    //
    if($valid){   
       
        // if svm, the ranking points have only to be distributed on the results that count afterwards for team
        // so: only the best 2 athletes of the same team will get points    
        
        if(!$bSVM){                 
            
            $sql= "
                SELECT
                    ss.xSerienstart
                    , ss.Rang
                FROM
                    serienstart AS ss
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie )
                WHERE 
                     s.xRunde = $round
                     AND ss.Rang > 0
                ORDER BY ss.Rang ASC
            ";   
               
            $res = mysql_query($sql);

     }else{               
                 $res = mysql_query("
                    SELECT 
                        ss.xSerienstart
                        , ss.Rang
                        , IF(a.xTeam > 0, a.xTeam, staf.xTeam)
                    FROM
                        serienstart AS ss
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie  )
                        LEFT JOIN start AS st ON (ss.xStart = st.xStart)
                        LEFT JOIN staffel AS staf ON (st.xStaffel = staf.xStaffel)
                        LEFT JOIN anmeldung AS a ON (st.xAnmeldung = a.xAnmeldung)
                    WHERE                           
                        s.xRunde = $round
                        AND ss.Rang > 0
                    ORDER BY ss.Rang ASC
                    ");    
                       
        }     
         
        if(mysql_errno() > 0) {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
            
            $pts = 0;    // points to share
            $rank = 1;    // current rank
            $update = array();    // holding serienstart[key] with points
            $tmp = array();    // holding temporary serienstarts
            $point = $pStart;    // current points to set
            $i = 0;    // share counter
            
            $cClubs = array(); // count athlete teams for svm mode
            
            while($row = mysql_fetch_array($res)){
                
                if($bSVM){
                    // count athletes per club
                    if(isset($cClubs[$row[2]])){
                        $cClubs[$row[2]]++;
                    }else{
                        $cClubs[$row[2]] = 1;
                    }  
                            
                    // skip result if more than MaxRes athletes of a team are on top
                    if(isset($cClubs[$row[2]]) && $cClubs[$row[2]] > $countMaxRes){   
                                                          
                        mysql_query("UPDATE resultat SET
                                Punkte = 0
                            WHERE
                                xSerienstart = $row[0]");
                        if(mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                        
                        AA_StatusChanged($row[0]);                           
                        
                        continue; // skip
                    }
                }       
                
                    if($rank != $row[1] && $i > 0){
                        
                        $p = $pts / $i; // divide points for athletes with the same rank
                        $p = round($p, 1);
                        foreach($tmp as $x){
                            $update[$x] = $p;
                        }
                        $i = 1;
                        $pts = $point;
                        $rank = $row[1];
                        $tmp = array(); 
                                                    
                    }else{ 
                                                 
                        $i++;
                        $pts += $point; 
                                                       
                    }
                    
                    $tmp[] = $row[0];
                    
                    if ($minus){
                        $point -= $pStep;
                    }
                    else {
                        $point += $pStep; 
                    }    
            }
            
            // check on last entries
            if($i > 0){
                
                $p = $pts / $i; // divide points for athletes with the same rank
                $p = round($p, 1);
                foreach($tmp as $x){
                    $update[$x] = $p;
                }
                
            }
            
            // update points
            foreach($update as $key => $p){     
                mysql_query("UPDATE resultat SET
                        Punkte = $p
                    WHERE
                        xSerienstart = $key");
                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                
                 AA_StatusChanged($key);                    
            }
            
        }
    } // endif $valid
      
}

function AA_printErrorMsg($msg) {    
    // provide plain text message for certain DB errors
    ?>
    <script type="text/javascript">
    <!--
        alert("<?php echo $msg; ?>");
    //-->
    </script>
    <?php
}
?>
