<?php

if (!defined('AA_CL_PERFORMANCE_LIB_INCLUDED'))
{
	define('AA_CL_PERFORMANCE_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Performance
 *
 * Basic class to handle an athletes performance
 *
 *******************************************/


class Performance
{
	var $performance;


	
	function Performance($performance)
	{
		$this->setPerformance($performance);
	}


	function setPerformance($performance, $secFlag = false)
	{
		$this->performance = $this->validatePerformance($performance, $secFlag);
	}


	function getPerformance()
	{
		return $this->performance;
	}


	function validatePerformance($performance)
	{
		return $performance;
	}

} // end Performance



/********************************************
 *
 * CLASS PerformanceTime
 *
 * Class to handle an athletes time performance
 *
 *******************************************/

class PerformanceTime extends Performance
{

	function PerformanceTime($performance, $secFlag = false)
	{
		$this->setPerformance($performance, $secFlag);
	}

	function validatePerformance($performance, $secFlag = false)
	{
		// format time value: replace all separators by comma
		// (e.g. 10.52 -> 10,52; 1:58,12 -> 1,58,12)
		$time = strtr($performance, $GLOBALS['cfgResultsSepTrans']);
		$tok = strtok($time, $GLOBALS['cfgResultsSeparator']);
		
		// get tokanized performance if entered without separators
		if(strpos($time, $GLOBALS['cfgResultsSeparator']) === false){
			if($secFlag){
                 if ($performance == $GLOBALS['cfgMissedAttempt']['db']){
                     $performance = substr($performance,0,-3).$GLOBALS['cfgResultsSeparator'].substr($performance,strlen($performance)-3); 
                 }
                 else {
				    $performance = substr($performance,0,-2).$GLOBALS['cfgResultsSeparator'].substr($performance,strlen($performance)-2);
                 }
			}else{
				$tmp = $performance;
				$performance = "";
                if ($tmp == $GLOBALS['cfgMissedAttempt']['db']){
                    for($i = (strlen($tmp)-3); $i>=-1; $i = $i-2){
                        $c = $i;
                        $a = 3;
                        if($c == -1){ $c = 0; $a = 1; }
                        $performance = substr($tmp,$c,$a).$GLOBALS['cfgResultsSeparator'].$performance;
                    }
                    
                }
                else {
				    for($i = (strlen($tmp)-2); $i>=-1; $i = $i-2){
					    $c = $i;
					    $a = 2;
					    if($c == -1){ $c = 0; $a = 1; }
					    $performance = substr($tmp,$c,$a).$GLOBALS['cfgResultsSeparator'].$performance;
				    }
                }
			}
			$time = strtr($performance, $GLOBALS['cfgResultsSepTrans']);
			$tok = strtok($time, $GLOBALS['cfgResultsSeparator']);
		}
		
		$i=0;
		$num = TRUE;
		while ($tok != '') {
            if ($tok != '-'){
			    if(!is_numeric($tok)) {
				    $num = FALSE;
                }
			}
			$t[$i] = $tok;	
			$tok = strtok($GLOBALS['cfgResultsSeparator']);
			$i++;
		}
		$time = NULL;

		if($num == TRUE)	// only numerics found
		{
			switch(count($t)) // nbr of time elements
			{
			case 1:	 // only seconds entered	
				$hrs = 0;
				$min = 0;
				$sec = $t[0];
				$frac = 0;
				break;
			case 2:	 // sec and fractions of seconds entered	
				$hrs = 0;
				$min = 0;
				$sec = $t[0];
				$frac = $t[1];
				break;
			case 3:	 // min, sec and fractions of seconds entered	
				$hrs = 0;
				$min = $t[0];
				$sec = $t[1];
				$frac = $t[2];
				break;
			case 4:	 // hrs, min, sec and fractions of seconds entered	
				$hrs = $t[0];
				$min = $t[1];
				$sec = $t[2];
				$frac = $t[3];
				break;
			}

			switch(count($t)) // nbr of time elements
			{
			case 1:	 // one element: negative value	
				// -1, -2, -3 are valid
				if(($t[0] <= -1) && ($t[0] >= -3)) {
					$time = $t[0];
				}
				else if ($t[0] > 0) {
					$time = $sec*1000;
				}
				else {
					$time = NULL;
				}
				break;
			case 2:	 // sec and fractions of seconds entered	
			case 3:	 // min, sec and fractions of seconds entered	
			case 4:	 // hrs, min, sec and fractions of seconds entered	
				// convert fractions to thousands of seconds
				if(strlen($frac) == 1) {	// 10th of seconds entered
					$frac = $frac * 100;
				}
				else if(strlen($frac) == 2) {	// 100th of seconds entered
					$frac = $frac * 10;
				}
				if(strlen($frac) == 3) {	// 1000th of seconds entered
					$frac = $frac * 1;
				}
				// store time value in thousands of seconds
				
				// secFlag is set at trakdisciplines (wind / nowind)
				if($secFlag){
					if((($hrs >=0) && ($hrs <= 23))	
						&& (($min >=0) && ($min <= 59))
						&& ($sec >=0)
						&& (($frac >=0) && ($frac <= 999)))
					{
						$time = $hrs*3600000 + $min*60000 + $sec*1000 + $frac;
					}
					// time value invalid
					else {
						$time = NULL;
					}
				}else{
					if((($hrs >=0) && ($hrs <= 23))	
						&& (($min >=0) && ($min <= 59))
						&& (($sec >=0) && ($sec <= 59))
						&& (($frac >=0) && ($frac <= 999)))
					{
						$time = $hrs*3600000 + $min*60000 + $sec*1000 + $frac;
					}
					// time value invalid
					else {
						$time = NULL;
					}
				}
				break;
			default:
				$time = NULL;
			}
		}

		return $time;
	}

} // end PerformanceTime 


/********************************************
 *
 * CLASS PerformanceAttempt
 *
 * Class to handle an athletes meter performance
 *
 *******************************************/

class PerformanceAttempt extends Performance
{

	function PerformanceAttempt($performance)
	{
		$this->setPerformance($performance);
	}

	function validatePerformance($performance, $secFlag = false)
	{  
		// format meter value: replace all separators by comma
		// (e.g. 10.52 -> 10,52)
		$meter = strtr($performance, $GLOBALS['cfgResultsSepTrans']);
		$tok = strtok($meter, $GLOBALS['cfgResultsSeparator']);
		
        if (!is_numeric($performance)){
            $performance=strtoupper($performance);
        }
        
		// get tokanized performance if entered without separators
		if(strpos($time, $GLOBALS['cfgResultsSeparator']) === false){  
			
		   	if ($performance == $GLOBALS['cfgInvalidResult']['WAI']['code']) {
                $performance = $GLOBALS['cfgMissedAttempt']['db'];                
            }
            elseif  ($performance == $GLOBALS['cfgInvalidResult']['NAA']['code'] ) {
                
		   	  	$performance = $GLOBALS['cfgMissedAttempt']['dbx'];   
		  	}
		  	else {
		    	$performance = substr($performance,0,-2).$GLOBALS['cfgResultsSeparator'].substr($performance,strlen($performance)-2);
		 	}     
			$meter = strtr($performance, $GLOBALS['cfgResultsSepTrans']);
			$tok = strtok($meter, $GLOBALS['cfgResultsSeparator']);
		}
	   
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
		$meter = NULL;

		if($num == TRUE)	// only numerics found
		{
			if(count($t) == 2) // nbr of meter elements
			{
				$m = $t[0];
				$cm = $t[1];
			}

			if(count($t) == 1) 	 // one element: negative value or zero
			{
				// negative value  
                
                 
				if((($t[0] <= $GLOBALS['cfgInvalidResult']['DNS']['code'])
					&& ($t[0] >= $GLOBALS['cfgInvalidResult']['NRS']['code']) )
					||  ($t[0] == $GLOBALS['cfgMissedAttempt']['db'])
                    ||  ($t[0] == $GLOBALS['cfgMissedAttempt']['dbx']) 
                    ||  ($t[0] == $GLOBALS['cfgInvalidResult']['NAA']['code'])) 
				{
					$meter = $t[0];
				}
				// zero or meter value assumed
				else if ($t[0] >= 0) {
					$meter = $t[0] * 100;
				}
			}
			else if(count($t) == 2) // nbr of meter elements
			{
				// store meter value in centimeters
				if(strlen($cm) == 1) {	// decimeters entered
					$cm = $cm * 10;
				}
				else if(strlen($cm) > 2) {	// fractions of centimeters (rounded down)
					$cm = substr($cm, 0, 2);
				}
				else if(strlen($cm) == 2) {	// decimeters entered
					$cm = $cm;
				}

				$meter = $m * 100 + $cm;
			}
		}	// ET only numerics entered

		else {	// non-numeric value entered
			if((count($t) == 1) 	 // one element: may be '-'
				&& ($t[0] == $GLOBALS['cfgMissedAttempt']['code']))
			{
			   	$meter = $GLOBALS['cfgMissedAttempt']['db'];
			}
            elseif ((count($t) == 1)      // one element: may be 'X'  
                && ($t[0] == 'X' || $t[0] == 'x')) 
                {
                     $meter =  $GLOBALS['cfgMissedAttempt']['dbx'];       
                }  
		}    
		return $meter;
	}

} // end PerformanceAttempt

/**
 * Formating result db entrys to alabus verband formats
 */

function AA_alabusTime($time){
   
	if($time > 0){
		$time = $time/1000;
		list($sec, $mili) = explode(".", $time);
		list($hour, $rest) = explode(".", ($sec/3600));
		list($min, $rest) = explode(".", (($sec-($hour*3600))/60));
		list($sec, $rest) = explode(".", ($sec-($hour*3600)-($min*60)));
		
		// round up to hundredth  (examples: 651 --> 660 and 650 --> 650)
	    $mili=ceil(sprintf ("%-03s",$mili)/10);              
	   	list($a,$mili)=explode(".",($mili/100));         
		$sec+=$a;                                 // round up to hundredth  (example: 999 --> 1000)
		return sprintf("%02d", $hour).":".sprintf("%02d", $min).":".sprintf("%02d", $sec).".".sprintf("%-03s", $mili);
	}else{
		return $time;
	}
}

function AA_alabusDistance($meter){
	if($meter > 0){
		$meter = $meter / 100;
		return sprintf("%09.2f", $meter);
	}else{
		return $meter;
	}
}

function AA_alabusScore($score){
	return sprintf("%07.1f", $score);
}

} // end AA_CL_PERFORMANCE_LIB_INCLUDED

?>
