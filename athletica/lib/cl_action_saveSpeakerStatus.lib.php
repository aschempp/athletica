<?php

if (!defined('AA_CL_ACTION_SAVESPEAKERSTATUS_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_SAVESPEAKERSTATUS_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Action_saveSpeakerStatus
 *
 * implements action "saveSpeakerStatus"
 * (see base cl_action_default for implementation details)
 *
 *
 * expects following POST-parameters:
 * 	act		this action (already evaluated by controller.php)
 * 	round		primary key of this event round
 *		status	status
 *		checked	status checked
 *
 *******************************************/


require('./lib/cl_action_default.lib.php');
require('./lib/cl_round.lib.php');


class Action_saveSpeakerStatus extends Action_default
{
	function Action_saveSpeakerStatus()
	{
		$this->ok = "status.php";
		$this->ok_frame = "status";
		$this->ok_out = array(
			"msg"=>'OK'
			);

		$this->err = "print_rankinglist.php";
		$this->err_frame = "main";
		$this->err_out = array(
			"round"=>$_POST['round']
			, "type"=>"single"
			, "formaction"=>"speaker"
			);
	}


	function process()
	{
		require('./lib/common.lib.php');
		require('./lib/utils.lib.php');

		if(empty($_POST['checked'])) {	// 
			$status = $GLOBALS['cfgSpeakerStatus']['open'];
		}
		else {
			$status = $_POST['status'];
		}

		$round = new Round($_POST['round']);
		$round->setSpeakerStatus($status);
		if(!empty($GLOBALS['AA_ERROR'])) {
			return;
		}

		$this->ok_out['msg'] = $GLOBALS['strOKUpdate'] . ": "
	  		. $GLOBALS['strSpeakerStatus']
		  	. " (xRunde = " . $_POST['round'] . ")";
	}
	
} // end class Action_saveSpeakerStatus


} // end AA_CL_ACTION_SAVESPEAKERSTATUS_LIB_INCLUDED

?>
