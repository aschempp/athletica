<?php

if (!defined('AA_CL_ACTION_SAVEWIND_LIB_INCLUDED'))
{
	define('AA_CL_ACTION_SAVEWIND_LIB_INCLUDED', 1);



/********************************************
 *
 * CLASS Action_saveWind
 *
 * implements action "saveWind"
 * (see base cl_action_default for implementation details)
 *
 *
 * expects following POST-parameters:
 * 	act		this action (already evaluated by controller.php)
 * 	round		primary key of this event round
 * 	item		primary key of heat
 *		wind		wind for this heat
 *
 *******************************************/


require('./lib/cl_action_default.lib.php');
require('./lib/cl_wind.lib.php');

class Action_saveWind extends Action_default
{
	var $wind;

	function Action_saveWind()
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

		$this->wind = '';
	}

	function process()
	{
		$this->wind = new HeatWind($_POST['round'], $_POST['item']);
		$this->wind->setWind($_POST['wind']);
		if(!empty($GLOBALS['AA_ERROR'])) {
			return;
		}

		$this->ok_out['msg'] = $GLOBALS['strOKUpdate'] . ": "
			. $GLOBALS['strWind'] . " " . $this->wind->getWind()
			. " (xSerie=" . $_POST['item'] . ")";
	}
	

	function update()
	{
?>
<script type="text/javascript">
<!--
	parent.frames[1].document.<?php echo $_POST['obj']; ?>.wind.value="<?php echo $this->wind->getWind(); ?>";
//-->
</script>
<?php
	}	// end function update()

} // end class Action_saveFilm


} // end AA_CL_ACTION_SAVEFILM_LIB_INCLUDED

?>
