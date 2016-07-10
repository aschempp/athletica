<?php

if (!defined('AA_CL_ACTION_SAVEHANDSTOPPED_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_SAVEHANDSTOPPED_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Action_saveHandStopped
 *
 * implements action "saveHandStopped"
 * (see base cl_action_default for implementation details)
 *
 *
 * expects following POST-parameters:
 * 	act		this action (already evaluated by controller.php)
 * 	round		primary key of this event round
 * 	item		primary key of heat
 *	handstopped	checkbox if heat is hand stopped
 *
 *******************************************/


require('./lib/cl_action_default.lib.php');
require('./lib/heats.lib.php');

class Action_saveHandStopped extends Action_default
{
	function Action_saveHandStopped()
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
	}

	function process()
	{
		
		AA_heats_changeHandStopped($_POST['item']);
		if(!empty($GLOBALS['ERROR'])){
			$GLOBALS['AA_ERROR'] = $GLOBALS['ERROR'];
			return;
		}
		
		$this->ok_out['msg'] = $GLOBALS['strOKUpdate'] . ": ". $GLOBALS['strHandStopped'] . " " . $_POST['handstopped']
			. " (xSerie=" . $_POST['item'] . ")";
	}
	
}


}

?>
