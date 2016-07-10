<?php

if (!defined('AA_CL_ACTION_SAVEHEATSTATUS_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_SAVEHEATSTATUS_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Action_saveHeatStatus
 *
 * implements action "saveHeatStatus"
 * (see base cl_action_default for implementation details)
 *
 *
 * expects following POST-parameters:
 * 	act		this action (already evaluated by controller.php)
 * 	round		primary key of this event round
 * 	item		primary key of round
 * 	status	new heat status
 *
 *******************************************/


require('./lib/cl_action_default.lib.php');
require('./lib/cl_heat.lib.php');


class Action_saveHeatStatus extends Action_default
{
	function Action_saveHeatStatus()
	{
		$this->ok = "status.php";
		$this->ok_frame = "status";
		$this->ok_out = array(
			"msg"=>'OK'
			);

		$this->err = "speaker_results.php";
		$this->err_frame = "main";
		$this->err_out = array(
			"round"=>$_POST['round']
			);
	}


	function process()
	{
		require('./lib/common.lib.php');
		require('./lib/utils.lib.php');

		if(empty($_POST['status'])) {	// 
			$status = $GLOBALS['cfgHeatStatus']['open'];
		}
		else {
			$status = $GLOBALS['cfgHeatStatus']['announced'];
		}

		$heat = new Heat($_POST['item'], $_POST['round']);
		$heat->setHeatStatus($status);
		if(!empty($GLOBALS['AA_ERROR'])) {
			return;
		}

		$this->ok_out['msg'] = $GLOBALS['strOKUpdate'] . ": "
	  		. $GLOBALS['strResultAnnouncement']
		  	. " (xSerie = " . $_POST['item'] . ")";
	}
	

	function update()
	{
?>
<script type="text/javascript">
<!--

	if(parent.frames[1].document.getElementById('<?php echo "heat_" . $_POST['item']; ?>').className=="nav")
	{
		parent.frames[1].document.getElementById('<?php echo "heat_" . $_POST['item']; ?>').className="nav_announced";
	}
	else if(parent.frames[1].document.getElementById('<?php echo "heat_" . $_POST['item']; ?>').className=="nav_announced")
	{
		parent.frames[1].document.getElementById('<?php echo "heat_" . $_POST['item']; ?>').className="nav";
	}

//-->
</script>
<?php
	}

} // end class Action_saveHeatStatus

} // end AA_CL_ACTION_SAVEHEATSTATUS_LIB_INCLUDED
?>
