<?php

if (!defined('AA_CL_XML_DATA_LIB_INCLUDED'))
{
    define('AA_CL_XML_DATA_LIB_INCLUDED', 1);
}else{
    return;
}

/************************************
 *
 * XML_data
 *
 * Loads xml data from Alabus Verband and the online registration
 * system into Athletica. Also generates the xml result feed for
 * uploading to Alabus Verband.
 * (can open gz compressed xml files)
 *
/************************************/

//require("common.lib.php");
require('cl_performance.lib.php');
require('cl_timetable.lib.php');
require('meeting.lib.php');
 include('./config.inc.php');
 
 
if(AA_connectToDB() == FALSE){ // invalid db connection
    return;
}

if(AA_checkMeetingID() == FALSE){        // no meeting selected
    return;        // abort
}

// global definition
$updateType = ""; // "complete" or "update" load of data
// type reg
$discode = "";
$catcode = "";
$xDis = array();
$distype = "";
$bCombined = false;
// type base
$bAthlete = false;
$bPerf = false;
$biPerf = false;
$bAccount = false;
$bRelay = false;
$bSvm = false;
$athlete = array();
$perf = array();
$iperf = array();
$account = array();
$relay = array();
$svm = array();
$cName = "";
$arr_noCat = array();

// type result                                        

class XML_data{
    var $opentags = array();
    var $gzfp;
       
    function load_xml($file, $type, $mode=''){          
        global $strErrXmlParse, $strErrFileNotFound,$arr_noCat, $cfgResDisc , $cfgSvmDiscFirst, $cfgSvmDiscLast;   
        
        if($type != "base" && $type != "result" && $type != "reg" && $type != "regZLV" ){ // unknown type of data
            return false;
        }
        switch ($type){
            case "reg":
            mysql_query("LOCK TABLES disziplin_de READ,disziplin_fr READ, disziplin_it READ, kategorie READ, meeting READ"
                    . ", runde READ, team READ, verein READ, wettkampf WRITE"
                    . ", anmeldung WRITE, athlet WRITE start WRITE");
            break;
            case "base":
            mysql_query("LOCK TABLES base_athlete WRITE, base_account WRITE
                        , base_performance WRITE, base_relay WRITE
                        , base_svm WRITE, verein WRITE, athlet WRITE");
            break;
            case "regZLV":
            mysql_query("LOCK TABLES base_athlete READ, base_athlete AS b READ
                        , base_performance READ, base_relay READ , staffel READ,
                        , verein READ, athlet WRITE, athlet AS a READ, meeting READ
                        , team WRITE,  kategorie READ, wettkampf AS w READ,
                        , disziplin_de AS d READ, disziplin_fr AS d READ, disziplin_it AS d READ, anmeldung AS an READ, anmeldung WRITE
                        , kategorie AS k READ, start WRITE, runde WRITE");
            break;                
        }
        //mysql_query("START TRANSACTION");
        
        $xml_parser = xml_parser_create();
        if ($type == 'regZLV') {
             xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING,"UTF-16");  
        }
        else {
            xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING,"ISO-8859-1");  
        }   
       
        xml_set_element_handler($xml_parser, "XML_".$type."_start", "XML_".$type."_end");
        xml_set_character_data_handler($xml_parser, "XML_".$type."_data");       
    
        if(strtolower(substr($file,(strlen($file)-3))) == ".gz"){
            if (!($fp = @gzopen($file, "r"))) {
                AA_printErrorMsg($strErrFileNotFound.": ".$file);
            }
                                                                            
            $tb = @filesize($file); // file size uncompressed
            $tb = $tb/6*100; // file compression factor (approximately)
            $cb = 0;
            $c = 5;
        
            while ($data = gzgets($fp, 4096)) {     
                $cb += strlen($data);
                if($tb != 0){ $perc = (($cb / $tb)*100); }
                if($perc >= $c){
                    $width = $c*1.5; // width of progress bar (max 150px)
                    ?>
<script type="text/javascript">
document.getElementById("progress").width="<?php echo $width ?>";
</script>
                    <?php
                    ob_flush();
                    flush();
                    $c += 5;
                }           
                if (!xml_parse($xml_parser, $data, gzeof($fp))) {
                    XML_db_error(sprintf($strErrXmlParse.": %s, %d",
                        xml_error_string(xml_get_error_code($xml_parser)),
                        xml_get_current_line_number($xml_parser)));
                }         
            }
            xml_parser_free($xml_parser);
            
            gzclose($fp);  
        }else{
            if (!($fp = @fopen($file, "r"))) {
                AA_printErrorMsg($strErrFileNotFound.": ".$file);
            }
            
            while ($data = fgets($fp, 4096)) {
                if (!xml_parse($xml_parser, $data, feof($fp))) {
                    XML_db_error(sprintf($strXmlParseErr.": %s, %d",
                        xml_error_string(xml_get_error_code($xml_parser)),
                        xml_get_current_line_number($xml_parser)));
                }
            }
            xml_parser_free($xml_parser);
            
            fclose($fp);
        }
                               
        //mysql_query("COMMIT");
        mysql_query("UNLOCK TABLES");
        
        return $arr_noCat;
    }
    
    function set_updateType($value){
        global $updateType;
        $updateType = $value;
    }
      
    function gen_result_xml($file){  
        //function returns containing number of results in xml-file
        $nbr_effort=0;              
        $notStartedMK = array(); 

        //global    $opentags;
        global    $cfgDisciplineType, $cfgEventType, $strEventTypeSingleCombined, 
            $strEventTypeClubCombined, $strDiscTypeTrack, $strDiscTypeTrackNoWind, 
            $strDiscTypeRelay, $strDiscTypeDistance, $cfgRoundStatus,
            $strDiscTypeJump, $strDiscTypeJumpNoWind, $strDiscTypeThrow, $strDiscTypeHigh,
            $cfgCombinedWO, $cfgCombinedDef;
        
        $this->gzip_open($file);
        
        // begin xml
        $this->write_xml_open("watDataset", array('version'=>date('y-m-d')));
        $this->write_xml_open("event");
        
        //
        // output contest data
        //
        $global_rankadd = "";   
        $indoor = '0';
        $query = "
            SELECT
                m.*
                , s.Name as Stadion
                , s.Ueber1000m
                , s.Halle
            FROM 
                meeting as m 
                LEFT JOIN stadion as s ON m.xStadion = s.xStadion 
            WHERE xMeeting = ".$_COOKIE['meeting_id'];
        $res = mysql_query($query);
        if(mysql_errno() > 0){
            echo(mysql_errno().": ".mysql_error());
        }else{
            $row = mysql_fetch_assoc($res);
            mysql_free_result($res);
            
            $this->write_xml_finished("eventNumber",$row['xControl']);
            $this->write_xml_finished("name",str_replace('&', '&amp;', $row['Name']));
            $this->write_xml_finished("eventStart",$row['DatumVon']);
            $this->write_xml_finished("eventEnd",$row['DatumBis']);
            $this->write_xml_finished("location",$row['Ort']);
            $this->write_xml_finished("stadium",$row['Stadion']);
            $this->write_xml_finished("amountSpectators"," ");
            
            if($row['Ueber1000m'] == 'y'){ $global_rankadd = "A"; }
            //if($row['Halle'] == 'y'){ $global_rankadd .= "i"; }                
            if($row['Saison'] == 'I'){ $indoor = "1"; }                           // 1 = inddor , 0 = outdoor
        }
        
        //
        // output all athletica generated teams (relays, svm)
        //
        $this->write_xml_open("accounts");      
                
         $query = "
            SELECT 
                s.xStaffel
                , s.Name
                , v.xCode as Verein
                , k.Code as Kat
                , d.Code as Dis
            FROM
                staffel as s
                LEFT JOIN verein as v ON (s.xVerein = v.xVerein)
                LEFT JOIN kategorie as k ON (k.xKategorie = s.xKategorie  )
                LEFT JOIN start as st ON (st.xStaffel = s.xStaffel)
                LEFT JOIN wettkampf as w ON (w.xWettkampf = st.xWettkampf )
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d  ON (d.xDisziplin = w.xDisziplin)
            WHERE 
                Athleticagen = 'y'    
                AND w.Mehrkampfcode != 408         
                AND s.xMeeting = ".$_COOKIE['meeting_id']."
            ORDER BY 
                v.xVerein";        
        
        $res_teams = mysql_query($query);       
        
        if(mysql_errno() > 0){
            echo(mysql_errno().": ".mysql_error());
        }else{
            
            $account = 0;
            
            
            while($row_teams = mysql_fetch_assoc($res_teams)){
                if(empty($row_teams['Verein']) || $row_teams['Verein'] == '999999'){
                    continue;
                }
                if($account != $row_teams['Verein']){
                    $this->close_open_tags("accounts");
                    
                    $this->write_xml_open("account");
                    $this->write_xml_finished("accountCode", $row_teams['Verein']);
                    $this->write_xml_open("relays");
                }
                
                //$licenseCategory = ($row_teams['Kat']=='MASM' || $row_teams['Kat']=='MASW') ? '' : $row_teams['Kat'];
                $licenseCategory = $row_teams['Kat'];
                
                $this->write_xml_open("relay", array('id'=>$row_teams['xStaffel'], 'isAthleticaGenerated'=>'1'));
                $this->write_xml_finished("relayName", $row_teams['Name']);
                $this->write_xml_finished("licenseCategory", $licenseCategory);
                $this->write_xml_finished("sportDiscipline", $row_teams['Dis']);
                $this->close_open_tags("relays");
            }
            
        }
                      
                
        $query = "
            SELECT 
                t.xTeam
                , t.Name
                , v.xCode as Verein
                , k.Code as Kat
            FROM
                team as t
                LEFT JOIN verein as v ON (t.xVerein = v.xVerein) 
                LEFT JOIN wettkampf as w ON (t.xKategorie = w.xKategorie)
                LEFT JOIN kategorie_svm as k ON (w.xKategorie_svm = k.xKategorie_svm)   
            WHERE 
                Athleticagen = 'y'   
                AND w.Mehrkampfcode != 408                 
                AND w.xMeeting = ".$_COOKIE['meeting_id']."
            GROUP BY
                t.xTeam
            ORDER BY 
                v.xVerein";      
         
        $res_teams = mysql_query($query);     
        
        if(mysql_errno() > 0){
            echo(mysql_errno().": ".mysql_error());
        }else{
            
            $account = 0;
            
            
            while($row_teams = mysql_fetch_assoc($res_teams)){
                if(empty($row_teams['Verein']) || $row_teams['Verein'] == '999999'){
                    continue;
                }
                if($account != $row_teams['Verein']){
                    $this->close_open_tags("accounts");
                    
                    $this->write_xml_open("account");
                    $this->write_xml_finished("accountCode", $row_teams['Verein']);
                    $this->write_xml_open("svms");
                }
                $this->write_xml_open("svm", array('id'=>$row_teams['xTeam'], 'isAthleticaGenerated'=>'1'));
                $this->write_xml_finished("svmName", $row_teams['Name']);
                $this->write_xml_finished("svmCategory", $row_teams['Kat']);
                $this->close_open_tags("svms");
            }
            
        }
        $this->close_open_tags("event");
        
        //
        // get all disciplines
        //
        $this->write_xml_open("disciplines");             
        
        $sql = "SELECT
                    w.Typ,
                    w.Windmessung,
                    d.Typ,
                    d.Code AS dCode,
                    k.Code,
                    w.xWettkampf,
                    d.Kurzname,
                    w.Mehrkampfcode,
                    w.xKategorie,
                    d.Staffellaeufer, 
                    r.xRunde
                FROM 
                    wettkampf as w 
                    LEFT JOIN runde as r ON (w.xWettkampf = r.xWettkampf)  
                LEFT JOIN
                    disziplin_" . $_COOKIE['language'] . " as d ON d.xDisziplin = w.xDisziplin
                LEFT JOIN
                    kategorie as k ON k.xKategorie = w.xKategorie     
                WHERE r.xRunde > 0 
                      AND w.Mehrkampfcode != 408         
                      AND w.xMeeting = ".$_COOKIE['meeting_id']."                  
                ORDER BY
                    k.Code
                    , w.xKategorie
                    , dCode
                    , w.Mehrkampfcode
                    , w.Mehrkampfreihenfolge
                    , d.Staffellaeufer";
                // the order "k.Code, w.xKategorie" makes sense if there are multiple self made categories (without any code)        
        $res = mysql_query($sql);  
         
        if(mysql_errno() > 0){
            echo(mysql_errno().": ".mysql_error());
        }else{
            
            $current_type = "";
            $combined = "";
            $current_cat = "";
            $current_xcat = 0;
            $fetched_events = array(); // used for combined events
                        // if an athlete has no result for one discipline of a combined event,
                        // the detail text has to include a 'null' result
            $GLOBALS['rounds'] = array();    
            
            while($row = mysql_fetch_array($res)){
                    
                /*if(empty($row[3]) || empty($row[4])){
                    // self made discipline or category
                    continue;
                }*/
                if(empty($row[3])){
                    // self made discipline
                    continue;
                }
                if(empty($row[4])){
                    // self made category
                    $row[4] = "";
                }
                
                $relay = AA_checkRelay($row[5]);    // check, if this is a relay event    
                if ($relay != False){
                     if ($keep_dCode == $row[3] && $keep_kCode = $row[4] && $keep_Event= $row[5] && $keep_wCat = $row[8]){
                          // skip hier relays with more than one round (to prevent them double)                             
                          continue;   
                     }  
                }
                          
                //
                // generate results for combined events
                //
                
                if($current_xcat != $row[8] || $combined_dis != $row[7]){ // cat or combcode changed, print combined results
                    //if(!empty($combined) && $combined_dis < 9000){ // combined codes 9000 and above are self made disciplines
                    //if(!empty($combined) && $combined_dis < 796){ // combined codes 9000 and above are self made disciplines      
                    if(!empty($combined) && $combined_dis < 9000 && $combined_dis != 796 && $combined_dis != 797 && $combined_dis != 798 && $combined_dis != 799) {                
                        $this->write_xml_open("discipline", array('sportDiscipline'=>$combined_dis, 'licenseCategory'=>$combined_cat));
                        $this->write_xml_open("athletes");
                        
                        // calc points
                        foreach($combined as $xathlet => $disc){
                            
                            $points = 0;
                            $eDetails = "";
                            $tmp_fe = $fetched_events; // temp array for fetched events
                            
                            foreach($disc as $xdisc => $tmp){
                                if($xdisc == "catathlete"){ continue; }
                                
                                // check if there are events missing for the current athlete and add 'null' entries
                                while($tmp_fe[0][3] != $xdisc){
                                    //$eDetails .= $tmp_fe[0][6]." (0); ";
                                    $eDetails .= "0/";
                                    array_shift($tmp_fe);
                                }
                                array_shift($tmp_fe);
                                
                                $points += $tmp['points'];
                                if($tmp['wind'] == " "){
                                    $tmp['wind'] = "";
                                }else{
                                    if($tmp['wind'] >= 0){
                                        $tmp['wind'] = "+".$tmp['wind'];
                                    }else{
                                        $tmp['wind'] = $tmp['wind'];
                                    }
                                }
                                //$eDetails .= $tmp['discipline']." (".$tmp['effort'].$tmp['wind']."); ";
                                $eDetails .= $tmp['discipline'].' '.$tmp['effort'].$tmp['wind']."/";
                            }
                            
                            // check if last events are missing
                            while(isset($tmp_fe[0][3])){
                                $eDetails .= "0/";
                                array_shift($tmp_fe);
                            }
                            
                            $eDetails = substr($eDetails, 0, -1);
                            $combined[$xathlet]['points'] = $points;
                            $combined[$xathlet]['edetails'] = $eDetails;
                            
                        }
                        // sort for points
                        usort($combined, array($this, "sort_combined"));
                        
                        // write
                        //$rank = array();
                        //$curr_athlete_cat = "";
                        $rank = 0;    // athletes rank
                        $cRank = 0;    // rank counter
                        $lp = 0;    // remembers points of last athlete
                        foreach($combined as $xathlet => $disc){
                            $this->close_open_tags("athletes");
                            
                            // count place for each athlete category
                            /*$curr_athlete_cat = $combined[$xathlet]['catathlete'];
                            if(!isset($rank[$curr_athlete_cat])){
                                $rank[$curr_athlete_cat] = 1;
                            }else{
                                $rank[$curr_athlete_cat]++;
                            }*/
                            
                           
                            $cRank++;
                            if($lp != $disc['points']){
                                $rank = $cRank;
                                $lp = $disc['points'];
                            }
                            
                            // get information for athlete, remove not needed information and sort per DateOfEffort
                            $tmp = $disc;
                            $tmp['points'] = null;
                            $tmp['edetails'] = null;
                            $tmp['catathlete'] = null;
                            $tmp = array_values($tmp);
                            usort($tmp, array($this, "sort_perdate"));
                            $tmp = $tmp[0];
                            
                            // filter athletes not from switzerland and athletes without license
                            if($tmp['accountCode'] == '' || $tmp['accountCode'] == '999999' || $tmp['licenseType'] == 3){
                                continue;
                            }   
                           
                            if (!$notStartedMK[$tmp['xAthlet']]) {     // Athlet is not started for one disziplin --> not send the combined event points to AES    
                            
                                $this->write_xml_open("athlete", array('license'=>$tmp['license'], 'licensePaid'=>$tmp['licensePaid']
                                        , 'licenseCat'=>'', 'inMasterData'=>$tmp['inMasterData']));
                                
                                if(!$tmp['inMasterData']){
                                    $this->write_xml_finished("lastName", $tmp['lastName']);
                                    $this->write_xml_finished("firstName", $tmp['firstName']);
                                    
                                    $this->write_xml_finished("birthDate", $tmp['birthDate']);
                                    $this->write_xml_finished("sex", $tmp['sex']);
                                    $this->write_xml_finished("nationality", $tmp['nationality']);
                                    $this->write_xml_finished("accountCode", $tmp['accountCode']);
                                    $this->write_xml_finished("secondaccountCode", " ");
                                }
                                
                                $nbr_effort++;
                                $this->write_xml_open("efforts");
                                $this->write_xml_open("effort");
                                $this->write_xml_finished("DateOfEffort",$tmp['DateOfEffort']);
                                $this->write_xml_finished("scoreResult",AA_alabusScore($disc['points']));
                                $this->write_xml_finished("wind"," ");
                                $this->write_xml_finished("kindOfLap"," ");    // round type combined (D)
                                $this->write_xml_finished("lap"," ");        // heat name (A_, B_, 01, 02 ..)
                                //$this->write_xml_finished("place",$rank[$curr_athlete_cat]);
                                $this->write_xml_finished("place",$rank);
                                $this->write_xml_finished("placeAddon",$tmp['placeAddon']);    
                                $this->write_xml_finished("indoor",$tmp['indoor']);   
                                $this->write_xml_finished("relevant","1");
                                $this->write_xml_finished("effortDetails",$disc['edetails']);
                                $this->write_xml_close("effort");
                            }
                        }
                        
                        $this->close_open_tags("disciplines");
                       
                    }
                    $combined = array();
                    $fetched_events = array();
                    $combined_dis = $row[7];
                    $combined_cat = $row[4];
                    $current_cat = $row[4];
                    $current_xcat = $row[8];
                }
                
               
                
                // keep events rows of combined events to check on missing results after
                if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){
                    $fetched_events[] = $row;
                }
                
                //
                // first of all, print all single results (athletes and relays)
                //
                                    
                $order_perf = "";
                $valid_result = "";
                $best_perf = "";
                //$highjump = false;
                
                if(($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                    || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow]))
                {
                    $order_perf = "DESC";
                    $best_perf = ", max(r.Leistung) as Leistung";
                }
                else if($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                {
                    if ($row[1] == 1) {            // with wind
                        //$order_perf = "DESC, r.Info ASC";
                        $order_perf = "DESC";
                    }
                    else {                            // without wind
                        $order_perf = "DESC";
                    }
                    $best_perf = ", max(r.Leistung) as Leistung";
                }
                else if($row[2] == $cfgDisciplineType[$strDiscTypeHigh])
                {
                    $order_perf = "DESC";
                    $valid_result =    " AND (r.Info LIKE '%O%') ";
                    //$highjump = true;
                    $best_perf = ", max(r.Leistung) as Leistung";
                }
                else
                {
                    $order_perf = "ASC";
                    $best_perf = ", min(r.Leistung) as Leistung";
                }
                
                if($relay == FALSE) {    
                    // check if merged rounds                     
                    $sqlEvents = AA_getMergedEventsFromEvent($row[5]);
                    if (empty($sqlEvents)) {
                           $sqlSeparate = "ru.xRunde = " . $row[10];
                    }
                    else {
                          $sqlSeparate = "ss.RundeZusammen = " . $row[10];
                    }
                    // by merged rounds the result must be uploded separate 
                    $query = "
                        SELECT
                            ss.xSerienstart
                            , ss.Rang
                            , ss.Qualifikation
                            , r.Leistung
                            , r.Info
                            , s.Bezeichnung
                            , s.Wind
                            , r.Punkte
                            , v.Name
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang
                            , at.Land
                            , at.xAthlet
                            , at.Lizenznummer
                            , ru.Datum
                            , rt.Code as Typ
                            , at.Bezahlt
                            , at.Geburtstag
                            , at.Geschlecht
                            , v.xCode as Vereincode
                            , k.Code as Katathlet
                            , ru.xRunde
                            , s.Handgestoppt
                            , at.Lizenztyp
                            , a.Vereinsinfo
                            , rt.Typ
                            , ba.license_paid 
                            , if (ss.RundeZusammen > 0,ss.RundeZusammen,ru.xRunde) as spezRound     
                        FROM
                            runde as ru
                            LEFT JOIN serie AS s ON (ru.xRunde = s.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN verein AS v  ON (v.xVerein = at.xVerein  )
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON (ru.xRundentyp = rt.xRundentyp)
                            LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)
                            LEFT JOIN base_athlete AS ba ON (ba.license = at.Lizenznummer)
                        WHERE $sqlSeparate
                        AND ru.Status = ".$cfgRoundStatus['results_done']."
                        AND ru.StatusUpload = 0
                        AND r.Leistung >= " . $GLOBALS['cfgInvalidResult']['DNS']['code'] . "
                        
                        $valid_result
                        ORDER BY 
                            at.xAthlet
                            , ru.xRunde
                            , r.Leistung "
                            . $order_perf;                         
                }
                else {                        // relay event
                                
                    $query = "
                        SELECT
                            ss.xSerienstart
                            , ss.Rang
                            , ss.Qualifikation
                            , r.Leistung
                            , r.Info
                            , s.Bezeichnung
                            , s.Wind
                            , r.Punkte
                            , v.Name
                            , sf.Name
                            , sf.xStaffel
                            , ru.Datum
                            , rt.Code as Typ
                            , st.xStart
                            , ru.xRunde
                            , s.Handgestoppt
                            , ss.rundeZusammen
                            , k.Name
                            , k.Code  
                        FROM
                            runde as ru
                            LEFT JOIN serie AS s ON (s.xRunde = ru.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie  )
                            LEFT JOIN resultat AS r  ON (r.xSerienstart = ss.xSerienstart)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN staffel AS sf ON (sf.xStaffel = st.xStaffel)
                            LEFT JOIN verein AS v ON (v.xVerein = sf.xVerein)
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " as rt ON (ru.xRundentyp = rt.xRundentyp )
                            LEFT JOIN kategorie AS k ON (k.xKategorie = sf.xKategorie)
                        WHERE 
                            ru.xWettkampf = $row[5]                                 
                            AND ru.Status = ".$cfgRoundStatus['results_done']."
                            AND ru.StatusUpload = 0
                            AND r.Leistung > 0
                            AND v.xCode != ''
                            AND v.xCode != '999999'
                            $valid_result
                        GROUP BY
                            r.xSerienstart
                        ORDER BY
                            k.Name
                            , st.xStaffel
                            , ss.Rang
                             "
                            . $order_perf;    
                    
                        $res_teams = mysql_query($query);   
                     
                }            
                 
                $res_results = mysql_query($query);
               
                if(mysql_errno() > 0){
                    echo mysql_Error();
                }else{
                    if(mysql_num_rows($res_results) > 0 ){
                        
                        if ($row[3] != 796 && $row[3] != 797 && $row[3] != 798) {                  // ...lauf / ...sprung  / ...wurf       --> not send to AES 
                                                                
                                if (!$relay){
                                    $this->write_xml_open("discipline", array('sportDiscipline'=>$row[3], 'licenseCategory'=>$row[4]));        
                                    $this->write_xml_open("athletes");          
                                }
                        }
                    }
                
                    $id = 0;    // athletes id
                    $ru = 0;    // round id
                    $c = 0;                           
                
                    while($row_results = mysql_fetch_assoc($res_results)){    
                        if ($row['Mehrkampfcode'] >= 796 && $row['Mehrkampfcode'] <= 798 )   {                    // ...sprung / ...lauf / ...wurf     --> not send to AES
                             continue; 
                        }
                         if ($row['dCode'] >= 796 && $row['dCode'] <= 798 )   {                    // ...sprung / ...lauf / ...wurf     --> not send to AES
                             continue; 
                        }
                        if ($row_results['Leistung'] == -1){    
                            $notStartedMK[$row_results['xAthlet']] = true;  
                            continue;
                        }
                        
                      
                        // store round ids for later purpose
                        $GLOBALS['rounds'][] = $row_results['xRunde'];
                        
                        // set "rangzusatz"
                        /*switch($row_results['Typ']){
                            case "D":
                            break;
                            case "S":
                            $rankadd = "r";
                            break;
                            case "V":
                            $rankadd = "h";
                            break;
                            case "Z":
                            $rankadd = "qf";
                            break;
                            case "X":
                            $rankadd = "sf";
                            break;
                            case "Q":
                            $rankadd = "Q";
                            break;
                            case "F":
                            $rankadd = "A";
                            break;
                            default:
                            $rankadd = " ";
                        }*/
                        
                        $season = $_SESSION['meeting_infos']['Saison'];  
                        if ($saison == ''){
                            $saison = "O"; //if no saison is set take outdoor
                        }
                        $rankadd = " ";
                        
                        // set "no wind" flag if not measured or wind is equal "-"
                        if ($season == 'O'){                                            // only outdoor  (indoor: never a '*' )
                            if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])) {                                
                                if($row[1] == 0 || $row_results['Info'] == "-" || $row_results['Info'] == ""){
                                    $rankadd .= "*";
                                }
                            }
                            if(($row[2] == $cfgDisciplineType[$strDiscTypeTrack])){                                
                                if($row[1] == 0 || $row_results['Wind'] == "-" || $row_results['Wind'] == ""){
                                    $rankadd .= "*";
                                }
                            }
                        }
                        
                        // set "hand stopped" flag if set
                        if(($row[2] == $cfgDisciplineType[$strDiscTypeNone])
                            || ($row[2] == $cfgDisciplineType[$strDiscTypeTrack])
                            || ($row[2] == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                            || ($row[2] == $cfgDisciplineType[$strDiscTypeDistance])
                            || ($row[2] == $cfgDisciplineType[$strDiscTypeRelay]))
                        {
                            if($row_results['Handgestoppt'] == 1){
                                $rankadd .= "m";
                            }
                        }
                        
                        $rankadd .= $global_rankadd;
                        
                        if($relay){
                            //
                            // relay results
                            //
                            if($id != $row_results['xStaffel']){                                     
                                  // new relay
                                  $id = $row_results['xStaffel'];                                     
                                
                                  if ( $row_results[rundeZusammen] > 0 ){   
                                       if ($c == 0) {
                                           
                                              $this->write_xml_open("discipline", array('sportDiscipline'=>$row[3], 'licenseCategory'=>$row_results['Code']));   
                                              $this->write_xml_open("teams");      
                                           
                                              $lic_catName = $row_results['Name'];  
                                              $this->write_xml_open("team", array('teamCode'=>'S'));  
                                              $this->write_xml_finished("relayId",$id);     
                                              $this->write_xml_open("efforts");  
                                       }
                                       else {                                                  
                                            if ($lic_catName != $row_results['Name'] ){  
                                                $lic_catName = $row_results['Name'];   
                                                $this->write_xml_close("efforts"); 
                                                $this->write_xml_close("team");
                                                $this->write_xml_close("teams"); 
                                                $this->write_xml_close("discipline");    
                                                
                                                $this->write_xml_open("discipline", array('sportDiscipline'=>$row[dCode], 'licenseCategory'=>$row_results['Code']));
                                                $this->write_xml_open("teams"); 
                                                $this->write_xml_open("team", array('teamCode'=>'S'));
                                                $this->write_xml_finished("relayId",$id);
                                                $this->write_xml_open("efforts");  
                                            }
                                            else {
                                                   $this->write_xml_close("efforts"); 
                                                   $this->write_xml_close("team");
                                                   
                                                   $this->write_xml_open("team", array('teamCode'=>'S'));
                                                   $this->write_xml_finished("relayId",$id);
                                                   $this->write_xml_open("efforts");  
                                            } 
                                       }                                          
                                 }     
                                 else {                                         
                                        if ($c == 0) {  
                                                $this->write_xml_open("discipline", array('sportDiscipline'=>$row[3], 'licenseCategory'=>$row[4]));         
                                                $this->write_xml_open("teams");                                                       
                                        }     
                                 
                                        $this->close_open_tags("teams"); 
                                        
                                        $this->write_xml_open("team", array('teamCode'=>'S'));
                                        $this->write_xml_finished("relayId",$id);
                                        //staffel id                                                 
                                        $this->write_xml_open("efforts");    
                                }                                   
                            }
                            
                            $nbr_effort++;
                            $this->write_xml_open("effort");
                            
                            // add effort parameters
                            $this->write_xml_finished("DateOfEffort",$row_results['Datum']);
                            
                            if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeHigh])) {
                                $perf = AA_alabusDistance($row_results['Leistung']);
                                $this->write_xml_finished("distanceResult",$perf);
                            }else{
                                $perf = AA_alabusTime($row_results['Leistung']);
                                $this->write_xml_finished("timeResult",$perf);
                            }
                            
                            $wind = "";
                            if($row[1] == 1){
                                $wind = strtr($row_results['Wind'],",",".");
                            }else{
                                $wind = " ";
                            }
                            if(is_numeric($row_results['Bezeichnung'])){
                                $row_results['Bezeichnung'] = sprintf("%02s",$row_results['Bezeichnung']);
                            }else{
                                if(strlen($row_results['Bezeichnung']) == 1){
                                    $row_results['Bezeichnung'] .= "_";
                                }
                            }
                            // check on relevant for bestlist
                            $relevant = 1;
                            if($wind > "2"){
                                //$relevant = 0;
                            }
                            
                            if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeHigh])) {
                                    
                                   if ($row_results['Typ'] == 'F'){                                     // tech. discipline Final (no serie) 
                                          if (is_numeric($row_results['Bezeichnung'])){    
                                                $row_results['Bezeichnung'] = '';  
                                          }
                                   }            
                            }                        
                            if ($row_results['Typ'] == '0'){                 // (ohne)     
                                   $row_results['Bezeichnung'] = '';
                                }
                            if ($row_results['Typ'] == 'F'){                 // (ohne)     
                                   $row_results['Typ'] = '';
                                }
                            
                            //$this->write_xml_finished("timeResult"," ");
                            //$this->write_xml_finished("distanceResult"," ");
                            //$this->write_xml_finished("scoreResult"," ");
                            
                           
                            $this->write_xml_finished("wind",$wind);
                            $this->write_xml_finished("kindOfLap"," ".$row_results['Typ']);    // round type
                            $this->write_xml_finished("lap",$row_results['Bezeichnung']);        // heat name (A_, B_, 01, 02 ..)
                            $this->write_xml_finished("place",$row_results['Rang']);
                            $this->write_xml_finished("placeAddon",$rankadd);  
                            $this->write_xml_finished("indoor",$indoor); 
                            $this->write_xml_finished("relevant",$relevant);   
                            
                            // get athletes for effort details
                            $cRelayAt = 4;
                            if($row[9] > 0){ // staffelläufer count of discipline
                                $cRelayAt = $row[9];
                            }    
                            
                            $query = "
                                SELECT 
                                    at.Name, 
                                    at.Vorname, 
                                    st.Position 
                                FROM
                                    staffelathlet as st
                                    LEFT JOIN start as s  ON (st.xAthletenstart = s.xStart)
                                    LEFT JOIN anmeldung as a ON (s.xAnmeldung = a.xAnmeldung)
                                    LEFT JOIN athlet as at ON (a.xAthlet = at.xAthlet)
                                WHERE 
                                    st.xStaffelstart = ".$row_results['xStart']."
                                    AND (st.xRunde = ".$row_results['xRunde']."  OR st.xRunde = ".$row_results['rundeZusammen'].")                                
                                ORDER BY
                                    st.Position ASC
                                LIMIT $cRelayAt";       
                            
                            $res_relayat = mysql_query($query);       
                            
                            if(mysql_errno() > 0){
                                echo mysql_error();
                            }else{
                                $eDetails = "";
                                while($row_relayat = mysql_fetch_assoc($res_relayat)){
                                    $eDetails .= trim($row_relayat['Name'])." ".trim($row_relayat['Vorname'])." / ";
                                }
                                $eDetails = substr($eDetails, 0,strlen($eDetails)-3);
                                $this->write_xml_finished("effortDetails",$eDetails);
                            }
                            
                            $this->write_xml_close("effort");                            
                            
                        }else{  
                            //
                            // athlete results
                            //
                            if($ru == $row_results['xRunde'] && $id == $row_results['xAthlet']){
                                continue;
                            }
                            $ru = $row_results['xRunde'];
                            
                            // array for ordering (order after WO) combined events
                            $combinedPriority = 0;
                            /*$combinedPrio = $cfgCombinedWO[$cfgCombinedDef[$row[7]]];
                            $combinedPriority = array_keys($combinedPrio, $row[3]);
                            if(count($combinedPriority) > 0){
                                $combinedPriority = $combinedPriority[0];
                            }else{
                                $combinedPriority = 999; // not a official discipline for this combined event
                            }*/
                            
                            if($id != $row_results['xAthlet']){
                                // new athlete
                                $id = $row_results['xAthlet'];
                                $this->close_open_tags("athletes");
                                
                                // if athlete is not from switzerland filter him but add to combined array for correct ranking
                                // the same for athletes without license (type 3)
                                // these rules have also to be present in the parsing section of combined events (before and after result loop)
                                
                                /*if($row_results['Vereincode'] == '' || $row_results['Vereincode'] == '999999'
                                    || $row_results['Lizenztyp'] == 3){*/
                                if($row_results['Lizenztyp'] == 3){
                                    if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){
                                        $rank = " ";
                                        $row_results['Bezeichnung'] = " ";
                                        //
                                        //add points for combined contests                                            
                                        if($combined[$row_results['xAthlet']][$row[3]]['points'] < $row_results['Punkte']){
                                            
                                            $combined[$row_results['xAthlet']][$row[3]] = array('kindOfLap'=>" ".$row_results['Typ'],
                                                'lap'=>$row_results['Bezeichnung'], 'placeAddon'=>$rankadd, 'indoor'=>$indoor,
                                                'points'=>$row_results['Punkte'],
                                                'discipline'=>$row[6], 'license'=>$row_results['Lizenznummer'],  'xAthlet'=>$row_results['xAthlet'],
                                                'DateOfEffort'=>$row_results['Datum'],
                                                'lastName'=>$row_results['Name'], 'firstName'=>$row_results['Vorname'], 
                                                'birthDate'=>$birthday, 'sex'=>$row_results['Geschlecht'], 
                                                'nationality'=>$row_results['Land'], 
                                                'accountCode'=>$row_results['Vereincode'],
                                                'priority'=>$combinedPriority, 'licenseType'=>$row_results['Lizenztyp'] );
                                            
                                            // category of athlete, used for calculating the rankings
                                            $combined[$row_results['xAthlet']]['catathlete']= $row_results['Katathlet'];                                             
                                        }
                                    }
                                    $id = 0; // if this is not set, results for the skipped athlete will be written
                                    continue; // next result/athlete
                                }
                                
                                
                                // license_paid = license printed (information from basa data)
                                // only upload results from athletes with license available (=license printed)    
                                if($row_results['Lizenztyp'] <= 1 && $row_results['license_paid'] == 'n'){                                     
                                    $id = 0; // if this is not set, results for the skipped athlete will be written
                                    continue; // next result/athlete
                                }
                               
                                if(!empty($row_results['Lizenznummer'])){
                                    $inMasterData = 1;
                                    $licensePaid = 1;
                                }else{                                    
                                    $inMasterData = 0;
                                    if($row_results['Bezahlt'] == 'y'){
                                        $licensePaid = 1;
                                    }else{
                                        $licensePaid = 0;
                                    }
                                }
                                //$this->close_open_tags("athletes");
                                
                               
                                $this->write_xml_open("athlete", array('license'=>$row_results['Lizenznummer'], 'licensePaid'=>$licensePaid
                                        , 'licenseCat'=>'', 'inMasterData'=>$inMasterData));   
                                
                                // write athletes data if athletica generated
                                //
                                if(!$inMasterData){
                                    if($row_results['Land'] == "-"){ $row_results['Land'] = " "; }
                                    
                                    $this->write_xml_finished("lastName", $row_results['Name']);
                                    $this->write_xml_finished("firstName", $row_results['Vorname']);
                                    $birthday = $row_results['Geburtstag'];
                                    if($birthday == "0000-00-00"){
                                        $birthday = $row_results['Jahrgang']."-01-01";
                                    }
                                    $this->write_xml_finished("birthDate", $birthday);
                                    $this->write_xml_finished("sex", $row_results['Geschlecht']);
                                    $this->write_xml_finished("nationality", $row_results['Land']);
                                    $this->write_xml_finished("accountCode", $row_results['Vereincode']);
                                    $this->write_xml_finished("secondaccountCode", " ");
                                }
                                        
                                $this->write_xml_open("efforts");
                            }
                              
                            $perf = 0; // result for alabus
                            $wind = "";
                            $perfRounded = 0; // result for combined detail text
                            
                            $nbr_effort++;
                            $this->write_xml_open("effort");
                            
                            // add effort parameters
                            $this->write_xml_finished("DateOfEffort",$row_results['Datum']);
                            
                            $wind = "";
                            if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeHigh])) {
                                $perf = AA_alabusDistance($row_results['Leistung']);
                                $perfRounded = AA_formatResultMeter($row_results['Leistung']);
                                $this->write_xml_finished("distanceResult",$perf);
                                $wind = strtr($row_results['Info'],",",".");
                            }else{
                                $perf = AA_alabusTime($row_results['Leistung']);                                    
                                $perfRounded = AA_formatResultTime($row_results['Leistung'], true);
                                $this->write_xml_finished("timeResult",$perf);
                                $wind = strtr($row_results['Wind'],",",".");
                            }                            
                            
                            if($row[1] == 0 || $wind == "-" || $wind == ""){
                                $wind = " ";
                            }
                            
                            if(is_numeric($row_results['Bezeichnung'])){
                                $row_results['Bezeichnung'] = sprintf("%02s",$row_results['Bezeichnung']);
                            }else{
                                if(strlen($row_results['Bezeichnung']) == 1){
                                    $row_results['Bezeichnung'] .= "_";
                                }
                            }
                            
                            if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){
                                //$rankadd = "D)".$rankadd;
                                if($wind > 4){ // if any result has a wind of over 4 m/s, the combined result gets a flag 'w'
                                    $rankadd .= "w";
                                }
                                $rank = " ";
                                $row_results['Bezeichnung'] = " ";
                                //
                                //add points for combined contests     
                                if($combined[$row_results['xAthlet']][$row[3]]['points'] < $row_results['Punkte']){
                                            
                                            $combined[$row_results['xAthlet']][$row[3]] = array('wind'=>$wind, 'kindOfLap'=>" ".$row_results['Typ'],
                                                'lap'=>$row_results['Bezeichnung'], 'placeAddon'=>$rankadd, 'indoor'=>$indoor, 'points'=>$row_results['Punkte'],
                                                'effort'=>$perfRounded, 'discipline'=>$row[6], 'license'=>$row_results['Lizenznummer'], 'xAthlet'=>$row_results['xAthlet'], 
                                                'inMasterData'=>$inMasterData, 'licensePaid'=>$licensePaid, 'DateOfEffort'=>$row_results['Datum'],
                                                'lastName'=>$row_results['Name'], 'firstName'=>$row_results['Vorname'], 
                                                'birthDate'=>$birthday, 'sex'=>$row_results['Geschlecht'], 'nationality'=>$row_results['Land'], 
                                                'accountCode'=>$row_results['Vereincode'], 'priority'=>$combinedPriority, 
                                                'licenseType'=>$row_results['Lizenztyp']);
                                            
                                            // category of athlete, used for calculating the rankings
                                            $combined[$row_results['xAthlet']]['catathlete']= $row_results['Katathlet'];
                                }   
                                
                            }else{
                                $rank = $row_results['Rang'];
                            }    
                            
                            // check on relevant for bestlist
                            $relevant = 1;     
                            
                           if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeHigh])) {
                                    
                                   if ($row_results['Typ'] == 'F'){                                     // tech. discipline Final (no serie)
                                          if (is_numeric($row_results['Bezeichnung'])){    
                                                $row_results['Bezeichnung'] = '';  
                                          }
                                   }            
                            }                        
                                                                                             
                            if ($row_results['Typ'] == '0'){                 // (ohne)                                      
                                   $row_results['Bezeichnung'] = '';
                            }
                            if ($row_results['Typ'] == 'F'){                 // (ohne)                                      
                                   $row_results['Typ'] = '';
                            }
                            
                            // output result data
                            
                            // temp. solution (wind only with 4 characters)
                            $windEnd= substr($wind,-1);
                            if ($windEnd == 'm'){
                                $wind= substr($wind,0,-1);
                            }
                            $this->write_xml_finished("wind",$wind);
                            $this->write_xml_finished("kindOfLap"," ".$row_results['Typ']);    // round type
                            $this->write_xml_finished("lap",$row_results['Bezeichnung']);    // heat name (A_, B_, 01, 02 ..)  
                            $this->write_xml_finished("place",$rank);
                            $this->write_xml_finished("placeAddon",$rankadd); 
                            $this->write_xml_finished("indoor",$indoor);
                            $this->write_xml_finished("relevant",$relevant);
                            $this->write_xml_finished("effortDetails"," ");
                            $this->write_xml_finished("accountinfo", " ".$row_results['Vereinsinfo']);
                            $this->write_xml_finished("homologate", "1");     // not yet implemented -> TODO
                            
                            $this->write_xml_close("effort");   
                            
                            if($wind > "2" && $row[2] == $cfgDisciplineType[$strDiscTypeJump]){
                                // since we get only the best result per xSerienstart,
                                // here we'll get the next with valid wind
                                $res_wind = mysql_query("
                                        SELECT Info, Leistung FROM
                                            resultat
                                        WHERE
                                            xSerienstart = ".$row_results['xSerienstart']."
                                        ORDER BY
                                            Leistung DESC");
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }else{
                                    while($row_wind = mysql_fetch_array($res_wind)){
                                        
                                        if($row_wind[0] <= 2){   
                                            
                                            $perf = AA_alabusDistance($row_wind[1]);
                                             if ($perf == -98){                             // -98 = Fehlversuch
                                               break; 
                                            }
                                            $nbr_effort++;
                                            $this->write_xml_open("effort");
                                            $this->write_xml_finished("DateOfEffort",$row_results['Datum']);
                                            
                                            $this->write_xml_finished("distanceResult",$perf);
                                            
                                            $wind = strtr($row_wind[0],",",".");
                                            if($wind == "-"){ $wind = " "; }
                                            $this->write_xml_finished("wind",$wind);  
                                            $this->write_xml_finished("kindOfLap"," ".$row_results['Typ']);    // round type
                                            $this->write_xml_finished("lap",$row_results['Bezeichnung']);    // heat name (A_, B_, 01, 02 ..)
                                            $this->write_xml_finished("place"," ");
                                            $this->write_xml_finished("placeAddon",$rankadd); 
                                            $this->write_xml_finished("indoor",$indoor);
                                            $this->write_xml_finished("relevant",$relevant);
                                            $this->write_xml_finished("effortDetails"," ");
                                            $this->write_xml_close("effort");
                                            break;
                                        }
                                        
                                    }
                                }
                            }// end if wind > 2
                        } // end if relay or athlete
                        
                        $c++;
                        
                    } // end while res_results
                   
                }
                  
                $this->close_open_tags("disciplines");
                
              $keep_dCode = $row[3];  
              $keep_kCode = $row[4];   
              $keep_Event= $row[5];  
              $keep_wCat = $row[8];   
               
            }
            
            // check on last combined event                   
           // if(!empty($combined) && $combined_dis < 9000){ // combined codes 9000 and above are self made disciplines
           if(!empty($combined) && $combined_dis < 9000 && $combined_dis != 796 && $combined_dis != 797 && $combined_dis != 798 && $combined_dis != 799) {                   
                    $this->write_xml_open("discipline", array('sportDiscipline'=>$combined_dis, 'licenseCategory'=>$combined_cat));
                    $this->write_xml_open("athletes");
                    
                    // calc points
                    foreach($combined as $xathlet => $disc){
                        $points = 0;
                        $eDetails = "";
                        $tmp_fe = $fetched_events;
                        
                        foreach($disc as $xdisc => $tmp){
                            if($xdisc == "catathlete"){ continue; }
                            
                            // check if there are events missing for the current athlete and add 'null' entries
                            while($tmp_fe[0][3] != $xdisc){
                                //$eDetails .= $tmp_fe[0][6]." (0); ";
                                $eDetails .= "0/";
                                array_shift($tmp_fe);
                            }
                            array_shift($tmp_fe);
                            
                            $points += $tmp['points'];
                            if($tmp['wind'] == " "){
                                $tmp['wind'] = "";
                            }else{
                                //$tmp['wind'] = " / ".$tmp['wind'];
                                if($tmp['wind'] >= 0){
                                    $tmp['wind'] = "+".$tmp['wind'];
                                }else{
                                    $tmp['wind'] = $tmp['wind'];
                                }
                            }
                            //$eDetails .= $tmp['discipline']." (".$tmp['effort'].$tmp['wind']."); ";
                            $eDetails .= $tmp['effort'].$tmp['wind']."/";
                        }
                        
                        // check if last events are missing
                        while(isset($tmp_fe[0][3])){
                            $eDetails .= "0/";
                            array_shift($tmp_fe);
                        }
                        
                        $eDetails = substr($eDetails, 0, -1);
                        $combined[$xathlet]['points'] = $points;
                        $combined[$xathlet]['edetails'] = $eDetails;
                    }
                    // sort for points
                    usort($combined, array($this, "sort_combined"));
                    
                    // write
                    //$rank = array();
                    //$curr_athlete_cat = "";
                    $rank = 0;    // athletes rank
                    $cRank = 0;    // rank counter
                    $lp = 0;    // remembers points of last athlete
                    foreach($combined as $xathlet => $disc){
                        $this->close_open_tags("athletes");
                        
                        // count place for each athlete category
                        /*$curr_athlete_cat = $combined[$xathlet]['catathlete'];
                        if(!isset($rank[$curr_athlete_cat])){
                            $rank[$curr_athlete_cat] = 1;
                        }else{
                            $rank[$curr_athlete_cat]++;
                        }*/
                        $cRank++;
                        if($lp != $disc['points']){
                            $rank = $cRank;
                            $lp = $disc['points'];
                        }
                        
                        // get information for athlete
                        $tmp = $disc;
                        $tmp['points'] = null;
                        $tmp['edetails'] = null;
                        $tmp['catathlete'] = null;
                        $tmp = array_values($tmp);
                        usort($tmp, array($this, "sort_perdate"));
                        $tmp = $tmp[0];
                        
                        // filter athletes not from switzerland and athletes without license
                        if($tmp['accountCode'] == '' || $tmp['accountCode'] == '999999' || $tmp['licenseType'] == 3){
                            continue;
                        }    
                            
                        if (!$notStartedMK[$tmp['xAthlet']]) {     // Athlet is not started for one disziplin --> not send the combined event points     
                        
                            $this->write_xml_open("athlete", array('license'=>$tmp['license'], 'licensePaid'=>$tmp['licensePaid']
                                    , 'licenseCat'=>'', 'inMasterData'=>$tmp['inMasterData']));
                            
                            if(!$tmp['inMasterData']){
                                $this->write_xml_finished("lastName", $tmp['lastName']);
                                $this->write_xml_finished("firstName", $tmp['firstName']);
                                
                                $this->write_xml_finished("birthDate", $tmp['birthDate']);
                                $this->write_xml_finished("sex", $tmp['sex']);
                                $this->write_xml_finished("nationality", $tmp['nationality']);
                                $this->write_xml_finished("accountCode", $tmp['accountCode']);
                                $this->write_xml_finished("secondaccountCode", " ");
                            }
                            
                            $this->write_xml_open("efforts");
                            
                            $nbr_effort;
                            $this->write_xml_open("effort");
                            $this->write_xml_finished("DateOfEffort",$tmp['DateOfEffort']);
                            $this->write_xml_finished("scoreResult",AA_alabusScore($disc['points']));
                            $this->write_xml_finished("wind"," ");
                            $this->write_xml_finished("kindOfLap"," ");    // round type combined (D)
                            $this->write_xml_finished("lap"," ");        // heat name (A_, B_, 01, 02 ..)
                            //$this->write_xml_finished("place",$rank[$curr_athlete_cat]);
                            $this->write_xml_finished("place",$rank);
                            $this->write_xml_finished("placeAddon",$tmp['placeAddon']);
                             $this->write_xml_finished("indoor",$tmp['indoor']);   
                            $this->write_xml_finished("relevant","1");
                            $this->write_xml_finished("effortDetails",$disc['edetails']);
                            $this->write_xml_close("effort");
                        
                        }
                        
                    }
                    
                    $this->close_open_tags("disciplines");
                }
                
            $combined = array();
            
            // get the svm results
            mysql_free_result($res);
            $res = mysql_query("
                SELECT
                    w.Typ,
                    w.Windmessung,
                    d.Typ,
                    d.Code,
                    k.Code,
                    w.xWettkampf,
                    d.Kurzname,
                    w.Mehrkampfcode,
                    w.xKategorie,
                    ks.Code,
                    MAX(r.Datum)
                FROM 
                    runde as r
                LEFT JOIN 
                    wettkampf as w USING(xWettkampf) 
                LEFT JOIN
                    disziplin_" . $_COOKIE['language'] . " as d ON d.xDisziplin = w.xDisziplin
                LEFT JOIN
                    kategorie as k ON k.xKategorie = w.xKategorie
                LEFT JOIN
                    kategorie_svm as ks ON ks.xKategorie_svm = w.xKategorie_svm
                WHERE    xMeeting = ".$_COOKIE['meeting_id']."
                GROUP BY w.xKategorie
                ORDER BY
                    w.xWettkampf
            ");
           
            if(mysql_errno() > 0){
                
                echo(mysql_errno().": ".mysql_error());
            }else{
               
                while($row = mysql_fetch_array($res)){
                    //
                    // open rankinlist_team lib for calculating the svm points
                    //
                    if($row[0] > $cfgEventType[$strEventTypeSingleCombined]){
                        $this->write_xml_open("discipline", array('sportDiscipline'=>$row[9], 'licenseCategory'=>$row[4]));
                        $this->write_xml_open("teams");
                        
                        $GLOBALS['doe'] = $row[10]; // date of team effort (last round date)
                        $GLOBALS['rankadd'] = $global_rankadd;                       
                        AA_rankinglist_Team($row[8], 'xml', "", false, $this);                         
                        $this->close_open_tags("disciplines");
                    }
                }
            }
            
            // close last tags
            $this->close_open_tags();
        }
        
        $this->gzip_close();
       
        return $nbr_effort;
    }
    
// generate results UBS kidscup for upload
//
function gen_result_xml_UKC($file){ 
         //function returns containing number of results in xml-file
        $nbr_effort=0;                  
       
        global $cfgDisciplineType, $cfgEventType, $strEventTypeSingleCombined, 
            $strEventTypeClubCombined, $strDiscTypeTrack, $strDiscTypeTrackNoWind, 
            $strDiscTypeRelay, $strDiscTypeDistance, $cfgRoundStatus,
            $strDiscTypeJump, $strDiscTypeJumpNoWind, $strDiscTypeThrow, $strDiscTypeHigh,
            $cfgCombinedWO, $cfgCombinedDef;
        
        $this->gzip_open($file);
        
        // begin xml
        $this->write_xml_open("kidDataset", array('version'=>date('y-m-d')));
        $this->write_xml_open("event");
        
        //
        // output contest data
        //   
        $indoor = '0';                    
        $query = "
            SELECT
                m.*
                , s.Name as Stadion
                , s.Ueber1000m
                , s.Halle
            FROM 
                meeting as m 
                LEFT JOIN stadion as s ON m.xStadion = s.xStadion 
            WHERE xMeeting = ".$_COOKIE['meeting_id'];
        $res = mysql_query($query);
        if(mysql_errno() > 0){
            echo(mysql_errno().": ".mysql_error());
        }else{
            $row = mysql_fetch_assoc($res);
            mysql_free_result($res);
            
            $this->write_xml_finished("eventNumber",$row['Nummer']);                      // not xControl for UBS kidscup
            $this->write_xml_finished("name",str_replace('&', '&amp;', $row['Name']));
            $this->write_xml_finished("eventStart",$row['DatumVon']);
            $this->write_xml_finished("eventEnd",$row['DatumBis']);
            $this->write_xml_finished("location",$row['Ort']);
            $this->write_xml_finished("stadium",$row['Stadion']);
            $this->write_xml_finished("amountSpectators"," ");
            
            $this->write_xml_open("athletes");  
            
            if($row['Ueber1000m'] == 'y'){ $global_rankadd = "A"; }               
            if($row['Saison'] == 'I'){ $indoor = "1"; }                      // 1 = inddor , 0 = outdoor 
        }
                        
        //
        // get all disciplines
        //  
        $sql="  SELECT
                    w.Typ,
                    w.Windmessung,
                    d.Typ,
                    d.Code,
                    k.Code,
                    w.xWettkampf,
                    d.Kurzname,
                    w.Mehrkampfcode,
                    w.xKategorie,
                    d.Staffellaeufer,  
                    r.xRunde
                FROM 
                    wettkampf as w 
                    LEFT JOIN runde as r ON (w.xWettkampf = r.xWettkampf)  
                LEFT JOIN
                    disziplin_" . $_COOKIE['language'] . " as d ON d.xDisziplin = w.xDisziplin
                LEFT JOIN
                    kategorie as k ON k.xKategorie = w.xKategorie     
                WHERE r.xRunde > 0 AND  
                    w.xMeeting = ".$_COOKIE['meeting_id']."                  
                ORDER BY
                    k.Code
                    , w.xKategorie
                    , w.Mehrkampfcode   
                    , r.Gruppe                 
                    , w.Mehrkampfreihenfolge";     
               // the order "k.Code, w.xKategorie" makes sense if there are multiple self made categories (without any code)        
        $res = mysql_query($sql);
       
        if(mysql_errno() > 0){              
            echo(mysql_errno().": ".mysql_error());
        }else{
            
            $current_type = "";
            $combined = "";
            $current_cat = "";
            $current_xcat = 0;
            $fetched_events = array(); // used for combined events
                        // if an athlete has no result for one discipline of a combined event,
                        // the detail text has to include a 'null' result
            $GLOBALS['rounds'] = array();
            
            while($row = mysql_fetch_array($res)){    
                //
                // generate results for combined events
                //
                if($current_xcat != $row[8] || $combined_dis != $row[7]){ // cat or combcode changed, print combined results    
                   
                    if(!empty($combined) && $combined_dis == 408) {         // only UBS kidscup                        
                        
                        // calc points
                        foreach($combined as $xathlet => $disc){
                            $points = 0;
                            $eDetails = "";
                            $tmp_fe = $fetched_events; // temp array for fetched events
                            
                            foreach($disc as $xdisc => $tmp){
                                if($xdisc == "catathlete"){ continue; }
                                
                                // check if there are events missing for the current athlete and add 'null' entries
                                while($tmp_fe[0][3] != $xdisc){                                             
                                    $eDetails .= "0/";
                                    array_shift($tmp_fe);
                                }
                                array_shift($tmp_fe);
                                
                                $points += $tmp['points'];
                                if($tmp['wind'] == " "){
                                    $tmp['wind'] = "";
                                }else{
                                    if($tmp['wind'] >= 0){
                                        $tmp['wind'] = "+".$tmp['wind'];
                                    }else{
                                        $tmp['wind'] = $tmp['wind'];
                                    }
                                }                                       
                               $eDetails .= $tmp['effort']."/";   
                                
                            }
                            
                            // check if last events are missing
                            while(isset($tmp_fe[0][3])){
                                $eDetails .= "0/";
                                array_shift($tmp_fe);
                            }
                            
                            $eDetails = substr($eDetails, 0, -1);
                            $combined[$xathlet]['points'] = $points;
                            $combined[$xathlet]['edetails'] = $eDetails;
                        }
                        // sort for points
                        usort($combined, array($this, "sort_combined"));
                        
                        // write                                   
                        $rank = 0;    // athletes rank
                        $cRank = 0;    // rank counter
                        $lp = 0;    // remembers points of last athlete
                        foreach($combined as $xathlet => $disc){    
                                 
                            // count place for each athlete category    
                            $cRank++;
                            if($lp != $disc['points']){
                                $rank = $cRank;
                                $lp = $disc['points'];
                            }
                            
                            // get information for athlete, remove not needed information and sort per DateOfEffort
                            $tmp = $disc;
                            $tmp['points'] = null;
                            $tmp['edetails'] = null;
                            $tmp['catathlete'] = null;
                            $tmp = array_values($tmp);
                            usort($tmp, array($this, "sort_perdate"));
                            $tmp = $tmp[0];       
                            
                            $this->write_xml_open("athlete", array('license'=>$tmp['license'], 'kidID'=>$tmp['kidID'] ));    
                            
                            $nbr_effort++;                                    
                            
                            list ($run, $jump, $throw ) = split('[/]', $disc['edetails']);
                           
                            $this->write_xml_open("efforts");      
                            $this->write_xml_finished("run",$run); 
                            $this->write_xml_finished("throw",$throw); 
                            $this->write_xml_finished("jump",$jump);   
                            $this->write_xml_finished("totalPoints",AA_alabusScore($disc['points']));   
                            $this->write_xml_finished("position",$rank);    
                            $this->write_xml_close("efforts");             
                                                                                                 
                            if (empty($tmp['license']) && empty($tmp['kidID'])){   
                                $this->write_xml_open("personalData");     
                                 
                                $this->write_xml_finished("lastName", $tmp['lastName']);
                                $this->write_xml_finished("firstName", $tmp['firstName']);                                 
                                $this->write_xml_finished("ageGroup", $tmp['birthDate']);
                                $this->write_xml_finished("sex", $tmp['sex']);
                                $this->write_xml_finished("street", $tmp['adress']);
                                $this->write_xml_finished("zipCode", $tmp['plz']);
                                $this->write_xml_finished("city", $tmp['city']); 
                                $this->write_xml_finished("club", $tmp['accountName']);  
                                $this->write_xml_finished("email", $tmp['email']); 
                                $this->write_xml_finished("canton", $tmp['canton']);
                                
                                $this->write_xml_close("personalData");    
                            }                                                                      
                             $this->write_xml_close("athlete");  
                        }    
                    }
                    $combined = array();
                    $fetched_events = array();
                    $combined_dis = $row[7];
                    $combined_cat = $row[4];
                    $current_cat = $row[4];
                    $current_xcat = $row[8];
                }           
                
                // keep events rows of combined events to check on missing results after
                if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){
                    $fetched_events[] = $row;
                }              
                               
                //
                // first of all, print all single results (athletes and relays)
                //         
                $order_perf = "";
                $valid_result = "";                
                
                if(($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                    || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow]))
                {
                    $order_perf = "DESC";                          
                }
                else if($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                {
                    if ($row[1] == 1) {            // with wind
                        //$order_perf = "DESC, r.Info ASC";
                        $order_perf = "DESC";
                    }
                    else {                            // without wind
                        $order_perf = "DESC";
                    }                             
                }
                else if($row[2] == $cfgDisciplineType[$strDiscTypeHigh])
                {
                    $order_perf = "DESC";
                    $valid_result =    " AND (r.Info LIKE '%O%') ";    
                }
                else
                {
                    $order_perf = "ASC";                              
                }
                
            
                // check if merged rounds                     
                $sqlEvents = AA_getMergedEventsFromEvent($row[5]);
                if (empty($sqlEvents)) {
                           $sqlSeparate = "ru.xRunde = " . $row[10];
                }
                else {
                          $sqlSeparate = "ss.RundeZusammen = " . $row[10];
                }
                // by merged rounds the result must be uploded separate 
                $query = "
                        SELECT
                            ss.xSerienstart
                            , ss.Rang
                            , ss.Qualifikation
                            , r.Leistung
                            , r.Info
                            , s.Bezeichnung
                            , s.Wind
                            , r.Punkte
                            , v.Name
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang
                            , at.Land
                            , at.xAthlet
                            , at.Lizenznummer
                            , ru.Datum
                            , rt.Code as Typ
                            , at.Bezahlt
                            , at.Geburtstag
                            , at.Geschlecht
                            , v.xCode as Vereincode
                            , k.Code as Katathlet
                            , ru.xRunde
                            , s.Handgestoppt
                            , at.Lizenztyp
                            , a.Vereinsinfo
                            , rt.Typ
                            
                            , if (ss.RundeZusammen > 0,ss.RundeZusammen,ru.xRunde) as spezRound     
                            , a.kidID
                            , at.Adresse
                            , at.Plz
                            , at.Ort
                            , at.Email  
                            , re.Anzeige as Kanton   
                            , v.Name as Vereinname
                        FROM
                            runde as ru
                            LEFT JOIN serie AS s ON (ru.xRunde = s.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN verein AS v  ON (v.xVerein = at.xVerein  )
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON (ru.xRundentyp = rt.xRundentyp)
                            LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)                             
                            LEFT JOIN region AS re ON (re.xRegion = at.xRegion) 
                        WHERE $sqlSeparate
                        AND ru.Status = ".$cfgRoundStatus['results_done']."
                        AND ru.StatusUpload = 0
                        AND r.Leistung >= " . $GLOBALS['cfgInvalidResult']['DNS']['code'] . "
                        
                        $valid_result
                        ORDER BY 
                            at.xAthlet
                            , ru.xRunde
                            , r.Leistung "
                            . $order_perf;      
            
                $res_results = mysql_query($query);      
               
                if(mysql_errno() > 0){                              
                    echo mysql_Error();
                }else{                        
                
                    $id = 0;    // athletes id
                    $ru = 0;    // round id
                   
                    while($row_results = mysql_fetch_assoc($res_results)){                            
                        if ($row_results['Leistung'] == -1){  
                            continue;
                        }
                        // store round ids for later purpose
                        $GLOBALS['rounds'][] = $row_results['xRunde'];   
                       
                       //
                       // athlete results
                       //
                       if($ru == $row_results['xRunde'] && $id == $row_results['xAthlet']){
                               
                                continue;
                       }
                       $ru = $row_results['xRunde'];    
                            
                       if($id != $row_results['xAthlet']){
                                // new athlete
                                $id = $row_results['xAthlet'];                                                                 
                               
                                if(empty($row_results['Lizenznummer']) && empty($row_results['kidID'])){                                        
                                    $inMasterData = 1;
                                    $licensePaid = 1;
                                }else{                                    
                                    $inMasterData = 0;                                          
                                    if($row_results['Bezahlt'] == 'y'){
                                        $licensePaid = 1;
                                    }else{
                                        $licensePaid = 0;
                                    }
                                }   
                       }
                              
                       $perf = 0; // result for alabus
                       $wind = "";
                       $perfRounded = 0; // result for combined detail text    
                            
                        // add effort parameters   
                            
                        $wind = "";
                        if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeHigh])) {
                                $perf = AA_alabusDistance($row_results['Leistung']);
                                $perfRounded = AA_formatResultMeter($row_results['Leistung']);      
                                $wind = strtr($row_results['Info'],",",".");
                        }else{
                                $perf = AA_alabusTime($row_results['Leistung']);                                    
                                $perfRounded = AA_formatResultTime($row_results['Leistung'], true);  
                                $wind = strtr($row_results['Wind'],",",".");
                        }                            
                            
                        if($row[1] == 0 || $wind == "-" || $wind == ""){
                                $wind = " ";
                        }
                               
                        if(is_numeric($row_results['Bezeichnung'])){
                                $row_results['Bezeichnung'] = sprintf("%02s",$row_results['Bezeichnung']);
                        }else{
                                if(strlen($row_results['Bezeichnung']) == 1){
                                    $row_results['Bezeichnung'] .= "_";
                                }
                        }
                            
                        if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){     
                               
                                $rank = " ";
                                $row_results['Bezeichnung'] = " ";
                                //
                                //add points for combined contests                                 
                                if($combined[$row_results['xAthlet']][$row[3]]['points'] < $row_results['Punkte']){                                               
                                    $license = $row_results['Lizenznummer'];
                                    if ($row_results['Lizenznummer'] == 0){
                                        $license = '';
                                    }
                                    
                                    $kidsID_upload = $row_results['kidID'];
                                    if ($row_results['kidID'] == 0){
                                        $kidsID_upload = '';
                                    }
                                    
                                    $combined[$row_results['xAthlet']][$row[3]] = array('wind'=>$wind, 'kindOfLap'=>" ".$row_results['Typ'],
                                        'lap'=>$row_results['Bezeichnung'], 'placeAddon'=>$rankadd, 'indoor'=>$indoor, 'points'=>$row_results['Punkte'],
                                        'effort'=>$perfRounded, 'discipline'=>$row[6], 'license'=>$license, 'kidID'=>$kidsID_upload,
                                        'inMasterData'=>$inMasterData, 'licensePaid'=>$licensePaid, 'DateOfEffort'=>$row_results['Datum'],
                                        'lastName'=>htmlspecialchars($row_results['Name']), 'firstName'=>htmlspecialchars($row_results['Vorname']), 
                                        'birthDate'=>$row_results['Jahrgang'], 'sex'=>strtoupper ( $row_results['Geschlecht']), 'nationality'=>$row_results['Land'], 
                                        'adress'=>htmlspecialchars($row_results['Adresse']),  'plz'=>$row_results['Plz'],  'city'=>htmlspecialchars($row_results['Ort']),
                                         'email'=>$row_results['Email'],   'canton'=>$row_results['Kanton'], 
                                        'accountName'=>htmlspecialchars($row_results['Vereinname']), 'priority'=>$combinedPriority, 
                                        'licenseType'=>$row_results['Lizenztyp']);
                                    
                                    // category of athlete, used for calculating the rankings
                                    $combined[$row_results['xAthlet']]['catathlete']= $row_results['Katathlet'];
                                }
                        }
                            
                        // check on relevant for bestlist
                        $relevant = 1;                             
                               
                        if ($row_results['Typ'] == '0'){                 // (ohne)                                      
                                   $row_results['Bezeichnung'] = '';
                        }
                            
                        // output result data
                            
                        if($wind > "2" && $row[2] == $cfgDisciplineType[$strDiscTypeJump]){
                                // since we get only the best result per xSerienstart,
                                // here we'll get the next with valid wind
                                $res_wind = mysql_query("
                                        SELECT Info, Leistung FROM
                                            resultat
                                        WHERE
                                            xSerienstart = ".$row_results['xSerienstart']."
                                        ORDER BY
                                            Leistung DESC");
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }else{
                                    while($row_wind = mysql_fetch_array($res_wind)){
                                        
                                        if($row_wind[0] <= 2){   
                                            
                                            $perf = AA_alabusDistance($row_wind[1]);
                                             if ($perf == -98){                             // -98 = Fehlversuch
                                               break; 
                                            }
                                            $nbr_effort++;
                                           
                                            break;
                                        }
                                        
                                    }
                                }
                        }// end if wind > 2      
                        
                    } // end while res_results    
                }         
            }
           
            // check on last combined event             
            if(!empty($combined) && $combined_dis < 9000){ // combined codes 9000 and above are self made disciplines                   
                    
                    // calc points
                    foreach($combined as $xathlet => $disc){                            
                        $points = 0;
                        $eDetails = "";
                        $tmp_fe = $fetched_events;
                        
                        foreach($disc as $xdisc => $tmp){
                            if($xdisc == "catathlete"){ continue; }
                            
                            // check if there are events missing for the current athlete and add 'null' entries
                            while($tmp_fe[0][3] != $xdisc){                                        
                                $eDetails .= "0/";
                                array_shift($tmp_fe);
                            }
                            array_shift($tmp_fe);
                            
                            $points += $tmp['points'];
                            if($tmp['wind'] == " "){
                                $tmp['wind'] = "";
                            }else{
                               
                                if($tmp['wind'] >= 0){
                                    $tmp['wind'] = "+".$tmp['wind'];
                                }else{
                                    $tmp['wind'] = $tmp['wind'];
                                }
                            }
                           
                            $eDetails .= $tmp['effort'].$tmp['wind']."/";
                            $birthDate = $tmp['birthDate']; 
                            
                        }
                        
                        // check if last events are missing
                        while(isset($tmp_fe[0][3])){
                            $eDetails .= "0/";
                            array_shift($tmp_fe);
                        }
                        
                        $eDetails = substr($eDetails, 0, -1);
                        $combined[$xathlet]['points'] = $points;
                        $combined[$xathlet]['edetails'] = $eDetails;
                       
                    }
                    // sort for points
                    usort($combined, array($this, "sort_combined"));
                    
                    // write                         
                    $rank = 0;    // athletes rank
                    $cRank = 0;    // rank counter
                    $lp = 0;    // remembers points of last athlete
                    foreach($combined as $xathlet => $disc){   
                        
                        $cRank++;
                        if($lp != $disc['points']){
                            $rank = $cRank;
                            $lp = $disc['points'];
                        }
                        
                        // get information for athlete
                        $tmp = $disc;
                        $tmp['points'] = null;
                        $tmp['edetails'] = null;
                        $tmp['catathlete'] = null;
                        $tmp = array_values($tmp);
                        usort($tmp, array($this, "sort_perdate"));
                        $tmp = $tmp[0];          
                                                                 
                        $this->write_xml_open("athlete", array('license'=>$tmp['license'], 'kidID'=>$tmp['kidID']));    
                         
                        list ($run, $jump, $throw) = split('[/]', $disc['edetails']);   
                        
                        $this->write_xml_open("efforts");      
                        $this->write_xml_finished("run",$run); 
                        $this->write_xml_finished("throw",$throw); 
                        $this->write_xml_finished("jump",$jump);   
                        $this->write_xml_finished("totalPoints",AA_alabusScore($disc['points']));   
                        $this->write_xml_finished("position",$rank);    
                        $this->write_xml_close("efforts");                         
                        
                         if (empty($tmp['license']) && empty($tmp['kidID'])){
                                  
                                $this->write_xml_open("personalData");    
                                 
                                $this->write_xml_finished("lastName", $tmp['lastName']);
                                $this->write_xml_finished("firstName", $tmp['firstName']);                                 
                                $this->write_xml_finished("ageGroup", $tmp['birthDate']);
                                $this->write_xml_finished("sex", $tmp['sex']);
                                $this->write_xml_finished("street", $tmp['adress']);
                                $this->write_xml_finished("zipCode", $tmp['plz']);
                                $this->write_xml_finished("city", $tmp['city']);  
                                $this->write_xml_finished("club", $tmp['accountName']);  
                                $this->write_xml_finished("email", $tmp['email']);  
                                $this->write_xml_finished("canton", $tmp['canton']); 
                             
                                $this->write_xml_close("personalData");
                        }    
                        $this->write_xml_close("athlete"); 
                                              
                        $nbr_effort++;  
                    }    
                }
               
          
            $combined = array();
            
            // get the svm results
            mysql_free_result($res);
            $res = mysql_query("
                SELECT
                    w.Typ,
                    w.Windmessung,
                    d.Typ,
                    d.Code,
                    k.Code,
                    w.xWettkampf,
                    d.Kurzname,
                    w.Mehrkampfcode,
                    w.xKategorie,
                    ks.Code,
                    MAX(r.Datum)
                FROM 
                    runde as r
                LEFT JOIN 
                    wettkampf as w USING(xWettkampf) 
                LEFT JOIN
                    disziplin_" . $_COOKIE['language'] . " as d ON d.xDisziplin = w.xDisziplin
                LEFT JOIN
                    kategorie as k ON k.xKategorie = w.xKategorie
                LEFT JOIN
                    kategorie_svm as ks ON ks.xKategorie_svm = w.xKategorie_svm
                WHERE    xMeeting = ".$_COOKIE['meeting_id']."
                GROUP BY w.xKategorie
                ORDER BY
                    w.xWettkampf
            ");
            
            if(mysql_errno() > 0){
                echo(mysql_errno().": ".mysql_error());
            }else{
                
                while($row = mysql_fetch_array($res)){
                    //
                    // open rankinlist_team lib for calculating the svm points
                    //
                    if($row[0] > $cfgEventType[$strEventTypeSingleCombined]){
                        $this->write_xml_open("discipline", array('sportDiscipline'=>$row[9], 'licenseCategory'=>$row[4]));
                        $this->write_xml_open("teams");
                        
                        $GLOBALS['doe'] = $row[10]; // date of team effort (last round date)
                        $GLOBALS['rankadd'] = $global_rankadd;
                        AA_rankinglist_Team($row[8], 'xml', "", false, $this);        
                    }
                }
            }
            
            // close last tags
            $this->close_open_tags();
        }
        
        $this->gzip_close();
        
        return $nbr_effort;
    }
    
    
    // generate results UBS kidscup for upload
//
function gen_result_xml_UKC_CM($file, $meeting_nr){ 
         //function returns containing number of results in xml-file
        $nbr_effort_ukc=0;                  
       
        global $cfgDisciplineType, $cfgEventType, $strEventTypeSingleCombined, 
            $strEventTypeClubCombined, $strDiscTypeTrack, $strDiscTypeTrackNoWind, 
            $strDiscTypeRelay, $strDiscTypeDistance, $cfgRoundStatus,
            $strDiscTypeJump, $strDiscTypeJumpNoWind, $strDiscTypeThrow, $strDiscTypeHigh,
            $cfgCombinedWO, $cfgCombinedDef,  $cfgUKC_disc;
        
        $this->gzip_open($file);
        
        // begin xml
        $this->write_xml_open("kidDataset", array('version'=>date('y-m-d')));
        $this->write_xml_open("event");
        
        //
        // output contest data
        //   
        $indoor = '0';                    
        $query = "
            SELECT
                m.*
                , s.Name as Stadion
                , s.Ueber1000m
                , s.Halle
            FROM 
                meeting as m 
                LEFT JOIN stadion as s ON m.xStadion = s.xStadion 
            WHERE xMeeting = ".$_COOKIE['meeting_id'];
        $res = mysql_query($query);
        if(mysql_errno() > 0){
            echo(mysql_errno().": ".mysql_error());
        }else{
            $row = mysql_fetch_assoc($res);
            mysql_free_result($res);
            
            if (empty($meeting_nr)) {
                  $this->write_xml_finished("eventNumber",$row['Nummer']);                      // not xControl for UBS kidscup   
            }
            else {
                 $this->write_xml_finished("eventNumber",$meeting_nr);                      
            }
            $this->write_xml_finished("name",str_replace('&', '&amp;', $row['Name']));
            $this->write_xml_finished("eventStart",$row['DatumVon']);
            $this->write_xml_finished("eventEnd",$row['DatumBis']);
            $this->write_xml_finished("location",$row['Ort']);
            $this->write_xml_finished("stadium",$row['Stadion']);
            $this->write_xml_finished("amountSpectators"," ");
            
            $this->write_xml_open("athletes");  
            
            if($row['Ueber1000m'] == 'y'){ $global_rankadd = "A"; }               
            if($row['Saison'] == 'I'){ $indoor = "1"; }                      // 1 = indoor , 0 = outdoor 
        }
                  
          // get enrolement per athlete     
          $checkyear= date('Y') - 16;   
          
          $sql="SELECT DISTINCT 
                a.xAnmeldung
                , at.Name
                , at.Vorname
                , at.Jahrgang
                
                , IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo)
                , IF(at.xRegion = 0, at.Land, re.Anzeige)
                , 0
                , d.Name
               
                
                
                , ka.Alterslimite 
                , d.Code 
                , at.xAthlet
                , at.Geschlecht               
            FROM
                anmeldung AS a
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet )
                LEFT JOIN verein AS v  ON (v.xVerein = at.xVerein  )
                LEFT JOIN start as st ON (st.xAnmeldung = a.xAnmeldung ) 
                LEFT JOIN wettkampf as w  ON (w.xWettkampf = st.xWettkampf) 
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (w.xDisziplin = d.xDisziplin)
                LEFT JOIN kategorie AS k ON (k.xKategorie = w.xKategorie)
                LEFT JOIN kategorie AS ka ON (ka.xKategorie = a.xKategorie)     
                LEFT JOIN region as re ON (at.xRegion = re.xRegion) 
            WHERE a.xMeeting = " . $_COOKIE['meeting_id'] ."             
            AND at.Jahrgang > $checkyear AND (d.Code = " . $cfgUKC_disc[0] ." || d.Code = " . $cfgUKC_disc[1]  . " || d.Code = " . $cfgUKC_disc[2] . ")
            AND st.anwesend = 0 
            ORDER BY at.Geschlecht, at.Jahrgang,  at.Name, at.Vorname,  d.Anzeige";           

            $results = mysql_query($sql);      

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {     
               while($row = mysql_fetch_row($results))  {
                           
                        if ($roundsUkc[$row[10]] == ""){
                              $roundsUkc[$row[10]] = 0;
                          }
                          $roundsUkc[$row[10]]++;
                    }    
            }  
            
                       
        //
        // get all disciplines
        //            
        $selection = " (d.Code = " . $cfgUKC_disc[0] ." || d.Code = " . $cfgUKC_disc[1]  . " || d.Code = " . $cfgUKC_disc[2] . ") AND ";    
                   
        $sql="  SELECT                        
                    w.Typ,
                    w.Windmessung,
                    d.Typ,
                    d.Code,
                    k.Code,
                    w.xWettkampf,
                    d.Kurzname,
                    w.Mehrkampfcode,
                    0,
                    d.Staffellaeufer,  
                    r.xRunde,
                    w.info  
                FROM 
                    wettkampf as w 
                    LEFT JOIN runde as r ON (w.xWettkampf = r.xWettkampf)  
                LEFT JOIN
                    disziplin_" . $_COOKIE['language'] . " as d ON d.xDisziplin = w.xDisziplin
                LEFT JOIN
                    kategorie as k ON k.xKategorie = w.xKategorie     
                WHERE $selection r.xRunde > 0 AND  
                   
                    w.xMeeting = ".$_COOKIE['meeting_id']."                  
                ORDER BY
                    k.Code                      
                    , w.info
                    , w.xKategorie
                    , d.Anzeige";     
               // the order "k.Code, w.xKategorie" makes sense if there are multiple self made categories (without any code)        
        $res = mysql_query($sql);   
        
        if(mysql_errno() > 0){              
            echo(mysql_errno().": ".mysql_error());
        }else{
            
            $current_type = "";
            $combined = "";
            $current_cat = "";
            $current_xcat = 0;
         
            $GLOBALS['rounds'] = array();
            
            $combined = array();
            $fetched_events = array();
            
            while($row = mysql_fetch_array($res)){    
                //
                // generate results for combined events
                //                     
                  
               
                //
                // first of all, print all single results (athletes and relays)
                //         
                $order_perf = "";
                $valid_result = "";                

                if(($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                    || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow]))
                {
                    $order_perf = "DESC";                          
                }
                else if($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                {
                    if ($row[1] == 1) {            // with wind                        
                        $order_perf = "DESC";
                    }
                    else {                            // without wind
                        $order_perf = "DESC";
                    }                             
                }
                else if($row[2] == $cfgDisciplineType[$strDiscTypeHigh])
                {
                    $order_perf = "DESC";
                    $valid_result =    " AND (r.Info LIKE '%O%') ";    
                }
                else
                {
                    $order_perf = "ASC";                              
                }
                                
                // check if merged rounds                     
                $sqlEvents = AA_getMergedEventsFromEvent($row[5]);
                if (empty($sqlEvents)) {
                           $sqlSeparate = "ru.xRunde = " . $row[10];
                }
                else {
                          $sqlSeparate = "ss.RundeZusammen = " . $row[10];
                }
                $checkyear= date('Y') - 16; 
                $selection = " at.Jahrgang > $checkyear AND ";            
                // by merged rounds the result must be uploded separate 
                $query = "
                        SELECT
                            ss.xSerienstart
                            , ss.Rang
                            , ss.Qualifikation
                            , r.Leistung
                            , r.Info
                            , s.Bezeichnung
                            , s.Wind
                            , r.Punkte
                            , v.Name
                            , at.Name
                            , at.Vorname
                            , at.Jahrgang
                            , at.Land
                            , at.xAthlet
                            , at.Lizenznummer
                            , ru.Datum
                            , rt.Code as Typ
                            , at.Bezahlt
                            , at.Geburtstag
                            , at.Geschlecht
                            , v.xCode as Vereincode
                            , k.Code as Katathlet
                            , ru.xRunde
                            , s.Handgestoppt
                            , at.Lizenztyp
                            , a.Vereinsinfo
                            , rt.Typ
                            
                            , if (ss.RundeZusammen > 0,ss.RundeZusammen,ru.xRunde) as spezRound     
                            , a.kidID
                            , at.Adresse
                            , at.Plz
                            , at.Ort
                            , at.Email  
                            , re.Anzeige as Kanton   
                            , v.Name as Vereinname
                            , a.xAnmeldung
                        FROM
                            runde as ru
                            LEFT JOIN serie AS s ON (ru.xRunde = s.xRunde)
                            LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                            LEFT JOIN resultat AS r ON (r.xSerienstart = ss.xSerienstart)
                            LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                            LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                            LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                            LEFT JOIN verein AS v  ON (v.xVerein = at.xVerein  )
                            LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON (ru.xRundentyp = rt.xRundentyp)
                            LEFT JOIN kategorie AS k ON (a.xKategorie = k.xKategorie)                             
                            LEFT JOIN region AS re ON (re.xRegion = at.xRegion) 
                        WHERE $selection $sqlSeparate
                        AND ru.Status = ".$cfgRoundStatus['results_done']."
                        AND ru.StatusUploadUKC = 0
                        AND r.Leistung >= " . $GLOBALS['cfgInvalidResult']['DNS']['code'] . "
                        
                        $valid_result
                        ORDER BY 
                            at.Jahrgang
                            , at.xAthlet
                            , ru.xRunde
                            , r.Leistung "
                            . $order_perf;      
                
                $res_results = mysql_query($query);      

                if(mysql_errno() > 0){                              
                    echo mysql_Error();
                }else{                        
                
                    $id = 0;    // athletes id
                    $ru = 0;    // round id
                   
                    while($row_results = mysql_fetch_assoc($res_results)){                            
                                                  
                        // store round ids for later purpose
                        $GLOBALS['rounds'][] = $row_results['xRunde'];   
                       
                       //
                       // athlete results
                       //
                       if($ru == $row_results['xRunde'] && $id == $row_results['xAthlet']){   
                                continue;
                       }
                       $ru = $row_results['xRunde'];    
                            
                       if($id != $row_results['xAthlet']){
                                // new athlete
                                $id = $row_results['xAthlet'];                                                                 
                               
                                if(empty($row_results['Lizenznummer']) && empty($row_results['kidID'])){                                        
                                    $inMasterData = 1;
                                    $licensePaid = 1;
                                }else{                                    
                                    $inMasterData = 0;                                          
                                    if($row_results['Bezahlt'] == 'y'){
                                        $licensePaid = 1;
                                    }else{
                                        $licensePaid = 0;
                                    }
                                }   
                       }
                              
                       $perf = 0; // result for alabus
                       $wind = "";
                       $perfRounded = 0; // result for combined detail text    
                            
                        // add effort parameters   
                            
                        $wind = "";
                        if(($row[2] == $cfgDisciplineType[$strDiscTypeJump])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeJumpNoWind])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeThrow])
                                || ($row[2] == $cfgDisciplineType[$strDiscTypeHigh])) {
                                $perf = AA_alabusDistance($row_results['Leistung']);
                                $perfRounded = AA_formatResultMeter($row_results['Leistung']);      
                                $wind = strtr($row_results['Info'],",",".");
                        }else{
                                $perf = AA_alabusTime($row_results['Leistung']);                                    
                                $perfRounded = AA_formatResultTime($row_results['Leistung'], true);  
                                $wind = strtr($row_results['Wind'],",",".");
                        }                            
                            
                        if($row[1] == 0 || $wind == "-" || $wind == ""){
                                $wind = " ";
                        }
                               
                        if(is_numeric($row_results['Bezeichnung'])){
                                $row_results['Bezeichnung'] = sprintf("%02s",$row_results['Bezeichnung']);
                        }else{
                                if(strlen($row_results['Bezeichnung']) == 1){
                                    $row_results['Bezeichnung'] .= "_";
                                }
                        }                                
                                                       
                        $rank = " ";
                        $row_results['Bezeichnung'] = " ";
                        //
                        //add points for combined contests                                 
                        if($combined[$row_results['xAthlet']][$row[3]]['points'] < $row_results['Punkte']){                                               
                                    $license = $row_results['Lizenznummer'];
                                    if ($row_results['Lizenznummer'] == 0){
                                        $license = '';
                                    }
                                    
                                    $kidsID_upload = $row_results['kidID'];
                                   
                                    if ($row_results['kidID'] == 0){
                                        $kidsID_upload = '';
                                    }
                                    if ($row[3] == 30){
                                         $perfRounded = "r". $perfRounded;                 // r = run
                                    }
                                    if ($row[3] == 331){
                                         $perfRounded = "j". $perfRounded;                 // j = jump
                                    }
                                    if ($row[3] == 386){
                                         $perfRounded = "t". $perfRounded;                  // t = throw
                                    } 
                                    
                                    $combined[$row_results['xAthlet']][$row[3]] = array('wind'=>$wind, 'kindOfLap'=>" ".$row_results['Typ'],
                                        'lap'=>$row_results['Bezeichnung'], 'placeAddon'=>$rankadd, 'indoor'=>$indoor, 'points'=>$row_results['Punkte'],
                                        'effort'=>$perfRounded, 'discipline'=>$row[6], 'license'=>$license, 'kidID'=>$kidsID_upload,
                                        'inMasterData'=>$inMasterData, 'licensePaid'=>$licensePaid, 'DateOfEffort'=>$row_results['Datum'],
                                        'lastName'=>htmlspecialchars($row_results['Name']), 'firstName'=>htmlspecialchars($row_results['Vorname']), 
                                        'birthDate'=>$row_results['Jahrgang'], 'sex'=>strtoupper ( $row_results['Geschlecht']), 'nationality'=>$row_results['Land'], 
                                        'adress'=>htmlspecialchars($row_results['Adresse']),  'plz'=>$row_results['Plz'],  'city'=>htmlspecialchars($row_results['Ort']),
                                         'email'=>$row_results['Email'],   'canton'=>$row_results['Kanton'], 
                                        'accountName'=>htmlspecialchars($row_results['Vereinname']), 'priority'=>$combinedPriority, 
                                        'licenseType'=>$row_results['Lizenztyp'], 'dCode'=>$row_results['dCode'], 'xAnmeldung'=>$row_results['xAnmeldung'], 
                                        'xSerienstart'=>$row_results['xSerienstart'], 'Leistung'=>$row_results['Leistung'], 'xathlete'=>$row_results['xAthlet']);        
                                    
                                    // category of athlete, used for calculating the rankings
                                    $combined[$row_results['xAthlet']]['catathlete']= $row_results['Jahrgang'];
                                 
                        }    
                            
                        // check on relevant for bestlist
                        $relevant = 1;                             
                               
                        if ($row_results['Typ'] == '0'){                 // (ohne)                                      
                                   $row_results['Bezeichnung'] = '';
                        }
                            
                        // output result data                               
                        if($wind > "2" && $row[2] == $cfgDisciplineType[$strDiscTypeJump]){
                                // since we get only the best result per xSerienstart,
                                // here we'll get the next with valid wind
                                $res_wind = mysql_query("
                                        SELECT Info, Leistung FROM
                                            resultat
                                        WHERE
                                            xSerienstart = ".$row_results['xSerienstart']."
                                        ORDER BY
                                            Leistung DESC");
                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }else{
                                    while($row_wind = mysql_fetch_array($res_wind)){
                                        
                                        if($row_wind[0] <= 2){   
                                            
                                            $perf = AA_alabusDistance($row_wind[1]);
                                             if ($perf == -98){                             // -98 = Fehlversuch
                                               break; 
                                            }
                                            $nbr_effort_ukc++;
                                           
                                            break;
                                        }
                                        
                                    }
                                }
                        }// end if wind > 2      
                        
                    } // end while res_results    
                }         
            } 
           
           $min_age =  date('Y') - 7;      
            // check on last combined event             
            if(!empty($combined) && $combined_dis < 9000){ // combined codes 9000 and above are self made disciplines                   
                    
                    // calc points
                    foreach($combined as $xathlet => $disc){                            
                        $points = 0;
                        $eDetails = "";       
                                                 
                                              
                        foreach($disc as $xdisc => $tmp){
                            if($xdisc == "catathlete"){ continue; }
                                                            
                           
                            $pointsUKC = AA_utils_calcPointsUKC(0, $tmp['Leistung'], 0, $tmp['sex'], $tmp['xSerienstart'], $xathlet, $tmp['xAnmeldung'], $xdisc); 
                            $points += $pointsUKC;   
                            
                            mysql_query("UPDATE resultat SET
                                                    Punkte = $pointsUKC
                                              WHERE
                                                    xSerienstart = ". $tmp['xSerienstart']);
                            if(mysql_errno() > 0) {
                                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }
                            AA_StatusChanged(0,0, $tmp['xSerienstart']);   
                             
                            if($tmp['wind'] == " "){
                                $tmp['wind'] = "";
                            }else{
                               
                                if($tmp['wind'] >= 0){
                                    $tmp['wind'] = "+".$tmp['wind'];
                                }else{
                                    $tmp['wind'] = $tmp['wind'];
                                }
                            }
                           
                            $eDetails .= $tmp['effort'].$tmp['wind']."/";
                            $birthDate = $tmp['birthDate']; 
                            $sex = $tmp['sex'];
                            $tmp_xathlete = $tmp['xathlete'];
                        }
                       
                        $eDetails = substr($eDetails, 0, -1);
                        
                        
                        $points = sprintf ("%05d" , $points);
                        
                        
                        $combined[$xathlet]['points'] = $points;
                        $combined[$xathlet]['edetails'] = $eDetails;                           
                        if ($birthDate > $min_age){
                            $birthDate = $min_age;
                        }
                        $combined[$xathlet]['birthDate'] = $sex.$birthDate;                // need for sort in combination
                        $combined[$xathlet]['xathlete'] = $tmp_xathlete;   
                       
                    }
                    // sort for points   
                    array_sort($combined,'!birthDate','!points');  
                    
                    // write    
                    $cRank = 0;    // rank counter
                    $lp = 0;    // remembers points of last athlete
                   
                    foreach($combined as $xathlet => $disc){                                
                         if ($roundsUkc[$disc['xathlete']] != 3) {                        // enrolement for 3 kids cup disciplines
                             continue;
                         }            
                         if ($disc['birthDate'] != $birthDate_keep) {                       // in combination sex and birthdate  
                                $cRank=0; 
                            }
                         $cRank++;    
                         if($lp != $disc['points'] || $disc['points'] == 0){    
                            $lp = $disc['points'];
                            $combined[$xathlet]['rank'] = $cRank;
                         }
                         else {
                             // same total points
                             $c=0;
                             $keep_c=0;      
                               
                             for ($i=0; $i < sizeof($cfgUKC_disc); $i++){                                  
                                 if  ($disc_keep[$cfgUKC_disc[$i]]['points'] > $disc[$cfgUKC_disc[$i]]['points']){                                                                                       
                                      $keep_c ++;
                                 }
                                 else {   
                                     $c++;
                                 }
                             }
                            $more=ceil(sizeof($cfgUKC_disc)/2);  
                            if (sizeof($cfgUKC_disc) % 2 == 0){              // combined with even number discs
                                 $more++;                                   
                            }
                            if  ($keep_c >= $more && $keep_c > $c){    
                                    $lp = $disc['points'];
                                    $combined[$xathlet]['rank'] = $cRank;
                            }
                            else {
                                 if  ($c >= $more && $c > $keep_c){   
                                     
                                     $combined[$xathlet_keep]['rank'] = $cRank;  
                                     $combined[$xathlet]['rank'] = $cRank-1;  
                                 }                                  
                            }   
                         }
                         // get information for athlete
                         $tmp = $disc;
                         $tmp['points'] = null;
                         $tmp['edetails'] = null;
                         $tmp['birthDate'] =  substr($tmp['birthDate'], 1, 4);                      
                         $tmp['catathlete'] = null;
                         $tmp = array_values($tmp);
                        
                         usort($tmp, array($this, "sort_perdate"));
                         
                         $tmp = $tmp[0];  
                         
                         $run = '';  
                         $jump = '';
                         $throw = '';    
                                      
                         list ($perf1, $perf2, $perf3) = split('[/]', $disc['edetails']);  
                         if (substr($perf1,0,1) == 'r'){
                             $run = substr($perf1,1);
                         }
                         elseif (substr($perf1,0,1) == 'j'){                               
                             $jump = substr($perf1,1);
                         }
                         elseif (substr($perf1,0,1) == 't'){
                             $throw = substr($perf1,1);
                         }  
                         if (substr($perf2,0,1) == 'r'){ 
                            $run = substr($perf2,1); 
                         }    
                         elseif (substr($perf2,0,1) == 'j'){ 
                            $jump = substr($perf2,1); 
                         }
                         elseif (substr($perf2,0,1) == 't'){ 
                            $throw = substr($perf2,1); 
                         } 
                         if (substr($perf3,0,1) == 'r'){ 
                            $run = substr($perf3,1); 
                         }  
                         elseif (substr($perf3,0,1) == 'j'){ 
                            $jump = substr($perf3,1); 
                         }
                         elseif (substr($perf3,0,1) == 't'){  
                              $throw = substr($perf3,1); 
                         } 
                          
                         if ($run == -1 ||  $jump == -1 ||  $throw == -1){ 
                                $cRank = $cRank -1;                         
                                continue;
                            }
                        
                        $xathlet_keep =   $xathlet;
                        $disc_keep =   $disc; 
                        $birthDate_keep = $disc['birthDate'];  
                                                    
                     }
                                                        
                     foreach($combined as $xathlet => $disc){   
                        
                         if ($roundsUkc[$disc['xathlete']] != 3) {                  // enrolement for 3 kids cup disciplines
                             continue;
                         }            
                        // get information for athlete
                        $tmp = $disc;
                       
                        $tmp['points'] = null;
                        $tmp['edetails'] = null;
                        $tmp['birthDate'] =  substr($tmp['birthDate'], 1, 4);                      
                        $tmp['catathlete'] = null;
                        $tmp = array_values($tmp);
                         
                       // usort($tmp, array($this, "sort_perdate"));
                      
                        $tmp = $tmp[0];  
                        $run = '';  
                        $jump = '';
                        $throw = '';
                          
                         list ($perf1, $perf2, $perf3) = split('[/]', $disc['edetails']);  
                         if (substr($perf1,0,1) == 'r'){
                             $run = substr($perf1,1);
                         }
                         elseif (substr($perf1,0,1) == 'j'){                               
                             $jump = substr($perf1,1);
                         }
                         elseif (substr($perf1,0,1) == 't'){
                             $throw = substr($perf1,1);
                         }  
                         if (substr($perf2,0,1) == 'r'){ 
                            $run = substr($perf2,1); 
                         }  
                         elseif (substr($perf2,0,1) == 'j'){ 
                            $jump = substr($perf2,1); 
                         }
                         elseif (substr($perf2,0,1) == 't'){ 
                            $throw = substr($perf2,1); 
                         } 
                         if (substr($perf3,0,1) == 'r'){ 
                            $run = substr($perf3,1); 
                         }  
                         elseif (substr($perf3,0,1) == 'j'){ 
                            $jump = substr($perf3,1); 
                         }
                         elseif (substr($perf3,0,1) == 't'){  
                              $throw = substr($perf3,1); 
                         } 
                          
                         if ($run == -1 ||  $jump == -1 ||  $throw == -1){   
                                continue;
                            }
                                                                 
                        $this->write_xml_open("athlete", array('license'=>$tmp['license'], 'kidID'=>$tmp['kidID'] ));    
                          
                        $this->write_xml_open("efforts");      
                        $this->write_xml_finished("run",$run); 
                        $this->write_xml_finished("throw",$throw); 
                        $this->write_xml_finished("jump",$jump);   
                        $this->write_xml_finished("totalPoints",AA_alabusScore($disc['points']));   
                        $this->write_xml_finished("position",$disc['rank']);                           
                        $this->write_xml_close("efforts");
                       
                        
                         if (empty($tmp['license']) && empty($tmp['kidID'])){
                             
                                $this->write_xml_open("personalData"); 
                              
                                $this->write_xml_finished("lastName", $tmp['lastName']);
                                $this->write_xml_finished("firstName", $tmp['firstName']);                               
                                $this->write_xml_finished("ageGroup", $tmp['birthDate']);
                                $this->write_xml_finished("sex", $tmp['sex']);
                                $this->write_xml_finished("street", $tmp['adress']);
                                $this->write_xml_finished("zipCode", $tmp['plz']);
                                $this->write_xml_finished("city", $tmp['city']);  
                                $this->write_xml_finished("club", $tmp['accountName']);  
                                $this->write_xml_finished("email", $tmp['email']);  
                                $this->write_xml_finished("canton", $tmp['canton']); 
                            
                                $this->write_xml_close("personalData"); 
                        }        
                        
                        $this->write_xml_close("athlete");    
                                        
                        $nbr_effort_ukc++;                           
                        $birthDate_keep = $disc['birthDate'];
                    }    
                }
               
          
            $combined = array();
            
            // get the svm results
            mysql_free_result($res);
            $res = mysql_query("
                SELECT
                    w.Typ,
                    w.Windmessung,
                    d.Typ,
                    d.Code,
                    k.Code,
                    w.xWettkampf,
                    d.Kurzname,
                    w.Mehrkampfcode,
                    w.xKategorie,
                    ks.Code,
                    MAX(r.Datum)
                FROM 
                    runde as r
                LEFT JOIN 
                    wettkampf as w USING(xWettkampf) 
                LEFT JOIN
                    disziplin_" . $_COOKIE['language'] . " as d ON d.xDisziplin = w.xDisziplin
                LEFT JOIN
                    kategorie as k ON k.xKategorie = w.xKategorie
                LEFT JOIN
                    kategorie_svm as ks ON ks.xKategorie_svm = w.xKategorie_svm
                WHERE    xMeeting = ".$_COOKIE['meeting_id']."
                GROUP BY w.xKategorie
                ORDER BY
                    w.xWettkampf
            ");
            
            if(mysql_errno() > 0){
                echo(mysql_errno().": ".mysql_error());
            }else{
                
                while($row = mysql_fetch_array($res)){
                    //
                    // open rankinlist_team lib for calculating the svm points
                    //
                    if($row[0] > $cfgEventType[$strEventTypeSingleCombined]){
                        $this->write_xml_open("discipline", array('sportDiscipline'=>$row[9], 'licenseCategory'=>$row[4]));
                        $this->write_xml_open("teams");
                        
                        $GLOBALS['doe'] = $row[10]; // date of team effort (last round date)
                        $GLOBALS['rankadd'] = $global_rankadd;
                        AA_rankinglist_Team($row[8], 'xml', "", false, $this);        
                    }
                }
            }
            
            // close last tags
            $this->close_open_tags();
        }
        
        $this->gzip_close();
        
        return $nbr_effort_ukc;
    }
    
    function gzip_open($file){
        global $gzfp, $strErrFileOpenFailed;
        
        if(!$gzfp = @gzopen($file, 'wb9')){
            AA_printErrorMsg($strErrFileOpenFailed.": ".$file);
            return false;
        }
        
        gzwrite($gzfp, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n");
    }
    
    function gzip_close(){
        global $gzfp;
        gzclose($gzfp);
    }
    
    function close_open_tags($until=''){
        global    $opentags;
        
        for($i = (count($opentags)-1); $i >= 0; $i--){
            if(empty($until)){
                $this->write_xml_close($opentags[$i]);
                //$tmp = array_pop($opentags);
            }else{
                if($until == $opentags[$i]){
                    break;
                }else{
                    $this->write_xml_close($opentags[$i]);
                    //$tmp = array_pop($opentags);
                }
            }
        }
    }
    
    function write_xml_finished($tag, $value='', $args=''){
        global $gzfp;
        
        gzwrite($gzfp, $this->get_tabs());
        // start
        if(empty($args)){
            gzwrite($gzfp, "<$tag");
        }else{
            gzwrite($gzfp, "<$tag");
            foreach($args as $key => $val){
                gzwrite($gzfp, " $key=\"$val\"");
            }
        }
        
        // end
        if(empty($value)){
            gzwrite($gzfp, " />\n");
        }else{
            gzwrite($gzfp, ">".trim($value)."</$tag>\n");
        }
    }
    
    function write_xml_open($tag, $args=''){
        global $gzfp;
        global    $opentags;
        
        
        gzwrite($gzfp, $this->get_tabs());
        if(empty($args)){
            gzwrite($gzfp, "<$tag>\n");
        }else{
            gzwrite($gzfp, "<$tag");
            foreach($args as $key => $val){
                gzwrite($gzfp, " $key=\"$val\"");
            }
            gzwrite($gzfp, ">\n");
        }
        
        //array_push($opentags, $tag);
        $opentags[] = $tag;
    }
    
    function write_xml_close($tag){
        global $gzfp;
        global    $opentags;
        
        $tmp = array_pop($opentags);
        gzwrite($gzfp, $this->get_tabs()."</$tag>\n");
    }
    
    function get_tabs(){
        global $opentags;
        if(count($opentags) > 0){
            foreach($opentags as $asdf){
                $tt .= "    ";
            }
        }
        return $tt;
    }        
    
    function sort_combined($a, $b)
    {              
       if ($a['points'] == $b['points']) {
                return 0;
            }
       return ($a['points'] < $b['points']) ? 1 : -1;
       
    }   
    
    function sort_perdate($a, $b)
    {  
        $ret = strcasecmp($a['DateOfEffort'], $b['DateOfEffort']);
        if($ret == 0){ return 0; }
        return ($ret < 0) ? 1 : -1;
    }  
    
    /*function sort_perpriority($a, $b)
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }
        return ($a['priority'] > $b['priority']) ? 1 : -1;
    }*/
}



/* error function ****************************************************************************************************************/
// IMPORTANT: if a db error occurs, rollback an die! else we'll have inconsitent data
//    well... no innodb, no rollback ;)
function XML_db_error($msg){
    //mysql_query("ROLLBACK");
    echo $msg;
    AA_printErrorMsg($msg);
    die();
}

/* handling base data ************************************************************************************************************/
function XML_base_start($parser, $name, $attr){
    global $bAthlete, $bPerf, $biPerf, $bAccount, $bRelay, $bSvm, $athlete, $perf, $iperf, $account, $relay, $svm, $cName;
    global $updateType;
    $cName = $name;
    
    // start tags
    switch ($name){
        // setting xml attributes for each object
        case "ATHLETES":
            // insert update log
            /*$glc = $attr['GOBALLASTCHANGE'];
            $time = date('Y-d-m h:i:s');
            mysql_query("INSERT INTO base_log (type, update_time, global_last_change) VALUES ('base_$updateType','$time','$glc')");
            if(mysql_errno() > 0){
                XML_db_error(mysql_errno().": ".mysql_error());
            }*/
            break;
        case "ATHLETE":
            $bAthlete = true;
            $athlete['LICENSE'] = $attr['LICENSE'];
            $athlete['LICENSEPAID'] = $attr['LICENSEPAID'];
            $athlete['LICENSECAT'] = $attr['LICENSECAT'];
            break;
        case "PERFORMANCE":
            $bPerf = true;
            $perf[] = array('SPORTDISCIPLINE'=>$attr['SPORTDISCIPLINE']);
            break;
        case "BESTEFFORT":
            if ($bPerf){    
                $perf[count($perf)-1]['BESTEFFORT_DATE'] = substr($attr['DATE'],6,4)."-".substr($attr['DATE'],0,2)."-".substr($attr['DATE'],3,2);
                $perf[count($perf)-1]['BESTEFFORT_EVENT'] = $attr['EVENT'];
            }elseif($biPerf){
                $iperf[count($iperf)-1]['BESTEFFORT_DATE'] = substr($attr['DATE'],6,4)."-".substr($attr['DATE'],0,2)."-".substr($attr['DATE'],3,2);
                $iperf[count($iperf)-1]['BESTEFFORT_EVENT'] = $attr['EVENT'];
            }
            break;
        case "SEASONEFFORT":
            if ($bPerf){    
                $perf[count($perf)-1]['SEASONEFFORT_DATE'] = substr($attr['DATE'],6,4)."-".substr($attr['DATE'],0,2)."-".substr($attr['DATE'],3,2);
                $perf[count($perf)-1]['SEASONEFFORT_EVENT'] = $attr['EVENT'];
            }elseif($biPerf){
                $iperf[count($iperf)-1]['SEASONEFFORT_DATE'] = substr($attr['DATE'],6,4)."-".substr($attr['DATE'],0,2)."-".substr($attr['DATE'],3,2);
                $iperf[count($iperf)-1]['SEASONEFFORT_EVENT'] = $attr['EVENT'];
            }
            break;
        case "NOTIFICATIONEFFORT":
            if ($bPerf){    
                $perf[count($perf)-1]['NOTIFICATIONEFFORT_DATE'] = substr($attr['DATE'],6,4)."-".substr($attr['DATE'],0,2)."-".substr($attr['DATE'],3,2);
                $perf[count($perf)-1]['NOTIFICATIONEFFORT_EVENT'] = $attr['EVENT'];
            }elseif($biPerf){
                $iperf[count($iperf)-1]['NOTIFICATIONEFFORT_DATE'] = substr($attr['DATE'],6,4)."-".substr($attr['DATE'],0,2)."-".substr($attr['DATE'],3,2); 
                $iperf[count($iperf)-1]['NOTIFICATIONEFFORT_EVENT'] = $attr['EVENT'];
            }
            break;
        case "PERFORMANCEINDOOR":
            $biPerf = true;
            $iperf[] = array('SPORTDISCIPLINE'=>$attr['SPORTDISCIPLINE']);
            break;

        case "ACCOUNT":
            $bAccount = true;
            break;
            case "RELAY":
            $bRelay = true;
            $relay[count($relay)] = array('ID'=>$attr['ID'], 'ISATHLETICAGENERATED'=>$attr['ISATHLETICAGENERATED']);
            break;
        case "SVM":
            $bSvm = true;
            $svm[count($svm)] = array('ID'=>$attr['ID'], 'ISATHLETICAGENERATED'=>$attr['ISATHLETICAGENERATED']);
            break;
    }
}

function XML_base_end($parser, $name){
    global $bAthlete, $bPerf, $biPerf, $bAccount, $bRelay, $bSvm, $athlete, $iperf, $perf, $account, $relay, $svm, $cName;   
   
   
    // end tags
    switch ($name){  
        case "ATHLETE":      
        $bAthlete = false;
        //print_r($athlete);
        //flush();
        //die();
        // save athlete with performances
        
        if($athlete['LICENSEPAID'] == 0){ $athlete['LICENSEPAID']='n'; }else{ $athlete['LICENSEPAID']='y'; }
        if(!empty($athlete['BIRTHDATE'])){
            $bdate = substr($athlete['BIRTHDATE'],6,4)."-".substr($athlete['BIRTHDATE'],0,2)."-".substr($athlete['BIRTHDATE'],3,2);
        }else{
            $bdate = "0000-00-00";
        }
        // change license category from (m|w)xx_ to man_ or wom_
        if(substr($athlete['LICENSECAT'],0,1) == 'M'){ $athlete['LICENSECAT'] = 'MAN_'; }
        if(substr($athlete['LICENSECAT'],0,1) == 'W'){ $athlete['LICENSECAT'] = 'WOM_'; }
        
        // check if entry exists
        $sql = "SELECT id_athlete FROM base_athlete WHERE license = '".trim($athlete['LICENSE'])."';";
        $res = mysql_query($sql);
                
        if(mysql_num_rows($res) == 0){   
            
            //if(empty($athlete['LICENSE'])){ break; }
            mysql_query("    INSERT IGNORE INTO
                        base_athlete (
                            license
                            , license_paid
                            , license_cat
                            , lastname
                            , firstname
                            , sex
                            , nationality
                            , account_code
                            , second_account_code
                            , birth_date
                            , account_info)
                        VALUES (
                            '".$athlete['LICENSE']."'
                            ,'".$athlete['LICENSEPAID']."'
                            ,'".$athlete['LICENSECAT']."'
                            ,'".addslashes(trim($athlete['LASTNAME']))."'
                            ,'".addslashes(trim($athlete['FIRSTNAME']))."'
                            ,'".strtolower(trim($athlete['SEX']))."'
                            ,'".trim($athlete['NATIONALITY'])."'
                            ,'".trim($athlete['ACCOUNTCODE'])."'
                            ,'".trim($athlete['SECONDACCOUNTCODE'])."'
                            ,'".$bdate."'
                            ,'".trim($athlete['LASTKNOWNACCOUNTINFO'])."')");
            if(mysql_errno() > 0){
                XML_db_error(mysql_errno().": ".mysql_error());
            }else{
                $xAthlete = mysql_insert_id();
                foreach($perf as $row){            
                    if(empty($row['SPORTDISCIPLINE'])){ continue; } //prevent from empty entrys
                    $sql = "    INSERT IGNORE INTO
                                base_performance (
                                    id_athlete
                                    , discipline
                                    , best_effort
                                    , best_effort_date
                                    , best_effort_event
                                    , season_effort
                                    , season_effort_date
                                    , season_effort_event
                                    , notification_effort
                                    , notification_effort_date
                                    , notification_effort_event
                                    , season)
                                VALUES (
                                    '".$xAthlete."'
                                    ,'".$row['SPORTDISCIPLINE']."'
                                    ,'".trim($row['BESTEFFORT'])."'
                                    ,'".trim($row['BESTEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['BESTEFFORT_EVENT']))."'
                                    ,'".trim($row['SEASONEFFORT'])."'
                                    ,'".trim($row['SEASONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['SEASONEFFORT_EVENT']))."'
                                    ,'".trim($row['NOTIFICATIONEFFORT'])."'
                                    ,'".trim($row['NOTIFICATIONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['NOTIFICATIONEFFORT_EVENT']))."'
                                    ,'O')";
                    mysql_query($sql);
                    
                    if(mysql_errno() > 0){
                        XML_db_error(mysql_errno().": ".mysql_error(). "\n SQL= $sql");
                    }else{
                        //ok
                    }
                }            

                foreach($iperf as $row){
                    if(empty($row['SPORTDISCIPLINE'])){ continue; } //prevent from empty entrys
                    $sql = "    INSERT IGNORE INTO
                                base_performance (
                                    id_athlete
                                    , discipline
                                    , best_effort
                                    , best_effort_date
                                    , best_effort_event
                                    , season_effort
                                    , season_effort_date
                                    , season_effort_event
                                    , notification_effort
                                    , notification_effort_date
                                    , notification_effort_event
                                    , season)
                                VALUES (
                                    '".$xAthlete."'
                                    ,'".$row['SPORTDISCIPLINE']."'
                                    ,'".trim($row['BESTEFFORT'])."'
                                    ,'".trim($row['BESTEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['BESTEFFORT_EVENT']))."'
                                    ,'".trim($row['SEASONEFFORT'])."'
                                    ,'".trim($row['SEASONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['SEASONEFFORT_EVENT']))."'
                                    ,'".trim($row['NOTIFICATIONEFFORT'])."'
                                    ,'".trim($row['NOTIFICATIONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['NOTIFICATIONEFFORT_EVENT']))."'
                                    ,'I')";
                    mysql_query($sql);
                    
                    if(mysql_errno() > 0){
                        XML_db_error(mysql_errno().": ".mysql_error());
                    }else{
                        //ok
                    }
                }
            }
            
        }else{ // athlete update
            
            $row = mysql_fetch_array($res);
            $xAthlete = $row[0];
            mysql_free_result($res);
            
            mysql_query("    UPDATE
                        base_athlete
                    SET 
                        license = '".$athlete['LICENSE']."'
                        , license_paid = '".$athlete['LICENSEPAID']."'
                        , license_cat = '".$athlete['LICENSECAT']."'
                        , lastname = '".addslashes(trim($athlete['LASTNAME']))."'
                        , firstname = '".addslashes(trim($athlete['FIRSTNAME']))."'
                        , sex = '".strtolower(trim($athlete['SEX']))."'
                        , nationality = '".trim($athlete['NATIONALITY'])."'
                        , account_code = '".trim($athlete['ACCOUNTCODE'])."'
                        , second_account_code = '".trim($athlete['SECONDACCOUNTCODE'])."'
                        , birth_date = '".$bdate."'
                        , account_info = '".trim($athlete['LASTKNOWNACCOUNTINFO'])."'
                    WHERE
                        id_athlete = $xAthlete");
            
            if(mysql_errno() > 0){
                XML_db_error(mysql_errno().": ".mysql_error());
            }else{
                foreach($perf as $row){
                    if(empty($row['SPORTDISCIPLINE'])){ continue; } //prevent from empty entrys

                    $sql =  "DELETE FROM base_performance WHERE id_athlete = $xAthlete AND discipline = '". $row['SPORTDISCIPLINE'] ."' AND season = 'O'";                            
                    mysql_query($sql);
                    
                    if(mysql_errno() > 0){
                        XML_db_error(mysql_errno().": ".mysql_error());
                    } else {
                        $sql = "    INSERT IGNORE INTO
                                base_performance (
                                    id_athlete
                                    , discipline
                                    , best_effort
                                    , best_effort_date
                                    , best_effort_event
                                    , season_effort
                                    , season_effort_date
                                    , season_effort_event
                                    , notification_effort
                                    , notification_effort_date
                                    , notification_effort_event
                                    , season)
                                VALUES (
                                    '".$xAthlete."'
                                    ,'".$row['SPORTDISCIPLINE']."'
                                    ,'".trim($row['BESTEFFORT'])."'
                                    ,'".trim($row['BESTEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['BESTEFFORT_EVENT']))."'
                                    ,'".trim($row['SEASONEFFORT'])."'
                                    ,'".trim($row['SEASONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['SEASONEFFORT_EVENT']))."'
                                    ,'".trim($row['NOTIFICATIONEFFORT'])."'
                                    ,'".trim($row['NOTIFICATIONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['NOTIFICATIONEFFORT_EVENT']))."'
                                    ,'O')";
                                
                                /* would be nice... unfortunately not supported in MySQL4 ... now deleting before insert
                                ON DUPLICATE KEY UPDATE
                                    best_effort = '".trim($row['BESTEFFORT'])."'
                                    , season_effort = '".trim($row['SEASONEFFORT'])."'
                                    , notification_effort = '".trim($row['NOTIFICATIONEFFORT']). "'"; */ 
                        
                        mysql_query($sql);
                        if(mysql_errno() > 0){
                            XML_db_error(mysql_errno().": ".mysql_error(). "\n SQL= $sql");
                        }else{
                            //ok
                        }
                    }
                }

                foreach($iperf as $row){
                    if(empty($row['SPORTDISCIPLINE'])){ continue; } //prevent from empty entrys
                    $sql="DELETE FROM base_performance WHERE id_athlete = $xAthlete AND discipline = '". $row['SPORTDISCIPLINE'] ."' AND season = 'I'";
                    mysql_query($sql);
                    
                    if(mysql_errno() > 0){
                        XML_db_error(mysql_errno().": ".mysql_error());
                    } else {  
                        $sql = "    INSERT IGNORE INTO
                                base_performance (
                                    id_athlete
                                    , discipline
                                    , best_effort
                                    , best_effort_date
                                    , best_effort_event
                                    , season_effort
                                    , season_effort_date
                                    , season_effort_event
                                    , notification_effort
                                    , notification_effort_date
                                    , notification_effort_event
                                    , season)
                                VALUES (
                                    '".$xAthlete."'
                                    ,'".$row['SPORTDISCIPLINE']."'
                                    ,'".trim($row['BESTEFFORT'])."'
                                    ,'".trim($row['BESTEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['BESTEFFORT_EVENT']))."'
                                    ,'".trim($row['SEASONEFFORT'])."'
                                    ,'".trim($row['SEASONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['SEASONEFFORT_EVENT']))."'
                                    ,'".trim($row['NOTIFICATIONEFFORT'])."'
                                    ,'".trim($row['NOTIFICATIONEFFORT_DATE'])."'
                                    ,'".addslashes(trim($row['NOTIFICATIONEFFORT_EVENT']))."'
                                    ,'I')";
                        
                        mysql_query($sql);
                        if(mysql_errno() > 0){
                            XML_db_error(mysql_errno().": ".mysql_error(). "\n SQL= $sql");
                        }else{
                            //ok
                        }
                    }
                }
            }
        }
             
        
        
        $sql2 = "SELECT TRIM(lastname) AS lastname, 
                        TRIM(firstname) AS firstname, 
                        substring(birth_date, 1,4) AS jahrgang, 
                        license, 
                        TRIM(sex) AS sex, 
                        nationality, 
                        birth_date, 
                        account_code, 
                        second_account_code, 
                        id_athlete 
                   FROM base_athlete 
                  WHERE license = '".trim($athlete['LICENSE'])."';";
        $query2 = mysql_query($sql2);
        
        if($query2 && mysql_num_rows($query2)==1){
            $row2 = mysql_fetch_assoc($query2);
            
            $club = $row2['account_code'];         
            $club2 = $row2['second_account_code'];
            $athlete_id = $row2['id_athlete'];
            $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
            if(mysql_errno() > 0){
                XML_db_error("6-".mysql_errno() . ": " . mysql_error());
            }else{                   
                    $rowClub1 = mysql_fetch_array($result2);
                    $club = $rowClub1[0];
                    if(!empty($club2)){
                        $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club2."'");
                        if(mysql_errno() > 0){
                            XML_db_error("7-".mysql_errno() . ": " . mysql_error());
                            $club2 = 0; // prevents from insert error in next statement
                        }else{
                            $rowClub2 = mysql_fetch_array($result2);
                            $club2 = $rowClub2[0];
                        }
                    }   
            }
            mysql_free_result($result2);   
            
            // check if there are manual changes     
            $sql4 = "SELECT Vorname, Name, xVerein, Manuell FROM athlet WHERE Lizenznummer = '".trim($athlete['LICENSE'])."'";
            $query4 = mysql_query($sql4);
        
            if(mysql_num_rows($query4) > 0){
                $row4 = mysql_fetch_assoc($query4);  
                
                if ($row4['Manuell'] == '0' || $GLOBALS['mode'] == 'overwrite' ){         // overwrite the manual changes
            
                    $sql3 = "UPDATE athlet 
                        SET Name = '".trim($row2['lastname'])."', 
                            Vorname = '".trim($row2['firstname'])."', 
                            Jahrgang = '".trim($row2['jahrgang'])."', 
                            Geschlecht = '".trim($row2['sex'])."', 
                            Land = '".trim($row2['nationality'])."', 
                            Geburtstag = '".trim($row2['birth_date'])."', 
                            xVerein = '".trim($club)."', 
                            xVerein2 = '".trim($club2)."', 
                            Lizenznummer = '".trim($athlete['LICENSE'])."',
                            Manuell = 0  
                      WHERE (Lizenznummer = '".trim($athlete['LICENSE'])."' 
                         OR (Name = '".trim($row2['lastname'])."' 
                        AND Vorname = '".trim($row2['firstname'])."' 
                        AND Jahrgang = '".trim($row2['jahrgang'])."' 
                        AND xVerein = '".trim($club)."'));";
                                                             
                }
                else {
                    switch ($row4['Manuell']){
                        case 1: $firstname = trim($row2['firstname']); 
                                $name = trim($row4['Name']);
                                $verein = trim($club);
                                break;
                        case 2: $firstname = $row4['Vorname'];  
                                $name = trim($row2['lastname']);
                                $verein = trim($club);
                                 break;
                        case 3: $firstname = trim($row2['firstname']);
                                $name = trim($row2['lastname']);
                                $verein = $row4['xVerein'];
                                break;    
                        case 4: $firstname = $row4['Vorname'];
                                $name = $row4['Name'];
                                $verein = trim($club);
                                break;   
                        case 5: $firstname = trim($row2['firstname']);    
                                $name = $row4['Name'];
                                $verein = $row4['xVerein'];
                                break;  
                        case 6: $firstname = $row4['Vorname']; 
                                $name =trim($row2['lastname']);  
                                $verein = $row4['xVerein'];
                                break; 
                        case 7: $firstname = $row4['Vorname'];
                                $name = $row4['Name'];
                                $verein = $row4['xVerein'];
                                break;                                                  
                        default: 
                                break;
                    }
                    
                    $sql3 = "UPDATE athlet 
                        SET Name = '".$name  ."', 
                            Vorname = '".$firstname ."', 
                            Jahrgang = '".trim($row2['jahrgang'])."', 
                            Geschlecht = '".trim($row2['sex'])."', 
                            Land = '".trim($row2['nationality'])."', 
                            Geburtstag = '".trim($row2['birth_date'])."', 
                            xVerein = '".$verein."', 
                            xVerein2 = '".trim($club2)."', 
                            Lizenznummer = '".trim($athlete['LICENSE'])."'                           
                      WHERE (Lizenznummer = '".trim($athlete['LICENSE'])."' 
                         OR (Name = '".trim($row2['lastname'])."' 
                        AND Vorname = '".trim($row2['firstname'])."' 
                        AND Jahrgang = '".trim($row2['jahrgang'])."' 
                        AND xVerein = '".trim($club)."'));";                          
                  
                }
                $query3 = mysql_query($sql3);
            }
        }
        
        $athlete = array();
        $perf = array();
        $iperf = array();
        break;
        
        case "PERFORMANCE":
        $bPerf = false;
        break;
        case "PERFORMANCEINDOOR":
        $biPerf = false;
        break;
        case "ACCOUNT":    
        $bAccount = false;
        
        // trim xml nodes for eliminating whitespaces at the end <<--- !!!!! important
        $account['ACCOUNTCODE'] = trim($account['ACCOUNTCODE']);
        $account['ACCOUNTNAME'] = trim($account['ACCOUNTNAME']);
        $account['ACCOUNTSHORT'] = (trim($account['ACCOUNTSHORT'])!='') ? trim($account['ACCOUNTSHORT']) : $account['ACCOUNTNAME'];
        $account['ACCOUNTTYPE'] = trim($account['ACCOUNTTYPE']);
        $account['LG'] = trim($account['LG']);
                    
        //
        // save account with relays and svm teams
        //
        if(empty($account['ACCOUNTCODE'])){
            $account = array();
            $relay = array();
            $svm = array();
            break; } //prevent from empty entrys
        if(empty($account['ACCOUNTSHORT'])){
            $account = array();
            $relay = array();
            $svm = array();
            break; } //prevent from empty entrys
        if(empty($account['ACCOUNTNAME'])){
            $account = array();
            $relay = array();
            $svm = array();
            break; } //prevent from empty entrys
        
        // add account to global clubstore
        $GLOBALS['clubstore'][] = $account['ACCOUNTCODE'];
        
        // check if account exists
        $res = mysql_query("SELECT account_code FROM base_account WHERE account_code = '".trim($account['ACCOUNTCODE'])."'");
        
        if(mysql_num_rows($res) == 0){
            
            mysql_query("    INSERT IGNORE INTO
                        base_account (
                            account_code
                            , account_name
                            , account_short
                            , account_type
                            , lg)
                        VALUES (
                            '".$account['ACCOUNTCODE']."'
                            , '".addslashes($account['ACCOUNTNAME'])."'
                            , '".addslashes($account['ACCOUNTSHORT'])."'
                            , '".$account['ACCOUNTTYPE']."'
                            , '".$account['LG']."')");
        }else{ // update
            
            mysql_query("    UPDATE base_account
                    SET
                        account_name = '".addslashes($account['ACCOUNTNAME'])."'
                        , account_short = '".addslashes($account['ACCOUNTSHORT'])."'
                        , account_type = '".$account['ACCOUNTTYPE']."'
                        , lg = '".$account['LG']."'
                    WHERE
                        account_code = '".$account['ACCOUNTCODE']."'");
        }
        
        if(mysql_errno() > 0){
            XML_db_error(mysql_errno().": ".mysql_error());
        }else{
            //
            // update table "verein"
            //
            
            if(trim($account['ACCOUNTTYPE']) != ""){
                $xVerein = "";
                $result = mysql_query("SELECT xVerein FROM verein WHERE TRIM(xCode) = '".trim(addslashes($account['ACCOUNTCODE']))."'");
                /*if(mysql_errno() > 0){
                    echo mysql_error();
                    die();
                }*/
                if(mysql_num_rows($result) == 0){
                    $result2 = mysql_query("SELECT xVerein FROM verein WHERE TRIM(Name) = '".trim(addslashes($account['ACCOUNTNAME']))."'");
                    if(mysql_num_rows($result2) > 0){ 
                           $row2 = mysql_fetch_array($result2); 
                           $xVerein = $row2[0];  
                           // update
                           $sql = "UPDATE verein 
                               SET Name = '".trim(addslashes($account['ACCOUNTNAME']))."', 
                                   Sortierwert = '".trim(addslashes($account['ACCOUNTSHORT']))."', 
                                   xCode = '".trim($account['ACCOUNTCODE'])."', 
                                   Geloescht = 0 
                             WHERE xVerein = ".$xVerein.";";
                    mysql_query($sql);
                    }
                    else {                   
                        // insert     
                        $sql = "INSERT INTO verein 
                                    SET Name = '".trim(addslashes($account['ACCOUNTNAME']))."', 
                                        Sortierwert = '".trim(addslashes($account['ACCOUNTSHORT']))."', 
                                        xCode = '".trim($account['ACCOUNTCODE'])."';";
                        mysql_query($sql);                    
                        $xVerein = mysql_insert_id();
                    }
                }else{
                    $row = mysql_fetch_array($result);
                    $xVerein = $row[0];
                    
                    // update
                    $sql = "UPDATE verein 
                               SET Name = '".trim(addslashes($account['ACCOUNTNAME']))."', 
                                   Sortierwert = '".trim(addslashes($account['ACCOUNTSHORT']))."', 
                                   xCode = '".trim($account['ACCOUNTCODE'])."', 
                                   Geloescht = 0 
                             WHERE xVerein = ".$xVerein.";";
                    mysql_query($sql);
                }
                
                //$xAccount = mysql_insert_id();
                foreach($relay as $row){
                    if(empty($row['ID'])){ continue; } //prevent from empty entrys
                    if($row['ISATHLETICAGENERATED'] == 0){ $row['ISATHLETICAGENERATED']='n'; }else{ $row['ISATHLETICAGENERATED']='y'; }
                    $row['RELAYNAME'] = mysql_real_escape_string(str_replace("\r", "\n", trim($row['RELAYNAME']))); 
                    $row['LICESECATEGORY'] = mysql_real_escape_string(str_replace("\r", "\n", trim($row['LICESECATEGORY']))); 
                    $row['SPORTDISCIPLINE'] = mysql_real_escape_string(str_replace("\r", "\n", trim($row['SPORTDISCIPLINE'])));                  
                    mysql_query("    INSERT IGNORE INTO
                                base_relay (
                                    id_relay
                                    , is_athletica_gen
                                    , relay_name
                                    , category
                                    , discipline
                                    , account_code)
                                VALUES (
                                    '".$row['ID']."'
                                    ,'".$row['ISATHLETICAGENERATED']."'
                                    ,'".addslashes($row['RELAYNAME'])."'
                                    ,'".$row['LICESECATEGORY']."'
                                    ,'".$row['SPORTDISCIPLINE']."'
                                    ,'".$xVerein."')");      
                                   
                    if(mysql_errno() > 0){                            
                        XML_db_error(mysql_errno().": ".mysql_error());
                    }else{
                        // ok
                    }
                }
                foreach($svm as $row){
                    if(empty($row['ID'])){ continue; } //prevent from empty entrys
                    if($row['ISATHLETICAGENERATED'] == 0){ $row['ISATHLETICAGENERATED']='n'; }else{ $row['ISATHLETICAGENERATED']='y'; }                     
                    $row['SVMCATEGORY']= mysql_real_escape_string(str_replace("\r", "\n", trim($row['SVMCATEGORY'])));    
                    $row['SVMNAME']= mysql_real_escape_string(str_replace("\r", "\n", trim($row['SVMNAME'])));                                                                                                                   
                    mysql_query("    INSERT IGNORE INTO
                                base_svm (
                                    id_svm
                                    , is_athletica_gen
                                    , svm_name
                                    , svm_category
                                    , account_code)
                                VALUES (
                                    '".$row['ID']."'
                                    ,'".$row['ISATHLETICAGENERATED']."'
                                    ,'".addslashes($row['SVMNAME'])."'
                                    ,'".$row['SVMCATEGORY']."'
                                    ,'".$xVerein."')");
                  
                    if(mysql_errno() > 0){                               
                        XML_db_error(mysql_errno().": ".mysql_error());
                       
                    }else{
                        // ok
                    }
                }
            }
        }
        $account = array();
        $relay = array();
        $svm = array();
        break;
        case "RELAY":
        $bRelay = false;
        break;
        case "SVM":
        $bSvm = false;
        break;
    }
}

function XML_base_data($parser, $data){
    global $bAthlete, $bPerf, $biPerf, $bAccount, $bRelay, $bSvm, $athlete, $perf, $iperf, $account, $relay, $svm, $cName;
    
    if($bAthlete && !$bPerf){
        $athlete[$cName] .= $data;
    }
    if($bAthlete && $bPerf && !$biPerf){
        $perf[(count($perf)-1)][$cName] .= $data;
    }
    if($bAthlete && !$bPerf && $biPerf){
        $iperf[(count($iperf)-1)][$cName] .= $data;
    }
    if($bAccount && !$bRelay && !$bSvm){
        $account[$cName] .= $data;
    }
    if($bAccount && $bRelay && !$bSvm){
        $relay[(count($relay)-1)][$cName] .= $data;
    }
    if($bAccount && !$bRelay && $bSvm){
        $svm[(count($svm)-1)][$cName] .= $data;
    }
    
}

/* handling result data **********************************************************************************************************/
function XML_result_start($parser, $name, $attr){
    
}

function XML_result_end($parser, $name){
    
}

function XML_result_data($parser, $data){
    
}

/* handling registration data ****************************************************************************************************/
function XML_reg_start($parser, $name, $attr){
    global $discode, $catcode, $xDis, $distype, $bCombined;
    global $strBaseAthleteNotFound, $strBaseTeamNotFound , $strBaseRelayNotFound, $strLicenseNr;
    global $cfgDisciplineType, $cfgEventType, $strEventTypeSingleCombined, 
        $strEventTypeClubCombined, $strDiscTypeTrack, $strDiscTypeTrackNoWind, 
        $strDiscTypeRelay, $strDiscTypeDistance, $strErrNoSuchDisCode, $strNoSuchCategory;
    global $cfgCombinedDef, $cfgCombinedWO, $appnbr;   
    global $cfgSVM, $relay_id, $relay_pos, $relay_round, $relay_xStart, $category, $team_type, $paid;
    
    global $arr_noCat, $cfgResDisc, $cfgSvmDiscFirst, $cfgSvmDiscLast;          
  
    //
    // get approval number
    //
    if($name == "MEETDATASET"){
        $appnbr = $attr['APPROVAL'];
        
        mysql_query("UPDATE meeting SET Nummer = '$appnbr' WHERE xMeeting = ".$_COOKIE['meeting_id']);
        
        $team_type = '';
    }


    //
    // get costs (entry_fee, entry_fee_reduction, penalty)
    //
    if($name == "MEETDATASET"){
        $meet_fee_red = $attr['ENTRY_FEE_REDUCTION'];
        
        mysql_query("UPDATE meeting SET StartgeldReduktion = '$meet_fee_red' WHERE xMeeting = ".$_COOKIE['meeting_id']);
    }
    
    if($name == "MEETDATASET"){
        $meet_fee = $attr['ENTRY_FEE'];
        
        mysql_query("UPDATE meeting SET Startgeld = '$meet_fee' WHERE xMeeting = ".$_COOKIE['meeting_id']);
    }

    if($name == "MEETDATASET"){
        $meet_penalty = $attr['PENALTY'];
        
        mysql_query("UPDATE meeting SET Haftgeld = '$meet_penalty' WHERE xMeeting = ".$_COOKIE['meeting_id']);
        $_SESSION['meeting_infos']['Haftgeld']=$meet_penalty;
    }

    
    //
    // start of discipline
    //
    if($name == "DISCIPLINE"){     
        
        $xDis = array();
        $discode = $attr['DISCODE'];     
        
        $svmCategoryCode = $attr['SVMCATEGORYCODE'];                                       
        if ($svmCategoryCode == '36_09'){
             $catcode = 'U12M';
        } 
        else {
            $catcode = $attr['CATCODE'];   
        }
        
        $disname = trim($attr['DISNAME']); // special name of discipline, ordinary disname + info (cold be user defined)
        $disinfo = trim($attr['DISINFO']); // special name of discipline, without ordinary disc-name (cold be user defined)
        $disspecial = $attr['DISSPECIAL'];
        $disfee = 0;
        $disfee = $attr['DISFEE']/100;
        $disid = $attr['DISID'];
        $type = $attr['TYPE'];    
        
        $svmCatCode = 0;
        $xCatSvm = 0;
        
        if (isset($attr['SVMCATEGORYCODE'])){
              $svmCatCode = $attr['SVMCATEGORYCODE'];  
              // get svm xCat
              $res = mysql_query("SELECT xKategorie_svm FROM kategorie_svm WHERE Code = '$svmCatCode'");
               if(mysql_errno() > 0){
                    XML_db_error("23-".mysql_errno().": ".mysql_error());
               }
              $row = mysql_fetch_row($res);
              $xCatSvm = $row[0];
                
        }            
       
                                    
        $relay_id = 0;
      
        $relay_round = 0;      
            
            // check discode and return if it doesn't exists
            $res = mysql_query("SELECT xDisziplin FROM disziplin_" . $_COOKIE['language'] . " WHERE Code = '$discode'");
            if(mysql_errno() == 0){
                if(mysql_num_rows($res) == 0 && ($discode < $cfgResDisc && $discode > $cfgSvmDiscLast )){
                    echo "<p>$strErrNoSuchDisCode $disname ($discode)</p>";
                    return;
                }
                mysql_free_result($res);
            }
           
            
            // if this is a self specified discipline, check onlineid to differentiate
            $SQLdisSpecial = "";
            if($disspecial == "y"){
                $SQLdisSpecial = " AND w.OnlineId = '$disid' ";
            }else{
                $disname = ""; // do not fill the info-field with disname if there is no need
            }
            

            // if this is a discipline with info, check additonal the info to differentiate
            $SQLdisInfo = "";
            if(strlen($disinfo) != 0){
                //$SQLdisInfo = " AND w.OnlineId = '$disid' ";
                $SQLdisInfo = " AND w.Info = '$disinfo' "; 
                $disname = $disinfo;
            }else{
                $disname = ""; // do not fill the info-field with disname if there is no need
            }

            
            // combined event
            if(isset($cfgCombinedDef[$discode])){
                $bCombined = true;
                // check if this combined event exists   
                 
                 $query = "SELECT * 
                           FROM
                                wettkampf as w
                                LEFT JOIN kategorie as k  ON (w.xKategorie = k.xKategorie)
                                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (w.xDisziplin = d.xDisziplin)
                            WHERE  
                                w.xMeeting = ".$_COOKIE['meeting_id']."
                                AND k.Code = '$catcode'
                                AND w.Mehrkampfcode = $discode";     
               
                $res = mysql_query($query);   
                             
            }else{
                $bCombined = false;
                
                 if ($discode >= $cfgSvmDiscFirst && $discode <= $cfgSvmDiscLast) {  
                         $query = "SELECT * 
                                   FROM
                                        wettkampf as w
                                        LEFT JOIN kategorie as k  ON ( w.xKategorie = k.xKategorie)
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d  ON (w.xDisziplin = d.xDisziplin)
                                    WHERE  
                                       w.xMeeting = ".$_COOKIE['meeting_id']."                                           
                                       AND xKategorie_svm = " . $xCatSvm ."                            
                                       AND w.Mehrkampfcode = 0
                                       $SQLdisSpecial 
                                       $SQLdisInfo ";            
                        
                        $res = mysql_query($query);  
                          while($row_dis = mysql_fetch_assoc($res)){
                            $xDis[] = $row_dis['xWettkampf'];
                        }
                 }
                 else {   
                        // check if this discipline exists                         
                        //    important: Mehrkampfcode has to be 0, else the query will also select 
                        //        the already defined combined events (if the same discipline)     
                                    
                         $query = "SELECT * 
                                   FROM
                                        wettkampf as w
                                        LEFT JOIN kategorie as k  ON ( w.xKategorie = k.xKategorie)
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d  ON (w.xDisziplin = d.xDisziplin)
                                    WHERE  
                                       w.xMeeting = ".$_COOKIE['meeting_id']."
                                       AND k.Code = '$catcode'
                                       AND d.Code = $discode     
                                       AND xKategorie_svm = " . $xCatSvm ."                            
                                       AND w.Mehrkampfcode = 0
                                       $SQLdisSpecial 
                                       $SQLdisInfo ";            
                                                        
                        $res = mysql_query($query);  
                        
                 } 
            }
            if(mysql_errno() > 0){
                XML_db_error("1-".mysql_errno().": ".mysql_error());
            }else{    
                
                 $res_catcode = mysql_query("SELECT xKategorie FROM kategorie WHERE Code = '$catcode'");
                 if(mysql_errno() > 0){
                            XML_db_error("2-".mysql_errno().": ".mysql_error());
                 }else{
                           
                            $row_catcode = mysql_fetch_array($res_catcode);
                            $category = $row_catcode[0];
                            if($bCombined){ // create combined disciplines
                                if(mysql_num_rows($res) == 0){
                                     $_POST['combinedtype'] = $discode; // needed by addCombinedEvent  
                                     $_POST['cat'] = $row_catcode[0]; // needed by addCombinedEvent
                            
                                     AA_meeting_addCombinedEvent($disfee, $_SESSION['meeting_infos']['Haftgeld']/100);
                                       //$xDis = $cfgCombinedWO[$cfgCombinedDef[$discode]];
                                }
                            }
                 }
                
                if($bCombined){ // create combined disciplines
                    if(mysql_num_rows($res) == 0){                                
                        
                        // select again to get all generated xWettkampf ids  
                                    
                          $query = "SELECT 
                                        w.xWettkampf 
                                    FROM
                                        wettkampf as w
                                        LEFT JOIN kategorie as k ON (w.xKategorie = k.xKategorie)
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON ( w.xDisziplin = d.xDisziplin)
                                    WHERE  
                                        w.xMeeting = ".$_COOKIE['meeting_id']."
                                        AND    k.Code = '$catcode'
                                        AND    w.Mehrkampfcode = $discode";                                       
                        
                            $res = mysql_query($query);                               
                                    
                        while($row_dis = mysql_fetch_assoc($res)){
                            $xDis[] = $row_dis['xWettkampf'];
                        }
                    }else{
                        // combined event already exists, get existing disciplines
                        while($row_dis = mysql_fetch_assoc($res)){
                            $xDis[] = $row_dis['xWettkampf'];
                        }
                    }
                    
                }else{ // create single disciplines
                    if ($discode < $cfgResDisc || $discode > $cfgSvmDiscLast) {                       
                        if(mysql_num_rows($res) == 0 ){ //discipline does not exist
                            // insert
                            //    ($disname will be empty if this is not a "special" discipline)      
                            $sql="INSERT INTO 
                                        wettkampf (xKategorie, xDisziplin, xMeeting
                                            , Info, Haftgeld, Startgeld, OnlineId)
                                    SELECT 
                                        k.xKategorie
                                        , d.xDisziplin
                                        , ".$_COOKIE['meeting_id']."
                                        , '$disname'
                                        , " .($_SESSION['meeting_infos']['Haftgeld']/100)."
                                        , '$disfee'
                                        , '$disid'
                                    FROM
                                        disziplin_" . $_COOKIE['language'] . " as d
                                        , (kategorie as k)
                                    WHERE    d.Code = $discode
                                    AND    k.Code = '$catcode'";
                                    
                            mysql_query($sql);   
                                    
                            if(mysql_errno() > 0){
                                XML_db_error("3-".mysql_errno().": ".mysql_error());
                            }else{
                                $xDis[] = mysql_insert_id();
                            }  
                        }else{
                            $row_dis = mysql_fetch_assoc($res);
                            $xDis[] = $row_dis['xWettkampf'];
                        }
                    }
                   
                }  
            }
        
        
    }
    //
    // start of an athlete
    //
    if($name == "ATHLETE" && (count($xDis) != 0 || $discode == $cfgResDisc || ($discode >= $cfgSvmDiscFirst && $discode <= $cfgSvmDiscLast))){        
             
        $license = $attr['LICENSE'];       
        
        if (isset($attr['PAID'])){  
            $paid = $attr['PAID'];
        }   
         
        $relayDisc = AA_checkRelayDisc($discode);
        
        $effort = $attr['NOTIFICATIONEFFORT'];
                                              
        if ($relay_id > 0){
             $relay_pos++;
        }
        $team_id = 0;
        $xTeam = 0;      
        
        if (isset($attr['SVMTEAMID'])){
             $team_id =  $attr['SVMTEAMID'];   
             
             $sql= "SELECT * FROM team WHERE xTeam = " .$team_id;
             $res = mysql_query($sql);
             if (mysql_num_rows($res) == 0) {
                 return;
             }
        }
        
        if ($relay_id == 0 && $relayDisc){
              // if event is a relay and the relay-id doesn't exist --> no announcement for the athletes
        }
        else {              
            $athlete_id = 0;
            
            $sql2 = "SELECT TRIM(lastname) AS lastname, 
                            TRIM(firstname) AS firstname, 
                            substring(birth_date, 1,4) AS jahrgang, 
                            license, 
                            TRIM(sex) AS sex, 
                            nationality, 
                            birth_date, 
                            account_code, 
                            second_account_code,
                            id_athlete ,
                            account_info
                       FROM base_athlete 
                      WHERE license = '".$license."';";
            $query2 = mysql_query($sql2);
            
            if($query2 && mysql_num_rows($query2)==1){
                $row2 = mysql_fetch_assoc($query2);
                
                $club = $row2['account_code'];
                $club2 = $row2['second_account_code'];
                
                $vInfo = $row2['account_info'];
                
                $athlete_id = $row2['id_athlete'];
                $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
                if(mysql_errno() > 0){
                    XML_db_error("6-".mysql_errno() . ": " . mysql_error());
                }else{
                    $rowClub1 = mysql_fetch_array($result2);
                    $club = $rowClub1[0];
                    if(!empty($club2)){
                        $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club2."'");
                        if(mysql_errno() > 0){
                            XML_db_error("7-".mysql_errno() . ": " . mysql_error());
                            $club2 = 0; // prevents from insert error in next statement
                        }else{
                            $rowClub2 = mysql_fetch_array($result2);
                            $club2 = $rowClub2[0];
                        }
                    }
                    else {
                         $club2 = 0;
                    }
                }
                mysql_free_result($result2);
             
                 // check if there are manual changes     
                $sql4 = "SELECT Vorname, Name, xVerein, Manuell FROM athlet WHERE Lizenznummer = '".$license ."'";
                $query4 = mysql_query($sql4);
               
                if(mysql_num_rows($query4) > 0){
                    $row4 = mysql_fetch_assoc($query4);
                   
                    if ($row4['Manuell'] == '0' || $GLOBALS['mode'] == 'overwrite' ){         // overwrite the manual changes
                
                        $sql3 = "UPDATE athlet 
                            SET Name = '".addslashes(trim($row2['lastname']))."', 
                                Vorname = '".addslashes(trim($row2['firstname']))."', 
                                Jahrgang = '".trim($row2['jahrgang'])."', 
                                Geschlecht = '".trim($row2['sex'])."', 
                                Land = '".trim($row2['nationality'])."', 
                                Geburtstag = '".trim($row2['birth_date'])."', 
                                xVerein = '".trim($club)."', 
                                xVerein2 = '".trim($club2)."', 
                                Lizenznummer = '".trim($license)."',
                                 Manuell = 0   
                          WHERE (Lizenznummer = '".trim($license)."' 
                             OR (Name = '".addslashes (trim($row2['lastname']))."' 
                            AND Vorname = '".addslashes(trim($row2['firstname']))."' 
                            AND Jahrgang = '".trim($row2['jahrgang'])."' 
                            AND xVerein = '".trim($club)."'));";
                       
                          
                    }
                    
                    else {  
                          switch ($row4['Manuell']){
                            case 1: $firstname = trim($row2['firstname']); 
                                    $name = trim($row4['Name']);
                                    $verein = trim($club);
                                    break;
                            case 2: $firstname = $row4['Vorname'];  
                                    $name = trim($row2['lastname']);
                                    $verein = trim($club);
                                     break;
                            case 3: $firstname = trim($row2['firstname']);
                                    $name = trim($row2['lastname']);
                                    $verein = $row4['xVerein'];
                                    break;    
                            case 4: $firstname = $row4['Vorname'];
                                    $name = $row4['Name'];
                                    $verein = trim($club);
                                    break;   
                            case 5: $firstname = trim($row2['firstname']);    
                                    $name = $row4['Name'];
                                    $verein = $row4['xVerein'];
                                    break;  
                            case 6: $firstname = $row4['Vorname']; 
                                    $name =trim($row2['lastname']);  
                                    $verein = $row4['xVerein'];
                                    break; 
                            case 7: $firstname = $row4['Vorname'];
                                    $name = $row4['Name'];
                                    $verein = $row4['xVerein'];
                                    break;                                                  
                            default: 
                                    break;
                        }
                         
                        $sql3 = "UPDATE athlet 
                            SET Name = '".addslashes($name)  ."', 
                                Vorname = '".addslashes($firstname) ."', 
                                Jahrgang = '".trim($row2['jahrgang'])."', 
                                Geschlecht = '".trim($row2['sex'])."', 
                                Land = '".trim($row2['nationality'])."', 
                                Geburtstag = '".trim($row2['birth_date'])."', 
                                xVerein = '".$verein."', 
                                xVerein2 = '".trim($club2)."', 
                                Lizenznummer = '".trim($athlete['LICENSE'])."'                           
                          WHERE (Lizenznummer = '".trim($athlete['LICENSE'])."' 
                             OR (Name = '".addslashes(trim($row2['lastname']))."' 
                            AND Vorname = '".addslashes(trim($row2['firstname']))."' 
                            AND Jahrgang = '".trim($row2['jahrgang'])."' 
                            AND xVerein = '".trim($club)."'));";   
                      
                    }   
                   $query3 = mysql_query($sql3); 
                   if(mysql_errno() > 0){                                 
                        XML_db_error("4-".mysql_errno().": ".mysql_error());       
                     }        
                 }   
            }
            
            // check if athlete is already in "athlet" table
            $result = mysql_query("SELECT xAthlet FROM athlet WHERE Lizenznummer = $license");
            if(mysql_errno() > 0){                       
                XML_db_error("4-".mysql_errno().": ".mysql_error());
            }else{
                //first copy athlete from base
                if(mysql_num_rows($result) == 0){  
                    
                    $sql = "SELECT * FROM base_athlete
                        WHERE license = $license";
                    $res = mysql_query($sql);
                    if(!$res){
                        AA_printErrorMsg("5-".mysql_errno() . ": " . mysql_error());
                    }else{
                        // check if athlete exists in base
                        if(mysql_num_rows($res) == 0){ // athlete not found in base data
                            echo "<p>$strBaseAthleteNotFound: $strLicenseNr $license</p>\n";
                            $xAthlete = 0;
                        }else{
                            
                            // get club id from club code
                            $row = mysql_fetch_assoc($res);
                            $club = $row['account_code'];
                            $club2 = $row['second_account_code'];
                            $athlete_id = $row['id_athlete'];
                            $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
                            if(mysql_errno() > 0){
                                XML_db_error("6-".mysql_errno() . ": " . mysql_error());
                            }else{
                                $rowClub1 = mysql_fetch_array($result2);
                                $club = $rowClub1[0];
                                if(!empty($club2)){
                                    $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club2."'");
                                    if(mysql_errno() > 0){
                                        XML_db_error("7-".mysql_errno() . ": " . mysql_error());
                                        $club2 = 0; // prevents from insert error in next statement
                                    }else{
                                        $rowClub2 = mysql_fetch_array($result2);
                                        $club2 = $rowClub2[0];
                                    }
                                } else {
                                    $club2 = 0;
                                }
                            }
                            mysql_free_result($result2);
                            
                            // if club is valid
                            // insert athlete from base data
                            if(is_numeric($club)){
                                $sql = "INSERT IGNORE INTO athlet 
                                            (Name, Vorname, Jahrgang, 
                                            Lizenznummer, Geschlecht, Land, 
                                            Geburtstag, xVerein, xVerein2)
                                        SELECT 
                                            TRIM(lastname), TRIM(firstname), substring(birth_date, 1,4), 
                                            license, TRIM(sex), nationality, 
                                            birth_date, '$club', '$club2'
                                        FROM
                                            base_athlete
                                        WHERE
                                            license = $license";
                                mysql_query($sql);
                                
                                if(mysql_errno() > 0){  
                                    XML_db_error("8-".mysql_errno().": ".mysql_error());
                                   
                                }else{
                                    $xAthlete = mysql_insert_id();
                                    
                                    if($xAthlete == 0 || empty($xAthlete)){
                                        echo "<p>++: $strBaseAthleteNotFound: $strLicenseNr $license</p>\n";
                                    }
                                }
                            }                          
                        } // end athlete found
                    }
                    
                    
                }else{ // athlete already available
                    
                    // check if athlete is still in base data (he could be deleted)
                    $sql = "SELECT * FROM base_athlete
                        WHERE license = $license";
                    $res = mysql_query($sql);
                    if(mysql_errno() > 0){
                        XML_db_error("5b-".mysql_errno() . ": " . mysql_error());
                    }else{
                        
                        if(mysql_num_rows($res) == 0){ // athlete deleted
                            echo "<p>$strBaseAthleteNotFound (deleted): $strLicenseNr $license</p>\n";
                            $xAthlete = 0;
                        }else{
                            // athlete available, get id
                            $row = mysql_fetch_array($result);
                            $xAthlete = $row[0];
                        }
                        
                    }
                    
                }
                
                if($xAthlete > 0){                  
                    
                    // check if already registered 
                    $result = mysql_query("SELECT xAnmeldung, xTeam , xKategorie FROM anmeldung WHERE xAthlet = $xAthlete AND xMeeting = ".$_COOKIE['meeting_id']."");
                  
                    if(mysql_errno() > 0){
                        XML_db_error("9-".mysql_errno().": ".mysql_error());
                    }else{
                        if(mysql_num_rows($result) == 0){ // not yet registered
                        
                            // get license category from base data       
                            $result = mysql_query("    
                                        SELECT k.xKategorie FROM
                                            kategorie as k
                                            , base_athlete as b
                                        WHERE b.license = $license
                                        AND k.Code = b.license_cat");
                            if(mysql_errno() > 0){
                                XML_db_error("10-".mysql_errno().": ".mysql_error());
                            }else{
                                  if (mysql_num_rows($result) > 0) {  
                                  
                                    $row = mysql_fetch_array($result);
                                  
                                    $xCat = $row[0];                                    
                                    
                                    if($xCat!=''){
                                        mysql_query("    INSERT INTO anmeldung SET
                                                    Startnummer = 0
                                                    , Bezahlt = '$paid'
                                                    , xAthlet = $xAthlete
                                                    , xMeeting = ".$_COOKIE['meeting_id']."
                                                    , xKategorie = $xCat
                                                    , Vereinsinfo = '$vInfo'
                                                    , xTeam = " .$team_id );                                             
                                        if(mysql_errno() > 0){ 
                                            XML_db_error("11-".mysql_errno().": ".mysql_error());
                                        }else{
                                            $xReg = mysql_insert_id();
                                        }
                                    } else {
                                        $result2 = mysql_query("SELECT license_cat 
                                                                  FROM base_athlete
                                                                 WHERE license = $license;");
                                        $row2 = mysql_fetch_array($result2);
                                        $license_cat = $row2[0];
                                        XML_db_error(str_replace('%cat%', $license_cat, $strNoSuchCategory));
                                    }
                                }
                                else {
                                    
                                }
                            }
                        }else{ // registered
                            $row = mysql_fetch_array($result);
                            $xReg = $row[0];
                            $xTeam = $row[1];  
                            $xCat = $row[2];                             
                          
                            if ($xTeam != $team_id && $team_id > 0){     
                                   if ($xTeam > 0){   
                                       if (!in_array($xTeam, $arr_noCat[$xAthlete])) {
                                           $arr_noCat[$xAthlete][] = $xTeam;   
                                       }
                                   }
                                   if (!in_array($team_id, $arr_noCat[$xAthlete])) {
                                       $arr_noCat[$xAthlete][] = $team_id;   
                                   } 
                            } 
                        }  
                        
                        if($bCombined){
                            // effort are points, saved on registration
                            $sql = "SELECT notification_effort 
                                    FROM base_performance
                                    WHERE id_athlete = $athlete_id
                                    AND    discipline = $discode";
                            
                            $res_effort = mysql_query($sql);
                            if(mysql_errno() > 0){
                                XML_db_error("12-".mysql_errno().": ".mysql_error());
                            }else{
                                if(mysql_num_rows($res_effort) > 0){
                                    $row_effort = mysql_fetch_assoc($res_effort);
                                    $effort = $row_effort['notification_effort'];
                                    
                                    mysql_query("UPDATE anmeldung SET
                                            BestleistungMK = '$effort'
                                        WHERE
                                            xAnmeldung = $xReg");
                                }
                            }
                        }
                        
                        if($xReg > 0){  
                            // check if athlete alredy starts for this discipline(s)
                            foreach($xDis as $xDis1){
                                // because we can get multiple disciplines (combined event),
                                // it is nessesary to determinate distype and discode for each discipline
                                // (catcode won't change)          
                                $res_distype = mysql_query("
                                            SELECT 
                                                d.Typ, d.Code 
                                            FROM 
                                                disziplin_" . $_COOKIE['language'] . " as d
                                                LEFT JOIN wettkampf as w ON (w.xDisziplin = d.xDisziplin)
                                            WHERE 
                                                w.xWettkampf = $xDis1");
                                if(mysql_errno() > 0){
                                    XML_db_error("13-".mysql_errno().": ".mysql_error());
                                }else{
                                    $row_distype = mysql_fetch_Array($res_distype);
                                    $distype = $row_distype[0];
                                    $temp_discode = $row_distype[1];  
                                    
                                }
                                
                                $result = mysql_query("SELECT xStart FROM start WHERE xAnmeldung = $xReg and xWettkampf = $xDis1");
                                if(mysql_errno() > 0){                                    
                                    XML_db_error("14-".mysql_errno().": ".mysql_error());
                                }else{
                                    if(mysql_num_rows($result) == 0){ // not yet starting, add start
                                        
                                        $saison = $_SESSION['meeting_infos']['Saison'];
                                        if ($saison == ''){
                                            $saison = "O"; //if no saison is set take outdoor
                                        }
                                        
                                        if(!$bCombined){
                                            // check on notification effort. 
                                            $res_effort = mysql_query("
                                                    SELECT * FROM base_performance
                                                    WHERE    id_athlete = $athlete_id
                                                    AND    discipline = $temp_discode
                                                    AND    category = '$catcode'");
                                                    
                                            $sql = "SELECT * FROM base_performance
                                                    WHERE    id_athlete = $athlete_id
                                                    AND    discipline = $temp_discode
                                                    AND    category = '$catcode'";
                                        } else {
                                            $sql = "SELECT
                                                    base_performance.notification_effort
                                                FROM
                                                    athletica.base_performance
                                                    INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
                                                        ON (base_performance.discipline = d.Code)
                                                    INNER JOIN athletica.wettkampf 
                                                        ON (d.xDisziplin = wettkampf.xDisziplin)
                                                WHERE (base_performance.id_athlete =$athlete_id
                                                    AND wettkampf.xWettkampf =$xDis1
                                                    AND wettkampf.xMeeting =".$_COOKIE['meeting_id']."
                                                    AND base_performance.season ='I')";
                                            
                                            $res_effort = mysql_query($sql);    
                                        }    
                                        
                                        
                                        if(mysql_errno() > 0){                                            
                                            XML_db_error("15-".mysql_errno().": ".mysql_error());
                                        }else{
                                            if(mysql_num_rows($res_effort) > 0){
                                                $row_effort = mysql_fetch_assoc($res_effort);
                                                $effort = $row_effort['notification_effort'];
                                            }
                                            //
                                            // convert effort
                                            //
                                            if(($distype == $cfgDisciplineType[$strDiscTypeTrack])
                                                || ($distype == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                                                || ($distype == $cfgDisciplineType[$strDiscTypeRelay])
                                                || ($distype == $cfgDisciplineType[$strDiscTypeDistance]))
                                                {
                                                $pt = new PerformanceTime($effort);
                                                $perf = $pt->getPerformance();
                                                
                                            }
                                            else {
                                                
                                                $pa = new PerformanceAttempt($effort);
                                                $perf = $pa->getPerformance();
                                                //$perf = (ltrim($effort,"0"))*100;
                                            }
                                            if($perf == NULL) {    // invalid performance
                                                $perf = 0;
                                            }
                                            $sql = "INSERT INTO start SET
                                                        xWettkampf = $xDis1
                                                        , Bezahlt = '$paid'
                                                        , xAnmeldung = $xReg
                                                        , Bestleistung = '".$perf."' 
                                                        , BaseEffort = 'y'";
                                                        
                                            $res = mysql_query($sql);
                                            if(mysql_errno() > 0){
                                                XML_db_error("16-".mysql_errno().": ".mysql_error());
                                            }
                                            
                                            if ($relay_id > 0) {
                                                $xStart = mysql_insert_id();
                                               
                                                $sql = "INSERT INTO staffelathlet SET
                                                            xStaffelstart=" . $relay_xStart . " 
                                                            , xAthletenstart = " . $xStart ."
                                                            , xRunde = " . $relay_round ."
                                                            , Position = " . $relay_pos;
                                                           
                                                            
                                                $res = mysql_query($sql);
                                                if(mysql_errno() > 0){
                                                    XML_db_error("16-".mysql_errno().": ".mysql_error());
                                                }
                                            }
                                            
                                        }  
                                    }
                                }
                            } // enf foreach
                        } // end xReg > 0  
                    }
                } // end xAthlete > 0
            }       
      }           
    }
    
    
    //
    // start of a relay
    //
    if($name == "RELAY" && count($xDis) != 0){
        
        $id = $attr['ID'];
        $paid = $attr['PAID'];
        $notificationEffort = $attr['NOTIFICATIONEFFORT'];  
        $teamID = $attr['TEAMID'];    
        $relay_id = 0;
        $xDis1 = $xDis[0];
        $relay_pos = 0;   
       
        // check if relay is already in table staffel
        $res = mysql_query("SELECT xStaffel FROM staffel WHERE xStaffel = $id");
        if(mysql_errno() > 0){
            XML_db_error(mysql_errno().": ".mysql_error());
        }else{
            
            if(mysql_num_rows($res) == 0){   
                //
                // no, insert relay (get category first)
                //                      
                $result = mysql_query("    SELECT k.xKategorie, b.is_athletica_gen, b.relay_name, b.account_code FROM
                                            kategorie as k
                                            LEFT JOIN base_relay as b ON (k.Code = b.category)
                                        WHERE 
                                                b.id_relay = '$id'");
                                                                                                      
                if(mysql_errno() > 0){
                    XML_db_error(mysql_errno().": ".mysql_error());
                }else{
                    
                    if(mysql_num_rows($result) > 0){   
                        $row = mysql_fetch_array($result);
                        $cat = $row[0];
                        
                         if (empty($teamID)) { 
                             $teamID = 0;
                         }
                        mysql_query("
                            INSERT IGNORE INTO staffel
                                (xStaffel, Name, xVerein
                                , xMeeting, xKategorie, Athleticagen, xTeam)
                            SELECT
                                id_relay, relay_name, account_code
                                , ".$_COOKIE['meeting_id'].", '$cat',is_athletica_gen , $teamID
                            FROM
                                base_relay
                            WHERE
                                id_relay = '$id'");                                  
                       
                        if(mysql_errno() > 0){
                            XML_db_error(mysql_errno().": ".mysql_error());
                        }else{                 
                            
                            $relay_id = $id; // do not use mysql_insert_id() - it will return 0
                            // like this i will notice if the insert succeeded or not
                            if(mysql_affected_rows() == 0){
                                echo "Fehler: Staffel ID $id";
                            }
                        }
                    }
                    else {
                          
                            $msg = str_replace('%id%', $id, $strBaseRelayNotFound);   
                            echo "<p>$msg: </p>\n";    
                    }
                }
            }else{
                //
                // yes, relay found
                //         
                
                if (!empty($teamID)) {
                    
                    $sql="UPDATE staffel SET xTeam = " .$teamID ." WHERE xStaffel = " . $id;    
                    
                    mysql_query($sql); 
                     if(mysql_errno() > 0){
                            XML_db_error(mysql_errno().": ".mysql_error());
                     }
                     else{
                         $relay_id = $id; // do not use mysql_insert_id() - it will return 0     
                } 
                }
                else {
                         
                $row = mysql_fetch_array($res);
                $relay_id = $row[0];      
                }              
            }
            
            //
            // add start for discipline
            //
            if($relay_id > 0){
                
                $sql = "SELECT 
                                xStart, xRunde 
                        FROM 
                                start AS s
                                LEFT JOIN runde AS r ON (r.xWettkampf = s.xWettkampf) 
                        WHERE 
                                s.xWettkampf = " . $xDis1 ." AND s.xStaffel = " .$relay_id;
                $result = mysql_query($sql);
               
                if(mysql_errno() > 0){
                    XML_db_error(mysql_errno().": ".mysql_error());
                }else{                         
                                                                                                       
                    if(mysql_num_rows($result) == 0){
                        
                        // insert into table start     
                        
                        mysql_query("
                            INSERT INTO start SET                                 
                                Bezahlt = '" .$paid ."',
                                xWettkampf = $xDis1
                                , xStaffel = $relay_id");                                       
                                
                        if(mysql_errno() > 0){
                            XML_db_error(mysql_errno().": ".mysql_error());
                        }
                        
                        $relay_xStart = mysql_insert_id();
                        
                        if ($team_type == "SVM"){
                                 
                                 $sql = "SELECT 
                                            xRunde 
                                         FROM 
                                            start AS s
                                            LEFT JOIN runde AS r ON (r.xWettkampf = s.xWettkampf) 
                                         WHERE 
                                            s.xWettkampf = " . $xDis1 ." AND s.xStaffel = " .$relay_id;
                                 $res= mysql_query($sql);
                                 if(mysql_errno() > 0){
                                    XML_db_error(mysql_errno().": ".mysql_error());  
                                 }
                                 else {
                                     $row = mysql_fetch_row($res);
                                     $relay_round = $row[0]; 
                                 }
                        }
                        else {     
                                $sql="SELECT 
                                        r.xRunde, w.xWettkampf , d.Code , d.Typ, m.DatumVon
                                     FROM
                                        wettkampf as w
                                        LEFT JOIN runde as r On (w.xWettkampf = r.xWettkampf)
                                        LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin)
                                        LEFT JOIN meeting as m ON (m.xMeeting = w.xMeeting)
                                     WHERE
                                        w.xKategorie = ". $category ."        
                                        AND w.xMeeting = ".$_COOKIE['meeting_id']." 
                                     ORDER BY d.Anzeige";
                             
                            $res= mysql_query($sql);
                            if(mysql_errno() > 0){
                                    XML_db_error(mysql_errno().": ".mysql_error());
                                }
                            else {
                                    $row=mysql_fetch_row($res);
                                    
                                    // check if dummy round already exists
                                    if ($relay_id > 0){                                        
                                       $event =  $xDis1;   
                                    }  
                                    else {
                                       $event =  $row[1];                    
                                    } 
                                   
                                    $sql = "SELECT * FROM runde WHERE xWettkampf =  " . $event;      
                                                         
                                    $res = mysql_query($sql); 
                                    if (mysql_num_rows($res) == 0) {
                                        
                                            // add dummy round for relay
                                            $sql = "INSERT into runde SET
                                                    Datum = '" . $row[4]. "'    
                                                    , xRundentyp = 6 
                                                    , xWettkampf = " . $event;
                                                    
                                            $res = mysql_query($sql);
                                               
                                            if(mysql_errno() > 0){
                                                XML_db_error(mysql_errno().": ".mysql_error());
                                            }
                                            $relay_round = mysql_insert_id();      
                                    }  
                            }                           
                      }                             
                        
                    }else{
                        // already entered
                        $row = mysql_fetch_array($result);
                        $relay_xStart = $row[0];
                        $relay_round = $row[1];   
                        
                        $sql="UPDATE start SET 
                                Bezahlt = '" .$paid ."'
                                WHERE xStart = " .$relay_xStart;
                       
                        mysql_query($sql);
                        
                        if(mysql_errno() > 0){
                                XML_db_error(mysql_errno().": ".mysql_error());
                            }     
                    }  
                }
            }
        }
    }
    
  
    if($name == "TEAM"){            
        $id = $attr['TEAMID'];
        $team_type = $attr['TYPE'];
        $team_name = $attr['NAME'];  
        $svm_cat = $attr['SVMCATEGORYCODE'];  
        $team_id = 0;   
                                   
        if ($team_type == "SVM") {
            
            $teamInBase = true;
            // check if team with same id is already in table team
            $sql = "SELECT xTeam FROM team WHERE xTeam = " . $id ." AND xMeeting = " .$_COOKIE['meeting_id'];   
            $res = mysql_query($sql);
           
            if(mysql_errno() > 0){
                XML_db_error(mysql_errno().": ".mysql_error());
            }else{
                
                if (mysql_num_rows($res) == 0){     
                   
                    //
                    // no, insert relay (get category first)
                    //                                          
                  
                    $sql="SELECT k.Code, b.is_athletica_gen, b.svm_name, b.account_code FROM
                                                kategorie_svm as k
                                                LEFT JOIN base_svm as b ON (k.Code = b.svm_category)
                                            WHERE 
                                                    b.id_svm = '$id'";
                   
                    $result = mysql_query($sql);        
                    if(mysql_errno() > 0){
                        XML_db_error(mysql_errno().": ".mysql_error());
                    }else{
                       if (mysql_num_rows($result) > 0) {
                            $row = mysql_fetch_array($result); 
                           
                            $svmCode = $row[0];                              
                            $cat = 0;
                            if (isset($cfgSVM[$svmCode."_C"])){   
                                $cat_code = $cfgSVM[$svmCode."_C"]; 
                            }
                            $sql = "SELECT xKategorie FROM kategorie WHERE Code = '" . $cat_code. "'";
                            $res = mysql_query($sql);                            
                            if(mysql_errno() > 0){
                                XML_db_error(mysql_errno().": ".mysql_error());
                            }
                            $row_cat = mysql_fetch_row($res); 
                            $cat = $row_cat[0]; 
                            
                            // get svm cat 
                            $sql = "SELECT xKategorie_svm FROM kategorie_svm WHERE Code = '" . $svm_cat. "'";
                            $res = mysql_query($sql);                            
                            if(mysql_errno() > 0){
                                XML_db_error(mysql_errno().": ".mysql_error());
                            }
                            $row_cat_svm = mysql_fetch_row($res); 
                            $xCat_svm = $row_cat_svm[0]; 
                            
                            
                            
                            $sql = "SELECT xTeam FROM team WHERE xKategorie = " . $cat ." AND Name = '" . $team_name ."' AND xKategorie_svm = " . $xCat_svm . " AND xMeeting = " .$_COOKIE['meeting_id'];
                            $res = mysql_query($sql);
                           
                            if(mysql_errno() > 0){
                                XML_db_error(mysql_errno().": ".mysql_error());
                            }                                                             
                           
                            if (mysql_num_rows($res) > 0) {    
                                      $row_team = mysql_fetch_row($res);
                                           
                                      $sql="UPDATE team
                                                    SET 
                                                    xTeam =  " .$row[3] .",
                                                    Athleticagen = '" .$row[1] ."'
                                              WHERE
                                                    xTeam = ".$row_team[0];                                          
                            }
                            else {   
                                                
                                        $sql="INSERT IGNORE INTO team
                                                (xTeam, Name, Athleticagen, xMeeting,
                                                  xVerein , xKategorie, xKategorie_svm )
                                            SELECT
                                                id_svm, svm_name, is_athletica_gen , ".$_COOKIE['meeting_id'].",
                                                account_code ,  '$cat' , '$xCat_svm'
                                               
                                            FROM
                                                base_svm
                                            WHERE
                                                id_svm = '$id'";   
                            }
                           
                            mysql_query($sql);    
                           
                            if(mysql_errno() > 0){                                
                                XML_db_error(mysql_errno().": ".mysql_error());
                            }else{                 
                                
                                $team_id = $id; // do not use mysql_insert_id() - it will return 0
                                // like this i will notice if the insert succeeded or not                                 
                                if(mysql_affected_rows() == 0){
                                    echo "Fehler: Team ID $id";
                                }
                            } 
                       }
                       else {          // team not in base data
                       
                            $msg = str_replace('%name%', $team_name, $strBaseTeamNotFound);
                            $msg = str_replace('%id%', $id, $msg);   
                            echo "<p>$msg: </p>\n";    
                            $teamInBase = false; 
                       }
                    }
                }else{
                    //
                    // yes, team found
                    //                         
                    $row = mysql_fetch_array($res);
                    $team_id = $row[0];                      
                }   
            }    
        }
    }   
    
    if($name == "SVMCATCODE"){  
        
           $svmCode = $attr['SVMCATEGORYCODE']; 
                                       
           //   
           //  create SVM disciplines
       
           $cat = 0;
           if (isset($cfgSVM[$svmCode."_C"])){   
                    $cat_code = $cfgSVM[$svmCode."_C"]; 
           }
                            
           $sql = "SELECT xKategorie FROM kategorie WHERE Code = '" . $cat_code. "'";
           $res = mysql_query($sql);                            
           if(mysql_errno() > 0){
                XML_db_error(mysql_errno().": ".mysql_error());
           }
           $row_cat = mysql_fetch_row($res); 
           $cat = $row_cat[0];                          
           $_POST['cat'] =  $row_cat[0];                  
           
           $res = mysql_query("SELECT ks.xKategorie_svm FROM kategorie_svm AS ks WHERE ks.Code = '$svmCode'");  
           $row = mysql_fetch_array($res);
           
           $_POST['svmcategory'] = $row[0];
           $_POST['svm'] = $row[0];                  
                             
           $sql="SELECT 
                        m.DatumVon
                 FROM      
                        meeting AS m
                 WHERE      
                        m.xMeeting = ".$_COOKIE['meeting_id'];                              
                       
           $res= mysql_query($sql);
           $row= mysql_fetch_row($res); 
                            
           $_POST['date'] =  $row[0]; 
                             
           $_POST['wTyp'] = $cfgSVM[$svmCode."_ET"];     
                     
           AA_meeting_addSVMEvent($_SESSION['meeting_infos']['Startgeld']/100,$_SESSION['meeting_infos']['Haftgeld']/100);              
    }      
    
}

function XML_reg_end($parser, $name){
    global $discode, $catcode, $xDis, $distype, $relay_id;    
    
    // end of discipline
    if($name == "DISCIPLINE"){
        $discode = "";
        $catcode = "";
        $xDis = array();
        $distype = "";
        $bCombined = false;
    }
     if($name == "RELAY"){
        $relay_id = 0;
    }
}

function XML_reg_data($parser, $data){
    
}

/*$temp = new XML_data();
$res = $temp->load_xml("http://slv.exigo.ch/meetings/athletica/export_meeting.php", "reg");
if(!$res){
    echo "false";
}*/




/* handling online registration data (ZLV) ****************************************************************************************************/
function XML_regZLV_start($parser, $name, $attr){
    global $bCombined, $arr_noCat;
    global $strBaseAthleteNotFound, $strLicenseNr;
    global $cfgDisciplineType, 
        $strDiscTypeTrack, $strDiscTypeTrackNoWind, 
        $strDiscTypeRelay, $strDiscTypeDistance,  $strNoSuchCategory;
    global $cfgCombinedDef, $cfgCombinedWO;    
     
     if($name == "ATHLETE"){ 
        
        $regNr = $attr['ANMELDENR'];
        $license = $attr['LIZENZNR'];  
        $name = $attr['NAME'];
        $firstname = $attr['VORNAME'];
        $birth = $attr['GEBDAT'];
        $arr_birth = explode(".",$birth);
        $birthdate = $arr_birth[2] . "-" .  $arr_birth[1] . "-" . $arr_birth[0]; 
        $birthyear = $arr_birth[2];
        $club = $attr['VEREIN'];
        $sex = $attr['GESCHLECHT'];   
        $nationality = $attr['NATIONALITAET'];  
        $cat = $attr['KATEGORIE'];    
        $group = $attr['GRUPPENR'];  
        $starttime = $attr['STARTZEIT'];  
        $license_paid =  $attr['BEZ']; 
        if  ($license_paid == ''){
            $license_paid = 'n';
        }
        $registerNr = $attr['ANMELDENR'];   
        
        $meetingDate = ''; 
        $discode = 0;                      
           
        if ($license >  0){  
          
            // check if event exist for this cateory and select event to get all generated events ids
            $sql = "SELECT 
                            w.xWettkampf,
                            w.Mehrkampfcode,
                            m.DatumVon 
                    FROM
                            wettkampf as w
                            LEFT JOIN kategorie as k on (w.xKategorie = k.xKategorie)
                            LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d on (w.xDisziplin = d.xDisziplin)
                            LEFT JOIN meeting AS m ON (m.xMeeting = w.xMeeting)
                    WHERE 
                            w.xMeeting = ".$_COOKIE['meeting_id']."
                            AND k.Code = '" .$cat ."'";
                   
            $res = mysql_query($sql);
            if(mysql_errno() > 0) {   
                    AA_printErrorMsg("xml-1-".mysql_errno() . ": " . mysql_error());  
            }
            if (mysql_num_rows($res) > 0) {
                while($row_dis = mysql_fetch_assoc($res)){
                    $xDis[] = $row_dis['xWettkampf'];
                    if  ($row_dis['Mehrkampfcode'] > 0) {
                        $discode =  $row_dis['Mehrkampfcode'];     // same for all combined disciplines  
                    }                       
                    $meetingDate =  $row_dis['DatumVon'];    
                }    
                
                $athlete_id = 0;
        
                $sql2 = "SELECT TRIM(lastname) AS lastname, 
                                        TRIM(firstname) AS firstname, 
                                        substring(birth_date, 1,4) AS jahrgang, 
                                        license, 
                                        TRIM(sex) AS sex, 
                                        nationality, 
                                        birth_date, 
                                        account_code, 
                                        second_account_code,
                                        id_athlete,
                                        license_paid 
                                 FROM base_athlete 
                                 WHERE license = '".$license."';";
                $query2 = mysql_query($sql2);
                if(mysql_errno() > 0) {   
                    AA_printErrorMsg("xml-2-".mysql_errno() . ": " . mysql_error());  
                }
                else {   
                    if($query2 && mysql_num_rows($query2)==1){        // athlete exist in base data
                        $row2 = mysql_fetch_assoc($query2);
            
                        $club = $row2['account_code'];
                        $club2 = $row2['second_account_code'];
                        $athlete_id = $row2['id_athlete'];
                        $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
                        if(mysql_errno() > 0){ 
                                    AA_printErrorMsg("xml-3-".mysql_errno() . ": " . mysql_error());
                                     
                        }else{
                            $rowClub1 = mysql_fetch_array($result2);
                            $club = $rowClub1[0];
                            if(!empty($club2)){
                                $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club2."'");
                                if(mysql_errno() > 0){ 
                                    AA_printErrorMsg("xml-4-".mysql_errno() . ": " . mysql_error());
                                    $club2 = 0; // prevents from insert error in next statement
                                }else{
                                    $rowClub2 = mysql_fetch_array($result2);
                                    $club2 = $rowClub2[0];
                                }
                            }
                            else {
                                  $club2 = 0;
                            }
                        }
                        mysql_free_result($result2);
         
                        // check if athlete exist        // check if athlete is already in "athlet" table    
                        $sql4 = "SELECT xAthlet FROM athlet WHERE Lizenznummer = '".$license ."'";
                        $query4 = mysql_query($sql4);
                        if(mysql_errno() > 0) {   
                            AA_printErrorMsg("xml-5-".mysql_errno() . ": " . mysql_error());  
                        }
                        if(mysql_num_rows($query4) > 0){
                                    $row4 = mysql_fetch_assoc($query4);      
                                   
                                    $sql3 = "UPDATE athlet 
                                                    SET Name = '".trim($row2['lastname'])."', 
                                                    Vorname = '".trim($row2['firstname'])."', 
                                                    Jahrgang = '".trim($row2['jahrgang'])."', 
                                                    Geschlecht = '".trim($row2['sex'])."', 
                                                    Land = '".trim($row2['nationality'])."', 
                                                    Geburtstag = '".trim($row2['birth_date'])."', 
                                                    xVerein = '".trim($club)."', 
                                                    xVerein2 = '".trim($club2)."', 
                                                    Lizenznummer = '".trim($license)."',
                                                    Bezahlt = '".trim($row2['license_paid'])."',
                                                    Manuell = 0   
                                             WHERE (Lizenznummer = '".trim($license)."' 
                                                    OR (Name = '".trim($row2['lastname'])."' 
                                                    AND Vorname = '".trim($row2['firstname'])."' 
                                                    AND Jahrgang = '".trim($row2['jahrgang'])."' 
                                                    AND xVerein = '".trim($club)."'));"; 
                                   
                                    $query3 = mysql_query($sql3);                                        
                                    $xAthlete = $row4['xAthlet'];              
                                }   
                            else {   
                                            
                                            // if club is valid
                                            // insert athlete from base data
                                            if(is_numeric($club)){
                                                $sql = "INSERT INTO athlet 
                                                            (Name, Vorname, Jahrgang, 
                                                            Lizenznummer, Geschlecht, Land, 
                                                            Geburtstag, xVerein, xVerein2, Bezahlt, Lizenztyp)
                                                        SELECT 
                                                            TRIM(lastname), TRIM(firstname), substring(birth_date, 1,4), 
                                                            license, TRIM(sex), nationality, 
                                                            birth_date, '$club', '$club2', license_paid, '1'
                                                        FROM
                                                            base_athlete
                                                        WHERE
                                                            license = $license";
                                                mysql_query($sql);
                                               
                                                if(mysql_errno() > 0){                                                      
                                                    AA_printErrorMsg("xml-6-".mysql_errno().": ".mysql_error());
                                                }else{
                                                    $xAthlete = mysql_insert_id();  
                                                }
                                             }                          
                                       
                                }    
                               
            
                                if($xAthlete > 0){
                                    // check if already registered
                                    $result = mysql_query("SELECT xAnmeldung FROM anmeldung WHERE xAthlet = $xAthlete AND xMeeting = ".$_COOKIE['meeting_id']."");
                                    if(mysql_errno() > 0){ 
                                        AA_printErrorMsg("xml-7-".mysql_errno().": ".mysql_error());
                                    }else{
                                       
                                            // get license category from base data
                                            $res = mysql_query("    
                                                        SELECT k.xKategorie FROM
                                                            kategorie as k
                                                            LEFT JOIN base_athlete as b on (k.Code = b.license_cat)
                                                        WHERE b.license = " .$license);
                                                        
                                            if(mysql_errno() > 0){ 
                                                AA_printErrorMsg("xml-8-".mysql_errno().": ".mysql_error());
                                            }else{
                                                $row = mysql_fetch_array($res);
                                                $xCat = $row[0];
                                                if($xCat!=''){
                                                    if(mysql_num_rows($result) == 0){ // not yet registered  
                                                        // insert                                                             
                                                        mysql_query("INSERT INTO anmeldung SET
                                                                Startnummer = 0
                                                                , Bezahlt = '" .$row2['license_paid']."'
                                                                , Gruppe = '" .$group ."'  
                                                                , xAthlet = $xAthlete
                                                                , xMeeting = ".$_COOKIE['meeting_id']."
                                                                , xKategorie = $xCat
                                                                , Anmeldenr_ZLV = $registerNr");   
                                                                
                                                        if(mysql_errno() > 0){ 
                                                            AA_printErrorMsg("xml-9-".mysql_errno().": ".mysql_error());
                                                        }else{
                                                            $xReg = mysql_insert_id();
                                                        }
                                                    }
                                                    else {   
                                                          // update 
                                                           $row = mysql_fetch_array($result);  
                                                             
                                                           mysql_query("Update anmeldung SET
                                                                Startnummer = 0
                                                                , Bezahlt = '" .$row2['license_paid']."'  
                                                                 , Gruppe = '" .$group ."'   
                                                                , xAthlet = $xAthlete
                                                                , xMeeting = ".$_COOKIE['meeting_id']."
                                                                , xKategorie = $xCat
                                                                , Anmeldenr_ZLV = $registerNr
                                                                WHERE xAnmeldung = $row[0]");
                                                         
                                                        if(mysql_errno() > 0){ 
                                                            AA_printErrorMsg("xml-10-".mysql_errno().": ".mysql_error());
                                                        }else{
                                                            $xReg = $row[0];
                                                        }
                                                          
                                                    }
                                                } else {
                                                        $result2 = mysql_query("SELECT license_cat 
                                                                        FROM base_athlete
                                                                        WHERE license = $license;");
                                                        $row2 = mysql_fetch_array($result2);
                                                        $license_cat = $row2[0];
                                                        AA_printErrorMsg(str_replace('%cat%', $license_cat, $strNoSuchCategory)); 
                                                }   
                                            } 
                   
                                            // only combined events for ZLV    
                                            // effort are points, saved on registration
                                            $sql = "SELECT notification_effort 
                                                        FROM base_performance
                                                        WHERE id_athlete = $athlete_id
                                                        AND    discipline = $discode";
                                          
                                            $res_effort = mysql_query($sql);
                                            if(mysql_errno() > 0){   
                                                    AA_printErrorMsg("xml-11-".mysql_errno().": ".mysql_error());
                                            }else{
                                                    if(mysql_num_rows($res_effort) > 0){
                                                        $row_effort = mysql_fetch_assoc($res_effort);
                                                        $effort = $row_effort['notification_effort'];
                                
                                                        mysql_query("UPDATE anmeldung SET
                                                                            BestleistungMK = '$effort'
                                                                            , xMeeting = ".$_COOKIE['meeting_id']."  
                                                                     WHERE
                                                                            xAnmeldung = $xReg");  
                                                                            
                                                         if(mysql_errno() > 0){ 
                                                            AA_printErrorMsg("xml-12-".mysql_errno().": ".mysql_error());
                                                         }
                                                    }
                                            }
                                       
                    
                                            if($xReg > 0){
                                                // check if athlete alredy starts for this discipline(s)
                                                foreach($xDis as $xDis1){
                                                    // because we can get multiple disciplines (combined event),
                                                    // it is nessesary to determinate distype and discode for each discipline
                                                    // (catcode won't change)
                                                    $res_distype = mysql_query("
                                                    SELECT d.Typ, d.Code, d.Appellzeit, d.Stellzeit FROM 
                                                            disziplin_" . $_COOKIE['language'] . " as d
                                                            LEFT JOIN wettkampf as w ON (w.xDisziplin = d.xDisziplin)
                                                    WHERE 
                                                        w.xWettkampf = $xDis1"); 
                                                        
                                                if(mysql_errno() > 0){   
                                                    AA_printErrorMsg("xml-13-".mysql_errno().": ".mysql_error());
                                                }else{
                                                    $row_distype = mysql_fetch_Array($res_distype);
                                                    $distype = $row_distype[0];
                                                    $temp_discode = $row_distype[1]; 
                                                    
                                                }
                            
                                                $result = mysql_query("SELECT xStart FROM start WHERE xAnmeldung = $xReg and xWettkampf = $xDis1");
                                               
                                                if(mysql_errno() > 0){ 
                                                    AA_printErrorMsg("xml-14-".mysql_errno().": ".mysql_error());
                                                }else{
                                                    if(mysql_num_rows($result) == 0){ // not yet starting, add start
                                    
                                                        $saison = $_SESSION['meeting_infos']['Saison'];
                                                        if ($saison == ''){
                                                            $saison = "O"; //if no saison is set take outdoor
                                                        }   
                                                      
                                                      $sql = "SELECT
                                                                        base_performance.notification_effort
                                                                    FROM
                                                                        athletica.base_performance
                                                                        INNER JOIN athletica.disziplin_" . $_COOKIE['language'] . " AS d 
                                                                        ON (base_performance.discipline = d.Code)
                                                                        INNER JOIN athletica.wettkampf 
                                                                        ON (d.xDisziplin = wettkampf.xDisziplin)
                                                                    WHERE (base_performance.id_athlete =$athlete_id
                                                                        AND wettkampf.xWettkampf =$xDis1
                                                                        AND wettkampf.xMeeting =".$_COOKIE['meeting_id']."
                                                                        AND base_performance.season ='I')";
                                       
                                                        $res_effort = mysql_query($sql);   
                                    
                                                        if(mysql_errno() > 0){                                                              
                                                            AA_printErrorMsg("xml-15-".mysql_errno().": ".mysql_error());
                                                        }else{
                                                            if(mysql_num_rows($res_effort) > 0){
                                                                $row_effort = mysql_fetch_assoc($res_effort);
                                                                $effort = $row_effort['notification_effort'];
                                                            }
                                                            //
                                                            // convert effort
                                                            //
                                                            if(($distype == $cfgDisciplineType[$strDiscTypeTrack])
                                                                || ($distype == $cfgDisciplineType[$strDiscTypeTrackNoWind])
                                                                || ($distype == $cfgDisciplineType[$strDiscTypeRelay])
                                                                || ($distype == $cfgDisciplineType[$strDiscTypeDistance]))
                                                                {
                                                                $pt = new PerformanceTime($effort);
                                                                $perf = $pt->getPerformance();
                                            
                                                            }
                                                            else {
                                                                //echo $bigger;
                                                                $pa = new PerformanceAttempt($effort);
                                                                $perf = $pa->getPerformance();
                                                                //$perf = (ltrim($effort,"0"))*100;
                                                            }
                                                            if($perf == NULL) {    // invalid performance
                                                                $perf = 0;
                                                            }
                                        
                                                            mysql_query("INSERT INTO start SET
                                                                            xWettkampf = $xDis1   
                                                                            , Bezahlt = '$license_paid'
                                                                            , xAnmeldung = $xReg   
                                                                            , Bestleistung = '".$perf."' 
                                                                            , BaseEffort = 'y'");
                                                            if(mysql_errno() > 0){ 
                                                                AA_printErrorMsg("xml-16-".mysql_errno().": ".mysql_error());
                                                            }
                                                        } 
                                                    }  
                                                }
                                                                           
                                                // update group in round for every event  
                                                $sql_r = "SELECT Gruppe FROM runde WHERE xWettkampf = ". $xDis1;   
                                                $res_r = mysql_query($sql_r); 
                                                if(mysql_errno() > 0){  
                                                    AA_printErrorMsg("xml-16a-".mysql_errno() . ": " . mysql_error());   
                                                } 
                                                else {
                                                    if (mysql_num_rows($res_r) > 0){ 
                                                        while ($row_r = mysql_fetch_row($res_r)) {
                                                            $arr_row[] = $row_r[0];
                                                        }
                                                        if ($arr_row[0] == "") {
                                                            //update group in round for every event
                                                            $sql_r = "UPDATE IGNORE runde SET Gruppe = '" .$group ."' WHERE xWettkampf = ". $xDis1;  
                                                            mysql_query($sql_r);
                                                            if(mysql_errno() > 0){  
                                                                AA_printErrorMsg("xml-17-".mysql_errno() . ": " . mysql_error());   
                                                            } 
                                                        }
                                                        elseif (!in_array($group, $arr_row)) {
                                                            
                                                                   $stdEtime = strtotime($row_distype[2]); // hold standard delay for enrolement time
                                                                   $stdMtime = strtotime($row_distype[3]); // and manipulation time
                                                                 
                                                                   list($hr, $min) = AA_formatEnteredTime($starttime);     
                                                             
                                                                   $tmp = strtotime($hr.":".$min.":00");
                                                                   $tmp = $tmp - $stdEtime;
                                                                   $appellTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);   
                                                             
                                                                   $tmp = strtotime($hr.":".$min.":00");
                                                                   $tmp = $tmp - $stdMtime;
                                                                   $putTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);     
                                                                 
                                                                   //insert group in round for every event
                                                                   $sql_r = "INSERT INTO runde SET
                                                                                Datum = '". $meetingDate. "', 
                                                                                Startzeit =  '".$starttime. "', 
                                                                                Appellzeit=  '".$appellTime. "', 
                                                                                Stellzeit=  '". $putTime. "',                                                                                 
                                                                                Gruppe = '" .$group ."', 
                                                                                xRundentyp = 8, 
                                                                                xWettkampf = " .$xDis1;     
                                                                
                                                                   mysql_query($sql_r);
                                                                   if(mysql_errno() > 0){ 
                                                                        AA_printErrorMsg("xml-18-".mysql_errno() . ": " . mysql_error());   
                                                                   } 
                                                        }
                                                    }
                                                    else {                                                                                                                                
                                                             $stdEtime = strtotime($row_distype[2]); // hold standard delay for enrolement time
                                                             $stdMtime = strtotime($row_distype[3]); // and manipulation time
                                                                 
                                                             list($hr, $min) = AA_formatEnteredTime($starttime);     
                                                             
                                                             $tmp = strtotime($hr.":".$min.":00");
                                                             $tmp = $tmp - $stdEtime;
                                                             $appellTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);    
                                                                 
                                                             $tmp = strtotime($hr.":".$min.":00");
                                                             $tmp = $tmp - $stdMtime;
                                                             $putTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);    
                                                                 
                                                             //insert group in round for every event
                                                              $sql_r = "INSERT INTO runde SET
                                                                                Datum = '". $meetingDate. "', 
                                                                                Startzeit =  '".$starttime. "', 
                                                                                Appellzeit=  '".$appellTime. "', 
                                                                                Stellzeit=  '". $putTime. "',                                                                                 
                                                                                Gruppe = '" .$group ."', 
                                                                                xRundentyp = 8, 
                                                                                xWettkampf = " .$xDis1;     
                                                              
                                                              mysql_query($sql_r);
                                                              if(mysql_errno() > 0){ 
                                                                    AA_printErrorMsg("xml-18I-".mysql_errno() . ": " . mysql_error());   
                                                                } 
                                                    }
                                                }   
                                                } // enf foreach
                       
                                            } // end xReg > 0
                                    }
                                } // end xAthlete > 0
                            
                    }
                    else {  // athlete with this license not in base
                             if (!in_array($license,$arr_noCat)){
                                $arr_noCat['lic'][] = $license;   
                             }   
                    }
                }
                       
            }
            else {    // category not in meeting
                    if (!in_array($cat,$arr_noCat)){
                          $arr_noCat['cat'][] = $cat;   
                    }   
            }
               
        }   
        else {     
            // license = 0   
            // select event to get all generated events ids
            
            $sql = "SELECT 
                        w.xWettkampf,
                        w.Mehrkampfcode,
                        k.xKategorie,
                        m.DatumVon   
                    FROM
                        wettkampf as w
                        LEFT JOIN kategorie as k on (w.xKategorie = k.xKategorie )
                        LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d on (w.xDisziplin = d.xDisziplin)
                        LEFT JOIN meeting AS m ON (m.xMeeting = w.xMeeting)  
                    WHERE 
                        w.xMeeting = ".$_COOKIE['meeting_id']."
                        AND k.Kurzname = '" .$cat ."'";
                   
            $res = mysql_query($sql);
            if (mysql_num_rows($res) >= 1) {
                while($row_dis = mysql_fetch_assoc($res)){
                        $xDis[] = $row_dis['xWettkampf'];
                        if  ($row_dis['Mehrkampfcode'] > 0) {
                            $discode =  $row_dis['Mehrkampfcode'];     // same for all combined disciplines  
                        }                       
                        $catnr =   $row_dis['xKategorie']; 
                        $meetingDate =  $row_dis['DatumVon'];    
                }  
                        
                $result2 = mysql_query("SELECT xVerein FROM verein WHERE xCode = '".$club."'");
                if (mysql_errno() > 0){
                    AA_printErrorMsg("xml-19-".mysql_errno() . ": " . mysql_error());
                }else{
                        $rowClub1 = mysql_fetch_array($result2);
                        $clubnr = $rowClub1[0];  
                }
                
                mysql_free_result($result2);  
              
                // if club is valid
                // insert athlete from base data   
                if(is_numeric($clubnr)) { 
                    
                    $name = mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($name))));   
                    $firstname = mysql_real_escape_string(str_replace("\r", "\n", trim(utf8_decode($firstname))));                           
                                   
                    // if athlet exist
                    $sql = "SELECT * FROM athlet WHERE Name= '" .  $name ."' AND Vorname = '" .  $firstname ."' AND Geburtstag = '" . $birthdate ."'";
                    $res = mysql_query($sql);  
                   
                    if(mysql_errno() > 0){  
                        AA_printErrorMsg("xml-20-".mysql_errno().": ".mysql_error());
                    }else{     
                         $row = mysql_fetch_array($res);   
                         if (mysql_num_rows($res) == 0) {                              
                           
                            $sql = "INSERT IGNORE INTO athlet SET
                                                                Name = '" .  $name ."',
                                                                Vorname = '" .  $firstname ."',  
                                                                Jahrgang = '" .  $birthyear ."', 
                                                                Lizenznummer = 0 , 
                                                                Geschlecht = '" .  $sex ."', 
                                                                Land = '" .  $nationality ."', 
                                                                Geburtstag = '" .  $birthdate ."',
                                                                Athleticagen = 'n',   
                                                                xVerein = '" .  $clubnr ."',  
                                                                Bezahlt = '" .  $license_paid ."', 
                                                                Lizenztyp = 3";  
                                                           
                            mysql_query($sql);
                           
                            if(mysql_errno() > 0){    
                                AA_printErrorMsg("xml-21-".mysql_errno().": ".mysql_error());
                            }else{
                                    $xAthlete = mysql_insert_id();   
                            }
                        }
                        else {
                               $sql = "UPDATE athlet SET                                                                 
                                                    Jahrgang = '" .  $birthyear ."', 
                                                    Geschlecht = '" .  $sex ."', 
                                                    Land = '" .  $nationality ."',  
                                                    xVerein = '" .  $clubnr ."',  
                                                    Bezahlt = '" .  $license_paid ."'
                                                    WHERE Name= '" .  $name ."' AND Vorname = '" .  $firstname ."' AND Geburtstag = '" . $birthdate ."'";    
                              
                                mysql_query($sql);
                                if(mysql_errno() > 0){  
                                    AA_printErrorMsg("xml-22-".mysql_errno().": ".mysql_error());
                                }else{
                                        $xAthlete = $row[0];   
                                }
                        }    
                    }
                }
                else {
                        // club not found
                        if (!in_array($license,$arr_noCat)){
                            $arr_noCat['club'][] = $club;   
                        }   
                } 
                                                                      
            } // end athlete found  
           
            if($xAthlete > 0){
                // check if already registered
                $result = mysql_query("SELECT xAnmeldung FROM anmeldung WHERE xAthlet = $xAthlete AND xMeeting = ".$_COOKIE['meeting_id']."");
                if(mysql_errno() > 0){
                    AA_printErrorMsg("xml-23-".mysql_errno().": ".mysql_error());
                }else{
                        if(mysql_num_rows($result) == 0){ // not yet registered  
                            if($catnr!=''){
                                mysql_query("INSERT INTO anmeldung SET
                                                        Startnummer = 0
                                                        , Bezahlt = '$license_paid'
                                                        , xAthlet = $xAthlete
                                                        , xMeeting = ".$_COOKIE['meeting_id']."
                                                        , xKategorie = $catnr
                                                        , Anmeldenr_ZLV = $registerNr");
                               
                                if(mysql_errno() > 0){ 
                                    AA_printErrorMsg("xml-24-".mysql_errno().": ".mysql_error());
                                }else{
                                        $xReg = mysql_insert_id();
                                }
                                                
                            } 
                            }else{ // registered
                                    $row = mysql_fetch_array($result);
                                    mysql_query("Update anmeldung SET
                                                                Startnummer = 0
                                                                , Bezahlt = '$license_paid'
                                                                , xAthlet = $xAthlete
                                                                , xMeeting = ".$_COOKIE['meeting_id']."
                                                                , xKategorie = $xCat
                                                                , Anmeldenr_ZLV = $registerNr
                                                                WHERE xAnmeldung = $row[0]");
                                    if(mysql_errno() > 0){ 
                                        AA_printErrorMsg("xml-25-".mysql_errno().": ".mysql_error());
                                    }else{
                                            $xReg = $row[0];
                                    }   
                            }  
                    
                        if($xReg > 0){
                            // check if athlete alredy starts for this discipline(s)
                            foreach($xDis as $xDis1){
                                    // because we can get multiple disciplines (combined event),
                                    // it is nessesary to determinate distype and discode for each discipline
                                    // (catcode won't change)  
                                    $res_distype = mysql_query("
                                                    SELECT d.Typ, d.Code, d.Appellzeit, d.Stellzeit FROM 
                                                            disziplin_" . $_COOKIE['language'] . " as d
                                                            LEFT JOIN wettkampf as w  ON (w.xDisziplin = d.xDisziplin)
                                                    WHERE w.xWettkampf = $xDis1");
                                    if(mysql_errno() > 0){
                                                    AA_printErrorMsg("xml-26-".mysql_errno().": ".mysql_error());
                                    }else{
                                            $row_distype = mysql_fetch_Array($res_distype);
                                            $distype = $row_distype[0];
                                            $temp_discode = $row_distype[1];  
                                    }
                            
                                    $result = mysql_query("SELECT xStart FROM start WHERE xAnmeldung = $xReg and xWettkampf = $xDis1");
                                    if(mysql_errno() > 0){
                                                    AA_printErrorMsg("xml-27-".mysql_errno().": ".mysql_error());
                                    }else{
                                            if(mysql_num_rows($result) == 0){ // not yet starting, add start     
                                                $saison = $_SESSION['meeting_infos']['Saison'];
                                                if ($saison == ''){
                                                    $saison = "O"; //if no saison is set take outdoor
                                                } 
                                                
                                                mysql_query("INSERT INTO start SET
                                                                            xWettkampf = $xDis1
                                                                            , Bezahlt = '$license_paid'
                                                                            , xAnmeldung = $xReg
                                                                            ");
                                                if(mysql_errno() > 0){
                                                                AA_printErrorMsg("xml-28-".mysql_errno().": ".mysql_error());
                                                }
                                            } 
                                    }                             
                            
                            // update group in round for every event  
                            $sql_r = "SELECT Gruppe FROM runde WHERE xWettkampf = ". $xDis1;   
                            $res_r = mysql_query($sql_r); 
                            if(mysql_errno() > 0){
                                    AA_printErrorMsg("xml-28a-".mysql_errno() . ": " . mysql_error());   
                            } 
                            else {
                                if (mysql_num_rows($res_r) > 0){ 
                                    while ($row_r = mysql_fetch_row($res_r)) {
                                            $arr_row[] = $row_r[0];
                                    }
                                    if ($arr_row[0] == "") {
                                        //update group in round for every event
                                        $sql_r = "UPDATE IGNORE runde SET Gruppe = " .$group ." WHERE xWettkampf = ". $xDis1;  
                                        mysql_query($sql_r);
                                        if(mysql_errno() > 0){
                                                AA_printErrorMsg("xml-29-".mysql_errno() . ": " . mysql_error());   
                                        } 
                                    }
                                    elseif (!in_array($group, $arr_row)) {
                                           $stdEtime = strtotime($row_distype[2]); // hold standard delay for enrolement time
                                                                   $stdMtime = strtotime($row_distype[3]); // and manipulation time
                                                                 
                                                                   list($hr, $min) = AA_formatEnteredTime($starttime);     
                                                             
                                                                   $tmp = strtotime($hr.":".$min.":00");
                                                                   $tmp = $tmp - $stdEtime;
                                                                   $appellTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);   
                                                             
                                                                   $tmp = strtotime($hr.":".$min.":00");
                                                                   $tmp = $tmp - $stdMtime;
                                                                   $putTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);     
                                                                 
                                                                   //insert group in round for every event
                                                                    $sql_r = "INSERT INTO runde SET
                                                                                Datum = '". $meetingDate. "', 
                                                                                Startzeit =  '".$starttime. "', 
                                                                                Appellzeit=  '".$appellTime. "', 
                                                                                Stellzeit=  '". $putTime. "',                                                                                 
                                                                                Gruppe = '" .$group ."', 
                                                                                xRundentyp = 8, 
                                                                                xWettkampf = " .$xDis1;     
                                                                
                                                                   mysql_query($sql_r);
                                        if(mysql_errno() > 0){
                                                AA_printErrorMsg("xml-30-".mysql_errno() . ": " . mysql_error());   
                                        } 
                                    }
                                }
                                 else {                                                                                                                                
                                                             $stdEtime = strtotime($row_distype[2]); // hold standard delay for enrolement time
                                                             $stdMtime = strtotime($row_distype[3]); // and manipulation time
                                                                 
                                                             list($hr, $min) = AA_formatEnteredTime($starttime);     
                                                             
                                                             $tmp = strtotime($hr.":".$min.":00");
                                                             $tmp = $tmp - $stdEtime;
                                                             $appellTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);    
                                                                 
                                                             $tmp = strtotime($hr.":".$min.":00");
                                                             $tmp = $tmp - $stdMtime;
                                                             $putTime = floor($tmp / 3600).":".floor(($tmp % 3600) / 60);    
                                                                 
                                                             //insert group in round for every event
                                                             $sql_r = "INSERT INTO runde SET
                                                                                Datum = '". $meetingDate. "', 
                                                                                Startzeit =  '".$starttime. "', 
                                                                                Appellzeit=  '".$appellTime. "', 
                                                                                Stellzeit=  '". $putTime. "',                                                                                 
                                                                                Gruppe = '" .$group ."', 
                                                                                xRundentyp = 8, 
                                                                                xWettkampf = " .$xDis1;     
                                                              
                                                              mysql_query($sql_r);
                                                              if(mysql_errno() > 0){ 
                                                                    AA_printErrorMsg("xml-18I-".mysql_errno() . ": " . mysql_error());   
                                                                } 
                                 }
                                }     // end foreach      
                            }   
                        } // end xReg > 0   
                }   
            } // end xAthlete > 0
        }  
     }  // end ATHLETE
     
      if($name == "ANMELDUNG"){ 
       
        $regNr = $attr['ANMELDENR'];
        $team = $attr['MANNSCHAFT']; 
        $cat = $attr['KATEGORIE'];  
        $license = $attr['LIZENZNR'];  
        $group = $attr['GRUPPENR']; 
        $club = $attr['VEREIN']; 
        $registerNr = $attr['ANMELDENR']; 
        
        $sql = "SELECT id_athlete FROM base_athlete WHERE license = '".$license."';";
        $query = mysql_query($sql);
        if(mysql_errno() > 0) {   
            AA_printErrorMsg("xml-31-".mysql_errno() . ": " . mysql_error());   
        }                        
        else {  
            if (mysql_num_rows($query) > 0 || $license == '') {      // athlete not in base     
         
                if (!in_array($cat,$arr_noCat)) {
                    // get the eventnumber of this meeting for generating a team id in the form eventnumber999 (xxxxxx999)
                    $res = mysql_query("SELECT xControl FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
                    if(mysql_errno() > 0) { 
                        AA_printErrorMsg("xml-32-".mysql_errno() . ": " . mysql_error());    
                    }else{
                        $row = mysql_fetch_array($res);
                        $eventnr = $row[0];
                        if(empty($eventnr)){
                            $idcounter = "";
                        }else{
                            mysql_free_result($res);
                            $arrid = array();
                            $res = mysql_query("select max(xStaffel) from staffel where xStaffel like '$eventnr%'");
                            $row = mysql_fetch_array($res);
                            $arrid[] = $row[0];
                            $res = mysql_query("select max(xTeam) from team where xTeam like '$eventnr%'");
                            $row = mysql_fetch_array($res);
                            $arrid[] = $row[0];
                            $res = mysql_query("select max(id_relay) from base_relay where id_relay like '$eventnr%'");
                            $row = mysql_fetch_array($res);
                            $arrid[] = $row[0];
                            $res = mysql_query("select max(id_svm) from base_svm where id_svm like '$eventnr%'");
                            $row = mysql_fetch_array($res);
                            $arrid[] = $row[0];
                
                            rsort($arrid);
                            $biggestId = $arrid[0];  
                
                            if($biggestId == 0 || strlen($biggestId) != 9){
                                $idcounter = "001";
                            }else{
                                $idcounter = substr($biggestId,6,3);
                                $idcounter++;
                                $idcounter = sprintf("%03d", $idcounter);
                            }
                
                            $xTeamSQL = ", xTeam = ".$eventnr.$idcounter.", Athleticagen ='y' ";
                
                        }
                    }
                    $sql = "SELECT xKategorie FROM kategorie WHERE Kurzname = '" .$cat."'";
                    $res = mysql_query($sql);
                    if(mysql_errno() > 0) {  
                        AA_printErrorMsg("xml-33-".mysql_errno() . ": " . mysql_error());   
                    }
                    else {
                        $row = mysql_fetch_row($res);
                        $catnr = $row[0];
            
                        $sql = "SELECT xVerein FROM verein WHERE xCode = '" .$club."'";
                        $res = mysql_query($sql);
                        if(mysql_errno() > 0) {  
                            AA_printErrorMsg("xml-34-".mysql_errno() . ": " . mysql_error());     
                        }
                        else {  
                            $row = mysql_fetch_row($res);
                            $clubnr = $row[0];   
          
                            if ($license > 0) {    
                                $sql_a = "SELECT 
                                        an.xAnmeldung
                                  FROM
                                        athlet AS a
                                        LEFT JOIN anmeldung AS an ON (a.xAthlet = an.xAthlet)
                                  WHERE
                                        a.Lizenznummer = " .$license ."
                                        AND an.xMeeting = ".$_COOKIE['meeting_id'];
                            }
                            else {
                                $sql_a = "SELECT 
                                        an.xAnmeldung
                                  FROM
                                        athlet AS a
                                        LEFT JOIN anmeldung AS an ON (a.xAthlet = an.xAthlet)
                                  WHERE
                                        an.Anmeldenr_ZLV = " .$registerNr ."
                                        AND an.xMeeting = ".$_COOKIE['meeting_id'];  
                            }
                            $result = mysql_query($sql_a);
                            if(mysql_errno() > 0) {    
                                AA_printErrorMsg("xml-35-".mysql_errno() . ": " . mysql_error()); 
                            }
                            else {  
                                if (mysql_num_rows($result) >= 1){                
                                    $row_a = mysql_fetch_row($result);  
             
                                    $sql = "SELECT xTeam FROM team WHERE Name = '" .$team ."' AND xMeeting=" . $_COOKIE['meeting_id'] . " AND xKategorie = " .$catnr ." AND xVerein=" . $clubnr; 
                                   
                                    $res = mysql_query($sql);
                                    if (mysql_num_rows($res) > 0) {
                                        $row = mysql_fetch_array($res);
                                        $xTeam = $row[0];      
                                    }
                                    else {
                                        if ($clubnr > 0){                         // no club exist --> error msg in ATHLETE
                                            $sql = "INSERT IGNORE INTO team SET 
                                                        Name=\"". $team ."\"
                                                        , xMeeting=" . $_COOKIE['meeting_id'] ."
                                                        , xKategorie = " .$catnr ."
                                                        , xVerein=" . $clubnr 
                                                        .$xTeamSQL;
                    
                                            mysql_query($sql);
                                            if(mysql_errno() > 0) {   
                                                AA_printErrorMsg("xml-36-".mysql_errno() . ": " . mysql_error());  
                                            }
                                            else {
                                                $xTeam = mysql_insert_id();    // get new ID      
                                            }  
                                        } 
                                    } 
                                                                         
                                    $sql = "UPDATE anmeldung SET xTeam = '".$xTeam."', Gruppe = '".$group ."' WHERE xAnmeldung = " .$row_a[0] ." AND xMeeting = " .$_COOKIE['meeting_id'];
                                  
                                    $res = mysql_query($sql);
                                    if(mysql_errno() > 0) { 
                                            AA_printErrorMsg("xml-37-".mysql_errno() . ": " . mysql_error());   
                                    }   
                               }  
                          } 
                        }
                    }
                } 
            }
        }
     }     // end ANMELDUNG
  
} 

function XML_regZLV_end($parser, $name){  
}

function XML_regZLV_data($parser, $data){  
}       
         
