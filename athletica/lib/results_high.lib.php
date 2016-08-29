<?php

/**********
 *
 *    high jump, pole vault results
 *    
 */

if (!defined('AA_RESULTS_HIGH_LIB_INCLUDED'))
{
    define('AA_RESULTS_HIGH_LIB_INCLUDED', 1);

function AA_results_High($round, $layout, $singleRound)
{     
       $zaehler = 0;  
       $max = 0;  
       foreach ($_POST as $key => $val){   
          
           $arr_key = explode('_', $key);
           if ($arr_key[0] == 'hight'){
                $max = $arr_key[1];  
           }
       }
       if ($max > 0){
            $max++;   
       }
       else {
           $max = $GLOBALS['cfgCountHeight'];
       }
       
                 
require('./lib/cl_gui_button.lib.php');

require('./config.inc.php');
require('./lib/common.lib.php');
require('./lib/heats.lib.php');
require('./lib/results.lib.php');
require('./lib/utils.lib.php');
require('./lib/cl_performance.lib.php');   


$presets = AA_results_getPresets($round);    // read GET/POST variables

$performance = 0;        // initialize    

$svm = AA_checkSVM(0, $round); // decide whether to show club or team name

$teamsm = AA_checkTeamSM(0, $round); 

$prog_mode = AA_results_getProgramMode();   


if ($singleRound > 0){    
    $single_svm = AA_checkSVM(0, $singleRound); // decide whether to show club or team name      
}
       

$click = true;           // true = User clicks at this athlete      false = user save athlete before     
//
// update result(s)
//



if($_POST['arg'] == 'save_res')
{   $click = false;

    // check if athlet valid
    if(AA_checkReference("serienstart", "xSerienstart", $_POST['start']) == 0)
    {
        AA_printErrorMsg($strErrAthleteNotInHeat);
    }
    else
    {
        AA_utils_changeRoundStatus($round, $cfgRoundStatus['results_in_progress']);
        if(!empty($GLOBALS['AA_ERROR'])) {
            AA_printErrorMsg($GLOBALS['AA_ERROR']);
        }
       
        mysql_query("
            LOCK TABLES
                disziplin_de READ
                , disziplin_fr READ
                , disziplin_it READ
                , disziplin_de as d READ
                , disziplin_fr as d READ
                , disziplin_it as d READ
                , runde READ
                , runde AS r READ 
                , serienstart WRITE
                , wettkampf READ
                , wettkampf AS w READ 
                , resultat WRITE
                , wertungstabelle READ
                , wertungstabelle_punkte READ
                , meeting READ
        ");
       
        // validate result
        if ($_POST['attempts'] < 0) {
            $perf = new PerformanceAttempt($_POST['attempts']); 
            $performance = $perf->getPerformance();
        }
        else {
             $perf = new PerformanceAttempt($_POST['perf']);
             $performance = $perf->getPerformance();
        }
       

        // validate attempts
        if($performance > 0) {
            $info = strtoupper($_POST['attempts']);
            $info = strtr($info, '0', 'O');
            $info = str_replace("OOO", "O", $info);
            $info = str_replace("OO", "O", $info);
            if(in_array($info, $cfgResultsHigh) == false) {
                $info = NULL;
            }
        }
        else {                // negative or zero result
            $info = $cfgResultsHighOut;
        }
        
        // check on failed attempts (not more than 3 X in a row, it doesent matter on which hights)
        $res = mysql_query("SELECT Leistung, Info FROM 
                    resultat
                WHERE
                    xSerienstart = ".$_POST['start']."
                ORDER BY
                    Leistung ASC");
        $Xcount = 0;
        while($row = mysql_fetch_array($res)){
            if(strpos($row[1], strtoupper("o")) === false){
                preg_match_all("[X]", $row[1], $m);
                $Xcount += count($m[0]);
            }else{
                $Xcount = 0;
            }
        }
        if(strpos($info, strtoupper("o")) === false){ // count X for last entered attempt
            preg_match_all("[X]", $info, $m);
            $Xcount += count($m[0]);
        }else{
            $Xcount = 0;
        }
                
        if($info == $cfgResultsHighOut || $Xcount >= 3) {        // last attempt
            if($cfgProgramMode[$prog_mode]['name'] == $strProgramModeBackoffice) {
               $_POST['athlete'] = $_POST['athlete'] + 1;    // next athlete    
            }   
            
            $points = 0;
        }
        else {
            /*$sql_sex = "SELECT Geschlecht 
                          FROM athlet 
                     LEFT JOIN anmeldung USING(xAthlet) 
                     LEFT JOIN start USING(xAnmeldung) 
                     LEFT JOIN serienstart USING(xStart) 
                         WHERE xSerienstart = ".$_POST['start'].";";*/
            $sql_sex = "SELECT Geschlecht 
                          FROM kategorie 
                     LEFT JOIN wettkampf USING(xKategorie) 
                     LEFT JOIN start USING(xWettkampf) 
                     LEFT JOIN serienstart USING(xStart) 
                         WHERE xSerienstart = ".$_POST['start'].";";
            $query_sex = mysql_query($sql_sex);
            
            if($_POST['attempts']== '-' ){
                $points=0;
            }
            else{
                if ($single_svm) {
                      $single_presets = AA_results_getPresets($singleRound);    // read GET/POST variables
                      $points = AA_utils_calcPoints($single_presets['event'], $performance, 0, mysql_result($query_sex, 0, 'Geschlecht'));   
                }
                else {
                   $points = AA_utils_calcPoints($presets['event'], $performance, 0, mysql_result($query_sex, 0, 'Geschlecht'));  
                }
                
            }
           
        }
        if (!empty($info)) {
                AA_results_update($performance, $info, $points);  
        }
        else {
            $_POST['athlete'] --;
        }
       
    }    // ET Athlete valid
    mysql_query("UNLOCK TABLES");
    
    
    // set ranks after every new result in mode = decentral with ranking
    // ******************************************************************
    
    if ($prog_mode == 2){    
        
        $eval = AA_results_getEvaluationType($round);
        $combined = AA_checkCombined(0, $round);
        
       mysql_query("DROP TABLE IF EXISTS tempresult");    // temporary table

        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            mysql_query("
                LOCK TABLES
                    serie READ
                    , wettkampf READ
                    , resultat WRITE
                    , serienstart WRITE
                    , tempresult WRITE
            ");
            
            // clean ranks, set all to 0
            mysql_query("UPDATE 
                    serienstart
                    , serie
                SET
                    serienstart.Rang = 0
                WHERE
                    serienstart.xSerie = serie.xSerie
                AND    serie.xRunde = $round");
            
            // Set up a temporary table to hold all results for ranking.
            mysql_query("
                CREATE TABLE tempresult (
                    xSerienstart int(11)
                    , xSerie int(11)
                    , Leistung int(9)
                    , TopX int(1)
                    , TotalX int(2)
                    )
                    ENGINE=HEAP 
            ");
            
            
            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {
                // if this is a combined event, rank all rounds togheter
                $roundSQL = "";                       
                if($combined){
                    $roundSQL = " s.xRunde IN (";                             
                    $res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$presets['event']);
                    while($row_c = mysql_fetch_array($res_c)){
                        $roundSQL .= $row_c[0].",";                              
                    }
                    $roundSQL = substr($roundSQL,0,-1).")";                         
                }else{
                    $roundSQL = " s.xRunde = $round";                              
                }
                
                // read all valid results (per athlet)  
                $sql = "
                    SELECT
                        r.Leistung
                        , r.Info
                        , ss.xSerienstart
                        , ss.xSerie
                    FROM
                        resultat AS r
                        LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                        LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie   )
                    WHERE                      
                        $roundSQL
                        AND r.Leistung != 0
                    ORDER BY
                        ss.xSerienstart
                        ,r.Leistung DESC
                ";               
                
                $result = mysql_query($sql);     
                
                if(mysql_errno() > 0)        // DB error
                {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else
                {
                    // initialize variables
                    $leistung = 0;        
                    $serienstart = 0;
                    $serie = 0;
                    $topX = 0;
                    $totX = 0;

                    $ss = 0;        // athlete's ID
                    $tt = FALSE;    // top result check

                    // process every result
                    while($row = mysql_fetch_row($result))
                    {  
                        // new athlete: save last athlete's data
                        if(($ss != $row[2]) && ($ss != 0))
                        {

                            if($leistung != 0)
                            {
                                // add one row per athlete to temp table
                                mysql_query("
                                    INSERT INTO tempresult
                                    VALUES(
                                        $serienstart
                                        , $serie
                                        , $leistung
                                        , $topX
                                        , $totX)
                                ");

                                if(mysql_errno() > 0) {        // DB error
                                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                                }
                            }
                            // initialize variables
                            $leistung = 0;        
                            $serienstart = 0;
                            $serie = 0;
                            $totX = 0;
                            $topX = 0;

                            $tt = FALSE;
                        }

                        // save data of current athlete's top result
                        if(($tt == FALSE) && (strstr($row[1], 'O')))
                        {
                            $leistung = $row[0];        
                            $serienstart = $row[2];
                            $serie = $row[3];
                            $topX = substr_count($row[1], 'X');                         
                            $tt = TRUE;
                        }

                        // count total invalid attempts
                        $totX = $totX + substr_count($row[1], 'X');                     
                        $ss = $row[2];                // keep athlete's ID
                    }
                    mysql_free_result($result);

                    // insert last pending data in temp table
                    if(($ss != 0) && ($leistung != 0)) {
                        mysql_query("
                            INSERT INTO tempresult
                            VALUES(
                                $serienstart
                                , $serie
                                , $leistung
                                , $topX
                                , $totX)
                        ");
                          
                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                    }
                }

                if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
                    $order = "xSerie ,";
                }
                else {    // default: rank results from all heats together
                    $order = "";
                }

                // Read rows from temporary table ordered by performance,
                // nbr of invalid attempts for top performance and
                // total nbr of invalid attempts to determine ranking.
                $result = mysql_query("
                    SELECT
                        xSerienstart
                        , xSerie
                        , Leistung
                        , TopX
                        , TotalX
                    FROM
                        tempresult
                    ORDER BY
                        $order
                        Leistung DESC
                        ,TopX ASC
                        ,TotalX ASC
                ");

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {
                    // initialize variables
                    $heat = 0;
                    $perf = 0;
                    $topX = 0;
                    $totalX = 0;
                    $i = 0;
                    $rank = 0;
                    // set rank for every athlete
                    while($row = mysql_fetch_row($result))
                    {
                        if(($eval == $cfgEvalType[$strEvalTypeHeat])    // new heat
                            &&($heat != $row[1]))
                        {
                            $i = 0;        // restart ranking
                            $perf = 0;
                            $topX = 0;
                            $totalX = 0;
                        }

                        $j++;                                // increment ranking
                        if($perf != $row[2] || $topX != $row[3] || $totalX != $row[4])
                        {
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
                        $perf = $row[2];
                        $topX = $row[3];
                        $totalX = $row[4];
                    }
                    mysql_free_result($result);
                }

                mysql_query("DROP TABLE IF EXISTS tempresult");

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
            }    // ET DB error (create temp table)
               
            mysql_query("UNLOCK TABLES");
        }    // ET DB error (drop temp table)

        AA_results_setNotStarted($round);    // update athletes with no result   
      
        if(!empty($GLOBALS['AA_ERROR'])) {
            AA_printErrorMsg($GLOBALS['AA_ERROR']);
        }  
    }   // end prog mode = 2 (decentral with ranking)
   
}  
//    
// save changed heights  (only prog mode = 2 possible)
//
elseif ($_POST['arg'] == 'save_height'){
    
         $max = $_POST['max'];
         
         $arr_prev = array();
         $arr_new = array();
         
         for ($g=0;$g<$max;$g++) { 
              $name = "hight_" . $g;
              $hiddenName = "hiddenHeight_" . $g;    
              $previous_height = $_POST[$hiddenName];
              $previous_height = new PerformanceAttempt($previous_height);
              $previous_height = $previous_height->getPerformance(); 
              if (isset($_POST[$name])){ 
                  $new_height = $_POST[$name];  
                  $new_height = new PerformanceAttempt($new_height);
                  $new_height = $new_height->getPerformance();  
              } 
              else {
                    $new_height = $previous_height;
              }
              $arr_prev[$g] = $previous_height;
              $arr_new[$g] = $new_height;    
         }
         
         $diff = 0;
         $first_change = false;
         foreach ($arr_prev as $key => $val){
             if ($first_change){
                  $arr_new[$key] = $arr_new[$key-1]  + $diff;
             }
             else {
                 if ($val == $arr_new[$key]) {
                     if ($diff > 0){ 
                        $arr_new[$key] = $arr_new[$key-1]  + $diff;    
                     }
                 }
                 else {
                      if ($diff == 0){
                          if ($key == 0) {
                             $first_change = true;
                             if ($_POST['disCode'] == '310'){                      // jump
                                        $diff = $cfgHeightDiffJump * 100;
                                   }  
                                   elseif ($_POST['disCode'] == '320'){
                                          $diff = $cfgHeightDiffPole * 100;      // pole 
                                   }
                                   else {                 
                                         $diff = $cfgHeightDiffJump * 100; 
                                   }      
                          }
                          else {
                                $diff = $arr_new[$key] - $keep_val;
                          }
                      }
                      else {
                           $arr_new[$key] = $arr_prev[$key]  + $diff;
                      }
                      
                 }
             }
             $keep_val = $val;
         }
         
         AA_delHeight($_POST['round'], $_POST['heat']);  
         
         AA_setHeight($arr_new,  $_POST['round'], $_POST['heat']); 
         
         $name = "hight_" . $max;    
         if (isset($_POST[$name]) && !empty($_POST[$name]) ){    
         
             $new_height = $_POST[$name];  
             $new_height = new PerformanceAttempt($new_height);
             $new_height = $new_height->getPerformance();   
             
             // insert new hight
             $sql = "INSERT INTO hoehe SET 
                                    hoehe = " . $new_height .",
                                    xRunde = " . $_POST['round'] .",   
                                    xSerie = " . $_POST['heat'];  
                                                                   
             $res = mysql_query($sql);      
             if (mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
             }
         }    
} 
//
// delete technical result
//
else if($_GET['arg'] == 'delete')
{
    AA_results_delete($round, $_GET['item']);
}

//
// terminate result processing
//
if($_GET['arg'] == 'results_done')       
{
    $eval = AA_results_getEvaluationType($round);
    $combined = AA_checkCombined(0, $round);
    
    mysql_query("DROP TABLE IF EXISTS tempresult");    // temporary table

    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else
    {
        mysql_query("
            LOCK TABLES
                serie READ
                , wettkampf READ
                , resultat WRITE
                , serienstart WRITE
                , tempresult WRITE
        ");
        
        // clean ranks, set all to 0
        mysql_query("UPDATE 
                serienstart
                , serie
            SET
                serienstart.Rang = 0
            WHERE
                serienstart.xSerie = serie.xSerie
            AND    serie.xRunde = $round");
        
        // Set up a temporary table to hold all results for ranking.
        mysql_query("
            CREATE TABLE tempresult (
                xSerienstart int(11)
                , xSerie int(11)
                , Leistung int(9)
                , TopX int(1)
                , TotalX int(2)
                )
               ENGINE=HEAP
        ");
         
        
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            // if this is a combined event, rank all rounds togheter
            $roundSQL = "";                   
            if($combined){
                $roundSQL = " s.xRunde IN (";                            
                $res_c = mysql_query("SELECT xRunde FROM runde WHERE xWettkampf = ".$presets['event']);
                while($row_c = mysql_fetch_array($res_c)){
                    $roundSQL .= $row_c[0].",";                       
                }
                $roundSQL = substr($roundSQL,0,-1).")";                   
            }else{
                $roundSQL = " s.xRunde = $round";                        
            }
            
            // read all valid results (per athlet)     
             $sql = "
                SELECT
                    r.Leistung
                    , r.Info
                    , ss.xSerienstart
                    , ss.xSerie
                FROM
                    resultat AS r
                    LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie)
                WHERE                  
                    $roundSQL
                    AND r.Leistung != 0
                ORDER BY
                    ss.xSerienstart
                    ,r.Leistung DESC
            ";   
            
            $result = mysql_query($sql);  
            
            if(mysql_errno() > 0)        // DB error
            {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else
            {
                // initialize variables
                $leistung = 0;        
                $serienstart = 0;
                $serie = 0;
                $topX = 0;
                $totX = 0;

                $ss = 0;        // athlete's ID
                $tt = FALSE;    // top result check

                // process every result
                while($row = mysql_fetch_row($result))
                {  
                    // new athlete: save last athlete's data
                    if(($ss != $row[2]) && ($ss != 0))
                    {

                        if($leistung != 0)
                        {
                            // add one row per athlete to temp table
                            mysql_query("
                                INSERT INTO tempresult
                                VALUES(
                                    $serienstart
                                    , $serie
                                    , $leistung
                                    , $topX
                                    , $totX)
                            ");

                            if(mysql_errno() > 0) {        // DB error
                                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                            }
                        }
                        // initialize variables
                        $leistung = 0;        
                        $serienstart = 0;
                        $serie = 0;
                        $totX = 0;
                        $topX = 0;

                        $tt = FALSE;
                    }

                    // save data of current athlete's top result
                    if(($tt == FALSE) && (strstr($row[1], 'O')))
                    {
                        $leistung = $row[0];        
                        $serienstart = $row[2];
                        $serie = $row[3];
                        $topX = substr_count($row[1], 'X');                         
                        $tt = TRUE;
                    }

                    // count total invalid attempts
                    if($tt == TRUE) {
                        $totX = $totX + substr_count($row[1], 'X'); 
                    }                    
                    $ss = $row[2];                // keep athlete's ID
                }
                mysql_free_result($result);

                // insert last pending data in temp table
                if(($ss != 0) && ($leistung != 0)) {
                    mysql_query("
                        INSERT INTO tempresult
                        VALUES(
                            $serienstart
                            , $serie
                            , $leistung
                            , $topX
                            , $totX)
                    ");
                      
                    if(mysql_errno() > 0) {        // DB error
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }
                }
            }

            if($eval == $cfgEvalType[$strEvalTypeHeat]) {    // eval per heat
                $order = "xSerie ,";
            }
            else {    // default: rank results from all heats together
                $order = "";
            }

            // Read rows from temporary table ordered by performance,
            // nbr of invalid attempts for top performance and
            // total nbr of invalid attempts to determine ranking.
            $result = mysql_query("
                SELECT
                    xSerienstart
                    , xSerie
                    , Leistung
                    , TopX
                    , TotalX
                FROM
                    tempresult
                ORDER BY
                    $order
                    Leistung DESC
                    ,TopX ASC
                    ,TotalX ASC
            ");
                                               
            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                // initialize variables
                $heat = 0;
                $perf = 0;
                $topX = 0;
                $totalX = 0;
                $i = 0;
                $rank = 0;
                // set rank for every athlete
                while($row = mysql_fetch_row($result))
                {
                    if(($eval == $cfgEvalType[$strEvalTypeHeat])    // new heat
                        &&($heat != $row[1]))
                    {
                        $i = 0;        // restart ranking
                        $perf = 0;
                        $topX = 0;
                        $totalX = 0;
                    }

                    $j++;                                // increment ranking
                    if($perf != $row[2] || $topX != $row[3] || $totalX != $row[4])
                    {
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
                    $perf = $row[2];
                    $topX = $row[3];
                    $totalX = $row[4];
                }
                mysql_free_result($result);
            }

            mysql_query("DROP TABLE IF EXISTS tempresult");

            if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
        }    // ET DB error (create temp table)

        // read all starting athletes with no valid result (rank=0)
        // and add disqualification code      
        $sql = "SELECT DISTINCT
                    ss.xSerienstart
                FROM
                    resultat AS r
                    LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
                    LEFT JOIN serie AS s ON (ss.xSerie = s.xSerie )
                WHERE   
                    ss.Rang = 0
                    AND s.xRunde = " . $round ."
                    AND r.Leistung >= 0";  
         
        $result = mysql_query($sql);      

        if(mysql_errno() > 0)        // DB error
        {
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            // 
            while($row = mysql_fetch_row($result))
            {
                // check if "disqualified" result already there
                $res = mysql_query("
                    SELECT
                        xResultat
                    FROM
                        resultat
                    WHERE xSerienstart = $row[0]
                    AND (Leistung = ". $cfgInvalidResult['DSQ']['code']." OR Leistung = ". $cfgInvalidResult['NRS']['code'] .")" 
                );

                if(mysql_errno() > 0) {        // DB error
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                }
                else {
                    if(mysql_num_rows($res) <= 0)
                    {
                        mysql_query("
                            INSERT INTO
                                resultat
                            SET
                                Leistung = ". $cfgInvalidResult['NRS']['code']."
                                , Info = '$cfgResultsHighOut'
                                , xSerienstart = $row[0]
                        ");

                        if(mysql_errno() > 0) {        // DB error
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                        }
                    }
                    mysql_free_result($res);
                }
            }
            mysql_free_result($result);
        }

        mysql_query("UNLOCK TABLES");
    }    // ET DB error (drop temp table)

    AA_results_setNotStarted($round);    // update athletes with no result      
   
    AA_utils_changeRoundStatus($round, $cfgRoundStatus['results_done']);  
    if(!empty($GLOBALS['AA_ERROR'])) {
        AA_printErrorMsg($GLOBALS['AA_ERROR']);
    }
}
 
//
// calculate ranking points if needed
//
if(($_GET['arg'] == 'results_done')
|| ($_POST['arg'] == 'save_rank') ){
    
    AA_utils_calcRankingPoints($round);
    
}
if ($_POST['arg'] == 'save_remark') {
    
    AA_utils_saveRemark($_POST['item'], $_POST['remark'], $_POST['xAthlete']);
}


//
// print HTML page header
//
AA_results_printHeader($presets['category'], $presets['event'], $round);

$mergedMain=AA_checkMainRound($round);
if ($mergedMain != 1) {

// read round data
if($round > 0 && $prog_mode != 2)
{
    $status = AA_getRoundStatus($round);

    // No action yet
    if(($status == $cfgRoundStatus['open'])
        || ($status == $cfgRoundStatus['enrolement_done'])
        || ($status == $cfgRoundStatus['heats_in_progress']))
    {
        AA_printWarningMsg($strHeatsNotDone);
    }
    // Enrolement pending
    else if($status == $cfgRoundStatus['enrolement_pending'])
    {
        AA_printWarningMsg($strEnrolementNotDone);
    }
    // Heat seeding completed, ready to enter results
    else if($status >= $cfgRoundStatus['heats_done'])
    {
        AA_heats_printNewStart($presets['event'], $round, "event_results.php");

        // display all athletes      
        if ($teamsm){
            $sql = "
                SELECT rt.Name
                    , rt.Typ
                    , s.xSerie
                    , s.Bezeichnung
                    , ss.xSerienstart
                    , ss.Position
                    , ss.Rang
                    , a.Startnummer
                    , at.Name
                    , at.Vorname
                    , at.Jahrgang
                    , t.Name
                    , LPAD(s.Bezeichnung,5,'0') as heatid
                    , rs.xResultat
                    , rs.Leistung
                    , rs.Info
                    , at.Land
                    , ss.Bemerkung
                    , at.xAthlet
                    , r.xRunde 
                    , ss.RundeZusammen  
                FROM
                    runde AS r
                    LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                    LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie   )
                    LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                    LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                    LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                    LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                    INNER JOIN teamsmathlet AS tat ON(st.xAnmeldung = tat.xAnmeldung)
                    LEFT JOIN teamsm as t ON (tat.xTeamsm = t.xTeamsm)                      
                    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                    LEFT JOIN resultat AS rs ON rs.xSerienstart = ss.xSerienstart
                WHERE 
                    r.xRunde = $round              
                ORDER BY
                    heatid
                    , ss.Position
                    , rs.xResultat DESC
            ";        
        } 
        else {
            $sql = "
                SELECT rt.Name
                    , rt.Typ
                    , s.xSerie
                    , s.Bezeichnung
                    , ss.xSerienstart
                    , ss.Position
                    , ss.Rang
                    , a.Startnummer
                    , at.Name
                    , at.Vorname
                    , at.Jahrgang
                    , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))   
                    , LPAD(s.Bezeichnung,5,'0') as heatid
                    , rs.xResultat
                    , rs.Leistung
                    , rs.Info
                    , at.Land
                    , ss.Bemerkung
                    , at.xAthlet
                    , r.xRunde 
                    , ss.RundeZusammen  
                FROM
                    runde AS r
                    LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                    LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie   )
                    LEFT JOIN start AS st ON (st.xStart = ss.xStart)
                    LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                    LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                    LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                    LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                    LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                    LEFT JOIN resultat AS rs ON rs.xSerienstart = ss.xSerienstart
                WHERE 
                    r.xRunde = $round              
                ORDER BY
                    heatid
                    , ss.Position
                    , rs.xResultat DESC
            ";        
        }
        
        
        $result = mysql_query($sql);    
       
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
             AA_results_printMenu($round, $status, $prog_mode, 'high'); 

            // initialize variables
            $a = 0;
            $h = 0;
            $i = 0;
            if(!empty($_GET['athlete'])) {    
                $_POST['athlete'] = $_GET['athlete'];
            }
            if((empty($_POST['athlete']))            // no athlete selected or after
                || (mysql_num_rows($result) < $_POST['athlete'])) // last athlete
            {
                $_POST['athlete'] = 1;            // focus to first athlete
            }
            $rowclass = 'odd';  
           
            if($cfgProgramMode[$prog_mode]['name'] == $strProgramModeBackoffice)
            {
                $focus = 0;        // keep focus on this athlete if Backoffice Mode
            }
            else {
                $focus = 1;        // focus on next athlete if Field Mode
            }
?>
<p/>

<table class='dialog'>
<?php
            



            $btn = new GUI_Button('', '');    // create button object
            while($row = mysql_fetch_row($result))
            {   if ($row[20] > 0){
                    $singleRound = $row[20];
                }
                else {
                      $singleRound = $row[19];  
                }
                
                // terminate last row if new athlete and not first item
                if(($a != $row[4]) && ($i != 0))
                {
                   if($_POST['athlete'] == ($i+1) && $cfgProgramMode[$prog_mode]['name'] == $strProgramModeField) {
                        if ($row[15] == 'XXX' && !$click){
                            $_POST['athlete'] = $_POST['athlete'] + 1;
                        }
                    }        
                     
                    if($_POST['athlete'] == $i)        // active item
                    {  
                        echo "<td>";
                        $btn->set("event_results.php?arg=del_start&item=$a&round=$round", $strDelete);
                        $btn->printButton();
                        echo "</td>";
                    }
                    echo "</tr>";
                }
/*
 *  Heat headerline
 */   
                if($h != $row[2])        // new heat
                {
                    $h = $row[2];                // keep heat ID
                    if(is_null($row[0])) {        // only one round

                        $title = "$strFinalround";
                    }
                    else {        // more than one round
                        $title = "$row[0]";
                    }

                    $c = 0;
                    if($status == $cfgRoundStatus['results_done']) {
                        $c = 1;        // increment colspan to include ranking
                    }
 
?>
    <tr>
        <form action='event_results.php#heat_<?php echo $row[3]; ?>' method='post'
            name='heat_id_<?php echo $h; ?>'>

        <th class='dialog' colspan='<?php echo 7+$c; ?>' />
            <?php echo $title; ?>
            <input type='hidden' name='arg' value='change_heat_name' />
            <input type='hidden' name='round' value='<?php  echo $round; ?>' />
            <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
            <input type='hidden' name='item' value='<?php echo $row[2]; ?>' />
            <input class='nbr' type='text' name='id' maxlength='2'
                value='<?php echo $row[3]; ?>'
                onChange='document.heat_id_<?php echo $h;?>.submit()' />
                <a name='heat_<?php echo $row[3]; ?>' />
        </th> 
        
        </form>
    </tr>  
    <tr>
        <th class='dialog'><?php echo $strPositionShort; ?></th>
        <th class='dialog' colspan='2'><?php echo $strAthlete; ?></th>
        <th class='dialog'><?php echo $strYearShort; ?></th>
        <th class='dialog'><?php echo $strCountry; ?></th>
        <th class='dialog'><?php if($svm){ echo $strTeam; }  elseif ($teamsm){ echo $strTeamsm;} else{ echo $strClub;} ?></th>
<?php
                    if($status == $cfgRoundStatus['results_done'])
                    {
?>
        <th class='dialog'><?php echo $strRank; ?></th>
<?php
                    }
?>
        <th class='dialog' ><?php echo $strResultRemark; ?></th>         
        <th class='dialog' colspan='2'><?php echo $strPerformance; ?></th>  
    </tr>
<?php
                }        // ET new heat

/*
 * Athlete data lines
 */  
                if($a != $row[4])        // new athlete
                {
                    $a = $row[4];        // keep athlete ID
                    $i++;                    // increment athlete counter
                    $l = 0;                // reset result counter
                    
                   if($_POST['athlete'] == $i) {    // active item                    
                        $rowclass='active';
                    }
                    else if($row[5] % 2 == 0) {        // even row numer
                        $rowclass='even';
                    }
                    else {                            // odd row number
                        $rowclass='odd';
                    }

                    if($rowclass == 'active') {
?>
    <tr class='active'>
<?php
                    }
                    else {   
?>
    <tr class='<?php echo $rowclass; ?>'
        onclick='selectAthlete(<?php echo $i; ?>)'>
<?php
                    }  
?>
        <td class='forms_right'><?php echo $row[5]; ?></td>
        <td class='forms_right'><?php echo $row[7]; /* start nbr */ ?></td>
        <td nowrap><?php echo $row[8] . " " . $row[9];  /* name */ ?></td>
        <td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[10]); ?></td>
        <td><?php echo (($row[16]!='' && $row[16]!='-') ? $row[16] : '&nbsp;'); ?></td>
        <td nowrap><?php echo $row[11]; /* club */ ?></td>
   
<?php
   
                    if($status == $cfgRoundStatus['results_done'])
                    {
                        if($_POST['athlete'] == $i)    // only current athlet
                        {
?>
        <form action='event_results.php' method='post'
            name='rank'>
        <td>
            <input type='hidden' name='arg' value='save_rank' />
            <input type='hidden' name='round' value='<?php echo $round; ?>' />
             <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
            <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
            <input type='hidden' name='item' value='<?php echo $row[4]; ?>' />
            <input class='nbr' type='text' name='rank' maxlength='3'
                value='<?php echo $row[6]; ?>' onChange='document.rank.submit()' />
        </td>
        </form>
<?php
                        }
                        else {
                            echo "<td>" . $row[6] . "</td>";
                            
                        }
                        echo "<td>" . $row[17] . "</td>";  
                    }        // ET results done
                    else {
                         ?> 
                        

<form action='event_results.php' method='post'
            name='remark_<?php echo $i; ?>'>
        <td>
            <input type='hidden' name='arg' value='save_remark' />
            <input type='hidden' name='round' value='<?php echo $round; ?>' />
             <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
            <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
            <input type='hidden' name='item' value='<?php echo $row[4]; ?>' />
            <input type='hidden' name='xAthlete' value='<?php echo $row[18]; ?>' />    
            <input class='textshort' type='text' name='remark' maxlength='7'
                value='<?php echo $row[17]; ?>' onchange='document.remark_<?php echo $i; ?>.submit()' />
        </td>
        </form>  
       
  
      
                   <?php  
                     
                    }
                
                }        // ET new athlete
               

                $new_perf = '';
                if($_POST['athlete'] == $i)                // only current athlet
                {
                    if(is_null($row[14])) {        // no result yet: show form
                        $last_perf = 0;
                    }
                    else {
                        $last_perf = $row[14];
                    }

                    $item = '';
                    if($l == 0)                                // first item
                    {
                        // read all performances achieved in current heat and
                        // better than last entered performance
                    if ($cfgProgramMode[$prog_mode]['name'] == $strProgramModeField) {
                        if(in_array($row[15], $cfgResultsHighStayDecentral)) {
                            $new_perf = AA_formatResultMeter($last_perf);
                            $new_info = $row[15];
                            $item = $row[13];
                        }
                        else 
                        {
                            $new_perf = getNextHeight($row[2], $last_perf);
                            $new_info = '';
                        } 
                        
                    }
                    else {
                        if(in_array($row[15], $cfgResultsHighStay)) {
                            $new_perf = AA_formatResultMeter($last_perf);
                            $new_info = $row[15];
                            $item = $row[13];
                        }
                        else 
                        {
                            $new_perf = getNextHeight($row[2], $last_perf);
                            $new_info = '';
                        } 
                    }  
?>
        <form action='event_results.php' method='post'
            name='perf'>
        <td nowrap colspan='2'> 
            <input type='hidden' name='arg' value='save_res' />
            <input type='hidden' name='round' value='<?php echo $round; ?>' />
             <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
            <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
            <input type='hidden' name='start' value='<?php echo $row[4]; ?>' />
             <input type='hidden' name='xSerie' value='<?php echo $row[2]; ?>' />  
            <input type='hidden' name='item' value='<?php echo $item; ?>' />
            <input class='perfheight' type='text' name='perf' maxlength='5'
                value='<?php echo $new_perf; ?>'
                    onChange='checkSubmit(document.perf)' />  
            <input class='texttiny' type='text' name='attempts' maxlength='3'
                value='<?php echo $new_info; ?>'
                    onChange='document.perf.submit()' onBlur='document.perf.submit()' />
        </td>
        </form>   
        
        
                        <?php
                    }

                    if((is_null($row[14]) == false)    // result to display
                        && (empty($item))) {                // next height                         
                        ?>
        <td nowrap>
            <?php echo AA_formatResultMeter($row[14]) . "<br/>( $row[15] )"; ?>
        </td>
        <td>
                        <?php
                        $btn = new GUI_Button("event_results.php?arg=delete&round=$round&item=$row[13]&athlete=$i", "X");
                        $btn->printButton();
                        ?>
        </td>
                        <?php
                    }
                    $l++;
                }
                else if (is_null($row[14]) == false) // result entered
                {
                    echo "<td colspan='2' nowrap>" . AA_formatResultMeter($row[14])
                        . " ( $row[15] )</td>";
                }
              
           
            
            }
            
          
            if($a != 0)
            {
                if($_POST['athlete'] == $i)        // active item
                {
                    echo "<td>";
                    $btn->set("event_results.php?arg=del_start&item=$a&round=$round", $strDelete);
                    $btn->printButton();
                    echo "</td>";
                }
                echo "</tr>";
            }
            ?>
           
            
            
            
</table>
  
                 
            <?php
            mysql_free_result($result);
        }        // ET DB error
    }
}        // ET round selected

//
// only prog mode = 2 (decentral with ranking)
// ********************************************
else if($round > 0 && $prog_mode == 2){        
    
    $status = AA_getRoundStatus($round);
     
    // No action yet
    if(($status == $cfgRoundStatus['open'])
        || ($status == $cfgRoundStatus['enrolement_done'])
        || ($status == $cfgRoundStatus['heats_in_progress']))
    {
        AA_printWarningMsg($strHeatsNotDone);
    }
    // Enrolement pending
    else if($status == $cfgRoundStatus['enrolement_pending'])
    {
        AA_printWarningMsg($strEnrolementNotDone);
    }
    // Heat seeding completed, ready to enter results
    else if($status >= $cfgRoundStatus['heats_done'])
    {   
        AA_heats_printNewStart($presets['event'], $round, "event_results.php");      
        
        $ph = 0;  
        // find max height per athlete        
        $sql_max_h = "SELECT 
                 LPAD(s.Bezeichnung,5,'0') as heatid
                , count(*) 
            FROM
                runde AS r
                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                LEFT JOIN start AS st ON (st.xStart = ss.xStart) 
                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                LEFT JOIN resultat AS rs ON rs.xSerienstart = ss.xSerienstart
            WHERE r.xRunde = $round              
            GROUP BY ss.xSerienstart
            ORDER BY
                heatid
                , ss.Position DESC
                , rs.xResultat";    
                                                   
          $res_max_h = mysql_query($sql_max_h);        
         
          if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
          }
          else { $ph = 0;                                         //  height column
                $starts  = 0;                                          // all started athletes
              while ($row_max_h = mysql_fetch_row($res_max_h)){
                   $starts ++;
                    if ($row_max_h[1] > $ph){
                        $ph = $row_max_h[1]; 
                    }                        
              }
          }
         
         // fill last infos (included previous info by 'XXX') in an array
           $sql_info = "SELECT rt.Name
                , rt.Typ
                , s.xSerie
                , s.Bezeichnung
                , ss.xSerienstart
                , ss.Position
                , ss.Rang
                , a.Startnummer
                , at.Name
                , at.Vorname
                , at.Jahrgang
                , if('".$svm."', t.Name, IF(a.Vereinsinfo = '', v.Name, a.Vereinsinfo))   
                , LPAD(s.Bezeichnung,5,'0') as heatid
                , rs.xResultat
                , rs.Leistung
                , rs.Info
                , at.Land
                , ss.Bemerkung
                , at.xAthlet
                , r.xRunde 
                , ss.RundeZusammen  
            FROM
                runde AS r
                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                LEFT JOIN start AS st ON (st.xStart = ss.xStart) 
                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                LEFT JOIN resultat AS rs ON rs.xSerienstart = ss.xSerienstart  
            WHERE r.xRunde = $round      
              ORDER BY
                heatid
                , ss.Position 
                , rs.xResultat
                
                "; 
         
          $res_info = mysql_query($sql_info);
           if(mysql_errno() > 0) {        // DB error
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
          }
          else {
               $c = 0;
                $arr_info = array();
                $arr_info_remark = array();               // previous info 'p'
                $count_results   = 0;                                
                $count_ends = 0;                          // athletes with results 'XXX'
                while ($row_info = mysql_fetch_row($res_info)){
                
                    if ($row_info[4] != $a && $c > 0){
                        if (!empty($keep_info)){                                  
                             if ($c == $ph){
                                  $arr_info[] = $keep_info; 
                                  $arr_info_remark[] = '';               
                                   $count_results ++;
                             }
                             else {
                                   if ($keep_info == 'XXX'){
                                        $arr_info[] = $keep_info; 
                                        $arr_info_remark[] = 'p';       // previous
                                         $count_results ++;  
                                   }
                                   else {
                                        $arr_info[] = ''; 
                                        $arr_info_remark[] = '';  
                                         $count_results ++; 
                                   }
                             }
                            $c = 0;  
                        }
                    }
                    $c++;    
                    if ($row_info[15] == 'XXX'){
                            $count_ends++;                             
                    }                         
                    $a = $row_info[4];
                    $keep_info = $row_info[15];  
               
               }
               if (!empty($keep_info)){      
                     if ($c == $ph){
                        $arr_info[] = $keep_info; 
                        $arr_info_remark[] = '';   
                         $count_results++;  
                     }
                     else {
                           if ($keep_info == 'XXX'){
                                $arr_info[] = $keep_info; 
                                $arr_info_remark[] = 'p';             // previous
                                 $count_results ++;     
                           }
                           else {
                                $arr_info[] = '';  
                                $arr_info_remark[] = '';
                                 $count_results ++;    
                           }
                     }
               }
          }
        
          // array for infos to jump
          $arr_info_toJump = array();  
          foreach ($arr_info as $key => $val){
              if (!in_array($val,$cfgResultsHighStayDecentralEnd)) {
                    $arr_info_toJump[$key] = $val;
              }               
         }  
         
         // find out new column (= newHight )         
         $sql_hight = "SELECT    
                LPAD(s.Bezeichnung,5,'0') as heatid                 
                , rs.Leistung
                , rs.Info                   
                , d.Code  
            FROM
                runde AS r
                LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                LEFT JOIN serienstart AS ss ON (ss.xSerie = s.xSerie)
                LEFT JOIN start AS st ON (st.xStart = ss.xStart) 
                LEFT JOIN anmeldung AS a ON (a.xAnmeldung = st.xAnmeldung)
                LEFT JOIN athlet AS at ON (at.xAthlet = a.xAthlet)
                LEFT JOIN verein AS v ON (v.xVerein = at.xVerein)
                LEFT JOIN team AS t ON(a.xTeam = t.xTeam) 
                LEFT JOIN rundentyp_" . $_COOKIE['language'] . " AS rt ON rt.xRundentyp = r.xRundentyp
                LEFT JOIN resultat AS rs ON rs.xSerienstart = ss.xSerienstart
                LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf)  
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " AS d ON (d.xDisziplin = w.xDisziplin)   
            WHERE r.xRunde = $round                 
              ORDER BY
                heatid
                , rs.Leistung DESC
                , ss.Position ASC
                ";
         $res_hight = mysql_query($sql_hight);    
         
         $e = 0;                                 // count performance when is finished with this high
         $z = 0;          
         $first = true;   
         $disCode = 0;
         while ($row_hight = mysql_fetch_row($res_hight)){
                if ($perf_keep != $row_hight[1] && !$first){
                       $z++;                          
                       if ($z > 1) {
                          break;   
                       }
                       else {
                           $e_keep = $e;
                           $e = 0;
                       }    
                 }
                 $first = false;    
                 if(in_array($row_hight[2], $cfgResultsHighStayDecentralEnd)) { 
                       if ($row_hight[2] != 'XXX'){
                             $e++;     
                       }                          
                 }  
                 $perf_keep = $row_hight[1];
                 $disCode = $row_hight[3];
         }
                
         $newHight = false;
         if (is_null($e_keep) )   {
                  $e_keep = $e;                    
         }
       
         if ($e_keep == ($starts - $count_ends) ){
              $newHight = true;
         }  
       
         if (empty($_POST)) {
                  if ($ph > $max) {
                       $max = $ph;
                  } 
         }   
            
         if (empty($_POST)) { 
                  $y = 0;
                  while ($row = mysql_fetch_row($res_c_h)){
                         $name = "hight_" .$y;
                         $hiddenName = "hiddeHight_" .$y; 
                         $performance_new = AA_formatResultMeter($row[0]); 
                         $_POST[$name] = $performance_new;
                         $_POST[$hiddenName] = $performance_new;
                         $y++;
                  }
              }
                   
         // found active athlete
         $a_activ = 0;
         $flen = 0;
         $f_len_keep = 0;
         $r_f_len = 0;
         $r_f_len_keep = 0;  
         $a_activ_set = false; 
         $f = 0;                  
         $reset_activ = false;
            
         for ($r=$starts-1;$r>=0;$r--){  
                if ($arr_info[$r] == '' || $arr_info_remark[$r] == 'p'){
                      $f++;     
                }                            
                else {
                    if ($f > 0){
                        $a_activ = $r + 2;
                        for ($j=$a_activ-1;$j<$starts;$j++){
                                if ($arr_info_remark[$a_activ-1] == 'p' ) {
                                     $a_activ++;
                                } 
                        }
                        $a_activ_set = true;
                    } 
                    if  ($a_activ > $starts){
                            $a_activ_set = false;
                            $reset_activ = true;
                    }                       
                    break;
                }
         }  
         if (!$a_activ_set) { 
             if ($newHight){  
                   
                  for ($r=0;$r<$starts;$r++){     
                        if ($arr_info[$r] != 'XXX' ) {
                               $a_activ = $r + 1; 
                              $a_activ_set = true;
                              break;                                
                        }     
                        
                  }
                  if (!$a_activ_set){
                      $a_activ = 1;
                  }  
                 
             }
             elseif ($reset_activ){  
                 
                    $val_keep = '';   
                   
                    foreach ($arr_info_toJump as $key => $val) { 
                            if (strlen($val) == 1){
                                $arr_info_toJump_1[$key] = $val;
                            }
                            elseif (strlen($val) == 2){ 
                                    $arr_info_toJump_2[$key] = $val; 
                           }
                    }
                    
                    if (count($arr_info_toJump_1) > 0){
                           foreach ($arr_info_toJump_1 as $key => $val) { 
                                 $a_activ = $key + 1;
                                 break;
                           }
                    }
                    else {
                           foreach ($arr_info_toJump_2 as $key => $val) { 
                                 $a_activ = $key + 1;
                                 break;
                           }
                    }    
             }
             else {     
                    for ($r=$starts-1;$r>=0;$r--){     
                                    if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                         $athleteValidFirst = false;
                                    }
                                    else {  
                                       
                                            $f_len = strlen($arr_info[$r]);
                                            $r_f_len = $r;
                                            if ($f_len_keep > 0){
                                                if ($f_len_keep < $f_len) {
                                                    $a_activ   = $r_f_len_keep + 1;
                                                    break;
                                                               
                                                }
                                                elseif  ($f_len_keep = $f_len) {
                                                        $a_activ   = $r_f_len + 1;                                                                          
                                                }   
                                                elseif  ($f_len_keep > $f_len) {                                                                      
                                                        break; 
                                                }   
                                            }
                                            else {
                                                    $a_activ   = $r_f_len + 1;                                                               
                                            } 
                                            $f_len_keep = $f_len; 
                                            $r_f_len_keep = $r_f_len;    
                                               
                                    }   
                      }
             }
         }
         
       
         if (is_null($_POST['athlete'])) {
             $_POST['athlete'] = $a_activ;
         }  
         
         $aClick = false;
         if (isset($_POST['result'])){           // this means that user makes a click to a athlete           
            $aClick = true;  
         }   
       
        if (isset($_GET['arg']) && $_GET['arg'] == 'delete') {
              $aClick = true;  
        }                                          
           
         $athleteValidAfter = true;                                         
         if ($_POST['athlete'] >= 1 && !$aClick){                         
                  
                   $athleteValidAfter = true;  
                   if ($starts != $count_ends){
                       if ( count($arr_info) == $starts){     
                             
                            for ($r=$starts-1;$r>=0;$r--){  
                                if ($r < $_POST['athlete'] - 1 ){
                                    break;
                                }                           
                                if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                     $athleteValidAfter = false;
                                }
                                else {
                                    $athleteValidAfter = true; 
                                    break;
                                }                            
                           }
                           if ($_POST['athlete'] > $starts){
                                 $athleteValidAfter = false;
                           }       
                       }
                   }
                   else {
                         $athleteValidAfter = false;  
                         if (!$aClick){
                             $_POST['athlete'] = 1;
                             $a_activ = 1;
                         }
                   }
                  
                   if (!$athleteValidAfter && !$aClick && !$newHight) {                       // set to first athlete when no valid athletes after        
                       $_POST['athlete'] = 1;
                   }
                   
             
         }                  
         
         // find out first valid athlete
         $athleteValidFirst = true; 
         $a_first = 0;                                        
         if ($_POST['athlete'] >= 1 && !$aClick){                          
                  
                   $athleteValidFirst = true;  
                   if ($starts != $count_ends){ 
                       if ( count($arr_info) == $starts){     
                            
                            for ($r=0;$r<$starts;$r++){  
                               
                                if ($arr_info[$r] == 'XXX'){
                                     $athleteValidFirst = false;
                                }
                                else {
                                    $athleteValidFirst = true; 
                                    $a_first = $r + 1;
                                    break;
                                }                            
                           }                         
                       } 
                   }
                   else {
                          $athleteValidAfter = false;
                          if (!$aClick){ 
                              $_POST['athlete'] = 1;
                              $a_activ = 1; 
                          }  
                   } 
                  
                   if ($a_first > 0 && !$athleteValidAfter && !$click) { 
                       $_POST['athlete'] = $a_first;
                   }
                   
             
         }  
      
        if (isset($_POST['result']) || $aClick){           // this means that user makes a click to a athlete
            $a_activ = $_POST['athlete'];               
        }    
       
        if ($starts == $count_ends && !$aClick){
            $_POST['athlete'] = 1;
            $a_activ = 1;
        }      
       
             
        if(mysql_errno() > 0) {        // DB error
            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }
        else
        {
            AA_results_printMenu($round, $status, $prog_mode, 'high');

            // initialize variables
            $a = 0;
            $h = 0;
            $i = 0; 
            if(!empty($_GET['athlete'])) {    
                $_POST['athlete'] = $_GET['athlete'];
            }
            if((empty($_POST['athlete']))            // no athlete selected or after
                || ($starts < $_POST['athlete'])) // last athlete
            {
                $_POST['athlete'] = 1;            // focus to first athlete
               
            }
            $rowclass = 'odd';             
           
            if($cfgProgramMode[$prog_mode]['name'] == $strProgramModeBackoffice)
            {
                $focus = 0;        // keep focus on this athlete if Backoffice Mode
            }
            else {
                $focus = 1;        // focus on next athlete if Field Mode
            }
?>
<p/>

<table class='dialog'>
<?php
            
            $l = 0;
            $newAthl = false;
            $flagField = false;  
            $skip = false; 
            $s = 0;
            $s_keep = 0;  
            $k = 0;  
            $p = 0;      // all pass   
           
            $btn = new GUI_Button('', '');    // create button object
            
            $flagField = false;
              $res_info = mysql_query($sql_info); 
         
            while($row = mysql_fetch_row($res_info))
            
            {  
                if(($a != $row[4]) && ($i != 0)) {   // new athlete
                   $k = 0;   
                   if ($s >= $s_keep) {
                       $s_keep = $s;     
                   }                     
                    $s = 0;                      
                }
                $p++;
                $k++;  
                $s++;
               
                if ($row[15] == 'XXX'){
                     $skip = true;
                }
                else {                       
                    if (( ( in_array($row[15], $cfgResultsHighStayDecentralEnd)) && $ph == $k ) && !$newHight ){
                         $skip = true;
                    }
                    else {
                         $skip = false; 
                    }
                }    
                 
                if ($row[20] > 0){
                    $singleRound = $row[20];
                }
                else {
                      $singleRound = $row[19];  
                }     
                
                // terminate last row if new athlete and not first item
                if(($a != $row[4]) && ($i != 0))
                {                                      
                    $athleteValid = true;
                    $athleteValidAfter = true;  
                    if ($starts != $count_ends){
                        if ( count($arr_info) == $starts){
                       
                            for ($r=$starts-1;$r>=0;$r--){                             
                                if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                     $athleteValid = false;
                                }
                                else {
                                    $athleteValid = true; 
                                    break;
                                }                            
                            }
                           
                            for ($r=$starts-1;$r>=0;$r--){  
                                if ($r < $_POST['athlete'] - 1){
                                    break;
                                }                           
                                if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                     $athleteValidAfter = false;
                                }
                                else {
                                    $athleteValidAfter = true; 
                                    break;
                                }                            
                            }                          
                        }                        
                    }
                    else {
                           $athleteValid = false;
                           $athleteValidAfter = false;
                           if (!$aClick){ 
                               $_POST['athlete'] = 1;
                               $a_activ = 1; 
                           } 
                    }
                    if($_POST['athlete'] == $i || ( !$athleteValid  && $newHight && $k != 1) || ($aClick && $a_activ == $i)) {    
                   
                          if (!$flagField){
                              
                             ?>
                                <form action='event_results.php' method='post'
                                            name='perf'>
                                <td nowrap colspan='2'> 
                                            <input type='hidden' name='arg' value='save_res' />
                                            <input type='hidden' name='round' value='<?php echo $round; ?>' />
                                             <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
                                            <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
                                            <input type='hidden' name='start' value='<?php echo $a; ?>' />                                              
                                            <input type='hidden' name='item' value='<?php echo $item; ?>' />
                                            <input class='perfheight' type='hidden' name='perf' maxlength='5'    
                                               value='<?php echo $new_perf; ?>'
                                                     />
                                            <?php
                                            foreach ($_POST as $key => $val){
                                                $arr_key= explode('_', $key);
                                                $hiddenName = $arr_key[0]."_". $arr_key[1];
                                                ?>
                                                 <input type='hidden' name='<?php echo $hiddenName; ?>' value='<?php echo $val; ?>' /> 
                                                 <?php
                                            }
                                            ?>         
                                            <input class='texttiny' type='text' name='attempts' maxlength='3'  onfocus="this.value = '<?php echo $new_info; ?>'"
                                                value='<?php echo $new_info; ?>' />
                                             <button type='button' name="butt" onclick="document.perf.submit()">ok</button>
                                </td>
                                </form>                                              
                                          
                               <?php
                               
                            
                              $flagField = true;                                   
                              
                               echo "<td colspan='31'>&nbsp;";
                               echo "</td>"; 
                               echo "<td>";
                               $btn->set("event_results.php?arg=del_start&item=$a&round=$round", $strDelete);
                               $btn->printButton();
                               echo "</td>";    
                        }      
                  
                   }
                    echo "</tr>";
                }
/*
 *  Heat headerline
 */              
                if($h != $row[2])        // new heat
                {
                    $h = $row[2];                // keep heat ID
                    if(is_null($row[0])) {        // only one round

                        $title = "$strFinalround";
                    }
                    else {        // more than one round
                        $title = "$row[0]";
                    }

                    $c = 0;
                    if($status == $cfgRoundStatus['results_done']) {
                        $c = 1;        // increment colspan to include ranking
                    }
 
?>
    <tr>
        <form action='event_results.php#heat_<?php echo $row[3]; ?>' method='post'
            name='heat_id_<?php echo $h; ?>'>

        <th class='dialog' colspan='<?php echo 7+$c; ?>' />
            <?php echo $title; ?>
            <input type='hidden' name='arg' value='change_heat_name' />
            <input type='hidden' name='round' value='<?php  echo $round; ?>' />
            <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
            <input type='hidden' name='item' value='<?php echo $row[2]; ?>' />               
            <input class='nbr' type='text' name='id' maxlength='2'
                value='<?php echo $row[3]; ?>'
                onchange='document.heat_id_<?php echo $h;?>.submit()' />
                <a name='heat_<?php echo $row[3]; ?>' />
        </th> 
        <?php             
         
          if (isset($_POST['max'])){
                   $colspanPerf = $_POST['max'] * 2 + 2;   
          }
          else {
                $colspanPerf = $cfgCountHeight * 2 + 2;   
          }
          
         ?>  
            <th colspan="<?php echo $colspanPerf; ?>"><?php echo $strPerformance; ?></th>  
         <?php
        
        ?>
        </form>
    </tr>  
    <tr>
        <th class='dialog'><?php echo $strPositionShort; ?></th>
        <th class='dialog' colspan='2'><?php echo $strAthlete; ?></th>
        <th class='dialog'><?php echo $strYearShort; ?></th>
        <th class='dialog'><?php echo $strCountry; ?></th>
        <th class='dialog'><?php if($svm){ echo $strTeam; } elseif ($teamsm){ echo $strTeamsm;} else{ echo $strClub;} ?></th>  
        <th class='dialog'><?php echo $strRank; ?></th>   
        <th class='dialog' ><?php echo $strResultRemark; ?></th>    
         
        <?php                  
                   
        $height_start = $cfgHeightStartJump;         // default jump
        if ($disCode == '310'){                      // jump
             $height_start = $cfgHeightStartJump; 
        }  
        elseif ($disCode == '320'){
               $height_start = $cfgHeightStartPole;     // pole 
        }         
              
        ?>
        <form action='event_results.php' method='post' name='hight_id'> 
        <input type='hidden' name='arg' value='save_height' />
        <input type='hidden' name='round' value='<?php echo $round; ?>' />
        <input type='hidden' name='heat' value='<?php echo $row[2]; ?>' />  
        <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />          
                  
        <?php
                
                 
        $count_height = AA_checkHeight($round, $row[2]);     
        $arr_height = array();
        if ($count_height > 0) {
            $max = $count_height;  
            $sql = "SELECT h.Hoehe 
                    FROM 
                         hoehe AS h
                         LEFT JOIN runde AS r ON (r.xRunde = h.xRunde)
                         LEFT JOIN wettkampf AS w ON (w.xWettkampf = r.xWettkampf) 
                    WHERE 
                         h.xRunde = " . $round . " 
                         AND h.xSerie = " .$row[2] ." 
                         AND w.xMeeting = " . $_COOKIE['meeting_id'] ."
                         ORDER BY hoehe";
            $res = mysql_query($sql);
          
            if (mysql_errno() > 0) {
                            AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else {
                    $g = 0;
                    while ($row_height = mysql_fetch_row($res)) {
                            $arr_height[] = $row_height[0];
                            $height = AA_formatResultMeter($row_height[0]);
                                 
                            $dis = '';                                           // disable the hight done
                                    
                            if ($newHight) {
                                    $ph_vgl = $ph - 1;    
                                    if ($g <= $ph_vgl) {
                                            $dis = 'disabled="disabled"';      
                                    }
                            }
                            else {
                                    $ph_vgl = ceil($ph -1); 
                                    if ($g <= $ph_vgl ) {
                                            $dis = 'disabled="disabled"';      
                                    }
                                    if ($count_results == 0 && $g == 0) {
                                               $dis = ''; 
                                    }
                            }     
                                    
                            ?>   
                                   
                            <input class='perfheight' type='hidden' name='hiddenHeight_<?php echo $g; ?>' maxlength='5' value='<?php echo $height;?>'/>
                            <th class='dialog' colspan='2'><input class='perfheight' type='text' name='hight_<?php echo $g; ?>' maxlength='5'
                                value='<?php echo $height; ?>' onchange='document.hight_id.submit()' <?php echo $dis; ?> /></th>   
                           
                            <?php    
                            $g++;
                    }   
            }
        }
        else {
                $max = $cfgCountHeight;  
                for ($g=0;$g<$cfgCountHeight;$g++) {                                 
                        $_POST['start'] = 0;  
                               
                        $height_start_tmp = new PerformanceAttempt($height_start); 
                        $height_start_tmp = $height_start_tmp->getPerformance();                           
                                 
                        $arr_height[] = $height_start_tmp; 
                        AA_setHeight($height_start_tmp, $round, $row[2],'');   
                                  
                        ?>     
                                   
                        <input class='perfheight' type='hidden' name='hiddenHeight_<?php echo $g; ?>' maxlength='5' value='<?php echo sprintf("%.2f", $height_start);?>'/>
                            <th class='dialog' colspan='2'><input class='perfheight' type='text' name='hight_<?php echo $g; ?>' maxlength='5'
                                value='<?php echo sprintf("%.2f", $height_start); ?>' onchange='document.hight_id.submit()' /></th>
                          
                        <?php   
                        if ($disCode == '310'){                      // jump
                             $height_start += $cfgHeightDiffJump;; 
                        }  
                        elseif ($disCode == '320'){
                             $height_start += $cfgHeightDiffPole;     // pole 
                        }
                        else {                 
                            $height_start += $cfgHeightDiffJump;
                        }
                        
                }
        }   
                
                ?>  
                    
                <input class='perfheight' type='hidden' name='hiddenHeight_<?php echo $g; ?>' maxlength='5' value=''/> 
                    <th class='dialog' colspan='2'><input class='perfheight' type='text' name='hight_<?php echo $max; ?>' maxlength='5'
                        value='' onchange='document.hight_id.submit()' /></th>   
                <input type='hidden' name='max' value='<?php echo $max; ?>' />  
                <input type='hidden' name='disCode' value='<?php echo $disCode; ?>' />  
                 </form>   
                 
    </tr>
<?php
                }        // ET new heat

/*
 * Athlete data lines   
 */               
               
                if  ($newAthl) {
                     
                      if ($k == $ph){                                  // k = results per athlete  ph = hight column
                           
                               $athleteValid = true;
                               $athleteValidAfter = true;  
                               
                               if ($starts != $count_ends){
                                   if ( count($arr_info) == $starts){
                                       for ($r=($starts-1);$r>=0;$r--){  
                                            if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                                 $athleteValid = false;
                                            }
                                            else {
                                                $athleteValid = true; 
                                                break;
                                            }                                           
                                       }   
                                       
                                       for ($r=$starts-1;$r>=0;$r--){  
                                            if ($r < $_POST['athlete'] -1){
                                                break;
                                            }                           
                                            if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                                 $athleteValidAfter = false;
                                            }
                                            else {
                                                $athleteValidAfter = true; 
                                                break;
                                            }                            
                                       }                                       
                                   } 
                               }
                               else {
                                      $athleteValid = false;
                                      $athleteValidAfter = false; 
                                      if (!$aClick){  
                                          $_POST['athlete'] = 1;
                                          $a_activ = 1;
                                      }
                               }                               
                               if  (!$athleteValid){ 
                                    $l = 1;
                                    if (!$click){
                                        if ($a_first == 0){
                                           $_POST['athlete'] = 1;     
                                           $l = 0;    
                                        }
                                    }  
                               }         
                      }
                                     
                    if(in_array($row[15], $cfgResultsHighStayDecentralNotPassed)) {         // find O (example: O, XO , XXO )  
                        $l = 0;                // reset result counter                   
                        $newAthl = false;   
                    }
                    else {
                         if (is_null($row[15])){
                             $l = 0;  
                             $newAthl = false;              
                         }
                         else {    
                              if (in_array($row[15], $cfgResultsHighStayDecentralEnd) && $_POST['athlete'] == $i && $skip) {
                                     if ($athleteValidAfter && !$aClick){     
                                        $_POST['athlete'] ++;
                                     }                                          
                                      $newAthl = false;              
                                      $l = 1;  
                                     
                              }
                              elseif (in_array($row[15], $cfgResultsHighStayDecentralEnd) && $_POST['athlete'] == $i && !$skip && $p != $ph) {
                                     $l = 1;                                         
                              }
                              else {
                                  $l = 1;                 
                              }     
                         }
                    }
                }
                
                if($a != $row[4] )        // new athlete    
                {   
                     if ($k == $ph){ 
                             
                              $athleteValid = true;
                              if ($starts != $count_ends){
                                  if ( count($arr_info) == $starts){
                                   
                                         for ($r=$starts-1;$r>=0;$r--){  
                                            if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                                 $athleteValid = false;
                                            }
                                            else {
                                                $athleteValid = true; 
                                                break;
                                            }                                           
                                       }
                                   }
                              }
                              else {
                                    $athleteValid = false; 
                                    if (!$aClick){  
                                        $_POST['athlete'] = 1;
                                        $a_activ = 1; 
                                    }
                              }
                               $athleteValidAfter = true;
                               for ($r=$starts-1;$r>=0;$r--){  
                                    if ($r >= $_POST['athlete']){
                                        break;
                                    }                           
                                    if (in_array($arr_info[$r], $cfgResultsHighStayDecentralEnd)){
                                         $athleteValidAfter = false;
                                    }
                                    else {
                                        $athleteValidAfter = true; 
                                        break;
                                    }                            
                               }                                   
                              
                               if  (!$athleteValid){ 
                                    $l = 1;
                                    if (!$click){
                                        if ($a_first == 0){
                                            $_POST['athlete'] = 1;
                                            $l = 0;
                                        }
                                    }                                      
                               }     
                      }  
            
                      $a = $row[4];        // keep athlete ID 
                      $info_keep = $row[15];        // keep info           
                      $i++;              // increment athlete counter  
                      $newAthl = true;  
                     
                      if(in_array($row[15], $cfgResultsHighStayDecentralNotPassed)) {         // find O (example: O, XO , XXO ) 
                            $l = 0;                // reset result counter                       
                            $newAthl = false;   
                      }
                      else {
                            if (is_null($row[15])){
                                $l = 0;  
                                $newAthl = false;              
                            }
                            else {    
                                if (in_array($row[15], $cfgResultsHighStayDecentralEnd) && $_POST['athlete'] == $i && $skip && ! $aClick) { 
                                     if ($starts != $count_ends){
                                         $_POST['athlete'] ++;
                                     }   
                                      $newAthl = false;              
                                      $l = 1;                 
                                }
                                elseif (in_array($row[15], $cfgResultsHighStayDecentralEnd) && $_POST['athlete'] == $i && !$skip && $p != $ph) {
                                     $l = 1; 
                                }
                                else {
                                    $l = 1;                 
                                }   
                         }  
                      }          
            
                   
                      if($a_activ == $i) {    // active item 
                        $rowclass='active';                           
                        if (isset($_POST['start'])) {                              
                            AA_setCurrAthlete($row[2], $row[4]);
                        }   
                      }
                      else 
                        if($row[5] % 2 == 0) {        // even row numer
                            $rowclass='even';
                        }
                        else {                            // odd row number
                            $rowclass='odd';
                        }
                 
                        if($rowclass == 'active') {
                            ?>
                                <tr  id="athlete_<?php echo $i; ?>" class='active'>
                            <?php
                        }
                        else {   
                            ?>
                                <tr id="athlete_<?php echo $i; ?>" class='<?php echo $rowclass; ?>'
                                    onclick='selectAthlete(<?php echo $i; ?>)'>
                            <?php
                        }  
                    
                        ?>
                        <td class='forms_right'><?php echo $row[5]; ?></td>
                        <td class='forms_right'><?php echo $row[7]; /* start nbr */ ?></td>
                        <td nowrap><?php echo $row[8] . " " . $row[9];  /* name */ ?></td>
                        <td class='forms_ctr'><?php echo AA_formatYearOfBirth($row[10]); ?></td>
                        <td><?php echo (($row[16]!='' && $row[16]!='-') ? $row[16] : '&nbsp;');?></td>
                        <td nowrap><?php echo $row[11]; /* club */ ?></td>
                               
                        <?php
   
                    
                            if($a_activ == $i)    
                            {
                            ?>
                                    <form action='event_results.php' method='post'
                                        name='rank'>
                                    <td>
                                        <input type='hidden' name='arg' value='save_rank' />
                                        <input type='hidden' name='round' value='<?php echo $round; ?>' />
                                         <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
                                        <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
                                        <input type='hidden' name='item' value='<?php echo $row[4]; ?>' />                                           
                                        <input class='nbr' type='text' name='rank' maxlength='3'
                                            value='<?php echo $row[6]; ?>' onchange='document.rank.submit()' />
                                    </td>
                                    </form>
                              
                                  <?php if ($zaehler == 0){
                                      ?>
                                    
                                     <form action='event_results.php' method='post'
                                        name='remark_<?php echo $i; ?>'>
                                    <td>
                                        <input type='hidden' name='arg' value='save_remark' />
                                        <input type='hidden' name='round' value='<?php echo $round; ?>' />
                                         <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
                                        <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
                                        <input type='hidden' name='item' value='<?php echo $row[4]; ?>' />                                          
                                        <input type='hidden' name='xAthlete' value='<?php echo $row[18]; ?>' />    
                                        <input class='textshort' type='text' name='remark' maxlength='5'
                                            value='<?php echo $row[17]; ?>' onchange='document.remark_<?php echo $i; ?>.submit()' />
                                    </td>
                                    </form>  
                                    <?php
                                    $zaehler++;
                                  }       

                           }
                            else {
                                echo "<td>" . $row[6] . "</td>";
                                 echo "<td>" . $row[17] . "</td>";   
                            }                                  
                    
                    }        // ET new athlete                        

                   $new_perf = '';       
              
               
                    if($_POST['athlete'] == $i ) {                // only current athlet                                                                                                                   
                    
                        $item = '';
                    
                        $ph_perf = $ph - 1;
                        $hiddenName = "hiddenHight_" . $ph_perf;    
                        if ($newHight) {
                            $ph_perf +=1;
                        }
                        $hiddenName = "hiddenHight_" . $ph_perf;    
                        $new_perf = $arr_height[$ph_perf];
                        
                        $new_perf = new PerformanceAttempt($new_perf); 
                        $new_perf = $new_perf->getPerformance();           
                   
                        // read all performances achieved in current heat and
                        // better than last entered performance
                       
                        if(in_array($row[15], $cfgResultsHighStayDecentral)) {  
                                $new_info = $row[15];
                                if (in_array($row[15], $cfgResultsHighStayDecentralEnd)){ 
                                    $new_info = '';                                        
                                } 
                                else {
                                     $item = $row[13];  
                                }
                        }
                        else 
                            {    
                                $new_info = '';
                        }    
                        
                        if((is_null($row[14]) == false)    // result to display
                            && (empty($item))) {                // next height    
                                                                                     
                                ?>
                                <td nowrap>
                                    <?php echo  $row[15]; ?>
                                </td>
                                <?php 
                            
                                if ($_POST['athlete'] == $a_activ){   
                                        ?>
                                        <td>
                                        <?php
                                        $btn = new GUI_Button("event_results.php?arg=delete&round=$round&item=$row[13]&athlete=$i", "del");
                                        $btn->printButton();
                                        ?>
                                        </td>
                                        <?php
                                }
                                else {
                                        ?>
                                        <td></td>  
                                        <?php
                                }     
                        }
                        
                        if ($l == 0){                                // first item     
                       
                           $find = strpos(strtoupper($row[15], 'O'));        
                        
                           if ($row[15] == '-' ) {
                                      if (!$skip){
                                         $l++;                                           
                                      }
                           }   
                           elseif (!in_array($row[15], $cfgResultsHighStayDecentralNotPassed)) { 
                                    $l++;  
                                    
                           }   
                          
                           $flagField = true;
                            ?>
                            <form action='event_results.php' method='post'
                                        name='perf'>
                            <td nowrap colspan='2'> 
                                        <input type='hidden' name='arg' value='save_res' />
                                        <input type='hidden' name='round' value='<?php echo $round; ?>' />
                                         <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
                                        <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
                                        <input type='hidden' name='start' value='<?php echo $row[4]; ?>' />  
                                        <input type='hidden' name='xSerie' value='<?php echo $row[2]; ?>' />                                        
                                        <input type='hidden' name='item' value='<?php echo $item; ?>' />
                                        <input class='perfheight' type='hidden' name='perf' maxlength='5'
                                            value='<?php echo $new_perf; ?>'
                                                 />
                                         <?php
                                            foreach ($_POST as $key => $val){
                                                $arr_key= explode('_', $key);
                                                $hiddenName = $arr_key[0]."_". $arr_key[1];
                                                ?>
                                                 <input type='hidden' name='<?php echo $hiddenName; ?>' value='<?php echo $val; ?>' /> 
                                                 <?php
                                            }
                                            ?>         
                                        <input class='texttiny' type='text' name='attempts' maxlength='3' onfocus="this.value = '<?php echo $new_info; ?>'"  
                                            value='<?php echo $new_info; ?>' />
                                                
                                             <button type='button' name="butt" onclick="document.perf.submit()">ok</button> 
                            </td>
                            </form>      
                            
                                           <?php
                             $prev_info = '';                                     
                              
                             echo "<td colspan='31'>&nbsp;";
                             echo "</td>"; 
                             echo "<td>";
                             $btn->set("event_results.php?arg=del_start&item=$a&round=$round", $strDelete);
                             $btn->printButton();
                             echo "</td>";  
                    }  
               }
               else if (is_null($row[14]) == false) {  // result entered
                    $showNotValidRes = '';
                    if ($row[14] < 0){
                        $showNotValidRes = "($row[14])";
                    }                    
                    echo "<td colspan='2' nowrap>$showNotValidRes $row[15] </td>";
               }  
            }    
          
            if($a != 0)
            {   
                if (($s == $s_keep || $s_keep == 1 || ($info_keep == '-' && $ph == $k ) ) ){
                     if (! $aClick){

                        $skip = true;
                         $_POST['athlete'] ++;   
                     }
                }
                else {
                     $skip = false; 
                }  
                
                if($_POST['athlete'] == $i)        // active item
                {                                   
                     if (!$flagField && !$skip){
                          
                         ?>
                            <form action='event_results.php' method='post'
                                        name='perf'>
                            <td nowrap colspan='2'> 
                                        <input type='hidden' name='arg' value='save_res' />
                                        <input type='hidden' name='round' value='<?php echo $round; ?>' />
                                         <input type='hidden' name='singleRound' value='<?php  echo $singleRound; ?>' />    
                                        <input type='hidden' name='athlete' value='<?php echo $i+$focus; ?>' />
                                        <input type='hidden' name='start' value='<?php echo $a; ?>' />                                            
                                        <input type='hidden' name='item' value='<?php echo $item; ?>' />
                                        <input class='perfheight' type='hidden' name='perf' maxlength='5'
                                            value='<?php echo $new_perf; ?>'
                                                 />
                                                  <?php
                                            foreach ($_POST as $key => $val){
                                                $arr_key= explode('_', $key);
                                                $hiddenName = $arr_key[0]."_". $arr_key[1];
                                                ?>
                                                 <input type='hidden' name='<?php echo $hiddenName; ?>' value='<?php echo $val; ?>' /> 
                                                 <?php
                                            }
                                            ?>         
                                        <input class='texttiny' type='text' name='attempts' maxlength='3' onfocus="this.value = '<?php echo $new_info; ?>'"   
                                            value='<?php echo $new_info; ?>' />
                                         <button type='button' name="butt" onclick="document.perf.submit()">ok</button>
                            </td>
                            </form> 
                            <?php                                         
                    
                            echo "<td colspan='15'>&nbsp;";
                            echo "</td>"; 
                            echo "<td>";
                            $btn->set("event_results.php?arg=del_start&item=$a&round=$round", $strDelete);
                            $btn->printButton();
                            echo "</td>";
                     }                          
                }                   
                echo "</tr>";
            }
            ?>    
            
</table>
  
                 
            <?php
            mysql_free_result($res);
        }        // ET DB error
    }
}



?>

<script type="text/javascript">
<!--
    var prog = "<?php echo $prog_mode; ?>";
  
   
    if (prog == 2) {   
           if(document.perf) { 
               document.perf.attempts.focus();                
               window.scrollBy(0,100);  
            }    
    }
    else {      
        if(document.perf) {
                    document.perf.perf.focus();
                    document.perf.perf.select();
                    window.scrollBy(0,100);
        }
         if(document.rank) {
            document.rank.rank.focus();
            document.rank.rank.select();
            window.scrollBy(0,100);
        }
    }  
//-->
</script>

</body>
</html>

<?php
}
else
{
        AA_printErrorMsg($strErrMergedRound); 
    } 
}    // end function AA_results_High



function getNextHeight($heat, $curr_perf)
{
    require('./lib/common.lib.php');    
   
     $sql = "SELECT DISTINCT
                    r.Leistung
             FROM
                    resultat AS r
                    LEFT JOIN serienstart AS ss ON (r.xSerienstart = ss.xSerienstart)
             WHERE 
                    ss.xSerie = $heat
                    AND r.Leistung > $curr_perf
             ORDER BY
                    r.Leistung ASC";       
     
    $result = mysql_query($sql);   
    
    if(mysql_errno() > 0) {        // DB error
        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
    }
    else {
        $row = mysql_fetch_row($result);
        $new_perf = AA_formatResultMeter($row[0]);
        mysql_free_result($result);
    }

    return $new_perf;
}    // end function getNextHeight 


}    // AA_RESULTS_HIGH_LIB_INCLUDED
?>
