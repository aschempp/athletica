<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_tech.php');

$return = "error";

$ath_res = $_POST['ath_res'];
$ath_wind = $_POST['ath_wind'];
$ath_id = $_POST['ath_id'];
$res_id = $_POST['res_id'];
$round = $_POST['round'];
$event = $_POST['event'];
$attempt = $_POST['attempt'];

$ath_res_db = formatResultDB($ath_res);
$ath_wind_db = formatWindDB($ath_res, $ath_wind);

if($ath_res_db == 'error') {
    $return = 'result';
    
} else {
    try{    
        $sql_sex = "SELECT Geschlecht 
                          FROM kategorie 
                     LEFT JOIN wettkampf USING(xKategorie) 
                     LEFT JOIN start USING(xWettkampf) 
                     LEFT JOIN serienstart USING(xStart) 
                         WHERE xSerienstart = :ath_id;";   
        $query_sex = $glb_connection_server->prepare($sql_sex);    
        
        $query_sex->bindValue(':ath_id', $ath_id);  
        $query_sex->execute();
        $sex = $query_sex->fetch(PDO::FETCH_ASSOC);
        $sex = $sex['Geschlecht'];
        
        $points = calcPoints($event, $ath_res_db, $fraction = 0, $sex, $ath_id);
        $res_id_sql = (!is_null($res_id)) ? "xResultat = :res_id, " : "";
        
        $sql = "INSERT INTO resultat
                        SET ".$res_id_sql."
                            Leistung = :ath_res
                            , Info = :ath_wind
                            , Punkte = :ath_pts
                            , xSerienstart = :ath_id
                ON DUPLICATE KEY UPDATE
                            Leistung = :ath_res
                            , Info = :ath_wind
                            , Punkte = :ath_pts
                            , xSerienstart = :ath_id
                            ;";
        $query = $glb_connection_server->prepare($sql);
            
        // +++ bind parameters
        if(!is_null($res_id)) {
            $query->bindValue(':res_id', $res_id);    
        }
        $query->bindValue(':ath_res', $ath_res_db);  
        $query->bindValue(':ath_wind', $ath_wind_db);   
        $query->bindValue(':ath_pts', $points);   
        $query->bindValue(':ath_id', $ath_id);    
        // --- bind parameters
        $query->execute();
        
        $res_id = $glb_connection_server->lastInsertId();
        
        $sql_skip = "SELECT IF(COUNT(*)>0,1,0) AS skip
                        FROM resultat 
                        WHERE xSerienstart = :serienstart
                            AND Leistung IN (".implode(',', $glb_results_skip).");";
                    
        $query_skip = $glb_connection_server->prepare($sql_skip);
        
        $query_skip->bindValue(':serienstart', $ath_id);
        
        $query_skip->execute();
        $skip = $query_skip->fetch(PDO::FETCH_ASSOC);
        
        
                $sql_select = "SELECT Leistung AS ath_res
                            , Info AS ath_wind
                        FROM resultat
                        WHERE xSerienstart = :serienstart
                        ORDER BY xSerienstart
                            , Leistung DESC;";
        $query_select = $glb_connection_server->prepare($sql_select);
        
        $query_select->bindValue(':serienstart', $ath_id);
        
        $query_select->execute();
        $results = $query_select->fetchAll(PDO::FETCH_ASSOC);
        
        $n = 1;
        foreach($results as $result){
            $sql_update = "UPDATE tempresult_".$round." 
                            SET skip = :skip 
                            , Res".$n."=:result
                            , Wind".$n."=:wind
                            WHERE xSerienstart = :serienstart;";
            $query_update = $glb_connection->prepare($sql_update);
            
            $query_update->bindValue(':skip', $skip['skip']);
            $query_update->bindValue(':result', $result['ath_res']);
            $query_update->bindValue(':wind', $result['ath_wind']);
            $query_update->bindValue(':serienstart', $ath_id);
            
            $query_update->execute();
            $n++;
        }
               
        rankAthletes(CFG_CURRENT_EVENT);
        calcRankingPoints($round);
        resetQualification($round);
        StatusChanged($round);
        
        $return = $res_id;
        
    }catch(PDOException $e){
        trigger_error($e->getMessage());
        $return = $e;
    }
    
}
echo $return;


//---------------------------------------------------------------------

?>
