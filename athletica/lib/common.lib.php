<?php
error_reporting(0);
ini_set('max_execution_time', 3600);

/**
 * common functions
 * ----------------
 * This library contains common functions.
 * Attention: use utils.lib.php for functions that return errors as a global
 * field.
 */

if(!session_id())
{
    session_start('athletica');
}

if (!defined('AA_COMMON_LIB_INCLUDED'))
{
    define('AA_COMMON_LIB_INCLUDED', 1);


require('./lib/utils.lib.php');
require('./config.inc.php');

   
   
/**
 *    Languages
 *        Define list of available languages.
 *
 *    cfgLanguage:
 *        - file: include translation table
 *        - doc: URL to application documentation
 */

    $cfgLanguage = array("Deutsch"
                                => array    ("file" => "./lang/german.inc.php"
                                            , "doc" => "doc/de/"
                                            , "short" => "de"
                                            )
                            , "English"
                                => array    ("file" => "./lang/english.inc.php"
                                            , "doc" => "doc/de/"
                                            , "short" => "en"
                                            )
                            , "Français"
                                => array     ("file" => "./lang/french.inc.php"
                                            , "doc" => "doc/fr/"
                                            , "short" => "fr"
                                            )
                            , "Italiano"
                                => array     ("file" => "./lang/italian.inc.php"
                                            , "doc" => "doc/it/"
                                            , "short" => "it"
                                            )
                    );

    if(empty($_COOKIE['language_trans'])) {
        setcookie("language_trans", $cfgLanguage['Deutsch']['file']
            , time()+$cfgCookieExpires);
        $_COOKIE['language_trans'] = $cfgLanguage['Deutsch']['file'];
    }
    if(empty($_COOKIE['language_doc'])) {
        setcookie("language_doc", $cfgLanguage['Deutsch']['doc']
            , time()+$cfgCookieExpires);
        $_COOKIE['language_doc'] = $cfgLanguage['Deutsch']['doc'];
    }
    if(empty($_COOKIE['language'])) {
        setcookie("language", $cfgLanguage['Deutsch']['short']
            , time()+$cfgCookieExpires);
        $_COOKIE['language'] = $cfgLanguage['Deutsch']['short'];
    }

/*
 * ------------------------------------------------------
 *
 *    Processing Functions
 *    --------------------
 *    various processing functions
 *
 * ------------------------------------------------------
 */


    /**
     * Establish DB connection
     *
     * @return        DB link
     */
    function AA_connectToDB()
    {
        $db = AA_utils_connectToDB();
        if(!empty($GLOBALS['AA_ERROR'])) {
            AA_printErrorPage($GLOBALS['AA_ERROR']);
        }
        return $db;
    }


    /**
     * Check if meeting selected
     *
     * @return TRUE if OK, FALSE if not OK
     *
     */
    function AA_checkMeetingID()
    {
        global $noMeetingCheck;
        
        $ret = TRUE;
        
        if(!$noMeetingCheck){
            if(empty($_COOKIE['meeting_id']))
            {
                AA_printErrorPage($GLOBALS['strNoMeetingSelected']);
                $ret = FALSE;
            }
        }
        

        if(isset($_COOKIE['meeting_id'])){
            if(isset($_SESSION['meeting_infos'])){
                unset($_SESSION['meeting_infos']);
            }
                      
            $sql_m= "SELECT * 
                      FROM meeting";
            $query_m = mysql_query($sql_m);   
            
            $sql = "SELECT * 
                      FROM meeting 
                     WHERE xMeeting = ".$_COOKIE['meeting_id'].";";
            $query = mysql_query($sql); 
            
            if($query && mysql_num_rows($query)==1){ 
                $row = mysql_fetch_assoc($query);
                $_SESSION['meeting_infos'] = $row; 
            }
            else {
                 if($query_m && mysql_num_rows($query_m)>0){
                    $_SESSION['meeting_infos'] = "meetingNotChosen";       // meeting exist but is not chosen 
                 }
                 else
                    if  ($query_m){
                        $_SESSION['meeting_infos'] = "noMeeting";      // no data in table meeting   
                    } 
            }
        }
        return $ret;
    }
    
    /**
    
    check if xControl of the meeting is set
    
    @return 1 if xControl is given and result upload is wished
        2 if no result upload will be made
        0 if xControl is 0 but the result upload is activated
    
    */
    function AA_checkControl(){
        
        if(AA_checkMeetingID()){
            
            $res = mysql_query("SELECT xControl, Online FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
            if(mysql_errno() > 0){
                AA_printErrorPage(mysql_errno().": ".mysql_error());
            }else{
                $row = mysql_fetch_array($res);
                if($row[0] > 0 && $row[1] == 'y'){
                    return 1;
                }elseif($row[0] == 0 && $row[1] == 'y'){
                    return 0;
                }elseif($row[1] == 'n'){
                    return 2;
                }
            }
            
        }
        
    }
    
    /**
    
    check if xControl of the meeting is set
    
    @return 1 if xControl is given and result upload is wished
        2 if no result upload will be made
        0 if xControl is 0 but the result upload is activated
    
    */
    function AA_checkControl_UKC($meeting_nr = ''){
        
        if(AA_checkMeetingID()){
            
            if (empty($meeting_nr)){
                $res = mysql_query("SELECT Nummer FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
                if(mysql_errno() > 0){
                    AA_printErrorPage(mysql_errno().": ".mysql_error());
                }else{
                    $row = mysql_fetch_array($res);
                    if($row[0] > 0){
                        return 1;
                    }
                    else {
                        return 0;
                    }
                }
            }
            else {
                 return 1; 
            }
        }
            
    }
        
   

/*
 * ------------------------------------------------------
 *
 *    Data functions
 *    --------------------
 *    various functions to retrieve or check data from DB
 *
 * ------------------------------------------------------
 */


    /**
     * Get first category ID from DB
     *
     * @return    Category ID (primary key)
     */
    function AA_getFirstCategoryID()
    {
        $result = mysql_query("
            SELECT
                DISTINCT w.xKategorie
            FROM
                wettkampf AS w
                LEFT JOIN kategorie AS k ON (w.xKategorie = k.xKategorie)
            WHERE
                w.xMeeting=" . $_COOKIE['meeting_id'] . "                 
            ORDER BY
                k.Anzeige
        ");

        if(mysql_errno() > 0) {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            $category = 0;        // set category to zero
        }
        else {
            $row = mysql_fetch_row($result);
            $category = $row[0];            // preselect default category
        }
        mysql_free_result($result);

        return $category;
    }
    
    
    /**
     * Get width of a string in the current font (Arial/Helvetica)
     *
     * @param    string        any text
     * @param    int        font size
     * @return    int        width of given text
     *
    **/
    function AA_getStringWidth($s, $size)
    {
        global $cfgCharWidth;
        $s=(string)$s;
        $cw=&$cfgCharWidth;
        $w=0;
        $l=strlen($s);
        for($i=0;$i<$l;$i++)
            $w+=$cw[$s{$i}];
        return $w*$size/1000;
    }
    
    
    /**
     * Check if event or round belongs to a combined contest
     *
     * @param    int        event
     * @param    int        round
     * @return    TRUE/FALSE
    **/
    function AA_checkCombined($event=0, $round=0){
        global $cfgEventType, $strEventTypeSingleCombined;
        
        if($event > 0){
            
            $res = mysql_query("SELECT Typ FROM
                        wettkampf
                    WHERE    xWettkampf = $event
                    AND    xMeeting = ".$_COOKIE['meeting_id']);
            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){
                    return true;
                }else{
                    return false;
                }
                
            }
            
        }elseif($round > 0){    
            
            $sql ="SELECT 
                        Typ 
                   FROM
                        wettkampf as w
                        LEFT JOIN runde as r ON (r.xWettkampf = w.xWettkampf)
                   WHERE    
                        r.xRunde = " .$round ."                    
                        AND w.xMeeting = ".$_COOKIE['meeting_id'];    
            
            $res = mysql_query($sql);

            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] == $cfgEventType[$strEventTypeSingleCombined]){
                    return true;
                }else{
                    return false;
                }
                
            }
            
        }
        
    }
    
    
    /**
     * Check if event or round belongs to a svm contest
     *
     * @param    int        event
     * @param    int        round
     * @return    TRUE/FALSE
    **/
    function AA_checkSVM($event=0, $round=0){
        global $cfgEventType, $strEventTypeSingleCombined, $strEventTypeTeamSM;
        
        if($event > 0){
            
            $res = mysql_query("SELECT Typ FROM
                        wettkampf
                    WHERE    xWettkampf = $event
                    AND    xMeeting = ".$_COOKIE['meeting_id']);
            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] > $cfgEventType[$strEventTypeSingleCombined]
                    && $row[0] != $cfgEventType[$strEventTypeTeamSM]){
                    return true;
                }else{
                    return false;
                }
                
            }
            
        }elseif($round > 0){   
            
            $sql = "SELECT 
                        Typ 
                    FROM
                        wettkampf as w
                        LEFT JOIN runde as r ON (r.xWettkampf = w.xWettkampf)
                    WHERE    
                        r.xRunde = $round  
                        AND w.xMeeting = ".$_COOKIE['meeting_id'];     
             
            $res = mysql_query($sql);

            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] > $cfgEventType[$strEventTypeSingleCombined]
                    && $row[0] != $cfgEventType[$strEventTypeTeamSM]){
                    return true;
                }else{
                    return false;
                }
                
            }
            
        }
        
    }
    
    /**
     * Check if event belongs to a svm contest
     *  and check if only Nat. A - C
     *
     * @param    int        event       
     * @return    TRUE/FALSE
    **/
    function AA_checkSVMNatAC($event=0){
        global $cfgEventType, $strEventTypeSVMNL;
        
        if($event > 0){
            
            $res = mysql_query("SELECT 
                                    Typ,
                                    xKategorie_svm 
                                FROM
                                    wettkampf
                                WHERE 
                                    xWettkampf = $event
                                    AND xMeeting = ".$_COOKIE['meeting_id']);
                                    
            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] == $cfgEventType[$strEventTypeSVMNL]){
                     $sql="SELECT 
                                Code 
                           FROM 
                                kategorie_svm 
                           WHERE 
                                xKategorie_svm = " . $row[1];
                    
                     $res = mysql_query($sql);
                     if(mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                     }else{
                          $row = mysql_fetch_array($res);
                          $code = explode('_',$row[0]);
                          if ($code[0] < 32) {            // only Nat. A, B and C
                            return true;
                          }
                          else {
                              return false;  
                          }
                     }
                }else{
                    return false;
                }
                
            }
            
        }
        else {
             return false;  
        }
    }
    
    /**
     * Check if event or round belongs to a team sm contest
     *
     * @param    int        event
     * @param    int        round
     * @return    TRUE/FALSE
    **/
    function AA_checkTeamSM($event=0, $round=0){
        global $cfgEventType, $strEventTypeTeamSM;
        
        if($event > 0){
            
            $res = mysql_query("SELECT Typ FROM
                        wettkampf
                    WHERE    xWettkampf = $event
                    AND    xMeeting = ".$_COOKIE['meeting_id']);
            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] == $cfgEventType[$strEventTypeTeamSM]){
                    return true;
                }else{
                    return false;
                }
                
            }
            
        }elseif($round > 0){     
            
            $sql = "SELECT 
                        Typ 
                    FROM
                        wettkampf as w
                        LEFT JOIN runde as r ON (r.xWettkampf = w.xWettkampf)
                    WHERE    
                        r.xRunde = $round                     
                        AND w.xMeeting = ".$_COOKIE['meeting_id'];  
            
            $res = mysql_query($sql);

            if(mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }else{
                
                $row = mysql_fetch_array($res);
                if($row[0] == $cfgEventType[$strEventTypeTeamSM]){
                    return true;
                }else{
                    return false;
                }
                
            }
            
        }
        else {
               $sql = "SELECT         
                        w.Typ
                    FROM
                        wettkampf AS w
                    GROUP BY w.Typ";
  
               $results = mysql_query($sql);
               if(mysql_errno() > 0)        // DB error
                    {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
               }
               if (mysql_num_rows($results) == 1) {       
                      $row = mysql_fetch_row($results);
                      if ($row[0] == $cfgEventType[$strEventTypeTeamSM]) {
                            return true;
                      } 
               }
               return false;    
        }
        
    }
    
    
    /**
     * Check athlete's age
     *
     * @param    int            category            uniqe key of category
     * @param    int            year                birth year
     * @return    TRUE/FALSE
     */
    function AA_checkAge($category, $year)
    {
        $limit = FALSE;
        if($category > 0)
        {
            $res = mysql_query("SELECT kategorie.Alterslimite"
                                . " FROM kategorie"
                                . " WHERE kategorie.xKategorie = " . $category);

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                $row = mysql_fetch_row($res);
                $agelimit = $row[0];
                mysql_free_result($res);

                $res = mysql_query("SELECT DATE_FORMAT(meeting.DatumVon, '%Y')"
                            . " FROM meeting"
                            . " WHERE meeting.xMeeting = " . $_COOKIE['meeting_id']);

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {
                    $row = mysql_fetch_row($res);

                    if(($row[0] - $year) <= $agelimit) {
                        $limit = TRUE;
                    }
                    mysql_free_result($res);
                }    // ET DB error meeting date
            }    // ET DB error category
        }    // ET categore provided
        return $limit;
    }


    /**
     * Check data reference
     *
     * @param        string    table        name of table to be checked
     * @param        string    unique    name of unique key column
     * @param        int            id            uniqe key of item to be checked (xTablename)
     * @return    int            rows        nbr of rows found
     */
    function AA_checkReference($table, $unique, $id)
    {    
        $rows = AA_utils_checkReference($table, $unique, $id);
        if(!empty($GLOBALS['AA_ERROR'])) {
            AA_printErrorMsg($GLOBALS['AA_ERROR']);
        }
        return $rows;
    }


    /**
     * Check if relay event
     *
     * @param    int            id            uniqe key of event
     * @return    TRUE/FALSE
     */
    function AA_checkRelay($event=0, $round=0)
    {
        $relay = FALSE;
        if($event > 0)
        {                      
            $sql ="SELECT 
                        d.Staffellaeufer
                   FROM 
                        disziplin_" . $_COOKIE['language'] . " AS d 
                        LEFT JOIN wettkampf as w ON (w.xDisziplin = d.xDisziplin)
                   WHERE 
                        w.xWettkampf = " . $event;     
            
            $result = mysql_query($sql);      

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                $row = mysql_fetch_row($result);
                if($row[0] > 0) {
                    $relay = TRUE;
                }
                mysql_free_result($result);
            }
        }
        elseif($round > 0)
        {                        
             $sql = "SELECT
                            d.Staffellaeufer
                     FROM 
                            disziplin_" . $_COOKIE['language'] . " AS d   
                            , wettkampf
                            , runde
                     WHERE 
                            wettkampf.xWettkampf = runde.xWettkampf
                            AND runde.xRunde = " . $round ." 
                            AND wettkampf.xDisziplin = d.xDisziplin";     
          
            $result = mysql_query($sql);

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                $row = mysql_fetch_row($result);
                if($row[0] > 0) {
                    $relay = TRUE;
                }
                mysql_free_result($result);
            }
        }
        return $relay;
    }


    /**
     * get discipline type
     * -------------------
    */
    function AA_getDisciplineType($round)
    {
        require('./config.inc.php');

        $status = 0;
        
        $sql = "SELECT
                        d.Typ
                        , w.Windmessung
                FROM
                        runde AS r
                        LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
                        LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON (d.xDisziplin = w.xDisziplin)
                WHERE 
                        r.xRunde = " . $round;     
         
         $result = mysql_query($sql);    

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            $type = '';
        }
        else
        {
            $row = mysql_fetch_row($result);

            // check whether standard discipline type has been overruled by
            // meeting definitions (wind or no wind)
            if(($row[0] == $cfgDisciplineType[$strDiscTypeTrack])
                && ($row[1] == 0))    // overruled: no wind
            {
                $type = $cfgDisciplineType[$strDiscTypeTrackNoWind];
            }
            else if(($row[0] == $cfgDisciplineType[$strDiscTypeJump])
                && ($row[1] == 0))    // overruled: no wind
            {
                $type = $cfgDisciplineType[$strDiscTypeJumpNoWind];
            }
            else
            {
                $type = $row[0];
            }
            mysql_free_result($result);
        }        // ET DB error
        return $type;
    }



    /**
     * Get last used startnumber
     *
     * @return    int startnumber
     */
    function AA_getLastStartnbr()
    {
        $n = 0;        // default
        $result = mysql_query("
            SELECT
                MAX(Startnummer)
            FROM
                anmeldung
            WHERE xMeeting = " . $_COOKIE['meeting_id']
        );
                                    
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            if(mysql_num_rows($result) > 0)
            {
                $row = mysql_fetch_row($result);
                if($row[0] != null) {
                    $n = $row[0];
                }
            }
            mysql_free_result($result);
        }
        return $n;
    }
    
    
    /**
     * Get last used startnumber for a relay
     *
     * @return    int startnumber
     */
    function AA_getLastStartnbrRelay()
    {
        $nbr = 0;
        
        $result = mysql_query("
                SELECT
                    MAX(Startnummer)
                FROM
                    staffel
                WHERE
                    xMeeting = ". $_COOKIE['meeting_id']);
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
             if (mysql_num_rows($result) > 0) {
                $row = mysql_fetch_array($result);
                if (!empty($row[0])){
                     $nbr = $row[0];  
                }                
             }
            
        }
        
        return $nbr;
    }
    
    
    /**
     * Get next free start number from pool
     *
     * @param    int    entered start number
     * @return    int    free startnumber
     */
    function AA_getNextStartnbr($n){
        
        $pool = array();
        
        $res = mysql_query("SELECT Startnummer FROM
                    staffel
                WHERE    xMeeting = ".$_COOKIE['meeting_id']."
                AND    Startnummer >= $n
                ");
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
            
            while($row = mysql_fetch_array($res)){
                $pool[] = $row[0];
            }
            
        }
        
        $res = mysql_query("SELECT Startnummer FROM
                    anmeldung
                WHERE    xMeeting = ".$_COOKIE['meeting_id']."
                AND    Startnummer >= $n
                ");
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
            
            while($row = mysql_fetch_array($res)){
                $pool[] = $row[0];
            }
            
        }
        
        $res = mysql_query("SELECT Startnummer FROM
                    teamsm
                WHERE    xMeeting = ".$_COOKIE['meeting_id']."
                AND    Startnummer >= $n
                ");
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
            
            while($row = mysql_fetch_array($res)){
                $pool[] = $row[0];
            }
            
        }
        
        sort($pool);
        
        if(count($pool) > 0){
            
            // search next available number
            $lp = $pool[0];
            foreach($pool as $p){ 
                if(($p-$lp) > 1){
                    return ($lp+1);
                }
                $lp = $p;
            }
            
            return ($lp+1);
        }
        
        return 0;
    }
    
    /**
     * Get last used startnumber for teamsm
     *
     * @return    int startnumber
     */
    function AA_getLastStartnbrTeamsm()
    {
        $nbr = 0;
        
        $result = mysql_query("
                SELECT
                    MAX(Startnummer)
                FROM
                    teamsm
                WHERE
                    xMeeting = ". $_COOKIE['meeting_id']);
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
            
            $row = mysql_fetch_array($result);
            $nbr = $row[0];
            
        }
        
        return $nbr;
    }
    
    /**
     * get round status
     * ----------------
    */
    function AA_getRoundStatus($round)
    {
        $status = AA_utils_getRoundStatus($round);
        if(!empty($GLOBALS['AA_ERROR'])) {
            AA_printErrorMsg($GLOBALS['AA_ERROR']);
        }
        return $status;
    }


    /**
     * get next round
     * --------------
     *
     * - event: event to be processed
     * - round: current round
     *
     * returns: next round's primary key, or zero
    */
    function AA_getNextRound($event, $round)
    {
        $nextRound = 0;        // initialize return value

        if((!empty($event)) && (!empty($round)))    // check parameters
        {
            // check if another round follows
            $result = mysql_query("    SELECT
                                                xRunde
                                            FROM
                                                runde
                                            WHERE xWettkampf = $event
                                            ORDER BY
                                                Datum
                                                , Startzeit
                                            ");

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {
                $found = FALSE;

                while($row = mysql_fetch_row($result))
                {
                    $r = $row[0];            // keep ID of round currently processed
                    if($found == TRUE) {    // break if this is the next round
                        break;
                    }
                    if($row[0] == $round) {    // current round found
                        $found = TRUE;
                    }
                }
                mysql_free_result($result);
                
                if(($found == TRUE) && ($r != $round))        // next round found
                {
                    $nextRound = $r;
                }
            }
        }    // ET input parameters
        return $nextRound;
    }


/*
 * ------------------------------------------------------
 *
 *    String Format Functions
 *    -----------------------
 *    different functions to format strings to support
 * MySQL formats.
 *
 * ------------------------------------------------------
 */

    /**
     * format year of birth
     *
     */
    function AA_formatYearOfBirth($year)
    {
        $y=substr($year, -2);
        return $y;
    }

    /**
     * set two-digit year to correct four-digit year
     *
     */
    function AA_setYearOfBirth($year)
    {
        if($year <= 30) {
            $year = $year+2000;
        }
        else if ($year <= 99) {
            $year = $year+1900;
        }
        return $year;
    }



    /**
     * format meter result
     *
     */
    function AA_formatResultMeter($meter)
    {
        if($meter > 0)        // leave negative or zero results unchanged
        {
            $m = (int) ($meter / 100);        // calculate meters
            $cm = $meter % 100;    // keep remainder

            $meter = $m . $GLOBALS['cfgResultsMeterSeparator'];
            if($cm < 10) {
                $meter = $meter . "0";
            }
            $meter = $meter . $cm;
        }    // ET negative value
        else  if($meter == $GLOBALS['cfgMissedAttempt']['db']) {
            $meter = $GLOBALS['cfgMissedAttempt']['code'];
        }
        elseif($meter == $GLOBALS['cfgMissedAttempt']['dbx']) {
            $meter = $GLOBALS['cfgMissedAttempt']['codeX'];
        }

        return $meter;
    }



    /**
     * format time result
     *  - rankingList: on ranking lists show only hundrets of seconds
     *  - secFlag: format time in seconds e.g. 62.12
     */
    function AA_formatResultTime($time, $rankingList = false, $secFlag = false)
    {      
        if($time >= 0)
        {
            if($secFlag){
                $sec = (int) ($time / 1000);            // calculate only in seconds
                $frac = $time % 1000;
            }else{
                $hrs = (int) ($time / 3600000);        // calculate hours
                $time = $time % 3600000;    // keep remainder
                $min = (int) ($time / 60000);        // calculate minutes
                $time = $time % 60000;        // keep remainder
                $sec = (int) ($time / 1000);            // calculate seconds
                $frac = $time % 1000;        // keep remainder
            }

            $time = '';

            // Hours
            if($hrs > 0) {
                $time = $hrs . $GLOBALS['cfgResultsHourSeparator'];
            }

            // Minutes
            if(($min > 0) && ($time == '')) {    // no time set yet
                $time = $min . $GLOBALS['cfgResultsMinSeparator'];
            }
            else if($min >= 10) {        // hrs already set
                $time = $time . $min . $GLOBALS['cfgResultsMinSeparator'];
            }
            else if(($min < 10) && ($time != '')) { // hrs already set
                $time = $time . "0" . $min . $GLOBALS['cfgResultsMinSeparator'];
            }
        
            // Seconds
            if(($sec >= 0) && ($time == '')) {    // no time set yet
                $time = $sec . $GLOBALS['cfgResultsSecSeparator'];
            }
            else if($sec >= 10) {        // min already set
                $time = $time . $sec . $GLOBALS['cfgResultsSecSeparator'];
            }
            else if(($sec < 10) && ($time != '')) {    // min already set
                $time = $time . "0" . $sec . $GLOBALS['cfgResultsSecSeparator'];
            }

            // Fractions
            
            if($rankingList){
                $frac = ceil($frac/10);
                if($frac < 10) {
                    $time = $time . "0";
                }
                if($frac >= 100){
                    $tmp = mktime($hour, $min, $sec);
                    $tmp++;
                    $frac = "00";
                    
                    if(date("H",$tmp) > 0){
                        $time = date("H".$GLOBALS['cfgResultsHourSeparator']."i".$GLOBALS['cfgResultsMinSeparator']."s".$GLOBALS['cfgResultsSecSeparator'],$tmp);
                    }elseif(date("i",$tmp) > 0){
                        $time = date("i".$GLOBALS['cfgResultsMinSeparator']."s".$GLOBALS['cfgResultsSecSeparator'],$tmp);
                    }elseif(date("s",$tmp) > 0){      
                        $time = date("s".$GLOBALS['cfgResultsSecSeparator'],$tmp);
                    }
                    if(substr($time,0,1) == 0){
                        $time = substr($time,1);
                    }
                    if($secFlag){
                        $time = ((date("i",$tmp)*60)+date("s",$tmp)).$GLOBALS['cfgResultsSecSeparator'];
                    }
                }
                $time = $time . $frac; // on rankinglist, show only 100of secs, rounded up in each case
            }else{
                if($frac < 100) { // add a zero if fraction is only 2 digits
                    $time = $time . "0";
                }
                if($frac < 10) { // add again a zero if fraction is 1 digit  
                    $time = $time . "0";
                }
                if($frac > 0) {
                    if($frac % 10 == 0) {    // show hundredths of seconds
                        $time = $time . round($frac/10);
                    }
                    else {            // show thousandths of seconds
                        $time = $time . $frac;
                    }
                }}
        }    // ET negative value

        return $time;
    }
    
    
    /**
     * format day times entered in one field
     *     return array (hour, minutes)
     *
    **/
    
    function AA_formatEnteredTime($st){
        $ret = false;
        
        $st=trim($st);
        if (strlen($st) < 4 && $st == 0){
            $st=sprintf("%04d", $st); 
        } 
        elseif (strlen($st) == 1) {
            $st=sprintf("%02d", $st)."00";
        }
        elseif (strlen($st) == 2) {
            $st.="00";
        }        
        
        if(preg_match("/[\.,;:]/",$st) == 0){
            $ret = array();
            $ret[] = substr($st,0,-2);
            if(strlen($st) == 3){
                $ret[] = substr($st,1);
            }elseif(strlen($st) == 4){
                $ret[] = substr($st,2);
            }
        }else{
            $ret = array();
            $ret = preg_split("/[\.,;:]/", $st);
        }
        
        return $ret;
    }



/*
 * ------------------------------------------------------
 *
 *    HTML Print Functions
 *    --------------------
 *    different functions generating HTML output
 *
 * ------------------------------------------------------
 */

    /**
     * Error Message
     * - show javascript alert
     * - show error message in status bar
     */
    function AA_printErrorMsg($msg)
    {    
        // provide plain text message for certain DB errors
        //echo $msg;
        if(strncmp($msg, '1062', 4) == 0) {
            $msg = $GLOBALS['strDBErrorDoubleEntry'];
        }
        else if(strncmp($msg, '1064', 4) == 0) {
            $msg = $GLOBALS['strDBErrorSQLError'];
        }
?>
        <script type="text/javascript">
        <!--
            alert("<?php echo $msg; ?>");
            top.frames[2].location.href= "status.php?msg=<?php echo $GLOBALS['strError']
                . ": " . $msg; ?>";
        //-->
        </script>
<?php
    }


    /**
     * Error Message
     * - show javascript alert
     * - show error message in status bar
     */
    function AA_printErrorPage($msg)
    {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>error_page</title>
</head>
<body>
<?php    AA_printErrorMsg($msg); ?>
</body>
<?php
    }



    /**
     * Warning Message
     */
    function AA_printWarningMsg($msg)
    {
?>
    <table>
        <tr><td class='warning'><?php echo $msg; ?></td><tr>
    </table>
<?php
    }


    /**
     * Drop-down list Category, with auto-update
     *        arg. 1 = category ID
     *        arg. 2 = teams only
     *
     */
    function AA_printCategorySelectionDropDown($category)
    {
        require('./lib/cl_gui_dropdown.lib.php');
?>
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strCategory']; ?></th>
<?php
        $dd = new GUI_CategoryDropDown($category, 'document.cat_selection.submit()');
?>
        </tr>
    </table>
<?php
    }
    
    /**
     * Drop-down list Category, with auto-update
     *        arg. 1 = category ID
     *        arg. 2 = teams only
     *
     */
    function AA_printCategorySvmSelectionDropDown($cat_svm, $category)
    {
        require('./lib/cl_gui_dropdown.lib.php');
?>
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strSvmCategory']; ?></th>
<?php
        $dd = new GUI_CategorySvmDropDown($cat_svm, 'document.cat_svm_selection.submit()', $category);
?>
        </tr>
    </table>
<?php
    }


    /**
     * Category selection
     *    query arg. 1 = form_action
     *    query arg. 2 = category ID
     *
     */
    function AA_printCategorySelection($form_action, $category, $method='post')
    {
?>
<form action='<?php echo $form_action; ?>' method='<?php echo $method; ?>' name='cat_selection'>
    <input name='arg' type='hidden' value='select_cat' />
<?php
        AA_printCategorySelectionDropDown($category);
?>
</form>
<?php
    }


    /**
     * Category selection on entry form
     *        arg. 1 = form_action
     *        arg. 2 = category ID
     *        arg. 3 = club ID
     *        arg. 2 = team events only
     */
    function AA_printCategoryEntries($form_action, $category, $club, $teams=FALSE)
    {
?>
<form action='<?php echo $form_action; ?>' method='post' name='cat_selection'>
    <input name='club' type='hidden' value='<?php echo $club; ?>' /> 
<?php
        AA_printCategorySelectionDropDown($category, $teams);
?>
</form> 
<?php
    }
    
    /**
     * Category SVM selection on entry form
     *        arg. 1 = form_action
     *        arg. 2 = category ID
     *        arg. 3 = club ID
     *        arg. 2 = team events only
     */
    function AA_printCategorySvmEntries($form_action, $cat_svm, $category, $club, $teams=FALSE)
    {
?>
<form action='<?php echo $form_action; ?>' method='post' name='cat_svm_selection'>
    <input name='club' type='hidden' value='<?php echo $club; ?>' />
    <input name='category' type='hidden' value='<?php echo $category; ?>' />    
    <input name='category_svm' type='hidden' value='<?php echo $cat_svm; ?>' />     
<?php
        AA_printCategorySvmSelectionDropDown($cat_svm, $category, $teams);
?>
</form>
<?php
    }



    /**
     * Club selection drop down
     *    query arg. 1 = form_action
     *    query arg. 2 = club ID
     *    query arg. 3 = category ID
     *    query arg. 4 = event ID
     *    query arg. 5 = all:    show all clubs
     */
    function AA_printClubSelection($form_action, $club, $category, $event, $all=false)
    {
        require('./lib/cl_gui_dropdown.lib.php');
?>
<script type="text/javascript">
<!--
    function check()
    {
        if(document.club_selection.club.value == 'new')
        {
            window.open("admin_clubs.php", "_self");
        }
        else {
            document.club_selection.submit()
        }
    }
//-->
</script>

<form action='<?php echo $form_action; ?>' method='post' name='club_selection'>
    <input name='arg' type='hidden' value='select_club' />
    <input name='category' type='hidden' value='<?php echo $category; ?>' />
    <input name='event' type='hidden' value='<?php echo $event; ?>' />
        <table>
            <tr>
                <th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
<?php
        $dd = new GUI_ClubDropDown($club, $all, "check()");
?>
        </tr>
    </table>
</form>
<?php
    }


    /**
     * Event selection drop down
     *    query arg. 1 = form_action
     *    query arg. 2 = category ID
     *    query arg. 3 = event ID
     *    query arg. 4 = HTTP method (default GET)
     */
    function AA_printEventSelection($action, $category, $event, $method='get')
    {
?>
<form action='<?php echo $action; ?>' method='<?php echo $method; ?>'
        name='event_selection'>
    <input name='category' type='hidden' value='<?php echo $category; ?>' />
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strEvent']; ?></th>
<?php

        $dd = new GUI_EventDropDown($category, $event, 'document.event_selection.submit()');
?>
        </tr>
    </table>
</form>
<?php
    }
    
    
    /**
     * Combined event selection drop down
     *    query arg. 1 = form_action
     *    query arg. 2 = category ID
     *    query arg. 3 = combined Code
     *    query arg. 4 = HTTP method (default GET)
     */
    function AA_printEventCombinedSelection($action, $category, $comb, $method='get')
    {
?>
<form action='<?php echo $action; ?>' method='<?php echo $method; ?>'
        name='event_combined_selection'>
    <input name='category' type='hidden' value='<?php echo $category; ?>' />
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strCombinedEvent']; ?></th>
<?php

        $dd = new GUI_EventCombinedDropDown($category, $comb, 'document.event_combined_selection.submit()');
?>
        </tr>
    </table>
</form>
<?php
    }


    /**
     * Heat Selection:
     *        requires anchor tags (#heat_X) to be set in HTML page
     *        - arg. 1 = round ID
     *        - arg. 1 = speaker page
     */
    function AA_printHeatSelection($round, $speaker=false)
    {
        require('./lib/cl_gui_button.lib.php');

        $status = "";
        if($speaker == true) {
            $status = ", Status";    // select heat status
        }
        
        // read all heats
        // (Remark: LPAD(s.Bezeichnung,5,'0') is used to order heats by their
        // name. This trick is necessary as 'Bezeichnung' may be alpha-numeric.)
        $result = mysql_query("
            SELECT
                xSerie
                , Bezeichnung
                , LPAD(Bezeichnung,5,'0') as heatid
                $status
            FROM
                serie
            WHERE xRunde = $round
            ORDER BY
                heatid
        ");

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            // show heat selection if more than one heat
            if(mysql_num_rows($result) > 1)
            {
?>
<form>
<table>
    <tr>
        <th class='dialog'><?php echo $GLOBALS['strHeats']; ?></th>
        <td />
<?php                   
                $class='nav';
                while($row = mysql_fetch_row($result))
                {    
                    if(($speaker == true)
                        && ($row[3] == $GLOBALS['cfgHeatStatus']['announced']))
                    {
                        echo "<td id='heat_$row[0]' class='nav_announced'>\n";
                    }
                    else {
                        echo "<td id='heat_$row[0]' class='nav'>\n";
                    }
                    ?>
                    <a href='#heat_<?php echo $row[1]; ?>'><?php echo $row[1]; ?></a>
                    </td>
                    <?php                    
                }    
?>
        </td>
    </tr>
</table>
</form>
<?php
            }
            mysql_free_result($result);
        }
    }


    /**
     * Relay selection drop down
     *    query arg. 1 = action
     *    query arg. 2 = category ID
     *    query arg. 3 = event ID
     *
     */
    function AA_printRelaySelection($action, $category, $event, $club)
    {
?>
<form action='<?php echo $action; ?>' method='post' name='rel_selection'>
    <input name='arg' type='hidden' value='select_rel' />
    <input name='category' type='hidden' value='<?php echo $category; ?>' />
    <input name='club' type='hidden' value='<?php echo $club; ?>' />
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
<?php
        $dd = new GUI_EventDropDown($category, $event, 'document.rel_selection.submit()', true);
?>
        </tr>
    </table>
</form>
<?php
    }


    /**
     * Round selection drop down
     * -------------------------
     */

    function AA_printRoundSelection($action, $category, $event, $round)
    {   
?>
    <form action='<?php echo $action; ?>' method='post' name='round_selection'>
        <input name='arg' type='hidden' value='select_round' />
        <input name='category' type='hidden' value='<?php echo $category; ?>' />
        <input name='event' type='hidden' value='<?php echo $event; ?>' />
        <table>
            <tr>
                <th class='dialog'><?php echo $GLOBALS['strRound']; ?></th>
<?php
        $dd = new GUI_RoundDropDown($event, $round);
?>
            </tr>
        </table>
    </form>
<?php
    }

    /**
     * get the best result from previous day for disciplines 
     * -----------------------------------------------------
     * 
     * Variables DATE and TIME **MUST** be specified together!
     */  
                         
function AA_getBestPrevious($disciplin, $enrolement, $order, $date = '', $time = '', $previous_date = '')
{   
    $best = 0; 
       
    $query="SELECT 
                    a.xAthlet
             FROM
                    anmeldung AS a
             WHERE
                    a.xAnmeldung = " . $enrolement;
    $res_a = mysql_query($query);   
      
    if(mysql_errno() > 0){
        AA_printErrorPage(mysql_errno().": ".mysql_error());
    }else{ 
            $where = ($date!='') ? "AND (r.Datum < '".$date."' OR (r.Datum = '".$date."' AND r.Startzeit < '".$time."')) " : "";
        
            $row_a=mysql_fetch_row($res_a);             
            $sql = "SELECT ss.xSerienstart, 
                           a.xAthlet, 
                           rs.Leistung, 
                           r.Datum 
                      FROM runde AS r 
                 LEFT JOIN serie AS s USING(xRunde) 
                 LEFT JOIN serienstart AS ss USING(xSerie) 
                 LEFT JOIN start AS st USING(xStart) 
                 LEFT JOIN anmeldung AS a USING(xAnmeldung) 
                 LEFT JOIN athlet AS at USING(xAthlet) 
                 LEFT JOIN wettkampf AS w ON(st.xWettkampf = w.xWettkampf) 
                 LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d USING(xDisziplin) 
                 LEFT JOIN verein AS v ON(at.xVerein = v.xVerein) 
                 LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON(r.xRundentyp = rt.xRundentyp) 
                INNER JOIN resultat AS rs ON(ss.xSerienstart = rs.xSerienstart) 
                     WHERE a.xAnmeldung = ".$enrolement." 
                       AND d.xDisziplin = ".$disciplin." 
                       AND w.xMeeting = ".$_COOKIE['meeting_id']." 
                       AND at.xAthlet = ".$row_a[0]." 
                       ".$where."
                  ORDER BY rs.Leistung ".$order.";";
                                          
            $result = mysql_query($sql);  
               
            if(mysql_errno() > 0){
                    AA_printErrorPage(mysql_errno().": ".mysql_error());
            }else{ 
                if (mysql_num_rows($result) > 0) {  
                    $row=mysql_fetch_row($result);  
                    $best=$row[2];                  
                    $previous_date = $row[3];
                }
            }  
    }
   return $best;   
}
 
   /**
     * get reduction of fee
     * --------------------
     */     

function AA_getReduction() {
    $reduction=0;
    
    $sql="SELECT
            StartgeldReduktion  
        FROM
            meeting
        WHERE
            xMeeting = " .  $_COOKIE['meeting_id'];
            
  $result = mysql_query($sql); 
     
   if(mysql_errno() > 0){
            AA_printErrorPage(mysql_errno().": ".mysql_error());
     }else{ 
           if (mysql_num_rows($result) > 0) {  
                $row=mysql_fetch_row($result);  
                $reduction=$row[0];
        }  
     }   
 return $reduction;
} 


function AA_getEventTypesCat(){
// get event-types categories (single, combined, club (svm))
    $res = mysql_query('SELECT Typ FROM wettkampf WHERE xMeeting = ' .$_COOKIE['meeting_id']  . ' GROUP BY Typ ORDER BY Typ');
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    } else {
        $cfgEventType = $GLOBALS['cfgEventType'];
        while($row=mysql_fetch_array($res)){
            if ( $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeSingle']]){
                $eventTypeCat['single'] = true;    
            }else if ($row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeSingleCombined']]){
                $eventTypeCat['combined']=true;
                $show_combined = true;
            } else if ($row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeSVMNL']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMA']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMB']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMC']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubFA']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubFB']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubBasic']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubAdvanced']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubTeam']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubCombined']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMixedTeam']]){
                $eventTypeCat['club'] = true;
            } else if ($row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeTeamSM']]){
                $eventTypeCat['teamsm'] = true;            
            }
                
        }
        return $eventTypeCat;
    }
}

function AA_getEventTypes($round){
// get event-type (single, combined, club (svm))
    $res = mysql_query('SELECT 
                            w.Typ 
                        FROM 
                            runde AS r 
                            LEFT JOIN wettkampf AS w USING (xWettkampf)  
                        WHERE w.xMeeting = ' .$_COOKIE['meeting_id']  . ' AND r.xRunde = ' . $round );
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    } else {
        $cfgEventType = $GLOBALS['cfgEventType'];
        if (mysql_num_rows($res) > 0 )  {
            $row=mysql_fetch_array($res);
            if ( $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeSingle']]){
                $eventTypeCat['single'] = true;    
            }else if ($row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeSingleCombined']]){
                $eventTypeCat['combined']=true;
                $show_combined = true;
            } else if ($row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeSVMNL']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMA']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMB']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMC']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubFA']]
                        // || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubFB']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubBasic']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubAdvanced']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubTeam']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubCombined']]
                        || $row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeClubMixedTeam']]){
                $eventTypeCat['club'] = true;
            } else if ($row['Typ'] == $cfgEventType[$GLOBALS['strEventTypeTeamSM']]){
                $eventTypeCat['teamsm'] = true;            
            }
        }   
        return $eventTypeCat;
    }
}

/**
     * get sort display order from disziplines
     * ---------------------------------------
     */                 
 function AA_getSortDisc($discFrom, $discTo ){
      $arrDisc=array();    
      $arrDisc[0]=false;  
      $result = mysql_query("SELECT d.xDisziplin, d.Anzeige"
                                        . " FROM disziplin_" . $_COOKIE['language'] . " AS d" 
                                        . " WHERE d.xDisziplin IN (" . $discFrom . ","  .  $discTo . ")");  
                                                              
                                        
      if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
      }
      else {
                while($row = mysql_fetch_row($result)){
                    $arrDisc[0]=true; 
                    $arrDisc[$row[0]]=$row[1];
                }
            }   
      return $arrDisc;    
 }
 
 /**
     * get sort display order from categories
     * --------------------------------------
     */                 
 function AA_getSortCat($catFrom, $catTo ){  
      $arrCat=array();   
      $arrCat[0]=false; 
      $result = mysql_query("SELECT k.xKategorie, k.Anzeige"
                                        . " FROM kategorie AS k" 
                                        . " WHERE k.xKategorie IN (" . $catFrom . ","  .  $catTo . ")");  
                                        
      if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
      }
      else {  
                while($row = mysql_fetch_row($result)){
                    $arrCat[0]=true; 
                    $arrCat[$row[0]]=$row[1];
                }
            }  
      return $arrCat;     
 }

/**
     * read merged rounds and select all events 
     * ----------------------------------------
     */    
function AA_getMergedEvents($round){    
    $sqlEvents = "";
    $eventMerged = false;
    $result = mysql_query("SELECT 
                                xRundenset 
                           FROM 
                                rundenset
                           WHERE    
                                xRunde = $round
                                AND xMeeting = ".$_COOKIE['meeting_id']);
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
        $rsrow = mysql_fetch_array($result); // get round set id    
    }
    
    if($rsrow[0] > 0){   
        $sql = "SELECT
                    xWettkampf  
                FROM
                    rundenset 
                LEFT JOIN 
                    runde USING(xRunde)
                WHERE
                    xMeeting = ".$_COOKIE['meeting_id']."
                AND
                    xRundenset = ".$rsrow[0].";";
        
        $result = mysql_query($sql);
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
        }else{
            if(mysql_num_rows($result) > 0){ // merged rounds     
                    $sqlEvents .= "(".$event;
                    while($row = mysql_fetch_array($result)){
                        $eventMerged = true;   
                        $sqlEvents .= $row[0] . ",";
                    }   
                $sqlEvents = substr($sqlEvents,0,-1).")";  
            }      
        }
    } 
     if (!$eventMerged) {  
           $sqlEvents = ""; 
    }   
    return  $sqlEvents;
}   

/**
     * read merged rounds an select all rounds
     * ---------------------------------------
     */    
function AA_getMergedRounds($round){  
    $sqlRounds = "";
    $roundMerged = false;
    $result = mysql_query("SELECT 
                                xRundenset 
                           FROM 
                                rundenset
                           WHERE    
                                xRunde = $round
                                AND xMeeting = ".$_COOKIE['meeting_id']);
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
        $rsrow = mysql_fetch_array($result); // get round set id   
    }
    
    if($rsrow[0] > 0){   
        $sql = "SELECT 
                    xRunde 
                FROM 
                    rundenset
                WHERE    
                    xRundenset = $rsrow[0]
                    AND xMeeting = ".$_COOKIE['meeting_id'];
                    
        $res = mysql_query($sql);
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }else{
              $sqlRounds .= "(".$row[0];   
              while($row = mysql_fetch_array($res)){   // get merged rounds  
                   $roundMerged = true;  
                   $sqlRounds .= $row[0] . ","; 
                }
                $sqlRounds = substr($sqlRounds,0,-1).")";  
            }   
        }   
    if (!$roundMerged) {  
           $sqlRounds = ""; 
    }  
    return  $sqlRounds;

}  

/**
     * read merged rounds and select all events with corresponding rounds
     * -----------------------------------------------------------------
     */    
function AA_getMergedEventsFromEvent($event){   
    $sqlEvents = "";
    $eventMerged = false;    
     
        $sql = "SELECT  
                     r.xRunde
                FROM
                    rundenset 
                LEFT JOIN 
                    runde as r USING(xRunde)
                WHERE
                    xMeeting = ".$_COOKIE['meeting_id']."
                AND
                    xWettkampf = ".$event;   
       
        $result = mysql_query($sql);
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
        }else{
            $row = mysql_fetch_array($result); // no merged rounds              
            if ($row[0] > 0){    
                  $result1 = mysql_query("SELECT 
                                xRundenset 
                           FROM 
                                rundenset
                           WHERE    
                                xRunde = $row[0]
                                AND xMeeting = ".$_COOKIE['meeting_id']);
                  if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }else{
                    $rsrow = mysql_fetch_array($result1); // get round set id
                    mysql_free_result($result1);
                  }  
                  if($rsrow[0] > 0){   
                    $sql1 = "SELECT
                             xWettkampf  
                          FROM
                            rundenset 
                            LEFT JOIN runde USING(xRunde)
                          WHERE
                            xMeeting = ".$_COOKIE['meeting_id']."
                            AND xRundenset = ".$rsrow[0];
        
                    $result2 = mysql_query($sql1);
                   
                    if(mysql_errno() > 0){
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
                    }else{  
                        if(mysql_num_rows($result2) > 0){ // merged rounds     
                            $sqlEvents = "(";
                            while($row = mysql_fetch_array($result2)){
                                $eventMerged = true;   
                                $sqlEvents .= $row[0] . ",";
                            }   
                            $sqlEvents = substr($sqlEvents,0,-1).")";  
                        }  
                    }
                 }
            }
        }  
        
    if (!$eventMerged) {  
           $sqlEvents = ""; 
    }   
   return  $sqlEvents;  
    
}     

/**
     * get the main round event or event of merged rounds
     * --------------------------------------------------
     * 
     *  flagRound: true  --> the main round will be returned
     *  flagRound: false  --> the main event will be returned
     */    
function AA_getMainRoundEvent($event,$flagRound){     
    $mainRound = "";
    $eventMerged = false;    
   
        $sql = "SELECT  
                     xRundenset  
                FROM
                    rundenset 
                LEFT JOIN 
                    runde as r USING(xRunde)
                WHERE
                    xMeeting = ".$_COOKIE['meeting_id']."
                AND
                    xWettkampf = ".$event;   
       
        $result = mysql_query($sql);
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
        }else{
            $row = mysql_fetch_array($result); // no merged rounds              
            if ($row[0] > 0){    
                  $result1 = mysql_query("SELECT 
                                xRunde  
                           FROM 
                                rundenset
                           WHERE    
                                xRundenset = $row[0]
                                AND Hauptrunde = 1
                                AND xMeeting = ".$_COOKIE['meeting_id']);
                  if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }else{
                    $rsrow = mysql_fetch_array($result1); // get round set id  
                  }  
                  if($rsrow[0] > 0){     
                       if ($flagRound){
                          $mainRound=$rsrow[0]; 
                          $eventMerged=true;  
                       }
                       else {
                            $sql1 = "SELECT
                                        xWettkampf  
                                     FROM
                                        rundenset as rs 
                                        LEFT JOIN runde as r ON (r.xRunde=rs.xRunde)
                                     WHERE
                                        xMeeting = ".$_COOKIE['meeting_id']."
                                        AND r.xRunde = ".$rsrow[0];
                            
                            $result2 = mysql_query($sql1);  
                         
                            if(mysql_errno() > 0){
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }else{
                                $erow = mysql_fetch_array($result2); // get round set id 
                            }  
                            if($erow[0] > 0){ 
                                $mainRoundEvent=$erow[0];  
                                $eventMerged=true;
                            }
                     }
                 }
            }
        }   
    if (!$eventMerged) {  
           $main = ""; 
    }
    else {
         if ($flagRound)    
             $main=$mainRound;  
         else
            $main=$mainRoundEvent;   
    
    }    
   return  $main;    
}
/**
     * get main round 
     * ---------------  
     */    
function AA_getMainRound($round){     
    $mainRound = 0;   
    $sql = "SELECT  
                     xRundenset  
            FROM
                    rundenset    
            WHERE
                    xMeeting = ".$_COOKIE['meeting_id']."
                    AND xRunde = ".$round;   
       
    $result = mysql_query($sql);
    if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
    }else{
            $row = mysql_fetch_array($result); // no merged rounds              
            if ($row[0] > 0){    
                  $result1 = mysql_query("SELECT 
                                                xRunde  
                                          FROM 
                                                rundenset
                                          WHERE    
                                                xRundenset = $row[0]
                                                AND Hauptrunde = 1
                                                AND xMeeting = ".$_COOKIE['meeting_id']);
                  if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }else{
                    $rsrow = mysql_fetch_array($result1); // get round set id                         
                    if($rsrow[0] > 0){  
                          $mainRound=$rsrow[0]; 
                    }
                  }  
            }
        } 
   return  $mainRound;    
}

/**
     * check merged rounds
     * -------------------
     *  mergeMain: 0    not e merged round
     *  mergeMain: 1    not main round 
     *  mergeMain: 2    main round 
     *  
     */    
function AA_checkMainRound($round){    
    $mainRound = "";
    $mergedMain = 0;    
      
        $sql = "SELECT  
                     xRunde,
                     Hauptrunde 
                FROM
                    rundenset   
                WHERE
                    xMeeting = ".$_COOKIE['meeting_id']."
                    AND xRunde = ".$round;   
       
        $result = mysql_query($sql);
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
        }else{
            if (mysql_num_rows($result) > 0) {              
                $row = mysql_fetch_array($result);  
                if ($row[1] == 1)
                    $mergedMain=2;                // main round
                else
                    $mergedMain=1;                // not main round   
            }
            else
               $mergedMain=0;                     // not e merged round
        }  
   return  $mergedMain; 
}  


/**
     * read merged rounds and select all category with corresponding rounds
     * --------------------------------------------------------------------
     */    
function AA_mergedCatEvent($category,$event){    
    $sqlCat = "";
    $eventMerged = false;    
    
    $sql = "SELECT  
                    r.xRunde
            FROM
                    rundenset 
                    LEFT JOIN runde as r USING(xRunde)
            WHERE
                    xMeeting = ".$_COOKIE['meeting_id']."
                    AND xWettkampf = ".$event;   
       
    $result = mysql_query($sql);
    if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
    }else{
            $row = mysql_fetch_array($result); // no merged rounds              
            if ($row[0] > 0){    
                  $result1 = mysql_query("SELECT 
                                                xRundenset 
                                          FROM 
                                                rundenset
                                          WHERE    
                                                xRunde = $row[0]
                                                AND xMeeting = ".$_COOKIE['meeting_id']);
                  if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }else{
                    $rsrow = mysql_fetch_array($result1); // get round set id   
                  }  
                  if($rsrow[0] > 0){   
                    $sql1 = "SELECT
                                    w.xKategorie  
                             FROM
                                    rundenset 
                                    LEFT JOIN runde AS r USING(xRunde)
                                    LEFT JOIN wettkampf AS w ON (w.xWettkampf=r.xWettkampf)
                             WHERE
                                    w.xMeeting = ".$_COOKIE['meeting_id']."
                                    AND xRundenset = ".$rsrow[0];
        
                    $result2 = mysql_query($sql1);
                   
                    if(mysql_errno() > 0){
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
                    }else{  
                        if(mysql_num_rows($result2) > 0){ // merged rounds     
                            $sqlCat = "(";
                            while($row = mysql_fetch_array($result2)){
                                $eventMerged = true;   
                                $sqlCat .= $row[0] . ",";
                            }   
                            $sqlCat = substr($sqlCat,0,-1).")";  
                        }  
                    }
                 }
            }
        }  
        
    if (!$eventMerged) {  
           $sqlCat = ""; 
    }     
    return  $sqlCat; 
}  

 /**
     * read merged rounds and select all category with corresponding rounds
     * --------------------------------------------------------------------
     */    
function AA_mergedCat($category){  
    $sqlCat = "";    
    $eventMerged = false;    
      
    $sql = "SELECT  
                    r.xRunde
            FROM
                    rundenset AS rs
                    LEFT JOIN runde as r ON (r.xRunde = rs.xRunde)
                    LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)
            WHERE
                    w.xMeeting = ".$_COOKIE['meeting_id']."
                    AND w.xKategorie = " .$category;   
       
    $result = mysql_query($sql);
    if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
    }else{  
            $row = mysql_fetch_array($result); // no merged rounds              
            if ($row[0] > 0){    
                  $result1 = mysql_query("SELECT 
                                                xRundenset 
                                          FROM 
                                                rundenset
                                          WHERE    
                                                xRunde = $row[0]
                                                AND xMeeting = ".$_COOKIE['meeting_id']);
                  if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                  }else{ 
                    $rsrow = mysql_fetch_array($result1); // get round set id
                    mysql_free_result($result1);
                  }  
                  if($rsrow[0] > 0){  
                    $sql1 = "SELECT
                                    w.xKategorie  
                             FROM
                                    rundenset 
                                    LEFT JOIN runde AS r USING(xRunde)
                                    LEFT JOIN wettkampf AS w ON (w.xWettkampf=r.xWettkampf)
                             WHERE
                                    w.xMeeting = ".$_COOKIE['meeting_id']."
                                    AND xRundenset = ".$rsrow[0];
        
                    $result2 = mysql_query($sql1);
                   
                    if(mysql_errno() > 0){
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            
                    }else{
                        if(mysql_num_rows($result2) > 0){ // merged rounds     
                            $sqlCat = "(";
                            while($row = mysql_fetch_array($result2)){
                                $eventMerged = true;   
                                $sqlCat .= $row[0] . ",";
                            }   
                            $sqlCat = substr($sqlCat,0,-1).")";  
                        }
                        mysql_free_result($result);
                    }
                 }
            }
        }      
    
    if (!$eventMerged) {  
           $sqlCat = ""; 
    }   
    return  $sqlCat;  
}  

   /**
     * get all rounds to set checked automatic
     * ---------------------------------------
     */   
function AA_getAllRoundsforChecked($event,$action,$round){   
    $sqlRoundset='';
    $sqlRound='';  
    $sqlEvent='';
    $arr_rounds[] = array();
    
    $result = mysql_query("select 
                                r.xRunde, 
                                r.xRundentyp
                            From  
                                wettkampf as w
                                LEFT JOIN runde as r on (r.xWettkampf=w.xWettkampf)
                            where
                                w.xWettkampf = " . $event . "
                                AND xMeeting = ".$_COOKIE['meeting_id']);
    if(mysql_errno() > 0){
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
         $i=0;    
         while($row = mysql_fetch_array($result)){ // get round set id   
               $i++;  
               $arr_rounds[0][$i]=$row[0];          // round
               $arr_rounds[1][$i]=$row[1];          // round typ    
        }     
    
        if (count($arr_rounds[0])>1){  
            foreach  ($arr_rounds[0] as $key){
                $sqlRound.=$key. ",";   
            } 
            if ($sqlRound!=''){
                $sqlRound=substr($sqlRound,0,-1);    
                // check if minimum one merged round is set
                $sqlr = "SELECT 
                            xRundenset, 
                            xRunde              
                        FROM
                            rundenset
                        WHERE 
                            xRunde IN (" . $sqlRound . ")
                            AND xMeeting = " .$_COOKIE['meeting_id'];     
                $resr = mysql_query($sqlr); 
                
                if(mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
                elseif(mysql_num_rows($resr) > 0) {        //   minimum one merged round      
                       while($rowr=mysql_fetch_array($resr)){
                             $sqlRoundset.=$rowr[0]. ",";  
                       }
                       $sqlRoundset=substr($sqlRoundset,0,-1); 
                       
                       $sqls = " SELECT  
                                    r.xWettkampf, 
                                    r.xRunde
                                FROM
                                    rundenset as rs
                                    LEFT JOIN runde as r ON (r.xRunde=rs.xRunde)
                                WHERE 
                                    xRundenset IN (" . $sqlRoundset . ")
                                    AND rs.Hauptrunde = 0
                                    AND xMeeting = " .$_COOKIE['meeting_id'];   
                       $ress = mysql_query($sqls);  
                            
                       if(mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                       }
                       else {                           
                             if (mysql_num_rows($ress) == 1) {            
                                // merged round for this event     
                                while($rows=mysql_fetch_array($ress)){
                                    $sqlEvent.=$rows[0]. ",";  
                                    }
                                $sqlEvent=substr($sqlEvent,0,-1);    
                                    
                                $sql = " SELECT  
                                            r.xRunde, 
                                            r.xRundentyp
                                         FROM  
                                            runde AS r 
                                         WHERE r.xWettkampf IN (" . $sqlEvent . ")";   
                                $res = mysql_query($sql); 
                               
                                if(mysql_errno() > 0) {
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }
                                else {                                     
                                    while ($row=mysql_fetch_array($res)){   
                                        if ($row[0]!=$rows[1]){ 
                                            $i=0;
                                            foreach ($arr_rounds[0] as $key){     
                                                $i++;
                                                if ($key!=$rowr[1] && $arr_rounds[1][$i] == $row[1]) {   
                                                    $mr=$key;
                                                    $r= $row[0];
                                                    if ($action=='add')
                                                        AA_addRoundset($mr,$r);        // create roundset
                                                    else
                                                        AA_delRoundset($mr,$r);        // delete roundset   
                                                }   
                                            }   
                                        } 
                                    }   
                                }    
                            }    
                            else { 
                                   // there exist already merged rounds for an event
                                   if ($action=='add'){                             // add roundset 
                                        // merged round for this event 
                                        while($rows=mysql_fetch_array($ress)){
                                            $sqlEvent.=$rows[0]. ",";  
                                        }
                                        $sqlEvent=substr($sqlEvent,0,-1);  
                                                        
                                        $sqls = "SELECT 
                                                r.xRunde, 
                                                r.xRundentyp, 
                                                rs.Hauptrunde, 
                                                rs.xRundenset, 
                                                w.xWettkampf
                                            FROM 
                                                runde AS r
                                                LEFT JOIN rundenset as rs ON  (r.xRunde=rs.xRunde AND rs.xMeeting = " .$_COOKIE['meeting_id'] .")
                                                LEFT JOIN wettkampf AS w ON (r.xWettkampf=w.xWettkampf)
                                            WHERE 
                                                r.xWettkampf IN (" . $sqlEvent . ")
                                                AND w.xMeeting = " .$_COOKIE['meeting_id'] ."
                                            ORDER BY r.xRundentyp, rs.xRundenset DESC";
                                            
                                        $ress = mysql_query($sqls); 
                                       
                                   }
                                   else {                                             // delete roundset
                                        $sql_event = "SELECT 
                                                            r.xWettkampf
                                                      FROM 
                                                            runde AS r
                                                      WHERE 
                                                            r.xRunde =" . $round;   
                                        $rese = mysql_query($sql_event); 
                                        if(mysql_errno() > 0) {
                                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                        }
                                        else {
                                            $ev=mysql_fetch_array($rese);
                                       
                                            $sqls = "SELECT 
                                                    r.xRunde, 
                                                    r.xRundentyp, 
                                                    rs.Hauptrunde, 
                                                    rs.xRundenset, 
                                                    w.xWettkampf
                                                 FROM 
                                                    runde AS r
                                                    LEFT JOIN rundenset as rs ON  (r.xRunde=rs.xRunde AND rs.xMeeting = " .$_COOKIE['meeting_id'] .")   
                                                    LEFT JOIN wettkampf AS w ON (r.xWettkampf=w.xWettkampf)
                                                 WHERE 
                                                    r.xWettkampf = (" . $ev[0] . ")
                                                    AND w.xMeeting = " .$_COOKIE['meeting_id'] ." 
                                                 ORDER BY r.xRundentyp, rs.xRundenset DESC";
                                            
                                            $ress = mysql_query($sqls);  
                                        } 
                                  }                             
                                  if(mysql_errno() > 0) {
                                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                  }
                                  else {                                    
                                        while ($row=mysql_fetch_array($ress)){
                                            if ($action=='del'){
                                                if ($row[3] >0 )
                                                    AA_delRoundsetMore($row[0],$row[3]);   
                                            } 
                                            else {                     
                                                if ($row[3] > 0){
                                                    $roundset=$row[3];   
                                                }       
                                                else {
                                                    if ($action=='add'){
                                                        AA_addRoundsetMore($row[0],$roundset);  
                                                    }       
                                                }
                                            }  
                                        }    
                                  }  
                            }
                       }
                }      
            }    
        }  
  }  
} 
       
 /**
     * add roundset 
     * ------------
     */   

 function AA_addRoundset($mr,$r){
     $select="SELECT 
                    xRunde
              FROM 
                    rundenset
              WHERE xRunde = " . $mr ."
                    AND xMeeting = " .$_COOKIE['meeting_id'];
              
     $res_ru=mysql_query($select); 
     if (mysql_num_rows($res_ru) == 0) {        
            // get next roundset number
            $result = mysql_query("SELECT MAX(xRundenset) FROM rundenset");
            $max = 0;
            if(mysql_num_rows($result) > 0){
                    $row = mysql_fetch_array($result);
                    $max = $row[0];
            }
            $max++;
                
            mysql_query("INSERT INTO rundenset SET
                        xRundenset = $max
                        , Hauptrunde = 1
                        , xRunde = $mr
                        , xMeeting = ".$_COOKIE['meeting_id']);
            if(mysql_errno() > 0){
                    $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
            }else{
                    $rs = $max;
            }     
            // insert new round     
           mysql_query("INSERT INTO rundenset SET
                        xRundenset = $rs
                        , Hauptrunde = 0
                        , xRunde = $r
                        , xMeeting = ".$_COOKIE['meeting_id']);
           if(mysql_errno() > 0){
                    $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
           }                                     
     }   
 }
/**
     * add roundset 
     * ------------
     */    
function AA_addRoundsetMore($r,$rs){    
     // insert new round       
     mysql_query("INSERT INTO rundenset SET
                        xRundenset = $rs
                        , Hauptrunde = 0
                        , xRunde = $r
                        , xMeeting = ".$_COOKIE['meeting_id']);
     if(mysql_errno() > 0){
            $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
     }   
 } 
 
/**
     * delete roundset 
     * ---------------
     */    
function AA_delRoundset($mr,$r){     
      $select="SELECT 
                    xRundenset
               FROM 
                    rundenset
               WHERE 
                    xRunde = " . $mr ."
                    AND xMeeting = " .$_COOKIE['meeting_id'];  
      $res_ru=mysql_query($select); 
      if (mysql_num_rows($res_ru) > 0) { 
            $row= mysql_fetch_row($res_ru);
            // remove round from set
            mysql_query("DELETE FROM rundenset WHERE
                            xRundenset = $row[0]
                            AND xMeeting = ".$_COOKIE['meeting_id']."
                            AND xRunde = $r");
            if(mysql_errno() > 0){
                $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
            }else{  
                // check if there are no more rounds in set
                $res = mysql_query("SELECT * FROM rundenset WHERE
                                        xRundenset = $row[0]");
                if(mysql_errno() > 0){
                    $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
                }else{ 
                    if(mysql_num_rows($res) == 1){ // mainround only
                        mysql_query("DELETE FROM rundenset WHERE
                                        xRundenset = $row[0]
                                        AND xMeeting = ".$_COOKIE['meeting_id']);
                        if(mysql_errno() > 0){
                            $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
                        }
                    }  
                }    
            } 
      }     
 }
 /**
     * delete roundset 
     * ---------------
     */    
 function AA_delRoundsetMore($mr,$r){     
    // remove round from set
    mysql_query("DELETE FROM rundenset WHERE
                    xRundenset = $r
                    AND xMeeting = ".$_COOKIE['meeting_id']."
                    AND xRunde = $mr");    
               
    if(mysql_errno() > 0){
        $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
    }
    else {
            // check if there are no more rounds in set
            $res = mysql_query("SELECT * FROM rundenset WHERE
                                xRundenset = $r");
            if(mysql_errno() > 0){
                    $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
            }else{  
                    if(mysql_num_rows($res) == 1){ // mainround only
                        mysql_query("DELETE FROM rundenset WHERE
                                    xRundenset = $r
                                    AND xMeeting = ".$_COOKIE['meeting_id']);
                        if(mysql_errno() > 0){
                            $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
                        }
                    }  
                } 
            }
      }     
 /**
     * count rounds from event 
     * -----------------------
     */   
function AA_countRound($round){
    $count=0;
    $sql="SELECT 
                r.xWettkampf
          FROM 
                runde as r     
          WHERE 
                r.xRunde = " . $round;
    
    $res = mysql_query($sql); 
    if(mysql_errno() > 0){
                $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
    }
    else {      
        if (mysql_num_rows($res) > 0){
            $row=mysql_fetch_array($res);  
               
            $sqlc="SELECT 
                        r.xRunde
                   FROM 
                        runde as r     
                   WHERE 
                        r.xWettkampf = " . $row[0];
    
            $resc = mysql_query($sqlc); 
            if(mysql_errno() > 0){
                        $GLOBALS['AA_ERROR'] = mysql_errno().": ".mysql_error();
            }
            else {      
                  $count=mysql_num_rows($resc);  
            }
        }  
     }
   return $count;
}  
   /**    
     * Check if group exist for this category and combined
     * ---------------------------------------------------
     */   
function AA_checkGroup($group,$cat,$comb){    
   
   $groupexist=false;
   $qGroup="SELECT
                    DISTINCT(a.Gruppe) as g
             FROM
                     wettkampf AS w
                    LEFT JOIN start AS st USING(xWettkampf)
                    LEFT JOIN anmeldung As a USING(xAnmeldung)
             WHERE
                     w.Mehrkampfcode = ".$comb ."
                    AND w.xKategorie = ".$cat ."              
                    AND w.xMeeting = ".$_COOKIE['meeting_id']."
                    AND a.Gruppe = '" . $group ."' 
                    ORDER BY g ASC;";
    
    $res = mysql_query($qGroup);
   
      if(mysql_errno() > 0)
        {
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
        if (mysql_num_rows($res) > 0) {
            $groupexist=true;
        }
    }
    return $groupexist;    
}    

/**    
     * Check if group exist for this events
     * ------------------------------------
     */   
function AA_checkGroupEvent($event,$cGroup){    
   
   $groupexist=false;
   $qGroup="SELECT 
                Gruppe 
            FROM 
                runde 
            WHERE 
                Gruppe= '" .$cGroup ."' 
                AND xWettkampf = ". $event;  
             
    $res = mysql_query($qGroup);  
   
      if(mysql_errno() > 0)
        {  
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
        if (mysql_num_rows($res) > 0) {
            $groupexist=true;
        }
    }
    return $groupexist;    
}    

/**
     * check if relay name exist in same category and discipline
     * ---------------------------------------------------------
     */   
function AA_checkRelayName($category,$event,$relayName){  
    $checkName=false;      
       $sql="SELECT 
             st.Name, 
             st.xKategorie,              
             w.xDisziplin
         FROM
             staffel AS st
            LEFT JOIN start AS s ON (s.xStaffel = st.xStaffel)
            LEFT JOIN wettkampf AS w ON (s.xWettkampf = w.xWettkampf)
           WHERE
               st.Name = '" . $relayName ."'
              AND st.xKategorie = " . $category ."  
              AND w.xWettkampf =  " . $event;
      
      $res = mysql_query($sql);  

    if(mysql_errno() > 0){  
          AA_printErrorMsg(mysql_errno() . ": " . mysql_error());  
    }
    else {       
        if (mysql_num_rows($res) == 1) {
           $checkName=true;                      // there exist already this relay name
          }   
    }    
   return $checkName;    
}    
      
      
/**    
     * heat selection drop down from
     * -----------------------------
     */

    function AA_printHeatSelectionDropDownFrom($action, $category, $event, $round, $heatFrom, $heatTo)
    {    
?>
    <form action='<?php echo $action; ?>' method='post' name='heat_selectionFrom'>
        <input name='arg' type='hidden' value='change_heatFrom' />
        <input name='category' type='hidden' value='<?php echo $category; ?>' />
        <input name='event' type='hidden' value='<?php echo $event; ?>' />
        <input name='round' type='hidden' value='<?php echo $round; ?>' />        
           <input name='heatFrom' type='hidden' value='<?php echo $heatFrom; ?>' /> 
           <input name='heatTo' type='hidden' value='<?php echo $heatTo; ?>' />     
        <table>
            <tr>
                <th class='dialog'><?php echo $GLOBALS['strHeat']. " ";  echo $GLOBALS['strOf2']; ?></th>
<?php
                  
               $dd = new GUI_HeatDropDownFrom($round, $heatFrom, false);          
        
?>
            </tr>
        </table>
    </form>
<?php
    }    
    
/**    
     * heat selection drop down to
     * -----------------------------
     */

    function AA_printHeatSelectionDropDownTo($action, $category, $event, $round, $heatFrom, $heatTo)
    {    
?>
    <form action='<?php echo $action; ?>' method='post' name='heat_selectionTo'>
        <input name='arg' type='hidden' value='change_heatTo' />          
        <input name='category' type='hidden' value='<?php echo $category; ?>' />
        <input name='event' type='hidden' value='<?php echo $event; ?>' />
        <input name='round' type='hidden' value='<?php echo $round; ?>' />        
        <input name='heatFrom' type='hidden' value='<?php echo $heatFrom; ?>' /> 
           <input name='heatTo' type='hidden' value='<?php echo $heatTo; ?>' />        
        <table>
            <tr>
                <th class='dialog'><?php echo $GLOBALS['strHeat']. " ";  echo $GLOBALS['strTo2']; ?></th>
<?php
                  
               $dd = new GUI_HeatDropDownTo($round, $heatTo, false);          
        
?>
            </tr>
        </table>
    </form>
<?php
    }    
 

 
 /**    
     * get points of the highest points of discipline from athlet with same total points
     * ---------------------------------------------------------------------------------
     */   
function AA_get_AthletBestPointDisc($points_disc_keep, $points_disc, $key_keep, $key_a)
{       
    arsort($points_disc_keep, SORT_NUMERIC);        //sort descending by points
    arsort($points_disc, SORT_NUMERIC);             //sort descending by points   
    
    $points_compare1 = array();
    $points_compare2 = array();   
            
    foreach($points_disc_keep as $key => $val) {
         $points_compare1[] = $val;
    }
    foreach($points_disc as $key => $val) {
         $points_compare2[] = $val;
    }  
            
    foreach($points_compare1 as $key => $val) {           
        if ($points_compare1[$key] > $points_compare2[$key]) {                
            return $key_a;            
        }
        elseif  ($points_compare1[$key] < $points_compare2[$key]) {  
            return $key_keep;                    
        }           
        // same points --> check next discipline          
    }  
     
    return 0;        // all disciplines same points
}


/**    
     * check if event is the last combined event (mehrkampfende = 1)
     * -------------------------------------------------------------
     */   
function AA_checkCombinedLast($event)
{   
   $comb_last=false;
   $sql="SELECT 
                mehrkampfcode 
            FROM 
                wettkampf as w
                LEFT JOIN meeting as m ON (m.xMeeting = w.xMeeting) 
            WHERE  
                w.xWettkampf = ". $event ." 
                AND w.Mehrkampfende = 1 
                AND m.xMeeting = ".$_COOKIE['meeting_id'];  
             
    $res = mysql_query($sql);  
   
    if(mysql_errno() > 0)
        {
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }else{
        if (mysql_num_rows($res) > 0) {
            $comb_last=true;
        }
    }
    return $comb_last;    
}

/**    
     * set new position to athletes (after 3 attempts (pass = 2) and at final (pass = 3))
     * ----------------------------------------------------------------------------------
     */   
function AA_newPosition($round, $pass)
{  
    global $cfgEightRank, $cfgMaxAthlete, $cfgInvalidResult;
    $field = '';
    
    if ($pass == 2){
       $field = ", ss.Position"; 
    }
    elseif ($pass == 3){
       $field = ", ss.Position2";    
    }
    
    // find all athletes with result -2 or -3
     $sql_d = "SELECT 
                  ss.xSerienstart "
                  . $field ."                      
                  , LPAD(s.Bezeichnung,5,'0') as heatid                     
                  , s.xSerie
                  , re.Leistung                  
            FROM runde AS r
                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                LEFT JOIN serienstart AS ss  ON (ss.xSerie = s.xSerie)
                LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)                                                      
                LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp 
                LEFT JOIN anlage AS an ON an.xAnlage = s.xAnlage
                LEFT JOIN resultat as re ON (re.xSerienstart = ss.xSerienstart)
            WHERE r.xRunde = " . $round ." AND ss.Rang > 0                 
            ORDER BY heatid, ss.Rang";             
    
       $res_d = mysql_query($sql_d);    
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else {
              $arr_heat = array();                            // athletes array with result -2 or -3 
              
              while ($row_d = mysql_fetch_row($res_d)) {  
                         if ($row_d[4] == $cfgInvalidResult['DNF']['code'] || $row_d[4] == $cfgInvalidResult['DSQ']['code'] ) {
                             if (!in_array($row_d[0],$arr_heat)){ 
                                $arr_heat[] = $row_d[0] ;
                             }
                         }
                    
              }  
        }   
    
        $sql = "SELECT 
                  ss.xSerienstart "
                  . $field ."
                  , if (ss.Rang = 0, 9999999, ss.Rang) as orderRang
                  , LPAD(s.Bezeichnung,5,'0') as heatid
                  , s.MaxAthlet
                  , s.xSerie                   
            FROM runde AS r
                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                LEFT JOIN serienstart AS ss  ON (ss.xSerie = s.xSerie)
                LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)                                                      
                LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt
                ON rt.xRundentyp = r.xRundentyp
                LEFT JOIN anlage AS an
                ON an.xAnlage = s.xAnlage
            WHERE r.xRunde = " . $round ."                 
            ORDER BY heatid, orderRang";             
        
        $res = mysql_query($sql);    
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        $sameRank = false;
        $z = 0;
        while ($row = mysql_fetch_row($res)) {
            if (in_array($row[0],$arr_heat)){
                continue;
            }
            if ($z == 0){
                $maxAthlete = $row[4];
                $xSerie = $row[5];   
            }
            $heatStarts[] = $row[0];
            if ($row[2] < 9999999){
                $heatStartsRank[] = $row[2];  
            } 
            if ($row[2] == $keep_rank){
                if ($row[2] == $cfgEightRank){
                   $sameRank = true;
                }     
            } 
            $z++;
            $keep_rank = $row[2];
        }
        $heatStarts = array_merge($heatStarts, $arr_heat);
        
        if (count($heatStartsRank) < $maxAthlete){
              $i = count($heatStartsRank);    
        }
        else {
              $i = $maxAthlete;   
        }
        if ($sameRank){
            $maxAthlete = $cfgMaxAthlete + 1;
            // update max athlete
            AA_setMaxAthlete($xSerie, $maxAthlete);
            $i = $maxAthlete;   
        }
        else {
             $maxAthlete = $i;   
        }
        $i =  $maxAthlete;
        
        foreach ($heatStarts AS $key => $val){
             if ($i <= 0){
                 $i = $maxAthlete + 1;
             }
             if ($pass == 2) {
                 $sql = "UPDATE serienstart SET Position2 = " . $i ." WHERE xSerienstart = " . $val;  
             }
             elseif ($pass == 3) {  
                  $sql = "UPDATE serienstart SET Position3 = " . $i ." WHERE xSerienstart = " . $val;  
             }
             mysql_query($sql);    
             if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
             } 
             if ($i <= $maxAthlete){
                   $i--;  
             }
             else {
                 $i++;
             }
        }
}  

/**    
     * ranking for decentral before new position
     * ------------------------------------------
     */   
function AA_rankingForNewPosition($round, $pass)
{  
    $presets = AA_results_getPresets($round);    // read GET/POST variables
    
    $eval = AA_results_getEvaluationType($round);
    $combined = AA_checkCombined(0, $round);
    
    mysql_query("LOCK TABLES r READ, s READ, ss READ, runde READ");
    
    // if this is a combined event, rank all rounds togheter
    $roundSQL = "";
    $roundSQL2 = "";
    if($combined){
        $roundSQL = "s.xRunde IN (";
        $roundSQL2 = "s.xRunde IN (";
        $res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$presets['event']);
        while($row_c = mysql_fetch_array($res_c)){
            $roundSQL .= $row_c[0].",";
            $roundSQL2 .= $row_c[0].",";
        }
        $roundSQL = substr($roundSQL,0,-1).")";
        $roundSQL2 = substr($roundSQL2,0,-1).")";
    }else{
        $roundSQL = "s.xRunde = $round";
        $roundSQL2 = "s.xRunde = $round";
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
             WHERE r.xRunde = " . $round;   
                 
    $res = mysql_query($sql);
   
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else {
        $count_athlete = mysql_num_rows($res);     
    }
    
    // evaluate max. nbr of results entered
    $r = 0;
   
    $sql="SELECT 
                COUNT(*),                  
                ru.Versuche, 
                LPAD(s.Bezeichnung,5,'0') as heatid,  
                if (ss.Position2 > 0, if (ss.Position3 > 0, ss.Position3, ss.Position2) , ss.Position ) as posOrder 
                , ss.Position
                , ss.Position2
                , ss.Position3 
                , ss.xSerienstart
                , ss.Rang
                , s.MaxAthlet
                , s.xSerie 
                , r.Leistung                    
          FROM 
                resultat AS r
                LEFT JOIN serienstart AS ss ON ( r.xSerienstart = ss.xSerienstart )
                LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                LEFT JOIN runde AS ru ON (s.xRunde = ru.xRunde) 
          WHERE  " .
                $roundSQL2 ."                       
          GROUP BY r.xSerienstart
          ORDER BY posOrder ";
          
    $result = mysql_query($sql);   
    
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else {
        
                 $z=0;    
                 $arr_attAthlete = array();
                
                 while( $row = mysql_fetch_row($result)){  
                     if ($z == 0){                                // first row                      
                        $maxatt = $row[1]; 
                         $maxAthlete = $row[9];  
                         $xSerie = $row[10];                                                                                                
                     }
                    if ($row[11] != $cfgInvalidResult['DNS']['code']){
                          $arr_attAthlete[] = $row[0]; 
                    }
                   
                    $z++; 
                 }
               
                $maxAthleteAtt = max($arr_attAthlete);
                $minAthleteAtt = min($arr_attAthlete);   
               
                $onlyMaxAthlete = false; 
                if ($count_athlete > $maxAthlete){
                    if ($maxAthleteAtt == $minAthleteAtt && $minAthleteAtt == $cfgAfterAttempts1){
                       $onlyMaxAthlete = true; 
                    }
                    elseif ($maxAthleteAtt > $cfgAfterAttempts1) {
                             $onlyMaxAthlete = true;
                    }
                }
                if ($onlyMaxAthlete && $count_athlete > $maxAthlete){
                    $c = 0;
                    $arr_attAthlete_new = array();
                    foreach ($arr_attAthlete as $key => $val){
                        $c++;
                        $arr_attAthlete_new[] = $val;
                        if ($c >= $maxAthlete ) {
                            break;
                        }
                    }
                    $arr_attAthlete = $arr_attAthlete_new;  
                    $maxAthleteAtt = max($arr_attAthlete);
                    $minAthleteAtt = min($arr_attAthlete);  
                }
                
                if ($count_athlete < $maxAthlete){
                     $maxAthlete = $count_athlete;
                     // update max athlete 
                     AA_setMaxAthlete($xSerie,$maxAthlete);
                 }
                
                if ($pass == 2){
                    $r = 3;
                }
                elseif ($pass == 3){ 
                         $r = 5;   
                }   
                $first_row = false; 
              
                if ($maxAthleteAtt == $minAthleteAtt && $z == $count_athlete){
                            $first_row = true;
                            $maxAthleteAtt++;
                }
                               
                $z=0;
                $att_athlet = 0;
                $fieldFocus = 0;
                $first = true;
                
                if ($pass == 2){
                    $posSQL = "  ss.Position2 > 0 ";
                }
                elseif ($pass == 3){
                    $posSQL = "  ss.Position3 > 0 ";
                }
                else {
                      $posSQL = "";
                }
                
                if (!empty($posSQL) && !empty($roundSQL2)) {
                    $roundSQL2 = " AND " . $roundSQL2;
                }
                 $sql="SELECT 
                        COUNT(*),                  
                        ru.Versuche, 
                        LPAD(s.Bezeichnung,5,'0') as heatid,  
                        if (ss.Position2 > 0, if (ss.Position3 > 0, ss.Position3, ss.Position2) , ss.Position ) as posOrder 
                        , ss.Position
                        , ss.Position2
                        , ss.Position3                      
                  FROM 
                        resultat AS r
                        LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                        LEFT JOIN runde AS ru ON (s.xRunde = ru.xRunde)  
                  WHERE " .  
                        $posSQL .                      
                        $roundSQL2 ."                       
                  GROUP BY r.xSerienstart
                  ORDER BY posOrder ";
                  
                  $result = mysql_query($sql);   
              
                  if (mysql_num_rows($result) < $maxAthlete) {
                      $maxAthlete = mysql_num_rows($result);
                  }
                  
                while( $row = mysql_fetch_row($result)){
                     $z++;               
                     $att_athlet =  $row[0];   
                     if ($row[0] < $maxAthleteAtt && !$first_row){
                            break;
                     }
                     elseif ($row[0] < $maxAthleteAtt){
                             $fieldFocus =  $att_athlet+1; 
                              break;
                     }
                     else {
                            $fieldFocus = ($z * ($maxatt + 1) ) + $att_athlet; 
                     }
                     if ($onlyMaxAthlete && $z >= $maxAthlete) {
                         break;
                     }
                }  
                if ($fieldFocus == 0){
                     $fieldFocus = 1;              
                }
                else {
                    if ($onlyMaxAthlete){
                        if ($z == $maxAthlete && $att_athlet == $maxAthleteAtt){
                            $z = 1;
                            $att_athlet++;
                            $fieldFocus = $att_athlet;   
                        }
                    }
                    else{     
                    
                        if ($z == $count_athlete && $att_athlet == $maxAthleteAtt){
                            $z = 1;
                            $att_athlet++;
                            $fieldFocus = $att_athlet;   
                        }
                    }
                }    
          
         mysql_free_result($result);
    }

    if($r > 0)        // any results found
    {
        mysql_query("DROP TABLE IF EXISTS tempresult1");    // temporary table
         mysql_query("DROP TABLE IF EXISTS tempresult2");    // temporary table    

        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            mysql_query("
                LOCK TABLES
                    resultat as r READ
                    , serie as s READ   
                    , serienstart as ss WRITE
                    , tempresult1 WRITE
                    , tempresult2 WRITE 
            ");

            // Set up a temporary table to hold all results for ranking.
            // The number of result columns varies according to the maximum
            // number of results per athlete.
            $qry = "
                CREATE TABLE tempresult1 (
                    xSerienstart int(11)
                    , xSerie int(11)";

            for($i=1; $i <= $r; $i++) {
                $qry = $qry . ", Res" . $i . " int(9) default '0'";
                $qry = $qry . ", Wind" . $i . " char(5) default '0'";
            }
            $qry = $qry . ") ENGINE=HEAP";
          
            mysql_query($qry);    // create temporary table

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {  
                $result = mysql_query("
                    SELECT
                        r.Leistung
                        , r.Info
                        , ss.xSerienstart
                        , ss.xSerie
                    FROM
                        resultat as r
                        LEFT JOIN serienstart  as ss ON(r.xSerienstart = ss.xSerienstart )
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)  
                    WHERE                      
                    $roundSQL
                    AND r.Leistung >= 0
                    ORDER BY
                        ss.xSerienstart
                        , r.xResultat
                       
                ");
               
                $sql_temp = "CREATE TABLE tempresult2 (
                                    Leistung int(9)
                                    , Info char(5)
                                     , xSerienstart int(11)  
                                      , xSerie int(11)  )";
                 mysql_query($sql_temp);
                 if(mysql_errno() > 0) {        // DB error
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                 }
                 $i = 0;  
                 while($row = mysql_fetch_row($result)) { 
                     
                        if($ss != $row[2] ){     // next athlete
                             $i = 0;
                        }
                        if ($i < $r){
                             $ins = "INSERT INTO tempresult2 VALUES($row[0],'$row[1]', $row[2],$row[3])"; 
                             mysql_query($ins);               
                             if(mysql_errno() > 0) {        // DB error                                 
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());   
                             }  
                        }                             
                        $ss = $row[2];                // keep athlete's ID
                        $i++;                                // count nbr of results
                    
                 }
                 
                 $sql = "SELECT * FROM tempresult2 
                        ORDER BY
                                xSerienstart
                                , Leistung DESC";
                                 
                $result = mysql_query($sql);      
                 
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
                        if($ss != $row[2] )     // next athlete
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
                            $qry = "INSERT INTO tempresult1 VALUES($row[2],$row[3]";
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
                            tempresult1
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
                            tempresult1
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
                        for($i=0; $i <= $r; $i++) {
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

                mysql_query("DROP TABLE IF EXISTS tempresult1");
                mysql_query("DROP TABLE IF EXISTS tempresult2"); 

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
            }    // ET DB error (create temp table)

            mysql_query("UNLOCK TABLES");
        }    // ET DB error (drop temp table)
    }    // ET any results found
    
    AA_results_setNotStarted($round);    // update athletes with no result  
   
} 

/**    
     * set new max athlete (tech. disciplines for decentral with ranking)
     * ------------------------------------------------------------------
     */      

function AA_setMaxAthlete($xSerie, $maxAthlete){
    
    $sql = "Update serie Set MaxAthlet = " .$maxAthlete . " WHERE xSerie = " . $xSerie;
    mysql_query($sql);
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }
    
} 

/**    
     * reset position 2 or position 3 (tech. disciplines for decentral with ranking)
     * -----------------------------------------------------------------------------
     */      

function AA_resetPos($heat, $posNr){   
 
    $posName = "Position" . $posNr;      
    
    $sql = "UPDATE serienstart SET " . $posName ." = 0 WHERE xSerie = " . $heat;  
    $res = mysql_query($sql);  
    
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }           
} 

/**    
     * set current athlete --> needed by regie (high disciplines for decentral with ranking)
     * -----------------------------------------------------------------------------------
     */      

function AA_setCurrAthlete($xSerie, $xSerienstart){
    
    // reset activ athlete if there is one
    $sql = "Update serienstart Set AktivAthlet = 'n' WHERE  xSerie = " . $xSerie . " AND AktivAthlet = 'y'";
    
    $res = mysql_query($sql);
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }
    else {  
            // set activ athlete 
            $sql = "Update serienstart Set AktivAthlet = 'y' WHERE  xSerienstart = " . $xSerienstart;    
            mysql_query($sql);
            if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
            }   
    }    
}

/**    
     * get current athlete --> needed by regie (high disciplines for decentral with ranking)
     * -----------------------------------------------------------------------------------
     */      

function AA_getCurrAthlete($xSerie){
    
    $heatStart = 0;
    $sql = "SELECT xSerienstart FROM serienstart WHERE  xSerie = " . $xSerie . " AND AktivAthlet = 'y'";
    
    $res = mysql_query($sql);
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }
    else {  
         if (mysql_num_rows($res) >  0){
             $row = mysql_fetch_row($res);
             $heatStart =  $row[0];  
         }
    } 
    return $heatStart;                          // no activ athlete
}


function AA_checkRelayDisc($disc){
    
     $sql = "SELECT * FROM disziplin_" . $_COOKIE['language'] . " AS d 
             WHERE d.Code = " .  $disc . " AND d.Typ = 3 AND d.Strecke > 0";
             
     $res = mysql_query($sql);
     if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }
    else {  
         if (mysql_num_rows($res) > 0) {
             return true;
         }
         else {
                return false;
         }
    }
}



/**    
     * set status changed --> needed by live results
     * ---------------------------------------------
     */      

function AA_StatusChanged($xResultat, $xSerie , $xSerienstart, $runde = 0){
    
    if ($runde == 0) {
        if ($xResultat > 0){
                $sql = "SELECT 
                            r.xRunde
                        FROM 
                            resultat as re
                            LEFT JOIN serienstart as ss ON (re.xSerienstart = ss.xSerienstart)
                            LEFT JOIN serie as s ON (s.xSerie = ss.xSerie)
                            LEFT JOIN runde as r ON (r.xRunde = s.xRunde)
                        WHERE  re.xResultat = " . $xResultat;
        }
        elseif ($xSerie > 0) {
              $sql = "SELECT 
                        r.xRunde
                    FROM  
                        serie as s 
                        LEFT JOIN runde as r ON (r.xRunde = s.xRunde)
                WHERE  s.xSerie = " . $xSerie;
        }
        else {
            $sql = "SELECT 
                        r.xRunde
                    FROM  
                        serienstart as ss 
                        LEFT JOIN serie as s ON (s.xSerie = ss.xSerie)
                        LEFT JOIN runde as r ON (r.xRunde = s.xRunde)
                WHERE  ss.xSerienstart = " . $xSerienstart;
        }
       
        $res = mysql_query($sql);
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
        }
        else {  
             if (mysql_num_rows($res) >  0){
                 $row = mysql_fetch_row($res);
                 $runde = $row[0];
                 
             }
        }
    }
    
    $mRounds= AA_getMergedRounds($runde);
    
    if (empty($mRounds)){
    $sqlRound = "= ". $runde;      
        }
        else {
             $sqlRound = "IN ". $mRounds;   
        }
    
     $sql = "UPDATE runde SET StatusChanged = 'y' WHERE xRunde " . $sqlRound;
     
     $res = mysql_query($sql);
     if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
     }
             
   return $runde; 
}

/**    
     * set status changed --> needed by live results
     * ---------------------------------------------
     */      

function AA_UpdateStatusChanged($round){
    
    $sql = "UPDATE runde SET StatusChanged = 'n' WHERE xRunde = " . $round;
             
    $res = mysql_query($sql);
    if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }
    
    $sql = "UPDATE meeting SET StatusChanged = 'n' WHERE xMeeting = " .  $_COOKIE['meeting_id'];              
             
    $res = mysql_query($sql);
    if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }
    
}


 /**    
     * set status changed --> needed by live results
     * ---------------------------------------------
     */      

function AA_getEvent($round){
    
    $event = 0;
    $sql = "SELECT 
                w.xWettkampf  
            FROM 
                wettkampf as w
                LEFT JOIN runde as r ON (w.xWettkampf = r.xWettkampf)
            WHERE r.xRunde = " . $round;
    
    $res = mysql_query($sql);
    if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }  
      
    if (mysql_num_rows($res) > 0){
          $row = mysql_fetch_row($res);  
          $event = $row[0]; 
    }    
   
   return $event;

}    

/**    
     * check event for tech discipline
     * -------------------------------
     */      

function AA_checkEventDisc($event){
    global $cfgDisciplineType, $strDiscTypeJump, $strDiscTypeJumpNoWind, $strDiscTypeHigh, $strDiscTypeThrow;
    
    $techDisc = false;
    $sql = "SELECT 
                d.Typ
            FROM 
                wettkampf as w
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (w.xDisziplin = d.xDisziplin)     
            WHERE 
                (d.Typ = ". $cfgDisciplineType[$strDiscTypeJump] . " || d.Typ = ". $cfgDisciplineType[$strDiscTypeJumpNoWind] . " || d.Typ = ". $cfgDisciplineType[$strDiscTypeHigh] ." || d.Typ = ". $cfgDisciplineType[$strDiscTypeThrow]. ") 
                AND w.xWettkampf = " . $event;
    
    $res = mysql_query($sql);
   
    if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());      
    }  
      
    if (mysql_num_rows($res) > 0){
          $techDisc = true;
    }    
   
   return $techDisc;

}   

/**
     * Teamsm selection drop down
     *    query arg. 1 = action
     *    query arg. 2 = category ID
     *    query arg. 3 = event ID
     *
     */
    function AA_printTeamsmSelection($action, $category, $event, $club)
    {
?>
<form action='<?php echo $action; ?>' method='post' name='teamsm_selection'>
    <input name='arg' type='hidden' value='select_teamsm' />
    <input name='category' type='hidden' value='<?php echo $category; ?>' />
    <input name='club' type='hidden' value='<?php echo $club; ?>' />
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
<?php
        $dd = new GUI_EventDropDown($category, $event, 'document.teamsm_selection.submit()', false);
?>
        </tr>
    </table>
</form>
<?php
    } 
    
    /**
     * Group selection Teamsm drop down
     *    query arg. 1 = action
     *    query arg. 2 = category ID
     *    query arg. 3 = event ID
     *
     */
    function AA_printGroupSelection($action, $category, $event, $club, $group)
    {
?>
<form action='<?php echo $action; ?>' method='post' name='group_selection'>
    <input name='arg' type='hidden' value='select_group' />
    <input name='category' type='hidden' value='<?php echo $category; ?>' />
    <input name='club' type='hidden' value='<?php echo $club; ?>' />
     <input name='event' type='hidden' value='<?php echo $event; ?>' /> 
    <input name='group' type='hidden' value='<?php echo $group; ?>' />  
    <table>
        <tr>
            <th class='dialog'><?php echo $GLOBALS['strGroup']; ?></th>
<?php
        $dd = new GUI_GroupDropDown($category, $event, 'document.group_selection.submit()', $group);
?>
        </tr>
    </table>
</form>
<?php
    } 
    
    function AA_formatResultTimePalmares($time) 
    {
        list($hour,$min,$sec,$z) = split(':', $time);
        if ($hour > 0){
            $time_new =  trim($hour,'0') .":";
        }
        if ($min > 0){
            if ($hour > 0){
                $time_new .=  $min .":";   
                
            }
            else {
                  $time_new .=  trim($min,'0') .":";  
            }
        }
        if ($sec > 0){
            if ($min > 0){
                  $time_new .=  $sec .":";   
            }
            else {
                  $time_new .=  trim($sec,'0') .":";   
            }
           
        }
         $time_new .=  $z; 
         return $time_new;
    }
    
} // end AA_COMMON_LIB_INCLUDED
?>
