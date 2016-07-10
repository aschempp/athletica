<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$return = "error";

$ath_res = $_POST['ath_res'];
$ath_id = $_POST['ath_id'];
$res_id = $_POST['res_id'];
$round = $_POST['round'];
$event = $_POST['event'];
$height = $_POST['height'];

try{

    $ath_res_db = formatResultInput($ath_res);

    if($ath_res_db == 'error') {
        $return = 'result';
    } else {
        if($ath_res_db) {
        
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
                                Leistung = :height
                                , Info = :ath_res
                                , Punkte = :ath_pts
                                , xSerienstart = :ath_id
                    ON DUPLICATE KEY UPDATE
                                Leistung = :height
                                , Info = :ath_res
                                , Punkte = :ath_pts
                                , xSerienstart = :ath_id
                                ;";
            $query = $glb_connection_server->prepare($sql);
                
            // +++ bind parameters
            if(!is_null($res_id)) {
                $query->bindValue(':res_id', $res_id);    
            }
            $query->bindValue(':ath_res', $ath_res_db);  
            $query->bindValue(':height', $height);   
            $query->bindValue(':ath_pts', $points);   
            $query->bindValue(':ath_id', $ath_id);    
            // --- bind parameters
            $query->execute();
        } else{
            $sql_delete = "DELETE
                            FROM resultat
                            WHERE xResultat = :res_id;";
            $query_delete = $glb_connection_server->prepare($sql_delete);
            $query_delete->bindValue(':res_id', $res_id);
            $query_delete->execute();                                            
        }

        $sql_athlete = "SELECT Starthoehe AS ath_start
                            FROM serienstart
                            WHERE xSerienstart = :serienstart;";
        $query_athlete = $glb_connection_server->prepare($sql_athlete);
        $query_athlete->bindValue(':serienstart', $ath_id);
        $query_athlete->execute();

        $athlete = $query_athlete->fetch(PDO::FETCH_ASSOC);
        
        createHeightTable(CFG_CURRENT_EVENT);
        updateResultTable($ath_id, CFG_CURRENT_EVENT, $athlete['ath_start']);
               
        rankAthletes(CFG_CURRENT_EVENT);
        calcRankingPoints($round);
        resetQualification($round);
        StatusChanged($round);

        $return = "ok";
    }
}catch(PDOException $e){
    trigger_error($e->getMessage());
    $return = $e;
}
echo $return;


//---------------------------------------------------------------------

?>
