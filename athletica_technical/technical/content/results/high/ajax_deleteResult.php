<?php
define('GLOBAL_PATH', '../../../../');
define('ROOT_PATH', '../../../');
define('CURRENT_CATEGORY', 'athletica_tech');
define('CURRENT_PAGE', 'results');

require_once(ROOT_PATH.'lib/inc.init.php');
require_once(ROOT_PATH.'lib/cls.result_high.php');

$ath_id = $_POST['ath_id'];
$ath_start = $_POST['ath_start'];
$ath_res = $_POST['ath_res'];

$sql_delete = "DELETE
                FROM resultat
                WHERE xSerienstart = :athlete
                    AND Leistung = :result;";
$query_delete = $glb_connection_server->prepare($sql_delete);
$query_delete->bindValue(':athlete', $ath_id);
$query_delete->bindValue(':result', $ath_res);
$query_delete->execute();    

updateResultTable($ath_id, CFG_CURRENT_EVENT, $ath_start);
rankAthletes(CFG_CURRENT_EVENT);
calcRankingPoints($round);
resetQualification($round);
StatusChanged($round);
?>