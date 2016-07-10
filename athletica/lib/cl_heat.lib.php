<?php

if (!defined('AA_CL_HEAT_LIB_INCLUDED'))
{
	define('AA_CL_HEAT_LIB_INCLUDED', 1);

/********************************************
 *
 * CLASS Heat
 *
 * Provides functionality to maintain heat info.
 * Usage: After object creation, the user may call save function,
 * which determines to required DML-action.
 *
 *******************************************/

require('./lib/cl_round.lib.php');


class Heat
{
	var $heatID;
	var $roundID;

	function Heat($heatID, $round)
	{
		$this->heatID = $heatID;
		$this->roundID = $round;
	}
	
	function setHeatStatus($status)
	{
		if($status >= $GLOBALS['cfgHeatStatus']['open'])
		{
			$this->update("
				UPDATE serie SET
					Status = $status
					WHERE xSerie = " . $this->heatID
			);

			// change this round's speaker status
			// (class Round will determine correct speaker status)
			$round = new Round($this->roundID);
			$round->setSpeakerStatus($GLOBALS['cfgSpeakerStatus']['open']); 
		}	// ET valid heat status
	}

	function update($query)
	{
		if(!empty($query))
		{
			$GLOBALS['AA_ERROR'] = '';

			mysql_query("
				LOCK TABLES
					serie WRITE
			");

			mysql_query($query);

			if(mysql_errno() > 0) {
				$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			}

			mysql_query("UNLOCK TABLES");
		}
	}

} // end class Heat

} // end AA_CL_HEAT_LIB_INCLUDED
?>
