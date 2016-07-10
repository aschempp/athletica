<?php
/**
* provides functions for the result handling 
* 
* @package Athletica Technical Client
*
* @author mediasprint gmbh, Domink Hadorn <dhadorn@mediasprint.ch>
* @copyright Copyright (c) 2012, mediasprint gmbh
*/

// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
	header('Location: index.php');
	exit();
}
// +++ make sure that the file was not loaded directly  
    
function getNofResults($event, $xSerienstart = 0, $maxRang = 0, $details = true, $all = false) {
    global $glb_connection_server;
    global $glb_results_skip;

    try {
        
        if($all){
            $where = 'xRunde = :runde';
        } else{
            $where = 'xSerie = :serie';
        }
        if($xSerienstart == 0) {
            $and_id = "";
        } else{
            $and_id = " AND xSerienstart = :serienstart";
        }
        if($maxRang == 0) {
            $and_rank = "";
        } else{
            $and_rank = " AND Rang <= :maxrang AND Rang > 0";
        }
        
        $sql_res = "SELECT xSerienstart
                        , IF(Position2=0, Position, Position2) AS Position
                        , COUNT(Leistung) AS results
                        , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        FROM serienstart  
                            LEFT JOIN resultat USING(xSerienstart)
                            LEFT JOIN serie USING(xSerie) 
                        WHERE 
                        ".$where."
                        ".$and_id."
                        ".$and_rank."
                        GROUP BY xSerienstart 
                        ORDER BY Position ASC;";
         
        $query_res = $glb_connection_server->prepare($sql_res);

        if($all) {
            $query_res->bindValue(':runde', getRound(CFG_CURRENT_EVENT)); 
        } else{
            $query_res->bindValue(':serie', CFG_CURRENT_EVENT);
        }
        if($xSerienstart != 0) {
            $query_res->bindValue(':serienstart', $xSerienstart);
        }
        if($maxRang != 0) {
            $query_res->bindValue(':maxrang', $maxRang);
        }

        $query_res->execute();
        if($xSerienstart == 0) {
            $return = $query_res->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
            $return = array_map('reset', $return);
        } else {
            $return = $query_res->fetch(PDO::FETCH_ASSOC);
            if(!$details){
                $return = $return['results'];
            }
        }
    
        
        return $return;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }       
}

function getNofAthletes($event, $all = false){
    global $glb_connection_server;  
    
    try{
        if($all){
            $where = 'xRunde = :runde';
        } else{
            $where = 'xSerie = :serie';
        }
                
       $sql_res = "SELECT * 
                        FROM serienstart  
                            LEFT JOIN start USING(xStart)
                            LEFT JOIN serie USING(xSerie) 
                        WHERE 
                        ".$where."
                            AND start.Anwesend = 0
                        GROUP BY xSerienstart 
                        ORDER BY Position ASC;";
         
        $query_res = $glb_connection_server->prepare($sql_res);

        if($all) {
            $query_res->bindValue(':runde', getRound(CFG_CURRENT_EVENT)); 
        } else{
            $query_res->bindValue(':serie', CFG_CURRENT_EVENT);
        }

        $query_res->execute();
            
        $return = $query_res->rowCount();
        
        
        
        return $return;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }  
}

function getCurrentAthlete($athletes) {
    try{            
        $curr = 0;
        $tmp = 10000;
        foreach($athletes AS $id => $ath) {                
            if($ath['results'] < $tmp  && $ath['skip'] == 0) {
                $curr = $id;
                $tmp = $ath['results'];
            }
        } 
        
        return $curr;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }     
}

function getNextAthlete($athletes, $current) {
    try{            
        foreach($athletes AS $id => $ath) { // Athlet mit der nächten Positionsnummer       
            if($ath['Position'] > $current && $ath['skip'] == 0) {
                return $id;
            }
        } 
        return 0; // falls letzter Athlet
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }     
}

function getFirstAthlete($athletes) {
    try{            
        foreach($athletes AS $id => $ath) { // erster Athlet ohne DSQ und DNS          
            if($ath['skip'] == 0) {
                return $id;
            }
        } 
        return 0;
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }     
}

function getAthleteDetails($athlete = 0, $result = false, $order = 'ath_pos', $maxRang = 0, $singleRow = false, $all = false) {
    global $glb_connection_server;
    global $glb_results_skip;
    
    try{
        if($athlete > 0){
            $where = 'xSerienstart = :serienstart';
        } elseif($all){
            $where = 'xRunde = :runde';
        } else{
            $where = 'xSerie = :serie';
        }
        
        if($maxRang > 0) {
            $and = ' AND Rang <= :rang AND Rang > 0';
        } else {
            $and = '';
        }
        
        if($result) {
            $select_result = ", IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")),IFNULL((SELECT COALESCE(Leistung) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")),''),IFNULL((SELECT MAX(Leistung) FROM resultat WHERE xSerienstart = serienstart.xSerienstart),'')) AS ath_res";
        } else {
            $select_result = "";
        }
        
        $sql_ath = "SELECT DISTINCT
                        xSerienstart
                        , IF(Position2=0, Position, Position2) AS ath_pos
                        , IF(Rang=0,999999,Rang) AS ath_rank
                        , IF(Rang=0,'',CONCAT(Rang,'.')) AS ath_rank_out
                        , Startnummer AS ath_bib
                        , Bemerkung AS ath_remark
                        , athlet.Name AS ath_name
                        , athlet.Vorname AS ath_firstname
                        , athlet.Jahrgang AS ath_yob
                        , verein.Name AS ath_club
                        ".$select_result."
                    FROM serienstart
                        LEFT JOIN serie USING(xSerie)
                        LEFT JOIN start USING(xStart) 
                        LEFT JOIN anmeldung USING(xAnmeldung) 
                        LEFT JOIN athlet USING(xAthlet)
                        LEFT JOIN verein USING(xVerein) 
                        LEFT JOIN resultat USING(xSerienstart)
                    WHERE 
                        ".$where."
                        ".$and."
                    ORDER BY
                        ".$order.";";
        $query_ath = $glb_connection_server->prepare($sql_ath);
        
        if($athlete > 0) {
            $query_ath->bindValue(':serienstart', $athlete);    
        } elseif($all) {
            $query_ath->bindValue(':runde', getRound(CFG_CURRENT_EVENT)); 
        } else{
            $query_ath->bindValue(':serie', CFG_CURRENT_EVENT);
        }
        if($maxRang > 0) {
            $query_ath->bindValue(':rang', $maxRang);    
        }
        
        
        $query_ath->execute();
        if($singleRow){
            $ath = $query_ath->fetch(PDO::FETCH_ASSOC);
        } else {
            $ath = $query_ath->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $ath;            
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }     
}

function getAthleteResults($athlete) {
    global $glb_connection_server;
    
    try {
        $sql_res = "SELECT xResultat, Leistung AS ath_res
                        , Info AS ath_wind
                        , xSerienstart
                    FROM resultat
                    WHERE xSerienstart = :serienstart
                    ORDER BY xResultat;";
        $query_res = $glb_connection_server->prepare($sql_res);
        
        $query_res->bindValue(':serienstart', $athlete);
        
        $query_res->execute();
        $athres = $query_res->fetchAll(PDO::FETCH_ASSOC);
        
        return $athres;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}
    
function getMaxResult($athlete) {
    global $glb_connection_server;
    
    try{
        $sql_res = "SELECT DISTINCT
                        IF((SELECT COUNT(Leistung) FROM resultat WHERE xSerienstart = :serienstart AND Leistung IN (-1,-3)),IFNULL(MIN(SELECT COALESCE(Leistung) FROM resultat WHERE xSerienstart = :serienstart AND Leistung IN (-1,-3)),''),IFNULL(MAX(Leistung),''))AS ath_res
                    FROM
                        resultat 
                    WHERE xSerienstart = :serienstart;";
        $query_res = $glb_connection_server->prepare($sql_res)              ;
        
        $query_res->bindValue(':serienstart', $athlete);
        
        $query_res->execute();
        $res = $query_res->fetch(PDO::FETCH_ASSOC);
        
        return $res;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function getResult($result) {
    global $glb_connection_server;
    
    try{
        $sql_res = "SELECT 
                        xSerienstart
                        , Leistung AS result
                        , Info AS wind
                    FROM
                        resultat 
                    WHERE xResultat = :resultat;";
        $query_res = $glb_connection_server->prepare($sql_res)              ;
        
        $query_res->bindValue(':resultat', $result);
        
        $query_res->execute();
        $res = $query_res->fetch(PDO::FETCH_ASSOC);
        
        return $res;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function formatResultOutput($result, $format = "RANKING"){
    global $lg;    
    global $glb_results_meter_separator;
    global $glb_invalid_attempt;

    if($result > 0)
    {
        $m = (int) ($result / 100);        // calculate meters
        $cm = $result % 100;    // keep remainder

        $result = $m . $glb_results_meter_separator;
        if($cm < 10) {
            $result = $result . "0";
        }
        $result = $result . $cm;
    }    
    else if($result)  {
        $result = $lg['RESULT_INVALID_'.$glb_invalid_attempt[$result].'_'.$format];
    } else {
        $result = "";
    }
    return $result;
}

function formatResultInput($result) {  
    global $lg;
    global $glb_results_meter_separator;
    global $glb_invalid_attempt, $glb_invalid_attempt_input;
    
    $result = strtoupper($result);
         
    if(is_numeric($result) && $result > 0) { 
        if(substr_count($result,'.') == 0) {
            $m = (int) ($result / 100);        // calculate meters
            $cm = $result % 100;    // keep remainder

            $result = $m . $glb_results_meter_separator;
            if($cm < 10) {
                $result = $result . "0";
            }
            $result = $result . $cm;
        } else{
            $result = number_format($result, 2);
        }    
    }else if(array_key_exists($result,$glb_invalid_attempt_input))  {
        $result = $lg['RESULT_INVALID_'.$glb_invalid_attempt[$glb_invalid_attempt_input[$result]].'_DB'];
    } else if(array_key_exists($result,$glb_invalid_attempt)) {
        $result = $lg['RESULT_INVALID_'.$glb_invalid_attempt[$result].'_DB'];
    } else {
        $result = 'error';
    }
    return $result;
}

function formatResultDB($result) {
    global $glb_invalid_attempt, $glb_invalid_attempt_input;
    if(is_numeric($result) && $result>0) {
        if(substr_count($result,'.') > 0) {
            $result = $result*100;
        }
    } else {
        if(array_key_exists(strtoupper($result),$glb_invalid_attempt_input)) {
            $result = $glb_invalid_attempt_input[strtoupper($result)];
        } else if(array_key_exists($result,$glb_invalid_attempt)) {
            $result = $result;
        } else {
            $result = 'error';
        }
    }
    return $result;
}

function formatWindInput($wind) {
    global $glb_invalid_attempt, $glb_invalid_attempt_input;
    global $cfgResultsSepTrans, $cfgResultsSeparator, $cfgResultsWindSeparator, $cfgResultsWindDefault;
    
    if($wind == $cfgResultsWindDefault){
        return $cfgResultsWindDefault;
    } elseif (is_numeric($wind)) {
        // remove any spaces and + signs
        $wind = trim($wind);
        if(substr($wind,0,1) == "-"){
            $wind = "-".trim(substr($wind,1));
        }elseif(substr($wind,0,1) == "+"){
            $wind = trim(substr($wind,1));
        }
        
        // format wind value: replace all separators by point
        $wind = strtr($wind, $cfgResultsSepTrans);
        
        // if strlen is longer or equal 2 and there are no separators
        if(strlen($wind) >= 2 && strpos($wind, $cfgResultsSeparator) === false){
            if(substr($wind,0,1) == "-"){
                $wind = substr($wind,0,2). $cfgResultsSeparator .substr($wind,2);
            }else{
                $wind = substr($wind,0,1). $cfgResultsSeparator .substr($wind,1);
            }
        }
        
        // tokenize wind
        $tok = strtok($wind, $cfgResultsSeparator);
        $i=0;
        $num = TRUE;
        while ($tok != '') {
            if(!is_numeric($tok)) {
                $num = FALSE;
            }
            $t[$i] = $tok;    
            $tok = strtok($cfgResultsSeparator);
            $i++;
        }

        $wind = "0" . $cfgResultsWindSeparator . "0";

        if($num == TRUE)
        {
            switch(count($t)) // nbr of time elements
            {
            case 1:     // meters per second entered    
                $wind = $t[0] . $cfgResultsWindSeparator . "0";
                break;
            case 2:     // meters and centimeters per second entered    
                $wind = $t[0] . $cfgResultsWindSeparator . $t[1];
                $wind = (ceil($wind*10)/10); // round hundert fraction
                $wind = sprintf("%01.1f", $wind);
                break;
            default:    
                $wind = "0" . $cfgResultsWindSeparator . "0";
                break;
            }
        }
        return $wind; 
    } elseif($wind == "") {
        return "";
    } else{
        return 'error';
    }
}

function formatWindDB($result, $wind) {
    global $glb_invalid_attempt, $glb_invalid_attempt_input;
    global $cfgResultsSepTrans, $cfgResultsSeparator, $cfgResultsWindSeparator;
    
    if(array_key_exists(strtoupper($result),$glb_invalid_attempt_input) || array_key_exists($result,$glb_invalid_attempt)) {
        $wind = '-';
    }
    if($wind == "-"){
        return "-";
    } elseif ($wind != "") {
        // remove any spaces and + signs
        $wind = trim($wind);
        if(substr($wind,0,1) == "-"){
            $wind = "-".trim(substr($wind,1));
        }elseif(substr($wind,0,1) == "+"){
            $wind = trim(substr($wind,1));
        }
        
        // format wind value: replace all separators by point
        $wind = strtr($wind, $cfgResultsSepTrans);
        
        // if strlen is longer or equal 2 and there are no separators
        if(strlen($wind) >= 2 && strpos($wind, $cfgResultsSeparator) === false){
            if(substr($wind,0,1) == "-"){
                $wind = substr($wind,0,2). $cfgResultsSeparator .substr($wind,2);
            }else{
                $wind = substr($wind,0,1). $cfgResultsSeparator .substr($wind,1);
            }
        }
        
        // tokenize wind
        $tok = strtok($wind, $cfgResultsSeparator);
        $i=0;
        $num = TRUE;
        while ($tok != '') {
            if(!is_numeric($tok)) {
                $num = FALSE;
            }
            $t[$i] = $tok;    
            $tok = strtok($cfgResultsSeparator);
            $i++;
        }

        $wind = "0" . $cfgResultsWindSeparator . "0";

        if($num == TRUE)
        {
            switch(count($t)) // nbr of time elements
            {
            case 1:     // meters per second entered    
                $wind = $t[0] . $cfgResultsWindSeparator . "0";
                break;
            case 2:     // meters and centimeters per second entered    
                $wind = $t[0] . $cfgResultsWindSeparator . $t[1];
                $wind = (ceil($wind*10)/10); // round hundert fraction
                $wind = sprintf("%01.1f", $wind);
                break;
            default:    
                $wind = "0" . $cfgResultsWindSeparator . "0";
                break;
            }
        }
        return $wind; 
    } else {
        return "";
    }
}

function fillWind($result) {
    global $glb_invalid_attempt, $glb_invalid_attempt_input;
    global $cfgResultsWindDefault;
    
    $result = strtoupper($result);
    
    if(array_key_exists($result, $glb_invalid_attempt) || array_key_exists($result, $glb_invalid_attempt_input)) {
        return $cfgResultsWindDefault;
    } else {
        return "";
    }
}

function checkFinal($event) {
    global $glb_connection_server;
    global $glb_results_skip;
    global $cfgEvalType, $strEvalTypeHeat;
    
    $final_athletes = 0;
    
    try {
        $eval = getEvaluationType($event);
        
        if($eval != $cfgEvalType[$strEvalTypeHeat]) {   
            $sql_final =    "SELECT
                                Endkampf
                                , Finalisten
                                , FinalNach
                            FROM runde
                                LEFT JOIN serie USING(xRunde)
                            WHERE xSerie = :serie;";
            $query_final = $glb_connection_server->prepare($sql_final);
            
            $query_final->bindValue(':serie', $event);
            
            $query_final->execute();
            
            $final = $query_final->fetch(PDO::FETCH_ASSOC);
            
            $final_athletes = 0;
            
            $present = getNofAthletes($event);
            if($present > $final['Finalisten']) {                       
                if($final['Endkampf'] == 1){
                    
                    $attempt = getCurrentAttempt($event, 0);
                    
                    if($attempt > $final['FinalNach']) {
                       $final_athletes = $final['Finalisten'];
                    }
                }
            }
        }
    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }
    
    return $final_athletes;
    
}

function getCurrentAttempt($event, $maxRang) {
    global $glb_connection_server;
    global $glb_results_skip;
    
    try {      
        if($maxRang > 0) {  
            $and_rank = "AND Rang <= :rang";
        } else{
            $and_rank = "";
        }
        
        $sql_attempt = "    SELECT xSerienstart
                                ,COUNT(Leistung) AS results
                                , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        FROM resultat
                            LEFT JOIN serienstart USING(xSerienstart)
                        WHERE xSerie = :serie
                        ".$and_rank."
                        GROUP BY xSerienstart
                        HAVING skip = 0
                        ORDER BY results ASC
                        LIMIT 1;";
                        
        $query_attempt = $glb_connection_server->prepare($sql_attempt);
    
        $query_attempt->bindValue(':serie', $event);
        if($maxRang > 0) {
            $query_attempt->bindValue(':rang', $maxRang);
        }
        $query_attempt->execute();
            
        if($query_attempt->rowCount() > 0) {
            $attempts = $query_attempt->fetch(PDO::FETCH_ASSOC);   
            $attempt = $attempts['results'] + 1; 
        } else {
            $attempt = 1;
        }
        
        return $attempt;
        
        
    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }
}

function dropPosition($event, $maxRang) {
    global $glb_connection_server;
    
    try{
        if($maxRang > 0) {  
            $and_rank = "AND Rang <= :rang AND Rang > 0";
        } else{
            $and_rank = "";
        }
        
        $sql_drop = "SELECT 
                        xSerienstart
                        , IF(Rang > 0, Rang, 9999) As rang_sort
                        , IF((SELECT COUNT(*) 
                                FROM
                                    resultat 
                                WHERE xSerienstart = serienstart.xSerienstart 
                                    AND Leistung IN (-1,-3)),
                            1,0) AS skip 
                    FROM
                        serienstart 
                        LEFT JOIN resultat USING (xSerienstart) 
                        LEFT JOIN serie USING(xSerie)
                    WHERE xRunde = :runde 
                        ".$and_rank."
                    GROUP BY xSerienstart
                    
                    ORDER BY rang_sort DESC, Position ASC;";
                    
        $query_drop = $glb_connection_server->prepare($sql_drop);
    
        $query_drop->bindValue(':runde', getRound($event));
        if($maxRang > 0) {
            $query_drop->bindValue(':rang', $maxRang);
        }
        $query_drop->execute();
        
        $athletes = $query_drop->fetchAll(PDO::FETCH_ASSOC);
        
        $pos = 1;
        foreach($athletes as $athlete) {
            if($athlete['skip']==0){
                $pos_db = $pos;
                $pos++;
            }else {
                $pos_db = 999999;
            }
                    
            $sql_update = "UPDATE serienstart
                            SET Position2 = :position
                            WHERE xSerienstart = :serienstart;";
            $query_update = $glb_connection_server->prepare($sql_update);

            // +++ bind parameters
            $query_update->bindValue(':position', $pos_db);
            $query_update->bindValue(':serienstart', $athlete['xSerienstart']);
            // --- bind parameters

            $query_update->execute();    
        }

    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }
}

function resetPosition($event) {
    global $glb_connection_server;
    
    try{

        $sql_drop = "SELECT 
                        xSerienstart
                        , Position
                    FROM
                        serienstart 
                    WHERE xSerie = :serie;";
                    
        $query_drop = $glb_connection_server->prepare($sql_drop);
    
        $query_drop->bindValue(':serie', $event);

        $query_drop->execute();
        
        $athletes = $query_drop->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($athletes as $athlete) {
                    
            $sql_update = "UPDATE serienstart
                            SET Position2 = :position
                            WHERE xSerienstart = :serienstart;";
            $query_update = $glb_connection_server->prepare($sql_update);

            // +++ bind parameters
            $query_update->bindValue(':position', 0);
            $query_update->bindValue(':serienstart', $athlete['xSerienstart']);
            // --- bind parameters

            $query_update->execute();    
        }

    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }
}

function checkDrop($event, $maxRang) {
    global $glb_connection_server;
    global $glb_results_skip;
    global $cfgEvalType, $strEvalTypeHeat;
    
    $drop = false;
    
    try {      
        
        $eval = getEvaluationType($event);
        
        if($eval == $cfgEvalType[$strEvalTypeHeat]) {   
            return $drop;
        }

        if($maxRang > 0) {  
            $and_rank = "AND Rang <= :rang";
        } else{
            $and_rank = "";
        }
        
        $sql_attempts = "SELECT xSerienstart
                                ,COUNT(Leistung) AS results
                                , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        FROM resultat
                            LEFT JOIN serienstart USING(xSerienstart)
                            LEFT JOIN serie USING(xSerie)
                        WHERE xRunde = :runde
                        ".$and_rank."
                        GROUP BY xSerienstart
                        HAVING skip = 0
                        ORDER BY results ASC;";
              
        $query_attempts = $glb_connection_server->prepare($sql_attempts);
    
        $query_attempts->bindValue(':runde', getRound($event));
        if($maxRang > 0) {
            $query_attempts->bindValue(':rang', $maxRang);
        }
        $query_attempts->execute();
        
        if($query_attempts->rowCount() > 0 ){
            $attempts = $query_attempts->fetchAll(PDO::FETCH_ASSOC); 
            $i = $attempts[0]['results'];
            
            foreach($attempts as $attempt){
                if($attempt['results'] != $i) {
                    $drop = false;
                    return $drop;
                }
                $i = $attempt['results'];
            }
            $sql_settings =    "SELECT
                                Drehen
                            FROM runde
                                LEFT JOIN serie USING(xRunde)
                            WHERE xSerie = :serie;";
            $query_settings = $glb_connection_server->prepare($sql_settings);
            
            $query_settings->bindValue(':serie', $event);
            
            $query_settings->execute();
            
            $settings = $query_settings->fetch(PDO::FETCH_ASSOC);
            
            $drop_after = explode(",",trim($settings['Drehen'],"()"));
            
            if(in_array($i, $drop_after)) {
                $drop = true;
            }
        }
        
        return $drop;
        
    }catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}
function createResultTable($event) {
    global $glb_connection, $glb_connection_server;
    global $glb_results_skip;
    
    try{        
        $settings = getTechSettings($event);
        $round = getRound($event);
        
        $sql_drop = "DROP TABLE IF EXISTS tempresult_".$round;
        
        $query_drop = $glb_connection->prepare($sql_drop);
        
        $query_drop->execute();
        
        $sql_tmp = "CREATE TABLE tempresult_".$round." (
                    xSerienstart int(11)
                    , xSerie int(11)
                    , skip tinyint(2)";

        for($i=1; $i <= $settings['round_attempts']; $i++) {
            $sql_tmp = $sql_tmp . ", Res" . $i . " int(9) default '0'";
            $sql_tmp = $sql_tmp . ", Wind" . $i . " char(5) default '0'";
        }
        $sql_tmp = $sql_tmp . ")";
        
        $query_tmp = $glb_connection->prepare($sql_tmp);
        $query_tmp->execute();
        
        $sql_ath = "SELECT xSerienstart
                        , xSerie
                        , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                    FROM serienstart
                        LEFT JOIN serie USING(xSerie)
                    WHERE xRunde = :runde   ;";
        $query_ath = $glb_connection_server->prepare($sql_ath);
        
        $query_ath->bindValue(':runde', $round);
        
        $query_ath->execute();
        $athletes = $query_ath->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($athletes as $athlete){
            $xSerienstart = $athlete['xSerienstart'];
            $xSerie = $athlete['xSerie'];
            $skip = $athlete['skip'];
            
            $sql_insert = "INSERT INTO tempresult_".$round." (xSerienstart, xSerie, skip) VALUES($xSerienstart,$xSerie, $skip)";
            $query_insert = $glb_connection->prepare($sql_insert);
            $query_insert->execute();
        
            $sql_res = "SELECT Leistung AS ath_res
                            , Info AS ath_wind
                        FROM resultat
                        WHERE xSerienstart = :serienstart
                        ORDER BY xSerienstart
                            , Leistung DESC;";
            $query_res = $glb_connection_server->prepare($sql_res);
            
            $query_res->bindValue(':serienstart', $athlete['xSerienstart']);
            
            $query_res->execute();
            $results = $query_res->fetchAll(PDO::FETCH_ASSOC);
            
            $n = 1;
            foreach($results as $result){
                $sql_update = "UPDATE tempresult_".$round." 
                                SET Res".$n."=:result
                                , Wind".$n."=:wind
                                WHERE xSerienstart = :serienstart;";
                $query_update = $glb_connection->prepare($sql_update);
                
                $query_update->bindValue(':result', $result['ath_res']);
                $query_update->bindValue(':wind', $result['ath_wind']);
                $query_update->bindValue(':serienstart', $athlete['xSerienstart']);
                
                $query_update->execute();
                $n++;
            }
        }
        
    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }   
}

function rankAthletes($event){
    global $glb_connection, $glb_connection_server;
    global $cfgEvalType, $strEvalTypeHeat;
    
    try{
        $settings = getTechSettings($event);
        $eval = getEvaluationType($event);
        $round = getRound($event);
        
        if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
            $sql_athletes = "
                SELECT
                    *
                FROM
                    tempresult_".$round."
                ORDER BY
                    xSerie";

            for($i=1; $i <= $settings['round_attempts']; $i++) {
                $sql_athletes = $sql_athletes . ", Res".$i." DESC";
            }                                                                                                     
        } else{    //rank results from all heats together
            $sql_athletes = "
                SELECT
                    *
                FROM
                    tempresult_".$round."
                ORDER BY ";
            $comma = "";
            // order by available result columns
            for($i=1; $i <= $settings['round_attempts']; $i++) {
                $sql_athletes = $sql_athletes . $comma . "Res".$i." DESC";
                $comma = ", ";
            }
        }
        $query_athletes = $glb_connection->prepare($sql_athletes);
        $query_athletes->execute();
        
        $athletes = $query_athletes->fetchAll(PDO::FETCH_ASSOC);
        
        $heat = 0;   
        $perf_old[] = '';
        $j = 1;
        $rank = 0;
        // set rank for every athlete
        foreach($athletes as $athlete){
            if($eval == $cfgEvalType[$strEvalTypeHeat] && $heat != $athlete['xSerie']){  // new heat
                $j = 1;        // restart ranking
                $perf_old[] = '';
            }
            for($i=1; $i <= $settings['round_attempts']; $i++) {
                $perf[$i] = $athlete['Res'.$i];
            }
            if($athlete['skip'] == 0 && $athlete['Res1'] > 0) { //check if DNS or DSQ   
                if($perf_old != $perf) {  // check if same performance
                    $rank = $j; 
                } 
                $rank_db = $rank;    
                $j++; // increment ranking
            }else{
                $rank_db = 0;
            } 

            $sql_update = "UPDATE serienstart SET
                                Rang = $rank_db
                            WHERE xSerienstart = :serienstart;";
                            
            $query_update = $glb_connection_server->prepare($sql_update);
            
            $query_update->bindValue(':serienstart', $athlete['xSerienstart']);
            
            $query_update->execute();        
            
            $heat = $athlete['xSerie'];        // keep current heat ID
            $perf_old = $perf;
        }
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }       
}

function resetQualification($round) {
    global $glb_connection_server;
    global $cfgQualificationType;

    try{
        if(!empty($round))
        {
            $sql = "LOCK TABLES serie AS s READ, serienstart AS ss WRITE, serie READ, serienstart WRITE;";
            
            $qry = $glb_connection_server->prepare($sql);
            $qry->execute(); 

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
             
            $qry = $glb_connection_server->prepare($sql);
            $qry->execute(); 
            $rows = $qry->fetchAll(PDO::FETCH_NUM);     

            foreach($rows as $row) {
                $sql = "UPDATE serienstart SET"
                            . " Qualifikation = 0"
                            . " WHERE xSerienstart = " . $row[0].";";
                $qry = $glb_connection_server->prepare($sql);
                $qry->execute(); 
            }
                
            $sql = "UNLOCK TABLES;";
            
            $qry = $glb_connection_server->prepare($sql);
            $qry->execute();
        }
        
    }catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}


function calcRankingPoints($round){  
    global $glb_connection_server;  
    global $strConvtableRankingPoints, $strConvtableRankingPointsU20, $cfgEventType;
    global $strEventTypeSVMNL, $strEventTypeSingleCombined, $strEventTypeClubAdvanced
        , $strEventTypeClubBasic, $strEventTypeClubTeam, $strEventTypeClubMixedTeam;
    global $cvtTable;
    
    
    try{
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
        $qry = $glb_connection_server->prepare($sql);
        $qry->execute(); 
            
        $row = $qry->fetch(PDO::FETCH_NUM);
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
                   
                $qry = $glb_connection_server->prepare($sql);
                $qry->execute(); 

            } else{               
                $sql = "
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
                    ORDER BY ss.Rang ASC;";  
                      
                $qry = $glb_connection_server->prepare($sql);
                $qry->execute();                        
            }     
            $rows = $qry->fetchAll(PDO::FETCH_NUM);
                        
            $pts = 0;    // points to share
            $rank = 1;    // current rank
            $update = array();    // holding serienstart[key] with points
            $tmp = array();    // holding temporary serienstarts
            $point = $pStart;    // current points to set
            $i = 0;    // share counter
            
            $cClubs = array(); // count athlete teams for svm mode
            
            foreach($rows as $row){
                
                if($bSVM){
                    // count athletes per club
                    if(isset($cClubs[$row[2]])){
                        $cClubs[$row[2]]++;
                    }else{
                        $cClubs[$row[2]] = 1;
                    }  
                            
                    // skip result if more than MaxRes athletes of a team are on top
                    if(isset($cClubs[$row[2]]) && $cClubs[$row[2]] > $countMaxRes){   
                                                          
                        $sql = "UPDATE resultat SET
                                Punkte = 0
                            WHERE
                                xSerienstart = $row[0];";
                                
                        $qry = $glb_connection_server->prepare($sql);
                        $qry->execute();
                        
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
                $sql = "UPDATE resultat SET
                        Punkte = $p
                    WHERE
                        xSerienstart = $key;";
                        
                $qry = $glb_connection_server->prepare($sql);
                $qry->execute();
                
                 AA_StatusChanged($key);                    
            }
        } // endif $valid
        
    }catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function calcPoints($event, $perf, $fraction = 0, $sex = 'M', $startID){  
    global $glb_connection_server;
    global $cvtTable, $cfgDisciplineType, $strDiscTypeTrack, $strDiscTypeTrackNoWind, $strDiscTypeRelay, $strDiscTypeDistance, $cvtFormulas;
    
    try{       
        // check if this is a merged round   (important for calculate points for merged round with different sex)
        $sql = "SELECT                                           
                    se.RundeZusammen                       
                FROM
                    serienstart as se                     
                WHERE 
                    se.xSerienstart = " .$startID;
        $qry = $glb_connection_server->prepare($sql);
        $qry->execute();    
        $row = $qry->fetch(PDO::FETCH_NUM);
           
        if ($row[0] > 0) {                  // merged round exist
        
            // get event to the merged round
            $sql="SELECT
                ru.xWettkampf                      
            FROM
                runde as ru                    
            WHERE 
                ru.xRunde = " .$row[0];
            $qry = $glb_connection_server->prepare($sql);
            $qry->execute();    

            if ($qry->rowCount() > 0){   
                $row = $qry->fetch(PDO::FETCH_NUM);                        
                $event=$row[0];   
            } 
        }
    
             
        global $strConvtableRankingPoints;
        global $strConvtableRankingPointsU20; 
        
        $points = 0;
        if($perf > 0)
        {
            // get formula to calculate points from performance     
            $sql= "SELECT
                    d.Typ 
                    , w.Punktetabelle
                    , w.Punkteformel
                    , d.xDisziplin
                FROM
                    disziplin_".strtolower(CFG_CURRENT_LANGUAGE)." As d
                    LEFT JOIN wettkampf AS w ON (d.xDisziplin = w.xDisziplin)
                WHERE                          
                     w.xWettkampf = $event     
                    AND w.Punktetabelle > 0
                    AND (w.Punkteformel != '0'
                        OR (w.Punkteformel = '0' 
                    AND w.Punktetabelle >= 100))";     
            
            $qry = $glb_connection_server->prepare($sql);
            $qry->execute();   
            
            if ($qry->rowCount() > 0)    // event has formula assigned
            {
                
                $row = $qry->fetch(PDO::FETCH_NUM);
                
                // if ranking points are set, return
                if($row[1] == $cvtTable[$strConvtableRankingPoints] || $row[1] == $cvtTable[$strConvtableRankingPointsU20]){
                    return 0;
                }

                // track disciplines: performance in 1/100 sec
                if($row[1]<100 && ($row[0] == $cfgDisciplineType[$strDiscTypeTrack]
                                  || $row[0] == $cfgDisciplineType[$strDiscTypeTrackNoWind]
                                  || $row[0] == $cfgDisciplineType[$strDiscTypeRelay]
                                  || $row[0] == $cfgDisciplineType[$strDiscTypeDistance]))
                {
                    $perf = ceil($perf/10);
                }
                
                // own score table
                $test = 0;
                if($row[1] >= 100){
                    
                    $operator = '>=';
                    $sort = 'ASC';
                    if(($row[0] == $cfgDisciplineType[$strDiscTypeTrack]
                      || $row[0] == $cfgDisciplineType[$strDiscTypeTrackNoWind]
                      || $row[0] == $cfgDisciplineType[$strDiscTypeRelay]
                      || $row[0] == $cfgDisciplineType[$strDiscTypeDistance]))
                        {
                        $operator = '>=';
                        $sort = 'ASC';
                    }
                    else {
                        $operator = '<=';
                        $sort = 'DESC';
                    }
                     
                    $sqlpt = "SELECT Punkte 
                                FROM wertungstabelle_punkte 
                               WHERE xWertungstabelle = ".$row[1]." 
                                 AND xDisziplin = ".$row[3]." 
                                 AND Leistung ".$operator." ".$perf." 
                                 AND Geschlecht = '".$sex."'
                            ORDER BY Leistung ".$sort." 
                               LIMIT 1;";
                    $qrypt = $glb_connection_server->prepare($sqlpt);
                    $qrypt->execute();
                    
                    $datei = fopen('test.txt', 'w+');
                    fwrite($datei, $sqlpt);
                    fclose($datei);
                    
                    if($qrypt->rowCount() > 0){
                        $points = $qrypt->fetch(PDO::FETCH_NUM);
                        $points = $points[0];
                    }
                    
                    $testdatei = fopen('test.txt', 'w+');
                    fwrite($testdatei, $sqlpt);
                    fclose($testdatei);                    
                } else {    
                    // split formula into parameters
                    $params = explode(" ", $cvtFormulas[$row[1]][$row[2]]);
                   
                    // formula types
                    $A = $params[1];
                    $B = $params[2];
                    $C = $params[3];
                    
                    switch ($params[0]) {
                        // points = A * ((B - perf) / 100) ^ C, fractions are rounded down
                        case 1:
                            $points = floor($A * (pow(($B-$perf)/100, (float)$C)));    
                            break;
                        // points = A * ((perf - B) / 100) ^ C, fractions are rounded down
                        case 2:
                            $points = floor($A * (pow(($perf-$B)/100, (float)$C)));    
                            break;
                        // points = A * (perf - B) ^ C, fractions are rounded down
                        case 3:
                            $points = floor($A * (pow($perf-$B, (float)$C)));
                            break;
                        // points = A * ((B - perf/100)^2) + C, fractions rounded down
                        // (unused)
                        case 4:
                            $points = floor( $A * pow($B - ($perf/100), 2) - $C);
                            break;
                        // points = A * (perf/100 + B)^2 - C, fractions rounded down
                        case 5:
                            $points = floor( $A * pow(($perf/100)+$B, 2) - $C);
                            break;
                        default:
                            
                    }        // end switch params[]
                }
            }        // ET event with formula
        }        // ET performance provided
        
        if(is_nan($points) || $points<0){ // prevent wrong or negative points for "to bad" performances
            $points = 0;
        }
    }catch(PDOException $e){
        trigger_error($e->getMessage());
    }
    
    return $points;
}
?>