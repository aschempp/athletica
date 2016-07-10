<?php
/**
* provides functions for the event handling 
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
 
function getEvents($meeting = 0, $event = 0){
    global $glb_connection_server;
    global $glb_status_results, $glb_status_live;
    global $glb_types_results;
    
    try {
        if($meeting != 0) {
            $and_meeting = " AND xMeeting = :meeting";
        }else {
            $and_meeting = "";
        }
        
        if($event != 0) {
            $and_event = " AND xSerie = :serie";
        }else {
            $and_event = "";
        }
        
        $sql_get = "SELECT disziplin.Name AS disc_name
                            , disziplin.Typ AS disc_type
                            , kategorie.Kurzname AS cat_name
                            , rundentyp.Typ AS round_type
                            , rundentyp.Wertung AS round_wertung
                            , IF(rundentyp.Typ!='0',rundentyp.Name,'') AS round_name
                            , serie.Bezeichnung AS serie_bez
                            , TIME_FORMAT(runde.Startzeit, '%H:%i') AS round_start_time
                            , TIME_FORMAT(runde.Stellzeit, '%H:%i') AS round_call_time
                            , DATE_FORMAT(runde.Datum, '%d.%m.%y') AS round_start_date
                            , runde.Status AS round_status
                            , runde.Versuche AS round_attempts
                            , runde.Endkampf AS round_final
                            , runde.Finalisten AS round_finalists
                            , runde.FinalNach AS round_final_after
                            , runde.Drehen AS round_drop
                            , wettkampf.Windmessung AS event_wind
                            , serie.xSerie AS xSerie
                            , runde.xRunde AS xRunde
                            , wettkampf.xWettkampf AS xWettkampf
                            , disziplin.xDisziplin AS xDisziplin
                            , kategorie.xKategorie AS xKategorie
                            , rundentyp.xRundentyp AS xRundentyp
                      FROM serie
                        LEFT JOIN runde USING(xRunde)
                        LEFT JOIN wettkampf USING(xWettkampf)
                        LEFT JOIN disziplin_".strtolower(CFG_CURRENT_LANGUAGE)." AS disziplin USING(xDisziplin)
                        LEFT JOIN kategorie USING(xKategorie)
                        LEFT JOIN rundentyp_".strtolower(CFG_CURRENT_LANGUAGE)." AS rundentyp USING (xRundentyp)
                     WHERE runde.Status IN (".implode(',',$glb_status_results).")
                     AND disziplin.Typ IN (".implode(',',array_keys($glb_types_results)).")
                        ".$and_meeting."
                        ".$and_event."
                     ORDER BY 
                        Startzeit
                        , disc_name;";
        $query_get = $glb_connection_server->prepare($sql_get);

        // +++ bind parameters
        if($meeting != 0){
            $query_get->bindValue(':meeting', $meeting);
        }
        if($event != 0){
            $query_get->bindValue(':serie', $event);
        }
        // --- bind parameters

        $query_get->execute();
        
        if($event != 0){
        
            $events = $query_get->fetch(PDO::FETCH_ASSOC);
            
            define('CFG_CURRENT_WIND', $events['event_wind']);
            
            $merged = getMergedRounds($events['xRunde']);
            
            if($merged) {
                $where = "WHERE xRunde IN ".$merged;
                
                $categories = getMergedCategories($merged);
                $cat_string = "";
                $tmp = "";
                foreach($categories as $cat) {
                    $cat_string .= $tmp.$cat['cat_name'];
                    $tmp = "/";
                }
                $events['cat_name'] = $cat_string;
            } else {
                $where = "WHERE xRunde = :runde";
            }
            
            $sql_status = "UPDATE runde
                            SET Status = :status
                            ".$where.";";
            $query_status = $glb_connection_server->prepare($sql_status);

            // +++ bind parameters
            $query_status->bindValue(':status', $glb_status_live);
            if(!$merged) {
                $query_status->bindValue(':runde', $events['xRunde']);
            }
            // --- bind parameters

            $query_status->execute();
            StatusChanged($events['xRunde']);
            
        } else{
            $events = $query_get->fetchAll(PDO::FETCH_ASSOC);
        }
    
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
    
    return $events;
}

function getRound($event){
    global $glb_connection_server;
    
    try{
        $sql_get = "SELECT xRunde
                        FROM serie
                        WHERE xSerie = :serie;";
        $query_get = $glb_connection_server->prepare($sql_get);

        // +++ bind parameters
        $query_get->bindValue(':serie', $event);
        // --- bind parameters

        $query_get->execute();
        $round = $query_get->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
    
    return $round['xRunde'];
}

function getTechSettings($event){
    global $glb_connection_server;
    
    try{
        $sql_settings = "SELECT 
                            Versuche AS round_attempts
                            , Endkampf AS round_final
                            , Finalisten AS round_finalists
                            , FinalNach AS round_final_after
                            , Drehen AS round_drop
                            FROM runde
                                LEFT JOIN serie USING(xRunde)
                            WHERE xSerie = :serie;";
                            
        $query_settings = $glb_connection_server->prepare($sql_settings);
            
        $query_settings->bindValue(':serie', $event);

        $query_settings->execute();
        $settings = $query_settings->fetch(PDO::FETCH_ASSOC);
        
        
        
    }catch(PDOException $e) {
        trigger_error($e->getMessage());
    }
    
    return $settings;
    
}

function getHighSettings($event){
    global $glb_connection;
    global $glb_high_diff_1_until_default, $glb_high_diff_2_until_default, $glb_high_diff_1_value_default, $glb_high_diff_2_value_default, $glb_high_diff_3_value_default;
    
    try{
        $sql_settings = "SELECT 
                            *
                            FROM t_high_settings
                            WHERE xSerie = :serie;";
                            
        $query_settings = $glb_connection->prepare($sql_settings);
            
        $query_settings->bindValue(':serie', $event);

        $query_settings->execute();
        if($query_settings->rowCount() > 0) {
            $settings = $query_settings->fetch(PDO::FETCH_ASSOC);
        } else{
            $settings['diff_1_until'] = $glb_high_diff_1_until_default;
            $settings['diff_2_until'] = $glb_high_diff_2_until_default;
            $settings['diff_1_value'] = $glb_high_diff_1_value_default;
            $settings['diff_2_value'] = $glb_high_diff_2_value_default;
            $settings['diff_3_value'] = $glb_high_diff_3_value_default;
        }

    } catch(PDOException $e) {
        trigger_error($e->getMessage());
    }
    
    return $settings;
    
}

function updateHighSettings($event, $field, $value){
    global $glb_connection;
    
    try{
        $sql_update = "INSERT INTO t_high_settings
                        SET xSerie = :serie
                            , ".$field." = :value
                        ON DUPLICATE KEY UPDATE  
                            ".$field." = :value;";
        $query_update = $glb_connection->prepare($sql_update);
        $query_update->bindValue(':serie', $event);
        $query_update->bindValue(':value', $value);
        $query_update->execute();
        
        return 'ok';
        
    } catch(PDOException $e) {
        trigger_error($e->getMessage());
        return 'error';
    }
    
}

function getMergedRounds($round){  
    global $glb_connection_server;
    
    $sqlRounds = "";
    $roundMerged = false;
    $sql_roundset = "SELECT xRundenset 
                            FROM rundenset
                            WHERE xRunde = :round;";
                            
    $query_roundset = $glb_connection_server->prepare($sql_roundset);
        
    $query_roundset->bindValue(':round', $round);
    
    $query_roundset->execute();
    $roundset = $query_roundset->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($roundset) > 0){   
        $sql_merged = "SELECT xRunde 
                            FROM rundenset
                            WHERE xRundenset = :roundset;";
                            
        $query_merged = $glb_connection_server->prepare($sql_merged);
        
        $query_merged->bindValue(':roundset', $roundset[0]['xRundenset']);
        
        $query_merged->execute();
        $merged = $query_merged->fetchAll(PDO::FETCH_ASSOC);
        
        $sqlRounds .= "(";   
        foreach($merged as $round_tmp){   // get merged rounds  
            $roundMerged = true;  
            $sqlRounds .= $round_tmp['xRunde'] . ","; 
        }
        $sqlRounds = substr($sqlRounds,0,-1).")";  
    }   
           
    if (!$roundMerged) {  
           $sqlRounds = ""; 
    }  
    return  $sqlRounds;
}

function getMergedCategories($rounds){  
    global $glb_connection_server;
    
    $sql_cat = "SELECT kategorie.Kurzname AS cat_name
                  FROM runde
                    LEFT JOIN wettkampf USING(xWettkampf)
                    LEFT JOIN kategorie USING(xKategorie)
                 WHERE runde.xRunde IN ".$rounds.";";
    
    $query_cat = $glb_connection_server->prepare($sql_cat);
    
    $query_cat->execute();
    $categories = $query_cat->fetchAll(PDO::FETCH_ASSOC);
    
    return $categories;
 
    
}

function closeEvent($round, $status){
    global $glb_connection_server;
        
    
    
    try{
        $_SESSION[CFG_SESSION]['xSerie'] = 0;
        $_SESSION[CFG_SESSION]['xMeeting'] = 0;
        
        if($round > 0){
            $merged = getMergedRounds($round);
            
            if($merged) {
                $where = "WHERE xRunde IN ".$merged;
            } else {
                $where = "WHERE xRunde = :runde";
            }
            
            $sql_status = "UPDATE runde
                            SET Status = :status
                            ".$where.";";
            $query_status = $glb_connection_server->prepare($sql_status);

            // +++ bind parameters
            $query_status->bindValue(':status', $status);
            if(!$merged) {
                $query_status->bindValue(':runde', $round);
            }
            // --- bind parameters

            $query_status->execute();
            StatusChanged($round);
        }
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function resetActiveAthlete($event) {
    global $glb_connection_server;
    
    try{
        $sql_reset = "UPDATE serienstart
                        SET AktivAthlet = 'n'
                        WHERE xSerie = :serie;";
        $query_reset = $glb_connection_server->prepare($sql_reset);
        
        $query_reset->bindValue(':serie', $event);
        $query_reset->execute();
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function setActiveAthlete($event, $athlete) {
    global $glb_connection_server;
    
    try{
        resetActiveAthlete($event);
        
        $sql_set = "UPDATE serienstart
                        SET AktivAthlet = 'y'
                        WHERE xSerienstart = :serienstart;";
        $query_set = $glb_connection_server->prepare($sql_set);
        
        $query_set->bindValue(':serienstart', $athlete);
        $query_set->execute();
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
}

function StatusChanged($round){    
    global $glb_connection_server;
    
    try{
        $sql_update = "UPDATE runde SET StatusChanged = 'y' WHERE xRunde = :round;";
        $query_update = $glb_connection_server->prepare($sql_update);
        $query_update->bindValue(':round', $round);
        $query_update->execute();
        
        StatusChangedMeeting(CFG_CURRENT_MEETING);
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }      
}

function StatusChangedMeeting($meeting) {
    global $glb_connection_server;
    
    try{
        $sql_update = "UPDATE meeting SET StatusChanged = 'y' WHERE xMeeting = :meeting;";
        $query_update = $glb_connection_server->prepare($sql_update);
        $query_update->bindValue(':meeting', $meeting);
        $query_update->execute();
        
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }
    
    
}

function getEvaluationType($event) {
    global $glb_connection_server;
    
    if(!empty($event))
    {
        try {
            $sql_eval = "SELECT Wertung
                            FROM rundentyp_" . CFG_CURRENT_LANGUAGE  . "
                                LEFT JOIN runde USING(xRundentyp)
                                LEFT JOIN serie USING(xRunde)
                            WHERE xSerie = :serie";
                                        
            $query_eval = $glb_connection_server->prepare($sql_eval);
            
            $query_eval->bindValue(':serie', $event);
            
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
?>