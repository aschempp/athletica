<?php

if (!defined('AA_CL_ACTION_DEFAULT_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_DEFAULT_LIB_INCLUDED', 1);

/********************************************
 *
 * CLASS Action_default
 *
 * This is the base class for all specific action classes.
 * An action class controls the execution of a specific action request
 *
 *	Variables:
 *		- $ok				script to be called when processing OK
 *		- $ok_frame		frame (defined in index.php), where output is displayed
 *		- $ok_out		array of form parameters to be sent 
 *		- $err			script to be called when error
 * 	- $err_frame	frame (defined in index.php), where output is displayed
 *		- $err_out		array of form parameters to be sent 
 *
 *	Methods:
 *		- Constructor	defines variables
 *		- process()		specific action processing, usually provided
 *                   by a specific class
 *		- update()		Javascript updates to the requesting page after
 *                   processing is completed
 *		- get[XX]Action() 		retrieves action script
 *		- get[XX]Target() 		retrieves target frame
 *		- get[XX]OKParameters()	retrieves from parameters
 *
 *******************************************/

class Action_default
{
	var $ok;
	var $ok_frame;
	var $ok_out;

	var $err;
	var $err_frame;
	var $err_out;

	function Action_default()
	{
		$this->ok = "status.php";
		$this->ok_frame = "status";
		$this->ok_out = array(
			"msg"=>''
			);

		$this->err = "meeting.php";
		$this->err_frame = "main";
		$this->err_out = array();
	}

	function process()
	{
		$this->ok_out['msg'] = "OK";
	}
	
	function update()
	{}
	
	function getOKAction()
	{
		return $this->ok;
	}
	
	function getOKTarget()
	{
		return $this->ok_frame;
	}

	function getOKParameters()
	{
		return $this->ok_out;
	}
	
	function getErrorAction()
	{
		return $this->err;
	}
	
	function getErrorTarget()
	{
		return $this->err_frame;
	}

	function getErrorParameters()
	{
		return $this->err_out;
	}
} // end class Action_default


} // end AA_CL_ACTION_DEFAULT_LIB_INCLUDED

?>
