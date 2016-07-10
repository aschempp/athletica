<?php

if (!defined('AA_CL_GUI_BUTTON_LIB_INCLUDED'))
{
	define('AA_CL_GUI_BUTTON_LIB_INCLUDED', 1);


/********************************************
 *
 * CLASS GUI_Button
 *
 * Prints a button to call other functions.
 *
 *******************************************/

class GUI_Button
{
	var $action;
	var $caption;

	/*
	 *	Constructor
	 *	-----------
	 * To create a button, provide the onClick-action, its caption
	 * and the target window.
	 *		action:		link href-value
	 *		caption:		the label to be printed
	 *		target:		the target window (if not provided, the same window will
	 *						be used; otherwise say e.g. '_blank')
	 */

	function GUI_Button($action, $caption, $target='_self')
	{
		$this->set($action, $caption, $target);
	}

	/*
	 *	set()
	 *	-----------
	 * set new properties
	 */

	function set($action, $caption, $target='_self')
	{
		$this->action = $action;
		$this->caption = $caption;
		$this->target = $target;
	}

	/*
	 *	print()
	 *	-----------
	 * Finally, print the new button
	 */

	function printButton()
	{
		?>
<button type='button'
	onClick="window.open('<?php echo $this->action; ?>', '<?php echo $this->target; ?>')">
	<?php echo $this->caption; ?>
</button>
<?php
	}

} // END CLASS Gui_Button

} // end AA_CL_GUI_BUTTON_LIB_INCLUDED

?>
