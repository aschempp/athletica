<?php
/**
* provides functions for the meeting handling 
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

function getMeetings($meeting = 0){
    global $glb_connection_server;
    global $glb_status_results, $glb_status_live;
    global $glb_types_results;
    
    try {
        if($meeting != 0) {
            $where_meeting = "WHERE xMeeting = :meeting";
        }else {
            $where_meeting = "";
        }
        
        $sql_get = "SELECT meeting.Name AS meeting_name
                            , meeting.Ort AS meeting_ort
                            , meeting.DatumVon AS meeting_date_from
                            , meeting.DatumBis AS meeting_date_to
                            , xMeeting
                      FROM meeting
                        ".$where_meeting."
                      ORDER BY 
                        meeting_date_from
                        , meeting_name;";
        $query_get = $glb_connection_server->prepare($sql_get);

        // +++ bind parameters
        if($meeting != 0){
            $query_get->bindValue(':meeting', $meeting);
        }
        // --- bind parameters

        $query_get->execute();
        
        $meetings = $query_get->fetchAll(PDO::FETCH_ASSOC);
    
    } catch(PDOException $e){
        trigger_error($e->getMessage());
    }

    return $meetings;
}

?>