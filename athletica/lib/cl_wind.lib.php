<?php

if (!defined('AA_CL_WIND_LIB_INCLUDED'))
{
	define('AA_CL_WIND_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Wind
 *
 * Class to handle wind support
 *
 *******************************************/


class Wind
{
	var $wind;

	function Wind($wind="0,0")
	{
		$this->setWind($wind);
	}


	function setWind($wind)
	{
		$this->wind = $this->validateWind($wind);
	}


	function getWind()
	{
		return $this->wind;
	}


	function validateWind($wind)
	{
		if($wind == "-"){
			return "-";
		}
		
		// remove any spaces and + signs
		$wind = trim($wind);
		if(substr($wind,0,1) == "-"){
			$wind = "-".trim(substr($wind,1));
		}elseif(substr($wind,0,1) == "+"){
			$wind = trim(substr($wind,1));
		}
		
		// format wind value: replace all separators by point
		$wind = strtr($wind, $GLOBALS['cfgResultsSepTrans']);
		
		// if strlen is longer or equal 2 and there are no separators
		if(strlen($wind) >= 2 && strpos($wind, $GLOBALS['cfgResultsSeparator']) === false){
			if(substr($wind,0,1) == "-"){
				$wind = substr($wind,0,2). $GLOBALS['cfgResultsSeparator'] .substr($wind,2);
			}else{
				$wind = substr($wind,0,1). $GLOBALS['cfgResultsSeparator'] .substr($wind,1);
			}
		}
		
		// tokenize wind
		$tok = strtok($wind, $GLOBALS['cfgResultsSeparator']);
		$i=0;
		$num = TRUE;
		while ($tok != '') {
			if(!is_numeric($tok)) {
				$num = FALSE;
			}
			$t[$i] = $tok;	
			$tok = strtok($GLOBALS['cfgResultsSeparator']);
			$i++;
		}

		$wind = "0" . $GLOBALS['cfgResultsWindSeparator'] . "0";

		if($num == TRUE)
		{
			switch(count($t)) // nbr of time elements
			{
			case 1:	 // meters per second entered	
				$wind = $t[0] . $GLOBALS['cfgResultsWindSeparator'] . "0";
				break;
			case 2:	 // meters and centimeters per second entered	
				$wind = $t[0] . $GLOBALS['cfgResultsWindSeparator'] . $t[1];
				$wind = (ceil($wind*10)/10); // round hundert fraction
				$wind = sprintf("%01.1f", $wind);
				break;
			default:	
				$wind = "0" . $GLOBALS['cfgResultsWindSeparator'] . "0";
				break;
			}
		}

		return $wind;
	}

} // end Wind



/********************************************
 *
 * CLASS HeatWind
 *
 * Class to handle wind support for track disciplines
 * where wind is measured per heat.
 *
 *******************************************/

class HeatWind extends Wind
{
	var $round;
	var $heatID;

	function HeatWind($round, $heatID)
	{
		$this->round = $round;
		$this->heatID = $heatID;
		$this->wind = '0,0';
	}


	function setWind($wind)
	{
		require('./lib/utils.lib.php');
		$GLOBALS['AA_ERROR'] = '';

		AA_utils_changeRoundStatus($this->round, $GLOBALS['cfgRoundStatus']['results_in_progress']);
		if(mysql_errno() > 0) {
			$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
			return;
		}

		mysql_query("
			LOCK TABLES
				serie WRITE
		");

		$this->wind = $this->validateWind($wind);

		mysql_query("
			UPDATE serie SET
				Wind = '" . $this->getWind() . "'
			WHERE xSerie = " . $this->heatID
		);

		if(mysql_errno() > 0) {
			$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
		}

		mysql_query("UNLOCK TABLES");
	}

} // end HeatWind

}	// ET AA_CL_WIND_LIB_INCLUDED
?>
