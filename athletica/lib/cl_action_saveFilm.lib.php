<?php

if (!defined('AA_CL_ACTION_SAVEFILM_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_SAVEFILM_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Action_saveFilm
 *
 * implements action "saveFilm"
 * (see base cl_action_default for implementation details)
 *
 *
 * expects following POST-parameters:
 * 	act		this action (already evaluated by controller.php)
 * 	round		primary key of this event round
 * 	item		primary key of heat
 *		film		film description
 *
 *******************************************/


require('./lib/cl_action_default.lib.php');
require('./lib/cl_film.lib.php');

class Action_saveFilm extends Action_default
{
	function Action_saveFilm()
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
		$film = new Film($_POST['item']);
		$film->save($_POST['film']);
		if(!empty($GLOBALS['AA_ERROR'])) {
			return;
		}

		$this->ok_out['msg'] = $GLOBALS['strOKUpdate'] . ": ". $GLOBALS['strFilm'] . " " . $_POST['film']
			. " (xSerie=" . $_POST['item'] . ")";
	}
	
} // end class Action_saveFilm


} // end AA_CL_ACTION_SAVEFILM_LIB_INCLUDED

?>
