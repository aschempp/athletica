<?php

if (!defined('AA_CL_GUI_MENULIST_LIB_INCLUDED'))
{
	define('AA_CL_GUI_MENULIST_LIB_INCLUDED', 1);


	require('./lib/cl_gui_button.lib.php');
	require('./lib/cl_gui_searchfield.lib.php');

/********************************************
 *
 * CLASS GUI_Menulist
 *
 * Prints a function menu.
 *
 *******************************************/

class GUI_Menulist
{
	var $buttons;


	function GUI_Menulist()
	{
		$this->buttons = array();
		$this->searchfield = null;
	}



	/*
	 *	addButton()
	 *	-----------
	 * To add a button, provide the onClick-action, its caption
	 * and the target window.
	 *		action:		link href-value
	 *		caption:		the label to be printed
	 *		target:		the target window (if not provided, the same window will
	 *						be used; otherwise say e.g. '_blank')
	 */

	function addButton($action, $caption, $target='_self')
	{
		$this->buttons[] = new GUI_Button($action, $caption, $target);
	}


	/*
	 *	addSearchField()
	 *	----------------
	 * To add a athlete search field.
	 *		action:	link href-value
	 *		target:	the target window (if not provided, the same window will
	 *					be used; otherwise say e.g. '_blank')
	 *		method:	POST (default) or GET
	 *		back:		back-link after search
	 */

	function addSearchField($action, $target='_self', $method='post', $back='', $hide = false)
	{
		$this->searchfield = new GUI_Searchfield($action, $target, $method, $back, $hide);
	}


	/*
	 *	printMenu()
	 *	-----------
	 * Finally, print the menu list
	 */

	function printMenu()
	{
		?>
<table><tr>
		<?php
		foreach($this->buttons as $button) {
			?>
	<td class='forms'><?php $button->printButton(); ?></td>
			<?php
		}
		if($this->searchfield != null) {
			?>
	<td class='forms'>&nbsp;</td>
	<td class='forms'><?php $this->searchfield->printSearchfield(true); ?></td>
			<?php
		}
?>
</tr></table>
<?php
	}


} // END CLASS Gui_Menulist


} // end AA_CL_GUI_MENULIST_LIB_INCLUDED

?>
