<?php

if (!defined('AA_CL_ACTION_SAVERESULT_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_SAVERESULT_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Action_saveResult
 *
 * implements action "saveResult"
 * (see base cl_action_default for implementation details)
 *
 *
 * expects following POST-parameters:
 * 	act		this action (already evaluated by controller.php)
 * 	obj		name of calling FORM
 * 	type		$cfgDisciplineType
 * 	round		primary key of this event round
 * 	start		primary key of athlete in this heat
 * 	item		primary key of result (update, delete only)
 *		perf		new performance value
 *
 * updates the following fields in the calling FORM:
 *		item		new primary key of result (insert and delete only)
 *		perf		new, formatted performance value
 *
 *******************************************/


require('./lib/cl_action_default.lib.php');
require('./lib/cl_result.lib.php'); 
require('./lib/results.lib.php'); 

class Action_saveResult extends Action_default
{
	var $reply;
	var $type;        

	function Action_saveResult()
	{
		$this->ok = "status.php";
		$this->ok_frame = "status";
		$this->ok_out = array(
			"msg"=>'OK'
			);

		$this->err = "event_results.php";
		$this->err_frame = "main";
		$this->err_out = array(
			"round"=>$_POST['round']
			);

		$this->type = $_POST['type'];
	}

	function process()
	{   
		require('./lib/common.lib.php');
		require('./lib/utils.lib.php');
        // get program mode
        $prog_mode = AA_results_getProgramMode();
        if ($prog_mode == 2){
            
              $sql = "SELECT 
                            ss.xSerienstart,
                            ss.Position,
                            MAX(ss.Position2), 
                            MAX(ss.Position3),
                            s.MaxAthlet,
                            count(*)
                      FROM 
                            runde AS r
                            LEFT JOIN serie AS s ON (s.xRunde = r.xRunde)
                            LEFT JOIN serienstart AS ss  ON (ss.xSerie = s.xSerie)
                      WHERE 
                            r.xRunde = " . $_POST['round'] ."
                      GROUP BY s.xSerie";  
       
                      $res = mysql_query($sql);    
                      $row = mysql_fetch_row($res);
                      $maxPos2 = $row[2];
                      $maxPos3 = $row[3];
                      $maxAthlete = $row[4];
                      $countAthlete = $row[5]; 
        }
        
        global $cfgMaxAthlete, $cfgAfterAttempts1, $cfgAfterAttempts2;

		// track disciplines, with or without wind
		if(($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeNone']])
			|| ($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
			|| ($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']])
			|| ($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeDistance']])
			|| ($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']]))
		{
			$result = new TrackResult($_POST['round'], $_POST['start'], $_POST['item']);
            
			
			// if this is a track (wind / nowind) format results in seconds e.g. 80,123 secs
			if(($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
			|| ($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']])
			|| ($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']])){ 		
               
                
                $this->reply = $result->save($_POST['perf'], '', true,$_POST['remark'], $_POST['xAthlete']);
               
			}else{
                
				$this->reply = $result->save($_POST['perf'],'','',$_POST['remark'], $_POST['xAthlete']);
			}
            
			
			if(!empty($GLOBALS['AA_ERROR'])) {
				return;
			}
             if ($_POST['perf'] == ''){                 
                $txt = '';
            }                            
            else {
			    $txt =  AA_formatResultTime($this->reply->getPerformance());
            }
		}

		// technical disciplines with wind
		else if($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJump']])
		{
			$result = new TechResult($_POST['round'], $_POST['start'], $_POST['item']);
			$this->reply = $result->save($_POST['perf'], $_POST['wind'],'',$_POST['remark'], $_POST['xAthlete'], $_POST['row_col'], $_POST['maxatt']);
                                    
			if(!empty($GLOBALS['AA_ERROR'])) {
				return;
			}

			$txt = AA_formatResultMeter($this->reply->getPerformance())
				.", ". $this->reply->getInfo();
		}

		// technical disciplines without wind
		else if(($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJumpNoWind']])	
		|| ($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeThrow']]))
		{
            
			$result = new TechResult($_POST['round'], $_POST['start'], $_POST['item']);
           
			$this->reply = $result->save($_POST['perf'],'','',$_POST['remark'], $_POST['xAthlete'], $_POST['row_col'], $_POST['maxatt']);     
            
			if(!empty($GLOBALS['AA_ERROR'])) {
				return;
			}
            if ($this->reply->getPerformance() == ''){                 
                $txt = '';
            }                            
            else {
			    $txt = AA_formatResultMeter($this->reply->getPerformance());
            } 
		}

		// high jump, pole vault
		else if($this->type== $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeHigh']])
		{
			$result = new HighResult($_POST['round'], $_POST['start'], $_POST['item']);
			$this->reply = $result->save($_POST['perf']);
			$this->reply = $result->save($_POST['perf'], $_POST['attempts']);
			if(!empty($GLOBALS['AA_ERROR'])) {
				return;
			}
			$txt = AA_formatResultMeter($this->reply->getPerformance())
				." [ ". $this->reply->getInfo() ." ]";
		}

		switch($this->reply->getAction())
		{
			case RES_ACT_INSERT:
				$msg = $GLOBALS['strOKInsert'] . ": $txt (xStart=" . $_POST['start'] .  ")"; 
				break;
			case RES_ACT_UPDATE:
				$msg = $GLOBALS['strOKUpdate'] . ": $txt (xResultat=" . $this->reply->getKey()
					. ")"; 
				break;
			case RES_ACT_DELETE:
				$msg = $GLOBALS['strOKDelete'] . " (xResultat=" . $this->reply->getKey() . ")"; 
				break;
			default:
				$msg = ""; 
		}

		$this->ok_out['msg'] = $msg;           
	}
	

	function update()
	{
		require('./lib/utils.lib.php');
		require('./lib/cl_result.lib.php');

		// update result ID
		// ----------------
		if(($this->reply->getAction() == RES_ACT_INSERT)
			|| ($this->reply->getAction() == RES_ACT_DELETE))
		{
			$item = '';
			if($this->reply->getAction() == RES_ACT_INSERT) {
				$item = $this->reply->getKey();
			}
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.item.value="<?php echo $item; ?>";
//-->
</script>
<?php
		}

		// update page with formatted result, info
		// ---------------------------------------

		// after insert, update action
		if(($this->reply->getAction() == RES_ACT_UPDATE)
			|| ($this->reply->getAction() == RES_ACT_INSERT))
		{
			// track disciplines, with or without wind
			if(($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeNone']])
				|| ($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrack']])
				|| ($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeTrackNoWind']])
				|| ($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeDistance']])
				|| ($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeRelay']]))
			{
				$perf = AA_formatResultTime($this->reply->getPerformance());
			}
			else
			{
				$perf = AA_formatResultMeter($this->reply->getPerformance());
			}
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.perf.value="<?php echo $perf; ?>";
//-->
</script>
<?php
			// technical disciplines: update wind
			if($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJump']])
			{
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.wind.value="<?php echo $this->reply->getInfo(); ?>";
//-->
</script>
<?php
			}
			// high jump, pole vault: update attempts
			else if($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeHigh']])
			{
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.attempts.value="<?php echo $this->reply->getInfo(); ?>";
//-->
</script>
<?php
			}

		}

		// after delete action
		else if($this->reply->getAction() == RES_ACT_DELETE)
		{
			// technical disciplines: clear wind
			if($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeJump']])
			{
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.wind.value="";
//-->
</script>
<?php
			}
			// high jump, pole vault: clear attempts
			else if($this->type == $GLOBALS['cfgDisciplineType'][$GLOBALS['strDiscTypeHigh']])
			{
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.attempts.value="";
//-->
</script>
<?php
			}
		}	// ET insert/update, delete

	}	// end function update()
} // end class Action_saveResult


} // end AA_CL_ACTION_SAVERESULT_LIB_INCLUDED

?>
