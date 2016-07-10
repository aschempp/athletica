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
    
function getAthletes($event, $xSerienstart = 0) {
    global $glb_connection_server;
    global $glb_results_skip;

    try {
        if($xSerienstart == 0) {
            $and_id = "";
        } else{
            $and_id = " AND xSerienstart = :serienstart";
        }
        
        $sql_res = "SELECT xSerienstart AS ath_id
                        , Position AS ath_pos
                        , Starthoehe AS ath_start
                        , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        FROM serienstart  
                            LEFT JOIN resultat USING(xSerienstart) 
                        WHERE xSerie = :serie 
                        ".$and_id."
                        GROUP BY xSerienstart 
                        ORDER BY Position ASC;";
        $query_res = $glb_connection_server->prepare($sql_res);
        
        $query_res->bindValue(':serie', $event);
        
        if($xSerienstart != 0) {
            $query_res->bindValue(':serienstart', $xSerienstart);
        }
        
        $query_res->execute();
        
        if($xSerienstart == 0) {
            $return = $query_res->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
            $return = array_map('reset', $return);
        } else {
            $return = $query_res->fetch(PDO::FETCH_ASSOC);
        }
        
        return $return;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }       
}

function getCurrentAthlete($event) {
    global $glb_connection;
    
    try{
        $round = getRound($event);
        $curr = array();
        
        $sql_curr = "SELECT xSerienstart AS ath_id
                            , curr_height
                            , curr_miss
                            , position AS ath_pos
                            FROM tempresult_".$round."
                            WHERE skip = 0
                                AND ath_out = 0
                                AND xSerie = $event
                            ORDER BY curr_height
                                , curr_miss
                                , ath_pos
                            LIMIT 1;";   
                            
        $query_curr = $glb_connection->prepare($sql_curr);
        $query_curr->execute();
        if($query_curr->rowCount() > 0){
            $curr = $query_curr->fetch(PDO::FETCH_ASSOC);
        }
        
        
        return $curr;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }     
}

function getCurrentResult($athlete, $height){
    global $glb_connection_server;
    
    try{
        $sql_get = "SELECT xResultat AS res_id
                        , Info AS ath_res
                        FROM resultat
                        WHERE xSerienstart = :athlete
                            AND Leistung = :height;";
        $query_get = $glb_connection_server->prepare($sql_get);
        $query_get->bindValue(':athlete', $athlete);
        $query_get->bindValue(':height', $height);
        $query_get->execute();
        
        $res = $query_get->fetch(PDO::FETCH_ASSOC);
        return $res;
        
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
    
    
}


function getNextAthlete($athletes, $current, $event) {
    global $glb_connection;
    
    try{     
        $round = getRound($event);   
        $next = array();
        $sql_next = "SELECT xSerienstart AS ath_id
                            , curr_height
                            , curr_miss
                            , position AS ath_pos
                            FROM tempresult_".$round."
                            WHERE skip = 0
                                AND ath_out = 0
                                AND xSerienstart <> :curr_id
                                AND xSerie = :event
                            ORDER BY curr_height
                                , curr_miss
                                , ath_pos
                            LIMIT 1;";   
        $query_next = $glb_connection->prepare($sql_next);
        $query_next->bindValue(':curr_id', $current['ath_id']);
        $query_next->bindValue(':event', $event);
        $query_next->execute();
        if($query_next->rowCount() > 0){
            $next = $query_next->fetch(PDO::FETCH_ASSOC);
        } else{
            $next = $current;
        }
        
        return $next;
        
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

function getAthleteDetails($athlete = 0, $result = true, $order = 'ath_pos', $maxRang = 0, $singleRow = false, $all = false) {
    global $glb_connection_server;
    global $glb_results_skip;
    global $glb_high_attempt_passed, $glb_high_attempt_failed;
    global $glb_invalid_attempt_input;
    
    try{
        if($athlete > 0) {
            $where = 'xSerienstart = :serienstart';
        } elseif($all){
            $where = 'xRunde = :runde';
        } else{
            $where = 'xSerie = :serie';
        }
        
        if($maxRang > 0) {
            $and = ' AND Rang <= :rang';
        } else {
            $and = '';
        }
        
        if($result) {
            $select_result = ", IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")),IFNULL((SELECT COALESCE(Leistung) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).") LIMIT 1),''),IFNULL((SELECT MAX(Leistung) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Info LIKE '%".$glb_high_attempt_passed."%'),IF((SELECT COUNT(Leistung) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Info LIKE '%".$glb_high_attempt_failed."%'),'".$glb_invalid_attempt_input[$glb_high_attempt_failed]."',''))) AS ath_res";
        } else {
            $select_result = "";
        }
        
        $sql_ath = "SELECT DISTINCT
                        xSerienstart AS ath_id
                        , Starthoehe AS ath_start
                        , Bemerkung AS ath_remark
                        , IF(Position=0,999999,Position) AS ath_pos
                        , IF(Rang=0,999999,Rang) AS ath_rank
                        , IF(Rang=0,'',CONCAT(Rang,'.')) AS ath_rank_out
                        , Startnummer AS ath_bib
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

function checkFirstAttempt($athlete, $event){
    global $glb_connection;
    
    try{
        $round = getRound($event);
        $sql_check = "SELECT result
                        , miss
                        FROM tempresult_".$round."
                        WHERE xSerienstart = :serienstart;";
        $query_check = $glb_connection->prepare($sql_check);
        $query_check->bindValue(':serienstart', $athlete);
        $query_check->execute();
        
        $check = $query_check->fetch(PDO::FETCH_ASSOC);
        
        if($check['result'] == 0 && $check['miss'] == 0){
            return true;
        }
        else{
            return false;
        }
        
    }catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function getMaxHeightID(){
    global $glb_connection;
    
    try{
        $sql_get = "SELECT IFNULL(MAX(xHeight), 0) AS max_id
                        FROM t_heights;";
        $query_get = $glb_connection->prepare($sql_get);
        $query_get->execute();
        $max_id = $query_get->fetch(PDO::FETCH_ASSOC);
        $return = $max_id['max_id'] + 1;
        
        return $return;

    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function getAthleteResults($athlete, $height = 0) {
    global $glb_connection_server;
    
    try {
        if($height > 0) {
            $and = " AND Leistung = :height";
        } else{
            $and = "";
        }
        $sql_res = "SELECT xResultat AS res_id
                        , Leistung AS ath_res
                        , Info AS ath_info
                        , xSerienstart
                    FROM resultat
                    WHERE xSerienstart = :serienstart
                    ".$and."
                    ORDER BY xResultat;";
        $query_res = $glb_connection_server->prepare($sql_res);
        
        $query_res->bindValue(':serienstart', $athlete);
        if($height > 0) {
            $query_res->bindValue(':height', $height);
        }
        
        $query_res->execute();
        if($height == 0){
            $athres = $query_res->fetchAll(PDO::FETCH_ASSOC);
        } else{
            if($query_res->rowCount() > 0){
                $athres = $query_res->fetch(PDO::FETCH_ASSOC);
            } else{
                $athres = "";
            }
        }
        
        return $athres;
        
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
                        , Info AS info
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

function formatHeightInput($result) {  
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

function formatResultInput($result) {  
    global $lg;
    global $glb_results_meter_separator;
    global $glb_invalid_attempt, $glb_invalid_attempt_input;
    global $glb_high_attempt_input;
    global $glb_high_attempt_waived, $glb_high_attempt_passed;
    global $glb_results_skip;
    
    if($result < 0) {
        if(in_array($result, $glb_results_skip)) {
            return $result;
        } else{
            return 'error';
        }    
    }
    
    $result = strtoupper($result);
    
    $result_arr = str_split($result);
    $result_out = "";
    foreach($result_arr as $res){
         
        if(array_key_exists($res, $glb_high_attempt_input)) { 
            $result_out .= $glb_high_attempt_input[$res];
            $res = $glb_high_attempt_input[$res];   
        }else if(array_key_exists($res, $glb_invalid_attempt_input))  {
            $result_out .= $lg['RESULT_INVALID_'.$glb_invalid_attempt[$glb_invalid_attempt_input[$res]].'_DB'];
        } else if(array_key_exists($res,$glb_invalid_attempt)) {
            $result_out .= $lg['RESULT_INVALID_'.$glb_invalid_attempt[$res].'_DB'];
        } else if(!$res) {
            $result_out .= "";
        } else {
            return 'error';
        }
        if($res == $glb_high_attempt_waived || $res == $glb_high_attempt_passed || strlen($result_out) > 3){
            return $result_out;    
        }
    }
    return $result_out;
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

function createHeightTable($event) {
    global $glb_connection, $glb_connection_server;
    
    try{       
        $settings = getHighSettings($event);
             
        $sql_start = "SELECT MIN(Starthoehe) AS start_min
                        , MAX(Starthoehe) AS start_max
                    FROM serienstart
                    WHERE xSerie = :serie
                        AND Starthoehe <> 0;";
        $query_start = $glb_connection_server->prepare($sql_start);
        
        $query_start->bindValue(':serie', $event);
        
        $query_start->execute();
        $start = $query_start->fetch(PDO::FETCH_ASSOC);
        
        for($height = $start['start_min']; $height <= $start['start_max']; ){
            $height_id = getMaxHeightID();
            $sql_insert = "INSERT IGNORE INTO t_heights (xHeight, serie, height) VALUES(:height_id, :serie, :height);";
            $query_insert = $glb_connection->prepare($sql_insert);
            
            $query_insert->bindValue(':height_id', $height_id);
            $query_insert->bindValue(':serie', $event);
            $query_insert->bindValue(':height', $height);
            
            $query_insert->execute();
            
            if($height < $settings['diff_1_until']) {
                $diff = $settings['diff_1_value'];
            } elseif($height < $settings['diff_2_until']){
                $diff = $settings['diff_2_value'];
            } else{
                $diff = $settings['diff_3_value'];
            }    
            $height = $height + $diff;
        }
            
        $sql_heights = "SELECT 
                Leistung AS height
                FROM resultat
                    LEFT JOIN serienstart USING(xSerienstart)
                WHERE xSerie = :serie
                    AND Leistung > 0;";
        $query_heights = $glb_connection_server->prepare($sql_heights);
        
        $query_heights->bindValue(':serie', $event);
        
        $query_heights->execute();
        $heights = $query_heights->fetchAll(PDO::FETCH_ASSOC);
        foreach($heights as $height) {
            $height_id = getMaxHeightID();
            $sql_insert = "INSERT IGNORE INTO t_heights (xHeight, serie, height) VALUES(:height_id, :serie, :height);";
            $query_insert = $glb_connection->prepare($sql_insert);
            
            $query_insert->bindValue(':height_id', $height_id);
            $query_insert->bindValue(':serie', $event);
            $query_insert->bindValue(':height', $height['height']);
            
            $query_insert->execute();
        }
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }    
}

function getHeights($event){
    global $glb_connection;
    
    try{
        $heights = array();
        
        $sql_heights = "SELECT *
                            FROM t_heights
                            WHERE serie = :serie
                            ORDER BY height;";
        $query_heights = $glb_connection->prepare($sql_heights);
        $query_heights->bindValue(':serie', $event);
        $query_heights->execute();
        
        $heights = $query_heights->fetchAll(PDO::FETCH_ASSOC);
        
        return $heights;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function checkStartheightsComplete($event) {
    global $glb_connection_server;
    global $glb_results_skip;
    
    try{
        $sql_check = "SELECT Starthoehe
                            , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        FROM serienstart
                            LEFT JOIN resultat USING(xSerienstart)
                        WHERE xSerie = :serie
                            AND Starthoehe = 0
                        HAVING skip = 0;";
        $query_check = $glb_connection_server->prepare($sql_check);
        $query_check->bindValue(':serie', $event);
        $query_check->execute();
        
        if($query_check->rowCount() > 0){
            return false;
        } else{
            return true;
        }
        
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function checkStartHeight($height, $event){
        
    try{
        $settings = getHighSettings($event);

        if($height <= $settings['diff_1_until']){
            if(($settings['diff_1_until'] - $height) % $settings['diff_1_value'] == 0){
                return true;
            }
        } elseif($height <= $settings['diff_2_until']){
            if(($height - $settings['diff_1_until']) % $settings['diff_2_value'] == 0){
                return true;
            }
        } else{
            if(($height - $settings['diff_2_until']) % $settings['diff_3_value'] == 0){
                return true;
            }
        }
        
        return false;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function saveStartHeight($athlete, $height){
    global $glb_connection_server;
    
    try{
        if(isset($height)) {
            $sql_save = "UPDATE serienstart
                            SET Starthoehe = :height
                            WHERE xSerienstart = :athlete;";
            $query_save = $glb_connection_server->prepare($sql_save);
            
            $query_save->bindValue(':height', $height);                                                    
            $query_save->bindValue(':athlete', $athlete);     
            
            $query_save->execute();
        }
        return 'ok';
                
    } catch(PDOException $e){
        trigger_error($e->getMessage());
        return 'error';
    }
    
}

function insertHeight($height, $event){
    global $glb_connection;
    
    try{
        if($height) {          
            if($height > 0){
                $height_id = getMaxHeightID();
                $sql_insert = "INSERT IGNORE INTO t_heights (xHeight, serie, height) VALUES(:height_id, :serie, :height);";
                $query_insert = $glb_connection->prepare($sql_insert);
                
                $query_insert->bindValue(':height_id', $height_id);
                $query_insert->bindValue(':serie', $event);
                $query_insert->bindValue(':height', $height);
                
                $query_insert->execute();
            }
        }
        return 'ok';
                
    } catch(PDOException $e){
        trigger_error($e->getMessage());
        return 'error';
    }
}

function deleteHeight($height_id){
    global $glb_connection;
    
    try{
        if($height_id > 0){
            $sql_insert = "DELETE 
                                FROM t_heights
                                WHERE xHeight = :height_id;";
            $query_insert = $glb_connection->prepare($sql_insert);
            
            $query_insert->bindValue(':height_id', $height_id);            
            $query_insert->execute();
        }
        
        return 'ok';
                
    } catch(PDOException $e){
        trigger_error($e->getMessage());
        return 'error';
    }
    
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

function checkOut($athlete){
    global $glb_connection_server;
    global $glb_high_attempt_failed, $glb_high_attempt_passed;
    
    try{
        $sql_results = "SELECT Leistung 
                            , Info
                            FROM resultat
                            WHERE xSerienstart = :athlete
                            ORDER BY Leistung;";
        $query_results = $glb_connection_server->prepare($sql_results);
        $query_results->bindValue(':athlete', $athlete);
        $query_results->execute();
        
        $results = $query_results->fetchAll(PDO::FETCH_ASSOC);
        
        $x = 0;
        foreach($results as $result){
            $x = $x + substr_count($result['Info'], $glb_high_attempt_failed);
            if($x>=3){
                return 1;
            }
            if(substr_count($result['Info'], $glb_high_attempt_passed) > 0){
                $x = 0;
            }
        }
        return 0;
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function createResultTable($event) {
    global $glb_connection, $glb_connection_server;
    global $glb_results_skip;
    global $glb_high_attempt_passed, $glb_high_attempt_failed, $glb_high_attempt_waived;
    
    try{                   
        $settings = getHighSettings($event);
        $round = getRound($event);
        
        $sql_drop = "DROP TABLE IF EXISTS tempresult_".$round;
        
        $query_drop = $glb_connection->prepare($sql_drop);
        
        $query_drop->execute();
        
        $sql_tmp = "CREATE TABLE tempresult_".$round." (
                    xSerienstart int(11)
                    , xSerie int(11)
                    , position int(11)
                    , skip tinyint(2)
                    , result int(11)
                    , result_attempts int(11)
                    , miss int(11)
                    , ath_out int(11)
                    , curr_height int(11)
                    , curr_miss int(11));";

        $query_tmp = $glb_connection->prepare($sql_tmp);
        $query_tmp->execute();
        
        $sql_ath = "SELECT xSerienstart AS ath_id
                        , xSerie
                        , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        , Starthoehe AS ath_start
                        , Position AS ath_pos
                    FROM serienstart
                        LEFT JOIN serie USING(xSerie)
                    WHERE xRunde = :runde;";
        $query_ath = $glb_connection_server->prepare($sql_ath);
        
        $query_ath->bindValue(':runde', $round);
        
        $query_ath->execute();
        $athletes = $query_ath->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($athletes as $athlete){
            $xSerienstart = $athlete['ath_id'];
            $position = $athlete['ath_pos'];
            $xSerie = $athlete['xSerie'];
            $skip = $athlete['skip'];
        
            $sql_res = "SELECT 
                            xSerienstart
                            , Leistung AS result_max
                            , Info AS result_info
                        FROM
                            resultat 
                            LEFT JOIN serienstart USING (xSerienstart) 
                        WHERE xSerienstart = :serienstart 
                            AND Info LIKE '%".$glb_high_attempt_passed."%'
                        ORDER BY Leistung DESC
                        LIMIT 1;";
            $query_res = $glb_connection_server->prepare($sql_res);
            
            $query_res->bindValue(':serienstart', $xSerienstart);
            
            $query_res->execute();
            $result = $query_res->fetch(PDO::FETCH_ASSOC);
            $result_res = ($result['result_max']) ? $result['result_max'] : 0;

            $res_attempts = substr_count($result['result_info'], $glb_high_attempt_failed) + 1;
            
            $sql_miss = "SELECT 
                            xSerienstart
                            , Leistung AS result
                            , Info AS result_info
                        FROM
                            resultat 
                            LEFT JOIN serienstart USING (xSerienstart) 
                        WHERE xSerienstart = :serienstart 
                            AND Info LIKE '%".$glb_high_attempt_failed."%';";
            $query_miss = $glb_connection_server->prepare($sql_miss);
            
            $query_miss->bindValue(':serienstart', $xSerienstart);
            
            $query_miss->execute();
            $results_miss = $query_miss->fetchAll(PDO::FETCH_ASSOC);
            
            $miss = 0;
            foreach($results_miss as $result_miss){
                $miss = $miss + substr_count($result_miss['result_info'], $glb_high_attempt_failed);
            }
            
            $sql_heights = "SELECT serie
                                , height
                                FROM t_heights
                                WHERE serie = :serie
                                ORDER BY height ASC;";
            $query_heights = $glb_connection->prepare($sql_heights);
            $query_heights->bindValue(':serie', $xSerie);
            $query_heights->execute();
            $heights = $query_heights->fetchAll(PDO::FETCH_ASSOC);      
            
            $sql_maxHeight = "SELECT MAX(height) as max_height
                                FROM t_heights
                                WHERE serie = :serie
                                ORDER BY height ASC;";
            $query_maxHeight = $glb_connection->prepare($sql_maxHeight);
            $query_maxHeight->bindValue(':serie', $xSerie);
            $query_maxHeight->execute();
            $maxHeight = $query_maxHeight->fetch(PDO::FETCH_ASSOC);   
            $maxHeight = $maxHeight['max_height'];                                           
            
            
            if($maxHeight < $settings['diff_1_until']) {
                $diff = $settings['diff_1_value'];
            } elseif ($maxHeight < $settings['diff_2_until']) {
                $diff = $settings['diff_2_value'];
            } else {
                $diff = $settings['diff_3_value'];
            }
            
            $curr_height = $maxHeight + $diff;
            $curr_miss = 0;
            
            $out = checkOut($athlete['ath_id']);
            if($out){
                $curr_height = 0;
                $curr_miss = 0;
            } else{
                foreach($heights as $height){
                    if($height['height'] >= $athlete['ath_start']){
                        $sql_check = "SELECT Info
                                        FROM resultat
                                        WHERE Leistung = :height
                                            AND xSerienstart = :serienstart;";
                        $query_check = $glb_connection_server->prepare($sql_check);
                        $query_check->bindValue(':height', $height['height']);
                        $query_check->bindValue(':serienstart', $athlete['ath_id']);
                        $query_check->execute();
                        
                        if($query_check->rowCount() == 0){
                            $curr_height = $height['height'];
                            $curr_miss = 0;
                            break;
                        } else{
                            $check = $query_check->fetch(PDO::FETCH_ASSOC);
                            $check_miss = substr_count($check['Info'], $glb_high_attempt_failed);
                            $check_skip = substr_count($check['Info'], $glb_high_attempt_passed) + substr_count($check['Info'], $glb_high_attempt_waived);
                            if($check_miss > 0 && $check_skip == 0){
                                $curr_height = $height['height'];
                                $curr_miss = $check_miss;
                                break;
                            }
                        }
                    }
                }
            }
            $sql_insert = "INSERT INTO tempresult_".$round." (xSerienstart, xSerie, position, skip, result, result_attempts, miss, ath_out, curr_height, curr_miss) VALUES(:serienstart, :serie, :position, :skip, :result, :res_attempts, :miss, :out, :curr_height, :curr_miss)";
            $query_insert = $glb_connection->prepare($sql_insert);            
            
            $query_insert->bindValue(':serienstart', $xSerienstart);
            $query_insert->bindValue(':serie', $xSerie);
            $query_insert->bindValue(':position', $position);
            $query_insert->bindValue(':skip', $skip);
            $query_insert->bindValue(':result', $result_res);
            $query_insert->bindValue(':res_attempts', $res_attempts);
            $query_insert->bindValue(':miss', $miss);
            $query_insert->bindValue(':out', $out);
            $query_insert->bindValue(':curr_height', $curr_height);
            $query_insert->bindValue(':curr_miss', $curr_miss);
            
            $query_insert->execute();   
        }
    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }   
}

function updateResultTable($athlete, $event, $startHeight){
    global $glb_connection, $glb_connection_server;
    global $glb_results_skip;
    global $glb_high_attempt_passed, $glb_high_attempt_failed, $glb_high_attempt_waived;
    
    try{   
        $settings = getHighSettings($event);
        $round = getRound($event); 
        
        $sql_ath = "SELECT xSerienstart AS ath_id
                        , xSerie
                        , IF((SELECT COUNT(*) FROM resultat WHERE xSerienstart = serienstart.xSerienstart AND Leistung IN (".implode(',', $glb_results_skip).")), 1, 0) AS skip
                        , Starthoehe AS ath_start
                        , Position AS ath_pos
                    FROM serienstart
                    WHERE xSerienstart = :serienstart;";
        $query_ath = $glb_connection_server->prepare($sql_ath);
        
        $query_ath->bindValue(':serienstart', $athlete);
        
        $query_ath->execute();
        $ath = $query_ath->fetch(PDO::FETCH_ASSOC);
        
        $xSerienstart = $ath['ath_id'];
        $position = $ath['ath_pos'];
        $xSerie = $ath['xSerie'];
        $skip = $ath['skip'];
        
        $sql_res = "SELECT 
                        xSerienstart
                        , Leistung AS result_max
                        , Info AS result_info
                    FROM
                        resultat 
                        LEFT JOIN serienstart USING (xSerienstart) 
                    WHERE xSerienstart = :serienstart 
                        AND Info LIKE '%".$glb_high_attempt_passed."%'
                    ORDER BY Leistung DESC
                    LIMIT 1;";
        $query_res = $glb_connection_server->prepare($sql_res);
        
        $query_res->bindValue(':serienstart', $xSerienstart);
        
        $query_res->execute();
        $result = $query_res->fetch(PDO::FETCH_ASSOC);
        $result_res = ($result['result_max']) ? $result['result_max'] : 0;
        
        $res_attempts = substr_count($result['result_info'], $glb_high_attempt_failed) + 1;
        
        $sql_miss = "SELECT 
                        xSerienstart
                        , Leistung AS result
                        , Info AS result_info
                    FROM
                        resultat 
                        LEFT JOIN serienstart USING (xSerienstart) 
                    WHERE xSerienstart = :serienstart 
                        AND Info LIKE '%".$glb_high_attempt_failed."%';";
        $query_miss = $glb_connection_server->prepare($sql_miss);
        
        $query_miss->bindValue(':serienstart', $xSerienstart);
        
        $query_miss->execute();
        $results_miss = $query_miss->fetchAll(PDO::FETCH_ASSOC);
        
        $miss = 0;
        foreach($results_miss as $result_miss){
            $miss = $miss + substr_count($result_miss['result_info'], $glb_high_attempt_failed);
        }

        $sql_heights = "SELECT serie
                            , height
                            FROM t_heights
                            WHERE serie = :serie
                            ORDER BY height ASC;";
        $query_heights = $glb_connection->prepare($sql_heights);
        $query_heights->bindValue(':serie', $event);
        $query_heights->execute();
        $heights = $query_heights->fetchAll(PDO::FETCH_ASSOC);           
        
        $sql_maxHeight = "SELECT MAX(height) as max_height
                                FROM t_heights
                                WHERE serie = :serie
                                ORDER BY height ASC;";
        $query_maxHeight = $glb_connection->prepare($sql_maxHeight);
        $query_maxHeight->bindValue(':serie', $event);
        $query_maxHeight->execute();
        $maxHeight = $query_maxHeight->fetch(PDO::FETCH_ASSOC);   
        $maxHeight = $maxHeight['max_height'];          
        
        
        if($maxHeight < $settings['diff_1_until']) {
            $diff = $settings['diff_1_value'];
        } elseif ($maxHeight < $settings['diff_2_until']) {
            $diff = $settings['diff_2_value'];
        } else {
            $diff = $settings['diff_3_value'];
        }
        
        $curr_height = $maxHeight + $diff;                                 
        
        $curr_miss = 0;                                         
        
        $out = checkOut($athlete);
        if($out){
            $curr_height = 0;
            $curr_miss = 0;
        } else{
            foreach($heights as $height){
                if($height['height'] >= $startHeight){
                    $sql_check = "SELECT Info
                                    FROM resultat
                                    WHERE Leistung = :height
                                        AND xSerienstart = :serienstart;";
                    $query_check = $glb_connection_server->prepare($sql_check);
                    $query_check->bindValue(':height', $height['height']);
                    $query_check->bindValue(':serienstart', $athlete);
                    $query_check->execute();
                    
                    if($query_check->rowCount() == 0){
                        $curr_height = $height['height'];
                        $curr_miss = 0;
                        break;
                    } else{
                        $check = $query_check->fetch(PDO::FETCH_ASSOC);
                        $check_miss = substr_count($check['Info'], $glb_high_attempt_failed);
                        $check_skip = substr_count($check['Info'], $glb_high_attempt_passed) + substr_count($check['Info'], $glb_high_attempt_waived);
                        if($check_miss > 0 && $check_skip == 0){
                            $curr_height = $height['height'];
                            $curr_miss = $check_miss;
                            break;
                        }
                    }
                }
            }
        }
        $sql_insert = "UPDATE tempresult_".$round." SET
                                            skip = :skip
                                            , result = :result
                                            , result_attempts = :res_attempts
                                            , miss = :miss
                                            , ath_out = :out
                                            , curr_height = :curr_height
                                            , curr_miss = :curr_miss
                                            WHERE xSerienstart = :serienstart;";
        $query_insert = $glb_connection->prepare($sql_insert);            
        
        $query_insert->bindValue(':serienstart', $athlete);
        $query_insert->bindValue(':skip', $skip);
        $query_insert->bindValue(':result', $result_res);
        $query_insert->bindValue(':res_attempts', $res_attempts);
        $query_insert->bindValue(':miss', $miss);
        $query_insert->bindValue(':out', $out);
        $query_insert->bindValue(':curr_height', $curr_height);
        $query_insert->bindValue(':curr_miss', $curr_miss);
        
        $query_insert->execute();
    }catch(PDOException $e){
        trigger_error($e->getMessage());
    }  
}

function rankAthletes($event){
    global $glb_connection, $glb_connection_server;
    global $cfgEvalType, $strEvalTypeHeat;
    
    try{
        $eval = getEvaluationType($event);
        $round = getRound($event);
        if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat    
            $sql_athletes = "
                SELECT
                    xSerienstart
                    , result
                    , result_attempts
                    , miss
                    , skip
                FROM
                    tempresult_".$round."
                ORDER BY
                    xSerie 
                    , result DESC
                    , result_attempts ASC
                    , miss ASC;";
        } else{       //rank results from all heats together
            $sql_athletes = "
                SELECT
                    xSerienstart
                    , result
                    , result_attempts
                    , miss
                    , skip
                FROM
                    tempresult_".$round."
                ORDER BY 
                    result DESC
                    , result_attempts ASC
                    , miss ASC;";
        }        
        $query_athletes = $glb_connection->prepare($sql_athletes);
        $query_athletes->execute();
        
        $athletes = $query_athletes->fetchAll(PDO::FETCH_ASSOC);
        
        $heat = 0; 
        $perf = array();
        $perf_old = array();
        $j = 1;
        $rank = 0;
        $naa = false;
        // set rank for every athlete
        foreach($athletes as $athlete){
            if($eval == $cfgEvalType[$strEvalTypeHeat] && $heat != $athlete['xSerie']){ // new heat
                $j = 1;        // restart ranking
                $perf_old[] = '';
            }
            
            $perf = $athlete;
            
            unset($perf['xSerienstart']);
            unset($perf['skip']);
            
            if($athlete['skip'] == 0 && ($athlete['result'] > 0 || $athlete['miss'] > 0)) { //check if DNS or DSQ   
                if($perf_old != $perf) {  // check if same performance
                    $rank = $j;
                    if($athlete['result_attempts'] > 0) { // check if passed height
                        
                    }
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