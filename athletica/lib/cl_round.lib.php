<?php

if (!defined('AA_CL_ROUND_LIB_INCLUDED'))
{
	define('AA_CL_ROUND_LIB_INCLUDED', 1);


/********************************************
 *
 * CLASS Round
 *
 * Provides functionality to maintain round info.
 * After data manipulations check $GLOBALS['AA_ERROR']
 * for errors.
 *
 *******************************************/

class Round
{
	var $roundID;

	function Round($round)
	{
		$this->roundID = $round;
	}
	
	// round status
	function setStatus($status)
	{
		require ('./lib/utils.lib.php');

		if($status >= $GLOBALS['cfgRoundStatus']['open'])
		{
			$result = mysql_query("
				SELECT
					Status
				FROM
					runde
				WHERE xRunde = " . $this->roundID
			);

			if(mysql_errno() > 0) {
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
			else
			{
				$row = mysql_fetch_row($result);

				// update only if status changed
				if($row[0] != $status)
				{
					$this->update("
						UPDATE runde SET
							Status = $status                          
						WHERE xRunde = " . $this->roundID
					);

					// update log file if update OK
					if(empty($GLOBALS['AA_ERROR']))
					{
						$txt = $GLOBALS['strStatus'] . ": "
							. $GLOBALS['cfgRoundStatusTranslation'][$status];	
						AA_utils_logRoundEvent($this->roundID, $txt);
					}
                    
                     $sql = "UPDATE meeting SET
                            StatusChanged = 'y'                          
                        WHERE xMeeting = " .  $_COOKIE['meeting_id'];              
              
                     mysql_query($sql);   
                        
                     if(mysql_errno() > 0) {
                                $GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
                     }             
				}
                
				mysql_free_result($result);
			}	// ET DB error
		}	// ET valid status
	}


	// speakers status
	function setSpeakerStatus($status)
	{
		// determine actual speaker status
		if($status == $GLOBALS['cfgSpeakerStatus']['open'])
		{
			$rndStat = $GLOBALS['cfgSpeakerStatus']['announcement_pend']; 

			// check if all heats open
			$result = mysql_query("
				SELECT
					count(Status)
				FROM
					serie
				WHERE xRunde = " . $this->roundID . "
				AND Status != " . $GLOBALS['cfgHeatStatus']['open']
			);

			if(mysql_errno() > 0) {
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
			else
			{
				$row = mysql_fetch_row($result);

				// all heats with speaker state open
				if($row[0] == 0) {
					$rndStat = $GLOBALS['cfgSpeakerStatus']['open']; 
				}
				mysql_free_result($result);
			}	// ET DB error

			// check if any heats left with status open
			$result = mysql_query("
				SELECT
					count(Status)
				FROM
					serie
				WHERE xRunde = " . $this->roundID . "
				AND Status != " . $GLOBALS['cfgHeatStatus']['announced']
			);

			if(mysql_errno() > 0) {
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
			else
			{
				$row = mysql_fetch_row($result);

				// round's speaker state if no more heats left
				if($row[0] == 0) {
					$rndStat = $GLOBALS['cfgSpeakerStatus']['announcement_done']; 
				}
				mysql_free_result($result);
			}	// ET DB error

			// change this round's speaker status
			$this->update("
				UPDATE runde SET
					Speakerstatus = $rndStat
				WHERE xRunde = " . $this->roundID
			);
		}
		else if($status > $GLOBALS['cfgSpeakerStatus']['open'])
		{
			$this->update("
				UPDATE runde SET
					Speakerstatus = $status
				WHERE xRunde = " . $this->roundID
			);
		}
	}


	// update DB
	function update($query)
	{
		if(!empty($query))
		{
			$GLOBALS['AA_ERROR'] = '';

			mysql_query("
				LOCK TABLES
					runde WRITE
			");

			mysql_query($query);

			if(mysql_errno() > 0) {
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}
			mysql_query("UNLOCK TABLES");
		}
	}

} // end class Round

} // end AA_CL_ROUND_LIB_INCLUDED
?>
