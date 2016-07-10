<?php

if (!defined('AA_CL_TIMETABLE_LIB_INCLUDED'))
{
	define('AA_CL_TIMETABLE_LIB_INCLUDED', 1);

/********************************************
 *
 * CLASS Timetable
 *
 *		Timetable maintenance
 *		
 *******************************************/

class Timetable
{
	var $date;
	var $event;
	var $hour;
	var $round;
	var $min;
	var $type;
	var $group;
	var $etime; // enrolement time
	var $mtime; // manipulation time (stellzeit)
    var $svmCode; 

	/*		Timetable()
	 * 	----------- 
	 *		Gets session variables
	 */
	function Timetable()
	{   
		$this->date = $_POST['date'];
		$this->event = $_POST['item'];
		$this->round = $_POST['round'];
		$this->type = $_POST['roundtype'];
		$this->hour = $_POST['hr'];
		$this->min = $_POST['min'];
		$this->group = $_POST['g'];
		$this->etime = $_POST['etime'];
		$this->mtime = $_POST['mtime'];
        $this->svmCode = $_POST['svmCode'];   
	}

	/*		add()
	 *  	-----
	 *		add a new event round
	 */
	function add()
	{    
        include('./convtables.inc.php');  
		require('./lib/utils.lib.php');
		$GLOBALS['AA_ERROR'] = '';
        $keep_key = '';
		// Error: Empty fields
		if(empty($this->date) || empty($this->hour) || empty($this->min)
			|| empty($this->event))
		{
			$GLOBALS['AA_ERROR'] = $GLOBALS['strErrEmptyFields'];
		}
		else
		{
			// Error: Invalid time
			if(($this->hour<0) || ($this->hour>23)
				|| ($this->min<0) || ($this->min>59))
			{
				$GLOBALS['AA_ERROR'] = $GLOBALS['strErrInvalidTime'];
			}
			else
			{
				mysql_query("LOCK TABLES wettkampf, rundentyp_de READ, rundentyp_fr READ, rundentyp_it READ, runde WRITE");
				// check if event is valid
				if(AA_utils_checkReference("wettkampf", "xWettkampf",
					$this->event)==0)
				{
					$GLOBALS['AA_ERROR'] = $GLOBALS['strEvent'] . $GLOBALS['strErrNotValid'];
				}
				else
				{   
                    // check if roundtype is valid
					if((!empty($this->type))
						&& (AA_utils_checkReference("rundentyp_" . $_COOKIE['language'], "xRundentyp",
							$this->type) == 0))
					{
						$GLOBALS['AA_ERROR'] = $GLOBALS['strType'] . $GLOBALS['strErrNotValid'];
					}elseif(empty($this->type)){ // round type has to be given!!
						$GLOBALS['AA_ERROR'] = $GLOBALS['strType'] . $GLOBALS['strErrNotValid'];
					}
					// OK: try to add round
					else
					{  						
						if(!empty($this->etime)){
							$et = AA_formatEnteredTime($this->etime);
							$sqlEtime = ", Appellzeit = '$et[0]:$et[1]:00'";
						}
						if(!empty($this->mtime)){
							$mt = AA_formatEnteredTime($this->mtime);
							$sqlMtime = ", Stellzeit = '$mt[0]:$mt[1]:00'";
						} 
                        
                        // set conversion table           
                        if (isset($cfgSVM[$this->svmCode])){   
                            $cfgSVM_arr = $cfgSVM[$this->svmCode]; 
                            if (isset($cfgSVM[$this->svmCode."_NT"])){             // _NT = nulltime
                                $cfgSVM_arr_NT = $cfgSVM[$this->svmCode."_NT"]; 
                            }
                        }   
                        $d=$_POST['dCode'];
                        if ($_POST['arg'] == 'change_starttime'){
                            if (isset($cfgSVM[$this->svmCode."_NT"])){            // _NT = nulltime 
                                 foreach ($cfgSVM_arr as $key => $val){  
                                            if ($val == $_POST['dCode']){
                                                $keep_key=$key; 
                                                break;
                                            }
                                 }
                                
                                if ($cfgSVM_arr_NT[$keep_key] == '0000') {        // discipline with nulltime
                                    $this->change_all();  
                                }
                                else { 
                                                                         
                                     mysql_query("
                                INSERT runde SET
                                    Datum='" . $this->date . "'
                                    , Startzeit='" . $this->hour .":". $this->min .":00" . "'
                                    , xRundentyp=" . $this->type . "
                                    , xWettkampf=" . $this->event."
                                    $sqlEtime
                                    $sqlMtime 
                                      , Gruppe = '".$this->group."'"     
                                    );  
                                }  
                            } 
                            else {  
                              
                              mysql_query("
                                INSERT runde SET
                                    Datum='" . $this->date . "'
                                    , Startzeit='" . $this->hour .":". $this->min .":00" . "'
                                    , xRundentyp=" . $this->type . "
                                    , xWettkampf=" . $this->event."
                                    $sqlEtime
                                    $sqlMtime 
                                      , Gruppe = '".$this->group."'"     
                                    );  
                            }   
                         }
                         else {   
                                $sql_event="SELECT 
                                                w.Typ,
                                                w.Punktetabelle,
                                                w.Punkteformel,
                                                w.Info,
                                                w.Mehrkampfcode,
                                                w.Mehrkampfende,  
                                                w.Mehrkampfreihenfolge,
                                                w.TypAenderung  
                                            FROM
                                                wettkampf as w
                                            WHERE
                                                w.xWettkampf = " . $this->event;
                          
                                $res_event = mysql_query($sql_event); 
                                if(mysql_errno() > 0) {
                                    $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                }
                                else {
                                    $row_event=mysql_fetch_array($res_event);
                                    if ($row_event[4] > 0 && $this->type != 8){              // // typ 8 = combined event
                                        $typchange = $row_event[0].",". $row_event[1].",". $row_event[2] .",". $row_event[3] .","
                                                 . $row_event[4] .",". $row_event[5].",". $row_event[6];  
                                                 
                                         // update event as single event
                                         mysql_query("
                                                UPDATE wettkampf SET
                                                    Typ = 0
                                                    , Punktetabelle = 0
                                                    , Punkteformel = '0'
                                                    , Info = ''  
                                                    , Mehrkampfcode = 0
                                                    , Mehrkampfende = 0 
                                                    , Mehrkampfreihenfolge = 0 
                                                    , TypAenderung = '" .$typchange ."'  
                                                    WHERE xWettkampf = " .$this->event
                                         );
                               
                                        if(mysql_errno() > 0)
                                            {
                                            $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                        }  
                                    }   
                                }
                             
                             
						    mysql_query("
							    INSERT runde SET
								    Datum='" . $this->date . "'
								    , Startzeit='" . $this->hour .":". $this->min .":00" . "'
								    , xRundentyp=" . $this->type . "
								    , xWettkampf=" . $this->event."
								    $sqlEtime
								    $sqlMtime 
							  	    , Gruppe = '".$this->group."'"     
						            );
                        }
					}
				}
			}
			if(mysql_errno() > 0)
			{
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
			mysql_query("UNLOCK TABLES");
		}
	}
     
	/*		delete()
	 *  	--------
	 *		delete an event round
	 */
	function delete()
	{
		require('./lib/utils.lib.php');
		$GLOBALS['AA_ERROR'] = '';

		// Error: Empty fields
		if(empty($_POST['round']))
		{
			$GLOBALS['AA_ERROR'] = $GLOBALS['strErrEmptyFields'];
		}
		// OK: try to delete round
		else
		{
			mysql_query("LOCK TABLES serie READ, runde WRITE, rundenset READ, rundenset WRITE");
			// Still in use?
			if(AA_utils_checkReference("serie", "xRunde", $this->round) != 0)
			{
				$GLOBALS['AA_ERROR'] = $GLOBALS['strRound'] . $GLOBALS['strErrStillUsed'];
			}
			else	// OK: Not used anymore
			{
				mysql_query("
					DELETE FROM
						runde
					WHERE xRunde = " . $this->round
				);
			}
			// Check if any error returned from DB
			if(mysql_errno() > 0)
			{
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
            
            // check if merged round
            $sql = "SELECT xRundenset, Hauptrunde FROM rundenset WHERE xRunde = " . $this->round ." AND xMeeting = " .$_COOKIE['meeting_id'];
            $result = mysql_query($sql);
          
            if(mysql_errno() > 0)
            {
                $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
            }
            $num =  mysql_num_rows($result);  
            if ($num > 0){
                $row = mysql_fetch_row($result);
                
                if ($row[1] == 1 || $num == 2){      // main round                        
                    mysql_query("
                        DELETE FROM
                            rundenset
                        WHERE xRundenset = " . $row[0] ." AND xMeeting = " .$_COOKIE['meeting_id']);
                    
                    if(mysql_errno() > 0)
                        {
                        $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                    } 
                }
                else {
                      mysql_query("
                        DELETE FROM
                            rundenset
                        WHERE xRunde= " . $this->round ." AND xMeeting = " .$_COOKIE['meeting_id']);     
                      if(mysql_errno() > 0){                        
                        $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                      }   
                }
                    
            }
                
            

			mysql_query("UNLOCK TABLES");
		}
	}

	/*		change()
	 *  	--------
	 *		change an event round
	 */
	function change()
	{  
       include('./convtables.inc.php');
       require('./lib/common.lib.php'); 
		// Error: Empty fields
		if(empty($this->round))
		{
			$GLOBALS['AA_ERROR'] = $GLOBALS['strErrEmptyFields'];
		}
		// OK: try to change round
		else
		{
			mysql_query("LOCK TABLES serie READ, kategorie_svm as ks READ, wettkampf as w READ, runde as r READ, disziplin_de as d READ, disziplin_fr as d READ, disziplin_it as d READ, runde WRITE, wettkampf WRITE, meeting WRITE");

			$status = AA_utils_getRoundStatus($this->round);

			if($status == $GLOBALS['cfgRoundStatus']['results_done'])
			{
				$GLOBALS['AA_ERROR'] = $GLOBALS['strErrResultsEntered'];   
			}
			else
			{
				   
				if(empty($this->type)){ // round type is not optional!
					$GLOBALS['AA_ERROR'] = $GLOBALS['strType'] . $GLOBALS['strErrNotValid'];
				}else{
					
					if(!empty($this->etime)){
						$et = AA_formatEnteredTime($this->etime);
						$sqlEtime = ", Appellzeit = '$et[0]:$et[1]:00'";
					}
					if(!empty($this->mtime)){
						$mt = AA_formatEnteredTime($this->mtime);
						$sqlMtime = ", Stellzeit = '$mt[0]:$mt[1]:00'";
					} 
                                              
        
                    // set conversion table                 
                    if (isset($cfgSVM[$this->svmCode])){   
                                    $cfgSVM_arr = $cfgSVM[$this->svmCode]; 
                                     if (isset($cfgSVM[$this->svmCode."_NT"])){                // _NT = nulltime   
                                        $cfgSVM_arr_NT = $cfgSVM[$this->svmCode."_NT"]; 
                                     }
                    }   
                
                    if (isset($cfgSVM[$this->svmCode."_NT"])){                                 // _NT = nulltime   
                        foreach ($cfgSVM_arr as $key => $val){  
                            if ($val == $_POST['dCode']){
                                $keep_key=$key; 
                                continue;
                                }
                            }
                                
                            if ($cfgSVM_arr_NT[$keep_key] == '0000') {                    // discipline with nulltime
                                $this->change_all();  
                            }
                            else {
                                mysql_query("
                                    UPDATE runde SET
                                    Datum = '" . $this->date . "'
                                    , Startzeit = '".$this->hour.":".$this->min.":00"."'
                                    , xRundentyp = " . $this->type . "
                                    , Gruppe = '" . $this->group . "'  
                                    $sqlEtime
                                    $sqlMtime
                                    WHERE xRunde = " . $this->round
                                );
                  
                                if(mysql_errno() > 0)
                                {   
                                    $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                }  
                            }
                    }
                    else {   
                        
                        $sql="SELECT 
                                    xRundentyp,
                                    xWettkampf
                              FROM
                                    runde as r
                              WHERE
                                    r.xRunde = " . $this->round;
                          
                        $result = mysql_query($sql); 
                        if(mysql_errno() > 0) {
                             $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                        }
                        else {
                            $row=mysql_fetch_array($result);  
                            if ($row[0] == 8 && $this->type != 8){           // typ 8 = combined event       
                                $typchange= '';
                                
                                $sql_event="SELECT 
                                                w.Typ,
                                                w.Punktetabelle,
                                                w.Punkteformel,
                                                w.Info,
                                                w.Mehrkampfcode,
                                                w.Mehrkampfende,  
                                                w.Mehrkampfreihenfolge  
                                            FROM
                                                wettkampf as w
                                            WHERE
                                                w.xWettkampf = " .  $row[1];
                          
                                $res_event = mysql_query($sql_event); 
                                if(mysql_errno() > 0) {
                                    $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                }
                                else {
                                    $row_event=mysql_fetch_array($res_event);
                                    $typchange = $row_event[0].",". $row_event[1].",". $row_event[2] .",". $row_event[3] .","
                                                 . $row_event[4] .",". $row_event[5].",". $row_event[6];    
                                
                                    // update event as single event
                                    mysql_query("
                                            UPDATE wettkampf SET
                                                Typ = 0
                                                , Punktetabelle = 0
                                                , Punkteformel = '0'
                                                , Info = ''  
                                                , Mehrkampfcode = 0
                                                , Mehrkampfende = 0 
                                                , Mehrkampfreihenfolge = 0 
                                                , TypAenderung = '" .$typchange ."'  
                                                WHERE xWettkampf = " . $row[1]
                                    );
                               
                                    if(mysql_errno() > 0)
                                        {
                                        $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                    }
                                }
                            }
                            elseif ($this->type == 8 && $row[0] != 8){                // typ 8 = combined event
                                  
                                    $sql_event="SELECT 
                                                w.TypAenderung                                                
                                            FROM
                                                wettkampf as w
                                            WHERE
                                                w.xWettkampf = " . $row[1];
                          
                                    $res_event = mysql_query($sql_event); 
                                    if(mysql_errno() > 0) {
                                        $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                    }
                                    else {
                                        $row_event=mysql_fetch_array($res_event);
                                        if ($row_event[0] != '') {
                                            $typchange = explode(",",$row_event[0]);         
                                    
                                            // update event as combined event 
                                            mysql_query("
                                                UPDATE wettkampf SET
                                                    Typ = ".$typchange[0] ."
                                                    , Punktetabelle = ".$typchange[1] ." 
                                                    , Punkteformel =  '".$typchange[2] ."' 
                                                    , Info =  '".$typchange[3] ."' 
                                                    , Mehrkampfcode =  ".$typchange[4] ." 
                                                    , Mehrkampfende =  ".$typchange[5] ." 
                                                    , Mehrkampfreihenfolge =  ".$typchange[6] ." 
                                                    , TypAenderung = ''  
                                                    WHERE xWettkampf = " . $row[1]
                                            );
                               
                                            if(mysql_errno() > 0)
                                                {
                                                $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                                            } 
                                        }   
                                }  
                            }
                       
					    mysql_query("
						    UPDATE runde SET
							    Datum = '" . $this->date . "'
							    , Startzeit = '".$this->hour.":".$this->min.":00"."'
							    , xRundentyp = " . $this->type . "
							    , Gruppe = '" . $this->group . "'  
							    $sqlEtime
							    $sqlMtime
						        WHERE xRunde = " . $this->round
					            );
	              
					    if(mysql_errno() > 0)
					    {
						    $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
					    } 
                    }
				}
                 $sql = "UPDATE meeting SET
                            StatusChanged = 'y'                          
                        WHERE xMeeting = " .  $_COOKIE['meeting_id'];              
              
                mysql_query($sql);   
                
                if(mysql_errno() > 0) {
                        $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                }             
                
                
                }
			}
			mysql_query("UNLOCK TABLES");
                
			if($status > 0 && $status != 4)
			{
				$txt = $GLOBALS['strTimetableChanged'] . ": "
						 . $this->date . ", "
						 . $this->hr . ":" . $this->min;
				AA_utils_logRoundEvent($this->round, $txt);
                
			}
           
		}	// ET round status
	}
    
   /*        change()
   *      -----------
   *        change an event round
   */
    function change_all()
    {  
       include('./convtables.inc.php');
       require('./lib/common.lib.php');  
         
      
       // set conversion table                 
       $cfgSVM_arr = $cfgSVM[$this->svmCode];   
       $cfgSVM_arr_NT = $cfgSVM[$this->svmCode."_NT"];                  // _NT = nulltime   
       
       $sql="SELECT 
                r.xRunde, w.xWettkampf , d.Code , d.Typ
             FROM
                wettkampf as w
                LEFT JOIN runde as r On (w.xWettkampf = r.xWettkampf)
                LEFT JOIN disziplin_" . $_COOKIE['language'] . " as d ON (d.xDisziplin = w.xDisziplin)
             WHERE
                w.xKategorie = ". $_POST['cat'] ."
                AND w.xKategorie_svm = " .$_POST['svmcat'] ."
                AND w.xMeeting = ".$_COOKIE['meeting_id']." 
             ORDER BY d.Anzeige";
          
       $result=mysql_query($sql);
      
       if(mysql_errno() > 0)
            {
                $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
            }
       $nulltime=$this->hour . $this->min;
       $i=0;
      
       while ($row=mysql_fetch_row($result)){ 
           
             if ($row[3]   == $cfgDisciplineType[$strDiscTypeTrack] ||
                        $row[3]   == $cfgDisciplineType[$strDiscTypeTrackNoWind] ||  
                        $row[3]   == $cfgDisciplineType[$strDiscTypeDistance] ||  
                        $row[3]   == $cfgDisciplineType[$strDiscTypeRelay] )  
               {                                                                    // discipline type track
                    $this->type = 6; // round type "Serie"  
              }   
              else {                                                                // discipline type tech
                 $this->type = 9; // round type "ohne" 
              } 
              
             foreach ($cfgSVM_arr as $key => $val){  
                    if ($val == $row[2]){
                        $keep_key=$key; 
                        continue;
                    }
             }   
          
            $tn = $cfgSVM[$this->svmCode."_NT"][$keep_key];              // _NT = nulltime   
            $timeBerechnung=$nulltime+$tn;
            $timeBerechnung=sprintf("%04d", $timeBerechnung);
            $hour=substr($timeBerechnung,0,-2);
            $min=substr($timeBerechnung,2); 
            if ($min >= 60){
               $min-=60;
               $hour++;            
            }                
           
             if (is_null($row[0])){
                  mysql_query("
                        INSERT into runde SET
                            Datum = '" . $this->date . "'
                            , Startzeit = '".$hour.":".$min.":00"."'
                            , xRundentyp = " . $this->type . "
                            , xWettkampf = " . $row[1]  . " 
                            , Gruppe = '" . $this->group . "'  
                            $sqlEtime
                            $sqlMtime"      
                    );    
                        
                    if(mysql_errno() > 0)
                        {
                        $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();   
                    }
            }
            else {
                mysql_query("
                        UPDATE runde SET
                            Datum = '" . $this->date . "'
                            , Startzeit = '".$hour.":".$min.":00"."'
                            , xRundentyp = " . $this->type . "
                            , Gruppe = '" . $this->group . "'  
                            $sqlEtime
                            $sqlMtime
                        WHERE xRunde = " . $row[0]
                    );    
                  
                  if(mysql_errno() > 0)
                    {
                    $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                    break;
                  }
            }
          }  
    }  
    
} // end Timetable


/********************************************
 *
 * CLASS TimetableNew
 *
 *		Timetable class for new events
 *		
 *******************************************/

class TimetableNew extends Timetable
{
	/*		TimetableNew()
	 * 	-------------- 
	 *		Variables provided
	 */
	function TimetableNew($date, $item, $round, $roundtype, $hr, $min, $et='', $mt='')
	{
		$this->date = $date;
		$this->event = $item;
		$this->hour = $hr;
		$this->round = $round;
		$this->min = $min;
		$this->type = $roundtype;
		$this->etime = $et;
		$this->mtime = $mt;
	}

} // end TimetableNew

}	// ET AA_CL_TIMETABLE_LIB_INCLUDED
?>
